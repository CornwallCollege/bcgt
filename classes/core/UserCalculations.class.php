<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserCalculations
 *
 * @author mchaney
 */
class UserCalculations {
    //put your code here
    protected $calculation;
    
    //I AM FULLY AWARE THAT HAVING THESE SWITCHES IN IT IS NOT FULLY OBJECT ORIENTED!!
//THEY SHOULD BE inherited instancs of classes. in a workspace or something
    
    public function UserCalculations($calculation)
    {
        $this->calculation = $calculation;
    }
    
    public function get_tabs()
    {
        global $CFG;
        $out = '<div class="tabs"><div class="tabtree">';
        $out .= '<ul class="tabrow0">';
        $out .= '<li>'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/calculate_user_values.php?a=tg">'.
                '<span>'.get_string('targetgrades', 'block_bcgt').'</span></a></li>';
        $out .= '<li>'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/calculate_user_values.php?a=pg">'.
                '<span>'.get_string('predictedgrades', 'block_bcgt').'</span></a></li>';
        $out .= '</ul>';
        $out .= '</div></div>';
        
        return $out;
    }
    
    public function get_header()
    {
        switch($this->calculation)
        {
            case "tg":
                return get_string('calculatetargetgrade', 'block_bcgt');
                break;
            case "pg":
                return get_string('calculatepredictedgrade', 'block_bcgt');
                break;
            default:
                return "";
        }
    }
    
    public function get_calculation_form()
    {
        $out = '';
        
        switch($this->calculation)
        {
            case "tg":
                $out .= $this->get_target_grade_display();
                break;
            case "pg":
                $out .= $this->get_predicted_grade_display();
                break;
            default:
                $out .= "";
                break;
        }
        
        return $out;
    }
    
    public function get_description()
    {
        $out = '<p>';
        switch($this->calculation)
        {
            case "tg":
                $out .= get_string('calcusertgdesc','block_bcgt');
                break;
            case "pg":
                $out .= get_string('calcuserpgdesc','block_bcgt');
                break;
            default:
                $out .= "";
                break;
        }
        $out .= '</p>';
        return $out;
    }
    
    public function process_calculation()
    {
        if(isset($_POST['calculate']) || isset($_POST['calculateall']))
        {
            //so this will go and get all of the users. Either by course or qual or specific users
            set_time_limit(0);
            $users = null;
            if(isset($_POST['calculate']))
            {
                $selectedQuals = isset($_POST['qualID'])?$_POST['qualID']: array();
                //loop over the quals and find all of the users
                $selectedCourses = isset($_POST['courseID'])?$_POST['courseID']: array();
                //loop over the courses and find all of the users
                $selectedUsers = isset($_POST['userID'])?$_POST['userID']: array();
                //selected users
                $users = bcgt_get_users($selectedQuals, $selectedCourses, $selectedUsers);
            }

            $out = '';
            switch($this->calculation)
            {
                case "tg":
                    $out .= $this->process_target_grade_calc($users);
                    break;
                case "pg":
                    $out .= $this->process_pred_grade_calc($users);
                    break;
                default:
                    $out .= "";
                    break;
            }
            return $out;
        }
    }
    
    /**
     * Gets a drop down, and select for the quals
     * @return string
     */
    protected function get_qual_options()
    {
        $qualSearch = optional_param('qualsearch', '', PARAM_TEXT);
        $out = '<div>';
        $out .= '<h3>'.get_string('qualification','block_bcgt').'</h3>';
        $out .= '<select class="qual" multiple name="qualID[]">';
        $out .= '<option value="-1"></option>';
        $quals = search_qualification(-1, -1, -1, $qualSearch, -1, null, -1, null, true); 
        if($quals)
        {
            foreach($quals AS $qual)
            {
                $out .= '<option value="'.$qual->id.'">'.  bcgt_get_qualification_display_name($qual).'</option>';
            }
        } 
        $out .= '</select>';
        $out .= '<input type="text" name="qualsearch" value="'.$qualSearch.'"/>';
        $out.= '</div>';
        
        return $out;
    }
    
    /**
     * 
     * @return string
     */
    protected function get_course_options()
    {
        $courseSearch = optional_param('coursesearch', '', PARAM_TEXT);
        $out = '<div>';
        $out .= '<h3>'.get_string('course','block_bcgt').'</h3>';
        $out .= '<select class="course" multiple name="courseID[]">';
        $out .= '<option value="-1"></option>';
        $courses = bcgt_get_courses_with_quals(-1, null, $courseSearch);
        if($courses)
        {
            foreach($courses AS $course)
            {
                $out .= '<option value="'.$course->id.'">'.$course->shortname.' : '.$course->fullname.'</option>';
            }
        }
        $out .= '</select>';
        $out .= '<input type="text" name="coursesearch" value="'.$courseSearch.'"/>';
        $out.= '</div>';
        return $out;
    }
    
