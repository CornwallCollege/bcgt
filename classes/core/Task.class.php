<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author cwarwicker@bedford.ac.uk
 */

class Task {
    
    protected $ID;
    protected $name;
    protected $details;
    protected $unitID;
    protected $recordID;
    protected $studentID;
    protected $criteriaID;
    protected $qualID;
    protected $comments;
    //put your code here
    
    function __construct($taskID = -1, $params = null) {
       
       if($taskID > 0 && $params == null){
           $check = get_record_select("block_bcgt_task", array("id"=>$taskID));
           $this->ID = $taskID;
           $this->name = $check->name;
           $this->details = $check->details;
           $this->unitID = $check->bcgtunitid;
           
       }
       else
       {
           $this->ID = $taskID;
           if($params)
           {
               $this->name = $params->name;
               $this->details = $params->details;
           }
       }
       
   }
   
   //I Still cant decide if this is a 
   //a lazy way of doing it or not?
   //but it was how it was done by Conn in version 1
   function get($var){
       return $this->$var;
   }
   
   //I Still cant decide if this is a 
   //a lazy way of doing it or not?
   //but it was how it was done by Conn in version 1
   function set($var, $val)
   {
       $this->$var = $val;
   }
   
   function load_student($studentID, $criteriaID, $qualID)
   {
       global $DB;
       // See if student is taking task and if so, load award and such
       $check = $DB->get_record_select("block_bcgt_user_crit_task",
               array("userid"=>$studentID, "bcgtcriteriaid"=>$criteriaID,
                   "bcgttaskid"=>$this->ID, "bcgtqualificationid"=>$qualID));
       if(!isset($check->id)) return false;
       
       $this->recordID = $check->id;
       $this->studentID = $studentID;
       $this->criteriaID = $criteriaID;
       $this->qualID = $qualID;
       $this->comments = $check->comments;
       $this->awardID = $check->awardid;
                     
       return true;
       
   }
   
   function is_task_achieved()
   {
       global $DB;
       if(!isset($this->awardID) || !$this->awardID) return false;
       
       $check = $DB->get_record_select("block_bcgt_value", array("id"=>$this->awardID));
       
       if(!isset($check->id) || $check->criteriamet != "Yes") return false;
       
       return true;
   }
   
   function update_comments($comment)
   {
       global $DB;
       if($comment == " ") $comment = null;       
       
       $obj = new stdClass();
       $obj->id = $this->recordID;
       $obj->comments = $comment;
       $DB->update_record("block_bcgt_user_crit_task", $obj);
       
       $this->comments = $comment;
       
       $logValue = (!is_null($comment)) ? LOG_VALUE_GRADETRACKER_UPDATED_TASK_COMMENT : LOG_VALUE_GRADETRACKER_DELETED_TASK_COMMENT;
       logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_TASK, $logValue, $this->studentID, $this->qualID, $this->unitID, null, $this->recordID, $comment);
       
   }
   
   function save($unitID = null)
   {
       global $DB;
       // Insert new
       if($this->ID == -1){
           
           $obj = new stdClass();
           $obj->name = $this->name;
           $obj->details = $this->details;
           $obj->bcgtunitid = $unitID;
           $this->ID = $DB->insert_record("block_bcgt_task", $obj);
           
       }
       else
       {
           // Update
           $obj = new stdClass();
           $obj->id = $this->ID;
           $obj->name = $this->name;
           $obj->details = $this->details;
           if(!is_null($unitID)){
               $obj->bcgtunitid = $unitID;
           }
           $DB->update_record("block_bcgt_task", $obj);
       }
              
   }
   
   function delete()
   {
       global $DB;
       // Archive the task itself
       $this->archive();
       // Archive it's links to any practical criteria
       $this->archivePracticalCriteriaLinks();
       // Archive any user records on that link
       $this->archiveUserRecords();
       
       // Delete task
       $DB->delete_records_select("block_bcgt_task", array("id"=>$this->ID));
       
       // Delete practical criteria links
       $DB->delete_records_select("block_bcgt_task_criteria", array("trackingtaskid"=>$this->ID));
       
       // Delete user records
       $DB->delete_records_select("block_bcgt_user_crit_task", array("taskid"=>$this->ID));
       
   }
   
   private function archive()
   {
       global $DB;
       $obj = new stdClass();
       $obj->recordid = $this->ID;
       $obj->name = $this->name;
       $obj->details = $this->details;
       $obj->bcgtunitid = $this->unitID;
       $DB->insert_record("block_bcgt_task_history", $obj);
       
   }
   
   private function archivePracticalCriteriaLinks()
   {
       global $DB;
       $check = $DB->get_records_select("block_bcgt_task_criteria", array("trackingtaskid"=>$this->ID));
       foreach($check as $link)
       {
            $obj = new stdClass();
            $obj->recordid = $link->id;
            $obj->bcgttaskid = $link->bcgttaskid;
            $obj->bcgtcriteriaid = $link->bcgtcriteriaid;
            $DB->insert_record("block_bcgt_task_crit_his", $obj);
       }
       
   }
   
   private function archiveUserRecords()
   {
       global $DB;
       $DB->execute("INSERT INTO {block_bcgt_user_crit_tsk_his}
        (recordid, userid, bcgtqualificationid, bcgtcriteriaid, bcgttaskid, 
        bcgttypeawardid, updatedbyuserid, updatedtime, comments)
        SELECT * FROM {block_bcgt_user_crit_task} WHERE taskid = ?", array($this->ID));
   }
}

?>
