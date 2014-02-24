<?php

/**
 * <title>
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

/**
 * 
 */
class BespokeValue {
    
    private $id;
	private $value;
	private $shortValue;
    private $ranking;
    private $met;
    
    public function __construct($id) {
        
        global $DB;
        
        // Get value from bespoke crit grading vals
        $record = $DB->get_record("block_bcgt_bspk_c_grade_vals", array("id" => $id));
        if ($record)
        {
            
            $this->id = $record->id;
            $this->value = $record->grade;
            $this->shortValue = $record->shortgrade;
            $this->ranking = $record->points;
            $this->met = $record->met;
            
        }
        
    }
    
    
    public function get_id()
	{
		return $this->id;
	}
	
	public function set_value($value)
	{
		$this->value = $value;	
	}
	
	public function get_value()
	{
		return $this->value;
	}
    
    public function get_short_value()
	{
		return $this->shortValue;
	}
    
    public function is_met(){
        return ($this->met == 1);
    }
    
    
}