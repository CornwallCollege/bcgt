<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Criteria.class.php');

class BespokeCriteria extends Criteria {
    //put your code here
    
    protected $grading;
    protected $weighting;
    
    public function BespokeCriteria($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        
        global $DB;
        
        parent::Criteria($criteriaID, $params, $loadLevel);
        
        // If set in params
        if ($criteriaID < 1){
            
            if (isset($params->grading)){
                $this->grading = $params->grading;
            }
            
            if (isset($params->weighting)){
                $this->weighting = $params->weighting;
            }
            
        } 
        else
        {
            // Else get from DB
            $criteria = $DB->get_record("block_bcgt_bespoke_criteria", array("bcgtcritid" => $criteriaID));
            if ($criteria)
            {
                $this->grading = $criteria->gradingstructureid;
                $this->weighting = $criteria->weighting;
            }
        }
        
        
        
                
    }
    
    public function get_grading(){
        return $this->grading;
    }
    
    public function get_grading_name(){
        
        global $DB;
        
        $name = '';
        
        if ($this->grading)
        {
            $record = $DB->get_record("block_bcgt_bspk_crit_grading", array("id" => $this->grading));
            if ($record)
            {
                $name = $record->name;
            }
        }
        
        return $name;
        
    }
    
    public function get_grading_info(){
        global $DB;
        if (!$this->grading || $this->grading < 1) return false;
        return $DB->get_records("block_bcgt_bspk_c_grade_vals", array("critgradingid" => $this->grading), "rangelower DESC");
    }
    
    public function get_grading_serialized_object(){
        
        global $DB;
        
        if ($this->grading)
        {
            
            $grading = $DB->get_record("block_bcgt_bspk_crit_grading", array("id" => $this->grading));
            if ($grading)
            {
                
                $grading->vals = array();
                
                $vals = $DB->get_records("block_bcgt_bspk_c_grade_vals", array("critgradingid" => $grading->id));
                if ($vals)
                {
                    
                    foreach($vals as $val)
                    {
                        $grading->vals[] = $val;
                    }
                    
                }
                
            }
            
        }
                
        return serialize($grading);
        
    }
    
    
    public function get_grading_met_info(){
        global $DB;
        if (!$this->grading || $this->grading < 1) return false;
        return $DB->get_records("block_bcgt_bspk_c_grade_vals", array("critgradingid" => $this->grading, "met" => 1), "rangelower DESC");
    }
    
    public function get_grading_value_info($shortgrade){
        global $DB;
        if (!$this->grading || $this->grading < 1) return false;
        $record = $DB->get_record("block_bcgt_bspk_c_grade_vals", array("critgradingid" => $this->grading, "shortgrade" => $shortgrade));
        
        if (!$record)
        {
            $record = $DB->get_record("block_bcgt_bspk_c_grade_vals", array("critgradingid" => null, "shortgrade" => $shortgrade));
        }
        
        return $record;
        
    }
    
    public function get_weighting(){
        return $this->weighting;
    }
    
    public function set_grading($v){
        $this->grading = $v;
    }
    
    public function set_weighting($v){
        $this->weighting = $v;
    }
    
    
    /**
	 * Gets the students criteria values from the database
	 * @param $studentID
	 * @param $qualID
	 * @param $unitID
	 */
	private function get_students_value($studentID, $qualID, $unitID = -1)
	{
		//TODO change when we talk about projects
		global $DB;
		$sql = "SELECT c.*, bv.critgradingid, bv.grade, bv.shortgrade, bv.points, bv.rangelower, bv.rangeupper, bv.met, bv.img
                FROM {block_bcgt_user_criteria} c
                INNER JOIN {block_bcgt_criteria} criteria ON criteria.id = c.bcgtcriteriaid
                INNER JOIN {block_bcgt_bespoke_criteria} bc ON bc.bcgtcritid = c.bcgtcriteriaid
                LEFT JOIN {block_bcgt_bspk_c_grade_vals} bv ON bv.id = c.bcgtvalueid
                WHERE c.bcgtcriteriaid = ?
                AND c.bcgtqualificationid = ?
                AND c.userid = ?";
        $params = array($this->id, $qualID, $studentID);
		if($unitID != -1)
		{
			$sql .= " AND criteria.bcgtunitid = ?";
            $params[] = $unitID;
		}        
                
		return $DB->get_record_sql($sql, $params);
	}
    
