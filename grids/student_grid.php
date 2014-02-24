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
$studentID = optional_param('sID', -1, PARAM_INT);
$forceLoad = optional_param('fload', true, PARAM_BOOL);
$clearSession = optional_param('csess', true, PARAM_BOOL);
$order = optional_param('order', 'spec', PARAM_TEXT);


//TODO::::::
//IF no qual id is passed down then load all quals for this student!!!!



$qualification = null;
if(!$clearSession)
{
    $sessionQuals = isset($_SESSION['session_stu_quals'])? 
    unserialize(urldecode($_SESSION['session_stu_quals'])) : array(); 
}
else
{
    $sessionQuals = array();
}

$qualObject = new stdClass();

//this will be an array of studentID => qualarray->qual object->qual
//does the qual exist already for this student?
if(array_key_exists($studentID, $sessionQuals))
{
    //the sessionsQuals[studentID] is an array of qualid =>object
    //where object has qualification and session start
    $studentQualArray = $sessionQuals[$studentID];
    if(array_key_exists($qualID, $studentQualArray))
    {
        $qualObject = $studentQualArray[$qualID];
        $sessionStartTime = $qualObject->sessionStartTime;
        $qualification = $qualObject->qualification;
        
        //we need to check if the students qual has been changed at all since the start of the current session
        $studentUpdateTime = get_student_qual_update_time($qualID, $studentID);
        if($forceLoad || !$forceLoad && $studentUpdateTime > $sessionStartTime)
        {
            //so its been updated since we last loaded it, force it to load again. 
            $qualification = null;
        }
    }
    else
    {
        $qualObject->sessionStartTime = time();
        $studentQualArray[$qualID] = $qualObject;
        $sessionQuals[$studentID] = $sessionQuals[$studentID];
    }
}
else
{
    $qualObject->sessionStartTime = time();
    $qualArray = array();
    $qualArray[$qualID] = $qualObject;
    $sessionQuals[$studentID] = $qualArray;
}

$url = '/blocks/bcgt/forms/student_grid.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->set_heading(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->set_cacheable(true);
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('bcgtmydashboard', 'block_bcgt'));
$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);

