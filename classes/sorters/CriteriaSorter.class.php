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
class CriteriaSorter
{
	function ComparisonDelegateByName($a, $b)
	{
		return self::ComparisonDelegate($a, $b, "name");
	}
	
	function ComparisonDelegateByArrayName($a, $b)
	{
		return self::ComparisonDelegate($a, $b, "arrayName");
	}
	
	function ComparisonDelegateByArrayNameLetters($a, $b)
	{
		return self::ComparisonDelegate($a, $b, "arrayNameLetters");
	}
	
	function ComparisonDelegateByObjectName($a, $b)
	{
		return self::ComparisonDelegate($a, $b, "ObjectName");
	}
	
    function ComparisonDelegateByDBtName($a, $b)
	{
		return self::ComparisonDelegate($a, $b, "DBName");
	}
    
    
	function ComparisonDelegate($a, $b, $field)
	{			
		if($field == 'name')
		{
			//The name is in the format of 'number: name'
			//so lets get the substring upuntill the : 
			$aStrPos = strpos($a->get_name(), '.');
			$bStrPos = strpos($b->get_name(), '.');
			$aStr = substr($a->get_name(), $aStrPos+1);
			$bStr = substr($b->get_name(), $bStrPos+1); 
			if ($aStr == $bStr) {
				return 0;
			}
			//strstr($a->get_name(), ':', true)
			return ((int)$aStr < (int)$bStr) ? -1 : 1;
		}
		elseif($field == 'arrayName')
		{
			//The name is in the format of 'number: name'
			//so lets get the substring upuntill the : 
			$aStrPos = strpos($a, '.');
			$bStrPos = strpos($b, '.');
			$aStr = substr($a, $aStrPos+1);
			$bStr = substr($b, $bStrPos+1); 
			if ($aStr == $bStr) {
				return 0;
			}
			//strstr($a->get_name(), ':', true)
			return ((int)$aStr < (int)$bStr) ? -1 : 1;
		}
		elseif($field == 'arrayNameLetters')
		{
            //if the criteria contain dots
            $numLoc = 1;
            if(strpos($a, '_'))
            {
                $numLoc++;
                $numLoc++;
            }
			$aStr = substr($a, 0, 1);
			$bStr = substr($b, 0, 1);
			$aNum = substr($a, $numLoc);
            $bNum = substr($b, $numLoc);
			return self::sort_on_names($aStr, $bStr, $aNum, $bNum);
		}
		elseif($field == 'ObjectName')
		{
			$aStr = substr($a->get_name(), 0, 1);
			$bStr = substr($b->get_name(), 0, 1);
			$aNum = substr($a->get_name(), 1);
			$bNum = substr($b->get_name(), 1);
			return self::sort_on_names($aStr, $bStr, $aNum, $bNum);
		}
        elseif($field == 'DBName')
        {
            $aStr = substr($a->name, 0, 1);
			$bStr = substr($b->name, 0, 1);
			$aNum = substr($a->name, 1);
			$bNum = substr($b->name, 1); 
            return self::sort_on_names($aStr, $bStr, $aNum, $bNum);
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
		
        if($aStr == 'L')
        {
            return -1;
        }
        elseif($aStr == 'P')
        {
            if($bStr == 'M' || $bStr == 'D')
            {
                return -1;
            }
            return 1;
        }
		elseif($aStr == 'M')
		{
			if($bStr == 'P' || $bStr == 'L')
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