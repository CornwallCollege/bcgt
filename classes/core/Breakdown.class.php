<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Breakdown
 *
 * @author mchaney
 */
class Breakdown {
    //put your code here
    
    protected $id; 
    protected $targetgrade;
    protected $ucaspoints;
    protected $bcgttargetqualid; 
    protected $unitsscoreupper;
    protected $unitsscorelower;
    protected $entryscoreupper;
    protected $entryscorelower;
    protected $ranking;
    
    public function Breakdown($id, $params = null)
    {
        $this->id = $id;
        if($id != -1)
        {
            if($params)
            {
                $this->extract_params($params);
            }
            else 
            {
                $this->load_breakdown($id);
            }
        }
        elseif($params)
        {
            $this->extract_params($params);
        }
    }
    
    public function get_ucas_points()
    {
        return $this->ucaspoints;
    }
    
    public function get_id()
    {
        return $this->id;
    }
    
    public function get_target_grade()
    {
        return $this->targetgrade;
    }
    
    public function get_ranking()
    {
        return $this->ranking;
    }
    
    public function get_ucaspoints()
    {
        return $this->ucaspoints;
    }
    
    /**
     * Saves the breakdpown. Either inserts or updates. 
     */
    public function save()
    {
        if($this->id && $this->id != -1)
        {
            $this->update_breakdown();
        }
        else
        {
            $this->insert_breakdown();
        }
    }
    
