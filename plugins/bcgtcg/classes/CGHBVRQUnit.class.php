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

require_once 'CGHBVRQQualification.class.php';
require_once 'CGUnit.class.php';
require_once 'CGHBVRQCriteria.class.php';
require_once $CFG->dirroot . '/blocks/bcgt/classes/core/Range.class.php';


/**
 * 
 */
class CGHBVRQUnit extends CGUnit {
 
    
    const MAX_POINTS_ON_OBSERVATION = 3;
    
    /**
     * This method is on every non abstract class!
     * @param type $unitID
     * @param type $params
     * @param type $loadParams
     * @return \ALevelUnit
     */
    public static function get_instance($unitID, $params, $loadParams)
    {
        return new CGHBVRQUnit($unitID, $params, $loadParams);
    }
    
     /*
	 * Gets the associated Qualification ID
	 */
	public function get_typeID()
    {
        return CGHBVRQQualification::ID;
    }
    
    /*
	 * Gets the name of the associated qualification. 
	 */
	public function get_type_name()
    {
        return CGHBVRQQualification::NAME;
    }
    
    /*
	 * Gets the name of the associated qualification family. 
	 */
	public function get_family_name()
    {
        return CGHBVRQQualification::NAME;
    }
	
	/**
	 * Get the family of the qual.
	 */
	public function get_familyID()
    {
        return CGHBVRQQualification::FAMILYID;
    }
    
    
    
