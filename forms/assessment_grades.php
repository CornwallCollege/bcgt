<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;;
require_once('../../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

$cID = optional_param('cID', -1, PARAM_INT);
$action = optional_param('act', '', PARAM_INT);
if($cID != -1)
{
    $context = context_course::instance($cID);
}
else
{
    $context = context_course::instance($COURSE->id);
}
require_login();
$PAGE->set_context($context);
require_capability('block/bcgt:viewactivitylinks', $context);

$url = '/blocks/bcgt/forms/assessment_grades.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('managefagrades', 'block_bcgt'));
$PAGE->set_heading(get_string('managefagrades', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('activity', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
$PAGE->navbar->add(get_string('managefagrades', 'block_bcgt'),'','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initactivities', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();

$out .= html_writer::tag('h2', get_string('managefagrades','block_bcgt').
        '', 
        array('class'=>'formheading'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_activity_controls', 
    'id'=>'editCourseQual'));
$out .= '<form name="assessments" method="POST" action=""/>';
$out .= '<input type="hidden" name="cID" value="'.$cID.'"/>';
//load up all of the formal assessments already been created. 
$out .= '<div id="projectTileContainer">';
if(isset($_POST['cancel']))
{
    $action = '';
}
if($action == 'edit')
{
    $targetQualID = optional_param('tqID', -1, PARAM_INT);
    $familyID = optional_param('fID', -1, PARAM_INT);
    $typeID = optional_param('tID', -1, PARAM_INT);
    //then we get the targetqual in question
    $out .= '';
    $targetQual = bcgt_get_target_qual_id($targetQualID);
    if($targetQual)
    {
        $out .= '<h2>'.$targetQual->family.' '.$targetQual->trackinglevel.' '.$targetQual->subtype.'</h2>';
    }
    $out .= '<input type="hidden" name="tqID" value="'.$targetQualID.'"/>';
    $out .= '<input type="hidden" name="fID" value="'.$familyID.'"/>';
    $out .= '<table>';
    $out .= '<thead><tr>';
    
    $out .= '<th>'.get_string('enabled', 'block_bcgt').'</th>';
    $out .= '<th>'.get_string('rank', 'block_bcgt').'</th>';
    $out .= '<th>'.get_string('grade', 'block_bcgt').'</th>';
    $out .= '<th>'.get_string('delete', 'block_bcgt').'</th>';
    
    $out .= '</tr></thead>';
    $out .= '<tbody>';
    
    $possibleValues = array();
    $qualification = Qualification::get_correct_qual_class(-1, -1, $familyID);
    if($qualification)
    {
        $possibleValues = Qualification::get_possible_assessment_valued($qualification->get_family_instance_id(), $targetQualID, false);
    }
    if(isset($_POST['submit']) || isset($_POST['addRow']))
    {
                
        //then we are saving the new values;
        if($possibleValues)
        {
            foreach($possibleValues AS $value)
            {
                $value->ranking = $_POST['rank_'.$value->id];
                if(isset($_POST['value_'.$value->id]))
                {
                    $value->enabled = 1;
                }
                else {
                    $value->enabled = 0;
                }
                $value->shortvalue = $_POST['csv_'.$value->id];
                $value->value = $_POST['csv_'.$value->id];
                $DB->update_record('block_bcgt_value', $value);
            }
        }
        
        //now any new row. 
        if(isset($_POST['csv_']))
        {
            $obj = new stdClass();
            $obj->shortvalue = $_POST['csv_'];
            $obj->value = $obj->shortvalue;

            $obj->bcgttypeid = $qualification->get_family_instance_id();
            $obj->context = 'assessment';
            $obj->ranking = $_POST['rank_'];
            if(isset($_POST['value_']))
            {
                $obj->enabled = 1;
            }
            else
            {
                $obj->enabled = 0;
            }
            $DB->insert_record('block_bcgt_value', $obj);
        }   
        
    }
    else
    {
        //something else may have been pressed?
        foreach($possibleValues AS $value)
        {
            if(isset($_POST['del_'.$value->id]))
            {
                $DB->delete_records('block_bcgt_value', array("id"=>$value->id));
            }
        }
    }    
    if($qualification)
    {
        $possibleValues = Qualification::get_possible_assessment_valued($qualification->get_family_instance_id(), $targetQualID, false);
    }  
       
    if($possibleValues)
    {
        foreach($possibleValues AS $possibleValue)
        {          
            $out .= '<tr>';
            $out .= create_value_row($possibleValue);
            $out .= '</tr>';
        }
    }
    else
    {
        $out .= '<tr>';
        $out .= create_value_row();
        $out .= '</tr>';        
    }

    if(isset($_POST['addRow']))
    {
        $out .= "<tr><td colspan='3'>New Value</td></tr>";
        $out .= create_value_row();
    }
    $out .= '</tbody>';
    $out .= '</table>';
    
    $out .= '<input type="submit" name="submit" value="Save"/>';
    $out .= '<input type="submit" name="addRow" value="Save and add blank row"/>';
    $out .= '<input type="submit" name="cancel" value="Cancel"/>';
    
}
else
{
    $faFamilies = array();
    $families = get_qualification_type_families_used();
    if($families)
    {
        foreach($families AS $family)
        {
            $familyClass = Qualification::get_plugin_class($family->id);
            if($familyClass && $familyClass::has_formal_assessments())
            {
                $faFamilies[] = $family->family;
            }
        }
    }

    //foreach family get the targetquals
    $out .= '<table>';
    $out .= '<thead>';
    $out .= '<tr><th>Family</th><th>Level</th><th>Subtype</th></tr></thead><tbody>';
    $targetQuals = bcgt_get_target_quals_array($faFamilies);
    foreach($targetQuals AS $targetQual)
    {
        $out .= '<tr>';
        $out .= '<td>'.$targetQual->family.'</td>';
        $out .= '<td>'.$targetQual->trackinglevel.'</td>';
        $out .= '<td>'.$targetQual->subtype.'</td>';
        $out .= '<td><a href="?act=edit&tqID='.$targetQual->id.'&fID='.$targetQual->familyid.'&tID='.$targetQual->typeid.'">'.get_string('editgrades', 'block_bcgt').'</a></td>';
        $out .= '</tr>';
    }
    $out .= '</tbody></table>';
}



$out .= '</div>';
//ability to add a new project/formal assessment
$out .= '<div>';
$out .= '</div>';
//link to add a new formal assessment
$out .= '</form>';
$out .= html_writer::end_tag('div');//end main column
$out .= $OUTPUT->footer();

echo $out;


function create_value_row($value = null)
{
    $retval = '';
    global $CFG;
    $valueID = '';
    if($value)
    {
        $valueID = $value->id;
    }
    $retval .= '<td><input type="checkbox" name="value_'.$valueID.'"';
    if(($value && $value->enabled) || !$value)
    {
        $retval .= 'checked="checked"';
    }
    $retval .= '/></td>';
    $valueRanking = '';
    if($value)
    {
        $valueRanking = $value->ranking;
    }
    $retval .= '<td><input class="" type="text" value="'.$valueRanking.'" name="rank_'.$valueID.'"/></td>';
    $shortValue = '';
    if($value)
    {
        $shortValue = $value->shortvalue;
    }
    $retval .= '<td><input class="" type="text" name="csv_'.$valueID.'" value="'.
            $shortValue.'"/></td>';
    if($value)
    {
        $retval .= '<td><input type="submit" name="del_'.$valueID.'" value="'.get_string('delete').'"/></td>';
    }
    return $retval;
}

?>
