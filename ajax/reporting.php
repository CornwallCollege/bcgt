<?php
set_time_limit(0);
require_once '../../../config.php';
require_once $CFG->dirroot . '/blocks/bcgt/classes/core/ReportingSystem.class.php';

require_login();

$action = $_POST['action'];
$reportID = $_POST['reportID'];
$loadBelow = $_POST['below'];
$id = $_POST['id'];

$report = $DB->get_record("block_bcgt_repsys_reports", array("id" => $reportID));

if (!$report) exit;

$data = unserialize($report->data);
$elements = $data['elements'];
$filters = $data['filters'];

if ($action == 'load')
{
    
    
    switch($data['start_type'])
    {
        
        // We are listing by qualification family and below
        case 'qual':
            
            switch($loadBelow)
            {

                // Load the next level below qual family - the list of qualifications
                case 'FAMILY':

                    $results = ReportingSystem::get_results($data['start_type'], 'QUALS', $id, $filters, $elements);
                    
                    foreach($results as $qualID => $result)
                    {
                        echo ReportingSystem::get_results_row('QUAL', $qualID, $result['name'], $elements, $result);
                    }
                    

                break;
                
                case 'QUAL':
                    
                    $results = ReportingSystem::get_results($data['start_type'], 'STUDS', $id, $filters, $elements);
                    
                    foreach($results as $studentID => $result)
                    {
                        echo ReportingSystem::get_results_row('STUD', $studentID, $result['name'], $elements, $result);
                    }
                    
                break;

            }
            
        break;
        
        case 'unit':
        
            
            switch($loadBelow)
            {

                // Load the next level below qual family - the list of qualifications
                case 'FAMILY':

                    $results = ReportingSystem::get_results($data['start_type'], 'UNITS', $id, $filters, $elements);
                    
                    foreach($results as $unitID => $result)
                    {
                        echo ReportingSystem::get_results_row('UNIT', $unitID, $result['name'], $elements, $result);
                    }
                    

                break;
                
                case 'UNIT':
                    
                    $results = ReportingSystem::get_results($data['start_type'], 'STUDS', $id, $filters, $elements);
                    
                    foreach($results as $studentID => $result)
                    {
                        echo ReportingSystem::get_results_row('STUD', $studentID, $result['name'], $elements, $result);
                    }
                    
                break;

            }
            
        break;
        
        case 'cat':

            switch($loadBelow)
            {
            
                case 'CATEGORY':
                    
                    $results = ReportingSystem::get_results($data['start_type'], 'QUALS', $id, $filters, $elements);
                    
                    foreach($results as $qualID => $result)
                    {
                        echo ReportingSystem::get_results_row('QUAL', $qualID, $result['name'], $elements, $result);
                    }
                    
                break;
                
                case 'QUAL':
                    
                    $results = ReportingSystem::get_results($data['start_type'], 'STUDS', $id, $filters, $elements);
                    
                    foreach($results as $studentID => $result)
                    {
                        echo ReportingSystem::get_results_row('STUD', $studentID, $result['name'], $elements, $result);
                    }
                    
                break;
                
            
            }
            
        break;
        
        
    }
    
    
    
    
    
}