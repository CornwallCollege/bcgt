<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

function xmldb_block_bcgt_install()
{
    global $DB, $CFG;
    $record = new stdClass();
    $record->trackinglevel = 'Level 1';
    $DB->insert_record('block_bcgt_level', $record);

    $record = new stdClass();
    $record->trackinglevel = 'Level 2';
    $DB->insert_record('block_bcgt_level', $record);

    $record = new stdClass();
    $record->trackinglevel = 'Level 3';
    $DB->insert_record('block_bcgt_level', $record);

    $record = new stdClass();
    $record->trackinglevel = 'Level 4';
    $DB->insert_record('block_bcgt_level', $record);

    $record = new stdClass();
    $record->trackinglevel = 'Level 5';
    $DB->insert_record('block_bcgt_level', $record);

    $record = new stdClass();
    $record->trackinglevel = 'Bespoke';
    $DB->insert_record('block_bcgt_level', $record);
    
    $stdObj = new stdClass();
    $stdObj->trackinglevel = 'Level 1 & 2';
    $DB->insert_record('block_bcgt_level', $stdObj);
    
//    $record = new stdClass();
//    $record->id = 1;
//    $record->family = 'Bespoke';
//    $record->classfolderlocation = '/blocks/bcgt/plugins/bcgtbespoke/classes';
//    $record->pluginname = 'bcgtbespoke';

    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_type_family} (id,family,classfolderlocation,pluginname) 
        VALUES (1,'Bespoke','/blocks/bcgt/plugins/bcgtbespoke/classes','bcgtbespoke')");
//    $DB->insert_record_raw('block_bcgt_type_family', $record, false, false, true);
    
//    $record = new stdClass();
//    $record->id = 1;
//    $record->type = 'Bespoke';
//    $record->bcgttypefamilyid = 1;
//    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true);

    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_type} (id,type,bcgttypefamilyid) 
        VALUES (1,'Bespoke',1)");
    
