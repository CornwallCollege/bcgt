<?php
set_time_limit(0);
require_once '../../../config.php';
require_once $CFG->dirroot . '/blocks/bcgt/lib.php';

require_login();

$qualID = required_param('qID', PARAM_INT);

$loadParams = new stdClass();
$loadParams->loadLevel = \Qualification::LOADLEVELALL;
$loadParams->loadAward = true;
$loadParams->loadTargets = true;
$qualification = Qualification::get_qualification_class_id($qualID, $loadParams);

if ($qualification && method_exists($qualification, 'export_specification')){
    
    $name = preg_replace("/[^a-z 0-9]/i", "", $qualification->get_display_name());
    
    ob_clean();
    header("Pragma: public");
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$name.'.xlsx"');     
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    
    require_once $CFG->dirroot . '/blocks/bcgt/lib/PHPExcel/Classes/PHPExcel.php';
    
    if ($qualification->export_specification() == false){
        header_remove("Pragma");
        header_remove('Content-Type');
        header_remove('Content-Disposition');     
        header_remove("Cache-Control");
        header_remove("Cache-Control");
        header('Content-Type: text/html; charset=utf-8');
        echo "Specifications of this qualification family cannot yet be exported.";
    }
    
} else {
    echo "Specifications of this qualification family cannot yet be exported.";
}
exit;