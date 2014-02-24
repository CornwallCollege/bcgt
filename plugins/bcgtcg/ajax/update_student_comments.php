<?php
header("Content-Type: text/html; charset=utf-8");
require_once '../../../../../config.php';
require_once('../../../lib.php');
require_once '../lib.php';
require_cg();
require_login();


/**
 * Parameters:
 * $action - e.g. 'update_comments', 'update_task', etc... to define what is happening
 * $params - holds the given parameters for that specific action in an array
 */
$action = optional_param('action', null, PARAM_TEXT);
$params = optional_param_array('params', null, PARAM_TEXT);

if (!isset($params['grid'])) $params['grid'] = 'student';

// If no action or params are set, exit the script
if(is_null($action)) exit;

// Function to check if all required parameters are set
// Then convert array to object, for ease of use
function _check($req, &$params){
    if( count($req) > count($params) ) _error('count');
    foreach($req as $key){
        if(!isset($params[$key]) || empty($params[$key])) _error('key: ' . $key);
    }
    $params = (object)$params;
}

// Error function, to either just exit or alert a msg first as well
function _error($msg = null){
    if(!is_null($msg)) echo "alert('Error: {$msg}');";
    exit;
}
        

// Check if the qual, crit, etc... are all valid for a student
function _valid($type, $params, $stage=3){
    
    global $qualification, $unit, $criteria, $sessionQuals;
    
    if ($type == 'student' || $type == 'stud'){
        $sessionQuals = isset($_SESSION['session_stu_quals'])? unserialize(urldecode($_SESSION['session_stu_quals'])) : array();
    } elseif ($type == 'unit') {
        
        $studentUnit = false;
        $sessionUnits = isset($_SESSION['session_unit'])? unserialize(urldecode($_SESSION['session_unit'])) : array();
        if(array_key_exists($params->unitID, $sessionUnits))
        {
            $unitObject = $sessionUnits[$params->unitID];
            $unit = $unitObject->unit;
            $qualArray = $unitObject->qualArray;
            if(array_key_exists($params->qualID, $qualArray))
            {
                $studentArray = $qualArray[$params->qualID];
                if(array_key_exists($params->studentID, $studentArray))
                {
                    $studentObject = $studentArray[$params->studentID];
                    $studentUnit = $studentObject->unit;
                }
            } 
        }
        
        if (!$studentUnit) exit;
                
    }
    
    if($stage >= 1)
    {
                
        if ($type == 'student' || $type == 'stud'){
            
            // Load qual for student
            if (isset($sessionQuals[$params->studentID][$params->qualID])){
                $qualification = $sessionQuals[$params->studentID][$params->qualID]->qualification;
            } else {
                $o = new stdClass();
                $o->loadLevel = Qualification::LOADLEVELUNITS;
                $qualification = Qualification::get_qualification_class_id($params->qualID, $o);
                if(!$qualification) exit;
                $qualification->load_student_information($params->studentID, $o);
            }
        
            if(!$qualification) exit;
        
        }
                
    }

    if($stage >= 2)
    {
        // Load unit
        if ($type == 'student' || $type == 'stud'){
            $unit = $qualification->get_single_unit($params->unitID);
            if(!$unit) exit;
        } elseif ($type == 'unit') {
            $unit = $studentUnit;
        }
        
    }

    if($stage >= 3)
    {
        // Load Criteria
        $criteria = $unit->get_single_criteria($params->criteriaID);
        if(!$criteria) exit;
    }
                
    
}



