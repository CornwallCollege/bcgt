<?php

/**
 * Assign personal tutors to:
 *  - Courses
 *  - Course Groups
 *  - Individual Students
 * 
 * All links to students are stored in role_assignments, so that capabilities can be used
 * All links to courses/groups are stored in lbp_tutor_assignments so that their list can be filtered by courses & groups
 * 
 * @copyright 2012 Bedford College
 * @package Bedford College Electronic Learning Blue Print (ELBP)
 * @version 1.0
 * @author Conn Warwicker <cwarwicker@bedford.ac.uk> <conn@cmrwarwicker.com>
 * 
 */

require_once '../../../config.php';
require_once $CFG->dirroot . '/blocks/bcgt/lib.php';

// Need to be logged in to view this page
require_login();


$type = optional_param('type', false, PARAM_ALPHA);

$courseID = SITEID; # Front Page

// Check course context is valid
$courseContext = get_context_instance(CONTEXT_COURSE, $courseID);
if (!$courseContext){
    print_error( get_string('invalidcourse', 'block_bcgt') );
}

$tutorRoleShortname = get_config('bcgt', 'tutorrole');
$role = $DB->get_record("role", array("shortname" => $tutorRoleShortname));

if (!$tutorRoleShortname || !$role){
    print_error( get_string('notutorrole', 'block_bcgt') );
}


// Set up PAGE
$PAGE->set_context( context_course::instance($courseID) );
$PAGE->set_url($CFG->wwwroot . '/blocks/bcgt/forms/assign_tutors.php?id='.$courseID);
$PAGE->set_title( get_string('assigntutors', 'block_bcgt') );
$PAGE->set_heading('heading');
$PAGE->set_cacheable(true);

// If course is set, put that into breadcrumb
$PAGE->navbar->add( get_string('pluginname', 'block_bcgt') , null, navigation_node::TYPE_CUSTOM);
$PAGE->navbar->add( get_string('assigntutors', 'block_bcgt') , null, navigation_node::TYPE_CUSTOM);

echo $OUTPUT->header();

$html = "";

// Define variables to be used in heredocs
$vars = array();
$vars['string_courses'] = get_string('assignbycourses', 'block_bcgt');
$vars['string_students'] = get_string('assignbystudent', 'block_bcgt');
$vars['string_remove'] = get_string('removetutorassignments', 'block_bcgt');
$vars['link_class']['courses'] = '';
$vars['link_class']['student'] = '';
$vars['link_class']['remove'] = '';
$vars['string_search'] = get_string('search', 'block_bcgt');
$vars['string_search_course'] = get_string('searchcourse', 'block_bcgt');
$vars['string_search_tutor'] = get_string('searchtutor', 'block_bcgt');
$vars['string_search_student'] = get_string('searchstudent', 'block_bcgt');
$vars['string_assign'] = get_string('assign', 'block_bcgt');
$vars['string_results'] = get_string('results', 'block_bcgt');
$vars['assign_button'] = "<input type='submit' name='submit_assign' value='{$vars['string_assign']}' />";


$html .= "<h2>".get_string('assignpt', 'block_elbp')."</h2>";

// Tab styles
$vars['link_class'][$type] = 'selected';
        
// Navigation tabs - Courses, Mentees
$html .= <<<HTML

   <ul class="elbp_tabrow">
        <li class="{$vars['link_class']['courses']}"><a href="assign_tutors.php?type=courses">{$vars['string_courses']}</a></li>
        <li class="{$vars['link_class']['student']}"><a href="assign_tutors.php?type=student">{$vars['string_students']}</a></li>
        <li class="{$vars['link_class']['remove']}"><a href="assign_tutors.php?type=remove">{$vars['string_remove']}</a></li>
    </ul>

HTML;
        

