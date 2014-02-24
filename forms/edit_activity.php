<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;;
require_once('../../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

$cID = optional_param('cID', -1, PARAM_INT);
if($cID != -1)
{
    $context = context_course::instance($cID);
}
else
{
    $context = context_course::instance($COURSE->id);
}
require_login();
$PAGE->set_context($context);
require_capability('block/bcgt:manageactivitylinks', $context);
$uID = optional_param('uID', -1, PARAM_INT);
$fID = optional_param('fID', -1, PARAM_INT);
$aID = optional_param('aID', -1, PARAM_INT);


$url = '/blocks/bcgt/forms/add_activity.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('addactivitylinks', 'block_bcgt'));
$PAGE->set_heading(get_string('addactivitylinks', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('gridselect', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'','title');
$PAGE->navbar->add(get_string('addactivitylinks', 'block_bcgt'),'','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.addactivities', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();

$out .= html_writer::tag('h2', get_string('addactivitylinks','block_bcgt').
        '', 
        array('class'=>'formheading'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_activity_controls', 
    'id'=>'editCourseQual'));
$out .= '<form name="addActivity" action"#" method="POST" id="addActivity"/>';
//get all of the qual families that are on this course
$families = get_course_qual_families($cID);
if($families)
{
    //for each family get the parent family
    //then get the activity_view_page
    foreach($families AS $family)
    {
        require_once($CFG->dirroot.$family->classfolderlocation.'/'.$family->type.'Qualification.class.php');
        $class = $family->type.'Qualification';
        $out.= $class::edit_activity_view_page($cID, $uID, $aID);
    }
}


$out .= html_writer::end_tag('div');//end main column
$out .= $OUTPUT->footer();

echo $out;
?>
