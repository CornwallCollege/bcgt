<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

class Criteria {
    protected $id;
    protected $name;
    protected $details;
    protected $type;
    protected $awardID;
     
    //array of criteria objcts representing sub criteria
    protected $subCriteriaArray;
    protected $parentCriteriaID;
    
    //the id of the students criteria record in the db
    protected $studentCriteriaID;
    //this is an object
    protected $studentValue;
    protected $studentID;
    protected $qualID;
    protected $unitID;
    protected $comments;
    protected $dateSet;
    protected $dateUpdated;
    protected $dateSetUnix;
    protected $dateUpdatedUnix;
    protected $setByUserID;
    protected $updateByUserID;
    protected $rules;
    protected $userDefinedValue;
    protected $taskArray;
    protected $taskIDArray;
    protected $targetDate;
    protected $defaultTargetDate = null;
    protected $rangeIDArray;
    protected $rangeArray;
    protected $numberOfObservations;
    protected $studentGrade;
    protected $targetgrade;
    protected $targetgradeID;
    
    protected $assignmentID;
    protected $assignmentType;
    protected $userAssignmentValueID;
    protected $trackingAssignmentSelectionID;
    protected $grade;
    protected $bcgtTypeID;
    protected $bcgtFamilyID;
    
    protected $studentFlag;
    protected $awardDate;
    
    protected $displayname;
    
    public function Criteria($criteriaID = -1, $params = null, $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        if($criteriaID != -1 && $params == null)
        {
            //then we have been given the ID only and so we get it from the database.
            $this->id = $criteriaID;
            
            $criteria = Criteria::get_criteria($criteriaID);
            if($criteria)
            {
                $this->name = $criteria->name;
                $this->details = $criteria->details;
                $this->awardID = $criteria->bcgttypeawardid;
                $this->rules = $criteria->rules;
                $this->type = $criteria->type;
                $this->unitID = $criteria->bcgtunitid;
                $this->numberOfObservations = $criteria->numofobservations;
                $this->qualID = $criteria->bcgtqualificationid;
                $this->weighting = $criteria->weighting;
                $this->targetDate = $criteria->targetdate;
                $this->defaultTargetDate = $criteria->targetdate;
                $this->displayname = $criteria->displayname;
                $this->parentCriteriaID = $criteria->parentcriteriaid;
                if(isset($criteria->comments))
                {
                    $this->comments = $criteria->comments;
                }
            }    
        }
        else
        {
            $this->id = $criteriaID;
            if($params)
            {
                $this->name = $params->name;
                if(isset($params->details))
                {
                    $this->details = $params->details;
                }
                if(isset($params->displayname))
                {
                    $this->displayname = $params->displayname;
                }
                if(isset($params->awardID))
                {
                   $this->awardID = $params->awardID; 
                }
                if(isset($params->rules))
                {
                    $this->rules = $params->rules;
                }
                if(isset($params->type))
                {
                    $this->type = $params->type;
                }
                if(isset($params->tasks))
                {
                    $this->taskArray = $params->tasks;
                }
                if (isset($params->weighting)){
                    $this->weighting = $params->weighting;
                }
                if (isset($params->targetdate)){
                    $this->targetDate = $params->targetdate;
                    $this->defaultTargetDate = $params->targetdate;
                }
                if (isset($params->numofobservations)){
                    $this->numberOfObservations = $params->numofobservations;
                }
                
                
                $this->load_unit_id();
            }
        }
                
        $this->get_tracking_type();
                   
        if ($loadLevel && ( (isset($loadLevel->loadLevel) && $loadLevel->loadLevel >= Qualification::LOADLEVELSUBCRITERIA)) || (is_numeric($loadLevel) && $loadLevel >= Qualification::LOADLEVELSUBCRITERIA) ){
        
            $this->subCriteriaArray = $this->set_up_sub_criterias($criteriaID, $this->bcgtTypeID);
            $this->rangeArray = $this->set_up_ranges($criteriaID);
        
        }
        
        $this->parentCriteriaID = $this->get_parent_ID();
                
        
    }
    
    public function get_num_observations(){
        return $this->numberOfObservations;
    }
    
    public function set_award_date($date)
    {
        $this->awardDate = $date;
    }
    
    // This is an awful way to do this
    public function set_date()
    {
        if($this->dateSet && $this->dateSet != 1 && $this->dateSet != 0)
        {
            $this->dateUpdated = date('d M Y H:m:s');
            $this->dateUpdatedUnix = time();
        }
        else
        {
            $this->dateSet = date('d M Y H:m:s');
            $this->dateSetUnix = time();
        }
    }
    
    public function set_user($userID)
    {
        if(!$this->setByUserID || $this->setByUserID == '')
        {
            $this->setByUserID = $userID;
        }    
        else
        {
            $this->updateByUserID = $userID;    
        }
    }
    
    public function get_sub_criteria()
    {
        return $this->subCriteriaArray;
    }
        
    public function load_sub_criteria()
    {
        if (!$this->subCriteriaArray)
        {
            $this->subCriteriaArray = $this->set_up_sub_criterias($this->id, $this->bcgtTypeID);
        }
    }
    
    public function load_ranges(){
        if (!$this->rangeArray){
            $this->rangeArray = $this->set_up_ranges($this->id);
        }
    }
    
    /**
     * Add one criteria as a sub criteria
     * @param type $obj 
     */
    public function add_sub_criteria($obj)
    {
        $this->subCriteriaArray[] = $obj;
    }
    
    public function set_sub_criteria($subCriteriaArray)
    {
        $this->subCriteriaArray = $subCriteriaArray;
    }
    
    public function set_parent_criteria_ID($parentCriteriaID)
    {
         $this->parentCriteriaID    = $parentCriteriaID;
    }
    
    public function get_parent_criteria_ID()
    {
        return $this->parentCriteriaID;
    }
        
    public function get_qual_ID()
    {
        return $this->qualID;
    }

    public function get_user_defined_value()
    {
        return $this->userDefinedValue;
    }
    
    public function set_user_defined_value($userDefinedValue)
    {
        $this->userDefinedValue = $userDefinedValue;
    }

    public function get_target_date()
    {
        return (!is_null($this->targetDate) && $this->targetDate > 0) ? date('d-m-Y', $this->targetDate) : "";
    }

    public function get_target_date_unix()
    {
        return $this->targetDate;
    }

    public function set_target_date($date)
    {
        $this->targetDate = $date;
    }
    
    public function set_user_target_date($date)
    {
        $this->userTargetDate = $date;
    }
        
    public function get_grade()
    {
        return $this->grade;     
    }
    
    public function set_grade($grade)
    {
        $this->grade = $grade;
    }
    
    public function get_targetgrade()
    {
        return $this->targetgrade;     
    }
    
    public function get_targetgrade_id()
    {
        return $this->targetgradeID;     
    }
    
    public function set_targetgrade($targetGrade)
    {
        $this->targetgrade = $targetGrade;     
    }
    
