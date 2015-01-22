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
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Unit.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/lib.php');
class BTECUnit extends Unit{
    //put your code here
    
    const L1CRITERIANAME = 'L1_';
    const PASSCRITERIANAME = 'P';
	const MERITCRITERIANAME = 'M';
	const DISTINCTIONCRITERIANAME = 'D';
    const L1CRITERIAPOSS = 30;
    const PASSCRITERIAPOSS = 30;
    const MERITCRITERIAPOSS = 30;
    const DISSCRITERIAPOSS = 30;
    const DEFAULTUNITCREDITSNAME = 'DEFAULT_UNIT_LEVEL_CREDITS';
	protected $credits;
    protected $defaultColumns = array('picture', 'username', 'name');
    protected $gridLocked;
    
    public function BTECUnit($unitID, $params, $loadParams)
    {
        parent::Unit($unitID, $params, $loadParams);
        if($unitID != -1)
		{
			$creditsObj = BTECUnit::retrieve_credits($unitID);
			if($creditsObj)
			{
                if($creditsObj->credits >= 0)
                {
                    $this->credits = $creditsObj->credits; 
                }
                else
                {
                    //get default credits for this object if we can
                    $defaultCredits = $this->get_default_credits();
                    $this->credits = $defaultCredits;
                }
			}
		}
		elseif($params)
		{
            if(isset($params->credits) && $params->credits >= 0)
            {
                $this->credits = $params->credits;
            }
            else
            {
                $defaultCredits = $this->get_default_credits();
                $this->credits = $defaultCredits;
            }
		}
        else 
        {
            $defaultCredits = $this->get_default_credits();
            $this->credits = $defaultCredits;
        }
    }
    
    public function get_typeID()
	{
		return BTECQualification::ID;
	}
	
	public function get_type_name()
	{
		return BTECQualification::NAME;
	}
    
    public function get_family_name()
    {
        return BTECQualification::NAME; 
    }
	
	public function get_familyID()
	{
		return BTECQualification::FAMILYID;
	}
	
	public function get_credits()
	{
        if(isset($this->credits) && $this->credits >= 0)
        {
            return $this->credits;
        }
        return $this->get_default_credits();
	}
        
