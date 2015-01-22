<?php
set_time_limit(0);
require_once '../../../config.php';
require_once $CFG->dirroot . '/blocks/bcgt/lib.php';
require_once $CFG->dirroot . '/blocks/bcgt/classes/core/Alps.class.php';
require_login();

$alps = new Alps();
$alps->export_alps_report();

exit;