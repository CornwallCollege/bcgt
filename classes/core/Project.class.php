<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Project
 *
 * @author mchaney
 */
class Project {
    //put your code here
    
    //mapped to lang strings (or at least will be)
    
    const BEHIND = "behind";
    const AHEAD = "ahead";
    const ON = "on";
    
    private $id;
	private $name;
	private $details;
    private $date;
    private $qualIDs;
    private $centrallyManaged;
    private $awarded;
    //db array of quals
    private $quals;
    
    private $studentID;
    private $studentQualID;
    private $userValue;
    private $userTargetGrade;
    private $userTargetGradeID;
    private $userComments;
    
    private $currentProject;
    protected $studentProgress;
    
    const CURRENT_PROJECT = 'CURRENT';
    
    protected $importcsvqual;
    protected $success;
    protected $importcsvassessment;
	
	function Project($id = -1, $params = null)
	{
		$this->id = $id;
        if($id != -1 && !$params)
        {
            $project = $this->get_project();
            if($project)
            {
                $this->name = $project->name;
                $this->details = $project->details;
                $this->date = $project->targetdate;
                $this->centrallyManaged = $project->centrallymanaged;
                $this->awarded = $project->awarded;
            }
        }
        elseif($params)
        {
            if(isset($params->name))
            {
                $this->name = $params->name;
            }
            if(isset($params->details))
            {
                $this->details = $params->details;
            }
            if(isset($params->date))
            {
                if(is_string($params->date))
                {
                    $this->date = strtotime($params->date);
                }
                else 
                {
                    $this->date = $params->date;
                }
            }
            elseif(isset($params->targetdate))
            {
                $this->date = $params->targetdate;
            }
            if(isset($params->qualIDs))
            {
                $this->qualIDs = $params->qualIDs;
            }
            if(isset($params->centrallyManaged))
            {
                $this->centrallyManaged = $params->centrallyManaged;
            }
            elseif(isset($params->centrallymanaged))
            {
                $this->centrallyManaged = $params->centrallymanaged;
            }
            if(isset($params->awarded))
            {
                $this->awarded = $params->awarded;
            }
        }
        
        if($id != -1)
        {
            $quals = $this->get_project_quals();
            if($quals)
            {
                $this->quals = $quals;
                $this->qualIDs = array_keys($quals);
            }
        }
        
        
        $this->get_is_project_current();
//        $this->get_is_project_current();
	}
    
    function is_project_current()
    {
        return $this->currentProject;
    }
    
    public function display_import_options()
    {
        if(isset($_POST['option1']))
        {
            $this->importcsvqual = $_POST['option1'];
        }
        
        $retval = '<table>';
        $retval .= '<tr><td><label for="option1">'.get_string('famqual', 'block_bcgt').' : </label></td>';
        $retval .= '<td><select id="famquals" name="option1"><option id="-1"></option>';
        $quals = $this->get_quals_with_assessments();
        if($quals)
        {
            foreach($quals AS $qual)
            {
                $selected = '';
                if($this->importcsvqual && $qual->id == $this->importcsvqual)
                {
                    $selected = 'selected';
                }
                $retval .= '<option '.$selected.' value="'.$qual->id.'">'.
                        bcgt_get_qualification_display_name($qual, false).'</option>';
            }
        }
        $retval .= '</select></td>';
        $retval .= '<td></td></tr>';
        $retval .= '<tr><td><label for="option2">'.get_string('famassessment', 'block_bcgt').' : </label></td>';
        $retval .= '<td><select name="option2"><option id="-1"></option>';
        if($this->importcsvqual)
        {
            $projects = $this->get_qual_assessments($this->importcsvqual);
            if($projects)       
            {
               foreach($projects AS $project)
               {
                   $retval .= '<option value="'.$project->get_id().'">'.$project->get_name().
                           ' - '.$project->get_date().'</option>';
               }
            }
        }
        $retval .= '</select></td>';
        $retval .= '<td></td></tr>';
        $retval .= '</table>';
        return $retval;
    }
    
    public function get_quals_with_assessments()
    {
        global $DB;
        $sql = "SELECT distinct(qual.id) as id, type.id as typeid, type.type as type, 
            family.id as familyid, family.family as family, subtype.id as subtypeid, 
            subtype.subtype, subtype.subtypeshort, level.id as levelid, level.trackinglevel, 
            qual.name as name, qual.additionalname FROM {block_bcgt_activity_refs} aref 
            JOIN {block_bcgt_qualification} qual ON qual.id = aref.bcgtqualificationid 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
            JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid ORDER BY family ASC, levelid ASC, subtype ASC";
        return $DB->get_records_sql($sql, array());
    }
    
    public function get_editable_quals($userID = -1)
    {
        global $DB;
        $teacherRole = $DB->get_record_sql("SELECT * FROM {role} WHERE shortname = ?", array('teacher'));
        $sql = "SELECT distinct(qual.id) as id, type.id as typeid, type.type as type, 
            family.id as familyid, family.family as family, subtype.id as subtypeid, 
            subtype.subtype, subtype.subtypeshort, level.id as levelid, level.trackinglevel, 
            qual.name as name, qual.additionalname FROM {block_bcgt_activity_refs} aref 
            JOIN {block_bcgt_qualification} qual ON qual.id = aref.bcgtqualificationid 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
            JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
            JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id";
        $params = array();
        if($userID != -1)
        {
            $sql .= " JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qual.id 
                WHERE userqual.userid = ? AND userqual.roleid = ?";
            $params[] = $userID;
            $params[] = $teacherRole->id;
        }
        $sql .= " ORDER BY family ASC, levelid ASC, subtype ASC";
        return $DB->get_records_sql($sql, $params);
    }
    
    public function get_submitted_import_options()
    {
        if(isset($_POST['option1']))
        {
            $this->importcsvqual = $_POST['option1'];
        }
        if(isset($_POST['option2']))
        {
            $this->importcsvassessment = $_POST['option2'];
        }
    }
    
    public function was_success()
    {
        return $this->success;
    }
    
    public function display_summary()
    {
        $retval = '<p><ul>';
        $retval .= '<li>'.get_string('famimportsum4','block_bcgt').' : '.$this->summary->successCount.'</li>';
        if(!$this->success)
        {
            $retval .= '<li>'.get_string('famimportsum1','block_bcgt').' : '.count($this->summary->usersNotFound).'</li>'; 
            $retval .= '<li>'.get_string('famimportsum2','block_bcgt').' : '.count($this->summary->gradesNotFound).'</li>'; 
            $retval .= '<li>'.get_string('famimportsum3','block_bcgt').' : '.count($this->summary->cetaNotFound).'</li>';  
        }
        $retval .= '</ul></p>';
        return $retval;
    }
    
    public function get_headers()
    {
        return array("Username", "CurrentGrade", "CETAGrade", "Comments");
    }
    
    public function get_examples()
    {
        return "2132313,A,B,This is a comment of course<br />".
                "jbob,C/D,D/C,(Comments are optional)<br />".
                "jbob2213,A*,B/C,<br />";
    }
    
    public function get_description()
    {
        return get_string('famdesc', 'block_bcgt');
    }
    
    public function get_file_names()
    {
        return 'userassessments.csv';
    }
    
    public function has_multiple()
    {
        return false;
    }
    
