<?php

/**
 * Description of AlevelQualification
 *
 * @author mchaney
 */
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Qualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/lib.php');
class AlevelQualification extends Qualification{

    //the database id
	const ID = 6;
	const NAME = 'A Level';
	const FAMILYID = 3;
    const INITIALUNITS = 2;
    const INITIALFORMALASSESSMENTS = 4;
    const ASSubTypeID = 12;
    const A2SubTypeID = 13;
    const DEFAULTINITIALWEIGHTINGS = 8;

	protected $ums;
    //this is an array with the assignment/criteriaid as the key
    //then there is an object that has the unitid and an object of criteria
    protected $assessments;
	protected $assessmentUnits;
    protected $weightingPercentage = array(100, 90, 75, 60, 40, 25, 10, 0);
    
    public function AlevelQualification($qualID, $params, $loadParams)
    {
        //if we know the id then lets go get the ums from the database
		if($qualID != -1)
		{
			//gets the credits from the database
			$creditsObj = ALevelQualification::retrieve_ums($qualID);
			if($creditsObj)
			{
				$this->ums = $creditsObj->credits;
			}
//			$assessments = ALevelQualification::retrieve_assessments($qualID);
//			if($assessments)
//			{
//				foreach($assessments AS $assessment)
//				{
//					$assessmentObj = new stdClass();
//					$assessmentObj->unitID = $assessment->bcgtunitid;
//                    
//                    $params = new stdClass();
//                    $params->name = $assessment->name;
//                    $params->details = $assessment->details;
//                    $params->targetDate = $assessment->targetdate;
//                    $loadParams = new stdClass();
//                    $loadParams->loadLevel = Qualification::LOADLEVELALL;
//					$criteria = new Criteria($assessment->id, $params, $loadParams);
//					$assessmentObj->criteria = $criteria;
//					$assessmentArray[$assessment->id] = $assessmentObj;
//				}
//				$this->assessments = $assessmentArray;
//			}
            
            $weightings = Qualification::retrieve_weightings($qualID);
            if($weightings)
            {
                $this->weightings = $weightings;
            }
		}
        
        //todo get weightings
        
        parent::Qualification($qualID, $params, $loadParams);
    }
    
    /**
	 * Returns the id of the type not the qual
	 */
	public function get_family_ID()
    {
        AlevelQualification::FAMILYID;
    }
    
    /**
     * Returns the family name
     */
    public function get_family()
    {
        AlevelQualification::NAME;
    }
    
    /**
	 * Returns the human type name
	 */
	public function get_type()
    {
        AlevelQualification::NAME;
    }
	
	/**
	 * Returns the id of the type not the qual
	 */
	public function get_class_ID()
    {
        AlevelQualification::ID;
    }
    
    public function get_family_instance_id()
    {
        return AlevelQualification::ID;
    }
    
    public function get_assessments()
    {
        return $this->assessments;
    }
    
    public function get_assessment_by_criteria($criteriaID)
    {
        return $this->assessments[$criteriaID];
    }
    
    public function insert_qualification() {
        return false;
    }
    