switch($action)
{
    
    case 'criteriaComment':
        
        // Required params:
        $req = array("qualID", "criteriaID", "studentID", "comment", "element");
        
        // Optional params: "singleUnitView", "unitView"
        if (empty($params['comment'])){
            $params['comment'] = ' ';
        }
        
        _check($req, $params);
        _valid($params->grid, $params);
                        
        $params->comment = trim($params->comment);
        
        // Update the comment, but do not update the date in the user_criteria record - as is the default
        if(empty($params->comment))
        {
            $criteria->delete_students_comments($params->qualID);
        }
        else
        {
            $criteria->add_comments( urldecode($params->comment) );
            $criteria->save_students_comments($params->qualID);
        }
        
        // Update session
        if ($params->grid == 'student'){
            update_session_qual($params->studentID, $params->qualID, $qualification);
        } elseif ($params->grid == 'unit'){
            update_session_unit($params->studentID, $params->unitID, $unit, $params->qualID);
        }
        
        // Remove single quotes
        $params->comment = str_replace("'", "", $params->comment);
               
                        
        // If we're in the single student/unit view, we don't want to update a cell as we don't have one
        if(!isset($params->singleUnitView)){
            echo " updateCommentCell('{$params->element}', '{$params->comment}'); ";
        }
        
    break;
    
    case 'unitComment':
        
        // Required params:
        $req = array("qualID", "unitID", "studentID", "comment", "element");        
        if (empty($params['comment'])){
            $params['comment'] = ' ';
        }
        
        _check($req, $params);
        _valid($params->grid, $params, 2);
                
        $params->comment = trim($params->comment);
                                
        $unit->set_comments( urldecode($params->comment) );
        $unit->update_comments($params->qualID, urldecode($params->comment));
                
        // Update session
        if ($params->grid == 'student'){
            update_session_qual($params->studentID, $params->qualID, $qualification, $unit);
        } elseif($params->grid == 'unit'){
            update_session_unit($params->studentID, $params->unitID, $unit, $params->qualID);
        }
        
        // Replace actual newlines with \n so JS can parse it properly
        $params->comment = str_replace("'", '', $params->comment);
                
        echo " updateUnitCommentCell('{$params->element}', '{$params->comment}'); ";
        
    break;

    case 'getQualComment':
        $req = array("qualID", "studentID", "mode");
        _check($req, $params);
        _valid($params->grid, $params, 1);
        
        $output = "";
        $comment = $qualification->get_comments();
        if ($comment == "") $comment = "N/A";
        
        if ($params->mode == 'ae' || $params->mode == 'se')
        {
            $output .= "<br><fieldset><legend><h2>Qualification Comments</h2></legend><br><textarea>".nl2br( htmlentities($comment, ENT_QUOTES) )."</textarea><br><input type='button' qualid='{$qualification->get_id()}' studentid='{$params->studentID}' id='saveQualComment' value='Save' /></fieldset>";
        }
        else
        {
            $output .= "<br><fieldset><legend><h2>Qualification Comments</h2></legend><br>".nl2br( htmlentities($comment, ENT_QUOTES) )."</fieldset>";
        }
        
        echo $output;
        
    break;
    
    case 'qualComment':
                
        // Required params:
        $req = array("qualID", "studentID", "comment", "element");
        _check($req, $params);
        _valid($params->grid, $params, 1);
        
        $params->comment = trim($params->comment);
        
        if(empty($params->comment)) $params->comment = null;
        
        // Update comments
        $qualification->update_comments( urldecode($params->comment) );
        
        // Update session
        update_session_qual($params->studentID, $params->qualID, $qualification);
        
        exit;
        
    break;

    case 'taskComment':
        
        // Required params:
        $req = array("qualID", "studentID", "unitID", "criteriaID", "taskID", "comment", "element");
        _check($req, $params);
        _valid($params);
                
        // Load task and make sure student taking it
        $task = new Task($params->taskID);
                
        if(!$task->load_student($params->studentID, $params->criteriaID, $params->qualID))
        {
            echo "alert('Could not find user record...perhaps they do not have an award for this task yet?');"; 
            exit;
        }
        
        // Update comments
        $task->update_comments($params->comment);
        
        // Build up a JSON object of details to replicate cmt.edit/cmt.create onclick more easily
        
        $user = get_record_select('user', "`id` = {$params->studentID}");
        
        echo <<<JS
            var details = {
                div: "popUpDiv",
                qualID: {$params->qualID},
                studentID: {$params->studentID},
                username: "{$user->username}",
                fullname: "{$user->firstname} {$user->lastname}",
                unitID: {$params->unitID},
                unitName: "{$unit->get_name()}",
                criteriaID: {$params->criteriaID},
                criteriaName: "{$criteria->get_name()}",
                taskID: {$params->taskID},
                taskName: "{$task->get('name')}",
                body: "this.nextSibling.innerHTML"
            };
JS;
                
        $empty = ($params->comment == " ") ? "true" : "false";
        $params->comment = str_replace("\n", '\n', $params->comment);
        
        
        echo "updateTaskCommentCell('cmtCell_U{$params->unitID}_C{$params->criteriaID}_S{$params->studentID}_T{$params->taskID}', details, '".html($params->comment)."', $empty);";
        echo "cmt.cancel();";

        
    break;
    
}

