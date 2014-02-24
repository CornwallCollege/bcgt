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
        if($this->id != -1)
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
    
    public function get_breakdown_ucas_points($bcgtTargetQualID, $ucasPoints)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE ranking IN (SELECT MAX(ranking)
            FROM
            {block_bcgt_target_breakdown} breakown
            WHERE breakown.ucaspoints < ? AND bcgttargetqualid = ?) AND bcgttargetqualid = ? 
            ORDER BY ranking DESC";
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
        return $this->get_new_breakdown_by_ranking($newRanking);
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
    
    public static function retrieve_breakdown($id = -1, $targetQualID = -1, $breakdown = '')
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
        if(isset($params->ucaspoints))
        {
            $this->ucaspoints = $params->ucaspoints;
        }
        $this->targetgrade = $params->targetgrade;
        if(isset($params->unitsscorelower))
        {
            $params->unitscorelower = $params->unitsscorelower;
        }
        if(isset($params->unitsscoreupper))
        {
            $params->unitscoreupper = $params->unitsscoreupper;
        }
        if(isset($params->ranking))
        {
            $this->ranking = $params->ranking;
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
}

?>
