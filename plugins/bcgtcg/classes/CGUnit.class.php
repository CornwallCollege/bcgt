<?php
/**
 * Description of ALevelUnit
 *
 * @author mchaney
 */
global $CFG;

require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGQualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteria.class.php');

class CGUnit extends Unit {
	
	//any constants or properties
    
    const DEFAULTUNITCREDITSNAME = 'DEFAULT_UNIT_LEVEL_CREDITS';
    
    protected $credits;
    protected $grading;
    protected $defaultColumns = array('picture', 'username', 'name'); # ?
    protected $usePercentageBar = true;
    
    public function CGUnit($unitID, $params, $loadParams)
    {
        
        global $DB;
        
        parent::Unit($unitID, $params, $loadParams);
        
        if($unitID != -1)
		{
			$creditsObj = CGUnit::retrieve_credits($unitID);
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
            
            // Get grading from attributes
            $grading = $DB->get_record("block_bcgt_unit_attributes", array("bcgtunitid" => $unitID, "attribute" => "GRADING"));
            $this->grading = ($grading) ? $grading->value : null;
            // Percent bar attribute
            $percent = $DB->get_record("block_bcgt_unit_attributes", array("bcgtunitid" => $unitID, "attribute" => "PERCENT_BAR"));
            $this->usePercentageBar = ($percent && $percent->value == 1) ? true : false;
            
            $this->weighting = (isset($params->weighting)) ? $params->weighting : 1;
                      
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
            
            if (isset($params->weighting)) $this->weighting = $params->weighting;
            if (isset($params->grading)) $this->grading = $params->grading;
            
		}
        else 
        {
            $defaultCredits = $this->get_default_credits();
            $this->credits = $defaultCredits;
            $this->weighting = "1.0"; # Default
            $this->grading = "PMD"; # Default
            
        }
        
        
    }
    
    /*
	 * Gets the associated Qualification ID
	 */
	public function get_typeID()
    {
        return CGQualification::ID;
    }
    
    /*
	 * Gets the name of the associated qualification. 
	 */
	public function get_type_name()
    {
        return CGQualification::NAME;
    }
    
    /*
	 * Gets the name of the associated qualification family. 
	 */
	public function get_family_name()
    {
        return CGQualification::NAME;
    }
	
	/**
	 * Get the family of the qual.
	 */
	public function get_familyID()
    {
        return CGQualification::FAMILYID;
    }
    
    public function get_credits(){
        return $this->credits;
    }
    
    public function get_grading(){
        return (!is_null($this->grading)) ? $this->grading : 'PMD';
    }
    
