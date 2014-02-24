<?php

/**
 * Description of A2LevelQualification
 *
 * @author mchaney
 */

require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/lib.php');

class A2LevelQualification extends ALevelQualification {
	
	const ID = 8;
	const NAME = 'A2 Level';
	
	protected $lastYearsQualID;
	protected $lastYearsUnits;
    
    
    function A2LevelQualification($qualID, $params, $loadParams)
	{
		parent::ALevelQualification($qualID, $params, $loadParams);
	}
    
    protected function is_A2()
	{
		return true;
	}
    
    /**
	 * Returns the id of the type not the qual
	 */
	public function get_family_ID()
    {
        return AlevelQualification::FAMILYID;
    }
    
    /**
     * Returns the family name
     */
    public function get_family()
    {
        return AlevelQualification::NAME;
    }
    
    /**
	 * Returns the human type name
	 */
	public function get_type()
    {
        return A2LevelQualification::NAME;
    }
    
    /**
	 * Returns the id of the type not the qual
	 */
	public function get_class_ID()
    {
        return A2LevelQualification::ID;
    }
    
    public function insert_qualification($insertUnits = true, $insertWeightings = true)
    {
        return $this->insert_alevel_qualification(A2LevelQualification::ID, $insertUnits, $insertWeightings);
    }
    
    /**
	 * Gets the form fields that will go on edit_qualification_form.php
	 * They are different for each qual type
	 * e.g for Alevel its an <input> for ums
	 */
	public function get_edit_form_fields()
    {
        //any extras for an A2? Linking to the previous years qual for example?
        $lastYearsQualID = -1;
        if(isset($_POST['lastYearQualID']))
        {
            $lastYearsQualID = $_POST['lastYearQualID'];
        }
        $retval = '';
		$retval .= "<div class='inputContainer'><div class='inputLeft'>".
                "<label for='ums'><span class='required'>*</span>".
                get_string('lastyearsqual', 'block_bcgt')."</label></div>";
		$retval .= "<div class='inputRight'><select name='lastYearQualID' id='lastYearQualID'>";
		$retval .= "<option value='-1'>Please Select one</option>";
		$asLevelQuals = $this->get_AS_level_qualifications();
		if($asLevelQuals)
		{
			foreach($asLevelQuals AS $asLevel)
			{
				$selected = '';
				if(($this->lastYearsQualID && $asLevel->id == $this->lastYearsQualID) || (
                        $lastYearsQualID && $asLevel->id == $lastYearsQualID))
				{
					$selected = 'selected';
				}
				$retval .= "<option $selected value='$asLevel->id'>$asLevel->name</option>";
			}
		}
		$retval .= "</select></div></div>";
		$retval .= parent::get_edit_form_fields();
		return $retval;
        
        
        return parent::get_edit_form_fields();
    }
    
    function get_submitted_edit_form_data()
	{
		//needs to get previous years as level id
		$this->lastYearsQualID = isset($_POST['lastYearQualID']) ? isset($_POST['lastYearQualID']) : -1;
		parent::get_submitted_edit_form_data();	
	}
    
    public static function get_instance($qualID, $params, $loadParams)
    {   
        //units and criteria are directly on the qualification
        if(!$loadParams)
        {
            $loadParams = new stdClass();
        }
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        return new A2LevelQualification($qualID, $params, $loadParams);
    }
    
    private function get_AS_level_qualifications()
	{
		global $DB;
		$sql = "SELECT qual.* FROM {block_bcgt_qualification} qual 
		JOIN {block_bcgt_target_qual} targetQual ON targetQual.id = qual.bcgttargetqualid 
		WHERE targetQual.bcgttypeid = ?";
		return $DB->get_records_sql($sql, array(ASLevelQualification::ID));
	}
}

?>
