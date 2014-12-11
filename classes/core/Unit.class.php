<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
abstract class Unit {
    
    protected $id;
	protected $name;
	protected $details;
	protected $criterias = array();
    protected $tasks = array();
	protected $uniqueID;
    protected $dateUpdated = null;
	
    //object
	protected $unitType;
	protected $unitTypeID;

	protected $levelID;
    protected $subTypeID;
	//this is the level object.
	protected $level;
	//user values
	//is this an object or an id or a string?
	protected $userAward;
	protected $studentDoing;
	protected $studentID;
        
    protected $comments;
    protected $studentComments;
        
    protected $userDefinedValue;
    protected $valueID;
    //object could include grade stuff?
    protected $value;
    protected $setByUserId;
    protected $updatedByUserID;
    protected $dateSet;
        
    protected $projectsArray = array();    
    protected $aestheticName;
    protected $specificAwardType;
    protected $ruleID;
    protected $criteriaNames;
    protected $studentFlag;
    
    protected $weighting;

    //$loadParams->loadLevel = Qualification::LOADLEVELUNITS
    public function Unit($unitID, $params, $loadParams = null)
    {                
        if($unitID != -1 && $params == null)
		{
			//then we have been given the ID only and so we get it from the database.
			$this->id = $unitID;
			$unit = Unit::get_unit($unitID);
			if($unit)
			{
				$this->name = $unit->name;
				$this->details = $unit->details;
				$this->uniqueID = $unit->uniqueid;
                $this->aestheticName = $unit->aestheticname;
                $this->specificAwardType = $unit->specificawardtype;
                $this->weighting = $unit->weighting;
                $unitType = $unit->bcgttypeid;
                if($unit->bcgtlevelid != '')
                {
                	$this->levelID = $unit->bcgtlevelid;
                }
                else
               	{
                	$this->levelID = -1;
                }              
                $level = Unit::retrieve_level($unitID);
                if($level)
                {
                	$levelObj = new Level($level->bcgtlevelid, $level->trackinglevel);
                	$this->level = $levelObj;
                }
                else
                {
                	$this->level = false;
                }
                
                if (isset($unit->bcgtsubtypeid) && $unit->bcgtsubtypeid != ''){
                    $this->subTypeID = $unit->bcgtsubtypeid;
                } else {
                    $this->subTypeID = -1;
                }
                
                $this->pathwaytypeid = $unit->pathwaytypeid;
                if($loadParams && $loadParams->loadLevel && $loadParams->loadLevel >= Qualification::LOADLEVELCRITERIA)
                {
                    //TODO make sure that this is an array with the key being the id
                    //get the criteria from the database
                    $criterias = Unit::set_up_criteria($unitID, $unitType, $loadParams);
                    $this->criterias = $criterias->criteria;
                    $this->criteriaNames = $criterias->names;
                    $this->tasks = Unit::set_up_tasks($unitID);
                }
                $this->unitTypeID = $unit->bcgtunittypeid;
			}			
		}
		else
		{
            if($params)
            {
                //we are created a new unit or changing a pre-existing one.
                $this->id = $unitID;
                if(isset($params->name))
                {
                     $this->name = $params->name;
                }
                if(isset($params->details))
                {
                    $this->details = $params->details;
                }
                $this->aestheticName = (isset($params->aestheticName)) ? $params->aestheticName : "";
                $this->rules = (isset($params->rules)) ? $params->rules : "";
                $this->specificAwardType = (isset($params->specificAwardType)) ? $params->specificAwardType : "";
                $this->uniqueID = (isset($params->uniqueID)) ? $params->uniqueID : "";
                $this->weighting = (isset($params->weighting)) ? $params->weighting : 1;
                if(isset($params->bcgttypeid))
                {
                    $unitType = $params->bcgttypeid;
                }
                else
                {
                    $unitType = Unit::get_unit_tracking_type($unitID);
                }
                if(isset($params->bcgttypefamilyid))
                {
                    $unitFamilyType = $params->bcgttypefamilyid;
                }
                else
                {
                    $unitFamilyType = Unit::get_unit_tracking_type_family($unitID);
                }
                if(!isset($params->criteria) || !is_object($params->criteria))
                {                    
                    if($unitID != -1 && $loadParams && 
                            $loadParams->loadLevel && 
                            $loadParams->loadLevel >= Qualification::LOADLEVELCRITERIA)
                    {
                        //lets get the criteria from the db
                        $criterias = Unit::set_up_criteria($unitID, $unitType, $loadParams);
                        if($criterias)
                        {
                            $this->criterias = $criterias->criteria;
                            $this->criteriaNames = $criterias->names;
                        }
                        $this->tasks = Unit::set_up_tasks($unitID);
                    }
                }
                else
                {
                    $this->criteria = $params->criteria;
                }
                if(isset($params->level))
                {
                    $this->level = $params->level;
                }
                if(isset($params->levelID))
                {
                    $this->levelID = $params->levelID;
                }
                
                $this->pathwaytypeid = Unit::get_unit_pathway_type($unitID);
                
            }
			
			//can we retrieve the levelID?
			if($unitID != -1)
			{
				$level = Unit::retrieve_level($unitID);
                if($level)
                {
                	$levelObj = new Level($level->bcgtlevelid, $level->trackinglevel);
                	$this->level = $levelObj;
                	$this->levelID = $level->bcgtlevelid;
                }
                else
                {
                	$this->level = false;
                	$this->levelID = -1;
                }
			}
		}
		$unitTypeObj = Unit::retrieve_unit_type($unitID);
		if($unitTypeObj)
		{
			$this->unitType = $unitTypeObj->type;
			$this->unitTypeID = $unitTypeObj->id;
		}
    }
    
    public function get_id()
	{
		return $this->id;
	}
	
	public function get_name()
	{
		return html($this->name);
	}
	
	public function set_name($name)
	{
		$this->name = $name;
	}
        
    public function get_name_code()
    {
        $pos = strpos($this->name, ":");
        if(is_int($pos)) return preg_replace("/Unit(\s*)/i", "", substr($this->name, 0, $pos));
        else return preg_replace( "/Unit(\s*)/i", "", $this->name );
    }
	
	public function set_details($details)
	{
		$this->details = $details;
	}
    
    public function get_comments()
    {
        return $this->comments;            
    }
	
	public function get_details()
	{
		return $this->details;	
	}
	
	public function get_uniqueID()
	{
		return $this->uniqueID;
	}
	
	public function set_uniqueID($uniqueID)
	{
		$this->uniqueID = $uniqueID;
	}
	        
    public function get_date_updated()
    {
        return $this->dateUpdated;
    }
    
    public function get_last_update_date()
    {
        
    }

    public function set_date_updated($date)
    {
        $this->dateUpdated = $date;
    }
        
    public function set_date()
    {
        if($this->dateSet && $this->dateSet != 1 && $this->dateSet != 0)
        {
            $this->dateUpdated = date('d M Y H:m:s');
        }
        else
        {
            $this->dateSet = date('d M Y H:m:s');
        }
    }
    
    /**
     * This must be true or false. 
     * @param type $doing
     */
    public function set_is_student_doing($doing)
	{
		$this->studentDoing = $doing;
	}
	
	public function is_student_doing()
	{
		return $this->studentDoing;
	}
	
	public function set_user_award(Award $award)
	{
		$this->userAward = $award;
	}
	
	public function get_user_award()
	{
		return $this->userAward;
	}
	
	public function get_unit_type()
	{
		return $this->unitType;
	}
	
	public function set_unit_type($unitType)
	{
		$this->unitType = $unitType;
	}
	
	public function get_unit_type_id()
	{
		return $this->unitTypeID;
	}
	
	public function get_level_id()
	{
		return $this->levelID;
	}
    
    public function get_student_ID(){
        return $this->studentID;
    }
	
	public function set_level_id($levelID)
	{
		$this->levelID = $levelID;
	}
	
	public function get_level()
	{
		return $this->level;
	}
	
	public function set_level($level)
	{
		$this->level = $level;
	}
    
    public function get_weighting(){
        // If it's null, as opposed to 0, return a default weighting of 1
        return (!is_null($this->weighting) && $this->weighting !== "") ? $this->weighting : 1;
    }
	
	public function set_unit_type_ID($unitTypeID)
	{
		$this->unitTypeID = $unitTypeID;
	}
    
