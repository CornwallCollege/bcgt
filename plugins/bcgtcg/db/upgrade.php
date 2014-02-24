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
    
    
}
