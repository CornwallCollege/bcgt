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

require_once 'CGHBNVQQualification.class.php';
require_once 'CGUnit.class.php';
require_once 'CGHBNVQCriteria.class.php';
require_once $CFG->dirroot . '/blocks/bcgt/classes/core/Range.class.php';


/**
 * 
 */
class CGHBNVQUnit extends CGUnit {
 
    
    const MAX_OBSERVATIONS_ON_OUTCOME = 10;
    
    /**
     * This method is on every non abstract class!
     * @param type $unitID
     * @param type $params
     * @param type $loadParams
     * @return \ALevelUnit
     */
    public static function get_instance($unitID, $params, $loadParams)
    {
        return new CGHBNVQUnit($unitID, $params, $loadParams);
    }
    
     /*
	 * Gets the associated Qualification ID
	 */
	public function get_typeID()
    {
        return CGHBNVQQualification::ID;
    }
    
    /*
	 * Gets the name of the associated qualification. 
	 */
	public function get_type_name()
    {
        return CGHBNVQQualification::NAME;
    }
    
    /*
	 * Gets the name of the associated qualification family. 
	 */
	public function get_family_name()
    {
        return CGHBNVQQualification::NAME;
    }
	
	/**
	 * Get the family of the qual.
	 */
	public function get_familyID()
    {
        return CGHBNVQQualification::FAMILYID;
    }
    
    
    
