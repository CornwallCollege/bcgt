<?php
set_time_limit(0);
require_once '../../../config.php';
require_once $CFG->dirroot . '/blocks/bcgt/lib.php';
require_once $CFG->dirroot . '/blocks/bcgt/classes/core/ReportingSystem.class.php';

if (!isset($_GET['id'])) exit;

if(isset($_GET['export']) && $_GET['export'] == 'csv' && isset($_GET['id'])) {
            
    $report = $DB->get_record("block_bcgt_repsys_reports", array("id" => $_GET['id']));
    if (!$report) exit;
    
    $report->data = unserialize($report->data);
    ReportingSystem::export_csv($report);
    exit;
            
}

if (isset($_GET['csv']) && isset($_GET['id'])){
    
    $report = $DB->get_record("block_bcgt_repsys_reports", array("id" => $_GET['id']));
    if (!$report) exit;
    
    $file = $CFG->dataroot . '/bcgt/repsys/' . $report->id . '/' . $_GET['csv'];
    require_once $CFG->dirroot . '/lib/filelib.php';
    send_file($file, $report->name . '.csv');
    exit;
    
}