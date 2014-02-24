<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
//modified from: http://www.davenicholas.me.uk/blog/view_post/15/Sorting-an-array-of-objects-in-PHP
class ActivitySorter
{
	function ComparisonDelegateByArrayDate($a, $b)
	{
		return self::ComparisonDelegate($a, $b, "arrayDate");
	}
		
	function ComparisonDelegate($a, $b, $field)
	{			
		if($field == 'arrayDate')
		{
			$aDueDate = $a->duedate;
			$bDueDate = $b->duedate;
            if($aDueDate == $bDueDate)
            {
                return 0;
            }
            return ((int)$aDueDate < (int)$bDueDate) ? -1 : 1;
		}
	}
	
	private function sort_on_names($aStr, $bStr, $aNum, $bNum)
	{
		if($aStr == $bStr)
		{
			if($aNum == $bNum)
			{
				return 0;
			}
			return ((int)$aNum < (int)$bNum) ? -1 : 1;
		}
		
		if($aStr == 'P')
		{
			//then  bStr must be M or D
			return -1;
		} 
		elseif($aStr == 'M')
		{
			if($bStr == 'P')
			{
				return 1;	
			}
			//then its D
			return -1;
		}
		else
		{
			//a is D
			return 1;
		}
	}
}
?>