    /**
     * 
     */
    protected function get_user_options()
    {
        global $DB;
        $userSearch = optional_param('usersearch', '', PARAM_TEXT);
        $out = '<div>';
        $out .= '<h3>'.get_string('user','block_bcgt').'</h3>';
        $out .= '<select class="user" multiple name="userID[]">';
        if($userSearch == '')
        {
            $out .= '<option value="-1">'.get_string('toomanyusers','block_bcgt').'</option>';
        }
        else
        {
            $sql = "SELECT * FROM {user} WHERE username LIKE ? OR firstname LIKE ? OR lastname LIKE ? OR email LIKE ?";
            $params = array();
            $params[] = '%'.$userSearch.'%';
            $params[] = '%'.$userSearch.'%';
            $params[] = '%'.$userSearch.'%';
            $params[] = '%'.$userSearch.'%';                      
            $searches = explode(" ", $userSearch);
            if($searches)
            {
                foreach($searches AS $search)
                {
                    $sql .= ' OR firstname LIKE ? OR lastname LIKE ? OR username LIKE ? OR email LIKE ?';
                    $params[] = '%'.$search.'%';
                    $params[] = '%'.$search.'%';
                    $params[] = '%'.$search.'%';
                    $params[] = '%'.$search.'%';
                }
            }
            $users = $DB->get_records_sql($sql, $params);
            if($users)
            {
                foreach($users AS $user)
                {
                    $out .= '<option value="'.$user->id.'">'.$user->username.' : '.
                            $user->firstname.' '.$user->lastname.'</option>';
                }
            }         
        }
        $out .= '</select>';
        $out .= '<input type="text" name="usersearch" value="'.$userSearch.'"/>';
        $out.= '</div>';
        
        return $out;
    }
    
    protected function get_target_grade_display()
    {
        $tgCheck = optional_param('targetgrade', false, PARAM_BOOL);
        $avgCheck = optional_param('avgscore', false, PARAM_BOOL);
        $aspCheck = optional_param('asptargetgrade', false, PARAM_BOOL);
        
        $out = '';
        $checkedAvg = '';
        if($avgCheck)
        {
            $checkedAvg = 'checked';
        }
        $out .= '<div><label for="avgscore">'.get_string('calcavgscore', 'block_bcgt').
                ' : </label><input type="checkbox" '.$checkedAvg.' name="avgscore"/></div>';
        $checkedPred = '';
        if($tgCheck)
        {
            $checkedPred = 'checked';
        }
        $out .= '<div><label for="targetgrade">'.get_string('calctargetgrade', 'block_bcgt').
                ' : </label><input type="checkbox" '.$checkedPred.' name="targetgrade"/></div>';
        
        $checkedAsp = '';
        if($aspCheck)
        {
            $checkedAsp = 'checked';
        }
        $out .= '<div><label for="asptargetgrade">'.get_string('calcasptargetgrade', 'block_bcgt').
                ' : </label><input type="checkbox" '.$checkedAsp.' name="asptargetgrade"/></div>';
        
        $out .= $this->get_qual_options();
        $out .= $this->get_course_options();
        $out .= $this->get_user_options();
        
        return $out;
    }
    
    protected function get_predicted_grade_display()
    {
        $out = '';
        $out .= $this->get_qual_options();
        $out .= $this->get_course_options();
        $out .= $this->get_user_options();
        return $out;
    }
    
    protected function process_target_grade_calc($users)
    {
        $out = '';
        $tgCheck = optional_param('targetgrade', false, PARAM_BOOL);
        $avgCheck = optional_param('avgscore', false, PARAM_BOOL);
        $aspCheck = optional_param('asptargetgrade', false, PARAM_BOOL);
        $userCourseTarget = new UserCourseTarget();
        if($avgCheck)
        {
            $userCourseTarget->calculate_users_average_gcse_score($users, $tgCheck);
        }
        if(!$avgCheck && $tgCheck)
        {
            $userCourseTarget->calculate_aspirational_grades_check($aspCheck);
            $userCourseTarget->calculate_users_target_grades($users);
        }
        $out .= get_string('success', 'block_bcgt');
        return $out;
    }
    
    protected function process_pred_grade_calc($users)
    {
        $out = '';
        //we need to then, for each $users, 
        //calculate their predicted grade
        
        $qualAward = new QualificationAward();
        $qualAward->calculate_users_qual_awards($users);
        
        $out .= get_string('success', 'block_bcgt');
        return $out;
    }
    
}

?>
