<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 * 
 */

function require_bespoke(){
    global $CFG;
    
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeCriteriaSorter.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeValue.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeAward.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeQualificationAward.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeSubtype.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeCriteria.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeUnit.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeQualification.class.php';
    
}


function run_plugin_initial_import()
{
    //this will process the csv's in data and import the contents
}