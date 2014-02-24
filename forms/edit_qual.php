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
$qualID = optional_param('qID', -1, PARAM_INT);
if($qualID == -1)
{
    require_capability('block/bcgt:addnewqual', $context);
}
else
{
    require_capability('block/bcgt:editqual', $context);
}

//$action = optional_param('action', 'add', PARAM_TEXT);

$typeID = optional_param('tID', -1, PARAM_INT);
$familyID = optional_param('fID', -1, PARAM_INT);
$name = optional_param('name', '', PARAM_TEXT);
$code = optional_param('code', '', PARAM_TEXT);
$noYears = optional_param('noyears', 1, PARAM_INT);
$additionalName = optional_param('addname', '', PARAM_TEXT);
$pathway = optional_param('pathway', -1, PARAM_INT);
$pathwayType = optional_param('pathwaytype', -1, PARAM_INT);


$qualification = null;
$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELMIN;

// If we have set a pathway and a pathway type, then the typeID is going to change
if ($familyID > 0 && $pathway > 0 && $pathwayType > 0){
    
    $newType = $DB->get_record("block_bcgt_type", array("bcgttypefamilyid" => $familyID, "bcgtpathwaydeptid" => $pathway, "bcgtpathwaytypeid" => $pathwayType));
    if ($newType){
        $typeID = $newType->id;
    }
    
}


