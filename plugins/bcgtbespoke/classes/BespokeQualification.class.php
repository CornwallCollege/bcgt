<?php

/**
 * Description of BespokelQualification
 *
 * @author mchaney
 */

global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Qualification.class.php');

//This can be abstract if the qualification class is never returned from the get_correct_qual_class
//but doesnt always need to be used in an actual qualification target qual
class BespokeQualification extends Qualification
{

    //these are hardcoded from the install. The tables dont have auto incremental
    //id's
    
    //the database id
	const ID = 1;
	const NAME = 'Bespoke';
	const FAMILYID = 1;
    
    protected $credits;
    protected $displaytype;
    protected $grading;
    protected $settings;

	//any properties
    public function BespokeQualification($qualID, $params, $loadParams)
    {
        
        global $CFG, $DB;
        
        parent::Qualification($qualID, $params, $loadParams);
        
        $this->bespoke = true;
        
        // Qual stuff
		$record = $DB->get_record("block_bcgt_qualification", array("id" => $qualID));
        if ($record)
        {
            $this->name = $record->name;
            $this->credits = $record->credits;
            $this->code = $record->code;
            $this->additionalName = $record->additionalname;
            $this->noYears = $record->noyears;
        }
        
        // Bespoke stuff
		$record = $DB->get_record("block_bcgt_bespoke_qual", array("bcgtqualid" => $qualID));
        if ($record)
        {
            
            $this->displaytype = $record->displaytype;
            $this->subType = new SubType(-1, $record->subtype);
            $this->level = new Level(-1, $record->level);
            $this->grading = $record->gradingstructureid;
            
        }
        
        $this->load_settings();

        
    }
    
    
    /**
	 * This funcion loads up the qualification with the students data as set by the
	 * student id. 
	 * it checks if there is an award for the current type of qualification
	 * if there is it either gets the award or calculates the prediected grade
	 * it then loads the user information for all of the units and subsequent criteria. 
	 * @param unknown_type $studentID
	 */
    //$loadLevel = Qualification::LOADLEVELMIN, $loadAward = true
	public function load_student_information($studentID,
            $loadParams = null)
	{
        $this->clear_student_information();
		//check all of the units on the qual are those that the student is doing. 
		//if not remove them or set a variable on them.
		//For all units they are doing set the awards on them. 
		//For each criteria they are doing on each unit load the users values.
		$this->studentID = $studentID;
        $this->load_student();
        if($loadParams && $loadParams->loadLevel && 
                $loadParams->loadLevel >= Qualification::LOADLEVELUNITS)
        {
            foreach($this->units AS $unit)
            {
                //will go off and load the student info onto all units
                //checks if the student 'is doing' the unit.
                $unit->load_student_information($studentID, $this->id, $loadParams);	
            }
        }
        
		//Unit work above could have changed the potential final and/or prediected grade
		if($loadParams && isset($loadParams->loadAward) && $this->has_final_grade())
		{
                        
			//so the qual has a final grade (this is different per qual type)
			//do we allready have a grade in the db for this student?
			if(!$qualAwards = $this->retrieve_student_award())
			{
				$this->studentAward = $this->calculate_final_grade();
                $this->predictedAward = $this->calculate_predicted_grade();
			}
			else
			{
                $this->predictedAward = (isset($qualAwards->Predicted)) ? $qualAwards->Predicted : false;   
                $this->studentAward = (isset($qualAwards->Final)) ? $qualAwards->Final : false; 
			}
		}
        
        if($loadParams && isset($loadParams->loadTargets))
        {
            $userCourseTargets = new UserCourseTarget(-1);
            $targetGrades = $userCourseTargets->retrieve_users_target_grades($this->studentID, $this->id);
            if($targetGrades)
            {
                //as its one qual it will have one object
                $targetGradeObj = $targetGrades[$this->id];
                $this->userTargetGrades = $targetGradeObj;
            }
        }
		//finally we want to load the units that this student is doing that
		//are not on the qualification.
		//for example APL units
		//find all extra units that are not on the qual that this student is doing
		//for this qual.
        if(!$loadParams || $loadParams && !isset($loadParams->loadAddUnits) ||
                $loadParams && !isset($loadParams->loadAddUnits) && $loadParams->loadAddUnits == true)
        {
            $this->add_students_other_units();
        }       
        // Get comments as well
        $this->comments = "";
        $comments = $this->get_qual_comments(); 
        $this->comments = $comments; 

        //what about criteria that are on the qual but not on the unit?
        //for example Alevels. 
        $this->load_qual_criteria_student_info($studentID, $this->id);
        
        //Any extra loads
        $this->qual_specific_student_load_information($studentID, $this->id);
        
    }
    
    
    
    /**
	 * Get the display name for the qual
	 * This is level, type, subtype and name 
	 */
	public function get_display_name($long = true, $seperator = ' ', $exclusions = array(), $returnType = 'String')
	{
        $qual = new stdClass();
        $qual->family = $this->get_family();
        $qual->level = $this->get_level()->get_level();
        $qual->subtype = $this->subType->get_subtype();
        $qual->subtypeshort = $this->subType->get_short_subtype();
        $qual->name = $this->name;
        $qual->additionalname = $this->additionalName;
        $qual->levelid = $this->level->get_id();
        $qual->isbespoke = 1;
        $qual->displaytype = $this->get_display_type();
        return bcgt_get_qualification_display_name($qual, $long, $seperator, $exclusions, $returnType);
	}
    
