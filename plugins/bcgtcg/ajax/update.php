<?php

$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime; 

header("Content-Type: text/html; charset=utf-8");
require_once '../../../../../config.php';
require_once('../../../lib.php');
require_once '../lib.php';
bcgt_start_timing();
require_cg();
require_login();

/**
 * Parameters:
 * $action - e.g. 'update_comments', 'update_task', etc... to define what is happening
 * $params - holds the given parameters for that specific action in an array
 */
$action = $_POST['action'];
$params = $_POST['params'];

if (isset($params['grid']) && ctype_digit($params['grid'])){
    $params['grid'] = ($params['grid'] == 2) ? 'unit' : 'student';
}

if (!isset($params['grid'])) $params['grid'] = 'student';

// If no action or params are set, exit the script
if(is_null($action)) exit;

// Function to check if all required parameters are set
// Then convert array to object, for ease of use
function _check($req, &$params){
    if( count($req) > count($params) ) _error('count');
    foreach($req as $key){
        if(!isset($params[$key])) _error('key: ' . $key);
    }
    $params = (object)$params;
}

// Error function, to either just exit or alert a msg first as well
function _error($msg = null){
    if(!is_null($msg)) echo "alert('Error: {$msg}');";
    exit;
}
        

// Check if the qual, crit, etc... are all valid for a student
function _valid($type, $params, $stage=3, $exclude = array()){
        
    global $qualification, $unit, $criteria, $sessionQuals, $starttime;
    
    if ($type == 'student'){
        $sessionQuals = isset($_SESSION['session_stu_quals']) ? unserialize(urldecode($_SESSION['session_stu_quals'])) : array();
    } elseif ($type == 'unit') {
        
        $studentUnit = false;
        $sessionUnits = isset($_SESSION['session_unit'])? unserialize(urldecode($_SESSION['session_unit'])) : array();
        if(array_key_exists($params->unitID, $sessionUnits))
        {
            $unitObject = $sessionUnits[$params->unitID];
            $unit = $unitObject->unit;
            $qualArray = $unitObject->qualArray;
            if(array_key_exists($params->qualID, $qualArray))
            {
                $studentArray = $qualArray[$params->qualID];
                if(array_key_exists($params->studentID, $studentArray))
                {
                    $studentObject = $studentArray[$params->studentID];
                    $studentUnit = $studentObject->unit;
                }
            } 
        }
        
        // If couldn't find in session, get from DB
        if (!$studentUnit){
            
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELALL;
            $loadParams->loadAward = true;
            
            $studentUnit = Unit::get_unit_class_id($params->unitID, $loadParams);
            $studentUnit->load_student_information($params->studentID, $params->qualID, $loadParams);
                                    
        }
                        
        if (!$studentUnit) exit;
                
    }
    
    if($stage >= 1 && !in_array('qualification', $exclude))
    {
                
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
        
        if ($type == 'student'){
            
            // Load qual for student
            if (isset($sessionQuals[$params->studentID][$params->qualID])){
                $qualification = $sessionQuals[$params->studentID][$params->qualID]->qualification;
            } else {
                $o = new stdClass();
                $o->loadLevel = Qualification::LOADLEVELUNITS;
                $qualification = Qualification::get_qualification_class_id($params->qualID, $o);
                if(!$qualification) exit;
                $qualification->load_student_information($params->studentID, $o);
            }
        
            if(!$qualification) exit;
        
        } elseif ($type == 'unit'){
            
             $qualification = Qualification::get_qualification_class_id($params->qualID, $loadParams);
             $qualification->load_student_information($params->studentID, $loadParams);
            
        }
                
    }

    if($stage >= 2 && !in_array('unit', $exclude))
    {
        // Load unit
        if ($type == 'student'){
            if ($qualification){
                $unit = $qualification->get_single_unit($params->unitID);
            } else {
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELALL;
                $loadParams->loadAward = true;
                $unit = Unit::get_unit_class_id($params->unitID, $loadParams);
            }
        } elseif ($type == 'unit') {
            $unit = $studentUnit;
        }
        
        if(!$unit) exit;
        
    }

    if($stage >= 3 && !in_array('criteria', $exclude))
    {
        // Load Criteria
        if ($unit){
            $criteria = $unit->get_single_criteria($params->criteriaID);
        } else {
            $criteria = Criteria::get_correct_criteria_class(-1, $params->criteriaID, null, Qualification::LOADLEVELMIN);
        }
        if(!$criteria) exit;
    }
                
    
}


