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
class Pathway{
	
	const CGGENERAL = 1;
	const CGHB = 2;
    
	private $id;
	private $pathway;
	
	function Pathway($id, $pathway)
	{
		$this->id = $id;
		$this->pathway = $pathway; 
	}
	
	public function set_id($id)
	{
		$this->id = $id;
	}
	
	public function get_id()
	{
		return $this->id;
	}
	
	public function set_pathway($pathway)
	{
		$this->pathway = $pathway;	
	}
	
	public function get_pathway()
	{
		return $this->pathway;
	}	
}






?>