<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

global $COURSE, $CFG, $PAGE, $OUTPUT;
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

$familyID = required_param('fID', PARAM_INT);
$PAGE->set_context($context);
require_capability('block/bcgt:searchquals', $context);
$tab = optional_param('page', 1, PARAM_INTEGER);
$url = '/blocks/bcgt/forms/my_dashboard.php';
$PAGE->set_url($url, array('page' => $tab));
$PAGE->set_title(get_string('selectqual', 'block_bcgt'));
$PAGE->set_heading(get_string('selectqual', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
//$PAGE->navbar->add(get_string('bcgtmydashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add('',$url.'?page='.$tab,'title');
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();

echo $OUTPUT->header();

//this needs to be changed to get the qual_settings page of the correct plugin. 

//a function to get the plugin folder by the familyid ergo plugin name

$pluginName = get_plugin_name($familyID);
if($pluginName)
{
   require_once($CFG->dirroot.'/blocks/bcgt/plugins/'.$pluginName.'/forms/qual_settings.php'); 
}

echo $OUTPUT->footer();
?>
