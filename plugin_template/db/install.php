<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
function xmldb_bcgtplugin_install()
{
    global $DB, $CFG;
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtplugin/classes/PluginSubType.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtplugin/classes/PluginFamilyQualification.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtplugin/classes/PluginUnit.class.php');
    // ---------------------- The Family ---------------------------
    $record = new stdClass();
    $record->id = X;
    $record->family = 'X';
    $record->classfolderlocation = '/blocks/bcgt/plugins/bcgtplugin/classes';
    $DB->insert_record_raw('block_bcgt_type_family', $record, false, false, true);
    
    // ---------------------- The Types ---------------------------
    //one for each
    $record = new stdClass();
    $record->id = X;
    $record->type = 'X';
    $record->bcgttypefamilyid = Y;
    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true);

    $record = new stdClass();
    $record->id = X2;
    $record->type = 'X2';
    $record->bcgttypefamilyid = Y;
    $DB->insert_record_raw('block_bcgt_type', $record, false, false, true);    
    // ---------------------- The Parent Type Family ---------------------------
    $record = new stdClass();
    $record->bcgttypeid = X;
    $record->bcgtfamilyid = Y;
    $DB->insert_record('block_bcgt_fam_parent_type', $record);
    
    // ---------------------- The SubTypes ---------------------------
    //one for each
    if(!($DB->record_exists('block_bcgt_subtype', array('subtype'=>'Z'))))
    {
        $record = new stdClass();
        $record->id = Z;
        $record->subtype = 'Z';
        $record->subtypeshort = 'Z';
        $DB->insert_record_raw('block_bcgt_subtype', $record, false, false, true);
    }
    // ---------------------- The Values for the grids ---------------------------
    $record = new stdClass();
    $record->value = 'X';
    $record->shortvalue = 'X';
    $record->bcgttypeid = x;
    $record->criteriamet = 'Yes/No';
    $DB->insert_record('block_bcgt_value', $record);
    
    //---------------------- The Unit Types ---------------------------
    //if any
    $record = new stdClass();
    $record->type = '';
    $record->bcgttypeid = X;
    $DB->insert_record('block_bcgt_unit_type', $record);
    
    // ---------------------- The Type Awards ---------------------------
    //For units overall etc
    $record = new stdClass();
    $record->award = 'One';
    $record->ranking = X;
    $record->bcgttypeid = X;
    $ID1 = $DB->insert_record('block_bcgt_type_award', $record);
    
    $record = new stdClass();
    $record->award = 'Two';
    $record->ranking = X;
    $record->bcgttypeid = X;
    $ID2 = $DB->insert_record('block_bcgt_type_award', $record);
    
    // ---------------------- The Unit Points ---------------------------
    //level 3
    $record = new stdClass();
    $record->bcgtlevelid = 1; //1, 2, 3, 4 or 5
    $record->bcgttypeawardid = $ID1;
    $record->points = 7.0;
    $DB->insert_record('block_bcgt_unit_points', $record);
    
    $record = new stdClass();
    $record->bcgtlevelid = 3; //1, 2, 3, 4 or 5
    $record->bcgttypeawardid = $ID2;
    $record->points = 8.0;
    $DB->insert_record('block_bcgt_unit_points', $record);

    // ---------------------- The Target Quals ---------------------------
    //Level 1 (1), Type Level 1 (X), SubType (X)
    $record = new stdClass();
    $record->bcgtlevelid = 1;
    $record->bcgttypeid = X;
    $record->bcgtsubtypeid = X;
    $record->previoustargetqualid = -1; //if it has a disticnt previous one. 
    //e.g ASlevel to A2 Level
    $ID = $DB->insert_record('block_bcgt_target_qual', $record); 
    
    //Any attributes e,g default number of credits
    $record = new stdClass();
    $record->bcgttargetqualid = $ID;
    $record->name = PluginSubType::DEFAULTNUMBEROFCREDITSNAME;
    $record->value = 7;
    $DB->insert_record('block_bcgt_target_qual_att', $record); 
    
    //------------------ Unit Type Defaults ------------
    //Defaults for the unit levels e.g,default no unit credits
    $record = new stdClass();
    $record->bcgtlevelid = X;
    $record->name = PLUGINUNIT::DEFAULTUNITCREDITSNAME;
    $record->bcgttypefamilyid = X;
    $record->value = 3;
    $DB->insert_record('block_bcgt_unit_type_att', $record);

    // ---------------------- The Target Quals Grades ---------------------------
    //e.g. overal for the qual family
    
    //Level 2 Certificate $l2CertID
    $record = new stdClass();
    $record->bcgttargetqualid = $targetQualID;      
    $record->targetgrade = 'Pass';
    $record->unitsscorelower = 0; //Units score if needed
    $record->unitsscoreupper = 84;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
          
    //Adding a second
    $record->targetgrade = 'Merit';
    $record->unitsscorelower = 85;
    $record->unitsscoreupper = 94;
    $DB->insert_record('block_bcgt_target_breakdown', $record);
      
    //------------- ANY TABS ------------
    
    //THE TAB
    $record = new stdClass();
    $record->tabname = 'X';
    $record->component = 'X';
    $record->tabclassfile = '/mod/bcgtplugin/classes/PluginDashTab.class.php';
    $DB->insert_record('block_bcgt_tabs', $record);
    
    //Others
    //check config for prior learning and if need be go and run thise points
    //import
    global $CFG;
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtplugin/lib.php');
    run_plugin_initial_import();
}
?>
