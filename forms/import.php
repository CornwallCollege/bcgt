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
$report = '';
require_login();
if($cID != -1)
{
    $context = context_course::instance($cID);
}
else
{
    $context = context_course::instance($COURSE->id);
}

$PAGE->set_context($context);
$import = new Import($a, null);
$import->check_capability($cID);
$valid = true;
$error = '';
if(isset($_POST['import']) || isset($_POST['importcalc']) 
        || isset($_POST['importcalcfromserver']) || isset($_GET['server']))
{
    $server = false;
    if(isset($_POST['importcalcfromserver']) || isset($_GET['server']))
    {
        $server = true;
    }
    $validation = $import->validate($server);
    if($validation)
    {
        $process = false;
        $import->get_submitted_import_options();
        if(isset($_POST['importcalc']))
        {
            //then we want to calculate average gcse scores etc
            $process = true;
        }
        //now need to upload the file. 
        $import->set_file($server);
        $report = $import->process_import($process, $server);
    }
    else
    {
        $valid = false;
        $error = $import->get_message();
    }
    
}

$url = '/blocks/bcgt/forms/import.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('import', 'block_bcgt'));
$PAGE->set_heading(get_string('import', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('import', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
$PAGE->navbar->add(get_string('admin', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string('import', 'block_bcgt'),'','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initimport', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();
$out .= $import->get_header();
$out .= '<div id="importBCGT" class="bcgt_div_container">';
$out .= $import->get_tabs($cID);
$out .= '<div id="importWrapper">';
if((isset($_POST['import']) || isset($_POST['importcalc'])))
{
    $class = 'importfail';
    if($valid && $import->was_success())
    {
        $class='importsuccess';
    }
    $out .= '<div id="importsummary" class="'.$class.'">';
    $out .= '<h3>Summary</h3>';
    if(!$valid)
    {
        $out .= $error;
    }
    $out .= $import->display_summary();
    $out .= '</div>';
}
$out .= html_writer::start_tag('div', array('class'=>'bcgt_import_controls', 
    'id'=>'importContainer'));
$out .= $import->get_description();

$out .= '<form name="" id="importform" method="POST" action="#" enctype="multipart/form-data">';
$out .= '<input type="hidden" name="a" value="'.$a.'"/>';
$out .= '<h2>'.get_string('choosefile', 'block_bcgt').'</h2>';
$out .= $import->display_import_options();
$out .= $import->display_file_options();
$out .= '</form>';

$out .= html_writer::end_tag('div');//end main column

$out .= html_writer::start_tag('div', array('class'=>'bcgt_import_controls', 
    'id'=>'importValues'));
$out .= $import->get_import_values();
$out .= '</div>';
$out .= html_writer::end_tag('div');//

$out .= '</div>';
$out .= $OUTPUT->footer();

echo $out;
?>
