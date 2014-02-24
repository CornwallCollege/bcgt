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
$pID = optional_param('pID', -1, PARAM_INT);
$dID = optional_param('dID', -1, PARAM_INT);
$currentID = optional_param('currentID', -1, PARAM_INT);
if($dID != -1)
{
    $project = new Project($dID);
    if($project)
    {
        $project->delete_project();
    }
}
if($currentID != -1)
{
    $project = new Project($pID, null);
    if($project)
    {
        $project->mark_as_current();
    }
}
$url = '/blocks/bcgt/forms/activities.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('viewactivitylinks', 'block_bcgt'));
$PAGE->set_heading(get_string('viewactivitylinks', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('activity', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
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
$out .= html_writer::start_tag('div', array('class'=>'bcgt_activity_controls', 
    'id'=>'editCourseQual'));
$out .= '<form name="assessments" method="POST" action=""/>';
$out .= '<input type="hidden" name="cID" value="'.$cID.'"/>';
//load up all of the formal assessments already been created. 
$out .= '<div id="projectTileContainer">';
$projects = Project::get_all_projects(true);
if($projects)
{
    foreach($projects AS $project)
    {
        $currentProject = $project->get_is_project_current();
        $out .= '<div class="projectTile">';   
        
        $out .= '<h2>'.$project->get_name().'</h2>';
        $out .= get_string('details', 'block_bcgt').' : '.
                $project->get_details().'<br />';
        $out .= get_string('date', 'block_bcgt').' : '.
                $project->get_date().'<br />';
        $out .= get_string('qualcount', 'block_bcgt').' :'.
                count($project->get_qual_ids()).'<br />';
//        $out .= 'click to see summary';
        //no of quals its on
        //qual types its on
        //no of submissions
        //avg grade
        //avg target grade
        //avg difference
            $out .= '<div class="bcgtProjectIconBar">'; // icon bar
            
                $out .= '<span id="delete"> <a href="assessments.php?dID='.
                        $project->get_id().'&cID='.$cID.'" title="'.get_string('delete').'">'.
                        '<img class="delete" src="'.$CFG->wwwroot.'/blocks/bcgt/pix/remove.png" height="20" width="20" alt="'.get_string('delete', 'block_bcgt').
                        '" /></a></span>'.
                        '<span id="edit"> <a href="edit_project.php?pID='.
                        $project->get_id().'&cID='.$cID.'&cM=1&awarded=1" title="'.get_string('edit', 'block_bcgt').'">'.
                        '<img class="" src="'.$CFG->wwwroot.'/blocks/bcgt/pix/edit.png" height="20" width="20" alt="'.get_string('edit', 'block_bcgt').
                        '" /></a></span>'.
                        '</a></span>';
                if(!$currentProject)
                {
                    $out .= '<span id="markcurrent">'.
                            ' <a href="assessments.php?currentID='.$project->get_id().'&cID='.$cID.'&pID='.$project->get_id().
                            '" title="'.get_string('markcurrent', 'block_bcgt').'">'.
                                '<img class="" src="'.$CFG->wwwroot.'/blocks/bcgt/pix/inactive.png" height="20" width="20" alt="'.get_string('markcurrent', 'block_bcgt').
                        '" /></a></span>';
                }
                else {
                        $out .= '<span id="current">'.
                                ' <a href="assessments.php?currentID='.$project->get_id().'&cID='.$cID.'&pID='.$project->get_id().
                                '" title="'.get_string('currentproject', 'block_bcgt').'">'.
                                '<img class="" src="'.$CFG->wwwroot.'/blocks/bcgt/pix/active.png" height="20" width="20" alt="'.get_string('currentproject', 'block_bcgt').
                        '" /></a></span>';
                }
            $out .= '</div>'; // icon bar
        $out .= '</div>';
    }
}
$out .= '</div>';
//ability to add a new project/formal assessment
$out .= '<div>';
//link to add a new formal assessment
$out .= '<a href="edit_project.php?cID='.$cID.'&cM=1&awarded=1"/>Add a new Formal Assessment</a>';
$out .= '</div>';
$out .= '</form>';
$out .= html_writer::end_tag('div');//end main column
$out .= $OUTPUT->footer();

echo $out;
?>
