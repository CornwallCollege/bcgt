<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

global $COURSE, $CFG, $DB, $PAGE, $OUTPUT;
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
if(isset($_POST['editStudentUnit']))
{
    redirect('edit_students_units.php?a=ms');
}
$PAGE->set_context($context);
require_capability('block/bcgt:editteacherqual', $context);
$url = '/blocks/bcgt/forms/edit_qual_teacher.php';

$role = optional_param('role', 'student', PARAM_TEXT);
if($role == 'teacher')
{
    $string = 'editqualteacherheading';
}
elseif($role == 'student')
{
    $string = 'editqualstudentheading';
}

$PAGE->set_url($url, array());
$PAGE->set_title(get_string($string, 'block_bcgt'));
$PAGE->set_heading(get_string($string, 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('myDashboard', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
$PAGE->navbar->add(get_string('myDashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string($string, 'block_bcgt'));
$familyID = optional_param('family', -1, PARAM_INT);
$levelID = optional_param('level', -1, PARAM_INT);
$subTypeID = optional_param('subtype', -1, PARAM_INT);
$search = optional_param('searchKeyword', '', PARAM_TEXT);
$userID = optional_param('userID', -1, PARAM_INT); 
$completeNewAdditions = isset($_SESSION['new_quals'])? 
    unserialize(urldecode($_SESSION['new_quals'])) : array();

$stage2 = optional_param('stage2', false, PARAM_BOOL);

$usersQuals = null;
$searchUsers = optional_param('searchUsers', '', PARAM_TEXT);
$searchQuals = optional_param('searchQuals', '', PARAM_TEXT);
$qualsAdd = isset($_POST['addselect'])? $_POST['addselect'] : array();
if(count($qualsAdd) >= 1)
{
    $stage2 = true;
}
$qualsRemove = isset($_POST['removeselect'])? $_POST['removeselect'] : array();
$roleDB = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = ?', array($role));
if($userID != -1)
{
    if(count($qualsAdd) >= 1 && isset($_POST['add']))
    {
        //then we are adding the qual to the teacher
        //add qualid to teacher
        if(array_key_exists($userID, $completeNewAdditions))
        {
            $currentNewAdditions = $completeNewAdditions[$userID];
            $currentNewAdditions = array_merge($currentNewAdditions, $qualsAdd);
            $completeNewAdditions[$userID] = $currentNewAdditions;
        }
        else
        {
            $completeNewAdditions[$userID] = $qualsAdd;
        }
        $_SESSION['new_quals'] = urlencode(serialize($completeNewAdditions));
        add_qual_user($qualsAdd, $roleDB->id, $userID, $role);
    }
    if(count($qualsRemove) >= 1 && isset($_POST['remove']))
    {
        //reomve qualid from teacher
        remove_qual_user($qualsRemove, $roleDB->id, $userID, $role); 
    }   
    //get the teachers quals.
    $usersQuals = get_users_quals($userID, $roleDB->id, $searchQuals);
}
$users = get_users_bcgt($searchUsers);
$qualIDs = array();
if($usersQuals)
{
   foreach($usersQuals AS $qual)
   {
       $qualIDs[] = $qual->id;
   }
}
$searchResults = search_qualification(-1, $levelID, $subTypeID, $search, $familyID, $qualIDs);
$families = get_qualification_type_families_used();
$subTypes = get_subtype_from_type(-1, $levelID, $familyID);
$levels = get_level_from_type(-1, $familyID);   

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initteacherquals', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();

$out = $OUTPUT->header();

if($role == 'teacher')
{
    $out .= html_writer::tag('h2', get_string('editqualteacherheading','block_bcgt'), 
        array('class'=>'formheading'));
}
elseif($role == 'student')
{
    $out .= html_writer::tag('h2', get_string('editqualstudentheading','block_bcgt'), 
        array('class'=>'formheading')); 
}

$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_controls', 
    'id'=>'editQualUsers'));
$out .= '<form name="editUserQual" action="edit_qual_user.php" method="POST" id="editUserQual">';
$out .= '<input type="hidden" name="role" value="'.$role.'"/>';
$out .= '<input type="hidden" name="cID" value="'.$courseID.'"/>';
$out .= html_writer::start_tag('div', array('class'=>'bcgt_two_c_container bcgt_float_container', 
    'id'=>'bcgtColumnConainer'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_left bcgt_col_one bcgt_col'));

    if($role == 'teacher')
    {
        $out .= html_writer::tag('h3', get_string('teacherschooseheading','block_bcgt'), 
                array('class'=>'subformheading'));
            //the teachers to choose from
    }
    elseif($role == 'student')
    {
        $out .= html_writer::tag('h3', get_string('studentschooseheading','block_bcgt'), 
                array('class'=>'subformheading'));
            //the teachers to choose from
    }
        $out .= '<select name="userID" size="20" id="userID">';
        if($users)
        {
            foreach($users AS $user)
            {
                $selected = '';
                if($userID != -1 && $userID == $user->id)
                {
                    $selected = 'selected';
                }
                $out .= '<option '.$selected.' value="'.$user->id.'" title="'.$user->username.''.
                ' : '.$user->email.'">'.$user->firstname.' '.$user->lastname.''.
                        ', '.$user->email.'</option>';	
            }
        }
        $out .= '</select><br />';
        $out .= '<div class="inputContainer"><div class="inputLeft">'.
            '<label for="searchKeyword">'.get_string('search','block_bcgt').'</label></div>';
    $out .= '<div class="inputRight">'.
            '<input type="text" name="searchUsers" id="searchUsers" value="'.$searchUsers.'"/>'.
            '</div></div>';
    $out .= '<input type="submit" name="search" value="Search"/>';

$out .= html_writer::end_tag('div');
$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_right bcgt_col_two bcgt_col'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_three_c_container bcgt_float_container', 
    'id'=>'bcgtInnerConainer'));

$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_left bcgt_col_one bcgt_col'));

    if($role == 'teacher')
    {
        $out .= html_writer::tag('h3', get_string('teachersquals','block_bcgt'), 
            array('class'=>'subformheading'));
    }
    elseif($role == 'student')
    {
        $out .= html_writer::tag('h3', get_string('studentsquals','block_bcgt'), 
            array('class'=>'subformheading'));
    }

        $out .= '<select name="removeselect[]" size="20" id="removeselect" multiple="multiple">';
        if($usersQuals)
        {
            foreach($usersQuals AS $qual)
            {
                $out .= '<option value="'.$qual->id.'" title="'.$qual->name.''.
                '">'.$qual->family.' '.$qual->trackinglevel.''.
                        ', '.$qual->subtype.' '.$qual->name.'</option>';	
            }
        }
        $out .= '</select><br />';
        $out .= '<div class="inputContainer"><div class="inputLeft">'.
            '<label for="searchKeyword">'.get_string('search','block_bcgt').'</label></div>';
    $out .= '<div class="inputRight">'.
            '<input type="text" name="searchQuals" id="searchQuals" value="'.$searchQuals.'"/>'.
            '</div></div>';
    $out .= '<input type="submit" name="search" value="Search"/>';
    //the teachers on the qual
$out .= html_writer::end_tag('div');

$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_center bcgt_col_two bcgt_col'));
$out .= '<input name="add" id="addQual" type="submit" disabled="disabled" value="'.get_string('add','block_bcgt').'"/><br />';
$out .= '<input name="remove" id="removeQual" type="submit" disabled="disabled" value="'.get_string('remove','block_bcgt').'"/><br />';
$out .= html_writer::end_tag('div');

$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_right bcgt_col_three bcgt_col'));
    
//LAST COLUMN
$out .= html_writer::tag('h3', get_string('qualselectheading','block_bcgt'), 
        array('class'=>'subformheading'));
    $out .= '<select name="addselect[]" id="addselect" size="10" multiple="multiple">';
        if($searchResults)
        {
            foreach($searchResults AS $result)
            {
//                $coursesCount = 0;
//                if($result->countcourse)
//                {
//                    $coursesCount = $result->countcourse;
//                }
//                $unitsCount = 0;
//                if($result->countunits)
//                {
//                    $unitsCount = $result->countunits;
//                }
                $out .= "<option value='$result->id'".
                    "title='$result->name".
                    "'>$result->family $result->trackinglevel ".
                    "$result->subtype $result->name </option>";
            }
        }
    $out .= '</select><br />';
    $out .= '<div class="inputContainer"><div class="inputLeft">'.
            '<label for="type">'.get_string('qualfamily', 'block_bcgt').'</label></div>';
    $out .= '<div class="inputRight"><select id="family" name="family">'.
            '<option value="-1">Please Select one</option>';
        if($families)
        {
            foreach($families as $family) {
                $selected = '';
                if($family->id == $familyID)
                {
                    $selected = 'selected';
                }
                $out .= "<option $selected value='$family->id'>$family->family</option>";
            }	
        }
    $out .= '</select></div></div>';
    $out .= '<div class="inputContainer"><div class="inputLeft">'.
            '<label for="level">'.get_string('level', 'block_bcgt')
            .'</label></div>';
    $out .= '<div class="inputRight"><select id="level" name="level">'.
            '<option value="-1">Please Select one</option>';
        if($levels)
        {
            foreach($levels as $level) {
                $selected = '';
                if($level->get_id() == $levelID)
                {
                    $selected = 'selected';
                }
                $out .= "<option $selected value='".$level->get_id()."'>"
                        .$level->get_level()."</option>";
            }	
        }
    $out .= '</select></div></div>';
    $out .= '<div class="inputContainer"><div class="inputLeft">'.
            '<label for="subtype">'.get_string('subtype','block_bcgt')
            .'</label></div>';
    $out .= '<div class="inputRight"><select id="subtype" name="subtype">'.
            '<option value="-1">Please Select one</option>';
        if($subTypes)
        {
            foreach($subTypes as $subType) {
                $selected = '';
                if($subType->get_id() == $subTypeID)
                {
                    $selected = 'selected';
                }
                $out .= "<option $selected value='".$subType->get_id()."'>".
                        $subType->get_subtype()."</option>";	
            }	
        }
    $out .= '</select></div></div>'; 
    $out .= '<div class="inputContainer"><div class="inputLeft">'.
            '<label for="searchKeyword">'.get_string('ksearch','block_bcgt').'</label></div>';
    $out .= '<div class="inputRight">'.
            '<input type="text" name="searchKeyword" id="searchKeyword" value="'.$search.'"/>'.
            '</div></div>';
    $out .= '<input type="submit" name="search" value="Search"/>';
    $out .= '<input type="submit" name="stage2" value="'.$stage2.'"/>';


$out .= html_writer::end_tag('div');
$out .= html_writer::end_tag('div');//end the three columns
$out .= html_writer::end_tag('div');//end right column
if($role == 'student' )
{
    $disabled = '';
    if(!$stage2)
    {
        $disabled = 'disabled="disabled"';
    }
    $out .= '<input type="submit" '.$disabled.' name="editStudentUnit" value="STAGE 2: Edit Students Units"/>';
}
$out .= '</form>';
$out .= html_writer::end_tag('div');//end the container
$out .= $OUTPUT->footer();
echo $out;
?>
