<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function process_assignments_cron()
{
    global $DB;
    $frequency = get_config('bcgt', 'assignmentfrequencycheck');
    mtrace("Frequency to check = $frequency");
    $hour = date('H');
    $lastCronDB = $DB->get_record_sql('SELECT lastcron FROM {block} WHERE name = ?', array('bcgt'));
    $lastCron = $lastCronDB->lastcron;
    $lastCronHour = date('H', $lastCron);
    $today = date("Y m d G"); 
    $runCheck = false;
    if($frequency == 'daily')
    {
        $time = get_config('bcgt', 'assignmenttimecheck');
        mtrace("Time to check = $time AND hour now = $hour");
        //if the hour is the same as when we want to do the check
        //and the last time that we ran it wasnt in the same hour:
        if($hour == $time && $hour != $lastCronHour)
        {
            $runCheck = true;
            //then lets do the check for the last 24 hours. 
            //hour of yesterday against hour of today. 
            //Any assignments that exist in this period of time that is due
            $starttime = time() - (60 * 60 * 25);
            //so 25 hours before now and now. 
        }
    }
    elseif($frequency == 'hourly')
    {
        if($hour != $lastCronHour)
        {
            $runCheck = true;
            //then we are in a new hour
            //so run the check:
            //do check for last hour. 
            //anything this is due in the hour of $hour - 1?
            $starttime = time() - 60 * 70;
            //starts at -1 hour and ten mins to this hour.
        }
    }
    else
    {
        mtrace("Frequency not Daily or Hourly so cant run cron");
    }
    if($runCheck)
    { 
        //need to check for late and IN
        bcgt_cron_process_assignments_frequency_inlate($starttime, time());
        
        //this will check for WNS
        bcgt_cron_process_assignments_frequency_wns($starttime, time());
    }
    else
    {
        mtrace("Its not time to run the check: (May have been run in the last hour?)");
    }
    
}

function bcgt_cron_process_assignments_frequency_wns($startTime, $finishTime)
{
    mtrace("BCGT: Checking for any work not submitted assignment values");
    mtrace("BCGT: Check between: ".date("d m y - H:i", $startTime)." and ".date("d m y - H:i", $finishTime));
    
    //get the mods we are linking to:
    $modules = get_mod_linking();
    foreach($modules AS $mod)
    {
        if($mod->checkforautotracking)
        {
            bcgt_process_mod_wns($mod->modname, $mod->modtablename, $mod->modtableduedatefname, $mod->modtablecoursefname, 
            $mod->modsubmissiontable, $mod->submissionuserfname, $mod->submissionmodidfname, $startTime, $finishTime);
        }
    }
    mtrace("BCGT: Finished checking for any work not submitted assignment values");
}

function bcgt_cron_process_assignments_frequency_inlate($startTime, $finishTime)
{
    mtrace("BCGT: Checking for any work submitted (or Late) assignment values");
    mtrace("BCGT: Check between: ".date("d m y - H:i", $startTime)." and ".date("d m y - H:i", $finishTime));

    //get the mods we are linking to:
    $modules = get_mod_linking();
    foreach($modules AS $mod)
    {
        if($mod->checkforautotracking)
        {
            bcgt_process_mod_inlate($mod->modname, $mod->modsubmissiontable, $mod->submissiondatefname, 
                    $mod->submissionuserfname, $mod->modtablename, $mod->submissionmodidfname,
                    $mod->modtableduedatefname, $mod->modtablecoursefname, 
            $startTime, $finishTime);
        }
    }
    mtrace("BCGT: Finished checking for any work submitted (or Late) assignment values");
}

function bcgt_find_deadline_submission_mods($tableName, $dbField, $startCheck, $finishTime)
{
    global $DB;
    $sql = "SELECT * FROM {".$tableName."} WHERE $dbField >= ? AND $dbField <= ?";
    $params = array($startCheck, $finishTime);
    return $DB->get_records_sql($sql, $params);
}

