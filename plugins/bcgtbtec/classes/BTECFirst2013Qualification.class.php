<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BTECFirst2013
 *
 * @author mchaney
 */
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/lib.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECQualification.class.php');
class BTECFirst2013Qualification extends BTECQualification{
    //put your code here
    
    const ID = 12;
	const NAME = 'BTEC First 2013';
    
    function BTECFirst2013Qualification($qualID, $params, $loadParams)
	{
		parent::BTECQualification($qualID, $params, $loadParams);
	}
	
	function get_type()
	{
		return BTECFirst2013Qualification::NAME;
	}
	
	function get_class_ID()
	{
		return BTECFirst2013Qualification::ID;
	}
    
    /**
	 * Using the object values inserts the qualification into the database
	 */
	function insert_qualification()
	{
        global $DB;
		//as each qual is different its easier to do this hear. 
		$dataobj = new stdClass();
		$dataobj->name = $this->name;
        $dataobj->additionalname = $this->additionalName;
		$dataobj->code = $this->code;
		$dataobj->credits = $this->credits;
        $dataobj->noyears = $this->noYears;
		$targetQualID = parent::get_target_qual(BTECFirst2013Qualification::ID);
		$dataobj->bcgttargetqualid = $targetQualID;
		$id = $DB->insert_record("block_bcgt_qualification", $dataobj);
		$this->id = $id;
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_INSERTED_QUAL, null, $this->id, null, null, null);         
	}
    
    public static function get_instance($qualID, $params, $loadParams)
    {   
        if(!$params || !isset($params->subtype))
        {
            $subTypeID = optional_param('subtype', -1, PARAM_INT);
            if(!$params)
            {
                $params = new stdClass();
            }
            if($subTypeID)
            {
                $subType = new SubType($subTypeID);
                $params->subType = $subType;
            }
        }
        $params->level = new Level(7);

        return new BTECFirst2013Qualification($qualID, $params, $loadParams);
    }
    
    public function display_student_grid($fullGridView = true, $studentView = true)
    {
        return $this->display_student_grid_btec($fullGridView, $studentView, true);
    }
    
    public function has_sub_criteria()
    {
        return false;
    }
			    
    public function get_credits_display_name()
    {
        return get_string('btecglh', 'block_bcgt');
    }
	
	
	protected function calculate_qual_award($failIfPredicted = false)
	{		
		return $this->calculate_actual_qual_award($failIfPredicted, 10);
	}
    
    public function has_final_grade() {
        return true;
    }
    
//    public function has_min_award()
//    {
//        return true;
//    }
//    
//    public function has_max_award()
//    {
//        return true;
//    }
}

?>
