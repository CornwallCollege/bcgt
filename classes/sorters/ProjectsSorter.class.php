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
class ProjectsSorter
{
	function ComparisonDelegateByObjectDueDate($a, $b)
	{
		return self::ComparisonDelegate($a, $b, "ObjectDueDate");
	}
    
    function CompareByDateCurrent($a, $b)
    {
        //check if its the current one, 
        if($a->is_project_current())
        {
            return -1;
        }
        elseif($b->is_project_current())
        {
            return 1;
        }
        else
        {
            $aTime = $a->get_Due_Date_TimeStamp();
            $bTime = $b->get_Due_Date_TimeStamp();
            if($aTime == $bTime)
			{
				return 0;
			}
			return ($aTime < $bTime) ? -1 : 1;
        }
        //else do a check on the time stamp
    }
		
	function ComparisonDelegate($a, $b, $field)
	{			
		if($field == 'ObjectDueDate')
		{
			$aDate = $a->get_Due_Date_TimeStamp();
            $bDate = $b->get_Due_Date_TimeStamp();
			if($aDate == $bDate)
			{
				return 0;
			}
			return ($aDate < $bDate) ? -1 : 1;
		}
	}
}
?>