    /**
     * username|currentgrade|cetagrade|comments
     * @param type $csvFile
     */
    public function process_import_csv($csvFile, $process = false)
    {
        $usersNotFound = array();
        $successCount = 0;
        $count = 1;
        $gradesNotFound = array();
        $cetaNotFound = array();
        $CSV = fopen($csvFile, 'r');
        while(($assessmentMark = fgetcsv($CSV)) !== false) {
            if($count != 1)
            {
                //need to find the user
                //needs to find the grade
                //needs to find the ceta
                //add them all in for the project and qual set  
                global $DB;
                $user = $DB->get_record_sql('SELECT * FROM {user} WHERE username = ?', array($assessmentMark[0]));
                if(!$user)
                {
                    $usersNotFound[$assessmentMark[0]] = $assessmentMark[0];
                    continue;
                }
                else
                {
                    $userID = $user->id;
                }
                $familyID = -1;
                $targetQualID = Qualification::get_target_qual_by_qualID($this->importcsvqual);
                $qualification = Qualification::get_qualification_class_id($this->importcsvqual);
                if($qualification)
                {
                    $familyID = $qualification->get_family_instance_id();
                }
                
                //get the value
                $value = Value::retrieve_assessment_value(-1, $familyID, 
                        $targetQualID, $assessmentMark[1]);
                if(!$value)
                {   
                    $gradesNotFound[$assessmentMark[1]] = $assessmentMark[1];
                    continue;
                }
                else
                {
                    $valueID = $value->get_id();
                }
                //get the target grade
                $grade = TargetGrade::retrieve_target_grade(-1, $targetQualID, $assessmentMark[2]);
                if(!$grade)
                {
                    $cetaNotFound[$assessmentMark[2]] = $assessmentMark[2];
                    continue;
                }
                else
                {
                    $gradeID = $grade->get_id();
                }
                $this->id = $this->importcsvassessment;
                $this->set_student($userID);
                $this->set_user_values($valueID, $gradeID, $assessmentMark[3]);
                $this->save_user_values($this->importcsvqual); 
                $successCount++;
            }
            $count++;
            
        }  
        fclose($CSV);
        
        $success = true;
        if(count($usersNotFound) > 0 || count($gradesNotFound) > 0 || count($cetaNotFound) > 0)
        {
            $success = false;
        }
        $summary = new stdClass();
        $summary->successCount = $successCount;
        $summary->usersNotFound = $usersNotFound;
        $summary->gradesNotFound = $gradesNotFound;
        $summary->cetaNotFound = $cetaNotFound;
        $this->summary = $summary;
        $this->success = $success;
    }
    
    function get_user_comments()
    {
        return $this->userComments;
    }
    
    function set_qual_ids(array $qualIDs)
    {
        $this->qualIDs = $qualIDs;
    }
    
    function get_qual_ids()
    {
        return $this->qualIDs;
    }
    
    function get_name()
    {
        return $this->name;
    }
    
    function get_details()
    {
        return $this->details;
    }
    
    function get_date()
    {
        return ($this->date ? date('d-m-Y', $this->date):'');
    }
    
    function get_Due_Date_TimeStamp()
    {
        return (isset($this->date) ? $this->date : '');
    }
    
    function get_id()
    {
        return $this->id;
    }
    
    function set_student($studentID)
    {
        $this->studentID = $studentID;
    }
    
    function set_user_value($valueID)
    {
        $this->userValue = new Value($valueID);
    }
    
    function set_user_target_grade($targetGradeID)
    {
        $this->userTargetGrade = new TargetGrade($targetGradeID);
    }
    
    function set_user_comments($comments)
    {
        $this->userComments = $comments;
    }
    
    function set_user_values($valueID = -1, $targetGradeID = -1, $comments = '')
    {
        $this->userValue = new Value($valueID);
        $this->userTargetGrade = new TargetGrade($targetGradeID);
        $this->userComments = $comments;
    }
    
    public function user_predicted_behind_ahead($qualID = -1, $userTargetGrades = null, $weighted = null)
    {
        $studentProgress = $this->get_student_progress($qualID, $userTargetGrades);
        if($weighted)
        {
            //then we are comparing against weighting
            return $studentProgress->weightedGradeTarget->target;
        }
        else
        {
            //we are comparing against normal
            return $studentProgress->targetGradeTarget->target;
        }
    }
    
    public function user_current_behind_ahead($qualID = -1, $userTargetGrades = null, $weighted = null)
    {
        $studentProgress = $this->get_student_progress($qualID, $userTargetGrades);
        if($weighted)
        {
            //then we are comparing against weighting
            return $studentProgress->weightedGradeValue->target;
        }
        else
        {
            //we are comparing against normal
            return $studentProgress->targetGradeValue->target;
        }
    }
    
    public function get_student_progress($qualID = -1, $userTargetGrades = null)
    {
        if(isset($this->studentProgress) && $this->studentProgress)
        {
            return $this->studentProgress;
        }
        
        $targetQualID = Qualification::get_target_qual_by_qualID($qualID);
        if($qualID == -1)
        {
            //then we are going to do it for all quals
        }
        if(!$userTargetGrades)
        {
            //then go and get them. 
        }
        $targetGradeObj = null;
        $weightedTargetGradeObj = null;
        if(isset($userTargetGrades->targetgrade))
        {
            $targetGradeObj = $userTargetGrades->targetgrade;
        }
        elseif(isset($userTargetGrades->breakdown))
        {
            $targetGradeObj = $userTargetGrades->breakdown;
        }
        if(isset($userTargetGrades->weightedtargetgrade))
        {
            $weightedTargetGradeObj = $userTargetGrades->weightedtargetgrade;
        }
        elseif(isset($userTargetGrades->weightedbreakdown))
        {
            $weightedTargetGradeObj = $userTargetGrades->weightedbreakdown;
        }
        //get the students target grade for this qualID
        //get their current value
        //get their current ceta 
        $projectTargetGrade = $this->userTargetGrade;
        $projectValue = $this->userValue;
        $valueGrade = null;
        if($projectValue)
        {
            $valueGrade = TargetGrade::get_obj_from_grade($projectValue->get_short_value(), $projectValue->get_ranking(), $targetQualID);             
        }
        //The values set for the assessment
        if($targetGradeObj)
        {
            $targetGradeObj->type = 'TargetGrade';
        }
        if($valueGrade)
        {
            $valueGrade->type = 'Actual';
        }
        if($projectTargetGrade)
        {
            $projectTargetGrade->type = 'CETA';
        }
        if($weightedTargetGradeObj)
        {
            $weightedTargetGradeObj->type = 'Weighted';
        }
        
                
        $targetGradeValue = $this->compare_grades($targetGradeObj, $valueGrade);
        $weightedGradeValue = $this->compare_grades($weightedTargetGradeObj, $valueGrade);
        
        
        //the cetas
        $targetGradeTarget = $this->compare_grades($targetGradeObj, $projectTargetGrade);
        $weightedGradeTarget = $this->compare_grades($weightedTargetGradeObj, $projectTargetGrade);
        //need to see if I can get the TargetGrade from the database based on the value selected here
        //and the ranking. 
        
        $retval = new stdClass();
        $retval->targetGradeValue = $targetGradeValue;
        $retval->weightedGradeValue = $weightedGradeValue;
        $retval->targetGradeTarget = $targetGradeTarget;
        $retval->weightedGradeTarget = $weightedGradeTarget;
                
        $this->studentProgress = $retval;
        return $retval;
    }
    
    /**
     * Grade 1 = TargetGrade
     * Grade 2 = Actual Grade
     * @param type $grade1
     * @param type $grade2
     * @return \stdClass
     */
    
    private function compare_grades($grade1, $grade2)
    {
        $retval = new stdClass();
        $progress = null;
        $gradeDiff = null;
        $ucasDiff = null;
        $target = null;
        
        $lenience = get_config('bcgt', 'alvlvalenience');
        
        
        if($grade1 && $grade2 && $grade1->get_ranking() && $grade2->get_ranking())
        {
            $ranking1 = $grade1->get_ranking();
            $ranking2 = $grade2->get_ranking();
            if($ranking1 - $ranking2 > 0)
            {
                //the higher the rank the higher the grade: SO:
                //if this is a posituve value then its something like:
                //A - B > 0
                //which means we are behind target (as its targetGrade - Actual)
                $progress = Project::BEHIND;
                $gradeDiff = $ranking1 - $ranking2;
                $ucasDiff = $grade1->get_ucas_points() - $grade2->get_ucas_points();
                $target = -1;
            }
            elseif($ranking2 - $ranking1 > 0)
            {
                //C - A < 0
                //which means we are ahead target (as its targetGrade - Actual)
                $progress = Project::AHEAD;
                $gradeDiff = $ranking2 - $ranking1;
                $ucasDiff = $grade2->get_ucas_points() - $grade1->get_ucas_points();
                $target = 1;
            }
            else
            {
                $progress = Project::ON;
                $gradeDiff = 0;
                $ucasDiff = 0;
                $target = 0;
            }
        }
        
        // We are using a lenience score
        if ($lenience > 0)
        {
            
            // The difference falls within this lenience score
            if ($gradeDiff <= $lenience)
            {
                
                $progress = Project::ON;
                $gradeDiff = 0;
                $target = 0;
                $ucasDiff = 0;
                
            }
            
        }
        
        $retval->progress = $progress;
        $retval->gradeDiff = $gradeDiff;
        $retval->ucasDiff = $ucasDiff;
        $retval->target = $target;
        return $retval;
    }
    
