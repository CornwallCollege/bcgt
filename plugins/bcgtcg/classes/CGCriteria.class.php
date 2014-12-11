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
class CGCriteria extends Criteria {
    //put your code here
    
    protected $grading;
    protected $weighting;
    protected $ordernum;
    
    public function CGCriteria($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        parent::Criteria($criteriaID, $params, $loadLevel);
        
        if ($criteriaID < 1){
            
            if (isset($params->grading)){
                $this->grading = $params->grading;
            } else {
                $this->grading = 'PMD';
            }
            
            if (isset($params->weighting)){
                $this->weighting = $params->weighting;
            }
                        
        } 
        
        if (isset($params->ordernum)){
            $this->ordernum = $params->ordernum;
        }
        
        
        $this->grading = $this->get_grading();
        
    }
    
    public static function get_instance($criteriaID, $params, 
            $loadLevel = Qualification::LOADLEVELCRITERIA)
    {
        return new CGCriteria($criteriaID, $params, $loadLevel);
    }
    
    public function get_weighting(){
        return $this->weighting;
    }
    
    public function set_grading($v){
        $this->grading = $v;
    }
    
    public function get_grading(){

        global $DB;
                
        if (is_null($this->grading)){
            $get = $DB->get_record("block_bcgt_criteria_att", array("bcgtcriteriaid" => $this->id, "attribute" => "GRADING"));
            if ($get){
                $this->grading = $get->value;
            }
        }
        
        return $this->grading;
    }
        
    public function set_weighting($v){
        $this->weighting = $v;
    }
    
    public function get_order(){
        return $this->ordernum;
    }
    
    public function set_order($v){
        $this->ordernum = $v;
    }
    
    /**
     * Get the possible "met" values we can have, based on our grading structure
     */
    public function get_met_values( $typeID = CGQualification::ID ){
     
        global $DB;
        
        switch($this->grading)
        {
            case 'P':
            case 'DATE':    
            case 'TEXT':
                $records = $DB->get_records_select("block_bcgt_value", "bcgttypeid = ? AND specialval = 'A'", array($typeID), "ranking ASC", "*", 0, 1);
            break;
        
            case 'PMD':
                $records = $DB->get_records_select("block_bcgt_value", "bcgttypeid = ? AND specialval = 'A' AND shortvalue IN ('P','M','D')", array($typeID), "ranking ASC");
            break;
        
            case 'PCD':
                $records = $DB->get_records_select("block_bcgt_value", "bcgttypeid = ? AND specialval = 'A' AND shortvalue IN ('P','C','D')", array($typeID), "ranking ASC");
            break;
        
            default:
                $records = $DB->get_records_select("block_bcgt_value", "bcgttypeid = ? AND specialval = 'A'", array($typeID), "ranking ASC");
            break;
        
        }
        
        return $records;
        
    }
    
    public function get_non_met_values( $typeID = CGQualification::ID ){
        
        global $DB;
        
        $records = $DB->get_records_sql("SELECT * FROM {block_bcgt_value}
                                        WHERE bcgttypeid = ? AND (specialval != 'A' OR specialval IS NULL)
                                        ORDER BY
                                            CASE
                                                WHEN shortvalue = 'PA' THEN 0
                                                WHEN shortvalue = 'X' THEN 1
                                                WHEN shortvalue = 'L' THEN 2
                                                ELSE 3
                                            END ASC, value ASC", array($typeID));
        return $records;
        
    }
    
    protected function get_achieved_value(){
        
        global $DB;
        
        return $DB->get_record("block_bcgt_value", array("bcgttypeid" => $this->get_tracking_type(), "shortvalue" => "A"));
        
    }
    
    
    public function insert_criteria($unitID)
	{
		
        global $DB;
		$stdObj = new stdClass();
		$stdObj->name = $this->name;
		$stdObj->details = $this->details;
		$stdObj->bcgtunitid = $unitID;
        $stdObj->weighting = $this->weighting;
        $stdObj->ordernum = $this->ordernum;
        if (isset($this->parentCriteriaID)) $stdObj->parentcriteriaid = $this->parentCriteriaID;
		$id = $DB->insert_record('block_bcgt_criteria', $stdObj);
		
		$this->id = $id;
        
        // Sub Criteria
        if($this->subCriteriaArray)
		{
			foreach($this->subCriteriaArray AS $subCriteria)
			{
				$subCriteria->set_parent_criteria_ID($id);
				$subCriteria->insert_criteria($unitID);
			}
		}
       
        // Grading attribute
        $stdObj = new stdClass();
        $stdObj->bcgtcriteriaid = $this->id;
        $stdObj->attribute = 'GRADING';
        $stdObj->value = $this->grading;
        $DB->insert_record("block_bcgt_criteria_att", $stdObj);
                
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
        $stdObj->ordernum = $this->ordernum;
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
        
        
        
		
		
        // Grading attribute
        $grading = $DB->get_record("block_bcgt_criteria_att", array("bcgtcriteriaid" => $this->id, "attribute" => "GRADING"));
        $stdObj = new stdClass();
        $stdObj->id = $grading->id;
        $stdObj->value = $this->grading;
        $DB->update_record("block_bcgt_criteria_att", $stdObj);
        
        // Log
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, "updated criteria", null, null, $this->unitID, null, $this->id);
	}
    
    
    /**
	 * Creates a new value object and sets this criterias student value to
	 * the new object
	 * @param $valueID
     * returns false if there are no sub criteria. 
	 */	
	public function update_students_value($valueID, $updateSub = false)
    {
        
        // If setting it to late, set the flag as late
        $value = new Value($valueID);
        if ($value){
            if ($value->get_special_val() == 'L'){
                $this->studentFlag = 'L';
            } elseif ($valueID == -1){
                // If we set it back to not attempted, clear any late flag
                $this->studentFlag = null;
            }
        }
        
        $this->studentValue = $value;  

        return false;
        
    }
    
    
    
