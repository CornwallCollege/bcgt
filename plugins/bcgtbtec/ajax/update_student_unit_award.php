<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

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
$value = required_param('value', PARAM_TEXT);
$unitID = required_param('uID', PARAM_INT);
$user = optional_param('user', -1, PARAM_INT);
$grid = required_param('grid', PARAM_TEXT);
if($user == -1)
{
    $user = $USER->id;
    
}
if($grid == 'student')
{
    //get the qual from the browser
    require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Qualification.class.php');
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
    $valueID = $value;
    if(!$qualification)
    {
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        if($qualification)
        {
            $qualification->load_student_information($studentID,
                $loadParams);
        }
    }
    $retval = new stdClass();
    if($qualification)
    {
        $unit = $qualification->get_single_unit($unitID);
        if($unit)
        {
            $award = Award::get_award_id($valueID);
            $obj = $unit->set_award_criteria($award, $qualID);
            if($obj)
            {
                $criteriaList = array();
                $met = $obj->metCriteria;
                $unMet = $obj->unMetCriteria;
                if($met)
                {
                    foreach($met AS $m)
                    {
                        $jsonCriteria = new stdClass();
                        $jsonCriteria->id = $m;
                        $jsonCriteria->met = 1;
                        $jsonCriteria->valueid = true;
                        $criteriaList[] = $jsonCriteria;
                    }	
                }
                if($unMet)
                {
                    foreach($unMet AS $u)
                    {
                        $jsonCriteria = new stdClass();
                        $jsonCriteria->id = $u;
                        $jsonCriteria->met = 0;
                        $jsonCriteria->valueid = false;
                        $criteriaList[] = $jsonCriteria;
                    }	
                }
            }
            $retval->criterialist = $criteriaList;
            $retval->unitid = $unitID;
            $unit->save_student($qualID);

            $qualAward = null;
            if($qualification->has_final_grade())
            {
                $qualAward = $qualification->calculate_predicted_grade();
            }
            //qualAward is an object of
            //minAward
            //maxAward
            //avgAward

            $retval->studentid = $studentID;
            $retval->qualid = $qualID;
            $qualAwardID = -1;
            $qualAwardValue = "N/A";
            $qualAwardUcas = 'N/A';
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
            $retval->unitaward = null;

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


        echo json_encode( $retval );
    }
    else
    {
        echo json_encode("No Qualification Loaded");
    }
}
elseif($grid == 'unit')
{
    $valueID = $value;
    global $CFG;
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
        $qualArray = array();
        if(isset($unitObject->qualArray))
        {
            $qualArray = $unitObject->qualArray;
            if(array_key_exists($qualID, $qualArray))
            {
                $studentArray = $qualArray[$qualID];
                if(array_key_exists($studentID, $studentArray))
                {
                    $studentObject = $studentArray[$studentID];
                    $studentUnit = $studentObject->unit;
                    if($studentUnit)
                    {
                        $studentUnitFound = true;
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
    $retval = new stdClass();
    if($studentUnit)
    {
            $award = Award::get_award_id($valueID);
            $obj = $studentUnit->set_award_criteria($award, $qualID);
            if($obj)
            {
                $criteriaList = array();
                $met = $obj->metCriteria;
                $unMet = $obj->unMetCriteria;
                if($met)
                {
                    foreach($met AS $m)
                    {
                        $jsonCriteria = new stdClass();
                        $jsonCriteria->id = $m;
                        $jsonCriteria->met = 1;
                        $jsonCriteria->valueid = true;
                        $criteriaList[] = $jsonCriteria;
                    }	
                }
                if($unMet)
                {
                    foreach($unMet AS $u)
                    {
                        $jsonCriteria = new stdClass();
                        $jsonCriteria->id = $u;
                        $jsonCriteria->met = 0;
                        $jsonCriteria->valueid = false;
                        $criteriaList[] = $jsonCriteria;
                    }	
                }
            }
            $retval->criterialist = $criteriaList;
            $retval->unitid = $unitID;
            $retval->studentid = $studentID;
            $studentUnit->save_student($qualID);
            $calculated = false;
            $qualAward = null;
            $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
            if($qualification)
            {
                $qualification->load_student_information($studentID, $loadParams);
                $qualAward = $qualification->calculate_predicted_grade();
                $calculated = true;
            }
            if(!$calculated)
            {
                $qualAward = $studentUnit->get_student_qual_award($studentID, $qualID);
            }
            //qualAward is an object of
            //minAward
            //maxAward
            //avgAward

            $retval->studentid = $studentID;
            $qualAwardID = -1;
            $qualAwardValue = "N/A";
            $qualAwardUcas = 0;
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
            $retval->unitaward = null;

            $jsonQualAward = new stdClass();
            $jsonQualAward->awardid = $qualAwardID;
            $jsonQualAward->awardvalue = $qualAwardValue;
            $jsonQualAward->awardtype = $qualAwardType;
            $jsonQualAward->ucaspoints = $qualAwardUcas;

            $retval->qualaward = $jsonQualAward;
            $retval->time = date('H:i:s');

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


        if(array_key_exists($unitID, $sessionUnits))
        {
            $unitObject = $sessionUnits[$unitID];
            $unit = $unitObject->unit;
            $qualArray = array();
            if(isset($unitObject->qualArray))
            {
                $qualArray = $unitObject->qualArray;
            }
        }
        else
        {
            //it hasnt been loaded into the session before! (can it even get here if this is the case?)
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
        
        echo json_encode( $retval );
    }
    else
    {
        echo json_encode("No Qualification Loaded");
    }
}
elseif($grid == 'class')
{
    $valueID = $value;
    global $CFG;
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECQualification.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECUnit.class.php');
    $sessionQuals = isset($_SESSION['session_class_quals'])? 
        unserialize(urldecode($_SESSION['session_class_quals'])) : array();
    //get the qual from the session:
    //this will be an array of qualID => qual
    //does the qual exist already for this session?
    if(array_key_exists($qualID, $sessionQuals))
    {
        $qualification = $sessionQuals[$qualID];
    }
    else
    {
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
    }
    
    //we need to load the entire qualification up (including criteria) so that the criteria
    //get checked in the grid. 
    
    if($qualification)
    {
        $loadParams = new stdClass();
        //neess to be all so it will tick the criterias
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
//        $qualification->load_users('student', false, null, $courseID, $groupID);
        $qualification->load_student_information($studentID, $loadParams);
    }    
    $retval = new stdClass();
    if($qualification)
    {
        $unit = $qualification->get_single_unit($unitID);
        if($unit)
        {
            $award = Award::get_award_id($valueID);
            $obj = $unit->set_award_criteria($award, $qualID);
            $retval->unitid = $unitID;
            $unit->save_student($qualID);
            $qualAward = null;
            if($qualification->has_final_grade())
            {
                $qualAward = $qualification->calculate_predicted_grade();
            }
            //qualAward is an object of
            //minAward
            //maxAward
            //avgAward

            $retval->studentid = $studentID;
            $retval->qualid = $qualID;
            $qualAwardID = -1;
            $qualAwardValue = "N/A";
            $qualAwardUcas = 0;
            if($qualAward)
            {
                if(isset($qualAward->averageAward))
                {
                    $qualAwardID = $qualAward->averageAward->get_id();
                    $qualAwardValue = $qualAward->averageAward->get_award();
                    $qualAwardType = $qualAward->averageAward->get_type();
                    $qyalAwardUcas = $qualAward->averageAward->get_ucasPoints();
                }
            }

            $qualAwardType = 'Predicted';

            $jsonQualAward = new stdClass();
            $jsonQualAward->awardid = $qualAwardID;
            $jsonQualAward->awardvalue = $qualAwardValue;
            $jsonQualAward->awardtype = $qualAwardType;
            $jsonQualAward->ucaspoints = $qualAwardUcas;
            
            $retval->qualaward = $jsonQualAward;
            $retval->time = date('H:i:s');
            
            $sessionQuals[$qualID] = $qualification;
            $_SESSION['session_class_quals'] = urlencode(serialize($sessionQuals));
            
            echo json_encode( $retval );
        }
    }
}

?>