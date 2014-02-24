<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

//This class is used to sort the unit. 
//It will sort it on either name or uniqueID
//It assumes that a and b are always CRITERIA OBJECTS 
//rather than the criteria names as strings
//we want it to return all P's, then M'd and then D's

//modified from: http://www.davenicholas.me.uk/blog/view_post/15/Sorting-an-array-of-objects-in-PHP
class UnitSorter
{
	function ComparisonDelegateByName($a, $b)
	{
		return $this->ComparisonDelegate($a, $b, "name");
	}
	
	function ComparisonDelegateByUniqueID($a, $b)
	{
		return $this->ComparisonDelegate($a, $b, "uniqueID");
	}
	
	function ComparisonDelegateByType($a, $b)
	{
		return $this->ComparisonDelegate($a, $b, "type");
	}
	
	function ComparisonDelegate($a, $b, $field)
	{		
		if($field == 'type')
		{
			if($a->get_unit_type() == $b->get_unit_type())
			{
				return self::ComparisonDelegate($a, $b, "name");
			}
			else
			{
				return ($a->get_unit_type() < $b->get_unit_type()) ? -1 : 1;
			}
			
			
		}
		
		if($field == 'name')
		{
			//The name is in the format of 'number: name'
			//so lets get the substring upuntill the : 
			$aStrPos = strpos($a->get_name(), ':');
			$bStrPos = strpos($b->get_name(), ':');
			$aStr = substr($a->get_name(), 0, $aStrPos);
			$bStr = substr($b->get_name(), 0, $bStrPos); 
			if ($aStr == $bStr) {
				return 0;
			}
			//strstr($a->get_name(), ':', true)
			return ($aStr < $bStr) ? -1 : 1;
		}
		elseif($field == 'uniqueID')
		{
			if ($a->get_uniqueID() == $b->get_uniqueID()) {
				return 0;
			}
			
			return ($a->get_uniqueID() < $b->get_uniqueID) ? -1 : 1;
		}
		
	}
        
        function Comparison($obj1, $obj2)
        {

            // FInd whatever is before the ":"
            $pos = strpos($obj1->get_name(), ":");
            if(is_int($pos)) $A = preg_replace( "/Unit(\s*)/i", "", substr($obj1->get_name(), 0, $pos) );
            else $A = preg_replace( "/Unit(\s*)/i", "", $obj1->get_name() );

            $pos = strpos($obj2->get_name(), ":");
            if(is_int($pos)) $B = preg_replace( "/Unit(\s*)/i", "", substr($obj2->get_name(), 0, $pos) );
            else $B = preg_replace( "/Unit(\s*)/i", "", $obj2->get_name() );

            return ( strnatcasecmp($A, $B) == 0 ) ? 0 : (  strnatcasecmp($A, $B) > 0 ) ? 1 : -1;

        }

}