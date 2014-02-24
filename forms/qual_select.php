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

$qualID = optional_param('qualID', -1, PARAM_INT);
if(isset($_POST['edit']))
{
    redirect('edit_qual.php?qID='.$qualID);
}
if(isset($_POST['addUnits']))
{
    redirect('edit_qual_units.php?qID='.$qualID);
}
if(isset($_POST['addSudents']))
{
    redirect('edit_qual_stu.php?qID='.$qualID);
    //redirect('edit_qual_user.php?qID='.$qualID);
}
if(isset($_POST['addStaff']))
{
    redirect('edit_teacher_qual.php?qID='.$qualID);
}
if(isset($_POST['editstudentsunits']))
{
    redirect('edit_students_units.php?qID='.$qualID);
}

if (isset($_POST['copy']) && $qualID > 0){
    
    Qualification::copy_qual($qualID);
    
}

$PAGE->set_context($context);
require_capability('block/bcgt:searchquals', $context);
$tab = optional_param('page', 1, PARAM_INTEGER);
$url = '/blocks/bcgt/forms/my_dashboard.php';
$PAGE->set_url($url, array('page' => $tab));
$PAGE->set_title(get_string('selectqual', 'block_bcgt'));
$PAGE->set_heading(get_string('selectqual', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('myDashboard', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
$PAGE->navbar->add(get_string('myDashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add('',$url.'?page='.$tab,'title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initselqual', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();

$familyID = optional_param('family', -1, PARAM_INT);
$levelID = optional_param('level', -1, PARAM_INT);
$subTypeID = optional_param('subtype', -1, PARAM_INT);
$search = optional_param('searchKeyword', '', PARAM_TEXT);

$searchResults = search_qualification(-1, $levelID, $subTypeID, $search, $familyID);
$families = get_qualification_type_families_used();
$subTypes = get_subtype_from_type(-1, $levelID, $familyID);
$levels = get_level_from_type(-1, $familyID);
echo $OUTPUT->header();

echo'<h2 class="bcgt_form_heading">'.get_string('selectqual', 'block_bcgt').'</h2>';
    echo '<div id="bcgtQualSelectForm" class="bcgt_admin_controls">';
    //echo '<h3 class="bcgtSubTitle"><a href="edit_qual.php?">'.
        //get_string('addnewqual','block_bcgt').'</a></h3>';
    echo '<div id="bcgtColumnConainer" class="bcgt_two_c_container bcgt_float_container">';
        echo '<div id="search" class="bcgt_admin_left bcgt_col">';
            echo '<h3 class="sub_title">'.get_string('qualsearchpar', 'block_bcgt').'</h3>';
				
                echo '<form method="post" name="bcgtQualSelect" id="bcgtQualSelect" action="qual_select.php">';
                    echo '<input type="hidden" name="cID" value="'.$courseID.'"/>';
                    echo '<div class="inputContainer"><div class="inputLeft">'.
                            '<label for="type">'.get_string('qualfamily', 'block_bcgt').'</label></div>';
                    echo '<div class="inputRight"><select id="family" name="family">'.
                            '<option value="-1">Please Select one</option>';
						if($families)
						{
							foreach($families as $family) {
								$selected = '';
								if($family->id == $familyID)
								{
									$selected = 'selected';
								}
								echo "<option $selected value='$family->id'>$family->family</option>";
							}	
						}
					echo '</select></div></div>';
					echo '<div class="inputContainer"><div class="inputLeft">'.
                            '<label for="level">'.get_string('level', 'block_bcgt')
                            .'</label></div>';
					echo '<div class="inputRight"><select id="level" name="level">'.
                            '<option value="-1">Please Select one</option>';
						if($levels)
						{
							foreach($levels as $level) {
								$selected = '';
								if($level->get_id() == $levelID)
								{
									$selected = 'selected';
								}
								echo "<option $selected value='".$level->get_id()."'>"
                                        .$level->get_level()."</option>";
							}	
						}
					echo '</select></div></div>';
					echo '<div class="inputContainer"><div class="inputLeft">'.
                            '<label for="subtype">'.get_string('subtype','block_bcgt')
                            .'</label></div>';
					echo '<div class="inputRight"><select id="subtype" name="subtype">'.
                            '<option value="-1">Please Select one</option>';
						if($subTypes)
						{
							foreach($subTypes as $subType) {
                                $selected = '';
								if($subType->get_id() == $subTypeID)
								{
                                    $selected = 'selected';
                                }
								echo "<option $selected value='".$subType->get_id()."'>".
                                        $subType->get_subtype()."</option>";	
							}	
						}
					echo '</select></div></div>'; 
					echo '<div class="inputContainer"><div class="inputLeft">'.
                            '<label for="searchKeyword">'.get_string('ksearch','block_bcgt').'</label></div>';
					echo '<div class="inputRight">'.
                            '<input type="text" name="searchKeyword" id="searchKeyword" value="'.$search.'"/>'.
                            '</div></div>';
					echo '<br /><input type="submit" name="search" value="Search"/>';
				echo '</form>';
			//echo '</div>';
			//echo '<div class="bcgt_admin_right bcgt_col">';
                                echo '<br /><br />';
				echo '<form method="post" name="qualificationSelectForm" action="qual_select.php?">';
					echo '<select name="qualID" size="20">';
						if($searchResults)
						{
							foreach($searchResults AS $result)
							{
								$coursesCount = 0;
                                $unitsCount = 0;
                                                                
								if(!is_null($result->countcourse))
								{
									$coursesCount = $result->countcourse;
								}
                                
								if(!is_null($result->countunits))
								{
									$unitsCount = $result->countunits;
								}
                                
                                if (isset($result->isbespoke)){
                                    
                                    $display = "{$result->displaytype} ";
                                    if ($result->level > 0){
                                        $display .= get_string('level', 'block_bcgt') . " {$result->level} ";
                                    }
                                    $display .= $result->subtype . " ";
                                    $display .= $result->name;
                                    
                                    echo "<option value='$result->id'>{$display}</option>";
                                } else {
                                    echo "<option value='$result->id'".
                                    "title=' $coursesCount Courses --- $unitsCount ".
                                    "Units '>$result->type $result->trackinglevel ".
                                    "$result->subtype $result->name </option>";
                                }
                                
								
							}
						}
					echo '</select><br />';                    
                    echo '<input type="submit" name="edit" value="Edit Qual" class="bcgtFormButton" />';
                    echo '<input type="submit" name="copy" value="Create Copy of Qual" class="bcgtFormButton" />';
                    echo "<br />";
                    echo '<input type="submit" disabled="disabled" name="viewUnits" value="View Units/Criteria on Qual" class="bcgtFormButton" />';
                    echo '<input type="submit" name="addUnits" value="Add/Remove Units on Qual" class="bcgtFormButton" />';
//                    echo '<input type="submit" name="addSudents" value="Add/Remove Students on Qual" class="bcgtFormButton" />';
//                    echo '<input type="submit" name="addStaff" value="Add/Remove Staff on Qual" class="bcgtFormButton" />';
                    echo "<br />";                    
                    echo '<input type="submit" disabled="disabled" name="viewCourses" value="View Courses" class="bcgtFormButton" />';
                    echo '<input type="submit" disabled="disabled" name="select" value="Add to Course" class="bcgtFormButton" />';
                    echo '<input type="submit" name="editstudentsunits" value="Edit Students Units" class="bcgtFormButton" />';
			    echo '</form>';
                            
                        echo '<h3 class="subTitle"><a href="edit_qual.php?">Add a new Qualification</a></h3>';
                        echo '<h3 class="menuLink"><a href="my_dashboard.php">Back to Menu</a></h3>';
                        
			echo '</div>';    
		echo '</div>';		
	echo '</div>';

echo $OUTPUT->footer();
?>
