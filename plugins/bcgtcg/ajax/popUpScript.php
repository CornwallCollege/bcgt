<?php


header("Content-Type: text/html; charset=utf-8");
require_once '../../../../../config.php';
require_once('../../../lib.php');
require_once '../lib.php';
require_cg();
require_login();

$type = optional_param('type', null, PARAM_TEXT);
$params = optional_param_array('params', array(), PARAM_TEXT);

switch($type)
{
    
    case "observation":
        
        if(!isset($params['rangeID']) || !isset($params['studentID']) || !isset($params['qualID']) || !isset($params['grid'])) exit;
        
        $student = $DB->get_record("user", array("id" => $params['studentID']));
        if(!$student){
            echo " alert('Please select a valid student'); ";
            exit;
        }

        // Make sure qual exists
        $lvl = new stdClass();
        $lvl->loadLevel = Qualification::LOADLEVELCRITERIA;
        
        $qualification = Qualification::get_qualification_class_id($params['qualID'], $lvl);
        if (!$qualification) exit;
        
        // Make sure range exists
        $range = new Range( $params['rangeID'] );
        $range->load_student_information($params['studentID'], $params['qualID']);
        if (!$range->is_valid()) exit;
                        
        // Check student is taking unit        
        $unit = Unit::get_unit_class_id($range->unitid, $lvl);
        if (!$unit) exit;
        
        $unit->load_student_information($params['studentID'], $params['qualID'], $lvl);
        if (!$unit->is_student_doing()) exit;
        
                       
        // Build a table of crtiera/range
        $output = <<<JS
                
        // Content
        popup.set_title("{$range->name}");
        var subtitle =  "<span style='font-size:14pt;color:#023561;'>{$qualification->get_display_name()}</span><br>";
            subtitle += "<span style='font-size:12pt;color:#023561;'>{$unit->get_name()}</span><br><br>";
            subtitle += "<span style='font-size:14pt;color:#0055A8;'>{$student->username} : {$student->firstname} {$student->lastname}</span>";    
        popup.set_sub_title(subtitle);    
        popup.set_content("{$range->build_table($params['grid'])}");
        popup.open();
        
        $('.datePickerRange').click( function(){
            tmpDate = $(this).val();
        });
        
JS;
        
        echo $output;
        
        exit;
        
    break;
    
    case 'outcome':
                
        if(!isset($params['criteriaID']) || !isset($params['unitID']) || !isset($params['studentID']) || !isset($params['qualID']) || !isset($params['grid'])) exit;
       
        $lvl = new stdClass();
        $lvl->loadLevel = Qualification::LOADLEVELMIN;
        $lvl->loadAward = true;
                
        $qualification = Qualification::get_qualification_class_id($params['qualID'], $lvl);
        if(!$qualification) exit;
        
        $lvl->loadLevel = Qualification::LOADLEVELALL;

        
        // Check student is taking unit
        $unit = Unit::get_unit_class_id($params['unitID'], $lvl);
        
        $unit->load_student_information($params['studentID'], $params['qualID'], $lvl);
        
        if(!$unit || !$unit->is_student_doing()) exit;
        
        
        $criteria = $unit->get_single_criteria($params['criteriaID']);
        
        if(!$criteria) exit;
                
        $student = $DB->get_record("user", array("id" => $params['studentID']));
        if(!$student){
            echo " alert('Please select a valid student'); ";
            exit;
        }
        
        $details = preg_replace("/(\r|\n)/", " ", $criteria->get_details());
        
        // Build a table of crtiera/range
        $output = <<<JS
        
        // Content
        popup.set_title("{$criteria->get_name()}");
        var subtitle = "<span><em>{$details}</em></span><br><br>";
            subtitle += "<span style='font-size:14pt;color:#023561;'>{$qualification->get_display_name()}</span><br>";
            subtitle += "<span style='font-size:12pt;color:#023561;'>{$unit->get_name()}</span><br><br>";
            subtitle += "<span style='font-size:14pt;color:#0055A8;'>{$student->username} : {$student->firstname} {$student->lastname}</span>";    
        popup.set_sub_title(subtitle);    
        popup.set_content("{$criteria->build_outcome_table($params['grid'])}");

        $('.datePickerOutcomeObservation').click( function(){
            tmpDate = $(this).val();
        });
        
        popup.open();
        applyTT();
        
JS;
        
        echo $output;
        exit;
        
    break;
    
    
    case 'signoff':
                
        if(!isset($params['unitID']) || !isset($params['studentID']) || !isset($params['qualID'])) exit;
        
        $lvl = new stdClass();
        $lvl->loadLevel = Qualification::LOADLEVELMIN;
        
        $qualification = Qualification::get_qualification_class_id($params['qualID'], $lvl);
        if(!$qualification) exit;
        
        $lvl->loadLevel = Qualification::LOADLEVELCRITERIA;
        
        // Check student is taking unit
        $unit = Unit::get_unit_class_id($params['unitID'], $lvl);
        $unit->load_student_information($params['studentID'], $params['qualID'], $lvl);
        
        if(!$unit || !$unit->is_student_doing()) exit;
        
        $student = $DB->get_record("user", array("id" => $params['studentID']));
        if(!$student){
            echo " alert('Please select a valid student'); ";
            exit;
        }
        
        $unit->load_sign_off_sheets();
        $links = "";
        
        foreach($unit->get_signoff_sheets() as $sheet)
        {
            $col = (isset($params['sheetID']) && $params['sheetID'] == $sheet->id) ? "#CC5500" : "#000";
            $links .= "&nbsp;&nbsp;<a href='#' onclick='loadSignOffSheets({$params['studentID']}, {$params['unitID']}, {$params['qualID']}, {$sheet->id});return false;' style='color:{$col};'>{$sheet->name}</a>&nbsp;&nbsp;";
        }
        
        // Links to all sheets at the top
        // Build a table of crtiera/range
        $output = <<<JS
            popup.set_title("");
            var subtitle = "";
                subtitle += "<span style='font-size:14pt;color:#023561;'>{$qualification->get_display_name()}</span><br>";
                subtitle += "<span style='font-size:12pt;color:#023561;'>{$unit->get_name()}</span><br><br>";
                subtitle += "<span style='font-size:14pt;color:#0055A8;'>{$student->username} : {$student->firstname} {$student->lastname}</span><br><br>";    
                subtitle += "{$links}";
            popup.set_sub_title(subtitle);   
            popup.set_content("");
JS;
                
        // Specific sheet
        if(isset($params['sheetID']))
        {
                        
            // Build sheet info
            $sheet = $DB->get_record("block_bcgt_signoff_sheet", array("id" => $params['sheetID'], "bcgtunitid" => $params['unitID'])); 
            if($sheet)
            {
                                
                $sheet->range = $DB->get_records("block_bcgt_soff_sheet_ranges", array("bcgtsignoffsheetid" => $sheet->id), "id ASC");
                $info = "";
                $info .= "<table id='rangePopupTable'>";
                    $info .= "<tr class='lightpink'><th></th>";
                        for($i = 1; $i <= $sheet->numofobservations; $i++) $info .= "<th class='c'>{$i}</th>";
                    $info .= "</tr>";
                    
                    // Now the ranges
                    if($sheet->range)
                    {
                        foreach($sheet->range as $range)
                        {
                            $info .= "<tr class='lightpink'><th>{$range->name}</th>";
                                
                                for($i = 1; $i <= $sheet->numofobservations; $i++){
                                    $check = $DB->get_record("block_bcgt_user_soff_sht_rgs", array("userid" => $params['studentID'], "bcgtqualificationid" => $params['qualID'], "bcgtsignoffsheetid" => $sheet->id, "bcgtsignoffrangeid" => $range->id, "observationnum" => $i, "value" => 1));
                                    $chkd = ($check) ? "checked" : "";
                                    $info .= "<td class='c'><input type='checkbox' onchange='updateSignOffRangeObservation({$params['studentID']}, {$params['qualID']}, {$params['unitID']}, {$sheet->id}, {$range->id}, {$i}, $(this));return false;' {$chkd} /></td>";
                                }
                                
                            $info .= "</tr>";
                        }
                    }
                    
                $info .= "</table>";

                $output .= <<<JS
                    popup.set_title("{$sheet->name}");
                    popup.set_content("{$info}");    
JS;
            
            }
            
        }
        else
        {
            $output .= <<<JS
                popup.open();
JS;
        }
        
        echo $output;
        exit;
        
        
    break;
    
    case 'sub_criteria':
        
        if(!isset($params['criteriaID']) || !isset($params['unitID']) || !isset($params['studentID']) || !isset($params['qualID']) || !isset($params['grid'])) exit;
       
        $student = $DB->get_record("user", array("id" => $params['studentID']));
        if(!$student){
            echo " alert('Please select a valid student'); ";
            exit;
        }
        
        $lvl = new stdClass();
        $lvl->loadLevel = Qualification::LOADLEVELALL;
        $lvl->loadAward = true;
        
        $qualification = Qualification::get_qualification_class_id($params['qualID'], $lvl);
        if(!$qualification) exit;
        
        // Check student is taking unit
        $unit = Unit::get_unit_class_id($params['unitID'], $lvl);
        $unit->load_student_information($params['studentID'], $params['qualID'], $lvl);
        
        if(!$unit || !$unit->is_student_doing()) exit;
        
        $criteria = $unit->get_single_criteria($params['criteriaID']);
        if(!$criteria) exit;
                        
        $details = preg_replace("/(\r|\n)/", " ", $criteria->get_details());
        
        $subCriteria = $criteria->get_sub_criteria();
        if (!$subCriteria) exit;
        
        // Build a table of crtiera/range
        $output = <<<JS
        
        // Content
        popup.set_title("{$criteria->get_name()}");
        var subtitle = "<span><em>{$details}</em></span><br><br>";
            subtitle += "<span style='font-size:14pt;color:#023561;'>{$qualification->get_display_name()}</span><br>";
            subtitle += "<span style='font-size:12pt;color:#023561;'>{$unit->get_name()}</span><br><br>";
            subtitle += "<span style='font-size:14pt;color:#0055A8;'>{$student->username} : {$student->firstname} {$student->lastname}</span>";    
        popup.set_sub_title(subtitle);    
        popup.set_content("{$criteria->build_sub_criteria_table($params['grid'])}");

        $('.datePickerOutcomeObservation').click( function(){
            tmpDate = $(this).val();
        });
        
        popup.open();
        applyTT();
        
JS;
        
        // Apply TT for this grid
        if ($params['grid'] == 'student'){
            $output .= "applyStudentTT();";
        } elseif ($params['grid'] == 'unit'){
            $output .= "applyUnitTT();";
        }
        
        echo $output;
                
        exit;
        
    break;
    
    
}