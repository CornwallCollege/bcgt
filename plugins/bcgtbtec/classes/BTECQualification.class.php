<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Qualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/lib.php');
class BTECQualification extends Qualification {
   
    const ID = 2;
	const NAME = 'BTEC';
	const FAMILYID = 2;
	protected $credits;
    protected $usedCriteriaNames;
    protected $defaultColumns = array('picture', 'username', 'name');
    protected $defaultcredits;
    
    protected $gridLocked;
	
	function BTECQualification($qualID, $params, $loadParams)
	{
		parent::Qualification($qualID, $params, $loadParams);
        //if we know the id then lets go get the credits from the database
        $defaultCredits = $this->get_default_credits();
        $this->defaultcredits = $defaultCredits;
		if($qualID != -1)
		{
			//gets the credits from the database
			$creditsObj = BTECQualification::retrieve_credits($qualID);
			if($creditsObj && $creditsObj->credits)
			{
				$this->credits = $creditsObj->credits;
			}
            else 
            {
                $this->credits = $defaultCredits;
            }
		}
		else
		{
			//then we have been passed the credits.
            if($params && isset($params->credits))
            {
                $this->credits = $params->credits; 
            }
            else 
            {
                $this->credits = $defaultCredits;
            }      
			
		}
	}
	
	function get_type()
	{
		return BTECQualification::NAME;
	}
    
    function get_family()
	{
		return BTECQualification::NAME;
	}
	
	function get_class_ID()
	{
		return BTECQualification::ID;
	}
	
	function get_family_ID()
	{
		return BTECQualification::FAMILYID;
	}
    			
	function get_credits()
	{
        if(isset($this->credits) && $this->credits >= 0)
        {
            return $this->credits;
        }
        $credits = $this->get_default_credits();
        $this->credits = $credits;
        return $credits;
	}
    
    function add_unit(Unit $unit)
	{
		$added = parent::add_unit_qual($unit);
		return $added;
	}
	
	function remove_unit(Unit $unit)
	{
		$removed = parent::remove_units_qual($unit);
		return $removed;	
	}
    
    /**
	 * Get the value for the MET
	 */
	public static function get_criteria_met_val()
	{
		//This gets the one criteria value that will go towards having
		// the criteria met and thus the unit award
		//THIS ASSUMES ONE!!!!!
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $params = array(BTECQualification::ID, 'A');
		$record = $DB->get_record_sql($sql, $params);
		if($record)
		{
			return $record->id;
		}
		return -1;
	}
    
    public static function has_formal_assessments()
    {
        return true;
    }
    
    public function has_units()
    {
        return true;
    }
    
    public function get_criteria_met_value()
    {
        return BTECQualification::get_criteria_met_val();
    }
    
    public function get_criteria_not_met_value()
    {
        return BTECQualification::get_criteria_not_met_val();
    }
    
    /**
	 * Get the value for not met
	 */
	public static function get_criteria_not_met_val()
	{
		//This gets the one criteria value that will go towards having
		// the criteria not met
		//THIS ASSUMES ONE!!!!!
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $params = array(BTECQualification::ID, 'X');
		$record = $DB->get_record_sql($sql, $params);
		if($record)
		{
			return $record->id;
		}
		return -1;
	}
	
