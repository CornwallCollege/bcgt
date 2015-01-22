<?php
set_time_limit(0);
require_once '../../../config.php';
require_once $CFG->dirroot . '/blocks/bcgt/lib.php';

require_login();

$qualID = required_param('qualID', PARAM_INT);
$studentID = required_param('studentID', PARAM_INT);
$courseID = optional_param('courseID', SITEID, PARAM_INT);

$context = context_course::instance($courseID);

if (!has_capability('block/bcgt:importexportstudentgrids', $context)){
    print_error('invalid access');
}

$loadParams = new stdClass();
$loadParams->loadLevel = \Qualification::LOADLEVELALL;
$loadParams->loadAward = true;
$loadParams->loadTargets = true;
$qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
$qualification->load_student_information($studentID, $loadParams);

if ($qualification && method_exists($qualification, 'export_student_grid')){
    
    $student = $DB->get_record("user", array("id" => $studentID));
    
    $name = preg_replace("/[^a-z 0-9]/i", "", $qualification->get_display_name() . ' - ' . fullname($student) . ' ('.$student->username.')');
    
    ob_clean();
    header("Pragma: public");
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$name.'.xlsx"');     
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    
    require_once $CFG->dirroot . '/blocks/bcgt/lib/PHPExcel/Classes/PHPExcel.php';
    
    $qualification->export_student_grid();
    
} else {
    echo "Grids of this qualification family cannot yet be exported.";
}
exit;