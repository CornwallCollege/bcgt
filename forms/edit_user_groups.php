<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 * 
 * 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
//if the user being looked at is not the user looking
//do they have access to actually do it.
//e.g. do a check. 
 */


global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;;
require_once('../../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

$uID = optional_param('uID', -1, PARAM_INT);
if($uID == -1)
{
    $uID = $USER->id;
}
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
if($uID != $USER->id)
{
    //then we are looking at another user's groups. 
    //do we have the capibility to edit another users groups?
    require_capability('block/bcgt:manageusersgroups', $context);
}
$url = '/blocks/bcgt/forms/edit_user_groups.php';
$urlGroups = '/blocks/bcgt/forms/my_dashboard.php?tab=group&cID='.$cID;
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('grouppreferences', 'block_bcgt'));
$PAGE->set_heading(get_string('grouppreferences', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('grouppreferences', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),$urlGroups,'title');
$PAGE->navbar->add(get_string('grouppreferences', 'block_bcgt'),null,'title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.grouppreferences', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();

$out .= html_writer::tag('h2', get_string('grouppreferences','block_bcgt').
        '', 
        array('class'=>'formheading'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_grouppreferences_controls', 
    'id'=>'editGroupPreferences'));

$out .= '<form name="editGroupPrefs" action"#" method="POST" class="bcgt_form" id="editGroupPrefsForm"/>';
$group = new Group();
//$userGroupPrefs = $group->get_user_possible_groups($uID);
$out .= '<input type="submit" class="save_input" name="save" value="'.get_string('save', 'block_bcgt').'"/>';
$out .= '<table class="bcgt_table bcgt_group_prefs" align="center">';
$out .= '<thead>';
$out .= '<tr class="bcgt_table_header"><th>'.get_string('course').'</th><th>'.get_string('groupswithgroupsmembers', 'block_bcgt').'</th></tr>';
$out .= '</thead>';
$out .= '<tbody>';
$usersCourses = bcgt_get_users_courses_any_role($uID, true);
if($usersCourses)
{
    $grouping = new Grouping();
    foreach($usersCourses AS $usersCourse)
    {
        $courseGroupings = $grouping->get_all_groupings($usersCourse->id);
        if(!$courseGroupings)
        {
            $out .= '<tr class="no_groups">';
        }
        else
        {
            $out .= '<tr>';
        }
        
        $out .= '<td class="bcgtcoursename">';
        $out .= $usersCourse->shortname;
        $out .= '</td>';
        $out .= '<td class="bcgtgrouppref">';
        
        if($courseGroupings)
        {
            $out .= '<table class="bcgt_table bcgt_group_pref_inner" id="">';
            foreach($courseGroupings AS $courseGrouping)
            {
                if(isset($_POST['save']))
                {
                    //then lets save it. 
                    if(isset($_POST[$courseGrouping->id]))
                    {
                        //save it
                        $group->add_user_bcgt_group_pref($courseGrouping->id, $usersCourse->id, $uID);
                    }
                    else
                    {
                        $group->remove_user_bcgt_group_pref($courseGrouping->id, $uID);
                    }
                }
                $out .= '<tr>';
                $countGroups = $grouping->count_groups_in_grouping($courseGrouping->id);
                $countUsers = $grouping->count_users_in_grouping($courseGrouping->id);
                $out .= '<td class="bcgtgroupname">'.$courseGrouping->name.
                        '<span class="countmembers">['.$countGroups.']['.$countUsers.']</span></td>';
                $checked = '';
                if($group->get_user_bcgt_group_prefs(-1, $courseGrouping->id, $usersCourse->id, $uID))
                {
                    $checked = 'checked="checked"';
                }
                $out .= '<td class="bcgtpref"><input type="checkbox" '.$checked.' class="'.$courseGrouping->name.
                        '" name="'.$courseGrouping->id.'" value="'.$courseGrouping->id.'"/></td>';
                $out .= '</tr>';
            }
            $out .= '</table>';
        }
        else
        {
            $out .= '<table class="bcgt_table bcgt_group_pref_inner">';
            $out .= '<tr class="nogroups"><td colspan="2">'.get_string('nogroupings', 'block_bcgt').'</td></tr>';
            $out .= '</table>';
        }
        $out .= '</td>';
        $out .= '</tr>';
    }
}
else
{
    $out .= '<p>'.get_string('nousergroupings', 'block_bcgt').'</p>';
}
$out .= '</tbody>';
$out .= '</table>';
$out .= '<input type="submit" class="save_input" name="save" value="'.get_string('save', 'block_bcgt').'"/>';
$out .= '</form>';
$out .= html_writer::end_tag('div');//end main column
$out .= $OUTPUT->footer();

echo $out;
?>

