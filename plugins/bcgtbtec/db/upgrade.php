<?php
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/lib.php');
function xmldb_block_bcgtbtec_upgrade($oldversion = 0)
{
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2013052200)
    {
        $record = new stdClass();
        $record->type = 'Final Project';
        $record->bcgttypeid = 4;
        $DB->insert_record('block_bcgt_unit_type', $record);
    }
    
    if ($oldversion < 2013060800)
    {
        $record = new stdClass();
        $record->id = 5;
        $record->type = 'BTEC Lower';
        $record->bcgttypefamilyid = 2;
        $DB->update_record('block_bcgt_type', $record);
    }
    
    if ($oldversion < 2013061900)
    {
        //need to alter all of the values with 
        //special val and also enabled
        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ? ";
        $record = $DB->get_record_sql($sql, array(2, 'A'));
        if($record)
        {
            $record->specialval = 'A';
            $record->enabled = 1;
            $record->ranking = 1;
            $DB->update_record('block_bcgt_value', $record);
        }
        $record = $DB->get_record_sql($sql, array(2, 'PA'));
        if($record)
        {
            $record->specialval = '';
            $record->enabled = 1;
            $record->ranking = 2;
            $DB->update_record('block_bcgt_value', $record);
        }
        $record = $DB->get_record_sql($sql, array(2, 'X'));
        if($record)
        {
            $record->specialval = 'X';
            $record->enabled = 1;
            $record->ranking = 3;
            $DB->update_record('block_bcgt_value', $record);
        }
        $record = $DB->get_record_sql($sql, array(2, 'N/A'));
        if($record)
        {
            $record->specialval = '';
            $record->enabled = 1;
            $record->ranking = 4;
            $DB->update_record('block_bcgt_value', $record);
        }
        $record = $DB->get_record_sql($sql, array(2, 'R'));
        if($record)
        {
            $record->specialval = '';
            $record->enabled = 1;
            $record->ranking = 5;
            $DB->update_record('block_bcgt_value', $record);
        }
        $record = $DB->get_record_sql($sql, array(2, 'L'));
        if($record)
        {
            $record->specialval = 'L';
            $record->enabled = 1;
            $record->ranking = 6;
            $DB->update_record('block_bcgt_value', $record);
        }
        $record = $DB->get_record_sql($sql, array(2, 'WS'));
        if($record)
        {
            $record->specialval = 'WS';
            $record->enabled = 1;
            $record->ranking = 7;
            $DB->update_record('block_bcgt_value', $record);
        }
        $record = $DB->get_record_sql($sql, array(2, 'WNS'));
        if($record)
        {
            $record->specialval = 'WNS';
            $record->enabled = 1;
            $record->ranking = 8;
            $DB->update_record('block_bcgt_value', $record);
        }
    }
    
    if($oldversion < 2013062000)
    {
        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $record = $DB->get_record_sql($sql, array(2, 'A'));
        if($record)
        {
            $id = $record->id;
            $record = new stdClass();
            $record->bcgtvalueid = $id;
            $record->coreimg = '/pix/grid_symbols/core/achieved.png';
            $record->coreimglate = '/pix/grid_symbols/core/achievedLate.png';
            $DB->insert_record('block_bcgt_value_settings', $record);
        }
        
        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $record = $DB->get_record_sql($sql, array(2, 'PA'));
        if($record)
        {
            $id = $record->id;
            $record = new stdClass();
            $record->bcgtvalueid = $id;
            $record->coreimg = '/pix/grid_symbols/core/pachieved.png';
            $record->coreimglate = '/pix/grid_symbols/core/paLate.png';
            $DB->insert_record('block_bcgt_value_settings', $record);
        }
        
        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $record = $DB->get_record_sql($sql, array(2, 'X'));
        if($record)
        {
            $id = $record->id;
            $record = new stdClass();
            $record->bcgtvalueid = $id;
            $record->coreimg = '/pix/grid_symbols/core/notachieved.png';
            $record->coreimglate = '/pix/grid_symbols/core/notachievedLate.png';
            $DB->insert_record('block_bcgt_value_settings', $record);
        }
        
        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $record = $DB->get_record_sql($sql, array(2, 'N/A'));
        if($record)
        {
            $id = $record->id;
            $record = new stdClass();
            $record->bcgtvalueid = $id;
            $record->coreimg = '/pix/grid_symbols/core/notattempted.png';
            $DB->insert_record('block_bcgt_value_settings', $record);
        }
        
        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $record = $DB->get_record_sql($sql, array(2, 'R'));
        if($record)
        {
            $id = $record->id;
            $record = new stdClass();
            $record->bcgtvalueid = $id;
            $record->coreimg = '/pix/grid_symbols/core/referred.png';
            $DB->insert_record('block_bcgt_value_settings', $record);
        }
        
        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $record = $DB->get_record_sql($sql, array(2, 'L'));
        if($record)
        {
            $id = $record->id;
            $record = new stdClass();
            $record->bcgtvalueid = $id;
            $record->coreimg = '/pix/grid_symbols/core/late.png';
            $DB->insert_record('block_bcgt_value_settings', $record);
        }
        
        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $record = $DB->get_record_sql($sql, array(2, 'WS'));
        if($record)
        {
            $id = $record->id;
            $record = new stdClass();
            $record->bcgtvalueid = $id;
            $record->coreimg = '/pix/grid_symbols/core/in.png';
            $DB->insert_record('block_bcgt_value_settings', $record);
        }
        
        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND shortvalue = ?";
        $record = $DB->get_record_sql($sql, array(2, 'WNS'));
        if($record)
        {
            $id = $record->id;
            $record = new stdClass();
            $record->bcgtvalueid = $id;
            $record->coreimg = '/pix/grid_symbols/core/notin.png';
            $DB->insert_record('block_bcgt_value_settings', $record);
        }
    }
    
    if($oldversion < 2013071200)
    {
        $record = new stdClass();
        $record->id = 2;
        $record->pluginname = 'bcgtbtec';
        $DB->update_record('block_bcgt_type_family', $record);
    }
    
    if ($oldversion < 2013081200){
        //insert BTEC values into database
        //also insert the BTEC Grades into the database
        //get the targetqualid for the btecs
        //level 3 ext dip 
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(3, 2, 2));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'PPP';
            $stdObj->shortvalue = 'PPP';
            $stdObj->ranking = 1;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?  
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(1, 120, $targetQualID, 'PPP'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'PPP/MPP';
            $stdObj->shortvalue = 'PPP/MPP';
            $stdObj->ranking = 1.3;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'PPP/MPP';
            $stdObj->ucaspoints = 133.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MPP/PPP';
            $stdObj->shortvalue = 'MPP/PPP';
            $stdObj->ranking = 1.6;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MPP/PPP';
            $stdObj->ucaspoints = 146.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MPP';
            $stdObj->shortvalue = 'MPP';
            $stdObj->ranking = 2;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(2, 160, $targetQualID, 'MPP'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MPP/MMP';
            $stdObj->shortvalue = 'MPP/MMP';
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MPP/MMP';
            $stdObj->ucaspoints = 173.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MMP/MPP';
            $stdObj->shortvalue = 'MMP/MPP';
            $stdObj->ranking = 2.6;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MMP/MPP';
            $stdObj->ucaspoints = 186.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MMP';
            $stdObj->shortvalue = 'MMP';
            $stdObj->ranking = 3;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(3, 200, $targetQualID, 'MMP'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MMP/MMM';
            $stdObj->shortvalue = 'MMP/MMM';
            $stdObj->ranking = 3.3;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MMP/MMM';
            $stdObj->ucaspoints = 213.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MMM/MMP';
            $stdObj->shortvalue = 'MMM/MMP';
            $stdObj->ranking = 3.6;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MMM/MMP';
            $stdObj->ucaspoints = 226.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MMM';
            $stdObj->shortvalue = 'MMM';
            $stdObj->ranking = 4;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(4, 240, 34.0,0.0, $targetQualID, 'MMM'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MMM/DMM';
            $stdObj->shortvalue = 'MMM/DMM';
            $stdObj->ranking = 4.3;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MMM/DMM';
            $stdObj->ucaspoints = 253.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 4.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DMM/MMM';
            $stdObj->shortvalue = 'DMM/MMM';
            $stdObj->ranking = 4.6;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DMM/MMM';
            $stdObj->ucaspoints = 266.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 4.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DMM';
            $stdObj->shortvalue = 'DMM';
            $stdObj->ranking = 5;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(5, 280,38.2,34.0,$targetQualID, 'DMM'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DMM/DDM';
            $stdObj->shortvalue = 'DMM/DDM';
            $stdObj->ranking = 5.3;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DMM/DDM';
            $stdObj->ucaspoints = 293.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 5.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DDM/DMM';
            $stdObj->shortvalue = 'DDM/DMM';
            $stdObj->ranking = 5.6;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DDM/DMM';
            $stdObj->ucaspoints = 306.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 5.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DDM';
            $stdObj->shortvalue = 'DDM';
            $stdObj->ranking = 6;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(6, 320,38.2,44.8,$targetQualID, 'DDM'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DDM/DDD';
            $stdObj->shortvalue = 'DDM/DDD';
            $stdObj->ranking = 6.3;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DDM/DDD';
            $stdObj->ucaspoints = 333.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 6.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DDD/DDM';
            $stdObj->shortvalue = 'DDD/DDM';
            $stdObj->ranking = 6.6;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DDD/DDM';
            $stdObj->ucaspoints = 346.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 6.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DDD';
            $stdObj->shortvalue = 'DDD';
            $stdObj->ranking = 7;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(7, 360,46.6,44.8,$targetQualID, 'DDD'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DDD/D*DD';
            $stdObj->shortvalue = 'DDD/D*DD';
            $stdObj->ranking = 7.3;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DDD/D*DD';
            $stdObj->ucaspoints = 366.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 7.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*DD/DDD';
            $stdObj->shortvalue = 'D*DD/DDD';
            $stdObj->ranking = 7.6;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*DD/DDD';
            $stdObj->ucaspoints = 373.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 7.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*DD';
            $stdObj->shortvalue = 'D*DD';
            $stdObj->ranking = 8;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'DDD*'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('D*DD', $record->id));
            }
            
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(8, 380,50.2,46.6,$targetQualID, 'D*DD'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*DD/D*D*D';
            $stdObj->shortvalue = 'D*DD/D*D*D';
            $stdObj->ranking = 8.3;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*DD/D*D*D';
            $stdObj->ucaspoints = 386.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 8.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*D*D/D*DD';
            $stdObj->shortvalue = 'D*D*D/D*DD';
            $stdObj->ranking = 8.6;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*D*D/D*DD';
            $stdObj->ucaspoints = 393.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 8.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*D*D';
            $stdObj->shortvalue = 'D*D*D';
            $stdObj->ranking = 9;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'DD*D*'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('D*D*D', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(9, 400, $targetQualID, 'D*D*D'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*D*D/D*D*D*';
            $stdObj->shortvalue = 'D*D*D/D*D*D*';
            $stdObj->ranking = 9.3;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*D*D/D*D*D*';
            $stdObj->ucaspoints = 406.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 9.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*D*D*/D*D*D';
            $stdObj->shortvalue = 'D*D*D*/D*D*D';
            $stdObj->ranking = 9.6;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*D*D*/D*D*D';
            $stdObj->ucaspoints = 413.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 9.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*D*D*';
            $stdObj->shortvalue = 'D*D*D*';
            $stdObj->ranking = 10;
            $stdObj->context = 'assessment';
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(10, 420,58.0,50.2,$targetQualID, 'D*D*D*'));
        }
        
        //Diploma
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(3, 2, 3));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'PP';
            $stdObj->shortvalue = 'PP';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(1, 80, $targetQualID, 'PP'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'PP/MP';
            $stdObj->shortvalue = 'PP/MP';
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'PP/MP';
            $stdObj->ucaspoints = 93.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'MP/PP';
            $stdObj->shortvalue = 'MP/PP';
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MP/PP';
            $stdObj->ucaspoints = 106.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'MP';
            $stdObj->shortvalue = 'MP';
            $stdObj->ranking = 2;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(2, 120, $targetQualID, 'MP'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'MP/MM';
            $stdObj->shortvalue = 'MP/MM';
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MP/MM';
            $stdObj->ucaspoints = 133.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'MM/MP';
            $stdObj->shortvalue = 'MM/MP';
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MM/MP';
            $stdObj->ucaspoints = 146.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'MM';
            $stdObj->shortvalue = 'MM';
            $stdObj->ranking = 3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(3, 160,34.0,0,$targetQualID, 'MM'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'MM/DM';
            $stdObj->shortvalue = 'MM/DM';
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MM/DM';
            $stdObj->ucaspoints = 173.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'DM/MM';
            $stdObj->shortvalue = 'DM/MM';
            $stdObj->ranking = 3.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DM/MM';
            $stdObj->ucaspoints = 186.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.6;
            $stdObj->entryscoreupper = 35.8;
            $stdObj->entryscorelower = 34.0;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'DM';
            $stdObj->shortvalue = 'DM';
            $stdObj->ranking = 4;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(4, 200,41.2,35.8,$targetQualID, 'DM'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'DM/DD';
            $stdObj->shortvalue = 'DM/DD';
            $stdObj->ranking = 4.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DM/DD';
            $stdObj->ucaspoints = 213.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 4.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'DD/DM';
            $stdObj->shortvalue = 'DD/DM';
            $stdObj->ranking = 4.6;
            $stdObj->entryscoreupper = 43.0;
            $stdObj->entryscorelower = 41.2;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DD/DM';
            $stdObj->ucaspoints = 226.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 4.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'DD';
            $stdObj->shortvalue = 'DD';
            $stdObj->ranking = 5;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(5, 240,46.6,43.0,$targetQualID, 'DD'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'DD/D*D';
            $stdObj->shortvalue = 'DD/D*D';
            $stdObj->ranking = 5.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DD/D*D';
            $stdObj->ucaspoints = 246.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 5.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'D*D/DD';
            $stdObj->shortvalue = 'D*D/DD';
            $stdObj->ranking = 5.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*D/DD';
            $stdObj->ucaspoints = 253.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 5.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'D*D';
            $stdObj->shortvalue = 'D*D';
            $stdObj->ranking = 6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(6, 260,50.2,46.6,$targetQualID, 'D*D'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'D*D/D*D*';
            $stdObj->shortvalue = 'D*D/D*D*';
            $stdObj->ranking = 6.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*D/D*D*';
            $stdObj->ucaspoints = 266.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 6.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'D*D*/D*D';
            $stdObj->shortvalue = 'D*D*/D*D';
            $stdObj->ranking = 6.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*D*/D*D';
            $stdObj->ucaspoints = 273.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 6.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->bcgttypeid = -1;
            $stdObj->context = 'assessment';
            $stdObj->value = 'D*D*';
            $stdObj->shortvalue = 'D*D*';
            $stdObj->ranking = 7;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(7, 280,58.0,50.2,$targetQualID, 'D*D*'));
        }
        
        //90 cred Diploma
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(3, 2, 9));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'PP';
            $stdObj->shortvalue = 'PP';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(1, 60, $targetQualID, 'PP'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'PP/MP';
            $stdObj->shortvalue = 'PP/MP';
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'PP/MP';
            $stdObj->ucaspoints = 73.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MP/PP';
            $stdObj->shortvalue = 'MP/PP';
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MP/PP';
            $stdObj->ucaspoints = 86.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MP';
            $stdObj->shortvalue = 'MP';
            $stdObj->ranking = 2;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(2, 100, $targetQualID, 'MP'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MP/MM';
            $stdObj->shortvalue = 'MP/MM';
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MP/MM';
            $stdObj->ucaspoints = 106.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MM/MP';
            $stdObj->shortvalue = 'MM/MP';
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MM/MP';
            $stdObj->ucaspoints = 113.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MM';
            $stdObj->shortvalue = 'MM';
            $stdObj->ranking = 3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(3, 120,34.0,0,$targetQualID, 'MM'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'MM/DM';
            $stdObj->shortvalue = 'MM/DM';
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'MM/DM';
            $stdObj->ucaspoints = 133.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DM/MM';
            $stdObj->shortvalue = 'DM/MM';
            $stdObj->ranking = 3.6;
            $stdObj->entryscoreupper = 35.8;
            $stdObj->entryscorelowet = 34.0;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DM/MM';
            $stdObj->ucaspoints = 146.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DM';
            $stdObj->shortvalue = 'DM';
            $stdObj->ranking = 4;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(4, 160,41.2,35.8,$targetQualID, 'DM'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DM/DD';
            $stdObj->shortvalue = 'DM/DD';
            $stdObj->ranking = 4.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DM/DD';
            $stdObj->ucaspoints = 166.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 4.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DD/DM';
            $stdObj->shortvalue = 'DD/DM';
            $stdObj->ranking = 4.6;
            $stdObj->enryscoreupper = 43.0;
            $stdObj->entryscorelower = 41.2;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DD/DM';
            $stdObj->ucaspoints = 173.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 4.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DD';
            $stdObj->shortvalue = 'DD';
            $stdObj->ranking = 5;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(5, 180,46.6,43.0,$targetQualID, 'DD'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'DD/D*D';
            $stdObj->shortvalue = 'DD/D*D';
            $stdObj->ranking = 5.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'DD/D*D';
            $stdObj->ucaspoints = 186.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 5.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*D/DD';
            $stdObj->shortvalue = 'D*D/DD';
            $stdObj->ranking = 5.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*D/DD';
            $stdObj->ucaspoints = 193.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 5.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*D';
            $stdObj->shortvalue = 'D*D';
            $stdObj->ranking = 6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(6, 200,50.2,46.6,$targetQualID, 'D*D'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*D/D*D*';
            $stdObj->shortvalue = 'D*D/D*D*';
            $stdObj->ranking = 6.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*D/D*D*';
            $stdObj->ucaspoints = 203.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 6.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*D*/D*D';
            $stdObj->shortvalue = 'D*D*/D*D';
            $stdObj->ranking = 6.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*D*/D*D';
            $stdObj->ucaspoints = 206.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 6.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*D*';
            $stdObj->shortvalue = 'D*D*';
            $stdObj->ranking = 7;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(7, 210,58.0,50.2,$targetQualID, 'D*D*'));
        }
        
        //subsid Diploma
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(3, 2, 4));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P';
            $stdObj->shortvalue = 'P';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(1, 40, $targetQualID, 'P'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P/M';
            $stdObj->shortvalue = 'P/M';
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'P/M';
            $stdObj->ucaspoints = 53.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/P';
            $stdObj->shortvalue = 'M/P';
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/P';
            $stdObj->ucaspoints = 66.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M';
            $stdObj->shortvalue = 'M';
            $stdObj->ranking = 2;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(2, 80, 35.8,0,$targetQualID, 'M'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/D';
            $stdObj->shortvalue = 'M/D';
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/D';
            $stdObj->ucaspoints = 93.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/M';
            $stdObj->shortvalue = 'D/M';
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/M';
            $stdObj->ucaspoints = 106.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.6;
            $stdObj->entryscoreupper = 41.2;
            $stdObj->entryscorelowet = 35.8;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D';
            $stdObj->shortvalue = 'D';
            $stdObj->ranking = 3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(3, 120,46.6,41.2,$targetQualID, 'D'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/D*';
            $stdObj->shortvalue = 'D/D*';
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/D*';
            $stdObj->ucaspoints = 126.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*/D';
            $stdObj->shortvalue = 'D*/D';
            $stdObj->ranking = 3.6;
            $stdObj->entryscoreupper = 48.4;
            $stdObj->entryscorelower = 46.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/D*';
            $stdObj->ucaspoints = 133.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*';
            $stdObj->shortvalue = 'D*';
            $stdObj->ranking = 4;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(4, 140,58.0,50.2,$targetQualID, 'D*'));
        }
        
        //cert
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(3, 2, 5));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P';
            $stdObj->shortvalue = 'P';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(1, 20, $targetQualID, 'P'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P/M';
            $stdObj->shortvalue = 'P/M';
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'P/M';
            $stdObj->ucaspoints = 26.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/P';
            $stdObj->shortvalue = 'M/P';
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/P';
            $stdObj->ucaspoints = 33.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M';
            $stdObj->shortvalue = 'M';
            $stdObj->ranking = 2;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(2, 40,35.8,0,$targetQualID, 'M'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/D';
            $stdObj->shortvalue = 'M/D';
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/D';
            $stdObj->ucaspoints = 46.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/M';
            $stdObj->shortvalue = 'D/M';
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/M';
            $stdObj->ucaspoints = 53.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.6;
            $stdObj->entryscoreupper = 41.2;
            $stdObj->entryscorelower = 38.2;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D';
            $stdObj->shortvalue = 'D';
            $stdObj->ranking = 3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(3, 60,46.6,41.2,$targetQualID, 'D'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/D*';
            $stdObj->shortvalue = 'D/D*';
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/D*';
            $stdObj->ucaspoints = 63.3;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*/D';
            $stdObj->shortvalue = 'D*/D';
            $stdObj->ranking = 3.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*/D';
            $stdObj->ucaspoints = 66.6;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.6;
            $stdObj->entryscoreupper = 48.4;
            $stdObj->entryscorelower = 46.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*';
            $stdObj->shortvalue = 'D*';
            $stdObj->ranking = 4;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(4, 70,58.0,48.4,$targetQualID, 'D*'));
        }
        
        //Level 4 HNC
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(4, 3, 7));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P';
            $stdObj->shortvalue = 'P';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Pass'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('P', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(1, 0, $targetQualID, 'P'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P/M';
            $stdObj->shortvalue = 'P/M';
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'P/M';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/P';
            $stdObj->shortvalue = 'M/P';
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/P';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M';
            $stdObj->shortvalue = 'M';
            $stdObj->ranking = 2;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Merit'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('M', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(2, 0, $targetQualID, 'M'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/D';
            $stdObj->shortvalue = 'M/D';
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/D';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/M';
            $stdObj->shortvalue = 'D/M';
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/M';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D';
            $stdObj->shortvalue = 'D';
            $stdObj->ranking = 3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Distinction'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('D', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(3, 0, $targetQualID, 'D'));
        }
        
        //Level 5 HND
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(5, 3, 8));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P';
            $stdObj->shortvalue = 'P';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Pass'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('P', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(1, 0, $targetQualID, 'P'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P/M';
            $stdObj->shortvalue = 'P/M';
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'P/M';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/P';
            $stdObj->shortvalue = 'M/P';
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/P';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M';
            $stdObj->shortvalue = 'M';
            $stdObj->ranking = 2;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Merit'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('M', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(2, 0, $targetQualID, 'M'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/D';
            $stdObj->shortvalue = 'M/D';
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/D';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/M';
            $stdObj->shortvalue = 'D/M';
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/M';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D';
            $stdObj->shortvalue = 'D';
            $stdObj->ranking = 3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //UPdate the target breakdown with ucas points and ranking
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Disticntion'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('D', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(3, 0, $targetQualID, 'D'));
        }
        
        //Level 3 Found Diploma
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(3, 4, 10));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P';
            $stdObj->shortvalue = 'P';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Pass'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('P', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(1, 165, $targetQualID, 'P'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P/M';
            $stdObj->shortvalue = 'P/M';
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'P/M';
            $stdObj->ucaspoints = 185;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/P';
            $stdObj->shortvalue = 'M/P';
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/P';
            $stdObj->ucaspoints = 205;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M';
            $stdObj->shortvalue = 'M';
            $stdObj->ranking = 2;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Merit'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('M', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(2, 225, $targetQualID, 'M'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/D';
            $stdObj->shortvalue = 'M/D';
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/D';
            $stdObj->ucaspoints = 245;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/M';
            $stdObj->shortvalue = 'D/M';
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/M';
            $stdObj->ucaspoints = 265;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D';
            $stdObj->shortvalue = 'D';
            $stdObj->ranking = 3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Distinction'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('D', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(3, 285, $targetQualID, 'D'));
        }
        
        //Level 2 Award
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(2, 2, 6));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P';
            $stdObj->shortvalue = 'P';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Pass'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('P', $record->id));
            }
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P/M';
            $stdObj->shortvalue = 'P/M';
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'P/M';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/P';
            $stdObj->shortvalue = 'M/P';
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/P';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);

            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M';
            $stdObj->shortvalue = 'M';
            $stdObj->ranking = 2;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Merit'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('M', $record->id));
            }

            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/D';
            $stdObj->shortvalue = 'M/D';
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/D';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);

            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/M';
            $stdObj->shortvalue = 'D/M';
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/M';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);

            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D';
            $stdObj->shortvalue = 'D';
            $stdObj->ranking = 3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Distinction'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('D', $record->id));
            }

            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/D*';
            $stdObj->shortvalue = 'D/D*';
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/D*';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);

            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*/D';
            $stdObj->shortvalue = 'D*/D';
            $stdObj->ranking = 3.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*/D';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);

            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*';
            $stdObj->shortvalue = 'D*';
            $stdObj->ranking = 4;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Distinction*'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('D*', $record->id));
            }
        }
        
        //Level 2 Cert
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(2, 2, 5));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P';
            $stdObj->shortvalue = 'P';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Pass'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('P', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(1, 0, $targetQualID, 'P'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P/M';
            $stdObj->shortvalue = 'P/M';
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'P/M';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/P';
            $stdObj->shortvalue = 'M/P';
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/P';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M';
            $stdObj->shortvalue = 'M';
            $stdObj->ranking = 2;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Merit'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('M', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(2, 0, $targetQualID, 'M'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/D';
            $stdObj->shortvalue = 'M/D';
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/D';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/M';
            $stdObj->shortvalue = 'D/M';
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/M';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D';
            $stdObj->shortvalue = 'D';
            $stdObj->ranking = 3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Distinction'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('D', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(3, 0, $targetQualID, 'D'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/D*';
            $stdObj->shortvalue = 'D/D*';
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/D*';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*/D';
            $stdObj->shortvalue = 'D*/D';
            $stdObj->ranking = 3.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*/D';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*';
            $stdObj->shortvalue = 'D*';
            $stdObj->ranking = 4;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Distinction*'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('D*', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(4, 0, $targetQualID, 'D*'));
        }
        
        //Level 2 Ext Cert
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(2, 2, 11));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P';
            $stdObj->shortvalue = 'P';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Pass'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('P', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(1, 0, $targetQualID, 'P'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P/M';
            $stdObj->shortvalue = 'P/M';
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'P/M';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/P';
            $stdObj->shortvalue = 'M/P';
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/P';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M';
            $stdObj->shortvalue = 'M';
            $stdObj->ranking = 2;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Merit'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('M', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(2, 0, $targetQualID, 'M'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/D';
            $stdObj->shortvalue = 'M/D';
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/D';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/M';
            $stdObj->shortvalue = 'D/M';
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/M';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D';
            $stdObj->shortvalue = 'D';
            $stdObj->ranking = 3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Distinction'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('D', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(3, 0, $targetQualID, 'D'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/D*';
            $stdObj->shortvalue = 'D/D*';
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/D*';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*/D';
            $stdObj->shortvalue = 'D*/D';
            $stdObj->ranking = 3.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*/D';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*';
            $stdObj->shortvalue = 'D*';
            $stdObj->ranking = 4;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Distinction*'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('D*', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(4, 0, $targetQualID, 'D*'));
        }
        
        //Level 2 Dip
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(2, 2, 3));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P';
            $stdObj->shortvalue = 'P';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Pass'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('P', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(1, 0, $targetQualID, 'P'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P/M';
            $stdObj->shortvalue = 'P/M';
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'P/M';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/P';
            $stdObj->shortvalue = 'M/P';
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/P';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 1.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M';
            $stdObj->shortvalue = 'M';
            $stdObj->ranking = 2;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Merit'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('M', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(2, 0, $targetQualID, 'M'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'M/D';
            $stdObj->shortvalue = 'M/D';
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'M/D';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/M';
            $stdObj->shortvalue = 'D/M';
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/M';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 2.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D';
            $stdObj->shortvalue = 'D';
            $stdObj->ranking = 3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Distinction'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('D', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(3, 0, $targetQualID, 'D'));
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D/D*';
            $stdObj->shortvalue = 'D/D*';
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D/D*';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.3;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*/D';
            $stdObj->shortvalue = 'D*/D';
            $stdObj->ranking = 3.6;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            //insert this as a target breeakdown.
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->targetgrade = 'D*/D';
            $stdObj->ucaspoints = 0;
            $stdObj->unitscorelower = -1;
            $stdObj->unitsscoreupper = -1;
            $stdObj->ranking = 3.6;
            $DB->insert_record('block_bcgt_target_breakdown', $stdObj);
            
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'D*';
            $stdObj->shortvalue = 'D*';
            $stdObj->ranking = 4;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Distinction*'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('D*', $record->id));
            }
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(4, 0, $targetQualID, 'D*'));
        }
        
        //Level 1 Award
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(1, 5, 6));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P';
            $stdObj->shortvalue = 'P';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Pass'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('P', $record->id));
            }
        }
        
        //Level 1 Certificate
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(1, 5, 5));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P';
            $stdObj->shortvalue = 'P';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Pass'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('P', $record->id));
            }
        }
        
        //Level 1 Diploma
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(1, 5, 3));
        if($record)
        {
            $targetQualID = $record->id;
            $stdObj = new stdClass();
            $stdObj->bcgttargetqualid = $targetQualID;
            $stdObj->context = 'assessment';
            $stdObj->bcgttypeid = -1;
            $stdObj->value = 'P';
            $stdObj->shortvalue = 'P';
            $stdObj->ranking = 1;
            $DB->insert_record('block_bcgt_value', $stdObj);
            
            $record = $DB->get_record_sql("SELECT * FROM {block_bcgt_target_breakdown} 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array($targetQualID, 'Pass'));
            if($record)
            {
                $DB->execute("UPDATE {block_bcgt_target_breakdown} SET targetgrade = ? WHERE id = ?", array('P', $record->id));
            }
        }
        
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
                $stdObj->ucaspoints = $record->ucaspoints;
                $stdObj->ranking = $record->ranking;
                $stdObj->upperscore = $record->entryscoreupper;
                $stdObj->lowerscore = $record->entryscorelower;
                $DB->insert_record('block_bcgt_target_grades', $stdObj);
            }
        }
    }
    
    if($oldversion < 2013091003)
    {
        //want to find the reffered value if it exists. 
        $sql = "SELECT * FROM {block_bcgt_value} value WHERE shortvalue = ?";
        $record = $DB->get_record_sql($sql , array('Reffered'));
        if($record)
        {
            $record->shortvalue = 'Referred';
            $DB->update_record('block_bcgt_value', $record);
        }
    }
    
    if($oldversion < 2013091003)
    {
        //due to an installation error we dont have extended certificate
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
    }
    
    if($oldversion < 2013091502)
    {
        //level 3 ext dip 
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(3, 2, 2));
        if($record)
        {
            $targetQualID = $record->id;
    
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(5, 280,38.2,34.0,$targetQualID, 'DMM'));
            
            $DB->execute("UPDATE {block_bcgt_target_grades} SET ranking = ?, ucaspoints = ?, upperscore = ?, 
            lowerscore = ? 
                WHERE bcgttargetqualid = ? AND grade = ?", array(5, 280,38.2,34.0,$targetQualID, 'DMM'));
            
            //UPdate the target breakdown with ucas points and ranking
            $DB->execute("UPDATE {block_bcgt_target_breakdown} SET ranking = ?, ucaspoints = ?, entryscoreupper = ?, 
            entryscorelower = ? 
                WHERE bcgttargetqualid = ? AND targetgrade = ?", array(6, 320,44.8,38.2,$targetQualID, 'DDM'));
            
            $DB->execute("UPDATE {block_bcgt_target_grades} SET ranking = ?, ucaspoints = ?, upperscore = ?, 
            lowerscore = ? 
                WHERE bcgttargetqualid = ? AND grade = ?", array(6, 320,44.8,38.2,$targetQualID, 'DDM'));
        }
    }
    
    if($oldversion < 2013101801)
    {
        //want to find the reffered value if it exists. 
        $sql = "SELECT * FROM {block_bcgt_value} value WHERE shortvalue = ?";
        $record = $DB->get_record_sql($sql , array('Reffered'));
        if($record)
        {
            $record->shortvalue = 'Referred';
            $DB->update_record('block_bcgt_value', $record);
        }
    }
    
    if($oldversion < 2013112610)
    {
        //targetqualid of LEVEL 3 BTEC subsidiary is: 13
        //found old missing BTEC Subsid
        
        //THE WRONG D/M
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ?";
        $record = $DB->get_record_sql($sql, array(13, 'D/M'));
        if($record)
        {
            $record->entryscorelower = 35.8;
            $DB->update_record('block_bcgt_target_breakdown',$record);
        }
        
        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ?";
        $record = $DB->get_record_sql($sql, array(13, 'D/M'));
        if($record)
        {
            $record->lowerscore = 35.8;
            $DB->update_record('block_bcgt_target_grades',$record);
        }
        
        
        
        //THE wrong D/D*
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? AND ranking = ?";
        $record = $DB->get_record_sql($sql, array(13, 'D/D*', 3.6));
        if($record)
        {
            $record->targetgrade = 'D*/D';
            $record->entryscoreupper = 48.4;
            $record->entryscorelower = 46.6;
            $DB->update_record('block_bcgt_target_breakdown',$record);
        }
        else
        {
            $record = $DB->get_record_sql($sql, array(13, 'D*/D', 3.6));
            if($record)
            {
                $record->entryscoreupper = 48.4;
                $record->entryscorelower = 46.6;
                $DB->update_record('block_bcgt_target_breakdown',$record);
            }
        }

        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? AND ranking = ?";
        $record = $DB->get_record_sql($sql, array(13, 'D/D*', 3.6));
        if($record)
        {
            $record->grade = 'D*/D';
            $record->upperscore = 48.4;
            $record->lowerscore = 46.6;
            $DB->update_record('block_bcgt_target_grades',$record);
        }
        
        
        //The wrong D*
        
        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ?";
        $record = $DB->get_record_sql($sql, array(13, 'D*'));
        if($record)
        {
            $record->lowerscore = 48.4;
            $DB->update_record('block_bcgt_target_grades',$record);
        }
        
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ?";
        $record = $DB->get_record_sql($sql, array(13, 'D*'));
        if($record)
        {
            $record->entryscorelower = 48.4;
            $DB->update_record('block_bcgt_target_breakdown',$record);
        }
    }
    
    if($oldversion < 2013112700)
    {
        //targetqualid of LEVEL 3 BTEC diploma is: 15
        //found old missing BTEC diploma points
        
        //missing between 43.00 and 41.20
        //DD/DM is missing the points:
        //43.0 -> 41.2;

        //THE WRONG D/M
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ?";
        $record = $DB->get_record_sql($sql, array(15, 'DD/DM'));
        if($record)
        {
            $record->entryscorelower = 41.2;
            $record->entryscoreupper = 43.0;
            $DB->update_record('block_bcgt_target_breakdown',$record);
        }
        
        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ?";
        $record = $DB->get_record_sql($sql, array(15, 'DD/DM'));
        if($record)
        {
            $record->lowerscore = 41.2;
            $record->upperscore = 43.0;
            $DB->update_record('block_bcgt_target_grades',$record);
        }
        
        //targetqualid of LEVEL 3 BTEC 90-credit diploma is: 14
        //found old missing BTEC diploma points
        
        //missing between 43.00 and 41.20
        //DD/DM is missing the points:
        //43.0 -> 41.2;

        //THE WRONG D/M
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ?";
        $record = $DB->get_record_sql($sql, array(14, 'DD/DM'));
        if($record)
        {
            $record->entryscorelower = 41.2;
            $record->entryscoreupper = 43.0;
            $DB->update_record('block_bcgt_target_breakdown',$record);
        }
        
        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ?";
        $record = $DB->get_record_sql($sql, array(14, 'DD/DM'));
        if($record)
        {
            $record->lowerscore = 41.2;
            $record->upperscore = 43.0;
            $DB->update_record('block_bcgt_target_grades',$record);
        }
    }
    
    if($oldversion < 2013112900)
    {
        //targetqualid of LEVEL 3 BTEC 90-credit diploma is: 14
        //found old missing BTEC diploma points
        
        //missing between 35.8 and 34
        //DD/DM is missing the points:
        //35.8 -> 34

        //THE WRONG DM/MM
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ?";
        $record = $DB->get_record_sql($sql, array(14, 'DM/MM'));
        if($record)
        {
            $record->entryscorelower = 34;
            $record->entryscoreupper = 35.8;
            $DB->update_record('block_bcgt_target_breakdown',$record);
        }
        
        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ?";
        $record = $DB->get_record_sql($sql, array(14, 'DM/MM'));
        if($record)
        {
            $record->lowerscore = 34;
            $record->upperscore = 35.8;
            $DB->update_record('block_bcgt_target_grades',$record);
        }
    }
    
    if($oldversion < 2013120500)
    {
        //need to insert the missing specdesc
        $sql = "SELECT * FROM {block_bcgt_type} WHERE type = ?";
        $BTEC = $DB->get_record_sql($sql, array('BTEC'));
        if($BTEC)
        {
            $BTEC->specificationdesc = 'BTEC Nationals From 2010';
            $DB->update_record('block_bcgt_type', $BTEC);
        }
        
        $BTECH = $DB->get_record_sql($sql, array('BTEC Higher'));
        if($BTECH)
        {
            $BTECH->specificationdesc = 'BTEC Higher Nationals from 2010';
            $DB->update_record('block_bcgt_type', $BTECH);
        }
        
        $BTECL = $DB->get_record_sql($sql, array('BTEC Lower'));
        if($BTECL)
        {
            $BTECL->specificationdesc = 'BTEC Firsts 2010';
            $DB->update_record('block_bcgt_type', $BTECL);
        }
        
        $BTECF = $DB->get_record_sql($sql, array('BTEC Foundation'));
        if($BTECF)
        {
            $BTECF->specificationdesc = 'BTEC Foundation Diploma in Art and Design';
            $DB->update_record('block_bcgt_type', $BTECF);
        }
        
        $sql = "SELECT * FROM {block_bcgt_type} WHERE type = ?";
        $record = $DB->get_record_sql($sql, array('BTEC Firsts 2013'));
        if(!$record)
        {
            $DB->execute("INSERT INTO {block_bcgt_type} (id,type,bcgttypefamilyid,specificationdesc) 
        VALUES (12,'BTEC First 2013',2,'BTEC Firsts 2013')");
        }
          
        $sql = "SELECT * FROM {block_bcgt_unit_type} WHERE type = ? AND bcgttypeid = ?";
        $record = $DB->get_record_sql($sql, array('Externally Assessed', 12));
        if(!$record)
        {
            //new unit type:
            $stdObj = new stdClass();
            $stdObj->type = 'Externally Assessed';
            $stdObj->bcgttypeid = 12;
            $DB->insert_record('block_bcgt_unit_type', $stdObj);
        }
        
        $sql = "SELECT * FROM {block_bcgt_unit_type} WHERE type = ? AND bcgttypeid = ?";
        $record = $DB->get_record_sql($sql, array('Internally Assessed', 12));
        if(!$record)
        {
            $stdObj = new stdClass();
            $stdObj->type = 'Internally Assessed';
            $stdObj->bcgttypeid = 12;
            $DB->insert_record('block_bcgt_unit_type', $stdObj);
        }
        
        //new targetquals for:
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? 
            AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(6, 12, 6));
        if(!$record)
        {
            /*1.BTECFirst2013,Level 1 & 2,Award default credits = 120 
            * level = 7, type = 12. Award = 6*/
            $record = new stdClass();
            $record->bcgtlevelid = 7;
            $record->bcgttypeid = 12;
            $record->bcgtsubtypeid = 6;
            $record->previoustargetqualid = -1;
            $l12Award = $DB->insert_record('block_bcgt_target_qual', $record);
            
            $record = new stdClass();
            $record->bcgttargetqualid = $l12Award;
            $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
            $record->value = 120;
            $DB->insert_record('block_bcgt_target_qual_att', $record); 
        }
        else
        {
            $l12Award = $record->id;
        }
        
        
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? 
            AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(6, 12, 5));
        if(!$record)
        {
            /*2.BTECFirst2013, Level 1 & 2, Certificate, default credits = 240
             * level = 7, type = 12. Certificate = 5*/
            $record = new stdClass();
            $record->bcgtlevelid = 7;
            $record->bcgttypeid = 12;
            $record->bcgtsubtypeid = 5;
            $record->previoustargetqualid = -1;
            $l12Cert = $DB->insert_record('block_bcgt_target_qual', $record);

            $record = new stdClass();
            $record->bcgttargetqualid = $l12Cert;
            $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
            $record->value = 240;
            $DB->insert_record('block_bcgt_target_qual_att', $record); 
        }
        else
        {
            $l12Cert = $record->id;
        }
        
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? 
            AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(6, 12, 11));
        if(!$record)
        {
            /*3.BTECFirs2013, Level 1 & 2, Extended Certificate, default credits = 360
             * level = 7, type = 12. Extended Certificate = 11*/
            $record = new stdClass();
            $record->bcgtlevelid = 7;
            $record->bcgttypeid = 12;
            $record->bcgtsubtypeid = 11;
            $record->previoustargetqualid = -1;
            $l12ExtCert = $DB->insert_record('block_bcgt_target_qual', $record);

            $record = new stdClass();
            $record->bcgttargetqualid = $l12ExtCert;
            $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
            $record->value = 360;
            $DB->insert_record('block_bcgt_target_qual_att', $record); 
        }
        else
        {
            $l12ExtCert = $record->id;
        }
        //DEFAULT NUMBER OF CREDITS
        
        $sql = "SELECT * FROM {block_bcgt_target_qual} WHERE bcgtlevelid = ? AND bcgttypeid = ? 
            AND bcgtsubtypeid = ?";
        $record = $DB->get_record_sql($sql, array(6, 12, 3));
        if(!$record)
        {
            /*4.BTECFirst2013, Level 1 & 2, Diploma, default credits = 480
                * level = 7, type = 12. Diploma = 3*/
            $record = new stdClass();
            $record->bcgtlevelid = 7;
            $record->bcgttypeid = 12;
            $record->bcgtsubtypeid = 3;
            $record->previoustargetqualid = -1;
            $l12Dip = $DB->insert_record('block_bcgt_target_qual', $record);

            $record = new stdClass();
            $record->bcgttargetqualid = $l12Dip;
            $record->name = BTECSubType::DEFAULTNUMBEROFCREDITSNAME;
            $record->value = 480;
            $DB->insert_record('block_bcgt_target_qual_att', $record); 
            //DEFAULT NUMBER OF CREDITS
        }
        else
        {
            $l12Dip = $record->id;
        }
        
        $record = new stdClass();
        $record->bcgtlevelid = 7;
        $record->name = BTECUNIT::DEFAULTUNITCREDITSNAME;
        $record->bcgttypefamilyid = 2;
        $record->value = 30;
        $DB->insert_record('block_bcgt_unit_type_att', $record);
        
        //The breakdowns:
        //Award:
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'U';
        $record->unitsscorelower = 0;
        $record->unitsscoreupper = 24;
        $record->ranking = 1;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'U/Level 1';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 1/U';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 1';
        $record->unitsscorelower = 24;
        $record->unitsscoreupper = 48;
        $record->ranking = 2;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 1/Level 2 Pass';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 2 Pass/Level 1';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 2 Pass';
        $record->unitsscorelower = 48;
        $record->unitsscoreupper = 66;
        $record->ranking = 3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 2 Pass/Level 2 Merit';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 2 Merit/Level 2 Pass';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 2 Merit';
        $record->unitsscorelower = 66;
        $record->unitsscoreupper = 84;
        $record->ranking = 4;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 2 Merit/Level 2 Distinction';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 2 Distinction/Level 2 Merit';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 2 Distinction';
        $record->unitsscorelower = 84;
        $record->unitsscoreupper = 90;
        $record->ranking = 5;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 2 Distinction/Level 2 Distinction *';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 2 Distinction */Level 2 Distinction';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Award;      
        $record->targetgrade = 'Level 2 Distinction *';
        $record->unitsscorelower = 90;
        $record->unitsscoreupper = 200;
        $record->ranking = 6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        //Certificate
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'U';
        $record->unitsscorelower = 0;
        $record->unitsscoreupper = 48;
        $record->ranking = 1;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'U/Level 1';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 1/U';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 1';
        $record->unitsscorelower = 48;
        $record->unitsscoreupper = 96;
        $record->ranking = 2;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 1/Level 2 PP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 PP/Level 1';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 PP';
        $record->unitsscorelower = 96;
        $record->unitsscoreupper = 114;
        $record->ranking = 3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 PP/Level 2 MP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 MP/Level 2 PP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 MP';
        $record->unitsscorelower = 114;
        $record->unitsscoreupper = 132;
        $record->ranking = 4;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 MP/Level 2 MM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 MM/Level 2 MP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 MM';
        $record->unitsscorelower = 132;
        $record->unitsscoreupper = 150;
        $record->ranking = 5;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 MM/Level 2 DM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 DM/Level 2 MM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 DM';
        $record->unitsscorelower = 150;
        $record->unitsscoreupper = 168;
        $record->ranking = 6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 DM/Level 2 DD';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 6.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 DD/Level 2 DM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 6.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 DD';
        $record->unitsscorelower = 168;
        $record->unitsscoreupper = 174;
        $record->ranking = 7;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 DD/Level 2 D*D';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 7.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 D*D/Level 2 DD';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 7.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 D*D';
        $record->unitsscorelower = 174;
        $record->unitsscoreupper = 180;
        $record->ranking = 8;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 D*D/Level 2 D*D*';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 8.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 D*D*/Level 2 D*D';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 8.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Cert;      
        $record->targetgrade = 'Level 2 D*D*';
        $record->unitsscorelower = 180;
        $record->unitsscoreupper = 300;
        $record->ranking = 9;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        //WRONG!
        
        //Ext Certificate
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'U';
        $record->unitsscorelower = 0;
        $record->unitsscoreupper = 48;
        $record->ranking = 1;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'U/Level 1';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 1/U';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 1';
        $record->unitsscorelower = 48;
        $record->unitsscoreupper = 96;
        $record->ranking = 2;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 1/Level 2 PP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 PP/Level 1';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 PP';
        $record->unitsscorelower = 96;
        $record->unitsscoreupper = 114;
        $record->ranking = 3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 PP/Level 2 MP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 MP/Level 2 PP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 MP';
        $record->unitsscorelower = 114;
        $record->unitsscoreupper = 132;
        $record->ranking = 4;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 MP/Level 2 MM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 MM/Level 2 MP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 MM';
        $record->unitsscorelower = 132;
        $record->unitsscoreupper = 150;
        $record->ranking = 5;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 MM/Level 2 DM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 DM/Level 2 MM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 DM';
        $record->unitsscorelower = 150;
        $record->unitsscoreupper = 168;
        $record->ranking = 6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 DM/Level 2 DD';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 6.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 DD/Level 2 DM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 6.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 DD';
        $record->unitsscorelower = 168;
        $record->unitsscoreupper = 174;
        $record->ranking = 7;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 DD/Level 2 D*D';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 7.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 D*D/Level 2 DD';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 7.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 D*D';
        $record->unitsscorelower = 174;
        $record->unitsscoreupper = 180;
        $record->ranking = 8;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 D*D/Level 2 D*D*';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 8.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 D*D*/Level 2 D*D';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 8.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12ExtCert;      
        $record->targetgrade = 'Level 2 D*D*';
        $record->unitsscorelower = 180;
        $record->unitsscoreupper = 300;
        $record->ranking = 9;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        //Diploma
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'U';
        $record->unitsscorelower = 0;
        $record->unitsscoreupper = 48;
        $record->ranking = 1;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'U/Level 1';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 1/U';
        $record->unitscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 1.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 1';
        $record->unitsscorelower = 48;
        $record->unitsscoreupper = 96;
        $record->ranking = 2;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 1/Level 2 PP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 PP/Level 1';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 2.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 PP';
        $record->unitsscorelower = 96;
        $record->unitsscoreupper = 114;
        $record->ranking = 3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 PP/Level 2 MP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 MP/Level 2 PP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 3.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 MP';
        $record->unitsscorelower = 114;
        $record->unitsscoreupper = 132;
        $record->ranking = 4;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 MP/Level 2 MM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 MM/Level 2 MP';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 4.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 MM';
        $record->unitsscorelower = 132;
        $record->unitsscoreupper = 150;
        $record->ranking = 5;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 MM/Level 2 DM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 DM/Level 2 MM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 5.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 DM';
        $record->unitsscorelower = 150;
        $record->unitsscoreupper = 168;
        $record->ranking = 6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 DM/Level 2 DD';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 6.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 DD/Level 2 DM';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 6.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 DD';
        $record->unitsscorelower = 168;
        $record->unitsscoreupper = 174;
        $record->ranking = 7;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 DD/Level 2 D*D';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 7.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 D*D/Level 2 DD';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 7.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 D*D';
        $record->unitsscorelower = 174;
        $record->unitsscoreupper = 180;
        $record->ranking = 8;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 D*D/Level 2 D*D*';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 8.3;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 D*D*/Level 2 D*D';
        $record->unitsscorelower = -1;
        $record->unitsscoreupper = -1;
        $record->ranking = 8.6;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        $record = new stdClass();
        $record->bcgttargetqualid = $l12Dip;      
        $record->targetgrade = 'Level 2 D*D*';
        $record->unitsscorelower = 180;
        $record->unitsscoreupper = 300;
        $record->ranking = 9;
        $DB->insert_record('block_bcgt_target_breakdown', $record);
        
        //find all of the breakdowns for the targetquals that are for the btec families. 
        $sql = "SELECT distinct(breakdown.id), breakdown.* FROM {block_bcgt_target_breakdown} breakdown 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = breakdown.bcgttargetqualid 
            WHERE targetqual.id IN (?, ?, ?, ?)";
        $records = $DB->get_records_sql($sql, array($l12Award,$l12Cert,$l12ExtCert,$l12Dip));
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
        
        //now the type awards and points::
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
        
    }
    
    if($oldversion < 2014010800)
    {
        //want to find the reffered value if it exists. 
        $sql = "SELECT * FROM {block_bcgt_value} value WHERE shortvalue = ?";
        $record = $DB->get_record_sql($sql , array('Reffered'));
        if($record)
        {
            $record->shortvalue = 'Referred';
            $DB->update_record('block_bcgt_value', $record);
        }
    }
    
    if($oldversion < 2014021700)
    {
	
		// Changing nullability of field ucaspoints on table block_bcgt_target_grades to null
        $table = new xmldb_table('block_bcgt_target_grades');
        $field = new xmldb_field('ucaspoints', XMLDB_TYPE_NUMBER, '5, 2', null, null, null, null, 'grade');

        // Launch change of nullability for field ucaspoints
        $dbman->change_field_notnull($table, $field);
		
	
        //the Pass/ Fa
        //targetr grade
        //breakdown
        //level 1
        $sql = "SELECT distinct(targetqual.id), targetqual.* FROM {block_bcgt_target_qual} targetqual 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} fam ON fam.id = type.bcgttypefamilyid 
            JOIN {block_bcgt_subtype} sub ON sub.id = targetqual.bcgtsubtypeid 
            JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
            WHERE level.trackinglevel = ? AND fam.family = ? 
                AND sub.subtype = ?";
        $record = $DB->get_record_sql($sql, array('Level 1', 'BTEC', 'Award'));
        if($record)
        {
            //do we have any Pass and/or N/A?
            $targetGrade = $DB->get_record_sql('SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ?', array($record->id, 'Pass'));
            if(!$targetGrade)
            {
                $targeGrade = new stdClass();
                $targeGrade->bcgttargetqualid = $record->id;      
                $targeGrade->targetgrade = 'Pass';
                $targeGrade->unitsscorelower = 0;
                $targeGrade->unitsscoreupper = 0;
                $targeGrade->ranking = 1;
                $DB->insert_record('block_bcgt_target_breakdown', $targeGrade);
            }

            $targetGrade = $DB->get_record_sql('SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ?', array($record->id, 'Not Achieved'));
            if(!$targetGrade)
            {
                $targeGrade = new stdClass();
                $targeGrade->bcgttargetqualid = $record->id;      
                $targeGrade->targetgrade = 'Not Achieved';
                $targeGrade->unitsscorelower = 0;
                $targeGrade->unitsscoreupper = 0;
                $targeGrade->ranking = 0;
                $DB->insert_record('block_bcgt_target_breakdown', $targeGrade);
            }

            $targetGrade = $DB->get_record_sql('SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ?', array($record->id, 'Pass'));
            if(!$targetGrade)
            {
                $targeGrade = new stdClass();
                $targeGrade->bcgttargetqualid = $record->id;      
                $targeGrade->grade = 'Pass';
                $targeGrade->unitsscorelower = 0;
                $targeGrade->unitsscoreupper = 0;
                $targeGrade->ranking = 1;
                $DB->insert_record('block_bcgt_target_grades', $targeGrade);
            }

            $targetGrade = $DB->get_record_sql('SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ?', array($record->id, 'Not Achieved'));
            if(!$targetGrade)
            {
                $targeGrade = new stdClass();
                $targeGrade->bcgttargetqualid = $record->id;      
                $targeGrade->grade = 'Not Achieved';
                $targeGrade->unitsscorelower = 0;
                $targeGrade->unitsscoreupper = 0;
                $targeGrade->ranking = 0;
                $DB->insert_record('block_bcgt_target_grades', $targeGrade);
            }
        }

        $record = $DB->get_record_sql($sql, array('Level 1', 'BTEC', 'Certificate'));
        if($record)
        {
            //do we have any Pass and/or N/A?
            $targetGrade = $DB->get_record_sql('SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ?', array($record->id, 'Pass'));
            if(!$targetGrade)
            {
                $targeGrade = new stdClass();
                $targeGrade->bcgttargetqualid = $record->id;      
                $targeGrade->targetgrade = 'Pass';
                $targeGrade->unitsscorelower = 0;
                $targeGrade->unitsscoreupper = 0;
                $targeGrade->ranking = 1;
                $DB->insert_record('block_bcgt_target_breakdown', $targeGrade);
            }

            $targetGrade = $DB->get_record_sql('SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ?', array($record->id, 'Not Achieved'));
            if(!$targetGrade)
            {
                $targeGrade = new stdClass();
                $targeGrade->bcgttargetqualid = $record->id;      
                $targeGrade->targetgrade = 'Not Achieved';
                $targeGrade->unitsscorelower = 0;
                $targeGrade->unitsscoreupper = 0;
                $targeGrade->ranking = 0;
                $DB->insert_record('block_bcgt_target_breakdown', $targeGrade);
            }

            $targetGrade = $DB->get_record_sql('SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ?', array($record->id, 'Pass'));
            if(!$targetGrade)
            {
                $targeGrade = new stdClass();
                $targeGrade->bcgttargetqualid = $record->id;      
                $targeGrade->grade = 'Pass';
                $targeGrade->unitsscorelower = 0;
                $targeGrade->unitsscoreupper = 0;
                $targeGrade->ranking = 1;
                $DB->insert_record('block_bcgt_target_grades', $targeGrade);
            }

            $targetGrade = $DB->get_record_sql('SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ?', array($record->id, 'Not Achieved'));
            if(!$targetGrade)
            {
                $targeGrade = new stdClass();
                $targeGrade->bcgttargetqualid = $record->id;      
                $targeGrade->grade = 'Not Achieved';
                $targeGrade->unitsscorelower = 0;
                $targeGrade->unitsscoreupper = 0;
                $targeGrade->ranking = 0;
                $DB->insert_record('block_bcgt_target_grades', $targeGrade);
            }
        }

        $record = $DB->get_record_sql($sql, array('Level 1', 'BTEC', 'Diploma'));
        if($record)
        {
            //do we have any Pass and/or N/A?
            $targetGrade = $DB->get_record_sql('SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ?', array($record->id, 'Pass'));
            if(!$targetGrade)
            {
                $targeGrade = new stdClass();
                $targeGrade->bcgttargetqualid = $record->id;      
                $targeGrade->targetgrade = 'Pass';
                $targeGrade->unitsscorelower = 0;
                $targeGrade->unitsscoreupper = 0;
                $targeGrade->ranking = 1;
                $DB->insert_record('block_bcgt_target_breakdown', $targeGrade);
            }

            $targetGrade = $DB->get_record_sql('SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ?', array($record->id, 'Not Achieved'));
            if(!$targetGrade)
            {
                $targeGrade = new stdClass();
                $targeGrade->bcgttargetqualid = $record->id;      
                $targeGrade->targetgrade = 'Not Achieved';
                $targeGrade->unitsscorelower = 0;
                $targeGrade->unitsscoreupper = 0;
                $targeGrade->ranking = 0;
                $DB->insert_record('block_bcgt_target_breakdown', $targeGrade);
            }

            $targetGrade = $DB->get_record_sql('SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ?', array($record->id, 'Pass'));
            if(!$targetGrade)
            {
                $targeGrade = new stdClass();
                $targeGrade->bcgttargetqualid = $record->id;      
                $targeGrade->grade = 'Pass';
                $targeGrade->unitsscorelower = 0;
                $targeGrade->unitsscoreupper = 0;
                $targeGrade->ranking = 1;
                $DB->insert_record('block_bcgt_target_grades', $targeGrade);
            }

            $targetGrade = $DB->get_record_sql('SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ?', array($record->id, 'Not Achieved'));
            if(!$targetGrade)
            {
                $targeGrade = new stdClass();
                $targeGrade->bcgttargetqualid = $record->id;      
                $targeGrade->grade = 'Not Achieved';
                $targeGrade->unitsscorelower = 0;
                $targeGrade->unitsscoreupper = 0;
                $targeGrade->ranking = 0;
                $DB->insert_record('block_bcgt_target_grades', $targeGrade);
            }
        }

        //the n/a for all values
        //find all of the BTEC target quals. 
        $sql = "SELECT distinct(targetqual.id), targetqual.* FROM {block_bcgt_target_qual} targetqual 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid WHERE family.family = ?";
        $targetQuals = $DB->get_records_sql($sql, array('BTEC'));
        if($targetQuals)
        {
            foreach($targetQuals AS $targetQual)
            {
                $notA = $DB->get_record_sql("SELECT * FROM {block_bcgt_value} WHERE bcgttargetqualid = ? AND context = ? AND shortvalue = ? AND bcgttypeid = ?", array($targetQual->id, 'assessment', 'N/A', -1));
                if(!$notA)
                {
                    $stdObj = new stdClass();
                    $stdObj->bcgttargetqualid = $targetQual->id;
                    $stdObj->context = 'assessment';
                    $stdObj->bcgttypeid = -1;
                    $stdObj->value = 'Not Achieved';
                    $stdObj->shortvalue = 'N/A';
                    $stdObj->ranking = 0;
                    $DB->insert_record('block_bcgt_value', $stdObj);
                }
            }
        }
        //all values for Level 1/Level 2
        //get all of the target grades for them. 
        $sql = "SELECT distinct(targetqual.id), targetqual.* FROM {block_bcgt_target_qual} targetqual 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} fam ON fam.id = type.bcgttypefamilyid 
            JOIN {block_bcgt_subtype} sub ON sub.id = targetqual.bcgtsubtypeid 
            JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
            WHERE level.trackinglevel = ? AND fam.family = ? 
                AND sub.subtype = ?";
        $First2013l12AwardDB = $DB->get_record_sql($sql, array('Level 1 & 2', 'BTEC', 'Award'));
        $First2013l12Award = $First2013l12AwardDB->id;

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

        $First2013l12CertDB = $DB->get_record_sql($sql, array('Level 1 & 2', 'BTEC', 'Certificate'));
        $First2013l12Cert = $First2013l12CertDB->id;

        $sql2 = "SELECT * FROM {block_bcgt_value} WHERE bcgttargetqualid = ? AND context = ?";
        $records = $DB->get_records_sql($sql2, array($First2013l12Award, 'assessment'));
        if($records)
        {
            foreach($records AS $record)
            {
                $record->bcgttargetqualid = $First2013l12Cert;
                $DB->insert_record('block_bcgt_value', $record);
            }
        }

        $First2013l12ExtCertDB = $DB->get_record_sql($sql, array('Level 1 & 2', 'BTEC', 'Extended Certificate'));
        $First2013l12ExtCert = $First2013l12ExtCertDB->id;
        $records = $DB->get_records_sql($sql2, array($First2013l12Award, 'assessment'));
        if($records)
        {
            foreach($records AS $record)
            {
                $record->bcgttargetqualid = $First2013l12ExtCert;
                $DB->insert_record('block_bcgt_value', $record);
            }
        }

        $First2013l12DipDB = $DB->get_record_sql($sql, array('Level 1 & 2', 'BTEC', 'Diploma'));
        $First2013l12Dip = $First2013l12DipDB->id;
        $records = $DB->get_records_sql($sql2, array($First2013l12Award, 'assessment'));
        if($records)
        {
            foreach($records AS $record)
            {
                $record->bcgttargetqualid = $First2013l12Dip;
                $DB->insert_record('block_bcgt_value', $record);
            }
        } 
    }
    
    if($oldversion < 2014021705)
    {
        $sql = "SELECT distinct(targetqual.id), targetqual.* FROM {block_bcgt_target_qual} targetqual 
            JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
            JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
            WHERE subtype.subtype = ? AND level.id = ?";
        $ExtCert = $DB->get_record_sql($sql, array('Extended Certificate',7));
        if($ExtCert)
        {
            $sql2 = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ?";
            $record = $DB->get_record_sql($sql2, array($ExtCert->id, 'U'));
            if($record)
            {
                $record->unitsscorelower = 0;
                $record->unitsscoreupper = 72;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($ExtCert->id, 'Level 1'));
            if($record)
            {
                $record->unitsscorelower = 72;
                $record->unitsscoreupper = 144;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($ExtCert->id, 'Level 2 PP'));
            if($record)
            {
                $record->unitsscorelower = 144;
                $record->unitsscoreupper = 174;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($ExtCert->id, 'Level 2 MP'));
            if($record)
            {
                $record->unitsscorelower = 174;
                $record->unitsscoreupper = 204;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($ExtCert->id, 'Level 2 MM'));
            if($record)
            {
                $record->unitsscorelower = 204;
                $record->unitsscoreupper = 234;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($ExtCert->id, 'Level 2 DM'));
            if($record)
            {
                $record->unitsscorelower = 234;
                $record->unitsscoreupper = 264;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($ExtCert->id, 'Level 2 DD'));
            if($record)
            {
                $record->unitsscorelower = 264;
                $record->unitsscoreupper = 270;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($ExtCert->id, 'Level 2 D*D'));
            if($record)
            {
                $record->unitsscorelower = 270;
                $record->unitsscoreupper = 276;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($ExtCert->id, 'Level 2 D*D*'));
            if($record)
            {
                $record->unitsscorelower = 276;
                $record->unitsscoreupper = 500;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
        }
        
        $dip = $DB->get_record_sql($sql, array('Diploma',7));
        if($dip)
        {
            $sql2 = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ?";
            $record = $DB->get_record_sql($sql2, array($dip->id, 'U'));
            if($record)
            {
                $record->unitsscorelower = 0;
                $record->unitsscoreupper = 96;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($dip->id, 'Level 1'));
            if($record)
            {
                $record->unitsscorelower = 96;
                $record->unitsscoreupper = 192;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($dip->id, 'Level 2 PP'));
            if($record)
            {
                $record->unitsscorelower = 192;
                $record->unitsscoreupper = 234;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($dip->id, 'Level 2 MP'));
            if($record)
            {
                $record->unitsscorelower = 234;
                $record->unitsscoreupper = 276;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($dip->id, 'Level 2 MM'));
            if($record)
            {
                $record->unitsscorelower = 276;
                $record->unitsscoreupper = 318;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($dip->id, 'Level 2 DM'));
            if($record)
            {
                $record->unitsscorelower = 318;
                $record->unitsscoreupper = 360;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($dip->id, 'Level 2 DD'));
            if($record)
            {
                $record->unitsscorelower = 360;
                $record->unitsscoreupper = 366;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($dip->id, 'Level 2 D*D'));
            if($record)
            {
                $record->unitsscorelower = 366;
                $record->unitsscoreupper = 372;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $record = $DB->get_record_sql($sql2, array($dip->id, 'Level 2 D*D*'));
            if($record)
            {
                $record->unitsscorelower = 372;
                $record->unitsscoreupper = 700;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
        }

    }
    
    if($oldversion < 2014021707)
    {
        $record = null;
        $sql = "SELECT break.* FROM {block_bcgt_target_breakdown} break 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = break.bcgttargetqualid
            JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid
            JOIN {block_bcgt_type_family} fam ON fam.id = type.bcgttypefamilyid
            JOIN {block_bcgt_subtype} sub ON sub.id = targetqual.bcgtsubtypeid
            WHERE fam.family = ? AND level.id = ? AND sub.subtype = ? AND break.targetgrade = ?";
        $record = $DB->get_record_sql($sql, array('BTEC',2,'Diploma','D*'));
        if($record)   
        {
            $record->unitsscorelower = 400;
            $record->unitsscoreupper = 999;
            $DB->update_record('block_bcgt_target_breakdown', $record);
        }
    }
	

    
    
}