    public function get_default_credits()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_unit_type_att} WHERE bcgtlevelid = ? 
            AND bcgttypefamilyid = ? AND name = ?";
        $result = $DB->get_record_sql($sql, array($this->levelID, 
            CGQualification::FAMILYID, CGUnit::DEFAULTUNITCREDITSNAME));
        if($result)
        {
            return $result->value;
        }
        return 0;
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
    
    public function load_student_information($studentID, $qualID, 
            $loadParams = null, $loadSubCriteria = false)
	{
        
        parent::load_student_information($studentID, $qualID, $loadParams, $loadSubCriteria);
        
        // Get the student's unit points if they have any
        $this->calculate_unit_award($qualID, false);

        
    }
    
    
    /**
     * @param type $unit
     * @param type $rank
     * @param type $award
     * @param type $unitAwards
     * @return string
     */
    public function edit_unit_award($unit, $rank, $award, $qualID, $unitAwards = null)
	{
		$retval = "";
        $retval .= "<select class='unitAward' id='unitAwardEdit_".$this->get_id()."_{$this->studentID}' name='unitAwardAPL_".$this->get_id()."' unitid='{$this->get_id()}' qualid='{$qualID}' studentid='{$this->studentID}'>";        
		$retval .= "<option value='-1'>N/A</option>";
		if($unitAwards)
		{
			foreach($unitAwards AS $possAward)
			{
				$selected = '';
				if($possAward->award == $award)
				{
					$selected = 'selected';
				}
				$retval .= "<option $selected value='$possAward->id'>$possAward->award</option>";
                
                if ($this->get_grading() == 'P')
                {
                    break;
                }
                
			}
		}
		$retval .= "</select>";
		return $retval;
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
        $weight = optional_param('weighting', $this->weighting, PARAM_FLOAT);
        $grading = optional_param('grading', $this->grading, PARAM_TEXT);
        $percent = optional_param('usepercent', 1, PARAM_INT);
        
        $chk = array();
        $chk['PMD'] = ($grading == 'PMD') ? 'checked' : '';
        $chk['PCD'] = ($grading == 'PCD') ? 'checked' : '';
        $chk['P'] = ($grading == 'P') ? 'checked' : '';
        
        $chk['PERCENT_YES'] = ($percent) ? 'checked' : '';
        $chk['PERCENT_NO'] = (!$percent) ? 'checked': '';
        
        // If not set at all, probably new unit, so set PMD as default
        if (is_null($grading)) $chk['PMD'] = 'checked';
        
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='credits'>".get_string('credits', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<input style='width:40px;' type='number' name='credits' value='{$credits}' />";
            $retval .= "</div>";
        $retval .= "</div>";
        
        // Weight
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='weighting'>".get_string('weighting', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<input style='width:30px;' type='text' name='weighting' value='{$weight}' />";
            $retval .= "</div>";
        $retval .= "</div>";
                
        // Grading for unit
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='weighting'>".get_string('grading', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<input type='radio' name='grading' value='PMD' {$chk['PMD']} /> Pass, Merit, Distinction<br><input type='radio' name='grading' value='PCD' {$chk['PCD']} /> Pass, Credit, Distinction<br><input type='radio' name='grading' value='P' {$chk['P']} /> Pass Only";
            $retval .= "</div>";
        $retval .= "</div>";
        
        // Use the percentage bar?
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='weighting'>".get_string('percentagebar', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<input type='radio' name='usepercent' value='1' {$chk['PERCENT_YES']} /> Enable<br><input type='radio' name='usepercent' value='0' {$chk['PERCENT_NO']} /> Disable";
            $retval .= "</div>";
        $retval .= "</div>";
        
        return $retval;
        
    }
    
    /**
	 * Used in edit unit
	 * Gets the criteria tablle that will go on edit_unit_form.php
	 * This is different for each unit type. 
	 */
	public function get_edit_criteria_table()
    {
        $retval = "";
        
        $family = optional_param('unitTypeFamily', 0, PARAM_INT);#
        $pathway = optional_param('pathway', 0, PARAM_INT);
        $pathwayType = optional_param('type', 0, PARAM_INT);
        
        if ( ($this->id > 0) || ( $family > 0 && $pathway > 0 && $pathwayType > 0 ) )
        {
        
            $retval .= "<script> var numOfCriterion = 0; var dynamicNumOfCriterion = 0; </script>";

            $retval .= "<a href='#' id='addNewCrit'>".get_string('addcriteria', 'block_bcgt')."</a><br><br>";

            $retval .= "<table id='criteriaHolder' class='cgCriteriaHolderTable'>";

               $retval .=  $this->build_criteria_form();

            $retval .= "</table>";
            $retval .= "<br><br>";
        
        }
        else
        {
            $retval .= "<p>".get_string('criterianotavailableuntilformcomplete', 'block_bcgt')."</p>";
        }
                        
        return $retval;
        
    }
    
    /**
	 * Builds the table of the unit information that gets presented to the 
	 * user when they hover of the unit name. This is called through ajax and jquery.
	 */
	public function build_unit_details_table()
	{
        
		$retval = "";

        $retval .= "<h3>{$this->name}</h3>";
        
        if ($this->comments != ''){
            $retval .= "<br><div style='background-color:#FF9;padding:10px;'>".bcgt_html($this->comments, true)."</div><br>";
        }
        
        $retval .= "<table>";
            $retval .= "<tr>";
                $retval .= "<th>".get_string('criterianame', 'block_bcgt')."</th>";
                $retval .= "<th>".get_string('criteriadetails', 'block_bcgt')."</th>";
            $retval .= "</tr>";
            
            if ($this->criterias)
            {
                foreach($this->criterias as $crit)
                {
                    $retval .= "<tr><td>{$crit->get_name()}</td><td>{$crit->get_details()}</td></tr>";
                }
            }
            
        $retval .= "</table>";
        
        return $retval;
                
	}
    
    protected function build_criteria_form()
    {
        
        global $CFG, $OUTPUT;
        
        $retval = "";
                
            $retval .= "<tr>";
                $retval .= "<th>".get_string('name', 'block_bcgt')."</th>";
                $retval .= "<th>".get_string('details', 'block_bcgt')."</th>";
                $retval .= "<th>".get_string('weighting', 'block_bcgt')."</th>";
                $retval .= "<th>".get_string('grading', 'block_bcgt')."</th>";
                $retval .= "<th>".get_string('order', 'block_bcgt')."</th>";
                $retval .= "<th>".get_string('parent', 'block_bcgt')."</th>";
                $retval .= "<th></th>";
            $retval .= "</tr>";
            
            $flatCriteria = $this->load_criteria_flat_array();
                        
            // Criteria
            if($flatCriteria)
            {
            
                
                require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
                $criteriaSorter = new CGCriteriaSorter();
                uasort($flatCriteria, array($criteriaSorter, "ComparisonOrder"));
                
                $i = 0; // Number of criteria displayed
                foreach($flatCriteria as $criterion)
                {
                    
                    $i++;
                    
                    $chk = array();
                    $chk['PMD'] = ($criterion->get_grading() == 'PMD') ? 'checked' : '';
                    $chk['PCD'] = ($criterion->get_grading() == 'PCD') ? 'checked' : '';
                    $chk['P'] = ($criterion->get_grading() == 'P') ? 'checked' : '';
                    $chk['DATE'] = ($criterion->get_grading() == 'DATE') ? 'checked' : '';
                    $chk['TEXT'] = ($criterion->get_grading() == 'TEXT') ? 'checked' : '';
                    
                    $retval .= "<tr id='criterionRow_{$i}'>";
                    
                        $retval .= "<td><input type='hidden' name='criterionIDs[{$i}]' value='{$criterion->get_id()}' /><input type='text' placeholder='Name' name='criterionNames[{$i}]' value='{$criterion->get_name()}' class='critNameInput' id='critName_{$i}' /></td>";
                        $retval .= "<td><textarea placeholder='Criteria Details' name='criterionDetails[{$i}]' id='criterionDetails{$i}' class='critDetailsTextArea'>".format_text($criterion->get_details(), FORMAT_PLAIN)."</textarea></td>";
                        $retval .= "<td><input title='Weighting' type='text' class='w40' name='criterionWeights[{$i}]' value='{$criterion->get_weighting()}' /></td>";
                        $retval .= "<td class='align-l'><input type='radio' name='criterionGradings[{$i}]' value='PMD' {$chk['PMD']} /> Pass, Merit, Distinction<br><input type='radio' name='criterionGradings[{$i}]' value='PCD' {$chk['PCD']} /> Pass, Credit, Distinction<br><input type='radio' name='criterionGradings[{$i}]' value='P' {$chk['P']} /> Pass Only<br><input type='radio' name='criterionGradings[{$i}]' value='DATE' {$chk['DATE']} /> Date<br><input type='radio' name='criterionGradings[{$i}]' value='TEXT' {$chk['TEXT']} /> Free Text</td>";
                        $retval .= "<td><input type='text' class='w40' name='criterionOrders[{$i}]' value='{$criterion->get_order()}' /></td>";
                        $retval .= "<td><select name='criterionParents[{$i}]'><option value=''></option>";
                            foreach($flatCriteria as $c)
                            {
                                if ($c->get_id() == $criterion->get_id()) continue;
                                $sel = ($criterion->get_parent_ID() == $c->get_id()) ? 'selected' : '';
                                $retval .= "<option value='{$c->get_name()}' {$sel}>{$c->get_name()}</option>";
                            }
                        $retval .= "</select></td>";
                        $retval .= "<td><a href='#' onclick='removeCriterionTable({$i});return false;'><img src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/redX.png' /></a><script>numOfCriterion++;dynamicNumOfCriterion++;</script></td>";
                        
                    $retval .= "</tr>";
                                        
                }
                
            }
                
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
            $criteriaGradings = $_POST['criterionGradings'];
            $criteriaWeights = $_POST['criterionWeights'];
            $criteriaOrders = $_POST['criterionOrders'];
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
                    $params->ordernum = $criteriaOrders[$DID];
                    $obj = new CGCriteria(-1, $params, Qualification::LOADLEVELCRITERIA);                    
                                        
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
                    $obj->set_order($criteriaOrders[$DID]);
                    
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
    
    
    public static function get_edit_form_menu($disabled = '', $unitID = -1)
	{
        $jsModule = array(
            'name'     => 'mod_bcgtcg',
            'fullpath' => '/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        global $PAGE;
        $PAGE->requires->js_init_call('M.mod_bcgtcg.cginiteditunit', null, true, $jsModule);
        
        $pathwayID = optional_param('pathway', -1, PARAM_INT);
        $pathwayTypeID = optional_param('type', -1, PARAM_INT);
        $weight = optional_param('weighting', "1.0", PARAM_FLOAT);
        
        $pathways = get_pathway_from_type(CGQualification::FAMILYID);
        
        if ($unitID > 0){
            
            $unit = new CGUnit($unitID, null, null);
            if ($unit)
            {
                $weight = $unit->get_weighting();
                $both = get_pathway_and_type_from_dep_type($unit->get_pathway_type());
                $pathwayID = $both->pathway;
                $pathwayTypeID = $both->type;
            }
            
        }
        
        
        $retval = "";
        
        $retval .= "<div class='inputContainer'>";
            $retval .= "<div class='inputLeft'>";
                $retval .= "<label for='pathway'><span class='required'>*</span>".get_string('pathway', 'block_bcgt') . ": </label>";
            $retval .= "</div>";
            $retval .= "<div class='inputRight'>";
                $retval .= "<select name='pathway' {$disabled} id='unitPathway'>";
                    $retval .= "<option value=''>".get_string('pleaseselect', 'block_bcgt')."...</option>";
                    if ($pathways)
                    {
                        foreach($pathways as $key => $pathway)
                        {
                            $sel = ($pathwayID == $key) ? 'selected' : '';
                            $retval .= "<option value='{$key}' {$sel} >{$pathway}</option>";
                        }
                    }
                $retval .= "</select>";
            $retval .= "</div>";
        $retval .= "</div>";
        
        
        if ($pathwayID >= 1){
            
            $types = get_pathway_types_from_pathway($pathwayID);
            
            if ($types)
            {
                $retval .= "<div class='inputContainer'>";
                    $retval .= "<div class='inputLeft'>";
                        $retval .= "<label for='type'><span class='required'>*</span>".get_string('type', 'block_bcgt') . ": </label>";
                    $retval .= "</div>";
                    $retval .= "<div class='inputRight'>";
                        $retval .= "<select name='type' {$disabled} id='unitPathwayType'>";
                            $retval .= "<option value=''>".get_string('pleaseselect', 'block_bcgt')."...</option>";

                            foreach($types as $key => $type)
                            {
                                $sel = ($pathwayTypeID == $key) ? 'selected' : '';
                                $retval .= "<option value='{$key}' {$sel} >{$type}</option>";
                            }

                        $retval .= "</select>";
                    $retval .= "</div>";
                $retval .= "</div>";
                
                
            }
            else
            {
                $retval .= "<select name='type' {$disabled} id='unitPathwayType'>";
                    $retval .= "<option value=''>".get_string('notypesforthispathway', 'block_bcgt')."</option>";
                $retval .= "</select>";
            }
            
            
        }
        
       
        
        
        
        
        return $retval;
        
    }
    
    
    public function process_create_update_unit_form(){
        
        // If the unit isn't new, we don't care about these as they won't be changing
        if ($this->id < 1){
            $pathway = optional_param('pathway', null, PARAM_INT);
            $pathwayType = optional_param('type', null, PARAM_INT);
            $type = optional_param('type', null, PARAM_INT);
        }
        
        $extcode = optional_param('unique', NULL, PARAM_TEXT);
        $name = optional_param('name', NULL, PARAM_TEXT);
        $weighting = optional_param('weighting', NULL, PARAM_NUMBER);
        $grading = optional_param('grading', "PMD", PARAM_TEXT);
        $percentBar = optional_param('usepercent', 1, PARAM_INT);
                
        
        $extcode = trim($extcode);
        $name = trim($name);
        
        $this->processed_errors = '';
        
        if ($this->id < 1){
        
            // Pathway
            if (is_null($pathway) || $pathway < 1){
                $this->processed_errors .= get_string('error:pathway', 'block_bcgt') . '<br>';
            }

            // Pathway type
            if (is_null($pathwayType) || $pathwayType < 1){
                $this->processed_errors .= get_string('error:pathwaytype', 'block_bcgt') . '<br>';
            }
        
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
        
        if ($this->id < 1){
            $this->pathwayTypeID = get_pathway_dep_type_from_both($pathway, $type);
        }
        
        $this->uniqueID = $extcode;
        $this->name = $name;
        $this->weighting = $weighting;
        $this->grading = $grading;
        $this->usePercentageBar = (bool)$percentBar;
        
        unset($this->processed_errors);
        return true;
        
        
    }
    
    
    /**
	 * Inserts the unit AND the criteria and all related details
	 * Dont forget to set the id of the unit object
	 */
	public function insert_unit($trackingTypeID = CGQualification::ID)
    {
        
        global $DB;
        
        // Insert unit
		$stdObj = new stdClass();
		$stdObj->name = $this->name;
		$stdObj->details = $this->details;
		$stdObj->credits = $this->credits;
		$stdObj->uniqueid = $this->uniqueID;
		$stdObj->bcgttypeid = $trackingTypeID;
		$stdObj->bcgtunittypeid = $this->unitTypeID;
		$stdObj->bcgtlevelid = $this->levelID;;
        $stdObj->weighting = $this->weighting;
        $stdObj->pathwaytypeid = $this->pathwayTypeID;
		$this->id = $DB->insert_record('block_bcgt_unit', $stdObj);
        
        // Insert criteria
		foreach($this->criterias AS $criteria)
		{
			$criteria->insert_criteria($this->id);
		}
                
        // Grading attribute
        $stdObj = new stdClass();
        $stdObj->bcgtunitid = $this->id;
        $stdObj->attribute = 'GRADING';
        $stdObj->value = $this->grading;
        $DB->insert_record("block_bcgt_unit_attributes", $stdObj);
        
        // Percent bar attribute
        $stdObj = new stdClass();
        $stdObj->bcgtunitid = $this->id;
        $stdObj->attribute = 'PERCENT_BAR';
        $stdObj->value = $this->usePercentageBar;
        $DB->insert_record("block_bcgt_unit_attributes", $stdObj);
        
        // Log
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_UNIT, LOG_VALUE_GRADETRACKER_INSERTED_UNIT, null, null, $this->id, null, $this->id);        
	
        
        
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
                        
		$stdObj = new stdClass();
		$stdObj->id = $this->id;
		$stdObj->name = $this->name;
		$stdObj->details = $this->details;
		$stdObj->credits = $this->credits;
		$stdObj->uniqueid = $this->uniqueID;
		$stdObj->bcgtunittypeid = $this->unitTypeID;
		$stdObj->bcgtlevelid = $this->levelID;
        $stdObj->weighting = $this->weighting;
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
        
        
        // Grading attribute
        $grading = $DB->get_record("block_bcgt_unit_attributes", array("bcgtunitid" => $this->id, "attribute" => "GRADING"));
        if ($grading)
        {
            $stdObj = new stdClass();
            $stdObj->id = $grading->id;
            $stdObj->value = $this->grading;
            $DB->update_record("block_bcgt_unit_attributes", $stdObj);
        }
        else
        {
            $stdObj = new stdClass();
            $stdObj->bcgtunitid = $this->id;
            $stdObj->attribute = 'GRADING';
            $stdObj->value = $this->grading;
            $DB->insert_record("block_bcgt_unit_attributes", $stdObj);
        }
        
        // Use percent bar attribute
        $percent = $DB->get_record("block_bcgt_unit_attributes", array("bcgtunitid" => $this->id, "attribute" => "PERCENT_BAR"));
        if ($percent)
        {
            $stdObj = new stdClass();
            $stdObj->id = $percent->id;
            $stdObj->value = (int)$this->usePercentageBar;
            $DB->update_record("block_bcgt_unit_attributes", $stdObj);
        }
        else
        {
            $stdObj = new stdClass();
            $stdObj->bcgtunitid = $this->id;
            $stdObj->attribute = 'PERCENT_BAR';
            $stdObj->value = (int)$this->usePercentageBar;
            $DB->insert_record("block_bcgt_unit_attributes", $stdObj);
        }
                
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_UNIT, LOG_VALUE_GRADETRACKER_UPDATED_UNIT, null, null, $this->id, null, $this->id);        
        
        return true;
        
    }
    
    /**
	 * Certain qualificaton types have unit awards
	 */
	public function unit_has_award()
    {
        return true;
    }
    
    public function get_student_unit_points(){
        if (isset($this->studentUnitPoints)) return $this->studentUnitPoints;
        return '-';        
    }
    
   function calculate_unit_award($qualID, $update = true){
        
       global $DB;
       
       if (!$this->criterias) return;
              
       // Points (rankings) for award values are stored in the type_award table, by default these are:
       // Pass = 1.0 - 1.5
       // Merit = 1.6 - 2.5
       // Distinction = 2.6 - 3.0
       
       // THough people can change these, so let's get these values from the DB when we need them
       
       
       
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
                    if($valueObj->is_criteria_met_bool()){
                                                
                        // If weighting is 0, don't bother adding
                        if ($criteria->get_weighting() > 0){                        
                            $totalPassed++;
                            $ranking = $DB->get_record("block_bcgt_value", array("id" => $valueObj->get_id()), "id, ranking");
                            $awardArray[] = array("value" => $valueObj->get_short_value(), "weighting" => $criteria->get_weighting(), "ranking" => $ranking->ranking);
                        } else {
                            $totalCriteria--;
                        }
                    }
                }
                
            }
            
        }
                
        // Finished looping now, so let's work out if we need to award anything and if so, what
        if($totalPassed == $totalCriteria && $totalCriteria > 0)
        {
            
            // We're passed all the criteria, so let's work out the unit award
            $awardRanking = $this->calculate_average_score($awardArray);
            
            // Update the unit award
            $awardRecord = $this->get_unit_award($awardRanking);

            if ($awardRecord)
            {
                $params = new stdClass();
                $params->award = $awardRecord->award;
                $params->rank = $awardRecord->ranking;
                $award = new Award($awardRecord->id, $params);
                            
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
            $this->userAward = new Award(-1, 'N/S', 0);
            $this->update_unit_award($qualID);
        }
        return $this->userAward;
        
        
    }
    
    /**
     * Get a unit award by its ranking
     * @global type $CFG
     * @param type $ranking
     * @return type 
     */
    protected function get_unit_award($ranking)
    {
        global $DB;
        return $DB->get_record("block_bcgt_type_award", array("bcgttypeid" => Unit::get_unit_tracking_type($this->id), "ranking" => $ranking));
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
        $trackingType = Unit::get_unit_tracking_type($this->id);
        $awards = $DB->get_records("block_bcgt_type_award", array("bcgttypeid" => $trackingType));
                
        $records = array();
        
        if ($awards)
        {
            foreach($awards as $award)
            {
                if ($award->ranking < 1) continue;
                $obj = new stdClass();
                $obj->ranking = $award->ranking;
                $obj->pointslower = $award->pointslower;
                $obj->pointsupper = $award->pointsupper;
                $records[$award->award] = $obj;
            }
        }
                
        $totalCriteria = 0;
        $totalScore = 0;
                
        foreach($awardArray as $award)
        {
            // Get the points ranking of this award and add to total
            // Also take into account different criteria weightings
            $weight = $award['weighting'];
            $rank = $award['ranking'];
            $totalCriteria += $weight;
            $totalScore += ( ($rank * $weight) );
        }
        
        if ($totalCriteria > 0){
            
            $avgScore = round($totalScore / $totalCriteria, 1);

            $this->studentUnitPoints = $avgScore;

            // Now work out where in the points boundaries it lies
            foreach($records as $record)
            {
                if($avgScore >= $record->pointslower && $avgScore <= $record->pointsupper) return $record->ranking;
            }
        
        }
        
        return -1; // Something went quite wrong
        
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
     * 
     * @param type $qualID
     * @param type $advancedMode
     * @param type $editing
     */
    public function get_unit_grid_data($qualID, $advancedMode, $editing, $courseID)
    {
        global $CFG, $DB, $COURSE, $OUTPUT;

        $pageNumber = optional_param('page',1,PARAM_INT);
        $groupingID = optional_param('grID', -1, PARAM_INT);
        
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        
        $criteriaNames = $this->get_used_criteria_names();
        
        // ORDER BY ORDER NUM
        $output = "";
        
        $possibleValues = null;
        $unitAwards = null;
        if($editing)
        {
            $unitAwards = Unit::get_possible_unit_awards($this->get_typeID());
        }
        
        if($editing && $advancedMode)
        {
            $possibleValues = CGQualification::get_possible_values(CGQualification::ID);
        }
        
        //load the session object
        $sessionUnits = isset($_SESSION['session_unit'])? 
        unserialize(urldecode($_SESSION['session_unit'])) : array();
                
        //pn($sessionUnits);exit;
                
        $studentsLoaded = false;
        $studentsArray = array();
        
        $qualArray = array();
        $unitObject = new stdClass();
                        
        if(array_key_exists($this->id, $sessionUnits))
        {
            
            $unitObject = $sessionUnits[$this->id];
          
//            if (isset($unitObject->qualArray))
//            {
//                $qualArray = $unitObject->qualArray;
//                pn($qualArray);exit;
//                if(array_key_exists($qualID, $qualArray) && !empty($qualArray[$qualID]))
//                {
//                    //what happens if a student has been added since?
//
//                    //then this will return an array of students unit objects
//                    //for this qualid for this unit.
//                    $studentsArray = $qualArray[$qualID];
//                    if(count($studentsArray) != 0)
//                    {
//                        $studentsLoaded = true;
//                    }
//                    //studentsArray[] is an object with two properties. The Unit Object with stu
//                    //loaded and a few of the students information.
//                }  
//            }
                        
            if (isset($unitObject->unit))
            {
                            
                if (isset($unitObject->qualArray))
                {
                
                    $qualArray = $unitObject->qualArray;
                    $unitObject = $unitObject->unit;
                                        
                    if (isset($unitObject->students))
                    {
                        $studentsArray = array();
                        $unitStudents = $unitObject->students;

                        if ($qualArray)
                        {
                            foreach($qualArray as $qualID => $students)
                            {
                                if ($students)
                                {
                                    foreach($students as $student)
                                    {
                                        if (isset($unitStudents[$student->id]))
                                        {
                                            $unitStudents[$student->id] = $student;
                                        }
                                    }
                                }
                            }
                        }
                        
                        $studentsArray = $unitStudents;
                        $studentsLoaded = true;
                        
                    }
                
                }

            }
            
        }
                
        $regGroupID = optional_param('regGrpID', false, PARAM_INT);
        
        if(!$studentsLoaded)
        {   
            //load the students that are on this unit for this qual. 
            if ($regGroupID){
                $studentsArray = bcgt_get_register_group_users($regGroupID);
            } else {
                $studentsArray = get_users_on_unit_qual($this->id, $qualID, $courseID, $groupingID);
            }
        }
                          
        if(get_config('bcgt','pagingnumber') != 0)
        {
            $pageRecords = get_config('bcgt','pagingnumber');
            //then we only want a certain number!
            //we also need to take into account the page number we are on.
            //studentsArray is the array of students on the unit on this qual. 
            //the keys are the ids of the students. 
            $keys = array_keys($studentsArray);
            //arrays keys returns an array of the keys of the first aray. This return aray has its keys set to 
            //the numerical order, e.g. always starting at 0, then 1 etc.  
            
            $studentsShowArray = array();
            //are we at the first page, 
            if($pageNumber == 1)
            {
               $i = 0; 
            }
            else
            {
                //no so we want to start at the page number times by how many we show per page
                $i = ($pageRecords * ($pageNumber - 1));
            }
            //we want to loop over and only show the number of students in our page size. 
            $recordsEnd = ($i + $pageRecords);         
            
            for($i;$i<=$recordsEnd;$i++)
            {
                //gets the student object from the array by the key that we are looking at.
                if (isset($keys[$i]) && isset($studentsArray[$keys[$i]]))
                {
                    //so, if we have the student id for the nth student we need. 
                    //then find the student that that id coresponds to from our original array of students. 
                    $student = $studentsArray[$keys[$i]];
                    //add this student to the array that we want to display.
                    $studentsShowArray[$keys[$i]] = $student;
                }
            }
        }
        else {
            $studentsShowArray = $studentsArray;
        }
                
        $rowCount = 0;
        $studentsSessionArray = $studentsArray;
        foreach($studentsShowArray AS $student)
        {
            
            $rowVal = "";
            $rowVal .= "<tr>";
            
            $rowCount++;
            $rowClass = 'rO';
            if($rowCount % 2)
            {
                $rowClass = 'rE';
            }				
            
            if(isset($student->unit))
            {
                //then we are coming from the session and the unit object has aleady
                //been loaded
                $studentUnit = $student->unit;
            }
            else
            {
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELALL;
                $loadParams->loadAward = true;
                $studentUnit = Unit::get_unit_class_id($this->id, $loadParams);
                $studentUnit->load_student_information($student->id, $qualID, $loadParams);
                $student->unit = $studentUnit;
                
                //then we want to save the object to the session
                //but we also just want to sstudent load on this object (as each time it will
                //clear it down)
            }
            
            
            $studentsSessionArray[$student->id] = $student;
            $extraClass = '';
            if($rowCount == 1)
            {
                $extraClass = 'firstRow';
            }
            elseif($rowCount == count($studentsArray))
            {
                $extraClass = 'lastRow';
            }
            
            $rowVal .= "<td style='width:40px;min-width:40px;'>";            
            
                // Unit Comment
                $comments = $studentUnit->get_comments();

                $rowVal .= "<div class='criteriaTDContent'>";
                
                    $rowVal .= " <img src='{$CFG->wwwroot}/blocks/bcgt/pix/info.png' height='12' width='12' class='uNToolTipInfo hand' unitID='{$this->id}' /><div class='unitInfoContent' title='{$this->get_display_name()}'>{$this->build_unit_details_table()}</div><br><br>";
                    //$rowVal .= "<a href='{$CFG->wwwroot}/blocks/bcgt/grids/student_grid.php?qID={$qualID}&sID={$student->id}' target='_blank' title='View Student Grid'><img src='".$OUTPUT->pix_url('i/calendar', 'core')."' /></a><br>";

                $rowVal .= "</div>";

                $rowVal .= "<div class='hiddenCriteriaCommentButton'>";

                    $username = $student->username;
                    $fullname = fullname($student);
                    $unitname = bcgt_html($this->name);
                    $critname = "N/A";
                    $cellID = "cmtCell_U_{$this->id}_S_{$student->id}_Q_{$qualID}";
                    
                    if (!empty($comments))
                    {
                        $rowVal .= "<img id='{$cellID}' criteriaid='-1' unitid='{$this->id}' studentid='{$student->id}' qualid='{$qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='student' class='editCommentsUnit' title='Click to Edit Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/comment_edit.png' alt='".get_string('editcomments', 'block_bcgt')."' />";
                    }
                    else
                    {
                        $rowVal .= "<img id='{$cellID}' criteriaid='-1' unitid='{$this->id}' studentid='{$student->id}' qualid='{$qualID}' username='{$username}' fullname='{$fullname}' unitname='{$unitname}' critname='{$critname}' grid='student' class='addCommentsUnit' title='Click to Add Comments'  src='{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtcg/pix/comment_add.png' alt='".get_string('addcomment', 'block_bcgt')."' />";
                    }

                    //$retval .= "<span class='tooltipContent' style='display:none !important;'>".bcgt_html($this->comments, true)."</span>";
                    $rowVal .= "<div class='popUpDiv bcgt_unit_comments_dialog' id='dialog_S{$student->id}_U{$this->id}_Q{$qualID}' qualID='{$qualID}' unitID='{$this->id}' critID='-1' studentID='{$student->id}' grid='student' imgID='{$cellID}' title='Comments'>";
                        $rowVal .= "<span class='commentUserSpan'>Comments for {$fullname} : {$username}</span><br>";
                        $rowVal .= "<span class='commentUnitSpan'>{$this->get_display_name()}</span><br>";
                        $rowVal .= "<span class='commentCriteriaSpan'>N/A</span><br><br><br>";
                        $rowVal .= "<textarea class='dialogCommentText' id='text_S{$student->id}_U{$this->id}_Q{$qualID}'>".bcgt_html($comments)."!</textarea>";
                    $rowVal .= "</div>";


                $rowVal .= "</div>";
            
            $rowVal .= "</td>";
            // End Unit Comment  
            
            
            
            $rowVal .= $this->build_unit_grid_students_details($student, $qualID, $context);

            
            // Qual award
            $qualAward = $this->get_student_qual_award($student->id, $qualID);
            
            $studentQualAward = 'N/A';
            if ($qualAward){
                $studentQualAward = $qualAward->targetgrade;
            }
            
            $rowVal .= "<td style='width:100px;min-width:100px;'>".get_string('predicted', 'block_bcgt') . "<br><b><span style='text-transform:uppercase;' id='qualAward_{$student->id}'>".$studentQualAward."</span></b></td>";

            //work out the students unit award
            $stuUnitAward = $studentUnit->get_user_award();
            $award = '';
            $rank = '';
            if($stuUnitAward)
            {
                $rank = $stuUnitAward->get_rank();
                $award = $stuUnitAward->get_award();
            }
            if($editing)
            {
                $rowVal .= "<td id='unitAwardCell_{$student->id}_{$qualID}'  style='width:100px;min-width:100px;'>".$this->get_unit_award_edit($student, $qualID, 
                        $this->get_typeID(), $rank, $award, $unitAwards)."</td>";
            }
            else
            {
                //print out the unit award column
//                $retval .= "<td id='unitAward_".$student->id."' class='unitAward r".$student->id." rank$rank'><span id='unitAward_$student->id'>".$award."</span></td>";
                $rowVal .= "<td id='unitAwardCell_{$student->id}_{$qualID}' style='width:100px;min-width:100px;'><span id='unitAwardAdv_{$student->id}_{$qualID}'>".$award."</span></td>";
                
            }	
            
            // Percent
            if ($this->has_percentage_completions()){
                $rowVal .= "<td style='width:110px;min-width:110px;'><div class='tdPercentCompleted'>".$studentUnit->display_percentage_completed()."</div></td>";
            }
            
            

            if($criteriaNames)
            {
                foreach($criteriaNames AS $criteriaName)
                {	
                    if($studentCriteria = $studentUnit->get_single_criteria(-1, $criteriaName))
                    {
//                        $row = $this->set_up_criteria_grid($studentCriteria, '', $student, 
//                                $possibleValues, $editing, $advancedMode, '', $row, $qualID);
                        $c = ($editing) ? 'Edit' : 'NonEdit';
                        $width = ($editing) ? 100 : 40;
                        $rowVal .= "<td style='width:{$width}px;min-width:{$width}px;' class='criteriaCell criteriaValue{$c}' qualID='{$qualID}' criteriaID='{$studentCriteria->get_id()}' studentID='{$student->id}' unitID='{$this->id}' >".$studentCriteria->get_grid_td($editing, $advancedMode, $this, $student, null, 'unit')."</td>";
                        
                    }//end if the criteria found
                    else
                    {
                        $rowVal .= "<td class='criteriaCell' style='width:{$width}px;min-width:{$width}px;'></td>";
                    }
                }//end for each criteria Name
            }//end if criteriaNames
            
            $rowVal .= "</tr>";
            $output .= $rowVal;
            
        }//end for each student
        $qualArray[$qualID] = $studentsSessionArray;
        $unitObject->qualArray = $qualArray;
        $unitObject->unit = $this;
        $sessionUnits[$this->id] = $unitObject;
        $_SESSION['session_unit'] = urlencode(serialize($sessionUnits));

//                // Grid logs
//                $studentArray = array();
//                foreach($students as $student)
//                {
//                    $studentArray[] = $student->id;
//                }
//                $qualArray = array();
//                foreach($qualIDs as $qualID)
//                {
//                    $qualArray[] = $qualID;
//                }
//                
//                if($studentArray && $qualArray){
//                    $retval .= $this->show_logs($studentArray, $qualArray);
//                }
//                
                
		return $output;	
    }
    
    
    protected function get_unit_award_edit($student, $qualID, $typeID, $rank, $award, $unitAwards)
	{
		$retval = "";
		$retval .= "<select class='unitAward' id='unitAwardEdit_{$student->id}_{$qualID}' name='unitAwardAPL'  unitid='{$this->id}' qualid='{$qualID}' studentid='{$student->id}'>";
		$retval .= "<option value='-1'></option>";
		if($unitAwards)
		{
			foreach($unitAwards AS $possAward)
			{
                                
				$selected = '';
				if($possAward->award == $award)
				{
					$selected = 'selected';
				}
				$retval .= "<option $selected value='$possAward->id'>$possAward->award</option>";
                
                if ($this->grading == 'P') break;
                              
			}
		}
		$retval .= "</select></span>";
		return $retval;
	}
    
    
     protected function build_unit_grid_students_details($student, $qualID, $context)
	{
		global $CFG, $printGrid, $OUTPUT;
        
        $output = "";
		   
        //columns supported are:
        //picture,username,name,firstname,lastname,email
        $columns = $this->defaultColumns;
        $configColumns = get_config('bcgt','cggridcolumns');
        //need to get the global config record
        
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
        }
        foreach($columns AS $column)
        {
            $style = ($column == 'picture') ? "style='width:50px;min-width:50px;'" : "style='width:100px;min-width:100px;'";
            $content = '<td '.$style.'>';
            
            if ($column == 'username' || $column == 'name'){
                $content .= "<a href='{$CFG->wwwroot}/blocks/bcgt/grids/student_grid.php?sID={$student->id}&qID={$qualID}' target='_blank'>";
            }
            
            switch(trim($column))
            {
                case("picture"):
                    $content .= $OUTPUT->user_picture($student, array('size' => 25, 'link' => false));
                    break;
                case("username"):
                    $content .= $student->username;
                    break;
                case("name"):
                    $content .= fullname($student);
                    break;
                case("firstname"):
                    $content .= $student->firstname;
                    break;
                case("lastname"):
                    $content .= $student->lastname;
                    break;
                case("email"):
                    $content .= $student->email;
                    break;
            }
            
            if ($column == 'username' || $column == 'name'){
                $content .= "</a>";
            }
            
            $content .= '</td>';
            $output .= $content;
        }
		        
		return $output;	
	}
    
    
    
     /**
     * 
     * @global type $printGrid
     * @param type $criteriaNames
     * @param type $advancedMode
     * @param type $editing
     * @param type $subCriteriaDisplay
     * @return \stdClass
     */
    protected function get_unit_grid_header($criteriaNames, $grid, $context, $qualID = null)
	{
        $editing = false;
        $advancedMode = false;
        if($grid == 'es' || $grid == 'ea')
        {
            $editing = true;
        }
        if($grid == 'a' || $grid = 'ea')
        {
            $advancedMode = true;
        }
        global $printGrid;
		$headerObj = new stdClass();
		$header = '';
		$header .= "<thead>";
		
		$header .= "<tr class='mainRow'>";
                
		//denotes projects
        if($advancedMode && $editing)
        {
            $header .= "<th class='unitComment' style='width:40px;min-width:40px;'></th>";
        }
        else
        {
            $header .= "<th style='width:40px;min-width:40px;'></th>";
        }
        //columns supported are:
        //picture,username,name,firstname,lastname,email
        $columns = $this->defaultColumns;
        //need to get the global config record
        
        $configColumns = get_config('bcgt','cggridcolumns');
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
        }
        foreach($columns AS $column)
        {
            $style = ($column == 'picture') ? "style='width:50px;min-width:50px;'" : "style='width:100px;min-width:100px;'";
            $header .="<th {$style}>";
            $header .= get_string(trim($column), 'block_bcgt');
            $header .="</th>";
        }

        $header .= "<th style='width:100px;min-width:100px;'>".get_string('qualaward', 'block_bcgt')."</th>";
        $header .= "<th style='width:100px;min-width:100px;'>".get_string('unitaward', 'block_bcgt')."</th>";

        $totalHeaderCount = 7;
        // If unit has % completions enabled
        if($this->has_percentage_completions() && !$printGrid){
            $header .= "<th style='width:110px;min-width:110px;'>% Complete</th>";
            $totalHeaderCount++;
        }
		
        //if we are doing it by project
        //then order the projects by due date
        //for each projects        
                
		$criteriaCountArray = array();
		if($criteriaNames)
		{
			//loop over each criteria and create a header
			//have a spacer between P, M and D
			foreach($criteriaNames AS $criteriaName)
			{
                $header .= "<th class='criteriaName c$criteriaName'><span class='criteriaName'";
                $header .= ">$criteriaName</span></th>";
                $totalHeaderCount++;

            }
        }
		$header .= "</tr></thead>";
		
		$headerObj->header = $header;
		$headerObj->criteriaCountArray = $criteriaCountArray;
		//$headerObj->orderedCriteriaNames = $criteriaNames;
        $headerObj->totalHeaderCount = $totalHeaderCount;
                
		return $headerObj;
	}
    
    /**
     * Find a criteria on this unit, by it's name and return criteria object
     * @global type $CFG
     * @param type $name
     * @return boolean
     */
    function find_criteria_by_name($name)
    {

        global $DB;
        
        $check = $DB->get_record("block_bcgt_criteria", array("bcgtunitid" => $this->id, "name" => $name));

        if (!$check) return false;

        // If it's parentid is null, return it frmo the main array, else return it frmo the parent
        // We're assuming 1 level of sub criteria here, maybe change this in the future if we need more?
        if(!is_null($check->parentcriteriaid))
        {
            $parent = $this->criterias[$check->parentcriteriaid];
            $parentSub = $parent->get_sub_criteria();
            return $parentSub[$check->id];
        }
        
        return $this->criterias[$check->id];

    }
    
    public function has_percentage_completions()
    {
        return $this->usePercentageBar;
    }
    
    /**
     * displays the unit grid. 
     */
    public function display_unit_grid()
    {
        
        global $COURSE, $PAGE, $CFG, $USER;
        $courseID = optional_param('cID', -1, PARAM_INT);
        $context = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }
        
        $qualID = optional_param('qID', -1, PARAM_INT);
        if(has_capability('block/bcgt:viewallgrids', context_system::instance()))
        {
            $quals = $this->get_quals_on('',-1,-1,$courseID);
        }
        else
        {
            $quals = $this->get_quals_on_roles('', $USER->id, array('teacher', 'editingteacher'),$courseID);
        }
                
        if($qualID == -1 && $quals && count($quals == 1))
        {
            $qualID = end($quals)->id;
        }        
        $late = optional_param('late', false, PARAM_BOOL);
        $grid = optional_param('g', 's', PARAM_TEXT);
        $sCourseID = optional_param('scID', -1, PARAM_INT);
        $groupingID = optional_param('grID', -1, PARAM_INT);
        $regGroupID = optional_param('regGrpID', false, PARAM_INT);
        
        $editing = (has_capability('block/bcgt:editunitgrid', $context) && in_array($grid, array('se', 'ae'))) ? true : false;
        $advancedMode = ($grid == 'a' || $grid == 'ae') ? true : false;
                   
        $cols = 1;
        if ($this->has_percentage_completions()){
            $cols++;
        }
        
        $columns = $this->defaultColumns;
        $configColumns = get_config('bcgt','cggridcolumns');
        if($configColumns)
        {
            $columns = explode(",", $configColumns);
        }
        
        $cols += count($columns);
        $cols += 2; // Awards
        
        
        