    public function get_possible_values()
    {
        
        global $DB;
        
        return $DB->get_records_select("block_bcgt_bspk_c_grade_vals", "critgradingid IS NULL OR critgradingid = ?", array($this->grading), "met DESC, points ASC, rangelower ASC, shortgrade ASC");
        
    }
    
    
    public function get_td($grid, $editing, $student, $qual, $unit)
    {
     
        global $CFG;
        
        // Get value
        $valueObj = $this->get_student_value();
        $value = "N/A";
        $longValue = "Not Attempted";
        if ($valueObj)
        {
            $value = $valueObj->get_short_value();
            $longValue = $valueObj->get_value();
        }
        
        // Check if this exists as a value within our custom grade values table
        $valueInfo = $this->get_grading_value_info($value);
        $image = BespokeQualification::get_grid_image($value, $longValue, $valueInfo);
        
        $possibleValues = $this->get_possible_values();
        
        
        $output = "";
        
        $this->comments = iconv('UTF-8', 'ASCII//TRANSLIT', $this->comments); 
                
        $class = ($this->comments && !empty($this->comments)) ? 'hasComments' : '';
                
        $w = ($editing) ? 100 : 40;
        
        $output .= "<td style='width:{$w}px;min-width:{$w}px;max-width:{$w}px;' id='C_{$this->id}U_{$unit->get_id()}Q_{$qual->get_id()}S_{$this->studentID}' class='val {$class}' title='' criteriaID='{$this->id}' unitID='{$unit->get_id()}' qualID='{$qual->get_id()}' studentID='{$this->studentID}'>";
        
        $output .= "<div class='criteriaTDContent'>";
        
        switch($editing)
        {
            
            // Edit
            case true:
                
                $output .= "<select class='criteriaSelect' studentID='{$this->studentID}' criteriaID='{$this->id}' unitID='{$unit->get_id()}' qualID='{$qual->get_id()}' grid='{$grid}'>";
                
                    $output .= "<option value=''></option>";
                    
                    $break = false;
                    
                    if ($possibleValues)
                    {
                        foreach($possibleValues as $val)
                        {
                            
                            if ($val->met == 0 && !$break)
                            {
                                $output .= "<option value='' disabled='disabled'>------------</option>";
                                $break = true;
                            }
                            
                            $sel = ($valueObj && $valueObj->get_id() == $val->id) ? 'selected' : '';
                            $output .= "<option value='{$val->id}' {$sel}>{$val->shortgrade} - {$val->grade}</option>";
                        }
                    }
                    
                    
                $output .= "</select>";
                
            break;
        
        
            // View
            case false:
                
                $output .= "<img src='{$image->image}' class='{$image->class}' alt='{$value}' />";
                $output .= "<div class='criteriaValueTooltip'>".$this->build_criteria_tooltip_content($qual, $unit)."</div>";
                
            break;
        
        }
        
        $output .= "</div>";
        
        // Hidden div for adding a comment when it's enabled
        $output .= "<div class='hiddenCriteriaCommentButton'>";
        
            $username = $this->student->username;
            $fullname = fullname($this->student);
            $unitname = bcgt_html($unit->get_name());
            $critname = bcgt_html($this->name);
            
            // Change this so each thing has its own attribute, wil be easier
            $commentImgID = "cmtCell_cID_".$this->get_id()."_uID_".$unit->get_id()."_SID_".$this->studentID.
                            "_QID_".$this->qualID;
        
            if (!empty($this->comments))
            {
                $output .= "<img id='{$commentImgID}' criteriaid='{$this->id}' unitid='{$unit->get_id()}' studentid='{$this->studentID}' qualid='{$this->qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='{$grid}' class='editComments' title='Click to Edit Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/comment_edit.png' alt='".get_string('editcomments', 'block_bcgt')."' />";
            }
            else
            {
                $output .= "<img id='{$commentImgID}' criteriaid='{$this->id}' unitid='{$unit->get_id()}' studentid='{$this->studentID}' qualid='{$this->qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='{$grid}' class='addComments' title='Click to Add Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/comment_add.png' alt='".get_string('addcomment', 'block_bcgt')."' />";
            }
            
            
            if ($editing)
            {
            
                $output .= "<div class='popUpDiv bcgt_comments_dialog' id='dialog_{$this->studentID}_{$this->get_id()}_{$this->qualID}' qualID='{$this->qualID}' unitID='{$unit->get_id()}' critID='{$this->get_id()}' studentID='{$this->studentID}' grid='{$grid}' imgID='{$commentImgID}' title='Comments'>";
                    $output .= "<span class='commentUserSpan'>Comments for {$fullname} : {$username}</span><br>";
                    $output .= "<span class='commentUnitSpan'>{$unit->get_display_name()}</span><br>";
                    $output .= "<span class='commentCriteriaSpan'>{$this->get_name()}</span><br><br><br>";
                    $output .= "<textarea class='dialogCommentText' id='text_{$this->studentID}_{$this->get_id()}_{$this->qualID}'>".bcgt_html($this->comments)."</textarea>";
                $output .= "</div>";
            
            }
            
        
        $output .= "</div>";
        
        
        $output .= "</td>";
        return $output;
        
    }
    
