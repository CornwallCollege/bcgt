<?php

/**
 * Configure the [Plugin Name] plugin
 * 
 * @copyright 2012 Bedford College
 * @package Bedford College Electronic Learning Blue Print (ELBP)
 * @version 1.0
 * @author Conn Warwicker <cwarwicker@bedford.ac.uk> <conn@cmrwarwicker.com>
 * 
 */

require_once '../../config.php';
require_once $CFG->dirroot . '/blocks/elbp/lib.php';

$ELBP = ELBP\ELBP::instantiate();
$DBC = new ELBP\DB();

$view = optional_param('view', 'main', PARAM_ALPHA);

$access = $ELBP->getCoursePermissions(1);
if (!$access['god']){
    print_error( get_string('invalidaccess', 'block_elbp') );
}

// Need to be logged in to view this page
require_login();

try {
    $OBJ = \ELBP\Plugins\Plugin::instaniate("elbp_target_grades");
} catch (\ELBP\ELBPException $e){
    echo $e->getException();
    exit;
}

$TPL = new \ELBP\Template();
$MSGS['errors'] = '';
$MSGS['success'] = '';

// Submitted
if (!empty($_POST))
{
    $OBJ->saveConfig($_POST);
    $TPL->set("saved", get_string('saved', 'block_elbp'));
}


// Set up PAGE
$PAGE->set_context( context_course::instance(1) );
$PAGE->set_url($CFG->wwwroot . $OBJ->getPath() . 'config_elbp_target_grades.php');
$PAGE->set_title( get_string('config', 'block_elbp') );
$PAGE->set_heading( get_string('config', 'block_elbp') );
$PAGE->set_cacheable(true);
$ELBP->loadJavascript();
$ELBP->loadCSS();

// If course is set, put that into breadcrumb
$PAGE->navbar->add( get_string('config', 'block_elbp'), $CFG->wwwroot . $OBJ->getPath() . '/config.php', navigation_node::TYPE_CUSTOM);

echo $OUTPUT->header();


$TPL->set("OBJ", $OBJ);
$TPL->set("view", $view);
$TPL->set("MSGS", $MSGS);
$TPL->set("OUTPUT", $OUTPUT);

// Settings for display
$settings = array();
$settings['course_types'] = $OBJ->getSetting('course_types');

$TPL->set("settings", $settings);

try {
    $TPL->load( $CFG->dirroot . $OBJ->getPath() . '/tpl/elbp_target_grades/config.html' );
    $TPL->display();
} catch (\ELBP\ELBPException $e){
    echo $e->getException();
}

echo $OUTPUT->footer();