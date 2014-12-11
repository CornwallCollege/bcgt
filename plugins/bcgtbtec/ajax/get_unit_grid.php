<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

//header("Content-Type: text/xml");
require_once('../../../../../config.php');

global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$context = context_course::instance($COURSE->id);
require_login();
$PAGE->set_context($context);

$flag = optional_param('f', '', PARAM_TEXT);
$lock = optional_param('lock', false, PARAM_BOOL);
$unitID = required_param('uID', PARAM_INT);
$qualID = optional_param('qID', -1, PARAM_INT);
$grid = optional_param('g', 's', PARAM_TEXT);
$studentView = optional_param('v', true, PARAM_BOOL);
$courseID = optional_param('cID', -1, PARAM_INT);
$sCourseID = optional_param('scID', -1, PARAM_INT);
$groupingID = optional_param('grID', -1, PARAM_INT);
$showHTML = optional_param('html', false, PARAM_BOOL);
$regGroupID = optional_param('regGrpID', false, PARAM_INT);

$advancedMode = false;
if($grid == 'a' || $grid == 'ae')
{
    $advancedMode = true;
}
$editing = false;
if($grid == 'se' || $grid == 'ae')
{
    $editing = true;
}
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECQualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECUnit.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECCriteria.class.php');
$sessionUnits = isset($_SESSION['session_unit'])? 
    unserialize(urldecode($_SESSION['session_unit'])) : array();
$unit = null;
if(array_key_exists($unitID, $sessionUnits))
{
    $unitObject = $sessionUnits[$unitID];
    $unit = $unitObject->unit;
}
if(!$unit)
{
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELALL;
    $unit = Unit::get_unit_class_id($unitID, $loadParams);
}
if($unit)
{
    $unit->set_grid_disabled($lock);
    //this comes back as an object
    //there is a multidimentional array of rows and columns
    $unit->set_student_flag($flag);
    //above, (e.g. Flat is late to denote if we are showing late)
    if($qualID != -1)
    {
        $data = $unit->get_unit_grid_data($qualID, $advancedMode, $editing, $courseID);
    }
    else
    {
        $data = $unit->get_unit_grid_full_data($advancedMode, $editing, $courseID);
    }
//    $output = array(
//		"iTotalRecords" => count($data),
//		"iTotalDisplayRecords" => count($data),
//		"aaData" => $data
//	);
//	if($showHTML)
//    {
//        echo bcgt_output_simple_grid_table($data);    
//    }
//    else
//    {
//        echo json_encode( $output );
//    }
    
    echo $data;
    
}
else
{
    echo "No Unit Found";
}
//Call a function get student grid data
