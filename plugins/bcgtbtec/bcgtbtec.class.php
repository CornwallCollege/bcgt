<?php

class bcgtbtec extends bcgt {
    
    const NAME = 'bcgtbtec';
    const TITLE = 'BTEC';
    
    const VERSION = 2014021707;  
    
    function bcgtbtec()
    {
        
    }
    
    public static function get_instance()
    {
        return new bcgtbtec();
    }
    
    public function upgrade()
    {
        global $CFG;
        require_once $CFG->dirroot . '/blocks/bcgt/lib.php';
        //are we actually upgrading or are we installing?
        if(!$plugin = is_plugin_installed(bcgtbtec::NAME))
        {
            $this->install();
        }
        else
        {
            //do we need to upgrade?
            $oldVersion = $plugin->version;
            if($oldVersion < bcgtbtec::VERSION)
            {
                echo "Running BTEC Upgrade<br />";
                //then we run the upgrade!
                require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/db/upgrade.php');
                xmldb_block_bcgtbtec_upgrade($oldVersion);
                $this->update_plugin_version($plugin->id, bcgtbtec::VERSION);
            }
            else
            {
                echo "No BTEC Upgrade required<br />";
            }
        }
    }
    
    public function install()
    {
        echo "Installing BTEC<br />";
        global $CFG;
        //can we find it in the mod folder?
        global $DB;
        $sql = "SELECT * FROM {modules} WHERE name = ?";
        if($DB->get_record_sql($sql, array(bcgtbtec::NAME)))
        {
            echo "We are actually upgrading from the previous version<br />";
            $dbman = $DB->get_manager();
            //then we had it installed the old way! we need to remove it from the 
            //mod folder and we also need to remove the bcgtbtec table and remove the
            //modules enrty.
            $DB->delete_records('modules', array('name'=>bcgtbtec::NAME));

            $sql = "UPDATE {block_bcgt_type_family} 
                SET classfolderlocation = '/blocks/bcgt/plugins/bcgtbtec/classes' WHERE id = ? ";
            $DB->execute($sql, array(2));
            
            $table = new xmldb_table('bcgtbtec');
            if ($dbman->table_exists($table)) $dbman->drop_table($table);
            
            //attempt to delete the old bcgt stuff
            try
            {
                $this->delete_directory($CFG->dirroot.'/mod/bcgtbtec');
            }
            catch(Exception $e)
            {
                echo "Cannot delete old bcgtbtec in mod folder. Please remove this folder. The exception is ".print_object($e);
            }
            
            //we also need to run the new upgrades as the plugin will be installed 
            //with the current number. 
            $record = new stdClass();
            $record->type = 'Final Project';
            $record->bcgttypeid = 4;
            $DB->insert_record('block_bcgt_unit_type', $record);

            $record = new stdClass();
            $record->id = 5;
            $record->type = 'BTEC Lower';
            $record->bcgttypefamilyid = 2;
            $DB->update_record('block_bcgt_type', $record);
        }
        else {
            echo "Importing Data into tables<br />";
            //do the normal install.
            require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/db/install.php');
            xmldb_bcgtbtec_install();
        }
        
        //lets add the plugin. 
        $pluginObj = new stdClass();
        $pluginObj->name = bcgtbtec::NAME;
        $pluginObj->title = bcgtbtec::TITLE;
        $pluginObj->version = bcgtbtec::VERSION;
        $pluginObj->enabled = 1;
        $this->insert_plugin($pluginObj);
        
    }
    
    public function uninstall()
    {
        global $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/db/uninstall.php');
        xmldb_bcgtbtec_uninstall();
    }
    
    public function delete_directory($dir) { 
        if (is_dir($dir)) { 
            $objects = scandir($dir); 
            foreach ($objects as $object) { 
                if ($object != "." && $object != "..") 
                { 
                    if (filetype($dir."/".$object) == "dir") $this->delete_directory($dir."/".$object); else unlink($dir."/".$object); 
                } 
            } 
            reset($objects); 
            rmdir($dir); 
        } 
    }
}

