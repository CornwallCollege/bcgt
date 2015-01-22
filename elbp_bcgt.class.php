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
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numcorrectcredits", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numabovecredits", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numbelowcredits", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:targetprogress", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numbehindtarget", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numontarget", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numabovetarget", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numwithtargetpredicted", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:percentbehindtarget", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:percentontarget", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:percentabovetarget", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numquals", "getstringcomponent" => "block_bcgt"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numqualscorrectcredits", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numpcrit", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:nummcrit", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numdcrit", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassclosed", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassopen", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numasssubmissions", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numasslate", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassnon", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassbtecp", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassbtecm", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassbtecd", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassbtecpach", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassbtecmach", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassbtecdach", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numasspending", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numass", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numachpcrit", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numachmcrit", "getstringcomponent" => "block_bcgt"));        
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numachdcrit", "getstringcomponent" => "block_bcgt"));        
        
        // Hooks
        $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "Units"));
        $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "Target Grade"));
        $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "A Level Most Recent Grade"));
        $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "A Level Most Recent CETA"));
        $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "Weighted Target Grade"));
        
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
        
        
        if ($version < 2014022000)
        {
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numcorrectcredits", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numabovecredits", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numbelowcredits", "getstringcomponent" => "block_bcgt"));
            $this->version = 2014022000;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}"); 
        }
        
        if ($version < 2014022001)
        {
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:targetprogress", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numbehindtarget", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numontarget", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numabovetarget", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:percentbehindtarget", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:percentontarget", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:percentabovetarget", "getstringcomponent" => "block_bcgt"));
            $this->version = 2014022001;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}"); 
        }
        
        if ($version < 2014022100)
        {
            
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numwithtargetpredicted", "getstringcomponent" => "block_bcgt"));
            $this->version = 2014022100;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}"); 
            
        }
        
        if ($version < 2014022102)
        {
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numquals", "getstringcomponent" => "block_bcgt"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numqualscorrectcredits", "getstringcomponent" => "block_bcgt"));
            $this->version = 2014022102;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
        }

        if ($version < 2014062500)
        {
            $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "A Level Most Recent Grade"));
            $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "A Level Most Recent CETA"));
            $this->version = 2014062500;
            $this->updatePlugin();
            \mtrace("## Inserted lbp_hooks data for plugin: {$this->title}");
        }
        
        if ($version < 2014091100)
        {
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numpcrit", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:nummcrit", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numdcrit", "getstringcomponent" => "block_bcgt"));  
            $this->version = 2014091100;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
        }
                
        if ($version < 2014100600)
        {
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numasspending", "getstringcomponent" => "block_bcgt"));        
            $this->version = 2014100600;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
        }
        
        if ($version < 2014100800)
        {
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numass", "getstringcomponent" => "block_bcgt"));        
            $this->version = 2014100800;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
        }
        
        if ($version < 2014101000)
        {
            
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassclosed", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassopen", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numasssubmissions", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numasslate", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassnon", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassbtecp", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassbtecm", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassbtecd", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassbtecpach", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassbtecmach", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numassbtecdach", "getstringcomponent" => "block_bcgt"));        

            $this->version = 2014101000;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
            
        }
        
        if ($version < 2014110500)
        {
            $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "Weighted Target Grade"));
            $this->version = 2014110500;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
        }
        
        if ($version < 2015011200)
        {
            
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numachpcrit", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numachmcrit", "getstringcomponent" => "block_bcgt"));        
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bcgt:numachdcrit", "getstringcomponent" => "block_bcgt"));  
            $this->version = 2015011200;
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
     * @param type $filters
     * @param $obj - If a qual level is being used in the report then this is an arry of quals
     * @param $obj2 - If a qual level is being used in the report then this ia an array of courses that was used to find the students
     */
    public function getAllReportingData($students, $elements, $filters = false, $obj = false, $obj2 = false)
    {
        
        global $DB;
                        
        if (!$students || !$elements) return false;
                
        $now = time();
        
        $qualIDsArray = array();
        $courseIDsArray = array();
        
        if (is_array($obj))
        {
            foreach($obj as $o)
            {
                $qualIDsArray[] = $o->get_id();
            }
        }
        elseif (is_object($obj))
        {
            $qualIDsArray[] = $obj->get_id();
        }
        
        if (!$qualIDsArray) $qualIDsArray = false;
            
        
        
        if (is_array($obj2)){
            foreach($obj2 as $o){
                $courseIDsArray[] = $o->id;
            }
        } elseif (is_object($obj2)){
            $courseIDsArray[] = $obj2->id;
        }
        
        if (!$courseIDsArray) $courseIDsArray = false;
        
        
        
        
        
        $elementNames = array();
        foreach($elements as $elementID){
            $elementNames[] = $this->getReportingElementName($elementID);
        }
                                
        // Elements for checking criteria things
        $elementsCriteriaArray = array();
        $elementsCriteriaArray[] = 'reports:bcgt:numass';
        $elementsCriteriaArray[] = 'reports:bcgt:numassclosed';
        $elementsCriteriaArray[] = 'reports:bcgt:numassopen';
        $elementsCriteriaArray[] = 'reports:bcgt:numasspending';
        $elementsCriteriaArray[] = 'reports:bcgt:avgnumasssubmissions';
        $elementsCriteriaArray[] = 'reports:bcgt:avgnumasslate';
        $elementsCriteriaArray[] = 'reports:bcgt:avgnumassnon';
        $elementsCriteriaArray[] = 'reports:bcgt:numassbtecp';
        $elementsCriteriaArray[] = 'reports:bcgt:numassbtecm';
        $elementsCriteriaArray[] = 'reports:bcgt:numassbtecd';
        $elementsCriteriaArray[] = 'reports:bcgt:numassbtecpach';
        $elementsCriteriaArray[] = 'reports:bcgt:numassbtecmach';
        $elementsCriteriaArray[] = 'reports:bcgt:numassbtecdach';
        $elementsCriteriaArray[] = 'reports:bcgt:numdcrit';
        $elementsCriteriaArray[] = 'reports:bcgt:nummcrit';
        $elementsCriteriaArray[] = 'reports:bcgt:numpcrit';
        $elementsCriteriaArray[] = 'reports:bcgt:numachpcrit';
        $elementsCriteriaArray[] = 'reports:bcgt:numachmcrit';
        $elementsCriteriaArray[] = 'reports:bcgt:numachdcrit';
        
        
        $elementsCreditsArray = array();
        $elementsCreditsArray[] = 'reports:bcgt:numcredits';
        $elementsCreditsArray[] = 'reports:bcgt:numexpectedcredits';
        $elementsCreditsArray[] = 'reports:bcgt:percentcorrectcredits';
        $elementsCreditsArray[] = 'reports:bcgt:percentabovecredits';
        $elementsCreditsArray[] = 'reports:bcgt:percentbelowcredits';
        $elementsCreditsArray[] = 'reports:bcgt:qualcredits';
        $elementsCreditsArray[] = 'reports:bcgt:numcorrectcredits';
        $elementsCreditsArray[] = 'reports:bcgt:numabovecredits';
        $elementsCreditsArray[] = 'reports:bcgt:numbelowcredits';

        $elementsTargetsArray = array();
        $elementsTargetsArray[] = 'numontarget';
        $elementsTargetsArray[] = 'numbehindtarget';
        $elementsTargetsArray[] = 'numabovetarget';
        $elementsTargetsArray[] = 'targetprogress';
        

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
        $btecPCriteriaArray = array();
        $btecMCriteriaArray = array();
        $btecDCriteriaArray = array();
        $btecPCriteriaAchievedArray = array();
        $btecMCriteriaAchievedArray = array();
        $btecDCriteriaAchievedArray = array();
        
        
        $qualCorrectCreditsArray = array(); // Not used yet...
        
        $totalAheadOfTarget = 0;
        $totalBehindTarget = 0;
        $totalOnTarget = 0;
        
        $totalWithTargetAndPredicted = 0;
        $targetProgress = '-';
        
        $assignmentsArray = array();
        $assignmentsOpenArray = array();
        $assignmentsClosedArray = array();
        $assignmentsPendingArray = array();
        $assignmentsSubmitted = array();
        $assignmentsLate = array();
        $assignmentsNonSubmitted = array();
        
        $assignmentPCriteriaArray = array();
        $assignmentMCriteriaArray = array();
        $assignmentDCriteriaArray = array();
        $assignmentPCriteriaCount = 0;
        $assignmentMCriteriaCount = 0;
        $assignmentDCriteriaCount = 0;
        $assignmentPCriteriaAchievedArray = array();
        $assignmentMCriteriaAchievedArray = array();
        $assignmentDCriteriaAchievedArray = array();
        $assignmentPCriteriaAchievedCount = 0;
        $assignmentMCriteriaAchievedCount = 0;
        $assignmentDCriteriaAchievedCount = 0;
        $totalBtecPAssignmentCriteria = array();
        $totalBtecMAssignmentCriteria = array();
        $totalBtecDAssignmentCriteria = array();
        
        
        
        $qualArray = array();
        
        $PL = new \UserPriorLearning();
        $R = new \Reporting();
        
        $load = new \stdClass();
        
        // Higher load level if we are counting PMD criteria for BTECs
        if (array_intersect($elementsCriteriaArray, $elementNames)){
            $load->loadLevel = \Qualification::LOADLEVELCRITERIA;
        } else {
            $load->loadLevel = \Qualification::LOADLEVELMIN;
        }
        
        $load->loadAward = true;
                
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
            }
            
            // target grade?            
            $records = $R->get_users_target_grades($student->id);
            if ($records && count($records) > 0)
            {
                $totalWithTargetGrade++;
            }
            
            
            // Credits
            if (array_intersect($elementsCreditsArray, $elementNames))
            {
                
                $userExpectedCredits = \get_users_expected_credits($this->student->id);
                $userCredits = \get_users_credits($this->student->id);

                $expectedCredits += $userExpectedCredits;
                $credits += $userCredits;

                if ($userCredits == $userExpectedCredits) $totalCorrectCredits++;
                elseif ($userCredits < $userExpectedCredits) $totalBelowCredits++;
                elseif ($userCredits > $userExpectedCredits) $totalAboveCredits++;
            
            }
                        
            $studTargetProgress = array();
            
            // Count number of quals they haev which have a target grade & predicted grade
            if ($quals)
            {
                
                foreach($quals as $qual)
                {
                    
                    // If we have specified qualids, only do those quals
                    if ($qualIDsArray !== false && !in_array($qual->id, $qualIDsArray)){
                        continue;
                    }
                    
                    $qualArray[$qual->id] = $qual->id;       
                    
                    $targetRanking = false;
                    $predictedRanking = false;
                    
                    $qualObj = \Qualification::get_qualification_class_id($qual->id, $load);
                    if (!$qualObj) continue;
                    
                    $qualObj->load_student_information($student->id, $load);
                    
                    // Target progress stuff
                    if (array_intersect($elementsTargetsArray, $elementNames))
                    {
                    
                        // get the target grade for this qual
                        $userCourseTarget = new \UserCourseTarget();
                        $targetGrade = $userCourseTarget->retrieve_users_target_grades($student->id, $qual->id);
                        if($targetGrade)
                        {
                            $targetGradeObj = $targetGrade[$qual->id];
                            if($targetGradeObj && isset($targetGradeObj->breakdown))
                            {
                                $breakdown = $targetGradeObj->breakdown;
                                if($breakdown)
                                {
                                    $targetRanking = $breakdown->get_ranking();
                                }
                            }
                        }

                        // get the predicted grade for this qual
                        $getPredicted = $DB->get_record_sql("SELECT b.*
                                                             FROM {block_bcgt_user_award} a
                                                             INNER JOIN {block_bcgt_target_breakdown} b ON b.id = a.bcgtbreakdownid
                                                             WHERE a.userid = ? AND a.bcgtqualificationid = ? AND a.type = 'Predicted'", array($student->id, $qual->id));
                        if ($getPredicted)
                        {
                            $predictedRanking = $getPredicted->ranking;
                        }

                        if ($targetRanking && $predictedRanking){
                            $studTargetProgress[$qual->id] = array(
                                'target_ranking' => $targetRanking,
                                'predicted_ranking' => $predictedRanking
                            );

                        }
                    
                    }
                    
                    
                    // Assignment linking stuff
                    if (array_intersect($elementsCriteriaArray, $elementNames))
                    {
                        
                        $units = $qualObj->get_units();
                        if ($units)
                        {
                            foreach($units as $unit)
                            {
                                                     
                                // Is student doing the unit?
                                if (!$unit->is_student_doing())
                                {
                                    continue;
                                }
                                                                
                                // Simple count of criteria - BTEC only
                                if ( strpos($qualObj->get_type(), 'BTEC') !== false && array_intersect( array('reports:bcgt:numpcrit', 'reports:bcgt:nummcrit', 'reports:bcgt:numdcrit', 'reports:bcgt:numachpcrit', 'reports:bcgt:numachmcrit', 'reports:bcgt:numachdcrit'), $elementNames ))
                                {
                                    
                                    // P Criteria
                                    $check = $DB->get_records_select("block_bcgt_criteria", "bcgtunitid = ? AND name LIKE 'P%'", array($unit->get_id()));
                                    if ($check)
                                    {
                                        foreach($check as $crit)
                                        {
                                            if (!array_key_exists($crit->id, $btecPCriteriaArray))
                                            {
                                                $btecPCriteriaArray[$crit->id] = $crit->id;
                                            }
                                        }
                                    }
                                    
                                    // M Criteria
                                    $check = $DB->get_records_select("block_bcgt_criteria", "bcgtunitid = ? AND name LIKE 'M%'", array($unit->get_id()));
                                    if ($check)
                                    {
                                        foreach($check as $crit)
                                        {
                                            if (!array_key_exists($crit->id, $btecMCriteriaArray))
                                            {
                                                $btecMCriteriaArray[$crit->id] = $crit->id;
                                            }
                                        }
                                    }
                                    
                                    // D Criteria
                                    $check = $DB->get_records_select("block_bcgt_criteria", "bcgtunitid = ? AND name LIKE 'D%'", array($unit->get_id()));
                                    if ($check)
                                    {
                                        foreach($check as $crit)
                                        {
                                            if (!array_key_exists($crit->id, $btecDCriteriaArray))
                                            {
                                                $btecDCriteriaArray[$crit->id] = $crit->id;
                                            }
                                        }
                                    }
                                                                        
                                    // Achieved
                                    if ($btecPCriteriaArray)
                                    {
                                        foreach($btecPCriteriaArray as $P)
                                        {
                                            $userValue = $DB->get_record_sql("SELECT uc.*
                                                                              FROM {block_bcgt_user_criteria} uc
                                                                              INNER JOIN {block_bcgt_criteria} c ON c.id = uc.bcgtcriteriaid
                                                                              WHERE uc.userid = ? AND uc.bcgtqualificationid = ? AND uc.bcgtcriteriaid = ? AND c.bcgtunitid = ?", array($student->id, $qual->id, $P, $unit->get_id()));
                                            if ($userValue)
                                            {
                                                $userValueObj = new \Value($userValue->bcgtvalueid);
                                                if ($userValueObj->is_criteria_met_bool())
                                                {
                                                    $btecPCriteriaAchievedArray[] = $P;
                                                }
                                            }
                                        }
                                    }
                                    
                                    
                                    if ($btecMCriteriaArray)
                                    {
                                        foreach($btecMCriteriaArray as $M)
                                        {

                                            $userValue = $DB->get_record_sql("SELECT uc.*
                                                                              FROM {block_bcgt_user_criteria} uc
                                                                              INNER JOIN {block_bcgt_criteria} c ON c.id = uc.bcgtcriteriaid
                                                                              WHERE uc.userid = ? AND uc.bcgtqualificationid = ? AND uc.bcgtcriteriaid = ? AND c.bcgtunitid = ?", array($student->id, $qual->id, $M, $unit->get_id()));
                                            
                                            if ($userValue)
                                            {
                                                $userValueObj = new \Value($userValue->bcgtvalueid);
                                                if ($userValueObj->is_criteria_met_bool())
                                                {
                                                    $btecMCriteriaAchievedArray[] = $M;
                                                }
                                            }
                                        }
                                    }
                                    
                                    
                                    if ($btecDCriteriaArray)
                                    {
                                        foreach($btecDCriteriaArray as $D)
                                        {

                                            $userValue = $DB->get_record_sql("SELECT uc.*
                                                                              FROM {block_bcgt_user_criteria} uc
                                                                              INNER JOIN {block_bcgt_criteria} c ON c.id = uc.bcgtcriteriaid
                                                                              WHERE uc.userid = ? AND uc.bcgtqualificationid = ? AND uc.bcgtcriteriaid = ? AND c.bcgtunitid = ?", array($student->id, $qual->id, $D, $unit->get_id()));
                                            
                                            if ($userValue)
                                            {
                                                $userValueObj = new \Value($userValue->bcgtvalueid);
                                                if ($userValueObj->is_criteria_met_bool())
                                                {
                                                    $btecDCriteriaAchievedArray[] = $D;
                                                }
                                            }
                                        }
                                    }
                                    
                                    
                                }
                                                                
                                // Assignment criteria linking stuff
                                if ($qualIDsArray && $courseIDsArray)
                                {
                                    
                                    // To start off with, get the number of open/closed/pending assignments
                                    if (array_intersect( array('reports:bcgt:numass', 'reports:bcgt:numassclosed', 'reports:bcgt:numassopen', 'reports:bcgt:numasspending', 'reports:bcgt:numasssubmissions', 'reports:bcgt:numasslate', 'reports:bcgt:numassnon', 'reports:bcgt:numassbtecp', 'reports:bcgt:numassbtecm', 'reports:bcgt:numassbtecd', 'reports:bcgt:numassbtecpach', 'reports:bcgt:numassbtecmach', 'reports:bcgt:numassbtecdach'), $elementNames ))
                                    {
                                        
                                        $modules = \bcgt_get_course_modules_in_course_qual($courseIDsArray, $qualIDsArray);
                                                                                                       
                                        // Count up those that are closed, open or pending                                        
                                        if ($modules)
                                        {
                                            foreach($modules as $module)
                                            {
                                                                                                
                                                $startDateField = $module->moduleInfo->modtablestartdatefname;
                                                $dueDateField = $module->moduleInfo->modtableduedatefname;
                                               
                                                $assignmentsArray[$module->id] = true;
                                                
                                                // Closed?
                                                if ($module->$dueDateField <= $now)
                                                {
                                                    $assignmentsClosedArray[$module->id] = true;
                                                }
                                                // Pending? - Future
                                                elseif($module->$startDateField >= $now)
                                                {
                                                    $assignmentsPendingArray[$module->id] = true;
                                                }
                                                // Open
                                                elseif( $module->$startDateField < $now && $module->$dueDateField > $now )
                                                {
                                                    $assignmentsOpenArray[$module->id] = true;
                                                }
                                                
                                                // Submitted/Late/Non-Submitted?
                                                if (array_intersect( array('reports:bcgt:numasssubmissions', 'reports:bcgt:numasslate', 'reports:bcgt:numassnon'), $elementNames ))
                                                {
                                                    
                                                    $submissionTable = $module->moduleInfo->modsubmissiontable;
                                                    $submissionUserIDField = $module->moduleInfo->submissionuserfname;
                                                    $submissionDateField = $module->moduleInfo->submissiondatefname;
                                                    $submissionInstanceField = $module->moduleInfo->submissionmodidfname;
                                                    
                                                    // Is there a submission for this user and this assignment?
                                                    $assignmentSubmission = $DB->get_record($submissionTable, array(
                                                        $submissionInstanceField => $module->id,
                                                        $submissionUserIDField => $student->id
                                                    ));
                                                    
                                                    if ($assignmentSubmission)
                                                    {
                                                        
                                                        $submissionTime = $assignmentSubmission->$submissionDateField;
                                                                
                                                        // Was it on time?
                                                        if ($submissionTime <= $module->$dueDateField)
                                                        {
                                                            $assignmentsSubmitted[$module->id . ":" . $student->id] = true;
                                                        }
                                                        
                                                        // Was it late?
                                                        elseif ($submissionTime > $module->$dueDateField)
                                                        {
                                                            $assignmentsLate[$module->id . ":" . $student->id] = true;
                                                        }
                                                        
                                                    }
                                                    else
                                                    {
                                                        
                                                        // Has the due date passed? In which case it's a non-submission
                                                        if ($module->$dueDateField < $now)
                                                        {
                                                            $assignmentsNonSubmitted[$module->id . ":" . $student->id] = true;
                                                        }
                                                        
                                                    }
                                                                                                        
                                                }
                                                
                                                
                                                                                                
                                                // BTEC - Count PMD criteria
                                                if (strpos($qualObj->get_type(), 'BTEC') !== false)
                                                {
                                                    
                                                    $moduleCriteria = bcgt_get_criteria_on_course_module($module->courseModuleID, $qualObj->get_id(), $unit->get_id());
                                                    
                                                    if ($moduleCriteria)
                                                    {
                                                        foreach($moduleCriteria as $modCrit)
                                                        {
                                                            
                                                            // P
                                                            if (stripos($modCrit->name, "P") === 0)
                                                            {
                                                                
                                                                if (!array_key_exists($modCrit->id, $assignmentPCriteriaArray))
                                                                {
                                                                    // Add to count of Ps
                                                                    $assCritObj = new \Criteria($modCrit->id);
                                                                    $assCritObj->load_student_information($student->id, $qualObj->get_id());
                                                                    $assignmentPCriteriaArray[$modCrit->id] = $assCritObj;
                                                                }
                                                                else
                                                                {
                                                                    $assignmentPCriteriaArray[$modCrit->id]->load_student_information($student->id, $qualObj->get_id());
                                                                }
                                                                
                                                                
                                                                // Is it achieved?
                                                                $assCritObj = $assignmentPCriteriaArray[$modCrit->id];
                                                                                                                                
                                                                if ($assCritObj->is_met())
                                                                {
                                                                    if (!array_key_exists($modCrit->id . ":" . $student->id, $assignmentPCriteriaAchievedArray))
                                                                    {
                                                                        $assignmentPCriteriaAchievedArray[$modCrit->id . ":" . $student->id] = true;
                                                                        $assignmentPCriteriaAchievedCount++;
                                                                    }
                                                                }
                                                                
                                                                
                                                                // Total to use for achieved
                                                                if (!array_key_exists($modCrit->id . ":" . $student->id, $totalBtecPAssignmentCriteria))
                                                                {
                                                                    $totalBtecPAssignmentCriteria[$modCrit->id . ":" . $student->id] = $modCrit->id;
                                                                }
                                                                
                                                                
                                                                
                                                            }
                                                            
                                                            
                                                            // M
                                                            elseif (stripos($modCrit->name, "M") === 0)
                                                            {
                                                                
                                                                if (!array_key_exists($modCrit->id, $assignmentMCriteriaArray))
                                                                {
                                                                    // Add to count of Ps
                                                                    $assCritObj = new \Criteria($modCrit->id);
                                                                    $assCritObj->load_student_information($student->id, $qualObj->get_id());
                                                                    $assignmentMCriteriaArray[$modCrit->id] = $assCritObj;
                                                                }
                                                                else
                                                                {
                                                                    $assignmentMCriteriaArray[$modCrit->id]->load_student_information($student->id, $qualObj->get_id());
                                                                }
                                                                
                                                                
                                                                // Is it achieved?
                                                                $assCritObj = $assignmentMCriteriaArray[$modCrit->id];
                                                                if ($assCritObj->is_met())
                                                                {
                                                                    if (!array_key_exists($modCrit->id . ":" . $student->id, $assignmentMCriteriaAchievedArray))
                                                                    {
                                                                        $assignmentMCriteriaAchievedArray[$modCrit->id . ":" . $student->id] = true;
                                                                        $assignmentMCriteriaAchievedCount++;
                                                                    }
                                                                    
                                                                }
                                                                
                                                                
                                                                // Total to use for achieved
                                                                if (!array_key_exists($modCrit->id . ":" . $student->id, $totalBtecMAssignmentCriteria))
                                                                {
                                                                    $totalBtecMAssignmentCriteria[$modCrit->id . ":" . $student->id] = $modCrit->id;
                                                                }
                                                                
                                                                
                                                            }
                                                            // D
                                                            elseif (stripos($modCrit->name, "D") === 0)
                                                            {
                                                                
                                                                if (!array_key_exists($modCrit->id, $assignmentDCriteriaArray))
                                                                {
                                                                    // Add to count of Ps
                                                                    $assCritObj = new \Criteria($modCrit->id);
                                                                    $assCritObj->load_student_information($student->id, $qualObj->get_id());
                                                                    $assignmentDCriteriaArray[$modCrit->id] = $assCritObj;
                                                                }
                                                                else
                                                                {
                                                                    $assignmentDCriteriaArray[$modCrit->id]->load_student_information($student->id, $qualObj->get_id());
                                                                }
                                                                
                                                                
                                                                // Is it achieved?
                                                                $assCritObj = $assignmentDCriteriaArray[$modCrit->id];
                                                                if ($assCritObj->is_met())
                                                                {
                                                                    if (!array_key_exists($modCrit->id . ":" . $student->id, $assignmentDCriteriaAchievedArray))
                                                                    {
                                                                        $assignmentDCriteriaAchievedArray[$modCrit->id . ":" . $student->id] = true;
                                                                        $assignmentDCriteriaAchievedCount++;
                                                                    }
                                                                }
                                                                
                                                                
                                                                // Total to use for achieved
                                                                if (!array_key_exists($modCrit->id . ":" . $student->id, $totalBtecDAssignmentCriteria))
                                                                {
                                                                    $totalBtecDAssignmentCriteria[$modCrit->id . ":" . $student->id] = $modCrit->id;
                                                                }
                                                                
                                                                
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }                    
                }
            }
            
            $cntQualsProgress = count($studTargetProgress);
            
            if ($cntQualsProgress > 0){
                $totalWithTargetAndPredicted++;
            }
            
            
            if ($studTargetProgress)
            {
                
                $progressScore = 0;
                
                foreach($studTargetProgress as $array)
                {
                    
                    // We divide by the number of quals, so that one student only ever counts as 1 point, regardless
                    // of how many quals they are on and how many target progress differences they have
                    if ($array['predicted_ranking'] > $array['target_ranking']){
                        $totalAheadOfTarget += (1 / $cntQualsProgress);
                        $progressScore += 3;
                    }
                    elseif ($array['target_ranking'] == $array['predicted_ranking']) {
                        $totalOnTarget += (1 / $cntQualsProgress);
                        $progressScore += 2;
                    }
                    elseif ($array['target_ranking'] > $array['predicted_ranking']) {
                        $totalBehindTarget += (1 / $cntQualsProgress);
                        $progressScore += 1;
                    }
                    
                                            
                }
                
                // Sort so highest is top
                $progressAvg = $progressScore / $cntQualsProgress;
                                
                
            }                    
                
            
            
            
                        
            // If only one student, get their target grade and display
            if ($totalStudents == 1)
            {
                
                
                if (in_array('reports:bcgt:targetgrade', $elementNames))
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
                
                }
                
                
                if (in_array('reports:bcgt:weightedtargetgrade', $elementNames))
                {
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
                }
                
                if (in_array('reports:bcgt:quals', $elementNames))
                {
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
                }
                
                
                if (array_intersect($elementsCreditsArray, $elementNames))
                {
                    
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
                
                if (isset($progressAvg)){
                    if ($progressAvg >= 2.5){
                        $targetProgress = 'AHEAD';
                    } elseif($progressAvg >= 1.5){
                        $targetProgress = 'ON';
                    } else {
                        $targetProgress = 'BEHIND';
                    }
                }
                
                
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
        $data['reports:bcgt:numcorrectcredits'] = $totalCorrectCredits;
        $data['reports:bcgt:numabovecredits'] = $totalAboveCredits;
        $data['reports:bcgt:numbelowcredits'] = $totalBelowCredits;
        $data['reports:bcgt:qualcredits'] = $creditsOnQual;
        $data['reports:bcgt:numontarget'] = $totalOnTarget;
        $data['reports:bcgt:numbehindtarget'] = $totalBehindTarget;
        $data['reports:bcgt:numabovetarget'] = $totalAheadOfTarget;
        $data['reports:bcgt:numwithtargetpredicted'] = $totalWithTargetAndPredicted;
        $data['reports:bcgt:targetprogress'] = $targetProgress;
        $data['reports:bcgt:numquals'] = count($qualArray);
        $data['reports:bcgt:numqualscorrectcredits'] = count($qualCorrectCreditsArray); // ?
        $data['reports:bcgt:numpcrit'] = count($btecPCriteriaArray);
        $data['reports:bcgt:nummcrit'] = count($btecMCriteriaArray);
        $data['reports:bcgt:numdcrit'] = count($btecDCriteriaArray);
        $data['reports:bcgt:numass'] = count($assignmentsArray);
        $data['reports:bcgt:numassopen'] = count($assignmentsOpenArray);
        $data['reports:bcgt:numassclosed'] = count($assignmentsClosedArray);
        $data['reports:bcgt:numasspending'] = count($assignmentsPendingArray);
        $data['reports:bcgt:numasssubmissions'] = count($assignmentsSubmitted);
        $data['reports:bcgt:numasslate'] = count($assignmentsLate);
        $data['reports:bcgt:numassnon'] = count($assignmentsNonSubmitted);
        $data['reports:bcgt:numassbtecp'] = count($assignmentPCriteriaArray);
        $data['reports:bcgt:numassbtecm'] = count($assignmentMCriteriaArray);
        $data['reports:bcgt:numassbtecd'] = count($assignmentDCriteriaArray);
        $data['reports:bcgt:numassbtecpach'] = $assignmentPCriteriaAchievedCount . ' / ' . count($totalBtecPAssignmentCriteria);
        $data['reports:bcgt:numassbtecmach'] = $assignmentMCriteriaAchievedCount . ' / ' . count($totalBtecMAssignmentCriteria);
        $data['reports:bcgt:numassbtecdach'] = $assignmentDCriteriaAchievedCount . ' / ' . count($totalBtecDAssignmentCriteria);
        $data['reports:bcgt:numachpcrit'] = count($btecPCriteriaAchievedArray);
        $data['reports:bcgt:numachmcrit'] = count($btecMCriteriaAchievedArray);
        $data['reports:bcgt:numachdcrit'] = count($btecDCriteriaAchievedArray);

        // Percentages
        // [not at the moment]

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
    
    
    public function getSimpleQualsTargets(){
        
        if (!$this->student) return false;
        
        global $CFG, $DB;
        
        $return = array();
        $quals = \get_users_quals($this->student->id, 5);
        
        $loadParams = new \stdClass();
        $loadParams->loadLevel = \Qualification::LOADLEVELMIN;
        $loadParams->loadAward = true;
            
        if ($quals)
        {
            foreach($quals as &$qual)
            {
                
                $qualObj = \Qualification::get_qualification_class_id($qual->id, $loadParams);               
                $qualObj->load_student_information($this->student->id, $loadParams);
                
                $qualObj->targetgrade = '';
                $qualObj->predictedgrade = '';

                // get the target grade for this qual
                $userCourseTarget = new \UserCourseTarget();
                $targetGrade = $userCourseTarget->retrieve_users_target_grades($this->student->id, $qual->id);
                if($targetGrade)
                {
                    $targetGradeObj = $targetGrade[$qual->id];
                    if($targetGradeObj && isset($targetGradeObj->breakdown))
                    {
                        $breakdown = $targetGradeObj->breakdown;
                        if($breakdown)
                        {
                            $qualObj->targetgrade = $breakdown->get_target_grade();
                        }
                    }
                }

                // get the predicted grade for this qual
                $getPredicted = $DB->get_record_sql("SELECT b.*
                                                     FROM {block_bcgt_user_award} a
                                                     INNER JOIN {block_bcgt_target_breakdown} b ON b.id = a.bcgtbreakdownid
                                                     WHERE a.userid = ? AND a.bcgtqualificationid = ? AND a.type = 'Predicted'", array($this->student->id, $qual->id));
                if ($getPredicted)
                {
                    $qualObj->predictedgrade = $getPredicted->targetgrade;
                }
                
                $return[] = $qualObj;
                
            }
        }
        
        
        return $return;
        
    }
    
   
    public function getUserTargetGrades($simple = false, $courseID = -1){
        
        global $CFG;
        
        if (!$this->student) return false;
        
        require_once $CFG->dirroot . '/blocks/bcgt/lib.php';
        
        $R = new \Reporting();
        $records = $R->get_users_target_grades($this->student->id, -1, $courseID);
                
        $array = array();
        
        if ($records)
        {
            foreach($records as $record)
            {
                
                if (isset($record->targetgrade) && $record->targetgrade->get_id() > 0)
                {
                    if ($simple){
                        $array[$record->qualname] = $record->targetgrade->get_grade();
                    } else {
                        $array[] = "<span title='{$record->qualname}'>".$record->targetgrade->get_grade()."</span>";
                    }
                    
                }
                elseif (isset($record->breakdown) && $record->breakdown->get_id() > 0)
                {
                    if ($simple){
                        $array[$record->qualname] = $record->breakdown->get_target_grade();
                    } else {
                        $array[] = "<span title='{$record->qualname}'>".$record->breakdown->get_target_grade()."</span>";
                    }
                    
                }
                
            }
        }
                        
        if ($simple) return $array;
        
        else return ($array) ? implode(", ", $array) : 'N/A';
        
    }
    
    
    
    
    public function getUserWeightedTargetGrades($simple = false, $courseID = -1){
        
        global $CFG;
        
        if (!$this->student) return false;
        
        require_once $CFG->dirroot . '/blocks/bcgt/lib.php';
        
        $R = new \Reporting();
        $records = $R->get_users_target_grades($this->student->id, -1, $courseID);
                
        $array = array();
        
        if ($records)
        {
            foreach($records as $record)
            {
                
                if (isset($record->weightedtargetgrade) && $record->weightedtargetgrade->get_id() > 0)
                {
                    if ($simple){
                        $array[$record->qualname] = $record->weightedtargetgrade->get_grade();
                    } else {
                        $array[] = "<span title='{$record->qualname}'>".$record->weightedtargetgrade->get_grade()."</span>";
                    }
                    
                }
                elseif (isset($record->weightedbreakdown) && $record->weightedbreakdown->get_id() > 0)
                {
                    if ($simple){
                        $array[$record->qualname] = $record->weightedbreakdown->get_target_grade();
                    } else {
                        $array[] = "<span title='{$record->qualname}'>".$record->weightedbreakdown->get_target_grade()."</span>";
                    }
                    
                }
                
            }
        }
                        
        if ($simple) return $array;
        else return ($array) ? implode(", ", $array) : get_string('na', 'block_elbp');
        
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
            
                $output .= "<td>".get_string('mintargetgrades', 'block_bcgt')."</td>";
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
            $loadParams->loadAddUnits = false;
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
        if (!isset($params['courseID'])) $params['courseID'] = -1;
                        
        // Load student
        $this->loadStudent($obj->student->id);
                
        $return = array();
        $return['grades'] = $this->getUserTargetGrades(true, $params['courseID']);       
        return $return;
        
    }
    
    
    public function _callHook_Weighted_Target_Grade($obj, $params)
    {
        
        if (!$this->isEnabled()) return false;
        if (!isset($obj->student->id)) return false;
        if (!isset($params['courseID'])) $params['courseID'] = -1;
                        
        // Load student
        $this->loadStudent($obj->student->id);
                
        $return = array();
        $return['grades'] = $this->getUserWeightedTargetGrades(true, $params['courseID']);       
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
        
        $sorter = new \UnitSorter();
                        
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
                        
                        // Order units
                        uasort($return['quals'][$qualification->get_id()]['units'], array($sorter, "Comparison"));
                        
                    }
                    
                }
                
            }
        }
        
        return $return;
        
    }
    
    public function _callHook_A_Level_Most_Recent_Grade($obj, $params){
        
        if (!$this->isEnabled()) return false;
        if (!isset($obj->student->id)) return false;
        if (!isset($params['courseID'])) return false;
                        
        // Load student
        $this->loadStudent($obj->student->id);
                        
        $return = array();
        
        // Get quals on this course
        $quals = bcgt_get_course_quals($params['courseID']);
        if ($quals)
        {
            foreach($quals as $qual)
            {
                
                // Get units on this qual
                $loadParams = new \stdClass();
                $loadParams->loadLevel = \Qualification::LOADLEVELMIN;
                
                $qualification = \Qualification::get_qualification_class_id($qual->id, $loadParams);
                if ($qualification)
                {
                    
                    $grade = \Qualification::get_most_recent_fa_grade($qualification->get_id(), $obj->student->id);
                    if ($grade){
                        $return['grades'][$qualification->get_name()] = reset($grade)->value;
                    } 
                    
                }
                
            }
        }
        
        return $return;
        
    }
    
    
    
    public function _callHook_A_Level_Most_Recent_CETA($obj, $params){
        
        if (!$this->isEnabled()) return false;
        if (!isset($obj->student->id)) return false;
        if (!isset($params['courseID'])) return false;
                        
        // Load student
        $this->loadStudent($obj->student->id);
                        
        $return = array();
        
        // Get quals on this course
        $quals = bcgt_get_course_quals($params['courseID']);
        if ($quals)
        {
            foreach($quals as $qual)
            {
                
                // Get units on this qual
                $loadParams = new \stdClass();
                $loadParams->loadLevel = \Qualification::LOADLEVELMIN;
                
                $qualification = \Qualification::get_qualification_class_id($qual->id, $loadParams);
                if ($qualification)
                {
                    
                    $grade = \Qualification::get_most_recent_ceta($qualification->get_id(), $obj->student->id);
                    if ($grade){
                        $return['grades'][$qualification->get_name()] = reset($grade)->grade;
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
                
                if ($params['type'] == 'tracker'){
                    $this->loadTracker( $params['id'], $TPL );
                    $TPL->set("student", $this->student);
                }
                
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
    
    
    
    protected function supportsStudentProgress()
    {
        return true;
    }
    
    
    /**
     * Targets can have the definitions:
     * - Each target +1
     * - Set number of targets +1
     * - Set number of targets achieved +1
     */
    protected function getStudentProgressDefinitionForm()
    {
        
        $output = "";
        
        $output .= "<table class='student-progress-definitions'>";
        
            $output .= "<tr>";

                $output .= "<th></th>";
                $output .= "<th>".get_string('value', 'block_elbp')."</th>";
                $output .= "<th>".get_string('description')."</th>";
                $output .= "<th>".get_string('importance', 'block_elbp')."</th>";

            $output .= "</tr>";
        
            $output .= "<tr>";
                $chk = ($this->getSetting('student_progress_definitions_predgrade') == 1) ? 'checked' : '';
                $output .= "<td><input type='checkbox' name='student_progress_definitions_predgrade' value='1' {$chk} /></td>";
                $output .= "<td></td>";
                $output .= "<td>".get_string('studentprogressdefinitions:predgrade', 'block_bcgt')."</td>";
                $output .= "<td><input type='number' min='0.5' step='0.5' class='elbp_smallish' name='student_progress_definition_importance_predgrade' value='{$this->getSetting('student_progress_definition_importance_predgrade')}' /></td>";
            $output .= "</tr>";
            
        
        $output .= "</table>";
        
        return $output;
        
    }
    
     public function calculateStudentProgress(){
        
         global $DB;
         
        $max = 0;
        $num = 0;
        $info = array();
        
        // Enabled
        if ($this->getSetting('student_progress_definitions_predgrade') == 1)
        {
            
            $importance = $this->getSetting('student_progress_definition_importance_predgrade');

            // Get all student's quals with a target grade and predicted grade
            $quals = \get_users_quals($this->student->id, 5);
                        
            // Ahead/Behind/On target            
            $loadParams = new \stdClass();
            $loadParams->loadLevel = \Qualification::LOADLEVELMIN;
            $loadParams->loadAward = true;
            
            if ($quals)
            {
                
                foreach($quals as $qual)
                {
                                        
                    $targetRanking = false;
                    $predictedRanking = false;
                    
                    $qualObj = \Qualification::get_qualification_class_id($qual->id, $loadParams);
                    $qualObj->load_student_information($this->student->id, $loadParams);
                                        
                    // get the target grade for this qual
                    $userCourseTarget = new \UserCourseTarget();
                    $targetGrade = $userCourseTarget->retrieve_users_target_grades($this->student->id, $qual->id);
                    
                    if($targetGrade)
                    {
                        $targetGradeObj = $targetGrade[$qual->id];
                        if($targetGradeObj && isset($targetGradeObj->breakdown))
                        {
                            $breakdown = $targetGradeObj->breakdown;
                            if($breakdown)
                            {
                                $targetRanking = $breakdown->get_ranking();
                            }
                        }
                    }
                    
                    // get the predicted grade for this qual
                    $getPredicted = $DB->get_record_sql("SELECT b.*
                                                         FROM {block_bcgt_user_award} a
                                                         INNER JOIN {block_bcgt_target_breakdown} b ON b.id = a.bcgtbreakdownid
                                                         WHERE a.userid = ? AND a.bcgtqualificationid = ? AND a.type = 'Predicted'", array($this->student->id, $qual->id));
                    if ($getPredicted)
                    {
                        $predictedRanking = $getPredicted->ranking;
                    }
                    
                    if ($targetRanking && $predictedRanking){
                                                
                        // Once for every qual
                        $max += $importance;
                        
                        // If meet or exceed target, add max
                        if ($predictedRanking >= $targetRanking)
                        {
                            $num += $importance;
                            $diff = 100;
                        }
                        else
                        {
                            
                            // Otherwise work out what percentage of the targe tthey are at and
                            // add that % of the importance
                            $diff = ($predictedRanking / $targetRanking) * 100;
                            $val = ($diff / 100) * $importance;
                            $num += $val;
                            
                        }
                        
                        $key = get_string('studentprogress:info:gradetracker:predgrade', 'block_bcgt');
                        $key = str_replace('%q%', $qualObj->get_short_display_name(), $key);
                        $key = str_replace('%g%', $breakdown->get_target_grade(), $key);
                        $info[$key] = array(
                            'percent' => $diff,
                            'value' => $getPredicted->targetgrade
                        );
                        
                                                
                    }
                    
                }
                
            }
                            
        }
                
         
        return array(
            'max' => $max,
            'num' => $num,
            'info' => $info
        );
        
    }
    
    public function saveConfig($settings) {
        
        // Student progress definitions
                         
        // If any of them aren't defined, set their value to 0 for disabled        
        if (!isset($settings['student_progress_definitions_predgrade'])){
            $settings['student_progress_definitions_predgrade'] = 0;
            $settings['student_progress_definition_importance_predgrade'] = 0;
        }


        // If the req ones don't have a valid number as their value, set to disabled
        if (!isset($settings['student_progress_definition_importance_predgrade']) || $settings['student_progress_definition_importance_predgrade'] <= 0) $settings['student_progress_definitions_predgrade'] = 0;
                
        parent::saveConfig($settings);
        
    }
    
    
    
}