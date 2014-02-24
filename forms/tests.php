<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
global $COURSE, $CFG, $PAGE, $OUTPUT, $DB;
require_once('../../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$courseID = optional_param('cID', -1, PARAM_INT);
$view = optional_param('view', '', PARAM_TEXT);
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
require_capability('block/bcgt:calculatetargetgrades', $context);
$url = '/blocks/bcgt/forms/calculate_user_values.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('calculatetargetgrade', 'block_bcgt'));
$PAGE->set_heading(get_string('calculatetargetgrade', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class('calculatetargetgrade');
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
$PAGE->navbar->add(get_string('myDashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string('unittests', 'block_bcgt'));
$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initunittests', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript(true);
$out = $OUTPUT->header();
$out .= '<h2 class="bcgt_form_heading">'.get_string('unittests', 'block_bcgt').'</h2>';
$out .= '<p>'.get_string('unittests','block_bcgt').'</p>';
	$out .= "<div id='bcgtUnitSystemTests' class='bcgt_admin_controls'>";
        $out .= "<form method='POST' name='unittests' id='unittests' action='tests.php'>";
        if($view != '')
        {
            $unitTests = new UnitTests($view);
            $out .= $unitTests->get_unit_test($view);
            $unitTests->process_test($view);
            $out .= $unitTests->get_unit_test_result($view);
        }
        else
        {
            $unitTests = new UnitTests('');
            $out .= $unitTests->get_unit_tests_list();
        }
        $out .= "</form>";
    $out .= "</div>";
$out .= $OUTPUT->footer();

echo $out;
?>