    public static function get_edit_form_menu($disabled, $qualID, $typeID)
	{
        //we need to have a subtype drop down to denote if its a AS or A2
        
        $jsModule = array(
            'name'     => 'mod_bcgtalevel',
            'fullpath' => '/blocks/bcgt/plugins/bcgtalevel/js/bcgtalevel.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        global $PAGE;
        $PAGE->requires->js_init_call('M.mod_bcgtalevel.aleveliniteditqual', null, true, $jsModule);
        
		$subtypeID = optional_param('subtype', -1, PARAM_INT);
		$subTypes = get_subtype_from_type(-1, LEVEL::level3ID, ALEVELQualification::FAMILYID);
		if($qualID != -1)
		{
			$qualSubType = Qualification::get_qual_subtype($qualID);
		}
        //need an init for alevel      
		$retval = "";
		$retval .= '<input type="hidden" name="level" id="qualLevel" value="'.LEVEL::level3ID.'"/>';
		$retval .= '<div class="inputContainer"><div class="inputLeft">'.
            '<label for="subtype"><span class="required">*</span>'.
                get_string('subtype', 'block_bcgt').'</label></div>';
		$retval .= '<div class="inputRight"><select '.$disabled.' name="subtype" id="qualSubtype">';
			if($subTypes)
			{
				if(count($subTypes) > 1)
				{
					$retval .= '<option value="-1">'.get_string('pleaseselect', 'block_bcgt').'</option>';
				}
				foreach($subTypes as $subType) {
					$selected = '';
					if($qualID != -1 && $qualSubType && ($subType->get_id() == $qualSubType->id))
					{
						$selected = 'selected';
					}
					elseif($subtypeID != -1 && $subtypeID == $subType->get_id())
					{
						$selected = 'selected';
					}
					elseif(count($subTypes) == 1)
					{
						$selected = 'selected';
					}
					$retval .= "<option $selected value='".$subType->get_id()."'>".$subType->get_subtype()."</option>";
				}	
			}
			else
			{
				$retval .= "<option value=''>".get_string('nosubtypes','block_bcgt')."</option>";
			}
		$retval .= "</select></div></div>";
		return $retval;
	}
    
    public function get_target_grade_db_fields()
    {
        $retval = new stdClass();
        $retval->idField = 'bcgttargetgradesid';
        $retval->type = 'grade';
        $retval->gradeField = 'grade';
        $retval->weightedIdField = 'bcgtweightedgradeid';
        $retval->teacherSetIdField = 'teacherset_targetid';
        $retval->table = 'targetgrade';
        return $retval;
    }
    
    /**
	 * Gets the form fields that will go on edit_qualification_form.php
	 * They are different for each qual type
	 * e.g for Alevel its an <input> for ums
	 */
	public function get_edit_form_fields()
    {
        //input for UMS
        $retval = "";
        $retval .= "<div class='inputContainer'><div class='inputLeft'>".
                "<label for='ums'><span class='required'>*</span>".
                get_string('alevelums', 'block_bcgt')." : </label></div>";
		$retval .= "<div class='inputRight'><input type='input' name='ums' ".
                "id='ums' value='$this->ums'/></div></div>";
        
        //two columns. One for Units and one for assessments
        $retval .= '<div id="bcgtColumnConainer" class="bcgt_two_c_container bcgt_float_container">';
            //********************units
        //bcgt_admin_left 
            $retval .= '<div id="alevelunits" class="bcgt_col">';
            $retval .= '<h3>'.get_string('units','block_bcgt').'</h3>';
            $retval .= "<table align='center' id='aleveleUnitsTable'>";
            $retval .= '<tr><th>'.get_string('unitname', 'block_bcgt').'</th>'.
                    '<th>'.get_string('alevelunitums', 'block_bcgt').'</th></tr>';
            
            //new qual or old?
            if($this->id != -1)
            {
                $i=0;
                //then we are loading a previous qual
                foreach($this->units AS $unit)
                {
                    $i++;
                    $retval .= "<tr id='".$unit->get_id()."' class='unitRow'>".
                            "<td><input type='text' class='unitName' id='unitName".
                            $unit->get_id()."' name='unitName".$unit->get_id()."'".
                            "value='".$unit->get_name()."'/></td>";
                    $retval .= "<td><input type='text' name='unitUMS".$unit->get_id().
                            "' value='".$unit->get_ums()."'/></td>";
                    $retval .= "<td><input type='button' class='removeUnit'".
                            "name='remove' value='X'/></td>";
                    //this is so we know that the nth number of unit has the id of ...
                    $retval .= "<td><input type='hidden' name='unitID$i' value='".
                            $unit->get_id()."'/></td></tr>";
                }
            }
            else
            {
                //loading a brand new qual up
                $x = 1;
                if($this->is_A2())
                {
                    $x = 3;
                }
                for($i=1;$i<=AlevelQualification::INITIALUNITS;$i++)
                {
                    $unitName = get_string('unit', 'block_bcgt').$i;
                    if($this->is_A2())
                    {
                        $unitName = get_string('unit', 'block_bcgt').($i+2);
                    }
                    $retval .= "<tr id='$i' class='unitRow'><td><input type='text' class='unitName' name='unitName$i' id='unitName$i' value='$unitName'/></td>
                    <td><input type='text' name='unitUMS$i' value=''></td>
                    <td><input class='removeUnit' type='button' name='removeUnit' value='X'/></td></tr>";
                }
            }  
            $retval .= '</table>';
            //we need to know how many we have
            if($this->id != -1)
            {
                $countUnits = count($this->units);
            }
            else
            {
                $countUnits = AlevelQualification::INITIALUNITS;
            }
            $retval .= "<input type='hidden' name='noUnits' id='noUnits' value='$countUnits'/>";
            $retval .= "<input class='addUnit' id='addUnit' align='center' type='button' name='addUnitRow' value='Add new Unit'/>";
            $retval .= '</div>';
            
//            if($useFAs = get_config('bcgt', 'alevelusefa') && 
//                    !$manageCentrally = get_config('bcgt', 'alevelManageFACentrally'))
//            {
//                //****************Formal Assessments
//                $retval .= '<div id="formalAssessments" class="bcgt_admin_right bcgt_col">';
//                $retval .= "<h3>".get_string('alevelformalassessments', 'block_bcgt')."</h3>";
//                $retval .= "<table align='center' id='alevelAssTable'>";
//                $retval .= '<tr><th>'.get_string('alevelassname', 'block_bcgt').
//                        '</th><th>'.get_string('alevelassdate', 'block_bcgt').
//    //                    '</th><th>'.get_string('alevelassdetails', 'block_bcgt').
//                        '</th><th>'.get_string('alevelasslink', 'block_bcgt').'</th></tr>';
//
//                if($this->id != -1)
//                {
//                    $i=0;
//                    //then we are loading a previous qual
//                    //first get the assessments that are just on the qual
//                    if($this->assessments)
//                    {
//                        foreach($this->assessments AS $assessment)
//                        {
//                            $i++;
//                            $retval .= "<tr><td><input type='text' name='assName".
//                                    $assessment->criteria->get_id()."' value='".
//                                    $assessment->criteria->get_name()."'/></td>";
//                            $retval .= "<td><input type='text' class='bcgt_datepicker' name='assDate".
//                                    $assessment->criteria->get_id()."' value='".
//                                    $assessment->criteria->get_target_date()."'></td>";
//    //                        $retval .= "<td><input type='text' name='assDetails".
//    //                                $assessment->criteria->get_id()."' value='".
//    //                                $assessment->criteria->get_details()."'></td>";
//                            $retval .= "<td><select class='assUnit' name='assUnit".
//                                    $assessment->criteria->get_id()."'>";
//                            $retval .= "<option value='-1'>No Unit</option>";
//                            if($this->id != -1)
//                            {
//                                    $retval .= $this->get_unit_drop_down_ass($assessment->unitID);
//                            }					
//                            else
//                            {
//                                for($k=1;$k<=AlevelQualification::INITIALUNITS;$k++)
//                                {
//                                    $retval .= "<option value='$k'>".get_string('unit', 'block_bcgt')."$k</option>";
//                                }
//                            }
//                            $retval .= "</select></td>
//                            <td><input type='button' class='removeAss' name='remove' ".
//                                    "value='X'/></td>";
//                            //this is so we know that the nth number of unit has the id of ...
//                            $retval .= "<td><input type='hidden' name='assID$i' value='".
//                                    $assessment->criteria->get_id()."'/></td></tr>";
//                        }
//                    }
//
//                    //then get the assessments that are on the units. 
//                    if($this->units)
//                    {
//                        foreach($this->units AS $unit)
//                        {
//                            $crierias = $unit->get_criteria();
//                            if($crierias)
//                            {
//                                foreach($crierias AS $criteria)
//                                {
//                                    $i++;
//                                    $retval .= "<tr><td><input type='text' name='assName".
//                                            $criteria->get_id()."' value='".
//                                            $criteria->get_name()."'/></td>";
//                                    $retval .= "<td><input type='text' class='bcgt_datepicker' name='assDate".
//                                            $criteria->get_id()."' value='".
//                                            $criteria->get_target_date()."'></td>";
//    //                                $retval .= "<td><input type='text' name='assDetails".
//    //                                        $criteria->get_id()."' value='".
//    //                                        $criteria->get_details()."'></td>";
//                                    $retval .= "<td><select class='assUnit' name='assUnit".
//                                            $criteria->get_id()."'>";
//                                    $retval .= "<option value='-1'>No Unit</option>";
//                                    if($this->id != -1)
//                                    {
//                                        $retval .= $this->get_unit_drop_down_ass($unit->get_id());
//                                    }
//                                    $retval .= "<td><input type='button' class='removeAss' name='remove' value='X'/></td>
//                                    <td><input type='hidden' name='assID$i' value='".$criteria->get_id()."'/></td></tr>";
//                                }
//                            }
//                        }
//                    }
//
//                }
//                else
//                {
//                    //brand new qual
//                    for($i=1;$i<=AlevelQualification::INITIALFORMALASSESSMENTS;$i++)
//                    {
//                        $retval .= "<tr><td><input type='text' name='assName$i' ".
//                                "value='Ass$i'/></td>";
//                        $retval .= "<td><input type='text' class='bcgt_datepicker' name='assDate$i' ".
//                                "/></td>";
//    //                    $retval .= "<td><input type='text' name='".
//    //                            "assDetails$i' value=''></td>";
//                        $retval .= "<td><select class='assUnit' name='assUnit$i'>";
//                        $retval .= "<option value='-1'>No Unit</option>";
//                        for($k=1;$k<=AlevelQualification::INITIALUNITS;$k++)
//                        {
//                            $unitName = 'Unit'.$k;
//                            if($this->is_A2())
//                            {
//                                $unitName = 'Unit'.($k+2);
//                            }
//                            $retval .= "<option value='$k'>$unitName</option>";
//                        }
//                        $retval .= "</select></td>
//                        <td><input type='button' class='removeAss' name='remove' value='X'/></td></tr>";
//                    }
//                }
//                if($this->id != -1)
//                {
//                    $countAss = count($this->assessments);
//                    foreach($this->units AS $unit)
//                    {
//                        $countAss = $countAss + count($unit->get_criteria());
//                    }
//                }
//                else
//                {
//                    $countAss = AlevelQualification::INITIALFORMALASSESSMENTS;
//                }
//                $retval .= "<input type='hidden' name='noAss' id='noAss' value='$countAss'/>";
//                $retval .= '</table>';
//                $retval .= "<input align='center' class='addAss' type='button' name='addAssRow' value='Add new Assessment'/>";
//                $retval .= '</div>';
//                /********* end of assessments and units *****/
//                $retval .= '</div>';
//            }
//            else
//            {
//                $retval .= '</div>';
//            }
            $retval .= '</div>';
            $useWeightings = get_config('bcgt', 'alevelallowalpsweighting');
            if($useWeightings)
            {
            
                //******* Weightings **********/
                $retval .= '<div class="" id="alevelWeightTable"/>';
                $retval .= "<h3>".get_string('alevelweightings', 'block_bcgt')."</h3>";
                $retval .= '<table id="alevelWeightings" align="center">';
                $retval .= '<tr><th>'.get_string('number', 'block_bcgt').'</th><th>'.
                        get_string('percentage', 'block_bcgt').'</th><th>'.
                        get_string('alevelcoefficient', 'block_bcgt').'</th></tr>';
                if($this->id != -1)
                {
                    $i=0;
                    //then lets load up the others
                    if($this->weightings)
                    {
                        $countWeights = count($this->weightings);
                        foreach($this->weightings AS $weighting)
                        {
                            $i++;
                            $id = $weighting->id;
                            $retval .= '<tr><td><input type="text" name="weightNo'.$id.
                                    '" value="'.$weighting->number.'"/></td>';
                            $retval .= '<td><input type="text" name="weightPec'.$id.
                                    '" value="'.$weighting->percentage.'"/></td>';
                            $retval .= '<td><input type="text" name="weightCoef'.$id.
                                    '" value="'.$weighting->coefficient.'"/></td>';
                            $retval .= '<td><input type="checkbox" alt="'.
                                    get_string('aleveltargetcoefficient', 'block_bcgt').
                                    '" title="'.
                                    get_string('aleveltargetcoefficient', 'block_bcgt').
                                    '" class="weightingCoef" name="targetCoef'.$id.
                                    '" value="'.$id.'"';
                            if(isset($weighting->attribute))
                            {
                                $retval .= ' checked ';
                            }
                            $retval .= '/></td>';
                            $retval .= '<td><input type="button" class="removeWeighting" name="removeWeighting" value="X"/></td>';
                            $retval .= '<td><input type="hidden" name="weightID'.$i.'" value="'.$id.'"/></td>';            
                        }
                    }
                    else
                    {
                        $countWeights = 0;
                    }
                }
                else
                {
                    //band new qual
                    for($i=0;$i<AlevelQualification::DEFAULTINITIALWEIGHTINGS;$i++)
                    {
                        $retval .= '<tr><td><input type="text" name="weightNo'.$i.
                                '" value="'.($i+1).'"/></td>';
                        $retval .= '<td><input type="text" name="weightPec'.$i.
                                '" value="'.$this->weightingPercentage[$i].'"/></td>';
                        $retval .= '<td><input type="text" name="weightCoef'.$i.
                                '" value=""/></td>';
                        $retval .= '<td><input type="checkbox" ';
                        if($this->weightingPercentage[$i] == get_config('bcgt', 'aleveldefaultalpsperc'))
                        {
                            $retval .= 'checked="checked"';
                        }
                        $retval .= ' alt="'.
                                get_string('aleveltargetcoefficient', 'block_bcgt').
                                '" title="'.
                                get_string('aleveltargetcoefficient', 'block_bcgt').
                                '" class="weightingCoef" name="targetCoef'.$i.
                                '" value=""/></td>';
                        $retval .= '<td><input type="button" class="removeWeighting" name="removeWeighting" value="X"/></td>';
                    }
                    $countWeights = AlevelQualification::DEFAULTINITIALWEIGHTINGS;
                }
                $retval .= "<input type='hidden' name='noWeights' id='noWeights' value='$countWeights'/>";
                $retval .= '</table>';
                $retval .= "<input align='center' class='addWeight' type='button' name='addWeightRow' value='Add new Weighting'/>";

                $retval .= '</div>';
            }
		return $retval;
    }
    
    /**
	 * Used in edit qual
	 * Gets the submitted data from the edit form fields
	 * edit_qualification_form.php
	 * E.g. for Alevel its getting the POST of the ums input.
	 */
	public function get_submitted_edit_form_data()
    {
        //get the inputed UMS, units and assessments
        $this->ums = $_POST['ums'];
    }
    
    public static function has_formal_assessments()
    {
        return true;
    }

    /**
	 * using the object insert into the database
	 * Dont forget to set the ID up for the object once inserted
	 */
	public function insert_alevel_qualification($typeID, $insertUnit = true, $insertWeightings = true)
    {
        //can this actually be inserted? Its abstract after all
        //but it can always be overridden. 
        global $DB;
		//as each qual is different its easier to do this hear. 
		$dataobj = new stdClass();
		$dataobj->name = $this->name;
        $dataobj->additionalname = $this->additionalName;
		$dataobj->code = $this->code;
		$dataobj->credits = $this->ums;
        $dataobj->noyears = $this->noYears;
		$targetQualID = parent::get_target_qual($typeID);
		$dataobj->bcgttargetqualid = $targetQualID;
		$id = $DB->insert_record("block_bcgt_qualification", $dataobj);
		$this->id = $id;
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_INSERTED_QUAL, null, $this->id, null, null, null);
			
		//then build up the unit class and insert those. These have to be done here in this order
        if($insertUnit)
        {
            $this->get_unit_details(false);
            $this->insert_units();
        }
        
//        if($useFAs = get_config('bcgt', 'alevelusefa') && 
//                    !$manageCentrally = get_config('bcgt', 'alevelManageFACentrally'))
//        {
//            $this->get_assessment_details(false);
//            $this->insert_assessments();
//        }
        
        if($insertWeightings && $useWeightings = get_config('bcgt', 'alevelallowalpsweighting'))
        {
            $this->get_weighting_details(false);
            $this->insert_weightings();
        }
        return $this->id;
    }
	
	/***
	 * Deletes the qual
	 * For each type there maybe specific things we need to do
	 */
	public function delete_qualification()
    {
        $this->delete_qual_main();
    }
	
	/**
	 * Updates the qual
	 * For each type there maybe specific things we need to do
	 */
	public function update_qualification()
    {
        global $DB;
		$dataobj = new stdClass();
		$dataobj->id = $this->id;
		$dataobj->name = $this->name;
        $dataobj->additionalname = $this->additionalName;
		$dataobj->code = $this->code;
        $dataobj->noyears = $this->noYears;
		$dataobj->credits = $this->ums;
		$DB->update_record("block_bcgt_qualification", $dataobj);
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_UPDATED_QUAL, null, $this->id, null, null, null);
        
