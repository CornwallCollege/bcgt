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

$flag = optional_param('f', '', PARAM_TEXT);
$lock = optional_param('lock',false,PARAM_BOOL);
$cmID = optional_param('cmID', -1, PARAM_INT);
$qualID = optional_param('qID', -1, PARAM_INT);
$grid = optional_param('g', 's', PARAM_TEXT);
$studentView = optional_param('v', true, PARAM_BOOL);
$showHTML = optional_param('html', false, PARAM_BOOL);
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




$sessionActs = isset($_SESSION['session_act'])? 
    unserialize(urldecode($_SESSION['session_act'])) : new stdClass();
//this will be an array of studentID => qualarray->qual object->qual
$qualification = null;
$qualArray = $sessionActs->quals;
if(array_key_exists($qualID, $qualArray))
{
    $qualification = $qualArray[$qualID];
}
if (!$qualification)
{
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELALL;
    $loadParams->loadAward = true;
    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);    
}
if($qualification)
{
    $qualification->set_grid_disabled($lock);
    //this comes back as an object
    //there is a multidimentional array of rows and columns
    $qualification->set_student_flag($flag);
    $data = $qualification->get_act_grid_data($advancedMode, 
            $editing);
        
    echo $data;
//    
//    $output = array(
//		"iTotalRecords" => count($data),
//		"iTotalDisplayRecords" => count($data),
//		"aaData" => $data
//	);
//    if($showHTML)
//    {
//        echo bcgt_output_simple_grid_table($data);    
//    }
//    else
//    {
//        echo json_encode( $output );
//    }
	
}
else {
    echo "No Qualification Found";
}
//Call a function get student grid data