    public function get_pathway_type(){
        return $this->pathwaytypeid;
    }
	
	public function set_criteria($criterias)
	{
		$this->criterias = $criterias;
	}
	
	public function get_criteria()
	{
		return $this->criterias;
	}
        
    public function get_tasks()
    {
        return $this->taskArray;
    }

    public function set_tasks($tasks)
    {
        $this->taskArray = $tasks;
    }
	
	public function add_single_criteria($criteria)
	{
        $this->criterias[] = $criteria;	
	}
        
    public function return_comments()
    {
        return $this->comments;
    }

    public function set_comments($comment)
    {
        $this->comments = $comment;
    }
    
    public function set_student_comments($comment)
    {
        $this->studentComments = $comment;
    }

    public function get_student_comments(){
        return $this->studentComments;
    }
    
    public function get_specific_award_type()
    {
        return $this->specificAwardType;
    }
	
	public function get_projects()
	{
		return $this->projectsArray;
	}
	
	public function set_projects(Array $projects)
	{
		$this->projectsArray = $projects;
	}
    
    public function get_criteria_names()
    {
        return $this->criteriaNames;
    }
	
	public function get_userdefinedvalue()
	{
		return $this->userDefinedValue;
	}
	
	public function set_userdefinedvalue($userdefinedvalue)
	{
		$this->userDefinedValue = $userdefinedvalue;		
	}
	
	public function get_value()
	{
		return $this->value;
	}
	
	public function set_value($value)
	{
		$this->value = $value;
	}
	
	public function set_user($userID)
	{
		if(!$this->setByUserId || $this->setByUserId == '')
		{
			$this->setByUserId = $userID;
		}	
		else
		{
			$this->updatedByUserID = $userID;	
		}
	}
    
    function set_student_flag($flag)
    {
        $this->studentFlag = $flag;
    }
      
    /**
     * Get the name of the group this unit is in, on this qual
     * @param type $qualID
     */
    public function get_unit_group($qualID){
        
        global $DB;
        
        $record = $DB->get_record("block_bcgt_qual_units", array("bcgtqualificationid" => $qualID, "bcgtunitid" => $this->id));
        return ($record && !is_null($record->groupname)) ? $record->groupname : false;
        
    }
    
    /**
     * This is frmo the edit unit form page, it sets $rules to an array of rule IDs
     * @param type $rules 
     */
    public function set_rules($rules)
    {
        $this->rules = (is_array($rules) && !empty($rules)) ? $rules : null;
    }
        
    public function get_rules()
    {
        if($this->rules){
            return $this->rules;
        }
        return false;
    }
    
    public function get_ranges()
    {
        global $DB;
        return $DB->get_records("block_bcgt_range", array("bcgtunitid" => $this->id), "", "id");
    }
    
    public function get_display_name()
    {
        
        $output = "";
        //$output .= $this->get_uniqueID() . " ";
        $output .= $this->get_name();
        
        if ($this->level && $this->level->get_id() > 0){
            $output .= " (L{$this->level->get_level_number()})";
        }
        
        return $output;
        
    }
    
    /**
	 * Loads the students information into the unit and subsequent criteria.
	 * Does the unit have an award, if so can we retrieve it?
	 * If not can we calculate it?
	 * Is the student actually doing this unit?
	 * For each criteria load the students information
	 * @param unknown_type $studentID
	 * @param unknown_type $qualID
	 */
    //$loadLevel = QUALIFICATION::LOADLEVELUNITS, $loadAward = false
	public function load_student_information($studentID, $qualID, 
            $loadParams = null)
	{
        global $DB;
        $this->clear_student_information();
		$this->studentID = $studentID;
        $this->qualID = $qualID;
        
        $this->student = $DB->get_record("user", array("id" => $studentID));
        
		//is the student doing this unit?
        $onThisUnit = true;
        if($qualID != -1)
        {
            $onThisUnit = $this->student_doing_unit($qualID); 
        }                       
		if($onThisUnit)
		{
			$this->studentDoing = true;
			//for each criteria load_student_information. 
			if($loadParams && $loadParams->loadLevel && 
                    $loadParams->loadLevel >= Qualification::LOADLEVELCRITERIA && 
                    $this->criterias)
			{
                $loadSubCriteria = ($loadParams->loadLevel >= Qualification::LOADLEVELSUBCRITERIA) ? true : false;
				foreach($this->criterias AS $criteria)
				{
					$criteria->load_student_information($studentID, $qualID, $this->id, $loadSubCriteria);
				}	
			}
            
			//can this unit have an award calculated for it?
			if($loadParams && isset($loadParams->loadAward) && $this->unit_has_award())
			{
				//what is the award the student currently has for this unit?
				$unitAward = $this->retrieve_unit_award($qualID);
				if($unitAward)
				{
                    $params = new stdClass();
                    $params->award = $unitAward->award;
                    $params->rank = $unitAward->ranking;
                    $params->shortaward = $unitAward->shortaward;
                    
					$award = new Award($unitAward->id, $params);
					$this->userAward = $award;
                                                            
                    if(!is_null($unitAward->dateupdated) && $unitAward->dateupdated > 0){
                        $this->set_date_updated($unitAward->dateupdated);
                    }                  
				}
				elseif(!isset($loadParams->calcAward) || (isset($loadParams->calcAward) && $loadParams->calcAward))
				{
					//ok go and calculate it if we can.
					$this->userAward = $this->calculate_unit_award($qualID);
				}
			}
             
            // Get the comments on the student's unit as well
            $this->set_comments($this->retrieve_comments());
            $this->studentComments = $this->retrieve_student_comments();
            //TODO qual specific:     
            //
            //TODO put a loadLevel param into this. 
            //                  
			//does this unit have specific values/grades/informaton set for this student?
			//this may want to be put onto the qualification sepcific unit class
			//as the id fields and info fields may eventually point
			//to different things depending on the quals.
			$this->load_student_values($qualID);
		}
		else
		{
			$this->studentDoing = false;
		}	     
                
	}
    
    protected function clear_student_information()
    {
        $this->userAward = null;
    }
    
    public static function get_units_quals($unitID, $search = '')
    {
        return UNIT::get_quals($unitID, $search);
    }
    
