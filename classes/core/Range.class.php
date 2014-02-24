<?php

/**
 * Range
 * 
 * @copyright 2013 Bedford College
 * @package Bedford College Electronic Learning Blue Print (ELBP)
 * @version 1.0
 * @author Conn Warwicker <cwarwicker@bedford.ac.uk> <conn@cmrwarwicker.com>
 * 
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 * 
 */


class Range {
    
    private $id = false;
    private $name;
    private $details;
    private $unitid;
    private $targetdate;
    private $type; # bcgttypeid
    private $chart;
    private $links;
    
    
    
    public function __construct($params) {
        
        global $DB;
        
        if(is_numeric($params))
        {
            $check = $DB->get_record("block_bcgt_range", array("id" => $params));
            
            if ($check)
            {
            
                $this->id = $check->id;
                $this->name = $check->name;
                $this->details = $check->details;
                $this->unitid = $check->bcgtunitid;
                $this->targetdate = $check->targetdate;
                $this->defaulttargetdate = $check->targetdate;
                $this->type = $this->get_type();
                $this->chart = array();
                $this->links = array();

                // Get chart
                $check = $DB->get_records("block_bcgt_range_chart", array("bcgtrangeid" => $this->id));            
                if($check)
                {
                    foreach($check as $chart)
                    {
                        $this->chart[$chart->grade] = $chart->points;
                    }
                }

                // Get links
                $check = $DB->get_records("block_bcgt_range_criteria", array("bcgtrangeid" => $this->id), "id ASC");
                if($check)
                {
                    foreach($check as $chart)
                    {
                        $this->links[$chart->bcgtcriteriaid] = $chart->maxpoints;
                    }
                }
            
            }
            
            
        }
        elseif(is_array($params))
        {
            $this->id = (isset($params['id'])) ? $params['id'] : '';
            $this->name = (isset($params['name'])) ? $params['name'] : '';
            $this->details = (isset($params['details'])) ? $params['details'] : '';
            $this->unitid = (isset($params['unitid'])) ? $params['unitid'] : 0;
            $this->targetdate = (isset($params['targetdate'])) ? $params['targetdate'] : 0;
            $this->defaulttargetdate = (isset($params['targetdate'])) ? $params['targetdate'] : 0;
            $this->chart = (isset($params['chart'])) ? $params['chart'] : '';         
        }
        
        
    }
    
    public function get_name(){
        return $this->name;
    }
    
    public function is_valid(){
        return ($this->id !== false);
    }
    
    public function load_student_information($studentID, $qualID)
    {
        
        global $DB;
        
        $this->studentID = $studentID;
        $this->qualID = $qualID;
        $this->awards = array();
        $this->gradeID = null;
        $this->grade = null;
        $this->targetdate = $this->defaulttargetdate;
        
        // Loop through linked criteria and see if they have a value in the DB
        if($this->links)
        {
            foreach($this->links as $criteriaID => $points)
            {
                // Check for DB record
                $check = $DB->get_record("block_bcgt_user_crit_range", array("userid" => $this->studentID, "bcgtqualificationid" => $this->qualID, "bcgtrangeid" => $this->id, "bcgtcriteriaid" => $criteriaID));
                $this->awards[$criteriaID] = ($check) ? $check->bcgtvalueid : null;
            }
        }
        
        // See if they have a grade in the DB
        $check = $DB->get_record("block_bcgt_user_range", array("userid" => $this->studentID, "bcgtrangeid" => $this->id, "bcgtqualificationid" => $this->qualID));
        $this->gradeID = (isset($check->id) && $check->bcgtvalueid > 0) ? $check->bcgtvalueid : null;
        $this->awardDate = (isset($check->id) && $check->awarddate > 0) ? $check->awarddate : null;
        if(isset($check->id) && $check->targetdate > 0) $this->targetdate = $check->targetdate;
        
        
        if($this->gradeID)
        {
            $check = $DB->get_record("block_bcgt_value", array("id" => $this->gradeID));
            $this->grade = (isset($check->id)) ? $check->value : null;
        }
                        
    }
    
    
    
