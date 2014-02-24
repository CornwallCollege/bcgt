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

require_once 'CGCriteria.class.php';

/**
 * 
 */
class CGHBNVQCriteria extends CGCriteria {
    
    const OPTIONAL_NUM_OBSERVATIONS = 2;
    
    public static function get_instance($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELMIN)
    {
        return new CGHBNVQCriteria($criteriaID, $params, $loadLevel);
    }
    
    
    public function insert_criteria($unitID)
    {
        
        global $DB;
        
        $stdObj = new stdClass();
        $stdObj->name = $this->name;
        $stdObj->details = $this->details;
        $stdObj->type = $this->type;
        $stdObj->bcgttypeawardid = $this->awardID;
        $stdObj->bcgtunitid = $unitID;
        $stdObj->parentcriteriaid = $this->parentCriteriaID;
        $stdObj->targetdate = (empty($this->targetDate)) ? null : $this->targetDate ;
        $stdObj->ordernum = (int)$this->ordernum;
        $stdObj->numofobservations = $this->numberOfObservations;
        $id = $DB->insert_record('block_bcgt_criteria', $stdObj);
        $this->id = $id;
        
        
        // Now add any sub criteria
        // This works quite differently frmo most quals. This "Criteria" is actually a task and the sub criteria
        // are just Criteria of a range
        // If it has sub criteria, do them as well
        if($this->subCriteriaArray)
        {
            foreach($this->subCriteriaArray AS $subCriteria)
            {
                $subCriteria->set_parent_criteria_ID($this->id);
                $subCriteria->insert_criteria($unitID);
            }
            
        }
        
         // Grading attribute
        $stdObj = new stdClass();
        $stdObj->bcgtcriteriaid = $this->id;
        $stdObj->attribute = 'GRADING';
        $stdObj->value = $this->grading;
        $DB->insert_record("block_bcgt_criteria_att", $stdObj);
                       
        
                
                
        // Log
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, LOG_VALUE_GRADETRACKER_INSERTED_CRIT, null, null, $unitID, null, $this->id);

    }
    
    public function update_criteria($unitID = -1) {
                
        global $DB;
        
        $stdObj = new stdClass();
        $stdObj->id = $this->id;
        $stdObj->name = $this->name;
        $stdObj->details = $this->details;
        $stdObj->type = $this->type;
        $stdObj->bcgttypeawardid = $this->awardID;
        $stdObj->targetdate = (empty($this->targetDate)) ? null : $this->targetDate ;
        $stdObj->ordernum = (int)$this->ordernum;
        $stdObj->numofobservations = $this->numberOfObservations;
        $DB->update_record('block_bcgt_criteria', $stdObj);
        
        $this->check_sub_criteria_removed();
        
        if($this->subCriteriaArray)
        {
            foreach($this->subCriteriaArray AS $subCriteria)
            {
                $subCriteria->set_parent_criteria_ID($this->id);
                //check if it exists
                if($subCriteria->exists())
                {
                    $subCriteria->update_criteria($unitID);
                }
                else
                {
                    $subCriteria->insert_criteria($unitID);
                }

            }
        }
        
        
        
        
        
        // Grading attribute
        $grading = $DB->get_record("block_bcgt_criteria_att", array("bcgtcriteriaid" => $this->id, "attribute" => "GRADING"));
        $stdObj = new stdClass();
        $stdObj->id = $grading->id;
        $stdObj->value = $this->grading;
        $DB->update_record("block_bcgt_criteria_att", $stdObj);
        
       
                
        // Log
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, LOG_VALUE_GRADETRACKER_UPDATED_CRIT, null, null, $unitID, null, $this->id);
        
        
        
    }
    
    
    public function check_sub_criteria_removed()
	{
        global $DB;
		//needs to find all of the criteria
		//that were on this unit that are not anymore(if any)
		$originalSubCriteria = $this->get_sub_criteria_db($this->id);
		if($originalSubCriteria)
		{
			foreach($originalSubCriteria AS $originalSub)
			{
				if(!array_key_exists($originalSub->id, $this->subCriteriaArray))
				{
					//then do a history
					if($this->insert_sub_criteria_history($originalSub->id))
					{
						//delete the record. 
						$DB->delete_records('block_bcgt_criteria', array('id'=>$originalSub->id));
                        
                        
                        
					}	
				}
			}
		}
	}
    
    /**
     * Check if this criteria has outcomes - subcriteria with numofobservations not null
     */
    public function has_outcomes()
    {
        
        if (!$this->has_children()) return false;
        
        $has = false;
        
        if ($this->subCriteriaArray)
        {
            foreach($this->subCriteriaArray as $criteria)
            {
                if ($criteria->get_number_of_observations() > 0) $has = true;
            }
        }
        
        return $has;
        
    }
    
    
    
    
    /**
     * 
     * @param type $editing
     * @param type $advancedMode
     * @param type $unit
     * @param type $user
     * @param type $qual
     * @param type $grid
     * @param type $printTable
     */
    public function get_grid_td_($editing, $advancedMode, $unit, $user, $qualID, $grid, $listOfOutcomes, $colspan, $printTable = false){

        global $CFG;
        
        $output = "";
        
        $tName = str_replace(" ", "_", htmlentities($this->name, ENT_QUOTES));

        
        if ($this->has_outcomes())
        {
                        
            // Hidden overall task cell
            // Check if all outcomes on task are complete, and if so, then the overall task can have a tick
            
            $valueObj = $this->get_student_value();
            $value = 'N/A';
            $longValue = 'Not Attempted';
            $date = '';
            
            if ($valueObj)
            {
                $value = $valueObj->get_short_value();
                $longValue = $valueObj->get_value();
                $date = $this->get_award_date('d M Y');
            }
            
            $img = CGQualification::get_simple_grid_images($value, $longValue);

            // Simple Non
            if(!$advancedMode && !$editing){
                $output .= "<td class='taskHidden_{$tName} overallTask overallTaskStuValue' criteriaID='{$this->id}' studentID='{$this->studentID}' style='display:none;' title='t'><span><img class='gridIcon' src='{$img->image}' /><br><small>{$date}</small></span> ".$this->build_overall_task_tooltip()."</td>";
            }

            // Advanced Non
            elseif($advancedMode && !$editing){
                $output .= "<td class='taskHidden_{$tName} overallTask overallTaskStuValue' criteriaID='{$this->id}' studentID='{$this->studentID}' style='display:none;' title='t'><span>{$value}<br><small>{$date}</small></span> ".$this->build_overall_task_tooltip()."</td>";
            }

            // Simple Edit or Advanced Edit
            elseif($editing)
            {
                if ($date != ''){
                    $date = $this->get_award_date('d-m-Y');
                }
                $output .= "<td class='taskHidden_{$tName} overallTask' style='display:none;'>";
                    $output .= "<input type='text' style='width:70px;' class='datePickerCriteria' name='cID_{$this->id}' studentID='{$user->id}' criteriaID='{$this->id}' qualID='{$this->qualID}' unitID='{$unit->get_id()}' grid='{$grid}' value='{$date}' title='Click to edit award date for {$this->get_name()}' /> ";
                $output .= "</td>";
            }
 
            $numO = 0;
            
            $outcomes = $this->get_sub_criteria();
         
            foreach($listOfOutcomes as $listOutcome)
            {
                
                $fnd = false;
                                
                foreach($outcomes as $outcome)
                {
                    
                    // Load student info
                    $sID = $outcome->get_student_ID();
                    if (is_null($sID) || $sID <> $this->studentID){
                        $outcome->load_student_information($this->studentID, $this->qualID, $unit->get_id());
                    }
                    
                    if ($outcome->get_name() != $listOutcome->name) continue;
                    
                    $fnd = true;
                    $numO++;
                    
                    $valueObj = $outcome->get_student_value();
                    $value = null;
                    $studentValueID = null;
                    $studentCriteriaMet = false;
                    $longValue = '';

                    if($valueObj)
                    {
                        $studentValueID = $valueObj->get_id();
                        $value = $valueObj->get_short_value();
                        $studentCriteriaMet = ($valueObj->is_criteria_met() == 'Yes') ? true : false;
                        $longValue = $valueObj->get_value();
                    }

                    if($value == null) $value = "N/A";


                    $imageObj = CGQualification::get_simple_grid_images($value, $longValue);

                    $image = $imageObj->image;
                    $class = $imageObj->class;
                    
                    $awardDate = ($outcome->get_award_date() > 0 && $studentCriteriaMet == 'Yes') ? date('d M Y', $outcome->get_award_date()) : '';

                    // Simple, Non-Editing
                    if(!$advancedMode && !$editing)
                    {

                        $output .= "<td class='taskClass_{$tName}'><span title='t' class='stuValue' criteriaID='{$outcome->get_id()}' studentID='{$this->studentID}'><img src='{$image}' alt='{$value}' /><br><small>{$awardDate}</small></span>";
                        if(!$printTable) $output .= $outcome->build_criteria_value_popup($unit->get_id());
                        $output .= "</td>";

                    }

                    // Advanced, Non-Editing
                    elseif($advancedMode && !$editing)
                    {

                        $output .= "<td class='taskClass_{$tName}'><span title='t' class='stuValue' criteriaID='{$outcome->get_id()}' studentID='{$this->studentID}' style='font-weight:bold;'>{$value}<br><small>{$awardDate}</small></span>";
                        if(!$printTable) $output .= $outcome->build_criteria_value_popup($unit->get_id());
                        $output .= "</td>";

                    }

                    // Simple Editing
                    elseif(!$advancedMode && $editing)
                    {

                        $output .= "<td class='taskClass_{$tName}'>";

                        $img = ($studentCriteriaMet) ? "icon_OpenOutcomeComplete" : "icon_OpenOutcome";

                        $output .= "<small><a href='#' onclick='loadOutcome({$outcome->get_id()}, {$outcome->qualID}, {$unit->get_id()}, {$outcome->studentID}, \"{$grid}\");return false;'><img id='S{$this->studentID}_U{$unit->get_id()}_O{$outcome->id}_IMG' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/{$img}.png' title='Open {$outcome->get_name()} Popup' alt='Open Popup' class='gridIcon'  /></a></small>";

                        $output .= "</td>";

                    }                    

                    // Advanced Editing
                    elseif($advancedMode && $editing)
                    {

                        $output .= "<td class='taskClass_{$tName}'>";

                        $img = ($studentCriteriaMet) ? "icon_OpenOutcomeComplete" : "icon_OpenOutcome";

                            $output .= "<small><a href='#' onclick='loadOutcome({$outcome->get_id()}, {$outcome->qualID}, {$unit->get_id()}, {$outcome->studentID}, \"{$grid}\");return false;'><img id='S{$this->studentID}_U{$unit->get_id()}_O{$outcome->id}_IMG' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/{$img}.png' title='Open {$outcome->get_name()} Popup' alt='Open Popup' class='gridIcon'  /></a></small>";

                        $output .= "</td>";

                    }
                    
                }
                
                
                // If we didn't find it, do a blank cell
                if (!$fnd)
                {
                    $output .= "<td class='blank taskClass_{$tName}'></td>";
                }
                
                
            }
            
            
        }
        
        // It has sub criteria but they have no observations - it's an E3 or an E4 with shaded criteria to mark
        elseif ($this->has_children())
        {
            
            $valueObj = $this->get_student_value();

            $value = null;
            $longValue = '';
            $studentValueID = null;
            $studentCriteriaMet = false;

            if($valueObj)
            {
                $studentValueID = $valueObj->get_id();
                $value = $valueObj->get_short_value();
                $studentCriteriaMet = $valueObj->is_met($studentValueID);
                $longValue = $valueObj->get_value();
            }

            if($value == null){
                $value = "N/A";
                $longValue = 'Not Attempted';
            }
            
            $output .= "<td class='taskClass_{$tName}'>";

            // Simple, Non-Editing
            if(!$advancedMode && !$editing)
            {

                $imageObj = CGQualification::get_simple_grid_images($value, $longValue);

                $image = $imageObj->image;
                $class = $imageObj->class;

                if ($this->studentFlag == 'L') $class .= ' wasLate ';
                
                $awardDate = (isset($this->awardDate) && $studentCriteriaMet == 'Yes') ? date('d M Y', $this->awardDate) : '';

                $output .= "<span id='stCID_".$this->get_id()."_UID_".
                        $unit->get_id()."_SID_".$this->studentID."_QID_".
                        $this->qualID."' class='stuValue $class' title='' criteriaID='{$this->id}' studentID='{$this->studentID}'><img src='".
                        $image."'/><br><small>{$awardDate}</small></span>";

                if (!is_null($this->comments) && $this->comments != ''){
                    $output .= "<div class='tooltipContent'>".nl2br( htmlentities($this->comments, ENT_QUOTES) )."</div>";
                }


            }
            // Advanced, non-editing
            elseif($advancedMode && !$editing)
            {

                $class = '';
                if ($this->studentFlag == 'L') $class .= ' wasLate ';
                $awardDate = (isset($this->awardDate) && $studentCriteriaMet == 'Yes') ? date('d M Y', $this->awardDate) : '';

                 $output .= "<span id='stCID_".$this->get_id()."_UID_".
                        $unit->get_id()."_SID_".$this->studentID."_QID_".
                        $this->qualID."' class='stuValue stuValue{$value} {$class}' title='' criteriaID='{$this->id}' studentID='{$this->studentID}'>{$value}<br><small>{$awardDate}</small></span>";

                 if (!is_null($this->comments) && $this->comments != ''){
                     $output .= "<div class='tooltipContent'>".nl2br( htmlentities($this->comments, ENT_QUOTES) )."</div>";
                 }

            }
            // editing
            elseif($editing)
            {

                $img = ($studentCriteriaMet) ? "icon_OpenOutcomeComplete" : "icon_OpenOutcome";
                $output .= "<small><a href='#' onclick='loadSubCriteria({$this->get_id()}, {$this->qualID}, {$unit->get_id()}, {$this->studentID}, \"{$grid}\");return false;'><img id='S{$this->studentID}_U{$unit->get_id()}_C{$this->id}_POPUPIMG' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/{$img}.png' title='Open {$this->get_name()} Popup' alt='Open Popup' class='gridIcon'  /></a></small>";

            }
           
            
            $output .= "<div id='criteriaTooltipContent_{$this->id}_{$this->studentID}' style='display:none;'>".$this->build_criteria_tooltip($this->id, $this->qualID, $this->studentID)."</div>";
            
            $output .= "</td>";
            
            if($colspan > 1)
            {
                for($i = 1; $i < $colspan; $i++)
                {
                    $output .= "<td class='blank taskClass_{$tName}'></td>";
                }
            }
            
        }        
                
        // Ifit has no children it's just a normal criteria what will have an award - achieved/not acehivefd
        else
        {        
                    
            
                                             

            
            $valueObj = $this->get_student_value();

            $value = null;
            $longValue = '';
            $studentValueID = null;
            $studentCriteriaMet = false;

            if($valueObj)
            {
                $studentValueID = $valueObj->get_id();
                $value = $valueObj->get_short_value();
                $studentCriteriaMet = $valueObj->is_met($studentValueID);
                $longValue = $valueObj->get_value();
            }

            if($value == null){
                $value = "N/A";
                $longValue = 'Not Attempted';
            }

            $output .= "<td>";

            // Simple, Non-Editing
            if(!$advancedMode && !$editing)
            {

                $imageObj = CGQualification::get_simple_grid_images($value, $longValue);

                $image = $imageObj->image;
                $class = $imageObj->class;

                if ($this->studentFlag == 'L') $class .= ' wasLate ';
                
                $awardDate = (isset($this->awardDate) && $studentCriteriaMet == 'Yes') ? date('d M Y', $this->awardDate) : '';

                $output .= "<span id='stCID_".$this->get_id()."_UID_".
                        $unit->get_id()."_SID_".$this->studentID."_QID_".
                        $this->qualID."' class='stuValue $class' title='' criteriaID='{$this->id}' studentID='{$this->studentID}'><img src='".
                        $image."'/><br><small>{$awardDate}</small></span>";

                if (!is_null($this->comments) && $this->comments != ''){
                    $output .= "<div class='tooltipContent'>".nl2br( htmlentities($this->comments, ENT_QUOTES) )."</div>";
                }


            }
            // Advanced, non-editing
            elseif($advancedMode && !$editing)
            {

                $class = '';
                if ($this->studentFlag == 'L') $class .= ' wasLate ';
                $awardDate = (isset($this->awardDate) && $studentCriteriaMet == 'Yes') ? date('d M Y', $this->awardDate) : '';

                 $output .= "<span id='stCID_".$this->get_id()."_UID_".
                        $unit->get_id()."_SID_".$this->studentID."_QID_".
                        $this->qualID."' class='stuValue stuValue{$value} {$class}' title='' criteriaID='{$this->id}' studentID='{$this->studentID}'>{$value}<br><small>{$awardDate}</small></span>";

                 if (!is_null($this->comments) && $this->comments != ''){
                     $output .= "<div class='tooltipContent'>".nl2br( htmlentities($this->comments, ENT_QUOTES) )."</div>";
                 }

            }
            // Advanced, editing
            elseif($advancedMode && $editing)
            {

                $class = '';
                if ($this->studentFlag == 'L') $class .= ' wasLate ';

                $targetdate = $this->get_target_date();

                // Set new target date for this criteria
                $output .= "<small><a href='#' onclick='$(\"#targetDate_S_{$user->id}_U_{$unit->get_id()}_C_{$this->get_id()}_Q_{$this->qualID}\").slideToggle();return false;'>[Set&nbsp;target&nbsp;date]</a></small><br>";
                $output .= "<div id='targetDate_S_{$user->id}_U_{$unit->get_id()}_C_{$this->get_id()}_Q_{$this->qualID}' style='display:none;' class='c hiddenDate'><input type='text' style='width:70px;' studentID='{$this->studentID}' qualID='{$this->qualID}' unitID='{$this->unitID}' criteriaID='{$this->id}' grid='{$grid}' class='datePickerCriteriaTarget' value='{$targetdate}' /><br></div>";

                
                // First do the grading values
                $output .= "<select name='cID_".$this->get_id()."' class='criteriaValueSelect {$class}' grid='{$grid}' criteriaid='{$this->get_id()}' unitid='{$unit->get_id()}' qualid='{$this->qualID}' studentid='{$user->id}'>";
                $output .= "<option value='-1'></option>";

                $grade = $this->get_achieved_value();                

                if ($grade)
                {
                    $chk = ($studentValueID == $grade->id) ? 'selected' : '';
                    $output .= "<option value='{$grade->id}' {$chk}>{$grade->shortvalue} - {$grade->value}</option>";
                }


                // Now do the rest of the values, such as late, referred, etc...
                $possibleValues = $this->get_non_met_values( $this->get_tracking_type() );
                if ($possibleValues)
                {
                    foreach($possibleValues as $value)
                    {
                        $chk = ($studentValueID == $value->id) ? 'selected' : '';
                        $output .= "<option value='{$value->id}' {$chk}>{$value->shortvalue} - {$value->value}</option>";
                    }
                }

                $output .= "</select>";
                
                // Set new award date for this criteria
                // I'm putting &nbsp; in the link as a quickfix to keep it all on the same line
                $output .= "<br><small><a href='#' onclick='$(\"#awardDate_Usr_{$user->id}_U_{$unit->get_id()}_C_{$this->get_id()}\").slideToggle();return false;'>[Set&nbsp;award&nbsp;date]</a></small><br>";
                $awardDate = (isset($this->awardDate) && $studentCriteriaMet == 'Yes') ? date('d-m-Y', $this->awardDate) : '';
                $output .= "<div id='awardDate_Usr_{$user->id}_U_{$unit->get_id()}_C_{$this->get_id()}' style='display:none;' class='c hiddenDate'><input type='text' style='width:70px;' class='datePickerCriteria' qualID='{$this->qualID}' studentID='{$this->studentID}' unitID='{$this->unitID}' criteriaID='{$this->id}' grid='{$grid}' value='{$awardDate}' /></div>";                            


            }
            // Simple, editing
            else
            {

                // They want to just set a date it was achieved, so no chekcbox, just an input for date
                $val = $this->get_award_date('d-m-Y');
                $output .= "<input type='text' style='width:70px;' class='datePickerCriteria' name='cID_{$this->id}' studentID='{$user->id}' criteriaID='{$this->id}' qualID='{$this->qualID}' unitID='{$unit->get_id()}' grid='{$grid}' value='{$val}' title='Click to edit award date for {$this->get_name()}' /> ";
                                        
            }
            
            $output .= "<div id='criteriaTooltipContent_{$this->id}_{$this->studentID}' style='display:none;'>".$this->build_criteria_tooltip($this->id, $this->qualID, $this->studentID)."</div>";
            
            $output .= "</td>";
            
            if($colspan > 1)
            {
                for($i = 1; $i < $colspan; $i++)
                {
                    $output .= "<td class='blank taskClass_{$tName}'></td>";
                }
            }
            

        }
        
        
        
        return $output;
        
        
    }
    
    private function get_simple_awards($typeID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND specialval = 'A' AND shortvalue != 'A' ORDER BY ranking ASC";
        return $DB->get_records_sql($sql, array($typeID));
    }
    
    
    /**
     * This is a tooltip for the overall task when its toggled. It will show a list of the ranges and whewther or
     * not they are achieved yet
     */
    public function build_overall_task_tooltip()
    {
        $output = "<div id='overallTaskTooltipContent_{$this->id}_{$this->studentID}' style='display:none;'>";
        
        $output .= "<small>".fullname($this->student)." ({$this->student->username})</small><br><br>";
        $unitName = get_unit_name_by_id($this->unitID);
        $output .= "<p class='c'><b>{$unitName}</b></p>";
        $output .= "<p class='c'><b>{$this->name}</b></p>";
        
        $sub = $this->get_sub_criteria();
        if($sub)
        {
            foreach($sub as $subCriterion)
            {
                $output .= $subCriterion->get_name() . " - ";
                $value = $subCriterion->get_student_value();
                $output .= ($subCriterion->is_met()) ? $value->get_value() : "N/A";
                $output .= "<br>";
            }
        }
        
        $output .= "</div>";
        
        return $output;
    }
    
    
    
    public function build_criteria_value_popup($unitID, $range = null)
    {
                
        global $CFG;
                
        $output = "";
        $output .= "<div id='criteriaTooltipContent_{$this->id}_{$this->studentID}' style='display:none;'>";
        
            // Stanard criteria
            if (!$this->get_sub_criteria())
            {
                $output .= $this->build_criteria_tooltip($this->id, $this->qualID, $this->studentID);
            }
            else
            {
                
                // With observations and ranges
                $unit = get_unit_name_by_id($unitID);  
                $parent = get_criteria_by_id($this->parentCriteriaID);
                $output .= "<small>".fullname($this->student)."  ({$this->student->username})</small>";
                $output .= "<div class='c'><b>{$unit}</b> <br> {$parent->name} <br> <small>{$this->name}</small> <br><br></div>";

                $output .= "<table class='criteriaPopupDetailsTable'>";
                    $output .= "<tr><th>Description</th></tr>";
                    $output .= "<tr><td>{$parent->details}</td></tr>";
                $output .= "</table>";
                $output .= "<br>";
                     
                // Observation table
                $output .= $this->build_outcome_table('', true);
                                                                
                
            }
            
        $output .= "</div>";

        return $output;
        
    }
    
        
    
   public function build_sub_criteria_table($grid, $noEdit = false)
   {
   
       global $DB;
       
        $output = "";
                
        $output .= "<table id='rangePopupTable'>";
        
            $output .= "<tr class='lightpink'>";
                $output .= "<th>Criteria</th><th>Details</th><th>Award Date</th>";
            $output .= "</tr>";
            
            // Loop sub criteria
            $subCriteria = $this->get_sub_criteria();
            
            if ($subCriteria)
            {
                
                foreach($subCriteria as $criterion)
                {
                    
                    // No outcomes please, we're standard criteria
                    if ($criterion->get_number_of_observations() > 0) continue;
                                        
                    $date = '';
                    $awardDate = $criterion->get_award_date();
                    if ($awardDate != '' && $awardDate > 0)
                    {
                        $date = $criterion->get_award_date('d-m-Y');
                    }
                    
                    $class = ($criterion->get_type() != 'Read Only') ? 'lightyellow' : '';
                    
                    $output .= "<tr class='{$class}'>";
                        $output .= "<td>{$criterion->get_name()}</td>";
                        $output .= "<td>{$criterion->get_details()}</td>";
                        $output .= "<td>". ( ($criterion->get_type() != 'Read Only') ? "<input type='text' style='width:70px;' studentID='{$this->studentID}' qualID='{$this->qualID}' unitID='{$this->unitID}' criteriaID='{$criterion->get_id()}' grid='{$grid}' class='datePickerCriteria' value='{$date}' />" : "N/A" ) ."</td>";
                    $output .= "</tr>";
                    
                }
                
            }
            
            // Overall criteria stuff
            $output .= "<tr class='doubleTop pink'>";
                $output .= "<th>Target Date:</th>";
                $output .= "<td colspan='2'>";
                    
                    if ($noEdit)
                    {
                        $date = ($this->targetDate > 0) ? $this->get_target_date() : 'N/A';
                        $output .= $date;
                    }
                    else
                    {
                        $date = ($this->targetDate > 0) ? date('d-m-Y', $this->targetDate) : '';
                        $output .= "<input style='width:70px;' studentID='{$this->studentID}' qualID='{$this->qualID}' unitID='{$this->unitID}' criteriaID='{$this->id}' grid='{$grid}' class='datePickerCriteriaTarget' type='text' value='{$date}' />";
                    }
                
                $output .= "</td>";
            $output .= "</tr>";
            
            $output .= "<tr class='pink'>";
            
            $valueObj = $this->get_student_value();
            $value = 'N/A';
            $longValue = '';
            $studentValueID = null;
            if ($valueObj)
            {
                $studentValueID = $valueObj->get_id();
                $value = $valueObj->get_short_value();
                $longValue = $valueObj->get_value();
            }
            
            $image = CGQualification::get_simple_grid_images($value, $longValue);
            $img = ($image) ? $image->image : '';
            
                $output .= "<th>Value:</th><td colspan='2'>";
                
                if ($noEdit)
                {
                    $output .= "<img src='{$img}' />";
                }
                else
                {
                    // Select menu
                    $output .= "<select name='cID_".$this->get_id()."' class='criteriaValueSelect' grid='{$grid}' criteriaid='{$this->get_id()}' unitid='{$this->unitID}' qualid='{$this->qualID}' studentid='{$this->studentID}' style='width:100px;'>";
                    $output .= "<option value='-1'></option>";

                    $grade = $this->get_achieved_value();                

                    if ($grade)
                    {
                        $chk = ($studentValueID == $grade->id) ? 'selected' : '';
                        $output .= "<option value='{$grade->id}' {$chk}>{$grade->shortvalue} - {$grade->value}</option>";
                    }


                    // Now do the rest of the values, such as late, referred, etc...
                    $possibleValues = $this->get_non_met_values( $this->get_tracking_type() );
                    if ($possibleValues)
                    {
                        foreach($possibleValues as $value)
                        {
                            $chk = ($studentValueID == $value->id) ? 'selected' : '';
                            $output .= "<option value='{$value->id}' {$chk}>{$value->shortvalue} - {$value->value}</option>";
                        }
                    }

                    $output .= "</select>";
                    
                }
                
                $output .= "</td>";
            $output .= "</tr>";
            $output .= "<tr class='pink'>";
                $output .= "<th>Date:</th>";
                $output .= "<td colspan='2'>";
                    
                if ($noEdit)
                {
                    $date = ($this->awardDate && $this->awardDate > 0) ? $this->get_award_date('d M Y', $this->awardDate) : 'N/A';
                    $output .= $date;
                }
                else
                {
                    $date = ($this->awardDate && $this->awardDate > 0) ? $this->get_award_date('d-m-Y', $this->awardDate) : '';
                        $output .= "<input style='width:70px;' studentID='{$this->studentID}' qualID='{$this->qualID}' unitID='{$this->unitID}' criteriaID='{$this->id}' grid='{$grid}' class='datePickerCriteria ' type='text' value='{$date}' />";
                }
                
                $output .= "</td>";
            $output .= "</tr>";
        
        
        $output .= "</table>";
        return $output;
       
   }
    
   public function build_outcome_table($grid, $noEdit = false)
   {
      
       global $CFG, $DB;
        
        $output = "";
                
        $output .= "<table id='rangePopupTable'>";
        
            $output .= "<tr class='lightpink'>";
                $output .= "<th style='width:30%;'>Observation:</th>";
                for($num = 1; $num <= $this->get_number_of_observations(); $num++)
                {
                    $output .= "<th class='c outcomeDescCritTooltip'>{$num}</th>";
                }
                
                // + 2 optional extra ones
                $output .= "<th class='c'></th>";
                $output .= "<th class='c'></th>";
                
            $output .= "</tr>";
            
            $output .= "<tr class='lightpink'>";
                $output .= "<th>Date Achieved:</th>";
                for($num = 1; $num <= $this->get_number_of_observations(); $num++)
                {
                    $check = $DB->get_record("block_bcgt_user_outcome_obs", array("userid" => $this->studentID, "bcgtqualificationid" => $this->qualID, "bcgtcriteriaid" => $this->id, "observationnum" => $num));
                    if($noEdit){
                        $date = (isset($check->id) && $check->date > 0) ? date('d M Y', $check->date) : "";
                        $output .= "<td class='c'>{$date}</td>";
                    }
                    else
                    {
                        $date = (isset($check->id) && $check->date > 0) ? date('d-m-Y', $check->date) : "";
                        $output .= "<td class='c'><input type='text' class='datePickerOutcomeObservation' name='cID_{$this->id}' studentID='{$this->studentID}' unitID='{$this->unitID}' criteriaID='{$this->id}' qualID='{$this->qualID}' observationNum='{$num}' grid='{$grid}' value='{$date}' title='Click to edit award date for {$this->name}' /></td>";
                    }
                }
                
                // + 2 optional ones
                $minus = self::OPTIONAL_NUM_OBSERVATIONS - ( self::OPTIONAL_NUM_OBSERVATIONS * 2 );
                for ($optNum = -1; $optNum >= $minus; $optNum--)
                {
                    $check = $DB->get_record("block_bcgt_user_outcome_obs", array("userid" => $this->studentID, "bcgtqualificationid" => $this->qualID, "bcgtcriteriaid" => $this->id, "observationnum" => $optNum));
                    if ($noEdit)
                    {
                        $date = (isset($check->id) && $check->date > 0) ? date('d M Y', $check->date) : "";
                        $output .= "<td class='c'>{$date}</td>";
                    }
                    else
                    {
                        $date = (isset($check->id) && $check->date > 0) ? date('d-m-Y', $check->date) : "";
                        $output .= "<td class='c'><input type='text' class='datePickerOutcomeObservation' name='cID_{$this->id}' studentID='{$this->studentID}' unitID='{$this->unitID}' criteriaID='{$this->id}' qualID='{$this->qualID}' observationNum='{$optNum}' grid='{$grid}' value='{$date}' title='Click to edit award date for {$this->name}' /></td>";
                    }
                }
                
            $output .= "</tr>";
            
            
            // Descriptive criteria for ticking and dating the ones with stars
            if($this->get_sub_criteria())
            {
                
                foreach($this->get_sub_criteria() as $descCrit)
                {
                    $output .= "<tr>";
                    $gradeable = false;
                    
                    $details = $descCrit->get_details();
                    if (preg_match("/\*/", $details)){
                        $details = preg_replace("/\*/", "", $details);
                        $details = "<strong>{$details}</strong>";
                        $gradeable = true;
                    }
                    
                    $output .= "<th>{$descCrit->get_name()} - <small>{$details}</small></th>";
                        
                    for($num = 1; $num <= $this->get_number_of_observations() + self::OPTIONAL_NUM_OBSERVATIONS; $num++)
                    {
                        $output .= "<td class='c'>";
                            if ($gradeable)
                            {
                                
                                $obNum = $num;
                                if ($obNum > $this->get_number_of_observations()){
                                    $diff = $num - $this->get_number_of_observations();
                                    $obNum = $num - ($num + $diff);
                                }
                                
                                $check = $DB->get_record("block_bcgt_qual_attributes", array("userid" => $this->studentID, "bcgtqualificationid" => $this->qualID, "attribute" => "outcome_".$this->id."_criteria_".$descCrit->get_id()."_observation_".$obNum));
                                
                                if ($noEdit)
                                {
                                    $output .= ($check) ? ucfirst($check->value) : "" ;
                                }
                                else
                                {
                                    $output .= "<select name='outcome_".$this->id."_criteria_".$descCrit->get_id()."_observation_".$obNum."' onchange='updateUserSetting(this, {$this->studentID}, {$this->qualID});return false;'>";
                                        $output .= "<option value=''></option>";
                                        $output .= "<option value='observation' ".( ($check && $check->value == 'observation') ? 'selected' : '' )." >Observation</option>";
                                        $output .= "<option value='oral' ".( ($check && $check->value == 'oral') ? 'selected' : '' )." >Oral</option>";
                                    $output .= "</select>";                                
                                }
                                    
                                
                                
                            }
                        $output .= "</td>";
                    }
                    
                    $output .= "</tr>";

                }
                    
            }
            
            
            
            $output .= "<tr class='doubleTop pink'>";
                $output .= "<th>Target Date:</th>";
                $output .= "<td colspan='".($this->get_number_of_observations() + self::OPTIONAL_NUM_OBSERVATIONS)."'>";
                    
                    if ($noEdit)
                    {
                        $date = ($this->targetDate > 0) ? $this->get_target_date() : 'N/A';
                        $output .= $date;
                    }
                    else
                    {
                        $date = ($this->targetDate > 0) ? date('d-m-Y', $this->targetDate) : '';
                        $output .= "<input style='width:70px;' studentID='{$this->studentID}' qualID='{$this->qualID}' unitID='{$this->unitID}' criteriaID='{$this->id}' grid='{$grid}' class='datePickerCriteriaTarget' type='text' value='{$date}' />";
                    }
                
                $output .= "</td>";
            $output .= "</tr>";
            
            $output .= "<tr class='pink'>";
            
            $valueObj = $this->get_student_value();
            $value = 'N/A';
            $longValue = '';
            if ($valueObj)
            {
                $value = $valueObj->get_short_value();
                $longValue = $valueObj->get_value();
            }
            
            $image = CGQualification::get_simple_grid_images($value, $longValue);
            $img = ($image) ? $image->image : '';
            
                $output .= "<th>Outcome Value:</th><td colspan='".($this->get_number_of_observations() + self::OPTIONAL_NUM_OBSERVATIONS)."' class='c' id='outcome_{$this->id}_{$this->studentID}'><img src='{$img}' /></td>";
            $output .= "</tr>";
            $output .= "<tr class='doubleTop pink'>";
                $output .= "<th>Outcome Date:</th>";
                $output .= "<td colspan='".($this->get_number_of_observations() + self::OPTIONAL_NUM_OBSERVATIONS)."'>";
                    
                if ($noEdit)
                {
                    $date = ($this->awardDate && $this->awardDate > 0) ? $this->get_award_date('d M Y', $this->awardDate) : 'N/A';
                    $output .= $date;
                }
                else
                {
                    $date = ($this->awardDate && $this->awardDate > 0) ? $this->get_award_date('d-m-Y', $this->awardDate) : '';
                        $output .= "<input style='width:70px;' studentID='{$this->studentID}' qualID='{$this->qualID}' unitID='{$this->unitID}' criteriaID='{$this->id}' grid='{$grid}' class='datePickerCriteria ' type='text' value='{$date}' />";
                }
                
                $output .= "</td>";
            $output .= "</tr>";
        
        $output .= "</table>";
        
        return $output;
       
   }
   
   public function set_number_of_observations($num)
    {
        $this->numberOfObservations = $num;
    }
    
   public function get_number_of_observations()
    {
        return $this->numberOfObservations;
    }
   
    
}