    function save_user_values($qualID, $updateQualAward = false)
    {
        global $USER, $DB;
        //do they already exist?
        $record = new stdClass();
        $record->userid = $this->studentID;
        $valueID = -1;
        if($this->userValue)
        {
            $valueID = $this->userValue->get_id();
        }
        $record->bcgtvalueid = $valueID;
        if ($this->userComments !== false){
            $record->comments = $this->userComments;
        }
        $record->userdefinedvalue = '';
        $targetGradeID = -1;
        if($this->userTargetGrade)
        {
            $targetGradeID = $this->userTargetGrade->get_id();
        }
        
        $record->bcgttargetgradesid = $targetGradeID;
        if($databaseRecord = $this->retrieve_user_project_values($qualID))
        {
            //then update
            $record->bcgtactivityrefid = $databaseRecord->bcgtactivityrefid;
            $record->id = $databaseRecord->id;
            $record->dateupdated = time();
            $record->updatedbyuserid = $USER->id;
            $DB->update_record('block_bcgt_user_activity_ref', $record);
        }
        else {
            //then insert
            $record->bcgtactivityrefid = $this->retrieve_activity_ref_id($qualID);
            $record->dateset = time();
            $record->setbyuserid = $USER->id;
            $DB->insert_record('block_bcgt_user_activity_ref', $record);
        }
        
        if($this->is_project_current())
        {
        //is this the current CETA?
            if($updateQualAward && !get_config('block_bcgt', 'alevelusecalcpredicted'))
            {
                $family = $DB->get_record_sql('SELECT family.* FROM {block_bcgt_type_family} family 
                    JOIN {block_bcgt_type} type ON type.bcgttypefamilyid = family.id 
                    JOIN {block_bcgt_target_qual} targetqual ON targetqual.bcgttypeid = type.id 
                    JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetqual.id 
                    WHERE qual.id = ?', array($qualID));
                if($family && $family->family == 'ALevel')
                {
                    //then lets sets the qual award to this. 
                    $this->update_qualification_award($qualID, $targetGradeID);
                }
            }
            //if this for an alevel?
            //do we have a predicted grade?
            //then set the qual awrad for the user to this.  
        }
    }
    
    /**
	 * Updates the users Qualification 
	 * award in the database with the one passed in
	 * If the user doesnt have an award before then it inserts it
	 * @param unknown_type $award
	 */
	public function update_qualification_award($qualID, $targetGradeID)
	{    
        global $DB;
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_UPDATED_QUAL_AWARD, $this->studentID, $qualID, null, null, null);

        $courseID = -1;
        $course = Qualification::get_user_course($qualID, $this->studentID);
        if($course)
        {
            $courseID = $course->courseid;
        }
                
		$obj = new stdClass();
		$obj->bcgtqualificationid = $qualID;
		$obj->userid = $this->studentID;
		//todo
        //breakdown
        $obj->bcgtbreakdownid = -1;
        $obj->bcgttargetgradesid = $targetGradeID;
        $obj->courseid = $courseID;
        $obj->warning = '';
        $obj->type = 'CETA';
		//lets find out if the user has one inserted before?
        $record = $DB->get_record_sql('SELECT * FROM {block_bcgt_user_award} WHERE userid = ? 
            AND bcgtqualificationid = ? AND courseid = ?', array($this->studentID, $qualID, $courseID));
		if($record)
		{
            $id = $record->id;
            $obj->id = $id;
            return $DB->update_record('block_bcgt_user_award', $obj);
		}
		else
		{
			return $DB->insert_record('block_bcgt_user_award', $obj);
		}
	}
    
    private function retrieve_user_project_values($qualID)
    {
        global $DB;
        $sql = "SELECT userrefs.* FROM {block_bcgt_user_activity_ref} userrefs 
            JOIN {block_bcgt_activity_refs} refs ON refs.id = userrefs.bcgtactivityrefid 
            WHERE userrefs.userid = ? AND refs.bcgtprojectid = ? AND refs.bcgtqualificationid = ?";
        return $DB->get_record_sql($sql, array($this->studentID, $this->id, $qualID));
    }
    
    private function retrieve_activity_ref_id($qualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_activity_refs} WHERE bcgtprojectid = ? AND bcgtqualificationid = ?";
        $record = $DB->get_record_sql($sql, array($this->id, $qualID));
        if($record)
        {
            return $record->id;
        }
        return -1;
    }
    
    public function save()
    {
        $stdObj = new stdClass();
        $stdObj->name = $this->name;
        $stdObj->details = $this->details;
        $stdObj->targetdate = $this->date;
        $stdObj->centrallymanaged = $this->centrallyManaged;
        $stdObj->awarded = $this->awarded;
        if($this->id != -1)
        {
            $stdObj->id = $this->id;
            $this->update_project($stdObj);
        }
        else
        {
            $this->insert_project($stdObj);
        }
    }
    
    public function delete_project()
    {
        $this->delete_quals();
        global $DB;
        $DB->delete_records('block_bcgt_project', array('id'=>$this->id));
    }
    
    public function save_quals()
    {
        //needs to loop over
        //are there some now?
            //no -> were there before? if so delete
        //-> yes
            //->were there before?
                //->no, then just insert them all. 
                //->yes, then get the ones from before. 
                    //->loop over the new ones
                        //->if not in previous, then insert.
                    //->loop over the previous
                        //if not in the new, then delete
        if(!$this->qualIDs)
        {
            $this->delete_quals();
        }
        else
        {
            if(!$this->quals || count($this->quals) == 0)
            {
                //then there were no quals before, so insert
                $this->add_all_quals();
            }
            else
            {
                //we have some before, so loop over the new ones, 
                //inserting any new
                foreach($this->qualIDs AS $qualID)
                {
                    if(!array_key_exists($qualID, $this->quals))
                    {
                        //if it doesnt exists, lets insert it
                        $this->add_qual($qualID);
                    }
                }
                //now we loop over the previous ones, if its not in the
                //new ones we delete it
                foreach($this->quals AS $qual)
                {
                    //does the
                    if(!in_array($qual->id, $this->qualIDs))
                    {
                        $this->remove_qual($qual->id);
                    }
                }
            }
        }
    }
    
    public function mark_as_current()
    {
        global $DB;
        //this needs to find if any are currently markes as current, 
        //if not then just insert
        //else change the id that is marked as current.
        if($this->get_current_project())
        {
            //then we can update
            $DB->execute("UPDATE {block_bcgt_project_att} SET bcgtprojectid = ?, value = ? WHERE name = ?", array($this->id, $this->id, Project::CURRENT_PROJECT));
        }
        else
        {
            //we can insert new
            $record = new stdClass();
            $record->bcgtprojectid = $this->id;
            $record->name = Project::CURRENT_PROJECT;
            $record->value = $this->id;
            $DB->insert_record('block_bcgt_project_att', $record);
        }
        
    }
    
    public function get_is_project_current()
    {
        $project = $this->get_current_project(false);
        if($project)
        {
            if($project->id == $this->id)
            {
                $this->currentProject = true;
                return true;
            }
            $this->currentProject = false;
            return false;
        } 
    }
    
    public static function clear_current_project()
    {
        global $DB;
        $DB->delete_records('block_bcgt_project_att', array("name"=>Project::CURRENT_PROJECT));
    }
    
    public static function get_current_project($returnProject = true)
    {
        global $DB;
        $sql = "SELECT DISTINCT proj.* 
            FROM {block_bcgt_project_att} att 
            JOIN {block_bcgt_project} proj ON proj.id = att.bcgtprojectid 
            WHERE att.name = ?";
        $record = $DB->get_record_sql($sql, array(Project::CURRENT_PROJECT));
        if($record)
        {
            if($returnProject)
            {
                return new Project($record->id, $record);
            }
            return $record;
        }
        return false;
    }
    
    function set_cenrally_managed($centrallyManaged)
    {
        $this->centrallyManaged = $centrallyManaged;
    }
    
