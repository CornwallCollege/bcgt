<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/bclib.php');
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/QualificationAward.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Award.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Value.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Unit.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Criteria.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Level.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/SubType.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/UnitSorter.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');

/**
 * NOTE!!!!:::
 * 
 * 
 * THIS and the other objects are hideous and a stupid way of doing it. And thats
 * coming from the original/main developer
 * 
 * We need to break them out into userqual object, dataqual object and interface qual object
 */



abstract class Qualification {
    
    const LOADLEVELMIN = 1;
    const LOADLEVELUNITS = 4;
    const LOADLEVELCRITERIA = 7;
    const LOADLEVELSUBCRITERIA = 7.5;
    const LOADLEVELALL = 10;
    const SUPPORTED_GRADE_SCALES = 'BCGT BTEC Scale (PMD)';
    
    protected $id;
	protected $name;
    protected $additionalName;
	protected $code;
    
	//should be an array containing the UnitClass
	protected $units = array();
	//should contain the level class
	protected $level;
	//should contain the subtype class.
	protected $subType;
    
    protected $bcgtTargetQualID;
    protected $noYears;
	
	//new attributes
	protected $studentID;
    protected $student;
	//should be of type class qualaward (this is their final)
    //either one award class or an object with all award classes on it. 
    //different for each qual type. 
	protected $studentAward;
	//should be of type class qualaward
    
	protected $predictedAward;
    protected $minAward;
    protected $maxAward;
    protected $comments;
        
    protected $assignmentID;
    protected $assignmentType;
    
    protected $weightings;
    protected $possibleValues;
    protected $targetGrades;
    
    protected $userTargetGrades;
    
    //optional:
    //this will exist for several different where the rold can change.
    //e.g. usersstudent or usersteacher
    protected $usersRole;
    //this is an array where the key is the userid and the value is the qual object
    //with the students data loaded if its a student.
    protected $usersQualsRole;
    //TODO : LoadParameters
    //eg. Load Single Unit Criteria = sdsada
    //e.g Load Unit Awards etc
    protected $studentFlag;
    //also when looping through the criteria and units load up the criteria names used.
    //$loadParams->loadLevel
    //$loadParams->loadUnit = unitID;
    //$loadParams->loadCriteria = criteriaID;
    function Qualification($qualID = -1, $params = null, $loadParams = null){
        if($qualID != -1)
		{           
			//Then we have been given the id and nothing else. If id exists, qual has been created
			//before, therefore get from database
            $this->id = $qualID;
			//This gets the correct qualification class 
			$qualification = Qualification::get_qualification($qualID);
                        
			if($qualification)
			{
				$this->name = $qualification->name;
                $this->additionalName = $qualification->additionalname;
				$this->code = $qualification->code;
				$level = new Level($qualification->levelid, $qualification->level);
				$this->level = $level;
				$subType = new SubType($qualification->subtypeid, $qualification->subtype);
				$this->subType = $subType;
                $this->bcgtTargetQualID = $qualification->targetqualid;
                                
                if($loadParams && $loadParams->loadLevel && $loadParams->loadLevel >= Qualification::LOADLEVELUNITS)
                {
                    //right we want to load the units up as well.
                    $unitArray = array();
                    //go and get all of the units from the database for this
                    //qualidfication
                    $units = Qualification::retrieve_units($qualID);
                                        
                    if($units)
                    {
                        foreach($units AS $unit)
                        {
                            //create the correct type of unit. 
                            $unit = Unit::get_unit_class_id($unit->bcgtunitid, $loadParams);
                            if($unit)
                            {
                                $unitArray[$unit->get_id()] = $unit;
                            }
                        }
                    }
                    $this->units = $unitArray;
                }
			}
		}
		else
		{
			//Its either a brand new one -> therefore brand new fields
			//or its one that is being updated and therfore lets set the new valeus for the
			//fields 		
			//so set the fields to what is being passed in. 
			$this->id = $qualID;
            if($params)
            {
                if(isset($params->name))
                {
                    $this->name = $params->name;
                }
                if(isset($params->additionalName))
                {
                    $this->additionalName = $params->additionalName; 
                }
                if(isset($params->code))
                {
                    $this->code = $params->code; 
                }
                if(!is_object($params->level) && ($params->level != ''))
                {
                    //then go get the level based on the id passed in
                    $level = get_qualification_level($params->level);
                }
                elseif(is_object($params->level))
                {
                    $level = $params->level;
                }
                $this->level = $level;
                if(!is_object($params->subType) && ($params->subType != ''))
                {
                    $subType = get_qualification_subtype($subType);
                }
                else 
                {
                    $subType = $params->subType;
                }
                $this->subType = $subType;
            }
		} 
        $this->bcgtTargetQualID = $this->get_target_qual($this->get_class_ID());
        $this->noYears = $this->get_default_years($this->get_class_ID());
        
        //TODO load users
    }
    
    /**
	 * Returns the id of the qual (one stored in db)
	 */
	function get_id()
	{
		return $this->id;
	}
    
    function set_name($name)
	{
		$this->name = $name;
	}
	
	function get_name()
	{
		return $this->name;	
	}
    
    function set_additional_name($additionalName)
    {
        $this->additionalName = $additionalName;
    }
    
    function get_additional_name()
    {
        return $this->additionalName;
    }
	
	function set_code($code)
	{
		$this->code = $code;
	}
	
	function get_code()
	{
		return $this->code;
	}
	
	function add_level($levelID)
	{
		$this->level = get_qualification_level($levelID);
	}
		
	function set_level(Level $level)
	{
		$this->level = $level;	
	}
	
	function get_level()
	{
		return $this->level;	
	}
	
	function add_subType($subTypeID)
	{
		$this->subType = get_qualification_subtype($subTypeID);
	}
	
	function set_subType(SubType $subType)
	{
		$this->subType = $subType;
	}
	
	function get_subType()
	{
		return $this->subType;
	}
    
    function get_no_years()
    {
        if(isset($this->noYears))
        {
            return $this->noYears;
        }
        return 1;
    }   
    
	function set_no_years($noYears)
    {
        $this->noYears = $noYears;
    }
    
	function get_units()
	{
		return $this->units;	
	}
    
    public function get_ungrouped_units()
    {
        
        global $DB;
        
        $return = array();
        
        if ($this->units)
        {
            
            foreach($this->units as $unit)
            {
                
                $record = $DB->get_record("block_bcgt_qual_units", array("bcgtqualificationid" => $this->id, "bcgtunitid" => $unit->get_id()));
                                
                if ($record && is_null($record->groupname))
                {
                    
                    $return[$unit->get_id()] = $unit;
                    
                }
                
            }
            
        }
                
        
        return $return;
        
    }
    