    public function set_targetgrade_id($targetGradeID)
    {
        $this->targetgradeID = $targetGradeID;     
    }
    
    public function add_comments($comments)
    {
        $this->comments = $comments;
    }
    
    public function get_comments()
    {
        return $this->comments;
    }
    
    public function load_comments()
    {
        global $DB;

        $records = $DB->get_records("block_bcgt_user_criteria", array("userid" => $this->studentID, "bcgtcriteriaid" => $this->id, "bcgtqualificationid" => $this->qualID), "id, comments");
        $record = end($records);
        if ($record){
            $this->comments = $record->comments;
        }
        return $this->comments;
    }
    
    public function set_assignment_id($assignmentID)
    {
        $this->assignmentID = $assignmentID;
    }
    
    public function set_assignment_type($assignmentType)
    {
        $this->assignmentType = $assignmentType;
    }
    
    public function set_tracking_user_assignment_value_id($userAssignmentValueID)
    {
        $this->userAssignmentValueID = $userAssignmentValueID;
    }
    
    public function set_tracking_assignment_selection_id($assignmentSelectionID)
    {
        $this->trackingAssignmentSelectionID = $assignmentSelectionID;
    }
    
    public function get_qualID()
    {
        return $this->qualID;
    }
    
    public function set_qualID($qualID)
    {
        $this->qualID = $qualID;
    }
    
    public function get_details()
    {
        return $this->details;
    }
        
    public function set_details($details)
    {
        $this->details = $details;
    }
    
    public function get_id()
    {
        return $this->id;
    }
    
    public function get_name()
    {
        return $this->name;
    }
        
    public function set_name($name)
    {
        $this->name = $name;
    }

    public function get_type()
    {
        return $this->type;
    }
    
    public function get_unit_id()
    {
        return $this->unitID;
    }
    
    public function get_tasks()
    {
        return $this->taskArray;
    }

    public function get_task_ids()
    {
        return $this->taskIDArray;
    }

    public function get_range_ids()
    {
        return $this->rangeIDArray;
    }

    public function get_ranges()
    {
        return $this->rangeArray;
    }
    
    public function get_ranges_db(){
        global $DB;
        $records = $DB->get_records("block_bcgt_range_criteria", array("bcgtcriteriaid" => $this->id), null, "bcgtrangeid as id");
        $return = array();
        if ($records)
        {
            foreach($records as $record)
            {
                $return[] = new Range($record->id);
            }
        }
        return $return;
    }
    
    public function set_student_value($value)
    {
        $this->studentValue = $value;
    } 
    
    public function get_student_value()
    {
        return $this->studentValue;
    }
    
    public function get_date_updated()
    {
        return $this->dateUpdated;
    }
    
    public function get_date_updated_unix()
    {
        return $this->dateUpdatedUnix;
    }
    
    public function get_date_set()
    {
        return $this->dateSet;
    }
    
    public function get_date_set_unix()
    {
        return $this->dateSetUnix;
    }
    
    public function get_award_date($format = false)
    {
        if ($this->awardDate <= 0) return '';
        if ($format) return date($format, $this->awardDate);
        else return $this->awardDate;
    }
    
    public function get_update_by()
    {
        return $this->updatedByUserID;
    }
    
    public function get_set_by()
    {
        return $this->setByUserID;
    }
        
    public function set_date_set($date)
    {
        $this->dateSet = $date;
    }

    public function set_date_updated($date)
    {
        $this->dateUpdated = $date;
    }
    
    public function set_student_flag($flag)
    {
        $this->studentFlag = $flag;
    }
    
    public function get_student_flag()
    {
        return $this->studentFlag;
    }

    public function get_student_ID(){
        return $this->studentID;
    }
    
    public function set_display_name($displayName)
    {
        $this->displayname = $displayName;
    }
    
    public function get_display_name()
    {
        if ($this->displayname == '' || is_null($this->displayname)){
            return $this->name;
        }
        return $this->displayname;
    }
    
    public function get_tracking_type()
    {
        global $DB;
        // If already set, just return it
        if(isset($this->bcgtTypeID)) return $this->bcgtTypeID;
        // Get it by looking at unit
        $check = $DB->get_record_sql("SELECT u.bcgttypeid, t.bcgttypefamilyid
            FROM {block_bcgt_unit} u 
            INNER JOIN {block_bcgt_criteria} c ON u.id = c.bcgtunitid
            JOIN {block_bcgt_type} t ON t.id = u.bcgttypeid
            WHERE c.id = ?", array($this->id));
        if($check)
        {
            $this->bcgtTypeID = $check->bcgttypeid;
            $this->bcgtFamilyID = $check->bcgttypefamilyid;
            return $this->bcgtTypeID;
        }                         
        $this->bcgtTypeID = -1; 
        $this->bcgtFamilyID = -1;
        return -1;
    }
    
    public function get_parent_ID()
    {
        global $DB;
        $check = $DB->get_record_select("block_bcgt_criteria", "id = ?", 
                array($this->id), "parentcriteriaid");
        return (isset($check->parentcriteriaid)) ? $check->parentcriteriaid : null;
    }
    
    public function get_parent_name(){
        
        global $DB;
        
        if ($this->parentCriteriaID){
            
            $crit = $DB->get_record("block_bcgt_criteria", array("id" => $this->parentCriteriaID));
            if ($crit){
                return $crit->name;
            }
            
        }
        
    }
    
    public function add_range_link($id, $points)
    {
        $this->rangeIDArray[$id] = $points;
    }
    
    public function is_linked_to_range($rangeID)
    {
        if($this->rangeArray)
        {
            foreach($this->rangeArray as $range)
            {
                if($rangeID == $range->id) return true;
            }
        }
        return false;
    }

    /**
     * Get a distinct list of all the ranges linked to all sub criteria on this criteria
     */
    public function get_all_possible_ranges()
    {
        global $CFG;

        $records = array();
        
        // Loop through sub criteria
        if($this->subCriteriaArray)
        {
            foreach($this->subCriteriaArray as $subCriteria)
            {
                if (!$subCriteria->get_ranges())
                {
                    $subCriteria->load_ranges();
                }
                
                $ranges = $subCriteria->get_ranges();
                if($ranges)
                {
                    foreach($ranges as $range)
                    {
                        $records[$range->id] = $range;
                    }
                }                   
            }
        }


        return $records;

    }
    
    /**
     * 
     * @param type $qualID
     * @return type
     */
    public function get_possible_values_for_assignment_grading($qualID)
    {
        $qual = Qualification::get_qualification_class_id($qualID);
        $values = $qual->get_possible_values($qual->get_family_ID());
        $return = array(
            -1 => array(
                'value' => '',
                'met' => false
            )
        );
        if ($values)
        {
            foreach($values as $val)
            {
                $return[$val->id] = array(
                    'value' => $val->value,
                    'met' => ($val->specialval == 'A')
                );
            }
        }        
        return $return;
    }
    
    
    /**
     * This allows the deletion of a students comments that
     * have been placed on the criteria.
     * @param unknown_type $qualID
     * @return boolean
     */
    public function delete_students_comments()
    {
        global $DB;
        
        $this->comments = null;
        
        $obj = new stdClass();
        $obj->id = $this->studentCriteriaID;
        $obj->comments = null;
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, LOG_VALUE_GRADETRACKER_DELETED_CRIT_COMMENT, $this->studentID, $this->qualID, $this->unitID, null, $this->id);
        return $DB->update_record('block_bcgt_user_criteria', $obj); 
    }
    
