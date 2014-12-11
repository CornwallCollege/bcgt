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

set_time_limit(0);

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

if (isset($_POST['qualID'])){
    $qualIDs = $_POST['qualID'];
}

if (isset($_POST['runaway'])){
    redirect( $CFG->wwwroot . '/blocks/bcgt/forms/my_dashboard.php' );
}

$PAGE->set_context($context);
require_capability('block/bcgt:searchquals', $context);
$tab = optional_param('page', 1, PARAM_INTEGER);
$url = '/blocks/bcgt/forms/my_dashboard.php';
$PAGE->set_url($url, array('page' => $tab));
$PAGE->set_title(get_string('deletequals', 'block_bcgt'));
$PAGE->set_heading(get_string('selectqual', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
//$PAGE->navbar->add(get_string('bcgtmydashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
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

if (isset($_POST['confirmdelete']) && !empty($_POST['qualID'])){

    $quals = array();
    
    echo "<div class='c'>";
    
    if ($qualIDs){
        
        foreach($qualIDs as $qualID){
            
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
            $qual = Qualification::get_qualification_class_id($qualID, $loadParams);
            $quals[] = $qual;
            
        }
        
    }
    
    if ($quals){
        foreach($quals as $qual){
            $qual->delete_qual_main();
            echo "Deleted Qualification: " . $qual->get_display_name() . "<br>";
        }
    }
    
    
    echo "<br><br>";
    echo "<a href='{$CFG->wwwroot}/blocks/bcgt/forms/delete_quals.php'>Return to Qual Selecter</a>";
    echo "</div>";
    
}

elseif (isset($_POST['delete']) && !empty($_POST['qualID'])){
    
    $quals = array();
        
    if ($qualIDs){
        
        foreach($qualIDs as $qualID){
            
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
            $qual = Qualification::get_qualification_class_id($qualID, $loadParams);
            
            $quals[] = $qual;
            
        }
        
    }
    
    
    // Check what is linked to these quals
    $coursesLinked = array();
    $studentsLinked = array();
    
    if ($quals){
        
        foreach($quals as $qual){
            
            // What courses is this qual on?
            $courses = $DB->get_records("block_bcgt_course_qual", array("bcgtqualificationid" => $qual->get_id()));
            if ($courses){
                foreach($courses as $course){
                    // Just get it's name
                    $record = $DB->get_record("course", array("id" => $course->courseid), "fullname");
                    if ($record){
                        if (!isset($coursesLinked[$qual->get_id()])) $coursesLinked[$qual->get_id()] = array();
                        $coursesLinked[$qual->get_id()][$course->courseid] = $record->fullname;
                    }
                }
            }
            
            // What students are on this qual?
            $studs = $DB->get_records("block_bcgt_user_qual", array("bcgtqualificationid" => $qual->get_id()));
            if ($studs){
                foreach($studs as $stud){
                    // Just get their name/username
                    $record = $DB->get_record("user", array("id" => $stud->userid));
                    if ($record)
                    {
                        if (!isset($studentsLinked[$qual->get_id()])) $studentsLinked[$qual->get_id()] = array();
                        $studentsLinked[$qual->get_id()][$record->id] = fullname($record) . ' ('.$record->username.')';
                    }
                }
            }
            
            if (!empty($coursesLinked) && isset($coursesLinked[$qual->get_id()])) asort($coursesLinked[$qual->get_id()]);
            if (!empty($studentsLinked) && isset($studentsLinked[$qual->get_id()])) asort($studentsLinked[$qual->get_id()]);
            
        }
        
    }
        
    // Confirmation screen
    echo "<div class='c'>";
    
        echo "<h2>".get_string('areyousuredeletequals', 'block_bcgt')."</h2>";
        
        foreach($quals as $qual){
            
            echo "<h3>";
            echo $qual->get_family() . " " . $qual->get_level()->get_level() . " " . $qual->get_subType()->get_subType() . " " . $qual->get_name() . "</h3>";
            
        }
        
        echo "<form action='' method='post'>";
        
        if (!empty($coursesLinked) || !empty($studentsLinked)){

            echo "<h2>".get_string('theyarelinkedcoursesusers', 'block_bcgt') . ":</h2>";
            
            echo "<h3>".get_string('courses')."</h3>";
            
            echo "<table id='bcgtDelLinkedCourses' class='bcgtDelTables'>";
            echo "<tr><th>Qual</th><th>Course</th></tr>";
            
            foreach($quals as $qual){

                if (!empty($coursesLinked[$qual->get_id()])){
                    foreach($coursesLinked[$qual->get_id()] as $courseID => $courseName){
                        echo "<tr><td>".$qual->get_family() . " " . $qual->get_level()->get_level() . " " . $qual->get_subType()->get_subType() . " " . $qual->get_name()."</td><td>".$courseName . "</td></tr>";
                    }
                }

            }
            
            echo "</table>";
            
            echo "<br>";
            echo "<h3>".get_string('users')."</h3>";
            
            echo "<table id='bcgtDelLinkedUsers' class='bcgtDelTables'>";
            echo "<tr><th>Qual</th><th>User</th></tr>";
            
            foreach($quals as $qual){

                if (!empty($studentsLinked[$qual->get_id()])){
                    foreach($studentsLinked[$qual->get_id()] as $studentID => $studentName){
                        echo "<tr><td>".$qual->get_name() . "</td><td>" . $studentName . "</td></tr>";
                    }
                }

            }
            
            echo "</table>";
            echo "<br>";
            
            echo "<h3>".get_string('ifyoudeletequals', 'block_bcgt') . "</h3>";
                                                
        }
        
        foreach($quals as $qual){
            echo "<input type='hidden' name='qualID[]' value='{$qual->get_id()}' />";
        }
        
        echo "<input type='submit' style='font-size:14pt;padding:10px;' name='runaway' value=\"NO, I'M SCARED!\" />";
        echo "<br><br><br>";
        echo "<input type='submit' style='font-size:14pt;padding:10px;' name='confirmdelete' value='YES, DELETE THEM' />";
            
        echo "</form>";
    
    echo "</div>";    
    
}
else
{





    echo'<h2 class="bcgt_form_heading">'.get_string('selectqual', 'block_bcgt').'</h2>';
	echo '<div id="bcgtQualSelectForm" class="bcgt_admin_controls">';
    //echo '<h3 class="bcgtSubTitle"><a href="edit_qual.php?">'.
        //get_string('addnewqual','block_bcgt').'</a></h3>';
        echo '<div id="bcgtColumnConainer" class="bcgt_two_c_container bcgt_float_container">';
            echo '<div id="search" class="bcgt_admin_left bcgt_col">';
				echo '<h3 class="sub_title">'.get_string('qualsearchpar', 'block_bcgt').'</h3>';
				
                echo '<form method="post" name="bcgtQualSelect" id="bcgtQualSelect" action="delete_quals.php">';
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
			// echo '</div>';
			// echo '<div class="bcgt_admin_right bcgt_col">';
				echo '<form method="post" name="qualificationSelectForm" action="delete_quals.php?">';
					echo '<select name="qualID[]" size="20" multiple>';
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
                                    "Units '>$result->family $result->trackinglevel ".
                                    "$result->subtype $result->name </option>";
                                }
                                
								
							}
						}
					echo '</select><br />';
                    echo '<input type="submit" name="delete" value="Delete Qualifications" class="bcgtFormButton" />';
			    echo '</form>';
			echo '</div>';
		echo '</div>';
        echo '<h3 class="menuLink"><a href="my_dashboard.php?tab=adm">Back to Menu</a></h3>';
	echo '</div>';
    
}

echo $OUTPUT->footer();
?>
