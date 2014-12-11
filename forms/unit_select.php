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
$unitID = optional_param('unitID', -1, PARAM_INT);
$redirect = '';
if(isset($_POST['edit']))
{
	$redirect = 'edit_unit.php?unitID='.$unitID;
}
if($redirect != '')
{
	redirect($redirect);
}

$PAGE->set_context($context);
require_capability('block/bcgt:searchunits', $context);
$tab = optional_param('page', 1, PARAM_INTEGER);
$url = '/blocks/bcgt/forms/unit_selectphp';
$string = 'selectunit';
$PAGE->set_url($url, array('page' => $tab));
$PAGE->set_title(get_string('selectunit', 'block_bcgt'));
$PAGE->set_heading(get_string('selectunit', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
//$PAGE->navbar->add(get_string('bcgtmydashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string($string, 'block_bcgt'));
$PAGE->navbar->add('',$url.'?page='.$tab,'title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initselunit', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
echo $OUTPUT->header();

$typeID = optional_param('type', -1, PARAM_INT);
$qualTypeID = optional_param('qualType', -1, PARAM_INT);
$qualID = optional_param('qualID', -1, PARAM_INT);
$levelID = optional_param('level', -1, PARAM_INT);
$subTypeID = optional_param('subtype', -1, PARAM_INT);
$search = optional_param('searchKeyword', '', PARAM_TEXT);
$qualSearch = optional_param('qualksearch', '', PARAM_TEXT);
$qualFamilyID = optional_param('qualFamily', -1, PARAM_INT);
$unitFamilyID = optional_param('unitFamily', -1, PARAM_INT);
$unitLevelID = optional_param('unitLevel', -1, PARAM_INT);

$searchResults = search_unit($typeID, $qualID, $search, $levelID, $subTypeID, '', 
        $qualTypeID, '', '', $unitFamilyID, $qualFamilyID, $unitLevelID, -1, $qualSearch);
$families = get_qualification_type_families_used();
$subTypes = get_subtype_from_type(-1, $levelID, $qualFamilyID);
$levels = get_level_from_type(-1, $qualFamilyID);
$unitLevels = get_level_from_type(-1, $unitFamilyID);

	echo '<h2 class="formheading">'.get_string('selectunit', 'block_bcgt').'</h2>';
	echo '<div id="editUnitForm" class="bcgt_admin_controls">';
	echo '<h3 class="bcgtSubTitle"><a href="edit_unit.php">'.
            get_string('addnewunit', 'block_bcgt').'</a></h3>';    
		echo '<div class="bcgt_two_c_container bcgt_float_container" id="bcgtColumnConainer">';		
			echo '<div id="search" class="bcgt_admin_left bcgt_col">';
			echo '<div id="unitsSearchParameters">';
				echo '<form method="post" name="unitSearchForm" action="unit_select.php">';
					echo '<input type="hidden" name="cID" value="'.$courseID.'"/>';
                    echo '<h3 class="subTitle">'.get_string('qualsearchpar', 'block_bcgt').'</h3>';
					echo '<p>'.get_string('unitsearchhelp','block_bcgt').'</p>';
                    
					echo '<div class="inputContainer"><div class="inputLeft">'.
                            '<label for="qualFamily">'.
                            get_string('qualfamily', 'block_bcgt').'</label></div>';
					echo '<div class="inputRight"><select name="qualFamily">'.
                            '<option value="-1">Please Select one</option>';
						if($families)
						{
							foreach($families as $family) {
								$selected = '';
								if($family->id == $qualFamilyID)
								{
									$selected = 'selected';
								}
								echo "<option $selected value='$family->id'>$family->family</option>";
							}	
						}
					echo '</select></div></div>';
					echo '<div class="inputContainer"><div class="inputLeft"><label for="level">' 
                            .get_string('level', 'block_bcgt').'</label></div>';
					echo '<div class="inputRight"><select name="level">'.
                            '<option value="-1">Please Select one</option>';
						if($levels)
						{
							foreach($levels as $level) {
								$selected = '';
								if($level->get_id() == $levelID)
								{
									$selected = 'selected';
								}
								echo "<option $selected value='".$level->get_id()."'>".
                                        $level->get_level()."</option>";
							}	
						}
					echo '</select></div></div>';
					echo '<div class="inputContainer"><div class="inputLeft">'.
                            '<label for="subtype">'.get_string('subtype', 'block_bcgt').
                            '</label></div>';
					echo '<div class="inputRight"><select name="subtype">'.
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
                            '<label for="qualksearch">'.get_string('kqualsearch', 'block_bcgt').
                            '</label></div>';
					echo '<div class="inputRight"><input type="text" name="qualksearch" value="'.$qualSearch.'"/>'
                            .'</div></div>';
                    
					echo '<h3 class="subTitle">'.get_string('unitsearchpar', 'block_bcgt').'</h3>';
					echo '<div class="inputContainer"><div class="inputLeft">'.
                            '<label for="unitFamily">'.get_string('unitfamily', 'block_bcgt').
                            '</label></div>';
					echo '<div class="inputRight"><select name="unitFamily">'.
                            '<option value="-1">Please Select one</option>';
						if($families)
						{
							foreach($families as $family) {
								$selected = '';
								if($family->id == $unitFamilyID)
								{
									$selected = 'selected';
								}
								echo "<option $selected value='$family->id'>$family->family</option>";
							}	
						}
					echo '</select></div></div>';
					echo '<div class="inputContainer"><div class="inputLeft">'.
                            '<label for="unitLevel">'.get_string('level', 'block_bcgt').
                            '</label></div>';
					echo '<div class="inputRight"><select name="unitLevel">'.
                            '<option value="-1">Please Select one</option>';
						if($unitLevels)
						{
							foreach($unitLevels as $level) {
								$selected = '';
								if($level->get_id() == $unitLevelID)
								{
									$selected = 'selected';
								}
								echo "<option $selected value='".$level->get_id()."'>".$level->get_level()."</option>";
							}	
						}
					echo '</select></div></div>';
					echo '<div class="inputContainer"><div class="inputLeft">'.
                            '<label for="searchKeyword">'.get_string('ksearch', 'block_bcgt').
                            '</label></div>';
					echo '<div class="inputRight"><input type="text" name="searchKeyword" value="'.$search.'"/>'
                            .'</div></div>';
					echo '</div>';//ends the box with all of the select options in them
					echo '<input type="submit" name="search" value="Search"/>';
				echo '</form>';
			echo '</div>';	
			echo '<div id="unitsSelectList" class="bcgt_admin_right bcgt_col">';
                echo '<form method="post" name="unitSelectForm" action="unit_select.php">';
					$select = '<select name="unitID" size="20" id="addselect">';
					$loadParams = new stdClass();
                    $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
                    if($searchResults)
                    {
                        foreach($searchResults AS $result)
                        {
                            $unit = Unit::get_unit_class_id($result->id, $loadParams);
                            if($unit)
                            {
                                if (isset($result->isbespoke)){
                                    $select .= "<option value='".$unit->get_id()."' title='".$unit->get_name()."'>".$unit->get_display_type()." : ".$unit->get_uniqueID()." : ".$unit->get_name()."</option>";
                                } else {
                                    $select .= "<option value='".$unit->get_id()."' title='".$unit->get_name()."'>".$unit->get_type_name()." : ".$unit->get_uniqueID()." : ".$unit->get_name()."</option>";
                                }
                            }
                        }
                    }
					$select .= '</select><br />';
                    echo $select;
						echo '<input type="submit" name="edit" value="Edit Unit" class="bcgtFormButton" />';
						echo '<input type="submit" disabled="disabled" name="viewUnit" value="View Unit Criteria Details" class="bcgtFormButton" />';
						echo '<input type="submit" disabled="disabled" name="viewUnitQual" value="View Qualifications this Unit is on" class="bcgtFormButton" />';
				echo '</form>';
			echo '</div>';
		echo '</div>';
		echo '<h3 class="subTitle"><a href="edit_unit_form.php?action=add">Add a new Unit</a></h3>';
		echo '<h3 class="menuLink"><a href="qual_unit_menu.php">Back to Menu</a></h3>';
	echo '</div>';
echo $OUTPUT->footer();
?>
