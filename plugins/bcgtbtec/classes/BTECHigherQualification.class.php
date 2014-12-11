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
class BTECHigherQualification extends BTECQualification {
	
	const ID = 3;
	const NAME = 'BTECHigher';
    const POINTSPERCREDITS = 5;
	
	function BTECHigherQualification($qualID, $params, $loadParams)
	{
		parent::BTECQualification($qualID, $params, $loadParams);
	}
	
	function get_type()
	{
		return BTECHigherQualification::NAME;
	}
	
	function get_class_ID()
	{
		return BTECHigherQualification::ID;
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
		$targetQualID = parent::get_target_qual(BTECHigherQualification::ID);
		$dataobj->bcgttargetqualid = $targetQualID;
		$id = $DB->insert_record("block_bcgt_qualification", $dataobj);
		$this->id = $id;
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_INSERTED_QUAL, null, $this->id, null, null, null);
	}
    
    public function display_student_grid($fullGridView = true, $studentView = true)
    {
        return $this->display_student_grid_btec($fullGridView, $studentView, true);
    }
    
    public function has_sub_criteria()
    {
        return true;
    }
    
    public static function get_instance($qualID, $params, $loadParams)
    {   
        if(!$params || !isset($params->level))
        {
            $levelID = optional_param('level', -1, PARAM_INT);
            if(!$params)
            {
                $params = new stdClass();
            }
            if($levelID)
            {
                $level = new Level($levelID);
                $params->level = $level;
            } 
        }
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

        return new BTECHigherQualification($qualID, $params, $loadParams);
    }
	
//	/**
//	 * Builds the grid up for the student set from load_student_information
//	 * This function is used to build the form up for the form student_grid_view.php.
//	 * $editing tells us if we are in editing mode (only used when marking a students work)
//	 * $ilpView tells us if we are coming from the ILP, thus no editing or advanced possible
//	 * $studentView tells us if we are looking at a student or a qual in general
//	 * a qual gives you crosses to show where criteria is used in units. 
//	 * Student view gives symbols, values and drop downs to show how that stu is
//	 * doing. 
//	 */
//	public function student_view_grid($editing, $ilpView = false, $studentView = true, $mode)
//	{
//		return $this->get_student_view_grid($editing, $ilpView, $studentView, true, $mode);
//	}
		
	/**
	 * Calculate the predicted award and if its all units
	 * have been awarded then its final else predicted
	 */
	function calculate_predicted_grade()
	{
		return $this->calculate_qual_award(false);
	}
	
/**
	 * This calculates the Qualification Award
	 * If all units have been awarded it can calculate the final grade
	 * if only some units have been awarded it MAY be able to calculate 
	 * the predicted/progress award.
	 * Each unit has credits
	 * At each Qualification award level the unit award is worth a set number of points
	 * Each units points are then credits * points
	 * We can get the total unit points for the users units and calculate an 
	 * average per credit.
	 * This can be pushed out to predict the final award by taking the average and
	 * multiplying it by the total credits of the qual
	 * This points value is then looked up in the database and an award MAY be
	 * able to get it.
	 * Its possible if they only have one PASS (lets say a 7) for them
	 * not to be able to get a full award (If a student gets passes at all
	 * units they may not get an award at all)
	 * @param unknown_type $failIfPredicted
	 */
	protected function calculate_qual_award($failIfPredicted = false)
	{		
		return $this->calculate_actual_qual_award($failIfPredicted, BTECHigherQualification::POINTSPERCREDITS);
	}
    
    public function has_min_award()
    {
        return false;
    }
    
    public function has_max_award()
    {
        return false;
    }
    
    public function has_final_award()
    {
        return false;
    }
	
	public function get_extra_rows_for_export_spec(&$obj, $unit, $rowNum) {
        
        $rowNum = parent::get_extra_rows_for_export_spec($obj, $unit, $rowNum);
        
        if ($unit->get_unit_type_id() == 1){
            $type = 'Core Unit';
        } elseif ($unit->get_unit_type_id() == 2){
            $type = 'APL Unit';
        } else {
            $type = '';
        }
        
        $obj->getActiveSheet()->setCellValue("A{$rowNum}", "Unit Type");
        $obj->getActiveSheet()->setCellValue("B{$rowNum}", $type);
        $rowNum++;
        
        return $rowNum;
        
    }
	
}