// Assign by an individual student
if ($type == 'student')
{
        
    $results = array();
    $results['tutor'] = '';
    $results['student'] = '';
    $results['assign'] = '';
    
    $searchTutor = optional_param('search_tutor', false, PARAM_TEXT);
    $searchStudent = optional_param('search_student', false, PARAM_TEXT);
    $selectedTutor = optional_param('select_tutors', false, PARAM_TEXT);
    $selectedStudent = optional_param('select_students', false, PARAM_TEXT);
    
    
    // Assign those selected
    
    if (isset($_POST['submit_assign']))
    {
        
        $error = false;
        
        if (!$selectedTutor || !$selectedStudent)
        {
            $results['assign'] = "<span>".get_string('plsselvalidtutorstudent', 'block_bcgt')."<br></span>";
            $error = true;
        }
                
        
        // If no errors, do it
        if (!$error)
        {
            
            $result = bcgt_add_mentee($selectedTutor, $selectedStudent);
            if ($result === true)
            {
                $results['assign'] = "<p style='color:blue;text-align:center;'>".get_string('tutorassignmentsuccessful', 'block_bcgt')."<br></p>";
            }
            else
            {
                $results['assign'] = "<p style='color:red;text-align:center;'>".get_string('tutorassignmentfailed', 'block_bcgt')."<br></p>";
            }
                        
        }
        
                
    }
    
    
    
    
    
                
    // Search for a tutor with this name
    if ($searchTutor)
    {

        // if we have an additional where clause to define staff, use that
        $tutorResults = $DB->get_records_sql("SELECT * FROM {user} WHERE username LIKE ? OR lastname LIKE ? OR firstname LIKE ? OR CONCAT(firstname, ' ', lastname) LIKE ? ORDER BY lastname ASC, firstname ASC", array(
            '%'.$searchTutor.'%',
            '%'.$searchTutor.'%',
            '%'.$searchTutor.'%',
            '%'.$searchTutor.'%'
        ));

        if ($tutorResults)
        {

            $results['tutor'] .= "<select name='select_tutors'>";
                foreach($tutorResults as $result)
                {
                    $results['tutor'] .= "<option value='{$result->id}' title='".fullname($result).", {$result->email}'>".fullname($result).", {$result->email}</option>";
                }
            $results['tutor'] .= "</select>";

        }

    }

    if ($searchStudent)
    {
        // if we have an additional where clause to define staff, use that
        $studentResults = $DB->get_records_sql("SELECT * FROM {user} WHERE username LIKE ? OR lastname LIKE ? OR firstname LIKE ? OR CONCAT(firstname, ' ', lastname) LIKE ? ORDER BY lastname ASC, firstname ASC", array(
            '%'.$searchStudent.'%',
            '%'.$searchStudent.'%',
            '%'.$searchStudent.'%',
            '%'.$searchStudent.'%'
        ));

        if ($studentResults)
        {

            $results['student'] .= "<select name='select_students'>";
                foreach($studentResults as $result)
                {
                    $results['student'] .= "<option value='{$result->id}' title='".fullname($result).", {$result->email}'>".fullname($result).", {$result->email}</option>";
                }
            $results['student'] .= "</select>";

        }

    }

        
            
    
    // Build form - Search for tutor & search for student
    $html .= <<<HTML
        <form action='' method='post'>
        <table style='width:100%;' class='elbp_centre'>
            <tr><th style='width:35%;'>{$vars['string_search_tutor']}</th><th style='width:30%;'></th><th style='width:35%;'>{$vars['string_search_student']}</th></tr>
            <tr>
                <td><input type='text' name='search_tutor' value='{$searchTutor}' /></td>
                <td><input type='submit' name='submit_search_by_student' value='{$vars['string_search']}' /></td>
                <td><input type='text' name='search_student' value='{$searchStudent}' /></td>
            </tr>
        </table>
        </form>
HTML;
            
    if (!empty($results['tutor']) || !empty($results['student']))
    {
        
        // If one of them has no results, don't display the assign button
        if (empty($results['tutor']) || empty($results['student'])){
            $vars['assign_button'] = '';
        }
        
        $html .= "<br><div>{$results['assign']}</div>";
        
        $html .= <<<HTML
            <form action='' method='post'>
            <table style='width:100%;' class='elbp_centre'>
                <tr><th style='width:35%;'><span>{$vars['string_results']}</span></th><th style='width:30%;'></th><th style='width:35%;'><span>{$vars['string_results']}</span></th></tr>
                <tr>
                    <td>{$results['tutor']}</td>
                    <td style='vertical-align:middle;'>{$vars['assign_button']}</td>
                    <td>{$results['student']}</td>
                </tr>
            </table>
            <input type='hidden' name='search_tutor' value='{$searchTutor}' />
            <input type='hidden' name='search_student' value='{$searchStudent}' />
            </form>
    
HTML;
                
    }
        
}


