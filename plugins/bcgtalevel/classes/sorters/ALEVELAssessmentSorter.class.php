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
class ALEVELAssessmentSorter
{	
	function ComparisonDelegateByTargetDateObject($a, $b)
	{
		//ifa or b are actually the object itself
		return self::ComparisonDelegate($a, $b, "targetdate", true, false);
	}
    
    function ComparisonDelegateByTargetDateObjectBack($a, $b)
	{
		//ifa or b are actually the object itself
		return self::ComparisonDelegate($a, $b, "targetdate", true, true);
	}
    
    
	
	function ComparisonDelegate($a, $b, $field, $object = false, $ASC = true)
	{
		if($field == 'targetdate')
		{
			if($object)
			{
				//if its an object then we need to call the 
				//public geter method
                $criteriaA = $a->criteria;
                $criteriaB = $b->criteria;
				$dateA = $criteriaA->get_target_date_unix();
				$dateB = $criteriaB->get_target_date_unix();
			}
            if($dateA == $dateB)
            {
                return 0;
            }
            //Check the number
            if($ASC)
            {
                return ($dateA < $dateB) ? -1 : 1;
            }
            else
            {
                return ($dateB < $dateA) ? -1 : 1;
            }
            
		}		
	}

}