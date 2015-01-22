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
require_once $CFG->dirroot . '/blocks/bcgt/classes/core/AssessmentTracker.class.php';

class elbp_assessment_calendar extends Plugin {
    
    /**
     * Construct the plugin object
     * @param bool $install If true, we want to send the default info to the parent constructor, to install the record into the DB
     */
    public function __construct($install = false) {
        
        if ($install){
            parent::__construct( array(
                "name" => strip_namespace(get_class($this)),
                "title" => "Assessment Calendar",
                "path" => '/blocks/bcgt/',
                "version" => \ELBP\ELBP::getBlockVersionStatic()
            ) );
        }
        else
        {
            parent::__construct( strip_namespace(get_class($this)) );
        }

    }
    
    public function getConfigPath()
    {
        $path = $this->getPath() . 'config_'.$this->getName().'.php';
        return $path;
    }
    
    /**
     * Get the path to the icon to put into an img tag
     */
    public function getDockIconPath(){
        
        global $CFG;
        
        $icon = $CFG->wwwroot . $this->getPath() . 'pix/'.$this->getName().'/dock.png';
        if (!file_exists( str_replace($CFG->wwwroot, $CFG->dirroot, $icon) )){
            $icon = $CFG->wwwroot . $this->getPath() . 'pix/dock.png';
        }
        
        return $icon;        
        
    }
    
     /**
     * Install the plugin
     */
    public function install()
    {
        
        global $DB;
        
        $return = true;
        $this->id = $this->createPlugin();
        
        
        // [Any extra tables are handled by the bcgt block itself]
        
        // Data
        
        // Reporting elements for bc_dashboard reporting wizard
        
        
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
        
       
        // Upgrades
        
        
    
        return $return; # Never actually seems to change..
        
        
    }
    
        
    /**
     * Load the summary box
     * @return type
     */
    public function getSummaryBox(){
        
        $TPL = new \ELBP\Template();
        
        $AssessmentTracker = new \AssessmentTracker();
        $AssessmentTracker->loadStudent($this->student->id);
        
        $courses = \enrol_get_users_courses($this->student->id, true);
        $AssessmentTracker->setCourses($courses);
        
        $activeAssessments = $AssessmentTracker->getActiveAssessments();
        
        $TPL->set("obj", $this);
        $TPL->set("activeAssessments", $activeAssessments);
       
                
        try {
            return $TPL->load($this->CFG->dirroot . $this->path . 'tpl/'.$this->name.'/summary.html');
        }
        catch (\ELBP\ELBPException $e){
            return $e->getException();
        }
        
    }
    
    
    public function getDisplay($params = array()){
                
        $output = "";
        
        $output .= $this->loadJavascript(true);
                
        $TPL = new \ELBP\Template();
        
        $AssessmentTracker = new \AssessmentTracker();
        
        $AssessmentTracker->loadStudent($this->student->id);
        
        // Defaults
        $anyOrCriteriaActivities = \AssessmentTracker::DEFAULT_MOD_LINKS;
        $moduleTypes = explode(",", \AssessmentTracker::DEFAULT_MOD_TYPES);
        $year = date('Y');
        $showVals = false;
        $courses = \enrol_get_users_courses($this->student->id, true);
        
        
        $AssessmentTracker->setYear($year);
        $AssessmentTracker->setCourses($courses);
        $AssessmentTracker->setModuleLinks($anyOrCriteriaActivities);
        $AssessmentTracker->setModuleTypes($moduleTypes);
        $AssessmentTracker->setShowValues($showVals);
        
        
        $TPL->set("obj", $this);
        $TPL->set("access", $this->access);  
        $TPL->set("AssessmentTracker", $AssessmentTracker);
        
        try {
            $output .= $TPL->load($this->CFG->dirroot . $this->path . 'tpl/'.$this->name.'/expanded.html');
        } catch (\ELBP\ELBPException $e){
            $output .= $e->getException();
        }

        return $output;
        
    }
    
    public function loadJavascript($simple = false) {
        
        $this->js = array(
            '/blocks/bcgt/js/block_bcgt.js'
        );
        
        return parent::loadJavascript($simple);
        
    }
    
    
     /**
     * For the bc_dashboard reporting wizard - get all the data we can about Targets for these students,
     * then return the elements that we want.
     * @param type $students
     * @param type $elements
     */
    public function getAllReportingData($students, $elements)
    {
        
        
        
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
                    $TPL->load( $this->CFG->dirroot . $this->path . 'tpl/'.$this->name.'/'.$params['type'].'.html' );
                    $TPL->display();
                } catch (\ELBP\ELBPException $e){
                    echo $e->getException();
                }
                exit;                
                
            break;
        }
        
    }
    
   
    
}