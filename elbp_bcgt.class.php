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

require_once $CFG->dirroot . '/blocks/bcgt/lib.php';

/**
 * 
 */
class elbp_bcgt extends Plugin {
    
    /**
     * Construct the plugin object
     * @param bool $install If true, we want to send the default info to the parent constructor, to install the record into the DB
     */
    public function __construct($install = false) {
        
        if ($install){
            parent::__construct( array(
                "name" => strip_namespace(get_class($this)),
                "title" => "Grade Tracker",
                "path" => '/blocks/bcgt/',
                "version" => 2013090500
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
        
        // This is a core ELBP plugin, so the extra tables it requires are handled by the core ELBP install.xml
        
        // Reporting data
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numwithqual", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numwithoutqual", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numwithtargetgrade", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numwithouttargetgrade", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:targetgrade", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:weightedtargetgrade", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:quals", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numcredits", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numexpectedcredits", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:percentcorrectcredits", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:percentabovecredits", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:percentbelowcredits", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:qualcredits", "getstringcomponent" => "block_bcgt"));
        
        
        // Hooks
        $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "Units"));
        $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "Target Grade"));
        
        return $return;
    }
    
    /**
     * Upgrade the plugin from an older version to newer
     */
    public function upgrade(){
        
        global $DB;
        
        $result = true;
        $version = $this->version; # This is the current DB version we will be using to upgrade from     
        
        // [Upgrades here]
        if ($version < 2013092304)
        {
            
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numwithqual", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numwithoutqual", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numwithtargetgrade", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numwithouttargetgrade", "getstringcomponent" => "block_bcgt"));
            
            $this->version = 2013092304;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
            
        }
        
        if ($version < 2013092500)
        {
            
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:targetgrade", "getstringcomponent" => "block_bcgt"));
            $this->version = 2013092500;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
            
        }
        
        if ($version < 2013102400)
        {
            
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:quals", "getstringcomponent" => "block_bcgt"));
            $this->version = 2013102400;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
            
        }
        
        if ($version < 2013102500)
        {
            
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:weightedtargetgrade", "getstringcomponent" => "block_bcgt"));
            $this->version = 2013102500;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");            
        }
        
        if ($version < 2013103100)
        {
            
            // Hooks
            $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "Units"));
            $this->version = 2013103100;
            $this->updatePlugin();
            \mtrace("## Inserted hook data for plugin: {$this->title}"); 
            
        }
        
        if ($version < 2013103101)
        {
            
            // Hooks
            $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "Target Grade"));
            $this->version = 2013103101;
            $this->updatePlugin();
            \mtrace("## Inserted hook data for plugin: {$this->title}"); 
            
        }
        
        if ($version < 2013120500)
        {
            
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numcredits", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numexpectedcredits", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:percentcorrectcredits", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:percentabovecredits", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:percentbelowcredits", "getstringcomponent" => "block_bcgt"));
            $this->version = 2013120500;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}"); 
            
        }
        
        
        if ($version < 2013121600)
        {
            
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:qualcredits", "getstringcomponent" => "block_bcgt"));
            $this->version = 2013121600;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}"); 
            
        }

        return $result;
        
    }
    
    
    /**
     * Load the summary box
     * @return type
     */
    public function getSummaryBox(){
        
        $TPL = new \ELBP\Template();
        
        $quals = \get_users_quals($this->student->id, 5);
        usort($quals, function($A, $B){
            return ( \strnatcasecmp($A->name, $B->name) == 0 ) ? 0 : (  \strnatcasecmp($A->name, $B->name) > 0 ) ? -1 : 1;
        });
        
        $TPL->set("obj", $this);
        $TPL->set("quals", $quals);
                
        try {
            return $TPL->load($this->CFG->dirroot . $this->path . 'tpl/elbp_bcgt/summary.html');
        }
        catch (\ELBP\ELBPException $e){
            return $e->getException();
        }
        
    }
    
    
    public function getDisplay($params = array()){
                
        $output = "";
        
        $TPL = new \ELBP\Template();
        
        $quals = \get_users_quals($this->student->id, 5);
        usort($quals, function($A, $B){
            return ( \strnatcasecmp($A->name, $B->name) == 0 ) ? 0 : (  \strnatcasecmp($A->name, $B->name) > 0 ) ? -1 : 1;
        });
        
        
        $TPL->set("obj", $this);
        $TPL->set("access", $this->access);      
        $TPL->set("params", $params);
        $TPL->set("quals", $quals);
        
        try {
            $output .= $TPL->load($this->CFG->dirroot . $this->path . 'tpl/elbp_bcgt/expanded.html');
        } catch (\ELBP\ELBPException $e){
            $output .= $e->getException();
        }

        return $output;
        
    }
    
    public function loadJavascript($simple = false) {
        
        $this->js = array(
            '/blocks/bcgt/elbp_bcgt.js'
        );
        
        parent::loadJavascript($simple);
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
        $totalWithQual = 0;
        $totalWithTargetGrade = 0;
        $targetGrade = '-';
        $weightedTargetGrade = '-';
        $qualifications = '-';
        $expectedCredits = 0;
        $credits = 0;
        $totalCorrectCredits = 0;
        $totalAboveCredits = 0;
        $totalBelowCredits = 0;
        $creditsOnQual = '-';
        
        $totalQuals = 0;
        
        $PL = new \UserPriorLearning();
        $R = new \Reporting();
        
        $load = new \stdClass();
        $load->loadLevel = \Qualification::LOADLEVELMIN;
                
        // Loop students and find all their targets
        foreach($students as $student)
        {
            
            $this->loadStudent($student->id);
            
            // Check if has qual
            $quals = \get_users_quals($this->student->id, 5);
            $cnt = count($quals);
            
            if ($cnt > 0)
            {
                $totalWithQual++;
                $totalQuals += $cnt;
            }
            
            
            // target grade?
            $records = $R->get_users_target_grades($student->id);
            if ($records && count($records) > 0)
            {
                $totalWithTargetGrade++;
            }
            
            
            // Credits
            $userExpectedCredits = \get_users_expected_credits($this->student->id);
            $userCredits = \get_users_credits($this->student->id);
            
            $expectedCredits += $userExpectedCredits;
            $credits += $userCredits;
            
            if ($userCredits == $userExpectedCredits) $totalCorrectCredits++;
            elseif ($userCredits < $userExpectedCredits) $totalBelowCredits++;
            elseif ($userCredits > $userExpectedCredits) $totalAboveCredits++;
            
            
                        
            // If only one student, get their target grade and display
            if ($totalStudents == 1)
            {
                
                // Targets
                $t = array();
                if ($records)
                {
                    foreach($records as $record)
                    {
                        if (isset($record->targetgrade) && $record->targetgrade->get_id())
                        {
                            $t[] = $record->qualname . ' (' . $record->targetgrade->get_grade() . ')';
                        }
                        elseif (isset($record->grade))
                        {
                            $t[] = $record->name . ' ('.$record->grade.')';
                        }
                    }
                }
                
                $targetGrade = implode(",\n ", $t);
                
                
                // Weighted
                $w = array();
                if ($records)
                {
                    foreach($records as $record)
                    {
                        if (isset($record->weightedtargetgrade))
                        {
                            $w[] = $record->qualname . ' (' . $record->weightedtargetgrade->get_grade() . ')';
                        }
                    }
                }
                
                $weightedTargetGrade = implode(",\n ", $w);
                
                // Quals
                $q = array();
                if ($quals)
                {
                    foreach($quals as $qual)
                    {
                        if (!isset($qual->isbespoke)){
                            $level = str_replace("Level ", "", $qual->trackinglevel);
                            $q[] = $qual->type . ' L' . $level . ' ' . $qual->subtype . ' ' . $qual->name;
                        } elseif (isset($qual->isbespoke)){
                            $q[] = $qual->displaytype . ' L' . $qual->level . ' '. $qual->subtype . ' ' . $qual->name;
                        } 
                    }
                }
                
                $qualifications = implode(",\n ", $q);
                
                
                // Credits on qual
                $c = array();
                
                if ($quals)
                {
                    foreach($quals as $qual)
                    {
                        
                        // Expecting
                        $expecting = 0;
                        $found = 0;
                        
                        $qualification = \Qualification::get_qualification_class_id($qual->id, $load);
                        if ($qualification)
                        {
                            $check = $DB->get_record("block_bcgt_target_qual_att", array("bcgttargetqualid" => $qualification->get_target_qual_id(), "name" => \SubType::DEFAULTNUMBEROFCREDITSNAME));
                            if ($check)
                            {
                                $expecting = $check->value;
                            }
                            
                            $found = \get_users_credits($this->student->id, $qualification->get_id());
                            
                            
                            if (!isset($qual->isbespoke)){
                                $level = str_replace("Level ", "", $qual->trackinglevel);
                                $c[] = $qual->type . ' L' . $level . ' ' . $qual->subtype . ' ' . $qual->name . ': ' . $found . '/' . $expecting;
                            } elseif (isset($qual->isbespoke)){
                                $c[] = $qual->displaytype . ' L' . $qual->level . ' '. $qual->subtype . ' ' . $qual->name . ': ' . $found . '/' . $expecting;
                            } 
                            
                        }
                        
                    }
                }
                
                $creditsOnQual = implode(",\n ", $c);
                
                
            }
                        
            
        }
                       
        
        // Totals
        $data['reports:bcgt:numwithqual'] = $totalWithQual;
        $data['reports:bcgt:numwithoutqual'] = $totalStudents - $totalWithQual;
        $data['reports:bcgt:numwithtargetgrade'] = $totalWithTargetGrade;
        $data['reports:bcgt:numwithouttargetgrade'] = $totalStudents - $totalWithTargetGrade;
        $data['reports:bcgt:targetgrade'] = $targetGrade;
        $data['reports:bcgt:weightedtargetgrade'] = $weightedTargetGrade;
        $data['reports:bcgt:quals'] = $qualifications;
        $data['reports:bcgt:numcredits'] = $credits;
        $data['reports:bcgt:numexpectedcredits'] = $expectedCredits;
        $data['reports:bcgt:percentcorrectcredits'] = round(($totalCorrectCredits / $totalStudents) * 100, 1);
        $data['reports:bcgt:percentabovecredits'] = round(($totalAboveCredits / $totalStudents) * 100, 1);
        $data['reports:bcgt:percentbelowcredits'] = round(($totalBelowCredits / $totalStudents) * 100, 1);
        $data['reports:bcgt:qualcredits'] = $creditsOnQual;

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
    
   
    public function getUserTargetGrades($simple = false){
        
        global $CFG;
        
        if (!$this->student) return false;
        
        require_once $CFG->dirroot . '/blocks/bcgt/lib.php';
        
        $R = new \Reporting();
        $records = $R->get_users_target_grades($this->student->id);
        $array = array();
        
        if ($records)
        {
            foreach($records as $record)
            {
                
                if (isset($record->targetgrade))
                {
                    if ($simple){
                        $array[$record->qualname] = $record->targetgrade->get_grade();
                    } else {
                        $array[] = "<span title='{$record->qualname}'>".$record->targetgrade->get_grade()."</span>";
                    }
                    
                }
                
            }
        }
                        
        if ($simple) return $array;
        
        else return ($array) ? implode(", ", $array) : '-';
        
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
                $output .= "<td>{$this->getUserTargetGrades()}</td>";
            
            $output .= "</tr>";
                        
        $output .= "</table>";
                            
        return $output;
        
    }
    
    
    private function loadTracker($qualID, $TPL)
    {
        
        global $PAGE;
        
        if (!$this->student) return false;
        
        // Load qualification
        $loadParams = new \stdClass();
        $loadParams->loadLevel = \Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
        $qualification = \Qualification::get_qualification_class_id($qualID, $loadParams);
        
        if ($qualification)
        {
            $loadParams = new \stdClass;
            $loadParams->loadLevel = \Qualification::LOADLEVELALL;
            $loadParams->loadAward = true;
            $loadParams->loadTargets = true;
            $qualification->load_student_information($this->student->id, $loadParams);
            
        }
        
        // Require js for hovers and whatnot
        $jsModule = array(
            'name'     => 'block_bcgt',
            'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        $PAGE->requires->js_init_call('M.block_bcgt.initgridstu', null, true, $jsModule);

        $TPL->set("qualification", $qualification, true);
        
        
    }
    
    
    
    
    public function _callHook_Target_Grade($obj, $params)
    {
        
        if (!$this->isEnabled()) return false;
        if (!isset($obj->student->id)) return false;
        //if (!isset($params['courseID'])) return false;
                        
        // Load student
        $this->loadStudent($obj->student->id);
                
        $return = array();
        $return['grades'] = $this->getUserTargetGrades(true);       
        
        return $return;
        
    }
    
    
     /**
     * Get the current total att, punc data for a given student on a given course
     */
    public function _callHook_Units($obj, $params)
    {
                
        if (!$this->isEnabled()) return false;
        if (!isset($obj->student->id)) return false;
        if (!isset($params['courseID'])) return false;
                        
        // Load student
        $this->loadStudent($obj->student->id);
                        
        $return = array();
        $return['quals'] = array();
        
        // Get quals on this course
        $quals = bcgt_get_course_quals($params['courseID']);
        if ($quals)
        {
            foreach($quals as $qual)
            {
                
                // Get units on this qual
                $loadParams = new \stdClass();
                $loadParams->loadLevel = \Qualification::LOADLEVELUNITS;
                
                $qualification = \Qualification::get_qualification_class_id($qual->id, $loadParams);
                if ($qualification)
                {
                    
                    $units = $qualification->get_units();
                    if ($units)
                    {
                        
                        $return['quals'][$qualification->get_id()] = array();
                        $return['quals'][$qualification->get_id()]['qual'] = $qualification->get_display_name();
                        $return['quals'][$qualification->get_id()]['units'] = array();
                        
                        foreach($units as $unit)
                        {
                            
                            $unit->load_student_information($this->student->id, $qualification->get_id(), $loadParams);
                            if ($unit->student_doing_unit($qualification->get_id()))
                            {
                                $return['quals'][$qualification->get_id()]['units'][$unit->get_id()] = $unit;
                            }
                            
                        }
                        
                    }
                    
                }
                
            }
        }
        
        return $return;
        
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
                
                if ($params['type'] == 'tracker') $this->loadTracker( $params['id'], $TPL );
                
                try {
                    $TPL->load( $this->CFG->dirroot . $this->path . 'tpl/elbp_bcgt/'.$params['type'].'.html' );
                    $TPL->display();
                } catch (\ELBP\ELBPException $e){
                    echo $e->getException();
                }
                exit;                
                
            break;
        }
        
    }
    
}