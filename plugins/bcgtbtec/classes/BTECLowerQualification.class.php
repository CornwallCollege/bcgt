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
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/lib.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECQualification.class.php');
class BTECLowerQualification extends BTECQualification {
	
	const ID = 5;
	const NAME = 'BTEC Lower';
	
	function BTECLowerQualification($qualID, $params, $loadParams)
	{
		parent::BTECQualification($qualID, $params, $loadParams);
	}
	
	function get_type()
	{
		return BTECLowerQualification::NAME;
	}
	
	function get_class_ID()
	{
		return BTECLowerQualification::ID;
	}
	
    //TODO for crying out load! I need to put this in a central method!
    
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
		$targetQualID = parent::get_target_qual(BTECLowerQualification::ID);
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
        $params->level = new Level(1);

        return new BTECLowerQualification($qualID, $params, $loadParams);
    }
    
    public function display_student_grid($fullGridView = true, $studentView = true)
    {
        return $this->display_student_grid_btec($fullGridView, $studentView, true);
    }
    
    public function has_sub_criteria()
    {
        return true;
    }
			
	/**
	 * Calculate the predicted award and if its all units
	 * have been awarded then its final else predicted
	 */
	function calculate_predicted_grade()
	{
		return false;
	}
	
	
	protected function calculate_qual_award($failIfPredicted = false)
	{		
		return false;
	}
    
    public function has_min_award()
    {
        return false;
    }
    
    public function has_max_award()
    {
        return false;
    }

}