    public static function get_edit_form_menu($disabled = '', $unitID = -1)
	{
        $jsModule = array(
            'name'     => 'mod_bcgtbtec',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        global $PAGE;
        $PAGE->requires->js_init_call('M.mod_bcgtbtec.bteciniteditunit', null, true, $jsModule);
        $specID = optional_param('spec', -1, PARAM_INT);
		$levelID = optional_param('level', -1, PARAM_INT);
		$subTypeID = optional_param('subtype', -1, PARAM_INT);
		$retval = "";
		$levels = get_level_from_type(-1, BTECQualification::FAMILYID);
        $types = bcgt_get_types(-1, BTECQualification::FAMILYID, 'specificationdesc ASC');
		if(count($levels) == 1)
		{
			$level = end($levels);
			$levelID = $level->get_id();
		}
		if(($levelID == -1 || $levelID == '') && $unitID != -1)
		{
			$levelID = BTECUnit::get_unit_level($unitID);
		}
		if($levelID == '')
		{
			$levelID = -1;
		}
		if($subTypeID == -1 && $unitID != -1)
		{
			$subTypeID = BTECUnit::get_unit_subtype($unitID);
		}
        $qualTypeID = -1;
        if($unitID)
        {
            global $DB;
            //then we need to know what type it was:
            $unitDB = $DB->get_record_sql("SELECT * FROM {block_bcgt_unit} WHERE id = ?", array($unitID));
            if($unitDB)
            {
                $qualTypeID = $unitDB->bcgttypeid;
            }
        }
		$subTypes = get_subtype_from_type(-1, $levelID, BTECQualification::FAMILYID);
		
        $retval .= '<div class="inputContainer"><div class="inputLeft">'.
                '<label for="spec"><span class="required">*</span>'.
                get_string('specification','block_bcgt').'</label></div>';
			$retval .= '<div class="inputRight"><select '.$disabled.' name="spec" id="spec">';
				if($types)
				{
					if(count($types) > 1)
					{
						$retval .= '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>';
					}				
					foreach($types as $spec) {
                        $selected = '';
                        if(($specID != -1 && ($specID == $spec->id)) || $qualTypeID == $spec->id)
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
                        $retval .= '<option '.$selected.' value="'.$spec->id.'"'.
                                '>'.$spec->specificationdesc.'</option>';
					}	
				}
				else
				{
					$retval .= '<option value="">'.get_string('nospecs','block_bcgt').'</option>';
				}
			$retval .= '</select></div></div>';
        $retval .= '<div class="inputContainer"><div class="inputLeft">'.
                '<label for="level"><span class="required">*</span>'.
                get_string('level','block_bcgt').'</label></div>';
			$retval .= '<div class="inputRight"><select name="level" id="level">';
				if($levels)
				{
					if(count($levels) > 1)
					{
						$retval .= '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>';
					}				
					foreach($levels as $level) {
                        $selected = '';
                        if($levelID != -1 && ($levelID == $level->get_id()))
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
                        $retval .= '<option '.$selected.' value="'.$level->get_id().'"'.
                                '>'.$level->get_level().'</option>';
					}	
				}
				else
				{
					$retval .= '<option value="">'.get_string('noslevels','block_bcgt').'</option>';
				}
			$retval .= '</select></div></div>';
			
			if($levelID == Level::level3ID)
			{
				$retval .= '<p>'.get_string('btecsubtypedes','block_bcgt').'</p>';
				$retval .= '<div class="inputContainer"><div class="inputLeft">'.
                        '<label for="subtype"><span class="required">*</span>'.
                        get_string('subtype', 'block_bcgt').' : </label></div>';
				$retval .= '<div class="inputRight"><select name="subtype" id="subtype">';
				require_once('BTECSubType.class.php');
                if($subTypes)
				{
					$retval .= '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>';
					$selected = '';
					if($subTypeID == BTECSubType::BTECFndDipID)
					{
						$selected = 'selected';
					}
					$retval .= '<option '.$selected.' value="'.BTECSubType::BTECFndDipID.
                            '">'.get_string('foundationdiploma','block_bcgt').'</option>';
					$selected = '';
					if($subTypeID != BTECSubType::BTECFndDipID)
					{
						$selected = 'selected';
					}
					$retval .= '<option '.$selected.' value="-1">All Others</option>';
				}
				else
				{
					$retval .= '<option value="">'.get_string('nosubtypes','block_bcgt').'</option>';
				}
				$retval .= '</select></div></div>';	
			}	
		
		return $retval;
	}
    
    public static function get_pluggin_unit_class($typeID = -1, $unitID = -1, 
            $familyID = -1, $params = null, $loadLevel = Qualification::LOADLEVELUNITS) {
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
        if(!$params)
        {
            $params = new stdClass();
            $params->levelID = $levelID;
            $params->subTypeID = $subTypeID;
            $params->level = new Level($levelID);
        }
        switch($levelID)
        {
            case(Level::level1ID):
                return new BTECLowerUnit($unitID, $params, $loadLevel);
            case(level::level2ID):
            case(level::level3ID):
                require_once('BTECSubType.class.php');
                switch($subTypeID)
                {
                    case(BTECSubType::BTECFndDipID):
                        return new BTECFoundationUnit($unitID, $params, $loadLevel);	
                    default:
                        return new BTECUnit($unitID, $params, $loadLevel);
                }		
            case(Level::level4ID):	
            case(Level::level5ID): 
                return new BTECHigherUnit($unitID, $params, $loadLevel);	
            default:
                return new BTECUnit($unitID, $params, $loadLevel);
        }
        
    }
    
    /**
	 * Builds the table of the unit information that gets presented to the 
	 * user when they hover of the unit name. This is called through ajax and jquery.
	 */
	public function build_unit_details_table()
	{
        global $CFG;
		$retval = "<div id='unitName$this->id' class='tooltipContentT'>".
                "<div><h3>$this->name</h3>";
        
        $retval .= "<div>".bcgt_html($this->details, true)."</div><br>";
        
        if ($this->comments != ''){
            $retval .= "<br><div style='background-color:#FF9;'>".bcgt_html($this->comments, true)."</div><br>";
        }
        
        $retval .= "<table><tr><th>".get_string('criterianame','block_bcgt')."</th>".
                "<th>".get_string('criteriadetails','block_bcgt')."</th></tr>";
		if($criteriaList = $this->criterias)
		{
            require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/sorters/BTECCriteriaSorter.class.php');
            $criteriaSorter = new BTECCriteriaSorter();
            usort($criteriaList, array($criteriaSorter, "ComparisonDelegateByNameObject"));
			//Sort the criteria on P, M and then D
			foreach($criteriaList AS $criteria)
			{
				$retval .= "<tr><td>".$criteria->get_name()."</td><td>".$criteria->get_details()."</td></tr>";
			}
		}
		$retval .= "</table></div></div>";
		return $retval;
	}
    
    public static function get_instance($unitID, $params, $loadParams)
    {
        if(!$params || !isset($params->levelID))
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
                $params->levelID = $levelID;
            } 
        }
        return new BTECUnit($unitID, $params, $loadParams);
    }
    
    public function display_unit_grid()
    {
        return $this->display_unit_grid_btec(false);
    }
    
    
    public function display_unit_grid_btec($subCriteriaDisplay = false)
    {
        $subCriteriaDisplay;
        global $COURSE, $PAGE, $CFG, $USER;
                
        $courseID = optional_param('cID', -1, PARAM_INT);
        $sCourseID = optional_param('scID', -1, PARAM_INT);
        $groupingID = optional_param('grID', -1, PARAM_INT);
        $qualID = optional_param('qID', -1, PARAM_INT);
        $regGroupID = optional_param('regGrpID', false, PARAM_INT);
        $reload = optional_param('reload', false, PARAM_BOOL);
        
        if(has_capability('block/bcgt:viewallgrids', context_system::instance()))
        {
            $quals = $this->get_quals_on('',-1,-1,$courseID);
        }
        else
        {
            $quals = $this->get_quals_on_roles('', $USER->id, array('teacher', 'editingteacher'),$courseID);
        }
        if($qualID == -1 && $quals && count($quals == 1))
        {
            $qualID = end($quals)->id;
        }
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        $late = optional_param('late', false, PARAM_BOOL);
        $grid = optional_param('g', 's', PARAM_TEXT);
        $editing = (has_capability('block/bcgt:editunitgrid', $context) && in_array($grid, array('se', 'ae'))) ? true : false;
        $advancedMode = ($grid == 'a' || $grid == 'ae') ? true : false;
        $order = optional_param('order', 'btec', PARAM_TEXT);
        
        $jsModule = array(
            'name'     => 'mod_bcgtbtec',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
       
        
        // Work out how many columns to freeze
        $cols = 3;
        
        $configColumns = get_config('bcgt','btecgridcolumns');
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
        }
        
        $cols += count($columns);
        
        if(has_capability('block/bcgt:viewbtecavggrade', $context))
        {
            $cols++;
        }
                        
        $criteriaNames = $this->get_used_criteria_names();
        
        if($order == 'btec')
        {
            //Get this units criteria names and sort them. 
            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
            $criteriaSorter = new CriteriaSorter();
            usort($criteriaNames, array($criteriaSorter, "ComparisonDelegateByArrayNameLetters"));
        }
        elseif($order == 'act')
        {
            $activities = bcgt_unit_activities($sCourseID, $this->id, $qualID, $groupingID);
            $modLinking = load_bcgt_mod_linking();
            $this->modLinking = $modLinking;
            //get the due dates of the activities
            foreach($activities AS $activity)
            {
                $dueDate = get_bcgt_mod_due_date($activity->id, $activity->instanceid, $activity->cmodule, $modLinking);
                $activity->dueDate = $dueDate;
            }
            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ModSorter.class.php');
            $modSorter = new ModSorter();
            usort($activities, array($modSorter, "ComparisonDelegateByDueDateObj"));
            $this->activities = $activities;
        }
        
        
        $PAGE->requires->js_init_call('M.mod_bcgtbtec.initunitgrid', array($this->id, $qualID, $grid, $order, $cols), true, $jsModule);
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        load_javascript(true);
        
        $retval = "";
        $retval .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/css/start/jquery-ui-1.10.3.custom.min.css' />";
        
        $retval .= '<div>';
        
        $retval .= '<input type="hidden" id="grid" name="g" value="'.$grid.'"/>';   
        $retval .= "<input type='hidden' id='reggrpid' value='{$regGroupID}' />";
                
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
            $retval .= "<br><br>";
            
            if(has_capability('block/bcgt:viewbteclatetracking', $context))
            {

                $chk = ($late) ? 'checked' : '';
                $retval .= "<input type='checkbox' id='showlate' {$chk} > <label for='showlate'>Show Late History</label> &nbsp;&nbsp;&nbsp;&nbsp;";

            }
            
            //do we have any activities on this unit?
            if($activities = bcgt_unit_activities($sCourseID, $this->id, $qualID, $groupingID))
            {
                //then we have activities
                $retval .= '<label for="order">'.get_string('order', 'block_bcgt').' : </label>';
                $selected = '';
                if($order == 'btec')
                {
                    $selected = 'selected';
                }
                $retval .= '<select name="order" id="order">';
                $retval .= '<option '.$selected.' value="btec">'.get_string('orderbybtec','block_bcgt').'</option>';
                $selected = '';
                if($order == 'act')
                {
                    $selected = 'selected';
                }
                $retval .= '<option '.$selected.' value="act">'.get_string('orderbyactivity','block_bcgt').'</option>';
                $retval .= '</select>';
            }
            
            
            $page = optional_param('page', 1, PARAM_INT);
            $pageRecords = get_config('bcgt','pagingnumber');
            if($pageRecords != 0)
            {
                //then we are paging
                //need to count the total number of students and divide by the paging number
                //load the session object
                $studentsLoaded = false;
                if($qualID != -1)
                {
                    $sessionUnits = isset($_SESSION['session_unit'])? 
                    unserialize(urldecode($_SESSION['session_unit'])) : array();
                    if(array_key_exists($this->id, $sessionUnits))
                    {
                        $unitObject = $sessionUnits[$this->id];
                        $qualArray = array();
                        if(isset($unitObject->qualArray))
                        {
                            $qualArray = $unitObject->qualArray;
                        }
                        if(array_key_exists($qualID, $qualArray))
                        {
                            //what happens if a student has been added since?

                            //then this will return an array of students unit objects
                            //for this qualid for this unit.
                            $studentsArray = $qualArray[$qualID];
                            if(count($studentsArray) != 0)
                            {
                                $studentsLoaded = true;
                            }
                            //studentsArray[] is an object with two properties. The Unit Object with stu
                            //loaded and a few of the students information.
                        }    
                    }
                    else
                    {
                        $unitObject = new stdClass();
                        $qualArray = array();
                    }
                }
                elseif(isset($this->students))
                {
                    $studentsArray = $this->students;
                    $studentsLoaded = true;
                }
                if(!$studentsLoaded)
                {   
                    //load the students that are on this unit for this qual. 
                    if ($regGroupID){
                        $studentsArray = bcgt_get_register_group_users($regGroupID);
                    } else {
                        $studentsArray = get_users_on_unit_qual($this->id, $qualID, $sCourseID, $groupingID);
                    }
                    $this->students = $studentsArray;
                }
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
                $retval .= '<a target="_blank" href="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec/ajax/get_unit_grid.php?qID='.$qualID.'&uID='.$this->id.'&g='.$grid.'&page='.$page.'&scID='.$sCourseID.'&grID='.$groupingID.'&html=true&order='.$order.'">'.get_string('ajaxrequest', 'block_bcgt').'</a>';

            }
            
            
        $retval .= "</div>";
        
        $retval .= BTECQualification::get_grid_key();
        
        $retval .= "<br><br>";
        $retval .= "<p id='loading' class='c'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/ajax-loader.gif' alt='loading...' /></p>";
       
        $useMassUpdate = get_config('bcgt', 'usemassupdate');
        if ($useMassUpdate == 1 && $order == 'btec')
        {
        
            $retval .= "<div id='mass_value'>";
                $retval .= "<p><b>Mass Update</b></p>";
                $retval .= "Criterion: <select id='mass_value_crit_name'>";
                    $retval .= "<option value=''></option>";
                    if ($criteriaNames)
                    {
                        foreach($criteriaNames as $name)
                        {
                            $retval .= "<option value='{$name}'>{$name}</option>";
                        }
                    }
                $retval .= "</select>";
                $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                $retval .= "Value: <select id='mass_value_id'>";
                    $retval .= "<option value=''></option>";
                    $possibleValues = BTECQualification::get_possible_values(BTECQualification::ID);
                    if ($possibleValues)
                    {
                        foreach($possibleValues as $value)
                        {
                            $retval .= "<option value='{$value->id}'>{$value->shortvalue}</option>";
                        }
                    }
                $retval .= "</select>";
                $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                $retval .= "<input type='button' class='btn' style='padding:2px 4px;font-size:8pt;' id='do_mass_value_update' value='".get_string('update')."' />";
            $retval .= "</div>";
        
        }
       
        // If we want to reload the awards, do that now before we display it
        if ($reload && $this->students)
        {
            foreach($this->students as $stud)
            {
                $this->load_student_information($stud->id, $qualID);
                $this->calculate_unit_award($qualID);
            }
        }
        
        
        
        
        
        //the grid -> ajax
        $retval .= '<div id="BTECUnitGrid">';
        
        
        $retval .= "<div id='unitGridDiv' class='unitGridDiv ".
        $grid."UnitGrid tableDiv'>";
        
        
                
        
		$headerObj = $this->get_unit_grid_header($criteriaNames, 
                $subCriteriaDisplay, $grid, $context);
        
        
        
        // Fixed header, sinec this grid uses colspans and it fucks it up
        if ($order == 'act')
        {
            $retval .= "<div id='BTECUnitFixedHead'>";
                $retval .= "<table style='margin-bottom:0px;'>";
                    $retval .= $headerObj->fixedHeader;
                $retval .= "</table>";
            $retval .= "</div>";
        }
        
        $retval .= "<table align='center' class='unit_grid".
                $grid."FixedTables' id='BTECUnitGridTable'>";
        
        
		$criteriaCountArray = $headerObj->criteriaCountArray;
        $totalHeaderCount = $headerObj->totalHeaderCount;
        $this->criteriaCount = $criteriaCountArray;
		$header = $headerObj->header;	
        //$totalCellCount = $headerObj->totalCellCount;
//		if($subCriteria)
//		{
//			$subCriteriaNo = $headerObj->subCriteriaNo;
//		}
		$retval .= $header;
		
		$retval .= "<tbody>";
        
        $retval .= $this->get_unit_grid_data($qualID, $advancedMode, $editing, $courseID);
        
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
    
    public function has_sub_criteria()
    {
        return false;
    }
    
    public function set_grid_disabled($disabled)
    {
        $this->gridLocked = $disabled;
    }
    
    public function get_unit_grid_full_data($advancedMode, $editing, $courseID)
    {
        global $USER;
        //load the session object
        $sessionUnits = isset($_SESSION['session_group_unit'])? 
        unserialize(urldecode($_SESSION['session_group_unit'])) : array();
        if(array_key_exists($this->id, $sessionUnits))
        {
            $unitObject = $sessionUnits[$this->id];    
        }
        else
        {
            $unitObject = new stdClass();
        }
        $this->set_up_grid_display_values($editing, $advancedMode);
        $sCourseID = optional_param('scID', -1, PARAM_INT);
        $groupingID = optional_param('grID', -1, PARAM_INT);
        //load the students that are on this unit for this qual
        //this will need to pull back the qualid that the user is on when it does the search. .
        if(isset($this->students))
        {
            $studentsArray = $this->students;
        }
        else
        {
            $studentsArray = $this->get_users_on_unit($this->id, $USER->id, $sCourseID, $groupingID);
        }
        $studentsArray = $this->process_paging_from_grid_display($studentsArray);
        $retval = array();
        if($studentsArray)
        {
            $rowCount = 0;
            foreach($studentsArray AS $student)
            {
                //need to calculate the qualificationid (untill we get rid of the duplicate qualids (userunits having a qualid in))
                $qualID = $this->get_users_qual_from_unit($student->id, $this->id);
                $rowCount++;
                $row = $this->get_unit_grid_data_display($qualID, $student, $advancedMode, $editing, $courseID, $rowCount);
                $retval[] = $row;
            }
        }
        return $retval;
    }
    
    protected function get_users_qual_from_unit($userID, $unitID = -1)
    {
        if($unitID == -1)
        {
            $unitID = $this->id;
        }
        global $DB;
        
        //right. this is going to try and find ONE qual id for this
        //user for this unit. This is only while the unit can be on more than one
        //qual with different instances of the user criteria data.
        //it will try and pick the latest updated one for this unit/criteria
        
        $sql = "SELECT * FROM {block_bcgt_user_unit} WHERE userid = ? AND bcgtunitid = ? ORDER BY dateupdated ASC, dateset ASC";
        $userUnits = $DB->get_records_sql($sql, array($userID, $unitID));
        if($userUnits)
        {
            //only one found, therefore only on one.
            return end($userUnits)->bcgtqualificationid;
        }
        return false;
        
    }
    
    protected function process_paging_from_grid_display($studentsArray)
    {
        $pageNumber = optional_param('page',1,PARAM_INT);
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
        return $studentsShowArray;
    }
    
    protected function set_up_grid_display_values($editing, $advancedMode)
    {
        global $CFG;
        $possibleValues = null;
        $unitAwards = null;
        if($editing && !$advancedMode)
        {
            $unitAwards = Unit::get_possible_unit_awards($this->get_typeID());
        }
        $this->unitAwards = $unitAwards;
        if($editing && $advancedMode)
        {
            $possibleValues = BTECQualification::get_possible_values(BTECQualification::ID);
        }
        $this->possibleValues = $possibleValues;
        
        $criteriaNames = $this->get_used_criteria_names();
        //Get this units criteria names and sort them. 
        require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
		$criteriaSorter = new CriteriaSorter();
		usort($criteriaNames, array($criteriaSorter, "ComparisonDelegateByArrayNameLetters"));
        $this->criteriNames = $criteriaNames;
        
        $subCriteriaDisplay = $this->has_sub_criteria();
        $this->subCriteriaDisplay = $subCriteriaDisplay;
    }
    
    /**
     * 
     * @param type $qualID
     * @param type $advancedMode
     * @param type $editing
     */
    public function get_unit_grid_data($qualID, $advancedMode, $editing, $courseID)
    {
        
        global $OUTPUT;
        
        //load the session object
        $sessionUnits = isset($_SESSION['session_unit'])? 
        unserialize(urldecode($_SESSION['session_unit'])) : array();        
        $studentsLoaded = false;
        if(array_key_exists($this->id, $sessionUnits))
        {
            $unitObject = $sessionUnits[$this->id];
            
            if (isset($unitObject->unit))
            {
                            
                if (isset($unitObject->qualArray))
                {
                
                    $qualArray = $unitObject->qualArray;
                    $unitObject = $unitObject->unit;
                                        
                    if (isset($unitObject->students))
                    {
                        $studentsArray = array();
                        $unitStudents = $unitObject->students;

                        if ($qualArray)
                        {
                            foreach($qualArray as $qualID => $students)
                            {
                                if ($students)
                                {
                                    foreach($students as $student)
                                    {
                                        if (isset($unitStudents[$student->id]))
                                        {
                                            $unitStudents[$student->id] = $student;
                                        }
                                    }
                                }
                            }
                        }
                        
                        $studentsArray = $unitStudents;
                        $studentsLoaded = true;
                        
                    }
                
                }

            }
            
              
        }
        else
        {
            $unitObject = new stdClass();
            $qualArray = array();
        }
        $groupingID = optional_param('grID', -1, PARAM_INT);
        $regGroupID = optional_param('regGrpID', false, PARAM_INT);
        
//        pn("arr:");
//        pn($studentsArray);
        
        if(!$studentsLoaded)
        {   
            //load the students that are on this unit for this qual. 
            if ($regGroupID){
                $studentsArray = bcgt_get_register_group_users($regGroupID);
            } else {
                $studentsArray = get_users_on_unit_qual($this->id, $qualID, $courseID, $groupingID);
            }
        }
        
        $pageNumber = optional_param('page',1,PARAM_INT);
        global $CFG, $DB, $COURSE;
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        $subCriteriaDisplay = $this->has_sub_criteria();
        
        //need to get the order:
        $order = optional_param('order', 'btec', PARAM_TEXT);
        if($order == 'act')
        {
            if(isset($this->fullCriteriaNamesAct))
            {
                //get the full list of criteriaNames
//                $criteriaNames = $this->criteriaNamesUsedAct;
                
                $criteriaNames = $this->fullCriteriaNamesAct;
            }            
        }
        else
        {
            $criteriaNames = $this->get_used_criteria_names();
            //Get this units criteria names and sort them. 
            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
            $criteriaSorter = new CriteriaSorter();
            usort($criteriaNames, array($criteriaSorter, "ComparisonDelegateByArrayNameLetters"));
        }
        
        $output = "";
        
        $retval = array();
        $possibleValues = null;
        $unitAwards = null;
        if($editing)
        {
            $unitAwards = Unit::get_possible_unit_awards($this->get_typeID());
        }
        $this->unitAwards = $unitAwards;
        if($editing && $advancedMode)
        {
            $possibleValues = BTECQualification::get_possible_values(BTECQualification::ID);
        }
        $this->possibleValues = $possibleValues;
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
                $i = ($pageRecords * ($pageNumber - 1));
            }
            
            //we want to loop over and only show the number of students in our page size. 
            $recordsEnd = ($i + $pageRecords);
            for($i;$i<$recordsEnd;$i++)
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
        $studentsSessionArray = $studentsArray;
        foreach($studentsShowArray AS $student)
        {
            
            $output .= "<tr>";
            
            $rowCount++;
            $rowClass = 'rO';
            if($rowCount % 2)
            {
                $rowClass = 'rE';
            }				
            
            if(isset($student->unit))
            {
                //then we are coming from the session and the unit object has aleady
                //been loaded
                $studentUnit = $student->unit;
            }
            else
            {
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELALL;
                $loadParams->loadAward = true;
                $studentUnit = Unit::get_unit_class_id($this->id, $loadParams);
                $studentUnit->load_student_information($student->id, $qualID, $loadParams);
                $student->unit = $studentUnit;
                
                //then we want to save the object to the session
                //but we also just want to sstudent load on this object (as each time it will
                //clear it down)
            }
            
//            $studentsSessionArray[$student->id] = $student;
            $extraClass = '';
            if($rowCount == 1)
            {
                $extraClass = 'firstRow';
            }
            elseif($rowCount == count($studentsArray))
            {
                $extraClass = 'lastRow';
            }
            
            
            $output .= "<td class='assignmentTD'></td>"; #?   
            
            
            // Unit Comment
            $comments = $studentUnit->get_comments();
            $studentComments = $studentUnit->get_student_comments();
            
            $qual = Qualification::get_qualification_class_id($qualID);
            $studentCommentsSetting = $qual->get_attribute("read_unit_comments_{$studentUnit->get_id()}", $student->id);
            
            $class = ($comments != '') ? 'hasComments' : '';
            
            $output .= "<td class='unitCommentTD {$class}'>";            
            
                $output .= "<div class='criteriaTDContent'>";
                
                    $output .= " <img src='{$CFG->wwwroot}/blocks/bcgt/pix/info.png' height='12' width='12' class='uNToolTipInfo hand' unitID='{$this->id}' /><div class='unitInfoContent' title='{$studentUnit->get_display_name()}'>{$studentUnit->build_unit_details_table()}</div><br><br>";

                    if ($studentComments && strlen($studentComments) > 0){
                        $output .= "<img src='{$CFG->wwwroot}/blocks/bcgt/pix/comment.png' class='studentUnitComments hand' id='studentUnitComments_S{$student->id}_U{$studentUnit->get_id()}_Q{$qualID}' studentID='{$student->id}' unitID='{$studentUnit->get_id()}' qualID='{$qualID}' /> ";
                    }
                    elseif ($studentCommentsSetting && $studentCommentsSetting > 0){
                        $output .= "<img src='{$CFG->wwwroot}/blocks/bcgt/pix/tick.png' title='Unit Comments Have Been Read' /> ";
                    }
                    
                $output .= "</div>";

                $output .= "<div class='hiddenCriteriaCommentButton'>";

                    $username = $student->username;
                    $fullname = fullname($student);
                    $unitname = bcgt_html($this->name);
                    $critname = "N/A";
                    $cellID = "cmtCell_U_{$this->id}_S_{$student->id}_Q_{$qualID}";
                    
                    if (!empty($comments))
                    {
                        $output .= "<img id='{$cellID}' criteriaid='-1' unitid='{$this->id}' studentid='{$student->id}' qualid='{$qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='unit' class='editCommentsUnit' title='Click to Edit Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/comment_edit.png' alt='".get_string('editcomments', 'block_bcgt')."' />";
                    }
                    else
                    {
                        $output .= "<img id='{$cellID}' criteriaid='-1' unitid='{$this->id}' studentid='{$student->id}' qualid='{$qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='unit' class='addCommentsUnit' title='Click to Add Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/comment_add.png' alt='".get_string('addcomment', 'block_bcgt')."' />";
                    }

                    //$retval .= "<span class='tooltipContent' style='display:none !important;'>".bcgt_html($this->comments, true)."</span>";
                    $output .= "<div class='popUpDiv bcgt_unit_comments_dialog' id='dialog_S{$student->id}_U{$this->id}_Q{$qualID}' qualID='{$qualID}' unitID='{$this->id}' critID='-1' studentID='{$student->id}' grid='unit' imgID='{$cellID}' title='Comments'>";
                        $output .= "<span class='commentUserSpan'>Comments for {$fullname} : {$username}</span><br>";
                        $output .= "<span class='commentUnitSpan'>{$this->get_display_name()}</span><br>";
                        $output .= "<span class='commentCriteriaSpan'>N/A</span><br><br><br>";
                        $output .= "<textarea class='dialogCommentText' id='text_S{$student->id}_U{$this->id}_Q{$qualID}'>".bcgt_html($comments)."</textarea>";
                    $output .= "</div>";


                $output .= "</div>";
                
                
                
                $output .= "<div class='bcgt_student_comments_dialog c' id='student_dialog_S{$student->id}_U{$studentUnit->get_id()}_Q{$qualID}' qualID='{$qualID}' unitID='{$studentUnit->get_id()}' studentID='{$student->id}'>";

                    $output .= "<p><small>Unit Comments</small></p>";
                    $output .= "<textarea readonly>";
                        $output .= $studentUnit->get_comments();
                    $output .= "</textarea>";
                    $output .= "<br><br>";

                    // Students' response comments
                    $output .= "<p><small>Student Comments</small></p>";

                    $output .= "<textarea id='student_response_comments_S{$student->id}_U{$studentUnit->get_id()}_Q{$qualID}' readonly>";
                        $output .= $studentUnit->get_student_comments();
                    $output .= "</textarea>";
                    $output .= "<br><br>";

                $output .= "</div>";
                
                
                
            
            $output .= "</td>";
            // End Unit Comment  
            
            
            
            // End Unit Comment  
            $output .= $this->build_unit_grid_students_details($student, $qualID, $context, $courseID);


            //work out the students unit award
            $stuUnitAward = $studentUnit->get_user_award();
            $award = 'N/A';
            $rank = '';
            $shortAward = null;
            if($stuUnitAward)
            {
                $rank = $stuUnitAward->get_rank();
                $award = $stuUnitAward->get_award();
                $shortAward = $stuUnitAward->get_short_award();
            }
                        
            
            if($editing)
            {
                $output .= "<td id='unitAwardCell_{$student->id}_{$qualID}'  style='width:60px;min-width:60px;max-width:60px;'>".$this->get_unit_award_edit($student, $qualID, 
                        $this->get_typeID(), $rank, $award, $this->unitAwards)."</td>";
            }
            else
            {
                //print out the unit award column
    //                $retval .= "<td id='unitAward_".$student->id."' class='unitAward r".$student->id." rank$rank'><span id='unitAward_$student->id'>".$award."</span></td>";
                if ($shortAward && $shortAward != ''){
                    $award = $shortAward;
                }
                $output .= "<td id='unitAwardCell_{$student->id}_{$qualID}' style='width:60px;min-width:60px;max-width:60px;'><span id='unitAwardAdv_{$student->id}_{$qualID}'>".$award."</span></td>";

            }	
        
            

            if($criteriaNames)
            {
                $criteriaCount = 0;
                $previousLetter = '';
                
                $c = ($editing) ? 'Edit' : 'NonEdit';
                $width = ($editing) ? 100 : 40;
                                
                foreach($criteriaNames AS $criteriaName)
                {	
                    if($order == 'act' && $criteriaName == '')
                    {
                        //this is if its a activity criteriaName
                        //just want to ouput a blank entry
                        $output .= "<td></td>";
                        continue;
                    }
                    elseif($order != 'act')
                    {
                        $letter = substr($criteriaName, 0, 1);
                        if(($previousLetter != '' && $previousLetter != $letter))
                        {
                            $output .= "<td class='divider' style='width:5px;min-width:5px;max-width:5px;'></td>";
                        }
                        $previousLetter = $letter;
                    }
                    
                    if ($order == 'act' && !$editing){
                        $width = 50;
                    }
                    
                    $criteriaCount++;
                    if($studentCriteria = $studentUnit->get_single_criteria(-1, $criteriaName))
                    {
                        
                        $output .= "<td class='criteriaCell criteriaValue{$c}' qualID='{$qualID}' criteriaID='{$studentCriteria->get_id()}' studentID='{$student->id}' unitID='{$studentUnit->get_id()}' >".
                                $this->set_up_criteria_grid($studentCriteria, '', $student, $possibleValues, $editing, $advancedMode, '', $qualID).
                                "</td>";
                                                
                        if($subCriteriaDisplay)
                        {
                            $subCriterias = $studentCriteria->get_sub_criteria();
                            if($subCriterias)
                            {
                                $i = 0;
                                foreach($subCriterias AS $subCriteria)
                                {
                                    $firstLast = 0;
                                    $i++;
                                    $extraClass = '';
                                    if($i == 1)
                                    {
                                        $extraClass = 'startSubCrit';
                                        if(count($subCriterias) == 1)
                                        {
                                            $extraClass .= " endSubCrit";
                                        }
                                        $firstLast = 1;
                                    }
                                    elseif($i == count($subCriterias))
                                    {
                                        $extraClass = 'endSubCrit';
                                        $firstLast = -1;
                                    }
                                    
                                    $output .= "<td class='criteriaCell criteriaValue{$c}' qualID='{$qualID}' criteriaID='{$studentCriteria->get_id()}' studentID='{$student->id}' unitID='{$studentUnit->get_id()}' >".
                                        $this->set_up_criteria_grid($subCriteria, $extraClass.' subCriteria subCriteria_'.$criteriaName.'', $student, $possibleValues, $editing, $advancedMode, $firstLast, $qualID).
                                    "</td>";
                                    
                                }
                            }//end if subCriterias found	
                        }//end if displaying sub criterias
                    }//end if the criteria found
                }//end for each criteria Name
            }//end if criteriaNames
            
            $output .= "<td></td>";
            $output .= "<td class='ivColumn' style='width:50px;min-width:50px;max-width:50px;'>";
                            
                if ($editing){
                    $output .= "<small>Date:</small><br><input type='text' id='ivdate_q{$qualID}_u{$this->id}_s{$student->id}' class='datePickerIV' style='width:80px;' qualID='{$qualID}' unitID='{$this->id}' studentID='{$student->id}' name='IVDATE' grid='student' value='{$this->get_attribute('IVDATE', $qualID, $student->id)}' /><br><small>Who:</small><br><input type='text' style='width:80px;' class='updateUnitAttribute' id='ivby_q{$qualID}_u{$this->id}_s{$student->id}' qualID='{$qualID}' unitID='{$this->id}' studentID='{$student->id}' name='IVWHO' grid='student' value='{$this->get_attribute('IVWHO', $qualID, $student->id)}' /><br>";
                } else {
                    $output .= "<small>Date:</small><br>{$this->get_attribute('IVDATE', $qualID, $student->id)}<br><small>Who:</small><br>{$this->get_attribute('IVWHO', $qualID, $student->id)}<br>";
                }
                        
            $output .= "</td>";
            
            $output .= "</tr>";
            
        }//end for each student
        $qualArray[$qualID] = $studentsSessionArray;
        $unitObject->qualArray = $qualArray;
        $unitObject->unit = $this;
        $sessionUnits[$this->id] = $unitObject;
        $_SESSION['session_unit'] = urlencode(serialize($sessionUnits));

//                // Grid logs
//                $studentArray = array();
//                foreach($students as $student)
//                {
//                    $studentArray[] = $student->id;
//                }
//                $qualArray = array();
//                foreach($qualIDs as $qualID)
//                {
//                    $qualArray[] = $qualID;
//                }
//                
//                if($studentArray && $qualArray){
//                    $retval .= $this->show_logs($studentArray, $qualArray);
//                }
//                
		return $output;	
    }
    
    /**
     * 
     * @global type $CFG
     * @param type $criteria
     * @param type $extraCellClass
     * @param type $student
     * @param type $possibleValues
     * @param type $editing
     * @param type $advancedMode
     * @param type $firstLast
     * @param type $qualID
     * @return string
     */
    private function set_up_criteria_grid($criteria, $extraCellClass, $student, 
	$possibleValues, $editing, $advancedMode, $firstLast, $qualID)
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
        
        $studentComments = iconv('UTF-8', 'ASCII//TRANSLIT', $studentComments);  
        
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
        
        $retval .= "<div class='criteriaTDContent'>";
        
        
		if($editing)
		{
			if($advancedMode)
			{	
                $retval .= $this->advanced_editing_grid($student, 
						$criteria, $possibleValues, $studentValueObj, 
						$studentComments, $extraCellClass, $firstLast, $qualID);
			}
			else //editing but simple mode
			{
				$retval .= $this->simple_editing_grid($student, 
                    $studentValueObj, $criteria, $qualID);
			}
		}
		else //NOT EDITING
		{
			if($advancedMode)
			{
                $class = $studentValueObj->get_short_value();
                $class .= (!is_null($comments) && !empty($comments)) ? ' hasComments' : '';
                $shortValue = $studentValueObj->get_short_value();
                if($studentValueObj->get_custom_short_value())
                {
                    $shortValue = $studentValueObj->get_custom_short_value();
                }
				$retval .= "<span id='cID_".$criteria->get_id().
                "_uID_".$this->id."_sID_".$student->id."_qID_".$qualID."' ".
                        "class='stuValue stuValueNonEdit $class'>".$shortValue."</span>";
                    $retval .= "<div class='criteriaContent'></div>";
			}
			else //not editing but simple mode
			{
                $studentFlag = $criteria->get_student_flag();
                $flag = '';
                if(isset($this->studentFlag) && $studentFlag)
                {
                    $flag = $this->studentFlag;
                }
				$imageObj = BTECQualification::get_simple_grid_images($studentValueObj->get_short_value(), $studentValueObj->get_value(), $studentValueObj->get_id(), $flag);
                $image = $imageObj->image;
                $class = $imageObj->class;
                $class .= (!is_null($comments) && !empty($comments)) ? ' hasComments' : '';
                $retval .= "<span id='cID_".$criteria->get_id().
                "_uID_".$this->id."_sID_".$student->id."_qID_".$qualID."' ".
                        "class='stuValue stuValueNonEdit $class'><img src='{$image}'></span>";
                    $retval .= "<div class='criteriaContent'></div>";
			}	
		}//end else not editing
        
        $retval .= "</div>";
        
        // Hidden div for adding a comment when it's enabled
        $retval .= "<div class='hiddenCriteriaCommentButton'>";
        
            $username = $student->username;
            $fullname = fullname($student);
            $unitname = bcgt_html($this->get_name());
            $critname = bcgt_html($criteria->get_name());
            
            // Change this so each thing has its own attribute, wil be easier
            $commentImgID = "cmtCell_cID_".$criteria->get_id()."_uID_".$this->get_id()."_SID_".$student->id.
                            "_QID_".$qualID;
        
            if ($criteria->get_comments() != '')
            {
                $retval .= "<img id='{$commentImgID}' criteriaid='{$criteria->get_id()}' unitid='{$this->get_id()}' studentid='{$student->id}' qualid='{$qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='unit' class='editComments' title='Click to Edit Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/comment_edit.png' alt='".get_string('editcomments', 'block_bcgt')."' />";
            }
            else
            {
                $retval .= "<img id='{$commentImgID}' criteriaid='{$criteria->get_id()}' unitid='{$this->get_id()}' studentid='{$student->id}' qualid='{$qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='unit' class='addComments' title='Click to Add Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/comment_add.png' alt='".get_string('addcomment', 'block_bcgt')."' />";
            }
            
            
            if ($editing)
            {
            
                $retval .= "<div class='popUpDiv bcgt_comments_dialog' id='dialog_{$student->id}_{$criteria->get_id()}_{$qualID}' qualID='{$qualID}' unitID='{$this->get_id()}' critID='{$criteria->get_id()}' studentID='{$student->id}' grid='unit' imgID='{$commentImgID}' title='Comments'>";
                    $retval .= "<span class='commentUserSpan'>Comments for {$fullname} : {$username}</span><br>";
                    $retval .= "<span class='commentUnitSpan'>{$this->get_display_name()}</span><br>";
                    $retval .= "<span class='commentCriteriaSpan'>{$criteria->get_name()}</span><br><br><br>";
                    $retval .= "<textarea class='dialogCommentText' id='text_{$student->id}_{$criteria->get_id()}_{$qualID}'>".bcgt_html($this->comments)."</textarea>";
                $retval .= "</div>";
            
            }
            
        
        $retval .= "</div>";
        
		return $retval;
	}
    
    protected function get_unit_grid_data_display($qualID, $user, 
            $advancedMode, $editing, $courseID, $rowCount)
    {
        global $CFG, $COURSE;
        $row = array();
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }       
        $rowCount++;
        $rowClass = 'rO';
        if($rowCount % 2)
        {
            $rowClass = 'rE';
        }				

        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
        $studentUnit = Unit::get_unit_class_id($this->id, $loadParams);
        $studentUnit->load_student_information($user->id, $qualID, $loadParams);
        $row[] = '';      
        // Unit Comment
        $getComments = $studentUnit->get_comments();
        $cellID = "cmtCell_U_{$studentUnit->get_id()}_S_{$user->id}_Q_{$studentUnit->qualID}";


        $username = htmlentities( $user->username, ENT_QUOTES );
        $fullname = htmlentities( fullname($user), ENT_QUOTES );
        $unitname = htmlentities( $studentUnit->get_name(), ENT_QUOTES);
        $critname = "N/A";   

        $rowVal = "";

        if($advancedMode && $editing)
        {
            if(!empty($getComments))
            {                
                $rowVal .= "<img id='{$cellID}' grid='unit' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' type='button' class='editCommentsUnit' title='Click to Edit Unit Comments' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/comments.jpg' />";
                $rowVal .= "<div class='tooltipContent'>".nl2br( htmlentities($getComments, ENT_QUOTES) )."</div>";
            }
            else
            {                        
                $rowVal .= "<img id='{$cellID}' grid='unit' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' type='button' class='addCommentsUnit' title='Click to Add Unit Comment' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/plus.png' />";
            }
        }
        else
        {
            if(!empty($getComments)){
                $rowVal .= "<img src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/comment-icon.png' class='showCommentsUnit' />";
                $rowVal .= "<div class='tooltipContent'>".nl2br( htmlentities($getComments, ENT_QUOTES) )."</div>";
            }

        }

        $row[] = $rowVal;
        // End Unit Comment  
        $row = $this->build_unit_grid_students_details($user, $qualID, $context);


        //work out the students unit award
        $stuUnitAward = $studentUnit->get_user_award();
        $award = '';
        $rank = '';
        $shortAward = null;
        if($stuUnitAward)
        {
            $rank = $stuUnitAward->get_rank();
            $award = $stuUnitAward->get_award();
            $shortAward = $stuUnitAward->get_short_award();
        }
        
        
        if($editing)
        {
            $rowVal .= "<td id='unitAwardCell_{$user->id}_{$qualID}'  style='width:100px;min-width:100px;'>".$this->get_unit_award_edit($user, $qualID, 
                    $this->get_typeID(), $rank, $award, $this->unitAwards)."</td>";
        }
        else
        {
            //print out the unit award column
//                $retval .= "<td id='unitAward_".$student->id."' class='unitAward r".$student->id." rank$rank'><span id='unitAward_$student->id'>".$award."</span></td>";
            if ($shortAward && $shortAward != ''){
                $award = $shortAward;
            }
            $rowVal .= "<td id='unitAwardCell_{$user->id}_{$qualID}' style='width:100px;min-width:100px;'><span id='unitAwardAdv_{$user->id}_{$qualID}'>".$award."</span></td>";

        }	
        
        
