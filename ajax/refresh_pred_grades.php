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
$minUcas = 'N/A';
if($award && $award->minAward)
{
    $minAward = $award->minAward->get_award();
    $minUcas = $award->minAward->get_ucasPoints();
}
$maxAward = 'N/A';
$maxUcas = 'N/A';
if($award && $award->maxAward)
{
    $maxAward = $award->maxAward->get_award();
    $maxUcas = $award->maxAward->get_ucasPoints();
}
$avgAward = 'N/A';
$avgUcas = 'N/A';
if($award && $award->averageAward)
{
    $avgAward = $award->averageAward->get_award();
    $avgUcas = $award->averageAward->get_ucasPoints();
}
$output = array(
		"mingrade" => $minAward,
        "minucas" => $minUcas,
		"maxgrade" => $maxAward,
        "maxucas" => $maxUcas,
        "avggrade" => $avgAward,
        "avgucas" => $avgUcas,
	);
	echo json_encode( $output );

?>