		//lets updae the unit information
        $this->get_unit_details(true);
        $this->update_units();
//        if($useFAs = get_config('bcgt', 'alevelusefa') && 
//                    !$manageCentrally = get_config('bcgt', 'alevelManageFACentrally'))
//        {
//            $this->get_assessment_details(true);
//            $this->update_assessments();
//        }
        $useWeightings = get_config('bcgt', 'alevelallowalpsweighting');
        if($useWeightings)
        {
            $this->get_weighting_details(true);
            $this->update_weightings();
        }
        
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
     * 
     * @param Unit $unit
     * @return type
     */
    function add_unit(Unit $unit)
	{
		$added = parent::add_unit_qual($unit);
		return $added;
	}
	
    /**
     * 
     * @param Unit $unit
     * @return type
     */
	function remove_unit(Unit $unit)
	{
		$removed = parent::remove_units_qual($unit);
		return $removed;	
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
        return "<br /><br /><p>Coming Soon</p><br />";
    }
    
    /**
     * gets the javascript initialisation call
     */
    public function get_edit_student_page_init_call()
    {
        //this depends on the number of tables shown
    }
    
    /**
	 * Does the qual have a final grade?
	 * E.g. Alevels or BTECS or are they just pass/fail
	 */
	public function has_final_grade()
    {
        return true;
    }
    
    /**
	 * What is the final grade if it has been set
	 */
	public function retrieve_student_award()
    {
        //probably wants to be overwritten on the 
    }
    
    /**
	 * What is the final grade
	 */
	public function calculate_final_grade()
    {
        //Formal Assesmnets and Unit Awards
    }
    
    /**
	 * Calculate the predicted grade
	 */
	public function calculate_predicted_grade()
    {
        //CETAS
    }
    
    public function qual_specific_student_load_information($studentID, $qualID)
    {
        $this->possibleValues = null;
        $this->targetGrades =null;
        $this->load_possible_values(AlevelQualification::ID);
        $this->load_target_grades();
    }
    
    //some quals have criteria just on the qual like alevels. 
	//each qual might store this differently.
	public function load_qual_criteria_student_info($studentID, $qualID)
    {
        
//        //this needs to load values onto the Formal Assessments that are on the
//        //qual
//        if($this->assessments)
//        {
//            foreach($this->assessments AS $assessment)
//            {
//                $criteria = $assessment->criteria;
//                if($criteria)
//                {
//                    $criteria->load_student_information($studentID, $qualID);
//                }
//            }
//        }
        
    }
    
    /**
     * process the edit students units page. 
     */
    public function process_edit_students_units_page($courseID = -1)
    {
        
    }
    
