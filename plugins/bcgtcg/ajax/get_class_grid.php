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


//header("Content-Type: text/xml");
require_once('../../../../../config.php');

global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$context = context_course::instance($COURSE->id);
require_login();
$PAGE->set_context($context);

$qualID = required_param('qID', PARAM_INT);
$grid = optional_param('g', 's', PARAM_TEXT);
//need to send down courseid and group id
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
require_cg();
$sessionQuals = isset($_SESSION['session_class_quals'])? 
    unserialize(urldecode($_SESSION['session_class_quals'])) : array();
$qualification = null;
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

if($qualification)
{
    $data = $qualification->get_class_grid_data($advancedMode, 
            $editing);
//    $output = array(
//		"iTotalRecords" => count($data),
//		"iTotalDisplayRecords" => count($data),
//		"aaData" => $data
//	);
//	echo json_encode( $output );
    
    echo $data;
    
}
else {
    echo "No Qualification Found";
}
//Call a function get student grid data
