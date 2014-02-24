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
require_capability('block/bcgt:editstudentqual', $context);
$totalAddStudents = isset($_SESSION['new_students'])? 
    unserialize(urldecode($_SESSION['new_students'])) : array();
$addStudents = isset($_POST['addselect'])? $_POST['addselect'] : '';
$qualID = optional_param('qID', -1, PARAM_INT);
if(isset($_POST['studentsUnits']))
{
    redirect('edit_students_units.php?qID='.$qualID);
}
$qualification = null;
$url = '/blocks/bcgt/forms/edit_qual_stu.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->set_heading(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
$PAGE->navbar->add(get_string('myDashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$searchOn = optional_param('searchOn', '', PARAM_TEXT);
$searchStud = optional_param('searchChoose', '', PARAM_TEXT);
$students = null;
$studentRole = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = ?', array('student'));
if($qualID != -1)
{
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
    if($qualification)
    {
        //then we are adding the qual to the teacher
        if(isset($_POST['add']))
        {
            if($addStudents)
            {
                $totalAddStudents = array_merge($addStudents, $totalAddStudents);
                $qualification->add_users($addStudents, $studentRole->id, true);
                $_SESSION['new_students'] = urlencode(serialize($totalAddStudents));
            }
        }
        elseif(isset($_POST['remove']))
        {
            $removeStudents = isset($_POST['removeselect'])? $_POST['removeselect'] : '';
            if($removeStudents)
            {
                $qualification->remove_users($removeStudents, $studentRole->id, true);
            }
        }
        $students = $qualification->get_users($studentRole->id, $searchOn);
    }
}
$users = get_users_not_on_qual($qualID, $studentRole->id, $searchStud);
$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initqualstud', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();

$out = $OUTPUT->header();
$out .= html_writer::tag('h2', get_string('qualstudentheading','block_bcgt'), 
        array('class'=>'formheading'));
    $out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_controls', 
    'id'=>'editQualStudents'));
    $heading = '';
    if($qualification)
    {
        $heading .= $qualification->get_type().''. 
        ' '.$qualification->get_level()->get_level().''. 
        ' '.$qualification->get_subType()->get_subType();
        $heading .= ' '.$qualification->get_name().'<br />';
    }
    $out .= html_writer::tag('h3', $heading, 
            array('class'=>'subformheading'));
		$out .= '<form method="POST" name="qualStudentForm" action="edit_qual_stu.php">';	
        $out .= '<input type="hidden" id="qID" name="qID" value="'.$qualID.'"/>';  
        $out .= '<input type="hidden" name="cID" value="'.$courseID.'"/>';
        $out .= html_writer::start_tag('div', array('class'=>'bcgt_three_c_container bcgt_float_container', 
            'id'=>'bcgtInnerConainer'));	
        $out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_left bcgt_col_one bcgt_col'));
        $out .= html_writer::tag('h3', get_string('qualstudents','block_bcgt'), 
        array('class'=>'subformheading'));
            $out .= '<select name="removeselect[]" size="20" id="removeselect" multiple="multiple">';
                if($students)
                {
                    foreach($students AS $student)
                    {
                        $out .= '<option value="'.$student->id.'" title="'.$student->username.'">'.
                                $student->firstname.'  '.$student->lastname.' : '.$student->email.'</option>';	
                    }
                }
            $out .= '</select><br />'; 
            $out .= '<input type="text" name="searchOn" id="searchOn" value="'.$searchOn.'"/>';
            $out .= '<input id="searchStuOn" name="search" type="submit" value="'.get_string('search','block_bcgt').'"/><br />';
				
        $out .= html_writer::end_tag('div');
        $out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_center bcgt_col_two bcgt_col'));
            $out .= '<p class="arrow_button">';
                $out .= '<input name="add" id="addStu" type="submit" disabled="disabled" value="'.get_string('add','block_bcgt').'"/><br />';
                $out .= '<input name="remove" id="removeStu" type="submit" disabled="disabled" value="'.get_string('remove','block_bcgt').'"/><br />';
            $out .= '</p>';
        $out .= html_writer::end_tag('div');
        
        $out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_right bcgt_col_three bcgt_col'));
        $out .= html_writer::tag('h3', get_string('studentchoose','block_bcgt'), 
            array('class'=>'subformheading'));
        $out .= '<select name="addselect[]" size="20" id="addselect" multiple="multiple">';
            if($users)
            {
                foreach($users AS $user)
                {
                    $out .= '<option value="'.$user->id.'" title="'.$user->username.'">'.
                        $user->firstname.'  '.$user->lastname.' : '.$user->email.'</option>';	
                }
            }
        $out .= '</select><br />';
            $out .= '<input type="text" name="searchChoose" id="searchChoose" value="'.$searchStud.'"/>';
            $out .= '<input id="searchStu" name="searchStu" type="submit" value="'.get_string('search','block_bcgt').'"/><br />';
        $out .= html_writer::end_tag('div');//end third colymn
        $out .= html_writer::end_tag('div');//end third colymn	
            $out .= '<input type="submit" name="qualPicker" value="'.get_string('back','block_bcgt').'" />';
			$out .= '<input type="submit" name="studentsUnits" value="'.get_string('step2editstuunits','block_bcgt').'"/>';
        $out .= '</form>';
	$out .= html_writer::end_tag('div');
$out .= $OUTPUT->footer();
echo $out;

?>
