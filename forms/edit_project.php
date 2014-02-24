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
$pID = optional_param('pID', -1, PARAM_INT);
$cID = optional_param('cID', -1, PARAM_INT);
$name = optional_param('name', '', PARAM_TEXT);
$details = optional_param('details', '', PARAM_TEXT);
$date = optional_param('date', -1, PARAM_TEXT);
$cM = optional_param('cM', 0, PARAM_INT);
$awarded = optional_param('awarded', 0, PARAM_INT);
$newAddQuals = isset($_POST['addselect']) ? $_POST['addselect'] : array();
$newRemoveQuals = isset($_POST['removeselect']) ? $_POST['removeselect'] : array();
$qualsOnProject = isset($_POST['qualsOnProject']) ? $_POST['qualsOnProject'] : array();
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
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('activity', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
$PAGE->navbar->add(get_string('myDashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string('viewactivitylinks', 'block_bcgt'),'','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);

$PAGE->requires->js_init_call('M.block_bcgt.initprojectquals', null, true, $jsModule);

require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript(true);
$params = new stdClass();
$params->name = $name;
$params->details = $details;
$params->date = $date;
$params->centrallyManaged = $cM;
$params->awarded = $awarded;
$project = new Project($pID, $params);
if(isset($_POST['save']))
{
    //get the quals. 
    //upon save
    //add the project
    //add the quals to the project
    $project->save();
    $project->set_qual_ids($qualsOnProject);
    $project->save_quals();
    if(isset($_POST['current']))
    {
        $project->mark_as_current();
    }
    else
    {
        //was it before?
        if($project->get_is_project_current())
        {
            $project->clear_current_project();
        }
    }
    redirect($CFG->wwwroot.'/blocks/bcgt/forms/assessments.php');
}
if($pID != -1) 
{
    $project->load_project();
    $name = $project->get_name();
    $details = $project->get_details();
    $date = $project->get_date();
}
if(count($qualsOnProject) == 0)
{
    $qualsOnProject = $project->get_qual_ids();
}
$out = $OUTPUT->header();
$out .= '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/blocks/bcgt/css/start/jquery-ui-1.10.3.custom.css">';
$out .= html_writer::tag('h2', get_string('viewactivitylinks','block_bcgt').
        '', 
        array('class'=>'formheading'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_edit_project', 
    'id'=>'bcgt_edit_project'));
$out .= '<form name="project" method="POST" action="" class="mform">';
$out .= '<input type="hidden" name="cID" value="'.$cID.'"/>';
$out .= '<input type="hidden" name="cM" value="'.$cM.'"/>';
$out .= '<input type="hidden" name="awarded" value="'.$awarded.'"/>';
if($date == -1 || $date == 0)
{
    $date = '';
}

// General Form Fields

$out .= '<fieldset class="clearfix" id="general">
            <legend class="ftoggler">General</legend>
		<div class="fcontainer clearfix">';
$out .= '<div id="fitem_id_name" class="fitem required fitem_ftext">'.
        '<div class="fitemtitle"><label>'.get_string('name').' : </label>'.
        '</div><div class="felement ftext"><input type="text" name="name" value="'.$name.'"/></div></div>';
$out .= '<div id="fitem_id_details" class="fitem fitem_ftext">'.
        '<div class="fitemtitle"><label>'.get_string('details', 'block_bcgt').' : </label>'.
        '</div><div class="felement ftext"><input type="text" name="details" value="'.$details.'"/></div></div>';
$out .= '<div id="fitem_id_date" class="fitem fitem_ftext">'.
        '<div class="fitemtitle"><label>'.get_string('date').' : </label></div>'.
        '<div class="felement ftext"><input class="bcgt_datepicker" type="text" name="date" value="'.$date.'"/></div></div>';

$checked = '';
if(isset($_POST['current']) || $pID != -1 && $project->get_is_project_current())
{
    
    $checked = 'checked="checked"';
}
$out .= '<div id="fitem_id_date" class="fitem fitem_ftext">'.
        '<div class="fitemtitle"><label>'.get_string('markcurrent', 'block_bcgt').
        ' : </label></div><div class="felement ftext">'.
        '<input '.$checked.' type="checkbox" name="current" value="current"/></div></div>';
$out .= '</div></fieldset>';

// Qualifications
$out .= '<fieldset class="clearfix" id="bcgtProjectQualifications">
            <legend class="ftoggler">'.get_string('qualifications', 'block_bcgt').'</legend>';
$faFamilies = array();
$families = get_qualification_type_families_used();
if($families)
{
    foreach($families AS $family)
    {
        $familyClass = Qualification::get_plugin_class($family->id);
        if($familyClass && $familyClass::has_formal_assessments())
        {
            $faFamilies[] = $family;
        }
    }
}
$quals = array();
if($faFamilies)
{
    $onCourse = false;
    $hasStudents = false;
    foreach($faFamilies AS $family)
    {
        $famQuals = search_qualification(-1, -1, -1, 
                '', $family->id, '', -1, 
                $onCourse, $hasStudents);
        $quals = $quals + $famQuals;
    }  
    
    $out .= '<div id="projectsQualContainer" class="bcgt_three_c_container bcgt_float_container">';	
    
// Current Qualifications
$out .= '<div id="projectLeft" class="bcgt_admin_left bcgt_col">';    
    $out .= '<h2 class="formsubheading">'.get_string('currentquals', 'block_bcgt').'</h2>';
    $out .= '<select name="removeselect[]" size="20" id="removeselect" multiple="multiple">';
            
//this needs to loop through the new quals and add them into this
if($newAddQuals)
    {
        foreach($newAddQuals AS $newQual)
        {
            $qual = $quals[$newQual];
            $out .= '<option value="'.$qual->id.'" title="'.
                bcgt_get_qualification_display_name($qual).'">'.
                bcgt_get_qualification_display_name($qual).
                '</option>';
        }
    }
    //it also needs to loop through and get rid of any from before that are being removed
    if($qualsOnProject)
    {
        foreach($qualsOnProject AS $qualOnProj)
        {
            if(!in_array($qualOnProj, $newRemoveQuals))
            {
                $newAddQuals[] = $qualOnProj;
                $qual = $quals[$qualOnProj];
                $out .= '<option value="'.$qual->id.'" title="'.
                bcgt_get_qualification_display_name($qual).'">'.
                bcgt_get_qualification_display_name($qual).
                '</option>';
            }
        }
    }
    $out .= '</select><br />';
    foreach($newAddQuals AS $qualProject)
    {
        $out .= '<input type="hidden" name="qualsOnProject[]" value="'.$qualProject.'"/>';
    }
    $out .= '</div>';
    
    // Add / Remove     
    $out .= '<div id="projectCenter" class="bcgt_admin_center bcgt_col">';
        $out .= '<p class="arrow_button">';
            $out .= '<input name="add" id="addQual" type="submit" disabled="disabled" value="'.get_string('add','block_bcgt').'"/><br />';
            $out .= '<input name="remove" id="removeQual" type="submit" disabled="disabled" value="'.get_string('remove','block_bcgt').'"/><br />';
        $out .= '</p>';
    $out .= '</div>';
    $out .= '<div id="projectRight" class="bcgt_admin_right bcgt_col">';
    $out .= '<h2 class="formsubheading">'.get_string('allavailablequals', 'block_bcgt').'</h2>';
    $out .= '<select name="addselect[]" size="20" id="addselect" multiple="multiple">';
    if($quals)
    {
        foreach($quals AS $qual)
        {
            if(!in_array($qual->id, $newAddQuals))
            {
                $out .= '<option value="'.$qual->id.'" title="'.
                    bcgt_get_qualification_display_name($qual).'">'.
                    bcgt_get_qualification_display_name($qual).
                    '</option>';
            }
        }
    }
    $out .= '</select><br />';
    
    $out .= '</div>';
    $out .= '</div>';
}
//then it needs to get the qual families
//then it needs to get the quals
//options for all.
$out .= '</fieldset>';
$out .= '<input type="submit" name="save" value="'.get_string('save', 'block_bcgt').'" class="bcgtFormButton" />';
$out .= '</form>';
$out .= html_writer::end_tag('div');//end main column
$out .= $OUTPUT->footer();

echo $out;
?>
