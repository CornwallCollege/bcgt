<?php
global $CFG, $DB;
//using the familyID we need to get the parent qual class. 
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/classes/ALevelQualification.class.php');
$values = AlevelQualification::get_possible_values(AlevelQualification::ID, false);
if(isset($_POST['submit']) || isset($_POST['addRow']))
{
    //then we are saving the new values;
    if($values)
    {
        foreach($values AS $value)
        {
            $value->ranking = $_POST['rank_'.$value->id];
            if(isset($_POST['value_'.$value->id]))
            {
                $value->enabled = 1;
            }
            else {
                $value->enabled = 0;
            }
            $DB->update_record('block_bcgt_value', $value);
        }
        
        //now any new row. 
        if(isset($_POST['csv_']))
        {
            $obj = new stdClass();
            $obj->shortvalue = $_POST['csv_'];
            $obj->value = $obj->shortvalue;
            
            $obj->bcgttypeid = AlevelQualification::ID;
            $obj->ranking = $_POST['rank_'];
            if(isset($_POST['value_']))
            {
                $obj->enabled = 1;
            }
            else
            {
                $obj->enabled = 0;
            }
            $id = $DB->insert_record('block_bcgt_value', $obj);
        }    
    }
}
else
{
    //something else may have been pressed?
    foreach($values AS $value)
    {
        if(isset($_POST['del_'.$value->id]))
        {
            $DB->delete_records('block_bcgt_value', array("id"=>$value->id));
        }
    }
}
$values = AlevelQualification::get_possible_values(AlevelQualification::ID, false);
$retval = '<h2>'.get_string('alevelqualsettings', 'block_bcgt').'</h2>';
$retval .= '<form name="alevelqualsettings" method="POST" action="#" enctype="multipart/form-data" id="bcgtAlevelFamSettings">';
$retval .= '<table>';
$retval .= '<tr><td colspan="2">';
//this needs to go and get all of the current grid options and have inputs
//for each
if($values)
{
    $retval .= '<table>';
    $retval .= '<thead><tr>'.
            '<th>'.get_string('enabled', 'block_bcgt').'</th>'.
            '<th>'.get_string('rank', 'block_bcgt').'</th>'.
            '<th>'.get_string('shortname', 'block_bcgt').'</th>'.
            '</tr></thead>';
    foreach($values AS $value)
    {
        $retval .= '<tr>';
        $retval .= create_value_row($value);
        $retval .= '</tr>';
    }
    
    if(isset($_POST['addRow']))
    {
        $retval .= "<tr><td colspan='3'>New Value</td></tr>";
        $retval .= create_value_row();
    }
    
    $retval .= '</table>';
}
$retval .= '</td></tr>';
$retval .= '</table>';
$retval .= '<input type="submit" name="submit" value="Save"/>';
$retval .= '<input type="submit" name="addRow" value="Save and add blank row"/>';
$retval .= '</form>';

echo $retval;

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
    $retval .= '<td><input class="bcgtValueRow" type="text" value="'.$valueRanking.'" name="rank_'.$valueID.'"/></td>';
    $shortValue = '';
    if($value)
    {
        $shortValue = $value->shortvalue;
    }
    $retval .= '<td><input class="bcgtValueRow" type="text" name="csv_'.$valueID.'" value="'.
            $shortValue.'"/></td>';
    if($value)
    {
        $retval .= '<td><input type="submit" name="del_'.$valueID.'" value="'.get_string('delete').'"/></td>';
    }
    return $retval;
}