<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TargetGradeSorter
 *
 * @author mchaney
 */
class TargetGradeSorter {
    //put your code here

    function ComparisonDelegateByArrayRanking($a, $b)
	{
		return self::ComparisonDelegate($a, $b, "ArrayRanking");
	}

	function ComparisonDelegate($a, $b, $field)
	{			
		if($field == 'ArrayRanking')
		{
            //$a and $b will just be a number. The rankings. 
			if ($a == $b) {
				return 0;
			}
			//strstr($a->get_name(), ':', true)
			return ((int)$a < (int)$b) ? -1 : 1;
		}
	}
}

?>
