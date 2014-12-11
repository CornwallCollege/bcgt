<?php

function xmldb_block_bcgtalevel_upgrade($oldversion = 0)
{
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2013061300)
    {
        //if this was done as an install then the rankings were accidentally left off. 
        //So we need to add them on. 
        $sql = "SELECT * FROM {block_bcgt_value} value WHERE value = ? AND bcgttypeid = ?";
        $record = $DB->get_record_sql($sql, array('A*', 6));
        if($record)
        {
            $record->ranking = 7;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('A*/A', 6));
        if($record)
        {
            $record->ranking = 6.6;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('A/A*', 6));
        if($record)
        {
            $record->ranking = 6.3;
            $DB->update_record('block_bcgt_value', $record);
        }

        $record = $DB->get_record_sql($sql, array('A', 6));
        if($record)
        {
            $record->ranking = 6;
            $DB->update_record('block_bcgt_value', $record);
        }

        $record = $DB->get_record_sql($sql, array('A/B', 6));
        if($record)
        {
            $record->ranking = 5.6;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('B/A', 6));
        if($record)
        {
            $record->ranking = 5.3;
            $DB->update_record('block_bcgt_value', $record);
        }

        $record = $DB->get_record_sql($sql, array('B', 6));
        if($record)
        {
            $record->ranking = 5;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('B/C', 6));
        if($record)
        {
            $record->ranking = 4.6;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('C/B', 6));
        if($record)
        {
            $record->ranking = 4.3;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('C', 6));
        if($record)
        {
            $record->ranking = 4;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('C/D', 6));
        if($record)
        {
            $record->ranking = 3.6;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('D/C', 6));
        if($record)
        {
            $record->ranking = 3.3;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('D', 6));
        if($record)
        {
            $record->ranking = 3;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('D/E', 6));
        if($record)
        {
            $record->ranking = 2.6;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('E/D', 6));
        if($record)
        {
            $record->ranking = 2.3;
            $DB->update_record('block_bcgt_value', $record);
        }

        $record = $DB->get_record_sql($sql, array('E', 6));
        if($record)
        {
            $record->ranking = 2;
            $DB->update_record('block_bcgt_value', $record);
        }

        $record = $DB->get_record_sql($sql, array('F', 6));
        if($record)
        {
            $record->ranking = 1;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('U', 6));
        if($record)
        {
            $record->ranking = 0;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('Work Not Submitted', 6));
        if($record)
        {
            $record->ranking = -1;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('Work Submitted', 6));
        if($record)
        {
            $record->ranking = -2;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('Late', 6));
        if($record)
        {
            $record->ranking = -3;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('Not Attempted', 6));
        if($record)
        {
            $record->ranking = -4;
            $DB->update_record('block_bcgt_value', $record);
        }
    }
    
    if ($oldversion < 2013061900)
    {
        //need to alter all of the values with 
        //special val and also enabled
        $sql = "UPDATE {block_bcgt_value} SET enabled = ? WHERE bcgttypeid = ?";
        $DB->execute($sql, array(1, 6));
        
        $sql = "UPDATE {block_bcgt_value} SET specialval = ? WHERE bcgttypeid = ? AND 
            shortvalue NOT IN (?,?,?,?,?)";
        $DB->execute($sql, array('A', 6, 'U','WNS','IN','L','N/A'));
        
        $sql = "UPDATE {block_bcgt_value} SET specialval = ? WHERE bcgttypeid = ? AND 
            shortvalue = ? ";
        $DB->execute($sql, array('X', 6, 'U'));
        
        $sql = "UPDATE {block_bcgt_value} SET specialval = ? WHERE bcgttypeid = ? AND 
            shortvalue = ? ";
        $DB->execute($sql, array('WNS', 6, 'WNS'));
        
        $sql = "UPDATE {block_bcgt_value} SET specialval = ? WHERE bcgttypeid = ? AND 
            shortvalue = ? ";
        $DB->execute($sql, array('IN', 6, 'WS'));
        
        $sql = "UPDATE {block_bcgt_value} SET specialval = ? WHERE bcgttypeid = ? AND 
            shortvalue = ? ";
        $DB->execute($sql, array('L', 6, 'L'));
        
        $sql = "UPDATE {block_bcgt_value} SET specialval = ? WHERE bcgttypeid = ? AND 
            shortvalue = ? ";
        $DB->execute($sql, array('', 6, 'N/A'));
    }
    
    if($oldversion < 2013062001)
    {
        //inserting missing records
        //first check they arent there! Twit!
        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND value = ? ";
        $record = $DB->get_record_sql($sql, array(6, 'A*/A'));
        if(!$record)
        {
            $record = new stdClass();
            $record->value = 'A*/A';
            $record->shortvalue = 'A*/A';
            $record->bcgttypeid = 6;
            $record->specialval = 'A';
            $record->ranking = 6.6;
            $record->enabled = 1;
            $DB->insert_record('block_bcgt_value', $record);
        }
        
        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND value = ? ";
        $record = $DB->get_record_sql($sql, array(6, 'A/A*'));
        if(!$record)
        {
            $record = new stdClass();
            $record->value = 'A/A*';
            $record->shortvalue = 'A/A*';
            $record->bcgttypeid = 6;
            $record->specialval = 'A';
            $record->ranking = 6.3;
            $record->enabled = 1;
            $DB->insert_record('block_bcgt_value', $record);
        }

        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND value = ? ";
        $record = $DB->get_record_sql($sql, array(6, 'A/B'));
        if(!$record)
        {
            $record = new stdClass();
            $record->value = 'A/B';
            $record->shortvalue = 'A/B';
            $record->bcgttypeid = 6;
            $record->specialval = 'A';
            $record->ranking = 5.6;
            $record->enabled = 1;
            $DB->insert_record('block_bcgt_value', $record);
        }

        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND value = ? ";
        $record = $DB->get_record_sql($sql, array(6, 'B/A'));
        if(!$record)
        {
            $record = new stdClass();
            $record->value = 'B/A';
            $record->shortvalue = 'B/A';
            $record->bcgttypeid = 6;
            $record->specialval = 'A';
            $record->ranking = 5.3;
            $record->enabled = 1;
            $DB->insert_record('block_bcgt_value', $record);
        }

        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND value = ? ";
        $record = $DB->get_record_sql($sql, array(6, 'B/C'));
        if(!$record)
        {
            $record = new stdClass();
            $record->value = 'B/C';
            $record->shortvalue = 'B/C';
            $record->bcgttypeid = 6;
            $record->specialval = 'A';
            $record->ranking = 4.6;
            $record->enabled = 1;
            $DB->insert_record('block_bcgt_value', $record);
        }

        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND value = ? ";
        $record = $DB->get_record_sql($sql, array(6, 'C/B'));
        if(!$record)
        {
            $record = new stdClass();
            $record->value = 'C/B';
            $record->shortvalue = 'C/B';
            $record->bcgttypeid = 6;
            $record->specialval = 'A';
            $record->ranking = 4.3;
            $record->enabled = 1;
            $DB->insert_record('block_bcgt_value', $record);
        }

        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND value = ? ";
        $record = $DB->get_record_sql($sql, array(6, 'C/D'));
        if(!$record)
        {
            $record = new stdClass();
            $record->value = 'C/D';
            $record->shortvalue = 'C/D';
            $record->bcgttypeid = 6;
            $record->specialval = 'A';
            $record->ranking = 3.6;
            $record->enabled = 1;
            $DB->insert_record('block_bcgt_value', $record);
        }

        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND value = ? ";
        $record = $DB->get_record_sql($sql, array(6, 'D/C'));
        if(!$record)
        {
            $record = new stdClass();
            $record->value = 'D/C';
            $record->shortvalue = 'D/C';
            $record->bcgttypeid = 6;
            $record->specialval = 'A';
            $record->ranking = 3.3;
            $record->enabled = 1;
            $DB->insert_record('block_bcgt_value', $record);
        }

        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND value = ? ";
        $record = $DB->get_record_sql($sql, array(6, 'D/E'));
        if(!$record)
        {
            $record = new stdClass();
            $record->value = 'D/E';
            $record->shortvalue = 'D/E';
            $record->bcgttypeid = 6;
            $record->specialval = 'A';
            $record->ranking = 2.6;
            $record->enabled = 1;
            $DB->insert_record('block_bcgt_value', $record);
        }

        $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? AND value = ? ";
        $record = $DB->get_record_sql($sql, array(6, 'E/D'));
        if(!$record)
        {
            $record = new stdClass();
            $record->value = 'E/D';
            $record->shortvalue = 'E/D';
            $record->bcgttypeid = 6;
            $record->specialval = 'A';
            $record->ranking = 2.3;
            $record->enabled = 1;
            $DB->insert_record('block_bcgt_value', $record);
        }
    }
    
    if ($oldversion < 2013081200)
    {
        $sql = "UPDATE {block_bcgt_value} SET context = ? WHERE bcgttypeid = ?";
        $DB->execute($sql, array('assessment', 6));
        
        //add the breakdown scores and the target grade scores
        $sql = "SELECT id FROM {block_bcgt_target_qual} WHERE bcgttypeid = ? 
            AND bcgtsubtypeid = ? AND bcgtlevelid = ?";
        $asTargetQual = $DB->get_record_sql($sql, array(7, 12, 3));
        if($asTargetQual)
        {
            $asTargetQualID = $asTargetQual->id;
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'AAAB'));
            if($record)
            {
                $record->entryscoreupper = 58.0;
                $record->entryscorelower = 55.0;
                $record->ranking = 9;
                $record->ucaspoints = 230;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'A*'));
            if($record)
            {
                $record->ucaspoints = 70;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'A*/A'));
            if($record)
            {
                $record->ucaspoints = 66.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'A/A*'));
            if($record)
            {
                $record->ucaspoints = 63.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'A'));
            if($record)
            {
                $record->upperscore = 58.0;
                $record->lowerscore = 55.0;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'A/B'));
            if($record)
            {
                $record->ucaspoints = 56.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'B/A'));
            if($record)
            {
                $record->ucaspoints = 53.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $asTargetQualID = $asTargetQual->id;
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'ABBB'));
            if($record)
            {
                $record->entryscoreupper = 55.0;
                $record->entryscorelower = 52.0;
                $record->ranking = 8;
                $record->ucaspoints = 210;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
//            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
//            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'B'));
//            if($record)
//            {
//                $record->upperscore = 55.0;
//                $record->lowerscore = 52.0;
//                $DB->update_record('block_bcgt_target_grades', $record);
//            }
            
            $asTargetQualID = $asTargetQual->id;
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'BBBC'));
            if($record)
            {
                $record->entryscoreupper = 52;
                $record->entryscorelower = 50.2;
                $record->ranking = 7;
                $record->ucaspoints = 190;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'B'));
            if($record)
            {
                $record->upperscore = 55;
                $record->lowerscore = 50.2;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'B/C'));
            if($record)
            {
                $record->ucaspoints = 46.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'C/B'));
            if($record)
            {
                $record->ucaspoints = 43.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }

            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'BBCC'));
            if($record)
            {
                $record->entryscoreupper = 50.2;
                $record->entryscorelower = 48.4;
                $record->ranking = 6;
                $record->ucaspoints = 180;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'B/C'));
            if($record)
            {
                $record->upperscore = 50.2;
                $record->lowerscore = 48.4;
                $DB->update_record('block_bcgt_target_grades', $record);
            }

            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'CCCC'));
            if($record)
            {
                $record->entryscoreupper = 48.4;
                $record->entryscorelower = 46.6;
                $record->ranking = 5;
                $record->ucaspoints = 160;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'C'));
            if($record)
            {
                $record->upperscore = 48.4;
                $record->lowerscore = 46.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'C/D'));
            if($record)
            {
                $record->ucaspoints = 36.6;
                $record->ranking = 3.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'D/C'));
            if($record)
            {
                $record->ucaspoints = 33.3;
                $record->ranking = 3.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'CCDD'));
            if($record)
            {
                $record->entryscoreupper = 46.6;
                $record->entryscorelower = 44.8;
                $record->ranking = 4;
                $record->ucaspoints = 140;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
//            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
//            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'C/D'));
//            if($record)
//            {
//                $record->upperscore = 46.6;
//                $record->lowerscore = 44.8;
//                $DB->update_record('block_bcgt_target_grades', $record);
//            }
            
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'CDDD'));
            if($record)
            {
                $record->entryscoreupper = 44.8;
                $record->entryscorelower = 43.0;
                $record->ranking = 3;
                $record->ucaspoints = 130;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'C/D'));
            if($record)
            {
                $record->upperscore = 46.6;
                $record->lowerscore = 43.0;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'DDDD'));
            if($record)
            {
                $record->entryscoreupper = 43.0;
                $record->entryscorelower = 41.2;
                $record->ranking = 2;
                $record->ucaspoints = 120;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
//            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
//            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'D'));
//            if($record)
//            {
//                $record->upperscore = 43.0;
//                $record->lowerscore = 41.2;
//                $DB->update_record('block_bcgt_target_grades', $record);
//            }
            
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'DDDE/DDE'));
            if($record)
            {
                $record->entryscoreupper = 41.2;
                $record->entryscorelower = 38.2;
                $record->ranking = 1.6;
                $record->ucaspoints = 0;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'D'));
            if($record)
            {
                $record->upperscore = 43.0;
                $record->lowerscore = 38.2;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'D/E'));
            if($record)
            {
                $record->ucaspoints = 26.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'E/D'));
            if($record)
            {
                $record->ucaspoints = 23.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
//            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
//            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'DDE'));
//            if($record)
//            {
//                $record->entryscoreupper = 38.2;
//                $record->entryscorelower = 34.0;
//                $DB->update_record('block_bcgt_target_breakdown', $record);
//            }
            
//            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
//            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'D/E'));
//            if($record)
//            {
//                $record->upperscore = 38.2;
//                $record->lowerscore = 34.0;
//                $DB->update_record('block_bcgt_target_grades', $record);
//            }
            
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'DDE'));
            if($record)
            {
                $record->entryscoreupper = 38.2;
                $record->entryscorelower = 10.0;
                $record->ranking = 1;
                $record->ucaspoints = 80;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'D/E'));
            if($record)
            {
                $record->upperscore = 38.2;
                $record->lowerscore = 10.0;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
        }
        
        
        $sql = "SELECT id FROM {block_bcgt_target_qual} WHERE bcgttypeid = ? 
            AND bcgtsubtypeid = ? AND bcgtlevelid = ?";
        $a2TargetQual = $DB->get_record_sql($sql, array(8, 13, 3));
        if($a2TargetQual)
        {
            $a2TargetQualID = $a2TargetQual->id;
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'A*AAA'));
            if($record)
            {
                $record->entryscoreupper = 58.0;
                $record->entryscorelower = 55.0;
                $record->ranking = 10;
                $record->ucaspoints = 500;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'A*/A'));
            if($record)
            {
                $record->upperscore = 58.0;
                $record->lowerscore = 55.0;
                $record->ucaspoints = 133.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'A/A*'));
            if($record)
            {
                $record->ucaspoints = 126.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            } 
            
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'AAA'));
            if($record)
            {
                $record->entryscoreupper = 55.0;
                $record->entryscorelower = 52.0;
                $record->ranking = 9;
                $record->ucaspoints = 360;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'A'));
            if($record)
            {
                $record->upperscore = 55.0;
                $record->lowerscore = 52.0;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'A/B'));
            if($record)
            {
                $record->ucaspoints = 113.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            } 
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'B/A'));
            if($record)
            {
                $record->ucaspoints = 106.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            } 
            
            $asTargetQualID = $asTargetQual->id;
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'ABB'));
            if($record)
            {
                $record->entryscoreupper = 52;
                $record->entryscorelower = 50.2;
                $record->ranking = 8;
                $record->ucaspoints = 320;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
//            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
//            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'B'));
//            if($record)
//            {
//                $record->upperscore = 52;
//                $record->lowerscore = 50.2;
//                $DB->update_record('block_bcgt_target_grades', $record);
//            }

            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'BBB'));
            if($record)
            {
                $record->entryscoreupper = 50.2;
                $record->entryscorelower = 48.4;
                $record->ranking = 7;
                $record->ucaspoints = 300;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'B'));
            if($record)
            {
                $record->upperscore = 52.0;
                $record->lowerscore = 48.4;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'B/C'));
            if($record)
            {
                $record->ucaspoints = 93.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            } 
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'C/B'));
            if($record)
            {
                $record->ucaspoints = 86.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            } 

            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'BBC'));
            if($record)
            {
                $record->entryscoreupper = 48.4;
                $record->entryscorelower = 46.6;
                $record->ranking = 6;
                $record->ucaspoints = 280;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
//            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
//            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'B/C'));
//            if($record)
//            {
//                $record->upperscore = 48.4;
//                $record->lowerscore = 46.6;
//                $DB->update_record('block_bcgt_target_grades', $record);
//            }
            
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'BCC'));
            if($record)
            {
                $record->entryscoreupper = 46.6;
                $record->entryscorelower = 44.8;
                $record->ranking = 5;
                $record->ucaspoints = 260;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'B/C'));
            if($record)
            {
                $record->upperscore = 48.4;
                $record->lowerscore = 44.8;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'CCC'));
            if($record)
            {
                $record->entryscoreupper = 44.8;
                $record->entryscorelower = 43.0;
                $record->ranking = 4;
                $record->ucaspoints = 240;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
//            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
//            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'C'));
//            if($record)
//            {
//                $record->upperscore = 44.8;
//                $record->lowerscore = 43.0;
//                $DB->update_record('block_bcgt_target_grades', $record);
//            }
            
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'CCD'));
            if($record)
            {
                $record->entryscoreupper = 43.0;
                $record->entryscorelower = 41.2;
                $record->ranking = 3;
                $record->ucaspoints = 220;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'C'));
            if($record)
            {
                $record->upperscore = 44.8;
                $record->lowerscore = 41.2;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'C/D'));
            if($record)
            {
                $record->ucaspoints = 73.3;
                $record->ranking = 3.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            } 
                                    
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'D/C'));
            if($record)
            {
                $record->ucaspoints = 66.6;
                $record->ranking = 3.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            } 
            
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'CDD'));
            if($record)
            {
                $record->entryscoreupper = 41.2;
                $record->entryscorelower = 38.2;
                $record->ranking = 2;
                $record->ucaspoints = 200;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'C/D'));
            if($record)
            {
                $record->upperscore = 41.2;
                $record->lowerscore = 38.2;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
//            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
//            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'DDD'));
//            if($record)
//            {
//                $record->entryscoreupper = 38.2;
//                $record->entryscorelower = 34.0;
//                $DB->update_record('block_bcgt_target_breakdown', $record);
//            }
            
//            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
//            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'D'));
//            if($record)
//            {
//                $record->upperscore = 38.2;
//                $record->lowerscore = 34.0;
//                $DB->update_record('block_bcgt_target_grades', $record);
//            }
            
            $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? AND targetgrade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'DDD'));
            if($record)
            {
                $record->entryscoreupper = 38.2;
                $record->entryscorelower = 10.0;
                $record->ranking = 1;
                $record->ucaspoints = 180;
                $DB->update_record('block_bcgt_target_breakdown', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'D'));
            if($record)
            {
                $record->upperscore = 38.2;
                $record->lowerscore = 10.0;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'D/E'));
            if($record)
            {
                $record->ucaspoints = 53.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'E/D'));
            if($record)
            {
                $record->ucaspoints = 46.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
           
        }
        
        //the rankinks were set to single number. . 
        //So we need to re update them. 
        $sql = "SELECT * FROM {block_bcgt_value} value WHERE value = ? AND bcgttypeid = ?";
        $record = $DB->get_record_sql($sql, array('A*', 6));
        if($record)
        {
            $record->ranking = 7;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('A*/A', 6));
        if($record)
        {
            $record->ranking = 6.6;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('A/A*', 6));
        if($record)
        {
            $record->ranking = 6.3;
            $DB->update_record('block_bcgt_value', $record);
        }

        $record = $DB->get_record_sql($sql, array('A', 6));
        if($record)
        {
            $record->ranking = 6;
            $DB->update_record('block_bcgt_value', $record);
        }

        $record = $DB->get_record_sql($sql, array('A/B', 6));
        if($record)
        {
            $record->ranking = 5.6;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('B/A', 6));
        if($record)
        {
            $record->ranking = 5.3;
            $DB->update_record('block_bcgt_value', $record);
        }

        $record = $DB->get_record_sql($sql, array('B', 6));
        if($record)
        {
            $record->ranking = 5;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('B/C', 6));
        if($record)
        {
            $record->ranking = 4.6;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('C/B', 6));
        if($record)
        {
            $record->ranking = 4.3;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('C', 6));
        if($record)
        {
            $record->ranking = 4;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('C/D', 6));
        if($record)
        {
            $record->ranking = 3.6;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('D/C', 6));
        if($record)
        {
            $record->ranking = 3.3;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('D', 6));
        if($record)
        {
            $record->ranking = 3;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('D/E', 6));
        if($record)
        {
            $record->ranking = 2.6;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('E/D', 6));
        if($record)
        {
            $record->ranking = 2.3;
            $DB->update_record('block_bcgt_value', $record);
        }

        $record = $DB->get_record_sql($sql, array('E', 6));
        if($record)
        {
            $record->ranking = 2;
            $DB->update_record('block_bcgt_value', $record);
        }

        $record = $DB->get_record_sql($sql, array('F', 6));
        if($record)
        {
            $record->ranking = 1;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('U', 6));
        if($record)
        {
            $record->ranking = 0;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('Work Not Submitted', 6));
        if($record)
        {
            $record->ranking = -1;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('Work Submitted', 6));
        if($record)
        {
            $record->ranking = -2;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('Late', 6));
        if($record)
        {
            $record->ranking = -3;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = $DB->get_record_sql($sql, array('Not Attempted', 6));
        if($record)
        {
            $record->ranking = -4;
            $DB->update_record('block_bcgt_value', $record);
        }
        
        $record = new stdClass();
        $record->name = 'Grade Tracker Alevel Scale';
        $record->scale = 'U,F,E,E/D,D/E,D,D/C,C/D,C,C/B,B/C,B,B/A,A/B,A,A/A*,A*/A,A*';
        $record->description = 'Scale to be used with Alevel Grade Tracker activities';
        $DB->insert_record('scale', $record);
    }
    
    if($oldversion < 2013090200)
    {
        //we need to revert the rankings for some intallations
        $sql = "SELECT id FROM {block_bcgt_target_qual} WHERE bcgttypeid = ? 
            AND bcgtsubtypeid = ? AND bcgtlevelid = ?";
        $a2TargetQual = $DB->get_record_sql($sql, array(8, 13, 3));
        if($a2TargetQual)
        {
            $a2TargetQualID = $a2TargetQual->id;
            
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'A*'));
            if($record)
            {
                $record->ranking = 7;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'A*/A'));
            if($record)
            {
                $record->ranking = 6.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'A/A*'));
            if($record)
            {
                $record->ranking = 6.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'A'));
            if($record)
            {
                $record->ranking = 6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'A/B'));
            if($record)
            {
                $record->ranking = 5.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'B/A'));
            if($record)
            {
                $record->ranking = 5.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'B'));
            if($record)
            {
                $record->ranking = 5;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'B/C'));
            if($record)
            {
                $record->ranking = 4.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'C/B'));
            if($record)
            {
                $record->ranking = 4.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'C'));
            if($record)
            {
                $record->ranking = 4;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'C/D'));
            if($record)
            {
                $record->ranking = 3.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'D/C'));
            if($record)
            {
                $record->ranking = 3.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'D'));
            if($record)
            {
                $record->ranking = 3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'D/E'));
            if($record)
            {
                $record->ranking = 2.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'E/D'));
            if($record)
            {
                $record->ranking = 2.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'E'));
            if($record)
            {
                $record->ranking = 2;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'F'));
            if($record)
            {
                $record->ranking = 1;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($a2TargetQualID, 'U'));
            if($record)
            {
                $record->ranking = 0;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
        }
        
        //add the breakdown scores and the target grade scores
        $sql = "SELECT id FROM {block_bcgt_target_qual} WHERE bcgttypeid = ? 
            AND bcgtsubtypeid = ? AND bcgtlevelid = ?";
        $asTargetQual = $DB->get_record_sql($sql, array(7, 12, 3));
        if($asTargetQual)
        {
            $asTargetQualID = $asTargetQual->id;
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'A*'));
            if($record)
            {
                $record->ranking = 7;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'A*/A'));
            if($record)
            {
                $record->ranking = 6.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'A/A*'));
            if($record)
            {
                $record->ranking = 6.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'A'));
            if($record)
            {
                $record->ranking = 6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'A/B'));
            if($record)
            {
                $record->ranking = 5.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'B/A'));
            if($record)
            {
                $record->ranking = 5.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'B'));
            if($record)
            {
                $record->ranking = 5;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'B/C'));
            if($record)
            {
                $record->ranking = 4.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'C/B'));
            if($record)
            {
                $record->ranking = 4.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'C'));
            if($record)
            {
                $record->ranking = 4;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'C/D'));
            if($record)
            {
                $record->ranking = 3.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'D/C'));
            if($record)
            {
                $record->ranking = 3.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'D'));
            if($record)
            {
                $record->ranking = 3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'D/E'));
            if($record)
            {
                $record->ranking = 2.6;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'E/D'));
            if($record)
            {
                $record->ranking = 2.3;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'E'));
            if($record)
            {
                $record->ranking = 2;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'F'));
            if($record)
            {
                $record->ranking = 1;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
            $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? AND grade = ? ";
            $record = $DB->get_record_sql($sql, array($asTargetQualID, 'U'));
            if($record)
            {
                $record->ranking = 0;
                $DB->update_record('block_bcgt_target_grades', $record);
            }
        }
    }
    
    //somehow some colleges have their data wrong. Lets reload after recreating the whole 
    //process using a new csv import
    if($oldversion < 2014030601)
    {
        global $CFG;
        $breakdown = new Breakdown(-1, null);
        $breakdown->import_csv($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/data/Alevelbreakdowns.csv');
        
        $targetGrade = new TargetGrade(-1, null);
        $targetGrade->import_csv($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/data/Alevelgrades.csv');
    }
}
