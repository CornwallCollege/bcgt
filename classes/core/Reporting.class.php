<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Reporting
 *
 * @author mchaney
 */
class Reporting {
    
    //todo get user further ahead
    //todo get user further behind
    //todo rank students ahead/behind
    //todo ucas points
    //todo ucas point differences
    //todo get users with no pl by course, by qual, by me, overall
    ////todo get users with no avg by course, by qual, by me, overall
    //todo get users with no tg by course, by qual, by me, overall
    //todo get users with no predicted grade by course, by qual, by me, overall
    //todo extreme target grades
    //todo extreme qual awards
    
    //average gcse scores:
    //todo average level by course and by qual and by user of prior learning
    //highest level by course, by qual, by user
    //lowest highest level of a user by qual by course. 
    
    public function Reporting()
    {
        
    }   
    
    public function get_report()
    {
        $retval = '<div><h2>Results</h2>';
        $reportID = optional_param('report', -1, PARAM_TEXT);
        $studentID = optional_param('sID', -1, PARAM_INT);
        $qualID = optional_param('qID', -1, PARAM_INT);
        $courseID = optional_param('courseID', -1, PARAM_INT);
        $comparison = optional_param('grade', '', PARAM_TEXT);
        switch($reportID)
        {
            case 'u1':
                //Target Grades
                $object = $this->get_users_target_grades($studentID);
                $retval .= '<table><tr>';
                    $retval .= '<th>'.get_string('qualification', 'block_bcgt').'</th>';
                    $retval .= '<th>'.get_string('targetgrade', 'block_bcgt').'</th></tr>';
                foreach($object AS $qID=>$qualObject)
                {
                    $retval .= '<tr><td>';
                    $qual = Qualification::get_qualification_class_id($qID);
                    if($qual)
                    {
                        $retval .= $qual->get_display_name();
                    }
                    $retval .= '</td><td>';
                    if($comparison == "full")
                    {
                        if(isset($qualObject->targetgrade))
                        {
                            $retval .= $qualObject->targetgrade->get_grade();
                        }
                        elseif(isset($qualObject->breakdown))
                        {
                            $retval .= $qualObject->breakdown->get_target_grade();
                        }
                    }
                    elseif($comparison == "weight")
                    {
                        if(isset($qualObject->weightedtargetgrade))
                        {
                            $retval .= $qualObject->weightedtargetgrade->get_grade();
                        }
                        elseif(isset($qualObject->weightedbreakdown))
                        {
                            $retval .= $qualObject->weightedbreakdown->get_target_grade();
                        }
                    }
                    $retval .= '</td></tr>';
                }
                $retval .= '</table>';
                break;
            case 'u2':
                //Average GCSE Score
                $retval .= $this->get_users_average_gcse_score($studentID);
                break;
            case 'u3':
                //Average Value Added
                $obj = $this->get_users_average_target_current_status($studentID, -1, -1, $comparison);
                $retval .= $obj;
                break;
            case 'u4':
                //Extreme Value Added
                $obj = $this->get_users_extreme_target_current_status($studentID, -1, -1, $comparison);
                $retval .= $obj;
                break;
            case 'u5':
                //Combined Value Added
                $obj = $this->get_users_overall_target_current_status($studentID, -1, -1, $comparison);
                $retval .= $obj;
                break;
            case 'u6':
                //Overall Value Added
                $obj = $this->get_users_target_current_status($studentID, -1, -1, $comparison);
                if($obj)
                {
                    $retval .= '<table>';
                            $retval .= '<tr><td>'.get_string('qualification', 'block_bcgt').'</td>'.
                                    '<td>'.get_string('status', 'block_bcgt').'</td></tr>';
                    foreach($obj AS $qualID=>$status)
                    {
                        $qualification = Qualification::get_qualification_class_id($qualID);
                        if($qualification)
                        {
                            $retval .= '<tr><td>'.$qualification->get_display_name().'</td><td>';
                            if($status > 0)
                            {
                                $retval .= get_string('ahead', 'block_bcgt');
                            } 
                            elseif($status < 0)
                            {
                                $retval .= get_string('behind', 'block_bcgt');
                            }
                            else
                            {
                                $retval .= get_string('on', 'block_bcgt');
                            }
                            $retval .= '</td></tr>';
                        }
                    }
                    $retval .= '</table>';
                }
                break;
            case 'c1':
                //Average 'Average Gcse Score'
                $obj = $this->average_course_users_avg_score($courseID);
                $retval .= $obj->average;
                break;
            case 'c2':
                //Max Users VA
                $obj = $this->calc_course_users_va($courseID, $comparison, 'MAX');
                $retval .= $this->get_single_database_report_table($obj);
                break;
            case 'c3':
                //Min Users VA
                $obj = $this->calc_course_users_va($courseID, $comparison, 'MIN');
                $retval .= $this->get_single_database_report_table($obj);
                break;
            case 'c4':
                //Average Users VA
                $obj = $this->calc_course_users_va($courseID, $comparison, 'AVG');
                $retval .= $this->get_single_database_report_table($obj);
                break;
            case 'c5':
                //Combined/Total Users VA
                $obj = $this->calc_course_users_va($courseID, $comparison, 'SUM');
                $retval .= $this->get_single_database_report_table($obj);
                break;
            case 'c6':
                //Count Qualification Families On Course
                $obj = $this->count_course_qual_families($courseID);
                $retval .= $this->get_count_database_report_table($obj);
                break;
            case 'c7':
                //Count Diferent Qualification Combinations on Course
                $obj = $this->count_course_qual_target_quals($courseID);
                $retval .= $this->get_count_database_report_table($obj);
                break;
            case 'c8':
                //Count Different Qualifications on Course
                $obj = $this->count_course_quals($courseID);
                $retval .= $this->get_count_database_report_table($obj);
                break;
            case 'c9':
                //Count Users on Course
                $obj = $this->count_course_users($courseID);
                $retval .= $this->get_count_database_report_table($obj);
                break;
            case 'c10':
                //Count Users with Average GCSE Score
                $obj = $this->count_course_users_with_avg_score($courseID);
                $retval .= $this->get_count_database_report_table($obj);
                break;
            case 'c11':
                //Count Users With Qual Award (Predicted/Set)
                $obj = $this->count_course_users_with_qual_award($courseID);
                $retval .= $this->get_single_database_report_table($obj);
                break;
            case 'c12':
                //Breakdown of Users Target Status (Ahead/On/Behind)
                $obj = $this->get_course_users_va($courseID, $comparison);
                $retval .= $this->get_single_database_report_table($obj);
                break;
            case 'c13':
                //Average Qualification Award
                $obj = $this->get_course_users_predicted_grades($courseID);
                $retval .= $this->get_single_database_report_table($obj);
                break;
            case 'c14':
                //Show Users Average GCSE Scores
                return "";
                break;
            case 'c15':
                //Average Target Award
                $obj = $this->get_course_users_avg_target_grades($courseID, $comparison);
                $retval .= $this->get_multiple_database_report_table($obj);
                break;
            case 'c16':
                //Show Users Predicted Grades
                return "";
                break;
            case 'c17':
                //Show Users Target Grades
                return "";
                break;
            case 'c18':
                //Show Users VA
                return "";
                break;
            case 'c19':
                //Overall Users VA (Combines MAX, MIN, AVG, SUM)
                return "";
                break;
            case 'q1':
                //Average 'Average Gcse Score'
                $obj = $this->average_qual_users_avg_score($qualID);
                $retval .= $this->get_single_database_report_table($obj);
                break;
            case 'q2':
                //Max Users VA
                $obj = $this->calc_qual_users_va($qualID, $comparison, 'MAX');
                $retval .= $this->get_single_database_report_table($obj);
                break;
            case 'q3':
                //Min Users VA
                $obj = $this->calc_qual_users_va($qualID, $comparison, 'MIN');
                $retval .= $this->get_single_database_report_table($obj);
                break;
            case 'q4':
                //Average Users VA
                $obj = $this->calc_qual_users_va($qualID, $comparison, 'AVG');
                $retval .= $this->get_single_database_report_table($obj);
                break;
            case 'q5':
                //Combined/Total Users VA
                $obj = $this->calc_qual_users_va($qualID, $comparison, 'SUM');
                $retval .= $this->get_single_database_report_table($obj);
                break;
            case 'q5':
                //Breakdown of Users Target Status (Ahead/On/Behind)
                $obj = $this->get_qual_users_va($qualID, $comparison);
                $retval .= $this->get_single_database_report_table($obj);
                break;
            case 'g1':
                //Average GCSE Score by Course
                return "";
                break;
            case 'g2':
                //Average GCSE Score by Qual
                return "";
                break;
            case 'g3':
                //VA By Course
                return "";
                break;
            case 'g4':
                //VA By Qual
                return "";
                break;
            case 'g5':
                //Average Target Grade By Course
                return "";
                break;
            case 'g6':
                //Average Predicted Grade By Course
                return "";
                break;
            case 'g7':
                //Average Target Grade By Qual
                return "";
                break;
            case 'g8':
                //Average Predicted Grade By Qual
                return "";
                break;
            case 'g9':
                //All By Course
                return "";
                break;
            case 'g9':
                //All By Qual
                return "";
                break;
        }
        
        $retval .= '</div>';
        return $retval;
    }
    
