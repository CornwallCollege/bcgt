<?php
/**
 * Description of ALevelUnit
 *
 * @author mchaney
 */
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeQualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeCriteria.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeAward.class.php');


class BespokeUnit extends Unit {
	
	//any constants or properties
    
    protected $credits;
    protected $displaytype;
    protected $grading;
    
    public function BespokeUnit($unitID, $params, $loadParams)
    {
        global $DB;
        
        parent::Unit($unitID, $params, $loadParams);
        //get any specific things. e.g BTEC credits and Alevel UMS
        
        $unit = $DB->get_record("block_bcgt_bespoke_unit", array("bcgtunitid" => $unitID));
        if ($unit)
        {
            $this->displaytype = $unit->displaytype;
            $this->level = new Level(-1, $unit->level);
            $this->grading = $unit->gradingstructureid;
        }
        
        
        if($unitID != -1)
		{
			$creditsObj = BespokeUnit::retrieve_credits($unitID);
			if($creditsObj)
			{
                if(!$creditsObj->credits)
                {
                    //get default credits for this object if we can
                    $defaultCredits = $this->get_default_credits();
                    $this->credits = $defaultCredits;
                }
                else
                {
                    $this->credits = $creditsObj->credits;
                }
			}
		}
		elseif($params)
		{
            if(!isset($params->credits) || !$params->credits)
            {
                //get default credits for this object if we can
                $defaultCredits = $this->get_default_credits();
                $this->credits = $defaultCredits;
            }
            else
            {
                $this->credits = $params->credits;
            }
		}
        else 
        {
            $defaultCredits = $this->get_default_credits();
            $this->credits = $defaultCredits;
        }
        
        
    }
    
    /**
	 * Loads the students information into the unit and subsequent criteria.
	 * Does the unit have an award, if so can we retrieve it?
	 * If not can we calculate it?
	 * Is the student actually doing this unit?
	 * For each criteria load the students information
	 * @param unknown_type $studentID
	 * @param unknown_type $qualID
	 */
    //$loadLevel = QUALIFICATION::LOADLEVELUNITS, $loadAward = false
	public function load_student_information($studentID, $qualID, 
            $loadParams = null)
	{
        global $DB;
        $this->clear_student_information();
		$this->studentID = $studentID;
        $this->qualID = $qualID;
        
        $this->student = $DB->get_record("user", array("id" => $studentID));
        
		//is the student doing this unit?
		$onThisUnit = $this->student_doing_unit($qualID);
                                        
		if($onThisUnit)
		{
			$this->studentDoing = true;
			//for each criteria load_student_information. 
			if($loadParams && $loadParams->loadLevel && 
                    $loadParams->loadLevel >= Qualification::LOADLEVELCRITERIA && 
                    $this->criterias)
			{
                $loadSubCriteria = ($loadParams->loadLevel >= Qualification::LOADLEVELSUBCRITERIA) ? true : false;
				foreach($this->criterias AS $criteria)
				{
					$criteria->load_student_information($studentID, $qualID, $this->id, $loadSubCriteria);
				}	
			}
            
			//can this unit have an award calculated for it?
			if($loadParams && isset($loadParams->loadAward) && $this->unit_has_award())
			{
				//what is the award the student currently has for this unit?
				$unitAward = $this->retrieve_unit_award($qualID);
				if($unitAward)
				{
                    $params = new stdClass();
                    $params->award = $unitAward->grade;
                    $params->rank = $unitAward->points;
                    
					$award = new BespokeAward($unitAward->id, $params);
					$this->userAward = $award;
                                                            
                    if(!is_null($unitAward->dateupdated) && $unitAward->dateupdated > 0){
                        $this->set_date_updated($unitAward->dateupdated);
                    }                  
				}
				else
				{
					//ok go and calculate it if we can.
					$this->userAward = $this->calculate_unit_award($qualID);
				}
			}
             
            // Get the comments on the student's unit as well
            $this->set_comments($this->retrieve_comments());
            //TODO qual specific:              
			//does this unit have specific values/grades/informaton set for this student?
			//this may want to be put onto the qualification sepcific unit class
			//as the id fields and info fields may eventually point
			//to different things depending on the quals.
			$this->load_student_values($qualID);
		}
		else
		{
			$this->studentDoing = false;
		}	     
                
	}
    
    
    public function build_tooltip_content($qual)
    {
        
        global $DB;
                        
        $output = "";
        
        $output .= "<div class='c'>";
        
            $output .= "<small>".fullname($this->student)." ({$this->student->username})</small><br>";
            $output .= "<strong>{$qual->get_display_name()}</strong><br>";
            $output .= "<h3>{$this->name}</h3><br>";
            $output .= "<p>".bcgt_html($this->details, true)."</p>";
            $output .= "<br>";
            
            // Criteria
            $criteria = $this->load_criteria_flat_array();
            
            $output .= "<div class='l'>";
            
            if ($criteria)
            {
                                
                foreach($criteria as $criterion)
                {
                    
                    $output .= "<strong>{$criterion->get_name()}</strong> - ".bcgt_html($criterion->get_details())."<br>";
                    
                }
                                
            }
            
            $output .= "</div>";
            
            $output .= "<br>";
            
            if ($this->comments)
            {
                $output .= "<p class='commentsSection'>".bcgt_html($this->comments, true)."</p>";
            }
            
            
            $output .= "<br>";
            
            if(!is_null($this->dateUpdated)) $date = date('d M Y', $this->dateUpdated);
            elseif(!is_null($this->dateSet)) $date = date('d M Y', $this->dateSet);
            else $date = 'N/A';
                
            $unitAward = $this->get_user_award();
            if ($unitAward)
            {
                if ($unitAward->get_id() > 0)
                {
                    $output .= "<strong class='critValueInTooltip'>{$unitAward->get_award()} - {$date}</strong>";
                }
            }
            
            
        $output .= "</div>";
        
        
        
        
        return $output;
        
    }
    
    
    /**
	 * This function gets the award given to the unit for this qualification, 
	 * unit and student.
	 */
	protected function retrieve_unit_award($qualID)
	{
		global $DB;
		$sql = "SELECT award.id, award.grade, award.points, unit.dateupdated 
            FROM {block_bcgt_user_unit} as unit
		JOIN {block_bcgt_bspk_u_grade_vals} AS award ON award.id = unit.bcgttypeawardid
		WHERE unit.bcgtqualificationid = ? AND unit.userid = ? AND 
		unit.bcgtunitid = ?";
		return $DB->get_record_sql($sql, array($qualID, $this->studentID, $this->id));
		
	}
    
    
    
    public function set_student_award(BespokeAward $award)
	{
		$this->userAward = $award;
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
        return BespokeQualification::ID;
    }
	
	/*
	 * Gets the name of the associated qualification. 
	 */
	public function get_type_name()
    {
        return BespokeQualification::NAME;
    }
    
    /*
	 * Gets the name of the associated qualification family. 
	 */
	public function get_family_name()
    {
        return BespokeQualification::NAME;
    }
	
	/**
	 * Get the family of the qual.
	 */
	public function get_familyID()
    {
        return BespokeQualification::FAMILYID;
    }
    
    public function get_display_type(){
        return $this->displaytype;
    }
    
    public function get_credits(){
        return $this->credits;
    }
    
    public function get_grading(){
        return $this->grading;
    }
    
