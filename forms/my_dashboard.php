<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
//TODO submit on forms and POST rather than using links. 


global $COURSE, $PAGE, $OUTPUT, $CFG, $USER;
//require_once('../../../config.php');
//require_once('../lib.php');
//require_once($CFG->dirroot.'/user/profile/lib.php');

set_time_limit(0);

require_once('../classes/core/DashTab.class.php');
$courseID = optional_param('cID', -1, PARAM_INT);
if($courseID != -1)
{
    $context = context_course::instance($courseID);
}
else
{
    $context = context_course::instance($COURSE->id);
}
require_login();
$PAGE->set_context(context_system::instance());
require_capability('block/bcgt:viewdashboard', $context);
$tab = optional_param('tab', 'dash', PARAM_TEXT);
$url = '/blocks/bcgt/forms/my_dashboard.php';
$PAGE->set_url($url, array('page' => $tab));
$PAGE->set_title(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->set_heading(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
if($courseID != -1)
{
    global $DB;
    $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($courseID));
    if($course)
    {
        $PAGE->navbar->add($course->shortname,$CFG->wwwroot.'/course/view.php?id='.$courseID,'title');
    }
    
}
$pageTitle = DashTab::bcgt_get_dashboard_tab_title($tab);
$PAGE->navbar->add($pageTitle);
$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event')
);
$PAGE->requires->js_init_call('M.block_bcgt.init', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript(true);
echo $OUTPUT->header();

//form for the search
echo '<div id="bcgtPageContainer" class="bcgt_page_container">';
//echo '<form action="#" method="POST" name="search_student">';
//echo '<input type="hidden" name="cID" value="'.$courseID.'"/>';
////the search
//echo '<div id="bcgtStudentSearchDiv"><label for="studentSearch">'.get_string('searchStudent', 'block_bcgt').'</label>';
//echo '<input type="text" disabled="disabled" name="studentSearch" value="" id="searchStudent"/>';
//echo '<input type="button" disabled="disabled" name="search" value="'.
//        get_string('search', 'block_bcgt').'" id="search"/>';
//echo '</div></form>';
//end form
//the div for the extra search content
echo '<div id="studentSearchContent"></div>';

//div for the content
echo '<div class="bcgt_container" id="bcgtDashboard">';
echo '<form method="POST" name="gotocourse" action="'.$CFG->wwwroot.'/course/view.php">';
echo "<label for='gotocourse'>".get_string('gotomycourses', 'block_bcgt')."</label>";
$hasQual = false;
$hidden = false;
$courses = bcgt_get_users_course_access($USER->id, $hasQual, $hidden);
echo "<select id='gotocourse' name='id'>";
echo "<option value='-1'></option>";
if($courses)
{
    foreach($courses AS $course)
    {
        $selected = '';
        if($courseID == $course->courseid)
        {
            $selected = 'selected="selected"';
        }   
        echo "<option $selected value='".$course->courseid."'>".$course->shortname." - ".$course->fullname."</option>";
    }
}
echo "</select>";
echo "<input type='submit' id='courseGo' name='go' value='".get_string('go', 'block_bcgt')."'/>";
echo "</form>";
echo '<div id="bcgtDashTabs">';
echo '<form method="POST" name="changeView" action="#tab">';
echo '<input type="hidden" id="cID" name="cID" value="'.$courseID.'"/>';

echo '<div class="tabs"><div class="tabtree">';
echo '<ul class="tabrow0">';
//So get the tabs. 
//There are core tabs and there are additional tabs. 
//get the core tabs this can then depends on the permissions
//then find if there are any extra class. This will look through block_bcgt_tabs
//find any that are not component 'core', Look in the 'tabclassfile' for the class
//that has the name 'component'DashTab. Load that Class
//Display the tabs from there
echo DashTab::bcgt_get_dashboard_tabs($tab);
echo '</ul>';
//TODO Dont forget subjects when looking at Alevels
//for each qualification family installed, get extra tabs

//end tabtree//end tabs//end tabs
echo '</div></div></form></div>';
echo '<div class="bcgt_tab_content_container" id="bcgtTabContentContainer">';
//TODO make sure they cant get to the courses by guessing the tab id!
//same with students. 

//now to actually view the tab. 
//get the id, using the id go into block_bcgt_tabs and check if its
//a none core tab
//if its a non core tab then load the class
//display the tab 

echo DashTab::bcgt_display_dashboard_tab_view($tab, $courseID);

//end tab container//end container
echo '</div></div>';

echo '</div>';

echo $OUTPUT->footer();

?>
