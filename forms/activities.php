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
$tab = optional_param('tab', '', PARAM_TEXT);
$view = optional_param('view', 'os', PARAM_TEXT);
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
require_capability('block/bcgt:viewactivitylinks', $context);

$url = '/blocks/bcgt/forms/activities.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('viewactivitylinks', 'block_bcgt'));
$PAGE->set_heading(get_string('viewactivitylinks', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('activity', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
if($cID != -1)
{
    $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($cID));
    if($course)
    {
        $PAGE->navbar->add($course->shortname,$CFG->wwwroot.'/course/view.php?id='.$cID,'title');
    }
    
}
$PAGE->navbar->add(get_string('viewactivitylinks', 'block_bcgt'),'','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initactivities', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();

$out .= html_writer::tag('h2', get_string('viewactivitylinks','block_bcgt').
        '', 
        array('class'=>'formheading'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_activity_controls bcgt_div_container', 
    'id'=>'editCourseQual'));

$out.= '<div class="tabs"><div class="tabtree">';
$out.= '<ul class="tabrow0">';
if(has_capability('block/bcgt:manageactivitylinks', $context))
{
    if($tab == '')
    {
        $tab = 'acheck';
    }
    $focus = ($tab == 'acheck')? 'focus' : '';
    $out.= '<li class="last '.$focus.'">'.
        '<a href="?tab=acheck&cID='.$cID.'">'.
        '<span>'.get_string('activitycheck', 'block_bcgt').'</span></a></li>';
}
if($tab == '')
{
    $tab = 'act';
}
$focus = ($tab == 'act')? 'focus' : '';
$out.= '<li class="first '.$focus.'">'.
        '<a href="?tab=act&cID='.$cID.'">'.
        '<span>'.get_string('activitiesbyactivity', 'block_bcgt').'</span></a></li>';
$focus = ($tab == 'unit')? 'focus' : '';
$out.= '<li class="last '.$focus.'">'.
        '<a href="?tab=unit&cID='.$cID.'">'.
        '<span>'.get_string('activitiesbyunit', 'block_bcgt').'</span></a></li>';

//if(has_capability('block/bcgt:viewclassgrids', $context))
//{
//    $out.= '<li class="last">'.
//            '<a href="?tab=actgrid&cID='.$cID.'">'.
//            '<span>'.get_string('activitygrid', 'block_bcgt').'</span></a></li>';
//}
//$out.= '<li class="last">'.
//        '<a href="?tab=actcal&cID='.$cID.'">'.
//        '<span>'.get_string('activitycalendarview', 'block_bcgt').'</span></a></li>';
$out.= '</ul>';

$out .= '<br>';

$out .= bcgt_get_grid_assignment_overview_buttons($view, $cID);

$out.= '</div></div>';
if($tab == 'actcal')
{
    $out .= '<p>This will show a calendar view of all assignments, 
        units and criterias</p>';
}
elseif($tab == 'acheck')
{
    //get quals on course
    $includeFamilies = array('BTEC', 'CG');
    //get all of the qual families that are on this course
    $families = get_course_qual_families($cID, $includeFamilies);
    if($families)
    {
        foreach($families AS $family)
        {
            require_once($CFG->dirroot.$family->classfolderlocation.'/'.str_replace(' ', '', $family->family).'Qualification.class.php');
            $class = str_replace(' ', '', $family->family).'Qualification';
            $out.= $class::gradebook_check_page($cID);
            $out .= '<br><br>';
        }
    } 
}
elseif($tab == 'actgrid')
{
    //then we are showing the grade tracker!
    //get all of the qual families that are on this course
//    $families = get_course_qual_families($cID);
//    if($families)
//    {
//        //for each family get the parent family
//        //then get the activity_view_page
//        foreach($families AS $family)
//        {
//            require_once($CFG->dirroot.$family->classfolderlocation.'/'.str_replace(' ', '', $family->type).'Qualification.class.php');
//            //$class = $family->type.'Qualification';
//            $class = str_replace(' ', '', $family->type).'Qualification';
//            $out.= $class::activity_grade_tracker($cID);
//        }
//    }
    $out.= Qualification::activity_grade_tracker($cID);
}
else
{
    $includeFamilies = array('BTEC', 'CG');
    //get all of the qual families that are on this course
    $families = get_course_qual_families($cID, $includeFamilies);
    if($families)
    {
        //for each family get the parent family
        //then get the activity_view_page
        foreach($families AS $family)
        {
            require_once($CFG->dirroot.$family->classfolderlocation.'/'.str_replace(' ', '', $family->family).'Qualification.class.php');
            //$class = $family->type.'Qualification';
            $class = str_replace(' ', '', $family->family).'Qualification';
            $out.= $class::activity_view_page($cID, $tab);
        }
    }
}



$out .= html_writer::end_tag('div');//end main column
$out .= $OUTPUT->footer();

echo $out;
?>