    /**
	 * Used in edit unit
	 * Gets the submitted data from the criteria section of the edit form form.
	 * edit_unit_form.php
	 */
	public function get_submitted_criteria_edit_form_data()
    {
              
        // Create an array of id => bool for the current criteria on this unit
        // Then after we have dealt with all the data submitted, we can see which ones are still false and remove them
        $arrayOfCurrentTasks = array();
        if ($this->criterias)
        {
            foreach($this->criterias as $criteria)
            {
                $arrayOfCurrentTasks[$criteria->get_id()] = false;
            }
        }
        
        $tmpCriteria = array();
        
        if (isset($_POST['taskNames']))
        {
        
            // Overall tasks
            $taskIDs = $_POST['taskIDs'];
            $taskNames = $_POST['taskNames'];
            $taskDetails = $_POST['taskDetails'];
            $taskTargetDates = $_POST['taskTargetDates'];
            $taskOrders = $_POST['taskOrders'];
            $taskTypes = $_POST['taskTypes'];

            // Task Criteria
            $criteriaIDs = $_POST['taskCritIDs'];
            $criteriaNames = $_POST['taskCritNames'];

            // Task Observations
            $observationIDs = $_POST['taskObservationIDs'];
            $observationNames = $_POST['taskObservationNames'];
            $observationCC = $_POST['taskObservationCC'];
            $observationTargetDates = $_POST['taskObservationTargetDates'];

            // Observation/Criteria links
            $observationCriteriaPoints = $_POST['taskCriteriaObservationPoints'];
            
            // Formatives
            $formativeCriteriaIDs = @$_POST['formativeCriteriaIDs'];
            $formativeCriteriaNames = @$_POST['formativeCriteriaNames'];
            $formativeCriteriaDescs = @$_POST['formativeCriteriaDescs'];
            
            // Make sure we have at least one name set, otherwise there's no point doing anything
            $tempNames = array_filter($taskNames, function($e){
                $e = trim($e);
                return (!empty($e));
            });
            
            if (empty($tempNames)){
                return;
            }
                        
            // Since the script doesn't actually create the unit until after all this, any new units will
            // have their ranges "unitid" set to -1, since the unit doesn't exist yet to give it a real id
            // So now we have to create am array of range objects which will need checking once the unit is created
            $listOfObservationsToUpdate = array(); 
            
            
            // Loop through the tasks DYNAMICID => ID
            foreach($taskIDs as $DID => $ID)
            {
                
                // If the name is empty, skip it
                if (empty($taskNames[$DID])) continue;
                
                // If no task exists on this unit with this id, add one
                if (!isset($this->criterias[$ID]))
                {
                    
                    $params = new stdClass();
                    $params->name = $taskNames[$DID];
                    $params->details = $taskDetails[$DID];
                    $params->ordernum = $taskOrders[$DID];
                    $params->type = (isset($taskTypes[$DID])) ? $taskTypes[$DID] : 'Summative';
                    $obj = new CGHBVRQCriteria(-1, $params, Qualification::LOADLEVELCRITERIA);
                    
                    // Target date
                    $targetDate = strtotime($taskTargetDates[$DID]);
                    $obj->set_target_date($targetDate);
                    
                    // Observations
                    if (isset($observationIDs[$DID]))
                    {
                        
                        // Loop through each observation
                        foreach($observationIDs[$DID] as $ODID => $OID)
                        {
                            
                            // These observations are technically "Ranges" so will be stored in a Range object
                            $rangeParams = array(
                                "id" => $OID,
                                "name" => $observationNames[$DID][$ODID],
                                "unitid" => $this->id,
                                "targetdate" => strtotime($observationTargetDates[$DID][$ODID]),
                                "chart" => $observationCC[$DID][$ODID]
                            );
                            
                            $range = new Range($rangeParams);
                            $range->save();
                            
                            // Set the real ID
                            $observationIDs[$DID][$ODID] = $range->id;
                            
                            // Put in array
                            $listOfObservationsToUpdate[] = $range;
                            
                        }
                        
                    }
                    
                    // Criteria
                    if (isset($criteriaIDs[$DID]))
                    {
                        
                        // Loop criteria
                        foreach($criteriaIDs[$DID] as $CDID => $CID)
                        {
                            
                            // For these we will just assign them as sub criteria on the task
                            // with the `type` "Range" just to make it clear what they are
                            $params = new stdClass();
                            $params->name = $criteriaNames[$DID][$CDID];
                            $params->details = '';
                            $params->ordernum = 0;
                            $params->type = 'Range';
                            $subObj = new CGHBVRQCriteria(-1, $params, Qualification::LOADLEVELCRITERIA);
                            
                            // Are there any links to observations?
                            if (isset($observationCriteriaPoints[$DID]))
                            {
                                
                                // Loop them
                                foreach($observationCriteriaPoints[$DID] as $LINK => $POINTS)
                                {
                                    
                                    $explode = explode("|", $LINK);
                                    $C = substr($explode[0], 1);
                                    $O = substr($explode[1], 1); // get rid of "O" and just have the dynamic id
                                    if($C == $CDID){
                                        // Add link to range
                                        $actualObservationID = $observationIDs[$DID][$O];
                                        $subObj->add_range_link($actualObservationID, $POINTS);
                                    }
                                    
                                }
                                
                            }
                            
                            // Add sub criteria to overall criteria (task)
                            $obj->add_sub_criteria($subObj);
                            
                        }
                        
                    }
                    
                    
                    
                    // Any Formatives?
                    if (isset($formativeCriteriaIDs[$DID])){
                    
                        // Loop
                        foreach($formativeCriteriaIDs[$DID] as $FDID => $FID){
                            
                            // Create criteria object
                            $params = new stdClass();
                            $params->name = $formativeCriteriaNames[$DID][$FDID];
                            $params->details = $formativeCriteriaDescs[$DID][$FDID];
                            $params->ordernum = 0;
                            $subObj = new CGHBVRQCriteria(-1, $params, Qualification::LOADLEVELCRITERIA);
                            
                            // Add sub criteria to overall criteria (task)
                            $obj->add_sub_criteria($subObj);
                            
                        }
                        
                    }
                    
                    
                    
                    
                    $tmpCriteria[] = $obj;
                    
                }
                
                // It's already on this unit, so let's just update the object
                else
                {
                    
                    $params = new stdClass();
                    $params->name = $taskNames[$DID];
                    $params->details = $taskDetails[$DID];
                    $params->ordernum = $taskOrders[$DID];
                    $params->type = (isset($taskTypes[$DID])) ? $taskTypes[$DID] : 'Summative';
                    $obj = new CGHBVRQCriteria($ID, $params, Qualification::LOADLEVELCRITERIA);
                    
                    // Clear sub criteria array so we can delete any we haven't submitted
                    $obj->set_sub_criteria( array() );
                    
                    $arrayOfCurrentTasks[$ID] = true;
                    
                    // Target date
                    $targetDate = strtotime($taskTargetDates[$DID]);
                    $obj->set_target_date($targetDate);
                    
                    // Observations
                    if (isset($observationIDs[$DID]))
                    {
                        
                        // Loop through them
                        foreach($observationIDs[$DID] as $ODID => $OID)
                        {
                            
                            // if it doesn't exist, create it
                            if (!Range::exists($OID))
                            {
                                
                                // These observations are technically "Ranges" so will be stored in a Range object
                                $rangeParams = array(
                                    "id" => $OID,
                                    "name" => $observationNames[$DID][$ODID],
                                    "unitid" => $this->id,
                                    "targetdate" => strtotime($observationTargetDates[$DID][$ODID]),
                                    "chart" => $observationCC[$DID][$ODID]
                                );
                                                                
                                $range = new Range($rangeParams);
                                $range->save();
                                
                                // Update id of range in posted variables to the new one
                                $observationIDs[$DID][$ODID] = $range->id;
                                
                            }
                            else
                            {
                                
                                // Update it
                                $range = new Range($OID);
                                $range->set("name", $observationNames[$DID][$ODID]);
                                $range->set("chart", $observationCC[$DID][$ODID]);
                                $range->set("targetdate", strtotime($observationTargetDates[$DID][$ODID]));
                                $range->save();
                                
                            }
                            
                        }
                        
                    }
                    
                    
                    // Criteria
                    if (isset($criteriaIDs[$DID]))
                    {
                        
                        // Loop through them
                        foreach($criteriaIDs[$DID] as $CDID => $CID)
                        {
                            
                            
                            // For these we will just assign them as sub criteria on the task
                            // with the `type` "Range" just to make it clear what they are
                            $params = new stdClass();
                            $params->name = $criteriaNames[$DID][$CDID];
                            $params->details = '';
                            $params->ordernum = 0;
                            $params->type = 'Range';
                            $subObj = new CGHBVRQCriteria($criteriaIDs[$DID][$CDID], $params, Qualification::LOADLEVELCRITERIA);
                                                        
                            // Are there any links to observations?
                            if (isset($observationCriteriaPoints[$DID]))
                            {
                                
                                // Loop them
                                foreach($observationCriteriaPoints[$DID] as $LINK => $POINTS)
                                {
                                    
                                    $explode = explode("|", $LINK);
                                    $C = substr($explode[0], 1);
                                    $O = substr($explode[1], 1); // get rid of "O" and just have the dynamic id
                                    if($C == $CDID){
                                        // Add link to range
                                        $actualObservationID = $observationIDs[$DID][$O];
                                        $subObj->add_range_link($actualObservationID, $POINTS);
                                    }
                                    
                                }
                             
                            }
                            
                            // If the sub criteria doesn't exist, add it, otherwise update it
                            if (!$subObj->exists())
                            {
                                $obj->add_sub_criteria($subObj);
                            }
                            else
                            {
                                $sub = $obj->get_sub_criteria();
                                $currentSubCriteria =& $sub;
                                $currentSubCriteria[$CID] = $subObj;
                                $obj->set_sub_criteria($currentSubCriteria);  
                            }
                            
                            
                            
                        }
                        
                    }
                    
                                        
                    // Any Formatives?
                    if (isset($formativeCriteriaIDs[$DID])){
                    
                        // Loop
                        foreach($formativeCriteriaIDs[$DID] as $FDID => $FID){
                            
                            // Create criteria object
                            $params = new stdClass();
                            $params->name = $formativeCriteriaNames[$DID][$FDID];
                            $params->details = $formativeCriteriaDescs[$DID][$FDID];
                            $params->ordernum = 0;
                            $subObj = new CGHBVRQCriteria($FID, $params, Qualification::LOADLEVELCRITERIA);
                                                        
                            
                            // If the sub criteria doesn't exist, add it, otherwise update it
                            if (!$subObj->exists())
                            {
                                $obj->add_sub_criteria($subObj);
                            }
                            else
                            {
                                                                
                                $sub = $obj->get_sub_criteria();
                                $currentSubCriteria =& $sub;
                                $currentSubCriteria[$FID] = $subObj;
                                $obj->set_sub_criteria($currentSubCriteria);  
                                
                            }
                            
                            
                        }
                        
                    }
                    
                    $tmpCriteria[$ID] = $obj;
                    
                }
                
            }

            $this->listOfObservationsToUpdate = $listOfObservationsToUpdate;
        
        }

        $this->criterias = $tmpCriteria;
                 
    }
    
    
    public function insert_unit($trackingTypeID = CGHBVRQQualification::ID) {
        parent::insert_unit($trackingTypeID);
        
        // We now need to go through all the ranges we just created and update them to give them this unit id
        if($this->listOfObservationsToUpdate)
        {
            foreach($this->listOfObservationsToUpdate as $range)
            {
                $range->set("unitid", $this->id);
                $range->save();
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
		return "<br><br>".get_string('tasks', 'block_bcgt');
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
        
            $retval .= "<script> var numOfTasks = 0; var dynamicNumOfTasks = 0; var overallNumCRCriteria = 0; var overallNumCRObservation = 0; var arrayOfCRCriteria = new Array(); var arrayOfCRObservation = new Array(); var numOfFormativeCriteria = 0;</script>";

            $retval .= "<a href='#' id='addNewHBVRQTask'>".get_string('addtask', 'block_bcgt')."</a><br><br>";

            $retval .= "<table id='criteriaHolder' class='cgCriteriaHolderTable cgHBVRQTaskHolderTable'>";

               $retval .=  $this->build_criteria_form();

            $retval .= "</table>";
            $retval .= "<br><br>";
        
        }
        else
        {
            $retval .= "<p>".get_string('criterianotavailableuntilformcomplete', 'block_bcgt')."</p>";
        }
                        
        return $retval;
        
    }
    
    
    protected function build_criteria_form(){
     
        global $CFG;
        
        $retval = "";
                
        $retval .= "<tr>";
            $retval .= "<th>".get_string('name', 'block_bcgt')."</th>";
            $retval .= "<th>".get_string('type', 'block_bcgt')."</th>";
            $retval .= "<th>".get_string('details', 'block_bcgt')."</th>";
            $retval .= "<th>".get_string('targetdate', 'block_bcgt')."</th>";
            $retval .= "<th>".get_string('order', 'block_bcgt')."</th>";
            $retval .= "<th></th>";
        $retval .= "</tr>";


        $d = 0; # Dynamic number of tasks
        $c = 0; # Dynamic number of criteria
        $r = 0; # Dynamic number of observations (ranges)
        $f = 0; # Dynamic number of formative sub criteria
        
        // Find criteria (tasks) and loop through them
        if ($this->criterias)
        {
            foreach($this->criterias as $criterion)
            {
                
                $d++;
                
                $retval .= '<tr class="taskRow_'.$d.'">';
                    $retval .= '<td>';
                    $retval .= '<script> arrayOfCRCriteria['.$d.'] = new Array();arrayOfCRObservation['.$d.'] = new Array(); numOfTasks++; dynamicNumOfTasks++; </script>';
                    $retval .= '<input type="hidden" name="taskIDs['.$d.']" value="'.$criterion->get_id().'" /><input type="text" placeholder="Name" name="taskNames['.$d.']" value="'.bcgt_html($criterion->get_name()).'" class="critNameInput" id="taskName_'.$d.'" />';
                    $retval .= '</td>';
                    $retval .= '<td><select onchange="changeCriterionTypeVRQ(this.value, '.$d.');return false;" name="taskTypes['.$d.']"><option value="Summative" '.( ($criterion->get_type() == 'Summative') ? 'selected' : '' ).'>'.get_string('summative', 'block_bcgt').'</option><option value="Formative" '.( ($criterion->get_type() == 'Formative') ? 'selected' : '' ).'>'.get_string('formative', 'block_bcgt').'</option></select></td>';
                    $retval .= '<td><textarea style="width:100%;" placeholder="Task Details" name="taskDetails['.$d.']" id="taskDetails'.$d.'" class="critDetailsTextArea">'.bcgt_html( $criterion->get_details() ).'</textarea></td>';
                    $retval .= '<td><input type="text" readonly="true" name="taskTargetDates['.$d.']" value="'.$criterion->get_target_date().'" class="bcgtDatePicker" /> </td>';
                    $retval .= '<td><input type="text" class="w40" name="taskOrders['.$d.']" value="'.$criterion->get_order().'" /></td>';
                    $retval .= '<td><a href="#" onclick="removeTaskTable('.$d.');return false;"><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/redX.png" /></a></td>';
                $retval .= '</tr>';
                
                
                // Range/Criteria row (observations)
                $retval .= "<tr class='taskRow_{$d}'>";
                    $retval .= '<td colspan="6">';

                
                    if ($criterion->get_type() == 'Formative')
                    {

                        $retval .= '<table id="Task_'.$d.'_FormativeTable" class="criteriaObservationTable">';
                        
                            $retval .= '<tr>';
                                $retval .= '<th><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" title="Add new formative criteria" alt="Add new formative criteria" onclick="addHBVRQFormativeCriteria('.$d.');" /></th>';
                                $retval .= '<th>Name</th>';
                                $retval .= '<th>Description</th>';
                                $retval .= '<th></th>';
                            $retval .= '</tr>';
                            
                            if ($criterion->get_sub_criteria())
                            {
                                foreach($criterion->get_sub_criteria() as $subCriterion)
                                {

                                    
                                    $retval .= '<tr id="formativeCriteriaRow_'.$d.'_'.$f.'">';
                                        $retval .= '<td></td>';
                                        $retval .= '<td><input type="hidden" name="formativeCriteriaIDs['.$d.']['.$f.']" value="'.$subCriterion->get_id().'" /><input type="text" name="formativeCriteriaNames['.$d.']['.$f.']" placeholder="Name" value="'.$subCriterion->get_name().'" /></td>';
                                        $retval .= '<td><input type="text" name="formativeCriteriaDescs['.$d.']['.$f.']" placeholder="Details" class="long" value="'.$subCriterion->get_details().'" /></td>';
                                        $retval .= '<td><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/redX.png" title="Remove formative criteria" alt="Remove formative criteria" onclick="removeHBVRQFormativeCriteria('.$d.', '.$f.');" /><script> numOfFormativeCriteria++; </script></td>';

                                    $retval .= '</tr>';
                                    
                                    $f++;

                                }


                            }
                            
                            
                        
                        $retval .= '</table>';

                    }
                    else
                    {

                            $retval .= '<table id="Task_'.$d.'_ObservationsTable" class="criteriaObservationTable">';

                                $retval .= '<tr id="buttonRow_'.$d.'">';
                                    $retval .= '<td></td>';
                                    $retval .= '<td><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" title="Add new criteria" alt="Add new criteria" onclick="addNewHBVRQCriteria('.$d.');" /></td>';
                                    $retval .= '<td><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" title="Add new observation" alt="Add new observation" onclick="addNewHBVRQObservation('.$d.');" /></td>';

                                    // Find any observations (ranges) on this task and loop through to add delete links
                                    $ranges = $criterion->get_all_possible_ranges();
                                    $r = 0;
                                    if ($ranges)
                                    {
                                        foreach($ranges as $range)
                                        {

                                            $r++;
                                            $retval .= '<td class="c noBorder Ob'.$r.'"><a href="#" onclick="deleteHBRVQObservation('.$d.', '.$r.');return false;"><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/close.png" class="small" /></a></td>';
                                            $retval .= '<script>overallNumCRObservation++;arrayOfCRObservation['.$d.'].push(overallNumCRObservation);</script>';

                                        }
                                    }


                                $retval .= '</tr>';

                                $retval .= '<tr id="observationRow_'.$d.'">';
                                    $retval .= '<td></td>';
                                    $retval .= '<td>Criteria</td>';
                                    $retval .= '<td>Observations</td>';

                                    // Find any observations (ranges) on this task and loop through to put their name in
                                    $ranges = $criterion->get_all_possible_ranges();
                                    $r = 0;
                                    if ($ranges)
                                    {
                                        foreach($ranges as $range)
                                        {

                                            $r++;
                                            $retval .= '<td class="Ob'.$r.'"><input type="hidden" name="taskObservationIDs['.$d.']['.$r.']" value="'.$range->id.'" /><input type="text" name="taskObservationNames['.$d.']['.$r.']" value="'.$range->name.'" class="rangeInput hoverTitle" onkeyup="reloadHoverTitles();" /></td>';

                                        }
                                    }


                                $retval .= '</tr>';

                                $retval .= '<tr id="conversionChartRow_'.$d.'">';
                                    $retval .= '<td></td>';
                                    $retval .= '<td></td>';
                                    $retval .= '<td>Conversion Chart</td>';

                                    // Find any observations (ranges) on this task and loop through to put conversion chart in
                                    $ranges = $criterion->get_all_possible_ranges();
                                    $r = 0;
                                    if ($ranges)
                                    {
                                        foreach($ranges as $range)
                                        {

                                            $r++;
                                            $retval .= '<td class="c Ob'.$r.'"><table class="smalltext all_c"><tr class="b"><td>Grade</td><td title="Minimum marks required for this grade">Marks</td></tr><tr><td>Pass</td><td><input id="observationCC_P_'.$r.'" type="text" class="tinyInput" name="taskObservationCC['.$d.']['.$r.'][P]" onblur="checkCCNum(this);" value="'.$range->chart['P'].'" /></td></tr><tr><td>Merit</td><td><input id="observationCC_M_'.$r.'" type="text" class="tinyInput" name="taskObservationCC['.$d.']['.$r.'][M]" onblur="checkCCNum(this);" value="'.$range->chart['M'].'" /></td></tr><tr><td>Distinction</td><td><input id="observationCC_D_'.$r.'" type="text" class="tinyInput" name="taskObservationCC['.$d.']['.$r.'][D]" onblur="checkCCNum(this);" value="'.$range->chart['D'].'" /></td></tr></table><small class="output" style="color:red;"></small><br><small>Target Date:</small><br><input type="text" name="taskObservationTargetDates['.$d.']['.$r.']" value="'.$range->get_target_date(true).'" class="bcgtDatePicker" /></td>';

                                        }
                                    }


                                $retval .= '</tr>';


                                // Now the criteria (task sub criteria)
                                if ($criterion->get_sub_criteria())
                                {
                                    foreach($criterion->get_sub_criteria() as $subCriterion)
                                    {

                                        $c++;
                                        $retval .= '<tr id="taskCriteriaRow_'.$d.'_'.$c.'">';
                                            $retval .= '<td class="blank_cell_left small_cell"><a href="#" onclick="deleteHBVRQCriteria('.$d.', '.$c.');return false;"><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/close.png" class="small" /></a></td>';
                                            $retval .= '<td><input type="hidden" name="taskCritIDs['.$d.']['.$c.']" value="'.$subCriterion->get_id().'" /><input type="text" name="taskCritNames['.$d.']['.$c.']" value="'.$subCriterion->get_name().'" title="" class="observationCritInput hoverTitle" onkeyup="reloadHoverTitles();" /></td>';
                                            $retval .= '<td> <script>overallNumCRCriteria++;arrayOfCRCriteria['.$d.'].push(overallNumCRCriteria);</script> </td>';

                                            // Loop through observations again to add in the points links between them
                                            $ranges = $criterion->get_all_possible_ranges();

                                            $r = 0;
                                            if ($ranges)
                                            {
                                                foreach($ranges as $range)
                                                {

                                                    $r++;
                                                    $points = (array_key_exists($subCriterion->get_id(), $range->links)) ? $range->links[$subCriterion->get_id()] : 0;
                                                    $retval .= '<td class="C'.$c.' Ob'.$r.' c">';
                                                        $retval .= '<select class="tinySelect" name="taskCriteriaObservationPoints['.$d.'][C'.$c.'|O'.$r.']" title="Please select the maximum number of points the student can achieve for this criteria on this range, between 0-'.self::MAX_POINTS_ON_OBSERVATION.'">';
                                                            for($p = 0; $p <= self::MAX_POINTS_ON_OBSERVATION; $p++)
                                                            {
                                                                $selected = ($points == $p) ? "selected" : "";
                                                                $retval .= '<option value="'.$p.'" '.$selected.'>'.$p.'</option>';
                                                            }
                                                        $retval .= '</select>';
                                                    $retval .= '</td>';

                                                }
                                            }

                                        $retval .= '</tr>';

                                    }
                                }


                            $retval .= '</table>';

                            $retval .= "<script> $('.bcgtDatePicker').datepicker( { dateFormat: 'dd-mm-yy' } ); </script>";

                    }
                
                    $retval .= '</td>';
                $retval .= "</tr>";
                                
            }
        }
        
        return $retval;
        
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
			//loop over each criteria and create a header
			//have a spacer between P, M and D
			foreach($criteriaNames AS $criteriaName)
			{
                
                $criteria = $this->find_criteria_by_name($criteriaName);
                if (!$criteria) continue;
                
                if (!$criteria->get_sub_criteria()){
                    $criteria->load_sub_criteria();
                }
                
                $ranges = $criteria->get_all_possible_ranges();
                                
                $max = 1;
                
                $cnt = count($ranges);
                if ($cnt > $max) $max = $cnt;
                
                $tName = str_replace(" ", "_", htmlentities($criteriaName, ENT_QUOTES));
                if($max > 1 && !$printGrid) $header .= "<th class='toggleTD_{$tName}' defaultcolspan='{$max}' colspan='{$max}'><a href='#' onclick='toggleOverallTasks(\"{$tName}\");return false;'>{$criteriaName}</a></th>";
                else $header .= "<th colspan='{$max}'>{$criteriaName}</th>";
            
                $totalHeaderCount++;

            }
        }
        
        // IV
        $header .= "<th>IV</th>";
        
		$header .= "</tr></thead>";
		
		$headerObj->header = $header;
		$headerObj->criteriaCountArray = $criteriaCountArray;
		//$headerObj->orderedCriteriaNames = $criteriaNames;
        $headerObj->totalHeaderCount = $totalHeaderCount;
                
		return $headerObj;
	}
    
    
    /**
     * Get the range header for each unit on the grid
     * @global type $printGrid
     * @param type $taskNames
     * @param type $qual
     * @param type $editing
     * @param type $studentView
     * @return string
     */
    public function get_unit_header($taskNames, $qual, $editing, $studentView, $grid)
    {
        
        global $printGrid;
                
        $output = "";
                
        $output .= "<tr class='unitHeader'>";
            $output .= "<th></th>";
            $output .= "<th></th>";
            
             if ($grid == 'unit'){
                $output .= "<th></th>"; #xtra col for username
                $output .= "<th></th>"; #xtra col for name
            }
            
            
            // LOoking @ student will have 2 extra's - award & % completion
            if($studentView){
                $output .= "<th></th>"; #unit award
                $output .= "<th></th>"; #& complete
            }
            
           
            
            #if($printGrid) $output[] = "<th></th>";
            
            
            // Loop through task names and see if there are any observations for it on this unit
            if($taskNames)
            {
                foreach($taskNames as $taskName)
                {
                    
                    $colspan = $qual->max_all_ranges_of_task_name($taskName);
                    $task = $this->find_criteria_by_name($taskName);
                    $tName = str_replace(" ", "_", htmlentities($taskName, ENT_QUOTES));
                    
                    if(!$task){
                        for($i = 0; $i < $colspan; $i++)
                        {
                            $output .= "<th class='taskClass_{$tName}'></th>";
                        }
                        // Overall one
                        $output .= "<th class='taskHidden_{$tName}' style='display:none;'></th>";
                        continue;
                    }
                    
                    $ranges = $task->get_all_possible_ranges();
                    
                    if(!$ranges)
                    {
                        for($i = 0; $i < $colspan; $i++)
                        {
                            $output .= "<th></th>";
                        }
                        
                        continue;
                        
                    }
                                        
                    $num = 0;
                    
                    // Hidden overall task cell
                    $output .= "<th class='taskHidden_{$tName}' style='display:none;' /></th>";
                    
                    foreach($ranges as $range)
                    {
                        
                        $range->load_student_information($this->studentID, $qual->get_id());
                                                                        
                        $tooltip = $range->build_name_tooltip();
                                                
                        if($editing)
                        {
                            if (!$this->studentID){
                                $this->studentID = -1;
                            }
                            $output .= "<th class='rangeTitle taskClass_{$tName}'><a href='#' class='observationName' onclick='loadObservationPopup({$range->id}, {$this->studentID}, {$qual->get_id()}, \"{$grid}\");return false;'>{$range->name}</a>{$tooltip}</th>";
                        }
                        else
                        {
                            $output .= "<th class='rangeTitle taskClass_{$tName}'><span class='observationName'>{$range->name}</span>{$tooltip}</th>";
                        }
                        $num++;
                    }
                    
                    
                    // If number printed is less than the max colspan, print some more
                    if($num < $colspan)
                    {
                        for($i = $num; $i < $colspan; $i++)
                        {
                            $output .= "<th class='taskClass_{$tName}'></th>";
                        }
                    }
                    
                }
                
            }
            
            if(!$printGrid){
                $output .= "<th class='ivTD'></th>";
            }
        
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
    
    function calculate_unit_award($qualID, $update = true){
        
        global $DB;
                
        // Just need to work out the avg score of the unit, then consult the boundary scores in block_bcgt_type_award
        
        // For this type of qualification, we are only calculating using the practical tasks (ranges/criteria).
        // The standard tasks still need to be passed, but they are not used in the calculations
        
        $tasksRequired = 0;
        $tasksPassed = 0;
        $practicalRequired = 0;
        $practicalPassed = 0;
        
        $awardArray = array();
                
        // Loop criteria on unit
        if($this->criterias)
        {
            
            foreach($this->criterias as $criteria)
            {
            
                
                $sID = $criteria->get_student_ID();
                if (is_null($sID)){
                    $criteria->load_student_information($this->studentID, $this->qualID, $this->id);
                }
                
                // Do standard tasks first
                if(!$criteria->get_sub_criteria())
                {
                    
                    $tasksRequired++;
                    
                    // Get the student's award value for this criteria
                    $valueObj = $criteria->get_student_value();
                    
                    if(!$valueObj) continue;
                    
                    // If weighting is 0, don't bother adding
                    if($valueObj->is_criteria_met_bool()){
                        $tasksPassed++;
                    } else {
                        $tasksRequired--;
                    }
                    
                }
                else
                {
                    
                    // Tasks with ranges/criteria
                    $ranges = $criteria->get_all_possible_ranges();
                    if(!$ranges) continue;                    
                    
                    $practicalRequired += count($ranges);
                    
                    // Loop through ranges and see if they have an award
                    foreach($ranges as $range)
                    {
                        $range->load_student_information($this->studentID, $qualID);
                        if(!$range->gradeID) continue;
                        
                        $value = new Value($range->gradeID);
                        if(!$value) continue;
                        
                        if($value->is_criteria_met_bool()){
                            $practicalPassed++;
                            $ranking = $DB->get_record("block_bcgt_value", array("id" => $value->get_id()), "id, ranking");
                            $awardArray[] = array("value" => $value->get_short_value(), "weighting" => 1.0, "ranking" => $ranking->ranking);
                        }
                    }
                    
                }
                
                
            }
            
        }
                    
        // If have passed everything
        if($tasksRequired == $tasksPassed && $practicalRequired == $practicalPassed)
        {

            $awardRanking = $this->calculate_average_score($awardArray);
            if($awardRanking)
            {

                // Update the unit award
                $awardRecord = $this->get_unit_award($awardRanking);
                if ($awardRecord)
                {
                    $params = new stdClass();
                    $params->award = $awardRecord->award;
                    $params->rank = $awardRecord->ranking;
                    $award = new Award($awardRecord->id, $params);
                    if ($update){
                        $this->userAward = $award;
                        $this->update_unit_award($qualID);
                    }

                    return $award;
                }

            }

        }

        // If we get to this point, either the amount passed wasn't equal, or there was a problem with the award
        // So set it back to N/S
        if ($update){
            $this->userAward = new Award(-1, 'N/S', 0);
            $this->update_unit_award($qualID);
        }
                
        return $this->userAward;         
                    
        
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
            if(!$criterion->get_sub_criteria() || $criterion->get_type() == 'Formative')
            {
                // Increment cnt
                $count++;
            }
            else
            {
                // Has sub criteria/ranges
                $count += count($criterion->get_all_possible_ranges());
            }
            
            
        }
                        
        $numCompleted = $this->are_criteria_completed($criteria);

        $percent = round(($numCompleted * 100) / $count);                
        return $percent;
            
    }
    
