<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("../../../config.php");
$run = optional_param('run', 'run', PARAM_TEXT);
if($run == 'run')
{
    remove_duplicate_user_units();
    remove_duplicate_user_criteria();
}

function remove_duplicate_user_units()
{
    set_time_limit(0);
    /*
     * Full SQL is:
     * SELECT userunit.* FROM mdl_block_bcgt_user_unit userunit
        JOIN (
        SELECT userid, bcgtunitid, count(*) FROM `mdl_block_bcgt_user_unit` GROUP BY userid, bcgtunitid HAVING count(*) > 1 
        )  duplicates 
        ON duplicates.userid = userunit.userid AND duplicates.bcgtunitid = userunit.bcgtunitid
        ORDER BY userunit.userid, userunit.bcgtunitid, userunit.bcgtqualificationid";
     */
    echo "STARTING count before = ".get_count_distinct_unit()."<br />";
    global $DB;
    $sql = "
        SELECT id, userid, bcgtunitid, count(*) FROM {block_bcgt_user_unit} 
        GROUP BY userid, bcgtunitid HAVING count(*) > 1 
        ORDER BY userid, bcgtunitid, bcgtqualificationid";
    $userUnits = $DB->get_records_sql($sql, array());
    if($userUnits)
    {
        echo "FOUND ".count($userUnits)."Duplicates<br />";
        //this brings back the user and thir unit.
        foreach($userUnits AS $userUnit)
        {
            echo "Doing User 1<br />";
            //lets go find each instance of this:
            $sql = "SELECT * FROM {block_bcgt_user_unit} WHERE
                userid = ? AND bcgtunitid = ?";
            $records = $DB->get_records_sql($sql, array($userUnit->userid, $userUnit->bcgtunitid));
            if($records)
            {
                $count = 0;
                $qualsWithData = array();
                $noQualsWithData = array();
                $qualsWithNoData = array();
                foreach($records AS $record)
                {
                    //is this isntance of the unit, which is on
                    //a qualification. Is this user still on this
                    //qual?
                    //is this unit still on this qual?
                    $sql = "SELECT * FROM {block_bcgt_user_qual} userqual 
                        WHERE userqual.bcgtqualificationid = ? AND userqual.userid = ?";
                    if($DB->get_records_sql($sql, array($record->bcgtqualificationid, $record->userid)))
                    {
                        //then the user IS on this qualification.
                        $count++;
                        //do we have any data?
                        if(get_user_criteria_data($record->userid, $record->bcgtqualificationid, $record->bcgtunitid))
                        {
                            //we do have data
                            $qualsWithData[$record->id] = $record;
                        }
                        else
                        {
                            $qualsWithNoData[$record->id] = $record;
                        }
                    }
                    else 
                    {
                        //then the user IS not on this qualification?
                        //does it have any criteria data?
                        if((get_user_criteria_data($record->userid, $record->bcgtqualificationid, $record->bcgtunitid)))
                        {
                            //then we do have user criteria data
                            $count++;
                            $noQualsWithData[$record->id] = $record;
                        }
                        else
                        {
                            echo "DELETING user Unit record 1 step <br />";
                            //we dont have user criteria data
                            //then we can delete it. 
                            //we will keep the other one.
                            $DB->delete_records('block_bcgt_user_unit', array('id'=>$record->id));
                        }
                    }
                }
                
                if($count > 1)
                {
                    //then we have more than 1 to keep
                    //we have three arrays.
                    //we have the records where the user IS on the qual
                    //we have the one where the user is NOT on the qual
                    if(count($records) == 2)
                    {
                        //then we only have two to compare
                        if(count($noQualsWithData) == 1 && $qualsWithNoData == 1)
                        {
                            //then we know that the qual they arent on has data
                            //and the qual they are on does
                            //so we can just pick the unitid
                            echo "DELETING user unit record 2 step<br />";
                            $DB->delete_records('block_bcgt_user_unit', array("id"=>end($qualsWithNoData)->id));
                        }
                        elseif(count($noQualsWithData) == 2 || count($qualsWithData) == 2)
                        {
                            //then we compare and merge/delete erroneous/extra wrong data
                            compare_merge_and_truncate_user_criteria_data($userUnit->bcgtunitid, $userUnit->userid);
                            clear_down_after_criteria_truncate(-1, 
                                    $userUnit->bcgtunitid, $userUnit->userid, 
                                    -1);
                        }
                        elseif(count($qualsWithNoData) == 2)
                        {
                            //then neither have data
                            //so we dont really care
                            //so lets just pick one. 
                            echo "DELETING user unit record 3 step<br />";
                            $DB->delete_records('block_bcgt_user_unit', array("id"=>end($qualsWithNoData)->id));
                        }
                        else
                        {
                            compare_merge_and_truncate_user_criteria_data($userUnit->bcgtunitid, $userUnit->userid);
                            clear_down_after_criteria_truncate(-1, 
                                    $userUnit->bcgtunitid, $userUnit->userid, 
                                    -1);
                        }
                    }
                    else
                    {
                        //we have more permutations to play with. 
                        if(count($qualsWithNoData) == $count)
                        {
                            //then all of the 'found' quals have no data
                            //so we dont really care
                            //just randomly delete one of the instances.
                            //but there could be more than one, so its delete where NOT one of
                            //the instances
                            echo "DELETING user unit record 4 step<br />";
                            $DB->execute('DELETE FROM {block_bcgt_user_unit} WHERE id != ? AND userid = ? AND bcgtunitid = ? ', array(end($qualsWithNoData)->id, $userUnit->userid, $userUnit->bcgtunitid));
                        }
                        else
                        {
                            //some do, some dont
                            //so we want to drop make it so we only have one 
                            //unit in the user_unit table
                            //and one set of data in the user_criteria table:
                            compare_merge_and_truncate_user_criteria_data($userUnit->bcgtunitid, $userUnit->userid);
                            clear_down_after_criteria_truncate(-1, 
                                    $userUnit->bcgtunitid, $userUnit->userid, 
                                    -1);
                        }
                    }
                }
            }
        }
    }
    else      
    {
        //THERE ARE NO DUPLICATE UNITS
        echo "NO DUPLICATE UNITS FOUND";
    }
    echo "Done count now = ".get_count_distinct_unit()."<br />";
}

