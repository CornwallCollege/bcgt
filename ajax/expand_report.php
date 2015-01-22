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

$value = optional_param('val',0, PARAM_INT);
$type = optional_param('type', '', PARAM_TEXT);
$courseID = optional_param('cid',SITEID,PARAM_INT);
$showHTML = optional_param('html', false, PARAM_BOOL);
set_time_limit(0);

$alps = new Alps();
$display = $alps->get_alps_report_rows($type, $value, $courseID);

$output = array(
    "type"=>$type,
    "val"=>$value,
    "display"=>$display
);
if($showHTML)
{
    echo $display;
}
else
{
    echo json_encode( $output );
}
?>

