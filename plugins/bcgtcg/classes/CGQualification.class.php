<?php

/**
 * Description of CGQualification
 *
 * @author mchaney
 */
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Qualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/lib.php');


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
    protected $defaultColumns = array('picture', 'username', 'name');
    protected $usePercentageBar = true;
    
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
    public function get_default_credits()
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
        
        global $DB;
        
        $percent = $DB->get_record("block_bcgt_qual_attributes", array("bcgtqualificationid" => $this->id, "attribute" => "PERCENT_BAR"));
        return ($percent && $percent->value == 0) ? false : true;
        
    }
    
    protected function get_simple_qual_report_tabs()
    {
        $tabs = parent::get_simple_qual_report_tabs();
        return $tabs + array("u"=>"units", "co"=>"classoverview");
    }
    
    
    public static function activity_view_page($courseID, $tab)
    {
        if($tab == 'unit')
        {
            return cg_activity_by_unit_page($courseID);
        }
        elseif($tab == 'act')
        {
            return cg_activity_by_activity_page($courseID);
        }
    }
    
    
    public static function add_activity_view_page($courseID, $unitID, $activityID)
    {
        $jsModule = array(
            'name'     => 'mod_bcgtcg',
            'fullpath' => '/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        global $PAGE, $CFG;
        $PAGE->requires->js_init_call('M.mod_bcgtcg.cgaddactivity', null, true, $jsModule);
		
        
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
        $units = bcgt_get_course_units($courseID, CGQualification::FAMILYID);
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
                        
                        bcgt_cg_process_mod_unit_selection($activityID, $uID, $courseID);
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
                        bcgt_cg_process_mod_selection_changes($activityID, $activityUnit->id, $courseID);
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
                $retval .= get_cg_activity_unit_table($activityID, $activityUnit->id, $courseID);
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
            $retval .= get_cg_activity_unit_table($activityID, $newUnitID, $courseID, true);
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
            $unitActivities = CGQualification::get_unit_activities($courseID, $unitID);
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
        $unitActivities = CGQualification::get_unit_activities($courseID, $unitID);
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
                $retval .= get_cg_unit_activity_table($unitActivity->id, $unit, $courseID, false, $unitActivity, $modLinking, $modIcons);
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
            $retval .= get_cg_unit_activity_table($newActivityID, $unit, $courseID, true);
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
    
    
    
    public static function get_unit_activities($courseID, $unitID)
    {
        //this needs to get all of the activities for this course for this unit
        //order by due date
        return bcgt_unit_activities($courseID, $unitID);
        
    }
    
    
    public static function gradebook_check_page($courseID)
    {
        //need a drop down of groupINGs:
        //all and each group name on course. 
        global $PAGE;
        $retval = '';
        $groupingID = -1;
        $jsModule = array(
            'name'     => 'mod_bcgtcg',
            'fullpath' => '/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        $PAGE->requires->js_init_call('M.mod_bcgtcg.initactivitiescheck', array(), true, $jsModule);
        load_javascript(true);
        //get list of quals that are of type BTEC that are on this course:
        $quals = bcgt_get_course_quals($courseID, CGQualification::FAMILYID);
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
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
        $criteriaSorter = new CGCriteriaSorter();
		usort($criteriaNames, array($criteriaSorter, "ComparisonSimple"));
        $this->usedCriteriaNames = $criteriaNames;
        
        //are we a student view of not?
        $userID = optional_param('sID', -1, PARAM_INT);
        $view = optional_param('view', 'os', PARAM_TEXT);
//        if($userID == -1)
//        {
//            //then we are looking at specific student they always see the icons. 
//            //therefore need the key.
//            $retval .= $this->get_grid_assignment_overview_buttons($view, $courseID);
//        }
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
    
    
    public static function process_mod_tracker_options($courseModuleID, $courseID)
    {
        //get selected units
        $units = null;
        $unitsSelected = optional_param('bcgtunitsselectedcg', '', PARAM_TEXT);
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
                bcgt_cg_process_mod_units($courseModuleID, $unit, $courseID);
            }
        }
        bcgt_cg_remove_mod_unit_selection($courseModuleID, $units);  
        //explode the string on _
        //loop over each unit
        
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
                    //i want thre columns - well i want a lot of things, but we don't always get what we want. also you spelt three wrong.
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
    
    
    public static function get_mod_tracker_options($courseModuleID, $courseID)
    {
        $retval = '';
        $jsModule = array(
            'name'     => 'mod_bcgtcg',
            'fullpath' => '/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        global $PAGE, $CFG;
        $PAGE->requires->js_init_call('M.mod_bcgtcg.cgmodactivity', null, true, $jsModule);
        $unitsSelected = '';
        $units = bcgt_get_course_units($courseID, CGQualification::FAMILYID);
        $activityUnits = get_activity_units($courseModuleID, CGQualification::FAMILYID);
        if($activityUnits)
        {
            foreach($activityUnits AS $activityUnit)
            { 
                $unitsSelected .= '_'.$activityUnit->id;
                $retval .= '<div class="bcgt_col bgctActivityUnit">';
                $retval .= get_cg_activity_unit_table($courseModuleID, $activityUnit->id, $courseID, false, true);
                $retval .= '</div>';
            }
        }
        $retval .= '<label>'.get_string('addnewunit', 'block_bcgt').' : </label> (CG)<br />';
        $retval .= '<select name="nUIDCG" id="nUIDCG">';
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
        $retval .= '<input type="submit" id="bcgtAddUnitCG" course="'.
                $courseID.'" cmid="'.$courseModuleID.'" name="addUnit" value="'.
                get_string('addunit', 'block_bcgt').'"/>';
        $retval .= '<span id="bcgtloadingcg"></span>';
        $retval .= '<input type="hidden" name="bcgtunitsselectedcg" value="'.$unitsSelected.'" id="bcgtunitsselectedcg"/>';
        $retval .= '<div id="bcgtMODAddUnitSelectionCG">';
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
                        '&fID='.  CGQualification::FAMILYID.'';
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
                                    CGQualification::FAMILYID.'" class="bcgtcritnoass">';
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
    
    
    /**
     * Gets the form fields that will go on edit_qualification_form.php
     * They are different for each qual type
     * e.g for Alevel its an <input> for ums
     */
    public function get_edit_form_fields()
    {
        
        $percent = optional_param('usepercent', 1, PARAM_INT);
        
        $chk = array();
        $chk['PERCENT_YES'] = ($percent) ? 'checked' : '';
        $chk['PERCENT_NO'] = (!$percent) ? 'checked': '';
                
        $retval = '<div class="inputContainer"><div class="inputLeft">';
        $retval .= '<label for="credits">'.get_string('bteccredits', 'block_bcgt')
                .': </label></div>';
		$retval .= '<div class="inputRight">'.
                '<input type="number" name="credits" value="'.$this->credits.'"  /></div></div>';
        
        
        // Use the percentage bar?
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='weighting'>".get_string('percentagebar', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<input type='radio' name='usepercent' value='1' {$chk['PERCENT_YES']} /> Enable<br><input type='radio' name='usepercent' value='0' {$chk['PERCENT_NO']} /> Disable";
            $retval .= "</div>";
        $retval .= "</div>";
        
                
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
        $percentBar = optional_param('usepercent', 1, PARAM_INT);
                
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
        $this->usePercentageBar = (bool)$percentBar;
        
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
        return $users;
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
        
        // Percent bar attribute
        $stdObj = new stdClass();
        $stdObj->bcgtqualificationid = $this->id;
        $stdObj->attribute = 'PERCENT_BAR';
        $stdObj->value = $this->usePercentageBar;
        $DB->insert_record("block_bcgt_qual_attributes", $stdObj);
        
        
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
        
        // Use percent bar attribute
        $percent = $DB->get_record("block_bcgt_qual_attributes", array("bcgtqualificationid" => $this->id, "attribute" => "PERCENT_BAR"));
        if ($percent)
        {
            $stdObj = new stdClass();
            $stdObj->id = $percent->id;
            $stdObj->value = (int)$this->usePercentageBar;
            $DB->update_record("block_bcgt_qual_attributes", $stdObj);
        }
        else
        {
            $stdObj = new stdClass();
            $stdObj->bcgtqualificationid = $this->id;
            $stdObj->attribute = 'PERCENT_BAR';
            $stdObj->value = (int)$this->usePercentageBar;
            $DB->insert_record("block_bcgt_qual_attributes", $stdObj);
        }
        
        
     
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
                    
                    echo "<td></td>";
                                        
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
    
    public function call_display_student_grid_external($from = false)
    {
        return $this->display_student_grid(false, true, true, $from);
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
    public function display_student_grid($fullGridView = true, $studentView = true, $basicView = false, $from = false)
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
        
        $editing = (has_capability('block/bcgt:editstudentgrid', $context) && in_array($grid, array('se', 'ae'))) ? true : false;
        
        $cols = 3;
        if ($this->has_percentage_completions()){
            $cols++;
        }
        
        $retval = '<div>';
        
//        if (!$basicView)
//        {
//        
//            $retval .= "<div class='bcgtgridbuttons'>";
//            $retval .= "<input type='submit' id='viewsimple' class='gridbuttonswitch viewsimple' name='viewsimple' value='View Simple'/>";
//            $retval .= "<input type='submit' id='viewadvanced' class='gridbuttonswitch viewadvanced' name='viewadvanced' value='View Advanced'/>";
//            $retval .= "<br>";  
//            
//            if($courseID != -1)
//            {
//                $context = context_course::instance($courseID);
//            }
//            if(has_capability('block/bcgt:editstudentgrid', $context))
//            {	
//                $retval .= "<input type='submit' id='editsimple' class='gridbuttonswitch editsimple' name='editsimple' value='Edit Simple'/>";
//                $retval .= "<input type='submit' id='editadvanced' class='gridbuttonswitch editadvanced' name='editadvanced' value='Edit Advanced'/>"; 
//            }
//            $retval .= "</div>";
//        
//        }
        
        
        if (!$basicView)
        {
        
            $retval .= "<div class='c'>";

                $retval .= "<input type='button' id='viewsimple' class='btn' value='View Simple' />";
                $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                $retval .= "<input type='button' id='viewadvanced' class='btn' value='View Advanced' />";                
                
                $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                
                $retval .= "<input type='button' id='editsimple' class='btn' value='Edit Simple' />";
                $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                $retval .= "<input type='button' id='editadvanced' class='btn' value='Edit Advanced' />";                
                    
                $retval .= "<br><br>";
                $retval .= "<a href='#' onclick='toggleAddComments();return false;'><input id='toggleCommentsButton' type='button' class='btn' value='".get_string('addcomment', 'block_bcgt')."' disabled='disabled' /></a>";
                    
            $retval .= "</div>";
        
        }
        
        
        if ($basicView && $from != 'portal')
        {
            $retval .= "<p class='c'><a href='".$CFG->wwwroot."/blocks/bcgt/grids/print_grid.php?sID={$this->studentID}&qID={$this->id}' target='_blank'><img src='".$OUTPUT->pix_url('t/print', 'core')."' alt='' /> ".get_string('printgrid', 'block_bcgt')."</a> &nbsp;&nbsp;&nbsp;&nbsp; <a href='".$CFG->wwwroot."/blocks/bcgt/grids/print_report.php?sID={$this->studentID}&qID={$this->id}' target='_blank'><img src='".$OUTPUT->pix_url('t/print', 'core')."' alt='' /> ".get_string('printreport', 'block_bcgt')."</a></p>";
        }
        
        
        $retval .= $this->get_grid_key();
        
        $retval .= "<br><br>";
        $retval .= "<p id='loading' class='c'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/ajax-loader.gif' alt='loading...' /></p>";
       
        
        
        $retval .= '<input type="hidden" id="grid" name="g" value="'.$grid.'"/>';   
        $retval .= '<input type="hidden" id="sID" value="'.$this->studentID.'" />';
        $retval .= '<input type="hidden" id="qID" value="'.$this->id.'" />';
        $advancedMode = false;
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
            $PAGE->requires->js_init_call('M.mod_bcgtcg.initstudentgrid', array($this->id, $this->studentID, $grid, $cols), true, $jsModule);
        }
        
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        $retval .= load_javascript(true, $basicView);
        
        $retval .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/css/start/jquery-ui-1.10.3.custom.min.css' />";
//        $retval .= "
//		<div class='gridKey adminRight'>";
//		if($studentView)
//		{
//			$retval .= "<h2>Key</h2>";
//			//Are we looking at a student or just the actual criteria for the grid.
//			//if students then get the key that tells everyone what things stand for
//			$retval .= CGQualification::get_grid_key();
//		}
//		$retval .= "</div>";
//        
//        $retval .= "<br style='clear:both;' />";
        
        
                
        $unitGroups = $this->get_unit_groups();
        if ($unitGroups)
        {
            
            $retval .= "<div class='c'>";
            $retval .= "<select id='changeUnitGroup'>";
            
                $retval .= "<option value=''>".get_string('changeshowunitgroups', 'block_bcgt')."</option>";
                
                foreach($unitGroups as $group)
                {
                    $retval .= "<option value='{$group->name}'>{$group->name}</option>";
                }
            
            $retval .= "</select>";
            $retval .= "</div>";
            $retval .= "<br><br>";
            
        }
        
                
        
        //the grid -> ajax
        $retval .= '<div id="cgStudentGrid">';
                
        $retval .= "<div id='CGStudentGrid' class='studentGridDiv ".
        $grid."StudentGrid tableDiv'><table align='center' class='student_grid".
                $grid."FixedTables' id='CGStudentGridTable'>";
        
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
        
        $retval .= $this->get_student_grid_data($advancedMode, $editing, $studentView);
        
        $retval .= "</tbody>";
        $retval .= "<tfoot></tfoot>";
        $retval .= "</table>";
        
        // Qual Comment
//        if ($this->comments == '') $this->comments = 'N/A';
//        $retval .= "<div id='qualComment'><br><fieldset><legend><h2>Qualification Comments</h2></legend><br>".nl2br( htmlentities($this->comments, ENT_QUOTES) )."</fieldset></div>";
//        
//        if($studentView && !$editing)
//		{
//            //>>BEDCOLL TODO this need to be taken from the qual object
//            //as foundatonQual is different
//            $retval .= '<table id="summaryAwardGrades">';
//			$retval .= $this->show_predicted_qual_award($this->predictedAward, $context);
//            $retval .= '</table>';
//            
//        }
        $retval .= "</div>";
        $retval .= '</div>';
        $retval .= '</div>';
        
        $retval .=' <br><br>';
        
        
        if($studentView && !$editing)
		{
            //>>BEDCOLL TODO this need to be taken from the qual object
            //as foundatonQual is different
            $retval .= '<table id="summaryAwardGrades">';
                $retval .= $this->show_predicted_qual_award($this->predictedAward, $context);
                $retval .= $this->show_target_grade();    
                $retval .= $this->show_aspirational_grade();
            $retval .= '</table>';
            
        }
        
        
        if ($basicView){
            $retval .= " <script>$(document).ready( function(){
                M.mod_bcgtcg.initstudentgrid(Y, {$this->id}, {$this->studentID}, '{$grid}', '{$cols}');
            } ); </script> ";
        }
        
        return $retval;
        
    }
    
    
    
    public function get_student_grid_data($advancedMode, $editing, 
            $studentView)
    {
        
        global $DB, $OUTPUT;
         
        $output = "";
         
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
        
        $unitGroup = optional_param('uGroup', false, PARAM_TEXT);        
                
        $rowCount = 0;
                            
        foreach($units AS $unit)
        {
            
            if(($studentView && $unit->is_student_doing()) || !$studentView)
			{	
            
                // If we are displaying only certain unit group, check that
                if ($unitGroup)
                {

                    // Check the group for this unit on this qual
                    $group = $unit->get_unit_group($this->id);
                    if ($group != $unitGroup)
                    {
                        continue;
                    }

                }
                                
                $retval = "";
                $retval .= "<tr>";
                
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
                                                
                $retval .= "<td class='unitCommentCell'>";
                
                // Unit Comment
                $comments = $unit->get_comments();
                
                
                $retval .= "<div class='criteriaTDContent'>";
                                
                    if(has_capability('block/bcgt:editunit', $context)){
                        //$retval .= "<a href='{$CFG->wwwroot}/blocks/bcgt/grids/unit_grid.php?uID={$unit->get_id()}&qID={$this->id}' target='_blank' title='View Unit Grid'><img src='".$OUTPUT->pix_url('i/calendar', 'core')."' /></a><br>";
                        $retval .= "<a href='{$CFG->wwwroot}/blocks/bcgt/forms/edit_unit.php?unitID={$unit->get_id()}' target='_blank' title='Edit Unit'><img src='".$OUTPUT->pix_url('t/editstring', 'core')."' /></a>";
                    }

                $retval .= "</div>";

                $retval .= "<div class='hiddenCriteriaCommentButton'>";

                    $username = $this->student->username;
                    $fullname = fullname($this->student);
                    $unitname = bcgt_html($unit->get_name());
                    $critname = "N/A";
                    $cellID = "cmtCell_U_{$unit->get_id()}_S_{$this->studentID}_Q_{$this->id}";

                    if (!empty($comments))
                    {
                        $retval .= "<img id='{$cellID}' criteriaid='-1' unitid='{$unit->get_id()}' studentid='{$this->studentID}' qualid='{$this->id}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='student' class='editCommentsUnit' title='Click to Edit Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/comment_edit.png' alt='".get_string('editcomments', 'block_bcgt')."' />";
                    }
                    else
                    {
                        $retval .= "<img id='{$cellID}' criteriaid='-1' unitid='{$unit->get_id()}' studentid='{$this->studentID}' qualid='{$this->id}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='student' class='addCommentsUnit' title='Click to Add Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/comment_add.png' alt='".get_string('addcomment', 'block_bcgt')."' />";
                    }

                    //$retval .= "<span class='tooltipContent' style='display:none !important;'>".bcgt_html($this->comments, true)."</span>";
                    $retval .= "<div class='popUpDiv bcgt_unit_comments_dialog' id='dialog_S{$this->studentID}_U{$unit->get_id()}_Q{$this->id}' qualID='{$this->id}' unitID='{$unit->get_id()}' critID='-1' studentID='{$this->studentID}' grid='student' imgID='{$cellID}' title='Comments'>";
                        $retval .= "<span class='commentUserSpan'>Comments for {$fullname} : {$username}</span><br>";
                        $retval .= "<span class='commentUnitSpan'>{$unit->get_display_name()}</span><br>";
                        $retval .= "<span class='commentCriteriaSpan'>N/A</span><br><br><br>";
                        $retval .= "<textarea class='dialogCommentText' id='text_S{$this->studentID}_U{$unit->get_id()}_Q{$this->id}'>".bcgt_html($comments)."</textarea>";
                    $retval .= "</div>";


                $retval .= "</div>";
                
                
                
//                $cellID = "cmtCell_U_{$unit->get_id()}_S_{$user->id}_Q_{$this->get_id()}";
//                
//		        
//                $username = htmlentities( $user->username, ENT_QUOTES );
//                $fullname = htmlentities( fullname($user), ENT_QUOTES );
//                $unitname = htmlentities( $unit->get_name(), ENT_QUOTES);
//                $critname = "N/A";   
//                
//                if($advancedMode && $editing)
//                {
//
//                    if(!empty($getComments))
//                    {                
//                        $retval .= "<img id='{$cellID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' qualid='{$this->id}' unitid='{$unit->get_id()}' studentid='{$this->studentID}' grid='stud' type='button' class='editCommentsUnit' title='Click to Edit Unit Comments' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/comments.jpg' />";
//                    }
//                    else
//                    {                        
//                        $retval .= "<img id='{$cellID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' qualid='{$this->id}' unitid='{$unit->get_id()}' studentid='{$this->studentID}' grid='stud' type='button' class='addCommentsUnit' title='Click to Add Unit Comment' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/plus.png' />";
//                    }
//
//                }
//                else
//                {
//                    if(!empty($getComments)){
//                        $retval .= "<img src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/comment-icon.png' class='showCommentsUnit' />";
//                        $retval .= "<div class='tooltipContent'>".htmlspecialchars($getComments, ENT_QUOTES)."</div>";
//                    }
//                    
//                }
//                
//                $retval .= "<div class='popUpDiv bcgt_unit_comments_dialog' id='dialog_S{$this->studentID}_U{$unit->get_id()}_Q{$this->id}' qualID='{$this->id}' unitID='{$unit->get_id()}' critID='-1' studentID='{$this->studentID}' grid='student' imgID='{$cellID}' title='Comments'>";
//                    $retval .= "<span class='commentUserSpan'>Comments for {$fullname} : {$username}</span><br>";
//                    $retval .= "<span class='commentUnitSpan'>{$unit->get_display_name()}</span><br>";
//                    $retval .= "<span class='commentCriteriaSpan'>N/A</span><br><br><br>";
//                    $retval .= "<textarea class='dialogCommentText' id='text_S{$this->studentID}_U{$unit->get_id()}_Q{$this->id}'>{$getComments}</textarea>";
//                $retval .= "</div>";
                
                
                $retval .= "</td>";
                
                $studentID = -1;
				if($studentView)
				{
					//This is used to link to another page.
					//if studentID = -1 then we know we are not
					//looking at the student but the qual in general
					$studentID = $this->studentID;
				}
                
                $link = $unit->get_name();
                
                if(has_capability('block/bcgt:editunit', $context)){
                    $link = "<a href='{$CFG->wwwroot}/blocks/bcgt/grids/unit_grid.php?uID={$unit->get_id()}&qID={$this->id}' target='_blank' title='View Unit Grid'>{$unit->get_name()}</a><br>";
                }
                
                $retval .= "<td style='width:200px;min-width:200px;'>";
				$retval .= "<span id='uID_".$unit->get_id()."' class='uNToolTip unitName".$unit->get_id()."' unitID='{$unit->get_id()}' studentID='{$this->studentID}'>".$link."</span>";
                $retval .= "<span style='color:grey;font-size:85%;'><br />(".$unit->get_credits()." Credits)</span>";	
                $retval .= " <img src='".$CFG->wwwroot."/blocks/bcgt/pix/info.png' height='12' width='12' class='uNToolTipInfo hand' unitID='{$unit->get_id()}' /><div class='unitInfoContent' title='{$unit->get_display_name()}'>{$unit->build_unit_details_table()}</div>";
				$retval .= "</td>";
                
                if($studentView)
				{
					if($editing)
					{
						$retval .= "<td id='unitAwardCell_{$unit->get_id()}_{$this->studentID}' style='width:110px;min-width:110px;'>".$this->edit_unit_award($unit, $rank, $award, $unitAwards)."</td>";
                        
                    }
					else
					{
						//print out the unit award column
						//$retval .= "<td id='unitAward_".$unit->get_id()."' class='unitAward r".$unit->get_id()." rank$rank'>".$award."</td>";
                        $retval .= '<td id="unitAwardCell_'.$unit->get_id().'_'.$this->studentID.'" class="unitAward" style="width:110px;min-width:110px;"><span id="unitAwardAdv_'.$unit->get_id().'_'.$this->studentID.'">'.$award.'</span></td>';
                    }
                    
                    // Points
//                    $unitPoints = $unit->get_student_unit_points();
//                    if (!$unitPoints) $unitPoints = "-";
//                    $rowArray[] = "<span id='unitPoints_{$unit->get_id()}'>{$unitPoints}</span>";
                    
                    // Percent
                    if($this->has_percentage_completions()){
                        $retval .= "<td style='width:110px;min-width:110px;'><div class='tdPercentCompleted'>".$unit->display_percentage_completed()."</div></td>";
                    }
                    
				}
                
                
                
                
                if($criteriaNames)
				{
					//if we have found the used criteria names. 
					$criteriaCount = 0;
					foreach($criteriaNames AS $criteriaName)
					{	
						//TODO
                        $width = ($editing) ? 100 : 40;
						$criteriaCount++;
						if($studentView)
						{
							//if its the student view then lets print
							//out the students unformation
                            $studentCriteria = $unit->get_single_criteria(-1, $criteriaName);
							if($studentCriteria)
							{	
                                $c = ($editing) ? 'Edit' : 'NonEdit';
                                $retval .= "<td style='width:40px;min-width:{$width}px;max-width:{$width}px;' class='criteriaCell criteriaValue{$c}' qualID='{$this->id}' criteriaID='{$studentCriteria->get_id()}' studentID='{$this->studentID}' unitID='{$unit->get_id()}' >".$studentCriteria->get_grid_td($editing, $advancedMode, $unit, $user, $this, 'student')."</td>";
							}//end if student criteria
							else //not student criteria (i.e. the criteria doesnt exist on that unit)
							{         
                                //retval needs to be an array of the columns
								$retval .= "<td class='criteriaCell' style='width:{$width}px;min-width:{$width}px;'></td>";
                                #$rowArray[] = $retval;
							}//end else not sudent criteria	
                            
                            
						}
						else//its not the student view
						{//This means we are just showing the qual as a whole. 
							//then lets just test if he unit has that criteria
							//and mark it as present or not
							$retval .= "<td style='width:{$width}px;min-width:{$width}px;'>!sV</td>"; # wtf?
//							$retval .= $this->get_non_student_view_grid($criteriaCount, $criteriaCountArray, $criteriaName, $unit, $subCriteriaArray);
//                            $rowArray[] = $retval;
                            
                        }
						
					}//end for each criteria
				}//end if criteria names
                
                $retval .= "</tr>";
                
                $output .= $retval;                
            
            }
            
        }
                
        return $output;
        
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
                    $out .= '<td class="nameCol" sID="'.$student->id.'" qID="'.$this->id.'" title="Click here to apply set of units">'.$student->firstname.' '.$student->lastname.'</td>';
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
    
    public static function get_grid_key()
    {
        
        global $CFG;
        
        $output = "";
        
        $possibleGridValues = CGQualification::get_possible_values(CGQualification::ID, true);
                
        $width = 100 / (count($possibleGridValues) + 1);

        $output .= "<div id='cgGridKey'>";

            $output .= "<table>";

                $output .= "<tr>";
                    $output .= "<th colspan='".(count($possibleGridValues) + 1)."'>".get_string('gridkey', 'block_bcgt')."</th>";
                $output .= "</tr>";

                $output .= "<tr class='imgs'>";

                if ($possibleGridValues)
                {
                    foreach($possibleGridValues as $possible)
                    {
                        $image = CGQualification::get_grid_image($possible);
                        if ($image)
                        {
                            $output .= "<td style='width:{$width}%;'><img src='{$image->image}' alt='{$image->title}' class='{$image->class}' /></td>";
                        }
                    }
                }

                $output .= "</tr>";


                $output .= "<tr class='names'>";

                if ($possibleGridValues)
                {
                    foreach($possibleGridValues as $possible)
                    {
                        $name = (!is_null($possible->customvalue) && $possible->customvalue != '') ? $possible->customvalue : $possible->value;
                        $output .= "<td style='width:{$width}%;'>{$name}</td>";
                    }
                }

                $output .= "</tr>";


            $output .= "</table>";

        $output .= "</div>";

        return $output;
        
    }
    
         
    
    
//    
//    /**
//	 * This will build up the key for the Grid used in student view
//	 * and single view. 
//	 * SHOULD be a static function to the UNIT view can get to it
//	 * At the moment we have duplicate calls. 
//	 */
//	public static function get_grid_key($string = true)
//	{
//        global $CFG; 
//        $file = $CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg';
//        if($string)
//        {
//            $retval = '';
//        }
//        else
//        {
//            $retval = array();
//        }
//        
//        $possibleValues = CGQualification::get_possible_values(CGQualification::ID, true);
//        
//        $isAchieved = true;
//        
//        if($possibleValues)
//        {
//            foreach($possibleValues AS $possibleValue)
//            {
//                
//                $value = '<span class="keyValue"><img class="keyImage"';
//                    if(isset($possibleValue->customimg) && $possibleValue->customimg != '')
//                    {
//                        $icon = $possibleValue->customimg;
//                    }
//                    else
//                    {
//                        $icon = $possibleValue->coreimg;
//                    }
//                    if(isset($possibleValue->customvalue) && $possibleValue->customvalue != '')
//                    {
//                        $desc = $possibleValue->customvalue;
//                    }
//                    else
//                    {
//                        $desc = $possibleValue->value;
//                    }
//                $value .= ' src="'.$file.$icon.'"/> = '.$desc.'</span>';
//                
//                $currentIsAchieved = $isAchieved;
//                
//                if ($possibleValue->specialval == 'A') $isAchieved = true;
//                else $isAchieved = false;
//                                
//                // If we have just gone from achieved to others, line break
//                if ($currentIsAchieved && !$isAchieved && $string){
//                    $retval .= "<br>";
//                }
//                
//                
//                if($string)
//                {
//                    $retval .= $value . '&nbsp;&nbsp;&nbsp;';
//                }
//                else
//                {
//                    $retval[] = $value;
//                }
//            }
//        }      
//        
//        if ($string){
//            
//            $retval .= '<br>';
//            $retval .= '<span class="keyValue"><img class="keyImage" src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/'.
//                'grid_symbols/core/icon_HasComments.png"/> = Comments (Hover to view)'.
//                '</span>&nbsp;&nbsp;&nbsp;';
//            
//            $retval .= '<span class="keyValue"><img class="keyImage" src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/'.
//                'grid_symbols/core/icon_WasLate.png"/> = Was originally Late'.
//                '</span>';
//            
//            
//        } else {
//            
//            $retval[] = '<span class="keyValue"><img class="keyImage" src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/'.
//                'grid_symbols/core/icon_HasComments.png"/> = Comments (Hover to view)'.
//                '</span>';
//            
//            $retval[] = '<span class="keyValue"><img class="keyImage" src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/'.
//                'grid_symbols/core/icon_WasLate.png"/> = Was originally Late'.
//                '</span>';
//            
//        }
//        
//        
//
//        return $retval;
//        
//	}
    
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
                    ELSE 2
                   END ) ASC ,
                value.ranking ASC,
                value.value ASC";
        
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
    
    protected function show_aspirational_grade()
    {
        
        $retval = "";
        
        $gradeObjs = bcgt_get_aspirational_target_grade($this->studentID, $this->id);
        
        if($gradeObjs)
        {
            $gradeObj = end($gradeObjs);
            $retval .= "<tr>";
            $retval .= "<td>".get_string('asptargetgrade', 'block_bcgt')."</td>";
            $retval .= "<td>";
            $retval .= $gradeObj->grade;
            $retval .= "</td>";
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
                    $retval .= "<tr><td>".get_string('targetgrade', 'block_bcgt')."</td><td>{$grade}</td></tr>";
                }
            }
            
        }
        
        return $retval;
        
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
        
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        else
        {
            $context = context_course::instance($COURSE->id);
        }
                
        
        $retval = '<div>';//overall subject wrapper
        $retval .= '<input type="hidden" id="grid" name="g" value="'.$grid.'"/>';        
        
        $editing = (has_capability('block/bcgt:editstudentgrid', $context) && in_array($grid, array('se', 'ae'))) ? true : false;
        $advancedMode = ($grid == 'a' || $grid == 'ae');        
        
        $columnsLocked = 0;
        $columns = $this->defaultColumns;
        $configColumns = get_config('bcgt','cggridcolumns');
        
        if($configColumns)
        {
            $columns = explode(",",$configColumns);
        }
        
        $columnsLocked += count($columns);
        $columnsLocked++;
        
        
        
        
        
        $jsModule = array(
            'name'     => 'mod_bcgtbtec',
            'fullpath' => '/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
                
        if ($basicView){
            $retval .= <<< JS
            <script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js'></script>
JS;
        } else {
            $PAGE->requires->js_init_call('M.mod_bcgtcg.initclassgrid', array($this->id, $grid, $columnsLocked), true, $jsModule);
        }
        
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        $retval .= load_javascript(true, $basicView);       
        $retval .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/css/start/jquery-ui-1.10.3.custom.min.css' />";
                
        
   
        
        
        $retval .= "<div class='c'>";

            $retval .= "<input type='button' id='viewsimple' class='btn' value='View Simple' />";

            if (has_capability('block/bcgt:editunitgrid', $context))
            {
                $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                $retval .= "<input type='button' id='editsimple' class='btn' value='Edit Simple' />";
            }    
            
            $retval .= "<br><br>";
        
        
        
//        $loadParams = new stdClass();
//        $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
//        $loadParams->loadAward = true;
//        $loadParams->calcAward = false;
//        $loadParams->loadAddUnits = false;
//        $this->load_users('student', false, $loadParams, $sCourseID, $groupID);
            
        $studentsArray = $this->load_users('student', false, null, $sCourseID);
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
            $retval .= '<a target="_blank" href="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/ajax/get_class_grid.php?qID='.$this->id.'&grID='.$groupingID.'&g='.$grid.'">'.get_string('ajaxrequest', 'block_bcgt').'</a>';

        }
        
        $retval .= "<br><br>";
        $retval .= "<p id='loading' class='c'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/ajax-loader.gif' alt='loading...' /></p>";
       
        
        $retval .= "</div>";
        
        
        
        
        
        //the grid -> ajax
        $retval .= '<div id="CGClassGrid">';
        $retval .= "<div id='classGridDiv' class='classGridDiv ".
        $grid."ClassGrid tableDiv'><table align='center' class='class_grid".
                $grid."FixedTables' id='CGClassGridTable'>";
        
		//we will reuse the header at the bottom of the table.
        
		$headerObj = $this->get_class_grid_header($grid);
		$header = $headerObj->header;	
		$retval .= $header;
		$retval .= "<tbody>";
        
        $retval .= $this->get_class_grid_data($advancedMode, $editing);
        
        $retval .= "</tbody>";
        $retval .= "<tfoot></tfoot>";
        $retval .= "</table>";
        
        
        $retval .= "</div>";
        $retval .= '</div>';
        $retval .= '</div>';////end overall subject wrapper
                
        return $retval;

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
        
        $configColumns = get_config('bcgt','cggridcolumns');
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
        }
        foreach($columns AS $column)
        {
            $width = ($column != 'picture') ? 110 : 50;
            $header .= "<th style='width:{$width}px;min-width:{$width}px;'>";
            $header .= get_string(trim($column), 'block_bcgt');
            $header .= "</th>";
        }
        
        $header .= '<th style="width:100px;min-width:100px;">';
        $header .= get_string('award', 'block_bcgt');
        $header .= '</th>';
        
        foreach($units AS $unit)
        {
            $header .= "<th style='width:150px;min-width:150px;'>";
            $header .= $unit->get_display_name();
            $header .= "</th>";
        }
        $header .= "</tr></thead>";
		$headerObj->header = $header;
		return $headerObj;
    }
    
    
    
     public function get_class_grid_data($advancedMode, $editing)
    {
        //get the units
        //get the students
        //do it by paging. 
        
        $output = "";
        
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
                    
                    $output .= "<tr>";
                    
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
                    $output .= $this->build_class_grid_students_details($student, $this->id, 
                            $context);

                    //now we need to do for each unit
                    //if its edit
                    //then a drop down to change
                    //else if its not then just output

                    foreach($units = $this->units AS $unit)
                    {
                        
                        $output .= "<td style='width:150px;min-width:150px;'>";
                        
                        if(array_key_exists($unit->get_id(), $usersUnits) && $usersUnits[$unit->get_id()]->is_student_doing())
                        {
                                                        
                            $userUnit = $usersUnits[$unit->get_id()];
                            //then the user is on this unit for sure, 
                            $userAwardString = '-';
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
                                $output .= '<select qual="'.$this->id.'" id="unitAwardS_'.$student->id.'_u_'.$unit->get_id().'" class="unitAward" qualID="'.$this->id.'" studentID="'.$student->id.'" unitID="'.$userUnit->get_id().'">';
                                $output .= '<option value="-1"> </option>';
                                foreach($this->unitAwards AS $award)
                                {
                                    $selected = '';
                                    if($userAwardID == $award->id)
                                    {
                                        $selected = 'selected="selected"';
                                    }
                                    $output .= '<option '.$selected.' value="'.$award->id.'">'.$award->award.'</option>';
                                }
                                $output .= '</select>';
                            }
                            else
                            { 
                                //just output the award
                                $output .= $userAwardString;
                            }
                        }
                        else
                        {
                            $output .= '<span class="usernotonunit"></span>';
                        }
                        
                        $output .= "</td>";
                        
                    }//end for each unit
                    
                    $output .= "</tr>";
                    
