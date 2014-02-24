<?php

/**
 * Description of CGQualification
 *
 * @author mchaney
 */
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Qualification.class.php');

//This can be abstract if the qualification class is never returned from the get_correct_qual_class
//but doesnt always need to be used in an actual qualification target qual
class CGQualification extends Qualification{

    //these are hardcoded from the install. The tables dont have auto incremental
    //id's
    
    //the database id
    const ID = 9;
    const FAMILYID = 4;
    const NAME = 'CG';

    protected $credits;
    
	//any properties
    public function CGQualification($qualID, $params, $loadParams)
    {
        
        global $DB;
        
        parent::Qualification($qualID, $params, $loadParams);
        
        // Qual stuff
		$record = $DB->get_record("block_bcgt_qualification", array("id" => $qualID), "credits, noyears, pathwaytypeid");
        if ($record)
        {
            $this->credits = $record->credits;
            $this->noYears = $record->noyears;
            $this->pathwayTypeID = $record->pathwaytypeid;
        }
        
    }
    
    public static function get_instance($qualID, $params, $loadParams)
    {
        return new CGQualification($qualID, $params, $loadParams);
    }
    
    /**
	 * Returns the human type name
	 */
	public function get_type()
    {
        return CGQualification::NAME;
    }
	
	/**
	 * Returns the id of the type not the qual
	 */
	public function get_class_ID()
    {
        return CGQualification::ID;
    }
    
    
    /**
	 * Returns the id of the type not the qual
	 */
	public function get_family_ID()
    {
        return CGQualification::FAMILYID;
    }
    
    
    /**
     * Returns the family name
     */
    public function get_family()
    {
        return CGQualification::NAME;
    }
    
