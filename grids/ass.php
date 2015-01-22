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
$qID = optional_param('qID', -1, PARAM_INT);
$sID = optional_param('sID', -1, PARAM_INT);
$pID = optional_param('pID', -1, PARAM_INT);
$view = optional_param('v', 'q', PARAM_TEXT);
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

$firstname = '';
$lastname = '';
$assessLink = null;
if($sID != -1)
{
    $user = $DB->get_record_sql("SELECT * FROM {user} WHERE id = ?", array($sID));
    if($user)
    {
        $firstname = $user->firstname;
        $lastname = $user->lastname; 
    }
    $heading = $firstname.' '.$lastname;
    $assessLink = $CFG->wwwroot.'/blocks/bcgt/grids/ass_grid.php?sID='.$sID.'&cID='.$courseID;
}
elseif($qID != -1)
{
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
    $qualification = Qualification::get_qualification_class_id($qID, $loadParams);
    $heading = $qualification->get_display_name(false);
    $assessLink = $CFG->wwwroot.'/blocks/bcgt/grids/ass_grid_class.php?qID='.$qID.'&cID='.$courseID;
}
$project = new Project($pID);
$projectName = $project->get_name();
$projectDate = $project->get_date();
$url = '/blocks/bcgt/grids/ass.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('assessmenttracker', 'block_bcgt'));
$PAGE->set_heading(get_string('bcgtassessment', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('bcgtassessment', 'block_bcgt'));
if($courseID != 1)
{
    global $DB;
    $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($courseID));
    if($course)
    {
        $PAGE->navbar->add($course->shortname,$CFG->wwwroot.'/course/view.php?id='.$courseID,'title');
    }
}
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?&cID='.$courseID,'title');

$link1 = null;
if(has_capability('block/bcgt:viewclassgrids', $context))
{
    $link1 = $CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?&cID='.$courseID;
}
$PAGE->navbar->add(get_string('grids', 'block_bcgt'),$link1,'title');
$PAGE->navbar->add($heading.' '.get_string('fas', 'block_bcgt'),$assessLink,'title');
$PAGE->navbar->add($projectName.' '.$projectDate,null,'title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initgridfaclass', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();
    $out .= '<form id="assGridForm" method="POST" name="assGridForm" action="ass.php?">';			
    $out .= '<input type="hidden" name="cID" value="'.$courseID.'"/>';
    $out .= '<input type="hidden" name="sID" value="'.$sID.'"/>';
    $out .= '<input type="hidden" name="pID" value="'.$pID.'"/>';
    $out .= '<input type="hidden" name="qID" value="'.$qID.'"/>';
    $out .= '<input type="hidden" name="grID" value="'.$groupingID.'"/>';
    $out .= '<input type="hidden" name="v" value="'.$view.'"/>';
    $out .= html_writer::tag('h2', $heading.' - '.$projectName.' '.$projectDate, 
        array('class'=>'formheading'));
    
    $out .= html_writer::start_tag('div', array('class'=>'bcgt_grid_outer', 
    'id'=>'assGridOuter'));
    $out .= html_writer::tag('h3', 'Assessment', 
        array('class'=>'subTitle'));
    //at this point we load it up into the session
    $string = 'edit';
    if($editing)
    {
        $string = 'view';
    }
    if(has_capability('block/bcgt:editstudentgrid', $context))
    {	
        $out .= "<div class='bcgtgridbuttons'>";
        $out .= "<input type='submit' id='edit' class='editsimple' name='$string' value='".get_string($string)."'/>";
        if($editing)
        { 
            $out .= "<input type='submit' id='save' class='gridsave' name='save' value='".get_string("save", "block_bcgt")."'/>";
        }
        $out .= "</div><br clear='all' /><br />";
    }
    //$out .= Project::display_student_assessments($studentID, $editing, $save);
    
    if($view == 'q' || $view == 'qg')
    {
        //all students on one qual
        $out .= $qualification->display_qual_assessments($editing, $save, $pID, $view, $groupingID);
    }
    elseif($view == 's' || $view == 'sg')
    {
        //one student and all of their quals
        $out .= $project->display_student_assessments($sID, $editing, $save, $pID, $view, $qID);
    }
    
    $out .= html_writer::end_tag('div');
    $out .= '</form>';
    
$out .= $OUTPUT->footer();
echo $out;

?>