    public static function activity_view_page()
    {
        return "Coming Soon";
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
    
    public function save_user_grid()
    {
        //loop over each qual the user is on
        //get the fas for that qual
        //save the values. 
        $studentsQuals = get_role_quals($this->studentID, 'student', '', AlevelQualification::FAMILYID);
        if($studentsQuals)
        {
            foreach($studentsQuals AS $qual)
            {
                $qualification = Qualification::get_qualification_class_id($qual->id);
                //load up the students projects
                $projects = $qualification->get_projects();
                if($projects)
                {
                    foreach($projects AS $project)
                    {
                        $project->set_student($this->studentID);
                        $project->save_student($qual->id);
                    }
                }
            }
        }
    }
    
    public function save_subject_grid()
    {
        //loop over the users
        //get the fas for hat qual. 
        $projects = $this->get_projects();
        if($projects)
        {
            foreach($projects AS $project)
            {
                $students = $this->get_students();
                if($students)
                {
                    foreach($students AS $student)
                    {
                        $project->set_student($student->id);
                        $project->save_student($this->id);
                    }
                }
                
            }
        }
    }
    
    
    public function call_display_student_grid_external()
    {
        return $this->display_student_grid(true, true, true);
    }
    
    /**
     * Displays the Grid
     */
    public function display_student_grid($fullGridView = true, $studentView = true, $externalView = false)
    {
        if(isset($_POST['save']))
        {
            echo "saved!";
            $this->save_user_grid();
        }
        //show all formalAssessments iniially
        $formalAssessments = true;
        //show all gradebook initially
        $gradebook = true;
        //display body of the table through html
        $displayBody = true;
        //show the view and edit simple buttons. 
        global $COURSE, $PAGE, $CFG;
        $retval = '<div class="bcgtgridbuttons">';
        $retval .= "<input type='submit' id='viewsimple' name='viewsimple' class='gridbuttonswitch viewsimple' value='View Simple'/>";
        $retval .= "<br>";
        $courseID = optional_param('cID', -1, PARAM_INT);
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        $editing = false;
        $grid = optional_param('g', 's', PARAM_TEXT);
        if($grid == 'ae' || $grid == 'se')
        {
            $editing = true;
        }
        if(has_capability('block/bcgt:editstudentgrid', $context))
        {	
            $retval .= "<input type='submit' id='editsimple' name='editsimple' class='gridbuttonswitch editsimple' value='Edit Simple'/>";
            if($editing)
            {   
                $retval .= "<input type='submit' id='save' name='save' class='gridbuttonswitch gridsave' value='Save'/>";
            }
        }
        
        if ($externalView && has_capability('block/bcgt:printstudentgrid', $context)){
            $retval .= "<a href='{$CFG->wwwroot}/blocks/bcgt/grids/print_grid.php?sID={$this->studentID}&qID={$this->id}' target='_blank'><input type='button' class='gridbuttonswitch printsimple' value='Print Grid'/></a>";
        }
        
        $retval .= "</div><br clear='all' /><br />";
        $retval .= '<input type="hidden" id="grid" name="g" value="'.$grid.'"/>';
        $jsModule = array(
            'name'     => 'mod_bcgtalevel',
            'fullpath' => '/blocks/bcgt/plugins/bcgtalevel/js/bcgtalevel.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        //
        $PAGE->requires->js_init_call('M.mod_bcgtalevel.initstudentgrid', array($this->id, $this->studentID, $grid), true, $jsModule);
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        load_javascript();
        $seeTargetGrade = false;
        $seeWeightedTargetGrade = false;
        if(has_capability('block/bcgt:viewtargetgrade', $context))
        {
            $seeTargetGrade = true;
        }
        if(has_capability('block/bcgt:viewweightedtargetgrade', $context) && get_config('bcgt', 
                    'alevelallowalpsweighting'))
        {
            $seeWeightedTargetGrade = true;
        }
        $seeValueAdded = false;
        if(has_capability('block/bcgt:viewvalueaddedgrids', $context))
        {
            $seeValueAdded = true;
        }
        $seeBoth = false;
        if(has_capability('block/bcgt:viewbothweightandnormaltargetgrade', $context))
        {
            $seeBoth = true;
        }
        $retval .= $this->display_summary_table($seeTargetGrade, $seeWeightedTargetGrade, $seeValueAdded, false, $seeBoth);
        
        $retval .= '<div id="alevelStudentGridOuter" class="alevelgrid">';
        if(get_config('bcgt', 'alevelusefa') && get_config('bcgt', 'alevelManageFACentrally') 
                && !get_config('bcgt', 'alevelLinkAlevelGradeBook'))
        {
            //then all of the subjects can be in one table
            //the content here is retrieved using ajax. 
                //the table is retrieved, where the header is retrieved
                    //the body is retrieved in its own sub ajax call. 
            $retval .= $this->display_all_student_grids($editing, $formalAssessments, $gradebook);
        }
        else
        {
            //else each subject needs to have its own table
            //go and get the quals. 
            $studentsQuals = get_role_quals($this->studentID, 'student', '', AlevelQualification::FAMILYID);
            if($studentsQuals)
            {
                $i=0;
                foreach($studentsQuals AS $qual)
                {
                    $i++;
                    $retval .= '<div class="alevelSingleQual">';
                    $retval .= '<h2>'.bcgt_get_qualification_display_name($qual, 
                                true, ' ', array('family', 'trackinglevel')).'</h2>';
                    if($qual->id != $this->id)
                    {
                        //need to get the qual from the session.
                        $qualification = get_student_qual_from_session($qual->id, $this->studentID);
                        $retval .= $qualification->display_student_grid_actual($editing, $formalAssessments, $gradebook, $displayBody);
                    }
                    else
                    {
                        $retval .= $this->display_student_grid_actual($editing, $formalAssessments, $gradebook, $displayBody);
                    }
                    $retval .= '</div>';
                }
            }
        }
        $retval .= '</div>';
        return $retval;
    }
    
    protected function display_student_grid_actual($editing = false, $formalAssessments = false, 
            $gradebook = false, $displayBody = true, $print = false)
    {
        $this->get_projects();
        //create the table
        //create the header
        $id = ($print) ? 'printGridTable' : 'alevelStudentGridQ'.$this->id;
        $retval = '<table class="alevelStudentsGridTables" id="'.$id.'">';
            $retval .= $this->get_display_grid_header(false, $formalAssessments, $gradebook, true);
            $retval .= '<tbody>';
            if($displayBody)
            {  
                $retval .= $this->display_student_grid_data($editing, false, $formalAssessments, $gradebook, false, true);
            }
            $retval .= '</tbody>';
        $retval .= '</table>';
        return $retval;
    }
    
    protected function get_display_grid_header($students = true, $formalAssessments = false, $gradebook = false, $displaySubject = false)
    {
        global $CFG, $COURSE;
        $useGradeBook = false;
        $retval = '<thead><tr>';
        $rowSpan = 3;
        if($students)
        {
            //then we are on the subject grid. 
            //this need go get it from the config. 
            $retval .= bcgt_get_users_column_headings($rowSpan);
        }
        if($displaySubject)
        {
            $retval .= '<th rowspan="'.$rowSpan.'">'.get_string('subject', 'block_bcgt').'</th>';
        }
        // | Target |
        $courseID = optional_param('cID', -1, PARAM_INT);
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        
        $seeBoth = false;
        if(has_capability('block/bcgt:viewbothweightandnormaltargetgrade', $context))
        {
            $seeBoth = true;
        }
        
        $seeTargetGrade = false;
        if(has_capability('block/bcgt:viewtargetgrade', $context))
        {
            $seeTargetGrade = true;
        }
        // | Weighted |
        $seeWeightedGrade = false;
        if(has_capability('block/bcgt:viewweightedtargetgrade', $context) && get_config('bcgt', 
                'alevelallowalpsweighting'))
        {
            $string = 'target';
            if($seeTargetGrade)
            {
                $string = 'specifictargetgrade';
            }
            $seeWeightedGrade = true;
        }
        if(($seeBoth && $seeTargetGrade) || (!$seeBoth && !$seeWeightedGrade && $seeTargetGrade))
        {
            $retval .= '<th rowspan="'.$rowSpan.'">'.get_string('target', 'block_bcgt').'</th>';
        }
        if($seeWeightedGrade)
        {
            $retval .= '<th rowspan="'.$rowSpan.'">'.get_string($string, 'block_bcgt').'</th>';
        }
        // | Predicted |
        if(get_config('bcgt', 'alevelusecalcpredicted'))
        {
            $retval .= '<th rowspan="'.$rowSpan.'">'.get_string('predicted', 'block_bcgt').'</th>';
        }
        // | CETA |
        if(get_config('bcgt', 'aleveluseceta'))
        {
            $retval .= '<th rowspan="'.$rowSpan.'">'.get_string('ceta', 'block_bcgt').'</th>';
        }
        
        $showCeta = false;
        $multiplyer = 1;
        //then we are showing multiple
        if(get_config('bcgt', 'aleveluseceta'))
        {
            $showCeta = true;
            $multiplyer = 2;
        }
        if(get_config('bcgt', 'alevelusefa'))
        {
            if($formalAssessments)
            {
                $columnSpan = count($this->projects) * $multiplyer;
            }
            else{
                $columnSpan = $multiplyer;
            }

            $retval .= '<th colspan="'.$columnSpan.'">'.get_string('formalassessments', 'block_bcgt').'</th>';
        }
        $linkToGradeBook = get_config('bcgt', 'alevelLinkAlevelGradeBook');        
        if($linkToGradeBook)
        {
            //this could be coming from subject or student grids. 
            $this->get_gradebook_for_grid($students);
            if($this->gradebook && $this->gradebook->entriescount > 0)
            {
                $useGradeBook = true;
                $retval .= '<th colspan="'.$this->gradebook->entriescount.'">'.get_string('gradebook', 'block_bcgt').'</th>';
            }
        }
        $retval .= '</tr>';
        $retval .= '<tr>';
        $subHead = '';
        if(get_config('bcgt', 'alevelusefa'))
        {
            $projects = $this->get_projects();
            //now we need to display the latest formalAssessment/or all
            if($projects)
            {
                //are we showing one?
                if($formalAssessments)
                {
                    $link = $CFG->wwwroot.'/blocks/bcgt/grids/ass.php?qID='.$this->id.'&sID='.$this->studentID.'&v=sg';
                    if($students)
                    {
                        //then we are looking at the subject grid
                        $link = $CFG->wwwroot.'/blocks/bcgt/grids/ass.php?qID='.$this->id.'&sID=-1&v=qg';
                    }
                    
                    //then we are showing more than one
                    //lets reorder backwards
                    foreach($projects AS $project)
                    {
                        $retvalObj = $project->get_project_heading($showCeta, $link);
                        $retval .= $retvalObj->retval;
                        $subHead .= $retvalObj->subHead;
                    }
                }
                else
                {
                    //just get one. 
                    $project = reset($projects);
                    $retvalObj = $project->get_project_heading($showCeta, $link);
                    $retval .= $retvalObj->reval;
                    $subHead .= $retvalObj->subHead;
                }
            }
            
        }
        if($useGradeBook)
        {
            $retval .= $this->gradebook->header;
            $subHead .= $this->gradebook->subheader;
        }
        $retval .= '</tr><tr>';
        $retval .= $subHead;
        $retval .= '</tr>';
        
        $retval .= '</tr>';
        $retval .= '</thead>';
        return $retval;
    }
    
    protected function display_all_student_grids($editing, $formalAssessments, $gradebook)
    {
        //get all of the grids/quals into the one table.
        //only happens where the formal assessments are centrally managed
        
        //table:
            //header
                //target, predicted, ceta, formalassessments
        $this->get_projects();
        $displayBody = true;
        $gradebook = true;
        $retval = '<table class="alevelAllStudentsGridTables" id="alevelStudentGrid">';
        $retval .= '<thead>';
        $retval .= $this->get_display_grid_header(false, $formalAssessments, $gradebook, $displayBody);
        $retval .= '</thead>';
        $retval .= '<tbody>';
        if($displayBody)
        {
            $retval .= $this->get_all_students_grid_data($editing, $formalAssessments, $gradebook, $displayBody);
        }
        $retval .= '</tbody>';
        $retval .= '</table>';
        return $retval;
    }
    
    protected function get_all_students_grid_data($editing, $formalAssessments, $gradebook, $displayBody)
    {
        $retval = '';
        $studentsQuals = get_role_quals($this->studentID, 'student', '', AlevelQualification::FAMILYID);
        if($studentsQuals)
        {
            foreach($studentsQuals AS $qual)
            {
                $retval .= '<tr>';
                if($qual->id != $this->id)
                {
                    //need to get the qual from the session.
                    $qualification = get_student_qual_from_session($qual->id, $this->studentID);
                    $qualification->get_projects();
                    $retval .= $qualification->display_student_grid_data($editing, false, $formalAssessments, $gradebook, false, $displayBody);
                }
                else
                {
                    $retval .= $this->display_student_grid_data($editing, false, $formalAssessments, $gradebook, false, $displayBody);
                }
                $retval .= '</tr>';
            }
        }
        return $retval;
    }
    
    public function display_student_grid_data($editing = false, $displayStudents = true, 
            $formalAssessments = false, $gradebook = false, $ajaxCall = false, $displaySubject = false)
    {
        global $CFG;
        if($displayStudents)
        {
            //what was I thinking here?
            //then we need to loop over each student
            $students = $this->get_students();
            if($students)
            {
                foreach($students AS $student)
                {
                    //we need to add/load the objects into the session
                }
            }
            //pic
        }
        else
        {
            $retval = $this->get_single_grid_row($editing, false, $formalAssessments, $gradebook, $ajaxCall, $displaySubject);
        }
        return $retval;
    }
    
    protected function get_single_grid_row($editing, $multipleStudents, 
            $formalAssessments, $gradebook, $ajaxCall = false, $displaySubject = false)
    {
        global $CFG, $COURSE;
        $courseID = optional_param('cID', -1, PARAM_INT);
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        if($ajaxCall)
        {
            $retval = array();
        }
        else { 
            $retval = '';
        }
        if($multipleStudents)
        {
            //then we are shoiwng multiple students in the grid in total
            //therefore we are showing the usernames etc. 
            if($ajaxCall)
            {
                $retval[] = "";
                $retval[] = "";
                $retval[] = "";
            }
            else
            {
                $retval .= bcgt_get_users_columns($this->student, $this->id);
            }
        }
        //for this get the target grade
        //for this get the predicted grade
        //for this get the ceta grade
        $summary = $this->get_summary_information($this->id);
        $targetGradeID = -1;
        $targetGrade = '';
        $weightedTargetGrade = '';
        if($summary)
        {
            $targetGradeID = $summary->targetgradeid;
            $targetGrade = $summary->targetgrade;
            $predictedGrade = $summary->predictedgrade;
            if(isset($summary->weightedtargetgrade))
            {
                $weightedTargetGrade = $summary->weightedtargetgrade;
            }
        }
        if($ajaxCall)
        {
            $retval[] = "".$targetGrade;
            $retval[] = "".$weightedTargetGrade;
            $retval[] = "".$predictedGrade;
            //$retval[] = "".$cetagrade;
        }
        else {
            if($displaySubject)
            {
                $link1 = '';
                $link2 = '';
                if(has_capability('block/bcgt:viewclassgrids', $context))
                {
                    $link1 = '<a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/class_grid.php?cID='.$courseID.'&qID='.$this->id.'">';
                    $link2 = '</a>';
                }
                $retval .= '<td>'.$link1.$this->get_display_name(false, ' ', array('family', 'trackinglevel')).$link2.'</td>';
            }
            $seeTargetGrade = false;
            
            $seeBoth = false;
            if(has_capability('block/bcgt:viewbothweightandnormaltargetgrade', $context))
            {
                $seeBoth = true;
            }
            
            if(has_capability('block/bcgt:viewtargetgrade', $context))
            {
                $seeTargetGrade = true;
            }
            
            $predictedGrade = 'N/A';
            // | Weighted |
            $seeWeightedGrade = false;
            if(has_capability('block/bcgt:viewweightedtargetgrade', $context) && get_config('bcgt', 
                    'alevelallowalpsweighting'))
            {
                $seeWeightedGrade = true;
            }
            if(($seeBoth && $seeTargetGrade) | (!$seeBoth & !$seeWeightedGrade))
            {
                $retval .= '<td>'.$targetGrade.'</td>';
            }
            if($seeWeightedGrade)
            {
                $retval .= '<td>'.$weightedTargetGrade.'</td>';
            }
            if(get_config('bcgt', 'alevelusecalcpredicted'))
            {
                $retval .= '<td>'.$predictedGrade.'</td>';
            }
            if(get_config('bcgt', 'aleveluseceta'))
            {
                $ceta = $this->get_current_ceta($this->id, $this->studentID);
                if($ceta && $ceta->grade)
                {
                    $retval .= '<td>'.$ceta->grade.'</td>';
                }
                else
                {
                    $cetas = $this->get_most_recent_ceta($this->id, $this->studentID);
                    if($cetas)
                    {
                        $ceta = end($cetas);
                        $retval .= '<td><span class="projNonCurrentCeta">'.$ceta->grade.'</span></td>';
                        $cetaRank = $ceta->ranking;
                    }
                    else
                    {
                        $retval .= '<td></td>';
                    }
                }
            }
        }
        $targetGradeObj = $this->userTargetGrades;
        $useWeighted = false;
        if(has_capability('block/bcgt:viewweightedtargetgrade', $context))
        {
            $seeWeightedTargetGrade = true;
        }
        if(isset($targetGradeObj->weightedtargetgrade))
        {
            $weightedTargetGrade = $targetGradeObj->weightedtargetgrade->get_grade();
            if($seeWeightedTargetGrade && $weightedTargetGrade && $weightedTargetGrade != '')
            {
                $useWeighted = true;
                $this->useWeighted = true;
            }
        }
        if(get_config('bcgt', 'alevelusefa'))
        {
            //now we need to display the latest formalAssessment/or all
            
            $projects = $this->projects;
            if($projects)
            {
                if($formalAssessments)
                {
                    $retval .= $this->display_user_project_row($this->studentID, $projects, $editing, 
            $targetGradeObj, $useWeighted);
                }
                else
                {
                    //just get one. 
                    $project = reset($projects);
                    $retval .= $this->display_user_project_row($this->studentID, $projects, $editing, 
            $targetGradeObj, $useWeighted, $project->get_id());
                    
                }
            }
            
        }
        if(get_config('bcgt','alevelLinkAlevelGradeBook'))
        {
//            $retval .= '<td>'.get_string('gradebookExp', 'block_bcgt').'</td>';
            $gradeBook = $this->gradebook;
            if($gradeBook && $gradeBook->entriescount > 0)
            {
                $retval .= $gradeBook->body;
            }
            //then go and ge the actual gradebook from the course. 
            
        }
        return $retval;
    }
    
    public function get_gradebook_for_grid($courseInGeneral = false)
    {
        $retval = '';
        global $CFG;
        //is the student on that course?
        $header = '';
        $subHead = '';
        $body = '';
        if($courseInGeneral)
        {
            $courses = $this->get_courses();
        }
        else
        {
            $courses = $this->get_courses_by_user();
        }
        
        $entriesCount = 0;
        if($courses)
        {
            $courseCount = 0;
            $retval .= '<table>';
            foreach($courses AS $course)
            {
                $courseCount++;
                $courseClass = 'courseb';
                if($courseCount % 2)
                {
                    $courseClass =  'coursea';
                }
                if($courseInGeneral)
                {
                    $gradeBook = $this->get_qual_course_gradebook($course->id);
                }
                else
                {
                    $gradeBook = $this->get_user_course_gradebook($course->id);
                }
                if($gradeBook)
                {
                    $gradeCount = 0;
                    $colspan = count($gradeBook);
                    $header .= '<th colspan="'.$colspan.'" class="'.$courseClass.' gradebook">'.$course->shortname.'</th>';
                    foreach($gradeBook AS $gradeObj)
                    {
                        $subHead .= '<th class="'.$courseClass.' gradebook">'.$gradeObj->itemname.'</th>';
                        if(!$courseInGeneral)
                        {
                            $grade = $this->get_user_course_gradebook_values($course->id, $gradeObj->id);
                            $gradeCount++;
                            $gradeClass = 'gradeb';
                            if($gradeCount % 2)
                            {
                                $gradeClass = 'gradea';
                            }
                            $class = '';
                            $gridGrade = "<span class='novalue'><img src='".
                                $CFG->wwwroot."/blocks/bcgt/pix/qmark-trans.png'/></span>";
                            if($grade)
                            {
                                if($grade->scale)
                                {
                                    $scale = $grade->scale;
                                    $scales = explode(",",$scale);
                                    if($grade->finalgrade)
                                    {
                                        //the array will start at 0. 
                                        $gridGrade = $scales[($grade->finalgrade - 1)];
                                        //if we have it then try and find it in the grade so we 
                                        //can test it against the targetgrade
        //                                $this->
                                        //this will have the targetgrades on it etc.
                                        $targetGradeObj = $this->userTargetGrades;
                                        $gridGradeObj = TargetGrade::get_obj_from_grade($gridGrade, -1, $this->bcgtTargetQualID);
                                        if($gridGradeObj && $targetGradeObj)
                                        {
                                            $ranking = $gridGradeObj->get_ranking();
                                            //then we need to compare their rankings, 
                                            //but which? The weighted or non weighted. 
                                            if(isset($this->useWeighted) && $this->useWeighted)
                                            {
                                                //then compare against weighted
                                                $obj = $targetGradeObj->weightedtargetgrade;
                                            }
                                            else
                                            {
                                                //compare against non weighted
                                                $obj = $targetGradeObj->targetgrade;
                                            }
                                            if($obj)
                                            {
                                                $targetRanking = $obj->get_ranking();
                                                if($ranking - $targetRanking > 0)
                                                {
                                                    //then its
                                                    //A - B and we are ahead of target
                                                    $class = 'aheadtarget';
                                                }
                                                elseif($targetRanking - $ranking > 0)
                                                {
                                                    $class = 'behindtarget';
                                                }
                                                else
                                                {
                                                    $class = 'ontarget';
                                                }
                                            }
                                        }
                                    }

                                }
                                else
                                {
                                    $gridGrade = $grade->finalgrade; 
                                }
                            } 
                            $body .= '<td class="'.$class.' '.$courseClass.' '.$gradeClass.' gradebook">'.$gridGrade.'</td>';
                        }
                        $entriesCount++;
                    }
                }
            }
            $retval .= '<thead><tr>'.$header.'</tr><tr>'.$subHead.'</tr></thead>';
            $retval .= '<tbody><tr>'.$body.'</tr></tbody>';
            $retval .= '</table>';
        }
        $gradeBook = new stdClass();
        $gradeBook->gradebook = $retval;
        $gradeBook->header = $header;
        $gradeBook->subheader = $subHead;
        $gradeBook->body = $body;
        $gradeBook->coursecount = count($courses);
        $gradeBook->entriescount = $entriesCount;
        
        $this->gradebook = $gradeBook;
        return $retval;
    }
    
    public function get_user_course_gradebook($courseID)
    {
        global $DB;
        $sql = "SELECT distinct(items.id), items.itemname, items.itemtype,items.itemmodule, 
            scale.scale 
            FROM {course} course 
            JOIN {context} context ON context.instanceid = course.id
            JOIN {role_assignments} roleass ON roleass.contextid = context.id 
            JOIN {user} user ON user.id = roleass.userid
            JOIN {grade_items} items ON items.courseid = course.id
            LEFT OUTER JOIN {scale} scale ON scale.id = items.scaleid
            WHERE user.id = ? AND course.id = ? AND itemtype != ?";
        $params = array($this->studentID, $courseID, 'course');
        if(get_config('bcgt', 'alevelgradebookscaleonly'))
        {
            $sql .= " AND scale.name = ?";
            $params[] = 'Grade Tracker Alevel Scale';
        }
        $sql .= "ORDER BY itemmodule, items.sortorder";
        return $DB->get_records_sql($sql, $params);
    }
    
    public function get_user_course_gradebook_values($courseID, $itemID)
    {
        global $DB;
        $sql = "SELECT grades.id, items.itemname, items.itemtype,items.itemmodule, 
            grades.rawgrade, grades.finalgrade, grades.feedback, scale.scale 
            FROM {course} course 
            JOIN {context} context ON context.instanceid = course.id
            JOIN {role_assignments} roleass ON roleass.contextid = context.id 
            JOIN {user} user ON user.id = roleass.userid
            JOIN {grade_items} items ON items.courseid = course.id
            LEFT OUTER JOIN {grade_grades} grades ON grades.itemid = items.id AND grades.userid = user.id
            LEFT OUTER JOIN {scale} scale ON scale.id = items.scaleid
            WHERE user.id = ? AND course.id = ? AND itemtype != ? AND grades.itemid = ?";
        $params = array($this->studentID, $courseID, 'course', $itemID);
        if(get_config('bcgt', 'alevelgradebookscaleonly'))
        {
            $sql .= " AND scale.name = ?";
            $params[] = 'Grade Tracker Alevel Scale';
        }
        $sql .= "ORDER BY itemmodule, items.sortorder";
        return $DB->get_record_sql($sql, $params);
    }
    
    public function get_qual_course_gradebook($courseID)
    {
        global $DB;
        $sql = "SELECT distinct(items.id), items.itemname, items.itemtype,items.itemmodule 
            FROM {course} course 
            JOIN {grade_items} items ON items.courseid = course.id
            LEFT OUTER JOIN {scale} scale ON scale.id = items.scaleid
            WHERE course.id = ? AND itemtype != ?";
        $params = array($courseID, 'course');
        if(get_config('bcgt', 'alevelgradebookscaleonly'))
        {
            $sql .= " AND scale.name = ?";
            $params[] = 'Grade Tracker Alevel Scale';
        }
        $sql .= "ORDER BY itemmodule, items.sortorder";
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
	 * Returns the possible values that can be selected for this qualification type
	 * when updating criteria for students
	 */
	public static function get_possible_values($typeID)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_value} 
		WHERE bcgttypeid = ?";
        $params = array($typeID);
		return $DB->get_records_sql($sql, $params);
		
	}
    
    protected function display_summary_table($seeTargetGrade, $seeWeightedGrade, $seeValueAdded, $print = false, $seeBoth)
    {
                
        $id = ($print) ? 'printGridTable' : 'alevelSummary';
        
        $retval = '';
        //show the summary -> Get all of the subjects
        $retval .= '<div id="alevelSummary">';
            $retval .= '<table id="'.$id.'">';
                $thirdRowNeeded = false;
                $rowSpan = 2;
                if($seeValueAdded && $seeBoth && $seeTargetGrade && $seeWeightedGrade)
                {
                    $thirdRowNeeded = true;
                    $rowSpan = 3;
                }
                $retval .= '<tr><th rowspan="'.$rowSpan.'">'.get_string('subject', 'block_bcgt').'</th>';
                if($seeTargetGrade)
                {
                    $tg = '<th rowspan="'.$rowSpan.'">'.get_string('target', 'block_bcgt').'</th>';
                }
                if($seeWeightedGrade)
                {
                    if($seeTargetGrade)
                    {
                        $string = 'specifictargetgrade';
                    }
                    else
                    {
                        $string = 'targetgrade';
                    }
                    $wtg = '<th rowspan="'.$rowSpan.'">'.get_string($string, 'block_bcgt').'</th>';
                }
                
                if(($seeBoth && $seeTargetGrade) || (!$seeBoth && !$seeWeightedGrade && $seeTargetGrade))
                {
                    $retval .= $tg;
                }
                if($seeWeightedGrade)
                {
                    $retval .= $wtg;
                }
                if(get_config('bcgt', 'alevelusecalcpredicted'))
                {
                    $retval .= '<th rowspan="'.$rowSpan.'">'.get_string('predicted', 'block_bcgt').'</th>';
                }
                $subRow1 = '';
                $thirdRow = '';
                if(get_config('bcgt', 'aleveluseceta'))
                {
                    $colspan = 1;
                    if($seeValueAdded && ($seeTargetGrade || $seeWeightedGrade))
                    {
                        $colspan = 2;
                        $subColSpan = 1;
                        $subRowSpan = 1;
                        if($seeBoth && $seeTargetGrade && $seeWeightedGrade)
                        {
                            $colspan = 3;
                            $subRowSpan = 2;
                            $subColSpan = 2;
                            $thirdRow .= '<th class="ceta">'.get_string('t', 'block_bcgt').
                                    '</th><th class="ceta">'.get_string('wt', 'block_bcgt').
                                    '</th>';
                        }
                        $subRow1 .= '<th rowspan="'.$subRowSpan.'" class="ceta">'.get_string('current', 'block_bcgt').'</th>';
                        $subRow1 .= '<th colspan="'.$subColSpan.'" class="ceta">'.get_string('va', 'block_bcgt').'</th>';
                    }
                    $retval .= '<th colspan="'.$colspan.'" class="ceta">'.get_string('ceta', 'block_bcgt').'</th>';
                }
                if(get_config('bcgt', 'alevelusefa'))
                {
                    //are we using Formal Asessments?
                    $colspan = 1;
                    if($seeValueAdded && ($seeTargetGrade || $seeWeightedGrade))
                    {
                        $subColSpan = 1;
                        $subRowSpan = 1;
                        $colspan = 2;
                        if($seeBoth && $seeTargetGrade && $seeWeightedGrade)
                        {
                            $colspan = 3;
                            $subRowSpan = 2;
                            $subColSpan = 2;
                            $thirdRow .= '<th class="fa">'.get_string('t', 'block_bcgt').
                                    '</th><th class="fa">'.get_string('wt', 'block_bcgt').
                                    '</th>';
                        }
                        $subRow1 .= '<th rowspan="'.$subRowSpan.'" class="fa">'.get_string('current', 'block_bcgt').'</th>';
                        $subRow1 .= '<th colspan="'.$subColSpan.'" class="fa">'.get_string('va', 'block_bcgt').'</th>';
                    }
                    $retval .= '<th colspan="'.$colspan.'" class="fa">'.get_string('formalassessment', 'block_bcgt').'</th>';
                }
                $retval .= '</tr>';
                $retval .= '<tr>';
                $retval .= $subRow1;
                $retval .= '</tr>';
                if($thirdRowNeeded)
                {
                    $retval .= $thirdRow;
                }
                //go and get the summary. 
                $summary = $this->get_summary_information();
                if($summary)
                {
                    foreach($summary AS $record)
                    {
                        $targetGradeRank = $record->targetgraderank;
                        $weightedTargetGradeRank = $record->weightedtargetgraderank;
                        $formalAssessmentRank = 0;
                        $cetaRank = 0;
                        $retval .= '<tr>';
                            $retval .= '<td>'.$record->type.' '.$record->name.'</td>';
                            if(($seeBoth && $seeTargetGrade) || (!$seeBoth && !$seeWeightedGrade && $seeTargetGrade))
                            {
                                $retval .= '<td>'.$record->targetgrade.'</td>';
                            }
                            if($seeWeightedGrade)
                            {
                                $retval .= '<td>'.$record->weightedtargetgrade.'</td>';
                            }
                            if(get_config('bcgt', 'alevelusecalcpredicted'))
                            {
                                $retval .= '<td>'.$record->predictedgrade.'</td>';
                            }
                            //for the qual go and get the latest formalassessment updated after this date
                            //for the qual go and get the latest ceta updates after this date
                            if(get_config('bcgt', 'aleveluseceta'))
                            {
                                $ceta = $this->get_current_ceta($record->id, $this->studentID);
                                if($ceta && $ceta->grade)
                                {
                                    $retval .= '<td>'.$ceta->grade.'</td>';
                                    $cetaRank = $ceta->ranking;
                                }
                                else
                                {
                                    $cetas = $this->get_most_recent_ceta($record->id, $this->studentID);
                                    if($cetas)
                                    {
                                        $ceta = end($cetas);
                                        $retval .= '<td><span class="projNonCurrentCeta">'.$ceta->grade.'</span></td>';
                                        $cetaRank = $ceta->ranking;
                                    }
                                    else
                                    {
                                        $retval .= '<td></td>';
                                    }
                                }
                            
                                if($seeValueAdded)
                                {
                                    if(($seeBoth && $seeTargetGrade) || (!$seeBoth && !$seeWeightedGrade && $seeTargetGrade))
                                    {
                                        if($cetaRank && $targetGradeRank)
                                        {
                                            $retval .= '<td>'.($cetaRank - $targetGradeRank).'</td>';
                                        }
                                        else
                                        {
                                            $retval .= '<td></td>';
                                        }
                                    }
                                    if($seeWeightedGrade)
                                    {
                                        if($cetaRank && $weightedTargetGradeRank)
                                        {
                                            $retval .= '<td>'.($cetaRank - $weightedTargetGradeRank).'</td>';
                                        }
                                        else
                                        {
                                            $retval .= '<td></td>';
                                        }
                                    }
                                    
                                }
                            }
                            if(get_config('bcgt', 'alevelusefa'))
                            {
                                $formalAssessment = $this->get_current_formal_assessment($record->id);
                                if($formalAssessment)
                                {
                                    //loop over them all and find the one thats the closest backwards
                                    //to todays date
                                    foreach($formalAssessment AS $assessment)
                                    {
                                        $retval .= '<td>'.$assessment->value.'</td>';
                                        $formalAssessmentRank = $assessment->ranking;
                                    }
                                }
                                else {
                                    $retval .= '<td></td>';
                                }
                                if($seeValueAdded)
                                {
                                    if(($seeBoth && $seeTargetGrade) || (!$seeBoth && !$seeWeightedGrade && $seeTargetGrade))
                                    {
                                        if($formalAssessmentRank && $targetGradeRank)
                                        {
                                            $retval .= '<td>'.($formalAssessmentRank - $targetGradeRank).'</td>';
                                        }
                                        else
                                        {
                                            $retval .= '<td></td>';
                                        }
                                    }
                                    if($seeWeightedGrade)
                                    {
                                        if($formalAssessmentRank && $weightedTargetGradeRank)
                                        {
                                            $retval .= '<td>'.($formalAssessmentRank - $weightedTargetGradeRank).'</td>';
                                        }
                                        else
                                        {
                                            $retval .= '<td></td>';
                                        }
                                    }
                                }
                                
                            }
//                            $retval .= '<td>'.$record->formalassessment.'</td>';
//                            $retval .= '<td>'.$record->cetagrade.'</td>';
                            
                        $retval .= '</tr>';
                    }
                }
                
                
            $retval .= '</table>';
        $retval .= '</div>';
        
        
        
        return $retval;
    }
    
    protected function get_summary_information($qualID = -1)
    {
        global $DB;
        $sql = 'SELECT qual.id, qual.name, type.type, targetgrades.grade as targetgrade, 
            targetgrades.id AS targetgradeid, targetgrades.ranking AS targetgraderank, 
            awardgrades.grade as predictedgrade, usertargets.userid, targetgradesweighted.id as weightedgradeid, 
            targetgradesweighted.grade as weightedtargetgrade, targetgradesweighted.ranking as weightedtargetgraderank 
            FROM {block_bcgt_user_qual} userqual 
            JOIN {block_bcgt_qualification} qual ON qual.id = userqual.bcgtqualificationid 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            LEFT OUTER JOIN {block_bcgt_user_course_trgts} usertargets ON usertargets.bcgtqualificationid = qual.id 
            LEFT OUTER JOIN {block_bcgt_user_award} useraward ON useraward.bcgtqualificationid = qual.id AND useraward.userid = ? 
            LEFT OUTER JOIN {block_bcgt_target_grades} targetgrades ON targetgrades.id = usertargets.bcgttargetgradesid
            LEFT OUTER JOIN {block_bcgt_target_grades} awardgrades ON awardgrades.id = useraward.bcgttargetgradesid 
            LEFT OUTER JOIN {block_bcgt_target_grades} targetgradesweighted ON targetgradesweighted.id = usertargets.bcgtweightedgradeid';
        
        $sql .= ' WHERE userqual.userid = ? AND (type.id = ? OR type.id = ?) AND usertargets.userid = ?
            ';
        $params = array($this->studentID, $this->studentID, ASLevelQualification::ID, A2LevelQualification::ID, $this->studentID);
        if($qualID != -1)
        {
            $sql .= ' AND qual.id = ?';
            $params[] = $qualID;
        }
        if($qualID != -1)
        {
            return $DB->get_record_sql($sql, $params);
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_current_formal_assessment($qualID)
    {
        //so we want the formal assessment that is closest to todays date, but before it
        global $DB;
        $sql = "SELECT value.* FROM {block_bcgt_user_activity_ref} userref 
            JOIN {block_bcgt_activity_refs} refs ON refs.id = userref.bcgtactivityrefid 
            JOIN {block_bcgt_value} value ON value.id = bcgtvalueid 
            JOIN {block_bcgt_project} project ON project.id = refs.bcgtprojectid 
            WHERE refs.bcgtqualificationid = ? AND userref.userid = ? AND 
            project.targetdate < NOW() AND project.targetdate IS NOT NULL 
            ORDER BY project.targetdate DESC";
        return $DB->get_records_sql($sql, array($qualID,$this->studentID), 0, 1);
    }
    
    protected function get_max_formal_assessment($qualID)
    {
        
    }

    protected function get_max_ceta_assessment($qualID)
    {
        
    }
    
    /**
     * displays the subject grid. 
     */
    public function display_subject_grid()
    {
        if(isset($_POST['save']))
        {
            $this->save_subject_grid();
        }
        $formalAssessments = true;
        $gradebook = true;
        $ajaxCall = false;

        global $COURSE, $PAGE, $CFG;
        $this->get_projects();
        $retval = '<div>';
        $retval .= "<input type='submit' id='viewsimple' name='viewsimple' value='View Simple' class='viewsimple' />";
        $retval .= "<br>";
        $courseID = optional_param('cID', -1, PARAM_INT);
        $groupingID = optional_param('grID', -1, PARAM_INT);
        $sCourseID = optional_param('scID', -1, PARAM_INT);
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        $editing = false;
        $grid = optional_param('g', 's', PARAM_TEXT);
        if($grid == 'ae' || $grid == 'se')
        {
            $editing = true;
        }
        if(has_capability('block/bcgt:editclassgrids', $context))
        {	
            $retval .= "<input type='submit' id='editsimple' name='editsimple' value='Edit Simple' class='editsimple' />";
            if($editing)
            {
                $retval .= "<input type='submit' id='save' name='save' value='Save' class='gridsave' />";
            }
        }
        $retval .= '<input type="hidden" id="grid" name="g" value="'.$grid.'"/>';
        $jsModule = array(
            'name'     => 'mod_bcgtalevel',
            'fullpath' => '/blocks/bcgt/plugins/bcgtalevel/js/bcgtalevel.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        //
        $PAGE->requires->js_init_call('M.mod_bcgtalevel.initclassgrid', array($this->id), true, $jsModule);
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        load_javascript();
        
        //header
        //then get them all. 
        $retval .= '<div id="alevelClassGridOuter" class="alevelClassGridOuter alevelgrid">';
        $retval .= '<table class="alevelClassGridTables" id="alevelClassGridQ'.$this->id.'">';
        $retval .= '<thead>';
        $retval .= $this->get_display_grid_header($students = true, $formalAssessments, $gradebook,false);
        $retval .= '</thead>';
        $retval .= '<tbody>';
        //now get the students
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadTargets = true;
        $onCourse = true;
        $students = $this->get_students('', 'lastname ASC, firstname ASC', $sCourseID, $onCourse, $groupingID);
        if($students)
        {
            foreach($students AS $student)
            {
                $retval .= '<tr>';
                //load_student_information will clear previous student's information. 
                $this->load_student_information($student->id, $loadParams);
                $this->get_gradebook_for_grid();
                $retval .= $this->get_single_grid_row($editing, true, 
            $formalAssessments, $gradebook, $ajaxCall, false);
                $retval .= '</tr>';
            }
        }
        $retval .= '</tbody>';
        $retval .= '</table>';
        $retval .= '</div>';
        $retval .= '</div>';
        return $retval;
    }
    
//    public static function get_instance($qualID, $params, $loadParams)
//    {
//        return new AlevelQualification($qualID, $params, $loadParams);
//    }
    
    public static function get_pluggin_qual_class($typeID = -1, $qualID = -1, 
            $familyID = -1, $params = null, $loadParams = null)
    {
        //units and criteria are directly on the qualification
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        global $CFG;
        $subTypeID = -1;
        if($params)
        {
            if($params->subtype)
            {
                $subTypeID = $params->subtype;
            }
        }
        if($subTypeID == -1)
        {
            if(isset($_REQUEST['subtype']))
            {
                $subTypeID = $_REQUEST['subtype'];
            }
        }
        $levelID = 3;
        if(!$params)
        {
            $params = new stdClass();
        }
        $params->level = new Level($levelID);
        $params->subType = new Subtype($subTypeID);
        switch($subTypeID)
        {
            case(AlevelQualification::ASSubTypeID):
                return new ASLevelQualification($qualID, $params, $loadParams);
                break;
            case(AlevelQualification::A2SubTypeID):
                require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/classes/A2LevelQualification.class.php');
                return new A2LevelQualification($qualID, $params, $loadParams);
                break;
            default;
                return new ALevelQualification($qualID, $params, $loadParams);
        }
        return false;
    }
    
    /**
	 * Used to get the credits value from the database
	 * @param $id
	 */
	protected static function retrieve_ums($id)
	{
		global $DB;
		$sql = "SELECT credits FROM {block_bcgt_qualification} WHERE id = ?";
		return $DB->get_record_sql($sql, array($id));
	}
	
    /**
     * Gets the formal assessments on the qual
     * @global type $DB
     * @param type $id
     * @return type
     */
	protected static function retrieve_assessments($id)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_criteria} WHERE bcgtqualificationid = ?";
		return $DB->get_records_sql($sql, array($id));
	}
    
    private function get_unit_drop_down_ass($unitID)
	{
		$retval = '';
		foreach($this->units AS $unit)
		{
			$selected = '';
			if($unit->get_id() == $unitID)
			{
				$selected = 'selected';
			}
			$retval .= "<option $selected value='".$unit->get_id()."'>".$unit->get_name()."</option>";
		}
		return $retval;
	}

    /**
	 * This gets the information from the edit_qualification form for the unit
	 * information for this new qual or updating qual?
	 */
	public function get_unit_details($update = true)
	{
		$unitsArray = array();
        if(isset($_POST['noUnits']))
        {
            $noUnits = $_POST['noUnits'];
            if(!$update)
            {
                for($i=0;$i<=$noUnits;$i++)
                {
                    //so is the new unit there and ready to be added to the qual?
                    if(isset($_POST['unitName'.$i]))
                    {
                        //TODO PARAMS
                        $params = new stdClass();
                        $params->name = $_POST['unitName'.$i];
                        $loadLevel = new stdClass();
                        $loadLevel->loadLevel = Qualification::LOADLEVELALL;
                        $unit = new ALevelUnit(-1, $params, $loadLevel);
                        $unit->set_ums($_POST['unitUMS'.$i]);
                        $unitsArray[$i] = $unit;
                    }	
                }
            }
            else
            {
                //we are updating
                //so the number of units will equal the total no of units on the form. 
                //but some of these might be new ones!
                //every row has a field that is called unitID{ID/No}
                //where id is the database ID or is the number of the unit
                //if its the number of the unit its _{no} so as not to confuse when
                //the id of a unit is 8, and there is a number of unit 8. 
                for($i=0;$i<=$noUnits;$i++)
                {
                    if(isset($_POST['unitID'.$i]))
                    {
                        $id = $_POST['unitID'.$i];
                        $useID = true;
                        if(strpos($id, '_') !== false)
                        {
                            //then it was a new one added and it has an underscore
                            $id = substr($id, 1);
                            $useID = false;
                        }
                        $params = new stdClass();
                        $params->name = $_POST['unitName'.$id];
                        $loadLevel = new stdClass();
                        $loadLevel->loadLevel = Qualification::LOADLEVELALL;
                        if(!$useID)
                        {
                            //if the id contains an underscore than it was a new one added
                            $unit = new ALevelUnit(-1, $params, $loadLevel);
                        }
                        else
                        {
                            $unit = new ALevelUnit($id, $params, $loadLevel);
                        }

                        $unit->set_ums($_POST['unitUMS'.$id]);
                        $unitsArray[$id] = $unit;
                    }
                }
            }
        }
        $this->units = $unitsArray;
	}
	
    /**
     * Gets the assessment details from the edit_qual form
     * @param type $update
     */
	public function get_assessment_details($update = true)
	{
		$assessmentArray = array();
        if(isset($_POST['noAss']))
        {
            $noAss = $_POST['noAss'];
            if(!$update)
            {
                //we are inserting. therefore we dont know the id
                for($i=0;$i<=$noAss;$i++)
                {
                    if(isset($_POST['assName'.$i]))
                    {
                        //TODO  PARAMS
                        $params = new stdClass();
                        $params->name = $_POST['assName'.$i];
                        $targetDate = $_POST['assDate'.$i];
                        $params->targetDate = strtotime($targetDate);
                        $params->awardID = -1;
                        $loadParams = new stdClass();
                        $loadParams->loadLevel = Qualification::LOADLEVELALL;

                        $assessmentObj = new stdClass;
                        $assessment = new Criteria(-1, $params, $loadParams);
                        $assessmentObj->criteria = $assessment;
                        //we need to first get the id of the unit that has been selected from the drop downs
                        //but the id that was on the options was in fact the counter
                        //the units have now been inserted and have proper id's
                        //so we need to associate the assessments with the proper ids
                        if($_POST['assUnit'.$i] != -1)
                        {
                            //assessmentUnits is set on insert of units 
                            $unitID = $this->assessmentUnits[$_POST['assUnit'.$i]];
                        } 
                        else
                        {
                            $unitID = -1;
                        }

                        //$assessmentObj->unitID = $_POST['assUnit'.$i];
                        $assessmentObj->unitID = $unitID;
                        $assessmentArray[] = $assessmentObj;
                    }
                }
            }
            else
            {
                //we are updating
                for($i=0;$i<=$noAss;$i++)
                {
                    if(isset($_POST['assID'.$i]))
                    {
                        $id = $_POST['assID'.$i];
                        $useID = true;
                        if(strpos($id, '_') !== false)
                        {
                            //then it was a new one added and it has an underscore
                            $id = substr($id, 1);
                            $useID = false;
                        }
                        $assessmentObj = new stdClass;
                        //TODO PARAMS
                        $params = new stdClass();
                        $params->name = $_POST['assName'.$id];
                        $targetDate = $_POST['assDate'.$id];
                        $params->targetDate = strtotime($targetDate);
                        $params->awardID = -1;
                        $loadParams = new stdClass();
                        $loadParams->loadLevel = Qualification::LOADLEVELALL;
                        if(!$useID)
                        {
                            //if the id contains an underscore than it was a new one added
                            $assessment = new Criteria(-1, $params, $loadParams);
                        }
                        else
                        {
                            $assessment = new Criteria($id, $params, $loadParams);
                        }
                        $unitID = $_POST['assUnit'.$id];
                        if($unitID != -1)
                        {
                            //so the assessment is now being added to a unit
                            //was it before? Because if it wasnt before, then it was on the qual and this needs changing
                            //BUT it will get changed by the call to update criteria later!!!
                        }
                        $assessmentObj->criteria = $assessment;
                        $assessmentObj->unitID = $unitID;
                        $assessmentArray[$id] = $assessmentObj;
                    }
                }
            }
        }
		$this->assessments = $assessmentArray;
	}
    
    /**
     * 
     * @param type $update
     */
    public function get_weighting_details($update = true)
    {
        //weightings are stored on the object as
        //array() with each having an object. No, Percentage, coefficient and if its the target
        $weightings = array();
        if(isset($_POST['noWeights']))
        {
            $noWeightings = $_POST['noWeights'];
            if(!$update)
            {
                for($i=0;$i<$noWeightings;$i++)
                {
                    $weightingObj = new stdClass;
                    $weightingObj->bcgtqualificationid = $this->id;
                    if(isset($_POST['weightNo'.$i]))
                    {
                        $weightingObj->number = $_POST['weightNo'.$i];
                    }
                    if(isset($_POST['weightPec'.$i]))
                    {
                        $weightingObj->percentage = $_POST['weightPec'.$i];
                    }
                    if(isset($_POST['weightCoef'.$i]))
                    {
                        $weightingObj->coefficient = $_POST['weightCoef'.$i];
                    }
                    if(isset($_POST['targetCoef'.$i]))
                    {
                        $weightingObj->attribute = $_POST['targetCoef'.$i];
                    }
                    $weightings[] = $weightingObj;
                }
            }
            else
            {
                //we are updating
                for($i=0;$i<=$noWeightings;$i++)
                {
                    if(isset($_POST['weightID'.$i]))
                    {
                        $id = $_POST['weightID'.$i];
                        if(strpos($id, '_') !== false)
                        {
                            //then it was a new one added and it has an underscore
                            $id = substr($id, 1);
                        }
                        $weightingObj = new stdClass;
                        $weightingObj->id = $id;
                        if(isset($_POST['weightNo'.$id]))
                        {
                            $weightingObj->number = $_POST['weightNo'.$id];
                        }
                        if(isset($_POST['weightPec'.$id]))
                        {
                            $weightingObj->percentage = $_POST['weightPec'.$id];
                        }
                        if(isset($_POST['weightCoef'.$id]))
                        {
                            $weightingObj->coefficient = $_POST['weightCoef'.$id];
                        }
                        if(isset($_POST['targetCoef'.$id]))
                        {
                            $weightingObj->attribute = $_POST['targetCoef'.$id];
                        }
                        $weightings[$id] = $weightingObj;
                    }
                }
            }
        }
        $this->weightings = $weightings;
        
    }
    
    /**
     *Inserts the assessments into the database 
     */
    function insert_units()
	{
        //TODO also add to the students!!!!
        
		$assessmentUnits = array();
		//as it comes in the keys for these are the counters of i
		//but as it leaves the ids are needed to match the assessments
		//to the correct units. 
		foreach($this->units AS $key => $unit)
		{
			$unit->insert_unit();
			$unitID = $unit->get_id();
			$assessmentUnits[$key] = $unitID;
			$unit->add_to_qualification($this->id);
		}
		$this->assessmentUnits = $assessmentUnits;
	}
	
    /**
     * Inserts the assessments into the database
     */
	function insert_assessments()
	{
		foreach($this->assessments AS $assessment)
		{
			$unitID = $assessment->unitID;
			$criteria = $assessment->criteria;
			if($unitID != -1)
			{
				$criteria->insert_criteria($unitID);
			}
			else
			{
				//we are just adding it to the qualification
				$criteria->insert_criteria_on_qual($unitID, $this->id);
			}
		}
	}
	
    /**
     * UIpdates the Units into the database
     * @return boolean
     */
	function update_units()
	{
		//ok are there any new ones that we havent inserted before?
		//are there any we are changing?
		//are there any we are removing?
		
		//first are there any on the object?
		if($this->units)
		{
			//second, were there any before?
			$units = $this->retrieve_units_qual();
			if(!$units)
			{
				//then there are non in the database so
				//then we need to insert them all!!
				$this->insert_units();
				return true;
			}
			//ok so we know we need to do some updating
			//are there any new ones to add?
			foreach($this->units AS $unit)
			{
				if(array_key_exists($unit->get_id(), $units))
				{
					//then we know that it existed in the database and it needs saving
					$unit->save();
				}
				else
				{
                    //so it doesnt exist in the database so lets add it. 
					$unit->insert_unit();
					$unit->add_to_qualification($this->id);
				}
			}
			
			//now search for ones that need deleting
			//by searhing through the database and seeing if it is NOT in the ones on
			//the object
			foreach($units AS $unit)
			{
				if(!array_key_exists($unit->id, $this->units))
				{
					$this->delete_unit($unit->id);
				}
			}
		}
		else
		{
			//no? so lets delete all of the units from the database
			$this->delete_all_selection();
		}
		
	}
	
    /**
     * Updates the assessments into the database
     * @return boolean
     */
	function update_assessments()
	{
		//ok are there any new ones that we havent inserted before?
		//are there any we are changing?
		//are there any we are removing?
		
		//first are there any on the object?
		if($this->assessments)
		{
			//second, were there any before?
			//$assessments = $this->retrieve_criteria_qual();
			$dbAssessments = $this->retrieve_criteria_qual_and_units();
			if(!$dbAssessments)
			{
				//then there are non in the database so
				//then we need to insert them all!!
				$this->insert_assessments();
				return true;
			}
			//ok so we know we need to do some updating
			//are there any new ones to add?
			foreach($this->assessments AS $assessment)
			{
				//does the one on the current object exist in the database?
				if(array_key_exists($assessment->criteria->get_id(), $dbAssessments))
				{
					//then we know that it existed in the database and it needs saving
					$criteria = $assessment->criteria;
					$criteria->update_criteria($assessment->unitID);
				}
				else
				{
                    //so it doesnt exist in the database so lets add it. 
					if($assessment->unitID == -1)
					{
						$assessment->criteria->insert_criteria_on_qual($assessment->unitID, $this->id);
					}
					else
					{	
						$assessment->criteria->insert_criteria($assessment->unitID, $this->id);
					}
				}
			}
			
			//now search for ones that need deleting
			//by searhing through the database and seeing if it is NOT in the ones on
			//the object
			foreach($dbAssessments AS $assessment)
			{
				if(!array_key_exists($assessment->id, $this->assessments))
				{
					$this->delete_assessment($assessment->id);
				}
			}
		}
		else
		{
			//no? so lets delete all of the units from the database
			$this->delete_all_ass_selection();
		}
	}
	
	private function delete_all_selection()
	{
		$this->insert_qual_units_history();
		$this->delete_units();
	}
	
	private function delete_all_ass_selection()
	{
        global $DB;
		$this->insert_qual_criteria_history();
		$DB->delete_records('block_bcgt_criteria', 'bcgtqualificationid', $this->id);
	}

	public function delete_units()
	{
        global $DB;
		$DB->delete_records('block_bcgt_qual_units', 'bcgtqualificationid', $this->id);
	}
		
	public function delete_unit($unitID)
	{
        global $DB;
		$this->insert_qual_units_history();
		$this->insert_student_units_history();
		$DB->delete_records('block_bcgt_qual_units', array('bcgtqualificationid'=>$this->id, 'bcgtunitid'=>$unitID));
	}
	
	public function delete_assessment($assessmentID)
	{
        global $DB;
		$this->insert_criteria_history($assessmentID);
		$DB->delete_records('block_bcgt_criteria', 'id', $assessmentID);
	}
    
    protected function get_qual_summary_grades()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_criteria}";
        return $DB->get_record_sql($sql, array());
    }
    
