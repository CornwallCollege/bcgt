<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
//example URL to test:
//checkboxes
///blocks/bcgt/plugins/bcgtbtec/ajax/update_student_value.php?sID=10&qID=146&cID=2453&uservalue=-1&value=true&vtype=check&user=3&uID=241&grid=student
//advanced:
//
/**
 * TO DO make sure that quals are updated upon save of the criteria update
 * Thus allowing check of force load of new qual object vs session
 */

require_once('../../../../../config.php');

global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$context = context_course::instance($COURSE->id);
require_login();
get_btec_requires();
$PAGE->set_context($context);

$studentID = required_param('sID', PARAM_INT);
$qualID = required_param('qID', PARAM_INT);
$criteriaID = required_param('cID', PARAM_INT);
$value = required_param('value', PARAM_TEXT);
$unitID = required_param('uID', PARAM_INT);
$user = optional_param('user', -1, PARAM_INT);
$vType = optional_param('vtype', 'id', PARAM_TEXT);
$grid = required_param('grid', PARAM_TEXT);
if($user == -1)
{
    $user = $USER->id;
}
$retval = new stdClass();
//get the qual from the browser
if($grid == 'student' || $grid == 'act')
{
    require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Qualification.class.php');
    if($grid == 'student')
    { 
        $sessionQuals = isset($_SESSION['session_stu_quals'])? 
            unserialize(urldecode($_SESSION['session_stu_quals'])) : array();
        //this will be an array of studentID => qualarray->qual object->qual
        $qualification = null;
        if(array_key_exists($studentID, $sessionQuals))
        {
            //the sessionsQuals[studentID] is an array of qualid =>object
            //where object has qualification and session start
            $studentQualArray = $sessionQuals[$studentID];
            if(array_key_exists($qualID, $studentQualArray))
            {
                $qualObject = $studentQualArray[$qualID];
                $qualification = $qualObject->qualification;
            }
        }
    }
    elseif($grid == 'act')
    {
        $sessionActs = isset($_SESSION['session_act'])? 
            unserialize(urldecode($_SESSION['session_act'])) : array();
        //this will be an array of studentID => qualarray->qual object->qual
        $qualification = null;
        if(array_key_exists($qualID, $sessionActs->quals))
        {
            $qualification = $sessionActs->quals[$qualID];
        }
    }
    $stuLoaded = false;
    $valueID = $value;
    if(!$qualification)
    {
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        if($qualification)
        {
            $stuLoaded = true;
            $qualification->load_student_information($studentID,
                $loadParams);
        }
    }
    if($qualification)
    {
        $criteria = null;
        $unit = $qualification->get_single_unit($unitID);
        if($unit)
        {
            $criteria = $unit->get_single_criteria($criteriaID);
        }  
        if($criteria)
        {    
            //we might not have loaded the student information??
            if(!$stuLoaded)
            {
                $criteria->load_student_information($studentID, $qualID);
            }
            if($vType == 'check')
            {
                //then its just the check box and so we need to get the met and not met value
                if($value == 'true')
                {
                    $valueID = $qualification->get_criteria_met_value();
                }
                else
                {
                    $valueID = $qualification->get_criteria_not_met_value();
                }
            }
            $criteria->set_user($user);
            $criteria->set_date();
            $subCritUpdated = $criteria->update_students_value($valueID);
            $criteria->set_flag();
            //are we updating the flag?
            $criteria->save_student($qualID, true); # Save straight away so we can check the parent and loop through all children's awards         
            //need to know if the above is met or unmet for the simple grid
            //we need to check if all sub criteria have been marked 
            //to then mark the criteria associated
            //returns true if the parent criteria is changed. 
            //but we might not have loaded the criteria from the unit
            //it might be from the qual
            $parentCriteriaAltered = $unit->check_parent_criteria($criteriaID, $qualID);
            //need to know if this is met or unmet for the simple grid
            $met = Value::is_met($valueID);
            if($subCritUpdated || $parentCriteriaAltered)
            {
                $criteriaList = array();
                if($subCritUpdated)
                {
                    //then we need to return all of the 
                    //subcriteria so we can update the form.
                    $subCriteria = $criteria->get_sub_criteria();
                    if($subCriteria)
                    {
                        foreach($subCriteria AS $sub)
                        {
                            $jsonCriteria = new stdClass();
                            $jsonCriteria->id = $sub->get_id();
                            $jsonCriteria->met = $met;
                            $jsonCriteria->valueid = $valueID;
                            $criteriaList[] = $jsonCriteria;
                         }	
                    }
                }
                if($parentCriteriaAltered)
                {
                   //then we need to return the parent criteria
                   $parentCrit = Criteria::set_up_parent_criteria_from_sub_criteria($criteriaID);
                   if($parentCrit)
                   {
                        $jsonCriteria = new stdClass();
                        $jsonCriteria->id = $parentCrit->get_id();
                        $jsonCriteria->met = $met;
                        $jsonCriteria->valueid = $valueID;
                        $criteriaList[] = $jsonCriteria;
                   }
                }

                $retval->criterialist = $criteriaList; 
            }
        }

        if($unit || $criteria)
        {
            $changed = false;
            $oldAward = '';
            //TODO SHould also update who updated it. 

            $award = null;
            if($unit)
            {
                //has the unit award altered? do we need to calculate the qual award?
                $oldUnitAward = $unit->get_user_award();
                if($oldUnitAward)
                {
                    $oldAward = $oldUnitAward->get_award();
                }

                ///only do it if the value is change for the criteria
                if($valueID)
                {
                    $award = $unit->calculate_unit_award($qualID);
                    if($award)
                    {
                        $newAward = $award->get_award();
                        if($oldAward != $newAward)
                        {
                            $changed = true;
                        }
                        $newShortAward = $award->get_short_award();
                        if($newShortAward && $newShortAward != '')
                        {
                            $newAward = $newShortAward;
                        }
                    }
                }
            }

            $qualAward = null;
            $calculated = false;
            if($changed && $qualification->has_final_grade())
            {
                //only do it if the value is changing for the criteria
                if($valueID)
                {
                    $calculated = true;
                    $qualAward = $qualification->calculate_predicted_grade();
                }
            }
            if(!$calculated)
            {
                $qualAward = $qualification->get_student_award();
            }
            //qualAward is an object of
            //minAward
            //maxAward
            //avgAward

            $retval->studentid = $studentID;
            $retval->unitid = $unitID;
            $retval->qualid = $qualID;
            $retval->valueid = $value;
            $retval->originalcriteriaid = $criteriaID;

            $unitAwardID = -1;
            $unitAwardValue = "N/S";
            $unitAwardRank = 0;
            $qualAwardID = -1;
            $qualAwardValue = "N/A";
            $qualAwardUcas = 'N/A';
            if($award)
            {
                $unitAwardID = $award->get_id();
                $unitAwardValue = $award->get_award();
                $unitAwardRank = $award->get_rank();
            }

            if($qualAward)
            {
                if(isset($qualAward->averageAward))
                {
                    $qualAwardID = $qualAward->averageAward->get_id();
                    $qualAwardValue = $qualAward->averageAward->get_award();
                    $qualAwardType = $qualAward->averageAward->get_type();
                    $qualAwardUcas = $qualAward->averageAward->get_ucasPoints();
                }
                if(isset($qualAward->minAward))
                {
                    $jsonQualAward = new stdClass();
                    $jsonQualAward->awardid = $qualAward->minAward->get_id();
                    $jsonQualAward->awardvalue = $qualAward->minAward->get_award();
                    $jsonQualAward->awardtype = $qualAward->minAward->get_type();
                    $jsonQualAward->ucaspoints = $qualAward->minAward->get_ucasPoints();
                    $retval->minqualaward = $jsonQualAward;

                }
                if(isset($qualAward->maxAward))
                {
                    $jsonQualAward = new stdClass();
                    $jsonQualAward->awardid = $qualAward->maxAward->get_id();
                    $jsonQualAward->awardvalue = $qualAward->maxAward->get_award();
                    $jsonQualAward->awardtype = $qualAward->maxAward->get_type();
                    $jsonQualAward->ucaspoints = $qualAward->maxAward->get_ucasPoints();
                    $retval->maxqualaward = $jsonQualAward;
                }
            }

            $qualAwardType = 'Predicted';

            $jsonUnitAward = new stdClass();
            $jsonUnitAward->unitid = $unitID;
            $jsonUnitAward->awardid = $unitAwardID;
            $jsonUnitAward->awardvalue = $unitAwardValue;
            $jsonUnitAward->awardrank = $unitAwardRank;
            $retval->unitaward = $jsonUnitAward;

            $jsonQualAward = new stdClass();
            $jsonQualAward->awardid = $qualAwardID;
            $jsonQualAward->awardvalue = $qualAwardValue;
            $jsonQualAward->awardtype = $qualAwardType;
            $jsonQualAward->ucaspoints = $qualAwardUcas;

            $retval->qualaward = $jsonQualAward;
            $retval->time = date('H:i:s');
        }

    //                // Get the logs for this qual & student
    //                $user = get_record_select("user", "`id` = '{$studentID}'", "username");
    //                $params['qualID'] = $qualID;
    //                $params['student'] = $user->username;
    //                $params['lastID'] = (isset($_SESSION['lastLogID'][$qualID])) ? $_SESSION['lastLogID'][$qualID] : 0;
    //                $logXml = Log::get_grid_xml($params);
    //                                
    //                $retval .= $logXml;
    //                                
    //                $retval .= "<error>{$GLOBALS['AJAX_ERROR']}</error>";


        if($grid == 'student')
        { 
            $qualArray = $sessionQuals[$studentID];
            if(array_key_exists($qualID, $qualArray))
            {
                $qualObject = $qualArray[$qualID];
            }
            else 
            {
                $qualObject = new stdClass();
            }

            $qualObject->qualification = $qualification;
            $qualArray[$qualID] = $qualObject;
            $sessionQuals[$studentID] = $qualArray;
            $_SESSION['session_stu_quals'] = urlencode(serialize($sessionQuals));
        }
        elseif($grid == 'act')
        {
            $sessionActs->quals[$qualID] = $qualification;
            $_SESSION['session_act'] = urlencode(serialize($sessionActs));
        }


        echo json_encode( $retval );
    }
    else
    {
        echo json_encode("No Qualification Loaded");
    }
}
elseif($grid == 'unit')
{
    $retval = '';
    if($qualID == -1)
    {
        //then we need to work out what qualid this
        //user is on for this unit what happens if they are on more than one?
        $sql = "SELECT * FROM {block_bcgt_user_unit} WHERE userid = ? AND bcgtunitid = ?";
        $records = $DB->get_records_sql($sql, array($studentID, $unitID));
        if($records)
        {
            $retval = new stdClass();
            $returnArray = array();
            $retval->multiple = true;
            foreach($records AS $record)
            {
                $returnArray[] = process_bcgt_btec_uit_criteria_check($record->bcgtqualificationid, $unitID, $value, $studentID, $criteriaID, $vType, $user); 
            }
            $retval->multipleArray = $returnArray;
        }
    }
    else
    {
        $retval = process_bcgt_btec_uit_criteria_check($qualID, $unitID, $value, $studentID, $criteriaID, $vType, $user);
        $retval->multiple = false;  
    }
    if($retval != '')
    {
        echo json_encode( $retval );
    }
    else
    {
        echo json_encode("No Unit Loaded");
    }
}

