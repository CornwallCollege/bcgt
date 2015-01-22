<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EntryQual
 *
 * @author mchaney
 */
class TargetQualWeighting {
    //put your code here
    protected $bcgttargetqualid;
    protected $coefficient;
    protected $percentage;
    protected $number;
    
    //import options
    protected $insertmissingqual;
    
    protected $success;
    protected $summary;
    
    protected $weightingPercentage = array(100, 90, 75, 60, 40, 25, 10, 0);
    
    //| seperated list of levels that will be supported. 
    CONST BCGT_WEIGHTINGS_LEVELS = "3";
    CONST BCGT_WEIGHTING_CONSTANT_ATT_NAME = "ALPS_WEIGHTING_CONSTANT";
    CONST BCGT_WEIGHTING_MULTIPLIER_ATT_NAME = "ALPS_MULTIPLIER_CONSTANT";
    
    public function TargetQualWeighting($id = -1, $params = null)
    {
        $this->id = $id;
        if($id != -1)
        {
            if($params)
            {
                $this->extract_params($params);
            }
            else
            {
                $this->load_weighting($id);
            }
        }
        elseif($params)
        {
            $this->extract_params($params);
        }
    }

    public function get_alps_temperature($targetQualID, $coefficientScore)
    {
        $coefficient = null;
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_tqual_weight} WHERE bcgttargetqualid = ? 
            AND coefficient < ? ORDER BY coefficient ASC";
        $records = $DB->get_records_sql($sql, array($targetQualID, $coefficientScore));
        if($records)
        {
            //this will come back as: all of the coefficents that are less than the score
            //in ASC order. So the end record is the highest that is smaller
            $coefficient = end($records);
        }
        return $coefficient;
    }
    
    public function get_headers()
    {
        return array("QualFamily", "QualLevel", "QualSubtype", "Percentage", "Coefficient", "Number");
    }
    
    public function get_examples()
    {
        
    }
    
    public function get_description()
    {
        return get_string('wqdesc', 'block_bcgt');
    }
    
    public function get_file_names()
    {
        return 'qualweightings.csv';
    }
    
    public function has_multiple()
    {
        return false;
    }
    
    /**
     * Saves the Weighting into the database. 
     * It either updates or inserts. 
     */
    public function save($checkExists = false)
    {
        if($checkExists && $record = $this->get_weighting_by_number())
        {
            $this->id = $record->id;
        }
        //What happens if these are already in the database but we didnt have an id. 
        //this needs to replace them!
        if($this->id != -1)
        {
            $this->update_weighting();
        }
        else
        {
            $this->insert_weighting();
        }
    }
    
    
    public function display_import_options()
    {
        $retval = '<table>';
        $retval .= '<tr><td><label for="option1">'.get_string('qwcreatemissingqual', 'block_bcgt').' : </label></td>';
        $retval .= '<td><input type="checkbox" checked="checked" name="option1"/></td>';
        $retval .= '<td><span class="description">('.get_string('qwcreatemissingqualdesc', 'block_bcgt').')</span></td></tr>';
        $retval .= '</table>';
//        $retval .= '<label for="">'.get_string('plcreatemissinguser', 'block_bcgt').' : </label>';
//        $retval .= '<input type="checkbox" name="option1"/>';
//        $retval .= '<span class="description">('.get_string('plcreatemissinguserdesc', 'block_bcgt').')</span><br />';
        return $retval;
    }
    
    public function get_submitted_import_options()
    {
        if(isset($_POST['option1']))
        {
            $this->insertmissingqual = true;
        }
    }
    
    public function was_success()
    {
        return $this->success;
    }
    
    public function display_summary()
    {
        $retval = '<p><ul>';
        $retval .= '<li>'.get_string('qwimportsum1','block_bcgt').' : '.$this->summary->successCount.'</li>';
        if(!$this->success)
        {
            $retval .= '<li>'.get_string('qwimportsum3','block_bcgt').' : '.count($this->summary->qualsInserted).'</li>';
            $retval .= '<li>'.get_string('tgimportsum4','block_bcgt').' : '.count($this->summary->qualsNotFound).'</li>'; 
            $retval .= '<li>'.get_string('qwimportsum2','block_bcgt').' : '.count($this->summary->qualCouldntInsert).'</li>';     
        }
        $retval .= '</ul></p>';
        return $retval;
    }
    
    /**
     * QualFamily|QualLevel|QualSubtype|QualName|QualID(optional)|percentage|coefficient|number
     * @param type $csvFile
     */
    public function process_import_csv($csvFile, $process = false)
    {
//        $qualsNotFound = array();
//        $successCount = 0;
//        $count = 1;
//        $qualsAltered = array();
//        $qualCouldntInsert = array();
//        $qualsInserted = array();
//        $CSV = fopen($csvFile, 'r');
//        while(($qualWeighting = fgetcsv($CSV)) !== false) {
//            if($count != 1)
//            {
//                //$qualID = (isset($qualWeighting[4]) ? $qualWeighting[4] : -1);
//                $quals = Qualification::retrieve_qual(-1, -1, $qualWeighting[0], -1, '', -1, 
//                        $qualWeighting[1], -1, $qualWeighting[2], $qualWeighting[3]);
//                if($quals && count($quals)==1)
//                {
//                    $qual = end($quals);
//                    $qualID = $qual->id;
//                    $this->new_qual_weighting($qualID, $qualWeighting[5], $qualWeighting[6], $qualWeighting[7]);
//                    $successCount++;
//                    $qualsAltered[$qualID] = $qualID;
//                } 
//                elseif($this->insertmissingqual)
//                {
//                    $qualsNotFound[] = $qualWeighting[0].' '.$qualWeighting[1].' '.$qualWeighting[2].' '.$qualWeighting[3];
//                    //this then needs to insert the qualweighting!
//                    //need to get the familyid or type id. 
//                    $row = array($qualWeighting[0], $qualWeighting[1], $qualWeighting[2], $qualWeighting[3]);
//                    $newQualID = Qualification::insert_from_csv($row);
//                    if($newQualID)
//                    {
//                        $qualsInserted[] = $qualWeighting[0]." ".$qualWeighting[1]." ".$qualWeighting[2]." ".$qualWeighting[3];
//                        $this->new_qual_weighting($newQualID, $qualWeighting[5], $qualWeighting[6], $qualWeighting[7]);
//                        $successCount++;
//                        $qualsAltered[$newQualID] = $newQualID;
//                    }
//                    else
//                    {
//                        $qualCouldntInsert[$qualWeighting[0]." ".$qualWeighting[1]." ".$qualWeighting[2]." ".$qualWeighting[3]] = $qualWeighting[0]." ".$qualWeighting[1]." ".$qualWeighting[2]." ".$qualWeighting[3];
//                    }
//                    
//                }
//                else {
//                    $qualsNotFound[] = $qualWeighting[0].' '.$qualWeighting[1].' '.$qualWeighting[2].' '.$qualWeighting[3];
//                }
//            }
//            $count++;
//        }  
//        fclose($CSV);
//        
//        if($process)
//        {
//            $userCourseTargets = new UserCourseTarget();
//            $justCalculateWeightedTargets = true;
//            $calculateNewAverageGCSEScore = false;
//            $userCourseTargets->calculate_quals_target_grades($qualsAltered, $calculateNewAverageGCSEScore, $justCalculateWeightedTargets);
//            //then recalculate the target grades for these weightings, just the quals. 
//        }
//        $success = true;
//        if((!$this->insertmissingqual && count($qualsNotFound) > 0) || count($qualCouldntInsert) > 0)
//        {
//            $success = false;
//        }
//        $summary = new stdClass();
//        $summary->successCount = $successCount;
//        $summary->qualsNotFound = $qualsNotFound;
//        $summary->qualCouldntInsert = $qualCouldntInsert;
//        $summary->qualsInserted = $qualsInserted;
//        $this->summary = $summary;
//        $this->success = $success;
    }
    
    private function new_qual_weighting($qualID, $pecentage, $coefficient, $number)
    {
        $params = new stdClass();
        $params->bcgttargetqualid = $qualID;
        $params->percentage = $pecentage;
        $params->coefficient = $coefficient;
        $params->number = $number;
        $weighting = new TargetQualWeighting(-1, $params);
        $weighting->save(true);
    }
    
    public function get_coefficients_for_targetqual_summary($targetQualID)
    {
        $retval = array();
        $stdObj = new stdClass();
        $coefficients = $this->get_all_coefficients_for_targetqual($targetQualID);
        $stdObj->qual = bcgt_get_target_qual_id($targetQualID);
        $stdObj->coefficients = $coefficients;
        $retval[] = $stdObj; 
        return $retval;
    }
    
    public function get_all_coefficients_for_targetqual($targetQualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_tqual_weight} WHERE bcgttargetqualid = ? ORDER BY number ASC";
        return $DB->get_records_sql($sql, array($targetQualID));
    }
    
    public function get_coefficient_for_targetqual($targetQualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_tqual_weight} WHERE bcgttargetqualid = ? AND 
            (attribute IS NOT NULL OR attribute = ?)";
        $record = $DB->get_record_sql($sql, array($targetQualID, ''));
        if(!$record)
        {
            $defaultAplsPercentage = get_config('bcgt', 'defaultalpsperc');
            if($defaultAplsPercentage)
            {
                $sql = "SELECT * FROM {block_bcgt_tqual_weight} WHERE bcgttargetqualid = ? AND 
                percentage = ?";
                $record = $DB->get_record_sql($sql, array($targetQualID,$defaultAplsPercentage));
                if($record)
                {
                    return $record->coefficient;
                }
                
            }
        }
        else
        {
            return $record->coefficient;
        }
        return false;
    }
    
    /**
     * Gets the params from the object and passes it bak as a new object. 
     * @return \stdClass
     */
    private function get_params()
    {        
        $params = new stdClass();
        $params->bcgttargetqualid = $this->bcgttargetqualid;
        $params->coefficient = $this->coefficient;
        $params->percentage = $this->percentage;
        $params->number = $this->number;
        return $params;
    }
    
    /**
     * Inserts the target grade into the database. 
     * @global type $DB
     */
    private function insert_weighting()
    {
        global $DB;
        $params = $this->get_params();
        $this->id = $DB->insert_record('block_bcgt_tqual_weight', $params);
    }
    
    private function update_weighting()
    {
        global $DB;
        $params = $this->get_params();
        $params->id = $this->id;
        $DB->update_record('block_bcgt_tqual_weight', $params);
    }
    
    public function get_precentage_from_number($number)
    {
        return isset($this->weightingPercentage[($number - 1)]) ? $this->weightingPercentage[($number - 1)] : 0;
    }
    
    public function get_multiplier($targetQualID)
    {
        $record = $this->retrieve_multiplier($targetQualID);
        if($record)
        {
            return $record->value;
        }
        return 100;
    }
        
    protected function retrieve_multiplier($targetQualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_target_qual_att} WHERE name = ? AND bcgttargetqualid = ?";
        $record = $DB->get_record_sql($sql, array(QualWeighting::BCGT_WEIGHTING_MULTIPLIER_ATT_NAME, $targetQualID));
        return $record;
    }
    
    /**
     * Gets the params from the object passed in and puts them onto 
     * the target grade objectl. 
     * @param type $params
     */
    private function extract_params($params)
    {                
        $this->bcgttargetqualid = $params->bcgttargetqualid;
        $this->coefficient = $params->coefficient;
        if(isset($params->percentage))
        {
            $this->percentage = $params->percentage;
        }
        else
        {
            $percentage = $this->get_precentage_from_number($params->number);
            $this->percentage = $percentage;
        }
        $this->number = $params->number;
    }
    
    /**
     * gets the target grade from the database and loads onto the obj
     * @global type $DB
     * @param type $id
     */
    private function load_weighting($id)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_tqual_weight} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($id));
        if($record)
        {
            $this->extract_params($record);
        }
    }
    
    private function get_weighting_by_number()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_tqual_weight} WHERE bcgttargetqualid = ? AND number = ?";
        return $DB->get_record_sql($sql, array($this->bcgttargetqualid, $this->number));
    }
}

?>
