<?php
/************************************************
 * 
 * This code has been written by Mark Chaney for
 * Bedford College. For contact details please use:
 * 
 * mchaney@bedford.ac.uk
 * or
 * mchaneycomputing@gmail.com
 * 
 ***************************************************/
class Value{
	
	private $id;
	private $value;
	private $shortValue;
    private $ranking;
    private $customValue;
    private $customShortValue;
    private $coreImg;
    private $customImg;
    private $coreImgLate;
    private $customImgLate;
    private $specialVal;
    private $enabled;
	
	//Is this a new value or a value from the db?
	function Value($id = -1, $params = null)
	{
		if($id != -1 && $params === null)
		{
			$details = $this->get_value_details($id);
			if($details)
			{
				$this->id = $id;
				$this->value = $details->value;
				$this->shortValue = $details->shortvalue;
                if (isset($details->ranking)) $this->ranking = $details->ranking;
                $this->customValue = $details->customvalue;
                $this->customShortValue = $details->customshortvalue;
                $this->coreImg = $details->coreimg;
                $this->customImg = $details->customimg;
                $this->coreImgLate = $details->coreimglate;
                $this->customImgLate = $details->customimglate;
                $this->specialVal = $details->specialval;
			}	
		}
		else
		{
			$this->id = $id;
            if(isset($params->value))
            {
                $this->value = $params->value; 
            }
            if(isset($params->shortValue))
            {
                $this->shortValue = $params->shortValue;
            }
            elseif(isset($params->shortvalue))
            {
                $this->shortValue = $params->shortvalue;
            }    
            if(isset($params->ranking))
            {
                $this->ranking = $params->ranking;
            }
            if(isset($params->customValue))
            {
                $this->customValue = $params->customvalue;
            }
            if(isset($params->customShortValue))
            {
                $this->customShortValue = $params->customshortvalue;
            }
            if(isset($params->coreImg))
            {
                $this->coreImg = $params->coreimg;
            }
            if(isset($params->customImg))
            {
                $this->customImg = $params->customimg;
            }
            if(isset($params->coreImgLate))
            {
                $this->coreImgLate = $params->coreimglate;
            }
            if(isset($params->customImgLate))
            {
                $this->customImgLate = $params->customimglate;	
            }
            if(isset($params->specialval))
            {
                $this->specialVal = $params->specialval;	
            }
            elseif(isset($params->specialVal))
            {
                $this->specialVal = $params->specialVal;
            }
		}
        if(!isset($this->coreImg) || $this->coreImg == '')
        {
            $this->get_attributes();
        }
	}
	
	function _destruct()
	{
	}
	
	public function set_id($id)
	{
		$this->id = $id;
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

	//This really should return a BOOLEAN!
	public function is_criteria_met()
	{
        if($this->specialVal == 'A')
        {
            return 'Yes';
        }
	}
    
    public function get_ranking()
    {
        return $this->ranking;
    }
    
    public function is_enabled()
    {
        return $this->enabled;
    }
        
    public function is_criteria_met_bool()
    {
        return ($this->specialVal == 'A');
    }
    
    public function get_special_val(){
        return $this->specialVal;
    }
	
	public function set_special_val($specialVal)
	{
		$this->specialval = $specialVal;
	}
	
	public function get_short_value()
	{
		return $this->shortValue;
	}
    
    public function get_custom_short_value()
    {
        return $this->customShortValue;
    }
    public function get_core_image_late()
    {
        return $this->coreImgLate;
    }
    
    public function get_custom_image_late()
    {
        return $this->customImgLate;
    }
    public function get_core_image()
    {
        return $this->coreImg;
    }
	public function get_custom_image()
    {
        return $this->customImg;
    }
    
    public function get_attributes()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_value_settings} WHERE bcgtvalueid = ?";
        $record = $DB->get_record_sql($sql, array($this->id));
        if($record)
        {
            $this->coreImg = $record->coreimg;
            $this->customImg = $record->customimg;
            $this->coreImgLate = $record->coreimglate;
            $this->customImgLate = $record->customimglate;
        }
    }
    
	public function set_short_value($shortValue)
	{
		$this->shortValue = $shortValue;
	}
	
	public static function is_met($valueID)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_value} WHERE id = ?";
		$record = $DB->get_record_sql($sql, array($valueID));
		if($record)
		{
			if($record->specialval == 'A')
			{
				return true;
			}
			return false;
		}
		return false;
	}
        
    public function create_default_object($shortValue, $bcgtTypeID)
    {
        global $DB;
        $sql = "SELECT value.*, settings.coreimg, settings.customimg, settings.coreimglate, settings.customimglate
            FROM {block_bcgt_value} value 
            JOIN {block_bcgt_value_settings} settings ON settings.bcgtvalueid = value.id 
            WHERE value.shortvalue = ? AND value.bcgttypeid = ?";
        $details = $DB->get_record_sql($sql, array($shortValue, $bcgtTypeID));
        if($details)
        {
            $this->id = $details->id;
            $this->value = $details->value;
            $this->shortValue = $details->shortvalue;
            if (isset($details->ranking)) $this->ranking = $details->ranking;
            $this->customValue = $details->customvalue;
            $this->customShortValue = $details->customshortvalue;
            $this->coreImg = $details->coreimg;
            $this->customImg = $details->customimg;
            $this->coreImgLate = $details->coreimglate;
            $this->customImgLate = $details->customimglate;
            $this->specialVal = $details->specialval;
            $this->enabled = $details->enabled;
        }
    }
    
    public static function retrieve_value($id = -1, $typeID = -1, $shortvalue = '')
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_value} WHERE"; 
        $params = array();
        if($id != -1)
        {
            $sql .= " id = ?";
            $params[] = $id;
        }
        elseif($typeID != -1 && $shortvalue != '')
        {
            $sql  .= ' bcgttypeid = ? AND shortvalue = ?';
            $params[] = $typeID;
            $params[] = $shortvalue;
        }
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return new Value($record->id, $record);
        }
        return false;
    }
    
    public static function retrieve_assessment_value($id, $typeID, $targetQualID, $value)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_value} ";
        $sql .= "WHERE context = ? AND value = ? AND ((bcgttypeid = ? AND bcgttargetqualid = ?) OR (bcgttypeid = ? AND bcgttargetqualid IS NULL))";
        $params = array('assessment', $value, -1, $targetQualID, $typeID);
        $sql .= " ORDER BY ranking DESC, id ASC";
        $record = $DB->get_record_sql($sql, $params);
        if($record)
        {
            return new Value($record->id, $record);
        }
        return false;
    }
	
	private function get_value_details($valueID)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_value} value 
            LEFT OUTER JOIN {block_bcgt_value_settings} settings ON settings.bcgtvalueid = value.id 
            WHERE value.id = ?";
		return $DB->get_record_sql($sql, array($valueID));
	}
}