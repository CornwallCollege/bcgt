<?php

function xmldb_block_bcgtplugin_upgrade($oldversion = 0)
{
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < $versionNumber)
    {
        //only do data inmports/.inserts and removals. 
        //all database changes need to be in the main db
    }
}
