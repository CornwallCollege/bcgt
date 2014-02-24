<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserDataImport
 *
 * @author mchaney
 */
class BackupImport {
    //put your code here
    protected $files;
    protected $summary;
    protected $success;
    
    function BackupImport()
    {
        
    }
    
    public function get_headers()
    {
        $retval = '';
        return $retval;
    }
    
    private function get_header($no)
    {
        return array();
    }
    
    public function get_examples()
    {
        return '';
    }
    
    public function get_description()
    {
        return "";
    }
    
    public function get_file_names()
    {
        $retval = '';
        return $retval;
    }
    
    public function get_files_name_array()
    {
        return array();
    }
    
    public function has_multiple()
    {
        return true;
    }
    
    public function get_file_options()
    {
        $retval = '';
        return $retval;
    }
    
    public function get_submitted_import_options()
    {
        
    }
    
    public function was_success()
    {
        return $this->success;
    }
    
    public function display_summary()
    {        
        $retval = '';
        return $retval;
    }
    
    public function validate($server = false)
    {
        global $CFG;
        return true;
    }
    
    public function check_header($file, $headerArray)
    {

    }
    
    public function validate_header($headerCSV, $headerArray)
    {

    }

    public function get_files($server = false)
    {

    }
    
    public function process_import_csv($process = false)
    {
     
    }
    
    public function display_import_options()
    {
        $retval = '';
        return $retval;
    }
}

?>