    protected function build_criteria_tooltip_content($qual, $unit)
    {
        
        global $DB;
                        
        $output = "";
        
        $output .= "<div class='c'>";
        
            $output .= "<small>".fullname($this->student)." ({$this->student->username})</small><br>";
            $output .= "<strong>{$qual->get_display_name()}</strong><br>";
            $output .= "<span>{$unit->get_name()}</span><br>";
            $output .= "<h3>{$this->name}</h3><br>";
            $output .= "<p style='text-align:left;'>".bcgt_html($this->details, true)."</p>";
            
            if ($this->comments)
            {
                $output .= "<p class='commentsSection'>".bcgt_html($this->comments, true)."</p>";
            }
            
            
            $output .= "<br>";
            if ($this->studentValue)
            {
                
                if(!is_null($this->dateUpdated)) $date = $this->dateUpdated;
                elseif(!is_null($this->dateSet)) $date = $this->dateSet;
                else $date = 'N/A';
                
                if ($this->studentValue->is_met())
                {
                    $output .= "<strong class='critValueInTooltip'>{$this->studentValue->get_value()} - {$date}</strong>";
                }
                else
                {
                    $output .= "<span>{$this->studentValue->get_value()} - {$date}</span>";
                }                
                                
            }
            
        $output .= "</div>";
        
        
        
        
        return $output;
        
    }
    
    
    /**
	 * Loads the students information onto the criteria
	 * This will load the studentsValue (i.e. achieved or not achieved)
	 * as an object
	 * sets the dates and the people who updated it.
	 * @param unknown_type $studentID
	 * @param unknown_type $qualID
	 * @param unknown_type $unitID
	 */
	public function load_student_information($studentID, $qualID, $unitID = -1, $loadSubCriteria = true)
	{
        global $CFG, $DB;
        
        $this->clear_student_information();
		//retrieve the students value if it has been set             
		$this->studentID = $studentID;
        $this->qualID = $qualID;
        $this->student = $DB->get_record("user", array("id" => $studentID));
		$studentCriteria = $this->get_students_value($studentID, $qualID, $unitID);
                
//        $studentGrade = $this->get_students_grade($studentID, $qualID, $unitID); 
//        $studentTargetGrade = $this->get_students_target_grade($studentID, $qualID, $unitID);
        if($studentCriteria)
		{	     
			if($studentCriteria->bcgtvalueid && $studentCriteria->grade)
			{
                $params = new stdClass();
                $params->value = $studentCriteria->grade;
                $params->shortValue = $studentCriteria->shortgrade;
                $params->ranking = $studentCriteria->points;
                require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeValue.class.php';
				$value = new BespokeValue($studentCriteria->bcgtvalueid);
				$this->studentValue = $value;
			}	
                                                
			$this->comments = $studentCriteria->comments;
			$this->studentCriteriaID = $studentCriteria->id;
			if($studentCriteria->dateset)
			{
				$this->dateSet = date('d M Y', $studentCriteria->dateset);	
                $this->dateSetUnix = $studentCriteria->dateset;
			}
			if($studentCriteria->dateupdated)
			{
				$this->dateUpdated = date('d M Y', $studentCriteria->dateupdated);
                $this->dateUpdatedUnix = $studentCriteria->dateupdated;
			}
			$this->setByUserID = $studentCriteria->setbyuserid;
			$this->updatedByUserId = $studentCriteria->updatedbyuserid;
            $this->userDefinedValue = htmlentities($studentCriteria->userdefinedvalue, ENT_QUOTES);
            if(!is_null($studentCriteria->targetdate)){
                $this->targetDate = $studentCriteria->targetdate;
            }
            $this->studentFlag = (isset($studentCriteria->flag)) ? $studentCriteria->flag : false;
            
            if(isset($studentCriteria->awarddate))
            {
                $this->awardDate = $studentCriteria->awarddate;
            }
            
		}

		if($loadSubCriteria && $subCriteria = $this->get_sub_criteria())
        {
            foreach($subCriteria AS $sub)
            {
                $sub->load_student_information($studentID, $qualID, $unitID);
            }    
        }
       
	}
    
    
    /**
	 * Creates a new value object and sets this criterias student value to
	 * the new object
	 * @param $valueID
     * returns false if there are no sub criteria. 
	 */	
	public function update_students_value($valueID, $updateSub = true)
	{
		$value = new BespokeValue($valueID);
		$this->studentValue = $value;        
        // Log
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, LOG_VALUE_GRADETRACKER_UPDATED_CRIT_AWARD, $this->studentID, $this->qualID, $this->unitID, null, $this->id, $valueID);

