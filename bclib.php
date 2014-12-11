<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author cwarwicker@bedford.ac.uk
 */

/*
 * General library file for Logging
 */

// Grade Tracker Module
define("LOG_MODULE_GRADETRACKER", "GRADETRACKER");

// Grade Tracker Elements
define("LOG_ELEMENT_GRADETRACKER_QUALIFICATION", "QUALIFICATION");
define("LOG_ELEMENT_GRADETRACKER_UNIT", "UNIT"); 
define("LOG_ELEMENT_GRADETRACKER_CRITERIA", "CRITERIA");
define("LOG_ELEMENT_GRADETRACKER_SETTINGS", "SETTINGS"); # This is for admin stuff, like setting new values, lvls, etc..
define("LOG_ELEMENT_GRADETRACKER_TASK", "TASK");
define("LOG_ELEMENT_GRADETRACKER_RANGE", "RANGE");
#define("LOG_ELEMENT_GRADETRACKER_", "");

// Grade Tracker Values
// Qual related
define("LOG_VALUE_GRADETRACKER_INSERTED_QUAL", "inserted qualification");
define("LOG_VALUE_GRADETRACKER_UPDATED_QUAL", "updated qualification");
define("LOG_VALUE_GRADETRACKER_DELETED_QUAL", "deleted qualification");
define("LOG_VALUE_GRADETRACKER_ADDED_UNIT_TO_QUAL", "added unit onto qualification");
define("LOG_VALUE_GRADETRACKER_REMOVED_UNIT_FROM_QUAL", "removed unit from qualification");
define("LOG_VALUE_GRADETRACKER_ADDED_QUAL_TO_COURSE", "added qualification to course");
define("LOG_VALUE_GRADETRACKER_UPDATED_QUAL_COMMENTS", "updated student's qualification comments");
define("LOG_VALUE_GRADETRACKER_UPDATED_QUAL_AWARD", "updated student's qualification award");
define("LOG_VALUE_GRADETRACKER_DELETED_QUAL_AWARD", "deleted student's qualification award");
define("LOG_VALUE_GRADETRACKER_SAVED_GRID", "saved student's grid");
define("LOG_VALUE_GRADETRACKER_UPDATED_QUAL_ATTRIBUTE", "updated student's qualification attribute");

define("LOG_VALUE_GRADETRACKER_ADDED_USER_TO_QUAL", "added user onto qualification");
define("LOG_VALUE_GRADETRACKER_REMOVED_USER_FROM_QUAL", "removed user from qualification");
define("LOG_VALUE_GRADETRACKER_ADDED_USER_TO_ALL_UNITS", "added user onto all qualification units");
define("LOG_VALUE_GRADETRACKER_REMOVED_USER_FROM_ALL_UNITS", "removed user from all qualification units");



// Criteria related
define("LOG_VALUE_GRADETRACKER_INSERTED_CRIT", "inserted criteria");
define("LOG_VALUE_GRADETRACKER_UPDATED_CRIT", "updated criteria");
define("LOG_VALUE_GRADETRACKER_DELETED_CRIT", "deleted criteria");
define("LOG_VALUE_GRADETRACKER_UPDATED_CRIT_AWARD", "updated student's criteria award");
define("LOG_VALUE_GRADETRACKER_UPDATED_CRIT_AWARD_AUTO", "automatically updated student's criteria award");
define("LOG_VALUE_GRADETRACKER_UPDATED_CRIT_COMMENT", "updated student's criteria comment");
define("LOG_VALUE_GRADETRACKER_DELETED_CRIT_COMMENT", "deleted student's criteria comment");
define("LOG_VALUE_GRADETRACKER_UPDATED_USER_DEFINED_VALUE", "updated student's user defined value");
define("LOG_VALUE_GRADETRACKER_UPDATED_CRIT_USER_TARGET_DATE", "updated student's target date");
define("LOG_VALUE_GRADETRACKER_UPDATED_CRIT_USER_AWARD_DATE", "updated student's award date");
define("LOG_VALUE_GRADETRACKER_UPDATED_OUTCOME_OBSERVATION", "updated student's outcome observation");
define("LOG_VALUE_GRADETRACKER_UPDATED_SIGNOFF_RANGE_OBSERVATION", "updated student's signoff range observation");
define("LOG_VALUE_GRADETRACKER_UPDATED_CRIT_ATTRIBUTE", "updated student's criteria attribute");

// Unit related
define("LOG_VALUE_GRADETRACKER_INSERTED_UNIT", "inserted unit");
define("LOG_VALUE_GRADETRACKER_UPDATED_UNIT", "updated unit");
define("LOG_VALUE_GRADETRACKER_DELETED_UNIT", "deleted unit");
define("LOG_VALUE_GRADETRACKER_ADDED_STUDENT_TO_UNIT", "added student to unit");
define("LOG_VALUE_GRADETRACKER_UPDATED_UNIT_AWARD", "updated student's unit award");
define("LOG_VALUE_GRADETRACKER_UPDATED_UNIT_COMMENT", "updated student's unit comment");
define("LOG_VALUE_GRADETRACKER_DELETED_UNIT_COMMENT", "deleted student's unit comment");
define("LOG_VALUE_GRADETRACKER_UPDATED_UNIT_ATTRIBUTE", "updated student's unit attribute");