    /**
     * Get the target date of the date, depdnding on whether student has different one or not
     */
    public function get_target_date($format = false)
    {
        
        global $DB;
        
        // If student info has been loaded, first check to see if they have their own target date
        if(isset($this->studentID))
        {
            $check = $DB->get_record("block_bcgt_user_range", array("userid" => $this->studentID, "bcgtrangeid" => $this->id, "bcgtqualificationid" => $this->qualID));
            if($check && $check->targetdate > 0)
            {
                if ($format) return date('d-m-Y', $check->targetdate);
                else return $check->targetdate;
            }
        }
        
        // Return default from range
        if ($format){
            if ($this->targetdate <= 0) return '';
            else return date('d-m-Y', $this->targetdate);
        }
        else {
            return $this->targetdate;
        }
        
    }
    
    private function reload_awards()
    {
        
        global $DB;
        
        $this->awards = array();
        
        // Loop through linked criteria and see if they have a value in the DB
        if($this->links)
        {
            foreach($this->links as $criteriaID => $points)
            {
                // Check for DB record
                $check = $DB->get_record("block_bcgt_user_crit_range", array("userid" => $this->studentID, "bcgtqualificationid" => $this->qualID, "bcgtrangeid" => $this->id, "bcgtcriteriaid" => $criteriaID));
                $this->awards[$criteriaID] = ($check) ? $check->bcgtvalueid : null;
            }
        }
        
    }
    
    private function delete_student_award()
    {
        if(!isset($this->studentID) || !isset($this->qualID)) return false;
        global $DB;
        $DB->delete_records("block_bcgt_user_range", array("userid" => $this->studentID, "bcgtrangeid" => $this->id));
        $this->gradeID = null;
        $this->grade = null;
    }
    
    /**
     * Update the award a student has got for the whole range
     * @param type $awardID 
     */
    public function update_student_award($awardID, $name = null)
    {
        if(!isset($this->studentID) || !isset($this->qualID)) return false;

        global $DB;

        $check = $DB->get_record("block_bcgt_user_range", array("userid" => $this->studentID, "bcgtrangeid" => $this->id, "bcgtqualificationid" => $this->qualID));
        
        if(!isset($check->id))
        {
            $obj = new stdClass();
            $obj->userid = $this->studentID;
            $obj->bcgtrangeid = $this->id;
            $obj->bcgtqualificationid = $this->qualID;
            $obj->bcgtvalueid = $awardID;
            $record = $DB->insert_record("block_bcgt_user_range", $obj);
        }
        else
        {
            $check->bcgtvalueid = $awardID;
            $record = $DB->update_record("block_bcgt_user_range", $check);
        }
        
        $this->gradeID = $awardID;
        
        if(!is_null($name)){
            $this->grade = $name;
        }
        
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_RANGE, LOG_VALUE_GRADETRACKER_UPDATED_RANGE_AWARD, $this->studentID, $this->qualID, $this->unitid, null, $this->id, $awardID);
        
