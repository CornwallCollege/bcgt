<?php

/*
 * Moodle Gradetracker V1.0 â€“ This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECCriteria.class.php');
class BTECFoundationCriteria extends BTECCriteria{
    
   public function BTECFoundationCriteria($criteriaID, $params, 
           $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        parent::BTECCriteria($criteriaID, $params, $loadLevel);
    }
    
    public static function get_instance($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        return new BTECFoundationCriteria($criteriaID, $params, $loadLevel);
    }
    //put your code here
}

?>
