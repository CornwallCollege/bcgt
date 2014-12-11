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
$PAGE->set_context($context);
require_capability('block/bcgt:editqualunit', $context);
$addUnits = isset($_POST['addselect']) ? $_POST['addselect'] : '';
$removeUnits = isset($_POST['removeselect']) ? $_POST['removeselect'] : '';
$qualID = optional_param('qID', -1, PARAM_INT);
if($qualID == -1)
{
    redirect($CFG->wwwroot.'/blocks/bcgt/forms/qual_select.php');
}
$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELUNITS;
$qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
if(isset($_POST['add']))
{
    if($qualification)
    {
        foreach($addUnits AS $unitID)
        {
            $unitObj = Unit::get_unit_class_id($unitID, $loadParams);
            if($unitObj)
            {
                $qualification->add_unit($unitObj);
                $qualification->save(true, false);
            }
        } 
    }	
}
if(isset($_POST['remove']))
{
    if($qualification)
    {
        foreach($removeUnits AS $unitID)
        {
            $unitObj = Unit::get_unit_class_id($unitID, $loadParams);
            if($unitObj)
            {
                $qualification->remove_unit($unitObj);
                $qualification->save(true, false);
            }
        }
    }
}					
if(isset($_POST['edit']))
{
    if($addUnits != '')
    {
        $unitID = end($addUnits);
    }
    else
    {
        $unitID = end($removeUnits);
    }
    redirect('edit_unit.php?unitID='.$unitID);
}
if(isset($_POST['addNewUnit']))
{
    redirect('edit_unit.php');
}
if(isset($_POST['qualPicker']))
{
    redirect('qual_select.php');
}
if(isset($_POST['menu']))
{
    redirect('my_dashboard.php?tab=adm');
}
if(isset($_POST['groupUnits']))
{
    redirect('group_units.php?qID=' . $qualID);
}

