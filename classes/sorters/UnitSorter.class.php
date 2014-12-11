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
    
    function ComparisonDelegateByUserAward($a, $b)
    {
        return $this->ComparisonDelegate($a, $b, "userAward");
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
        elseif($field == 'userAward')
        {
            $unitPointsA = $this->get_unit_award_points($a);
            $unitPointsB = $this->get_unit_award_points($b);
            if($unitPointsA == $unitPointsB)
            {
                //lets check the credits!
                if($a->get_credits() == $b->get_credits())
                {
                    return 0;
                }
                $retval = ($a->get_credits() < $b->get_credits()) ? 1 : -1;
                return $retval;
            }
            $retval = ($unitPointsA < $unitPointsB) ? 1 : -1;
            return $retval;
        }
	}
    
    function get_unit_award_points($unit)
    {
        $points = 0;
        $unitAward = $unit->get_user_award();
        //do we actually have a unt award?
        if($unitAward != null && $unitAward->get_id() != '' 
                    && $unitAward->get_id() != null 
                    && $unitAward->get_id() > 0)
        {
            //what is the points?
            $unitLevelID = $unit->get_level_id();
            if($unitLevelID && $unitLevelID != -1)
            {
                $unitPointsObj = $unit->get_unit_award_points($unitAward->get_id(), $unitLevelID);
                if($unitPointsObj)
                {
                    $points = $unitPointsObj->points;
                }
            }
            else
            {
                ///so we need to hard code it for a bit:
                $award = $unitAward->get_award();
                switch($award)
                {
                   case "Pass":
                   case "P":
                       $points = 1;
                       break;
                   case "Merit":
                   case "M";
                       $points = 2;
                       break;
                   case "Distinction":
                   case "D":
                       $points = 3;
                       break;
                }
                
            }

        }
        return $points;
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
            
            

            return strnatcasecmp($A, $B);

        }
        
        function ComparisonSimple($obj1, $obj2)
        {

            // FInd whatever is before the ":"
            $pos = strpos($obj1, ":");
            if(is_int($pos)) $A = preg_replace( "/Unit(\s*)/i", "", substr($obj1, 0, $pos) );
            else $A = preg_replace( "/Unit(\s*)/i", "", $obj1 );

            $pos = strpos($obj2, ":");
            if(is_int($pos)) $B = preg_replace( "/Unit(\s*)/i", "", substr($obj2, 0, $pos) );
            else $B = preg_replace( "/Unit(\s*)/i", "", $obj2 );
            
            return strnatcasecmp($A, $B);

        }

}