    /**
     * 
     * @param type $editing
     * @param type $advancedMode
     * @param type $unit
     * @param type $user
     * @param type $qual
     * @param type $grid
     * @param type $printTable
     */
    public function get_grid_td($editing, $advancedMode, $unit, $user, $qual, $grid, $printTable = false){

        global $CFG;
        
        $output = "";
        
        $valueObj = null;
        $valueObj = $this->get_student_value();
        
        $value = null;
        $longValue = '';
        $studentValueID = null;
        $studentCriteriaMet = false;
        
        if($valueObj)
        {
            $studentValueID = $valueObj->get_id();
            $value = $valueObj->get_short_value();
            $studentCriteriaMet = $valueObj->is_met($studentValueID);
            $longValue = $valueObj->get_value();
        }
        
        if($value == null){
            $value = "N/A";
            $longValue = 'Not Attempted';
        }
        
        $this->comments = iconv('UTF-8', 'ASCII//TRANSLIT', $this->comments); 
                
        $output .= "<div class='criteriaTDContent'>";
                                
        // Simple, Non-Editing
        if(!$advancedMode && !$editing)
        {
            
            if ($this->grading == 'TEXT')
            {
                if (!empty($this->userDefinedValue)){
                    $output .= "<span>{$this->userDefinedValue}</span>";
                } else {
                    $imageObj = CGQualification::get_grid_image($valueObj);
                    $image = $imageObj->image;
                    $class = $imageObj->class;
                    $output .= "<span><img src='{$image}' class='stuValue {$class}' /></span>";
                }
            }
            else
            {
                
                $imageObj = CGQualification::get_grid_image($valueObj);
                $image = $imageObj->image;
                $class = $imageObj->class;

                if ($this->studentFlag == 'L') $class .= ' wasLate ';

                $output .= "<span id='stCID_".$this->get_id()."_UID_".
                        $unit->get_id()."_SID_".$this->studentID."_QID_".
                        $this->qualID."' class='stuValue $class' criteriaID='{$this->id}' studentID='{$this->studentID}'><img src='".
                        $image."'/></span>";

                if($this->grading == 'DATE')
                {
                    if ($this->awardDate > 0)
                    {
                        if (ctype_digit($this->awardDate)) $output .= "<br><reallysmall>(".date('d M Y', $this->awardDate).")</reallysmall>";
                        else $output .= "<br><reallysmall>({$this->awardDate})</reallysmall>";
                    }
                }
                
            }
                       
        }
        // Advanced, non-editing
        elseif($advancedMode && !$editing)
        {
            
            if ($this->grading == 'TEXT')
            {
                if (!empty($this->userDefinedValue)){
                    $output .= "<span>{$this->userDefinedValue}</span>";
                } else {
                    $output .= "<span>{$value}</span>";
                }
            }
            else
            {
            
                $class = '';
                if ($this->studentFlag == 'L') $class .= ' wasLate ';

                $output .= "<span id='stCID_".$this->get_id()."_UID_".
                        $unit->get_id()."_SID_".$this->studentID."_QID_".
                        $this->qualID."' class='stuValue stuValue{$value} {$class}' criteriaID='{$this->id}' studentID='{$this->studentID}'>{$value}</span>";

                if($this->grading == 'DATE')
                {
                    if ($this->awardDate > 0)
                    {
                        if (ctype_digit($this->awardDate)) $output .= "<br><reallysmall>(".date('d M Y',$this->awardDate).")</reallysmall>";
                        else $output .= "<br><reallysmall>({$this->awardDate})</reallysmall>";
                    }
                }
            
            }
                    

        }
        // Advanced, editing
        elseif($advancedMode && $editing)
        {

            $class = '';
            if ($this->studentFlag == 'L') $class .= ' wasLate ';
            
            // First do the grading values
            $output .= "<select name='cID_".$this->get_id()."' class='criteriaValueSelect {$class}' grid='{$grid}' criteriaid='{$this->get_id()}' unitid='{$unit->get_id()}' qualid='{$this->qualID}' studentid='{$user->id}'>";
            $output .= "<option value='-1'></option>";
            
            $grades = $this->get_met_values();                

            if ($grades)
            {
                foreach($grades as $grade)
                {
                    
                    $chk = ($studentValueID == $grade->id) ? 'selected' : '';
                    $output .= "<option value='{$grade->id}' {$chk}>{$grade->shortvalue} - {$grade->value}</option>";
                }
            }
                
                                                        
            // Now do the rest of the values, such as late, referred, etc...
            $possibleValues = $this->get_non_met_values();   
            if ($possibleValues)
            {
                foreach($possibleValues as $value)
                {
                    $chk = ($studentValueID == $value->id) ? 'selected' : '';
                    $output .= "<option value='{$value->id}' {$chk}>{$value->shortvalue} - {$value->value}</option>";
                }
            }
            
            $output .= "</select>&nbsp;";
            
            $username = $this->student->username;
            $fullname = fullname($this->student);
            $unitname = bcgt_html($unit->get_name());
            $critname = bcgt_html($this->name);
            
            // Change this so each thing has its own attribute, wil be easier
            $commentImgID = "cmtCell_cID_".$this->get_id()."_uID_".$unit->get_id()."_SID_".$this->studentID.
                            "_QID_".$this->qualID;
        
            if (!empty($this->comments))
            {
                $output .= "<img id='{$commentImgID}' criteriaid='{$this->id}' unitid='{$unit->get_id()}' studentid='{$this->studentID}' qualid='{$this->qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='{$grid}' class='editComments' title='Click to Edit Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/comment_edit.png' alt='".get_string('editcomments', 'block_bcgt')."' style='width:20px;vertical-align:middle;'  />";
            }
            else
            {
                $output .= "<img id='{$commentImgID}' criteriaid='{$this->id}' unitid='{$unit->get_id()}' studentid='{$this->studentID}' qualid='{$this->qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='{$grid}' class='addComments' title='Click to Add Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/comment_add.png' alt='".get_string('addcomment', 'block_bcgt')."' style='width:20px;vertical-align:middle;' />";
            }


        }
        // Simple, editing
        else
        {
            
            if ($this->grading == 'TEXT')
            {
                $output .= "<input class='criteriaUserDefinedValue' grid='{$grid}' criteriaid='{$this->get_id()}' unitid='{$unit->get_id()}' qualid='{$this->qualID}' studentid='{$user->id}' type='text' value='{$this->userDefinedValue}' />";
            }
            else
            {
            
            
                $class = '';
                if ($this->studentFlag == 'L') $class .= ' wasLate ';

                $values = $this->get_met_values();

                // If there is only one pass value, use a checkbox, else use a select menu
                if ($this->grading == 'P')
                {
                    $checked = ($studentCriteriaMet) ? 'checked' : '';
                    $output .= "<input class='criteriaCheck {$class}' grid='{$grid}' criteriaid='{$this->get_id()}' unitid='{$unit->get_id()}' qualid='{$this->qualID}' studentid='{$user->id}' type='checkbox' name='met' value='1' {$checked} />";
                }
                elseif($this->grading == 'DATE')
                {
                    if (is_numeric($this->awardDate) && $this->awardDate > 0){
                        $date = date('d-m-Y', $this->awardDate);
                    } elseif (!is_numeric($this->awardDate) && !is_null($this->awardDate)){
                        $date = date('d-m-Y', strtotime($this->awardDate)) . '!';
                    } else {
                        $date = '';
                    }
                    $output .= "<input class='criteriaValueDate' grid='{$grid}' criteriaid='{$this->get_id()}' unitid='{$unit->get_id()}' qualid='{$this->qualID}' studentid='{$user->id}' type='text' name='metdate' value='{$date}' />";
                }
                else
                {
                    $output .= "<select name='cID_".$this->get_id()."' class='criteriaValueSelect {$class}' grid='{$grid}' criteriaid='{$this->get_id()}' unitid='{$unit->get_id()}' qualid='{$this->qualID}' studentid='{$user->id}'>";
                    $output .= "<option value='-1'></option>";
                        foreach($values as $value)
                        {
                            $chk = ($studentValueID == $value->id) ? 'selected' : '';
                            if (!empty($value->customvalue)) $value->value = $value->customvalue;
                            $output .= "<option value='{$value->id}' {$chk}>{$value->shortvalue} - {$value->value}</option>";
                        }
                    $output .= "</select>";
                }
            
            }
                
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
                $output .= "<img id='{$commentImgID}' criteriaid='{$this->id}' unitid='{$unit->get_id()}' studentid='{$this->studentID}' qualid='{$this->qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='{$grid}' class='editComments' title='Click to Edit Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/comment_edit.png' alt='".get_string('editcomments', 'block_bcgt')."' />";
            }
            else
            {
                $output .= "<img id='{$commentImgID}' criteriaid='{$this->id}' unitid='{$unit->get_id()}' studentid='{$this->studentID}' qualid='{$this->qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='{$grid}' class='addComments' title='Click to Add Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/comment_add.png' alt='".get_string('addcomment', 'block_bcgt')."' />";
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
        
        $output .= "<div class='criteriaContent'>".Criteria::build_criteria_tooltip($this->id, $this->qualID, $this->studentID)."</div>";
        
        return $output;
        
        
    }
    
    
    
    
}