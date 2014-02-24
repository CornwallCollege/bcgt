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
class Subject {
    //put your code here
    protected $id;
    protected $subject;
    
    public function Subject($id, $params = null)
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
                $this->load_subject($id);
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
    
    public function get_subject()
    {
        return $this->subject;
    }
    
    /**
     * Saves the Target Grade into the database. 
     * It either updates or inserts. 
     */
    public function save()
    {
        if($this->id != -1)
        {
            $this->update_subject();
        }
        else
        {
            $this->insert_subject();
        }
    }
    
    private function update_subject()
    {
        global $DB;
        $params = $this->get_params();
        $params->id = $this->id;
        $DB->update_record('block_bcgt_subject', $params);
    }
    
    private function insert_subject()
    {
        global $DB;
        $params = $this->get_params();
        $this->id = $DB->insert_record('block_bcgt_subject', $params);
    }
    
    /**
     * Deletes the target grade using the target grade id passed in (from the database.)
     * @global type $DB
     * @param type $targetGradeID
     */
    public static function delete_subject($subjectID)
    {
        global $DB;
        $DB->delete_records('block_bcgt_subject', array('id'=>$subjectID));
    }
    
    public static function retrieve_csv($subject, $insertNew)
    {
        $subject = Subject::retrieve_subject(-1, $subject);
        if($subject)
        {
            return $subject;
        }
        if($subject)
        {
            $params = new stdClass();
            $params->subject = $subject;
            $subject = new Subject(-1, $params);
            $subject->save();
            return $subject;
        }
        return false;
    }
    
    public static function retrieve_subject($id = -1, $subject = '')
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_subject} WHERE"; 
        $params = array();
        if($id != -1)
        {
            $sql .= " id = ?";
            $params[] = $id;
        }
        elseif($subject != '')
        {
            $sql  .= ' subject = ?';
            $params[] = $subject;
        }
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return new Subject($record->id, $record);
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
        $params->subject = $this->subject;
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
        $this->id = $DB->insert_record('subject', $params);
    }
    
    private function update_entry_qual()
    {
        global $DB;
        $params = $this->get_params();
        $params->id = $this->id;
        $DB->update_record('subject', $params);
    }
    
    /**
     * Gets the params from the object passed in and puts them onto 
     * the target grade objectl. 
     * @param type $params
     */
    private function extract_params($params)
    {                
        $this->subject = $params->subject;
    }
    
    /**
     * gets the target grade from the database and loads onto the obj
     * @global type $DB
     * @param type $id
     */
    private function load_subject($id)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_subject} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($id));
        if($record)
        {
            $this->extract_params($record);
        }
    }
}

?>