    /**
	 * Used in edit unit
	 * Gets the submitted data from the criteria section of the edit form form.
	 * edit_unit_form.php
	 */
	public function get_submitted_criteria_edit_form_data()
    {
                              
        if(isset($_POST['taskIDs']))
        {
            
            // Clear criteria array so that any we don't submit that were there before get deleted
                        
            // Overall tasks
            $taskIDs = $_POST['taskIDs'];
            $taskNames = $_POST['taskNames'];
            $taskDetails = $_POST['taskDetails'];
            $taskDates = $_POST['taskTargetDates'];
            $taskOrders = $_POST['taskOrders'];
                                                
            // Outcomes
            $outcomeIDs = $_POST['outcomesIDs'];
            $outcomeNames = $_POST['outcomeNames'];
            $outcomeDetails = $_POST['outcomeDetails'];
            $outcomeDates = $_POST['outcomeDates'];
            $outcomeNumOfObs = $_POST['outcomeNumOfObservations'];
            
            // Obsv
            $descCritIDs = $_POST['descCritIDs'];
            $descCritNames = $_POST['descCritNames'];
            $descCritDetails = $_POST['descCritDetails'];
            
            // Standard sub criteria
            $subCritIDs = $_POST['subCritIDs'];
            $subCritNames = $_POST['subCritNames'];
            $subCritDetails = $_POST['subCritDetails'];
            $subCritMarkable = (isset($_POST['subCritMarkable'])) ? $_POST['subCritMarkable'] : array();
                                    
            if(empty($taskNames) || empty($taskIDs)){
                return false;
            }
            
                        
            // We will store these details as:
            // Criteria -> SUb Criteria -> Sub Criteria
            // Task
            //      Outcome
            //              Criterion
            
            // Foreach task as DynamicID => Actual ID
            foreach($taskIDs as $DID => $ID)
            {
                                                
                // if there is no criteria(task) on the unit with this id, add one
                if(!isset($this->criterias[$ID]))
                {
                    
                    // If name is empty, skip it
                    if (empty($taskNames[$DID])) continue;
                    
                    $params = new stdClass();
                    $params->name = $taskNames[$DID];
                    $params->details = $taskDetails[$DID];
                    $params->ordernum = $taskOrders[$DID];
                    
                    $obj = new CGHBNVQCriteria(-1, $params, Qualification::LOADLEVELCRITERIA);
                    
                    // Target date
                    $targetDate = strtotime($taskDates[$DID]);
                    $obj->set_target_date($targetDate);

                    // Check if this task has any observations/criteria
                    
                    
                    // Outcomes
                    if(isset($outcomeIDs[$DID]))
                    {
                        // For each outcomeid[parentdynamicid] as outcomedynamicid -> outcomeid
                        foreach($outcomeIDs[$DID] as $ODID => $OID)
                        {
                            
                            // If name is empty, skip it
                            if (empty($outcomeNames[$DID][$ODID])) continue;
                    
                            $params = new stdClass();
                            $params->name = $outcomeNames[$DID][$ODID];
                            $params->details = $outcomeDetails[$DID][$ODID];
                            $params->ordernum = 1;
                            $params->numberOfObservations = $outcomeNumOfObs[$DID][$ODID];
                            
                            $subObj = new CGHBNVQCriteria(-1, $params, Qualification::LOADLEVELCRITERIA);
                            
                            $targetDate = strtotime($outcomeDates[$DID][$ODID]);
                            $subObj->set_target_date($targetDate);
                            
                            $subObj->set_number_of_observations($outcomeNumOfObs[$DID][$ODID]);
                            
                            
                            // Descriptive Criteria
                            if(isset($descCritIDs[$DID][$ODID]))
                            {
                                // For each criteriaid[parentdynamicid] as criteriadynamicid -> criteriaid
                                foreach($descCritIDs[$DID][$ODID] as $CDID => $CID)
                                {

                                    // If name is empty, skip it
                                    if (empty($descCritNames[$DID][$ODID][$CDID])) continue;
                                    
                                    // For these we will just assign them as sub criteria on the sub criteria
                                    $params = new stdClass();
                                    $params->name = $descCritNames[$DID][$ODID][$CDID];
                                    $params->details = $descCritDetails[$DID][$ODID][$CDID];
                                    $params->ordernum = 1;
                                    $subSubObj = new CGHBNVQCriteria(-1, $params, Qualification::LOADLEVELCRITERIA);                            

                                    // Add sub criteria to overall criteria (task)
                                    $subObj->add_sub_criteria($subSubObj);

                                }
                            }
                            
                            // Add to parent
                            $obj->add_sub_criteria($subObj);
                            
                        }
                    }
                    
                    // Standard sub criteria
                    if (isset($subCritIDs[$DID]))
                    {
                        
                        foreach($subCritIDs[$DID] as $SCDID => $SCID)
                        {
                            
                            // If name is empty, skip it
                            if (empty($subCritNames[$DID][$SCDID])) continue;
                            
                            $params = new stdClass();
                            $params->name = $subCritNames[$DID][$SCDID];
                            $params->details = $subCritDetails[$DID][$SCDID];
                            $params->ordernum = 1;
                            
                            if (!isset($subCritMarkable[$DID][$SCDID]))
                            {
                                $params->type = 'Read Only';
                            }
                                                        
                            $subCritObj = new CGHBNVQCriteria(-1, $params, Qualification::LOADLEVELCRITERIA);                            
                            // Add to parent
                            $obj->add_sub_criteria($subCritObj);
                            
                        }
                        
                    }
                    
                                                           
                    $this->criterias[] = $obj;
                    
                }
                
                // It is already there, so update the object with new values
                else
                {
                                        
                    $params = new stdClass();
                    $params->name = $taskNames[$DID];
                    $params->details = $taskDetails[$DID];
                    $params->ordernum = $taskOrders[$DID];
                    
                    $obj = new CGHBNVQCriteria($ID, $params, Qualification::LOADLEVELCRITERIA);
                    $targetDate = strtotime($taskDates[$DID]);
                    $obj->set_target_date($targetDate);
                    
                                        
                                        
                    // Outcomes
                    if(isset($outcomeIDs[$DID]))
                    {
                        
                        // For each outcomeid[parentdynamicid] as outcomedynamicid -> outcomeid
                        foreach($outcomeIDs[$DID] as $ODID => $OID)
                        {
                            
                            // If name is empty, skip it
                            if (empty($outcomeNames[$DID][$ODID])) continue;
                            
                            $params = new stdClass();
                            $params->name = $outcomeNames[$DID][$ODID];
                            $params->details = $outcomeDetails[$DID][$ODID];
                            $params->ordernum = 1;
                            $params->numberOfObservations = $outcomeNumOfObs[$DID][$ODID];
                            
                            $subObj = new CGHBNVQCriteria($OID, $params, Qualification::LOADLEVELCRITERIA);
                            
                            $targetDate = strtotime($outcomeDates[$DID][$ODID]);
                            
                            $subObj->set_target_date($targetDate);
                            $subObj->set_number_of_observations($outcomeNumOfObs[$DID][$ODID]);

                                // Descriptive Criteria
                                if(isset($descCritIDs[$DID][$ODID]))
                                {
                                    // For each criteriaid[parentdynamicid] as criteriadynamicid -> criteriaid
                                    foreach($descCritIDs[$DID][$ODID] as $CDID => $CID)
                                    {

                                        // If name is empty, skip it
                                        if (empty($descCritNames[$DID][$ODID][$CDID])) continue;
                                        
                                        // For these we will just assign them as sub criteria on the sub criteria
                                        $params = new stdClass();
                                        $params->name = $descCritNames[$DID][$ODID][$CDID];
                                        $params->details = $descCritDetails[$DID][$ODID][$CDID];
                                        
                                        $subSubObj = new CGHBNVQCriteria($descCritIDs[$DID][$ODID][$CDID], $params, Qualification::LOADLEVELCRITERIA);                            

                                        if(!$subSubObj->exists())
                                        {
                                            $subObj->add_sub_criteria($subSubObj);
                                        }
                                        else
                                        {
                                            // Exists, so just update
                                            $currentSubCriteria = $subObj->get_sub_criteria();
                                            $currentSubCriteria[$CID] = $subSubObj;
                                            $subObj->set_sub_criteria($currentSubCriteria);
                                        }

                                    }
                                }

                            if(!$subObj->exists())
                            {
                                $obj->add_sub_criteria($subObj);
                            }
                            else
                            {
                                // Exists, so just update
                                $currentSubCriteria = $obj->get_sub_criteria();
                                $currentSubCriteria[$OID] = $subObj;
                                $obj->set_sub_criteria($currentSubCriteria);
                            }
                        
                        }
                        
                    }
                    
                    // Standard sub criteria
                    if (isset($subCritIDs[$DID]))
                    {
                        
                        foreach($subCritIDs[$DID] as $SCDID => $SCID)
                        {
                            
                            // If name is empty, skip it
                            if (empty($subCritNames[$DID][$SCDID])) continue;
                            
                            $params = new stdClass();
                            $params->name = $subCritNames[$DID][$SCDID];
                            $params->details = $subCritDetails[$DID][$SCDID];
                            $params->ordernum = 1;
                            
                            if (!isset($subCritMarkable[$DID][$SCDID]))
                            {
                                $params->type = 'Read Only';
                            }
                            
                            $subCritObj = new CGHBNVQCriteria($SCID, $params, Qualification::LOADLEVELCRITERIA);                            
                            
                            if(!$subCritObj->exists())
                            {
                                $obj->add_sub_criteria($subCritObj);
                            }
                            else
                            {
                                // Exists, so just update
                                $currentSubCriteria = $obj->get_sub_criteria();
                                $currentSubCriteria[$SCID] = $subCritObj;
                                $obj->set_sub_criteria($currentSubCriteria);
                            }
                            
                            
                        }
                        
                    }
                                        
                    $this->criterias[$ID] = $obj;
                                        
                }
                
            }

                  
        }
        
        
        // Remove any not sent this time
        foreach ($this->criterias as $criteria)
        {
            if (!in_array($criteria->get_id(), $taskIDs)){
                unset($this->criterias[$criteria->get_id()]);
            }
        }
        
        
        
        
        // Sign Off Sheets
        if(isset($_POST['sheetIDs']))
        {
            
            $sheetIDs = $_POST['sheetIDs'];
            $sheetNames = $_POST['sheetNames'];
            $sheetNumObs = $_POST['sheetNumObs'];
            
            $rangeIDs = $_POST['rangeIDs'];
            $rangeNames = $_POST['rangeNames'];
            
            // Clear signoff sheets
            $this->signOffSheets = array();
            
            if(empty($sheetIDs) || empty($sheetNames)){
                return false;
            }
            
            // Loop through submitted sheets
            foreach($sheetIDs as $DID => $ID)
            {
                
                // If name is empty, skip it
                if (empty($sheetNames[$DID])) continue;
                
                // If the sheet doesn't exist on the unit, add it
                if(!isset($this->signOffSheets[$ID]))
                {
                    
                    $sheet = new stdClass();
                    if ($ID > 0) $sheet->id = $ID;
                    $sheet->name = $sheetNames[$DID];
                    $sheet->numofobservations = $sheetNumObs[$DID];
                    $sheet->bcgtunitid = $this->id;
                    $sheet->ranges = array();
                    
                        // Ranges on the sheet
                        if(isset($rangeIDs[$DID]))
                        {
                            foreach($rangeIDs[$DID] as $RDID => $RID)
                            {
                                
                                // If name is empty skip it
                                if (empty($rangeNames[$DID][$RDID])) continue;
                                
                                $range = new stdClass();
                                if ($RID > 0) $range->id = $RID;
                                $range->name = $rangeNames[$DID][$RDID];
                                if ($RID > 0) $sheet->ranges[$RID] = $range;
                                else $sheet->ranges[] = $range;
                            }
                        }
                    
                    $this->add_signoff_sheet($sheet);
                    
                }
                
                // Else, update it
                else
                {
                    $sheet = $this->signOffSheets[$ID];
                    $sheet->name = $sheetNames[$DID];
                    $sheet->numofobservations = $sheetNumObs[$DID];
                    $sheet->bcgtunitid = $this->id;
                    
                    // Loop through ranges and see if exist or not
                    if(isset($rangeIDs[$DID]))
                    {
                        foreach($rangeIDs[$DID] as $RDID => $RID)
                        {
                            
                            // If name is empty skip it
                            if (empty($rangeNames[$DID][$RDID])) continue;
                            
                            // CHeck if range exists on sheet
                            if(isset($sheet->ranges[$RID]))
                            {
                                // Update name
                                $range = $sheet->ranges[$RID];
                                $range->name = $rangeNames[$DID][$RDID];
                                $sheet->ranges[$RID] = $range;
                            }
                            else
                            {
                                // Add to sheet
                                $range = new stdClass();
                                if ($RID > 0) $range->id = $RID;
                                $range->name = $rangeNames[$DID][$RDID];
                                if ($RID > 0) $sheet->ranges[$RID] = $range;
                                else $sheet->ranges[] = $range;
                            }
                        }
                    }
                }
            }
        }
        
        
                
                         
    }
    
    
    public function insert_unit($trackingTypeID = CGHBNVQQualification::ID) {
        
        global $DB;
        
        parent::insert_unit($trackingTypeID);
        
        // Sign Off SHeets & Their Ranges
        if($this->get_signoff_sheets())
        {
                        
            foreach($this->get_signoff_sheets() as $sheet)
            {
                
                // Insert it

                $ranges = $sheet->ranges;
                unset($sheet->ranges);
                $sheet->bcgtunitid = $this->id;

                $sheet->id = $DB->insert_record("block_bcgt_signoff_sheet", $sheet);

                // Now any ranges
                if($ranges)
                {
                    foreach($ranges as $range)
                    {
                        $range->bcgtsignoffsheetid = $sheet->id;
                        $DB->insert_record("block_bcgt_soff_sheet_ranges", $range);
                    }
                }
                
            }
            
        }
        
    }
    
    
    public function update_unit($updateCriteria = true) {
        
        global $DB;
        
        parent::update_unit($updateCriteria);
        
        $this->check_signoff_sheets_removed();
        
        // Sign Off SHeets & Their Ranges
        if($this->get_signoff_sheets())
        {
                        
            foreach($this->get_signoff_sheets() as $sheet)
            {
                
                // Check if it already exists pn the unit
                if(isset($sheet->id) && $sheet->id > 0)
                {
                    
                    // Update it                    
                    $ranges = $sheet->ranges;
                    unset($sheet->ranges);
                    
                    $DB->update_record("block_bcgt_signoff_sheet", $sheet);
                    
                    // Now the ranges
                    if($ranges)
                    {
                        foreach($ranges as $range)
                        {
                            
                            // Check if range exists
                            if(isset($range->id) && $range->id > 0)
                            {
                                $DB->update_record("block_bcgt_soff_sheet_ranges", $range);
                            }
                            else
                            {
                                $range->bcgtsignoffsheetid = $sheet->id;
                                $DB->insert_record("block_bcgt_soff_sheet_ranges", $range);
                            }
                            
                        }
                    }
                    
                }
                else
                {
                    // Insert it
                    
                    $ranges = $sheet->ranges;
                    unset($sheet->ranges);
                    
                    $sheet->id = $DB->insert_record("block_bcgt_signoff_sheet", $sheet);
                    
                    // Now any ranges
                    if($ranges)
                    {
                        foreach($ranges as $range)
                        {
                            $range->bcgtsignoffsheetid = $sheet->id;
                            $DB->insert_record("block_bcgt_soff_sheet_ranges", $range);
                        }
                    }
                    
                }
                
            }
            
        }
        
    }
    
    
    public static function get_edit_form_menu($disabled = '', $unitID = -1)
	{
        $jsModule = array(
            'name'     => 'mod_bcgtcg',
            'fullpath' => '/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        global $PAGE;
        $PAGE->requires->js_init_call('M.mod_bcgtcg.cginiteditunit', null, true, $jsModule);
        
        $pathwayID = optional_param('pathway', -1, PARAM_INT);
        $pathwayTypeID = optional_param('type', -1, PARAM_INT);
        $weight = optional_param('weighting', "1.0", PARAM_FLOAT);
        
        $pathways = get_pathway_from_type(CGQualification::FAMILYID);
        
        if ($unitID > 0){
            
            $unit = new CGHBVRQUnit($unitID, null, null);
            if ($unit)
            {
                $weight = $unit->get_weighting();
                $both = get_pathway_and_type_from_dep_type($unit->get_pathway_type());
                $pathwayID = $both->pathway;
                $pathwayTypeID = $both->type;
            }
            
        }
        
        
        $retval = "";
        
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='pathway'><span class='required'>*</span>".get_string('pathway', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<select name='pathway' {$disabled} id='unitPathway'>";
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
        
        
        if ($pathwayID >= 1){
            
            $types = get_pathway_types_from_pathway($pathwayID);
            
            if ($types)
            {
                $retval .= "<div class='inputContainer'>";
                    $retval .= "<div class='inputLeft'>";
                        $retval .= "<label for='type'><span class='required'>*</span>".get_string('type', 'block_bcgt') . ": </label>";
                    $retval .= "</div>";
                    $retval .= "<div class='inputRight'>";
                        $retval .= "<select name='type' {$disabled} id='unitPathwayType'>";
                            $retval .= "<option value=''>".get_string('pleaseselect', 'block_bcgt')."...</option>";

                            foreach($types as $key => $type)
                            {
                                $sel = ($pathwayTypeID == $key) ? 'selected' : '';
                                $retval .= "<option value='{$key}' {$sel} >{$type}</option>";
                            }

                        $retval .= "</select>";
                    $retval .= "</div>";
                $retval .= "</div>";
                
                
            }
            else
            {
                $retval .= "<select name='type' {$disabled} id='unitPathwayType'>";
                    $retval .= "<option value=''>".get_string('notypesforthispathway', 'block_bcgt')."</option>";
                $retval .= "</select>";
            }
            
            
        }
        
       
        
        
        
        
        return $retval;
        
    }
    
    /**
     * Can be overridden by the other units
     * @return type
     */
    public function get_criteria_header()
	{
		return "<br><br>" . get_string('tasks', 'block_bcgt');
	}
    
    
    /**
	 * Used in edit unit
	 * Gets the criteria tablle that will go on edit_unit_form.php
	 * This is different for each unit type. 
	 */
	public function get_edit_criteria_table()
    {
        
        $retval = "";
        
        $family = optional_param('unitTypeFamily', 0, PARAM_INT);#
        $pathway = optional_param('pathway', 0, PARAM_INT);
        $pathwayType = optional_param('type', 0, PARAM_INT);
        
        if ( ($this->id > 0) || ( $family > 0 && $pathway > 0 && $pathwayType > 0 ) )
        {
            
            $retval .= "<script> var numOfTasks = 0; var dynamicNumOfTasks = 0; var overallNumOutcomes = 0; var arrayOfOutcomes = new Array(); var MAX_OBSERVATIONS_ON_OUTCOME = ".self::MAX_OBSERVATIONS_ON_OUTCOME."; var overallNumDescCrit = 0; var arrayOfDescCriteria = new Array(); var overallNumSubCriteria = 0; var arrayOfSubCriteria = new Array(); </script>";
            $retval .= "<a href='#' id='addNewHBNVQTask'>".get_string('addtask', 'block_bcgt')."</a><br><br>";
            
            $retval .= "<table id='criteriaHolder' class='cgCriteriaHolderTable cgHBNVQTaskHolderTable'>";

               $retval .=  $this->build_criteria_form();

            $retval .= "</table>";
            $retval .= "<br><br>";
            
            // Signoff sheets
            $retval .= "<hr><br><br>";
            $retval .= "<h3 class='subTitle'>".get_string('signoffsheets', 'block_bcgt')."</h3>";
            
            $retval .= "<script> var dynamicSignOffID = 0; var numOfSignOffSheets = 0; arrayOfSheetRanges = new Array(); var numOfSheetRanges = 0; var numOfSheetRanges = 0; </script>";
            $retval .= "<a href='#' id='addNewHBNVQSignOffSheet'>".get_string('addsignoffsheet', 'block_bcgt')."</a><br><br>";
            
            $retval .= "<table id='signoffSheetHolder' class='cgCriteriaHolderTable cgHBNVQSSHolderTable'>";

                $retval .= $this->build_signoff_sheet_form();
            
            $retval .= "</table><br><br>";
                        
        }
         else
        {
            $retval .= "<p>".get_string('criterianotavailableuntilformcomplete', 'block_bcgt')."</p>";
        }
        
        return $retval;
        
    }
    

    /**
	 * Gets the used criteria names from this unit. 
	 * @return multitype:
	 */
	protected function get_used_criteria_names_()
	{
        global $CFG;
        
		$usedCriteriaNames = array();
        
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
        $criteriaSorter = new CGCriteriaSorter();
        uasort($this->criterias, array($criteriaSorter, "ComparisonOrder"));
        
		if($this->criterias)
		{
			foreach($this->criterias AS $criteria)
			{
				$usedCriteriaNames[] = $criteria->get_name();
			}
		}
        
		return $usedCriteriaNames;
	}
    
    protected function build_signoff_sheet_form()
    {
        
        global $CFG, $DB;
        
        $retval = "";
                
        $retval .= "<tr>";
            $retval .= "<th>".get_string('name', 'block_bcgt')."</th>";
            $retval .= "<th>".get_string('numobservations', 'block_bcgt')."</th>";
            $retval .= "<th>".get_string('ranges', 'block_bcgt')."</th>";
            $retval .= "<th></th>";
        $retval .= "</tr>";
        
        // Loop signoff sheets
        $this->load_sign_off_sheets();
        if ($this->get_signoff_sheets())
        {
            
            $d = 0;
            
            foreach($this->get_signoff_sheets() as $sheet)
            {
                
                $d++;
                
                $retval .= "<script> numOfSignOffSheets++; dynamicSignOffID = {$d}; if( arrayOfSheetRanges[{$d}] == undefined ) { arrayOfSheetRanges[{$d}] = new Array(); } </script>";
                
                 $retval .= '<tr>';
                    $retval .= '<td><input type="hidden" name="sheetIDs['.$d.']" value="'.$sheet->id.'" /><input type="text" name="sheetNames['.$d.']" value="'.$sheet->name.'" /></td>';
                    $retval .= '<td><input type="number" min="1" max="'.self::MAX_OBSERVATIONS_ON_OUTCOME.'" name="sheetNumObs['.$d.']" value="'.$sheet->numofobservations.'" /></td>';
                    $retval .= '<td id="signoffSheetRangeCell_'.$d.'"><a href="#" onclick="addNewSignOffRange('.$d.');return false;"><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" alt="add" /></a> <table id="signoffSheetRangeHolder_'.$d.'" class="signoffSheetRangeTable">';
                    
                    // Signoff ranges
                    $ranges = $DB->get_records("block_bcgt_soff_sheet_ranges", array("bcgtsignoffsheetid" => $sheet->id));
                    if ($ranges)
                    {
                        $n = 0;
                        foreach($ranges as $range)
                        {
                            $n++;
                            
                            $retval .= "<script> numOfSheetRanges++; arrayOfSheetRanges[{$d}].push({$n}); </script>";
                            
                            $retval .= '<tr>';
                                $retval .= '<td>Range Name:</td>';
                                $retval .= '<td><input type="hidden" name="rangeIDs['.$d.']['.$n.']" value="'.$range->id.'" /><input type="text" name="rangeNames['.$d.']['.$n.']" value="'.$range->name.'" /></td>';
                            $retval .= '</tr>';
                        }
                    }
                    
                    $retval .= '</table></td>';
                $retval .= '</tr>';
                
            }
        }
        
        
        return $retval;
        
    }
    
    
    protected function build_criteria_form(){
     
        global $CFG, $DB;
        
        $retval = "";
                
        $retval .= "<tr>";
            $retval .= "<th>".get_string('name', 'block_bcgt')."</th>";
            $retval .= "<th>".get_string('details', 'block_bcgt')."</th>";
            $retval .= "<th>".get_string('targetdate', 'block_bcgt')."</th>";
            $retval .= "<th>".get_string('order', 'block_bcgt')."</th>";
            $retval .= "<th></th>";
        $retval .= "</tr>";
        
        
        // Loop through criteria and build up form the same as js form
        if ($this->criterias)
        {
            
            $d = 0;
            
            foreach($this->criterias as $criteria)
            {
                
                $d++;
                
                $retval .= "<script> numOfTasks++; dynamicNumOfTasks = {$d};  </script>";

                
                $retval .= '<tr class="taskRow_'.$d.'">';
                    $retval .= '<td><input type="hidden" name="taskIDs['.$d.']" value="'.$criteria->get_id().'" /><input type="text" placeholder="Name" name="taskNames['.$d.']" value="'.$criteria->get_name().'" class="critNameInput" id="taskName_'.$d.'" /></td>';
                    $retval .= '<td><textarea style="width:100%;" placeholder="Task Details" name="taskDetails['.$d.']" id="taskDetails'.$d.'" class="critDetailsTextArea">'.$criteria->get_details().'</textarea></td>';
                    $retval .= '<td><input type="text" name="taskTargetDates['.$d.']" class="bcgtDatePicker" value="'.$criteria->get_target_date().'" /> </td>';
                    $retval .= '<td><input type="text" class="w40" name="taskOrders['.$d.']" value="'.$criteria->get_order().'" /></td>';
                    $retval .= '<td><a href="#" onclick="removeTaskTable('.$d.');return false;"><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/redX.png" /></a></td>';
                $retval .= '</tr>';
                
                // Outcome & Descriptive criteria row
                $retval .= '<tr class="taskRow_'.$d.'">';
                    $retval .= '<td colspan="5" class="cgHBNVQOutcomeCriteriaCell">';

                        $retval .= '<table id="Task_'.$d.'_OutcomeTable" class="criteriaOutcomeTable">';

                            $retval .= '<tr><th><a style="vertical-align:top;" href="#" onclick="addNewHBNVQOutcome('.$d.');return false;"><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" /></a> Outcome</th><th>Descriptive Criteria</th></tr>';

                            if ($criteria->get_sub_criteria())
                            {
                                
                                $num = 0;
                                
                                foreach($criteria->get_sub_criteria() as $subCriteria)
                                {
                                    
                                    if ($subCriteria->get_number_of_observations() <= 0) continue;
                                    
                                    $num++;
                                    
                                    $retval .= "<script> overallNumOutcomes++; if(arrayOfOutcomes[{$d}] == undefined) { arrayOfOutcomes[{$d}] = new Array(); } arrayOfOutcomes[{$d}].push({$num}); </script>";
                                    
                                    $retval .= '<tr id="outcomeRow_'.$num.'">';
    
                                    // Outcome cell
                                    $retval .= '<td style="width:40%;">';
                                        $retval .= '<table>';

                                            $retval .= '<tr>';
                                                $retval .= '<td>Name</td>';
                                                $retval .= '<td><input type="hidden" name="outcomesIDs['.$d.']['.$num.']" value="'.$subCriteria->get_id().'" /><input type="text" name="outcomeNames['.$d.']['.$num.']" value="'.$subCriteria->get_name().'" class="rangeInput" /></td>';
                                            $retval .= '</tr>';

                                            $retval .= '<tr>';
                                                $retval .= '<td>Details</td>';
                                                $retval .= '<td><textarea name="outcomeDetails['.$d.']['.$num.']">'.$subCriteria->get_details().'</textarea></td>';
                                            $retval .= '</tr>';

                                            $retval .= '<tr>';
                                                $retval .= '<td>Target Date</td>';
                                                $retval .= '<td><input type="text" class="bcgtDatePicker" name="outcomeDates['.$d.']['.$num.']" value="'.$subCriteria->get_target_date().'" /></td>';
                                            $retval .= '</tr>';

                                            $retval .= '<tr>';
                                                $retval .= '<td>No. Observations</td>';
                                                $retval .= '<td><input type="number" name="outcomeNumOfObservations['.$d.']['.$num.']" min="1" max="'.self::MAX_OBSERVATIONS_ON_OUTCOME.'" value="'.$subCriteria->get_num_observations().'" onblur="checkCCNum(this);return false;" /> <small class="output" style="color:red;"></small></td>';
                                            $retval .= '</tr>';

                                            $retval .= '<tr>';
                                                $retval .= '<td>Descriptive Criteria</td>';
                                                $retval .= '<td><a href="#" onclick="addNewHBNVQDescCriteria('.$d.', '.$num.');return false;"><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" alt="add" /></a></td>';
                                            $retval .= '</tr>';

                                        $retval .= '</table>';
                                    $retval .= '</td>';


                                    // Criteria cell
                                    $retval .= '<td>';
                                        $retval .= '<table id="taskCriteriaTable_'.$d.'_'.$num.'">';

                                            $subCriteria->load_sub_criteria();
                                            if ($subCriteria->get_sub_criteria())
                                            {
                                                
                                                $subNum = 0;
                                                
                                                foreach($subCriteria->get_sub_criteria() as $descCriteria)
                                                {
                                                    
                                                    
                                                    $subNum++;
                                                    
                                                    $retval .= "<script>  overallNumDescCrit++; if (arrayOfDescCriteria[{$d}] == undefined) { arrayOfDescCriteria[{$d}] = new Array(); } if (arrayOfDescCriteria[{$d}][{$num}] == undefined) { arrayOfDescCriteria[{$d}][{$num}] = new Array(); } arrayOfDescCriteria[{$d}][{$num}].push({$subNum});  </script>";
                                                    
                                                    $retval .= '<tr id="outcomeCriteriaRow_'.$num.'_'.$subNum.'">';
                                                        $retval .= '<td><input type="hidden" name="descCritIDs['.$d.']['.$num.']['.$subNum.']" value="'.$descCriteria->get_id().'" /><input type="text" name="descCritNames['.$d.']['.$num.']['.$subNum.']" value="'.$descCriteria->get_name().'" class="critNameInput" /></td>';
                                                        $retval .= '<td><input type="text" placeholder="Details..." style="width:350px;" name="descCritDetails['.$d.']['.$num.']['.$subNum.']" value="'.$descCriteria->get_details().'" /></td>';
                                                    $retval .= '</tr>';
                                                    
                                                }
                                            }

                                        $retval .= '</table>';
                                    $retval .= '</td>';

                                $retval .= '</tr>';
                                    
                                }
                                
                            }
                            
                            
                        $retval .= '</table>';

                    $retval .= '</td>';
                $retval .= '</tr>';
                
                
                // Sub criteria row - E3, E4 criteria
                $retval .= '<tr class="subCriteriaRow taskRow_'.$d.'">';
                    $retval .= '<td colspan="5" class="cgHBNVQSubCriteriaCell">';

                        $retval .= '<table id="Task_'.$d.'_SubCriteriaTable" class="criteriaSubCriteriaTable">';

                            $retval .= '<tr><th colspan="3"><a style="vertical-align:top;" href="#" onclick="addNewHBNVQSubCriteria('.$d.');return false;"><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" /></a> Sub Criteria</th></tr>';

                            if ($criteria->get_sub_criteria())
                            {
                                
                                $subnum = 0;
                                
                                foreach($criteria->get_sub_criteria() as $subCriteria)
                                {
                                    
                                    if ($subCriteria->get_number_of_observations() > 0) continue;
                                    
                                    $subnum++;
                                                    
                                    $retval .= "<script>  overallNumSubCriteria++; if (arrayOfSubCriteria[{$d}] == undefined) { arrayOfSubCriteria[{$d}] = new Array(); }  arrayOfSubCriteria[{$d}].push({$subnum});  </script>";

                                    $retval .= '<tr id="subCriteriaRow_'.$d.'_'.$subnum.'">';
                                        $retval .= '<td><small>Name</small><br><input type="hidden" name="subCritIDs['.$d.']['.$subnum.']" value="'.$subCriteria->get_id().'" /><input type="text" name="subCritNames['.$d.']['.$subnum.']" value="'.$subCriteria->get_name().'" class="critNameInput" /></td>';
                                        $retval .= '<td><small>Details</small><br><input type="text" placeholder="Details..." style="width:350px;" name="subCritDetails['.$d.']['.$subnum.']" value="'.$subCriteria->get_details().'" /></td>';
                                        $retval .= '<td><small>Markable?</small><br><input type="checkbox" name="subCritMarkable['.$d.']['.$subnum.'] value="1" '. ( ($subCriteria->get_type() != 'Read Only') ? 'checked' : '' ) .' />';
                                    $retval .= '</tr>';
                                    
                                }
                                
                            }
                            
                        $retval .= '</table>';

                    $retval .= '</td>';
                $retval .= '</tr>';
                
                $retval .= '<tr class="sepRow"><td>&nbsp;</td></tr>';
                
                
            }
        }
        
        
        return $retval;
        
    }
    
    
     /**
     * Given the name of a parent criteria, get the maximum number of sub criteria which might fall under it on this
     * qualification, so we can set the colspan of the <th> element correctly
     * @param type $name 
     */
    public function get_max_sub_criteria_of_criteria($name)
    {
        
        $array = array();

        // Find criteria on this unit with name
        $criteria = $this->find_criteria_by_name($name);
        if(!$criteria) return 1;

        // Get the sub criteria on this criteria
        $criteria->load_sub_criteria();
        $sub = $criteria->get_sub_criteria();

        if(!$sub) return 1;

        foreach($sub as $subCriteria)
        {
            if(!in_array($subCriteria->get_name(), $array) && $subCriteria->get_number_of_observations() > 0) $array[] = $subCriteria->get_name();
        }             
        
        return ($array) ? count($array) : 1;

    }
    
    
    /**
     * Same as above, except it doesn't expect the sub criteria to be in "Par." format
     * @param type $parent 
     */
    function get_distinct_list_of_all_sub_criteria($parent)
    {
        global $CFG, $DB;
        
        if (isset($this->distinctSubCriteria[$parent])){
            return $this->distinctSubCriteria[$parent];
        }
        
        if (!isset($this->distinctSubCriteria)){
            $this->distinctSubCriteria = array();
        }
        
        $sql = "SELECT DISTINCT(c.name)
                FROM {block_bcgt_qual_units} qu 
                LEFT JOIN {block_bcgt_criteria} c ON c.bcgtunitid = qu.bcgtunitid 
                LEFT JOIN {block_bcgt_criteria} p ON p.id = c.parentcriteriaid
                WHERE qu.bcgtunitid = ? 
                AND c.name IS NOT NULL 
                AND c.parentcriteriaid IS NOT NULL
                AND c.numofobservations > 0
                AND p.name = ?";

        $records = $DB->get_records_sql($sql, array($this->id, $parent));

        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
        $criteriaSorter = new CGCriteriaSorter();
        if($records) usort($records, array($criteriaSorter, "ComparisonOnTheFly"));

        $this->distinctSubCriteria[$parent] = $records;
        
        return $this->distinctSubCriteria[$parent];
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
    protected function get_unit_grid_header($criteriaNames, $grid, $context, $qualID = null)
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
		$header .= "<thead>";
		
		$header .= "<tr class='mainRow'>";
                
		//denotes projects
        if($advancedMode && $editing)
        {
            $header .= "<th class='unitComment'></th>";
        }
        else
        {
            $header .= "<th></th>";
        }
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
            $header .= "<th>";
            $header .= get_string(trim($column), 'block_bcgt');
            $header .="</th>";
        }

        // HBVRQ doesn't have qual award
        $header .= "<th>".get_string('unitaward', 'block_bcgt')."</th>";

        $totalHeaderCount = 7;
        // If unit has % completions enabled
        if($this->has_percentage_completions() && !$printGrid){
            $header .= "<th>% Complete</th>";
            $totalHeaderCount++;
        }
		
        //if we are doing it by project
        //then order the projects by due date
        //for each projects        
                
		$criteriaCountArray = array();
		if($criteriaNames)
		{
			foreach($criteriaNames as $criteriaName)
            {

                // Count how many ranges are on this task, so we know what colspan to use
                $max = $this->get_max_sub_criteria_of_criteria($criteriaName);
                $tName = str_replace(" ", "_", htmlentities($criteriaName, ENT_QUOTES));

                if ($max > 1){
                    $header .= "<th class='toggleTD_{$tName}' colspan='{$max}' defaultcolspan='{$max}'><a class='taskName' href='#' onclick='toggleOverallTasks(\"{$tName}\");return false;'>{$criteriaName}</a></th>";
                } else {
                    $header .= "<th colspan='{$max}' defaultcolspan='{$max}'>{$criteriaName}</th>";
                }

                $totalHeaderCount++;

            }
        }
        
        // Signoff
        $header .= "<th>Sign-off</th>";
        
        // IV
        $header .= "<th>IV</th>";
        
		$header .= "</tr>";
        
        $header .= $this->get_sub_criteria_header($criteriaNames);
        
        $header .= "</thead>";
		
		$headerObj->header = $header;
		$headerObj->criteriaCountArray = $criteriaCountArray;
		//$headerObj->orderedCriteriaNames = $criteriaNames;
        $headerObj->totalHeaderCount = $totalHeaderCount;
                
		return $headerObj;
        
	}
    
    
    private function get_sub_criteria_header($criteriaNames)
    {
        
        $output = "";
        
        $output .= "<tr>";

        $output .= "<th></th>"; # Unit Comments
        #
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
            $output .= "<th></th>";
        }
        