		return true;
        
	}
    
	public function insert_criteria($unitID)
	{
		
        global $DB;
		$stdObj = new stdClass();
		$stdObj->name = $this->name;
		$stdObj->details = $this->details;
		$stdObj->bcgttypeawardid = $this->awardID;
		$stdObj->bcgtunitid = $unitID;
        $stdObj->targetdate = $this->targetDate;
        $stdObj->weighting = $this->weighting;
        if (isset($this->parentCriteriaID)) $stdObj->parentcriteriaid = $this->parentCriteriaID;
		$id = $DB->insert_record('block_bcgt_criteria', $stdObj);
		if($this->subCriteriaArray)
		{
			foreach($this->subCriteriaArray AS $subCriteria)
			{
				$subCriteria->set_parent_criteria_ID($id);
				$subCriteria->insert_criteria($unitID);
			}
		}
		$this->id = $id;
        
        // Bespoke criteria record
        $obj = new stdClass();
        $obj->bcgtcritid = $this->id;
        $obj->gradingstructureid = $this->grading;
        $obj->weighting = (float) $this->weighting;
        $DB->insert_record("block_bcgt_bespoke_criteria", $obj);
                
        // Log
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, LOG_VALUE_GRADETRACKER_INSERTED_CRIT, null, null, $unitID, null, $this->id);        
	
        
	}
    
    
    
    
    
     /**
	 * Updates the criteria in the database
	 */
	public function update_criteria($unitID = -1)
	{        
        global $DB;
		$stdObj = new stdClass();
		$stdObj->id = $this->id;
		$stdObj->name = $this->name;
		$stdObj->details = $this->details;
        $stdObj->targetdate = $this->targetDate;
        $stdObj->weighting = $this->weighting;
        $stdObj->parentcriteriaid = ($this->parentCriteriaID > 0) ? $this->parentCriteriaID : null;
        if($unitID != -1)
		{
			$stdObj->bcgtunitid = $unitID;
		}
        
		$DB->update_record('block_bcgt_criteria', $stdObj);
		
		if($this->subCriteriaArray)
		{
			foreach($this->subCriteriaArray AS $subCriteria)
			{
				$subCriteria->set_parent_criteria_ID($this->id);
				//check if it exists
				if($subCriteria->exists())
				{
					$subCriteria->update_criteria($unitID);
				}
				else
				{
					$subCriteria->insert_criteria($unitID);
				}
				
			}
		}
        
        // Bespoke criteria record
        $bespoke = $DB->get_record("block_bcgt_bespoke_criteria", array("bcgtcritid" => $this->id));
        if (!$bespoke)
        {
            // Bespoke criteria record
            $obj = new stdClass();
            $obj->bcgtcritid = $this->id;
            $obj->gradingstructureid = $this->grading;
            $obj->weighting = $this->weighting;
            $DB->insert_record("block_bcgt_bespoke_criteria", $obj);
            $bespoke = $DB->get_record("block_bcgt_bespoke_criteria", array("bcgtcritid" => $this->id));
        }
        
        $bespoke->gradingstructureid = $this->grading;
        $bespoke->weighting = $this->weighting;
        $DB->update_record("block_bcgt_bespoke_criteria", $bespoke);
        
        // Log
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, "updated criteria", null, null, $this->unitID, null, $this->id);
	}
    
    
    
    /**
     * 
     * @global type $CFG
     * @global type $DB
     * @param type $editing
     * @param type $advancedMode
     * @param type $unit
     * @param type $user
     * @param type $qual
     * @param type $grid
     * @param type $printTable
     * @return string
     */
    public function get_student_grid_td($editing, $advancedMode, $unit, $user, $qual, $grid, $printTable = false)
    {

        global $CFG, $DB;
                       
        $output = "";
        
        $valueObj = $this->get_student_value();
        
        $value = null;
        $longValue = '';
        $studentValueID = null;
        $studentCriteriaMet = false;
        
        if($valueObj)
        {
            $studentValueID = $valueObj->get_id();
            $value = $valueObj->get_short_value();
            $studentCriteriaMet = $valueObj->is_met();
            $longValue = $valueObj->get_value();
        }
        
        if($value == null){
            $value = "N/A";
            $longValue = 'Not Attempted';
        }
                
                
        // Simple, Non-Editing
        if(!$advancedMode && !$editing)
        {
            
            $imageObj = BespokeQualification::get_simple_grid_images($value, $longValue);

            $image = $imageObj->image;
            $class = $imageObj->class;
            $output .= "<span id='stCID_".$this->get_id()."_UID_".
                    $unit->get_id()."_SID_".$this->studentID."_QID_".
                    $this->id."' class='stuValue $class'><img src='".
                    $image."'/></span>";

        }
        // Advanced, non-editing
        elseif($advancedMode && !$editing)
        {
            
             $output .= "<span id='stCID_".$this->get_id()."_UID_".
                    $unit->get_id()."_SID_".$this->studentID."_QID_".
                    $this->id."' class='stuValue stuValue{$value}'>{$value}</span>";

        }
        // Advanced, editing
        elseif($advancedMode && $editing)
        {

            
            // First do the grading values
            $output .= "<select name='cID_".$this->get_id()."' class='criteriaValueSelect'>";
            $output .= "<option value='-1'></option>";
            
            if ($this->grading > 0){
                $grading = $this->get_met_values();                

                
                    foreach($grading as $grade)
                    {
                        $output .= "<option value='{$grade->id}'>{$grade->shortgrade} - {$grade->grade}</option>";
                    }
                                                        
            }
            
            // Now do the rest of the values, such as late, referred, etc...
            $possibleValues = $this->get_non_met_values();   
            if ($possibleValues)
            {
                foreach($possibleValues as $value)
                {
                    $output .= "<option value='{$value->id}'>{$value->shortgrade} - {$value->grade}</option>";
                }
            }
            
            $output .= "</select>";


        }
        // Simple, editing
        else
        {
            
            // What grading structure does this criteria use?
            if ($this->grading > 0){
                $grading = $this->get_met_values();
                
                // If there is only one pass value, use a checkbox, else use a select menu
                if (count($grading) == 1)
                {
                    $checked = ($studentCriteriaMet) ? 'checked' : '';
                    $output .= "<input class='criteriaCheck' grid='{$grid}' criteriaid='{$this->get_id()}' unitid='{$unit->get_id()}' qualid='{$qual->get_id()}' studentid='{$user->id}' type='checkbox' name='met' value='1' {$checked} />";
                }
                else
                {
                    $output .= "<select name='cID_".$this->get_id()."' class='criteriaValueSelect'>";
                    $output .= "<option value='-1'></option>";
                        foreach($grading as $grade)
                        {
                            $output .= "<option value='{$grade->id}'>{$grade->shortgrade} - {$grade->grade}</option>";
                        }
                    $output .= "</select>";
                }
                
            } else {
                $output .= "?";
            }
            
            
        }

        
        
        return $output;
        
    }
    
    
    /**
	 * Returns the possible values that can be selected for this qualification type
	 * when updating criteria for students
	 */
	public function get_met_values()
	{
		global $DB;
        return $DB->get_records("block_bcgt_bspk_c_grade_vals", array("critgradingid" => $this->grading), "rangelower ASC");
	}
    
    /**
	 * Returns the possible values that can be selected for this qualification type
	 * when updating criteria for students
	 */
	public function get_non_met_values()
	{
		global $DB;
        return $DB->get_records("block_bcgt_bspk_c_grade_vals", array("critgradingid" => null), "shortgrade ASC");
	}
    
    
    public static function get_instance($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        return new BespokeCriteria($criteriaID, $params, $loadLevel);
    }
    
    
    public static function get_crit_grading_structures()
    {
        
        global $DB;
        
        $results = array();
        
        $records = $DB->get_records("block_bcgt_bspk_crit_grading");
        
        if ($records)
        {
            foreach($records as $record)
            {
                
                $values = $DB->get_records("block_bcgt_bspk_c_grade_vals", array("critgradingid" => $record->id), "rangelower ASC");
                if ($values)
                {
                    
                    $record->values = $values;
                    $results[$record->id] = $record;
                    
                }
                
            }
        }
                
        return $results;
        
    }
    
}

?>
