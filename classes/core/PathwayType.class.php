<?php
/************************************************
 * 
 * This code has been written by Mark Chaney for
 * Bedford College. For contact details please use:
 * 
 * mchaney@bedford.ac.uk
 * or
 * mchaneycomputing@gmail.com
 * 
 ***************************************************/

//This class is used to store the Level details. For example Level 1
class PathwayType{
	
	const CGHBVRQ = 1;
	const CGHBNVQ= 2;
	const CGGENERAL = 3;
	
	private $id;
	private $pathwayType;
	
	function PathwayType($id, $pathwayType)
	{
		$this->id = $id;
		$this->pathwayType = $pathwayType; 
	}
	
	public function set_id($id)
	{
		$this->id = $id;
	}
	
	public function get_id()
	{
		return $this->id;
	}
	
	public function set_pathwayType($pathwayType)
	{
		$this->pathwayType = $pathwayType;	
	}
	
	public function get_pathwayType()
	{
		return $this->pathwayType;
	}	
}






?>