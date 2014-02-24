<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TargetGrade
 *
 * @author mchaney
 */
class TargetGrade {
    //put your code here
    
    protected $grade;
    protected $ucaspoints;
    protected $bcgttargetqualid;
    protected $upperscore;
    protected $lowerscore;
    protected $ranking;
    
    public function TargetGrade($id, $params = null)
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
                $this->load_target_grade($id);
            }
        }
        elseif($params)
        {
            $this->extract_params($params);
        }
    }
    
    public function get_grade()
    {
        return $this->grade;
    }
    
    public function get_ranking()
    {
        return $this->ranking;
    }
    
    public function get_ucas_points()
    {
        return $this->ucaspoints;
    }
    
    public function get_id()
    {
        return $this->id;
    }
    
    /**
     * Saves the Target Grade into the database. 
     * It either updates or inserts. 
     */
    public function save()
    {
        if($this->id != -1)
        {
            $this->update_target_grade();
        }
        else
        {
            $this->insert_target_grade();
        }
    }
    
    public function get_target_grade_average_score($bcgtTargetQualID, $averageGCSEScore)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? 
            AND ((upperscore > ? AND lowerscore <= ?) OR (? >= upperscore AND upperscore = 
            (SELECT max(upperscore) FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ?)))";
        $record = $DB->get_record_sql($sql, array($bcgtTargetQualID, $averageGCSEScore, $averageGCSEScore, 
            $averageGCSEScore, $bcgtTargetQualID, $bcgtTargetQualID));
        if($record)
        {
            $record->bcgttargetgradesid = $record->id;
            $this->extract_params($record);
        }
    }
    
    public function get_target_grade_ucas_points($bcgtTargetQualID, $ucasPoints)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE ranking IN (SELECT MAX(ranking)FROM
            {block_bcgt_target_grades} grades
            WHERE grades.ucaspoints < ? AND bcgttargetqualid = ?) AND bcgttargetqualid = ? 
            ORDER BY ranking DESC";
        $record = $DB->get_record_sql($sql, array($ucasPoints, $bcgtTargetQualID, $bcgtTargetQualID));
        if($record)
        {
            $record->bcgttargetgradesid = $record->id;
            $this->extract_params($record);
        }
    }
    
    public function get_target_asp($difference)
    {
        //so get the current ranking
        //add the difference to the current ranking
        //get the new breakdown
        $newRanking = $this->ranking + $difference;
        return $this->get_new_target_by_ranking($newRanking);
    }
    
    public function get_new_target_by_ranking($newRanking)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE ranking = ? and bcgttargetqualid = ?";
        $params = array($newRanking, $this->bcgttargetqualid);
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return new TargetGrade($record->id, $record);
        }
    }
    
    /**
     * Deletes the target grade using the target grade id passed in (from the database.)
     * @global type $DB
     * @param type $targetGradeID
     */
    public static function delete_target_grade($targetGradeID)
    {
        global $DB;
        $DB->delete_records('block_bcgt_target_grades', array('id'=>$targetGradeID));
    }
    
    public static function retrieve_target_grade($id = -1, $targetQualID = -1, $grade = '')
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE"; 
        $params = array();
        if($id != -1)
        {
            $sql .= " id = ?";
            $params[] = $id;
        }
        elseif($targetQualID != -1 && $grade != '')
        {
            $sql  .= ' bcgttargetqualid = ? AND grade = ?';
            $params[] = $targetQualID;
            $params[] = $grade;
        }
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return new TargetGrade($record->id, $record);
        }
        return false;
    }
    
    public static function get_obj_from_grade($grade, $ranking, $targetQualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE grade = ? and bcgttargetqualid = ?";
        $params = array($grade, $targetQualID);
        if($ranking != -1)
        {
            $sql .= ' and ranking = ?';
            $params[] = $ranking;
        }
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return new TargetGrade($record->id, $record);
        }
    }
    
    /**
     * Gets the params from the object and passes it bak as a new object. 
     * @return \stdClass
     */
    private function get_params()
    {
        $params = new stdClass();
        $params->grade = $this->grade;
        $params->ucaspoints = $this->ucaspoints;
        $params->bcgttargetqualid = $this->bcgttargetqualid;
        $params->upperscore = $this->upperscore;
        $params->lowerscore = $this->lowerscore;
        $params->ranking = $this->ranking;
        return $params;
    }
    
    /**
     * Inserts the target grade into the database. 
     * @global type $DB
     */
    private function insert_target_grade()
    {
        global $DB;
        $params = $this->get_params();
        $this->id = $DB->insert_record('block_bcgt_target_grades', $params);
    }
    
    private function update_target_grade()
    {
        global $DB;
        $params = $this->get_params();
        $params->id = $this->id;
        $DB->update_record('block_bcgt_target_grades', $params);
    }
    
    /**
     * Gets the params from the object passed in and puts them onto 
     * the target grade objectl. 
     * @param type $params
     */
    private function extract_params($params)
    {
        if(isset($params->id))
        {
            $this->id = $params->id;
        }        
        $this->grade = $params->grade;
        $this->ucaspoints = $params->ucaspoints;
        $this->bcgttargetqualid = $params->bcgttargetqualid;
        $this->upperscore = $params->upperscore;
        $this->lowerscore = $params->lowerscore;
        $this->ranking = $params->ranking;
    }
    
    /**
     * gets the target grade from the database and loads onto the obj
     * @global type $DB
     * @param type $id
     */
    private function load_target_grade($id)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($id));
        if($record)
        {
            $this->extract_params($record);
        }
    }
}

?>