//                }
            }//end for each student
            
        }
        return $output;
    }
    
    
    
    
    protected function build_class_grid_students_details($student, $qualID, $context)
    {
        //now to build up the columns that contain the students.
        global $CFG, $printGrid, $OUTPUT;
        
        $output = "";
		   
        //columns supported are:
        //picture,username,name,firstname,lastname,email
        $columns = $this->defaultColumns;
        $configColumns = get_config('bcgt','cggridcolumns');
        //need to get the global config record
        
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
        }
        foreach($columns AS $column)
        {
            
            $width = ($column != 'picture') ? 110 : 50;
            $output .= "<td style='width:{$width}px;min-width:{$width}px;'>";
            
            if ($column == 'username' || $column == 'name'){
                $output .= "<a href='{$CFG->wwwroot}/blocks/bcgt/grids/student_grid.php?sID={$student->id}&qID={$this->id}' target='_blank'>";
            }

            switch(trim($column))
            {
                case("picture"):
                    $output .= $OUTPUT->user_picture($student, array('size' => 25));
                    break;
                case("username"):
                    $output .= $student->username;
                    break;
                case("name"):
                    $output .= fullname($student);
                    break;
                case("firstname"):
                    $output .= $student->firstname;
                    break;
                case("lastname"):
                    $output .= $student->lastname;
                    break;
                case("email"):
                    $output .= $student->email;
                    break;
            }
            
            if ($column == 'username' || $column == 'name'){
                $output .= "</a>";
            }
                        
//            if ($column == "username")
//            {
//                $content .= "&nbsp;<img src='".$CFG->wwwroot."/blocks/bcgt/pix/info.png' class='studentUnitInfo' qualID='{$qualID}' studentID='{$student->id}' unitID='{$this->get_id()}' />";
//            }
            
            $output .= "</td>";
            
        }
		$qualAward = "N/A";

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
        $output .= "<td style='width:100px;min-width:100px;'>".$qualAward."</td>";
		
		return $output;
    }
        
    
    
    
    
    public function display_activity_grid($activities)
    {
        global $COURSE, $PAGE, $CFG;
        
        $retval = '<div>';
        
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
                
            $retval .= "<div class='c'>";

                $retval .= "<input type='button' id='viewsimple' class='btn' value='View Simple' />";
                $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                $retval .= "<input type='button' id='viewadvanced' class='btn' value='View Advanced' />";                
                
                if (has_capability('block/bcgt:manageactivitylinks', $context)){
                    
                    $retval .= "<br><br>";
                    $retval .= "<input type='button' id='editsimple' class='btn' value='Edit Simple' />";
                    $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                    $retval .= "<input type='button' id='editadvanced' class='btn' value='Edit Advanced' />";                

                    $retval .= "<br><br>";
                    $retval .= "<a href='#' onclick='toggleAddComments();return false;'><input id='toggleCommentsButton' type='button' class='btn' value='".get_string('addcomment', 'block_bcgt')."' disabled='disabled' /></a>";
                
                }
                    
            $retval .= "</div>";

        $late = optional_param('late', false, PARAM_BOOL);
        $grid = optional_param('g', 's', PARAM_TEXT);
        $retval .= '<input type="hidden" id="grid" name="g" value="'.$grid.'"/>';
        $editing = false;
        if($grid == 'ae' || $grid == 'se')
        {
            $editing = true;
        }
        $advancedMode = ($grid == 'a' || $grid == 'ae') ? true : false;
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
            $studentsArray = bcgt_get_users_on_coursemodules($this->id, $scID, $groupingID, $cmID);
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
            $retval .= '<li><a target="_blank" href="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/ajax/get_act_grid.php?qID='.$this->id.'&g='.$grid.'&page='.$page.'&cmID='.$cmID.'&html=true">'.get_string('ajaxrequest', 'block_bcgt').'</a></li>';
            $retval .= '</ul>';

        }
        //we need to work out how many columns are being locked and
        //what the widths are
        //default is columns (assignments, comments)
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

        $jsModule = array(
            'name'     => 'mod_bcgtcg',
            'fullpath' => '/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        $PAGE->requires->js_init_call('M.mod_bcgtcg.initactgrid', array($this->id, $grid, $scID, $groupingID, $cmID, $columnsLocked), true, $jsModule);
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        load_javascript(true);
        $retval .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/css/start/jquery-ui-1.10.3.custom.min.css' />";

        $retval .= CGQualification::get_grid_key();
        
        $retval .= "<br><br>";
        $retval .= "<p id='loading' class='c'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/ajax-loader.gif' alt='loading...' /></p>";
        
        //the grid -> ajax
        $retval .= '<div id="cgActGrid">';
        
        $retval .= "<div id='actGridDiv' class='actGridDiv ".
        $grid."ActGrid tableDiv'>";
        
        
        $headerObj = $this->get_act_grid_header($activities, $grid);
        
        $retval .= "<table style='table-layout:fixed;margin-bottom:0px;padding:0px;'>";
            $retval .= $headerObj->fixedHeader;
        $retval .= "</table>";
        
        $retval .= "<table align='center' class='act_grid".
                $grid."FixedTables' id='CGActGridTable'>";
        
		$retval .= $headerObj->header;
        
		$retval .= "<tbody>";
        
        $retval .= $this->get_act_grid_data($advancedMode, $editing);
        
        $retval .= "</tbody>";
        $retval .= "<tfoot></tfoot>";
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
    
    public function set_grid_disabled($disabled)
    {
        $this->gridLocked = $disabled;
    }
    
    protected function build_unit_grid_students_details($student, $qualID, $context)
	{
		global $CFG, $printGrid, $OUTPUT;
		   
        $output = "";
        
        //columns supported are:
        //picture,username,name,firstname,lastname,email
        
        $columns = $this->defaultColumns;
        $configColumns = get_config('bcgt','cggridcolumns');
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
        }
        
        $link = $CFG->wwwroot.'/blocks/bcgt/grids/student_grid.php?qID='.$qualID.'&sID='.$student->id;  
        //need to get the global config record
        
        
        foreach($columns AS $column)
        {
            $output .= "<td style='width:100px;min-width:100px;'>"; 
            $output .= '<a href="'.$link.'" class="studentUnit" title="" id="sID_'.
                    $student->id.'_qID_'.$qualID.'">';
            switch(trim($column))
            {
                case("picture"):
                    $output .= $OUTPUT->user_picture($student, array('size' => 25));
                    break;
                case("username"):
                    $output .= $student->username;
                    break;
                case("name"):
                    $output .= fullname($student);
                    break;
                case("firstname"):
                    $output .= $student->firstname;
                    break;
                case("lastname"):
                    $output .= $student->lastname;
                    break;
                case("email"):
                    $output .= $student->email;
                    break;
            }
            
            $output .= '</a>';
            $output .= "</td>";
            
        }
		
		return $output;	
	}
    
    
    protected function get_act_grid_header($activities, $grid)
    {

        global $CFG;

        $cID = optional_param('cID', -1, PARAM_INT);
        $courseID = optional_param('scID', -1, PARAM_INT);
        $groupID = optional_param('grID', -1, PARAM_INT);
        
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
        
        $fixedHeader = "";
        $header = "";
        
        $fixedHeader .= "<thead>";
        $header .= "<thead>";
        
            $fixedHeader .= "<tr>";
                
                $columns = $this->defaultColumns;
                $configColumns = get_config('bcgt','cggridcolumns');
                if($configColumns)
                {
                    $columns = explode(",", $configColumns);
                }
            
                if ($columns)
                {
                    foreach($columns as $column)
                    {
                        $fixedHeader .= "<th style='width:100px;min-width:100px;'></th>";
                    }
                }
                
                $modIcons = load_mod_icons($courseID, $this->id, $groupID, -1, -1);
                $modLinking = load_bcgt_mod_linking();
                $qual = new stdClass();
                $qual->id = $this->id;
                
                $unitArray = array();
                $criteriaArray = array();
                
                if ($activities)
                {
                    foreach($activities AS $activity)
                    {
                        
                        $dueDate = get_bcgt_mod_due_date($activity->id, $activity->instanceid, $activity->cmodule, $modLinking);
                        $activity->dueDate = $dueDate;
                        $activityUnits = bcgt_get_mod_unit_criteria($courseID, $this->id, $groupID, $activity->id); 

                        $unitArray[$activity->id] = array();
                        
                        $cnt = 0;
                        
                        foreach($activityUnits AS $unitID => $criteria)
                        {
                            
                            $unitObj = $this->units[$unitID];
                            $unitArray[$activity->id][$unitObj->get_id()] = array('name' => $unitObj->get_name(), 'cnt' => count($criteria));
                            $cnt += count($criteria);

                            require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
                            $criteriaSorter = new CGCriteriaSorter();
                            uasort($criteria, array($criteriaSorter, "ComparisonSimple"));
                            
                            $criteriaArray[$unitID] = array();
                            
                            foreach($criteria AS $criteriaID => $criteriaName)
                            {
                                $criteriaArray[$unitID][$criteriaID] = $criteriaName;
                            }
                            
                        }
                        
                        
                        
                        
                        
                        $icon = false;
                        if(array_key_exists($activity->module,$modIcons))
                        {
                            $icon = $modIcons[$activity->module];
                        }
                        
                        $colspan = count($unitArray[$activity->id]);
                        
                        $fixedHeader .= "<th class='activityName' colspan='{$colspan}'>";
                        
                            $fixedHeader .= "<a href='{$CFG->wwwroot}/blocks/bcgt/forms/add_activity.php?page=addunit&aID={$activity->id}&cID={$cID}' target='_blank'>";
                        
                            if ($icon)
                            {
                                $fixedHeader .= "<img src='{$icon}' alt='{$activity->module}' class='bcgtmodcriticon activityicon' /> ";
                            }
                            
                            $fixedHeader .= "<span class='activityname'>{$activity->name}</span>";
                            
                            $fixedHeader .= "</a>";
                            
                            if ($activity->dueDate)
                            {
                                $fixedHeader .= "<br>";
                                $fixedHeader .= "<small class='activityduedate'>".date('d M Y, H:i', $activity->dueDate)."</small>";
                            }
                        
                        $fixedHeader .= "</th>";
                        
                        $activity->unitcriteria = $activityUnits;
                        
                    }
                }
                                
            $fixedHeader .= "</tr>";
            
                                  
            
            // Units
            $fixedHeader .= "<tr>";
            
            for ($i = 0; $i < count($columns); $i++)
            {
                $fixedHeader .= "<th style='width:100px;min-width:100px;'></th>";
            }
            
            if ($unitArray)
            {
                foreach($unitArray as $activityID => $units)
                {
                    if ($units)
                    {
                                                
                        foreach($units as $unitID => $unit)
                        {
                            $fixedHeader .= "<th class='activityUnitName' unitID='{$unitID}'>{$unit['name']}</th>";
                        }
                    }
                }
            }
            
            $fixedHeader .= "</tr>";
            
            
            
            
            
            
            // Criteria
            $header .= "<tr>";
                        
            if ($columns)
            {
                foreach($columns as $column)
                {
                    $header .= "<th style='width:100px;min-width:100px;'>".get_string(trim($column), 'block_bcgt')."</th>";
                }
            }
            
            if ($unitArray)
            {
                foreach($unitArray as $activityID => $units)
                {
                    if ($units)
                    {
                        foreach($units as $unitID => $unit)
                        {
                            $criteria = $criteriaArray[$unitID];
                            if ($criteria)
                            {
                                
                                $cnt = count($criteria);
                                $width = round( (200 / $cnt), 1 );
                                
                                foreach($criteria as $criterion)
                                {
                                    $header .= "<th class='criteriaName criterionUnit_{$unitID}'>{$criterion}</th>";
                                }
                            }
                        }
                    }
                }
            }
            
            $header .= "</tr>";
            
        
        $header .= "</thead>";
        
        $this->activities = $activities;
        $obj = new stdClass();
        $obj->fixedHeader = $fixedHeader;
        $obj->header = $header;
        
        return $obj;
        
        /*
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
            $activityUnitRow = '<tr class="actUnitRow">';
            $hasActUnits = false;
            $activityCritRow = '<tr  class="actCritRow">';
            $hasActCrits = false;
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
                $hasActUnits = true;
                $unitObj = $this->units[$unitID];
                $activityUnitRow .= '<th role="columnheader" colspan="'.count($criterias).'">'.$unitObj->get_name().'</th>';
                $totalHeaderCount++;
                
                require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
                $criteriaSorter = new CGCriteriaSorter();
                uasort($criterias, array($criteriaSorter, "ComparisonSimple"));

                foreach($criterias AS $criteriaID => $criteriaName)
                {
                    $hasActCrits = true;
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
                
        if ($hasActUnits){
            $header .= $activityUnitRow;
        }
        
        if ($hasActCrits){
            $header .= $activityCritRow;
        }
        
        $header .= "</thead>";
		$headerObj->header = $header;
        
        //need to work out this one for the activities. 
        
		$headerObj->criteriaCountArray = $criteriaCountArray;
		//$headerObj->orderedCriteriaNames = $criteriaNames;
        $headerObj->totalHeaderCount = $totalHeaderCount;
		return $headerObj;
         * 
         */
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
        
        global $CFG, $DB, $COURSE;

        $output = "";
        
        $studentsArray = $this->students;
        $activities = $this->activities;
        
        //pagig
        $pageNumber = optional_param('page',1,PARAM_INT);
        $context = context_course::instance($COURSE->id);
        $courseID = optional_param('cID', -1, PARAM_INT);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }        
        //need to get the order:
        $order = optional_param('order', 'act', PARAM_TEXT);
        
        $criteriaNames = $this->get_used_criteria_names();
        //Get this units criteria names and sort them. 
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
        $criteriaSorter = new CGCriteriaSorter();
        usort($criteriaNames, array($criteriaSorter, "ComparisonSimple"));
        
        $retArray = array();
        $possibleValues = null;
        if(!isset($this->possibleValues) && $editing && $advancedMode)
        {
            $possibleValues = CGQualification::get_possible_values(CGQualification::ID);
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
            
            $output .= "<tr>";
                        
            $rowCount++;
            $countProcessed = 0;
            
            $output .= $this->build_unit_grid_students_details($student, $this->id, $context);
            
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
                        $cnt = count($criterias);
                        foreach($criterias AS $criteriaID => $criteriaName)
                        {
                            $userCriteria = $userUnit->get_single_criteria($criteriaID);
                            $c = ($editing) ? 'Edit' : 'NonEdit';
                            $output .= "<td class='criteriaValue{$c} criteriaCell'>".$userCriteria->get_grid_td($editing, $advancedMode, $userUnit, $student, $this, 'unit')."</td>";
                        }
                    }
                    else
                    {
                        //user isnt doing unit
                        //but we still need to ouput empty
                        $cnt = count($criterias);
                        foreach($criterias AS $criteria)
                        {
                            $output .= "<td class='criteriaCell'></td>";
                        }
                    }
                }
                if(count($activities) != $countProcessed)
                {
                    $output .= "<td></td>";
                }
            }
            $output .= "</tr>";
        }
        return $output;
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
		$header .= "<thead><tr>";
        $header .= "<th class='unitCommentCell'></th>";
                $header .= "<th style='width:200px;min-width:200px;'>Unit (Total Credits: $totalCredits)</th>";
                $totalCellCount = 3;
		if($studentView)
		{//if its not student view then we are looking at just
			//the qual in general rather than a student.
			$header .= "<th style='width:110px;min-width:110px;'>Award</th>";
            $totalCellCount++;
            
//            $header .= "<th class='points'>Points</th>";
//            $totalCellCount++;
            
            // If qual has % completions enabled
            if($this->has_percentage_completions() && !$printGrid && $studentView){
                $header .= "<th style='width:110px;min-width:110px;'>% Complete</th>";
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
    
    protected function get_simple_grid_header($criteriaNames)
    {
        $header = '';
        $header .= "<thead><tr>";
        $header .= '<th></th><th></th>';
        $header .= '<th>'.get_string('unit', 'block_bcgt').'</th>';
        
        $headerObj = self::get_criteria_headers($criteriaNames, false, false, false);
        $header .= $headerObj->subHeader;
        
        $header .= "</tr></thead>";
        return $header;
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
    
    
    public static function get_grid_image($valueObj)
    {
        
        global $CFG, $DB;
        
        $image = false;
                
        if (is_null($valueObj))
        {
            
            $foundInDB = false;
            $shortValue = "N/A";
            $longValue = "Not Attempted";
            
            // Is there an image defined in the DB for this? As we may or may not have it as a value record
            $check = $DB->get_record("block_bcgt_value", array("bcgttypeid" => self::ID, "shortvalue" => $shortValue, "enabled" => "1.0"));
            if ($check)
            {
               $setting = $DB->get_record("block_bcgt_value_settings", array("bcgtvalueid" => $check->id));
               if ($setting)
               {
                   // Is there a custom image?
                    if ($setting->customimg && file_exists($CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg' . $setting->customimg))
                    {
                        $image = $CFG->wwwroot . '/blocks/bcgt/plugins/bcgtcg' . $setting->customimg;
                        $foundInDB = true;
                    }

                    // Is there a core image defined instead?
                    elseif ($setting->coreimg && file_exists($CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg' . $setting->coreimg))
                    {
                        $image = $CFG->wwwroot . '/blocks/bcgt/plugins/bcgtcg' . $setting->coreimg;
                        $foundInDB = true;
                    }
               }
            }
            
            if (!$foundInDB)
            {
                
                // Default img?
                if (CGQualification::get_default_value_image($shortValue) != false ){
                    $image = $CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/'.CGQualification::get_default_value_image($shortValue);
                } 

                // It's broken
                else {
                    $image = $CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/icon_NoIcon.png';
                } 
            
            }
            
        }
        else
        {
            
            // If valueObj then grid cell
            if ($valueObj instanceof Value)
            {
                $shortValue = $valueObj->get_short_value();
                $longValue = $valueObj->get_value();
                $customImg = $valueObj->get_custom_image();
                $coreImg = $valueObj->get_core_image();
            }
            
            // Else, grid key - stdClass
            else
            {
                $shortValue = $valueObj->shortvalue;
                $longValue = $valueObj->value;
                $customImg = $valueObj->customimg;
                $coreImg = $valueObj->coreimg;
            }
            
            
            
            // Is there a custom image?
            if ($customImg && file_exists($CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg' . $customImg))
            {
                $image = $CFG->wwwroot . '/blocks/bcgt/plugins/bcgtcg' . $customImg;
            }
            
            // Is there a core image defined instead?
            elseif ($coreImg && file_exists($CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg' . $coreImg))
            {
                $image = $CFG->wwwroot . '/blocks/bcgt/plugins/bcgtcg' . $coreImg;
            }
            
            // Is there a hard-coded default?
            elseif (CGQualification::get_default_value_image($shortValue) != false ){
                $image = $CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/'.CGQualification::get_default_value_image($shortValue);
            } 
            
            // It's broken
            else {
                $image = $CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/icon_NoIcon.png';
            }
            
        }
        
        $class = $class = 'stuValue'.$shortValue;
        
        $obj = new stdClass();
        $obj->image = $image;
        $obj->class = $class;
        $obj->title = $longValue;
        
        return $obj;
        
        
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
        if (file_exists( str_replace($CFG->wwwroot, $CFG->dirroot, $img) )){
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
    
    
    
    public function export_class_grid()
    {
                
        global $CFG, $DB, $USER;
        
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
                     ->setCreator(fullname($USER))
                     ->setLastModifiedBy(fullname($USER))
                     ->setTitle($this->get_display_name())
                     ->setSubject($this->get_display_name())
                     ->setDescription($this->get_display_name() . " generated by Moodle Grade Tracker");

        $sheetIndex = 0;
        
        
        // Get possible values
        $possibleValues = $this->get_possible_values( self::ID );
        $possibleValuesArray = array();
        foreach($possibleValues as $possibleValue){
            $possibleValuesArray[] = $possibleValue->shortvalue;
        }
        
        // Have a worksheet for each unit
        $units = $this->get_units();
        
        require_once $CFG->dirroot . '/blocks/bcgt/classes/sorters/UnitSorter.class.php';
        $unitSorter = new UnitSorter();
        usort($units, array($unitSorter, "ComparisonDelegateByType"));
        
        // Params for loading student info
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
        
        require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php';
        
        if ($units)
        {
            
            foreach($units as $unit)
            {
                
                $title = "(".$unit->get_id() . ") ";
                $unitName = preg_replace("/[^a-z 0-9]/i", "", $unit->get_name());
                $cnt = strlen($title);
                $diff = 30 - $cnt;
                $title .= substr($unitName, 0, $diff);
                
                
                // Set current sheet
                $objPHPExcel->createSheet($sheetIndex);
                $objPHPExcel->setActiveSheetIndex($sheetIndex);
                $objPHPExcel->getActiveSheet()->setTitle($title);
                
                $rowNum = 1;
                
                // Headers
                $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", "Username");
                $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", "First name");
                $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", "Last name");                

                // Overall unit award
                $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", "Award");
                
                $letter = 'E';
                
                // Get list of criteria on this unit
                $criteria = $unit->get_used_criteria_names();
                
                // Sort
                $criteriaSorter = new CriteriaSorter();
                usort($criteria, array($criteriaSorter, "ComparisonSimple"));

                if ($criteria)
                {
                    foreach($criteria as $criterion)
                    {
                        $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", $criterion);
                        $letter++;
                    }
                }
                
                $rowNum++;
                
                // Get all the students on this unit
                $students = get_users_on_unit_qual($unit->get_id(), $this->id);
                
                if ($students)
                {
                    
                    foreach($students as $student)
                    {
                        
                        $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", $student->username);
                        $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", $student->firstname);
                        $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", $student->lastname);
                        
                        // Load student into unit
                        $unit->load_student_information($student->id, $this->id, $loadParams);
                        
                        $userAward = $unit->get_user_award();
                        $award = '-';
                        if($userAward)
                        {
                            $award = $userAward->get_award();
                        }
                        
                        $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", $award);
                        
                        $letter = 'E';
                        
                        // Loop criteria
                        if ($criteria)
                        {
                            foreach($criteria as $criterion)
                            {
                                
                                $studentCriterion = $unit->get_single_criteria(-1, $criterion);
                                if ($studentCriterion)
                                {
                                    $shortValue = 'N/A';
                                    $studentValueObj = $studentCriterion->get_student_value();	
                                    if ($studentValueObj){
                                        $shortValue = $studentValueObj->get_short_value();
                                        if($studentValueObj->get_custom_short_value())
                                        {
                                            $shortValue = $studentValueObj->get_custom_short_value();
                                        }
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
                                    $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", "-");
                                }
                                
                                $letter++;
                                
                            }
                        }
                                                
                        $rowNum++;
                        
                    }
                    
                }
                
                // Freeze top & first 3 columns (everything to the left of D and above 2)
                $objPHPExcel->getActiveSheet()->freezePane('D2');
                
                $objPHPExcel->getActiveSheet()->getStyle("E2:{$letter}{$rowNum}")->getProtection()
                                              ->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                
                $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
                                                
                // Increment sheet index
                $sheetIndex++;
                
            }
            
        }
        
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        ob_clean();
        $objWriter->save('php://output');
        exit;                
        
    }
    
    
    
    
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
        
        // Remove default sheet
        $objPHPExcel->removeSheetByIndex(0);
        
        // Style for blank cells - criteria not on that unit
        $blankCellStyle = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'E0E0E0')
            )
        );

        $sheetIndex = 0;
        
                
        // Have a worksheet for each unit
        $units = $this->get_units();
        
        $unitSorter = new UnitSorter();
        usort($units, array($unitSorter, "ComparisonDelegateByType"));
        
        $criteria = $this->get_used_criteria_names();
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
        $criteriaSorter = new CGCriteriaSorter();
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
                $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", $unit->get_display_name());
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
                                $metArray = $studentCriterion->get_met_values();  
                                $nonMetArray = $studentCriterion->get_non_met_values();   

                                $possibleValuesArray = array('N/A');
                                if ($metArray){
                                    foreach($metArray as $value){
                                        $possibleValuesArray[] = $value->shortvalue;
                                    }
                                }

                                if ($nonMetArray){
                                    foreach($nonMetArray as $value){
                                        $possibleValuesArray[] = $value->shortvalue;
                                    }
                                }
                                
                                $shortValue = 'N/A';
                                $studentValueObj = $studentCriterion->get_student_value();	
                                if ($studentValueObj){
                                    $shortValue = $studentValueObj->get_short_value();
                                    if($studentValueObj->get_custom_short_value())
                                    {
                                        $shortValue = $studentValueObj->get_custom_short_value();
                                    }
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
        
        
        // Now comments worksheet
        
        // Set current sheet
        $sheetIndex = 1;
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
                $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", $unit->get_display_name());
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
                        
                            $output .= "attempting to set value for criterion {$criteriaName} to {$value} ... ";

                            if ($studentCriterion)
                            {

                                // Get possible values
                                $metArray = $studentCriterion->get_met_values();  
                                $nonMetArray = $studentCriterion->get_non_met_values();   

                                $possibleValuesArray = array();
                                $possibleValuesArray[-1] = 'N/A';
                                if ($metArray){
                                    foreach($metArray as $val){
                                        $possibleValuesArray[$val->id] = $val->shortvalue;
                                    }
                                }

                                if ($nonMetArray){
                                    foreach($nonMetArray as $val){
                                        $possibleValuesArray[$val->id] = $val->shortvalue;
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
            $output .= "end of process - {$cnt} criteria updated<br>";
            
            
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
                            $value = $DB->get_record("block_bcgt_value", array("id" => $update->bcgtvalueid));
                            if ($update->dateupdated > $update->dateset){
                                $updateTime = $update->dateupdated;
                                $updateUser = $DB->get_record("user", array("id" => $update->updatedbyuserid));
                            } else {
                                $updateTime = $update->dateset;
                                $updateUser = $DB->get_record("user", array("id" => $update->setbyuserid));
                            }
                                                       
                            $output .= $unit->get_name() . " (" . $critRecord->name . ") was updated to: " . $value->value . ", at: " . date('d-m-Y, H:i', $updateTime) . ", by: ".fullname($updateUser)." ({$updateUser->username})<br>";
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

                                $value = $cellValue;
                                
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
                                    
                                    if ($currentValue != $value){
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
                
                // Unit name
                $objPHPExcel->getActiveSheet()->setCellValue("A1", "Unit Name");
                $objPHPExcel->getActiveSheet()->setCellValue("B1", $unit->get_name());
                
                // Unit code
                $objPHPExcel->getActiveSheet()->setCellValue("A2", "Unit Code");
                $objPHPExcel->getActiveSheet()->setCellValue("B2", $unit->get_uniqueID());
                
                // Unit details
                $objPHPExcel->getActiveSheet()->setCellValue("A3", "Unit Details");
                $objPHPExcel->getActiveSheet()->setCellValue("B3", $unit->get_details());
                
                // Unit level
                $objPHPExcel->getActiveSheet()->setCellValue("A4", "Unit Level");
                $objPHPExcel->getActiveSheet()->setCellValue("B4", $unit->get_level()->get_level());
                
                // Unit credits
                $objPHPExcel->getActiveSheet()->setCellValue("A5", "Unit Credits");
                $objPHPExcel->getActiveSheet()->setCellValue("B5", $unit->get_credits());
                
                // Unit weighting
                $objPHPExcel->getActiveSheet()->setCellValue("A6", "Unit Weighting");
                $objPHPExcel->getActiveSheet()->setCellValue("B6", $unit->get_weighting());
                
                // Unit grading
                $objPHPExcel->getActiveSheet()->setCellValue("A7", "Unit Grading");
                $objPHPExcel->getActiveSheet()->setCellValue("B7", $unit->get_grading());
                
                                
                // Criteria headers
                $objPHPExcel->getActiveSheet()->setCellValue("A8", "Criteria Names");
                $objPHPExcel->getActiveSheet()->setCellValue("B8", "Criteria Details");
                $objPHPExcel->getActiveSheet()->setCellValue("C8", "Criteria Weighting");
                $objPHPExcel->getActiveSheet()->setCellValue("D8", "Criteria Grading");
                $objPHPExcel->getActiveSheet()->setCellValue("E8", "Criteria Parent");
                
                $criteria = $this->sort_criteria(null, $unit->get_criteria());
                
                $rowNum = 9;
                
                if ($criteria)
                {
                    foreach($criteria as $criterion)
                    {

                        $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", $criterion->get_name());
                        $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", $criterion->get_details());
                        $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", $criterion->get_weighting());
                        $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", $criterion->get_grading());
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
    
    
    
    
}