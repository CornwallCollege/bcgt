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
$courseID = optional_param('cID', -1, PARAM_INT);
if(isset($_POST['save']))
{
    //loop over all of the users, quals and see if the user is now doing them. 
    bcgt_process_course_qual_users($courseID);
}
require_capability('block/bcgt:editqualscourse', $context);
$url = '/blocks/bcgt/forms/edit_course_qual.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('edituserscoursequal', 'block_bcgt'));
$PAGE->set_heading(get_string('edituserscoursequal', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('edituserscoursequals', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
if($courseID != -1)
{
    $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($courseID));
    if($course)
    {
        $PAGE->navbar->add($course->shortname,$CFG->wwwroot.'/course/view.php?id='.$courseID,'title');
    }
    $PAGE->navbar->add(get_string('editcoursequal','block_bcgt'), $CFG->wwwroot.
            '/blocks/bcgt/forms/edit_course_qual.php?oCID='.
            $originalCourseID.'&cID='.$courseID,'title');
    
}
$PAGE->navbar->add(get_string('editcoursequalusers', 'block_bcgt'),null,'title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initcoursequalsusers', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();
//get the courses qualifications

//get the courses students, by child course. 

$currentQuals = bcgt_get_course_quals($courseID);
$users = bcgt_get_course_students($courseID);
$heading = '';
$course = $DB->get_record_select('course', 'id = ?', array($courseID));
if($course)
{
    $heading = ''.$course->shortname.' : '.$course->fullname;
}
$out .= html_writer::tag('h2', get_string('editcoursequalusers','block_bcgt'), 
        array('class'=>'formheading'));
$out .= html_writer::tag('h2', $heading, 
        array('class'=>'formheading'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_controls', 
    'id'=>'editCourseQualUsers'));

$out .= '<form name="editCourseQual" action="edit_course_qual_user.php" method="POST" id="editCourseQualForm">';
$out .= '<input type="submit" name="save" value="'.get_string('save', 'block_bcgt').'"/>';
$out .= '<input type="hidden" name="cID" value="'.$courseID.'"/>';
$out .= '<input type="hidden" name="oCID" value="'.$originalCourseID.'"/>';

$out .= '<p><img src="'.$CFG->wwwroot.'/blocks/bcgt/images/linksymbol.jpg"/> = '.get_string('linkedonotherquals', 'block_bcgt');
$context = context_course::instance($COURSE->id);
if(has_capability('block/bcgt:checkuseraccess', $context))
{
    $out .= ' '.get_string('viewusersaccessquals', 'block_bcgt');
}
$out .= '</p>';

$out .= '<table id="courseQualUserTable1" class="courseQualUserTable">';
$header = '<thead><tr><th>'.get_string('enrolment', 'block_bcgt').'</th><th></th><th>'.get_string('username').
        '</th><th>'.get_string('name').'</th><th></th>';
$staffHeader = '';
$headerStu = '';
if($currentQuals)
{
    foreach($currentQuals AS $qual)
    {
        
        //Select all Students for this Qual
        $headerStu .= '<th>'.(isset($qual->displaytype) ? $qual->displaytype : $qual->type).'<br />'.( isset($qual->level) ? 'Level ' . $qual->level : $qual->trackinglevel ).'<br />'.
                $qual->subtype.'<br />'.$qual->name.'<br /><br />'.
                '<a href="edit_course_qual_user?cID='.$courseID.'" 
                    title="'.get_string('selectallstudentsqual', 'block_bcgt').'">'.
                        '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowdown.jpg"'. 
                        'width="25" height="25" class="qualColumnAll" id="q'.$qual->id.'"/>'.
                        '</a></th>';
        $staffHeader .= '<th>'.(isset($qual->displaytype) ? $qual->displaytype : $qual->type).'<br />'.( isset($qual->level) ? 'Level ' . $qual->level : $qual->trackinglevel ).'<br />'.
                $qual->subtype.'<br />'.$qual->name.'<br /><br />'.
                '<a href="edit_course_qual_user?cID='.$courseID.'" 
                    title="'.get_string('selectallstudentsqual', 'block_bcgt').'">'.
                        '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowdown.jpg"'. 
                        'width="25" height="25" class="qualColumnStaffAll" id="q'.$qual->id.'st"/>'.
                        '</a></th>';
    }
}
$headerStu = $header.$headerStu;
$headerStu .= '</tr>';
$headerStu .= '</thead>';
$out .= $headerStu;
$out .= '<tbody>';
$role = bcgt_get_role('student');
$out .= display_course_tracker_users($courseID, $users, $currentQuals, $role, true);
//now get the old students
if(has_capability('block/bcgt:editredundanttrackeruserlinks', $context))
{
    $oldStudents = bcgt_get_old_students_still_on_qual($courseID, $currentQuals);
    $out .= display_course_tracker_unlinked_users($courseID, $oldStudents, $currentQuals);
}

$out .= '</tbody></table>';

//now get the staff
if(has_capability('block/bcgt:editstafftrackerlinks', $context))
{
    $staff = bcgt_get_course_staff($courseID);
    if($staff)
    {
        $out .= '<h3 class="enroledstaff">'.get_string('enroledstaff', 'block_bcgt').'</h3>';
        $role = bcgt_get_role('student');
        $out .= '<table id="courseQualUserTableStaff" class="courseQualUserTableStaff">';
        
        $staffHeader = $header.$staffHeader;
        $staffHeader .= '</tr>';
        $staffHeader .= '</thead>';
        $out .= $staffHeader;
        $out .= '<tbody>';
        $checkForOtherRoles = true;
        $out .= display_course_tracker_staff($courseID, $staff, $currentQuals, $role, false, $checkForOtherRoles);
        $out .= '</tbody></table>';
    }
}
$out .= '<input type="submit" name="save" value="'.get_string('save', 'block_bcgt').'"/>';
$out .= '</form>';





//get the students enrolled on this course, and where they are enrolled find the method, where the
//method is child course//get the child course. 

$out .= html_writer::end_tag('div');//end main column
$out .= $OUTPUT->footer();
echo $out;
?>
