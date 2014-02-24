<?php

class bcgt {
    
    function bcgt()
    {
        
    }
    
    function install_all_plugins()
    {
        //this will find all folders beneath the plugins folder. 
            //inside each it will look for foldername.class.php
                //instantiate it
                    //call its install function.
        echo "Installing All Plugins <br />";
        set_time_limit(0);
        return $this->execute_command('install');
    }
    
    public function upgrade_plugins()
    {
        return $this->execute_command('upgrade');
    }
    
    public function update_plugin_version($id, $version)
    {
        global $DB;
        $obj = new stdClass();
        $obj->id = $id;
        $obj->version = $version;
        $DB->update_record('block_bcgt_plugins', $obj);
    }
    
    public function uninstall_all_plugins()
    {
        set_time_limit(0);
        return $this->execute_command('uninstall');
    }
    
    protected function execute_command($function)
    {
        echo "Executing ".$function."<br />";
        global $CFG;
        //this will find all of the plugins
        $directories = scandir($CFG->dirroot.'/blocks/bcgt/plugins');
        if($directories)
        {
            //there is always a '.' and a '..' directory
            echo "$function ".(count($directories)-2)." Plugins<br />";
            foreach($directories AS $directory)
            {
                if($directory != '.' && $directory != '..')
                {
                    echo "$function : ".$directory."<br />";
                    //require_once the class
                    require_once($CFG->dirroot.'/blocks/bcgt/plugins/'.$directory.'/'.$directory.'.class.php');
                    $plugin = $directory::get_instance();
                    if($plugin)
                    {
                        $plugin->$function();
                        //events, lang and capibilities as well as styles will have to come from 
                            //the block itself. 
                    }
                    else
                    {
                        echo "There was a problem during the install of ".$directory." no plugin class coule be found<br />";
                    }
                }
            }
        }
        return true;
    }
    
    public function install(){}
    
    public function delete(){}
    
    protected function insert_plugin($pluginObj)
    {
        echo "Inserting plugin into table<br />";
        print_object($pluginObj);
        global $DB;
        $DB->insert_record('block_bcgt_plugins', $pluginObj);
    }
}
