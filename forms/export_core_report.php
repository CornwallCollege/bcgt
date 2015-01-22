<?php
set_time_limit(0);
require_once '../../../config.php';
require_once $CFG->dirroot . '/blocks/bcgt/lib.php';
require_once $CFG->dirroot . '/blocks/bcgt/classes/core/CoreReports.class.php';
require_once $CFG->dirroot . '/blocks/bcgt/classes/core/ReportingSystem.class.php';
require_login();

$number = required_param('id', PARAM_INT);
$courseID = optional_param('courseID', SITEID, PARAM_INT);

$context = context_course::instance($courseID);

if (!has_capability('block/bcgt:importexportstudentgrids', $context)){
    print_error('invalid access');
}
CoreReports::display_view_report($number, true);

exit;