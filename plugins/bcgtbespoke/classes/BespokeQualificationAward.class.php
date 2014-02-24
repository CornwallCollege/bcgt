<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
class BespokeQualificationAward
{
	
	private $id;
	//text
	private $award;
	
	//predicted/techerinputed/final
	private $type;
	
	//optional
	private $unitsScoreLower;
	
	//optional
	private $unitsScoreUpper;
        
    private $dateUpdated;
	
	function BespokeQualificationAward($id, $params = null)
	{
		$this->id = $id;
        if($params)
        {
            if(isset($params->award))
            {
                $this->award = $params->award;
            }
            if(isset($params->type))
            {
                $this->type = $params->type; 
            }
            
            if(isset($params->dateUpdated))
            {
                $this->dateUpdated = $params->dateUpdated;
            }
            else
            {
                $this->dateUpdated = time();
            }
            if(isset($params->unitsScoreLower))
            {
                $this->unitsScoreLower = $params->unitsScoreLower;
            }
            if(isset($params->unitsScoreUpper))
            {
                $this->unitsScoreLower = $params->unitsScoreUpper;
            }
        }
	}
	
	function _destruct()
	{
		unset($this->id);
		unset($this->award);
		unset($this->type);
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
		return $this->award;
	}	
	
	public function set_type($type)
	{
		$this->type = $type;
	}
	
	public function get_type()
	{
		return $this->type;
	}
	
	
	public function set_unitsPointsLower($unitsPointsLower)
	{
		$this->unitsPointsLower = $unitsPointsLower;
	}
	
	public function get_unitsPointsLower()
	{
		return $this->unitsPointsLower;
	}
	
	public function set_unitsPointsUpper($unitsPointsUpper)
	{
		$this->unitsPointsUpper = $unitsPointsUpper;
	}
	
	public function get_unitsPointsUpper()
	{
		return $this->unitsPointsUpper;
	}
	
    public function get_date_updated()
    {
        return date('d M Y', $this->dateUpdated);
    }

    public function set_date_updated($value)
    {
        $this->dateUpdated = $value;
    }
}