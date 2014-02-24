<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

global $COURSE, $CFG, $PAGE, $OUTPUT;
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
$courseID = optional_param('cID', -1, PARAM_INT);
if(isset($_POST['editQuals']))
{
    redirect('edit_course_qual.php?cID='.$courseID);
}

$PAGE->set_context($context);
require_capability('block/bcgt:editqualscourse', $context);
$url = '/blocks/bcgt/forms/course_select.php';
$string = 'selectcourse';
$PAGE->set_url($url);
$PAGE->set_title(get_string('selectqual', 'block_bcgt'));
$PAGE->set_heading(get_string('selectqual', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('myDashboard', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
$PAGE->navbar->add(get_string('myDashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string($string, 'block_bcgt'));

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initselcourse', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();

$categoryID = optional_param('cat', -1, PARAM_INT);
$studentSearch = optional_param('sSearch', '', PARAM_TEXT);
$teacherSearch = optional_param('tSearch', '', PARAM_TEXT);
$search = optional_param('coursesearch', '', PARAM_TEXT);

$searchResults = search_courses($categoryID, $search, $studentSearch, $teacherSearch, 'shortname ASC');
$categories = get_categories();
echo $OUTPUT->header();

echo'<h2 class="bcgt_form_heading">'.get_string('selectcourse', 'block_bcgt').'</h2>';
	echo '<div id="bcgtCourseSelectForm" class="bcgt_admin_controls">';
        echo '<div id="bcgtColumnConainer" class="bcgt_two_c_container bcgt_float_container">';
            echo '<div id="search" class="bcgt_admin_left bcgt_col">';
                    echo '<h3 class="sub_title">'.get_string('coursesearchpar', 'block_bcgt').'</h3>';
                    
                echo '<form method="post" name="bcgtCourseSelect" id="bcgtCourseSelect" action="course_select.php">';
                        echo '<input type="hidden" name="oCID" value="'.$originalCourseID.'"/>';	
                        echo '<div class="inputContainer"><div class="inputLeft">'.
                            '<label for="type">'.get_string('category').'</label></div>';
                        echo '<div class="inputRight"><select id="cat" name="cat">'.
                            '<option value="-1">Please Select one</option>';
						if($categories)
						{
							foreach($categories as $category) {
								$selected = '';
								if($category->id == $categoryID)
								{
									$selected = 'selected';
								}
								echo "<option $selected value='$category->id'>$category->name</option>";
							}	
						}
					echo '</select></div></div>';
					echo '<div class="inputContainer"><div class="inputLeft">'.
                            '<label for="level">'.get_string('teachersearch', 'block_bcgt')
                            .'</label></div>';
					echo '<div class="inputRight"><input type="text" name="tSearch" id="tSearch"'.
                            'value="'.$teacherSearch.'"></div></div>';
					echo '<div class="inputContainer"><div class="inputLeft">'.
                            '<label for="level">'.get_string('studentsearch', 'block_bcgt')
                            .'</label></div>';
					echo '<div class="inputRight"><input type="text" name="sSearch" id="sSearch"'.
                            'value="'.$studentSearch.'"></div></div>'; 
					echo '<div class="inputContainer"><div class="inputLeft">'.
                            '<label for="level">'.get_string('coursesearch', 'block_bcgt')
                            .'</label></div>';
					echo '<div class="inputRight"><input type="text" name="coursesearch" id="cSearch"'.
                            'value="'.$search.'"></div></div>';
					echo '<br /><input type="submit" name="search" value="Search"/>';
				echo '</form>';
			// echo '</div>';
			// echo '<div class="bcgt_admin_right bcgt_col">';
                $useCourseCategories = false;
                if(get_config('bcgt','showcoursecategories'))
                {
                    $useCourseCategories = true;
                }
                                echo '<br /><br />';
				echo '<form method="post" name="courseSelectForm" action="course_select.php?">';
					echo '<input type="hidden" name="oCID" value="'.$originalCourseID.'"/>';
                    echo '<select name="cID" size="20">';
						if($searchResults)
						{
							foreach($searchResults AS $result)
							{
								$qualsCount = 0;
								if($result->countquals)
								{
									$qualsCount = $result->countquals;
								}
								$studentsCount = 0;
								if($result->countstudents)
								{
									$studentsCount = $result->countstudents;
								}
								echo "<option value='$result->id'".
                                    "title=' $qualsCount Quals --- $studentsCount ".
                                    "Students '>";
                                if($useCourseCategories)
                                {
                                    echo "($result->categoryname) --- ";
                                }
                                echo "$result->shortname : ".
                                    "$result->fullname </option>";
							}
						}
					echo '</select><br />';
                    echo '<input type="submit" name="editQuals" value="Edit Qualifications on this Course" class="bcgtFormButton" />';
                echo '</form>';
			echo '</div>';
		echo '</div>';
    echo '</div>';

echo $OUTPUT->footer();
?>
