<?php
require_once '../../../config.php';
require_once '../lib.php';
require_once '../classes/sorters/CriteriaSorter.class.php';
require_login();

if (!isset($_POST['mod']) || !isset($_POST['id'])){
    echo json_encode( array("error" => "required param missing") );
    exit;
}

$mod = $_POST['mod'];
$id = $_POST['id'];
$studentID = (isset($_POST['studentID'])) ? $_POST['studentID'] : false;
$showVals = (isset($_POST['showVals'])) ? $_POST['showVals'] : false;

$modLink = get_mod_linking_by_name($mod);
if (!$modLink){
    echo json_encode( array("error" => "module not linked to gradetracker") );
    exit;
}

$module = $DB->get_record($modLink->modtablename, array("id" => $id));
if (!$module){
    echo json_encode( array("error" => "invalid module") );
    exit;
}

$titleField = $modLink->modtitlefname;
$courseField = $modLink->modtablecoursefname;
$startField = $modLink->modtablestartdatefname;
$endField = $modLink->modtableduedatefname;

$start = $module->$startField;
$end = $module->$endField;

// Course module
$courseModule = bcgt_get_course_module($module->$courseField, $modLink->moduleid, $module->id);
if (!$courseModule){
    echo json_encode( array("error" => "invalid course module") );
    exit;
}

$criteriaSorter = new CriteriaSorter();


// Get info for display
$content = "";

$course = $DB->get_record("course", array("id" => $module->$courseField));
if ($course){
    $content .= "<p class='c'><b><a href='{$CFG->wwwroot}/course/view.php?id={$course->id}' target='_blank'>{$course->fullname}</a></b></p>";
}


$content .= "<p class='c'>";

$icon = $CFG->dirroot . '/mod/' . $mod . '/pix/icon.png';
if (file_exists($icon)){
    $icon = str_replace($CFG->dirroot, $CFG->wwwroot, $icon);
    $content .= "<img src='{$icon}' class='icn' /> ";
}

$content .= "<a href='{$CFG->wwwroot}/mod/{$mod}/view.php?id={$courseModule->id}' target='_blank'>" . get_string('pluginname', 'mod_'.$mod) . "</a>";

$content .= "</p>";


// Dates
$content .= "<p class='c'>";

    $content .= "<small>";
        $content .= date('M jS Y, H:i', $start);
        $content .= "<br>-<br>";
        $content .= date('M jS Y, H:i', $end);
    $content .= "</small>";
    
$content .= "</p>";

// Criteria links
$criteria = bcgt_get_course_module_criteria($courseModule->id);


if ($criteria)
{
    
    $content .= "<table class='assessment_tracker_activity_criteria'>";
    
        $content .= "<tr><th>".get_string('criterialinkedmod', 'block_bcgt')."</th></tr>";
        
        foreach($criteria as $qualID => $units)
        {
            
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
            
            $qual = Qualification::get_qualification_class_id($qualID, $loadParams);
            if ($qual && $units)
            {

                $content .= "<tr><td class='qualname'><a href='{$CFG->wwwroot}/blocks/bcgt/forms/activities.php?cID={$course->id}' target='_blank'>".$qual->get_display_name()."</a></td></tr>";
                
                foreach($units as $unitID => $criteria)
                {
                    
                    $loadParams = new stdClass();
                    $loadParams->loadLevel = Qualification::LOADLEVELALL;
                    
                    $unit = Unit::get_unit_class_id($unitID, $loadParams);
                    if ($unit && $criteria)
                    {
                        
                        $content .= "<tr>";
                            $content .= "<td class='unitname'>".$unit->get_name()."</td>";
                        $content .= "</tr>";
                        
                            $content .= "<tr>";
                            $content .= "<td>";
                            
                                $critNames = array();
                            
                                foreach($criteria as $critID)
                                {
                                    $criterion = $unit->get_single_criteria($critID);
                                    if ($criterion)
                                    {
                                        $critNames[] = $criterion->get_name();
                                    }
                                }
                                
                                $critNames = $qual->sort_criteria($critNames);
                                                                
                                $content .= implode(", ", $critNames);
                                                                
                            $content .= "</td>";
                        $content .= "</tr>";
                        
                        // If we are looking at a student, and we want to see their values
                        if ($studentID && $showVals == 1){
                                                        
                            $unit->load_student_information($studentID, $qual->get_id(), $loadParams);
                            
                            $content .= "<tr>";
                                $content .= "<td>";
                                    $content .= "<table class='criteria_values'>";
                                    $content .= "<tr><th colspan='2'>".get_string('criteriavalues', 'block_bcgt')."</th></tr>";
                                        
                                    if ($critNames)
                                    {
                                        foreach($critNames as $critName)
                                        {
                                            $criterion = $unit->get_single_criteria(-1, $critName);
                                            if ($criterion)
                                            {
                                                $studentValueObj = $criterion->get_student_value();
                                                $value = ($studentValueObj) ? $studentValueObj->get_short_value() : '-';
                                                $content .= "<tr>";
                                                    $content .= "<td>{$criterion->get_name()}</td>";
                                                    $content .= "<td>{$value}</td>";
                                                $content .= "</tr>";
                                            }
                                        }
                                    }
                                    
                                    $content .= "</table>";
                                $content .= "</td>";
                            $content .= "</tr>";
                        }
                                
                        
                        
                        
                    }
                    
                }
                
            }
        }
    
    $content .= "</table>";
    
}

$return = array(
    "title" => $module->$titleField,
    "content" => $content,
);
echo json_encode( $return );