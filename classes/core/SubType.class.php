<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

class SubType {
	
    const DEFAULTNUMBEROFYEARSNAME = 'DEFAULT_YEARS';
    const DEFAULTNUMBEROFCREDITSNAME = 'DEFAULT_CREDITS';
	private $id;
	private $subType;
    private $shortSubType;

	function SubType($id, $subType = '')
	{
		$this->id = $id;
        if($subType != '')
        {
           $this->subType = $subType; 
        }
        else
        {
            $subTypeObj = $this->retrive_subtype($id);
            if($subTypeObj)
            {
               $this->subType = $subTypeObj->subtype; 
               $this->shortSubType = $subTypeObj->subtypeshort;
            }
        }
        
        $subTypeObj = $this->retrive_subtype($id);
        if($subTypeObj)
        {
           $this->shortSubType = $subTypeObj->subtypeshort;
        }
	}
	
	public function set_id($id)
	{
		$this->id = $id;
	}
	
	public function get_id()
	{
		return $this->id;
	}
	
	public function set_subType($subType)
	{
		$this->subType = $subType;	
	}
	
	public function get_subType()
	{
		return $this->subType;
	}
    
    public function get_short_subtype()
    {
        return $this->shortSubType;
    }
        
        public function get_short_sub_type()
        {
//            switch($this->subType)
//            {
//                case 'Diploma': return 'Dip';
//                case 'Subsidiary Diploma': return 'Sub Dip';
//                case 'Certificate': return 'Cert';
//                case 'A Level': return 'A2' ;
//                case 'AS Level': return 'AS' ;
//                case 'Bespoke': return 'Bespoke' ;
//                case 'Extended Diploma': return 'Ext Dip' ;
//                case 'GCSE': return 'GCSE' ;
//                case 'Extended Certificate': return 'Ext Cert' ;
//                case 'Award': return 'Award' ;
//                case 'HNC': return 'HNC' ;
//                case 'HND': return 'HND' ;
//                case 'TechCert': return 'TechCert' ;
//                case 'PEO': return 'PEO' ;
//                case 'VRQ': return 'VRQ' ;
//                case 'NVQ': return 'NVQ' ;
//                default: return '';
//                    
//            }
        }
	
    public static function get_subtypeID_by_subtype($subtype)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_subtype} WHERE subtype = ?";
        $record = $DB->get_record_sql($sql, array($subtype));
        if($record)
        {
            return $record->id;
        }
        return -1;
    }
        
        
	public function to_string()
	{
		return 'Id: '.$this->id.', Level: '.$this->subType.'';
	}
    
    protected function retrive_subtype($id)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_subtype} WHERE id = ?";
        return $DB->get_record_sql($sql, array($id));
    }
        
	
}