function remove_duplicate_user_criteria()
{
    set_time_limit(0);
    /*
     * Full SQL is:
     * SELECT userunit.* FROM mdl_block_bcgt_user_unit userunit
        JOIN (
        SELECT userid, bcgtunitid, count(*) FROM `mdl_block_bcgt_user_unit` GROUP BY userid, bcgtunitid HAVING count(*) > 1 
        )  duplicates 
        ON duplicates.userid = userunit.userid AND duplicates.bcgtunitid = userunit.bcgtunitid
        ORDER BY userunit.userid, userunit.bcgtunitid, userunit.bcgtqualificationid";
     */
    echo "STARTING CRITERIA<br />";
    echo "Count of distinct user criteria before:".get_count_distinct_criteria();
    global $DB;
    $sql = "
        SELECT id, userid, bcgtcriteriaid, count(*) 
        FROM {block_bcgt_user_criteria} GROUP BY userid, bcgtcriteriaid 
        HAVING count(*) > 1 
        ORDER BY userid, bcgtcriteriaid, bcgtqualificationid";
    $userCriterias = $DB->get_records_sql($sql, array());
    if($userCriterias)
    {
        foreach($userCriterias AS $userCriteria)
        {
            //get all of the instances of them:
            $sql = "SELECT * FROM {block_bcgt_user_criteria} WHERE userid = ? AND bcgtcriteriaid = ? 
                ORDER BY dateupdated DESC, dateset DESC";
            $records = $DB->get_records_sql($sql, array($userCriteria->userid, $userCriteria->bcgtcriteriaid));
            if($records)
            {
                //the last is the one we want to keep
                $record = end($records);
                $idToKeep = $record->id;
                //now we get rid of the rest. 
                //DELETING the previous records
                $DB->execute("DELETE FROM {block_bcgt_user_criteria} 
                    WHERE userid = ? AND bcgtcriteriaid = ? AND id != ? ", array($userCriteria->userid, $userCriteria->bcgtcriteriaid, $idToKeep));
            }
        }
    }
    echo "DONE CRITERIA count now = ".get_count_distinct_criteria();
}

function get_count_distinct_criteria()
{
    global $DB;
    $sql = "SELECT * FROM {block_bcgt_user_criteria} GROUP BY bcgtcriteriaid, userid";
    $count = $DB->get_records_sql($sql, array());
    return count($count);
}

function get_count_distinct_unit()
{
    global $DB;
    $sql = "SELECT * FROM {block_bcgt_user_unit} GROUP BY bcgtunitid, userid";
    $count = $DB->get_records_sql($sql, array());
    return count($count);
}

function clear_down_after_criteria_truncate($userUnitIDKeep, $unitID, $userID, $qualID)
{
    global $DB, $CFG;
    echo "DELETING USER UNIT record step 5";
    if($userUnitIDKeep == -1 || $qualID == -1)
    {
        //then it doesnt matter which one we keep:
        $sql = "SELECT * FROM {block_bcgt_user_unit} WHERE userid = ? AND bcgtunitid = ?";
        $toKeep = $DB->get_records_sql($sql, array($userID, $unitID));
        $qualID = end($toKeep)->bcgtqualificationid;
        $userUnitIDKeep = end($toKeep)->id;
    }
    $DB->execute('DELETE FROM {block_bcgt_user_unit} WHERE id != ? AND userid = ? AND bcgtunitid = ?', array($userUnitIDKeep, $userID, $unitID));                        
    //we need to recalculate the users unit awards
    require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Qualification.class.php');
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELALL;
    $unit = Unit::get_unit_class_id($unitID, $loadParams);
    $unit->load_student_information($userID, $qualID, $loadParams);
    echo "Recalculating Unit Awards";
    $unit->calculate_unit_award($qualID);
}

function get_user_criteria_data($userID, $qualID, $unitID)
{
    global $DB;
    $sql = "SELECT usercrit.* FROM {block_bcgt_user_criteria} usercrit
            JOIN {block_bcgt_criteria} crit ON crit.id = usercrit.bcgtcriteriaid 
            WHERE usercrit.userid = ? AND usercrit.bcgtqualificationid = ? AND crit.bcgtunitid = ?";
    return $DB->get_records_sql($sql, array($userID, $qualID, $unitID));
}

function compare_merge_and_truncate_user_criteria_data($unitID, $userID)
{
    global $DB;
    //first get all of the criteria on this unit
    $sql = "SELECT * FROM {block_bcgt_criteria} WHERE bcgtunitid = ?";
    $criterias = $DB->get_records_sql($sql, array($unitID));
    if($criterias)
    {
        foreach($criterias AS $criteria)
        {
            //we now find all of the instances where the user has 
            //a criteria record for this record. 
            //we order by date_updated, then date_set
            //we loop over each
            $sql = "SELECT * FROM {block_bcgt_user_criteria} WHERE bcgtcriteriaid = ? 
                AND userid = ? ORDER BY dateupdated DESC, dateset DESC";
            //we need to know the latest updated date and the latest date set
            //which ever record corresponds to the latest one of these
            //keep, delete the rest.
            $latestUpdated = array();
            $latestSet = array();
            $userCriterias = $DB->get_records_sql($sql, array($criteria->id, $userID));
            if($userCriterias)
            {
                foreach($userCriterias AS $userCriteria)
                {
                    $currentUpdated = end($latestUpdated);
                    if(!$currentUpdated)
                    {
                        $latestUpdated[$userCriteria->id] = $userCriteria->dateupdated;
                    }
                    else
                    {
                        if($currentUpdated < $userCriteria->dateupdated)
                        {
                            //jusy reset the array: Easiest way of doing it
                            $latestUpdated = array();
                            $latestUpdated[$userCriteria->id] = $userCriteria->dateupdated;
                        }
                    }
                    
                    $currentSet = end($latestSet);
                    if(!$currentSet)
                    {
                        $latestSet[$userCriteria->id] = $userCriteria->dateset;
                    }
                    else
                    {
                        if($currentSet < $userCriteria->dateset)
                        {
                            //just reset the array: Easiest way of doing it
                            $latestSet = array();
                            $latestSet[$userCriteria->id] = $userCriteria->dateset;
                        }
                    }
                }
                
                //now we compare the two arrays
                $latestDateSet = end($latestSet);
                $latestDateSetUserCritId = key($latestSet);
                
                $latestDateUpdated = end($latestUpdated);
                $latestDateUpdatedCritId = key($latestUpdated);
                
                if($latestDateUpdated >= $latestDateSet)
                {
                    $keepID = $latestDateUpdatedCritId;
                }
                else
                {
                    $keepID = $latestDateSetUserCritId;
                }
                echo "DELETING user criteria record MAJOR step<br />";
                //now we delete all that are not of this id
                $DB->execute("DELETE FROM {block_bcgt_user_criteria} WHERE userid = ? AND bcgtcriteriaid = ? AND id != ?", 
                        array($userID, $criteria->id, $keepID));
            }
        }
    }
    
    //once this has been done
    //we then want to delete the user unit record. 
    
    
}
?>
