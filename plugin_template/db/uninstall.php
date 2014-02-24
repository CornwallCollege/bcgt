<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
function xmldb_bcgtplugin_uninstall()
{
    //delete all of the data from the database
    
    global $DB;
    //TODO delete the type attributes and unit_type_attributes
    
    //delete all user data
    //delete all quals on units
    //delete all units
    //delete all quals
    //delete all criteria
    //find all of the quals that are btecfamily 
    $quals = $DB->get_records_sql("SELECT qual.id FROM {block_bcgt_qualification} AS qual
        JOIN {block_bcgt_target_qual} AS targetqual ON targetqual.id = qual.bcgttargetqualid 
        JOIN {block_bcgt_type} AS type ON type.id = targetqual.bcgttypeid 
        WHERE type.bcgttypefamilyid = ?", array(x));
    if($quals)
    {
        foreach($quals AS $qual)
        {
            $DB->delete_records('block_bcgt_qual_units', array("bcgtqualificationid" => $qual->id));
            $DB->delete_records('block_bcgt_qualification', array("id" => $qual->id));
        }
    }
    
    $units = $DB->get_records_sql("SELECT unit.id FROM {block_bcgt_unit} AS unit 
        JOIN {block_bcgt_type} AS type ON type.id = unit.bcgttypeid 
        WHERE type.bcgttypefamilyid = ?", array(x));
    if($units)
    {
        foreach($units AS $unit)
        {
            $DB->delete_records('block_bcgt_criteria', array("bcgtunitid"=>$unit->id));
            $DB->delete_records('block_bcgt_qual_units', array("bcgtunitid"=>$unit->id));
            $DB->delete_records('block_bcgt_unit', array("id"=>$unit->id));
        }
    }
    
    //delete the target breakdowns
    //find the target quals
    $targetQuals = $DB->get_records_sql("SELECT targetqual.id 
        FROM {block_bcgt_target_qual} AS targetqual 
        JOIN {block_bcgt_type} AS type ON type.id = targetqual.bcgttypeid 
        WHERE type.bcgttypefamilyid = ?", array(x));
    if($targetQuals)
    {
        foreach($targetQuals AS $targetQual)
        {
            $DB->delete_records('block_bcgt_target_breakdown', array("bcgttargetqualid"=>$targetQual->id));
            $DB->delete_records('block_bcgt_target_qual', array("id"=>$targetQual->id));
        }
    }
    
    $typeAwards = $DB->get_records_sql("SELECT typeaward.id 
        FROM {block_bcgt_type_award} AS typeaward
        JOIN {block_bcgt_type} AS type ON type.id = typeaward.bcgttypeid 
        WHERE type.bcgttypefamilyid = ?", array(x));
    if($typeAwards)
    {
        foreach($typeAwards AS $typeAward)
        {
            $DB->delete_records('block_bcgt_unit_points', array("bcgttypeawardid"=>$typeAward->id));
            $DB->delete_records('block_bcgt_type_award', array("id"=>$typeAward->id));
        }
    }

    $types = $DB->get_records_sql("SELECT id FROM {block_bcgt_type} 
        WHERE bcgttypefamilyid = ?", array(x));
    if($types)
    {
        foreach($types AS $type)
        {
            $DB->delete_records('block_bcgt_unit_type', array("bcgttypeid"=>$type->id));
            $DB->delete_records('block_bcgt_value', array("bcgttypeid"=>$type->id));
            $DB->delete_records('block_bcgt_fam_parent_type', array("bcgttypeid"=>$type->id));
            $DB->delete_records('block_bcgt_type', array("id"=>$type->id));
        }
    }
    
    $DB->delete_records('block_bcgt_type_family', array("id"=>x));
    
    $DB->delete_records('block_bcgt_subtype', array("id"=>x));
    $DB->delete_records('block_bcgt_subtype', array("id"=>x));
    $DB->delete_records('block_bcgt_subtype', array("id"=>x));
    $DB->delete_records('block_bcgt_subtype', array("id"=>x));
    $DB->delete_records('block_bcgt_subtype', array("id"=>x));
    $DB->delete_records('block_bcgt_subtype', array("id"=>x));
    $DB->delete_records('block_bcgt_subtype', array("id"=>x));
    $DB->delete_records('block_bcgt_subtype', array("id"=>x));
    $DB->delete_records('block_bcgt_subtype', array("id"=>x));
    $DB->delete_records('block_bcgt_subtype', array("id"=>x));
    
    $DB->delete_records('block_bcgt_tabs', array('tabname'=>'X'));
    
    //anything else
    
}
?>