// Task related
define("LOG_VALUE_GRADETRACKER_INSERTED_TASK", "inserted task");
define("LOG_VALUE_GRADETRACKER_INSERTED_TASK_AWARD", "inserted student's criteria task award");
define("LOG_VALUE_GRADETRACKER_UPDATED_TASK_AWARD", "updated student's criteria task award");
define("LOG_VALUE_GRADETRACKER_UPDATED_TASK_COMMENT", "updated student's criteria task comment");
define("LOG_VALUE_GRADETRACKER_DELETED_TASK_COMMENT", "deleted student's criteria task comment");

// Range related
define("LOG_VALUE_GRADETRACKER_UPDATED_CRITERIA_RANGE_VALUE", "updated student's criteria/range value");
define("LOG_VALUE_GRADETRACKER_UPDATED_RANGE_AWARD_DATE", "updated student's range award date");
define("LOG_VALUE_GRADETRACKER_UPDATED_RANGE_AWARD", "updated student's range award");
define("LOG_VALUE_GRADETRACKER_UPDATED_RANGE_AWARD_AUTO", "automatically updated student's range award");
define("LOG_VALUE_GRADETRACKER_UPDATED_RANGE_TARGET_DATE", "updated student's range target date");
define("LOG_VALUE_GRADETRACKER_DELETED_SIGNOFF_SHEET", "deleted signoff sheet"); # Not technically range but who cares
define("LOG_VALUE_GRADETRACKER_DELETED_SIGNOFF_SHEET_RANGE", "deleted signoff sheet range");

/*
 * Example useage:
 * 
 * Adding a new unit onto the system
 *  logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_UNIT, "created a unit: {$unitName}", null, $qualID, 
 *            null, $unitID);
 * 
 * Updating a student's criteria value to Achieved (A)
 *  logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, "updated award to: {$newAward}", $studentID,
 *            $qualID, null, $criteriaID);
 * 
 */



/**
 * logging actions for gradetracker, etc...
 * 
 * @global object $USER
 * @param string $mod The module/block they are using. E.g. "plp", "tracker", "bksb", etc... This should use a standard set of values.
 * @param string $element The sub element of the module. This is more loose. E.g. could be "tutorial" for plp tutorials, "course reports" for plp course reports, "grid" for tracker, etc...
 * @param string $log The actual log text. E.g. "added a course report", "updated value of student's criteria", etc...
 * @param int $student the student's id
 * @param int $qual The qual id if applicable, if not set to null
 * @param int $unit The unit id, if not set to null
 * @param int $course The course id if applicable, if not set to null
 * @param int $value A generic value, this could be the course report ID, the tutorial ID, the criteria ID, etc... depending on what the module and element are.
 */
function logAction($mod, $element, $log, $student, $qual, $unit, $course, $value, $value2=null, $value3=null)
{
    global $USER, $DB;
    
    $userID = (!in_array($log, array(LOG_VALUE_GRADETRACKER_UPDATED_RANGE_AWARD_AUTO, LOG_VALUE_GRADETRACKER_UPDATED_CRIT_AWARD_AUTO))) ? $USER->id : -1;
    
    $obj = new stdClass();
    $obj->userid = $userID;
    $obj->module = $mod;
    $obj->element = $element;
    $obj->log = $log;
    $obj->studentid = $student;
    $obj->bcgtqualificationid = $qual;
    $obj->bcgtunitid = $unit;
    $obj->courseid = $course;
    $obj->valueid = $value;
    $obj->valueid2 = $value2;
    $obj->valueid3 = $value3;
    $obj->time = time();
    
    // Newlines are being annoying and i can't get them to work, so just stripping them out od display for now
    if(!is_null($obj->valueid2)){
        $obj->valueid2 = preg_replace("/\\n/", " ", $obj->valueid2);
    }
    if(!is_null($obj->valueid3)){
        $obj->valueid3 = preg_replace("/\\n/", " ", $obj->valueid3);
    }
    
    $DB->insert_record("block_bcgt_logs", $obj);
    
}





/* Other Helpful Functions */


function printOut($txt)
{
    global $CFG;
    $handle = fopen($CFG->dirroot . "/test.tst", "a+");
    fwrite($handle, $txt . "\n");
    fclose($handle);
        
}

function printError($txt)
{
    global $CFG;
    $handle = fopen($CFG->dirroot . "/error.log", "a+");
    fwrite($handle, $txt . "\n");
    fclose($handle);
}

function pn($obj)
{
    printOut(pnr($obj));
}

function pnr($obj)
{
    return print_r($obj, true);
}

function html($str, $nl2br=false)
{
    if($nl2br) return nl2br(htmlspecialchars($str, ENT_QUOTES));
    return htmlspecialchars($str, ENT_QUOTES);
}
