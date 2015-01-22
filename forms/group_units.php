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
$qualID = optional_param('qID', -1, PARAM_INT);
if($qualID == -1)
{
    redirect($CFG->wwwroot.'/blocks/bcgt/forms/qual_select.php');
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
if (isset($_POST['editQualUnits']))
{
    redirect('edit_qual_units.php?qID=' . $qualID);
}



$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELUNITS;
$qualification = Qualification::get_qualification_class_id($qualID, $loadParams);

$currentUnits = false;
$qualUnitGroups = false;



$url = '/blocks/bcgt/forms/group_units.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('groupunits', 'block_bcgt'));
$PAGE->set_heading(get_string('groupunits', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);

$PAGE->requires->js_init_call('M.block_bcgt.initgroupunits', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();

echo $OUTPUT->header();

if (isset($_POST['unitGroupNames'])){
    
    // Clear groups
    $DB->execute("UPDATE {block_bcgt_qual_units} SET groupname = NULL WHERE bcgtqualificationid = ?", array($qualID));
    
    $groupNames = $_POST['unitGroupNames'];
    $groupUnits = $_POST['unitGroupUnits'];
    
    foreach ($groupNames as $id => $name)
    {
        
        $name = trim($name);
        
        if (!empty($name))
        {
            
            // Find units we want on this group
            $units = $groupUnits[$id];
            
            foreach($units as $unitID)
            {
                
                $record = $DB->get_record("block_bcgt_qual_units", array("bcgtqualificationid" => $qualID, "bcgtunitid" => $unitID));
                if ($record)
                {
                    $record->groupname = $name;
                    $DB->update_record("block_bcgt_qual_units", $record);
                }
                
            }
            
        }
        
    }
    
    
}

if ($qualification)
{
    $currentUnits = $qualification->get_ungrouped_units();
    $qualUnitGroups = $qualification->get_unit_groups();
}



echo'<h2 class="formheading">Qualification Units</h2>';
	echo '<div id="editQualUnits" class="bcgt_admin_controls">';
		echo '<h3 class="menuLink">'.get_string('qualification','block_bcgt').' : ';
		if($qualification)
		{
			echo $qualification->get_type().''. 
			' '.$qualification->get_level()->get_level().''. 
			' '.$qualification->get_subType()->get_subType();
			echo ' '.$qualification->get_name().'<br />';
		}
		echo '</h3>';
		echo '<form id="unitGroupForm" method="post" action="">';	
			echo '<input type="hidden" id="qID" name="qID" value="'.$qualID.'"/>';
            echo '<br><br>';
            echo '<p><input type="button" onclick="saveUnitGroupsForm(\'unitGroupForm\');return false;" name="saveUnitGroups" value="'.get_string('save', 'block_bcgt').'" class="bcgtFormButton" /></p>';           
            
            echo '<div id="unitsQualContainer" class="bcgt_three_c_container bcgt_float_container">';	
            
				echo '<div id="unitsLeft" class="bcgt_admin_left bcgt_col">';
					echo '<h2 class="formsubheading">'.get_string('editqualunithead', 'block_bcgt').'</h2>';
					echo '<br />';
					echo '<select size="20" id="unitlist" multiple="multiple">';
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
					
				echo '</div>';
                
				echo '<div id="unitsCenter" class="bcgt_admin_center bcgt_col">';

                echo '</div>';
                
				echo '<div id="unitsRight" class="bcgt_admin_right bcgt_col">';
                
                    echo '<br>';
                    echo '<p><input type="button" onclick="addUnitGroup();return false;" value="'.get_string('addgroup', 'block_bcgt').'" /></p>';
                    
                    echo '<div id="unitGroups">';
                    
                        if ($qualUnitGroups)
                        {
                            
                            $i = 1;
                            
                            foreach($qualUnitGroups as $group)
                            {
                                
                                echo "<div class='unitGroup' id='unitGroup_{$i}'>";

                                    echo "<p><input type='text' name='unitGroupNames[{$i}]' placeholder='Group name' value='{$group->name}' /></p>";
                                    echo "<div>";
                                        echo "<select id='unitGroupUnits_{$i}' name='unitGroupUnits[{$i}][]' multiple='multiple'>";

                                            if ($group->units)
                                            {
                                                foreach($group->units as $groupUnit)
                                                {
                                                    
                                                    $unit = $qualification->get_single_unit($groupUnit->bcgtunitid);
                                                    
                                                    if ($unit)
                                                    {
                                                    
                                                        $uniqueID = $unit->get_uniqueID();
                                                        if($unit->get_level() && $unit->get_level()->get_short() != '')
                                                        {
                                                            $uniqueID = '('.$unit->get_level()->get_short().') '.$unit->get_uniqueID().'';
                                                        }
                                                        echo '<option value="'.$unit->get_id().'" title="'.$unit->get_name().'">'.$uniqueID.' : '.$unit->get_name().'</option>';	

                                                    }
                                                    
                                                }
                                            }
                                        
                                        echo "</select>";
                                        echo "<br>";
                                        echo "<p><input type='button' onclick='addUnitsToGroup({$i});return false;' value='Add Selected Units' />&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' onclick='removeUnitsFromGroup({$i});return false;' value='Remove Selected Units' /></p>";
                                        echo "<p><input type='button' onclick='removeUnitGroup({$i});return false;' value='Remove Group' /></p>";
                                    echo "</div>";

                                echo "</div>";
                                
                                $i++;
                                
                            }
                        }
                    
                    echo '</div>';
                    
                echo '</div>';
                
			echo '</div>';
            
            echo '<p><input type="button" onclick="saveUnitGroupsForm(\'unitGroupForm\');return false;" name="saveUnitGroups" value="'.get_string('save', 'block_bcgt').'" class="bcgtFormButton" /></p>';           
            
            echo '<br><br>';
            echo '<br><br>';
            
			echo '<input type="submit" name="editQualUnits" value="'.get_string('editqualunits','block_bcgt').'" class="bcgtFormButton" />';
            
            echo '<br><br>';
            
			echo '<input type="submit" name="addNewUnit" value="'.get_string('addnewunit','block_bcgt').'" class="bcgtFormButton" />';
			echo '<input type="submit" name="qualPicker" value="'.get_string('qualpicker','block_bcgt').'" class="bcgtFormButton"/>';
			echo '<input type="submit" name="menu" value="'.get_string('backmenu','block_bcgt').'" class="bcgtFormButton" />';
		echo '</form>';
	echo '</div>';
echo $OUTPUT->footer();