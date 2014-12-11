<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Breakdown
 *
 * @author mchaney
 */
class UserCourseTarget {
    //put your code here
    
    protected $id; 
    protected $averageScore;
    
    //array of quals with breakdowns, targetgrades etc
    protected $userID;
    protected $usersTargetGrades;
    protected $courseid;
    
    //iimport options
    protected $insertmissingbreakdown;
    protected $insertmissingtargetgrade;
    protected $calculateAspGrade;
    
    protected $bcgtqualificationid;
    protected $userid;
    protected $bcgttargetbreakdownid;
    protected $bcgttargetgradesid;
    protected $bcgtweightedbreakdownid;
    protected $bcgtweightedgradeid;

    protected $calculateAspGrades;
    
    protected $success;
    protected $summary;
    
    protected $testResults;
    
    public function UserCourseTarget($id = -1, $params = null)
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
                $this->load_user_course_target($id);
            }
        }
        elseif($params)
        {
            $this->extract_params($params);
        }
    }
    
    public function calculate_aspirational_grades_check($calculateAspGrades)
    {
        $this->calculateAspGrades = $calculateAspGrades;
    }
    
    public function get_test_page()
    {
        $score = optional_param('score', '', PARAM_TEXT);
        $qualID = optional_param('qual', -1, PARAM_INT);
        $retval = '';
        $retval .= '<p>'.get_string('targetgradestestdesc','block_bcgt').'</p>';
        $retval .= '<label for="score">'.get_string('avggcsescore', 'block_bcgt').' : </label>';
        $retval .= '<input type="text" name="score" value="'.$score.'"/>';
        $retval .= '<input type="hidden" name="view" value="tg"/>';
        $retval .= '<label for="qual">'.get_string('qualification', 'block_bcgt').' : </label>';
        $retval .= '<select name="qual">';
        $retval .= '<option value="-1"></option>';
        $qualifications = search_qualification();
        foreach($qualifications AS $qual)
        {
            $selected = '';
            if($qualID == $qual->id)
            {
                $selected = 'selected';
            }
            $retval .= '<option '.$selected.' value='.$qual->id.'">'.  bcgt_get_qualification_display_name($qual).'</option>';
        }
        $retval .= '</select>';
        $retval .= '<input type="submit" name="run" value="'.get_string('run', 'block_bcgt').'"/>';
        return $retval;
    }
    
    public function process_test()
    {
        if(isset($_POST['run']))
        {
            $qualID = optional_param('qual', -1, PARAM_INT);
            $score = optional_param('score', -1, PARAM_INT);
            if($qualID != -1)
            {
                $userCourseTarget = new UserCourseTarget();
                $testResults = $userCourseTarget->calculate_user_target_grade(-1, 
                    $score, false, false, $qualID);
                $this->testResults = $testResults;    
            }
            else
            {
                $this->testResults = null;
            }
            //get the average gcse score and then calculate the scores.  
        }
        else
        {
            $this->testResults = null;
        }
    }
    
    public function get_test_page_results()
    {
        $qualID = optional_param('qual', 1, PARAM_INT);
        $retval = '';
        if($this->testResults)
        {
            $weightedTargetGradeMethod = get_config('bcgt','weightedtargetmethod');
            $retval .= '<p>'.get_string('labelweightedtargetmethod', 'block_bcgt').
                    ' : '.$weightedTargetGradeMethod.' -> '.
                    get_string('descweightedtargetmethod','block_bcgt').'</p>';
            $results = $this->testResults;
            //then output:
            $retval .= '<table>';
            $retval .= '<tr><th>'.get_string('breakdown', 'block_bcgt').'</th>';
            $retval .= '<th>'.get_string('targetgrade', 'block_bcgt').'</th>';
            $retval .= '<th>'.get_string('aspbreakdown', 'block_bcgt').'</th>';
            $retval .= '<th>'.get_string('asptargetgrade', 'block_bcgt').'</th>';
            $retval .= '<th>'.get_string('weightedbreakdown', 'block_bcgt').'</th>';
            $retval .= '<th>'.get_string('weightedtargetgrade', 'block_bcgt').'</th>';
            $retval .= '</tr>';
            
            foreach($results AS $result)
            {
                $retval .= '<tr>';
                //$stdObj->breakdown = $breakdown;
                //$stdObj->targetgrade = $targetGrade;
                //$stdObj->teachersetbreakdown = $aspBreakdown;
                //$stdObj->teachersettargetgrade = $aspTargetGrade;
                //$stdObj->weightedbreakdown = $weightedBreakdown;
                //$stdObj->weightedtargetgrade = $weightedTargetGrade;
                $retval .= '<td>'.(isset($result->breakdown)? $result->breakdown->get_target_grade().' ('.get_string('ucaspoints', 'block_bcgt').': '.$result->breakdown->get_ucas_points().')'   : 'N/A').'</td>';
                $retval .= '<td>'.(isset($result->targetgrade)? $result->targetgrade->get_grade().' ('.get_string('ucaspoints', 'block_bcgt').': '.$result->targetgrade->get_ucas_points().')'   : 'N/A').'</td>';
                $retval .= '<td>'.(isset($result->teachersetbreakdown)? $result->teachersetbreakdown->get_target_grade().' ('.get_string('ucaspoints', 'block_bcgt').': '.$result->teachersetbreakdown->get_ucas_points().')'   : 'N/A').'</td>';
                $retval .= '<td>'.(isset($result->teachersettargetgrade)? $result->teachersettargetgrade->get_grade().' ('.get_string('ucaspoints', 'block_bcgt').': '.$result->teachersettargetgrade->get_ucas_points().')'   : 'N/A').'</td>';
                $retval .= '<td>'.(isset($result->weightedbreakdown)? $result->weightedbreakdown->get_target_grade().' ('.get_string('ucaspoints', 'block_bcgt').': '.$result->weightedbreakdown->get_ucas_points().')' : 'N/A').'</td>';
                $retval .= '<td>'.(isset($result->weightedtargetgrade)? $result->weightedtargetgrade->get_grade().' ('.get_string('ucaspoints', 'block_bcgt').': '.$result->weightedtargetgrade->get_ucas_points().')'  : 'N/A').'</td>';
                $retval .= '<td>'.get_string('coefficient', 'block_bcgt').' : '.$result->coefficient.'<br />'.
                    get_string('newavgscore','block_bcgt').' : '.$result->newaveragegcsescore.'<br />'.
                        get_string('newweighteducas', 'block_bcgt').' : '.$result->weighteducaspoints.'</td>';
                $retval .= '</tr>';
                
            }
            
            $retval .= '</table>';
            global $DB;
            $targetGradeValues = $DB->get_records_sql("SELECT grades.* FROM {block_bcgt_target_grades} grades 
                JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = grades.bcgttargetqualid 
                JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetqual.id 
                WHERE qual.id = ? ORDER BY ranking DESC", array($qualID));
            if($targetGradeValues)
            {
                $retval .= '<table>';
                $retval .= '<tr><th>'.get_string('targetgrade', 'block_bcgt').'</th>';
                $retval .= '<th>'.get_string('ucaspoints', 'block_bcgt').'</th>';
                $retval .= '<th>'.get_string('gcselower', 'block_bcgt').'</th>';
                $retval .= '<th>'.get_string('gcseupper', 'block_bcgt').'</th>';
                $retval .= '<th>'.get_string('ranking', 'block_bcgt').'</th>';
                $retval .= '</tr>';
                foreach($targetGradeValues AS $value)
                {
                    $retval .= '<tr>';
                    $retval .= '<td>'.$value->grade.'</td>';
                    $retval .= '<td>'.$value->ucaspoints.'</td>';
                    $retval .= '<td>'.$value->lowerscore.'</td>';
                    $retval .= '<td>'.$value->upperscore.'</td>';
                    $retval .= '<td>'.$value->ranking.'</td>';
                    $retval .= '</tr>';
                }
                $retval .= '</tr>';
                $retval .= '</table>';
            }
        }
        return $retval;
    }
    
    public function get_headers()
    {
        return array("QualFamily", "QualLevel", "QualSubtype", "QualName", 
            "QualAdditionalName", "Username", "FullTargetGrade", "TargetGrade", "AverageGcseScore");
    }
    
    public function get_examples()
    {
        return "ALevel,Level 3,AS Level,Economics,,12932,ABBB,A/B,<br />".
                "ALevel,Level 3,AS Level,Economics,,jsmith,CCCD/CCCC,C,48.9<br />".
                "ALevel,Level 3,AS Level,Economics,,jsmith12,,,38.92<br />".
                "BTEC,Level 3,Extended Diploma,Engineering,2012,123213,MMM,MMM,<br />".
                "BTEC,Level 3,Extended Diploma,Sport,Year 2,123213,,,33<br />";
    }
    
    public function get_description()
    {
        return get_string('tddesc', 'block_bcgt');
    }
    
    public function get_file_names()
    {
        return 'usertargets.csv';
    }
    
    public function has_multiple()
    {
        return false;
    }
    
    public function display_import_options()
    {
        $retval = '<table>';
        $retval .= '<tr><td><label for="option1">'.get_string('tgcreatemissingfulltarget', 'block_bcgt').' : </label></td>';
        $retval .= '<td><input type="checkbox" checked="checked" name="option1"/></td>';
        $retval .= '<td><span class="description">('.get_string('tgcreatemissingfulltargetdesc', 'block_bcgt').')</span></td></tr>';
        $retval .= '<tr><td><label for="option2">'.get_string('tgcreatemissingtargetgrade', 'block_bcgt').' : </label></td>';
        $retval .= '<td><input type="checkbox" checked="checked" name="option2"/></td>';
        $retval .= '<td><span class="description">('.get_string('tgcreatemissingtargetgradedesc', 'block_bcgt').')</span></td></tr>';
        
        $retval .= '<tr><td><label for="option3">'.get_string('plcalculateaspgrades', 'block_bcgt').' : </label></td>';
        $retval .= '<td><input type="checkbox" name="option3"/></td>';
        $retval .= '<td><span class="description">('.get_string('plcalculateaspgradesdesc', 'block_bcgt').')</span></td></tr>';
        
        $retval .= '</table>';
//        $retval .= '<label for="">'.get_string('plcreatemissinguser', 'block_bcgt').' : </label>';
//        $retval .= '<input type="checkbox" name="option1"/>';
//        $retval .= '<span class="description">('.get_string('plcreatemissinguserdesc', 'block_bcgt').')</span><br />';
        return $retval;
    }
    
    public function get_submitted_import_options()
    {
        if(isset($_POST['option1']))
        {
            $this->insertmissingbreakdown = true;
        }
        if(isset($_POST['option2']))
        {
            $this->insertmissingtargetgrade = true;
        }
        if(isset($_POST['option3']))
        {
            $this->calculateAspGrade = true;
        }
        else
        {
            $this->calculateAspGrade = false;
        }
    }
    
    public function calculate_quals_target_grades(array $qualIDs, $calculateNewAverageGCSEScore = true, 
            $justCalculateWeightedTargets = false, $abstractUser = false, $averageGCSEScore = null)
    {
        //get all of the users who are on the qualIDs
        //for each calculate their target grades/ 
        foreach($qualIDs AS $qualID)
        {
            $qualification = Qualification::get_qualification_class_id($qualID);
            if($qualification)
            {
                if(!$abstractUser)
                {
                    $users = $qualification->get_students();
                    if($users)
                    {
                        foreach($users AS $user)
                        {
                            $userCourseTarget = new UserCourseTarget();
                            $userCourseTarget->calculate_user_target_grade($user->id, 
                                    null, $calculateNewAverageGCSEScore, $justCalculateWeightedTargets, $qualID);
                        }
                    }
                }
                else
                {
                    $userCourseTarget = new UserCourseTarget();
                    $userCourseTarget->calculate_user_target_grade(-1, 
                        $averageGCSEScore, $calculateNewAverageGCSEScore, $justCalculateWeightedTargets, $qualID);
                }
                
            }
        }
    }
    
    public function was_success()
    {
        return $this->success;
    }
    
    public function display_summary()
    {
        $retval = '<p><ul>';
        $retval .= '<li>'.get_string('tgimportsum1','block_bcgt').' : '.$this->summary->successCount.'</li>';
        if(!$this->success)
        {
            $retval .= '<li>'.get_string('tgimportsum4','block_bcgt').' : '.count($this->summary->qualsNotFound).'</li>'; 
            $retval .= '<li>'.get_string('plimportsum2','block_bcgt').' : '.count($this->summary->usersnotfound).'</li>';
            $retval .= '<li>'.get_string('tgimportsum2','block_bcgt').' : '.count($this->summary->breakdownsNotFound).'</li>';
            $retval .= '<li>'.get_string('tgimportsum3','block_bcgt').' : '.count($this->summary->targetGradesNotFound).'</li>';
        }
        $retval .= '</ul></p>';
        return $retval;
    }
        
    /**
     * Saves the breakdpown. Either inserts or updates. 
     */
    public function save($checkIfExists = false, $qualID = -1)
    {
        if($checkIfExists && $record = $this->get_target_by_qual())
        {
            $this->id = $record->id;
        }
        if($this->id != -1)
        {
            $this->update_user_course_target();
        }
        else
        {
            $this->insert_user_course_target();
        }
    }
    
    public function calculate_user_average_score($userID, $calculateTargetGrade = false)
    {
        //calculate the users average score by taking into consideration all
        //of their gcse quals on entry and the grades
        //get all of the users points:
        $overallPoints = 0;
        $numberOfEntries = 0;
        $usersPriorLearning = UserPriorLearning::get_users_prior_learning($userID); 
        if($usersPriorLearning)
        {
            $usersQuals = $usersPriorLearning->get_users_quals();
            if($usersQuals)
            {
                foreach($usersQuals AS $qual)
                {
                    $qualWeighting = $qual->get_weighting();
                    if(!$qualWeighting || $qualWeighting == 0)
                    {
                        $qualWeighting = 1;
                    }
                    $grades = $qual->get_users_grades();
                    if($grades)
                    {
                        foreach($grades AS $grade)
                        {
                            $points = $grade->get_points();
                            //are there actually points?????
                            //we may have an issue where a brand new grade gets entered (Lets say {PASS} at a GCSE, 
                            //we wont have any points for this! So lets NOT use it to do any average calculations)
                            if($points != 0 || $grade->get_grade() == 'U')
                            {
                                $gradeWeighting = $grade->get_weighting();
                                if(!$gradeWeighting || $gradeWeighting == 0)
                                {
                                    $gradeWeighting = 1;
                                }
                                //e.g. Double GCSE has a qual Weighting of 2
                                //normal gcse has qual weighting of 1
                                //normal grade of an A has weighting of 1 and points is just one
                                //weird grade which is AB, has a weighting of 2. Its points is points for A plus points for B
                                $numberOfEntries = $numberOfEntries + ($gradeWeighting * $qualWeighting);
                                //so normally the calculation above is just add 1, equivalant to NumberOfEntries++

                                $totalValue = $points*$qualWeighting;
                                $overallPoints = $overallPoints + $totalValue;
                            }
                            
                        }
                    }
                }
            }
        }
        $averageGCSEScore = -1;
        if($numberOfEntries != 0)
        {
            $averageGCSEScore = $overallPoints/$numberOfEntries;
        }
        if($averageGCSEScore != -1)
        {
            $this->averageScore = $averageGCSEScore;
            $this->save_user_averagescore($userID);
        }
        if($calculateTargetGrade)
        {
            $this->calculate_user_target_grade($userID, $averageGCSEScore, false);
        }
        //get all of the users PriorLearning
        //loop over them, calculate an average score
    }
    
    /**
     * 
     * @param type $userID
     * @param type $averageGCSEScore
     * @param type $calculateNewAverageScore
     * @param type $justCalculateWeightedTargets
     */
    public function calculate_user_target_grade($userID, $averageGCSEScore = null, $calculateNewAverageScore = false, 
            $justCalculateWeightedTargets = false, $qualID = null)
    {
        $this->usersTargetGrades = array();
        //if userID is -1 then we are doing an abstract
        $this->userID = $userID;
        //get the averagescore
        //calculate the users target grade based on their average score or calculate
        //a new one. 
        if($calculateNewAverageScore)
        {
            $this->calculate_user_average_score($userID, false);
            $averageGCSEScore = $this->averageScore;
        }
        elseif(($calculateNewAverageScore && !$averageGCSEScore) || !$averageGCSEScore)
        {
            $averageScore = $this->get_users_average_score($userID);
            if($averageScore)
            {
                $averageGCSEScore = $averageScore->averagegcsescore;
            }
        }
        if($averageGCSEScore)
        {
            $useAsp = false;
            if(get_config('bcgt','autocalculateasptargetgrade') && 
                        get_config('bcgt','autocalcaspvalue'))
            {
                $useAsp = true;
            }
            //get the targetbreakdown, targetgrade, weightedtargets etc for this averagecsore for all of
            //the quals 
            //find the users quals
            if(!$qualID && $userID != -1)
            {
                //then we are doing all quals for this user
                $usersQuals = get_users_quals($userID);
                foreach($usersQuals AS $qual)
                {
                    $this->process_target_grade_calc($qual, $userID, $useAsp, 
                            $averageGCSEScore, $justCalculateWeightedTargets);
                }
            }
            else 
            {
                //we have one qualID and or no userID
                //we are assuming its just we have one qualID and we dont have the userID, rather
                //than the other way around. 
                global $DB;
                $qual = $DB->get_record_sql("SELECT * FROM {block_bcgt_qualification} WHERE id = ?", array($qualID));
                //we have a single qual.
                $this->process_target_grade_calc($qual, $userID, $useAsp, 
                            $averageGCSEScore, $justCalculateWeightedTargets);
            }
            
            $recalculateAspGrades = false;
            if(isset($this->calculateAspGrades))
            {
                $recalculateAspGrades = $this->calculateAspGrades;
            }
            
            if($userID != -1)
            {
                $this->save_user_target_grades($recalculateAspGrades);
            }
            else
            {
                //we are just returning
                return $this->usersTargetGrades;
            }
        }
        else
        {
            //we need to remove all old target grades and also reset all target grades to na if 
            //we have no avg gcse score
            //e.g. we were enrolled on a course, and now we arent, or a qual
            $this->reset_users_old_targets();
        }
    }
    
    public function calculate_weighted_target_grade($qual, $userID, $courseID, $record)
    {
       
        global $DB;
        
        $qualFamily = $qualFamily = bcgt_get_family_for_qual($qual->get_id());
        $weighting = new QualWeighting(-1, null);
        $calculatingWeightings = $weighting->can_family_have_weighted_target_grades( $qualFamily );
                
        $coefficient = $weighting->get_coefficient_for_qual($qual->get_id());
        $useConstant = get_config('bcgt','weightedtargetgradesuseconstant');
        $weightedMethod = get_config('bcgt','weightedtargetgradesclosestgrade');//will be UP or DOWN
        
        $qualWeighting = new QualWeighting();
        $constant = $qualWeighting->get_constant($qual->get_target_qual_ID());
        
        $params = new stdClass();
        $params->userid = $userID;
        $params->bcgtqualificationid = $qual->get_id();
        $params->courseid = $courseID;
        
        if($calculatingWeightings)
        {

            // Weighted single grade, e.g. B
            if ($record->bcgttargetgradesid)
            {
                
                $targetGrade = $DB->get_record("block_bcgt_target_grades", array("id" => $record->bcgttargetgradesid));
                $targetGradeObj = new TargetGrade($targetGrade->id, $targetGrade);

                $weightedTargetGrade = false;
                if($targetGradeObj)
                {
                    $targetGradeUcasPoints = $targetGradeObj->get_ucas_points();
                    $newUcasPointsTarget = $targetGradeUcasPoints * $coefficient;
                    if($useConstant)
                    {
                        $newUcasPointsTarget = $newUcasPointsTarget + $constant;
                    } 
                    $weightedTargetGrade = new TargetGrade(-1, null);
                    $weightedTargetGrade->get_target_grade_ucas_points($qual->get_target_qual_ID(), $newUcasPointsTarget, $weightedMethod);
                }

                if ($weightedTargetGrade && $weightedTargetGrade->get_id() > 0)
                {
                    $params->bcgtweightedgradeid = $weightedTargetGrade->get_id();
                }
                else 
                {
                    $params->bcgtweightedgradeid = $record->bcgttargetgradesid;
                }
                
                $params->bcgttargetgradesid = $record->bcgttargetgradesid;
            
            }

        }               
        
        $userCourseTarget = new UserCourseTarget(-1, $params); 
        $userCourseTarget->save(true, $qual->get_id());    
        
        // Remove duplicates
        $this->remove_redundant_grade_records();
        
    }
    
    protected function process_target_grade_calc($qual, $userID, $useAsp, $averageGCSEScore, $justCalculateWeightedTargets)
    {
        
        //the object is: (Where the breakdown and targetGrades are instances of the classes Breakdown and TargetGrade)
        //$stdObj->breakdown = $breakdown;
        //$stdObj->targetgrade = $targetGrade;
        //$stdObj->teachersetbreakdown = $aspBreakdown;
        //$stdObj->teachersettargetgrade = $aspTargetGrade;
        //$stdObj->weightedbreakdown = $weightedBreakdown;
        //$stdObj->weightedtargetgrade = $weightedTargetGrade;
        
        $aspBreakdown = null;
        $aspTargetGrade = null;
        $qualsArray = $this->usersTargetGrades;
        if(!$justCalculateWeightedTargets)
        {
            //get the breakdown, targetgrade, weightedtargets etc for this averagescore for the qual
            $breakdown = new Breakdown(-1, null);
            $breakdown->get_breakdown_average_score($qual->bcgttargetqualid, $averageGCSEScore);
            $targetGrade = new TargetGrade(-1, null);
            $targetGrade->get_target_grade_average_score($qual->bcgttargetqualid, $averageGCSEScore);
            
            if($useAsp)
            {
                //so we are auto calculating an aspirational grade and we want to set it to the autocalcaspvalue
                $aspBreakdown = $breakdown->get_breakdown_asp(get_config('bcgt','autocalcaspvalue'));
                $aspTargetGrade = $targetGrade->get_target_asp(get_config('bcgt','autocalcaspvalue'));
            }
        }
        //then do the weightings
        $newUcasPointsTarget = 'N/A';
        $newAverageGcseScore = 'N/A';
        $weightedBreakdown = null;
        $weightedTargetGrade = null;
        
        //are we allows to calculate a weighted target grade for this qual?
        //what is the family:
        if(isset($qual->family))
        {
            $qualFamily = $qual->family;
        }
        else
        {
            $qualFamily = bcgt_get_family_for_qual($qual->id);
        }
        $coefficient = null;
        $weighting = new QualWeighting(-1, null);
        $calculatingWeightings = $weighting->can_family_have_weighted_target_grades($qualFamily);
        if($calculatingWeightings)
        {
            $coefficient = $weighting->get_coefficient_for_qual($qual->id);
            if($coefficient)
            {
                //get_config: 
                $weightedTargetGradeMethod = get_config('bcgt','weightedtargetmethod');
                switch($weightedTargetGradeMethod)
                {
                    //after speaking with PAUL:
                    
                    //we shall:Do weighting multiplication
                    //get new ucaspoints
                    //Add contstant : get from the global settings
                    //Get NEXT grade UP

                    case 1:
                        //multiply the average gcse score by the coeeficient
                        $newAverageGcseScore = $averageGCSEScore*$coefficient;
                        $weightedBreakdown = new Breakdown(-1, null);
                        $weightedBreakdown->get_breakdown_average_score($qual->bcgttargetqualid, $newAverageGcseScore);   

                        $weightedTargetGrade = new TargetGrade(-1, null);
                        $weightedTargetGrade->get_target_grade_average_score($qual->bcgttargetqualid, $newAverageGcseScore);
                        break;
                    case 2:
                        //multiply the target grade ucas points by the coefficient
                        //are we just calculating the weighted target grade? if so we need to go and get the
                        //students target grade from the database
                        if($justCalculateWeightedTargets && $userID != -1)
                        {
                            $usersTargetGrades = $this->retrieve_users_target_grades($userID, $qual->id);
                            if($usersTargetGrades)
                            {
                                $targetGrade = $usersTargetGrades[$qual->id]->targetgrade;
                                $breakdown = $usersTargetGrades[$qual->id]->breakdown;
                            }
                        }
                        //are we artificially inflating by adding a constant?
                        $useConstant = get_config('bcgt','weightedtargetgradesuseconstant');
                        $weightedMethod = get_config('bcgt','weightedtargetgradesclosestgrade');//will be UP or DOWN
                        $qualWeighting = new QualWeighting();
                        $constant = $qualWeighting->get_constant($qual->bcgttargetqualid);
                        if(isset($targetGrade) && $targetGrade->get_id() > 0)
                        {
                            $targetGradeUcasPoints = $targetGrade->get_ucas_points();
                            $newUcasPointsTarget = $targetGradeUcasPoints * $coefficient;
                            if($useConstant)
                            {
                                //$constant = get_config('bcgt','weightedtargetgradeconstant');
                                //hard code to 1.65 for now
                                
                                //this wants to be half the ucas points difference in grades, for this qual.
                                $newUcasPointsTarget = $newUcasPointsTarget + $constant;
                            } 
                            $weightedTargetGrade = new TargetGrade(-1, null);
                            $weightedTargetGrade->get_target_grade_ucas_points($qual->bcgttargetqualid, $newUcasPointsTarget, $weightedMethod);
                        }
                        if(isset($breakdown) && $breakdown->get_id() > 0)
                        {
                            $breakdownUcasPoints = $breakdown->get_ucas_points();
                            $newUcasPointsBreakdown = $breakdownUcasPoints * $coefficient;
                            if($useConstant)
                            {                                
                                $newUcasPointsBreakdown = $newUcasPointsBreakdown + $constant;
                            }
                            $weightedBreakdown = new Breakdown(-1, null);
                            $weightedBreakdown->get_breakdown_ucas_points($qual->bcgttargetqualid, $newUcasPointsBreakdown, $weightedMethod); 
                        }
                        break;
                    default:
                        //do nothing
                        break;
                }

            }
        }
        //find the targetpercentage
        //find the coefficient
        //multiply the avergescore by that
        //find the breakdown and targetgrade by this new averagescore
        $stdObj = new stdClass();
        if(!$justCalculateWeightedTargets)
        {
            $stdObj->breakdown = $breakdown;
            $stdObj->targetgrade = $targetGrade;
//            $stdObj->teachersetbreakdown = $aspBreakdown;
//            $stdObj->teachersettargetgrade = $aspTargetGrade;
        }
        if($weightedBreakdown)
        {
            $stdObj->weightedbreakdown = $weightedBreakdown;
        }
        else
        {
            $stdObj->weightedbreakdown = $breakdown;
        }
        if($weightedTargetGrade)
        {
            $stdObj->weightedtargetgrade = $weightedTargetGrade;
        }
        else 
        {
            $stdObj->weightedtargetgrade = $targetGrade;
        }
        $stdObj->coefficient = $coefficient;
        $stdObj->weighteducaspoints = $newUcasPointsTarget;
        $stdObj->newaveragegcsescore = $newAverageGcseScore;
        $stdObj->id = $qual->id;
        $qualsArray[$qual->id] = $stdObj;
        $this->usersTargetGrades = $qualsArray;
    }
    
    protected function get_ucas_grade_difference()
    {
        
    }
    
    public function save_user_target_grades($recalculateAspGrades)    
    {
        $usersCourses = array();
        global $DB;
        $usersQuals = array();
        //we might not have any target grades!
        if($this->usersTargetGrades)
        {
            foreach($this->usersTargetGrades AS $qual)
            {
                $usersQuals[$qual->id] = $qual->id;
                //what is the users course that this qualification is on?
                $courseID = -1;
                $courses = Qualification::get_user_course($qual->id, $this->userID, true);
                if($courses)
                {
                    foreach($courses AS $course)
                    {
                        $courseID = $course->courseid;
                        $usersCourses[$courseID] = $courseID;
                        $this->save_user_target_grades_db($qual, $courseID, $recalculateAspGrades);
                    }
                }
                else
                {
                    $this->save_user_target_grades_db($qual, $courseID, $recalculateAspGrades);
                }
            }
            //so we need to now get rid of all of the target grades for old course
            //and for old quals
        }
        $this->remove_users_old_targets(null, $usersQuals);
    }
    
    /**
     * Deletes all users course targets where the qualID and/or courseid is not in 
     * the two arrays 
     * @param type $usersCourses
     * @param type $usersQuals
     */
    private function remove_users_old_targets($usersCourses = null, $usersQuals = null)
    {
        global $DB;
        $sql = 'DELETE FROM {block_bcgt_user_course_trgts} WHERE userid = ?';
        $params = array($this->userID);
        if($usersCourses)
        {
            $totalCount = count($usersCourses);
            $count = 0;
            $sql .= ' AND courseid NOT IN (';
            foreach($usersCourses AS $courseID)
            {
                $count++;
                $sql .= '?';
                if($totalCount != $count)
                {
                    $sql .= ',';
                }
                $params[] = $courseID;
            }
            $sql .= ')';
        }
        if($usersQuals)
        {
            $totalCount = count($usersQuals);
            $count = 0;
            $sql .= ' AND bcgtqualificationid NOT IN (';
            foreach($usersQuals AS $qualID)
            {
                $count++;
                $sql .= '?';
                if($totalCount != $count)
                {
                    $sql .= ',';
                }
                $params[] = $qualID;
            }
            $sql .= ')';
        }
        $DB->execute($sql, $params); 
    }
    
    /**
     * Deletes all users course targets where the qualID and/or courseid is not in 
     * the two arrays 
     * @param type $usersCourses
     * @param type $usersQuals
     */
    private function reset_users_old_targets($usersCourses = null, $usersQuals = null)
    {
        global $DB;
        $sql = 'UPDATE {block_bcgt_user_course_trgts} SET bcgttargetbreakdownid = ?, bcgttargetgradesid = ?, 
            bcgtweightedbreakdownid = ?, bcgtweightedgradeid = ? WHERE userid = ?';
        $params = array(-1, -1, -1, -1, $this->userID);
        if($usersCourses)
        {
            $totalCount = count($usersCourses);
            $count = 0;
            $sql .= ' AND courseid NOT IN (';
            foreach($usersCourses AS $courseID)
            {
                $count++;
                $sql .= '?';
                if($totalCount != $count)
                {
                    $sql .= ',';
                }
                $params[] = $courseID;
            }
            $sql .= ')';
        }
        if($usersQuals)
        {
            $totalCount = count($usersQuals);
            $count = 0;
            $sql .= ' AND bcgtqualificationid NOT IN (';
            foreach($usersQuals AS $qualID)
            {
                $count++;
                $sql .= '?';
                if($totalCount != $count)
                {
                    $sql .= ',';
                }
                $params[] = $qualID;
            }
            $sql .= ')';
        }
        $DB->execute($sql, $params); 
    }
    
    private function save_user_target_grades_db($qual, $courseID, $recalculateAspGrades = false)
    {
        global $DB;
        $stdObj = new stdClass();
        $stdObj->userid = $this->userID;
        $stdObj->bcgtqualificationid = $qual->id;
        if(isset($qual->targetgrade))
        {
            $stdObj->bcgttargetgradesid = $qual->targetgrade->get_id();
        }
        if(isset($qual->breakdown))
        {
            $stdObj->bcgttargetbreakdownid = $qual->breakdown->get_id();
        }
        if(isset($qual->teachersetbreakdown))
        {
            $stdObj->teacherset_breakdownid = $qual->teachersetbreakdown->get_id();
        }
        if(isset($qual->teachersettargetgrade))
        {
            $stdObj->teacherset_targetid = $qual->teachersettargetgrade->get_id();
        }
        global $USER;
        $stdObj->teacherset_teacherid = $USER->id;
        $stdObj->courseid = $courseID;
        $stdObj->userid = $this->userID;
        if(isset($qual->weightedtargetgrade) && $qual->weightedtargetgrade->get_id() && $qual->weightedtargetgrade->get_id() != -1)
        {
            $stdObj->bcgtweightedgradeid = $qual->weightedtargetgrade->get_id();
        }
        elseif(isset($qual->targetgrade))
        {
            //if we dont have a weighting then just add the target grade niormal
            $stdObj->bcgtweightedgradeid = $qual->targetgrade->get_id();
        }
        if(isset($qual->weightedbreakdown) && $qual->weightedbreakdown->get_id() && $qual->weightedbreakdown->get_id() != -1)
        {
            $stdObj->bcgtweightedbreakdownid = $qual->weightedbreakdown->get_id();
        }
        elseif(isset($qual->breakdown))
        {
            //incase we dont have a weighted, lets just use the normal
            $stdObj->bcgtweightedbreakdownid = $qual->breakdown->get_id();
        }
        if($records = $this->retrieve_users_target_grades($this->userID, $qual->id, $courseID))
        {
            //one qualification id therefore it will find only one record. 
            $record = end($records);
            if(isset($record->usercoursetargetsid))
            {
                $id = $record->usercoursetargetsid;
                $stdObj->id = $id;
                $DB->update_record('block_bcgt_user_course_trgts',$stdObj);
            }
            
        }
        else {
            //we are inserting brand new
            $DB->insert_record('block_bcgt_user_course_trgts',$stdObj);
        }
        //also need to save the aspirational target grade in the stud_course_grade table
        if(isset($qual->teachersetbreakdown) && $recalculateAspGrades)
        {
            $this->set_student_course_grade($this->userID, 'aspirational', $courseID, $qual->id, 'block_bcgt_target_breakdown', $qual->teachersetbreakdown->get_id());
        }
        if(isset($qual->teachersettargetgrade) && $recalculateAspGrades)
        {
            $this->set_student_course_grade($this->userID, 'aspirational', $courseID, $qual->id, 'block_bcgt_target_grades', $qual->teachersettargetgrade->get_id());
        }
    }
    
    protected function set_student_course_grade($userID, $type, $courseID, $qualID, $location, $recordID)
    {
        global $DB, $USER;
        $update = true;
        $stdObj = $DB->get_record("block_bcgt_stud_course_grade", array("userid" => $userID, "qualid" => $qualID, "type" => $type));
        if(!$stdObj)
        {
            $update = false;
            $stdObj = new stdClass();
        }
        $stdObj->userid = $userID;
        $stdObj->qualid = $qualID;
        $stdObj->courseid = $courseID;
        $stdObj->type = $type;
        $stdObj->recordid = $recordID;
        $stdObj->setbyuserid = $USER->id;
        $stdObj->settime = time();
        $stdObj->location = $location;
        
        if($update)
        {
            $DB->update_record('block_bcgt_stud_course_grade', $stdObj);
        }
        else
        {
            $DB->insert_record('block_bcgt_stud_course_grade', $stdObj);
        }
    }
    
    
    
    
    public function calculate_users_average_gcse_score(array $users = null, $calculateTargetGrade = false)
    {
//        $userCourseTarget = new UserCourseTarget();
        global $DB;
        //if the users is null then we are calculating all
        if(!$users)
        {
            $users = $DB->get_records_sql("SELECT * FROM {user}");
        }
        foreach($users AS $user)
        { 
            if(is_object($user))
            {
                $userID = $user->id;
            }
            else
            {
                $userID = $user;
            }
            $this->calculate_user_average_score($userID, $calculateTargetGrade);
        }
        
        //now remove all extra target grades
        $this->remove_redundant_grade_records();
        
    }
    
    public function calculate_users_target_grades(array $users = null, $recaclculateAverageScore = false)
    {
        //        $userCourseTarget = new UserCourseTarget();
        global $DB;
        //if the users is null then we are calculating all
        if(!$users)
        {
            $users = $DB->get_records_sql("SELECT * FROM {user}");
        }
        foreach($users AS $user)
        {
            $this->calculate_user_target_grade($user->id, null, $recaclculateAverageScore);
        }
        //now remove all extra target grades
        $this->remove_redundant_grade_records();
    }
    
    private function remove_redundant_grade_records()
    {
        global $DB;
        $sql = "DELETE FROM {block_bcgt_user_course_trgts} 
            WHERE (bcgttargetbreakdownid = ? OR bcgttargetbreakdownid IS NULL OR bcgttargetbreakdownid = ?) 
            AND (bcgttargetgradesid = ? OR bcgttargetgradesid IS NULL OR bcgttargetgradesid = ?) 
            AND (bcgtweightedgradeid = ? OR bcgtweightedgradeid IS NULL OR bcgtweightedgradeid = ?)
            AND (bcgtweightedbreakdownid = ? OR bcgtweightedbreakdownid IS NULL OR bcgtweightedbreakdownid = ?) 
            ";
        $DB->execute($sql, array(-1, 0,
                -1, 0,
                -1, 0,
                -1, 0));        
    }
    
    private function save_user_averagescore($userID)
    {
        $stdObj = new stdClass();
        $stdObj->averagegcsescore = $this->averageScore;
        $stdObj->userid = $userID;
        $averageScore = $this->get_users_average_score($userID);
        if($averageScore)
        {
            $stdObj->id = $averageScore->id;
            $this->update_average_score($stdObj);
        }
        else
        {
            $this->insert_average_score($stdObj);
        }
    }
        
    public static function update_average_score($record)
    {
        global $DB;
        $DB->update_record('block_bcgt_user_prior', $record);
    }
    
    public static function insert_average_score($record)
    {
        global $DB;
        $DB->insert_record('block_bcgt_user_prior', $record);
    }
    
    public static function get_users_average_score($userID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_prior} WHERE userid = ?";
        return $DB->get_record_sql($sql, array($userID));
    }
    
    public static function get_users_target_grade($userID, $qualID)
    {
        global $DB;
        $sql = "SELECT distinct(target.id), target.* FROM {block_bcgt_user_course_trgts} utargets 
            JOIN {block_bcgt_target_grades} target ON target.id = utargets.bcgttargetgradesid 
            WHERE userid = ? AND bcgtqualificationid = ? ";
        $records = $DB->get_records_sql($sql, array($userID, $qualID));
        if($records)
        {
            $record = end($records);
            return new TargetGrade($record->id, $record);
        }
        return false;
    }
    
    public static function get_users_weighted_target_grade($userID, $qualID)
    {
        global $DB;
        $sql = "SELECT distinct(target.id), target.* FROM {block_bcgt_user_course_trgts} utargets 
            JOIN {block_bcgt_target_grades} target ON target.id = utargets.bcgtweightedgradeid 
            WHERE userid = ? AND bcgtqualificationid = ? ";
        $records = $DB->get_records_sql($sql, array($userID, $qualID));
        if($records)
        {
            $record = end($records);
            return new TargetGrade($record->id, $record);
        }
        return false;
    }
    
    /**
     * Gets the params from the object and returns an object of them. 
     * @return \stdClass
     */
    private function get_params()
    {
        $params = new stdClass();
        $params->bcgtqualificationid = $this->bcgtqualificationid;
        $params->userid = $this->userid;
        $params->courseid = $this->courseid;
        $params->bcgttargetbreakdownid = $this->bcgttargetbreakdownid;
        $params->bcgttargetgradesid = $this->bcgttargetgradesid;
        $params->bcgtweightedbreakdownid = $this->bcgtweightedbreakdownid;
        $params->bcgtweightedgradeid = $this->bcgtweightedgradeid;
        return $params;
    }
    
    /**
     * Deletes the breakdown from the database using passed in ID. 
     * @global type $DB
     * @param type $breakdownID
     */
    public static function delete_user_course_target($userCourseTargetID)
    {
        global $DB;
        $DB->delete_records('block_bcgt_user_course_trgts', array('id'=>$userCourseTargetID));
    }
    
    /**
     * QualFamily|QualLevel|QualSubtype|QualName|QualAdditionalName|username|fulltargetgrade|targetgrade|averagegcsescore(optional)
     * @param type $csvFile
     */
    public function process_import_csv($csvFile, $process = false)
    {
        global $DB;
        $usersNotFound = array();
        $breakdownsNotFound = array();
        $targetGradesNotFound = array();
        $successCount = 0;
        $qualsNotFound = array();
        $usersArray = array();
        $studentRole = $DB->get_record_sql("SELECT * FROM {role} WHERE shortname = ?", array('student'));
        $studentRoleID = $studentRole->id;
        $count = 1;
        $CSV = fopen($csvFile, 'r');
        while(($targetGrade = fgetcsv($CSV)) !== false) {
            if($count != 1)
            {
                //first find the user
                if (empty($targetGrade[5])) continue;
                
                $user = $DB->get_record_sql('SELECT * FROM {user} WHERE username = ?', array($targetGrade[5]));
                if(!$user)
                {
                    $usersNotFound[$targetGrade[5]] = $targetGrade[5];
                    continue;
                }
                                
                $userID = $user->id;
                $usersArray[$userID] = $user; 
                //first find the qual
                $quals = Qualification::retrieve_qual(-1, -1, $targetGrade[0], -1, 
            '', -1, $targetGrade[1], -1, $targetGrade[2], $targetGrade[3]);
                if($quals)
                {
                    foreach($quals AS $qual)
                    {
                        
                        if(isset($qual->family))
                        {
                            $qualFamily = $qual->family;
                        }
                        else
                        {
                            $qualFamily = bcgt_get_family_for_qual($qual->id);
                        }
                        
                        
                        //might find more than one. but the user will only be on one
                        if(Qualification::check_user_on_qual($userID, $studentRoleID, $qual->id))
                        {
                            $breakdownID = -1;
                            $breakdown = null;
                            if(isset($targetGrade[6]))
                            {
                                if($targetGrade[6] != '' && $qual->bcgttargetqualid)
                                {
                                    $breakdown = Breakdown::retrieve_breakdown(-1, $qual->bcgttargetqualid, $targetGrade[6]);
                                    if(!$breakdown)
                                    {
                                        $breakdownsNotFound[$targetGrade[6]] = $targetGrade[6];
                                        if(!$this->insertmissingbreakdown)
                                        {
                                            continue;
                                        }
                                        $obj = new stdClass();
                                        $params = new stdClass();
                                        $params->bcgttargetqualid = $qual->bcgttargetqualid;
                                        $params->targetgrade = $targetGrade[6];
                                        $breakdown = new Breakdown(-1, $params);
                                        $breakdown->save();
                                    }
                                    $breakdownID = $breakdown->get_id();
                                }
                                
                            }
                            $targetGradeID = -1;
                            $targetGradeObj = null;
                            if(isset($targetGrade[7]))
                            {
                                if($targetGrade[7] != '' && $qual->bcgttargetqualid)
                                {
                                    $targetGradeObj = TargetGrade::retrieve_target_grade(-1, $qual->bcgttargetqualid, $targetGrade[7]);
                                    if(!$targetGradeObj)
                                    {
                                        $targetGradesNotFound[$targetGrade[7]] = $targetGrade[7];
                                        if(!$this->insertmissingtargetgrade)
                                        {
                                            continue;
                                        }
                                        $obj = new stdClass();
                                        $params = new stdClass();
                                        $params->bcgttargetqualid = $qual->bcgttargetqualid;
                                        $params->grade = $targetGrade[6];
                                        $targetGradeObj = new TargetGrade(-1, $params);
                                        $targetGradeObj->save();
                                    }
                                    $targetGradeID = $targetGradeObj->get_id();
                                }
                                
                            }
                            
                            //what is the users course that this qualification is on?
                            $courseID = -1;
                            $courses = Qualification::get_user_course($qual->id, $userID, true);
                            if($courses)
                            {
                                foreach($courses AS $course)
                                {
                                    $courseID = $course->courseid;
                                    //what happens if its already been inserted into the database?
                                    //save will take care of this for us
                                    $params = new stdClass();
                                    $params->bcgtqualificationid = $qual->id;
                                    $params->userid = $userID;
                                    $params->courseid = $courseID;
                                    $params->bcgttargetbreakdownid = $breakdownID;
                                    $params->bcgttargetgradesid = $targetGradeID;
                                    
                                    // Calculate weighted
                                    if ($breakdown)
                                    {
                                        
                                        $weighting = new QualWeighting(-1, null);
                                        $calculatingWeightings = $weighting->can_family_have_weighted_target_grades($qualFamily);
                                        if($calculatingWeightings)
                                        {

                                            // Weighted overall grade, e.g. CCDD
                                            $coefficient = $weighting->get_coefficient_for_qual($qual->id);
                                            $useConstant = get_config('bcgt','weightedtargetgradesuseconstant');
                                            $weightedMethod = get_config('bcgt','weightedtargetgradesclosestgrade');//will be UP or DOWN
                                            $qualWeighting = new QualWeighting();
                                            $constant = $qualWeighting->get_constant($qual->bcgttargetqualid);

                                            $breakdownUcasPoints = $breakdown->get_ucas_points();
                                            $newUcasPointsBreakdown = $breakdownUcasPoints * $coefficient;
                                            if($useConstant)
                                            {                                
                                                $newUcasPointsBreakdown = $newUcasPointsBreakdown + $constant;
                                            }

                                            $weightedBreakdown = new Breakdown(-1, null);
                                            $weightedBreakdown->get_breakdown_ucas_points($qual->bcgttargetqualid, $newUcasPointsBreakdown, $weightedMethod); 

                                            if($weightedBreakdown && $weightedBreakdown->get_id() > 0)
                                            {
                                                $params->bcgtweightedbreakdownid = $weightedBreakdown->get_id();
                                            }
                                            else
                                            {
                                                $params->bcgtweightedbreakdownid = $breakdownID;
                                            }
                                            
                                            
                                            // Weighted single grade, e.g. B
                                            $weightedTargetGrade = false;
                                            if($targetGradeObj)
                                            {
                                                $targetGradeUcasPoints = $targetGradeObj->get_ucas_points();
                                                $newUcasPointsTarget = $targetGradeUcasPoints * $coefficient;
                                                if($useConstant)
                                                {
                                                    $newUcasPointsTarget = $newUcasPointsTarget + $constant;
                                                } 
                                                $weightedTargetGrade = new TargetGrade(-1, null);
                                                $weightedTargetGrade->get_target_grade_ucas_points($qual->bcgttargetqualid, $newUcasPointsTarget, $weightedMethod);
                                            }
                                            
                                            if ($weightedTargetGrade && $weightedTargetGrade->get_id() > 0)
                                            {
                                                $params->bcgtweightedgradeid = $weightedTargetGrade->get_id();
                                            }
                                            else 
                                            {
                                                $params->bcgtweightedgradeid = $targetGradeID;
                                            }

                                        }                            
                                    
                                    }
                                                                                 
                                    $userCourseTarget = new UserCourseTarget(-1, $params); 
                                    $userCourseTarget->save(true, $qual->id);                                    
                                    $successCount++;
                                    
                                    // Save to student_course_grades table as well, since we should be using this one
                                    if ($params->bcgttargetgradesid > 0){
                                        $this->set_student_course_grade($userID, 'target', $courseID, $qual->id, 'block_bcgt_target_grades', $params->bcgttargetgradesid);
                                    } elseif ($params->bcgttargetbreakdownid > 0){
                                        $this->set_student_course_grade($userID, 'target', $courseID, $qual->id, 'block_bcgt_target_breakdown', $params->bcgttargetbreakdownid);
                                    }
                                                                    
                                    if($this->calculateAspGrade)
                                    {
                                        //then we also need to calculate the aspirational grade
                                        if($breakdown)
                                        {
                                            $aspBreakdown = $breakdown->get_breakdown_asp(get_config('bcgt','autocalcaspvalue'));
                                            if($aspBreakdown)
                                            {
                                                $this->set_student_course_grade($userID, 'aspirational', $courseID, $qual->id, 'block_bcgt_target_breakdown', $aspBreakdown->get_id());
                                            }
                                                                                        
                                        }

                                        if($targetGradeObj)
                                        {
                                            $aspTargetGrade = $targetGradeObj->get_target_asp(get_config('bcgt','autocalcaspvalue'));
                                            if($aspTargetGrade)
                                            {
                                                $this->set_student_course_grade($userID, 'aspirational', $courseID, $qual->id, 'block_bcgt_target_grades', $aspTargetGrade->get_id());
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            //student isnt on qual that was found
                        }
                    }
                }
                else
                {
                    //no quals were found
                    $qualsNotFound[$targetGrade[0].' '.$targetGrade[1].' '.$targetGrade[2].' '.$targetGrade[3]] = $targetGrade[0].' '.$targetGrade[1].' '.$targetGrade[2].' '.$targetGrade[3];
                }
                if(isset($targetGrade[8]) && $targetGrade[8] != '')
                {
                    //the averagescore
                    $record = new stdClass();
                    $record->userid = $userID;
                    $record->averagegcsescore = $targetGrade[8];
                    $userAverageScore = UserCourseTarget::get_users_average_score($userID);
                    if($userAverageScore)
                    {
                        $record->id = $userAverageScore->id;
                        UserCourseTarget::update_average_score($record);
                    }
                    else
                    {
                        UserCourseTarget::insert_average_score($record);
                    }
                    
                }
                //then find the course its on that this user is on
                //then find the target grade
                //then put into the database 
            }
            $count++;
        }
        if($process && isset($targetGrade[8]) && $targetGrade[8] != '' && (!isset($targetGrade[7]) && (!isset($targetGrade[6]))))
        {
            //then calculate target grades
            $userCourseTarget = UserCourseTarget();
            $userCourseTarget->calculate_aspirational_grades_check($this->calculateAspGrade);
            $userCourseTarget->calculate_users_target_grades($usersArray);
        }
        $success = true;
        if((count($usersNotFound) > 0) || (count($qualsNotFound) > 0) ||
             (!$this->insertmissingbreakdown && count($breakdownsNotFound) > 0) ||
                (!$this->insertmissingtargetgrade && count($targetGradesNotFound)))
        {
            $success = false;
        }
        $summary = new stdClass();
        $summary->usersnotfound = $usersNotFound;
        $summary->successCount = $successCount;
        $summary->breakdownsNotFound = $breakdownsNotFound;
        $summary->targetGradesNotFound = $targetGradesNotFound;
        $summary->qualsNotFound = $qualsNotFound;
        $this->summary = $summary;
        $this->success = $success;
    }
    
    public static function retrieve_user_course_target($id = -1)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_breakdown} WHERE"; 
        $params = array();
        if($id != -1)
        {
            $sql .= " id = ?";
            $params[] = $id;
        }
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return new UserCourseTarget($record->id, $record);
        }
        return false;
    }
    
    /**
     * Inserts breakdown into database
     * @global type $DB
     */
    private function insert_user_course_target()
    {
        global $DB;
        $params = $this->get_params();
        $this->id = $DB->insert_record('block_bcgt_user_course_trgts', $params);
    }
    
    /**
     * Updates breakdown in database
     * @global type $DB
     */
    private function update_user_course_target()
    {
        global $DB;
        $params = $this->get_params();
        $params->id = $this->id;
        $DB->update_record('block_bcgt_user_course_trgts', $params);
    }
    
    /**
     * Extracts the params from the object and puts it onto the obj. 
     * @param type $params
     */
    private function extract_params($params)
    {
        $this->bcgtqualificationid = $params->bcgtqualificationid;
        $this->userid = $params->userid;
        $this->courseid = $params->courseid;
        $this->bcgttargetbreakdownid = $params->bcgttargetbreakdownid;
        $this->bcgttargetgradesid = $params->bcgttargetgradesid;
        if(isset($params->bcgtweightedbreakdownid)) $this->bcgtweightedbreakdownid = $params->bcgtweightedbreakdownid;
        if(isset($params->bcgtweightedgradeid)) $this->bcgtweightedgradeid = $params->bcgtweightedgradeid;
    }
    
    /**
     * Gets breakdown from the database and loads it into this obj. 
     * @global type $DB
     * @param type $id
     */
    private function load_user_course_target($id)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_course_trgts} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($id));
        if($record)
        {
            $this->extract_params($record);
        }
    }
    
    private function get_target_by_qual()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_course_trgts} WHERE bcgtqualificationid = ? AND userid = ? AND courseid = ?";
        return $DB->get_record_sql($sql, array($this->bcgtqualificationid, $this->userid, $this->courseid), IGNORE_MULTIPLE);
    }
    
    //Reporting:
    
    //Get users target grade (by course/by qual)
    /**
     * This will get a users Target Grades either by a qualification, course or for the user overall
     * It will return an object. 
     * This object will have on it an array of qualifications or an array of courses
     * for each one of these it will have several objects:
     * breakdown
     * targetgrade
     * weightedbreakdown
     * weightedtargetgrade
     * @global type $DB
     * @param type $userID
     * @param type $qualID
     * @return \stdClass|boolean
     */
    public function retrieve_users_target_grades($userID, $qualID = -1, $courseID = -1)    
    {
        $reporting = new Reporting();
        return $reporting->get_users_target_grades($userID, $qualID, $courseID);
    }
    
    
    
}