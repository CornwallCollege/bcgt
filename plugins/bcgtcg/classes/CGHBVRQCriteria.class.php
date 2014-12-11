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
class CGHBVRQCriteria extends CGCriteria {
    
    public static function get_instance($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        return new CGHBVRQCriteria($criteriaID, $params, $loadLevel);
    }
    
    public function get_type() {
        
        return (!is_null($this->type)) ? $this->type : 'Summative';
        
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
                       
        // Any Range Links
        if($this->rangeIDArray)
        {
            foreach($this->rangeIDArray as $rangeID => $points)
            {
                $range = new Range($rangeID);
                $range->insert_criteria_link($this->id, $points);
                $this->rangeArray[] = $range;
            }
        }
                
                
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
        
        // Any Range Links
        $originalRanges = $this->get_ranges_db();
        
        $this->rangeArray = array();
        
        if($this->rangeIDArray)
        {
            foreach($this->rangeIDArray as $rangeID => $points)
            {
                $range = new Range($rangeID);
                $range->insert_criteria_link($this->id, $points);
                $this->rangeArray[] = $range;
            }
        }
        
        // Loop through original ranges and if they weren't submitted this time, remove the range
        if ($originalRanges)
        {
            foreach($originalRanges as $originalRange)
            {
                // Might already have been deleted from a different criteria
                if ($originalRange->is_valid())
                {
                    if (!array_key_exists($originalRange->id, $this->rangeIDArray))
                    {
                        $originalRange->delete();
                    }
                }
            }
        }
                
                
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
                        
                        $ranges = Range::get_all_possible_ranges($originalSub->id);
            
                        if($ranges)
                        {
                            foreach($ranges as $range)
                            {
                                $range->delete_criteria_link($originalSub->id);
                            }
                        }
                        
					}	
				}
			}
		}
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
    public function get_grid_td_($editing, $advancedMode, $unit, $user, $qual, $grid, $colspan, $printTable = false){

        global $CFG;
        
        $output = "";
                        
        // If it's a Level 1 PMD criteria
        if ($this->type == 'L1')
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

            $c = ($editing) ? 'Edit' : 'NonEdit';
            $output .= "<td class='criteriaValue{$c}'>";
            
                // Simple non-editing
                if (!$advancedMode && !$editing)
                {
                    
                    $imageObj = CGQualification::get_simple_grid_images($value, $longValue);
                    $image = $imageObj->image;
                    $class = $imageObj->class;

                    $output .= "<span id='stCID_".$this->get_id()."_UID_".
                            $unit->get_id()."_SID_".$this->studentID."_QID_".
                            $this->qualID."' class='stuValue $class' criteriaID='{$this->id}' studentID='{$this->studentID}'><img src='".
                            $image."'/></span>";
                    
                }
                
                // Advanced non-editing
                elseif ($advancedMode && !$editing)
                {
                    
                    $output .= "<span id='stCID_".$this->get_id()."_UID_".
                    $unit->get_id()."_SID_".$this->studentID."_QID_".
                    $this->qualID."' class='stuValue stuValue{$value}' criteriaID='{$this->id}' studentID='{$this->studentID}'>{$value}</span>";
                  
                    
                }
                
                // Simple Editing
                elseif (!$advancedMode && $editing)
                {
                    
                    $values = $this->get_met_values();
                    
                    $output .= "<select name='cID_".$this->get_id()."' class='criteriaValueSelect' grid='{$grid}' criteriaid='{$this->get_id()}' unitid='{$unit->get_id()}' qualid='{$this->qualID}' studentid='{$user->id}'>";
                    $output .= "<option value='-1'></option>";
                        foreach($values as $value)
                        {
                            $chk = ($studentValueID == $value->id) ? 'selected' : '';
                            if (!empty($value->customvalue)) $value->value = $value->customvalue;
                            $output .= "<option value='{$value->id}' {$chk}>{$value->shortvalue} - {$value->value}</option>";
                        }
                    $output .= "</select>";
                    
                }
                
                // Advanced editing
                elseif ($advancedMode && $editing)
                {
                    
                    // First do the grading values
                    $output .= "<select name='cID_".$this->get_id()."' class='criteriaValueSelect' grid='{$grid}' criteriaid='{$this->get_id()}' unitid='{$unit->get_id()}' qualid='{$this->qualID}' studentid='{$user->id}'>";
                    $output .= "<option value='-1'></option>";

                    $grades = $this->get_met_values(); 
                    
                    if ($grades)
                    {
                        foreach($grades as $grade)
                        {

                            $chk = ($studentValueID == $grade->id) ? 'selected' : '';
                            $output .= "<option value='{$grade->id}' {$chk}>{$grade->shortvalue} - {$grade->value}</option>";
                        }
                    }
                    
                    // Now do the rest of the values, such as late, referred, etc...
                    $possibleValues = $this->get_non_met_values();   
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
            
        }
        
        
        // If it has no children it's just a normal criteria what will have an award
        elseif (!$this->has_children() || $this->type == 'Formative')
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

            $c = ($editing) ? 'Edit' : 'NonEdit';
            $output .= "<td class='criteriaValue{$c}'>";

            // Simple, Non-Editing
            if(!$advancedMode && !$editing)
            {

                $imageObj = CGQualification::get_simple_grid_images($value, $longValue);

                $image = $imageObj->image;
                $class = $imageObj->class;

                if ($this->studentFlag == 'L') $class .= ' wasLate ';

                $output .= "<span id='stCID_".$this->get_id()."_UID_".
                        $unit->get_id()."_SID_".$this->studentID."_QID_".
                        $this->qualID."' class='stuValue $class' criteriaID='{$this->id}' studentID='{$this->studentID}'><img src='".
                        $image."'/><br><small>".$this->get_award_date('d M Y')."</small></span>";

            }
            // Advanced, non-editing
            elseif($advancedMode && !$editing)
            {

                $class = '';
                if ($this->studentFlag == 'L') $class .= ' wasLate ';

                 $output .= "<span id='stCID_".$this->get_id()."_UID_".
                        $unit->get_id()."_SID_".$this->studentID."_QID_".
                        $this->qualID."' class='stuValue stuValue{$value} {$class}' criteriaID='{$this->id}' studentID='{$this->studentID}'>{$value}<br><small>{$this->get_award_date('d M Y')}</small></span>";

//                 if (!is_null($this->comments) && $this->comments != ''){
//                     $output .= "<div class='tooltipContent'>".nl2br( htmlentities($this->comments, ENT_QUOTES) )."</div>";
//                 }

            }
            // Advanced, editing
            elseif($advancedMode && $editing)
            {

                $class = '';
                if ($this->studentFlag == 'L') $class .= ' wasLate ';
                
                if ($this->type == 'Formative')
                {
                    
                    $img = ($studentCriteriaMet) ? "icon_OpenOutcomeComplete" : "icon_OpenOutcome";
                    $output .= "<small><a href='#' onclick='loadSubCriteria({$this->id}, {$this->qualID}, {$unit->get_id()}, {$this->studentID}, \"{$grid}\");return false;'><img id='S{$this->studentID}_U{$unit->get_id()}_C{$this->id}_POPUPIMG' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/{$img}.png' title='Open {$this->get_name()} Popup' alt='Open Popup' class='gridIcon'  /></a></small>";
                    
                }
                else
                {
                
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
                    $possibleValues = $this->get_non_met_values();
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
                
                
            }
            // Simple, editing
            else
            {

                if ($this->type == 'Formative')
                {
                    
                    $img = ($studentCriteriaMet) ? "icon_OpenOutcomeComplete" : "icon_OpenOutcome";
                    $output .= "<small><a href='#' onclick='loadSubCriteria({$this->id}, {$this->qualID}, {$unit->get_id()}, {$this->studentID}, \"{$grid}\");return false;'><img id='S{$this->studentID}_U{$unit->get_id()}_C{$this->id}_POPUPIMG' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/{$img}.png' title='Open {$this->get_name()} Popup' alt='Open Popup' class='gridIcon'  /></a></small>";
                    
                }
                else
                {
                
                    // They want to just set a date it was achieved, so no chekcbox, just an input for date
                    $val = $this->get_award_date('d-m-Y');
                    $output .= "<input type='text' style='width:70px;' class='datePickerCriteria' name='cID_{$this->id}' studentID='{$user->id}' criteriaID='{$this->id}' qualID='{$this->qualID}' unitID='{$unit->get_id()}' grid='{$grid}' value='{$val}' title='Click to edit award date for {$this->get_name()}' /> ";
                
                }
                                        
            }
            
            $output .= "<div id='criteriaTooltipContent_{$this->id}_{$this->studentID}' style='display:none;' class='criteriaContent'>".$this->build_criteria_tooltip($this->id, $this->qualID, $this->studentID)."</div>";
            
            $output .= "</td>";
            
            $tName = str_replace(" ", "_", htmlentities($this->name, ENT_QUOTES));
            
            if ($colspan > 1)
            {
                for ($i = 1; $i < $colspan; $i++)
                {
                    $output .= "<td class='blank taskClass_{$tName}'></td>";
                }
            }
            

        }
        
        // It DOES have sub criteria, then it is a task with ranges which we need to display differently
        else
        {
                        
            $ranges = $this->get_all_possible_ranges();
            $rNum = 0;


            $tName = str_replace(" ", "_", htmlentities($this->name, ENT_QUOTES));

            // Hidden overall task cell
            $tValue = $this->get_student_value();
            $img = $CFG->wwwroot . '/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/icon_NotAttempted.png';
            $date = '';
            $val = '';

            if($tValue)
            {
                $val = $tValue->get_short_value();
                $tImgObj = CGQualification::get_simple_grid_images($val, $this->get_tracking_type());
                $img = $tImgObj->image;
                $date = $this->get_award_date('d M Y');
            }


            // Simple Non
            if(!$advancedMode && !$editing){
                $output .= "<td class='taskHidden_{$tName} overallTask overallTaskStuValue criteriaValueNonEdit' criteriaID='{$this->id}' studentID='{$this->studentID}' style='display:none;'><img class='gridIcon' src='{$img}' /><br><small>{$date}</small> ".$this->build_overall_task_tooltip()."</td>";
            }

            // Advanced Non
            elseif($advancedMode && !$editing){
                $output .= "<td class='taskHidden_{$tName} overallTask overallTaskStuValue criteriaValueNonEdit' criteriaID='{$this->id}' studentID='{$this->studentID}' style='display:none;'>{$val}<br><small>{$date}</small> ".$this->build_overall_task_tooltip()."</td>";
            }

            // Simple Edit or Advanced Edit
            elseif($editing)
            {
                $date = "";
                if($this->get_award_date()){
                    $date = strtotime($this->get_award_date());
                    $date = date('d-m-Y', $date);
                }
                $output .= "<td class='taskHidden_{$tName} overallTask' style='display:none;'>";
                    $output .= "<input type='text' style='width:70px;' class='datePickerCriteria' name='cID_{$this->id}' studentID='{$user->id}' criteriaID='{$this->id}' qualID='{$this->qualID}' unitID='{$unit->get_id()}' grid='{$grid}' value='{$date}' title='Click to edit award date for {$this->get_name()}' /> ";
                $output .= "</td>";
            }
//            else
//            {
//                 $output .= "<td class='taskHidden_{$tName} overallTask' style='display:none;'>";
//
//                    $awards = $this->get_possible_awards($trackingTypeID);
//
//                    $output .= "&nbsp;<select class='criteriaValueSelect' id='S_{$user->id}C_{$this->id}' name='cID_".$this->get_id()."' onchange='update_criteria_value_only(this, ".$this->qualID.", ".$this->id.", ".$user->id.")'>";
//                    $output .= "<option value='-1'></option>";
//                        foreach($awards as $award)
//                        {
//                            if( in_array($award->shortvalue, array("P", "M", "D")) ) continue;
//                            if($tValID == $award->id) $output .= "<option value='$award->id' selected='selected'>$award->shortvalue - $award->value</option>";
//                            else $output .= "<option value='$award->id'>$award->shortvalue - $award->value</option>";
//                        }
//                    $output .= "</select>";
//
//                    // Set new award date for this criteria
//                    // I'm putting &nbsp; in the link as a quickfix to keep it all on the same line
//                    $awardDate = '';
//                    if($this->get_award_date()){
//                        $awardDate = strtotime($this->get_award_date());
//                        $awardDate = date('d-m-Y', $awardDate);
//                    }
//                    $output .= "<br><small><a href='#' onclick='$(\"#awardDate_Usr_{$user->id}_U_{$unitID}_C_{$this->get_id()}_Q_{$this->qualID}\").slideToggle();return false;'>[Set&nbsp;award&nbsp;date]</a></small><br>";
//                    $output .= "<div id='awardDate_Usr_{$user->id}_U_{$unitID}_C_{$this->get_id()}_Q_{$this->qualID}' style='display:none;' class='c hiddenDate'><input type='text' class='datePickerCriteria' param='S_{$user->id}C_{$this->id}U_{$unitID}Q_{$this->qualID}' value='{$awardDate}' title='Click to edit award date for {$this->get_name()}'  /></div>";                            
//
//                $output .= "</td>";               
//            }
//                




            foreach($ranges as $range)
            {
                $rNum++;

                $range->load_student_information($this->studentID, $this->qualID);

                $value = "N/A";
                $shortvalue = null;
                $studentCriteriaMet = false;
                $studentValueID = -1;

                if(isset($range->gradeID))
                {
                    $valueObj = new Value($range->gradeID);
                    if($valueObj)
                    {
                        $value = $valueObj->get_short_value();
                        $longvalue = $valueObj->get_value();
                        $studentCriteriaMet = ($valueObj->is_criteria_met() == 'Yes') ? true : false;
                        $studentValueID = $valueObj->get_id();
                    }
                }



                // Simple, Non-Editing
                if(!$advancedMode && !$editing)
                {

                    $imageObj = CGQualification::get_simple_grid_images($value, $this->get_tracking_type());
                    $style = "";
                    if($this->comments != '' && !$printTable){
                        // Have to use in-line as could not get class to work
                        $style = "style='background-color: #FFFF99;'";
                    }

                    $awardDate = (isset($range->awardDate) && $range->awardDate > 0 && $studentCriteriaMet == 'Yes') ? date('d M Y', $range->awardDate) : '';
                    $img = "<img src='".$imageObj->image."' class='".$imageObj->class." gridIcon' /><br><small>" . $awardDate . "</small>";


                    $output .= "<td {$style} class='taskClass_{$tName} criteriaValueNonEdit'><span class='rangeValue' title='' rangeID='{$range->id}' unitID='{$this->unitID}' studentID='{$this->studentID}'>{$img}</span>";
                    if (!$printTable) $output .= $this->build_criteria_value_popup($this->unitID, $range);
                    $output .= "</td>";

                }

                // Advanced, Non-Editing
                elseif($advancedMode && !$editing)
                {

                    $cellClass = 'noComments';
                    if($this->comments != '' && !$printTable){
                        $cellClass = 'criteriaComments';
                    }

                    // Award Date
                    $awardDate = (isset($range->awardDate) && $range->awardDate > 0 && $studentCriteriaMet == 'Yes') ? '<div class="hiddenDate"><small>'.date('d M Y', $range->awardDate) .'</small></div>' : '';

                    $output .= "<td class='{$cellClass} taskClass_{$tName} criteriaValueNonEdit'><span class='rangeValue' title='' rangeID='{$range->id}' unitID='{$this->unitID}' studentID='{$this->studentID}' style='font-weight:bold;'>{$value}<br>{$awardDate}</span>";
                    if (!$printTable) $output .= $this->build_criteria_value_popup($this->unitID, $range);
                    $output .= "</td>";

                }

                // Simple Editing
                elseif(!$advancedMode && $editing)
                {

                    $output .= "<td class='taskClass_{$tName}'>";

                        $awards = $this->get_simple_awards( CGHBVRQQualification::ID );
                        $output .= "<select class='updateRangeAward' id='grid_S{$this->studentID}_R{$range->id}' qualID='{$this->qualID}' unitID='{$unit->get_id()}' rangeID='{$range->id}' studentID='{$this->studentID}' grid='{$grid}'>";
                        $output .= "<option value='-1'></option>";
                            foreach($awards as $award)
                            {
                                if($studentValueID == $award->id)  $output .= "<option value='$award->id' selected='selected'>$award->shortvalue - $award->value</option>";
                                else $output .= "<option value='$award->id'>$award->shortvalue - $award->value</option>";
                            }
                        $output .= "</select>";

                    $output .= "</td>";

                }                    

                // Advanced Editing
                elseif($advancedMode && $editing)
                {

                    $output .= "<td class='taskClass_{$tName}'>";

                        $targetdate = $range->get_target_date();
                        $targetdate = ($targetdate > 0) ? date('d-m-Y', $targetdate) : '';

                        // Set new target date for this criteria
                        $output .= "<small><a href='#' onclick='$(\"#targetDate_S_{$user->id}_U_{$unit->get_id()}_C_{$this->get_id()}_Q_{$this->qualID}_R_{$range->id}\").slideToggle();return false;'>[Set&nbsp;target&nbsp;date]</a></small><br>";
                        $output .= "<div id='targetDate_S_{$user->id}_U_{$unit->get_id()}_C_{$this->get_id()}_Q_{$this->qualID}_R_{$range->id}' style='display:none;' class='c hiddenDate'><input type='text' style='width:70px;' studentID='{$this->studentID}' qualID='{$this->qualID}' rangeID='{$range->id}' class='datePickerRangeTarget' value='{$targetdate}' /><br></div>";

                        $output .= "&nbsp;<select class='updateRangeAward' id='grid_S{$this->studentID}_R{$range->id}' qualID='{$this->qualID}' unitID='{$unit->get_id()}' rangeID='{$range->id}' studentID='{$this->studentID}' grid='{$grid}'>";
                        $output .= "<option value='-1'></option>";

                            $grades = $this->get_met_values( CGHBVRQQualification::ID );                
                            if ($grades)
                            {
                                foreach($grades as $grade)
                                {

                                    $chk = ($studentValueID == $grade->id) ? 'selected' : '';
                                    $output .= "<option value='{$grade->id}' {$chk}>{$grade->shortvalue} - {$grade->value}</option>";
                                }
                            }


                            // Now do the rest of the values, such as late, referred, etc...
                            $possibleValues = $this->get_non_met_values( CGHBVRQQualification::ID );   
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
                        $output .= "<br><small><a href='#' onclick='$(\"#awardDate_Usr_{$user->id}_U_{$unit->get_id()}_C_{$this->get_id()}_R_{$range->id}\").slideToggle();return false;'>[Set&nbsp;award&nbsp;date]</a></small><br>";
                        $awardDate = (isset($range->awardDate) && $studentCriteriaMet == 'Yes') ? date('d-m-Y', $range->awardDate) : '';
                        $output .= "<div id='awardDate_Usr_{$user->id}_U_{$unit->get_id()}_C_{$this->get_id()}_R_{$range->id}' style='display:none;' class='c hiddenDate'><input type='text' style='width:70px;' class='datePickerRange' qualID='{$this->qualID}' studentID='{$this->studentID}' rangeID='{$range->id}' value='{$awardDate}' id='advRngAwardDate_{$this->studentID}_{$range->id}' /></div>";                            

                    $output .= "</td>";

                }

            }


            if ($colspan > 1 && $rNum < $colspan)
            {
                for ($i = $rNum; $i < $colspan; $i++)
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
        $output = "<div id='overallTaskTooltipContent_{$this->id}_{$this->studentID}' style='display:none;' class='criteriaContent'>";
        
        $unitName = get_unit_name_by_id($this->unitID);
        $output .= "<p class='c'><b>{$unitName}</b></p>";
        $output .= "<p class='c'><b>{$this->name}</b></p>";
        
        $ranges = $this->get_all_possible_ranges();
        if($ranges)
        {
            foreach($ranges as $range)
            {
                $range->load_student_information($this->studentID, $this->qualID);
                if(isset($range->grade)) $output .= "<b>".$range->name . "</b> - ".$range->grade."<br>";
                else $output .= "<b>". $range->name . "</b> - Not Attempted<br>";
            }
        }
        
        $output .= "</div>";
        
        return $output;
    }
    
    
    
    public function build_criteria_value_popup($unitID, $range = null)
    {
                
        
            $rangeID = (!is_null($range)) ? $range->id : 0;
            $output = "<div id='rangeTooltipContent_{$rangeID}_{$unitID}_{$this->studentID}' class='criteriaContent' style='display:none;'>";
        
            // Normal task with no sub criteira/ranges
            if(is_null($range))
            {
                
                $output = parent::build_criteria_value_popup($unitID, false);
                return $output;        
                
            }
            
            // Task with sub criteira/ranges
            else
            {
                
                $unitName = get_unit_name_by_id($unitID);  
                $output .= "<div class='c'><b>{$unitName}</b> <br> {$this->name} <br> <small>{$range->name}</small> <br><br></div>";

                $output .= "<table class='criteriaPopupDetailsTable'>";
                    $output .= "<tr><th>Description</th></tr>";
                    $output .= "<tr><td>{$range->details}</td></tr>";
                $output .= "</table>";
                $output .= "<br>";
                                
                $output .= "<table class='criteriaPopupDetailsTable'>";
                    $output .= "<tr><th>Criteria</th><th>Points</th></tr>";
                    
                    if($range->awards)
                    {
                        foreach($range->awards as $criteriaID => $value)
                        {
                            if($range->links[$criteriaID] <= 0) continue;
                            if(!$value) $value = "N/A";
                            $output .= "<tr><td>".Criteria::get_name_from_id($criteriaID)."</td><td>{$value}</td></tr>";
                        }
                    }
                    
                    $grade = ($range->grade) ? $range->grade : "N/A";
                    $date = ($range->awardDate) ? date('d M Y', $range->awardDate) : '';
                    
                    $output .= "<tr><td class='b'>Total</td><td class='b'>{$range->get_awarded_points()}</td></tr>";
                    $output .= "<tr><th colspan='2' style='text-transform:uppercase;'>{$grade}</th></tr>";
                    $output .= "<tr><th colspan='2' style='text-transform:uppercase;'>{$date}</th></tr>";
                    
                $output .= "</table>";
                
            }
            
            $output .= "</div>";
            
            return $output;
        
    }
    
    
   public function build_sub_criteria_table($grid, $noEdit = false)
   {
   
      
        $output = "";
                
        $output .= "<table id='rangePopupTable'>";
        
            $output .= "<tr class='lightpink'>";
                $output .= "<th>Criteria</th><th>Description</th><th>Details</th><th>Award Date</th><th></th>";
            $output .= "</tr>";
            
            // Loop sub criteria
            $subCriteria = $this->get_sub_criteria();
            
            if ($subCriteria)
            {
                
                foreach($subCriteria as $criterion)
                {
                                                            
                    $date = '';
                    $awardDate = $criterion->get_award_date();
                    if ($awardDate != '' && $awardDate > 0)
                    {
                        $date = $criterion->get_award_date('d-m-Y');
                    }
                                       
                    $details = $criterion->get_user_defined_value();
                    $details = preg_replace("/(\n|\r)/", " ", $details);
                                        
                    $output .= "<tr>";
                        $output .= "<td>{$criterion->get_name()}</td>";
                        $output .= "<td>{$criterion->get_details()}</td>";
                        $output .= "<td><textarea studentID='{$this->studentID}' id='details_studentID_{$this->studentID}_qualID_{$this->qualID}_critID_{$criterion->get_id()}'>{$details}</textarea></td>";
                        $output .= "<td><input type='text' style='width:70px;' studentID='{$this->studentID}' qualID='{$this->qualID}' unitID='{$this->unitID}' criteriaID='{$criterion->get_id()}' grid='{$grid}' setAchieved='1' class='datePickerCriteria' value='{$date}' /></td>";
                        $output .= "<td><input type='button' value='Save' onclick='saveHBVRQFormative({$this->studentID}, {$this->qualID}, {$this->unitID}, {$criterion->get_id()});return false;' /></td>";
                    $output .= "</tr>";
                    
                }
                
            }
            
                        
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
            
                $output .= "<th>Value:</th><td colspan='4'>";
                
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
                $output .= "<td colspan='4'>";
                    
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
    
    
    
    
}