<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../../config.php');

global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$cID = optional_param('cID', -1, PARAM_INT);
$context = context_course::instance($COURSE->id);
require_login();
$PAGE->set_context($context);
set_time_limit(0);
$studentID = optional_param('sID', -1, PARAM_INT);
$qualID = optional_param('qID', -1, PARAM_INT);
$assID = optional_param('assID', -1, PARAM_INT);
$groupID = optional_param('grID', -1, PARAM_INT);
$type = optional_param('type', '', PARAM_TEXT);
$subtype = optional_param('subtype', '', PARAM_TEXT);
$courseID = optional_param('courseid', -1, PARAM_INT);
$fam = optional_param('fam', -1, PARAM_TEXT);
$showHTML = optional_param('html', false, PARAM_BOOL);
$typeID = optional_param('typeID', -1, PARAM_INT);
$showCoefficient = optional_param('score', false, PARAM_BOOL);
if($qualID != -1)
{
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELALL;
    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
}
$display = '';
switch($type)
{
    case "student":
        switch($subtype)
        {
            case "all":
                $temp = $qualification->get_user_all_alps_temp($studentID);
                $display = "<span class='alpstemp alpstemp'>".$temp."</span>";
                break;
            case "ceta":
                //need to get the ucas target points
                //need to get the latest ceta points. 
                $temp = $qualification->get_user_ceta_alps_temp($studentID, $showCoefficient);
                $display = bcgt_display_alps_temp($temp, $showCoefficient);
                break;
            case "fa":
                $temp = $qualification->get_user_fa_alps_temp($studentID, $showCoefficient);
                $display = bcgt_display_alps_temp($temp, $showCoefficient);
                break;
            case "projectg":
                $temp = $qualification->get_user_fa_ind_alps_temp($studentID, $assID, $showCoefficient);
                $display = bcgt_display_alps_temp($temp, $showCoefficient);
                break;
            case "projectc":
                $temp = $qualification->get_user_ceta_ind_alps_temp($studentID, $assID, $showCoefficient);
                $display = bcgt_display_alps_temp($temp, $showCoefficient);
                break;
            case "gbook":
                $temp = $qualification->get_user_gbook_alps_temp($studentID, $assID, $courseID, $showCoefficient);
                $display = bcgt_display_alps_temp($temp, $showCoefficient);
                break;
        }
        break;
    case "class":
        switch($subtype)
        {
            case "projectg":
                $temp = $qualification->get_class_fa_ind_alps_temp($assID, $groupID, $showCoefficient);
                $display = bcgt_display_alps_temp($temp, $showCoefficient);
                break;
            case "projectc":
                $temp = $qualification->get_class_ceta_ind_alps_temp($assID, $groupID, $showCoefficient);
                $display = bcgt_display_alps_temp($temp, $showCoefficient);
                break;
            case "all":
                $temp = $qualification->get_class_alps_temp($groupID, $showCoefficient);
                $display = bcgt_display_alps_temp($temp, $showCoefficient, ''.get_string('overall', 'block_bcgt').': ');
                break;
            case "gbook":
                $temp = $qualification->get_class_gbook_alps_temp($assID, $courseID, $groupID, $showCoefficient);
                $display = bcgt_display_alps_temp($temp, $showCoefficient);
                break;
        }
        break;
    case "family":
        switch($subtype)
        {
            case "all":
                $temp = Qualification::get_overall_alps_temp($fam);
                $display = "<span class='alpstemp alpstemp".$temp."'>".$temp."</span>";
                break;
            case "fag":
                $temp = Qualification::get_overall_alps_temp_fag($fam, $assID);
                $display = "<span class='alpstemp alpstemp".$temp."'>".$temp."</span>";
                break;
            case "fac":
                $temp = Qualification::get_overall_alps_temp_fac($fam, $assID);
                $display = "<span class='alpstemp alpstemp".$temp."'>".$temp."</span>";
                break;
            default:
                break;
        }
        break;
    case "famType":
        switch($subtype)
        {
            case "all":
                $temp = Qualification::get_overall_alps_temp($fam, $typeID);
                $display = "<span class='alpstemp alpstemp".$temp."'>".$temp."</span>";
                break;
            case "fag":
                $temp = Qualification::get_overall_alps_temp_fag($fam, $assID, $typeID);
                $display = "<span class='alpstemp alpstemp".$temp."'>".$temp."</span>";
                break;
            case "fac":
                $temp = Qualification::get_overall_alps_temp_fac($fam, $assID, $typeID);
                $display = "<span class='alpstemp alpstemp".$temp."'>".$temp."</span>";
                break;
            default:
                break;
        }
        break;
    default:
}

$output = array(
    "qualid"=>$qualID,
    "userid"=>$studentID,
    "type"=>$type,
    "assid"=>$assID,
    "subtype"=>$subtype,
    "display"=>$display,
    "courseid"=>$courseID,
    "fam"=>$fam,
    "typeid"=>$typeID
);
if($showHTML)
{
    echo $display;
}
else
{
    echo json_encode( $output );
}
?>

