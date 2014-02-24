<?php

/**
 * Description of ASLevelQualification
 *
 * @author mchaney
 */
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/lib.php');

class ASLevelQualification extends ALevelQualification {
	
	const ID = 7;
	const NAME = 'AS Level';
    //put your code here
    
    function ASLevelQualification($qualID, $params, $loadParams)
	{
		parent::ALevelQualification($qualID, $params, $loadParams);
	}
    
    protected function is_A2()
	{
		return false;
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
        return ASLevelQualification::NAME;
    }
    
    /**
	 * Returns the id of the type not the qual
	 */
	public function get_class_ID()
    {
        return ASLevelQualification::ID;
    }
    
    public function insert_qualification($insertUnits = true, $insertWeightings = true)
    {
        return $this->insert_alevel_qualification(ASLevelQualification::ID, $insertUnits, $insertWeightings);
    }
    
    /**
	 * Gets the form fields that will go on edit_qualification_form.php
	 * They are different for each qual type
	 * e.g for Alevel its an <input> for ums
	 */
	public function get_edit_form_fields()
    {
        //any that are just for an AS?
        return parent::get_edit_form_fields();
    }
    
    public static function get_instance($qualID, $params, $loadParams)
    {   
        //units and criteria are directly on the qualification
        if($loadParams == null)
        {
            $loadParams = new stdClass();
        }
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        
        return new ASLevelQualification($qualID, $params, $loadParams);
    }
}

?>