function calculateUnitAward($unit, $params = array())
{
    
    global $params;
    
    echo "$('input, select').removeAttr('disabled');";
                
    if($unit->unit_has_award())
    {
        $award = $unit->calculate_unit_award($params->qualID);
        
        if ($award)
        {
            // Display Unit Award
            $textAward = ($award) ? $award->get_award() : "N/S";
            $unitAwardDiv = "unitAward_{$unit->get_id()}_{$params->studentID}";
            $unitAwardEditDiv = "unitAwardEdit_{$unit->get_id()}_{$params->studentID}";
            echo "  if ( $('#{$unitAwardDiv}').length > 0 ) { $('#{$unitAwardDiv}').text('{$textAward}'); } else { $('#{$unitAwardEditDiv}').val('{$award->get_id()}'); } ";
        }
        
    }
    
    // Also do % complete here
    if($unit->has_percentage_completions())
    {
        $percent = $unit->get_percent_completed();
        echo <<<JS
            $('#U{$unit->get_id()}S{$params->studentID}PercentParent').attr('title', '{$percent}% Complete');
            $('#U{$unit->get_id()}S{$params->studentID}PercentComplete').css('width', '{$percent}%');
            $('#U{$unit->get_id()}S{$params->studentID}PercentText').text('{$percent}%');
JS;
    }
    
}


function calculateQualAward($qualification)
{
    
    global $params;
        
    if($qualification->has_final_grade())
    {
        
        // reload units on qual
       # $qualification->reload_units();
        
        $qualAward = $qualification->calculate_predicted_grade();
        
        // Display Qual Award
        $textQualAward = ($qualAward) ? $qualAward->get_award() : "N/A" ;
        $awardType = ($qualAward) ? $qualAward->get_type() : $qualification->get_default_award_type();
        echo <<<JS
            // First look for "qualAward_userid", but if just "qualAward" exists, update that instead.
            // if neither exist, give up

            if( $('#qualAward_{$params->studentID}').length > 0 )
            {
                $('#qualAward_{$params->studentID}').text('{$awardType} - {$textQualAward}');
            }
            else if( $('#qualAward').length > 0 )
            {
                $('#qualAward').text('{$textQualAward}');
            }
JS;
    }
    
}