    public static function get_possible_unit_awards($typeID)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_type_award} WHERE bcgttypeid = ? ORDER BY ranking ASC";
		return $DB->get_records_sql($sql, array($typeID));
	}
    
    /**
	 * Gets the qualifications that this unit has been put on. 
	 * @return mixed
	 */
	public function get_quals_on($search = '', $userID = -1, $roleID = -1, $courseID = -1)
	{
		return UNIT::get_quals($this->id, $search, $userID, $roleID, $courseID);
	}
    
    public function get_quals_on_roles($search = '', $userID = -1, $roles = array(), $courseID = -1)
	{
		return UNIT::get_quals_roles($this->id, $search, $userID, $roles, $courseID);
	}
    
    public function get_attribute($name, $qualID=null, $userID=null)
    {

        global $DB;
        $check = $DB->get_record("block_bcgt_unit_attributes", array("bcgtunitid" => $this->id, "bcgtqualificationid" => $qualID, "attribute" => $name, "userid" => $userID));
        return ($check) ? $check->value : false;

    }
    
    public function set_attribute($name, $value, $qualID, $userID=null)
    {
        global $DB;
        
        $check = $DB->get_record("block_bcgt_unit_attributes", array("bcgtunitid" => $this->id, "bcgtqualificationid" => $qualID, "attribute" => $name, "userid" => $userID));
        if($check)
        {
            $check->value = $value;
            return $DB->update_record("block_bcgt_unit_attributes", $check);
        }
        else
        {
            $obj = new stdClass();
            $obj->bcgtunitid = $this->id;
            $obj->bcgtqualificationid = $qualID;
            $obj->attribute = $name;
            $obj->value = $value;
            $obj->userid = $userID;
            return $DB->insert_record("block_bcgt_unit_attributes", $obj);
        }
    }
    
    /**
	 * Gets the used criteria names from this unit. 
	 * @return multitype:
	 */
	public function get_used_criteria_names()
	{
        global $CFG;
        
		$usedCriteriaNames = array();
        
		if($this->criterias)
		{
			foreach($this->criterias AS $criteria)
			{
				$usedCriteriaNames[] = $criteria->get_name();
			}
		}
        
		return $usedCriteriaNames;
	}
    
    public function get_unit_award_points($bcgtTypeAwardID, $bcgtLevelID)
    {
        global $DB;
		$sql = "SELECT * FROM {block_bcgt_unit_points} 
            WHERE bcgtlevelid = ? AND bcgttypeawardid = ?";
		return $DB->get_record_sql($sql, array($bcgtLevelID, $bcgtTypeAwardID));
    }
    
    protected static function get_quals_roles($unitID, $search = '', $userID = -1, $roles = array(), $courseID = -1)
    {
        global $DB;
		$sql = "SELECT distinct(qual.id) AS id, type.type, level.trackinglevel, 
            subtype.subtype, qual.name, family.family, qual.additionalname 
            FROM {block_bcgt_qual_units} AS qualUnits 
		JOIN {block_bcgt_qualification} AS qual ON qual.id = qualUnits.bcgtqualificationid 
		JOIN {block_bcgt_target_qual} AS targetQual ON targetQual.id = qual.bcgttargetqualid 
		JOIN {block_bcgt_type} AS type ON type.id = targetQual.bcgttypeid 
		JOIN {block_bcgt_subtype} AS subtype ON subtype.id = targetQual.bcgtsubtypeid 
		JOIN {block_bcgt_level} AS level ON level.id = targetQual.bcgtlevelid
        JOIN {block_bcgt_type_family} AS family ON family.id = type.bcgttypefamilyid";
        if($userID != -1 && count($roles) > 0)
        {
            $sql .= " JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qual.id 
                JOIN {role} role ON role.id = userqual.roleid ";
        }
        if($courseID != -1 && $courseID != SITEID)
        {
            $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id";
        }
		$sql .= " WHERE qualUnits.bcgtunitid = ? ";
        $params = array($unitID);
        if($search != '')
        {
            $sql .= " AND qualname LIKE ?";
            $params[] = '%'.$search.'%';
        }
        if($userID != -1 && count($roles > 0))
        {
            $sql .= ' AND (';
            $count=0;
            foreach($roles AS $role)
            {
                $count++;
                $sql .= " role.shortname= ?";
                if($count != count($roles))
                {
                    $sql .= ' OR';
                }          
                $params[] = $role;
            }

            $sql .= ') AND userqual.userid = ?';
            $params[] = $userID;
        }
        if($courseID != -1 && $courseID != SITEID)
        {
            $sql .= " AND coursequal.courseid = ?";
            $params[] = $courseID;
        }
		return $DB->get_records_sql($sql, $params);
    }
    
    protected static function get_quals($unitID, $search = '', $userID = -1, $roleID = -1, $courseID = -1)
    {
        global $DB;
		$sql = "SELECT qual.id AS id, type.type, level.trackinglevel, 
            subtype.subtype, qual.name, family.family, qual.additionalname 
            FROM {block_bcgt_qual_units} AS qualUnits 
		JOIN {block_bcgt_qualification} AS qual ON qual.id = qualUnits.bcgtqualificationid 
		JOIN {block_bcgt_target_qual} AS targetQual ON targetQual.id = qual.bcgttargetqualid 
		JOIN {block_bcgt_type} AS type ON type.id = targetQual.bcgttypeid 
		JOIN {block_bcgt_subtype} AS subtype ON subtype.id = targetQual.bcgtsubtypeid 
		JOIN {block_bcgt_level} AS level ON level.id = targetQual.bcgtlevelid
        JOIN {block_bcgt_type_family} AS family ON family.id = type.bcgttypefamilyid";
        if($userID != -1 && $roleID != -1)
        {
            $sql .= " JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qual.id";
        }
        if($courseID != -1 && $courseID != SITEID)
        {
            $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id";
        }
		$sql .= " WHERE qualUnits.bcgtunitid = ? ";
        $params = array($unitID);
        if($search != '')
        {
            $sql .= " AND qualname LIKE ?";
            $params[] = '%'.$search.'%';
        }
        if($userID != -1 && $roleID != -1)
        {
            $sql .= " AND userqual.roleid = ? AND userqual.userid = ? ";
            $params[] = $roleID;
            $params[] = $userID;
        }
        if($courseID != -1 && $courseID != SITEID)
        {
            $sql .= " AND coursequal.courseid = ?";
            $params[] = $courseID;
        }
		return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Can be overridden by the other units
     * @return type
     */
    public function get_criteria_header()
	{
		return get_string('criteria', 'block_bcgt');
	}
    
    /**
	 * This function checks the parent criteria of this sub criteria
	 * it checks it to see if it can be put to 'met'. This is done if
	 * all of the sub criteria are now met
	 * it gets the parent criteria out of the database.
	 * then gets all of the sub criteria and checks if they have been met
	 * if they have it sets the parent criteria to met.
	 * @param unknown_type $id
	 */
	public function check_parent_criteria($criteriaID, $qualID = -1)
	{	
		//need to check if this is indeed a $subCriteria
		$criteriaType = Criteria::criteria_type($criteriaID);
		if($criteriaType == 'Sub')
		{
			//then we can check its parent
			$parentCriteria = $this->get_parent_criteria_from_sub($criteriaID);
            if($parentCriteria)
			{
				//get the sub criteria
				$subCriteria = $parentCriteria->get_sub_criteria();
				if($subCriteria)
				{
					$allMet = true;
					foreach($subCriteria AS $sub)
					{
						//get the students value
						$studentValue = $sub->get_student_value();
						if($studentValue)
						{
							//has it been met?
							if($studentValue->is_criteria_met() != 'Yes')
							{
								$allMet = false;
							}
						}
						else
						{
							$allMet = false;
						}
					}
					
					if($allMet)
					{
						//if we get to this point then we know all sub criteria have been met.
						//now set the parent criteria to met.
						$parentCriteria->set_criteria_to_met($qualID, false, $this->get_typeID());
						return true;
					}
					else
					{
						//at least one of the subCriteria has not been met
						//so dont put the parent criteria as met
						//but! What happens if it is already met!
						return $parentCriteria->unmeet_criteria($qualID, $this->get_typeID());
					}
					
				}
			}
		}
		return false;
	}
    
    /**
	 * Loops over the criteria and checks if it contains the subCriteria
	 * if it does it returns that criteria.
	 * @param unknown_type $subCriteriaID
	 */
	public function get_parent_criteria_from_sub($subCriteriaID)
	{
		if($criterias = $this->get_criteria())
		{
			foreach($criterias AS $criteria)
			{
				if($criteria->does_criteria_have_sub_criteria($subCriteriaID))
				{
					return $criteria;
				}
			}
		
		}
		return -1;
	}

    /**
	 * Searches through the crieria on this unit looking for the criteriaID or name specified
	 * @param unknown_type $criteriaID
	 * @param unknown_type $name
	 * if criteriaID is not -1 it will search on that, else it will
	 * search on the name
	 */
	public function get_single_criteria($criteriaID = -1, $name = '')
	{
		//we dont know if this criteriaID is the parent or sub. 
		//// CURRENTLY ONLY WORKS FOR ONE LEVEL OF SUB CRITERIA
		//we dont know if the criteria id is a parent criteria or a sub criteria
		//so lets find out. 
		return $this->get_single_criteria_from_arrays($this->criterias, $criteriaID, $name);
	} 
    
    /**
	 * Saves the unit and all of the criteria
	 */
	public function save($updateCriteria = true)
	{
		//TODO should do if exists in db then update, else do insert
		$this->update_unit($updateCriteria);
		//dont need to do the below as its done in update_unit.
		//$this->save_criteria();	
	}
    
    /**
     * This saves the student on the unit
     * or removes them from it
     * CALLED AFTER LOAD_STUDENT_INFORMATION
     */
    public function save_students_units($qualID)
    {
        //is the student doing this unit?
            //were they before
        //if not
            //were they before
        $databaseOn = $this->is_student_on_unit($qualID, $this->studentID);
        if($databaseOn)
        {
            //they are on it in the database
            if(!$this->is_student_doing() && $this->is_student_doing() != 'Yes')
            {
                //they are not on it in the object
                $this->delete_student_on_unit($databaseOn->id);
            }
        }
        else
        {
            //they arent on it in the database
            if($this->is_student_doing() || $this->is_student_doing() == 'Yes')
            {
                //they are on it in the object
                $this->insert_student_on_unit($qualID, $this->studentID);
            }
        }
        
    }
    
    /**
     * 
     * @param type $qualID
     */
	public function save_student($qualID)
	{
        global $DB;
		//assumes THAT THE STUDENT IS DOING THE UNIT ALREAY
		$studentsUnitRecord = $this->student_doing_unit($qualID);
		if($studentsUnitRecord)
		{
            
            $this->insert_students_unit_history($qualID, $studentsUnitRecord->id);
            
			$id = $studentsUnitRecord->id;
			$stdObj = new stdClass();
			$stdObj->id = $id;
			if($this->userAward)
			{
				$stdObj->bcgttypeawardid = $this->userAward->get_id();
			}
			$stdObj->dateupdated = time();
			$stdObj->updatedbyuserid = $this->updatedByUserID;
            if($this->updatedByUserID == null || $this->updatedByUserID == '')
            {
            	$stdObj->updatedbyuserid = $this->setByUserId;
            }
            $stdObj->userdefinedvalue = $this->userDefinedValue;
            if($this->value)
            {
            	$stdObj->bcgtvalueid = $this->value->get_id();
            }
            
            
			$DB->update_record('block_bcgt_user_unit', $stdObj);
            // Log
            $awardTypeID = -1;
			if($this->userAward)
            {
            	$awardTypeID = $this->userAward->get_id();
            }
            logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_UNIT, LOG_VALUE_GRADETRACKER_UPDATED_UNIT_AWARD, $studentsUnitRecord->userid, $qualID, $this->id, null, $this->id, $awardTypeID);
        
		}
		
	}
    
    /**
	 * Loops over the original criteria this unit had from the database
	 * if it has been removed from the unit it creates a history
	 * and then deletes the criteria from the database
	 * It then calls save on each criteria. 
	 */
	public function save_criteria()
	{
		//check its been removed
		$this->check_criteria_removed();
		
		if($this->criterias)
		{
			foreach($this->criterias AS $criteria)
			{
				$criteria->save();
			}
		}
	}
    
    /**
     * Adds this unit to the array of qualID's passed in.
     * @param type $qualIDs
     */
    public function add_to_quals($qualIDs)
    {
        global $DB;
        $roleDB = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = ?', array('student'));
        foreach($qualIDs AS $qualID)
        {
            $stdObj = new stdClass();
            $stdObj->bcgtqualificationid = $qualID;
            $stdObj->bcgtunitid = $this->id;
            $DB->insert_record('block_bcgt_qual_units', $stdObj);
            
            
            //TODO this will be a config. 
            //are there any users on the qual?
            // Find all users on this qual and add them to this unit as well
            $records = $DB->get_records_sql("SELECT * FROM {block_bcgt_user_qual} 
                WHERE bcgtqualificationid = ? AND roleid = ?", array($qualID, $roleDB->id));
            if($records)
            {
                
                foreach($records as $record)
                {
                    
                    $obj = new stdClass();
                    $obj->bcgtqualificationid = $qualID;
                    $obj->bcgtunitid = $this->id;
                    $obj->userid = $record->userid;
                    $obj->bcgttypeawardid = null;
                    $DB->insert_record("block_bcgt_user_unit", $obj);
                    
                }
                
            }
            
        }
    }
    
    /*
	 * Gets the list of unit types for the unitTypeID passed in.
	 */
	public static function get_unit_types($trackingTypeID, $id = -1)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_unit_type} WHERE bcgttypeid = ?";
		if($id != -1)
		{
			$sql .= " AND id = ?";
			return $DB->get_record_sql($sql, array($trackingTypeID, $id));
		}
		return $DB->get_records_sql($sql, array($trackingTypeID));
	}
    
    /**
     * Removes the unit from the quals
     * @global type $DB
     * @param type $qualIDs
     */
    public function remove_from_quals($qualIDs)
    {
        global $DB;
        $roleDB = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = ?', array('student'));
        foreach($qualIDs AS $qualID)
        {
            $DB->delete_records('block_bcgt_qual_units', 
                    array('bcgtqualificationid'=>$qualID, 'bcgtunitid'=>$this->id));
            
            //TODO this will be a config. 
            
            //now delete all students from this unit
            $sql = "DELETE FROM {block_bcgt_user_unit} WHERE userid IN 
                (SELECT userid FROM {block_bcgt_user_qual} WHERE bcgtqualificationid = ?
                AND roleid = ?) 
                AND bcgtqualificationid = ? AND bcgtunitid = ?";
            $DB->execute($sql, array($qualID, $roleDB->id, $qualID, $this->id)); 
        }
    }
    
    //overridden on quals such as the Alevels.
	protected function load_student_values($qualID)
	{
		$unitValues = $this->get_student_unit_values($qualID);
		if($unitValues)
		{
			$this->userDefinedValue = $unitValues->userdefinedvalue;
			if($unitValues->bcgtvalueid)
			{
                $params = new stdClass();
                $params->value = $unitValues->value;
                $params->shortValue = $unitValues->shortvalue;
                $params->specialVal = $unitValues->specialval;
				$value = new Value($unitValues->bcgtvalueid, $params);
				$this->value = $value;
				$this->valueID = $unitValues->bcgtvalueid;
			}
			
		}
	}
    
    public function retrieve_sign_off_sheets()
    {
        $sheets = $this->signOffSheets;
        $original = $this->load_sign_off_sheets();
        $this->signOffSheets = $sheets;
        return $original;
    }
    
    public function load_sign_off_sheets($forceReload = false)
    {
    
        global $DB;
        
        if (!$forceReload){
        
            if (isset($this->signOffSheets) && !empty($this->signOffSheets)){
                return $this->signOffSheets;
            }
        
        }
        
        $this->signOffSheets = array();

        $check = $DB->get_records("block_bcgt_signoff_sheet", array("bcgtunitid" => $this->id));
        if($check)
        {
            foreach($check as $sheet)
            {

                $sheet->ranges = array();

                // Get ranges on it as well
                $get = $DB->get_records("block_bcgt_soff_sheet_ranges", array("bcgtsignoffsheetid" => $sheet->id));
                if($get)
                {
                    foreach($get as $range)
                    {
                        $sheet->ranges[$range->id] = $range;
                    }
                }

                $this->signOffSheets[$sheet->id] = $sheet;
            }
        }

        return $this->signOffSheets;
    }
    
    
    public function get_signoff_sheets()
    {
        return $this->signOffSheets;
    }

    public function add_signoff_sheet($sheet)
    {
        if (isset($sheet->id) && $sheet->id > 0) $this->signOffSheets[$sheet->id] = $sheet;
        else $this->signOffSheets[] = $sheet;
    }

    protected function is_sheet_completed($sheet)
    {

        global $DB;
        
        if(!$sheet->ranges) return true;

        // Find all ranges on sheet and see if there is a value for each of them
        foreach($sheet->ranges as $range)
        {
            $check = $DB->get_records_select("block_bcgt_user_soff_sht_rgs", "userid = ? AND bcgtqualificationid = ? AND bcgtsignoffsheetid = ? AND bcgtsignoffrangeid = ? AND value = ? AND observationnum > ?", array($this->studentID, $this->qualID, $sheet->id, $range->id, 1, 0));
            if(!$check) return false;
        }

        return true;

    }
    
    
	protected function check_signoff_sheets_removed()
	{
        global $DB;
        
        $original = $this->retrieve_sign_off_sheets();
                
		if($original)
		{
			foreach($original AS $sheet)
			{
				if(!array_key_exists($sheet->id, $this->signOffSheets))
				{
					$DB->delete_records('block_bcgt_signoff_sheet', array('id'=>$sheet->id));
                    $DB->delete_records('block_bcgt_soff_sheet_ranges', array('bcgtsignoffsheetid' => $sheet->id));
                    // Log
                    logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_UNIT, LOG_VALUE_GRADETRACKER_DELETED_SIGNOFF_SHEET, null, null, $this->id, null, $this->id, $sheet->id);
				}
                else
                {
                    
                    // Check if any of its ranges have been removed
                    if ($sheet->ranges)
                    {
                        foreach($sheet->ranges as $range)
                        {
                            if (!array_key_exists($range->id, $this->signOffSheets[$sheet->id]->ranges))
                            {
                                $DB->delete_records('block_bcgt_soff_sheet_ranges', array('id' => $range->id));
                                // Log
                                logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_UNIT, LOG_VALUE_GRADETRACKER_DELETED_SIGNOFF_SHEET_RANGE, null, null, $this->id, null, $this->id, $range->id);
                            }
                        }
                    }
                    
                }
			}
		}
	}
    
    
    
    
    public function has_percentage_completions()
    {
        return false;
    }
    
    //THis may want to go on the qualification specific unit classes
	//at some point.
	protected function get_student_unit_values($qualID = -1)
	{
		global $DB;
		$sql = "SELECT distinct(userunit.id), userunit.*, value.shortvalue, value.ranking, value.value, 
            value.customvalue, value.customshortvalue, value.context, 
            value.bcgttypeid, value.bcgttargetqualid, value.specialval
		FROM {block_bcgt_user_unit} AS userunit 
		LEFT OUTER JOIN {block_bcgt_value} AS value ON value.id = userunit.bcgtvalueid
		WHERE userid = ? AND bcgtunitid = ?";
        $params = array($this->studentID, $this->id);
		if($qualID != -1)
		{
			$sql .= " AND bcgtqualificationid = ?";
            $params[] = $qualID;
		}
		$records = $DB->get_records_sql($sql, $params);
        if($records)
        {
            return end($records);
        }
        return false;
	}
    
    /**
     * Check if a given criteria id exists within an array of criteria and sub criteria
     * @param type $critieras
     * @param type $id
     */
    protected function exists_in_criteria_or_sub_criteria($criteria, $id){
        
        if ($criteria)
        {
            
            foreach($criteria as $crit)
            {
                if ($crit->get_id() == $id)
                {
                    return true;
                }
                
                // Try sub criteria
                if ($crit->get_sub_criteria())
                {
                    $try = $this->exists_in_criteria_or_sub_criteria($crit->get_sub_criteria(), $id);
                    if ($try)
                    {
                        return true;
                    }
                }
                
            }
        }
        
        
        return false;
        
    }
    
    /**
	 *Loops over the original criteria this unit had from the database
	 * if it has been removed from the unit it creates a history
	 * and then deletes the criteria from the database 
	 */
	protected function check_criteria_removed()
	{
        global $DB;
		//needs to find all of the criteria
		//that were on this unit that are not anymore(if any)
		$originalCriteria = $this->retrieve_criteria($this->id);            
		if($originalCriteria)
		{
			foreach($originalCriteria AS $origCriteria)
			{
				if(($this->criterias && !array_key_exists($origCriteria->id, $this->criterias)) || (!$this->criterias))
				{
                    //then do a history
					if($this->insert_criteria_history($origCriteria->id))
					{
                        //TODO we need to DELETE ANY SUB CRITERIA!!!!!!!!
                        
						//delete the record. 
						$DB->delete_records('block_bcgt_criteria', array('id'=>$origCriteria->id));
                        // Log
                        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, LOG_VALUE_GRADETRACKER_DELETED_CRIT, null, null, $this->id, null, $this->id, $origCriteria->id);
					}	
				}
			}
		}
	}
    
    /**
	 * Used to get the correct unit class based on the type that is passed in.
	 * @param unknown_type $typeID
	 */
	public static function get_unit_class_type($typeID = -1, $familyID = -1, 
            $params = null, $loadLevel = Qualification::LOADLEVELUNITS)
	{
        // Changed this
        return Unit::get_correct_unit_class($familyID, $typeID, 
                -1, $params, $loadLevel);
	}
    
    /**
    * Iserts a record into the tracking_user_unit table
    * @param $qualID
    * @param $studentID
    */
   public function insert_student_on_unit($qualID, $studentID)
   {
       global $DB;
       //first lets check if the student is on the unit already
       if($this->is_student_on_unit($qualID, $studentID))
       {
           return false;
       }
       $stdObj = new stdClass();
       $stdObj->bcgtqualificationid = $qualID;
       $stdObj->bcgtunitid = $this->id;
       $stdObj->userid = $studentID;
       $stdObj->bcgttypeawardid = -1;
       return $DB->insert_record('block_bcgt_user_unit', $stdObj);
   }
   
    public function is_student_on_unit($qualID, $studentID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_unit} 
        WHERE bcgtqualificationid = ? AND bcgtunitid = ? AND userid = ?";
        $record = $DB->get_record_sql($sql, array($qualID, $this->id, $studentID));
        if($record)
        {
            return $record;
        }
        return false;
    }
    
    /**
     * Delete self
     * If specific unit types need to do more, they can just extend this
     */
    public function delete_unit(){
        
        global $DB;
        
        // Firstly create an archive of ourself, then delete self
        $DB->execute(" INSERT INTO {block_bcgt_unit_history} (bcgtunitsid, uniqueid, name, credits, weighting, bcgttypeid, bcgtlevelid, bcgtunittypeid, details, aestheticname, specificawardtype, pathwaytypeid)
                       SELECT id, uniqueid, name, credits, weighting, bcgttypeid, bcgtlevelid, bcgtunittypeid, details, aestheticname, specificawardtype, pathwaytypeid FROM {block_bcgt_unit} WHERE id = ? ", array($this->id) );
        
        $DB->delete_records("block_bcgt_unit", array("id" => $this->id));
        
        
        
        // Archive and delete the links between this unit and any quals
        $DB->execute( "INSERT INTO {block_bcgt_qual_units_his}
                       (bcgtqualificationunitid, bcgtqualificationid, bcgtunitsid)
                       SELECT id, bcgtqualificationid, bcgtunitid FROM {block_bcgt_qual_units} WHERE bcgtunitid = ?", array($this->id) );
        
        $DB->delete_records("block_bcgt_qual_units", array("bcgtunitid" => $this->id));

        
        
        // Archive & delete any user_unit records for this unit
        $DB->execute( "INSERT INTO {block_bcgt_user_unit_his} 
                       (bcgtuserunitid, userid, bcgtqualificationid, bcgtunitid, bcgttypeawardid, comments, dateupdated, userdefinedvalue, bcgtvalueid, setbyuserid, updatedbyuserid, dateset, studentcomments) 
                       SELECT * FROM {block_bcgt_user_unit} WHERE bcgtunitid = ?", array($this->id) );
        
        $DB->delete_records("block_bcgt_user_unit", array("bcgtunitid" => $this->id));
        
        
        return true;      
        
        
    }
    
    /**
    * Deletes the student from the unit based on the student id, qualid and unit ID
    * //TODO what about archiving?
    * @param $studentID
    * @param $qualID
    */
    public function delete_student_on_unit_no_id($studentID, $qualID)
    {
        $record = $this->is_student_on_unit($qualID, $studentID);
        if($record)
        {
            //history!!!!!!
            $this->insert_students_unit_history($qualID, $studentID);
            return $this->delete_student_on_unit($record->id);
        }
        return -1;
    }
    
    /**
    * Deletes the record from tracking_user_unit where the id matches
    * SO therefore removes the user from the unit for this qualification.
    * IT DOES NOT remove their values
    * @param unknown_type $id
    */
    public function delete_student_on_unit($id)
    {
        global $DB;
        $DB->delete_records('block_bcgt_user_unit', array('id'=>$id));
    }
    
    /**
     * Gete the type of the unit
     * @global type $DB
     * @param type $unitID
     * @return type
     */
    public static function retrieve_unit_type($unitID)
	{
		global $DB;
		$sql = "SELECT unittype.* FROM {block_bcgt_unit_type} AS unittype 
		JOIN {block_bcgt_unit} AS unit ON unit.bcgtunittypeid = unittype.id 
		WHERE unit.id = ?";
		return $DB->get_record_sql($sql, array($unitID));
	}
    
    /**
	 *Used to return the correct Unit class based on the ID that is passed in. 
	 *This assumes that the UNIT exists in the database
	 */
	public static function get_unit_class_id($unitID, $loadParams)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_unit} WHERE id = ?";
		$record = $DB->get_record_sql($sql, array($unitID));
		$typeID = -1;
		if($record)
		{
			$typeID = $record->bcgttypeid;           
            // Get any rules as well
            $check = $DB->get_records_select("block_bcgt_rule_links", 
                    "bcgtunitid = ?", array("bcgtunitid"=>$unitID), "bcgtrulesid");
            $rules = array();
            if($check) foreach($check as $rule) $rules[] = new Rule($rule->ruleid);
            $params = new stdClass();
            $params->name = $record->name;
            $params->details = $record->details; 
            $params->uniqueID = $record->uniqueid;
            $params->aestheticName = $record->aestheticname; 
            $params->rules = $rules;
            $params->specificAwardType = $record->specificawardtype;
            $params->weighting = $record->weighting;
            
            
			return Unit::get_correct_unit_class(-1, $typeID, $unitID, $params, $loadParams);
		}
		return false;
	} 
    
    public function update_comments($qualID, $comments)
    {
        global $DB;
        
        $sql = "SELECT * FROM {block_bcgt_user_unit} AS userunit 
		WHERE userunit.userid = ? AND bcgtqualificationid = ? 
		AND bcgtunitid = ?";
		$userUnit = $DB->get_record_sql($sql, array($this->studentID, $qualID, $this->id));
		if($userUnit)
		{
			$id = $userUnit->id;
			$obj = new stdClass();
			$obj->id = $id;
			$obj->comments = $comments;
            
            $this->comments = $comments;
            
			return $DB->update_record('block_bcgt_user_unit', $obj);
		}
		return false;
    }
    
    public function update_student_comments($qualID, $comments)
    {
        global $DB;
        
        $sql = "SELECT * FROM {block_bcgt_user_unit} AS userunit 
		WHERE userunit.userid = ? AND bcgtqualificationid = ? 
		AND bcgtunitid = ?";
		$userUnit = $DB->get_record_sql($sql, array($this->studentID, $qualID, $this->id));
		if($userUnit)
		{
			$id = $userUnit->id;
			$obj = new stdClass();
			$obj->id = $id;
			$obj->studentcomments = $comments;
			return $DB->update_record('block_bcgt_user_unit', $obj);
		}
		return false;
    }
    
    
    public static function get_unit_edit_form_menu($familyID, $disabled, $unitID, $typeID)
	{
		$unitClass = Unit::get_plugin_class($familyID);
        if($unitClass)
        {
            return $unitClass::get_edit_form_menu($disabled, $unitID, $typeID);
        }
        return false;
	}
    
    
    /**
	 * Does this unit, for this student 
	 * on the qual passed in have at least one project
	 * @param unknown_type $qualID
	 */
	public function has_project_for_student($qualID)
	{
		//TODO THIS DOES NOT TAKE INTO CONSIDERATION GROUPS!!!
		global $CFG;
		$studentID = $this->studentID;
		//find the quals that the student has access to 
		//is one of them this qual?
		//if so, does it have a project for this unit?
		
		//find all projects on this unit for the qualID
		//the projects are on courses
		//does the student have access to this course?	
		$sqlSelect = "SELECT * FROM {block_bcgt_activity_refs} activityrefs
            JOIN on the course module. 
        WHERE activityrefs.bcgtunitid = ? AND activityrefs.bcgtqualificationid = ?";
        
    
        
        
        
		$sqlFrom = " FROM {$CFG->prefix}tracking_assignment_selection AS asssel";
		$sqlAssignmentTurn = " JOIN {$CFG->prefix}turnitintool AS turnitin ON turnitin.id = asssel.assignmentid 
		JOIN {$CFG->prefix}course AS course ON course.id = turnitin.course";
		$sqlAssignment = " JOIN {$CFG->prefix}assignment AS assignment ON assignment.id = asssel.assignmentid 
		JOIN {$CFG->prefix}course AS course ON course.id = assignment.course";
		$sqlJoin = " JOIN {$CFG->prefix}context AS context ON context.instanceid = course.id
		JOIN {$CFG->prefix}role_assignments AS role_ass ON role_ass.contextid = context.id 
		JOIN {$CFG->prefix}role AS role ON role.id = role_ass.roleid";
		$sqlWhere = " WHERE trackingunitid = $this->id AND trackingqualificationid = $qualID 
		AND context.contextlevel = 50 AND role.name = 'Student' AND role_ass.userid = $this->studentID";
		$sqlUnion = " UNION ";
		$sql = $sqlSelect.$sqlFrom.$sqlAssignmentTurn.$sqlJoin.$sqlWhere.$sqlUnion.$sqlSelect.$sqlFrom.$sqlAssignment.$sqlJoin.$sqlWhere;
		$records = get_records_sql($sql);
		if($records)
		{
			return true;	
		}
		return false;
	}
    
    public function get_users_on_unit($unitID, $editableByUserID = -1, $courseID = -1, $groupingID = -1)
    {
        global $DB;
        $sql = "SELECT distinct(user.id), user.*
            FROM {user} user 
            JOIN {block_bcgt_user_unit} userunit ON user.id = userunit.userid
            JOIN {block_bcgt_user_qual} userqual ON userqual.userid = user.id
            JOIN {block_bcgt_qual_units} qualunits ON qualunits.bcgtqualificationid = userqual.bcgtqualificationid 
            AND qualunits.bcgtunitid = userunit.bcgtunitid
            JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = userqual.bcgtqualificationid ";
        if($editableByUserID != -1)
        {
            $sql .= ' JOIN {block_bcgt_user_qual} userqualedit ON userqualedit.bcgtqualificationid = userqual.bcgtqualificationid 
                JOIN {role} role ON role.id = userqualedit.roleid';
        }
        if($groupingID != -1)
        {
            $sql .= ' JOIN {groups_members} members ON members.userid = user.id 
                JOIN {groupings_groups} gg ON gg.groupid = members.groupid';
        }
        $sql .= " WHERE userunit.bcgtunitid = ?";
        $params = array();
        $params[] = $unitID;
        if($editableByUserID != -1)
        {
            $sql .= ' AND userqualedit.userid = ? AND (role.shortname = ? OR role.shortname = ?) ';
            $params[] = $editableByUserID;
            $params[] = 'teacher';
            $params[] = 'editingteacher';
        }
        if($courseID != -1)
        {
            $sql .= ' AND coursequal.courseid = ?';
            $params[] = $courseID;
        }
        if($groupingID != -1)
        {
            $sql .= ' AND gg.groupingid = ?';
            $params[] = $groupingID;
        }
        return $DB->get_records_sql($sql, $params);
    }

    /**
	 * Builds the table of the unit information that gets presented to the 
	 * user when they hover of the unit name. This is called through ajax and jquery.
	 */
	public function build_unit_details_table()
	{
        global $CFG;
		$retval = "<div id='unitName$this->id' class='tooltipContent'>".
                "<div><h3>$this->name</h3><table><tr><th>".get_string('criterianame','block_bcgt')."</th>".
                "<th>".get_string('criteriaDetails','block_bcgt')."</th></tr>";
		if($this->criterias)
		{
			foreach($this->criterias AS $criteria)
			{
				$retval .= "<tr><td>".$criteria->get_name()."</td><td>".$criteria->get_details()."</td></tr>";
			}
		}
		$retval .= "</table></div></div>";
		
		return $retval;
                
	}
    
    public static function get_unit_tracking_type($unitID)
    {

        global $DB;
        $sql = "SELECT * FROM {block_bcgt_unit} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($unitID));
        if($record)
        {
            return $record->bcgttypeid;
        }
        return false;

    }
    
    public static function get_unit_tracking_type_family($unitID)
    {

        global $DB;
        $sql = "SELECT type.* FROM {block_bcgt_unit} unit 
            JOIN {block_bcgt_type} type ON type.id = unit.bcgttypeid 
            WHERE unit.id = ?";
        $record = $DB->get_record_sql($sql, array($unitID));
        if($record)
        {
            return $record->bcgttypefamilyid;
        }
        return false;

    }
    
    public static function get_unit_pathway_type($unitID){
        global $DB;
        $record = $DB->get_record("block_bcgt_unit", array("id" => $unitID), "id, pathwaytypeid");
        return ($record) ? $record->pathwaytypeid : false;
    }
    
    protected function retrieve_comments()
    {
        global $DB;
        $checks = $DB->get_records_select("block_bcgt_user_unit", 
                "userid = ? AND bcgtqualificationid = ? AND bcgtunitid = ?",  
                array($this->studentID,$this->qualID,$this->id));
        $check = new stdClass();
        if($checks)
        {
            $check = end($checks);
        }
        if(!isset($check->id) || is_null($check->id) || $check->comments == ""){
            return "";
        }
        return $check->comments;
    }
    
    protected function retrieve_student_comments()
    {
        global $DB;
        $checks = $DB->get_records_select("block_bcgt_user_unit", 
                "userid = ? AND bcgtqualificationid = ? AND bcgtunitid = ?",  
                array($this->studentID,$this->qualID,$this->id));
        $check = new stdClass();
        if($checks)
        {
            $check = end($checks);
        }
        if(!isset($check->id) || is_null($check->id) || $check->studentcomments == ""){
            return "";
        }
        return $check->studentcomments;
    }
    
    /**
	 * Used to return the correct Unit Class.
	 * @param unknown_type $typeID
	 */
	private static function get_correct_unit_class($familyID = -1, $typeID = -1, 
            $unitID = -1, $params = null, $loadParams = Qualification::LOADLEVELALL)
	{	
                
		if($typeID != -1)
		{   
            //we need to know the family of the type, and get the folder where all of the classes belong
            $unitClass = Unit::get_plugin_class_type($typeID);
            if($unitClass)
            {
                return $unitClass::get_instance($unitID, $params, $loadParams);
            }
            return false;

		}
		elseif($familyID != -1)
		{
                
            //then we know the family (e.g. BTEC, Alevel or C&G)
            //so lets load the family from the database
            //its class and then load its static function of get_correct_unit_class
            $unitClass = Unit::get_plugin_class($familyID);
            if($unitClass)
            {
                return $unitClass::get_pluggin_unit_class($typeID, $unitID, 
                        $familyID, $params, $loadParams);
            }
            return false;
            
		}	
		return false;		
	}
    
    private static function get_plugin_class($familyID)
    {
        global $DB, $CFG;
        $sql = "SELECT * FROM {block_bcgt_type_family} WHERE id = ?";
        $class = $DB->get_record_sql($sql, array($familyID));
        if($class)
        {
            $folder = $CFG->dirroot.$class->classfolderlocation;
            $className = $class->family;
            if (is_dir($folder)) {
                $file = $folder.'/'.$className.'Unit.class.php';
                if(file_exists($file))
                {
                    include_once($file);
                    $class = $className.'Unit';
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
                $file = $folder.'/'.$className.'Unit.class.php';
                if(file_exists($file))
                {
                    include_once($file);
                    $class = $className.'Unit';
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
    
    /**
	 * Returns the unit from the database based on the id that has been set
	 * for this object. 
	 * @return Found
	 */
	private static function get_unit($id)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_unit} WHERE id = ?";
		return $DB->get_record_sql($sql, array($id));
	}
    
    /**
    * Get an actual list of rules from the DB
    */
    private static function get_rules_db($unitID)
    {
        global $DB;
        $check = $DB->get_records_select("block_bcgt_rule_links", array('unitid'=>$unitID));
        $result = array();
        if($check) foreach($check as $rule) $result[] = $rule->ruleid;
        return $result;
    }
    
    private static function retrieve_level($unitID)
	{
		global $DB;
		$sql = "SELECT unit.bcgtlevelid, level.trackinglevel 
		FROM {block_bcgt_unit} AS unit
		LEFT OUTER JOIN {block_bcgt_level} AS level ON level.id = unit.bcgtlevelid 
		WHERE unit.id = ?";
		return $DB->get_record_sql($sql, array($unitID));
	}
    
    /**
	 * Get the criteria from the database for this unit
	 * Loop through them all, create a criteria object for 
	 * the criteria and add it to an array and return that array
	 * @param unknown_type $unitID
	 */
	private static function set_up_criteria($unitID, $unitType, 
            $loadParams)
	{
        global $DB;
        $criteriaNames = array();
		$criteriaSet = Unit::retrieve_criteria($unitID);
		if($criteriaSet)
		{
			$criteriaArray = array();
			foreach($criteriaSet AS $criteria)
			{
                $criteriaNames[$criteria->name] = $criteria->name; 
                // get rules
                $criteriaObj = Criteria::get_correct_criteria_class($unitType,
                        $criteria->id, $criteria, $loadParams);
				if($criteriaObj)
                {
					$criteriaObj->set_up_tasks();
					$criteriaArray[$criteriaObj->get_id()] = $criteriaObj;
                }
			}
            $retval = new stdClass();
            $retval->criteria = $criteriaArray;
            $retval->names = $criteriaNames;
			return $retval;
		}
		return false;		
	}
    
    protected static function retrieve_criteria_flat($unitID)
    {
        
        global $DB;
        
        $records = $DB->get_records("block_bcgt_criteria", array("bcgtunitid" => $unitID));
        return $records;
        
    }
    
    /**
	 * Gets all of the criteria for this unit from the database
	 * @param unknown_type $unitID
	 */
	private static function retrieve_criteria($unitID)
	{
		global $DB;
		$sql = "SELECT c.*
        FROM {block_bcgt_criteria} c
        WHERE c.bcgtunitid = ? AND (c.parentcriteriaid IS NULL OR c.parentcriteriaid = ?)
        ORDER BY c.id ASC";
		$records = $DB->get_records_sql($sql, array($unitID, 0));
        
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
    
    /**
    * Set up an array of tasks on this unit (if any)
    * @param type $unitID 
    */
    private static function set_up_tasks($unitID)
    {
      global $DB;
       $return = array();
       $records = $DB->get_records_select("block_bcgt_task", 'bcgtunitid = ?', array($unitID), 'id ASC');
       if($records)
       {
           foreach($records as $record)
           {
               $return[] = new Task($record->id);
           }
       }
       return $return;
    }
    
    protected function get_single_criteria_from_arrays($criteriaArray, $criteriaID, $name)
	{
		$type = Criteria::criteria_type($criteriaID, $name, $this->id);     
		if($criteriaArray)
		{		
			foreach($criteriaArray AS $criteria)
			{
				if($type == 'Sub')
				{
					$subCriteria = $criteria->get_sub_criteria();
					if($subCriteria)
					{
						foreach($subCriteria AS $sub)
						{
							if((($criteriaID != -1) && ($sub->get_id() == $criteriaID)) || 
							($sub->get_name() == $name))
							{
								return $sub;
							}
						}
					}	
				}
				else
				{
					if((($criteriaID != -1) && ($criteria->get_id() == $criteriaID)) || 
					($criteria->get_name() == $name))
					{
						return $criteria;
					}
				}
			}
		}
		return false;
	}
    
    /**
	 * Inserts the criteria history
	 * @param unknown_type $criteriaID
	 */
	protected function insert_criteria_history($criteriaID = -1)
	{
		global $DB;
		$sql = "INSERT INTO {block_bcgt_criteria_his} 
		(bcgtcriteriaid, name, details, bcgttypeawardid, bcgtunitid) 
		SELECT id, name, details, bcgttypeawardid, bcgtunitid 
        FROM {block_bcgt_criteria} WHERE bcgtunitid = ? "; 
        $params = array($this->id);
		if($criteriaID != -1)
		{
			$sql .= " AND id = ?";
            $params[] = $criteriaID;
		}
		return $DB->execute($sql, $params);
	}
    
    /**
	 * Inserts the students unit hirstory
	 */
	private function insert_students_unit_history($qualID = -1, $studentID = -1)
	{
		global $DB;
		$sql = "INSERT INTO {block_bcgt_user_unit_his} 
		(bcgtuserunitid, userid, bcgtqualificationid, bcgtunitid, bcgttypeawardid, 
		comments, dateupdated, userdefinedvalue, bcgtvalueid, setbyuserid, updatedbyuserid, dateset, studentcomments) 
		SELECT * FROM {block_bcgt_user_unit} WHERE bcgtunitid = ?";
        $params = array($this->id);
        if($studentID != -1)
        {
            $sql .= " AND userid = ?";
            $params[] = $studentID;
        }
        if($qualID != -1)
        {
            $sql .= " AND bcgtqualificationid = ?";
            $params[] = $qualID;
        }
		return $DB->execute($sql, $params);
	}
    
    public static function insert_user_unit_history_by_id($id)
    {
        global $DB;
		$sql = "INSERT INTO {block_bcgt_user_unit_his} 
		(bcgtuserunitid, userid, bcgtqualificationid, bcgtunitid, bcgttypeawardid, 
		comments, dateupdated, userdefinedvalue, bcgtvalueid, setbyuserid, updatedbyuserid, dateset, studentcomments) 
		SELECT * FROM {block_bcgt_user_unit} WHERE id = ?";
        $params = array($id);
		return $DB->execute($sql, $params);
    }
    
    /**
	 * If the student is doing this unit then
	 * it can return the db record.
	 * @param unknown_type $qualID
	 * @return Found
	 */
	public function student_doing_unit($qualID)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_user_unit} 
		WHERE bcgtqualificationid = ? AND userid = ? AND
		bcgtunitid = ?";
		$records =  $DB->get_records_sql($sql, array($qualID, $this->studentID, $this->id));
        if($records)
        {
            return end($records);
        }
        return false;
	}
    
    /**
	 * This function gets the award given to the unit for this qualification, 
	 * unit and student.
	 */
	protected function retrieve_unit_award($qualID)
	{
		global $DB;
		$sql = "SELECT award.id, award.award, award.ranking, award.shortaward, unit.dateupdated 
            FROM {block_bcgt_user_unit} as unit
		JOIN {block_bcgt_type_award} AS award ON award.id = unit.bcgttypeawardid
		WHERE unit.bcgtqualificationid = ? AND unit.userid = ? AND 
		unit.bcgtunitid = ?";
		return $DB->get_record_sql($sql, array($qualID, $this->studentID, $this->id));
		
	}
    
    public function has_tasks(){
        return false;
    }
    
    public function has_sub_criteria(){
        return false;
    }
    
    public function display_percentage_completed()
    {       
        
        $percent = $this->get_percent_completed();

        $bar = '<div class="c"><small id="U'.$this->get_id().'S'.$this->studentID.'PercentText">'.$percent.'%</small></div>
                <div class="meter" id="U'.$this->get_id().'S'.$this->studentID.'PercentParent" title="'.$percent.'% Complete">
                        <span style="width:'.$percent.'%" id="U'.$this->get_id().'S'.$this->studentID.'PercentComplete"></span>
                </div>';

        return $bar;

    }
    
    public function get_percent_completed()
    {

        $subCriteria = $this->has_sub_criteria();
        $tasks = $this->has_tasks();
        $criteria = $this->criterias;

        if(!$criteria) return 0; # No criteria, so is it 0% complete or 100% complete? 0 will do

        // If has tasks, then we only want to count THEORY criteria and LINKS between practical criteria & tasks
        if($tasks)
        {

            $count = 0;

            foreach($criteria as $criterion)
            {
                if($criterion->get_type() == "theory")
                {
                    $count++;
                }
            }

            // Now count the links between practical criteria & tasks on this unit
            foreach($criteria as $criterion)
            {
                if($criterion->get_type() == "practical")
                {
                    $count += count($criterion->get_tasks());
                }
            }

            // Now we've counted everything, we need to find out how many of these are completed
            $numCompleted = $this->are_criteria_completed($criteria, $subCriteria, $tasks);
            $percent = round(($numCompleted * 100) / $count);                
            return $percent;

        }

        // First count how many criteria are on it
        $count = count($criteria);
        $numCompleted = 0;


        // If SubCriteria - add them to the count
        if($subCriteria)
        {

            foreach($criteria as $criterion)
            {
                $sub = $criterion->get_sub_criteria();
                $count += count($sub);
            }

        }

        $numCompleted += $this->are_criteria_completed($criteria, $subCriteria);

        $percent = round(($numCompleted * 100) / $count);

        return $percent;

    }
    
    protected function are_criteria_completed($criteria, $sub = false)
    {
        if(!$criteria) return 0;

        $numCompleted = 0;

        foreach($criteria as $criterion)
        {
            
            $sID = $criterion->get_student_ID();
            if (is_null($sID)){
                $criterion->load_student_information($this->studentID, $this->qualID, $this->id);
            }
            
            $award = $criterion->get_student_value();
            if($award)
            {
                $met = $award->is_criteria_met();
                if($met == "Yes")
                {
                    $numCompleted++;
                }
            }

            // Sub Criteria
            if($sub && $subCriteria = $criterion->get_sub_criteria())
            {
                $numCompleted += $this->are_criteria_completed($subCriteria);
            }

        }

        return $numCompleted;

    }
    
    
    
    
    /*Static functions that the classes must implement!*/
    //public static abstract function get_instance($unitID, $params);
    //public static abstract function get_pluggin_unit_class($typeID, $unitID, $familyID, $params);
    //public static abstract function get_edit_form_menu($disabled, $unitID, $typeID);
    /*
	 * Gets the associated Qualification ID
	 */
	abstract function get_typeID();
	
	/*
	 * Gets the name of the associated qualification. 
	 */
	abstract function get_type_name();
    
    /*
	 * Gets the name of the associated qualification family. 
	 */
	abstract function get_family_name();
	
	/**
	 * Get the family of the qual.
	 */
	abstract function get_familyID();
    
    
    /**
	 * Gets the form fields that will go on edit_unit_form.php
	 * They are different for each unit type
	 */
	abstract function get_edit_form_fields();
    
    public function has_unique_id()
    {
        return true;
    }
    
    /**
	 * Used in edit unit
	 * Gets the criteria tablle that will go on edit_unit_form.php
	 * This is different for each unit type. 
	 */
	abstract function get_edit_criteria_table();
    
    /**
	 * Used in edit unit
	 * Gets the submitted data from the edit form fields
	 * edit_unit_form.php
	 */
	abstract function get_submitted_edit_form_data();
    
    /**
	 * Used in edit unit
	 * Gets the submitted data from the criteria section of the edit form form.
	 * edit_unit_form.php
	 */
	abstract function get_submitted_criteria_edit_form_data();
    
    /**
	 * Inserts the unit AND the criteria and all related details
	 * Dont forget to set the id of the unit object
	 */
	abstract function insert_unit();
	
	/**
	 * Updates the unit AND the criteria and all related details
	 */
	abstract function update_unit($updateCriteria = true); 
    
    /**
	 * Certain qualificaton types have unit awards
	 */
	abstract function unit_has_award();
    
    /**
	 * Certain qualification types have unit awards.
	 */
	abstract function calculate_unit_award($qualID);
    
    /**
     * displays the unit grid. 
     */
    abstract function display_unit_grid();
    
    /**
     * Created on every non abstract class
     * returns the actual instance of the class
     */
    //abstract static function get_instance($unitID, $params, $loadParams);
    
    /**
     * On the family class. Used to work out which unit class of the 
     * family actually needs to be returns. 
     */
    //abstract static function get_pluggin_unit_class($typeID = -1, $unitID = -1, 
    //        $familyID = -1, $params = null, $loadLevel = Qualification::LOADLEVELUNITS);
    
    public function process_create_update_unit_form(){
        
        $name = optional_param('name', NULL, PARAM_TEXT);
        $unique = optional_param('unique', NULL, PARAM_TEXT);
        
        $name = trim($name);
        $unique = trim($unique);
        
        $this->processed_errors = '';
        
        // External Code
        if ($this->has_unique_id()){
            if (is_null($unique) || empty($unique)){
                $this->processed_errors .= get_string('error:uniquecode', 'block_bcgt') . '<br>';
            }
        }
        
        // Name
        if (is_null($name) || empty($name)){
            $this->processed_errors .= get_string('error:name', 'block_bcgt') . '<br>';
        }
        
        if (!empty($this->processed_errors)){
            return false;
        }
        
        $this->uniqueID = $unique;
        $this->name = $name;
        
        unset($this->processed_errors);
        
        return true;        
        
    }
    
    public function get_processed_errors(){
        return (isset($this->processed_errors)) ? $this->processed_errors : false;
    }
    
    public function print_grid($qualID){
        echo "Coming soon";
    }
    
    public function order_criteria_ids($criteria)
    {
        global $CFG, $DB;
        require_once $CFG->dirroot . '/blocks/bcgt/classes/sorters/CriteriaSorter.class.php';
        $sorter = new CriteriaSorter();
        usort($criteria, array($sorter, "ComparisonSimpleArray"));
        return $criteria;
    }
    
}



?>
