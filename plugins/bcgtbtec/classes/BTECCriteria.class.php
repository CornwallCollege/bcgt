<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Criteria.class.php');
class BTECCriteria extends Criteria {
    //put your code here
    const LATE = 'L';
    const NOTATTEMPTED = 'N/A';
    
    public function BTECCriteria($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        parent::Criteria($criteriaID, $params, $loadLevel);
    }
    
    public static function get_instance($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        return new BTECCriteria($criteriaID, $params, $loadLevel);
    }
    
    public function set_flag()
    {
        //if the value is late the set the flag to late
        $value = $this->studentValue;
        if($value)
        {
            $shortValue = $value->get_short_value();
            if($shortValue == BTECCriteria::LATE)
            {
                $this->studentFlag = BTECCriteria::LATE;
            }
            elseif($shortValue == BTECCriteria::NOTATTEMPTED 
                    || $shortValue == '' || $shortValue == NULL)
            {
                $this->studentFlag = "";
            }
        }
        else
        {
            $this->studentFlag = "";
        }
        //if the value is not attempted then set the flag to nothing
    }
}

?>
