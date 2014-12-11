<?php
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
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Unit.class.php');
set_time_limit(0);
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
$PAGE->set_context($context);
$qualID = optional_param('qID', -1, PARAM_INT);
$unitID = optional_param('uID', -1, PARAM_INT);
$sCourseID = optional_param('scID', -1, PARAM_INT);
$forceLoad = optional_param('fload', true, PARAM_BOOL);
$clearSession = optional_param('csess', true, PARAM_BOOL);
$groupingID = optional_param('grID', -1, PARAM_INT);
//this is actually the coursemoduleid
$cmID = optional_param('cmID', -1, PARAM_INT);
$url = '/blocks/bcgt/forms/unit_grid.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('activitygrid', 'block_bcgt'));
$PAGE->set_heading(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('bcgtmydashboard', 'block_bcgt'));
if($courseID != 1)
{
    global $DB;
    $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($courseID));
    if($course)
    {
        $PAGE->navbar->add($course->shortname,$CFG->wwwroot.'/course/view.php?id='.$courseID,'title');
    }
}
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=track&cID='.$courseID,'title');
$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initgridact', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');

if(!$clearSession)
{
    $sessionActs = isset($_SESSION['session_act'])? 
    unserialize(urldecode($_SESSION['session_act'])) : new stdClass();
}
else
{
    $sessionActs = new stdClass();
}

//$sessionActs
//has two arays
//quals
//activities

if(isset($sessionActs->quals))
{
    $qualsArray = $sessionActs->quals;
}
else 
{
    $qualsArray = array();
}
if(isset($sessionActs->activities))
{
    $activitiesArray = $sessionActs->activities;
}
else
{
    $activitiesArray = bcgt_get_coursemodules($sCourseID, $qualID, $groupingID, '', -1, $cmID);
}

//step one find all of the activities
$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELALL;
if($qualID != -1 && !array_key_exists($qualID, $qualsArray))
{
    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
    $qualsArray[$qualID] = $qualification;
}
elseif($qualID == -1 && $clearSession)
{
    //then we are viewing for a course and/or group and/or a single activity
    //step two find all of the quals that are on this activities
    //so find the distinct quals that are on it
    $activityQuals = bcgt_get_quals_on_course_modules($sCourseID, $groupingID, '', -1, $cmID);
    foreach($activityQuals AS $qual)
    {
        $qualification = Qualification::get_qualification_class_id($qual->id, $loadParams);
        $qualsArray[$qual->id] = $qualification;
    }
}

$link1 = null;
if(has_capability('block/bcgt:viewclassgrids', $context))
{
    $link1 = $CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?&cID='.$courseID.'&g=u';
}
$PAGE->navbar->add(get_string('grids', 'block_bcgt'),$link1,'title');
$out = $OUTPUT->header();
    $out .= '<form id="actGridForm" method="POST" name="actGridForm">';			
    $out .= '<input type="hidden" name="cID" id="cID" value="'.$courseID.'"/>';
    $out .= '<input type="hidden" name="gridType" value="activity" />';
    // Menu
    $out .= '<div class="bcgtGridMenu">';
    if(has_capability('block/bcgt:viewclassgrids', $context))
    {
        $dropDowns = "yes";
        //Drop down of other activities
        $activities = bcgt_get_users_activities($USER->id, -1, -1, -1);
        $out .= '<label for="activityChange">Change Activity to : </label>';
        $out .= '<select id="activityChange" name="cmID"><option value="-1"></option>';
        if($activities)
        {  
            foreach($activities AS $activity)
            {
                $selected = '';
                if($cmID == $activity->id)
                {
                    $selected = "selected";
                }
                $out .= '<option '.$selected.' value="'.$activity->id.'">'.
                        $activity->name.' ('.$activity->module.')</option>';
            }
        }
        else
        {
            $out .= '<option selected value="'.$cmID.'"></option>';
        }
        $out .= '</select>';
        $out .= '</div>'; //bcgtUnitChange
    }
    else
    {
        $dropDowns = "no";
        $out .= '<input type="hidden" id="aID" name="cmID" value="'.$actID.'"/>';
    }
    $out .= '<input type="hidden" id="selects" name="selects" value="'.$dropDowns.'"/>'; 
    $out .= '<input type="hidden" id="user" name="user" value="'.$USER->id.'"/>';
    $out .= '</div>';
    
    $heading = get_string('trackinggrid','block_bcgt');
    $out .= html_writer::tag('h2', $heading, 
        array('class'=>'formheading'));
    if($groupingID != -1)
    {
        $groupDB = $DB->get_record_sql("SELECT * FROM {groups} WHERE id = ?", array($groupingID));
        if($groupDB)
        {
            $out .= '<h3>'.get_string('grouping', 'block_bcgt').': '.
                    $groupDB->name.' (<a href="'.$CFG->wwwroot.
                    '/blocks/bcgt/grids/act_grid.php?cID='.$courseID.'&scID='.
                    $sCourseID.'&qID="'.$qualID.'&cmID='.$cmID.'>'.
                    get_string('cleargroup', 'block_bcgt').'</a>)</h3>';
        }
    }
    $out .= html_writer::start_tag('div', array('class'=>'bcgt_grid_outer', 
    'id'=>'activityGridOuter'));
    
    foreach($qualsArray AS $qualification)
    {
        $out .= $qualification->display_activity_grid($activitiesArray);
    }
    
    $sessionActs->quals = $qualsArray;
    $sessionActs->activities = $activitiesArray;
    $_SESSION['session_act'] = urlencode(serialize($sessionActs));
    //other options at the bottom

    $out .= html_writer::end_tag('div');
    
    $out .= "<div id='bcgtblanket'></div>";
    $out .= '<div id="popUpDiv">
                <div id="commentClose"><a href="#"><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/close.png" style="width:24px;" alt="Close" /></a></div><br class="cl" />
                <span id="commentUserSpan">Comments for <span id="commentBoxUsername"></span> : <span id="commentBoxFullname"></span></span><br>
                <span id="commentUnitSpan">Unit : <span id="commentBoxUnit"></span></span><br>
                <span id="commentCriteriaSpan">Criteria : <span id="commentBoxCriteria"></span></span><br><br><br>
                <textarea id="commentText" style="width:80%;height:200px;margin:auto;"></textarea><br><br> 
                <input type="button" id="saveComment" value="Save" />
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" id="cancelComment" value="Cancel" />
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" id="deleteComment" value="Delete" />
            </div>';
     $out .= '<div id="genericPopup" style="display:none;"><div id="genericContent">
                <div id="commentClose"><a href="#" onclick="popup.close();return false;"><img src="'.$CFG->wwwroot.'/blocks/bcgt/pix/close.png" style="width:24px;" alt="Close" /></a></div><br class="cl" /><!-- Toggle -->
                <span id="popUpTitle"></span><br><br>
                    <div id="popUpSubTitle"></div><br>
                    <div id="popUpContent"></div>
                    <br>
                    <input type="button" value="Close" onclick="popup.close();return false;" />    
              </div></div>';
    $out .= '</form>';			
$out .= $OUTPUT->footer();
echo $out;

?>