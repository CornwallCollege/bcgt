<?php

function xmldb_block_bcgtcg_upgrade($oldversion = 0)
{
    global $DB;
    $dbman = $DB->get_manager();
    
    
    if ($oldversion < 2013091002)
    {
        $record = $DB->get_record_sql('SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? 
            AND bcgttypeid = ? AND bcgtsubtypeid = ?', array(1, 11, 3));
        if(!$record)
        {
            $record = new stdClass();
            $record->bcgtlevelid = 1; # L1
            $record->bcgttypeid = 11; # HB NVQ
            $record->bcgtsubtypeid = 3; # Cert
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            $ID = $DB->insert_record('block_bcgt_target_qual', $record); 

            $record = new stdClass();
            $record->bcgttargetqualid = $ID;     
            $record->targetgrade = 'Pass';
            $DB->insert_record('block_bcgt_target_breakdown', $record);
        }
        
        
    }
    
    if ($oldversion < 2013092303)
    {
        
        $check = $DB->get_record("block_bcgt_value", array("bcgttypeid" => 10, "shortvalue" => "A"));
        if (!$check)
        {
        
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
            
        }
        
    }
    
    if ($oldversion < 2013092700)
    {
        
        // UPdate type_awards for general CG and set correct rankings
        $pass = $DB->get_record("block_bcgt_type_award", array("bcgttypeid" => 9, "award" => "Pass"));
        if ($pass)
        {
            $pass->ranking = 1;
            $DB->update_record("block_bcgt_type_award", $pass);
        }
        
        $merit = $DB->get_record("block_bcgt_type_award", array("bcgttypeid" => 9, "award" => "Merit"));
        if ($merit)
        {
            $merit->ranking = 2;
            $DB->update_record("block_bcgt_type_award", $merit);
        }
        
        $dist = $DB->get_record("block_bcgt_type_award", array("bcgttypeid" => 9, "award" => "Distinction"));
        if ($dist)
        {
            $dist->ranking = 3;
            $DB->update_record("block_bcgt_type_award", $dist);
        }
        
    }
    
    if ($oldversion < 2013102800)
    {
        
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
        
    }
    
    
    
    if ($oldversion < 2014011500)
    {
        
        $record = new stdClass();
        $record->bcgtlevelid = 3;
        $record->bcgttypeid = 10; # HB VRQ
        $record->bcgtsubtypeid = 5; # Cert
        $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
        //e.g ASlevel to A2 Level
        $ID_HB_VRQ_L3_CERT = $DB->insert_record('block_bcgt_target_qual', $record); 
        
        mtrace("Inserted targetqual record for HB VRQ L3 Certificate");
        
            
        $check = $DB->get_record("block_bcgt_target_qual", array("bcgtlevelid" => 3, "bcgttypeid" => 10, "bcgtsubtypeid" => 3));
        if ($check)
        {
            $ID_HB_VRQ_L3_DIP = $check->id;
            
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
            
            mtrace("Inserted target breakdowns for HB VRQ L3 DIP");
            
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
            
            mtrace("Inserted target breakdown for HB VRQ L3 Cert");
            
            
            
        }
                        
    }
    
    
    
    // Entry L3 HB VRQ
    if ($oldversion < 2014072900)
    {
        
        $record = $DB->get_record("block_bcgt_level", array("trackinglevel" => "Entry Level 3"));
        if (!$record)
        {
            $ins = new stdClass();
            $ins->id = 8;
            $ins->trackinglevel = "Entry Level 3";
            $EL3ID = $DB->insert_record("block_bcgt_level", $ins);
        }
        else
        {
            $EL3ID = $record->id;
        }
        
        
        // Entry 3 Diploma
        $check = $DB->get_record("block_bcgt_target_qual", array("bcgtlevelid" => $EL3ID, "bcgttypeid" => 10, "bcgtsubtypeid" => 3));
        if (!$check)
        {
        
            $record = new stdClass();
            $record->bcgtlevelid = $EL3ID; # Entry L3
            $record->bcgttypeid = 10; # HB VRQ
            $record->bcgtsubtypeid = 3; # Dip
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_VRQ_EL3_DIP = $DB->insert_record('block_bcgt_target_qual', $record); 
            
            
            // HB VRQ, EL3, Dip - PMD
            $check = $DB->get_record("block_bcgt_target_breakdown", array("bcgttargetqualid" => $ID_HB_VRQ_EL3_DIP));
            if (!$check)
            {

                $record = new stdClass();
                $record->bcgttargetqualid = $ID_HB_VRQ_EL3_DIP;      
                $record->targetgrade = 'Pass';
                $record->unitsscorelower = 1; //Units score if needed
                $record->unitsscoreupper = 1.5;
                $DB->insert_record('block_bcgt_target_breakdown', $record);

                $record = new stdClass();
                $record->bcgttargetqualid = $ID_HB_VRQ_EL3_DIP;      
                $record->targetgrade = 'Merit';
                $record->unitsscorelower = 1.6; //Units score if needed
                $record->unitsscoreupper = 2.5;
                $DB->insert_record('block_bcgt_target_breakdown', $record);

                $record = new stdClass();
                $record->bcgttargetqualid = $ID_HB_VRQ_EL3_DIP;      
                $record->targetgrade = 'Distinction';
                $record->unitsscorelower = 2.6; //Units score if needed
                $record->unitsscoreupper = 3;
                $DB->insert_record('block_bcgt_target_breakdown', $record);

                mtrace("Installed Entry Level 3 Qual Type for HB VRQ");

            }
            
            
        
        }
        
        
    }
    
    
    if ($oldversion < 2014073000)
    {

            //HB VRQ L3 Award
            $check = $DB->get_record("block_bcgt_target_qual", array("bcgtlevelid" => 3, "bcgttypeid" => 10, "bcgtsubtypeid" => 6));
            if (!$check)
            {
            
                $record = new stdClass();
                $record->bcgtlevelid = 3; # L1
                $record->bcgttypeid = 10; # HB VRQ
                $record->bcgtsubtypeid = 6; # Dip
                $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
                $ID_HB_VRQ_L3_AW = $DB->insert_record('block_bcgt_target_qual', $record); 

                // HB VRQ L3 Award - PMD
                $record = new stdClass();
                $record->bcgttargetqualid = $ID_HB_VRQ_L3_AW;      
                $record->targetgrade = 'Pass';
                $record->unitsscorelower = 1; //Units score if needed
                $record->unitsscoreupper = 1.5;
                $DB->insert_record('block_bcgt_target_breakdown', $record);

                $record = new stdClass();
                $record->bcgttargetqualid = $ID_HB_VRQ_L3_AW;      
                $record->targetgrade = 'Merit';
                $record->unitsscorelower = 1.6; //Units score if needed
                $record->unitsscoreupper = 2.5;
                $DB->insert_record('block_bcgt_target_breakdown', $record);

                $record = new stdClass();
                $record->bcgttargetqualid = $ID_HB_VRQ_L3_AW;      
                $record->targetgrade = 'Distinction';
                $record->unitsscorelower = 2.6; //Units score if needed
                $record->unitsscoreupper = 3;
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            }

            
    }
    
    
    if ($oldversion < 2014073101)
    {
        
        
        $check = $DB->get_record("block_bcgt_target_qual", array("bcgtlevelid" => 2, "bcgttypeid" => 10, "bcgtsubtypeid" => 6));
        
        if (!$check)
        {
            
            // L2 Award
            $record = new stdClass();
            $record->bcgtlevelid = 2; # L2
            $record->bcgttypeid = 10; # HB VRQ
            $record->bcgtsubtypeid = 6; # Award
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_VRQ_L2_AW = $DB->insert_record('block_bcgt_target_qual', $record); 
        
        
            // HB VRQ L2 Award - PMD
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L2_AW;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L2_AW;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_HB_VRQ_L2_AW;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            $DB->insert_record('block_bcgt_target_breakdown', $record);
            
        }
        
    }
    
    
    if ($oldversion < 2014073102)
    {
        
        
        $record = $DB->get_record("block_bcgt_level", array("trackinglevel" => "Entry Level 3"));
        $EL3ID = $record->id;
        
        
        // Entry 3 Cert
        $check = $DB->get_record("block_bcgt_target_qual", array("bcgtlevelid" => $EL3ID, "bcgttypeid" => 10, "bcgtsubtypeid" => 5));
        if (!$check)
        {
            $record = new stdClass();
            $record->bcgtlevelid = $EL3ID; # Entry L3
            $record->bcgttypeid = 10; # HB VRQ
            $record->bcgtsubtypeid = 5; # Cert
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_VRQ_EL3_CERT = $DB->insert_record('block_bcgt_target_qual', $record); 
        }
        else
        {
            $ID_HB_VRQ_EL3_CERT = $check->id;
        }

        
        
        // Entry 3 Award
        $check = $DB->get_record("block_bcgt_target_qual", array("bcgtlevelid" => $EL3ID, "bcgttypeid" => 10, "bcgtsubtypeid" => 6));
        if (!$check)
        {
            $record = new stdClass();
            $record->bcgtlevelid = $EL3ID; # Entry L3
            $record->bcgttypeid = 10; # HB VRQ
            $record->bcgtsubtypeid = 6; # Award
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            $ID_HB_VRQ_EL3_AW = $DB->insert_record('block_bcgt_target_qual', $record); 
        }
        else
        {
            $ID_HB_VRQ_EL3_AW = $check->id;
        }
        
        
        
         // HB VRQ, EL3, Cert - PMD
            $check = $DB->get_record("block_bcgt_target_breakdown", array("bcgttargetqualid" => $ID_HB_VRQ_EL3_CERT));
            if (!$check)
            {
                $record = new stdClass();
                $record->bcgttargetqualid = $ID_HB_VRQ_EL3_CERT;      
                $record->targetgrade = 'Pass';
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
        // HB VRQ, EL3, Award - PMD
            $check = $DB->get_record("block_bcgt_target_breakdown", array("bcgttargetqualid" => $ID_HB_VRQ_EL3_AW));
            if (!$check)
            {
                $record = new stdClass();
                $record->bcgttargetqualid = $ID_HB_VRQ_EL3_AW;      
                $record->targetgrade = 'Pass';
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
            
        // Delete the Dip ones which i messed up and insert again
            $check = $DB->get_record("block_bcgt_target_qual", array("bcgtlevelid" => $EL3ID, "bcgttypeid" => 10, "bcgtsubtypeid" => 3));
            if ($check)
            {
                
                $DB->delete_records("block_bcgt_target_breakdown", array("bcgttargetqualid" => $check->id));
                
                // HB VRQ, EL3, Award - PMD
                $record = new stdClass();
                $record->bcgttargetqualid = $check->id;      
                $record->targetgrade = 'Pass';
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            
                
            }
        
        
        
        
        
    }
    
        
    if ($oldversion < 2014080800)
    {
        
        // NVQ
            $record = new stdClass();
            $record->bcgtlevelid = 1; # L1
            $record->bcgttypeid = 9; # General
            $record->bcgtsubtypeid = 15; # NVQ
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            
            $check = $DB->get_record("block_bcgt_target_qual", array(
                "bcgtlevelid" => $record->bcgtlevelid,
                "bcgttypeid" => $record->bcgttypeid,
                "bcgtsubtypeid" => $record->bcgtsubtypeid,
                "previoustargetqualid" => $record->previoustargetqualid
                    ));
            
            if ($check){
                $ID_GENERAL_L1_NVQ = $check->id;
            } else {
                $ID_GENERAL_L1_NVQ = $DB->insert_record('block_bcgt_target_qual', $record);
            }
            
            $record = new stdClass();
            $record->bcgtlevelid = 2; # L2
            $record->bcgttypeid = 9; # General
            $record->bcgtsubtypeid = 15; # NVQ
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            
            $check = $DB->get_record("block_bcgt_target_qual", array(
                "bcgtlevelid" => $record->bcgtlevelid,
                "bcgttypeid" => $record->bcgttypeid,
                "bcgtsubtypeid" => $record->bcgtsubtypeid,
                "previoustargetqualid" => $record->previoustargetqualid
                    ));
            
            if ($check){
                $ID_GENERAL_L2_NVQ = $check->id;
            } else {
                $ID_GENERAL_L2_NVQ = $DB->insert_record('block_bcgt_target_qual', $record);
            }
                        
            $record = new stdClass();
            $record->bcgtlevelid = 3; # L3
            $record->bcgttypeid = 9; # General
            $record->bcgtsubtypeid = 15; # NVQ
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            
             $check = $DB->get_record("block_bcgt_target_qual", array(
                "bcgtlevelid" => $record->bcgtlevelid,
                "bcgttypeid" => $record->bcgttypeid,
                "bcgtsubtypeid" => $record->bcgtsubtypeid,
                "previoustargetqualid" => $record->previoustargetqualid
                    ));
            
            if ($check){
                $ID_GENERAL_L3_NVQ = $check->id;
            } else {
                $ID_GENERAL_L3_NVQ = $DB->insert_record('block_bcgt_target_qual', $record);
            }
                        
            $record = new stdClass();
            $record->bcgtlevelid = 4; # L4
            $record->bcgttypeid = 9; # General
            $record->bcgtsubtypeid = 15; # NVQ
            $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
            //e.g ASlevel to A2 Level
            
            $check = $DB->get_record("block_bcgt_target_qual", array(
                "bcgtlevelid" => $record->bcgtlevelid,
                "bcgttypeid" => $record->bcgttypeid,
                "bcgtsubtypeid" => $record->bcgtsubtypeid,
                "previoustargetqualid" => $record->previoustargetqualid
                    ));
            
            if ($check){
                $ID_GENERAL_L4_NVQ = $check->id;
            } else {
                $ID_GENERAL_L4_NVQ = $DB->insert_record('block_bcgt_target_qual', $record);
            }
                        
            
            
            
            
            
        // General L1 NVQ
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L1_NVQ;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            
            $check = $DB->get_record("block_bcgt_target_breakdown", array(
                "bcgttargetqualid" => $record->bcgttargetqualid,
                "targetgrade" => $record->targetgrade,
                "unitsscorelower" => $record->unitsscorelower,
                "unitsscoreupper" => $record->unitsscoreupper
            ));
            
            if (!$check){            
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
            
            
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L1_NVQ;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            
            $check = $DB->get_record("block_bcgt_target_breakdown", array(
                "bcgttargetqualid" => $record->bcgttargetqualid,
                "targetgrade" => $record->targetgrade,
                "unitsscorelower" => $record->unitsscorelower,
                "unitsscoreupper" => $record->unitsscoreupper
            ));
            
            if (!$check){            
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
            
            
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L1_NVQ;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;

            $check = $DB->get_record("block_bcgt_target_breakdown", array(
                "bcgttargetqualid" => $record->bcgttargetqualid,
                "targetgrade" => $record->targetgrade,
                "unitsscorelower" => $record->unitsscorelower,
                "unitsscoreupper" => $record->unitsscoreupper
            ));
            
            if (!$check){            
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
            
            
            
        // General L2 NVQ
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L2_NVQ;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            
            $check = $DB->get_record("block_bcgt_target_breakdown", array(
                "bcgttargetqualid" => $record->bcgttargetqualid,
                "targetgrade" => $record->targetgrade,
                "unitsscorelower" => $record->unitsscorelower,
                "unitsscoreupper" => $record->unitsscoreupper
            ));
            
            if (!$check){            
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
            
            
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L2_NVQ;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            
            $check = $DB->get_record("block_bcgt_target_breakdown", array(
                "bcgttargetqualid" => $record->bcgttargetqualid,
                "targetgrade" => $record->targetgrade,
                "unitsscorelower" => $record->unitsscorelower,
                "unitsscoreupper" => $record->unitsscoreupper
            ));
            
            if (!$check){            
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
            
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L2_NVQ;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            
            $check = $DB->get_record("block_bcgt_target_breakdown", array(
                "bcgttargetqualid" => $record->bcgttargetqualid,
                "targetgrade" => $record->targetgrade,
                "unitsscorelower" => $record->unitsscorelower,
                "unitsscoreupper" => $record->unitsscoreupper
            ));
            
            if (!$check){            
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
            
            
        // General L3 NVQ
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L3_NVQ;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
           
            $check = $DB->get_record("block_bcgt_target_breakdown", array(
                "bcgttargetqualid" => $record->bcgttargetqualid,
                "targetgrade" => $record->targetgrade,
                "unitsscorelower" => $record->unitsscorelower,
                "unitsscoreupper" => $record->unitsscoreupper
            ));
            
            if (!$check){            
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
            
            
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L3_NVQ;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
           
            $check = $DB->get_record("block_bcgt_target_breakdown", array(
                "bcgttargetqualid" => $record->bcgttargetqualid,
                "targetgrade" => $record->targetgrade,
                "unitsscorelower" => $record->unitsscorelower,
                "unitsscoreupper" => $record->unitsscoreupper
            ));
            
            if (!$check){            
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
            
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L3_NVQ;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            
            $check = $DB->get_record("block_bcgt_target_breakdown", array(
                "bcgttargetqualid" => $record->bcgttargetqualid,
                "targetgrade" => $record->targetgrade,
                "unitsscorelower" => $record->unitsscorelower,
                "unitsscoreupper" => $record->unitsscoreupper
            ));
            
            if (!$check){            
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
            
            
            
        // General L4 NVQ
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L4_NVQ;      
            $record->targetgrade = 'Pass';
            $record->unitsscorelower = 1; //Units score if needed
            $record->unitsscoreupper = 1.5;
            
            $check = $DB->get_record("block_bcgt_target_breakdown", array(
                "bcgttargetqualid" => $record->bcgttargetqualid,
                "targetgrade" => $record->targetgrade,
                "unitsscorelower" => $record->unitsscorelower,
                "unitsscoreupper" => $record->unitsscoreupper
            ));
            
            if (!$check){            
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
            
            
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L4_NVQ;      
            $record->targetgrade = 'Merit';
            $record->unitsscorelower = 1.6; //Units score if needed
            $record->unitsscoreupper = 2.5;
            
            $check = $DB->get_record("block_bcgt_target_breakdown", array(
                "bcgttargetqualid" => $record->bcgttargetqualid,
                "targetgrade" => $record->targetgrade,
                "unitsscorelower" => $record->unitsscorelower,
                "unitsscoreupper" => $record->unitsscoreupper
            ));
            
            if (!$check){            
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
            
            
            
            $record = new stdClass();
            $record->bcgttargetqualid = $ID_GENERAL_L4_NVQ;      
            $record->targetgrade = 'Distinction';
            $record->unitsscorelower = 2.6; //Units score if needed
            $record->unitsscoreupper = 3;
            
            $check = $DB->get_record("block_bcgt_target_breakdown", array(
                "bcgttargetqualid" => $record->bcgttargetqualid,
                "targetgrade" => $record->targetgrade,
                "unitsscorelower" => $record->unitsscorelower,
                "unitsscoreupper" => $record->unitsscoreupper
            ));
            
            if (!$check){            
                $DB->insert_record('block_bcgt_target_breakdown', $record);
            }
            
            
            
        
    }
    
    
    if ($oldversion < 2014102700)
    {
        
        // HB VRQ L2 Cert
        $record = new stdClass();
        $record->bcgtlevelid = 2; # L2
        $record->bcgttypeid = 10; # HB VRQ
        $record->bcgtsubtypeid = 5; # Cert
        $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
        
        $check = $DB->get_record("block_bcgt_target_qual", array(
                "bcgtlevelid" => $record->bcgtlevelid,
                "bcgttypeid" => $record->bcgttypeid,
                "bcgtsubtypeid" => $record->bcgtsubtypeid,
                "previoustargetqualid" => $record->previoustargetqualid
                    ));
        if ($check){
            $ID_HB_VRQ_L2_CERT = $check->id;
        } else {
            $ID_HB_VRQ_L2_CERT = $DB->insert_record('block_bcgt_target_qual', $record);
        }
        
        
        // HB VRQ L2 Cert - PMD
        $record = new stdClass();
        $record->bcgttargetqualid = $ID_HB_VRQ_L2_CERT;      
        $record->targetgrade = 'Pass';
        $record->unitsscorelower = 1; //Units score if needed
        $record->unitsscoreupper = 1.5;
        $DB->insert_record('block_bcgt_target_breakdown', $record);

        $record = new stdClass();
        $record->bcgttargetqualid = $ID_HB_VRQ_L2_CERT;      
        $record->targetgrade = 'Merit';
        $record->unitsscorelower = 1.6; //Units score if needed
        $record->unitsscoreupper = 2.5;
        $DB->insert_record('block_bcgt_target_breakdown', $record);

        $record = new stdClass();
        $record->bcgttargetqualid = $ID_HB_VRQ_L2_CERT;      
        $record->targetgrade = 'Distinction';
        $record->unitsscorelower = 2.6; //Units score if needed
        $record->unitsscoreupper = 3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
            
                
    }
    
    
    
    if ($oldversion < 2014111900)
    {
        
        $record = new stdClass();
        $record->bcgtlevelid = 4; #L4
        $record->bcgttypeid = 10; # HB VRQ
        $record->bcgtsubtypeid = 3; # Dip
        $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
        //e.g ASlevel to A2 Level
        
        $check = $DB->get_record("block_bcgt_target_qual", array(
                "bcgtlevelid" => $record->bcgtlevelid,
                "bcgttypeid" => $record->bcgttypeid,
                "bcgtsubtypeid" => $record->bcgtsubtypeid,
                "previoustargetqualid" => $record->previoustargetqualid));
        
        if ($check){
            $ID_HB_VRQ_L4_DIP = $check->id;
        } else {
            $ID_HB_VRQ_L4_DIP = $DB->insert_record('block_bcgt_target_qual', $record); 
        }
        
        
        
        
        // HB VRQ L3 Dip - PMD
        $record = new stdClass();
        $record->bcgttargetqualid = $ID_HB_VRQ_L4_DIP;      
        $record->targetgrade = 'Pass';
        $record->unitsscorelower = 1; //Units score if needed
        $record->unitsscoreupper = 1.5;
        
        $check = $DB->get_record("block_bcgt_target_breakdown", array(
            "bcgttargetqualid" => $ID_HB_VRQ_L4_DIP,
            "targetgrade" => "Pass"
        ));
        
        if (!$check){
            $DB->insert_record('block_bcgt_target_breakdown', $record);
        }

        
        
        
        $record = new stdClass();
        $record->bcgttargetqualid = $ID_HB_VRQ_L4_DIP;      
        $record->targetgrade = 'Merit';
        $record->unitsscorelower = 1.6; //Units score if needed
        $record->unitsscoreupper = 2.5;
        
        $check = $DB->get_record("block_bcgt_target_breakdown", array(
            "bcgttargetqualid" => $ID_HB_VRQ_L4_DIP,
            "targetgrade" => "Merit"
        ));
        
        if (!$check){
            $DB->insert_record('block_bcgt_target_breakdown', $record);
        }
        
        
  
        
        $record = new stdClass();
        $record->bcgttargetqualid = $ID_HB_VRQ_L4_DIP;      
        $record->targetgrade = 'Distinction';
        $record->unitsscorelower = 2.6; //Units score if needed
        $record->unitsscoreupper = 3;
        
        $check = $DB->get_record("block_bcgt_target_breakdown", array(
            "bcgttargetqualid" => $ID_HB_VRQ_L4_DIP,
            "targetgrade" => "Distinction"
        ));
        
        if (!$check){
            $DB->insert_record('block_bcgt_target_breakdown', $record);
        }
        
    }
    
    
}