    public function get_unit_groups()
    {
        
        global $DB;
        
        $groups = $DB->get_records_sql("SELECT DISTINCT groupname
                                        FROM {block_bcgt_qual_units}
                                        WHERE bcgtqualificationid = ? AND groupname IS NOT NULL
                                        ORDER BY groupname ASC", array($this->id));
        
        $return = array();
        
        if ($groups){
            
            
            foreach($groups as $group){
                
                $obj = new stdClass();
                $obj->name = $group->groupname;
                $obj->units = $DB->get_records("block_bcgt_qual_units", array("bcgtqualificationid" => $this->id, "groupname" => $group->groupname));
                $return[] = $obj;
                
            }
            
        }
        
        return $return;
        
    }
    
    function get_unit($unitID){
        return (isset($this->units[$unitID])) ? $this->units[$unitID] : false;
    }
    
    function set_units($units){
        $this->units = $units;
    }
    
    //Gets a single unit from the array class by id. 
	function get_single_unit($unitID)
	{
		if($this->units && isset($this->units[$unitID]))
		{
			return $this->units[$unitID];
		}
        
        return false;
        
	}
    
    function update_single_unit($unit)
    {
        if($this->units)
        {
            $this->units[$unit->get_id()] = $unit;
        }
    }
    
    function get_studentID()
    {
        return $this->studentID;
    }
    
    function get_student()
    {
        return $this->student;
    }

    function get_student_award()
    {
        return $this->studentAward;
    }
    
    function load_student()
    {
        global $DB;
        $this->student = $DB->get_record("user", array("id" => $this->studentID));
    }
    
    public function get_family_instance_id()
    {
        return -1;
    }
        
    /**
	 * Used to return a list of all unit id's that this qualification has
	 * where the returned string is : '(id,id,id,id)'
	 * False if there are no units on this qual
	 */
	public function generate_sql_unit_in()
	{
		$in = '(';
		$found = false;
		foreach($this->units AS $unit)
		{	
			$found = true;
			$in .= $unit->get_id().",";	
		}
		$in = substr($in, 0, -1);
		$in .= ')';
		
		if($found)
		{
			return $in;
		}
		return false;
	}
    
    public function get_alps_multiplier()
    {
        return 0;
    }
    
    /**
     * Get the display name for the qual
	 * This is level, type, subtype and name 
     * @param type $long
     * @param type $seperator
     * @param type $exclusions
     * @param type $returnType
     * @return type
     */
	public function get_display_name($long = true, $seperator = ' ', $exclusions = array(), $returnType = 'String')
	{
        $qual = new stdClass();
        $qual->family = $this->get_family();
        $qual->trackinglevel = $this->level->get_level();
        $qual->subtype = $this->subType->get_subtype();
        $qual->subtypeshort = $this->subType->get_short_subtype();
        $qual->name = $this->name;
        $qual->additionalname = $this->additionalName;
        $qual->levelid = $this->level->get_id();
        return bcgt_get_qualification_display_name($qual, $long, $seperator, $exclusions, $returnType);
	}
    
    
    function set_student_flag($flag)
    {
        $this->studentFlag = $flag;
    }
    
    function set_student_id($studentID)
    {
        $this->studentID = $studentID;
    }
        
    /**
     * Same as above, but with max characters of 31 - for excel sheet titles
     */
    function get_short_display_name()
    {
        $output = "";
        if($this->level) $output .= "L" . preg_replace("/\D/", "", $this->level->get_level()) . " ";
        if($this->subType) $output .= $this->subType->get_short_sub_type() . " ";
        $output .= $this->name;

        if(strlen($output) > 31) $output = substr ($output, 0, 29) . "..";

        return $output;
    }
    
    /**
	 * Counts the number of units on the qual object
	 */
	public function count_units()
	{
		if(empty($this->units))
		{
			return 0;
		}
		return count($this->units);
	}
    
    public function get_target_qual_id(){
        return $this->bcgtTargetQualID;
    }
    
    /**
     * 
     * @global type $DB
     * @param string $search
     * @param string $sort
     * @param type $courseID
     * @return type
     */
    public function get_students($search = '', $sort = '', $courseID = -1, $onCourse = true, $groupingID = -1)
    {
        $this->oldStudents = array();
        if($onCourse && !isset($this->students) || $onCourse && $this->students == null || !$onCourse)
        {
            //so if we are checking if they are on the course and we havent loaded the students
            //OR
            //if we are not checking if its on the course.
            global $DB;
            $users = array();
            $studentRole = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = ?', array('student'));
            if($studentRole)
            {
                $users = $this->get_users($studentRole->id, $search, $sort, $courseID, $onCourse, $groupingID);
                if($onCourse)
                {
                    $this->students = $users;
                }
                else
                {
                    $this->oldStudents = $users;
                }
                return $users;
            }
            return $users;
        }
        else
        {
            return $this->students;
        }
    }
    
    /**
     * 
     * @global type $DB
     * @param string $search
     * @param string $sort
     * @param type $courseID
     * @return type
     */
    public function get_teachers($search = '', $sort = '', $courseID = -1)
    {
        global $DB;
        $teacherRole = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = ?', array('teacher'));
        if($teacherRole)
        {
            return $this->get_users($teacherRole->id, $search, $sort, $courseID);
        }
    }
    
    /**
     * Returns the users quals array that contains the loaded objects/
     * @param type $role
     * @return boolean
     */
    public function get_loaded_users($role = 'student')
    {
        $usersQuals = 'usersQuals'.$role;
        if(isset($this->$usersQuals))
        {
            return $this->$usersQuals;
        }
        return false;
    }
    
    public function set_users_objects($role = 'student', $usersArray = array())
    {
        $usersQuals = 'usersQuals'.$role;
        $this->$usersQuals = $usersArray;
    }
    
    /**
     * Gets the users that are on this qual (if course ID passed in it gets
     * the users on the courses)
     * @global type $DB
     * @param type $roleID
     * @param type $search
     * @param type $sort
     * @param type $courseID
     * @return type
     */
    public function get_users($roleID, $search = '', $sort = '', $courseID = -1, $onCourse = true, $groupingID = -1)
    {
        global $DB;
        $sql = "SELECT distinct(u.id), u.* FROM {block_bcgt_user_qual} userQual
            JOIN {user} u ON u.id = userQual.userid";
        $params = array();
        if($courseID != -1 && $courseID != SITEID)
        {
            $sql .= " JOIN {role_assignments} roleass ON roleass.userid = u.id 
                JOIN {context} context ON context.id = roleass.contextid 
                JOIN {course} course ON course.id = context.instanceid";
            if($courseID != -1 && $onCourse)
            {
                $sql .= " AND course.id = ?";
                $params[] = $courseID;
            }
            elseif($courseID != -1 && !$onCourse)
            {
                $sql .= " AND course.id != ?";
                $params[] = $courseID;
            }
        }
        if($groupingID != -1)
        {
            $sql .= ' JOIN {groups_members} members ON members.userid = user.id 
                JOIN {groupings_groups} gg ON gg.groupid = members.groupid';
        }
        $sql .= " WHERE userQual.bcgtqualificationid = ? AND userQual.roleid = ? 
            AND u.deleted != ?";
        $params[] = $this->id;
        $params[] = $roleID;
        $params[] = 1;
        if($search != '')
        {
            $sql .= ' AND (';
            $sql .= bcgt_student_search_db($search, $params);
            
//            $sql .= " AND (user.firstname LIKE ? OR user.lastname LIKE ? 
//                OR user.email LIKE ? OR user.username LIKE ? ";
//            $params[] = '%'.$search.'%';
//            $params[] = '%'.$search.'%';
//            $params[] = '%'.$search.'%';
//            $params[] = '%'.$search.'%';
//            $searchSplit = explode(' ', $search);
//            if($searchSplit)
//            {
//                foreach($searchSplit AS $split)
//                {
//                    $sql .= ' OR user.firstname LIKE ? OR user.lastname LIKE ? 
//                OR user.email LIKE ? OR user.username LIKE ? ';
//                    $params[] = '%'.$split.'%';
//                    $params[] = '%'.$split.'%';
//                    $params[] = '%'.$split.'%';
//                    $params[] = '%'.$split.'%';
//                }
//            }
            $sql .= ')';
        }
        if($courseID != -1 && !$onCourse)
        {
            //and where the userid are not the users that are actually on the course that this qual is on. 
            $sql .= " AND u.id NOT IN 
                (
                    SELECT roleass.userid FROM {role_assignments} roleass  
                    JOIN {context} context ON context.id = roleass.contextid 
                    JOIN {course} course ON course.id = context.instanceid
                    JOIN {role} role ON role.id = roleass.roleid
                    WHERE role.shortname = ? AND course.id = ?
                )";
            $params[] = 'student';
            $params[] = $courseID;
        }
        if($groupingID != -1)
        {
            $sql .= ' AND gg.groupingid = ?';
            $params[] = $groupingID;
        }
        if($sort != '')
        {
            $sql .= ' ORDER BY '.$sort;
        }
        else
        {
            $sql .= ' ORDER BY u.lastname ASC, u.firstname ASC';
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * 
     * @param type $userIDs
     * @param type $roleID
     * @param type $addToUnits
     */
    public function add_users($userIDs, $roleID, $addToUnits = false)
    {
        foreach($userIDs AS $userID)
        {
            $this->add_user_to_qual($userID, $roleID, $addToUnits);
        }
        
    }
    
    /**
     * Family|Level|SubType|Name|AdditionalName|code|credits|noyears
     * @param type $csvRow
     */
    public static function insert_from_csv($csvRow)
    {
        //need to calculate the targetqualid
        //type, level and subtype
        //we will be given the family. 
        
        //need to get the familyid from this
        //need to get the levelid from the level
        //need to get the subtype from the subtype
        
        //name and additional name. 
        $family = $csvRow[0];
        $level = $csvRow[1];
        $subtype = $csvRow[2];
        $name = $csvRow[3];
        $additionalName = (isset($csvRow[4])? $csvRow[4] : '');
        $code = (isset($csvRow[5])? $csvRow[5] : '');
        $credits = (isset($csvRow[6])? $csvRow[6] : '');
        $noYears = (isset($csvRow[7])? $csvRow[7] : '');
 
        $familyID = bcgt_get_qualification_family_ID($family);
        $levelID = Level::get_levelID_by_level($level);
        $subTypeID = SubType::get_subtypeID_by_subtype($subtype);
        
        //check if targetqual actually exists for this combo!
        $targetQual = check_target_qual_exists($familyID, -1, $subTypeID, $levelID);
        if(!$targetQual)
        {
            return false;
        }
        
        $params = new stdClass();
        $params->level = $levelID;
        $params->subtype = $subTypeID;
        $params->name = $name;
        $params->additionalname = $additionalName;
        $params->code = $code;
        $params->credits = $credits;
        $params->noyears = $noYears;
        $loadParams = new stdClass();
        $qualification = Qualification::get_correct_qual_class(-1, -1, $familyID, $params, $loadParams);
        if($qualification)
        {
            return $qualification->insert_qualification();
        }
    }
    
    public static function has_formal_assessments()
    {
        return false;
    }
    
    public static function get_target_qual_by_qualID($qualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_qualification} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($qualID));
        if($record)
        {
            return $record->bcgttargetqualid;
        }
        return -1;
    }

    public function get_formal_assessments()
    {
        //does this qualification instance have formal assessments?
        return Project::project_exist_for_qual($this->id);
    }
    
    public function save_user_project_row($studentID, $projects)
    {
        if($projects)
        {
            foreach($projects AS $project)
            {
                if($project->project_on_qual($this->id))
                {
                    $project->load_student_information($studentID, $this->id);
                    $comments = $project->get_user_comments();
                    $project->set_student($studentID);
                    $valueID = -1;
                    $targetGradeID = -1;
                    //there is a value and a ceta (targetgrade)
                    $saveValue = false;
                    $saveTarget = false;
                    $saveComments = false;
                    if(isset($_POST['sID_'.$studentID.'_qID_'.$this->id.'_pID_'.$project->get_id().'_v']))
                    {
                        $saveValue = true;
                        $valueID = $_POST['sID_'.$studentID.'_qID_'.$this->id.'_pID_'.$project->get_id().'_v'];
                    }
                    if(isset($_POST['sID_'.$studentID.'_qID_'.$this->id.'_pID_'.$project->get_id().'_c']))
                    {
                        $saveTarget = true;
                        $targetGradeID = $_POST['sID_'.$studentID.'_qID_'.$this->id.'_pID_'.$project->get_id().'_c'];
                    }
                    if(isset($_POST['sID_'.$studentID.'_qID_'.$this->id.'_pID_'.$project->get_id().'_com']))
                    {
                        $saveComments = true;
                        $comments = $_POST['sID_'.$studentID.'_qID_'.$this->id.'_pID_'.$project->get_id().'_com'];
                    }
                    //now need to update or insert.
                    if($saveValue)
                    {
                        $project->set_user_value($valueID);
                    }
                    if($saveTarget)
                    {
                        $project->set_user_target_grade($targetGradeID);
                    }
                    if($saveComments)
                    {
                        $project->set_user_comments($comments);
                    }
                    if($saveValue || $saveTarget || $saveComments)
                    {
                        $project->save_user_values($this->id, true);
                    }
                }

            }
        }
    }
    
    public static function does_user_have_access($userID, $qualID)
    {
        global $DB;
        $sql = "SELECT role.shortname FROM {block_bcgt_user_qual} userqual 
            JOIN {role} role ON role.id = userqual.roleid 
            WHERE userid = ? AND bcgtqualificationid = ?";
        $records = $DB->get_records_sql($sql, array($userID, $qualID));
        if($records)
        {
            $retval = '';
            foreach($records AS $record)
            {
                $retval .= $record->shortname.'|';
            }
            return $retval;
        }
        return false;
    }
    
    public static function get_all_quals_on_grouping($groupingID)
    {
        global $DB;
        $sql = "SELECT distinct(qual.id), qual.* FROM {block_bcgt_qualification} qual
            JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qual.id 
            JOIN {groups_members} members ON members.userid = userqual.userid 
            JOIN {groupings_groups} gg ON gg.groupid = members.groupid
            WHERE gg.groupingid = ?";
        return $DB->get_records_sql($sql, array($groupingID));
    }
    
    public static function activity_grade_tracker($courseID)
    {
        //need to get each qualification that is on the grade tracker. 
        //for each go and get the activity grade tracker
        $retval = '';
        if($courseID && $courseID != 1)
        {
            //go and get all of the quals.
            global $CFG;
            $context = context_course::instance($courseID);
            $aID = optional_param('aID', 0, PARAM_INT);
            $showAll = optional_param('showall', false, PARAM_BOOL);
            //get the quals
            $quals = bcgt_get_course_quals($courseID);
            if($quals)
            {
                $activities = bcgt_get_activities_on_course($courseID);
                $retval .= '<form name="activitytrackers" method="POST" action="#"/>';
                $retval .= '<div id="atcivityselector">';
                $retval .= '<label for="aID">'.get_string('activity','block_bcgt').' : </label>';
                $retval .= '<select name="aID">';
                $retval .= '<option value="0"></option>';
                if($activities)
                {
                    foreach($activities AS $activity)
                    {
                        $activityDetails = bcgt_get_module_from_course_mod($activity->id);
                        if($activityDetails)
                        {
                            $selected = '';
                            if($aID == $activity->id)
                            {
                                $selected = 'selected';
                            }
                            $retval .= '<option '.$selected.' value="'.$activity->id.'">'.$activityDetails->name.'</option>';
                        }
                    }
                }
                $retval .= '</select>';
                $retval .= '<label for="showall">'.get_string('showall', 'block_bcgt').' : </label>';
                $checked = '';
                if($showAll)
                {
                    $checked = 'checked="checked"';
                }
                $retval .= '<input type="checkbox" name="showall" '.$checked.'/>';
                $retval .= '<input type="submit" name="show" value="'.get_string('show', 'block_bcgt').'"/>';
                $retval .= '</div>';
                foreach($quals AS $qual)
                {   
                    $qualification = Qualification::get_qualification_class_id($qual->id);
                    if($qualification)
                    {
                        if($showAll)
                        {
                            $activityShow = -1;
                        }
                        else
                        {
                            $activityShow = $aID;
                        }
                        $retval .= $qualification->get_activities_grid($courseID, $activityShow);
                    }
                }
                $retval .= '</form>';
            }
        }
        return $retval;
    }
    
    public function get_activities_grid($courseID, $activityID = -1)
    {
        return "";
    }
    
    public function save_qual_user_projects()
    {
        //loop over the users, 
        //loop over the projects
        $users = $this->get_students();
        $projects = $this->get_projects();
        foreach($users AS $user)
        {
            foreach($projects AS $project)
            {
                $project->set_student($user->id);
                $project->save_student($this->id);
            }
        }
    }
    
    public function get_projects($projectID = -1)
    {
        global $CFG;
        if(!isset($this->projects) || $this->projects == null)
        {
            $project = new Project();
            $projects = $project->get_qual_assessments($this->id, $projectID);
            $this->projects = $projects;
        }
        $projects = $this->projects;
        if($projects)
        {
            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ProjectsSorter.class.php');
            $projectSorter = new ProjectsSorter();
            usort($projects, array($projectSorter, "ComparisonDelegateByObjectDueDate"));
            $this->projects = $projects;
            return $this->projects;
        }
        return false;
    }
    
    
    public function get_user_ceta_alps_temp($userID, $showCoefficient = false)
    {
        //need to get the ucas target points
        //need to get the latest ceta points.
        
        //this is the latest CETA: 
        $cetaUcas = null;
        $targetGradeUcasPoints = null;
        $ceta = $this->get_current_ceta($this->id, $userID);
        if($ceta && $ceta->grade)
        {
            $cetaUcas = $ceta->ucaspoints;
        }
        else
        {
            $cetas = $this->get_most_recent_ceta($this->id, $userID);
            if($cetas)
            {
                $ceta = end($cetas);
                $cetaUcas = $ceta->ucaspoints;
            }
            
        }
        
        //get the target grade ucas !NOT WEIGHTED!
        $userCourseTarget = new UserCourseTarget();
        $targetGrades = $userCourseTarget->retrieve_users_target_grades($userID, $this->id);
        if($targetGrades)
        {
            //this will return a list of object
            //each object is this users target grades for this qual 
            //(qual and user could be on more than one course) so it will in theory return
            //more than one
            //we are only interested in the first
            $targetGrade = end($targetGrades);
            if(isset($targetGrade->targetgrade))
            {
                $targetGradeRecord = $targetGrade->targetgrade;
                $targetGradeUcasPoints = $targetGradeRecord->get_ucas_points();
            }
        }
        $temp = '';
        if($cetaUcas && $targetGradeUcasPoints)
        {
            
            $qualWeighting = new QualWeighting();
            $multiplier = $qualWeighting->get_multiplier($this->bcgtTargetQualID);
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_students_alps_report($targetGradeUcasPoints, $cetaUcas, $this->id, $showCoefficient);
        }
        return $temp;
    }
    
    public function get_user_all_alps_temp($userID)
    {
        //get all of the users quals.
        $retval = '';
        $totalCeta = 0;
        $cetaCount = 0;
        $totalFa = 0;
        $faCount = 0;
        $quals = $this->get_users_alevel_quals($userID);
        if($quals)
        {
            foreach($quals AS $qual)
            {
                $qualification = Qualification::get_qualification_class_id($qual->id);
                if($qualification)
                {
                    $cetaTemp = $qualification->get_user_ceta_alps_temp($userID);
                    if($cetaTemp)
                    {
                        $totalCeta = $totalCeta + $cetaTemp;
                        $cetaCount++;
                    }
                    $faTemp = $qualification->get_user_fa_alps_temp($userID);
                    if($faTemp)
                    {
                        $totalFa = $totalFa + $faTemp;
                        $faCount++;
                    }
                }
            }
        }
        if($faCount != 0)
        {
            $retval .= floor($totalFa/$faCount);
        }
        $retval .= '/';
        if($cetaCount != 0)
        {
            $retval .= floor($totalCeta/$cetaCount);
        }
        return $retval;
        
    }
    
    public function get_user_fa_ind_alps_temp($userID, $projectID, $showCoefficient = false)
    {
        $faUcas = null;
        $targetGradeUcasPoints = null;
        //this needs to get the target ucas
        //then needs to get the fa for the project passed in
        //get the target grade ucas !NOT WEIGHTED!
        $userCourseTarget = new UserCourseTarget();
        $targetGrades = $userCourseTarget->retrieve_users_target_grades($userID, $this->id);
        if($targetGrades)
        {
            //this will return a list of object
            //each object is this users target grades for this qual 
            //(qual and user could be on more than one course) so it will in theory return
            //more than one
            //we are only interested in the first
            $targetGrade = end($targetGrades);
            if(isset($targetGrade->targetgrade))
            {
                $targetGradeRecord = $targetGrade->targetgrade;
                $targetGradeUcasPoints = $targetGradeRecord->get_ucas_points();
            }
        }
        
        $project = new Project($projectID);
        $project->load_student_information($userID, $this->id);
        $userValue = $project->get_user_value();
        if($userValue && $userValue->get_value())
        {
            $targetGrade = new TargetGrade();
            $targetGradeObj = $targetGrade->retrieve_target_grade(-1, $this->bcgtTargetQualID, $userValue->get_value());
            if($targetGradeObj)
            {
                $faUcas = $targetGradeObj->get_ucas_points();
            }
        }
        
        $temp = '';
        if($faUcas && $targetGradeUcasPoints)
        {
            $qualWeighting = new QualWeighting();
            $multiplier = $qualWeighting->get_multiplier($this->bcgtTargetQualID);
            
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_students_alps_report($targetGradeUcasPoints, $faUcas, $this->id, $showCoefficient);
        }
        return $temp;
    }
    
    public function get_user_ceta_ind_alps_temp($userID, $projectID, $showCoefficient = false)
    {
        $cetaUcas = null;
        $targetGradeUcasPoints = null;
        //this needs to get the target ucas
        //then needs to get the ceta for the project passed in
        //get the target grade ucas !NOT WEIGHTED!
        $userCourseTarget = new UserCourseTarget();
        $targetGrades = $userCourseTarget->retrieve_users_target_grades($userID, $this->id);
        if($targetGrades)
        {
            //this will return a list of object
            //each object is this users target grades for this qual 
            //(qual and user could be on more than one course) so it will in theory return
            //more than one
            //we are only interested in the first
            $targetGrade = end($targetGrades);
            if(isset($targetGrade->targetgrade))
            {
                $targetGradeRecord = $targetGrade->targetgrade;
                $targetGradeUcasPoints = $targetGradeRecord->get_ucas_points();
            }
        }
        
        $project = new Project($projectID);
        $project->load_student_information($userID, $this->id);
        $targetGrade = $project->get_user_grade();
        if($targetGrade)
        {
            $cetaUcas = $targetGrade->get_ucas_points();
        }    
        
        $temp = '';
        if($cetaUcas && $targetGradeUcasPoints)
        {
            $qualWeighting = new QualWeighting();
            $multiplier = $qualWeighting->get_multiplier($this->bcgtTargetQualID);
            
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_students_alps_report($targetGradeUcasPoints, $cetaUcas, $this->id, $showCoefficient);
        }
        return $temp;
    }
    
    public function get_user_gbook_alps_temp($userID, $gradeBookID, $courseID, $showCoefficient = false)
    {
        $gradeBookUcas = null;
        $targetGradeUcasPoints = null;
        //this needs to:
        //this needs to get the target ucas
        //then needs to get the fa for the project passed in
        //get the target grade ucas !NOT WEIGHTED!
        $userCourseTarget = new UserCourseTarget();
        $targetGrades = $userCourseTarget->retrieve_users_target_grades($userID, $this->id);
        if($targetGrades)
        {
            //this will return a list of object
            //each object is this users target grades for this qual 
            //(qual and user could be on more than one course) so it will in theory return
            //more than one
            //we are only interested in the first
            $targetGrade = end($targetGrades);
            if(isset($targetGrade->targetgrade))
            {
                $targetGradeRecord = $targetGrade->targetgrade;
                $targetGradeUcasPoints = $targetGradeRecord->get_ucas_points();
            }
        }
        //now get the grade from the gradeBook thing. 
        $this->load_student_information($userID);
        $grade = $this->get_user_course_gradebook_values($courseID, $gradeBookID);
        if($grade)
        {
            
            if($grade->scale)
            {
                $scale = $grade->scale;
                $scales = explode(",",$scale);
                if($grade->finalgrade)
                {
                    //the array will start at 0. 
                    $gridGrade = $scales[($grade->finalgrade - 1)];
                    //if we have it then try and find it in the grade so we 
                    //can test it against the targetgrade
                    $gridGradeObj = TargetGrade::get_obj_from_grade($gridGrade, -1, $this->bcgtTargetQualID);
                    if($gridGradeObj)
                    {
                        $gradeBookUcas = $gridGradeObj->get_ucas_points();
                    }
                }
            }
        }
        
        
        $temp = '';
        if($gradeBookUcas && $targetGradeUcasPoints)
        {
            $qualWeighting = new QualWeighting();
            $multiplier = $qualWeighting->get_multiplier($this->bcgtTargetQualID);
            
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_students_alps_report($targetGradeUcasPoints, $gradeBookUcas, $this->id, $showCoefficient);
        }
        return $temp;
        
    }
    
    public function get_class_fa_ind_alps_temp($projectID, $groupID, $showCoefficient = false)
    {
        //gets the overall class Alps score.
        //need to find all of the students on that class/group 
        //that are doing this fa. 
        
        //then find the total number of target ucas points (dont forget to count the number of students WITH these target points)
        
        //for each one of these students get the fa grade -> ucas points
        $usersUcas = $this->get_users_and_ucas_points($groupID);
        $totalFaUcas = 0;
        $totalTargetUcas = 0;
        $userCount = 0;
        if($usersUcas)
        {
            foreach($usersUcas AS $user)
            {
                //we have their target ucas points
                //can we get formal assessment grade ucas points?
                $project = new Project($projectID);
                $project->load_student_information($user->userid, $this->id);
                $userValue = $project->get_user_value();
                if($userValue && $userValue->get_value())
                {
                    $targetGrade = new TargetGrade();
                    $targetGradeObj = $targetGrade->retrieve_target_grade(-1, $this->bcgtTargetQualID, $userValue->get_value());
                    if($targetGradeObj && $targetGradeObj->get_grade() && $targetGradeObj->get_grade() != '')
                    {
                        $faUcas = $targetGradeObj->get_ucas_points();
                        $totalFaUcas = $totalFaUcas + $faUcas;
                        $totalTargetUcas = $totalTargetUcas + $user->ucaspoints;
                        $userCount++;
                    }
                }
            }
        }
        $temp = '';
        if($totalFaUcas != 0 && $totalTargetUcas != 0 && $userCount != 0)
        {
            $qualWeighting = new QualWeighting();
            $multiplier = $qualWeighting->get_multiplier($this->bcgtTargetQualID);
            
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_class_alps_report($totalTargetUcas, $totalFaUcas, $this->id, $userCount, $showCoefficient);
        }
        return $temp;
    }
    
    public function get_class_gbook_alps_temp($assID, $courseID, $groupID = -1, $showCoefficient = false)
    {
        $usersUcas = $this->get_users_and_ucas_points($groupID);
        $totalGBUcas = 0;
        $totalTargetUcas = 0;
        $userCount = 0;
        if($usersUcas)
        {
            foreach($usersUcas AS $user)
            {
                //we have their target ucas points
                //can we get grade book grade ucas points?
                $this->load_student_information($user->userid);
                $grade = $this->get_user_course_gradebook_values($courseID, $assID);
                if($grade)
                {
                    if($grade->scale)
                    {
                        $scale = $grade->scale;
                        $scales = explode(",",$scale);
                        if($grade->finalgrade)
                        {
                            //the array will start at 0. 
                            $gridGrade = $scales[($grade->finalgrade - 1)];
                            //if we have it then try and find it in the grade so we 
                            //can test it against the targetgrade
                            $gridGradeObj = TargetGrade::get_obj_from_grade($gridGrade, -1, $this->bcgtTargetQualID);
                            if($gridGradeObj)
                            {
                                $gradeBookUcas = $gridGradeObj->get_ucas_points();
                                $totalGBUcas = $totalGBUcas + $gradeBookUcas;
                                $totalTargetUcas = $totalTargetUcas + $user->ucaspoints;
                                $userCount++;
                            }
                        }
                    }
                }
            }
        }
        $temp = '';
        if($totalGBUcas != 0 && $totalTargetUcas != 0 && $userCount != 0)
        {
            $qualWeighting = new QualWeighting();
            $multiplier = $qualWeighting->get_multiplier($this->bcgtTargetQualID);
            
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_class_alps_report($totalTargetUcas, $totalGBUcas, $this->id, $userCount, $showCoefficient);
        }
        return $temp;
    }
    
    public function get_class_ceta_ind_alps_temp($projectID, $groupID, $showCoefficient = false) 
    {
        $usersUcas = $this->get_users_and_ucas_points($groupID);
        $totalCetaUcas = 0;
        $totalTargetUcas = 0;
        $userCount = 0;
        if($usersUcas)
        {
            foreach($usersUcas AS $user)
            {
                //we have their target ucas points
                //can we get formal assessment grade ucas points?
                $project = new Project($projectID);
                $project->load_student_information($user->userid, $this->id);
                $targetGrade = $project->get_user_grade();
                if($targetGrade && $targetGrade->get_grade() && $targetGrade->get_grade() != '')
                {
                    $cetaUcas = $targetGrade->get_ucas_points();
                    $totalCetaUcas = $totalCetaUcas + $cetaUcas;
                    $totalTargetUcas = $totalTargetUcas + $user->ucaspoints;
                    $userCount++;
                }   
            }
        }
        $temp = '';
        if($totalCetaUcas != 0 && $totalTargetUcas != 0 && $userCount != 0)
        {
            $qualWeighting = new QualWeighting();
            $multiplier = $qualWeighting->get_multiplier($this->bcgtTargetQualID);
            
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_class_alps_report($totalTargetUcas, $totalCetaUcas, $this->id, $userCount, $showCoefficient);
        }
        return $temp; 
    }
    
    public function get_class_alps_temp($groupID, $showCoefficient = false)
    {
        $usersUcas = $this->get_users_and_ucas_points($groupID);
        $totalCetaUcas = 0;
        $totalTargetUcas = 0;
        $userCount = 0;
        if($usersUcas)
        {
            if(get_config('bcgt', 'aleveluseceta'))
            {
                foreach($usersUcas AS $user)
                {
                    //ceta:
                    $cetaUcas = 'X';
                    $ceta = $this->get_current_ceta($this->id, $user->userid);
                    if($ceta && $ceta->grade)
                    {
                        $cetaUcas = $ceta->ucaspoints;
                    }
                    else
                    {
                        $cetas = $this->get_most_recent_ceta($this->id, $user->userid);
                        if($cetas)
                        {
                            $ceta = end($cetas);
                            $cetaUcas = $ceta->ucaspoints;
                        }
                    }
                    if($cetaUcas != 'X' && ($user->ucaspoints && $user->ucaspoints != 0))
                    {
                        $totalCetaUcas = $totalCetaUcas + $cetaUcas;
                        $totalTargetUcas = $totalTargetUcas + $user->ucaspoints;
                        $userCount++; 
                    } 
                }
            }
        }
        $temp = '';
        if($totalCetaUcas != 0 && $totalTargetUcas != 0 && $userCount != 0)
        {
            $qualWeighting = new QualWeighting();
            $multiplier = $qualWeighting->get_multiplier($this->bcgtTargetQualID);
            
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_class_alps_report($totalTargetUcas, $totalCetaUcas, $this->id, $userCount, $showCoefficient);
        }
        return $temp;
    }
    
    public function get_class_alps_fa_temp($groupID, $showCoefficient = false)
    {
        $usersUcas = $this->get_users_and_ucas_points($groupID);
        $totalFAUcas = 0;
        $totalTargetUcas = 0;
        $userCount = 0;
        if($usersUcas)
        {
            if(get_config('bcgt', 'aleveluseceta'))
            {
                foreach($usersUcas AS $user)
                {
                    //ceta: 
                    $faUcas = 'X';
                    $fa = $this->get_current_fa_grade($this->id, $user->userid);
                    if($fa && $fa->shortvalue)
                    {
                        $shortValue = $fa->shortvalue;
                    }
                    else
                    {
                        $fas = $this->get_most_recent_fa_grade($this->id, $user->userid);
                        if($fas)
                        {
                            $fa = end($fas);
                            $shortValue = $fa->shortvalue;
                        }
                    }
                    if($shortValue)
                    {
                        $targetGrade = new TargetGrade();
                        $targetGradeObj = $targetGrade->retrieve_target_grade(-1, $this->bcgtTargetQualID, $shortValue);
                        if($targetGradeObj && $targetGradeObj->get_grade() && $targetGradeObj->get_grade() != '')
                        {
                            $faUcas = $targetGradeObj->get_ucas_points();
                        }
                    }

                    if($faUcas != 'X' && ($user->ucaspoints && $user->ucaspoints != 0))
                    {
                        $totalFAUcas = $totalFAUcas + $faUcas;
                        $totalTargetUcas = $totalTargetUcas + $user->ucaspoints;
                        $userCount++; 
                    }
                }
            }
        }
        $temp = '';
        if($totalFAUcas != 0 && $totalTargetUcas != 0 && $userCount != 0)
        {
            $qualWeighting = new QualWeighting();
            $multiplier = $qualWeighting->get_multiplier($this->bcgtTargetQualID);
            
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_class_alps_report($totalTargetUcas, $totalFAUcas, $this->id, $userCount, $showCoefficient);
        }
        return $temp;
    }
    
    public function get_users_and_ucas_points($groupID = -1)
    {
        global $DB;
        $sql = "SELECT distinct(usertrgts.id), user.id as userid, target.ucaspoints FROM {block_bcgt_target_grades} target 
            JOIN {block_bcgt_user_course_trgts} usertrgts ON usertrgts.bcgttargetgradesid = target.id
            JOIN {user} user ON user.id = usertrgts.userid
            JOIN {block_bcgt_qualification} qual ON qual.id = usertrgts.bcgtqualificationid
            JOIN {block_bcgt_user_qual} userquals ON userquals.userid = user.id AND userquals.bcgtqualificationid = qual.id
        ";
        if($groupID != -1)
        {
            $sql .= " JOIN {block_bcgt_course_qual} coursequals ON coursequals.bcgtqualificationid = qual.id
                JOIN {groups} g ON g.courseid = coursequals.courseid
                JOIN {groups_members} members ON members.userid = user.id AND members.groupid = g.id 
                JOIN {groupings_groups} gg ON gg.groupid = g.id ";
        }
        $sql .= " WHERE qual.id = ?";
        $params = array($this->id);
        if($groupID != -1)
        {
            $sql .= " AND gg.groupingid = ?";
            $params[] = $groupID;
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_users_and_ucas_points_family($family)
    {
        global $DB;
        $sql = "SELECT user.id, target.ucaspoints FROM {block_bcgt_target_grades} target 
            JOIN {block_bcgt_user_course_trgts} usertrgts ON usertrgts.bcgttargetgradesid = target.id
            JOIN {user} user ON user.id = usertrgts.userid
            JOIN {block_bcgt_qualification} qual ON qual.id = usertrgts.bcgtqualificationid
            JOIN {block_bcgt_user_qual} userquals ON userquals.userid = user.id AND userquals.bcgtqualificationid = qual.id
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid
            JOIN {block_bcgt_type_family} fam ON fam.id = type.bcgttypefamilyid
        ";
        $sql .= " WHERE family.family = ?";
        $params = array($family);
        return $DB->get_records_sql($sql, $params);
    }
    
    
    public function get_user_fa_alps_temp($userID, $showCoefficient = false)
    {
        //need to get the ucas target points
        //need to get the latest ceta points.
        
        //this is the latest CETA: 
        $faUcas = null;
        $targetGradeUcasPoints = null;       
        
        $fa = $this->get_current_fa_grade($this->id, $userID);
        if($fa&& $fa->value)
        {
            //then lets find the target grade that is this value
            $targetGrade = new TargetGrade();
            $targetGradeObj = $targetGrade->retrieve_target_grade(-1, $this->bcgtTargetQualID, $fa->value);
            if($targetGradeObj)
            {
                $faUcas = $targetGradeObj->get_ucas_points();
            }
        }
        else
        {
            $fas = $this->get_most_recent_fa_grade($this->id, $userID);
            if($fas)
            {
                $fa = end($fas);
                //then lets find the target grade that is this value
                $targetGrade = new TargetGrade();
                $targetGradeObj = $targetGrade->retrieve_target_grade(-1, $this->bcgtTargetQualID, $fa->value);
                if($targetGradeObj)
                {
                    $faUcas = $targetGradeObj->get_ucas_points();
                }
            }
        }
        
        //get the target grade ucas !NOT WEIGHTED!
        $userCourseTarget = new UserCourseTarget();
        $targetGrades = $userCourseTarget->retrieve_users_target_grades($userID, $this->id);
        if($targetGrades)
        {
            //this will return a list of object
            //each object is this users target grades for this qual 
            //(qual and user could be on more than one course) so it will in theory return
            //more than one
            //we are only interested in the first
            $targetGrade = end($targetGrades);
            if(isset($targetGrade->targetgrade))
            {
                $targetGradeRecord = $targetGrade->targetgrade;
                $targetGradeUcasPoints = $targetGradeRecord->get_ucas_points();
            }
        }
        
        $temp = '';
        if($faUcas && $targetGradeUcasPoints)
        {
            $qualWeighting = new QualWeighting();
            $multiplier = $qualWeighting->get_multiplier($this->bcgtTargetQualID);
            
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_students_alps_report($targetGradeUcasPoints, $faUcas, $this->id, $showCoefficient);
        }
        return $temp;
    }
    
    public static function get_overall_alps_temp($family, $typeID = -1)
    {
        //need to get all of the qualifications that are 
        //under this family:
        //for each get the temp.
        global $DB;
        //then average it
        $qualCount = 0;
        $totalTemp = 0;
        $familyObj = $DB->get_record_sql('SELECT * FROM {block_bcgt_type_family} family WHERE family.family = ?', array($family));
        if($familyObj)
        {
            $qualifications = search_qualification($typeID, -1, -1, '', $familyObj->id, null, -1, true, true);
            if($qualifications)
            {
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                foreach($qualifications AS $qual)
                {
                    $qualification = Qualification::get_qualification_class_id($qual->id, $loadParams);
                    $temp = $qualification->get_class_alps_temp(-1);
                    if($temp)
                    {
                        $totalTemp = $totalTemp + $temp;
                        $qualCount++;
                    }
                }
            }
        }
        $averageTemp = -1;
        if($qualCount != 0 && $totalTemp != 0)
        {
            $averageTemp = $totalTemp/$qualCount;
        }
        return floor($averageTemp);
    }
    
    public static function get_overall_alps_temp_fag($family, $assID, $typeID = -1)
    {
        //need to get all of the qualifications that are 
        //under this family:
        //for each get the temp.
        global $DB;
        //then average it
        $qualCount = 0;
        $totalTemp = 0;
        $familyObj = $DB->get_record_sql('SELECT * FROM {block_bcgt_type_family} family WHERE family.family = ?', array($family));
        if($familyObj)
        {
            $qualifications = search_qualification($typeID, -1, -1, '', $familyObj->id, null, -1, true, true);
            if($qualifications)
            {
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                foreach($qualifications AS $qual)
                {
                    $qualification = Qualification::get_qualification_class_id($qual->id, $loadParams);
                    $temp = $qualification->get_class_fa_ind_alps_temp($assID, -1);
                    if($temp)
                    {
                        $totalTemp = $totalTemp + $temp;
                        $qualCount++;
                    }
                }
            }
        }
        $averageTemp = -1;
        if($qualCount != 0 && $totalTemp != 0)
        {
            $averageTemp = $totalTemp/$qualCount;
        }
        return floor($averageTemp);
    }
    
    public static function get_overall_alps_temp_fac($family, $assID, $typeID = -1)
    {
        //need to get all of the qualifications that are 
        //under this family:
        //for each get the temp.
        global $DB;
        //then average it
        $qualCount = 0;
        $totalTemp = 0;
        $familyObj = $DB->get_record_sql('SELECT * FROM {block_bcgt_type_family} family WHERE family.family = ?', array($family));
        if($familyObj)
        {
            $qualifications = search_qualification($typeID, -1, -1, '', $familyObj->id, null, -1, true, true);
            if($qualifications)
            {
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                foreach($qualifications AS $qual)
                {
                    $qualification = Qualification::get_qualification_class_id($qual->id, $loadParams);
                    $temp = $qualification->get_class_ceta_ind_alps_temp($assID, -1);
                    if($temp)
                    {
                        $totalTemp = $totalTemp + $temp;
                        $qualCount++;
                    }
                }
            }
        }
        $averageTemp = -1;
        if($qualCount != 0 && $totalTemp != 0)
        {
            $averageTemp = $totalTemp/$qualCount;
        }
        return floor($averageTemp);
    }
    
    public static function get_quals_and_alsp_report($typeID)
    {
        global $CFG;
        $retval = '';
        
        $familyID = bcgt_get_familyID_from_typeID($typeID);
        
        $qualifications = search_qualification($typeID, 3, -1, '', $familyID, null, -1, true, true);
        if($qualifications)
        {
            $project = new Project();
            $projects = $project->get_all_projects($centrallyManaged = null);
            if($projects)
            {
                require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ProjectsSorter.class.php');
                $projectSorter = new ProjectsSorter();
                usort($projects, array($projectSorter, "CompareByDateCurrent"));
            }
            
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELMIN;
            foreach($qualifications AS $qual)
            {
                $qualification = Qualification::get_qualification_class_id($qual->id, $loadParams);
                //build a row. 
                //get the overall
                //get the formal assessments
                $retval .= '<tr class="expand" id="tE_qual_'.
                            $qualification->get_id().'_-1" type="qual" eID="'.$qualification->get_id().'" e2ID="-1">';
                $retval .= '<td class="subRow2">'.$qualification->get_display_name().'</td>';
                $temp = $qualification->get_class_alps_temp(-1);
                $retval .= '<td><span class="alpstemp alpstemp'.$temp.'">'.$temp.'</span></td>';
                //get the formal assessments
                foreach($projects AS $project)
                {
                    $temp2 = $qualification->get_class_fa_ind_alps_temp($project->get_id(), -1);
                    $retval .= '<td><span class="alpstemp alpstemp'.$temp2.'">'.$temp2.'</span></td>';
                    $temp3 = $qualification->get_class_ceta_ind_alps_temp($project->get_id(), -1);
                    $retval .= '<td><span class="alpstemp alpstemp'.$temp3.'">'.$temp3.'</span></td>';
                }
                $retval .= '<td><a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/class_grid.php?qID='.$qualification->get_id().'">'.get_string('classoverview','block_bcgt').'</a></td>';
                $retval .= '</tr>';
            }
        }
        return $retval;
    }
    
    public static function get_qual_alsp_group_report($qualID, $groupID = -1)
    {
        if($groupID != -1)
        {
            return Qualification::get_qual_alsp_users_report($qualID, $groupID);
        }
        global $CFG;
        $retval = '';
        //if groups:
        //then output the groups and also if there are students not in a group 'no group'
        if(get_config('bcgt','usegroupsingradetracker'))
        {
            $qualification = Qualification::get_qualification_class_id($qualID);
            
            $project = new Project();
            $projects = $project->get_all_projects($centrallyManaged = null);
            if($projects)
            {
                require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ProjectsSorter.class.php');
                $projectSorter = new ProjectsSorter();
                usort($projects, array($projectSorter, "CompareByDateCurrent"));
            }
            //does the qual have groups?
            $group = new Group();
            $groups = $group->get_groups_on_qual($qualification->get_id());
            if($groups)
            {
                foreach($groups AS $qualGroup)
                {
                    
                    $retval .= '<tr class="expand" id="tE_qual_'.
                            $qualification->get_id().'_'.
                            $qualGroup->id.'" type="qual" eID="'.$qualification->get_id().'" 
                                e2ID="'.$qualGroup->id.'">';
                    $retval .= '<td class="subRow3">'.$qualGroup->name.'</td>';
                    $temp = $qualification->get_class_alps_temp($qualGroup->id);
                    $retval .= '<td><span class="alpstemp alpstemp'.$temp.'">'.$temp.'</span></td>';
                    //get the formal assessments
                    foreach($projects AS $project)
                    {
                        $temp2 = $qualification->get_class_fa_ind_alps_temp($project->get_id(), $qualGroup->id);
                        $retval .= '<td><span class="alpstemp alpstemp'.$temp2.'">'.$temp2.'</span></td>';
                        $temp3 = $qualification->get_class_ceta_ind_alps_temp($project->get_id(), $qualGroup->id);
                        $retval .= '<td><span class="alpstemp alpstemp'.$temp3.'">'.$temp3.'</span></td>';
                    }
                    $retval .= '<td><a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/class_grid.php?qID='.$qualification->get_id().'&grID='.$qualGroup->id.'">'.get_string('groupoverview','block_bcgt').'</a></td>';
                    $retval .= '</tr>';
                }

            }
        }
        return $retval;
    }
    
    public static function get_qual_alsp_users_report($qualID, $groupingID = -1)
    {
        global $CFG;
        $qualification = Qualification::get_qualification_class_id($qualID);
        //get all of the users
        //for each do a row
        //output alps. 
        $retval = '';
        $students = $qualification->get_students('', 'lastname ASC, firstname ASC', -1, null, $groupingID);
        if($students)
        {
            $project = new Project();
            $projects = $project->get_all_projects($centrallyManaged = null);
            if($projects)
            {
                require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ProjectsSorter.class.php');
                $projectSorter = new ProjectsSorter();
                usort($projects, array($projectSorter, "CompareByDateCurrent"));
            }
            foreach($students AS $student)
            {
                $retval .= '<tr>';
                $retval .= '<td class="subRow4">'.$student->username.' : '.$student->firstname.' : '.$student->lastname.'</td>';
                $temp = $qualification->get_user_ceta_alps_temp($student->id);
                $retval .= '<td><span class="alpstemp alpstemp'.$temp.'">'.$temp.'</span></td>';
                //get the formal assessments
                foreach($projects AS $project)
                {
                    $temp2 = $qualification->get_user_fa_ind_alps_temp($student->id, $project->get_id());
                    $retval .= '<td><span class="alpstemp alpstemp'.$temp2.'">'.$temp2.'</span></td>';
                    $temp3 = $qualification->get_user_ceta_ind_alps_temp($student->id, $project->get_id());
                    $retval .= '<td><span class="alpstemp alpstemp'.$temp3.'">'.$temp3.'</span></td>';
                }
                $retval .= '<td><a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/student_grid.php?qID='.$qualification->get_id().'&sID='.$student->id.'">'.get_string('viewsimple','block_bcgt').'</a></td>';
                $retval .= '</tr>';
            }
        }
        return $retval;
    }
    
    
    public function display_qual_assessments($editing, $save, $projectID = -1, $view = '', $groupingID = -1)
    {
        global $COURSE, $CFG;
                        
        $fromPortal = (isset($_SESSION['pp_user'])) ? true : false;
        
        $courseID = optional_param('cID', -1, PARAM_INT);
        if($courseID != -1)
        {
            $courseContext = context_course::instance($courseID);
        }
        else
        {
            $courseContext = context_course::instance($COURSE->id);
        }
        $project = new Project();
        $projects = $this->get_projects($projectID);
        $users = $this->get_students('', 'lastname ASC, firstname ASC', -1, true, $groupingID);
        
        //TODO 
        $retval = '';
        //add the targetgrades in for the BTECS
        //add the possible values in as same as target grades
        
        //ability to view grid purely by seaching for a student
        $seeTargetGrade = false;
        if(has_capability('block/bcgt:viewtargetgrade', $courseContext))
        {
            $seeTargetGrade = true;
        }
        $seeWeightedTargetGrade = false;
        if( (has_capability('block/bcgt:viewweightedtargetgrade', $courseContext) || $fromPortal) && get_config('bcgt', 
                    'allowalpsweighting'))
        {
            $seeWeightedTargetGrade = true;
        }
        $seeBoth = false;
        if(has_capability('block/bcgt:viewbothweightandnormaltargetgrade', $courseContext))
        {
            $seeBoth = true;
        }
        $retval .= '<table>';
        $link = '';
        if($view == 'qg')
        {
            $link = $CFG->wwwroot.'/blocks/bcgt/grids/class_grid.php?sID=-1&qID='.$this->id.'&g=c&cID='.$courseID.'&grID='.$groupingID;
        }
        elseif($view == 'q')
        {
            $link = $CFG->wwwroot.'/blocks/bcgt/grids/ass.php?sID=-1&qID='.$this->id.'&cID='.$courseID.'&grID='.$groupingID;
        }
        elseif($view == 'sg')
        {
            $link = $CFG->wwwroot.'/blocks/bcgt/grids/student_grid.php?sID='.$this->studentID.'&qID='.$this->id.'&cID='.$courseID;
        }
        elseif($view == 's')
        {
            $link = $CFG->wwwroot.'/blocks/bcgt/grids/ass_grid.php?sID='.$this->studentID.'&qID='.$this->id.'&cID='.$courseID.'&grID='.$groupingID;
        }
        //are we showing the alps reports?
        $seeAlps = false;
        if(get_config('bcgt', 'calcultealpstempreports') 
                && has_capability('block/bcgt:seealpsreportsstudent', $courseContext) && $this->has_qual_weightings())
        {
            //does this qualification have alps weightings?
            $seeAlps = true;
        }
        $retval .= $project->get_grid_heading($projects, 
                    $seeTargetGrade, $seeWeightedTargetGrade, 'stu', $projectID, $link, $seeBoth, $seeAlps, $this->id);
        $retval .= '<tbody>';
        
        foreach($users AS $user)
        {
            $targetGradeObj = null;
            if($seeTargetGrade || $seeWeightedTargetGrade)
            {
                $userCourseTargets = new UserCourseTarget(-1);
                $targetGrades = $userCourseTargets->retrieve_users_target_grades($user->id, $this->id);
                if($targetGrades)
                {
                    //as its one qual it will have one object
                    $targetGradeObj = $targetGrades[$this->id];
                }
            }

            $retval .= '<tr>';
            $qual = new stdClass();
            $qual->id = $this->id;
            $obj = $project->get_grid_info("qual", $qual, $user, 
                    $seeTargetGrade, $seeWeightedTargetGrade, $targetGradeObj, $seeBoth);
            $retval .= $obj->info;
                        
            $weightedGradeUsed = $obj->weightedgradeused;
            if($seeAlps)
            {
                $retval .= '<td><span class="alpsceta alpstemp" qual="'.$this->id.'" user="'.$user->id.'" id="alpsceta_'.$this->id.'_'.$user->id.'_2">';
            }
            if($save)
            {
                $this->save_user_project_row($user->id, $projects);
            }
            $retval .= $this->display_user_project_row($user->id, $projects, $editing, 
                    $targetGradeObj, $weightedGradeUsed, $projectID);  
        }
        
        $retval .= '</tbody></table>';
        //then comes the projects 
        //need to do a sort. 
        
        //then loop over the project and build the header
        
        //then loop over each student
        //build the row. 
        return $retval;
    }
    
    
    
    
    // Most recent targetdate
    public static function get_current_ceta($qualID, $userID)
    {
        //so we want the ceta that is closest to todays date, but before it
        global $DB;
        $sql = "SELECT grades.*, userref.dateupdated FROM {block_bcgt_user_activity_ref} userref 
            LEFT JOIN {block_bcgt_activity_refs} refs ON refs.id = userref.bcgtactivityrefid 
            LEFT JOIN {block_bcgt_target_grades} grades ON grades.id = bcgttargetgradesid 
            LEFT JOIN {block_bcgt_project} project ON project.id = refs.bcgtprojectid
            LEFT OUTER JOIN {block_bcgt_project_att} att ON att.bcgtprojectid = project.id 
            WHERE refs.bcgtqualificationid = ? AND userref.userid = ? 
            AND project.targetdate IS NOT NULL AND bcgttargetgradesid IS NOT NULL 
            ORDER BY project.targetdate DESC";
        //            OR (SELECT id FROM {block_bcgt_project_att} WHERE name = ?) IS NULL 
//            AND project.targetdate < NOW() AND project.targetdate IS NOT NULL) 
        $records = $DB->get_records_sql($sql, array($qualID,$userID), 0, 1);
        if($records)
        {
            return end($records);
        }
        return false;
    }
    
    
    
    
    
    
    
    
    
    // Most recently updated for the user
    public static function get_most_recent_ceta($qualID, $userID)         
    {
        global $DB;
        $sql = "SELECT grades.*, userref.dateupdated FROM {block_bcgt_user_activity_ref} userref 
            JOIN {block_bcgt_activity_refs} refs ON refs.id = userref.bcgtactivityrefid 
            JOIN {block_bcgt_target_grades} grades ON grades.id = bcgttargetgradesid 
            JOIN {block_bcgt_project} project ON project.id = refs.bcgtprojectid
            LEFT OUTER JOIN {block_bcgt_project_att} att ON att.bcgtprojectid = project.id 
            WHERE refs.bcgtqualificationid = ? AND userref.userid = ? 
            AND (userref.dateupdated < ? OR userref.dateupdated IS NULL )
            AND userref.bcgttargetgradesid != ?
            ORDER BY userref.dateupdated DESC, refs.id DESC";
                
        //            OR (SELECT id FROM {block_bcgt_project_att} WHERE name = ?) IS NULL 
//            AND project.targetdate < NOW() AND project.targetdate IS NOT NULL) 
        return $DB->get_records_sql($sql, array($qualID,$userID, time(), 0), 0, 1);
    }
    
    public static function get_current_fa_grade($qualID, $userID)
    {
        //so we want the ceta that is closest to todays date, but before it
        global $DB;
//        $sql = "SELECT value.*, userref.dateupdated FROM {block_bcgt_user_activity_ref} userref 
//            JOIN {block_bcgt_activity_refs} refs ON refs.id = userref.bcgtactivityrefid 
//            JOIN {block_bcgt_value} value ON value.id = userref.bcgtvalueid  
//            JOIN {block_bcgt_project} project ON project.id = refs.bcgtprojectid
//            LEFT OUTER JOIN {block_bcgt_project_att} att ON att.bcgtprojectid = project.id 
//            WHERE refs.bcgtqualificationid = ? AND userref.userid = ? AND 
//            project.targetdate < UNIX_TIMESTAMP(NOW()) AND project.targetdate IS NOT NULL AND userref.bcgtvalueid IS NOT NULL 
//            ORDER BY project.targetdate DESC";
//            $records = $DB->get_records_sql($sql, array($qualID,$userID), 0, 1);
//            
        $sql = "SELECT value.*, userrefs.dateupdated
                FROM {block_bcgt_activity_refs} refs
                INNER JOIN {block_bcgt_project} project ON project.id = refs.bcgtprojectid 
                LEFT JOIN {block_bcgt_user_activity_ref} userrefs ON (userrefs.bcgtactivityrefid = refs.id AND userrefs.userid = ?)
                LEFT JOIN {block_bcgt_value} value ON value.id = userrefs.bcgtvalueid 
                WHERE refs.bcgtqualificationid = ?
                AND project.targetdate IS NOT NULL 
                ORDER BY project.targetdate DESC";
//            
        //            OR (SELECT id FROM {block_bcgt_project_att} WHERE name = ?) IS NULL 
//            AND project.targetdate < NOW() AND project.targetdate IS NOT NULL) 
        $records = $DB->get_records_sql($sql, array($userID, $qualID), 0, 1);       
        if($records)
        {
            return end($records);
        }
        return false;
    }
    
    public static function get_most_recent_fa_grade($qualID, $userID)         
    {
        global $DB;
        $sql = "SELECT value.*, userref.dateupdated FROM {block_bcgt_user_activity_ref} userref 
            JOIN {block_bcgt_activity_refs} refs ON refs.id = userref.bcgtactivityrefid 
            JOIN {block_bcgt_value} value ON value.id = userref.bcgtvalueid 
            JOIN {block_bcgt_project} project ON project.id = refs.bcgtprojectid
            LEFT OUTER JOIN {block_bcgt_project_att} att ON att.bcgtprojectid = project.id 
            WHERE refs.bcgtqualificationid = ? AND userref.userid = ? AND (userref.dateupdated < UNIX_TIMESTAMP(NOW()) OR userref.dateupdated IS NULL )
            AND userref.bcgtvalueid != ?
            ORDER BY userref.dateupdated DESC, refs.id DESC";
        //            OR (SELECT id FROM {block_bcgt_project_att} WHERE name = ?) IS NULL 
//            AND project.targetdate < NOW() AND project.targetdate IS NOT NULL) 
        return $DB->get_records_sql($sql, array($qualID,$userID, 0), 0, 1);
    }
    
    public function has_ucas_points()
    {
        return false;
    }
    
    public function has_qual_weightings()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_qual_weighting} weighting WHERE bcgtqualificationid = ?";
        $records = $DB->get_records_sql($sql, array($this->id));
        if($records && count($records) > 0)
        {
            return true;
        }
        return false;
    }
    
    public function get_courses()
    {
        global $DB;
        $sql = "SELECT course.* FROM {course} course 
            JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = course.id 
            WHERE coursequal.bcgtqualificationid = ?";
        return $DB->get_records_sql($sql, array($this->id));
    }
    
    public function get_courses_by_user()
    {
        global $DB;
        $sql = "SELECT distinct(course.id), course.* FROM {course} course 
            JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = course.id
            JOIN {context} context ON context.instanceid = course.id
            JOIN {role_assignments} roleass ON roleass.contextid = context.id 
            JOIN {user} u ON u.id = roleass.userid
            WHERE coursequal.bcgtqualificationid = ? AND u.id = ?";
        return $DB->get_records_sql($sql, array($this->id, $this->studentID));
    }
    
    /**
     * $targetGradeObject has 4 possible grades on it. 
     * @param type $studentID
     * @param type $projects
     * @param type $editing
     * @param type $targetGradeObject
     * @return string
     */
    public function display_user_project_row($studentID, $projects, $editing = false, 
            $targetGradeObject = null, $useWeighted = false, $projectID = -1, $seeUcas = false)
    {
        global $CFG, $OUTPUT, $printGrid;
        //TODO 
        
        $fromPortal = (isset($_SESSION['pp_user'])) ? true : false;
        
        $retval = '';
        //add the targetgrades in for the BTECS
        //add the possible values in as same as target grades
        
        //ability to view grid purely by seaching for a student

        //get the possible target grades
        //get the possible values.
                        
        if($editing)
        {
            $this->load_target_grades('ranking DESC');
            $this->load_possible_assessment_values($this->get_family_instance_id(), $this->bcgtTargetQualID);
//            $this->load_possible_values($this->get_family_instance_id(), array('bcgttargetqualid'=>$this->bcgtTargetQualID));
        }
        $projCount = 0;
        
        if($projects)
        {
            foreach($projects AS $project)
            {
                                       
                if ($project->is_visible_to_you())
                {    
                    
                    $projCount++;
                    $projClass = 'projclassb';
                    if($projCount % 2)
                    {
                        $projClass = 'projclassa';
                    }
                    if(($projectID != -1 && $project->get_id() == $projectID) || $projectID == -1)
                    {
                        $class = '';
                        if($project->is_project_current())
                        {
                            $class = 'current';
                        }
                        //is this qualification in this project???
                        if(!$project->project_on_qual($this->id))
                        {
                            //why two? - how should I know? You wrote it.
                            $retval .= '<td class="'.$class.' noproj fas"></td><td class="'.$class.' noproj fas"></td>';
                        }
                        else {
        //                    $vclass = $this->get_cell_class_grade_v_target_grade($project);
                            //are we editing
                            //load the student information up into each project
                            $project->load_student_information($studentID, $this->id);
                            //are we doing weighted or non weighted?
                            $vclass = 'unknown';
                            $tclass = 'unknown';

                            $currentComparison = $project->user_current_behind_ahead($this->id, $targetGradeObject, $useWeighted);

                            if($currentComparison == -1)
                            {
                                $vclass = 'behindtarget';
                            }
                            elseif($currentComparison == 0)
                            {
                                $vclass = 'ontarget';
                            }
                            elseif($currentComparison == 1)
                            {
                                $vclass = 'aheadtarget';
                            }
                            $targetComparison = $project->user_predicted_behind_ahead($this->id, $targetGradeObject, $useWeighted);
                            if($targetComparison == -1)
                            {
                                $tclass = 'behindtarget';
                            }
                            elseif($targetComparison == 0)
                            {
                                $tclass = 'ontarget';
                            }
                            elseif($targetComparison == 1)
                            {
                                $tclass = 'aheadtarget';
                            }
                            //once the 
                            $userValue = $project->get_user_value();
                            $value = '';
                            $valueID = -1;
                            $valueUcas = '';
                            if($userValue)
                            {
                                $value = $userValue->get_short_value();
                                $valueID = $userValue->get_id();
                                if($seeUcas)
                                {
                                    $targetQualID = Qualification::get_target_qual_by_qualID($this->id);
                                    $valueGrade = TargetGrade::get_obj_from_grade($value, $userValue->get_ranking(), $targetQualID);
                                    if($valueGrade)
                                    {
                                        $valueUcas = ' {'.$valueGrade->get_ucas_points().'}';
                                    }
                                }
                            }
                            $userGrade = '';
                            $userGradeID = -1;
                            $userGradeUcas = '';
                            $comments = $project->get_user_comments();
                            if($project->get_user_grade())
                            {
                                $userGrade = $project->get_user_grade()->get_grade();
                                $userGradeID = $project->get_user_grade()->get_id();
                                if($seeUcas && $project->get_user_grade()->get_ucas_points())
                                {
                                    $userGradeUcas = ' {'.$project->get_user_grade()->get_ucas_points().'}';
                                }

                            }
                            if(!$editing)
                            {
                                                                
                                if($value == "")
                                {
                                    $value = "<span class='novalue'><img src='".
                                    $CFG->wwwroot."/blocks/bcgt/pix/qmark-trans.png'/></span>";
                                }
                                if($userGrade == "")
                                {
                                    $userGrade = "<span class='novalue'><img src='".
                                    $CFG->wwwroot."/blocks/bcgt/pix/qmark-trans.png'/></span>";
                                }
                                $retval .= '<td class="'.$class.' '.$vclass.' '.$projClass.' proj'.$projCount.' fas">'.$value;
                                if($seeUcas)
                                {
                                    $retval .= '<sub class="ucas">'.$valueUcas.'</sub>';
                                }
                                $retval .= '</td>';
                                if(get_config('bcgt', 'aleveluseceta'))
                                {
                                    $retval .= '<td class="'.$class.' '.$tclass.' '.$projClass.' proj'.$projCount.' fas">'.$userGrade;
                                    if($seeUcas)
                                    {
                                        $retval .= '<sub class="ucas">'.$userGradeUcas.'</sub>';
                                    }
                                    $retval .= '</td>';
                                }

                            }
                            else {
                                $retval .= '<td class="'.$class.' '.$vclass.' '.$projClass.' proj'.$projCount.' fas"><select name="sID_'.$studentID.'_qID_'.$this->id.'_pID_'.$project->get_id().'_v">';
                                    $retval .= '<option></option>';
                                    if($this->possibleValues)
                                    {
                                        foreach($this->possibleValues AS $possValue)
                                        {
                                            $selected = '';
                                            if($studentID != -1 && $possValue->id == $valueID)
                                            {
                                                $selected = 'selected';
                                            }
                                            $retval .= '<option '.$selected.' value="'.
                                                    $possValue->id.'">'.
                                                    $possValue->shortvalue.
                                                    '</option>';
                                        }
                                    }
                                    $retval .= '</select></td>';
                                    if(get_config('bcgt', 'aleveluseceta'))
                                    {
                                        $retval .= '<td class="'.$class.' '.$tclass.' '.$projClass.' proj'.$projCount.' fas"><select name="sID_'.$studentID.'_qID_'.$this->id.'_pID_'.$project->get_id().'_c">';
                                            $retval .= '<option></option>';
                                            if($this->targetGrades)
                                            {
                                                foreach($this->targetGrades AS $grade)
                                                {
                                                    $selected = '';
                                                    if($grade->id == $userGradeID)
                                                    {
                                                        $selected = 'selected';
                                                    }
                                                    $retval .= '<option '.$selected.' value="'.
                                                        $grade->id.'">'.
                                                        $grade->grade.'</option>';
                                                }
                                            }
                                        $retval .= '</select></td>';
                                    }
                            }
                            if($projectID != -1 || $fromPortal || $printGrid)
                            {
                                $retval .= '<td class="'.$class.'">';
                                if($editing)
                                {
                                    $retval .= '<textarea name="sID_'.$studentID.'_qID_'.$this->id.'_pID_'.$project->get_id().'_com">';
                                    $retval .= $comments;
                                    $retval .= '</textarea>';
                                }
                                else 
                                {
                                    $comments = format_text($comments, FORMAT_PLAIN);
                                    $retval .= "<div id='project_comments_{$studentID}_{$this->id}_{$project->get_id()}' style='display:none;width:500px !important;'><small>{$comments}</small></div>";
                                    if ($fromPortal && strlen($comments) > 100){
                                        $comments = substr($comments, 0, 100) . '...<br><a href="#" onclick="showProjectCommentsPopup('.$studentID.', '.$this->id.', '.$project->get_id().', \''.$project->get_name().'\');return false;"><small>[Read more...]</small></a>';
                                    }
                                    $retval .= $comments;
                                }
                                $retval .= '</td>';
                            }
                            
                        }


                    }

                }
                else
                {
                    
                    // Not visible to you
                    $retval .= '<td><img src="'.$OUTPUT->pix_url('t/show').'" alt="hidden" /></td>';
                    
                    if(get_config('bcgt', 'aleveluseceta'))
                    {
                        $retval .= '<td><img src="'.$OUTPUT->pix_url('t/show').'" alt="hidden" /></td>';
                    }
                    
                }

            }
        }
                
        return $retval;
    }
    
    /**
     * 
     * @param type $userID
     * @param type $roleID
     * @param type $addToUnits
     */
    public function add_user_to_qual($userID, $roleID, $addToUnits = false)
    {
        if(!Qualification::check_user_on_qual($userID, $roleID, $this->id))
        {
//            if($this->id == 130 && $userID == 2456)
//            {
//                echo "not on user";
//            }
            $this->insert_user($userID, $roleID);
            if($addToUnits)
            {
                $this->add_single_student_units($userID);
            }
        }
    }
    
    /**
     * 
     * @param type $users
     * @param type $roleID
     * @param type $addToUnits
     */
    public function add_users_object($users, $roleID, $addToUnits = false)
    {
        foreach($users AS $user)
        {
            $this->add_user_to_qual($user->id, $roleID, $addToUnits);
        }
    }
    
    /**
     * 
     * @param type $userIDs
     * @param type $roleID
     * @param type $removeFromUnits
     */
    public function remove_users($userIDs, $roleID, $removeFromUnits = false)
    {
        foreach($userIDs AS $userID)
        {
            $this->remove_user_from_qual($userID, $roleID, $removeFromUnits);
        }
    }
    
    /**
     * 
     * @param type $userID
     * @param type $roleID
     * @param type $removeFromUnits
     */
    public function remove_user_from_qual($userID, $roleID, $removeFromUnits = false)
    {
        if(Qualification::check_user_on_qual($userID, $roleID, $this->id))
        {
            $this->remove_user($userID, $roleID);
            if($removeFromUnits)
            {
                $this->remove_single_students_units($userID);
            }
        }
        else
        {
        }
    }
    
    public function update_student_target_grade($userID, $value, $type, $courseID)
    {
        global $DB, $USER;
        $sql = "SELECT * FROM {block_bcgt_user_course_trgts} WHERE userid = ? AND bcgtqualificationid = ? AND courseid = ?";
        $update = false;
        $record = $DB->get_record_sql($sql, array($userID, $this->id, $courseID));
        if($record)
        {
            $update = true;
        }
        else 
        {
            $record = new stdClass();
            $record->userid = $userID;
            $record->bcgtqualificationid = $this->id;
            $record->courseid = $courseID;
        }
                
        switch($type)
        {
            case 'breakdown':
                $record->bcgttargetbreakdownid = $value;
                $location = 'block_bcgt_target_breakdown';
                break;
            case 'grade':
                $record->bcgttargetgradesid = $value;
                $location = 'block_bcgt_target_grades';
                break;
        }
        if($update)
        {
            $id = $record->id;
            $DB->update_record('block_bcgt_user_course_trgts', $record);
        }
        else 
        {
            $id = $DB->insert_record('block_bcgt_user_course_trgts', $record);
        }
        
        
        
        // Store in other table
        $check = $DB->get_record("block_bcgt_stud_course_grade", array("userid" => $userID, "qualid" => $this->id, "type" => "target"));
        if ($check)
        {
            $check->recordid = $value;
            $check->location = $location;
            $check->setbyuserid = $USER->id;
            $check->settime = time();
            $DB->update_record("block_bcgt_stud_course_grade", $check);
        }
        else
        {

            $ins = new stdClass();
            $ins->userid = $userID;
            $ins->qualid = $this->id;
            $ins->courseid = $courseID;
            $ins->type = "target";
            $ins->recordid = $value;
            $ins->location = $location;
            $ins->setbyuserid = $USER->id;
            $ins->settime = time();
            $DB->insert_record("block_bcgt_stud_course_grade", $ins);

        }
        
        
        
        // Calculate weighted
        $UCT = new UserCourseTarget();
        $UCT->calculate_weighted_target_grade($this, $userID, $courseID, $record);
        
    }
    
    /**
     * 
     * @param type $users
     * @param type $roleID
     * @param type $removeFromUnits
     */
    public function remove_users_object($users, $roleID, $removeFromUnits = false)
    {
        foreach($users AS $user)
        {
            $this->remove_user_from_qual($user->id, $roleID, $removeFromUnits);
        }
    }
    
    /**
	 * This function will add a single student to all of the units
	 * that are on this qualification
	 * @param unknown_type $studentID
	 */
	public function add_single_student_units($studentID)
	{
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, 
                LOG_VALUE_GRADETRACKER_ADDED_USER_TO_ALL_UNITS, $studentID, $this->id, null, 
                null, null, null);
		if($this->units)
		{
            global $DB;
            $sql = "INSERT INTO {block_bcgt_user_unit} 
            (userid, bcgtqualificationid, bcgtunitid, bcgttypeawardid)  
            SELECT ?, ?, bcgtunitid, ? 
            FROM {block_bcgt_qual_units} WHERE bcgtqualificationid = ?";
            $params = array($studentID, $this->id, -1, $this->id);
            return $DB->execute($sql, $params);
		}
	}
	
