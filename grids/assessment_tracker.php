<?php
require_once '../../../config.php';
require_once '../lib.php';
require_once $CFG->dirroot . '/course/lib.php';
require_once '../classes/core/AssessmentTracker.class.php';

$studentID = optional_param('studentID', false, PARAM_INT);
$courseID = optional_param('courseID', SITEID, PARAM_INT);
$qualID = optional_param('qualID', false, PARAM_INT);

if ($courseID < 1){
    $courseID = SITEID;
}

$context = context_course::instance($courseID);
require_login();

$AssessmentTracker = new AssessmentTracker();

// Defaults
$anyOrCriteriaActivities = AssessmentTracker::DEFAULT_MOD_LINKS;
$moduleTypes = explode(",", AssessmentTracker::DEFAULT_MOD_TYPES);
$year = date('Y');
$showVals = false;


if (!has_capability('block/bcgt:viewassessmenttracker', $context)){
    print_error('invalid access');
}

// Make sure student is valid
$student = false;
if ($studentID){
    
    $student = $DB->get_record("user", array("id" => $studentID));
    if (!$student){
        print_error('invalid student');
    }
    
    // Find all the courses the student is on
    $courses = enrol_get_users_courses($student->id, true);
    
}

$course = false;
if ($courseID > 0 && $courseID != SITEID){
    $course = $DB->get_record("course", array("id" => $courseID));
}


$qual = false;
if ($qualID > 0){
    
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELALL;
    
    $qual = Qualification::get_qualification_class_id($qualID, $loadParams);
    
    // Find all the courses the qual is on
    $courses = bcgt_get_courses_with_quals($qualID);
}


$url = $CFG->wwwroot . '/blocks/bcgt/grids/assessment_tracker.php';

$PAGE->set_context($context);
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('assessmenttracker', 'block_bcgt'));
$PAGE->set_heading(get_string('assessmenttracker', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
if($courseID != 1)
{
    global $DB;
    $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($courseID));
    if($course)
    {
        $PAGE->navbar->add($course->shortname,$CFG->wwwroot.'/course/view.php?id='.$courseID,'title');
    }
}
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'), $CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php', 'title');
$PAGE->navbar->add(get_string('assessmenttracker', 'block_bcgt'), $url, 'title');
if ($student){
    $PAGE->navbar->add( fullname($student) . ' ('.$student->username.')' , $url . '?courseID='.$courseID.'&studentID='.$studentID, 'title');
}
load_javascript(true);

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initassessmenttracker', array($studentID, $courseID, $qualID, (int)$showVals), true, $jsModule);

echo $OUTPUT->header();

echo "<div id='assessment_tracker'>";

// Looking at the assessment calendar of a particular student
if ($student)
{
    
    if (!$AssessmentTracker->loadStudent($student->id)){
        print_error('could not load student');
    }
    
    $AssessmentTracker->setYear($year);
    $AssessmentTracker->setCourses($courses);
    $AssessmentTracker->setModuleLinks($anyOrCriteriaActivities);
    $AssessmentTracker->setModuleTypes($moduleTypes);
    $AssessmentTracker->setShowValues($showVals);
    
    echo "<div id='assessment_tracker_left'>";
    
    echo "<div id='assessment_tracker_options'>";
        
        echo "<div id='assessment_tracker_options_title'>";
            echo get_string('options');
        echo "</div>";
        
        echo "<div id='assessment_tracker_options_content'>";
            
        echo $AssessmentTracker->getStudentTrackerOptions();
        
        echo "</div>";
            
    echo "</div>"; // ENd of options
        
    echo "<p id='loading' style='display:none;'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/ajax-loader.gif' /></p>";
    
    echo "<div id='assessment_tracker_info'>";
        
        echo "<div id='assessment_tracker_info_title'></div>";
                    
        echo "<div id='assessment_tracker_info_content'></div>";
        
    echo "</div>";
    
    echo "</div>"; // ENd of left
    
    echo "<div id='assessment_tracker_content'>";
            
    echo $AssessmentTracker->getStudentTracker();
    
    echo "</div>";
                    
}




