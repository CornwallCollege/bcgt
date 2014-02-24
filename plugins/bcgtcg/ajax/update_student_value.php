<?php
require_once '../../../../../config.php';
require_once '../../../bclib.php';
require_once '../../../lib.php';
require_once('../lib.php');

bcgt_start_timing();

require_cg();
require_login();

// This is an AJAX script so the default global COURSE will be the front page
$context = context_course::instance($COURSE->id);
$PAGE->set_context($context);

$grid = $_POST['grid']; # Student or Unit grid
$method = $_POST['method']; # Check or Select
$qualID = $_POST['qualID'];
$unitID = $_POST['unitID'];
$criteriaID = $_POST['criteriaID'];
$studentID = $_POST['studentID'];
$value = $_POST['value'];

$retval = new stdClass();

// Student grid
if ($grid == 'student')
{
    
    require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Qualification.class.php');
    $sessionQuals = isset($_SESSION['session_stu_quals'])? 
        unserialize(urldecode($_SESSION['session_stu_quals'])) : array();
        
    //this will be an array of studentID => qualarray->qual object->qual
    $qualification = null;
    if(array_key_exists($studentID, $sessionQuals))
    {
        //the sessionsQuals[studentID] is an array of qualid =>object
        //where object has qualification and session start
        $studentQualArray = $sessionQuals[$studentID];
        if(array_key_exists($qualID, $studentQualArray))
        {
            $qualObject = $studentQualArray[$qualID];
            $qualification = $qualObject->qualification;
        }
    }
        
    // If we couldn't find the qual in the session, get it frmo db
    if(!$qualification)
    {
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELCRITERIA;
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        if($qualification)
        {
            $qualification->load_student_information($studentID,
                $loadParams);
        }
    }
    
    // If qualification is still not valid
    if (is_null($qualification) || !$qualification){
        echo json_encode( get_string('invalidqual', 'block_bcgt') );
        exit;
    }
    
    // Continue
    $criteria = false;
    $unit = $qualification->get_single_unit($unitID);
    if($unit)
    {
        $criteria = $unit->get_single_criteria($criteriaID);
    }
            
    // If criteria is valid
    if ($criteria)
    {
        
        // If the method is "check" that means it's a checkbox
        if ($method == 'check' && $criteria->get_grading() == 'P')
        {
            
            // Get the pass value
            $grades = $criteria->get_met_values();
            $grade = array_shift($grades);
            
            // If the value coming from the form is 0, then set this to -1 as we have unchecked it
            // otherwise if it's 1, set it to the id of this value
            $value = ($value == 0) ? -1 : $grade->id;
            
            
            // Set info
            $criteria->set_user($USER->id);
            $criteria->set_date();
            $criteria->update_students_value($value);
            $criteria->save_student($qualID, false); # Save straight away so we can check the parent and loop through all children's awards         

            
        }
        
        elseif ($method == 'date' && $criteria->get_grading() == 'DATE')
        {

            // If it's a date thingy, we're going to set the actual award as Pass and the setdate as the date
            $criteria->set_user($USER->id);
            $criteria->set_date();
            $criteria->set_award_date( strtotime($value) );

            // Get the pass value for this qualtype
            $grades = $criteria->get_met_values();
            $grade = array_shift($grades);
            $value = $grade->id;

            $criteria->update_students_value($value);
            $criteria->save_student($qualID, false); # Save straight away so we can check the parent and loop through all children's awards         


        }
        
        // Else we're using a select menu to pick the value
        // No need to check grading here, since could still be P only but doing advanced and doing non-met value
        elseif ($method == 'select')
        {
                        
            $criteria->set_user($USER->id);
            $criteria->set_date();
            $criteria->update_students_value($value);
            
            // Set award date back to 0 if value is -1 - no award
            if ($value == -1){
                $criteria->set_award_date( 0 );
            }
            
            $criteria->save_student($qualID, false); # Save straight away so we can check the parent and loop through all children's awards         
              
        }
        
        
        // If we just updated it to a value that is not met, get rid of any award date
        // If the award was not met (e.g. anything but Achieved) reset the award date to null
        if( !$criteria->get_student_value()->is_criteria_met_bool() )
        {
            $criteria->set_award_date( 0 );
            $criteria->save_student($qualID, false);
        }
        
        
        
        
    }
    
    
    if ($unit || $criteria)
    {
        
        // Try to calculate the unit award
        if ($unit)
        {
            
            $changed = false;
            
            //has the unit award altered? do we need to calculate the qual award?
            $oldUnitAward = $unit->get_user_award();
            if($oldUnitAward)
            {
                $oldAward = $oldUnitAward->get_award();
            }
                                    
            if (isset($value))
            {
                $award = $unit->calculate_unit_award($qualID);
                if($award)
                {
                    $newAward = $award->get_award();
                    if($oldAward != $newAward)
                    {
                        $changed = true;
                    }
                }
            }
            
            
            // Unit award response
            $unitAwardID = -1;
            $unitAwardValue = "N/S";
            $unitAwardRank = 0;
            $unitPoints = '-';

            if($award)
            {
                $unitAwardID = $award->get_id();
                $unitAwardValue = $award->get_award();
                $unitAwardRank = $award->get_rank();
                $unitPoints = $unit->get_student_unit_points();
            }
            
            $jsonUnitAward = new stdClass();
            $jsonUnitAward->unitid = $unitID;
            $jsonUnitAward->awardid = $unitAwardID;
            $jsonUnitAward->awardvalue = $unitAwardValue;
            $jsonUnitAward->awardrank = $unitAwardRank;
            $jsonUnitAward->points = $unitPoints;
            $jsonUnitAward->studentid = $studentID;
            $retval->unitaward = $jsonUnitAward;
            
            // Percentage stuff
            if ($qualification->has_percentage_completions()){
                
                $percentageObj = new stdClass();
                $percentageObj->studentid = $studentID;
                $percentageObj->unitid = $unit->get_id();
                $percentageObj->percent = $unit->get_percent_completed();
                $retval->percentage = $percentageObj;
                
            }
            
            // Qual award
            $qualAward = null;
            $calculated = false;
            if($changed && $qualification->has_final_grade())
            {
                //only do it if the value is changing for the criteria
                $calculated = true;
                $qualAward = $qualification->calculate_final_grade();
            }
            if(!$calculated)
            {
                $qualAward = $qualification->get_student_award();
            }
            
            $qualAwardID = -1;
            $qualAwardValue = "N/A";
            if($qualAward)
            {
                $qualAwardID = $qualAward->get_id();
                $qualAwardValue = $qualAward->get_award();
                $qualAwardType = $qualAward->get_type();
            }
                        
            $qualAwardType = 'Predicted';
            
            $jsonQualAward = new stdClass();
            $jsonQualAward->awardid = $qualAwardID;
            $jsonQualAward->awardvalue = $qualAwardValue;
            $jsonQualAward->awardtype = $qualAwardType;

            $retval->qualaward = $jsonQualAward;
            $retval->time = date('H:i:s');
            
        }
        
    }
    
    
    
    $qualArray = $sessionQuals[$studentID];
    if(array_key_exists($qualID, $qualArray))
    {
        $qualObject = $qualArray[$qualID];
    }
    else 
    {
        $qualObject = new stdClass();
    }

    $qualObject->qualification = $qualification;
    $qualArray[$qualID] = $qualObject;
    $sessionQuals[$studentID] = $qualArray;
    $_SESSION['session_stu_quals'] = urlencode(serialize($sessionQuals));
                    
    echo json_encode( $retval );
    
    
}

