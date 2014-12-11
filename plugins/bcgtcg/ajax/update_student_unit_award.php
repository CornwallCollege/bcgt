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
require_once('../../../lib.php');

global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$context = context_course::instance($COURSE->id);
require_login();
require_cg();
$PAGE->set_context($context);

$params = $_POST['params'];
$studentID = $params['sID'];
$qualID = $params['qID'];
$unitID = $params['uID'];
$value = $params['value'];
$grid = $params['grid'];


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
                        
            $unit->set_user_award($award);
            $unit->save_student($qualID);

            $retval->unitid = $unitID;
            $retval->studentid = $studentID;
            $retval->qualid = $qualID;
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
        
        
        
        $qualAward = null;
        $calculated = false;
        if($qualification->has_final_grade())
        {
            //only do it if the value is changing for the criteria
            $calculated = true;
            $qualAward = $qualification->calculate_final_grade();
        }
        if(!$calculated)
        {
            $qualAward = $qualification->get_student_award();
        }

        $qualAwardID = -1;
        $qualAwardValue = "N/A";
        if($qualAward)
        {
            $qualAwardID = $qualAward->get_id();
            $qualAwardValue = $qualAward->get_award();
            $qualAwardType = $qualAward->get_type();
        }

        $qualAwardType = 'Predicted';

        $jsonQualAward = new stdClass();
        $jsonQualAward->awardid = $qualAwardID;
        $jsonQualAward->awardvalue = $qualAwardValue;
        $jsonQualAward->awardtype = $qualAwardType;

        $retval->qualaward = $jsonQualAward;

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
    
    $sessionUnits = isset($_SESSION['session_unit'])? 
        unserialize(urldecode($_SESSION['session_unit'])) : array();
    $unit = null;
    $studentUnitFound = false;
    if(array_key_exists($unitID, $sessionUnits))
    {
        $unitObject = $sessionUnits[$unitID];
        $unit = $unitObject->unit;
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
            $studentUnit->set_user_award($award);
            $studentUnit->save_student($qualID);
            
            $retval->unitid = $unitID;
            $retval->studentid = $studentID;
            $retval->qualid = $qualID;
            $retval->time = date('H:i:s');
        
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
            if($qualAward)
            {
                if(isset($qualAward->averageAward))
                {
                    $qualAwardID = $qualAward->averageAward->get_id();
                    $qualAwardValue = $qualAward->averageAward->get_award();
                    $qualAwardType = $qualAward->averageAward->get_type();
                }
                elseif(isset($qualAward->targetgrade))
                {
                    $qualAwardID = $qualAward->id;
                    $qualAwardValue = $qualAward->targetgrade;
                    $qualAwardType = $qualAward->type;
                }
            }

            $qualAwardType = 'Predicted';
            $retval->unitaward = null;

            $jsonQualAward = new stdClass();
            $jsonQualAward->awardid = $qualAwardID;
            $jsonQualAward->awardvalue = $qualAwardValue;
            $jsonQualAward->awardtype = $qualAwardType;

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
            $qualArray = $unitObject->qualArray;
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
        
        if ($studentObject){
        
            $studentObject->unit = $studentUnit;
            $studentArray[$studentID] = $studentObject;
            $qualArray[$qualID] = $studentArray;
            $unitObject->qualArray = $qualArray;
            $sessionUnits[$unitID] = $unitObject;
            $_SESSION['session_unit'] = urlencode(serialize($sessionUnits));
        
        }
        
        echo json_encode( $retval );
    }
    else
    {
        echo json_encode("No Qualification Loaded");
    }
}

?>