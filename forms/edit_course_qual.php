<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

global $COURSE, $CFG, $PAGE, $OUTPUT, $USER;
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
if(!has_capability('block/bcgt:editqualscourse', $context) && !has_capability('block/bcgt:addqualtocurentcourse', $context))
{
    throw new required_capability_exception($context, 'block/bcgt:editqualscourse', 'nopermissions', '');
}
$cID = optional_param('cID', -1, PARAM_INT);
if(isset($_POST['editStudentsUnits']))
{
    //then redirect to edit students units
    redirect('edit_students_units.php?a=c&cID='.$cID.'&oCID='.$originalCourseID);
}
if(isset($_POST['editStudentsQuals']))
{
    //then redirect to edit students quals
    redirect('edit_course_qual_user.php?cID='.$cID.'&oCID='.$originalCourseID);
}

$addQuals = isset($_POST['addselect']) ? $_POST['addselect'] : array();
$removeQuals = isset($_POST['removeselect']) ? $_POST['removeselect'] : array();
$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELUNITS;
if(isset($_POST['add']))
{
    foreach((array)$addQuals AS $qualID)
    {
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        if($qualification)
        {
            //true is add users to qual and units
           $qualification->add_to_course($cID, true); 
        }
    }
    //for each qual selected
    //add to the course
    //find all users on the course, add them to the qual(s)
    //students and teachers. 
}
if(isset($_POST['remove']))
{
    foreach($removeQuals AS $qualID)
    {
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        if($qualification)
        {
            //true is remove users from qual and units
           $qualification->remove_from_course($cID, true); 
        }
    }
    //for each qual selected
    //remove from the course
    //find all users on the course, remove them from the qual(s)
    //students and teachers. 
}

$url = '/blocks/bcgt/forms/edit_course_qual.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('editcoursequal', 'block_bcgt'));
$PAGE->set_heading(get_string('editcoursequal', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('editcoursequal', 'block_bcgt'));
if($originalCourseID != -1 && $originalCourseID != 1)
{
    global $DB;
    $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($originalCourseID));
    $PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
    $PAGE->navbar->add($course->shortname,$CFG->wwwroot.'/course/view.php?id='.$originalCourseID,'title');
    $PAGE->navbar->add(get_string('editcoursequal', 'block_bcgt'),null,'title');
}
elseif($cID != 1)
{
    global $DB;
    $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($cID));
    $PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
    $PAGE->navbar->add($course->shortname,$CFG->wwwroot.'/course/view.php?id='.$cID,'title');
    $PAGE->navbar->add(get_string('editcoursequal', 'block_bcgt'),null,'title');
}
else
{
    $PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
    $PAGE->navbar->add(get_string('myDashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
    $PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php','title');
}


$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initcoursequals', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
global $DB;
$search = optional_param('searchKeyword', '', PARAM_TEXT);
$searchExisting = optional_param('searchExisting', '', PARAM_TEXT);
$familyID = optional_param('family', -1, PARAM_INT);
$levelID = optional_param('level', -1, PARAM_INT);
$subTypeID = optional_param('subtype', -1, PARAM_INT);
$out = $OUTPUT->header();
$course = $DB->get_record_select('course', 'id = ?', array($cID));
$families = get_qualification_type_families_used();
$subTypes = get_subtype_from_type(-1, $levelID, $familyID);
$levels = get_level_from_type(-1, $familyID); 
$currentQuals = bcgt_get_course_quals($cID);
$heading = '';
if($course)
{
    $heading .= ' - '.$course->shortname." : ".$course->fullname;
}
$out .= html_writer::tag('h2', get_string('editcoursequalheading','block_bcgt').
        $heading, 
        array('class'=>'formheading'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_controls', 
    'id'=>'editCourseQual'));

$out .= '<form name="editCourseQual" action="edit_course_qual.php" method="POST" id="editCourseQualForm">';
$out .= '<input type="hidden" name="oCID" value="'.$originalCourseID.'"/>';
$out .= html_writer::start_tag('div', array('class'=>'bcgt_three_c_container bcgt_float_container', 
    'id'=>'bcgtCurrentQual'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_left bcgt_col_one bcgt_col'));
$out .= html_writer::tag('h3', get_string('currentquals','block_bcgt'), 
        array('class'=>'subformheading'));
$out .= '<input type="hidden" id="cID" name="cID" value="'.$cID.'"/>';
$out .= '<select name="removeselect[]" size="20" id="removeselect" multiple="multiple">';
if($currentQuals)
{
    foreach($currentQuals AS $qual)
    {
         if (isset($qual->isbespoke)){
                    $out .= "<option value='$qual->id'>$qual->displaytype Level $qual->level ".
                    "$qual->subtype $qual->name </option>";
                } else {
        
                    $out .= '<option value="'.$qual->id.'" title="'.$qual->name.'">'.
                    $qual->family.' '.$qual->trackinglevel.' '.$qual->subtype.
                    ' '.$qual->name.'</option>';	
        
                }
    }
}
$out .= '</select><br />';
$out .= '<input type="text" name="searchExisting" value="'.$searchExisting.'"/>';


$out .= html_writer::end_tag('div');

$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_center bcgt_col_two bcgt_col'));
$out .= '<input name="add" id="addQual" type="submit" disabled="disabled" value="'.get_string('add','block_bcgt').'"/><br />';
$out .= '<input name="remove" id="removeQual" type="submit" disabled="disabled" value="'.get_string('remove','block_bcgt').'"/><br />';

//TODO add all users tick box

$out .= html_writer::end_tag('div');
               
$searchResults = search_qualification(-1, $levelID, $subTypeID, $search, $familyID, null, $cID, false);
    $out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_right bcgt_col_three bcgt_col'));
    $out .= html_writer::tag('h3', get_string('qualselectheading','block_bcgt'), 
        array('class'=>'subformheading'));
    $out .= '<select name="addselect[]" multiple="multiple" id="addselect" size="10">';
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
                
                if (isset($result->isbespoke)){
                    $out .= "<option $selected value='$result->id'>$result->displaytype $result->level ".
                    "$result->subtype $result->name </option>";
                } else {
                    $out .= "<option $selected value='$result->id'".
                    "title=' $result->family $result->trackinglevel ".
                    "$result->subtype $result->name --- $coursesCount Courses --- $unitsCount ".
                    "Units '>$result->family $result->trackinglevel ".
                    "$result->subtype $result->name </option>";
                }
                
                
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
$out .= html_writer::end_tag('div');//end right column
$disabled = '';
//if(count($currentQuals) < 2)
//{
//    $disabled = 'disabled="disabled"';
//}

$out .= html_writer::end_tag('div');//end float column
$out .= '<input type="submit" '.$disabled.' name="editStudentsQuals" value="Stage 2: Edit Students Qualifications"/>';
$out .= '<input type="submit" name="editStudentsUnits" value="Stage 3: Edit Students Units"/>';
$out .= '</form>';
$out .= html_writer::end_tag('div');//end main column
$out .= $OUTPUT->footer();

echo $out;
?>
