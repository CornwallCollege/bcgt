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

$courseID = required_param('cID', PARAM_INT);
$unitID = required_param('uID', PARAM_INT);
$cmID = optional_param('cmID', -1, PARAM_INT);


global $CFG;
require_cg();

//get the unit grid. 

$selection = get_cg_activity_unit_table($cmID, $unitID, $courseID, true, true);

$output = array(
    "unit" => $unitID,
    "cmid" => $cmID,
    "course" => $courseID,
    "retval" => $selection
);
        
echo json_encode( $output );