	/**
	 * This function will remove the single student from all of the units that are
	 * on this qualification
	 * @param unknown_type $studentID
	 */
	public function remove_single_students_units($studentID)
	{ 
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, 
                LOG_VALUE_GRADETRACKER_REMOVED_USER_FROM_ALL_UNITS, $studentID, $this->id, null, 
                null, null, null);
		if($this->units)
		{
            $this->insert_student_units_history($studentID);
            global $DB;
            return $DB->delete_records('block_bcgt_user_unit', 
                    array('userid'=>$studentID, 'bcgtqualificationid'=>$this->id));
            
		}
	}
    
    /**
     * Called after the load_student_information
     * Will reset all UNIT->is_student_doing() to true;
     * ONLY ON THE OBJECT
     */
    public function add_student_to_all_units()
    {
        foreach($this->units AS $unit)
        {
            $unit->set_is_student_doing(true);
        }
    }
    
    /**
     * Called after the load_student_information
     * Will reset all UNIT->is_student_doing() to alse;
     * ONLY ON THE OBJECT
     */
    public function remove_student_from_all_units()
    {
        foreach($this->units AS $unit)
        {
            $unit->set_is_student_doing(false);
        }
    }
    
    /**
	 * Gets the numeric position of the unit
	 * in the array of units
	 * @param Unit $unit
	 * 
	 * This is where I started to put type forcing in
	 * REALLY I SHOULD HAVE DONE THIS EVERYWHERE
	 */
	public function is_unit_on_qual_object(Unit $unit)
	{
		$unitID = $unit->get_id();
		$i = 0;
		foreach($this->units AS $unitQ)
		{
			if($unitQ->get_id() && $unitQ->get_id() == $unitID)
			{
				return $i;
			}
			$i++;
		}
		return -1;
	}
    
	/**
	 * Removes the unit from the qualification
	 * @param unknown_type $unit
	 */
	protected function remove_units_qual($unit)
	{
		$i = $this->is_unit_on_qual_object($unit);
		if($i != -1)
		{
			unset($this->units[$unit->get_id()]);
            logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_REMOVED_UNIT_FROM_QUAL, null, $this->id, $unit->get_id(), null, $unit->get_id());
			return true;
		}
		return false;
	}
    
    /**
     * Adds the qual to the course and adds the students and teachers
     * to the qual and units. 
     * @global type $DB
     * @param type $courseID
     * @param type $addUsers
     */
    public function add_to_course($courseID, $addUsers)
    {
        global $DB;
        //insert this qual into the table for the course ID
        $this->insert_course($courseID);
        if($addUsers)
        {
            $studentRoleDB = $DB->get_record_select('role', 'shortname = ?', array('student'));
            $teacherRoleDB = $DB->get_record_select('role', 'shortname = ?', array('teacher'));
            $context = context_course::instance($courseID);
            //find all of the users that are on this course
            $students = get_enrolled_users($context, 'block/bcgt:addasstudentongrids');
            $teachers = get_enrolled_users($context, 'block/bcgt:addasteacherongrids');
            $this->insert_users($students, $studentRoleDB->id);
            $this->insert_users($teachers, $teacherRoleDB->id);
            foreach($students AS $student)
            {
                foreach($this->units AS $unit)
                {
                    $unit->insert_student_on_unit($this->id, $student->id);
                }
            }
            //TODO add the cron hook and event handling on add students to course. 
            
        }
      
    }
    
    /**
	 * Used to get the credits value from the database
	 * @param $id
	 */
	protected static function retrieve_credits($qualID)
	{
		global $DB;
		$sql = "SELECT credits FROM {block_bcgt_qualification} WHERE id = ?";
		return $DB->get_record_sql($sql, array($qualID));
	}
    
    /**
     * 
     * @param type $qualID
     * @param type $familyID
     * @param type $family
     * @param type $typeID
     * @param type $type
     * @param type $levelID
     * @param type $level
     * @param type $subTypeID
     * @param type $subType
     * @param type $name
     * @return type
     */
    public static function retrieve_qual($qualID = -1, $familyID = -1, $family = '', $typeID = -1, 
            $type = '', $levelID = -1, $level = '', $subTypeID = -1, $subType = '', $name = '')
    {
        global $DB;
        $sql = "SELECT qual.id, qual.name, qual.additionalname, qual.credits, 
            level.id as levelid, level.trackinglevel, subtype.id as subtypeid, subtype.subtype,
            type.id as typeid, type.type, family.id as familyid, family.family, targetqual.id AS bcgttargetqualid
            FROM {block_bcgt_qualification} qual 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
            JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid";
        $params = array();
        if($familyID != -1 || $family != '' || $typeID != -1 || 
            $type != '' || $levelID != -1 || $level != '' || $subTypeID != -1 || $subType != '' || $name != '' || $qualID != -1)
        {
            $sql .= ' WHERE ';
            $and = false;
            if($qualID != -1)
            {
                $sql .= ' qual.id = ?';
                $params[] = $qualID;
                $and = true;
            }
            if($familyID != -1)
            {
                if($and)
                {
                    $sql .= ' AND ';
                }
                $sql .= ' family.id = ?';
                $params[] = $familyID;
                $and = true;
            }
            if($family != '')
            {
                if($and)
                {
                    $sql .= ' AND ';
                }
                $sql .= ' family.family = ?';
                $params[] = $family;
                $and = true;
            }
            if($typeID != -1)
            {
                if($and)
                {
                    $sql .= ' AND ';
                }
                $sql .= ' type.id = ?';
                $params[] = $typeID;
                $and = true;
            }
            if($type != '')
            {
                if($and)
                {
                    $sql .= ' AND ';
                }
                $sql .= ' type.type = ?';
                $params[] = $type;
                $and = true;
            }
            if($levelID != -1)
            {
                if($and)
                {
                    $sql .= ' AND ';
                }
                $sql .= ' level.id = ?';
                $params[] = $levelID;
                $and = true;
            }
            if($level != '')
            {
                if($and)
                {
                    $sql .= ' AND ';
                }
                $sql .= ' level.trackinglevel = ?';
                $params[] = $level;
                $and = true;
            }
            if($subTypeID != -1)
            {
                if($and)
                {
                    $sql .= ' AND ';
                }
                $sql .= ' subtype.id = ?';
                $params[] = $subTypeID;
                $and = true;
            }
            if($subType != '')
            {
                if($and)
                {
                    $sql .= ' AND ';
                }
                $sql .= ' subtype.subtype = ?';
                $params[] = $subType;
                $and = true;
            }
            if($name != '')
            {
                if($and)
                {
                    $sql .= ' AND ';
                }
                $sql .= ' qual.name = ?';
                $params[] = $name;
                $and = true;
            }
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Removes a students from the qual from the course and student and teachers
     * from the units and qual. 
     * @param type $courseID
     * @param type $removeUsers
     */
    public function remove_from_course($courseID, $removeUsers)
    {
        global $DB;
        //remove this qual from the table for the course ID
        $this->remove_course($courseID);
        if($removeUsers)
        {    
            $studentRoleDB = $DB->get_record_select('role', 'shortname = ?', array('student'));
            $teacherRoleDB = $DB->get_record_select('role', 'shortname = ?', array('teacher'));
            $context = context_course::instance($courseID);
            $students = get_enrolled_users($context, 'block/bcgt:addasstudentongrids');
            $teachers = get_enrolled_users($context, 'block/bcgt:addasteacherongrids');
            //delete all users from this qual.
            $this->delete_users($students, $studentRoleDB->id);
            $this->delete_users($teachers, $teacherRoleDB->id);
            
            //delete all users from the units of this qual
            foreach($students AS $student)
            {
                foreach($this->units AS $unit)
                {
                    $unit->delete_student_on_unit_no_id($student->id, $this->id);
                }
            }
            //get the users
            //TODO add the cron hook and event handling on add students to course. 
        }
    }
    
    protected function insert_course($courseID)
    {
        //check its not already on
        if(!$this->get_qual_courses($courseID))
        {
            global $DB;
            $stdObject = new stdClass();
            $stdObject->courseid = $courseID;
            $stdObject->bcgtqualificationid = $this->id;
            return $DB->insert_record('block_bcgt_course_qual', $stdObject); 
        }
    }
    
    /**
     * Gets the courses this qual is on,
     * @global type $DB
     * @param type $courseID checks if its on this course. 
     * @return type
     */
    protected function get_qual_courses($courseID = -1)
    {
        global $DB;
        $sql = "SELECT * FROM {course} course 
            JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = course.id 
            WHERE coursequal.bcgtqualificationid = ?";
        $params = array($this->id);
        if($courseID != -1)
        {
            $sql .= ' AND coursequal.courseid = ?';
            $params[] = $courseID;
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Removes this qual from the course passed in
     * @global type $DB
     * @param type $courseID
     */
    protected function remove_course($courseID)
    {
        $this->insert_qual_course_history($courseID);
        global $DB;
        $DB->delete_records('block_bcgt_course_qual', array('courseid'=>$courseID, 
            'bcgtqualificationid'=>$this->id));
    }
    
    /**
     * Removes the qual from all courses. 
     * @global type $DB
     */
    protected function remove_from_all_courses()
    {
        global $DB;
        $DB->delete_records('block_bcgt_course_qual', array( 
            'bcgtqualificationid'=>$this->id));
    }

    /**
	 * Updates the qualification in the database with the object
	 * For every unit that is on the qualification, it updates them.
	 * if not a simple save then it will upate the qualification and the units
	 * if it is a simple save then it will onlu update the quals
	 * units
	 * 
	 * UpdateCriteria is passed down to save_units
	 * Do we need to save the criteria as welll? Are they changing?
	 */
	public function save($simpleSave = false, $updateCriteria = false)
	{
		//TODO should do if exists in db then update, else do insert
		if(!$simpleSave)
		{
			$this->update_qualification();	
		}
		$this->update_qual_unit();
		if(!$simpleSave)
		{
			$this->save_units($updateCriteria);
		}	
	}
    
    /**
	 * Will call save on each unit.
	 * @param unknown_type $updateCriteria
	 */
	public function save_units($updateCriteria = true)
	{
		foreach($this->units AS $unit)
		{
			$unit->save($updateCriteria);
		}
	}
    
    /**
     * This will save the students units. i.e. will
     * save them on them or remove them from them. 
     */
    public function save_students_units()
    {
        foreach($this->units AS $unit)
		{
			$unit->save_students_units();
		} 
    }
    
    /**
	 * This function updates the database 
	 * sets the database with the
	 * units on the qualification
	 */
	public function update_qual_unit()
	{
        global $DB;
		$in = array();
		$found = false;
		foreach($this->units AS $unit)
		{
			$unitID = $unit->get_id();
			$dataObj = new stdClass();
			$dataObj->bcgtqualificationid = $this->id;
			$dataObj->bcgtunitid = $unit->get_id();
			$id = $this->is_unit_on_qual_db($unitID);
			if($id == -1)
			{
				$DB->insert_record('block_bcgt_qual_units', $dataObj);
				//lets insert the students units
				//TODO
                //$this->edit_students_units($unit->get_id(), true);
			}
			else
			{
				$dataObj->id = $id;
				$DB->update_record('block_bcgt_qual_units', $dataObj);
			}
			$found = true;
			$in[] = $unit->get_id();	
		}
		
		//lets see what units we have to remove if any.
		$sql = "SELECT * FROM {block_bcgt_qual_units} 
            WHERE bcgtqualificationid = ?";
        $params = array($this->id);
		if($found)
		{
            $count = 0;
			$sql .= " AND bcgtunitid NOT IN (";
            foreach($in AS $unitID)
            {   
                $count++;
                if($count != 1)
                {
                    $sql .= ',';
                }
                $sql .= '?';
                $params[] = $unitID;
            }
            $sql .= ')';
		}
		$unitsToRemove = $DB->get_records_sql($sql, $params);
		if($unitsToRemove)
		{
			foreach($unitsToRemove AS $qualUnits)
			{
				$DB->delete_records('block_bcgt_qual_units', array('id' => $qualUnits->id, 'bcgtqualificationid' => $this->id));
				//lets remove and archive the students units
				//TODO
                //$this->edit_students_units($qualUnits->bcgtunitid, false);	
			}
		}
	}
    
    public function get_students_total_credits(){
        return 0;
    }
    
    public function get_students_units_summary()
    {
        $retval = '';
        if($this->units)
        {
            $retval = "<div id='stuQAwS".$this->studentID."Q".$this->id."'>";
            $retval .= "<div>";
            $retval .= "<table><tr><th>".get_string('unit', 'block_bcgt').
                    "</th><th>".get_string('award', 'block_bcgt')."</th></tr>";
            foreach($this->units AS $unit)
            {
                if($unit->is_student_doing())
                {
                    $award = 'N/A';
                    $stuAward = $unit->get_user_award();
                    if($stuAward)
                    {
                        $award = $stuAward->get_award();
                    }
                    $retval .= "<tr><td>".$unit->get_name()."</td><td>$award</td></tr>";
                }
            }
            $retval .= "</table></div>";
            $retval .= "</div>";
        }
        return $retval;
    }
    
    public function get_predicted_award(){
        return $this->predictedAward;
    }
    
    /**
	 * This funcion loads up the qualification with the students data as set by the
	 * student id. 
	 * it checks if there is an award for the current type of qualification
	 * if there is it either gets the award or calculates the prediected grade
	 * it then loads the user information for all of the units and subsequent criteria. 
	 * @param unknown_type $studentID
	 */
    //$loadLevel = Qualification::LOADLEVELMIN, $loadAward = true
	public function load_student_information($studentID,
            $loadParams = null)
	{
        $this->clear_student_information();
		//check all of the units on the qual are those that the student is doing. 
		//if not remove them or set a variable on them.
		//For all units they are doing set the awards on them. 
		//For each criteria they are doing on each unit load the users values.
		$this->studentID = $studentID;
        $this->load_student();
        if($loadParams && $loadParams->loadLevel && 
                $loadParams->loadLevel >= Qualification::LOADLEVELUNITS)
        {
            foreach($this->units AS $unit)
            {
                //will go off and load the student info onto all units
                //checks if the student 'is doing' the unit.
                $unit->load_student_information($studentID, $this->id, $loadParams);	
            }
        }
		//Unit work above could have changed the potential final and/or prediected grade
		if($loadParams && isset($loadParams->loadAward) && $this->has_final_grade())
		{
			//so the qual has a final grade (this is different per qual type)
			//do we allready have a grade in the db for this student?
			if(!$qualAwards = $this->retrieve_student_award())
			{
				//ok so we dont have a final grade. can we predict one?
				//will return final if its final.
                //do we really need to recalculate one each time?
				$awards = $this->calculate_predicted_grade();
                //award is actually loads. 
				if($awards)
				{
                    if(isset($awards->predicted))
                    {
                        $this->predictedAward = $awards->predicted;   
                    }
                    if(isset($awards->final))
                    {
                        $this->studentAward = $awards->final; 
                    }
					if(isset($awards->minAward))
                    {
                        $this->minAward = $awards->minAward;
                    }
                    if(isset($awards->maxAward))
                    {
                        $this->maxAward = $awards->maxAward;
                    }
				}
				else
				{
					//no award possible
				}
			}
			else
			{
                if(isset($qualAwards->Min))
                {
                    $this->minAward = $qualAwards->Min;
                }
                if(isset($qualAwards->Max))
                {
                    $this->maxAward = $qualAwards->Max;
                }
				//we have an award, what type is it?
				if(isset($qualAwards->Predicted))
                {
                    $this->predictedAward = $qualAwards->Predicted;   
                }
                if(isset($qualAwards->Final))
                {
                    $this->studentAward = $qualAwards->Final; 
                }
			}
		}
        if($loadParams && isset($loadParams->loadTargets))
        {
            $userCourseTargets = new UserCourseTarget(-1);
            $targetGrades = $userCourseTargets->retrieve_users_target_grades($this->studentID, $this->id);
            if($targetGrades)
            {
                //as its one qual it will have one object
                $targetGradeObj = $targetGrades[$this->id];
                $this->userTargetGrades = $targetGradeObj;
            }
        }
		//finally we want to load the units that this student is doing that
		//are not on the qualification.
		//for example APL units
		//find all extra units that are not on the qual that this student is doing
		//for this qual.
        if((!$loadParams) || ($loadParams && !isset($loadParams->loadAddUnits)) ||
                ($loadParams && isset($loadParams->loadAddUnits) && $loadParams->loadAddUnits == true))
        {
            $this->add_students_other_units();
        }       
        // Get comments as well
        $this->comments = "";
        $comments = $this->get_qual_comments(); 
        $this->comments = $comments; 

        //what about criteria that are on the qual but not on the unit?
        //for example Alevels. 
        $this->load_qual_criteria_student_info($studentID, $this->id);
        
        //Any extra loads
        $this->qual_specific_student_load_information($studentID, $this->id);
    }
    
    protected function clear_student_information()
    {
        $this->student = null;
        $this->minAward = null;
        $this->maxAward = null;
        $this->predictedAward = null;
        $this->studentAward = null;
    }
    
    
    public function get_comments()
    {
        return $this->comments;
    }
    
    /**
     * Gets the comments that have been set for this Qual for this student
     * Why am I getting it from thte DB each time? Was there a reason?
     */
    public function get_qual_comments()
    {
        global $DB;
        $roleDB = $DB->get_record_sql("SELECT * FROM {role} WHERE shortname = ? ", array('student'));
        $roleID = $roleDB->id;
        $comments = '';
        $tracking = $DB->get_record_select("block_bcgt_user_qual", 
                "userid = ? AND bcgtqualificationid = ? AND roleid = ?", 
                array($this->studentID,$this->id, $roleID));
        if(isset($tracking->id)){
            $comments = $tracking->comments;
        }
        return $comments;
    }
    
    
    public function update_comments($comments)
    {
        global $DB;
        
        $sql = "SELECT * FROM {block_bcgt_user_qual} AS userqual 
		WHERE userqual.userid = ? AND bcgtqualificationid = ? ";
		$userQual = $DB->get_record_sql($sql, array($this->studentID, $this->id));
		if($userQual)
		{
            
            $this->comments = $comments;
            
			$id = $userQual->id;
			$obj = new stdClass();
			$obj->id = $id;
			$obj->comments = $comments;
			return $DB->update_record('block_bcgt_user_qual', $obj);
		}
		return false;
    }
    
    /**
     * Find the distinct qualification types that are in use from an array of
     * qualIDs
     * @param type $qualIDs an array of qualIDs
     */
    public function get_qual_types($qualIDs)
    {
        global $DB;
        $sql .= "SELECT type.* FROM {block_bcgt_type} type
            JOIN {block_bcgt_target_qual} targetQual ON targetQual.bcgttypeid = type.id
            JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetQual.id 
            WHERE qual.id IN (";
        $params = array();
        $count = 0;
        foreach($qualIDs AS $qualID)
        {
            $count++;
            if($count != 1)
            {
                $sql .= ',';
            }
            $sql .= "?"; 
            $params[] = $qualID;
        }
        $sql .= ")";
        return $DB->get_records_sql($sql, $params);
    }
    
    public function update_users_target_grade($gradeID, $courseID = -1)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_course_trgts} WHERE userid = ? AND bcgtqualificationid = ?";
        $params = array($this->studentID, $this->id);
        if($courseID != -1)
        {
            $sql .= " AND courseid = ?";
            $params[] = $courseID;
        }
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            $record->bcgttargetgradesid = $gradeID;
            $DB->update_record('block_bcgt_user_course_trgts', $record);
        }
        else
        {
            $record = new stdClass();
            $record->userid = $this->studentID;
            $record->courseid = -1;
            $record->bcgttargetbreakdownid = -1;
            $record->bcgttargetgradesid = $gradeID;
            $record->bcgtqualificationid = $this->id;
            $DB->insert_record('block_bcgt_user_course_trgts', $record);
        }
    }
   
    //****** THE STATIC FUNCTIONS ******//
    /**
	 * Used to return the correct qualificationID. 
	 * This method will be used when the qualificationID is not know
	 * (i.e.) the qualification is brand new and doesnt exist in the database
	 * This only deals with the core attributes. The normal set and get attributes on sub classes
	 * wil need to be used
	 * @param unknown_type $typeID
	 * @param unknown_type $qualID
     * @param unknown_type $familyID
     * @param unknown_type $$params is an object with extra parameters, such as
     * subtype and level, name etc
	 */
    //$loadParams=>loadLevel = Qualification::LOADLEVELMIN
	public static function get_qualification_class($typeID = -1, $qualID = -1, $familyID = -1, 
            $params = null, $loadParams = null)
	{
		return Qualification::get_correct_qual_class($typeID, $qualID, $familyID, $params, $loadParams);
	}
    
    /**
	 * Used to return the full correct qualification class when only the qualificationID is known
	 * This only deals with the core attributes. The normal set and get attributes on sub classes
	 * wil need to be used
	 * 
	 * First it gets the records from the database
	 * Then it gets the correct qual type class
	 * 
	 * @param unknown_type $qualID
	 */
    //$loadParams->Qualification::LOADLEVELMIN
	static function get_qualification_class_id($qualID, $loadParams = null)
	{
		global $CFG, $DB;                       
		$sql = "SELECT type.id AS id, bcgttypefamilyid 
            FROM {block_bcgt_qualification} AS qual
		JOIN {block_bcgt_target_qual} AS targetQual ON targetQual.id = qual.bcgttargetqualid
		JOIN {block_bcgt_type} AS type ON type.id = targetQual.bcgttypeid
		WHERE qual.id = ?";
		$record = $DB->get_record_sql($sql, array($qualID));

        // If that returns false, see if it's a bespoke qual
        if (!$record)
        {
            $bespoke = $DB->get_record("block_bcgt_bespoke_qual", array("bcgtqualid" => $qualID));
            if ($bespoke)
            {
                require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeQualification.class.php';
                return Qualification::get_correct_qual_class(BespokeQualification::ID, $qualID, BespokeQualification::FAMILYID, null, $loadParams);
            }
        }
        
        $typeID = -1;
		$familyID = -1;
		if($record)
		{
			$typeID = $record->id;
			$familyID = $record->bcgttypefamilyid;
            //Get the correct qual type class
            $ret = Qualification::get_correct_qual_class($typeID, $qualID, $familyID, null, $loadParams);
            return $ret;
		}
        
        return false;
        
	}
    
    public static function get_students_unit_breakdown_tooltip($qualID, $studentID)
    {
        
    }
    
    /**
     * This returns the form elements to help us determine what qual type to load up. 
     * E.g. if selecting a level wants to load a different class
     * or selecting a subtype.
     * @param type $familyID
     * @param type $disabled
     * @param type $qualID
     * @param type $typeID
     * @return type
     */
    public static function get_qualification_edit_form_menu($familyID, $disabled, $qualID, $typeID = -1)
	{
        $qualificationClass = Qualification::get_plugin_class($familyID);
        if($qualificationClass)
        {
            return $qualificationClass::get_edit_form_menu($disabled, $qualID, $typeID);
        }
        return false;
	}
    
    /**
     * Checks if the user is on the qualifiction id passed in. 
     * @global type $DB
     * @param type $userID
     * @param type $roleID
     * @param type $qualID
     * @return boolean
     */
    public static function check_user_on_qual($userID, $roleID, $qualID, $isRole = true)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_qual} 
            WHERE bcgtqualificationid = ? AND userid = ? and roleid";
        if($isRole)
        {
            $sql .= ' = ';
        }
        else 
        {
            $sql .= ' != ';
        }
        $sql .= ' ?';
        if($isRole)
        {
            $check = $DB->get_record_sql($sql,array($qualID, $userID, $roleID));
        }
        else
        {
            $check = $DB->get_records_sql($sql,array($qualID, $userID, $roleID));
        }
        if($check)
        {
            if($isRole && $check->id)
            {
                return true;
            }
            elseif(!$isRole && count($check) > 0) 
            {
                return true;
            }
        }
        return false;   
    }
    
    public function has_percentage_completions()
    {
        return false;
    }
    
    /**
     * Deleletes the users qualificatioon award
     * @global type $DB
     */
    public function set_qualification_award_null()
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_user_award) WHERE bcgtqualificationid = ? 
            AND userid = $this->studentID";
		$record = $DB->get_record_sql($sql, array($this->id, $this->studentID));
		if($record)
		{
			$DB->delete_records("block_bcgt_user_award", array("id"=>$record->id));
		}
		
	}
    
    
    //***************THE Protected functions***********//
    /**
	 * Gets the unit id's that are on the qualification using 
	 * the table tracking_qual_units
	 * @param unknown_type $qualID
	 */
	protected static function retrieve_units($qualID)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_qual_units} 
		WHERE bcgtqualificationid = ?";
		return $DB->get_records_sql($sql, array($qualID));
	}
    
    protected static function get_qual_type($qualID)
    {
        global $DB;
		$sql = "SELECT type.* FROM {block_bcgt_type} AS type
		JOIN {block_bcgt_target_qual} AS targetqual ON targetqual.bcgttypeid = type.id 
		JOIN {block_bcgt_qualification} AS qual ON qual.bcgttargetqualid = targetqual.id 
		WHERE qual.id = ?";	
		return $DB->get_record_sql($sql, array($qualID));
    }

    protected static function get_qual_level($qualID)
	{
		global $DB;
		$sql = "SELECT level.* FROM {block_bcgt_level} AS level 
		JOIN {block_bcgt_target_qual} AS targetqual ON targetqual.bcgtlevelid = level.id 
		JOIN {block_bcgt_qualification} AS qual ON qual.bcgttargetqualid = targetqual.id 
		WHERE qual.id = ?";	
		return $DB->get_record_sql($sql, array($qualID));
	}
	
	protected static function get_qual_subtype($qualID)
	{
		global $DB;
		$sql = "SELECT subtype.* FROM {block_bcgt_subtype} AS subtype 
		JOIN {block_bcgt_target_qual} AS targetqual ON targetqual.bcgtsubtypeid = subtype.id 
		JOIN {block_bcgt_qualification} AS qual ON qual.bcgttargetqualid = targetqual.id 
		WHERE qual.id = ?";	
		return $DB->get_record_sql($sql, array($qualID));
	}
    
    protected static function get_qual_pathway($qualID)
    {
        global $DB;
        $sql = "SELECT pt.id, p.id as pathwayid, t.id as pathwaytypeid
                FROM {block_bcgt_qualification} q
                INNER JOIN {block_bcgt_pathway_dep_type} pt ON pt.id = q.pathwaytypeid
                INNER JOIN {block_bcgt_pathway_dep} p ON p.id = pt.bcgtpathwaydepid
                INNER JOIN {block_bcgt_pathway_type} t ON t.id = pt.bcgtpathwaytypeid
                WHERE q.id = ?";
        return $DB->get_record_sql($sql, array($qualID));
    }
    
    protected static function get_pathway_type($pathway, $type)
    {
        
        global $DB;
        
        $record = $DB->get_record("block_bcgt_pathway_dep_type", array("bcgtpathwaydepid" => $pathway, "bcgtpathwaytypeid" => $type));
        return ($record) ? $record->id : null;
        
    }
    
    
    /**
	 * Gets the target_qualification ID from for the combination of
	 * type, level and subtype. 
	 * @param unknown_type $typeID
	 * @param unknown_type $levelID
	 * @param unknown_type $subTypeID
	 */
	protected function get_target_qual($typeID)
	{
		global $DB;
		$sql = "SELECT id FROM {block_bcgt_target_qual} 
		WHERE bcgttypeid = ? AND bcgtlevelid = ?  
		AND bcgtsubtypeid = ?";
		//strictly speaking this could return more than one record. 
		//But in theory, and later after todo change, can only return one. 
		//TODO put unique constraint on three fields in db table
        $levelID = -1;
        if(isset($this->level))
        {
            $levelID = $this->level->get_id();
        }
        $subTypeID = -1;
        if(isset($this->subType))
        {
            $subTypeID = $this->subType->get_id();
        }
		$record = $DB->get_record_sql($sql, array($typeID, $levelID, $subTypeID));
		if($record)
		{
			return $record->id;
		}
		return -1;
	}
    
    /**
	 * Creates a history of all of the units on this qual
	 * Deletes all of the units from this qual
	 * Createa a history of all of the students doing all of the units on this qual
	 * Deletes all of the user units records for this qual
	 * Removes the qual from all courses
	 * Creates a history of the qualification
	 * Deletes the qualification
	 */
	public function delete_qual_main()
	{    
        // Log
        global $DB;
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_DELETED_QUAL, null, $this->id, null, null, null);
		if($this->units)
		{
			//remove/delete all units on quals but dont delete the actual units
			if($this->insert_qual_units_history())
			{
				//create a history record for all units on this qual
				$DB->delete_records('block_bcgt_qual_units', array('bcgtqualificationid'=>$this->id));	
			}
			if($this->insert_student_units_history())
			{
				//remove the students units
				//create a history record for all units on this qual
				$DB->delete_records('block_bcgt_user_unit', array('bcgtqualificationid'=>$this->id));
			}
		}
		
		// Course qual links
        $courses = $DB->get_records("block_bcgt_course_qual", array("bcgtqualificationid" => $this->id));
        if ($courses){
            foreach($courses as $course){
                if ($this->insert_qual_course_history($course->courseid)){
                    $DB->delete_records("block_bcgt_course_qual", array("bcgtqualificationid" => $this->id, "courseid" => $course->courseid));
                }
            }
        }
        
        // User Qual links
        $users = $DB->get_records("block_bcgt_user_qual", array("bcgtqualificationid" => $this->id));
        if ($users){
            foreach($users as $user){
                if ($this->insert_user_qual_history($user->userid, $user->roleid)){
                    $DB->delete_records("block_bcgt_user_qual", array("id" => $user->id));
                }
            }
        }
        
        // User Criteria links
        $criteria = $DB->get_records("block_bcgt_user_criteria", array("bcgtqualificationid" => $this->id));
        if ($criteria)
        {
            foreach($criteria as $criterion)
            {
                $criterion->bcgtusercriteriaid = $criterion->id;
                $id = $criterion->id;
                unset($criterion->id);
                $DB->insert_record("block_bcgt_user_criteria_his", $criterion);
                $DB->delete_records("block_bcgt_user_criteria", array("id" => $id));
            }
        }
        
        
        // Qual Units
        
		
		//delete the qual
		//create a history record for this qual
		if($this->insert_history_record($this->id))
		{
			return $DB->delete_records('block_bcgt_qualification', array('id'=>$this->id));
		}
	}
    
    public function has_advanced_mode()
    {
        return false;
    }
    
    protected function get_possible_unit_awards()
    {
        return array();
    }
    
    protected function get_simple_qual_report_tabs()
    {
        return array("s"=>"students");
    }
    
    public function get_simple_qual_report($userID, $tab, $edit, $courseID, $filter, $sort, $groupingID = -1, $type='')
    {
        global $CFG;
        //sort out the sort
        $sortArray = array();
        foreach($sort AS $header)
        {
            if($header != '')
            {
                if(array_key_exists($header, $sortArray))
                {
                     $sortArray[$header] = $sortArray[$header] + 1;
                }
                else
                {
                    $sortArray[$header] = 1;
                }
            }
        }
        $retval = '';
        //this ensure we only display the tabs that can be access for this qual/qualtype
        //this can be either overridden and/or the children can call the parent
        //see BTECQualification or CGQualification
        if($groupingID != -1)
        {
            $retval .= '<h3>'.$this->get_display_name().'</h3>';
        }
        $qualID = $this->id;
        $loadID = $qualID;
        if($groupingID != -1)
        {
            $qualID = -1;
            $loadID = $groupingID;
        }
        $tabs = $this->get_simple_qual_report_tabs();
        $retval .= "<div class='simplequalreportcontent'>";
		$retval .= "<div class='tabs'><form method='POST' name='changeView' action=''>";
		$retval .= "<div class='tabtree'><ul class='tabrow0'>";
        $retval .= "<li>
            <a class='nolink'>
            <img class='closereport' id='".$loadID."' tabtype='$type' src='".$CFG->wwwroot."/blocks/bcgt/pix/cross.gif'>
            </a>
            </li>";
        $count = 0;
        
        foreach($tabs AS $key=>$tabString)
        {
            $focus = ($tab == $key)? 'focus' : '';
            $count++;
            $class = 'middle';
            if($count == 1)
            {
                $class = 'first';
            }
            elseif($count == count($tabs))
            {
                $class = 'last';
            } 
            
            $retval .= "<li class='$class $focus'>
            <a class='nolink'>
                <span class='tab' course='$courseID' tab='$key' tabtype='$type' qual='$qualID' group='$groupingID' id='".$key."_".$loadID."'>".get_string($tabString, 'block_bcgt')."</span>
                <span class='".$loadID."loading' id='".$key."_".$loadID."loading'></span>
            </a>
            </li>";
        }
		
//                if($this->has_units())
//                {
//                    $retval .= "<li class='middle'>
//							<a class='nolink'>
//								<span class='tab' tab='u' qual='$this->id' id='u_".$this->id."'>".get_string('units', 'block_bcgt')."</span>
//							</a>
//						</li>";
//                }
//                $retval .= "<li class='last'>
//							<a class='nolink'>
//								<span class='tab' tab='co' qual='$this->id' id='co_".$this->id."'>".get_string('classoverview', 'block_bcgt')."</span>
//							</a>
//						</li>
            $retval .= "</ul>
				</div>";//end tabtrees
			$retval .= "</form>
			</div>";//end tabs
  
        switch($tab)
        {
            case 's':
                $retval .= $this->get_simple_qual_report_student($userID, $edit, $filter, $sortArray, $groupingID);
                break;
            case 'u':
                $retval .= $this->get_simple_qual_report_unit($userID,$sortArray, $courseID, $groupingID);
                break;
            case 'co':
                $retval .= $this->get_simple_qual_report_class($userID, $groupingID, $type);
                break;
        }
        return $retval;
    }
        
    /**
	 * Gets all of the students doing the qual and course
	 * Has an array of a filter
	 * Has an array of a sort
	 * Returns the students details
	 * The number of pass, merit and distinction
	 * their unit count, units awarded count
	 * qual award, target award, 
	 * points difference
	 * @param unknown_type $courseID
	 * @param unknown_type $qualID
	 * @param unknown_type $filter
	 * @param unknown_type $sort
	 */
	protected function get_students_course_qual_report($qualID, $filter, $sort, $groupingID = -1)
	{
		global $DB;
        
        $uFields = user_picture::fields('u', null, 'userid');        
		$sql = "
		SELECT distinct(role_ass.id) as id, course.id as courseid, course.shortname as courseshortname,
        course.fullname as coursefullname, {$uFields}, u.username";
                $sql .= ", teacherSetBreakdown.targetgrade as tstargetgrade, teacherSetBreakdown.unitsscorelower as tstargetlowerscore, teacherSetBreakdown.ranking as tstargetgraderanking, 
                    teacherSetBreakdown.id as teacherset_breakdownid ";
                $sql .= ", teacherSetTarget.grade as tsgrade, teacherSetTarget.ranking as tsgraderanking, 
                    teacherSetTarget.id as teacherset_targetid ";
                $sql .= ", scg.location as asplocation, scg.recordid as asprecordid ";
//                $sql .= ", teacherSetPredicted.targetgrade as tspred, teacherSetPredicted.unitsscorelower as tspredlowerscore";
		$hasUnits = false;
        if($this->has_units())
        {
            
            $hasUnits = true;
            $possibleUnitValues = $this->get_possible_unit_awards();
            foreach($possibleUnitValues AS $unitValue)
            {
                $field = strtolower($unitValue).'count';
                $table = strtolower($unitValue).'award';
                $fields = $table.'.'.$field;
                $sql .= ', '.$fields.'';
            }
            
            
//            $sql .= ", passaward.passcount";
//            $sql .= ", meritaward.meritcount"; 
//            $sql .= ", dissaward.disscount";
            $sql .= ", COALESCE(unitscount.unitcount, 0) AS unitcount";
            $sql .= ", COALESCE(unitawarded.unitsawarded, 0) AS unitsawarded";
            
        }
		$sql .= ", awardgrade.grade AS gradeaward, awardgrade.ranking as gradeawardranking"; 
        $sql .= ", awardbreakdown.targetgrade AS breakdownaward, awardbreakdown.ranking as breakdownawardranking"; 
		$sql .= ", breakdown.targetgrade AS targetgrade, breakdown.unitsscoreupper AS targetscore, breakdown.id as bcgtbreakdownid, breakdown.ranking as targetgraderanking"; 
        $sql .= ", targetgrade.grade AS grade, targetgrade.id as bcgttargetgradesid, targetgrade.ranking as graderanking"; 
		$sql .= ", (awardbreakdown.ranking - breakdown.ranking) as breakdowndifference";
        $sql .= ", (awardgrade.ranking - targetgrade.ranking) as gradedifference";
        $sql .= ", bespokequalgrades.grade as bespokequalaward";
		$sql .= " FROM {user} as u
		JOIN {role_assignments} AS role_ass ON role_ass.userid = u.id 
		JOIN {role} AS role ON role.id = role_ass.roleid 
		JOIN {context} AS context ON context.id = role_ass.contextid 	
		JOIN {course} AS course ON course.id = context.instanceid
        JOIN {block_bcgt_user_qual} userqual ON userqual.userid = u.id AND userqual.bcgtqualificationid = ?
        LEFT OUTER JOIN {block_bcgt_bespoke_qual} bespokequal ON bespokequal.bcgtqualid = userqual.bcgtqualificationid
        JOIN {role} userqualrole ON userqualrole.id = userqual.roleid AND role.shortname = ? 
        JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = userqual.bcgtqualificationid AND coursequal.courseid = course.id
		";
                
//                 $sql .= " LEFT OUTER JOIN 
//                (
//                    
//                    SELECT b.id, b.ranking, b.targetgrade, u.id as userid, b.unitsscorelower
//                    FROM {block_bcgt_target_breakdown} b
//                    INNER JOIN {block_bcgt_user_course_trgts} ct ON ct.teacherset_breakdownid = b.id
//                    INNER JOIN {user} user ON u.id = ct.userid
//
//                ) AS teacherSetBreakdown ON teacherSetBreakdown.userid = u.id";
        
        $sql .= " LEFT OUTER JOIN 
            (
                SELECT b.id, b.ranking, b.targetgrade, u.id AS userid, b.unitsscorelower
                FROM {block_bcgt_target_breakdown} b 
                INNER JOIN {block_bcgt_stud_course_grade} grade ON grade.recordid = b.id
                INNER JOIN {user} u ON u.id = grade.userid 
                WHERE grade.location = ? AND grade.type = ? AND grade.qualid = ?
            ) AS teacherSetBreakdown ON teacherSetBreakdown.userid = u.id";
        
//                 $sql .= " LEFT OUTER JOIN 
//                (
//                    
//                    SELECT t.id, t.ranking, t.grade, u.id as userid
//                    FROM {block_bcgt_target_grades} t
//                    INNER JOIN {block_bcgt_user_course_trgts} ct ON ct.teacherset_targetid = t.id
//                    INNER JOIN {user} user ON u.id = ct.userid
//
//                ) AS teacherSetTarget ON teacherSetTarget.userid = u.id";
        
        $sql .= " LEFT OUTER JOIN 
            (
                SELECT t.id, t.ranking, t.grade, u.id AS userid
                FROM {block_bcgt_target_grades} t 
                INNER JOIN {block_bcgt_stud_course_grade} grade ON grade.recordid = t.id
                INNER JOIN {user} u ON u.id = grade.userid 
                WHERE grade.location = ? AND grade.type = ? AND grade.qualid = ?
            ) AS teacherSetTarget ON teacherSetTarget.userid = u.id";
                 
                 $sql .= " LEFT OUTER JOIN
                            (

                                SELECT userid, qualid, type, recordid, location
                                FROM {block_bcgt_stud_course_grade}

                            ) as scg ON (scg.userid = u.id AND scg.qualid = ? AND type = 'aspirational') ";
                    
//                $sql .= " LEFT OUTER JOIN 
//                (
//                    
//                    SELECT b.targetgrade, u.id as userid, b.unitsscorelower
//                    FROM {block_bcgt_target_breakdown} b
//                    INNER JOIN {block_bcgt_user_course_trgts} ct ON ct.teacherset_predictedid = b.id
//                    INNER JOIN {user} user ON u.id = ct.userid
//
//                ) AS teacherSetPredicted ON teacherSetPredicted.userid = u.id"; 
        if($hasUnits)
        {
            
            $count = 0;
            //so get all of the unit values that are possible, 
            //e.g. Pass, Merit and Distinction and count the number of units
            //at each of these awards that the user has. 
            foreach($possibleUnitValues AS $unitValue)
            {
                $field = strtolower($unitValue).'count';
                $table = strtolower($unitValue).'award';
                $count++;
                $sql .= " LEFT OUTER JOIN 
                (
                    SELECT u.id AS userid, COALESCE(test$count.count, 0) AS $field 
                    FROM {user} AS u 
                    LEFT OUTER JOIN 
                    (
                        SELECT userunit.userid, count(award.award) AS count 
                        FROM {block_bcgt_user_unit} AS userunit 
                        LEFT OUTER JOIN {block_bcgt_type_award} AS award ON award.id = userunit.bcgttypeawardid 
                        WHERE userunit.bcgtqualificationid = ?
                        AND award.award = ? GROUP BY userid
                    ) AS test$count ON test$count.userid = u.id
                ) AS $table ON $table.userid = u.id";
            }
            
//            $sql .= " LEFT OUTER JOIN 
//            (
//                SELECT u.id AS userid, COALESCE(test2.count, 0) AS meritcount 
//                FROM {user} AS user 
//                LEFT OUTER JOIN 
//                (
//                    SELECT userunit.*, count(award.award) AS count 
//                    FROM {block_bcgt_user_unit} AS userunit 
//                    LEFT OUTER JOIN {block_bcgt_type_award} AS award ON award.id = userunit.bcgttypeawardid 
//                    WHERE userunit.bcgtqualificationid = ? 
//                    AND award.award = ? GROUP BY userid
//                ) AS test2 ON test2.userid = u.id
//            ) AS meritaward ON meritaward.userid = u.id";
//            $sql .= " LEFT OUTER JOIN 
//            (
//                SELECT u.id AS userid, COALESCE(test3.count, 0) AS disscount 
//                FROM {user} AS user 
//                LEFT OUTER JOIN 
//                (
//                    SELECT userunit.*, count(award.award) AS count 
//                    FROM {block_bcgt_user_unit} AS userunit 
//                    LEFT OUTER JOIN {block_bcgt_type_award} AS award ON award.id = userunit.bcgttypeawardid 
//                    WHERE userunit.bcgtqualificationid = ? 
//                    AND award.award = ? GROUP BY userid
//                ) AS test3 ON test3.userid = u.id
//            ) AS dissaward ON dissaward.userid = u.id";
            $sql .= " LEFT OUTER JOIN
            (
                SELECT count(bcgtunitid) as unitcount, userid
                FROM {block_bcgt_user_unit} AS userunit
                WHERE userunit.bcgtqualificationid = ?
                GROUP BY userid
            ) AS unitscount ON unitscount.userid = u.id";
            $sql .= " LEFT OUTER JOIN
            (
                SELECT count(bcgtunitid) as unitsawarded, userid
                FROM {block_bcgt_user_unit} AS userunit
                WHERE userunit.bcgtqualificationid = ?
                AND (userunit.bcgttypeawardid != ? AND userunit.bcgttypeawardid != ?)
                GROUP BY userid
            ) AS unitawarded ON unitawarded.userid = u.id";
        }
		$sql .= " LEFT OUTER JOIN {block_bcgt_user_award} AS useraward ON useraward.userid = u.id AND useraward.bcgtqualificationid = ? 
            AND (useraward.type = ? OR useraward.type = ? OR useraward.type = ? OR useraward.type IS NULL)";
		$sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} AS awardbreakdown ON awardbreakdown.id = useraward.bcgtbreakdownid"; 
		$sql .= " LEFT OUTER JOIN {block_bcgt_target_grades} AS awardgrade ON awardgrade.id = useraward.bcgttargetgradesid";
        $sql .= " LEFT OUTER JOIN {block_bcgt_user_course_trgts} AS coursetarget ON coursetarget.userid = u.id AND coursetarget.bcgtqualificationid = ? AND coursetarget.courseid = course.id";
        //removed: AND (coursetarget.courseid = course.id OR coursetarget.courseid = ? OR coursetarget.courseid IS NULL)
        $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} AS breakdown ON breakdown.id = coursetarget.bcgttargetbreakdownid";
		$sql .= " LEFT OUTER JOIN {block_bcgt_target_grades} AS targetgrade ON targetgrade.id = coursetarget.bcgttargetgradesid";
        $sql .= " LEFT OUTER JOIN {block_bcgt_bspk_q_grade_vals} bespokequalgrades ON bespokequalgrades.id = useraward.bcgtbreakdownid";
        if($groupingID != -1)
        {
            $sql .= ' JOIN {groups_members} members ON members.userid = u.id 
                JOIN {groupings_groups} gg ON gg.groupid = members.groupid';
        }
        $sql .= " WHERE role.shortname = ? ";
		$params = array();
        $params[] = $qualID;
        $params[] = 'student';
        
        $params[] = 'block_bcgt_target_breakdown';
        $params[] = 'aspirational';
        $params[] = $qualID;
        
        $params[] = 'block_bcgt_target_grades';
        $params[] = 'aspirational';
        $params[] = $qualID;
        
        $params[] = $qualID;
        if($hasUnits)
        {
            foreach($possibleUnitValues AS $unitValue)
            {
                $params[] = $qualID;
                $params[] = $unitValue;
            }
            $params[] = $qualID;
            $params[] = $qualID;
            $params[] = 0;
            $params[] = -1;
        }
        $params[] = $qualID;
        $params[] = 'CETA';
        $params[] = 'Predicted';
        $params[] = 'AVG';
        $params[] = $qualID;
        $params[] = 'student';
        $dbFields = $this->get_target_grade_db_fields();
        $dbTable = $dbFields->table;
        $dbType = $dbFields->type;
        $difference = ''.$dbTable.'.ranking - award'.$dbType.'.ranking';
        if($filter)
		{
			$targetFilter = 'all';
			$unitsFilter = 'all';
			foreach($filter AS $key => $filterOption)
			{
				if($key == 'target')
				{
					$targetFilter = $filterOption;
				}
				elseif($key == 'units')
				{
					$unitsFilter = $filterOption;
				} 	
			}
			if($targetFilter != 'all')
			{
				if($targetFilter == 'ahead')
				{
					$sql .= " AND $difference > ?";
				}
				elseif($targetFilter == 'behind')
				{
					$sql .= " AND $difference < ?";
				}
				else
				{
					$sql .= " AND $difference = ?";
				}
                $params[] = 0;
			}
			if($hasUnits && $unitsFilter != 'all')
			{
				if($unitsFilter == 'awarded')
				{
					$sql .= " AND COALESCE(unitscount.unitcount, 0) = COALESCE(unitawarded.unitsawarded, 0)";	
				}
				elseif($unitsFilter == 'notawarded')
				{
					$sql .= " AND COALESCE(unitscount.unitcount, 0) != COALESCE(unitawarded.unitsawarded, 0)"; 
				}
				else//none
				{
					$sql .= " AND COALESCE(unitscount.unitcount, 0) != ? AND COALESCE(unitawarded.unitsawarded, 0) = ?";
                    $params[] = 0;
                    $params[] = 0;
				}
			}
		}
        if($groupingID != -1)
        {
            $sql .= ' AND gg.groupingid = ?';
            $params[] = $groupingID;
        }
		if($sort)
		{
			$orderSql = "";
			$plural = false;
			//in case all are at 0
			$orderUsed = false;
			if(count($sort) > 0)
			{
				$orderSql .= " ORDER BY";
				if(isset($sort['username']) &&  $sort['username'] != 0)
				{
					$orderUsed = true;
					$order = 'Desc';
					if($sort['username']%2 > 0)
					{
						$order = 'Asc';
					}
					$orderSql .= " u.username $order";
					$plural = true;
				}
				if(isset($sort['firstname']) && $sort['firstname'] != 0)
				{
					$orderUsed = true;
					$order = 'Desc';
					if($sort['firstname']%2 > 0)
					{
						$order = 'Asc';
					}
					if($plural)
					{
						$orderSql .= ',';
					}
					$plural = true;
					$orderSql .= " u.firstname $order";
				}
				if(isset($sort['lastname']) && $sort['lastname'] != 0)
				{
					$orderUsed = true;
					$order = 'Desc';
					if($sort['lastname']%2 > 0)
					{
						$order = 'Asc';
					}
					if($plural)
					{
						$orderSql .= ',';
					}
					$plural = true;
					$orderSql .= " u.lastname $order";
				}
				if(isset($sort['targetgrade']) && $sort['targetgrade'] != 0)
				{
					$orderUsed = true;
					$order = 'Desc';
					if($sort['targetgrade']%2 > 0)
					{
						$order = 'Asc';
					}
					if($plural)
					{
						$orderSql .= ',';
					}
					$plural = true;
					$orderSql .= " $dbTable.ranking $order";
				}
                                
                if(isset($sort['tstarget']) && $sort['tstarget'] != 0)
				{
					$orderUsed = true;
					$order = 'Desc';
					if($sort['tstarget']%2 > 0)
					{
						$order = 'Asc';
					}
					if($plural)
					{
						$orderSql .= ',';
					}
					$plural = true;
                    $orderField = 'teacherSet'.$dbTable.'.ranking';
					$orderSql .= " $orderField $order";
				}                
//                if($sort['tspred'] != 0)
//				{
//					$orderUsed = true;
//					$order = 'Desc';
//					if($sort['tspred'] > 0)
//					{
//						$order = 'Asc';
//					}
//					if($plural)
//					{
//						$orderSql .= ',';
//					}
//					$plural = true;
//					$orderSql .= " tspredlowerscore $order";
//				}
                                
				if(isset($sort['qaward']) && $sort['qaward'] != 0)
				{
					$orderUsed = true;
					$order = 'Desc';
					if($sort['qaward']%2 > 0)
					{
						$order = 'Asc';
					}
					if($plural)
					{
						$orderSql .= ',';
					}
					$plural = true;
                    $orderField = 'award'.$dbTable.'.ranking';
					$orderSql .= " $orderField $order";
				}
				if(isset($sort['target']) && $sort['target'] != 0)
				{
					$orderUsed = true;
					$order = 'Desc';
					if($sort['target']%2 > 0)
					{
						$order = 'Asc';
					}
					if($plural)
					{
						$orderSql .= ',';
					}
					$plural = true;
					$orderSql .= " ($difference) $order";
				}
				if($hasUnits && isset($sort['awarded']) && $sort['awarded'] != 0)
				{
					$orderUsed = true;
					$order = 'Desc';
					if($sort['awarded']%2 > 0)
					{
						$order = 'Asc';
					}
					if($plural)
					{
						$orderSql .= ',';
					}
					$plural = true;
					$orderSql .= " COALESCE(unitawarded.unitsawarded, 0) $order";
				}
				if($orderUsed)
				{
					$sql .= $orderSql;
				}
			}
		}
        else
        {
            $sql .= ' ORDER BY course.shortname ASC, lastname ASC';
        }
		//$sql .= " AND coursetarget.courseid = $courseID";
		//echo $sql;
                
		return $DB->get_records_sql($sql, $params);	
	}
    
    
    
    
    /**
	 * Builds the image for the columns in My Qualification
	 * Ascending and descending values. -1 for desc
	 * and 1 for asc
	 * @param unknown_type $columnValue
	 */
	protected function build_sort_image($columnValue)
	{
		global $CFG;
        if($columnValue == -1)
		{
			return "";
		}
		elseif($columnValue%2 > 0)
		{
			return "<img src='$CFG->wwwroot/blocks/bcgt/pix/asc.gif'>";
		}
		else
		{
			return "<img src='$CFG->wwwroot/blocks/bcgt/pix/desc.gif'>";
		}
	}

    /**
	 * Gets the units on the qualification by course with the sort array
	 * gets back the unit details
	 * the number of students awarded
	 * the number of students doing the unit
	 * the number of students with a diss, merit and pass
	 * @param unknown_type $courseID
	 * @param unknown_type $qualID
	 * @param unknown_type $sort
	 */
	protected function get_units_course_qual_report($qualID, $courseID, $sort, $groupingID = -1)
	{
        $possibleUnitValues = $this->get_possible_unit_awards();
		global $DB;
		$sql = "SELECT distinct(units.id), units.name, studentsdoing.count AS students, 
		studentsawarded.count AS studentsawarded, course.id as courseid, course.shortname as courseshortname,
        course.fullname as coursefullname";
        foreach($possibleUnitValues AS $unitValue)
        {   
                    $table = ''.strtolower($unitValue).'count';
            $sql .= ', '.$table.'.count AS '.$table;
        }
        $sql .= " FROM {block_bcgt_unit} AS units ";
		$sql .= " JOIN {block_bcgt_qual_units} AS qualunits ON qualunits.bcgtunitid = units.id";
        $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qualunits.bcgtqualificationid";
		$sql .= " JOIN {course} course ON course.id = coursequal.courseid ";
        $sql .= " JOIN 
		(
			SELECT unit.id, COALESCE(studentcount.count, 0) AS count 
			FROM {block_bcgt_unit} AS unit
			JOIN {block_bcgt_qual_units} AS qualunits ON qualunits.bcgtunitid = unit.id 
			LEFT OUTER JOIN 
			(
	 			SELECT userunit.bcgtunitid AS unitid, 
	 			count(userunit.userid) AS count
				FROM {block_bcgt_user_unit} AS userunit 
				JOIN {block_bcgt_qual_units} AS qualunits ON qualunits.bcgtunitid = userunit.bcgtunitid
				JOIN {role_assignments} AS role_ass ON role_ass.userid = userunit.userid 
				JOIN {role} AS role ON role.id = role_ass.roleid 
				JOIN {context} AS context ON context.id = role_ass.contextid 	
				JOIN {course} AS course ON course.id = context.instanceid
				JOIN {block_bcgt_course_qual} AS coursequal ON coursequal.courseid = course.id AND coursequal.bcgtqualificationid = qualunits.bcgtqualificationid 
				";
                if($groupingID != -1)
                {
                    $sql .= " JOIN {groups_members} members ON members.userid = userunit.userid 
                        JOIN {groupings_groups} gg ON gg.groupid = members.groupid";
                }
                $sql .= " WHERE qualunits.bcgtqualificationid = ?"; 
                if($groupingID != -1)
                {
                    $sql .= " AND gg.groupingid = ?";
                }
				$sql .= " GROUP BY userunit.bcgtunitid
			)AS studentcount ON studentcount.unitid = unit.id
			WHERE qualunits.bcgtqualificationid = ? 
		) AS studentsdoing ON studentsdoing.id = units.id";
		$sql .= " JOIN 
		(
			SELECT unit.id, COALESCE(studentcount.count, 0) AS count 
			FROM {block_bcgt_unit} AS unit
			JOIN {block_bcgt_qual_units} AS qualunits ON qualunits.bcgtunitid = unit.id 
			LEFT OUTER JOIN 
			(
	 			SELECT userunit.bcgtunitid AS unitid, 
	 			count(userunit.userid) AS count
				FROM {block_bcgt_user_unit} AS userunit 
				JOIN {block_bcgt_qual_units} AS qualunits ON qualunits.bcgtunitid = userunit.bcgtunitid
				JOIN {role_assignments} AS role_ass ON role_ass.userid = userunit.userid 
				JOIN {role} AS role ON role.id = role_ass.roleid 
				JOIN {context} AS context ON context.id = role_ass.contextid 	
				JOIN {course} AS course ON course.id = context.instanceid
				JOIN {block_bcgt_course_qual} AS coursequal ON coursequal.courseid = course.id AND coursequal.bcgtqualificationid = qualunits.bcgtqualificationid 
				";
                if($groupingID != -1)
                {
                    $sql .= " JOIN {groups_members} members ON members.userid = userunit.userid
                        JOIN {groupings_groups} gg ON gg.groupid = members.groupid";
                }
                $sql .= " WHERE qualunits.bcgtqualificationid = ? AND userunit.bcgttypeawardid != ? AND userunit.bcgttypeawardid != ? AND userunit.bcgttypeawardid IS NOT NULL 
				"; 
                if($groupingID != -1)
                {
                    $sql .= " AND gg.groupingid = ?";
                }
                $sql .= " GROUP BY userunit.bcgtunitid
			)AS studentcount ON studentcount.unitid = unit.id
			WHERE qualunits.bcgtqualificationid = ? 
		) AS studentsawarded ON studentsawarded.id = units.id";
		
        foreach($possibleUnitValues AS $unitValue)
        {   
            $table = ''.strtolower($unitValue).'count';            
            $sql .= " JOIN 
            (
                SELECT unit.id, COALESCE(studentcount.count, 0) AS count 
                FROM {block_bcgt_unit} AS unit
                JOIN {block_bcgt_qual_units} AS qualunits ON qualunits.bcgtunitid = unit.id 
                LEFT OUTER JOIN 
                (
                    SELECT userunit.bcgtunitid AS unitid, 
                    count(userunit.userid) AS count
                    FROM {block_bcgt_user_unit} AS userunit 
                    JOIN {block_bcgt_qual_units} AS qualunits ON qualunits.bcgtunitid = userunit.bcgtunitid
                    JOIN {role_assignments} AS role_ass ON role_ass.userid = userunit.userid 
                    JOIN {role} AS role ON role.id = role_ass.roleid 
                    JOIN {context} AS context ON context.id = role_ass.contextid 	
                    JOIN {course} AS course ON course.id = context.instanceid
                    JOIN {block_bcgt_course_qual} AS coursequal ON coursequal.courseid = course.id AND coursequal.bcgtqualificationid = qualunits.bcgtqualificationid 
                    LEFT OUTER JOIN {block_bcgt_type_award} AS award ON award.id = userunit.bcgttypeawardid
                    ";
                if($groupingID != -1)
                {
                    $sql .= " JOIN {groups_members} members ON members.userid = userunit.userid
                        JOIN {groupings_groups} gg ON gg.groupid = members.groupid";
                }
                $sql .= " WHERE qualunits.bcgtqualificationid = ? AND award.award = ?";
                if($groupingID != -1)
                {
                    $sql .= " AND gg.groupingid = ?";
                }
                $sql .= " GROUP BY userunit.bcgtunitid
                )AS studentcount ON studentcount.unitid = unit.id
                WHERE qualunits.bcgtqualificationid = ? 
            ) AS $table ON $table.id = units.id";
        }
		$sql .= " WHERE qualunits.bcgtqualificationid = ?";
        $params = array();
        //
        $params[] = $qualID;
        if($groupingID != -1)
        {
            $params[] = $groupingID;
        }
        $params[] = $qualID;
        $params[] = $qualID;
        $params[] = 0;
        $params[] = -1;
        if($groupingID != -1)
        {
            $params[] = $groupingID;
        }
        $params[] = $qualID;
        foreach($possibleUnitValues AS $unitValue)
        {  
            if($groupingID != -1)
            {
                $params[] = $groupingID;
            }
            $params[] = $qualID;
            $params[] = $unitValue;
            $params[] = $qualID;
        }
        $params[] = $qualID;
		if($sort)
		{
			$orderSql = "";
			$plural = false;
			$orderUsed = false;
			if(count($sort) > 0)
			{
				$orderSql .= " ORDER BY";
				if(isset($sort['unitname']) &&  $sort['unitname'] != 0)
				{
					$orderUsed = true;
					$order = 'Desc';
					if($sort['unitname']%2 > 0)
					{
						$order = 'Asc';
					}
					$orderSql .= " units.name $order";
					$plural = true;
				}
				if(isset($sort['awarded']) && $sort['awarded'] != 0)
				{
					$orderUsed = true;
					$order = 'Desc';
					if($sort['awarded']%2 > 0)
					{
						$order = 'Asc';
					}
					if($plural)
					{
						$orderSql .= ',';
					}
					$plural = true;
					$orderSql .= " studentsawarded.count $order";
				}
				if($orderUsed)
				{
					$sql .= $orderSql;
				}
			}
			
		}
		return $DB->get_records_sql($sql, $params);
	}
        
    public function get_target_grade_db_fields()
    {
        $retval = new stdClass();
        $retval->idField = 'bcgtbreakdownid';
        $retval->type = 'breakdown';
        $retval->gradeField = 'targetgrade';
        $retval->weightedIdField = 'bcgtweightedbreakdownid';
        $retval->teacherSetIdField = 'teacherset_breakdownid';
        $retval->table = 'breakdown';
        $retval->aspLocation = 'asplocation';
        $retval->aspRecordID = 'asprecordid';
        return $retval;
    }
    
    protected function get_simple_qual_report_student($userID, $editing, $filterArray, $sortArray, $groupingID = -1)
    {
        global $DB, $COURSE, $OUTPUT;
        $type = optional_param('type', '', PARAM_TEXT);
        $cID = optional_param('cID', -1, PARAM_INT);
        if($cID != -1)
        {
            $context = context_course::instance($cID);
        }
        else
        {
            $context = context_course::instance($COURSE->id);
        }
        $retval = '';
        $retval .= '<form>';
        $retval .= '<h3>'.get_string('student', 'block_bcgt');
        $retval .= '</h3>';
        $retval .= '</form>';
        $targetFilter = 'all';
		$unitsFilter = 'all';
		foreach($filterArray AS $key => $filterOption)
		{
			//work out the filter options. 
			if($key == 'target')
			{
				//target is 'all, behind, ahead, or on'
				$targetFilter = $filterOption;
			}
			elseif($key == 'units')
			{
				//units is 'all, none ect'
				$unitsFilter = $filterOption;
			} 	
		}
		global $CFG;
		//for each course get the students
		$formName = "qSRQ".$this->id."";
		//anchor so we can test if we have loaded the report before or not
		$retval .= "<span id='cLoadedQ$this->id'></span>";
		$retval .= "<form method='POST' id='qualStudentReport' name='$formName' action=''>";
		$retval .= "<input type='hidden' name='view' value='s'/>";
        $qualIDToUse = $this->id;
        $filterID = $this->id;
        if($groupingID != -1)
        {
            //we know therefore that we are looking at the tabs with the groupings
            //not the quals. 
            $qualIDToUse = -1;
            $filterID = $groupingID;
        }
        $hasUnits = false;
        if($this->has_units())
        {
            $hasUnits = true;
            $retval .= "<label for='units'>".get_string('units', 'block_bcgt')."</label>".
                    "<select class='unitFilter' course='".$cID."' group='".$groupingID."' tabtype='".$type."' tab='s' id='uf_".$filterID."' qual='".$qualIDToUse."' name='units'>";
            //create the filter
            $unitsFilterOptions = $this->get_units_report_filter();
            foreach($unitsFilterOptions AS $key=>$filterOption)
            {
                $selected = "";
                if($unitsFilter == $key)
                {
                    $selected = 'selected';
                }
                $retval .= "<option $selected value='$key'>$filterOption</option>";
            }
            $retval .= "</select>";
        }
		$targetGrade = false;
        $editTarget = false;
        if(get_config('bcgt', 'showtargetgrades') && has_capability('block/bcgt:viewtargetgrade', $context))
        {
            $targetGrade = true;
            //can they edit/add a target grade for the student??
            if(has_capability('block/bcgt:edittargetgrade', $context))
            {
                //then add an edit button
                $editTarget = true;
            }
        }
        $editAsp = false;
        $aspGrade = false;
        if(get_config('bcgt', 'showaspgrades') && has_capability('block/bcgt:viewaspgrade', $context))
        {
            //if they are using alsp
            //then allow them to use an aspirational grade
            // Aspirational Targets
            $aspGrade = true;
            if(has_capability('block/bcgt:editasptargetgrade', $context))
            {
                //then add an edit button
                $editAsp = true;
            }
        }
        //add the filter to the page about selting ahead or behind target
        if($targetGrade || $aspGrade)
        {
            $retval .= "<label for='targets'>".get_string('targets', 'block_bcgt')."</label>".
                    "<select class='targetFilter' course='".$cID."' group='".$groupingID."' tabtype='".$type."' tab='s' id='tf_".$filterID."' qual='".$qualIDToUse."' name='targets'>";
            $targetFilterOptions = $this->get_target_report_filter();
            foreach($targetFilterOptions AS $key=>$filterOption)
            {
                $selected = "";
                if($targetFilter == $key)
                {
                    $selected = 'selected';
                }
                $retval .= "<option $selected value='$key'>$filterOption</option>";
            }
            $retval .= "</select>"; 
        }
        $targetGradeOptions = null;
        $retval .= '<input type="hidden" name="editing" id="editing" value="'.$editing.'"/>';
        $sorting = '';
        foreach($sortArray AS $key=>$count)
        {
            for($i=1;$i<=$count;$i++)
            {
                $sorting .= $key.',';
            }
        }
        $retval .= '<input type="hidden" name="sorting" id="sorting" value="'.$sorting.'"/>';
        //are we using breakdown or target grade table?
        $dbFields = $this->get_target_grade_db_fields();
        $dbField = $dbFields->idField;
        $dbType = $dbFields->type;
        $dbGradeField = $dbFields->gradeField;
        $dbTeacherSetField = $dbFields->teacherSetIdField;
        //if we can edit the target grade then we need a button that says, edit/view 
        if($editTarget || $editAsp)
        {
//            $dbWeightedSetField = $dbFields->weightedIdField;
            $string = 'edittargetgrades';
            $id = 'edit';
            if($editing)
            {
                $string = 'viewnonedit';
                $id = 'view';
            }
            $retval .= '<input type="submit" course="'.$cID.'" qual="'.$qualIDToUse.'" group="'.$groupingID.'" tabtype="'.$type.'" tab="s" id="'.$id.'" class="'.$id.'" name="edit" value="'.get_string($string,'block_bcgt').'"/>';
            if($editing)
            {
                //if we are editing then get all of the grades. 
                //either the target grades or the breakdowns. Different database tables. 
                $targetGradeOptions = $this->get_possible_target_grades();
                if($targetGradeOptions)
                {
                    if($dbField == 'bcgttargetgradesid' && isset($targetGradeOptions->targetgrades))
                    {
                        $options = $targetGradeOptions->targetgrades;
                        $field = 'grade';
                    }
                    elseif($dbField == 'bcgtbreakdownid' && isset($targetGradeOptions->breakdowns))
                    {
                        $options = $targetGradeOptions->breakdowns;
                        $field = 'targetgrade';
                    }
                }
                
                //because the selects are all basically the same. They all have the same grades in them.
                //we dont want to do multiple fors for exactly the same thing. 
                //so do it once.
                //build the string up. 
                //then later we will try and do a string replace on the selected. 
                
                $selectTarget = '<select cidsid type="'.$dbType.'" course="'.$cID.'" qual="'.$this->id.'" id="t_'.$this->id.'" group="'.$groupingID.'" class="edittarget" name="target">';
                $selectAsp = '<select cidsid type="asp'.$dbType.'" course="'.$cID.'" qual="'.$this->id.'" id="a_'.$this->id.'" group="'.$groupingID.'" class="editasp" name="asp">';
                $targetSelects = $this->build_select($selectTarget, $options, 'id', $field);
                $aspSelects = $this->build_select($selectAsp, $options, 'id', $field);
            }
            
        }
		//Go and get all of the students PLUS all of the data for this qual and
		//course
		$students = $this->get_students_course_qual_report($this->id, $filterArray, $sortArray, $groupingID);  
		//default/reset the sort on the top of the columns. 
		$sortUsername = -1;
		$sortFirstName = -1;
		$sortLastName = -1;
		$sortTargetA = -1;
		$sortQual = -1;
		$sortTarget = -1;
		$sortAwarded = -1;
        $sortTSTarget = -1;
                
		if($sortArray && !empty($sortArray))
		{
			$sortUsername = isset($sortArray['username'])? $sortArray['username'] : -1;
			$sortFirstName = isset($sortArray['firstname'])? $sortArray['firstname'] : -1;
			$sortLastName = isset($sortArray['lastname'])? $sortArray['lastname'] : -1;
			$sortTargetA = isset($sortArray['targetgrade'])? $sortArray['targetgrade'] : -1;
			$sortQual = isset($sortArray['qaward'])? $sortArray['qaward'] : -1;
			$sortTarget = isset($sortArray['target'])? $sortArray['target'] : -1;
			$sortAwarded = isset($sortArray['awarded'])? $sortArray['awarded'] : -1;
            $sortTSTarget = isset($sortArray['tstarget'])? $sortArray['tstarget'] : -1;
		}
        
        $retval .= "<table align='center' class='simplequalreports'>";
            
        $header = "<tr>";
        $columns = array('picture', 'username','name');
        //need to get the global config record
        //for each column build it up with an input and 
		//an image to show if it is currently being sorted ASC or DESC
        $configColumns = get_config('bcgt','btecgridcolumns');
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
            $columns = array_map('trim', $columns);
        }
        $header .= '<th></th>'; //one for the block of colour
        if(in_array('picture', $columns))
        {
            $header .= '<th></th>';
        }
        if(in_array('username', $columns))
        {
            $header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                    "sortname='username' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('username', 'block_bcgt')."</a></th>";		
            $header .= "<th class='sort'>";
            $header .= $this->build_sort_image($sortUsername);
            $header .= "</th>"; 
        }
        if(in_array('name', $columns))
        {
            $header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                    "sortname='firstname' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('firstname', 'block_bcgt')."</a> 
            / <a class='sorthead' qual='".$qualIDToUse."' group='".$groupingID."' tabtype='".$type."' tab='s' sortname='lastname' href='#q".$this->id."c'>".get_string('lastname', 'block_bcgt')."</a></th>";
            $header .= "<th class='sort'>";
            $header .= $this->build_sort_image($sortFirstName);
            $header .= $this->build_sort_image($sortLastName);
            $header .= "</th>";
        }
        if($targetGrade)
        {
            $header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                    "sortname='targetgrade' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('targetgrade', 'block_bcgt')."</a></th>";
            $header .= "<th class='sort'>";
            $header .= $this->build_sort_image($sortTargetA);
            $header .= "</th>";
        }
        if($aspGrade)
        {
            $header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                    "sortname='tstarget' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('asptargetgrades', 'block_bcgt')."</a></th>";
            $header .= "<th class='sort'>";
            $header .= $this->build_sort_image($sortTSTarget);
            $header .= "</th>";
        }        		
		$header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                "sortname='qaward' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('qualaward', 'block_bcgt')."</a></th>";
		$header .= "<th class='sort'>";
		$header .= $this->build_sort_image($sortQual);
		$header .= "</th>";
		
        $useVa = false;
        if($targetGrade || $aspGrade)
        {
            $useVa = true;
            $header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                    "sortname='target' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('va', 'block_bcgt')."</a></th>";
            $header .= "<th class='sort'>";
            $header .= $this->build_sort_image($sortTarget);
            $header .= "</th>";
        }

        if($hasUnits)
        {
            $header .= "<th><a class='sorthead' group='".$groupingID."' tabtype='".$type."' ".
                    "sortname='awarded' course='".$cID."' qual='".$qualIDToUse."' tab='s' href='#q".$this->id."c'>".get_string('nounitsawarded', 'block_bcgt')."</a></th>";
            $header .= "<th class='sort'>";
            $header .= $this->build_sort_image($sortAwarded);
            $header .= "</th>";
            $possibleUnitValues = $this->get_possible_unit_awards();
            foreach($possibleUnitValues AS $unitValue)
            {
                $string = 'no'.strtolower($unitValue);
                $header .= "<th colspan='2'>".get_string($string, 'block_bcgt')."</th>";
            }
            
            // BTEC - PMDs
            if ($this->get_family() == 'BTEC')
            {
                $header .= "<th>No. P</th>";
                $header .= "<th>No. M</th>";
                $header .= "<th>No. D</th>";
            }

//            $retval .= "<th colspan='2'>".get_string('nomerit', 'block_bcgt')."</th>";
//
//            $retval .= "<th colspan='2'>".get_string('nopass', 'block_bcgt')."</th>";
        }
		
		$header .= "<th>Grid</th>";
		$header .= "</tr>";
                
		//end the head
		if($students)
		{
			$count = 0;
			//for each student output the record
            $lastCourse = '';
			foreach($students AS $student)
            {
                $onTarget = '';
                $diff = 0;
                $userVA = new UserVa();
                if($useVa)
                {
                    $qualAwardRankingField = ''.$dbType.'awardranking';
                    $targetRankingField = $dbGradeField.'ranking';
                    //if useVA means we are using TargetGrade or ASP Grade and so 
                    //we want to be able to check the ahead/behind.
                    $diff = $userVA->get_diff($student->$targetRankingField, $student->$qualAwardRankingField);
                    $onTarget = $userVA->ahead_behind_on($student->$targetRankingField, $student->$qualAwardRankingField);
                }
                //check if we are displaying a new course (quals can be on more than one course)
                if($lastCourse != $student->courseid)
                {
                    $retval.= '</table><h4 class="courserow">'.get_string('course').' = '.$student->coursefullname.' : '.$student->courseshortname.'</h4><table>';
                    $retval .= $header;
                }
                $lastCourse = $student->courseid;
				$class = 'even';
				$count++;
				if($count%2)
				{
					$class = 'odd';
				}
				$retval .=  "<tr class='$class $onTarget'>";
                $retval .= "<td class='status $onTarget'></td>";//one for the block of colour
                if(in_array('picture', $columns))
                { 
                    $user = new stdClass;
                    $user = $student;
                    $user->id = $student->userid;
//                    $user->email = $student->email;
//                    $user->picture = $student->picture;
//                    $user->firstname = $student->firstname;
//                    $user->lastname = $student->lastname;
//                    $user->imagealt = $student->imagealt;
                    //'picture', 'firstname', 'lastname', 'imagealt', 'email'
                    $retval .= "<td>".$OUTPUT->user_picture($user)."</td>";
                }     
                if(in_array('username', $columns))
                { 
                    $retval .= "<td colspan='2'>$student->username</td>";
                }
                if(in_array('name', $columns))
                {
                    $retval .= "<td colspan='2'>$student->firstname $student->lastname</td>";
                }
                
                //Editing of the target grades
                //is saved upon change. 
                //this is loaded in the javascript file
                //this javascipt file is loaded in DashTab.class , function 
                //bcgt_tab_get_trackers_tab (loaded from my_dashboard.php)
                //the function in block_bcgt.js
                //is inittrackerstab
                //this function loads /blocks/bcgt/ajax/get_simple_qual_report.php
                //this file returns this function we are in now (eventually)
                
                //In the javascript function above it also calls an ApplyTT type of function
                //this puts a JQuery listener on the selects. 
                //this calls
                //blocks/bcgt/ajax/update_user_target.php";
                //which saves the target grades
                
                
                if($targetGrade)
                {
                    //so are we editing? are we editing the Target?
                    if($editing && $editTarget)
                    {
                        //we are now going to do some string replaces. 
                        //first we need to remove the generic id's from the select name
                        //and put in the students id and the course id. 
                        $targetSelectsStu = str_replace('cidsid', 'sid="'.$student->userid.'" cid="'.$student->courseid.'"', $targetSelects);
                        $targetSelectsStu = str_replace('id="t_'.$this->id.'"', 'id="t_'.$this->id.'_s_'.$student->userid.'"', $targetSelectsStu);
                        if($student->$dbField)
                        {
                            //if we have a databaseField set (e.g. breakdown or grades id) (in other words)
                            //we already have a grade set, so we need to change the drop down to
                            //be set to this. 
                            //then we need to replace the selectedDatabaseID with just selected. 
                            //so it gets selected. 
                            $targetSelectsStu = str_replace('selected'.$student->$dbField, 'selected', $targetSelectsStu);
                        }
                        $retval .= '<td colspan="2">'.$targetSelectsStu.'</td>';
                    }
                    else
                    {
                        //lets just display it!
                        if(!$student->$dbGradeField || $student->$dbGradeField == '')
                        {
                            $userPriorLearn = new UserPriorLearning();
                            $targetGradeOut = $userPriorLearn->get_users_prlearn_status_when_no_target($student->userid);
                        }
                        else
                        {
                            $targetGradeOut = $student->$dbGradeField;
                        }
                        $retval .= "<td colspan='2'>$targetGradeOut</td>";
                    }
                    
                }
				if($aspGrade)
                {
                    
                    // Aspirational grades are stored in different location
                    $retval .= "<td colspan='2'>";
                    
                        $aspirationalGrade = bcgt_get_aspirational_target_grade($student->userid, $this->id);
                        if ($aspirationalGrade)
                        {
                            $aspirationalGrade = reset($aspirationalGrade);
                        }
                    
                        if ($editing && $editAsp)
                        {
                            
                            $possibleGrades = \bcgt_get_qual_possible_grades($this);
                            $retval .= "<select id='edit_asp_select_{$student->id}_{$this->id}' class='update_asp_grade' studentid='{$student->userid}' qualid='{$this->id}'>";
                            $retval .= "<option value=''></option>";
                                if ($possibleGrades)
                                {
                                    foreach($possibleGrades as $possibleGrade)
                                    {
                                        $sel = ($aspirationalGrade && $aspirationalGrade->id == $possibleGrade['id']) ? 'selected' : '';
                                        $retval .= "<option value='{$possibleGrade['location']}:{$possibleGrade['id']}' {$sel} >{$possibleGrade['grade']}</option>";
                                    }
                                }
                            $retval .= "</select>";
                            
                        }
                        else
                        {
                    
                            if ($aspirationalGrade){
                                $retval .= $aspirationalGrade->grade;
                            }
                            
                        }
                    
                    $retval .= "</td>";
                    
                    
//                    //this is a mirror of above but for the aspirational grade. 
//                    if($editing && $editAsp)
//                    {
//                        $aspSelectsStu = str_replace('cidsid', 'sid="'.$student->userid.'" cid="'.$student->courseid.'"', $aspSelects);
//                        $aspSelectsStu = str_replace('id="a_'.$this->id.'"', 'id="a_'.$this->id.'_s_'.$student->userid.'"', $aspSelectsStu);
//                        if($student->$dbTeacherSetField)
//                        {
//                            $aspSelectsStu = str_replace('selected'.$student->$dbTeacherSetField, 'selected', $aspSelectsStu);
//                        }
//                        $retval .= '<td colspan="2">'.$aspSelectsStu.'</td>';
//                    }
//                    else
//                    {
//                    }
                }
                $qualAwardField = ''.$dbType.'award';
				$retval .= "<td colspan='2'>".$student->$qualAwardField."</td>";
                if($useVa)
                {
                    $retval .= "<td colspan='2'>".$diff."</td>";
                }
                if($hasUnits)
                {
                    $retval .= "<td colspan='2'>$student->unitsawarded / $student->unitcount</td>";
                    $possibleUnitValues = $this->get_possible_unit_awards();
                    foreach($possibleUnitValues AS $unitValue)
                    {
                        $field = strtolower($unitValue).'count';
                        $retval .= "<td colspan='2'>".$student->$field."</td>";
                    }
                    
                    // BTEC
                    if ($this->get_family() == 'BTEC')
                    {
                        
                        // P
                        $countP = $DB->count_records_sql("select count(c.id)
                                                            from {block_bcgt_user_criteria} uc
                                                            inner join {block_bcgt_criteria} c on c.id = uc.bcgtcriteriaid
                                                            inner join {block_bcgt_value} v ON v.id = uc.bcgtvalueid
                                                            where uc.userid = ? and c.name LIKE 'P%' and v.specialval = 'A' and uc.bcgtqualificationid = ?", array($student->userid, $this->id));
                        
                        $countPTotal = $DB->count_records_sql("select count(c.id)
                                                                from {block_bcgt_criteria} c
                                                                inner join {block_bcgt_user_unit} uu ON uu.bcgtunitid = c.bcgtunitid
                                                                where uu.bcgtqualificationid = ? AND c.name LIKE 'P%' AND uu.userid = ?", array($this->id, $student->userid));
                        
                        // M
                        $countM = $DB->count_records_sql("select count(c.id)
                                                            from {block_bcgt_user_criteria} uc
                                                            inner join {block_bcgt_criteria} c on c.id = uc.bcgtcriteriaid
                                                            inner join {block_bcgt_value} v ON v.id = uc.bcgtvalueid
                                                            where uc.userid = ? and c.name LIKE 'M%' and v.specialval = 'A' and uc.bcgtqualificationid = ?", array($student->userid, $this->id));
                        
                        $countMTotal = $DB->count_records_sql("select count(c.id)
                                                                from {block_bcgt_criteria} c
                                                                inner join {block_bcgt_user_unit} uu ON uu.bcgtunitid = c.bcgtunitid
                                                                where uu.bcgtqualificationid = ? AND c.name LIKE 'M%' AND uu.userid = ?", array($this->id, $student->userid));
                       
                        // P
                        $countD = $DB->count_records_sql("select count(c.id)
                                                            from {block_bcgt_user_criteria} uc
                                                            inner join {block_bcgt_criteria} c on c.id = uc.bcgtcriteriaid
                                                            inner join {block_bcgt_value} v ON v.id = uc.bcgtvalueid
                                                            where uc.userid = ? and c.name LIKE 'D%' and v.specialval = 'A' and uc.bcgtqualificationid = ?", array($student->userid, $this->id));
                        
                        $countDTotal = $DB->count_records_sql("select count(c.id)
                                                                from {block_bcgt_criteria} c
                                                                inner join {block_bcgt_user_unit} uu ON uu.bcgtunitid = c.bcgtunitid
                                                                where uu.bcgtqualificationid = ? AND c.name LIKE 'D%' AND uu.userid = ?", array($this->id, $student->userid));
                        
                                                
                        $retval .= "<td>{$countP}/{$countPTotal}</td>";
                        $retval .= "<td>{$countM}/{$countMTotal}</td>";
                        $retval .= "<td>{$countD}/{$countDTotal}</td>";
                        
                    }
                    
                    
                }
				$retval .= "<td><a href='{$CFG->wwwroot}/blocks/bcgt/grids/student_grid.php?qID=$this->id&sID=$student->userid&cID=$cID'>View Grid</a></td>";
				$retval .= "</tr>";
			}//end loop students
			$retval .= "</table>";
			$retval .= "<p>".get_string('simplereportsortinst', 'block_bcgt')."</p>";
		}//end students
		else
		{
			$retval .= "</table>";
			$retval .= "<p>".get_string('nostudentsfound', 'block_bcgt')."</p>";
		}
		
		$retval .= "</form>";
        return $retval;
    }  
    
    protected function build_select($selectString, $options, $idField, $optionField)
    {
        $retval = $selectString;
        $retval .= '<option value="-1">'.get_string('pleaseselectblank', 'block_bcgt').'</option>';
        foreach($options AS $option)
        {
            $retval .= '<option selected'.$option->$idField.' value="'.$option->$idField.'">';
            $retval .= $option->$optionField;
            $retval .= '</option>';
        }
        $retval .= '</select>';
        return $retval;
    }
    
    public function get_possible_target_grades($dbobject = 'db')
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE bcgttargetqualid = ? ORDER BY ranking DESC";
        $breakdowns = $DB->get_records_sql($sql,array($this->bcgtTargetQualID));
        
        $sql = "SELECT * FROM {block_bcgt_target_grades} WHERE bcgttargetqualid = ? ORDER BY ranking DESC";
        $targetGrades = $DB->get_records_sql($sql, array($this->bcgtTargetQualID));
        $retval = new stdClass();
        if($dbobject == 'db')
        {
            $retval->breakdowns = $breakdowns;
            $retval->targetgrades = $targetGrades;
        }
        else
        {
            $breakdownObjects = array();
            if($breakdowns)
            {
                foreach($breakdowns AS $breakdown)
                {
                    $breakdownObj = new Breakdown($breakdown->id, $breakdown);
                    $breakdownObjects[$breakdown->id] = $breakdownObj;
                }
            }
            $retval->breakdowns = $breakdownObjects;
            $gradeObjects = array();
            if($targetGrades)
            {
                foreach($targetGrades AS $grade)
                {
                    $gradeObj = new TargetGrade($grade->id, $grade);
                    $gradeObjects[$grade->id] = $gradeObj;
                }
            }
            $retval->targetgrades = $gradeObjects;
        }
        return $retval;
    }
    
    protected function get_units_report_filter()
    {
        return array('all'=>'All','awarded'=>'Students with all units marked',
            'notawarded'=>'Students with units outstanding', 'none'=>'Students with alll units outstanding');
    }
    
    protected function get_target_report_filter()
    {
        return array('all'=>'All', 'behind'=>'Behind Target', 'on'=>'On Target', 'ahead'=>'Ahead of Target');
    }
    
    protected function get_simple_qual_report_unit($userID, $sort = array(),$courseID = -1, $groupingID = -1)
    {
        $retval = '';
        $retval .= '<h3>'.get_string('unit', 'block_bcgt').'</h3>';
        //for btec units there is no filter.
		global $CFG;
		$formName = "qSRQ".$this->id."C$courseID";
		//anchor so we can test if we have loaded the report before or not
		$retval .= "<span id='cLoaded".$courseID."Q$this->id'></span>";
		$retval .= "<form method='POST' name='$formName' action='#'>";
        $sorting = '';
        foreach($sort AS $key=>$count)
        {
            for($i=1;$i<=$count;$i++)
            {
                $sorting .= $key.',';
            }
        }
        $retval .= '<input type="hidden" name="usorting" id="usorting" value="'.$sorting.'"/>';
		//get the units and all of the data
		$units = $this->get_units_course_qual_report($this->id, $courseID, $sort, $groupingID);
		
		//default and reset the sort order.
		$sortName = -1;
		$sortAwarded = -1;
		if($sort && !empty($sort))
		{
			$sortName = isset($sort['unitname'])? $sort['unitname']:-1;
			$sortAwarded = isset($sort['awarded'])? $sort['awarded']:-1;;
		}
		//for each header build the sort inputs and the images for
		//ASC and DESC
		$retval .= "<table align='center'><tr><th colspan='2'></th>".
            "<th colspan='9'>".get_string('numstudents', 'block_bcgt')."</th></tr><tr>";
		$retval .= "<th><a class='usorthead' course='".$courseID."' sortname='unitname' tab='u' qual='".$this->id."' href='#q".$this->id."c$courseID'>".get_string('unitname', 'block_bcgt')."</a></th>";		
		$retval .= "<th class='sort'>";
		$retval .= $this->build_sort_image($sortName);
		$retval .= "</th>";
			
		$retval .= "<th><a class='usorthead' course='".$courseID."' sortname='awarded' tab='u' qual='".$this->id."' href='#q".$this->id."c$courseID'>".get_string('withunitdoingunit', 'block_bcgt')."</a></th>";
		$retval .= "<th class='sort'>";
		$retval .= $this->build_sort_image($sortAwarded);
		$retval .= "</th>";
		$possibleUnitValues = $this->get_possible_unit_awards();
        foreach($possibleUnitValues AS $unitValue)
        {
            $header = 'with'.strtolower($unitValue).'award';
            $retval .= "<th colspan='2'>".get_string($header, 'block_bcgt')."</th>";
        }
		$retval .= "<th>".get_string('grid', 'block_bcgt')."</th>";
		
		$retval .= "</tr>";
		if($units)
		{
			//for each unit output all of the data
			$count = 0;
			foreach($units AS $unit)
			{
				$class = 'even';
				$count++;
				if($count%2)
				{
					$class = 'odd';
				}
				$retval .=  "<tr class='$class'>";
				$retval .=  "<td colspan='2'>$unit->name</td>";
				$retval .=  "<td colspan='2'>$unit->studentsawarded / $unit->students</td>";
                foreach($possibleUnitValues AS $unitValue)
                {   
                    $field = ''.strtolower($unitValue).'count';
                    $retval .=  "<td colspan='2'>".$unit->$field."</td>";
                }
				$retval .=  "<td><a href='{$CFG->wwwroot}/blocks/bcgt/grids/unit_grid.php?qID=$this->id&uID=$unit->id&cID=$courseID'>".get_string('unitgrid', 'block_bcgt')."</a></td>";
				$retval .=  "</tr>";
			}//end loop students
			$retval .=  "</table>";
		}//end students
		else
		{
			$retval .=  "<p>".get_string('nounitsfound','block_bcgt')."</p>";
		}
		$retval .= "</form>";
        return $retval;
    }
    
    protected function get_simple_qual_report_class($userID, $groupingID = -1, $type = '')
    {
        $cID = optional_param('cID', SITEID, PARAM_INT);
        $idUse = $this->id;
        if($groupingID != -1)
        {
            $idUse = $groupingID;
        }
        global $CFG;
        $retval = '';
        $retval .= '<h3>'.get_string('qualoverview', 'block_bcgt').'</h3>';
        //grid. 
		$retval .= "<div id='classReport'><table id='classGrid_".$idUse."_".$type."' class='"; 
		$retval .= "classGrid overviewreport' align='center'>";
        $units = $this->get_overview_qual_course_report_units(-1, null, $groupingID);
        $unitKeys = array_keys($units);
        $columns = array('picture', 'username','name');
        $retval .= "<thead><tr class='overviewhead'>";
        $configColumns = get_config('bcgt','btecgridcolumns');
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
        }
        $retval .= '<th></th>'; //one for the block of colour
        if(in_array('username', $columns))
        {
            $retval .= "<th class='student'>".get_string('username', 'block_bcgt')."</th>";
        }
        if(in_array('name', $columns))
        {
            $retval .= "<th class='student'>".get_string('name','block_bcgt')."</th>";
        }
		if($units)
        {
            foreach($units AS $unit)
            {
                $retval .= '<th>'.$unit->name.'</th>';
            }
        }
        $retval .= '</tr></thead><tbody>';
        $students = $this->get_overview_qual_course_report($groupingID);
        if($students)
        {
            $rowCount = 0;
            $lastStudentID = -1;
            foreach($students AS $student)
            {
                if($lastStudentID != $student->userid)
                {
                    //then we are on a new student so end 
                    //the last row and start a new one. 
                    $unitCount = 0;
                    $rowCount++;
                    $rowClass = 'rO';
                    if($rowCount % 2)
                    {
                        $rowClass = 'rE';
                        
                    }
                    if($rowCount != 1)
                    {
                        //everything but the first time we want to end the previous row
                        $retval .= '</tr>';
                    }
                    $retval .= "<tr class='$rowClass'>";
                    $retval .= "<td></td>"; //one for the block of colour
                    if(in_array('username', $columns))
                    {
                        $retval .= "<td><a href='$CFG->wwwroot/blocks/bcgt/grids/student_grid.php?qID=$this->id&sID=$student->id&cID=$cID'>$student->username</a></td>";
                    }
                    if(in_array('name', $columns))
                    {
                        $retval .= "<td><a href='$CFG->wwwroot/blocks/bcgt/grids/student_grid.php?qID=$this->id&sID=$student->id&cID=$cID'>$student->firstname $student->lastname</a></td>";
                    }
                }
                $lastStudentID = $student->userid;
                //see if the unitid that we are at now matches the header
                if($unitKeys[$unitCount] != $student->unitid)
                {
                    //then we need to work out how many we are away from it. Must be forward of us, cant be behind. 
                    $startPlace = $unitCount + 1;
                    while($unitKeys[$startPlace] != $student->unitid)
                    {
                        //in other words the student isnt doing that unit
                        $retval .= '<td class="stunoton">N/A</td>';
                        $startPlace++;
                    }
                }
                //now we are at the same point as the head when it comes to our unit so output it
                $retval .= '<td class="stuaward'.$student->award.'">'.substr($student->award,0,1).'</td>';
                $unitCount++;
            }
        }
        else
        {
            $retval .= '<tr>No Students Found</tr>';
        }
        $retval .= '</tr>';
        $retval .= '</tbody></table>';
        $retval .= '</div>';
        return $retval;
    }
    
    protected function get_overview_qual_course_report($groupingID = -1)
    {
        global $DB;
        $sql = "SELECT distinct(userunit.id) as id, u.id as userid, u.username, 
            u.firstname, u.lastname, userqual.bcgtqualificationid, unit.id as unitid, 
            unit.name as unitname, typeaward.award 
            FROM {user} u
            JOIN {block_bcgt_user_unit} userunit ON userunit.userid = u.id 
            JOIN {block_bcgt_user_qual} userqual ON userqual.userid = u.id 
            AND userqual.bcgtqualificationid = userunit.bcgtqualificationid 
            JOIN {block_bcgt_unit} unit ON unit.id = userunit.bcgtunitid
            LEFT OUTER JOIN {block_bcgt_type_award} typeaward ON typeaward.id = userunit.bcgttypeawardid";
            if($groupingID != -1)
            {
                $sql .= " JOIN {groups_members} members ON members.userid = userunit.userid 
                    JOIN {groupings_groups} gg ON gg.groupid = members.groupid";
            }
            $sql .= " WHERE userqual.bcgtqualificationid = ? AND userunit.bcgtqualificationid = ?";
        $params = array();
        $params[] = $this->id;
        $params[] = $this->id;
        if($groupingID != -1)
        {
            $sql .= " AND gg.groupingid = ?";
            $params[] = $groupingID;
        }
        $sql .= " ORDER BY u.lastname ASC, u.firstname ASC, u.username ASC, unit.id ASC";
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_overview_qual_course_report_units($courseID = -1, $order = null, $groupingID = -1)
    {
        global $DB;
        $sql = "SELECT distinct(unit.id) as id, unit.name, unit.uniqueid, unit.credits 
        FROM {user} u
        JOIN {block_bcgt_user_unit} userunit ON userunit.userid = u.id 
        JOIN {block_bcgt_user_qual} userqual ON userqual.userid = u.id 
        AND userqual.bcgtqualificationid = userunit.bcgtqualificationid 
        JOIN {block_bcgt_unit} unit ON unit.id = userunit.bcgtunitid";
        if($courseID != -1)
        {
            $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = userqual.bcgtqualificationid";
        }
        if($groupingID != -1)
        {
            $sql .= " JOIN {groups_members} members ON members.userid = userunit.userid 
                JOIN {groupings_groups} gg ON gg.groupid = members.groupid";
        }
        $sql .= " LEFT OUTER JOIN {block_bcgt_type_award} typeaward ON typeaward.id = userunit.bcgttypeawardid 
        WHERE userqual.bcgtqualificationid = ?";
        $params = array();
        $params[] = $this->id;
        if($courseID != -1)
        {
            $sql .= " AND coursequal.courseid = ?";
            $params[] = $courseID;
        }
        if($groupingID != -1)
        {
            $sql .= " AND gg.groupingid = ?";
            $params[] = $groupingID;
        }
        if(!$order)
        {
            $sql .= " ORDER BY unit.id ASC";
        }
        else
        {
            $sql .= " ORDER BY ".$order;
        }
        return $DB->get_records_sql($sql, $params);
    }

    public function has_units()
    {
        return false;
    }
    
    public function delete_qualification_award($type)
    {
        global $DB;
        return $DB->execute("DELETE FROM {block_bcgt_user_award} WHERE bcgtqualificationid = ? AND userid = ? AND type = ?", array($this->id, $this->studentID, $type));

    }
    
    /**
	 * Inserts a hirstory of the qualification for the id passed in
	 * @param unknown_type $id
	 */
	protected function insert_history_record($qualID)
	{
		global $DB;
		$sql = "INSERT INTO {block_bcgt_qualification_his} 
		(bcgtqualificationid, bcgttargetqualid, name, credits, code, previousqualid, additionalname, noyears, pathwaytypeid) 
		SELECT * FROM {block_bcgt_qualification} WHERE id = ?";
		return $DB->execute($sql, array($qualID));
	}
	
	/**
	 * Inserts a history of this qualifications units
	 */
	protected function insert_qual_units_history()
	{
		global $DB;
		$sql = "INSERT INTO {block_bcgt_qual_units_his} 
		(bcgtqualificationunitid, bcgtqualificationid, bcgtunitsid, groupname) 
		SELECT id, bcgtqualificationid, bcgtunitid, groupname  FROM {block_bcgt_qual_units} WHERE bcgtqualificationid = ?";
		return $DB->execute($sql, array($this->id));
	}
	
	protected function insert_qual_criteria_history()
	{
		global $DB;
		$sql = "INSERT INTO {block_bcgt_criteria_his} 
		(bcgtcriteriaid, name, details, bcgttypeawardid, bcgtunitid, parentcriteriaid, bcgtqualificationid) 
		SELECT * FROM {block_bcgt_criteria WHERE bcgtqualificationid = ?";
		return $DB->execute($sql, array($this->id));
	}
	
	protected function insert_criteria_history($criteriaID)
	{
		global $DB;
		$sql = "INSERT INTO {block_bcgt_criteria_his} 
		(bcgtcriteriaid, name, details, bcgttypeawardid, bcgtunitid, bcgtqualificationid) 
		SELECT id, name, details, bcgttypeawardid, bcgtunitid, bcgtqualificationid 
        FROM {block_bcgt_criteria} WHERE id = ?"; 
		return $DB->execute($sql, array($criteriaID));
	}
    
    /**
	 * Adds a a unit to the qualification
	 * @param $unit
	 */
	protected function add_unit_qual($unit)
	{
		//check if it exists on the qual object 
		//(rather than in the db)
		if($this->is_unit_on_qual_object($unit) == -1)
		{
			$this->units[] = $unit;
            logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_ADDED_UNIT_TO_QUAL, null, $this->id, $unit->get_id(), null, $unit->get_id());
			return true;
		}
		return false;	
	}
	
	/**
	 * Inserts a history of the students units
	 * @param unknown_type $studentID
	 * @param unknown_type $unitID
	 */
	protected function insert_student_units_history($studentID = -1, $unitID = -1)
	{
		global $DB;
		$sql = "INSERT INTO {block_bcgt_user_unit_his} 
		(bcgtuserunitid, userid, bcgtqualificationid, bcgtunitid, bcgttypeawardid, 
        comments, dateupdated, userdefinedvalue, bcgtvalueid, setbyuserid, updatedbyuserid, dateset, studentcomments) 
		SELECT id, userid, bcgtqualificationid, bcgtunitid, 
		bcgttypeawardid, comments, dateupdated, userdefinedvalue, bcgtvalueid, setbyuserid, updatedbyuserid, dateset, studentcomments  
		FROM {block_bcgt_user_unit} WHERE bcgtqualificationid = ?";
        $params = array($this->id);
		if($studentID != -1)
		{
			$sql .= " AND userid = ?";
            $params[] = $studentID;
		}
		if($unitID != -1)
		{
			$sql .= " AND bcgtunitid = ?";
            $params[] = $unitID;
		}
		return $DB->execute($sql, $params);
	}
    
    /**
     * Insert a history record for this qual and course. 
     * @global type $DB
     * @param type $courseID
     * @return type
     */
    protected function insert_qual_course_history($courseID)
    {
        global $DB;
		$sql = "INSERT INTO {block_bcgt_course_qual_his} 
		(bcgtcoursequalid, courseid, bcgtqualificationid) 
        SELECT id, courseid, bcgtqualificationid 
		FROM {block_bcgt_course_qual} WHERE bcgtqualificationid = ? AND courseid = ?";
        $params = array($this->id, $courseID);
		return $DB->execute($sql, $params);
    }
    
    /**
	 * Inserts a history of the students units
	 * @param unknown_type $studentID
	 * @param unknown_type $unitID
	 */
	public static function insert_user_qual_history_by_id($recordID)
	{
		global $DB;
		$sql = "INSERT INTO {block_bcgt_user_qual_his} 
		(bcgtuserqualid, userid, bcgtqualificationid, roleid, comments) 
        SELECT id, userid, bcgtqualificationid, roleid, 
		comments  
		FROM {block_bcgt_user_qual} WHERE id = ?";
        $params = array($recordID);
		return $DB->execute($sql, $params);
	}
    
    /**
	 * Inserts a history of the students units
	 * @param unknown_type $studentID
	 * @param unknown_type $unitID
	 */
	protected function insert_user_qual_history($userID, $roleID)
	{
		global $DB;
		$sql = "INSERT INTO {block_bcgt_user_qual_his} 
		(bcgtuserqualid, userid, bcgtqualificationid, roleid, comments) 
        SELECT id, userid, bcgtqualificationid, roleid, 
		comments  
		FROM {block_bcgt_user_qual} WHERE bcgtqualificationid = ? AND roleid = ? AND userid = ?";
        $params = array($this->id, $roleID, $userID);
		return $DB->execute($sql, $params);
	}

    /**
     * Gets the comments that have been set for this Qual for this student
     */
    protected function get_qual_comments_db()
    {
        global $DB;
        $comments = '';
        $tracking = $DB->get_record_select("block_bcgt_user_qualification", 
                array("userid"=>$this->studentID, "bcgtqualificationid"=>$this->id));
        if(isset($tracking->id)){
            $comments = $tracking->comments;
        }
        return $comments;
    }
    
    protected function insert_user($userID, $roleID)
    {
        global $DB;
        $userQual = new stdClass();
        $userQual->bcgtqualificationid = $this->id; 
        $userQual->userid = $userID; 
        $userQual->roleid = $roleID;
        $DB->insert_record('block_bcgt_user_qual', $userQual);
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, 
                LOG_VALUE_GRADETRACKER_ADDED_USER_TO_QUAL, $userID, $this->id, null, 
                null, null, $userID, $roleID);
			
    }
    
    /**
     * Array of user objects. 
     * Inserts the users into the database onto this qual.
     * Checks if they are already on there or not. 
     * @param type $users
     * @param type $roleID
     */
    protected function insert_users($users, $roleID)
    {
        foreach($users AS $user)
        {
            $on = $this->check_user_on_qual($user->id, $roleID, $this->id);
            if(!$on)
            {
                $this->insert_user($user->id, $roleID);
            }
        }
    }
    
    protected function delete_users($users, $roleID)
    {
        foreach($users AS $user)
        {
            $on = $this->check_user_on_qual($user->id, $roleID, $this->id);
            if($on)
            {
                $this->insert_user_qual_history($user->id, $roleID);
                $this->remove_user($user->id, $roleID);
            }
        }
    }
    
    protected function remove_user($userID, $roleID)
    {
        //INSERT HISTORY!
        $this->insert_user_qual_history($userID, $roleID);
        
        global $DB;
        $conditions = array('bcgtqualificationid'=>$this->id,
            'userid'=>$userID,'roleid'=>$roleID);
        $DB->delete_records('block_bcgt_user_qual', $conditions);
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, 
                LOG_VALUE_GRADETRACKER_REMOVED_USER_FROM_QUAL, $userID, $this->id, null, 
                null, null, $userID, $roleID);	
    }
    
    protected function get_default_years()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_qual_att} 
            WHERE bcgttargetqualid = ? AND name = ?";
        $params = array($this->bcgtTargetQualID, SubType::DEFAULTNUMBEROFYEARSNAME);
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return $record->value;
        }
        return 1;
    }

    //************* The Private Functions **************//
    public static function get_plugin_class($familyID = -1)
    {
        global $DB, $CFG;
        $sql = "SELECT * FROM {block_bcgt_type_family} WHERE id = ?";
        $class = $DB->get_record_sql($sql, array($familyID));
        if($class)
        {
            $folder = $CFG->dirroot.$class->classfolderlocation;
            $className = $class->family;
            if (is_dir($folder)) {
                $file = $folder.'/'.$className.'Qualification.class.php';
                if(file_exists($file))
                {
                    require_once($file);
                    $class = $className.'Qualification';
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
            $classNameFolder = 'bcgt'.strtolower($className);
            if(is_dir($CFG->dirroot.'/blocks/bcgt/plugins/'.$classNameFolder))
            {
                $file = $CFG->dirroot.'/blocks/bcgt/plugins/'.$classNameFolder.'/lib.php';
                if(file_exists($file))
                {
                    require_once($file);
                }
            }
            if (is_dir($folder)) {
                $file = $folder.'/'.$className.'Qualification.class.php';
                if(file_exists($file))
                {
                    require_once($file);
                    $class = $className.'Qualification';
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
	 * Returns the qualification from the database based on the id that has been set
	 * for this object. 
	 * @return Found
	 */
	private static function get_qualification($qualID)
	{
		global $DB;
        
        // Since you've done this as a static function on the Qualification class, there is no way to override it
        // Bespoke check will have to go here
        $check = $DB->get_record("block_bcgt_bespoke_qual", array("bcgtqualid" => $qualID));
        if ($check)
        {
            
            $record = $DB->get_record("block_bcgt_qualification", array("id" => $qualID), "id, name, additionalname, code");
            if ($record)
            {
                $record->typeid = -1;
                $record->type = $check->displaytype;
                $record->levelid = -1;
                $record->level = $check->level;
                $record->targetqualid = -1;
                $record->subtypeid = -1;
                $record->subtype = $check->subtype;
                return $record;
            }
            
            return false;
                        
        }
        else
        {
            
            $sql = "SELECT qual.id AS id, qual.name, qual.additionalname, qual.code, 
                        type.id AS typeid, type.type, 
                    level.trackinglevel AS level, level.id AS levelid, targetqual.id AS targetqualid,  
                    subtype.id AS subtypeid, subtype.subtype
                    FROM {block_bcgt_qualification} qual 
                    JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
                    JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
                    JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
                    JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid
                    WHERE qual.id = ?";		
                    return $DB->get_record_sql($sql, array($qualID));
            
        }
                
		
	}
    
    /**
	 * Used to return the correct qualification Class.
	 * @param unknown_type $typeID
	 * @param unknown_type $qualID
     * @param unknown_type $familyID
     * @param unknown_type $params is an object with extra parameters, such as
     * subtype and level, name etc
	 */
    //$loadParams->loadLevel = Qualification::LOADLEVELMIN
	public static function get_correct_qual_class($typeID = -1, $qualID = -1, 
            $familyID = -1, $params = null, $loadParams = null)
	{	
                                
		if($typeID != -1)
		{
            //if the typeID is known then 
            //we know exactly which class to return. 
            //what we will need to do is to go to the database
            //find which family this belongs to
            //get the class/file from our plugin table
            //load that class and call its static function of get_correct_qual_class
            $qualificationClass = Qualification::get_plugin_class_type($typeID);
            if($qualificationClass)
            {
                return $qualificationClass::get_instance($qualID, $params, $loadParams);
            }
            return false;
			
		}
		elseif($familyID != -1)
		{
            
            //then we know the family (e.g. BTEC, Alevel or C&G)
            //so lets load the family from the database
            //its class and then load its static function of get_correct_qual_class
            $qualificationClass = Qualification::get_plugin_class($familyID);
            if($qualificationClass)
            {
                return $qualificationClass::get_pluggin_qual_class($typeID, 
                        $qualID, $familyID, $params, $loadParams);
            }
            return false;

        }	
		return false;
	}
    
    /**
     * 
     * @global type $DB
     * Gets any additional units that this student is doing
     * that are not normally on the qualificaion. 
     */
    protected function add_students_other_units()
	{
		global $DB;
		$sql = "SELECT DISTINCT unit.* FROM {block_bcgt_unit} AS unit
		JOIN {block_bcgt_user_unit} AS userunit ON userunit.bcgtunitid = unit.id 
		WHERE userunit.userid = ? AND userunit.bcgtqualificationid = ? 
		AND unit.id NOT IN (SELECT bcgtunitid FROM {block_bcgt_qual_units} 
        WHERE bcgtqualificationid = ?)";
		$records = $DB->get_records_sql($sql, array($this->studentID, $this->id, $this->id));
		if($records)
		{
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELALL;
			$units = $this->units;
			foreach($records AS $record)
			{
				$unit = Unit::get_unit_class_id($record->id, $loadParams);
				$unit->load_student_information($this->studentID, $this->id);	
				$units[$record->id] = $unit;
			}
			$this->units = $units;
		} 
	}
    
     /**
	 * Used to see if a unit is already assigned to the qualification in the database
	 * @param unknown_type $unitID
	 */
	private function is_unit_on_qual_db($unitID)
	{
		global $DB;
		$sql = "SELECT id FROM {block_bcgt_qual_units} 
		WHERE bcgtqualificationid = ? AND bcgtunitid = ?";
		$record = $DB->get_record_sql($sql, array($this->id, $unitID));
		if($record)
		{
			return $record->id;
		}
		return -1;	
	}
    
    /**
     * Gets the unit ids that are on this qual straight from the database
     * @global type $DB
     * @return type
     */
    protected function retrieve_units_qual()
	{
		global $DB;
		$sql = "SELECT bcgtunitid AS id FROM {block_bcgt_qual_units} 
		WHERE bcgtqualificationid = ?";
		return $DB->get_records_sql($sql, array($this->id));
	}
	
    /**
     * Gets the criteria database object that is directly on this qual. 
     * @global type $DB
     * @return type
     */
	protected function retrieve_criteria_qual()
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_criteria} 
		WHERE bcgtqualificationid = ?";
		return $DB->get_records_sql($sql, array($this->id));
	}
	
    /**
     * Gets the database criteria objects that are on this qual
     * and that are on the units that are on this qual. 
     * @global type $DB
     * @return type
     */
	protected function retrieve_criteria_qual_and_units()
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_criteria} WHERE bcgtqualificationid = ? 
		UNION SELECT crit.* FROM {block_bcgt_criteria} crit 
		JOIN {block_bcgt_unit} units ON units.id = crit.bcgtunitid 
		JOIN {block_bcgt_qual_units} qualUnits ON qualUnits.bcgtunitid = units.id 
		WHERE qualUnits.bcgtqualificationid = ?";
		return $DB->get_records_sql($sql, array($this->id, $this->id));
	}  
 
    protected function retrieve_weightings($qualID = -1)
    {
       global $DB;
       if($qualID == -1)
       {
           $qualID = $this->id;
       }
       $sql = "SELECT * FROM {block_bcgt_qual_weighting} WHERE bcgtqualificationid = ? ORDER BY number ASC";
       return $DB->get_records_sql($sql, array($qualID));
    }
    
    public function update_weightings()
    {
        //it needs to see if we already have any? if not and we have them now, then insert
        if($this->weightings)
        {
            $dbWeightings = $this->retrieve_weightings();
			if(!$dbWeightings)
			{
				//then there are non in the database so
				//then we need to insert them all!!
				$this->insert_weightings();
				return true;
			}
            
            //ok so we know we need to do some updating
			//are there any new ones to add?
			foreach($this->weightings AS $weighting)
			{
				//does the one on the current object exist in the database?
				if(array_key_exists($weighting->id, $dbWeightings))
				{
					//then we know that it existed in the database and it needs saving
					$this->update_weighting($weighting);
				}
				else
				{
                    $weighting->bcgtqualificationid = $this->id;
					$this->insert_weighting($weighting);
				}
			}
			
			//now search for ones that need deleting
			//by searhing through the database and seeing if it is NOT in the ones on
			//the object
			foreach($dbWeightings AS $weighting)
			{
				if(!array_key_exists($weighting->id, $this->weightings))
				{
					$this->delete_weighting($weighting->id);
				}
			}
        }
        else {
            $this->delete_all_weightings();
        }
        //it needs to update all we have and add any new ones
        
        //it needs to delete all old ones
    }
    
    protected function update_weighting($weighting)
    {
        global $DB;
        $DB->update_record('block_bcgt_qual_weighting', $weighting);
    }
    
    protected function insert_weighting($weighting)
    {
        global $DB;
        $weighting->bcgtqualificationid = $this->id;
        $DB->insert_record('block_bcgt_qual_weighting', $weighting);
    }
    
    protected function delete_weighting($weightingID)
    {
        global $DB;
        $DB->delete_records('block_bcgt_qual_weighting', array('id'=>$weightingID));
    }
    
    protected function delete_all_weightings()
    {
        global $DB;
        $DB->delete_records('block_bcgt_qual_weighting', 
                array('bcgtqualificationid'=>$this->id));
    }
   
    protected function load_possible_assessment_values($typeID = -1, $targetQualID = -1)
    {
        $possibleValues = Qualification::get_possible_assessment_valued($typeID,$targetQualID);
        $this->possibleValues = $possibleValues;
    }
    
    public static function get_possible_assessment_valued($typeID = -1, $targetQualID = -1, $checkForEnabled = true)
    {
        global $DB;
		$sql = "SELECT * FROM {block_bcgt_value} ";
        $sql .= "WHERE context = ? AND ((bcgttypeid = ? AND bcgttargetqualid = ?) OR (bcgttypeid = ? AND bcgttargetqualid IS NULL))";
        $params = array('assessment', -1, $targetQualID, $typeID);
        if($checkForEnabled)
        {
            $sql .= ' AND enabled = ?';
            $params[] = 1;
        }
        $sql .= " ORDER BY ranking DESC, id ASC";
        
		$possibleValues = $DB->get_records_sql($sql, $params);
        return $possibleValues;
    }
    
    protected function load_possible_values($typeID = -1, array $query = null)
    {
        global $DB;
		$sql = "SELECT * FROM {block_bcgt_value} 
		WHERE bcgttypeid = ? ";
        $params = array();
        if($typeID != -1)
        {
            $params[] = $typeID;
        }
        else
        {
            $params[] = $this->get_class_ID();
        }
        if($query)
        {
            $count = 0;
            $total = count($query);
            $sql .= ' AND ';
            foreach($query AS $param=>$value)
            {
                $count++;
                $sql .= $param.' = ?';
                if($count != $total)
                {
                    $sql .= ' AND ';
                }
                $params[] = $value;
            }
        }
        $sql .= " ORDER BY ranking DESC, id ASC";
        
		$possibleValues = $DB->get_records_sql($sql, $params);
        $this->possibleValues = $possibleValues;
    }
    
    protected function load_target_grades($orderBY = '')
    {
        global $DB;
		$sql = "SELECT * FROM {block_bcgt_target_grades} 
		WHERE bcgttargetqualid = ? ORDER BY ";
        if($orderBY == '')
        {
            $sql .= 'ranking ASC';
        }
        else
        {
            $sql .= $orderBY;
        }
        $params = array($this->bcgtTargetQualID);
		$targetGrades = $DB->get_records_sql($sql, $params);
        $this->targetGrades = $targetGrades;
    }
    
    function insert_weightings()
    {
        global $DB;
        $newArray = array();
        foreach($this->weightings AS $weighting)
        {
            $weighting->bcgtqualificationid = $this->id;
            $id = $DB->insert_record('block_bcgt_qual_weighting', $weighting);
            $weighting->id = $id;
            $newArray[$id] = $weighting;
        }
        $this->weightings = $newArray;
    }
    
    
    
    
    //************* THE ABSTRACT FUNCTIONS *************//
    //******* STATIC ABSTRACT *************//
    //The following methdods must be implemented in a STATIC form on the sub classes!!
    //          used to determine what qualification class (if family has multiple) to load
    //public static function get_pluggin_qual_class($typeID = -1, $qualID = -1, $familyID = -1, $params = null);
    //
    //          Returns a new instance of the qualification e.g. return new XYQualification();
    //public static function get_instance($qualID, $params);
    //
    //          returns the form fields used in edit_qual.pho to help decide what qual class to load
    //public static function get_edit_form_menu($disabled, $qualID, $typeID);
    //***** END STATIC ABSTRACT *********//
    
    /**
	 * Returns the id of the type not the qual
	 */
	abstract function get_family_ID();
    
    /**
     * Returns the family name
     */
    abstract function get_family();
    
    /**
	 * Returns the human type name
	 */
	abstract function get_type();
	
	/**
	 * Returns the id of the type not the qual
	 */
	abstract function get_class_ID();
    
    /**
	 * Gets the form fields that will go on edit_qualification_form.php
	 * They are different for each qual type
	 * e.g for BTEC its an <input> for credits
	 */
	abstract function get_edit_form_fields();
    
    public function process_create_update_qual_form(){
        return true;
    }
    
    public function get_processed_errors(){
        return (isset($this->processed_errors)) ? $this->processed_errors : false;
    }
    
    /**
	 * Used in edit qual
	 * Gets the submitted data from the edit form fields
	 * edit_qualification_form.php
	 * E.g. for BTEC its getting the POST of the credits input.
	 */
	abstract function get_submitted_edit_form_data();
    
    /**
	 * using the object insert into the database
	 * Dont forget to set the ID up for the object once inserted
	 */
	abstract function insert_qualification();
	
	/***
	 * Deletes the qual
	 * For each type there maybe specific things we need to do
	 */
	abstract function delete_qualification();
	
	/**
	 * Updates the qual
	 * For each type there maybe specific things we need to do
	 */
	abstract function update_qualification();
    
    /**
	 * Used to get the type specific title values and labels.
	 * E.g. for BTEC its 'Credits Required. '
	 */
	abstract function get_type_qual_title();
    
    /**
	 * So when adding or removing units from a qual.
	 * returns a string with fields for edit_qualification_units 
	 * for example, total credits for BTECs
	 * This is used when the form comes up so that a user can 
	 * view things that are specific to the qual when adding units to quals. 
	 */
	abstract function get_unit_list_type_fields();
    
    /**
	 * Adds a unit to the qualification
	 * @param Unit $unit
	 */
	abstract function add_unit(Unit $unit);
    
    /**
	 * Removes a unit from the qualification. 
	 * @param Unit $unit
	 */
	abstract function remove_unit(Unit $unit);
        
    /**
     * Multiple denotes if this will appear multiple times on a page. 
     * Gets the page and grid that is used in the edit students unit
     * page. 
     */
    abstract function get_edit_students_units_page($courseID = -1, $multiple = false, 
            $count = 1, $action = 'q');
    
    /**
     * gets the javascript initialisation call
     */
    abstract function get_edit_student_page_init_call();
    
    /**
	 * Does the qual have a final grade?
	 * E.g. Alevels or BTECS or are they just pass/fail
	 */
	abstract function has_final_grade();	
    
    /**
	 * What is the final grade if it has been set
	 */
	abstract function retrieve_student_award();
    
    /**
	 * What is the final grade
	 */
	abstract function calculate_final_grade();
    
    /**
	 * Calculate the predicted grade
	 */
	abstract function calculate_predicted_grade();
    
    //some quals have criteria just on the qual like alevels. 
	//each qual migt store this differently.
	abstract function load_qual_criteria_student_info($studentID, $qualID);
    
    /**
     * process the edit students units page. 
     */
    abstract function process_edit_students_units_page($courseID = -1);
    
    /**
     * Gets a single row of abiliy to select a students units 
     * on the qualification.
     */
    abstract function get_edit_single_student_units($currentCount);
    
    /**
     * Gets the initialisation call of each functtion for dealing 
     * with the edit students units when we are looking at one single student
     * per qualification. 
     * In the previous screen the user may have added the student(s) to qualifications
     * of a different type so will need different behaviour. 
     */
    abstract function get_edit_single_student_units_init_call();
    
    /**
     * Gets the page where the settings for the qualification can be set.
     */
    abstract function get_qual_settings_page();
    
    /**
     * Displays the Grid
     */
    public abstract function display_student_grid($fullGridView = true, $studentView = true);
    
    public function display_activity_grid($activities)
    {
        return "";
    }
    
    public abstract function qual_specific_student_load_information($studentID, $qualID);
    
    public abstract function display_subject_grid();
    
    public function get_extra_data_for_copy($oldQualification){
        ;
    }
    
    public function display_gradebook_check_grid($courseID = -1, $groupingID = -1)
    {
        return ""; 
    }
    
    public static function get_user_course($qualID, $userID, $returnMultiple = false)
    {
        global $DB;
        $sql = "SELECT distinct(coursequal.id), coursequal.courseid, coursequal.bcgtqualificationid FROM {block_bcgt_course_qual} coursequal 
            JOIN {course} course ON course.id = coursequal.courseid 
            JOIN {context} context ON context.instanceid = course.id 
            JOIN {role_assignments} roleass ON roleass.contextid = context.id 
            JOIN {role} role ON role.id = roleass.roleid 
            JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = coursequal.bcgtqualificationid 
            AND userqual.userid = roleass.userid 
            WHERE coursequal.bcgtqualificationid = ? AND roleass.userid = ? 
            AND context.contextlevel = ? AND role.shortname = ?";
        $records = $DB->get_records_sql($sql, array($qualID, $userID, 50, 'student'));
        if($records)
        {
            if($returnMultiple)
            {
                return $records;
            }
            $record = end($records);
            return $record;
        }
        return false;
    }
    
    /**
     * Create a copy of a qualification
     * @param type $qualID
     */
    public static function copy_qual($qualID){
        
        global $DB;
        
        $qualification = null;
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        if (!$qualification) return false;
        
        // Firstly let's create the blank copy of the qualification, changing the name
        $newQualification = Qualification::get_qualification_class($qualification->get_class_ID(), -1, 
            $qualification->get_family_ID(), null, $loadParams);
                
        
        $newQualification->set_name($qualification->get_name() . " (Copy)");
        $newQualification->set_additional_name($qualification->get_additional_name());
        $newQualification->set_code($qualification->get_code());
        $newQualification->set_level( $qualification->get_level() );
        $newQualification->set_subType( $qualification->get_subType() );
        $newQualification->set_no_years( $qualification->get_no_years() );
        $newQualification->set_units( $qualification->get_units() );
        if (method_exists($newQualification, 'set_credits') && method_exists($qualification, 'get_credits')){        
            $newQualification->set_credits( $qualification->get_credits() );
        }
        
        $newQualification->get_extra_data_for_copy($qualification);
        
        $newQualification->insert_qualification();
        
        if ($newQualification->get_id() > 0){
        
            // Now the units - We don't need to create these again, we simply link the new qual to the existing ones
            $newQualification->save(true, false);
            
            
        }
                
        return true;
        
    }
    
    public function print_grid(){
        echo "This default message has not been overwritten with a print_grid() method for this qual type";
    }
    
    public function print_class_grid(){
        echo "This default message has not been overwritten with a print_class_grid() method for this qual type. Feature to come.";
    }
    
    public function get_attribute($name, $userID=null)
    {
        global $DB;
        $check = $DB->get_record("block_bcgt_qual_attributes", array("bcgtqualificationid" => $this->id, "attribute" => $name, "userid" => $userID));
        return ($check) ? htmlspecialchars($check->value, ENT_QUOTES) : false;
    }

    public function set_attribute($name, $value, $userID=null)
    {
        global $DB;
        $check = $DB->get_record("block_bcgt_qual_attributes", array("bcgtqualificationid" => $this->id, "attribute" => $name, "userid" => $userID));
        if($check)
        {
            $check->value = $value;
            $try = $DB->update_record("block_bcgt_qual_attributes", $check);
            if($try) logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_UPDATED_QUAL_ATTRIBUTE, $userID, $this->id, null, null, $check->id, $value);
            return $try;
        }
        else
        {
            $obj = new stdClass();
            $obj->bcgtqualificationid = $this->id;
            $obj->attribute = $name;
            $obj->value = $value;
            $obj->userid = $userID;
            $try = $DB->insert_record("block_bcgt_qual_attributes", $obj);
            if($try) logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_UPDATED_QUAL_ATTRIBUTE, $userID, $this->id, null, null, $try, $value);
            return $try;
        }
    }
        
    /**
     * Given the name of a parent criteria, get the maximum number of sub criteria which might fall under it on this
     * qualification, so we can set the colspan of the <th> element correctly
     * @param type $name 
     */
    public function get_max_sub_criteria_of_criteria($name)
    {
        if(!$this->units) return 1;

        $array = array();

         // Loop through units
        foreach($this->units as $unit)
        {

            // Find criteria on this unit with name
            $criteria = $unit->find_criteria_by_name($name);
            if(!$criteria) continue;

            // Get the sub criteria on this criteria
            $sub = $criteria->get_sub_criteria();
            if(!$sub) continue;

            foreach($sub as $subCriteria)
            {
                if(!in_array($subCriteria->get_name(), $array)) $array[] = $subCriteria->get_name();
            }             

        }

        return ($array) ? count($array) : 1;

    }
    
    public function has_printable_report(){
        return false;
    }
    
    public function has_auto_target_grade_calculation(){
        return false;
    }
    
    public function can_have_target_grade(){
        return true;
    }
    
    public function has_logs()
    {
        return false;
    }
    
    public function show_logs()
    {
        
        // Grid logs
        $params = array();
        $params['qualID'] = $this->id;
        $params['student'] = $this->student->username;

        $xml = Log::get_grid_xml($params);
        $retval = "";        
        $retval .= "<div id='gridLogs' style='display:none;'>";
        $retval .= Log::parse_logs_xml($xml);
        $retval .= "</div><br>";

        return $retval;

    }
    
    
     public function sort_criteria($criteriaNames = null, $criteria = null){
        
        $sorter = new CriteriaSorter();
        if (!is_null($criteriaNames)){
            usort($criteriaNames, array($sorter, "ComparisonSimple"));
            return $criteriaNames;
        }
        
        if (!is_null($criteria)){
            usort($criteria, array($sorter, "ComparisonSimpleObject"));
            return $criteria;
        }
        
        return false;
        
    }
    
    /**
     * This is the detault which will be overridden by qual families which support it
     * @param type $criteria
     * @param type $scale
     * @param type $grade
     */
    public function update_student_criteria_from_mod_grading($criteria, $scale, $grade){
        mtrace("This qualification type does not support this");
    }
    
    
    /**
     * Export the spec of the qualification - units, criteria, etc... 
     * No user data
     * @return boolean
     */
    public function export_specification(){
        
        global $CFG, $USER;
        
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
                     ->setCreator(fullname($USER))
                     ->setLastModifiedBy(fullname($USER))
                     ->setTitle($this->get_display_name())
                     ->setSubject($this->get_display_name())
                     ->setDescription($this->get_display_name() . " - generated by Moodle Grade Tracker");

        // Remove default sheet
        $objPHPExcel->removeSheetByIndex(0);
        
        $sheetIndex = 0;
        
        // Have a worksheet for each unit
        $units = $this->get_units();
        
        $unitSorter = new UnitSorter();
        usort($units, array($unitSorter, "ComparisonDelegateByType"));
        
        if ($units)
        {
            
            foreach($units as $unit)
            {
                
                // Set current sheet
                $unitName = substr($unit->get_name(), 0, 30);
                $unitName = preg_replace("/[^a-z 0-9]/i", "", $unitName);
                
                $objPHPExcel->createSheet($sheetIndex);
                $objPHPExcel->setActiveSheetIndex($sheetIndex);
                $objPHPExcel->getActiveSheet()->setTitle($unitName);
                
                // Unit name
                $objPHPExcel->getActiveSheet()->setCellValue("A1", "Unit Name");
                $objPHPExcel->getActiveSheet()->setCellValue("B1", $unit->get_name());
                
                // Unit code
                $objPHPExcel->getActiveSheet()->setCellValue("A2", "Unit Code");
                $objPHPExcel->getActiveSheet()->setCellValue("B2", $unit->get_uniqueID());
                
                // Unit details
                $objPHPExcel->getActiveSheet()->setCellValue("A3", "Unit Details");
                $objPHPExcel->getActiveSheet()->setCellValue("B3", $unit->get_details());
                
                // Unit level
                $objPHPExcel->getActiveSheet()->setCellValue("A4", "Unit Level");
                $objPHPExcel->getActiveSheet()->setCellValue("B4", $unit->get_level()->get_level());
                
                // Unit credits
                $objPHPExcel->getActiveSheet()->setCellValue("A5", "Unit Credits");
                $objPHPExcel->getActiveSheet()->setCellValue("B5", $unit->get_credits());
                
                $rowNum = $this->get_extra_rows_for_export_spec($objPHPExcel, $unit, 5);
                
                // Criteria headers
                $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", "Criteria Names");
                $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", "Criteria Details");
                
                
                $criteria = $this->sort_criteria(null, $unit->get_criteria());
                
                $rowNum++;
                
                if ($criteria)
                {
                    foreach($criteria as $criterion)
                    {

                        $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", $criterion->get_name());
                        $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", $criterion->get_details());

                        $rowNum++;

                    }
                }
               
                                                
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                
                $sheetIndex++;
                
            }
            
        }
        
        
        // Alignment
        $objPHPExcel->getDefaultStyle()
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        
        
        // End
        if ($units){
            $objPHPExcel->setActiveSheetIndex(0);
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        ob_clean();
        $objWriter->save('php://output');
        
        return true;
        
    }
    
    
    public function get_extra_rows_for_export_spec(&$obj, $unit, $rowNum){
        return $rowNum;
    }
    
    public function get_value_id($shortValue, $typeID)
    {
        
        global $DB;
        
        $record = $DB->get_record("block_bcgt_value", array("bcgttypeid" => $typeID, "shortvalue" => $shortValue));
        
        return ($record) ? $record->id : false;
        
    }
    
}
