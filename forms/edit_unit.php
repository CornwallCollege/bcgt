<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

//TODO : 
//Add form validation
//add check if qual name etc already exists in database
//add copy units from another qual

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
$PAGE->set_context($context);
require_capability('block/bcgt:addnewunit', $context);

$qualID = optional_param('qual', -1, PARAM_INT);
$unitID = optional_param('unitID', -1, PARAM_INT);
$typeID = optional_param('typeID', -1, PARAM_INT);
$familyID = optional_param('unitTypeFamily', -1, PARAM_INT);
//are we just adding to a student and not the qual
//this is in the case of APL.
//$studentQual = optional_param('stu', false, PARAM_BOOL);

//$addQual = optional_param('addQual', '', PARAM_TEXT);
$name=optional_param('name', '', PARAM_TEXT);
$details=optional_param('details', '', PARAM_TEXT);
$uniqueID =optional_param('unique', '', PARAM_TEXT);
//$rules = optional_param('unitRule', '', PARAM_TEXT);
$pathway = optional_param('pathway', -1, PARAM_INT);
$pathwayType = optional_param('type', -1, PARAM_INT);

// If we have set a pathway and a pathway type, then the typeID is going to change
if ($familyID > 0 && $pathway > 0 && $pathwayType > 0){
    $newType = $DB->get_record("block_bcgt_type", array("bcgttypefamilyid" => $familyID, "bcgtpathwaydeptid" => $pathway, "bcgtpathwaytypeid" => $pathwayType));
    if ($newType){
        $typeID = $newType->id;
    }
    
}

$families = get_qualification_type_families_used();

$unit = null;
$qualsOn = null;

//can we get the Unit by typeID or by actual ID?
$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELALL;
if($unitID != -1)
{
	$unit = Unit::get_unit_class_id($unitID, $loadParams);
}
elseif($typeID != -1 || $familyID != -1)
{
	//some need the level and subtype most dont.
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
	$unit = Unit::get_unit_class_type($typeID, $familyID, 
            null, $loadParams);
}
if($unit)
{
	$typeID = $unit->get_typeID();
	$familyID = $unit->get_familyID();
}	



//$searchResults = search_qualification($typeID, -1, -1, '', $familyID);
$qualification = null;

if(isset($_POST['save']))
{
            
	if($unit)
	{
                
		$unit->set_name($name);
		$unit->set_details($details);
		$unit->set_uniqueID($uniqueID);
//        $unit->set_rules($rules);
                
		//this gets the submitted edit data form the form. 
		//this is different per unit type.
		//e.g. BTEC needs credits and level and subtype etc
		$unit->get_submitted_edit_form_data();
		
		//this gets the suvvmitted edit data from the form for the criteria
		//this is very different per different type.
		$unit->get_submitted_criteria_edit_form_data();
		//print_object($unit->get_criteria());
		//if its a new one insert
                    
        if ($unit->process_create_update_unit_form()){
                    
            if($unitID == -1)
            {
                //insert
                $unit->insert_unit();
                $unitID = $unit->get_id();
                //are we adding it to a qual?
    //			if($addQual == 'on' && $qualID != '')
    //			{
    //				$unit->save_unit_qual($qualID);
    //				$qualification = Qualification::get_qualification_class_id($qualID);
    //				//this will find all of the students on this qual and add the unit to it
    //				$qualification->edit_students_units($unitID, true);
    //			}
    //			if($studentQual)
    //			{
    //				//then we are adding to a student and not to the qual
    //				//studentID, $qualID
    //				$unit->add_student_to_unit($studentID, $qualID);
    //			}
            }
            elseif($unitID != -1)
            {
                //Update because we know the id
                $unit->update_unit();
    //			if($addQual == 'on' && $qualID != '')
    //			{
    //				$unit->save_unit_qual($qualID);
    //				$qualification = Qualification::get_qualification_class_id($qualID);
    //				//this will find all of the students on this qual and add the unit to it
    //				$qualification->edit_students_units($unitID, true);
    //			}
            }
            
            // reload unit
            $loadParams->loadLevel = Qualification::LOADLEVELCRITERIA;
            $unit = Unit::get_unit_class_id($unitID, $loadParams);
            
            $successmsg = get_string('unitsaved', 'block_bcgt');
        
        } 
        
        
	}
//	if(isset($_POST['save']))
//	{
//        //add as a splash screen
////		if($addQual == 'on' && $qualID != -1)
////		{
////			redirect($CFG->wwwroot.'/mod/qualification/edit_qualification_units.php?qualID='.$qualID);
////		}
////		elseif($studentQual)
////		{
////			redirect($CFG->wwwroot.'/mod/qualification/student_grid.php?sID='.$studentID.'&qID='.$qualID);
////		}
		//redirect($CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=adm&cID='.$courseID);
//	}
}
elseif(isset($_POST['cancel']))
{
	//redirect($CFG->wwwroot.'/mod/qualification/qual_unit_menu.php');
}
elseif($unit)
{
	if($name == '')
	{
		$name = $unit->get_name();
	}
	if($details == '')
	{
		$details = $unit->get_details();	
	}
	if($uniqueID == '')
	{
		$uniqueID = $unit->get_uniqueID();
	}
    if($unitID != -1)
    {
        $qualsOn = $unit->get_quals_on();
    }
	$typeID = $unit->get_typeID();
}

