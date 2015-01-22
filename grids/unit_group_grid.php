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
$sCourseID = optional_param('scID', -1, PARAM_INT);
$qualID = optional_param('qID',-1, PARAM_INT);
if($courseID != -1)
{
    $context = context_course::instance($courseID);
}
else
{
    $context = context_course::instance($COURSE->id);
}
require_login();

if(!has_capability('block/bcgt:viewunitgrid', $context)){
    print_error('invalid access');
}

$PAGE->set_context($context);

$unitID = optional_param('uID', -1, PARAM_INT);
$groupingID = optional_param('grID', -1, PARAM_INT);
$forceLoad = optional_param('fload', true, PARAM_BOOL);
$clearSession = optional_param('csess', true, PARAM_BOOL);

load_unit_class($unitID);
$unit = null;
if(!$clearSession)
{
    $sessionUnits = isset($_SESSION['session_unit'])? 
    unserialize(urldecode($_SESSION['session_unit'])) : array();
}
else
{
    $sessionUnits = array();
}

$unitObject = new stdClass();

//this will be an array of unitIDs
//each unitids has an object. 
//The object has an instance of the unit, no student data
if(array_key_exists($unitID, $sessionUnits))
{
    $unitObject = $sessionUnits[$unitID];
    $unit = $unitObject->unit;   
}
else
{
    $unitObject->unit = null;
    $sessionUnits[$unitID] = $unitObject;
}

$url = '/blocks/bcgt/forms/unit_group_grid.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('unitgroupgrid', 'block_bcgt'));
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
$PAGE->requires->js_init_call('M.block_bcgt.initgridgroupunit', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');

$link1 = null;
if(has_capability('block/bcgt:viewclassgrids', $context))
{
    $link1 = $CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?&cID='.$courseID.'&g=u';
}
$PAGE->navbar->add(get_string('grids', 'block_bcgt'),$link1,'title');
$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELALL;
if(!$unit || empty($unit) || $unit == null || $unit == '')
{
    $unit = Unit::get_unit_class_id($unitID, $loadParams);
}
if($unit)
{
    $PAGE->navbar->add($unit->get_uniqueID().' - '.$unit->get_name(),null,'title');
}
$out = $OUTPUT->header();
    $out .= '<form id="unitGroupGridForm" method="POST" name="unitGroupGridForm" action="unit_group_grid.php?">';			
    $out .= '<input type="hidden" name="cID" id="cID" value="'.$courseID.'"/>';
    $out .= '<input type="hidden" name="gridType" value="unit" />';
    $out .= '<input type="hidden" name="grID" id="grID" value="'.$groupingID.'"/>';
    $out .= '<input type="hidden" name="scID" id="scID" value="'.$sCourseID.'"/>';
    //need to put down what qual the user is on for this unit
    // Menu
    $out .= '<div class="bcgtGridMenu">';
    if(has_capability('block/bcgt:viewclassgrids', $context))
    {
        $dropDowns = "yes";
        //Drop down of other students
        //qual change:
        if(has_capability('block/bcgt:viewallgrids', context_system::instance()))
        {
            $quals = $unit->get_quals_on('',-1,-1,$courseID);
        }
        else
        {
            $quals = $unit->get_quals_on_roles('', $USER->id, array('teacher', 'editingteacher'),$courseID);
        }
        $out .= '<div class="bcgtQualChange">';
        $out .= '<label for="qualChange">'.get_string('changequal','block_bcgt').' : </label>';
        $out .= '<select id="qualChange" name="qID"><option value="-1">'.get_string('showall','block_bcgt').'</option>';
        $qualFound = false;
        if($quals)
        {
            foreach($quals AS $qual)
            {
                $selected = '';
                if($qualID == $qual->id)
                {
                    $qualFound = true;
                    $selected = "selected";
                }
                elseif(count($quals) == 1)
                {
                    $qualFound = true;
                    $selected = "selected";
                    $qualID = $qual->id;
                }
                $out .= '<option '.$selected.' value="'.$qual->id.'">'.
                bcgt_get_qualification_display_name($qual, ' ').'</option>';
            }
        }
        if((!$quals || !$qualFound) && $qualID != -1)
        {
            //its not been able to find any so just put the current one on so
            //it doesnt error.
            $qualification = Qualification::get_qualification_class_id($qualID);
            $out .= '<option selected value="'.$qualID.'">'.
                $qualification->get_display_name().'</option>';
        }
        $out .= '</select>';
        $out .= '</div>'; //bcgtQualChange
        
        
        $out .= '<div class="bcgtUnitChange">';
        $out .= '<label for="unitChange">Change Unit to : </label>';
        $out .= '<select id="unitChange" name="uID"><option value="-1"></option>';
        //now get the other units to change to
        
        $teacherRole = $DB->get_record_select('role', 'shortname = ?', array('teacher'));
        $units = bcgt_get_users_units($USER->id, $teacherRole->id, '', $qualID);
        $unitFound = false;
        if($units)
        {
            foreach($units AS $qualUnit)
            {
                $selected = '';
                if($unitID == $qualUnit->id)
                {
                    $unitFound = true;
                    $selected = "selected";
                }
                $out .= '<option '.$selected.' value="'.$qualUnit->id.'">'.
                        $qualUnit->uniqueid.' '.$qualUnit->name.'</option>';
            }
        }
        if(!$units || !$unitFound)
        {
            $unit = Unit::get_unit_class_id($unitID, $loadParams);
            $out .= '<option selected value="'.$unit->get_id().'">'.
                        $unit->get_uniqueID().' '.$unit->get_name().'</option>';
        }
        $out .= '</select>';
        $out .= '</div>'; //bcgtUnitChange
    }
    else
    {
        $dropDowns = "no";
        $out .= '<input type="hidden" id="uID" name="uID" value="'.$unitID.'"/>';
    }
    $out .= '<input type="hidden" id="selects" name="selects" value="'.$dropDowns.'"/>'; 
    $out .= '<input type="hidden" id="user" name="user" value="'.$USER->id.'"/>';
    
    $out .= get_grid_menu(null, $unitID, $qualID, $courseID);
    $out .= '</div>';
    
    $heading = get_string('trackinggrid','block_bcgt');
    $heading .= " - " . $unit->get_display_name();
    $out .= html_writer::tag('h2', $heading, 
        array('class'=>'formheading'));
    
    $out .= html_writer::start_tag('div', array('class'=>'bcgt_grid_outer', 
    'id'=>'unitGridOuter'));
    //at this point we load it up into the session
    $unitGrid = $unit->display_unit_grid();
    if(!$unitGrid)
    {
        $out .= '<p>'.get_string('notenoughinfofilter', 'block_bcgt').'</p>';
    }
    else
    {
        $out .= $unitGrid;
    }
    
    $unitObject = $sessionUnits[$unitID];
    $unitObject->unit = $unit;
    $_SESSION['session_unit'] = urlencode(serialize($sessionUnits));
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
     $out .= '<div id="genericPopup" style="display:none;">
         <div id="genericContent">
                <div id="commentClose"><a href="#" onclick="popup.close();return false;"><img src="'.$CFG->wwwroot.'/blocks/bcgt/pix/close.png" style="width:24px;" alt="Close" /></a></div><br class="cl" /><!-- Toggle -->
                <span id="popUpTitle"></span><br><br>
                    <div id="popUpSubTitle"></div><br>
                    <div id="popUpContent"></div>
                    <br>
                    <input type="button" value="Close" onclick="popup.close();return false;" /> 
                    </div>
            </div>';
    
    $out .= '</form>';			
$out .= $OUTPUT->footer();
echo $out;

?>