    public function get_breakdown_average_score($bcgtTargetQualID, $averageGCSEScore)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? 
            AND ((entryscoreupper > ? AND entryscorelower <= ?) OR (? >= entryscoreupper AND entryscoreupper = 
            (SELECT max(entryscoreupper) FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ?)))";
        $record = $DB->get_record_sql($sql, array($bcgtTargetQualID, $averageGCSEScore, 
            $averageGCSEScore, $averageGCSEScore, $bcgtTargetQualID));
        if($record)
        {
            $record->bcgttargetbreakdownid = $record->id;
            $this->extract_params($record);
        }
    }
    
    //Method can be UP, DOWN, CLOSEST
    //They stand for: Find the record where we are:
    //UP: closest ucas points record that is higher than the ucaspoints passed in
    //DOWN: closest ucas points record that is lower than the ucaspoints passed in
    public function get_breakdown_ucas_points($bcgtTargetQualID, $ucasPoints, $method = "DOWN")
    {
        global $DB;
        $operand = '';
        $where = '';
        $order = '';
        switch($method)
        {
            case "UP":
                $operand = "MIN";
                $where = " breakown.ucaspoints > ?";
                $order = " ORDER BY ranking ASC";
                break;
            case "DOWN":
                $operand = "MAX";
                $where .= " breakown.ucaspoints < ?";
                $order = " ORDER BY ranking DESC";
                break;
        }
        
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE ranking IN (SELECT $operand(ranking)
            FROM
            {block_bcgt_target_breakdown} breakown WHERE ";
        $sql .= $where;
        $sql .= " AND bcgttargetqualid = ?) AND bcgttargetqualid = ?"; 
        $sql .= $order;   
        $record = $DB->get_record_sql($sql, array($ucasPoints, $bcgtTargetQualID, $bcgtTargetQualID));
        if($record)
        {
            $record->bcgttargetbreakdownid = $record->id;
            $this->extract_params($record);
        }
    }
    
    public function get_breakdown_asp($difference)
    {
        //so get the current ranking
        //add the difference to the current ranking
        //get the new breakdown
        $newRanking = $this->ranking + $difference;
        $newBreakdown = $this->get_new_breakdown_by_ranking($newRanking);
        if(!$newBreakdown)
        {
            $newBreakdown = $this->get_new_breakdown_by_ranking($this->ranking);
        }
        return $newBreakdown;
    }
    
    public function get_new_breakdown_by_ranking($newRanking)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE ranking = ? and bcgttargetqualid = ?";
        $params = array($newRanking, $this->bcgttargetqualid);
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return new Breakdown($record->id, $record);
        }
    }
    
    /**
     * Gets the params from the object and returns an object of them. 
     * @return \stdClass
     */
    private function get_params()
    {
        $params = new stdClass();
        $params->bcgttargetqualid = $this->bcgttargetqualid;
        $params->ucaspoints = $this->ucaspoints;
        $params->targetgrade = $this->targetgrade;
        $params->unitsscorelower = $this->unitsscorelower;
        $params->unitsscoreupper = $this->unitsscoreupper;
        $params->entryscoreupper = $this->entryscoreupper;
        $params->entryscorelower = $this->entryscorelower;
        $params->ranking = $this->ranking;
        return $params;
    }
    
    /**
     * Deletes the breakdown from the database using passed in ID. 
     * @global type $DB
     * @param type $breakdownID
     */
    public static function delete_breakdown($breakdownID)
    {
        global $DB;
        $DB->delete_records('block_bcgt_target_breakdown', array('id'=>$breakdownID));
    }
    
    public static function retrieve_breakdown($id = -1, $targetQualID = -1, $breakdown = '', $returnBlankOnFail = false)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE"; 
        $params = array();
        if($id != -1)
        {
            $sql .= " id = ?";
            $params[] = $id;
        }
        elseif($targetQualID != -1 && $breakdown != '')
        {
            $sql  .= ' bcgttargetqualid = ? AND targetgrade = ?';
            $params[] = $targetQualID;
            $params[] = $breakdown;
        }
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return new Breakdown($record->id, $record);
        }
        elseif($returnBlankOnFail)
        {
            return new Breakdown(-1, null);
        }   
        return false;
    }
    
    /**
     * Inserts breakdown into database
     * @global type $DB
     */
    private function insert_breakdown()
    {
        global $DB;
        $params = $this->get_params();
        $this->id = $DB->insert_record('block_bcgt_target_breakdown', $params);
    }
    
    /**
     * Updates breakdown in database
     * @global type $DB
     */
    private function update_breakdown()
    {
        global $DB;
        $params = $this->get_params();
        $params->id = $this->id;
        $DB->update_record('block_bcgt_target_breakdown', $params);
    }
    
    public function set_params($params)
    {
        $this->extract_params($params);
    }
    
    /**
     * Extracts the params from the object and puts it onto the obj. 
     * @param type $params
     */
    private function extract_params($params)
    {
        if(isset($params->id))
        {
            $this->id = $params->id;
        }
        $this->bcgttargetqualid = $params->bcgttargetqualid;
        if(isset($params->ucaspoints) && $params->ucaspoints != 'NULL')
        {
            $this->ucaspoints = $params->ucaspoints;
        }
        else
        {
            $this->ucaspoints = NULL;
        }
        $this->targetgrade = $params->targetgrade;
        if(isset($params->unitsscorelower) && $params->unitsscorelower != 'NULL')
        {
            $this->unitsscorelower = $params->unitsscorelower;
        }
        else
        {
            $this->unitsscorelower = NULL;
        }
        if(isset($params->unitsscoreupper) && $params->unitsscoreupper != 'NULL')
        {
            $this->unitsscoreupper = $params->unitsscoreupper;
        }
        else
        {
            $this->unitsscoreupper = NULL;
        }
        if(isset($params->entryscoreupper) && $params->entryscoreupper != 'NULL')
        {
            $this->entryscoreupper = $params->entryscoreupper;
        }
        else
        {
            $this->entryscoreupper = NULL;
        }
        if(isset($params->entryscorelower) && $params->entryscorelower != 'NULL')
        {
            $this->entryscorelower = $params->entryscorelower;
        }
        else
        {
            $this->entryscorelower = NULL;
        }
        if(isset($params->ranking) && $params->ranking != 'NULL')
        {
            $this->ranking = $params->ranking;
        }
        else
        {
            $this->ranking = NULL;
        }
    }
    
    /**
     * Gets breakdown from the database and loads it into this obj. 
     * @global type $DB
     * @param type $id
     */
    private function load_breakdown($id)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($id));
        if($record)
        {
            $this->extract_params($record);
        }
    }
    
    public function import_csv($csvFile)
    {
        //the csv is in the form:
        //Family|Level|Subtype|Grade|Ucas|Entryscoreupper|EntryScoreLower|Unitsscoreupper|unitsscorelower|ranking
        
        //loop over each row. 
        //find targetqualid
        
        //if targetgrade exists, update
        //else insert new. 
        global $DB, $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        $count = 1;
        $CSV = fopen($csvFile, 'r');
        while(($breakdown = fgetcsv($CSV)) !== false) {
            if($count != 1)
            {
                $family = $breakdown[0];
                $level = $breakdown[1];
                $subtype = $breakdown[2];
                
                $targetQual = bcgt_get_target_qual($family, $level, $subtype);
                if($targetQual)
                {
                    $targetQualID = $targetQual->id;
                    
                    $params = new stdClass();
                    $params->targetgrade = $breakdown[3];
                    $params->ucaspoints = $breakdown[4];
                    $params->entryscoreupper = $breakdown[5];
                    $params->entryscorelower = $breakdown[6];
                    $params->unitsscoreupper = $breakdown[7];
                    $params->unitsscorelower = $breakdown[8];
                    $params->ranking = $breakdown[9];
                    $params->bcgttargetqualid = $targetQualID;
                    
                    //does breakdown already exist?
                    //this either returns a blank breakdown or one with the id set
                    $breakdownObj = Breakdown::retrieve_breakdown(-1, $targetQualID, $breakdown[3], true);
                    //insert of update the breakdown with the values from the csv
                    $breakdownObj->set_params($params);
                    //insert of update into database
                    $breakdownObj->save();
                }
                else
                {
                    echo "Target Qual Not Found: $family $level $subtype<br />";
                }
            }
            $count++;
        }
    }
}

?>
