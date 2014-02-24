<?php
require_once('../../../config.php');
global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$context = context_course::instance($COURSE->id);
require_login();
$PAGE->set_context($context);

$qualID = optional_param('qID', -1, PARAM_INT);
$groupingID = optional_param('grID', -1, PARAM_INT);
$edit = optional_param('edit', false, PARAM_BOOL);
$tab = optional_param('tab', 's', PARAM_TEXT);
$uFilter = optional_param('ufilter', 'all', PARAM_TEXT);
$tFilter = optional_param('tfilter', 'all', PARAM_TEXT);
$sort = optional_param('sort', '', PARAM_TEXT);
$type = optional_param('type', '', PARAM_TEXT);
$showHTML = optional_param('html', false, PARAM_BOOL);

$filter = array();
$filter['units'] = $uFilter;
$filter['target'] = $tFilter;
$sortArray = explode(",", $sort);
$courseID = optional_param('cID', -1, PARAM_INT);
$userID = $USER->id;
if($qualID != -1)
{
    $qualification = Qualification::get_qualification_class_id($qualID);
    $retval = '';
    if($qualification)
    {
        $retval = $qualification->get_simple_qual_report($userID, $tab, $edit, $courseID, $filter, $sortArray, -1, $type);
    }

$output = array(
		"qualid" => $qualID,
        "groupingid" => -1,
		"retval" => $retval,
        "type" => $type,
        "tab" => $tab,
	);
    if($showHTML)
    {
        echo $retval;    
    }
    else
    {
        echo json_encode( $output );
    }
//	echo json_encode( $output );
}
elseif($groupingID != -1)
{
    //so we need to do it by group:
    //get all of the quals:
    
    //then for each we could load up the 
    $qualifications = Qualification::get_all_quals_on_grouping($groupingID);
    if($qualifications)
    {
        $retval = '';
        foreach($qualifications AS $qualification)
        {
            $qualificationOBJ = Qualification::get_qualification_class_id($qualification->id);
            $retval .= $qualificationOBJ->get_simple_qual_report($userID, $tab, $edit, $courseID, $filter, $sortArray, $groupingID, $type);            
        }
        $output = array(
		"qualid" => -1,
        "groupingid" => $groupingID,
		"retval" => $retval,
        "type" => $type,
        "tab" => $tab,    
	);
	if($showHTML)
    {
        echo $retval;;    
    }
    else
    {
        echo json_encode( $output );
    }
    }
}


?>