if($qualID != -1)
{
    //go and get the small qualification object
    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
    if($qualification)
    {
        $typeID = $qualification->get_class_ID();
        $familyID = $qualification->get_family_ID(); 
    }
    
}
elseif($typeID != -1)
{
    //we have all of the drop downs and options that can be used to 
    //calculate the qualification. It will be quicker to use this to get the
    //class rather than the family stuff. 
	$qualification = Qualification::get_qualification_class($typeID, 
            -1, -1, null, $loadParams);
	if($qualification && $familyID == -1)
	{
		$familyID = $qualification->get_family_ID();
	} 
}
elseif($familyID != -1)
{
    
    //then we arent editing a qual, we are creating a new one but the family
    //of the qualification has been selected.
	$qualification = Qualification::get_qualification_class($typeID, $qualID, 
            $familyID, null, $loadParams);
    if($qualification)
	{
		$typeID = $qualification->get_class_ID();
	}
        
    
}
if(isset($_POST['save']))
{    
	//each qualification must have a level and a subtype
	if(isset($_POST['level']))
	{
		$levelID = $_POST['level'];
	}
	if(isset($_POST['subtype']))
	{
		$subTypeID = $_POST['subtype'];
	}	
                    
	if($qualification)
	{	
        //qualification MUST have been set before this bit
        //if we saved it then we must have been able to have created a qual object
        $qualification->set_name($name);
        $qualification->set_additional_name($additionalName);
        $qualification->set_code($code);
        $qualification->set_no_years($noYears);
		if(isset($_POST['level']))
		{
			$qualification->add_level($levelID);
		}
		if(isset($_POST['subtype']))
		{
			$qualification->add_subType($subTypeID);
		}
		$qualification->get_submitted_edit_form_data();

        
        // process submitted data
        if ($qualification->process_create_update_qual_form()){
                    
            //if its a new one insert
            if($qualID == -1)
            {
                    //insert
                    $qualification->insert_qualification();
                    $qualID = $qualification->get_id();
            }
            else
            {
                    //Update
                    $qualification->update_qualification();
            }
        
        } 
        
	}
	if(isset($_POST['save']))
	{
        redirect($CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=adm');
//        but what about things like ALEVELS (they dont have units)
//        what about other scenarios such as we have just added this to a course?
//        we need a way of redirecting successfully
//        redirect($CFG->wwwroot.'/mod/qualification/edit_qualification_units.php?qualID='.$qualID);		
	}
}
elseif(isset($_POST['cancel']))
{
    $levelID = -1;
    $subTypeID = -1;
    if($qualification)
    {
        $level = $qualification->get_level();
        if($level)
        {
            $levelID = $level->get_id();
        }
        $subtype = $qualification->get_subtype();
        if($subtype)
        {
            $subTypeID = $subtype->get_id();
        }
    }
    //??
	redirect($CFG->wwwroot.'/blocks/bcgt/forms/qual_select.php?family='.$familyID.'&level='.$levelID.'&subtype='.$subTypeID);
}
elseif($qualification)
{
	if($name == '')
	{
		$name = $qualification->get_name();
	}
	$code = $qualification->get_code();
    $additionalName = $qualification->get_additional_name();
    $noYears = $qualification->get_no_years();
}
$url = '/blocks/bcgt/forms/edit_qual.php';
$PAGE->set_url($url, array());
$string = 'addnewqual';
if($qualID != -1)
{
    $string = 'editqual';
}
$PAGE->set_title(get_string($string, 'block_bcgt'));
$PAGE->set_heading(get_string($string, 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class($string);
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
$PAGE->navbar->add(get_string('myDashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string($string, 'block_bcgt'));
$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initeditqual', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript(true);

$families = get_qualification_type_families_used();

echo $OUTPUT->header();
echo'<h2 class="bcgt_form_heading">'.get_string('addeditqualsheading', 'block_bcgt').'</h2>';
	echo "<div id='bcgtEditQualForm' class='bcgt_admin_controls'>";
		echo "<form method='POST' name='editQualificationForm' id='editQualificationForm' action='edit_qual.php?'>";
			echo '<input type="hidden" name="cID" value="'.$courseID.'"/>';
            echo "<input type='hidden' name='qID' id='qID' value='$qualID'/>";
			echo "<input type='hidden' name='tID' id='tID' value='$typeID'/>";
			$disabled = '';
			if($qualID != -1)
			{
				$disabled = 'disabled';
            }
            
            if ($qualification && $qualification->get_processed_errors()){
                echo '<div class="error">'.$qualification->get_processed_errors().'</div><br><br>';
            }
            
            //TODO add to a course
			echo '<div class="inputContainer"><div class="inputLeft">';
            echo '<label for="qualFamilySelect"><span class="required">*</span>'
                .get_string('qualfamily', 'block_bcgt').': </label></div>';
			echo '<div class="inputRight"><select id="qualFamilySelect" '.$disabled.' name="fID"><option value="">'.get_string('pleaseselect', 'block_bcgt').'</option>';
            if($families)
            {
                foreach($families as $family) {
                    $selected = '';
                    if($familyID != -1 && $family->id == $familyID)
                    {
                        $selected = 'selected';
                    }
                    echo "<option  $selected value='".$family->id."'>".$family->family."</option>";
                }	
            }
			echo "</select></div></div>";
			
			//This is now where we now need to go and get the qualdrop downs based on the family. 
			echo Qualification::get_qualification_edit_form_menu($familyID, $disabled, $qualID, $typeID);
			echo '<div class="inputContainer"><div class="inputLeft"><label for="name">';
            echo '<span class="required">*</span>'.get_string('name', 'block_bcgt').' : </label></div>';
            echo '<div class="inputRight"><input type="text" name="name" id="qualName" value="'.$name.'"/>';
            echo '</div></div>';
            
            echo '<div class="inputContainer"><div class="inputLeft"><label for="addname">';
            echo get_string('addname', 'block_bcgt').' : </label></div>';
			
            echo '<div class="inputRight"><input type="text" name="addname" id="qualAddName"'.
                    'value="'.$additionalName.'"/>';
            echo '</div></div>';
            
			echo '<span id="warning"></span>';
			echo '<div class="inputContainer"><div class="inputLeft"><label for="code">'
            .get_string('qualuniqueid', 'block_bcgt').' : </label></div>';
			echo '<div class="inputRight"><input type="text" name="code" value="'.$code.'"/></div></div>';
			echo '<div class="inputContainer"><div class="inputLeft"><label for="noyears">';
            echo get_string('noyears', 'block_bcgt').' : </label></div>';
			
            echo '<div class="inputRight"><input type="number" name="noyears" id="qualNoYears"'.
                    'value="'.$noYears.'"/>';
            echo '</div></div>';
			if($qualification)
			{
				//These are specific forms dependant on the qualification selected. 
				echo $qualification->get_edit_form_fields();
			}
            
            //TODO copy units from another qualification
			echo '<div id="controls">';
                        echo '<br /><br />';
			echo '<input type="submit" name="save" value="Save" id="save" class="bcgtFormButton" />';
			echo '<input type="submit" name="cancel" value="Cancel" class="bcgtFormButton" />';
			echo '</div>';
		echo '</form>';
		//echo "<SCRIPT language=JavaScript>form=document.getElementById('editQualificationForm');checkQual(form, false);checkSave();</script>";
	echo '</div>';


echo $OUTPUT->footer();
?>
