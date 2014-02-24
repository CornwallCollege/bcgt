<?php

/**
 * <title>
 * 
 * @copyright 2013 Bedford College
 * @package Bedford College Electronic Learning Blue Print (ELBP)
 * @version 1.0
 * @author Conn Warwicker <cwarwicker@bedford.ac.uk> <conn@cmrwarwicker.com>
 * 
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 * 
 */

namespace ELBP\Plugins;

require_once 'lib.php';
require_once 'classes/core/UserPriorLearning.class.php';
require_once 'classes/core/UserCourseTarget.class.php';
require_once 'classes/core/Reporting.class.php';


class elbp_target_grades extends Plugin {
    
    /**
     * Construct the plugin object
     * @param bool $install If true, we want to send the default info to the parent constructor, to install the record into the DB
     */
    public function __construct($install = false) {
        
        if ($install){
            parent::__construct( array(
                "name" => strip_namespace(get_class($this)),
                "title" => "Aspirational Grades",
                "path" => '/blocks/bcgt/',
                "version" => 2013101500
            ) );
        }
        else
        {
            parent::__construct( strip_namespace(get_class($this)) );
        }

    }
    
    
     /**
     * Install the plugin
     */
    public function install()
    {
        
        global $DB;
        
        $return = true;
        $this->id = $this->createPlugin();
        $return = $return && $this->id;
        
        
        // [Any extra tables are handled by the bcgt block itself]
        
        // Data
        
        // Reporting elements for bc_dashboard reporting wizard
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt_target_grades:aspgrades", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt_target_grades:percentwithaspgrades", "getstringcomponent" => "block_bcgt"));
        
        // Hooks
        
        
        
        return $return;
    }
    
    /**
     * Upgrade the plugin from an older version to newer
     */
    public function upgrade(){
        
        global $DB;
        
        $return = true;
        $version = $this->version; # This is the current DB version we will be using to upgrade from     

       
        if ($version < 2013102401)
        {
            
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt_target_grades:aspgrades", "getstringcomponent" => "block_bcgt"));
            $this->version = 2013102401;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
            
        }
        
        if ($version < 2014012402)
        {
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt_target_grades:percentwithaspgrades", "getstringcomponent" => "block_bcgt"));
            $this->version = 2014012402;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
        }
    
    
        return $return; # Never actually seems to change..
        
        
    }
    
        
    /**
     * Load the summary box
     * @return type
     */
    public function getSummaryBox(){
        
        $TPL = new \ELBP\Template();
        
        $TPL->set("obj", $this);
        
        $quals = $this->getStudentsQualifications();
        $courses = $this->getStudentsCoursesWithoutQualifications();
                
        if ($quals)
        {
            foreach($quals as $qual)
            {
                $qual->aspirationalGrade = $this->getAspirationalTargetGrade($qual->get_id());
                $qual->targetGrade = $this->getTargetGrade($qual->get_id());
                if (is_array($qual->aspirationalGrade)) $qual->aspirationalGrade = reset($qual->aspirationalGrade);
                if (is_array($qual->targetGrade)) $qual->targetGrade = reset($qual->targetGrade);
            }
        }
        
        if ($courses)
        {
            foreach($courses as $course)
            {
                $course->aspirationalGrade = $this->getAspirationalTargetGradeCourse($course->id);
                $course->targetGrade = $this->getTargetGradeCourse($course->id);
                if (is_array($course->aspirationalGrade)) $course->aspirationalGrade = reset($course->aspirationalGrade);
                if (is_array($course->targetGrade)) $course->targetGrade = reset($course->targetGrade);
            }
        }
        
        usort($quals, function($a, $b){
            return strcasecmp($a->get_display_name(), $b->get_display_name());
        });
        
        usort($courses, function($a, $b){
            return strcasecmp($a->fullname, $b->fullname);
        });
                
        $TPL->set("quals", $quals);
        $TPL->set("courses", $courses);
        
        try {
            return $TPL->load($this->CFG->dirroot . $this->path . 'tpl/elbp_target_grades/summary.html');
        }
        catch (\ELBP\ELBPException $e){
            return $e->getException();
        }
        
    }
    
    
    /**
     * For the bc_dashboard reporting wizard - get all the data we can about Targets for these students,
     * then return the elements that we want.
     * @param type $students
     * @param type $elements
     */
    public function getAllReportingData($students, $elements)
    {
        
        global $DB;
                
        if (!$students || !$elements) return false;
        
        $data = array();
        
        // Some overal variables for counting
        $totalStudents = count($students);
        $totalWithAspirational = 0;
        
        $aspGrades = '-';
                  
        
        // Loop students and find all their targets
        foreach($students as $student)
        {

            $this->loadStudent($student->id);

            $quals = $this->getStudentsQualifications();
            $courses = $this->getStudentsCoursesWithoutQualifications();


            $a = array();
            if ($quals)
            {
                foreach($quals as $qual)
                {

                    $grade = $this->getAspirationalTargetGrade($qual->get_id());
                    if (is_array($grade)) $grade = reset($grade);

                    if ($grade && isset($grade->grade) && $grade->grade)
                    {
                        $a[] = $qual->get_display_name(false) . ' [' . $grade->grade . ']';
                    }

                }

            }

            if ($courses)
            {
                foreach($courses as $course)
                {

                    $grade = $this->getAspirationalTargetGradeCourse($course->id);
                    if (is_array($grade)) $grade = reset($grade);

                    if ($grade && isset($grade->grade) && $grade->grade)
                    {
                        $a[] = $course->fullname . ' [' . $grade->grade . ']';
                    }

                }

            }


            // We can't get any overalls here, so only bother once we're looking at individual students
            if ($totalStudents == 1)
            {
                $aspGrades = implode(",\n ", $a);
            }
            
            if (!empty($a))
            {
                $totalWithAspirational++;
            }


        }
                               
        
        // Totals
        $data['reports:bcgt_target_grades:aspgrades'] = $aspGrades;
        $data['reports:bcgt_target_grades:percentwithaspgrades'] = ($totalWithAspirational) ? round( ($totalWithAspirational / $totalStudents) * 100, 2 ) : 0;

        
        $names = array();
        $els = array();
        
        foreach($elements as $element)
        {
            $record = $DB->get_record("lbp_plugin_report_elements", array("id" => $element));
            $names[] = $record->getstringname;
            $els[$record->getstringname] = $record->getstringcomponent;
        }
        
        $return = array();
        foreach($names as $name)
        {
            if (isset($data[$name])){
                $newname = \get_string($name, $els[$name]);
                $return["{$newname}"] = $data[$name];
            }
        }
        
        return $return;
        
    }
    
    
    public function getStudentsQualifications(){
        
        global $DB;
        
        $quals = \get_users_quals($this->student->id, 5);
        $qualArray = array();
        
        if ($quals)
        {
            foreach($quals as $qual)
            {
                
                $load = new \stdClass();
                $load->loadLevel = \Qualification::LOADLEVELMIN;
                $qualification = \Qualification::get_qualification_class_id($qual->id, $load);
                if ($qualification)
                {
                    $qualArray[$qual->id] = $qualification;
                }
                
            }
        }
       
        return $qualArray;
        
    }
    
    public function getStudentsCourses(){
        
        global $DB;
        
        if (!$this->student) return false;
        
        global $DB;
        
        $DBC = new \ELBP\DB();
        
        $courses = $DBC->getStudentsCourses($this->student->id);
                
        if (!$courses) return $courses; # Empty array
        
        $courseType = $this->getSetting('course_types');
                        
        $array = array();
        
        foreach($courses as $course)
        {

            $checkEnrol = $DB->get_records("enrol", array("enrol" => "meta", "courseid" => $course->id));
            
            // Meta
            if ($courseType == 'meta' && $checkEnrol) $array[] = $course;
            
            // Child
            elseif ($courseType == 'child' && !$checkEnrol) $array[] = $course;
            
            elseif ($courseType == 'both') $array[] = $course;

        }
        
        $return = array();
        
        // Multiple rows for each qual this course is on
        foreach($array as $course)
        {
            
            $quals = $DB->get_records("block_bcgt_course_qual", array("courseid" => $course->id));
            if ($quals)
            {
                foreach($quals as $qual)
                {
                    
                    $loadParams = new \stdClass();
                    $loadParams->loadLevel = \Qualification::LOADLEVELMIN;
                    $qualObj = \Qualification::get_qualification_class_id($qual->bcgtqualificationid);
                    
                    $obj = new \stdClass();
                    $obj->id = $course->id;
                    $obj->fullname = $course->fullname;
                    $obj->shortname = $course->shortname;
                    $obj->qualid = $qualObj->get_id();
                    $obj->qual = $qualObj;
                    $return[] = $obj;
                }
            }
            else
            {
                $obj = new \stdClass();
                $obj->id = $course->id;
                $obj->fullname = $course->fullname;
                $obj->shortname = $course->shortname;
                $obj->qualid = null;
                $obj->qual = null;
                $return[] = $obj;
            }
            
        }              
        
        return $return;
        
    }
    
    public function getStudentsCoursesWithoutQualifications(){
        
        global $DB;
        
        if (!$this->student) return false;
        
        global $DB;
        
        $DBC = new \ELBP\DB();
        
        $courses = $DBC->getStudentsCourses($this->student->id);
                
        if (!$courses) return $courses; # Empty array
        
        $courseType = $this->getSetting('course_types');
                        
        $array = array();
                
        foreach($courses as $course)
        {

            $checkEnrol = $DB->get_records("enrol", array("enrol" => "meta", "courseid" => $course->id));
            
            // Meta
            if ($courseType == 'meta' && $checkEnrol) $array[] = $course;
            
            // Child
            elseif ($courseType == 'child' && !$checkEnrol) $array[] = $course;
            
            elseif ($courseType == 'both') $array[] = $course;

        }
        
        $return = array();
        
        // Multiple rows for each qual this course is on
        foreach($array as $course)
        {
            
            $quals = $DB->get_records("block_bcgt_course_qual", array("courseid" => $course->id));
            if (!$quals)
            {
                $return[] = $course;
            }
            
        }              
        
        return $return;
        
    }

    
    public function getAllPossibleGrades(){
        
        global $DB;
        
        $processedGrades = array();
        $return = array();
        
        
        $grades = $DB->get_records_sql("SELECT id, grade FROM {block_bcgt_target_grades} GROUP BY grade");
        foreach($grades as $grade)
        {
            if (!in_array($grade->grade, $processedGrades))
            {
                $return[] = array("id" => $grade->id, "grade" => $grade->grade, "location" => "block_bcgt_target_grades");
                $processedGrades[] = $grade->grade;
            }        
        }
        
        
        $breakdowns = $DB->get_records_sql("SELECT id, targetgrade FROM {block_bcgt_target_breakdown} GROUP BY targetgrade");
        foreach($breakdowns as $breakdown)
        {
            if (!in_array($breakdown->targetgrade, $processedGrades))
            {
                $return[] = array("id" => $breakdown->id, "grade" => $breakdown->targetgrade, "location" => "block_bcgt_target_breakdown");
                $processedGrades[] = $breakdown->targetgrade;
            }
        }
        
        
        $grades = $DB->get_records_sql("SELECT id, grade FROM {block_bcgt_custom_grades} GROUP BY grade");
        foreach($grades as $grade)
        {
            if (!in_array($grade->grade, $processedGrades))
            {
                $return[] = array("id" => $grade->id, "grade" => $grade->grade, "location" => "block_bcgt_custom_grades");
                $processedGrades[] = $grade->grade;
            }        
        }
        
        usort($return, function($a, $b){
            return strcasecmp($a['grade'], $b['grade']);
        });

        return $return;
        
        
    }
    
    
    
    
    
    
    public function getDisplay($params = array()){
                
        global $DB;
        
        $output = "";
        
        $TPL = new \ELBP\Template();
                        
        $quals = $this->getStudentsQualifications();
        $courses = $this->getStudentsCoursesWithoutQualifications();
                
        if ($quals)
        {
            foreach($quals as $qual)
            {
                
                $qual->aspirationalGrade = $this->getAspirationalTargetGrade($qual->get_id());
                $qual->targetGrade = $this->getTargetGrade($qual->get_id());
                
                if (is_array($qual->aspirationalGrade)) $qual->aspirationalGrade = reset($qual->aspirationalGrade);
                if (is_array($qual->targetGrade)) $qual->targetGrade = reset($qual->targetGrade);
                                        
                // Possible grades
                if (isset($qual->bespoke) && $qual->bespoke)
                {
                    
                    $awards = $qual->get_possible_awards();
                    if ($awards)
                    {
                        $awardArray = array();
                        foreach($awards as $award)
                        {
                            $awardArray[] = array("id" => $award->id, "grade" => $award->grade, "location" => "block_bcgt_bspk_q_grade_vals");
                        }
                        $qual->possibleGrades = $awardArray;
                    }
                    
                }
                else
                {
                    
                    // Check breakdown first
                    $breakdown = $DB->get_records("block_bcgt_target_breakdown", array("bcgttargetqualid" => $qual->get_target_qual_id()), "ranking DESC, unitsscoreupper DESC");
                    if ($breakdown)
                    {

                        $courseGrades = array();
                        foreach($breakdown as $b)
                        {
                            $courseGrades[] = array("id" => $b->id, "grade" => $b->targetgrade, "location" => "block_bcgt_target_breakdown");
                        }

                        $qual->possibleGrades = $courseGrades;

                    }


                    else
                    {

                        // If not, try target_grades
                        $targetgrades = $DB->get_records("block_bcgt_target_grades", array("bcgttargetqualid" => $qual->get_target_qual_id()), "ranking DESC, upperscore DESC");
                        if ($targetgrades)
                        {

                            $courseGrades = array();
                            foreach($targetgrades as $b)
                            {
                                $courseGrades[] = array("id" => $b->id, "grade" => $b->grade, "location" => "block_bcgt_target_grades");
                            }

                            $qual->possibleGrades = $courseGrades;

                        }

                    }
                    
                }
                                
                
            }
            
        }
        
        if ($courses)
        {
            foreach($courses as $course)
            {
                
                $course->aspirationalGrade = $this->getAspirationalTargetGradeCourse($course->id);
                $course->targetGrade = $this->getTargetGradeCourse($course->id);
                
                if (is_array($course->aspirationalGrade)) $course->aspirationalGrade = reset($course->aspirationalGrade);
                if (is_array($course->targetGrade)) $course->targetGrade = reset($course->targetGrade);
                
                // Check for custom grades for this course
                $customGrades = $DB->get_records("block_bcgt_custom_grades", array("courseid" => $course->id), "ranking DESC, grade ASC");
                if ($customGrades)
                {
                    $courseGrades = array();
                    foreach($customGrades as $customGrade)
                    {
                        $courseGrades[] = array("id" => $customGrade->id, "grade" => $customGrade->grade, "location" => "block_bcgt_custom_grades");
                    }
                    $course->possibleGrades = $courseGrades;
                }
                
            }
        }
        
        usort($quals, function($a, $b){
            return strcasecmp($a->get_display_name(), $b->get_display_name());
        });
        
        $TPL->set("quals", $quals);
        $TPL->set("courses", $courses);
        $TPL->set("obj", $this);
        $TPL->set("access", $this->access);      
        
        try {
            $output .= $TPL->load($this->CFG->dirroot . $this->path . 'tpl/elbp_target_grades/expanded.html');
        } catch (\ELBP\ELBPException $e){
            $output .= $e->getException();
        }

        return $output;
        
    }
    
    public function loadJavascript($simple = false) {
        parent::loadJavascript($simple);
    }
      
   
    public function getAspirationalTargetGrade($qualID){
        
        if (!$this->student) return false;
        
        return bcgt_get_aspirational_target_grade($this->student->id, $qualID);
        
    }
    
    public function getTargetGrade($qualID){
        
        if (!$this->student) return false;
        
        $userCourseTarget = new \UserCourseTarget();
        $targetGrade = $userCourseTarget->retrieve_users_target_grades($this->student->id, $qualID);
        return (isset($targetGrade[$qualID])) ? $targetGrade[$qualID] : false;
        
    }
    
    public function getTargetGradeCourse($courseID){
        
        if (!$this->student) return false;
        
        return bcgt_get_target_grade($this->student->id, false, $courseID);
        
    }
    
    public function getAspirationalTargetGradeCourse($courseID){
        
        if (!$this->student) return false;
        
        return bcgt_get_aspirational_target_grade($this->student->id, false, $courseID);
        
    }
    
    public function saveConfig($settings){
                
        parent::saveConfig($settings);
        
    }
    
    
    private function getUserGrades($type){
        
        if (!$this->student) return false;
                
        switch ($type)
        {
            
            case "aspirational":
                
                $grades = bcgt_get_aspirational_target_grade($this->student->id);
                $array = array();
                
                if ($grades)
                {
                    foreach($grades as $grade)
                    {
                        if ($grade->grade)
                        {
                            $array[] = "<span title='{$grade->name}'>{$grade->grade}</span>";
                        }
                    }
                }
                
                return ($array) ? implode(", ", $array) : '-';
                
                
            break;
        
        
            case "target":
                
                $R = new \Reporting();
                $records = $R->get_users_target_grades($this->student->id);
                $array = array();
                
                if ($records)
                {
                    foreach($records as $record)
                    {

                        if (isset($record->targetgrade) && $record->targetgrade->get_id())
                        {
                            $array[] = "<span title='{$record->qualname}'>".$record->targetgrade->get_grade()."</span>";
                        }
                        elseif (isset($record->grade))
                        {
                            $array[] = "<span title='{$record->name}'>".$record->grade."</span>";
                        }

                    }
                }

                return ($array) ? implode(", ", $array) : '-';
                
            break;
           
            
        }
        
    }
    
    
    /**
     * Get the little bit of info we want to display in the Student Profile summary section
     * @return mixed
     */
    public function getSummaryInfo(){
                
        if (!$this->student) return false;
        
        $output = "";
            
        $output .= "<table>";
            
            // Target grade
            $output .= "<tr>";
            
                $output .= "<td>".get_string('targetgrades', 'block_bcgt')."</td>";
                $output .= "<td>{$this->getUserGrades("target")}</td>";
            
            $output .= "</tr>";
            
            // Target grade
            $output .= "<tr>";
            
                $output .= "<td>".get_string('asptargetgrades', 'block_bcgt')."</td>";
                $output .= "<td>{$this->getUserGrades("aspirational")}</td>";
            
            $output .= "</tr>";
                        
        $output .= "</table>";
                            
        return $output;
        
    }
    
    
    public function ajax($action, $params, $ELBP){
        
        global $DB, $USER;
        
        switch($action)
        {
            
            case 'load_display_type':
                                
                // Correct params are set?
                if (!$params || !isset($params['studentID']) || !$this->loadStudent($params['studentID'])) return false;
                
                // We have the permission to do this?
                $access = $ELBP->getUserPermissions($params['studentID']);
                if (!$ELBP->anyPermissionsTrue($access)) return false;
                                
                $TPL = new \ELBP\Template();
                $TPL->set("obj", $this)
                    ->set("access", $access);
                                
                try {
                    $TPL->load( $this->CFG->dirroot . $this->path . 'tpl/elbp_target_grades/'.$params['type'].'.html' );
                    $TPL->display();
                } catch (\ELBP\ELBPException $e){
                    echo $e->getException();
                }
                exit;                
                
            break;
        }
        
    }
    
   
    
}