<?php
header("Content-Type: text/html; charset=utf-8");
require_once '../../../../../config.php';
require_once('../../../lib.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/lib.php');

require_login();

$action = optional_param('action', null, PARAM_TEXT);
$params = optional_param_array('params', null, PARAM_TEXT);

// If no action or params are set, exit the script
if(is_null($action)) exit;

$qualID = $params['qualID'];
$unitID = $params['unitID'];
$studentID = $params['studentID'];

$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELALL;

$qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
if (!$qualification){
    _err();
}

$qualification->load_student_information($studentID, $loadParams);
$unit = $qualification->get_unit($unitID);

if (!$unit){
    _err();
}

switch($action)
{
    
    case 'confirmUnitCommentsRead':
        
        if ($USER->id <> $studentID){
            echo " $('#student_dialog_S{$studentID}_U{$unitID}_Q{$qualID}').dialog('close'); ";
            exit;
        }
        
        $unit->update_student_comments($qualID, $params['studentComments']);
        $qualification->set_attribute("read_unit_comments_{$unit->get_id()}", time(), $USER->id);
        
        echo " $('#student_dialog_S{$studentID}_U{$unitID}_Q{$qualID}').dialog('close'); ";
        echo " $($('#studentUnitComments_S{$studentID}_U{$unitID}_Q{$qualID}').parents('td')[0]).effect('highlight', {color: '#ccff66'}, 3000); ";

    break;
    
    case 'updateUnitAttribute':
        
        // Required params:
        if (!isset($params['attribute']) || !isset($params['value'])){
            _err();
        }
                                
        $unit->set_attribute($params['attribute'], $params['value'], $qualID, $studentID);        
        
        echo " $($('#{$params['el']}').parents('td')[0]).effect('highlight', {color: '#ccff66'}, 3000); ";
        exit;
        
    break;
    
}


function _err(){
    
    global $params;
    
    if (isset($params['el']))
    {
        echo " $($('#{$params['el']}').parents('td')[0]).effect('highlight', {color: '#FF0000'}, 3000); ";
    }
    
    exit;
    
}