    public function get_count_database_report_table($obj)
    {
        $retval = '<table>';
        $retval .= '<tr><th>'.get_string('count', 'block_bcgt').'</th></tr>';
        $retval .= '<tr><td>'.$obj.'</td></tr>';
        $retval .= '</table>';
        return $retval;
    }
    
    public function get_single_database_report_table($obj)
    {
        $retval = '';
        $retval .= '<table>';
        if($obj)
        {
            $row = '';
            $header = '';
            foreach($obj AS $head=>$record)
            {
                $header .= '<th>'.$head.'</th>';
                $row .= '<td>'.$record.'</td>';
            }
            $retval .= '<tr>'.$header.'</tr>';
            $retval .= '<tr>'.$row.'</tr>';
        }
        $retval .= '</table>';
        return $retval;
    }
    
    public function get_multiple_database_report_table($obj)
    {
        $retval = '';
        $retval .= '<table>';
        if($obj)
        {
            $count = 1;
            foreach($obj AS $record)
            {
                $row = '';
                $header = '';
                foreach($record AS $head=>$cell)
                {
                    if($count == 1)
                    {
                        $header .= '<th>'.$head.'</th>';
                    }
                    $row .= '<td>'.$cell.'</td>';
                }
                if($count == 1)
                {
                    $retval .= '<tr>'.$header.'</tr>';
                }
                $retval .= '<tr>'.$row.'</tr>';
                $count++;
            }
        }
        $retval .= '</table>';
        return $retval;
    }
        
    public function get_reports_drop_down()
    {
        $reportID = optional_param('report', '', PARAM_TEXT);
        $retval = '<select name="report">';
        $retval .= '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>';
        $users = $this->get_user_report_names();
        if($users)
        {
            $count = 0;
            $retval .= '<optgroup label="By User">';
            foreach($users AS $user)
            {
                $count++;
                $selected = '';
                if($reportID == 'u'.$count)
                {
                    $selected = 'selected ';
                }
                $retval .= '<option '.$selected.' value="u'.$count.'">'.$user.'</option>';
            }
            $retval .= '</optgroup>';
        }
        $courses = $this->get_course_report_names();
        if($courses)
        {
            $count = 0;
            $retval .= '<optgroup label="By Course">';
            foreach($courses AS $course)
            {
                $count++;
                $selected = '';
                if($reportID == 'c'.$count)
                {
                    $selected = 'selected ';
                }
                $retval .= '<option '.$selected.' value="c'.$count.'">'.$course.'</option>';
            }
            $retval .= '</optgroup>';
        }
        $quals = $this->get_qual_report_names();
        if($quals)
        {
            $count = 0;
            $retval .= '<optgroup label="By Qual">';
            foreach($quals AS $qual)
            {
                $count++;
                $selected = '';
                if($reportID == 'q'.$count)
                {
                    $selected = 'selected ';
                }
                $retval .= '<option '.$selected.' value="q'.$count.'">'.$qual.'</option>';
            }
            $retval .= '</optgroup>';
        }
        $generals = $this->get_general_report_names();
        if($generals)
        {
            $count = 0;
            $retval .= '<optgroup label="General">';
            foreach($generals AS $general)
            {
                $count++;
                $selected = '';
                if($reportID == 'g'.$count)
                {
                    $selected = 'selected ';
                }
                $retval .= '<option '.$selected.' value="g'.$count.'">'.$general.'</option>';
            }
            $retval .= '</optgroup>';
        }
        $retval .= '</select>';
        return $retval;
    }
    
    public function get_qual_report_names()
    {
        return array("Average 'Average Gcse Score'", "Max Users VA", "Min Users VA", 
            "Average Users VA", "Combined/Total Users VA", "Breakdown of Users Target Status (Ahead/On/Behind)");
    }
    
    public function get_general_report_names()
    {
        return array("Average GCSE Score by Course", "Average GCSE Score by Qual", "VA By Course", "VA By Qual", "Average Target Grade By Course", 
            "Average Predicted Grade By Course","Average Target Grade By Qual", "Average Predicted Grade By Qual", "All By Course", "All By Qual");
    }
    
    public function get_course_report_names()
    {
        return array("Average 'Average Gcse Score'", "Max Users VA", "Min Users VA", 
            "Average Users VA", "Combined/Total Users VA", "Count Qualification Families On Course", 
            "Count Diferent Qualification Combinations on Course", "Count Different Qualifications on Course", 
            "Count Users on Course", "Count Users with Average GCSE Score", "Count Users With Qual Award (Predicted/Set)", 
            "Breakdown of Users Target Status (Ahead/On/Behind)", "Average Qualification Award", 
            "Show Users Average GCSE Scores", "Average Target Award", "Show Users Predicted Grades", "Show Users Target Grades",
            "Show Users VA", "Overall Users VA (Combines MAX, MIN, AVG, SUM)");
    }
    
    public function get_user_report_names()
    {
        return array("Users Target Grades", "Users Average GCSE Score", "Users Average VA", "Users Extreme VA", "Users Combined VA", "Users Current Status");
    }
        
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
    public function get_users_target_grades($userID, $qualID = -1, $courseID = -1)    
    {        
        global $DB;
        $sql = "SELECT usertrgts.id as id, breakdown.id as breakdownid, breakdown.targetgrade as targetgrade, 
            breakdown.ucaspoints as bucaspoints, breakdown.unitsscorelower as bunitsscorelower, 
            breakdown.unitsscoreupper AS bunitsscoreupper, breakdown.ranking AS branking, 
            breakdown.entryscoreupper AS bentryscoreupper, breakdown.entryscorelower as bentryscorelower, 
            grades.id AS targetgradesid, grades.grade as grade, grades.ucaspoints as gucaspoints, grades.upperscore
            AS gupperscore, grades.lowerscore as glowerscore, grades.ranking as granking, weightedbreakdown.id as wbreakdownid, 
            weightedbreakdown.targetgrade as wtargetgrade, 
            weightedbreakdown.ucaspoints as wucaspoints, weightedbreakdown.unitsscorelower as wunitsscorelower, 
            weightedbreakdown.unitsscoreupper AS wunitsscoreupper, weightedbreakdown.ranking AS wranking, 
            weightedbreakdown.entryscoreupper AS wentryscoreupper, weightedbreakdown.entryscorelower as wentryscorelower, 
            weightedgrades.id AS wtargetgradesid, weightedgrades.grade as wgrade, weightedgrades.ucaspoints as wucaspoints, weightedgrades.upperscore 
            AS wupperscore, weightedgrades.lowerscore as wlowerscore, weightedgrades.ranking as wranking, grades.bcgttargetqualid AS bcgttargetqualid
            , usertrgts.bcgtqualificationid as bcgtqualificationid , q.name as qualname, usertrgts.id as usercoursetargetsid 
            FROM {block_bcgt_user_course_trgts} usertrgts 
            LEFT OUTER JOIN {block_bcgt_target_breakdown} breakdown on breakdown.id = bcgttargetbreakdownid 
            LEFT OUTER JOIN {block_bcgt_target_grades} grades ON grades.id = bcgttargetgradesid 
            LEFT OUTER JOIN {block_bcgt_target_breakdown} weightedbreakdown ON weightedbreakdown.id = bcgtweightedbreakdownid 
            LEFT OUTER JOIN {block_bcgt_target_grades} weightedgrades ON weightedgrades.id = bcgtweightedgradeid
            INNER JOIN {block_bcgt_qualification} q ON q.id = usertrgts.bcgtqualificationid";
            if($courseID != -1)
            {
                $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = usertrgts.bcgtqualificationid AND coursequal.courseid = usertrgts.courseid";
            }   
            $sql .= " WHERE userid = ? AND (usertrgts.bcgttargetbreakdownid > 0 OR usertrgts.bcgttargetgradesid > 0) ";
        $params = array($userID);
        if($qualID != -1)
        {
            $sql .= ' AND usertrgts.bcgtqualificationid = ?';
            $params[] = $qualID;
        }
        if($courseID != -1)
        {
            $sql .= ' AND (usertrgts.courseid = ? OR coursequal.courseid = ?)';
            $params[] = $courseID;
            $params[] = $courseID;
        }
                        
        $records = $DB->get_records_sql($sql, $params);
        
        $qualArray = array();
        
        if($records)
        {
            
            foreach($records AS $record)
            {
                $stdObj = new stdClass();
                
                $params = new stdClass();
                $params->targetgrade = $record->targetgrade;
                $params->ucaspoints = $record->bucaspoints;
                if(isset($record->bunitscorelower))
                {
                    $params->unitscorelower = $record->bunitscorelower;
                }
                if(isset($record->bunitscoreupper))
                {
                    $params->unitscoreupper = $record->bunitscoreupper;
                }
                $params->ranking = $record->branking;
                $params->bcgttargetqualid = $record->bcgttargetqualid;
                $stdObj->breakdown = new Breakdown($record->breakdownid, $params);
                
                $params = new stdClass();
                $params->targetgrade = $record->wtargetgrade;
                $params->ucaspoints = $record->wucaspoints;
                if(isset($record->bunitscorelower))
                {
                    $params->unitscorelower = $record->wunitscorelower;
                }
                if(isset($record->bunitscoreupper))
                {
                    $params->unitscoreupper = $record->wunitscoreupper;
                }
                $params->ranking = $record->branking;
                $params->bcgttargetqualid = $record->bcgttargetqualid;
                $stdObj->weightedbreakdown = new Breakdown($record->wbreakdownid, $params);
                
                $params = new stdClass();
                $params->grade = $record->grade;
                $params->ucaspoints = $record->gucaspoints;
                $params->upperscore = $record->gupperscore;
                $params->lowerscore = $record->glowerscore;
                $params->ranking = $record->granking;
                $params->bcgttargetqualid = $record->bcgttargetqualid;
                $stdObj->targetgrade = new TargetGrade($record->targetgradesid, $params);
                $stdObj->grade = $stdObj->targetgrade->get_grade();
                
                $params = new stdClass();
                $params->grade = $record->wgrade;
                $params->ucaspoints = $record->wucaspoints;
                $params->upperscore = $record->wupperscore;
                $params->lowerscore = $record->wlowerscore;
                $params->ranking = $record->wranking;
                $params->bcgttargetqualid = $record->bcgttargetqualid;
                $stdObj->weightedtargetgrade = new TargetGrade($record->targetgradesid, $params);
                $stdObj->usercoursetargetsid = $record->id;
                
                $stdObj->qualid = $record->bcgtqualificationid;
                $stdObj->qualname = $record->qualname;
                
                $qualArray[$record->bcgtqualificationid] = $stdObj;
            }
        }
        
        // Check elsewhere for target grades for quals who don't support auto calculations
        $qualID = ($qualID > 0) ? $qualID : false;
        $courseID = ($courseID > 0) ? $courseID : false;
        
        $grades = bcgt_get_target_grade($userID, $qualID, $courseID);
        if ($grades)
        {
            foreach($grades as $grade)
            {
                if ($qualID){
                    $qualArray[$qualID] = $grade;
                } else {
                    $qualArray[] = $grade;
                }
                
            }
        }

        
        return ($qualArray) ? $qualArray : false;
        
    }
    
