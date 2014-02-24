<?php
global $CFG, $DB;
//using the familyID we need to get the parent qual class. 
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECQualification.class.php');
$values = BTECQualification::get_possible_values(BTECQualification::ID);
if(isset($_POST['submit']) || isset($_POST['addRow']))
{
    //then we are saving the new values;
    if($values)
    {
        foreach($values AS $value)
        {
            $value->ranking = $_POST['rank_'.$value->id];
            $value->customvalue = $_POST['cv_'.$value->id];
            $value->customshortvalue = $_POST['csv_'.$value->id];
            if(isset($_POST['value_'.$value->id]))
            {
                $value->enabled = 1;
            }
            else {
                $value->enabled = 0;
            }
            
            $DB->update_record('block_bcgt_value', $value);
            
            //now need to upload the file. 
            if($_FILES['f_'.$value->id])
            {
                process_file('f_'.$value->id, $value, false); 
            }
            
            if($_FILES['fLate_'.$value->id])
            {
                process_file('fLate_'.$value->id, $value, true);
            }
        }
        
        //now any new row. 
        if(isset($_POST['cv_']) && isset($_POST['csv_']))
        {
            $obj = new stdClass();
            $obj->value = $_POST['cv_'];
            $obj->shortvalue = $_POST['csv_'];
            $obj->bcgttypeid = 2;
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
            $fileImgLate = process_file('fLate_', $obj, true, true);
            $setting->coreimg = $fileImg;
            $setting->customimg = $fileImg;
            $setting->coreimglate = $fileImgLate;
            $setting->customimglate = $fileImgLate;
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
$values = BTECQualification::get_possible_values(BTECQualification::ID);
$retval = '<h2>'.get_string('btecqualsettings', 'block_bcgt').'</h2>';
$retval .= '<form name="btecqualsettings" method="POST" action="#" enctype="multipart/form-data" id="bcgtBtecFamSettings">';
$retval .= '<table>';
$retval .= '<tr><td><label>'.get_string('btecshowaspgrades', 'block_bcgt').'</label>'.
        '</td><td><input type="checkbox" disabled="disabled" name="aspirationalgrades"/></td></tr>';
$retval .= '<tr><td>'.get_string('btecgridoptions', 'block_bcgt').'</td></tr>';
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
            '<th>'.get_string('btecdefaulticonlate', 'block_bcgt').'</th>'.
            '<th>'.get_string('fullname', 'block_bcgt').'</th>'.
            '<th>'.get_string('shortname', 'block_bcgt').'</th>'.
            '<th>'.get_string('customfullname', 'block_bcgt').'</th>'.
            '<th>'.get_string('customshortname', 'block_bcgt').'</th>'.
            '<th colspan="2">'.get_string('bteccurrenticon', 'block_bcgt').'</th>'.
            '<th colspan="2">'.get_string('bteccurrenticonlate', 'block_bcgt').'</th>'.
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
        $retval .= '<td><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec'.$value->coreimg.'"/></td>';
    }
    else
    {
        $retval .= '<td></td>';
    }
    if($value && $value->coreimglate != '')
    {
        $retval .= '<td><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec'.$value->coreimglate.'"/></td>';
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
    $customShortValue = '';
    if($value)
    {
        $customShortValue = $value->customshortvalue;
    }
    $retval .= '<td><input class="bcgtValueRow" type="text" name="csv_'.$valueID.'" value="'.
            $customShortValue.'"/></td>';
    if($value && $value->customimg != '')
    {
        $retval .= '<td><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec'.$value->customimg.'"/></td>';
    }
    else
    {
        $retval .= '<td></td>';
    }
    $retval .= '<td><input type="file" name="f_'.$valueID.'" value="file" id="file"/></td>';
    if($value && $value->customimglate != '')
    {
        $retval .= '<td><img src="'.$CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec'.$value->customimglate.'"/></td>';
    }
    else
    {
        $retval .= '<td></td>';
    }
    $retval .= '<td><input type="file" name="fLate_'.$valueID.'" value="lateFile" id="lateFile"/></td>';
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
    $fullFilePath = $CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec'.$filePath;

    if ($_FILES[$fileName]["error"] > 0)
    {
//      echo "Return Code: " . $_FILES['fLate_'.$value->id]["error"] . "<br>";
    }
    else
    {
        move_uploaded_file($_FILES[$fileName]["tmp_name"],
            $fullFilePath . $_FILES[$fileName]["name"]);
        //delete the old one
        if(!$newRecord)
        {
            if($late)
            {
                $oldPic = $value->customimglate;
            }
            else {
                $oldPic = $value->customimg;
            }

            if($oldPic)
            {
                try{
                    unlink($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec'.$oldPic);
                }
                catch(Exception $e)
                {
                    echo "Couldnt delete old icon";
                }
            }
        }
        
        if(!$newRecord)
        {
            //now we need to update the database.
            $obj = new stdClass();
            if($late)
            {
                $obj->customimglate = $filePath.$_FILES[$fileName]["name"];
            }
            else
            {
                $obj->customimg = $filePath.$_FILES[$fileName]["name"];
            }

            $obj->id = $value->settingid;
            $DB->update_record('block_bcgt_value_settings', $obj); 
        }
         return $filePath.$_FILES[$fileName]["name"];
    }
}