    public function print_grid()
    {
            
        global $CFG, $COURSE;
        
        //show all formalAssessments iniially
        $formalAssessments = true;
        //show all gradebook initially
        $gradebook = true;
        //display body of the table through html
        $displayBody = true;
        
        $courseID = optional_param('cID', -1, PARAM_INT);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        else
        {
            $context = context_course::instance($COURSE->id);
        }
        
        $seeTargetGrade = false;
        $seeWeightedTargetGrade = false;
        if(has_capability('block/bcgt:viewtargetgrade', $context))
        {
            $seeTargetGrade = true;
        }
        if(has_capability('block/bcgt:viewweightedtargetgrade', $context))
        {
            $seeWeightedTargetGrade = true;
        }
        $seeValueAdded = false;
        if(has_capability('block/bcgt:viewvalueaddedgrids', $context))
        {
            $seeValueAdded = true;
        }
        $seeBoth = false;
        if(has_capability('block/bcgt:viewbothweightandnormaltargetgrade', $context))
        {
            $seeBoth = true;
        }
        
        
        echo "<!doctype html><html><head>";
        echo "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/print.css'>";
        
        $logo = get_config('bcgt', 'logoimgurl');
        
        echo load_javascript(false, true);
        
        echo "</head><body style='background: url(\"{$logo}\") no-repeat;'>";
                
        echo "<div class='c'>";
            echo "<h1>{$this->get_display_name()}</h1>";
            echo "<h2>".fullname($this->student)." ({$this->student->username})</h2>";
            echo "<br><br><br>";
            
            echo $this->display_summary_table($seeTargetGrade, $seeWeightedTargetGrade, $seeValueAdded, true, $seeBoth);
            
            echo "<br><br><br>";
            
            if(get_config('bcgt', 'alevelusefa') && get_config('bcgt', 'alevelManageFACentrally') 
                && !get_config('bcgt', 'alevelLinkAlevelGradeBook'))
            {
                //then all of the subjects can be in one table
                //the content here is retrieved using ajax. 
                    //the table is retrieved, where the header is retrieved
                        //the body is retrieved in its own sub ajax call. 
                echo $this->display_all_student_grids(false, $formalAssessments, $gradebook);
            }
            else
            {
                //else each subject needs to have its own table
                //go and get the quals. 
                $studentsQuals = get_role_quals($this->studentID, 'student', '', AlevelQualification::FAMILYID);
                if($studentsQuals)
                {
                    $i=0;
                    foreach($studentsQuals AS $qual)
                    {
                        $i++;
                        echo '<div class="alevelSingleQual">';
                        echo '<h2>'.bcgt_get_qualification_display_name($qual, 
                                    true, ' ', array('family', 'trackinglevel')).'</h2>';
                        if($qual->id != $this->id)
                        {
                            //need to get the qual from the session.
                            $qualification = get_student_qual_from_session($qual->id, $this->studentID);
                            echo $qualification->display_student_grid_actual(false, $formalAssessments, $gradebook, $displayBody, true);
                        }
                        else
                        {
                            echo $this->display_student_grid_actual(false, $formalAssessments, $gradebook, $displayBody, true);
                        }
                        echo '</div>';
                    }
                }
            }
            
        
        echo "</div>";
            
            //echo "<br class='page_break'>";
            
            // Comments and stuff
            // TODO at some point
            
        echo "<script> $('a').contents().unwrap(); </script>";
        echo "</body></html>";
        
    }
    