        $output .= "<th></th>"; # Unit Award
        if ($this->has_percentage_completions()) $output .= "<th></th>"; # %
                
        foreach($criteriaNames as $criteriaName)
        {
            
            $colspan = $this->get_max_sub_criteria_of_criteria($criteriaName);
            $subCriteria = $this->get_distinct_list_of_all_sub_criteria($criteriaName);
                        
            $tName = str_replace(" ", "_", htmlentities($criteriaName, ENT_QUOTES));
            
            $num = 0;
            
            if($subCriteria)
            {
                
                // Hidden column
                $output .= "<th class='taskHidden_{$tName}' style='display:none;'></th>";
                
                foreach($subCriteria as $criteria)
                {
                    $output .= "<th class='taskClass_{$tName}'>{$criteria->name}</th>";
                    $num++;
                }
            }
                        
            if($num < $colspan)
            {
                while($num < $colspan)
                {
                    $output .= "<th></th>";
                    $num++;
                }
            }
            
        }
        
        $output .= "<th></th>"; # Signoff col
        $output .= "<th></th>"; # IV col
        $output .= "</tr>";
        
        return $output;
        
    }
    
        
    
    /**
     * Get the IV table cell
     * @param type $editing
     * @return string
     */
    public function get_iv_td($editing)
    {
        
        $output = "";
        
        $output .= "<td class='ivTD'>";
        
        if($editing)
        {
            $output .= "<small>Date:</small><br>";
            $output .= "<input type='text' name='IVDATE' style='width:70px;' studentID='{$this->studentID}' unitID='{$this->id}' qualID='{$this->qualID}' class='datePickerIV' value='{$this->get_attribute('IVDATE', $this->qualID, $this->studentID)}' /><br>";
            $output .= "<small>Who:</small><br>";
            $output .= "<input name='IVWHO' type='text' class='updateUnitAttribute' style='width:70px;' studentID='{$this->studentID}' unitID='{$this->id}' qualID='{$this->qualID}' value='{$this->get_attribute('IVWHO', $this->qualID, $this->studentID)}' />";
        }
        else
        {
            $ivdate = $this->get_attribute('IVDATE', $this->qualID, $this->studentID);
            if(!$ivdate) $ivdate = 'N/A';
            $ivwho = $this->get_attribute('IVWHO', $this->qualID, $this->studentID);
            if(!$ivwho) $ivwho = 'N/A';
            $output .= "<small>{$ivdate}</small><br>";
            $output .= "<small><strong>{$ivwho}</strong></small>";
        }
        
        
        $output .= "</td>";
        
        return $output;
        
    }
    
    
    
    function calculate_unit_award($qualID, $update = true){
        
        global $DB, $USER;
        
        if (!$this->criterias) return;
       
        // If it already has an award, don't try to recalculate, since this only has pass who cares
        if ($this->userAward)
        {
            if ($this->userAward->get_id() > 0)
            {
                return $this->userAward;
            }
        }
        
        // This is kind of sneaky, but let's see how it goes and if it works as I expect
        if($this->get_percent_completed() == 100)
        {
            $awardRecord = $this->get_unit_award(1); # Only 1, as is a pass/fail qualification
            $params = new stdClass();
            $params->award = $awardRecord->award;
            $params->rank = $awardRecord->ranking;
            
            $award = new Award($awardRecord->id, $params);
            $this->userAward = $award;
            $this->update_unit_award($qualID);
            
            return $award;
            
        }
        
        $this->userAward = new Award(-1, 'N/S', 0);
        $this->update_unit_award($qualID);
        return null; 
                    
        
    }
    
    public function get_percent_completed()
    {
            
        // We'll calculate completion based on standard tasks with no sub-criteria, and whole ranges
        // E.g. Task 1, Task 2 (Range 1, Range 2, Range 3) would be 4 things that need completing.
        
        $criteria = $this->criterias;
        if(!$criteria) return 100; # No criteria, so is it 0% complete or 100% complete?
         
        $count = 0;
        
        foreach($criteria as $criterion)
        {
            
            // We're only interseted if it's a standard task at the moment
            if ($criterion->has_outcomes())
            {
                // Has sub criteria/ranges
                $count += count($criterion->get_sub_criteria());
            }
            else
            {
                // Increment cnt
                $count++;
            }
                        
            
        }
        
        if($this->load_sign_off_sheets()) $count++; // Sign off sheets
        
        reset($criteria);
                
        $numCompleted = $this->are_criteria_completed($criteria);
        $percent = round(($numCompleted * 100) / $count);             
                
        return $percent;
            
    }
    
    protected function are_criteria_completed($criteria, $sub = false)
    {
            
        if(!$criteria) return 0;

        $numCompleted = 0;
                
        foreach($criteria as $criterion)
        {
                    
            $criterion->load_student_information($this->studentID, $this->qualID, $this->id);
                
            if ($criterion->has_outcomes())
            {
                
                // Outcomes with observations
                $outcomes = $criterion->get_sub_criteria();
                
                foreach($outcomes as $outcome)
                {
                    // Check that there is an award date for each observation on this outcome
                    $award = $outcome->get_student_value();
                    if($award)
                    {
                        if($award->is_criteria_met_bool()) {
                            $numCompleted++;
                        }
                    }
                    
                }

            }
            else
            {
                $award = $criterion->get_student_value();
                if($award)
                {
                    if($award->is_criteria_met_bool()){
                        $numCompleted++;
                    }
                }
            }

        }
        
        // Now the signoff sheets
        $sheets = $this->load_sign_off_sheets();
        
        if($sheets)
        {
            if($this->are_all_sign_offs_complete()) $numCompleted++;
        }

        return $numCompleted;
            
    }
    
    
    
    
    
    function build_unit_details_table(){
        
        global $CFG;
        
        $output = "";
        $output .= "<div id='unitName{$this->id}' class='unitTooltipContent'><div>";
            $output .= "<h3>{$this->name}</h3>";
            $output .= "<table>";
                $output .= "<tr><th>Task Name</th><th>Task Details</th></tr>";
                if($criteriaList = $this->criterias)
                {
                    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
                    $criteriaSorter = new CGCriteriaSorter();
                    usort($criteriaList, array($criteriaSorter, "Comparison"));
                    foreach($criteriaList as $task)
                    {
                        $output .= "<tr class='lightpink bordered'><th>{$task->get_name()}</th><td>{$task->get_details()}</td></tr>";
                        
                        // If the task has sub criteria, list them as well
                        if($subCriteria = $task->get_sub_criteria())
                        {
                            foreach($subCriteria as $sub)
                            {
                                $output .= "<tr class='lightpink bordered'><th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$sub->get_name()}</th><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$sub->get_details()}</td></tr>";
                            }
                        }
                        
                    }
                }
            $output .= "</table>";
        $output .= "</div></div>";
        return $output;  
        
    }

    
    
    
    
    function update_any_beyond_target()
    {
        
        global $DB;
        $now = time();
        
        // First check the standard tasks (criteria) which don't have any ranges/criteria linked to them
        if($this->criterias)
        {
            foreach($this->criterias as $task)
            {
                                                
                // Outcomes
                if ($task->has_outcomes())
                {
                                        
                    // Outcomes (sub criteria)
                    foreach($task->get_sub_criteria() as $outcome)
                    {
                                                
                        if(!$outcome->get_target_date_unix()) continue;
                        if ($outcome->get_number_of_observations() <= 0) continue;
                        
                        // If the target date is > now and the task hasn't got an award, set to X
                        $award = $outcome->get_student_value();

                        if( !$award && $now > $outcome->get_target_date_unix())
                        {
                            // Get the "PTD" award for this type
                            $record = $DB->get_record("block_bcgt_value", array("bcgttypeid" => $outcome->get_tracking_type(), "shortvalue" => "PTD"));
                            if($record)
                            {
                                $outcome->update_students_value_auto($record->id);
                                $outcome->save_student($outcome->get_qual_ID());
                            }
                        }
                        
                    }
                    
                }
                
                // Normal sub criteria
                elseif ($task->get_sub_criteria())
                {
                                        
                    foreach($task->get_sub_criteria() as $subCriteria)
                    {
                                             
                        if (!$subCriteria->get_target_date_unix()) continue;
                        if ($subCriteria->get_number_of_observations() > 0) continue;
                                                
                        // If the target date is > now and the task hasn't got an award, set to X
                        $award = $subCriteria->get_student_value();

                        if( !$award && $now > $subCriteria->get_target_date_unix())
                        {
                            // Get the "PTD" award for this type
                            $record = $DB->get_record("block_bcgt_value", array("bcgttypeid" => $subCriteria->get_tracking_type(), "shortvalue" => "PTD"));
                            if($record)
                            {
                                $subCriteria->update_students_value_auto($record->id);
                                $subCriteria->save_student($subCriteria->get_qual_ID());
                            }
                        }
                        
                    }
                    
                }

                // Standard criteria - check anyway                                    
                if(!$task->get_target_date_unix()) continue;

                // If the target date is > now and the task hasn't got an award, set to X
                $award = $task->get_student_value();

                if(( ($award && $award->get_short_value() == "NA") || !($award)) && $now > $task->get_target_date_unix())
                {
                    // Get the "PTD" award for this type
                    $record = $DB->get_record("block_bcgt_value", array("bcgttypeid" => $task->get_tracking_type(), "shortvalue" => "PTD"));
                    if($record)
                    {
                        $task->update_students_value_auto($record->id);
                        $task->save_student($task->get_qual_ID());
                    }
                }
                                               
                
            }
            
        }        
        
        
    }

    
    
    
     /**
     * displays the unit grid. 
     */
    public function display_unit_grid()
    {
        
        global $COURSE, $PAGE, $CFG;
        
        $retval = '<div>';
        $retval .= "<input type='submit' id='viewsimple' class='gridbuttonswitch viewsimple' name='viewsimple' value='View Simple'/>";
        $retval .= "<input type='submit' id='viewadvanced' class='gridbuttonswitch viewadvanced' name='viewadvanced' value='View Advanced'/>";
        $retval .= "<br>";
        $courseID = optional_param('cID', -1, PARAM_INT);
        $qualID = optional_param('qID', -1, PARAM_INT);
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
        $advancedMode = false;
        $editing = false;
        if($grid == 'ae' || $grid == 'se')
        {
            $editing = true;
        }
        if($grid == 'a' || $grid == 'ae')
        {
            $advancedMode = true;
        }    
        
        //we need to work out how many columns are being locked and
        //what the widths are
        //default is columns (assignments, comments, unitaward)
        $columnsLocked = 3;
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
            'name'     => 'mod_bcgtcg',
            'fullpath' => '/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        //
        
        $PAGE->requires->js_init_call('M.mod_bcgtcg.inithbvrqunitgrid', array($qualID, $this->id), true, $jsModule);
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        load_javascript(true);
        $retval .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/css/start/jquery-ui-1.10.3.custom.min.css' />";
        
        $retval .= "
		<div class='gridKey adminRight'>";
        $retval .= "<h2>Key</h2>";
        //Are we looking at a student or just the actual criteria for the grid.
        //if students then get the key that tells everyone what things stand for
        $retval .= CGHBNVQQualification::get_grid_key();
		$retval .= "</div>";
        
        $retval .= "<br style='clear:both;' /><br>";
        
        //the grid -> ajax
        $retval .= '<div id="cgUnitGrid">';
        
        
        $retval .= "<div id='unitGridDiv' class='unitGridDiv ".
        $grid."UnitGrid tableDiv'><table align='center' class='unit_grid CGHB ".
                $grid."FixedTables' id='CGUnitGrid'>";
        $criteriaNames = $this->get_used_criteria_names_();
		
               
		$headerObj = $this->get_unit_grid_header($criteriaNames, $grid, $context, $qualID);
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
        $retval .= "</table>";
        $retval .= "</div>";
        $retval .= '</div>';
        $retval .= '</div>';
        
        
        //the buttons.
        return $retval;
        
       
        
    }   
    
    
    /**
     * 
     * @param type $qualID
     * @param type $advancedMode
     * @param type $editing
     */
    public function get_unit_grid_data($qualID, $advancedMode, $editing, $courseID)
    {
        
        global $CFG, $DB, $COURSE;
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        $criteriaNames = $this->get_used_criteria_names_();
        
        // ORDER BY ORDER NUM
        $retval = "";
        $unitAwards = null;
        if($editing)
        {
            $unitAwards = Unit::get_possible_unit_awards($this->get_typeID());
        }
        
                        
        
        //load the students that are on this unit for this qual. 
        $studentsArray = get_users_on_unit_qual($this->id, $qualID);
        
        if ($studentsArray)
        {
            
            $rowCount = 0;
            
            foreach($studentsArray as $student)
            {
                
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELCRITERIA;
                $loadParams->loadAward = true;
                                
                $this->load_student_information($student->id, $qualID, $loadParams);
                $this->update_any_beyond_target();
                                               
                
                $rowClass = 'rO';
                if($rowCount % 2)
                {
                    $rowClass = 'rE';
                }
                
                $retval .= "<tr class='{$rowClass} setStudent' studentID='{$student->id}'>";
                
                
                // First column is for unit comment
                $getComments = $this->get_comments();
                
                $cellID = "cmtCell_U_{$this->get_id()}_S_{$student->id}_Q_{$qualID}";
                
		        
                $username = htmlentities( $student->username, ENT_QUOTES );
                $fullname = htmlentities( fullname($student), ENT_QUOTES );
                $unitname = htmlentities( $this->get_name(), ENT_QUOTES);
                $critname = "N/A";   
                
                $retval .= "<td title='title'>";

                if($advancedMode && $editing)
                {

                    if(!empty($getComments))
                    {                
                        $retval .= "<img id='{$cellID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' qualid='{$qualID}' unitid='{$this->id}' studentid='{$this->studentID}' grid='stud' type='button' class='editCommentsUnit' title='Click to Edit Unit Comments' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/comments.jpg' />";
                        $retval .= "<div class='tooltipContent'>".nl2br( htmlspecialchars($getComments, ENT_QUOTES) )."</div>";
                    }
                    else
                    {                        
                        $retval .= "<img id='{$cellID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' qualid='{$qualID}' unitid='{$this->id}' studentid='{$this->studentID}' grid='stud' type='button' class='addCommentsUnit' title='Click to Add Unit Comment' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/plus.png' />";
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
                
                
                
                // Next columns are the default ones like picture, name, etc...
                $cols = $this->build_unit_grid_students_details($student, $qualID, array(), $context);
                if ($cols)
                {
                    foreach($cols as $col)
                    {
                        $retval .= "<td>{$col}</td>";
                    }
                }
                
                                
                // Unit award
                $award = 'N/S';
				$rank = 'nr';
                
                //get the users award from the unit
                $unitAward = $this->get_user_award();   
                if($unitAward)
                {
                    $rank = $unitAward->get_rank();
                    $award = $unitAward->get_award();
                }	
                
                if($editing)
                {
                    $retval .= "<td>".$this->edit_unit_award($this, $rank, $award, $qualID, $unitAwards)."</td>";
                }
                else
                {
                    $retval .= '<td><span id="unitAward_'.$this->get_id().'_'.$student->id.'">'.$award.'</span></td>';
                }
                
                // % complete
                if($this->has_percentage_completions()){
                    $retval .= "<td><div class='tdPercentCompleted'>".$this->display_percentage_completed()."</div></td>";
                }
                
                // Tasks
                
                if ($criteriaNames)
                {
                    
                    foreach($criteriaNames as $criteriaName)
                    {
                        
                         $studentCriteria = $this->get_single_criteria(-1, $criteriaName);
                         if ($studentCriteria)
                         {
                            $colspan = $this->get_max_sub_criteria_of_criteria($criteriaName);
                            $listOfOutcomes = $this->get_distinct_list_of_all_sub_criteria($criteriaName);
                            $retval .= $studentCriteria->get_grid_td_($editing, $advancedMode, $this, $student, $qualID, 'unit', $listOfOutcomes, $colspan);
                         }
                         else
                         {
                             $retval .= "<td>-</td>";
                         }
                        
                    }
                    
                }
                
                // Signoff Sheet
                if($this->get_signoff_sheets())
                {
                    $retval .= "<td class='signOffTD' title='t'>".$this->get_sign_off_td($editing, $advancedMode)."</td>";
                }
                else
                {
                    $retval .= "<td class='blank'></td>";
                }
                
                // IV
                $retval .= $this->get_iv_td($editing);
                
                
                $retval .= "</tr>";
                
                
            }
        }
                
		return $retval;	
    
        
    }
    
    
    
    /**
     * Get the grid TD for signoff sheets
     */
    function get_sign_off_td($editing, $advanced)
    {
        global $CFG;
        
        if($editing)
        {
            $img = ($this->are_all_sign_offs_complete()) ? "icon_SignOffSheet" : "icon_SignOffSheetIncomplete";
            $output = "<a href='#' onclick='loadSignOffSheets({$this->studentID}, {$this->id}, {$this->qualID});return false;'><img id='SIGNOFF_IMG_{$this->studentID}_{$this->id}_{$this->qualID}' src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/{$img}.png' /></a>";
        }
        else
        {
            $output = "";
            
            if($this->are_all_sign_offs_complete())
            {
                if($advanced){
                    $output .= "<span class='stuValue' style='font-weight:bold;'>A</span>";
                }
                else
                {
                    $image = CGQualification::get_simple_grid_images("A", "Achieved");
                    $output .= "<img src='{$image->image}' class='{$image->class}' />";
                }
            }
            else
            {
                
                // If any are completed, show Partially Achieved
                if($this->are_any_sign_offs_complete())
                {
                    if(!$advanced){
                        $image = CGQualification::get_simple_grid_images("PA", "Partially Achieved");
                        $output .= "<img src='{$image->image}' class='{$image->class}' />";                    }
                    else
                    {
                        $output .= "<span class='stuValue' style='font-weight:bold;'>PA</span>";
                    }
                }
                else
                {
                    // Else show N/A
                    if(!$advanced){
                        $image = CGQualification::get_simple_grid_images("N/A", "Not Attempted");
                        $output .= "<img src='{$image->image}' class='{$image->class}' />";
                    }
                    else
                    {
                        $output .= "<span class='stuValue' style='font-weight:bold;'>N/A</span>";
                    }
                }
                
            }
        }
        
        // Tooltip
        $output .= "<div class='signoffTooltip c'>";
            $output .= "<small>".fullname($this->student)." ({$this->student->username})</small><br><br>";
            
            // Loop through the sheets and just say whether completed or not, as would get too large otherwise
            $sheets = $this->load_sign_off_sheets();
            if($sheets)
            {
                foreach($sheets as $sheet)
                {
                    $comp = ($this->is_sheet_completed($sheet)) ? "Complete" : "Not Complete";
                    $output .= "<b>{$sheet->name}</b><br><i>{$comp}</i><br><br>";
                }
            }
        
        $output .= "</div>";
        
        return $output;
    }
    
    public function are_any_sign_offs_complete()
    {
        global $DB;
        
        // Loop through sheets on unit
        $sheets = $this->load_sign_off_sheets();
        if(!$sheets) return true;
        
        foreach($sheets as $sheet)
        {
            
            // Loop through ranges on sheet
            if($sheet->ranges)
            {
                foreach($sheet->ranges as $range)
                {
                    // Check for value in DB
                    $check = $DB->get_records("block_bcgt_user_soff_sht_rgs", array("userid" => $this->studentID, "bcgtqualificationid" => $this->qualID, "bcgtsignoffsheetid" => $sheet->id, "bcgtsignoffrangeid" => $range->id, "value" => 1));
                    // If no record then return false straight away, as no point going through the rest
                    if($check){
                        return true;
                    }
                }
            }
            
        }
        
        return false;
    }
    
    /**
     * Check all sign off sheets on this unit and see if there is at least 1 tick in each range of each sheet
     */
    public function are_all_sign_offs_complete()
    {
                
        global $DB;
        
        // Loop through sheets on unit
        $sheets = $this->load_sign_off_sheets();
        if(!$sheets) return true;
        
        foreach($sheets as $sheet)
        {
            
            // Loop through ranges on sheet
            if($sheet->ranges)
            {
                foreach($sheet->ranges as $range)
                {
                    // Check for value in DB
                    $check = $DB->get_records("block_bcgt_user_soff_sht_rgs", array("userid" => $this->studentID, "bcgtqualificationid" => $this->qualID, "bcgtsignoffsheetid" => $sheet->id, "bcgtsignoffrangeid" => $range->id, "value" => 1));
                    // If no record then return false straight away, as no point going through the rest
                    if(!$check){
                        return false;
                    }
                }
            }
            
        }
        
        return true;
        
    }
    
    
    
}