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
$courseID = optional_param('cID', -1, PARAM_INT);
if($courseID != -1)
{
    $context = context_course::instance($courseID);
}
else
{
    $context = context_course::instance($COURSE->id);
}
require_login();
$PAGE->set_context($context);
require_capability('block/bcgt:viewowngrid', $context);
$grid = optional_param('g', 's', PARAM_TEXT);
//TODO take into account COURSE
$studentRole = $DB->get_record_sql('SELECT * FROM {role} WHERE shortname = ? ', array('student'));
$userID = $USER->id;
$trackingSheets = get_users_quals($userID, $studentRole->id);
if($trackingSheets && count($trackingSheets) == 1)
{
    $qualification = end($trackingSheets);
    redirect('student_grid.php?sID='.$userID.'&qID='.$qualification->id.'&cID='.$courseID);
}


$url = '/blocks/bcgt/grids/my_grids.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('mytrackingsheet', 'block_bcgt'));
$PAGE->set_heading(get_string('mytrackingsheet', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('gridselect', 'block_bcgt'));
if($courseID != 1)
{
    global $DB;
    $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($courseID));
    if($course)
    {
        $PAGE->navbar->add($course->shortname,$CFG->wwwroot.'/course/view.php?id='.$courseID,'title');
    }
}
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
$PAGE->navbar->add(get_string('mytrackingsheet', 'block_bcgt'),null,'title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initmygrid', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();

$out .= html_writer::tag('h2', get_string('mytrackingsheet','block_bcgt').
        '', 
        array('class'=>'formheading'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_controls', 
    'id'=>'mytrackingsheet'));
$out .= '<form name="mygrid" action="my_grids.php" method="POST" id="gridselect">';
$out .= '<input type="hidden" name="g" value="'.$grid.'"/>';
$out .= '<input type="hidden" name="cID" value="'.$courseID.'"/>';
$out .= '<table>';
foreach($trackingSheets AS $qualification)
{
    $out .= '<tr><td>'.bcgt_get_qualification_display_name($qualification, true, ' ').
            '</td><td><a href="student_grid.php?sID='.$userID.'&qID='.$qualification->id.'">View Grid</a></td></tr>';
}
$out .= '</table>';


$out .= '</form>';
$out .= html_writer::end_tag('div');//end main column
$out .= $OUTPUT->footer();

echo $out;
?>
