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
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECUnit.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECLowerQualification.class.php');
class BTECLowerUnit extends BTECUnit {	
	
    function BTECLowerUnit($unitID, $params, $loadParams)
	{
		parent::BTECUnit($unitID, $params, $loadParams);
	}

	public function get_typeID()
	{
		return BTECLowerQualification::ID;
	}
	
	public function get_type_name()
	{
		return BTECLowerQualification::NAME;
	}
		
//	/**
//	 * This grid produces the unit with all of the students doing that unit
//	 * QualID's is an array of qualID's we are looking at this unit for
//	 * CourseID's is an array of courseID's we are looking at this unit for
//	 * @param unknown_type $editing
//	 * @param unknown_type $qualIDs
//	 * @param unknown_type $courseIDs
//	 */
//	public function show_unit_grid($editing, $qualIDs, $courseIDs, $advancedMode = false, $orderByProjects = false)
//	{
//		return $this->get_unit_grid($editing, $qualIDs, $courseIDs, true, $advancedMode, $orderByProjects);
//	}
	
	/**
	 * Returns the form fields that go on the 
	 * edit unit form for this unit type
	 * When a new unit is created or edited then it needs unit type specific 
	 * input fields. 
	 */
	public function get_edit_form_fields()
	{
		$retval = parent::get_edit_form_fields();
		return $retval;
	}
	
	//This is used to get the data that is unit type specific
	//from the edit form. 
	public function get_submitted_edit_form_data()
	{
		parent::get_submitted_edit_form_data();
	}
	
	public function get_edit_criteria_table()
	{
		return $this->get_edit_criteria_table_actual(true, 'Lower');	
	}

	
	/**
	 * When the unit is created or edited we need to collect the information
	 * inputted by the user of the criteria. 
	 * 
	 * For btecs this is all the P's M's and D's that you want.
	 * 
	 * This can be in two ways. 
	 * 1.)
	 * three drop downs to select how many p's, m's and d's the user wants
	 */
	public function get_submitted_criteria_edit_form_data()
	{
		return $this->get_submitted_criteria_edit_form_data_actual(true);
	}

	
	public function insert_unit()
	{
		parent::insert_unit(BTECLowerQualification::ID);
	}
    
    public function display_unit_grid()
    {
        return $this->display_unit_grid_btec(true);
    }
    
    public function has_sub_criteria()
    {
        return true;
    }
    
    public static function get_instance($unitID, $params, $loadParams)
    {
        if(!$params)
        {
            $params = new stdClass();
        }
        $params->level = new Level(1);
        return new BTECLowerUnit($unitID, $params, $loadParams);
    }
}