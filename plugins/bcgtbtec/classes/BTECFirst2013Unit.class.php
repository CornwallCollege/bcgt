<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BTECFirst2013Unit
 *
 * @author mchaney
 */

global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/lib.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECUnit.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFirst2013Qualification.class.php');
class BTECFirst2013Unit extends BTECUnit {
    //put your code here
    
    function BTECFirst2013Unit($unitID, $params, $loadParams)
	{
		parent::BTECUnit($unitID, $params, $loadParams);
	}

	public function get_typeID()
	{
		return BTECFirst2013Qualification::ID;
	}
	
	public function get_type_name()
	{
		return BTECFirst2013Qualification::NAME;
	}
    
    public function has_unique_id()
    {
        $this->uniqueID = '';
        return false;
    }
    
    public function get_credits_display_name()
    {
        return get_string('btecglh', 'block_bcgt');
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
        $unitSubType = optional_param('unitSubTypeFirst', -1, PARAM_INT);
        if($unitSubType == -1 && $this->unitTypeID)
        {
            $unitSubType = $this->unitTypeID;
        }
        $unitTypes = $this->get_unit_types($this->get_typeID());	
		$retval .= "<div class='inputContainer'><div class='inputLeft'><label for='unitSubType'>".get_string('unittype', 'block_bcgt')." : </label></div>
		<div class='inputRight'><select name='unitSubTypeFirst' id='unitSubType'>";
        foreach($unitTypes AS $unitType)
        {
            $selected = '';
            if($unitSubType != -1 && $unitType->id == $unitSubType)
            {
                $selected = 'selected';
            }
            elseif($unitSubType == -1 && $unitType->type == 'Internally Assessed')
            {
                $selected = 'selected';
            }
            $retval .= '<option '.$selected.' value="'.$unitType->id.'">'.$unitType->type.'</option>';
        }
		$retval .= "</select></div></div>";
        $this->unitTypeID = $unitSubType;
		return $retval;
	}
	
    //This is used to get the data that is unit type specific
	//from the edit form. 
	public function get_submitted_edit_form_data()
	{
		parent::get_submitted_edit_form_data();
		if(isset($_POST['unitSubTypeFirst']))
		{
			//then get the ID for the final Project
			//$this->finalProject = true;
            $this->unitTypeID = $_POST['unitSubTypeFirst'];
		}
		else
		{
			
			//$this->finalProject = false;
			$this->unitTypeID = null;
		}
	}
	
	public function get_edit_criteria_table()
	{
		return $this->get_edit_criteria_table_actual(false, 'BTECFirst2013', true);	
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
		return $this->get_submitted_criteria_edit_form_data_actual(false);
	}

	
	public function insert_unit()
	{
		parent::insert_unit(BTECFirst2013Qualification::ID);
	}
    
    public function display_unit_grid()
    {
        return $this->display_unit_grid_btec(false);
    }
    
    public function has_sub_criteria()
    {
        return false;
    }
    
    public static function get_instance($unitID, $params, $loadParams)
    {
        if(!$params)
        {
            $params = new stdClass();
        }
        $params->level = new Level(7);
        return new BTECFirst2013Unit($unitID, $params, $loadParams);
    }
    
    /**
	 * Calculates the unit award:
	 * It gets all of the criteria for this unit
	 * Separates them out into different arrays
	 * one for L1, one for pass, one for merit and one for diss
	 * Then loops over these and tests all of the criteri.
     * If they have all L1 it sets the award to Level 1
     * Then moves onto Pass
	 * If they have all pass it sets the award to pass
	 * Then moves onto merit.
	 * If they have all merit then it sets the award to merit
	 * Then moves onto diss
	 * If they have all diss then it sets the award to diss
	 * If at any point it fails with some criteria not met it
	 * doesnt move onto the next criteria. 
	 */
	public function calculate_unit_award($qualID)
	{
        $level1Criteria = array();
		$passCriteria = array();
		$meritCriteria = array();
		$distinctionCriteria = array();
		$found = false;
		if($this->criterias)
		{
			//first get all of the criteria that is being used in this unit.
			foreach($this->criterias AS $criteria)
			{     
                if(strpos($criteria->get_name(), "L1") === 0)
                {
                    $level1Criteria[$criteria->get_id()] = $criteria;
                }
				elseif(strpos($criteria->get_name(), "P") === 0)
				{
					$passCriteria[$criteria->get_id()] = $criteria;	
				}
				elseif(strpos($criteria->get_name(), "M") === 0)
				{
					$meritCriteria[$criteria->get_id()] = $criteria;	
				}
				elseif(strpos($criteria->get_name(), "D") === 0)
				{
					$distinctionCriteria[$criteria->get_id()] = $criteria;
				}	
			}	
			
            $pass = false;
			$merit = false;
			$distinction = false;
			$award = "";
			$rank = 1; // = unclassified
            $level1 = $this->check_criteria_award_set_for_met($level1Criteria);
            if($level1)
            {
                $found = true;
                $rank = 2;
                $pass = $this->check_criteria_award_set_for_met($passCriteria);
                if($pass)
                {
                    $found = true;
                    $rank = 3;
                    $merit = $this->check_criteria_award_set_for_met($meritCriteria);
                    if($merit)
                    {
                        $rank = 4;
                        $distinction = $this->check_criteria_award_set_for_met($distinctionCriteria);
                        if($distinction)
                        {
                            //update done on save ??
                            $rank = 5;
                            //return "DISTINCTION";
                        }
                        //return "MERIT";
                    }
                    //return "PASS";
                }
                //return "LEVEL1";
            }
			
		}
		
		if(!$found)
		{
			//set award to N/S
			$this->userAward = new Award(-1, 'N/S', 0);
			$this->update_unit_award($qualID);
			return null;
		}
		//else get the unit award for the rank and then set it, return it
		//and update the users database record. 
		$awardRecord = $this->get_unit_award($rank);
		if($awardRecord)
		{
            $params = new stdClass();
            $params->award = $awardRecord->award;
            $params->rank = $awardRecord->ranking;
			$award = new Award($awardRecord->id, $params);
			$this->userAward = $award;
			$this->update_unit_award($qualID);
			return $award;
		}			
	}
    