$link1 = null;
$link2 = null;
if(has_capability('block/bcgt:viewclassgrids', $context))
{
    $link1 = $CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?&cID='.$courseID;
	$link2 = $CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=track';
}
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),$link2,'title');
$PAGE->navbar->add(get_string('grids', 'block_bcgt'),$link1,'title');
$firstname = '';
$lastname = '';
if($studentID != -1)
{
    $user = $DB->get_record_sql("SELECT * FROM {user} WHERE id = ?", array($studentID));
    if($user)
    {
        $firstname = $user->firstname;
        $lastname = $user->lastname; 
    }
    $heading = $firstname.' '.$lastname;
}
$PAGE->navbar->add($firstname.' '.$lastname,null,'title');
//grids
//student name grid
$PAGE->requires->js_init_call('M.block_bcgt.initgridstu', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');

if(!$qualification)
{
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELALL;
    $loadParams->loadAward = true;
    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
}
$studentInd = $DB->get_record_sql('SELECT * FROM {user} WHERE id = ?', array($studentID));
$out = $OUTPUT->header();
    $out .= '<form id="studentGridForm" method="POST" name="studentGridForm" action="student_grid.php?">';			
    $out .= '<input type="hidden" name="cID" value="'.$courseID.'"/>';  

    // Menu
    $out .= '<div class="bcgtGridMenu">';
    if(has_capability('block/bcgt:viewclassgrids', $context))
    {
        $dropDowns = "yes";
        //Drop down of other students
        if($qualification)
        {
            $students = $qualification->get_students();
            if($students)
            {                
                $out .= '<div class="bcgtStudentChange">';
                $out .= '<label for="studentChange">Change Student to : </label>';
                $out .= '<select id="studentChange" name="sID"><option value=""></option>';                
                foreach($students AS $student)
                {
                    $selected = '';
                    if($studentID == $student->id)
                    {
                        $selected = "selected";
                    }
                    $out .= '<option '.$selected.' value="'.$student->id.'">'.
                            $student->username.' : '.$student->firstname.' '.
                            $student->lastname.'</option>';
                }
                
                $out .= '</select><br />'; 
                $out .= '</div>'; //bcgtStudentChange
            }
        }
        //if we have the ability to see all, then we need to to see all here
        //>>BEDCOLL todo this should be only the students other quals
        $qualifications = get_users_quals($studentID);
        if($qualifications)
        {
            $out .= '<div class="bcgtQualChange">';
            $out .= '<label for="qualChange">Change Qualification to : </label>';
            $out .= '<select id="qualChange" name="qID"><option value=""></option>';
            foreach($qualifications AS $qual)
            {
                $selected = '';
                if($qualID == $qual->id)
                {
                    $selected = "selected";
                }
                $out .= '<option '.$selected.' value="'.$qual->id.'">'.
                        bcgt_get_qualification_display_name($qual).'</option>';
            }

            $out .= '</select>';
            $out .= '</div>'; //bcgtQualChange 
        }
        else
        {
            $out .= '<input type="hidden" name="qID" id="qID" value="'.$qualID.'"/>';
        }
    }
    else
    {
        $dropDowns = "no";
        $out .= '<input type="hidden" id="sID" name="sID" value="'.$studentID.'"/>';
        $out .= '<input type="hidden" id="qID" name="qID" value="'.$qualID.'"/>';
    }
    $out .= '<input type="hidden" id="selects" name="selects" value="'.$dropDowns.'"/>'; 
    $out .= '<input type="hidden" id="user" name="user" value="'.$USER->id.'"/>';
    $out .= '<input type="hidden" name="gridType" value="student" />';
    

    // $out .= get_grid_menu($courseID);
    $out .= get_grid_menu($studentID, $qualID);
    $out .= '</div>';

    $heading = get_string('trackinggrid','block_bcgt');
	if($studentInd)
	{
		$heading .= " - $studentInd->username : $studentInd->firstname $studentInd->lastname";
	}
    $out .= html_writer::tag('h2', $heading, 
        array('class'=>'formheading'));
    
    $out .= '<input type="hidden" id="order" value="'.$order.'" name="order"/>';
    if($activities = bcgt_user_activities($qualID, $studentID, -1))
    {
        //if we have activities then show the other options
        $out .= '<div class="tabs"><div class="tabtree">';
        $out .= '<ul class="tabrow0">';
        if($order == '')
        {
            $order = 'spec';
        }
        $focus = ($order == 'spec')? 'focus' : '';
        $out.= '<li class="last '.$focus.'">'.
            '<a order="spec" class="ordertab" href="?&sID='.$studentID.
                '&qID='.$qualID.'&cID='.$courseID.'&order=spec">'.
            '<span>'.get_string('byspec', 'block_bcgt').'</span></a></li>';
        $focus = ($order == 'actunit')? 'focus' : '';
        $out.= '<li class="first '.$focus.'">'.
                '<a order="actunit" class="ordertab" href="?&sID='.$studentID.
                '&qID='.$qualID.'&cID='.$courseID.'&order=actunit">'.
                '<span>'.get_string('orderbyactivityunit', 'block_bcgt').'</span></a></li>';
        $focus = ($order == 'unitact')? 'focus' : '';
        $out.= '<li class="last '.$focus.'">'.
                '<a order="unitact" class="ordertab" href="?&sID='.$studentID.
                '&qID='.$qualID.'&cID='.$courseID.'&order=unitact">'.
                '<span>'.get_string('orderbyunitactivity', 'block_bcgt').'</span></a></li>';
    }
    $out.= '</ul>';
    $out.= '</div></div>';
    
    $out .= html_writer::start_tag('div', array('class'=>'bcgt_grid_outer', 
    'id'=>'studentGridOuter'));
    $out .= html_writer::tag('h3', $qualification->get_display_name(), 
        array('class'=>'subTitle'));
    $loadParams = new stdClass;
    $loadParams->loadLevel = Qualification::LOADLEVELALL;
    $loadParams->loadAward = true;
    $loadParams->loadTargets = true;
    $loadParams->loadAddUnits = false;
    $qualification->load_student_information($studentID, $loadParams);
    
    //at this point we load it up into the session

    $out .= $qualification->display_student_grid(false, true);
    
    $qualArray = $sessionQuals[$studentID];    
    $qualObject = $qualArray[$qualID];
    $qualObject->qualification = $qualification;
    $qualArray[$qualID] = $qualObject;
    $sessionQuals[$studentID] = $qualArray;
    $_SESSION['session_stu_quals'] = urlencode(serialize($sessionQuals));
    //other options at the bottom

    $out .= html_writer::end_tag('div');
    
    $out .= "<div id='bcgtblanket'></div>";
    $out .= '<div id="popUpDiv">
                <div id="commentMove">&nbsp;</div>
                <div id="commentClose"><a href="#"><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec/pix/grid_symbols/close.png" style="width:24px;" alt="Close" /></a></div><br class="cl" />
                <span id="commentUserSpan">Comments for <span id="commentBoxUsername"></span> : <span id="commentBoxFullname"></span></span><br>
                <span id="commentUnitSpan"><span id="commentBoxUnit"></span></span><br>
                <span id="commentCriteriaSpan"><span id="commentBoxCriteria"></span></span><br><br><br>
                <textarea id="commentText" style="width:80%;height:200px;margin:auto;"></textarea><br><br> 
                <input type="button" id="saveComment" value="Save" />
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" id="cancelComment" value="Cancel" />
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" id="deleteComment" value="Delete" />
            </div>';
    $out .= '<div id="genericPopup" style="display:none;">
                <div id="commentClose"><a href="#" onclick="popup.close();return false;"><img src="'.$CFG->wwwroot.'/blocks/bcgt/pix/close.png" style="width:24px;" alt="Close" /></a></div><br class="cl" /><!-- Toggle -->
                <span id="popUpTitle"></span><br><br>
                    <div id="popUpSubTitle"></div><br>
                    <div id="popUpContent"></div>
                    <br>
                    <input type="button" value="Close" onclick="popup.close();return false;" />    
            </div>';
    
$out .= $OUTPUT->footer();
echo $out;