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
require_cg();
require_login();
$PAGE->set_context($context);

$flag = optional_param('f', '', PARAM_TEXT);
$unitID = required_param('uID', PARAM_INT);
$qualID = required_param('qID', PARAM_INT);
$grid = optional_param('g', 's', PARAM_TEXT);
$studentView = optional_param('v', true, PARAM_BOOL);
$courseID = optional_param('cID', -1, PARAM_INT);
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

$sessionUnits = isset($_SESSION['session_unit'])? 
    unserialize(urldecode($_SESSION['session_unit'])) : array();
$unit = null;
if(array_key_exists($unitID, $sessionUnits))
{
    $unitObject = $sessionUnits[$unitID];
    $unit = $unitObject->unit;
}
if($unit)
{
    //this comes back as an object
    //there is a multidimentional array of rows and columns
    $unit->set_student_flag($flag);
    $data = $unit->get_unit_grid_data($qualID, $advancedMode, $editing, $courseID);
    $output = array(
		"iTotalRecords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);
	echo json_encode( $output );
}
else
{
    echo "No Unit Found";
}
//Call a function get student grid data