    public function print_class_grid()
    {
        
        global $CFG;
        
        $formalAssessments = true;
        $gradebook = true;
        
        $this->get_projects();
        
        echo "<!doctype html><html><head>";
        echo "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/print.css'>";
        
        $logo = get_config('bcgt', 'logoimgurl');
        
        echo load_javascript(false, true);
        
        echo "</head><body style='background: url(\"{$logo}\") no-repeat;'>";
                
        echo "<div class='c'>";
            echo "<h1>{$this->get_display_name()}</h1>";
                        
            echo "<br><br><br><br>";
            
            echo "<table id='printGridTable' class='aLvl'>";
            
            echo '<thead>';
            echo $this->get_display_grid_header($students = true, $formalAssessments, $gradebook,false);
            echo '</thead>';
            
            
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELALL;
            $loadParams->loadTargets = true;
            $students = $this->get_students('', 'lastname ASC, firstname ASC');
            if($students)
            {
                foreach($students AS $student)
                {
                    echo '<tr>';
                    //load_student_information will clear previous student's information. 
                    $this->load_student_information($student->id, $loadParams);
                    $this->get_gradebook_for_grid();
                    echo $this->get_single_grid_row(false, true, 
                        $formalAssessments, $gradebook, false, false);
                    echo '</tr>';
                }
            }
            
            
            echo "</table>";
            echo "</div>";
            
            //echo "<br class='page_break'>";
            
            // Comments and stuff
            // TODO at some point
            
        echo "<script> $('a').contents().unwrap(); </script>";
        echo "</body></html>";
        
    }
    
    public function has_auto_target_grade_calculation(){
        return true;
    }
    
}

?>
