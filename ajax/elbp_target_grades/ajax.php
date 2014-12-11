<?php
require_once '../../../../config.php';
require_once '../../lib.php';
require_once $CFG->dirroot . '/blocks/elbp/lib.php';

require_login();

$ELBP = ELBP\ELBP::instantiate();

$action = $_POST['action'];
$params = $_POST['params'];

if ($action == 'save')
{
    
    if (!isset($params['qualID']) && !isset($params['courseID'])) exit;
    if (!isset($params['studentID'])) exit;
    
    $qualID = (isset($params['qualID'])) ? $params['qualID'] : null;
    $courseID = (isset($params['courseID'])) ? $params['courseID'] : null;
    $studentID = $params['studentID'];
            
    $access = $ELBP->getUserPermissions($studentID);
        
    $grades = array();
    
    if (elbp_has_capability('block/bcgt:editasptargetgrade', $access))
    {
                
        // Aspirational is set
        if (isset($params['aspirationalgrade']) && !empty($params['aspirationalgrade'])){
            
            if ($params['aspirationalgrade'] == "OTHER")
            {
                $grade = $params['aspirationalcustom'];
                if (!empty($grade))
                {
                    $grades[] = array(
                        "type" => "aspirational",
                        "custom" => $grade
                    );
                }
                
            }
            else
            {
                $explode = explode(":", $params['aspirationalgrade']);
                $grades[] = array(
                    "type" => "aspirational",
                    "grade" => @$explode[2],
                    "recordid" => @$explode[1],
                    "location" => @$explode[0]
                );
                
            }
        }
        else
        {
            // Not set - remove any from DB
            if ($DB->delete_records("block_bcgt_stud_course_grade", array("userid" => $studentID, "qualid" => $qualID, "courseid" => $courseID, "type" => "aspirational"))){
                if (!is_null($qualID)){
                    echo " $('#aspirational_info_{$qualID}').html('-'); ";
                } elseif (!is_null($courseID)){
                    echo " $('#aspirational_info_course_{$courseID}').html('-'); ";
                }
            }
        }
        
        
        if (!isset($params['ignoreTarget']))
        {
                    
            if (isset($params['targetgrade']) && !empty($params['targetgrade'])){

                if ($params['targetgrade'] == "OTHER")
                {

                    $grade = $params['targetcustom'];
                    if (!empty($grade))
                    {
                        $grades[] = array(
                            "type" => "target",
                            "custom" => $grade
                        );
                    }

                }
                else
                {

                    $explode = explode(":", $params['targetgrade']);
                    $grades[] = array(
                        "type" => "target",
                        "grade" => @$explode[2],
                        "recordid" => @$explode[1],
                        "location" => @$explode[0]
                    );

                }

            }
            elseif (isset($params['targetgrade']))
            {
                
                $explode = explode(":", $params['targetgrade']);
                $grades[] = array(
                    "type" => "target",
                    "grade" => @$explode[2],
                    "recordid" => @$explode[1],
                    "location" => @$explode[0]
                );
                
            }
            
        }
        else
        {
            // Not set - remove any from DB
            $check = $DB->get_records("block_bcgt_stud_course_grade", array("userid" => $studentID, "qualid" => $qualID, "courseid" => $courseID, "type" => "target"));
            if ($check){
                if ($DB->delete_records("block_bcgt_stud_course_grade", array("userid" => $studentID, "qualid" => $qualID, "courseid" => $courseID, "type" => "target"))){
                    if (!is_null($qualID)){
                        echo " $('#target_info_{$qualID}').html('-'); ";
                    } elseif (!is_null($courseID)){
                        echo " $('#target_info_course_{$courseID}').html('-'); ";
                    }
                }
            }
        
        
        }
        
        
        
    }
                                
    if ($grades)
    {
        
        foreach($grades as $grade)
        {
            
            // If custom
            if (isset($grade['custom']) && !is_null($courseID))
            {
                
                $ins = new stdClass();
                $ins->courseid = $courseID;
                $ins->grade = $grade['custom'];
                $ins->ranking = 1;
                $grade['recordid'] = $DB->insert_record("block_bcgt_custom_grades", $ins);
                $grade['location'] = 'block_bcgt_custom_grades';
                $grade['grade'] = $grade['custom'];
                
            }
            
            if (!is_null($qualID)){
                $check = $DB->get_record("block_bcgt_stud_course_grade", array("userid" => $studentID, "qualid" => $qualID, "type" => $grade['type']));
            } elseif (!is_null($courseID)){
                $check = $DB->get_record("block_bcgt_stud_course_grade", array("userid" => $studentID, "courseid" => $courseID, "type" => $grade['type']));
            }
                    
            if (!$grade['recordid']){
                
                if ($grade['type'] == 'target' && !is_null($qualID)){
                    
                    echo " $('#qual_{$qualID}_edit_target').hide(); ";
                    echo " $('#qual_{$qualID}_view_target').show(); ";
                    
                }
                elseif ($grade['type'] == 'target' && !is_null($courseID)){
                    
                    echo " $('#course_{$courseID}_edit_target').hide(); ";
                    echo " $('#course_{$courseID}_view_target').show(); ";
                    
                }
                
                continue;
                
            }
            
                        
            if ($check)
            {
                $check->recordid = $grade['recordid'];
                $check->location = $grade['location'];
                $check->setbyuserid = $USER->id;
                $check->settime = time();
                $DB->update_record("block_bcgt_stud_course_grade", $check);
            }
            else
            {
                
                $ins = new stdClass();
                $ins->userid = $studentID;
                $ins->qualid = $qualID;
                $ins->courseid = $courseID;
                $ins->type = $grade['type'];
                $ins->recordid = $grade['recordid'];
                $ins->location = $grade['location'];
                $ins->setbyuserid = $USER->id;
                $ins->settime = time();
                $DB->insert_record("block_bcgt_stud_course_grade", $ins);
                                                
            }
                        
            if (!is_null($qualID)){
                echo " $('#{$grade['type']}_info_{$qualID}').html('<h3>{$grade['grade']}</h3><small>".get_string('setby', 'block_elbp')." ".fullname($USER)."</small>'); ";
            } elseif (!is_null($courseID)){
                echo " $('#{$grade['type']}_info_course_{$courseID}').html('<h3>{$grade['grade']}</h3><small>".get_string('setby', 'block_elbp')." ".fullname($USER)."</small>'); ";
            }
            
        }
        
    }
    
    exit;
    
}