switch($action)
{
    
    // This action will set a given criteria to Achieved & set the fields `dateset` and `dateupdated` to the given date
    // It's setting both just in case, so we know the correct one is being displayed at all times
    // This is mainly for Gity & Guilds Hair and Beauty who want just to update a date of award, rather than the award
    case 'updateCriteriaAwardDate':
                
        // Required params:        
        $req = array("qualID", "criteriaID", "studentID", "unitID", "mode", "grid");
        _check($req, $params);
        _valid($params->grid, $params, 3, array('qualification'));    
        
        $o = new stdClass();
        $o->loadLevel = Qualification::LOADLEVELMIN;
        $qualification = Qualification::get_qualification_class_id($params->qualID, $o);
        if(!$qualification) exit;
        $qualification->load_student_information($params->studentID, $o);
        
        $sID = $criteria->get_student_ID();
        if (is_null($sID)){
            $criteria->load_student_information($params->studentID, $params->qualID, $params->unitID, false);
        }
        
        if ($params->date == '')
        {
            $time = 0;
        }
        else
        {
            $time = strtotime($params->date);
        }
        
        $sID = $unit->get_student_ID();
        if (is_null($sID)){
            $unit->load_student_information($params->studentID, $params->qualID, $o);
        }
                                
        // Update the award to achieved (if mode simple)
        if($params->mode == "se" || isset($params->setAchieved)){
            
            $awardID = $qualification->get_criteria_met_value();
                                                
            $criteria->update_students_value($awardID);
            
            // If qual has a final award, try to calculate it
            #calculateQualAward($qualification);
            
            echo "$('#S{$params->studentID}_U{$unit->get_id()}_C{$params->criteriaID}_POPUPIMG').attr('src', '{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/icon_OpenOutcomeComplete.png');";
            
        }
                       
        // Update the dateset/dateupdated to overwrite defaults set in save_student
        $criteria->set_award_date($time);
        
        // Save Award
        $criteria->save_student($params->qualID);
                        
        
        // Update session
        if ($params->grid == 'student'){
            update_session_qual($params->studentID, $params->qualID, $qualification);
        } elseif ($params->grid == 'unit'){
            update_session_unit($params->studentID, $params->unitID, $unit, $params->qualID);
        }
        
        // Calculate unit award
        calculateUnitAward($unit);
                                               
        exit;
        
    break;
    
    case 'updateCriteriaTargetDate':
        
        // Required params:        
        $req = array("qualID", "criteriaID", "studentID", "unitID", "date", "mode", "grid");
        _check($req, $params);
        _valid($params->grid, $params, 3, array('qualification'));        
                
        // If time specifed was blank, set to null in db
        $time = strtotime($params->date);
        $time = ($time < 0 || !$time) ? null : $time;
                        
        // Update the target date to overwrite defaults set in save_student
        $criteria->set_user_target_date($time);
        
        // Save Award
        $criteria->save_student($params->qualID);
        
        // Update session
        if ($params->grid == 'student'){
            update_session_qual($params->studentID, $params->qualID, $qualification);
        } elseif ($params->grid == 'unit'){
            update_session_unit($params->studentID, $params->unitID, $unit, $params->qualID);
        }
                                               
        exit;
        
    break;
        
    
    case 'updateUnitAttribute':
        
        // Required params:
        $req = array("unitID", "attribute", "studentID", "value", "qualID");
        _check($req, $params);
        _valid($params->grid, $params, 0);
        
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELMIN;
        $unit = Unit::get_unit_class_id($params->unitID, $loadParams);
                
        $unit->set_attribute($params->attribute, $params->value, $params->qualID, $params->studentID);        
        
        exit;
        
    break;


    case 'updateRangeAwardDate':
        
        $req = array("studentID", "qualID", "rangeID");
        _check($req, $params);
        _valid($params->grid, $params, 1, array('qualification'));
        
        if (!isset($params->date)){
            $params->date = false;
        }
        
        $lvl = new stdClass();
        $lvl->loadLevel = Qualification::LOADLEVELCRITERIA;
        
        
        $range = new Range($params->rangeID);
        $range->load_student_information($params->studentID, $params->qualID);
        if (!$range->is_valid()) exit;
        
        $unit = Unit::get_unit_class_id($range->unitid, $lvl);
        if (!$unit) exit;
        
        $unit->load_student_information($params->studentID, $params->qualID, $lvl);
        if (!$unit->is_student_doing()) exit;
        
        $range->update_student_award_date($params->date);
        
        echo <<<JS
        $('#advRngAwardDate_{$params->studentID}_{$range->id}').val('{$params->date}');
JS;
                        
        exit;
        
    break;
    


    case 'updateRangeAward':
        
        $req = array("studentID", "qualID", "unitID", "rangeID", "value");
        _check($req, $params);
        _valid($params->grid, $params, 2, array('qualification'));
        
        $o = new stdClass();
        $o->loadLevel = Qualification::LOADLEVELMIN;
        $qualification = Qualification::get_qualification_class_id($params->qualID, $o);
        if(!$qualification) exit;
        $qualification->load_student_information($params->studentID, $o);
                
        $range = new Range($params->rangeID);
        $range->load_student_information($params->studentID, $params->qualID);
        if(!$range) exit;
                        
        $unit->load_student_information($params->studentID, $params->qualID);
        if(!$unit->is_student_doing()) exit;
                
        $range->update_student_award($params->value);
        
        // If setting it to no value, set award date back to 0
        if ($params->value == -1){
            $range->update_student_award_date(0);
        }
        
        
        // If all ranges on unit are now awarded, set the overall task to Achieved, else set it to -1
        $unitRanges = $unit->get_ranges();
        $cntRanges = count($unitRanges);
        $numCompleted = 0;
        
        
        foreach($unitRanges as $unitRange)
        {
            $rangeObj = new Range($unitRange->id);
            $rangeObj->load_student_information($params->studentID, $params->qualID);
            if(!$rangeObj || !$rangeObj->gradeID) continue;
            $value = new Value($rangeObj->gradeID);
            if(!$value) continue;
            if($value->is_criteria_met() == "Yes") $numCompleted++;
        }
                
        if($numCompleted == $cntRanges)
        {
            $pID = $range->get_parent_criteria_id();
            $parent = Criteria::get_correct_criteria_class($qualification->get_class_ID(), $pID);
            if($parent){
                $parent->load_student_information($params->studentID, $params->qualID, $unit->get_id());
                $achieved = $qualification->get_criteria_met_value();
                if($achieved > 0){
                    $parent->update_students_value_manual($achieved);
                }
            }
        }
        else
        {
            $pID = $range->get_parent_criteria_id();
            $parent = Criteria::get_correct_criteria_class($qualification->get_class_ID(), $pID);
            if($parent){
                $parent->load_student_information($params->studentID, $params->qualID, $unit->get_id());
                $parent->update_students_value_manual(-1);
            }
        }
        
        $range->ajax_set_grid_grade($params->value);
        
               
        // If unit has an award, try to calculate it
        calculateUnitAward($unit, $params);
        
        // If qual has a final award, try to calculate it
        #calculateQualAward($qualification);
                
        
        exit;
        
    break;


    case 'updateRangeCriteriaAward':
        
        $req = array("studentID", "qualID", "unitID", "criteriaID", "rangeID", "value");
        _check($req, $params);
        _valid($params->grid, $params, 3, array('qualification'));
        
        if (!ctype_digit($params->value)) exit;
        
        $range = new Range($params->rangeID);
        if (!$range->is_valid()) exit;
        
        $o = new stdClass();
        $o->loadLevel = Qualification::LOADLEVELMIN;
        $qualification = Qualification::get_qualification_class_id($params->qualID, $o);
        if(!$qualification) exit;
        $qualification->load_student_information($params->studentID, $o);
        
        $sID = $unit->get_student_ID();
        if (is_null($sID)){
            $unit->load_student_information($params->studentID, $params->qualID, $o);
        }
        
        // Parent criteria (task)
        $task = $unit->get_single_criteria($criteria->get_parent_criteria_ID());
        
        $range->load_student_information($params->studentID, $params->qualID);
        $range->update_student_value($params->criteriaID, $params->value);
        
        
        // If all ranges on unit are now awarded, set the overall task to Achieved, else set it to -1
        $unitRanges = $unit->get_ranges();
        $cntRanges = count($unitRanges);
        $numCompleted = 0;
        
        foreach($unitRanges as $unitRange)
        {
            $rangeObj = new Range($unitRange->id);
            $rangeObj->load_student_information($params->studentID, $params->qualID);
            if(!$rangeObj->is_valid()) continue;
            if(!$rangeObj->gradeID) continue;
            $value = new Value($rangeObj->gradeID);
            if(!$value->get_id()) continue;
            if($value->is_criteria_met() == "Yes") $numCompleted++;
        }
                
        if($numCompleted == $cntRanges)
        {
            $pID = $range->get_parent_criteria_id();
            $parent = Criteria::get_correct_criteria_class($qualification->get_class_ID(), $pID);
            if($parent){
                $parent->load_student_information($params->studentID, $params->qualID, $unit->get_id());
                $achieved = $qualification->get_criteria_met_value();
                if($achieved > 0){
                    $parent->update_students_value_manual($achieved);
                }
            }
        }
        else
        {
            $pID = $range->get_parent_criteria_id();
            $parent = Criteria::get_correct_criteria_class($qualification->get_class_ID(), $pID);
            if($parent){
                $parent->load_student_information($params->studentID, $params->qualID, $unit->get_id());
                $parent->update_students_value_manual(-1);
            }
        }
        
        // If unit has an award, try to calculate it
        calculateUnitAward($unit);
        
        // If qual has a final award, try to calculate it
        //calculateQualAward($qualification);
        
        exit;
        
        
    break;

    
    
    case 'updateRangeTargetDate':
        
        $req = array("studentID", "qualID", "rangeID", "date");
        _check($req, $params);
        _valid($params->grid, $params, 1, array('qualification'));
        
        $lvl = new stdClass();
        $lvl->loadLevel = Qualification::LOADLEVELMIN;
        
        $range = new Range($params->rangeID);
        $range->load_student_information($params->studentID, $params->qualID);
        if (!$range->is_valid()) exit;
        
        $unit = Unit::get_unit_class_id($range->unitid, $lvl);
        if (!$unit) exit;
        
        $unit->load_student_information($params->studentID, $params->qualID, $lvl);
        if (!$unit->is_student_doing()) exit;
        
        
                
        $range->update_student_target_date($params->date);
        
        exit;
        
    break;
    
    
    case 'updateQualAttribute':
        
        // Required params:
        $req = array("qualID", "attribute");
        _check($req, $params);
        
        if (!isset($params->studentID)) $params->studentID = null;
        if (!isset($params->value)) $params->value = '';
        
        _valid('student', $params, 1);
        
        $qualification->set_attribute($params->attribute, $params->value, $params->studentID);
        
        exit;
        
    break;
    
    case 'updateOutcomeObservationDate':
        
        $req = array("studentID", "qualID", "criteriaID", "observationNum");
                
        _check($req, $params);
        _valid($params->grid, $params, 3, array('qualification'));
        
        $o = new stdClass();
        $o->loadLevel = Qualification::LOADLEVELMIN;
        $qualification = Qualification::get_qualification_class_id($params->qualID, $o);
        if(!$qualification) exit;
        $qualification->load_student_information($params->studentID, $o);
        
        $sID = $unit->get_student_ID();
        if (is_null($sID)){
            $unit->load_student_information($params->studentID, $params->qualID);
        }
        
        $sID = $criteria->get_student_ID();
        if (is_null($sID)){
            $criteria->load_student_information($params->studentID, $params->qualID);
        }
                
        if (!isset($params->date)) $params->date = '';
        
        // If date is blank or 0, delete the record rather than update
        if($params->date == 0 || empty($params->date))
        {
            $check = $DB->get_record("block_bcgt_user_outcome_obs", array("userid" => $params->studentID, "bcgtqualificationid" => $params->qualID, "bcgtcriteriaid" => $params->criteriaID, "observationnum" => $params->observationNum));
            if(isset($check->id))
            {
                $DB->delete_records("block_bcgt_user_outcome_obs", array("id" => $check->id));
                $criteria->update_students_value(-1);
                $criteria->save_student($params->qualID);
                echo "$('#outcome_{$criteria->get_id()}_{$params->studentID}').html('');";
                echo "$('#S{$params->studentID}_U{$unit->get_id()}_O{$params->criteriaID}_IMG').attr('src', '{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/icon_OpenOutcome.png');";
                calculateUnitAward($unit);
                exit;
            }
        }
        
        $date = strtotime($params->date);
                
        // Check if there is a record for this yet, insert/update as appropriate
        $check = $DB->get_record("block_bcgt_user_outcome_obs", array("userid" => $params->studentID, "bcgtqualificationid" => $params->qualID, "bcgtcriteriaid" => $params->criteriaID, "observationnum" => $params->observationNum));
        if(isset($check->id))
        {
            $check->date = $date;
            $DB->update_record("block_bcgt_user_outcome_obs", $check);
        }
        else
        {
            $obj = new stdClass();
            $obj->userid = $params->studentID;
            $obj->bcgtqualificationid = $params->qualID;
            $obj->bcgtcriteriaid= $params->criteriaID;
            $obj->observationnum = $params->observationNum;
            $obj->date = $date;
            $DB->insert_record("block_bcgt_user_outcome_obs", $obj);
        }
        
        // Log
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, LOG_VALUE_GRADETRACKER_UPDATED_OUTCOME_OBSERVATION, $params->studentID, $params->qualID, $unit->get_id(), null, $params->criteriaID, $params->observationNum, $date);
        
        // If the student has a date recorded for all observations on this outcome, display tick as outcome achieved
        $achNum = 0;
        
        for($i = 1; $i <= $criteria->get_number_of_observations(); $i++)
        {
            // Check record
            $check = $DB->get_record_select("block_bcgt_user_outcome_obs", "userid = ? AND bcgtqualificationid = ? AND bcgtcriteriaid = ? AND observationnum = ? AND date > 0", array($params->studentID, $params->qualID, $params->criteriaID, $i));
            if(isset($check->id)) $achNum++;
        }
                        
        // Student has an award date for each of the observations on this outcome, so set the award of the 
        // actual outcome (as a criterion) to achieved
        if($achNum == $criteria->get_number_of_observations())
        {
            $awardID = $qualification->get_criteria_met_value();
            $criteria->update_students_value($awardID);
            $criteria->save_student($params->qualID);
            echo "$('#outcome_{$criteria->get_id()}_{$params->studentID}').html('<img src=\"{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/icon_Achieved.png\" class=\"gridIcon\" />');";
            echo "$('#S{$params->studentID}_U{$unit->get_id()}_O{$params->criteriaID}_IMG').attr('src', '{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/icon_OpenOutcomeComplete.png');";
        }
        elseif ($achNum > 0)
        {
            // Not all awarded, but some, so set to Partially Achieved
            $awardID = $qualification->get_criteria_specific_value("PA");
            $criteria->update_students_value($awardID);
            $criteria->save_student($params->qualID);
        }
        
        // If unit has an award, try to calculate it
        calculateUnitAward($unit);
        exit;
        
    break;
    
    
    case 'updateSignOffRangeObservation':
        
        $req = array("studentID", "qualID", "unitID", "sheetID", "rangeID", "observationNum", "value");
        _check($req, $params);
        _valid($params->grid, $params, 1);
        
        $lvl = new stdClass();
        $lvl->loadLevel = Qualification::LOADLEVELCRITERIA;
        
        $unit = Unit::get_unit_class_id($params->unitID, $lvl);
        if(!$unit) exit;
        
        $unit->load_student_information($params->studentID, $params->qualID, $lvl);
        if(!$unit->is_student_doing()) exit;
        
        // Insert/Update record in DB
        $check = $DB->get_record("block_bcgt_user_soff_sht_rgs", array("userid" => $params->studentID, "bcgtqualificationid" => $params->qualID, "bcgtsignoffsheetid" => $params->sheetID, "bcgtsignoffrangeid" => $params->rangeID, "observationnum" => $params->observationNum));
        if(!$check)
        {
            $obj = new stdClass();
            $obj->userid = $params->studentID;
            $obj->bcgtqualificationid = $params->qualID;
            $obj->bcgtsignoffsheetid = $params->sheetID;
            $obj->bcgtsignoffrangeid = $params->rangeID;
            $obj->observationnum = $params->observationNum;
            $obj->value = $params->value;
            $DB->insert_record("block_bcgt_user_soff_sht_rgs", $obj);
        }
        else
        {
            $check->value = $params->value;
            $DB->update_record("block_bcgt_user_soff_sht_rgs", $check);
        }
        
        // If all signoff sheets are now complete, change the img to black
        if($unit->are_all_sign_offs_complete()){
            echo "$('#SIGNOFF_IMG_{$params->studentID}_{$params->unitID}_{$params->qualID}').attr('src', '{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/icon_SignOffSheet.png');";
        }
        else {
            echo "$('#SIGNOFF_IMG_{$params->studentID}_{$params->unitID}_{$params->qualID}').attr('src', '{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/icon_SignOffSheetIncomplete.png');";
        }
        
        // Else set the img to red (in-case it was black before)
        
        // Log
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_UNIT, LOG_VALUE_GRADETRACKER_UPDATED_SIGNOFF_RANGE_OBSERVATION, $params->studentID, $params->qualID, $params->unitID, null, $params->sheetID, $params->rangeID, $params->observationNum);
        
        
        // If unit has an award, try to calculate it
        calculateUnitAward($unit);
        
        
        exit;
        
    break;
    
    case 'updateFormativeDetails':
                
        $req = array("qualID", "criteriaID", "unitID", "studentID", "value");
        _check($req, $params);
        _valid($params->grid, $params, 3, array('qualification'));    
        
        $o = new stdClass();
        $o->loadLevel = Qualification::LOADLEVELMIN;
        $qualification = Qualification::get_qualification_class_id($params->qualID, $o);
        if(!$qualification) exit;
        $qualification->load_student_information($params->studentID, $o);
        
        $sID = $criteria->get_student_ID();
        if (is_null($sID)){
            $criteria->load_student_information($params->studentID, $params->qualID, $params->unitID, false);
        }
                              
        // Update the dateset/dateupdated to overwrite defaults set in save_student
        $criteria->set_user_defined_value( $params->value );
        
        // Save Award
        $criteria->save_student($params->qualID);
                
        exit;
        
    break;
    
    
}

