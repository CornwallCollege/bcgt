<?php
//target quals supported:
/*  BTEC HIGHER:
 *  LEVEL 5 HND
 *  LEVEL 4 HNC
 * 
 *  BTEC :
 *  LEVEL 3 EXTENDED DIPLOMA
 *  LEVEL 3 Diploma
 *  LEVEL 3 Subsidiary Diploma
 *  LEVEL 3 Certificate
 *  LEVEL 3 90-Credit Diploma
 * 
 *  LEVEL 2 Award
 *  LEVEL 2 Certificate
 *  LEVEL 2 Extended Certificate
 *  LEVEL 2 Diploma
 *  
 *  BTEC FOUNDATION:
 *  LEVEL 3 Foundation Diplom
 * 
 *  BTEC LOWER:
 *  LEVEL 1, Award
 *  LEVEL 1, Certificate
 *  Level 1 Diploma
 * 
 */


/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
function xmldb_bcgtbtec_install()
{
    global $DB, $CFG;
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/lib.php');
    // ---------------------- The Family ---------------------------
//    $record = new stdClass();
//    $record->id = 2;
//    $record->family = 'BTEC';
//    $record->classfolderlocation = '/blocks/bcgt/plugins/bcgtbtec/classes';
//    $record->pluginname = 'bcgtbtec';
//    $DB->insert_record_raw('block_bcgt_type_family', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_type_family} (id,family,classfolderlocation,pluginname) 
        VALUES (2,'BTEC','/blocks/bcgt/plugins/bcgtbtec/classes','bcgtbtec')");
    
    // ---------------------- The Types ---------------------------
//    $record = new stdClass();
//    $record->id = 2;
//    $record->type = 'BTEC';
//    $record->bcgttypefamilyid = 2
//    $record->specificationdesc = 'BTEC Nationals From 2010';
//    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_type} (id,type,bcgttypefamilyid, specificationdesc) 
        VALUES (2,'BTEC',2, 'BTEC Nationals From 2010')");

//    $record = new stdClass();
//    $record->id = 3;
//    $record->type = 'BTEC Higher';
//    $record->bcgttypefamilyid = 2;
//    $record->specificationdesc = 'BTEC Higher Nationals from 2010';
//    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_type} (id,type,bcgttypefamilyid, specificationdesc) 
        VALUES (3,'BTEC Higher',2, 'BTEC Higher Nationals from 2010')");

//    $record = new stdClass();
//    $record->id = 4;
//    $record->type = 'BTEC Foundation';
//    $record->bcgttypefamilyid = 2;
//    $record->specificationdesc = 'BTEC Foundation Diploma in Art and Design';
//    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_type} (id,type,bcgttypefamilyid,specificationdesc) 
        VALUES (4,'BTEC Foundation',2,'BTEC Foundation Diploma in Art and Design')");

//    $record = new stdClass();
//    $record->id = 5;
//    $record->type = 'BTEC Lower';
//    $record->bcgttypefamilyid = 2;
//    $record->specificationdesc = 'BTEC Firsts 2010';
//    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_type} (id,type,bcgttypefamilyid,specificationdesc) 
        VALUES (5,'BTEC Lower',2,'BTEC Firsts 2010')");
    
    //    $record = new stdClass();
//    $record->id = 12;
//    $record->type = 'BTEC Firsts 2013';
//    $record->bcgttypefamilyid = 2;
//    $record->specificationdesc = 'BTEC Firsts 2013';
//    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_type} (id,type,bcgttypefamilyid,specificationdesc) 
        VALUES (12,'BTEC First 2013',2,'BTEC Firsts 2013')");
    
    // ---------------------- The Parent Type Family ---------------------------
    $record = new stdClass();
    $record->bcgttypeid = 2;
    $record->bcgtfamilyid = 2;
    $DB->insert_record('block_bcgt_fam_parent_type', $record);
    
    // ---------------------- The SubTypes ---------------------------
    if(!($DB->record_exists('block_bcgt_subtype', array('subtype'=>'Extended Diploma'))))
    {
//        $record = new stdClass();
//        $record->id = 2;
//        $record->subtype = 'Extended Diploma';
//        $record->subtypeshort = 'ExDip';
//        $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
        VALUES (2,'Extended Diploma','ExDip')");
    }
    if(!($DB->record_exists('block_bcgt_subtype', array('subtype'=>'Diploma'))))
    {
//        $record = new stdClass();
//        $record->id = 3;
//        $record->subtype = 'Diploma';
//        $record->subtypeshort = 'Dip';
//        $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
        VALUES (3,'Diploma','Dip')");
    }
    if(!($DB->record_exists('block_bcgt_subtype', array('subtype'=>'Subsidiary Diploma'))))
    {
//        $record = new stdClass();
//        $record->id = 4;
//        $record->subtype = 'Subsidiary Diploma';
//        $record->subtypeshort = 'SubDip';
//        $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
        VALUES (4,'Subsidiary Diploma','SubDip')");
    }
    if(!($DB->record_exists('block_bcgt_subtype', array('subtype'=>'Certificate'))))
    {
//        $record = new stdClass();
//        $record->id = 5;
//        $record->subtype = 'Certificate';
//        $record->subtypeshort = 'Cert';
//        $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
        VALUES (5,'Certificate','Cert')");
    }
    if(!($DB->record_exists('block_bcgt_subtype', array('subtype'=>'Award'))))
    {
//        $record = new stdClass();
//        $record->id = 6;
//        $record->subtype = 'Award';
//        $record->subtypeshort = 'Awrd';
//        $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
        VALUES (6,'Award','Awrd')");
    }
    if(!($DB->record_exists('block_bcgt_subtype', array('subtype'=>'HNC'))))
    {
//        $record = new stdClass();
//        $record->id = 7;
//        $record->subtype = 'HNC';
//        $record->subtypeshort = 'HNC';
//        $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
        VALUES (7,'HNC','HNC')");
    }
    if(!($DB->record_exists('block_bcgt_subtype', array('subtype'=>'HND'))))
    {
//        $record = new stdClass();
//        $record->id = 8;
//        $record->subtype = 'HND';
//        $record->subtypeshort = 'HND';
//        $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
        VALUES (8,'HND','HND')");
    }
    if(!($DB->record_exists('block_bcgt_subtype', array('subtype'=>'90-Credit Diploma'))))
    {
//        $record = new stdClass();
//        $record->id = 9;
//        $record->subtype = '90-Credit Diploma';
//        $record->subtypeshort = '90-Dip';
//        $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
        VALUES (9,'90-Credit Diploma','90-Dip')");
    }
    if(!($DB->record_exists('block_bcgt_subtype', array('subtype'=>'Foundation Diploma'))))
    {
//        $record = new stdClass();
//        $record->id = 10;
//        $record->subtype = 'Foundation Diploma';
//        $record->subtypeshort = 'FndDip';
//        $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
        VALUES (10,'Foundation Diploma','FndDip')");
    }
    if(!($DB->record_exists('block_bcgt_subtype', array('subtype'=>'Extended Certificate'))))
    {
//        $record = new stdClass();
//        $record->id = 11;
//        $record->subtype = 'Extended Certificate';
//        $record->subtypeshort = 'ExCert';
//        $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
        VALUES (11,'Extended Certificate','ExCert')");
    }
 
    // ---------------------- The Values for the grids ---------------------------
    $record = new stdClass();
    $record->value = 'Achieved';
    $record->shortvalue = 'A';
    $record->bcgttypeid = 2;
    $record->specialval = 'A';
    $record->ranking = 1;
    $record->enabled = 1;
    $id = $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->bcgtvalueid = $id;
    $record->coreimg = '/pix/grid_symbols/core/achieved.png';
    $record->coreimglate = '/pix/grid_symbols/core/achievedLate.png';
    $DB->insert_record('block_bcgt_value_settings', $record);
    
    $record = new stdClass();
    $record->value = 'Partially Achieved';
    $record->shortvalue = 'PA';
    $record->bcgttypeid = 2;
    $record->specialval = '';
    $record->ranking = 2;
    $record->enabled = 1;
    $id = $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->bcgtvalueid = $id;
    $record->coreimg = '/pix/grid_symbols/core/pachieved.png';
    $record->coreimglate = '/pix/grid_symbols/core/paLate.png';
    $DB->insert_record('block_bcgt_value_settings', $record);
    
    $record = new stdClass();
    $record->value = 'Not Achieved';
    $record->shortvalue = 'X';
    $record->bcgttypeid = 2;
    $record->specialval = 'X';
    $record->ranking = 3;
    $record->enabled = 1;
    $id = $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->bcgtvalueid = $id;
    $record->coreimg = '/pix/grid_symbols/core/notachieved.png';
    $record->coreimglate = '/pix/grid_symbols/core/notachievedLate.png';
    $DB->insert_record('block_bcgt_value_settings', $record);
    
    $record = new stdClass();
    $record->value = 'Not Attempted';
    $record->shortvalue = 'N/A';
    $record->bcgttypeid = 2;
    $record->specialval = '';
    $record->ranking = 4;
    $record->enabled = 1;
    $id = $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->bcgtvalueid = $id;
    $record->coreimg = '/pix/grid_symbols/core/notattempted.png';
    $DB->insert_record('block_bcgt_value_settings', $record);
    
    $record = new stdClass();
    $record->value = 'Referred';
    $record->shortvalue = 'R';
    $record->bcgttypeid = 2;
    $record->specialval = '';
    $record->ranking = 5;
    $record->enabled = 1;
    $id = $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->bcgtvalueid = $id;
    $record->coreimg = '/pix/grid_symbols/core/referred.png';
    $DB->insert_record('block_bcgt_value_settings', $record);
    
    $record = new stdClass();
    $record->value = 'Late Submission';
    $record->shortvalue = 'L';
    $record->bcgttypeid = 2;
    $record->specialval = 'L';
    $record->ranking = 6;
    $record->enabled = 1;
    $id = $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->bcgtvalueid = $id;
    $record->coreimg = '/pix/grid_symbols/core/late.png';
    $DB->insert_record('block_bcgt_value_settings', $record);
    
    $record = new stdClass();
    $record->value = 'Work Submitted';
    $record->shortvalue = 'WS';
    $record->bcgttypeid = 2;
    $record->specialval = 'WS';
    $record->ranking = 7;
    $record->enabled = 1;
    $id = $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->bcgtvalueid = $id;
    $record->coreimg = '/pix/grid_symbols/core/in.png';
    $DB->insert_record('block_bcgt_value_settings', $record);
    
    $record = new stdClass();
    $record->value = 'Work Not Submitted';
    $record->shortvalue = 'WNS';
    $record->bcgttypeid = 2;
    $record->specialval = 'WNS';
    $record->ranking = 8;
    $record->enabled = 1;
    $id = $DB->insert_record('block_bcgt_value', $record);
    
    $record = new stdClass();
    $record->bcgtvalueid = $id;
    $record->coreimg = '/pix/grid_symbols/core/notin.png';
    $DB->insert_record('block_bcgt_value_settings', $record);
    
    //---------------------- The Unit Types ---------------------------
    $record = new stdClass();
    $record->type = 'Core Unit';
    $record->bcgttypeid = 3;
    $DB->insert_record('block_bcgt_unit_type', $record);
    
    $record = new stdClass();
    $record->type = 'APL Unit';
    $record->bcgttypeid = 3;
    $DB->insert_record('block_bcgt_unit_type', $record);
    
    /*** FINAL PROJECT ****/
    $record = new stdClass();
    $record->type = 'Final Project';
    $record->bcgttypeid = 4;
    $DB->insert_record('block_bcgt_unit_type', $record);
    
    //new unit type: for 
    $stdObj = new stdClass();
    $stdObj->type = 'Externally Assessed';
    $stdObj->bcgttypeid = 12;
    $DB->insert_record('block_bcgt_unit_type', $stdObj);

    $stdObj = new stdClass();
    $stdObj->type = 'Internally Assessed';
    $stdObj->bcgttypeid = 12;
    $DB->insert_record('block_bcgt_unit_type', $stdObj);
    
    // ---------------------- The Type Awards ---------------------------
    $record = new stdClass();
    $record->award = 'Pass';
    $record->ranking = 1;
    $record->bcgttypeid = 2;
    $btecPassID = $DB->insert_record('block_bcgt_type_award', $record);
    
    $record = new stdClass();
    $record->award = 'Merit';
    $record->ranking = 2;
    $record->bcgttypeid = 2;
    $btecMeritID = $DB->insert_record('block_bcgt_type_award', $record);
    
    $record = new stdClass();
    $record->award = 'Distinction';
    $record->ranking = 3;
    $record->bcgttypeid = 2;
    $btecDissID = $DB->insert_record('block_bcgt_type_award', $record);
    
    $record = new stdClass();
    $record->award = 'Pass';
    $record->ranking = 1;
    $record->bcgttypeid = 3;
    $btecHPassID = $DB->insert_record('block_bcgt_type_award', $record);
    
    $record = new stdClass();
    $record->award = 'Merit';
    $record->ranking = 2;
    $record->bcgttypeid = 3;
    $btecHMeritID = $DB->insert_record('block_bcgt_type_award', $record);
    
    $record = new stdClass();
    $record->award = 'Distinction';
    $record->ranking = 3;
    $record->bcgttypeid = 3;
    $btecHDissID = $DB->insert_record('block_bcgt_type_award', $record);
    
    $record = new stdClass();
    $record->award = 'Pass';
    $record->ranking = 1;
    $record->bcgttypeid = 4;
    $btecFPassID = $DB->insert_record('block_bcgt_type_award', $record);
    
    $record = new stdClass();
    $record->award = 'Merit';
    $record->ranking = 2;
    $record->bcgttypeid = 4;
    $btecFMeritID = $DB->insert_record('block_bcgt_type_award', $record);
    
    $record = new stdClass();
    $record->award = 'Distinction';
    $record->ranking = 3;
    $record->bcgttypeid = 4;
    $btecFDissID = $DB->insert_record('block_bcgt_type_award', $record);
    
    $record = new stdClass();
    $record->award = 'Pass';
    $record->ranking = 1;
    $record->bcgttypeid = 5;
    $btecLPassID = $DB->insert_record('block_bcgt_type_award', $record);
    
    // ---------------------- The Unit Points ---------------------------
    //level 3
    $record = new stdClass();
    $record->bcgtlevelid = 3;
    $record->bcgttypeawardid = $btecPassID;
    $record->points = 7.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 3;
    $record->bcgttypeawardid = $btecMeritID;
    $record->points = 8.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 3;
    $record->bcgttypeawardid = $btecDissID;
    $record->points = 9.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    //level 2
    $record = new stdClass();
    $record->bcgtlevelid = 2;
    $record->bcgttypeawardid = $btecPassID;
    $record->points = 5.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 2;
    $record->bcgttypeawardid = $btecMeritID;
    $record->points = 6.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 2;
    $record->bcgttypeawardid = $btecDissID;
    $record->points = 7.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    //;evel 1
    $record = new stdClass();
    $record->bcgtlevelid = 1;
    $record->bcgttypeawardid = $btecPassID;
    $record->points = 3.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 1;
    $record->bcgttypeawardid = $btecMeritID;
    $record->points = 4.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 1;
    $record->bcgttypeawardid = $btecDissID;
    $record->points = 5.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    //level 4
    $record = new stdClass();
    $record->bcgtlevelid = 4;
    $record->bcgttypeawardid = $btecHPassID;
    $record->points = 0.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 4;
    $record->bcgttypeawardid = $btecHMeritID;
    $record->points = 1.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 4;
    $record->bcgttypeawardid = $btecHDissID;
    $record->points = 2.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    //level 5
    $record = new stdClass();
    $record->bcgtlevelid = 5;
    $record->bcgttypeawardid = $btecHPassID;
    $record->points = 0.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 5;
    $record->bcgttypeawardid = $btecHMeritID;
    $record->points = 1.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 5;
    $record->bcgttypeawardid = $btecHDissID;
    $record->points = 2.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    //now the type awards and points for BTECFirsts2013::
        //award, ranking, bcgttypeid, pointslower, pointsupper
        $stdObj = new stdClass();
        $stdObj->award = 'Unclassified';
        $stdObj->ranking = 1;
        $stdObj->bcgttypeid = 12;
        $stdObj->shortaward = 'U';
        $unclasAward = $DB->insert_record('block_bcgt_type_award', $stdObj);
        
        //now points
        $stdObj = new stdClass();
        $stdObj->bcgtlevelid = 7;
        $stdObj->bcgttypeawardid = $unclasAward;
        $stdObj->points = 0;
        $DB->insert_record('block_bcgt_unit_points', $stdObj);
        
        $stdObj = new stdClass();
        $stdObj->award = 'Level 1';
        $stdObj->ranking = 2;
        $stdObj->bcgttypeid = 12;
        $stdObj->shortaward = 'L1';
        $l1Award = $DB->insert_record('block_bcgt_type_award', $stdObj);
        
        $stdObj = new stdClass();
        $stdObj->bcgtlevelid = 7;
        $stdObj->bcgttypeawardid = $l1Award;
        $stdObj->points = 2;
        $DB->insert_record('block_bcgt_unit_points', $stdObj);
        
        $stdObj = new stdClass();
        $stdObj->award = 'Level 2 Pass (P)';
        $stdObj->ranking = 3;
        $stdObj->bcgttypeid = 12;
        $stdObj->shortaward = 'L2 P';
        $passAward = $DB->insert_record('block_bcgt_type_award', $stdObj);
        
        $stdObj = new stdClass();
        $stdObj->bcgtlevelid = 7;
        $stdObj->bcgttypeawardid = $passAward;
        $stdObj->points = 4;
        $DB->insert_record('block_bcgt_unit_points', $stdObj);
        
        $stdObj = new stdClass();
        $stdObj->award = 'Level 2 Merit (M)';
        $stdObj->ranking = 4;
        $stdObj->bcgttypeid = 12;
        $stdObj->shortaward = 'L2 M';
        $meritAward = $DB->insert_record('block_bcgt_type_award', $stdObj);
        
        $stdObj = new stdClass();
        $stdObj->bcgtlevelid = 7;
        $stdObj->bcgttypeawardid = $meritAward;
        $stdObj->points = 6;
        $DB->insert_record('block_bcgt_unit_points', $stdObj);
        
        $stdObj = new stdClass();
        $stdObj->award = 'Level 2 Distinction (D)';
        $stdObj->ranking = 5;
        $stdObj->bcgttypeid = 12;
        $stdObj->shortaward = 'L2 D';
        $dissAward = $DB->insert_record('block_bcgt_type_award', $stdObj);
        
        $stdObj = new stdClass();
        $stdObj->bcgtlevelid = 7;
        $stdObj->bcgttypeawardid = $dissAward;
        $stdObj->points = 8;
        $DB->insert_record('block_bcgt_unit_points', $stdObj);
    
    
    // ---------------------- The Target Quals ---------------------------
    //Level 1 (1), BTEC Level 1 (5), Award (6)
    $record = new stdClass();
    $record->bcgtlevelid = 1;
    $record->bcgttypeid = 5;
    $record->bcgtsubtypeid = 6;
    $record->previoustargetqualid = -1;
    $l1AwardID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l1AwardID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l1AwardID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'P';
    $stdObj->shortvalue = 'P';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l1AwardID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 7;
    $DB->insert_record('block_bcgt_target_qual_att', $record); 
    //DEFAULT NUMBER OF CREDITS
    
    //Level 1 (1), BTEC Level 1 (5), Certificate (5)
    $record = new stdClass();
    $record->bcgtlevelid = 1;
    $record->bcgttypeid = 5;
    $record->bcgtsubtypeid = 5;
    $record->previoustargetqualid = $l1AwardID;
    $l1CertificateID = $DB->insert_record('block_bcgt_target_qual', $record);

    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l1CertificateID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l1CertificateID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'P';
    $stdObj->shortvalue = 'P';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l1CertificateID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 13;
    $DB->insert_record('block_bcgt_target_qual_att', $record); 
    
    //Level 1 (1), BTEC Level 1 (5), Diploma (3)
    $record = new stdClass();
    $record->bcgtlevelid = 1;
    $record->bcgttypeid = 5;
    $record->bcgtsubtypeid = 3;
    $record->previoustargetqualid = $l1CertificateID;
    $l1DipID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l1DipID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l1DipID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'P';
    $stdObj->shortvalue = 'P';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l1DipID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 37;
    $DB->insert_record('block_bcgt_target_qual_att', $record); 
    
    //Level 2 (2), BTEC (2), Award (6)
    $record = new stdClass();
    $record->bcgtlevelid = 2;
    $record->bcgttypeid = 2;
    $record->bcgtsubtypeid = 6;
    $record->previoustargetqualid = -1;
    $l2AwardID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l2AwardID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l2AwardID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'P';
    $stdObj->shortvalue = 'P';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'P/M';
    $stdObj->shortvalue = 'P/M';
    $stdObj->ranking = 1.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/P';
    $stdObj->shortvalue = 'M/P';
    $stdObj->ranking = 1.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M';
    $stdObj->shortvalue = 'M';
    $stdObj->ranking = 2;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/D';
    $stdObj->shortvalue = 'M/D';
    $stdObj->ranking = 2.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/M';
    $stdObj->shortvalue = 'D/M';
    $stdObj->ranking = 2.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D';
    $stdObj->shortvalue = 'D';
    $stdObj->ranking = 3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/D*';
    $stdObj->shortvalue = 'D/D*';
    $stdObj->ranking = 3.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*/D';
    $stdObj->shortvalue = 'D*/D';
    $stdObj->ranking = 3.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*';
    $stdObj->shortvalue = 'D*';
    $stdObj->ranking = 4;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l2AwardID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 10;
    $DB->insert_record('block_bcgt_target_qual_att', $record); 

    //Level 2 (2), BTEC (2), Certificate (5)
    $record = new stdClass();
    $record->bcgtlevelid = 2;
    $record->bcgttypeid = 2;
    $record->bcgtsubtypeid = 5;
    $record->previoustargetqualid = $l2AwardID;
    $l2CertID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l2CertID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l2CertID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'P';
    $stdObj->shortvalue = 'P';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'P/M';
    $stdObj->shortvalue = 'P/M';
    $stdObj->ranking = 1.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/P';
    $stdObj->shortvalue = 'M/P';
    $stdObj->ranking = 1.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M';
    $stdObj->shortvalue = 'M';
    $stdObj->ranking = 2;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/D';
    $stdObj->shortvalue = 'M/D';
    $stdObj->ranking = 2.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/M';
    $stdObj->shortvalue = 'D/M';
    $stdObj->ranking = 2.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D';
    $stdObj->shortvalue = 'D';
    $stdObj->ranking = 3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/D*';
    $stdObj->shortvalue = 'D/D*';
    $stdObj->ranking = 3.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*/D';
    $stdObj->shortvalue = 'D*/D';
    $stdObj->ranking = 3.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*';
    $stdObj->shortvalue = 'D*';
    $stdObj->ranking = 4;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l2CertID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 15;
    $DB->insert_record('block_bcgt_target_qual_att', $record); 

    //Level 2 (2), BTEC (2), Extended Certificate (11)
    $record = new stdClass();
    $record->bcgtlevelid = 2;
    $record->bcgttypeid = 2;
    $record->bcgtsubtypeid = 11;
    $record->previoustargetqualid = $l2CertID;
    $l2ExCertID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l2ExCertID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l2ExCertID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'P';
    $stdObj->shortvalue = 'P';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'P/M';
    $stdObj->shortvalue = 'P/M';
    $stdObj->ranking = 1.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/P';
    $stdObj->shortvalue = 'M/P';
    $stdObj->ranking = 1.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M';
    $stdObj->shortvalue = 'M';
    $stdObj->ranking = 2;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/D';
    $stdObj->shortvalue = 'M/D';
    $stdObj->ranking = 2.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/M';
    $stdObj->shortvalue = 'D/M';
    $stdObj->ranking = 2.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D';
    $stdObj->shortvalue = 'D';
    $stdObj->ranking = 3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/D*';
    $stdObj->shortvalue = 'D/D*';
    $stdObj->ranking = 3.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*/D';
    $stdObj->shortvalue = 'D*/D';
    $stdObj->ranking = 3.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*';
    $stdObj->shortvalue = 'D*';
    $stdObj->ranking = 4;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l2ExCertID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 30;
    $DB->insert_record('block_bcgt_target_qual_att', $record); 
    
    //Level 2 (2), BTEC (2), Diploma (3)
    $record = new stdClass();
    $record->bcgtlevelid = 2;
    $record->bcgttypeid = 2;
    $record->bcgtsubtypeid = 3;
    $record->previoustargetqualid = $l2ExCertID;
    $l2DiplomaID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l2DiplomaID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l2DiplomaID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'P';
    $stdObj->shortvalue = 'P';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'P/M';
    $stdObj->shortvalue = 'P/M';
    $stdObj->ranking = 1.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/P';
    $stdObj->shortvalue = 'M/P';
    $stdObj->ranking = 1.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M';
    $stdObj->shortvalue = 'M';
    $stdObj->ranking = 2;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/D';
    $stdObj->shortvalue = 'M/D';
    $stdObj->ranking = 2.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/M';
    $stdObj->shortvalue = 'D/M';
    $stdObj->ranking = 2.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D';
    $stdObj->shortvalue = 'D';
    $stdObj->ranking = 3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/D*';
    $stdObj->shortvalue = 'D/D*';
    $stdObj->ranking = 3.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*/D';
    $stdObj->shortvalue = 'D*/D';
    $stdObj->ranking = 3.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*';
    $stdObj->shortvalue = 'D*';
    $stdObj->ranking = 4;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l2DiplomaID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 60;
    $DB->insert_record('block_bcgt_target_qual_att', $record); 
    
    //Level 3 (3), BTEC Foundation (4), Foundation Diploma (10)
    $record = new stdClass();
    $record->bcgtlevelid = 3;
    $record->bcgttypeid = 4;
    $record->bcgtsubtypeid = 10;
    $record->previoustargetqualid = -1;
    $l3FoundDiplomaID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l3FoundDiplomaID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l3FoundDiplomaID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'P';
    $stdObj->shortvalue = 'P';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'P/M';
    $stdObj->shortvalue = 'P/M';
    $stdObj->ranking = 1.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/P';
    $stdObj->shortvalue = 'M/P';
    $stdObj->ranking = 1.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M';
    $stdObj->shortvalue = 'M';
    $stdObj->ranking = 2;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/D';
    $stdObj->shortvalue = 'M/D';
    $stdObj->ranking = 2.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/M';
    $stdObj->shortvalue = 'D/M';
    $stdObj->ranking = 2.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D';
    $stdObj->shortvalue = 'D';
    $stdObj->ranking = 3;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    //Level 3 (3), BTEC (2), Certificate (5)
    $record = new stdClass();
    $record->bcgtlevelid = 3;
    $record->bcgttypeid = 2;
    $record->bcgtsubtypeid = 5;
    $record->previoustargetqualid = $l3FoundDiplomaID;
    $l3CertID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l3CertID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l3CertID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'P';
    $stdObj->shortvalue = 'P';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'P/M';
    $stdObj->shortvalue = 'P/M';
    $stdObj->ranking = 1.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/P';
    $stdObj->shortvalue = 'M/P';
    $stdObj->ranking = 1.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M';
    $stdObj->shortvalue = 'M';
    $stdObj->ranking = 2;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/D';
    $stdObj->shortvalue = 'M/D';
    $stdObj->ranking = 2.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/M';
    $stdObj->shortvalue = 'D/M';
    $stdObj->ranking = 2.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D';
    $stdObj->shortvalue = 'D';
    $stdObj->ranking = 3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/D*';
    $stdObj->shortvalue = 'D/D*';
    $stdObj->ranking = 3.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*/D';
    $stdObj->shortvalue = 'D*/D';
    $stdObj->ranking = 3.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*';
    $stdObj->shortvalue = 'D*';
    $stdObj->ranking = 4;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l3CertID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 30;
    $DB->insert_record('block_bcgt_target_qual_att', $record);

    //Level 3 (3), BTEC (2), Subsidiary Diploma (4)
    $record = new stdClass();
    $record->bcgtlevelid = 3;
    $record->bcgttypeid = 2;
    $record->bcgtsubtypeid = 4;
    $record->previoustargetqualid = $l3CertID;
    $l3SubID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l3SubID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l3SubID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'P';
    $stdObj->shortvalue = 'P';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'P/M';
    $stdObj->shortvalue = 'P/M';
    $stdObj->ranking = 1.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/P';
    $stdObj->shortvalue = 'M/P';
    $stdObj->ranking = 1.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M';
    $stdObj->shortvalue = 'M';
    $stdObj->ranking = 2;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/D';
    $stdObj->shortvalue = 'M/D';
    $stdObj->ranking = 2.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/M';
    $stdObj->shortvalue = 'D/M';
    $stdObj->ranking = 2.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D';
    $stdObj->shortvalue = 'D';
    $stdObj->ranking = 3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/D*';
    $stdObj->shortvalue = 'D/D*';
    $stdObj->ranking = 3.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*/D';
    $stdObj->shortvalue = 'D*/D';
    $stdObj->ranking = 3.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*';
    $stdObj->shortvalue = 'D*';
    $stdObj->ranking = 4;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l3SubID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 60;
    $DB->insert_record('block_bcgt_target_qual_att', $record);
    
    //Level 3 (3), BTEC (2), 90-Credit Diploma (9)
    $record = new stdClass();
    $record->bcgtlevelid = 3;
    $record->bcgttypeid = 2;
    $record->bcgtsubtypeid = 9;
    $record->previoustargetqualid = $l3SubID;
    $l390CredID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l390CredID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l390CredID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'PP';
    $stdObj->shortvalue = 'PP';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'PP/MP';
    $stdObj->shortvalue = 'PP/MP';
    $stdObj->ranking = 1.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MP/PP';
    $stdObj->shortvalue = 'MP/PP';
    $stdObj->ranking = 1.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MP';
    $stdObj->shortvalue = 'MP';
    $stdObj->ranking = 2;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MP/MM';
    $stdObj->shortvalue = 'MP/MM';
    $stdObj->ranking = 2.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MM/MP';
    $stdObj->shortvalue = 'MM/MP';
    $stdObj->ranking = 2.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MM';
    $stdObj->shortvalue = 'MM';
    $stdObj->ranking = 3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MM/DM';
    $stdObj->shortvalue = 'MM/DM';
    $stdObj->ranking = 3.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DM/MM';
    $stdObj->shortvalue = 'DM/MM';
    $stdObj->ranking = 3.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DM';
    $stdObj->shortvalue = 'DM';
    $stdObj->ranking = 4;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DM/DD';
    $stdObj->shortvalue = 'DM/DD';
    $stdObj->ranking = 4.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DD/DM';
    $stdObj->shortvalue = 'DD/DM';
    $stdObj->ranking = 4.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DD';
    $stdObj->shortvalue = 'DD';
    $stdObj->ranking = 5;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DD/D*D';
    $stdObj->shortvalue = 'DD/D*D';
    $stdObj->ranking = 5.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D/DD';
    $stdObj->shortvalue = 'D*D/DD';
    $stdObj->ranking = 5.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D';
    $stdObj->shortvalue = 'D*D';
    $stdObj->ranking = 6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D/D*D*';
    $stdObj->shortvalue = 'D*D/D*D*';
    $stdObj->ranking = 6.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D*/D*D';
    $stdObj->shortvalue = 'D*D*/D*D';
    $stdObj->ranking = 6.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D*';
    $stdObj->shortvalue = 'D*D*';
    $stdObj->ranking = 7;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l390CredID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 90;
    $DB->insert_record('block_bcgt_target_qual_att', $record);
    
    //Level 3 (3), BTEC (2), Diploma (3)
    
    
    $record = new stdClass();
    $record->bcgtlevelid = 3;
    $record->bcgttypeid = 2;
    $record->bcgtsubtypeid = 3;
    $record->previoustargetqualid = $l390CredID;
    $l3DipID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l3DipID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l3DipID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'PP';
    $stdObj->shortvalue = 'PP';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'PP/MP';
    $stdObj->shortvalue = 'PP/MP';
    $stdObj->ranking = 1.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MP/PP';
    $stdObj->shortvalue = 'MP/PP';
    $stdObj->ranking = 1.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MP';
    $stdObj->shortvalue = 'MP';
    $stdObj->ranking = 2;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MP/MM';
    $stdObj->shortvalue = 'MP/MM';
    $stdObj->ranking = 2.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MM/MP';
    $stdObj->shortvalue = 'MM/MP';
    $stdObj->ranking = 2.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MM';
    $stdObj->shortvalue = 'MM';
    $stdObj->ranking = 3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MM/DM';
    $stdObj->shortvalue = 'MM/DM';
    $stdObj->ranking = 3.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DM/MM';
    $stdObj->shortvalue = 'DM/MM';
    $stdObj->ranking = 3.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DM';
    $stdObj->shortvalue = 'DM';
    $stdObj->ranking = 4;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DM/DD';
    $stdObj->shortvalue = 'DM/DD';
    $stdObj->ranking = 4.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DD/DM';
    $stdObj->shortvalue = 'DD/DM';
    $stdObj->ranking = 4.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DD';
    $stdObj->shortvalue = 'DD';
    $stdObj->ranking = 5;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DD/D*D';
    $stdObj->shortvalue = 'DD/D*D';
    $stdObj->ranking = 5.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D/DD';
    $stdObj->shortvalue = 'D*D/DD';
    $stdObj->ranking = 5.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D';
    $stdObj->shortvalue = 'D*D';
    $stdObj->ranking = 6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D/D*D*';
    $stdObj->shortvalue = 'D*D/D*D*';
    $stdObj->ranking = 6.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D*/D*D';
    $stdObj->shortvalue = 'D*D*/D*D';
    $stdObj->ranking = 6.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D*';
    $stdObj->shortvalue = 'D*D*';
    $stdObj->ranking = 7;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l3DipID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 120;
    $DB->insert_record('block_bcgt_target_qual_att', $record);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l3DipID;
    $record->name = BTECSubType::DEFAULTNUMBEROFYEARSNAME;
    $record->value = 2;
    $DB->insert_record('block_bcgt_target_qual_att', $record);
    
    //Level 3 (3), BTEC (2), Extended Diploma (2)
    $record = new stdClass();
    $record->bcgtlevelid = 3;
    $record->bcgttypeid = 2;
    $record->bcgtsubtypeid = 2;
    $record->previoustargetqualid = $l3DipID;
    $l3EDipID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l3EDipID;
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $stdObj->context = 'assessment';
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l3EDipID;
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'PPP';
    $stdObj->shortvalue = 'PPP';
    $stdObj->ranking = 1;
    $stdObj->context = 'assessment';
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l3EDipID;
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'PPP/MPP';
    $stdObj->shortvalue = 'PPP/MPP';
    $stdObj->ranking = 1.3;
    $stdObj->context = 'assessment';
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l3EDipID;
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'MPP/PPP';
    $stdObj->shortvalue = 'MPP/PPP';
    $stdObj->ranking = 1.6;
    $stdObj->context = 'assessment';
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MPP';
    $stdObj->shortvalue = 'MPP';
    $stdObj->ranking = 2;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MPP/MMP';
    $stdObj->shortvalue = 'MPP/MMP';
    $stdObj->ranking = 2.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MMP/MPP';
    $stdObj->shortvalue = 'MMP/MPP';
    $stdObj->ranking = 2.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MMM';
    $stdObj->shortvalue = 'MMM';
    $stdObj->ranking = 3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'MMM/DMM';
    $stdObj->shortvalue = 'MMM/DMM';
    $stdObj->ranking = 3.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DMM/MMM';
    $stdObj->shortvalue = 'DMM/MMM';
    $stdObj->ranking = 3.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DMM';
    $stdObj->shortvalue = 'DMM';
    $stdObj->ranking = 4;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DMM/DDM';
    $stdObj->shortvalue = 'DMM/DDM';
    $stdObj->ranking = 4.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DDM/DMM';
    $stdObj->shortvalue = 'DDM/DMM';
    $stdObj->ranking = 4.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DDM';
    $stdObj->shortvalue = 'DDM';
    $stdObj->ranking = 5;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DDM/DDD';
    $stdObj->shortvalue = 'DDM/DDD';
    $stdObj->ranking = 5.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DDD/DDM';
    $stdObj->shortvalue = 'DDD/DDM';
    $stdObj->ranking = 5.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DDD';
    $stdObj->shortvalue = 'DDD';
    $stdObj->ranking = 6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'DDD/D*DD';
    $stdObj->shortvalue = 'DDD/D*DD';
    $stdObj->ranking = 6.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*DD/DDD';
    $stdObj->shortvalue = 'D*DD/DDD';
    $stdObj->ranking = 6.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*DD';
    $stdObj->shortvalue = 'D*DD';
    $stdObj->ranking = 7;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*DD/D*D*D';
    $stdObj->shortvalue = 'D*DD/D*D*D';
    $stdObj->ranking = 7.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D*D/D*DD';
    $stdObj->shortvalue = 'D*D*D/D*DD';
    $stdObj->ranking = 7.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D*D';
    $stdObj->shortvalue = 'D*D*D';
    $stdObj->ranking = 8;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D*D/D*D*D*';
    $stdObj->shortvalue = 'D*D*D/D*D*D*';
    $stdObj->ranking = 8.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D*D*/D*D*D';
    $stdObj->shortvalue = 'D*D*D*/D*D*D';
    $stdObj->ranking = 8.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D*D*D*';
    $stdObj->shortvalue = 'D*D*D*';
    $stdObj->ranking = 9;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l3EDipID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 180;
    $DB->insert_record('block_bcgt_target_qual_att', $record);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l3EDipID;
    $record->name = BTECSubType::DEFAULTNUMBEROFYEARSNAME;
    $record->value = 2;
    $DB->insert_record('block_bcgt_target_qual_att', $record);
    
    //Level 4 (4), BTEC Higher (3), HNC (7) 
    $record = new stdClass();
    $record->bcgtlevelid = 4;
    $record->bcgttypeid = 3;
    $record->bcgtsubtypeid = 7;
    $record->previoustargetqualid = -1;
    $l4HNCID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l4HNCID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l4HNCID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'P';
    $stdObj->shortvalue = 'P';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'P/M';
    $stdObj->shortvalue = 'P/M';
    $stdObj->ranking = 1.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/P';
    $stdObj->shortvalue = 'M/P';
    $stdObj->ranking = 1.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M';
    $stdObj->shortvalue = 'M';
    $stdObj->ranking = 2;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/D';
    $stdObj->shortvalue = 'M/D';
    $stdObj->ranking = 2.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/M';
    $stdObj->shortvalue = 'D/M';
    $stdObj->ranking = 2.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D';
    $stdObj->shortvalue = 'D';
    $stdObj->ranking = 3;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l4HNCID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 120;
    $DB->insert_record('block_bcgt_target_qual_att', $record);
    
    //Level 5 (5), BTEC Higher (3), HND (8)
    $record = new stdClass();
    $record->bcgtlevelid = 5;
    $record->bcgttypeid = 3;
    $record->bcgtsubtypeid = 8;
    $record->previoustargetqualid = -1;
    $l5HNDID = $DB->insert_record('block_bcgt_target_qual', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l5HNDID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $l5HNDID;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'P';
    $stdObj->shortvalue = 'P';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'P/M';
    $stdObj->shortvalue = 'P/M';
    $stdObj->ranking = 1.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/P';
    $stdObj->shortvalue = 'M/P';
    $stdObj->ranking = 1.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M';
    $stdObj->shortvalue = 'M';
    $stdObj->ranking = 2;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'M/D';
    $stdObj->shortvalue = 'M/D';
    $stdObj->ranking = 2.3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D/M';
    $stdObj->shortvalue = 'D/M';
    $stdObj->ranking = 2.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj->value = 'D';
    $stdObj->shortvalue = 'D';
    $stdObj->ranking = 3;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l5HNDID;
    $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 240;
    $DB->insert_record('block_bcgt_target_qual_att', $record);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l5HNDID;
    $record->name = BTECSubType::DEFAULTNUMBEROFYEARSNAME;
    $record->value = 2;
    $DB->insert_record('block_bcgt_target_qual_att', $record);
    
    /*1.BTECFirst2013,Level 1 & 2,Award default credits = 120 
         * level = 7, type = 12. Award = 6*/
        $record = new stdClass();
        $record->bcgtlevelid = 7;
        $record->bcgttypeid = 12;
        $record->bcgtsubtypeid = 6;
        $record->previoustargetqualid = -1;
        $First2013l12Award = $DB->insert_record('block_bcgt_target_qual', $record);
    
        $record = new stdClass();
        $record->bcgttargetqualid = $l2AwardID;
        $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
        $record->value = 120;
        $DB->insert_record('block_bcgt_target_qual_att', $record); 
        
        /*2.BTECFirst2013, Level 1 & 2, Certificate, default credits = 240
         * level = 7, type = 12. Certificate = 5*/
        $record = new stdClass();
        $record->bcgtlevelid = 7;
        $record->bcgttypeid = 12;
        $record->bcgtsubtypeid = 5;
        $record->previoustargetqualid = -1;
        $First2013l12Cert = $DB->insert_record('block_bcgt_target_qual', $record);
    
        $record = new stdClass();
        $record->bcgttargetqualid = $l2CertID;
        $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
        $record->value = 240;
        $DB->insert_record('block_bcgt_target_qual_att', $record); 
        
        /*3.BTECFirs2013, Level 1 & 2, Extended Certificate, default credits = 360
         * level = 7, type = 12. Extended Certificate = 11*/
        $record = new stdClass();
        $record->bcgtlevelid = 7;
        $record->bcgttypeid = 12;
        $record->bcgtsubtypeid = 11;
        $record->previoustargetqualid = -1;
        $First2013l12ExtCert = $DB->insert_record('block_bcgt_target_qual', $record);
    
        $record = new stdClass();
        $record->bcgttargetqualid = $l2ExCertID;
        $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
        $record->value = 360;
        $DB->insert_record('block_bcgt_target_qual_att', $record); 
        //DEFAULT NUMBER OF CREDITS
        
        /*4.BTECFirst2013, Level 1 & 2, Diploma, default credits = 480
            * level = 7, type = 12. Diploma = 3*/
        $record = new stdClass();
        $record->bcgtlevelid = 7;
        $record->bcgttypeid = 12;
        $record->bcgtsubtypeid = 3;
        $record->previoustargetqualid = -1;
        $First2013l12Dip = $DB->insert_record('block_bcgt_target_qual', $record);
    
        $record = new stdClass();
        $record->bcgttargetqualid = $l2DiplomaID;
        $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
        $record->value = 480;
        $DB->insert_record('block_bcgt_target_qual_att', $record); 
        //DEFAULT NUMBER OF CREDITS
        
    //Defaults for the unit levels
    //
    $record = new stdClass();
    $record->bcgtlevelid = 1;
    $record->name = BTECUNIT::DEFAULTUNITCREDITSNAME;
    $record->bcgttypefamilyid = 2;
    $record->value = 3;
    $DB->insert_record('block_bcgt_unit_type_att', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 2;
    $record->name = BTECUNIT::DEFAULTUNITCREDITSNAME;
    $record->bcgttypefamilyid = 2;
    $record->value = 5;
    $DB->insert_record('block_bcgt_unit_type_att', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 3;
    $record->name = BTECUNIT::DEFAULTUNITCREDITSNAME;
    $record->bcgttypefamilyid = 2;
    $record->value = 10;
    $DB->insert_record('block_bcgt_unit_type_att', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 4;
    $record->name = BTECUNIT::DEFAULTUNITCREDITSNAME;
    $record->bcgttypefamilyid = 2;
    $record->value = 15;
    $DB->insert_record('block_bcgt_unit_type_att', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 5;
    $record->name = BTECUNIT::DEFAULTUNITCREDITSNAME;
    $record->bcgttypefamilyid = 2;
    $record->value = 15;
    $DB->insert_record('block_bcgt_unit_type_att', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 7;
    $record->name = BTECUNIT::DEFAULTUNITCREDITSNAME;
    $record->bcgttypefamilyid = 2;
    $record->value = 30;
    $DB->insert_record('block_bcgt_unit_type_att', $record);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Not Achieved';
    $stdObj->shortvalue = 'N/A';
    $stdObj->ranking = 0;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'U';
    $stdObj->shortvalue = 'U';
    $stdObj->ranking = 1;
    $DB->insert_record('block_bcgt_value', $stdObj);
        
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'U/Level 1';
    $stdObj->shortvalue = 'U/L1';
    $stdObj->ranking = 1.3;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 1/U';
    $stdObj->shortvalue = 'L1/U';
    $stdObj->ranking = 1.6;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 1';
    $stdObj->shortvalue = 'L1';
    $stdObj->ranking = 2;
    $DB->insert_record('block_bcgt_value', $stdObj);
      
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 1/Level 2 Pass';
    $stdObj->shortvalue = 'L1/L2 P';
    $stdObj->ranking = 2.3;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 2 Pass/Level 1';
    $stdObj->shortvalue = 'L2 P/L1';
    $stdObj->ranking = 2.6;
    $DB->insert_record('block_bcgt_value', $stdObj);
        
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 2 Pass';
    $stdObj->shortvalue = 'L2 P';
    $stdObj->ranking = 3;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 2 Pass/Level 2 Merit';
    $stdObj->shortvalue = 'L2 P/L2 M';
    $stdObj->ranking = 3.3;
    $DB->insert_record('block_bcgt_value', $stdObj);
        
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 2 Merit/Level 2 Pass';
    $stdObj->shortvalue = 'L2 M/L2 P';
    $stdObj->ranking = 3.6;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 2 Merit';
    $stdObj->shortvalue = 'L2 M';
    $stdObj->ranking = 4;
    $DB->insert_record('block_bcgt_value', $stdObj);   
        
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 2 Merit/Level 2 Distinction';
    $stdObj->shortvalue = 'L2 M/L2 D';
    $stdObj->ranking = 4.3;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 2 Distinction/Level 2 Merit';
    $stdObj->shortvalue = 'L2 D/L2 M';
    $stdObj->ranking = 4.6;
    $DB->insert_record('block_bcgt_value', $stdObj);
       
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 2 Distinction';
    $stdObj->shortvalue = 'L2 D';
    $stdObj->ranking = 5;
    $DB->insert_record('block_bcgt_value', $stdObj);

    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 2 Distinction/Level 2 Distinction *';
    $stdObj->shortvalue = 'L2 D/L2 D*';
    $stdObj->ranking = 5.3;
    $DB->insert_record('block_bcgt_value', $stdObj);    
        
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 2 Distinction */Level 2 Distinction';
    $stdObj->shortvalue = 'L2 D*/L2 D';
    $stdObj->ranking = 5.6;
    $DB->insert_record('block_bcgt_value', $stdObj);
    
    $stdObj = new stdClass();
    $stdObj->bcgttargetqualid = $First2013l12Award;      
    $stdObj->context = 'assessment';
    $stdObj->bcgttypeid = -1;
    $stdObj->value = 'Level 2 Distinction *';
    $stdObj->shortvalue = 'L2 D*';
    $stdObj->ranking = 6;
    $DB->insert_record('block_bcgt_value', $stdObj);
        
    $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttargetqualid = ? AND context = ?";
    $records = $DB->get_records_sql($sql, array($First2013l12Award, 'assessment'));
    if($records)
    {
        foreach($records AS $record)
        {
            $record->bcgttargetqualid = $First2013l12Cert;
            $DB->insert_record('block_bcgt_value', $record);
        }
    }
    
    $records = $DB->get_records_sql($sql, array($First2013l12Award, 'assessment'));
    if($records)
    {
        foreach($records AS $record)
        {
            $record->bcgttargetqualid = $First2013l12ExtCert;
            $DB->insert_record('block_bcgt_value', $record);
        }
    }
    
    $records = $DB->get_records_sql($sql, array($First2013l12Award, 'assessment'));
    if($records)
    {
        foreach($records AS $record)
        {
            $record->bcgttargetqualid = $First2013l12Dip;
            $DB->insert_record('block_bcgt_value', $record);
        }
    }   
    
    //
    
    // ---------------------- The Target Quals Grades ---------------------------
    //Level 2 Award $l2AwardID
    //DOESNT HAVE ANY???????
    
    //level 1 Award 
    $record = new stdClass();
    $record->bcgttargetqualid = $l1AwardID;      
    $record->targetgrade = 'Pass';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 0;
    $record->ranking = 1;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l1AwardID;      
    $record->targetgrade = 'Not Achieved';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 0;
    $record->ranking = 0;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    //level 1 Certificate
    $record = new stdClass();
    $record->bcgttargetqualid = $l1CertificateID;      
    $record->targetgrade = 'Pass';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 0;
    $record->ranking = 1;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l1CertificateID;      
    $record->targetgrade = 'Not Achieved';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 0;
    $record->ranking = 0;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    //Level 1 Diploma
    $record = new stdClass();
    $record->bcgttargetqualid = $l1DipID;      
    $record->targetgrade = 'Pass';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 0;
    $record->ranking = 1;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record = new stdClass();
    $record->bcgttargetqualid = $l1DipID;      
    $record->targetgrade = 'Not Achieved';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 0;
    $record->ranking = 0;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    
    //Level 2 Certificate $l2CertID
    $record = new stdClass();
    $record->bcgttargetqualid = $l2CertID;      
    $record->targetgrade = 'P';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 84;
    $record->ranking = 1;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    //insert this as a target breeakdown.
    $record->targetgrade = 'P/M';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/P';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
          
    $record->targetgrade = 'M';
    $record->unitsscorelower = 85;
    $record->unitsscoreupper = 94;
    $record->ranking = 2;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/D';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D/M';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
         
    $record->targetgrade = 'D';
    $record->unitsscorelower = 95;
    $record->unitsscoreupper = 99;
    $record->ranking = 3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D/D*';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*/D';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
         
    $record->targetgrade = 'D*';
    $record->unitsscorelower = 100;
    $record->unitsscoreupper = 400;
    $record->ranking = 4;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    //Level 2 Extended Certificate $l2ExCertID
    $record = new stdClass();
    $record->bcgttargetqualid = $l2ExCertID;      
    $record->targetgrade = 'P';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 169;
    $record->ranking = 1;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'P/M';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/P';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
           
    $record->targetgrade = 'M';
    $record->unitsscorelower = 170;
    $record->unitsscoreupper = 189;
    $record->ranking = 2;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/D';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D/M';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
         
    $record->targetgrade = 'D';
    $record->unitsscorelower = 190;
    $record->unitsscoreupper = 199;
    $record->ranking = 3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D/D*';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*/D';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
          
    $record->targetgrade = 'D*';
    $record->unitsscorelower = 200;
    $record->unitsscoreupper = 600;
    $record->ranking = 4;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    //Level 2 Diploma $l2DiplomaID
    $record = new stdClass();
    $record->bcgttargetqualid = $l2DiplomaID;       
    $record->targetgrade = 'P';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 339;
    $record->ranking = 1;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'P/M';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/P';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
          
    $record->targetgrade = 'M';
    $record->unitsscorelower = 340;
    $record->unitsscoreupper = 379;
    $record->ranking = 2;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/D';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D/M';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
          
    $record->targetgrade = 'D';
    $record->unitsscorelower = 380;
    $record->unitsscoreupper = 399;
    $record->ranking = 3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D/D*';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*/D';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
           
    $record->targetgrade = 'D*';
    $record->unitsscorelower = 400;
    $record->unitsscoreupper = 999;
    $record->ranking = 4;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    //Level 3 Foundation Diploma $l3FoundDiplomaID
    $record = new stdClass();
    $record->bcgttargetqualid = $l3FoundDiplomaID;       
    $record->targetgrade = 'P';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 0;
    $record->ranking = 1;
    $record->ucaspoints = 165;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'P/M';
    $record->ucaspoints = 185;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/P';
    $record->ucaspoints = 205;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
          
    $record->targetgrade = 'M';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 0;
    $record->ranking = 2;
    $record->ucaspoints = 225;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/D';
    $record->ucaspoints = 245;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D/M';
    $record->ucaspoints = 265;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
          
    $record->targetgrade = 'D';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 0;
    $record->ranking = 3;
    $record->ucaspoints = 285;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    //Level 3 Certificate $l3CertID
    $record = new stdClass();
    $record->bcgttargetqualid = $l3CertID;        
    $record->targetgrade = 'P';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 229;
    $record->ranking = 1;
    $record->ucaspoints = 20;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    //insert this as a target breeakdown.
    $record->targetgrade = 'P/M';
    $record->ucaspoints = 26.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/P';
    $record->ucaspoints = 33.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->gcseupperscore = 0;
    $record->gcselowerscore = 0;        
    $record->targetgrade = 'M';
    $record->unitsscorelower = 230;
    $record->unitsscoreupper = 249;
    $record->ranking = 2;
    $record->ucaspoints = 40;
    $record->entryscoreupper = 35.8;
    $record->entryscorelower = 0;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/D';
    $record->ucaspoints = 46.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D/M';
    $record->ucaspoints = 53.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.6;
    $record->entryscoreupper = 41.2;
    $record->entryscorelower = 35.8;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
        
    $record->targetgrade = 'D';
    $record->unitsscorelower = 250;
    $record->unitsscoreupper = 259;
    $record->ranking = 3;
    $record->ucaspoints = 60;
    $record->entryscoreupper = 46.6;
    $record->entryscorelower = 41.2;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D/D*';
    $record->ucaspoints = 63.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*/D';
    $record->ucaspoints = 66.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.6;
    $record->entryscoreupper = 48.4;
    $record->entryscorelower = 46.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
          
    $record->targetgrade = 'D*';
    $record->unitsscorelower = 260;
    $record->unitsscoreupper = 600;
    $record->ranking = 4;
    $record->ucaspoints = 70;
    $record->entryscoreupper = 58.0;
    $record->entryscorelower = 48.4;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    //Level 3 90 Credit Diploma $l390CredID
    $record = new stdClass();
    $record->bcgttargetqualid = $l390CredID;       
    $record->targetgrade = 'PP';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 659;
    $record->ranking = 1;
    $record->ucaspoints = 60;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'PP/MP';
    $record->ucaspoints = 73.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MP/PP';
    $record->ucaspoints = 86.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
          
    $record->targetgrade = 'MP';
    $record->unitsscorelower = 660;
    $record->unitsscoreupper = 689;
    $record->ranking = 2;
    $record->ucaspoints = 100;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MP/MM';
    $record->ucaspoints = 106.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MM/MP';
    $record->ucaspoints = 113.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
         
    $record->targetgrade = 'MM';
    $record->unitsscorelower = 690;
    $record->unitsscoreupper = 719;
    $record->ranking = 3;
    $record->ucaspoints = 120;
    $record->entryscoreupper = 34.0;
    $record->entryscorelower = 0;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MM/DM';
    $record->ucaspoints = 133.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DM/MM';
    $record->ucaspoints = 146.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.6;
    $record->entryscoreupper = 35.8;
    $record->entryscorelower = 34.0;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
           
    $record->targetgrade = 'DM';
    $record->unitsscorelower = 720;
    $record->unitsscoreupper = 749;
    $record->ranking = 4;
    $record->ucaspoints = 160;
    $record->entryscoreupper = 41.2;
    $record->entryscorelower = 35.8;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DM/DD';
    $record->ucaspoints = 166.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 4.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DD/DM';
    $record->ucaspoints = 173.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 4.6;
    $record->entryscoreupper = 43.0;
    $record->entryscorelower = 41.2;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
        
    $record->targetgrade = 'DD';
    $record->unitsscorelower = 750;
    $record->unitsscoreupper = 769;
    $record->ranking = 5;
    $record->ucaspoints = 180;
    $record->entryscoreupper = 46.6;
    $record->entryscorelower = 43.0;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DD/D*D';
    $record->ucaspoints = 186.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 5.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*D/DD';
    $record->ucaspoints = 193.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 5.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
           
    $record->targetgrade = 'D*D';
    $record->unitsscorelower = 770;
    $record->unitsscoreupper = 789;
    $record->ranking = 6;
    $record->ucaspoints = 200;
    $record->entryscoreupper = 50.2;
    $record->entryscorelower = 48.4;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*D/D*D*';
    $record->ucaspoints = 203.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 6.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*D*/D*D';
    $record->ucaspoints = 206.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 6.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
           
    $record->targetgrade = 'D*D*';
    $record->unitsscorelower = 790;
    $record->unitsscoreupper = 1500;
    $record->ranking = 7;
    $record->ucaspoints = 210;
    $record->entryscoreupper = 58.0;
    $record->entryscorelower = 50.2;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
         
    //Level 3 Subsidiary Diploma $l3SubID
    $record = new stdClass();
    $record->bcgttargetqualid = $l3SubID;       
    $record->targetgrade = 'P';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 459;
    $record->ranking = 1;
    $record->ucaspoints = 40;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'P/M';
    $record->ucaspoints = 53.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/P';
    $record->ucaspoints = 66.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
        
    $record->targetgrade = 'M';
    $record->unitsscorelower = 460;
    $record->unitsscoreupper = 499;
    $record->ranking = 2;
    $record->ucaspoints = 80;
    $record->entryscoreupper = 35.8;
    $record->entryscorelower = 0;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/D';
    $record->ucaspoints = 93.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D/M';
    $record->ucaspoints = 106.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.6;
    $record->entryscoreupper = 41.2;
    $record->entryscorelower = 35.8;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
           
    $record->targetgrade = 'D';
    $record->unitsscorelower = 500;
    $record->unitsscoreupper = 519;
    $record->ranking = 3;
    $record->ucaspoints = 120;
    $record->entryscoreupper = 46.6;
    $record->entryscorelower = 41.2;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D/D*';
    $record->ucaspoints = 126.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*/D';
    $record->ucaspoints = 133.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.6;
    $record->entryscoreupper = 48.4;
    $record->entryscorelower = 46.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
          
    $record->targetgrade = 'D*';
    $record->unitsscorelower = 520;
    $record->unitsscoreupper = 1000;
    $record->ranking = 4;
    $record->ucaspoints = 140;
    $record->entryscoreupper = 58.0;
    $record->entryscorelower = 48.4;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
          
    
    //Level 3 Diploma $l3DipID
    $record = new stdClass();
    $record->bcgttargetqualid = $l3DipID;       
    $record->targetgrade = 'PP';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 879;
    $record->ranking = 1;
    $record->ucaspoints = 80;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'PP/MP';
    $record->ucaspoints = 93.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MP/PP';
    $record->ucaspoints = 106.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
           
    $record->targetgrade = 'MP';
    $record->unitsscorelower = 880;
    $record->unitsscoreupper = 919;
    $record->ranking = 2;
    $record->ucaspoints = 120;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MP/MM';
    $record->ucaspoints = 133.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MM/MP';
    $record->ucaspoints = 146.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
           
    $record->targetgrade = 'MM';
    $record->unitsscorelower = 920;
    $record->unitsscoreupper = 959;
    $record->ranking = 3;
    $record->ucaspoints = 160;
    $record->entryscoreupper = 34.0;
    $record->entryscorelower = 0;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MM/DM';
    $record->ucaspoints = 173.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DM/MM';
    $record->ucaspoints = 186.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.6;
    $record->entryscoreupper = 35.8;
    $record->entryscorelower = 34.0;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
           
    $record->targetgrade = 'DM';
    $record->unitsscorelower = 960;
    $record->unitsscoreupper = 999;
    $record->ranking = 4;
    $record->ucaspoints = 200;
    $record->entryscoreupper = 41.2;
    $record->entryscorelower = 35.8;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DM/DD';
    $record->ucaspoints = 213.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 4.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DD/DM';
    $record->ucaspoints = 226.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 4.6;
    $record->entryscoreupper = 43.0;
    $record->entryscorelower = 41.2;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
           
    $record->targetgrade = 'DD';
    $record->unitsscorelower = 1000;
    $record->unitsscoreupper = 1029;
    $record->ranking = 5;
    $record->ucaspoints = 240;
    $record->entryscoreupper = 46.6;
    $record->entryscorelower = 43.0;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DD/D*D';
    $record->ucaspoints = 246.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 5.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*D/DD';
    $record->ucaspoints = 253.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 5.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
          
    $record->targetgrade = 'D*D';
    $record->unitsscorelower = 1030;
    $record->unitsscoreupper = 1059;
    $record->ranking = 6;
    $record->ucaspoints = 260;
    $record->entryscoreupper = 50.2;
    $record->entryscorelower = 46.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*D/D*D*';
    $record->ucaspoints = 266.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 6.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*D*/D*D';
    $record->ucaspoints = 273.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 6.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
           
    $record->targetgrade = 'D*D*';
    $record->unitsscorelower = 1060;
    $record->unitsscoreupper = 2000;
    $record->ranking = 7;
    $record->ucaspoints = 280;
    $record->entryscoreupper = 58.0;
    $record->entryscorelower = 50.2;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    //Level 3 Extended DIploma $l3EDipID
    $record = new stdClass();
    $record->bcgttargetqualid = $l3EDipID;       
    $record->targetgrade = 'PPP';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 1299;
    $record->ranking = 1;
    $record->ucaspoints = 120;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'PPP/MPP';
    $record->ucaspoints = 133.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MPP/PPP';
    $record->ucaspoints = 146.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
   
    $record->targetgrade = 'MPP';
    $record->unitsscorelower = 1300;
    $record->unitsscoreupper = 1339;
    $record->ranking = 2;
    $record->ucaspoints = 160;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MPP/MMP';
    $record->ucaspoints = 173.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MMP/MPP';
    $record->ucaspoints = 186.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MMP';
    $record->unitsscorelower = 1340;
    $record->unitsscoreupper = 1379;
    $record->ranking = 3;
    $record->ucaspoints = 200;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MMP/MMM';
    $record->ucaspoints = 213.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MMM/MMP';
    $record->ucaspoints = 226.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 3.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MMM';
    $record->unitsscorelower = 1380;
    $record->unitsscoreupper = 1419;
    $record->ranking = 4;
    $record->ucaspoints = 240;
    $record->entryscoreupper = 34.0;
    $record->entryscorelower = 0;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'MMM/DMM';
    $record->ucaspoints = 253.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 4.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DMM/MMM';
    $record->ucaspoints = 266.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 4.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DMM';
    $record->unitsscorelower = 1420;
    $record->unitsscoreupper = 1459;
    $record->ranking = 5;
    $record->ucaspoints = 280;
    $record->entryscoreupper = 38.2;
    $record->entryscorelower = 34.0;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DMM/DDM';
    $record->ucaspoints = 293.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 5.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DDM/DMM';
    $record->ucaspoints = 306.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 5.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DDM';
    $record->unitsscorelower = 1460;
    $record->unitsscoreupper = 1499;
    $record->ranking = 6;
    $record->ucaspoints = 320;
    $record->entryscoreupper = 44.8;
    $record->entryscorelower = 38.2;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DDM/DDD';
    $record->ucaspoints = 333.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 6.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DDD/DDM';
    $record->ucaspoints = 346.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 6.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DDD';
    $record->unitsscorelower = 1500;
    $record->unitsscoreupper = 1529;
    $record->ranking = 7;
    $record->ucaspoints = 360;
    $record->entryscoreupper = 44.6;
    $record->entryscorelower = 44.8;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'DDD/D*DD';
    $record->ucaspoints = 366.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 7.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*DD/DDD';
    $record->ucaspoints = 373.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 7.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*DD';
    $record->unitsscorelower = 1530;
    $record->unitsscoreupper = 1559;
    $record->ranking = 8;
    $record->ucaspoints = 380;
    $record->entryscoreupper = 50.2;
    $record->entryscorelower = 46.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*DD/D*D*D';
    $record->ucaspoints = 386.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 8.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*D*D/D*DD';
    $record->ucaspoints = 393.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 8.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*D*D';
    $record->unitsscorelower = 1560;
    $record->unitsscoreupper = 1589;
    $record->ranking = 9;
    $record->ucaspoints = 400;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*D*D/D*D*D*';
    $record->ucaspoints = 406.6;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 9.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*D*D*/D*D*D';
    $record->ucaspoints = 413.3;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 9.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D*D*D*';
    $record->unitsscorelower = 1590;
    $record->unitsscoreupper = 2500;
    $record->ranking = 10;
    $record->ucaspoints = 420;
    $record->entryscoreupper = 58.0;
    $record->entryscorelower = 50.2;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    //Level 4 HNC $l4HNCID
    $record = new stdClass();
    $record->bcgttargetqualid = $l4HNCID;       
    $record->targetgrade = 'P';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 74;
    $record->ranking = 1;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'P/M';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/P';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M';
    $record->unitsscorelower = 75;
    $record->unitsscoreupper = 149;
    $record->ranking = 2;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/D';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D/M';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
   
    $record->targetgrade = 'D';
    $record->unitsscorelower = 150;
    $record->unitsscoreupper = 500;
    $record->ranking = 3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    //level 5 HND $l5HNDID
    $record = new stdClass();
    $record->bcgttargetqualid = $l5HNDID;       
    $record->targetgrade = 'P';
    $record->unitsscorelower = 0;
    $record->unitsscoreupper = 74;
    $record->ranking = 1;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'P/M';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/P';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 1.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
       
    $record->targetgrade = 'M';
    $record->unitsscorelower = 75;
    $record->unitsscoreupper = 149;
    $record->ranking = 2;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'M/D';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    $record->targetgrade = 'D/M';
    $record->ucaspoints = 0;
    $record->unitscorelower = -1;
    $record->unitsscoreupper = -1;
    $record->ranking = 2.6;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
   
    $record->targetgrade = 'D';
    $record->unitsscorelower = 150;
    $record->unitsscoreupper = 500;
    $record->ranking = 3;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
    
    //The breakdowns: for the BTEC FIRST 2013
        //Award:
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'U';
        $record->unitsscorelower = 0;
        $record->unitsscoreupper = 24;
        $record->ranking = 1;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'U/Level 1';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 1/U';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 1';
        $record->unitsscorelower = 24;
        $record->unitsscoreupper = 48;
        $record->ranking = 2;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 1/Level 2 Pass';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 2 Pass/Level 1';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 2 Pass';
        $record->unitsscorelower = 48;
        $record->unitsscoreupper = 66;
        $record->ranking = 3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 2 Pass/Level 2 Merit';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 2 Merit/Level 2 Pass';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 2 Merit';
        $record->unitsscorelower = 66;
        $record->unitsscoreupper = 84;
        $record->ranking = 4;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 2 Merit/Level 2 Distinction';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 2 Distinction/Level 2 Merit';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 2 Distinction';
        $record->unitsscorelower = 84;
        $record->unitsscoreupper = 90;
        $record->ranking = 5;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 2 Distinction/Level 2 Distinction *';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 2 Distinction */Level 2 Distinction';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Award;      
        $record->targetgrade = 'Level 2 Distinction *';
        $record->unitsscorelower = 90;
        $record->unitsscoreupper = 200;
        $record->ranking = 6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        //Certificate
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'U';
        $record->unitsscorelower = 0;
        $record->unitsscoreupper = 48;
        $record->ranking = 1;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'U/Level 1';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 1/U';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 1';
        $record->unitsscorelower = 48;
        $record->unitsscoreupper = 96;
        $record->ranking = 2;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 1/Level 2 PP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 PP/Level 1';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 PP';
        $record->unitsscorelower = 96;
        $record->unitsscoreupper = 114;
        $record->ranking = 3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 PP/Level 2 MP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 MP/Level 2 PP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 MP';
        $record->unitsscorelower = 114;
        $record->unitsscoreupper = 132;
        $record->ranking = 4;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 MP/Level 2 MM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 MM/Level 2 MP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 MM';
        $record->unitsscorelower = 132;
        $record->unitsscoreupper = 150;
        $record->ranking = 5;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 MM/Level 2 DM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 DM/Level 2 MM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 DM';
        $record->unitsscorelower = 150;
        $record->unitsscoreupper = 168;
        $record->ranking = 6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 DM/Level 2 DD';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 6.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 DD/Level 2 DM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 6.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 DD';
        $record->unitsscorelower = 168;
        $record->unitsscoreupper = 174;
        $record->ranking = 7;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 DD/Level 2 D*D';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 7.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 D*D/Level 2 DD';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 7.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 D*D';
        $record->unitsscorelower = 174;
        $record->unitsscoreupper = 180;
        $record->ranking = 8;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 D*D/Level 2 D*D*';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 8.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 D*D*/Level 2 D*D';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 8.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Cert;      
        $record->targetgrade = 'Level 2 D*D*';
        $record->unitsscorelower = 180;
        $record->unitsscoreupper = 300;
        $record->ranking = 9;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        //Ext Certificate
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'U';
        $record->unitsscorelower = 0;
        $record->unitsscoreupper = 72;
        $record->ranking = 1;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'U/Level 1';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 1/U';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 1';
        $record->unitsscorelower = 72;
        $record->unitsscoreupper = 144;
        $record->ranking = 2;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 1/Level 2 PP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 PP/Level 1';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 PP';
        $record->unitsscorelower = 144;
        $record->unitsscoreupper = 174;
        $record->ranking = 3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 PP/Level 2 MP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 MP/Level 2 PP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 MP';
        $record->unitsscorelower = 174;
        $record->unitsscoreupper = 204;
        $record->ranking = 4;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 MP/Level 2 MM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 MM/Level 2 MP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 MM';
        $record->unitsscorelower = 204;
        $record->unitsscoreupper = 234;
        $record->ranking = 5;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 MM/Level 2 DM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 DM/Level 2 MM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 DM';
        $record->unitsscorelower = 234;
        $record->unitsscoreupper = 264;
        $record->ranking = 6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 DM/Level 2 DD';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 6.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 DD/Level 2 DM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 6.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 DD';
        $record->unitsscorelower = 264;
        $record->unitsscoreupper = 270;
        $record->ranking = 7;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 DD/Level 2 D*D';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 7.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 D*D/Level 2 DD';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 7.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 D*D';
        $record->unitsscorelower = 270;
        $record->unitsscoreupper = 276;
        $record->ranking = 8;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 D*D/Level 2 D*D*';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 8.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 D*D*/Level 2 D*D';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 8.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12ExtCert;      
        $record->targetgrade = 'Level 2 D*D*';
        $record->unitsscorelower = 276;
        $record->unitsscoreupper = 500;
        $record->ranking = 9;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        //Diploma
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'U';
        $record->unitsscorelower = 0;
        $record->unitsscoreupper = 96;
        $record->ranking = 1;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'U/Level 1';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 1/U';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 1';
        $record->unitsscorelower = 96;
        $record->unitsscoreupper = 192;
        $record->ranking = 2;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 1/Level 2 PP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 PP/Level 1';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 PP';
        $record->unitsscorelower = 192;
        $record->unitsscoreupper = 234;
        $record->ranking = 3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 PP/Level 2 MP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 MP/Level 2 PP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 MP';
        $record->unitsscorelower = 234;
        $record->unitsscoreupper = 276;
        $record->ranking = 4;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 MP/Level 2 MM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 MM/Level 2 MP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 MM';
        $record->unitsscorelower = 276;
        $record->unitsscoreupper = 318;
        $record->ranking = 5;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 MM/Level 2 DM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 DM/Level 2 MM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 DM';
        $record->unitsscorelower = 318;
        $record->unitsscoreupper = 360;
        $record->ranking = 6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 DM/Level 2 DD';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 6.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 DD/Level 2 DM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 6.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 DD';
        $record->unitsscorelower = 360;
        $record->unitsscoreupper = 366;
        $record->ranking = 7;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 DD/Level 2 D*D';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 7.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 D*D/Level 2 DD';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 7.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 D*D';
        $record->unitsscorelower = 366;
        $record->unitsscoreupper = 372;
        $record->ranking = 8;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 D*D/Level 2 D*D*';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 8.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 D*D*/Level 2 D*D';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 8.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $First2013l12Dip;      
        $record->targetgrade = 'Level 2 D*D*';
        $record->unitsscorelower = 372;
        $record->unitsscoreupper = 700;
        $record->ranking = 9;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
    
//    //THE TAB
//    $record = new stdClass();
//    $record->tabname = 'BTEC';
//    $record->component = 'BTEC';
//    $record->tabclassfile = '/mod/bcgtbtec/classes/BTECDashTab.class.php';
//    $DB->insert_record('block_bcgt_tabs', $record);
    
    
    //find all of the breakdowns for the targetquals that are for the btec families. 
        $sql = "SELECT distinct(breakdown.id), breakdown.* FROM {block_bcgt_target_breakdown} breakdown 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = breakdown.bcgttargetqualid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
            WHERE family = ?";
        $records = $DB->get_records_sql($sql, array('btec'));
        if($records)
        {
            foreach($records AS $record)
            {
                $stdObj = new stdClass();
                $stdObj->bcgttargetqualid = $record->bcgttargetqualid;
                $stdObj->grade = $record->targetgrade;
                $ucasPoints = $record->ucaspoints;
                if(!isset($record->ucaspoints) || !$record->ucaspoints)
                {
                    $ucasPoints = 0;
                }
                $stdObj->ucaspoints = $ucasPoints;
                $stdObj->ranking = $record->ranking;
                $stdObj->upperscore = $record->entryscoreupper;
                $stdObj->lowerscore = $record->entryscorelower;
                $DB->insert_record('block_bcgt_target_grades', $stdObj);
            }
        }
    
    //check config for prior learning and if need be go and run thise points
    //import
    echo "Finished Installing basic data, inserting Quals, Units and Criteria<br />";
    global $CFG;
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/lib.php');
    run_btec_initial_import();
    return true;
}
?>