    public function has_units()
    {
        return true;
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
    
    function has_sub_criteria()
    {
        return false;
    }
    
    public function get_pathway_type_id(){
        return $this->pathwayTypeID;
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
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGSubType.class.php');
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_qual_att} 
            WHERE bcgttargetqualid = ? AND name = ?";
        $params = array($this->bcgtTargetQualID, CGSubType::DEFAULTNUMBEROFCREDITSNAME);
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return $record->value;
        }
        return false;
    }
    
    public function has_percentage_completions()
    {
        return true;
    }
    
    protected function get_simple_qual_report_tabs()
    {
        $tabs = parent::get_simple_qual_report_tabs();
        return $tabs + array("u"=>"units", "co"=>"classoverview");
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
        
        $subtype = optional_param('subtype', $this->subType, PARAM_TEXT);
        $level = optional_param('level', $this->level, PARAM_INT);
        $name = optional_param('name', $this->name, PARAM_TEXT);
        $name = trim($name);
                
        $this->processed_errors = '';
        
        // usubtype must be set
        if (is_null($subtype) || $subtype == '' ){
            $this->processed_errors .= get_string('error:subtype', 'block_bcgt') . '<br>';
        }
        
        
        // Name
        if (is_null($name) || empty($name)){
            $this->processed_errors .= get_string('error:name', 'block_bcgt') . '<br>';
        }

        
        if (!empty($this->processed_errors)){
            return false;
        }
        
        $pathway = optional_param('pathway', null, PARAM_INT);
        $type = optional_param('type', null, PARAM_INT);
        
        $this->pathwayTypeID = get_pathway_dep_type_from_both($pathway, $type);
        
        return true;
        
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
     * Any additional loads that are required when doing student load information
     */
    public function qual_specific_student_load_information($studentID, $qualID)
    {
        
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
		$targetQualID = parent::get_target_qual(CGQualification::ID);
		$dataobj->bcgttargetqualid = $targetQualID;
        $dataobj->pathwaytypeid = $this->pathwayTypeID;
        
        
		$id = $DB->insert_record("block_bcgt_qualification", $dataobj);
		$this->id = $id;
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_INSERTED_QUAL, null, $this->id, null, null, null);
	}
    
    public function get_extra_data_for_copy($oldQualification){
        
        // Pathway type id
        $this->pathwayTypeID = $oldQualification->get_pathway_type_id();
        
    }
	
	/***
	 * Deletes the qual
	 * For each type there maybe specific things we need to do
	 */
	public function delete_qualification()
    {
        $this->delete_qual_main();
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
     
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_UPDATED_QUAL, null, $this->id, null, null, null);
        
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
            

            echo "</table>";
        
        echo "</div>";
        
        
        echo "</body></html>";
        
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
                echo CGQualification::get_grid_key();
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
            require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
            $criteriaSorter = new CGCriteriaSorter();
            usort($criteriaNames, array($criteriaSorter, "ComparisonSimple"));


            $headerObj = $this->get_grid_header($totalCredits, true, $criteriaNames, 'student', false, true);
            $criteriaCountArray = $headerObj->criteriaCountArray;
            $this->criteriaCount = $criteriaCountArray;
            
            echo $headerObj->header;
            
            
            
            // Units & Grades
            $units = $this->units;
            $unitSorter = new UnitSorter();
            usort($units, array($unitSorter, "ComparisonDelegateByType"));
            $possibleValues = null;

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
                    
                    if($criteriaNames)
                    {
                        //if we have found the used criteria names. 
                        foreach($criteriaNames AS $criteriaName)
                        {	
                                $criteriaCount++;
                                //if its the student view then lets print
                                //out the students unformation
                                $studentCriteria = $unit->get_single_criteria(-1, $criteriaName);
                                if($studentCriteria)
                                {	
                                    echo "<td>".$studentCriteria->get_grid_td(false, false, $unit, $this->student, $this, 'student')."</td>";
                                }
                                else 
                                {         
                                    echo "<td class='grid_cell_blank'></td>";
                                }
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
            echo $this->show_predicted_qual_award($this->predictedAward, $context);
            echo '</table>';
            
            
            //echo "<br class='page_break'>";
            
            // Comments and stuff
            // TODO at some point
            
        
        echo "</body></html>";
        
    }
    
    public function call_display_student_grid_external()
    {
        return $this->display_student_grid(false, true, true);
    }
    
    protected function get_used_criteria_names($criteria = false, &$array = false, $subCriteria = true)
    {
        
        if ($criteria && $array)
        {
            
            foreach($criteria as $crit)
            {
                $array[] = $crit->get_name();
                if ($subCriteria && $crit->get_sub_criteria())
                {
                    $this->get_used_criteria_names($crit->get_sub_criteria(), $array, $subCriteria);
                }
            }
            
            return;
            
        }
        
        
        
        $return = array();
        
        if ($this->units)
        {
        
            foreach($this->units as $unit)
            {
            
                if ($unit->get_criteria())
                {
                    foreach($unit->get_criteria() as $crit)
                    {
                        $return[] = $crit->get_name();
                        if ($subCriteria && $crit->get_sub_criteria())
                        {
                            $this->get_used_criteria_names($crit->get_sub_criteria(), $return);
                        }
                    }
                }
        
            }
        
        }
        
        $this->usedCriteriaNames = array_unique($return);
        return $this->usedCriteriaNames;
        
    }
    
    
    /**
     * Displays the student Grid
     * basicView is for when viewing through the ELBP
     */
    public function display_student_grid($fullGridView = true, $studentView = true, $basicView = false)
    {
        
        global $COURSE, $PAGE, $CFG, $OUTPUT;
        $grid = optional_param('g', 's', PARAM_TEXT);
        $late = optional_param('late', false, PARAM_BOOL);
        $courseID = optional_param('cID', -1, PARAM_INT);
        $context = context_course::instance($COURSE->id);
        
        $retval = '<div>';
        
        if (!$basicView)
        {
        
            $retval .= "<input type='submit' id='viewsimple' class='gridbuttonswitch viewsimple' name='viewsimple' value='View Simple'/>";
            $retval .= "<input type='submit' id='viewadvanced' class='gridbuttonswitch viewadvanced' name='viewadvanced' value='View Advanced'/>";
            $retval .= "<br>";  
            
            if($courseID != -1)
            {
                $context = context_course::instance($courseID);
            }
            if(has_capability('block/bcgt:editstudentgrid', $context))
            {	
                $retval .= "<input type='submit' id='editsimple' class='gridbuttonswitch editsimple' name='editsimple' value='Edit Simple'/>";
                $retval .= "<input type='submit' id='editadvanced' class='gridbuttonswitch editadvanced' name='editadvanced' value='Edit Advanced'/>"; 
            }
        
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
        
        $jsModule = array(
            'name'     => 'mod_bcgtcg',
            'fullpath' => '/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        //
                
        if ($basicView){
            $retval .= <<< JS
            <script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js'></script>
JS;
        } else {
            $PAGE->requires->js_init_call('M.mod_bcgtcg.initstudentgrid', array($this->id, $this->studentID, $grid), true, $jsModule);
        }
        
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        $retval .= load_javascript(true, $basicView);
        
        $retval .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/css/start/jquery-ui-1.10.3.custom.min.css' />";
        $retval .= "
		<div class='gridKey adminRight'>";
		if($studentView)
		{
			$retval .= "<h2>Key</h2>";
			//Are we looking at a student or just the actual criteria for the grid.
			//if students then get the key that tells everyone what things stand for
			$retval .= CGQualification::get_grid_key();
		}
		$retval .= "</div>";
        
        //the grid -> ajax
        $retval .= '<div id="cgStudentGrid">';
        
        if($studentView && !$editing)
		{
            //>>BEDCOLL TODO this need to be taken from the qual object
            //as foundatonQual is different
            $retval .= '<table id="summaryAwardGrades">';
			$retval .= $this->show_predicted_qual_award($this->predictedAward, $context);
            $retval .= '</table>';
            
        }
        
        $retval .= "<div id='studentGridDiv' class='studentGridDiv ".
        $grid."StudentGrid tableDiv'><table align='center' class='student_grid".
                $grid."FixedTables' id='CGStudentGrid'>";
        
		//we will reuse the header at the bottom of the table.
		$totalCredits = $this->get_students_total_credits($studentView);
		//for all of the units on this qual, lets check which crieria names
		//have actually been used. i.e. dont show P17 if no unit has a p17
        $false = false; # Can't pass just "false" by reference
		$criteriaNames = $this->get_used_criteria_names($false, $false, true);
        
        // Can't sort by ordernum here because could be different between units, can only do this on unit grid
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
        $criteriaSorter = new CGCriteriaSorter();
		usort($criteriaNames, array($criteriaSorter, "ComparisonSimple"));
        
                
		$headerObj = $this->get_grid_header($totalCredits, $studentView, $criteriaNames, $grid, false);
		$criteriaCountArray = $headerObj->criteriaCountArray;
        $this->criteriaCount = $criteriaCountArray;
		$header = $headerObj->header;	
        
        
		$retval .= $header;
		$retval .= "<tbody>";
        //the body is loaded through an ajax call. This ajax call
        //is called in the js file of bcgtbtec.js and is in the initstdentgrid
        //it calls ajax and calls ajax/get_student_grid.php
        $retval .= "</tbody>";
        $retval .= "</table>";
        
        // Qual Comment
        if ($this->comments == '') $this->comments = 'N/A';
        $retval .= "<div id='qualComment'><br><fieldset><legend><h2>Qualification Comments</h2></legend><br>".nl2br( htmlentities($this->comments, ENT_QUOTES) )."</fieldset></div>";
        
        if($studentView && !$editing)
		{
            //>>BEDCOLL TODO this need to be taken from the qual object
            //as foundatonQual is different
            $retval .= '<table id="summaryAwardGrades">';
			$retval .= $this->show_predicted_qual_award($this->predictedAward, $context);
            $retval .= '</table>';
            
        }
        $retval .= "</div>";
        $retval .= '</div>';
        $retval .= '</div>';
        
        if ($basicView){
            $retval .= " <script>$(document).ready( function(){
                M.mod_bcgtcg.initstudentgrid(Y, {$this->id}, {$this->studentID}, '{$grid}');
            } ); </script> ";
        }
        
        return $retval;
        
    }
    
    
    
    public function get_student_grid_data($advancedMode, $editing, 
            $studentView)
    {
        
         global $DB, $OUTPUT;
        $subCriteria = $this->has_sub_criteria();
        //$this->load_student();
        $user = $DB->get_record_sql('SELECT * FROM {user} WHERE id = ?', array($this->studentID));
        $subCriteriaArray = false;
        $false = false;
        if (!isset($this->usedCriteriaNames)){
            $criteriaNames = $this->get_used_criteria_names($false, $false, true);
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
        
        // Can't sort by ordernum here because could be different between units, can only do this on unit grid
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
        $criteriaSorter = new CGCriteriaSorter();
		usort($criteriaNames, array($criteriaSorter, "ComparisonSimple"));
        
		$units = $this->units;
        $unitSorter = new UnitSorter();
		usort($units, array($unitSorter, "ComparisonDelegateByType"));        
        $possibleValues = null;
        if($advancedMode && $editing)
        {
           $possibleValues = $this->get_possible_values(CGQualification::ID, true); 
        }
		if($editing)
        {
            $unitAwards = Unit::get_possible_unit_awards($this->get_class_ID());
        }
        
        $rowCount = 0;
                
        foreach($units AS $unit)
        {
            
            if(($studentView && $unit->is_student_doing()) || !$studentView)
			{	
            
                $rowArray = array();
                
                $rowClass = 'rO';
				if($rowCount % 2)
				{
					$rowClass = 'rE';
				}				
				$award = 'N/S';
				$rank = 'nr';
				if($studentView)
				{
					//get the users award from the unit
					$unitAward = $unit->get_user_award();   
					if($unitAward)
					{
						$rank = $unitAward->get_rank();
						$award = $unitAward->get_award();
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
                        $retval .= "<img id='{$cellID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' qualid='{$this->id}' unitid='{$unit->get_id()}' studentid='{$this->studentID}' grid='stud' type='button' class='editCommentsUnit' title='Click to Edit Unit Comments' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/comments.jpg' />";
                        $retval .= "<div class='tooltipContent'>".nl2br( htmlspecialchars($getComments, ENT_QUOTES) )."</div>";
                    }
                    else
                    {                        
                        $retval .= "<img id='{$cellID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' qualid='{$this->id}' unitid='{$unit->get_id()}' studentid='{$this->studentID}' grid='stud' type='button' class='addCommentsUnit' title='Click to Add Unit Comment' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/plus.png' />";
                    }

                }
                else
                {
                    if(!empty($getComments)){
                        $retval .= "<img src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/comment-icon.png' class='showCommentsUnit' />";
                        $retval .= "<div class='tooltipContent'>".nl2br( htmlspecialchars($getComments, ENT_QUOTES) )."</div>";
                    }
                    
                }
                
                $rowArray[] = $retval;
                
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
				$retval = "<span id='uID_".$unit->get_id()."' class='uNToolTip unitName".$unit->get_id()."' unitID='{$unit->get_id()}' studentID='{$this->studentID}' title='title'>".$link."</span>";
				
                $retval .= "<span style='color:grey;font-size:85%;'><br />(".$unit->get_credits()." Credits)</span>";	
				
                //if has capibility
				if(has_capability('block/bcgt:editunit', $context))
				{		
                    $retval .= "<a class='editing_update editUnit' href='{$CFG->wwwroot}/blocks/bcgt/forms/edit_unit.php?unitID=".$unit->get_id()."' title = 'Update Unit'>
					<img class='iconsmall editUnit' alt='Update Unit' src='".$OUTPUT->pix_url("t/edit", "core")."'/></a>";
				}
                
                $retval .= "<div id='unitTooltipContent_{$unit->get_id()}_{$this->studentID}' style='display:none;'>".$unit->build_unit_details_table()."</div>";
                
				//$retval .= "</td>";
                $rowArray[] = $retval;
                
                if($studentView)
				{
					if($editing)
					{
						$rowArray[] = $this->edit_unit_award($unit, $rank, $award, $unitAwards);
                        
                    }
					else
					{
						//print out the unit award column
						//$retval .= "<td id='unitAward_".$unit->get_id()."' class='unitAward r".$unit->get_id()." rank$rank'>".$award."</td>";
                        $rowArray[] = '<span id="unitAwardAdv_'.$unit->get_id().'_'.$this->studentID.'">'.$award.'</span>';
                    }
                    
                    // Points
//                    $unitPoints = $unit->get_student_unit_points();
//                    if (!$unitPoints) $unitPoints = "-";
//                    $rowArray[] = "<span id='unitPoints_{$unit->get_id()}'>{$unitPoints}</span>";
                    
                    // Percent
                    if($this->has_percentage_completions()){
                        $rowArray[] = "<div class='tdPercentCompleted'>".$unit->display_percentage_completed()."</div>";
                    }
                    
				}
                
                
                
                
                if($criteriaNames)
				{
					//if we have found the used criteria names. 
					$criteriaCount = 0;
					foreach($criteriaNames AS $criteriaName)
					{	
						//TODO
						$criteriaCount++;
						if($studentView)
						{
							//if its the student view then lets print
							//out the students unformation
                            $studentCriteria = $unit->get_single_criteria(-1, $criteriaName);
							if($studentCriteria)
							{	
								$rowArray[] = $studentCriteria->get_grid_td($editing, $advancedMode, $unit, $user, $this, 'student');
							}//end if student criteria
							else //not student criteria (i.e. the criteria doesnt exist on that unit)
							{         
                                //retval needs to be an array of the columns
								$rowArray[] = "";
                                #$rowArray[] = $retval;
							}//end else not sudent criteria	
                            
                            
						}
						else//its not the student view
						{//This means we are just showing the qual as a whole. 
							//then lets just test if he unit has that criteria
							//and mark it as present or not
							$rowArray[] = "!sV"; # wtf?
//							$retval .= $this->get_non_student_view_grid($criteriaCount, $criteriaCountArray, $criteriaName, $unit, $subCriteriaArray);
//                            $rowArray[] = $retval;
                            
                        }
						
					}//end for each criteria
				}//end if criteria names
                
                
                // test
                $rowArray[] = $unit->get_name();
                $rowsArray[] = $rowArray;
            
            }
            
        }
                
        return $rowsArray;
        
    }
    
    
    /**
     * @param type $unit
     * @param type $rank
     * @param type $award
     * @param type $unitAwards
     * @return string
     */
    public function edit_unit_award($unit, $rank, $award, $unitAwards = null)
	{
		$retval = "";
        $retval .= "<select class='unitAward' id='unitAwardEdit_".$unit->get_id()."_{$this->studentID}' name='unitAwardAPL_".$unit->get_id()."' unitid='{$unit->get_id()}' qualid='{$this->id}' studentid='{$this->studentID}'>";        
		$retval .= "<option value='-1'>N/A</option>";
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
                
                if ($unit->get_grading() == 'P')
                {
                    break;
                }
                
			}
		}
		$retval .= "</select>";
		return $retval;
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
        //does the ALEVEL need to do anything else?
        return parent::remove_units_qual($unit);
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
        $heading = $this->get_type().''. 
        ' '.$this->get_level()->get_level().''. 
        ' '.$this->get_subType()->get_subType();
        $heading .= ' '.$this->get_name().'<br />';
        $heading .= ' ('.get_string('bteccredits','block_bcgt').': '.$this->get_credits().')';
        if(!$multiple)
        {
            
            $jsModule = array(
                'name'     => 'mod_bcgtcg',
                'fullpath' => '/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js',
                'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
            );
            $PAGE->requires->js_init_call('M.mod_bcgtcg.initstudunits', null, true, $jsModule);
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
            $out .= '<table id="cgStudentUnits'.$count.'" class="cgStudentsUnitsTable" align="center"><thead><tr><th></th><th></th><th>Username</th><th>Name</th>';
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
	 * What is the final grade if it has been set
	 */
	public function retrieve_student_award()
    {
        $award = $this->get_students_qual_award();
		if($award)
		{
            $retval = new stdClass();
            $params = new stdClass();
            $params->award = $award->targetgrade;
            $params->type = $award->type;
            $params->ucasPoints = $award->ucaspoints;
            $params->unitsScoreUpper = $award->unitsscoreupper;
            $params->unitsScoreLower = $award->unitsscorelower;
            $qualAward = new QualificationAward($award->breakdownid, $params);
            $retval->{$award->type} = $qualAward;
            return $retval;
		}
		return false;
    }
    
    /**
	 * What is the final grade
	 */
	public function calculate_predicted_grade()
    {
        return false;
    }
    
    /**
	 * Calculate the predicted grade
	 */
	public function calculate_final_grade()
    {
        
        global $DB;
        
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
                        $ranking = $DB->get_record("block_bcgt_type_award", array("id" => $unitAward->get_id()), "id, ranking");
                        $awardArray[] = array("value" => $unitAward->get_award(), "weighting" => $unit->get_weighting(), "ranking" => $ranking->ranking);
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
                $params->type = 'Predicted';
                $qualAward = new QualificationAward($award->id, $params);
                $this->update_qualification_award($qualAward);
                return $qualAward;
            }                
            
            
        }
        else
        {
            $this->delete_qualification_award('Predicted');
            return false;
        }
        
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
        if($this->get_students_qual_award())
        {
            $award = $this->get_students_qual_award();
            $id = $award->id;
            $obj->id = $id;
            return $DB->update_record('block_bcgt_user_award', $obj);
        }
        else
        {
            return $DB->insert_record('block_bcgt_user_award', $obj);
        }
        
    }
    
    /**
     * Gets the students qualification award from the database
     * @return Found
     */
    function get_students_qual_award()
    {
        global $DB;
                
        $sql = "SELECT useraward.id as id, useraward.type, breakdown.id AS breakdownid, breakdown.bcgttargetqualid, breakdown.targetgrade, breakdown.ucaspoints, breakdown.unitsscorelower, breakdown.unitsscoreupper 
        FROM {block_bcgt_user_award} AS useraward 
        INNER JOIN {block_bcgt_target_breakdown} AS breakdown ON breakdown.id = useraward.bcgtbreakdownid
        WHERE useraward.bcgtqualificationid = ?
        AND useraward.userid = ?";
        return $DB->get_record_sql($sql, array($this->id, $this->studentID));
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

        global $CFG, $DB;

        // Get the point boundaries and awards that are possible for this qual
        $awards = $DB->get_records("block_bcgt_target_breakdown", array("bcgttargetqualid" => $this->get_target_qual(CGQualification::ID)), "unitsscorelower ASC");
        $records = array();

        if ($awards)
        {
            foreach($awards as $award)
            {
                $obj = new stdClass();
                $obj->id = $award->id;
                $obj->grade = $award->targetgrade;
                $obj->pointslower = $award->unitsscorelower;
                $obj->pointsupper = $award->unitsscoreupper;
                $records[$award->targetgrade] = $obj;
            }
        }

        $totalUnits = 0;
        $totalScore = 0;

        foreach($awardArray as $award)
        {
            // Get the points ranking of this award and add to total
            // Also take into account different criteria weightings
            $weight = $award['weighting'];
            $rank = $award['ranking'];
            $totalUnits += $weight;
            $totalScore += ( ($rank * $weight) );
        }
        
        $avgScore = round($totalScore / $totalUnits, 1);
                
        // Now work out where in the points boundaries it lies
        foreach($records as $record)
        {
            if($avgScore >= $record->pointslower && $avgScore <= $record->pointsupper)
            {
                return $record;
            }
        }
        
        return false; // Something went quite wrong


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
    
    /**
	 * This will build up the key for the Grid used in student view
	 * and single view. 
	 * SHOULD be a static function to the UNIT view can get to it
	 * At the moment we have duplicate calls. 
	 */
	public static function get_grid_key($string = true)
	{
        global $CFG; 
        $file = $CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg';
        if($string)
        {
            $retval = '';
        }
        else
        {
            $retval = array();
        }
        
        $possibleValues = CGQualification::get_possible_values(CGQualification::ID, true);
        
        $isAchieved = true;
        
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
                
                $currentIsAchieved = $isAchieved;
                
                if ($possibleValue->specialval == 'A') $isAchieved = true;
                else $isAchieved = false;
                                
                // If we have just gone from achieved to others, line break
                if ($currentIsAchieved && !$isAchieved && $string){
                    $retval .= "<br>";
                }
                
                
                if($string)
                {
                    $retval .= $value . '&nbsp;&nbsp;&nbsp;';
                }
                else
                {
                    $retval[] = $value;
                }
            }
        }      
        
        if ($string){
            
            $retval .= '<br>';
            $retval .= '<span class="keyValue"><img class="keyImage" src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/'.
                'grid_symbols/core/icon_HasComments.png"/> = Comments (Hover to view)'.
                '</span>&nbsp;&nbsp;&nbsp;';
            
            $retval .= '<span class="keyValue"><img class="keyImage" src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/'.
                'grid_symbols/core/icon_WasLate.png"/> = Was originally Late'.
                '</span>';
            
            
        } else {
            
            $retval[] = '<span class="keyValue"><img class="keyImage" src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/'.
                'grid_symbols/core/icon_HasComments.png"/> = Comments (Hover to view)'.
                '</span>';
            
            $retval[] = '<span class="keyValue"><img class="keyImage" src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/'.
                'grid_symbols/core/icon_WasLate.png"/> = Was originally Late'.
                '</span>';
            
        }
        
        

        return $retval;
        
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
        $params = array($typeID);
        if($enabled)
        {
            $sql .= " AND value.enabled = ?";
            $params[] = 1;
        }

        $sql .= " ORDER BY
                ( CASE
                    WHEN value.specialval = 'A' THEN 0
                    WHEN value.specialval = 'X' THEN 1
                    ELSE value.value
                   END ) ASC ,
                value.ranking ASC";
        
        return $DB->get_records_sql($sql, $params);
		
	}
    
    /**
     * This is for unit grid
     * @return type
     */
    public function get_qual_award()
    {
        $type = get_string('predicted','block_bcgt');
        $award = "N/S";
        if ($this->studentAward){
            $award = $this->studentAward->get_award();
        } 
        
        return "<span id='qualAwardType_{$this->studentID}'>$type</span><br><span id='qualAward_{$this->studentID}'>".$award."</span>";	
        
    }
    
    protected function show_predicted_qual_award($studentAward, $context)
	{
        //TODO CHANGE THIS TO USE THE STUDENT AWARD
		$retval = "";
        $retval .= "<tr><td>";
        $type = get_string('predictedavgaward','block_bcgt');
        $award = 'N/A';
        if($studentAward)
        {
            $type = get_string('predictedfinalaward','block_bcgt');
            $award = $studentAward->get_award();
            $retval .= "<span class='qualAwardType'>$type</span></td><td><span class='qualAward'>".$award."</span></td>";	
        }   
        else
        {
            $retval .= "<span class='qualAwardType'>$type</span></td><td>".
                    "<span class='qualAward'>$award</span></td>";
        }
        $retval .= "</tr>";
        
        
        return $retval;
	}
    
    
    /**
     * displays the unit grid. 
     */
    public function display_subject_grid()
    {
        //display the subject grid
    }
    
     /**
	 * Gets the criteria names that are used at least once in the units of the qualification. 
	 */
