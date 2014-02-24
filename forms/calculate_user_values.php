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
$action = optional_param('a', '', PARAM_TEXT);
$courseID = optional_param('cID', -1, PARAM_INT);
//$userSearch = optional_param('usersearch', '', PARAM_TEXT);
//$courseSearch = optional_param('coursesearch', '', PARAM_TEXT);
//$qualSearch = optional_param('qualsearch', '', PARAM_TEXT);
//$tgCheck = optional_param('targetgrade', false, PARAM_BOOL);
//$avgCheck = optional_param('avgscore', false, PARAM_BOOL);
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
$PAGE->navbar->add(get_string('calculatetargetgrade', 'block_bcgt'));
$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initcalctargetgrades', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript(true);
$out = $OUTPUT->header();
$userCalculations = new UserCalculations($action);
$out .= '<h2 class="bcgt_form_heading">'.$userCalculations->get_header().'</h2>';
$userCalculations->process_calculation();

$out .= $userCalculations->get_tabs();
//get the desc
$out .= $userCalculations->get_description();

	$out .= "<div id='bcgtCalcTargetGrades' class='bcgt_admin_controls'>";
		$out .= "<form method='POST' name='calctargetgrades' id='calctargetgrades' action='calculate_user_values.php'>";
            $out .= '<input type="submit" name="calculateall" value="'.get_string('calculateall', 'block_bcgt').'"/>';
            $out .= '<input type="hidden" name="cID" value="'.$courseID.'"/>';
            $out .= '<input type="hidden" name="a" value="'.$action.'"/>';
            $out .= $userCalculations->get_calculation_form();
            $out .= '<input type="submit" name="search" value="'.get_string('search', 'block_bcgt').'"/>';
            $out .= '<input type="submit" name="calculate" value="'.get_string('calculatesel', 'block_bcgt').'"/>';
            
        $out .= "</form>";
    $out .= "</div>";
$out .= $OUTPUT->footer();

echo $out;
?>
