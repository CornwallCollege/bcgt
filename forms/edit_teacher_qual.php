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
require_capability('block/bcgt:editteacherqual', $context);
$url = '/blocks/bcgt/forms/edit_teacher_qual.php';
$string = 'editqualteacherheading';
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

$usersNotOn = null;
$teachersOn = null;
$searchOn = optional_param('searchOn', '', PARAM_TEXT);
$searchAdd = optional_param('searchAdd', '', PARAM_TEXT);
$qualID = optional_param('qID', -1, PARAM_TEXT);
if($qualID != -1)
{
    $roleDB = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = ?', array('teacher'));
    //load the qual and find the teachers that are on it
    $qualification = Qualification::get_qualification_class_id($qualID);
    if($qualification)
    {
        if(isset($_POST['add']))
        {
            $addTeachers = isset($_POST['addselect'])? $_POST['addselect'] : '';
            if($addTeachers)
            {
                $qualification->add_users($addTeachers, $roleDB->id);
            }
        }
        elseif(isset($_POST['remove']))
        {
            $removeTeachers = isset($_POST['removeselect'])? $_POST['removeselect'] : '';
            if($removeTeachers)
            {
                $qualification->remove_users($removeTeachers, $roleDB->id);
            }
        }
        
        $teachersOn = $qualification->get_users($roleDB->id, $searchOn);
    }
    //find teachers not on the qualification
    //find the teacher role. 
    $usersNotOn = get_users_not_on_qual($qualID, $roleDB->id, $searchAdd);
}

$searchResults = search_qualification(-1, $levelID, $subTypeID, $search, $familyID);
$families = get_qualification_type_families_used();
$subTypes = get_subtype_from_type(-1, $levelID, $familyID);
$levels = get_level_from_type(-1, $familyID);   

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initqualteachers', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();

$out = $OUTPUT->header();

$out .= html_writer::tag('h2', get_string('editqualteacherheading','block_bcgt'), 
        array('class'=>'formheading'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_controls', 
    'id'=>'editQualTeachers'));
$out .= '<form name="editQualTeacher" action="edit_teacher_qual.php" method="POST" id="editQualTeacher">';
$out .= '<input type="hidden" name="cID" value="'.$courseID.'"/>';
$out .= html_writer::start_tag('div', array('class'=>'bcgt_two_c_container bcgt_float_container', 
    'id'=>'bcgtColumnConainer'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_left bcgt_col_one bcgt_col'));
    $out .= html_writer::tag('h3', get_string('qualselectheading','block_bcgt'), 
        array('class'=>'subformheading'));
    $out .= '<select name="qID" id="qualSelect" size="10">';
        if($searchResults)
        {
            foreach($searchResults AS $result)
            {
                $selected = '';
                $coursesCount = 0;
                if($result->countcourse)
                {
                    $coursesCount = $result->countcourse;
                }
                $unitsCount = 0;
                if($result->countunits)
                {
                    $unitsCount = $result->countunits;
                }
                if($result->id == $qualID)
                {
                    $selected = 'selected';
                }
                $out .= "<option $selected value='$result->id'".
                    "title=' $coursesCount Courses --- $unitsCount ".
                    "Units '>$result->family $result->trackinglevel ".
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
$out .= html_writer::end_tag('div');
$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_right bcgt_col_two bcgt_col'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_three_c_container bcgt_float_container', 
    'id'=>'bcgtInnerConainer'));

$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_left bcgt_col_one bcgt_col'));
    $out .= html_writer::tag('h3', get_string('teachersquals','block_bcgt'), 
        array('class'=>'subformheading'));
        $out .= '<select name="removeselect[]" size="20" id="removeselect" multiple="multiple">';
        if($teachersOn)
        {
            foreach($teachersOn AS $user)
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
            '<input type="text" name="searchOn" id="searchOn" value="'.$searchOn.'"/>'.
            '</div></div>';
    $out .= '<input type="submit" name="search" value="Search"/>';
    //the teachers on the qual
$out .= html_writer::end_tag('div');

$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_center bcgt_col_two bcgt_col'));
$out .= '<input name="add" id="addTeacher" type="submit" disabled="disabled" value="'.get_string('add','block_bcgt').'"/><br />';
$out .= '<input name="remove" id="removeTeacher" type="submit" disabled="disabled" value="'.get_string('remove','block_bcgt').'"/><br />';
$out .= html_writer::end_tag('div');

$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_right bcgt_col_three bcgt_col'));
    $out .= html_writer::tag('h3', get_string('teacherschooseheading','block_bcgt'), 
            array('class'=>'subformheading'));
        //the teachers to choose from
        $out .= '<select name="addselect[]" size="20" id="addselect" multiple="multiple">';
        if($usersNotOn)
        {
            foreach($usersNotOn AS $user)
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
            '<input type="text" name="searchAdd" id="searchAdd" value="'.$searchAdd.'"/>'.
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
