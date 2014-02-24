<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Data
 *
 * @author mchaney
 */

//I AM FULLY AWARE THAT HAVING THESE SWITCHES IN IT IS NOT FULLY OBJECT ORIENTED!!
//THEY SHOULD BE inherited instancs of classes. in a workspace or something

class Data {
    //put your code here
    protected $action;
    
    protected $qualsToRemoveAllStudents;
    protected $usersToUnEnrol;
    public function Data($action)
    {
        $this->action = $action;
    }
    
    public function get_header()
    {
        return '<h2>'.get_string('datacleanse', 'block_bcgt').'</h2>';
    }
    
    public function get_tabs($courseID = -1)
    {
        global $CFG;
        //needs to check available capibilities
        $out = '<div class="tabs"><div class="tabtree">';
        $out .= '<ul class="tabrow0">';
        $out .= '<li>'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/data_cleanse.php?a=uq">'.
                '<span>'.get_string('unlinkedquals', 'block_bcgt').'</span></a></li>';
        $out .= '<li>'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/data_cleanse.php?a=us">'.
                '<span>'.get_string('unlinkedstudents', 'block_bcgt').'</span></a></li>';
        $out .= '</ul>';
        $out .= '</div></div>';
        
        return $out;
    }
    
    public function get_description()
    {
        $retval = '';
        switch($this->action)
        {
            case "uq":
                $retval .= get_string('unlinkqualdesc','block_bcgt');
                break;
            case "us":
                $retval .= get_string('unlinkstudentdesc', 'block_bcgt');
                break;
            default:
                $retval .= '';
                break;
        }
        return $retval;
    }
    
    public function display_data_check()
    {
        switch($this->action)
        {
            case "uq":
                return $this->get_unlinked_qual_summary();
                break;
            case "us":
                return $this->get_unlinked_student_summary();
                break;
            default:
                return "";
                break;
        }
    }
    
    public function display_final_summary()
    {
        return "";
    }
    
    public function run_cleanse()
    {
        set_time_limit(0);
        switch($this->action)
        {
            case "uq":
                $this->get_unlinked_qual_summary();
                $this->summary = $this->run_unlinked_quals();
                break;
            case "us":
                $this->get_unlinked_student_summary();
                $this->summary = $this->run_unlinked_students();
                break;
            default:
                $this->summary = "";
                break;
        }
    }
    
    /**
     * Finds the qualifications not on a course
     * //find the students on that qualification.
     * @global type $DB
     * @return string
     */
    protected function get_unlinked_qual_summary()
    {
        global $DB;
        $sql = "SELECT qual.id, level.id AS levelid, level.trackinglevel, type.id AS typeid, 
                type.type, subtype.id AS subtypeid, subtype.subtype, targetqual.id as bcgttargetqualid, family.family, 
                qual.name, qual.additionalname
                FROM {block_bcgt_qualification} qual
                JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
                JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid
                JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid
                JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid
                JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid
                WHERE qual.id NOT IN (SELECT bcgtqualificationid FROM {block_bcgt_course_qual}) ";
        $quals = $DB->get_records_sql($sql, array());
        if($quals)
        {
            $retval = '<table>';
            $retval .= '<thead><tr>';
            $retval .= '<th>'.get_string('qual', 'block_bcgt').'</th>';   
            $retval .= '<th>'.get_string('countofstudentsremoved', 'block_bcgt').'</th>';
            $retval .= '<th>'.get_string('countofstaffremoved', 'block_bcgt').'</th>';  
            $retval .= '</tr></thead><tbody>';
            $qualsToRemoveAllStudents = $quals;
            $qualsToRemove = false;
            foreach($quals AS $qual)
            {
                //now lets count the students that are on them
                $sql = "SELECT count(distinct(userid)) FROM {block_bcgt_user_qual} userqual 
                    JOIN {role} role ON role.id = userqual.roleid WHERE bcgtqualificationid = ? AND role.shortname = ?";
                $countStu = $DB->count_records_sql($sql, array($qual->id, 'student'));
                 $sql = "SELECT count(distinct(userid)) FROM {block_bcgt_user_qual} userqual 
                    JOIN {role} role ON role.id = userqual.roleid WHERE bcgtqualificationid = ? AND role.shortname != ?";
                $countStaff = $DB->count_records_sql($sql, array($qual->id, 'student'));
                if($countStu > 0 || $countStaff > 0)
                {
                    $retval .= '<tr>';
                    $retval .= '<td>'.bcgt_get_qualification_display_name($qual).'</td>';
                    $retval .= '<td>'.$countStu.'</td>';
                    $retval .= '<td>'.$countStaff.'</td>';
                    $retval .= '</tr>';
                    $qualsToRemove = true;
                }                
            }
            if(!$qualsToRemove)
            {
                $retval .= '<tr><td colspan="3">'.get_string('noredundantstudents','block_bcgt').'</td></tr>';
            }
            $retval .= '</tbody></table>';
        }
        $this->qualsToRemoveAllStudents = $qualsToRemoveAllStudents;
        return $retval;
    }
    