    public function save_students_comments($qualID)
    {

        global $DB, $USER;
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, LOG_VALUE_GRADETRACKER_UPDATED_CRIT_COMMENT, $this->studentID, $this->qualID, $this->unitID, null, $this->id, $this->comments);

        $obj = new stdClass();

        $stuCrit = $this->does_student_criteria_exist($qualID);
        
        // Update record
        if($stuCrit)
        {
            $obj->id = $stuCrit->id;
            $obj->comments = $this->comments;
            return $DB->update_record('block_bcgt_user_criteria', $obj); 
        }

        // Insert record
        else
        {
            $obj->userid = $this->studentID;
            $obj->bcgtqualificationid = $this->qualID;
            $obj->bcgtcriteriaid = $this->id;
            $obj->comments = $this->comments;
            $obj->dateset = time();
            $obj->setbyuserid = $USER->id;
            return $DB->insert_record('block_bcgt_user_criteria', $obj); 
        }


    }
    
    /**
     * Loads the students information onto the criteria
     * This will load the studentsValue (i.e. achieved or not achieved)
     * as an object
     * sets the dates and the people who updated it.
     * @param unknown_type $studentID
     * @param unknown_type $qualID
     * @param unknown_type $unitID
     */
    public function load_student_information($studentID, $qualID, $unitID = -1, $loadSubCriteria = true)
    {
        global $DB;
        $this->clear_student_information();
        //retrieve the students value if it has been set     
        $this->studentID = $studentID;
        $this->qualID = $qualID;
        $studentCriteria = $this->get_students_value($studentID, $qualID, $unitID);
        $studentGrade = $this->get_students_grade($studentID, $qualID, $unitID); 
        $studentTargetGrade = $this->get_students_target_grade($studentID, $qualID, $unitID);
        if($studentCriteria)
        {         
            if($studentCriteria->bcgtvalueid && $studentCriteria->value)
            {
                //TODO THIS NEEDS TO BE CHECKED AGAINST A SET OF VALUES PASSED IN to all
                //criterias, LOADED BY THE UNIT
                //e,g. load all possible values (there will only be a set number
                //then, here, get the value from the array (that way we arent going off to the database
                //EVERY SINGLE TIME!)
                $value = new Value($studentCriteria->bcgtvalueid, null);
                $this->studentValue = $value;
            }    
                                                
            $this->comments = $studentCriteria->comments;
            $this->studentCriteriaID = $studentCriteria->usercritid;
            if($studentCriteria->dateset)
            {
                $this->dateSetUnix = $studentCriteria->dateset;
                $this->dateSet = date('d M Y', $studentCriteria->dateset);    
            }
            if($studentCriteria->dateupdated)
            {
                $this->dateUpdatedUnix = $studentCriteria->dateupdated;
                $this->dateUpdated = date('d M Y', $studentCriteria->dateupdated);
            }
            $this->setByUserID = $studentCriteria->setbyuserid;
            $this->updatedByUserId = $studentCriteria->updatedbyuserid;
            $this->userDefinedValue = htmlentities($studentCriteria->userdefinedvalue, ENT_QUOTES);
            
            $this->studentFlag = (isset($studentCriteria->flag)) ? $studentCriteria->flag : false;
            if(isset($studentCriteria->awarddate))
            {
                $this->awardDate = $studentCriteria->awarddate;
            }
            if(!is_null($studentCriteria->targetdate)){
                $this->targetDate = $studentCriteria->targetdate;
            }
            
        }
        if($studentGrade)
        {
            $grade = new Grade($studentGrade->id, $studentGrade->grade, $studentGrade->umspercentagelower, $studentGrade->umspercentagehigher);
            $this->grade = $grade;
        }
        if($studentTargetGrade)
        {
            $grade = new Grade($studentTargetGrade->id, $studentTargetGrade->grade, $studentTargetGrade->umspercentagelower, $studentTargetGrade->umspercentagehigher);
            $this->targetGradeObj = $grade;
            $this->targetgradeID = $studentTargetGrade->id;
            $this->targetgrade = $studentTargetGrade->grade;
        }
        if($loadSubCriteria && $subCriteria = $this->get_sub_criteria())
        {
            foreach($subCriteria AS $sub)
            {
                $sub->load_student_information($studentID, $qualID, $unitID);
            }    
        }
                
        // Student obj
        $this->student = $DB->get_record("user", array("id" => $this->studentID));
                
    }
    
    protected function clear_student_information()
    {
        $this->studentValue = null;
        $this->comments = null;
        $this->studentCriteriaID = null;
        $this->dateSet = null;
        $this->dateUpdated = null;
        $this->setByUserID = null;
        $this->updatedByUserId = null;
        $this->userDefinedValue = null;
        $this->targetDate = $this->defaultTargetDate; #This is not cleared because the default value would be overridden
        $this->grade = null;
        $this->targetGradeObj = null;
        $this->targetgradeID = null;
        $this->targetgrade = null;
        $this->awardDate = null;
        $this->studentFlag = null;
    }
    
    /**
     * Loops over the subcriteria and returns true if the
     * subcriteriaID exists. False otherwise. 
     * @param unknown_type $subCriteriaID
     */
    public function does_criteria_have_sub_criteria($subCriteriaID)
    {
        $subCriteria = $this->get_sub_criteria();
        if($subCriteria)
        {
            foreach($subCriteria AS $sub)
            {
                if($sub->get_id() == $subCriteriaID)
                {
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    /**
     * Gets the criteriamet value from the database and creates a new student value.
     * @global type $CFG
     * @global type $USER
     * @param type $qualID
     * @param type $subCriteria
     * @param type $typeID
     */
    public function set_criteria_to_met($qualID = -1, $subCriteria = false, $typeID = -1)
    {
        global $DB, $USER;
        $sql = "SELECT * FROM {block_bcgt_value} WHERE specialval = ? and bcgttypeid= ? ";
        $value = $DB->get_record_sql($sql, array('A', $typeID));
        if($value)
        {
            $valueObj = new Value($value->id, $value);
            $this->studentValue = $valueObj;
            //TODO 
            $this->set_user($USER->id);
            $this->set_date();
            $this->save_student($qualID, false);
        }
        if($subCriteria)
        {
            if($this->subCriteriaArray)
            {
                foreach($this->subCriteriaArray AS $sub)
                {
                    $sub->set_criteria_to_met($qualID, $subCriteria, $typeID);
                }    
            }
        } 
    }
    
    public function set_criteria_to_unknown($qualID = -1, $subCriteria = false, $typeID)
    {
        global $USER;
        global $DB, $USER;
        $sql = "SELECT * FROM {block_bcgt_value} WHERE shortvalue = ? and bcgttypeid= ? ";
        $value = $DB->get_record_sql($sql, array('N/A', $typeID));
        if($value)
        {
            $valueObj = new Value($value->id, $value);
            $this->studentValue = $valueObj;
            $this->set_user($USER->id);
            $this->set_date();
            $this->save_student($qualID, false);
        }
        
        
        if($subCriteria)
        {
            if($this->subCriteriaArray)
            {
                foreach($this->subCriteriaArray AS $sub)
                {
                    $sub->set_criteria_to_unknown($qualID, $subCriteria, $typeID);
                }
            }
        }
    }
    
    public function is_met(){
        
        if (!$this->studentValue) return false;
        
        return $this->studentValue->is_criteria_met_bool();
        
    }
    
    /**
     * This chcks if the criteria is already set to met
     * if it is then it sets it to unmet
     * ASSUMES THERE iS AN 'X' unmet criteria in the db
     * @param unknown_type $qualID
     * Returns true if the parent criteria gets altered
     *      */
    public function unmeet_criteria($qualID, $typeID)
    {
        if($studentValue = $this->studentValue)
        {
            if($studentValue->is_criteria_met() == 'Yes')
            {
                //then lets convert to NO;
                global $DB, $USER;
                $sql = "SELECT * FROM {block_bcgt_value} WHERE shortvalue = ? and bcgttypeid= ? ";
                $value = $DB->get_record_sql($sql, array('X', $typeID));
                if($value)
                {
                    $valueObj = new Value($value->id, $value);
                    $this->studentValue = $valueObj;
                    //TODO 
                    $this->set_user($USER->id);
                    $this->set_date();
                    $this->save_student($qualID, false);
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Get the parent criteria object based on the subcriteriaID
     * @param unknown_type $subCriteriaID
     * @return Criteria|number
     */
    public static function set_up_parent_criteria_from_sub_criteria($subCriteriaID)
    {
        global $DB;
        $sql = "SELECT crit.* FROM {block_bcgt_criteria} AS crit 
        JOIN {block_bcgt_criteria} AS subcrit ON subcrit.parentcriteriaid = crit.id 
        WHERE subcrit.id = ?";
        $criteriaRecord = $DB->get_record_sql($sql, array($subCriteriaID));
        if($criteriaRecord)
        {
            $params = new stdClass();
            $params->name = $criteriaRecord->name;
            $params->details = $criteriaRecord->details;
            $params->bcgttypeawardid = $criteriaRecord->bcgttypeawardid;
            return new Criteria($criteriaRecord->id, $params);    
        }
        return -1;
    }
    
    /**
	 * Returns parent or sub if the criteria is a parent or subcriteria.
	 * @param unknown_type $criteriaID
	 */
	public static function criteria_type($criteriaID, $name = false, $unitID = false)
	{
		global $DB;
        
        
        if ($criteriaID > 0){
            $sql = "SELECT * FROM {block_bcgt_criteria} WHERE id = ?";
            $params = array($criteriaID);
        } else {
            $sql = "SELECT * FROM {block_bcgt_criteria} WHERE name = ? AND bcgtunitid = ?";
            $params = array($name, $unitID);
        }
        
		$criteria = $DB->get_record_sql($sql, $params);
		if($criteria)
		{
			if($criteria->parentcriteriaid == null || $criteria->parentcriteriaid == 0)
			{
				return "Parent";
			}
			else
			{
				return "Sub";
			}
		}
		else
		{
			return -1;
		}
	}
    
    /**
     * 
     * @global type $DB
     * @param type $criteriaID
     * @param type $qualID
     * @param type $studentID
     * @return string or boolean
     * Builds the table of the comments that gets presented to the user when they
     * hover over the comments icon in the grid
     * This is used for the teacher in advanced editing mode
     */
    public static function build_comments_tooltip($criteriaID, $qualID, $studentID)
    {
        global $DB;
        $sql = "SELECT comments FROM {block_bcgt_user_criteria} 
            WHERE bcgtqualificationid = ? AND userid = ? AND bcgtcriteriaid = ?";
        $comments = $DB->get_record_sql($sql, array($qualID, $studentID, $criteriaID));
        if($comments)
        {
            return '<div id="cCTT_'.$criteriaID.'" class="cCTT">'.$comments->comments.'</div>';
        }
        return false;
    }
    
    /**
     * 
     * @global type $DB
     * @param type $criteriaID
     * @param type $qualID
     * @param type $studentID
     * @return string|boolean
     */
    public static function build_criteria_tooltip($criteriaID, $qualID, $studentID)
    {
        global $DB;
        $sql = "SELECT distinct(crit.id) AS id, usercrit.id as usercritid, crit.name AS criterianame, 
            unit.id AS unitid, unit.name AS unitname, crit.details AS criteriadetails, 
            usercrit.comments AS criteriacomments, value.value AS value, value.customvalue AS customvalue,
            u.firstname AS firstname,
            u.lastname AS lastname, 
            u.username AS username,usercrit.dateupdated AS dateupdated, usercrit.updatedbyuserid, usercrit.setbyuserid, 
            usercrit.dateset AS dateset, usercreated.firstname AS createdfirstname,
            usercreated.lastname AS createdlastname FROM {block_bcgt_criteria} crit 
            LEFT OUTER JOIN {block_bcgt_user_criteria} usercrit ON usercrit.bcgtcriteriaid = crit.id 
            AND usercrit.userid = ? AND usercrit.bcgtqualificationid = ? 
            JOIN {block_bcgt_unit} unit ON unit.id = crit.bcgtunitid 
            LEFT OUTER JOIN {block_bcgt_value} value ON value.id = usercrit.bcgtvalueid 
            LEFT OUTER JOIN {user} u ON u.id = usercrit.updatedbyuserid
            LEFT OUTER JOIN {user} usercreated ON usercreated.id = usercrit.setbyuserid 
            WHERE crit.id = ?";// AND usercrit.userid = ? "; crit.bcgtqualificationid = ? 
            //
            //var_dump($sql);
            //
        //, $studentID $qualID, 
        $details = $DB->get_record_sql($sql, array($studentID, $qualID, $criteriaID));
        if($details)
        {
            $student = $DB->get_record("user", array("id" => $studentID));
            $output = '';
            $output .= "<div id='stuValU".$details->unitid."C".$details->criterianame."' class='cTT'>";
            $output .= "<div class='c'><small>".fullname($student)." ({$student->username})</small></div>";
            $output .= "<div class='c'><b>{$details->unitname}</b> <br /> {$details->criterianame} <br><br></div>";
            $output .= "<table class='criteriaPopupDetailsTable'>";
                $output .= "<tr><th>Description</th></tr>";
                $output .= "<tr><td>{$details->criteriadetails}</td></tr>";
            $output .= "</table>";
            if($details->criteriacomments)
            {
                $comments = format_text($details->criteriacomments, FORMAT_PLAIN);
                $output .= "<br>";
                 $output .= "<table class='criteriaPopupDetailsTable'>";
                    $output .= "<tr><th>Tutor Comments</th></tr>";
                    $output .= "<tr><td>{$comments}</td></tr>";
                $output .= "</table>";
            }

            $valueType = "Value";
            $detailsValue = $details->value;
            if($details->customvalue != '')
            {
                $detailsValue = $details->customvalue;
            }
            
            if ($detailsValue == '') $detailsValue = 'N/A';
            
            $valueBit = "{$valueType}: {$detailsValue}<br>";

            if(!is_null($details->dateupdated)) $date = date('d/m/Y',$details->dateupdated);
            elseif(!is_null($details->dateset)) $date = date('d/m/Y',$details->dateset);
            else $date = 'N/A';
            
            if(!is_null($details->updatedbyuserid)) $user = $details->firstname. ' ' .$details->lastname;
            elseif(!is_null($details->setbyuserid)) $user = $details->createdfirstname. ' ' .$details->createdlastname;
            else $user = 'N/A';

            $valueBit .= "Date Set: {$date}<br>By: {$user}<br>";
            $output .= "<br>";
             $output .= "<table class='criteriaPopupDetailsTable'>";
                $output .= "<tr><th>Criteria {$valueType}</th></tr>";
                $output .= "<tr><td>{$valueBit}</td></tr>";
            $output .= "</table>";
            
            
            // Sub Criteria
            $subCriteria = $DB->get_records("block_bcgt_criteria", array("parentcriteriaid" => $criteriaID));
            if ($subCriteria)
            {
                
                $output .= "<br>";
                $output .= "<table class='criteriaPopupDetailsTable'>";
                    $output .= "<tr><th>Sub Criteria</th></tr>";
                    foreach($subCriteria as $subCriterion)
                    {
                        $userInfo = $DB->get_record_sql("SELECT uc.*, v.value
                                                        FROM {block_bcgt_user_criteria} uc
                                                        INNER JOIN {block_bcgt_value} v ON v.id = uc.bcgtvalueid
                                                        WHERE uc.bcgtcriteriaid = ? AND uc.userid = ?", array($subCriterion->id, $studentID));
                            
                        $output .= "<tr><td><b>{$subCriterion->name}</b> - {$subCriterion->details}<br>";
                            if ($userInfo)
                            {
                                $output .= $userInfo->userdefinedvalue;
                                $output .= "<br><br>";
                                $output .= "{$userInfo->value}";
                            }
                        $output .= "</td></tr>";
                    }
                $output .= "</table>";
                
            }
            
            $output .= "</div>";
            
            return $output;
        }
        return "No Details Found";        
    }
    
    public static function get_correct_criteria_class($typeID = -1, $criteriaID = -1, 
            $params = null, $loadLevel = Qualification::LOADLEVELSUBCRITERIA)
    {
        //Based on the type (its the unitType strictly speaking)
        //we go and get the ParentCriteria class of the type using the
        //database
        $criteriaClass = Criteria::get_plugin_class_type($typeID);
        if($criteriaClass)
        {
            return $criteriaClass::get_instance($criteriaID, $params, $loadLevel);
        }
    }  
    
    private static function get_plugin_class_type($typeID)
    {
        global $DB, $CFG;
        $sql = "SELECT type.*, family.classfolderlocation 
            FROM {block_bcgt_type} AS type
            JOIN {block_bcgt_type_family} AS family ON family.id = type.bcgttypefamilyid 
            WHERE type.id = ?";
        $class = $DB->get_record_sql($sql, array($typeID));
        if($class)
        {
            $folder = $CFG->dirroot.$class->classfolderlocation;
            $className = $class->type;
            $className = str_replace(' ','',$className);
            if (is_dir($folder)) {
                $file = $folder.'/'.$className.'Criteria.class.php';
                if(file_exists($file))
                {
                    include_once($file);
                    $class = $className.'Criteria';
                    if(class_exists($class))
                    {
                        //then we can load it and return it. 
                        return $class;
                    }
                }
            }
        }
        return false;
    }
    
    public function set_up_tasks()
    {
        global $DB;
        $check = $DB->get_records_select("block_bcgt_task_criteria", 
                'bcgtcriteriaid = ?', array($this->id), "", "bcgttaskid");
        $return = array();
        $ids = array();
        if($check)
        {
            foreach($check as $record){
                $task = new Task($record->bcgttaskid);
                $return[] = $task;
                $ids[] = $record->bcgttaskid;
            }
        }

        $this->taskArray = $return;
        $this->taskIDArray = $ids;
    }
    
    //This needs to take into account if there are
    //any sub criteria 
    public function insert_criteria($unitID)
    {
        $this->insert_criteria_details($unitID);
    }
    
    public function insert_criteria_on_qual($unitID, $qualID)
    {
        $this->insert_criteria_details($unitID, $qualID);
    }
    
    public function check_sub_criteria_removed()
    {
        global $DB;
        //needs to find all of the criteria
        //that were on this unit that are not anymore(if any)
        $originalSubCriteria = $this->get_sub_criteria_db($this->id);
        if($originalSubCriteria)
        {
            foreach($originalSubCriteria AS $originalSub)
            {
                if(!array_key_exists($originalSub->id, $this->subCriteriaArray))
                {
                    //then do a history
                    if($this->insert_sub_criteria_history($originalSub->id))
                    {
                        //delete the record. 
                        $DB->delete_records('block_bcgt_criteria', array('id'=>$originalSub->id));
                    }    
                }
            }
        }
    }
    
    /**
     * Updates the criteria in the database
     */
    public function update_criteria($unitID = -1)
    {        
        global $DB;
        $stdObj = new stdClass();
        $stdObj->id = $this->id;
        $stdObj->name = $this->name;
        $stdObj->details = addslashes($this->details);
        $stdObj->targetdate = $this->targetDate;
        $stdObj->bcgttypeawardid = $this->awardID;
        $stdObj->displayname = $this->displayname;
        if($unitID != -1)
        {
            $stdObj->bcgtqualificationid = null;
            $stdObj->bcgtunitid = $unitID;
        }
        $DB->update_record('block_bcgt_criteria', $stdObj);
        
        if($this->subCriteriaArray)
        {
            foreach($this->subCriteriaArray AS $subCriteria)
            {
                $subCriteria->set_parent_criteria_ID($this->id);
                //check if it exists
                if($subCriteria->exists())
                {
                    $subCriteria->update_criteria($unitID);
                }
                else
                {
                    $subCriteria->insert_criteria($unitID);
                }
                
            }
        }
        // Log
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, "updated criteria", null, null, $this->unitID, null, $this->id);
    }
    
    /**
     * Checks if the criteria exists in the database
     * This checks if the criteria exists, not the students criteria. 
     */
    public function exists()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_criteria} WHERE id = ?";
        return $DB->get_record_sql($sql, array($this->id));
    }
    
    public function existsByName($name, $unitID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_criteria} WHERE bcgtunitid = ? AND name = ?";
        return $DB->get_record_sql($sql, array($unitID, $name));
    }
    
    /**
     * Used to see if a subcriteria exists on this criteria
     * Either by id or by name
     * @param unknown_type $id
     * @param unknown_type $name
     */
    public function get_single_criteria($id = -1, $name)
    {
        if($this->subCriteriaArray)
        {            
            foreach($this->subCriteriaArray AS $criteria)
            {
                if((($id != -1) && ($criteria->get_id() == $id)) || 
                ($criteria->get_name() == $name))
                {
                    return $criteria;
                }
            }
        }
        return false;
    }
    
    /**
     * Creates a new value object and sets this criterias student value to
     * the new object
     * @param $valueID
     * returns false if there are no sub criteria. 
     */    
    public function update_students_value($valueID, $updateSub = true)
    {
        $value = new Value($valueID);
        $this->studentValue = $value;        

        
        //if this criteria has subcriteria then update all of those too. 
        if($this->subCriteriaArray && $updateSub)
        {
            foreach($this->subCriteriaArray AS $subCriteria)
            {
                $subCriteria->update_students_value($valueID);
            }
            return true;
        }
        return false;
    }
    
    /**
         * This is for when we want to update the value, but nothing else, so not the dateset, dateupdated, etc...
         * @param int $valueID 
         */
        public function update_students_value_manual($valueID)
        {
            
            global $DB, $USER;
            
            $value = new Value($valueID);
            $this->studentValue = $value;
            
            logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, LOG_VALUE_GRADETRACKER_UPDATED_CRIT_AWARD, $this->studentID, $this->qualID, $this->unitID, null, $this->id, $valueID);
            
            $stdObj = new stdClass();
            
            // If record already exists - upddate, otherwise insert
            if( $this->does_student_criteria_exist($this->qualID) ){

                // update
                $stdObj->id = $this->studentCriteriaID;
                if($this->studentValue)
                {
                    $stdObj->bcgtvalueid = $this->studentValue->get_id();
                }
                return $DB->update_record('block_bcgt_user_criteria', $stdObj);

            }
            else
            {

                // insert
                $stdObj->bcgtcriteriaid = $this->id;
                $stdObj->userid = $this->studentID;
                $stdObj->bcgtqualificationid = $this->qualID;
                $stdObj->setbyuserid = $USER->id;
                if($this->studentValue)
                {
                    $stdObj->bcgtvalueid = $this->studentValue->get_id();
                }
                return $DB->insert_record('block_bcgt_user_criteria', $stdObj); 
                
            }
            
            
            
        }
    
    /**
     * Isnerts or updates the students criteria
     * Checks if the criteria exists for this student
     * OIf it does then update
     * else insert
     * @param unknown_type $qualID
     */
    public function save_student($qualID, $saveSubCriteria = false)
    {
        $studentCritExists = $this->does_student_criteria_exist($qualID);
        if($studentCritExists)
        {
            $this->update_students_criteria($qualID, $studentCritExists->id);
        }
        else
        {
            $id = $this->insert_students_criteria($qualID);
            $this->studentCriteriaID = $id;
        }
        //lets do sub criteria
        //parent criteria needs to be done by the unit
        if($this->subCriteriaArray && $saveSubCriteria)
        {
            foreach($this->subCriteriaArray AS $sub)
            {
                $sub->save_student($qualID);
            }
        }
    }
    
    private function insert_criteria_details($unitID, $qualID = -1)
    {
        global $DB;
        $stdObj = new stdClass();
        $stdObj->name = $this->name;
        $stdObj->details = addslashes($this->details);
        $stdObj->bcgttypeawardid = $this->awardID;
        $stdObj->bcgtunitid = $unitID;
        $stdObj->targetdate = $this->targetDate;
        $stdObj->parentcriteriaid = $this->parentCriteriaID;
        $stdObj->displayname = $this->displayname;
        if($qualID != -1)
        {
            $stdObj->bcgtqualificationid = $qualID;
        }
        $id = $DB->insert_record('block_bcgt_criteria', $stdObj);
        if($this->subCriteriaArray)
        {
            foreach($this->subCriteriaArray AS $subCriteria)
            {
                $subCriteria->set_parent_criteria_ID($id);
                $subCriteria->insert_criteria($unitID);
            }
        }
        $this->id = $id;
                
        // Log
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, LOG_VALUE_GRADETRACKER_INSERTED_CRIT, null, null, $unitID, null, $this->id);        
    }
    
    /**
     * Updates the students criteria into tracking_user_criteria
     * This will set the times
     */
    private function update_students_criteria($qualID, $studentCriteriaID)
    {
        global $DB;
        
        $this->insert_user_criteria_history_by_id($studentCriteriaID);
        
        $stdObj = new stdClass();
        if(isset($this->studentCriteriaID) && $this->studentCriteriaID != -1)
        {
            $stdObj->id = $this->studentCriteriaID;
        }
        else
        {
           $stdObj->id =  $studentCriteriaID;
        }
        
        $stdObj->bcgtcriteriaid = $this->id;
        $stdObj->userid = $this->studentID;
        $stdObj->bcgtqualificationid = $qualID;
        if($this->studentValue)
        {
            $stdObj->bcgtvalueid = $this->studentValue->get_id();
        }
                
        if(!is_null($this->comments))
        {
            $this->comments = iconv('UTF-8', 'ASCII//TRANSLIT', $this->comments); 
            $stdObj->comments = trim($this->comments);
        }
        if($this->dateSet)
        {
            if (isset($this->dateSetUnix) && $this->dateSetUnix > 0){
                $stdObj->dateset = $this->dateSetUnix;
            } else {
                $stdObj->dateset = strtotime($this->dateSet); 
            }
        }
                        
        if (isset($this->awardDate))
        {
            if ($this->awardDate <= 0) $this->awardDate = null;
            $stdObj->awarddate = $this->awardDate;
        }
                
        if($this->dateUpdated)
        {
            if (isset($this->dateUpdatedUnix) && $this->dateUpdatedUnix > 0){
                $stdObj->dateupdated = $this->dateUpdatedUnix;
            } else {
                $stdObj->dateupdated = strtotime($this->dateUpdated); 
            }
        }
                
        if($this->setByUserID)
        {
            $stdObj->setbyuserid = $this->setByUserID;
        }
        if($this->updateByUserID)
        {
            $stdObj->updatedbyuserid = $this->updateByUserID;
        }
        if(strlen($this->userDefinedValue))
        {
                $stdObj->userdefinedvalue = trim($this->userDefinedValue);
        }
        else
        {
            $stdObj->userdefinedvalue = "";
        }
        if(isset($this->userTargetDate) && !is_null($this->userTargetDate)){
            $stdObj->targetdate = $this->userTargetDate;
        }
        if($this->targetgradeID)
        {
            $stdObj->bcgttargetgradesid = $this->targetgradeID;
        }
        $stdObj->flag = $this->studentFlag; 
                                
        if(isset($stdObj->bcgtvalueid) && $stdObj->bcgtvalueid > 0)
        {
            logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, LOG_VALUE_GRADETRACKER_UPDATED_CRIT_AWARD, $this->studentID, $this->qualID, $this->unitID, null, $this->id, $stdObj->bcgtvalueid);
        }
        
        return $DB->update_record('block_bcgt_user_criteria', $stdObj); 
    }
    
    /**
     * Inserts the students criteria into tracking_user_criteria
     */
    private function insert_students_criteria($qualID)
    {
        global $DB, $USER;
        $stdObj = new stdClass();
        $stdObj->bcgtcriteriaid = $this->id;
        $stdObj->userid = $this->studentID;
        $stdObj->bcgtqualificationid = $qualID;
        if($this->studentValue)
        {
            $stdObj->bcgtvalueid = $this->studentValue->get_id();
        }
        if($this->comments)
        {
            $this->comments = iconv('UTF-8', 'ASCII//TRANSLIT', $this->comments); 
            $stdObj->comments = $this->comments;
        }
        if(!$this->dateSet)
        {
            $this->dateSet = date('d M Y H:m:s');
        }
        $now = time();
        $stdObj->dateset = $now;
        $stdObj->dateupdated = $now;
        if(!$this->setByUserID)
        {
            $this->setByUserID = $USER->id;
        }
        $stdObj->setbyuserid = $this->setByUserID;
        if(strlen($this->userDefinedValue))
        {
                $stdObj->userdefinedvalue = trim($this->userDefinedValue);
        }
        else
        {
            $stdObj->userdefinedvalue = "";
        }
        
        if(isset($this->userTargetDate) && !is_null($this->userTargetDate)){
            $stdObj->targetdate = $this->userTargetDate;
        }
        if($this->targetgradeID)
        {
            $stdObj->bcgttargetgradesid = $this->targetgradeID;
        }
        if($this->studentFlag)
        {
            $stdObj->flag = $this->studentFlag; 
        }
        
        if (isset($this->awardDate)){
            $stdObj->awarddate = $this->awardDate;
        }
                
        $this->studentCriteriaID = $DB->insert_record('block_bcgt_user_criteria', $stdObj); 
        //do we need to set a flag?
        
        if(isset($stdObj->bcgtvalueid) && $stdObj->bcgtvalueid > 0)
        {
            logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, LOG_VALUE_GRADETRACKER_UPDATED_CRIT_AWARD, $this->studentID, $this->qualID, $this->unitID, null, $this->id, $stdObj->bcgtvalueid);
        }
        
        return $this->studentCriteriaID;
        
    }
    
    /**
     * Check if it has any chldren
     * @global type $CFG
     * @return type
     */
    public function has_children()
    {

        if (isset($this->hasChildren)){
            return $this->hasChildren;
        }
        
        global $DB;
        $record = $DB->get_record("block_bcgt_criteria", array("parentcriteriaid" => $this->id), "id", IGNORE_MULTIPLE);
        $this->hasChildren = ($record) ? true : false;
        return $this->hasChildren;

    }
    
    
    /*
     * Returns the criteria from the database based on the id that has been set
     * for this object. 
     * @return Found
     */
    private static function get_criteria($criteriaID)
    {
        global $DB;
        $sql = "SELECT c.*
                FROM {block_bcgt_criteria} c 
                WHERE c.id = ?";
        $result = $DB->get_record_sql($sql, array($criteriaID));

        // Also get the rules
        $rules = $DB->get_records_select("block_bcgt_rule_links", 
                "bcgtcriteriaid = ?", array($criteriaID));

        $rulesArray = array();
        if($rules)
        {
            foreach($rules as $rule)
            {
                $rulesArray[] = new Rule($rule->ruleid);
            }
        }

        $result->rules = $rulesArray;
                                                
        return $result;
    }
    
    /**
     * Load the sub criterias from the database and add them to the object
     */
    protected static function set_up_sub_criterias($criteriaID, $bcgtTypeID)
    {  
        //set the subcriteria up if we can
        $subCriterias = Criteria::get_sub_criteria_db($criteriaID); 
        if($subCriterias)
        {
            $subCriteriaArray = array();
            foreach($subCriterias AS $subCriteria)
            {
                //WARNING! THIS POTENTIALLY COULD CONSTANTLY CALL ITSELF
                //FOR EVERY LEVEL OF SUB CRITERIA
//                echo "creating new sub crit object:";
//                print_object($subCriteria);
                
                $newSubCrit = Criteria::get_correct_criteria_class($bcgtTypeID, $subCriteria->id, $subCriteria);
                if($newSubCrit)
                {
                    $newSubCrit->set_parent_criteria_ID($criteriaID);
                    $subCriteriaArray[$subCriteria->id] = $newSubCrit;
                }    
            }
            return $subCriteriaArray;
        }
    }
    
    /**
     * Gets all of the criteria records from the database for the sub criterias. 
     * @param unknown_type $id
     */
    protected static function get_sub_criteria_db($criteriaID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_criteria} WHERE 
            parentcriteriaid = ? ORDER BY id ASC";
        $records = $DB->get_records_sql($sql, array($criteriaID));
        
        if ($records)
        {
            foreach($records as &$record)
            {

                // Bespoke here because static method again
                $bespoke = $DB->get_record("block_bcgt_bespoke_criteria", array("bcgtcritid" => $record->id));
                if ($bespoke)
                {
                    $record->isbespoke = 1;
                    $record->grading = $bespoke->gradingstructureid;
                }

            }
        }
        
        return $records;
    }
    
    protected function load_unit_id()
    {
        global $DB;
        if($this->id > 0)
        {
            $get = $DB->get_record_select("block_bcgt_criteria", "id = ?", 
                    array($this->id), 'bcgtunitid');
            if($get) $this->unitID = $get->bcgtunitid;
        }
    }
    
    /**
    * Load up any ranges the criteria has
    * @param type $id
    * @param type $trackingTypeID
    */
    private static function set_up_ranges($criteriaID)
    {
        global $DB;
        // Select ranges
        $check = $DB->get_records_sql("SELECT r.id
            FROM {block_bcgt_range} r
            INNER JOIN {block_bcgt_range_criteria} rc ON rc.bcgtcriteriaid = ?
            WHERE r.id = rc.bcgtrangeid", array($criteriaID));
        $records = array();
        if($check)
        {
            foreach($check as $chk)
            {
                $records[] = new Range($chk->id);
            }
        }
        return $records;
    }
    
    public function get_max_points_of_range($rangeID)
    {
        global $DB;
        $check = $DB->get_record("block_bcgt_range_criteria", array("bcgtrangeid" => $rangeID, "bcgtcriteriaid" => $this->id));
        return ($check) ? $check->maxpoints : false;
    }
    
    /**
     * Inserts the criteria history
     * @param unknown_type $criteriaID
     */
    protected function insert_sub_criteria_history($subCriteriaID)
    {
        global $DB;
        $sql = "INSERT INTO {block_bcgt_criteria_his}
        (bcgtcriteriaid, name, details, type, bcgttypeawardid, bcgtunitid, parentcriteriaid, 
        targetdate, numofobservations, bcgtqualificationid, weighting, ordernum, displayname) 
        SELECT * FROM {block_bcgt_criteria} WHERE id = ?"; 
        return $DB->execute($sql, array($subCriteriaID));
    }
    
    public static function insert_user_criteria_history_by_id($id)
    {
        global $DB;
        $sql = "INSERT INTO {block_bcgt_user_criteria_his}
        (bcgtusercriteriaid, userid, bcgtqualificationid, bcgtcriteriaid, bcgtrangeid, bcgtvalueid, 
        setbyuserid, dateset, dateupdated, updatedbyuserid, comments, bcgtprojectid, userdefinedvalue, targetdate, 
        bcgttargetgradesid, bcgttargetbreakdownid, flag, awarddate) 
        SELECT * FROM {block_bcgt_user_criteria} WHERE id = ?"; 
        return $DB->execute($sql, array($id));
    }

    
    /**
     * Gets the students criteria values from the database
     * @param $studentID
     * @param $qualID
     * @param $unitID
     */
    private function get_students_value($studentID, $qualID, $unitID = -1)
    {
        //TODO change when we talk about projects
        global $DB;
        $sql = "SELECT usercriteria.*, value.shortvalue, value.value, value.specialval, value.bcgttypeid,
            value.ranking, value.customvalue, value.customshortvalue, value.context, value.bcgttargetqualid, 
            usercriteria.id as usercritid
             FROM {block_bcgt_user_criteria} AS usercriteria
        LEFT OUTER JOIN {block_bcgt_value} AS value ON value.id = usercriteria.bcgtvalueid 
        JOIN {block_bcgt_criteria} AS criteria ON criteria.id = usercriteria.bcgtcriteriaid
        WHERE usercriteria.bcgtcriteriaid = ? 
        AND usercriteria.bcgtqualificationid = ? 
        AND usercriteria.userid = ?";
        $params = array($this->id, $qualID, $studentID);
        if($unitID != -1)
        {
            $sql .= " AND criteria.bcgtunitid = ?";
            $params[] = $unitID;
        }
        $records = $DB->get_records_sql($sql, $params);
        return end($records);
    }
    
    private function get_students_grade($studentID, $qualID, $unitID = -1)
    {
        global $DB;
        $sql = "SELECT grade.* FROM {block_bcgt_target_grades} AS grade
        JOIN {block_bcgt_user_criteria} AS usercriteria ON usercriteria.bcgttargetgradesid = grade.id 
        JOIN {block_bcgt_criteria} AS crit ON crit.id = usercriteria.bcgtcriteriaid
        WHERE usercriteria.bcgtcriteriaid = ? 
            AND usercriteria.bcgtqualificationid = ? 
        AND usercriteria.userid = ?"; 
        $params = array($this->id , $qualID, $studentID);
        if($unitID != -1)
        {
            $sql .= " AND crit.bcgtunitid = ?";
            $params[] = $unitID;
        }
        return $DB->get_record_sql($sql, $params);
    }
    
    private function get_students_target_grade($studentID, $qualID, $unitID = -1)
    {
        global $DB;
        $sql = "SELECT grade.* FROM {block_bcgt_target_grades} AS grade
        JOIN {block_bcgt_user_criteria} AS usercriteria ON usercriteria.bcgttargetgradesid = grade.id 
        JOIN {block_bcgt_criteria} AS crit ON crit.id = usercriteria.bcgtcriteriaid
        WHERE usercriteria.bcgtcriteriaid = ? 
            AND usercriteria.bcgtqualificationid = ? 
        AND usercriteria.userid = ?"; 
        $params = array($this->id , $qualID, $studentID);
        if($unitID != -1)
        {
            $sql .= " AND crit.bcgtunitid = ?";
            $params[] = $unitID;
        }
        return $DB->get_record_sql($sql, $params);
    }
    
    
    
    /**
     * Checks if the student has had criteria values put into the database yet
     */
    private function does_student_criteria_exist($qualID = -1)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_criteria}
        WHERE bcgtqualificationid = ? AND bcgtcriteriaid = ? AND userid = ?";
        $params = array($qualID, $this->id, $this->studentID);
        $records = $DB->get_records_sql($sql, $params);
        return end($records);
    }
    
    /**
     * This copies the users criteria records and puts them into a history.
     * This is used for example when a user deletes the comments. 
     */
    private function archive_students_criteria()
    {
        global $DB;
                
        $sql = "INSERT INTO {block_bcgt_user_criteria_his}
        (bcgtusercriteriaid, userid, bcgtqualificationid, bcgtcriteriaid, bcgtrangeid, bcgtvalueid, dateset, 
        setbyuserid, dateupdated, updatedbyuserid, comments, bcgtprojectid, userdefinedvalue, targetdate, 
        bcgttargetgradesid, bcgttargetbreakdownid, flag) 
        SELECT * FROM {block_bcgt_user_criteria} WHERE id = ?";
        return $DB->execute($sql, array($this->studentCriteriaID));
    }    
    
    public static function get_name_from_id($id)
    {
        global $DB;
        $check = $DB->get_record("block_bcgt_criteria", array("id" => $id), "name");
        return ($check) ? $check->name : false;
    }
    
    public function update_students_value_auto($valueID)
    {
        $value = new Value($valueID);
        $this->studentValue = $value;
        return true;
    }
    
    protected function are_all_sub_criteria_achieved()
    {
        if($this->subCriteriaArray)
        {
            foreach($this->subCriteriaArray as $sub)
            {
                $value = $sub->get_student_value();
                if(!$value) return false;
                if(!$value->is_criteria_met_bool()) return false;                    
            }

            return true;

        }

        return false;

    }
    
    public function add_grading_form_select_option($val, $info, &$el){
        
        $el->addOption($info['value'], $val);
        
    }
    
    public function get_grading_form_select($critinfo, &$mform){
        
        return $mform->addElement('select', 'criteria['.$critinfo['qualID'].']['.$this->unitID.'][' . $this->id . ']', $this->get_name());
        
    }
    
    
}
