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
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFoundationQualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECUnit.class.php');
class BTECFoundationUnit extends BTECUnit {	
		
    protected $finalProject;
    
	function BTECFoundationUnit($unitID, $params, $loadParams)
	{
		parent::BTECUnit($unitID, $params, $loadParams);
	}
	
	public function get_typeID()
	{
		return BTECFoundationQualification::ID;
	}
	
	public function get_type_name()
	{
		return BTECFoundationQualification::NAME;
	}
	
	public function get_final_project()
	{
		return $this->finalProject;
	}
	
	public function set_final_project(Boolean $finalProject)
	{
		$this->finalProject = $finalProject;
	}
    
    /**
	 * Returns the form fields that go on the 
	 * edit unit form for this unit type
	 * When a new unit is created or edited then it needs unit type specific 
	 * input fields. 
	 */
	public function get_edit_form_fields()
	{
		$retval = parent::get_edit_form_fields();
		$unitTypeID = $this->unitTypeID;
		$unitTypes = null;
		if($unitTypeID)
		{
			$unitTypes = $this->get_unit_types($this->get_typeID(), $unitTypeID);
		}
		$finalProject = false;
		if($unitTypes)
		{
			if($unitTypes->type == 'Final Project')
			{
				$finalProject = true;
			}
		}
		else
		{
			if(isset($_POST['finalProject']))
			{
				$finalProject = true;
			}
		}		
		$retval .= "<div class='inputContainer'><div class='inputLeft'><label for='unitSubtype'>".get_string('finalProject', 'block_bcgt')." : </label></div>
		<div class='inputRight'><input type='checkbox' name='finalProject' value='yes'";
		if($finalProject)
		{
			$retval .= " checked='true' ";
		}
		$retval .= "/></div></div>";
		return $retval;
	}
	
    //This is used to get the data that is unit type specific
	//from the edit form. 
	public function get_submitted_edit_form_data()
	{
		parent::get_submitted_edit_form_data();
		$finalProject = '';
		if(isset($_POST['finalProject']))
		{
			//then get the ID for the final Project
			//$this->finalProject = true;
			$finalProject = $this->get_final_project_unit_type();
            if($finalProject)
            {
                $this->unitTypeID = $finalProject->id;
            }
		}
		else
		{
			
			//$this->finalProject = false;
			$this->unitTypeID = null;
		}
	}
	
	public function insert_unit()
	{
		parent::insert_unit(BTECFoundationQualification::ID);
	}
	
	public function update_unit($updateCriteria = true)
	{
		parent::update_unit($updateCriteria);
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
        return new BTECFoundationUnit($unitID, $params, $loadParams);
    }
		
	private function get_final_project_unit_type()
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_unit_type} WHERE bcgttypeid = ? AND type = ?";
		return $DB->get_record_sql($sql, array($this->get_typeID(), 'Final Project'));
	}
	
}