    protected function run_unlinked_quals()
    {
        global $DB;
        //get all of the quals. 
        //remove all enrolments for these quals. 
        foreach($this->qualsToRemoveAllStudents AS $qual)
        {
            $DB->delete_records('block_bcgt_user_qual', array("bcgtqualificationid" => $qual->id));
        }
    }
    
    protected function run_unlinked_students()
    {
        global $DB;
        //get all of the quals. 
        //remove all enrolments for these quals. 
        $studentRole = $DB->get_record_sql("SELECT * FROM {role} WHERE shortname = ?", array('student'));
        foreach($this->usersToUnEnrol AS $qualID=>$users)
        {
            foreach($users AS $user)
            {
                $DB->delete_records('block_bcgt_user_qual', 
                        array("bcgtqualificationid" => $qualID, "userid" => $user->id, "roleid" => $studentRole->id));
            }
            
        } 
    }
    
    protected function get_unlinked_student_summary()
    {
        global $DB;
        $sql = "SELECT distinct(qual.id), level.id AS levelid, level.trackinglevel, type.id AS typeid, 
                type.type, subtype.id AS subtypeid, subtype.subtype, targetqual.id as bcgttargetqualid, family.family, 
                qual.name, qual.additionalname
                FROM {block_bcgt_qualification} qual
                JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
                JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid
                JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid
                JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid
                JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid
                JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id";
        $quals = $DB->get_records_sql($sql, array());
        if($quals)
        {
            $retval = '<table>';
            $retval .= '<thead><tr>';
            $retval .= '<th>'.get_string('qual', 'block_bcgt').'</th>';   
            $retval .= '<th>'.get_string('countofstudentsremoved', 'block_bcgt').'</th>';
            $retval .= '</tr></thead><tbody>';
            $usersToUnEnrol = array();
            $usersToUnlink = false;
            foreach($quals AS $qual)
            {
                //now lets count the students that are on them
                
                //get the students that are on the qual
                $sql = "SELECT distinct(user.id), user.* FROM {user} user 
                    JOIN {block_bcgt_user_qual} userqual ON userqual.userid = user.id 
                    JOIN {role} role ON role.id = userqual.roleid
                    WHERE userqual.bcgtqualificationid = ? AND role.shortname = ?";
                $users = $DB->get_records_sql($sql, array($qual->id, 'student'));
                $usersArray = array();
                if($users)
                {
                    //get the courses that are on this qual
                    $sql = "SELECT coursequal.courseid as id FROM {block_bcgt_course_qual} coursequal
                        WHERE coursequal.bcgtqualificationid = ?";
                    $courses = $DB->get_records_sql($sql, array($qual->id));
                    if($courses)
                    {
                        //then we now want to check that the users are actually on one of the courses or not!
                        foreach($users AS $user)
                        {
                            $sql = "SELECT ra.id FROM {role_assignments} ra 
                                JOIN {context} con ON con.id = ra.contextid 
                                JOIN {course} course ON course.id = con.instanceid 
                                WHERE ra.userid = ? AND course.id IN (";
                            $count = 0;
                            $params = array($user->id);
                            foreach($courses AS $course)
                            {
                                $sql .= '?';
                                $count++;
                                if($count != count($courses))
                                {
                                    $sql .= ',';
                                }
                                $params[] = $course->id;
                            }
                            $sql .= ")";
                            $roleAssignments = $DB->get_records_sql($sql, $params);
                            if(!$roleAssignments)
                            {
                                //if wqe have role assignments then the users
                                //that are on the quals are also on one of the courses that
                                //this qual is on
                                //if they are not, then they are on the qual and shouldnt be. 
                                $usersArray[$user->id] = $user;
                            }
                        }
                        $usersToUnEnrol[$qual->id] = $usersArray;
                    }
                }
                if(count($usersArray) > 0)
                {
                    $retval .= '<tr>';
                    $retval .= '<td>'.bcgt_get_qualification_display_name($qual).'</td>';
                    $retval .= '<td>'.count($usersArray).'</td>';
                    $retval .= '</tr>';
                    $usersToUnlink = true;
                }
            }
            if(!$usersToUnlink)
            {
                $retval .= '<tr><td colspan="2">'.get_string('noredundantstudents', 'block_bcgt').'</td></tr>';
            }
            $this->usersToUnEnrol = $usersToUnEnrol;
            $retval .= '</tbody></table>';
        }
        return $retval;
    }
}

?>
