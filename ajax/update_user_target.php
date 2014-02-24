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
$value = required_param('value', PARAM_INT);
$type = required_param('type', PARAM_TEXT);
$userID = required_param('sID', PARAM_INT);
$courseID = required_param('cID', PARAM_INT);
$uFilter = optional_param('ufilter', 'all', PARAM_TEXT);
$tFilter = optional_param('tfilter', 'all', PARAM_TEXT);
$sort = optional_param('sort', '', PARAM_TEXT);
$filter = array();
$filter['units'] = $uFilter;
$filter['target'] = $tFilter;
$sortArray = explode(",", $sort);

//get the qualification
//update the students target grade
//get the report
$retval = null;
$qualification = Qualification::get_qualification_class_id($qualID);
if($qualification)
{
    $qualification->update_student_target_grade($userID, $value, $type, $courseID);
    $retval = $qualification->get_simple_qual_report($userID, 's', true, -1, $filter, $sortArray);
}

$output = array(
		"qualid" => $qualID,
		"retval" => $retval,
	);
	echo json_encode( $output );

?>
