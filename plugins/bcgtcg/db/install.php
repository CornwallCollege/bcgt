<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
function xmldb_bcgtcg_install()
{
    global $DB, $CFG;
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGSubType.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGQualification.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGUnit.class.php');
    // ---------------------- The Family ---------------------------
//    $family = new stdClass();
//    $family->id = 4;
//    $family->family = 'CG';
//    $family->classfolderlocation = '/blocks/bcgt/plugins/bcgtcg/classes';
//    $family->pluginname = 'bcgtcg';
//    $DB->insert_record_raw('block_bcgt_type_family', $family, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_type_family} (id,family,classfolderlocation,pluginname) 
        VALUES (4,'CG','/blocks/bcgt/plugins/bcgtcg/classes','bcgtcg')");
    
    // Pathways - Depts
//    $record = new stdClass();
//    $record->id = 1;
//    $record->pathway = 'General'; # General CG that don't need specific classes
//    $record->bcgttypefamilyid = $family->id;
//    $DB->insert_record_raw('block_bcgt_pathway_dep', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_pathway_dep} (id,pathway,bcgttypefamilyid) 
        VALUES (1,'General', 4)");
    
//    $record = new stdClass();
//    $record->id = 2;
//    $record->pathway = 'Hair & Beauty';
//    $record->bcgttypefamilyid = $family->id;
//    $DB->insert_record_raw('block_bcgt_pathway_dep', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_pathway_dep} (id,pathway,bcgttypefamilyid) 
        VALUES (2,'Hair & Beauty', 4)");
        
    
//    // Pathway - Types
//    $record = new stdClass();
//    $record->id = 1;
//    $record->pathwaytype = 'VRQ';
//    $DB->insert_record_raw('block_bcgt_pathway_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_pathway_type} (id,pathwaytype) 
        VALUES (1,'VRQ')");
    
//    $record = new stdClass();
//    $record->id = 2;
//    $record->pathwaytype = 'NVQ';
//    $DB->insert_record_raw('block_bcgt_pathway_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_pathway_type} (id,pathwaytype) 
        VALUES (2,'NVQ')");
    
//    $record = new stdClass();
//    $record->id = 3;
//    $record->pathwaytype = 'General';
//    $DB->insert_record_raw('block_bcgt_pathway_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_pathway_type} (id,pathwaytype) 
        VALUES (3,'General')");
    
//    // Pathway - Dept/Types
//    $record = new stdClass();
//    $record->id = 1;
//    $record->bcgtpathwaydepid = 2; #HB
//    $record->bcgtpathwaytypeid = 1; # VRQ
//    $DB->insert_record_raw('block_bcgt_pathway_dep_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_pathway_dep_type} (id,bcgtpathwaydepid,bcgtpathwaytypeid) 
        VALUES (1,2,1)");
    
//    $record = new stdClass();
//    $record->id = 2;
//    $record->bcgtpathwaydepid = 2; #HB
//    $record->bcgtpathwaytypeid = 2; # NVQ
//    $DB->insert_record_raw('block_bcgt_pathway_dep_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_pathway_dep_type} (id,bcgtpathwaydepid,bcgtpathwaytypeid) 
        VALUES (2,2,2)");
    
//    $record = new stdClass();
//    $record->id = 3;
//    $record->bcgtpathwaydepid = 1; #General
//    $record->bcgtpathwaytypeid = 3; # General
//    $DB->insert_record_raw('block_bcgt_pathway_dep_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_pathway_dep_type} (id,bcgtpathwaydepid,bcgtpathwaytypeid) 
        VALUES (3,1,3)");
    
    // Sub types
//    $record = new stdClass();
//    $record->id = 14;
//    $record->subtype = 'VRQ';
//    $record->subtypeshort = 'VRQ';
//    $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
        VALUES (14,'VRQ','VRQ')");
    
//    $record = new stdClass();
//    $record->id = 15;
//    $record->subtype = 'NVQ';
//    $record->subtypeshort = 'NVQ';
//    $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
    $DB->execute("INSERT INTO {block_bcgt_subtype} (id,subtype,subtypeshort) 
        VALUES (15,'NVQ','NVQ')");
    
    
    // Pathway - Subtypes
        // Hair & Beauty (Dip VRQ, Award VRQ, Cert VRQ, Dip NVQ, Cert NVQ)
//        $record = new stdClass();
//        $record->id = 1;
//        $record->bcgtsubtypeid = 3; # Diploma
//        $record->bcgtpathwaydeptypeid = 1; # HB VRQ
//        $DB->insert_record_raw('block_bcgt_pathway_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_pathway_subtype} (id,bcgtsubtypeid,bcgtpathwaydeptypeid) 
        VALUES (1,3,1)");
        
//        $record = new stdClass();
//        $record->id = 2;
//        $record->bcgtsubtypeid = 6; # Award
//        $record->bcgtpathwaydeptypeid = 1; # HB VRQ
//        $DB->insert_record_raw('block_bcgt_pathway_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_pathway_subtype} (id,bcgtsubtypeid,bcgtpathwaydeptypeid) 
        VALUES (2,6,1)");
        
//        $record = new stdClass();
//        $record->id = 3;
//        $record->bcgtsubtypeid = 5; # Cert
//        $record->bcgtpathwaydeptypeid = 1; # HB VRQ
//        $DB->insert_record_raw('block_bcgt_pathway_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_pathway_subtype} (id,bcgtsubtypeid,bcgtpathwaydeptypeid) 
        VALUES (3,5,1)");
        
//        $record = new stdClass();
//        $record->id = 4;
//        $record->bcgtsubtypeid = 3; # Dip
//        $record->bcgtpathwaydeptypeid = 2; # HB NVQ
//        $DB->insert_record_raw('block_bcgt_pathway_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_pathway_subtype} (id,bcgtsubtypeid,bcgtpathwaydeptypeid) 
        VALUES (4,3,2)");
        
//        $record = new stdClass();
//        $record->id = 5;
//        $record->bcgtsubtypeid = 5; # Cert
//        $record->bcgtpathwaydeptypeid = 2; # HB NVQ
//        $DB->insert_record_raw('block_bcgt_pathway_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_pathway_subtype} (id,bcgtsubtypeid,bcgtpathwaydeptypeid) 
        VALUES (5,5,2)");
        
//        // General (Dip, Cert, Award)
//        $record = new stdClass();
//        $record->id = 6;
//        $record->bcgtsubtypeid = 3; # Dip
//        $record->bcgtpathwaydeptypeid = 3; # General
//        $DB->insert_record_raw('block_bcgt_pathway_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_pathway_subtype} (id,bcgtsubtypeid,bcgtpathwaydeptypeid) 
        VALUES (6,3,3)");
        
//        $record = new stdClass();
//        $record->id = 7;
//        $record->bcgtsubtypeid = 5; # Cert
//        $record->bcgtpathwaydeptypeid = 3; # General
//        $DB->insert_record_raw('block_bcgt_pathway_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_pathway_subtype} (id,bcgtsubtypeid,bcgtpathwaydeptypeid) 
        VALUES (7,5,3)");
        
//        $record = new stdClass();
//        $record->id = 8;
//        $record->bcgtsubtypeid = 6; # Award
//        $record->bcgtpathwaydeptypeid = 3; # General
//        $DB->insert_record_raw('block_bcgt_pathway_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_pathway_subtype} (id,bcgtsubtypeid,bcgtpathwaydeptypeid) 
        VALUES (8,6,3)");
        
//        $record = new stdClass();
//        $record->id = 9;
//        $record->bcgtsubtypeid = 14; # VRQ
//        $record->bcgtpathwaydeptypeid = 3; # General
//        $DB->insert_record_raw('block_bcgt_pathway_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_pathway_subtype} (id,bcgtsubtypeid,bcgtpathwaydeptypeid) 
        VALUES (9,14,3)");
        
//        $record = new stdClass();
//        $record->id = 10;
//        $record->bcgtsubtypeid = 15; # NVQ
//        $record->bcgtpathwaydeptypeid = 3; # General
//        $DB->insert_record_raw('block_bcgt_pathway_subtype', $record, false, false, true);
        
        //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_pathway_subtype} (id,bcgtsubtypeid,bcgtpathwaydeptypeid) 
        VALUES (10,15,3)");
    
    // ---------------------- The Types ---------------------------
    //one for each
        
    // General CG
//    $record = new stdClass();
//    $record->id = 9;
//    $record->type = 'CG';
//    $record->bcgttypefamilyid = $family->id;
//    $record->bcgtpathwaydeptid = 1;
//    $record->bcgtpathwaytypeid = 3;
//    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true); 
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_type} (id,type,bcgttypefamilyid,bcgtpathwaydeptid,bcgtpathwaytypeid) 
        VALUES (9,'CG',4,1,3)");
        
//    $record = new stdClass();
//    $record->id = 10;
//    $record->type = 'CG HB VRQ';
//    $record->bcgttypefamilyid = $family->id;
//    $record->bcgtpathwaydeptid = 2;
//    $record->bcgtpathwaytypeid = 1;
//    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true);
    
    //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_type} (id,type,bcgttypefamilyid,bcgtpathwaydeptid,bcgtpathwaytypeid) 
        VALUES (10,'CG HB VRQ',4,2,1)");
    
//    $record = new stdClass();
//    $record->id = 11;
//    $record->type = 'CG HB NVQ';
//    $record->bcgttypefamilyid = $family->id;
//    $record->bcgtpathwaydeptid = 2;
//    $record->bcgtpathwaytypeid = 2;
//    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true);

   //THIS HAS BEEN CHANGED TO THE BELOW DUE TO AN ERROR IN moodle 2.2 core code. THE below should fix this.
        $DB->execute("INSERT INTO {block_bcgt_type} (id,type,bcgttypefamilyid,bcgtpathwaydeptid,bcgtpathwaytypeid) 
        VALUES (11,'CG HB NVQ',4,2,2)");
    
    
    
    
    // ---------------------- The Parent Type Family ---------------------------
//    $record = new stdClass();
//    $record->bcgttypeid = 9;
//    $record->bcgtfamilyid = $family->id;
//    $DB->insert_record('block_bcgt_fam_parent_type', $record);
//    
//    $record = new stdClass();
//    $record->bcgttypeid = 10;
//    $record->bcgtfamilyid = $family->id;
//    $DB->insert_record('block_bcgt_fam_parent_type', $record);
//    
//    $record = new stdClass();
//    $record->bcgttypeid = 11;
//    $record->bcgtfamilyid = $family->id;
//    $DB->insert_record('block_bcgt_fam_parent_type', $record);
//    
//    $record = new stdClass();
//    $record->bcgttypeid = 12;
//    $record->bcgtfamilyid = $family->id;
//    $DB->insert_record('block_bcgt_fam_parent_type', $record);
//    
//    $record = new stdClass();
//    $record->bcgttypeid = 13;
//    $record->bcgtfamilyid = $family->id;
//    $DB->insert_record('block_bcgt_fam_parent_type', $record);
//    
//    $record = new stdClass();
//    $record->bcgttypeid = 14;
//    $record->bcgtfamilyid = $family->id;
//    $DB->insert_record('block_bcgt_fam_parent_type', $record);
//    
//    $record = new stdClass();
//    $record->bcgttypeid = 15;
//    $record->bcgtfamilyid = $family->id;
//    $DB->insert_record('block_bcgt_fam_parent_type', $record);
    
    
    
        
   
    // ---------------------- The Values for the grids (9-15) ---------------------------
    
    
     // General CG
            
            // Met
            $record = new stdClass();
            $record->value = 'Pass';
            $record->shortvalue = 'P';
            $record->bcgttypeid = 9;
            $record->specialval = 'A';
            $record->ranking = 1;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Pass.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Merit';
            $record->shortvalue = 'M';
            $record->bcgttypeid = 9;
            $record->specialval = 'A';
            $record->ranking = 2;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Merit.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Distinction';
            $record->shortvalue = 'D';
            $record->bcgttypeid = 9;
            $record->specialval = 'A';
            $record->ranking = 3;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Distinction.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
           
                
            $record = new stdClass();
            $record->value = 'Credit';
            $record->shortvalue = 'C';
            $record->bcgttypeid = 9;
            $record->specialval = 'A';
            $record->ranking = 2;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Credit.png';
                $DB->insert_record("block_bcgt_value_settings", $img);   
                
                
            
            // Not Met
            $record = new stdClass();
            $record->value = 'Not Achieved';
            $record->shortvalue = 'X';
            $record->bcgttypeid = 9;
            $record->specialval = 'X';
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_NotAchieved.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Partially Achieved';
            $record->shortvalue = 'PA';
            $record->bcgttypeid = 9;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_PartiallyAchieved.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Referred';
            $record->shortvalue = 'R';
            $record->bcgttypeid = 9;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Referred.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Late';
            $record->shortvalue = 'L';
            $record->bcgttypeid = 9;
            $record->specialval = 'L';
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Late.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Work Submitted';
            $record->shortvalue = 'WS';
            $record->bcgttypeid = 9;
            $record->specialval = 'WS';
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_WorkSubmitted.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Work Not Submitted';
            $record->shortvalue = 'WNS';
            $record->bcgttypeid = 9;
            $record->specialval = 'WNS';
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_WorkNotSubmitted.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Absent';
            $record->shortvalue = 'ABS';
            $record->bcgttypeid = 9;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Absent.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
    
    
    
    
        // HB VRQ
            // Met
                
            $record = new stdClass();
            $record->value = 'Achieved';
            $record->shortvalue = 'A';
            $record->bcgttypeid = 10;
            $record->specialval = 'A';
            $record->ranking = 0;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Achieved.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
                
            $record = new stdClass();
            $record->value = 'Pass';
            $record->shortvalue = 'P';
            $record->bcgttypeid = 10;
            $record->specialval = 'A';
            $record->ranking = 1;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Pass.png';
                $DB->insert_record("block_bcgt_value_settings", $img);

            $record = new stdClass();
            $record->value = 'Merit';
            $record->shortvalue = 'M';
            $record->bcgttypeid = 10;
            $record->specialval = 'A';
            $record->ranking = 2;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Merit.png';
                $DB->insert_record("block_bcgt_value_settings", $img);

            $record = new stdClass();
            $record->value = 'Distinction';
            $record->shortvalue = 'D';
            $record->bcgttypeid = 10;
            $record->specialval = 'A';
            $record->ranking = 3;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Distinction.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
        
            // Not Met
            $record = new stdClass();
            $record->value = 'Late';
            $record->shortvalue = 'L';
            $record->bcgttypeid = 10;
            $record->specialval = 'L';
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Late.png';
                $DB->insert_record("block_bcgt_value_settings", $img);

            $record = new stdClass();
            $record->value = 'Referred';
            $record->shortvalue = 'R';
            $record->bcgttypeid = 10;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Referred.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
        

            $record = new stdClass();
            $record->value = 'Work Submitted';
            $record->shortvalue = 'WS';
            $record->bcgttypeid = 10;
            $record->specialval = 'WS';
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_WorkSubmitted.png';
                $DB->insert_record("block_bcgt_value_settings", $img);

            $record = new stdClass();
            $record->value = 'Work Not Submitted';
            $record->shortvalue = 'WNS';
            $record->bcgttypeid = 10;
            $record->specialval = 'WNS';
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_WorkNotSubmitted.png';
                $DB->insert_record("block_bcgt_value_settings", $img);

            $record = new stdClass();
            $record->value = 'Partially Achieved';
            $record->shortvalue = 'PA';
            $record->bcgttypeid = 10;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_PartiallyAchieved.png';
                $DB->insert_record("block_bcgt_value_settings", $img);

            $record = new stdClass();
            $record->value = 'Not Achieved';
            $record->shortvalue = 'X';
            $record->bcgttypeid = 10;
            $record->specialval = 'X';
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_NotAchieved.png';
                $DB->insert_record("block_bcgt_value_settings", $img);

            $record = new stdClass();
            $record->value = 'Past Target Date';
            $record->shortvalue = 'PTD';
            $record->bcgttypeid = 10;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_PastTargetDate.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Absent';
            $record->shortvalue = 'ABS';
            $record->bcgttypeid = 10;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Absent.png';
                $DB->insert_record("block_bcgt_value_settings", $img);

            
        // HB NVQ
            
            // Met
            $record = new stdClass();
            $record->value = 'Achieved';
            $record->shortvalue = 'A';
            $record->bcgttypeid = 11;
            $record->specialval = 'A';
            $record->ranking = 1;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Achieved.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            // Not Met
            $record = new stdClass();
            $record->value = 'Not Achieved';
            $record->shortvalue = 'X';
            $record->bcgttypeid = 11;
            $record->specialval = 'X';
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_NotAchieved.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Partially Achieved';
            $record->shortvalue = 'PA';
            $record->bcgttypeid = 11;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_PartiallyAchieved.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Late';
            $record->shortvalue = 'L';
            $record->bcgttypeid = 11;
            $record->specialval = 'L';
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Late.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Referred';
            $record->shortvalue = 'R';
            $record->bcgttypeid = 11;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Referred.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Work Submitted';
            $record->shortvalue = 'WS';
            $record->bcgttypeid = 11;
            $record->specialval = 'WS';
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_WorkSubmitted.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Work Not Submitted';
            $record->shortvalue = 'WNS';
            $record->bcgttypeid = 11;
            $record->specialval = 'WNS';
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_WorkNotSubmitted.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Past Target Date';
            $record->shortvalue = 'PTD';
            $record->bcgttypeid = 11;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_PastTargetDate.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
            
            $record = new stdClass();
            $record->value = 'Absent';
            $record->shortvalue = 'ABS';
            $record->bcgttypeid = 11;
            $record->enabled = 1;
            $id = $DB->insert_record('block_bcgt_value', $record);
            
                $img = new stdClass();
                $img->bcgtvalueid = $id;
                $img->coreimg = '/pix/grid_symbols/core/icon_Absent.png';
                $DB->insert_record("block_bcgt_value_settings", $img);
        
        
        
            // Hosp here when done
        
        
        
        
        
        
        
        
    //---------------------- The Unit Types ---------------------------
    
    
    
    
    
    
    
    // ---------------------- The Type Awards ---------------------------
    //For units overall etc
    
        // General CG    
            
        $record = new stdClass();
        $record->award = 'Pass';
        $record->ranking = 1;
        $record->bcgttypeid = 9;
        $record->pointslower = 1.0;
        $record->pointsupper = 1.5;
        $DB->insert_record('block_bcgt_type_award', $record);
        
        $record = new stdClass();
        $record->award = 'Merit';
        $record->ranking = 2;
        $record->bcgttypeid = 9;
        $record->pointslower = 1.6;
        $record->pointsupper = 2.5;
        $DB->insert_record('block_bcgt_type_award', $record);
        
        $record = new stdClass();
        $record->award = 'Distinction';
        $record->ranking = 3;
        $record->bcgttypeid = 9;
        $record->pointslower = 2.6;
        $record->pointsupper = 3;
        $DB->insert_record('block_bcgt_type_award', $record);
            
            
        // HB VRQ        
        $record = new stdClass();
        $record->award = 'Pass';
        $record->ranking = 1;
        $record->bcgttypeid = 10;
        $record->pointslower = 1.0;
        $record->pointsupper = 1.5;
        $DB->insert_record('block_bcgt_type_award', $record);
        
        $record = new stdClass();
        $record->award = 'Merit';
        $record->ranking = 2;
        $record->bcgttypeid = 10;
        $record->pointslower = 1.6;
        $record->pointsupper = 2.5;
        $DB->insert_record('block_bcgt_type_award', $record);
        
        $record = new stdClass();
        $record->award = 'Distinction';
        $record->ranking = 3;
        $record->bcgttypeid = 10;
        $record->pointslower = 2.6;
        $record->pointsupper = 3;
        $DB->insert_record('block_bcgt_type_award', $record);
        
        
        
        // HB NVQ
        $record = new stdClass();
        $record->award = 'Pass';
        $record->ranking = 1;
        $record->bcgttypeid = 11;
        $DB->insert_record('block_bcgt_type_award', $record);
        
        
        
        
        
        // Hosp when done
    
        
    
    
    // ---------------------- The Unit Points ---------------------------
    

    // ---------------------- The Target Quals ---------------------------
    
        // HB VRQ (L1 Dip, L1 Cert, L1 Award, L2 Dip, L3 Dip)
            $record = new stdClass();
            $record->bcgtlevelid = 1; # L1
            $record->bcgttypeid = 10; # HB VRQ
            $record->bcgtsubtypeid = 3; # Dip
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_VRQ_L1_DIP = $DB->insert_record('block_bcgt_target_qual', $record); 

            $record = new stdClass();
            $record->bcgtlevelid = 1; # L1
            $record->bcgttypeid = 10; # HB VRQ
            $record->bcgtsubtypeid = 5; # Cert
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_VRQ_L1_CERT = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            $record = new stdClass();
            $record->bcgtlevelid = 1; # L1
            $record->bcgttypeid = 10; # HB VRQ
            $record->bcgtsubtypeid = 6; # Award
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_VRQ_L1_AW = $DB->insert_record('block_bcgt_target_qual', $record); 

            $record = new stdClass();
            $record->bcgtlevelid = 2;
            $record->bcgttypeid = 10; # HB VRQ
            $record->bcgtsubtypeid = 3; # Dip
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_VRQ_L2_DIP = $DB->insert_record('block_bcgt_target_qual', $record); 

            $record = new stdClass();
            $record->bcgtlevelid = 3;
            $record->bcgttypeid = 10; # HB VRQ
            $record->bcgtsubtypeid = 3; # Dip
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_VRQ_L3_DIP = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            // NEW
            $record = new stdClass();
            $record->bcgtlevelid = 3;
            $record->bcgttypeid = 10; # HB VRQ
            $record->bcgtsubtypeid = 5; # Cert
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_VRQ_L3_CERT = $DB->insert_record('block_bcgt_target_qual', $record); 
            // NEW
    
    
        // HB NVQ
            $record = new stdClass();
            $record->bcgtlevelid = 1; # L1
            $record->bcgttypeid = 11; # HB NVQ
            $record->bcgtsubtypeid = 5; # Cert
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_NVQ_L1_CERT = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            $record = new stdClass();
            $record->bcgtlevelid = 1; # L1
            $record->bcgttypeid = 11; # HB NVQ
            $record->bcgtsubtypeid = 3; # Cert
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_NVQ_L1_DIP = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            $record = new stdClass();
            $record->bcgtlevelid = 2; # L2
            $record->bcgttypeid = 11; # HB NVQ
            $record->bcgtsubtypeid = 3; # Dip
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_NVQ_L2_DIP = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            $record = new stdClass();
            $record->bcgtlevelid = 3; # L3
            $record->bcgttypeid = 11; # HB NVQ
            $record->bcgtsubtypeid = 3; # Dip
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_NVQ_L3_DIP = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            
        // General CG (L1, L2, L3 - Dip, Cert, Award)
            
            // Dips
            $record = new stdClass();
            $record->bcgtlevelid = 1; # L2
            $record->bcgttypeid = 9; # General
            $record->bcgtsubtypeid = 3; # Dip
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_GENERAL_L1_DIP = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            $record = new stdClass();
            $record->bcgtlevelid = 2; # L2
            $record->bcgttypeid = 9; # General
            $record->bcgtsubtypeid = 3; # Dip
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_GENERAL_L2_DIP = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            $record = new stdClass();
            $record->bcgtlevelid = 3; # L2
            $record->bcgttypeid = 9; # General
            $record->bcgtsubtypeid = 3; # Dip
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_GENERAL_L3_DIP = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            // Certs
            $record = new stdClass();
            $record->bcgtlevelid = 1; # L2
            $record->bcgttypeid = 9; # General
            $record->bcgtsubtypeid = 5; # Cert
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_GENERAL_L1_CERT = $DB->insert_record('block_bcgt_target_qual', $record);
            
            $record = new stdClass();
            $record->bcgtlevelid = 2; # L2
            $record->bcgttypeid = 9; # General
            $record->bcgtsubtypeid = 5; # Cert
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_GENERAL_L2_CERT = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            $record = new stdClass();
            $record->bcgtlevelid = 3; # L2
            $record->bcgttypeid = 9; # General
            $record->bcgtsubtypeid = 5; # Cert
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_GENERAL_L3_CERT = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            
            // Awards
            $record = new stdClass();
            $record->bcgtlevelid = 1; # L2
            $record->bcgttypeid = 9; # General
            $record->bcgtsubtypeid = 6; # Award
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_GENERAL_L1_AWARD = $DB->insert_record('block_bcgt_target_qual', $record);
            
            $record = new stdClass();
            $record->bcgtlevelid = 2; # L2
            $record->bcgttypeid = 9; # General
            $record->bcgtsubtypeid = 6; # Award
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_GENERAL_L2_AWARD = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            $record = new stdClass();
            $record->bcgtlevelid = 3; # L2
            $record->bcgttypeid = 9; # General
            $record->bcgtsubtypeid = 6; # Award
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_GENERAL_L3_AWARD = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            
            
            
            
            
        // Hosp when done
    
    
    
    
    //Any attributes e,g default number of credits
    
            
    
    //------------------ Unit Type Defaults ------------
   
            

    // ---------------------- The Target Quals Grades ---------------------------
    //e.g. overal for the qual family
    
        // HB VRQ, L1, Dip - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L1_DIP;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L1_DIP;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);

            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L1_DIP;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
        // HB VRQ L1 Cert - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L1_CERT;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L1_CERT;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);

            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L1_CERT;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            
        // HB VRQ L1 Award - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L1_AW;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L1_AW;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);

            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L1_AW;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
          
        // HB VRQ L2 Dip - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L2_DIP;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L2_DIP;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L2_DIP;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
        // HB VRQ L3 Dip - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L3_DIP;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L3_DIP;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L3_DIP;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            
        // HB VRQ L3 Cert - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L3_CERT;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L3_CERT;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L3_CERT;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
    
        
        // HB NVQ - Only Pass for L1 L2 & L3
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_NVQ_L1_CERT;     
            $record->targetgrade = 'Pass';
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_NVQ_L1_DIP;     
            $record->targetgrade = 'Pass';
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_NVQ_L2_DIP;     
            $record->targetgrade = 'Pass';
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_NVQ_L3_DIP;     
            $record->targetgrade = 'Pass';
            $DB->insert_record('block_bcgt_target_breakdown', $record);
    
        
        // General L1 Dip - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L1_DIP;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L1_DIP;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L1_DIP;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
        // General L2 Dip - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L2_DIP;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L2_DIP;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L2_DIP;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
        // General L3 Dip - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L3_DIP;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L3_DIP;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L3_DIP;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            
        // General L1 Cert - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L1_CERT;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L1_CERT;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L1_CERT;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);  
            
        // General L2 Cert - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L2_CERT;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L2_CERT;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L2_CERT;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
        // General L3 Cert - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L3_CERT;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L3_CERT;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L3_CERT;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
        // General L1 Award - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L1_AWARD;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L1_AWARD;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L1_AWARD;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
        // General L2 Award - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L2_AWARD;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L2_AWARD;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L2_AWARD;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
        // General L3 Award - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L3_AWARD;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L3_AWARD;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L3_AWARD;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
        
    
        // Hosp when done
            
    
      
    //------------- ANY TABS ------------
    
    
    
    //Others
    //check config for prior learning and if need be go and run thise points
    //import
    global $CFG;
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/lib.php');
    run_bcgtcg_initial_import();
    return true;
}
?>