// Assign multiple courses to a tutor
elseif ($type == 'courses')
{
    
    
    $results = array();
    $results['tutor'] = '';
    $results['course'] = '';
    $results['assign'] = '';
    
    $searchTutor = optional_param('search_tutor', false, PARAM_TEXT);
    $searchCourse = optional_param('search_course', false, PARAM_TEXT);
    $selectedTutor = optional_param('select_tutors', false, PARAM_TEXT);
    $selectedCourse = optional_param('select_courses', false, PARAM_TEXT);
    
    
    // Assign those selected
    if (isset($_POST['submit_assign']))
    {
        
        $error = false;
        
        if (!$selectedTutor || !$selectedCourse)
        {
            $results['assign'] = "<span>".get_string('plsselvalidtutorcourse', 'block_bcgt')."<br></span>";
            $error = true;
        }
        
                        
        // If no errors, do it
        if (!$error)
        {
            
            $students = bcgt_get_students_on_course($selectedCourse);
            if ($students)
            {
                
                foreach($students as $student)
                {
                    
                    $result = bcgt_add_mentee($selectedTutor, $student->id);
                    if ($result === true)
                    {
                        $results['assign'] .= "<p style='color:blue;text-align:center;'>".get_string('tutorassignmentsuccessful', 'block_bcgt')." - ".fullname($student)." ({$student->username})<br></p>";
                    }
                    else
                    {
                        $results['assign'] .= "<p style='color:red;text-align:center;'>".get_string('tutorassignmentfailed', 'block_bcgt')." - ".fullname($student)." ({$student->username})<br></p>";
                    }
                    
                }
                
            }
            else
            {
                $results['assign'] .= "No Data";
            }
            
           
                        
        }
        
        
        
        
    }
    
    
    
    
    
    // Search for a tutor with this name
    if ($searchTutor)
    {

        // if we have an additional where clause to define staff, use that
        $tutorResults = $DB->get_records_sql("SELECT * FROM {user} WHERE username LIKE ? OR lastname LIKE ? OR firstname LIKE ? OR CONCAT(firstname, ' ', lastname) LIKE ? ORDER BY lastname ASC, firstname ASC", array(
            '%'.$searchTutor.'%',
            '%'.$searchTutor.'%',
            '%'.$searchTutor.'%',
            '%'.$searchTutor.'%'
        ));

        if ($tutorResults)
        {

            $results['tutor'] .= "<select name='select_tutors'>";
                foreach($tutorResults as $result)
                {
                    $results['tutor'] .= "<option value='{$result->id}' title='".fullname($result).", {$result->email}'>".fullname($result).", {$result->email}</option>";
                }
            $results['tutor'] .= "</select>";

        }

    }
    
    
    if ($searchCourse)
    {
        
        // if we have an additional where clause to define staff, use that
        $courseResults = $DB->get_records_sql("SELECT * FROM {course} WHERE shortname LIKE ? OR fullname LIKE ? ORDER BY shortname ASC, fullname ASC", array(
            '%'.$searchCourse.'%',
            '%'.$searchCourse.'%'
        ));

        if ($courseResults)
        {

            $results['course'] .= "<select name='select_courses'>";
                foreach($courseResults as $result)
                {
                    $results['course'] .= "<option value='{$result->id}' title='[{$result->shortname}] {$result->fullname}'>[{$result->shortname}] {$result->fullname}</option>";
                }
            $results['course'] .= "</select>";

        }
        
    }
    
    
    
    
    // Build form - Search for tutor & search for student
    $html .= <<<HTML
        <form action='' method='post'>
        <table style='width:100%;'>
            <tr><th style='width:35%;'>{$vars['string_search_tutor']}</th><th style='width:30%;'></th><th style='width:35%;'>{$vars['string_search_course']}</th></tr>
            <tr>
                <td><input type='text' name='search_tutor' value='{$searchTutor}' /></td>
                <td><input type='submit' name='submit_search_by_student' value='{$vars['string_search']}' /></td>
                <td><input type='text' name='search_course' value='{$searchCourse}' /></td>
            </tr>
        </table>
        </form>
HTML;
            
    if (!empty($results['tutor']) || !empty($results['course']))
    {
        
        // If one of them has no results, don't display the assign button
        if (empty($results['tutor']) || empty($results['course'])){
            $vars['assign_button'] = '';
        }
        
        $html .= "<br><div>{$results['assign']}</div>";
        
        $html .= <<<HTML
            <form action='' method='post'>
            <table style='width:100%;'>
                <tr><th style='width:35%;'><span>{$vars['string_results']}</span></th><th style='width:30%;'></th><th style='width:35%;'><span>{$vars['string_results']}</span></th></tr>
                <tr>
                    <td>{$results['tutor']}</td>
                    <td style='vertical-align:middle;'>{$vars['assign_button']}</td>
                    <td>{$results['course']}</td>
                </tr>
            </table>
            <input type='hidden' name='search_tutor' value='{$searchTutor}' />
            <input type='hidden' name='search_course' value='{$searchCourse}' />
            </form>
    
HTML;
                
    }
    
    
}


