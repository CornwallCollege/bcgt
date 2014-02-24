<?php
global $CFG, $DB;
//using the familyID we need to get the parent qual class. 
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGQualification.class.php');
$values = CGQualification::get_possible_values(CGQualification::ID);
if(isset($_POST['submit']) || isset($_POST['addRow']))
{
    //then we are saving the new values;
    if($values)
    {
        foreach($values AS $value)
        {
            $value->ranking = @$_POST['rank_'.$value->id];
            $value->customvalue = @$_POST['cv_'.$value->id];
            $value->customshortvalue = @$_POST['csv_'.$value->id];
            if(isset($_POST['value_'.$value->id]))
            {
                $value->enabled = 1;
            }
            else {
                $value->enabled = 0;
            }
            
            $DB->update_record('block_bcgt_value', $value);
            
            //now need to upload the file. 
            if(isset($_FILES['f_'.$value->id]))
            {
                process_file('f_'.$value->id, $value, false); 
            }
            
        }
        
        //now any new row. 
        if(isset($_POST['cv_']) && isset($_POST['csv_']))
        {
            $obj = new stdClass();
            $obj->value = $_POST['cv_'];
            $obj->shortvalue = $_POST['csv_'];
            $obj->bcgttypeid = CGQualification::ID;
            $obj->ranking = $_POST['rank_'];
            if(isset($_POST['value_']))
            {
                $obj->enabled = 1;
            }
            else
            {
                $obj->enabled = 0;
            }
            $obj->customvalue = $_POST['cv_'];
            $obj->customshortvalue = $_POST['csv_'];
            $id = $DB->insert_record('block_bcgt_value', $obj);
            
            $setting = new stdClass();
            $setting->bcgtvalueid = $id;
            $fileImg = process_file('f_', $obj, false, true);
            $setting->coreimg = $fileImg;
            $setting->customimg = $fileImg;
            $DB->insert_record('block_bcgt_value_settings', $setting);
            
        }    
            
        
    }
}
if(isset($_POST['revert']))
{
    //then loop through all and delete all custom values, short values, customimg and customimglate
    if($values)
    {
        foreach($values AS $value)
        {
            $value->customvalue = '';
            $value->customshortvalue = '';
            $value->enabled = 1;
            $DB->update_record('block_bcgt_value', $value);
            
            $obj = new stdClass();
            $obj->customimglate = '';
            $obj->customimg = '';
            $obj->id = $value->settingid;
            $DB->update_record('block_bcgt_value_settings', $obj); 
        }
    }
}

$values = CGQualification::get_possible_values(CGQualification::ID);
$retval = '<h2>'.get_string('cgqualsettings', 'block_bcgt').'</h2>';
$retval .= '<form name="btecqualsettings" method="POST" action="#" enctype="multipart/form-data" id="bcgtBtecFamSettings">';
$retval .= '<table>';
$retval .= '<tr><td colspan="2">';
//this needs to go and get all of the current grid options and have inputs
//for each
if($values)
{
    $retval .= '<table>';
    $retval .= '<thead><tr>'.
            '<th>'.get_string('btecenabled', 'block_bcgt').'</th>'.
            '<th>'.get_string('rank', 'block_bcgt').'</th>'.
            '<th>'.get_string('btecdefaulticon', 'block_bcgt').'</th>'.
            '<th>'.get_string('fullname', 'block_bcgt').'</th>'.
            '<th>'.get_string('shortname', 'block_bcgt').'</th>'.
            '<th>'.get_string('customfullname', 'block_bcgt').'</th>'.
            '<th></th>'.
            '<th colspan="2">'.get_string('bteccurrenticon', 'block_bcgt').'</th>'.
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
$retval .= '<input type="submit" name="revert" value="Revert to Defaults"/>';
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
    if($value)
    {
        $retval .= '<td><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/core/'.CGQualification::get_default_value_image($value->shortvalue).'"/></td>';
    }
    else
    {
        $retval .= '<td></td>';
    }
    
    
    $valueLong = '';
    if($value)
    {
        $valueLong = $value->value;
    }
    $retval .= '<td>'.$valueLong.'</td>';
    $shortValue = '';
    if($value)
    {
        $shortValue = $value->shortvalue;
    }
    $customValue = '';
    if($value)
    {
        $customValue = $value->customvalue;
    }
    $retval .= '<td>'.$shortValue.'</td>';
    $retval .= '<td><input class="bcgtValueRowL" type="text" name="cv_'.$valueID.'" value="'.
            $customValue.'"/></td>';
   
    if (!is_null($value)){
        $retval .= '<td></td>';    
    } else {
        $retval .= '<td><input class="bcgtValueRow" type="text" name="csv_'.$valueID.'" value="" />';
    }
   
    if($value && $value->customimg != '')
    {
        $retval .= '<td><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg'.$value->customimg.'"/></td>';
    }
    else
    {
        $retval .= '<td></td>';
    }
    $retval .= '<td><input type="file" name="f_'.$valueID.'" value="file" id="file"/></td>';
    if($value && $value->customimglate != '')
    {
        $retval .= '<td><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtcg/pix/grid_symbols/custom/custom_'.$value->shortvalue.'.png"/></td>';
    }
    else
    {
        $retval .= '<td></td>';
    }

    return $retval;
}

/**
 * 
 * @global type $CFG
 * @global type $DB
 * @param type $fileName
 * @param type $value
 * @param type $late
 * @param type $newRecord
 * @return type
 */
function process_file($fileName, $value, $late = false, $newRecord = false)
{
    global $CFG, $DB;
    $filePath = '/pix/grid_symbols/custom/';
    $fullFilePath = $CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg'.$filePath;
    $_FILES[$fileName]["name"] = $value->shortvalue . '.png';

    if ($_FILES[$fileName]["error"] > 0)
    {
//      echo "Return Code: " . $_FILES['fLate_'.$value->id]["error"] . "<br>";
    }
    else
    {
        
        //delete the old one
        if(!$newRecord)
        {
            
            $oldPic = $value->customimg;

            if($oldPic)
            {
                try{
                    unlink($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg'.$oldPic);
                }
                catch(Exception $e)
                {
                    echo "Couldnt delete old icon";
                }
            }
        }
        
        move_uploaded_file($_FILES[$fileName]["tmp_name"],
            $fullFilePath . $_FILES[$fileName]["name"]);
        
        if(!$newRecord)
        {
            //now we need to update the database.
            $obj = new stdClass();
            $obj->customimg = $filePath.$_FILES[$fileName]["name"];
            $obj->id = $value->settingid;
            $DB->update_record('block_bcgt_value_settings', $obj); 
        }
         return $filePath.$_FILES[$fileName]["name"];
    }
}