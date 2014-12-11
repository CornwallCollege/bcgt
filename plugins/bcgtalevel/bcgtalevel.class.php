<?php

class bcgtalevel extends bcgt {
    
    const NAME = 'bcgtalevel';
    const TITLE = 'ALEVEL';
    
    const VERSION = 2014030601;  
    
    function bcgtalevel()
    {
        
    }
    
    public static function get_instance()
    {
        return new bcgtalevel();
    }
    
    public function upgrade()
    {
        global $CFG;
        require_once $CFG->dirroot . '/blocks/bcgt/lib.php';
        //are we actually upgrading or are we installing?
        if(!$plugin = is_plugin_installed(bcgtalevel::NAME))
        {
            $this->install();
        }
        else
        {
            //do we need to upgrade?
            $oldVersion = $plugin->version;
            echo "Checking ALEVEL Upgrade<br />";
            if($oldVersion < bcgtalevel::VERSION)
            {
                echo "Running ALEVEL Upgrade<br />";
                //then we run the upgrade!
                require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/db/upgrade.php');
                xmldb_block_bcgtalevel_upgrade($oldVersion);
                //update the version number
                $this->update_plugin_version($plugin->id, bcgtalevel::VERSION);
            }
            else
            {
                echo "No ALEVEL Upgrade required<br />";
            }
        }
    }
    
    public function install()
    {
        echo "Installing Alevel<br />";
        global $CFG;
        //lets add the plugin. 
        $pluginObj = new stdClass();
        $pluginObj->name = bcgtalevel::NAME;
        $pluginObj->title = bcgtalevel::TITLE;
        $pluginObj->version = bcgtalevel::VERSION;
        $pluginObj->enabled = 1;
        $this->insert_plugin($pluginObj);
        
        //can we find it in the mod folder?
        //do the normal install.
        echo "Doing initial import<br />";
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/db/install.php');
        xmldb_bcgtalevel_install();
        return true;
    }
    
    public function uninstall()
    {
        global $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/db/uninstall.php');
        xmldb_bcgtalevel_uninstall();
    }
}