elseif ($type == 'remove')
{
    
    
    $results = array();
    $results['tutor'] = '';
    $results['students'] = '';
    $results['assign'] = '';
    
    $searchTutor = optional_param('search_tutor', false, PARAM_TEXT);
    $loadStudents = optional_param('load_students', false, PARAM_TEXT);
    $selectedTutor = optional_param('select_tutors', false, PARAM_TEXT);
    $confirmRemoveStudents = optional_param('submit_remove_students', false, PARAM_TEXT);
    $removeStudents = optional_param_array('remove_students', false, PARAM_INT);
    
    if ($confirmRemoveStudents && $selectedTutor){
        
        foreach($removeStudents as $studentID)
        {

            $student = $DB->get_record("user", array("id" => $studentID));
            
            if ($student)
            {
                $result = bcgt_remove_mentee($selectedTutor, $student->id);
                
                // role_unassign returns false so can't check if it worked or not
                $results['assign'] .= "<p style='color:blue;text-align:center;'>".get_string('tutorunassignmentsuccessful', 'block_bcgt')." - ".fullname($student)." ({$student->username})<br></p>";
                
            }

        }
        
        $loadStudents = true;
        
    }
    
    
    
    
    // Search for a tutor with this name
    if ($searchTutor)
    {

        // if we have an additional where clause to define staff, use that
        $tutorResults = $DB->get_records_sql("SELECT * FROM {user} WHERE username LIKE ? OR lastname LIKE ? OR firstname LIKE ? OR CONCAT(firstname, ' ', lastname) LIKE ? ORDER BY lastname ASC, firstname ASC", array(
            '%'.$searchTutor.'%',
            '%'.$searchTutor.'%',
            '%'.$searchTutor.'%',
            '%'.$searchTutor.'%'
        ));

        if ($tutorResults)
        {

            $results['tutor'] .= "<select name='select_tutors'>";
                foreach($tutorResults as $result)
                {
                    $results['tutor'] .= "<option value='{$result->id}' title='".fullname($result).", {$result->email}'>".fullname($result).", {$result->email}</option>";
                }
            $results['tutor'] .= "</select>";
            $results['tutor'] .= "<br>";
            
            if (count($tutorResults) == 1){
                $loadStudents = true;
                $tutor = reset($tutorResults);
                $selectedTutor = $tutor->id;
            } else {
                $results['tutor'] .= "<input type='submit' name='load_students' value='Load' />";
            }

        }

    }
    
    
    if ($loadStudents)
    {
        
        $students = bcgt_get_students_on_tutor($selectedTutor);
        if ($students)
        {
            
            $results['students'] = '<table>';
            $results['students'] .= '<tr><th>Remove</th><th>Student Name</th></tr>';
                    
            foreach($students as $student)
            {
                
                $results['students'] .= '<tr>';
                
                    $results['students'] .= '<td><input type="checkbox" name="remove_students[]" value="'.$student->id.'" /></td>';
                    $results['students'] .= '<td>'.fullname($student).' ('.$student->username.')</td>';                    
                
                $results['students'] .= '</tr>';
                
            }
            
            $results['students'] .= '</table>';
            $results['students'] .= '<br><br>';
            $results['students'] .= '<input type="submit" name="submit_remove_students" value="Remove" />';
            
        }
        else
        {
            $results['students'] = 'No Data';
        }
        
    }
    
    
    
    
    
    
    
    
    $html .= "<br><div>{$results['assign']}</div>";
        
    // Build form - Search for tutor & search for student
    $html .= <<<HTML
        <form action='' method='post' class='c'>
        <b>{$vars['string_search_tutor']}</b><br>
        <input type='text' name='search_tutor' value='{$searchTutor}' />                
        <br><br>
        <input type='submit' name='submit_search_by_tutor' value='{$vars['string_search']}' />
        <br><br>
        {$results['tutor']}
        <br><hr><br>
        {$results['students']}
        </form>
HTML;
    
    
}




$html .= "<br><br>";

echo $html;
echo $OUTPUT->footer();