    public function get_grading_name(){
        
        global $DB;
        
        $name = '';
        
        if ($this->grading)
        {
            $record = $DB->get_record("block_bcgt_bspk_unit_grading", array("id" => $this->grading));
            if ($record)
            {
                $name = $record->name;
            }
        }
        
        return $name;
        
    }
    
    
    public function get_grading_info(){
        
        global $DB;
        
        if ($this->grading)
        {
            
            $grading = $DB->get_record("block_bcgt_bspk_unit_grading", array("id" => $this->grading));
            if ($grading)
            {
                
                $grading->vals = array();
                
                $vals = $DB->get_records("block_bcgt_bspk_u_grade_vals", array("unitgradingid" => $grading->id));
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
    
    
    public function get_display_name()
    {
        
        $output = "";
        $output .= $this->get_display_type() . " ";
        
        if ($this->level){
            $output .= "(L{$this->level->get_level()}) ";
        }
        
        $output .= $this->get_uniqueID() . " ";
        $output .= $this->get_name();
        
        
        
        return $output;
        
    }
    
    /**
	 * Gets the form fields that will go on edit_unit_form.php
	 * They are different for each unit type
     * e.g. for ALEVEL there are ums 
	 */
	public function get_edit_form_fields()
    {
        
        $retval = "";
        
        $credits = optional_param('credits', $this->credits, PARAM_INT);
        
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='credits'>".get_string('credits', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<input style='width:40px;' type='number' name='credits' value='{$credits}' />";
            $retval .= "</div>";
        $retval .= "</div>";
        
         // Assign/Create grading structure
        
        $structures = BespokeUnit::get_unit_grading_structures();
                
        $retval .= '<div class="inputContainer"><div class="inputLeft">';
        $retval .= '<span class="required">*</span><label for="credits">'.get_string('gradingstructure', 'block_bcgt')
                .': </label></div>';
		$retval .= '<div class="inputRight">';
            $retval .= '<select name="gradingstructure">';
                $retval .= '<option value="">'.get_string('pleaseselect', 'block_bcgt').'</option>';
                
                if ($structures)
                {
                    foreach($structures as $structure)
                    {
                        $sel = (isset($this->grading) && $this->grading == $structure->id) ? 'selected' : '';
                        $output = "<option value='{$structure->id}' {$sel}>";
                        $output .= $structure->name . ": &nbsp;&nbsp;&nbsp; ";
                        foreach($structure->values as $val)
                        {
                            $output .= $val->grade . " ({$val->rangelower} - {$val->rangeupper} ) &nbsp;";
                        }
                        
                        $output .= "</option>";
                        $retval .= $output;
                        
                    }
                }
                
            $retval .= '</select>';
        $retval .= '</div></div>';
        
        return $retval;
        
    }
    
    public function recursive_form_to_criteria($lvl, $num, $randNum= 0){
        
        // Count total levels in post
        $cnt = count($_POST['criterionIDs']);
        
        $results = array();
        
        
        
        return (!empty($results)) ? $results : null;
        
    }
    
    /**
	 * Used in edit unit
	 * Gets the criteria tablle that will go on edit_unit_form.php
	 * This is different for each unit type. 
	 */
	public function get_edit_criteria_table()
    {
        $retval = "";
        
        $retval .= "<script>var numOfCriterion = 0;var dynamicNumOfCriterion = 0;var dynamicNumOfSubCriterion = 0;var arrayOfSubCriterion = new Array();</script>";
        
        $retval .= "<script>
            var critGradings = '';
            critGradings += '<option value=\"\">Criteria Grading Structure...</option>';";
        
        $structures = BespokeCriteria::get_crit_grading_structures();
        if ($structures)
        {
            foreach($structures as $structure)
            {
                $retval .= "critGradings += '<option value=\"{$structure->id}\">';";
                $retval .= "critGradings += '{$structure->name} &nbsp;&nbsp;&nbsp;';";
                foreach($structure->values as $val)
                {
                    $retval .= "critGradings += '{$val->grade} [{$val->points}] ({$val->rangelower} - {$val->rangeupper} ), &nbsp;'; ";
                }

                $retval .= "critGradings += '</option>';";
            }
        }
        
        $retval .= "</script>";
        
        $retval .= "<a href='#' id='addNewCrit'>".get_string('addcriteria', 'block_bcgt')."</a><br><br>";
        
        $retval .= "<div id='criteriaHolder'>";
        
            $retval .= "<table id='criteriaHolder' class='bespokeCriteriaHolderTable'>";

               $retval .=  $this->build_criteria_form();

            $retval .= "</table>";
        
        $retval .= "</div>";
                        
        return $retval;
    }
    
    /**
     * Load the criteria into a flat array
     */
    public function load_criteria_flat_array($criteria = false, &$array = false)
    {
                        
        if ($criteria && $array)
        {
            $array[$criteria->get_id()] = $criteria;
            $criteria->load_sub_criteria();
            if ($criteria->get_sub_criteria())
            {
                foreach($criteria->get_sub_criteria() as $sub)
                {
                    $this->load_criteria_flat_array($sub, $array);
                }
            }
            return;
        }
        
        
        $return = array();
        
        if ($this->criterias)
        {
            
            foreach($this->criterias as $criteria)
            {
                $return[$criteria->get_id()] = $criteria;
                $criteria->load_sub_criteria();
                if ($criteria->get_sub_criteria())
                {
                    foreach($criteria->get_sub_criteria() as $sub)
                    {
                        $this->load_criteria_flat_array($sub, $return);
                    }
                }
            }
            
        }
        
        
        return $return;
        
    }
    
    protected function build_criteria_form()
    {
        
        global $CFG, $OUTPUT;
        
        $strucs = BespokeCriteria::get_crit_grading_structures();
        
        $retval = "";
                
            $retval .= "<tr>";
                $retval .= "<th>".get_string('name', 'block_bcgt')."</th>";
                $retval .= "<th>".get_string('details', 'block_bcgt')."</th>";
                $retval .= "<th>".get_string('weighting', 'block_bcgt')."</th>";
                $retval .= "<th>".get_string('parent', 'block_bcgt')."</th>";
                $retval .= "<th>".get_string('grading', 'block_bcgt')."</th>";
                $retval .= "<th></th>";
            $retval .= "</tr>";
            
            $flatCriteria = $this->load_criteria_flat_array();
                        
            // Criteria
            if($flatCriteria)
            {
            
                
//                usort($flatCriteria, function($a, $b){
//                    return ($a->get_order() > $b->get_order());
//                });
                
                $i = 0; // Number of criteria displayed
                foreach($flatCriteria as $criterion)
                {
                    
                    $i++;
                    
                    $chk = array();
                    $chk['PMD'] = ($criterion->get_grading() == 'PMD') ? 'checked' : '';
                    $chk['PCD'] = ($criterion->get_grading() == 'PCD') ? 'checked' : '';
                    $chk['P'] = ($criterion->get_grading() == 'P') ? 'checked' : '';
                    $chk['DATE'] = ($criterion->get_grading() == 'DATE') ? 'checked' : '';
                    
                    $retval .= "<tr id='criterionRow_{$i}'>";
                    
                        $retval .= "<td><input type='hidden' name='criterionIDs[{$i}]' value='{$criterion->get_id()}' /><input type='text' placeholder='Name' name='criterionNames[{$i}]' value='{$criterion->get_name()}' class='critNameInput' id='critName_{$i}' /></td>";
                        $retval .= "<td><textarea placeholder='Criteria Details' name='criterionDetails[{$i}]' id='criterionDetails{$i}' class='critDetailsTextArea'>".$criterion->get_details()."</textarea></td>";
                        $retval .= "<td><input title='Weighting' type='text' class='w40' name='criterionWeights[{$i}]' value='{$criterion->get_weighting()}' /></td>";
                        //$retval .= "<td class='align-l'><input type='radio' name='criterionGradings[{$i}]' value='PMD' {$chk['PMD']} /> Pass, Merit, Distinction<br><input type='radio' name='criterionGradings[{$i}]' value='PCD' {$chk['PCD']} /> Pass, Credit, Distinction<br><input type='radio' name='criterionGradings[{$i}]' value='P' {$chk['P']} /> Pass Only<br><input type='radio' name='criterionGradings[{$i}]' value='DATE' {$chk['DATE']} /> Date</td>";
                        $retval .= "<td><select name='criterionParents[{$i}]'><option value=''></option>";
                            foreach($flatCriteria as $c)
                            {
                                if ($c->get_id() == $criterion->get_id()) continue;
                                $sel = ($criterion->get_parent_ID() == $c->get_id()) ? 'selected' : '';
                                $retval .= "<option value='{$c->get_name()}' {$sel}>{$c->get_name()}</option>";
                            }
                        $retval .= "</select></td>";
                        
                        $retval .= '<td><select class="criterionGrading" name="criterionGradingStructure['.$i.']">';
                        $retval .= '<option value="">Criteria Grading Structure</option>';
                        if ($strucs)
                        {
                            foreach($strucs as $structure)
                            {

                                $sel = ($criterion->get_grading() == $structure->id) ? 'selected' : '';
                                $retval .= "<option value='{$structure->id}' {$sel}>{$structure->name} &nbsp;&nbsp; ";
                                
                                foreach($structure->values as $val)
                                {
                                    $retval .= "{$val->grade} [{$val->points}] ({$val->rangelower} - {$val->rangeupper} ), ";
                                }
                                
                                $retval .= "</option>";

                            }
                        }
                        $retval .= '</select> &nbsp;&nbsp; <small><a href="#" title="Copy to all Criteria" class="copyGradingCriteria"><img src="'.$CFG->wwwroot.'/blocks/bcgt/images/copy.png" /></a></small></td>';
                        
                        $retval .= "<td><a href='#' onclick='removeCriterionTable({$i});return false;'><img src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/redX.png' /></a><script>numOfCriterion++;dynamicNumOfCriterion++;</script></td>";
                        
                    $retval .= "</tr>";
                                        
                }
                
            }
                
        return $retval;
        
    }
    
    
    
    
    
    /**
	 * Used in edit unit
	 * Gets the submitted data from the edit form fields
	 * edit_unit_form.php
	 */
	public function get_submitted_edit_form_data()
    {
        $this->credits = $_POST['credits'];       
    }
    
    public function findCriteriaByNameFromArray($criterias, $name){
        
        if ($criterias){
            
            foreach($criterias as &$criteria){

                if ($criteria->get_name() == $name){
                    // This is our fella
                    return $criteria;
                }
                
                // Has sub criteria?
                if ($criteria->get_sub_criteria()){
                    $find = $this->findCriteriaByNameFromArray($criteria->get_sub_criteria(), $name);
                    if ($find){
                        return $find;
                    }
                }
                
            }
            
        }
        
        
        return false;
        
    }
    
    protected function remove_criteria_not_submitted(&$criterias, $criteriaIDs){
        
        if ($criterias)
        {
            
            foreach($criterias as $criteria)
            {
                
                if (!in_array($criteria->get_id(), $criteriaIDs))
                {
                    unset($criterias[$criteria->get_id()]);
                }
                else
                {
                    // Sub?
                    
                    if ($criteria->get_sub_criteria())
                    {
                        $sub = $criteria->get_sub_criteria();
                        $this->remove_criteria_not_submitted($sub, $criteriaIDs);
                        $criteria->set_sub_criteria($sub);
                    }
                    
                }
                
            }
            
        }
        
    }
    
     /**
	 * Used in edit unit
	 * Gets the submitted data from the criteria section of the edit form form.
	 * edit_unit_form.php
	 */
	public function get_submitted_criteria_edit_form_data()
    {
        
      
        if(isset($_POST['criterionNames']))
        {
                        
                       
            $criteriaNames = $_POST['criterionNames'];
            $criteriaIDs = $_POST['criterionIDs'];
            $criteriaDetails = $_POST['criterionDetails'];
            $criteriaGradings = $_POST['criterionGradingStructure'];
            $criteriaWeights = $_POST['criterionWeights'];
            $criteriaParents = $_POST['criterionParents'];
            
            if(empty($criteriaNames) && empty($criteriaDetails)){
                return false;
            }

            $criterias = $this->load_criteria_flat_array();
                        
            // Loop through submitted criteria and create objects for them
            foreach($criteriaIDs as $DID => $ID)
            {
                                
                // If the name is empty, skip it
                if (empty($criteriaNames[$DID])) continue;
                                
                // If there is no element in the array with that ID as a key
                if(!isset($criterias[$ID]))
                {
                    $params = new stdClass();
                    $params->name = $criteriaNames[$DID];
                    $params->details = $criteriaDetails[$DID];
                    $params->grading = $criteriaGradings[$DID];
                    $params->weighting = $criteriaWeights[$DID];
                    $obj = new BespokeCriteria(-1, $params, Qualification::LOADLEVELCRITERIA);
                                        
                    // Does this have a parent ID? And also make sure we're not assigned it to itself
                    if (isset($criteriaParents[$DID]) && !empty($criteriaParents[$DID]) && $criteriaParents[$DID] != $criteriaNames[$DID]){
                                                                        
                        // Find the parent off the array of criteria
                        $parent = $this->findCriteriaByNameFromArray($criterias, $criteriaParents[$DID]);
                        if ($parent){
                            $parent->add_sub_criteria($obj);
                        }
                        
                    }
                                        
                }
                
                // It is already there, so update the object with new values
                else
                {
                    $obj = $criterias[$ID];
                    $obj->set_name($criteriaNames[$DID]);
                    $obj->set_details($criteriaDetails[$DID]);
                    $obj->set_grading($criteriaGradings[$DID]);
                    $obj->set_weighting($criteriaWeights[$DID]);
                    
                    // Clear parent on this criterion
                    $obj->set_parent_criteria_ID(null);
                    // Clear sub criteria on this
                    $obj->set_sub_criteria(array());
                                        
                    // Parent has been sent?
                    if (isset($criteriaParents[$DID]) && !empty($criteriaParents[$DID]) && $criteriaParents[$DID] != $criteriaNames[$DID]){
                        
                                               
                        // Find the parent off the array of criteria
                        $parent = $this->findCriteriaByNameFromArray($criterias, $criteriaParents[$DID]);
                        if ($parent){
                            $parent->add_sub_criteria($obj);
                            
                            // Remove this from the flat array
                            if (isset($criterias[$obj->get_id()])){
                                unset($criterias[$obj->get_id()]);
                            }
                            
                        }
                        
                        
                    }
                                        
                }
                                                
      
                if (!isset($criteriaParents[$DID]) || empty($criteriaParents[$DID])){
                    if(!isset($criterias[$ID]))
                    {
                        $criterias[] = $obj;
                    }
                    else
                    {
                        $criterias[$ID] = $obj;
                    }           
                }
                                
            }
                                   
        
        }
        
        // Remove any rfmo the array if we didn't submit it
        $this->remove_criteria_not_submitted($criterias, $criteriaIDs);
        $this->criterias = $criterias;
       
    }
    
    
    
    
    /**
	 * Inserts the unit AND the criteria and all related details
	 * Dont forget to set the id of the unit object
	 */
	public function insert_unit()
    {
        
        global $DB;
                
        // Insert unit
		$stdObj = new stdClass();
		$stdObj->name = $this->name;
		$stdObj->details = $this->details;
		$stdObj->credits = $this->credits;
		$stdObj->uniqueid = $this->uniqueID;
		$stdObj->bcgttypeid = 1;
		$stdObj->bcgtunittypeid = $this->unitTypeID;
		$stdObj->bcgtlevelid = 0;
        $stdObj->weighting = $this->weighting;
		$this->id = $DB->insert_record('block_bcgt_unit', $stdObj);
        
        // Insert criteria
		foreach($this->criterias AS $criteria)
		{
			$criteria->insert_criteria($this->id);
		}
		
        // Insert bespoke unit
        $obj = new stdClass();
        $obj->bcgtunitid = $this->id;
        $obj->displaytype = $this->displaytype;
        $obj->level = $this->level;
        $obj->gradingstructureid = $this->grading;
        $DB->insert_record("block_bcgt_bespoke_unit", $obj);

        return true;
        
    }
    
     /**
	 *Loops over the original criteria this unit had from the database
	 * if it has been removed from the unit it creates a history
	 * and then deletes the criteria from the database 
	 */
	protected function check_criteria_removed()
	{
        global $DB;
		//needs to find all of the criteria
		//that were on this unit that are not anymore(if any)
		$originalCriteria = $this->retrieve_criteria_flat($this->id);      
                
		if($originalCriteria)
		{
			foreach($originalCriteria AS $origCriteria)
			{
				if(!$this->exists_in_criteria_or_sub_criteria($this->criterias, $origCriteria->id))
				{
					//then do a history
					if($this->insert_criteria_history($origCriteria->id))
					{
						//delete the record. 
						$DB->delete_records('block_bcgt_criteria', array('id'=>$origCriteria->id));
                        // Log
                        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_CRITERIA, LOG_VALUE_GRADETRACKER_DELETED_CRIT, null, null, $this->id, null, $this->id, $origCriteria->id);
					}	
				}
			}
		}
	}
	
	/**
	 * Updates the unit AND the criteria and all related details
	 */
	public function update_unit($updateCriteria = true)
    {
        global $DB;
        
        // Insert unit
		$stdObj = new stdClass();
        $stdObj->id = $this->id;
		$stdObj->name = $this->name;
		$stdObj->details = $this->details;
		$stdObj->credits = $this->credits;
		$stdObj->uniqueid = $this->uniqueID;
		$stdObj->bcgtlevelid = 0;
        $stdObj->weighting = $this->weighting;
		$DB->update_record('block_bcgt_unit', $stdObj);
        
        if ($updateCriteria)
        {
            
            $this->check_criteria_removed();
            
            if ($this->criterias)
            {
            
                foreach($this->criterias as $criteria)
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
        
        $bespoke = $DB->get_record("block_bcgt_bespoke_unit", array("bcgtunitid" => $this->id));
        if (!$bespoke)
        {
            
            $obj = new stdClass();
            $obj->bcgtunitid = $this->id;
            $obj->displaytype = $this->displaytype;
            $obj->level = $this->level;
            $obj->gradingstructureid = $this->grading;
            $DB->insert_record("block_bcgt_bespoke_unit", $obj);
            $bespoke = $DB->get_record("block_bcgt_bespoke_unit", array("bcgtunitid" => $this->id));
            
        }
        
        
        // update bespoke unit
        $bespoke->displaytype = $this->displaytype;
        $bespoke->level = $this->level;
        $bespoke->gradingstructureid = $this->grading;
        $DB->update_record("block_bcgt_bespoke_unit", $bespoke);
        
        
    }
    
    
   /**
     * Update a student's unit award
     * @global type $CFG
     * @param type $qualID
     * @return type 
     */
	protected function update_unit_award($qualID)
	{
		global $CFG, $DB, $USER;
		$userUnit = $DB->get_record("block_bcgt_user_unit", array("userid" => $this->studentID, "bcgtqualificationid" => $qualID, "bcgtunitid" => $this->id));
		if($userUnit)
		{
			$id = $userUnit->id;
			$obj = new stdClass();
			$obj->id = $id;
			if($this->userAward)
			{
				$obj->bcgttypeawardid = $this->userAward->get_id();
                if($this->userAward->get_rank() > 0){
                    $obj->dateupdated = time();
                }
			}
			else
			{
				$obj->bcgttypeawardid = 0;
			}
                        
            // Only log if new award differs from current
            if($userUnit->bcgttypeawardid <> $obj->bcgttypeawardid && $obj->bcgttypeawardid > 0)
            {
                logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_UNIT, LOG_VALUE_GRADETRACKER_UPDATED_UNIT_AWARD, $this->studentID, $qualID, $this->id, null, $this->id, $obj->bcgttypeawardid);
            }
                        
			return $DB->update_record("block_bcgt_user_unit", $obj);
		}
		return false;
	}
    
    /**
	 * Certain qualificaton types have unit awards
	 */
	public function unit_has_award()
    {
        return true;
    }
    
    /**
     *  Calculate the average score of a unit, based on criteria awards, their points and point boundaries
     * @param type $awardArray
     * @param type $totalCriteria
     * @return int|boolean
     */
    protected function calculate_average_score($awardArray)
    {
                
        global $DB;             
                                
        // Get the points rankings of the different met awards, e.g. P, M, D
        $awards = $this->get_possible_awards();
                
        $records = array();
        
        if ($awards)
        {
            foreach($awards as $award)
            {
                if ($award->points < 1) continue;
                $obj = new stdClass();
                $obj->points = $award->points;
                $obj->rangelower = $award->rangelower;
                $obj->rangeupper = $award->rangeupper;
                $records[$award->shortgrade] = $obj;
            }
        }
                
        $totalCriteria = 0;
        $totalScore = 0;
                
        foreach($awardArray as $award)
        {
            // Get the points ranking of this award and add to total
            // Also take into account different criteria weightings
            $weight = $award['weighting'];
            $rank = $award['points'];
            $totalCriteria += $weight;
            $totalScore += ( ($rank * $weight) );
        }
        
        if ($totalCriteria > 0){
            
            $avgScore = round($totalScore / $totalCriteria, 1);

            $this->studentUnitPoints = $avgScore;

            // Now work out where in the points boundaries it lies
            foreach($records as $record)
            {
                if($avgScore >= $record->rangelower && $avgScore <= $record->rangeupper)
                {
                    return $record->points;
                }
            }
        
        }
        
        return -1; // Something went quite wrong
        
    }    
    
    /**
     * Get a unit award by its ranking
     * @global type $CFG
     * @param type $ranking
     * @return type 
     */
    protected function get_unit_award_by_points($ranking)
    {
        global $DB;
        return $DB->get_record("block_bcgt_bspk_u_grade_vals", array("unitgradingid" => $this->grading, "points" => $ranking));
    }
    
    
    /**
	 * Certain qualification types have unit awards.
	 */
	public function calculate_unit_award($qualID, $update = true, $subCriteria = false, &$totalPassed = 0, &$totalCriteria = 0, &$awardArray = false)
    {
        
       global $DB;
       
       if ($subCriteria)
       {
           
           foreach($subCriteria as $sub)
           {
               
                $totalCriteria++;
                
                // Get the student's award value for this criteria
                $valueObj = $sub->get_student_value();
                if($valueObj)
                {
                    if($valueObj->is_met()){
                        
                        // If weighting is 0, don't bother adding
                        if ($sub->get_weighting() > 0){                        
                            $totalPassed++;
                            $ranking = $DB->get_record("block_bcgt_bspk_c_grade_vals", array("id" => $valueObj->get_id()), "id, points");
                            $awardArray[] = array("criteria" => $sub->get_name(), "value" => $valueObj->get_short_value(), "weighting" => $sub->get_weighting(), "points" => $ranking->points);
                        } else {
                            $totalCriteria--;
                        }
                    }
                }
               
                if ($sub->get_sub_criteria())
                {
                    $this->calculate_unit_award($qualID, $update, $sub->get_sub_criteria(), $totalPassed, $totalCriteria, $awardArray);
                }
                
           }
           
           return;
           
       }
       
       
       
       
       if (!$this->criterias) return;
       
        // Just need to work out the avg score of the unit, then consult the boundary scores in tracking_type_award
        
        $totalPassed = 0;
        $totalCriteria = count($this->criterias);
        
        $awardArray = array();
        
        // Loop criteria on unit
        if($this->criterias)
        {
            
            foreach($this->criterias as $criteria)
            {
            
                // Get the student's award value for this criteria
                $valueObj = $criteria->get_student_value();
                if($valueObj)
                {
                    if($valueObj->is_met()){
                        
                        // If weighting is 0, don't bother adding
                        if ($criteria->get_weighting() > 0){                        
                            $totalPassed++;
                            $ranking = $DB->get_record("block_bcgt_bspk_c_grade_vals", array("id" => $valueObj->get_id()), "id, points");
                            $awardArray[] = array("criteria" => $criteria->get_name(), "value" => $valueObj->get_short_value(), "weighting" => $criteria->get_weighting(), "points" => $ranking->points);
                        } else {
                            $totalCriteria--;
                        }
                    }
                }
                
                if ($criteria->get_sub_criteria())
                {
                    $this->calculate_unit_award($qualID, $update, $criteria->get_sub_criteria(), $totalPassed, $totalCriteria, $awardArray);
                }
                
            }
            
        }
                        
        // Finished looping now, so let's work out if we need to award anything and if so, what
        if($totalPassed == $totalCriteria && $totalCriteria > 0)
        {
            
            // We're passed all the criteria, so let's work out the unit award
            $awardRanking = $this->calculate_average_score($awardArray);
                        
            // Update the unit award
            $awardRecord = $this->get_unit_award_by_points($awardRanking);

            if ($awardRecord)
            {
                $params = new stdClass();
                $params->award = $awardRecord->grade;
                $params->rank = $awardRecord->points;
                $award = new BespokeAward($awardRecord->id, $params);
                            
                // Only update if we don't have an award or if it's different
                if ($update && (!$this->userAward || ($this->userAward && $this->userAward->get_id() <> $award->get_id())) ){
                    $this->userAward = $award;
                    $this->update_unit_award($qualID);
                }
                
                return $award;
            
            }
        
        }
        
        
        // If we get to this point, either the amount passed wasn't equal, or there was a problem with the award
        // So set it back to N/S
        if ($update){
            $this->userAward = new BespokeAward(-1, 'N/S', 0);
            $this->update_unit_award($qualID);
        }
        
        return $this->userAward;
                
    }
    
    
    /**
     * displays the unit grid. 
     */
    public function display_unit_grid()
    {
        
        global $CFG, $PAGE, $OUTPUT, $COURSE, $DB;
        
        require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/lib.php';
        require_bespoke();
        
        $output = "";
        
        $grid = optional_param('g', 'v', PARAM_TEXT);
        $courseID = optional_param('cID', -1, PARAM_INT);
        $qualID = optional_param('qID', -1, PARAM_INT);
        
        //if the qualid is null, can we go and get it?
        if($qualID == -1)
        {
            $quals = bcgt_get_unit_quals($this->id, $courseID);
            if($quals && count($quals) == 1)
            {
                $qual = end($quals);
                $qualID = $qual->id;
            }
        }
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        if (is_null($qualification) || !$qualification) return false;        
        
        if ($grid == 's') $grid = 'v';
        
        $errors = $qualification->check_grading_structures_compatability();
        if ($errors)
        {
            
            $output .= "<div class='errorsDiv'>";
            $output .= "<h2 class='c'>".get_string('compatabilityerrors', 'block_bcgt')."</h2><br>";
            
                foreach($errors as $error)
                {
                    $output .= $error . "<br><br>";
                }
            
            $output .= "</div>";
            
            return $output;
            
        }
        
        
        
        if($courseID > 1)
        {
            $context = context_course::instance($courseID);
        }
        else
        {
            $context = context_course::instance($COURSE->id);
        }
        
        // Only allow editing if we have the capability
        if ($grid == 'e' && !has_capability('block/bcgt:editunitgrid', $context)){
            $grid = 'v';
        }
        
        $editing = ($grid == 'e');
                
        $output .= load_javascript(true, true);
        $output .= load_css(true, true);
        
        $criteriaNames = $this->get_used_criteria_names();
        
        $jsModule = array(
            'name'     => 'mod_bcgtbespoke',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbespoke/js/bcgtbespoke.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        
        $freezeCols = 5;
        if ($qualification->has_unit_percentages()){
            $freezeCols++;
        }
                
        $PAGE->requires->js_init_call('M.mod_bcgtbespoke.initunitgrid', array($qualID, $this->id, $grid, $freezeCols), true, $jsModule);
        
        $output .= "<div class='c'>";
        
        $output .= "<a href='".$CFG->wwwroot."/blocks/bcgt/grids/unit_grid.php?uID={$this->id}&qID={$qualID}&g=v'><input type='button' class='btn' value='".get_string('view', 'block_bcgt')."' /></a>";
        $output .= "&nbsp;&nbsp;&nbsp;&nbsp;";
        $output .= "<a href='".$CFG->wwwroot."/blocks/bcgt/grids/unit_grid.php?uID={$this->id}&qID={$qualID}&g=e'><input type='button' class='btn' value='".get_string('edit', 'block_bcgt')."' /></a>";

        if ($editing)
        {
            $output .= "<br><br>";
            $output .= "<a href='#' onclick='toggleAddComments();return false;'><input id='toggleCommentsButton' type='button' class='btn' value='".get_string('addcomment', 'block_bcgt')."' /></a>";
        }
        
        // Pages
        $page = optional_param('page', 1, PARAM_INT);
        $pageRecords = get_config('bcgt','pagingnumber');
        
        if ($page < 1) {
            $page = 1;
        }
                
        $studentsArray = get_users_on_unit_qual($this->id, $qualID);
                
        // If we are using pages, work out what the limits should be on the sql
        if ($pageRecords != 0)
        {
            
            $cntStudents = count($studentsArray);
            $noPages = ceil($cntStudents / $pageRecords);
            
            if ($page == 1){
                $start = 0;
                $end = $pageRecords - 1;
            } else {
                $start = $pageRecords * ( $page - 1 );
                $end = ( $pageRecords * $page ) - 1;
            }
            
            $oldStudentsArray = array();
            
            $j = 0;
            
            foreach($studentsArray as $student)
            {
                $oldStudentsArray[$j] = $student;
                $j++;
            }
                        
            $newStudentsArray = array();
            
            for($i = 0; $i < $cntStudents; $i++)
            {
                
                if ($i >= $start && $i <= $end)
                {
                    $stud = $oldStudentsArray[$i];
                    $newStudentsArray[$stud->id] = $stud;
                }
                
            }
            
            // Set new array as the one we want to use
            $studentsArray = $newStudentsArray;
                        
            $output .= '<div class="bcgt_pagination">'.get_string('pagenumber', 'block_bcgt').' : ';

                for ($i = 1; $i <= $noPages; $i++)
                {
                    $class = ($i == $page) ? 'active' : '';
                    $output .= "<a class='unitgridpage pageNumber {$class}' page='{$i}' href='{$CFG->wwwroot}/blocks/bcgt/grids/unit_grid.php?uID={$this->id}&qID={$qualID}&g={$grid}&cID={$courseID}&page={$i}'>{$i}</a>";
                }

            $output .= '</div>';
            
            
        }
            
        $output .= "</div>";
        
        if ($grid == 'v')
        {
            
            $output .= $this->get_grid_key();
        
        }
        
        $output .= "<br><br>";
        $output .= "<p id='loading' class='c'><img src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/loader.gif' alt='loading...' /></p>";
        
        
         $output .= "<div id='bespokeUnitGrid'>";
        
            $output .= "<table id='bespokeUnitGridTable'>";
                $output .= "<thead>";
                $output .= "<tr>";
                    $output .= "<th></th>";
                    $output .= "<th></th>";
                    $output .= "<th>".get_string('user')."</th>";
                    $output .= "<th>".get_string('qualaward', 'block_bcgt')."</th>";
                    $output .= "<th>".get_string('unitaward', 'block_bcgt')."</th>";

                    $colCount = 5;
                    
                    if ($qualification->has_unit_percentages())
                    {
                        $output .= "<th>".get_string('percentcomplete', 'block_bcgt')."</th>";
                        $colCount++;
                    }
                    
                    
                    if ($criteriaNames)
                    {
                        foreach($criteriaNames as $name)
                        {
                            $output .= "<th>{$name}</th>";
                            $colCount++;
                        }
                    }
                    
                $output .= "</tr>";
                $output .= "</thead>";
                
                $output .= "<tbody>";
                                        
                    if ($studentsArray)
                    {
                        foreach($studentsArray as $student)
                        {
                            
                            $output .= "<tr>";
                            
                            $qualification->load_student_information($student->id, $loadParams);
                            $this->load_student_information($student->id, $qualID, $loadParams);
                            
                            $class = (!empty($this->comments)) ? 'hasComments' : '';
                                                        
                            // Student grid link
                            $output .= "<td class='{$class}'>";
                            
                            $output .= "<div class='criteriaTDContent'>";
                            
                                if(has_capability('block/bcgt:editunit', $context)){
                                    $output .= "<a href='{$CFG->wwwroot}/blocks/bcgt/grids/student_grid.php?sID={$student->id}&qID={$qualID}' target='_blank' title='View Student Grid'><img src='".$OUTPUT->pix_url('i/calendar', 'core')."' /></a><br>";
                                }
                                
                            $output .= "</div>";
                            
                            $output .= "<div class='hiddenCriteriaCommentButton'>";
                                    
                                        $username = $student->username;
                                        $fullname = fullname($student);
                                        $unitname = bcgt_html($this->name);
                                        $critname = "N/A";
                                        $cellID = "cmtCell_U_{$this->id}_S_{$student->id}_Q_{$qualID}";

                                        if (!empty($this->comments))
                                        {
                                            $output .= "<img id='{$cellID}' criteriaid='-1' unitid='{$this->id}' studentid='{$student->id}' qualid='{$qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='unit' class='editCommentsUnit' title='Click to Edit Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/comment_edit.png' alt='".get_string('editcomments', 'block_bcgt')."' />";
                                        }
                                        else
                                        {
                                            $output .= "<img id='{$cellID}' criteriaid='-1' unitid='{$this->id}' studentid='{$student->id}' qualid='{$qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='unit' class='addCommentsUnit' title='Click to Add Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/comment_add.png' alt='".get_string('addcomment', 'block_bcgt')."' />";
                                        }

                                        //$output .= "<span class='tooltipContent' style='display:none !important;'>".bcgt_html($this->comments, true)."</span>";
                                        $output .= "<div class='popUpDiv bcgt_unit_comments_dialog' id='dialog_S{$student->id}_U{$this->id}_Q{$qualID}' qualID='{$qualID}' unitID='{$this->id}' critID='-1' studentID='{$student->id}' grid='unit' imgID='{$cellID}' title='Comments'>";
                                            $output .= "<span class='commentUserSpan'>Comments for {$fullname} : {$username}</span><br>";
                                            $output .= "<span class='commentUnitSpan'>{$this->get_display_name()}</span><br>";
                                            $output .= "<span class='commentCriteriaSpan'>N/A</span><br><br><br>";
                                            $output .= "<textarea class='dialogCommentText' id='text_S{$student->id}_U{$this->id}_Q{$qualID}'>".bcgt_html($this->comments)."</textarea>";
                                        $output .= "</div>";

                                    
                            $output .= "</div>";
                            
                                
                                
                            $output .= "</td>";
                            
                            // Student pic & name
                            $output .= "<td class='{$class}'>".$OUTPUT->user_picture($student, array('size' => 25))."</td>";
                            
                            $output .= "<td class='studentUnit {$class}' title=''>".fullname($student)." ({$student->username})<div class='unitDetailsTooltip'>{$this->build_tooltip_content($qualification)}</div></td>";
                            
                            
                            
                            
                            // Qual award
                            $qualAward = $qualification->get_student_award();
                            $award = '-';
                            $awardID = -1;
                            if ($qualAward)
                            {
                                $award = $qualAward->get_award();
                                $awardID = $qualAward->get_id();
                            }
                            
                            $output .= "<td><span class='finalAward_S{$student->id}_Q{$qualID}'>";
                            
//                            if ($grid == 'e' && !$qualification->use_auto_calculations())
//                            {
//                                $awards = $DB->get_records("block_bcgt_bspk_q_grade_vals", array("qualgradingid" => $qualification->get_grading()), "rangelower ASC");
//                                $output .= "<select class='qualAwardSelect' qualID='{$this->id}' studentID='{$this->studentID}'>";
//                                    $output .= "<option value='-1'></option>";
//                                    if ($awards)
//                                    {
//                                        foreach($awards as $award)
//                                        {
//                                            $chk = ($awardID == $award->id) ? 'selected' : '';
//                                            $output .= "<option value='{$award->id}' {$chk} >{$award->grade}</option>";
//                                        }
//                                    }
//                                $output .= "</select>";
//                            }
//                            else
//                            {
//                                $output .= $award;
//                            }
                            
                            $output .= $award;
                            
                            $output .= "</span></td>";
                            
                            
                            
                            
                            
                            // Unit award
                            $unitAward = $this->get_user_award();
                            $award = '-';
                            if ($unitAward)
                            {
                                
                                if ($grid == 'e')
                                {
                                    $award = "<select id='unitAwardSelect_U{$this->id}_Q{$qualID}_S{$student->id}' class='unitAwardSelect' grid='unit' qualID='{$qualID}' unitID='{$this->id}' studentID='{$student->id}'>";
                                        $award .= "<option value=''></option>";
                                        foreach($this->get_possible_awards() as $possibleAward)
                                        {
                                            $sel = ($unitAward && $unitAward->get_id() == $possibleAward->id) ? 'selected' : '';
                                            $award .= "<option value='{$possibleAward->id}' {$sel}>{$possibleAward->shortgrade} - {$possibleAward->grade}</option>";
                                        }
                                    $award .= "</select>";
                                }
                                else
                                {
                                    $award = $unitAward->get_award();
                                }
                                
                            }
                            
                            
                            $output .= "<td id='unitAward_U{$this->id}_Q{$qualID}_S{$student->id}'>{$award}</td>";
                            
                                                       
                            
                            
                            // % complete
                            if ($qualification->has_unit_percentages())
                            {
                                $output .= "<td id='percentComplete_U{$this->id}_Q{$qualID}_S{$student->id}'>".$this->display_percentage_completed()."</td>";
                            }
                            
                            
                            // Criteria
                            if ($criteriaNames)
                            {
                                foreach($criteriaNames as $name)
                                {
                                    $studentCriteria = $this->get_single_criteria(-1, $name);
                                    if ($studentCriteria)
                                    {
                                        $output .= $studentCriteria->get_td('unit', $editing, $this->student, $qualification, $this);
                                    }
                                    else
                                    {
                                        $output .= "<td></td>";
                                    }
                                        
                                }
                            }
                            
                            $output .= "</tr>";
                            
                        }
                    }
                    else
                    {
                        $output .= "<tr><td colspan='{$colCount}'>".get_string('nostudentsfound', 'block_bcgt')."</td></tr>";
                    }
                    
                $output .= "</tbody>";
                
                $output .= "<tfoot></tfoot>";
                
            $output .= "</table>";
        
        $output .= "</div>";
        
        
        
        return $output;
        
    }
    
    
    public function get_used_criteria_names()
    {
        
        global $CFG;
        
        require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeCriteriaSorter.class.php';
        $criteriaSorter = new BespokeCriteriaSorter(); 
                        
        $criteriaArray = array();
        
        if ($this->criterias)
        {
            foreach($this->criterias as $crit)
            {
                $criteriaArray[$crit->get_name()] = $this->get_recursive_sub_criteria_names( $crit->get_name() );
            }
        }
   
                
        uksort($criteriaArray, array($criteriaSorter, "ComparisonSimple"));
                
        $this->usedCriteriaNames = bcgt_flatten_by_keys($criteriaArray);
        return $this->usedCriteriaNames;
        
    }
    
    protected function get_recursive_sub_criteria_names($critName, $level = 1)
    {
        
        global $CFG;
        
        require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeCriteriaSorter.class.php';
        $criteriaSorter = new BespokeCriteriaSorter(); 
        
        
        $array = array();
        
        if ($this->criterias)
        {

            foreach($this->criterias as $criterion)
            {

                if ($criterion->get_name() == $critName && $level == 1)
                {

                    if ($criterion->get_sub_criteria())
                    {

                        foreach($criterion->get_sub_criteria() as $subCriterion)
                        {

                            $array[$subCriterion->get_name()] = $this->get_recursive_sub_criteria_names( $subCriterion->get_name(), 2 );

                        }

                    }

                }
                else
                {

                    if ($criterion->get_sub_criteria())
                    {

                        foreach($criterion->get_sub_criteria() as $subCriterion)
                        {

                            if ($subCriterion->get_name() == $critName && $level == 2)
                            {

                                if ($subCriterion->get_sub_criteria())
                                {

                                    foreach($subCriterion->get_sub_criteria() as $subSubCriterion)
                                    {

                                        $array[$subSubCriterion->get_name()] = true; # Max level we support is 2, until I can work out a good way of doing this recursively

                                    }

                                }

                            }

                        }

                    }

                }

            }

        }

        
        uksort($array, array($criteriaSorter, "ComparisonSimple"));

        return ($array) ? $array : true;
        
    }
    
    
    protected function get_possible_grid_values($criteria = false, &$array = false)
    {
        
        global $DB;
        
        if ($criteria && $array)
        {
            
            foreach($criteria as $criterion)
            {
                
                $array[] = $criterion->get_grading();
                        
                if ($criterion->get_sub_criteria())
                {
                    $this->get_possible_grid_values($criterion->get_sub_criteria(), $array);
                }
                
            }
            
            return true;
            
        }
        
        
        $array = array();
        
        if ($this->criterias)
        {

            foreach($this->criterias as $criterion)
            {

                $array[] = $criterion->get_grading();

                if ($criterion->get_sub_criteria())
                {
                    $this->get_possible_grid_values($criterion->get_sub_criteria(), $array);
                }

            }

        }
        
        $gradingIDs = array_unique($array);
        $gradingIDs = implode(",", $gradingIDs);
        
        return $DB->get_records_select("block_bcgt_bspk_c_grade_vals", "critgradingid IN ({$gradingIDs}) OR critgradingid IS NULL", null, "met DESC, rangelower ASC, grade ASC");
        
        
    }
    
    
    public function get_grid_key()
    {
        
        global $CFG;
        
        $output = "";
        
        $possibleGridValues = $this->get_possible_grid_values();
        $width = 100 / (count($possibleGridValues) + 1);

        $output .= "<div id='bespokeGridKey'>";

            $output .= "<table>";

                $output .= "<tr>";
                    $output .= "<th colspan='".(count($possibleGridValues) + 1)."'>".get_string('gridkey', 'block_bcgt')."</th>";
                $output .= "</tr>";

                $output .= "<tr class='imgs'>";

                if ($possibleGridValues)
                {
                    foreach($possibleGridValues as $possible)
                    {
                        $image = BespokeQualification::get_grid_image($possible->shortgrade, $possible->grade, $possible);
                        if ($image)
                        {
                            $output .= "<td style='width:{$width}%;'><img src='{$image->image}' alt='{$image->title}' class='{$image->class}' /></td>";
                        }
                    }
                    $output .= "<td style='width:{$width}%;'><img src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/grid_symbols/icon_NoIcon.png' alt='".get_string('missingiconimg', 'block_bcgt')."' /></td>";
                }

                $output .= "</tr>";


                $output .= "<tr class='names'>";

                if ($possibleGridValues)
                {
                    foreach($possibleGridValues as $possible)
                    {
                        $output .= "<td style='width:{$width}%;'>{$possible->grade}</td>";
                    }
                    $output .= "<td style='width:{$width}%;'>".get_string('missingiconimg', 'block_bcgt')."</td>";
                }

                $output .= "</tr>";


            $output .= "</table>";

        $output .= "</div>";

        return $output;
        
    }
    
    
    
    
    
    
    public function process_create_update_unit_form(){
        
        $displaytype = optional_param('displaytype', NULL, PARAM_TEXT);
        $gradingstructureid = optional_param('gradingstructure', NULL, PARAM_INT);
        $extcode = optional_param('unique', NULL, PARAM_TEXT);
        $name = optional_param('name', NULL, PARAM_TEXT);
        $weighting = optional_param('weighting', NULL, PARAM_NUMBER);
        $level = optional_param('level', NULL, PARAM_INT);
        
        $displaytype = trim($displaytype);
        $extcode = trim($extcode);
        $name = trim($name);
        
        $this->processed_errors = '';
        
        // Display type 
        if (is_null($displaytype) || empty($displaytype)){
            $this->processed_errors .= get_string('error:displaytype', 'block_bcgt') . '<br>';
        }
        
        // Grading structure
        if (is_null($gradingstructureid) || $gradingstructureid <= 0){
            $this->processed_errors .= get_string('error:gradingstructure', 'block_bcgt') . '<br>';
        }
        
        // External Code
        if (is_null($extcode) || empty($extcode)){
            $this->processed_errors .= get_string('error:uniquecode', 'block_bcgt') . '<br>';
        }
        
        // Name
        if (is_null($name) || empty($name)){
            $this->processed_errors .= get_string('error:name', 'block_bcgt') . '<br>';
        }
        
        if (!empty($this->processed_errors)){
            return false;
        }
        
        $this->displaytype = $displaytype;
        $this->grading = $gradingstructureid;
        $this->uniqueID = $extcode;
        $this->name = $name;
        $this->weighting = $weighting;
        $this->level = $level;
        unset($this->processed_errors);
        return true;
        
        
    }
    
    
    public static function get_edit_form_menu($disabled = '', $unitID = -1)
	{
        $jsModule = array(
            'name'     => 'mod_bcgtbespoke',
            'fullpath' => '/blocks/bcgt/plugins/bcgtbespoke/js/bcgtbespoke.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        global $PAGE;
        $PAGE->requires->js_init_call('M.mod_bcgtbespoke.bespokeiniteditunit', null, true, $jsModule);
        
        $displayType = optional_param('displaytype', '', PARAM_TEXT);
        $level = optional_param('level', '', PARAM_INT);
        $weight = optional_param('weighting', "1.0", PARAM_FLOAT);
        
        
        if ($unitID > 0){
            
            $unit = new BespokeUnit($unitID, null, null);
            if ($unit)
            {
                $displayType = $unit->get_display_type();
                $level = $unit->get_level()->get_level();
                $weight = $unit->get_weighting();
            }
            
        }
        
		$retval = '';
        
        
         // Display Type
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='displaytype'><span class='required'>*</span>".get_string('displaytype', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<input type='text' name='displaytype' value='{$displayType}' title='".get_string('displaytype:desc', 'block_bcgt')."' />";
            $retval .= "</div>";
        $retval .= "</div>";
        
        
        // Level
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='level'>".get_string('level', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<input style='width:40px;' type='number' name='level' value='{$level}' />";
            $retval .= "</div>";
        $retval .= "</div>";
        
        
        // Weight
        
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='level'>".get_string('weighting', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<input style='width:40px;' type='text' name='weighting' value='{$weight}' />";
            $retval .= "</div>";
        $retval .= "</div>";
        
        
        
       
        
        
        
		
		return $retval;
	}
    
    
    
    public static function get_unit_grading_structures()
    {
        
        global $DB;
        
        $results = array();
        
        $records = $DB->get_records("block_bcgt_bspk_unit_grading");
        
        if ($records)
        {
            foreach($records as $record)
            {
                
                $values = $DB->get_records("block_bcgt_bspk_u_grade_vals", array("unitgradingid" => $record->id));
                if ($values)
                {
                    
                    $record->values = $values;
                    $results[$record->id] = $record;
                    
                }
                
            }
        }
                
        return $results;
        
    }
    
    
    public function get_possible_awards()
    {
        global $DB;
        return $DB->get_records("block_bcgt_bspk_u_grade_vals", array("unitgradingid" => $this->grading));
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
        
        return new BespokeUnit($unitID, $params, $loadLevel);
    }
    
    /**
	 * Used to get the credits value from the database for this unit
	 * @param $id
	 */
	protected static function retrieve_credits($unitID)
	{		
		global $DB;
		$sql = "SELECT credits FROM {block_bcgt_unit} WHERE id = ?";
		return $DB->get_record_sql($sql, array($unitID));
	}
    
    
    public function get_default_credits()
    {
        return $this->credits;
    }
    
    
    protected function get_single_criteria_from_arrays($criteriaArray, $criteriaID, $name)
	{
                        
        if ($criteriaArray)
        {
            
            foreach($criteriaArray as $crit)
            {
                
                // If name of criteria is just a number, convert it to a string so === doesn't fail
                $name = (string)$name;               
                
                if ($criteriaID > 0 && $criteriaID == $crit->get_id())
                {
                    return $crit;
                }
                elseif ($name !== '' && $name === $crit->get_name())
                {
                    return $crit;
                }
                
                if (($sub = $this->get_single_criteria_from_arrays($crit->get_sub_criteria(), $criteriaID, $name)) !== false)
                {
                    return $sub;
                }
                
            }
            
        }
        
		return false;
        
	}
    
    public function get_percent_completed()
    {

        $criteria = $this->load_criteria_flat_array();

        if(!$criteria) return 0; # No criteria, so is it 0% complete or 100% complete? 0 will do

        $count = count($criteria);

        // Now we've counted everything, we need to find out how many of these are completed
        $numCompleted = $this->are_criteria_completed_($criteria);
        $percent = round(($numCompleted * 100) / $count);                
        return $percent;
               

    }
    
    protected function are_criteria_completed_($criteria)
    {
        if(!$criteria) return 0;

        $numCompleted = 0;

        foreach($criteria as $criterion)
        {
            
            $sID = $criterion->get_student_ID();
            if (is_null($sID)){
                $criterion->load_student_information($this->studentID, $this->qualID, $this->id);
            }
            
            $award = $criterion->get_student_value();
            if($award)
            {
                if($award->is_met())
                {
                    $numCompleted++;
                }
            }

        }

        return $numCompleted;

    }
    
    
    
    public function print_grid($qualID)
    {
        
        global $CFG, $COURSE, $printGrid, $OUTPUT;
        $printGrid = true;
        $context = context_course::instance($COURSE->id);
        $courseID = optional_param('cID', -1, PARAM_INT);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        if (is_null($qualification) || !$qualification) return false;    
        
        $criteriaNames = $this->get_used_criteria_names();

        echo "<!doctype html><html><head>";
        echo "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/print.css'>";
        echo load_javascript(false, true);
        
        $logo = get_config('bcgt', 'logoimgurl');
        
        echo "</head><body style='background: url(\"{$logo}\") no-repeat;'>";
                
        echo "<div class='c'>";
        
            echo "<h1>{$qualification->get_display_name()}</h1>";
            echo "<h2>{$this->get_display_name()}</h2>";

            echo "<br><br>";
            
            // Key
            echo "<div id='key'>";
                echo $qualification->get_grid_key();
            echo "</div>";
            
            
            
            echo "<br><br>";
            
            echo "<table id='printGridTable'>";
            
            
            echo "<thead>";
            echo "<tr>";
                echo "<th></th>";
                echo "<th></th>";
                echo "<th>".get_string('user')."</th>";
                echo "<th>".get_string('unitaward', 'block_bcgt')."</th>";
                echo "<th>".get_string('qualaward', 'block_bcgt')."</th>";

                $colCount = 5;


                if ($criteriaNames)
                {
                    foreach($criteriaNames as $name)
                    {
                        echo "<th>{$name}</th>";
                        $colCount++;
                    }
                }

            echo "</tr>";
            echo "</thead>";
            
            

            echo "<tbody>";

            $studentsArray = get_users_on_unit_qual($this->id, $qualID);

            if ($studentsArray)
            {
                foreach($studentsArray as $student)
                {

                    echo "<tr>";

                    $qualification->load_student_information($student->id, $loadParams);
                    $this->load_student_information($student->id, $qualID, $loadParams);

                    // Student grid link
                    echo "<td></td>";

                    // Student pic & name
                    echo "<td>".$OUTPUT->user_picture($student, array('size' => 25))."</td>";

                    echo "<td class='studentUnit' title=''>".fullname($student)." ({$student->username})</td>";


                    // Unit award
                    $unitAward = $this->get_user_award();
                    $award = '-';
                    if ($unitAward)
                    {
                        $award = $unitAward->get_award();
                    }


                    echo "<td id='unitAward_U{$this->id}_Q{$qualID}_S{$student->id}'>{$award}</td>";


                    // Qual award
                    $qualAward = $qualification->get_student_award();
                    $award = '-';
                    if ($qualAward)
                    {
                        $award = $qualAward->get_award();
                    }

                    echo "<td><span class='finalAward_S{$student->id}_Q{$qualID}'>{$award}</span></td>";
                    

                    // Criteria
                    if ($criteriaNames)
                    {
                        foreach($criteriaNames as $name)
                        {
                            $studentCriteria = $this->get_single_criteria(-1, $name);
                            if ($studentCriteria)
                            {
                                echo $studentCriteria->get_td('unit', false, $this->student, $qualification, $this);
                            }
                            else
                            {
                                echo "<td></td>";
                            }

                        }
                    }

                    echo "</tr>";

                }
            }
            else
            {
                echo "<tr><td colspan='{$colCount}'>".get_string('nostudentsfound', 'block_bcgt')."</td></tr>";
            }

            echo "</tbody>";
            
            
            
            
            
            echo "</table>";
            echo "</div>";
            
            //echo "<br class='page_break'>";
            
            // Comments and stuff
            // TODO at some point
            
            echo "<script> $('a').contents().unwrap(); $('.studentUnitInfo').remove(); </script>";
            
        echo "</body></html>";
            
            
            
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
        return new BespokeUnit($unitID, $params, $loadParams);
    }
    
    
    
    
    /**
     * Export unit grid to excel
     * @global type $CFG
     * @global type $DB
     * @global type $USER
     * @param type $qualID
     */
    public function export_unit_grid($qualID)
    {
                
        global $CFG, $DB, $USER;
                
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
                     ->setCreator(fullname($USER))
                     ->setLastModifiedBy(fullname($USER))
                     ->setTitle($this->get_display_name())
                     ->setSubject($this->get_display_name())
                     ->setDescription($this->get_display_name() . " - generated by Moodle Grade Tracker");

        // Remove default sheet
        $objPHPExcel->removeSheetByIndex(0);
        
        $sheetIndex = 0;
        
                
        // Have a worksheet for each unit
        $qualificationID = ($qualID) ? $qualID : -1;
        $students = get_users_on_unit_qual($this->id, $qualificationID);
                
        $criteria = $this->get_used_criteria_names();
        
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
       
        // Set current sheet
        $objPHPExcel->createSheet($sheetIndex);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $objPHPExcel->getActiveSheet()->setTitle("Grades");

        $rowNum = 1;

        // Headers
        $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", "ID");
        $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", "First Name");
        $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", "Last Name");
        $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", "Username");

        $letter = 'E';

        

        if ($criteria)
        {
            foreach($criteria as $criterion)
            {
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("{$letter}{$rowNum}", $criterion, PHPExcel_Cell_DataType::TYPE_STRING);
                $letter++;
            }
        }

        $rowNum++;

        if ($students)
        {

            foreach($students as $student)
            {

                // Load student into
                $this->load_student_information($student->id, $qualificationID, $loadParams);
                
                $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", $student->id);
                $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", $student->firstname);
                $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", $student->lastname);
                $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", $student->username);
                
                $letter = 'E';

                if ($this->is_student_doing())
                {
                
                    // Loop criteria
                    if ($criteria)
                    {
                        foreach($criteria as $criterion)
                        {

                            $studentCriterion = $this->get_single_criteria(-1, $criterion);
                            if ($studentCriterion)
                            {
                                
                                
                                // Get possible values
                                $possibleValues = $studentCriterion->get_possible_values();
                                
                                $possibleValuesArray[-1] = 'N/A';
                                
                                if ($possibleValues){
                                    foreach($possibleValues as $val){
                                        $possibleValuesArray[$val->id] = $val->shortgrade;
                                    }
                                }
                                
                                
                                
                                $shortValue = 'N/A';
                                $studentValueObj = $studentCriterion->get_student_value();	
                                if ($studentValueObj){
                                    $shortValue = $studentValueObj->get_short_value();
                                }
                                $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", $shortValue);

                                // Apply drop-down list
                                $objValidation = $objPHPExcel->getActiveSheet()->getCell("{$letter}{$rowNum}")->getDataValidation();
                                $objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
                                $objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                                $objValidation->setAllowBlank(false);
                                $objValidation->setShowInputMessage(true);
                                $objValidation->setShowErrorMessage(true);
                                $objValidation->setShowDropDown(true);
                                $objValidation->setErrorTitle('input error');
                                $objValidation->setError('Value is not in list');
                                $objValidation->setPromptTitle('Choose a value');
                                $objValidation->setPrompt('Please choose a criteria value from the list');
                                $objValidation->setFormula1('"'.implode(",", $possibleValuesArray).'"');

                            }
                            else
                            {
                                $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", "");
                            }

                            $letter++;

                        }
                    }

                    $rowNum++;
                
                }

            }
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);

        }

        // Freeze rows and cols (everything to the left of E and above 2)
        $objPHPExcel->getActiveSheet()->freezePane('E2');
        
        
        
        
        // Now do it again for comments on worksheet 2
 
        $sheetIndex = 1;
        
        // Set current sheet
        $objPHPExcel->createSheet($sheetIndex);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $objPHPExcel->getActiveSheet()->setTitle("Comments");

        $rowNum = 1;

        // Headers
        $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", "ID");
        $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", "First Name");
        $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", "Last Name");
        $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", "Username");

        $letter = 'E';

        if ($criteria)
        {
            foreach($criteria as $criterion)
            {
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("{$letter}{$rowNum}", $criterion, PHPExcel_Cell_DataType::TYPE_STRING);
                $letter++;
            }
        }

        $rowNum++;

        if ($students)
        {

            foreach($students as $student)
            {

                // Load student into
                $this->load_student_information($student->id, $qualificationID, $loadParams);
                
                $objPHPExcel->getActiveSheet()->setCellValue("A{$rowNum}", $student->id);
                $objPHPExcel->getActiveSheet()->setCellValue("B{$rowNum}", $student->firstname);
                $objPHPExcel->getActiveSheet()->setCellValue("C{$rowNum}", $student->lastname);
                $objPHPExcel->getActiveSheet()->setCellValue("D{$rowNum}", $student->username);
                
                $letter = 'E';

                if ($this->is_student_doing())
                {
                
                    // Loop criteria
                    if ($criteria)
                    {
                        foreach($criteria as $criterion)
                        {

                            $studentCriterion = $this->get_single_criteria(-1, $criterion);
                            if ($studentCriterion)
                            {
                                
                                $comments = $studentCriterion->get_comments();
                                $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", $comments);

                            }
                            else
                            {
                                $objPHPExcel->getActiveSheet()->setCellValue("{$letter}{$rowNum}", "");
                            }

                            $letter++;

                        }
                    }

                    $rowNum++;
                
                }

            }
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);

        }

        // Freeze rows and cols (everything to the left of E and above 2)
        $objPHPExcel->getActiveSheet()->freezePane('E2');
        

        // End
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        ob_clean();
        $objWriter->save('php://output');
        exit;                
        
    }
    
    
    
    
    /**
      * Import unit grid data from excel
      * @global type $CFG
      * @global type $DB
      * @global type $USER
      * @param type $qualID
      * @param type $file
      * @param type $confirm
      * @return boolean|string
      */       
     public function import_unit_grid($qualID, $file, $confirm = false){
        
        global $CFG, $DB, $USER;
        
        require_once 'BespokeValue.class.php';
                
        $now = time();
                
        $return = array();
        $output = "";
        $cnt = 0;
        
        if ($confirm)
        {
            
            $output .= "loading file {$file['tmp_name']} ...<br>";
            
            try {
                
                $inputFileType = PHPExcel_IOFactory::identify($file['tmp_name']);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($file['tmp_name']);
                
            } catch(Exception $e){
                
                print_error($e->getMessage());
                return false;
                
            }
            
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELALL;
            $loadParams->loadAward = true;
            
            $cnt = 0;
            
            $output .= "file loaded successfully ...<br>";
            
            $objPHPExcel->setActiveSheetIndex(0);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            
            $output .= " loaded worksheet - " . $objWorksheet->getTitle() . " ...<br>";
            
            $commentsWorkSheet = $objPHPExcel->getSheet(1);
            
            $output .= " loaded worksheet - " . $commentsWorkSheet->getTitle() . " ...<br>";
            
            $lastCol = $objWorksheet->getHighestColumn();
            $lastCol++;
            $lastRow = $objWorksheet->getHighestRow();
                        
            
            // Loop through rows to get students
            for ($row = 2; $row <= $lastRow; $row++)
            {

                $output .= "processing row {$row} ...<br>";
                
                // Loop columns
                $rowClass = ( ($row % 2) == 0 ) ? 'even' : 'odd';

                for ($col = 'A'; $col != $lastCol; $col++){

                    $cellValue = $objWorksheet->getCell($col . $row)->getCalculatedValue();

                    if ($col == 'A'){
                        $userID = $cellValue;
                        $this->load_student_information($userID, $qualID, $loadParams);
                        $output .= "loaded student " . fullname($this->student) . " ({$this->student->username}) ...<br>";
                        continue; // Don't want to print the id out
                    }


                    if ($col != 'A' && $col != 'B' && $col != 'C' && $col != 'D'){

                        $value = $cellValue;

                        // Get studentCriteria to see if it has been updated since we downloaded the sheet
                        $criteriaName = $objWorksheet->getCell($col . "1")->getCalculatedValue();
                        $studentCriterion = $this->get_single_criteria(-1, $criteriaName);
                        
                        $output .= "attempting to set value for criterion {$criteriaName} to {$value} ... ";

                        if ($studentCriterion)
                        {

                            
                            // Get possible values
                            $possibleValues = $studentCriterion->get_possible_values(); 

                            $possibleValuesArray[-1] = 'N/A';

                            if ($possibleValues){
                                foreach($possibleValues as $val){
                                    $possibleValuesArray[$val->id] = $val->shortgrade;
                                }
                            }   
                            
                            
                            // Set new value
                            if (array_search($value, $possibleValuesArray) !== false)
                            {

                                $valueID = array_search($value, $possibleValuesArray);
                                $studentCriterion->set_user($USER->id);
                                $studentCriterion->set_date();
                                $studentCriterion->update_students_value($valueID);

                                // Comments
                                $commentsCellValue = (string)$commentsWorkSheet->getCell($col . $row)->getCalculatedValue();
                                $commentsCellValue = trim($commentsCellValue);
                                $studentCriterion->add_comments($commentsCellValue);

                                $studentCriterion->save_student($qualID, false);
                                $output .= "success - criterion updated ...<br>";
                                $cnt++;

                            }
                            else
                            {
                                $output .= "error - {$value} is an invalid criteria value ...<br>";
                            }

                        } 
                        else
                        {
                            $output .= "error - student criteria could not be loaded ...<br>";
                        }
                        
                    }

                }
                
                // recalculate student unit award
                $this->calculate_unit_award($qualID);

            }
            
            $output .= "end of worksheet ...<br>";
            $output .= "end of process - {$cnt} criteria updated updated<br>";
            
            
        }
        else
        {
            
            try {
                
                $inputFileType = PHPExcel_IOFactory::identify($file['tmp_name']);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($file['tmp_name']);
                
            } catch(Exception $e){
                
                print_error($e->getMessage());
                return false;
                
            }
            
            // Save the tmp file to Moodledata so we can still use it when we click confirm
            $saveFile = bcgt_save_file($file['tmp_name'], $qualID . '_' . $this->id . '_' . $now . '.xlsx', "import_unit_grids");
            if (!$saveFile){
                print_error('Could not save uploaded file. Either the save location does not exist, or is not writable. (moodledata - bcgt/import_unit_grids)');
            }    
            
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELALL;
            $loadParams->loadAward = true;
                     
            $unix = $objPHPExcel->getProperties()->getCreated();
                        
            $objPHPExcel->setActiveSheetIndex(0);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            
            $lastCol = $objWorksheet->getHighestColumn();
            $lastCol++;
            $lastRow = $objWorksheet->getHighestRow();
            
            $commentWorkSheet = $objPHPExcel->getSheet(1);
            
            
            
            // See if anything has been updated in the DB since we downloaded the file
            $updates = $DB->get_records_sql(
                    "SELECT uc.*, c.name
                     FROM {block_bcgt_user_criteria} uc
                     INNER JOIN {block_bcgt_criteria} c ON c.id = uc.bcgtcriteriaid
                     WHERE c.bcgtunitid = ?
                     AND uc.bcgtqualificationid = ? 
                     AND ( dateset > ? OR dateupdated > ? ) ", 
                        array($this->id, $qualID, $unix, $unix));

            if ($updates)
            {
                
                $output .= "<div class='importwarning'>";
                    $output .= "<b>".get_string('warning').":</b><br><br>";
                    $output .= "<p>".get_string('importwarning', 'block_bcgt')."</p>";
                    foreach($updates as $update)
                    {
                        
                        $value = $DB->get_record("block_bcgt_value", array("id" => $update->bcgtvalueid));
                        $val = ($value) ? $value->value : 'N/A';
                        if ($update->dateupdated > $update->dateset){
                            $updateTime = $update->dateupdated;
                            $updateUser = $DB->get_record("user", array("id" => $update->updatedbyuserid));
                        } else {
                            $updateTime = $update->dateset;
                            $updateUser = $DB->get_record("user", array("id" => $update->setbyuserid));
                        }

                        $student = $DB->get_record("user", array("id" => $update->userid));
                        
                        $output .= $update->name . " for user ".fullname($student)." ({$student->username}) was updated to: " . $val . ", at: " . date('d-m-Y, H:i', $updateTime) . ", by: ".fullname($updateUser)." ({$updateUser->username})<br>";
                        
                    }
                    
                $output .= "</div>";
                $output .= "<br><br>";
                
            }
                        
            // Key
            $output .= "<h3>Key</h3>";
            $output .= "<table class='importgridtable'>";
                $output .= "<tr>";
                    $output .= "<td class='updatedsince crit'>&nbsp;</td>";
                    $output .= "<td>The criterion has been updated in Gradetracker since you downloaded the spreadsheet</td>";
                $output .= "</tr>";
                    
                $output .= "<tr>";
                    $output .= "<td class='updatedinsheet crit'>&nbsp;</td>";
                    $output .= "<td>The criterion value in your spreadsheet is different to the one in Gradetracker. (You presumably updated it in the spreadsheet).</td>";
                $output .= "</tr>";
                
                $output .= "<tr>";
                    $output .= "<td class='updatedinsheet updatedsince crit'>&nbsp;</td>";
                    $output .= "<td>Both of the above</td>";
                $output .= "</tr>";
                
            $output .= "</table>";
            
            $output .= "<br><br>";
            
            $output .= "Below you will find all the data in the spreadsheet you have just uploaded.<br><br>";
            
            $output .= "<h2 class='c'>".$this->get_display_name()."</h2>";
            
            $output .= "<div class='importgriddiv'>";
            $output .= "<table class='importgridtable'>";
            
                $output .= "<tr>";
                
                    $output .= "<th>".get_string('name')."</th>";
                    $output .= "<th>".get_string('username')."</th>";
                    
                    for ($col = 'E'; $col != $lastCol; $col++){

                        $cellValue = $objWorksheet->getCell($col . "1")->getCalculatedValue();
                        $output .= "<th>{$cellValue}</th>";

                    }
                    
                $output .= "</tr>";
                
                // Loop through rows to get students
                for ($row = 2; $row <= $lastRow; $row++)
                {
                    
                    // Loop columns
                    $rowClass = ( ($row % 2) == 0 ) ? 'even' : 'odd';

                    $output .= "<tr class='{$rowClass}'>";

                        for ($col = 'A'; $col != $lastCol; $col++){
                            
                            $critClass = '';
                            $currentValue = 'N/A';                                        
                            $cellValue = $objWorksheet->getCell($col . $row)->getCalculatedValue();

                            if ($col == 'A'){
                                $userID = $cellValue;
                                $this->load_student_information($userID, $qualID, $loadParams);
                                $output .= "<td>".fullname($this->student)."</td>";
                                $output .= "<td>{$this->student->username}</td>";
                                continue; // Don't want to print the id out
                            }
                                                        
                            if ($col != 'A' && $col != 'B' && $col != 'C' && $col != 'D'){

                                $value = $cellValue;
                                
                                $critClass .= 'crit ';

                                // Get studentCriteria to see if it has been updated since we downloaded the sheet
                                $criteriaName = $objWorksheet->getCell($col . "1")->getCalculatedValue();
                                $studentCriterion = $this->get_single_criteria(-1, $criteriaName);
                                
                                if ($studentCriterion)
                                {
                                
                                    $critDateSet = $studentCriterion->get_date_set_unix();
                                    $critDateUpdated = $studentCriterion->get_date_updated_unix();

                                    $studentValueObj = $studentCriterion->get_student_value();	
                                    if ($studentValueObj)
                                    {
                                        $currentValue = $studentValueObj->get_short_value();
                                    }
                                    
                                    if ($currentValue != $value){
                                        $critClass .= 'updatedinsheet ';
                                    }
                                    
                                    if ($critDateSet > $unix || $critDateUpdated > $unix)
                                    {
                                        $critClass .= 'updatedsince ';
                                    }
                                    
                                    $comment = $commentWorkSheet->getCell($col . $row)->getCalculatedValue();

                                    $output .= "<td title='{$comment}' class='{$critClass}' currentValue='{$currentValue}' unix='{$unix}' dateset='{$critDateSet}' dateupdated='{$critDateUpdated}'><small>{$cellValue}</small></td>";
                                
                                } 
                                else
                                {
                                    $output .= "<td></td>";
                                }

                            }

                        }

                    $output .= "</tr>";

                }
                
                
            
            $output .= "</table>";
            $output .= "</div>";
            
            $output .= "<form action='' method='post' class='c'>";
                $output .= "<input type='hidden' name='qualID' value='{$qualID}' />";
                $output .= "<input type='hidden' name='unitID' value='{$this->id}' />";
                $output .= "<input type='hidden' name='now' value='{$now}' />";
                $output .= "<input type='submit' class='btn' name='submit_confirm' value='".get_string('confirm')."' />";
                $output .= str_repeat("&nbsp;", 8);
                $output .= "<input type='button' class='btn' onclick='window.location.href=\"{$CFG->wwwroot}/blocks/bcgt/grids/student_grid.php?sID={$this->studentID}&qID={$this->id}\";' value='".get_string('cancel')."' />";

            $output .= "</form>";
            
              
        }
        
        $return['summary'] = $cnt;
        $return['output'] = $output;
        return $return;
                
    }
    
    
    
    
    
    
    
}