function process_bcgt_btec_uit_criteria_check($qualID, $unitID, $value, $studentID, $criteriaID, $vType, $user)
{
    $retval = new stdClass();
    $valueID = $value;
    global $CFG, $DB;
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECQualification.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECUnit.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECCriteria.class.php');
    $sessionUnits = isset($_SESSION['session_unit'])? 
        unserialize(urldecode($_SESSION['session_unit'])) : array();
    $unit = null;
    $studentUnitFound = false;
    if(array_key_exists($unitID, $sessionUnits))
    {
        $unitObject = $sessionUnits[$unitID];
        $unit = $unitObject->unit;
        if(isset($unitObject->qualArray))
        {
            $qualArray = $unitObject->qualArray;
            if(array_key_exists($qualID, $qualArray))
            {
                $studentArray = $qualArray[$qualID];
                if(array_key_exists($studentID, $studentArray))
                {
                    $studentObject = $studentArray[$studentID];
                    if (isset($studentObject->unit))
                    {
                        $studentUnit = $studentObject->unit;
                        if($studentUnit)
                        {
                            $studentUnitFound = true;
                        }
                    }
                }
            }
        }
         
    }
        
    //will be used later
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELALL;
    $loadParams->loadAward = true;
    if(!$studentUnitFound)
    {
        $studentUnit = Unit::get_unit_class_id($unitID, $loadParams);
        if($studentUnit)
        {
            $studentUnit->load_student_information($studentID, $qualID, $loadParams);
        }
    }
       
    if($studentUnit)
    {
        $criteria = $studentUnit->get_single_criteria($criteriaID);
        if($criteria)
        {    
            if($vType == 'check')
            {
                //then its just the check box and so we need to get the met and not met value
                if($value == 'true')
                {
                    $valueID = BTECQualification::get_criteria_met_val();
                }
                else
                {
                    $valueID = BTECQualification::get_criteria_not_met_val();
                }
            }
            $criteria->set_user($user);
            $criteria->set_date();
            $subCritUpdated = $criteria->update_students_value($valueID);
            $criteria->set_flag();
            $criteria->save_student($qualID, true); # Save straight away so we can check the parent and loop through all children's awards         
            //need to know if the above is met or unmet for the simple grid
            //we need to check if all sub criteria have been marked 
            //to then mark the criteria associated
            //returns true if the parent criteria is changed. 
            //but we might not have loaded the criteria from the unit
            //it might be from the qual
            $parentCriteriaAltered = $studentUnit->check_parent_criteria($criteriaID, $qualID);
            //need to know if this is met or unmet for the simple grid
            $met = Value::is_met($valueID);
            if($subCritUpdated || $parentCriteriaAltered)
            {
                $criteriaList = array();
                if($subCritUpdated)
                {
                    //then we need to return all of the 
                    //subcriteria so we can update the form.
                    $subCriteria = $criteria->get_sub_criteria();
                    if($subCriteria)
                    {
                        foreach($subCriteria AS $sub)
                        {
                            $jsonCriteria = new stdClass();
                            $jsonCriteria->id = $sub->get_id();
                            $jsonCriteria->met = $met;
                            $jsonCriteria->valueid = $valueID;
                            $criteriaList[] = $jsonCriteria;
                         }	
                    }
                }
                if($parentCriteriaAltered)
                {
                   //then we need to return the parent criteria
                   $parentCrit = Criteria::set_up_parent_criteria_from_sub_criteria($criteriaID);
                   if($parentCrit)
                   {
                        $jsonCriteria = new stdClass();
                        $jsonCriteria->id = $parentCrit->get_id();
                        $jsonCriteria->met = $met;
                        $jsonCriteria->valueid = $valueID;
                        $criteriaList[] = $jsonCriteria;
                   }
                }

                $retval->criterialist = $criteriaList; 
            }
        }
        $retval->studentid = $studentID;
        $retval->valueid = $value;
        if($studentUnit || $criteria)
        {
            $changed = false;
            $oldAward = '';
            //TODO SHould also update who updated it. 

            $award = null;
            if($studentUnit)
            {
                //has the unit award altered? do we need to calculate the qual award?
                $oldUnitAward = $studentUnit->get_user_award();
                if($oldUnitAward)
                {
                    $oldAward = $oldUnitAward->get_award();
                }

                ///only do it if the value is change for the criteria
                if($valueID)
                {
                    $award = $studentUnit->calculate_unit_award($qualID);
                    if($award)
                    {
                        $newAward = $award->get_award();
                        if($oldAward != $newAward)
                        {
                            $changed = true;
                        }
                    }
                }
            }

            $qualAward = null;
            $calculated = false;
            if($changed)
            {
                //then we need to load the students qual up. 
                //only do it if the value is changing for the criteria
                if($valueID)
                {
                    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
                    if($qualification)
                    {
                        $qualification->load_student_information($studentID, $loadParams);
                        $calculated = true;
                        $qualAward = $qualification->calculate_predicted_grade();
                    }
                }
            }
            if(!$calculated)
            {
                $qualAward = $studentUnit->get_student_qual_award($studentID, $qualID);
            }
            //qualAward is an object of
            //minAward
            //maxAward
            //avgAward
            $retval->unitid = $unitID;
            $retval->originalcriteriaid = $criteriaID;

            $unitAwardID = -1;
            $unitAwardValue = "N/S";
            $unitAwardRank = 0;
            $qualAwardID = -1;
            $qualAwardValue = "N/A";

            if($award)
            {
                $unitAwardID = $award->get_id();
                $unitAwardValue = $award->get_award();
                $unitAwardRank = $award->get_rank();
            }

            if($qualAward)
            {
                if(isset($qualAward->averageAward))
                {
                    $qualAwardID = $qualAward->averageAward->get_id();
                    $qualAwardValue = $qualAward->averageAward->get_award();
                    $qualAwardType = $qualAward->averageAward->get_type();
                    $qualAwardUcas = $qualAward->averageAward->get_ucasPoints();
                }
                elseif(isset($qualAward->targetgrade))
                {
                    $qualAwardID = $qualAward->id;
                    $qualAwardValue = $qualAward->targetgrade;
                    $qualAwardType = $qualAward->type;
                    $qualAwardUcas = $qualAward->ucaspoints;
                }
            }

            $qualAwardType = 'Predicted';
            $qualAwardUcas = 0;

            $jsonUnitAward = new stdClass();
            $jsonUnitAward->unitid = $unitID;
            $jsonUnitAward->awardid = $unitAwardID;
            $jsonUnitAward->awardvalue = $unitAwardValue;
            $jsonUnitAward->awardrank = $unitAwardRank;
            $retval->unitaward = $jsonUnitAward;

            $jsonQualAward = new stdClass();
            $jsonQualAward->awardid = $qualAwardID;
            $jsonQualAward->awardvalue = $qualAwardValue;
            $jsonQualAward->awardtype = $qualAwardType;
            $jsonQualAward->ucaspoints = $qualAwardUcas;
            

            $retval->qualaward = $jsonQualAward;
            $retval->time = date('H:i:s');
        }   
                 
        if(array_key_exists($unitID, $sessionUnits))
        {
            $unitObject = $sessionUnits[$unitID];
            $unit = $unitObject->unit;
            if(isset($unitObject->qualArray))
            {
                $qualArray = $unitObject->qualArray;
            }
            else
            {
                $qualArray = array();
            }
        }
        else
        {
            //it hasnt been loaded into the session before! (can it even get here if this is the case?
            ////yes if it is the unit class grid. 
            //then we need to add it
            $unitObject = new stdClass();
            $unitObject->unit = Unit::get_unit_class_id($unitID, $loadParams);
            $qualArray = array();
        }
                        
        if(array_key_exists($qualID, $qualArray))
        {
            $studentArray = $qualArray[$qualID];
        }
        else
        {
            $studentArray = array();
        }
                
        if(array_key_exists($studentID, $studentArray))
        {
            $studentObject = $studentArray[$studentID];
        }
        else
        {
            $studentObject = $DB->get_record_sql("SELECT * FROM {user} WHERE id = ?", array($studentID));
        }
        $studentObject->unit = $studentUnit;
        $studentArray[$studentID] = $studentObject;
        $qualArray[$qualID] = $studentArray;
        $unitObject->qualArray = $qualArray;
        $sessionUnits[$unitID] = $unitObject;
        $_SESSION['session_unit'] = urlencode(serialize($sessionUnits));
    }
    return $retval;
}

?>