    //BY USER:
    
    //Get users average gcse score
    /**
     * Tries to find the users average gcse score
     * if retRecord = true then it returns the database record
     * else it returns the average gcse score, or NoPl if the user
     * has no Prior Learning and NoGcse if the user has prior learning
     * but no GCSE
     * @global type $DB
     * @param type $userID
     * @param type $retRecord
     * @return boolean
     */
    public function get_users_average_gcse_score($userID, $retRecord = false)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_prior} WHERE userid =?";
        $record = $DB->get_record_sql($sql, array($userID));
        if($record && $retRecord)
        {
            return $record;
        }
        else
        {
            $averageGCSEScore = 0;
            if($record)
            {
                $averageGCSEScore = $record->averagegcsescore;
                return $averageGCSEScore;
            }
            if(!$averageGCSEScore || $averageGCSEScore == 0)
            {
                //then lets check if they have any prior learning, but no gcse etc
                $sql = "SELECT * FROM {block_bcgt_user_prlearn} WHERE userid = ?";
                $records = $DB->get_records_sql($sql, array($userID));
                if($records)
                {
                    //then we have prior learning but we dont have any gcse's
                    return get_string('reportnogcse', 'block_bcgt');
                }
                else
                {
                    return get_string('reportnopl', 'block_bcgt');
                }
            }
            
        }
        return false;
    }

    //Get users behind/ahead (by course/by qual) and Get users VA (by course/qual)
    /**
     * $comparison = 
     *    "full" = calculated normal
     *    "weight" = calculated aspirational
     *    "teach" = calculated normal + one grade (unless changed by user)
     * If comparison is null then it assumes full
     * This finds all of the quals the user is on that they have a user award for
     * This then finds the target grades and the awards for the quals
     * This then returns the diference between them 
     * //So if its negative they are behind
     * //if its 0 they are on target
     * //If its positive they are ahead
     * The number is how many grades they are ahead/behind. 
     * @global type $DB
     * @param type $userID
     * @param type $qualID
     * @param type $courseID
     * @return null|boolean
     */
    public function get_users_target_current_status($userID, $qualID = -1, $courseID = -1, $comparison = null)
    {
        //so this needs to get their targets and their predicted grades
        //user course targets
        //user award
        global $DB;
        $sql = "SELECT useraward.id, qual.id AS bcgtqualificationid, qual.name, 
            breakdown.id as awardid, breakdown.ranking AS awardranking, 
            breakdown.targetgrade as awardgrade, grades.id as gradeid, grades.ranking as graderanking, 
            grades.grade as grade, tbreakdown.id as targetbreakdownid, tbreakdown.ranking as targetbreakdownranking, 
            tbreakdown.targetgrade as breakdowntargetgrade, tgrades.id AS targetgradeid, tgrades.ranking as targetgradesranking, 
            tgrades.grade as targetgrade FROM {block_bcgt_user_award} useraward 
            LEFT OUTER JOIN {block_bcgt_target_breakdown} breakdown ON useraward.bcgtbreakdownid = breakdown.id 
            LEFT OUTER JOIN {block_bcgt_target_grades} grades ON grades.id = useraward.bcgttargetgradesid
            JOIN {block_bcgt_user_course_trgts} utrgts ON utrgts.bcgtqualificationid = useraward.bcgtqualificationid 
            AND utrgts.userid = useraward.userid ";
        if($comparison && $comparison == "weight")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.bcgtweightedbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.bcgtweightedgradeid";
        }
        elseif($comparison && $comparison == "teach")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.teacherset_breakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.teacherset_targetid"; 
        }
        else
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.bcgttargetbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.bcgttargetgradesid";
        }
        $sql .= " JOIN {block_bcgt_qualification} qual ON qual.id = useraward.bcgtqualificationid";
            
        if($courseID != -1)
        {
            $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id";
        }   
        $sql .= " WHERE useraward.userid = ? AND (useraward.type = ? OR useraward.type = ? OR useraward.type = ? OR useraward.type = ?)";
        $params = array($userID, 'CETA', 'AVG', 'FINAL', 'Predicted');
        if($qualID != -1)
        {
            $sql .= " AND qual.id = ?";
            $params[] = $qualID;
        }
        if($courseID != -1)
        {
            $sql .= " AND (utrgts.courseid = ? OR coursequal.courseid = ?)";
            $params[] = $courseID;
            $params[] = $courseID;
        }
        $records = $DB->get_records_sql($sql, $params);
        if($records)
        {
            $qualsArray = array();
            foreach($records AS $record)
            {
                $va = null;
                //check the rankings
                if($record->targetgradesranking && $record->graderanking)
                {
                    //then we have the ranking of the predicted award and the ranking
                    //of the targetgrade
                    $va = $record->graderanking - $record->targetgradesranking;
                }
                elseif($record->targetbreakdownranking && $record->awardranking)
                {
                    //then we have the ranking of the predicted breakdown award and
                    //the ranking of the targetbreakdown
                    $va = $record->awardranking - $record->targetbreakdownranking;
                }
                else
                {
                    //we are missing something
                    $va = null;
                }
                if(count($records) == 1)
                {
                    return $va;
                }
                //do the targetgrades first
                //then do the breakdowns
                //also do the value added. 
                $qualsArray[$record->bcgtqualificationid] = $va;
            }
            return $qualsArray;
        }
        return false;
    }
     
    //Get users behnd/ahead for all e.g. Overall Ahead
    //Get users combined VA for all. e.g. if they are -1, -2, +2 would be a combined -1.
    /**
     * $comparison = 
     *    "full" = calculated normal
     *    "weight" = calculated aspirational
     *    "teach" = calculated normal + one grade (unless changed by user)
     * If comparison is null then it assumes full
     * Gets all of the users target_current status values
     * //eg. all of their value added for all of theur courses and quals
     * //loops over them all and adds them all up
     * //thus if they have a posiive then overall they are ahead
     * //if they are negative then overall they are behind
     * //if they are 0 overall they are ontarget
     * the number is how many grades diference. 
     * @param type $userID
     * @return boolean
     */
    public function get_users_overall_target_current_status($userID, $qualID = -1, $courseID = -1, $comparison = null)
    {
        $stati = $this->get_users_target_current_status($userID, $qualID, $courseID, $comparison);
        if($stati)
        {
            if(count($stati) == 1)
            {
                return $stati;
            }
            elseif(count($stati) > 1)
            {
                $overAllStatus = 0;
                foreach($stati AS $status)
                {
                    $overAllStatus = $overAllStatus + $status;
                }
                return $overAllStatus;
            }
            else 
            {
                return false;
            }
        }
        return false;
    }
    
    //Get users average VA for all, e.g. -1, -1, -3, +4 would be -1/4
    /**
     *  $comparison = 
     *    "full" = calculated normal
     *    "weight" = calculated aspirational
     *    "teach" = calculated normal + one grade (unless changed by user)
     * If comparison is null then it assumes full
     * Gets all of the users target_current status values
     * //eg. all of their value added for all of theur courses and quals
     * //loops over them all and adds them all up then divides by the number
     * it finds
     * //thus if they have a posiive then average they are ahead
     * //if they are negative then average they are behind
     * //if they are 0 average they are ontarget
     * the number is how many grades diference. 
     * @param type $userID
     * @return boolean
     */
    public function get_users_average_target_current_status($userID, $qualID = -1, $courseID = -1, $comparison = null)
    {
        $stati = $this->get_users_target_current_status($userID, $qualID, $courseID, $comparison);
        if($stati)
        {
            if(count($stati) == 1)
            {
                return $stati;
            }
            elseif(count($stati) > 1)
            {
                $overAllStatus = 0;
                foreach($stati AS $status)
                {
                    $overAllStatus = $overAllStatus + $status;
                }
                return $overAllStatus/(count($stati));
            }
            else 
            {
                return false;
            }
        }
        return false;
    }
    
    //get the users highest positive and highest negative
    /**
     * $comparison = 
     *    "full" = calculated normal
     *    "weight" = calculated aspirational
     *    "teach" = calculated normal + one grade (unless changed by user)
     * If comparison is null then it assumes full
     * Gets all of the users target_current status values
     * //eg. all of their value added for all of theur courses and quals
     * //loops over them all and finds the highest positive and lowest negative
     * it returns this as a string seperated by a slash: highest/lowest
     * @param type $userID
     * @return boolean
     */
    public function get_users_extreme_target_current_status($userID, $qualID = -1, $courseID = -1, $comparison = null)
    {
        $stati = $this->get_users_target_current_status($userID, $qualID, $courseID, $comparison);
        if($stati)
        {
            if(count($stati) == 1)
            {
                return $stati;
            }
            elseif(count($stati) > 1)
            {
                $highestPositive = null;
                $lowestNegative = null;
                foreach($stati AS $status)
                {
                    if($status > 0)
                    {
                        if(($highestPositive && $highestPositive < $status) || !$highestPositive)
                        {
                            $highestPositive = $status;
                        }
                    }
                    elseif($status < 0)
                    {
                        if(($lowestNegative && $lowestNegative > $status) || !$lowestNegative)
                        {
                            $lowestNegative = $status;
                        }
                    }
                }
                return $highestPositive."/".$lowestNegative;
            }
            else 
            {
                return false;
            }
        }
        return false;
    }
    
    //Users Predicted Grade (AVG)
    /**
     * 
     * This finds all of the users awards set for the qualification
     * if qual or course are set then it uses those to reduce the search
     * it returns the targetgrade if set or then the breakdown grade if set
     * if the count is one then it returns the grade else it returns an array of the
     * string grades.  
     * @global type $DB
     * @param type $userID
     * @param type $qualID
     * @param type $courseID
     * @return null
     */
    public function get_users_qual_awards($userID, $qualID = -1, $courseID = -1)
    {
        global $DB;
        $sql = "SELECT award.id, breakdown.id as breakdownid, breakdown.ranking as 
            breakdownranking, breakdown.targetgrade as targetgrade, grades.id as gradeid, 
            grade.ranking as graderanking, grade.grade as grade, qual.id as bcgtqualificationid FROM {block_bcgt_user_award} award 
            LEFT OUTER JOIN {block_bcgt_target_breakdown} breakdown ON breakdown.id = award.bcgttargetbreakdownid 
            LETT OUTER JOIN {block_bcgt_target_grades} grades ON grades.id = award.bcgttargetgradesid 
            JOIN {block_bcgt_qualificationd} qual ON qual.id = award.bcgtqualificationid";
        if($courseID != -1)
        {
            $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id";
        }
        $params = array($userID, 'CETA', 'AVG', 'FINAL', 'Predicted');
        $sql .= " WHERE award.userid = ? AND (award.type = ? OR award.type = ? OR award.type = ? OR award.type = ?)";
        if($qualID != -1)
        {
            $sql .= " AND qual.id = ?";
            $params[] = $qualID;
        }
        if($courseID != -1)
        {
            $sql .= ' AND (award.courseid = ? OR coursequal.courseid = ?)';
            $params[] = $courseID;
            $params[] = $courseID;
        }
        $records = $DB->get_records_sql($sql, $params);
        if($records)
        {
            $awards = array();
            foreach($records AS $record)
            {
                $award = null;
                if($record->grade)
                {
                    $award = $record->grade;
                }
                elseif($record->targetgrade)
                {
                    $award = $record->targetgrade;
                }
                if(count($record) == 1)
                {
                    return $award;
                }
                else
                {
                    $awards[$record->bcgtqualificationid] = $award;
                }
            }
            return $awards;
        }
        return null;
    }

    //BY COURSE/Qual:
    /**
     * //counts students on a course
     * @global type $DB
     * @param type $courseID
     * @return type
     */
    private function count_course_users($courseID)
    {
        global $DB;
        $sql = "SELECT count(distinct(user.id)) AS count FROM {user} user 
            JOIN {role_assignments} roleass ON roleass.userid = user.id 
            JOIN {context} context ON context.id = roleass.contextid 
            JOIN {role} role ON role.id = roleass.roleid
            JOIN {course} course ON course.id = context.instanceid
            WHERE course.id = ? AND context.contextlevel = ? AND role.shortname = ?";
        return $DB->count_records_sql($sql, array($courseID, 50, 'student'));
    }
    
    
    /**
     * //Counts student with an average score on a course
     * @global type $DB
     * @param type $courseID
     * @return type
     */
    public function count_course_users_with_avg_score($courseID)
    {
        //get enrolled users
        global $DB;
        $sql = "SELECT count(distinct(user.id)) AS count FROM {user} user 
            JOIN {role_assignments} roleass ON roleass.userid = user.id 
            JOIN {context} context ON context.id = roleass.contextid 
            JOIN {role} role ON role.id = roleass.roleid
            JOIN {block_bcgt_user_prior} prior ON prior.userid = user.id
            JOIN {course} course ON course.id = context.instanceid
            WHERE course.id = ? AND context.contextlevel = ? AND role.shortname = ?";
        return $DB->count_records_sql($sql, array($courseID, 50, 'student'));
    }
    
    /**
     * Gets the average gcse score for students enrolled on a course. 
     * @global type $DB
     * @param type $courseID
     * @return type
     */
    public function average_course_users_avg_score($courseID)
    {
        //get enrolled users
        global $DB;
        $sql = "SELECT AVG(prior.averagegcsescore) AS average FROM {user} user 
            JOIN {role_assignments} roleass ON roleass.userid = user.id 
            JOIN {context} context ON context.id = roleass.contextid 
            JOIN {role} role ON role.id = roleass.roleid
            JOIN {block_bcgt_user_prior} prior ON prior.userid = user.id
            JOIN {course} course ON course.id = context.instanceid
            WHERE course.id = ? AND context.contextlevel = ? AND role.shortname = ?";
        return $DB->get_record_sql($sql, array($courseID, 50, 'student'));
    }
    
    //GET No with/without avg score and percentage
    //Averegae AVG score
    /**
     * Returns an object that conists of:
     * totalstudents
     * withavg
     * percentage
     * avgscore
     * @param type $courseID
     * @return \stdClass
     */
    public function get_course_users_avg_score($courseID)
    {
        $usersOnCourse = $this->count_course_users($courseID);
        $usersWithAvg = $this->count_course_users_with_avg_score($courseID);
        
        $retval = new stdClass();
        $retval->totalstudents = $usersOnCourse;
        $retval->withavg = $usersWithAvg;
        $retval->percentage = ($usersWithAvg/$usersOnCourse) * 100;
        $average = $this->average_course_users_avg_score($courseID);
        if($average)
        {
            $retval->avgscore = $average->average;
        }
        else
        {
            $retval->avgscore = null;
        }
        return $retval;
    }
    
    //GET No with/without avg score and percentage
    //Averegae AVG score
    /**
     * Returns an object that conists of:
     * totalstudents
     * withavg
     * percentage
     * avgscore
     * @param type $qualID
     * @return \stdClass
     */
    public function get_qual_users_avg_score($qualID, $courseID = -1)
    {
        $usersOnQual = $this->count_qual_students($qualID, $courseID);
        $usersWithAvg = $this->count_qual_users_avg_score($qualID, $courseID);
        
        $retval = new stdClass();
        $retval->totalstudents = $usersOnQual;
        $retval->withavg = $usersWithAvg;
        $retval->percentage = ($usersWithAvg/$usersOnQual) * 100;
        $average = $this->average_qual_users_avg_score($qualID, $courseID);
        if($average)
        {
            $retval->avgscore = $average->average;
        }
        else
        {
            $retval->avgscore = null;
        }
        return $retval;
    }
    
    /**
     * Gets the average of the average gcse score for all users on a qual
     * if the course is set it add this into the join
     * @global type $DB
     * @param type $qualID
     * @param type $courseID
     * @return type
     */
    public function average_qual_users_avg_score($qualID, $courseID = -1)
    {
        //get users in user_qual
        global $DB;
        $sql = "SELECT AVG(userprior.averagegcsescore) FROM {block_bcgt_user_prior} userprior 
            JOIN {block_bcgt_user_qual} userquals ON userquals.userid = userprior.userid 
            JOIN {role} role ON role.id = userquals.roleid ";
        if($courseID != -1)
        {
            $sql .= ' JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = userquals.bcgtqualificationid';
        }
        $params = array($qualID, 'student');
        $sql .= ' WHERE userquals.bcgtqualificationid = ? AND role.shortname = ?';
        if($courseID != -1)
        {
            $sql .= ' AND coursequal.courseid = ?';
            $params[] = $courseID; 
        }
        return $DB->get_record_sql($sql, $params);
    }
    
    /**
     * Counts the users on the qual
     * if courseID is not set to -1 then it counts the qual course combination
     * @global type $DB
     * @param type $qualID
     * @param type $courseID
     * @return type
     */
    public function count_qual_students($qualID, $courseID = -1)
    {
        global $DB;
        $sql = "SELECT count(distinct(user.id)) FROM {user} user 
            JOIN {block_bcgt_user_quals} userquals ON userquals.userid = user.id 
            JOIN {role} role ON role.id = userquals.roleid ";
        if($courseID != -1)
        {
            $sql .= ' JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = userquals.bcgtqualificationid';
        }
        $sql .= ' WHERE userquals.bcgtqualificationid = ? and role.shortname';
        $params = array($qualID, 'student');
        if($courseID != -1)
        {
            $sql .= ' AND coursequal.courseid = ?';
            $params[] = $courseID;
        }
        return $DB->count_records_sql($sql, $params);
    }
    
    /**
     * Counts the qual users that have an average gcse score
     * if courseid is not -1 then it adds this combination
     * @global type $DB
     * @param type $qualID
     * @param type $courseID
     * @return type
     */
    public function count_qual_users_avg_score($qualID, $courseID = -1)
    {
        global $DB;
        $sql = "SELECT count(distinct(user.id)) FROM {user} user 
            JOIN {block_bcgt_user_quals} userquals ON userquals.userid = user.id
            JOIN {block_bcgt_user_prior} userprior ON userprior.userid = user.id
            JOIN {role} role ON role.id = userquals.roleid ";
        if($courseID != -1)
        {
            $sql .= ' JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = userquals.bcgtqualificationid';
        }
        $sql .= ' WHERE userquals.bcgtqualificationid = ? role.shortname = ? AND userprior.averagegcsescore IS NOT NULL AND userprior.averagegcsescore != ? ';
        $params = array($qualID, 'student', 0);
        if($courseID != -1)
        {
            $sql .= ' AND coursequal.courseid = ?';
            $params[] = $courseID;
        }
        return $DB->count_records_sql($sql, $params);
    }
    
    /**
     * This will get an object that consists of
     * breakdown -> numberAhead / numberON/ numberBehind
     * breakdownpercentage -> %Ahead / %ON/ %Behind
     * $comparison = 
     *    "full" = calculated normal
     *    "weight" = calculated aspirational
     *    "teach" = calculated normal + one grade (unless changed by user)
     * @param type $courseID
     * @param type $comparison
     * @return \stdClass
     */
    public function get_breakdown_course_users_status($courseID, $comparison)
    {
        //will get the percentage of ahead
        //will get the percentage if behind
        //will get the no ahead
        //will get the no behind
        
        //so needs to get all of those where the ranking of predicted 
        //is greater than the ranking of target
        //so needs to get all of those where the ranking of target 
        //is greater than the ranking of predicted
        $ahead = count($this->get_course_users_by_target_current_status($courseID, $comparison, 'AHEAD'));
        $behind = count($this->get_course_users_by_target_current_status($courseID, $comparison, 'BEHIND'));
        $on = count($this->get_course_users_by_target_current_status($courseID, $comparison, 'ON'));
        
        $total = $ahead + $behind + $on;
        
        $retval = new stdClass();
        $retval->breakdown = $ahead.'/'.$on.'/'.$behind;
        $retval->breakdownpercentage = (($ahead/$total) * 100).'/'.(($on/$total) * 100).'/'.(($behind/$total) * 100);
        return $retval;
    }
    
    /**
     * Gets the breakdowns and awards for users on the course
     * where they are AHEAD/BEHIND/ON
     * $comparison = 
     *    "full" = calculated normal
     *    "weight" = calculated aspirational
     *    "teach" = calculated normal + one grade (unless changed by user)
     * @global type $DB
     * @param type $courseID
     * @param type $comparison
     * @param type $aheadBehindOn
     * @return type
     */
    public function get_course_users_by_target_current_status($courseID, $comparison, $aheadBehindOn)
    {
        global $DB;
        $sql = "SELECT useraward.id, user.id, qual.id AS bcgtqualificationid, qual.name, 
            breakdown.id as awardid, breakdown.ranking AS awardranking, 
            breakdown.targetgrade as awardgrade, grades.id as gradeid, grades.ranking as graderanking, 
            grades.targetgrade as grade, tbreakdown.id as targetbreakdownid, tbreakdown.ranking as targetbreakdownranking, 
            tbreakdown.targetgrade as breakdowntargetgrade, tgrades.id AS targetgradeid, tgrades.ranking as targetgradesranking, 
            tgrades.grade as targetgrade FROM {block_bcgt_user_award} useraward 
            LEFT OUTER JOIN {block_bcgt_target_breakdown} breakdown ON useraward.bcgttargetbreakdownid = breakdown.id 
            LEFT OUTER JOIN {block_bcgt_target_grades} grades ON grades.id = useraward.bcgttargetgradesid
            JOIN {block_bcgt_user_course_trgts} utrgts ON utrgts.bcgtqualificationid = useraward.bcgtqualificationid
            AND utrgts.userid = useraward.userid
            JOIN {user} user ON user.id = useraward.userid
            JOIN {role_assignments} roleass ON roleass.userid = user.id 
            JOIN {context} context ON context.id = roleass.contextid 
            JOIN {role} role ON role.id = roleass.roleid
            JOIN {block_bcgt_user_prior} prior ON prior.userid = user.id
            JOIN {course} course ON course.id = context.instanceid ";
        if($comparison && $comparison == "weight")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.bcgtweightedbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.bcgtweightedgradeid";
        }
        elseif($comparison && $comparison == "teach")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.teacherset_breakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.teacherset_targetid"; 
        }
        else
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.bcgttargetbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.bcgttargetgradesid";
        }
        $sql .= " JOIN {block_bcgt_qualification} qual ON qual.id = useraward.bcgtqualificationid";
        $sql .= " WHERE course.id = ? AND (useraward.type = ? OR useraward.type = ? OR useraward.type = ? OR useraward.type = ?)";
        $params = array($courseID, 'CETA', 'AVG', 'FINAL', 'Predicted');
        $operand = ' = ';
        if($aheadBehindOn == 'ON')
        {
            $operand = ' = ';
        }
        elseif($aheadBehindOn == 'AHEAD')
        {
            $operand = ' > ';
        }
        elseif($aheadBehindOn == 'BEHIND')
        {
            $operand = ' < ';
        }
        $sql .= ' AND (awardranking '.$operand.' targetbreakdownranking OR graderanking '.$operand.' targetgradesranking)';
        
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Gets the breakdowns and awards for users on the qual
     * where they are AHEAD/BEHIND/ON
     * $comparison = 
     *    "full" = calculated normal
     *    "weight" = calculated aspirational
     *    "teach" = calculated normal + one grade (unless changed by user)
     * @global type $DB
     * @param type $qualID
     * @param type $comparison
     * @param type $aheadBehindOn
     * @param type $courseID
     * @return type
     */
    public function get_qual_users_by_target_current_status($qualID, $comparison, $aheadBehindOn, $courseID = -1)
    {
        global $DB;
        $sql = "SELECT useraward.id, user.*, qual.id AS bcgtqualificationid, qual.name, 
            breakdown.id as awardid, breakdown.ranking AS awardranking, 
            breakdown.targetgrade as awardgrade, grades.id as gradeid, grades.ranking as graderanking, 
            grades.targetgrade as grade, tbreakdown.id as targetbreakdownid, tbreakdown.ranking as targetbreakdownranking, 
            tbreakdown.targetgrade as breakdowntargetgrade, tgrades.id AS targetgradeid, tgrades.ranking as targetgradesranking, 
            tgrades.grade as targetgrade FROM {block_bcgt_user_award} useraward 
            LEFT OUTER JOIN {block_bcgt_target_breakdown} breakdown ON useraward.bcgttargetbreakdownid = breakdown.id 
            LEFT OUTER JOIN {block_bcgt_target_grades} grades ON grades.id = useraward.bcgttargetgradesid
            JOIN {block_bcgt_user_course_trgts} utrgts ON utrgts.bcgtqualificationid = useraward.bcgtqualificationid
            AND utrgts.userid = useraward.userid
            JOIN {user} user ON user.id = useraward.userid
            JOIN {block_bcgt_user_quals} userquals ON userquals.userid = useraward.userid";
        if($courseID != -1)
        {
            $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = userquals.bcgtqualificationid";
        }
        if($comparison && $comparison == "weight")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.bcgtweightedbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.bcgtweightedgradeid";
        }
        elseif($comparison && $comparison == "teach")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.teacherset_breakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.teacherset_targetid"; 
        }
        else
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.bcgttargetbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.bcgttargetgradesid";
        }
        $sql .= " JOIN {block_bcgt_qualification} qual ON qual.id = useraward.bcgtqualificationid";
        $sql .= " WHERE userquals.bcgtqualificationid = ? AND (useraward.type = ? OR useraward.type = ? OR useraward.type = ? OR useraward.type = ?)";
        $params = array($qualID, 'CETA', 'AVG', 'FINAL', 'Predicted');
        if($courseID != -1)
        {
            $sql .= ' AND coursequal.courseid = ?';
            $params[] = $courseID;
        }
        $operand = ' = ';
        if($aheadBehindOn == 'ON')
        {
            $operand = ' = ';
        }
        elseif($aheadBehindOn == 'AHEAD')
        {
            $operand = ' > ';
        }
        elseif($aheadBehindOn == 'BEHIND')
        {
            $operand = ' < ';
        }
        $sql .= ' AND (awardranking '.$operand.' targetbreakdownranking OR graderanking '.$operand.' targetgradesranking)';
        
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * This will get an object that consists of
     * breakdown -> numberAhead / numberON/ numberBehind
     * breakdownpercentage -> %Ahead / %ON/ %Behind
     * $comparison = 
     *    "full" = calculated normal
     *    "weight" = calculated aspirational
     *    "teach" = calculated normal + one grade (unless changed by user)
     * @param type $qualID
     * @param type $comparison
     * @param type $courseID
     * @return \stdClass
     */
    public function get_breakdown_qual_users_status($qualID, $comparison, $courseID = -1)
    {
        //will get the percentage of ahead
        //will get the percentage if behind
        //will get the no ahead
        //will get the no behind
        
        //so needs to get all of those where the ranking of predicted 
        //is greater than the ranking of target
        //so needs to get all of those where the ranking of target 
        //is greater than the ranking of predicted
        $ahead = count($this->get_qual_users_by_target_current_status($qualID, $comparison, 'AHEAD', $courseID));
        $behind = count($this->get_qual_users_by_target_current_status($qualID, $comparison, 'BEHIND', $courseID));
        $on = count($this->get_qual_users_by_target_current_status($qualID, $comparison, 'ON', $courseID));
        
        $total = $ahead + $behind + $on;
        
        $retval = new stdClass();
        $retval->breakdown = $ahead.'/'.$on.'/'.$behind;
        $retval->breakdownpercentage = (($ahead/$total) * 100).'/'.(($on/$total) * 100).'/'.(($behind/$total) * 100);
        return $retval;
    }
    
    /**
     * This gets the 
     *  combined value added
     *  average value added
     *  furthest ahead
     *  furthest behind
     * for a course. 
     * @param type $courseID
     * @param type $comparison
     * @return \stdClass
     */
    public function get_course_users_va($courseID, $comparison)
    {
        //will get the combined VA
        $totalObj = $this->calc_course_users_va($courseID, $comparison, 'SUM');
        if($totalObj)
        {
            $totalBreak = $totalObj->fullgradedifference;
            $totalGrade = $totalObj->singlegradedifferece;
        }
        //will get the average VA
        $averageObj = $this->calc_course_users_va($courseID, $comparison, 'AVG');
        if($averageObj)
        {
            $avgBreak = $averageObj->fullgradedifference;
            $avgGrade = $averageObj->singlegradedifferece;
        }
        //will get the bigest ahead
        $furthestAheadObj = $this->calc_course_users_va($courseID, $comparison, 'MAX');
        if($furthestAheadObj)
        {
            $aheadBreak = $furthestAheadObj->fullgradedifference;
            $aheadGrade = $furthestAheadObj->singlegradedifferece;
        }
        
        //will get the bigest behind
        $furthestBehindObj = $this->calc_course_users_va($courseID, $comparison, 'MIN');
        if($furthestBehindObj)
        {
            $behindBreak = $furthestBehindObj->fullgradedifference;
            $behindGrade = $furthestBehindObj->singlegradedifferece;
        }
        
        $retval = new stdClass();
        $retval->combinedbreakdown = $totalBreak;
        $retval->combinedgrade = $totalGrade;
        $retval->averagebreakdown = $avgBreak;
        $retval->averagegrade = $avgGrade;
        $retval->aheadbreakdown = $aheadBreak;
        $retval->aheadgrade = $aheadGrade;
        $retval->behindbreakdown = $behindBreak;
        $retval->behindgrade = $behindGrade;
        return $retval;
        
    }
    
    /**
     * This gets the 
     *  combined value added
     *  average value added
     *  furthest ahead
     *  furthest behind
     * for a course. 
     * @param type $courseID
     * @param type $comparison
     * @return \stdClass
     */
    public function get_qual_users_va($qualID, $comparison, $courseID = -1)
    {
        //will get the combined VA
        $totalObj = $this->calc_qual_users_va($qualID, $comparison, 'SUM', $courseID);
        if($totalObj)
        {
            $totalBreak = $totalObj->sumbreakdown;
            $totalGrade = $totalObj->sumgrade;
        }
        //will get the average VA
        $averageObj = $this->calc_qual_users_va($qualID, $comparison, 'AVG', $courseID);
        if($averageObj)
        {
            $avgBreak = $averageObj->sumbreakdown;
            $avgGrade = $averageObj->sumgrade;
        }
        //will get the bigest ahead
        $furthestAheadObj = $this->calc_qual_users_va($qualID, $comparison, 'MAX', $courseID);
        if($furthestAheadObj)
        {
            $aheadBreak = $furthestAheadObj->sumbreakdown;
            $aheadGrade = $furthestAheadObj->sumgrade;
        }
        
        //will get the bigest behind
        $furthestBehindObj = $this->calc_qual_users_va($qualID, $comparison, 'MIN', $courseID);
        if($furthestBehindObj)
        {
            $behindBreak = $furthestBehindObj->sumbreakdown;
            $behindGrade = $furthestBehindObj->sumgrade;
        }
        
        $retval = new stdClass();
        $retval->combinedbreakdown = $totalBreak;
        $retval->combinedgrade = $totalGrade;
        $retval->averagebreakdown = $avgBreak;
        $retval->averagegrade = $avgGrade;
        $retval->aheadbreakdown = $aheadBreak;
        $retval->aheadgrade = $aheadGrade;
        $retval->behindbreakdown = $behindBreak;
        $retval->behindgrade = $behindGrade;
        return $retval;
        
    }
    
    /**
     * Gets the calculation (MAX, MIN, SUM, AVG) of the difference between predicted and target
     * Two sums are returned
     * breakdown and grades
     * it goes predicted - target
     * * $comparison = 
     *    "full" = calculated normal
     *    "weight" = calculated aspirational
     *    "teach" = calculated normal + one grade (unless changed by user)
     * @global type $DB
     * @param type $courseID
     * @param type $comparison
     * @return type
     */
    public function calc_course_users_va($courseID, $comparison, $function)
    {
        global $DB;
        $sql = "SELECT ".$function."(breakdown.ranking - tbreakdown.ranking) AS FullGradeDifference, 
            ".$function."(grades.ranking - tgrades.ranking) AS SingleGradeDifferece 
            FROM {block_bcgt_user_award} useraward 
            LEFT OUTER JOIN {block_bcgt_target_breakdown} breakdown ON useraward.bcgtbreakdownid = breakdown.id 
            LEFT OUTER JOIN {block_bcgt_target_grades} grades ON grades.id = useraward.bcgttargetgradesid
            JOIN {block_bcgt_user_course_trgts} utrgts ON utrgts.bcgtqualificationid = useraward.bcgtqualificationid
            AND utrgts.userid = useraward.userid
            JOIN {user} user ON user.id = useraward.userid
            JOIN {role_assignments} roleass ON roleass.userid = user.id 
            JOIN {context} context ON context.id = roleass.contextid 
            JOIN {role} role ON role.id = roleass.roleid
            JOIN {block_bcgt_user_prior} prior ON prior.userid = user.id
            JOIN {course} course ON course.id = context.instanceid ";
        if($comparison && $comparison == "weight")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.bcgtweightedbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.bcgtweightedgradeid";
        }
        elseif($comparison && $comparison == "teach")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.teacherset_breakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.teacherset_targetid"; 
        }
        else
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.bcgttargetbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.bcgttargetgradesid";
        }
        $sql .= " JOIN {block_bcgt_qualification} qual ON qual.id = useraward.bcgtqualificationid";
        $sql .= " WHERE course.id = ? AND (useraward.type = ? OR useraward.type = ? OR useraward.type = ? OR useraward.type = ?)";
        $params = array($courseID, 'CETA', 'AVG', 'FINAL', 'Predicted');
        return $DB->get_record_sql($sql, $params);
    }
    
    /**
     * Gets the calculation (MAX, MIN, SUM, AVG) of the difference between predicted and target
     * Two sums are returned
     * breakdown and grades
     * it goes predicted - target
     * * $comparison = 
     *    "full" = calculated normal
     *    "weight" = calculated aspirational
     *    "teach" = calculated normal + one grade (unless changed by user)
     * @global type $DB
     * @param type $courseID
     * @param type $comparison
     * @return type
     */
    public function calc_qual_users_va($qualID, $comparison, $function, $courseID = -1)
    {
        global $DB;
        $sql = "SELECT ".$function."(breakdown.ranking - tbreakdown.ranking) AS sumbreakdown, 
            ".$function."(grades.ranking - tgrades.ranking) AS sumgrade 
            FROM {block_bcgt_user_award} useraward 
            LEFT OUTER JOIN {block_bcgt_target_breakdown} breakdown ON useraward.bcgtbreakdownid = breakdown.id 
            LEFT OUTER JOIN {block_bcgt_target_grades} grades ON grades.id = useraward.bcgttargetgradesid
            JOIN {block_bcgt_user_course_trgts} utrgts ON utrgts.bcgtqualificationid = useraward.bcgtqualificationid
            AND utrgts.userid = useraward.userid
            JOIN {block_bcgt_user_qual} userquals ON userquals.userid = useraward.userid
            JOIN {role} role ON role.id = userquals.roleid ";
        if($comparison && $comparison == "weight")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.bcgtweightedbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.bcgtweightedgradeid";
        }
        elseif($comparison && $comparison == "teach")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.teacherset_breakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.teacherset_targetid"; 
        }
        else
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.bcgttargetbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.bcgttargetgradesid";
        }
        if($courseID != -1)
        {
            $sql .= " JOIN {block_bcgt_course_quals} coursequals ON coursequals.bcgtqualificationid = userquals.bcgtqualificationid";
        }
        $sql .= " JOIN {block_bcgt_qualification} qual ON qual.id = useraward.bcgtqualificationid";
        $sql .= " WHERE qual.id = ? AND role.shortname = ? AND (useraward.type = ? OR useraward.type = ? OR useraward.type = ? OR useraward.type = ?)";
        $params = array($qualID, 'student', 'CETA', 'AVG', 'FINAL', 'Predicted');
        if($courseID != -1)
        {
            $sql .= ' AND coursequals.courseid = ?';
            $params[] = $courseID;
        }
        return $DB->get_record_sql($sql, $params);
    }
    
    //Average Target Grade
    /**
     * Gets the average predicted grade for a course based on the ranking of the target grades
     * if the course has more than one type of targetqualid
     * then it will return a set.
     * @param type $courseID
     * @param type $comparison
     * @return boolean
     */
    public function get_course_users_avg_target_grades($courseID, $comparison)
    {
        //will get the users target grades
        //this averages on the ranking. 
        $targetQualCount = $this->count_course_qual_target_quals($courseID);
        if($targetQualCount == 1)
        {
            //then we can do just a straight comparison and average
            $targetQualID = -1;
            $targetQuals = $this->get_target_qual_course($courseID);
            if($targetQuals)
            {
                $targetQualID = end($targetQuals)->id;
            }
            return $this->get_course_users_average_target_award($courseID, $comparison, -1, $targetQualID);
        }
        elseif($targetQualCount > 1)
        {
            //else we are going to have to do a set
            //then we can do just a straight comparison and average
            return $this->get_course_users_average_target_award($courseID, $comparison);
        }
        return false;
        
    }
    
    //Average Predicted Grade
    /**
     * Gets the average predicted grade for a course based on the ranking of the predicted grades
     * if the course has more than one type of targetqualid
     * then it will return a set.
     * @param type $courseID
     * @param type $comparison
     * @return boolean
     */
    public function get_course_users_avg_predicted_grades($courseID, $comparison)
    {
        //Average Predicted Grade
        //will get the users target grades
        //this averages on the ranking. 
        $targetQualCount = $this->count_course_qual_target_quals($courseID);
        if($targetQualCount == 1)
        {
            //then lets get the targetqualid
            //then we can do just a straight comparison and average
            $targetQualID = -1;
            $targetQuals = $this->get_target_qual_course($courseID);
            if($targetQuals)
            {
                $targetQualID = end($targetQuals)->id;
            }
            return $this->get_course_users_average_qual_award($courseID, $comparison, -1, $targetQualID);
        }
        elseif($targetQualCount > 1)
        {
            //else we are going to have to do a set
            //then we can do just a straight comparison and average
            return $this->get_course_users_average_qual_award($courseID, $comparison);
        }
        return false;
    }
    
    //Average Target Grade
    /**
     * Gets the average predicted grade for a course based on the ranking of the target grades
     * if the course has more than one type of targetqualid
     * then it will return a set.
     * @param type $courseID
     * @param type $comparison
     * @return boolean
     */
    public function get_qual_users_avg_target_grades($qualID, $comparison)
    {
        //will get the users target grades
        //this averages on the ranking. 
        $coursesCount = $this->count_qual_courses($qualID);
        if($coursesCount == 1)
        {
            //then we can do just a straight comparison and average
            $courseID = -1;
            $courses = $this->get_qual_courses($qualID);
            if($courses)
            {
                $courseID = end($courses)->id;
            }
            return $this->get_course_users_average_target_award($courseID, $comparison, $qualID);
        }
        elseif($coursesCount > 1)
        {
            //else we are going to have to do a set
            //then we can do just a straight comparison and average
            return $this->get_course_users_average_target_award(-1, $comparison, $qualID);
        }
        return false;
        
    }
    
    //Average Predicted Grade
    /**
     * Gets the average predicted grade for a course based on the ranking of the predicted grades
     * if the course has more than one type of targetqualid
     * then it will return a set.
     * @param type $courseID
     * @param type $comparison
     * @return boolean
     */
    public function get_qual_users_avg_predicted_grades($qualID, $comparison)
    {
        //Average Predicted Grade
        //will get the users target grades
        //this averages on the ranking. 
        $coursesCount = $this->count_qual_courses($qualID);
        if($coursesCount == 1)
        {
            //then we can do just a straight comparison and average
            $courseID = -1;
            $courses = $this->get_qual_courses($qualID);
            if($courses)
            {
                $courseID = end($courses)->id;
            }
            return $this->get_course_users_average_qual_award($courseID, $comparison, $qualID);
        }
        elseif($coursesCount > 1)
        {
            //else we are going to have to do a set
            //then we can do just a straight comparison and average
            return $this->get_course_users_average_qual_award(-1, $comparison, $qualID);
        }
        return false;
    }

    /**
     * Gets the courses on a qual.
     * @global type $DB
     * @param type $qualID
     * @return type
     */
    private function get_qual_courses($qualID)
    {
        global $DB;
        $sql = "SELECT dictinct(courseid) AS id, courseid FROM {block_bcgt_course_qual} WHERE bcgttargetqualid = ?";
        return $DB->get_records_sql($sql, array($qualID));
    }
    
    /**
     * Gets the target quals ids that are on a course.
     * @global type $DB
     * @param type $courseID
     * @return type
     */
    private function get_target_qual_course($courseID)
    {
        global $DB;
        $sql = "SELECT distinct(qual.bcgttargetqualid) as id, qual.bcgttargetqualid FROM {block_bcgt_course_qual} coursequal 
            JOIN {block_bcgt_qualification} qual ON qual.id = coursequal.bcgtqualificationid 
            WHERE coursequal.courseid = ?";
        return $DB->get_records_sql($sql, array($courseID));
    }
    
    /**
     * Gets the users of a course and gets the number and percentages that have
     * a predicted status
     * Gets the totalstudents, withaward, percentage
     * @param type $courseID
     * @return \stdClass
     */
    public function get_course_users_predicted_status($courseID)
    {
        //will get the total no
        //will get the no without
        //will get the average
        //will get the percentage
        $usersOnCourse = $this->count_course_users($courseID);
        $usersWithAward = $this->count_course_users_with_qual_award($courseID);
        
        $retval = new stdClass();
        $retval->totalstudents = $usersOnCourse;
        $retval->withaward = $usersWithAward;
        $retval->percentage = ($usersWithAward/$usersOnCourse) * 100;
        
        return $retval;
    }
    
    /**
     * Gets the users of a course and gets the number and percentages that have
     * a predicted status
     * Gets the totalstudents, withaward, percentage
     * @param type $courseID
     * @return \stdClass
     */
    public function get_qual_users_predicted_status($qualID)
    {
        //will get the total no
        //will get the no without
        //will get the average
        //will get the percentage
        $usersOnQual = $this->count_qual_users($qualID);
        $usersWithAward = $this->count_qual_users_with_qual_award($qualID);
        
        $retval = new stdClass();
        $retval->totalstudents = $usersOnQual;
        $retval->withaward = $usersWithAward;
        $retval->percentage = ($usersWithAward/$usersOnQual) * 100;
        
        return $retval;
    }
    
    
