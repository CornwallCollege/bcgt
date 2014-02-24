<?php

require_once $CFG->dirroot . '/blocks/bcgt/bcgt.class.php';

function xmldb_block_bcgt_upgrade($oldversion = 0)
{
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2013051502)
    {
        //then we are inserting the new plugins table
        $table = new xmldb_table('block_bcgt_plugins');
	
		$table_id = new xmldb_field('id');
        $table_id->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addField($table_id);
        
        $table_name = new xmldb_field('name');
        $table_name->set_attributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addField($table_name);
        
        $table_title = new xmldb_field('title');
        $table_title->set_attributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addField($table_title);
        
        $table_version = new xmldb_field('version');
        $table_version->set_attributes(XMLDB_TYPE_INTEGER, 20, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_version);
        
        $table_enabled = new xmldb_field('enabled');
        $table_enabled->set_attributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_enabled);
                
        $table_key = new xmldb_key('primary');
        $table_key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));
        $table->addKey($table_key);

       	if (!$dbman->table_exists($table)) $dbman->create_table($table);
    }
    
    if ($oldversion < 2013052200)
    {
        //then we are inserting the new plugins table
        $table = new xmldb_table('block_bcgt_qual_weighting');
	
		$table_id = new xmldb_field('id');
        $table_id->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addField($table_id);
        
        $table_qualid = new xmldb_field('bcgtqualificationid');
        $table_qualid->set_attributes(XMLDB_TYPE_INTEGER, 18, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_qualid);
        
        $table_coef = new xmldb_field('coefficient');
        $table_coef->set_attributes(XMLDB_TYPE_NUMBER, "3,2", null, null);
        $table->addField($table_coef);
        
        $table_perc = new xmldb_field('percentage');
        $table_perc->set_attributes(XMLDB_TYPE_NUMBER, "5,2", null, null);
        $table->addField($table_perc);
        
        $table_no = new xmldb_field('number');
        $table_no->set_attributes(XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED, null);
        $table->addField($table_no);
        
        $table_att = new xmldb_field('attribute');
        $table_att->set_attributes(XMLDB_TYPE_CHAR, 200, null, null);
        $table->addField($table_att);
                
        $table_key = new xmldb_key('primary');
        $table_key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));
        $table->addKey($table_key);

       	if (!$dbman->table_exists($table)) $dbman->create_table($table);
        
        //Adding the rank to the grades
        $table2 = new xmldb_table('block_bcgt_target_grades');
        $table_rank = new xmldb_field('ranking');
        $table_rank->set_attributes(XMLDB_TYPE_NUMBER, "5,2", XMLDB_UNSIGNED, null);
        if(!$dbman->field_exists($table2, $table_rank)) $dbman->add_field($table2, $table_rank);
        
    }
    
    if($oldversion < 2013060300)
    {
        //Adding the qualificationid to the coursetargets
        $table2 = new xmldb_table('block_bcgt_user_course_trgts');
        $table_qual = new xmldb_field('bcgtqualificationid');
        $table_qual->set_attributes(XMLDB_TYPE_INTEGER, 18, XMLDB_UNSIGNED, null);
        if(!$dbman->field_exists($table2, $table_qual)) $dbman->add_field($table2, $table_qual);

        //Add grades id to userward
        $table3 = new xmldb_table('block_bcgt_user_award');
        $table_grade = new xmldb_field('bcgttargetgradesid');
        $table_grade->set_attributes(XMLDB_TYPE_INTEGER, 18, XMLDB_UNSIGNED, null);
        if(!$dbman->field_exists($table3, $table_grade)) $dbman->add_field($table3, $table_grade);
    
        //Add targetgrades id to usercriteria
        $table4 = new xmldb_table('block_bcgt_user_criteria');
        $table_grade = new xmldb_field('bcgttargetgradesid');
        $table_grade->set_attributes(XMLDB_TYPE_INTEGER, 18, XMLDB_UNSIGNED, null);
        if(!$dbman->field_exists($table4, $table_grade)) $dbman->add_field($table4, $table_grade);
    }
    
    if($oldversion < 2013060400)
    {
        //Adding the order to the values
        $table = new xmldb_table('block_bcgt_value');
        $table_order = new xmldb_field('ranking');
        $table_order->set_attributes(XMLDB_TYPE_INTEGER, 18, XMLDB_UNSIGNED, null);
        if(!$dbman->field_exists($table, $table_order)) $dbman->add_field($table, $table_order);
    }
    
    if($oldversion < 2013060500)
    {
        $table = new xmldb_table('block_bcgt_user_criteria');
        $table_breakdown = new xmldb_field('bcgttargetbreakdownid');
        $table_breakdown->set_attributes(XMLDB_TYPE_INTEGER, 18, XMLDB_UNSIGNED, null);
        if(!$dbman->field_exists($table, $table_breakdown)) $dbman->add_field($table, $table_breakdown);
    
        $table2 = new xmldb_table('block_bcgt_user_criteria_his');
        $table_grade = new xmldb_field('bcgttargetgradesid');
        $table_grade->set_attributes(XMLDB_TYPE_INTEGER, 18, XMLDB_UNSIGNED, null);
        if(!$dbman->field_exists($table2, $table_grade)) $dbman->add_field($table2, $table_grade);
        
        $table4 = new xmldb_table('block_bcgt_user_criteria_his');
        $table_breakdown = new xmldb_field('bcgttargetbreakdownid');
        $table_breakdown->set_attributes(XMLDB_TYPE_INTEGER, 18, XMLDB_UNSIGNED, null);
        if(!$dbman->field_exists($table4, $table_breakdown)) $dbman->add_field($table4, $table_breakdown);
        
    }
    
    if($oldversion < 2013060503)
    {
        $table = new xmldb_table('block_bcgt_type_family');
        $table_pluginname = new xmldb_field('pluginname');
        $table_pluginname->set_attributes(XMLDB_TYPE_CHAR, 100, null, null);
        if(!$dbman->field_exists($table, $table_pluginname)) $dbman->add_field($table, $table_pluginname);
    }
    
    if($oldversion < 2013060800)
    {
        //add two missing fields from criteria history
        $table = new xmldb_table('block_bcgt_criteria_his');
        $table_targetdate = new xmldb_field('targetdate');
        $table_targetdate->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        if(!$dbman->field_exists($table, $table_targetdate)) $dbman->add_field($table, $table_targetdate);
    
        $table_obs = new xmldb_field('numofobservations');
        $table_obs->set_attributes(XMLDB_TYPE_INTEGER, 9, null, null);
        if(!$dbman->field_exists($table, $table_obs)) $dbman->add_field($table, $table_obs);
    }
    
    if($oldversion < 2013061300)
    {
        //Adding the order to the values this was accidentallty left off of the install .xml
        $table = new xmldb_table('block_bcgt_value');
        $table_order = new xmldb_field('ranking');
        $table_order->set_attributes(XMLDB_TYPE_INTEGER, 18, XMLDB_UNSIGNED, null);
        if(!$dbman->field_exists($table, $table_order)) $dbman->add_field($table, $table_order);
    }
    
    if($oldversion < 2013071500)
    {
        //Adding the flag column to the table
        $table = new xmldb_table('block_bcgt_user_criteria');
        $table_flag = new xmldb_field('flag');
        $table_flag->set_attributes(XMLDB_TYPE_CHAR, 100, null, null);
        if(!$dbman->field_exists($table, $table_flag)) $dbman->add_field($table, $table_flag);
        
        //Adding the flag column to the table
        $table = new xmldb_table('block_bcgt_user_criteria_his');
        $table_flag = new xmldb_field('flag');
        $table_flag->set_attributes(XMLDB_TYPE_CHAR,100, null, null);
        if(!$dbman->field_exists($table, $table_flag)) $dbman->add_field($table, $table_flag);
    }
    
    if($oldversion < 2013071500)
    {
        //Adding the enabled column to the database
        //changing the criteriamet column to specialval
        $table = new xmldb_table('block_bcgt_value');
        $table_enabled = new xmldb_field('enabled');
        $table_enabled->set_attributes(XMLDB_TYPE_INTEGER,2, null, null);
        if(!$dbman->field_exists($table, $table_enabled)) $dbman->add_field($table, $table_enabled);
    
        $table_criteriamet = new xmldb_field('criteriamet');
        $table_criteriamet->set_attributes(XMLDB_TYPE_CHAR, 255, null, null);
        if($dbman->field_exists($table, $table_criteriamet)) $dbman->rename_field($table, $table_criteriamet, 'specialval');
    }
    
    if($oldversion < 2013071500)
    {
        //removing the special val, adding the customvalue and customshortvalue
        $table = new xmldb_table('block_bcgt_value');
        $table_custom = new xmldb_field('customvalue');
        $table_custom->set_attributes(XMLDB_TYPE_CHAR,255, null, null);
        if(!$dbman->field_exists($table, $table_custom)) $dbman->add_field($table, $table_custom);
    
        $table_customs = new xmldb_field('customshortvalue');
        $table_customs->set_attributes(XMLDB_TYPE_CHAR,255, null, null);
        if(!$dbman->field_exists($table, $table_customs)) $dbman->add_field($table, $table_customs);
    }
    
    if($oldversion < 2013071500)
    {
        //changing the value names etc
        $table = new xmldb_table('block_bcgt_value_settings');
        $table_value = new xmldb_field('value');
        $table_value->set_attributes(XMLDB_TYPE_CHAR,3, null, null);
        if($dbman->field_exists($table, $table_value)) $dbman->rename_field($table, $table_value, 'bcgtvalueid');
        
        //drop the index. 
        
        $index = new xmldb_index("");
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('bcgtvalueid'));
        $indexName = $dbman->find_index_name($table, $index);
        
        $index = new xmldb_index($indexName);
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('bcgtvalueid'));
        if($dbman->index_exists($table, $index)) $dbman->drop_index($table, $index);
        
        $table_value = new xmldb_field('bcgtvalueid');
        $table_value->set_attributes(XMLDB_TYPE_INTEGER,18, null, XMLDB_NOTNULL);
        if($dbman->field_exists($table, $table_value)) $dbman->change_field_type($table, $table_value);
    
        //changing the value names etc
        $table_img = new xmldb_field('img');
        $table_img->set_attributes(XMLDB_TYPE_CHAR,45, null, null);
        if($dbman->field_exists($table, $table_img)) $dbman->rename_field($table, $table_img, 'coreimg');
        
        $table_img = new xmldb_field('coreimg');
        $table_img->set_attributes(XMLDB_TYPE_CHAR,255, null, XMLDB_NOTNULL);
        if($dbman->field_exists($table, $table_img)) $dbman->change_field_type($table, $table_img);        
        
        //changing the value names etc
        $table_class = new xmldb_field('class');
        $table_class->set_attributes(XMLDB_TYPE_CHAR,45, null, null);
        if($dbman->field_exists($table, $table_class)) $dbman->rename_field($table, $table_class, 'customimg');
        
        $table_class = new xmldb_field('customimg');
        $table_class->set_attributes(XMLDB_TYPE_CHAR,255, null, null);
        if($dbman->field_exists($table, $table_class)) $dbman->change_field_type($table, $table_class);
    }

    if($oldversion < 2013071500)
    {
        $table = new xmldb_table('block_bcgt_value_settings');
        $table_dlate = new xmldb_field('coreimglate');
        $table_dlate->set_attributes(XMLDB_TYPE_CHAR,255, null, null);
        if(!$dbman->field_exists($table, $table_dlate)) $dbman->add_field($table, $table_dlate);
        
        $table = new xmldb_table('block_bcgt_value_settings');
        $table_clate = new xmldb_field('customimglate');
        $table_clate->set_attributes(XMLDB_TYPE_CHAR,255, null, null);
        if(!$dbman->field_exists($table, $table_clate)) $dbman->add_field($table, $table_clate);
    }
    
    if ($oldversion < 2013071500)
    {
        
        $obj = new stdClass();
        $obj->id = 1;
        $obj->classfolderlocation = '/blocks/bcgt/plugins/bcgtbespoke/classes';
        $obj->pluginname = 'bcgtbespoke';
        $DB->update_record("block_bcgt_type_family", $obj);
        
    }
    
    if($oldversion < 2013071500)
    {
        //delete the project criteria table
        $table = new xmldb_table('block_bcgt_project_criteria');
        if($dbman->table_exists($table)) $dbman->drop_table($table);
        
        //drop the index
        $index = new xmldb_index('blocbcgtproj_nam_uix');
        if($dbman->index_exists($table, $index)) $dbman->drop_index($table, $index);
        
        //add new rows
        $table = new xmldb_table('block_bcgt_project');
        $field = new xmldb_field('targetdate');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 20, null, null);
        if(!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);
        
        $field = new xmldb_field('centrallymanaged');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 1, null, null);
        if(!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);
        
        $field = new xmldb_field('awarded');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 1, null, null);
        if(!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);
        
        $field = new xmldb_field('datecreated');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 20, null, null);
        if(!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);
    
        $field = new xmldb_field('createdbyuserid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 20, null, null);
        if(!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);
    
        $field = new xmldb_field('dateupdated');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 20, null, null);
        if(!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);
    
        $field = new xmldb_field('updatedbyuserid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 20, null, null);
        if(!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);
    
        //drop the ass_selection_his table
        $table = new xmldb_table('block_bcgt_ass_selection_his');
        if($dbman->table_exists($table)) $dbman->drop_table($table);
        
        //drop the user_ass_val and user_ass_val_his table
        $table = new xmldb_table('block_bcgt_user_ass_val');
        if($dbman->table_exists($table)) $dbman->drop_table($table);
        
        $table = new xmldb_table('block_bcgt_user_ass_val_his');
        if($dbman->table_exists($table)) $dbman->drop_table($table);
        
        //rename the ass_selection table to block_bcgt_activity_refs
        $table = new xmldb_table('block_bcgt_ass_selection');
        if($dbman->table_exists($table)) $dbman->rename_table($table, 'block_bcgt_activity_refs');
        
        //assignmentid rename field to activityid
        $table = new xmldb_table('block_bcgt_activity_refs');
        $field = new xmldb_field('assignmentid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        if($dbman->field_exists($table, $field)) $dbman->rename_field($table, $field, 'activityid');
    
        //ad field bcgtprojectid
        $table = new xmldb_table('block_bcgt_activity_refs');
        $field = new xmldb_field('bcgtprojectid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        if(!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);
        
        //add_user_activity_records table
        $table = new xmldb_table('block_bcgt_user_activity_ref');
        $fieldID = new xmldb_field('id');
        $fieldID->set_attributes(XMLDB_TYPE_INTEGER, 20, null, null);
        $table->addField($fieldID);
        
        $fieldarID = new xmldb_field('bcgtactivityrefid');
        $fieldarID->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        $table->addField($fieldarID);
        
        $userID = new xmldb_field('userid');
        $userID->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        $table->addField($userID);
        
        $valueID = new xmldb_field('bcgtvalueid');
        $valueID->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        $table->addField($valueID);
        
        $comm = new xmldb_field('comments');
        $comm->set_attributes(XMLDB_TYPE_CHAR, 1333, null, null);
        $table->addField($comm);
       
        $dateSetID = new xmldb_field('dateset');
        $dateSetID->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        $table->addField($dateSetID);
        //dateset
        //setbyuserid
        $dateSetUserID = new xmldb_field('setbyuserid');
        $dateSetUserID->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        $table->addField($dateSetUserID);
        //dateupdated
        $dateUpdatedID = new xmldb_field('dateupdated');
        $dateUpdatedID->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        $table->addField($dateUpdatedID);
        //updatedbyuserid
        $dateUpdatedUserID = new xmldb_field('updatedbyuserid');
        $dateUpdatedUserID->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        $table->addField($dateUpdatedUserID);
        //userdefinedvalue
        $userDefinedVal = new xmldb_field('userdefinedvalue');
        $userDefinedVal->set_attributes(XMLDB_TYPE_CHAR, 250, null, null);
        $table->addField($userDefinedVal);
        //bcgttargetgradesid
        $targetGrades = new xmldb_field('bcgttargetgradesid');
        $targetGrades->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        $table->addField($targetGrades);
        //bcgttargetbreakdownid
        $targetBreakdown = new xmldb_field('bcgttargetbreakdownid');
        $targetBreakdown->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        $table->addField($targetBreakdown);
        //flag
        $flag = new xmldb_field('flag');
        $flag->set_attributes(XMLDB_TYPE_CHAR, 250, null, null);
        $table->addField($flag);
        
        $table_key = new xmldb_key('primary');
        $table_key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));
        $table->addKey($table_key);

       	if (!$dbman->table_exists($table)) $dbman->create_table($table);
    }
    
    if ($oldversion < 2013071500)
    {
        
        $table = new xmldb_table('block_bcgt_bespoke_qual');
        
        if(!$dbman->table_exists($table)){
            
            $fields = array();
            $fields[0] = new xmldb_field('id');
            $fields[0]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            
            $fields[1] = new xmldb_field('bcgtqualid');
            $fields[1]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            
            $fields[2] = new xmldb_field('displaytype');
            $fields[2]->set_attributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            
            $fields[3] = new xmldb_field('subtype');
            $fields[3]->set_attributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            
            $fields[4] = new xmldb_field('level');
            $fields[4]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, false);
            
            foreach($fields as $field){
                $table->addField($field);
            }

            $key = new xmldb_key('primary');
            $key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));
            $table->addKey($key);

            $dbman->create_table($table);
            
        }
        
        
        
        $table = new xmldb_table('block_bcgt_bspk_qual_grading');
        
        if(!$dbman->table_exists($table)){
            
            $fields = array();
            $fields[0] = new xmldb_field('id');
            $fields[0]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            
            $fields[1] = new xmldb_field('name');
            $fields[1]->set_attributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
           
            foreach($fields as $field){
                $table->addField($field);
            }

            $key = new xmldb_key('primary');
            $key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));
            $table->addKey($key);

            $dbman->create_table($table);
            
        }
        
        
        // We're really handicapped with table names having such a long prefix as block_bcgt_
        // Could easily have been just bgct_ giving us 6 extra characters to use...
        $table = new xmldb_table('block_bcgt_bspk_q_grade_vals');
        
        if(!$dbman->table_exists($table)){
            
            $fields = array();
            $fields[0] = new xmldb_field('id');
            $fields[0]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            
            $fields[1] = new xmldb_field('qualgradingid');
            $fields[1]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            
            $fields[2] = new xmldb_field('grade');
            $fields[2]->set_attributes(XMLDB_TYPE_CHAR, 50, null, XMLDB_NOTNULL);
            
            $fields[3] = new xmldb_field('rangelower');
            $fields[3]->set_attributes(XMLDB_TYPE_NUMBER, "2,1", null, XMLDB_NOTNULL);
            
            $fields[4] = new xmldb_field('rangeupper');
            $fields[4]->set_attributes(XMLDB_TYPE_NUMBER, "2,1", null, XMLDB_NOTNULL);
           
            foreach($fields as $field){
                $table->addField($field);
            }

            $key = new xmldb_key('primary');
            $key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));
            $table->addKey($key);

            $dbman->create_table($table);
            
        }
        
        
        
    }
    
    if ($oldversion < 2013071500)
    {
        
        $table = new xmldb_table('block_bcgt_bespoke_qual');
        $field = new xmldb_field('gradingstructureid');
        if(!$dbman->field_exists($table, $field)){
            $field->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            $table->addField($field);
        }
        
        $table = new xmldb_table('block_bcgt_bspk_q_grade_vals');
        $field = new xmldb_field('shortgrade');
        if(!$dbman->field_exists($table, $field)){
            $field->set_attributes(XMLDB_TYPE_CHAR, 2, null, XMLDB_NOTNULL);
            $table->addField($field);
        }
        
        
    }
    
        
    if($oldversion < 2013071500)
    {
        //activityid rename field to coursemoduleid
        $table = new xmldb_table('block_bcgt_activity_refs');
        $field = new xmldb_field('activityid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        if($dbman->field_exists($table, $field)) $dbman->rename_field($table, $field, 'coursemoduleid');
    
        //weirdly it may still be called assignmentid
        $table = new xmldb_table('block_bcgt_activity_refs');
        $field = new xmldb_field('assignmentid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        if($dbman->field_exists($table, $field)) $dbman->rename_field($table, $field, 'coursemoduleid');
        
    }
    
    if ($oldversion < 2013071500)
    {
        
        $table = new xmldb_table('block_bcgt_bspk_unit_grading');
        
        if(!$dbman->table_exists($table)){
            
            $fields = array();
            $fields[0] = new xmldb_field('id');
            $fields[0]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            
            $fields[1] = new xmldb_field('name');
            $fields[1]->set_attributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
           
            foreach($fields as $field){
                $table->addField($field);
            }

            $key = new xmldb_key('primary');
            $key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));
            $table->addKey($key);

            $dbman->create_table($table);
            
        }
        
        
        
        $table = new xmldb_table('block_bcgt_bspk_u_grade_vals');
        
        if (!$dbman->table_exists('block_bcgt_bspk_u_grade_vals')){
        
            $fields = array();

            $fields[0] = new xmldb_field('id');
            $fields[0]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);

            $fields[1] = new xmldb_field('unitgradingid');
            $fields[1]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);

            $fields[2] = new xmldb_field('grade');
            $fields[2]->set_attributes(XMLDB_TYPE_CHAR, 50, null, XMLDB_NOTNULL);

            $fields[3] = new xmldb_field('shortgrade');
            $fields[3]->set_attributes(XMLDB_TYPE_CHAR, 2, null, XMLDB_NOTNULL);

            $fields[4] = new xmldb_field('points');
            $fields[4]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);

            $fields[5] = new xmldb_field('rangelower');
            $fields[5]->set_attributes(XMLDB_TYPE_NUMBER, "2,1", null, XMLDB_NOTNULL);

            $fields[6] = new xmldb_field('rangeupper');
            $fields[6]->set_attributes(XMLDB_TYPE_NUMBER, "2,1", null, XMLDB_NOTNULL);

            foreach($fields as $field){
                $table->addField($field);
            }

            $key = new xmldb_key('primary');
            $key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));
            $table->addKey($key);

            $dbman->create_table($table);
        
        }
        
    }
    
    if ($oldversion < 2013071500)
    {
        
        $table = new xmldb_table('block_bcgt_bespoke_unit');
        
        if(!$dbman->table_exists($table)){
            
            $fields = array();
            $fields[0] = new xmldb_field('id');
            $fields[0]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            
            $fields[1] = new xmldb_field('bcgtunitid');
            $fields[1]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            
            $fields[2] = new xmldb_field('displaytype');
            $fields[2]->set_attributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            
            $fields[3] = new xmldb_field('level');
            $fields[3]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, false);
            
            $fields[4] = new xmldb_field('gradingstructureid');
            $fields[4]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, false);
            
            foreach($fields as $field){
                $table->addField($field);
            }

            $key = new xmldb_key('primary');
            $key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));
            $table->addKey($key);

            $dbman->create_table($table);
            
        }
        
    }
    
    
    if ($oldversion < 2013071500)
    {
        
        $table = new xmldb_table('block_bcgt_bespoke_criteria');
        if(!$dbman->table_exists($table)){
            
            $fields = array();
            $fields[0] = new xmldb_field('id');
            $fields[0]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            
            $fields[1] = new xmldb_field('bcgtcritid');
            $fields[1]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            
            $fields[2] = new xmldb_field('gradingstructureid');
            $fields[2]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            
            $fields[3] = new xmldb_field('weighting');
            $fields[3]->set_attributes(XMLDB_TYPE_NUMBER, "2,1", null, XMLDB_NOTNULL, null, "1.0");
            
            foreach($fields as $field){
                $table->addField($field);
            }

            $key = new xmldb_key('primary');
            $key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));
            $table->addKey($key);

            $dbman->create_table($table);
            
        }
        
        
        $table = new xmldb_table('block_bcgt_bspk_crit_grading');
        if(!$dbman->table_exists($table)){
            
            $fields = array();
            $fields[0] = new xmldb_field('id');
            $fields[0]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            
            $fields[1] = new xmldb_field('name');
            $fields[1]->set_attributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            
            foreach($fields as $field){
                $table->addField($field);
            }

            $key = new xmldb_key('primary');
            $key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));
            $table->addKey($key);

            $dbman->create_table($table);
            
        }
        
        
        $table = new xmldb_table('block_bcgt_bspk_c_grade_vals');
        if(!$dbman->table_exists($table)){
            
            $fields = array();
            $fields[0] = new xmldb_field('id');
            $fields[0]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            
            $fields[1] = new xmldb_field('critgradingid');
            $fields[1]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            
            $fields[2] = new xmldb_field('grade');
            $fields[2]->set_attributes(XMLDB_TYPE_CHAR, 50, null, XMLDB_NOTNULL);
            
            $fields[3] = new xmldb_field('shortgrade');
            $fields[3]->set_attributes(XMLDB_TYPE_CHAR, 3, null, XMLDB_NOTNULL);
            
            $fields[4] = new xmldb_field('points');
            $fields[4]->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            
            $fields[5] = new xmldb_field('rangelower');
            $fields[5]->set_attributes(XMLDB_TYPE_NUMBER, "2,1", null, XMLDB_NOTNULL);
            
            $fields[6] = new xmldb_field('rangeupper');
            $fields[6]->set_attributes(XMLDB_TYPE_NUMBER, "2,1", null, XMLDB_NOTNULL);
            
            foreach($fields as $field){
                $table->addField($field);
            }

            $key = new xmldb_key('primary');
            $key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));
            $table->addKey($key);

            $dbman->create_table($table);
            
        }
    }
    
    
    if ($oldversion < 2013071500){
        
        
        
            // Changing nullability of field critgradingid on table block_bcgt_bspk_c_grade_vals to null
            $table = new xmldb_table('block_bcgt_bspk_c_grade_vals');
            $field = new xmldb_field('critgradingid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'id');

            // Launch change of nullability for field critgradingid
            $dbman->change_field_notnull($table, $field);
            
            // New field - met
            $field = new xmldb_field('met');
            $field->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
            $dbman->add_field($table, $field);
            
            $field = new xmldb_field('shortgrade', XMLDB_TYPE_CHAR, '3', null, XMLDB_NOTNULL, null, null, 'grade');
            $dbman->change_field_precision($table, $field);
            
        
            // Not Met
            $record = new stdClass();
            $record->critgradingid = null;
            $record->grade = 'Late';
            $record->shortgrade = 'L';
            $record->points = 0;
            $record->rangelower = 0;
            $record->rangeupper = 0;
            $record->met = 0;
            $DB->insert_record('block_bcgt_bspk_c_grade_vals', $record);
            
             $record = new stdClass();
            $record->critgradingid = null;
            $record->grade = 'Referred';
            $record->shortgrade = 'R';
            $record->points = 0;
            $record->rangelower = 0;
            $record->rangeupper = 0;
            $record->met = 0;
            $DB->insert_record('block_bcgt_bspk_c_grade_vals', $record);
            
            $record = new stdClass();
            $record->critgradingid = null;
            $record->grade = 'Work Submitted';
            $record->shortgrade = 'WS';
            $record->points = 0;
            $record->rangelower = 0;
            $record->rangeupper = 0;
            $record->met = 0;
            $DB->insert_record('block_bcgt_bspk_c_grade_vals', $record);
            
             $record = new stdClass();
            $record->critgradingid = null;
            $record->grade = 'Work Not Submitted';
            $record->shortgrade = 'WNS';
            $record->points = 0;
            $record->rangelower = 0;
            $record->rangeupper = 0;
            $record->met = 0;
            $DB->insert_record('block_bcgt_bspk_c_grade_vals', $record);
            
             $record = new stdClass();
            $record->critgradingid = null;
            $record->grade = 'Partially Achieved';
            $record->shortgrade = 'PA';
            $record->points = 0;
            $record->rangelower = 0;
            $record->rangeupper = 0;
            $record->met = 0;
            $DB->insert_record('block_bcgt_bspk_c_grade_vals', $record);

            $record = new stdClass();
            $record->critgradingid = null;
            $record->grade = 'Not Achieved';
            $record->shortgrade = 'X';
            $record->points = 0;
            $record->rangelower = 0;
            $record->rangeupper = 0;
            $record->met = 0;
            $DB->insert_record('block_bcgt_bspk_c_grade_vals', $record);
            
            $record = new stdClass();
            $record->critgradingid = null;
            $record->grade = 'Absent';
            $record->shortgrade = 'Abs';
            $record->points = 0;
            $record->rangelower = 0;
            $record->rangeupper = 0;
            $record->met = 0;
            $DB->insert_record('block_bcgt_bspk_c_grade_vals', $record);
            
        
    }
    
    if ($oldversion < 2013071500)
    {
        
        // Adding pathway column to qual table so we can work out what pathway & pathway type it is
        $table = new xmldb_table('block_bcgt_qualification');
        $field = new xmldb_field('pathwaytypeid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, null);
        if(!$dbman->field_exists($table, $field)){
            $dbman->add_field($table, $field);
        }
        
    }
    
    if ($oldversion < 2013071500)
    {
        
        // Adding pathway column to qual table so we can work out what pathway & pathway type it is
        $table = new xmldb_table('block_bcgt_unit');
        $field = new xmldb_field('pathwaytypeid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, null);
        if(!$dbman->field_exists($table, $field)){
            $dbman->add_field($table, $field);
        }
        
    }
    
    if ($oldversion < 2013071500)
    {
        
        // Adding pathway column to qual table so we can work out what pathway & pathway type it is
        $table = new xmldb_table('block_bcgt_criteria');
        $field = new xmldb_field('weighting');
        $field->set_attributes(XMLDB_TYPE_FLOAT, "4,2", XMLDB_UNSIGNED, null, null, "1.0");
        if(!$dbman->field_exists($table, $field)){
            $dbman->add_field($table, $field);
        }
        
    }
    
    if ($oldversion < 2013071500)
    {
        
        // Adding pathway column to qual table so we can work out what pathway & pathway type it is
        $table = new xmldb_table('block_bcgt_criteria');
        $field = new xmldb_field('ordernum');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, null, null, 1);
        if(!$dbman->field_exists($table, $field)){
            $dbman->add_field($table, $field);
        }
        
    }
    
    
    if ($oldversion < 2013071800) {

        // Define field targetdate to be added to block_bcgt_range_history
        $table = new xmldb_table('block_bcgt_range_history');
        $field = new xmldb_field('targetdate', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, null, 'details');

        // Conditionally launch add field targetdate
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('parentcriteriaid');

        // Conditionally launch drop field targetdate
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

    }
    
    if ($oldversion < 2013071900)
    {
        
        // Changing nullability of field bcgttypeawardid on table block_bcgt_criteria_his to null
        $table = new xmldb_table('block_bcgt_criteria_his');
        $field = new xmldb_field('bcgttypeawardid', XMLDB_TYPE_INTEGER, '18', null, null, null, null, 'type');

        // Launch change of nullability for field bcgttypeawardid
        $dbman->change_field_notnull($table, $field);
        
    }
    
    if ($oldversion < 2013071901)
    {
        
        // Define field weighting to be added to block_bcgt_criteria_his
        $table = new xmldb_table('block_bcgt_criteria_his');
        $field = new xmldb_field('weighting', XMLDB_TYPE_NUMBER, '4, 2', null, null, null, null, 'bcgtqualificationid');

        // Conditionally launch add field weighting
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        
        
        // Define field ordernum to be added to block_bcgt_criteria_his
        $table = new xmldb_table('block_bcgt_criteria_his');
        $field = new xmldb_field('ordernum', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'weighting');

        // Conditionally launch add field ordernum
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
    }
    
    
    if ($oldversion < 2013072401){
        
        // Define field specificawardtype to be added to block_bcgt_unit_history
        $table = new xmldb_table('block_bcgt_unit_history');
        $field = new xmldb_field('specificawardtype', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'aestheticname');

        // Conditionally launch add field specificawardtype
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define field pathwaytypeid to be added to block_bcgt_unit_history
        $table = new xmldb_table('block_bcgt_unit_history');
        $field = new xmldb_field('pathwaytypeid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'specificawardtype');

        // Conditionally launch add field pathwaytypeid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
    }
    
    
    
    
    
    if ($oldversion < 2013080100){
        
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
        
    
    
    
    
    if ($oldversion < 2013080202){
        
        // Rename field bcgtvalueid on table block_bcgt_user_range to NEWNAMEGOESHERE
        $table = new xmldb_table('block_bcgt_user_range');
        $field = new xmldb_field('bcgttypeawardid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null, 'bcgtrangeid');

        // Launch rename field bcgtvalueid
        if ($dbman->field_exists($table, $field)){
            $dbman->rename_field($table, $field, 'bcgtvalueid');
        }
        
        
    }
    
    
    if ($oldversion < 2013080203){
        
        $record = $DB->get_record("block_bcgt_value", array("bcgttypeid" => 10, "shortvalue" => "P"));
        if ($record){
            $record->ranking = 1;
            $DB->update_record("block_bcgt_value", $record);
        }

        
        $record = $DB->get_record("block_bcgt_value", array("bcgttypeid" => 10, "shortvalue" => "M"));
        if ($record){
            $record->ranking = 2;
            $DB->update_record("block_bcgt_value", $record);
        }
        
        
        
        $record = $DB->get_record("block_bcgt_value", array("bcgttypeid" => 10, "shortvalue" => "D"));
        if ($record){
            $record->ranking = 3;
            $DB->update_record("block_bcgt_value", $record);
        }
    }

   
    
    if ($oldversion < 2013081302)
    {
        mtrace("Trying to insert value setting for PA (CGHBVRQ)");
        $record = $DB->get_record("block_bcgt_value", array("bcgttypeid" => 10, "shortvalue" => "PA"));
        if ($record)
        {
            $img = new stdClass();
            $img->bcgtvalueid = $record->id;
            $img->coreimg = '/pix/grid_symbols/core/icon_PartiallyAchieved.png';
            $DB->insert_record("block_bcgt_value_settings", $img);
            mtrace("Done");
        }
        
    }
    
    
    if ($oldversion < 2013081801){
        
        //alter the breakdown table
        $table = new xmldb_table('block_bcgt_target_breakdown');
        $table_ucas = new xmldb_field('ucaspoints');
        $table_ucas->set_attributes(XMLDB_TYPE_NUMBER,"5,1", null, null);
        if($dbman->field_exists($table, $table_ucas)) $dbman->change_field_type($table, $table_ucas);  
        
        $table_upper = new xmldb_field('unitsscoreupper');
        $table_upper->set_attributes(XMLDB_TYPE_NUMBER,"6,2", null, null);
        if($dbman->field_exists($table, $table_upper)) $dbman->change_field_type($table, $table_upper); 
        
        $table_lower = new xmldb_field('unitsscorelower');
        $table_lower->set_attributes(XMLDB_TYPE_NUMBER,"6,2", null, null);
        if($dbman->field_exists($table, $table_lower)) $dbman->change_field_type($table, $table_lower); 
        
        $table = new xmldb_table('block_bcgt_target_grades');
        $table_ucas = new xmldb_field('ucaspoints');
        $table_ucas->set_attributes(XMLDB_TYPE_NUMBER,"5,1", null, null);
        if($dbman->field_exists($table, $table_ucas)) $dbman->change_field_type($table, $table_ucas); 
        
        $table = new xmldb_table('block_bcgt_target_breakdown');
        $table_rank = new xmldb_field('ranking');
        $table_rank->set_attributes(XMLDB_TYPE_NUMBER, "3,1", null, null);
        if(!$dbman->field_exists($table, $table_rank)) $dbman->add_field($table, $table_rank); 
        
        $table = new xmldb_table('block_bcgt_value');
        $table_use = new xmldb_field('context');
        $table_use->set_attributes(XMLDB_TYPE_CHAR, 20, null, null);
        if(!$dbman->field_exists($table, $table_use)) $dbman->add_field($table, $table_use); 
        
        $table = new xmldb_table('block_bcgt_value');
        $table_tqid = new xmldb_field('bcgttargetqualid');
        $table_tqid->set_attributes(XMLDB_TYPE_INTEGER, 20, null, null);
        if(!$dbman->field_exists($table, $table_tqid)) $dbman->add_field($table, $table_tqid);
        
        //change the ranking to a number
        $table_rank = new xmldb_field('ranking');
        $table_rank->set_attributes(XMLDB_TYPE_NUMBER, "3,1", null, null);
        if($dbman->field_exists($table, $table_rank)) $dbman->change_field_type($table, $table_rank);
        
        
        $index = new xmldb_index('bcgttypeid-shortvalue-ind', XMLDB_INDEX_UNIQUE, array('bcgttypeid', 'shortvalue'));
        //remove the unique component index from the type and value
        //drop the index
        if($dbman->index_exists($table, $index)) $dbman->drop_index($table, $index);
        
//        $index = new xmldb_index('blocbcgtvalu_bcgsho_uix');
//        if($dbman->index_exists($table, $index)) $dbman->drop_index($table, $index);
        
        $index = new xmldb_index('bcgttypeid-shortvalue-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgttypeid', 'shortvalue'));
        if(!$dbman->index_exists($table, $index)) $dbman->add_index($table, $index);
        
        
        //get rid of the prior_qual_breakdown table
        $table = new xmldb_table('block_bcgt_prior_breakdown');
        if($dbman->table_exists($table)) $dbman->drop_table($table);
        
        // Define field entryscoreupper to be added to block_bcgt_target_breakdown
        $table = new xmldb_table('block_bcgt_target_breakdown');
        $field = new xmldb_field('entryscoreupper', XMLDB_TYPE_NUMBER, '5, 2', null, null, null, null, 'ranking');

        // Conditionally launch add field entryscoreupper
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define field entryscorelower to be added to block_bcgt_target_breakdown
        $table = new xmldb_table('block_bcgt_target_breakdown');
        $field = new xmldb_field('entryscorelower', XMLDB_TYPE_NUMBER, '5, 2', null, null, null, null, 'entryscoreupper');

        // Conditionally launch add field entryscorelower
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        //add the autoincremental to the field id in table subject. 
        $table = new xmldb_table('block_bcgt_subject');
        $field = new xmldb_field('id');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 18, null, XMLDB_UNSIGNED, XMLDB_SEQUENCE);
        $dbman->change_field_type($table, $field);
        
        $subject = new stdClass();
        $subject->subject = 'N/A';
        $DB->insert_record('block_bcgt_subject', $subject);
        
        // Define table block_bcgt_core_subjects to be dropped
        $table = new xmldb_table('block_bcgt_core_subjects');

        // Conditionally launch drop table for block_bcgt_core_subjects
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        
        // Define field bcgtweightedbreakdownid to be added to block_bcgt_user_course_trgts
        $table = new xmldb_table('block_bcgt_user_course_trgts');
        $field = new xmldb_field('bcgtweightedbreakdownid', XMLDB_TYPE_INTEGER, '18', null, null, null, null, 'teacherset_date');

        // Conditionally launch add field bcgtweightedbreakdownid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
         // Define field bcgtweightedgradeid to be added to block_bcgt_user_course_trgts
        $table = new xmldb_table('block_bcgt_user_course_trgts');
        $field = new xmldb_field('bcgtweightedgradeid', XMLDB_TYPE_INTEGER, '18', null, null, null, null, 'bcgtweightedbreakdownid');

        // Conditionally launch add field bcgtweightedgradeid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        //Add the GCSE isnt the system
        $record = new stdClass();
        $record->name = 'GCSE';
        $record->weighting = 1;
        $gcseID = $DB->insert_record('block_bcgt_prior_qual', $record);
        
        $record = new stdClass();
        $record->name = 'GCSE Short Course';
        $record->weighting = 0.5;
        $gcseSCID = $DB->insert_record('block_bcgt_prior_qual', $record);
        
        $record = new stdClass();
        $record->name = 'GCSE Double Award';
        $record->weighting = 2;
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
        $record->points = 29;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'A';
        $record->points = 26;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'B';
        $record->points = 23;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'C';
        $record->points = 20;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'D';
        $record->points = 17;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'E';
        $record->points = 14;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'F';
        $record->points = 11;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'G';
        $record->points = 8;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'U';
        $record->points = 0;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        //Add the GCSE Grades
        $record = new stdClass();
        $record->bcgtpriorqualid = $gcseDAID;
        $record->grade = 'A*';
        $record->weighting = 1;
        $record->points = 116;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'A';
        $record->points = 104;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'B';
        $record->points = 92;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'C';
        $record->points = 80;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'D';
        $record->points = 68;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'E';
        $record->points = 56;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'F';
        $record->points = 44;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'G';
        $record->points = 32;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        $record->grade = 'U';
        $record->points = 0;
        $DB->insert_record('block_bcgt_prior_qual_grades', $record);
        
        // Define field averagegcsescore to be added to block_bcgt_user_prior
        $table = new xmldb_table('block_bcgt_user_prior');
        $field = new xmldb_field('averagegcsescore', XMLDB_TYPE_NUMBER, '5, 2', null, null, null, null, 'locked');

        // Conditionally launch add field averagegcsescore
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define table block_bcgt_project_att to be created
        $table = new xmldb_table('block_bcgt_project_att');

        // Adding fields to table block_bcgt_project_att
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('bcgtprojectid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '240', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_CHAR, '240', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_bcgt_project_att
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_bcgt_project_att
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        $table = new xmldb_table('block_bcgt_target_grades');
        $field = new xmldb_field('grade');
        $field->set_attributes(XMLDB_TYPE_CHAR, 20, null, null);
        if($dbman->field_exists($table, $field)) $dbman->change_field_precision($table, $field);
        
        $table = new xmldb_table('block_bcgt_user_activity_ref');
        $field = new xmldb_field('id');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 18, null, XMLDB_UNSIGNED, XMLDB_SEQUENCE);
        $dbman->change_field_type($table, $field);
    }
    
    if($oldversion < 2013083001)
    {
        $table = new xmldb_table('block_bcgt_qual_weighting');
        $table_rank = new xmldb_field('coefficient');
        $table_rank->set_attributes(XMLDB_TYPE_NUMBER, "4,2", null, null);
        if($dbman->field_exists($table, $table_rank)) $dbman->change_field_type($table, $table_rank); 
        
        $table = new xmldb_table('block_bcgt_qual_weighting');
        $table_rank = new xmldb_field('percentage');
        $table_rank->set_attributes(XMLDB_TYPE_NUMBER, "5,1", null, null);
        if($dbman->field_exists($table, $table_rank)) $dbman->change_field_type($table, $table_rank); 
    }
    
    if ($oldversion < 2013090400)
    {
        
        // Rename field value on table block_bcgt_user_outcome_obs to NEWNAMEGOESHERE
        $table = new xmldb_table('block_bcgt_user_outcome_obs');
        
        $key = new xmldb_key('bcgtvalueid-fk', XMLDB_KEY_FOREIGN, array('bcgtvalueid'), 'block_bcgt_value', array('id'));

        // Launch drop key bcgtcriteriaid-fk
        $dbman->drop_key($table, $key);
        
        
        $field = new xmldb_field('bcgtvalueid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'observationnum');

        // Launch rename field value
        if ($dbman->field_exists($table, $field)){
            $dbman->rename_field($table, $field, 'value');
        }
        
        $field = new xmldb_field('value', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'observationnum');

        // Launch change of default for field value
        if ($dbman->field_exists($table, $field)){
            $dbman->change_field_default($table, $field);
        }

    }
    
    if ($oldversion < 2013090402)
    {
        
        // Define key bcgtvalueid-fk (foreign) to be dropped form block_bcgt_user_soff_sht_rgs
        $table = new xmldb_table('block_bcgt_user_soff_sht_rgs');
        $key = new xmldb_key('bcgtvalueid-fk', XMLDB_KEY_FOREIGN, array('bcgtvalueid'), 'block_bcgt_value', array('id'));

        // Launch drop key bcgtvalueid-fk
        $dbman->drop_key($table, $key);
        
        $field = new xmldb_field('bcgtvalueid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, '0', 'observationnum');

        // Launch rename field bcgtvalueid
        if ($dbman->field_exists($table, $field)){
            $dbman->rename_field($table, $field, 'value');
        }
        
        $field = new xmldb_field('value', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, '0', 'observationnum');
        if ($dbman->field_exists($table, $field)){
            $dbman->change_field_default($table, $field);
        }

        
        
    }
    
    if ($oldversion < 2013090404)
    {
        
        $CG = $DB->get_record("block_bcgt_type", array("id" => 9));
        if ($CG)
        {
            if (is_null($CG->bcgtpathwaydeptid))
            {
                $CG->bcgtpathwaydeptid = 1;
            }
            if (is_null($CG->bcgtpathwaytypeid))
            {
                $CG->bcgtpathwaytypeid = 3;
            }
            $DB->update_record("block_bcgt_type", $CG);
        }
        
        
        
        $CGHBVRQ = $DB->get_record("block_bcgt_type", array("id" => 10));
        if ($CGHBVRQ)
        {
            if (is_null($CGHBVRQ->bcgtpathwaydeptid))
            {
                $CGHBVRQ->bcgtpathwaydeptid = 2;
            }
            if (is_null($CGHBVRQ->bcgtpathwaytypeid))
            {
                $CGHBVRQ->bcgtpathwaytypeid = 1;
            }
            $DB->update_record("block_bcgt_type", $CGHBVRQ);
        }
        
        
        
        $CGHBNVQ = $DB->get_record("block_bcgt_type", array("id" => 11));
        if ($CGHBNVQ)
        {
            if (is_null($CGHBNVQ->bcgtpathwaydeptid))
            {
                $CGHBNVQ->bcgtpathwaydeptid = 2;
            }
            if (is_null($CGHBNVQ->bcgtpathwaytypeid))
            {
                $CGHBNVQ->bcgtpathwaytypeid = 2;
            }
            $DB->update_record("block_bcgt_type", $CGHBNVQ);
        }
        
        
    }
    
    
    
    if ($oldversion < 2013090405){
        
        // Define field pathwaytypeid to be added to block_bcgt_qualification_his
        $table = new xmldb_table('block_bcgt_qualification_his');
        $field = new xmldb_field('pathwaytypeid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'noyears');

        // Conditionally launch add field pathwaytypeid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
    }
    
    if ($oldversion < 2013090500)
    {
        
        // Define field awarddate to be added to block_bcgt_user_criteria
        $table = new xmldb_table('block_bcgt_user_criteria');
        $field = new xmldb_field('awarddate', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'flag');

        // Conditionally launch add field awarddate
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        
        // Define field awarddate to be added to block_bcgt_user_criteria_his
        $table = new xmldb_table('block_bcgt_user_criteria_his');
        $field = new xmldb_field('awarddate', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'flag');

        // Conditionally launch add field awarddate
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        
    }
    
    if($oldversion < 2013091000)
    {
        $sql = "SELECT * FROM {block_bcgt_prior_qual} WHERE name = ?";
        $record = $DB->get_record_sql($sql, array('GCSE Short Course'));
        if($record)
        {
            $gcseSCID = $record->id;
            $grades = $DB->get_records_sql('SELECT * FROM {block_bcgt_prior_qual_grades} WHERE bcgtpriorqualid = ?', 
                    array($gcseSCID));
            if($grades)
            {
                foreach($grades AS $grade)
                {
                    $grade->points = $grade->points * 2;
                    $DB->update_record('block_bcgt_prior_qual_grades', $grade);
                }
            }
        }

        $sql = "SELECT * FROM {block_bcgt_prior_qual} WHERE name = ?";
        $record = $DB->get_record_sql($sql, array('GCSE Double Award'));
        if($record)
        {
            $gcseSCID = $record->id;
            $grades = $DB->get_records_sql('SELECT * FROM {block_bcgt_prior_qual_grades} WHERE bcgtpriorqualid = ?', 
                    array($gcseSCID));
            if($grades)
            {
                foreach($grades AS $grade)
                {
                    $grade->points = $grade->points * 0.5;
                    $DB->update_record('block_bcgt_prior_qual_grades', $grade);
                }
            }
        }
    }
    
    
    if ($oldversion < 2013091004)
    {
        
        // Changing nullability of field bcgttypeawardid on table block_bcgt_criteria_his to null
        $table = new xmldb_table('block_bcgt_criteria_his');
        $field = new xmldb_field('bcgttypeawardid', XMLDB_TYPE_INTEGER, '18', null, null, null, null, 'type');

        // Launch change of nullability for field bcgttypeawardid
        if ($dbman->field_exists($table, $field)){
            $dbman->change_field_notnull($table, $field);
        }
        
    }
    
    if($oldversion < 2013091005)
    {
        // Define field teacherset_breakdownid to be added to block_bcgt_user_course_trgts
        $table = new xmldb_table('block_bcgt_user_course_trgts');
        $field = new xmldb_field('teacherset_breakdownid', XMLDB_TYPE_INTEGER, '18', null, null, null, null, 'teacherset_targetid');

        // Conditionally launch add field teacherset_breakdownid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if($oldversion < 2013091203)
    {
        // Define field quallevel to be added to block_bcgt_prior_qual
        $table = new xmldb_table('block_bcgt_prior_qual');
        $field = new xmldb_field('quallevel', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'name');

        // Conditionally launch add field quallevel
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        //drop index
        
        // Define index name-ind (not unique) to be dropped form block_bcgt_prior_qual
        $table = new xmldb_table('block_bcgt_prior_qual');
        $index = new xmldb_index('name-ind', XMLDB_INDEX_NOTUNIQUE, array('name'));

        // Conditionally launch drop index name-ind
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        // Changing precision of field name on table block_bcgt_prior_qual to (250)
        $table = new xmldb_table('block_bcgt_prior_qual');
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '250', null, XMLDB_NOTNULL, null, null, 'id');

        // Launch change of precision for field name
        $dbman->change_field_precision($table, $field);
        
                //add index. 
         // Define index name-ind (not unique) to be added to block_bcgt_prior_qual
        $table = new xmldb_table('block_bcgt_prior_qual');
        $index = new xmldb_index('name-ind', XMLDB_INDEX_NOTUNIQUE, array('name'));

        // Conditionally launch add index name-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        $sql = "UPDATE {block_bcgt_prior_qual} SET quallevel = '2' WHERE name LIKE '%GCSE%'";
        $DB->execute($sql, array());
    }
    
    if($oldversion < 2013092000)
    {
        // Changing nullability of field bcgttypeawardid on table block_bcgt_user_unit_his to null
        $table = new xmldb_table('block_bcgt_user_unit_his');
        $field = new xmldb_field('bcgttypeawardid', XMLDB_TYPE_INTEGER, '18', null, null, null, null, 'bcgtunitid');

        // Launch change of nullability for field bcgttypeawardid
        $dbman->change_field_notnull($table, $field);
    }
    
    if ($oldversion < 2013092300){
        
        // Rename field bcgtvalueid on table block_bcgt_user_range to NEWNAMEGOESHERE
        $table = new xmldb_table('block_bcgt_user_range');
        $field = new xmldb_field('bcgttypeawardid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null, 'bcgtrangeid');

        // Launch rename field bcgtvalueid
        if ($dbman->field_exists($table, $field)){
            $dbman->rename_field($table, $field, 'bcgtvalueid');
        }
        
        
    }
    
    if ($oldversion < 2013092301){
        
        // Changing nullability of field targetdate on table block_bcgt_user_range to null
        $table = new xmldb_table('block_bcgt_user_range');
        $field = new xmldb_field('targetdate', XMLDB_TYPE_INTEGER, '18', null, null, null, null, 'bcgtvalueid');

        // Launch change of nullability for field targetdate
        $dbman->change_field_notnull($table, $field);
        
        
        
         // Changing nullability of field targetdate on table block_bcgt_user_range to null
        $table = new xmldb_table('block_bcgt_user_range');
        $field = new xmldb_field('awarddate', XMLDB_TYPE_INTEGER, '18', null, null, null, null);

        // Launch change of nullability for field awarddate
        $dbman->change_field_notnull($table, $field);
        
        
    }
    
    
    if ($oldversion < 2013092302){
        
        // Changing nullability of field targetdate on table block_bcgt_user_range to null
        $table = new xmldb_table('block_bcgt_user_criteria');
        $field = new xmldb_field('dateset', XMLDB_TYPE_INTEGER, '18', null, null, null, null, 'setbyuserid');

        // Launch change of nullability for field
        $dbman->change_field_notnull($table, $field);
        
        
        
        
        // Changing nullability of field targetdate on table block_bcgt_user_range to null
        $table = new xmldb_table('block_bcgt_user_criteria_his');
        $field = new xmldb_field('dateset', XMLDB_TYPE_INTEGER, '18', null, null, null, null, 'setbyuserid');

        // Launch change of nullability for field
        $dbman->change_field_notnull($table, $field);
        
    }
    
    
    if ($oldversion < 2013101100)
    {
        
        // Define key bcgttypeawardid-fk (foreign) to be dropped form block_bcgt_criteria
        $table = new xmldb_table('block_bcgt_criteria');
        $key = new xmldb_key('bcgttypeawardid-fk', XMLDB_KEY_FOREIGN, array('bcgttypeawardid'), 'block_bcgt_type_award', array('id'));

        // Launch drop key bcgttypeawardid-fk
        $dbman->drop_key($table, $key);
        
        // Changing nullability of field bcgttypeawardid on table block_bcgt_criteria_his to null
        $table = new xmldb_table('block_bcgt_criteria');
        $field = new xmldb_field('bcgttypeawardid', XMLDB_TYPE_INTEGER, '18', null, null, null, null, 'type');

        // Launch change of nullability for field bcgttypeawardid
        $dbman->change_field_notnull($table, $field);
        
    }
    
    
    if ($oldversion < 2013101500)
    {
        
        // Define table block_bcgt_custom_grades to be created
        $table = new xmldb_table('block_bcgt_custom_grades');

        // Adding fields to table block_bcgt_custom_grades
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('grade', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('ranking', XMLDB_TYPE_NUMBER, '5, 2', null, XMLDB_NOTNULL, null, '1');

        // Adding keys to table block_bcgt_custom_grades
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_bcgt_custom_grades
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        
        
        
        // Define table block_bcgt_stud_course_grade to be created
        $table = new xmldb_table('block_bcgt_stud_course_grade');

        // Adding fields to table block_bcgt_stud_course_grade
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('recordid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('setbyuserid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('settime', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_bcgt_stud_course_grade
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_bcgt_stud_course_grade
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        
        
    }
    
    if ($oldversion < 2013101501)
    {
        
        // Define field location to be added to block_bcgt_stud_course_grade
        $table = new xmldb_table('block_bcgt_stud_course_grade');
        $field = new xmldb_field('location', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'settime');

        // Conditionally launch add field location
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
    }
    
    
    if ($oldversion < 2013101600)
    {
        
        // Define field qualid to be added to block_bcgt_stud_course_grade
        $table = new xmldb_table('block_bcgt_stud_course_grade');
        $field = new xmldb_field('qualid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'courseid');

        // Conditionally launch add field qualid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
    }
    
    
    if ($oldversion < 2013101700)
    {
        
        // Define field gradingstructureid to be added to block_bcgt_bespoke_qual
        $table = new xmldb_table('block_bcgt_bespoke_qual');
        $field = new xmldb_field('gradingstructureid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'level');

        // Conditionally launch add field gradingstructureid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        
    }
    
    if ($oldversion < 2013101701)
    {
        
         // Define field img to be added to block_bcgt_bspk_c_grade_vals
        $table = new xmldb_table('block_bcgt_bspk_c_grade_vals');
        $field = new xmldb_field('img', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'met');

        // Conditionally launch add field img
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
    }
    
    if($oldversion < 2013102601)
    {
        //time to add all the missing indexes!!!!
        // Define index bcgtprojectid-ind (not unique) to be added to block_bcgt_activity_refs
        $table = new xmldb_table('block_bcgt_activity_refs');
        $index = new xmldb_index('bcgtprojectid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtprojectid'));

        //ad field bcgtprojectid
        $field = new xmldb_field('bcgtprojectid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 18, null, null);
        if(!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);
        
        // Conditionally launch add index bcgtprojectid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualid_projectid_ind (not unique) to be added to block_bcgt_activity_refs
        $table = new xmldb_table('block_bcgt_activity_refs');
        $index = new xmldb_index('bcgtqualid_projectid_ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'bcgtprojectid'));

        // Conditionally launch add index bcgtqualid_projectid_ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualid_unitid-ind (not unique) to be added to block_bcgt_activity_refs
        $table = new xmldb_table('block_bcgt_activity_refs');
        $index = new xmldb_index('bcgtqualid_unitid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'bcgtunitid'));

        // Conditionally launch add index bcgtqualid_unitid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualid_unitid_criteriaid-ind (not unique) to be added to block_bcgt_activity_refs
        $table = new xmldb_table('block_bcgt_activity_refs');
        $index = new xmldb_index('bcgtqualid_unitid_criteriaid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'bcgtunitid', 'bcgtcriteriaid'));

        // Conditionally launch add index bcgtqualid_unitid_criteriaid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualid_projectid_unitid (not unique) to be added to block_bcgt_activity_refs
        $table = new xmldb_table('block_bcgt_activity_refs');
        $index = new xmldb_index('bcgtqualid_projectid_unitid', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'bcgtprojectid', 'bcgtunitid'));

        // Conditionally launch add index bcgtqualid_projectid_unitid
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index coursemoduleid_bcgtprojectid-ind (not unique) to be added to block_bcgt_activity_refs
        $table = new xmldb_table('block_bcgt_activity_refs');
        $index = new xmldb_index('coursemoduleid_bcgtprojectid-ind', XMLDB_INDEX_NOTUNIQUE, array('coursemoduleid', 'bcgtprojectid'));

        // Conditionally launch add index coursemoduleid_bcgtprojectid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index coursemoduleid_qualid-ind (not unique) to be added to block_bcgt_activity_refs
        $table = new xmldb_table('block_bcgt_activity_refs');
        $index = new xmldb_index('coursemoduleid_qualid-ind', XMLDB_INDEX_NOTUNIQUE, array('coursemoduleid', 'bcgtqualificationid'));

        // Conditionally launch add index coursemoduleid_qualid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index coursemodule_qualid_projectid-ind (not unique) to be added to block_bcgt_activity_refs
        $table = new xmldb_table('block_bcgt_activity_refs');
        $index = new xmldb_index('coursemodule_qualid_projectid-ind', XMLDB_INDEX_NOTUNIQUE, array('coursemoduleid', 'bcgtqualificationid', 'bcgtprojectid'));

        // Conditionally launch add index coursemodule_qualid_projectid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index coursemoduleid_qualid_unitid-ind (not unique) to be added to block_bcgt_activity_refs
        $table = new xmldb_table('block_bcgt_activity_refs');
        $index = new xmldb_index('coursemoduleid_qualid_unitid-ind', XMLDB_INDEX_NOTUNIQUE, array('coursemoduleid', 'bcgtqualificationid', 'bcgtunitid'));

        // Conditionally launch add index coursemoduleid_qualid_unitid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index coursemoduleid_qualid_unitid_projectid-ind (not unique) to be added to block_bcgt_activity_refs
        $table = new xmldb_table('block_bcgt_activity_refs');
        $index = new xmldb_index('coursemoduleid_qualid_unitid_projectid-ind', XMLDB_INDEX_NOTUNIQUE, array('coursemoduleid', 'bcgtqualificationid', 'bcgtunitid', 'bcgtprojectid'));

        // Conditionally launch add index coursemoduleid_qualid_unitid_projectid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index courseid-ind (not unique) to be added to block_bcgt_course_qual_his
        $table = new xmldb_table('block_bcgt_course_qual_his');
        $index = new xmldb_index('courseid-ind', XMLDB_INDEX_NOTUNIQUE, array('courseid'));

        // Conditionally launch add index courseid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualificationid-ind (not unique) to be added to block_bcgt_course_qual_his
        $table = new xmldb_table('block_bcgt_course_qual_his');
        $index = new xmldb_index('bcgtqualificationid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid'));

        // Conditionally launch add index bcgtqualificationid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index coursequalid-ind (not unique) to be added to block_bcgt_course_qual_his
        $table = new xmldb_table('block_bcgt_course_qual_his');
        $index = new xmldb_index('coursequalid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtcoursequalid'));

        // Conditionally launch add index coursequalid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index courseid_qualid-ind (not unique) to be added to block_bcgt_course_qual_his
        $table = new xmldb_table('block_bcgt_course_qual_his');
        $index = new xmldb_index('courseid_qualid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'courseid'));

        // Conditionally launch add index courseid_qualid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualid_unitid-ind (not unique) to be added to block_bcgt_criteria
        $table = new xmldb_table('block_bcgt_criteria');
        $index = new xmldb_index('bcgtqualid_unitid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'bcgtunitid'));

        // Conditionally launch add index bcgtqualid_unitid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtunitid_name-ind (not unique) to be added to block_bcgt_criteria
        $table = new xmldb_table('block_bcgt_criteria');
        $index = new xmldb_index('bcgtunitid_name-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtunitid', 'name'));

        // Conditionally launch add index bcgtunitid_name-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualid_criteriaid-ind (not unique) to be added to block_bcgt_criteria_att
        $table = new xmldb_table('block_bcgt_criteria_att');
        $index = new xmldb_index('bcgtqualid_criteriaid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'bcgtcriteriaid'));

        // Conditionally launch add index bcgtqualid_criteriaid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualid_userid-ind (not unique) to be added to block_bcgt_criteria_att
        $table = new xmldb_table('block_bcgt_criteria_att');
        $index = new xmldb_index('bcgtqualid_userid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'userid'));

        // Conditionally launch add index bcgtqualid_userid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualid_userid_attribute-ind (not unique) to be added to block_bcgt_criteria_att
        $table = new xmldb_table('block_bcgt_criteria_att');
        $index = new xmldb_index('bcgtqualid_userid_attribute-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'userid', 'attribute'));

        // Conditionally launch add index bcgtqualid_userid_attribute-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualid_criteriaid_userid_attribute (not unique) to be added to block_bcgt_criteria_att
        $table = new xmldb_table('block_bcgt_criteria_att');
        $index = new xmldb_index('bcgtqualid_criteriaid_userid_attribute', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'bcgtcriteriaid', 'userid', 'attribute'));

        // Conditionally launch add index bcgtqualid_criteriaid_userid_attribute
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtcriteriaid-ind (not unique) to be added to block_bcgt_criteria_his
        $table = new xmldb_table('block_bcgt_criteria_his');
        $index = new xmldb_index('bcgtcriteriaid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtcriteriaid'));

        // Conditionally launch add index bcgtcriteriaid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtunitid-ind (not unique) to be added to block_bcgt_criteria_his
        $table = new xmldb_table('block_bcgt_criteria_his');
        $index = new xmldb_index('bcgtunitid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtunitid'));

        // Conditionally launch add index bcgtunitid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index parentcriteriaid-ind (not unique) to be added to block_bcgt_criteria_his
        $table = new xmldb_table('block_bcgt_criteria_his');
        $index = new xmldb_index('parentcriteriaid-ind', XMLDB_INDEX_NOTUNIQUE, array('parentcriteriaid'));

        // Conditionally launch add index parentcriteriaid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualid-ind (not unique) to be added to block_bcgt_criteria_his
        $table = new xmldb_table('block_bcgt_criteria_his');
        $index = new xmldb_index('bcgtqualid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid'));

        // Conditionally launch add index bcgtqualid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualid_unitid-ind (not unique) to be added to block_bcgt_criteria_his
        $table = new xmldb_table('block_bcgt_criteria_his');
        $index = new xmldb_index('bcgtqualid_unitid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'bcgtunitid'));

        // Conditionally launch add index bcgtqualid_unitid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index qualid_unitid_criteriaid-ind (not unique) to be added to block_bcgt_criteria_his
        $table = new xmldb_table('block_bcgt_criteria_his');
        $index = new xmldb_index('qualid_unitid_criteriaid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'bcgtunitid', 'bcgtcriteriaid'));

        // Conditionally launch add index qualid_unitid_criteriaid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index qualid_unitid_name (not unique) to be added to block_bcgt_criteria_his
        $table = new xmldb_table('block_bcgt_criteria_his');
        $index = new xmldb_index('qualid_unitid_name', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'bcgtunitid', 'name'));

        // Conditionally launch add index qualid_unitid_name
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index cenrallymanaged-ind (not unique) to be added to block_bcgt_project
        $table = new xmldb_table('block_bcgt_project');
        $index = new xmldb_index('cenrallymanaged-ind', XMLDB_INDEX_NOTUNIQUE, array('centrallymanaged'));

        // Conditionally launch add index cenrallymanaged-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index awarded-ind (not unique) to be added to block_bcgt_project
        $table = new xmldb_table('block_bcgt_project');
        $index = new xmldb_index('awarded-ind', XMLDB_INDEX_NOTUNIQUE, array('awarded'));

        // Conditionally launch add index awarded-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index centrallymanaged_awarded-ind (not unique) to be added to block_bcgt_project
        $table = new xmldb_table('block_bcgt_project');
        $index = new xmldb_index('centrallymanaged_awarded-ind', XMLDB_INDEX_NOTUNIQUE, array('centrallymanaged', 'awarded'));

        // Conditionally launch add index centrallymanaged_awarded-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtprojectid-ind (not unique) to be added to block_bcgt_project_att
        $table = new xmldb_table('block_bcgt_project_att');
        $index = new xmldb_index('bcgtprojectid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtprojectid'));

        // Conditionally launch add index bcgtprojectid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index name-ind (not unique) to be added to block_bcgt_project_att
        $table = new xmldb_table('block_bcgt_project_att');
        $index = new xmldb_index('name-ind', XMLDB_INDEX_NOTUNIQUE, array('name'));

        // Conditionally launch add index name-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtprojectid_name-ind (not unique) to be added to block_bcgt_project_att
        $table = new xmldb_table('block_bcgt_project_att');
        $index = new xmldb_index('bcgtprojectid_name-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtprojectid', 'name'));

        // Conditionally launch add index bcgtprojectid_name-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualid-ind (not unique) to be added to block_bcgt_qual_units_his
        $table = new xmldb_table('block_bcgt_qual_units_his');
        $index = new xmldb_index('bcgtqualid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid'));

        // Conditionally launch add index bcgtqualid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtunitsid-ind (not unique) to be added to block_bcgt_qual_units_his
        $table = new xmldb_table('block_bcgt_qual_units_his');
        $index = new xmldb_index('bcgtunitsid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtunitsid'));

        // Conditionally launch add index bcgtunitsid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualificationunitid-ind (not unique) to be added to block_bcgt_qual_units_his
        $table = new xmldb_table('block_bcgt_qual_units_his');
        $index = new xmldb_index('bcgtqualificationunitid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationunitid'));

        // Conditionally launch add index bcgtqualificationunitid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualid_unitid-ind (not unique) to be added to block_bcgt_qual_units_his
        $table = new xmldb_table('block_bcgt_qual_units_his');
        $index = new xmldb_index('bcgtqualid_unitid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'bcgtunitsid'));

        // Conditionally launch add index bcgtqualid_unitid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index qualid-ind (not unique) to be added to block_bcgt_qual_weighting
        $table = new xmldb_table('block_bcgt_qual_weighting');
        $index = new xmldb_index('qualid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid'));

        // Conditionally launch add index qualid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index coefficient-ind (not unique) to be added to block_bcgt_qual_weighting
        $table = new xmldb_table('block_bcgt_qual_weighting');
        $index = new xmldb_index('coefficient-ind', XMLDB_INDEX_NOTUNIQUE, array('coefficient'));

        // Conditionally launch add index coefficient-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index qualid_coefficient-ind (not unique) to be added to block_bcgt_qual_weighting
        $table = new xmldb_table('block_bcgt_qual_weighting');
        $index = new xmldb_index('qualid_coefficient-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'coefficient'));

        // Conditionally launch add index qualid_coefficient-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index percentage (not unique) to be added to block_bcgt_qual_weighting
        $table = new xmldb_table('block_bcgt_qual_weighting');
        $index = new xmldb_index('percentage', XMLDB_INDEX_NOTUNIQUE, array('percentage'));

        // Conditionally launch add index percentage
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index number-ind (not unique) to be added to block_bcgt_qual_weighting
        $table = new xmldb_table('block_bcgt_qual_weighting');
        $index = new xmldb_index('number-ind', XMLDB_INDEX_NOTUNIQUE, array('number'));

        // Conditionally launch add index number-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index qualid_percentage-ind (not unique) to be added to block_bcgt_qual_weighting
        $table = new xmldb_table('block_bcgt_qual_weighting');
        $index = new xmldb_index('qualid_percentage-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'percentage'));

        // Conditionally launch add index qualid_percentage-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index pointslower-ind (not unique) to be added to block_bcgt_unit_award
        $table = new xmldb_table('block_bcgt_unit_award');
        $index = new xmldb_index('pointslower-ind', XMLDB_INDEX_NOTUNIQUE, array('pointslower'));

        // Conditionally launch add index pointslower-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index pointsupper-ind (not unique) to be added to block_bcgt_unit_award
        $table = new xmldb_table('block_bcgt_unit_award');
        $index = new xmldb_index('pointsupper-ind', XMLDB_INDEX_NOTUNIQUE, array('pointsupper'));

        // Conditionally launch add index pointsupper-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index pointslower_pointsupper_typeaward (not unique) to be added to block_bcgt_unit_award
        $table = new xmldb_table('block_bcgt_unit_award');
        $index = new xmldb_index('pointslower_pointsupper_typeaward', XMLDB_INDEX_NOTUNIQUE, array('pointslower', 'pointsupper', 'bcgttypeawardid'));

        // Conditionally launch add index pointslower_pointsupper_typeaward
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index pointslower_pointsupper_unitid-ind (not unique) to be added to block_bcgt_unit_award
        $table = new xmldb_table('block_bcgt_unit_award');
        $index = new xmldb_index('pointslower_pointsupper_unitid-ind', XMLDB_INDEX_NOTUNIQUE, array('pointslower', 'pointsupper', 'bcgtunitid'));

        // Conditionally launch add index pointslower_pointsupper_unitid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index uniqueid-ind (not unique) to be added to block_bcgt_unit_history
        $table = new xmldb_table('block_bcgt_unit_history');
        $index = new xmldb_index('uniqueid-ind', XMLDB_INDEX_NOTUNIQUE, array('uniqueid'));

        // Conditionally launch add index uniqueid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index name-ind (not unique) to be added to block_bcgt_unit_history
        $table = new xmldb_table('block_bcgt_unit_history');
        $index = new xmldb_index('name-ind', XMLDB_INDEX_NOTUNIQUE, array('name'));

        // Conditionally launch add index name-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index uniqueid-name-ind (not unique) to be added to block_bcgt_unit_history
        $table = new xmldb_table('block_bcgt_unit_history');
        $index = new xmldb_index('uniqueid-name-ind', XMLDB_INDEX_NOTUNIQUE, array('uniqueid', 'name'));

        // Conditionally launch add index uniqueid-name-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtunitsid-ind (not unique) to be added to block_bcgt_unit_history
        $table = new xmldb_table('block_bcgt_unit_history');
        $index = new xmldb_index('bcgtunitsid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtunitsid'));

        // Conditionally launch add index bcgtunitsid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcglevelid-ind (not unique) to be added to block_bcgt_unit_history
        $table = new xmldb_table('block_bcgt_unit_history');
        $index = new xmldb_index('bcglevelid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtlevelid'));

        // Conditionally launch add index bcglevelid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgttypeid-ind (not unique) to be added to block_bcgt_unit_history
        $table = new xmldb_table('block_bcgt_unit_history');
        $index = new xmldb_index('bcgttypeid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgttypeid'));

        // Conditionally launch add index bcgttypeid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtunittypeid-ind (not unique) to be added to block_bcgt_unit_history
        $table = new xmldb_table('block_bcgt_unit_history');
        $index = new xmldb_index('bcgtunittypeid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtunittypeid'));

        // Conditionally launch add index bcgtunittypeid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }  
        
        // Define index bcgtactivityrefid-ind (not unique) to be added to block_bcgt_user_activity_ref
        $table = new xmldb_table('block_bcgt_user_activity_ref');
        $index = new xmldb_index('bcgtactivityrefid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtactivityrefid'));

        // Conditionally launch add index bcgtactivityrefid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid-ind (not unique) to be added to block_bcgt_user_activity_ref
        $table = new xmldb_table('block_bcgt_user_activity_ref');
        $index = new xmldb_index('userid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Conditionally launch add index userid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtvalueid-ind (not unique) to be added to block_bcgt_user_activity_ref
        $table = new xmldb_table('block_bcgt_user_activity_ref');
        $index = new xmldb_index('bcgtvalueid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtvalueid'));

        // Conditionally launch add index bcgtvalueid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgttargetgradesid (not unique) to be added to block_bcgt_user_activity_ref
        $table = new xmldb_table('block_bcgt_user_activity_ref');
        $index = new xmldb_index('bcgttargetgradesid', XMLDB_INDEX_NOTUNIQUE, array('bcgttargetgradesid'));

        // Conditionally launch add index bcgttargetgradesid
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgttargetbreakdownid-ind (not unique) to be added to block_bcgt_user_activity_ref
        $table = new xmldb_table('block_bcgt_user_activity_ref');
        $index = new xmldb_index('bcgttargetbreakdownid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgttargetbreakdownid'));

        // Conditionally launch add index bcgttargetbreakdownid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid_bcgtactivityrefid-ind (not unique) to be added to block_bcgt_user_activity_ref
        $table = new xmldb_table('block_bcgt_user_activity_ref');
        $index = new xmldb_index('userid_bcgtactivityrefid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'bcgtactivityrefid'));

        // Conditionally launch add index userid_bcgtactivityrefid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index type-ind (not unique) to be added to block_bcgt_user_award
        
        //drop all index of the column type:
        
        // Define index userid_qualid_type-ind (not unique) to be dropped form block_bcgt_user_award
        $table = new xmldb_table('block_bcgt_user_award');
        $index = new xmldb_index('userid_qualid_type-ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'bcgtqualificationid', 'type'));

        // Conditionally launch drop index userid_qualid_type-ind
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        
        // Define index qualid_courseid_type-ind (not unique) to be dropped form block_bcgt_user_award
        $table = new xmldb_table('block_bcgt_user_award');
        $index = new xmldb_index('qualid_courseid_type-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'courseid', 'type'));

        // Conditionally launch drop index qualid_courseid_type-ind
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        
        // Define index qualid_userid_courseid_type-ind (not unique) to be dropped form block_bcgt_user_award
        $table = new xmldb_table('block_bcgt_user_award');
        $index = new xmldb_index('qualid_userid_courseid_type-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'userid', 'courseid', 'type'));

        // Conditionally launch drop index qualid_userid_courseid_type-ind
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        
        // Define index type-ind (not unique) to be dropped form block_bcgt_user_award
        $table = new xmldb_table('block_bcgt_user_award');
        $index = new xmldb_index('type-ind', XMLDB_INDEX_NOTUNIQUE, array('type'));

        // Conditionally launch drop index type-ind
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        
        //need to alter the TYPE column: 
        // Changing type of field type on table block_bcgt_user_award to varchar 220
        $table = new xmldb_table('block_bcgt_user_award');
        $field = new xmldb_field('type');
        $field->set_attributes(XMLDB_TYPE_CHAR, 220, null, null);
        if($dbman->field_exists($table, $field)) $dbman->change_field_type($table, $field);
        
        $table = new xmldb_table('block_bcgt_user_award');
        $index = new xmldb_index('type-ind', XMLDB_INDEX_NOTUNIQUE, array('type'));

        // Conditionally launch add index type-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid_qualid-ind (not unique) to be added to block_bcgt_user_award
        $table = new xmldb_table('block_bcgt_user_award');
        $index = new xmldb_index('userid_qualid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'bcgtqualificationid'));

        // Conditionally launch add index userid_qualid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid_qualid_type-ind (not unique) to be added to block_bcgt_user_award
        $table = new xmldb_table('block_bcgt_user_award');
        $index = new xmldb_index('userid_qualid_type-ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'bcgtqualificationid', 'type'));

        // Conditionally launch add index userid_qualid_type-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index qualid_courseid-ind (not unique) to be added to block_bcgt_user_award
        $table = new xmldb_table('block_bcgt_user_award');
        $index = new xmldb_index('qualid_courseid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'courseid'));

        // Conditionally launch add index qualid_courseid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index qualid_courseid_type-ind (not unique) to be added to block_bcgt_user_award
        $table = new xmldb_table('block_bcgt_user_award');
        $index = new xmldb_index('qualid_courseid_type-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'courseid', 'type'));

        // Conditionally launch add index qualid_courseid_type-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index qualid_userid_courseid-ind (not unique) to be added to block_bcgt_user_award
        $table = new xmldb_table('block_bcgt_user_award');
        $index = new xmldb_index('qualid_userid_courseid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'userid', 'courseid'));

        // Conditionally launch add index qualid_userid_courseid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index qualid_userid_courseid_type-ind (not unique) to be added to block_bcgt_user_award
        $table = new xmldb_table('block_bcgt_user_award');
        $index = new xmldb_index('qualid_userid_courseid_type-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid', 'userid', 'courseid', 'type'));

        // Conditionally launch add index qualid_userid_courseid_type-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index qualid-ind (not unique) to be added to block_bcgt_user_course_trgts
        $table = new xmldb_table('block_bcgt_user_course_trgts');
        $index = new xmldb_index('qualid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid'));

        // Conditionally launch add index qualid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index courseid_qualid-ind (not unique) to be added to block_bcgt_user_course_trgts
        $table = new xmldb_table('block_bcgt_user_course_trgts');
        $index = new xmldb_index('courseid_qualid-ind', XMLDB_INDEX_NOTUNIQUE, array('courseid', 'bcgtqualificationid'));

        // Conditionally launch add index courseid_qualid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid_qualid-ind (not unique) to be added to block_bcgt_user_course_trgts
        $table = new xmldb_table('block_bcgt_user_course_trgts');
        $index = new xmldb_index('userid_qualid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'bcgtqualificationid'));

        // Conditionally launch add index userid_qualid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid_qualid_courseid-ind (not unique) to be added to block_bcgt_user_course_trgts
        $table = new xmldb_table('block_bcgt_user_course_trgts');
        $index = new xmldb_index('userid_qualid_courseid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'courseid', 'bcgtqualificationid'));

        // Conditionally launch add index userid_qualid_courseid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid_qualid_courseid-ind (not unique) to be added to block_bcgt_user_course_trgts
        $table = new xmldb_table('block_bcgt_user_course_trgts');
        $index = new xmldb_index('userid_qualid_courseid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'courseid', 'bcgtqualificationid'));

        // Conditionally launch add index userid_qualid_courseid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid_criteriaid-ind (not unique) to be added to block_bcgt_user_criteria
        $table = new xmldb_table('block_bcgt_user_criteria');
        $index = new xmldb_index('userid_criteriaid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'bcgtcriteriaid'));

        // Conditionally launch add index userid_criteriaid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid_criteriaid_qualid-ind (not unique) to be added to block_bcgt_user_criteria
        $table = new xmldb_table('block_bcgt_user_criteria');
        $index = new xmldb_index('userid_criteriaid_qualid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'bcgtcriteriaid', 'bcgtqualificationid'));

        // Conditionally launch add index userid_criteriaid_qualid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid_projectid-ind (not unique) to be added to block_bcgt_user_criteria
        $table = new xmldb_table('block_bcgt_user_criteria');
        $index = new xmldb_index('userid_projectid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'bcgtprojectid'));

        // Conditionally launch add index userid_projectid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid_projectid_criteriaid-ind (not unique) to be added to block_bcgt_user_criteria
        $table = new xmldb_table('block_bcgt_user_criteria');
        $index = new xmldb_index('userid_projectid_criteriaid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'bcgtprojectid', 'bcgtcriteriaid'));

        // Conditionally launch add index userid_projectid_criteriaid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid_qualid_projectid-ind (not unique) to be added to block_bcgt_user_criteria
        $table = new xmldb_table('block_bcgt_user_criteria');
        $index = new xmldb_index('userid_qualid_projectid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'bcgtqualificationid', 'bcgtprojectid'));

        // Conditionally launch add index userid_qualid_projectid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid_qualid_projectid_criteriaid-ind (not unique) to be added to block_bcgt_user_criteria
        $table = new xmldb_table('block_bcgt_user_criteria');
        $index = new xmldb_index('userid_qualid_projectid_criteriaid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'bcgtqualificationid', 'bcgtprojectid', 'bcgtcriteriaid'));

        // Conditionally launch add index userid_qualid_projectid_criteriaid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid-ind (not unique) to be added to block_bcgt_user_criteria_his
        $table = new xmldb_table('block_bcgt_user_criteria_his');
        $index = new xmldb_index('userid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Conditionally launch add index userid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtusercriteriaid-ind (not unique) to be added to block_bcgt_user_criteria_his
        $table = new xmldb_table('block_bcgt_user_criteria_his');
        $index = new xmldb_index('bcgtusercriteriaid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtusercriteriaid'));

        // Conditionally launch add index bcgtusercriteriaid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtcriteriaid-ind (not unique) to be added to block_bcgt_user_criteria_his
        $table = new xmldb_table('block_bcgt_user_criteria_his');
        $index = new xmldb_index('bcgtcriteriaid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtcriteriaid'));

        // Conditionally launch add index bcgtcriteriaid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtrangeid-ind (not unique) to be added to block_bcgt_user_criteria_his
        $table = new xmldb_table('block_bcgt_user_criteria_his');
        $index = new xmldb_index('bcgtrangeid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtrangeid'));

        // Conditionally launch add index bcgtrangeid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtvalueid-ind (not unique) to be added to block_bcgt_user_criteria_his
        $table = new xmldb_table('block_bcgt_user_criteria_his');
        $index = new xmldb_index('bcgtvalueid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtvalueid'));

        // Conditionally launch add index bcgtvalueid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtprojectid-ind (not unique) to be added to block_bcgt_user_criteria_his
        $table = new xmldb_table('block_bcgt_user_criteria_his');
        $index = new xmldb_index('bcgtprojectid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtprojectid'));

        // Conditionally launch add index bcgtprojectid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgttargetgradesid-ind (not unique) to be added to block_bcgt_user_criteria_his
        $table = new xmldb_table('block_bcgt_user_criteria_his');
        $index = new xmldb_index('bcgttargetgradesid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgttargetgradesid'));

        // Conditionally launch add index bcgttargetgradesid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgttargetbreakdownid-ind (not unique) to be added to block_bcgt_user_criteria_his
        $table = new xmldb_table('block_bcgt_user_criteria_his');
        $index = new xmldb_index('bcgttargetbreakdownid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgttargetbreakdownid'));

        // Conditionally launch add index bcgttargetbreakdownid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index role_qualid-ind (not unique) to be added to block_bcgt_user_qual
        $table = new xmldb_table('block_bcgt_user_qual');
        $index = new xmldb_index('role_qualid-ind', XMLDB_INDEX_NOTUNIQUE, array('roleid', 'bcgtqualificationid'));

        // Conditionally launch add index role_qualid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index role_userid-ind (not unique) to be added to block_bcgt_user_qual
        $table = new xmldb_table('block_bcgt_user_qual');
        $index = new xmldb_index('role_userid-ind', XMLDB_INDEX_NOTUNIQUE, array('roleid', 'userid'));

        // Conditionally launch add index role_userid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index role_qualid_userid-ind (not unique) to be added to block_bcgt_user_qual
        $table = new xmldb_table('block_bcgt_user_qual');
        $index = new xmldb_index('role_qualid_userid-ind', XMLDB_INDEX_NOTUNIQUE, array('roleid', 'bcgtqualificationid', 'userid'));

        // Conditionally launch add index role_qualid_userid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtuserqualid-ind (not unique) to be added to block_bcgt_user_qual_his
        $table = new xmldb_table('block_bcgt_user_qual_his');
        $index = new xmldb_index('bcgtuserqualid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtuserqualid'));

        // Conditionally launch add index bcgtuserqualid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualificationid-ind (not unique) to be added to block_bcgt_user_qual_his
        $table = new xmldb_table('block_bcgt_user_qual_his');
        $index = new xmldb_index('bcgtqualificationid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid'));

        // Conditionally launch add index bcgtqualificationid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid-ind (not unique) to be added to block_bcgt_user_qual_his
        $table = new xmldb_table('block_bcgt_user_qual_his');
        $index = new xmldb_index('userid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Conditionally launch add index userid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index roleid-ind (not unique) to be added to block_bcgt_user_qual_his
        $table = new xmldb_table('block_bcgt_user_qual_his');
        $index = new xmldb_index('roleid-ind', XMLDB_INDEX_NOTUNIQUE, array('roleid'));

        // Conditionally launch add index roleid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtuserunitid-ind (not unique) to be added to block_bcgt_user_unit_his
        $table = new xmldb_table('block_bcgt_user_unit_his');
        $index = new xmldb_index('bcgtuserunitid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtuserunitid'));

        // Conditionally launch add index bcgtuserunitid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index userid-ind (not unique) to be added to block_bcgt_user_unit_his
        $table = new xmldb_table('block_bcgt_user_unit_his');
        $index = new xmldb_index('userid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Conditionally launch add index userid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtqualificationid-ind (not unique) to be added to block_bcgt_user_unit_his
        $table = new xmldb_table('block_bcgt_user_unit_his');
        $index = new xmldb_index('bcgtqualificationid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtqualificationid'));

        // Conditionally launch add index bcgtqualificationid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtunitid-ind (not unique) to be added to block_bcgt_user_unit_his
        $table = new xmldb_table('block_bcgt_user_unit_his');
        $index = new xmldb_index('bcgtunitid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtunitid'));

        // Conditionally launch add index bcgtunitid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgttypeawardid-ind (not unique) to be added to block_bcgt_user_unit_his
        $table = new xmldb_table('block_bcgt_user_unit_his');
        $index = new xmldb_index('bcgttypeawardid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgttypeawardid'));

        // Conditionally launch add index bcgttypeawardid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgtvalueid-ind (not unique) to be added to block_bcgt_user_unit_his
        $table = new xmldb_table('block_bcgt_user_unit_his');
        $index = new xmldb_index('bcgtvalueid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtvalueid'));

        // Conditionally launch add index bcgtvalueid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgttargetqualid-ind (not unique) to be added to block_bcgt_value
        $table = new xmldb_table('block_bcgt_value');
        $index = new xmldb_index('bcgttargetqualid-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgttargetqualid'));

        // Conditionally launch add index bcgttargetqualid-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgttypeid_value-ind (not unique) to be added to block_bcgt_value
        $table = new xmldb_table('block_bcgt_value');
        $index = new xmldb_index('bcgttypeid_value-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgttypeid', 'value'));

        // Conditionally launch add index bcgttypeid_value-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgttargetqualid_shortvalue-ind (not unique) to be added to block_bcgt_value
        $table = new xmldb_table('block_bcgt_value');
        $index = new xmldb_index('bcgttargetqualid_shortvalue-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgttargetqualid', 'shortvalue'));

        // Conditionally launch add index bcgttargetqualid_shortvalue-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgttargetqualid_value-ind (not unique) to be added to block_bcgt_value
        $table = new xmldb_table('block_bcgt_value');
        $index = new xmldb_index('bcgttargetqualid_value-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgttargetqualid', 'value'));

        // Conditionally launch add index bcgttargetqualid_value-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index ranking-ind (not unique) to be added to block_bcgt_value
        $table = new xmldb_table('block_bcgt_value');
        $index = new xmldb_index('ranking-ind', XMLDB_INDEX_NOTUNIQUE, array('ranking'));

        // Conditionally launch add index ranking-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgttypeid_ranking-ind (not unique) to be added to block_bcgt_value
        $table = new xmldb_table('block_bcgt_value');
        $index = new xmldb_index('bcgttypeid_ranking-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgttypeid', 'ranking'));

        // Conditionally launch add index bcgttypeid_ranking-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index bcgttargetqualid_ranking-ind (not unique) to be added to block_bcgt_value
        $table = new xmldb_table('block_bcgt_value');
        $index = new xmldb_index('bcgttargetqualid_ranking-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgttargetqualid', 'ranking'));

        // Conditionally launch add index bcgttargetqualid_ranking-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        // Define index value-ind (not unique) to be added to block_bcgt_value_settings
        $table = new xmldb_table('block_bcgt_value_settings');
        $index = new xmldb_index('value-ind', XMLDB_INDEX_NOTUNIQUE, array('bcgtvalueid'));

        // Conditionally launch add index value-ind
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
    }

    if ($oldversion < 2013112000)
    {
        
        // Changing nullability of field courseid on table block_bcgt_stud_course_grade to null
        $table = new xmldb_table('block_bcgt_stud_course_grade');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'userid');

        // Launch change of nullability for field courseid
        $dbman->change_field_notnull($table, $field);
        
    }
    
    if ($oldversion < 2013112100)
    {
        
        // Changing type of field valueid2 on table block_bcgt_logs to text
        $table = new xmldb_table('block_bcgt_logs');
        $field = new xmldb_field('valueid2', XMLDB_TYPE_TEXT, null, null, null, null, null, 'valueid');

        // Launch change of type for field valueid2
        $dbman->change_field_type($table, $field);
        
        
        
        // Changing type of field valueid3 on table block_bcgt_logs to text
        $table = new xmldb_table('block_bcgt_logs');
        $field = new xmldb_field('valueid3', XMLDB_TYPE_TEXT, null, null, null, null, null, 'valueid2');

        // Launch change of type for field valueid3
        $dbman->change_field_type($table, $field);
        
    }
    
    if($oldversion < 2013112607)
    {
        $sql = "SELECT * FROM {block_bcgt_plugins} WHERE title = ?";
        $BTEC = $DB->get_record_sql($sql, array('BTEC'));
        if($BTEC)
        {
            echo "Checking BTEC version<br />";
            if($BTEC->version == 20131018000)
            {
                echo "UPDATING BTEC version<br />";
                $BTEC->version = 2013101800;
                $DB->update_record('block_bcgt_plugins',$BTEC);
            }
        }
    }
    
    //create the folder in the datadir
    if($oldversion < 2013120300)
    {
        global $CFG;
        //does the dir exist in the moodledata folder?
        if(!file_exists($CFG->dataroot.'/bcgt') && !is_dir($CFG->dataroot.'/bcgt'))
        {
            mkdir($CFG->dataroot.'/bcgt');
        }
        if(!file_exists($CFG->dataroot.'/bcgt/import') && !is_dir($CFG->dataroot.'/bcgt/import'))
        {
            mkdir($CFG->dataroot.'/bcgt/import');
        }
    }

    if ($oldversion < 2013120600) {

        // Define table block_bcgt_settings to be created
        $table = new xmldb_table('block_bcgt_settings');

        // Adding fields to table block_bcgt_settings
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('setting', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table block_bcgt_settings
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_bcgt_settings
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

    }
    
    if ($oldversion < 2013120601) {

        // Define field specificationdesc to be added to block_bcgt_type
        $table = new xmldb_table('block_bcgt_type');
        $field = new xmldb_field('specificationdesc', XMLDB_TYPE_TEXT, null, null, null, null, null, 'bcgtpathwaytypeid');

        // Conditionally launch add field specificationdesc
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        //new levels:
        
        //level 1 & 2
        $sql = "SELECT * FROM {block_bcgt_level} WHERE trackinglevel = ?";
        $record = $DB->get_record_sql($sql, array('Level 1 & 2'));
        if(!$record)
        {
            $stdObj = new stdClass();
            $stdObj->trackinglevel = 'Level 1 & 2';
            $DB->insert_record('block_bcgt_level', $stdObj);
        }

        // Define field shortaward to be added to block_bcgt_type_award
        $table = new xmldb_table('block_bcgt_type_award');
        $field = new xmldb_field('shortaward', XMLDB_TYPE_TEXT, null, null, null, null, null, 'pointsupper');

        // Conditionally launch add field shortaward
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        //need to change the length of targetgrade:
        // Changing precision of field targetgrade on table block_bcgt_target_breakdown to (50)
        $table = new xmldb_table('block_bcgt_target_breakdown');
        $field = new xmldb_field('targetgrade', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'bcgttargetqualid');

        // Launch change of precision for field targetgrade
        $dbman->change_field_precision($table, $field);
        
        // Changing precision of field grade on table block_bcgt_target_grades to (50)
        $table = new xmldb_table('block_bcgt_target_grades');
        $field = new xmldb_field('grade', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'bcgttargetqualid');

        // Launch change of precision for field grade
        $dbman->change_field_precision($table, $field);
    }
    
    if($oldversion < 2013120601)
    {
        // Define field displayname to be added to block_bcgt_criteria
        $table = new xmldb_table('block_bcgt_criteria');
        $field = new xmldb_field('displayname', XMLDB_TYPE_CHAR, '30', null, null, null, null, 'ordernum');

        // Conditionally launch add field displayname
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if($oldversion < 2013120601)
    {
        //seem to be missing the weighting
        // Define field weighting to be added to block_bcgt_criteria_his
        $table = new xmldb_table('block_bcgt_criteria_his');
        $field = new xmldb_field('weighting', XMLDB_TYPE_NUMBER, '4, 2', null, null, null, null, 'bcgtqualificationid');

        // Conditionally launch add field weighting
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define field ordernum to be added to block_bcgt_criteria_his
        $table = new xmldb_table('block_bcgt_criteria_his');
        $field = new xmldb_field('ordernum', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'weighting');

        // Conditionally launch add field ordernum
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define field displayname to be added to block_bcgt_criteria_his
        $table = new xmldb_table('block_bcgt_criteria_his');
        $field = new xmldb_field('displayname', XMLDB_TYPE_CHAR, '30', null, null, null, null, 'ordernum');

        // Conditionally launch add field displayname
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if ($oldversion < 2014010600)
    {
        
        // Define field courseid to be added to block_bcgt_custom_grades
        $table = new xmldb_table('block_bcgt_custom_grades');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');

        // Conditionally launch add field courseid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
    }
    
    if ($oldversion < 2014012900) {

         // Define table block_bcgt_user_group to be created
        $table = new xmldb_table('block_bcgt_user_grouping');

        // Adding fields to table block_bcgt_user_group
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('groupingid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);

        // Adding keys to table block_bcgt_user_group
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table block_bcgt_user_group
        $table->add_index('userid-ind', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        $table->add_index('courseid-ind', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
        $table->add_index('groupingid-ind', XMLDB_INDEX_NOTUNIQUE, array('groupingid'));
        $table->add_index('userid_courseid_ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'courseid'));
        $table->add_index('userid_groupingid_ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'groupingid'));
        $table->add_index('courseid_groupingid_ind', XMLDB_INDEX_NOTUNIQUE, array('courseid', 'groupingid'));
        $table->add_index('userid_courseid_groupingid_ind', XMLDB_INDEX_NOTUNIQUE, array('userid', 'courseid', 'groupingid'));

        // Conditionally launch create table for block_bcgt_user_group
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }
    
    if($oldversion < 2014012900)
    {
        $sql = "SELECT * FROM {config} WHERE name = ? AND value = ?";
        if(!$DB->get_record_sql($sql, array('dImp_default_staff_group_file', 'tutorongroup.csv')))
        {
            $record = new stdClass();
            $record->name = 'dImp_default_staff_group_file';
            $record->value = 'tutorongroup.csv';
            $DB->insert_record('config', $record);
        }
        
        if(!$DB->get_record_sql($sql, array('dImp_archive_staff_groups', 'staffgroups')))
        {
            $record = new stdClass();
            $record->name = 'dImp_archive_staff_groups';
            $record->value = 'staffgroups';
            $DB->insert_record('config', $record);
        }
        
        if(!$DB->get_record_sql($sql, array('dImp_errors_staff_group_file', 'staffgroupError')))
        {
            $record = new stdClass();
            $record->name = 'dImp_errors_staff_group_file';
            $record->value = 'staffgroupError';
            $DB->insert_record('config', $record);
        }
        
        if(!$DB->get_record_sql($sql, array('dImp_errors_staff_group', 'staffgroup')))
        {
            $record = new stdClass();
            $record->name = 'dImp_errors_staff_group';
            $record->value = 'staffgroup';
            $DB->insert_record('config', $record);
        }
    }
    
    if ($oldversion < 2014021708) {

        // Define table block_bcgt_mod_linking to be created
        $table = new xmldb_table('block_bcgt_mod_linking');

        // Adding fields to table block_bcgt_mod_linking
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('moduleid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modtablename', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('modtablecoursefname', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('modtableduedatefname', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('modsubmissiontable', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('submissionuserfname', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('submissiondatefname', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('submissionmodidfname', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('checkforautotracking', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        // Adding keys to table block_bcgt_mod_linking
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $performInitialInstall = false;
        // Conditionally launch create table for block_bcgt_mod_linking
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
            $performInitialInstall = true;
        }
        
        //now we create the standard ones. 
        if($performInitialInstall)
        {
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
                $stdObj->moduleid = $turnitin->id;
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
        }
        
        
    }
    
    
    //update required:
    //values
    //target grades
    //insert of data as well. 
    $result = true;
    $BCGT = new bcgt();
    $result = $BCGT->upgrade_plugins($oldversion);
    
    return $result;
    
}
