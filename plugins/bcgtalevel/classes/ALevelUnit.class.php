<?php
/**
 * Description of ALevelUnit
 *
 * @author mchaney
 */
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/lib.php');
class AlevelUnit extends Unit {
	
    const INITIALFORMALASSESSMENTS = 4;
	private $ums;
    
    public function ALevelUnit($unitID, $params, $loadParams)
    {
        parent::Unit($unitID, $params, $loadParams);
        if($unitID != -1)
		{
			$creditsObj = AlevelUnit::retrieve_ums($unitID);
			if($creditsObj)
			{
				$this->ums = $creditsObj->credits;
			}
		}
		else
		{
            if(isset($params->ums))
            {
                $this->ums = $params->ums;
            }
		}
    }
    
    public function set_ums($ums)
	{
		$this->ums = $ums;
	}
    
    public function get_ums()
	{
		return $this->ums;
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
        return AlevelQualification::ID;
    }
	
	/*
	 * Gets the name of the associated qualification. 
	 */
	public function get_type_name()
    {
        return AlevelQualification::NAME;
    }
    
    /**
     * Add this unit to the qualification ID passed in. 
     * @global type $DB
     * @param type $qualificationID
     */
    public function add_to_qualification($qualificationID)
	{
        global $DB;
		$stdObj = new stdClass();
		$stdObj->bcgtqualificationid = $qualificationID;
		$stdObj->bcgtunitid = $this->id;
		$id = $DB->insert_record('block_bcgt_qual_units', $stdObj);
	}
    
    /*
	 * Gets the name of the associated qualification family. 
	 */
	public function get_family_name()
    {
        return AlevelQualification::NAME;
    }
	
	/**
	 * Get the family of the qual.
	 */
	public function get_familyID()
    {
        return AlevelQualification::FAMILYID;
    }
    
    
    /**
	 * Gets the form fields that will go on edit_unit_form.php
	 * They are different for each unit type
     * e.g. for ALEVEL there are ums 
	 */
	public function get_edit_form_fields()
    {
        $retval = "<div class='inputContainer'><div class='inputLeft'>".
                "<label for='ums'><span class='required'>*</span>".
                get_string('alevelums', 'block_bcgt')." : </label></div>";
		$retval .= "<div class='inputRight'><input type='input' name='ums'".
                "id='ums' value='$this->ums'/></div></div>";
		return $retval;
    }
    
    /**
     * Can be overridden by the other units
     * @return type
     */
    public function get_criteria_header()
	{
		return get_string('alevelformalassessments', 'block_bcgt');
	}
    
    /**
     * All alevel units are the same so it doesnt matter what this returns
     * @return string
     */
    public static function get_edit_form_menu()
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
        //does alevel have critera?
        //Assessments? FormalAssessments etc
        if($useFAs = get_config('bcgt', 'alevelusefa') && 
                    !$manageCentrally = get_config('bcgt', 'alevelManageFACentrally'))
            {
                $jsModule = array(
                'name'     => 'mod_bcgtalevel',
                'fullpath' => '/blocks/bcgt/plugins/bcgtalevel/js/bcgtalevel.js',
                'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
            );
            global $PAGE;
            $PAGE->requires->js_init_call('M.mod_bcgtalevel.aleveliniteditunit', null, true, $jsModule);

            $retval = '<table id="alevelAssTable" align="center">';
            $retval .= '<tr><td>'.get_string('alevelassname', 'block_bcgt').'</td>'.
                    '<td>'.get_string('alevelassdate', 'block_bcgt').'</td></tr>';
            if($this->criterias && $this->id != -1)
            {
                $k = 0;
                foreach($this->criterias AS $criteria)
                {
                    $k++;
                    $retval .= '<tr><td><input type="text" name="assName'.
                            $criteria->get_id().'" value="'.$criteria->get_name().
                            '"/></td>';
                    $retval .= '<td><input type="text" class="bcgt_datepicker" name="assDate'.
                            $criteria->get_id().'" value="'.$criteria->get_target_date().
                            '"/></td>';
                    $retval .= '<td><input type="button" class="removeAss" '.
                            'name="remove" value="X" onclick="deleteAss('.
                            $criteria->get_id().')"/></td>';
                    $retval .= '<input type="hidden" name="assID'.$k.'" value="'.
                            $criteria->get_id().'"/></tr>';
                }
            }
            else
            {
                //its a brand new unit
                for($i=1;$i<=ALevelUnit::INITIALFORMALASSESSMENTS;$i++)
                {
                    $retval .= '<tr><td><input type="text" name="assName'.$i.'" value="Ass'.
                            $i.'"/></td>';
                    $retval .= '<td><input type="text" class="bcgt_datepicker" name="assDate'.$i.'" value=""'.
                            '/></td>';
                    $retval .= '<td><input type="button" class="removeAss" name="remove"'.
                            'value="X"/></td></tr>';
                }
            }	
            $retval .= '</table>';
            if($this->id && $this->id != -1)
            {
                $countAss = count($this->criterias);
            }
            else
            {
                $countAss = ALevelUnit::INITIALFORMALASSESSMENTS;
            }
            $retval .= "<input type='hidden' name='noAss' id='noAss' value='$countAss'/>";
            $retval .= "<input align='center' class='addAss' type='button' ".
                    "name='addAssRow' value='Add new Assessment'/>";
        
            return $retval;
        }
            
