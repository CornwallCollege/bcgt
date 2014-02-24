<?php

/**
 * <title>
 * 
 * @copyright 2013 Bedford College
 * @package Bedford College Electronic Learning Blue Print (ELBP)
 * @version 1.0
 * @author Conn Warwicker <cwarwicker@bedford.ac.uk> <conn@cmrwarwicker.com>
 * 
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 * 
 */

require_once 'CGQualification.class.php';
require_once $CFG->dirroot . '/blocks/bcgt/classes/core/Range.class.php';

/**
 * 
 */
class CGHBVRQQualification extends CGQualification {
 
    const ID = 10;
    const FAMILYID = 4;
    const NAME = 'CG HB VRQ';
    
    
    public static function get_instance($qualID, $params, $loadParams)
    {
        return new CGHBVRQQualification($qualID, $params, $loadParams);
    }
    
    /**
	 * Returns the human type name
	 */
	public function get_type()
    {
        return CGHBVRQQualification::NAME;
    }
	
	/**
	 * Returns the id of the type not the qual
	 */
	public function get_class_ID()
    {
        return CGHBVRQQualification::ID;
    }
    
    
    /**
	 * Returns the id of the type not the qual
	 */
	public function get_family_ID()
    {
        return CGHBVRQQualification::FAMILYID;
    }
    
    
    /**
     * Returns the family name
     */
    public function get_family()
    {
        return CGHBVRQQualification::NAME;
    }
    