    function set_awarded($awarded)
    {
        $this->awarded = $awarded;
    }
    
    function is_centrally_managed()
    {
        return $this->centrallyManaged;
    }
    
    function is_awarded()
    {
        return $this->awarded;
    }
    
    private function update_project($stdObj)
    {
        global $DB, $USER;
        $stdObj->updatedbyuserid = $USER->id;
        $stdObj->dateupdated = time();
        $DB->update_record('block_bcgt_project', $stdObj);
    }
    
    private function insert_project($stdObj)
    {
        global $DB, $USER;
        $stdObj->createdbyuserid = $USER->id;
        $stdObj->datecreated = time();
        $id = $DB->insert_record('block_bcgt_project', $stdObj);
        $this->id = $id;
        return $id;
    }
    
    private function get_project()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_project} WHERE id = ?";
        return $DB->get_record_sql($sql, array($this->id));
    }
    
    public function load_project()
    {
        global $DB;
        $project = $this->get_project();
        if($project)
        {
            $this->name = $project->name;
            $this->details = $project->details;
            $this->date = $project->targetdate;
        }
    }
    
    private function get_project_quals()
    {
        global $DB;
        $sql = "SELECT qual.id, qual.name, qual.additionalname, type.type, type.id AS typeid,
            family.family, family.id AS familyid, subtype.subtype, subtype.subtypeshort, 
            level.trackinglevel, refs.id as refsid
            FROM {block_bcgt_qualification} qual 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
            JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
            JOIN {block_bcgt_activity_refs} refs ON refs.bcgtqualificationid = qual.id
            WHERE refs.bcgtprojectid = ?";
        return $DB->get_records_sql($sql, array($this->id));
    }
    
    private function delete_quals()
    {
        global $DB;
        $DB->delete_records('block_bcgt_activity_refs', array('bcgtprojectid'=>$this->id));
    }
    
    private function add_all_quals()
    {
        foreach($this->qualIDs AS $qualID)
        {
            $this->add_qual($qualID);
        }
    }
    
    private function add_qual($qualID)
    {
        global $DB, $USER;
        $stdClass = new stdClass();
        $stdClass->bcgtprojectid = $this->id;
        $stdClass->bcgtqualificationid = $qualID;
        $stdClass->coursemoduleid = -1;
        $stdClass->bcgtunitid = -1;
        $stdClass->bcgtcriteriaid = -1;
        $stdClass->createdby = $USER->id;
        $stdClass->created = time();
        $stdClass->updated = time();
        $stdClass->updatedby = $USER->id;
        $DB->insert_record('block_bcgt_activity_refs', $stdClass);
    }
    
    private function remove_qual($qualID)
    {
        global $DB;
        $DB->delete_records('block_bcgt_activity_refs', 
                array('bcgtprojectid'=>$this->id,'bcgtqualificationid'=>$qualID));
    }
    
    public static function get_all_projects($centrallyManaged = null, $orderBY = NULL)
    {
        global $DB;
        $sql = 'SELECT * FROM {block_bcgt_project}';
        $params = array();
        if($centrallyManaged !== null)
        {
            $sql .= ' WHERE centrallymanaged = ?';
            if($centrallyManaged === true)
            {
                $params[] = 1;
            }
            elseif($centrallyManaged === false)
            {
                $params[] = 0;
            }
        }
        if($orderBY)
        {
            $sql .= $orderBY;
        }
        $projects = $DB->get_records_sql($sql, $params);
        $retval = array();
        foreach($projects AS $project)
        {
            $retval[$project->id] = new Project($project->id, $project);
        }
        return $retval;
    }
    
    public static function get_user_qual_grade($userID, $assessmentID, $qualID)
    {
        global $DB;
        $sql = "SELECT grade.* FROM {block_bcgt_target_grades} grade 
            JOIN {block_bcgt_user_activity_ref} urefs ON urefs.bcgttargetgradesid = grade.id
            JOIN {block_bcgt_activity_refs} refs ON refs.id = urefs.bcgtactivityrefid 
            WHERE urefs.userid = ? AND refs.bcgtprojectid = ? AND refs.coursemoduleid IS NULL 
            AND refs.bcgtqualificationid = ?";
        $record = $DB->get_record_sql($sql, array($userID, $assessmentID, $qualID));
        if($record)
        {
            $targetGrade = new TargetGrade($record->id, $record);
            return $targetGrade;
        }
        else
        {
            return false;
        }
    }
    
    public static function get_user_qual_value($userID, $assessmentID, $qualID)
    {
        global $DB;
        $sql = "SELECT value.* FROM {block_bcgt_value} value 
            JOIN {block_bcgt_user_activity_ref} urefs ON urefs.bcgtvalueid = value.id
            JOIN {block_bcgt_activity_refs} refs ON refs.id = urefs.bcgtactivityrefid 
            WHERE urefs.userid = ? AND refs.bcgtprojectid = ? AND refs.coursemoduleid IS NULL 
            AND refs.bcgtqualificationid = ?";
        $record = $DB->get_record_sql($sql, array($userID, $assessmentID, $qualID));
        if($record)
        {
            $targetGrade = new Value($record->id, $record);
            return $targetGrade;
        }
        else
        {
            return false;
        }
    }
    
    public static function project_exist_for_qual($qualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_activity_refs} 
            WHERE bcgtqualificationid = ? AND bcgtprojectid IS NOT NULL";
        return $DB->get_records_sql($sql, array($qualID));
    }
        
    public function display_student_assessments($studentID, $editing = false, 
            $save = false, $projectID = -1, $view = '', $qualID = -1)
    {
        global $COURSE, $CFG;
        $courseID = optional_param('cID', -1, PARAM_INT);
        if($courseID != -1)
        {
            $courseContext = context_course::instance($courseID);
        }
        else
        {
            $courseContext = context_course::instance($COURSE->id);
        }
        //get all of the formal assessments for this student, for all quals. 
        
        //print out the header
        //print out the body
        $retval = '';
        //get all of the formal assessments that this student could have
        //first get all of the qualifications they are on
        //then get all of the formal assessments on all of these. 
        $userProjectQuals = Project::get_user_assessments($studentID);
        if($userProjectQuals)
        {
            $userQuals = $userProjectQuals->quals;
            $projects = $userProjectQuals->projects;
            
            //sort the $projects
            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ProjectsSorter.class.php');
            $projectSorter = new ProjectsSorter();
            usort($projects, array($projectSorter, "CompareByDateCurrent"));
            $retval .= '<div class="bcgt_grid_table" id="ass_stu_grid">';
            $retval .= '<table>';
            $seeTargetGrade = false;
            $seeWeightedTargetGrade = false;
            $seeBoth = false;
            if(has_capability('block/bcgt:viewtargetgrade', $courseContext))
            {
                $seeTargetGrade = true;
            }
            if(has_capability('block/bcgt:viewweightedtargetgrade', $courseContext))
            {
                $seeWeightedTargetGrade = true;
            }
            if($seeTargetGrade && $seeWeightedTargetGrade)
            {
                $seeBoth = true;
            }
            $link = '';
            if($view == 'qg')
            {
                $link = $CFG->wwwroot.'/blocks/bcgt/grids/class_grid.php?sID=-1&qID='.$qualID.'&g=c&cID='.$courseID;
            }
            elseif($view == 'q')
            {
                $link = $CFG->wwwroot.'/blocks/bcgt/grids/ass_grid_class.php?sID=-1&qID='.$qualID.'&cID='.$courseID;
            }
            elseif($view == 'sg')
            {
                $link = $CFG->wwwroot.'/blocks/bcgt/grids/student_grid.php?sID='.$studentID.'&qID='.$qualID.'&g=s&cID='.$courseID;
            }
            elseif($view == 's')
            {
                $link = $CFG->wwwroot.'/blocks/bcgt/grids/ass.php?sID='.$studentID.'&qID='.$qualID.'&cID='.$courseID.'&v=s';
            }
            $retval .= $this->get_grid_heading($projects, 
                    $seeTargetGrade, $seeWeightedTargetGrade, 'qual', $projectID, $link, $seeBoth);
            
            $retval .= '<tbody>';
            foreach($userQuals AS $qual)
            {
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                $qualification = Qualification::get_qualification_class_id($qual->id, $loadParams);
                if($qualification && $qualification->has_formal_assessments())
                {
                    $retval .= '<tr>';
                    $user = new stdClass();
                    $user->id = $studentID;
                    $targetGradeObj = null;
                    $userCourseTargets = new UserCourseTarget(-1);
                    $targetGrades = $userCourseTargets->retrieve_users_target_grades($user->id, $qual->id);
                    if($targetGrades)
                    {
                        //as its one qual it will have one object
                        $targetGradeObj = $targetGrades[$qual->id];
                    }
                    $obj = $this->get_grid_info("stu", $qual, $user, $seeTargetGrade, 
                            $seeWeightedTargetGrade, $targetGradeObj, $seeBoth);
                    $retval .= $obj->info;
                    $weightedGradeUsed = $obj->weightedgradeused;
                    if($save)
                    {
                        $qualification->save_user_project_row($studentID, $projects);
                    }
                    $retval .= $qualification->display_user_project_row($studentID, $projects, $editing, 
                            $targetGradeObj, $weightedGradeUsed, $projectID);       
                    $retval .= '</tr>';
                }
            }
            $retval .= '</tbody>';
            $retval .= '</table>';
            $retval .= '</div>';
        }
        
        return $retval;
    }
    
    public function save_student($qualID)
    {
        $valueID = -1;
        $targetGradeID = -1;
        //there is a value and a ceta (targetgrade)       
        if(isset($_POST['sID_'.$this->studentID.'_qID_'.$qualID.'_pID_'.$this->id.'_v']))
        {
            $saveValue = true;
            $valueID = $_POST['sID_'.$this->studentID.'_qID_'.$qualID.'_pID_'.$this->id.'_v'];
        }
        if(isset($_POST['sID_'.$this->studentID.'_qID_'.$qualID.'_pID_'.$this->id.'_c']))
        {
            $saveTarget = true;
            $targetGradeID = $_POST['sID_'.$this->studentID.'_qID_'.$qualID.'_pID_'.$this->id.'_c'];
        }
        
        if(!isset($_POST['sID_'.$this->studentID.'_qID_'.$qualID.'_pID_'.$this->id.'_com']))
        {
            $this->userComments = false;
        }
        
        if($saveValue)
        {
            $this->set_user_value($valueID);
        }
        if($saveTarget)
        {
            $this->set_user_target_grade($targetGradeID);
        }
        //now need to update or insert. 
        if($saveValue || $saveTarget)
        {
            $this->save_user_values($qualID, true);
        }
    }

    public function get_grid_heading($projects, 
            $seeTargetGrade, $seeWeightedTargetGrade, $view, $projectID = -1, $link = '', $seeBoth = false, $seeAlps = false, $qualID = -1)
    {
        $retval = '';
        $retval .= '<thead>';
        $row0 = '<tr>';
        $overallAlpsColSpan = 0;
        $row1 = '<tr>';
        //need to get the global config record
        switch($view)
        {
            case "qual" :
                $row1 .= '<th rowspan="2">'.
                get_string('qual', 'block_bcgt').
                '</th>';
                break;
            case "stu" :
                //columns
                $columns = array('picture', 'username','name');
                $configColumns = get_config('bcgt','btecgridcolumns');
                if($configColumns)
                {
                    $columns = explode(",", $configColumns);
                }
                foreach($columns AS $column)
                {
                    $row1 .= '<th rowspan="2">';
                    $row1 .= get_string(trim($column), 'block_bcgt');
                    $row1 .= '</th>';
                    $overallAlpsColSpan++;
                }
                break;
        }
        $subHead = '';
        //now the two different target grades
        if(($seeBoth && $seeTargetGrade) || (!$seeBoth && !$seeWeightedTargetGrade && $seeTargetGrade))
        {
            $row1 .= '<th rowspan="2">'.get_string('targetgrade', 'block_bcgt').'</th>';
            $overallAlpsColSpan++;
        }
        if($seeWeightedTargetGrade)
        {
            if($seeTargetGrade)
            {
                $string = 'specifictargetgrade';
            }
            else
            {
                $string = 'targetgrade';
            }
            $row1 .= '<th rowspan="2">'.get_string($string, 'block_bcgt').'</th>';
            $overallAlpsColSpan++;
        }
        // | CETA |
        $seeCeta = false;
        if(get_config('bcgt', 'aleveluseceta'))
        {
            $seeCeta = true;
            $row1 .= '<th rowspan="2">'.get_string('ceta', 'block_bcgt').'</th>';
            $overallAlpsColSpan++;
        }
        
        if($seeAlps)
        {
            $row1 .= '<th rowspan="2"></th>';
            $row0 .= '<th colspan="'.$overallAlpsColSpan.'">';
            $row0 .= '<span alpstemp id="alpsclass_'.$qualID.'" class="alpsclass" qual="'.$qualID.'"></span></th>';
            $row0 .= '<th>'.get_string('alps','block_bcgt').'</th>';
        }
        if($projects)
        {
            foreach($projects AS $project)
            {
                if(($projectID != -1 && $project->get_id() == $projectID) || $projectID == -1)
                {
                    //so we are either looking at once project and we have found that project
                    //or we are looking at them all
                    $retvalObj = $project->get_project_heading($projectID, $link, $qualID, null, $seeCeta);
                    $row1 .= $retvalObj->retval;
                    $subHead .= $retvalObj->subHead;
                    $row0 .= $retvalObj->alspHead;
                    if($projectID != -1)
                    {
                        //have we found that project?
                        break;
                    }
                }

            }
        }
        
        if($seeAlps)
        {
            $row0 .= '</tr>';
            $retval .= $row0;;
        }
        $row1 .= '</tr>';
        $retval .= $row1;
        $retval .= '<tr>';
        $retval .= $subHead;
        $retval .= '</tr>';
        $retval .= '</thead>';
        return $retval;
    }
    
    /**
     * 
     * @param type $projectID
     * @param type $link
     * @return \stdClass
     */
    public function get_project_heading($projectID, $link, $qualID = -1, $alps = null, $seeCeta = false)
    {
        //where are we coming back to though? The link needs to show where we are coming back to
        
        //TODO check if using CETA!!!!
        
        global $printGrid;
                
        $fromPortal = (isset($_SESSION['pp_user'])) ? true : false;
        
        $retval = '';
        $class = '';
        $subHead = '';
        $alpsHead = '';
        if($this->is_project_current())
        {
            $class = 'current';
        }
        $colspan = 1;
        if($seeCeta)
        {
            $colspan++;
        }
        
        if ($fromPortal || $printGrid)
        {
            $colspan++;
        }
        
        if ($fromPortal || $printGrid){
            $link = false;
        }
                
        if($projectID != -1)
        {
            $colspan++;
            //we are looking at one project therefeore we are showing comments.
            $retval .= '<th colspan="'.$colspan.'" class="'.$class.'">'.
                    $this->get_name().' - <a href="'.
                    $link.'">'.get_string('viewallassessments', 'block_bcgt').'</a></th>';
            $alpsHead .= '<th><span class="faGradeAlps alpstemp" project="'.$this->get_id().'" qual="'.$qualID.'" id="faGradeAlps_'.$this->get_id().'_'.$qualID.'">';
            if($alps)
            {
                $alpsHead .= $alps->grade;
            }
            $alpsHead .= '</span></th>';
            if($seeCeta)
            {
                $alpsHead .= '<th><span class="faCetaAlps alpstemp" project="'.$this->get_id().'" qual="'.$qualID.'" id="faCetaAlps_'.$this->get_id().'_'.$qualID.'">';
                if($alps)
                {
                    $alpsHead .= $alps->ceta;
                }
                $alpsHead .= '</span></th>';
            }
            
            if ($fromPortal || $printGrid)
            {
                $alpsHead .= '<th></th>';
            }
            
//            //one for the comments
//            $retval .= '<th></th>';
        }
        else
        {
            $retval .= '<th colspan="'.$colspan.'" class="'.$class.'">';
            
            if ($link){
                $retval .= '<a href="'.$link.'&pID='.$this->get_id().
                        '">'.$this->get_name().'</a>';
            }
            else {
                $retval .= $this->get_name();
            }
            
            $retval .= '<br /><span class="projdate">'.$this->get_date().'</span></th>';
            $alpsHead .= '<th><span class="faGradeAlps alpstemp" project="'.$this->get_id().'" qual="'.$qualID.'" id="faGradeAlps_'.$this->get_id().'_'.$qualID.'">';
            if($alps)
            {
                $alpsHead .= $alps->grade;
            }
            $alpsHead .= '</span></th>';
            if($seeCeta)
            {
                $alpsHead .= '<th><span class="faCetaAlps alpstemp" project="'.$this->get_id().'" qual="'.$qualID.'" id="faCetaAlps_'.$this->get_id().'_'.$qualID.'">';
                if($alps)
                {
                    $alpsHead .= $alps->ceta;
                }
                $retval .= '</span></th>';
            }
            
            if ($fromPortal || $printGrid)
            {
                $alpsHead .= '<th></th>';
            }
            
        }
        $subHead .= '<th class="'.$class.'">'.get_string('grade')
                .'</th>';
        if($seeCeta)
        {
            $subHead .= '<th class="'.$class.'">'.get_string('ceta', 'block_bcgt').'</th>';
        }

        if($projectID != -1 || $fromPortal || $printGrid)
        {
            $subHead .= '<th class="'.$class.'">'.get_string('comments');
            $subHead .= '</th>';
        }
        
        $stdObj = new stdClass();
        $stdObj->retval = $retval;
        $stdObj->subHead = $subHead;
        $stdObj->alspHead = $alpsHead;
        
        return $stdObj;
    }
    
    public function get_grid_info($view, $qual, $user, 
            $seeTargetGrade, $seeWeightedTargetGrade, $targetGradeObj, $seeBoth)
    {
        global $OUTPUT;
        $retval = '';
        switch($view)
        {
            case"qual":
                $columns = array('picture', 'username','name');
                $configColumns = get_config('bcgt','btecgridcolumns');
                if($configColumns)
                {
                    $columns = explode(",", $configColumns);
                }
                foreach($columns AS $column)
                {
                    $retval .= '<td>';
                    switch(trim($column))
                    {
                        case("picture"):
                            $retval .= $OUTPUT->user_picture($user, array(1));
                            break;
                        case("username"):
                            $retval .= $user->username;
                            break;
                        case("name"):
                            $retval .= $user->firstname."<br />".$user->lastname;
                            break;
                        case("firstname"):
                            $retval .= $user->firstname;
                            break;
                        case("lastname"):
                            $retval .= $user->lastname;
                            break;
                        case("email"):
                            $retval .= $user->email;
                            break;
                    }
                    $retval .= '</td>';
                }
                break;
            case"stu":
                $retval .= '<td>'.bcgt_get_qualification_display_name($qual, false).'</td>';
                break;
        }
        $targetGrade = 'N/S';
        $weightedTargetGrade = 'N/S';
        $weightedGradeUsed = false;
        if($seeTargetGrade || $seeWeightedTargetGrade)
        {
            if($targetGradeObj)
            {
                if(isset($targetGradeObj->targetgrade))
                {
                    $targetGrade = $targetGradeObj->targetgrade->get_grade();
                }
                elseif(isset($targetGradeObj->breakdown))
                {
                    $targetGrade = $targetGradeObj->breakdown->get_target_grade();
                }
                if(isset($targetGradeObj->weightedtargetgrade))
                {
                    $weightedTargetGrade = $targetGradeObj->weightedtargetgrade->get_grade();
                    if($seeWeightedTargetGrade && $weightedTargetGrade && $weightedTargetGrade != '')
                    {
                        $weightedGradeUsed = true;
                    }
                }
                elseif(isset($targetGradeObj->weightedbreakdown))
                {
                    $weightedTargetGrade = $targetGradeObj->weightedbreakdown->get_target_grade();
                }
            }
        }      
        
        if(($seeBoth && $seeTargetGrade) || (!$seeBoth && !$seeWeightedTargetGrade && $seeTargetGrade))
        {
            $retval .= '<td>'.$targetGrade.'</td>';
        }
        if($seeWeightedTargetGrade)
        {
            $retval .= '<td>'.$weightedTargetGrade.'</td>';
        }
        // | CETA |
        if(get_config('bcgt', 'aleveluseceta'))
        {
            $ceta = $this->get_current_ceta($qual->id, $user->id);
            if($ceta && $ceta->grade)
            {
                $retval .= '<td>'.$ceta->grade.'</td>';
                $cetaRank = $ceta->ranking;
            }
            else
            {
                $cetas = $this->get_most_recent_ceta($qual->id, $user->id);
                if($cetas)
                {
                    $ceta = end($cetas);
                    $retval .= '<td><span class="projNonCurrentCeta">'.$ceta->grade.'</span></td>';
                    $cetaRank = $ceta->ranking;
                }
                else
                {
                    $retval .= '<td></td>';
                }
            }
            
        }
        $stdObj = new stdClass();
        $stdObj->info = $retval;
        $stdObj->weightedgradeused = $weightedGradeUsed;
        return $stdObj;
    }
    
    private function get_current_ceta($qualID, $userID)
    {
        return Qualification::get_current_ceta($qualID, $userID);
    }
    
    private function get_most_recent_ceta($qualID, $userID)
    {
        return Qualification::get_most_recent_ceta($qualID, $userID);
    }
    
    public function load_student_information($studentID, $qualID)
    {
        $this->studentProgress = null;
        $this->studentID = $studentID;
        $this->studentQualID = $qualID;
        
        //go and get everything from the database.
        //studentValue
        //studentTargetGrade
        $userActivityRef = $this->get_user_data();
        if($userActivityRef)
        {
            $this->userComments = $userActivityRef->comments;
            $this->userSetDate = $userActivityRef->dateset;
            $this->userUpdatedDate = $userActivityRef->dateupdated;
            $this->userActivityRefID = $userActivityRef->id;
            $this->userValue = new Value($userActivityRef->valueid, null);
            $this->userSetBy = array($userActivityRef->setbyid=>$userActivityRef->setby);
            $this->userUpdatedBy = array($userActivityRef->updatedbyid=>$userActivityRef->updatedby);
            $this->userDefinedValue = array($userActivityRef->userdefinedvalue);
            //these need to be objects and  classes really. 
            $this->userTargetGrade = new TargetGrade($userActivityRef->bcgttargetgradesid, null);
            $this->userTargetGradeID = $userActivityRef->bcgttargetgradesid;
            $this->userTargetBreakdown = new Breakdown($userActivityRef->bcgttargetbreakdownid, null);
            $this->userTargetBreakdownID = $userActivityRef->bcgttargetbreakdownid;
        }
        else
        {
            $this->userComments = null;
            $this->userSetDate = null;
            $this->userUpdatedDate = null;
            $this->userActivityRefID = null;
            $this->userValue = null;
            $this->userSetBy = null;
            $this->userUpdatedBy = null;
            $this->userDefinedValue = null;
            //these need to be objects and  classes really. 
            $this->userTargetGrade = null;
            $this->userTargetGradeID = null;
            $this->userTargetBreakdown = null;
            $this->userTargetBreakdownID = null;
        }
    }
    
