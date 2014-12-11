<?php
require_once '../../../config.php';
require_once '../lib.php';
require_once '../classes/core/AssessmentTracker.class.php';
require_login();

$studentID = (isset($_POST['studentID']) && $_POST['studentID'] > 0) ? $_POST['studentID'] : false;
$courseID = (isset($_POST['courseID']) && $_POST['courseID'] > 0) ? $_POST['courseID'] : SITEID;
$qualID = (isset($_POST['qualID']) && $_POST['qualID'] > 0) ? $_POST['qualID'] : false;
$year = $_POST['year'];
$modLinks = (isset($_POST['modLinks'])) ? $_POST['modLinks'] : false;
$modTypes = $_POST['modTypes'];
$viewType = (isset($_POST['viewType'])) ? $_POST['viewType'] : false;

//pn($studentID);
//pn($courseID);
//pn($qualID);
//pn($year);
//pn($modLinks);
//pn($modTypes);

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


$AssessmentTracker = new AssessmentTracker();

// Student
if ($student)
{
    
    if (!$AssessmentTracker->loadStudent($student->id)){
        echo "could not load student";
        exit;
    }
    
    // Specific course
    if ($course){
        $studentCourses = array($course);
    }
    // All student's courses
    else
    {
        $studentCourses = $courses;
    }

    $AssessmentTracker->setYear($year);
    $AssessmentTracker->setCourses($studentCourses);
    $AssessmentTracker->setModuleLinks($modLinks);
    $AssessmentTracker->setModuleTypes($modTypes);
    
    echo $AssessmentTracker->getStudentTracker();
    exit;
    
}


elseif ($qual)
{
    
    $AssessmentTracker->setYear($year);
    $AssessmentTracker->setCourses($courses);
    $AssessmentTracker->setQual($qual);
    $AssessmentTracker->setModuleLinks($modLinks);
    $AssessmentTracker->setModuleTypes($modTypes);
    $AssessmentTracker->setViewType($viewType);
    
    echo $AssessmentTracker->getQualTracker();
    exit;
    
}


elseif ($course)
{
    
    $AssessmentTracker->setYear($year);
    $AssessmentTracker->setCourse($course);
    $AssessmentTracker->setModuleLinks($modLinks);
    $AssessmentTracker->setModuleTypes($modTypes);
    $AssessmentTracker->setViewType($viewType);
       
    echo $AssessmentTracker->getCourseTracker();
    exit;
    
}