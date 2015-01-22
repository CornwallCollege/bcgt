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
        $out .= '<li>'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/data_cleanse.php?a=duu">'.
                '<span>'.get_string('duplicateuserunits', 'block_bcgt').'</span></a></li>';
        $out .= '</ul>';
        $out .= '<ul class="tabrow0">';
        $out .= '<li>'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/data_cleanse.php?a=uu">'.
                '<span>'.get_string('unlinkedunits', 'block_bcgt').'</span></a></li>';
        $out .= '<li>'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/data_cleanse.php?a=ruqu">'.
                '<span>'.get_string('redundantuserqualunits', 'block_bcgt').'</span></a></li>';
        $out .= '<li>'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/data_cleanse.php?a=du">'.
                '<span>'.get_string('unlinkdeletedusers', 'block_bcgt').'</span></a></li>';
        $out .= '</ul>';
        $out .= '<ul class="tabrow0">';
        $out .= '<li>'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/data_cleanse.php?a=rucq">'.
                '<span>'.get_string('unlinkusercoursequal', 'block_bcgt').'</span></a></li>';
//        $out .= '<li>'.
//                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/data_cleanse.php?a=du">'.
//                '<span>'.get_string('deleteemptyquals', 'block_bcgt').'</span></a></li>';
//        $out .= '<li>'.
//                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/data_cleanse.php?a=du">'.
//                '<span>'.get_string('deletenonusedunits', 'block_bcgt').'</span></a></li>';
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
            case "duu":
                $retval .= get_string('duplicateuserunitsdesc', 'block_bcgt');
                break;
            case "uu":
                $retval .= get_string('unlinkedunitsdesc', 'block_bcgt');
                break;
            case "ruqu":
                $retval .= get_string('redundantuserqualunitsdesc', 'block_bcgt');
                break;
            case "du":
                $retval .= get_string('unlinkdeletedusersdesc', 'block_bcgt');
                break;
            case "rucq":
                $retval .= get_string('unlinkredundantusercoursequal', 'block_bcgt');
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
            case "duu":
                return $this->get_duplicate_user_units_summary();
                break;
            case "uu":
                return $this->get_unlinked_user_units_summary();
                break;
            case "ruqu":
                return $this->get_redundant_user_units_summary();
                break;
            case "du":
                return $this->get_deleted_users_summary();
                break;
            case "rucq":
                return $this->get_redundant_user_course_qual_summary();
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
            case "duu":
                $this->get_duplicate_user_units_summary();
                $this->summary = $this->run_remove_duplicate_user_units();
                break;
            case "uu":
                $this->get_unlinked_user_units_summary();
                $this->summary = $this->run_unlink_users_units();
                break;
            case "ruqu";
                $this->get_redundant_user_units_summary();
                $this->summary = $this->run_remove_redundant_user_units();
                break;
            case "du";
                $this->summary = $this->run_unlink_deleted_users();
                break;
            case "rucq";
                $this->get_redundant_user_course_qual_summary();
                $this->summary = $this->run_remove_user_course_quals();
                break;
            default:
                $this->summary = "";
                break;
        }
    }
    
    protected function get_deleted_users_summary()
    {
        global $DB;
        $sql = "SELECT count(uu.id) FROM {block_bcgt_user_unit} uu 
            JOIN {user} u ON u.id = uu.userid 
            WHERE u.deleted = 1";
        $uuCount = $DB->count_records_sql($sql, array());
        
        $sql = "SELECT count(uq.id) FROM {block_bcgt_user_qual} uq 
            JOIN {user} u ON u.id = uq.userid 
            WHERE u.deleted = 1";
        $uqCount = $DB->count_records_sql($sql, array());
        
        $retval = $uuCount + $uqCount .' To be Deleted ';
        return $retval;
    }
    
    protected function run_unlink_deleted_users()
    {
        global $DB;
        $sql = "SELECT uu.* FROM {block_bcgt_user_unit} uu 
            JOIN {user} u ON u.id = uu.userid 
            WHERE u.deleted = 1";
        $uuCount = $DB->get_records_sql($sql, array());
        if($uuCount)
        {
            foreach($uuCount AS $uu)
            {
                $DB->delete_records('block_bcgt_user_unit', array("id"=>$uu->id));
            }
        }
        $sql = "SELECT uq.* FROM {block_bcgt_user_qual} uq 
            JOIN {user} u ON u.id = uq.userid 
            WHERE u.deleted = 1";
        $uqCount = $DB->get_records_sql($sql, array());
        if($uqCount)
        {
            foreach($uqCount AS $uq)
            {
                $DB->delete_records('block_bcgt_user_qual', array("id"=>$uq->id));
            }
        }
        return "";
    }
    
    protected function get_redundant_user_units_summary()
    {
        //find all instances where the user is not on the qualification, 
        //but they still have the units in the database.
        global $DB;
        $sql = "SELECT userunit.*, family.family, subtype.subtype, level.trackinglevel, 
            qual.name, qual.additionalname, unit.uniqueid, unit.name as unitname, 
            user.username, user.firstname, user.lastname 
            FROM {block_bcgt_user_unit} userunit 
            LEFT JOIN {block_bcgt_user_qual} userqual ON userqual.userid = userunit.userid
            AND userqual.bcgtqualificationid = userunit.bcgtqualificationid
            JOIN {user} user ON user.id = userunit.userid 
            JOIN {block_bcgt_unit} unit ON userunit.bcgtunitid = unit.id 
            JOIN {block_bcgt_qualification} qual ON qual.id = userunit.bcgtqualificationid 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
            JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
            WHERE userqual.id IS NULL;";
        $records = $DB->get_records_sql($sql, array());
        if($records)
        {
            $retval = '<h3>'.get_string('recordsfoundcount', 'block_bcgt').' : '.count($records).'</h3>';
            $retval .= '<table>';
            $retval .= '<thead>';
            $retval .= '<tr>';
            $retval .= '<th></th>';
            $retval .= '<th>'.get_string('qual','block_bcgt').'</th>';
            $retval .= '<th>'.get_string('unit','block_bcgt').'</th>';
            $retval .= '<th>'.get_string('user','block_bcgt').'</th>';
            $retval .= '</tr>';
            $retval .= '</thead>';
            $retval .= '<tbody>';
            foreach($records AS $record)
            {
                $retval .= '<tr>';
                $retval .= '<td>'.$record->id.'</td>';
                $retval .= '<td>'.bcgt_get_qualification_display_name($record).'</td>';
                $retval .= '<td>'.$record->uniqueid.' '.$record->unitname.'</td>';
                $retval .= '<td>'.$record->username.' - '.$record->firstname.' '.$record->lastname.'</td>';
                $retval .= '</tr>';
            }
            $retval .= '</tbody>';
            $retval .= '</table>';
            $this->redundantuserqualunits = $records;
        }
        else
        {
            $retval = get_string('norecordsfound', 'block_bcgt');
        }
        return $retval;
    }
    
    protected function get_redundant_user_course_qual_summary()
    {
        global $DB;
        $sql = " SELECT userqual.id, level.id AS levelid, level.trackinglevel, type.id AS typeid, 
                type.type, subtype.id AS subtypeid, subtype.subtype, targetqual.id as bcgttargetqualid, family.family, 
                qual.name, qual.additionalname, user.firstname, user.lastname, user.username 
                FROM {block_bcgt_qualification} qual
                JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
                JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid
                JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid
                JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid
                JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
                JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qual.id 
                JOIN {user} user ON user.id = userqual.userid
                WHERE userqual.id NOT IN (
                SELECT userqual.id FROM {block_bcgt_user_qual} userqual 
                JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = userqual.bcgtqualificationid 
                JOIN {role_assignments} ra ON ra.userid = userqual.userid 
                JOIN {context} con ON con.id = ra.contextid 
                JOIN {course} course ON course.id = con.instanceid AND course.id = coursequal.courseid)";
        $retval = '';
        $quals = $DB->get_records_sql($sql, array());
        if($quals)
        {
            $retval = '<h3>'.get_string('found','block_bcgt').' : '.count($quals).'</h3>';
            $retval .= '<table>';
            $retval .= '<thead><tr>';
            $retval .= '<th>'.get_string('qual', 'block_bcgt').'</th>';   
            $retval .= '<th>'.get_string('user', 'block_bcgt').'</th>';
            $retval .= '</tr></thead><tbody>';
            $qualsToRemoveAllStudents = $quals;
            foreach($quals AS $qual)
            {
                //now lets count the students that are on them
                $retval .= '<tr>';
                $retval .= '<td>'.bcgt_get_qualification_display_name($qual).'</td>';
                $retval .= '<td>'.$qual->username.' : '.$qual->firstname.' '.$qual->lastname.'</td>';
                $retval .= '</tr>';               
            }
            $retval .= '</tbody></table>';
            $this->qualsToRemoveStudents = $qualsToRemoveAllStudents;
        }
        return $retval;
    }
    
    protected function run_remove_user_course_quals()
    {
        global $DB;        
        if(isset($this->qualsToRemoveStudents))
        {
            foreach($this->qualsToRemoveStudents AS $record)
            {
                //then lets delete it
                $DB->delete_records('block_bcgt_user_qual',array("id"=>$record->id));
            }
        }
        else
        {
            echo "No Records Found";
        }
        return "";
    }
    
    protected function run_remove_redundant_user_units()
    {
        global $DB;        
        if(isset($this->redundantuserqualunits))
        {
            foreach($this->redundantuserqualunits AS $record)
            {
                //then lets delete it
                $DB->delete_records('block_bcgt_user_unit',array("id"=>$record->id));
            }
        }
        else
        {
            echo "No Records Found";
        }
        return "";
    }
    
    protected function get_duplicate_user_units_summary()
    {
        //
        global $DB;
        $sql = "SELECT userunit.id, userunit.*, family.family, subtype.subtype, level.trackinglevel, 
            qual.name, qual.additionalname, unit.uniqueid, unit.name as unitname, 
            user.username, user.firstname, user.lastname, count(*) 
            FROM {block_bcgt_user_unit} userunit 
            JOIN {user} user ON user.id = userunit.userid 
            JOIN {block_bcgt_unit} unit ON userunit.bcgtunitid = unit.id 
            JOIN {block_bcgt_qualification} qual ON qual.id = userunit.bcgtqualificationid 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
            JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
            GROUP BY userunit.userid, userunit.bcgtunitid, userunit.bcgtqualificationid
            HAVING count(*) > 1
            ORDER BY family ASC, level.id ASC, subtype. subtype ASC, qual.name ASC";
        $records = $DB->get_records_sql($sql, array());
        if($records)
        {
            $retval = '<h3>'.get_string('recordsfoundcount', 'block_bcgt').' : '.count($records).'</h3>';
            $retval .= '<table>';
            $retval .= '<thead>';
            $retval .= '<tr>';
            $retval .= '<th></th>';
            $retval .= '<th>'.get_string('qual','block_bcgt').'</th>';
            $retval .= '<th>'.get_string('unit','block_bcgt').'</th>';
            $retval .= '<th>'.get_string('user','block_bcgt').'</th>';
            $retval .= '</tr>';
            $retval .= '</thead>';
            $retval .= '<tbody>';
            foreach($records AS $record)
            {
                $retval .= '<tr>';
                $retval .= '<td>'.$record->id.'</td>';
                $retval .= '<td>'.bcgt_get_qualification_display_name($record).'</td>';
                $retval .= '<td>'.$record->uniqueid.' '.$record->unitname.'</td>';
                $retval .= '<td>'.$record->username.' - '.$record->firstname.' '.$record->lastname.'</td>';
                $retval .= '</tr>';
            }
            $retval .= '</tbody>';
            $retval .= '</table>';
            $this->duplicateuserunit = $records;
        }
        else
        {
            $retval = get_string('norecordsfound', 'block_bcgt');
        }
        return $retval;
    }
    
    protected function get_unlinked_user_units_summary()
    {
        global $DB;
        $sql = "SELECT userunit.id, family.family, subtype.subtype, level.trackinglevel, 
            qual.name, qual.additionalname, unit.uniqueid, unit.name as unitname, user.username, user.firstname, user.lastname 
            FROM {block_bcgt_user_unit} userunit 
            JOIN {user} user ON user.id = userunit.userid 
            JOIN {block_bcgt_unit} unit ON userunit.bcgtunitid = unit.id 
            JOIN {block_bcgt_qualification} qual ON qual.id = userunit.bcgtqualificationid 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
            JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
            WHERE userunit.id NOT IN (
            SELECT userunit.id FROM {block_bcgt_user_unit} userunit 
            JOIN {block_bcgt_qual_units} qualunits ON qualunits.bcgtunitid = userunit.bcgtunitid AND userunit.bcgtqualificationid = 
            qualunits.bcgtqualificationid
            )
            ORDER BY family ASC, level.id ASC, subtype. subtype ASC, qual.name ASC";
        $records = $DB->get_records_sql($sql, array());
        if($records)
        {
            $retval = '<h3>'.get_string('recordsfoundcount', 'block_bcgt').' : '.count($records).'</h3>';
            $retval .= '<table>';
            $retval .= '<thead>';
            $retval .= '<tr>';
            $retval .= '<th></th>';
            $retval .= '<th>'.get_string('qual','block_bcgt').'</th>';
            $retval .= '<th>'.get_string('unit','block_bcgt').'</th>';
            $retval .= '<th>'.get_string('user','block_bcgt').'</th>';
            $retval .= '</tr>';
            $retval .= '</thead>';
            $retval .= '<tbody>';
            foreach($records AS $record)
            {
                $retval .= '<tr>';
                $retval .= '<td>'.$record->id.'</td>';
                $retval .= '<td>'.bcgt_get_qualification_display_name($record).'</td>';
                $retval .= '<td>'.$record->uniqueid.' '.$record->unitname.'</td>';
                $retval .= '<td>'.$record->username.' - '.$record->firstname.' '.$record->lastname.'</td>';
                $retval .= '</tr>';
            }
            $retval .= '</tbody>';
            $retval .= '</table>';
            $this->userunitunlink = $records;
        }
        else
        {
            $retval = get_string('norecordsfound', 'block_bcgt');
        }
        return $retval;
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
        return "";
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
        return "";
    }
    
    protected function run_remove_duplicate_user_units()
    {
        global $DB;
        if(isset($this->duplicateuserunit))
        {
            foreach($this->duplicateuserunit AS $record)
            {
                //hen we are trying to delete it

                //first lets get all that are like this:
                $allDuplicates = $DB->get_records_sql("SELECT * FROM {block_bcgt_user_unit} 
                    WHERE userid = ? AND bcgtunitid = ? AND bcgtqualificationid = ? ORDER BY dateupdated DESC", array($record->userid, $record->bcgtunitid, $record->bcgtqualificationid));
                if($allDuplicates)
                {
                    $updatedDuplicates = array();
                    $nonUpdatedDuplicate = array();
                    //so do any of them have comments or awards?
                    foreach($allDuplicates AS $allDuplicate)
                    {
                        if(($allDuplicate->comments && $allDuplicate->comments != '') 
                                || $allDuplicate->bcgttypeawardid || $allDuplicate->userdefinedvalue 
                                || $allDuplicate->bcgtvalueid)
                        {
                            //then it has comments or type award or value or userdefinedvalue
                            $updatedDuplicates[] = $allDuplicate;
                        }
                        else
                        {
                            $nonUpdatedDuplicate[] = $allDuplicate;
                        }
                    }
                    if(count($updatedDuplicates) != 1)
                    {
                        //then none have anything set, so lets just take the top one and get rid of the rest
                        //OR
                        //the count is greater than 1. 
                        //so we just need to keep the top as its the most recent updated and 
                        //get rid of the rest
                        $firstElement = true;
                        $first = null;
                        foreach($allDuplicates AS $allDuplicate)
                        {
                            if($firstElement) 
                            {
                                $firstElement = false;
                                $first = $allDuplicate;
                            } 
                            else 
                            {
                                $DB->delete_records('block_bcgt_user_unit', array("id"=>$allDuplicate->id));
                            }
                        }
                        //then, lets just check we have the correct unit award, lets recalculate it:
                        $loadParams = new stdClass();
                        $loadParams->loadLevel = Qualification::LOADLEVELALL;
                        $qualification = Qualification::get_qualification_class_id($first->bcgtqualificationid, $loadParams);
                        if($qualification)
                        {
                            $units = $qualification->get_units();
                            if($units)
                            {
                                $unit = $units[$first->bcgtunitid];
                                $unit->load_student_information($first->userid, $first->bcgtqualificationid, $loadParams);
                                $unit->calculate_unit_award($first->bcgtqualificationid);
                            }
                        }
                    }
                    elseif(count($updatedDuplicates) == 1)
                    {
                        //then get rid of the rest
                        foreach($nonUpdatedDuplicate AS $nonUpdated)
                        {
                            $DB->delete_records('block_bcgt_user_unit', array("id"=>$nonUpdated->id));
                        }
                    }
                }
                else
                {
                    echo "NON FOUND";
                }
            }
        }
        else
        {
            echo "NO DATA SET";
        }
        return "";
    }
    
    protected function run_unlink_users_units()
    {
        global $DB;        
        if(isset($this->userunitunlink))
        {
            foreach($this->userunitunlink AS $record)
            {
                    //then lets delete it
                    $DB->delete_records('block_bcgt_user_unit',array("id"=>$record->id));
            }
        }
        else
        {
            echo "No Records Found";
        }
        return "";
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