    public function set_award_criteria(Award $award, $qualID)
	{
		//set the award
		$this->userAward = $award;
        $l1Criteria = array();
		$passCriteria = array();
		$passIDs = array();
		$meritCriteria = array();
        $l1IDs = array();
		$meritIDs = array();
		$distinctionCriteria = array();
		$distinctionIDs = array();
		foreach($this->criterias AS $criteria)
		{
			$this->check_criteria_award_level($criteria, 
					$passCriteria, $meritCriteria, $distinctionCriteria, $passIDs, $meritIDs, $distinctionIDs, $l1Criteria, $l1IDs);	
				
			if($criteria->get_sub_criteria())
			{
				foreach($criteria->get_sub_criteria() AS $subCriteria)
				{
					$this->check_criteria_award_level($subCriteria, 
					$passCriteria, $meritCriteria, $distinctionCriteria, $passIDs, $meritIDs, $distinctionIDs, $l1Criteria, $l1IDs);	
				}
			}	
		}
		
		$obj = new stdClass();
		if($award && $award->get_award() == 'Level 2 Distinction (D)')
		{
            $this->mark_criteria($l1Criteria, $qualID);
			$this->mark_criteria($passCriteria, $qualID);
			$this->mark_criteria($meritCriteria, $qualID);
			$this->mark_criteria($distinctionCriteria, $qualID);
			
			$obj->metCriteria = array_merge($l1IDs, $passIDs, $meritIDs, $distinctionIDs);
			$obj->unMetCriteria = false;
		}
		elseif($award && $award->get_award() == 'Level 2 Merit (M)')
		{
            $this->mark_criteria($l1Criteria, $qualID);
			$this->mark_criteria($passCriteria, $qualID);
			$this->mark_criteria($meritCriteria, $qualID);
			$this->un_mark_criteria($distinctionCriteria, $qualID);
			
			$obj->metCriteria = array_merge($l1IDs, $passIDs, $meritIDs);
			$obj->unMetCriteria = $distinctionIDs;
		}
		elseif($award && $award->get_award() == 'Level 2 Pass (P)')
		{
            $this->mark_criteria($l1Criteria, $qualID);
			$this->mark_criteria($passCriteria, $qualID);
			$this->un_mark_criteria($meritCriteria, $qualID);
			$this->un_mark_criteria($distinctionCriteria, $qualID);
			
			$obj->metCriteria = array_merge($l1IDs, $passIDs);
			$obj->unMetCriteria = array_merge($distinctionIDs, $meritIDs);
		}
        elseif($award && $award->get_award() == 'Level 1')
		{
			$this->mark_criteria($l1Criteria, $qualID);
            $this->un_mark_criteria($passCriteria, $qualID);
			$this->un_mark_criteria($meritCriteria, $qualID);
			$this->un_mark_criteria($distinctionCriteria, $qualID);
			
			$obj->metCriteria = $l1IDs;
			$obj->unMetCriteria = array_merge($passIDs, $distinctionIDs, $meritIDs);
		}
		else
		{
            $this->un_mark_criteria($l1Criteria, $qualID);
			$this->un_mark_criteria($passCriteria, $qualID);
			$this->un_mark_criteria($meritCriteria, $qualID);
			$this->un_mark_criteria($distinctionCriteria, $qualID);
			
			$obj->metCriteria = false;
			$obj->unMetCriteria = array_merge($l1IDs,$passIDs,$meritIDs, $distinctionIDs);
		}
		return $obj;
		//return the the array
	}
    
    /**
	 *Gets the unit award for BTECS for the ranking 
	 */
	protected function get_unit_award($ranking)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_type_award} AS award 
            WHERE bcgttypeid = ? AND ranking = ?";
		return $DB->get_record_sql($sql, array(BTECFirst2013Qualification::ID, $ranking));
	}
    
}



?>
