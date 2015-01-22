<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
class QualificationAward{
	
	private $id;
	//text
	private $award;
	
	//predicted/techerinputed/final
	private $type;
	
	//number
	private $ucasPoints;
	
	//optional
	private $unitsScoreLower;
	
	//optional
	private $unitsScoreUpper;
	
	//optional
	private $warningCount;
	
	//optional
	private $warning;
        
    private $dateUpdated;
    
    private $ranking;
	    
	function QualificationAward($id = -1, $params = null)
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
            if(isset($params->ucasPoints))
            {
                $this->ucasPoints = $params->ucasPoints;
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
            if (isset($params->ranking)){
                $this->ranking = $params->ranking;
            }
        }
	}
	
	function _destruct()
	{
		unset($this->id);
		unset($this->award);
		unset($this->type);
		unset($this->ucasPoints);
	}
	
	public function set_id($id)
	{
		$this->id = $id;
	}
	
	public function get_id()
	{
		return $this->id;
	}
	
    public function get_ranking(){
        return $this->ranking;
    }
    
    public function set_ranking($ranking){
        $this->ranking = $ranking;
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
	
	public function set_ucasPoint($ucasPoints)
	{
		$this->ucasPoints = $ucasPoints;
	}
	
	public function get_ucasPoints()
	{
		return $this->ucasPoints;
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
	
	public function set_warningCount($warningCount)
	{
		$this->warningCount = $warningCount;
	}
	
	public function get_warningCount()
	{
		return $this->warningCount;
	}
	
	public function set_warning($warning)
	{
		$this->warning = $warning;
	}
	
	public function get_warning()
	{
		return $this->warning;
	}
        
    public function get_date_updated()
    {
        return date('d M Y', $this->dateUpdated);
    }

    public function set_date_updated($value)
    {
        $this->dateUpdated = $value;
    }
    
    public function calculate_users_qual_awards($users)
    {
        //for each user get their quals
        //for each qual calculate their predicted grade
        if($users)
        {
            foreach($users AS $user)
            {
                $quals = get_users_quals($user->id);
                if($quals)
                {
                    foreach($quals AS $qual)
                    {
                        $loadParams = new stdClass();
                        $loadParams->loadLevel = Qualification::LOADLEVELALL;
                        $qualification = Qualification::get_qualification_class_id($qual->id,$loadParams);
                        if($qualification)
                        {
                            $loadParams = new stdClass();
                            $loadParams->loadLevel = Qualification::LOADLEVELALL;
                            $loadParams->loadAward = true;
                            $qualification->load_student_information($user->id,
                                    $loadParams);
                            $qualification->calculate_predicted_grade();
                        }
                    }
                }
            }
        }
    }
}