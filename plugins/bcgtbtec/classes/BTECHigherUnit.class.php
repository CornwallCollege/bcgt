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
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECHigherQualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECUnit.class.php');
class BTECHigherUnit extends BTECUnit {	
	
	function BTECHigherUnit($unitID, $params, $loadParams)
	{
		parent::BTECUnit($unitID, $params, $loadParams);
	}
	
	public function get_typeID()
	{
		return BTECHigherQualification::ID;
	}
	
	public function get_type_name()
	{
		return BTECHigherQualification::NAME;
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
		$unitType = optional_param('unitSubtype', -1, PARAM_INT);
		
		$unitTypes = Unit::get_unit_types($this->get_typeID());
		
		$retval .= "<div class='inputContainer'><div class='inputLeft'><label for='unitSubtype'>".get_string('btecUnitSubtype', 'block_bcgt')."</label></div>
		<div class='inputRight'><select name='unitSubtype'>";
		if($unitTypes)
		{
			foreach($unitTypes AS $type)
			{
				if($unitType != -1 && $type->id == $unitType)
				{
					$retval .= "<option selected value='$type->id'>$type->type</option>";
				}
				else
				{
					$retval .= "<option value='$type->id'>$type->type</option>";
				}
				
			}
		}
		$retval .= "</select></div></div>";
		$retval .= '<p>'.get_string('btecUnitSubtypeExp', 'block_bcgt').'</p>';
		return $retval;
	}
	
	//This is used to get the data that is unit type specific
	//from the edit form. 
	public function get_submitted_edit_form_data()
	{
		parent::get_submitted_edit_form_data();
		$unitTypeID = $_POST['unitSubtype'];
		$unitType = Unit::get_unit_types($this->get_typeID(), $unitTypeID);
		$this->unitType = $unitType->type;
		$this->unitTypeID = $unitType->id;	
	}
	
	public function get_edit_criteria_table()
	{
		return $this->get_edit_criteria_table_actual(true, 'Higher');	
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
		parent::insert_unit(BTECHigherQualification::ID);
		//$this->update_foundation_unit();
	}
    
    public static function get_instance($unitID, $params, $loadParams)
    {
        if(!$params || !isset($params->levelID))
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
                $params->levelID = $levelID;
            } 
        }
        return new BTECHigherUnit($unitID, $params, $loadParams);
    }
    
    public function display_unit_grid()
    {
        return $this->display_unit_grid_btec(true);
    }
    
    public function has_sub_criteria()
    {
        return true;
    }
    
    /**
	 *Gets the unit award for BTECS for the ranking 
	 */
	protected function get_unit_award($ranking)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_type_award} AS award WHERE bcgttypeid = ? AND ranking = ?";
		return $DB->get_record_sql($sql, array(BTECHigherQualification::ID, $ranking));
	}
}