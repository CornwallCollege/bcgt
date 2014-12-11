<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Archive
 *
 * @author mchaney
 */
class Archive {
    //put your code here
    protected $subType;
    protected $type;
    const FORMALASSESSMENTSVALUE = 'fa';
    const STUDENTARCHIVETYPE = 'stu';
    
    const FASTUDBID = 1;
    
    public function Archive($type = null, $subType = null)
    {
        $this->type = $type;
        $this->subType = $subType;
        $this->load_selected_options();
    }
    
    public function load_selected_options()     
    {
        switch($this->type)
        {
            case Archive::STUDENTARCHIVETYPE:
                switch($this->subType)
                {
                    case Archive::FORMALASSESSMENTSVALUE:
                        $this->load_stu_fa_archive_options();
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }
    }
    
    public function run_archive()
    {
        switch($this->type)
        {
            case Archive::STUDENTARCHIVETYPE:
                switch($this->subType)
                {
                    case Archive::FORMALASSESSMENTSVALUE:
                        $this->run_stu_fa_archive_options();
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }
    }
    
    public function get_archive($componentID)
    {
        switch($this->type)
        {
            case Archive::STUDENTARCHIVETYPE:
                switch($this->subType)
                {
                    case Archive::FORMALASSESSMENTSVALUE:
                        return $this->get_stu_fa_archive($componentID);
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }
    }
    
    public function get_types($type)
    {
        $retval = array();
        switch($type)
        {
            case Archive::STUDENTARCHIVETYPE:
                $retval = array(Archive::FORMALASSESSMENTSVALUE=>get_string('formalassessments','block_bcgt'));
                break;
            default:
                $retval = array();
                break;
        }
        return $retval;
    }
    
    public function get_archive_options($type, $subType)
    {
        $retval = '';
        switch($type)
        {
            case Archive::STUDENTARCHIVETYPE:
                switch($subType)
                {
                    case Archive::FORMALASSESSMENTSVALUE:
                        $retval = $this->get_stu_fa_archive_options();
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }
        return $retval;
    }
    
    protected function load_stu_fa_archive_options()
    {
        $this->targetSnapShot = optional_param('targetsnapshot', false, PARAM_BOOL);
        $this->weightedSnapShot = optional_param('weightedsnapshot', false, PARAM_BOOL);
        $this->alpsSnapShot = optional_param('alpssnapshot', false, PARAM_BOOL);
        $this->qualsSelected = isset($_POST['addselect']) ? $_POST['addselect'] : array();
    }
    
    protected function get_stu_fa_archive_options()
    {
        //options: quals to select, 
        //include snapshot Target Grade
        //include snapshot weighted target grade
        //include alps snapshot
        $retval = '';
        $retval .= 'Snapshot Target Grade: ';
        $checked = '';
        if(isset($this->targetSnapShot) && $this->targetSnapShot)
        {
            $checked = 'checked="checked"';
        }
        $retval .= '<input type="checkbox" name="targetsnapshot" '.$checked.'/><br />';
        if(get_config('bcgt', 'allowalpsweighting'))
        {
            $retval .= 'Snapshot Specific Target Grade: ';
            $checked = '';
            if(isset($this->weightedSnapShot) && $this->weightedSnapShot)
            {
                $checked = 'checked="checked"';
            }
            $retval .= '<input type="checkbox" name="weightedsnapshot" '.$checked.'/><br />';
        }
        if(get_config('bcgt', 'calcultealpstempreports'))
        {
            $retval .= 'Snapshot ALPS Scores: ';
            $checked = '';
            if(isset($this->alpsSnapShot) && $this->alpsSnapShot)
            {
                $checked = 'checked="checked"';
            }
            $retval .= '<input type="checkbox" name="alpssnapshot" '.$checked.'/><br />';
        } 
        
        $faFamilies = array();
        $families = get_qualification_type_families_used();
        if($families)
        {
            foreach($families AS $family)
            {
                $familyClass = Qualification::get_plugin_class($family->id);
                if($familyClass && $familyClass::has_formal_assessments())
                {
                    $faFamilies[] = $family;
                }
            }
        }
        $quals = array();
        if($faFamilies)
        {
            $onCourse = true;
            $hasStudents = true;
            foreach($faFamilies AS $family)
            {
                $famQuals = search_qualification(-1, -1, -1, 
                        '', $family->id, '', -1, 
                        $onCourse, $hasStudents);
                $quals = $quals + $famQuals;
            }              
        }
        $retval .= '<p id="archivequals">'.get_string('allavailablequals', 'block_bcgt').'</p>';
        $retval .= '<select name="addselect[]" size="20" id="addselect" multiple="multiple">';
        if($quals)
        {
            foreach($quals AS $qual)
            {
                $selected = '';
                if(in_array($qual->id, $this->qualsSelected))
                {
                    $selected = 'selected';
                }
                    
                $retval .= '<option '.$selected.' value="'.$qual->id.'" title="'.
                    bcgt_get_qualification_display_name($qual).'">'.
                    bcgt_get_qualification_display_name($qual).
                    '</option>';
            }
        }
        $retval .= '</select><br />';
        
        
        return $retval;
    }
    
    protected function run_stu_fa_archive_options()
    {
        set_time_limit(0);
        global $CFG;
        $users = $this->get_users_archive($this->qualsSelected);
        $userCourseTarget = new UserCourseTarget();
        
        $project = new Project();
        $projects = $project->get_all_projects();
        require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ProjectsSorter.class.php');
        $projectSorter = new ProjectsSorter();
        usort($projects, array($projectSorter, "ComparisonDelegateByObjectDueDate"));
        
        if($users)
        {
            foreach($users AS $user)
            {
                $jsonObj= new stdClass();
                $assessmentArray = array();
                $userQuals = bcgt_get_users_quals($user->id);
                if($userQuals)
                {
                    $qualArray = array();
                    foreach($userQuals AS $qual)
                    {
                        $qualObj = new stdClass();
                        //get the name
                        //targetgrade
                        //weightedgrade
                        $qualName = bcgt_get_qualification_display_name($qual);
                        $qualObj->qual = $qualName;
                        if($this->targetSnapShot)
                        {
                            $targetGrade = $userCourseTarget->get_users_target_grade($user->id, $qual->id);
                            if($targetGrade)
                            {
                                $qualObj->targetgrade = $targetGrade->get_grade();
                            }
                        }
                        
                        if(get_config('bcgt', 'allowalpsweighting') && $this->weightedSnapShot)
                        {
                            $weightedGrade = $userCourseTarget->get_users_weighted_target_grade($user->id, $qual->id);
                            if($weightedGrade)
                            {
                                $qualObj->weightedtargetgrade = $weightedGrade->get_grade();
                            }
                        }
                        
                        //now get the projects
                        $assessmentQualArray = array();
                        
                        if($projects)
                        {
                            foreach($projects AS $assessment)
                            {
                                $assessmentName = $assessment->get_name();
                                $assessmentArray[$assessmentName] = $assessmentName;
                                //is this project on this qual?
                                if(!$assessment->project_on_qual($qual->id))
                                {
                                    continue;
                                }

                                $assessmentObj = new stdClass();
                                $assessmentObj->name = $assessmentName;
                                //get the users grade
                                $assessment->load_student_information($user->id, $qual->id);
                                $userGrade = $assessment->get_user_value();
                                if($userGrade)
                                {
                                    $assessmentObj->grade = $userGrade->get_short_value();
                                }
                                
                                if(get_config('bcgt', 'aleveluseceta'))
                                {
                                    $userCeta = $assessment->get_user_grade();
                                    if($userCeta)
                                    {
                                        $assessmentObj->ceta = $userCeta->get_grade();
                                    }
                                }
                                
                                if(get_config('bcgt', 'calcultealpstempreports') && $this->alpsSnapShot)
                                {
                                    //then get the ceta score for this one
                                    $loadParams = new stdClass();
                                    $loadParams->loadLevel = Qualification::LOADLEVELALL;
                                    $qualification = Qualification::get_qualification_class_id($qual->id, $loadParams);
                                    
                                    $temp = $qualification->get_user_fa_ind_alps_temp($user->id, $assessment->get_id(), true);
                                    if(isset($temp->number))
                                    {
                                        $temperature = $temp->number;
                                        $assessmentObj->gradescore = $temperature;
                                        $score = "{".round($temp->score, 3)."}";
                                        $assessmentObj->gradecoef = $score;
                                    }
                                    if(get_config('bcgt', 'aleveluseceta'))
                                    {
                                        $temp = $qualification->get_user_ceta_ind_alps_temp($user->id, $assessment->get_id(), true);
                                        if(isset($temp->number))
                                        {
                                            $temperature = $temp->number;
                                            $assessmentObj->cetascore = $temperature;
                                            $score = "{".round($temp->score, 3)."}";
                                            $assessmentObj->cetacoef = $score;
                                        }
                                    }
                                    
                                }
                                $assessmentQualArray[$assessmentName] = $assessmentObj;
                            }
                            $qualObj->assessments = $assessmentQualArray;
                            $qualArray[] = $qualObj;
                        }
                    }
                    $jsonObj->quals = $qualArray;
                    $jsonObj->projects = $assessmentArray;
                }
                $json = json_encode($jsonObj);
                
                //now go and see if this already exists in the database?
                $record = $this->get_archive_from_database(Archive::FASTUDBID, $user->id);
                if($record)
                {
                    $record->archive = $json;
                    $this->update_archive($record);
                }
                else
                {
                    $record = new stdClass();
                    $record->componentid = $user->id;
                    $record->type = Archive::FASTUDBID;
                    $record->archive = $json;
                    $this->insert_archive($record);
                }
            }
        }
            
        
        
        //get the quals selected
        //for each get the users
        //for each go and get their stuff. 
        //get their target grade?
        //get their weighted target grade?
        //get their alps score?
        //get their projects
        //put projects into seperate array
        //for each project get their ceta, grades (if using ceta)
        //get their scores
        
        //save to database
    }
    
    protected function get_users_archive($qualIDs = null)
    {
        global $DB;
        $sql = "SELECT distinct(user.id) FROM {block_bcgt_user_qual} userqual JOIN 
            {role} role ON role.id = userqual.roleid JOIN {user} user ON user.id = userqual.userid 
            WHERE role.shortname = ?";
        $params = array('student');
        if($qualIDs)
        {
            $count = 0;
            $sql .= " AND userqual.bcgtqualificationid IN (";
            foreach($qualIDs AS $qualID)
            {
                $count++;
                $sql .= '?';
                $params[] = $qualID;
                if($count != count($qualIDs))
                {
                    $sql .= ',';
                }
            }
            $sql .= ')';
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_archive_from_database($type, $componentID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_archive} WHERE componentid = ? AND type = ?";
        return $DB->get_record_sql($sql, array($componentID, $type));
    }
    
    protected function update_archive($object)
    {
        global $DB;
        $DB->update_record('block_bcgt_archive', $object);
    }
    
    protected function insert_archive($object)
    {
        global $DB;
        $DB->insert_record('block_bcgt_archive', $object);
    }
    
    protected function get_stu_fa_archive($userID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_archive} archive WHERE type = ? AND componentid = ?";
        $record = $DB->get_record_sql($sql, array(Archive::FASTUDBID, $userID));
        if($record)
        {
            return $record->archive;
        }
        return false;
    }
            
}

?>
