<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once('../../../config.php');
global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$context = context_course::instance($COURSE->id);
require_login();
$PAGE->set_context($context);

$qualID = required_param('qID', PARAM_INT);
$studentID = required_param('sID', PARAM_INT);
$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELALL;
$qualification = Qualification::get_qualification_class_id($qualID,$loadParams);
$award = null;
if($qualification)
{
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELALL;
    $loadParams->loadAward = true;
    $qualification->load_student_information($studentID,
            $loadParams);
    $award = $qualification->calculate_predicted_grade();
}
$minAward = 'N/A';
if($award && $award->minAward)
{
    $minAward = $award->minAward->get_award();
}
$maxAward = 'N/A';
if($award && $award->maxAward)
{
    $maxAward = $award->maxAward->get_award();
}
$avgAward = 'N/A';
if($award && $award->averageAward)
{
    $avgAward = $award->averageAward->get_award();
}
$output = array(
		"mingrade" => $minAward,
		"maxgrade" => $maxAward,
        "avggrade" => $avgAward,
	);
	echo json_encode( $output );

?>
