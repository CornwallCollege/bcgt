<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once('../../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$courseID = optional_param('cID', -1, PARAM_INT);
if($courseID != -1)
{
    $context = context_course::instance($courseID);
}
else
{
    $context = context_course::instance($COURSE->id);
}

require_login();

$PAGE->set_context($context);
$qualID = optional_param('qID', -1, PARAM_INT);
$unitID = optional_param('uID', -1, PARAM_INT);
$studentID = optional_param('sID', -1, PARAM_INT);
$forceLoad = optional_param('fload', true, PARAM_BOOL);
$clearSession = optional_param('csess', true, PARAM_BOOL);

$qualification = null;
if(!$clearSession)
{
    $sessionQuals = isset($_SESSION['session_stu_quals'])? 
    unserialize(urldecode($_SESSION['session_stu_quals'])) : array(); 
}
else
{
    $sessionQuals = array();
}

$qualObject = new stdClass();

//this will be an array of studentID => qualarray->qual object->qual
//does the qual exist already for this student?
if(array_key_exists($studentID, $sessionQuals))
{
    //the sessionsQuals[studentID] is an array of qualid =>object
    //where object has qualification and session start
    $studentQualArray = $sessionQuals[$studentID];
    if(array_key_exists($qualID, $studentQualArray))
    {
        $qualObject = $studentQualArray[$qualID];
        $sessionStartTime = $qualObject->sessionStartTime;
        $qualification = $qualObject->qualification;
        
        //we need to check if the students qual has been changed at all since the start of the current session
        $studentUpdateTime = get_student_qual_update_time($qualID, $studentID);
        if($forceLoad || !$forceLoad && $studentUpdateTime > $sessionStartTime)
        {
            //so its been updated since we last loaded it, force it to load again. 
            $qualification = null;
        }
    }
    else
    {
        $qualObject->sessionStartTime = time();
        $studentQualArray[$qualID] = $qualObject;
        $sessionQuals[$studentID] = $sessionQuals[$studentID];
    }
}
else
{
    $qualObject->sessionStartTime = time();
    $qualArray = array();
    $qualArray[$qualID] = $qualObject;
    $sessionQuals[$studentID] = $qualArray;
}

$url = '/blocks/bcgt/forms/print_grid.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('printgrid', 'block_bcgt'));
$PAGE->set_heading(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php','title');
$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELALL;
$loadParams->loadAward = true;
if(!$qualification && $qualID != -1)
{
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELALL;
    $loadParams->loadAward = true;
    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
    $qualification->load_student_information($studentID, $loadParams);
}

 if ($qualification && $unitID > 0){
    
    $unit = $qualification->get_single_unit($unitID);
    if ($unit){
        
        $unit->print_grid($qualID);
        
    }
    
}
//elseif($unitID > 0)
//{
//    $unit = UNIT::get_unit_class_id($unitID, $loadParams);
//    if ($unit){
//        
//        $unit->print_grid($qualID); 
//    }
//}
elseif ($studentID > 0) 
{
    
    $qualification->print_grid();
    
}
else
{
    $qualification->print_class_grid();
}

echo "<script type='text/javascript'>
window.document.close();
window.focus();
window.print();
</script>";