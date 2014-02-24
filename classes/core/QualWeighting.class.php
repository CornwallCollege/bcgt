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
class QualWeighting {
    //put your code here
    protected $bcgtqualificationid;
    protected $coefficient;
    protected $percentage;
    protected $number;
    
    //import options
    protected $insertmissingqual;
    
    protected $success;
    protected $summary;
    
    public function QualWeighting($id = -1, $params = null)
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
    
    public function get_headers()
    {
        return array("QualFamily", "QualLevel", "QualSubtype", "QualName", "QualID", "Percentage", "Coefficient", "Number");
    }
    
    public function get_examples()
    {
        return "ALevel,Level 3,AS Level,Economics,1,100,1.45,1<br />".
                "ALevel,Level 3,AS Level,Economics,1,90,1.23,2<br />".
                "ALevel,Level 3,AS Level,Economics,1,75,1.03,3<br />".
                "ALevel,Level 3,AS Level,Economics,1,60,0.99,4<br />".
                "ALevel,Level 3,AS Level,Economics,1,0,0.23,8<br />".
                "ALevel,Level 3,AS Level,Economics,1,0,0,9<br />".
                "ALevel,Level 3,A2 Level,Maths,1,100,1.45,1<br />".
                "BTEC,Level 4,HNC,Art & Desc,,100,1.23,1<br />";
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
    
    /**
     * Deletes the target grade using the target grade id passed in (from the database.)
     * @global type $DB
     * @param type $targetGradeID
     */
    public static function delete_user_prior_learning($weightingID)
    {
        global $DB;
        $DB->delete_records('block_bcgt_qual_weighting', array('id'=>$weightingID));
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
        $qualsNotFound = array();
        $successCount = 0;
        $count = 1;
        $qualsAltered = array();
        $qualCouldntInsert = array();
        $qualsInserted = array();
        $CSV = fopen($csvFile, 'r');
        while(($qualWeighting = fgetcsv($CSV)) !== false) {
            if($count != 1)
            {
                //$qualID = (isset($qualWeighting[4]) ? $qualWeighting[4] : -1);
                $quals = Qualification::retrieve_qual(-1, -1, $qualWeighting[0], -1, '', -1, 
                        $qualWeighting[1], -1, $qualWeighting[2], $qualWeighting[3]);
                if($quals && count($quals)==1)
                {
                    $qual = end($quals);
                    $qualID = $qual->id;
                    $this->new_qual_weighting($qualID, $qualWeighting[5], $qualWeighting[6], $qualWeighting[7]);
                    $successCount++;
                    $qualsAltered[$qualID] = $qualID;
                } 
                elseif($this->insertmissingqual)
                {
                    $qualsNotFound[] = $qualWeighting[0].' '.$qualWeighting[1].' '.$qualWeighting[2].' '.$qualWeighting[3];
                    //this then needs to insert the qualweighting!
                    //need to get the familyid or type id. 
                    $row = array($qualWeighting[0], $qualWeighting[1], $qualWeighting[2], $qualWeighting[3]);
                    $newQualID = Qualification::insert_from_csv($row);
                    if($newQualID)
                    {
                        $qualsInserted[] = $qualWeighting[0]." ".$qualWeighting[1]." ".$qualWeighting[2]." ".$qualWeighting[3];
                        $this->new_qual_weighting($newQualID, $qualWeighting[5], $qualWeighting[6], $qualWeighting[7]);
                        $successCount++;
                        $qualsAltered[$newQualID] = $newQualID;
                    }
                    else
                    {
                        $qualCouldntInsert[$qualWeighting[0]." ".$qualWeighting[1]." ".$qualWeighting[2]." ".$qualWeighting[3]] = $qualWeighting[0]." ".$qualWeighting[1]." ".$qualWeighting[2]." ".$qualWeighting[3];
                    }
                    
                }
                else {
                    $qualsNotFound[] = $qualWeighting[0].' '.$qualWeighting[1].' '.$qualWeighting[2].' '.$qualWeighting[3];
                }
            }
            $count++;
        }  
        fclose($CSV);
        
        if($process)
        {
            $userCourseTargets = new UserCourseTarget();
            $justCalculateWeightedTargets = true;
            $calculateNewAverageGCSEScore = false;
            $userCourseTargets->calculate_quals_target_grades($qualsAltered, $calculateNewAverageGCSEScore, $justCalculateWeightedTargets);
            //then recalculate the target grades for these weightings, just the quals. 
        }
        $success = true;
        if((!$this->insertmissingqual && count($qualsNotFound) > 0) || count($qualCouldntInsert) > 0)
        {
            $success = false;
        }
        $summary = new stdClass();
        $summary->successCount = $successCount;
        $summary->qualsNotFound = $qualsNotFound;
        $summary->qualCouldntInsert = $qualCouldntInsert;
        $summary->qualsInserted = $qualsInserted;
        $this->summary = $summary;
        $this->success = $success;
    }
    
    private function new_qual_weighting($qualID, $pecentage, $coefficient, $number)
    {
        $params = new stdClass();
        $params->bcgtqualificationid = $qualID;
        $params->percentage = $pecentage;
        $params->coefficient = $coefficient;
        $params->number = $number;
        $weighting = new QualWeighting(-1, $params);
        $weighting->save(true);
    }
    
    public function get_coefficient_for_qual($qualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_qual_weighting} WHERE bcgtqualificationid = ? AND 
            (attribute IS NOT NULL OR attribute = ?)";
        $record = $DB->get_record_sql($sql, array($qualID, ''));
        if(!$record)
        {
            $defaultAplsPercentage = get_config('bcgt', 'aleveldefaultalpsperc');
            if($defaultAplsPercentage)
            {
                $sql = "SELECT * FROM {block_bcgt_qual_weighting} WHERE bcgtqualificationid = ? AND 
                percentage = ?";
                $record = $DB->get_record_sql($sql, array($qualID,$defaultAplsPercentage));
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
        $params->bcgtqualificationid = $this->bcgtqualificationid;
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
        $this->id = $DB->insert_record('block_bcgt_qual_weighting', $params);
    }
    
    private function update_weighting()
    {
        global $DB;
        $params = $this->get_params();
        $params->id = $this->id;
        $DB->update_record('block_bcgt_qual_weighting', $params);
    }
    
    /**
     * Gets the params from the object passed in and puts them onto 
     * the target grade objectl. 
     * @param type $params
     */
    private function extract_params($params)
    {                
        $this->bcgtqualificationid = $params->bcgtqualificationid;
        $this->coefficient = $params->coefficient;
        $this->percentage = $params->percentage;
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
        $sql = "SELECT * FROM {block_bcgt_qual_weighting} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($id));
        if($record)
        {
            $this->extract_params($record);
        }
    }
    
    private function get_weighting_by_number()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_qual_weighting} WHERE bcgtqualificationid = ? AND number = ?";
        return $DB->get_record_sql($sql, array($this->bcgtqualificationid, $this->number));
    }
}

?>
