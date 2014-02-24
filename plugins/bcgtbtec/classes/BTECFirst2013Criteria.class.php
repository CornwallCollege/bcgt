<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BTECFirst2013Criteria
 *
 * @author mchaney
 */
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECCriteria.class.php');
class BTECFirst2013Criteria extends BTECCriteria {
    //put your code here
    public function BTECFirst2013Criteria($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        parent::BTECCriteria($criteriaID, $params, $loadLevel);
    }
    
    public static function get_instance($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        return new BTECFirst2013Criteria($criteriaID, $params, $loadLevel);
    }
    //put your code here
}

?>