	return "";
    }
    
    /**
	 * Used in edit unit
	 * Gets the submitted data from the edit form fields
	 * edit_unit_form.php
	 */
	public function get_submitted_edit_form_data()
    {
        //get the UMS
        //needs to get the UMS marks.  
		$this->ums = $_POST['ums'];
        
    }
    
    /**
	 * Used in edit unit
	 * Gets the submitted data from the criteria section of the edit form form.
	 * edit_unit_form.php
	 */
	public function get_submitted_criteria_edit_form_data()
    {
        $criteriaArray = array();
		$noAss = $_POST['noAss'];
		if($this->id && $this->id != -1)
		{
			//its an update
			for($i=0;$i<=$noAss;$i++)
			{
				if(isset($_POST['assID'.$i]))
				{
					$id = $_POST['assID'.$i];
                    $params = new stdClass();
                    $params->name = $_POST['assName'.$id];
                    $params->targetDate = $_POST['assDate'.$id];
                    $params->awardID = -1;
                    $loadParams = new stdClass();
                    $loadParams->loadLevel = Qualification::LOADLEVELALL;
					$assessment = new Criteria($id, $params, $loadParams);
					$criteriaArray[$id] = $assessment;
				}
			}
		}
		else
		{
			//its a new one. 
			for($i=0;$i<=$noAss;$i++)
			{
				if(isset($_POST['assName'.$i]))
				{
					$assessmentObj = new stdClass;
                    $params = new stdClass();
                    $params->name = $_POST['assName'.$i];
                    $params->targetDate = $_POST['assDate'.$i];
                    $params->awardID = -1;
                    $loadParams = new stdClass();
                    $loadParams->loadLevel = Qualification::LOADLEVELALL;
					$assessment = new Criteria(-1, $params, $loadParams);
					$criteriaArray[] = $assessment;
				}
			}
		}
		$this->criterias = $criteriaArray;
    }
    
    /**
	 * Inserts the unit AND the criteria and all related details
	 * Dont forget to set the id of the unit object
	 */
	public function insert_unit()
    {      
        global $DB;
        $stdObj = new stdClass();
		$stdObj->name = $this->name;
		$stdObj->details = $this->details;
		$stdObj->uniqueid = $this->uniqueID;
		$stdObj->credits = $this->ums;
        $stdObj->bcgttypeid = AlevelQualification::ID;
		$stdObj->bcgtunittypeid = $this->unitTypeID;
		$stdObj->bcgtlevelid = Level::level3ID;
        $id = $DB->insert_record('block_bcgt_unit', $stdObj);
		$this->id = $id;
		//this also needs to add the assessments/criteria to the qualifications. 
		//so we need to find out what quals this unit is on. 
        foreach($this->criterias AS $criteria)
		{
			if(isset($_POST['qual']))
			{
				$criteria->insert_criteria_on_qual($id, $_POST['qual']);	
			}
			else
			{
				$criteria->insert_criteria($id);
			}
		}
    }
	
	/**
	 * Updates the unit AND the criteria and all related details
	 */
	public function update_unit($updateCriteria = true)
    {
        global $DB;
        $stdObj = new stdClass();
		$stdObj->id = $this->id;
		$stdObj->name = $this->name;
		$stdObj->details = $this->details;
		$stdObj->uniqueid = $this->uniqueID;
		$stdObj->credits = $this->ums;
		$DB->update_record('block_bcgt_unit', $stdObj);
		
		if($updateCriteria)
		{
			$this->check_criteria_removed();
				
			if($this->criterias)
			{
				foreach($this->criterias AS $criteria)
				{					
					if($criteria->exists())
					{
						$criteria->update_criteria($this->id);
					}
					else
					{
						$criteria->insert_criteria($this->id);
					}
				}
			}
		}
    }
    
    /**
	 * Certain qualificaton types have unit awards
	 */
	public function unit_has_award()
    {
        //does the alevel have a unit award?
        //is it exam results????
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
    
    public static function get_pluggin_unit_class($typeID = -1, $unitID = -1, 
            $familyID = -1, $params = null, $loadParams = null) {
        return new ALevelUnit($unitID, $params, $loadParams);
    }
    
    public static function get_instance($unitID, $params, $loadParams)
    {
        return new ALevelUnit($unitID, $params, $loadParams);
    }
    
    /**
	 * Used to get the credits value from the database for this unit
	 * @param $id
	 */
	public static function retrieve_ums($id)
	{		
		global $DB;
		$sql = "SELECT credits FROM {block_bcgt_unit} WHERE id = ?";
		return $DB->get_record_sql($sql, array($id));
	}
    
}

?>