//	function get_used_criteria_names()
//	{
//		//checks all units and see's if the criteria name is used. 
//		$usedCriteriaNames = array();
//        foreach($this->units AS $unit)
//        {
//            $unitCriteriaNames = $unit->get_criteria_names();
//            $usedCriteriaNames = array_merge($unitCriteriaNames, $usedCriteriaNames);
//        }
//        $this->usedCriteriaNames = $usedCriteriaNames;
//		return $usedCriteriaNames;
//        
//	}
    
    
    protected function get_grid_header($totalCredits, $studentView, $criteriaNames, $grid, $subCriteriaArray = false, $printGrid = false)
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
		$headerObj = new stdClass();
		$header = '';
		//extra one for projects
		$header .= "<thead><tr><th></th>";
                if($advancedMode && $editing)
                {
                    $header .= "<th class='unitComment'></th>";
                }
                elseif(!($editing && $advancedMode))
                {
                    $header .= "<th></th>";
                }
                                
                $header .= "<th>Unit (Total Credits: $totalCredits)</th>";
                $totalCellCount = 3;
		if($studentView)
		{//if its not student view then we are looking at just
			//the qual in general rather than a student.
			$header .= "<th>Award</th>";
            $totalCellCount++;
            
//            $header .= "<th class='points'>Points</th>";
//            $totalCellCount++;
            
            // If qual has % completions enabled
            if($this->has_percentage_completions() && !$printGrid && $studentView){
                $header .= "<th>% Complete</th>";
                $totalCellCount++;
            }
		}	  
		$headerObj = CGQualification::get_criteria_headers($criteriaNames, $subCriteriaArray, 
                $advancedMode, $editing, $totalCellCount);
		$subHeader = $headerObj->subHeader;
		$header .= $subHeader;
		$header .= "</tr></thead>";
		$headerObj->header = $header;
		return $headerObj;
	}
    
    /**
     * Get the maximum number of ranges required for any unit on this qual, linked to a task of given name
     * This is so we know what colspan to set "Task 2", etc... as
     * @param type $taskName 
     */
    public function max_all_ranges_of_task_name($taskName)
    {

        $max = 1;

        if(!$this->units) return $max;

        // Loop through units
        foreach($this->units as $unit)
        {

            // Find criteria on this unit with taskName
            $criteria = $unit->find_criteria_by_name($taskName);
            if(!$criteria) continue;

            // Get the ranges on this task
            $ranges = $criteria->get_all_possible_ranges();
            if(!$ranges) continue;

            $cnt = count($ranges);
            if($cnt > $max) $max = $cnt;                

        }

        return $max;

    }
    
    public static function get_sub_criteria_headers($criteriaName)
    {
        
        global $DB;
        
        #$records = $DB->get_records("block_bcgt_criteria");
        
    }
    
    public static function get_criteria_headers($criteriaNames, $subCriteriaArray, 
            $advancedMode, $editing, $totalCellCount = 0)
	{		
		$headerObj = new stdClass();
		
		$subHeader = "";
		$criteriaCountArray = array();
		$subCriteriaNo = array();
		if($criteriaNames)
		{
			$criteriaCount = 0;
			foreach($criteriaNames AS $criteriaName)
			{
				//for each criteria create the heading. 
				$criteriaCount++;
				$subHeader .= "<th class='criteriaName c$criteriaName'><span class='criteriaName";
				$subHeader .= "'>$criteriaName</span></th>";
                $totalCellCount++;
				if($advancedMode && $editing)
				{
					//if its advanced and editing then we have the extra 
					//cell required for the add/edit comments. 
					$subHeader .= "<th class='blankHeader'></th>";
                    $totalCellCount++;
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
    
    
    public static function get_edit_form_menu($disabled = '', $qualID = -1, $typeID = -1)
	{
                
        $jsModule = array(
            'name'     => 'mod_bcgtcg',
            'fullpath' => '/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        global $PAGE;
        $PAGE->requires->js_init_call('M.mod_bcgtcg.cginiteditqual', null, true, $jsModule);
        $pathwayID = optional_param('pathway', -1, PARAM_INT);
        $pathwayTypeID = optional_param('pathwaytype', -1, PARAM_INT);
        
		$levelID = optional_param('level', -1, PARAM_INT);
		$subtypeID = optional_param('subtype', -1, PARAM_INT);
        
        $pathways = get_pathway_from_type(CGQualification::FAMILYID);
		
		$subTypes = get_subtype_from_type(-1, $levelID, CGQualification::FAMILYID);
		if($qualID != -1)
		{
			$qualLevel = Qualification::get_qual_level($qualID);
            if ($qualLevel){
                $levelID = $qualLevel->id;
            }
			$qualSubType = Qualification::get_qual_subtype($qualID);
            if ($qualSubType){
                $subtypeID = $qualSubType->id;
            }
            $pathwayType = Qualification::get_qual_pathway($qualID);
            if ($pathwayType){
                $pathwayID = $pathwayType->pathwayid;
                $pathwayTypeID = $pathwayType->pathwaytypeid;
            }
		}
            
        
		$retval = "";
        
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='pathway'><span class='required'>*</span>".get_string('pathway', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<select name='pathway' {$disabled} id='qualPathway'>";
                    $retval .= "<option value=''>".get_string('pleaseselect', 'block_bcgt')."...</option>";
                    if ($pathways)
                    {
                        foreach($pathways as $key => $pathway)
                        {
                            $sel = ($pathwayID == $key) ? 'selected' : '';
                            $retval .= "<option value='{$key}' {$sel} >{$pathway}</option>";
                        }
                    }
                $retval .= "</select>";
            $retval .= "</div>";
        $retval .= "</div>";
        
        // Pathway id set, so get type
        if ($pathwayID >= 1){
            
            $types = get_pathway_types_from_pathway($pathwayID);
            
            if ($types)
            {
                $retval .= "<div class='inputContainer'>";
                    $retval .= "<div class='inputLeft'>";
                        $retval .= "<label for='type'><span class='required'>*</span>".get_string('type', 'block_bcgt') . ": </label>";
                    $retval .= "</div>";
                    $retval .= "<div class='inputRight'>";
                        $retval .= "<select name='type' {$disabled} id='qualPathwayType'>";
                            $retval .= "<option value=''>".get_string('pleaseselect', 'block_bcgt')."...</option>";

                            foreach($types as $key => $type)
                            {
                                $sel = ($pathwayTypeID == $key) ? 'selected' : '';
                                $retval .= "<option value='{$key}' {$sel} >{$type}</option>";
                            }

                        $retval .= "</select>";
                    $retval .= "</div>";
                $retval .= "</div>";
                
                
                 // Pathway type set, so get subtypes
                if ($pathwayTypeID >= 1 && array_key_exists($pathwayTypeID, $types)){

                    $subs = get_pathway_subtypes_from_type($pathwayTypeID);

                    if ($subs)
                    {
                        $retval .= "<div class='inputContainer'>";
                            $retval .= "<div class='inputLeft'>";
                                $retval .= "<label for='type'><span class='required'>*</span>".get_string('subtype', 'block_bcgt') . ": </label>";
                            $retval .= "</div>";
                            $retval .= "<div class='inputRight'>";
                                $retval .= "<select name='subtype' {$disabled} id='qualPathwaySubType'>";
                                    $retval .= "<option value=''>".get_string('pleaseselect', 'block_bcgt')."...</option>";

                                    foreach($subs as $key => $type)
                                    {
                                        $sel = ($subtypeID == $key) ? 'selected' : '';
                                        $retval .= "<option value='{$key}' {$sel} >{$type}</option>";
                                    }

                                $retval .= "</select>";
                            $retval .= "</div>";
                        $retval .= "</div>";
                        
                        
                        
                        // Sub type if set, so get levels
                        if ($subtypeID >= 1 && array_key_exists($subtypeID, $subs)){
                            
                            $levels = get_level_from_type($typeID, CGQualification::FAMILYID, $subtypeID);
                            
                            if ($levels)
                            {
                                $retval .= "<div class='inputContainer'>";
                                    $retval .= "<div class='inputLeft'>";
                                        $retval .= "<label for='type'><span class='required'>*</span>".get_string('level', 'block_bcgt') . ": </label>";
                                    $retval .= "</div>";
                                    $retval .= "<div class='inputRight'>";
                                        $retval .= "<select name='level' {$disabled} id=''>";
                                            $retval .= "<option value=''>".get_string('pleaseselect', 'block_bcgt')."...</option>";

                                            foreach($levels as $level)
                                            {
                                                $sel = ($levelID == $level->get_id()) ? 'selected' : '';
                                                $retval .= "<option value='{$level->get_id()}' {$sel} >{$level->get_level()}</option>";
                                            }

                                        $retval .= "</select>";
                                    $retval .= "</div>";
                                $retval .= "</div>";
                            }
                            else
                            {
                                $retval .= "<select name='level' {$disabled} id='qualPathwaySubType'>";
                                    $retval .= "<option value=''>".get_string('nolevelsforthispathway', 'block_bcgt')."</option>";
                                $retval .= "</select>";
                            }
                            
                        }
                        
                        
                    }
                    else
                    {
                        $retval .= "<select name='subtype' {$disabled} id='qualPathwaySubType'>";
                            $retval .= "<option value=''>".get_string('nosubtypesforthispathway', 'block_bcgt')."</option>";
                        $retval .= "</select>";
                    }

                }
                
                
                
                
            }
            else
            {
                $retval .= "<select name='type' {$disabled} id='qualPathwayType'>";
                    $retval .= "<option value=''>".get_string('notypesforthispathway', 'block_bcgt')."</option>";
                $retval .= "</select>";
            }
                        
            
        }
        
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
        #$row[] = '<span class="grid_cell_blank"></span>';
        $row[] = '';
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
                #$row[] = '<span class="grid_cell_blank '.$extraClass.' subCriteria subCriteria_'.$criteriaName.'"></span>';
                $row[] = '';
            }
		}
		return $row;
	}
    
    
    
    
     /**
     * Get the image of a particular criteria value and return an image object
     * @global type $CFG
     * @param string $studentValue
     * @param type $typeID
     * @return stdClass 
     */
    public static function get_simple_grid_images($studentValue, $longValue)
    {

        global $CFG, $DB;

        if($studentValue == null)
        {
            $studentValue = "N/A";
        }
        
        $class = 'stuValue'.$studentValue;

        // Since we do not store values in the same way in the DB for bespoke, we cannot use the value_settings table
        // First we will check to see if an img for this has been uploaded
        $img = $CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/custom/'.$studentValue.'.png';
        if (file_exists($img)){
            $image = $img;
        } elseif (CGQualification::get_default_value_image($studentValue) != false ){
            // if not, check our defaults
            $image = $CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/'.CGQualification::get_default_value_image($studentValue);
        } else {
            // If still not, broken img
            $image = $CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/icon_NoIcon.png';
        }
        
        // If not, let's check our defaults

        $obj = new stdClass();
        $obj->image = $image;
        $obj->class = $class;
        $obj->title = $longValue;
        
        
        return $obj;

    }
    
    
    
    public static function get_default_value_image($val){
        
        $values = array(
            "A" => "icon_Achieved.png",
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
            "PTD" => "icon_PastTargetDate.png",
            "WS" => "icon_WorkSubmitted.png",
            "WNS" => "icon_WorkNotSubmitted.png"
        );
        
        if (array_key_exists($val, $values)){
            return $values[$val];
        } else {
            return false;
        }
        
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
                
        
        switch($familyID)
        {
            
            case CGQualification::FAMILYID:
                
                $pathway = optional_param('pathway', -1, PARAM_INT);
                $pathwayType = optional_param('pathwaytype', -1, PARAM_INT);
                                                
                switch($pathway)
                {
                    
                    // hair and Beauty
                    case Pathway::CGHB:
                        
                        switch($pathwayType)
                        {
                        
                            case PathwayType::CGHBVRQ:
                                require_once 'CGHBVRQQualification.class.php';
                                return new CGHBVRQQualification($qualID, $params, $loadParams);
                            break;
                            
                            case PathwayType::CGHBNVQ:
                                require_once 'CGHBNVQQualification.class.php';
                                return new CGHBNVQQualification($qualID, $params, $loadParams);
                            break;
                        
                        }
                        
                    break;
                
                    // General
                    default:
                        
                        return new CGQualification($qualID, $params, $loadParams);
                        
                    break;
                    
                    
                }
                                
            break;
            
        }
        
        return false;

    }
    
    
    
    
    public function get_criteria_met_value()
    {
        return CGQualification::get_criteria_met_val();
    }
    
    public function get_criteria_not_met_value()
    {
        return CGQualification::get_criteria_not_met_val();
    }
    
    /**
	 * Get the value for not met
	 */
	public static function get_criteria_met_val()
	{
		//This gets the one criteria value that will go towards having
		// the criteria not met
		//THIS ASSUMES ONE!!!!!
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $params = array(CGQualification::ID, 'A');
		$record = $DB->get_record_sql($sql, $params);
		if($record)
		{
			return $record->id;
		}
		return -1;
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
        $params = array(CGQualification::ID, 'X');
		$record = $DB->get_record_sql($sql, $params);
		if($record)
		{
			return $record->id;
		}
		return -1;
	}
    
    public static function get_non_met_values(){
        global $DB;
        return $DB->get_records_sql("SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND specialval NOT IN ('A', 'X')", array(CGQualification::ID));
    }
    
     /**
     * Same as above, except it doesn't expect the sub criteria to be in "Par." format
     * @param type $parent 
     */
    function get_distinct_list_of_all_sub_criteria($parent)
    {
        global $CFG, $DB;
        $sql = "SELECT DISTINCT(c.name)
                FROM {block_bcgt_qual_units} qu 
                LEFT JOIN {block_bcgt_criteria} c ON c.bcgtunitid = qu.bcgtunitid 
                LEFT JOIN {block_bcgt_criteria} p ON p.id = c.parentcriteriaid
                WHERE qu.bcgtqualificationid = ? 
                AND c.name IS NOT NULL 
                AND c.parentcriteriaid IS NOT NULL
                AND p.name = ?";

        $records = $DB->get_records_sql($sql, array($this->id, $parent));

        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
        $criteriaSorter = new CGCriteriaSorter();
        if($records) usort($records, array($criteriaSorter, "ComparisonOnTheFly"));

        return $records;
    }
    
    /**
     * Get the ID of the a specific value, by short code
     * @global type $CFG
     * @param type $val Default 'X' - Not Achieved
     * @return type 
     */
    public function get_criteria_specific_value($val)
    {
        global $DB;
        $record = $DB->get_record("block_bcgt_value", array("bcgttypeid" => $this->get_class_ID(), "shortvalue" => $val));
        return (isset($record->id)) ? $record->id : -1;
    }
    
    public function has_printable_report(){
        return true;
    }
    
    
    
    
    
    
}