function bcgt_process_mod_wns($modName, $modTable, $modDueField, $courseField, 
        $submissionTable, $submissionUserField, $dbModIDField, $startTime, $finishTime)
{
    global $DB;
    mtrace("BCGT: Finding all $modName mods with deadlines in the alloted time");
    $assignments = bcgt_find_deadline_submission_mods($modTable, $modDueField, $startTime, $finishTime);
    if($assignments)
    {
        mtrace("Found ".count($assignments)." that have been due in the alloted time");
        foreach($assignments AS $assignment)
        {
            $id = $assignment->id;
            $courseID = $assignment->$courseField;
            $course = $DB->get_record_sql("SELECT * FROM {course} course WHERE id = ?", array($courseID));
            if(!$course)
            {
                mtrace("No Course found for id: $courseID");
                continue;
            }
            //we have the id,
            //we have the courseid
            // 
            //we need to get the course module id:
            $courseModule = get_coursemodule_from_instance($modName, $id, $courseID);
            if(!$courseModule)
            {
                mtrace("No $courseModule found for courseid: $courseID type = $modName AND instance = $id");
                continue;
            }
            //is the mod attached to any quals, units, critera?
            //if yes:
            $project = new Project();
            if($project->is_course_mod_attached_qual($courseModule->id))
            {
                mtrace("This $modName is attached to a qualification");
                //now we need to find all of the students on the course
                //in the grouping (groups -> students)
                $users = $project->get_users_on_course_mod($courseModule);
                if($users)
                {
                    foreach($users AS $user)
                    {
                        //have any of them not submitted?
                        //have any of these got a blank section in the grade tracker?
                        $submission = bcgt_find_submission_mod_user($submissionTable, 
                                $submissionUserField, $user->id, $dbModIDField, $id);
                        if(!$submission)
                        {
                            //then we need to update the users qualification.
                            mtrace("BCGT: Updating $user->id($user->username) Course: ($course->shortname) $modName: $assignment->name to WNS");
                            $project->update_users_qual_cron($user->id, $courseModule->id, 'WNS');
                        }
                    }
                }
            }
        }
    }
    else
    {
        mtrace("No Activities found");
    }
}

function bcgt_process_mod_inlate($modName, $submissionTable, $submissionModifiedField, 
        $submissionUserField, $modTable, $submissionModIDField, $modDueField, 
        $modCourseField, $startTime, $finishTime)
{
    global $DB;
    mtrace("BCGT: Finding all $modName mod submissions in the alloted time");
    $assignmentSubmissions = bcgt_find_deadline_submission_mods($submissionTable, $submissionModifiedField, $startTime, $finishTime);
    if($assignmentSubmissions)
    {
        mtrace("Found ".count($assignmentSubmissions)." that have been submitted in the alloted time");
        foreach($assignmentSubmissions AS $submission)
        {
            //this is each student
            //we need to find the mod that this belongs to
            $mod = bcgt_get_mod($modTable, $submission->$submissionModIDField);
            if(!$mod)
            {
                continue;
            }
            //need to find the course module id that corresponds to this
            $courseModule = get_coursemodule_from_instance($modName, $mod->id, $mod->$modCourseField);
            if(!$courseModule)
            {
                mtrace("No $courseModule found for courseid: $mod->$modCourseField type = $modName AND instance = $mod->id");
                continue;
            }
            
            $user = $DB->get_record_sql("SELECT * FROM {user} WHERE id = ?", array($submission->$submissionUserField));
            if(!$user)
            {
                mtrace("BCGT: NO User found: {$submission->$submissionUserField}");
                continue;
            }
            
            $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($mod->$modCourseField));
            if(!$course)
            {
                mtrace("BCGT: No course found: $mod->$modCourseField");
                continue;
            }
            //is the mod attached to any quals, units, critera?
            //if yes:
            $project = new Project();
            if($project->is_course_mod_attached_qual($courseModule->id))
            {
                mtrace("This $modName is attached to a qualification");

                //NEED to see if it is LATE or IN. 
                $dueDate = $mod->$modDueField;
                $action = 'N/A';
                $dateSubmitted = $submission->$submissionModifiedField;
                if($dueDate >= $dateSubmitted)
                {
                    //we are IN
                    $action = 'WS';
                }
                else 
                {
                    //we are late
                    $action = 'L';
                    
                }
                mtrace("BCGT: Updating $user->id($user->username) Course: ($course->shortname) $modName: $mod->name to $action");
                            $project->update_users_qual_cron($user->id, $courseModule->id, $action); 
            }
            
        }
    }
    else
    {
        mtrace("Found no submissions");
    }
}

function bcgt_get_mod($modTable, $modID)
{
    global $DB;
    $sql = "SELECT * FROM {".$modTable."} WHERE id = ?";
    return $DB->get_record_sql($sql, array($modID));
}

function bcgt_find_submission_mod_user($tableName, $dbUserField, $userID, $dbModIDField, $modID)
{
    global $DB;
    $sql = "SELECT * FROM {".$tableName."} WHERE $dbUserField = ? AND $dbModIDField = ?";
    return $DB->get_record_sql($sql, array($userID, $modID));
}

?>
