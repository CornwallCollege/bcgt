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

global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$context = context_course::instance($COURSE->id);
if (!isset($_SESSION['pp_user'])){
    require_login();
}
$PAGE->set_context($context);

(int)$studentID = optional_param('sID', -1, PARAM_INT);
(int)$qualID = optional_param('qID', -1, PARAM_INT);
(int)$criteriaID = optional_param('cID', -1, PARAM_INT);
(int)$unitID = optional_param('uID', -1, PARAM_INT);
(string)$type = optional_param('type', 'value', PARAM_TEXT); //e.g. comments
if($type == 'studentsunits')
{
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
    $loadParams->loadAward = true;
    $loadParams->loadAddUnits = false;
    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
    if($qualification)
    {
        //TODO check if the loadlevel is set to not load the criteria
        $qualification->load_student_information($studentID, $loadParams);
        $output = $qualification->get_students_units_summary();
        echo $output;
    }
    else
    {
        echo "No Qualification Found";
    }
    //then get the breakdown of the units and what the student has got for them
}
else
{
    if($unitID != -1)
    {
        if($type == 'comm')
        {
            //get the unit commment

        }
        elseif($type == 'crit')
        {
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELCRITERIA;
            $unit = Unit::get_unit_class_id($unitID, $loadParams);
            if($unit)
            {
                //this is just the details of all of the criteria on the unit
                echo $unit->build_unit_details_table();
            }
        }
    }
    elseif($criteriaID != -1)
    {
        if($type == 'comment')
        {
            //get the criteria comment
            $output = Criteria::build_comments_tooltip($criteriaID, $qualID, $studentID);
            if($output)
            {
                echo $output;
            }
        }
        else
        {
            //get the criteria value
            $output = Criteria::build_criteria_tooltip($criteriaID, $qualID, $studentID);
            if($output)
            {
                echo $output;
            }    
            
        }
    }
}


//Call a function get student grid data