    protected function are_criteria_completed($criteria, $sub = false, $tasks = false)
    {
            
            if(!$criteria) return 0;
            
            $numCompleted = 0;
                        
            foreach($criteria as $criterion)
            {
                
                $sID = $criterion->get_student_ID();
                $criterion->load_student_information($this->studentID, $this->qualID, $this->id);
                
                // Standard task
                if(!$criterion->get_sub_criteria() || $criterion->get_type() == 'Formative')
                {
                   
                    $award = $criterion->get_student_value();
                    if($award)
                    {
                        if($award->is_criteria_met_bool())
                        {
                            $numCompleted++;
                        }
                    }
                                        
                }
                else
                {
                    
                    // Has ranges & criteria
                    $ranges = $criterion->get_all_possible_ranges();
                    if(!$ranges) continue;

                    foreach($ranges as $range)
                    {
                        $range->load_student_information($this->studentID, $this->qualID);
                        if(!$range->gradeID) continue;
                        $value = new Value($range->gradeID);
                        if(!$value) continue;
                        if($value->is_criteria_met_bool())
                        {
                            $numCompleted++;
                        }
                    }
                                                                
                }
                                                
            }
            
            return $numCompleted;
            
    }
    
    
    
    
    
    function build_unit_details_table(){
        
        $output = "";
                
            $output .= "<p class='c'><b style='font-size:9pt;'>{$this->name}</b></p>";
            $output .= "<table style='font-size:8pt;' class='c'>";
                $output .= "<tr><th>Task Name</th><th>Task Details</th></tr>";
                
                if($criteriaList = $this->criterias)
                {
                    foreach($criteriaList as $task)
                    {
                        $output .= "<tr><td>{$task->get_name()}</td><td>{$task->get_details()}</td></tr>";
                        
                        // If the task has sub criteria, list them as well, with all the ranges available
                        if($subCriteria = $task->get_sub_criteria())
                        {
                            
                            $output .= "<tr><td colspan='2'>";
                            
                                $output .= "<table class='l unittooltip'>";
                                    
                                    // First row should list all the ranges available on this task
                                    $ranges = $task->get_all_possible_ranges();
                                    if($ranges)
                                    {
                                        $output .= "<tr>";
                                        $output .= "<td class='bg'></td>";
                                            foreach($ranges as $range)
                                            {
                                                $output .= "<td class='bg'>{$range->name}</td>";
                                            }
                                        $output .= "</tr>";
                                        
                                        // Now the sub criteria
                                        foreach($subCriteria as $sub)
                                        {
                                            $output .= "<tr>";
                                                $output .= "<td class='bg'>{$sub->get_name()}</td>";
                                                
                                                foreach($ranges as $range)
                                                {
                                                    // Check the link between the range & the criteria
                                                    if(isset($range->links[$sub->get_id()]) && $range->links[$sub->get_id()] > 0)
                                                    {
                                                        $max = $range->links[$sub->get_id()];
                                                        $output .= "<td>";
                                                        for($i = 1; $i <= $max; $i++)
                                                        {
                                                            $output .= "&nbsp;&nbsp;&nbsp;{$i}&nbsp;&nbsp;&nbsp;";
                                                        }
                                                        $output .= "</td>";
                                                    }
                                                    else
                                                    {
                                                        $output .= "<td class='grid_cell_blank'>N/A</td>";
                                                    }
                                                }
                                                
                                            $output .= "</tr>";
                                        }
                                        
                                    }
                                
                                $output .= "</table>";
                            
                            $output .= "</td></tr>";
                            
                        }
                        
                    }
                    
                }
                
            $output .= "</table>";
                
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
                             
                // FIrst we'll do the standard tasks
                if(!$task->get_sub_criteria())
                {

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
                else
                {
                    // Now the ones with sub criteria & ranges
                    
                    // Get possible ranges
                    $ranges = $task->get_all_possible_ranges();
                    if(!$ranges) continue;
                                        
                    foreach($ranges as $range)
                    {                    
                        
                        $range->load_student_information($this->studentID, $this->qualID);
                        if(!$range->targetdate) continue;
                        
                        $award = false;
                        if($range->gradeID) $award = new Value($range->gradeID);
                        
                                               
                        if( ( ($award && $award->get_short_value() == "NA")  || !$award) && $now > $range->targetdate )
                        {
                            $record = $DB->get_record("block_bcgt_value", array("bcgttypeid" => $task->get_tracking_type(), "shortvalue" => "PTD"));
                            if($record){
                                $range->update_student_award_auto($record->id);
                            }
                           
                        }
                        
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
        $retval .= CGHBVRQQualification::get_grid_key();
		$retval .= "</div>";
        
        $retval .= "<br style='clear:both;' /><br>";
        
        //the grid -> ajax
        $retval .= '<div id="cgUnitGrid">';
        
        
        $retval .= "<div id='unitGridDiv' class='unitGridDiv ".
        $grid."UnitGrid tableDiv'><table align='center' class='unit_grid CGHB ".
                $grid."FixedTables' id='CGUnitGrid'>";
        $criteriaNames = $this->get_used_criteria_names_();
		
               
		$headerObj = $this->get_unit_grid_header($criteriaNames, $grid, $context);
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
        $possibleValues = null;
        $unitAwards = null;
        if($editing)
        {
            $unitAwards = Unit::get_possible_unit_awards($this->get_typeID());
        }
        
        if($editing && $advancedMode)
        {
            $possibleValues = CGQualification::get_possible_values(CGQualification::ID);
        }
        
        $qualification = Qualification::get_qualification_class_id($qualID);
        
        // First the unit header
        $retval .= $this->get_unit_header($criteriaNames, $qualification, $editing, true, "unit");
        
        
        
        //load the students that are on this unit for this qual. 
        $studentsArray = get_users_on_unit_qual($this->id, $qualID);
        
        if ($studentsArray)
        {
            
            $rowCount = 0;
            
            foreach($studentsArray as $student)
            {
                
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELALL;
                $loadParams->loadAward = true;
                
                $qualification->load_student_information($student->id, $loadParams);
                
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
                    $retval .= "<td>".$qualification->edit_unit_award($this, $rank, $award, $unitAwards)."</td>";
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
                         $colspan = $qualification->get_max_sub_criteria_of_criteria($criteriaName);
                         $studentCriteria = $this->get_single_criteria(-1, $criteriaName);
                         if ($studentCriteria)
                         {
                            $retval .= $studentCriteria->get_grid_td_($editing, $advancedMode, $this, $student, $qualification, 'unit', $colspan);
                         }
                         else
                         {
                             $retval .= "<td>-</td>";
                         }
                        
                    }
                    
                }
                
                // IV
                $retval .= $this->get_iv_td($editing);
                
                
                $retval .= "</tr>";
                
                
            }
        }
                
		return $retval;	
    }
    
    
    
}