//    process
    
    public function get_user_value()
    {
        return $this->userValue;
    }
    
    public function get_user_grade()
    {
        return $this->userTargetGrade;
    }
    
    public function get_user_grade_id()
    {
        return $this->userTargetGradeID;
    }
    
    public static function get_user_assessments($studentID)
    {
        //get all of the formal assessments that this student could have
        //first get all of the qualifications they are on
        //then get all of the formal assessments on all of these. 
        $projects = array();
        $userQuals = get_users_quals($studentID);
        if($userQuals)
        {
            foreach($userQuals AS $qual)
            {
                $array = Project::get_qual_assessments($qual->id);
                $projects = $projects + $array;
            }
        }
        $retval = new stdClass();
        $retval->quals = $userQuals;
        $retval->projects = $projects;
        return $retval;
    }
    
    public static function get_user_assessment_quals($studentID, $assessmentID)
    {
        //get the students qualifications that are on this assessment:
        global $DB;
        $sql = "SELECT distinct(qual.id),";
        $sql .= bcgt_get_qualification_details_fields_for_sql();
        $sql .= ' FROM {block_bcgt_qualification} qual';
        $sql .= bcgt_get_qualification_details_join_for_sql();
        $sql .= ' JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qual.id 
            JOIN {block_bcgt_activity_refs} refs ON refs.bcgtqualificationid = qual.id AND refs.coursemoduleid IS NULL';
        $sql .= ' WHERE userqual.userid = ? AND refs.bcgtprojectid = ? ORDER BY family.family ASC, level.trackinglevel ASC, subtype.subtype ASC, qual.name ASC';
        return $DB->get_records_sql($sql, array($studentID, $assessmentID));
    }
    
    public function project_on_qual($qualID, $projectID = -1)
    {
        global $DB;   
        $sql = "SELECT * FROM {block_bcgt_activity_refs} WHERE bcgtprojectid = ? AND bcgtqualificationid = ?";
        $params = array();
        if($projectID > 0)
        {
            $params[] = $projectID;
        }
        else
        {
            $params[] = $this->id;
        }
        $params[] = $qualID;
        return $DB->get_record_sql($sql, $params);
    }
    
    public static function get_qual_assessments($qualID, $projectID = -1)
    {
        global $DB;
        $sql = "SELECT distinct(project.id), project.* FROM {block_bcgt_project} project 
            JOIN {block_bcgt_activity_refs} refs ON refs.bcgtprojectid = project.id 
            WHERE refs.bcgtqualificationid = ?";
        $params = array($qualID);
        if($projectID != -1)
        {
            $sql .= ' AND project.id = ?';
            $params[] = $projectID;
        }
        $projects = $DB->get_records_sql($sql, $params);
        $retval = array();
        if($projects)
        {
            foreach($projects AS $project)
            {
                $retval[$project->id] = new Project($project->id, $project);
            }
        }
        return $retval;
    }
    
    private function get_user_data()
    {
        global $DB;
        $sql = "SELECT userref.id, userref.comments, value.id AS valueid, value.value, value.shortvalue, 
            teacher.username as setby, teacher.id as setbyid, 
            teacherup.username AS updatedby, teacherup.id AS updatedbyid,
            userref.userdefinedvalue, grade.grade AS grade, breakdown.targetgrade AS targetgrade, 
            userref.dateset, userref.dateupdated, userref.flag, grade.id as bcgttargetgradesid, breakdown.id as bcgttargetbreakdownid 
            FROM {block_bcgt_user_activity_ref} userref 
            JOIN {block_bcgt_activity_refs} ref ON ref.id = userref.bcgtactivityrefid 
            LEFT OUTER JOIN {block_bcgt_value} value ON value.id = userref.bcgtvalueid 
            LEFT OUTER JOIN {block_bcgt_target_grades} grade ON grade.id = userref.bcgttargetgradesid 
            LEFT OUTER JOIN {block_bcgt_target_breakdown} breakdown ON breakdown.id = userref.bcgttargetbreakdownid 
            LEFT OUTER JOIN {user} teacher ON teacher.id = userref.setbyuserid 
            LEFT OUTER JOIN {user} teacherup ON teacherup.id = userref.updatedbyuserid 
            WHERE ref.bcgtprojectid = ? AND ref.bcgtqualificationid = ? AND userref.userid = ?";
        return $DB->get_record_sql($sql, array($this->id, $this->studentQualID, $this->studentID));
    }
    
    
    
    
    
    
    //ASSIGNMENT STUFF:
    public function is_course_mod_attached_qual($courseModuleID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_activity_refs} refs WHERE coursemoduleid = ? ";
        $params = array($courseModuleID);
        if($DB->get_records_sql($sql, $params))
        {
            return true;
        }
        return false;
    }
    
    public function get_users_on_course_mod($courseModule)
    {
        //need to get all of the users that are on this course module. 
        //courseID and GroupingID
        //find all 'students'
        //if groupingID != -1 then find all of the students in that grouping. 
        global $DB;
        $sql = "SELECT distinct(user.id), user.* FROM {user} user 
            JOIN {role_assignments} ra ON ra.userid = user.id 
            JOIN {role} r ON r.id = ra.roleid 
            JOIN {context} con ON con.id = ra.contextid 
            JOIN {course} course ON course.id = con.instanceid 
            JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = course.id 
            JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = coursequal.bcgtqualificationid AND
            userqual.userid = user.id
            JOIN {role} rolequal ON rolequal.id = userqual.roleid";
        if($courseModule->groupingid)
        {
            $sql .= " JOIN {groupings} groupings ON groupings.courseid = course.id 
                JOIN {groupings_groups} gg ON gg.groupingid = groupings.id 
                JOIN {groups_members} members ON members.groupid = gg.groupid AND members.userid = user.id ";
        }
        $sql .= " WHERE course.id = ? AND rolequal.shortname = ?";
        $params = array();
        $params[] = $courseModule->course;
        $params[] = 'student';
        if($courseModule->groupingid)
        {
            $sql .= " AND groupings.id = ?";
            $params[] = $courseModule->groupingid;
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_users_quals_on_poject($userID, $courseModuleID)
    {
        global $DB;
        $sql = "SELECT distinct(qual.id), qual.* FROM {block_bcgt_qualification} qual 
            JOIN {block_bcgt_activity_refs} refs ON refs.bcgtqualificationid = qual.id 
            JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qual.id 
            WHERE userqual.userid = ? AND refs.coursemoduleid = ?";
        return $DB->get_records_sql($sql, array($userID, $courseModuleID));
    }
    
    public function get_course_mod_units_criteria($courseModuleID, $qualID = -1)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_activity_refs} WHERE coursemoduleid = ?";
        $params = array($courseModuleID);
        if($qualID != -1)
        {
            $sql .= ' AND bcgtqualificationid = ?';
            $params[] = $qualID;
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    public function update_users_qual_cron_grading($userID, $cmID, $scale, $grade){
        
        global $DB;
        
        $quals = $this->get_users_quals_on_poject($userID, $cmID);
        if(!$quals)
        {
            mtrace("NO USER QUALS");
            return false;
        }
        
        
        foreach($quals AS $qual)
        {
            $qualID = $qual->id;
            //now load up the users qualification
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELALL;
            $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
            if(!$qualification)
            {
                mtrace("couldnt load qual");
                return false;
            }
            
            $qualification->load_student_information($userID, $loadParams);
            
            // Get the criteria linked to the project
            $unitsCriteria = $this->get_course_mod_units_criteria($cmID, $qual->id);
            if(!$unitsCriteria)
            {
                mtrace("no Units Criteria");
                return false;
            }
            
            
            $criteriaArray = array();
            
            foreach($unitsCriteria as $unitCriterion){
                
                $unit = $qualification->get_single_unit($unitCriterion->bcgtunitid);
                if (!$unit)
                {
                    mtrace("no unit $unitCriterion->bcgtunitid");
                    continue;
                }
                
                $criteria = $unit->get_single_criteria($unitCriterion->bcgtcriteriaid);
                if (!$criteria)
                {
                    mtrace("no criteria $unitCriterion->bcgtcriteriaid");
                    continue;
                }
                                
                // Only use this criteria if it doesn't already have a met value
                $currentUserValue = $criteria->get_student_value();
                if($currentUserValue)
                {
                    $specialValue = $currentUserValue->get_special_val();
                    if($specialValue == 'A')
                    {
                        //if its WNS or N/A then we can overwrite it. 
                        //WNS can be overwritten with IN or LATE. 
                        mtrace("value already found : ({$currentUserValue->get_short_value()}) for UNITID = $unitCriterion->bcgtunitid AND criteria {$criteria->get_name()}");
                        continue;
                    }
                }
                
                $criteriaArray[] = $criteria;
                
            }
            
            mtrace("calling qualification method to update student's criteria");
            $qualification->update_student_criteria_from_mod_grading($criteriaArray, $scale, $grade);
            
            
            
        }
        
        
    }
    
    public function update_users_qual_cron($userID, $courseModuleID, $action)
    {
        global $DB;
        //get the qual from the unit, criteria and bcgtqualificationid
        $quals = $this->get_users_quals_on_poject($userID, $courseModuleID);
        if(!$quals)
        {
            mtrace("NO USER QUALS");
            return false;
        }
        foreach($quals AS $qual)
        {
            $qualID = $qual->id;
            //now load up the users qualification
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELALL;
            $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
            if(!$qualification)
            {
                mtrace("couldnt load qual");
                return false;
            }
            
            //lets get the value
            $value = new Value();
            mtrace($action);
            $value->create_default_object($action, $qualification->get_family_ID());
            if(!$value->is_enabled())
            {
                mtrace("couldnt find value");
                continue;
            }
            mtrace($value->get_short_value());
            //get the units and criteria that are on this project. 
            $unitsCriteria = $this->get_course_mod_units_criteria($courseModuleID, $qual->id);
            if(!$unitsCriteria)
            {
                mtrace("no Units Criteria");
                return false;
            }
            mtrace("loading : $userID");
//            $qualification->load_student_information($userID, $loadParams);

            foreach($unitsCriteria AS $unitCriteria)
            {
                //get the unit from the qual object
                //get the criteria from the unit object
                //update the users value
                //save
                $unit = $qualification->get_single_unit($unitCriteria->bcgtunitid);
                if(!$unit)
                {
                    mtrace("no unit $unitCriteria->bcgtunitid");
                    continue;
                }

                $criteria = $unit->get_single_criteria($unitCriteria->bcgtcriteriaid);
                if(!$criteria)
                {
                    mtrace("no criteria $unitCriteria->bcgtcriteriaid");
                    continue;
                }
                $criteria->load_student_information($userID, $qualID);
                //does the user already have a value?
                $currentUserValue = $criteria->get_student_value();
                
                // In the case where there are multiple assessments for the same criteria, we want the
                // latest to be displayed, so assuming each time this runs and gets called that will be
                // the latest at that time, we want to change to whatever is relevant then.
                // Unless they have already met the criteria of course
                if ($currentUserValue && Value::is_met($currentUserValue->get_id()))
                {
                    mtrace("criteria for unitID $unitCriteria->bcgtunitid AND criteriaID $unitCriteria->bcgtcriteriaid is already met. So skipping.");
                    continue;
                }
//                if($currentUserValue)
//                {
//                    $shortValue = $currentUserValue->get_short_value();
//                    if($shortValue != 'N/A' && $shortValue != 'WNS')
//                    {
//                        //if its WNS or N/A then we can overwrite it. 
//                        //WNS can be overwritten with IN or LATE. 
//                        mtrace("shortvalue already found : ($shortValue) for UNITID = $unitCriteria->bcgtunitid AND criteria id $unitCriteria->bcgtcriteriaid");
//                        continue;
//                    }
//                }
                mtrace($value->get_short_value());
                mtrace($value->get_id());
                $criteria->set_student_value($value);
                mtrace("save student");
                $criteria->save_student($qualID, true);
            }
        }
    }
    
    public static function get_qual_mods_by_unit($courseID = -1, $qualID = -1, 
            $groupingID = -1, $cmID = -1)
    {
        global $DB;
        $sql = "SELECT refs.id, refs.bcgtunitid, refs.bcgtcriteriaid, crit.name, refs.coursemoduleid 
            FROM {block_bcgt_activity_refs} refs 
            JOIN {block_bcgt_criteria} crit ON crit.id = refs.bcgtcriteriaid";
        if($courseID != -1 || $groupingID != -1)
        {
            $sql .= " JOIN {course_modules} mods ON mods.id = refs.coursemoduleid";
        }
        $params = array();
        if($courseID != -1 || $qualID != -1 || $groupingID != -1)
        {
            $and = false; 
            $sql .= " WHERE";
            if($courseID != -1)
            {
                $sql .= " mods.course = ?";
                $params[] = $courseID;
                $and = true;
            }
            if($qualID != -1)
            {
                if($and)
                {
                    $sql .= ' AND';
                }
                $sql .= " refs.bcgtqualificationid = ?";
                $params[] = $qualID;
                $and = true;
            }
            if($groupingID != -1)
            {
                if($and)
                {
                    $sql .= ' AND';
                }
                $sql .= " mods.groupingid = ?";
                $params[] = $groupingID;
                $and = true;
            }
        }
        if($cmID != -1)
        {
            $sql .= " AND refs.coursemoduleid = ?";
            $params[] = $cmID;
        }
        $records = $DB->get_records_sql($sql, $params);
        if($records)
        {
            $retval = array();
            foreach($records AS $record)
            {
                if(array_key_exists($record->bcgtunitid, $retval))
                {
                    $criteriaArray = $retval[$record->bcgtunitid];
                }
                else
                {
                    $criteriaArray = array();
                }
                if(array_key_exists($record->bcgtcriteriaid, $criteriaArray))
                {
                    $courseModuleArray = $criteriaArray[$record->bcgtcriteriaid];                
                }
                else
                {
                    $courseModuleArray = array();
                }
                $coureModuleArray[$record->coursemoduleid] = $record->coursemoduleid;
                $criteriaArray[$record->bcgtcriteriaid] = $coureModuleArray;
                $retval[$record->bcgtunitid] = $criteriaArray;
            }
            return $retval;
        }
        return false;
        
        
    }
    
    /**
     * This will check if this course has any qualifications, and that any 
     * of these can be linked with mods. 
     * It will then display a blank form
     * or it will display the update form. 
     * @param type $couseModuleID
     * @return string
     */
    public static function display_bcgt_mod_tracker_options($couseModuleID, $courseID)
    {
        $retval = '';
        //has the course got quals that can be associated with assignments?
        $families = get_course_qual_families($courseID, array('BTEC', 'CG'));
        if($families)
        {
            foreach($families AS $family)
            {
                $qualificationClass = Qualification::get_plugin_class($family->id);
                if($qualificationClass)
                {
                    $retval .= $qualificationClass::get_mod_tracker_options($couseModuleID, $courseID);
                    $retval .= "<br><br>";
                }
            }
        }
        //if yes
        
        if (strlen($retval))
        {
            
            $retval = "<label>".get_string('isresit', 'block_bcgt')."</label> <input type='checkbox' name='bcgt_attempt_no' value='2' /><br><br>" . $retval;
            
        }
        
        //display the grid. 
        return $retval;
    }
    
    /**
     * This will be called on save of the mod on a course.
     * This will check if this course has any qualifications, and that any 
     * of these can be linked with mods. 
     * Then it will save the ones from the form into the database. 
     * @param type $couseModuleID
     * @return boolean
     */
    public static function process_bcgt_mod_tracker_options($couseModuleID, $courseID)
    {
        //this needs to get all of the ones saved. 
        $families = get_course_qual_families($courseID, array('BTEC', 'CG'));
        if($families)
        {
            foreach($families AS $family)
            {
                $qualificationClass = Qualification::get_plugin_class($family->id);
                if($qualificationClass)
                {
                    $qualificationClass::process_mod_tracker_options($couseModuleID, $courseID);
                }
            }
        }
        return true;
    }
    
    /**
     * Deletes any activity refs (mod tracker links) that have a coursemoduleid that is no longer
     * in the coursemodules table. 
     * @global type $DB
     */
    public static function delete_redundant_bcgt_mod_tracker_options()
    {
        //find any activity refs that dont have an associated courseModule 
        //in the course module table anymore. 
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_activity_refs} refs WHERE coursemoduleid NOT IN (
            SELECT id FROM {course_modules})";
        $records = $DB->get_records_sql($sql, array());
        if($records)
        {
            foreach($records AS $record)
            {
                $DB->delete_records('block_bcgt_activity_refs', array("id"=>$record->id));
            }
        }
    }
    
    public function is_visible_to_you(){
        
        global $DB, $USER;
        
        $check = $DB->get_record("block_bcgt_project_att", array("bcgtprojectid" => $this->id, "name" => "HIDDEN", "value" => 1));
        
        if (!$check)
        {
            // If not hidden, yes it is visible
            return true;
        }
        
        // Users
        $check = $DB->get_record("block_bcgt_project_att", array("bcgtprojectid" => $this->id, "name" => "VISIBLE_TO"));
        if (!$check)
        {
            // If no users defined yet, no
            return false;
        }
        
        // Get usernames
        $usernames = explode(",", $check->value);
        
        // If in the array, yes we can
        if (in_array($USER->username, $usernames)){
            return true;
        }
        
        return false;
        
    }
    
    
}

?>
