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
class UserPriorLearning {
    //put your code here
    const GCSE = 'GCSE';
    const GCSES = 'GCSE Short Course';
    const GCSED = 'GCSE Double Award';
    
    protected $bcgtpriorqualid;
    protected $bcgtpriorqual;
    protected $bcgtpriorqualgradesid;
    protected $userid;
    protected $bcgtsubjectid;
    protected $subject;
    protected $examdate;
    protected $checked;
    
    //Array
    protected $usersQuals;
    
    //import options
    protected $importmissingqual;
    protected $importmissingsubject;
    protected $importmissinggrade;
    protected $importmissinguser;
    
    protected $summary;
    protected $success;
    
    public function UserPriorLearning($id = -1, $params = null)
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
                $this->load_user_prlearn($id);            
            }
        }
        elseif($params)
        {
            $this->extract_params($params);
        }
        $this->load_prior_qual_id();
        $this->load_subject();
    }
    
    public function get_headers()
    {
        return array("Username", "Year", "Subject", "Qual", "QualType", "Level","Grade");
    }
    
    public function get_examples()
    {
        return "103772,2012,Maths,GCSE,Normal,2,A*<br />".
                "msmith,2012,Physics,GCSE,Double,2,D<br />ctag12,2010,Art And Design".
                ",BTEC, Level2 Certificate,2, Distinction<br />".
                    "tjackson,2010,FunctionalSkills,-,-,-";
    }
    
    public function get_description()
    {
        return get_string('pldesc', 'block_bcgt');
    }
    
    public function get_file_names()
    {
        return 'userentryquals.csv';
    }
    
    public function has_multiple()
    {
        return false;
    }
    
    public function was_success()
    {
        return $this->success;
    }
    
    public function display_summary()
    {
//        $summary->usersotfound;
//        $summary->qualsNotFound;
//        $summary->gradesNotFound;
//        $summary->subjectsNotFound;
        $retval = '<p><ul>';
        if($this->summary)
        {
            $retval .= '<li>'.get_string('plimportsum1','block_bcgt').' : '.$this->summary->successCount.'</li>';
            if(!$this->success)
            {
                $retval .= '<li>'.get_string('plimportsum2','block_bcgt').' : '.count($this->summary->usersnotfound).'</li>';
                $retval .= '<li>'.get_string('plimportsum3','block_bcgt').' : '.count($this->summary->qualsNotFound).'</li>';
                $retval .= '<li>'.get_string('plimportsum4','block_bcgt').' : '.count($this->summary->gradesNotFound).'</li>';
                $retval .= '<li>'.get_string('plimportsum5','block_bcgt').' : '.count($this->summary->subjectsNotFound).'</li>';
            }
        } 
        $retval .= '</ul></p>';
        return $retval;
    }
    
    /**
     * Saves the Target Grade into the database. 
     * It either updates or inserts. 
     */
    public function save($checkExists = false)
    {
        if($checkExists && $record = $this->get_user_prlearn_by_subject_qual($this->subject, $this->bcgtpriorqualid, $this->userid))
        {
            $this->id = $record->id;
        }
        if($this->id != -1)
        {
            $this->update_entry_qual();
        }
        else
        {
            $this->insert_entry_qual();
        }
    }
    
    public function get_users_quals()
    {
        return $this->usersQuals;
    }
    
    /**
     * Deletes the target grade using the target grade id passed in (from the database.)
     * @global type $DB
     * @param type $targetGradeID
     */
    public static function delete_user_prior_learning($userPriorLearningID)
    {
        global $DB;
        $DB->delete_records('block_bcgt_user_prlearn', array('id'=>$userPriorLearningID));
    }
    
    public function display_import_options()
    {
        $retval = '<table>';
        $retval .= '<tr><td><label for="option1">'.get_string('plcreatemissingqual', 'block_bcgt').' : </label></td>';
        $retval .= '<td><input type="checkbox" checked="checked" name="option1"/></td>';
        $retval .= '<td><span class="description">('.get_string('plcreatemissingqualdesc', 'block_bcgt').')</span></td></tr>';
        $retval .= '<tr><td><label for="option2">'.get_string('plcreatemissingsubject', 'block_bcgt').' : </label></td>';
        $retval .= '<td><input type="checkbox" checked="checked" name="option2"/></td>';
        $retval .= '<td><span class="description">('.get_string('plcreatemissingsubjectdesc', 'block_bcgt').')</span></td></tr>';
        $retval .= '<tr><td><label for="option3">'.get_string('plcreatemissinggrade', 'block_bcgt').' : </label></td>';
        $retval .= '<td><input type="checkbox" checked="checked" name="option3"/></td>';
        $retval .= '<td><span class="description">('.get_string('plcreatemissinggradedesc', 'block_bcgt').')</span></td></tr>';
        $retval .= '<tr><td><label for="option4">'.get_string('plcreatemissinguser', 'block_bcgt').' : </label></td>';
        $retval .= '<td><input type="checkbox" name="option4"/></td>';
        $retval .= '<td><span class="description">('.get_string('plcreatemissinguserdesc', 'block_bcgt').')</span></td></tr>';
        $retval .= '</table>';
        return $retval;
    }
    
    public function get_submitted_import_options()
    {
        if(isset($_POST['option1']))
        {
            $this->importmissingqual = true;
        }
        if(isset($_POST['option2']))
        {
            $this->importmissingsubject = true;
        }
        if(isset($_POST['option3']))
        {
            $this->importmissinggrade = true;
        }
        if(isset($_POST['option4']))
        {
            $this->importmissinguser = true;
        }
    }
    
    /**
     * username|Year|subject|Qual|QualType(Normal,Short,Double),Grade
     * @param type $csvFile
     */
    public function process_import_csv($csvFile, $process = false)
    {
        $summary = new stdClass();
        $usersArray = array();
        $userNotFound = array();
        $qualsNotFound = array();
        $gradesNotFound = array();
        $subjectsNotFound = array();
        $successCount = 0;
        
        global $DB;
        $count = 1;
        $CSV = fopen($csvFile, 'r');
        while(($userPLearn = fgetcsv($CSV)) !== false) {
            if($count != 1)
            {
                //TODO think about lower and upper case: Mark 12th sept 2013. 
                
                
                //find the user
                //find the qualid
                //find the gradeid
                //find the subject
                $user = $DB->get_record_sql('SELECT * FROM {user} WHERE username = ?', array($userPLearn[0]));
                if(!$user)
                {
                    $userNotFound[$userPLearn[0]] = $userPLearn[0];
                    if(!$this->importmissinguser)
                    {
                        continue;
                    }
                    $obj = new stdClass();
                    $obj->username = $userPLearn[0];
                    $obj->email = $userPLearn[0].'@something.something';
                    $obj->firstname = $userPLearn[0];
                    $obj->lastname = $userPLearn[0];
                    $obj->country = 0;
                    $obj->city = 'Unknown';
                    $userID = $DB->insert_record('user', $obj);
                }
                else
                {
                    $userID = $user->id;
                }
                $usersArray[$userID] = $user;
                
                $qualName = $this->check_qual($userPLearn[3], $userPLearn[4]);
                $qual = EntryQual::retrieve_csv($qualName, $userPLearn[5], false);
                if(!$qual)
                {
                    $qualsNotFound[$qualName] = $qualName;
                    if(!$this->importmissingqual)
                    {
                        continue;
                    }
                    //then we are to create the missing qual
                    $params = new stdClass();
                    $params->name = $qualName;
                    $params->quallevel = $userPLearn[5];
                    $entryQual = new EntryQual(-1, $params);
                    $entryQual->save();
                    $qualID = $entryQual->get_id();
                }
                else
                {
                    $qualID = $qual->get_id();
                }
                $grade = EntryGrade::retrieve_csv($qualID, $userPLearn[6], true);
                if(!$grade)
                {
                    $gradesNotFound[$userPLearn[6]] = $userPLearn[6];
                    if(!$this->importmissinggrade)
                    {
                        continue;
                    }
                    $params = new stdClass();
                    $params->grade = $userPLearn[6];
                    $params->bcgtpriorqualid = $qualID;
                    $entryGrade = new EntryGrade(-1, $params);
                    $entryGrade->save();
                    $gradeID = $entryGrade->get_id();
                }
                else
                {
                    $gradeID = $grade->get_id();
                }
                $subject = Subject::retrieve_csv($userPLearn[2], true);
                if(!$subject)
                {
                    $subjectsNotFound[$userPLearn[2]] = $userPLearn[2];
                    if(!$this->importmissingsubject)
                    {
                        continue;
                    }
                    $params = new stdClass();
                    $params->subject = $userPLearn[2];
                    $subject = new Subject(-1, $params);
                    $subject->save();
                    $subjectID = $subject->get_id();
                }
                else
                {
                    $subjectID = $subject->get_id();
                }
                
                //we need to check if this already exists in the database!!!
                //check for the subject. If it exists, update with this grade
                //if it doesnt then insert. 
                $params = new stdClass();
                $params->bcgtpriorqualgradesid = $gradeID;
                $params->userid = $userID;
                $params->bcgtsubjectid = $subjectID;
                $params->examdate = $userPLearn[1];
                $userPLearn = new UserPriorLearning(-1, $params);
                $userPLearn->save(true);
                $successCount++;
            }
            $count++;
        }  
        fclose($CSV);
        
        if($process)
        {
            //then calculate average gcse scores
            $userCourseTarget = new UserCourseTarget();
            $userCourseTarget->calculate_users_average_gcse_score($usersArray, true);
        }
        $success = true;
        if((!$this->importmissinguser && count($userNotFound) > 0) ||
             (!$this->importmissingqual && count($qualsNotFound) > 0) ||
                (!$this->importmissinggrade && count($gradesNotFound)))
        {
            $success = false;
        }
        
        $summary->usersnotfound = $userNotFound;
        $summary->qualsNotFound = $qualsNotFound;
        $summary->gradesNotFound = $gradesNotFound;
        $summary->subjectsNotFound = $subjectsNotFound;
        $summary->successCount = $successCount;
        $this->summary = $summary;
        $this->success = $success;
    }
    
    private function check_qual($qualName1, $qualName2)
    {
        $pQual = $qualName1;
        $pQualType = $qualName2;
        if($qualName1 == 'GCSE' || $qualName1 == 'GCSEs in Vocational Subjects')
        {
            $pQual = 'GCSE';
            if($qualName2 == 'Double' || $qualName2 == 'Double Award' || $qualName2 == 'GCSE Double Award')
            {
                $pQual = 'GCSE Double Award';
            }
            elseif($qualName2 == 'Short' || $qualName2 == 'Short Course' 
                    || $qualName2 == 'GCSE Short Course' || $qualName2 == 'Short Course GCSE')
            {
                $pQual = 'GCSE Short Course';
            }
            $pQualType = '';
        }
        elseif($qualName1 == 'Short Course GCSE')
        {
            $pQual = 'GCSE Short Course';
            $pQualType = '';
        }
        return trim($pQual.' '.$pQualType);
    }
    
    /**
     * This gets only the GCSE prior Learning
     * @global type $DB
     * @param type $userID
     * @return \UserPriorLearning
     */
    public static function get_users_prior_learning($userID)
    {
        //get all of the users prior learning
        global $DB;
        $sql = "SELECT plearn.id, plearn.examdate, qual.id AS bcgtpriorqualid, qual.name as name, 
            qual.weighting as qualweighting, grades.id as bcgtpriorqualgradesid, grades.weighting as gradeweighting, 
            grades.grade as grade, grades.points as points, subject.id as bcgtsubjectid, subject.subject as subject 
            FROM {block_bcgt_user_prlearn} plearn 
            JOIN {block_bcgt_prior_qual_grades} grades ON grades.id = plearn.bcgtpriorqualgradesid 
            JOIN {block_bcgt_prior_qual} qual ON qual.id = grades.bcgtpriorqualid
            JOIN {block_bcgt_subject} subject ON subject.id = plearn.bcgtsubjectid 
            WHERE plearn.userid = ? AND (qual.name = ? || qual.name = ? || qual.name = ?)";
        //this should IDEALLY do an object or UserPriorLearning that has an array of each qual object. This qual object
        //should then have an array of all Grade options!
        $records = $DB->get_records_sql($sql, array($userID, UserPriorLearning::GCSE, UserPriorLearning::GCSES, UserPriorLearning::GCSED));
        if($records)
        {
            $userPriorLearning = new UserPriorLearning(-1, null);
            $userPriorLearning->build_users_prior_learning($records);
            return $userPriorLearning;
        }
    }
    
    /**
     * Gets the params from the object and passes it bak as a new object. 
     * @return \stdClass
     */
    private function get_params()
    {        
        $params = new stdClass();
        $params->bcgtpriorqualgradesid = $this->bcgtpriorqualgradesid;
        $params->userid = $this->userid;
        $params->bcgtsubjectid = $this->bcgtsubjectid;
        $params->examdate = isset($this->examdate)? $this->examdate : '';
        return $params;
    }
    
    public function build_users_prior_learning($priorLearning)
    {
        $qualsArray = array();
        foreach($priorLearning AS $prior)
        {
            if(!array_key_exists($prior->bcgtpriorqualid, $qualsArray))
            {
                $params = new stdClass();
                $params->name = $prior->name;
                $params->weighting = $prior->qualweighting;
                $qualsArray[$prior->bcgtpriorqualid] = new EntryQual($prior->bcgtpriorqualid, $params);
            }
            $entryQual = $qualsArray[$prior->bcgtpriorqualid];
            $params = new stdClass();
            $params->grade = $prior->grade;
            $params->points = $prior->points;
            $params->weighting = $prior->gradeweighting;
            $params->bcgtpriorqualid = $prior->bcgtpriorqualid;
            $entryGrade = new EntryGrade($prior->bcgtpriorqualgradesid, $params);
            $entryGrade->add_user_info($prior->examdate, $prior->bcgtsubjectid, $prior->subject);;
            $entryQual->add_user_grade($entryGrade);
        }
        $this->usersQuals = $qualsArray;
    }
    
    /**
     * Inserts the target grade into the database. 
     * @global type $DB
     */
    private function insert_entry_qual()
    {
        global $DB;
        $params = $this->get_params();
        $this->id = $DB->insert_record('block_bcgt_user_prlearn', $params);
    }
    
    private function update_entry_qual()
    {
        global $DB;
        $params = $this->get_params();
        $params->id = $this->id;
        $DB->update_record('block_bcgt_user_prlearn', $params);
    }
    
    /**
     * Gets the params from the object passed in and puts them onto 
     * the target grade objectl. 
     * @param type $params
     */
    private function extract_params($params)
    {                
        $this->bcgtpriorqualgradesid = $params->bcgtpriorqualgradesid;
        $this->userid = $params->userid;
        $this->bcgtsubjectid = $params->bcgtsubjectid;
        $this->examdate = isset($params->examdate)? $params->examdate : '';
    }
    
    /**
     * gets the target grade from the database and loads onto the obj
     * @global type $DB
     * @param type $id
     */
    private function load_user_prlearn($id)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_prlearn} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($id));
        if($record)
        {
            $this->extract_params($record);
        }
    }
    
    private function load_prior_qual_id()
    {
        global $DB;
        $sql = "SELECT qual.id, qual.name FROM {block_bcgt_prior_qual_grades} grades 
            JOIN {block_bcgt_prior_qual} qual ON qual.id = grades.bcgtpriorqualid 
            WHERE grades.id = ?";
        $record = $DB->get_record_sql($sql, array($this->bcgtpriorqualgradesid));
        if($record)
        {
            $this->bcgtpriorqualid = $record->id;
            $this->bcgtpriorqual = $record->name;
        }
    }
    
    private function load_subject()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_subject} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($this->bcgtsubjectid));
        if($record)
        {
            $this->subject = $record->subject;
        }
        
    }
    
    public function get_user_prlearn_by_subject_qual($subject, $entryQualID, $userID)
    {
        global $DB;
        $sql = "SELECT prlearn.* FROM {block_bcgt_user_prlearn} prlearn 
            JOIN {block_bcgt_prior_qual_grades} grades ON grades.id = prlearn.bcgtpriorqualgradesid
            JOIN {block_bcgt_subject} subject ON subject.id = prlearn.bcgtsubjectid
            WHERE prlearn.userid = ? AND subject.subject = ? AND grades.bcgtpriorqualid = ?";
        return $DB->get_record_sql($sql, array($userID, $subject, $entryQualID));
    }
    
    /**
     * Gets the users prlearn from the database
     * @global type $DB
     * @param type $userID
     * @return database records of the users pr learn
     * prlearn.id, qual.id as qualid, qual.name as qualname, 
            qual.weighting as qualweighting, grades.id as gradeid, grades.grade as grade, 
            grades.weighting as gradeweighting, grades.points as points, subject.id as subjectid, 
            subject.subject as subject, prlearn.examdate as examdate
     */
    public function retrieve_user_plearn($userID)
    {
        global $DB;
        $sql = "SELECT prlearn.id, qual.id as qualid, qual.name as qualname, 
            qual.weighting as qualweighting, grades.id as gradeid, grades.grade as grade, 
            grades.weighting as gradeweighting, grades.points as points, subject.id as subjectid, 
            subject.subject as subject, prlearn.examdate as examdate
            FROM {block_bcgt_user_prlearn} prlearn 
            JOIN {block_bcgt_prior_qual_grades} grades ON grades.id = prlearn.bcgtpriorqualgradesid
            JOIN {block_bcgt_subject} subject ON subject.id = prlearn.bcgtsubjectid
            JOIN {block_bcgt_prior_qual} qual ON qual.id = grades.bcgtpriorqualid
            WHERE prlearn.userid = ?
            ORDER BY prlearn.examdate DESC, grades.points DESC, subject.subject ASC ";
        return $DB->get_records_sql($sql, array($userID));
    }
    
    /**
     * Get a specific PL record for a student
     * @global type $DB
     * @param type $qualname
     * @param type $subject
     * @param int $userID
     */
    public function get_plearn_specific($qualname, $subject, $userID)
    {
        
        global $DB;
        
        $record = $DB->get_record_sql("SELECT prlearn.id, qual.id as qualid, qual.name as qualname, 
                        qual.weighting as qualweighting, grades.id as gradeid, grades.grade as grade, 
                        grades.weighting as gradeweighting, grades.points as points, subject.id as subjectid, 
                        subject.subject as subject, prlearn.examdate as examdate
                        FROM {block_bcgt_user_prlearn} prlearn 
                        JOIN {block_bcgt_prior_qual_grades} grades ON grades.id = prlearn.bcgtpriorqualgradesid
                        JOIN {block_bcgt_subject} subject ON subject.id = prlearn.bcgtsubjectid
                        JOIN {block_bcgt_prior_qual} qual ON qual.id = grades.bcgtpriorqualid
                        WHERE prlearn.userid = ?
                        AND qual.name = ?
                        AND subject.subject = ?", array($userID, $qualname, $subject));
        
        return $record;
        
        
    }
    
    /**
     * Thuis checks the database. 
     * If the user has priorlearn it will return noGCSE
     * If the user has no priorlearn it will return noQOE
     * @global type $DB
     * @param type $userID
     * @return string
     */
    public function get_users_prlearn_status_when_no_target($userID)
    {
        $targetGradeOut = '';
        global $DB;
        //then do we have prior learning?
        $sql = "SELECT * FROM {block_bcgt_user_prlearn} WHERE userid = ?";
        $records = $DB->get_records_sql($sql, array($userID));
        if($records)
        {
            //then we do have prior learning.
            //do we have an avg score?
            $targetGradeOut = '<span style="color:grey">'.
                    get_string('reportnogcse', 'block_bcgt').'</span>';
        }
        else
        {
            $targetGradeOut = '<span style="color:grey">'.
                    get_string('reportnopl', 'block_bcgt').'</span>';
        }
        return $targetGradeOut;
    }
    
}