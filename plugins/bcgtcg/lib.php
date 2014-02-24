<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 * 
 */

global $CFG;

function run_bcgtcg_initial_import()
{
    //this will process the csv's in data and import the contents
    echo "todo...";
    
}


function require_cg(){
    global $CFG;
    
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php';
    
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGSubType.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGCriteria.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGUnit.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGQualification.class.php';
    
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGHBVRQCriteria.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGHBVRQUnit.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGHBVRQQualification.class.php';
    
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGHBNVQCriteria.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGHBNVQUnit.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGHBNVQQualification.class.php';
    
}