    /**
     * 
     * @global type $CFG
     * @global type $DB
     * @return boolean
     */
    public function get_default_credits()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_qual_att} 
            WHERE bcgttargetqualid = ? AND name = ?";
        $params = array($this->bcgtTargetQualID, 'DEFAULT_CREDITS');
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return $record->value;
        }
        return false;
    }
    
    
    
    public function has_sub_criteria()
    {
        return true;
    }
    
    /**
	 * Returns the id of the type not the qual
	 */
	public function get_family_ID()
    {
        return BespokeQualification::FAMILYID;
    }
    
    /**
     * Returns the family name
     */
    public function get_family()
    {
        return BespokeQualification::NAME;
    }
    
    /**
	 * Returns the human type name
	 */
	public function get_type()
    {
        return BespokeQualification::NAME;
    }
	
	/**
	 * Returns the id of the type not the qual
	 */
	public function get_class_ID()
    {
        return BespokeQualification::ID;
    }
    
    public function get_display_type(){
        return $this->displaytype;
    }
    
    public function get_grading(){
        return $this->grading;
    }
    
    function get_credits()
	{
        if($this->credits)
        {
            return $this->credits;
        }
        $credits = $this->get_default_credits();
        $this->credits = $credits;
        return $credits;
	}
    
    public function load_users($role = 'student', $loadStudentQuals = false, 
            $loadParams = null, $courseID = -1)
    {
        global $DB;
        $roleDB = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = ?', array($role));
        $users = $this->get_users($roleDB->id, '', 'lastname ASC', $courseID);
        $usersQuals = array();
        if($users)
        {
            $property = 'users'.$role;
            $propertyQual = 'usersQuals'.$role;
            $this->$property = $users;
            if($loadStudentQuals)
            {
                foreach($users AS $user)
                {
                    $studentQual = Qualification::get_qualification_class_id($this->id, $loadParams);
                    if($studentQual)
                    {
                        $studentQual->load_student_information($user->id, 
                            $loadParams);
                        $usersQuals[$user->id] = $studentQual;
                    }
                }
                $this->$propertyQual = $usersQuals;
            }
        }
    }
    
    /**
	 * Gets the form fields that will go on edit_qualification_form.php
	 * They are different for each qual type
	 * e.g for Alevel its an <input> for ums
	 */
	public function get_edit_form_fields()
    {
        
        $retval = '<div class="inputContainer"><div class="inputLeft">';
        $retval .= '<label for="credits">'.get_string('bteccredits', 'block_bcgt')
                .': </label></div>';
		$retval .= '<div class="inputRight">'.
                '<input type="number" name="credits" value="'.$this->credits.'"  /></div></div>';
        
        
        // Assign/Create grading structure
        
        $structures = BespokeQualification::get_qual_grading_structures();
        
        $retval .= '<div class="inputContainer"><div class="inputLeft">';
        $retval .= '<span class="required">*</span><label for="credits">'.get_string('gradingstructure', 'block_bcgt')
                .': </label></div>';
		$retval .= '<div class="inputRight">';
            $retval .= '<select name="gradingstructure">';
                $retval .= '<option value="">'.get_string('pleaseselect', 'block_bcgt').'</option>';
                
                if ($structures)
                {
                    foreach($structures as $structure)
                    {
                        $sel = (isset($this->grading) && $this->grading == $structure->id) ? 'selected' : '';
                        $output = "<option value='{$structure->id}' {$sel}>";
                        $output .= $structure->name . ": &nbsp;&nbsp;&nbsp; ";
                        foreach($structure->values as $val)
                        {
                            $output .= $val->grade . " ({$val->rangelower} - {$val->rangeupper} ) &nbsp;";
                        }
                        
                        $output .= "</option>";
                        $retval .= $output;
                        
                    }
                }
                
            $retval .= '</select>';
        $retval .= '</div></div>';
        
        
        // Settings
        $retval .= "<br><br><br>";
        
        $retval .= "<table id='edit_qual_settings'>";
            $retval .= "<tr><th colspan='4'>".get_string('settings')."</th></tr>";
            
            $retval .= "<tr>";
            
                $retval .= "<td>".get_string('usepercentcompleteonunits', 'block_bcgt')."</td>";
                $chk = ($this->get_setting('setting_use_unit_percents') == 1) ? 'checked' : '';
                $retval .= "<td><input type='checkbox' name='setting_use_unit_percents' value='1' {$chk} /></td>";
                
                $retval .= "<td>".get_string('ignoreautocalculation', 'block_bcgt')."</td>";
                $chk = ($this->get_setting('setting_ignore_auto_calcs') == 1) ? 'checked' : '';
                $retval .= "<td><input type='checkbox' name='setting_ignore_auto_calcs' value='1' {$chk} /></td>";
                
            $retval .= "</tr>";
        
        $retval .= "</table>";
        
        
        
        return $retval;
    }
    
    /**
	 * Used in edit qual
	 * Gets the submitted data from the edit form fields
	 * edit_qualification_form.php
	 * E.g. for Alevel its getting the POST of the ums input.
	 */
	public function get_submitted_edit_form_data()
    {
        $this->credits = $_POST['credits'];	
    }
    
    /**
     * Processes the submitted info to make sure we've filled everything out properly
     * @return boolean
     */
    public function process_create_update_qual_form(){
        
        $displaytype = optional_param('displaytype', NULL, PARAM_TEXT);
        $subtype = optional_param('subtype', NULL, PARAM_TEXT);
        $customsubtype = optional_param('customsubtype', NULL, PARAM_TEXT);
        $level = optional_param('level', NULL, PARAM_INT);
        $name = optional_param('name', NULL, PARAM_TEXT);
        $name = trim($name);
        $grading = optional_param('gradingstructure', NULL, PARAM_INT);
                
        $this->processed_errors = '';
        
        // Display type must be set
        if (is_null($displaytype) || $displaytype == ''){
            $this->processed_errors .= get_string('error:displaytype', 'block_bcgt') . '<br>';
        }
        
        // Either subtype or custom subtype must be set
        
        if ((is_null($subtype) && is_null($customsubtype)) || ($subtype == '' && $customsubtype == '')){
            $this->processed_errors .= get_string('error:subtype', 'block_bcgt') . '<br>';
        }
        
        
        // Name
        if (is_null($name) || empty($name)){
            $this->processed_errors .= get_string('error:name', 'block_bcgt') . '<br>';
        }
        
        // Grading structure
        if (is_null($grading) || $grading <= 0){
            $this->processed_errors .= get_string('error:gradingstructure', 'block_bcgt') . '<br>';
        }
        
        
        if (!empty($this->processed_errors)){
            return false;
        }
        
        $this->displaytype = $displaytype;
        $this->subType = ($subtype == -2 && !is_null($customsubtype)) ? $customsubtype : $subtype;
        $this->level = $level;
        $this->grading = $grading;
        
        $this->settings['setting_use_unit_percents'] = (isset($_POST['setting_use_unit_percents']));
        $this->settings['setting_ignore_auto_calcs'] = isset($_POST['setting_ignore_auto_calcs']);
        
        return true;
        
    }
    
    /**
     * Any additional loads that are required when doing student load information
     */
    public function qual_specific_student_load_information($studentID, $qualID)
    {
        
    }
    
    /**
	 * using the object insert into the database
	 * Dont forget to set the ID up for the object once inserted
	 */
	public function insert_qualification()
    {
        
        global $DB;
		//as each qual is different its easier to do this hear. 
		$dataobj = new stdClass();
		$dataobj->name = $this->name;
        $dataobj->additionalname = $this->additionalName;
		$dataobj->code = $this->code;
		$dataobj->credits = $this->credits;
        $dataobj->noyears = $this->noYears;
		$dataobj->bcgttargetqualid = 0;
        
		$id = $DB->insert_record("block_bcgt_qualification", $dataobj);
		$this->id = $id;
        
        $obj = new stdClass();
        $obj->bcgtqualid = $this->id;
        $obj->displaytype = $this->displaytype;
        $obj->subtype = $this->subType;
        $obj->level = $this->level;
        $obj->gradingstructureid = $this->grading;
        $DB->insert_record("block_bcgt_bespoke_qual", $obj);
        
        if (isset($this->settings))
        {
            foreach($this->settings as $setting => $value)
            {
                $settingObj = new stdClass();
                $settingObj->bcgtqualificationid = $this->id;
                $settingObj->attribute = $setting;
                $settingObj->value = $value;
                $DB->insert_record("block_bcgt_qual_attributes", $settingObj);
            }
        }
        
        
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_INSERTED_QUAL, null, $this->id, null, null, null);
	
        
        
        
    }
    
    public function load_settings()
    {
        
        global $DB;
        
        $settings = $DB->get_records_select("block_bcgt_qual_attributes", "bcgtqualificationid = ? AND userid IS NULL AND attribute LIKE 'setting_%'", array($this->id));
        if ($settings)
        {
            foreach($settings as $setting)
            {
                $this->settings[$setting->attribute] = $setting->value;
            }
        }
        
        return $this->settings;
        
    }
    
    public function get_setting($setting)
    {
        
        return (isset($this->settings[$setting])) ? $this->settings[$setting] : false;
        
    }
    
    public function has_unit_percentages()
    {
        $setting = $this->get_setting('setting_use_unit_percents');
        return ($setting == 1);
    }
	
	/***
	 * Deletes the qual
	 * For each type there maybe specific things we need to do
	 */
	public function delete_qualification()
    {
        
    }
	
	/**
	 * Updates the qual
	 * For each type there maybe specific things we need to do
	 */
	public function update_qualification()
    {
        
        global $DB;
        
		$dataobj = new stdClass();
		$dataobj->id = $this->id;
		$dataobj->name = $this->name;
        $dataobj->additionalname = $this->additionalName;
		$dataobj->code = $this->code;
        $dataobj->noyears = $this->noYears;
		$dataobj->credits = $this->credits;
		$DB->update_record("block_bcgt_qualification", $dataobj);
        
        
        $bespoke = $DB->get_record("block_bcgt_bespoke_qual", array("bcgtqualid" => $this->id));
        
        // Shouldn't ever be a bespoke qual without that record
        $obj = new stdClass();
        $obj->id = $bespoke->id;
        $obj->displaytype = $this->displaytype;
        $obj->subtype = $this->subType;
        $obj->level = $this->level;
        $obj->gradingstructureid = $this->grading;
        $DB->update_record("block_bcgt_bespoke_qual", $obj);
        
        
        // Wipe settings
        $DB->execute("DELETE FROM {block_bcgt_qual_attributes} 
                      WHERE bcgtqualificationid = ? AND userid IS NULL AND attribute LIKE 'setting_%'", array($this->id));
        
        
        // Insert again
        if (isset($this->settings))
        {
            foreach($this->settings as $setting => $value)
            {
                $obj = new stdClass();
                $obj->bcgtqualificationid = $this->id;
                $obj->attribute = $setting;
                $obj->value = $value;
                $DB->insert_record("block_bcgt_qual_attributes", $obj);
            }
        }
        
        
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_UPDATED_QUAL, null, $this->id, null, null, null);
	
        
        
        
        
    }
    
    /**
	 * Used to get the type specific title values and labels.
	 * E.g. for BTEC its 'Credits Required. '
     * This is called from edit_qual_units
     * and is for things like credits required, no units required ect
	 */
	public function get_type_qual_title()
    {
        return "";
    }
    
    /**
	 * So when adding or removing units from a qual.
	 * returns a string with fields for edit_qualification_units 
	 * for example, total credits for BTECs or total no of units or toal UMS
	 * This is used when the form comes up so that a user can 
	 * view things that are specific to the qual when adding units to quals. 
	 */
	public function get_unit_list_type_fields()
    {
        //total no of UMS
        return "";
    }
    
    /**
	 * Adds a unit to the qualification
	 * @param Unit $unit
	 */
	public function add_unit(Unit $unit)
    {
        return parent::add_unit_qual($unit);
    }
    
    /**
	 * Removes a unit from the qualification. 
	 * @param Unit $unit
	 */
	public function remove_unit(Unit $unit)
    {
        return parent::remove_units_qual($unit);
    }
    
    /**
     * 
     * @param type $studentView
     * @return type
     * Gets the total credits for the student 
     */
    public function get_students_total_credits()
	{
		$totalCredits = 0;
		foreach($this->units AS $unit)
		{
			if($unit->is_student_doing() || $unit->is_student_doing() == 'Yes')
			{
				$totalCredits = $totalCredits + $unit->get_credits();
			}
		}
		return $totalCredits;
	}
        
    
    public function get_current_total_credits()
	{
		//This gets the current total credits that are on the qualification
		//gets credits for all units on the qual. 
		$totalCredits = 0;
		foreach($this->units AS $unit)
		{
			$credits = 0;
			if($unit->get_credits())
			{
				$credits = $unit->get_credits();
			}
			$totalCredits = $totalCredits + $credits;
		}
		return $totalCredits;
	}
    
    /**
     * Multiple denotes if this will appear multiple times on a page. 
     * Gets the page and grid that is used in the edit students unit
     * page. 
     */
    public function get_edit_students_units_page($courseID = -1, $multiple = false, 
            $count = 1, $action = 'q')
    {
        
        
        global $OUTPUT, $DB, $PAGE, $CFG;
        $sAID = optional_param('sAID', -1, PARAM_INT);
        $heading = '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_students_units.php?a=q&qID='.$this->id.'">';
        $heading .= $this->get_display_name();
        $heading .= '<br />';
        if ($this->credits)
        {
            $heading .= ' ('.get_string('bteccredits','block_bcgt').': '.$this->get_credits().')';
        }
        $heading .= '</a>';
        if(!$multiple)
        {
            
            $jsModule = array(
                'name'     => 'mod_bcgtbtec',
                'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
                'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
            );
            $PAGE->requires->js_init_call('M.mod_bcgtbtec.initstudunits', null, true, $jsModule);
//            $PAGE->requires->js('/blocks/bcgt/js/block_bcgt_functions.js');
//            $PAGE->requires->js('/blocks/bcgt/js/jquery.dataTables.js');
//            $PAGE->requires->js('/blocks/bcgt/js/FixedColumns.js');
//            $PAGE->requires->js('/blocks/bcgt/js/FixedHeader.js'); 
        }
        
//        $sID = optional_param('sID', -1, PARAM_INT);
//        $uID = optional_param('uID', -1, PARAM_INT);
//        $sAID = optional_param('sAID', -1, PARAM_INT);
        $studentRole = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = ?', array('student'));
        $units = $this->get_units();
        $students = $this->get_users($studentRole->id, '', 'lastname ASC', $courseID);
        $out = html_writer::tag('h3', $heading, 
            array('class'=>'subformheading'));  
		$out .= '<input type="hidden" id="qID" name="qID" value="'.$this->id.'"/>';
        $out .= '<input type="hidden" id="a" name="a" value="'.$action.'"/>';
        $out .= '<input type="hidden" id="cID" name="cID" value="'.$courseID.'"/>';
        $out .= '<input type="submit" id="all'.$this->id.'" class="all" name="all" value="Select All">';
        $out .= '<input type="submit" id="none'.$this->id.'" class="none" name="none" value="Deselect All">';
        if(!$multiple)
        {
            $out .= '<input type="submit" name="save'.$this->id.'" value="Save">';
        }
        $out .= '<p class="totalPossibleCredits">Total Possible Unit Credits: '.$this->get_current_total_credits().'</p>';
        //TODO put this on the QUALIFICATION so it can be loaded through AJAX???
        if($units || $students)
        {
            $out .= '<table id="btecStudentUnits'.$count.'" class="btecStudentsUnitsTable" align="center"><thead><tr><th></th><th></th><th>Username</th><th>Name</th>';
            $out .= '<th>';
            $out .= get_string('bteccredits', 'block_bcgt');
            $out .= '</th>';
            $out .= '<th></th>';
            foreach($units AS $unit)
            {
                $out .= '<th>'.$unit->get_uniqueID().' : '.$unit->get_name().
                        ' : '.$unit->get_credits().' '.get_string('bteccredits', 'block_bcgt').
                        '</th>';
            }
            $out .= '</tr><tr><th></th><th></th><th></th><th></th><th></th><th></th>';
            foreach($units AS $unit)
            {
                $out .= '<th><a href="edit_students_units.php?qID='.$this->id.'&uID='.$unit->get_id().'" title="Select all Students for this Unit">'.
                        '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowdown.jpg"'. 
                        'width="25" height="25" class="unitsColumn" id="q'.$this->id.'u'.$unit->get_id().'"/>'.
                        '</a></th>';
            }
            $out .= '</tr></thead><tbody>';
            $forceChecked = false;
            $forceUnChecked = false;
            if(isset($_POST['none']))
            {
                $forceUnChecked = true;
            }
            elseif(isset($_POST['all']))
            {
                $forceChecked = true;
            }
            if(!isset($this->usersstudent))
            {
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
                //load the users and load their qual objects
                $this->load_users('student', true, 
                        $loadParams, $courseID);
            }
            if(isset($this->usersstudent))
            {
                foreach($this->usersstudent AS $student)
                {
                    $studentQual = $this->usersQualsstudent[$student->id];
                    if($studentQual)
                    {
                        if($forceChecked)
                        {
                            $studentQual->add_student_to_all_units();
                        }
                        elseif($forceUnChecked)
                        {
                            $studentQual->remove_student_from_all_units();
                        }
                        //GETS all of the units, not just the students units. 
                        //but has in them if student is doing it or not
                        $studentsUnits = $studentQual->get_units();
                    }
                    $out .= '<tr>';
                    $out .= '<td>'.
                            '<a href="edit_students_units.php?qID='.$this->id.'&sAID='.
                            $student->id.'" id="chq'.$this->id.'s'.$student->id.'" '.
                            'title="Copy this student selection to all in this grid">'.
                            '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/infinity.jpg"'. 
                            'width="25" height="25" class="studentAll" id="chq'.$this->id.'s'.$student->id.'"/>'.
                            '</td>';
                    $out .= '<td>'.$OUTPUT->user_picture($student, array(1)).'</td>';
                    $out .= '<td>'.$student->username.'</td>';
                    $out .= '<td>'.$student->firstname.' '.$student->lastname.'</td>';
                    $out .= '<td>';
                    $out .= $studentQual->get_students_total_credits();
                    $out .= '</td>';
                    $out .= '<td><a href="edit_students_units.php?qID='.$this->id.'&sID='.$student->id.'" title="Select all Units for this Student">'.
                            '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowright.jpg"'. 
                            'width="25" height="25" class="studentRow" id="q'.$this->id.'s'.$student->id.'"/>'.
                            '</a></td>';
                    foreach($studentsUnits AS $unit)
                    {
                        $checked = '';
                        if($forceUnChecked)
                        {
                            $checked = '';
                        }
                        elseif($forceChecked || ($unit->is_student_doing() || $unit->is_student_doing() == 'Yes'))
                        {
                            $checked = 'checked="checked"';
                        }
                        $name='q'.$this->id.'S'.$student->id.'U'.$unit->get_id().'';
                        $out .= '<td><input id="chs'.$student->id.'q'.$this->id.'u'.$unit->get_id().'" class="eSU'.$this->id.' chq'.$this->id.'s'.$student->id.' chq'.$this->id.'u'.$unit->get_id().'" type="checkbox" '.$checked.' name="'.$name.'"/></td>';
                    }
                    $out .= '</tr>';
                }
                $out .= '</tbody></table>'; 
            }
            else
            {
                $out .= '</tbody></table>';
                $out .= '<p>This Qualification has no Students attached</p>';
            }
        }
        else
        {
            $out .= '<p>There are currently no Students or Units on this Qualification</p>';
        }
        
        return $out;
        
        
        
    }
    
    
    
    
    /**
     * gets the javascript initialisation call
     */
    public function get_edit_student_page_init_call()
    {
        //this depends on the number of tables shown
        return "";
    }
    
    /**
	 * Does the qual have a final grade?
	 * E.g. Alevels or BTECS or are they just pass/fail
	 */
	public function has_final_grade()
    {
        return true;
    }
    
    
    /**
	 * Gets the final grade from the database
	 */
	public function retrieve_student_award()
	{
		$awards = $this->get_students_qual_award();
		if($awards)
		{
            $retval = new stdClass();
            foreach($awards AS $award)
            {
                $params = new stdClass();
                $params->award = $award->grade;
                $params->type = $award->type;
                $qualAward = new QualificationAward($award->gradeid, $params);
                $retval->{$award->type} = $qualAward;
            }
            return $retval;
		}
		return false;
	}
    
     /**
	 * Calculate the predicted grade, based on the unit awards so far
	 */
	public function calculate_predicted_grade()
    {
        
        global $CFG, $DB;
        
        require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/lib.php';
        require_bespoke();
        
        // Loop through units. If each unit has an award, work out an avg of them for the final qual
        $cntAward = 0;
        $totalUnits = count($this->units);
        $awardArray = array();
        
        if(!$this->units) return false;
        
        foreach($this->units as $unit)
        {
            
            if(!$unit->is_student_doing()) continue;
            
            $unitAward = $unit->get_user_award();

            if($unitAward && $unitAward->get_id() > 0)
            {

                if($unitAward->get_rank() > 0)
                {
                    // If weighting is 0, don't bother adding
                    if ($unit->get_weighting() > 0){                        
                        $cntAward++;
                        $ranking = $DB->get_record("block_bcgt_bspk_u_grade_vals", array("id" => $unitAward->get_id()), "id, points");
                        $awardArray[] = array("value" => $unitAward->get_award(), "weighting" => $unit->get_weighting(), "points" => $ranking->points);
                    } else {
                        $totalUnits--;
                    }
                    
                }
            }
            
        }
        
        $award = $this->calculate_predicted_score($awardArray, $totalUnits);
        
        if($award)
        {
            $params = new stdClass();
            $params->award = $award->grade;
            $params->type = 'Predicted';
            $qualAward = new BespokeQualificationAward($award->id, $params);
            $this->update_qualification_award($qualAward);
            return $qualAward;
        }    
        else
        {
            $params = new stdClass();
            $params->award = 'N/A';
            $params->type = 'Predicted';
            $qualAward = new BespokeQualificationAward(-1, $params);
            return $qualAward;
            $this->delete_qualification_award('Predicted');
        }
        
        return $award;       
        
        
    }
    
    /**
	 * What is the final grade based on all the unit awards (assuming all units are complete)
	 */
	public function calculate_final_grade()
    {
       
        global $CFG, $DB;
        
        require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/lib.php';
        require_bespoke();
        
        // Loop through units. If each unit has an award, work out an avg of them for the final qual
        $cntAward = 0;
        $totalUnits = count($this->units);
        $awardArray = array();
        
        if(!$this->units) return false;
        
        foreach($this->units as $unit)
        {
            
            if(!$unit->is_student_doing()) continue;
            
            $unitAward = $unit->get_user_award();

            if($unitAward && $unitAward->get_id() > 0)
            {

                if($unitAward->get_rank() > 0)
                {
                    // If weighting is 0, don't bother adding
                    if ($unit->get_weighting() > 0){                        
                        $cntAward++;
                        $ranking = $DB->get_record("block_bcgt_bspk_u_grade_vals", array("id" => $unitAward->get_id()), "id, points");
                        $awardArray[] = array("value" => $unitAward->get_award(), "weighting" => $unit->get_weighting(), "points" => $ranking->points);
                    } else {
                        $totalUnits--;
                    }
                    
                }
            }
            
        }
                        
        // If all units have an award
        if($cntAward == $totalUnits && $totalUnits > 0)
        {
            
            // Work out the qualification award
            $award = $this->calculate_average_score($awardArray, $totalUnits);
            
            if($award)
            {
                $params = new stdClass();
                $params->award = $award->grade;
                $params->type = 'Final';
                $qualAward = new BespokeQualificationAward($award->id, $params);
                $this->update_qualification_award($qualAward);
                return $qualAward;
            }                
            
            
        }
        else
        {
            $this->delete_qualification_award('Final');
            $params = new stdClass();
            $params->award = '-';
            $params->type = 'Final';
            $qualAward = new BespokeQualificationAward(-1, $params);
            return $qualAward;
        }
        
    }
    
    protected function calculate_predicted_score($awardArray, $totalUnits)
    {
        
        global $DB;
        
        $totalAwarded = count($awardArray);
        $totalScore = 0;
        
        if ($awardArray)
        {
            foreach($awardArray as $award)
            {
                $totalScore += ( ($award['weighting'] * $award['points']) );
                if ($award['weighting'] > 1)
                {
                    $totalUnits += ( $award['weighting'] - 1 );
                    $totalAwarded += ( $award['weighting'] - 1 );
                }
            }
        }
        
        if ($totalAwarded == 0) $avgScore = 0;
        else $avgScore = round($totalScore / $totalAwarded, 1);
        
        $awards = $DB->get_records("block_bcgt_bspk_q_grade_vals", array("qualgradingid" => $this->grading), "rangelower ASC");
        $records = array();

        if ($awards)
        {
            foreach($awards as $award)
            {
                $obj = new stdClass();
                $obj->id = $award->id;
                $obj->grade = $award->grade;
                $obj->rangelower = $award->rangelower;
                $obj->rangeupper = $award->rangeupper;
                $records[$award->grade] = $obj;
            }
        }
        
        // Now work out where in the points boundaries it lies
        foreach($records as $record)
        {
            if($avgScore >= $record->rangelower && $avgScore <= $record->rangeupper)
            {
                return $record;
            }
        }
        
        return false; // Something went quite wrong
        
        
    }
    
    /**
     * Calculate the average score of a qual, based on unit awards, their weighting, their points and their point boundaries
     * @global type $CFG
     * @param type $awardArray
     * @param type $totalUnits
     * @return boolean
     */
    protected function calculate_average_score($awardArray, $totalUnits)
    {

        global $DB;

        // Get the point boundaries and awards that are possible for this qual
        $awards = $DB->get_records("block_bcgt_bspk_q_grade_vals", array("qualgradingid" => $this->grading), "rangelower ASC");
        $records = array();

        if ($awards)
        {
            foreach($awards as $award)
            {
                $obj = new stdClass();
                $obj->id = $award->id;
                $obj->grade = $award->grade;
                $obj->rangelower = $award->rangelower;
                $obj->rangeupper = $award->rangeupper;
                $records[$award->grade] = $obj;
            }
        }

        $totalUnits = 0;
        $totalScore = 0;

        foreach($awardArray as $award)
        {
            // Get the points ranking of this award and add to total
            // Also take into account different criteria weightings
            $weight = $award['weighting'];
            $rank = $award['points'];
            $totalUnits += $weight;
            $totalScore += ( ($rank * $weight) );
        }
        
        $avgScore = round($totalScore / $totalUnits, 1);
                
        // Now work out where in the points boundaries it lies
        foreach($records as $record)
        {
            if($avgScore >= $record->rangelower && $avgScore <= $record->rangeupper)
            {
                return $record;
            }
        }
        
        return false; // Something went quite wrong

    }   
    
    /**
     * Gets the students qualification award from the database
     * @return Found
     */
    protected function get_students_qual_award($awardType = null)
    {
        global $DB;
                
        $sql = "SELECT useraward.id as id, useraward.type, grade.id AS gradeid, grade.grade 
                FROM {block_bcgt_user_award} AS useraward 
                INNER JOIN {block_bcgt_bspk_q_grade_vals} AS grade ON grade.id = useraward.bcgtbreakdownid
                WHERE useraward.bcgtqualificationid = ?
                AND useraward.userid = ?";
        $params = array($this->id, $this->studentID);
        if($awardType)
        {
            $sql .= " AND useraward.type = ?";
            $params[] = $awardType;
        }
        
        return $DB->get_records_sql($sql, $params);
    }
    
    
    public function update_qualification_award($award)
    {
        
        global $DB;
        
        // Log
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_UPDATED_QUAL_AWARD, $this->studentID, $this->id, null, null, $award->get_id());
        
        $obj = new stdClass();
        $obj->bcgtqualificationid = $this->id;
        $obj->userid = $this->studentID;
        $obj->bcgtbreakdownid = $award->get_id();
        $obj->type = $award->get_type();
        $obj->dateupdated = time();
        $obj->warning = '';
        
        //lets find out if the user has one inserted before?
        $awards = $this->get_students_qual_award($award->get_type());
		if($awards)
		{
            foreach($awards AS $award)
            {
                $id = $award->id;
                $obj->id = $id;
                return $DB->update_record('block_bcgt_user_award', $obj);
            }
			
		}
        else
        {
            return $DB->insert_record('block_bcgt_user_award', $obj);
        }
        
    }
    
    
    
   
    
    //some quals have criteria just on the qual like alevels. 
	//each qual migt store this differently.
	public function load_qual_criteria_student_info($studentID, $qualID)
    {
        return false;
    }
    
    /**
     * This processes the students units selection
     * Loops over all of the students,and their units
     * checks if its been checked now and before 
     * updates accordingly
     * saves
     */
    public function process_edit_students_units_page($courseID = -1)
    {
        //loop over all of the students
            //load the students qual
            //add/remove the units
            //then save
        if(isset($_POST['saveAll']) || isset($_POST['save'.$this->id]))
        {
            if(!isset($this->usersstudent))
            {
                //load the users and load their qual objects
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
                $this->load_users('student', true, 
                        $loadParams, $courseID);
            }
            foreach($this->usersstudent AS $student)
            {
                $studentQual = $this->usersQualsstudent[$student->id];
                if($studentQual)
                {
                    foreach($studentQual->get_units() AS $unit)
                    {
                        //get the check boxes
                        //name is in the format of $name='s'.$student->id.'U'.$unit->get_id().'';
                        $fieldToCheck = 'q'.$this->id.'S'.$student->id.'U'.$unit->get_id().'';
                        $this->process_edit_students_units($unit, $fieldToCheck, $student->id);
                    }
                }
            }
        }
        //then we get rid of the session variable.
        $_SESSION['new_students'] = urlencode(serialize(array()));
        $_SESSION['new_quals'] = urlencode(serialize(array()));
    }
    
    
    /**
     * For the unit passes in it checks to see if the 'field' has been checked or
     * not checked and updates the database
     * Basically the check boxes will be to denote of the student is doing the
     * unit or not. 
     * @param type $unit
     * @param type $fieldToCheck
     * @param type $studentID
     */
    protected function process_edit_students_units($unit, $fieldToCheck, $studentID)
    {
        if(isset($_POST[$fieldToCheck]))
        {
            //so its been checked now. was it before?
            if(!$unit->is_student_doing() && $unit->is_student_doing() != 'Yes')
            {
                $unit->set_is_student_doing(true);
                $unit->insert_student_on_unit($this->id, $studentID);
            }
        }
        else
        {
            //so it isnt checked/ Was it before?
            if($unit->is_student_doing() || $unit->is_student_doing() == 'Yes')
            {
                $unit->set_is_student_doing(false);
                $unit->delete_student_on_unit_no_id($studentID, $this->id);
            } 
        }
    }
    
    /**
     * Gets a single row of abiliy to select a students units 
     * on the qualification.
     */
    public function get_edit_single_student_units($currentCount)
    {
        
    }
    
    /**
     * Gets the initialisation call of each functtion for dealing 
     * with the edit students units when we are looking at one single student
     * per qualification. 
     * In the previous screen the user may have added the student(s) to qualifications
     * of a different type so will need different behaviour. 
     */
    public function get_edit_single_student_units_init_call()
    {
        
    }
    
    /**
     * Gets the page where the settings for the qualification can be set.
     */
    public function get_qual_settings_page()
    {
        
    }
    
    protected function get_recursive_sub_criteria_names($critName, $level = 1)
    {
        
        global $CFG;
        
        require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeCriteriaSorter.class.php';
        $criteriaSorter = new BespokeCriteriaSorter(); 
        
        
        $array = array();
        
        if ($this->units)
        {
            
            foreach($this->units as $unit)
            {
             
                if ($unit->get_criteria())
                {
                    
                    foreach($unit->get_criteria() as $criterion)
                    {
                        
                        if ($criterion->get_name() == $critName && $level == 1)
                        {
                            
                            if ($criterion->get_sub_criteria())
                            {
                                
                                foreach($criterion->get_sub_criteria() as $subCriterion)
                                {
                                    
                                    $array[$subCriterion->get_name()] = $this->get_recursive_sub_criteria_names( $subCriterion->get_name(), 2 );
                                    
                                }
                                
                            }
                            
                        }
                        else
                        {
                            
                            if ($criterion->get_sub_criteria())
                            {
                                
                                foreach($criterion->get_sub_criteria() as $subCriterion)
                                {
                                    
                                    if ($subCriterion->get_name() == $critName && $level == 2)
                                    {
                                    
                                        if ($subCriterion->get_sub_criteria())
                                        {

                                            foreach($subCriterion->get_sub_criteria() as $subSubCriterion)
                                            {

                                                $array[$subSubCriterion->get_name()] = true; # Max level we support is 2, until I can work out a good way of doing this recursively

                                            }

                                        }

                                    }
                                    
                                }
                                
                            }
                            
                        }
                        
                    }
                    
                }
                
            }
            
        }
        
        uksort($array, array($criteriaSorter, "ComparisonSimple"));

        return ($array) ? $array : true;
        
    }
    
    protected function get_used_criteria_names()
    {
        
        global $CFG;
        
        require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeCriteriaSorter.class.php';
        $criteriaSorter = new BespokeCriteriaSorter(); 
                        
        $criteriaArray = array();
        
        // Top Level criteria
        if ($this->units)
        {
        
            foreach($this->units as $unit)
            {
            
                if ($unit->get_criteria())
                {
                    foreach($unit->get_criteria() as $crit)
                    {
                        $criteriaArray[$crit->get_name()] = $this->get_recursive_sub_criteria_names( $crit->get_name() );
                    }
                }
        
            }
        
        }
                
        uksort($criteriaArray, array($criteriaSorter, "ComparisonSimple"));
                
        $this->usedCriteriaNames = bcgt_flatten_by_keys($criteriaArray);
        return $this->usedCriteriaNames;
        
    }
    
    
    protected function get_possible_grid_values($criteria = false, &$array = false)
    {
        
        global $DB;
        
        if ($criteria && $array)
        {
            
            foreach($criteria as $criterion)
            {
                
                $array[] = $criterion->get_grading();
                        
                if ($criterion->get_sub_criteria())
                {
                    $this->get_possible_grid_values($criterion->get_sub_criteria(), $array);
                }
                
            }
            
            return true;
            
        }
        
        
        $array = array();
        
        if ($this->units)
        {
            
            foreach($this->units as $unit)
            {
                
                if ($unit->get_criteria())
                {
                    
                    foreach($unit->get_criteria() as $criterion)
                    {
                        
                        $array[] = $criterion->get_grading();
                        
                        if ($criterion->get_sub_criteria())
                        {
                            $this->get_possible_grid_values($criterion->get_sub_criteria(), $array);
                        }
                        
                    }
                    
                }
                
            }
            
        }
        
        $gradingIDs = array_unique($array);
        $gradingIDs = implode(",", $gradingIDs);
        
        return $DB->get_records_select("block_bcgt_bspk_c_grade_vals", "critgradingid IN ({$gradingIDs}) OR critgradingid IS NULL", null, "met DESC, points ASC, rangelower ASC, grade ASC");
        
        
    }
    
    
    public function get_grid_key()
    {
        
        global $CFG;
        
        $output = "";
        
        $possibleGridValues = $this->get_possible_grid_values();
        $width = 100 / (count($possibleGridValues) + 1);

        $output .= "<div id='bespokeGridKey'>";

            $output .= "<table>";

                $output .= "<tr>";
                    $output .= "<th colspan='".(count($possibleGridValues) + 1)."'>".get_string('gridkey', 'block_bcgt')."</th>";
                $output .= "</tr>";

                $output .= "<tr class='imgs'>";

                if ($possibleGridValues)
                {
                    foreach($possibleGridValues as $possible)
                    {
                        $image = BespokeQualification::get_grid_image($possible->shortgrade, $possible->grade, $possible);
                        if ($image)
                        {
                            $output .= "<td style='width:{$width}%;'><img src='{$image->image}' alt='{$image->title}' class='{$image->class}' /></td>";
                        }
                    }
                    //$output .= "<td style='width:{$width}%;'><img src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/grid_symbols/icon_NoIcon.png' alt='".get_string('missingiconimg', 'block_bcgt')."' /></td>";
                }

                $output .= "</tr>";


                $output .= "<tr class='names'>";

                if ($possibleGridValues)
                {
                    foreach($possibleGridValues as $possible)
                    {
                        $output .= "<td style='width:{$width}%;'>{$possible->grade}</td>";
                    }
                    //$output .= "<td style='width:{$width}%;'>".get_string('missingiconimg', 'block_bcgt')."</td>";
                }

                $output .= "</tr>";


            $output .= "</table>";

        $output .= "</div>";

        return $output;
        
    }
    
    public function call_display_student_grid_external($from = false)
    {
        return $this->display_student_grid(false, true, true, $from);
    }
    
        /**
     * Displays the Grid
     */
    public function display_student_grid($fullGridView = true, $studentView = true, $basicView = false, $from = false)
    {
        
        global $CFG, $PAGE, $OUTPUT, $COURSE;
                
        $output = "";
        
        $grid = optional_param('g', 'v', PARAM_TEXT);
        $courseID = optional_param('cID', -1, PARAM_INT);
        
        if ($grid == 's') $grid = 'v';
        
        $errors = $this->check_grading_structures_compatability();
        if ($errors)
        {
            
            $output .= "<div class='errorsDiv'>";
            $output .= "<h2 class='c'>".get_string('compatabilityerrors', 'block_bcgt')."</h2><br>";
            
                foreach($errors as $error)
                {
                    $output .= $error . "<br><br>";
                }
            
            $output .= "</div>";
            
            return $output;
            
        }
        
        
        
        if($courseID > 1)
        {
            $context = context_course::instance($courseID);
        }
        else
        {
            $context = context_course::instance($COURSE->id);
        }
        
        // Only allow editing if we have the capability
        if ($grid == 'e' && !has_capability('block/bcgt:editstudentgrid', $context)){
            $grid = 'v';
        }
        
        $editing = ($grid == 'e');
                
        $output .= load_javascript(true, true);
        $output .= load_css(true, true);
        
        $criteriaNames = $this->get_used_criteria_names();
        
        $jsModule = array(
            'name'     => 'mod_bcgtbespoke',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbespoke/js/bcgtbespoke.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
                
        $freezeCols = 3;
        if ($this->has_unit_percentages()){
            $freezeCols++;
        }
        
        if ($basicView){
            $output .= <<< JS
            <script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/js/bcgtbespoke.js'></script>
JS;
        } else {
            $PAGE->requires->js_init_call('M.mod_bcgtbespoke.initstudentgrid', array($this->id, $this->studentID, $grid, $freezeCols), true, $jsModule);
        }
        
        
        if ($basicView && $from != 'portal')
        {
            $output .= "<p class='c'><a href='".$CFG->wwwroot."/blocks/bcgt/grids/print_grid.php?sID={$this->studentID}&qID={$this->id}' target='_blank'><img src='".$OUTPUT->pix_url('t/print', 'core')."' alt='' /> ".get_string('printgrid', 'block_bcgt')."</a> &nbsp;&nbsp;&nbsp;&nbsp; <a href='".$CFG->wwwroot."/blocks/bcgt/grids/print_report.php?sID={$this->studentID}&qID={$this->id}' target='_blank'><img src='".$OUTPUT->pix_url('t/print', 'core')."' alt='' /> ".get_string('printreport', 'block_bcgt')."</a></p>";
        }
        
        // We don't want the buttons when looking at it in something like the ELBP
        if (!$basicView)
        {
        
            $output .= "<div class='c'>";

                $output .= "<a href='".$CFG->wwwroot."/blocks/bcgt/grids/student_grid.php?sID={$this->studentID}&qID={$this->id}&g=v'><input type='button' class='btn' value='".get_string('view', 'block_bcgt')."' /></a>";
                $output .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                if (has_capability('block/bcgt:editstudentgrid', $context)){
                    $output .= "<a href='".$CFG->wwwroot."/blocks/bcgt/grids/student_grid.php?sID={$this->studentID}&qID={$this->id}&g=e'><input type='button' class='btn' value='".get_string('edit', 'block_bcgt')."' /></a>";
                }

                if ($editing)
                {
                    $output .= "<br><br>";
                    $output .= "<a href='#' onclick='toggleAddComments();return false;'><input id='toggleCommentsButton' type='button' class='btn' value='".get_string('addcomment', 'block_bcgt')."' /></a>";
                }

            $output .= "</div>";
        
        }
        
        if ($grid == 'v')
        {
            
            $output .= $this->get_grid_key();
        
        }
        
        $output .= "<br><br>";
        $output .= "<p id='loading' class='c'><img src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/loader.gif' alt='loading...' /></p>";
        
        
        if ($this->grading)
        {
            $output .= '<table class="bespokeSummaryAwardGrades">';
                if ($this->use_auto_calculations())
                {
                    $output .= $this->show_predicted_qual_award($this->predictedAward, $context);
                }
                $output .= $this->show_final_qual_award($this->studentAward, $context, $editing);
                
                // Target grade
                $output .= $this->show_target_grade();
                
                // Aspirational grade
                $output .= $this->show_aspirational_grade();
                
            $output .= '</table>';
        }
                
        
        $output .= "<div id='bespokeStudentGrid'>";
        
            $output .= "<table id='bespokeStudentGridTable'>";
                $output .= "<thead>";
                $output .= "<tr>";
                    $output .= "<th style='width:40px;min-width:40px;'></th>";
                    $output .= "<th class='unit_name'>".get_string('unit', 'block_bcgt')."</th>";
                    $output .= "<th>".get_string('award', 'block_bcgt')."</th>";
                    
                    if ($this->has_unit_percentages())
                    {
                        $output .= "<th>".get_string('percentcomplete', 'block_bcgt')."</th>";
                    }
                    
                    if ($criteriaNames)
                    {
                        foreach($criteriaNames as $name)
                        {
                            $output .= "<th style='width:40px;min-width:40px;'>{$name}</th>";
                        }
                    }
                    
                $output .= "</tr>";
                $output .= "</thead>";
                
                $output .= "<tbody>";
                
                
                if ($this->units)
                {
                    
                    foreach($this->units as $unit)
                    {
                        
                        if ( $unit->is_student_doing() )
                        {
                            
                            $comments = $unit->get_comments();
                            $award = '-';
                            $unitAward = $unit->get_user_award();   
                            if($unitAward)
                            {
                                $award = $unitAward->get_award();
                            }
                            
                            if ($grid == 'e')
                            {
                                $award = "<select id='unitAwardSelect_U{$unit->get_id()}_Q{$this->id}_S{$this->studentID}' class='unitAwardSelect' grid='student' qualID='{$this->id}' unitID='{$unit->get_id()}' studentID='{$this->studentID}'>";
                                    $award .= "<option value=''></option>";
                                    foreach($unit->get_possible_awards() as $possibleAward)
                                    {
                                        $sel = ($unitAward && $unitAward->get_id() == $possibleAward->id) ? 'selected' : '';
                                        $award .= "<option value='{$possibleAward->id}' {$sel}>{$possibleAward->shortgrade} - {$possibleAward->grade}</option>";
                                    }
                                $award .= "</select>";
                            }
                            
                            
                            $output .= "<tr>";
                            
                                $class = (!empty($comments)) ? 'hasComments' : '';

                                $output .= "<td class='{$class}'>";
                                
                                    $output .= "<div class='criteriaTDContent'>";
                                
                                        if(has_capability('block/bcgt:editunit', $context)){
                                            //$output .= "<a href='{$CFG->wwwroot}/blocks/bcgt/grids/unit_grid.php?uID={$unit->get_id()}&qID={$this->id}' target='_blank' title='View Unit Grid'><img src='".$OUTPUT->pix_url('i/calendar', 'core')."' /></a><br>";
                                            $output .= "<a href='{$CFG->wwwroot}/blocks/bcgt/forms/edit_unit.php?unitID={$unit->get_id()}' target='_blank' title='Edit Unit'><img src='".$OUTPUT->pix_url('t/editstring', 'core')."' /></a>";
                                        }
                                    
                                    $output .= "</div>";
                                    
                                    $output .= "<div class='hiddenCriteriaCommentButton'>";
                                    
                                        $username = $this->student->username;
                                        $fullname = fullname($this->student);
                                        $unitname = bcgt_html($unit->get_name());
                                        $critname = "N/A";
                                        $cellID = "cmtCell_U_{$unit->get_id()}_S_{$this->studentID}_Q_{$this->id}";

                                        if (!empty($comments))
                                        {
                                            $output .= "<img id='{$cellID}' criteriaid='-1' unitid='{$unit->get_id()}' studentid='{$this->studentID}' qualid='{$this->id}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='student' class='editCommentsUnit' title='Click to Edit Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/comment_edit.png' alt='".get_string('editcomments', 'block_bcgt')."' />";
                                        }
                                        else
                                        {
                                            $output .= "<img id='{$cellID}' criteriaid='-1' unitid='{$unit->get_id()}' studentid='{$this->studentID}' qualid='{$this->id}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='student' class='addCommentsUnit' title='Click to Add Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/comment_add.png' alt='".get_string('addcomment', 'block_bcgt')."' />";
                                        }

                                        //$output .= "<span class='tooltipContent' style='display:none !important;'>".bcgt_html($this->comments, true)."</span>";
                                        $output .= "<div class='popUpDiv bcgt_unit_comments_dialog' id='dialog_S{$this->studentID}_U{$unit->get_id()}_Q{$this->id}' qualID='{$this->id}' unitID='{$unit->get_id()}' critID='-1' studentID='{$this->studentID}' grid='student' imgID='{$cellID}' title='Comments'>";
                                            $output .= "<span class='commentUserSpan'>Comments for {$fullname} : {$username}</span><br>";
                                            $output .= "<span class='commentUnitSpan'>{$unit->get_display_name()}</span><br>";
                                            $output .= "<span class='commentCriteriaSpan'>N/A</span><br><br><br>";
                                            $output .= "<textarea class='dialogCommentText' id='text_S{$this->studentID}_U{$unit->get_id()}_Q{$this->id}'>".bcgt_html($comments)."</textarea>";
                                        $output .= "</div>";

                                    
                                    $output .= "</div>";
                                    
                                $output .= "</td>";
                                
                                $output .= "<td class='unitName {$class}' title=''>";
                                    $output .= "<a href='{$CFG->wwwroot}/blocks/bcgt/grids/unit_grid.php?uID={$unit->get_id()}&qID={$this->id}' target='_blank' title='View Unit Grid'>".$unit->get_name()."</a>";
                                    $output .= "<div class='unitDetailsTooltip'>{$unit->build_tooltip_content($this)}</div>";
                                $output .= "</td>";
                                
                                
                                $output .= "<td id='unitAward_U{$unit->get_id()}_Q{$this->id}_S{$this->studentID}'>{$award}</td>";
                                
                                if ($this->has_unit_percentages())
                                {
                                    $output .= "<td id='percentComplete_U{$unit->get_id()}_Q{$this->id}_S{$this->studentID}'>".$unit->display_percentage_completed()."</td>";
                                }
                                
                                if ($criteriaNames)
                                {
                                    foreach($criteriaNames as $name)
                                    {
                                        
                                        $studentCriteria = $unit->get_single_criteria(-1, $name);
                                        
                                        if ($studentCriteria)
                                        {
                                            $output .= $studentCriteria->get_td('student', $editing, $this->student, $this, $unit);
                                        }
                                        else
                                        {
                                            $output .= "<td class='val'></td>";
                                        }
                                        
                                    }
                                }

                            $output .= "</tr>";
                        
                        }
                        
                    }
                    
                }
                else
                {
                    $colspan = count($criteriaNames) + 3;
                    $output .= "<tr><td colspan='{$colspan}'>".get_string('nounits', 'block_bcgt')."</td></tr>";
                }
                
                
                $output .= "</tbody>";
                
                $output .= "<tfoot></tfoot>";
                
            $output .= "</table>";
        
        
        $output .= "</div>";
        
        $output .= "<br>";
        
        if ($this->grading)
        {
            $output .= "<br>";
            $output .= '<table class="bespokeSummaryAwardGrades">';
                if ($this->use_auto_calculations())
                {
                    $output .= $this->show_predicted_qual_award($this->predictedAward, $context);
                }
                $output .= $this->show_final_qual_award($this->studentAward, $context, $editing);
                
                // Target grade
                $output .= $this->show_target_grade();
                
                // Aspirational grade
                $output .= $this->show_aspirational_grade();
                
            $output .= '</table>';
        }
        
        
        if ($basicView){
            $output .= " <script>$(document).ready( function(){
                M.mod_bcgtbespoke.initstudentgrid(null, '{$this->id}', '{$this->studentID}', '{$grid}', '{$freezeCols}');
            } ); </script> ";
        }
        
        
        
        return $output;
        
    }
    
    
    protected function show_aspirational_grade()
    {
        
        $retval = "";
        
        $gradeObjs = bcgt_get_aspirational_target_grade($this->studentID, $this->id);
        
        if($gradeObjs)
        {
            $gradeObj = end($gradeObjs);
            $retval .= "<tr class='award_row'>";
            $retval .= "<td>".get_string('asptargetgrade', 'block_bcgt')."</td>";
            $retval .= "<td>";
            $retval .= $gradeObj->grade;
            $retval .= "</td>";
            $retval .= "<td>".get_string('asptargetgrade:desc', 'block_bcgt')."</td>";
            $retval .= "</tr>";
        }
        
        return $retval;
        
    }
    
    protected function show_target_grade()
    {
        
        $retval = "";
        
        $userCourseTarget = new UserCourseTarget();
        $targetGrade = $userCourseTarget->retrieve_users_target_grades($this->studentID, $this->id);
        if($targetGrade)
        {
            $targetGradeObj = $targetGrade[$this->id];
            if($targetGradeObj)
            {
                if (isset($targetGradeObj->id) && $targetGradeObj->id > 0)
                {
                    $grade = $targetGradeObj->grade;
                    $retval .= "<tr class='award_row'><td>".get_string('targetgrade', 'block_bcgt')."</td><td>{$grade}</td><td>".get_string('targetgrade:desc', 'block_bcgt')."</td></tr>";
                }
            }
            
        }
        
        return $retval;
        
    }
    
    
    public function print_grid(){
        
        global $CFG, $COURSE;
        
        echo "<!doctype html><html><head>";
        echo "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/print.css'>";
        $logo = get_config('bcgt', 'logoimgurl');
        
        $criteriaNames = $this->get_used_criteria_names();
        
        echo "</head><body style='background: url(\"{$logo}\") no-repeat;'>";
                
        echo "<div class='c'>";
            echo "<h1>{$this->get_display_name()}</h1>";
            echo "<h2>".fullname($this->student)." ({$this->student->username})</h2>";

            echo "<br>";
            
            // Key
            echo "<div id='key'>";
                echo $this->get_grid_key();
            echo "</div>";
            
            echo "<br><br>";
            
            echo "<table id='printGridTable'>";
            echo "<tr>";

            echo "<th></th>";
            echo "<th class='unit_name'>".get_string('unit', 'block_bcgt')."</th>";
            echo "<th>".get_string('award', 'block_bcgt')."</th>";

            if ($this->has_unit_percentages())
            {
                echo "<th>".get_string('percentcomplete', 'block_bcgt')."</th>";
            }

            if ($criteriaNames)
            {
                foreach($criteriaNames as $name)
                {
                    echo "<th>{$name}</th>";
                }
            }

            echo "</tr>";
            

            
            foreach($this->units AS $unit)
            {

                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELALL;
                $loadParams->loadAward = true;
                $unit->load_student_information($this->student->id, $this->id, $loadParams);
                
                if($unit->is_student_doing())
                {	

                    echo "<tr>";
                    
                        $award = '-';
                        $unitAward = $unit->get_user_award();   
                        if($unitAward)
                        {
                            $award = $unitAward->get_award();
                        }


                        echo "<td></td>";

                        echo "<td class='unitName' title=''>";
                            echo $unit->get_name();
                        echo "</td>";


                        echo "<td id='unitAward_U{$unit->get_id()}_Q{$this->id}_S{$this->studentID}'>{$award}</td>";

                        if ($this->has_unit_percentages())
                        {
                            echo "<td id='percentComplete_U{$unit->get_id()}_Q{$this->id}_S{$this->studentID}'>".$unit->display_percentage_completed()."</td>";
                        }
                        
                        
                        if ($criteriaNames)
                        {
                            foreach($criteriaNames as $name)
                            {

                                $studentCriteria = $unit->get_single_criteria(-1, $name);

                                if ($studentCriteria)
                                {
                                    echo $studentCriteria->get_td('student', false, $this->student, $this, $unit);
                                }
                                else
                                {
                                    echo "<td></td>";
                                }

                            }
                        }
                        
                    
                    echo "</tr>";
                    
                }

            }
            
            echo "</table>";
            echo "</div>";
            
            
            $context = context_course::instance($COURSE->id);
            
            if ($this->grading)
            {
                echo '<table class="bespokeSummaryAwardGrades">';
                    echo $this->show_predicted_qual_award($this->predictedAward, $context);
                    echo $this->show_final_qual_award($this->studentAward, $context);
                echo '</table>';
            }
            
            
            //echo "<br class='page_break'>";
            
            // Comments and stuff
            // TODO at some point
            
        
        echo "</body></html>";
        
    }
    
    public function print_report(){
        
        global $CFG, $COURSE;
        
        echo "<!doctype html><html><head>";
        echo "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/print.css'>";
        $logo = get_config('bcgt', 'logoimgurl');
        
        echo "</head><body style='background: url(\"{$logo}\") no-repeat;'>";
        
        echo "<h1 class='c'>{$this->get_display_name()}</h1>";
        echo "<h2 class='c'>".fullname($this->student)."</h2>";
        echo "<h3 class='c'>(".$this->student->username.")</h3>";

        echo "<br><br><br>";
        
        echo "<div class='report_right'>";
            echo "<table>";
                echo "<tr><th>Criteria/Activity</th><th>Award</th><th>Date</th><th style='width:25%;'>Comments</th></tr>";
                                
                if ($this->units)
                {
                    foreach($this->units as $unit)
                    {

                        if ($unit->is_student_doing())
                        {
                                                        
                            //get the users award from the unit
                            $unitAward = $unit->get_user_award();   
                            $award = '';
                            if($unitAward)
                            {
                                $award = $unitAward->get_award();
                            }
                            
                            $date = '';
                            if(!is_null($unit->get_date_updated())){
                                $date = date('d M Y', $unit->get_date_updated());
                            }
                                                        
                            echo "<tr class='b'>";
                            
                                echo "<td>".$unit->get_name()."</td>";
                                echo "<td class='c'>".$award."</td>";
                                echo "<td class='c' style='min-width:50px;'>".$date."</td>";
                                echo "<td style='padding:5px;'>".format_text($unit->get_comments(), FORMAT_PLAIN)."</td>";
                            
                            echo "</tr>";
                            
                            
                            // Criteria
                            if ($unit->get_criteria())
                            {
                                foreach($unit->get_criteria() as $criteria)
                                {
                                    
                                    $value = '';
                                    $date = '';
                                    $valueObj = $criteria->get_student_value();
                                    if($valueObj)
                                    {
                                        $value = $valueObj->get_value();
                                        $date = (!is_null($criteria->get_date_updated())) ? $criteria->get_date_updated() : $criteria->get_date_set();
                                        if($criteria->get_user_defined_value() && strlen($criteria->get_user_defined_value())){
                                            $value .= " : {$criteria->get_user_defined_value()}";
                                        }
                                        
                                    }
                                                                        
                                    echo '<tr>';
                                        echo '<td style="vertical-align:top;">&nbsp;&nbsp;&nbsp;&nbsp;'.$criteria->get_name().' :- '.$criteria->get_details().'</td>';
                                        echo '<td style="vertical-align:top;" class="c">'.$value.'</td>';
                                        echo '<td style="vertical-align:top;" class="c">'.$date.'</td>';
                                        echo '<td style="vertical-align:top;padding:5px;"><small>'.format_text($criteria->get_comments(), FORMAT_PLAIN).'</small></td>';
                                    echo '</tr>';
                                                                        
                                    
                                }
                            }
                            
                            
                            echo "<tr class='divider-row'><td colspan='4'></td></tr>";
                            
                        }
                        
                    }
                }
                
            echo "<tr class='grey'><td colspan='4'><br></td></tr>";    
                        
            $context = context_course::instance($COURSE->id);
            
            //>>BEDCOLL TODO this need to be taken from the qual object
            //as foundatonQual is different
            //if we are looking at the student then show the qual award
            echo $this->show_predicted_qual_award($this->predictedAward, $context);
            echo $this->show_final_qual_award($this->studentAward, $context);
            

            echo "</table>";
        
        echo "</div>";
        
        
        echo "</body></html>";
        
        
        
    }
    
    
    protected function show_final_qual_award($studentAward, $context, $editing = false)
    {
        
        global $DB;
        
        $retval = "";
        $retval .= "<tr class='award_row'>";
        $type = get_string('predictedfinalaward','block_bcgt');
        $award = 'N/A';
        
        $awards = $DB->get_records("block_bcgt_bspk_q_grade_vals", array("qualgradingid" => $this->grading), "rangelower ASC");

        
        if($studentAward)
        {
            $award = $studentAward->get_award();
            $retval .= "<td><span class='qualAwardType'>$type</span></td>";
            
            if ($editing && !$this->use_auto_calculations())
            {
                $retval .= "<td><span class='qualAward finalAward_S{$this->studentID}_Q{$this->id}'>";
                $retval .= "<select class='qualAwardSelect' qualID='{$this->id}' studentID='{$this->studentID}'>";
                    $retval .= "<option value='-1'></option>";
                    if ($awards)
                    {
                        foreach($awards as $award)
                        {
                            $chk = ($studentAward->get_id() == $award->id) ? 'selected' : '';
                            $retval .= "<option value='{$award->id}' {$chk} >{$award->grade}</option>";
                        }
                    }
                $retval .= "</select>";
                $retval .= "</span></td>";
            }
            else
            {
                $retval .= "<td><span class='qualAward finalAward_S{$this->studentID}_Q{$this->id}'>".$award."</span></td>";
            }
            
            $retval .= "<td><small>".get_string('predictedfinalawardhelp', 'block_bcgt')."</small></td>";
        }   
        else
        {
            $retval .= "<td><span class='qualAwardType'>$type</span></td>";
            if ($editing && !$this->use_auto_calculations())
            {
                $retval .= "<td><span class='qualAward finalAward_S{$this->studentID}_Q{$this->id}'>";
                $retval .= "<select class='qualAwardSelect' qualID='{$this->id}' studentID='{$this->studentID}'>";
                    $retval .= "<option value='-1'></option>";
                    if ($awards)
                    {
                        foreach($awards as $award)
                        {
                            $retval .= "<option value='{$award->id}'>{$award->grade}</option>";
                        }
                    }
                $retval .= "</select>";
                $retval .= "</span></td>";
            }
            else
            {
                $retval .= "<td><span class='qualAward finalAward_S{$this->studentID}_Q{$this->id}'>".$award."</span></td>";
            }            
            $retval .= "<td><small>".get_string('predictedfinalawardhelp', 'block_bcgt')."</small></td>";
        }
        $retval .= "</tr>";
        
        
        return $retval;
        
    }
    
    protected function show_predicted_qual_award($studentAward, $context)
    {
        
        $retval = "";
        $retval .= "<tr class='award_row'>";
        $type = get_string('predictedavgaward','block_bcgt');
        $award = 'N/A';
        
        if($studentAward)
        {
            $award = $studentAward->get_award();
            $retval .= "<td><span class='qualAwardType'>$type</span></td>";
            $retval .= "<td><span class='qualAward predictedAward_S{$this->studentID}_Q{$this->id}'>".$award."</span></td>";
            $retval .= "<td><small>".get_string('predictedavgawardhelp', 'block_bcgt')."</small></td>";
        }   
        else
        {
            $retval .= "<td><span class='qualAwardType'>$type</span></td>";
            $retval .= "<td><span class='qualAward predictedAward_S{$this->studentID}_Q{$this->id}'>$award</span></td>";
            $retval .= "<td><small>".get_string('predictedavgawardhelp', 'block_bcgt')."</small></td>";
        }
        
        $retval .= "</tr>";
        
        return $retval;
        
    }
    
    public function get_possible_awards()
    {
        global $DB;
        return $DB->get_records("block_bcgt_bspk_q_grade_vals", array("qualgradingid" => $this->grading));
    }
    
    public function use_auto_calculations()
    {
        
        if (!isset($this->settings['setting_ignore_auto_calcs']) || $this->settings['setting_ignore_auto_calcs'] <> 1)
        {
            return true;
        }
        
    }
    
    public function check_grading_structures_compatability()
    {
        
        global $DB;
        
        $errors = array();
        
        if (!$this->use_auto_calculations())
        {
            return $errors;
        }
        
        
        if ($this->grading > 0)
        {
            
            $gradingInfo = $DB->get_record("block_bcgt_bspk_qual_grading", array("id" => $this->grading));
            $possibleQualAwards = $this->get_possible_awards();
            $lowestRange = null;
            $highestRange = null;
            if ($possibleQualAwards)
            {
                foreach($possibleQualAwards as $qualAward)
                {
                    
                    if (is_null($lowestRange) || $qualAward->rangelower < $lowestRange){
                        $lowestRange = $qualAward->rangelower;
                    }
                    
                    if (is_null($highestRange) || $qualAward->rangeupper > $highestRange){
                        $highestRange = $qualAward->rangeupper;
                    }
                    
                    // First make sure the ranges make sense
                    if ($qualAward->rangelower >= $qualAward->rangeupper){
                        $errors[] = "Qualification Award [{$qualAward->grade}] is setup incorrectly - Ranges are incorrect";
                    }
                    
                }
            }
            
            // Check that the grading structure chosen for the units fits in with the qual structure
            if ($this->units)
            {
                foreach($this->units as $unit)
                {

                    $unitGradingInfo = $DB->get_record("block_bcgt_bspk_unit_grading", array("id" => $unit->get_grading()));
                    $lowestUnitRange = null;
                    $highestUnitRange = null;
                    $possibleAwards = $unit->get_possible_awards();
                    if ($possibleAwards)
                    {
                        foreach($possibleAwards as $award)
                        {
                            
                            if (is_null($lowestUnitRange) || $award->rangelower < $lowestUnitRange){
                                $lowestUnitRange = $award->rangelower;
                            }

                            if (is_null($highestUnitRange) || $award->rangeupper > $highestUnitRange){
                                $highestUnitRange = $award->rangeupper;
                            }
                            
                            // Check that the points are between the qual ranges
                            if ($award->points < $lowestRange || $award->points > $highestRange){
                                $errors[] = "Unit Award [{$award->grade}] is incompatible with Qualification Grading Structure [{$gradingInfo->name}] - Unit award points do not fall between the given Qualification award ranges";
                            }
                            
                        }
                    }
                    
                    // Criteria
                    if ($unit->get_criteria())
                    {
                        
                        foreach($unit->get_criteria() as $criteria)
                        {
                            
                            $lowestCritRange = null;
                            $highestCritRange = null;
                            $highestCritPoints = null;
                            $possibleAwards = $criteria->get_met_values();
                            if ($possibleAwards)
                            {
                                foreach($possibleAwards as $award)
                                {

                                    if (is_null($lowestCritRange) || $award->rangelower < $lowestCritRange){
                                        $lowestCritRange = $award->rangelower;
                                    }

                                    if (is_null($highestCritRange) || $award->rangeupper > $highestCritRange){
                                        $highestCritRange = $award->rangeupper;
                                    }
                                    
                                    if (is_null($highestCritPoints) || $award->points > $highestCritPoints){
                                        $highestCritPoints = $award->points;
                                    }
                                    
                                    // Check that the points are between the unit ranges
                                    if ($award->points < $lowestUnitRange || $award->points > $highestUnitRange){
                                        $errors[] = "Criteria Award [{$award->grade}] is incompatible with Unit Grading Structure [{$unitGradingInfo->name}] - Criteria award points do not fall between the given Unit award ranges";
                                    }
                                    

                                }
                            }
                            
                            // NEED TO DO SUB CRITERIA AS WELL
                            
                            // Do we have a unit award with a range we could never reach?
                            if (!is_null($highestCritPoints) && $highestCritPoints < $highestUnitRange)
                            {
                                $errors[] = "[{$criteria->get_name()}] Criteria Awards are incompatible with Unit Grading Structure - Unit awards have a range that it is impossible to reach with given Criteria award points";
                            }

                            
                        }
                        
                    }                    
                    
                }
            }
            
        }
        
        return $errors;
        
    }
    
    
    
    
    /**
     * displays the unit grid. 
     */
    public function display_subject_grid()
    {
        //display the subject grid
    }
    
    
    /**
     * 
     * I hate all these static methods
     * 
     * Since people can create their own grade structures, we don't have a default set of values to list
     * When we let them customise it we will have to get a distinct list of values specified in all their structures
     * We will store some defaults here (cba with a new table in db), if they are here use this image, otherwise they can specify
     */
    public static function get_default_value_image($val){
        
        $values = array(
            "A" => "icon_Achieved.png",
            "Abs" => "icon_Absent.png",
            "ABS" => "icon_Absent.png",
            "C" => "icon_Credit.png",
            "D" => "icon_Distinction.png",
            "F" => "icon_Fail.png",
            "L" => "icon_Late.png",
            "M" => "icon_Merit.png",
            "N/A" => "icon_NotAttempted.png",
            "X" => "icon_NotAchieved.png",
            "PA" => "icon_PartiallyAchieved.png",
            "P" => "icon_Pass.png",
            "R" => "icon_Referred.png",
            "PTD" => "icon_Target.png",
            "WS" => "icon_WorkSubmitted.png",
            "WNS" => "icon_WorkNotSubmitted.png"
        );
        
        if (array_key_exists($val, $values)){
            return $values[$val];
        } else {
            return false;
        }
        
    }
    
    
    
    
    /**
     * Get the image of a particular criteria value and return an image object
     * @global type $CFG
     * @param string $studentValue
     * @param type $typeID
     * @return stdClass 
     */
    public static function get_grid_image($studentValue, $longValue, $valueInfo)
    {

        global $CFG, $DB;

        if($studentValue == null)
        {
            $studentValue = "N/A";
        }
        
        $class = 'stuValue'.$studentValue;

        // If the valueInfo exists, check if we have an image for that first
        if ($valueInfo && !is_null($valueInfo->img)){
            $image = $CFG->wwwroot . '/blocks/bcgt/plugins/bcgtbespoke/pix/grid_symbols/bespoke/'.$valueInfo->img;
        }
        elseif (BespokeQualification::get_default_value_image($studentValue) != false ){
            // if not, check our defaults
            $image = $CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbespoke/pix/grid_symbols/'.BespokeQualification::get_default_value_image($studentValue);
        } else {
            // If still not, broken img
            $image = $CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbespoke/pix/grid_symbols/icon_NoIcon.png';
        }
        
        // If not, let's check our defaults

        $obj = new stdClass();
        $obj->image = $image;
        $obj->class = $class;
        $obj->title = $longValue;
        

        return $obj;

    }
    
   
    
    
    public static function get_edit_form_menu($disabled = '', $qualID = -1, $typeID = -1)
	{
                
        $jsModule = array(
            'name'     => 'mod_bcgtbespoke',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbespoke/js/bcgtbespoke.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        global $PAGE;
        $PAGE->requires->js_init_call('M.mod_bcgtbespoke.bespokeiniteditqual', null, true, $jsModule);
       
        
		$qualLevel = optional_param('level', '', PARAM_INT);
		$qualSubType = optional_param('subtype', '', PARAM_TEXT);
		$customSubType = optional_param('customsubtype', '', PARAM_TEXT);
        $qualDisplayType = optional_param('displaytype', '', PARAM_TEXT);
 		
		$subTypes = get_subtype_from_type();
		if($qualID != -1)
		{
			$qualLevel = BespokeQualification::get_qual_level($qualID);
            $qualDisplayType = BespokeQualification::get_qual_display_type($qualID);
            $qualSubType = BespokeQualification::get_qual_custom_subtype($qualID);
		}
                
        
		$retval = "";
        
        
        // Display Type
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='displaytype'><span class='required'>*</span>".get_string('displaytype', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<input type='text' name='displaytype' value='{$qualDisplayType}' title='".get_string('displaytype:desc', 'block_bcgt')."' />";
            $retval .= "</div>";
        $retval .= "</div>";
        
        
        // Sub Type
        $retval .= '<div class="inputContainer"><div class="inputLeft">';
        $retval .= '<label for="subtype"><span class="required">*</span>';
        $retval .= get_string('subtype', 'block_bcgt');
        $retval .= '</label></div>';
		$retval .= '<div class="inputRight"><select name="subtype" id="qualSubtype">';
        $retval .= '<option value="">Please Select one</option>';
        $selected = ($qualSubType == '-2') ? 'selected' : '';
        $retval .= '<option value="-2" '.$selected.'>['.get_string('addnewsubtype', 'block_bcgt').']</option>';
			if($subTypes)
			{
				
				foreach($subTypes as $subType) {
					$selected = '';
					
                    if ($qualSubType == $subType->get_subtype()){
                        $selected = 'selected';
                    }
                    
					$retval .= '<option '.$selected.' value="'.$subType->get_subtype().'">';
                    $retval .= $subType->get_subtype().'</option>';
				}	
			}
			
		$retval .= '</select>';
        
        $style = ($qualSubType == '-2') ? 'inline-block' : 'none';
        $retval .= '<div id="custom-sub-type" style="display:'.$style.';"><input type="text" name="customsubtype" value="'.$customSubType.'" placeholder="'.get_string('subtype', 'block_bcgt').'" /></div>';
        
        $retval .= '</div></div>';
        
        
        // Level
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='level'>".get_string('level', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<input style='width:40px;' type='number' name='level' value='{$qualLevel}' />";
            $retval .= "</div>";
        $retval .= "</div>";
        
        $retval .= "<input type='hidden' name='tID' value='-1' />";
        
        
		return $retval;
        
	}
    
    
    /**
     * Why exactly do we have so many static functions? This sort of thing should be using an instantiated object...
     * @param type $qualID
     */
    public static function get_qual_display_type($qualID){
        
        global $DB;
		$sql = "SELECT displaytype FROM {block_bcgt_bespoke_qual}
		WHERE bcgtqualid = ?";	
		$record = $DB->get_record_sql($sql, array($qualID));
        return ($record) ? $record->displaytype : false;
        
    }
    
    public static function get_qual_custom_subtype($qualID){
        global $DB;
		$sql = "SELECT subtype FROM {block_bcgt_bespoke_qual}
		WHERE bcgtqualid = ?";	
		$record = $DB->get_record_sql($sql, array($qualID));
        return ($record) ? $record->subtype : false;
    }
    
    public static function get_qual_level($qualID){
        global $DB;
		$sql = "SELECT level FROM {block_bcgt_bespoke_qual}
		WHERE bcgtqualid = ?";	
		$record = $DB->get_record_sql($sql, array($qualID));
        return ($record) ? $record->level : false;
    }
    
    public static function get_instance($qualID, $params, $loadParams)
    {
        return new BespokeQualification($qualID, $params, $loadParams);
    }
    
    public static function get_qual_grading_structures(){
        
        global $DB;
        
        $results = array();
        
        $records = $DB->get_records("block_bcgt_bspk_qual_grading");
        
        if ($records)
        {
            foreach($records as $record)
            {
                
                $values = $DB->get_records("block_bcgt_bspk_q_grade_vals", array("qualgradingid" => $record->id));
                if ($values)
                {
                    
                    $record->values = $values;
                    $results[$record->id] = $record;
                    
                }
                
            }
        }
        
        return $results;
        
    }
    
    public static function get_pluggin_qual_class($typeID = -1, $qualID = -1, 
            $familyID = -1, $params = null, $loadParams = null)
    {
        //using things like subtypes and levels
        //this needs to be able to do a switch case and return the correct instance of the
        //qualification class.
        //examples see BTEC or Alevel.
        return new BespokeQualification($qualID, $params, $loadParams);
    }
    
    
    public function has_printable_report() {
        return true;
    }
    
    
    
//    
//    public function export_class_grid()
//    {
//                
//        global $CFG, $DB, $USER;
//        
//        $objPHPExcel = new \PHPExcel();
//        $objPHPExcel->getProperties()
//                     ->setCreator(fullname($USER))
//                     ->setLastModifiedBy(fullname($USER))
//                     ->setTitle($this->get_display_name())
//                     ->setSubject($this->get_display_name())
//                     ->setDescription($this->get_display_name() . " generated by Moodle Grade Tracker");
//
//        $sheetIndex = 0;
//        
//        
//        // Get possible values
//        $possibleValues = $this->get_possible_values( self::ID );
//        $possibleValuesArray = array();
//        foreach($possibleValues as $possibleValue){
//            $possibleValuesArray[] = $possibleValue->shortvalue;
//        }
//        
//        // Have a worksheet for each unit
//        $units = $this->get_units();
//        
//        require_once $CFG->dirroot . '/blocks/bcgt/classes/sorters/UnitSorter.class.php';
//        $unitSorter = new UnitSorter();
//        usort($units, array($unitSorter, "ComparisonDelegateByType"));
//        
//        // Params for loading student info
//        $loadParams = new stdClass();
//        $loadParams->loadLevel = Qualification::LOADLEVELALL;
//        $loadParams->loadAward = true;
//        
//        require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeCriteriaSorter.class.php';
//        
//        if ($units)
//        {
//            
//            foreach($units as $unit)
//            {
//                
//                $title = "(".$unit->get_id() . ") ";
//                $unitName = preg_replace("/[^a-z 0-9]/i", "", $unit->get_name());
//                $cnt = strlen($title);
//                $diff = 30 - $cnt;
//                $title .= substr($unitName, 0, $diff);
//                
//                
//                // Set current sheet
//                $objPHPExcel->createSheet($sheetIndex);
//                $objPHPExcel->setActiveSheetIndex($sheetIndex);
//                $objPHPExcel->getActiveSheet()->setTitle($title);
//                
//                $rowNum = 1;
//                
//                // Headers
//                $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", "Username");
//                $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", "First name");
//                $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", "Last name");                
//
//                // Overall unit award
//                $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", "Award");
//                
//                $letter = 'E';
//                
//                // Get list of criteria on this unit
//                $criteria = $unit->get_used_criteria_names();
//                
//                // Sort
//                $criteriaSorter = new CriteriaSorter();
//                usort($criteria, array($criteriaSorter, "ComparisonSimple"));
//
//                if ($criteria)
//                {
//                    foreach($criteria as $criterion)
//                    {
//                        $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", $criterion);
//                        $letter++;
//                    }
//                }
//                
//                $rowNum++;
//                
//                // Get all the students on this unit
//                $students = get_users_on_unit_qual($unit->get_id(), $this->id);
//                
//                if ($students)
//                {
//                    
//                    foreach($students as $student)
//                    {
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", $student->username);
//                        $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", $student->firstname);
//                        $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", $student->lastname);
//                        
//                        // Load student into unit
//                        $unit->load_student_information($student->id, $this->id, $loadParams);
//                        
//                        $userAward = $unit->get_user_award();
//                        $award = '-';
//                        if($userAward)
//                        {
//                            $award = $userAward->get_award();
//                        }
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", $award);
//                        
//                        $letter = 'E';
//                        
//                        // Loop criteria
//                        if ($criteria)
//                        {
//                            foreach($criteria as $criterion)
//                            {
//                                
//                                $studentCriterion = $unit->get_single_criteria(-1, $criterion);
//                                if ($studentCriterion)
//                                {
//                                    $shortValue = 'N/A';
//                                    $studentValueObj = $studentCriterion->get_student_value();	
//                                    if ($studentValueObj){
//                                        $shortValue = $studentValueObj->get_short_value();
//                                        if($studentValueObj->get_custom_short_value())
//                                        {
//                                            $shortValue = $studentValueObj->get_custom_short_value();
//                                        }
//                                    }
//                                    $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", $shortValue);
//                                    
//                                    // Apply drop-down list
//                                    $objValidation = $objPHPExcel->getActiveSheet()->getCell("{$letter}{$rowNum}")->getDataValidation();
//                                    $objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
//                                    $objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
//                                    $objValidation->setAllowBlank(false);
//                                    $objValidation->setShowInputMessage(true);
//                                    $objValidation->setShowErrorMessage(true);
//                                    $objValidation->setShowDropDown(true);
//                                    $objValidation->setErrorTitle('input error');
//                                    $objValidation->setError('Value is not in list');
//                                    $objValidation->setPromptTitle('Choose a value');
//                                    $objValidation->setPrompt('Please choose a criteria value from the list');
//                                    $objValidation->setFormula1('"'.implode(",", $possibleValuesArray).'"');
//                                    
//                                }
//                                else
//                                {
//                                    $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", "-");
//                                }
//                                
//                                $letter++;
//                                
//                            }
//                        }
//                                                
//                        $rowNum++;
//                        
//                    }
//                    
//                }
//                
//                // Freeze top & first 3 columns (everything to the left of D and above 2)
//                $objPHPExcel->getActiveSheet()->freezePane('D2');
//                
//                $objPHPExcel->getActiveSheet()->getStyle("E2:{$letter}{$rowNum}")->getProtection()
//                                              ->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
//                
//                $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
//                                                
//                // Increment sheet index
//                $sheetIndex++;
//                
//            }
//            
//        }
//        
//        $objPHPExcel->setActiveSheetIndex(0);
//        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//
//        ob_clean();
//        $objWriter->save('php://output');
//        exit;                
//        
//    }
    
    
    
    
    public function export_student_grid()
    {
                
        global $CFG, $DB, $USER;
        
        require_once $CFG->dirroot . '/blocks/bcgt/classes/sorters/UnitSorter.class.php';
        require_once $CFG->dirroot . '/blocks/bcgt/classes/sorters/CriteriaSorter.class.php';

        $student = $this->student;
        
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
                     ->setCreator(fullname($USER))
                     ->setLastModifiedBy(fullname($USER))
                     ->setTitle($this->get_display_name() . " - " . fullname($student))
                     ->setSubject($this->get_display_name() . " - " . fullname($student))
                     ->setDescription($this->get_display_name() . " - " . fullname($student) . " - generated by Moodle Grade Tracker");

        $sheetIndex = 0;
        
        // Remove default sheet
        $objPHPExcel->removeSheetByIndex(0);
        
        // Style for blank cells - criteria not on that unit
        $blankCellStyle = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'E0E0E0')
            )
        );
        
        
        $lockCells = array(); // At some point, if i can get it to work
        
                
        // Have a worksheet for each unit
        $units = $this->get_units();
                
        $criteria = $this->get_used_criteria_names();
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeCriteriaSorter.class.php');
        $criteriaSorter = new BespokeCriteriaSorter();
		usort($criteria, array($criteriaSorter, "ComparisonSimple"));
        
        // Set current sheet
        $objPHPExcel->createSheet($sheetIndex);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $objPHPExcel->getActiveSheet()->setTitle("Grades");

        $rowNum = 1;

        // Headers
        $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", "Unit ID");
        $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", "Unit Name");

        $letter = 'C';
        
        if ($criteria)
        {
            foreach($criteria as $criterion)
            {
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("{$letter}{$rowNum}", $criterion, PHPExcel_Cell_DataType::TYPE_STRING);
                $letter++;
            }
        }

        $rowNum++;

        if ($units)
        {

            foreach($units as $unit)
            {

                $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", $unit->get_id());
                $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", $unit->get_name());
                $letter = 'C';

                if ($unit->is_student_doing())
                {
                
                    // Loop criteria
                    if ($criteria)
                    {
                        foreach($criteria as $criterion)
                        {

                            $studentCriterion = $unit->get_single_criteria(-1, $criterion);
                            if ($studentCriterion)
                            {
                                
                                
                                // Get possible values
                                $possibleValues = $studentCriterion->get_possible_values();
                                
                                $possibleValuesArray[-1] = 'N/A';
                                
                                if ($possibleValues){
                                    foreach($possibleValues as $val){
                                        $possibleValuesArray[$val->id] = $val->shortgrade;
                                    }
                                }
                                
                                $shortValue = 'N/A';
                                $studentValueObj = $studentCriterion->get_student_value();	
                                if ($studentValueObj){
                                    $shortValue = $studentValueObj->get_short_value();
                                }
                                $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", $shortValue);

                                // Apply drop-down list
                                $objValidation = $objPHPExcel->getActiveSheet()->getCell("{$letter}{$rowNum}")->getDataValidation();
                                $objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
                                $objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                                $objValidation->setAllowBlank(false);
                                $objValidation->setShowInputMessage(true);
                                $objValidation->setShowErrorMessage(true);
                                $objValidation->setShowDropDown(true);
                                $objValidation->setErrorTitle('input error');
                                $objValidation->setError('Value is not in list');
                                $objValidation->setPromptTitle('Choose a value');
                                $objValidation->setPrompt('Please choose a criteria value from the list');
                                $objValidation->setFormula1('"'.implode(",", $possibleValuesArray).'"');
                                
                            }
                            else
                            {
                                $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", "");
                                $objPHPExcel->getActiveSheet()->getStyle("{$letter}{$rowNum}")->applyFromArray($blankCellStyle);
                            }                           

                            $letter++;

                        }
                    }

                    $rowNum++;
                
                }

            }
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            

        }

        // Freeze rows and cols (everything to the left of D and above 2)
        $objPHPExcel->getActiveSheet()->freezePane('C2');
        
        
        
        // Comments worksheet
        
        $sheetIndex = 1;
        
        // Set current sheet
        $objPHPExcel->createSheet($sheetIndex);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $objPHPExcel->getActiveSheet()->setTitle("Comments");

        $rowNum = 1;

        // Headers
        $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", "Unit ID");
        $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", "Unit Name");

        $letter = 'C';
        
        if ($criteria)
        {
            foreach($criteria as $criterion)
            {
                $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", $criterion);
                $letter++;
            }
        }

        $rowNum++;

        if ($units)
        {

            foreach($units as $unit)
            {

                $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", $unit->get_id());
                $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", $unit->get_name());
                $letter = 'C';

                if ($unit->is_student_doing())
                {
                
                    // Loop criteria
                    if ($criteria)
                    {
                        foreach($criteria as $criterion)
                        {

                            $studentCriterion = $unit->get_single_criteria(-1, $criterion);
                            if ($studentCriterion)
                            {
                                $comments = $studentCriterion->get_comments();
                                $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", $comments);
                            }
                            else
                            {
                                $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", "");
                            }                           

                            $letter++;

                        }
                    }

                    $rowNum++;
                
                }

            }
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            

        }

        // Freeze rows and cols (everything to the left of D and above 2)
        $objPHPExcel->getActiveSheet()->freezePane('C2');
        
        
        
        
        
        
        
        
                
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        
        ob_clean();
        $objWriter->save('php://output');
        exit;                
        
    }
    
    
    
    
    
    public function import_student_grid($file, $confirm = false){
        
        global $CFG, $DB, $USER;
                
        $now = time();
                
        $output = "";
        
        if ($confirm)
        {
            
            $output .= "loading file {$file['tmp_name']} ...<br>";
            
            try {
                
                $inputFileType = PHPExcel_IOFactory::identify($file['tmp_name']);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($file['tmp_name']);
                
            } catch(Exception $e){
                
                print_error($e->getMessage());
                return false;
                
            }
            
            $cnt = 0;
            
            $output .= "file loaded successfully ...<br>";
            $output .= "student ({$this->student->username}) " . fullname($this->student) . " loaded successfully ...<br>";
            
            $objPHPExcel->setActiveSheetIndex(0);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            
            $output .= " loaded worksheet - " . $objWorksheet->getTitle() . " ...<br>";
            
            $commentsWorkSheet = $objPHPExcel->getSheet(1);
            
            $output .= " loaded worksheet - " . $commentsWorkSheet->getTitle() . " ...<br>";
            
            $lastCol = $objWorksheet->getHighestColumn();
            $lastCol++;
            $lastRow = $objWorksheet->getHighestRow();
                        
            
            // Loop through rows to get students
            for ($row = 2; $row <= $lastRow; $row++)
            {

                $output .= "processing row {$row} ...<br>";
                
                $studentUnit = false;

                // Loop columns
                $rowClass = ( ($row % 2) == 0 ) ? 'even' : 'odd';

                for ($col = 'A'; $col != $lastCol; $col++){

                    $cellValue = $objWorksheet->getCell($col . $row)->getCalculatedValue();

                    if ($col == 'A'){
                        $unitID = $cellValue;
                        $studentUnit = $this->units[$unitID];
                        $output .= "loaded unit " . $studentUnit->get_name() . " ...<br>";
                        continue; // Don't want to print the id out
                    }


                    if ($col != 'A' && $col != 'B'){

                        $value = $cellValue;

                        // Get studentCriteria to see if it has been updated since we downloaded the sheet
                        $criteriaName = $objWorksheet->getCell($col . "1")->getCalculatedValue();
                        $studentCriterion = $studentUnit->get_single_criteria(-1, $criteriaName);

                        $unitCriteria = $studentUnit->get_single_criteria(-1, $criteriaName);

                        // If the unit doesn't have the criteria, don't bother doing anything
                        if ($unitCriteria)
                        {
                        
                            $txtValue = $value;
                            if ($txtValue == ''){
                                $txtValue = 'N/A';
                            }
                            $output .= "attempting to set value for criterion {$criteriaName} to {$txtValue} ... ";

                            if ($studentCriterion)
                            {

                                // Get possible values
                                $possibleValues = $studentCriterion->get_possible_values(); 

                                $possibleValuesArray[-1] = 'N/A';
                                
                                if ($possibleValues){
                                    foreach($possibleValues as $val){
                                        $possibleValuesArray[$val->id] = $val->shortgrade;
                                    }
                                }                                
                                
                                
                                
                                // Set new value
                                if (array_search($value, $possibleValuesArray) !== false)
                                {

                                    $valueID = array_search($value, $possibleValuesArray);
                                    $studentCriterion->set_user($USER->id);
                                    $studentCriterion->set_date();
                                    $studentCriterion->update_students_value($valueID);
                                    
                                    // Comments
                                    $commentsCellValue = (string)$commentsWorkSheet->getCell($col . $row)->getCalculatedValue();
                                    $commentsCellValue = trim($commentsCellValue);
                                    $studentCriterion->add_comments($commentsCellValue);
                                    
                                    $studentCriterion->save_student($this->id, false);
                                    $output .= "success - criterion updated ...<br>";
                                    $cnt++;

                                }
                                else
                                {
                                    $output .= "error - {$value} is an invalid value for this criterion ...<br>";
                                }

                            } 
                            else
                            {
                                $output .= "error - student criteria could not be loaded ...<br>";
                            }
                        
                        }

                    }

                }

            }
            
            $output .= "end of worksheet ...<br>";
            $output .= "end of process - {$cnt} criteria values updated<br>";
            
            
        }
        else
        {
            
            try {
                
                $inputFileType = PHPExcel_IOFactory::identify($file['tmp_name']);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($file['tmp_name']);
                
            } catch(Exception $e){
                
                print_error($e->getMessage());
                return false;
                
            }
            
            // Save the tmp file to Moodledata so we can still use it when we click confirm
            $saveFile = bcgt_save_file($file['tmp_name'], $this->id . '_' . $this->studentID . '_' . $now . '.xlsx', "import_student_grids");
            if (!$saveFile){
                print_error('Could not save uploaded file. Either the save location does not exist, or is not writable. (moodledata - bcgt/import_student_grids)');
            }    
                     
            $unix = $objPHPExcel->getProperties()->getCreated();
                        
            $objPHPExcel->setActiveSheetIndex(0);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            
            $lastCol = $objWorksheet->getHighestColumn();
            $lastCol++;
            $lastRow = $objWorksheet->getHighestRow();
            
            $commentWorkSheet = $objPHPExcel->getSheet(1);
            
            // See if anything has been updated in the DB since we downloaded the file
            $updates = $DB->get_records_select("block_bcgt_user_criteria", "bcgtqualificationid = ? AND userid = ? AND ( dateset > ? OR dateupdated > ? ) ", array($this->id, $this->studentID, $unix, $unix));

            if ($updates)
            {
                
                $output .= "<div class='importwarning'>";
                    $output .= "<b>".get_string('warning').":</b><br><br>";
                    $output .= "<p>".get_string('importwarning', 'block_bcgt')."</p>";
                    foreach($updates as $update)
                    {
                        $critRecord = $DB->get_record("block_bcgt_criteria", array("id" => $update->bcgtcriteriaid));
                        
                        if (isset($this->units[$critRecord->bcgtunitid])){
                            
                            $unit = $this->units[$critRecord->bcgtunitid];
                            $value = $DB->get_record("block_bcgt_bspk_c_grade_vals", array("id" => $update->bcgtvalueid));
                            if ($update->dateupdated > $update->dateset){
                                $updateTime = $update->dateupdated;
                                $updateUser = $DB->get_record("user", array("id" => $update->updatedbyuserid));
                            } else {
                                $updateTime = $update->dateset;
                                $updateUser = $DB->get_record("user", array("id" => $update->setbyuserid));
                            }
                                                       
                            $output .= $unit->get_name() . " (" . $critRecord->name . ") was updated to: " . $value->grade . ", at: " . date('d-m-Y, H:i', $updateTime) . ", by: ".fullname($updateUser)." ({$updateUser->username})<br>";
                        }
                        
                    }
                    
                $output .= "</div>";
                $output .= "<br><br>";
                
            }
                        
            // Key
            $output .= "<h3>Key</h3>";
            $output .= "<table class='importgridtable'>";
                $output .= "<tr>";
                    $output .= "<td class='updatedsince crit'>&nbsp;</td>";
                    $output .= "<td>The criterion has been updated in Gradetracker since you downloaded the spreadsheet</td>";
                $output .= "</tr>";
                    
                $output .= "<tr>";
                    $output .= "<td class='updatedinsheet crit'>&nbsp;</td>";
                    $output .= "<td>The criterion value in your spreadsheet is different to the one in Gradetracker. (You presumably updated it in the spreadsheet).</td>";
                $output .= "</tr>";
                
                $output .= "<tr>";
                    $output .= "<td class='updatedinsheet updatedsince crit'>&nbsp;</td>";
                    $output .= "<td>Both of the above</td>";
                $output .= "</tr>";
                
            $output .= "</table>";
            
            $output .= "<br><br>";
            
            $output .= "Below you will find all the data in the spreadsheet you have just uploaded.<br><br>";
            
            $output .= "<h2 class='c'>({$this->student->username}) ".fullname($this->student)."</h2>";
            
            $output .= "<div class='importgriddiv'>";
            $output .= "<table class='importgridtable'>";
            
                $output .= "<tr>";
                
                    $output .= "<th>".get_string('unit', 'block_bcgt')."</th>";
                    
                    for ($col = 'C'; $col != $lastCol; $col++){

                        $cellValue = $objWorksheet->getCell($col . "1")->getCalculatedValue();
                        $output .= "<th>{$cellValue}</th>";

                    }
                    
                $output .= "</tr>";
                
                // Loop through rows to get students
                for ($row = 2; $row <= $lastRow; $row++)
                {

                    $studentUnit = false;
                    
                    // Loop columns
                    $rowClass = ( ($row % 2) == 0 ) ? 'even' : 'odd';

                    $output .= "<tr class='{$rowClass}'>";

                        for ($col = 'A'; $col != $lastCol; $col++){
                            
                            $critClass = '';
                            $currentValue = 'N/A';                                        
                            $cellValue = $objWorksheet->getCell($col . $row)->getCalculatedValue();

                            if ($col == 'A'){
                                $unitID = $cellValue;
                                $studentUnit = $this->units[$unitID];
                                continue; // Don't want to print the id out
                            }
                            
                            
                            if ($col != 'A' && $col != 'B'){

                                $value = (string)$cellValue;
                                
                                $critClass .= 'crit ';

                                // Get studentCriteria to see if it has been updated since we downloaded the sheet
                                $criteriaName = $objWorksheet->getCell($col . "1")->getCalculatedValue();
                                $studentCriterion = $studentUnit->get_single_criteria(-1, $criteriaName);

                                if ($studentCriterion)
                                {
                                
                                    $critDateSet = $studentCriterion->get_date_set_unix();
                                    $critDateUpdated = $studentCriterion->get_date_updated_unix();

                                    $studentValueObj = $studentCriterion->get_student_value();	
                                    if ($studentValueObj)
                                    {
                                        $currentValue = $studentValueObj->get_short_value();
                                    }
                                    
                                    if ($currentValue != $value && !( $currentValue == 'N/A' && $value == '' )){
                                        $critClass .= 'updatedinsheet ';
                                    }
                                                                        
                                    if ($critDateSet > $unix || $critDateUpdated > $unix)
                                    {
                                        $critClass .= 'updatedsince ';
                                    }
                                    
                                    $comment = $commentWorkSheet->getCell($col . $row)->getCalculatedValue();

                                    $output .= "<td title='{$comment}' class='{$critClass}' currentValue='{$currentValue}'><small>{$cellValue}</small></td>";
                                
                                } 
                                else
                                {
                                    $output .= "<td></td>";
                                }

                            }
                            else
                            {
                                $output .= "<td>{$cellValue}</td>";
                            }


                        }

                    $output .= "</tr>";

                }
                
                
            
            $output .= "</table>";
            $output .= "</div>";
            
            $output .= "<form action='' method='post' class='c'>";
                $output .= "<input type='hidden' name='qualID' value='{$this->id}' />";
                $output .= "<input type='hidden' name='studentID' value='{$this->studentID}' />";
                $output .= "<input type='hidden' name='now' value='{$now}' />";
                $output .= "<input type='submit' class='btn' name='submit_confirm' value='".get_string('confirm')."' />";
                $output .= str_repeat("&nbsp;", 8);
                $output .= "<input type='button' class='btn' onclick='window.location.href=\"{$CFG->wwwroot}/blocks/bcgt/grids/student_grid.php?sID={$this->studentID}&qID={$this->id}\";' value='".get_string('cancel')."' />";

            $output .= "</form>";
            
 
            
        }
        
        
        return $output;
        
    }
    
    
    /**
     * Export the spec of the qualification - units, criteria, etc... 
     * No user data
     * @return boolean
     */
    public function export_specification(){
        
        global $CFG, $USER;
        
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
                     ->setCreator(fullname($USER))
                     ->setLastModifiedBy(fullname($USER))
                     ->setTitle($this->get_display_name())
                     ->setSubject($this->get_display_name())
                     ->setDescription($this->get_display_name() . " - generated by Moodle Grade Tracker");

        // Remove default sheet
        $objPHPExcel->removeSheetByIndex(0);
        
        $sheetIndex = 0;
        
        // Have a worksheet for each unit
        $units = $this->get_units();
        
        $unitSorter = new UnitSorter();
        usort($units, array($unitSorter, "ComparisonDelegateByType"));
        
        if ($units)
        {
            
            foreach($units as $unit)
            {
                
                // Set current sheet
                $unitName = substr($unit->get_name(), 0, 30);
                $unitName = preg_replace("/[^a-z 0-9]/i", "", $unitName);
                
                $objPHPExcel->createSheet($sheetIndex);
                $objPHPExcel->setActiveSheetIndex($sheetIndex);
                $objPHPExcel->getActiveSheet()->setTitle($unitName);
                
                 // Unit type
                $objPHPExcel->getActiveSheet()->setCellValue("A1", "Unit Type");
                $objPHPExcel->getActiveSheet()->setCellValue("B1", "Bespoke");
                
                
                 // Unit display typoe
                $objPHPExcel->getActiveSheet()->setCellValue("A2", "Unit Display Type");
                $objPHPExcel->getActiveSheet()->setCellValue("B2", $unit->get_display_type());
                
                // Unit name
                $objPHPExcel->getActiveSheet()->setCellValue("A3", "Unit Name");
                $objPHPExcel->getActiveSheet()->setCellValue("B3", $unit->get_name());
                
                // Unit code
                $objPHPExcel->getActiveSheet()->setCellValue("A4", "Unit Code");
                $objPHPExcel->getActiveSheet()->setCellValue("B4", $unit->get_uniqueID());
                
                // Unit details
                $objPHPExcel->getActiveSheet()->setCellValue("A5", "Unit Details");
                $objPHPExcel->getActiveSheet()->setCellValue("B5", $unit->get_details());
                
                // Unit level
                $objPHPExcel->getActiveSheet()->setCellValue("A6", "Unit Level");
                $objPHPExcel->getActiveSheet()->setCellValue("B6", $unit->get_level()->get_level());
                
                // Unit credits
                $objPHPExcel->getActiveSheet()->setCellValue("A7", "Unit Credits");
                $objPHPExcel->getActiveSheet()->setCellValue("B7", $unit->get_credits());
                
                // Unit weighting
                $objPHPExcel->getActiveSheet()->setCellValue("A8", "Unit Weighting");
                $objPHPExcel->getActiveSheet()->setCellValue("B8", $unit->get_weighting());
                
                // Unit grading
                $objPHPExcel->getActiveSheet()->setCellValue("A9", "Unit Grading Structure");
                $objPHPExcel->getActiveSheet()->setCellValue("B9", $unit->get_grading_name());
                
                                
                // Criteria headers
                $objPHPExcel->getActiveSheet()->setCellValue("A10", "Criteria Names");
                $objPHPExcel->getActiveSheet()->setCellValue("B10", "Criteria Details");
                $objPHPExcel->getActiveSheet()->setCellValue("C10", "Criteria Weighting");
                $objPHPExcel->getActiveSheet()->setCellValue("D10", "Criteria Grading Structure");
                $objPHPExcel->getActiveSheet()->setCellValue("E10", "Criteria Parent");
                
                $criteria = $this->sort_criteria(null, $unit->get_criteria());
                
                $rowNum = 11;
                
                if ($criteria)
                {
                    foreach($criteria as $criterion)
                    {

                        $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", $criterion->get_name());
                        $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", $criterion->get_details());
                        $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", $criterion->get_weighting());
                        $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", $criterion->get_grading_name());
                        $objPHPExcel->getActiveSheet()->setCellValue("E{$rowNum}", $criterion->get_parent_name());
                        $rowNum++;

                    }
                }
                                                
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
                
                $sheetIndex++;
                
            }
            
        }
        
        
        // Alignment
        $objPHPExcel->getDefaultStyle()
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        
        
        // End
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        ob_clean();
        $objWriter->save('php://output');
        
        return true;
        
    }
    
    public function has_units()
    {
        return true;
    }
    
    
    /**
     * 
     * @global type $DB
     * @global type $COURSE
     * @global type $OUTPUT
     * @global type $CFG
     * @param type $userID
     * @param type $editing
     * @param type $filterArray
     * @param type $sortArray
     * @param type $groupingID
     * @return string
     */
    protected function get_simple_qual_report_student($userID, $editing, $filterArray, $sortArray, $groupingID = -1)
    {
        global $DB, $COURSE, $OUTPUT;
        $type = optional_param('type', '', PARAM_TEXT);
        $cID = optional_param('cID', -1, PARAM_INT);
        if($cID != -1)
        {
            $context = context_course::instance($cID);
        }
        else
        {
            $context = context_course::instance($COURSE->id);
        }
        $retval = '';
        $retval .= '<form>';
        $retval .= '<h3>'.get_string('student', 'block_bcgt');
        $retval .= '</h3>';
        $retval .= '</form>';
        $targetFilter = 'all';
		$unitsFilter = 'all';
		foreach($filterArray AS $key => $filterOption)
		{
			//work out the filter options. 
			if($key == 'target')
			{
				//target is 'all, behind, ahead, or on'
				$targetFilter = $filterOption;
			}
			elseif($key == 'units')
			{
				//units is 'all, none ect'
				$unitsFilter = $filterOption;
			} 	
		}
		global $CFG;
		//for each course get the students
		$formName = "qSRQ".$this->id."";
		//anchor so we can test if we have loaded the report before or not
		$retval .= "<span id='cLoadedQ$this->id'></span>";
		$retval .= "<form method='POST' id='qualStudentReport' name='$formName' action=''>";
		$retval .= "<input type='hidden' name='view' value='s'/>";
        $qualIDToUse = $this->id;
        $filterID = $this->id;
        if($groupingID != -1)
        {
            //we know therefore that we are looking at the tabs with the groupings
            //not the quals. 
            $qualIDToUse = -1;
            $filterID = $groupingID;
        }
        $hasUnits = false;
        if($this->has_units())
        {
            $hasUnits = true;
            $retval .= "<label for='units'>".get_string('units', 'block_bcgt')."</label>".
                    "<select class='unitFilter' course='".$cID."' group='".$groupingID."' tabtype='".$type."' tab='s' id='uf_".$filterID."' qual='".$qualIDToUse."' name='units'>";
            //create the filter
            $unitsFilterOptions = $this->get_units_report_filter();
            foreach($unitsFilterOptions AS $key=>$filterOption)
            {
                $selected = "";
                if($unitsFilter == $key)
                {
                    $selected = 'selected';
                }
                $retval .= "<option $selected value='$key'>$filterOption</option>";
            }
            $retval .= "</select>";
        }
		$targetGrade = false;
        $editTarget = false;
        if(get_config('bcgt', 'showtargetgrades') && has_capability('block/bcgt:viewtargetgrade', $context))
        {
            $targetGrade = true;
            //can they edit/add a target grade for the student??
            if(has_capability('block/bcgt:edittargetgrade', $context))
            {
                //then add an edit button
                $editTarget = true;
            }
        }
        $editAsp = false;
        $aspGrade = false;
        if(get_config('bcgt', 'showaspgrades') && has_capability('block/bcgt:viewaspgrade', $context))
        {
            //if they are using alsp
            //then allow them to use an aspirational grade
            // Aspirational Targets
            $aspGrade = true;
            if(has_capability('block/bcgt:editasptargetgrade', $context))
            {
                //then add an edit button
                $editAsp = true;
            }
        }
        //add the filter to the page about selting ahead or behind target
        if($targetGrade || $aspGrade)
        {
            $retval .= "<label for='targets'>".get_string('targets', 'block_bcgt')."</label>".
                    "<select class='targetFilter' course='".$cID."' group='".$groupingID."' tabtype='".$type."' tab='s' id='tf_".$filterID."' qual='".$qualIDToUse."' name='targets'>";
            $targetFilterOptions = $this->get_target_report_filter();
            foreach($targetFilterOptions AS $key=>$filterOption)
            {
                $selected = "";
                if($targetFilter == $key)
                {
                    $selected = 'selected';
                }
                $retval .= "<option $selected value='$key'>$filterOption</option>";
            }
            $retval .= "</select>"; 
        }
        $targetGradeOptions = null;
        $retval .= '<input type="hidden" name="editing" id="editing" value="'.$editing.'"/>';
        $sorting = '';
        foreach($sortArray AS $key=>$count)
        {
            for($i=1;$i<=$count;$i++)
            {
                $sorting .= $key.',';
            }
        }
        $retval .= '<input type="hidden" name="sorting" id="sorting" value="'.$sorting.'"/>';
        //are we using breakdown or target grade table?
        $dbFields = $this->get_target_grade_db_fields();
        $dbField = $dbFields->idField;
        $dbType = $dbFields->type;
        $dbGradeField = $dbFields->gradeField;
        $dbTeacherSetField = $dbFields->teacherSetIdField;
        //if we can edit the target grade then we need a button that says, edit/view 
        if($editTarget || $editAsp)
        {
//            $dbWeightedSetField = $dbFields->weightedIdField;
            $string = 'edittargetgrades';
            $id = 'edit';
            if($editing)
            {
                $string = 'viewnonedit';
                $id = 'view';
            }
            $retval .= '<input type="submit" course="'.$cID.'" qual="'.$qualIDToUse.'" group="'.$groupingID.'" tabtype="'.$type.'" tab="s" id="'.$id.'" class="'.$id.'" name="edit" value="'.get_string($string,'block_bcgt').'"/>';
            if($editing)
            {
                //if we are editing then get all of the grades. 
                //either the target grades or the breakdowns. Different database tables. 
                //$targetGradeOptions = $this->get_possible_target_grades();
                $options = $this->get_possible_awards();
                
                
//                if($targetGradeOptions)
//                {
//                    if($dbField == 'bcgttargetgradesid' && isset($targetGradeOptions->targetgrades))
//                    {
//                        $options = $targetGradeOptions->targetgrades;
//                        $field = 'grade';
//                    }
//                    elseif($dbField == 'bcgtbreakdownid' && isset($targetGradeOptions->breakdowns))
//                    {
//                        $options = $targetGradeOptions->breakdowns;
//                        $field = 'targetgrade';
//                    }
//                }
                
                //because the selects are all basically the same. They all have the same grades in them.
                //we dont want to do multiple fors for exactly the same thing. 
                //so do it once.
                //build the string up. 
                //then later we will try and do a string replace on the selected. 
                
                $selectTarget = '<select cidsid type="bespoke" course="'.$cID.'" qual="'.$this->id.'" id="t_'.$this->id.'" group="'.$groupingID.'" studentID="x" class="edittarget" name="target">';
                $selectAsp = '<select cidsid type="aspbespoke" course="'.$cID.'" qual="'.$this->id.'" id="a_'.$this->id.'" group="'.$groupingID.'" class="editasp" name="asp">';
                $targetSelects = $this->build_select($selectTarget, $options, 'id', 'grade');
                $aspSelects = $this->build_select($selectAsp, $options, 'id', 'grade');
            }
            
        }
		//Go and get all of the students PLUS all of the data for this qual and
		//course
		$students = $this->get_students_course_qual_report($this->id, $filterArray, $sortArray, $groupingID);  
		//default/reset the sort on the top of the columns. 
		$sortUsername = -1;
		$sortFirstName = -1;
		$sortLastName = -1;
		$sortTargetA = -1;
		$sortQual = -1;
		$sortTarget = -1;
		$sortAwarded = -1;
        $sortTSTarget = -1;
                
		if($sortArray && !empty($sortArray))
		{
			$sortUsername = isset($sortArray['username'])? $sortArray['username'] : -1;
			$sortFirstName = isset($sortArray['firstname'])? $sortArray['firstname'] : -1;
			$sortLastName = isset($sortArray['lastname'])? $sortArray['lastname'] : -1;
			$sortTargetA = isset($sortArray['targetgrade'])? $sortArray['targetgrade'] : -1;
			$sortQual = isset($sortArray['qaward'])? $sortArray['qaward'] : -1;
			$sortTarget = isset($sortArray['target'])? $sortArray['target'] : -1;
			$sortAwarded = isset($sortArray['awarded'])? $sortArray['awarded'] : -1;
            $sortTSTarget = isset($sortArray['tstarget'])? $sortArray['tstarget'] : -1;
		}
        
        $retval .= "<table align='center' class='simplequalreports'>";
            
        $header = "<tr>";
        $columns = array('picture', 'username','name');
        //need to get the global config record
        //for each column build it up with an input and 
		//an image to show if it is currently being sorted ASC or DESC
        $configColumns = get_config('bcgt','btecgridcolumns');
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
            $columns = array_map('trim', $columns);
        }
        $header .= '<th></th>'; //one for the block of colour
        if(in_array('picture', $columns))
        {
            $header .= '<th></th>';
        }
        if(in_array('username', $columns))
        {
            $header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                    "sortname='username' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('username', 'block_bcgt')."</a></th>";		
            $header .= "<th class='sort'>";
            $header .= $this->build_sort_image($sortUsername);
            $header .= "</th>"; 
        }
        if(in_array('name', $columns))
        {
            $header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                    "sortname='firstname' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('firstname', 'block_bcgt')."</a> 
            / <a class='sorthead' qual='".$qualIDToUse."' group='".$groupingID."' tabtype='".$type."' tab='s' sortname='lastname' href='#q".$this->id."c'>".get_string('lastname', 'block_bcgt')."</a></th>";
            $header .= "<th class='sort'>";
            $header .= $this->build_sort_image($sortFirstName);
            $header .= $this->build_sort_image($sortLastName);
            $header .= "</th>";
        }
        if($targetGrade)
        {
            $header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                    "sortname='targetgrade' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('targetgrade', 'block_bcgt')."</a></th>";
            $header .= "<th class='sort'>";
            $header .= $this->build_sort_image($sortTargetA);
            $header .= "</th>";
        }
        if($aspGrade)
        {
            $header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                    "sortname='tstarget' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('asptargetgrades', 'block_bcgt')."</a></th>";
            $header .= "<th class='sort'>";
            $header .= $this->build_sort_image($sortTSTarget);
            $header .= "</th>";
        }        		
		$header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                "sortname='qaward' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('qualaward', 'block_bcgt')."</a></th>";
		$header .= "<th class='sort'>";
		$header .= $this->build_sort_image($sortQual);
		$header .= "</th>";
		
        $useVa = false;
        if($targetGrade || $aspGrade)
        {
            $header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                    "sortname='target' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('va', 'block_bcgt')."</a></th>";
            $header .= "<th class='sort'>";
            $header .= $this->build_sort_image($sortTarget);
            $header .= "</th>";
        }

        if($hasUnits)
        {
            $header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                    "sortname='awarded' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('nounitsawarded', 'block_bcgt')."</a></th>";
            $header .= "<th class='sort'>";
            $header .= $this->build_sort_image($sortAwarded);
            $header .= "</th>";
            $possibleUnitValues = $this->get_possible_unit_awards();
            foreach($possibleUnitValues AS $unitValue)
            {
                $string = 'no'.strtolower($unitValue);
                $header .= "<th colspan='2'>".get_string($string, 'block_bcgt')."</th>";
            }
            

//            $retval .= "<th colspan='2'>".get_string('nomerit', 'block_bcgt')."</th>";
//
//            $retval .= "<th colspan='2'>".get_string('nopass', 'block_bcgt')."</th>";
        }
		
		$header .= "<th>Grid</th>";
		$header .= "</tr>";
                
		//end the head
		if($students)
		{
			$count = 0;
			//for each student output the record
            $lastCourse = '';
			foreach($students AS $student)
            {
                $onTarget = '';
                $diff = 0;
                $userVA = new UserVa();
                if($useVa)
                {
                    $qualAwardRankingField = ''.$dbType.'awardranking';
                    $targetRankingField = $dbGradeField.'ranking';
                    //if useVA means we are using TargetGrade or ASP Grade and so 
                    //we want to be able to check the ahead/behind.
                    $diff = $userVA->get_diff($student->$targetRankingField, $student->$qualAwardRankingField);
                    $onTarget = $userVA->ahead_behind_on($student->$targetRankingField, $student->$qualAwardRankingField);
                }
                //check if we are displaying a new course (quals can be on more than one course)
                if($lastCourse != $student->courseid)
                {
                    $retval.= '</table><h4 class="courserow">'.get_string('course').' = '.$student->coursefullname.' : '.$student->courseshortname.'</h4><table>';
                    $retval .= $header;
                }
                $lastCourse = $student->courseid;
				$class = 'even';
				$count++;
				if($count%2)
				{
					$class = 'odd';
				}
				$retval .=  "<tr class='$class $onTarget'>";
                $retval .= "<td class='status $onTarget'></td>";//one for the block of colour
                if(in_array('picture', $columns))
                { 
                    $user = new stdClass;
                    $user = $student;
                    $user->id = $student->userid;
//                    $user->email = $student->email;
//                    $user->picture = $student->picture;
//                    $user->firstname = $student->firstname;
//                    $user->lastname = $student->lastname;
//                    $user->imagealt = $student->imagealt;
                    //'picture', 'firstname', 'lastname', 'imagealt', 'email'
                    $retval .= "<td>".$OUTPUT->user_picture($user)."</td>";
                }     
                if(in_array('username', $columns))
                { 
                    $retval .= "<td colspan='2'>$student->username</td>";
                }
                if(in_array('name', $columns))
                {
                    $retval .= "<td colspan='2'>$student->firstname $student->lastname</td>";
                }
                
                //Editing of the target grades
                //is saved upon change. 
                //this is loaded in the javascript file
                //this javascipt file is loaded in DashTab.class , function 
                //bcgt_tab_get_trackers_tab (loaded from my_dashboard.php)
                //the function in block_bcgt.js
                //is inittrackerstab
                //this function loads /blocks/bcgt/ajax/get_simple_qual_report.php
                //this file returns this function we are in now (eventually)
                
                //In the javascript function above it also calls an ApplyTT type of function
                //this puts a JQuery listener on the selects. 
                //this calls
                //blocks/bcgt/ajax/update_user_target.php";
                //which saves the target grades
                
                
                if($targetGrade)
                {
                    //so are we editing? are we editing the Target?
                    if($editing && $editTarget)
                    {
                        //we are now going to do some string replaces. 
                        //first we need to remove the generic id's from the select name
                        //and put in the students id and the course id. 
                        $targetSelectsStu = str_replace('cidsid', 'sid="'.$student->userid.'" cid="'.$student->courseid.'"', $targetSelects);
                        $targetSelectsStu = str_replace('id="t_'.$this->id.'"', 'id="t_'.$this->id.'_s_'.$student->userid.'"', $targetSelectsStu);
                        $targetSelectsStu = str_replace('studentID="x"', 'studentID="'.$student->userid.'"', $targetSelectsStu);
                        if($student->$dbField)
                        {
                            //if we have a databaseField set (e.g. breakdown or grades id) (in other words)
                            //we already have a grade set, so we need to change the drop down to
                            //be set to this. 
                            //then we need to replace the selectedDatabaseID with just selected. 
                            //so it gets selected. 
                            $targetSelectsStu = str_replace('selected'.$student->$dbField, 'selected', $targetSelectsStu);
                        }
                        $retval .= '<td colspan="2">'.$targetSelectsStu.'</td>';
                    }
                    else
                    {
                        //lets just display it!
                        if(!$student->$dbGradeField || $student->$dbGradeField == '')
                        {
                            $userPriorLearn = new UserPriorLearning();
                            $targetGradeOut = $userPriorLearn->get_users_prlearn_status_when_no_target($student->userid);
                        }
                        else
                        {
                            $targetGradeOut = $student->$dbGradeField;
                        }
                        $retval .= "<td colspan='2'>$targetGradeOut</td>";
                    }
                    
                }
				if($aspGrade)
                {
                    
                    // Aspirational grades are stored in different location
                    $retval .= "<td colspan='2'>";
                    
                        $aspirationalGrade = bcgt_get_aspirational_target_grade($student->userid, $this->id);
                        if ($aspirationalGrade)
                        {
                            $aspirationalGrade = reset($aspirationalGrade);
                        }
                    
                        if ($editing && $editAsp)
                        {
                            
                            $possibleGrades = \bcgt_get_qual_possible_grades($this);
                            $retval .= "<select id='edit_asp_select_{$student->id}_{$this->id}' class='update_asp_grade' studentid='{$student->userid}' qualid='{$this->id}'>";
                            $retval .= "<option value=''></option>";
                                if ($possibleGrades)
                                {
                                    foreach($possibleGrades as $possibleGrade)
                                    {
                                        $sel = ($aspirationalGrade && $aspirationalGrade->id == $possibleGrade['id']) ? 'selected' : '';
                                        $retval .= "<option value='{$possibleGrade['location']}:{$possibleGrade['id']}' {$sel} >{$possibleGrade['grade']}</option>";
                                    }
                                }
                            $retval .= "</select>";
                            
                        }
                        else
                        {
                    
                            if ($aspirationalGrade){
                                $retval .= $aspirationalGrade->grade;
                            }
                            
                        }
                    
                    $retval .= "</td>";
                    
                    
//                    //this is a mirror of above but for the aspirational grade. 
//                    if($editing && $editAsp)
//                    {
//                        $aspSelectsStu = str_replace('cidsid', 'sid="'.$student->userid.'" cid="'.$student->courseid.'"', $aspSelects);
//                        $aspSelectsStu = str_replace('id="a_'.$this->id.'"', 'id="a_'.$this->id.'_s_'.$student->userid.'"', $aspSelectsStu);
//                        if($student->$dbTeacherSetField)
//                        {
//                            $aspSelectsStu = str_replace('selected'.$student->$dbTeacherSetField, 'selected', $aspSelectsStu);
//                        }
//                        $retval .= '<td colspan="2">'.$aspSelectsStu.'</td>';
//                    }
//                    else
//                    {
//                    }
                }
                $qualAwardField = 'bespokequalaward';
				$retval .= "<td colspan='2'>".$student->$qualAwardField."</td>";
                if($useVa)
                {
                    $retval .= "<td colspan='2'>".$diff."</td>";
                }
                if($hasUnits)
                {
                    $retval .= "<td colspan='2'>$student->unitsawarded / $student->unitcount</td>";
                    $possibleUnitValues = $this->get_possible_unit_awards();
                    foreach($possibleUnitValues AS $unitValue)
                    {
                        $field = strtolower($unitValue).'count';
                        $retval .= "<td colspan='2'>".$student->$field."</td>";
                    }
                }
				$retval .= "<td><a href='{$CFG->wwwroot}/blocks/bcgt/grids/student_grid.php?qID=$this->id&sID=$student->userid&cID=$cID'>View Grid</a></td>";
				$retval .= "</tr>";
			}//end loop students
			$retval .= "</table>";
			$retval .= "<p>".get_string('simplereportsortinst', 'block_bcgt')."</p>";
		}//end students
		else
		{
			$retval .= "</table>";
			$retval .= "<p>".get_string('nostudentsfound', 'block_bcgt')."</p>";
		}
		
		$retval .= "</form>";
        return $retval;
    }  
    
}
