<?php
set_time_limit(0);
require_once '../../../config.php';
require_once $CFG->dirroot . '/blocks/bcgt/lib.php';

require_login();

// If we send the qualID we can do one worksheet, otherwise it'll be from the unit_group grid
// so we'll need to have 1 worksheet per qual
$qualID = required_param('qualID', PARAM_INT);
$unitID = required_param('unitID', PARAM_INT);
$courseID = optional_param('courseID', SITEID, PARAM_INT);
if ($courseID < 1) $courseID = SITEID;

$context = context_course::instance($courseID);

if (!has_capability('block/bcgt:importexportunitgrids', $context)){
    print_error('invalid access');
}

$qual = Qualification::get_qualification_class_id($qualID);
if (!$qual){
    print_error('invalid qualification');
}

$loadParams = new stdClass();
$loadParams->loadLevel = \Qualification::LOADLEVELALL;
$loadParams->loadAward = true;
$loadParams->loadTargets = true;
$unit = Unit::get_unit_class_id($unitID, $loadParams);

if ($unit && method_exists($unit, 'export_unit_grid')){
        
    $name = preg_replace("/[^a-z 0-9]/i", "", $unit->get_display_name());
    $name .= " (".preg_replace("/[^a-z 0-9]/i", "", $qual->get_display_name()).")";
    
    ob_clean();
    header("Pragma: public");
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$name.'.xlsx"');     
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    
    require_once $CFG->dirroot . '/blocks/bcgt/lib/PHPExcel/Classes/PHPExcel.php';
    
    $unit->export_unit_grid($qualID);
    
} else {
    echo "Grids of this unit family cannot yet be exported.";
}
exit;