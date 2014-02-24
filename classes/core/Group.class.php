<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Group
 *
 * @author mchaney
 */
class Group {
    
    protected $file;
    protected $summary;
    protected $success;

    protected $courseid;
    protected $name;
    protected $id;
    
    protected $matchMeta;
    protected $importMethod;
    protected $createGroupings;
    protected $groupForCourse;
    
    public function Group($id = -1, $params = null)
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
                $this->load_group($id);
            }
        }
        elseif($params)
        {
            $this->extract_params($params);
        }
        
        $importMethod = optional_param('sub', '', PARAM_TEXT);
        $this->importMethod = $importMethod;
        
        //below done on Method. 
//        $createMatchingGroupings = optional_param('groupings', '', PARAM_TEXT);
//        $this->matchGroupings = $createMatchingGroupings;
    }
    
    public function get_id()
    {
        return $this->id;
    }
    
    public function set_name($name)
    {
        $this->name = $name;
    }
    
    public function get_name()
    {
        return $this->name;
    }
    
    public function set_import_method($importMethod)
    {
        $this->importMethod = $importMethod;
    }
    
    public function set_course_id($courseID)
    {
        $this->courseid = $courseID;
    }
    
    public function get_course_id()
    {
        return $this->courseid;
    }
    
    public function was_success()
    {
        return $this->success;
    }
    
    public function get_description()
    {
        global $CFG;
        $retval = '<p>'.get_string('overallgroupdataimportdesc','block_bcgt').'</p>';
        $retval .= '<div class="tabs"><div class="tabtree">';
        $retval .= '<ul class="tabrow0">';
        $focus = '';
        if($this->importMethod == 'c')
        {
            $focus = 'focus';
        }
        $retval .= '<li class="'.$focus.'">'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/import.php?a=gr&sub=c">'.
                '<span>'.get_string('coursegroup', 'block_bcgt').'</span></a></li>';
        $focus = '';
        if($this->importMethod == 't')
        {
            $focus = 'focus';
        }
        $retval .= '<li class="'.$focus.'">'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/import.php?a=gr&sub=t">'.
                '<span>'.get_string('staffgroupings', 'block_bcgt').'</span></a></li>';
        $retval .= '</ul>';
        $retval .= '</div></div>';
        $retval .= '<p>'.$this->get_sub_description().'</p>';
        //now output the description. 
        
        return $retval .= '';
//        return get_string('uddesc', 'block_bcgt');
    }
    
    public function display_summary()
    {
        global $DB;
        //$summary->userNotOnCourse;
//        $summary->usersNotfound;
//        $summary->coursesNotFound;
//        $summary->groupsNotFound;
//        
        $retval = '<p><ul>';
        if($this->summary)
        {
            $retval .= '<li>'.get_string('successcount','block_bcgt').' : '.$this->summary->successCount.'</li>';
//            if(!$this->success)
//            {
                $retval .= '<li>'.get_string('nousersnotfound','block_bcgt').' : '.count($this->summary->usersNotFound).'</li>';
                $retval .= '<li>'.get_string('nocoursesnotfound','block_bcgt').' : '.count($this->summary->coursesNotFound).'</li>';
                switch($this->importMethod)
                {
                    case 'c':
                        $retval .= '<li>'.get_string('nousersnotonourse','block_bcgt').' : '.count($this->summary->usersNotOnCourse).'</li>';
                        break;
                    case 't':
                        $retval .= '<li>'.get_string('nogroupsnotfound','block_bcgt').' : '.count($this->summary->groupsNotFound).'</li>';
                        break;
                    default:
                        break;
                }
//             }
        } 
        $retval .= '</ul></p>';
        if(!$this->success)
        {
            if($this->summary)
            {
                if(count($this->summary->usersNotFound) > 0 )
                {
                    $retval .= '<h4>'.get_string('usersnotfound', 'block_bcgt').'</h4>';
                    $retval .= '<ul>';
                    foreach($this->summary->usersNotFound AS $username)
                    {
                        $retval .= '<li>'.$username.'</li>';
                    }
                    $retval .= '</ul>';
                }

                if(count($this->summary->coursesNotFound) > 0 )
                {
                    $retval .= '<h4>'.get_string('coursesnotfound', 'block_bcgt').'</h4>';
                    $retval .= '<ul>';
                    foreach($this->summary->coursesNotFound AS $courseName)
                    {
                        $retval .= '<li>'.$courseName.'</li>';
                    }
                    $retval .= '</ul>';
                }

                switch($this->importMethod)
                {
                    case 'c':
                        $retval .= '<h3>'.get_string('errors', 'block_bcgt').'</h3>';
                        if(count($this->summary->usersNotOnCourse) > 0 )
                        {
                            $retval .= '<h4>'.get_string('usersnotoncourse', 'block_bcgt').'</h4>';
                            $retval .= '<table>';
                            $retval .= '<tr><th>'.get_string('username').'</th><th>'.get_string('courses', 'block_bcgt').'</th></tr>';
                            foreach($this->summary->usersNotOnCourse AS $userObj)
                            {
                                $username = $userObj->user->username;
                                $retval .= '<tr>';
                                $retval .= '<td>'.$username.'</td>';
                                $retval .= '<td><ul>';
                                foreach($userObj->courses AS $courseID)
                                {
                                    $course = $DB->get_record_sql('SELECT * FROM {course} WHERE id = ?', array($courseID));
                                    $retval .= '<li>'.$course->shortname.'</li>';
                                }
                                $retval .= '</ul></td>';
                                $retval .= '</tr>';
                            }
                            $retval .= '</table>';
                        }
                        break;
                    case 't':
                        if(count($this->summary->groupsNotFound) > 0 )
                        {
                            $retval .= '<h4>'.get_string('groupsnotfound', 'block_bcgt').'</h4>';
                            $retval .= '<table>';
                            $retval .= '<tr><th>'.get_string('course').'</th><th>'.get_string('groups').'</th></tr>';
                            foreach($this->summary->groupsNotFound AS $courseID => $courseObj)
                            {
                                $course = $DB->get_record_sql('SELECT * FROM {course} WHERE id = ?', array($courseID));
                                $retval .= '<tr>';
                                $retval .= '<td>'.$course->shortname.'</td>';
                                $retval .= '<td><ul>';
                                foreach($courseObj->groups AS $groupName)
                                {
                                    $retval .= '<li>'.$groupName.'</li>';
                                }
                                $retval .= '</ul></td>';
                                $retval .= '</tr>';
                            }
                            $retval .= '</table>';
                        }

                        break;
                    default:
                        break;
                }
            }
            
        }
        return $retval;
    }
    
    protected function get_sub_description()
    {
        switch($this->importMethod)
        {
            case "c":
                return get_string('coursegroupimportdesc', 'block_bcgt');
                break;
            case "t":
                return get_string('staffgroupimportdesc', 'block_bcgt');
                break;
            default:
                return "";
                break;
        }
    }
    
    public function get_headers()
    {
        switch($this->importMethod)
        {
            case "c":
                return $this->get_header(1);
                break;
            case "t":
                return $this->get_header(2);
                break;
            default:
                return array();
                break;
        }
    }
    
    public function display_import_options()
    {
        global $DB;
        $retval = '<input type="hidden" name="sub" value="'.$this->importMethod.'"/>';
        $retval .= '<table>';
        if($this->importMethod == 'c')
        {
            //create the grouping as well
            $retval .= '<tr><td><label for="groupings">'.get_string('creategroupingsimport', 'block_bcgt').' : </label></td>';
            $retval .= '<td><input type="checkbox" name="groupings" checked="checked"/></td>';
            $retval .= '<td><span class="description">('.get_string('creategroupingsimportdesc', 'block_bcgt').')</span></td></tr>';
        
            //match groups on mete acourses as well?
            $retval .= '<tr><td><label for="metacoursematch">'.get_string('matchgroupmetacourseimport', 'block_bcgt').' : </label></td>';
            $retval .= '<td><input type="checkbox" name="metacoursematch"/></td>';
            $retval .= '<td><span class="description">('.get_string('matchgroupmetacourseimportdesc', 'block_bcgt').')</span></td></tr>';
        
            $retval .= '<tr><td><label for="groupforchild">'.get_string('groupforchildimport', 'block_bcgt').' : </label></td>';
            $retval .= '<td><input type="checkbox" name="groupforchild"/></td>';
            $retval .= '<td><span class="description">('.get_string('groupforchildimportdesc', 'block_bcgt').')</span></td></tr>';
        }
        $retval .= '</table>';
        return $retval;
    }
    
    public function get_submitted_import_options()
    {
        if(isset($_POST['groupings']))
        {
            $this->createGroupings = true;
        }
        if(isset($_POST['metacoursematch']))
        {
            $this->matchMeta = true;
        }
        if(isset($_POST['groupforchild']))
        {
            $this->groupForCourse = true;
        }
    }
    
    public function process_csv_line($row)
    {
        global $DB;
        $username = $row[0];
        $courseShortName = $row[1];
        $group = $row[2];
        
        $sql = "SELECT * FROM {user} WHERE username = ?";
        $user = $DB->get_record_sql($sql, array($username));
        if($user)
        {
            $sql = "SELECT * FROM {course} WHERE shortname = ?";
            $course = $DB->get_record_sql($sql, array($courseShortName));
            if($course)
            {
                $this->add_user_to_course_grouping($user->id, $course, -1, $group);
                
                //are they on any of the meta courses?
                $metaCourses = bcgt_get_meta_courses($course->id);
                if($metaCourses)
                {
                    foreach($metaCourses AS $metaCourse)
                    {
                        $this->add_user_to_course_grouping($user->id, $metaCourse, $course->id, $group);
                    }
                }
            }
            else
            {
                //course not found
                return array(
                    'result' => -1,
                    'error' => 'Course Not Found -> '.$courseShortName.''
                );
            }
        }
        else
        {
            //user not found
            return array(
                'result' => -1,
                'error' => 'User Not Found -> '.$username.''
            );
        }
        
        return array(
           'result' => 1,
       );
    }
    
    private function add_user_to_course_grouping($userID, $course, $childCourseID = -1, $groupingName = '')
    {
        //does the group exist on the course?
        //is the user enrolled on here?
        if(bcgt_is_user_on_course_user($userID, $course->id))
        {
            //does course have qual?
            if(bcgt_get_course_quals($course->id))
            {
                $groupNameAppend = '';
                if($childCourseID != -1)
                {
                    $groupNameAppend = $this->get_meta_course_group_name_string($course->id);
                }
                $grouping = new Grouping();
                $courseGrouping = $grouping->get_grouping_on_course($course->id, $groupNameAppend.$groupingName);
                if($courseGrouping)
                {
                    //does the pref exist?
                    if(!$this->get_user_bcgt_group_prefs(-1, $courseGrouping->id, $course->id, $userID))
                    {
                        $this->add_user_bcgt_group_pref($courseGrouping->id, $course->id, $userID);
                    }
                } 
            }                            
        }
    }
    
    public function get_my_groups($courseID = -1, $qualExcludes = null)
    {
        global $DB, $USER;
        $sql = "SELECT distinct(g.id), g.*, course.shortname as shortname FROM {groupings} g 
            JOIN {block_bcgt_user_grouping} ug ON g.id = ug.groupingid 
            JOIN {course} course ON course.id = g.courseid";
        if($qualExcludes && count($qualExcludes) != 0)
        {
            $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = g.courseid 
                JOIN {block_bcgt_qualification} qual ON qual.id = coursequal.id 
                JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
                JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
                JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid ";
        }
        $sql .= " WHERE ug.userid = ?";
        $params = array();
        $params[] = $USER->id;
        if($courseID != -1)
        {
            $sql .= ' AND g.courseid = ?';
            $params[] = $courseID;
        }
        if($qualExcludes && count($qualExcludes) != 0)
        {
            $sql .= ' AND';
            $count = 0;
            foreach($qualExcludes AS $family)
            {
                $count++;
                if($count != 1)
                {
                    $sql .= ' AND';
                }
                $sql .= ' family.family != ?';
                $params[] = $family;
            }
            $sql .= '';
            $and = true;
        }
        $sql .= ' ORDER BY g.name ASC, course.shortname ASC';
        return $DB->get_records_sql($sql, $params);
    }
    
    public function get_all_possible_groups($courseID = -1, $qualExcludes = null)
    {
        //find all of the courses that have qualifications
        //find all of their groups, that have students.
        global $DB;
        $sql = "SELECT distinct(gr.id), gr.*, course.shortname FROM {groupings} gr 
            JOIN {course} course ON course.id = gr.courseid 
            JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = course.id
            JOIN {groupings_groups} gg ON gg.groupingid = gr.id
            JOIN {groups_members} gm ON gm.groupid = gg.groupid";
        if($qualExcludes && count($qualExcludes) != 0)
        {
            $sql .= " JOIN {block_bcgt_qualification} qual ON qual.id = coursequal.id 
                JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
                JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
                JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid ";
        }
        $params = array();
        if($courseID != -1 || ($qualExcludes && count($qualExcludes) != 0))
        {
            $sql .= ' WHERE';
        }
        $and = false;
        if($courseID != -1)
        {
            $sql .= " course.id = ?";
            $params[] = $courseID;
            $and = true;
        }
        if($qualExcludes && count($qualExcludes) != 0)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $count = 0;
            foreach($qualExcludes AS $family)
            {
                $count++;
                if($count != 1)
                {
                    $sql .= ' AND';
                }
                $sql .= ' family.family != ?';
                $params[] = $family;
            }
            $sql .= '';
            $and = true;
        }
        $sql .= ' ORDER BY gr.name ASC, course.shortname ASC ';
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Adds staff to 
     * @param type $courseID
     * @param type $userID
     * @param type $childCourseGroup
     */
    public function add_staf_to_meta_course_group_prefs($courseID, $userID, $groupName)
    {
        //are they on any of the meta courses?
        $metaCourses = bcgt_get_meta_courses($courseID);
        if($metaCourses)
        {
            mtrace("Course has meta courses");
            foreach($metaCourses AS $metaCourse)
            {
                //does the group exist on the meta course?
                //is the user enrolled on here?
                if(bcgt_is_user_on_course_user($userID, $metaCourse->id))
                {
                    mtrace("User is on Meta course");
                    //does course have qual?
                    if(bcgt_get_course_quals($metaCourse->id))
                    {
                        mtrace("Meta course has qual process group");
                        $groupNameAppend = $this->get_meta_course_group_name_string($courseID);
                        $courseGroup = $this->get_group_on_course($metaCourse->id, $groupNameAppend.$groupName);
                        if($courseGroup)
                        {
                            //does the pref exist?
                            if(!$this->get_user_bcgt_group_prefs(-1, $courseGroup->id, $metaCourse->id, $userID))
                            {
                                $this->add_user_bcgt_group_pref($courseGroup->id, $metaCourse->id, $userID);
                            }
                        } 
                    }                            
                }
            }
        }
    }
    
    /**
     * 
     * @param type $courseID
     * @param type $userID
     * @param type $childCourseName
     */
    public function remove_staff_from_meta_course_group_prefs($courseID, $userID)
    {
        $groups = $this->get_all_groups($courseID);
        $appendName = $this->get_meta_course_group_name_string($courseID);
        foreach($groups AS $courseGroup)
        {
            $courseGroupName = $courseGroup->name;
            $metaCourseGroupName = $appendName.$courseGroupName;
            $courseGroup->metaname = $metaCourseGroupName;
        }
        //are they on any of the meta courses?
        $metaCourses = bcgt_get_meta_courses($courseID);
        if($metaCourses)
        {
            foreach($metaCourses AS $metaCourse)
            {
                //we cant just remove the student from all of the groups on 
                //this course. 
                
                //we can only remove them from the groups
                //that correspond to the child course. 
                //so we need to find all of he groups, on this course, 
                //that have the same name
                foreach($groups AS $courseGroup)
                {
                    $metaGroup = $this->get_group_on_course($courseID, $metaCourseGroupName);
                    if($metaGroup)
                    {
                        $this->remove_user_bcgt_group_pref($metaGroup->id, $userID);
                    }
                }
                
            }
        }
    }
    
    public function process_import_csv($csvFile, $process = false)
    {
        //this will create an object of:        
        $usersFound = array();
        $coursesFound = array();
        
        $usersNotFound = array();
        $coursesNotFound = array();
        $errored = false;
        
        $coursesArray = array();
        //courses
            //groups
                //students or staff 
        global $DB;
        $count = 1;
        $CSV = fopen($csvFile, 'r');
        while(($groupCSV = fgetcsv($CSV)) !== false) {
            if($count != 1)
            {
                $username = $groupCSV[0];
                $courseName = $groupCSV[1];
                $groupName = $groupCSV[2];
                
                $user = null;
                //first find the user
                if(array_key_exists($username, $usersFound))
                {
                    $user = $usersFound[$username];
                }
                else
                {
                    $sql = "SELECT * FROM {user} WHERE username = ?";
                    $user = $DB->get_record_sql($sql, array($username));
                    if($user)
                    {
                        $usersFound[$username] = $user;
                    }
                    else
                    {
                        $usersNotFound[$username] = $username;
                        $errored = true;
                    }
                }
                //then find the course
                if(array_key_exists($courseName, $coursesFound))
                {
                    $course = $coursesFound[$courseName];
                }
                else
                {
                    $sql = "SELECT * FROM {course} WHERE shortname = ?";
                    $course = $DB->get_record_sql($sql, array($courseName));
                    if($course)
                    {
                        $coursesFound[$courseName] = $course;
                    }
                    else
                    {
                        $coursesNotFound[$courseName] = $courseName;
                        $errored = true;
                    }
                }
                
                if($course && $user)
                {
                    //then lets create the object
                    //have we found this course before?
                    if(array_key_exists($course->id, $coursesArray))
                    {
                        //the courseID key corresponds to an array of groups;
                        $groupsArray = $coursesArray[$course->id];
                    }
                    else
                    {
                        $groupsArray = array();
                    }
                    
                    //have we found this group before?
                    if(array_key_exists($groupName, $groupsArray))
                    {
                        //the groupName key corresponds to an array of students;
                        $usersArray = $groupsArray[$groupName];
                    }
                    else
                    {
                        $usersArray = array();
                    }    
                    
                    $usersArray[$user->id] = $user;
                    $groupsArray[$groupName] = $usersArray;
                    $coursesArray[$course->id] = $groupsArray;
                }
            }
            $count++;
        }
        
        
        switch($this->importMethod)
        {
            case 'c':
                
                //the will create the groups etc on the courses
                //checks if we are creating the groupings
                //checks if we are matching on the meta course
                //checks if we are creating a group/grouping for the 
                $summary = $this->process_course_groups($coursesArray);
                break;
            case 't':
                
                //adds the preference into the block_bcgt_user_grouping table.
                //used to determine the preferenace of what groups a staff member is associoated with. 
                $summary = $this->process_user_pref_groups($coursesArray);
                break;
            default:
                break;
        }
        
        $summary->usersNotFound = $usersNotFound;
        $summary->coursesNotFound = $coursesNotFound;
        
        $this->summary = $summary;
        $this->success = !$errored;
    }
    
    protected function process_user_pref_groups($coursesArray)
    {
        $summary = new stdClass();
        $userNotOnCourse = array(); 
        $groupsNotFound = array();
        $successCount = 0;
        foreach($coursesArray AS $courseID => $groupsArray)
        {
            //we know the course must exist. 
            foreach($groupsArray AS $groupName => $usersArray)
            {
                $courseGroup = $this->get_group_on_course($courseID, $groupName);
                if($courseGroup)
                {
                    foreach($usersArray AS $user)
                    {
                        //is the user on the course?
                        if(bcgt_is_user_on_course_user($user->id, $courseID))
                        {
                            if(bcgt_get_course_quals($courseID))
                            {
                                //does the pref exist?
                                if(!$this->get_user_bcgt_group_prefs(-1, $courseGroup->id, $courseID, $user->id))
                                {
                                    $this->add_user_bcgt_group_pref($courseGroup->id, $courseID, $user->id);
                                    $successCount++;
                                }
                            }
                            
                        }
                        //are they on any of the meta courses?
                        $metaCourses = bcgt_get_meta_courses($courseID);
                        if($metaCourses)
                        {
                            foreach($metaCourses AS $metaCourse)
                            {
                                //does the group exist on the meta course?
                                //is the user enrolled on here?
                                if(bcgt_is_user_on_course_user($user->id, $metaCourse->id))
                                {
                                    if(bcgt_get_course_quals($metaCourse->id))
                                    {
                                        $groupNameAppend = $this->get_meta_course_group_name_string($courseID);
                                        $courseGroup = $this->get_group_on_course($metaCourse->id, $groupNameAppend.$groupName);
                                        if($courseGroup)
                                        {
                                            //does the pref exist?
                                            if(!$this->get_user_bcgt_group_prefs(-1, $courseGroup->id, $metaCourse->id, $user->id))
                                            {
                                                $this->add_user_bcgt_group_pref($courseGroup->id, $metaCourse->id, $user->id);
                                                $successCount++;
                                            }
                                        }
                                    }
                                    
                                }
                            }
                        }
                    }
                }
                else
                {
                    if(array_key_exists($courseID, $groupsNotFound))
                    {
                        $courseObj = $groupsNotFound[$courseID];
                    }
                    else
                    {
                        $courseObj = new stdClass();
                        $courseObj->groups = array();
                    }
                    $courseObj->groups[$groupName] = $groupName;
                    $groupsNotFound[$courseID] = $courseObj;
                }
            }
        }
        $summary->groupsNotFound = $groupsNotFound;
        $summary->successCount = $successCount;
        return $summary;
    }
    
    protected function process_course_groups($coursesArray)
    {
        global $DB;
        $grouping = new Grouping();
        $userNotOnCourse = array();
        $successCount = 0;
        foreach($coursesArray AS $courseID => $groupsArray)
        {
            //we know the course must exist. 
            //are we creating/checking for the group/grouping for the entire course?
            $groupCourseName = '';
            $newFullGroupID = -1;
            if($this->groupForCourse)
            {
                $column = get_config('bcgt','fullcoursegroupname');
                $courseDB = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($courseID));
                $groupCourseName = $courseDB->$column;
                $newFullGroupID = $this->check_and_create_group($courseDB->id, $groupCourseName);                
                //ae we creating groupings as well?
                if($this->createGroupings)
                {
                    $grouping->create_grouping_and_add_group($courseID, $groupCourseName, $newFullGroupID);
                }  
            }
            
            //Process the groups and the users
            $summary = $this->process_groups_and_users($groupsArray, $courseID, $newFullGroupID);
            $users = $summary->usersNotOnCourse;
            $sCount = $summary->successCount;
            $userNotOnCourse = array_merge($userNotOnCourse, $users);
            $successCount = $successCount + $sCount;
            
            if($this->matchMeta)
            {
                //then we are now finding all of the meta courses and matching the groups etc
                $this->process_meta_courses_for_child($courseID, $groupCourseName, $groupsArray);
            }//end if match meta
        }//end each courses
        $summary = new stdClass();
        $summary->usersNotOnCourse = $userNotOnCourse;
        $summary->successCount = $successCount;
        return $summary;
    }
     
    /**
     * 
     * @param type $childCourseID
     * @param type $groupCourseName
     * @param type $groupsArray
     * @param type $userID
     */
    public function process_meta_courses_for_child($childCourseID, $groupCourseName, $groupsArray = null, $userID = -1)
    {
        global $CFG;
        $grouping = new Grouping();
        //first find all of the meta courses:
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        $metaCourses = bcgt_get_meta_courses($childCourseID);
        if($metaCourses)
        {
            $groupNameAppend = $this->get_meta_course_group_name_string($childCourseID);
            foreach($metaCourses AS $metaCourse)
            {
                //are we creating a group for the whole course?
                if($this->groupForCourse)
                {
                    //we need to see if it exists?
                    //$groupCourseName calculated above
                    $newFullGroupID = $this->check_and_create_group($metaCourse->id, $groupCourseName);
                    //ae we creating groupings as well?
                    if($this->createGroupings)
                    {
                        $grouping->create_grouping_and_add_group($metaCourse->id, $groupCourseName, $newFullGroupID);
                    } 

                    if(!$groupsArray)
                    {
                        $groupsArray = $this->get_groups_array($childCourseID, $userID = -1);
                    }
                    
                    
                    //Process the groups and the users
                    $this->process_groups_and_users($groupsArray, $metaCourse->id, $newFullGroupID, $groupNameAppend, $groupCourseName);
                    
                }
            }//end for each meta
        }//end if found metas
    }
    
    public function add_user_to_course($username, $courseShortName, $groupName = null)
    {
        global $DB;
        $user = $DB->get_record_sql('SELECT * FROM {user} WHERE username = ?', array($username));
        if($user)
        {
            $course = $DB->get_record_sql('SELECT * FROM {course} WHERE shortname = ?', array($courseShortName));
            if($course)
            {
                //1.) we want to add/check the full course group
                //check if full course group exists
                $group = $this->get_group_on_course($course->id, $courseShortName);
                if(!$group)
                {
                    $groupID = $this->create_group_on_course($course->id, $courseShortName);
                }
                else
                {
                    $groupID = $group->id;
                }
                
                $grouping = new Grouping();
                if(!$courseGrouping = $grouping->get_grouping_on_course($course->id, $courseShortName))
                {
                    $groupingID = $grouping->create_grouping_on_course($course->id, $courseShortName);
                }
                else
                {
                    $groupingID = $courseGrouping->id;
                }
                
                
                if(!$this->get_user_in_group($user->id, $groupID))
                {
                    $this->add_user_to_group($user->id, $groupID);
                }
                
                if(!$grouping->get_group_in_grouping($groupID, $groupingID))
                {
                    $grouping->add_group_to_grouping($groupID, $groupingID);
                }
                
                //2.) is the group empty? if not check/create and add user
                if($groupName && $groupName != '')
                {
                    $group = $this->get_group_on_course($course->id, $groupName);
                    if(!$group)
                    {
                        $groupID = $this->create_group_on_course($course->id, $groupName);
                    }
                    else
                    {
                        $groupID = $group->id;
                    }

                    if(!$this->get_user_in_group($user->id, $groupID))
                    {
                        $this->add_user_to_group($user->id, $groupID); 
                    }
                    
                    //do one for the grouping
                    if(!$courseGrouping = $grouping->get_grouping_on_course($course->id, $groupName))
                    {
                        $groupingID = $grouping->create_grouping_on_course($course->id, $groupName);
                    }
                    else
                    {
                        $groupingID = $courseGrouping->id;
                    }
                    
                    if(!$grouping->get_group_in_grouping($groupID, $groupingID))
                    {
                        $grouping->add_group_to_grouping($groupID, $groupingID);
                    }
                }
                //3.) do the meta courses
                $this->groupForCourse = true;
                $this->createGroupings = true;
                $this->process_meta_courses_for_child($course->id, $courseShortName, null, $user->id);
            }
        }
    }
    
    public function remove_user_from_course($username, $courseShortName, $groupName = null)
    {
        $grouping = new Grouping();
        //this needs
        global $DB;
        $user = $DB->get_record_sql('SELECT * FROM {user} WHERE username = ?', array($username));
        if($user)
        {
            $course = $DB->get_record_sql('SELECT * FROM {course} WHERE shortname = ?', array($courseShortName));
            if($course)
            {
                if(!$groupName)
                {
                    $this->remove_user_from_all_groups_on_course($course->id, $user->id);
                }
                else
                {
                    $group = $this->get_group_on_course($course->id, $groupName);
                    if($group)
                    {
                        $this->remove_user_from_group($user->id, $group->id);
                    }
                }
                //then find any empty groups
                $emptyGroups = $this->get_all_empty_groups($course->id);
                if($emptyGroups)
                {
                    foreach($emptyGroups AS $group)
                    {
                        $this->delete_group($group->id);
                    }
                }
                
                $emptyGroupings = $grouping->get_all_empty_groupings($course->id);
                if($emptyGroupings)
                {
                    foreach($emptyGroupings AS $eGrouping)
                    {
                        $grouping->delete_grouping($eGrouping->id);
                    }
                }
                //remove them from the main course group
                $this->remove_user_from_meta_courses_for_child($course->id, $user->id, $groupName);
            }
        }
    }
    
    public function remove_user_from_meta_courses_for_child($childCourseID, $userID, $groupName = null)
    {
        $grouping = new Grouping();
        //first find all of the meta courses:
        $metaCourses = bcgt_get_meta_courses($childCourseID);
        if($metaCourses)
        {
            foreach($metaCourses AS $metaCourse)
            {
                if(!$groupName)
                {
                    //then remove from everything
                    $this->remove_user_from_all_groups_on_course($metaCourse->id, $userID);
                }
                else
                {
                    $groupNameAppend = $this->get_meta_course_group_name_string($childCourseID);
                    $foundGroup = $this->get_group_on_course($metaCourse->id, $groupNameAppend.$groupName);
                    if($foundGroup)
                    {
                        $this->remove_user_from_group($userID, $foundGroup->id);
                    }
                }
                //then find any empty groups
                $emptyGroups = $this->get_all_empty_groups($metaCourse->id);
                if($emptyGroups)
                {
                    foreach($emptyGroups AS $group)
                    {
                        $this->delete_group($group->id);
                    }
                }
                
                $emptyGroupings = $grouping->get_all_empty_groupings($metaCourse->id);
                if($emptyGroupings)
                {
                    foreach($emptyGroupings AS $eGrouping)
                    {
                        $grouping->delete_grouping($eGrouping->id);
                    }
                }
                
            }
        }
    }
    
    /**
     * 
     * @global type $DB
     * @param type $username
     * @param type $courseShortName
     */
    public function add_to_course_full_group($username, $courseShortName)
    {
        global $DB;
        $user = $DB->get_record_sql('SELECT * FROM {user} WHERE username = ?', array($username));
        if($user)
        {
            $course = $DB->get_record_sql('SELECT * FROM {course} WHERE shortname = ?', array($courseShortName));
            if($course)
            {
                //check if full course group exists
                $this->check_create_add_user_group($course->id, $courseShortName, $user->id);
            }
        }
    }
    
    /**
     * 
     * @param type $courseID
     * @param type $groupName
     * @param type $userID
     */
    public function check_create_add_user_group($courseID, $groupName, $userID)
    {
        $group = $this->get_group_on_course($courseID, $groupName);
        if(!$group)
        {
            $groupID = $this->create_group_on_course($courseID, $groupName);
        }
        else
        {
            $groupID = $group->id;
        }

        if(!$this->get_user_in_group($userID, $groupID))
        {
            $this->add_user_to_group($userID, $groupID);
        }
        
        return $groupID;
    }
    
    /**
     * This builds an array of the groups that are on the course
     * for every group it build an array of the users that are on that group. 
     * @param type $courseID
     */
    protected function get_groups_array($courseID, $userID = -1)
    {
        $retval = array();
        $groups = $this->get_all_groups($courseID, $userID = -1);
        if($groups)
        {
            foreach($groups AS $group)
            {
                $students = $this->get_users_in_group($group->id);
                $retval[$group->name] = $students;
            }
        }
        return $retval;
    }
    
    /**
     * This will loop over the groups array and 
     * process the groups on the course and the users in the groups
     * @param type $groupsArray
     */
    protected function process_groups_and_users($groupsArray, $courseID, $additionalGroupID = -1, $appendGroupNameTo = '', $groupCourseName = '')
    {
        $summary = new stdClass();
        $userNotOnCourse = array();
        $successCount = 0;
        $grouping = new Grouping();
        foreach($groupsArray AS $groupName => $usersArray)
        {
            if($groupCourseName != '' && $groupCourseName == $groupName)
            {
                //if the groupName we have come accross is the same 
                //as the one for the entire course, then skip it.
                continue;
            }
            //for meta courses when creating groups for the child
            //courses we want to append the name/idnumber of the child
            //course to the groupname
            $groupName = $appendGroupNameTo . $groupName;
            //does the Group Already exists on the course?
            $groupID = $this->check_and_create_group($courseID, $groupName);
            if($this->createGroupings)
            {
                //are we creating groupings? do we need to check if it already exists?
                $grouping->create_grouping_and_add_group($courseID, $groupName, $groupID);
            }

            foreach($usersArray AS $user)
            {
                //is the user enrolled on the course?
                if(bcgt_is_user_on_course_user($user->id, $courseID))
                {
                    //then we can add them to the group
                    //is the user already on the group?
                    if(!$this->get_user_in_group($user->id, $groupID))
                    {
                        $this->add_user_to_group($user->id, $groupID);
                        $successCount++;
                    }
                    //below used to add to the full course group.
                    if($additionalGroupID != -1)
                    {
                        if(!$this->get_user_in_group($user->id, $additionalGroupID))
                        {
                            $this->add_user_to_group($user->id, $additionalGroupID);
                        }
                    }
                }
                else
                {
                    if(array_key_exists($user->id, $userNotOnCourse))
                    {
                        $userObj = $userNotOnCourse[$user->id];
                    }
                    else
                    {
                        $userObj = new stdClass();
                        $userObj->courses = array();
                        $userObj->user = $user;
                    }
                    $userObj->courses[$courseID] = $courseID;
                    $userNotOnCourse[$user->id] = $userObj;
                }
            }
        }//end each groups
        $summary->usersNotOnCourse = $userNotOnCourse;
        $summary->successCount = $successCount;
        return $summary;
    }


    /**
     * Checks if the group exists
     * if it doesnt, create it
     * @param type $courseID
     * @param type $groupName
     * @return type
     */
    protected function check_and_create_group($courseID, $groupName)
    {
        $courseGroup = $this->get_group_on_course($courseID, $groupName);
        if(!$courseGroup)
        {
           $groupID = $this->create_group_on_course($courseID, $groupName); 
        }
        else
        {
            $groupID = $courseGroup->id;
        }
        
        return $groupID;
    }
    
    public function get_file_names()
    {
        switch($this->importMethod)
        {
            case "c":
                return 'coursegroups.csv';
                break;
            case "t":
                return 'staffgroups.csv';
                break;
            default:
                return '';
                break;
        }
        
    }
    
    private function get_header($no)
    {
        switch($no)
        {
            case 1:
                //this is 'course'
                return array('username','course','groupname');
                break;
            case 2:
                //this is 'staf'
                return array('username','course','groupingname');
                break;
            default:
                return array();
                break;

        }
    }
    
    public function has_multiple()
    {
        return false;
    }
    
    public function get_examples()
    {
        $retval = '';
        $retval .= '28937,X101:BTEC Course 1, Group 1 <br />';
        $retval .= '234423smith,testCourse, C1 <br />';
        $retval .= 'rjsmith,C101, Group Name A <br />';
        return $retval;
    }
    
    /**
     * This uss the config metacoursegroupnames
     * It finds the column for the course
     * Appends this to any strings the institute wants and then
     * appends this to the name
     * @global type $DB
     * @param type $courseID
     * @param type $name
     * @return type
     */
    public function calculate_group_name($courseID, $name)
    {
        global $DB;
        $groupName = '';
        $groupName .= $this->get_meta_course_group_name_string($courseID);
        $groupName = $groupName.$name;
        return $groupName;
    }
    
    public function get_meta_course_group_name_string($courseID)
    {
        global $DB;
        $groupName = '';
        $groupNameFormat = get_config('bcgt', 'metacoursegroupnames');
        if($groupNameFormat)
        {
            //this will be in the form of
            //[columnname]{somedata}[groupname]
            //e.g.[idnumber]{_}[groupname]
            
            //split the string between the first set of []
            $first = strpos($groupNameFormat, "[");
            $second = strpos($groupNameFormat, "]");
            $column = substr($groupNameFormat, $first+1,(($second - 1) - $first));
            $result = null;
            if($column != '')
            {
                try
                {
                    $result = $DB->get_record_sql("SELECT {$column} FROM {course} WHERE id = ?", array($courseID));
                }
                catch(Exception $e)
                {
                    
                }
            }

            //split the string between the set of {}
            $first = strpos($groupNameFormat, "{");
            $second = strpos($groupNameFormat, "}");
            $string = substr($groupNameFormat, $first+1,(($second - 1) - $first)); 
            $groupName = '';
            if($result)
            {
                $groupName = $groupName.$result->$column;
            }
            $groupName .= $string;
        }
        return $groupName;
    }
    
    /**
     * Gets all of the groups a students is on in a course
     * @param type $courseID
     * @param type $userID
     */
    public function get_user_groups_on_course($courseID, $userID)
    {
        global $DB;
        $sql = "SELECT g.* FROM {groups} g 
            JOIN {groups_members} members ON members.groupid = g.id 
            WHERE members.userid = ? AND g.courseid = ?";
        $params = array();
        $params[] = $userID;
        $params[] = $courseID;
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * This removes the user from all groups on the course
     * This will delete it from the database
     * @global type $DB
     * @param type $courseID
     * @param type $userID
     */
    public function remove_user_from_all_groups_on_course($courseID, $userID)
    {
        global $DB;
        $groups = $this->get_user_groups_on_course($courseID, $userID);
        if($groups)
        {
            foreach($groups AS $group)
            {
                $DB->delete_records('groups_members', array("groupid"=>$group->id, "userid"=>$userID));
            }
        }
        
    }
    
    /**
     * This removes the user from all groups on the course
     * This will delete it from the database
     * @global type $DB
     * @param type $courseID
     * @param type $userID
     */
    public function remove_user_bcgt_pref_from_all_groups_on_course($courseID, $userID)
    {
        global $DB;
//        mtrace("Deleting Group");
        $DB->delete_records('block_bcgt_user_grouping', array("courseid"=>$courseID, "userid"=>$userID));
    }
    
    /**
     * This removes the user from the group
     * @global type $DB
     * @param type $userID
     * @param type $groupID
     */
    public function remove_user_from_group($userID, $groupID)
    {
        global $DB;
        $DB->delete_records('groups_members', array("groupid"=>$groupID, "userid"=>$userID));
    }
    
    /**
     * Removs all members from a group
     * @global type $DB
     * @param type $groupID
     */
    public function remove_all_users_from_group($groupID = -1)
    {
        global $DB;
        if($groupID == -1)
        {
            $groupID = $this->id;
        }
        $DB->delete_records('groups_members', array("groupid"=>$groupID));
    }
    
    /**
     * Adds the user to the group
     * @global type $DB
     * @param type $userID
     * @param type $groupID
     */
    public function add_user_to_group($userID, $groupID)
    {
        global $DB;
        $record = new stdClass();
        $record->userid = $userID;
        $record->groupid = $groupID;
        $record->timeadded = time();
        $DB->insert_record('groups_members', $record);
    }
    
    /**
     * Counts the members of a group
     * @global type $DB
     * @param type $groupID
     * @return type
     */
    public function count_group_members($groupID)
    {
        global $DB;
        return $DB->count_records_sql("SELECT COUNT(*) FROM {groups_members} WHERE groupid = ?", array($groupID));
    }        
    
    /**
     * Deletes the group.
     * First it removes all members
     * Then it deletes the group
     * @global type $DB
     * @param type $groupID
     */
    public function delete_group($groupID = -1)
    {
        global $DB;
        if($groupID == -1)
        {
            $groupID = $this->id;
        }
        $this->remove_all_users_from_group($groupID);
        $grouping = new Grouping();
        $grouping->remove_group_from_groupings($groupID);
        $DB->delete_records('groups', array("id"=>$groupID));
    }
    
    /**
     * Gets the user in the group record
     * Used to check if the user is already in the group
     * @global type $DB
     * @param type $userID
     * @param type $groupID
     * @return type
     */
    public function get_user_in_group($userID, $groupID)
    {
        global $DB;
        $sql = "SELECT * FROM {groups_members} members WHERE userid = ? AND groupid = ?";
        $params = array();
        $params[] = $userID;
        $params[] = $groupID;
        return $DB->get_record_sql($sql, $params);
    }
    
    /**
     * Gets the user in the group record
     * Used to check if the user is already in the group
     * @global type $DB
     * @param type $userID
     * @param type $groupID
     * @return type
     */
    public function get_user_in_group_by_name($userID, $groupName)
    {
        global $DB;
        $sql = "SELECT members.* FROM {groups_members} members 
            JOIN {groups} g ON g.id = members.groupid WHERE userid = ? AND g.name = ?";
        $params = array();
        $params[] = $userID;
        $params[] = $groupName;
        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Gets the group on a course
     * Used to check if a group exists
     * @param type $courseID
     * @param type $groupName
     */
    public function get_group_on_course($courseID, $groupName)
    {
        global $DB;
        $sql = "SELECT * FROM {groups} WHERE courseid = ? AND name = ?";
        $params = array();
        $params[] = $courseID;
        $params[] = $groupName;
        return $DB->get_record_sql($sql, $params);
    }
    
    /**
     * ◦finds all groups by name. 
        ◦Used for a like %childcoursename%
        ◦Used for meta courses and child courses
     * @global type $DB
     * @param type $courseID
     * @param type $groupName
     */
    public function get_all_groups_by_name($courseID, $groupName)
    {
        global $DB;
        $sql = "SELECT * FROM {groups} g WHERE courseid = ? AND name LIKE ?";
        $params = array();
        $params[] = $courseID;
        $params[] = '%'.$groupName.'%';
        return $DB->get_records($sql, $params);
    }
    
    /**
     * Gets the users in a group
     * If Qualid is set, it only gets the users that are on this qual
     * on this group, 
     * @global type $DB
     * @param type $groupID
     * @param type $qualID
     * @return type
     */
    public function get_users_in_group($groupID, $qualID = null)
    {
        global $DB;
        $sql = "SELECT user.* FROM {user} user 
            JOIN {groups_members} members ON members.userid = user.id ";
        if($qualID)
        {
            $sql .= " JOIN {block_bcgt_user_qual} userqual ON userqual.userid = user.id";
        }
        $sql .= " WHERE members.groupid = ?";
        $params = array();
        $params[] = $groupID;
        if($qualID)
        {
            $sql .= " AND userqual.bcgtqualificationid = ?";
            $params[] = $qualID;
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Gets all of the groups on a course. 
     * @global type $DB
     * @param type $courseID
     * @return type
     */
    public function get_all_groups($courseID, $userID = -1)
    {
        global $DB;
        $sql = "SELECT * FROM {groups} WHERE courseid = ?";
        $params = array($courseID);
        if($userID != -1)
        {
            $sql .= " AND userid = ?";
            $params[] = $userID;
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Gets all of the groups that have no members. 
     * @global type $DB
     * @param type $courseID
     */
    public function get_all_empty_groups($courseID)
    {
        global $DB;
        $sql = "SELECT * FROM {groups}
            WHERE courseid = ? AND id NOT IN 
            (SELECT g.id FROM {groups} g
            JOIN {groups_members} members ON members.groupid = g.id)";
        $params = array();
        $params[] = $courseID;
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Adds the user to he group in block_bcgt_user_grouping
     * @global type $DB
     * @param type $groupID
     * @param type $courseID
     * @param type $userID
     */
    public function add_user_bcgt_group_pref($groupingID, $courseID, $userID)
    {
        global $DB;
        $exists = $this->get_user_bcgt_group_prefs(-1, $groupingID, $courseID, $userID);
        if(!$exists)
        {
            $record = new stdClass();
            $record->groupingid = $groupingID;
            $record->courseid = $courseID;
            $record->userid = $userID;
            $DB->insert_record('block_bcgt_user_grouping', $record);
        }
    }
    
    /**
     * removes the record from the block_bcgt_user_grouping table for
     * the user and group.
     * @global type $DB
     * @param type $groupID
     * @param type $userID
     */
    public function remove_user_bcgt_group_pref($groupingID, $userID)
    {
        global $DB;
        $DB->delete_records('block_bcgt_user_grouping', array("groupingid"=>$groupingID, "userid"=>$userID));
    }
    
    /**
     * removes the record from the block_bcgt_user_grouping table for
     * the course and group.
     * @global type $DB
     * @param type $groupID
     * @param type $userID
     */
    public function remove_bcgt_group_course_pref($courseID, $groupingID)
    {
        global $DB;
        $DB->delete_records('block_bcgt_user_grouping', array("groupingid"=>$groupingID, "courseid"=>$courseID));
    }
    
    /**
     * Finds all empty groups and groupings
     * //removes user saved preference
     * //deletes the groups and groupings. 
     * @param type $courseID
     */
    public function clear_empty_groups_and_groupings_course($courseID)
    {
        $grouping = new Grouping();
        $emptyGroups = $this->get_all_empty_groups($courseID);
        if($emptyGroups)
        {
            foreach($emptyGroups AS $emptyGroup)
            {
                $this->delete_group($emptyGroup->id);
                $this->remove_bcgt_group_course_pref($courseID, $emptyGroup->id);
            }
        }
        
        $emptyGroupings = $grouping->get_all_empty_groupings($courseID);
        if($emptyGroupings)
        {
            foreach($emptyGroupings AS $emptyGrouping)
            {
                $grouping->delete_grouping($emptyGrouping->id);
            }
        }
    }
    
    /**
     * 
     * @param type $groupID
     */
    public function count_users_in_group($groupID)
    {
        global $DB;
        $sql = "SELECT count(userid) FROM {groups_members} WHERE groupid = ?";
        return $DB->count_records_sql($sql, array($groupID));
    }
    
    
    /**
     * Gets the group pref records for the paramters passed in. 
     * @global type $DB
     * @param type $id
     * @param type $groupID
     * @param type $courseID
     * @param type $userID
     * @return type]
     */
    public function get_user_bcgt_group_prefs($id = -1, $groupID = -1, $courseID = -1, $userID = -1)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_grouping}";
        if($id != -1 || $groupID != -1 || $courseID != -1 || $userID != -1)
        {
            $sql .= " WHERE";
        }
        $and = false;
        $params = array();
        if($id != -1)
        {
            $sql .= " id = ?";
            $params[] = $id;
            $and = true;
        }
        if($groupID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $sql .= " groupingid = ?";
            $params[] = $groupID;
            $and = true;
        }
        if($courseID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $sql .= " courseid = ?";
            $params[] = $courseID;
            $and = true;
        }
        if($userID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $sql .= " userid = ?";
            $params[] = $userID;
            $and = true;
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Pass in the courseID and the GroupName and it will create the group 
     * in the database
     * @global type $DB
     * @param type $courseID
     * @param type $groupName
     */
    public function create_group_on_course($courseID, $groupName)
    {
        global $DB;
        $group = new stdClass();
        $group->courseid = $courseID;
        $group->name = $groupName;
        return $DB->insert_record('groups', $group);
    }
    
    /**
     * 
     */
    public function get_user_possible_groups($userID, $courseID = -1, $qualID = -1)
    {
        global $DB;
        
        //needs to return: course, groups, whether they are selected
        $sql = "SELECT distinct(g.id), g.name, course.id as courseid, course.shortname, ug.id AS pref 
            FROM {course} course
            JOIN {context} context ON context.instanceid = course.id 
            JOIN {role_assignments} ra ON ra.contextid = context.id 
            JOIN {groupings} g ON g.courseid = course.id 
            LEFT OUTER JOIN {block_bcgt_user_grouping} ug ON ug.userid = ra.userid AND ug.groupid = g.id";
            $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = course.id ";
        $sql .= " WHERE ra.userid = ?";
        $params = array();
        $params[] = $userID;
        if($courseID != -1)
        {
            $sql .= " AND course.id = ?";
            $params[] = $courseID;
        }
        if($qualID != -1)
        {
            $sql .= " AND coursequal.bcgtqualificationid = ?";
            $params[] = $qualID;
        }
        $sql .= ' ORDER BY g.name ASC';
        return $DB->get_records_sql($sql, $params);
    }
    
    
    /**
     * This duplicates groups, groupings, users in groups, and groups in groupings
     * from the original courseid to the new courseid
     * @param type $originalCourseID
     * @param type $newCourseID
     */
    public function duplicate_groups_and_groupings_between_courses($originalCourseID, $newCourseID, $appendName = '')
    {
        $grouping = new Grouping();
        //does new course have all groups from the original course?
        //get groups on original course
//        mtrace("Doing Groups");
        $originalGroups = $this->get_all_groups($originalCourseID);
        if($originalGroups)
        {
//            mtrace("Found original Group");
            foreach($originalGroups AS $originalGroup)
            {
                //do they exist on new course?
//                mtrace("Finding $newCourseID, $appendName.$originalGroup->name");
                $originalGroupOnNew = $this->get_group_on_course($newCourseID, $appendName.$originalGroup->name);
                if($originalGroupOnNew)
                {
//                    mtrace("found original Group on new course");
                    //if yes
                    //do they have all of the same enrolments?
                    $newGroupID = $originalGroupOnNew->id;
                }
                else
                {
//                    mtrace("creating original Group on new course");
                    //if not
                    //create and add correct students
                    $newGroupID = $this->create_group_on_course($newCourseID, $appendName.$originalGroup->name);

                }

                $groupEnrolments = $this->get_users_in_group($originalGroup->id);
                if($groupEnrolments)
                {
//                    mtrace("Found original enrolments in group");
                    foreach($groupEnrolments AS $groupEnrolment)
                    {
                        if(!$this->get_user_in_group($groupEnrolment->id, $newGroupID))
                        {
//                            mtrace("Adding new enrolments in group");
                            $this->add_user_to_group($groupEnrolment->id, $newGroupID);
                        }
                        
                    }
                }
            }
        }

//        mtrace("Doing Groupings");
        //get all of the groupings on the original course
        $originalGroupings = $grouping->get_all_groupings($originalCourseID);
        if($originalGroupings)
        {
//            mtrace("FOUND original Groupings");
            //for each child grouping
            foreach($originalGroupings AS $originalGrouping)
            {
                //do they exist on the new course? must be done by name
                $originalGroupingOnNew = $grouping->get_grouping_on_course($newCourseID, $originalGrouping->name);
                if($originalGroupingOnNew)
                {
                    $newGroupingID = $originalGroupingOnNew->id;
                }
                else
                {
                    //if not
                    //create and add correct students
                    $newGroupingID = $grouping->create_grouping_on_course($newCourseID, $originalGrouping->name);
                }
//                mtrace("New GroupingID is $newGroupingID");
                $groupingsGroups = $grouping->get_groups_in_grouping($originalGrouping->id);
                if($groupingsGroups)
                {
                    $count = 0;
//                    mtrace("Groups exist in Grouping");
//                    var_dump($groupingsGroups);
                    foreach($groupingsGroups AS $groupingsGroup)
                    {
                        $count++;
//                        mtrace("$count");
                        //need to search by name
                        if(!$grouping->get_group_in_grouping_by_group_name($groupingsGroup->name, $newGroupingID))
                        {
//                            mtrace("Group doesnt exist in grouping in new course.");
                            //if the actual group exists on the course then add the group to the grouping
                            if($newCourseGroup = $this->get_group_on_course($newCourseID, $appendName.$groupingsGroup->name))
                            {
//                                mtrace("Adding to Grouping");
                                $grouping->add_group_to_grouping($newCourseGroup->id, $newGroupingID);
                            }
                        }
                    }
//                    mtrace("Ended for each of groups in original grouping");
                }
            }
//            mtrace("Ended For each of original groupings");
        }
//        mtrace("Done duplicate groups between courses");
    }
        
    /**
     * Saves the Group to the database. 
     * It checks if the courseid and name is set
     * Checks if the database is is set, if it is, updates
     * if it isnt then it updats. 
     * @global type $DB
     * @return type
     */
    public function save_group()
    {
        global $DB;
        if($this->id == -1)
        {
            if(!isset($this->courseid) || !isset($this->name))
            {
                return -1;
            }
            $params = $this->get_params();
            //we are inserting a new one
            $DB->insert_record('groups', $params);
        }
        else
        {
            //we are updating one
            if(!isset($this->courseid) || !isset($this->name))
            {
                $this->load_group($this->id);
            }
            $params = $this->get_params();
            $DB->update_record('groups', $params);
        }
    }
    
    /**
     * Gets the params from the object and passes it bak as a new object. 
     * @return \stdClass
     */
    private function get_params()
    {
        $params = new stdClass();
        $params->courseid = $this->courseid;
        $params->name = $this->name;
        return $params;
    }
    
    /**
     * Gets the params from the object passed in and puts them onto 
     * the Group objectl. 
     * @param type $params
     */
    private function extract_params($params)
    {        
        $this->courseid = $params->courseid;
        $this->name = $params->name;
    }
    
    /**
     * gets the group from the database and loads onto the obj
     * @global type $DB
     * @param type $id
     */
    private function load_group($id)
    {
        global $DB;
        $sql = "SELECT * FROM {groups} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($id));
        if($record)
        {
            $this->extract_params($record);
        }
    }
    
    
    
}

?>
