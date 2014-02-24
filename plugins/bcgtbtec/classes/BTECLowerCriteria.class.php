<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BTECHigherCriteria
 *
 * @author kdavies
 */
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECCriteria.class.php');
class BTECLowerCriteria extends BTECCriteria{
    
    public function BTECLowerCriteria($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        parent::BTECCriteria($criteriaID, $params, $loadLevel);
    }
    
    public static function get_instance($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        return new BTECLowerCriteria($criteriaID, $params, $loadLevel);
    }
    //put your code here
}

?>