	public function get_work_submitted_value()
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $params = array(BTECQualification::ID, 'WS');
		$record = $DB->get_record_sql($sql, $params);
		if($record)
		{
			return $record->id;
		}
		return -1;
	}
	
	public function get_work_not_submitted_value()
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $params = array(BTECQualification::ID, 'WNS');
		$record = $DB->get_record_sql($sql, $params);
		if($record)
		{
			return $record->id;
		}
		return -1;
	}
	
	public function get_work_late_value()
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $params = array(BTECQualification::ID, 'L');
		$record = $DB->get_record_sql($sql, $params);
		if($record)
		{
			return $record->id;
		}
		return -1;
	}
    
    protected function get_simple_qual_report_tabs()
    {
        $tabs = parent::get_simple_qual_report_tabs();
        return $tabs + array("u"=>"units", "co"=>"classoverview");
    }
    
    protected function get_possible_unit_awards()
    {
        return array('Pass', 'Merit', 'Distinction');
    }
    
    public static function get_edit_form_menu($disabled = '', $qualID = -1, $typeID = -1)
	{
        //to add a new drop down: 
        $jsModule = array(
            'name'     => 'mod_bcgtbtec',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        global $PAGE;
        $PAGE->requires->js_init_call('M.mod_bcgtbtec.bteciniteditqual', null, true, $jsModule);
		$levelID = optional_param('level', -1, PARAM_INT);
		$subtypeID = optional_param('subtype', -1, PARAM_INT);
        $specID = optional_param('spec', -1, PARAM_INT);
		$levels = get_level_from_type($specID, BTECQualification::FAMILYID);
		$subTypes = get_subtype_from_type($specID, $levelID, BTECQualification::FAMILYID);
		if($qualID != -1)
		{
            $qualType = Qualification::get_qual_type($qualID);
			$qualLevel = Qualification::get_qual_level($qualID);
			$qualSubType = Qualification::get_qual_subtype($qualID);
		}
        $types = bcgt_get_types(-1, BTECQualification::FAMILYID, 'specificationdesc ASC');
		$retval = "";
        $retval .= '<div class="inputContainer"><div class="inputLeft">';
        $retval .= '<label for="spec"><span class="required">*</span>';
        $retval .= get_string('specification', 'block_bcgt');
        $retval .= ' : </label></div>';
		$retval .= '<div class="inputRight"><select '.$disabled.' name="spec" id="qualType">';
			if($types)
			{
				if(count($types) > 1)
				{
					$retval .= '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>';
				}				
				foreach($types as $spec) {
                        $selected = '';
                        if($qualID != -1 && ($spec->id == $qualType->id))
                        {
                            $selected = 'selected';
                        }
                        elseif($specID != -1 && ($specID == $spec->id))
                        {
                            $selected = 'selected';
                        }
                        else
                        {
                            if(count($types) == 1)
                            {
                                $selected = 'selected';
                            }
                        }
                        $retval .= '<option '.$selected.' value="'.$spec->id.'">';
                        $retval .= $spec->specificationdesc.'</option>';
				}	
			}
			else
			{
				$retval .= '<option value="-1">'.get_string('nospecs', 'block_bcgt').'</option>';
			}
		$retval .= '</select></div></div>';
		$retval .= '<div class="inputContainer"><div class="inputLeft">';
        $retval .= '<label for="level"><span class="required">*</span>';
        $retval .= get_string('level', 'block_bcgt');
        $retval .= ' : </label></div>';
		$retval .= '<div class="inputRight"><select '.$disabled.' name="level" id="qualLevel">';
			if($levels)
			{
				if(count($levels) > 1)
				{
					$retval .= '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>';
				}				
				foreach($levels as $level) {
                        $selected = '';
                        if($qualID != -1 && ($level->get_id() == $qualLevel->id))
                        {
                            $selected = 'selected';
                        }
                        elseif($levelID != -1 && ($levelID == $level->get_id()))
                        {
                            $selected = 'selected';
                        }
                        else
                        {
                            if(count($levels) == 1)
                            {
                                $selected = 'selected';
                            }
                        }
                        $retval .= '<option '.$selected.' value="'.$level->get_id().'">';
                        $retval .= $level->get_level().'</option>';
				}	
			}
			else
			{
				$retval .= '<option value="-1">'.get_string('nolevels', 'block_bcgt').'</option>';
			}
		$retval .= '</select></div></div>';
		$retval .= '<div class="inputContainer"><div class="inputLeft">';
        $retval .= '<label for="subtype"><span class="required">*</span>';
        $retval .= get_string('subtype', 'block_bcgt');
        $retval .= '</label></div>';
		$retval .= '<div class="inputRight"><select '.$disabled.' name="subtype" id="qualSubtype">';
			if($subTypes)
			{
				if(count($subTypes) > 1)
				{
					$retval .= '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>';
				}
				foreach($subTypes as $subType) {
					$selected = '';
					if($qualID != -1 && ($subType->get_id() == $qualSubType->id))
					{
						$selected = 'selected';
					}
					elseif($subtypeID != -1 && $subtypeID == $subType->get_id())
					{
						$selected = 'selected';
					}
					else
					{
						if(count($subTypes) == 1)
						{	
							$selected = 'selected';
						}
					}
					$retval .= '<option '.$selected.' value="'.$subType->get_id().'">';
                    $retval .= $subType->get_subtype().'</option>';
				}	
			}
			else
			{
				$retval .= '<option value="-1">'.get_string('nosubtypes', 'block_bcgt').'</option>';
			}
		$retval .= '</select></div></div>';
		return $retval;
	}
    
    public function get_credits_display_name()
    {
        return get_string('bteccredits', 'block_bcgt');
    }
    
    /**
	 * Returns the form fields that go on the edit qualification form for this qual type
	 */
	public function get_edit_form_fields()
	{
		//What adding or editing BTEC qual then we need to be able to
		//have crfedits. Not all quals have credits so this 
		//is qual specific.
		$retval = '<div class="inputContainer"><div class="inputLeft">';
        $retval .= '<label for="credits">'.$this->get_credits_display_name().''
                .': </label></div>';
		$retval .= '<div class="inputRight">'.
                '<input type="text" name="credits" value="'.$this->credits.'"/></div></div>';
        return $retval;
	} 
    
    /**
	 * Sets the credits from the post data as set in edit_qualifications_form
	 */
	public function get_submitted_edit_form_data()
	{
		//Because when editing the qual we need to get the qual specific fields
		//passed by post.
		$this->credits = $_POST['credits'];	
	}
    
    public function qual_specific_student_load_information($studentID, $qualID)
    {
        return true;
    }
    
    /**
	 * Using the object values inserts the qualification into the database
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
		$targetQualID = parent::get_target_qual(BTECQualification::ID);
		$dataobj->bcgttargetqualid = $targetQualID;
		$id = $DB->insert_record("block_bcgt_qualification", $dataobj);
		$this->id = $id;
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_INSERTED_QUAL, null, $this->id, null, null, null);
	}
	
	/**
	 * Call a parent delete_qualification_main
	 */
	public function delete_qualification()
	{
		//do we need tto do anythng thats BTEC Qual specific?
		return $this->delete_qual_main();
	}
	
	/**
	 * Using the object updates the database
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
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_UPDATED_QUAL, null, $this->id, null, null, null);
	}
    
    /**
     * Loads the users for this role onto this qualification
     * @global type $DB
     * @param type $role
     * @param type $loadStudentQuals
     * @param type $loadLevel
     * @param type $loadAward
     * @param type $courseID
     */
    //$loadLevel = Qualification::LOADLEVELUNITS, $loadAward = false
    public function load_users($role = 'student', $loadStudentQuals = false, 
            $loadParams = null, $courseID = -1, $groupingID = -1)
    {
        global $DB;
        $roleDB = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = ?', array($role));
        $users = $this->get_users($roleDB->id, '', 'lastname ASC', $courseID, true, $groupingID);
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
        return $users;
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
    
    public static function edit_activity_view_page($courseID, $unitID, $activityID)
    {
        //form that will allow editing of an activity. 
        
    }
    
    public static function get_mod_tracker_options($courseModuleID, $courseID)
    {
        $retval = '';
        $jsModule = array(
            'name'     => 'mod_bcgtbtec',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        global $PAGE, $CFG;
        $PAGE->requires->js_init_call('M.mod_bcgtbtec.btecmodactivity', null, true, $jsModule);
        $unitsSelected = '';
        $units = bcgt_get_course_units($courseID, BTECQualification::FAMILYID);
        $activityUnits = get_activity_units($courseModuleID);
        if($activityUnits)
        {
            foreach($activityUnits AS $activityUnit)
            { 
                $unitsSelected .= '_'.$activityUnit->id;
                $retval .= '<div class="bcgt_col bgctActivityUnit">';
                $retval .= get_btec_activity_unit_table($courseModuleID, $activityUnit->id, $courseID, false, true);
                $retval .= '</div>';
            }
        }
        $retval .= '<label>'.get_string('addnewunit', 'block_bcgt').' : </label><br />';
        $retval .= '<select name="nUID" id="nUID">';
        $retval .= '<option value="-1"></option>';
        if($units)
        {
            foreach($units AS $newUnit)
            {
                $disabled = '';
                if(array_key_exists($newUnit->id, $activityUnits))
                {
                    $disabled = 'disabled';
                }
                $retval .= '<option '.$disabled.' value="'.$newUnit->id.'">'.$newUnit->name.'</option>';
            }
        }
        $retval .= '</select>';
        $retval .= '<input type="submit" id="bcgtAddUnit" course="'.
                $courseID.'" cmid="'.$courseModuleID.'" name="addUnit" value="'.
                get_string('addunit', 'block_bcgt').'"/>';
        $retval .= '<span id="bcgtloading"></span>';
        $retval .= '<input type="hidden" name="bcgtunitsselected" value="'.$unitsSelected.'" id="bcgtunitsselected"/>';
        $retval .= '<div id="bcgtMODAddUnitSelection">';
        $retval .= '</div>';
        return $retval;
    }
    
    public static function process_mod_tracker_options($courseModuleID, $courseID)
    {
        //get selected units
        $units = null;
        $unitsSelected = optional_param('bcgtunitsselected', '', PARAM_TEXT);
        if($unitsSelected != '')
        {
            $units = explode('_', $unitsSelected);
        }
        //does the activity exist in the bcgt table already?
        //no then add all selections
        //yes then check all
        //then check any for deletions.
        //are we adding any?
        if($units)
        {
            //we are adding some
            foreach($units AS $unit)
            {
                //unit should be the id
                if($unit == '')
                {
                    //when exploding we can get a ''
                    continue;
                }
                bcgt_btec_process_mod_units($courseModuleID, $unit, $courseID);
            }
        }
        bcgt_btec_remove_mod_unit_selection($courseModuleID, $units);  
        //explode the string on _
        //loop over each unit
        
    }
    
    public static function gradebook_check_page($courseID)
    {
        //need a drop down of groupINGs:
        //all and each group name on course. 
        global $PAGE;
        $retval = '';
        $groupingID = -1;
        $jsModule = array(
            'name'     => 'mod_bcgtbtec',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        $PAGE->requires->js_init_call('M.mod_bcgtbtec.initactivitiescheck', array(), true, $jsModule);
        load_javascript(true);
        //get list of quals that are of type BTEC that are on this course:
        $quals = bcgt_get_course_quals($courseID, BTECQualification::FAMILYID);
        if($quals)
        {
            $retval .= '<table>';
            foreach($quals AS $qual)
            {
                $retval .= '<tr>';
                $retval .= '<td>'.get_string('check', 'block_bcgt').'</td>';
                $retval .= '<td>'.bcgt_get_qualification_display_name($qual).'</td>';
                $retval .= '</tr>';
                $retval .= '<tr>';
                //then lets just display it
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELALL;
                $qualification = Qualification::get_qualification_class_id($qual->id, $loadParams);
                $retval .= $qualification->display_gradebook_check_grid($courseID, $groupingID);
                $retval .= '</tr>';
            }
            $retval .= '</table>';
        }
        return $retval;
    }
    
    public function display_gradebook_check_grid($courseID = -1, $groupingID = -1)
    {
        global $CFG;
        $retval = '';

        //do we have the capibilities to view advanced?
        //simple = just shows icons
        //advanced = shows number of students submitted etc. 
        //are we viewing as a student or just the assignments overall
        //student shows colour coded cells dependant on what they have done. 
        
        
        //get a list of all of activities
        //that this qual is on
        //if courseid is set then check on that
        //if groupid is set then check on that
        
        
        //to order by date: cant need to order by sort order. 
        $criteriaNames = $this->get_used_criteria_names();
        require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
        $criteriaSorter = new CriteriaSorter();
		usort($criteriaNames, array($criteriaSorter, "ComparisonDelegateByArrayNameLetters"));
        $this->usedCriteriaNames = $criteriaNames;
        
        //are we a student view of not?
        $userID = optional_param('sID', -1, PARAM_INT);
        $view = optional_param('view', 'os', PARAM_TEXT);
        if($userID == -1)
        {
            //then we are looking at specific student they always see the icons. 
            //therefore need the key.
            $retval .= $this->get_grid_assignment_overview_buttons($view, $courseID);
        }
        $retval .= $this->get_mod_key($courseID, $groupingID, $view); 
        
		$header = $this->get_simple_grid_header($criteriaNames);
//        $this->load_mod_info($courseID);
        $retval .= '<table id="" class="bcgt_table" align="center">';
		$retval .= $header;
		
		$retval .= "<tbody>";
        
        $retval .= $this->get_gradebook_overview_data($courseID, $groupingID);
        
        $retval .= "</tbody>";
        $retval .= "</table>";
        return $retval;
    }
    
    protected function get_mod_key($courseID, $groupingID, $view = '')
    {
        global $OUTPUT, $CFG, $COURSE;
        $retval = '';
        $currentContext = context_course::instance($COURSE->id);
        $retval .= '<div id="bcgtmodkey" class="bcgt_mod_key bcgt_mod_key'.$view.'">';
        switch($view)
        {
            case"oa":
                $modIconArray = array();
                $activities = bcgt_get_coursemodules_types_in_course($courseID, $this->id, $groupingID);
                if($activities)
                {
                    $retval .= '<table class="bcgtmodkey" align="center">';
                    $retval .= '<tr>';
                    //i want thre columns
                    foreach($activities AS $activity)
                    {
                        $retval .= '<td>';
                        $icon = $OUTPUT->pix_url('icon', $activity->name);
                        $modIconArray[$activity->name] = $icon;
                        $retval .= html_writer::empty_tag('img', array('src' => $icon,
                                'class' => 'bcgtmodkeyicon activityicon', 'alt' => $activity->name));
                        $retval .= '<span class="bcgtmodkeymod"> '.$activity->name.'</span>';
                        $retval .= '</td>';
                    }
                    $retval .= '</tr>';
                    $retval .= '<tr><td colspan="'.count($activities).'">';
                    $retval .= '<span class="key"><img src="'.$CFG->wwwroot.
                    '/blocks/bcgt/images/redcross.png"/> = ';
                    $retval .= get_string('criterianolinkmod', 'block_bcgt');
                    if(has_capability('block/bcgt:manageactivitylinks', $currentContext))
                    {
                        $retval .= ' <span class="criterialinkextra"> ('.
                            get_string('criterianolinkextrainfo', 'block_bcgt').')</span>';
                    }
                    $retval .= '</td></tr>';
                    $retval .= '</table>';
                }
                $this->modIcons = $modIconArray;
                break;
            case"os":
                $retval .= '<span class="key"><img src="'.
                    $CFG->wwwroot.'/blocks/bcgt/images/greentick.png'.
                    '"/> = ';
                $retval .= get_string('criterialinkedmod', 'block_bcgt');
                $retval .= ' <span class="criterialinkextra"> ('.
                        get_string('criterialinkedextrainfo', 'block_bcgt').')</span>';
                $retval .= '</span>';
                $retval .= '<span class="key"><img src="'.$CFG->wwwroot.
                    '/blocks/bcgt/images/redcross.png"/> = ';
                $retval .= get_string('criterianolinkmod', 'block_bcgt');
                if(has_capability('block/bcgt:manageactivitylinks', $currentContext))
                {
                    $retval .= ' <span class="criterialinkextra"> ('.
                        get_string('criterianolinkextrainfo', 'block_bcgt').')</span>';
                }
                $retval .= '</span>';
                break;
            default:
                break;
        }
        $retval .= '</div>';
        return $retval;
    }

    protected function load_mod_info($courseID)
    {
        $modinfo = get_fast_modinfo($courseID);
//        print_object($modinfo);
        $activities = bcgt_get_coursemodules_in_course($courseID, $this->id);
        foreach($activities AS $activity)
        {
            $modnumber = $activity->id;
            $instancename = $activity->name;
            
            $mod = $modinfo->cms[$modnumber];
//            $mod->get_icon_url();
            if ($mod && $url = $mod->get_url()) {
                // Display link itself.
                //Accessibility: for files get description via icon, this is very ugly hack!
                $altname = $mod->modfullname;
                // Avoid unnecessary duplication: if e.g. a forum name already
                // includes the word forum (or Forum, etc) then it is unhelpful
                // to include that in the accessible description that is added.
                if (false !== strpos(textlib::strtolower($instancename),
                        textlib::strtolower($altname))) {
                    $altname = '';
                }
                // File type after name, for alphabetic lists (screen reader).
                if ($altname) {
                    $altname = get_accesshide(' '.$altname);
                }

                $groupinglabel = '';
                    if (!empty($mod->groupingid) && has_capability('moodle/course:managegroups', context_course::instance($courseID))) {
                        $groupings = groups_get_all_groupings($courseID);
                        $groupinglabel = html_writer::tag('span', '('.format_string($groupings[$mod->groupingid]->name).')',
                                array('class' => 'groupinglabel'));
                    }

                // Get on-click attribute value if specified and decode the onclick - it
                // has already been encoded for display (puke).
                $onclick = htmlspecialchars_decode($mod->get_on_click(), ENT_QUOTES);

                $activitylink = html_writer::empty_tag('img', array('src' => $mod->get_icon_url(),
                        'class' => 'iconlarge activityicon', 'alt' => $mod->modfullname)).
                        html_writer::tag('span', $instancename . $altname, array('class' => 'instancename'));
                echo html_writer::link($url, $activitylink, array('class' => '', 'onclick' => $onclick)) .
                        $groupinglabel;
            }
        }
    }
    
    public function get_gradebook_overview_data($courseID, $groupingID)
    {
        global $CFG, $OUTPUT, $COURSE;
        $currentContext = context_course::instance($COURSE->id);
        $retval = '';
        $criteriaNames = $this->usedCriteriaNames;
        $units = $this->units;
        $unitSorter = new UnitSorter();
		usort($units, array($unitSorter, "ComparisonDelegateByType"));
        $userID = optional_param('sID', -1, PARAM_INT);
        $view = optional_param('view', 'os', PARAM_TEXT);
        if($userID != -1)
        {
            //then we are displaying that users data
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELALL;
            $this->load_student_information($userID, $loadParams);
        }
        //lets get the data:
        //by unit:
        $rowCount = 0;
        $activityUnitCriteria = array();
        $activityUnits = Project::get_qual_mods_by_unit($courseID, $this->id, $groupingID);
        foreach($units AS $unit)
        {
            $rowCount++;
            if($activityUnits && array_key_exists($unit->get_id(), $activityUnits))
            {
                //do we have activities on this unit?
                $skipEntireUnit = false;
                $activityUnitCriteria = $activityUnits[$unit->get_id()];
            }
            else
            {
                //no well then dont check for any of the criteria 
                //on this unit then. 
                $skipEntireUnit = true;
            }
            $rowClass = 'rO';
            if($rowCount % 2)
            {
                $rowClass = 'rE';
            }
            $retval .= '<tr class="'.$rowClass.'">';
            $retval .= '<td>';
            if(has_capability('block/bcgt:manageactivitylinks', $currentContext))
            {
                $link = $CFG->wwwroot.'/blocks/bcgt/forms/add_activity.php?'.
                        'page=addact&uID='.$unit->get_id().'&cID='.$courseID.
                        '&fID='.BTECQualification::FAMILYID.'';
                $retval .= '<a href="'.$link.'"><img class="modcheckicon" src="'.
                    $CFG->wwwroot.'/blocks/bcgt/pix/activity.jpg" '.
                    'title="'.get_string('unitactivitytracker', 'block_bcgt').'"'.
                    'alt="'.get_string('unitactivitytracker', 'block_bcgt').'"/>'.
                    '</a>';
            }  
            $retval .= '</td>';
            $retval .= '<td>';
            if(has_capability('block/bcgt:viewclassgrids', $currentContext))
            {
                $link = $CFG->wwwroot.'/blocks/bcgt/grids/unit_grid.php?cID='.
                        $courseID.'&uID='.$unit->get_id().'&grID='.$groupingID.'&view=act&qID='.$this->id.'&order=act';
                $retval .= '<a href="'.$link.'"><img class="modcheckicon" src="'.
                    $CFG->wwwroot.'/blocks/bcgt/pix/trackericon.jpg" '.
                    'title="'.get_string('unitactivityselection', 'block_bcgt').'"'.
                    'alt="'.get_string('unitactivityselection', 'block_bcgt').'"/></a>';
            }
            $retval .= '</td>';
            $retval .= '<td>';
            $retval .= $unit->get_uniqueID().' : '.$unit->get_name();
            $retval .= '</td>';
            $previousLetter = '';
            foreach($criteriaNames AS $criteriaName)
            {
                $letter = substr($criteriaName, 0, 1);
                if($previousLetter != '' && $previousLetter != $letter)
                {
                    //if the criteria letter changes (i.e. P to M) then lets 
                    //create a divider. 
                    $retval .= "<td class='critdivider'><span class='critdivider'></span></td>";
                }
                $previousLetter = $letter;
                
                //is the criteriaName on the unit??
                $criteriaObject = $unit->get_single_criteria(-1, $criteriaName);
                if($criteriaObject)
                {
                    $courseModIDs = array();
                    $criteriaNoMod = false;
                    if(!$skipEntireUnit && array_key_exists($criteriaObject->get_id(), $activityUnitCriteria))
                    {
                        //then we have the criteria
                        //for this unit
                        //that is on an activity. 
                        $criteriaNoMod = false;
                        $courseModIDs = $activityUnitCriteria[$criteriaObject->get_id()];
                    }
                    else
                    {
                        $criteriaNoMod = true;
                    }
                    //are we in teacher simple mode:
                    //if it is, then just output a tick
                    //if the criteria isnt on an assignment then RED
                    if(!$criteriaNoMod)
                    {
                        if($userID != -1)
                        {
                            //then its the users view
                            //out put the symbol(s) for the mods
                            //colour code the border
                        }
                        elseif($view == 'subatt')
                        {
                            //output the statistics of how many
                            //IN, Marked, Total
                            $retval .= '<td>';
                            //go and get the total no student,
                            //total where its not N/A and not WNS
                            //total achieved. 
                            $noStudents = bcgt_get_criteria_submission_students(
                                    $criteriaObject->get_id(), $courseID, $this->id, 
                                    $groupingID);
                            $noAttempted = bcgt_get_criteria_submission_attempted(
                                    $criteriaObject->get_id(), $courseID, $this->id, $groupingID);
                            if($noStudents && $noAttempted)
                            {
                                $retval .= round(($noAttempted->count/$noStudents->count)*100,0).'%';
                            }
                            $retval .= '</td>';
                        }
                        elseif($view == 'subach')
                        {
                            //output the statistics of how many
                            //IN, Marked, Total
                            $retval .= '<td>';
                            //go and get the total no student,
                            //total where its not N/A and not WNS
                            //total achieved. 
                            $noStudents = bcgt_get_criteria_submission_students(
                                    $criteriaObject->get_id(), $courseID, $this->id, 
                                    $groupingID);
                            $noAchieved = bcgt_get_criteria_submission_achieved(
                                    $criteriaObject->get_id(), $courseID, $this->id, $groupingID);
                            if($noStudents && $noAchieved)
                            {
                                $retval .= round(($noAchieved->count/$noStudents->count)*100,0).'%';
                            }
                            $retval .= '</td>';
                        }
                        elseif($view == 'oa')
                        {
                            $retval .= '<td class="bcgtcritass">';
                            //output the icon. 
                            $mods = get_criteria_distinct_mods($criteriaObject->get_id(), 
                                    $courseID, $this->id, 
                                    $groupingID);
//                            print_object($mods);
                            if($mods)
                            {
                                $modIcons = $this->modIcons;
                                foreach($mods AS $mod)
                                {
                                    $retval .= '<span crit="'.$criteriaObject->get_id()
                                            .'" qual="'.$this->id.'"  group="'.$groupingID.
                                            '" course="'.$courseID.'" mod="'.$mod->name.
                                            '" class="bcgtcritass criteriamod">';
                                    
                                    if(array_key_exists($mod->name, $modIcons))
                                    {
                                        $icon = $modIcons[$mod->name];
                                    }
                                    $retval .= html_writer::empty_tag('img', array('src' => $icon,
                                        'class' => 'bcgtmodcriticon activityicon', 'alt' => $mod->name));
                                    if($mod->count != 1)
                                    {
                                        $retval .= '<sup>'.$mod->count.'</sup>';
                                    }
                                    $retval .= '</span>';
                                }
                            }
                            else
                            {
                               // output the question mark.  
                            }
                            $retval .= '</td>';
                        }
                        else
                        {
                            //the view must be overview simple
                            $retval .= '<td class="bcgtcritass"><span '.
                                    'crit="'.$criteriaObject->get_id()
                                    .'" qual="'.$this->id.'"  group="'.$groupingID.
                                    '" course="'.$courseID.'" mod="" '.
                                    'class="bcgtcritass criteriamod"><img src="'.
                                $CFG->wwwroot.'/blocks/bcgt/images/greentick.png'.
                                '"/></span></td>';//is this criteria on an assignment:
                        }
                        
                    }
                    else
                    {
                        if($userID == -1)
                        {
                            //then its not the users view. 
                            //just put the cross to denote not on 
                            //an assignment
                            $retval .= '<td course="'.$courseID.'" uID="'.
                                    $unit->get_id().'" fID="'.
                                    BTECQualification::FAMILYID.'" class="bcgtcritnoass">';
                            $retval .= '<span class="bcgtcritass criteriamodno">';
                            $retval .= '<span class="bcgtcritnoass">'.
                                '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/redcross.png'.
                                '"/>'.
                                '</span>';
                            $retval .= '</span>';
                            $retval .= '</td>';
                        }
                        else
                        {
                            //its the users view. 
                            //get the grid symbol. 
                        }
                        
                    }
                    
                    
                    //are we in teacher advanced mode:
                    //display the icons (plus number)
                    //on the grid. 
                    
                    //are we in student mode:
                    //display the icons with colour coding
                }
                else
                {
                    //this criteria isnt on the unit. 
                    $retval .= '<td class="unitnocrit"><span class="unitnocrit">';
                    $retval .= '</span></td>';
                }
                
                
            }    
                
            $retval .= '</tr>';
        }
        return $retval;
    }
    
    public static function add_activity_view_page($courseID, $unitID, $activityID)
    {
        $jsModule = array(
            'name'     => 'mod_bcgtbtec',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        global $PAGE, $CFG;
        $PAGE->requires->js_init_call('M.mod_bcgtbtec.btecaddactivity', null, true, $jsModule);
		
        
        $newUnitID = optional_param('nUID', -1, PARAM_INT);
        $newUnits = optional_param('newUnits', '', PARAM_TEXT);
        if($newUnitID != -1)
        {
            $newUnits .= ','.$newUnitID;
            $unitID = $newUnitID;
        }
        $newActivityID = optional_param('nAID', -1, PARAM_INT);
        $page = optional_param('page', 'addact', PARAM_TEXT);
        $newActvities = optional_param('newActivities', '', PARAM_TEXT);
        if($newActivityID != -1)
        {
            $newActvities .= ','.$newActivityID;
            $activityID = $newActivityID;
        }
        
        $activities = bcgt_get_coursemodules_in_course($courseID);
        $units = bcgt_get_course_units($courseID, BTECQualification::FAMILYID);
        $activityUnits = get_activity_units($activityID);
        
        //*************** ACTIVTIES*****************//
        //this is actually the coursemoduleid
        //lets load the unit up
        $unit = null;
        $qualsUnitOn = null;
        if($unitID != -1)
        {
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELCRITERIA;
            $unit = Unit::get_unit_class_id($unitID, $loadParams);
            $qualsUnitOn = $unit->get_quals_on('', -1, -1, $courseID );
        }
        
        // **********************************************************************************************
        // Show the correct form according to what is passed in aID (aka activityID) which defaults to -1
        // **********************************************************************************************
        
        if($page == 'addunit')
        {
            if(isset($_POST['saveUnitsAcc']))
            {
                //then we are saving the units on the activitues
                //we need to check the originals and get the new
                //new:            
                if($newUnits != '')
                {
                    $insertUnits = explode(',', $newUnits);
                    //then we are saving
                    foreach($insertUnits AS $uID)
                    {
                        if($uID == '')
                        {
                            continue;
                        }
                        //is it marked as delete
                        if(isset($_POST['remU_'.$uID]))
                        {
                            continue;
                        }
                        
                        bcgt_btec_process_mod_unit_selection($activityID, $uID, $courseID);
                    }
                    $newUnits = '';
                }
                //originals:
                if($activityUnits)
                {
                    foreach($activityUnits AS $activityUnit)
                    {
                        //is it marked as removed?
                        if(isset($_POST['remU_'.$activityUnit->id]))
                        {
                            //then lets delete it
                            delete_activity_from_unit($activityID, $activityUnit->id);
                            continue;
                        }
                        bcgt_btec_process_mod_selection_changes($activityID, $activityUnit->id, $courseID);
                    }
                }
                redirect($CFG->wwwroot.'/blocks/bcgt/forms/activities.php?cID='.$courseID.'&tab=act');
            }
            
        // **********************************************************************************************
        // ADD UNITS AND CRITERIA TO AN ASSIGNMENT ******************************************************
        // **********************************************************************************************
        
        //$retval = '<div class="bcgt_float_container bcgt_two_c_container">';
        //$retval .= '<div class="bcgt_col bcgt_admin_left">';
        $retval = '<div class="bcgt_col bcgt_unit_activities">';
        $retval .= '<h2>'.get_string('addunitsactivityheader', 'block_bcgt').'</h2>';
        $retval .= '<div class="bcgt_col bgctSelectActivity">';
        $retval .= '<label for="aID">'.get_string('selectactivity', 'block_bcgt').' : </label>';
        $retval .= '<select name="aID" id="aID">';
        $retval .= '<option value="-1"></option>';
        //get the activities on the course
        if($activities)
        {
            foreach($activities AS $activity)
            {
                $selected = '';
                if($activityID == $activity->id)
                {
                    $selected = 'selected';
                }
                $retval .= '<option '.$selected.' value="'.$activity->id.'">'.$activity->name.' ('.$activity->module.')</option>';
            }
        }
        $retval .= '</select><br />';
        $retval .= '</div>';
        $retval .= '<h3>'.get_string('currentactivityselection', 'block_bcgt').'</h3>';
        //then load the units currently on the activity
        if($activityUnits)
        {
            foreach($activityUnits AS $activityUnit)
            { 
                $retval .= '<div class="bcgt_col bgctActivityUnit">';
                $retval .= get_btec_activity_unit_table($activityID, $activityUnit->id, $courseID);
                $retval .= '</div>';
            }
        }
        else
        {
            $retval .= get_string('nocurrentactivityselection', 'block_bcgt');
        }
        //then allow them to be added.
        $retval .= '<h3>'.get_string('newunitscriteria', 'block_bcgt').'</h3>';
        //do we have any to remove?
        $finUnits = '';
        if($newUnits != '')
        {
            
            $newUnits = explode(',', $newUnits);
            $count = 0;
            foreach($newUnits AS $newUnit)
            {
                if($newUnit != '')
                {
                    if(isset($_POST['remU_'.$newUnit.'']))
                    {
                        //if remove has been selected then delete it from the array
                        unset($newUnits[$count]);
                    }
                    else
                    {
                        $finUnits .= ','.$newUnit;
                    }
                }
                $count++;
            }
        }
        else
        {
            $newUnits = array();
        }
        $retval .= '<div class="bcgt_col bgctSelectActivity">';
        $retval .= '<label>'.get_string('addnewunit', 'block_bcgt').' : </label><br />';
        $retval .= '<select name="nUID" id="nUID">';
        $retval .= '<option value="-1"></option>';
        //get the units on the course there are the new ones that can be selected
        if($units)
        {
            foreach($units AS $newUnit)
            {
                //do we already have it in the new ones being added?
                if(!in_array ($newUnit->id, $newUnits) && !array_key_exists($newUnit->id, $activityUnits))
                {
                    $retval .= '<option value="'.$newUnit->id.'">'.$newUnit->name.'</option>';
                }
            }
        }
        $retval .= '</select>';
        $retval .= '<input type="hidden" name="newUnits" value="'.$finUnits.'"/>';
        $retval .= '<input type="hidden" name="page" value="'.$page.'"/>';
        $retval .= '<input type="submit" name="addUnit" value="'.get_string('addunit', 'block_bcgt').'"/>';
        $retval .= '</div>';
        
        foreach($newUnits AS $newUnitID)
        {
            if($newUnitID == '')
            {
                continue;
            }
            $retval .= '<div class="bcgt_col bgctActivityUnit">';
            $retval .= get_btec_activity_unit_table($activityID, $newUnitID, $courseID, true);
            $retval .= '</div>';
        }
        $retval .= '<br /><br /><input class="save" type="submit" name="saveUnitsAcc" value="'.get_string('saveassignment', 'block_bcgt').'"/>';
        
        $retval .= '</div>';
        // **********************************************************************************************    
        } 
        else {
            //******************** UNITS ****************************//
            //// Show the form: Activity -> unit & criteria instead
            //
            //i.e. we have a list of activities and we want to add units and criteria
        // **********************************************************************************************     
            $unitActivities = BTECQualification::get_unit_activities($courseID, $unitID);
            if(isset($_POST['saveAccUnits']) && $unit)
            {
                //then we are saving the activities on the Unit
                //we need to check the originals and get the new
                //new:
                if($newActvities != '')
                {
                    $insertActivities = explode(',', $newActvities);
                    //then we are saving
                    foreach($insertActivities AS $cmID)
                    {
                        //is it marked as delete
                        if(isset($_POST['rem_'.$cmID]))
                        {
                            continue;
                        }
                        $criterias = $unit->get_criteria();
                        $criteriasUsed = array();
                        //I really dont want to loop through all criteria for every qual possible
                        foreach($criterias AS $criteria)
                        {
                           if(isset($_POST['a_'.$cmID.'_c_'.$criteria->get_id().'']))
                           {
                               //then we want to insert it
                               $criteriasUsed[] = $criteria->get_id();
                           }
                        }
                        //is it on a qual?
                        foreach($qualsUnitOn AS $qual)
                        {
                            if(isset($_POST['q_'.$qual->id.'_a_'.$cmID]))
                            {
                                //is on this qual so lets insert it. 
                                //we need to get the criteriaIDs. We know the unitID
                                $stdObj = new stdClass();
                                $stdObj->coursemoduleid = $cmID;
                                $stdObj->bcgtunitid = $unitID;
                                $stdObj->bcgtqualificationid = $qual->id;
                                foreach($criteriasUsed AS $criteriaID)
                                {
                                    $stdObj->bcgtcriteriaid = $criteriaID;
                                    insert_activity_onto_unit($stdObj);
                                }
                            }
                        }
                    }
                    $newActvities = '';
                }
                //originals:
                if($unitActivities)
                {
                    foreach($unitActivities AS $unitActivity)
                    {
                        //is it marked as removed?
                        if(isset($_POST['rem_'.$unitActivity->id]))
                        {
                            //then lets delete it
                            delete_activity_from_unit($unitActivity->id, $unitID);
                            continue;
                        }
                        //now check quals. 
                        foreach($qualsUnitOn AS $qual)
                        {
                            //is it checked?
                            if(!isset($_POST['q_'.$qual->id.'_a_'.$unitActivity->id]))
                            {
                                unset($qualsUnitOn[$qual->id]);
                                delete_activity_by_qual_from_unit($unitActivity->id, $qual->id, $unitID);
                            }
                        }
                        $criterias = $unit->get_criteria();
                        foreach($criterias AS $criteria)
                        {
                            //was it checked before?
                            $criteriaOnActivity = get_activity_criteria($unitActivity->id, $qualsUnitOn);
                            if(isset($_POST['a_'.$unitActivity->id.'_c_'.$criteria->get_id()])
                                    && !array_key_exists($criteria->get_id(), $criteriaOnActivity))
                            {
                                //so its been checked and it wasnt in the array from the database
                                //therefore INSERT!
                                foreach($qualsUnitOn AS $qual)
                                {
                                    $stdObj = new stdClass();
                                    $stdObj->coursemoduleid = $unitActivity->id;
                                    $stdObj->bcgtunitid = $unitID;
                                    $stdObj->bcgtqualificationid = $qual->id;
                                    $stdObj->bcgtcriteriaid = $criteria->get_id();
                                    insert_activity_onto_unit($stdObj);
                                }
                            }
                            elseif(!isset($_POST['a_'.$unitActivity->id.'_c_'.$criteria->get_id()])
                                    && array_key_exists($criteria->get_id(), $criteriaOnActivity))
                            {
                                //its in the array from before and its no longer checked!
                                //therefore delete
                                delete_activity_by_criteria_from_unit($unitActivity->id, $criteria->get_id(), $unitID);
                            }
                            //is it checked? 
                        }
                    }
                }
                redirect($CFG->wwwroot.'/blocks/bcgt/forms/activities.php?cID='.$courseID.'&tab=unit');
            }
        
        // **********************************************************************************************
        // ADD ASSIGNMENT TO A UNIT AND CRITERIA ******************************************************
        // **********************************************************************************************
        //$retval .= '<div class="bcgt_col bcgt_admin_right">';
        $retval = '<div class="bcgt_col bcgt_unit_activities">';
        $retval .= '<h2>'.get_string('addunitassignment', 'block_bcgt').'</h2>';
        $retval .= '<div class="bcgt_col bgctSelectActivity">';
        $retval .= '<label for="aID">'.get_string('selectunit', 'block_bcgt').' : </label>';
        $retval .= '<select name="uID" id="uID">';
        $retval .= '<option value="-1"></option>';
        //get the units on the course
        if($units)
        {
            foreach($units AS $unitOnCourse)
            {
                $selected = '';
                if($unitID == $unitOnCourse->id)
                {
                    $selected = 'selected';
                }
                $retval .= '<option '.$selected.' value="'.$unitOnCourse->id.'">'.$unitOnCourse->name.'</option>';
            }
        }
        $retval .= '</select>';
        $retval .= '</div>';
        $retval .= '<div class="currentUnitActivities currentSelections">';
        $retval .= '<h3>'.get_string('currentactivities', 'block_bcgt').'</h3>';
        
        //want the due date and the icon. 
        $modLinking = load_bcgt_mod_linking();
        $modIcons = load_mod_icons($courseID, -1, -1, -1);
        
        //load the assignments already on this unit. 
        $unitActivities = BTECQualification::get_unit_activities($courseID, $unitID);
        if($unitActivities)
        {
            //sort them
            foreach($unitActivities AS $unitActivity)
            {
                $dueDate = get_bcgt_mod_due_date($unitActivity->id, $unitActivity->instanceid, $unitActivity->cmodule, $modLinking);
                $unitActivity->dueDate = $dueDate; 
            }
            
            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ModSorter.class.php');
            $modSorter = new ModSorter();
            usort($unitActivities, array($modSorter, "ComparisonDelegateByDueDateObj"));
            
            foreach($unitActivities AS $unitActivity)
            {
                $retval .= '<div class="bcgt_col bgctActivityUnit">';
                //get the activity and the criteria
                $retval .= get_btec_unit_activity_table($unitActivity->id, $unit, $courseID, false, $unitActivity, $modLinking, $modIcons);
                $retval .= '</div>';
            }
        }
        else
        {
            $retval .= get_string('noactivitiesunit','block_bcgt');
        }
        $retval .= '</div>';
        $retval .= '<h3>'.get_string('addactivity','block_bcgt').'</h3>';
        //do we have any to remove?
        $finActivities = '';
        if($newActvities != '')
        {
            $newActvities = explode(',', $newActvities);
            $count = 0;
            foreach($newActvities AS $newActivity)
            {
                if($newActivity == '')
                {
                    continue;
                }
                if(isset($_POST['rem_'.$newActivity.'']))
                {
                    //if remove has been selected then delete it from the array
                    unset($newActvities[$count]);
                }
                else
                {
                    $finActivities .= ','.$newActivity;
                }
                $count++;
            }
        }
        else
        {
            $newActvities = array();
        }
        $retval .= '<div class="bcgt_col bgctSelectActivity">';
        $retval .= '<label>'.get_string('addactivity','block_bcgt').' : </label><br />';
        $retval .= '<select name="nAID" id="aID">';
        $retval .= '<option value="-1"></option>';
        //get the activities on the course
        //loop through them and show the, BUT dont show where they are already on this unit
        //dont show where they are being added next.
        if($activities)
        {
            foreach($activities AS $activity)
            {
                if(!in_array($activity->id, $newActvities) && !array_key_exists($activity->id, $unitActivities))
                {
                    $retval .= '<option value="'.$activity->id.'">'.$activity->name.' ('.$activity->module.')</option>';
                }
            }
        }
        $retval .= '</select>';
        //print_object($finActivities);
        $retval .= '<input type="hidden" name="newActivities" value="'.$finActivities.'"/>';
        $retval .= '<input type="hidden" name="page" value="'.$page.'"/>';
        $retval .= '<input type="submit" name="addAcc" value="'.get_string('addactivity','block_bcgt').'"/>';
        $retval .= '</div>';
        foreach($newActvities AS $newActivityID)
        {
            if($newActivityID == '')
            {
                continue;
            }
            $retval .= '<div class="bcgt_col bgctActivityUnit">';
            $retval .= get_btec_unit_activity_table($newActivityID, $unit, $courseID, true);
            $retval .= '</div>';
        }
        $retval .= '<br /><br /><input type="submit" class="save" name="saveAccUnits" value="'.get_string('saveassignment','block_bcgt').'"/>';
        $retval .= '</div>'; 
        $retval .= '</div>'; 
        
    // **********************************************************************************************  
   } // Finished with the forms
   // ********************************************************************************************** 
    return $retval; //Prints out the forms
   
    }
    
    public static function activity_view_page($courseID, $tab)
    {
        if($tab == 'unit')
        {
            return btec_activity_by_unit_page($courseID);
        }
        elseif($tab == 'act')
        {
            return btec_activity_by_activity_page($courseID);
        }
    }

    public function get_activities_grid($courseID, $activityID = -1)
    {
        global $DB;
        $retval = '';
        $grid = optional_param('grid', 's', PARAM_TEXT);
        $retval .= '<div class="activityqualgrid">';
        $retval .= '<h2>'.$this->get_display_name().'</h2>';
        //get all students on this qual
        $activities = null;
        if($activityID != 0)
        {
            $role = $DB->get_record_sql('SELECT * FROM {role} WHERE shortname = ?', array('student'));
            $students = $this->get_users($role->id);
            $activities = $this->get_activities($courseID, $activityID);
        }
        if($activities)
        {
            $columnsLocked = 2;
            $configColumns = get_config('bcgt','btecgridcolumns');
            if($configColumns)
            {
                $columns = explode(",",$configColumns);
                $columnsLocked += count($columns);
            }
            else
            {
                $columnsLocked += count($this->defaultColumns);
            }
            $configColumnWidth = get_config('bcgt','bteclockedcolumnswidth');
            $jsModule = array(
                'name'     => 'mod_bcgtbtec',
                'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
                'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
            );
            global $PAGE;
            $PAGE->requires->js_init_call('M.mod_bcgtbtec.initactivityqualgrid', 
                    array($this->id, $activityID, $grid, $columnsLocked, $configColumnWidth, $courseID), true, $jsModule);
        
            //set the edit boxes up
            $retval .= '<div>';
            $retval .= $this->get_grid_global_buttons($courseID);
            $retval .= '<input type="hidden" name="grid'.$this->id.'" id="grid'.$this->id.'"';
            $page = optional_param('page', 1, PARAM_INT);
            $pageRecords = get_config('bcgt','pagingnumber');
            if($pageRecords != 0)
            {
                //then we are paging
                //need to count the total number of students and divide by the paging number
                $totalNoStudents = count($students);
                $noPages = $totalNoStudents/$pageRecords;
                $retval .= '<p>'.get_string('pagenumber', 'block_bcgt').' : ';
                for($i=1;$i<=$noPages;$i++)
                {
                    $retval .= '<a class="unitgridpage" page="'.$i.'" href="#&page='.$i.'">'.$i.', </a>';
                }
                $retval .= '</p>';
            }
            global $CFG, $COURSE;
            $context = context_course::instance($COURSE->id);
            if($courseID != -1)
            {
                $context = context_course::instance($courseID);
            }
            $retval .= '<input type="hidden" name="pageInput" id="pageInput" value="'.$page.'"/>';
            if(has_capability('block/bcgt:viewajaxrequestdata', $context))
            {
                $retval .= '<ul>';
                $retval .= '<li><a target="_blank" href="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec/ajax/get_qual_activity_grid.php?qID='.$this->id.'&cID='.$courseID.'&aID='.$activityID.'&g='.$grid.'&page='.$page.'&showtable=true">'.get_string('ajaxrequest', 'block_bcgt').'</a></li>';
                $retval .= '</ul>';

            }
            
            $retval .= '</div>';
            $editing = false;
            $advancedMode = false;
            if($grid == 'ae' || $grid == 'se')
            {
                $editing = true;
            }
            if($grid == 'a' || $grid == 'ae')
            {
                $advancedMode = true;
            }  
            //the key
            $retval .= "
            <div class='gridKey adminRight'>";
            $retval .= "<h2>Key</h2>";
            $retval .= BTECQualification::get_grid_key();
            $retval .= "</div>";
            //the header
            $retval .= "<div id='activitiesBTECDiv".$this->id." class='activitiesGridDiv ".
            $grid."activitiesGrid tableDiv btecActivitiesGrid'>";
            $retval .= "<table align='center' class='activities_grid btecActivityGrid ".
                    $grid."FixedTables' id='btecActivityGrid".$this->id."'>";
            
            //need to sort the activities by their sort date (if there are more than one to show)
            $activitiesOnQual = $this->get_sort_activity_details($activities);
            
            $header = $this->get_grid_activities_header($activitiesOnQual, $grid);
            $retval .= $header;

            $retval .= "<tbody>";
            //the body is loaded through an ajax call. This ajax call
            //is called in the js file of bcgtbtec.js and is in the initstdentgrid
            //it calls ajax and calls ajax/get_qual_activity_grid.php
            $retval .= "</tbody>";
            $retval .= "</table>";
            //then set the table up -> ajax call
        }
        else
        {
            $retval .= '';
        }
        $retval .= '</div>';
        return $retval;
    }
    
    //this so needs to be done on a session!!!!!!!!!!!
    public function get_qual_activity_grid_data($courseID, $activityID, $advancedMode, $editing)
    {
        //dont forget paging!
        //so we need to get the students, and the activities, and the units etc etc
        //need to get the data for the students. 
        
        //for each student create a qual object
        //then for object, get the unit, criteria etc for it
        
        $activities = null;
        if($activityID != 0)
        {
            global $DB;
            $role = $DB->get_record_sql('SELECT * FROM {role} WHERE shortname = ?', array('student'));
            $students = $this->get_users($role->id);
            $activities = $this->get_activities($courseID, $activityID);
        }
        if($activities)
        {
            $possibleValues = null;
            if($editing && $advancedMode)
            {
                $possibleValues = BTECQualification::get_possible_values(BTECQualification::ID);
            }
            
            $pageNumber = optional_param('page',1,PARAM_INT);
            if(get_config('bcgt','pagingnumber') != 0)
            {
                $pageRecords = get_config('bcgt','pagingnumber');
                //then we only want a certain number!
                //we also need to take into account the page number we are on. 
                $keys = array_keys($students);
                $studentsShowArray = array();
                if($pageNumber == 1)
                {
                   $i = 0; 
                }
                else
                {
                    $i = $pageRecords * $pageNumber;
                }
                $recordsEnd = ($i + $pageRecords);
                for($i;$i<=$recordsEnd;$i++)
                {
                    //gets the student object from the array by the key that we are looking at.
                    $student = $students[$keys[$i]];
                    $studentsShowArray[$keys[$i]] = $student;
                }
            }
            else {
                $studentsShowArray = $students;
            }
            $rowCount = 0;
            //get and orderby
            $activitiesOnQual = $this->get_sort_activity_details($activities);
//            echo count($activitiesOnQual).'<br />';
            foreach($activitiesOnQual AS $activity)
            {
                $activity->units = array();
                $activityID = $activity->bcgtactivityid;
                $activityUnits = get_activity_units($activityID);
//                echo count($activityUnits).'<br />';
                if($activityUnits)
                {
                    foreach($activityUnits AS $activityUnit)
                    {
                        $activityCriterias = get_activity_units_criteria($activityID, $activityUnit->id);
//                        echo count($activityCriterias).'<br /><br /><br />';
                        $activity->units[$activityUnit->id] = $activityCriterias;
                    }
                }
            }
            //foreach assignment
            //get the units and the criteria
            //have these in an array???
            $retval = array();
            foreach($studentsShowArray AS $student)
            {
                $row = array();       
                $rowCount++;
                $rowClass = 'rO';
                if($rowCount % 2)
                {
                    $rowClass = 'rE';
                }	
                $row = $this->build_unit_grid_students_details($student, $this->id, 
                    $row);
                
                //load a student qual object up
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELALL;
                $this->load_student_information($student->id, $loadParams);
                foreach($activitiesOnQual AS $activity)
                {
                    $units = $activity->units;
                    foreach($units AS $unitID => $criterias)
                    {
                        $unitObject = $this->units[$unitID];
                        foreach($criterias AS $criteria)
                        {
                            $criteriaObject = $unitObject->get_single_criteria($criteria->id);
                            $row = $this->set_up_activity_criteria_grid($criteriaObject, '', $student, 
                                $possibleValues, $editing, $advancedMode, '', $row, $this->id);
                            
                        }
                    }
                }
                $retval[] = $row;
            }//end for each student
            //need to sort the activities by their sort date (if there are more than one to show)
        }
        return $retval;
        
    }
    
    protected function set_up_activity_criteria_grid($criteria, $extraCellClass, $student, 
        $possibleValues, $editing, $advancedMode, $firstLast, $row, $qualID)
    {
        global $CFG;
        $retval = "";
        $studentComments = '';
        $criteriaName = $criteria->get_name();
        if($criteria->get_comments() && $criteria->get_comments() != '')
        {
            $studentComments = $criteria->get_comments();
        }	
        else
        {
            $studentComments = $criteria->load_comments();
        }
        $studentValueObj = $criteria->get_student_value();	
        if(!$studentValueObj)
        {
            //then we need to create a default one to put in the database and get the symbol for
            $studentValueObj = new Value();
            $studentValueObj->create_default_object('N/A', BTECQualification::FAMILYID);
        }
        $cellClass = 'noComments';
        $comments = false;
        if($studentComments != '')
        {
            $cellClass = 'criteriaComments';
            $comments = true;
        }				
        if($editing)
        {
            if($advancedMode)
            {	
                $row = $this->advanced_editing_grid($student, 
                        $criteria, $possibleValues, $studentValueObj, 
                        $studentComments, $extraCellClass, $firstLast, $row, $qualID);
            }
            else //editing but simple mode
            {
                $retval .= $this->simple_editing_grid($student, 
                    $studentValueObj, $criteria, $qualID);
                $row[] = $retval;
            }
        }
        else //NOT EDITING
        {
            if($advancedMode)
            {
                $class = $studentValueObj->get_short_value();
                $shortValue = $studentValueObj->get_short_value();
                if($studentValueObj->get_custom_short_value())
                {
                    $shortValue = $studentValueObj->get_custom_short_value();
                }
                $retval .= "<span id='cID_".$criteria->get_id().
                "_uID_".$this->id."_sID_".$student->id."_qID_".$qualID."' ".
                        "class='stuValue stuValueNonEdit $class'>".$shortValue."</span>";
                if (!is_null($studentComments) && $studentComments != ''){
                    $retval .= "<div class='tooltipContent'>".nl2br( htmlentities($studentComments, ENT_QUOTES) )."</div>";
                }
                $row[] = $retval;
            }
            else //not editing but simple mode
            {
                $imageObj = BTECQualification::get_simple_grid_images($studentValueObj);
                $image = $imageObj->image;
                $class = $imageObj->class;
                $retval .= "<span id='cID_".$criteria->get_id().
                "_uID_".$this->id."_sID_".$student->id."_qID_".$qualID."' ".
                        "class='stuValue stuValueNonEdit $class'><img src='".$CFG->wwwroot."/blocks/bcgt/plugins/bcgtbtec$image'></span>";
                if (!is_null($studentComments) && $studentComments != ''){
                    $retval .= "<div class='tooltipContent'>".nl2br( htmlentities($studentComments, ENT_QUOTES) )."</div>";
                }
                $row[] = $retval;
            }	
        }//end else not editing
        return $row;
    }
    
    protected function build_unit_grid_students_details($student, $qualID, $row)
	{
		global $CFG, $printGrid, $OUTPUT;
		   
        //columns supported are:
        //picture,username,name,firstname,lastname,email
        $columns = $this->defaultColumns;
        $configColumns = get_config('bcgt','btecgridcolumns');
        $link = $CFG->wwwroot.'/blocks/bcgt/grids/student_grid.php?qID='.$qualID.'&sID='.$student->id;  
        //need to get the global config record
        
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
        }
        foreach($columns AS $column)
        {
            $content = '<a href="'.$link.'" class="studentUnit" title="" id="sID_'.
                    $student->id.'_qID_'.$qualID.'">';
            switch(trim($column))
            {
                case("picture"):
                    $content .= $OUTPUT->user_picture($student, array('size' => 25));
                    break;
                case("username"):
                    $content .= $student->username;
                    break;
                case("name"):
                    $content .= $student->firstname." ".$student->lastname;
                    break;
                case("firstname"):
                    $content .= $student->firstname;
                    break;
                case("lastname"):
                    $content .= $student->lastname;
                    break;
                case("email"):
                    $content .= $student->email;
                    break;
            }
            $content .= '</a>';
            
            $row[] = $content;
        }
		
		return $row;	
	}
    
    protected function get_activities($courseID, $activityID = -1)
    {
        return bcgt_get_activities_on_course($courseID, $this->id, -1, 'assign', 
        '', '', $activityID);
    }
    
    protected function get_sort_activity_details($activities)
    {
        $retval = array();
        //we need to get the details. 
        foreach($activities AS $activity)
        {
            $activityDetails = bcgt_get_module_from_course_mod($activity->id);
            if($activityDetails)
            {
                $activityDetails->bcgtactivityid = $activity->id;
                $retval[$activity->id] = $activityDetails;
            }
        }
        global $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ActivitySorter.class.php');
        $activitySorter = new ActivitySorter();
		usort($retval, array($activitySorter, "ComparisonDelegateByArrayDate"));
        
        return $retval;
    }
    
    protected function get_grid_activities_header($activities, $grid)
    {
        $editing = false;
        $advancedMode = false;
        if($grid == 'es' || $grid == 'ea')
        {
            $editing = true;
        }
        if($grid == 'a' || $grid = 'ea')
        {
            $advancedMode = true;
        }
        global $printGrid;
//		$headerObj = new stdClass();
		$header = '';
		//extra one for projects
		$header .= "<thead>";
        //columns supported are:
        //picture,username,name,firstname,lastname,email
        $columns = $this->defaultColumns;
        //need to get the global config record
        
        $configColumns = get_config('bcgt','btecgridcolumns');
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
        }
        $headerRow1 = '';
        $headerRow2 = '';
        $headerRow3 = '';
        foreach($columns AS $column)
        {
            $headerRow1 .= '<th rowspan="3">';
            $headerRow1 .= get_string(trim($column), 'block_bcgt');
            $headerRow1 .= '</th>';
        }
        foreach($activities AS $activity)
        {
            $activityID = $activity->bcgtactivityid;
            //now get units and criteria
            //eventually this is to be done in ONE call!
            $countOfCriteria = 0;
            $activityUnits = get_activity_units($activityID);
            if($activityUnits)
            {
                foreach($activityUnits AS $activityUnit)
                {
                    $activityCriterias = get_activity_units_criteria($activityID, $activityUnit->id);
                    $headerRow2 .= '<th colspan="'.count($activityCriterias).
                            '">'.$activityUnit->name.'</th>';
                    foreach($activityCriterias AS $criteria)
                    {
                        $headerRow3 .= '<th>'.$criteria->name.'</th>';
                    }
                    $countOfCriteria = $countOfCriteria + count($activityCriterias);
                }
            }
            $headerRow1 .= '<th colspan="'.$countOfCriteria.'">'.$activity->name.' ('.date('d/m/y', $activity->duedate).')</th>';
            
        }
        $header .= '<tr>'.$headerRow1.'</tr><tr>'.$headerRow2.'</tr><tr>'.$headerRow3.'</tr>';
        
        $header .= '</thead>';
        
        return $header;
    }
    
    public static function get_unit_activities($courseID, $unitID)
    {
        //this needs to get all of the activities for this course for this unit
        //order by due date
        return bcgt_unit_activities($courseID, $unitID);
        
    }
    
    public function display_activity_grid($activities)
    {
        global $COURSE, $PAGE, $CFG;
        $retval = '<div>';
        $retval .= '<div class="bcgtgridbuttons">';
        $retval .= "<input type='submit' id='viewsimple' class='gridbuttonswitch viewsimple' name='viewsimple' value='View Simple'/>";
        $retval .= "<input type='submit' id='viewadvanced' class='gridbuttonswitch viewadvanced' name='viewadvanced' value='View Advanced'/>";
        $retval .= "<br>";
        $courseID = optional_param('cID', -1, PARAM_INT);
        $groupingID = optional_param('grID', -1, PARAM_INT);
        $scID = optional_param('scID', -1, PARAM_INT);
        $qualID = optional_param('qID', -1, PARAM_INT);
        //this is actually the coursemoduleid
        $cmID = optional_param('cmID', -1, PARAM_INT);
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        if(has_capability('block/bcgt:editunitgrid', $context))
        {	
            $retval .= "<input type='submit' id='editsimple' class='gridbuttonswitch editsimple' name='editsimple' value='Edit Simple'/>";
            $retval .= "<input type='submit' id='editadvanced' class='gridbuttonswitch editadvanced' name='editadvanced' value='Edit Advanced'/>"; 
        }
        $late = optional_param('late', false, PARAM_BOOL);
        $grid = optional_param('g', 's', PARAM_TEXT);
        $retval .= '<input type="hidden" id="grid" name="g" value="'.$grid.'"/>';
        $editing = false;
        if($grid == 'ae' || $grid == 'se')
        {
            $editing = true;
        }
//        if($grid == 's' && has_capability('block/bcgt:viewbteclatetracking', $context))
//        {
//            $retval .= '<br /><span id="showLateFunc">Show Late History : ';
//            $retval .= '<input type="checkbox" name="late" id="showlate"';
//            if($late)
//            {
//                $retval .= ' checked="checked" ';
//            }
//            $retval .= '/></span>';
//        }
        
        $page = optional_param('page', 1, PARAM_INT);
        $pageRecords = get_config('bcgt','pagingnumber');
        if($pageRecords != 0)
        {
            //then we are paging
            //need to count the total number of students and divide by the paging number
            //load the session object
            //load the students that are on this unit for this qual. 
            //need to add this to the array
            $studentsArray = bcgt_get_users_on_coursemodules($this->id,$scID, $groupingID, $cmID);
            $this->students = $studentsArray;
            $totalNoStudents = count($studentsArray);
            $noPages = ceil($totalNoStudents/$pageRecords);
            $retval .= '<div class="bcgt_pagination">'.get_string('pagenumber', 'block_bcgt').' : ';
                
                for ($i = 1; $i <= $noPages; $i++)
                {
                    $class = ($i == 1) ? 'active' : '';
                    $retval .= "<a class='unitgridpage pageNumber {$class}' page='{$i}' href='#&page={$i}'>{$i}</a>";
                }
            
            $retval .= '</div>';
        }
        $retval .= '<input type="hidden" name="pageInput" id="pageInput" value="'.$page.'"/>';
        if(has_capability('block/bcgt:viewajaxrequestdata', $context))
        {
            $retval .= '<ul>';
            $retval .= '<li><a target="_blank" href="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec/ajax/get_act_grid.php?qID='.$this->id.'&g='.$grid.'&page='.$page.'&cmID='.$cmID.'&html=true">'.get_string('ajaxrequest', 'block_bcgt').'</a></li>';
            $retval .= '</ul>';

        }
        //we need to work out how many columns are being locked and
        //what the widths are
        //default is columns (assignments, comments)
        $columnsLocked = 2;
        $configColumns = get_config('bcgt','btecgridcolumns');
        if($configColumns)
        {
            $columns = explode(",",$configColumns);
            $columnsLocked += count($columns);
        }
        else
        {
            $columnsLocked += count($this->defaultColumns);
        }
        $configColumnWidth = get_config('bcgt','bteclockedcolumnswidth');
        $jsModule = array(
            'name'     => 'mod_bcgtbtec',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        $PAGE->requires->js_init_call('M.mod_bcgtbtec.initactgrid', array($this->id,$grid,$scID, $groupingID, $columnsLocked, $configColumnWidth, $cmID), true, $jsModule);
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        load_javascript(true);
        $retval .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/css/start/jquery-ui-1.10.3.custom.min.css' />";
        $retval .= '</div>'; //bcgtgridbuttons
        $retval .= "
		<div class='gridKey adminRight'>";
        $retval .= "<h2>Key</h2>";
        //Are we looking at a student or just the actual criteria for the grid.
        //if students then get the key that tells everyone what things stand for
        $retval .= BTECQualification::get_grid_key();
		$retval .= "</div>";
        
        //the grid -> ajax
        $retval .= '<div id="btecActGrid">';
        
        
        $retval .= "<div id='actGridDiv' class='actGridDiv ".
        $grid."ActGrid tableDiv'><table align='center' class='act_grid".
                $grid."FixedTables' id='BTECActGrid'>";
		$headerObj = $this->get_act_grid_header($activities, $grid);
		$header = $headerObj->header;	
		$retval .= $header;
        
		$retval .= "<tbody>";
        //the body is loaded through an ajax call
        $retval .= "</tbody>";
        $retval .= "</table>";
        $retval .= "</div>";        
        $retval .= '</div>';
        $retval .= '</div>';
        //Edit/Advanced etc options
    
        //four buttons. On click it needs to resubmit the table draw. 
        //and it needs to potentially redraw the key? 
        //Grid with a key

        
        
        //the buttons.
        return $retval;
    }
    
    public function get_act_grid_data($advancedMode, 
            $editing)
    {
        //get the activities that we are looking at
        //activities = $this->activities
        
        //get the students:
        //students = $this->activityStudents 
        //foreach students
        //foreach activity
        //foreach unit on the activity
        //is the student doing the unit?
        //for each criteria
        //output students marks. 
        
        $studentsArray = $this->students;
        $activities = $this->activities;
        
        //pagig
        $pageNumber = optional_param('page',1,PARAM_INT);
        global $CFG, $DB, $COURSE;
        $context = context_course::instance($COURSE->id);
        $courseID = optional_param('cID', -1, PARAM_INT);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }        
        //need to get the order:
        $order = optional_param('order', 'btec', PARAM_TEXT);
        if($order == 'act')
        {
            //get the full list of criteriaNames
            $criteriaNames = $this->criteriaNamesUsedAct;
        }
        else
        {
            $criteriaNames = $this->get_used_criteria_names();
            //Get this units criteria names and sort them. 
            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
            $criteriaSorter = new CriteriaSorter();
            usort($criteriaNames, array($criteriaSorter, "ComparisonDelegateByArrayNameLetters"));
        }
        
        $retArray = array();
        $possibleValues = null;
        if(!isset($this->possibleValues) && $editing && $advancedMode)
        {
            $possibleValues = BTECQualification::get_possible_values(BTECQualification::ID);
            $this->possibleValues = $possibleValues;
        }
        if(get_config('bcgt','pagingnumber') != 0)
        {
            $pageRecords = get_config('bcgt','pagingnumber');
            //then we only want a certain number!
            //we also need to take into account the page number we are on.
            //studentsArray is the array of students on the unit on this qual. 
            //the keys are the ids of the students. 
            $keys = array_keys($studentsArray);
            //arrays keys returns an array of the keys of the first aray. This return aray has its keys set to 
            //the numerical order, e.g. always starting at 0, then 1 etc.  
            
            $studentsShowArray = array();
            //are we at the first page, 
            if($pageNumber == 1)
            {
               $i = 0; 
            }
            else
            {
                //no so we want to start at the page number times by how many we show per page
                $i = ($pageRecords * ($pageNumber - 1)) + ($pageNumber - 1);
            }
            //we want to loop over and only show the number of students in our page size. 
            $recordsEnd = ($i + $pageRecords);
            for($i;$i<=$recordsEnd;$i++)
            {
                //gets the student object from the array by the key that we are looking at.
                if (isset($keys[$i]) && isset($studentsArray[$keys[$i]]))
                {
                    //so, if we have the student id for the nth student we need. 
                    //then find the student that that id coresponds to from our original array of students. 
                    $student = $studentsArray[$keys[$i]];
                    //add this student to the array that we want to display.
                    $studentsShowArray[$keys[$i]] = $student;
                }
            }
        }
        else {
            $studentsShowArray = $studentsArray;
        }
        $rowCount = 0;
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
        foreach($studentsShowArray AS $student)
        {
            $this->studentID = $student->id;
            //cant recall why I have two empty cells at the beginning. 
            $row = array();  
            $row[] = '';
            $row[] = '';
            $rowCount++;
            $countProcessed = 0;
            
            $row = $this->build_unit_grid_students_details($student, $this->id, 
                    $row, $context);
            foreach($activities AS $activity)
            {
                $countProcessed++;
                $activityUnits = $activity->unitcriteria;
                foreach($activityUnits AS $unitID => $criterias)
                {
                    $userUnit = $this->units[$unitID];
                    $userUnit->load_student_information($student->id, $this->id, $loadParams);
                    if($userUnit->is_student_doing())
                    {
                        //criteria were sorted on the header. 
                        foreach($criterias AS $criteriaID => $criteriaName)
                        {
                            $userCriteria = $userUnit->get_single_criteria($criteriaID);
                            
                            $retval = $this->set_up_criteria_grid($userCriteria, '', 
                                                $possibleValues, $editing, $advancedMode, false, $userUnit, 0, $student);
                            $row[] = $retval;
                        }
                    }
                    else
                    {
                        //user isnt doing unit
                        //but we still need to ouput empty
                        foreach($criterias AS $criteria)
                        {
                            $row[] = '';
                        }
                    }
                }
                if(count($activities) != $countProcessed)
                {
                    $row[] = '';
                }
            }
            $retArray[] = $row;
        }
        return $retArray;
    }
    
    public function display_student_grid($fullGridView = true, $studentView = true)
    {
        return $this->display_student_grid_btec($fullGridView, $studentView);
    }
    
    public function display_subject_grid()
    {
        //we need the header:
        //this will be the list of units
        //plus the usual key. 
        global $COURSE, $PAGE, $CFG, $OUTPUT;
        $grid = optional_param('g', 's', PARAM_TEXT);
        $courseID = optional_param('cID', -1, PARAM_INT);
        $sCourseID = optional_param('scID', -1, PARAM_INT);
        $context = context_course::instance($COURSE->id);
        $groupingID = optional_param('grID', -1, PARAM_INT);
        $basicView = optional_param('basic',false, PARAM_BOOL);
        $retval = '<div>';//overall subject wrapper

        if ($basicView)
        {
//            $retval .= "<p class='c'><a href='".$CFG->wwwroot."/blocks/bcgt/grids/print_grid.php?sID={$this->studentID}&qID={$this->id}' target='_blank'><img src='".$OUTPUT->pix_url('t/print', 'core')."' alt='' /> ".get_string('printgrid', 'block_bcgt')."</a></p>";
        }
        
        $retval .= '<input type="hidden" id="grid" name="g" value="'.$grid.'"/>';        
        $editing = false;
        $advancedMode = false;
        if($grid == 'ae' || $grid == 'se')
        {
            $editing = true;
        }
        if($grid == 'a' || $grid == 'ae')
        {
            $advancedMode = true;
        }    
        $columnsLocked = 0;
        $configColumns = get_config('bcgt','btecgridcolumns');
        if($configColumns)
        {
            $columns = explode(",",$configColumns);
            $columnsLocked += count($columns);
        }
        else
        {
            $columnsLocked += count($this->defaultColumns);
        }
        $configColumnWidth = get_config('bcgt','bteclockedcolumnswidthclass');
        //
        if(has_capability('block/bcgt:viewbtecavggrade', $context))
        {
            $columnsLocked++;
        }
        $jsModule = array(
            'name'     => 'mod_bcgtbtec',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        
        if ($basicView){
            $retval .= <<< JS
            <script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js'></script>
JS;
        } else {
            //courseID, columnsLocked, configColumnWidth, groupID
            $PAGE->requires->js_init_call('M.mod_bcgtbtec.initclassgrid', array($this->id, $columnsLocked, $configColumnWidth), true, $jsModule);
        }
        
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        $retval .= load_javascript(true, $basicView);
        $retval .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/css/start/jquery-ui-1.10.3.custom.min.css' />";
        
        $retval .= '<div>';
        $retval .= '<div class="bcgtgridbuttons">';
        $retval .= "<input type='submit' id='viewsimple' class='gridbuttonswitch viewsimple' name='viewsimple' value='View Simple'/>";
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        else
        {
            $context = context_course::instance($COURSE->id);
        }
        if(has_capability('block/bcgt:editunitgrid', $context))
        {	
            $retval .= "<input type='submit' id='editsimple' class='gridbuttonswitch editsimple' name='editsimple' value='Edit Simple'/>";
        }
        $studentsArray = $this->load_users('student', false, null, $sCourseID, $groupingID);
        $page = optional_param('page', 1, PARAM_INT);
        $pageRecords = get_config('bcgt','pagingnumber');
        if($pageRecords != 0)
        {
            
            //then we are paging
            //need to count the total number of students and divide by the paging number
            //load the session object
            //load the students that are on this unit for this qual.
            //have we already loaded the students?
            $totalNoStudents = count($studentsArray);
            $noPages = ceil($totalNoStudents/$pageRecords);
            $retval .= '<div class="bcgt_pagination">'.get_string('pagenumber', 'block_bcgt').' : ';
                
                for ($i = 1; $i <= $noPages; $i++)
                {
                    $class = ($i == 1) ? 'active' : '';
                    $retval .= "<a class='classgridpage pageNumber {$class}' page='{$i}' href='#&page={$i}'>{$i}</a>";
                }
            
            $retval .= '</div>';
        }
        $retval .= '<input type="hidden" name="pageInput" id="pageInput" value="'.$page.'"/>';
        if(has_capability('block/bcgt:viewajaxrequestdata', $context))
        {
            $retval .= '<ul>';
            $retval .= '<li><a target="_blank" href="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec/ajax/get_class_grid.php?qID='.$this->id.'&grID='.$groupingID.'&g='.$grid.'">'.get_string('ajaxrequest', 'block_bcgt').'</a></li>';
            $retval .= '</ul>';

        }
        $retval .= '</div>'; //bcgtgridbuttons
        $retval .=  '</div>';//end the wrapper of the key and buttons. 
        
        //the grid -> ajax
        $retval .= '<div id="btecClassGrid">';
        $retval .= "<div id='classGridDiv' class='classGridDiv ".
        $grid."ClassGrid tableDiv'><table align='center' class='class_grid".
                $grid."FixedTables' id='BTECClassGrid'>";
        
		//we will reuse the header at the bottom of the table.
        
		$headerObj = $this->get_class_grid_header($grid);
		$header = $headerObj->header;	
		$retval .= $header;
		$retval .= "<tbody>";
        //the body is loaded through an ajax call. This ajax call
        //is called in the js file of bcgtbtec.js and is in the initstdentgrid
        //it calls ajax and calls ajax/get_class_grid.php
        $retval .= "</tbody>";
        $retval .= "</table>";
        
        
        $retval .= "</div>";
        $retval .= '</div>';
        $retval .= '</div>';////end overall subject wrapper
        
        if ($basicView){
            $retval .= " <script>$(document).ready( function(){
                M.mod_bcgtbtec.initclassgrid(Y, {$this->id}, {$this->studentID}, '{$grid}');
            } ); </script> ";
        }
        
        return $retval;

    }
    
    public function get_family_instance_id()
    {
        return BTECQualification::ID;
    }
    
    public function has_advanced_mode()
    {
        return true;
    }
    
    public function call_display_student_grid_external()
    {
        return $this->display_student_grid_btec(false, true, false, true);
    }
    
    public function display_student_grid_btec($fullGridView = true, $studentView = true, $subCriteria = false, $basicView = false)
    {
        global $COURSE, $PAGE, $CFG, $OUTPUT;
        $grid = optional_param('g', 's', PARAM_TEXT);
        $late = optional_param('late', false, PARAM_BOOL);
        $courseID = optional_param('cID', -1, PARAM_INT);
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
            
                
        $retval = '<div>';
                
        if (!$basicView)
        {
        
            $retval .= $this->get_grid_global_buttons($courseID);
        
        }
        
        if ($basicView)
        {
            $retval .= "<p class='c'><a href='".$CFG->wwwroot."/blocks/bcgt/grids/print_grid.php?sID={$this->studentID}&qID={$this->id}' target='_blank'><img src='".$OUTPUT->pix_url('t/print', 'core')."' alt='' /> ".get_string('printgrid', 'block_bcgt')."</a> &nbsp;&nbsp;&nbsp;&nbsp; <a href='".$CFG->wwwroot."/blocks/bcgt/grids/print_report.php?sID={$this->studentID}&qID={$this->id}' target='_blank'><img src='".$OUTPUT->pix_url('t/print', 'core')."' alt='' /> ".get_string('printreport', 'block_bcgt')."</a></p>";
        }
        
        $retval .= '<input type="hidden" id="grid" name="g" value="'.$grid.'"/>';
        $retval .= '<input type="hidden" id="sID" value="'.$this->studentID.'" />';
        $retval .= '<input type="hidden" id="qID" value="'.$this->id.'" />';
        
        $editing = false;
        $advancedMode = false;
        if($grid == 'ae' || $grid == 'se')
        {
            $editing = true;
        }
        if($grid == 'a' || $grid == 'ae')
        {
            $advancedMode = true;
        }    
        
        if (!$basicView)
        {
        
            if(!$advancedMode && !$editing && has_capability('block/bcgt:viewbteclatetracking', $context))
            {
                $retval .= '<br /><span id="showLateFunc">Show Late History : ';
                $retval .= '<input type="checkbox" name="late" id="showlate"';
                if($late)
                {
                    $retval .= ' checked="checked" ';
                }
                $retval .= '/></span>';
            }
            $order = optional_param('order', 'spec', PARAM_TEXT);
            //do we have any activities on this unit?
            if($activities = bcgt_user_activities($this->id, $this->studentID, -1))
            {
                $this->activities = $activities;
                $this->activityids = array_keys($activities);
            }
            if(has_capability('block/bcgt:viewajaxrequestdata', $context))
            {
                $retval .= '<ul>';
                $retval .= '<li><a target="_blank" href="'.$CFG->wwwroot.
                        '/blocks/bcgt/plugins/bcgtbtec/ajax/get_student_grid.php?qID='.
                        $this->id.'&sID='.$this->studentID.'&g='.$grid.'&order='.
                        $order.'">'.get_string('ajaxrequest', 'block_bcgt').'</a></li>';
                $retval .= '</ul>';
            }        
        }
        
        $jsModule = array(
            'name'     => 'mod_bcgtbtec',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        
        if ($basicView){
            $retval .= <<< JS
            <script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js'></script>
JS;
        } else {
            $PAGE->requires->js_init_call('M.mod_bcgtbtec.initstudentgrid', array($this->id, $this->studentID, $grid), true, $jsModule);
        }
        
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        $retval .= load_javascript(true, $basicView);
        $retval .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/css/start/jquery-ui-1.10.3.custom.min.css' />";
        $retval .= '</div>'; //bcgtgridbuttons
        $retval .= "
		<div class='gridKey adminRight'>";
		if($studentView)
		{
			$retval .= "<h2>Key</h2>";
			//Are we looking at a student or just the actual criteria for the grid.
			//if students then get the key that tells everyone what things stand for
			$retval .= BTECQualification::get_grid_key();
		}
		$retval .= "</div>";
        
        //the grid -> ajax
        $retval .= '<div id="btecStudentGrid">';
        
        
        $retval .= "<div id='studentGridDiv' class='studentGridDiv ".
        $grid."StudentGrid tableDiv'><table align='center' class='student_grid".
                $grid."FixedTables' id='BTECStudentGrid'>";
        
		//we will reuse the header at the bottom of the table.
		$totalCredits = $this->get_students_total_credits($studentView);
		//for all of the units on this qual, lets check which crieria names
		//have actually been used. i.e. dont show P17 if no unit has a p17
        
		$criteriaNames = $this->get_used_criteria_names();
        require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
        $criteriaSorter = new CriteriaSorter();
		usort($criteriaNames, array($criteriaSorter, "ComparisonDelegateByArrayNameLetters"));
        
		$subCriteriaArray = false;
		if($subCriteria)
		{
			//This brings back an array that consists of:
			//(('P1',(P1.1, P1.2)),('P2', (P2.1, P2.2)),('M3', (M3.1, M3.2))) ect
			$subCriteriaArray = $this->get_used_sub_criteria_names($criteriaNames);
		}
		$headerObj = $this->get_grid_header($totalCredits, $studentView, $criteriaNames, $grid, $subCriteriaArray);
		$criteriaCountArray = $headerObj->criteriaCountArray;
        $this->criteriaCount = $criteriaCountArray;
		$header = $headerObj->header;	
        $totalCellCount = $headerObj->totalCellCount;
		if($subCriteria)
		{
            $subCriteriaNo = 0;
            if(isset($headerObj->subCriteriaNo))
            {
                $subCriteriaNo = $headerObj->subCriteriaNo;
            }
		}
		$retval .= $header;
		
		$retval .= "<tbody>";
        //the body is loaded through an ajax call. This ajax call
        //is called in the js file of bcgtbtec.js and is in the initstdentgrid
        //it calls ajax and calls ajax/get_student_grid.php
        $retval .= "</tbody>";
        $retval .= "</table>";
        
        // Qual Comment
        $retval .= "<div id='qualComment'></div>";
        
        if($studentView && !$editing)
		{
            //>>BEDCOLL TODO this need to be taken from the qual object
            //as foundatonQual is different
            $retval .= '<table id="summaryAwardGrades">';
			//if we are looking at the student then show the qual award
            if(has_capability('block/bcgt:viewbtectargetgrade', $context))
            {
                $retval .= $this->show_target_grade();
            }
			$retval .= $this->show_predicted_qual_award($this->studentAward, $context, $totalCredits);
            if(has_capability('block/bcgt:editstudentgrid', $context))
            {
                $retval .= '<tr><td><a class="refreshpredgrade" href="#">'.get_string('refreshpredgrade','block_bcgt').'</a></td></tr>';
            }
            $retval .= '</table>';
            
        }
        $retval .= "</div>";
        $retval .= '</div>';
        $retval .= '</div>';
        
        if ($basicView){
            $retval .= " <script>$(document).ready( function(){
                M.mod_bcgtbtec.initstudentgrid(Y, {$this->id}, {$this->studentID}, '{$grid}');
            } ); </script> ";
        }
        
        return $retval;
    }
    
    protected function get_grid_global_buttons($courseID)
    {
        global $COURSE;
        $retval = '<div class="bcgtgridbuttons">';
        $retval .= "<input type='submit' id='viewsimple' class='gridbuttonswitch viewsimple' name='viewsimple' value='View Simple'/>";
        $retval .= "<input type='submit' id='viewadvanced' class='gridbuttonswitch viewadvanced' name='viewadvanced' value='View Advanced'/>";
        $retval .= "<br>";  
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        
        if(has_capability('block/bcgt:editstudentgrid', $context))
        {	
            $retval .= "<input type='submit' id='editsimple' class='gridbuttonswitch editsimple' name='editsimple' value='Edit Simple'/>";
            $retval .= "<input type='submit' id='editadvanced' class='gridbuttonswitch editadvanced' name='editadvanced' value='Edit Advanced'/>"; 
        }
        
        return $retval;
    }
    
    protected function get_grid_assignment_overview_buttons($tab, $cID)
    {
        $retval = '<div class="tabs"><div class="tabtree">';
        $retval.= '<ul class="tabrow0">';
        $focus = ($tab == 'os')? 'focus' : '';
        $retval.= '<li class="first '.$focus.'">'.
                '<a href="?view=os&cID='.$cID.'&tab=acheck">'.
                '<span>'.get_string('overviewsimple', 'block_bcgt').'</span></a></li>';
        $focus = ($tab == 'oa')? 'focus' : '';
        $retval.= '<li class="last '.$focus.'">'.
                '<a href="?view=oa&cID='.$cID.'&tab=acheck">'.
                '<span>'.get_string('overviewadvanced', 'block_bcgt').'</span></a></li>';
        $focus = ($tab == 'subatt')? 'focus' : '';
        $retval.= '<li class="last '.$focus.'">'.
                '<a href="?view=subatt&cID='.$cID.'&tab=acheck">'.
                '<span>'.get_string('submissionsattempted', 'block_bcgt').'</span></a></li>';
        $focus = ($tab == 'subach')? 'focus' : '';
        $retval.= '<li class="last '.$focus.'">'.
                '<a href="?view=subach&cID='.$cID.'&tab=acheck">'.
                '<span>'.get_string('submissionsachieved', 'block_bcgt').'</span></a></li>';
        $retval.= '</ul>';
        $retval.= '</div></div>';
        return $retval;
    }
    
    protected function show_target_grade()
	{
		$retval = "";
		
		$retval .= "<tr>";
		$retval .= "<td>".get_string('targetgrade', 'block_bcgt')."</td>";
		$retval .= "<td>";
        $userCourseTarget = new UserCourseTarget();
        $grade = 'N/A';
        $targetGrade = $userCourseTarget->retrieve_users_target_grades($this->studentID, $this->id);
        if($targetGrade)
        {
            $targetGradeObj = $targetGrade[$this->id];
            if($targetGradeObj)
            {
                $breakdown = $targetGradeObj->breakdown;
                if($breakdown)
                {
                    $grade = $breakdown->get_target_grade();
                }
            }
        }
        $retval .= $grade;
		//$priorGrade = $this->get_user_course_targets_by_qual($this->studentID, $this->id);
//		if($priorGrade)
//		{
//			foreach($priorGrade AS $grade)
//			{
//				$retval .= $grade->targetgrade;
//			}
//		}
//		else
//		{
//			$retval .= "N/A";
//		}
		
		$retval .= "</td>";
		$retval .= "</tr>";
		
		return $retval;
	}
	
    public function has_min_award()
    {
        return true;
    }
    
    public function has_max_award()
    {
        return true;
    }
    
    public function has_final_award()
    {
        return true;
    }
    
	protected function show_predicted_qual_award($studentAward, $context, $totalCredits)
	{
        $warningClass = '';
        $warning = false;
        if($this->defaultcredits && $this->defaultcredits != $totalCredits)
        {
            $warning = true;
            $warningClass = 'warningcredits';
        }
        //TODO CHANGE THIS TO USE THE STUDENT AWARD
		$retval = "";
        if($this->has_min_award() && has_capability('block/bcgt:viewbtecmingrade', $context))
        {
            if($this->minAward)
            {
                $minAward = $this->minAward->get_award();
            }
            else {
                $minAwards = $this->get_default_award('Min');
                if($minAwards)
                {
                    $minAward = end($minAwards)->targetgrade;
                }
            }
            $retval .= '<tr><td>'.get_string('predictedminaward','block_bcgt').
                    '</td><td><span id="minAward" class="'.$warningClass.'">'.$minAward.'</span></td></tr>';
        }
        
        if($this->has_max_award() && has_capability('block/bcgt:viewbtecmaxgrade', $context))
        {
            if($this->maxAward)
            {
                $maxAward = $this->maxAward->get_award();
            }
            else
            {
                $maxAwards = $this->get_default_award('Max');
                if($maxAwards)
                {
                    $maxAward = end($maxAwards)->targetgrade;
                }
            }
            //extra cells for unit comments and projects
            $retval .= '<tr><td>'.get_string('predictedmaxaward','block_bcgt').
                    '</td><td><span id="maxAward" class="'.$warningClass.'">'.$maxAward.'</span></td></tr>';
        }
        
        if($this->has_final_award() && has_capability('block/bcgt:viewbtecavggrade', $context))
        {
            //are the predicted and final different?
            if($this->studentAward && $this->predictedAward && 
                    ($this->studentAward->get_award() != $this->predictedAward->get_award()))
            {
                //we need to recalculate
                $this->calculate_qual_award(false);
            }
            
            $retval .= "<tr><td>";
            $type = get_string('predictedavgaward','block_bcgt');
            $award = 'N/A';
            if($studentAward && isset($studentAward->final))
            {
                $award = $studentAward->final->get_award();
                $retval .= "<span id='qualAwardType'>$type</span></td><td><span id='qualAward' class='".$warningClass."'>".$award."</span></td>";	
            }
            elseif($studentAward && isset($studentAward->averageAward))
            {
                switch($studentAward->averageAward->get_type())
                {
                    case "Final":
                        $type = get_string('predictedfinalaward','block_bcgt');
                        break;
                    case "AVG":
                        $type = get_string('predictedavgaward','block_bcgt');
                        break;
                }
                $award = $studentAward->averageAward->get_award();
                $retval .= "<span id='qualAwardType'>$type</span></td><td><span id='qualAward' class='".$warningClass."'>".$award."</span></td>";	
            }
            elseif($studentAward && isset($studentAward->award))
            {
                $retval .= "<span id='qualAwardType'>$type</span></td><td>".
                        "<span id='qualAward' class='".$warningClass."'>$studentAward->award</span></td>";
            }
            elseif($this->studentAward)
            {
                //interestingly it nearly always goes here
                $predAward = $this->studentAward->get_award();
                $retval .= "<span id='qualAwardType'>$type</span></td><td>".
                        "<span id='qualAward' class='".$warningClass."'>$predAward</span></td>";
            }
            elseif($this->predictedAward)
            {
                ///or here!!
                $predAward = $this->predictedAward->get_award();
                $retval .= "<span id='qualAwardType'>$type</span></td><td>".
                        "<span id='qualAward' class='".$warningClass."'>$predAward</span></td>";
            }
            else
            {
                $retval .= "<span id='qualAwardType'>$type</span></td><td>".
                        "<span id='qualAward' class='".$warningClass."'>$award</span></td>";
            }
            $retval .= "</tr>";
        }
        if($warning)
        {
            $retval .= "<tr>";
            $retval .= "<td class='".$warningClass."' colspan='2'>".get_string('creditswarning', 'block_bcgt')."</td>";
            $retval .= "</tr>";
            if(has_capability('block/bcgt:editstudentunits', $context))
            {
                $retval .= "<tr>";
                $retval .= "<td class='".$warningClass."' colspan='2'>".get_string('creditschange', 'block_bcgt')."</td>";
                $retval .= "</tr>";
            }
        }
        
        return $retval;
	}
    
    public function has_sub_criteria()
    {
        return false;
    }
    
    public function set_grid_disabled($disabled)
    {
        $this->gridLocked = $disabled;
    }
    
    /**
     * 
     * @global type $COURSE
     * @global type $CFG
     * @param type $advancedMode
     * @param type $editing
     * @param type $studentView
     * @return string
     */
    public function get_student_grid_data($advancedMode, $editing, 
            $studentView)
    {
        global $DB, $OUTPUT;
        $subCriteria = $this->has_sub_criteria();
        //$this->load_student();
        if (isset($this->criteriaCount)){
            $criteriaCountArray = $this->criteriaCount;
        }
        $user = $DB->get_record_sql('SELECT * FROM {user} WHERE id = ?', array($this->studentID));
        $subCriteriaArray = false;
        
        if (!isset($this->usedCriteriaNames)){
            $criteriaNames = $this->get_used_criteria_names();
        }
        
        $criteriaNames = $this->usedCriteriaNames;
		if($subCriteria)
		{
			//This brings back an array that consists of:
			//(('P1',(P1.1, P1.2)),('P2', (P2.1, P2.2)),('M3', (M3.1, M3.2))) ect
			$subCriteriaArray = $this->get_used_sub_criteria_names($criteriaNames);
		}
        $rowsArray = array();
        global $COURSE, $CFG;
        $courseID = optional_param('cID', -1, PARAM_INT);
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        //get all of the units
        //get all of the units and sort them by their names.
        require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
        $criteriaSorter = new CriteriaSorter();
		usort($criteriaNames, array($criteriaSorter, "ComparisonDelegateByArrayNameLetters"));
        
		$units = $this->units;
        $unitSorter = new UnitSorter();
		usort($units, array($unitSorter, "ComparisonDelegateByType"));
        $possibleValues = null;
        if($advancedMode && $editing)
        {
           $possibleValues = $this->get_possible_values(BTECQualification::ID, true); 
        }
		if($editing && !$advancedMode)
        {
            $unitAwards = Unit::get_possible_unit_awards($this->get_class_ID());
        }
        
        $order = optional_param('order', 'spec', PARAM_TEXT);
        if($order == 'actunit' || $order == 'unitact')
        {
            //then we are showing all of the activities
            //and all of the units on those activities. 
            $activities = $this->activities;
            
            if(!isset($this->modLinking))
            {
                $modLinking = load_bcgt_mod_linking();
                $this->modLinking = $modLinking;
            }
            if(!isset($this->dueDateActivitySorted) || !$this->dueDateActivitySorted)
            {
                //only want to do it once. 
                //get the due dates of the activities
                foreach($activities AS $activity)
                {
                    $dueDate = get_bcgt_mod_due_date($activity->id, $activity->instanceid, $activity->cmodule, $modLinking);
                    $activity->dueDate = $dueDate;
                }
                require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ModSorter.class.php');
                $modSorter = new ModSorter();
                //uasort keeps the keys. 
                uasort ($activities, array($modSorter, "ComparisonDelegateByDueDateObj"));
                $this->activities = $activities;
                
                $this->dueDateActivitySorted = true;
            }
            if(!isset($this->modIcons))
            {
                $modIcons = load_mod_icons(-1, $this->id, -1, -1);
                $this->modIcons = $modIcons;
            }
            if(!isset($this->userActivityUnitsSelection))
            {
                $byUnit = false;
                if($order == 'unitact')
                {
                    $byUnit = true;
                }
                $this->load_user_activity_unit_selection($byUnit);
            }
            $activiySelection = $this->userActivityUnitsSelection;
            //now need to output them.
        }
        $rowCount = 0;
        if($order == 'actunit')
        {
            foreach($activities AS $activity)
            {
                $rowArray = array();
                $rowClass = 'rO';
                if($rowCount % 2)
                {
                    $rowClass = 'rE';
                }
                //icon, 
                //activiyName
                //due date
                $iconDisplay = '';
                $modIcons = $this->modIcons;
                if(array_key_exists($activity->module,$modIcons))
                {
                    $icon = $modIcons[$activity->module];
                    //show the icon. 
                    $iconDisplay .= html_writer::empty_tag('img', array('src' => $icon,
                                'class' => 'bcgtmodcriticon activityicon', 'alt' => $activity->module));
                }
                $rowArray[] = $iconDisplay;
                $rowArray[] = $activity->name;
                $dueDate = '';
                if(isset($activity->dueDate))
                {
                    $dueDate = date('d M Y : H:m', $activity->dueDate); 
                }
                $rowArray[] = $dueDate;
                //then its the units
                
                //so get each unit, that this user is doing
                //that is on this activity. 
                $activityUnitsCount = 0;
                if(array_key_exists($activity->id, $activiySelection))
                {
                    $activityUnits = $activiySelection[$activity->id];
                    $activityUnitsCount = count($activityUnits);
                    //probably need to reorder these units by their names!
                    foreach($activityUnits AS $unitID => $criterias)
                    {
                        //we want a table cell per unit
                        $userUnit = $this->units[$unitID];
                        $cellContent = '';
                        $cellContent .= '<table>';
                        $cellContent .= '<thead>';
                        $cellContent .= '<tr>';
                        $multiplier = 1;
                        if($advancedMode && $editing)
                        {
                            $multiplier = 2;
                        }
                        $cellContent .= '<th colspan="'.(count($criterias)*$multiplier).'">';
                        $cellContent .= $userUnit->get_name(); 
                        $cellContent .= '</th>';
                        $cellContent .= '</tr>';
                        $cellContent .= '<tr>';
                        $bodyRow = '';
                        //probably need to reorder the criteria. 
                        foreach($criterias AS $criteriaID)
                        {
                            $userCriteria = $userUnit->get_single_criteria($criteriaID);
                            $cellContent .= '<th>'.$userCriteria->get_name().'</th>';
                            if($advancedMode && $editing)
                            {
                                //if its advanced and editing then we have the extra 
                                //cell required for the add/edit comments. 
                                $cellContent .= "<th class='blankHeader'></th>";
                            }
                            //this then gets the users value. 
                            $bodyRow .= '<td>';
                            $retval = $this->set_up_criteria_grid($userCriteria, '', 
                                        $possibleValues, $editing, $advancedMode, false, $userUnit, 0, $user);
                            $bodyRow .= $retval;
                            $bodyRow .= '</td>';
                        }
                        $cellContent .= '</tr>';
                        $cellContent .= '</thead>';
                        $cellContent .= '<tbody>';
                        $cellContent .= '<tr>';
                        $cellContent .= $bodyRow;
                        $cellContent .= '</tr>';
                        $cellContent .= '</tbody>';
                        $cellContent .= '</table>';
                        $rowArray[] = '<span>'.$cellContent.'</span>';
                    }
                }
                
                
                //then we need to match the total possible number of units
                //with the umber of units that are on this activity
                //so the table matches.
                if(!isset($this->maxNoUnits))
                {
                    //then go and get them. 
                    $activityIDs = array_keys($this->activities);
                    //but is the user doing all of the units?
                    $maxNoUnits = bcgt_get_max_units_activity($activityIDs, $this->studentID);
                    $this->maxNoUnits = $maxNoUnits;
                }
                $cellsRemaining = $this->maxNoUnits->maxcount - $activityUnitsCount;
                for($i=0;$i<$cellsRemaining;$i++)
                {
                    $rowArray[] = '';
                }
                
                //a table for each
                //the unit name
                //the criteria names
                //the users values. 
                $rowsArray[] = $rowArray;
            }
        }
        else
        {
            foreach($units AS $unit)
            {
                if(($studentView && $unit->is_student_doing()) || !$studentView)
                {	
                    $rowArray = array();
                    //Are we looking at the student? 
                    //Is the student doing the unit. 
                    //If they are not then we dont want to show the unit. 
                    $rowClass = 'rO';
                    if($rowCount % 2)
                    {
                        $rowClass = 'rE';
                    }

                    $award = 'N/S';
                    $rank = 'nr';
                    $shortAward = null;
                    if($studentView)
                    {
                        //get the users award from the unit
                        $unitAward = $unit->get_user_award();
                        if($unitAward)
                        {
                            $rank = $unitAward->get_rank();
                            $award = $unitAward->get_award();
                            $shortAward = $unitAward->get_short_award();
                        }	
                    }

                    $extraClass = '';
                    if($rowCount == 1)
                    {
                        $extraClass = 'firstRow';
                    }
                    elseif($rowCount == count($units))
                    {
                        $extraClass = 'lastRow';
                    }
                    //the row class
                    //$retval .= "<tr class='$rowClass $extraClass ".$unit->get_unit_type()." prU".$unit->get_id()."' id='".$unit->get_id()."'>";

                    //the first json value
                    //$retval .= '<td></td>';
                    $rowArray[] = '';

                    // Unit Comment
                    $getComments = $unit->get_comments();

                    $cellID = "cmtCell_U_{$unit->get_id()}_S_{$user->id}_Q_{$this->get_id()}";


                    $username = htmlentities( $user->username, ENT_QUOTES );
                    $fullname = htmlentities( fullname($user), ENT_QUOTES );
                    $unitname = htmlentities( $unit->get_name(), ENT_QUOTES);
                    $critname = "N/A";   

                    $retval = "";

                    if($advancedMode && $editing)
                    {

                        if(!empty($getComments))
                        {                
                            $retval .= "<img id='{$cellID}' grid='student' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' type='button' class='editCommentsUnit' title='Click to Edit Unit Comments' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/comments.jpg' />";
                            $retval .= "<div class='tooltipContent'>".nl2br( htmlentities($getComments, ENT_QUOTES) )."</div>";
                        }
                        else
                        {                        
                            $retval .= "<img id='{$cellID}' grid='student' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' type='button' class='addCommentsUnit' title='Click to Add Unit Comment' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/plus.png' />";
                        }

                    }
                    else
                    {
                        if(!empty($getComments)){
                            $retval .= "<img src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/comment-icon.png' class='showCommentsUnit' />";
                            $retval .= "<div class='tooltipContent'>".nl2br( htmlentities($getComments, ENT_QUOTES) )."</div>";
                        }

                    }
                    $rowArray[] = $retval;
                    // End Unit Comment  
                    //$retval .= "<td class='unitName r".$unit->get_id()." '>";
                    $studentID = -1;
                    if($studentView)
                    {
                        //This is used to link to another page.
                        //if studentID = -1 then we know we are not
                        //looking at the student but the qual in general
                        $studentID = $this->studentID;
                    }
                    $link = '';
                    if(has_capability('block/bcgt:editunit', $context))
                    {
                        $link = '<a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/'.
                                'unit_grid.php?uID='.$unit->get_id().
                                '&qID='.$this->id.'">'.$unit->get_name().'</a>';
                    }
                    else
                    {
                        $link = $unit->get_name();
                    }
                    $retval = "<span id='uID_".$unit->get_id()."' class='uNToolTip unitName".$unit->get_id()."' title=''>".$link."</span>";

                    $retval .= "<span style='color:grey;font-size:85%;'><br />(".$unit->get_credits()." ".$this->get_credits_display_name().")</span>";	

                    //if has capibility
                    if(has_capability('block/bcgt:editunit', $context))
                    {		
                        $retval .= "<a class='editing_update editUnit' href='{$CFG->wwwroot}/blocks/bcgt/forms/edit_unit.php?unitID=".$unit->get_id()."' title = 'Update Unit'>
                        <img class='iconsmall editUnit' alt='Update Unit' src='".$OUTPUT->pix_url("t/edit", "core")."'/></a>";
                    }

                    $retval .= "<img src='".$CFG->wwwroot."/blocks/bcgt/pix/info.png' height='12' width='12' class='uNToolTipInfo' unitID='{$unit->get_id()}' />";

                    //$retval .= "</td>";
                    $rowArray[] = $retval;
                    if($studentView)
                    {
                        if($editing && !$advancedMode)
                        {
                            $retval = $this->edit_unit_award($unit, $rank, $award, $unitAwards, $this->gridLocked);
                            $rowArray[] = $retval;
                        }
                        else
                        {
                            $output = $award;
                            if($shortAward && $shortAward != '')
                            {
                                $output = $shortAward;
                            }
                            //print out the unit award column
                            //$retval .= "<td id='unitAward_".$unit->get_id()."' class='unitAward r".$unit->get_id()." rank$rank'>".$award."</td>";
                            $rowArray[] = '<span id="unitAwardAdv_'.$unit->get_id().'">'.$output.'</span>';
                        }
                    }

                    // % Completion
                    if($this->has_percentage_completions() && $studentView){
                        $retval .= "<td id='tdPercentCompleted'>".$unit->display_percentage_completed()."</td>";
                    }

                    if($order == 'unitact')
                    {
                        $unitActivitiesCount = 0;
                        //need to get all of the activities for this unit. 
                        //plus all of the criterias
                        if(array_key_exists($unit->get_id(),$activiySelection))
                        {
                            //then we can display it.   
                            $unitActivitiesCriteria = $activiySelection[$unit->get_id()];
                            //activities array (set above) has been orderd by due date above
                            //so i want to keep the order. 
                            $unitActivityIDs = array_intersect(array_keys($activities), array_keys($unitActivitiesCriteria));
                            $unitActivitiesCount = count($unitActivitiesCriteria);
                            //probably need to reorder these units by their names!
                            foreach($unitActivityIDs AS $id)
                            {
                                $activity = $activities[$id];
                                //does it actually have any  criterias? 
                                if(!array_key_exists($activity->id, $unitActivitiesCriteria))
                                {
                                    //need to remove one from the unitActivitiesCount so it
                                    //knows to put a blank one in this activities
                                    //spot
                                    $unitActivitiesCount--;
                                    continue;
                                }
                                $criterias = $unitActivitiesCriteria[$activity->id];
                                $cellContent = '<table>';
                                $cellContent .= '<thead>';
                                $cellContent .= '<tr>';
                                $multiplier = 1;
                                if($advancedMode && $editing)
                                {
                                    $multiplier = 2;
                                }
                                $cellContent .= '<th colspan="'.(count($criterias)*$multiplier).'"><span>';
                                if(array_key_exists($activity->module,$modIcons))
                                {
                                    $icon = $modIcons[$activity->module];
                                    //show the icon. 
                                    $cellContent .= html_writer::empty_tag('img', array('src' => $icon,
                                                'class' => 'bcgtmodcriticon activityicon', 'alt' => $activity->module));
                                }
                                $cellContent .= '</span>';
                                $cellContent .= '<span>';
                                $cellContent .= $activity->name;
                                //the name
                                $cellContent .= '</span>';
                                $cellContent .= '<span>';
                                //the duedate
                                if(isset($activity->dueDate))
                                {
                                    $cellContent .= date('d M Y : H:m', $activity->dueDate); 
                                }
                                $cellContent .= '</span>';
                                $cellContent .= '</tr>';
                                $subRow = '';
                                foreach($criterias AS $criteriaID)
                                {
                                    $userCriteria = $unit->get_single_criteria($criteriaID);
                                    $cellContent .= '<th>';
                                    $cellContent .= $userCriteria->get_name();
                                    $cellContent .= '</th>';
                                    if($advancedMode && $editing)
                                    {
                                        //if its advanced and editing then we have the extra 
                                        //cell required for the add/edit comments. 
                                        $cellContent .= "<th class='blankHeader'></th>";
                                    }
                                    $subRow .= '<td>';
                                    $retval = $this->set_up_criteria_grid($userCriteria, '', 
                                                $possibleValues, $editing, $advancedMode, false, $unit, 0, $user);
                                    $subRow .= $retval;
                                    $subRow .= '</td>';
                                }
                                $cellContent .= '</thead>';
                                $cellContent .= '<tbody>';
                                $cellContent .= '<tr>'.$subRow.'</tr>';
                                $cellContent .= '</tbody>';
                                $cellContent .= '</table>';
                                $rowArray[] = '<span>'.$cellContent.'</span>';
                            }
                        }
                        
                        //now need to fill the table with empty remaining cells
                        $cellsRemaining = $this->maxNoActivities->maxcount - $unitActivitiesCount;
                        for($i=0;$i<$cellsRemaining;$i++)
                        {
                            $rowArray[] = '';
                        }
                    }
                    else
                    {
                        if($criteriaNames)
                        {
                            //if we have found the used criteria names. 
                            $criteriaCount = 0;
                            $previousLetter = '';						
                            foreach($criteriaNames AS $criteriaName)
                            {	
                                //TODO
                                $criteriaCount++;
                                $letter = substr($criteriaName, 0, 1);
                                if($previousLetter != '' && $previousLetter != $letter)
                                {
                                    //if we have moved from P to M then put the divider in. 
                                    $rowArray[] = "";
                                }
                                $previousLetter = $letter;	

                                if($studentView)
                                {
                                    //if its the student view then lets print
                                    //out the students unformation
                                    if($studentCriteria = $unit->get_single_criteria(-1, $criteriaName))
                                    {	
                                        $retval = $this->set_up_criteria_grid($studentCriteria, '', 
                                                $possibleValues, $editing, $advancedMode, false, $unit, 0, $user);
                                        $rowArray[] = $retval;
                                        if($subCriteria)
                                        {
                                            //Get the used Sub Criteria Names from the heading for this criteriaName
                                            //for example get the p1.1, P1.2 ect for the P1
                                            if(array_key_exists($criteriaName, $subCriteriaArray))
                                            {
                                                $criteriaSubCriteriasUsed = $subCriteriaArray[$criteriaName];
                                                //Lets see if this Criteria has the subcriteria that matches the heading
                                                $cellCount = count($criteriaSubCriteriasUsed);
                                                $i = 0;
                                                foreach($criteriaSubCriteriasUsed AS $subCriteriaUsed)
                                                {
                                                    $firstLast = 0;
                                                    $i++;
                                                    $extraClass = '';
                                                    if($i == 1)
                                                    {
                                                        $extraClass = 'startSubCrit';
                                                        if(count($criteriaSubCriteriasUsed) == 1)
                                                        {
                                                            $extraClass .= " endSubCrit";
                                                        }
                                                        $firstLast = 1;
                                                    }
                                                    elseif($i == $cellCount)
                                                    {
                                                        $extraClass = 'endSubCrit';
                                                        $firstLast = -1;
                                                    }
                                                    $criteriaCount++;
                                                    $actualSubCriteria = $studentCriteria->get_single_criteria(-1, $subCriteriaUsed);
                                                    if($actualSubCriteria)
                                                    {
                                                        //then create the grid
                                                        $rowArray[] = $this->set_up_criteria_grid($actualSubCriteria, 
                                                                $extraClass.' subCriteria subCriteria_'.$criteriaName, 
                                                                $possibleValues, $editing, $advancedMode, 
                                                                true, $unit, $firstLast, $user);
                                                    }
                                                    else
                                                    {
                                                        $rowArray[] = '';
        //                                                $retval .= "<td display='none' class='grid_cell_blank $extraClass subCriteria subCriteria_$criteriaName'></td>";	
                                                    }//end else not actualSubCriteria
                                                }//end loop sub Criteria
                                            }
                                            else
                                            {
                                                $rowArray[] = '';
                                            }
                                        }//end if there is sub criteria
                                    }//end if student criteria
                                    else //not student criteria (i.e. the criteria doesnt exist on that unit)
                                    {                                
                                        //retval needs to be an array of the columns
                                        if (isset($criteriaCountArray)){
                                            $rowArray = $this->get_criteria_not_on_unit($criteriaCount, $criteriaCountArray, 
                                            $advancedMode, $editing, $criteriaName, $subCriteriaArray, $rowArray);
                                        } else {
                                            $rowArray[] = '';
                                        }
        //                                $rowArray[] = $retval;
                                    }//end else not sudent criteria	
                                }
                                else//its not the student view
                                {//This means we are just showing the qual as a whole. 
                                    //then lets just test if he unit has that criteria
                                    //and mark it as present or not

                                    $retval .= $this->get_non_student_view_grid($criteriaCount, $criteriaCountArray, $criteriaName, $unit, $subCriteriaArray);
                                    $rowArray[] = $retval;

                                }

                            }//end for each criteria
                        }//end if criteria names
                    }
                    
                    $rowsArray[] = $rowArray;
    //				$retval .= "</tr>";	
                }//end if student view and student doing the unit.
            }//end for each unit
        }
        return $rowsArray;
    }
    
    private function load_user_activity_unit_selection($byUnit = false)
    {
        //get the activity array keys
        global $DB;
        $activityKeys = $this->activityids;
        $sql = "SELECT distinct(refs.id), coursemoduleid, refs.bcgtunitid, refs.bcgtcriteriaid 
            FROM {block_bcgt_activity_refs} refs 
            JOIN {block_bcgt_user_unit} userunit ON userunit.bcgtunitid = refs.bcgtunitid 
            WHERE userunit.userid = ? AND refs.bcgtqualificationid = ? AND refs.coursemoduleid IN (";
        $params = array();
        $params[] = $this->studentID;
        $params[] = $this->id;
        $count = 0;
        foreach($activityKeys AS $id)
        {
            $count++;
            $sql .= '?';
            if($count != count($activityKeys))
            {
                $sql .= ',';
            }
            $params[] = $id;
        }
        $sql .= ')';
        $records = $DB->get_records_sql($sql, $params); 
        $activiySelection = array();
        if($records)
        {
            foreach($records AS $record)
            {
                if(!$byUnit)
                {
                    //then we are doing it by activity. 
                    if(array_key_exists($record->coursemoduleid, $activiySelection))
                    {
                       $unitSelection = $activiySelection[$record->coursemoduleid];
                    }
                    else
                    {
                        $unitSelection = array();
                    }
                    if(array_key_exists($record->bcgtunitid, $unitSelection))
                    {
                        $criteriaArray = $unitSelection[$record->bcgtunitid];
                    }
                    else
                    {
                        $criteriaArray = array();
                    }
                    $criteriaArray[$record->bcgtcriteriaid] = $record->bcgtcriteriaid;
                    $unitSelection[$record->bcgtunitid] = $criteriaArray;
                    $activiySelection[$record->coursemoduleid] = $unitSelection;
                }
                else
                {
                    //we are doing it by unit
                    if(array_key_exists($record->bcgtunitid, $activiySelection))
                    {
                       $activitySel = $activiySelection[$record->bcgtunitid];
                    }
                    else
                    {
                        $activitySel = array();
                    }
                    if(array_key_exists($record->coursemoduleid, $activitySel))
                    {
                        $criteriaArray = $activitySel[$record->coursemoduleid];
                    }
                    else
                    {
                        $criteriaArray = array();
                    }
                    $criteriaArray[$record->bcgtcriteriaid] = $record->bcgtcriteriaid;
                    $activitySel[$record->coursemoduleid] = $criteriaArray;
                    $activiySelection[$record->bcgtunitid] = $activitySel;
                }
            }
        }
        $this->userActivityUnitsSelection = $activiySelection;
    }
    
    public function get_class_grid_data($advancedMode, $editing)
    {
        //get the units
        //get the students
        //do it by paging. 
        $pageNumber = optional_param('page',1,PARAM_INT);
        $courseID = optional_param('cID', -1, PARAM_INT);
        $sCourseID = optional_param('scID', -1, PARAM_INT);
        $groupID = optional_param('grID', -1, PARAM_INT);
        global $COURSE;
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        } 
        $retval = array();
        $unitAwards = null;
        if($editing)
        {
            $unitAwards = Unit::get_possible_unit_awards($this->get_class_ID());
        }
        $this->unitAwards = $unitAwards;
        
        //get the students:
        //1.) get the students by qual
        //2.) then by course
        //3.) then by group
        $rows = array();
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
        $loadParams->loadAward = true;
        $loadParams->calcAward = false;
        $loadParams->loadAddUnits = false;
        $this->load_users('student', false, $loadParams, $sCourseID, $groupID);
        if(isset($this->usersstudent))
        {
            $studentsArray = $this->usersstudent;
            if(get_config('bcgt','pagingnumber') != 0)
            {
                $pageRecords = get_config('bcgt','pagingnumber');
                //then we only want a certain number!
                //we also need to take into account the page number we are on.
                //studentsArray is the array of students on the unit on this qual. 
                //the keys are the ids of the students. 
                $keys = array_keys($studentsArray);
                //arrays keys returns an array of the keys of the first aray. This return aray has its keys set to 
                //the numerical order, e.g. always starting at 0, then 1 etc.  

                $studentsShowArray = array();
                //are we at the first page, 
                if($pageNumber == 1)
                {
                   $i = 0; 
                }
                else
                {
                    //no so we want to start at the page number times by how many we show per page
                    $i = ($pageRecords * ($pageNumber - 1)) + ($pageNumber - 1);
                }
                //we want to loop over and only show the number of students in our page size. 
                $recordsEnd = ($i + $pageRecords);
                for($i;$i<=$recordsEnd;$i++)
                {
                    //gets the student object from the array by the key that we are looking at.
                    if (isset($keys[$i]) && isset($studentsArray[$keys[$i]]))
                    {
                        //so, if we have the student id for the nth student we need. 
                        //then find the student that that id coresponds to from our original array of students. 
                        $student = $studentsArray[$keys[$i]];
                        //add this student to the array that we want to display.
                        $studentsShowArray[$keys[$i]] = $student;
                    }
                }
            }
            else {
                $studentsShowArray = $studentsArray;
            }
            $rowCount = 0;
            foreach($studentsShowArray AS $student)
            {
                //this is loaded in get_users() above;
                //THIS MAY BE TOO COSTLY!
//                if(isset($this->usersQualsstudent[$student->id]))
//                {
//                    $studentQual = $this->usersQualsstudent[$student->id];
                    $studentQual = Qualification::get_qualification_class_id($this->id, $loadParams);
                    $studentQual->load_student_information($student->id, $loadParams);
                    $usersUnits = $studentQual->get_units();
                    $row = array();       
                    $rowCount++;
                    $rowClass = 'rO';
                    if($rowCount % 2)
                    {
                        $rowClass = 'rE';
                    }				

                    //load the qual object up for the student:
                    $extraClass = '';
                    if($rowCount == 1)
                    {
                        $extraClass = 'firstRow';
                    }
                    elseif($rowCount == count($studentsArray))
                    {
                        $extraClass = 'lastRow';
                    }  
                    // End Unit Comment  
                    $row = $this->build_class_grid_students_details($student, $this->id, 
                            $row, $context);

                    //now we need to do for each unit
                    //if its edit
                    //then a drop down to change
                    //else if its not then just output

                    foreach($units = $this->units AS $unit)
                    {
                        if(array_key_exists($unit->get_id(), $usersUnits) && $usersUnits[$unit->get_id()]->is_student_doing())
                        {
                            $userUnit = $usersUnits[$unit->get_id()];
                            //then the user is on this unit for sure, 
                            $userAwardString = '';
                            $userAwardID = -1;
                            $userAward = $userUnit->get_user_award();
                            if($userAward)
                            {
                                $userAwardString = $userAward->get_award();
                                $userAwardID = $userAward->get_id();
                            }
                            if($editing)
                            {
                                //drop down
                                $retval = '<select qual="'.$this->id.'" id="unitAwardS_'.$student->id.'_u_'.$unit->get_id().'" class="unitAward">';
                                $retval .= '<option value="-1"> </option>';
                                foreach($this->unitAwards AS $award)
                                {
                                    $selected = '';
                                    if($userAwardID == $award->id)
                                    {
                                        $selected = 'selected="selected"';
                                    }
                                    $retval .= '<option '.$selected.' value="'.$award->id.'">'.$award->award.'</option>';
                                }
                                $retval .= '</select>';
                                $row[] = $retval;
                            }
                            else
                            { 
                                //just output the award
                                $row[] = $userAwardString;
                            }
                        }
                        else
                        {
                            $row[] = '<span class="usernotonunit"></span>';
                        }
                    }//end for each unit
                    $rows[] = $row;
//                }
            }//end for each student
            
        }
        return $rows;
    }
    
    protected function build_class_grid_students_details($student, $qualID, 
                        $row, $context)
    {
        //now to build up the columns that contain the students.
        global $CFG, $printGrid, $OUTPUT;
		   
        //columns supported are:
        //picture,username,name,firstname,lastname,email
        $columns = $this->defaultColumns;
        $configColumns = get_config('bcgt','btecgridcolumns');
        $link = $CFG->wwwroot.'/blocks/bcgt/grids/student_grid.php?qID='.$qualID.'&sID='.$student->id;  
        //need to get the global config record
        
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
        }
        foreach($columns AS $column)
        {
            $content = '<a href="'.$link.'" class="studentUnit" title="" id="sID_'.
                    $student->id.'_qID_'.$qualID.'">';
            switch(trim($column))
            {
                case("picture"):
                    $content .= $OUTPUT->user_picture($student, array('size' => 25));
                    break;
                case("username"):
                    $content .= $student->username;
                    break;
                case("name"):
                    $content .= $student->firstname."<br />".$student->lastname;
                    break;
                case("firstname"):
                    $content .= $student->firstname;
                    break;
                case("lastname"):
                    $content .= $student->lastname;
                    break;
                case("email"):
                    $content .= $student->email;
                    break;
            }
            $content .= '</a>';
                        
            if ($column == "username")
            {
                $content .= "&nbsp;<img src='".$CFG->wwwroot."/blocks/bcgt/pix/info.png' class='studentUnitInfo' qualID='{$qualID}' studentID='{$student->id}' unitID='{$this->get_id()}' />";
            }
            
            $row[] = $content;
        }
		$qualAward = "N/A";
        if(has_capability('block/bcgt:viewbtecavggrade', $context))
        {
            $loadParams = new stdClass();
            $loadParams->loadLevel = 0;
            $loadParams->loadAward = true;
            $this->load_student_information($student->id, $loadParams);
            //work out the students qualification award
            $award = $this->predictedAward;
            $finalAward = $this->studentAward;
            
            //are the predicted and final different?
            if($this->studentAward && $this->predictedAward && 
                    ($this->studentAward->get_award() != $this->predictedAward->get_award()))
            {
                //we need to recalculate
                $this->calculate_qual_award(false);
            }
                    
            $qualAwardType = '';
            $awardFullString = 'N/A';
            if($award)
            {
                $qualAwardType = $award->get_type();
                $awardString = $award->get_award();
                $awardFullString = $awardString;
            }
            $qualAward = "<span id='qualAward_".$student->id."'>".
                        $awardFullString."</span>";
            $row[] = $qualAward;
        }
		
		return $row;
    }
        
    /**
	 * Returns the possible values that can be selected for this qualification type
	 * when updating criteria for students
	 */
	public static function get_possible_values($typeID, $enabled = false)
	{
		global $DB;
		$sql = "SELECT value.*, settings.id as settingid, settings.coreimg, 
            settings.customimg, settings.coreimglate, settings.customimglate 
            FROM {block_bcgt_value} value
            JOIN {block_bcgt_value_settings} settings ON settings.bcgtvalueid = value.id
		WHERE value.bcgttypeid = ?";
        if($enabled)
        {
            $sql .= " AND value.enabled = ?";
        }
        $params = array($typeID, 1);
		return $DB->get_records_sql($sql, $params);
		
	}
    
    protected function get_non_student_view_grid($criteriaCount, $criteriaCountArray, $criteriaName, $unit, $subCriteriaArray)
	{
		$retval = "";
		global $CFG;
		$retval .= "<td";
		if($criteria = $unit->get_single_criteria(-1, $criteriaName))
		{
			//if the crieria is on the unit then mark as so and build the on hover tooltip
			$retval .= " class='crit'><span class='critValue'><img class='criteriaPresent' src=\"{$CFG->wwwroot}/mod/qualification/pix/blackX.jpg\">";
			$retval .= "</span>";
			//$retval .= $criteria->build_criteria_value_popup($unit->get_id(), false);
			$retval .= "</td>";
			if($subCriteriaArray)
			{
				$criteriaSubCriteriasUsed = $subCriteriaArray[$criteriaName];
				if($criteriaSubCriteriasUsed)
				{
					$i = 0;
					foreach($criteriaSubCriteriasUsed AS $subCriteriaUsed)
					{
						$i++;
						$extraClass = '';
						if($i == 1)
						{
							$extraClass = 'startSubCrit';
						}
						elseif($i == count($criteriaSubCriteriasUsed))
						{
							$extraClass = 'endSubCrit';
						}
						$subCriteria = $criteria->get_single_criteria(-1, $subCriteriaUsed);
						if($subCriteria)
						{
							$retval .= "<td class='$extraClass subCriteria subCriteria_$criteriaName crit'><span class='critValue'><img class='criteriaPresent' src=\"{$CFG->wwwroot}/mod/qualification/pix/blackX.jpg\">";
							$retval .= "</span>";
							//$retval .= $subCriteria->build_criteria_value_popup($unit->get_id(), false);
							$retval .= "</td>";
						}
						else
						{
							$retval .= "<td class='$extraClass subCriteria subCriteria_$criteriaName critNo'></td>";
						}
						
					}
				}
			}//end if sub
		}
		else
		{
			//else the criteria isnt on the unit
			$retval .= " class='critNo'></td>";
			if($subCriteriaArray)
			{
				$criteriaSubCriteriasUsed = $subCriteriaArray[$criteriaName];
				if($criteriaSubCriteriasUsed)
				{
					$i=0;
					foreach($criteriaSubCriteriasUsed AS $subCriteriaUsed)
					{
						$i++;
						$extraClass = '';
						if($i == 1)
						{
							$extraClass = 'startSubCrit';
						}
						elseif($i == count($criteriaSubCriteriasUsed))
						{
							$extraClass = 'endSubCrit';
						}
						$retval .= "<td class='$extraClass subCriteria subCriteria_$criteriaName critNo'></td>";	
					}
				}
			}//end if subcriteria
		}//end else the criteria isnt on the unit.
		return $retval;
	}
    
    protected function get_criteria_not_on_unit($criteriaCount, 
            $criteriaCountArray, $advancedMode, 
            $editing, $criteriaName, 
            $subCriteriaArray = false, $row = array())
	{
//		$retval = "";
		//if the criteria isnt on the unit
		//create a blank cell
//		$retval .= "<td class='grid_cell_blank'></td>";
        $row[] = '<span class="grid_cell_blank"></span>';
		if($subCriteriaArray)
		{
			//get the sub criteria that should be here and output a blank cell for each
			//Get the used Sub Criteria Names from the heading for this criteriaName
			//for example get the p1.1, P1.2 ect for the P1
			$criteriaSubCriteriasUsed = $subCriteriaArray[$criteriaName];
			//Lets see if this Criteria has the subcriteria that matches the heading
			$i= 0;
			foreach($criteriaSubCriteriasUsed AS $subCriteriaUsed)
			{
				$extraClass = '';
				$i++;
				if($i == 1)
				{
					$extraClass = 'startSubCrit';
				}
				elseif($i == count($criteriaSubCriteriasUsed))
				{
					$extraClass = 'endSubCrit';
				}
				$criteriaCount++;
                $row[] = '<span class="grid_cell_blank '.$extraClass.' subCriteria subCriteria_'.$criteriaName.'"></span>';
            }
		}
		return $row;
	}
    
    protected function set_up_criteria_grid($criteria, $extraCellClass, 
	$possibleValues, $editing, $advancedMode, $sub, $unit, 
            $firstLast, $user)
	{
		global $CFG;
		$criteriaName = $criteria->get_name();
		$retval = "";
		//get the students criteria information
		//lets get the comments that have been added to the students criteria. 
		$studentComments = '';
		if($criteria->get_comments() && $criteria->get_comments() != '')
		{
			$studentComments = $criteria->get_comments();
		}	
        
        $studentComments = iconv('UTF-8', 'ASCII//TRANSLIT', $studentComments);  
               
		//get the actual object. I.e. what value has been given to 
		//the students criteria. 
        $studentValueObj = null;
		$studentValueObj = $criteria->get_student_value();	
        if(!$studentValueObj)
        {
            //then we need to create a default one
            $studentValueObj = new Value();
            $studentValueObj->create_default_object('N/A', BTECQualification::FAMILYID);
        }
		$cellClass = 'noComments';
		$comments = false;
		if($studentComments != '')
		{
			// do we have comments?
			$cellClass = 'criteriaComments';
			$comments = true;
		}
		
		//ok now lets output the actual cell/s containing
		//the student info
		if($editing)
		{
			//if we are editing then we need input options
			if($advancedMode)
			{
				//advanced mode allows
				//drop down options and comments
				//this td is used as the hover over tooltip.
				$retval .= $this->advanced_edit_grid($criteria, $unit, 
												$studentComments, 
                        $possibleValues, $studentValueObj, $extraCellClass, 
                        $firstLast);
			}
			else //editing but simple mode
			{			
				$retval .= $this->simple_edit_grid($criteria, $unit, 
						$studentValueObj, $comments);
			}
		}
		else //NOT EDITING
		{
			
			if($advancedMode)
			{
				$retval .= $this->advanced_not_edit_grid($criteria, $studentValueObj, 
								$unit, $comments);
			}
			else //not editing but simple mode
			{
				$retval .= $this->simple_not_edit_grid($criteria, $studentValueObj, 
							$unit, $comments);
			}//end else simple mode. when not editing
			
		}//end else not editing
		
		return $retval;
	}
    
    /**
     * 
     * @param type $studentCriteria
     * @param type $unit
     * @param type $criteriaName
     * @param type $comments
     * @param type $studentComments
     * @param type $possibleValues
     * @param type $studentValueID
     * @param type $extraCellClass
     * @param type $firstLast
     * @param type $user
     * @return string
     */
    protected function advanced_edit_grid($studentCriteria, $unit, 
	$studentComments, $possibleValues, $studentValueObj, 
            $extraCellClass, $firstLast)
	{
        $disabled = '';
        if(isset($this->gridLocked) && $this->gridLocked)
        {
            $disabled = 'disabled="disabled"';
        }
        global $CFG, $DB;
		$retval = '';
		//advanced mode allows
		//drop down options and comments
		//get all of the possible values that can be selected for the 
		//criteria
		//this td is used as the hover over tooltip.
		$extraClass = $extraCellClass;
		if($firstLast == -1)
		{
			//this means its the last column we are dealing with.
			//this negates their being two last columns which would draw
			//the extra border
			$extraClass = '_'.$extraCellClass;	
		}
		$extraClassEnd = $extraCellClass;
		if($firstLast == 1)
		{
			//this means its the first column we are dealing with.
			//this negates their being two first columns which would draw
			//the extra border
			$extraClassEnd = '_'.$extraCellClass;	
		}
		//$retval .= "<td class='$extraClass criteriaValue r".$unit->get_id()." c$criteriaName'>";								
		$retval .= "<span class='stuValue' student='".$this->studentID."' qual='$this->id' unit='".$unit->get_id()."' id='cID_".$studentCriteria->get_id().
                "_uID_".$unit->get_id()."_SID_".$this->studentID."_QID_".
                $this->id."'>";
        $retval .= "<select unit='".$unit->get_id()."' $disabled class='criteriaValueSelect' id='cID_".
                $studentCriteria->get_id().
                "_uID_".$unit->get_id()."_SID_".$this->studentID.
                "_QID_".$this->id."' student='".$this->studentID."' qual='$this->id' name='cID_".$studentCriteria->get_id().
                "'><option value='-1'></option>";
		if($possibleValues)
		{
			foreach($possibleValues AS $value)
			{
				//output each option
				//title used for on hover
                $valueShort = $value->shortvalue;
                if(isset($value->customshortvalue) && trim($value->customshortvalue) != '')
                {
                    $valueShort = $value->customshortvalue;
                }
                $valueLong = $value->value;
                if(isset($value->customvalue) && trim($value->customvalue) != '')
                {
                    $valueLong = $value->customvalue;
                }
                $selected = '';
				if($studentValueObj->get_id() == $value->id)
				{
                    $selected = 'selected';
				}
                $retval .= "<option $selected value = '$value->id' title='$valueLong'>".
                        "$valueShort - $valueLong</option>";
			}
		}
		$retval .= "</select></span>&nbsp;";
        $student = $this->student;
		        
        // Change this so each thing has its own attribute, wil be easier
        $commentImgID = "cmtCell_cID_".$studentCriteria->get_id()."_uID_".$unit->get_id()."_SID_".$this->studentID.
                        "_QID_".$this->id;
        
        $username = '';
        $fullName = '';
        if($student && isset($student->username))
        {
            $username = $student->username;
            $username = fullname($student);
        }
        $username = htmlentities( $username, ENT_QUOTES );
        $fullname = htmlentities( $username, ENT_QUOTES );
        $unitname = htmlentities( $unit->get_name(), ENT_QUOTES);
        $critname = htmlentities($studentCriteria->get_name(), ENT_QUOTES);        
                                
		if(!is_null($studentComments) && $studentComments != '')
		{ 
			$retval .= "<img id='{$commentImgID}' grid='student' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' class='editComments' title='Click to Edit Comments' ".
                    "alt='Click to Edit Comments' src='$CFG->wwwroot/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/comments.jpg'>";
            $retval .= "<div class='tooltipContent'>".nl2br( htmlentities($studentComments, ENT_QUOTES) )."</div>";
        }
		else
		{
            $retval .= "<img id='{$commentImgID}' grid='student' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' class='addComments' title='Click to Add Comments' ".
                    "alt='Click to Edit Comments' src='$CFG->wwwroot/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/plus.png'>";
        }
        
        
		return $retval;
	}
	
	protected function simple_edit_grid($studentCriteria, $unit, 
	$studentValueObj, $comments )
	{
        $disabled = '';
        if(isset($this->gridLocked) && $this->gridLocked)
        {
            $disabled = 'disabled="disabled"';
        }
		$retval = "";
		$retval .= "<span class='stuValue' qual='$this->id' student='".$this->studentID."' unit='".$unit->get_id()."' id='cID_".$studentCriteria->get_id().
                "_uID_".$unit->get_id()."_SID_".$this->studentID."_QID_".$this->id.
                "'><input $disabled student='".$this->studentID."' type='checkbox'".
                "class='criteriaValueMet criteriaCheck' unit='".$unit->get_id()."' qual='$this->id' name='cID_".$studentCriteria->get_id()."'".
                "id='cID_".$studentCriteria->get_id()."_uID_".$unit->get_id()."_SID_".$this->studentID."_QID_".$this->id."'";
		if($studentValueObj->get_short_value() == 'A')
		{
			$retval .= "checked='checked'";
		}
		$retval .= "/></span>";
        
        if (!is_null($comments) && $comments != ''){
            $retval .= "<div class='tooltipContent'>".nl2br( htmlentities($comments, ENT_QUOTES) )."</div>";
        }
        
		return $retval;
	}

	protected function advanced_not_edit_grid($studentCriteria, $studentValueObj, 
            $unit, $comments)
	{
		$retval = '';		
		$class = $studentValueObj->get_short_value();
        $shortValue = $studentValueObj->get_short_value();
        if($studentValueObj->get_custom_short_value())
        {
            $shortValue = $studentValueObj->get_custom_short_value();
        }
		$retval .= "<span id='stCID_".$studentCriteria->get_id()."_UID_".
                $unit->get_id()."_SID_".$this->studentID."_QID_".$this->id.
                "' class='stuValue stuValueNonEdit $class' title=''>".$shortValue."</span>";
        if (!is_null($comments) && $comments != ''){
            $retval .= "<div class='tooltipContent'>".nl2br( htmlentities($comments, ENT_QUOTES) )."</div>";
        }
		return $retval;
	}
	
    /**
     * 
     * @global type $CFG
     * @param type $studentCriteria
     * @param type $studentValueObj
     * @param type $unit
     * @param type $comments
     * @return string
     */
	protected function simple_not_edit_grid($studentCriteria, $studentValueObj, 
            $unit, $comments)
	{
        $studentFlag = $studentCriteria->get_student_flag();
		global $CFG;
		$retval = '';
        $flag = '';
        if(isset($this->studentFlag))
        {
            $flag = $this->studentFlag;
        }
		//show all of the symbols for the student
        $imageObj = BTECQualification::get_simple_grid_images($studentValueObj, $studentFlag, $flag);
		$image = $imageObj->image;
		$class = $imageObj->class;
		$retval .= "<span id='stCID_".$studentCriteria->get_id()."_UID_".
                $unit->get_id()."_SID_".$this->studentID."_QID_".
                $this->id."' class='stuValue stuValueNonEdit $class' title=''><img src='".
                $CFG->wwwroot."/blocks/bcgt/plugins/bcgtbtec$image'/></span>";
        if (!is_null($comments) && $comments != ''){
            $retval .= "<div class='tooltipContent'>".nl2br( htmlentities($comments, ENT_QUOTES) )."</div>";
        }
		return $retval;
	}
	
	/**
	 * THIS WHOLE FUNCTION AND FUNCTIONALITY REALLY SHOULD BE IN THE VALUE CLASS!!!!
	 * WHAT WAS I THINKING? SHOULD BE SUB CLASS VALUES SUCH AS BTECValue
	 * @param unknown_type $studentValue
	 * @param unknown_type $studentCriteriaMet
	 */
	public static function get_simple_grid_images($studentValueObj, 
            $studentFlag = '', $flag = '')
	{
		$obj = new stdClass;
        $class = 'stuValue'.$studentValueObj->get_short_value();
        if($studentFlag == BTECCriteria::LATE && $flag == 'L')
        {
            //then lets get the late
            $image = $studentValueObj->get_core_image_late();
            if($studentValueObj->get_custom_image_late())
            {
                $image = $studentValueObj->get_custom_image_late();
            }
        }
        else
        {
            $image = $studentValueObj->get_core_image();
            if($studentValueObj->get_custom_image())
            {
                $image = $studentValueObj->get_custom_image();
            }
        }
        if($image == '')
        {
            $image = '/pix/grid_symbols/core/notattempted.png';
        }
		$obj->image = $image;
		$obj->class = $class;
		
		return $obj;
	}
    
    public static function edit_unit_award($unit, $rank, $award, $unitAwards = null, $gridLocked = false)
	{
        $disabled = '';
        if($gridLocked)
        {
            $disabled = 'disabled="disabled"';
        }
		$retval = "";
        $retval .= "<select $disabled class='unitAward' id='uAw_".$unit->get_id()."' name='unitAwardAPL_".$unit->get_id()."'>";        
		$retval .= "<option value='-1'>NA</option>";
		if($unitAwards)
		{
			foreach($unitAwards AS $possAward)
			{
				$selected = '';
				if($possAward->award == $award)
				{
					$selected = 'selected';
				}
				$retval .= "<option $selected value='$possAward->id'>$possAward->award</option>";
			}
		}
		$retval .= "</select>";
		return $retval;
	}
    
    protected function get_act_grid_header($activities, $grid)
    {
        $editing = false;
        $advancedMode = false;
        if($grid == 'es' || $grid == 'ea')
        {
            $editing = true;
        }
        if($grid == 'a' || $grid = 'ea')
        {
            $advancedMode = true;
        }
        global $CFG;
		$headerObj = new stdClass();
		$header = '';
		$header .= "<thead>";
		$dividers = array();
        $header .= "<tr class='mainRow'>";
        $mainRowRowSpan = 3;    
		//denotes projects
		$header .= "<th rowspan='$mainRowRowSpan'></th>";
        if($advancedMode && $editing)
        {
            $header .= "<th rowspan='$mainRowRowSpan' class='unitComment'></th>";
        }
        elseif(!($editing && $advancedMode))
        {
            $header .= "<th rowspan='$mainRowRowSpan'></th>";
        }
        //columns supported are:
        //picture,username,name,firstname,lastname,email
        $columns = $this->defaultColumns;
        //need to get the global config record
        
        $configColumns = get_config('bcgt','btecgridcolumns');
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
        }
        foreach($columns AS $column)
        {
            $header .="<th rowspan='$mainRowRowSpan'>";
            $header .= get_string(trim($column), 'block_bcgt');
            $header .="</th>";
        }

        $totalHeaderCount = 7;

        //this specifies when there is a border. 
        $criteriaCountArray = array(); 
        $activityTopRow = '';
        $countProcessed = 0;
        $courseID = optional_param('scID', -1, PARAM_INT);
        $groupID = optional_param('grID', -1, PARAM_INT);
        $modIcons = load_mod_icons($courseID, $this->id, $groupID, -1, -1);
        $modLinking = load_bcgt_mod_linking();
        $qual = new stdClass();
            $qual->id = $this->id;
            $activityUnitRow = '<tr>';
            $activityCritRow = '<tr>';
        foreach($activities AS $activity)
        {
            //load the due date
            $dueDate = get_bcgt_mod_due_date($activity->id, $activity->instanceid, $activity->cmodule, $modLinking);
            $activity->dueDate = $dueDate;
            $countProcessed++;
            //top row is for the activity
            //icon, name, due date
            //need to get the criteria selection for this activity
            //
            $totalCriteriaCount = 0;
            $activityUnits = bcgt_get_mod_unit_criteria($courseID, $this->id, $groupID, $activity->id); 
            foreach($activityUnits AS $unitID => $criterias)
            {
                $unitObj = $this->units[$unitID];
                $activityUnitRow .= '<th role="columnheader" colspan="'.count($criterias).'">'.$unitObj->get_name().'</th>';
                $totalHeaderCount++;
                
                require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
                $criteriaSorter = new CriteriaSorter();
                uasort($criterias, array($criteriaSorter, "ComparisonDelegateByArrayNameLetters"));

                foreach($criterias AS $criteriaID => $criteriaName)
                {
                    $criteriaObj = $unitObj->get_single_criteria($criteriaID);
                    $activityCritRow .= '<th role="columnheader">'.$criteriaObj->get_name().'</th>';
                    $totalCriteriaCount++;
                }
                $criteriaCountArray[] = count($criterias);
                $activityUnits[$unitID] = $criterias;
            }
            //need to sort the criterias 
            //need the icon
            //need the name
            //need the due date. 
            $activityTopRow .= '<th role="columnheader" colspan="'.$totalCriteriaCount.'">';
            if(array_key_exists($activity->module,$modIcons))
            {
                $icon = $modIcons[$activity->module];
                //show the icon. 
                $activityTopRow .= '<span class="activityicon">';
                $activityTopRow .= html_writer::empty_tag('img', array('src' => $icon,
                            'class' => 'bcgtmodcriticon activityicon', 'alt' => $activity->module));
                $activityTopRow .= '</span>';
            }
            $activityTopRow .= '<span class="activityname">';
            $activityTopRow .= $activity->name;
            $activityTopRow .= '</span>';
            $activityTopRow .= '<span class="activityduedate">';
            if($activity->dueDate)
            {
                $activityTopRow .= '<br />'.date('d M Y : H:m', $activity->dueDate); 
            }
            $activityTopRow .= '</span>';
            $activityTopRow .= '</th>';
            if(count($activities) != $countProcessed)
            {
                $activityTopRow .= '<th class="divider"></th>';
                $activityUnitRow .= '<th class="divider"></th>';
                $activityCritRow .= '<th class="divider"></th>';
                $totalHeaderCount++;
            }
            $activity->unitcriteria = $activityUnits;
        }
        $this->activities = $activities;
        $activityCritRow .= '</tr>';
        $activityUnitRow .= '</tr>';
        $header .= $activityTopRow.'</tr>';
        $header .= $activityUnitRow;
        $header .= $activityCritRow;
        $header .= "</thead>";
		$headerObj->header = $header;
        
        //need to work out this one for the activities. 
        
		$headerObj->criteriaCountArray = $criteriaCountArray;
		//$headerObj->orderedCriteriaNames = $criteriaNames;
        $headerObj->totalHeaderCount = $totalHeaderCount;
		return $headerObj;
    }
    
    protected function get_class_grid_header($grid)
    {
        //needs to come up with a grid of the units
        //so needs to get all of the units on the qual:
        global $COURSE;
        $courseID = optional_param('cID', -1, PARAM_INT);
        if($courseID == -1)
        {
            $courseID = $COURSE->id;
        }
        $context = context_course::instance($courseID);
        $units = $this->units;
        //do we need to order the units? 
        //YES WHEN WE HAVE A BLOODY ORDER!!!!!
        $headerObj = new stdClass();
		$header = '';
		//extra one for projects
		$header .= "<thead><tr>";
        //columns supported are:
        //picture,username,name,firstname,lastname,email
        $columns = $this->defaultColumns;
        //need to get the global config record
        
        $configColumns = get_config('bcgt','btecgridcolumns');
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
        }
        foreach($columns AS $column)
        {
            $header .="<th>";
            $header .= get_string(trim($column), 'block_bcgt');
            $header .="</th>";
        }
        if(has_capability('block/bcgt:viewbtecavggrade', $context))
        {
            $header .= '<th>';
            $header .= get_string('award', 'block_bcgt');
            $header .= '</th>';
        }
        foreach($units AS $unit)
        {
            $header .="<th>";
            $header .= $unit->get_uniqueID().' : '.$unit->get_name();
            $header .="</th>";
        }
        $header .= "</tr></thead>";
		$headerObj->header = $header;
		return $headerObj;
    }
    
    protected function get_simple_grid_header($criteriaNames)
    {
        $header = '';
        $header .= "<thead><tr>";
        $header .= '<th></th><th></th>';
        $header .= '<th>'.get_string('unit', 'block_bcgt').'</th>';
        $previousLetter = '';
        foreach($criteriaNames AS $criteriaName)
        {
            $letter = substr($criteriaName, 0, 1);
            if($previousLetter != '' && $previousLetter != $letter)
            {
                //if the criteria letter changes (i.e. P to M) then lets 
                //create a divider. 
                $header .= "<th class='divider'></th>";
            }
            $previousLetter = $letter;
            
            $header .= '<th>';
            $header .= BTECQualification::build_criteria_display_name($criteriaName);
            $header .= '</th>';
        }
        $header .= "</tr></thead>";
        return $header;
    }
        
    protected function get_grid_header($totalCredits, $studentView, $criteriaNames, $grid, $subCriteriaArray = false)
	{
        $editing = false;
        $advancedMode = false;
        if($grid == 'es' || $grid == 'ea')
        {
            $editing = true;
        }
        if($grid == 'a' || $grid = 'ea')
        {
            $advancedMode = true;
        }
        global $printGrid;
		$headerObj = new stdClass();
		$header = '';
		//extra one for projects
        $header .= "<thead><tr>";
        $order = optional_param('order', 'spec', PARAM_TEXT);
        if($order == 'actunit')
        {
            //then we want 3 for activity
            $header .= "<th></th>";
            $header .= "<th>".get_string('name','block_bcgt')."</th>";
            $header .= "<th>".get_string('duedate', 'block_bcgt')."</th>";
            
            $colspan = 1;
            //we want max number of units on any activity.
            $activityIDs = array_keys($this->activities);
            //but is the user doing all of the units?
            $maxNoUnits = bcgt_get_max_units_activity($activityIDs, $this->studentID);
            if($maxNoUnits)
            {
                $colspan = $maxNoUnits->maxcount;
            }
            $this->maxNoUnits = $maxNoUnits;
            for($i=1;$i<=$colspan;$i++)
            {
                $header .= '<th>'.get_string('unit', 'block_bcgt').''.$i.'</th>';
            }
            
            $headerObj->criteriaCountArray = array();
            $headerObj->totalCellCount = 0;
        }
        else
        {
            $header .= "<th></th>";
            $warningClass = '';
            if($this->defaultcredits && $this->defaultcredits != $totalCredits)
            {
                $warningClass = 'warningcredits';
            }
            if($advancedMode && $editing)
            {
                $header .= "<th class='unitComment'></th>";
            }
            elseif(!($editing && $advancedMode))
            {
                $header .= "<th></th>";
            }

            $header .= "<th><span class=".$warningClass.">".get_string('unit','block_bcgt')." (".
                    get_string('total','block_bcgt')." ".
                    $this->get_credits_display_name()." $totalCredits/".$this->defaultcredits.")</span></th>";
            $totalCellCount = 3;
            if($studentView)
            {//if its not student view then we are looking at just
                //the qual in general rather than a student.
                $header .= "<th>".get_string('award','block_bcgt')."</th>";
                $totalCellCount++;
                // If qual has % completions enabled
                if($this->has_percentage_completions() && !$printGrid && $studentView){
                    $header .= "<th>% Complete</th>";
                    $totalCellCount++;
                }
            }	
            if($order == 'unitact')
            {
                //but what happens if the user isnt on the unit?
                $unitIDs = array_keys($this->units);
                $maxNoActivities = bcgt_get_max_activity_units($unitIDs, $this->studentID);
                $this->maxNoActivities = $maxNoActivities;
                if($maxNoActivities)
                {
                    $colspan = $maxNoActivities->maxcount;
                }
                for($i=1;$i<=$colspan;$i++)
                {
                    $header .= '<th>'.get_string('activities', 'block_bcgt').''.$i.'</th>';
                }
                
                $headerObj->criteriaCountArray = array();
                $headerObj->totalCellCount = 0;
            }
            else
            {
                $headerObj = BTECQualification::get_criteria_headers($criteriaNames, $subCriteriaArray, 
                    $advancedMode, $editing, $totalCellCount);
                $subHeader = $headerObj->subHeader;
                $header .= $subHeader;
            }
        }
        
            
		$header .= "</tr></thead>";
		$headerObj->header = $header;
		return $headerObj;
	}
    
    public static function build_criteria_display_name($criteriaName, $criteria = null)
    {
        if(strpos($criteriaName,'_'))
        {
            $criteriaName = str_replace('_', '.',$criteriaName);
            $criteriaName = substr($criteriaName, strpos($criteriaName,'.'));
        }
        if($criteria)
        {
            $originalName = $criteria->get_name();
            $displayName = $criteria->get_display_name();
            if($displayName && $displayName != '' && $originalName != $displayName)
            {
                $criteriaName = $displayName;
            }
        }
        return $criteriaName;
    }
    
    public static function get_criteria_headers($criteriaNames, $subCriteriaArray, 
            $advancedMode, $editing, $totalCellCount = 0)
	{		
		$headerObj = new stdClass();
		
		$subHeader = "";
		$previousLetter = '';
		$criteriaCountArray = array();
		$subCriteriaNo = array();
		if($criteriaNames)
		{
			$criteriaCount = 0;
			foreach($criteriaNames AS $criteriaName)
			{
				//for each criteria create the heading. 
				$criteriaCount++;
				$letter = substr($criteriaName, 0, 1);
				if($previousLetter != '' && $previousLetter != $letter)
				{
					//if the criteria letter changes (i.e. P to M) then lets 
					//create a divider. 
					//we also need to know how many criteria we have before each blank space
					//this is then used later. 
					$criteriaCountArray[] = $criteriaCount;
					$subHeader .= "<th class='divider'></th>";
                    $totalCellCount++;
				}
				$previousLetter = $letter;
				if($subCriteriaArray)
				{
					$subCriterias = $subCriteriaArray[$criteriaName];
				}
				$subHeader .= "<th class='criteriaName c$criteriaName'><span class='criteriaName";
				if($subCriteriaArray && $subCriterias)
				{
					$subHeader .= " hasSubCriteria' id='subCriteria_$criteriaName'>";
                    $subHeader .= BTECQualification::build_criteria_display_name($criteriaName)."</span>";
                    $subHeader .= " s";
                    $subHeader .= "</th>";
                    
                }
				else
				{
					$subHeader .= "'>".BTECQualification::build_criteria_display_name($criteriaName)."</span></th>";
				}
                $totalCellCount++;
				if($advancedMode && $editing)
				{
					//if its advanced and editing then we have the extra 
					//cell required for the add/edit comments. 
					$subHeader .= "<th class='blankHeader'></th>";
                    $totalCellCount++;
				}
				$subHeaderClass = 'subCriteria';
				$subCriteriaCount = 0;
				if($subCriteriaArray && $subCriterias)
				{
					foreach($subCriterias AS $subCriteria)
					{
						$criteriaCount++;
						$subCriteriaCount++;
						$subHeader .= "<th class='$subHeaderClass subCriteria_$criteriaName'>".$subCriteria."</th>";
						$totalCellCount++;
                        if($advancedMode && $editing)
						{
							//if its advanced and editing then we have the extra 
							//cell required for the add/edit comments. 
							$subHeader .= "<th class='blankHeader $subHeaderClass subCriteria_$criteriaName'></th>";
                            $totalCellCount++;
                        }
					}
					$subCriteriaNo[$criteriaName] = $subCriteriaCount;
				}
				
			}
		}
		$headerObj->subHeader = $subHeader;
		$headerObj->criteriaCountArray = $criteriaCountArray;
        $headerObj->totalCellCount = $totalCellCount;
		if($subCriteriaArray)
		{
			$headerObj->subCriteriaNo = $subCriteriaNo;	
		}
		return $headerObj;
	}
    
    /**
	 * Gets the criteria names that are used at least once in the units of the qualification. 
	 */
	function get_used_criteria_names()
	{
		//checks all units and see's if the criteria name is used. 
		$usedCriteriaNames = array();
        foreach($this->units AS $unit)
        {
            //is the user doing this unit?
            if(!$unit->get_student_ID() || ($unit->get_student_ID() && $unit->is_student_doing()))
            {
                $unitCriteriaNames = $unit->get_criteria_names();
                if($unitCriteriaNames)
                {
                    //because some units dont have any credits!
                    $usedCriteriaNames = array_merge($unitCriteriaNames, $usedCriteriaNames);
                }
            }
            
            
        }
        $this->usedCriteriaNames = $usedCriteriaNames;
		return $usedCriteriaNames;
        
	}
	
	private function get_used_sub_criteria_names($criteriaNames)
	{
        global $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
        $criteriaSorter = new CriteriaSorter();                
		$units = $this->units;
		$subCriteriaArray = array();
        if($units)
        {
            foreach($units AS $unit)
            {
                foreach($criteriaNames AS $criteriaName)
                {
                    $criteria = $unit->get_single_criteria(-1, $criteriaName);
                    if($criteria)
                    {
                        $subCriterias = $criteria->get_sub_criteria();
                        $subCriteriaNames = array();
                        if($subCriterias)
                        {
                            usort($subCriterias, array($criteriaSorter, "ComparisonDelegateByName"));
                            foreach($subCriterias AS $subCriteria)
                            {
                                $subCriteriaNames[$subCriteria->get_name()] = $subCriteria->get_name();
                            }
                        }
                        if(array_key_exists($criteriaName, $subCriteriaArray))
                        {
                            $subCriteriaArray[$criteriaName] = array_merge($subCriteriaArray[$criteriaName],$subCriteriaNames);	
                        }
                        else
                        {
                            $subCriteriaArray[$criteriaName] = $subCriteriaNames;
                        }
                    }
                }	
            }
        }

		//we need to sort the sub criteria on the off chance that they contain some missing criteria. //skipped criteria
		$arrayKeys = array_keys($subCriteriaArray);
		foreach($arrayKeys AS $key)
		{
			$subArray = $subCriteriaArray[$key];
			
			usort($subArray, array($criteriaSorter, "ComparisonDelegateByArrayName"));
            $subCriteriaArray[$key] = $subArray;
		}
		return $subCriteriaArray;
	}
    
    /**
	 * This will build up the key for the Grid used in student view
	 * and single view. 
	 * SHOULD be a static function to the UNIT view can get to it
	 * At the moment we have duplicate calls. 
	 */
	public static function get_grid_key($string = true)
	{
        global $CFG; 
        $file = $CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec';
        if($string)
        {
            $retval = '';
        }
        else
        {
            $retval = array();
        }
        
        $core = '<span class="keyValue"><img class="keyImage"';
        $core .= 'src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec/pix/'.
                'grid_symbols/commentsSimple.jpg"/> = Comments (Hover to view)'.
                '</span>';
        $warn = '';
        //this now needs to get them from the database!
        $possibleValues = BTECQualification::get_possible_values(BTECQualification::ID, true);
        if($possibleValues)
        {
            foreach($possibleValues AS $possibleValue)
            {
                $value = '<span class="keyValue"><img class="keyImage"';
                if(isset($possibleValue->customimg) && $possibleValue->customimg != '')
                {
                    $icon = $possibleValue->customimg;
                }
                else
                {
                    $icon = $possibleValue->coreimg;
                }
                if(isset($possibleValue->customvalue) && $possibleValue->customvalue != '')
                {
                    $desc = $possibleValue->customvalue;
                }
                else
                {
                    $desc = $possibleValue->value;
                }
                $value .= ' src="'.$file.$icon.'"/> = '.$desc.'</span>';
                if($string)
                {
                    $retval .= $value;
                }
                else
                {
                    $retval[] = $value;
                }
                if($possibleValue->shortvalue == 'A')
                {
                    
                    $warn = "<p><span>(Only $desc will be used towards Unit Award)</span> </p>";
                }
            }
        }      
        if($string)
        {
            $retval .= $warn;
        }
        return $retval;
        
	}
    
    /**
     * This processes the edit_single_student_units section.
     * IT is called AFTER load_student_information
     * IT is called from within the students qual object
     */
    protected function process_edit_single_students_units_page()
    {
        $units = $this->get_units();
        foreach($units AS $unit)
        {
            //get the check boxes
            //name is in the format of $name='s'.$student->id.'U'.$unit->get_id().'Q'.$this->id;
            $fieldToCheck = 's'.$this->studentID.'U'.$unit->get_id().'Q'.$this->id;
            $this->process_edit_students_units($unit, $fieldToCheck, $this->studentID);
        }
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
    
    public function get_edit_student_page_init_call()
    {
        global $PAGE;
        $jsModule = array(
            'name'     => 'mod_bcgtbtec',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        $PAGE->requires->js_init_call('M.mod_bcgtbtec.initstudunits', null, true, $jsModule);
//        $PAGE->requires->js('/blocks/bcgt/js/block_bcgt_functions.js');
//        $PAGE->requires->js('/blocks/bcgt/js/jquery.dataTables.js');
//        $PAGE->requires->js('/blocks/bcgt/js/FixedColumns.js');
//        $PAGE->requires->js('/blocks/bcgt/js/FixedHeader.js'); 
    }
    
    /**
     * Multiple is denoting if this will appear multiple times on a page
     * @global type $OUTPUT
     * @global type $DB
     * @global type $PAGE
     * @global type $CFG
     * @param type $multiple
     * @return string
     */
    public function get_edit_students_units_page($courseID = -1, $multiple = false, $count = 1, $action = 'q')
    {
        global $OUTPUT, $DB, $PAGE, $CFG;
        $sAID = optional_param('sAID', -1, PARAM_INT);
        $heading = $this->get_type().''. 
        ' '.$this->get_level()->get_level().''. 
        ' '.$this->get_subType()->get_subType();
        $heading .= ' '.$this->get_name().'<br />';
        $heading .= ' ('.get_string('bteccredits','block_bcgt').': '.$this->get_credits().')';
        if(!$multiple)
        {
            $jsModule = array(
                'name'     => 'mod_bcgtbtec',
                'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
                'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
            );
            $PAGE->requires->js_init_call('M.mod_bcgtbtec.initstudunits', null, true, $jsModule);
        }
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
        $out .= '<p class="totalPossibleCredits">Total Possible Unit Credits:'.$this->get_current_total_credits().'</p>';
        //TODO put this on the QUALIFICATION so it can be loaded through AJAX???
        if($units || $students)
        {
            $out .= '<table id="btecStudentUnits'.$count.'" class="btecStudentsUnitsTable" align="center">';
            $out .= '<thead><tr><th class="rowOptionsCol" rowspan="1"></th><th class="picCol" rowspan="1">';
            $out .= '</th><th class="usernameCol" rowspan="1">'.get_string('username').'</th>';
            $out .= '<th class="nameCol" rowspan="1">'.get_string('name').'</th>';
            $out .= '<th class="creditsCol" rowspan="1">';
            $out .= get_string('bteccredits', 'block_bcgt');
            $out .= '</th>';
            $out .= '<th rowspan="1" class="rowUserOptionCol"></th>';
            $row = '';
            $lowRow = '<th></th><th></th><th></th><th></th><th></th><th></th>';
            foreach($units AS $unit)
            {
                $row .= '<th>'.$unit->get_uniqueID().' : '.$unit->get_name().
                        ' : '.$unit->get_credits().' '.get_string('bteccredits', 'block_bcgt').
                        '</th>';
                $lowRow .= '<th><a href="edit_students_units.php?qID='.$this->id.'&uID='.$unit->get_id().'" title="Select all Students for this Unit">'.
                        '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowdown.jpg"'. 
                        'width="25" height="25" class="unitsColumn" id="q'.$this->id.'u'.$unit->get_id().'"/>'.
                        '</a></th>';
            }
            
            $out .= $row.'</tr><tr>'.$lowRow.'</tr>';
            $out .= '</thead><tbody>';
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
                //have the users been loaded before?
                //if not it will load up an array that contains
                //a QUAL object for each student
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
                $loadParams->loadAddUnits = false;
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
                            //are we forcing the student to be added to all units?
                            $studentQual->add_student_to_all_units();
                        }
                        elseif($forceUnChecked)
                        {
                            //are we taking the student from all units?
                            $studentQual->remove_student_from_all_units();
                        }
                        //GETS all of the units, not just the students units. 
                        //but has in them if student is doing it or not
                        $studentsUnits = $studentQual->get_units();
                    }
                    $out .= '<tr>';
                    $out .= '<td class="rowOptionsCol">'.
                            '<a href="edit_students_units.php?qID='.$this->id.'&sAID='.
                            $student->id.'" id="chq'.$this->id.'s'.$student->id.'" '.
                            'title="Copy this student selection to all in this grid">'.
                            '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/infinity.jpg"'. 
                            'width="25" height="25" class="studentAll" id="chq'.$this->id.'s'.$student->id.'"/>'.
                            '</td>';
//                    $out .= '<td class="picCol">'.$OUTPUT->user_picture($student, array(1)).'</td>';
                    $out .= '<td></td>';
                    $out .= '<td class="usernameCol">'.$student->username.'</td>';
                    $out .= '<td class="nameCol">'.$student->firstname.' '.$student->lastname.'</td>';
                    $out .= '<td class="creditsCol">';
                    $out .= $studentQual->get_students_total_credits();
                    $out .= '</td>';
                    $out .= '<td class="rowUserOptionCol"><a href="edit_students_units.php?qID='.$this->id.'&sID='.$student->id.'" title="Select all Units for this Student">'.
                            '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowright.jpg"'. 
                            'width="25" height="25" class="studentRow" id="q'.$this->id.'s'.$student->id.'"/>'.
                            '</a></td>';
                    foreach($studentsUnits AS $unit)
                    {
                        //we need to check if its actually on the qual above though!
                        //i .e. it may have been removed from the qual!
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
    
    private function get_students_units_data()
    {
        $sql = "SELECT distinct(userunit.id) as id, user.id as userid, user.username, 
            user.firstname, user.lastname, userqual.bcgtqualificationid, unit.id as unitid, 
            unit.name as unitname, typeaward.award 
            FROM {user} user
            JOIN {block_bcgt_user_unit} userunit ON userunit.userid = user.id 
            JOIN {block_bcgt_user_qual} userqual ON userqual.userid = user.id 
            AND userqual.bcgtqualificationid = userunit.bcgtqualificationid 
            JOIN {block_bcgt_unit} unit ON unit.id = userunit.bcgtunitid
            WHERE userqual.bcgtqualificationid = ? AND userunit.bcgtqualificationid = ? 
            ORDER BY user.lastname ASC, unit.id ASC";
    }
    
    public function get_edit_single_student_units($currentCount)
    {
        global $CFG;
        //are we saving this one?
        //All or just this one still means this one
        if(isset($_POST['saveAll']) || 
                (isset($_POST['save'.$this->studentID.'q'.$this->id.''])))
        {
            $this->process_edit_single_students_units_page();
        }
        $retval = '';
        $retval .= '<h4 class="singleStudentUnitsHeader">'.$this->get_display_name().'</h4>';
        //id="singleStudentUnits'.$currentCount.'"
        $retval .= '<table id="singleStudentUnits'.$currentCount.'" class="singleStudentUnits" align="center"><thead>'.
                '<tr><th>Credits</th><th></th>';
        $units = $this->get_units();
        if($units)
        {
            foreach($units AS $unit)
            {
                $retval .= '<th>'.$unit->get_uniqueID().' : '.$unit->get_name().
                        ' : '.$unit->get_credits().' '.get_string('bteccredits', 'block_bcgt').
                        '</th>';
            }
        }       
        $retval .= '</tr></thead><tbody><tr>';
        $retval .= '<td>'.$this->get_students_total_credits().'</td>';
        $retval .= '<td class="singleStudentUnitsSelAll"><a href="edit_students_units.php?qID='.$this->id.''.
            '&sID='.$this->studentID.'" title="Select all Units for this Student">'.
            '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowright.jpg"'. 
            'width="25" height="25" class="studentRow" id="s'.$this->studentID.'q'.$this->id.'"/>'.
            '</a></td>';
        foreach($units AS $unit)
        {
            $checked = '';
            if($unit->is_student_doing() || $unit->is_student_doing() == 'Yes')
            {
                $checked = 'checked="checked"';
            }
            $name='s'.$this->studentID.'U'.$unit->get_id().'Q'.$this->id;
            $retval .= '<td><input id="chs'.$this->studentID.'q'.$this->id.'u'.$unit->get_id().
                    '" class="eSU chs'.$this->studentID.'q'.$this->id.' chu'.$unit->get_id().
                    '" type="checkbox" '.$checked.' name="'.$name.'"/></td>';
        }
        $retval .= '</tr></tbody></table>';
        $retval .= '<input type="submit" name="save'.$this->studentID.'q'.$this->id.'" value="Save"/>';
        
        return $retval;
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
    
    /**
	 * Does this qual type have a final award that is given to the student
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
                $params->award = $award->targetgrade;
                $params->type = $award->type;
                $params->ucasPoints = $award->ucaspoints;
                $params->unitsScoreUpper = $award->unitsscoreupper;
                $params->unitsScoreLower = $award->unitsscorelower;
                $qualAward = new QualificationAward($award->breakdownid, $params);
                $retval->{$award->type} = $qualAward;
            }
            return $retval;
		}
		return false;
	}
    
    /**
     * Settings: 
     * Show Different Award Options
     * Ability to turn on and off different grid options
     * Ability to select grid symbols. 
     * @return string
     */
    public function get_qual_settings_page()
    {
        
        
        
        return $retval;
    }
    
    /**
	 * Calculate the final award and if its all units
	 * have been awarded then its final
	 */
	public function calculate_final_grade()
	{
		return $this->calculate_qual_award(true);
	}
	
	/**
	 * Calculate the predicted award and if its all units
	 * have been awarded then its final else predicted
	 */
	public function calculate_predicted_grade()
	{
		return $this->calculate_qual_award(false);
	}
    
    public function get_edit_single_student_units_init_call()
    {
        global $PAGE;
        $jsModule = array(
            'name'     => 'mod_bcgtbtec',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        $PAGE->requires->js_init_call('M.mod_bcgtbtec.initsinglestudunits', null, true, $jsModule);
    }
    
    protected function calculate_actual_qual_award($failIfPredicted = false, $pointsPerXCredits = 1)
	{        
        $noUnitsAwarded = 0;
		$countAwards = 0;
		$unitPoints = 0;
		$warningCount = 0;
		$totalCredit = 0;
		$creditsAward = 0;
        $unitPointsNoMin = 0;
        $unitPointsNoMax = 0;
        $unitPointsAtMin = 0;
        $unitPointsAtMax = 0;
        //what are the points that each unit can be worth if it was set to the minimum
        //possible grade?
        $unitPointsAtMinRecord = $this->get_min_award_points($this->level->get_id(),$this->get_class_ID());
        //what are the points that each unit can be worth if it was set to the max
        //possible grade?
        $unitPointsAtMaxRecord = $this->get_max_award_points($this->level->get_id(),$this->get_class_ID());
        if($unitPointsAtMinRecord)
        {
            $unitPointsAtMin = $unitPointsAtMinRecord->points;
        }
        if($unitPointsAtMaxRecord)
        {
            $unitPointsAtMax = $unitPointsAtMaxRecord->points;
        }
		//Get all of the units that have an award
		//add up all of the points that these awards make
		//count them
        $countUnitsOn = 0;
		foreach($this->units AS $unit)
		{
			//we want to only count the units a student is 
			//actually doing on the qual
			if($unit->is_student_doing())
			{
                $countUnitsOn++;
				//credits for the unit
				$unitCredit = $unit->get_credits();
				if($unitCredit == null || $unitCredit == 0)
				{
                    //we are skipping units that have a credit of 0
                    continue;
//					$unitCredit = 10;
//					$warningCount++;
				}
				$totalCredit = $totalCredit + $unitCredit;
				$unitAward = $unit->get_user_award();
				if($unitAward != null && $unitAward->get_id() != '' && $unitAward->get_id() != null && $unitAward->get_id() > 0)
				{
					//does the student have a unit award?
					$creditsAward = $creditsAward + $unitCredit;
					$countAwards++;
					//then the unit has an award
					//get the points that the unit with this award at this level is worth
					$pointsRecord = $this->get_unit_points($unitAward->get_id(), $this->level->get_id());
					if($pointsRecord)
					{
                        $noUnitsAwarded++;
						//lets get the unitPoints.
						//we need to take into consideration the $pointsPerXCredits
						//so divide the unitCredits by points per X credits before we multiply 
						$unitPoints = $unitPoints + ($pointsRecord->points * ($unitCredit/$pointsPerXCredits));
					}
				}
				elseif($failIfPredicted)
				{
					return false;
				}
                else {
                    //we dont have an award. 
                    //so lets add it to those without an award
                    $unitPointsNoMin = $unitPointsNoMin + ($unitPointsAtMin * ($unitCredit/$pointsPerXCredits));
                    $unitPointsNoMax = $unitPointsNoMax + ($unitPointsAtMax * ($unitCredit/$pointsPerXCredits));
                }
			}
		}
		//At this stage we have:
		//UnitPoints = total unitpoints for all units WITH AN AWARD
		//creditsAward = total credits for all units WITH AN AWARD
		//totalCredit = total credits for all units STUDENT IS DOING
        //creditsNoAward = total credits for all units with NO award
        //unitPointsNoMin = total unitpoints for all units with NO award AT PASS level
		//unitPointsNoMax = total unitpoints for all units with NO award AT DISS level
		$type = 'Predicted';
		$predicted = true;
		if($creditsAward == $totalCredit)
		{
			$type = 'Final';
			$predicted = false;
		}
		$averagePoints = 0;
		if($countAwards != 0)
		{
			//this is the average points per credit that the student has an award for
            $averagePoints = $unitPoints/($creditsAward/$pointsPerXCredits);
		}	
		//count number of actual units
		//predicted points score = average*totalcredit
		$overallPoints = $averagePoints * ($totalCredit/$pointsPerXCredits);
        $overallMinPoint = $unitPoints + $unitPointsNoMin;
        $overallMaxPoint = $unitPoints + $unitPointsNoMax;
		//Try and get the final award (may not do if we dont have enough
		//points)
        $retval = new stdClass();
        $minUnitAwards = get_config('bcgt', 'btecunitspredgrade');
        $awardRecord = null;
        $retval->averageAward = null;
        if($noUnitsAwarded >= $minUnitAwards)
        {
            $awardRecord = $this->get_final_grade_by_points($overallPoints);
            if($awardRecord)
            {
                $params = new stdClass();
                $params->award = $awardRecord->targetgrade;
                $params->type = $type;
                $params->ucasPoints = $awardRecord->ucaspoints;

                //get the qual award by those points
                $qualAward = new QualificationAward($awardRecord->id, $params);
                if($warningCount != 0)
                {	
                    $qualAward->set_warningCount($warningCount);
                    $qualAward->set_warning("$warningCount units had no credits, assumed 10 credits for calculation");
                }
                //update the students award in the DB
                $this->update_qualification_award($qualAward);
                $retval->averageAward = $qualAward;
            }
        }
		
		$minAward = $this->get_final_grade_by_points($overallMinPoint);
        if($minAward)
        {
            $params = new stdClass();
            $params->award = $minAward->targetgrade;
            $params->type = 'Min';
            $params->ucasPoints = $minAward->ucaspoints;
            
			//get the qual award by those points
			$qualMinAward = new QualificationAward($minAward->id, $params);
			if($warningCount != 0)
			{	
				$qualMinAward->set_warningCount = $warningCount;
				$qualMinAward->set_warning = "$warningCount units had no credits, assumed 10 credits for calculation";
			}
			//update the students award in the DB
			$this->update_qualification_award($qualMinAward);
			$retval->minAward = $qualMinAward; 
        }
        $maxAward = $this->get_final_grade_by_points($overallMaxPoint);
        if($maxAward)
        {
            $params = new stdClass();
            $params->award = $maxAward->targetgrade;
            $params->type = 'Max';
            $params->ucasPoints = $maxAward->ucaspoints;
            
			//get the qual award by those points
			$qualAwardMax = new QualificationAward($maxAward->id, $params);
			if($warningCount != 0)
			{	
				$qualAwardMax->set_warningCount = $warningCount;
				$qualAwardMax->set_warning = "$warningCount units had no credits, assumed 10 credits for calculation";
			}
			//update the students award in the DB
			$this->update_qualification_award($qualAwardMax);
			$retval->maxAward = $qualAwardMax; 
        }
        
        if(!$awardRecord && !$minAward && !$maxAward)
        {
            return false;
        }
        $this->studentAward = $retval;
        return $retval;
	}
    
    /**
	 * Updates the users Qualification 
	 * award in the database with the one passed in
	 * If the user doesnt have an award before then it inserts it
	 * @param unknown_type $award
	 */
	public function update_qualification_award($award)
	{    
        global $DB;
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_UPDATED_QUAL_AWARD, $this->studentID, $this->id, null, null, $award->get_id());

		$obj = new stdClass();
		$obj->bcgtqualificationid = $this->id;
		$obj->userid = $this->studentID;
		$obj->bcgtbreakdownid = $award->get_id();
		$obj->type = $award->get_type();
        $obj->warning = "";
		if($award->get_warningCount() && $award->get_warningCount() != 0)
		{
			$obj->warning = $award->get_warning();
		}
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
    
    public function load_qual_criteria_student_info($studentID, $qualID)
	{
		return false;
	}
    
    /**
	 * This calculates the Qualification Award
	 * If all units have been awarded it can calculate the final grade
	 * if only some units have been awarded it MAY be able to calculate 
	 * the predicted/progress award.
	 * Each unit has credits
	 * At each Qualification award level the unit award is worth a set number of points
	 * Each units points are then credits * points
	 * We can get the total unit points for the users units and calculate an 
	 * average per credit.
	 * This can be pushed out to predict the final award by taking the average and
	 * multiplying it by the total credits of the qual
	 * This points value is then looked up in the database and an award MAY be
	 * able to get it.
	 * Its possible if they only have one PASS (lets say a 7) for them
	 * not to be able to get a full award (If a student gets passes at all
	 * units they may not get an award at all)
	 * @param unknown_type $failIfPredicted
	 */
	protected function calculate_qual_award($failIfPredicted = false)
	{		
		return $this->calculate_actual_qual_award($failIfPredicted, 1);
	}
    
    /**
	 * Used to get the type specific title vales and labels.
	 */
	public function get_type_qual_title()
	{
		//At the top of add/remove units from Qual there is certain
		//information that will need to be displayed per qual.
		return get_string('bteccredrequired','block_bcgt').' : '.$this->credits;
	}
    
    public function get_unit_list_type_fields()
	{
		//This is used for the add/remove qualifications page. Different qual
		//types may want to display different information. 
		return '<label for="credits">'.get_string('btectotalcredits','block_bcgt').' : </label>
		<input type="text" name="credits" disabled="disabled" value="'.
                $this->get_current_total_credits().'"/>
		<p class="note">'.get_string('btectotalcrednote','block_bcgt').'</p>';
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
    
    public static function get_instance($qualID, $params, $loadParams)
    {   
        if(!$params || !isset($params->level))
        {
            $levelID = optional_param('level', -1, PARAM_INT);
            if(!$params)
            {
                $params = new stdClass();
            }
            if($levelID)
            {
                $level = new Level($levelID);
                $params->level = $level;
            } 
        }
        if(!$params || !isset($params->subtype))
        {
            $subTypeID = optional_param('subtype', -1, PARAM_INT);
            if(!$params)
            {
                $params = new stdClass();
            }
            if($subTypeID)
            {
                $subType = new SubType($subTypeID);
                $params->subType = $subType;
            }
        }

        return new BTECQualification($qualID, $params, $loadParams);
    }
    
    public static function get_pluggin_qual_class($typeID = -1, $qualID = -1, 
            $familyID = -1, $params = null, $loadParams = null)
    {
        global $CFG;
        $subTypeID = -1;
        if($params)
        {

            if($params->subtype)
            {
                $subTypeID = $params->subtype;
            }
        }
        if($subTypeID == -1)
        {
            if(isset($_REQUEST['subtype']))
            {
                $subTypeID = $_REQUEST['subtype'];
            }
        }
        $levelID = -1;
        if($params)
        {
            if($params->level)
            {
                $levelID = $params->level;
            }
        }
        if($levelID == -1)
        {
            if(isset($_REQUEST['level']))
            {
                $levelID = $_REQUEST['level'];
            }
        }
        $params = new stdClass();
        $params->level = new Level($levelID);
        $params->subType = new Subtype($subTypeID);
        switch($levelID)
        {
            case(Level::level1ID):
                require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECLowerQualification.class.php');
                return new BTECLowerQualification($qualID, $params, $loadParams);
                break;
            case(level::level2ID):
            case(level::level3ID):
                require_once('BTECSubType.class.php');
                switch($subTypeID)
                {
                    case(BTECSubType::BTECFndDipID):
                        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFoundationQualification.class.php');
                        return new BTECFoundationQualification($qualID, $params, $loadParams);
                        break;
                    default:
                        return new BTECQualification($qualID, $params, $loadParams);
                        break;
                }	
                break;
            case(Level::level4ID):	
            case(Level::level5ID):
                require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECHigherQualification.class.php');
                return new BTECHigherQualification($qualID, $params, $loadParams);
                break;

            default:
                return new BTECQualification($qualID, $params, $loadParams);
                break;
        }
    }
    
    /**
	 * Used to get the credits value from the database
	 * @param $id
	 */
	protected static function retrieve_credits($qualID)
	{
		global $DB;
		$sql = "SELECT credits FROM {block_bcgt_qualification} WHERE id = ?";
		return $DB->get_record_sql($sql, array($qualID));
	}
    
    /**
	 * Gets the students qualification award from the database
	 * @return Found
	 */
	protected function get_students_qual_award($awardType = null)
	{
		global $DB;
		$sql = "SELECT useraward.id as id, useraward.type, 
            breakdown.id AS breakdownid, bcgttargetqualid, 
		targetgrade, ucaspoints, unitsscorelower, unitsscoreupper 
        FROM {block_bcgt_user_award} AS useraward 
		JOIN {block_bcgt_target_breakdown} AS breakdown ON breakdown.id = useraward.bcgtbreakdownid
		WHERE bcgtqualificationid = ?
		AND userid = ?";
        $params = array($this->id, $this->studentID);
        if($awardType)
        {
            $sql .= " AND useraward.type = ?";
            $params[] = $awardType;
        }
		return $DB->get_records_sql($sql, $params);
	}
    
    /**
	 * Gets the points the unit is worth at the Qualification Level
	 * Each unit is worth a set number of points depending on what level its
	 * at and what award its got.
	 * @param unknown_type $bcgtTypeAwardID
	 * @param unknown_type $bcgtLevelID
	 */
	protected function get_unit_points($bcgtTypeAwardID, $bcgtLevelID)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_unit_points} 
            WHERE bcgtlevelid = ? AND bcgttypeawardid = ?";
		return $DB->get_record_sql($sql, array($bcgtLevelID, $bcgtTypeAwardID));
	}
    
    /**
	 * Gets the points the unit is worth at the Qualification Level
	 * Each unit is worth a set number of points depending on what level its
	 * at and what award its got.
	 * @param unknown_type $bcgtTypeAwardID
	 * @param unknown_type $bcgtLevelID
	 */
	protected function get_min_award_points($bcgtLevelID, $typeAwardID)
	{
		global $DB;
		$sql = "SELECT points.* FROM {block_bcgt_unit_points} points
            JOIN {block_bcgt_type_award} typeAward ON typeAward.id = points.bcgttypeawardid
            WHERE points.bcgtlevelid = ? AND typeAward.ranking = ? AND bcgttypeawardid = ?";
		return $DB->get_record_sql($sql, array($bcgtLevelID, 1, $typeAwardID));
	}

    /**
	 * Gets the points the unit is worth at the Qualification Level
	 * Each unit is worth a set number of points depending on what level its
	 * at and what award its got.
	 * @param unknown_type $bcgtTypeAwardID
	 * @param unknown_type $bcgtLevelID
	 */
	protected function get_max_award_points($bcgtLevelID)
	{
		global $DB;
		$sql = "SELECT points.* FROM {block_bcgt_unit_points} points
            JOIN {block_bcgt_type_award} typeAward ON typeAward.id = points.bcgttypeawardid
            WHERE points.bcgtlevelid = ? AND typeAward.ranking = (SELECT MAX(typeAward.ranking) FROM 
            {block_bcgt_unit_points} points JOIN {block_bcgt_type_award} typeAward ON typeAward.id = 
            points.bcgttypeawardid WHERE points.bcgtlevelid = ?) ORDER BY typeAward.ranking DESC";
		return $DB->get_record_sql($sql, array($bcgtLevelID, $bcgtLevelID), 0, 1);
	}
    
    /**
	 * Gets the final grade from the (qualification award) from the database
	 * based on the points passed down
	 * @param unknown_type $points
	 */
	protected function get_final_grade_by_points($points)
	{
		global $DB;
		$sql = "SELECT breakdown.* FROM {block_bcgt_target_breakdown} breakdown 
		JOIN {block_bcgt_target_qual} targetQual ON targetQual.id = breakdown.bcgttargetqualid 
		JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetQual.id 
		WHERE qual.id = ? AND breakdown.unitsscoreupper > ? 
            AND breakdown.unitsscorelower <= ?";
		return $DB->get_record_sql($sql, array($this->id, $points, $points));
	}
    
    /**
     * Gets the final awrad for the qualifiction by the string of that award
     * e.g. Distinction
     * @global type $CFG
     * @param type $award
     * @return type
     */
    protected function get_final_grade_by_award($award)
	{
		global $DB;
		$sql = "SELECT breakdown.* FROM {block_bcgt_target_breakdown} breakdown 
		JOIN {block_bcgt_target_qual} targetQual ON targetQual.id = breakdown.bcgttargetqualid 
		JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetQual.id 
		WHERE qual.id = ? AND breakdown.targetgrade = ?";
		return $DB->get_record_sql($sql, array($this->id, $award));
	}
    
    /**
     * 
     * @global type $CFG
     * @global type $DB
     * @return boolean
     */
    protected function get_default_credits()
    {
        global $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECSubType.class.php');
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_qual_att} 
            WHERE bcgttargetqualid = ? AND name = ?";
        $params = array($this->bcgtTargetQualID, BTECSubType::DEFAULTNUMBEROFCREDITSNAME);
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return $record->value;
        }
        return false;
    }
    
    /**
     * 
     * @global type $DB
     * @param type $type
     * @return type
     */
    protected function get_default_award($type)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? ORDER BY ";
        if($type == 'Min')
        {
            $sql .= "unitsscorelower ASC";
        }
        elseif($type == 'Max')
        {
            $sql .= "unitsscoreupper DESC";
        }
        return $DB->get_records_sql($sql, array($this->bcgtTargetQualID),0, 1);
    }
    
    
    public function print_grid(){
        
        global $CFG, $COURSE;
        
        echo "<!doctype html><html><head>";
        echo "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/print.css'>";
        
        $logo = get_config('bcgt', 'logoimgurl');
        
        echo "</head><body style='background: url(\"{$logo}\") no-repeat;'>";
                
        echo "<div class='c'>";
            echo "<h1>{$this->get_display_name()}</h1>";
            echo "<h2>".fullname($this->student)." ({$this->student->username})</h2>";

            echo "<br>";
            
            // Key
            echo "<div id='key'>";
                echo BTECQualification::get_grid_key();
            echo "</div>";
            
            echo "<br><br>";
            
            echo "<table id='printGridTable'>";
            
            // Header
            
            //we will reuse the header at the bottom of the table.
            $totalCredits = $this->get_students_total_credits(true);
            //for all of the units on this qual, lets check which crieria names
            //have actually been used. i.e. dont show P17 if no unit has a p17
            $criteriaNames = $this->get_used_criteria_names();

            // Can't sort by ordernum here because could be different between units, can only do this on unit grid
            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
            $criteriaSorter = new CriteriaSorter();
            usort($criteriaNames, array($criteriaSorter, "ComparisonDelegateByArrayNameLetters"));

            $subCriteriaArray = $this->get_used_sub_criteria_names($criteriaNames);
            
            $headerObj = $this->get_grid_header($totalCredits, true, $criteriaNames, 'student', $subCriteriaArray );
            $criteriaCountArray = $headerObj->criteriaCountArray;
            $this->criteriaCount = $criteriaCountArray;
            
            echo $headerObj->header;
            
            $subCriteriaArray = null;
            if ($this->has_sub_criteria()){
                $subCriteriaArray = $this->get_used_sub_criteria_names($criteriaNames);
            }
            
            // Units & Grades
            $units = $this->units;
            $unitSorter = new UnitSorter();
            usort($units, array($unitSorter, "ComparisonDelegateByType"));
            
            $rowCount = 0;
            
            foreach($units AS $unit)
            {

                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELALL;
                $loadParams->loadAward = true;
                $unit->load_student_information($this->student->id, $this->id, $loadParams);
                
                if($unit->is_student_doing())
                {	

                    echo "<tr>";
                    
                    //get the users award from the unit
                    $unitAward = $unit->get_user_award();   
                    $award = '';
                    if($unitAward)
                    {
                        $rank = $unitAward->get_rank();
                        $award = $unitAward->get_award();
                    }	

                    $extraClass = '';
                    if($rowCount == 1)
                    {
                        $extraClass = 'firstRow';
                    }
                    elseif($rowCount == count($units))
                    {
                        $extraClass = 'lastRow';
                    }

                    // Unit Comment
                    //$getComments = $unit->get_comments();

//                    if(!empty($getComments)){
//                        $retval .= "<img src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/comment-icon.png' class='showCommentsUnit' />";
//                        $retval .= "<div class='tooltipContent'>".nl2br( htmlspecialchars($getComments, ENT_QUOTES) )."</div>";
//                    }
                    
                    echo "<td></td><td></td>";
                                        
                    echo "<td>{$unit->get_name()}<br><small>(".$unit->get_credits()." Credits)</small></td>";

                    
                    echo "<td>{$award}</td>";

                    $criteriaCount = 0;
                    $previousLetter = '';
                    
                    if($criteriaNames)
                    {
                        //if we have found the used criteria names. 
                        foreach($criteriaNames AS $criteriaName)
                        {	
                            
                            
                            $letter = substr($criteriaName, 0, 1);
                            if($previousLetter != '' && $previousLetter != $letter)
                            {
                                echo "<td class='divider'></td>";
                            }
                            $previousLetter = $letter;
                            
                            $criteriaCount++;
                            
                            $studentCriteria = $unit->get_single_criteria(-1, $criteriaName);
                            if($studentCriteria)
                            {	
                                echo "<td>". $this->set_up_criteria_grid($studentCriteria, '', 
                                        null, false, false, false, $unit, 0, $this->student) . "</td>";
                            }
                            else 
                            {         
                                echo "<td class='grid_cell_blank'></td>";
                            }
                        
                            
                            if($this->has_sub_criteria())
                            {
                                //Get the used Sub Criteria Names from the heading for this criteriaName
                                //for example get the p1.1, P1.2 ect for the P1
                                if(array_key_exists($criteriaName, $subCriteriaArray))
                                {
                                    $criteriaSubCriteriasUsed = $subCriteriaArray[$criteriaName];
                                    //Lets see if this Criteria has the subcriteria that matches the heading
                                    $cellCount = count($criteriaSubCriteriasUsed);
                                    $i = 0;
                                    foreach($criteriaSubCriteriasUsed AS $subCriteriaUsed)
                                    {
                                        $firstLast = 0;
                                        $i++;
                                        $extraClass = '';
                                        if($i == 1)
                                        {
                                            $extraClass = 'startSubCrit';
                                            if(count($criteriaSubCriteriasUsed) == 1)
                                            {
                                                $extraClass .= " endSubCrit";
                                            }
                                            $firstLast = 1;
                                        }
                                        elseif($i == $cellCount)
                                        {
                                            $extraClass = 'endSubCrit';
                                            $firstLast = -1;
                                        }
                                        $criteriaCount++;
                                        $actualSubCriteria = false;
                                        if ($studentCriteria){
                                            $actualSubCriteria = $studentCriteria->get_single_criteria(-1, $subCriteriaUsed);
                                        }
                                        if($actualSubCriteria)
                                        {
                                            //then create the grid
                                            echo "<td>".$this->set_up_criteria_grid($actualSubCriteria, 
                                                    $extraClass.' subCriteria subCriteria_'.$criteriaName, 
                                                    null, false, false, 
                                                    true, $unit, $firstLast, $this->student)."</td>";
                                        }
                                        else
                                        {
                                            echo "<td class='grid_cell_blank'></td>";
                                        }//end else not actualSubCriteria
                                    }//end loop sub Criteria
                                }
                            
                            }
                            
                            //if its the student view then lets print
                            //out the students unformation
                            
                        }
                            
                    }

                    echo "</tr>";
                    
                }

            }

            
            
            
            echo "</table>";
            echo "</div>";
            
            $context = context_course::instance($COURSE->id);
            
            //>>BEDCOLL TODO this need to be taken from the qual object
            //as foundatonQual is different
            echo '<table id="summaryAwardGrades">';
            //if we are looking at the student then show the qual award
            if(has_capability('block/bcgt:viewbtectargetgrade', $context))
            {
                echo $this->show_target_grade();
            }
            echo $this->show_predicted_qual_award($this->studentAward, $context, $totalCredits);
            echo '</table>';

            
            //echo "<br class='page_break'>";
            
            // Comments and stuff
            // TODO at some point
            
        
        echo "</body></html>";
        
    }
    
    
    public function print_report(){
        
        global $CFG, $COURSE;
        
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/sorters/BTECCriteriaSorter.class.php');
        $criteriaSorter = new BTECCriteriaSorter();
        
        
        echo "<!doctype html><html><head>";
        echo "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/print.css'>";
        $logo = get_config('bcgt', 'logoimgurl');
        
        echo "</head><body style='background: url(\"{$logo}\") no-repeat;'>";
        
        echo "<h1 class='c'>{$this->get_display_name()}</h1>";
        $userName = '';
        $fullName = '';
        if(isset($this->student))
        {
            $userName = $this->student->username;
            $fullName = fullname($this->student);
        }
        echo "<h2 class='c'>".$fullName."</h2>";
        echo "<h3 class='c'>(".$userName.")</h3>";

        echo "<br><br><br>";
        
//        echo "<div class='report_left'>";
//        
//            echo "<table>";
//                echo "<tr><td class='b'>".get_string('date').":</td><td>".date('D jS M Y')."</td></tr>";
//                echo "<tr><td class='b'>".get_string('student', 'block_bcgt').":</td><td>".fullname($this->student)."</td></tr>";
//                echo "<tr><td class='b' colspan='2'>".get_string('qualificationcomments', 'block_bcgt').":<br><em>".format_text($this->comments, FORMAT_PLAIN)."</em></td></tr>";
//                echo "<tr><td class='b' colspan='2'>".get_string('report').":<br><em><textarea style='height:300px;width:100%;'>Please add any additional comments to the report here</textarea></em></td></tr>";
//                echo "<tr><td class='b' colspan='2'>Recommended Next Course:<br><br></td></tr>";
//                echo "<tr><td class='b' colspan='2'>Signatures:<br><br></td></tr>";
//                echo "<tr><td class='b' colspan='2'>Course Co-ordinator:<br></td></tr>";
//            echo "</table>";
//        
//        echo "</div>";
                
        
        echo "<div class='report_right'>";
            echo "<table>";
                echo "<tr><th>Criteria</th><th>Award</th><th>Date</th><th>Comments</th></tr>";
                                
                if ($this->units)
                {
                    
                    $units = $this->units;
                    $unitSorter = new UnitSorter();
                    usort($units, array($unitSorter, "ComparisonDelegateByType"));
                    
                    foreach($units as $unit)
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
                                echo "<td>".format_text($unit->get_comments(), FORMAT_PLAIN)."</td>";
                            
                            echo "</tr>";
                            
                            
                            // Criteria
                            if ($unit->get_criteria())
                            {
                                
                                $criteriaList = $unit->get_criteria();
                                
                                usort($criteriaList, array($criteriaSorter, "ComparisonDelegateByNameObject"));
                                
                                foreach($criteriaList as $criteria)
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
            $totalCredits = $this->get_students_total_credits(true);
            
            //>>BEDCOLL TODO this need to be taken from the qual object
            //as foundatonQual is different
            //if we are looking at the student then show the qual award
            if(has_capability('block/bcgt:viewbtectargetgrade', $context))
            {
                echo $this->show_target_grade();
            }
            echo $this->show_predicted_qual_award($this->studentAward, $context, $totalCredits);

            echo "</table>";
        
        echo "</div>";
        
        
        echo "</body></html>";
        
    }
    
    
    
    
    
//    protected function load_target_grades($orderBY = '')
//    {
//        global $DB;
//		$sql = "SELECT * FROM {block_bcgt_target_breakdown} 
//		WHERE bcgttargetqualid = ? ORDER BY ";
//        if($orderBY == '')
//        {
//            $sql .= 'ranking ASC';
//        }
//        else
//        {
//            $sql .= $orderBY;
//        }
//        $params = array($this->bcgtTargetQualID);
//		$targetGrades = $DB->get_records_sql($sql, $params);
//        foreach($targetGrades AS $targetGrade)
//        {
//            $targetGrade->grade = $targetGrade->targetgrade;
//        }
//        $this->targetGrades = $targetGrades;
//    }
    
    
    public function has_printable_report(){
        return true;
    }
    
    public function has_auto_target_grade_calculation(){
        return true;
    }
    
}

?>
