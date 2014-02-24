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
class EntryQual {
    //put your code here
    protected $name;
    protected $weighting;
    protected $quallevel;
    const GCSE = 'GCSE';
    const GCSENormal = 'GCSE';
    const GCSEShort = 'GCSE Short Course';
    const GCSEDouble = 'GCSE Double Award';
    
    protected $userGrades;
    
    public function EntryQual($id, $params = null)
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
    
    public function get_users_grades()
    {
        return $this->userGrades;
    }
    
    public function get_weighting()
    {
        return $this->weighting;
    }
    public function get_level()
    {
        return $this->level;
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
    
    public function add_user_grade(EntryGrade $grade)
    {
        if(!$this->userGrades)
        {
            $this->userGrades = array();
        }
        $this->userGrades[] = $grade;
    }
    
    /**
     * Deletes the target grade using the target grade id passed in (from the database.)
     * @global type $DB
     * @param type $targetGradeID
     */
    public static function delete_entry_qual($entryQualID)
    {
        global $DB;
        $DB->execute("DELETE FROM {block_bcgt_user_prlearn} uplearn 
            WHERE uplearn.bcgtpriorqualgradesid IN 
            (SELECT grades.id FROM {block_bcgt_prior_qual_grades} grades WHERE grades.bcgtpriorqualid = ?)", array($entryQualID));
        $DB->delete_records('block_bcgt_prior_qual_grades', array('bcgtpriorqualid'=>$entryQualID));
        $DB->delete_records('block_bcgt_prior_qual', array('id'=>$entryQualID));
    }
    
    /**
     * Qual (so GCSE etc)
     * @param type $qual
     * @param type $insert
     * @param type $level
     */
    public static function retrieve_csv($qual, $level, $insert)
    {
        $entryQual = EntryQual::retrieve_entry_qual(-1, $qual, $level);
        if($entryQual)
        {
            return $entryQual;
        }
        if($insert)
        {
            $params = new stdClass();
            $params->name = $qual;
            $params->quallevel = $level;
            $entryQual = new EntryQual(-1, $params);
            $entryQual->save();
            return $entryQual;
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
        $params->name = $this->name;
        $params->weighting = 1;
        if(isset($this->weighting))
        {
            $params->weighting = $this->weighting;
        }
        $params->quallevel = $this->quallevel;
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
        $this->id = $DB->insert_record('block_bcgt_prior_qual', $params);
    }
    
    private function update_entry_qual()
    {
        global $DB;
        $params = $this->get_params();
        $params->id = $this->id;
        $DB->update_record('block_bcgt_prior_qual', $params);
    }
    
    /**
     * Gets the params from the object passed in and puts them onto 
     * the target grade objectl. 
     * @param type $params
     */
    private function extract_params($params)
    {
        $this->name = $params->name;
        $this->weighting = 1;
        if(isset($params->weighting))
        {
            $this->weighting = $params->weighting;
        }
        $this->quallevel = '';
        if(isset($params->quallevel))
        {
            $this->quallevel = $params->quallevel;
        }
        
        
    }
    
    public static function retrieve_entry_qual($id = -1, $name = '', $level = '')
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_prior_qual} WHERE"; 
        $params = array();
        if($id != -1)
        {
            $sql .= " id = ?";
            $params[] = $id;
        }
        else
        {
            $sql  .= ' name = ? AND quallevel = ?';
            $params[] = $name;
            $params[] = $level;
        }
        $records = $DB->get_records_sql($sql, $params);
        if($records)
        {
            $record = reset($records);
            return new EntryQual($record->id, $record);
        }
        return false;
        
    }
    
    /**
     * gets the target grade from the database and loads onto the obj
     * @global type $DB
     * @param type $id
     */
    private function load_entry_qual($id = -1, $name = '')
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_prior_qual} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($id));
        if($record)
        {
            $this->extract_params($record);
        }
    }
}

?>
