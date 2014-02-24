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
require_once('../../../config.php');

global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$context = context_course::instance($COURSE->id);
require_login();
$PAGE->set_context($context);

$courseID = optional_param('cID', -1, PARAM_INT);
$unitID = optional_param('uID', -1, PARAM_INT);
$cmID = optional_param('cmID', -1, PARAM_INT);
$userID = optional_param('sID', -1, PARAM_INT);
$qualID = optional_param('qID', -1, PARAM_INT);
$criteriaID = optional_param('criteriaID', -1, PARAM_INT);
$modType = optional_param('mod', '', PARAM_TEXT);
$groupingID = optional_param('grID', -1, PARAM_INT);

//get the ass details
$out = '<div id="bcgtdialogcontent">';
if($courseID != -1)
{
    $currentContext = context_course::instance($courseID);
}
else
{
    $currentContext = context_course::instance($COURSE->id);
}
$viewSubSummary = false;
if(has_capability('block/bcgt:viewclassgrids', $currentContext))
{
    $viewSubSummary = true;
}

//if a criteria id is set
$modLinking = load_bcgt_mod_linking();
if($criteriaID != -1)
{
    //go and get the mod information for this criteria
    $modDetails = bcgt_get_mod_details($criteriaID, $modType, $qualID, $courseID, $groupingID);
    if($modDetails)
    {
        if($modType == '')
        {
            //load the icons
            $modIcons = load_mod_icons($courseID, $qualID, $groupingID, $criteriaID);
        }
        if($viewSubSummary)
        {
            $out .= '<div class="modlinkingsubsummary">';
            $out .= '<span class="modlinkingssub modlinkingsubsumstu">';
            $out .= get_string('students', 'block_bcgt').' : ';
            $noStudents = bcgt_get_criteria_submission_students($criteriaID, $courseID, $qualID, 
                                $groupingID, $modType);
            if($noStudents)
            {
                $out .= $noStudents->count;
            }
            else
            {
                $out .= 'N/A';
            }
            $out .= '</span>';
            $out .= '<span class="modlinkingssub modlinkingsubsumatt">';
            $out .= get_string('attempted', 'block_bcgt').' : ';
            $noAttempted = bcgt_get_criteria_submission_attempted($criteriaID, $courseID, $qualID, 
                                $groupingID, $modType);
            if($noAttempted && $noStudents)
            {
                $out .= round(($noAttempted->count/$noStudents->count)*100, 1).'%';
            }
            else
            {
                $out .= 'N/A';
            }
            $out .= '</span>';
            $out .= '<span class="modlinkingssub modlinkingsubsumach">';
            $out .= get_string('achieved', 'block_bcgt').' : ';
            $noAchieved = bcgt_get_criteria_submission_achieved($criteriaID, $courseID, $qualID, 
                                $groupingID, $modType);
            if($noAchieved && $noStudents)
            {
                $out .= round(($noAchieved->count/$noStudents->count)*100,1).'%';
            }
            else
            {
                $out .= 'N/A';
            }
            $out .= '</span>';
            //if has the capibilities of editing the class grid then
            //they can see the summary
            //total number of students doing
            //total number of students attempted
            //total number of students achieved
            $out .= '</div>';
        }
        
        //do i want to sort by due date?
        foreach($modDetails AS $modDetail)
        {
            $dueDate = get_bcgt_mod_due_date($modDetail->id, $modDetail->instanceid, $modDetail->cmodule, $modLinking);
            $modDetail->dueDate = $dueDate;
            $retval = '<div class="modlink '.$modDetail->module.'">';
            $retval .= '<div class="modlinkheader '.$modDetail->module.'header">';
            if($modType == '' && array_key_exists($modDetail->module,$modIcons))
            {
                $icon = $modIcons[$modDetail->module];
                //show the icon. 
                $retval .= html_writer::empty_tag('img', array('src' => $icon,
                            'class' => 'bcgtmodcriticon activityicon', 'alt' => $modDetail->module));
            }
            $retval .= '<span class="modlinkheadername '.$modDetail->module.'name">';
            $retval .= $modDetail->name;
            $retval .= '</span>';
            if($modType == '')
            {
                $retval .= '<span class="modlinkheadermod '.$modDetail->module.'mod">';
                $retval .= '('.$modDetail->module.')';
                $retval .= '</span>';
            }
            $retval .= '<span class="modlinkheaderdate '.$modDetail->module.'date">';
            if($dueDate)
            {
                $retval .= date('d M Y : H:m', $dueDate); 
            }
            $retval .= '</span>';
            $retval .= '</div>'; 
            $retval .= '<div class="modlinktrackingsummary '.$modDetail->module.'trackingsummary">';
            $retval .= get_mod_unit_summary_table($modDetail->id);
            $retval .= '</div>';
            //output if its grouping and what grouping its on 
            //if the user is show output the users results. 
            $retval .= '<span class="bcgtmodlinkingaction">';
            if($viewSubSummary)
            {
                //if has capibilites of eduiting the class grid
                //then link to edit the assignment view
                $retval .= '<input type="submit" cmID="'.$modDetail->id.
                        '" course="'.$courseID.'" grID="'.$groupingID.
                        '" qID="'.$qualID.'" class="acttracking" name="asstrack" '.
                        'value="'.get_string('activitygrid','block_bcgt').'"/>';
            }
            $retval .= '<input type="submit" cmID="'.$modDetail->id.'" name="ass"'.
                    ' class="act" mod="'.$modDetail->module.'" value="'.
                    get_string('activity', 'block_bcgt').'"/>';
            $retval .= '</span>';
            
            $retval .= '</div>';
            
            $modDetail->out = $retval;
        }
    }
}

//now we sort them. 
//then we output them. 
require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ModSorter.class.php');
        $modSorter = new ModSorter();
		usort($modDetails, array($modSorter, "ComparisonDelegateByDueDateObj"));
foreach($modDetails AS $modDetail)
{
    $out .= $modDetail->out;
}
$out .= '</div>';
    //if a modType is set
        //then show just this type of mod that is on that criteria
            //if qualid is set, narrow down 
            //if groupingid is set, narrow down
            //if courseid is set, narrow down. 
            //if userid is set, then narrow down
                //show results
    //else show all mod types. 

$output = array(
    "unit" => $unitID,
    "cmid" => $cmID,
    "course" => $courseID,
    "retval" => $out
);
        
echo json_encode( $output );
?>