        return $record;
        
    }
    
    public function update_student_award_auto($awardID, $name = null)
    {
                
        if(!isset($this->studentID) || !isset($this->qualID)) return false;

        global $DB;

        $check = $DB->get_record("block_bcgt_user_range", array("userid" => $this->studentID, "bcgtrangeid" => $this->id, "bcgtqualificationid" => $this->qualID));
        
        if(!isset($check->id))
        {
            $obj = new stdClass();
            $obj->userid = $this->studentID;
            $obj->bcgtrangeid = $this->id;
            $obj->bcgtqualificationid = $this->qualID;
            $obj->bcgtvalueid = $awardID;
            $record = $DB->insert_record("block_bcgt_user_range", $obj);
        }
        else
        {
            $check->bcgtvalueid = $awardID;
            $record = $DB->update_record("block_bcgt_user_range", $check);
        }
        
        $this->gradeID = $awardID;
        
        if(!is_null($name)){
            $this->grade = $name;
        }
                
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_RANGE, LOG_VALUE_GRADETRACKER_UPDATED_RANGE_AWARD_AUTO, $this->studentID, $this->qualID, $this->unitid, null, $this->id, $awardID);
        
        return $record;
        
    }
    
    public function update_student_award_date($date)
    {
        
        if(!isset($this->studentID) || !isset($this->qualID)) return false;
        
        global $DB;
        
        if (!$date) $unix = 0;
        else $unix = strtotime($date);
        
        $check = $DB->get_record("block_bcgt_user_range", array("userid" => $this->studentID, "bcgtrangeid" => $this->id, "bcgtqualificationid" => $this->qualID));
        
        if(!isset($check->id))
        {
            $obj = new stdClass();
            $obj->userid = $this->studentID;
            $obj->bcgtrangeid = $this->id;
            $obj->bcgtvalueid = 0;
            $obj->bcgtqualificationid = $this->qualID;
            $obj->awarddate = $unix;
            $record = $DB->insert_record("block_bcgt_user_range", $obj);
        }
        else
        {
            $check->awarddate = $unix;
            $record = $DB->update_record("block_bcgt_user_range", $check);
        }
        
        $this->awardDate = $unix;
        
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_RANGE, LOG_VALUE_GRADETRACKER_UPDATED_RANGE_AWARD_DATE, $this->studentID, $this->qualID, $this->unitid, null, $this->id, date('d-m-Y', $unix));
        
        return $record;
        
    }
    
    /**
     * Update the value a student has been given for a particular criteria on this range
     * @global type $USER
     * @param type $criteriaID
     * @param type $value
     * @return type 
     */
    public function update_student_value($criteriaID, $value, $calc = true)
    {
        
        global $USER, $DB;
        
        if(!isset($this->studentID) || !isset($this->qualID)) return false;
        
        $check = $DB->get_record("block_bcgt_user_crit_range", array("userid" => $this->studentID, "bcgtqualificationid" => $this->qualID, "bcgtrangeid" => $this->id, "bcgtcriteriaid" => $criteriaID));
        
        if(!isset($check->id))
        {
            
            $obj = new stdClass();
            $obj->userid = $this->studentID;
            $obj->bcgtqualificationid = $this->qualID;
            $obj->bcgtrangeid = $this->id;
            $obj->bcgtcriteriaid = $criteriaID;
            $obj->bcgtvalueid = $value;
            $obj->time = time(); 
            $obj->updatedbyuserid = $USER->id;
            $record = $DB->insert_record("block_bcgt_user_crit_range", $obj);
            logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_RANGE, LOG_VALUE_GRADETRACKER_UPDATED_CRITERIA_RANGE_VALUE, $this->studentID, $this->qualID, $this->unitid, null, $value, $this->id, $criteriaID);
            
        }
        else
        {
            $check->bcgtvalueid = $value;
            $check->time = time();
            $check->updatedbyuserid = $USER->id;
            $record = $DB->update_record("block_bcgt_user_crit_range", $check);
            logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_RANGE, LOG_VALUE_GRADETRACKER_UPDATED_CRITERIA_RANGE_VALUE, $this->studentID, $this->qualID, $this->unitid, null, $value, $this->id, $criteriaID);
        }
        
        $this->reload_awards();
        $this->recalculate($calc);
                
        
        return ($record) ? true : false;
        
    }
    
    public function update_student_target_date($date)
    {
        
        global $DB;
        
        if(!isset($this->studentID) || !isset($this->qualID)) return false;
        
        $unix = strtotime($date);
        $check = $DB->get_record("block_bcgt_user_range", array("userid" => $this->studentID, "bcgtrangeid" => $this->id, "bcgtqualificationid" => $this->qualID));
        
        if(!isset($check->id))
        {
            $obj = new stdClass();
            $obj->userid = $this->studentID;
            $obj->bcgtrangeid = $this->id;
            $obj->bcgtvalueid = 0;
            $obj->bcgtqualificationid = $this->qualID;
            $obj->targetdate = $unix;
            $record = $DB->insert_record("block_bcgt_user_range", $obj);
        }
        else
        {
            $check->targetdate = $unix;
            $record = $DB->update_record("block_bcgt_user_range", $check);
        }
        
        $this->targetdate = $unix;
        
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_RANGE, LOG_VALUE_GRADETRACKER_UPDATED_RANGE_TARGET_DATE, $this->studentID, $this->qualID, $this->unitid, null, $this->id, date('d-m-Y', $unix));
        
        return $record;
        
    }
    
    private function ajax_set_total_points($value)
    {
        echo "$('#awardedPoints').text('{$value}');";
    }
    
    public function ajax_set_grade($value)
    {
        echo "$('#awardedGrade').text('{$value}');";
    }
    
    public function ajax_set_grid_grade($value)
    {
        echo "$('#grid_S{$this->studentID}_R{$this->id}').val({$value});";
    }
    
    /**
     * Using a total points value, look up the values in the conversion chart and work out a grade
     * @param type $points 
     */
    private function calculate_grade($points)
    {
        
        global $DB;
        
        $grade = null;
                
        // Order by DESC
        arsort($this->chart);
        
        // Foreach entry in the conversion chart, as GRADE => POINTS
        foreach($this->chart as $GRADE => $POINTS)
        {
            if($points >= $POINTS){
                $grade = $GRADE;
                break; // No need to continue
            }
        }
        
        // Now get the actual awardID from the DB, using this $grade as a short value
        $check = $DB->get_record("block_bcgt_value", array("bcgttypeid" => $this->type, "shortvalue" => $grade));
        if(!isset($check->id)) return false;
                        
        // Update the award in the user_range table, then display on popup 
        $this->update_student_award($check->id, $check->value);
        
        $this->ajax_set_grade($this->grade);
        $this->ajax_set_grid_grade($this->gradeID);
        
    }
    
    private function get_type()
    {
        global $DB;
        $check = $DB->get_record("block_bcgt_unit", array("id" => $this->unitid), "bcgttypeid");
        return (isset($check->bcgttypeid)) ? $check->bcgttypeid : false;
    }
    
    /**
     * See if the student has a value for each of the criteria, and if so, work out their grade and update in DB
     */
    private function recalculate($calc = true)
    {
        
        // Go through all criteria linked to this range and make sure the student has a > 0 value for all of them
        // If they do, work out their total points and from the conversion chart - their grade, and update
        // If not, do nothing
        
        if(!$this->links) return false;
        
        $cnt = 0;
        $cntAwarded = 0;
        $ttl = 0;
        
        foreach($this->links as $critID => $max)
        {
            
            // If it's got 0 max points then it's not really linked to the range, so skip it
            if($max < 1) continue;
            $cnt++;
            if(isset($this->awards[$critID]) && $this->awards[$critID] > 0){
                $cntAwarded++;
                $ttl += $this->awards[$critID];
            }
            
        }
        
        // If cnt and cntawarded match, then they have a value for each of the criteria on this range
        // so we can calculate a grade and suchlike
        $this->ajax_set_total_points($ttl);
                
        if($cnt == $cntAwarded)
        {
            // Only try to recalculate the overall range award if there's no rule on the task saying not to
            if($calc) $this->calculate_grade($ttl);        
        }
        else
        {
            $this->delete_student_award();
            $this->ajax_set_grade("");
            $this->ajax_set_grid_grade(-1);
        }
        
    }
    
    public function save()
    {
        global $DB;
        $check = $DB->get_record("block_bcgt_range", array("id" => $this->id), "id");
        return (isset($check->id)) ? $this->update() : $this->insert();
    }
    
    public function insert_criteria_link($criteriaID, $points)
    {
        global $DB;
        
        $check = $DB->get_record("block_bcgt_range_criteria", array("bcgtrangeid" => $this->id, "bcgtcriteriaid" => $criteriaID));
        if(isset($check->id)){
            $check->maxpoints = $points;
            $DB->update_record("block_bcgt_range_criteria", $check);
            $this->links[$criteriaID] = $points;
            return true;
        }
        else
        {
            $obj = new stdClass();
            $obj->bcgtrangeid = $this->id;
            $obj->bcgtcriteriaid = $criteriaID;
            $obj->maxpoints = $points;
            $DB->insert_record("block_bcgt_range_criteria", $obj);
            $this->links[$criteriaID] = $points;
            return true;
        }
    }
    
    /**
     * Insert new range into DB
     */
    private function insert()
    {
        
        global $DB;
        
        // Insert range
        $obj = new stdClass();
        $obj->id = $this->id;
        $obj->name = $this->name;
        $obj->details = $this->details;
        $obj->bcgtunitid = $this->unitid;
        $obj->targetdate = $this->targetdate;
        $this->id = $DB->insert_record("block_bcgt_range", $obj);
        
        if ($this->id)
        {
        
            // Insert conversion chart
            foreach($this->chart as $GRADE => $POINTS)
            {
                $chart = new stdClass();
                $chart->bcgtrangeid = $this->id;
                $chart->points = $POINTS;
                $chart->grade = $GRADE;
                $DB->insert_record("block_bcgt_range_chart", $chart);
            }
            
            return true;
        
        }
        
        return false;
        
    }
    
    /**
     * Update range in DB
     */
    private function update()
    {
        
        global $DB;
        
        // Update range
        $obj = new stdClass();
        $obj->id = $this->id;
        $obj->name = $this->name;
        $obj->details = $this->details;
        $obj->bcgtunitid = $this->unitid;
        $obj->targetdate = $this->targetdate;
        if ($DB->update_record("block_bcgt_range", $obj)){
        
            // Update conversion chart
            foreach($this->chart as $GRADE => $POINTS)
            {
                $chart = new stdClass();
                $chart->id = $this->get_chart_record_id($GRADE);
                $chart->bcgtrangeid = $this->id;
                $chart->points = $POINTS;
                $chart->grade = $GRADE;
                $DB->update_record("block_bcgt_range_chart", $chart);
            }

            return true; 
        
        }
        
        return false;
        
    }
    
    public function delete_criteria_link($criteriaID){
        global $DB;
        return $DB->delete_records("block_bcgt_range_criteria", array("bcgtrangeid" => $this->id, "bcgtcriteriaid" => $criteriaID));
    }
    
    /**
     * Delete range from DB and archive it
     */
    public function delete()
    {
        return ( $this->archive_all() && $this->delete_all() ) ? true : false;
    }
    
    private function delete_all()
    {
        
        global $DB;
        
        $d1 = $DB->delete_records("block_bcgt_range", array("id" => $this->id));
        $d2 = $DB->delete_records("block_bcgt_range_chart", array("id" => $this->id));
        $d3 = $DB->delete_records("block_bcgt_range_criteria", array("id" => $this->id));;
        
        if($d1 && $d2 && $d3) return true;
        
        return false;
        
    }
    
   
    
    /**
     * Archive a range record
     */
    private function archive_all()
    {
        
        global $DB;
        
        // Archive the actual range
        $check = $DB->get_record("block_bcgt_range", array("id" => $this->id));
        $check->bcgtrangeid = $check->id;
        unset($check->id);
        $DB->insert_record("block_bcgt_range_history", $check);
        
        unset($check);
        
        // Archive the conversion chart
        $check = $DB->get_records("block_bcgt_range_chart", array("bcgtrangeid" => $this->id));
        
        foreach($check as $record)
        {
            $record->bcgtrangechartid = $record->id;
            unset($record->id);
            $DB->insert_record("block_bcgt_range_chart_his", $record);
        }
                
        unset($check);
        
        // Archive the range/criteria links
        $check = $DB->get_records("block_bcgt_range_criteria", array("bcgtrangeid" => $this->id));
        
        foreach($check as $record)
        {
            $record->bcgtrangecriteriaid = $record->id;
            unset($record->id);
            $DB->insert_record("block_bcgt_range_crit_his", $record);
        }
        
        return true;
                
        
    }
    
    private function get_chart_record_id($grade)
    {
        global $DB;
        $check = $DB->get_record("block_bcgt_range_chart",  array("bcgtrangeid" => $this->id, "grade" => $grade));
        return $check->id;
    }
    
    public function set($property, $value)
    {
        if(property_exists($this, $property)) $this->$property = $value;
        else return false;
    }
    
    public function get_awarded_points()
    {
        if(!isset($this->studentID) || !$this->awards) return false;
        
        $ttl = 0;
        
        // Loop through the values we've got and get the total
        foreach($this->awards as $criteriaID => $value)
        {
            $ttl += $value;
        }
        
        return $ttl;
        
    }
    
    private function get_max_points()
    {
        $max = 0;
        
        if($this->links)
        {
            foreach($this->links as $link)
            {
                $max += $link;
            }
        }
        
        return $max;
    }
    
    public function build_table($grid)
    {
        
        global $DB;
        
        $output = "";
        
        $output .= "<table id='rangePopupTable'>";
        
            $output .= "<tr class='lightpink'><th></th><th>{$this->name}</th></tr>";
            
            // Loop through criteria linked to it
            if($this->links)
            {
                foreach($this->links as $criteriaID => $points)
                {
                    $check = $DB->get_record("block_bcgt_criteria", array("id" => $criteriaID), "name");
                    if(!$check) continue;
                    
                    // See how many points it has, if 0 then shade it out
                    if($points < 1){
                        $content = "<td class='white_cell'></td>";
                    }
                    else
                    {
                        $content = "<td>";
                            for($i= 1 ; $i <= $points; $i++)
                            {
                                $chk = (isset($this->awards[$criteriaID]) && $this->awards[$criteriaID] == $i) ? "checked" : "";
                                $gridNum = ($grid == 'student') ? 1 : 2;
                                $content .= "<input type='radio' name='R{$this->id}C{$criteriaID}' value='{$i}' onclick='updateRangeCriteria(this.value, {$this->studentID}, {$this->qualID}, {$criteriaID}, {$this->id}, {$this->unitid}, {$gridNum});' {$chk} /> <label for=''>{$i}</label> ";
                                $content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                            }
                        $content .= "</td>";
                    }
                    
                    $output .= "<tr class='lightpink'><th>{$check->name}</th>{$content}</tr>";
                }
                
                $awardDate = ($this->awardDate > 0) ? date('d-m-Y', $this->awardDate) : "";
                
                // Build select menu for range award
                $awardMenu = "<select class='updateRangeAward' studentID='{$this->studentID}' rangeID='{$this->id}' qualID='{$this->qualID}' unitID='{$this->unitid}' grid='{$grid}' >";
                    $awards = $this->get_simple_awards();
                    $awardMenu .= "<option value='-1'></option>";
                    foreach($awards as $award)
                    {
                        $sel = ($this->gradeID == $award->id) ? "selected" : "";
                        $awardMenu .= "<option value='$award->id' {$sel}>$award->shortvalue - $award->value</option>";
                    }
                $awardMenu .= '</select>';
                
                $output .= "<tr class='doubleTop pink'><th>Total Points</th><td class='c'><span id='awardedPoints'>{$this->get_awarded_points()}</span> / <span id='maxPoints'>{$this->get_max_points()}</span></td></tr>";
                $output .= "<tr class='pink'><th>Grade</th><td id='awardedGrade' class='c'>{$awardMenu}</td></tr>";
                $output .= "<tr class='pink'><th>Award Date</th><td class='c'><input type='text' class='datePickerRange' qualID='{$this->qualID}' studentID='{$this->studentID}' rangeID='{$this->id}' value='{$awardDate}' /></td></tr>";
                
            }
        
        $output .= "</table>";
        
        return $output;
        
    }
    
    private function get_simple_awards()
    {
        global $DB;
        return $DB->get_records_select("block_bcgt_value", "bcgttypeid = ? AND specialval = 'A' AND shortvalue != 'A'", array($this->type), "id ASC");
    }
    
    public function build_name_tooltip()
    {
        
        global $DB;
        
        // Loop through all the criteria linked to this range and display their names
        $output = "<div>";
        
            $output .= "<h3 class='c'>{$this->name}</h2>";
            
            if($this->targetdate) $output .= "<b class='c'>Target Date: ".date('d M Y', $this->targetdate)."</b><br><br>";
                        
            if($this->links)
            {
                foreach($this->links as $criteriaID => $points)
                {
                    
                    if($points <= 0) continue;
                    
                    // Won't bother creating an Criteria opbject to get the name, waste of memory
                    $check = $DB->get_record("block_bcgt_criteria",array("id" => $criteriaID), "name");
                    if(isset($check->name))
                    {
                        $output .= "{$check->name}<br>";
                    }
                }
            }
                    
        $output .= "</div>";
        
        return $output;
        
    }
    
    /**
     * Get the Task criteria id of a range. There is a field in the DB for this, but it was never used due to
     * ranges having to be created before criteria. So find a record in range_criteria, then find the parent
     * of that criteria
     */
    public function get_parent_criteria_id()
    {
        global $CFG, $DB;
        
        $sql = "SELECT c.parentcriteriaid
                FROM {block_bcgt_range_criteria} rc
                INNER JOIN {block_bcgt_criteria} c ON c.id = rc.bcgtcriteriaid
                WHERE rc.bcgtrangeid = ?";
        $record = $DB->get_record_sql($sql, array($this->id), IGNORE_MULTIPLE);
        return ($record) ? $record->parentcriteriaid : false;
    }
    
    public static function exists($id)
    {
        global $DB;
        $check = $DB->get_record("block_bcgt_range", array("id" => $id), "id");
        return (isset($check->id)) ? true : false;
    }
    
    public static function get_all_possible_ranges($criteriaID)
    {
        
        global $DB;
        
        $return = array();
        
        $records = $DB->get_records("block_bcgt_range_criteria", array("bcgtcriteriaid" => $criteriaID));
        if ($records)
        {
            foreach($records as $record)
            {
                $return[] = new Range($record->bcgtrangeid);
            }
        }
        
        return $return;
        
    }
    
    
    public function __get($property) {
        if(property_exists($this, $property)){
            return $this->$property;
        }
    }
    
    
    public function toString(){
        return "<pre>".print_r($this, true)."</pre>";
    }
    
}
    
   