<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

/*This is the class used for the awards given to students for units and quals*/
class Award{
	
	private $id;
	private $award;
	private $rank;
    private $shortaward;
	
	function Award($id, $params = null)
	{
		$this->id = $id;
        if($params)
        {
            if(isset($params->award))
            {
                $this->award = $params->award;
            }
            if(isset($params->rank))
            {
                $this->rank = $params->rank;
            }
            if(isset($params->shortaward))
            {
                $this->shortaward = $params->shortaward;
            }
        }
	}
	
	function _destruct()
	{
		unset($this->id);
		unset($this->award);
		unset($this->rank);
	}
	
	public function set_id($id)
	{
		$this->id = $id;
	}
	
	public function get_id()
	{
		return $this->id;
	}
	
	public function set_award($award)
	{
		$this->award = $award;	
	}
	
	public function get_award()
	{
        if ($this->id == -1) return "N/S";
		return $this->award;
	}	
    
    public function get_short_award()
    {
        return $this->shortaward;
    }
    
    public function set_short_award($shortAward)
    {
        $this->shortaward = $shortAward;
    }
	
	public function set_rank($rank)
	{
		$this->rank = $rank;
	}
	
	public function get_rank()
	{
        if ($this->id == -1) return 0;
		return $this->rank;
	}
	
	public function has_units()
	{
		return false;
	}
	
	public static function get_award_id($id)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_type_award} WHERE id = ?";
		$record = $DB->get_record_sql($sql, array($id));
		if($record)
		{
            $params = new stdClass();
            $params->award = $record->award;
            $params->rank = $record->ranking;
            $params->shortaward = $record->shortaward;
			return new Award($record->id, $params);
		}
		else
		{
			return new Award(-1);	
		}		
	}
}