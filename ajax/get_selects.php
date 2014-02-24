<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../../config.php');
global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$context = context_course::instance($COURSE->id);
require_login();
$PAGE->set_context($context);

$qualID = optional_param('qID', -1, PARAM_INT);
$courseID = optional_param('cID', -1, PARAM_INT);
$select = required_param('sel', PARAM_TEXT);
$grid = optional_param('g', 'u', PARAM_TEXT);
$qualExcludes = array();
if($grid == 'u')
{
    $qualExcludes = array('ALevel');
}
switch($select)
{
    case "mcourse":
        $group = new Group();
        $groups = $group->get_my_groups($courseID, $qualExcludes);
        $groupsArray = array();
        if($groups)
        {
            foreach($groups AS $group)
            {
                $groupArray = array(
                    "id" => "$group->id",
                    "name" => $group->name,
                );
                $groupsArray[] = $groupArray;
            }
        }
        $output = array(
            "groups" => $groupsArray,
            "select" => $select,
        );
        break;
    case "mqual":
        $teacherRole = $DB->get_record_select('role', 'shortname = ?', array('teacher'));
        $units = bcgt_get_users_units($USER->id, $teacherRole->id, '', $qualID);
        $unitsArray = array();
        if($units)
        {
            foreach($units AS $unit)
            {
                $unitArray = array(
                    "id"=>$unit->id,
                    "uniqueid"=>$unit->uniqueid,
                    "name"=>$unit->name,
                );
                $unitsArray[] = $unitArray;
            }
        }
        $output = array(
            "units" => $unitsArray,
            "select" => $select,
        );
        break;
    case "acourse":
        $group = new Group();
        $groups = $group->get_all_possible_groups($courseID, $qualExcludes);
        $groupsArray = array();
        if($groups)
        {
            foreach($groups AS $group)
            {
                $groupArray = array(
                    "id" => "$group->id",
                    "name" => $group->name,
                    "shortname" => $group->shortname
                );
                $groupsArray[] = $groupArray;
            }
        }
        $output = array(
            "groups" => $groupsArray,
            "select" => $select,
        );
        break;
}

echo json_encode( $output );
?>
