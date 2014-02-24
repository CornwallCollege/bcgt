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
$PAGE->set_context($context);
require_capability('block/bcgt:editmentorsmentees', $context);
$url = '/blocks/bcgt/forms/edit_users_users.php';
$role = optional_param('role', 'GTTutor', PARAM_TEXT);
if($role == 'gtmanager')
{
    $string = 'editusersusersmanheading';
}
elseif($role == 'gttutor')
{
    $string = 'editusersuserstutheading';
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
$userID = optional_param('userID', -1, PARAM_INT);

$search = optional_param('search', '', PARAM_TEXT);
$usersUsers = null;
$searchUsers = optional_param('searchUsers', '', PARAM_TEXT);
$searchUsersUsers = optional_param('searchUsersUsers', '', PARAM_TEXT);
$usersAdd = isset($_POST['addselect'])? $_POST['addselect'] : array();
$usersRemove = isset($_POST['removeselect'])? $_POST['removeselect'] : array();
$roleDB = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = ?', array($role));
if($userID != -1)
{
    if(count($usersAdd) >= 1 && isset($_POST['add']))
    {
        add_users_users($usersAdd, $roleDB->id, $userID);
    }
    if(count($usersRemove) >= 1 && isset($_POST['remove']))
    {
        remove_users_users($usersRemove, $roleDB->id, $userID);
    }   
    //get the users users.
    $usersUsers = get_users_users($roleDB->id,$userID,$searchUsersUsers);
}
$users = get_users_bcgt($searchUsers);
$searchResults = get_users_non_users($roleDB->id, $userID, $search);

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initusersusers', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();

$out = $OUTPUT->header();

if($role == 'gtmanager')
{
    $out .= html_writer::tag('h2', get_string('editusersusersmanheading','block_bcgt'), 
        array('class'=>'formheading'));
}
elseif($role == 'gttutor')
{
    $out .= html_writer::tag('h2', get_string('editusersuserstutheading','block_bcgt'), 
        array('class'=>'formheading')); 
}

$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_controls', 
    'id'=>'editUsersUsers'));
$out .= '<form name="editUserUsers" action="edit_users_users.php" method="POST" id="editUserUsers">';
$out .= '<input type="hidden" name="cID" value="'.$courseID.'"/>';
$out .= '<input type="hidden" name="role" value="'.$role.'"/>';
$out .= html_writer::start_tag('div', array('class'=>'bcgt_two_c_container bcgt_float_container', 
    'id'=>'bcgtColumnConainer'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_left bcgt_col_one bcgt_col'));

        $out .= html_writer::tag('h3', get_string('users','block_bcgt'), 
                array('class'=>'subformheading'));
            //the teachers to choose from
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

    if($role == 'gtmanager')
    {
        $out .= html_writer::tag('h3', get_string('managersteam','block_bcgt'), 
            array('class'=>'subformheading'));
    }
    elseif($role == 'gttutor')
    {
        $out .= html_writer::tag('h3', get_string('tutorsmentees','block_bcgt'), 
            array('class'=>'subformheading'));
    }

        $out .= '<select name="removeselect[]" size="20" id="removeselect" multiple="multiple">';
        if($usersUsers)
        {
            foreach($usersUsers AS $user)
            {
                $out .= '<option value="'.$user->id.'" title="'.$user->username.''.
                ' : '.$user->email.'">'.$user->firstname.' '.$user->lastname.''.
                        ', '.$user->email.'</option>';		
            }
        }
        $out .= '</select><br />';
        $out .= '<div class="inputContainer"><div class="inputLeft">'.
            '<label for="searchKeyword">'.get_string('search','block_bcgt').'</label></div>';
    $out .= '<div class="inputRight">'.
            '<input type="text" name="searchUsersUsers" id="searchUsersUsers" value="'.$searchUsersUsers.'"/>'.
            '</div></div>';
    $out .= '<input type="submit" name="search" value="Search"/>';
    //the teachers on the qual
$out .= html_writer::end_tag('div');

$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_center bcgt_col_two bcgt_col'));
$out .= '<input name="add" id="addUser" type="submit" disabled="disabled" value="'.get_string('add','block_bcgt').'"/><br />';
$out .= '<input name="remove" id="removeUser" type="submit" disabled="disabled" value="'.get_string('remove','block_bcgt').'"/><br />';
$out .= html_writer::end_tag('div');

$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_right bcgt_col_three bcgt_col'));
    
//LAST COLUMN
$out .= html_writer::tag('h3', get_string('userschoose','block_bcgt'), 
        array('class'=>'subformheading'));
    $out .= '<select name="addselect[]" id="addselect" size="20" multiple="multiple">';
        if($searchResults)
        {
            foreach($searchResults AS $user)
            {
                $out .= '<option value="'.$user->id.'" title="'.$user->username.''.
                ' : '.$user->email.'">'.$user->firstname.' '.$user->lastname.''.
                        ', '.$user->email.'</option>';	
            }
        }
    $out .= '</select>';
    $out .= '<div class="inputContainer"><div class="inputLeft">'.
            '<label for="searchKeyword">'.get_string('ksearch','block_bcgt').'</label></div>';
    $out .= '<div class="inputRight">'.
            '<input type="text" name="search" id="search" value="'.$search.'"/>'.
            '</div></div>';
    $out .= '<input type="submit" name="search" value="Search"/>';


$out .= html_writer::end_tag('div');
$out .= html_writer::end_tag('div');//end the three columns
$out .= html_writer::end_tag('div');//end right column
$out .= '</form>';
$out .= html_writer::end_tag('div');//end the container
$out .= $OUTPUT->footer();
echo $out;
?>
