<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */


require_once('../../../../../config.php');

global $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

$courseID = required_param('cID', PARAM_INT);
if ($courseID < 1) $courseID = SITEID;
$context = context_course::instance($courseID);
require_login();

if (!has_capability("block/bcgt:editunit", $context)){
    echo 0;
    exit;
}

get_btec_requires();
$PAGE->set_context($context);

$groupID = optional_param('gID', -1, PARAM_INT);
$qualID = required_param('qID', PARAM_INT);
$unitID = required_param('uID', PARAM_INT);
$criteriaName = required_param('crit', PARAM_TEXT);
$valueID = required_param('value', PARAM_TEXT);


// Get the unit
$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELALL;
$loadParams->loadAward = true;

// Get students
$studentsArray = get_users_on_unit_qual($unitID, $qualID, $courseID, $groupID);
if (!$studentsArray){
    echo 1;
    exit;
}

$sessionUnits = isset($_SESSION['session_unit'])? 
        unserialize(urldecode($_SESSION['session_unit'])) : array();


// Loop through students
foreach($studentsArray as $student)
{

    $unit = Unit::get_unit_class_id($unitID, $loadParams);
    if ($unit)
    {
        
        $unit->load_student_information($student->id, $qualID, $loadParams);   

        $studentCriterion = $unit->get_single_criteria(-1, $criteriaName);

        if ($studentCriterion)
        {

            $studentCriterion->set_user($USER->id);
            $studentCriterion->set_date();
            $studentCriterion->update_students_value($valueID);
            $studentCriterion->save_student($qualID, true); 

            $award = $unit->calculate_unit_award($qualID);
            if ($award)
            {
                $awardID = $award->get_id();
            }
            else
            {
                $awardID = '';
            }

            $student->unit = $unit;

            // Update session unit
            if (!isset($sessionUnits[$unitID])){
                $sessionUnits[$unitID] = new stdClass();
                $sessionUnits[$unitID]->sessionStartTime = time();
                $sessionUnits[$unitID]->qualArray = array();
            }

            if (!isset($sessionUnits[$unitID]->qualArray[$qualID])){
                $sessionUnits[$unitID]->qualArray[$qualID] = array();
            }

            $sessionUnits[$unitID]->qualArray[$qualID][$student->id] = $student;

            // Update the select menu of this student
            echo " $('#sID_{$student->id}_cID_{$studentCriterion->get_id()}').val('{$valueID}'); ";
            echo " $('#uAw_{$student->id}').val('{$awardID}'); ";

        }
    
    }
    
}

$_SESSION['session_unit'] = urlencode(serialize($sessionUnits));

exit;