$url = '/blocks/bcgt/forms/edit_qual_units.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('editqualunits', 'block_bcgt'));
$PAGE->set_heading(get_string('editqualunits', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('editqualunits', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
//$PAGE->navbar->add(get_string('bcgtmydashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initqualunits', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();

$search = optional_param('searchKeyword', '', PARAM_TEXT);
echo $OUTPUT->header();
$currentUnits = -1;
$typeID = -1;
$familyID = -1;
$in = '';
if($qualification)
{
	$currentUnits = $qualification->get_units();
	$typeID = $qualification->get_class_ID();
	$familyID = $qualification->get_family_ID();
	$in = $qualification->generate_sql_unit_in();
}

$unitChoice = search_unit(-1, -1, $search, -1, -1, $in, -1, '', '', $familyID);

echo'<h2 class="formheading">Qualification Units</h2>';
	echo '<div id="editQualUnits" class="bcgt_admin_controls">';
		echo '<h3 class="menuLink">'.get_string('qualification','block_bcgt').' : ';
		if($qualification)
		{
			echo $qualification->get_type().''. 
			' '.$qualification->get_level()->get_level().''. 
			' '.$qualification->get_subType()->get_subType();
			echo ' '.$qualification->get_name().'<br />';
            //e.g. no units, no credits required = XYz
			echo $qualification->get_type_qual_title();
		}
		echo '</h3>';
		echo '<form method="POST" name="unitQualForm" action="edit_qual_units.php">';	
			echo '<input type="hidden" id="typeID" name="typeID" value="'.$typeID.'"/>';
			echo '<input type="hidden" id="qID" name="qID" value="'.$qualID.'"/>';
			echo '<input type="hidden" id="familyID" name="familyID" value="'.$familyID.'"/>';
			echo '<input type="hidden" name="cID" value="'.$courseID.'"/>';
            echo '<div id="unitsQualContainer" class="bcgt_three_c_container bcgt_float_container">';	
				echo '<div id="unitsLeft" class="bcgt_admin_left bcgt_col">';
					echo '<h2 class="formsubheading">'.get_string('editqualunithead', 'block_bcgt').'</h2>';
					echo '<h3 class="forminfoheading">'.get_string('unitlistdetails', 'block_bcgt').'</h3>';
					echo '<label for="noUnits">'.get_string('nounits', 'block_bcgt').' : </label>';
					echo '<input type="text" name="noUnits" disabled="disabled" value="';
					if($qualification)
					{
						echo $qualification->count_units();
					}
					echo '"/><br />';
					echo '<select name="removeselect[]" size="20" id="removeselect" multiple="multiple">';
		          		if($currentUnits)
		          		{
		          			foreach($currentUnits AS $unit)
		          			{
		          				$uniqueID = $unit->get_uniqueID();
		          				if($unit->get_level() && $unit->get_level()->get_short() != '')
		          				{
		          					$uniqueID = '('.$unit->get_level()->get_short().') '.$unit->get_uniqueID().'';
		          				}
		          				echo '<option value="'.$unit->get_id().'" title="'.$unit->get_name().'">'.$uniqueID.' : '.$unit->get_name().'</option>';	
		          			}
		          		}
					echo '</select><br />';
					if($qualification)
					{
						echo $qualification->get_unit_list_type_fields();
					}
				echo '</div>';
				echo '<div id="unitsCenter" class="bcgt_admin_center bcgt_col">';
					echo '<p class="arrow_button">';
		            	echo '<input name="add" id="addUnit" type="submit" disabled="disabled" value="'.get_string('add','block_bcgt').'"/><br />';
		            	echo '<input name="remove" id="removeUnit" type="submit" disabled="disabled" value="'.get_string('remove','block_bcgt').'"/><br />';
		            	echo '<input name="edit" id="editUnit" type="submit" disabled="disabled" value="'.get_string('edit','block_bcgt').'">';
		            	echo '<p class="edit">'.get_string('editunitcond','block_bcgt').'</p>';
		            	echo '<div id="editWarning"></div>';
		          	echo '</p>';
				echo '</div>';
				echo '<div id="unitsRight" class="bcgt_admin_right bcgt_col">';
					echo '<h2 class="formsubheading">'.get_string('unitchooseheading','block_bcgt').'</h2>';
					echo '<h3 class="forminfoheading">'.get_string('unitlistdetails', 'block_bcgt').'</h3>';
                                        echo '<br />';
					echo '<select name="addselect[]" size="20" id="addselect" multiple="multiple">';
						if($unitChoice)
						{
							foreach($unitChoice AS $unit)
							{
								$uniqueID = $unit->uniqueid;
								if($unit->unitlevel)
								{
									$uniqueID = '('.Level::get_short_version($unit->unitlevelid).') '.$unit->uniqueid;
								}
								echo '<option value="'.$unit->id.'" title="'.$unit->name.'">'.$uniqueID.' : '.$unit->name.'</option>';	
							}
						}
					echo '</select><br />';
					echo '<label for="searchKeyword">'.get_string('ksearch','block_bcgt').'</label>';
					echo '<input type="text" name="searchKeyword" id="searchKeyword"/>';
					echo '<input id="unitSearch" name="search" type="submit" value="'.get_string('search','block_bcgt').'" class="bcgtFormButon" /><br />';
	          	echo '</div>';
			echo '</div>';
            
			echo '<input type="submit" name="groupUnits" value="'.get_string('groupunits','block_bcgt').'" class="bcgtFormButton" />';
            
            echo '<br><br>';
            
			echo '<input type="submit" name="addNewUnit" value="'.get_string('addnewunit','block_bcgt').'" class="bcgtFormButton" />';
			echo '<input type="submit" name="qualPicker" value="'.get_string('qualpicker','block_bcgt').'" class="bcgtFormButton"/>';
			echo '<input type="submit" name="menu" value="'.get_string('backmenu','block_bcgt').'" class="bcgtFormButton" />';
		echo '</form>';
		echo '<div id="test"></div>';
		//echo "<h3 class='menuLink'><a href='edit_unit_form.php?action=add'>Add new Unit</a></h3>";
	echo '</div>';
echo $OUTPUT->footer();
?>