//    //TODO right a function that will return all averages for a course
//    //this way we can do a 
//    //Level 3 BTEC EDip = DDD
//    //AS Maths = E
//    //as a table
//    //with a boolean as to if there are more than one qual on a course
//    //then return N/S
//    public function get_course_users_average_awards($courseID)
//    {
//        
//    }
    
    /**
     * Counts the number of quals on a course
     * @global type $DB
     * @param type $courseID
     * @return type
     */
    public function count_course_quals($courseID)
    {
        global $DB;
        $sql = "SELECT count(distinct(bcgtqualificationid)) FROM {block_bcgt_course_qual} WHERE courseid = ?";
        return $DB->count_records_sql($sql, array($courseID)); 
    }
    
    /**
     * Counts the number of different qual setups are on a course
     * e.g. Btec Level 3 Extended Diploma
     * //ignores the name and thus if there were business and maths on there
     * //then it would return 2.
     * @global type $DB
     * @param type $courseID
     * @return type
     */
    public function count_course_qual_target_quals($courseID)
    {
        global $DB;
        $sql = "SELECT count(distinct(targetqual.id)) FROM {block_bcgt_course_qual} coursequal 
            JOIN {block_bcgt_qualification} qual ON qual.id = coursequal.bcgtqualificationid 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            WHERE courseid = ?";
        return $DB->count_records_sql($sql, array($courseID)); 
    }
    
    /**
     * Counts the number of different qual families are on a course
     * @global type $DB
     * @param type $courseID
     * @return type
     */
    public function count_course_qual_families($courseID)
    {
        global $DB;
        $sql = "SELECT count(distinct(family.id)) as FamilyCount FROM {block_bcgt_course_qual} coursequal 
            JOIN {block_bcgt_qualification} qual ON qual.id = coursequal.bcgtqualificationid 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
            WHERE courseid = ?";
        return $DB->count_records_sql($sql, array($courseID)); 
    }
    
    /**
     * Counts the number of courses a qual is attached to
     * @global type $DB
     * @param type $qualID
     * @return type
     */
    public function count_qual_courses($qualID)
    {
        global $DB;
        $sql = "SELECT count(distinct(courseid)) FROM {block_bcgt_course_qual} WHERE bcgtqualificationid = ?";
        return $DB->count_records_sql($sql, array($qualID));
    }
    
    /**
     * Groups by course or targetqual and gets the average ranking of the
     * breakdown and target grades for the award
     * @global type $DB
     * @param type $courseID
     * @param type $comparison
     * @param type $qualID
     * @param type $targetQualID
     * @return type
     */
    public function get_course_users_average_qual_award($courseID = -1, $comparison = null, 
            $qualID = -1, $targetQualID = -1)
    {
        global $DB;
        //average by ranking
        //Course->qual->users->breakdowns->average
        
        $sql = "SELECT coursequal.id as id, AVG(tbreakdown.ranking) as breakdownranking, tbreakdown.targetgrade as targetgrade,  
            AVG(tgrades.ranking) AS graderanking, tgrades.grade AS grade FROM {block_bcgt_course_qual} coursequal 
            JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = coursequal.bcgtqualificationid 
            JOIN {block_bcgt_user_award} uaward ON uaward.bcgtqualificationid = coursequal.bcgtqualificationid 
            AND uaward.userid = userqual.userid 
            JOIN {block_bcgt_qualification} qual ON qual.id = coursequal.bcgtqualificationid 
            JOIN {role} role ON role.id = userqual.roleid ";
        if($comparison && $comparison == "weight")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = uaward.bcgtweightedbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = uaward.bcgtweightedgradeid";
        }
        elseif($comparison && $comparison == "teach")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = uaward.teacherset_breakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = uaward.teacherset_targetid"; 
        }
        else
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = uaward.bcgttargetbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = uaward.bcgttargetgradesid";
        }
        $sql .= " WHERE role.shortname = ? AND (uaward.type = ? OR uaward.type = ? OR uaward.type = ? OR uaward.type = ?)";
        $params = array('student', 'CETA', 'AVG', 'FINAL', 'Predicted');
        if($courseID != -1)
        {
            $sql .= " AND coursequal.courseid = ?";
            $params[] = $courseID;
        }
        if($qualID != -1)
        {
            $sql .= " AND qual.id = ?";
            $params[] = $qualID;
        }
        if($targetQualID != -1)
        {
            $sql .= " AND qual.bcgttargetqualid = ?";
            $params[] = $targetQualID;
        }
        if($courseID != -1)
        {
            $sql .= " GROUP BY qual.id";
        }
        elseif($targetQualID != -1 || $qualID != -1)
        {
            $sql .= " GROUP BY coursequal.courseid";
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Groups by course or targetqual and gets the average ranking of the
     * breakdown and target grades for the target grade
     * @global type $DB
     * @param type $courseID
     * @param type $comparison
     * @param type $qualID
     * @param type $targetQualID
     * @return type
     */
    public function get_course_users_average_target_award($courseID = -1, $comparison = null, 
            $qualID = -1, $targetQualID = -1)
    {
        global $DB;
        //average by ranking
        //Course->qual->users->breakdowns->average
        
        $sql = "SELECT coursequal.id as id, AVG(tbreakdown.ranking) as breakdownranking, tbreakdown.targetgrade as targetgrade,  
            AVG(tgrades.ranking) AS graderanking, tgrades.grade AS grade FROM {block_bcgt_course_qual} coursequal 
            JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = coursequal.bcgtqualificationid 
            JOIN {block_bcgt_user_course_trgts} utrgts ON utrgts.bcgtqualificationid = coursequal.bcgtqualificationid 
            AND utrgts.userid = userqual.userid 
            JOIN {block_bcgt_qualification} qual ON qual.id = coursequal.bcgtqualificationid 
            JOIN {role} role ON role.id = userqual.roleid ";
        if($comparison && $comparison == "weight")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.bcgtweightedbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.bcgtweightedgradeid";
        }
        elseif($comparison && $comparison == "teach")
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.teacherset_breakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.teacherset_targetid"; 
        }
        else
        {
            $sql .= " LEFT OUTER JOIN {block_bcgt_target_breakdown} tbreakdown ON tbreakdown.id = utrgts.bcgttargetbreakdownid
            LEFT OUTER JOIN {block_bcgt_target_grades} tgrades ON tgrades.id = utrgts.bcgttargetgradesid";
        }
        $sql .= " WHERE role.shortname = ?";
        $params = array('student');
        if($courseID != -1)
        {
            $sql .= " AND coursequal.courseid = ?";
            $params[] = $courseID;
        }
        if($qualID != -1)
        {
            $sql .= " AND qual.id = ?";
            $params[] = $qualID;
        }
        if($targetQualID != -1)
        {
            $sql .= " AND qual.bcgttargetqualid = ?";
            $params[] = $targetQualID;
        }
        if($courseID != -1)
        {
            $sql .= " GROUP BY qual.id";
        }
        elseif($targetQualID != -1 || $qualID != -1)
        {
            $sql .= " GROUP BY coursequal.courseid";
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * This counts the users that have a qualification award set
     * @global type $DB
     * @param type $courseID
     * @return type
     */
    public function count_course_users_with_qual_award($courseID)
    {
        global $DB;
        $sql = "SELECT count(distinct(user.id)) as count FROM {user} user 
            JOIN {role_assignments} roleass ON roleass.userid = user.id 
            JOIN {context} context ON context.id = roleass.contextid 
            JOIN {role} role ON role.id = roleass.roleid
            JOIN {block_bcgt_user_award} award ON award.userid = user.id
            JOIN {course} course ON course.id = context.instanceid
            WHERE course.id = ? AND context.contextlevel = ? AND role.shortname = ? AND 
            ((award.bcgtbreakdownid IS NOT NULL AND award.bcgtbreakdownid != ?) OR 
            (award.bcgttargetgradesid IS NOT NULL AND award.bcgttargetgradesid != ?)) 
            AND (award.type = ? OR award.type = ? OR award.type = ? OR award.type = ?)";
        return $DB->count_records_sql($sql, array($courseID, 50, 'student', -1, -1,'CETA', 'AVG', 'FINAL', 'Predicted'));
    }
    
    /**
     * This counts the users that have a qualification award set for the qual
     * @global type $DB
     * @param type $courseID
     * @return type
     */
    public function count_qual_users_with_qual_award($qualID)
    {
        global $DB;
        $sql = "SELECT count(distinct(user.id)) as count FROM {user} user 
            JOIN {block_bcgt_qual_users} qualusers ON qualusers.userid = user.id 
            JOIN {block_bcgt_user_award} award ON award.userid = user.id
            JOIN {role} role ON role.id = qualusers.roleid
            WHERE qualusers.bcgtqualificationid = ? AND  
            ((award.bcgtbreakdownid IS NOT NULL AND award.bcgtbreakdownid != ?) OR 
            (award.bcgttargetgradesid IS NOT NULL AND award.bcgttargetgradesid != ?) AND role.shortname = ? 
            AND 
            ((award.bcgtbreakdownid IS NOT NULL AND award.bcgtbreakdownid != ?) OR 
            (award.bcgttargetgradesid IS NOT NULL AND award.bcgttargetgradesid != ?)) 
            AND (award.type = ? OR award.type = ? OR award.type = ? OR award.type = ?)";
        return $DB->count_records_sql($sql, array($qualID, -1, -1, 'student','CETA', 'AVG', 'FINAL', 'Predicted'));
    }
    
    
    //For a Course/Qual:
    //get all users average scores
    public function get_course_users_average_scores($courseID)
    {
        //returns users and their average scores in a db array
    }
    
    //get all users target grades
    public function get_course_users_target_grades($courseID)
    {
        //returns users and their target grades in a db array
        //where the users is on more than one qual it will return this
        //as a second row of the user. 
    }
    
    //get all users predicted grades
    public function get_course_users_predicted_grades($courseID)
    {
        //returns users and their predicted grades in a db array
        //where the users is on more than one qual it will return this
        //as a second row of the user. 
    }
    
    //get all users VA
    public function get_course_users_value_added($courseID)
    {
        //returns users and their + and - value added
        //where the users is on more than one qual it will return this
        //as a second row of the user. 
    }
}

?>
