<?php
class BespokeCriteriaSorter
{
	        
        function Comparison($obj1, $obj2)
        {
            // This wasn't ordering them in a "Natural" way as I was expecting, and it seems natsort doesn't ignore
            // leading zero's, so i've had to alter this myself to remove them temporarily in the search
            
            // Basically, for both objects: If there the name starts with a-z and then a 0 (so it's a leading zero)
            // and the length of the name is different to the length of the other object's name:
            //  e.g. "C1" and "C2" is fine, but "C10" and "C020" isn't
            // then we remove the first occurance of "0" from the name, otherwise just use the default name
            
            // Then compare as normal
            $A = ( preg_match("/^[a-z]0/i", $obj1->get_name()) && strlen($obj1->get_name()) <> strlen($obj2->get_name()) ) ? preg_replace("/0/", "", $obj1->get_name(), 1) : $obj1->get_name();
            $B = ( preg_match("/^[a-z]0/i", $obj2->get_name()) && strlen($obj2->get_name()) <> strlen($obj1->get_name()) ) ? preg_replace("/0/", "", $obj2->get_name(), 1) : $obj2->get_name();
            return ( strnatcasecmp($A, $B) == 0 ) ? 0 : (  strnatcasecmp($A, $B) > 0 ) ? 1 : -1;
        }
        
        function ComparisonByID($a, $b)
        {
            return ($a->get_id() > $b->get_id()) ? 1 : -1;
        }
	
        function ComparisonByTask($a, $b)
        {
            return ($a->get('ID') > $b->get('ID')) ? 1 : -1;
        }
        
        /**
         * Same as the comparison method, but for records out of the DB, having the "name" property, but no methods
         * @param type $obj1
         * @param type $obj2
         * @return type 
         */
        function ComparisonOnTheFly($obj1, $obj2)
        {
            $A = ( preg_match("/^[a-z]0/i", $obj1->name) && strlen($obj1->name) <> strlen($obj2->name) ) ? preg_replace("/0/", "", $obj1->name, 1) : $obj1->name;
            $B = ( preg_match("/^[a-z]0/i", $obj2->name) && strlen($obj2->name) <> strlen($obj1->name) ) ? preg_replace("/0/", "", $obj2->name, 1) : $obj2->name;
            return ( strnatcasecmp($A, $B) == 0 ) ? 0 : (  strnatcasecmp($A, $B) > 0 ) ? 1 : -1;
        }
        
        /**
         * Same as the others, except this is for those occasions when the criteria are just string elements of an array, with no
         * properties or methods or anything like that
         * @param type $obj1
         * @param type $obj2
         * @return type
         */
        function ComparisonSimple($obj1, $obj2)
        {
            $A = ( preg_match("/^[a-z]0/i", $obj1) && strlen($obj1) <> strlen($obj2) ) ? preg_replace("/0/", "", $obj1, 1) : $obj1;
            $B = ( preg_match("/^[a-z]0/i", $obj2) && strlen($obj2) <> strlen($obj1) ) ? preg_replace("/0/", "", $obj2, 1) : $obj2;
            return ( strnatcasecmp($A, $B) == 0 ) ? 0 : (  strnatcasecmp($A, $B) > 0 ) ? 1 : -1;
        }
        
//        function ComparisonOrder($a, $b)
//        {
//            return ($a->get_order() == $b->get_order()) ? 0 : (($a->get_order() > $b->get_order()) ? 1 : -1);
//        }
        
}