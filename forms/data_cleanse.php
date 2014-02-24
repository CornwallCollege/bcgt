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
$a = optional_param('a', '', PARAM_TEXT);
if($cID != -1)
{
    $context = context_course::instance($cID);
}
else
{
    $context = context_course::instance($COURSE->id);
}
//$report = '';
require_login();
$PAGE->set_context($context);
$data = new Data($a, null);
require_capability('block/bcgt:rundatacleanse', $context);
if(isset($_POST['runcleanse']))
{
    $data->run_cleanse();
}
$url = '/blocks/bcgt/forms/data_cleanse.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('datacleanse', 'block_bcgt'));
$PAGE->set_heading(get_string('datacleanse', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('datacleanse', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
$PAGE->navbar->add(get_string('admin', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string('datacleanse', 'block_bcgt'),'','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initdatacleanse', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();
$out .= $data->get_header();
$out .= '<div id="dataCleanseBCGT">';
$out .= $data->get_tabs($cID);
$out .= '<div id="dataCleanseWrapper">';
$out .= html_writer::start_tag('div', array('class'=>'bcgt_data_cleanse_controls', 
    'id'=>'dataCleanseContainer'));
$out .= $data->get_description();

$out .= '<form name="" id="importform" method="POST" action="#" enctype="multipart/form-data">';
$out .= $data->display_data_check();
if($a != '')
{
    $out .= '<h2>'.get_string('rundatacleanse', 'block_bcgt').'</h2>';
    $out .= '<input type="submit" name="runcleanse" value="'.get_String('runcleanse', 'block_bcgt').'"/>';
    $out .= $data->display_final_summary();
}

$out .= '</form>';

$out .= html_writer::end_tag('div');//end main column
$out .= html_writer::end_tag('div');//

$out .= '</div>';
$out .= $OUTPUT->footer();

echo $out;
?>
