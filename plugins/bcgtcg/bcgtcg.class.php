<?php

class bcgtcg extends bcgt {
    
    const NAME = 'bcgtplugin';
    const TITLE = 'CG';
    
    const VERSION = 2014011500;  
    
    function bcgtcg()
    {
        
    }
    
    public static function get_instance()
    {
        return new bcgtcg();
    }
    
    public function upgrade()
    {
        //upgrade data
        
        global $CFG;
        require_once $CFG->dirroot . '/blocks/bcgt/lib.php';
        //are we actually upgrading or are we installing?
        if(!$plugin = is_plugin_installed(self::NAME))
        {
            $this->install();
        }
        else
        {
            //do we need to upgrade?
            $oldVersion = $plugin->version;
            if($oldVersion <= self::VERSION)
            {
                //then we run the upgrade!
                require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/db/upgrade.php');
                xmldb_block_bcgtcg_upgrade($oldVersion);
                $this->update_plugin_version($plugin->id, self::VERSION);
            }
        }
    }
    
    public function install()
    {
        //THIS only does data. Any tables or changes to tables must be done in the
        //main block db
        
        global $CFG;
        //do the normal install.
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/db/install.php');
        xmldb_bcgtcg_install();
        
        //lets add the plugin. 
        $pluginObj = new stdClass();
        $pluginObj->name = self::NAME;
        $pluginObj->title = self::TITLE;
        $pluginObj->version = self::VERSION;
        $pluginObj->enabled = 1;
        $this->insert_plugin($pluginObj);
        
    }
    
    public function uninstall()
    {
        //get rid of data
        
        global $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/db/uninstall.php');
        xmldb_bcgtcg_uninstall();
    }
}