//        //we need to work out how many columns are being locked and
//        //what the widths are
//        //default is columns (assignments, comments, unitaward)
//        $columnsLocked = 3;
//        $configColumns = get_config('bcgt','btecgridcolumns');
//        if($configColumns)
//        {
//            $columns = explode(",",$configColumns);
//            $columnsLocked += count($columns);
//        }
//        else
//        {
//            $columnsLocked += count($this->defaultColumns);
//        }
//        $configColumnWidth = get_config('bcgt','bteclockedcolumnswidth');
        $jsModule = array(
            'name'     => 'mod_bcgtcg',
            'fullpath' => '/blocks/bcgt/plugins/bcgtcg/js/bcgtcg.js',
            'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
        );
        //
        
        $PAGE->requires->js_init_call('M.mod_bcgtcg.initunitgrid', array($qualID, $this->id, $grid, $cols), true, $jsModule);
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        
        $retval = '';
        
        $retval .= load_javascript(true);
        $retval .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/css/start/jquery-ui-1.10.3.custom.min.css' />";
        
        
        $retval .= '<div>';
        
        $retval .= '<input type="hidden" id="grid" name="g" value="'.$grid.'"/>';   
        $retval .= "<input type='hidden' id='reggrpid' value='{$regGroupID}' />";
                
        $retval .= "<div class='c'>";

            $retval .= "<input type='button' id='viewsimple' class='btn' value='View Simple' />";
            $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
            $retval .= "<input type='button' id='viewadvanced' class='btn' value='View Advanced' />";                

            $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
            $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
            $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
            
            $retval .= "<input type='button' id='editsimple' class='btn' value='Edit Simple' />";
            $retval .= "&nbsp;&nbsp;&nbsp;&nbsp;";
            $retval .= "<input type='button' id='editadvanced' class='btn' value='Edit Advanced' />";                

            $retval .= "<br><br>";
            $retval .= "<a href='#' onclick='toggleAddComments();return false;'><input id='toggleCommentsButton' type='button' class='btn' value='".get_string('addcomment', 'block_bcgt')."' disabled='disabled' /></a>";

            
            
            $page = optional_param('page', 1, PARAM_INT);
            $pageRecords = get_config('bcgt','pagingnumber');
            if($pageRecords != 0)
            {
                //then we are paging
                //need to count the total number of students and divide by the paging number
                //load the session object
                $studentsLoaded = false;
                if($qualID != -1)
                {
                    $sessionUnits = isset($_SESSION['session_unit'])? 
                    unserialize(urldecode($_SESSION['session_unit'])) : array();
                                        
                    if(array_key_exists($this->id, $sessionUnits))
                    {
                        $unitObject = $sessionUnits[$this->id];
                        $qualArray = array();
                        if(isset($unitObject->qualArray))
                        {
                            $qualArray = $unitObject->qualArray;
                        }
                        if(array_key_exists($qualID, $qualArray))
                        {
                            //what happens if a student has been added since?

                            //then this will return an array of students unit objects
                            //for this qualid for this unit.
                            $studentsArray = $qualArray[$qualID];
                            if(count($studentsArray) != 0)
                            {
                                $studentsLoaded = true;
                            }
                            //studentsArray[] is an object with two properties. The Unit Object with stu
                            //loaded and a few of the students information.
                        }    
                    }
                    else
                    {
                        $unitObject = new stdClass();
                        $qualArray = array();
                    }
                }
                elseif(isset($this->students))
                {
                    $studentsArray = $this->students;
                    $studentsLoaded = true;
                }
                if(!$studentsLoaded)
                {   
                    //load the students that are on this unit for this qual. 
                    if ($regGroupID){
                        $studentsArray = bcgt_get_register_group_users($regGroupID);
                    } else {
                        $studentsArray = get_users_on_unit_qual($this->id, $qualID, $sCourseID, $groupingID);
                    }
                    $this->students = $studentsArray;
                }
                $totalNoStudents = count($studentsArray);
                $noPages = ceil($totalNoStudents/$pageRecords);
                $retval .= '<div class="bcgt_pagination">'.get_string('pagenumber', 'block_bcgt').' : ';

                    for ($i = 1; $i <= $noPages; $i++)
                    {
                        $class = ($i == 1) ? 'active' : '';
                        $retval .= "<a class='unitgridpage pageNumber {$class}' page='{$i}' href='#&page={$i}'>{$i}</a>";
                    }

                $retval .= '</div>';
            }
            $retval .= '<input type="hidden" name="pageInput" id="pageInput" value="'.$page.'"/>';
            
            
            
            
            
        $retval .= "</div>";
        
        $retval .= CGQualification::get_grid_key();
        
        $retval .= "<br><br>";
        $retval .= "<p id='loading' class='c'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/ajax-loader.gif' alt='loading...' /></p>";
       
                       
        //the grid -> ajax
        $retval .= '<div>';
        
        $retval .= "<div id='CGUnitGrid' class='unitGridDiv ".
        $grid."UnitGrid tableDiv'>";
        
        $retval .= "<table align='center' class='unit_grid".
                $grid."FixedTables' id='CGUnitGridTable'>";
        $criteriaNames = $this->get_used_criteria_names();
		
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
        $criteriaSorter = new CGCriteriaSorter();
		usort($criteriaNames, array($criteriaSorter, "ComparisonSimple"));
               
		$headerObj = $this->get_unit_grid_header($criteriaNames, $grid, $context);
		$criteriaCountArray = $headerObj->criteriaCountArray;
        $totalHeaderCount = $headerObj->totalHeaderCount;
        $this->criteriaCount = $criteriaCountArray;
		$header = $headerObj->header;	
        //$totalCellCount = $headerObj->totalCellCount;
