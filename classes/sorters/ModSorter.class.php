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
class ModSorter
{
	function ComparisonDelegateByDueDateObj($a, $b)
	{
		return self::ComparisonDelegate($a, $b, "duedateobj");
	}
		
	function ComparisonDelegate($a, $b, $field)
	{			
		if($field == 'duedateobj')
		{
			$aDueDate = $a->dueDate;
			$bDueDate = $b->dueDate;
            if($aDueDate == $bDueDate)
            {
                return 0;
            }
            if($aDueDate == 0)
            {
                return 1;
            }
            if($bDueDate == 0)
            {
                return -1;
            }
            return ((int)$aDueDate < (int)$bDueDate) ? -1 : 1;
		}
	}
}
?>