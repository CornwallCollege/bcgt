<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EntryQual
 *
 * @author mchaney
 */
class EntryGrade {
    //put your code here
    protected $bcgtpriorqualid;
    protected $grade;
    protected $points;
    protected $weighting;
    
    protected $usersubject;
    protected $userexamdate;
    
    public function EntryGrade($id, $params = null)
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
                $this->load_entry_qual($id);
            }
        }
        elseif($params)
        {
            $this->extract_params($params);
        }
    }
    
    public function get_id()
    {
        return $this->id;
    }
    
    public function get_user_exam_date()
    {
        return $this->userexamdate;
    }
    
    public function get_user_subject()
    {
        return $this->usersubject;
    }
    
    public function get_points()
    {
        return $this->points;
    }
    
    public function get_weighting()
    {
        return $this->weighting;
    }
    
    public function get_grade()
    {
        return $this->grade;
    }
    
    public function add_user_info($examDate, $bcgtSubjectID, $subject)
    {
        $params = new stdClass();
        $params->subject = $subject;
        $subjectObj = new Subject($bcgtSubjectID, $params);
        $this->usersubject = $subjectObj;
        $this->userexamdate = $examDate;
    }
    
    /**
     * Saves the Target Grade into the database. 
     * It either updates or inserts. 
     */
    public function save()
    {
        if($this->id != -1)
        {
            $this->update_entry_qual();
        }
        else
        {
            $this->insert_entry_qual();
        }
    }
    
    
    
    /**
     * Deletes the target grade using the target grade id passed in (from the database.)
     * @global type $DB
     * @param type $targetGradeID
     */
    public static function delete_entry_qual_grade($entryQualGradeID)
    {
        global $DB;
        $DB->execute("DELETE FROM {block_bcgt_user_prlearn} uplearn 
            WHERE uplearn.bcgtpriorqualgradesid = ?", array($entryQualGradeID));
        $DB->delete_records('block_bcgt_prior_qual_grades', array('id'=>$entryQualGradeID));
    }
    
    public static function retrieve_csv($qualID, $grade, $insertNew)
    {
        if(!$grade || $grade == '')
        {
            $grade = 'Achieved';
        }
        if($qualID)
        {
            $entryGrade = EntryGrade::retrieve_entry_grade(-1, $qualID, $grade);
            if($entryGrade)
            {
                return $entryGrade;
            }
            if($insertNew)
            {
                $params = new stdClass();
                $params->bcgtpriorqualid = $qualID;
                $params->grade = $grade;
                $entryGrade = new EntryGrade(-1, $params);
                $entryGrade->save();
                return $entryGrade;
            }
        }
        
        return false;
    }
    
    public static function retrieve_entry_grade($id = -1, $qualID= -1, $grade = '')
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_prior_qual_grades} WHERE"; 
        $params = array();
        if($id != -1)
        {
            $sql .= " id = ?";
            $params[] = $id;
        }
        elseif($qualID != -1 && $grade != '')
        {
            $sql  .= ' bcgtpriorqualid = ? AND grade = ?';
            $params[] = $qualID;
            $params[] = $grade;
        }
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return new EntryGrade($record->id, $record);
        }
        return false;
        
    }
    
    /**
     * Gets the params from the object and passes it bak as a new object. 
     * @return \stdClass
     */
    private function get_params()
    {
        $params = new stdClass();
        $params->bcgtpriorqualid = $this->bcgtpriorqualid;
        $params->grade = $this->grade;
        $params->points = 0;
        if(isset($this->points))
        {
            $params->points = $this->points;
        }
        $params->weighting = 1;
        if(isset($this->weighting))
        {
            $params->weighting = $this->weighting;
        } 
        return $params;
    }
    
    /**
     * Inserts the target grade into the database. 
     * @global type $DB
     */
    private function insert_entry_qual()
    {
        global $DB;
        $params = $this->get_params();
        $this->id = $DB->insert_record('block_bcgt_prior_qual_grades', $params);
    }
    
    private function update_entry_qual()
    {
        global $DB;
        $params = $this->get_params();
        $params->id = $this->id;
        $DB->update_record('block_bcgt_prior_qual_grades', $params);
    }
    
    /**
     * Gets the params from the object passed in and puts them onto 
     * the target grade objectl. 
     * @param type $params
     */
    private function extract_params($params)
    {        
        $this->bcgtpriorqualid = $params->bcgtpriorqualid;
        $this->grade = $params->grade;
        $this->points = 0;
        if(isset($params->points))
        {
            $this->points = $params->points;
        }
        $this->weighting = 1;
        if(isset($params->weighting))
        {
            $this->weighting = $params->weighting;
        } 
    }
    
    /**
     * gets the target grade from the database and loads onto the obj
     * @global type $DB
     * @param type $id
     */
    private function load_entry_qual($id)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_prior_qual_grades} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($id));
        if($record)
        {
            $this->extract_params($record);
        }
    }
}

?>
