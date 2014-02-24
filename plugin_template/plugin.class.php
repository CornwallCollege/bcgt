<?php

class bcgtplugin extends bcgt {
    
    const NAME = 'bcgtplugin';
    const TITLE = 'plugin';
    
    const VERSION = 2013052200;  
    
    function bcgtplugin()
    {
        
    }
    
    public static function get_instance()
    {
        return new bcgtplugin();
    }
    
    public function upgrade()
    {
        //upgrade data
        
        global $CFG;
        require_once $CFG->dirroot . '/blocks/bcgt/lib.php';
        //are we actually upgrading or are we installing?
        if(!$plugin = is_plugin_installed(bcgtplugin::NAME))
        {
            $this->install();
        }
        else
        {
            //do we need to upgrade?
            $oldVersion = $plugin->version;
            if($oldVersion <= bcgtplugin::VERSION)
            {
                //then we run the upgrade!
                require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtplugin/db/upgrade.php');
                xmldb_block_bcgtplugin_upgrade($oldVersion);
            }
        }
    }
    
    public function install()
    {
        //THIS only does data. Any tables or changes to tables must be done in the
        //main block db
        
        global $CFG;
        //do the normal install.
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtplugin/db/install.php');
        xmldb_bcgtplugin_install();
        
        //lets add the plugin. 
        $pluginObj = new stdClass();
        $pluginObj->name = bcgtplugin::NAME;
        $pluginObj->title = bcgtplugin::TITLE;
        $pluginObj->version = bcgtplugin::VERSION;
        $pluginObj->enabled = 1;
        $this->insert_plugin($pluginObj);
        
    }
    
    public function uninstall()
    {
        //get rid of data
        
        global $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtplugin/db/uninstall.php');
        xmldb_bcgtplugin_uninstall();
    }
}