// Looking at the assessment calendar for a particular course
elseif ($course)
{
    
    $AssessmentTracker->setYear($year);
    $AssessmentTracker->setCourse($course);
    $AssessmentTracker->setModuleLinks($anyOrCriteriaActivities);
    $AssessmentTracker->setModuleTypes($moduleTypes);
   
    echo "<div id='assessment_tracker_left'>";
    
    echo "<div id='assessment_tracker_options'>";
        
        echo "<div id='assessment_tracker_options_title'>";
            echo get_string('options');
        echo "</div>";
        
        echo "<div id='assessment_tracker_options_content'>";
            
        echo $AssessmentTracker->getCourseTrackerOptions();
        
        echo "</div>";
            
    echo "</div>"; // ENd of options
        
    echo "<p id='loading' style='display:none;'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/ajax-loader.gif' /></p>";
    
    echo "<div id='assessment_tracker_info'>";
        
        echo "<div id='assessment_tracker_info_title'></div>";
                    
        echo "<div id='assessment_tracker_info_content'></div>";
        
    echo "</div>";
    
    echo "</div>"; // ENd of left
            
    echo "<div id='assessment_tracker_content'>";
        echo $AssessmentTracker->getCourseTracker();
    echo "</div>";
    
}



// Looking at a particular qualification
elseif ($qual)
{
        
    // Specific course
    if ($courseID > 0 && $courseID != SITEID){
        $course = $DB->get_record("course", array("id" => $courseID));
        $qualCourses = array($course);
    }
    // All student's courses
    else
    {
        $qualCourses = $courses;
    }
    
    $AssessmentTracker->setYear($year);
    $AssessmentTracker->setCourses($qualCourses);
    $AssessmentTracker->setQual($qual);
    $AssessmentTracker->setModuleLinks($anyOrCriteriaActivities);
    $AssessmentTracker->setModuleTypes($moduleTypes);
    
    echo "<div id='assessment_tracker_left'>";
    
    echo "<div id='assessment_tracker_options'>";
        
        echo "<div id='assessment_tracker_options_title'>";
            echo get_string('options');
        echo "</div>";
        
        echo "<div id='assessment_tracker_options_content'>";
            
        echo $AssessmentTracker->getQualTrackerOptions();
        
        echo "</div>";
            
    echo "</div>"; // ENd of options
        
    
    echo "<p id='loading' style='display:none;'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/ajax-loader.gif' /></p>";
    
    
    
    echo "<div id='assessment_tracker_info'>";
        
        echo "<div id='assessment_tracker_info_title'></div>";
                    
        echo "<div id='assessment_tracker_info_content'></div>";
        
    echo "</div>";
    
    
    // Show a list of units and criteria that have not been linked to any modules
    echo "<div id='assessment_tracker_units'>";
        
        echo "<div id='assessment_tracker_units_title'>".get_string('unlinkedunits2', 'block_bcgt')."</div>";
                    
        echo "<div id='assessment_tracker_units_content'>";
        
            echo "<table class='assessment_tracker_unlinked_units'>";
        
            $unlinkedCriteria = bcgt_get_unlinked_units_criteria($qual);
            if ($unlinkedCriteria)
            {
                foreach($unlinkedCriteria as $unitID => $criteria)
                {
                    if ($criteria)
                    {
                        
                        $unit = $qual->get_unit($unitID);
                        if ($unit)
                        {
                            
                            echo "<tr><td class='unitname'>".$unit->get_name()."</td></tr>";
                            echo "<tr><td class='critnames'>";
                            
                            $critNames = array();
                            
                            foreach($criteria as $criterion)
                            {
                                $critNames[] = $criterion->get_name();
                            }

                            $critNames = $qual->sort_criteria($critNames);

                            echo implode(", ", $critNames);
                            
                            echo "</td></tr>";
                            
                        }
                        
                    }
                }
            }
            else
            {
                echo "Everything is linked to at least one activity/module";
            }
            
            echo "</table>";
        
        echo "</div>";
        
    echo "</div>";
    
    
    
    
    echo "</div>"; // ENd of left
            
    echo "<div id='assessment_tracker_content'>";
        echo $AssessmentTracker->getQualTracker();
    echo "</div>";
    
}

echo "</div>";

echo $OUTPUT->footer();