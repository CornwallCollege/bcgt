<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Criteria.class.php');
class PluginCriteria extends Criteria {
    //put your code here
    
    public function PluginCriteria($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        parent::Criteria($criteriaID, $params, $loadLevel);
    }
    
    public static function get_instance($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        return new PluginCriteria($criteriaID, $params, $loadLevel);
    }
}

?>