//        if($editing && !$advancedMode)
//        {
//            $row[] = $this->get_unit_award_edit($user, $qualID, 
//                    $this->get_typeID(), $rank, $award, $this->unitAwards);
//        }
//        else
//        {
//            $output = $award;
//            if($shortAward && $shortAward != '')
//            {
//                $output = $shortAward;
//            }
//            //print out the unit award column
////                $retval .= "<td id='unitAward_".$student->id."' class='unitAward r".$student->id." rank$rank'><span id='unitAward_$student->id'>".$award."</span></td>";
//            $row[] = "<span id='unitAwardAdv_$user->id'>".$output."</span>";
//
//        }	
        
        

        //need to get the order:
        $order = optional_param('order', 'btec', PARAM_TEXT);
        if($order == 'act')
        {
            //get the full list of criteriaNames
//            $criteriaNames = $this->criteriaNamesUsedAct;
            
            $criteriaNames = $this->fullCriteriaNamesAct;
        }
        else
        {
            $criteriaNames = $this->get_used_criteria_names();
            //Get this units criteria names and sort them. 
            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
            $criteriaSorter = new CriteriaSorter();
            usort($criteriaNames, array($criteriaSorter, "ComparisonDelegateByArrayNameLetters"));
        }
        
        
        
        
        if($criteriaNames)
        {
            $criteriaCount = 0;
            $previousLetter = '';
            foreach($criteriaNames AS $criteriaName)
            {	
                if($order == 'act' && $criteriaName == '')
                {
                    //this is if its a activity criteriaName
                    //just want to ouput a blank entry
                    $row[] = "";
                    continue;
                }
                elseif($order != 'act')
                {
                    $letter = substr($criteriaName, 0, 1);
                    if(($previousLetter != '' && $previousLetter != $letter))
                    {
                        $row[] = "";
                    }
                    $previousLetter = $letter;
                }

                $criteriaCount++;
                $studentCriteria = $studentUnit->get_single_criteria(-1, $criteriaName);
                if($studentCriteria)
                {
                    $row = $this->set_up_criteria_grid($studentCriteria, '', $user, 
                            $this->possibleValues, $editing, $advancedMode, '', $row, $qualID);                        
                    if($this->subCriteriaDisplay)
                    {
                        $subCriterias = $studentCriteria->get_sub_criteria();
                        if($subCriterias)
                        {
                            $i = 0;
                            foreach($subCriterias AS $subCriteria)
                            {
                                $firstLast = 0;
                                $i++;
                                $extraClass = '';
                                if($i == 1)
                                {
                                    $extraClass = 'startSubCrit';
                                    if(count($subCriterias) == 1)
                                    {
                                        $extraClass .= " endSubCrit";
                                    }
                                    $firstLast = 1;
                                }
                                elseif($i == count($subCriterias))
                                {
                                    $extraClass = 'endSubCrit';
                                    $firstLast = -1;
                                }
                                $row = $this->set_up_criteria_grid($subCriteria, 
                                        $extraClass.' subCriteria subCriteria_'.$criteriaName.'', 
                                        $user, $this->possibleValues, $editing, 
                                        $advancedMode, 
                                        $firstLast, $row, $qualID);
                            }
                        }//end if subCriterias found	
                    }//end if displaying sub criterias
                }//end if the criteria found
            }//end for each criteria Name
        }//end if criteriaNames
        return $row;
    }
    
    protected function advanced_editing_grid($student, 
	$studentCriteria, $possibleValues, $studentValueObj, 
	$studentComments, $extraCellClass, $firstLast, $qualID)
	{
        $disabled = '';
        if(isset($this->gridLocked) && $this->gridLocked)
        {
            $disabled = 'disabled="disabled"';
        }
        global $CFG;
		//advanced mode allows
		//drop down options and comments
		//this td is used as the hover over tooltip.
		$extraClass = $extraCellClass;
		if($firstLast == -1)
		{
			//this means its the last column we are dealing with.
			//this negates their being two last columns which would draw
			//the extra border
			$extraClass = '_'.$extraCellClass;	
		}
		
		$retval = "";
		$retval .= "<span class='stuValue' qual='$qualID' id='cID_".$studentCriteria->get_id().
                "_uID_".$this->id."_sID_".$student->id."_qID_".$qualID."'>".
                "<select $disabled qual='$qualID' id='sID_".$student->id."_cID_".$studentCriteria->get_id()."' class='criteriaValueSelect' name='cID_".$studentCriteria->get_id()."'><option value='-1'></option>";
		if($possibleValues)
		{
			foreach($possibleValues AS $value)
			{
                $selected = '';
				if($studentValueObj->get_id() == $value->id)
				{
                    $selected = 'selected';
                }
                $retval .= "<option $selected value = '$value->id' title='$value->value'>$value->shortvalue - $value->value</option>";
			}
		}
		$retval .= "</select></span>&nbsp;";
		
		$extraClass = $extraCellClass;
		if($firstLast == 1)
		{
			//this means its the first column we are dealing with.
			//this negates their being two first columns which would draw
			//the extra border
			$extraClass = '_'.$extraCellClass;	
		}
		
//		$retval .= "<td class='$extraClass criteriaCommentsEAD r".$student->id." c$criteriaName'>";
          
				        
        // Change this so each thing has its own attribute, wil be easier
        $commentImgID = "cmtCell_cID_".$studentCriteria->get_id()."_uID_".$this->get_id()."_SID_".$student->id.
                        "_QID_".$qualID;
        
        $username = htmlentities( $student->username, ENT_QUOTES );
        $fullname = htmlentities( fullname($student), ENT_QUOTES );
        $unitname = htmlentities( $this->get_name(), ENT_QUOTES);
        $critname = htmlentities($studentCriteria->get_name(), ENT_QUOTES);        
                            
        $studentComments = htmlspecialchars($studentComments, ENT_QUOTES);		 

        if ($studentCriteria->get_comments() != '')
        {
            $retval .= "<img id='{$commentImgID}' criteriaid='{$studentCriteria->get_id()}' unitid='{$this->get_id()}' studentid='{$student->id}' qualid='{$qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='student' class='editComments' title='Click to Edit Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/comment_edit.png' alt='".get_string('editcomments', 'block_bcgt')."' style='width:20px;vertical-align:middle;' />";
        }
        else
        {
            $retval .= "<img id='{$commentImgID}' criteriaid='{$studentCriteria->get_id()}' unitid='{$this->get_id()}' studentid='{$student->id}' qualid='{$qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='student' class='addComments' title='Click to Add Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/comment_add.png' alt='".get_string('addcomment', 'block_bcgt')."' style='width:20px;vertical-align:middle;' />";
        }
        
        $retval .= "<div class='popUpDiv bcgt_comments_dialog' id='dialog_{$student->id}_{$studentCriteria->get_id()}_{$qualID}' qualID='{$qualID}' unitID='{$this->id}' critID='{$studentCriteria->get_id()}' studentID='{$student->id}' grid='unit' imgID='{$commentImgID}' title='Comments'>";
            $retval .= "<span class='commentUserSpan'>Comments for {$fullname} : {$username}</span><br>";
            $retval .= "<span class='commentUnitSpan'>{$this->get_display_name()}</span><br>";
            $retval .= "<span class='commentCriteriaSpan'>{$studentCriteria->get_name()}</span><br><br><br>";
            $retval .= "<textarea class='dialogCommentText' id='text_{$student->id}_{$studentCriteria->get_id()}_{$qualID}'>{$studentComments}</textarea>";
        $retval .= "</div>";
        
        
//		$retval .= "</td>";
		return $retval;
	}
	
	protected function simple_editing_grid($student, 
	$studentValueObj, $studentCriteria, $qualID)
	{	
        $disabled = '';
        if(isset($this->gridLocked) && $this->gridLocked)
        {
            $disabled = 'disabled="disabled"';
        }
		$retval = "<span class='stuValue' qual='$qualID' id='cID_".$studentCriteria->get_id().
                "_uID_".$this->id."_sID_".$student->id."_qID_".$qualID."'><input $disabled type='checkbox' class='criteriaValueMet criteriaCheck'".
                "name='cID_".$studentCriteria->get_id()."' qual='$qualID' id='sID_".$student->id.
                "_cID_".$studentCriteria->get_id()."' ";
		if($studentValueObj->get_short_value() == 'A')
		{
			$retval .= "checked='checked'";
		}
		$retval .= "/></span>";
		return $retval;
	}
    
    
    protected function get_unit_award_edit($student, $qualID, $typeID, $rank, $award, $unitAwards)
	{
        $disabled = '';
        if(isset($this->gridLocked) && $this->gridLocked)
        {
            $disabled = 'disabled="disabled"';
        }
		$retval = "";
		$retval .= "<select $disabled class='unitAward' qual='$qualID' id='uAw_$student->id' name='unitAwardAPL'>";
		$retval .= "<option value='-1'></option>";
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
		$retval .= "</select></span>";
		return $retval;
	}
    
    
    protected function build_unit_grid_students_details($student, $qualID, $context, $courseID = SITEID)
	{
		global $CFG, $printGrid, $OUTPUT;
		   
        $output = "";
        
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
           
            $style = ($column == 'picture') ? "style='width:50px;min-width:50px;max-width:50px;'" : "style='width:80px;min-width:80px;max-width:80px;'";
            $content = '<td '.$style.'>';
            
            if ($column == 'username' || $column == 'name'){
                $content .= "<a href='{$CFG->wwwroot}/blocks/bcgt/grids/student_grid.php?sID={$student->id}&qID={$qualID}&cID={$courseID}' target='_blank'>";
            }
            
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
                        
            if ($column == 'username' || $column == 'name'){
                $content .= "</a>";
            }
            
            $content .= '</td>';
            $output .= $content;
            
        }
		$qualAward = "N/A";
		$type = "NA";
        if(has_capability('block/bcgt:viewbtecavggrade', $context))
        {
            //work out the students qualification award
            $award = $this->get_student_qual_award($student->id, $qualID);
            if($award)
            {
                $type = $award->type;
                $qualAward = "<span id='qualAward_$student->id'>$award->type<br />$award->targetgrade</span>";
            }
            $output .= "<td style='width:60px;min-width:60px;max-width:60px;'>{$qualAward}</td>";
        }
		
		return $output;	
	}
    
    
    
    
    /**
     * 
     * @global type $printGrid
     * @param type $criteriaNames
     * @param type $advancedMode
     * @param type $editing
     * @param type $subCriteriaDisplay
     * @return \stdClass
     */
    protected function get_unit_grid_header($criteriaNames,$grid,
            $subCriteriaDisplay = false, $context)
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
        
        $width = ($editing) ? 100 : 40;
                
        global $printGrid, $CFG;
		$headerObj = new stdClass();
		$header = '';
		$header .= "<thead>";
        
        $fixedHeader = "";
        $fixedHeader .= "<thead>";
        $fixedHeader .= "<tr>";
                
		$dividers = array();

        $mainRowRowSpan = 1;
        $order = optional_param('order', 'btec', PARAM_TEXT);
        if($order == 'act')
        {
            $mainRowRowSpan = 2;
            $courseID = optional_param('cID', -1, PARAM_INT);
            $groupingID = optional_param('grID', -1, PARAM_INT);
            $qualID = optional_param('qID', -1, PARAM_INT);
            $modIcons = load_mod_icons($courseID, $qualID, $groupingID, -1, $this->id);
            //we are also going to want the activities
            $activities = $this->activities;
            
            if (!$editing){
                $width = 50;
            }
            
            //then we output the header with the activities.  
            //username, name, qual award, unit award are rowspan = 2
        }
        $header .= "<tr class='mainRow'>";
                
		//denotes projects
		$header .= "<th></th>";
        $header .= "<th id='unitCommentTH'></th>";
        
        
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
            $style = ($column == 'picture') ? "width:50px;min-width:50px;max-width:50px;" : "width:80px;min-width:80px;max-width:80px;";
            $header .="<th style='{$style}'>";
            $header .= get_string(trim($column), 'block_bcgt');
            $header .="</th>";
                        
            //$fixedHeader .= "<th style='{$style}'></th>";
            
        }
        if(has_capability('block/bcgt:viewbtecavggrade', $context))
        {
            $header .= "<th style='width:60px;min-width:60px;max-width:60px;'>".get_string('qualaward', 'block_bcgt')."</th>";
            //$fixedHeader .= "<th style='width:60px;min-width:60px;max-width:60px;'></th>";
        }
        
        $header .= "<th style='width:60px;min-width:60px;max-width:60px;'>".get_string('unitaward', 'block_bcgt')."</th>";
        //$fixedHeader .= "<th style='width:60px;min-width:60px;max-width:60px;'></th>";
        
        $fixedHeader .= "<th id='fixedHead_left_col'></th>";
        
        $totalHeaderCount = 7;
        
        //now it is different if its the activity or its the btec spec view. 
        if($order == 'act')
        {
            $criteriaNamesUsed = array();
            //this specifies when there is a border. 
            $criteriaCountArray = array(); 
            $activityTopRow = '';
            
            $w = ($editing) ? 100 : 60;
                        
            $criteriasOnActivities = array();
            $countProcessed = 0;
            foreach($activities AS $activity)
            {
                $countProcessed++;
                //top row is for the activity
                //icon, name, due date
                //need to get the criteria selection for this activity. 
                $criterias = get_activity_criteria($activity->id, null, $this->id);
                //need to sort the criterias
                require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
                $criteriaSorter = new CriteriaSorter();
                usort($criterias, array($criteriaSorter, "ComparisonDelegateByDBtName"));
                
                $criteriaCountArray[] = count($criterias);
                //need the icon
                //need the name
                //need the due date. 
                $activityTopRow .= '<th role="columnheader" colspan="'.count($criterias).'">';
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
                
                
                $cnt = count($criterias);
                $aWidth = ($editing) ? 100 : 40;
                $actWidth = $cnt * $aWidth;
                
                $fixedHeader .= "<th class='activityName' activityID='{$activity->id}'>{$activity->name}</th>";
                
                foreach($criterias AS $criteria)
                {
                    
                    $header .= '<th class="criteriaName criteriaName_'.$activity->id.'">'.$criteria->name.'</th>';
                    $criteriasOnActivities[$criteria->name] = $criteria->name;
                    $totalHeaderCount++;
                    $criteriaNamesUsed[] = $criteria->name;
                }
                if(count($activities) != $countProcessed)
                {
                    $activityTopRow .= '<th role="columnheader" class="divider"></th>';
                    $header .= '<th class="divider"></th>';
                    $fixedHeader .= '<th class="divider"></th>';
                    $totalHeaderCount++;
                    $criteriaNamesUsed[] = '';  
                }
            }
            //at end display criteriaNames not used.
            //get the criteriaNames on the actual unit. 
            $criteriaNamesNotUsed = array_diff($criteriaNames, $criteriasOnActivities);
            if(count($criteriaNamesNotUsed) > 0)
            {
                
                // Divider
                $header .= '<th class="divider"></th>';
                $fixedHeader .= '<th class="divider"></th>';

                $fixedHeader .= "<th class='activityName' activityID='Other'></th>";
                
                $criteriaNamesUsed[] = '';  
                
                //then we need an extra section
                $activityTopRow .= '<th colspan="'.count($criteriaNamesNotUsed).'">';
                $activityTopRow .= '</th>';
                usort($criteriaNamesNotUsed, array($criteriaSorter, "ComparisonDelegateByArrayNameLetters"));
                foreach($criteriaNamesNotUsed AS $criteriaName)
                {
                    $header .= '<th class="criteriaName criteriaName_Other">'.$criteriaName.'</th>';
                }
            }
            //$header .= $activityTopRow.'</tr>';
            $header .= '</tr>';
            $header .= '</thead>';
            $this->criteriaNamesUsedAct = $criteriaNamesUsed;
                        
            $this->fullCriteriaNamesAct = array_merge($criteriaNamesUsed, $criteriaNamesNotUsed);
        }
        else
        {
            // If unit has % completions enabled
//        if($this->has_percentage_completions() && !$printGrid){
//            $header .= "<th>% Complete</th>";
//            $totalHeaderCount++;
//        }
		
        //if we are doing it by project
        //then order the projects by due date
        //for each projects        
                
            $previousLetter = '';
            $criteriaCountArray = array();        
            if($criteriaNames)
            {
                //loop over each criteria and create a header
                //have a spacer between P, M and D
                $criteriaCount = 0;
                foreach($criteriaNames AS $criteriaName)
                {
                    //get the sub criteria
                    if($criteria = $this->get_single_criteria(-1, $criteriaName))
                    {
                        if($subCriteriaDisplay)
                        {
                            $subCriterias = $criteria->get_sub_criteria();
                        }
                    }
                    $criteriaCount++;
                    $letter = substr($criteriaName, 0, 1);
                    if(($previousLetter != '' && $previousLetter != $letter))
                    {
                        //the array is to be used at the end.
                        $criteriaCountArray[] = $criteriaCount;
                        $header .= "<th class='divider' style='width:5px;min-width:5px;max-width:5px;'></th>";
                        $totalHeaderCount++;
                    }
                    $previousLetter = $letter;
                    $header .= "<th class='criteriaName c{$criteriaName}'><span class='criteriaName";
                    if($subCriteriaDisplay && $subCriterias)
                    {
                        $header .= " hasSubCriteria' id='subCriteria_$criteriaName' ";
                    }
                    else
                    {
                        $header .= "' ";
                    }
                    $header .= ">".BTECQualification::build_criteria_display_name($criteriaName, $criteria)."</span></th>";
                    $totalHeaderCount++;

                    if($subCriteriaDisplay && $subCriterias)
                    {
                        foreach($subCriterias AS $subCriteria)
                        {
                            $header .= "<th class='criteriaName subCriteria subCriteria_$criteriaName' >".$subCriteria->get_name()."</th>";
                            $totalHeaderCount++;
                        }
                    }
    //                if($orderByProjects && in_array($criteriaCount, $dividers))
    //                {
    //                    $header .= "<th class='projectDivider'></th>";
    //                    $totalHeaderCount++;
    //                }
                }
            }
            
            $header .= "<th class='divider' style='width:5px;min-width:5px;max-width:5px;'></th>";
            $header .= "<th class='ivColumn'  style='width:85px;min-width:85px;max-width:85px;'>IV</th>";
            $header .= "</tr></thead>";
            $fixedHeader .= "</tr></thead>";
        }
		
		$headerObj->header = $header;
        $headerObj->fixedHeader = $fixedHeader;
                
        //need to work out this one for the activities. 
        
		$headerObj->criteriaCountArray = $criteriaCountArray;
		//$headerObj->orderedCriteriaNames = $criteriaNames;
        $headerObj->totalHeaderCount = $totalHeaderCount;
		return $headerObj;
	}
    
    /**
	 * Used to get the credits value from the database for this unit
	 * @param $id
	 */
	protected static function retrieve_credits($unitID)
	{		
		global $DB;
		$sql = "SELECT credits FROM {block_bcgt_unit} WHERE id = ?";
		return $DB->get_record_sql($sql, array($unitID));
	}
    
    public static function get_unit_level($unitID)
	{
		global $DB;
		$sql = "SELECT bcgtlevelid FROM {block_bcgt_unit} WHERE id = ?";
		$record = $DB->get_record_sql($sql, array($unitID));
		if($record)
		{
			return $record->bcgtlevelid;
		}
		return -1;
	}
	
	public static function get_unit_subtype($unitID)
	{
		global $DB;
		$sql = "SELECT bcgttypeid FROM {block_bcgt_unit} WHERE id = ?";
		$record = $DB->get_record_sql($sql, array($unitID));
		if($record)
		{
			$typeID = $record->bcgttypeid;
            require_once('BTECFoundationQualification.class.php');
			if($typeID == BTECFoundationQualification::ID)
			{
				return $typeID;
			}
			return -1;
		}
		return -1;
	}
    
    protected function get_credits_display_name()
    {
        return get_string('bteccredits', 'block_bcgt');
    }
    
    /**
	 * Returns the form fields that go on the 
	 * edit unit form for this unit type
	 * When a new unit is created or edited then it needs unit type specific 
	 * input fields. 
	 */
	public function get_edit_form_fields()
	{
		$creditsInputted = optional_param('credits', 0, PARAM_TEXT);
		if($creditsInputted == 0)
		{
            if(isset($this->id) && $this->id != -1)
            {
                $creditsInputted = $this->credits;
            }
            else
            {
                $creditsInputted = $this->get_default_credits();
            }
		}
		return '<div class="inputContainer"><div class="inputLeft">'.
                '<label for="credits">'.$this->get_credits_display_name(). 
                ' : </label></div><div class="inputRight"><input type="text"'.
                'name="credits" id="credits" value="'.$creditsInputted.'"/></div></div>';
	}
    
    public function get_edit_criteria_table()
	{
		return $this->get_edit_criteria_table_actual(false, '');	
	}
    
    //This is used to get the data that is unit type specific
	//from the edit form. 
	public function get_submitted_edit_form_data()
	{
		$this->credits = $_POST['credits'];
		$this->levelID = $_POST['level'];	
	}
    
    protected function merge_criteria_array($oldArray, $newArray)
	{
		//loop over newArray which contains the criteria already added.
		foreach($newArray AS $criteria)
		{
			//if the criteria hs an id then
			//add it to the old array (which is the orginal criteria)
			if($criteria->get_id() != -1)
			{
				$oldArray[$criteria->get_id()] = $criteria;
			}
			else
			{
				//else push the criteria into the array at the end. 
				//array_push($oldArray, $criteria);
				$oldArray[$criteria->get_name()] = $criteria;
			}
		}
		return $oldArray;
	}
    
    public function get_submitted_criteria_edit_form_data()
    {
        return $this->get_submitted_criteria_edit_form_data_actual();
	}
    
    /**
	 * Inserts the new unit and the criteria
	 */
	public function insert_unit($trackingTypeID = BTECQualification::ID)
	{
        global $DB;
		$stdObj = new stdClass();
		$stdObj->name = addslashes($this->name);
		$stdObj->details = addslashes($this->details);
		$stdObj->credits = $this->credits;
		$stdObj->uniqueid = $this->uniqueID;
		$stdObj->bcgttypeid = $trackingTypeID;
		$stdObj->bcgtunittypeid = $this->unitTypeID;
		$stdObj->bcgtlevelid = $this->levelID;
		$id = $DB->insert_record('block_bcgt_unit', $stdObj);
		foreach($this->criterias AS $criteria)
		{
			$criteria->insert_criteria($id);
		}
		
		$this->id = $id;
	}
	
	/**
	 * Updates the unit and the criteria
	 * Updates the unit in the db
	 * Then check if the criteria has been removed (is it in the
	 * db but not in the object array?)
	 * It then deletes frm the db if needed. 
	 * It then checks the other criteria in the array and either
	 * updates or inserts.  
	 */
	public function update_unit($updateCriteria = true)
	{
        global $DB;
		$stdObj = new stdClass();
		$stdObj->id = $this->id;
		$stdObj->name = addslashes($this->name);
		$stdObj->details = addslashes($this->details);
		$stdObj->credits = $this->credits;
		$stdObj->uniqueid = $this->uniqueID;
		$stdObj->bcgtunittypeid = $this->unitTypeID;
		$stdObj->bcgtlevelid = $this->levelID;
		$DB->update_record('block_bcgt_unit', $stdObj);
		
		if($updateCriteria)
		{
			$this->check_criteria_removed();
				
			if($this->criterias)
			{
                foreach($this->criterias AS $criteria)
                {
                    $criteria->check_sub_criteria_removed();

                    if($criteria->exists())
                    {
                        $criteria->update_criteria($this->id);
                    }
                    else
                    {
                        $criteria->insert_criteria($this->id);
                    }
                }
			}
		}
	}
    
    /**
	 * BTEC Unit does have an individual award. 
	 */
	public function unit_has_award()
	{
		return true;
	}
    
    /**
	 * Calculates the unit award:
	 * It gets all of the criteria for this unit
	 * Separates them out into different arrays
	 * one for pass, one for merit and one for diss
	 * Then loops over these and tests all of the criteri.
	 * If they have all pass it sets the award to pass
	 * Then moves onto merit.
	 * If they have all merit then it sets the award to merit
	 * Then moves onto diss
	 * If they have all diss then it sets the award to diss
	 * If at any point it fails with some criteria not met it
	 * doesnt move onto the next criteria. 
	 */
	public function calculate_unit_award($qualID)
	{
		$passCriteria = array();
		$meritCriteria = array();
		$distinctionCriteria = array();
		$found = false;
		if($this->criterias)
		{
			//first get all of the criteria that is being used in this unit.
			foreach($this->criterias AS $criteria)
			{                
				if(strpos($criteria->get_name(), "P") === 0)
				{
					$passCriteria[$criteria->get_id()] = $criteria;	
				}
				elseif(strpos($criteria->get_name(), "M") === 0)
				{
					$meritCriteria[$criteria->get_id()] = $criteria;	
				}
				elseif(strpos($criteria->get_name(), "D") === 0)
				{
					$distinctionCriteria[$criteria->get_id()] = $criteria;
				}	
			}	
			
			$merit = false;
			$distinction = false;
			$award = "";
			$rank = 0;
			$pass = $this->check_criteria_award_set_for_met($passCriteria);
			if($pass)
			{
				$found = true;
				$rank = 1;
				$merit = $this->check_criteria_award_set_for_met($meritCriteria);
				if($merit)
				{
					$rank = 2;
					$distinction = $this->check_criteria_award_set_for_met($distinctionCriteria);
					if($distinction)
					{
						//update done on save ??
						$rank = 3;
						//return "DISTINCTION";
					}
					//return "MERIT";
				}
				//return "PASS";
			}
		}
		
		if(!$found)
		{
			//set award to N/S
			$this->userAward = new Award(-1, 'N/S', 0);
			$this->update_unit_award($qualID);
			return null;
		}
		//else get the unit award for the rank and then set it, return it
		//and update the users database record. 
		$awardRecord = $this->get_unit_award($rank);
		if($awardRecord)
		{
            $params = new stdClass();
            $params->award = $awardRecord->award;
            $params->rank = $awardRecord->ranking;
			$award = new Award($awardRecord->id, $params);
			$this->userAward = $award;
			$this->update_unit_award($qualID);
			return $award;
		}			
	}
    
    public function set_award_criteria(Award $award, $qualID)
	{
		//set the award
		$this->userAward = $award;
		$passCriteria = array();
		$passIDs = array();
		$meritCriteria = array();
		$meritIDs = array();
		$distinctionCriteria = array();
		$distinctionIDs = array();
        $l1IDs = array();
        $l1Criteria = array();
		foreach($this->criterias AS $criteria)
		{
			$this->check_criteria_award_level($criteria, 
					$passCriteria, $meritCriteria, $distinctionCriteria, $passIDs, $meritIDs, $distinctionIDs, $l1Criteria, $l1IDs);	
				
			if($criteria->get_sub_criteria())
			{
				foreach($criteria->get_sub_criteria() AS $subCriteria)
				{
					$this->check_criteria_award_level($subCriteria, 
					$passCriteria, $meritCriteria, $distinctionCriteria, $passIDs, $meritIDs, $distinctionIDs, $l1Criteria, $l1IDs);	
				}
			}	
		}
		
		$obj = new stdClass();
		if($award && $award->get_award() == 'Distinction')
		{
			$this->mark_criteria($passCriteria, $qualID);
			$this->mark_criteria($meritCriteria, $qualID);
			$this->mark_criteria($distinctionCriteria, $qualID);
			
			$obj->metCriteria = array_merge($passIDs, array_merge($meritIDs, $distinctionIDs));
			$obj->unMetCriteria = false;
		}
		elseif($award && $award->get_award() == 'Merit')
		{
			$this->mark_criteria($passCriteria, $qualID);
			$this->mark_criteria($meritCriteria, $qualID);
			$this->un_mark_criteria($distinctionCriteria, $qualID);
			
			$obj->metCriteria = array_merge($passIDs, $meritIDs);
			$obj->unMetCriteria = $distinctionIDs;
		}
		elseif($award && $award->get_award() == 'Pass')
		{
			$this->mark_criteria($passCriteria, $qualID);
			$this->un_mark_criteria($meritCriteria, $qualID);
			$this->un_mark_criteria($distinctionCriteria, $qualID);
			
			$obj->metCriteria = $passIDs;
			$obj->unMetCriteria = array_merge($distinctionIDs, $meritIDs);
		}
		else
		{
			$this->un_mark_criteria($passCriteria, $qualID);
			$this->un_mark_criteria($meritCriteria, $qualID);
			$this->un_mark_criteria($distinctionCriteria, $qualID);
			
			$obj->metCriteria = false;
			$obj->unMetCriteria = array_merge($passIDs, array_merge($meritIDs, $distinctionIDs));
		}
		return $obj;
		//return the the array
	}
    
    /**
     * 
     * @param type $criteria
     * @param type $passCriteria
     * @param type $meritCriteria
     * @param type $distinctionCriteria
     * @param type $passIDs
     * @param type $meritIDs
     * @param type $distinctionIDs
     */
    protected function check_criteria_award_level($criteria, 
					&$passCriteria, &$meritCriteria, 
            &$distinctionCriteria, &$passIDs, 
            &$meritIDs, &$distinctionIDs, &$l1Criteria, &$l1IDs)
	{
		$letter = substr($criteria->get_name(), 0, 1);
        if($letter == 'L')
        {
            $l1Criteria[$criteria->get_id()] = $criteria;	
			$l1IDs[$criteria->get_id()] = $criteria->get_id();
        }
		if($letter == 'P')
		{
			$passCriteria[$criteria->get_id()] = $criteria;	
			$passIDs[$criteria->get_id()] = $criteria->get_id();
		}
		elseif($letter == 'M')
		{
			$meritCriteria[$criteria->get_id()] = $criteria;	
			$meritIDs[$criteria->get_id()] = $criteria->get_id();
		}
		elseif($letter == 'D')
		{
			$distinctionCriteria[$criteria->get_id()] = $criteria;
			$distinctionIDs[$criteria->get_id()] = $criteria->get_id();
		}				
	}
    
    /**
     * 
     * @param type $criteriaArray
     * @param type $qualID
     */
    protected function mark_criteria($criteriaArray, $qualID)
	{
		foreach($criteriaArray AS $criteria)
		{
			$criteria->set_criteria_to_met($qualID, true, $this->get_familyID());
		}
	}
	
    /**
     * 
     * @param type $criteriaArray
     * @param type $qualID
     */
	protected function un_mark_criteria($criteriaArray, $qualID)
	{
		foreach($criteriaArray AS $criteria)
		{
			$criteria->set_criteria_to_unknown($qualID, true, $this->get_familyID());
		}
	}
    
    /**
	 * This is called for each of the three award levels (P, M and D)
	 * Checks the array of criteria to see if all of the values assigned to the student
	 * are set to criteria met. 
	 * As soon as one isnt met then it breaks and returns false. 
	 * if all are met it returns true. 
	 * @param unknown_type $criteriaAwardArray
	 */
	protected function check_criteria_award_set_for_met($criteriaAwardArray)
	{
		$awardMet = false;
		foreach($criteriaAwardArray AS $criteria)
		{
			$studentValue = $criteria->get_student_value();
			if($studentValue)
			{
				$criteriaMet = $studentValue->is_criteria_met();
				if($criteriaMet && $criteriaMet == 'Yes')
				{
					$awardMet = true;
				}
				else
				{
					$awardMet = false;
					//if this criteria value means the criteria isnt met,
					//then they cant get this unit award level we are looping through
					//so dont bother with the rest. 
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		return $awardMet;
	}
    
    /**
	 * Used to update the users award they have been given for this unit 
	 * and for the qual passed in
	 * Its possible that the student is doing the unit on two differen quals. 
	 * If the student doesnt have an award for this unit and they did before then
	 * it gets updated accordingly. 
	 * @param unknown_type $qualID
	 */
	protected function update_unit_award($qualID)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_user_unit} AS userunit 
		WHERE userunit.userid = ? AND bcgtqualificationid = ? 
		AND bcgtunitid = ?";
		$userUnits = $DB->get_records_sql($sql, array($this->studentID, $qualID, $this->id));
		if($userUnits)
		{
            $userUnit = end($userUnits);
			$id = $userUnit->id;
			$obj = new stdClass();
			$obj->id = $id;
			if($this->userAward)
			{
				$obj->bcgttypeawardid = $this->userAward->get_id();
			}
			else
			{
				$obj->bcgttypeawardid = 0;
			}
            // THis gets called on every refeesh of page.....
            //logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_UNIT, LOG_VALUE_GRADETRACKER_UPDATED_UNIT_AWARD, $this->studentID, $qualID, null, $this->id, $obj->trackingtypeawardid);
			return $DB->update_record('block_bcgt_user_unit', $obj);
		}
		return false;
	}
    
    
    
    /**
	 *Gets the unit award for BTECS for the ranking 
	 */
	protected function get_unit_award($ranking)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_type_award} AS award 
            WHERE bcgttypeid = ? AND ranking = ?";
		return $DB->get_record_sql($sql, array(BTECQualification::ID, $ranking));
	}
	
	/**
	 * When the unit is created or edited we need to collect the information
	 * inputted by the user of the criteria. 
	 * 
	 * For btecs this is all the P's M's and D's that you want.
	 * 
	 * 
	 * three drop downs to select how many p's, m's and d's the user wants
	 */
	protected function get_submitted_criteria_edit_form_data_actual($subCriteria = false)
	{
		//this builds up an array of criteria objects based on the criteria
		//details inputted to the form. 
		//the unit object will therefore contain only the criteria from the form, 
		//not any that were on the unit before. 
		//those before will be in the database untill update_unit is called.
        //teacher ahas specfied the number they wanted. 
        $noPass = optional_param('noPass', 0, PARAM_INT);
        $noMerit = optional_param('noMerit', 0, PARAM_INT);
        $noDistinction = optional_param('noDiss', 0, PARAM_INT);
        $noL1 = optional_param('noL1', 0, PARAM_INT);
        $criteriaArray = array();
        for($i=1;$i<=$noL1;$i++)
        {
            $criteriaArray = $this->merge_criteria_array($criteriaArray, 
                    $this->check_criteria_from_edit(BTECUnit::L1CRITERIANAME.$i, 
                            -1, $subCriteria));
        }
        for($i=1;$i<=$noPass;$i++)
        {
            $criteriaArray = $this->merge_criteria_array($criteriaArray, 
                    $this->check_criteria_from_edit(BTECUnit::PASSCRITERIANAME.$i, 
                            -1, $subCriteria));
        }
        for($i=1;$i<=$noMerit;$i++)
        {
            $criteriaArray = $this->merge_criteria_array($criteriaArray, 
                    $this->check_criteria_from_edit(BTECUnit::MERITCRITERIANAME.$i, 
                            -1, $subCriteria));
        }

        for($i=1;$i<=$noDistinction;$i++)
        {
            $criteriaArray = $this->merge_criteria_array($criteriaArray, 
                    $this->check_criteria_from_edit(BTECUnit::DISTINCTIONCRITERIANAME.$i, 
                            -1, $subCriteria));
        }
        $this->criterias = $criteriaArray;
	}
	
	/**
	 * This checks the values coming back from the edit unit form
	 * to see if they are an old or new criteria.
     * Returns an array with ONE criteria object in it.
     * ifts a new criteria object then it adds this with no id, 
     * else it sets the key to the criteriaid. 
	 * @param unknown_type $criteria
	 * @param unknown_type $awardID
	 */
	protected function check_criteria_from_edit($criteria, $awardID, $subCriteria = false)
	{
		$criteriaArray = array();
		//for this criteria, has the user set it to be used. 
        $newCriteriaID = -1;
        //is the criteria on the object from a previous load?
        $oldCriteria = $this->get_single_criteria(-1, $criteria);
        if($oldCriteria)
        {
            //if so then its staying there
            $newCriteriaID = $oldCriteria->get_id();
        }
        $details = optional_param('details_'.$criteria, '', PARAM_TEXT);
        $displayName = optional_param($criteria.'_name', '', PARAM_TEXT);
        $params = new stdClass();
        $params->details = $details;
        $params->name = $criteria;
        $params->awardID = $awardID;
        $params->displayname = $displayName;

        //create a criteria object from the criteriaID (if it was used before then the id is from before)
        $criteriaOBJ = new Criteria($newCriteriaID, $params, Qualification::LOADLEVELCRITERIA);
        
        if($subCriteria)
        {
            $subCriteriaArray = array();
            //Lets get the subcriteria
            $noSubCriteria = $_POST['noSubCrit'.$criteria];
            if($noSubCriteria)
            {
                //so this is the number of criteria for say P1
                for($i=1;$i<=$noSubCriteria;$i++)
                {
                    //get a single subcriteria
                    //if it exists then its a previous one we are updating
                    //if it doesnt exist then its a new one.
                    $newSubCriteriaID = -1;
                    $subCriteriaName = $criteria."_$i";
                    $objectName = $criteria.".$i";
                    if($oldCriteria)
                    {
                        //if its an old criteria, then it was a previous load/edit
                        $oldSubCriteria = $oldCriteria->get_single_criteria(-1, $objectName);
                        if($oldSubCriteria)
                        {
                            //then its an old one
                            $newSubCriteriaID = $oldSubCriteria->get_id();	
                        }
                    }

                    if($_POST['sub_details_'.$subCriteriaName] != '')
                    {
                        //now create a subCriteriaObj
                        $params = new stdClass();
                        $params->name = $objectName;
                        $params->details = $_POST['sub_details_'.$subCriteriaName];
                        $params->awardID = $awardID;
                                
                        $subCriteriaObj = new Criteria($newSubCriteriaID, $params, Qualification::LOADLEVELCRITERIA);
                        if($newSubCriteriaID != -1)
                        {
                            //then we have it from before and we know its id
                            $subCriteriaArray[$newSubCriteriaID] = $subCriteriaObj; 
                        }
                        else
                        {
                            array_push($subCriteriaArray, $subCriteriaObj);
                        }
                    }
                }
            }
            $criteriaOBJ->set_sub_criteria($subCriteriaArray);

        }


        if($newCriteriaID != -1)
        {
            //if its an old one overite the criteria in
            //the array with the new one.
            $criteriaArray[$newCriteriaID] = $criteriaOBJ;
        }
        else
        {
            //else new one so just add to the array.
            //we dont know its id yet
            array_push($criteriaArray, $criteriaOBJ);
        }
		return $criteriaArray;
	} 
	
	/**
	 * Returns the grid of criteria to be edited/input by the teacher
	 * When adding a new unit or editing a unit the user needs to be able
	 * to add/edit criteria. This is different per unit type
	 * This is the BTEC's form:
     * The Type is used for: 
	 */
	protected function get_edit_criteria_table_actual($subCriteria = false, $type = '', $level1Criteria = false)
	{		
        global $DB;
        $displayCriteriaTable = true;
        if($level1Criteria)
        {
            //then is it an externally assessed unit?
            if($this->unitTypeID)
            {
                $sql = 'SELECT * FROM {block_bcgt_unit_type} WHERE type = ?';
                $unitType = $DB->get_record_sql($sql, array('Externally Assessed'));
                if($unitType && $unitType->id == $this->unitTypeID)
                {
                    $displayCriteriaTable = false;
                }
            }
        }
        
        if(!$displayCriteriaTable)
        {
            return '';
        }
        
        $jsModule = array(
            'name'     => 'mod_bcgtbtec',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbtec/js/bcgtbtec.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        global $PAGE;
        $PAGE->requires->js_init_call('M.mod_bcgtbtec.bteciniteditunitcriteria', null, true, $jsModule);
		//The user can select to fill the criteria information in
        $noL1 = optional_param('noL1', -1, PARAM_INT);
		$noPass = optional_param('noPass', -1, PARAM_INT);
		$noMerit = optional_param('noMerit', -1, PARAM_INT);
		$noDiss = optional_param('noDiss', -1, PARAM_INT);
						
		$editing = false;
		//Are we editing a unit and therefore want to go and get the correct number of critera
		//of each type out?
		if($this->id != NULL && $this->id != -1)
		{
			$editing = true;
			//Should we be displaying All of them?
			//should we be displaying a set number of the criteria?
			if($subCriteria || (($noPass == -1 || $noPass == 0) && 
			($noMerit == -1 || $noMerit == 0) && ($noDiss == -1 || $noDiss == 0) 
                    && (!$level1Criteria || 
                            ($level1Criteria && ($noL1 == -1 || $noL1 == 0)))))
			{
				//ok so we are editing a criteria from before.
				//we need to work out how many criteria to display
                $loadFromBefore = false;
				if($noPass == -1)
				{
                    //then we are loading from before
                    $loadFromBefore = true;
					$noPass = 0;	
				}
				if($noMerit == -1)
				{
                    $loadFromBefore = true;
					$noMerit = 0;
				}
				if($noDiss == -1)
				{
                    $loadFromBefore = true;
					$noDiss = 0;
				}
                if($noL1 == -1)
                {
                    $loadFromBefore = true;
                    $noL1 = 0;
                }
				$criteriaArray = $this->criterias;
                $noPassBefore = 0;
                $noMeritBefore = 0;
                $noDissBefore = 0;
                $noLevel1Before = 0;
				if($criteriaArray)
				{
					foreach($criteriaArray AS $criteria)
					{
                        if(substr($criteria->get_name(), 0, 3) == BTECUnit::L1CRITERIANAME)
                        {
                            $noLevel1Before++;
                        }
						elseif(substr($criteria->get_name(), 0, 1) == BTECUnit::PASSCRITERIANAME)
						{
							$noPassBefore++;	
						}
						elseif(substr($criteria->get_name(), 0, 1) == BTECUnit::MERITCRITERIANAME)
						{
							$noMeritBefore++;
						}
						elseif(substr($criteria->get_name(), 0, 1) == BTECUnit::DISTINCTIONCRITERIANAME)
						{
							$noDissBefore++;
						}
					}	
				}
                //BUT WHAT HAPPENS IF WE WANT TO MAKE IT 0 NOW???
                if($noLevel1Before != 0 && $noL1 != 0)
                {
                    //if the no of pass that was on the unit before
                    //is more than has been requested then we need to remove them.
                    if($noLevel1Before > $noL1)
                    {
                        $diff = $noLevel1Before - $noL1;
                        $noL1 = $noLevel1Before - $diff;
                    }
                    elseif($noLevel1Before < $noL1)
                    {
                        //If more has been requested than there was before then we need to increas
                        $diff = $noL1 - $noLevel1Before;
                        $noL1 = $noLevel1Before + $diff;
                    }
                }
                elseif(($loadFromBefore && $noL1 == 0 && $noLevel1Before != 0))
                {
                    //basically:
                    //If we have loaded straight up from before then we will have no number in the drop down.
                    //so we can simply set this to 0. 
                    //if we havent loaded from before, then if noL1 is 0, thats because we set it to 0.
                   $noL1 = $noLevel1Before; 
                }
                if($noPassBefore != 0 && $noPass != 0)
                {
                    //if the no of pass that was on the unit before
                    //is more than has been requested then we need to remove them.
                    if($noPassBefore > $noPass)
                    {
                        $diff = $noPassBefore - $noPass;
                        $noPass = $noPassBefore - $diff;
                    }
                    elseif($noPassBefore < $noPass)
                    {
                        //If more has been requested than there was before then we need to increas
                        $diff = $noPass - $noPassBefore;
                        $noPass = $noPassBefore + $diff;
                    }
                }
                elseif($loadFromBefore && $noPass == 0 && $noPassBefore != 0)
                {
                   $noPass = $noPassBefore; 
                }
                if($noMeritBefore != 0 && $noMerit != 0)
                {
                    //if the no of merit that was on the unit before
                    //is more than has been requested then we need to remove them.
                    if($noMeritBefore > $noMerit)
                    {
                        $diff = $noMeritBefore - $noMerit;
                        $noMerit = $noMeritBefore - $diff;
                    }
                    elseif($noMeritBefore < $noMerit)
                    {
                        //If more has been requested than there was before then we need to increase
                        $diff = $noMerit - $noMeritBefore;
                        $noMerit = $noMeritBefore + $diff;
                    }
                }
                elseif($loadFromBefore && $noMerit == 0 && $noMeritBefore != 0)
                {
                   $noMerit = $noMeritBefore; 
                }
                if($noDissBefore != 0 && $noDiss != 0)
                {
                    //if the no of diss that was on the unit before
                    //is more than has been requested then we need to remove them.
                    if($noDissBefore > $noDiss)
                    {
                        $diff = $noDissBefore - $noDiss;
                        $noDiss = $noDissBefore - $diff;
                    }
                    elseif($noDissBefore < $noPass)
                    {
                        //If more has been requested than there was before then we need to increas
                        $diff = $noDiss - $noDissBefore;
                        $noDiss = $noDissBefore + $diff;
                    }
                }
                elseif($loadFromBefore && $noDiss == 0 && $noDissBefore != 0)
                {
                   $noDiss = $noDissBefore; 
                }
                
			}
		}
		//Build the three/four drop downs, L1, Pass, Merit and Diss
		$retval = '';
        $retval .= '<div id="criteriaSelect"><p>'.get_string('bteccritdropdes','block_bcgt').'</p>';
        $passString = 'btecpass';
        $meritString = 'btecmerit';
        $dissString = 'btecdiss';
        if($level1Criteria)
        {
            $passString = 'btecpassl2';
            $meritString = 'btecmeritl2';
            $dissString = 'btecdissl2';
            $retval .= '<label for="noL1">'.get_string('btecl1', 'block_bcgt').' : </label>';
            $retval .= '<select name="noL1" id="noL1">';
            $retval .= $this->build_no_criteria_drop_down(BTECUnit::L1CRITERIAPOSS, $noL1);
            $retval .= '</select>';
        }
        
        $retval .= '<label for="noPass">'.get_string($passString, 'block_bcgt').' : </label>';
		$retval .= '<select name="noPass" id="noPass">';
		$retval .= $this->build_no_criteria_drop_down(BTECUnit::PASSCRITERIAPOSS, $noPass);
		$retval .= '</select>';
		
        $retval .= '<label for="noMerit">'.get_string($meritString, 'block_bcgt').
                ' : </label><select name="noMerit" id="noMerit">';
		$retval .= $this->build_no_criteria_drop_down(BTECUnit::MERITCRITERIAPOSS, $noMerit);
		$retval .= '</select>';
		$retval .= '<label for="noDiss">'.get_string($dissString, 'block_bcgt').
                ' : </label><select name="noDiss" id="noDiss">';
		$retval .= $this->build_no_criteria_drop_down(BTECUnit::DISSCRITERIAPOSS, $noDiss);
		$retval .= '</select>';
        $retval .= '<input type="hidden" name="subCriteria" id="subCriteria" value="'.$subCriteria.'"/>';
				
		$retval .= '</div>';
		if($subCriteria)
		{
			$retval .= '<p>'.get_string('btecblankcriteria','block_bcgt').'</p>';	
		}
		if($editing)
		{
			$retval .= '<p>'.get_string('bteceditcritins','block_bcgt').'</p>';
		}
		
		//Now create the actual form. 
		$retval .= '<div id="btecUnitCriteria">';
        if($level1Criteria && $noL1 > 0)
        {
            //build the text fields for the number of p's wanted
            $retval .= $this->build_criteria_edit_form_section(BTECUnit::L1CRITERIANAME, $noL1, "level1", $subCriteria, $type, $level1Criteria);
        }
        
		if($noPass > 0)
		{
			//build the text fields for the number of p's wanted
			$retval .= $this->build_criteria_edit_form_section(BTECUnit::PASSCRITERIANAME, $noPass, $passString, $subCriteria, $type, $level1Criteria);
		}
		
		if($noMerit > 0)
		{
			//build the text fields for the number of m's wanted
			$retval .= $this->build_criteria_edit_form_section(BTECUnit::MERITCRITERIANAME, $noMerit, $meritString, $subCriteria, $type, $level1Criteria);
		}
		
		if($noDiss > 0)
		{
			//build the text fields for the number of d's wanted
			$retval .= $this->build_criteria_edit_form_section(BTECUnit::DISTINCTIONCRITERIANAME, $noDiss, $dissString, $subCriteria, $type, $level1Criteria);
		}
		$retval .= '</div>';
		
		return $retval; 
	}
    
    /**
	 * Builds the drop downs for the no of criteria wanted
	 * in edit unit form
	 * @param unknown_type $criteriaNames
	 * @param unknown_type $numberCrit
	 */
	protected function build_no_criteria_drop_down($criteriaNo, $numberCrit)
	{
		$retval = "";
		for($i=0;$i<=$criteriaNo;$i++)
		{
            $selected = '';
			if($numberCrit != -1 && $numberCrit == $i)
			{
                $selected = 'selected';
			}
            $retval .= "<option $selected value='$i'>$i</option>";
		}
		return $retval;
	}
	
	/**
	 * Builds the text boxes for the edit criteria form
	 * This build the number depending on the drop downs from
	 * the no of each criteria wanted
	 * CriteriaArray are the list of pass, merit and diss criteria.
	 * @param unknown_type $criteriaArray
	 * @param unknown_type $noCriteria
	 * @return string
	 */
	protected function build_criteria_edit_form_section($criteriaName, $noCriteria, $heading, $subCriteria = false, $type = '', $level1Criteria)
	{
        global $CFG;
		$retval = '';
		$retval .= '<div id="btec'.$heading.'Criteria">'.
                '<h3 class="criteriaHeading">'.get_string($heading, 'block_bcgt').'</h3>';
        if($heading == 'btecmerit' || $heading == 'btecmeritl2')
        {
            $retval .= '<input type="button" name="pCopyMerit" value="Copy From Pass" id="pCopyMerit" class="bcgtFormButton" />';
        }
        elseif($heading == 'btecdiss' || $heading == 'btecdissl2')
        {  
            $retval .= '<input type="button" name="mCopyDiss" value="Copy From Merit" id="mCopyDiss" class="bcgtFormButton" />';
        }
            $retval .= '<table id="btecCrit'.$criteriaName.'" class="btec'.$type.'Criteria" align="center">'.
                '<tr><th>'.get_string('criterianame','block_bcgt').'</th>'.
                '<th>'.get_string('criteriadetails', 'block_bcgt').'</th></tr><tbody>';
			for($i=1;$i<=$noCriteria;$i++)
			{
				$criteriaFound = false;
				$details='';
                $displayName = '';
				$subCriterias = null;
				if($criteria = $this->get_single_criteria(-1, $criteriaName.$i))
				{
					$criteriaFound = true;
					$details = $criteria->get_details();
                    $displayName = $criteria->get_display_name();
					if($subCriteria)
					{
						$subCriterias = $criteria->get_sub_criteria();
					}
				}
                $crieriaNameContents = $criteriaName.$i;
                if($level1Criteria)
                {
                    $customName = optional_param($criteriaName.$i.'_name', $criteriaName.$i, PARAM_TEXT);
                    if($criteriaFound && $customName == '')
                    {
                        //we have found a previous criteria and the current criteria is the same as the default
                        //can we find from before?
                        $customName = $displayName;
                    }
                    elseif($customName == '')
                    {
                        $customName = $criteriaName.$i;
                    }
                    $crieriaNameContents = '<input class="criteriaCustomName" type="text" name="'.$criteriaName.$i.'_name" value="'.$customName.'"/>';
                }
				$retval .= '<tr id="'.$criteriaName.$i.'"><td>'.$crieriaNameContents.'</td><td>';
                if($details == '')
                {
                    //if the details are blank can we get them if the form has been reloaded?
                    $details = optional_param('details_'.$criteriaName.$i, '', PARAM_TEXT);
                }
				$retval .= '<textarea cols="20" rows="3" '.
                        'name="details_'.$criteriaName.$i.'" id="details_'.$criteriaName.$i.'">'.$details.'</textarea>';
				$retval .= '</td>';
                if($subCriteria)
                {
                    $retval .= '<td><img class="actionImageAddB" id="sA_'.$criteriaName.$i.'_0" alt="Insert New Below" '.
                        'title="Insert New Below" src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec/pix/greenPlus.png"'.
                        '"/></td>';
                }
				$retval .= '</tr>';		
				if($subCriteria)
				{
					$retval .= '<tr><td></td><td>';
					$retval .= '<table class="subCriteria" '.
                            'id="'.$criteriaName.$i.'_sub" align="center">';
					$number = 0;
					$oldNumber = 0;
                    //if we already have them as we are editing:
                    $blankRow = true;
					if($subCriterias)
					{
                        require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
                        $criteriaSorter = new CriteriaSorter();
                        usort($subCriterias, array($criteriaSorter, "ComparisonDelegateByName"));
						//loop over each and output the row.
                        foreach($subCriterias AS $subCriteria)
						{
							$fullNumber = $subCriteria->get_name();
							$period = strpos($fullNumber, ".");
							$number = substr($fullNumber, $period+1);
							//its possible that we have .2 and .4 only
							//so we want to build up .1 and .3 as blank ones. 
							if(($number -1) != $oldNumber)
							{
								$tempNumber = $number-1;
								for($k=1;$k<=($tempNumber-$oldNumber);$k++)
								{
									$retval .= $this->build_sub_criteria_row($criteriaName.$i, '', $oldNumber+$k);
								}
							}
							$retval .= $this->build_sub_criteria_row($criteriaName.$i, $subCriteria->get_details(), $number);
							$oldNumber = $number;
						}
                        $blankRow = false;
					}
                    elseif(isset($_POST['noSubCrit'.$criteriaName.$i]))
                    {
                        //then we were creating one and we lost what we were doing before
                        $noSubs = $_POST['noSubCrit'.$criteriaName.$i];
                        for($j=1;$j<=$noSubs;$j++)
                        {
                            $retval .= $this->build_sub_criteria_row($criteriaName.$i, $_POST['sub_details_'.$criteriaName.$i.'_'.$j], $j);
                            $blankRow = false;
                        }
                        $number = $noSubs;
                    }
                    else{
                        $number = 1;
                    }
                    if($blankRow)
                    {    
                        $retval .= $this->build_sub_criteria_row($criteriaName.$i, "", $number);
					}
					//build an extra blank row	
					$retval .= '</table></td></tr>';//end sub table
					$retval .= '<tr class="rowDivider"><td>';
                    $retval .= '<input type="hidden" id="noSubCrit'.$criteriaName.$i.'"'.
                            'name="noSubCrit'.$criteriaName.$i.'" value="'.$number.'"/>';
                    $retval .= '</td></tr>';	
				}
			}
			$retval .= '</tbody></table></div>';
		return $retval;
	}
	
	protected function build_sub_criteria_row($criteriaName, $details, $number)
	{
		$retval = "";
		global $CFG;
		$retval .= '<tr class="subcriteria">';
		$retval .= '<td>'.$criteriaName.'.'.$number.'</td>';
		$retval .= '<td><textarea class="subCriteriaDetails subCriteriaDetailsLast" id="ta_'.$criteriaName.'_'.$number.'" cols="15"'.
                'rows="3" name="sub_details_'.$criteriaName.'_'.$number.'" '.
                '">'.
                    $details.'</textarea></td>';
		$retval .= '<td><img class="actionImageAdd" id="sA_'.$criteriaName.'_'.$number.'" alt="Insert New Below" '.
                'title="Insert New Below" src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec/pix/greenPlus.png"'.
                '"/>'.
                '<img class="actionImageDel" id="sD_'.$criteriaName.'_'.$number.'" title="Delete This Row" alt="Delete This Row"'.
                'src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec/pix/redX.png"/></td>';
		$retval .= '</tr>';
		
		return $retval;
	}
    
    public function get_default_credits()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_unit_type_att} WHERE bcgtlevelid = ? 
            AND bcgttypefamilyid = ? AND name = ?";
        $result = $DB->get_record_sql($sql, array($this->levelID, 
            BTECQualification::FAMILYID, BTECUNIT::DEFAULTUNITCREDITSNAME));
        if($result)
        {
            return $result->value;
        }
        return 0;
    }
    
    public function get_student_qual_award($userID, $qualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_award} useraward 
            JOIN {block_bcgt_target_breakdown} breakdown ON breakdown.id = useraward.bcgtbreakdownid 
            WHERE useraward.type = ? AND useraward.userid = ? AND useraward.bcgtqualificationid = ?";
        return $DB->get_record_sql($sql, array('Predicted', $userID, $qualID));
    }
    
    
    public function print_grid($qualID){
        
        global $CFG, $COURSE;
        $context = context_course::instance($COURSE->id);
        $courseID = optional_param('cID', -1, PARAM_INT);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        echo "<!doctype html><html><head>";
        echo "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/print.css'>";
        echo load_javascript(false, true);
        
        $logo = get_config('bcgt', 'logoimgurl');
        
        echo "</head><body style='background: url(\"{$logo}\") no-repeat;'>";
                
        echo "<div class='c'>";
            echo "<h1>{$this->get_display_name()}</h1>";

            echo "<br><br>";
            
            // Key
            echo "<div id='key'>";
                echo BTECQualification::get_grid_key();
            echo "</div>";
            
            echo "<br><br>";
            
            echo "<table id='printGridTable'>";
            
            // Header
           
            $criteriaNames = $this->get_used_criteria_names();

            // Can't sort by ordernum here because could be different between units, can only do this on unit grid
            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
            $criteriaSorter = new CriteriaSorter();
            usort($criteriaNames, array($criteriaSorter, "ComparisonDelegateByArrayNameLetters"));

            $headerObj = $this->get_unit_grid_header($criteriaNames, 's', true, $context);
            $criteriaCountArray = $headerObj->criteriaCountArray;
            $this->criteriaCount = $criteriaCountArray;
            
            echo $headerObj->header;
            
            $subCriteriaArray = null;
            if ($this->has_sub_criteria()){
                $subCriteriaArray = $this->get_used_sub_criteria_names($criteriaNames);
            }
            
            $studentsArray = get_users_on_unit_qual($this->id, $qualID);
            
            if ($studentsArray)
            {
            
                foreach($studentsArray as $student)
                {
                
                    if(isset($student->unit))
                    {
                        //then we are coming from the session and the unit object has aleady
                        //been loaded
                        $studentUnit = $student->unit;
                    }
                    else
                    {
                        $loadParams = new stdClass();
                        $loadParams->loadLevel = Qualification::LOADLEVELALL;
                        $loadParams->loadAward = true;
                        $studentUnit = Unit::get_unit_class_id($this->id, $loadParams);
                        $studentUnit->load_student_information($student->id, $qualID, $loadParams);
                        $student->unit = $studentUnit;

                        //then we want to save the object to the session
                        //but we also just want to sstudent load on this object (as each time it will
                        //clear it down)
                    }
                    
                    // Units & Grades
                    if($studentUnit->is_student_doing())
                    {	

                        echo "<tr>";

                        echo "<td></td><td></td>";
                        
                        echo $this->build_unit_grid_students_details($student, $qualID, 
                                    $context);
                       


                        //work out the students unit award
                        $stuUnitAward = $studentUnit->get_user_award();
                        $award = '';
                        $rank = '';
                        if($stuUnitAward)
                        {
                            $rank = $stuUnitAward->get_rank();
                            $award = $stuUnitAward->get_award();
                        }
                        
                        echo "<td><span id='unitAwardAdv_$student->id'>".$award."</span></td>";


                        if($criteriaNames)
                        {
                            $criteriaCount = 0;
                            $previousLetter = '';

                            foreach($criteriaNames AS $criteriaName)
                            {	
                                
                                
                                 $letter = substr($criteriaName, 0, 1);
                                 if(($previousLetter != '' && $previousLetter != $letter))
                                 {
                                    echo "<td></td>";
                                 }
                                $previousLetter = $letter;
                                
                                if($studentCriteria = $studentUnit->get_single_criteria(-1, $criteriaName))
                                {
                                    echo "<td>".$this->set_up_criteria_grid($studentCriteria, '', $student, 
                                            null, false, false, '', array(), $qualID)."</td>";
                                    
                                    if($studentCriteria->get_sub_criteria())
                                    {
                                        $subCriterias = $studentCriteria->get_sub_criteria();
                                        if($subCriterias)
                                        {
                                            $i = 0;
                                            foreach($subCriterias AS $subCriteria)
                                            {
                                               
                                               echo "<td>".$this->set_up_criteria_grid($subCriteria, 
                                                        ' subCriteria subCriteria_'.$criteriaName.'', 
                                                        $student, null, false, 
                                                        false, 
                                                        null, array(), $qualID)."</td>";                                                
                                                
                                            }
                                        }//end if subCriterias found	
                                    }//end if displaying sub criterias
                                }//end if the criteria found
                            }//end for each criteria Name
                        }//end if criteriaNames
                        

                        echo "</tr>";

                    }
                
                }
                
            }
            
            
            echo "</table>";
            echo "</div>";
            
            //echo "<br class='page_break'>";
            
            // Comments and stuff
            // TODO at some point
            
            echo "<script> $('a').contents().unwrap(); $('.studentUnitInfo').remove(); </script>";
            
        
        echo "</body></html>";
        
    }
    
    
    private function get_used_sub_criteria_names($criteriaNames)
	{
        global $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
        $criteriaSorter = new CriteriaSorter();                
		$subCriteriaArray = array();
        
        foreach($criteriaNames AS $criteriaName)
        {
            $criteria = $this->get_single_criteria(-1, $criteriaName);
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
    
    
    
    
    
    
    
    public function export_unit_grid($qualID)
    {
                
        global $CFG, $DB, $USER;
        
        require_once $CFG->dirroot . '/blocks/bcgt/classes/sorters/CriteriaSorter.class.php';
        
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
        
        
        // Get possible values
        $possibleValues = BTECQualification::get_possible_values(BTECQualification::ID);
        $possibleValuesArray = array();
        foreach($possibleValues as $possibleValue){
            $possibleValuesArray[] = $possibleValue->shortvalue;
        }
        
        // Have a worksheet for each unit
        $qualificationID = ($qualID) ? $qualID : -1;
        $courseID = optional_param('cID', -1, PARAM_INT);
        $groupID = optional_param('grID', -1, PARAM_INT);
        
        $students = get_users_on_unit_qual($this->id, $qualificationID, $courseID, $groupID);
        
        $criteria = $this->get_used_criteria_names();
                
        // Sort
        $criteriaSorter = new CriteriaSorter();
        usort($criteria, array($criteriaSorter, "ComparisonDelegateByArrayNameLetters"));
        
        $subCriteria = $this->get_used_sub_criteria_names($criteria);
        $criteria = bcgt_flatten_sub_criteria_array($subCriteria, array());
                
        
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
       
        // Set current sheet
        $objPHPExcel->createSheet($sheetIndex);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $objPHPExcel->getActiveSheet()->setTitle("Grades");

        $rowNum = 1;

        // Headers
        $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", "ID");
        $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", "First Name");
        $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", "Last Name");
        $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", "Username");

        $letter = 'E';

        

        if ($criteria)
        {
            foreach($criteria as $criterion)
            {
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("{$letter}{$rowNum}", $criterion, PHPExcel_Cell_DataType::TYPE_STRING);
                $letter++;
            }
        }

        $rowNum++;

        if ($students)
        {

            foreach($students as $student)
            {

                // Load student into
                $this->load_student_information($student->id, $qualificationID, $loadParams);
                
                $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", $student->id);
                $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", $student->firstname);
                $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", $student->lastname);
                $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", $student->username);
                
                $letter = 'E';

                if ($this->is_student_doing())
                {
                
                    // Loop criteria
                    if ($criteria)
                    {
                        foreach($criteria as $criterion)
                        {

                            $studentCriterion = $this->get_single_criteria(-1, $criterion);
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
                                //$objValidation->setPromptTitle('Choose a value');
                                //$objValidation->setPrompt('Please choose a criteria value from the list');
                                $objValidation->setFormula1('"'.implode(",", $possibleValuesArray).'"');

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
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);

        }

        // Freeze rows and cols (everything to the left of E and above 2)
        $objPHPExcel->getActiveSheet()->freezePane('E2');
        
        
        
        
        // Now do it again for comments on worksheet 2
 
        $sheetIndex = 1;
        
        // Set current sheet
        $objPHPExcel->createSheet($sheetIndex);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $objPHPExcel->getActiveSheet()->setTitle("Comments");

        $rowNum = 1;

        // Headers
        $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", "ID");
        $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", "First Name");
        $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", "Last Name");
        $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", "Username");

        $letter = 'E';

        if ($criteria)
        {
            foreach($criteria as $criterion)
            {
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("{$letter}{$rowNum}", $criterion, PHPExcel_Cell_DataType::TYPE_STRING);
                $letter++;
            }
        }

        $rowNum++;

        if ($students)
        {

            foreach($students as $student)
            {

                // Load student into
                $this->load_student_information($student->id, $qualificationID, $loadParams);
                
                $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", $student->id);
                $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", $student->firstname);
                $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", $student->lastname);
                $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", $student->username);
                
                $letter = 'E';

                if ($this->is_student_doing())
                {
                
                    // Loop criteria
                    if ($criteria)
                    {
                        foreach($criteria as $criterion)
                        {

                            $studentCriterion = $this->get_single_criteria(-1, $criterion);
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
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);

        }

        // Freeze rows and cols (everything to the left of E and above 2)
        $objPHPExcel->getActiveSheet()->freezePane('E2');
        

        // End
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        ob_clean();
        $objWriter->save('php://output');
        exit;                
        
    }
    
    
    
    
     public function import_unit_grid($qualID, $file, $confirm = false){
        
        global $CFG, $DB, $USER;
                
        $now = time();
                
        $return = array();
        $output = "";
        
        $cnt = 0;
        
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
            
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELALL;
            $loadParams->loadAward = true;
            
            
            $output .= "file loaded successfully ...<br>";
            
            $objPHPExcel->setActiveSheetIndex(0);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            
            $output .= " loaded worksheet - " . $objWorksheet->getTitle() . " ...<br>";
            
            $commentsWorkSheet = $objPHPExcel->getSheet(1);
            
            $output .= " loaded worksheet - " . $commentsWorkSheet->getTitle() . " ...<br>";
            
            $lastCol = $objWorksheet->getHighestColumn();
            $lastCol++;
            $lastRow = $objWorksheet->getHighestRow();
            
            // Get possible values
            $possibleValues = BTECQualification::get_possible_values(BTECQualification::ID);
            $possibleValuesArray = array();
            foreach($possibleValues as $possibleValue){
                $possibleValuesArray[$possibleValue->id] = $possibleValue->shortvalue;
            }
            
            
            // Loop through rows to get students
            for ($row = 2; $row <= $lastRow; $row++)
            {

                $output .= "processing row {$row} ...<br>";
                
                // Loop columns
                $rowClass = ( ($row % 2) == 0 ) ? 'even' : 'odd';

                for ($col = 'A'; $col != $lastCol; $col++){

                    $cellValue = $objWorksheet->getCell($col . $row)->getCalculatedValue();

                    if ($col == 'A'){
                        $userID = $cellValue;
                        $this->load_student_information($userID, $qualID, $loadParams);
                        $output .= "loaded student " . fullname($this->student) . " ({$this->student->username}) ...<br>";
                        continue; // Don't want to print the id out
                    }


                    if ($col != 'A' && $col != 'B' && $col != 'C' && $col != 'D'){

                        $value = $cellValue;

                        // Get studentCriteria to see if it has been updated since we downloaded the sheet
                        $criteriaName = $objWorksheet->getCell($col . "1")->getCalculatedValue();
                        $studentCriterion = $this->get_single_criteria(-1, $criteriaName);
                        
                        $output .= "attempting to set value for criterion {$criteriaName} to {$value} ... ";

                        if ($studentCriterion)
                        {

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

                                $studentCriterion->save_student($qualID, false);
                                $output .= "success - criterion updated ...<br>";
                                $cnt++;

                            }
                            else
                            {
                                $output .= "error - {$value} is an invalid criteria value ...<br>";
                            }

                        } 
                        else
                        {
                            $output .= "error - student criteria could not be loaded ...<br>";
                        }
                        
                    }

                }
                
                // recalculate student unit award
                $this->calculate_unit_award($qualID);

            }
            
            $output .= "end of worksheet ...<br>";
            $output .= "end of process - {$cnt} criteria updated updated<br>";
            
            
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
            $saveFile = bcgt_save_file($file['tmp_name'], $qualID . '_' . $this->id . '_' . $now . '.xlsx', "import_unit_grids");
            if (!$saveFile){
                print_error('Could not save uploaded file. Either the save location does not exist, or is not writable. (moodledata - bcgt/import_unit_grids)');
            }    
            
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELALL;
            $loadParams->loadAward = true;
                     
            $unix = $objPHPExcel->getProperties()->getCreated();
                        
            $objPHPExcel->setActiveSheetIndex(0);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            
            $lastCol = $objWorksheet->getHighestColumn();
            $lastCol++;
            $lastRow = $objWorksheet->getHighestRow();
            
            $commentWorkSheet = $objPHPExcel->getSheet(1);
            
            
            
            // See if anything has been updated in the DB since we downloaded the file
            $updates = $DB->get_records_sql(
                    "SELECT uc.*, c.name
                     FROM {block_bcgt_user_criteria} uc
                     INNER JOIN {block_bcgt_criteria} c ON c.id = uc.bcgtcriteriaid
                     WHERE c.bcgtunitid = ?
                     AND uc.bcgtqualificationid = ? 
                     AND ( dateset > ? OR dateupdated > ? ) ", 
                        array($this->id, $qualID, $unix, $unix));

            if ($updates)
            {
                
                $output .= "<div class='importwarning'>";
                    $output .= "<b>".get_string('warning').":</b><br><br>";
                    $output .= "<p>".get_string('importwarning', 'block_bcgt')."</p>";
                    foreach($updates as $update)
                    {
                        
                        $value = $DB->get_record("block_bcgt_value", array("id" => $update->bcgtvalueid));
                        if ($update->dateupdated > $update->dateset){
                            $updateTime = $update->dateupdated;
                            $updateUser = $DB->get_record("user", array("id" => $update->updatedbyuserid));
                        } else {
                            $updateTime = $update->dateset;
                            $updateUser = $DB->get_record("user", array("id" => $update->setbyuserid));
                        }

                        $student = $DB->get_record("user", array("id" => $update->userid));
                        
                        $output .= $update->name . " for user ".fullname($student)." ({$student->username}) was updated to: " . $value->value . ", at: " . date('d-m-Y, H:i', $updateTime) . ", by: ".fullname($updateUser)." ({$updateUser->username})<br>";
                        
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
            
            $output .= "<h2 class='c'>".$this->get_display_name()."</h2>";
            
            $output .= "<div class='importgriddiv'>";
            $output .= "<table class='importgridtable'>";
            
                $output .= "<tr>";
                
                    $output .= "<th>".get_string('name')."</th>";
                    $output .= "<th>".get_string('username')."</th>";
                    
                    for ($col = 'E'; $col != $lastCol; $col++){

                        $cellValue = $objWorksheet->getCell($col . "1")->getCalculatedValue();
                        $output .= "<th>{$cellValue}</th>";

                    }
                    
                $output .= "</tr>";
                
                // Loop through rows to get students
                for ($row = 2; $row <= $lastRow; $row++)
                {
                    
                    // Loop columns
                    $rowClass = ( ($row % 2) == 0 ) ? 'even' : 'odd';

                    $output .= "<tr class='{$rowClass}'>";

                        for ($col = 'A'; $col != $lastCol; $col++){
                            
                            $critClass = '';
                            $currentValue = 'N/A';                                        
                            $cellValue = $objWorksheet->getCell($col . $row)->getCalculatedValue();

                            if ($col == 'A'){
                                $userID = $cellValue;
                                $this->load_student_information($userID, $qualID, $loadParams);
                                $output .= "<td>".fullname($this->student)."</td>";
                                $output .= "<td>{$this->student->username}</td>";
                                continue; // Don't want to print the id out
                            }
                                                        
                            if ($col != 'A' && $col != 'B' && $col != 'C' && $col != 'D'){

                                $value = $cellValue;
                                
                                $critClass .= 'crit ';

                                // Get studentCriteria to see if it has been updated since we downloaded the sheet
                                $criteriaName = $objWorksheet->getCell($col . "1")->getCalculatedValue();
                                $studentCriterion = $this->get_single_criteria(-1, $criteriaName);
                                
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

                                    $output .= "<td title='{$comment}' class='{$critClass}' currentValue='{$currentValue}' unix='{$unix}' dateset='{$critDateSet}' dateupdated='{$critDateUpdated}'><small>{$cellValue}</small></td>";
                                
                                } 
                                else
                                {
                                    $output .= "<td></td>";
                                }

                            }

                        }

                    $output .= "</tr>";

                }
                
                
            
            $output .= "</table>";
            $output .= "</div>";
            
            $output .= "<form action='' method='post' class='c'>";
                $output .= "<input type='hidden' name='qualID' value='{$qualID}' />";
                $output .= "<input type='hidden' name='unitID' value='{$this->id}' />";
                $output .= "<input type='hidden' name='now' value='{$now}' />";
                $output .= "<input type='submit' class='btn' name='submit_confirm' value='".get_string('confirm')."' />";
                $output .= str_repeat("&nbsp;", 8);
                $output .= "<input type='button' class='btn' onclick='window.location.href=\"{$CFG->wwwroot}/blocks/bcgt/grids/student_grid.php?sID={$this->studentID}&qID={$this->id}\";' value='".get_string('cancel')."' />";

            $output .= "</form>";
            
              
        }
        
        $return['summary'] = $cnt;
        $return['output'] = $output;
        return $return;
        
    }
    
    
    public function order_criteria_ids($criteria)
    {
        global $CFG;
        require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbtec/classes/sorters/BTECCriteriaSorter.class.php';
        $sorter = new BTECCriteriaSorter();
        usort($criteria, array($sorter, "ComparisonDelegateByNameArray"));       
        return $criteria;
    }
    
    
	
}

?>
