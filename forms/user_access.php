<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
global $COURSE, $CFG, $PAGE, $OUTPUT, $DB;;
require_once('../../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

$cID = optional_param('cID', -1, PARAM_INT);
$uID = optional_param('uID', -1, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);
if($cID != -1)
{
    $context = context_course::instance($cID);
}
else
{
    $context = context_course::instance($COURSE->id);
}

$a = optional_param('a', '', PARAM_TEXT);
$qID = optional_param('qID', -1, PARAM_INT);
$roleID = optional_param('rID', -1, PARAM_INT);
if($a == 'unlink' && $qID != -1 && $roleID != -1)
{
    //then we are removing the users
    $DB->delete_records('block_bcgt_user_qual', array('userid'=>$uID, 'bcgtqualificationid'=>$qID, 'roleid'=>$roleID));
}
//$report = '';
require_login();
$PAGE->set_context($context);
require_capability('block/bcgt:checkuseraccess', $context);

$url = '/blocks/bcgt/forms/user_access.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('useraccess', 'block_bcgt'));
$PAGE->set_heading(get_string('useraccess', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('useraccess', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
$PAGE->navbar->add(get_string('admin', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string('useraccess', 'block_bcgt'),'','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.inituseraccess', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();
$out .= '<div id="userAccessBCGT">';
$out .= '<h2>'.get_string('useraccess','block_bcgt').'</h2>';
$out .= '<div id="userAccessWrapper">';
$out .= html_writer::start_tag('div', array('class'=>'bcgt_user_access_controls', 
    'id'=>'userAccessContainer'));

$out .= '<form name="" id="userAccessform" method="POST" action="#" enctype="multipart/form-data">';

$out .= '<input type="text" name="search" value="'.$search.'"/>';
$out .= '<input type="submit" name="runsearch" value="'.get_string('search', 'block_bcgt').'"/>';

if($search != '')
{
    $sql = "SELECT * FROM {user} WHERE username LIKE ? OR firstname LIKE ? OR lastname LIKE ? OR email LIKE ?";
    $users = $DB->get_records_sql($sql, array('%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%'));
    if($users)
    {
        $out  .= '<select name="uID">';
        $out .= '<option value="-1">'.get_string('pleaseselect', 'block_bcgt').'</option>';
        foreach($users AS $user)
        {
            $selected = '';
            if($user->id == $uID)
            {
                $selected = 'selected';
            }
            $out .= '<option '.$selected.' value="'.$user->id.'">'.$user->username.' : '.$user->firstname.' '.$user->lastname.'</option>';
        }
        $out .= '</select>';
        $out .= '<input type="submit" name="run" value="'.get_string('checkuseraccess','block_bcgt').'"/>';
    }
}

if($uID != -1)
{
    //the perform check
    //get the courses this user is on
    $courses = bcgt_get_users_courses_any_role($uID, true);
    if($courses)
    {
        $out .= '<h3>'.get_string('usersqualsbycourse','block_bcgt').'</h3>';
        $out .= '<table>';
        $out .= '<thead>';
        $out .= '<tr><th>'.get_string('course').'</th>';
        $out .= '<th>'.get_string('courseroles', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('quals', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('qualroles', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('editquals', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('editunits', 'block_bcgt').'</th>';
        $out .= '</tr>';
        $qualsFound = array();
        foreach($courses AS $course)
        {
            $out .= '<tr>';
            $out .= '<td>';
            $out .= $course->shortname;
            $out .= '</td>';
            
            $out .= '<td>';
            $roles = bcgt_get_users_course_roles($uID, $course->id);
            if($roles)
            {
                $out .= '<ul>';
                foreach($roles AS $role)
                {
                    $out .= '<li>'.$role->shortname.'</li>';
                }
                $out .= '</ul>';
            }
            $out .= '</td>';
            
            //now we want to get the quals that are on this course. 
            $quals = bcgt_get_course_quals($course->id);
            if($quals)
            {
                $out .= '<td><ul>';
                foreach($quals AS $qual)
                {
                    $out .= '<li>'.bcgt_get_qualification_display_name($qual, false).'</li>';
                }
                $out .= '</ul></td>';
            }
            
            //now get qual roles. 
            $out .= '<td><ul>';
            foreach($quals AS $qual)
            {
                $qualsFound[$qual->id] = $qual->id;
                $out .= '<li>';
                $qualRoles = bcgt_get_user_qual_roles($uID, $qual->id);
                if($qualRoles)
                {
                    foreach($qualRoles AS $role)
                    {
                        $out .= $role->shortname.', ';
                    }
                }
                $out .= '</li>';
            }
            $out .= '</ul></td>';
            
            $out .= '<td>';
            $out .= '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_course_'.
                    'qual_user.php?cID='.$course->id.'">'.
                    get_string('editquals', 'block_bcgt').'</a>';
            $out .= '</td>';
            
            $out .= '<td>';
            $out .= '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_students'.
                    '_units.php?a=s&sID='.$uID.'&studentSearch='.$search.'&cID='.
                    $course->id.'">'.get_string('editunits', 'block_bcgt').'</a>';
            $out .= '</td>';
            
            $out .= '</tr>';
        }
        $out .= '</thead>';
        $out .= '</table>';
    }

    $out .= '<h3>'.get_string('usersremainingquals', 'block_bcgt').'</h3>';
    $usersQuals = get_users_quals($uID);
    if($usersQuals)
    {
        $out .= '<table>';
        $out .= '<thead>';
        $out .= '<tr>';
        $out .= '<th>'.get_string('quals', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('qualroles', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('unlinkfromqual', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('course').'</th>';
        $out .= '<th>'.get_string('courseroles', 'block_bcgt').'</th>';
        $out .= '</tr>';
        $out .= '</thead>';
        $out .= '<tbody>';
        foreach($usersQuals AS $qual)
        {
//            if(!in_array($qual->id, $qualsFound))
//            {
                //then we havent seen this qual before
                $out .= '<tr>';
                $out .= '<td>'.bcgt_get_qualification_display_name($qual, false).'</td>';
                
                //now get the users roles. 
                $out .= '<td>';
                $qualRoles = bcgt_get_user_qual_roles($uID, $qual->id);
                if($qualRoles)
                {
                    $out .= '<ul>';
                    foreach($qualRoles AS $role)
                    {
                        $out .= '<li>'.$role->shortname.'</li>';
                    }
                    $out .= '</ul>';
                }
                $out .= '</td>';
                
                //give ability to unlink.
                $out .= '<td>';
                $out .= '<ul>';
                foreach($qualRoles AS $role)
                {
                    $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/user_access'.
                        '.php?cID='.$cID.'&uID='.$uID.'&a=unlink&qID='.$qual->id.
                        '&search='.$search.'&rID='.$role->id.'">'.
                        get_string('unlinkfromqual', 'block_bcgt').'</a></li>';
                }
                $out .= '</ul>';
                $out .= '</td>';
                
                //now get the courses
                $out .= '<td>';
                $courses = bcgt_get_qual_courses($qual->id);
                if($courses)
                {
                    $out .= '<ul>';
                    foreach($courses AS $course)
                    {
                        $out .= '<li>'.$course->shortname.'</li>';
                    }
                    $out .= '</ul>';
                }
                $out .= '</td>';
                
                //Now get the course roles. 
                $out .= '<td>';
                $out .= '<ul>';
                if($courses)
                {
                    foreach($courses AS $course)
                    {
                        $out .= '<li>';
                        $roles = bcgt_get_users_course_roles($uID, $course->id);
                        if($roles)
                        {
                            foreach($roles AS $role)
                            {
                                $out .= $role->shortname.', ';
                            }
                        }
                        $out .= '</li>';
                    }
                }
                $out .= '</ul>';
                $out .= '</td>';

                $out .= '</tr>';
//            }
        }
        $out .= '</tbody>';
        $out .= '</table>';
    }
}

$out .= '</form>';

$out .= html_writer::end_tag('div');//end main column
$out .= html_writer::end_tag('div');//

$out .= '</div>';
$out .= $OUTPUT->footer();

echo $out;
?>
