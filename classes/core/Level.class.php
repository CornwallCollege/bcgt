<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

//This class is used to store the Level details. For example Level 1
class Level{
	
	const level1ID = 1;
	const level2ID = 2;
	const level3ID = 3;
	const level4ID = 4;
	const level5ID = 5;
    const levelBID = 6;
    const level12ID = 7;
	
	private $id;
	private $level;
	
	function Level($id, $level = '')
	{
		$this->id = $id;
        if($level != '')
        {
           $this->level = $level;  
        }
        else
        {
            $levelObj = $this->retrive_level($id);
            if($levelObj)
            {
               $this->level = $levelObj->trackinglevel; 
            }
        }
		
	}
	
	public function set_id($id)
	{
		$this->id = $id;
	}
	
	public function get_id()
	{
		return $this->id;
	}
	
	public function set_level($level)
	{
		$this->level = $level;	
	}
	
	public function get_level()
	{
		return $this->level;
	}
    
    public function get_level_number()
    {
        $lvl = preg_replace("/[^0-9]/", "", $this->level);
        return $lvl;
    }
	
	public function to_string()
	{
		return 'Id: '.$this->id.', Level: '.$this->level.'';
	}
	
	public function get_short()
	{
		return $this->get_short_version($this->id);
	}
    
    public static function get_levelID_by_level($level)
    {
        switch($level){
			case ("Level 1"):
				return 1;
				break;
			case ("Level 2"):
				return 2;
				break;
			case ("Level 3"):
				return 3;
				break;
			case ("Level 4"):
				return 4;
				break;
			case ("Level 5"):
				return 5;
				break;
			default:
                return "";
            break;
		}
    }
	
	public static function get_short_version($levelID)
	{
		switch($levelID){
			case (Level::level1ID):
				return "L1";
				break;
			case (Level::level2ID):
				return "L2";
				break;
			case (Level::level3ID):
				return "L3";
				break;
			case (Level::level4ID):
				return "L4";
				break;
			case (Level::level5ID):
				return "L5";
				break;
            case (Level::level12ID):
                return "L1&2";
			default:
                return "";
            break;
		}
	}
    
    protected function retrive_level($id)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_level} WHERE id = ?";
        return $DB->get_record_sql($sql, array($id));
    }
	
}






?>