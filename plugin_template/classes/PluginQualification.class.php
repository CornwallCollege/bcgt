<?php

/**
 * Description of AlevelQualification
 *
 * @author mchaney
 */
global $CFG;
require_once($CFG->dirrot.'/blocks/bcgt/classes/core/Qualification.class.php');

class PluginQualification extends PluginFamilyQualification{

    //these are hardcoded from the install. The tables dont have auto incremental
    //id's
    
    //the database id
	const ID = X;
	const NAME = 'X';
	const FAMILYID = X;
    const ASSubTypeID = X;
    const A2SubTypeID = X;

	//any properties
    
    
    function PluginQualification($qualID, $params, $loadParams)
	{
		parent::Qualification($qualID, $params, $loadParams);
        //retrieve anything specific from the database. 
        //e.g see BTEC or Alevel
	}
    
    /**
	 * Returns the id of the type not the qual
	 */
	public function get_family_ID()
    {
        PluginFamilyQualification::FAMILYID;
    }
    
    /**
     * Returns the family name
     */
    public function get_family()
    {
        PluginFamilyQualification::NAME;
    }
    
    /**
	 * Returns the human type name
	 */
	public function get_type()
    {
        PluginQualification::NAME;
    }
	
	/**
	 * Returns the id of the type not the qual
	 */
	public function get_class_ID()
    {
        PluginQualification::ID;
    }
    
    /**
	 * Gets the form fields that will go on edit_qualification_form.php
	 * They are different for each qual type
	 * e.g for Alevel its an <input> for ums
	 */
	public function get_edit_form_fields()
    {
        return "";
    }
    
    /**
	 * Used in edit qual
	 * Gets the submitted data from the edit form fields
	 * edit_qualification_form.php
	 * E.g. for Alevel its getting the POST of the ums input.
	 */
	public function get_submitted_edit_form_data()
    {
        return true;
    }
    
    /**
	 * using the object insert into the database
	 * Dont forget to set the ID up for the object once inserted
	 */
	public function insert_qualification()
    {
        //can this actually be inserted? Its abstract after all
        //but it can always be overridden. 
    }
	
	/***
	 * Deletes the qual
	 * For each type there maybe specific things we need to do
	 */
	public function delete_qualification()
    {
        
    }
	
	/**
	 * Updates the qual
	 * For each type there maybe specific things we need to do
	 */
	public function update_qualification()
    {
        
    }
    
    /**
	 * Used to get the type specific title values and labels.
	 * E.g. for BTEC its 'Credits Required. '
     * This is called from edit_qual_units
     * and is for things like credits required, no units required ect
	 */
	public function get_type_qual_title()
    {
        return "";
    }
    
    /**
	 * So when adding or removing units from a qual.
	 * returns a string with fields for edit_qualification_units 
	 * for example, total credits for BTECs or total no of units or toal UMS
	 * This is used when the form comes up so that a user can 
	 * view things that are specific to the qual when adding units to quals. 
	 */
	public function get_unit_list_type_fields()
    {
        //total no of UMS
        return "";
    }
    
    /**
	 * Adds a unit to the qualification
	 * @param Unit $unit
	 */
	public function add_unit(Unit $unit)
    {
        return true;
    }
    
    /**
	 * Removes a unit from the qualification. 
	 * @param Unit $unit
	 */
	public function remove_unit(Unit $unit)
    {
        //does the ALEVEL need to do anything else?
        return true;
    }
        
    /**
     * Multiple denotes if this will appear multiple times on a page. 
     * Gets the page and grid that is used in the edit students unit
     * page. 
     */
    public function get_edit_students_units_page($courseID = -1, $multiple = false, 
            $count = 1, $action = 'q')
    {
        //return a grid of students, units and which students are doing which units
        return "";
        
    }
    
    /**
     * gets the javascript initialisation call
     */
    public function get_edit_student_page_init_call()
    {
        //this depends on the number of tables shown
        return "";
    }
    
    /**
	 * Does the qual have a final grade?
	 * E.g. Alevels or BTECS or are they just pass/fail
	 */
	public function has_final_grade()
    {
        return false;
    }
    
    /**
	 * What is the final grade if it has been set
	 */
	public function retrieve_student_award()
    {
        //probably wants to be overwritten on the 
        return true;
    }
    
    /**
	 * What is the final grade
	 */
	public function calculate_final_grade()
    {
        return true;
    }
    
    /**
	 * Calculate the predicted grade
	 */
	public function calculate_predicted_grade()
    {
        return true;
    }
    
    //some quals have criteria just on the qual like alevels. 
	//each qual migt store this differently.
	public function load_qual_criteria_student_info($studentID, $qualID)
    {
        return false;
    }
    
    /**
     * process the edit students units page. 
     */
    public function process_edit_students_units_page($courseID = -1)
    {
        
    }
    
    /**
     * Gets a single row of abiliy to select a students units 
     * on the qualification.
     */
    public function get_edit_single_student_units($currentCount)
    {
        
    }
    
    /**
     * Gets the initialisation call of each functtion for dealing 
     * with the edit students units when we are looking at one single student
     * per qualification. 
     * In the previous screen the user may have added the student(s) to qualifications
     * of a different type so will need different behaviour. 
     */
    public function get_edit_single_student_units_init_call()
    {
        
    }
    
    /**
     * Gets the page where the settings for the qualification can be set.
     */
    public function get_qual_settings_page()
    {
        
    }
    
    /**
     * Displays the Grid
     */
    public function display_student_grid($fullGridView = true, $studentView = true)
    {
        
    }
    
    /**
     * displays the unit grid. 
     */
    public function display_subject_grid()
    {
        //display the unit grid
    }
    
    public static function get_instance($qualID, $params, $loadParams)
    {   
        //may need to add things to the params
        return new PluginQualification($qualID, $params, $loadParams);
    }

}

?>