//		if($subCriteria)
//		{
//			$subCriteriaNo = $headerObj->subCriteriaNo;
//		}
		$retval .= $header;
		
		$retval .= "<tbody>";
        
        $grid = $this->get_unit_grid_data($qualID, $advancedMode, $editing, $courseID);
        $retval .= $grid;
        
        $retval .= "</tbody>";
        $retval .= "<tfoot></tfoot>";
        $retval .= "</table>";
        
        $retval .= "</div>";
        $retval .= '</div>';
        $retval .= '</div>';
        //Edit/Advanced etc options
    
        //four buttons. On click it needs to resubmit the table draw. 
        //and it needs to potentially redraw the key? 
        //Grid with a key

        
        
        //the buttons.
        return $retval;
        
    }
    
    
    public function get_student_qual_award($userID, $qualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_award} useraward 
            JOIN {block_bcgt_target_breakdown} breakdown ON breakdown.id = useraward.bcgtbreakdownid 
            WHERE useraward.type = ? AND useraward.userid = ? AND useraward.bcgtqualificationid = ?";
        return $DB->get_record_sql($sql, array('Predicted', $userID, $qualID));
    }
    
    
     /**
	 * Gets the used criteria names from this unit. 
	 * @return multitype:
	 */
	public function get_used_criteria_names(&$criteria = false, &$array = false)
	{
        global $CFG;
     
        if ($criteria && $array)
        {
            foreach($criteria as $criterion)
            {
                $array[] = $criterion->get_name();
                if ($criterion->get_sub_criteria())
                {
                    $sub = $criterion->get_sub_criteria();
                    $this->get_used_criteria_names($sub, $array);
                }
            }
            
            return;
        }
        
        
        
		$usedCriteriaNames = array();
        
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
        $criteriaSorter = new CGCriteriaSorter();
        uasort($this->criterias, array($criteriaSorter, "Comparison"));
        
		if($this->criterias)
		{
			foreach($this->criterias AS $criteria)
			{
				$usedCriteriaNames[] = $criteria->get_name();
                if ($criteria->get_sub_criteria())
                {
                    $sub = $criteria->get_sub_criteria();
                    $this->get_used_criteria_names($sub, $usedCriteriaNames);
                }
			}
		}
        
        $usedCriteriaNames = array_unique($usedCriteriaNames);
        
		return $usedCriteriaNames;
	}

    
    
    public function print_grid($qualID)
    {
        
        global $CFG, $COURSE, $printGrid;
        $printGrid = true;
        $context = context_course::instance($COURSE->id);
        $courseID = optional_param('cID', -1, PARAM_INT);
        if($courseID != -1)
        {
            $context = context_course::instance($courseID);
        }

        echo "<!doctype html><html><head>";
        echo "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/print.css'>";
        echo load_javascript(false, true);
        
        $logo = get_config('bcgt', 'logoimgurl');
        
        echo "</head><body style='background: url(\"{$logo}\") no-repeat;'>";
                
        echo "<div class='c'>";
            echo "<h1>{$this->get_display_name()}</h1>";

            echo "<br><br>";
            
            // Key
            echo "<div id='key'>";
                echo CGQualification::get_grid_key();
            echo "</div>";
            
            
            
            echo "<br><br>";
            
            echo "<table id='printGridTable'>";
            
                $criteriaNames = $this->get_used_criteria_names();
                $headerObj = $this->get_unit_grid_header($criteriaNames, 's', $context);
                
                echo $headerObj->header;
                
                $studentsArray = get_users_on_unit_qual($this->id, $qualID);
            
                if ($studentsArray)
                {

                    foreach($studentsArray as $student)
                    {

                        if(isset($student->unit))
                        {
                            //then we are coming from the session and the unit object has aleady
                            //been loaded
                            $studentUnit = $student->unit;
                        }
                        else
                        {
                            $loadParams = new stdClass();
                            $loadParams->loadLevel = Qualification::LOADLEVELALL;
                            $loadParams->loadAward = true;
                            $studentUnit = Unit::get_unit_class_id($this->id, $loadParams);
                            $studentUnit->load_student_information($student->id, $qualID, $loadParams);
                            $student->unit = $studentUnit;

                            //then we want to save the object to the session
                            //but we also just want to sstudent load on this object (as each time it will
                            //clear it down)
                        }

                        // Units & Grades
                        if($studentUnit->is_student_doing())
                        {	

                            echo "<tr>";

                            echo "<td></td>";

                            $row = $this->build_unit_grid_students_details($student, $qualID, 
                                    array(), $context);

                            echo $row;

                            
                            // Qual award
                            $qualAward = $this->get_student_qual_award($student->id, $qualID);

                            $studentQualAward = 'N/A';
                            if ($qualAward){
                                $studentQualAward = $qualAward->targetgrade;
                            }

                            echo "<td>".$studentQualAward."</td>";


                            //work out the students unit award
                            $stuUnitAward = $studentUnit->get_user_award();
                            $award = '';
                            $rank = '';
                            if($stuUnitAward)
                            {
                                $rank = $stuUnitAward->get_rank();
                                $award = $stuUnitAward->get_award();
                            }

                            echo "<td><span id='unitAwardAdv_$student->id'>".$award."</span></td>";
                            
                            
                            if($criteriaNames)
                            {

                                foreach($criteriaNames AS $criteriaName)
                                {	
                                    
                                    if($studentCriteria = $studentUnit->get_single_criteria(-1, $criteriaName))
                                    {
                                        echo "<td>".$studentCriteria->get_grid_td(false, false, $this, $student, null, 'unit')."</td>";
                                    }
                                    
                                }//end for each criteria Name
                            }//end if criteriaNames


                            echo "</tr>";

                        }

                    }

                }
                
                
            
            
            echo "</table>";
            echo "</div>";
            
            //echo "<br class='page_break'>";
            
            // Comments and stuff
            // TODO at some point
            
            echo "<script> $('a').contents().unwrap(); $('.studentUnitInfo').remove(); </script>";
            
        echo "</body></html>";
        
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
        
        return new CGUnit($unitID, $params, $loadLevel);
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
        return new CGUnit($unitID, $params, $loadParams);
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
                                $metArray = $studentCriterion->get_met_values();  
                                $nonMetArray = $studentCriterion->get_non_met_values();   

                                $possibleValuesArray = array('N/A');
                                if ($metArray){
                                    foreach($metArray as $value){
                                        $possibleValuesArray[] = $value->shortvalue;
                                    }
                                }

                                if ($nonMetArray){
                                    foreach($nonMetArray as $value){
                                        $possibleValuesArray[] = $value->shortvalue;
                                    }
                                }
                                
                                
                                
                                $shortValue = 'N/A';
                                $studentValueObj = $studentCriterion->get_student_value();	
                                if ($studentValueObj){
                                    $shortValue = $studentValueObj->get_short_value();
                                    if($studentValueObj->get_custom_short_value())
                                    {
                                        $shortValue = $studentValueObj->get_custom_short_value();
                                    }
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
                
        $now = time();
                
        $output = "";
        
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
                            $metArray = $studentCriterion->get_met_values();  
                            $nonMetArray = $studentCriterion->get_non_met_values();   

                            $possibleValuesArray = array();
                            $possibleValuesArray[-1] = 'N/A';
                            if ($metArray){
                                foreach($metArray as $val){
                                    $possibleValuesArray[$val->id] = $val->shortvalue;
                                }
                            }

                            if ($nonMetArray){
                                foreach($nonMetArray as $val){
                                    $possibleValuesArray[$val->id] = $val->shortvalue;
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
        
        
        return $output;
        
    }
    
    
    public function display_percentage_completed() {
        
        if (!$this->usePercentageBar){
            return '-';
        }
        
        return parent::display_percentage_completed();
        
    }
    
    
    
    
}
