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
require_once 'classes/core/Reporting.class.php';


class elbp_prior_learning extends Plugin {
    
    /**
     * Construct the plugin object
     * @param bool $install If true, we want to send the default info to the parent constructor, to install the record into the DB
     */
    public function __construct($install = false) {
        
        if ($install){
            parent::__construct( array(
                "name" => strip_namespace(get_class($this)),
                "title" => "Prior Learning",
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
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:numrecords", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:percentwith", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:percentwithout", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:avggcsescore", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:percentwithavggcse", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:percentwithoutavggcse", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:avgnumrecords", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:numwith", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:numwithout", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:numwithavggcse", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:numwithoutavggcse", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:enggcse", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:mathsgcse", "getstringcomponent" => "block_bcgt"));

        
        // Hooks
        $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "English GCSE"));
        $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "Maths GCSE"));
        
        
        return $return;
    }
    
    /**
     * Upgrade the plugin from an older version to newer
     */
    public function upgrade(){
        
        global $DB;
        
        $return = true;
        $version = $this->version; # This is the current DB version we will be using to upgrade from     
        
       
        if ($version < 2013091600)
        {
            
            // Reporting elements for bc_dashboard reporting wizard
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:numrecords", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:percentwith", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:percentwithout", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:avggcsescore", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:percentwithavggcse", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:percentwithoutavggcse", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:avgnumrecords", "getstringcomponent" => "block_bcgt"));

            $this->version = 2013091600;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
            
        }
        
        if ($version < 2013091601)
        {
            
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:numwith", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:numwithout", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:numwithavggcse", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:numwithoutavggcse", "getstringcomponent" => "block_bcgt"));
            
            $this->version = 2013091601;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
            
        }
        
        if ($version < 2013091800)
        {
            
            $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "English GCSE"));
            $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "Maths GCSE"));
            
            $this->version = 2013091800;
            $this->updatePlugin();
            \mtrace("## Inserted hook data for plugin: {$this->title}");
            
        }
        
        if ($version < 2013091901)
        {
            
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:enggcse", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:pl:mathsgcse", "getstringcomponent" => "block_bcgt"));

            $this->version = 2013091901;
            $this->updatePlugin();
            \mtrace("## Inserted hook data for plugin: {$this->title}");
            
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
       
        $PL = new \UserPriorLearning();
        $prior = $PL->retrieve_user_plearn($this->student->id);
        $TPL->set("prior", $prior);
        
        $R = new \Reporting();
        $avgScore = $R->get_users_average_gcse_score($this->student->id);
        $TPL->set("avgScore", $avgScore);
                
        try {
            return $TPL->load($this->CFG->dirroot . $this->path . 'tpl/elbp_prior_learning/summary.html');
        }
        catch (\ELBP\ELBPException $e){
            return $e->getException();
        }
        
    }
    
    
    public function getDisplay($params = array()){
                
        $output = "";
        
        $TPL = new \ELBP\Template();
        
        $PL = new \UserPriorLearning();
        $prior = $PL->retrieve_user_plearn($this->student->id);
        
        $R = new \Reporting();
        $avgScore = $R->get_users_average_gcse_score($this->student->id);
        
        $TPL->set("prior", $prior);
        $TPL->set("avgScore", $avgScore);
        $TPL->set("obj", $this);
        $TPL->set("access", $this->access);      
        
        try {
            $output .= $TPL->load($this->CFG->dirroot . $this->path . 'tpl/elbp_prior_learning/expanded.html');
        } catch (\ELBP\ELBPException $e){
            $output .= $e->getException();
        }

        return $output;
        
    }
    
    public function loadJavascript($simple = false) {
        
//        $this->js = array(
//            '/blocks/bcgt/elbp_prior_learning.js'
//        );
        
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
        $totalPLRecords = 0;
        $totalWithPL = 0;
        $totalGcseScore = 0;
        $totalWithAvgGCSE = 0;
        
        $PL = new \UserPriorLearning();
        $R = new \Reporting();
                
        // Loop students and find all their targets
        foreach($students as $student)
        {
            
            $this->loadStudent($student->id);
            $prior = $PL->retrieve_user_plearn($this->student->id);
            $avgScore = $R->get_users_average_gcse_score($this->student->id);
            
            if ($prior){
                $totalWithPL++;
                $totalPLRecords += count($prior);
            }
            
            if (is_numeric($avgScore)){
                $totalGcseScore += $avgScore;
                $totalWithAvgGCSE++;
            }
                        
                           
            // If individual student
            if (count($students) == 1)
            {
                
                $getEngGCSE = $PL->get_plearn_specific("GCSE", "English", $this->student->id);
                
                // If no English try English Language
                if (!$getEngGCSE)
                {
                    $getEngGCSE = $PL->get_plearn_specific("GCSE", "English Language", $this->student->id);
                }
                
                $getMathsGCSE = $PL->get_plearn_specific("GCSE", "Mathematics", $this->student->id);
                
                $engGCSE = ($getEngGCSE) ? $getEngGCSE->grade : '-';
                $mathsGCSE = ($getMathsGCSE) ? $getMathsGCSE->grade : '-';
                
            }
            else
            {
                $engGCSE = '-';
                $mathsGCSE = '-';
            }
                        
            
        }
                
        $totalWithoutPL = $totalStudents - $totalWithPL;
        $avgGcseScore = ($totalWithAvgGCSE > 0) ? round( $totalGcseScore / $totalWithAvgGCSE, 2 ) : 0;
        $totalWithOutAvgGCSE = $totalStudents - $totalWithAvgGCSE;
        $avgPLRecords = ($totalStudents > 0) ? round( $totalPLRecords / $totalStudents) : 0;
        $percentWithPL = ($totalStudents > 0) ? round(( $totalWithPL / $totalStudents ) * 100) : 0;
        $percentWithoutPL = ($totalStudents > 0) ? round( ( $totalWithoutPL / $totalStudents ) * 100) : 0;
        $percentWithAvgGCSE = ($totalStudents > 0) ? round( ($totalWithAvgGCSE / $totalStudents ) * 100) : 0;
        $percentWithoutAvgGCSE = ($totalStudents > 0) ? round( ($totalWithOutAvgGCSE / $totalStudents ) * 100) : 0;
        
        
        // Totals
        $data['reports:pl:numrecords'] = $totalPLRecords;
        $data['reports:pl:numwith'] = $totalWithPL;
        $data['reports:pl:numwithout'] = $totalWithoutPL;
        $data['reports:pl:numwithavggcse'] = $totalWithAvgGCSE;
        $data['reports:pl:numwithoutavggcse'] = $totalWithOutAvgGCSE;
        
        // Averages
        $data['reports:pl:avggcsescore'] = $avgGcseScore;
        $data['reports:pl:avgnumrecords'] = $avgPLRecords;
        
        // Percentages
        $data['reports:pl:percentwith'] = $percentWithPL;
        $data['reports:pl:percentwithout'] = $percentWithoutPL;
        $data['reports:pl:percentwithavggcse'] = $percentWithAvgGCSE;
        $data['reports:pl:percentwithoutavggcse'] = $percentWithoutAvgGCSE;
        
        // Eng & Maths
        $data['reports:pl:enggcse'] = $engGCSE;
        $data['reports:pl:mathsgcse'] = $mathsGCSE;
        
        
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
                    $TPL->load( $this->CFG->dirroot . $this->path . 'tpl/elbp_prior_learning/'.$params['type'].'.html' );
                    $TPL->display();
                } catch (\ELBP\ELBPException $e){
                    echo $e->getException();
                }
                exit;                
                
            break;
        }
        
    }
    
    /**
     * For the loaded student, get their english GCSE prior learning (if they have one)
     * @global type $DB
     * @param type $obj
     * @param type $params
     */
    public function _callHook_English_GCSE($obj, $params){
               
       if (!$this->isEnabled()) return false;
       if (!isset($obj->student->id)) return false;
                
       // Load student
       $this->loadStudent($obj->student->id);
       
       $PL = new \UserPriorLearning();
       $prior = $PL->retrieve_user_plearn($this->student->id);
              
       if ($prior)
       {
           foreach($prior as $qual)
           {
               
               if ($qual->qualname == 'GCSE' && ($qual->subject == 'English' || $qual->subject == 'English Language'))
               {
                   return $qual->grade;
               }
               
           }
       }
       
       return get_string('na', 'block_bcgt');
       
    }
    
    /**
     * For the loaded student, get their maths GCSE prior learning (if they have one)
     * @global type $DB
     * @param type $obj
     * @param type $params
     */
    public function _callHook_Maths_GCSE($obj, $params){
                
        if (!$this->isEnabled()) return false;
        if (!isset($obj->student->id)) return false;
                
        // Load student
        $this->loadStudent($obj->student->id);
        
        $PL = new \UserPriorLearning();
        $prior = $PL->retrieve_user_plearn($this->student->id);

        if ($prior)
        {
            foreach($prior as $qual)
            {

                if ($qual->qualname == 'GCSE' && $qual->subject == 'Mathematics')
                {
                    return $qual->grade;
                }

            }
        }

        return get_string('na', 'block_bcgt');
        
    }
    
    
    
}