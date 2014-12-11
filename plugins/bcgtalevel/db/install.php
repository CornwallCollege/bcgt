<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
function xmldb_bcgtalevel_install()
{
    //family
    global $DB, $CFG;
    //require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalavel/classes/ALevelSubType.class.php');
    //require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalavel/classes/ALevelQualification.class.php');
    //require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalavel/classes/ALevelUnit.class.php');
    
// ---------------------- The Family ---------------------------
    $record = new stdClass();
    $record->id = 3;
    $record->family = 'ALevel';
    $record->classfolderlocation = '/blocks/bcgt/plugins/bcgtalevel/classes';
    $record->pluginname = 'bcgtalevel';
    $DB->insert_record_raw('block_bcgt_type_family', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
//    $DB->execute("INSERT INTO {block_bcgt_type_family} (id,family,classfolderlocation,pluginname) 
//        VALUES (3,'ALevel','/blocks/bcgt/plugins/bcgtalevel/classes','bcgtalevel')");
    
    // ---------------------- The Types ---------------------------
    $record = new stdClass();
    $record->id = 6;
    $record->type = 'A Level';
    $record->bcgttypefamilyid = 3;
    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
//    $DB->execute("INSERT INTO {block_bcgt_type} (id,type,bcgttypefamilyid) 
//        VALUES (6,'A Level',3)");
    
    $record = new stdClass();
    $record->id = 7;
    $record->type = 'AS Level';
    $record->bcgttypefamilyid = 3;
    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
//    $DB->execute("INSERT INTO {block_bcgt_type} (id,type,bcgttypefamilyid) 
//        VALUES (7,'AS Level',3)");

    $record = new stdClass();
    $record->id = 8;
    $record->type = 'A2 Level';
    $record->bcgttypefamilyid = 3;
    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
//    $DB->execute("INSERT INTO {block_bcgt_type} (id,type,bcgttypefamilyid) 
//        VALUES (8,'A2 Level',3)");

    // ---------------------- The Parent Type Family ---------------------------
    $record = new stdClass();
    $record->bcgttypeid = 6;
    $record->bcgtfamilyid = 3;
    $DB->insert_record('block_bcgt_fam_parent_type', $record);
    
    // ---------------------- The SubTypes ---------------------------
    if(!($DB->record_exists('block_bcgt_subtype', array('subtype'=>'AS Level'))))
    {
        $record = new stdClass();
        $record->id = 12;
        $record->subtype = 'AS Level';
        $record->subtypeshort = 'AS';
        $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
//        $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
//        VALUES (12,'AS Level','AS')");
    }
    if(!($DB->record_exists('block_bcgt_subtype', array('subtype'=>'A2 Level'))))
    {
        $record = new stdClass();
        $record->id = 13;
        $record->subtype = 'A2 Level';
        $record->subtypeshort = 'A2';
        $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
//        $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
//        VALUES (13,'A2 Level','A2')");
    }
    
    // ---------------------- The Target Quals ---------------------------
    //Level 3 (3), AS Level (7), AS Level (12)
    $record = new stdClass();
    $record->bcgtlevelid = 3;
    $record->bcgttypeid = 7;
    $record->bcgtsubtypeid = 12;
    $record->previoustargetqualid = -1;
    $ASLevelID = $DB->insert_record('block_bcgt_target_qual', $record); 
    
    //Level 3 (3), A2 Level (8), AS Level (13)
    $record = new stdClass();
    $record->bcgtlevelid = 3;
    $record->bcgttypeid = 8;
    $record->bcgtsubtypeid = 13;
    $record->previoustargetqualid = $ASLevelID;
    $A2LevelID = $DB->insert_record('block_bcgt_target_qual', $record); 
    
    // ---------------------- The Target Quals Grades ---------------------------
//    //A2 Level $A2LevelID
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->targetgrade = 'DDD';
//    $record->entryscoreupper = 38.2;
//    $record->entryscorelower = 10.0;
//    $record->ucaspoints = 180;
//    $record->ranking = 1;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->targetgrade = 'CDD';
//    $record->entryscoreupper = 41.2;
//    $record->entryscorelower = 38.2;
//    $record->ucaspoints = 200;
//    $record->ranking = 2;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->targetgrade = 'CCD';
//    $record->entryscoreupper = 43.0;
//    $record->entryscorelower = 41.2;
//    $record->ucaspoints = 220;
//    $record->ranking = 3;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->targetgrade = 'CCC';
//    $record->entryscoreupper = 44.8;
//    $record->entryscorelower = 43.0;
//    $record->ucaspoints = 240;
//    $record->ranking = 4;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->targetgrade = 'BCC';
//    $record->entryscoreupper = 46.6;
//    $record->entryscorelower = 44.8;
//    $record->ucaspoints = 260;
//    $record->ranking = 5;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->targetgrade = 'BBC';
//    $record->entryscoreupper = 48.4;
//    $record->entryscorelower = 46.6;
//    $record->ucaspoints = 280;
//    $record->ranking = 6;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->targetgrade = 'BBB';
//    $record->entryscoreupper = 50.2;
//    $record->entryscorelower = 48.4;
//    $record->ucaspoints = 300;
//    $record->ranking = 7;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->targetgrade = 'ABB';
//    $record->entryscoreupper = 52;
//    $record->entryscorelower = 50.2;
//    $record->ucaspoints = 320;
//    $record->ranking = 8;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->targetgrade = 'AAA';
//    $record->entryscoreupper = 55.0;
//    $record->entryscorelower = 52.0;
//    $record->ucaspoints = 360;
//    $record->ranking = 9;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->targetgrade = 'A*AAA';
//    $record->entryscoreupper = 58.0;
//    $record->entryscorelower = 55.0;
//    $record->ucaspoints = 500;
//    $record->ranking = 10;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    //A2 Level $A2LevelID
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->targetgrade = 'DDE';
//    $record->entryscoreupper = 38.2;
//    $record->entryscorelower = 10.0;
//    $record->ucaspoints = 80;
//    $record->ranking = 1;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->targetgrade = 'DDDE/DDE';
//    $record->entryscoreupper = 41.2;
//    $record->entryscorelower = 38.2;
//    $record->ucaspoints = 0;
//    $record->ranking = 1.6;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->targetgrade = 'DDDD';
//    $record->entryscoreupper = 43.0;
//    $record->entryscorelower = 41.2;
//    $record->ucaspoints = 120;
//    $record->ranking = 2;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->targetgrade = 'CDDD';
//    $record->entryscoreupper = 44.8;
//    $record->entryscorelower = 43.0;
//    $record->ucaspoints = 130;
//    $record->ranking = 3;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->targetgrade = 'CCDD';
//    $record->entryscoreupper = 46.6;
//    $record->entryscorelower = 44.8;
//    $record->ucaspoints = 140;
//    $record->ranking = 4;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->targetgrade = 'CCCC';
//    $record->entryscoreupper = 48.4;
//    $record->entryscorelower = 46.6;
//    $record->ucaspoints = 160;
//    $record->ranking = 5;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->targetgrade = 'BBCC';
//    $record->entryscoreupper = 50.2;
//    $record->entryscorelower = 48.4;
//    $record->ucaspoints = 180;
//    $record->ranking = 6;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->targetgrade = 'BBBC';
//    $record->entryscoreupper = 52;
//    $record->entryscorelower = 50.2;
//    $record->ucaspoints = 190;
//    $record->ranking = 7;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->targetgrade = 'ABBB';
//    $record->entryscoreupper = 55.0;
//    $record->entryscorelower = 52.0;
//    $record->ucaspoints = 210;
//    $record->ranking = 8;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->targetgrade = 'AAAB';
//    $record->entryscoreupper = 58.0;
//    $record->entryscorelower = 55.0;
//    $record->ucaspoints = 230;
//    $record->ranking = 9;
//    $DB->insert_record('block_bcgt_target_breakdown', $record);
//    
//    // ---------------------- The Traget Grades ---------------------------
//    //A2 Level $A2LevelID
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'U';
//    $record->ucaspoints = 0;
//    $record->ranking = 0;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'F';
//    $record->ucaspoints = 0;
//    $record->ranking = 1;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'E';
//    $record->ucaspoints = 40;
//    $record->ranking = 2;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'E/D';
//    $record->ucaspoints = 46.6;
//    $record->ranking = 2.3;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'D/E';
//    $record->ucaspoints = 53.3;
//    $record->ranking = 2.6;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'D';
//    $record->ucaspoints = 60;
//    $record->ranking = 3;
//    $record->upperscore = 38.2;
//    $record->lowerscore = 10.0;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'D/C';
//    $record->ucaspoints = 66.6;
//    $record->ranking = 3.3;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'C/D';
//    $record->ucaspoints = 73.3;
//    $record->ranking = 3.6;
//    $record->upperscore = 41.2;
//    $record->lowerscore = 38.2;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'C';
//    $record->ucaspoints = 80;
//    $record->ranking = 4;
//    $record->upperscore = 44.8;
//    $record->lowerscore = 41.2;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'C/B';
//    $record->ucaspoints = 86.6;
//    $record->ranking = 4.3;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'B/C';
//    $record->ucaspoints = 93.3;
//    $record->ranking = 4.6;
//    $record->upperscore = 48.4;
//    $record->lowerscore = 44.8;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'B';
//    $record->ucaspoints = 100;
//    $record->ranking = 5;
//    $record->upperscore = 52.0;
//    $record->lowerscore = 48.4;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'B/A';
//    $record->ucaspoints = 106.6;
//    $record->ranking = 5.3;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'A/B';
//    $record->ucaspoints = 113.3;
//    $record->ranking = 5.6;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'A';
//    $record->ucaspoints = 120;
//    $record->ranking = 6;
//    $record->upperscore = 55.0;
//    $record->lowerscore = 52.0;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'A/A*';
//    $record->ucaspoints = 126.6;
//    $record->ranking = 6.3;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'A*/A';
//    $record->ucaspoints = 133.3;
//    $record->ranking = 6.6;
//    $record->upperscore = 58.0;
//    $record->lowerscore = 55.0;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $A2LevelID;      
//    $record->grade = 'A*';
//    $record->ucaspoints = 140;
//    $record->ranking = 7;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    //AS Level $ASLevelID
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'U';
//    $record->ucaspoints = 0;
//    $record->ranking = 0;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'F';
//    $record->ucaspoints = 0;
//    $record->ranking = 1;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'E';
//    $record->ucaspoints = 20;
//    $record->ranking = 2;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'E/D';
//    $record->ucaspoints = 23.3;
//    $record->ranking = 2.3;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'D/E';
//    $record->ucaspoints = 26.6;
//    $record->ranking = 2.6;
//    $record->upperscore = 38.2;
//    $record->lowerscore = 10.0;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'D';
//    $record->ucaspoints = 30;
//    $record->ranking = 3;
//    $record->upperscore = 43.0;
//    $record->lowerscore = 38.2;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'C/D';
//    $record->ucaspoints = 36.6;
//    $record->ranking = 3.6;
//    $record->upperscore = 46.6;
//    $record->lowerscore = 43.0;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'D/C';
//    $record->ucaspoints = 33.3;
//    $record->ranking = 3.3;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'C';
//    $record->ucaspoints = 40;
//    $record->ranking = 4;
//    $record->upperscore = 48.4;
//    $record->lowerscore = 46.6;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'C/B';
//    $record->ucaspoints = 43.3;
//    $record->ranking = 4.3;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'B/C';
//    $record->ucaspoints = 46.6;
//    $record->ranking = 4.6;
//    $record->upperscore = 50.2;
//    $record->lowerscore = 48.4;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'B';
//    $record->ucaspoints = 50;
//    $record->ranking = 5;
//    $record->upperscore = 55;
//    $record->lowerscore = 50.2;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'B/A';
//    $record->ucaspoints = 53.3;
//    $record->ranking = 5.3;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'A/B';
//    $record->ucaspoints = 56.6;
//    $record->ranking = 5.6;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'A';
//    $record->ucaspoints = 60;
//    $record->ranking = 6;
//    $record->upperscore = 58.0;
//    $record->lowerscore = 55.0;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'A/A*';
//    $record->ucaspoints = 63.3;
//    $record->ranking = 6.3;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'A*/A';
//    $record->ucaspoints = 66.6;
//    $record->ranking = 6.6;
//    $DB->insert_record('block_bcgt_target_grades', $record);
//    
//    $record = new stdClass();
//    $record->bcgttargetqualid = $ASLevelID;      
//    $record->grade = 'A*';
//    $record->ucaspoints = 70;
//    $record->ranking = 7;
//    $DB->insert_record('block_bcgt_target_grades', $record);
    
    global $CFG;
    require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Breakdown.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/classes/core/TargetGrade.class.php');
    $breakdown = new Breakdown(-1, null);
    $breakdown->import_csv($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/data/Alevelbreakdowns.csv');

    $targetGrade = new TargetGrade(-1, null);
    $targetGrade->import_csv($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/data/Alevelgrades.csv');

    //******************** THE VALUES *********************//
    $record = new stdClass();
    $record->value = 'A*';
    $record->shortvalue = 'A*';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 7;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'A*/A';
    $record->shortvalue = 'A*/A';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 6.6;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'A/A*';
    $record->shortvalue = 'A/A*';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 6.3;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'A';
    $record->shortvalue = 'A';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 6;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'A/B';
    $record->shortvalue = 'A/B';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 5.6;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'B/A';
    $record->shortvalue = 'B/A';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 5.3;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'B';
    $record->shortvalue = 'B';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 5;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'B/C';
    $record->shortvalue = 'B/C';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 4.6;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'C/B';
    $record->shortvalue = 'C/B';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 4.3;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'C';
    $record->shortvalue = 'C';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 4;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'C/D';
    $record->shortvalue = 'C/D';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 3.6;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'D/C';
    $record->shortvalue = 'D/C';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 3.3;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'D';
    $record->shortvalue = 'D';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 3;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'D/E';
    $record->shortvalue = 'D/E';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 2.6;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'E/D';
    $record->shortvalue = 'E/D';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 2.3;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'E';
    $record->shortvalue = 'E';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 2;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'F';
    $record->shortvalue = 'F';
    $record->bcgttypeid = 6;
    $record->specialval = 'A';
    $record->ranking = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'U';
    $record->shortvalue = 'U';
    $record->bcgttypeid = 6;
    $record->criteriamet = 'X';
    $record->ranking = 0;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'Work Not Submitted';
    $record->shortvalue = 'WNS';
    $record->bcgttypeid = 6;
    $record->specialval = 'WNS';
    $record->ranking = -1;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'Work Submitted';
    $record->shortvalue = 'IN';
    $record->bcgttypeid = 6;
    $record->specialval = 'WS';
    $record->ranking = -2;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'Late';
    $record->shortvalue = 'L';
    $record->bcgttypeid = 6;
    $record->specialval = 'L';
    $record->ranking = -3;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->value = 'Not Attempted';
    $record->shortvalue = 'N/A';
    $record->bcgttypeid = 6;
    $record->specialval = 'X';
    $record->ranking = -4;
    $record->enabled = 1;
    $DB->insert_record('block_bcgt_value', $record);
    
    //now the scale
    $record = new stdClass();
    $record->name = 'Grade Tracker Alevel Scale';
    $record->scale = 'U,F,E,E/D,D/E,D,D/C,C/D,C,C/B,B/C,B,B/A,A/B,A,A/A*,A*/A,A*';
    $record->description = 'Scale to be used with Alevel Grade Tracker activities';
    $DB->insert_record('scale', $record);
    
    echo "Initial Alevel import done <br />";
    global $CFG;
//    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/lib.php');
//    run_alevel_initial_import();
    return true;
}
?>