//    $record = new stdClass();
//    $record->id = 1;
//    $record->subtype = 'Bespoke';
//    $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype) 
        VALUES (1,'Bespoke')");
    
    $record = new stdClass();
    $record->bcgtfamilyid = 1;
    $record->bcgttypeid = 1;
    $DB->insert_record('block_bcgt_fam_parent_type', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 1;
    $record->bcgttypeid = 1;
    $record->bcgtsubtypeid = 1;
    $DB->insert_record('block_bcgt_target_qual', $record);
    
    $maxSortOrderRecord = $DB->get_record_sql('SELECT MAX(sortorder) AS sortorder FROM {role}', null);
    if(!$maxSortOrderRecord)
    {
        return false;
    }
    $maxSortOrder = $maxSortOrderRecord->sortorder;
    //if it doesnt already exist
    if(!($DB->record_exists('role', array('name'=>'Grade Tracker Tutor'))))
    {
        $maxSortOrder++;
        $record = new stdClass();
        $record->name = 'Grade Tracker Tutor';
        $record->shortname = 'gttutor';
        $record->description = 'A Personal Mentor/Tutor type role. Someone that isnt always a teacher of a student and therefore is linked directly to the student';
        $record->sortorder = $maxSortOrder;
        $DB->insert_record('role', $record); 
    }
    if(!($DB->record_exists('role', array('name'=>'Grade Tracker Manager'))))
    {
        $maxSortOrder++;
        //if it doesnt already exist
        $record = new stdClass();
        $record->name = 'Grade Tracker Manager';
        $record->shortname = 'gtmanager';
        $record->description = 'A Manager type role. Someone that is a manger of other staff and would like to see a summary of their trackers';
        $record->sortorder = $maxSortOrder;
        $DB->insert_record('role', $record);
    }
    
    if(!($DB->record_exists('role', array('name'=>'Grade Tracker Admin'))))
    {
        $maxSortOrder++;
        //if it doesnt already exist
        $record = new stdClass();
        $record->name = 'Grade Tracker Admin';
        $record->shortname = 'gtadmin';
        $record->description = 'Someone who can administer the Grade Tracker';
        $record->sortorder = $maxSortOrder;
        $DB->insert_record('role', $record);
    }
    
    $record = new stdClass();
    $record->tabname = 'Trackers';
    $record->component = 'core';
    $record->tabclassfile = '/blocks/bcgt/classes/core/DashTab.class.php';
    $DB->insert_record('block_bcgt_tabs', $record);
    
    $record->tabname = 'Courses';
    $DB->insert_record('block_bcgt_tabs', $record);
    
    $record->tabname = 'Students';
    $DB->insert_record('block_bcgt_tabs', $record);
    
    $record->tabname = 'Team';
    $DB->insert_record('block_bcgt_tabs', $record);
    
    $record->tabname = 'Units';
    $DB->insert_record('block_bcgt_tabs', $record);
    
    $record->tabname = 'Reports';
    $DB->insert_record('block_bcgt_tabs', $record);
    
    $record->tabname = 'Assignments';
    $DB->insert_record('block_bcgt_tabs', $record);
    
    $record->tabname = 'Admin';
    $DB->insert_record('block_bcgt_tabs', $record);
    
    $record->tabname = 'Help';
    $DB->insert_record('block_bcgt_tabs', $record);
    
    $record->tabname = 'Feedback';
    $DB->insert_record('block_bcgt_tabs', $record);
     
    $record->tabname = 'Messages';
    $DB->insert_record('block_bcgt_tabs', $record);
    
    $subject = new stdClass();
    $subject->subject = 'N/A';
    $DB->insert_record('block_bcgt_subject', $subject);
    
    //Add the GCSE isnt the system
    $record = new stdClass();
    $record->name = 'GCSE';
    $record->weighting = 1;
    $record->quallevel = "2";
    $gcseID = $DB->insert_record('block_bcgt_prior_qual', $record);

    $record = new stdClass();
    $record->name = 'GCSE Short Course';
    $record->weighting = 0.5;
    $record->quallevel = "2";
    $gcseSCID = $DB->insert_record('block_bcgt_prior_qual', $record);

    $record = new stdClass();
    $record->name = 'GCSE Double Award';
    $record->weighting = 2;
    $record->quallevel = "2";
    $gcseDAID = $DB->insert_record('block_bcgt_prior_qual', $record);

    //Add the GCSE Grades
    $record = new stdClass();
    $record->bcgtpriorqualid = $gcseID;
    $record->grade = 'A*';
    $record->weighting = 1;
    $record->points = 58;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'A';
    $record->points = 52;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'B';
    $record->points = 46;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'C';
    $record->points = 40;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'D';
    $record->points = 34;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'E';
    $record->points = 28;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'F';
    $record->points = 22;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'G';
    $record->points = 16;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'U';
    $record->points = 0;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    //Add the GCSE Grades
    $record = new stdClass();
    $record->bcgtpriorqualid = $gcseSCID;
    $record->grade = 'A*';
    $record->weighting = 1;
    $record->points = 58;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'A';
    $record->points = 52;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'B';
    $record->points = 46;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'C';
    $record->points = 40;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'D';
    $record->points = 34;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'E';
    $record->points = 28;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'F';
    $record->points = 22;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'G';
    $record->points = 16;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'U';
    $record->points = 0;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    //Add the GCSE Grades
    $record = new stdClass();
    $record->bcgtpriorqualid = $gcseDAID;
    $record->grade = 'A*';
    $record->weighting = 1;
    $record->points = 58;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'A';
    $record->points = 52;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'B';
    $record->points = 46;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'C';
    $record->points = 40;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'D';
    $record->points = 34;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'E';
    $record->points = 28;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'F';
    $record->points = 22;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'G';
    $record->points = 16;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    $record->grade = 'U';
    $record->points = 0;
    $DB->insert_record('block_bcgt_prior_qual_grades', $record);

    //THE MOD LINKING
    //assign
    $sql = "SELECT * FROM {modules} WHERE name = ?";
    $assign = $DB->get_record_sql($sql, array('assign'));
    if($assign)
    {
        $stdObj = new stdClass();
        $stdObj->moduleid = $assign->id;
        $stdObj->modtablename = 'assign';
        $stdObj->modtablecoursefname = 'course';
        $stdObj->modtableduedatefname = 'duedate';
        $stdObj->modsubmissiontable = 'assign_submission';
        $stdObj->submissionuserfname = 'userid';
        $stdObj->submissiondatefname = 'timecreated';
        $stdObj->submissionmodidfname = 'assignment';
        $stdObj->checkforautotracking = 1;
        $DB->insert_record('block_bcgt_mod_linking', $stdObj);
    }

    //assignment
    $assignment = $DB->get_record_sql($sql, array('assignment'));
    if($assignment)
    {
        $stdObj = new stdClass();
        $stdObj->moduleid = $assignment->id;
        $stdObj->modtablename = 'assignment';
        $stdObj->modtablecoursefname = 'course';
        $stdObj->modtableduedatefname = 'timedue';
        $stdObj->modsubmissiontable = 'assignment_submissions';
        $stdObj->submissionuserfname = 'userid';
        $stdObj->submissiondatefname = 'timecreated';
        $stdObj->submissionmodidfname = 'assignment';
        $stdObj->checkforautotracking = 1;
        $DB->insert_record('block_bcgt_mod_linking', $stdObj);
    }

    //quiz
    $quiz = $DB->get_record_sql($sql, array('quiz'));
    if($quiz)
    {
        $stdObj = new stdClass();
        $stdObj->moduleid = $quiz->id;
        $stdObj->modtablename = 'quiz';
        $stdObj->modtablecoursefname = 'course';
        $stdObj->modtableduedatefname = 'timeclose';
        $stdObj->modsubmissiontable = 'quiz_attempts';
        $stdObj->submissionuserfname = 'userid';
        $stdObj->submissiondatefname = 'timefinish';
        $stdObj->submissionmodidfname = 'quiz';
        $stdObj->checkforautotracking = 1;
        $DB->insert_record('block_bcgt_mod_linking', $stdObj);
    }

    //urnitindirect
    $turnitin = $DB->get_record_sql($sql, array('turnitintool'));
    if($turnitin)
    {
        $stdObj = new stdClass();
        $stdObj->moduleid = $quiz->id;
        $stdObj->modtablename = 'turnitintool';
        $stdObj->modtablecoursefname = 'course';
        $stdObj->modtableduedatefname = 'defaultdtdue';
        $stdObj->modsubmissiontable = 'turnitintool_submissions';
        $stdObj->submissionuserfname = 'userid';
        $stdObj->submissiondatefname = 'submission_modified';
        $stdObj->submissionmodidfname = 'turnitintoolid';
        $stdObj->checkforautotracking = 1;
        $DB->insert_record('block_bcgt_mod_linking', $stdObj);
    }
    
    
    require_once($CFG->dirroot.'/blocks/bcgt/bcgt.class.php');
    $bcgt = new bcgt();
    $bcgt->install_all_plugins();
}
?>
