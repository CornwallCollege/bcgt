<?php
// lol


/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once('../../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$courseID = optional_param('cID', -1, PARAM_INT);
$edit = optional_param('edit', false, PARAM_BOOL);
$groupingID = optional_param('grID', -1, PARAM_INT);
$editing = false;
$save = false;
if(isset($_POST['edit']) || $edit)
{
    $editing = true;
}
if(isset($_POST['save']))
{
    $save = true;
}
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
$qualID = optional_param('qID', -1, PARAM_INT);

$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELUNITS;
$qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
$longName = $qualification->get_display_name();
$shortName = $qualification->get_display_name(false);
$url = '/blocks/bcgt/grids/ass_grid_class.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('bcgtassessment', 'block_bcgt'));
$PAGE->set_heading(get_string('bcgtassessment', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?&cID='.$courseID,'title');

$link1 = null;
if(has_capability('block/bcgt:viewclassgrids', $context))
{
    $link1 = $CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=a&cID='.$courseID;
}
$PAGE->navbar->add(get_string('grids', 'block_bcgt'),$link1,'title');
$PAGE->navbar->add($shortName.' '.get_string('fas', 'block_bcgt'),null,'title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initgridstu', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();
    $out .= '<form id="assGridClassForm" method="POST" name="assGridClassForm" action="ass_grid_class.php?">';			
    $out .= '<input type="hidden" name="cID" value="'.$courseID.'"/>';
    $out .= '<input type="hidden" name="qID" value="'.$qualID.'"/>';
    $out .= '<input type="hidden" name="grID" value="'.$groupingID.'"/>';
    
    $out .= html_writer::tag('h2', $longName, 
        array('class'=>'formheading'));
    
    $out .= html_writer::start_tag('div', array('class'=>'bcgt_grid_outer', 
    'id'=>'assGridOuter'));
    $out .= html_writer::tag('h3', '', 
        array('class'=>'subTitle'));
    //at this point we load it up into the session
    $string = 'edit';
    if($editing)
    {
        $string = 'view';
    }
    if(has_capability('block/bcgt:editunitgrid', $context))
    {	
        $out .="<div class='bcgtgridbuttons'>";
        $out .= "<input type='submit' id='edit' class='editsimple' name='$string' value='".get_string($string)."' />";
        if($editing)
        { 
            $out .= "<input type='submit' id='save' class='gridsave' name='save' value='".get_string("save", "block_bcgt")."'/>";
        }
        $out .="</div><br clear='all'><br />";
    }
    $out .= $qualification->display_qual_assessments($editing, $save, -1, 'q', $groupingID);
    $out .= html_writer::end_tag('div');
    $out .= '</form>';
    
$out .= $OUTPUT->footer();
echo $out;

?>