$url = '/blocks/bcgt/forms/edit_unit.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('editunit', 'block_bcgt'));
$PAGE->set_heading(get_string('editunit', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class('editunit');
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
//$PAGE->navbar->add(get_string('bcgtmydashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string('editunit', 'block_bcgt'));
$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initeditunit', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript(true);
echo "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/css/start/jquery-ui-1.10.3.custom.min.css' />";
echo $OUTPUT->header();

if (isset($successmsg)){
    echo "<h1 class='c' style='color:blue;'>{$successmsg}</h1>";
}

echo'<h2 class="bcgt_form_heading">'.get_string('addeditunitsheading', 'block_bcgt').'</h2>';
	echo '<div id="bcgtEditUnitForm" class="bcgt_admin_controls">';
		echo '<form method="POST" name="editUnitForm" id="editUnitForm" action="">';
		echo '<div id="unitDetailsDiv bcgtColumnConainer" class="bcgt_two_c_container bcgt_float_container">';	
			echo '<div id="unitDetails" class="bcgt_admin_left bcgt_col">';
			echo '<h3 class="subOptions">'.get_string('unitdetails', 'block_bcgt').' : </h3>';
            
            echo '<div class="error" id="outputErrors">';
            
            if ($unit && $unit->get_processed_errors()){
                echo '<div class="error c">'.$unit->get_processed_errors().'</div>';
            } 
            
            echo '</div>';
            
			echo '<input type="hidden" name="qualID" value="'.$qualID.'"/>';
			echo '<input type="hidden" name="unitID" value="'.$unitID.'"/>';
			echo '<input type="hidden" name="cID" value="'.$courseID.'"/>';
            echo '<input type="hidden" id="typeID" name="typeID" value="'.$typeID.'"/>';
			//echo '<input type="hidden" name="stu" value="'.$studentQual.'"/>';
			echo '<div class="inputContainer"><div class="inputLeft">'.
                    '<label for="unitType"><span class="required">*</span>'. 
                    get_string('unittype', 'block_bcgt').' : </label></div>';
			$disabled = '';
			if($unitID != -1)
			{
				$disabled = 'disabled';
			}
			echo '<div class="inputRight"><select '.$disabled.' name="unitTypeFamily"'.
                    'id="unitTypeFamily"><option value="-1">Please Select one</option>';
				if($families)
				{
					foreach($families as $family) {
						$selected = '';
						if($familyID != -1 && $family->id == $familyID)
						{
							$selected = 'selected';
						}
						echo "<option $selected value='".$family->id."'>".$family->family."</option>";	
					}
				}
			echo '</select></div></div>';
			
			//NOW GO AND GET THE other drop downs dependant on qualFamily.
			//e.g. an ALEVEL doesnt change Levels and we dont care if its an A2 or AS
			//BTECS we do care about Level and sometimes we care about subtype (e.g. Foundation Diploma)
			echo Unit::get_unit_edit_form_menu($familyID, $disabled, $unitID, $typeID);

            if($unit)
            {
                if($unit->has_unique_id())
                {
                    echo '<div class="inputContainer"><div class="inputLeft">'.
                    '<label for="unique"><span class="required">*</span>'.
                    get_string('uniqueid','block_bcgt').' :</label></div>';
                    echo '<div class="inputRight"><input type="text" name="unique"'.
                    'id="unique" value="'.$uniqueID.'"/></div></div>';
                }
            }
			echo '<div class="inputContainer"><div class="inputLeft">'.
                    '<label for="name"><span class="required">*</span>'.
                    get_string('name','block_bcgt').' : </label></div>';
			echo '<div class="inputRight"><input type="text" name="name"'.
                    'id="name" value="'.$name.'"/></div></div>';
			echo '<span id="warning"></span>';
			echo '<div class="inputContainer"><div class="inputLeft">'.
                    '<label for="details">'.get_string('unitdetails','block_bcgt').
                    ' : </label></div>';
			echo '<div class="inputRight"><textarea row="5" cols="30"'.
                    'name="details" id="details">'.$details.'</textarea>'.
                    '</div></div>';
//            echo '<div class="inputContainer"><div class="inputLeft">'.
//                    '<label for="addQual">'.get_string('addunitprequal','block_bcgt').
//                    ' : </label></div>';
//			echo '<div class="inputRight"><input type="checkbox" disabled="disabled"'.
//                    'name="addQual" id="addQual"';
////			if($addQual == 'on')
////			{
////				echo 'checked="checked"';
////			}
//			echo '/></div></div>';
			if($unit)
			{
				//this gets the unit type specific fields. 
				//certain different types of unit will need different fields entered
				//for example btec needs crdits, btecHigher needs to know if its 
				//a college unit or an APL unit. 
				echo $unit->get_edit_form_fields();
			}			
			echo '</div>';//end the column left
//			echo '<div class="bcgt_admin_right bcgt_col">';
//				echo '<div id="pickQualArea"';
//				if($addQual != 'on')
//				{
//					echo ' style="display:none;"';
//				}
//				
//				echo '>';
//					echo '<h3 class="subOptions">Add to Qualification : </h3>';
//					echo '<div class="inputContainer"><div class="inputLeft">'.
//                            '<label for="qualLevel">Level : </label></div>'.
//                            '<div class="inputRight"><select id="qualLevel"'.
//                            'name="qualLevel">';
//					if($levels)
//					{
//						if(count($levels) > 1)
//						{
//							echo '<option value="-1">Please Select one</option>';
//						}				
//						foreach($levels as $level) {
//                            echo '<option value="'.$level->get_id().'">'.$level->get_level().'</option>';
//						}	
//					}
//					else
//					{
//						echo '<option value="-1">There are no Levels for this Type</option>';
//					}
//					echo '</select></div></div>';
//					echo '<div class="inputContainer"><div class="inputLeft">'.
//                            '<label for="qualSubtype">Subtype : </label></div>'.
//                            '<div class="inputRight"><select name="qualSubtype"'.
//                            'id="qualSubtype">';
//					if($subTypes)
//					{
//						if(count($subTypes) > 1)
//						{
//							echo '<option value="-1">Please Select one</option>';
//						}
//						foreach($subTypes as $subType) {
//							echo '<option value="'.$subType->get_id().'">'.
//                                    $subType->get_subtype().'</option>';
//						}
//		
//					}
//					else
//					{
//						echo '<option value="-1">There are no Subtypes for this 
//                            Type and Search Level</option>';
//					}
//					echo '</select></div></div>';
//					echo '<div class="inputContainer"><div class="inputLeft">'.
//                            '<label for="qualSearch">Search : </label></div>'.
//                            '<div class="inputRight"><input type="text" name="qualSearch"'.
//                            'id="qualSearch"/></div></div>';
//										
//					echo '<div class="inputContainer"><div class="inputLeft">'.
//                            '<label for="qual">Add to : </label></div>';
//					echo '<div class="inputRight"><select name="qual" size="5"'.
//                            'id="qual">';
//						if($qualifications)
//						{
//							foreach($qualifications as $qualification) {
//                                $selected = '';
//								if($qualID != -1 && ($qualification->id == $qualID))
//								{
//                                    $selected = 'selected';
//                                }
//								echo '<option value="'.$qualification->id.'" '.
//                                    'title="'.$qualification->name.'">'.
//                                        $qualification->type." ".
//                                        $qualification->level." ".
//                                        $qualification->subtype." ".
//                                        $qualification->name.'</option>';
//							}
//						}
//					echo '</select></div></div>'; //end the input containers
//				echo '</div>';
//			echo '</div>';
            echo '</div>';
			if($unit)
			{
				//this gets the criteria form from the unit
				//its different per type.
                echo '<br><br>';
				echo '<div id="unitCriteria"><h3 class="subTitle">'.
                        $unit->get_criteria_header().'</h3>';
				echo $unit->get_edit_criteria_table();
				echo '</div>';
			}
//            else
//            {
//                echo "NO UNIT";
//            }
			
			echo '<div id="controls">';
			echo '<input type="submit" name="save" value="Save" id="save" class="bcgtFormButton" />';
			echo '<input type="submit" name="cancel" value="Cancel" class="bcgtFormButton" />';
			echo '</div>';
			
		echo '</form>';	
		echo '<div>';
			if($qualsOn && count($qualsOn) > 0)
			{
                echo '<h3>'.get_string('qualsuniton', 'block_bcgt').'</h3>';
				echo '<table class="qualsUnitOn"> 
				<tr><th>Type</th><th>Level</th><th>SubType</th><th>Name</th></tr>';
				foreach($qualsOn AS $quals)
				{
					echo '<tr><td>'.$quals->type.'</td><td>'.$quals->trackinglevel.'</td><td>'.
                            $quals->subtype.'</td><td>'.$quals->name.'</td></tr>';
				}
				echo '</table>';
			}
		echo '</div>';
	echo '</div>';
echo $OUTPUT->footer();
?>
