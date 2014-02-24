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
$groupingID = optional_param('grID', -1, PARAM_INT);
$edit = optional_param('edit', false, PARAM_BOOL);
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
$studentID = optional_param('sID', -1, PARAM_INT);
$firstname = '';
$lastname = '';
$user = $DB->get_record_sql("SELECT * FROM {user} WHERE id = ?", array($studentID));
if($user)
{
    $firstname = $user->firstname;
    $lastname = $user->lastname; 
}
$url = '/blocks/bcgt/grids/ass_grid.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('bcgtstudentassgrid', 'block_bcgt'));
$PAGE->set_heading(get_string('bcgtstudentassgrid', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('bcgtstudentassgrid', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?&cID='.$courseID,'title');
$link1 = null;
if(has_capability('block/bcgt:viewclassgrids', $context))
{
    $link1 = $CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?&cID='.$courseID;
}
$PAGE->navbar->add(get_string('grids', 'block_bcgt'),$link1,'title');
$PAGE->navbar->add(get_string('fas', 'block_bcgt'),null,'title');
$PAGE->navbar->add($firstname.' '.$lastname,null,'title');
$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initgridstu', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$studentInd = $DB->get_record_sql('SELECT * FROM {user} WHERE id = ?', array($studentID));
$out = $OUTPUT->header();
    $out .= '<form id="assGridForm" method="POST" name="assGridForm" action="ass_grid.php?">';			
    $out .= '<input type="hidden" name="cID" value="'.$courseID.'"/>';
    $out .= '<input type="hidden" name="sID" value="'.$studentID.'"/>';
    $heading = get_string('trackinggrid','block_bcgt');
	if($studentInd)
	{
		$heading .= " - $studentInd->username : $studentInd->firstname $studentInd->lastname";
	}
    $out .= html_writer::tag('h2', $heading, 
        array('class'=>'formheading'));
    
    $out .= html_writer::start_tag('div', array('class'=>'bcgt_grid_outer', 
    'id'=>'assGridOuter'));
    $out .= html_writer::tag('h3', get_string('studentfas', 'block_bcgt'), 
        array('class'=>'subTitle'));
    //at this point we load it up into the session
    $string = 'edit';
    if($editing)
    {
        $string = 'view';
    }
    if(has_capability('block/bcgt:editstudentgrid', $context))
    {	
        $out .="<div class='bcgtgridbuttons'>";
        $out .= "<input type='submit' id='edit' class='editsimple' name='$string' value='".get_string($string)."'/>";
        if($editing)
        { 
            $out .= "<input type='submit' id='save' class='gridsave' name='save' value='".get_string("save", "block_bcgt")."'/>";
        }
        $out .="</div><br clear='all'><br />";
    }
    $project = new Project();
    $out .= $project->display_student_assessments($studentID, $editing, $save, -1, 's');
    $out .= html_writer::end_tag('div');
    $out .= '</form>';
    
$out .= $OUTPUT->footer();
echo $out;

?>