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
    
    public function TargetGrade($id = -1, $params = null)
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
    
    public function get_all_target_grades($targetQualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_grades} grades WHERE grades.bcgttargetqualid = ?";
        return $DB->get_records_sql($sql, array($targetQualID));
    }
    
    /**
     * Saves the Target Grade into the database. 
     * It either updates or inserts. 
     */
    public function save()
    {
        if($this->id && $this->id != -1)
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
    
    public function get_target_grade_ucas_points($bcgtTargetQualID, $ucasPoints, $method = "DOWN")
    {
        global $DB;
        
        $operand = '';
        $where = '';
        $order = '';
        switch($method)
        {
            case "UP":
                $operand = "MIN";
                $where = " grades.ucaspoints > ?";
                $order = " ORDER BY ranking ASC";
                break;
            case "DOWN":
                $operand = "MAX";
                $where .= " grades.ucaspoints < ?";
                $order = " ORDER BY ranking DESC";
                break;
        }   
        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE ranking IN (SELECT $operand(ranking)FROM
            {block_bcgt_target_grades} grades
            WHERE";
        $sql .= $where;
        $sql .= " AND bcgttargetqualid = ?) AND bcgttargetqualid = ?";
        $sql .= $order;
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
        $newGrade =  $this->get_new_target_by_ranking($newRanking);
        if(!$newGrade)
        {
            $newGrade = $this->get_new_target_by_ranking($this->ranking);
        }
        return $newGrade;
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
    
    public static function retrieve_target_grade($id = -1, $targetQualID = -1, $grade = '', $returnBlankOnFail = false, $ranking = -1)
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
        elseif($ranking != -1)
        {
            $sql .= ' bcgttargetqualid = ? AND ranking= ?';
            $params[] = $targetQualID;
            $params[] = $ranking;
        }
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return new TargetGrade($record->id, $record);
        }
        elseif($returnBlankOnFail)
        {
            return new TargetGrade(-1, null);
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
    
    public function set_params($params)
    {
        $this->extract_params($params);
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
        if(isset($params->ucaspoints) && $params->ucaspoints != 'NULL')
        {
            $this->ucaspoints = $params->ucaspoints;
        }
        else
        {
            $this->ucaspoints = 0;
        }
        if(isset($params->bcgttargetqualid) && $params->bcgttargetqualid != 'NULL')
        {
            $this->bcgttargetqualid = $params->bcgttargetqualid;
        }
        else
        {
            $this->bcgttargetqualid = NULL;
        }
        if(isset($params->upperscore) && $params->upperscore != 'NULL')
        {
            $this->upperscore = $params->upperscore;
        }
        else
        {
            $this->upperscore = NULL;
        }
        if(isset($params->lowerscore) && $params->lowerscore != 'NULL')
        {
            $this->lowerscore = $params->lowerscore;
        }
        else
        {
            $this->lowerscore = NULL;
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
    
    public function import_csv($csvFile)
    {
        //the csv is in the form:
        //Family|Level|Subtype|Grade|Ucas|upperscore|lowerscore|ranking
        
        //loop over each row. 
        //find targetqualid
        
        //if targetgrade exists, update
        
        //else insert new. 
                
        global $DB, $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        $count = 1;
        $CSV = fopen($csvFile, 'r');
        while(($grade = fgetcsv($CSV)) !== false) {
            if($count != 1)
            {
                $family = $grade[0];
                $level = $grade[1];
                $subtype = $grade[2];
                
                $targetQual = bcgt_get_target_qual($family, $level, $subtype);
                if($targetQual)
                {
                    $targetQualID = $targetQual->id;
                    
                    $params = new stdClass();
                    $params->grade = $grade[3];
                    if(isset($grade[4]) && $grade[4] != '')
                    {
                        $params->ucaspoints = $grade[4];
                    }
                    else
                    {
                        $params->ucaspoints = 0;
                    }
                    $params->upperscore = $grade[5];
                    $params->lowerscore = $grade[6];
                    $params->ranking = $grade[7];
                    $params->bcgttargetqualid = $targetQualID;
                    
                    //does target grade already exist?
                    //this either returns a blank target grade or one with the id set
                    $breakdownObj = TargetGrade::retrieve_target_grade(-1, $targetQualID, $grade[3], true);
                    //insert of update the target grade with the values from the csv
                    $breakdownObj->set_params($params);
                                        
                    //insert of update into database
                    $breakdownObj->save();
                }
            }
            $count++;
        }
        
    }
}

?>
