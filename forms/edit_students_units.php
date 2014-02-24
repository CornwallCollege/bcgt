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
$originalCourseID = optional_param('oCID', -1, PARAM_INT);
if($originalCourseID != -1)
{
    $context = context_course::instance($originalCourseID);
}
else
{
    $context = context_course::instance($COURSE->id);
}
require_login();
$PAGE->set_context($context);
$qualID = optional_param('qID', -1, PARAM_INT);
$action = optional_param('a', 'q', PARAM_TEXT);
$courseID = optional_param('cID', -1, PARAM_INT);
$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELUNITS;
if($action == 'q' && $qualID != -1)
{
    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
    if($qualification)
    {
        $qualification->process_edit_students_units_page(); 
    }
}
require_capability('block/bcgt:editstudentunits', $context);
$string = 'studentsunitheadingstage';
if($action == 'u')
{
    $string = 'studentsunitheading';
}
//$newStudents = isset($_SESSION['new_students'])? 
//    unserialize(urldecode($_SESSION['new_students'])) : array();
$url = '/blocks/bcgt/forms/edit_students_units.php';

$PAGE->set_url($url, array());
$PAGE->set_title(get_string($string, 'block_bcgt'));
$PAGE->set_heading(get_string($string, 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string($string, 'block_bcgt'));
$PAGE->navbar->add(get_string('myDashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
if($courseID != -1)
{
    $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($courseID));
    $PAGE->navbar->add($course->shortname,$CFG->wwwroot.'/course/view.php?id='.$courseID,'title');
    $PAGE->navbar->add(get_string('editcoursequal','block_bcgt'), $CFG->wwwroot.
            '/blocks/bcgt/forms/edit_course_qual.php?oCID='.
            $originalCourseID.'&cID='.$courseID,'title');
    
}
$PAGE->navbar->add(get_string($string, 'block_bcgt'));

require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();

    $out .= html_writer::tag('h2', get_string($string,'block_bcgt'), 
        array('class'=>'formheading'));
    $out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_controls', 
    'id'=>'editStudentsUnits'));
    if($action == 'q' && $qualID != -1)
    {   
        //one qualification and all of the students units
        $out .= '<form method="POST" name="qualStudentForm" action="edit_students_units.php">';	       
        $out .= '<input type="hidden" name="oCID" value="'.$originalCourseID.'"/>';
        $out .= $qualification->get_edit_students_units_page();
        $out .= '</form>';
    }
    elseif($action == 'c' && $courseID != -1)
    {
        //one course and all of the quals and units
        //find all of the quals that are on the course. 
        $quals = bcgt_get_course_quals($courseID);
        if($quals)
        {
            $out .= '<form method="POST" name="qualStudentForm" action="edit_students_units.php">';	  
            $out .= '<input type="submit" name="saveAll" value="Save All"/>';
            $out .= '<input type="hidden" name="oCID" value="'.$originalCourseID.'"/>';
            $currentQualTypes = array();
            foreach($quals AS $qual)
            {
                $qualification = Qualification::get_qualification_class_id($qual->id, $loadParams);
                if($qualification)
                {
                    $qualType = $qualification->get_class_ID();
                    if(!array_key_exists($qualType, $currentQualTypes))
                    {
                        //then load the javascript initilisation call
                        $qualification->get_edit_student_page_init_call();
                        $currentQualTypes[$qualType] = 1;
                        $currentCount = 1;
                    }
                    else
                    {
                        $currentCount = $currentQualTypes[$qualType] + 1;
                        $currentQualTypes[$qualType] = $currentCount;
                    }
                    $qualification->process_edit_students_units_page($courseID);
                    $out .= $qualification->get_edit_students_units_page($courseID, true, $currentCount, $action);
                }
            }
            $out .= '<input type="submit" name="saveAll" value="Save All"/>';
            $out .= '</form>';
        }
    }
    elseif($action == 'ms')
    {
        //for many students, all of their quals and all of their units
        
        $out .= '<form method="POST" name="" action="edit_students_units.php">';
        $out .= '<input type="hidden" name="oCID" value="'.$originalCourseID.'"/>';
        //so we have many students and many qualifications. 
        $newStudentsQuals = isset($_SESSION['new_quals'])? 
            unserialize(urldecode($_SESSION['new_quals'])) : array();
        //for each student
            //find the new quals. 
                //load an object up
                    //get the singular row.
        $currentQualTypes = array();
        $currentCount = 0;
        $out .= '<input type="submit" name="saveAll" value="Save All"/>';
        $out .= '<input type="hidden" name="a" value="'.$action.'"/>';
        if($newStudentsQuals)
        {
            foreach($newStudentsQuals AS $studentID => $newStudentQuals)
            { 
                $user = $DB->get_record_sql("SELECT * FROM {user} WHERE id = ?", array($studentID));
                if($user)
                {
                    $out .= '<h3>'.$user->firstname.' '.$user->lastname.'</h3>';
                }
                foreach($newStudentQuals AS $qualID)
                {
                    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
                    if($qualification)
                    {
                        $qualType = $qualification->get_class_ID();
                        if(!array_key_exists($qualType, $currentQualTypes))
                        {
                            //then load the javascript initilisation call
                            $qualification->get_edit_single_student_units_init_call();
                            $currentQualTypes[$qualType] = 1;
                            $currentCount = 1;
                        }
                        else
                        {
                            $currentCount = $currentQualTypes[$qualType] + 1;
                            $currentQualTypes[$qualType] = $currentCount;
                        }
                        if($qualification)
                        {
                            $qualification->load_student_information($studentID, $loadParams, false);
                            $out .= $qualification->get_edit_single_student_units($currentCount);
                        }
                    }
                    
                }
            }
        }
        $out .= '<input type="submit" name="saveAll" value="Save All"/>';
        $out .= '</form>';
    }
    elseif($action == 's')
    {
        //one student and all of the units they can be doing
        $users = null;
        $user = null;
        $sSearch = optional_param('studentSearch', '', PARAM_TEXT);
        $sID = optional_param('sID', -1, PARAM_INT);
        if(isset($_POST['clear']))
        {
            $sSearch = '';
            $sID = -1;
        }
        if($sSearch != '')
        {
            $users = get_users_bcgt($sSearch, $sID);
        }
        if(count($users) == 1)
        {
            $user = end($users);
            $sID = $user->id;
        }
        if($sID != -1 || $user)
        {
            $user = $DB->get_record_sql("SELECT * FROM {user} WHERE id = ?", array($sID));
            $out .= '<h2>'.$user->username.' : '.$user->firstname.' '.$user->lastname.'</h2>';
        }
        $out .= '<form method="POST" name="" action="edit_students_units.php">'; 
        $out .= '<input type="hidden" name="oCID" value="'.$originalCourseID.'"/>';
        $out .= '<input type="hidden" name="a" value="'.$action.'"/>';
        $out .= '<input type="text" name="studentSearch" value="'.$sSearch.'"/>';
        $out .= '<input type="hidden" name="sID" value="'.$sID.'"/>';
        $out .= '<input type="submit" name="search" value="Student Search"/>';
        $out .= '<input type="submit" name="clear" value="Clear Selection"/>';
        if($user)
        {
            $currentQualTypes = array();
            $currentCount = 0;
            //find all of the user qualifications.
            $quals = get_role_quals($user->id, 'student');
            if($quals)
            {
                $out .= '<input type="submit" name="saveAll" value="Save All"/>';
                foreach($quals AS $qual)
                {
                    $qualification = Qualification::get_qualification_class_id($qual->id, $loadParams);
                    if($qualification)
                    {
                        $qualType = $qualification->get_class_ID();
                        if(!array_key_exists($qualType, $currentQualTypes))
                        {
                            //then load the javascript initilisation call
                            $qualification->get_edit_single_student_units_init_call();
                            $currentQualTypes[$qualType] = 1;
                            $currentCount = 1;
                        }
                        else
                        {
                            $currentCount = $currentQualTypes[$qualType] + 1;
                            $currentQualTypes[$qualType] = $currentCount;
                        }
                    }
                    if($qualification)
                    {
                        $qualification->load_student_information($user->id, $loadParams, false);
                        $out .= $qualification->get_edit_single_student_units($currentCount);
                    }
                }
                $out .= '<input type="submit" name="saveAll" value="Save All"/>';
            }
            else
            {
                $out .= '<table><tr>';
//                $out .= '<td>'.$OUTPUT->user_picture($user, array(1)).'</td>';
                $out .= '<td></td>';
                $out .= '<td>'.$user->username.'</td>'.
                        '<td>'.$user->firstname.'</td>'.
                        '<td>'.$user->lastname.'</td>';
                $out .= '<td>Has No Qualifications</td></tr></table>';
            }
        }
        elseif($users) {
            $out .= '<table align="center">';
            foreach($users AS $user)
            {
                $out .= '<tr><td>'.$OUTPUT->user_picture($user, array(1)).'</td>';
                $out .= '<td>'.$user->username.'</td>'.
                        '<td>'.$user->firstname.'</td>'.
                        '<td>'.$user->lastname.'</td>';
                $out .= '<td><a href="edit_students_units.php?a=s&sID='.
                                $user->id.'&oCID='.$originalCourseID.'">Select</a></td></tr>';
            }
            $out .= '</table>';
        
        }
        else
        {
            $out .= '<p>Please search for a student</p>';
        }
        $out .= '</form>';
    }
    elseif($action == 'mu')
    {
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELMIN;
        $completeNewAdditions = isset($_SESSION['new_quals'])? 
            unserialize(urldecode($_SESSION['new_quals'])) : array();
        foreach($completeNewAdditions AS $unitID => $quals)
        {
            $unit = Unit::get_unit_class_id($unitID, $loadParams);
            if($unit)
            {
                $out .= $unit->get_family_name().' : '.$unit->get_level()->get_level().
                    ' : '.$unit->get_uniqueID().' : '.$unit->get_name();
                $out .= '<table>';
                foreach($quals AS $qualID)
                {
                    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
                    if($qualification)
                    {
                        $out .= '<tr>';
                        $out .= '<td>'.$qualification->get_family().'</td>';
                        $out .= '<td>'.$qualification->get_level()->get_level().'</td>';
                        $out .= '<td>'.$qualification->get_subtype()->get_subtype().'</td>';
                        $out .= '<td>'.$qualification->get_name().'</td>';
                        $out .= '<td><a href="edit_students_units.php?a=q&qID='.$qualID.'&oCID='.$originalCourseID.'">'.get_string('select').'</a></td>';
                        $out .= '</tr>';
                    }
                }
                $out .= '</table>';
            }
        }
        //so we are looking for a single unit and we need to know what it was on. 
        //load the unit
        //for each unit show the new quals
        //then allow the user to select through to the usual qual unit grid. 
    }
    elseif($action == 'u')
    {
        //
        $units = null;
        $unit = null;
        $uSearch = optional_param('unitSearch', '', PARAM_TEXT);
        $uID = optional_param('uID', -1, PARAM_INT);
        $out .= '<form method="POST" name="" action="edit_students_units.php">'; 
        $out .= '<input type="hidden" name="oCID" value="'.$originalCourseID.'"/>';
        $out .= '<input type="hidden" name="a" value="'.$action.'"/>';
        $out .= '<input type="text" name="unitSearch" value="'.$uSearch.'"/>';
        $out .= '<input type="submit" name="search" value="'.get_string('unitsearch', 'block_bcgt').'"/>';
        if($uID == -1 && $uSearch != '')
        {
            $units = search_unit(-1, -1, $uSearch);
        }
        elseif($uID != -1)
        {
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELMIN;
            $unit = Unit::get_unit_class_id($uID, $loadParams);
        }
        if(count($units) == 1 || $unit)
        {
            if(count($units) == 1 && !$unit)
            {
                $unit = end($unit);
                $unit = Unit::get_unit_class_id($unit->id, $loadParams);
            }
            //find all of the user qualifications.
            $quals = $unit->get_quals_on();
            if($quals)
            {
                $out .= '<table>';
                foreach($quals AS $qual)
                {
                    $qualification = Qualification::get_qualification_class_id($qual->id);
                    if($qualification)
                    {
                        $out .= '<tr>';
                        $out .= '<td>'.$qualification->get_family().'</td>';
                        $out .= '<td>'.$qualification->get_level()->get_level().'</td>';
                        $out .= '<td>'.$qualification->get_subtype()->get_subtype().'</td>';
                        $out .= '<td>'.$qualification->get_name().'</td>';
                        $out .= '<td><a href="edit_students_units.php?a=q&qID='.$qual->id.'&oCID='.$originalCourseID.'">'.get_string('select').'</a></td>';
                        $out .= '</tr>';
                    }
                }
                $out .= '</table>';
            }
            else
            {
                $out .= '<table><tr>';
                $out .= '<td>'.$unit->get_family_name().'</td>'.
                        '<td>'.$unit->get_level()->get_level().'</td>'.
                        '<td>'.$unit->get_uniqueID().'</td>'.
                        '<td>'.$unit->get_name().'</td>';
                $out .= '<td>Is not attached to any Qualifications</td></tr></table>';
            }
        }
        elseif($units) {
            $out .= '<table align="center">';
            foreach($units AS $unit)
            {
                $out .= '<td>'.$unit->family.'</td>'.
                        '<td>'.$unit->unitlevel.'</td>'.
                        '<td>'.$unit->uniqueid.'</td>'.
                        '<td>'.$unit->name.'</td>';
                $out .= '<td><a href="edit_students_units.php?a=u&uID='.
                                $unit->id.'&oCID='.$originalCourseID.'">'.get_string('select').'</a></td></tr>';
            }
            $out .= '</table>';
        
        }
        else
        {
            $out .= '<p>Please search for a Unit</p>';
        }
        $out .= '</form>';
    }
    $out .= html_writer::end_tag('div');
$out .= $OUTPUT->footer();
echo $out;

?>