    public function has_final_grade()
    {
        return true;
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
		$targetQualID = parent::get_target_qual(CGHBVRQQualification::ID);
		$dataobj->bcgttargetqualid = $targetQualID;
        $dataobj->pathwaytypeid = $this->pathwayTypeID;
        
        
		$id = $DB->insert_record("block_bcgt_qualification", $dataobj);
		$this->id = $id;
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_INSERTED_QUAL, null, $this->id, null, null, null);
	}
    
    
    /**
     * Displays the student Grid
     */
    public function display_student_grid($fullGridView = true, $studentView = true, $strippedView = false)
    {
        
        global $COURSE, $PAGE, $CFG, $OUTPUT;
        
        $this->update_any_beyond_target();
        
        $grid = optional_param('g', 's', PARAM_TEXT);
        $late = optional_param('late', false, PARAM_BOOL);
        
        $retval = '';
        $retval .= '<div>';
        
        if (!$strippedView)
        {
        
            $retval .= "<input type='submit' id='viewsimple' class='gridbuttonswitch viewsimple' name='viewsimple' value='View Simple'/>";
            $retval .= "<input type='submit' id='viewadvanced' class='gridbuttonswitch viewadvanced' name='viewadvanced' value='View Advanced'/>";
            $retval .= "<br>";  
            $courseID = optional_param('cID', -1, PARAM_INT);
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
        
        }
        
//        if ($strippedView)
//        {
//            $retval .= "<a href='".$CFG->wwwroot."/blocks/bcgt/grids/print_grid.php?sID={$this->studentID}&qID={$this->id}' target='_blank'><img src='".$OUTPUT->pix_url('t/print', 'core')."' alt='' /> ".get_string('printgrid', 'block_bcgt')."</a>";
//        }
        
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
        
        $jsModule = array(
            'name'     => 'mod_bcgtcg',
            'fullpath' => '/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        //
                
        if ($strippedView){
            $retval .= <<< JS
            <script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js'></script>
JS;
        } else {
            $PAGE->requires->js_init_call('M.mod_bcgtcg.inithbvrqstudgrid', array($this->id, $this->studentID, $grid), true, $jsModule);
        }
        
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        $retval .= load_javascript(true, $strippedView);
        $retval .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/css/start/jquery-ui-1.10.3.custom.min.css' />";
        $retval .= "
		<div class='gridKey adminRight'>";
		if($studentView)
		{
			$retval .= "<h2>Key</h2>";
			//Are we looking at a student or just the actual criteria for the grid.
			//if students then get the key that tells everyone what things stand for
			$retval .= CGHBVRQQualification::get_grid_key();
		}
		$retval .= "</div>";
        
        $retval .= "<br style='clear:both;' />";
        
        //the grid -> ajax
        $retval .= '<div id="cgStudentGrid">';
        
        if($this->has_final_grade() && $studentView)
		{
            //>>BEDCOLL TODO this need to be taken from the qual object
            //as foundatonQual is different
            $retval .= '<table id="summaryAwardGrades">';
			$retval .= $this->show_predicted_qual_award($this->predictedAward, $context);
            $retval .= '</table>';
            
        }
        
        $retval .= "<div id='studentGridDiv' class='studentGridDiv ".
        $grid."StudentGrid tableDiv'><br><br><br><table align='center' class='student_grid".
                $grid."FixedTables CGHB' id='CGStudentGrid'>";
        
		//we will reuse the header at the bottom of the table.
		$totalCredits = $this->get_students_total_credits($studentView);
		//for all of the units on this qual, lets check which crieria names
		//have actually been used. i.e. dont show P17 if no unit has a p17
		$criteriaNames = $this->get_used_criteria_names_();
        
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
        $retval .= "</table>";
        
        // Qual Comment
        if ($this->comments == '') $this->comments = 'N/A';
        $retval .= "<div id='qualComment'><br><fieldset><legend><h2>Qualification Comments</h2></legend><br>".nl2br( htmlentities($this->comments, ENT_QUOTES) )."</fieldset></div>";
        
        if($this->has_final_grade() && $studentView && !$editing)
		{
            //>>BEDCOLL TODO this need to be taken from the qual object
            //as foundatonQual is different
            $retval .= '<table id="summaryAwardGrades">';
			$retval .= $this->show_predicted_qual_award($this->predictedAward, $context);
            $retval .= '</table>';
            
        }
        
        $retval .= $this->get_required_settings();
        
        $retval .= "</div>";
        $retval .= '</div>';
        $retval .= '</div>';
        
        if ($strippedView){
            $retval .= " <script>$(document).ready( function(){
                M.mod_bcgtcg.inithbvrqstudgrid(Y, {$this->id}, {$this->studentID}, '{$grid}');
            } ); </script> ";
        }
        
        return $retval;
        
    }
    
    public function get_criteria_met_value()
    {
        global $DB;
		$sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $params = array(CGHBVRQQualification::ID, 'A');
		$record = $DB->get_record_sql($sql, $params);
		if($record)
		{
			return $record->id;
		}
		return -1;
    }
    
    protected function get_used_criteria_names_()
    {
        
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
                    }
                }
        
            }
        
        }
        
        $this->usedCriteriaNames = array_unique($return);
        return $this->usedCriteriaNames;
        
    }
    
    private function get_criteria_header($criteriaNames)
    {
        
        $output = "";
        $totalCellCount = 0;
        
        foreach($criteriaNames as $criteriaName)
        {

            // Count how many ranges are on this task, so we know what colspan to use
            $max = $this->max_all_ranges_of_task_name($criteriaName);
            $tName = str_replace(" ", "_", htmlentities($criteriaName, ENT_QUOTES));
            
            if ($max > 1){
                $output .= "<th class='toggleTD_{$tName}' colspan='{$max}' defaultcolspan='{$max}'><a class='taskName' href='#' onclick='toggleOverallTasks(\"{$tName}\");return false;'>{$criteriaName}</a></th>";
            } else {
                $output .= "<th colspan='{$max}' defaultcolspan='{$max}'>{$criteriaName}</th>";
            }
            
            $totalCellCount++;

        }

        $headerObj = new stdClass();
        $headerObj->header = $output;
        $headerObj->criteriaCountArray = array();
        $headerObj->totalCellCount = $totalCellCount;
        return $headerObj;

    }
    
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
                        
            // If qual has % completions enabled
            if($this->has_percentage_completions() && !$printGrid && $studentView){
                $header .= "<th>% Complete</th>";
                $totalCellCount++;
            }
		}	  
        
        $headerObj = $this->get_criteria_header($criteriaNames);
		$header .= $headerObj->header; 
        
        // IV
        $header .= "<th>IV</th>";
        
		$header .= "</tr></thead>";
		$headerObj->header = $header;
		return $headerObj;
	}
    
    
    public function get_student_grid_data($advancedMode, $editing, 
            $studentView)
    {
                
         global $DB, $OUTPUT;
         
        $retval = "";
         
        $subCriteria = $this->has_sub_criteria();
        //$this->load_student();
        $criteriaCountArray = $this->criteriaCount;
        $user = $DB->get_record_sql('SELECT * FROM {user} WHERE id = ?', array($this->studentID));
        $subCriteriaArray = false;
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
        
        $rowCount = 0;
                
        foreach($units AS $unit)
        {
            
            if(($studentView && $unit->is_student_doing()) || !$studentView)
			{	
            
                // FIrstly we want the header for this unit
                $retval .= $unit->get_unit_header($criteriaNames, $this, $editing, $studentView, 'student');
                
                
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
                                
                $retval .= "<tr>";
                
                // Unit Comment
                $getComments = $unit->get_comments();
                
                $cellID = "cmtCell_U_{$unit->get_id()}_S_{$user->id}_Q_{$this->get_id()}";
                
		        
                $username = htmlentities( $user->username, ENT_QUOTES );
                $fullname = htmlentities( fullname($user), ENT_QUOTES );
                $unitname = htmlentities( $unit->get_name(), ENT_QUOTES);
                $critname = "N/A";   
                
                $retval .= "<td title='title'>";

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
                
                $retval .= "</td>";
                
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
                
                $retval .= "<td>";
                                
				$retval .= "<span id='uID_".$unit->get_id()."' title='title' class='uNToolTip unitName".$unit->get_id()."' studentID='{$this->studentID}' unitID='{$unit->get_id()}'>".$link."</span>";
                $retval .= "<span style='color:grey;font-size:85%;'><br />(".$unit->get_credits()." Credits)</span>";	
				
                //if has capibility
				if(has_capability('block/bcgt:editunit', $context))
				{		
                    $retval .= "<a class='editing_update editUnit' href='{$CFG->wwwroot}/blocks/bcgt/forms/edit_unit.php?unitID=".$unit->get_id()."' title = 'Update Unit'>
					<img class='iconsmall editUnit' alt='Update Unit' src='".$OUTPUT->pix_url("t/edit", "core")."'/></a>";
				}
                
                $retval .= "<div id='unitTooltipContent_{$unit->get_id()}_{$this->studentID}' style='display:none;'>".$unit->build_unit_details_table()."</div>";
                
				$retval .= "</td>";
                
                if($studentView)
				{
					if($editing)
					{
                        $retval .= "<td>".$this->edit_unit_award($unit, $rank, $award, $unitAwards)."</td>";
                    }
					else
					{
						//print out the unit award column
						//$retval .= "<td id='unitAward_".$unit->get_id()."' class='unitAward r".$unit->get_id()." rank$rank'>".$award."</td>";
                        $retval .= '<td><span id="unitAward_'.$unit->get_id().'_'.$studentID.'">'.$award.'</span></td>';
                    }
                    
                    // Percent
                    if($this->has_percentage_completions()){
                        $retval .= "<td><div class='tdPercentCompleted'>".$unit->display_percentage_completed()."</div></td>";
                    }
                    
				}
                
                
                
                
                if($criteriaNames)
				{
					//if we have found the used criteria names. 
					$criteriaCount = 0;
					foreach($criteriaNames AS $criteriaName)
					{	
                        
                        $colspan = $this->max_all_ranges_of_task_name($criteriaName);
                        
						//TODO
						$criteriaCount++;
						if($studentView)
						{
							//if its the student view then lets print
							//out the students unformation
                            $studentCriteria = $unit->get_single_criteria(-1, $criteriaName);
                                                        
							if($studentCriteria)
							{	
								$retval .= $studentCriteria->get_grid_td_($editing, $advancedMode, $unit, $user, $this, 'student', $colspan);

//								
							}//end if student criteria
							else //not student criteria (i.e. the criteria doesnt exist on that unit)
							{         
                                //retval needs to be an array of the columns
								$retval .= "<td class='blank'></td>";
                                if ($colspan > 1)
                                {
                                    for ($i = 1; $i < $colspan; $i++)
                                    {
                                        $retval .= "<td class='blank'></td>";
                                    }
                                }
                                
                                #$rowArray[] = $retval;
							}//end else not sudent criteria	
						}
						else//its not the student view
						{//This means we are just showing the qual as a whole. 
							//then lets just test if he unit has that criteria
							//and mark it as present or not
							$retval .= "<td>!sV</td>"; # wtf?


//							$retval .= $this->get_non_student_view_grid($criteriaCount, $criteriaCountArray, $criteriaName, $unit, $subCriteriaArray);
//                            $rowArray[] = $retval;
                            
                        }
						
					}//end for each criteria
				}//end if criteria names
                
                // IV column
                $retval .= $unit->get_iv_td($editing);
                $retval .= "</tr>";
            
            }
            
        }
        
                
        
        return $retval;
        
    }
    
    
    /**
     * 
     * @global type $CFG
     * @param type $string
     * @return string
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
        
        $possibleValues = CGQualification::get_possible_values(CGHBVRQQualification::ID, true);
        
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
     * Go through all the criteria & ranges on the qualification, and if there are any that are beyond the 
     * target date but hanve't got a "passed" award, set them to "Not Achieved".
     */
    private function update_any_beyond_target()
    {
        
        // Loop through the units
        if(!$this->units) return;
        
        foreach($this->units as $unit)
        {
            $unit->update_any_beyond_target();
        }
        
    }
    
    
    
    /**
     * Additional settings required for this, e.g. C&G Reg No., C&G Reg Date, etc..
     */
    protected function get_required_settings()
    {
        
        global $DB, $context;
        
        // Viewing from other page, e.g. PLP or PP
        if(is_null($context)) return;
        
        $user = $DB->get_record("user", array("id" => $this->studentID));
        
        $output = "";
        
        $output .= "<div id='requiredSettings'>";
        $output .= "<p id='requiredSettingsHeader'>Qualification Attributes - ".fullname($user)."</p>";
        
        $output .= "<table style='width:60%;margin:auto;'>";
            $output .= "<tr><td>C&G Registration Number:</td><td><input type='text' name='CGRegNo' onblur='updateUserSetting(this, {$this->studentID}, {$this->id});fadeInOut(\"cgregnoid\");return false;' value='".$this->get_attribute('CGRegNo', $this->studentID)."' /></td><td><span id='cgregnoid' class='saveSetting'>Saved</span></td></tr>";
            $output .= "<tr><td>C&G Registration Date <small>(dd-mm-yyyy)</small>:</td><td><input type='text' name='CGRegDate' onblur='updateUserSetting(this, {$this->studentID}, {$this->id});fadeInOut(\"cgregdateid\");return false;' value='".$this->get_attribute('CGRegDate', $this->studentID)."' /></td><td><span id='cgregdateid' class='saveSetting'>Saved</span></td></tr>";
            if ( has_capability('block/bcgt:editstudentgrid', $context)) $output .= "<tr><td>Expected End Date <small>(dd-mm-yyyy)</small>:</td><td><input type='text' name='ExpectedEnd' onblur='updateUserSetting(this, {$this->studentID}, {$this->id});fadeInOut(\"expectedend\");return false;' value='".$this->get_attribute('ExpectedEnd', $this->studentID)."' /></td><td><span id='expectedend' class='saveSetting'>Saved</span></td></tr>";
            if ( has_capability('block/bcgt:editstudentgrid', $context)) $output .= "<tr><td>Assessor(s):</td><td><input type='text' name='Assessors' onblur='updateUserSetting(this, {$this->studentID}, {$this->id});fadeInOut(\"assessors\");return false;' value='".$this->get_attribute('Assessors', $this->studentID)."' /></td><td><span id='assessors' class='saveSetting'>Saved</span></td></tr>";
            if ( has_capability('block/bcgt:editstudentgrid', $context)) $output .= "<tr><td>Internal Verifier(s):</td><td><input type='text' name='IVs' onblur='updateUserSetting(this, {$this->studentID}, {$this->id});fadeInOut(\"ivs\");return false;' value='".$this->get_attribute('IVs', $this->studentID)."' /></td><td><span id='ivs' class='saveSetting'>Saved</span></td></tr>";
            if ( has_capability('block/bcgt:editstudentgrid', $context)) $output .= "<tr><td>Cross Unit Knowledge <small>(dd-mm-yyyy)</small>:</td><td><input type='text' name='CrossUnitKnowledge' onblur='updateUserSetting(this, {$this->studentID}, {$this->id});fadeInOut(\"crossunitknowledge\");return false;' value='".$this->get_attribute('CrossUnitKnowledge', $this->studentID)."' /></td><td><span id='crossunitknowledge' class='saveSetting'>Saved</span></td></tr>";
        $output .= "</table>";
        
        $output .= "</div>";
        
        $output .= '<br><br>';
        
        if (has_capability('block/bcgt:editstudentgrid', $context))
        {
            // Overall qual attributes - not for any particular student
            $output .= "<div id='requiredSettingsRed'>";
            $output .= "<p id='requiredSettingsHeaderRed'>General Qualification Attributes</p>";

            $output .= "<table style='width:60%;margin:auto;'>";
                $output .= "<tr><td>Centre Name:</td><td><input type='text' name='CGCentreName' onblur='updateQualAttribute(this, {$this->id});fadeInOut(\"cgcentrename\");return false;' value='".$this->get_attribute('CGCentreName')."' /></td><td><span id='cgcentrename' class='saveSetting'>Saved</span></td></tr>";
                $output .= "<tr><td>Centre Number:</td><td><input type='text' name='CGCentreNum' onblur='updateQualAttribute(this, {$this->id});fadeInOut(\"cgcentrenum\");return false;' value='".$this->get_attribute('CGCentreNum')."' /></td><td><span id='cgcentrenum' class='saveSetting'>Saved</span></td></tr>";
                $output .= "<tr><td>Centre Address:</td><td><input type='text' name='CGCentreAddr' onblur='updateQualAttribute(this, {$this->id});fadeInOut(\"cgcentreaddr\");return false;' value='".$this->get_attribute('CGCentreAddr')."' /></td><td><span id='cgcentreaddr' class='saveSetting'>Saved</span></td></tr>";
                $output .= "<tr><td>Centre Contact:</td><td><input type='text' name='CGCentreCont' onblur='updateQualAttribute(this, {$this->id});fadeInOut(\"cgcentrecont\");return false;' value='".$this->get_attribute('CGCentreCont')."' /></td><td><span id='cgcentrecont' class='saveSetting'>Saved</span></td></tr>";
            $output .= "</table>";

            $output .= "</div>";
        }
                
        return $output;
    }
    
    
    
    public function has_printable_report(){
        return false;
    }
    
    public function print_grid(){
        echo "Not yet available";
    }
    
    
    
}