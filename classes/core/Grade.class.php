<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
class Grade{
	
	private $id;
	private $grade;
	//from the database the grade has a UMS upper and Lower Percentage. 
	//umsPercentage here is an average of these two. 
	private $umsAvgPercentage;
	
	//Is this a new value or a value from the db?
	function Grade($id = -1, $grade = '', $umsPercentageLower = null, 
	$umsPercentageHigher = null, $averageQualUMSPercentage = -1, $targetQualID = -1)
	{
		if($id != -1)
		{
			$details = $this->get_grade_details($id);
			if($details)
			{
				$this->id = $id;
				$this->grade = $details->grade;
				if(($details->umspercentagehigher - $details->umspercentagelower) > 10)
				{
					//in case of an A
					$this->umsAvgPercentage = ($details->umspercentagelower + $details->umspercentagelower + 10)/2;
				}
				else
				{
					$this->umsAvgPercentage = ($details->umspercentagelower + $details->umspercentagehigher)/2;
				}
			}	
		
		}
		elseif($averageQualUMSPercentage != -1 && $targetQualID != -1)
		{
			$details = null;
			$grades = $this->get_grade_details_by_percent($averageQualUMSPercentage, $targetQualID);
			if($grades && count($grades) == 1)
			{
				$details = end($grades);
			}
			elseif($grades)
			{
				$previousDiff = 0;
				//there should only ever be 2
				//we need to find which has the closest percent.
				foreach($grades AS $grade)
				{
					$diff = $grade->umspercentagehigher - $averageQualUMSPercentage;
					if($previousDiff == 0)
					{
						$previousDiff = $diff;
						$details = $grade;
					}
					else
					{
						if($previousDiff > $diff)
						{
							//then the second grade is closest to the actual average
							$details = $grade;
						}
					}
				}
				
			}
			if($details)
			{
				$this->id = $details->id;
				$this->grade = $details->grade;
				if(($details->umspercentagehigher - $details->umspercentagelower) > 10)
				{
					//in case of an A
					$this->umsAvgPercentage = ($details->umspercentagelower + $details->umspercentagelower + 10)/2;
				}
				else
				{
					$this->umsAvgPercentage = ($details->umspercentagelower + $details->umspercentagehigher)/2;
				}
			}	
		}
		else
		{
			$this->id = $id;
			$this->grade = $grade; 
			if($umsPercentageLower && $umsPercentageHigher)
			{
				if(($umsPercentageHigher - $umsPercentageLower) > 10)
				{
					//in case of an A
					$this->umsAvgPercentage = ($umsPercentageLower + $umsPercentageLower + 10)/2;
				}
				else
				{
					$this->umsAvgPercentage = ($umsPercentageLower + $umsPercentageHigher)/2;
				}
			}
		}
	}
	
	function _destruct()
	{
		unset($this->id);
		unset($this->grade);
	}
	
	public function set_id($id)
	{
		$this->id = $id;
	}
	
	public function get_id()
	{
		return $this->id;
	}
	
	public function set_grade($grade)
	{
		$this->grade = $grade;	
	}
	
	public function get_grade()
	{
		return $this->grade;
	}
	
	public function get_ums_avg_percentage()
	{
		return $this->umsAvgPercentage;	
	}
	
	public function set_ums_avg_percentage($umsAvgPercentage)
	{
		$this->umsAvgPercentage = $umsAvgPercentage;
	}
		
	private function get_grade_details($gradeID)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgt_target_grades} WHERE id = ?";
		return $DB->get_record_sql($sql, array($gradeID));
	}
	
	private function get_grade_details_by_percent($averageQualUMSPercentage, $targetQualID)
	{
		global $DB;
		$sql = "SELECT * FROM {block_bcgttarget_grades} 
		WHERE umspercentagelower <= ? AND 
		umspercentagehigher > ? 
		AND bcgttargetqualid = ?";
		return $DB->get_records_sql($sql, array($averageQualUMSPercentage, 
            $averageQualUMSPercentage, $targetQualID));
	}
}