elseif($grid == 'unit')
{
        
    $sessionUnits = isset($_SESSION['session_unit'])? 
        unserialize(urldecode($_SESSION['session_unit'])) : array();
    $unit = null;
    $studentUnitFound = false;
    if(array_key_exists($unitID, $sessionUnits))
    {
        $unitObject = $sessionUnits[$unitID];
        $unit = $unitObject->unit;
        $qualArray = $unitObject->qualArray;
        if(array_key_exists($qualID, $qualArray))
        {
            $studentArray = $qualArray[$qualID];
            if(array_key_exists($studentID, $studentArray))
            {
                $studentObject = $studentArray[$studentID];
                $studentUnit = $studentObject->unit;
                if($studentUnit)
                {
                    $studentUnitFound = true;
                }
            }
        } 
    }
    //will be used later
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELCRITERIA;
    $loadParams->loadAward = true;
    if(!$studentUnitFound)
    {
        $studentUnit = Unit::get_unit_class_id($unitID, $loadParams);
        if($studentUnit)
        {
            $studentUnit->load_student_information($studentID, $qualID, $loadParams);
        }
    }
        
    if($studentUnit)
    {
        $criteria = $studentUnit->get_single_criteria($criteriaID);
                
        if($criteria)
        {    
            // If the method is "check" that means it's a checkbox
            if ($method == 'check' && $criteria->get_grading() == 'P')
            {

                // Get the pass value
                $grades = $criteria->get_met_values();
                $grade = array_shift($grades);

                // If the value coming from the form is 0, then set this to -1 as we have unchecked it
                // otherwise if it's 1, set it to the id of this value
                $value = ($value == 0) ? -1 : $grade->id;


                // Set info
                $criteria->set_user($USER->id);
                $criteria->set_date();
                $criteria->update_students_value($value);
                $criteria->save_student($qualID, false); # Save straight away so we can check the parent and loop through all children's awards         

            }
            
            elseif ($method == 'date' && $criteria->get_grading() == 'DATE')
            {
                
                // If it's a date thingy, we're going to set the actual award as Pass and the setdate as the date
                $criteria->set_user($USER->id);
                $criteria->set_date_set( strtotime($value) );
                $criteria->set_date_updated( strtotime($value) );
                
                // Get the pass value for this qualtype
                $grades = $criteria->get_met_values();
                $grade = array_shift($grades);
                $value = $grade->id;
                
                $criteria->update_students_value($value);
                $criteria->save_student($qualID, false); # Save straight away so we can check the parent and loop through all children's awards         
                
                
            }

            // Else we're using a select menu to pick the value
            // No need to check grading here, since could still be P only but doing advanced and doing non-met value
            elseif ($method == 'select')
            {

                $criteria->set_user($USER->id);
                $criteria->set_date();
                $criteria->update_students_value($value);
                $criteria->save_student($qualID, false); # Save straight away so we can check the parent and loop through all children's awards         
                
            }
            
        }
        
        $retval->studentid = $studentID;
        $retval->valueid = $value;
        if($studentUnit || $criteria)
        {
            $changed = false;
            $oldAward = '';
            //TODO SHould also update who updated it. 

            $award = null;
            if($studentUnit)
            {
                //has the unit award altered? do we need to calculate the qual award?
                $oldUnitAward = $studentUnit->get_user_award();
                if($oldUnitAward)
                {
                    $oldAward = $oldUnitAward->get_award();
                }

                ///only do it if the value is change for the criteria
                if($value)
                {
                    $award = $studentUnit->calculate_unit_award($qualID);
                    if($award)
                    {
                        $newAward = $award->get_award();
                        if($oldAward != $newAward)
                        {
                            $changed = true;
                        }
                    }
                }
            }
                        
             // Qual award
            $qualAward = null;
            $calculated = false;
            
            $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
            $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
            if($qualification)
            {
                $qualification->load_student_information($studentID, $loadParams);
            }
            
            
            if($changed && $qualification->has_final_grade())
            {
                //only do it if the value is changing for the criteria
                $calculated = true;
                $qualAward = $qualification->calculate_predicted_grade();
            }
            if(!$calculated)
            {
                $qualAward = $qualification->get_student_award();
            }
            

            //qualAward is an object of
            //minAward
            //maxAward
            //avgAward
            $retval->unitid = $unitID;

            $unitAwardID = -1;
            $unitAwardValue = "N/S";
            $unitAwardRank = 0;
            
            $qualAwardID = -1;
            $qualAwardValue = "N/A";

            if($award)
            {
                $unitAwardID = $award->get_id();
                $unitAwardValue = $award->get_award();
                $unitAwardRank = $award->get_rank();
            }


            if($qualAward)
            {
                $qualAwardID = $qualAward->get_id();
                $qualAwardValue = $qualAward->get_award();
                $qualAwardType = $qualAward->get_type();
            }
                        
            $qualAwardType = 'Predicted';
            
            $jsonUnitAward = new stdClass();
            $jsonUnitAward->unitid = $unitID;
            $jsonUnitAward->awardid = $unitAwardID;
            $jsonUnitAward->awardvalue = $unitAwardValue;
            $jsonUnitAward->awardrank = $unitAwardRank;
            $jsonUnitAward->studentid = $studentID;
            $retval->unitaward = $jsonUnitAward;

            $jsonQualAward = new stdClass();
            $jsonQualAward->awardid = $qualAwardID;
            $jsonQualAward->awardvalue = $qualAwardValue;
            $jsonQualAward->awardtype = $qualAwardType;
            
            
            // Percentage stuff
            if ($qualification->has_percentage_completions()){
                
                $percentageObj = new stdClass();
                $percentageObj->studentid = $studentID;
                $percentageObj->unitid = $studentUnit->get_id();
                $percentageObj->percent = $studentUnit->get_percent_completed();
                $retval->percentage = $percentageObj;
                
            }
            

            $retval->qualaward = $jsonQualAward;
            $retval->time = date('H:i:s');
                        
        }

    //                // Get the logs for this qual & student
    //                $user = get_record_select("user", "`id` = '{$studentID}'", "username");
    //                $params['qualID'] = $qualID;
    //                $params['student'] = $user->username;
    //                $params['lastID'] = (isset($_SESSION['lastLogID'][$qualID])) ? $_SESSION['lastLogID'][$qualID] : 0;
    //                $logXml = Log::get_grid_xml($params);
    //                                
    //                $retval .= $logXml;
    //                                
    //                $retval .= "<error>{$GLOBALS['AJAX_ERROR']}</error>";

        
        if(array_key_exists($unitID, $sessionUnits))
        {
            $unitObject = $sessionUnits[$unitID];
            $unit = $unitObject->unit;
            $qualArray = $unitObject->qualArray;
        }
        else
        {
            //it hasnt been loaded into the session before! (can it even get here if this is the case?)
            //then we need to add it
            $unitObject = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELCRITERIA;
            $unitObject->unit = Unit::get_unit_class_id($unitID, $loadParams);
            $qualArray = array();
        }
        if(array_key_exists($qualID, $qualArray))
        {
            $studentArray = $qualArray[$qualID];
        }
        else
        {
            $studentArray = array();
        }
                
        if(array_key_exists($studentID, $studentArray))
        {
            $studentObject = $studentArray[$studentID];
        }
        else
        {
            $studentObject = $DB->get_record_sql("SELECT * FROM {user} WHERE id = ?", array($studentID));
        }
        $studentObject->unit = $studentUnit;
        $studentArray[$studentID] = $studentObject;
        $qualArray[$qualID] = $studentArray;
        $unitObject->qualArray = $qualArray;
        $sessionUnits[$unitID] = $unitObject;
        $_SESSION['session_unit'] = urlencode(serialize($sessionUnits));

        echo json_encode( $retval );
    }
    else
    {
        echo json_encode("No Unit Loaded");
    }
    
}

