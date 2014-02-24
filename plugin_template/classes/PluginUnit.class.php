<?php
/**
 * Description of ALevelUnit
 *
 * @author mchaney
 */
global $CFG;
require_once($CFG->dirrot.'/blocks/bcgt/classes/core/Qualification.class.php');
class AlevelUnit extends Unit {
	
	//any constants or properties
    
    
    public function PluginUnit($unitID, $params, $loadParams)
    {
        parent::Unit($unitID, $params, $loadParams);
        //get any specific things. e.g BTEC credits and Alevel UMS
    }
    
    /*Static functions that the classes must implement!*/
    //public static abstract function get_instance($unitID, $params);
    //public static abstract function get_pluggin_unit_class($typeID, $unitID, $familyID, $params);
    //public static abstract function get_edit_form_menu($disabled, $unitID, $typeID);
    /*
	 * Gets the associated Qualification ID
	 */
	public function get_typeID()
    {
        return PluginFamilyQualification::ID;
    }
	
	/*
	 * Gets the name of the associated qualification. 
	 */
	public function get_type_name()
    {
        return PluginFamilyQualification::NAME;
    }
    
    /*
	 * Gets the name of the associated qualification family. 
	 */
	public function get_family_name()
    {
        return PluginFamilyQualification::NAME;
    }
	
	/**
	 * Get the family of the qual.
	 */
	public function get_familyID()
    {
        return PluginFamilyQualification::FAMILYID;
    }
    
    
    /**
	 * Gets the form fields that will go on edit_unit_form.php
	 * They are different for each unit type
     * e.g. for ALEVEL there are ums 
	 */
	public function get_edit_form_fields()
    {
        return "";
    }
    
    /**
	 * Used in edit unit
	 * Gets the criteria tablle that will go on edit_unit_form.php
	 * This is different for each unit type. 
	 */
	public function get_edit_criteria_table()
    {
        return "";
    }
    
    /**
	 * Used in edit unit
	 * Gets the submitted data from the edit form fields
	 * edit_unit_form.php
	 */
	public function get_submitted_edit_form_data()
    {
        return true;
    }
    
    /**
	 * Used in edit unit
	 * Gets the submitted data from the criteria section of the edit form form.
	 * edit_unit_form.php
	 */
	public function get_submitted_criteria_edit_form_data()
    {
        return true;
    }
    
    /**
	 * Inserts the unit AND the criteria and all related details
	 * Dont forget to set the id of the unit object
	 */
	public function insert_unit()
    {
        return true;
    }
	
	/**
	 * Updates the unit AND the criteria and all related details
	 */
	public function update_unit($updateCriteria = true)
    {
        return true;
    }
    
    /**
	 * Certain qualificaton types have unit awards
	 */
	public function unit_has_award()
    {
        return false;
    }
    
    /**
	 * Certain qualification types have unit awards.
	 */
	public function calculate_unit_award($qualID)
    {
        return false;
    }
    
    /**
     * displays the unit grid. 
     */
    public function display_unit_grid()
    {
        //display the unit grid
    }
    
    /**
     * This wants to be able to take things like, level, subtype etc and
     * return the correct unit class. If there is only one type of unit,
     * then return that, else return another instance of a unit.
     * 
     * THIS method is only on the Family
     * 
     * @param type $typeID
     * @param type $unitID
     * @param type $familyID
     * @param type $params
     * @param type $loadLevel
     * @return PluginUnit
     */
    public static function get_pluggin_unit_class($typeID = -1, $unitID = -1, 
            $familyID = -1, $params = null, $loadLevel = Qualification::LOADLEVELUNITS) {
        
        return new PluginUnit($unitID, $params, $loadParams);
    }
    
    /**
     * This method is on every non abstract class!
     * @param type $unitID
     * @param type $params
     * @param type $loadParams
     * @return \ALevelUnit
     */
    public static function get_instance($unitID, $params, $loadParams)
    {
        return new PluginUnit($unitID, $params, $loadParams);
    }
    
}

?>
