<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

//header("Content-Type: text/xml");
require_once('../../../../../config.php');

global $COURSE, $CFG, $PAGE;
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$context = context_course::instance($COURSE->id);
require_login();
$PAGE->set_context($context);

$activityID = required_param('aID', PARAM_INT);
$qualID = required_param('qID', PARAM_INT);
$grid = optional_param('g', 's', PARAM_TEXT);
$courseID = optional_param('cID', -1, PARAM_INT);
$showTable = optional_param('showtable', false, PARAM_BOOL);
$advancedMode = false;
if($grid == 'a' || $grid == 'ae')
{
    $advancedMode = true;
}
$editing = false;
if($grid == 'se' || $grid == 'ae')
{
    $editing = true;
}
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECQualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECUnit.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECCriteria.class.php');
$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELALL;
$qual = Qualification::get_qualification_class_id($qualID, $loadParams);
if($qual)
{
    //this comes back as an object
    //there is a multidimentional array of rows and columns
    //above, (e.g. Flat is late to denote if we are showing late)
    $data = $qual->get_qual_activity_grid_data($courseID, $activityID, $advancedMode, $editing);
    $output = array(
		"iTotalRecords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);
	echo json_encode( $output );
    
    if($showTable)
    {
        $out = '';
        $out .= '<table>';
        foreach($data AS $row)
        {
            $out .= '<tr>';
            foreach($row AS $cell)
            {
                $out .= '<td>'.$cell.'</td>';
            }
            $out .= '</tr>';
        }
        $out .= '</table>';
        echo $out;
    }
}
else
{
    echo "No Unit Found";
}
//Call a function get student grid data
