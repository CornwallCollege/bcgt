<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;;
require_once('../../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

$cID = optional_param('cID', -1, PARAM_INT);
$type = optional_param('type', '', PARAM_TEXT);
$archiveSubType = optional_param('archivetype', '', PARAM_TEXT);
require_login();
if($cID != -1)
{
    $context = context_course::instance($cID);
}
else
{
    $context = context_course::instance($COURSE->id);
}
$PAGE->set_context($context);
$url = '/blocks/bcgt/forms/archive_data.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('archivedata', 'block_bcgt'));
$PAGE->set_heading(get_string('archivedata', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('import', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
$PAGE->navbar->add(get_string('admin', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string('archivedata', 'block_bcgt'),'','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initarchive', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();

$archive = new Archive($type, $archiveSubType);
if(isset($_POST['run']))
{
    $archive->run_archive();
}
    

$out = $OUTPUT->header();

$out .= '<div id="archiveBCGT" class="bcgt_div_container">';
$out .= '<h2>Archiving Data</h2>';
$out .= '<form name="archive_data_form" id="archive_data_form" action="archive_data.php" method="POST">';
$out .= '<input type="hidden" name="cID" value="'.$cID.'"/>';
$out .= '<input type="hidden" name="type" value="'.$type.'"/>';
$out .= '<div id="archivetype">';
$out .= '<h3>'.get_string('archivetype','block_bcgt').'</h3>';
$out .= get_string('archivetype', 'block_bcgt').'<select name="archivetype" id="archivetype">';
$out .= '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>';
$types = $archive->get_types($type);
if($types)
{
    foreach($types AS $value=>$subType)
    {
        $selected = '';
        if($archiveSubType != '' && $value == $archiveSubType)
        {
            $selected = 'selected';
        }
        $out .= '<option '.$selected.' value="'.$value.'">'.$subType.'</option>';
    }
        
}
$out .= '</select>';
$out .= '</div>';
$out .= '<div id="archiveoptions">';
$out .= '<h3>'.get_string('archiveoptions', 'block_bcgt').'</h3>';
$out .= $archive->get_archive_options($type, $archiveSubType);
$out .= '<input type="submit" name="run" value="'.get_string('archive','block_bcgt').'"/>';
$out .= '</div>';
//Run button
//process

//types: formal assessment

//create the json string

//Subject Name
//Target
//Weighted Target
//Overall Ceta
//Overall Alps Score
//Project Name
//Project Date
//Project Grade
//Project Ceta
//Project Grade Alps Score
//Project Ceta Alps Score

$out .= '</form>';
$out .= '</div>';
$out .= $OUTPUT->footer();

echo $out;
?>
