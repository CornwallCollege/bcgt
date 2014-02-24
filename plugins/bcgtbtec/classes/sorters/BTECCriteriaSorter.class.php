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
class BTECCriteriaSorter
{
	//Allows the sorting of the btec criteria. 
	//The name can comes down in the form of P1 or M1
	function ComparisonDelegateByName($a, $b)
	{
		//If we just have a or b as strings
		return self::ComparisonDelegate($a, $b, "name");
	}
	
	function ComparisonDelegateByNameObject($a, $b)
	{
		//ifa or b are actually the object itself
		return self::ComparisonDelegate($a, $b, "name", true);
	}
	
	function ComparisonDelegate($a, $b, $field, $object = false)
	{
		if($field == 'name')
		{
			//The name is in the format of 'letternumber'
			//for example P1 or D1
			//we want to sort so P's and then M's and then D's
			//ect
			//so lets get the substring upuntill the : 
			if($object)
			{
				//if its an object then we need to call the 
				//public geter method 
				$letterA = substr($a->get_name(), 0, 1);
				$letterB = substr($b->get_name(), 0, 1);
				$numberA = substr($a->get_name(), 1);
				$numberB = substr($b->get_name(), 1);
			}
			else
			{
				//its just the string of the name
				$letterA = substr($a, 0, 1);
				$letterB = substr($b, 0, 1);
				$numberA = substr($a, 1);
				$numberB = substr($b, 1);
			}
			if ($letterA == $letterB) 
			{
				//if we are looking at two P's
				if($numberA == $numberB)
				{
					//technically not possible
					//as a criteria should only be able to have unique names. 
					//e.g. we shouldnt be able to have a unit with P1, P1, P2, P2 ect
					return 0;
				}
				//Check the number
				return ($numberA < $numberB) ? -1 : 1;
			}
			elseif($letterA == 'P' && (($letterB == 'M') || ($letterB == 'D')))
			{
				//should return P, M, D in that order
				return -1;
			}
			elseif($letterA == 'D' && (($letterB == 'P') || ($letterB == 'M')))
			{
				return 1;
			}
			else
			{
				//latterA must be qual to M
				//so is Letter b P or D
				if($letterB == 'P')
				{
					return 1;
				}
				return -1;
			}
		}		
	}

}