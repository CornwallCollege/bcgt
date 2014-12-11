<?php
require_once '../../../../../config.php';
require '../lib.php';

// We're going to be really minimal here with the checks and permissiona and such to improve loading times, deal with it
if (!isset($_POST['action'])) exit;

$action = $_POST['action'];
$params = (isset($_POST['params'])) ? $_POST['params'] : false;

if (!isset($params['grid'])) $params['grid'] = 'student';

require_login();
require_bespoke();

switch($action)
{
    
    case 'update_criteria_value':
        
        if (!isset($params['studentID']) || !isset($params['unitID']) || !isset($params['criteriaID']) || !isset($params['qualID'])) exit;
                
        $studentID = $params['studentID'];
        $criteriaID = $params['criteriaID'];
        $unitID = $params['unitID'];
        $qualID = $params['qualID'];
        $value = (isset($params['value'])) ? $params['value'] : -1;
        
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        if($qualification)
        {
            $qualification->load_student_information($studentID,
                $loadParams);
        }

        // If qualification is still not valid
        if (is_null($qualification) || !$qualification) exit;
        
        $criteria = false;
        $unit = $qualification->get_single_unit($unitID);
        if($unit)
        {
            $criteria = $unit->get_single_criteria($criteriaID);
        }
                
        if (!$criteria) exit;
        
        // For now just do it this way, but will need to change it so diff crit can ahve diff methods, e.g. tick/date/etc..
        $criteria->set_user($USER->id);
        $criteria->set_date();
        $criteria->update_students_value($value);

        // Set award date back to 0 if value is -1 - no award
        if ($value == -1){
            $criteria->set_award_date( 0 );
        }

        $criteria->save_student($qualID, false); # Save straight away so we can check the parent and loop through all children's awards         

        if ($qualification->use_auto_calculations())
        {
            // Recaluclate unit stuff
            $unitAward = $unit->calculate_unit_award($qualID);
            if ($unitAward)
            {
                echo "$('#unitAwardSelect_U{$unitID}_Q{$qualID}_S{$studentID}').val('{$unitAward->get_id()}');";
                echo "$('td#unitAward_U{$unitID}_Q{$qualID}_S{$studentID}').effect( 'highlight', {color: '#ccff66'}, 3000 );";
            }
        }
        
        $qualification->update_single_unit($unit);
        
        if ($qualification->use_auto_calculations())
        {
            // Recaluclate qual stuff
            $predictedAward = $qualification->calculate_predicted_grade();        
            if ($predictedAward)
            {
                echo "$('.predictedAward_S{$studentID}_Q{$qualID}').text('{$predictedAward->get_award()}');";
                echo "$($('.predictedAward_S{$studentID}_Q{$qualID}').parents('td')).effect( 'highlight', {color: '#ccff66'}, 3000 );";
            }


            $qualAward = $qualification->calculate_final_grade();
            if ($qualAward)
            {
                echo "$('.finalAward_S{$studentID}_Q{$qualID}').text('{$qualAward->get_award()}');";
                echo "$($('.finalAward_S{$studentID}_Q{$qualID}').parents('td')).effect( 'highlight', {color: '#ccff66'}, 3000 );";
            }
        }
        
        // Highlight criteria box briefly
        echo "$('td#C_{$criteriaID}U_{$unitID}Q_{$qualID}S_{$studentID}').effect( 'highlight', {color: '#ccff66'}, 3000 );";
        
        // Do percentage thingy
        if ($qualification->has_unit_percentages())
        {
            $percent = $unit->get_percent_completed();
            echo "$('#U{$unitID}S{$studentID}PercentText').html('{$percent}%');";
            echo "$('#U{$unitID}S{$studentID}PercentParent').attr('title', '{$percent}% Complete');";
            echo "$('#U{$unitID}S{$studentID}PercentComplete').css('width', '{$percent}%');";
            echo "$('td#percentComplete_U{$unitID}_Q{$qualID}_S{$studentID}').effect( 'highlight', {color: '#ccff66'}, 3000 );";
        }
        
        exit;
        
    break;
    
    case 'update_unit_award':
        
        if (!isset($params['studentID']) || !isset($params['unitID']) || !isset($params['qualID'])) exit;
        
        $studentID = $params['studentID'];
        $unitID = $params['unitID'];
        $qualID = $params['qualID'];
        $value = (isset($params['value'])) ? $params['value'] : -1;
        
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        if($qualification)
        {
            $qualification->load_student_information($studentID,
                $loadParams);
        }

        // If qualification is still not valid
        if (is_null($qualification) || !$qualification) exit;
        
        $unit = $qualification->get_single_unit($unitID);
        if (!$unit) exit;
        
        $unit->load_student_information($studentID, $qualID, $loadParams);
        
        if ($value > 0){
            $award = BespokeAward::get_award_id($value);
            if ($award->get_grading() <> $unit->get_grading()) exit;
        } else {
            $award = BespokeAward::get_award_id(-1);
        }
                        
        $unit->set_student_award($award);
        $unit->save_student($qualID);
        
        if ($qualification->use_auto_calculations())
        {
            
            // Recaluclate stuff
            $predictedAward = $qualification->calculate_predicted_grade();        
            if ($predictedAward)
            {
                echo "$('.predictedAward_S{$studentID}_Q{$qualID}').text('{$predictedAward->get_award()}');";
                echo "$($('.predictedAward_S{$studentID}_Q{$qualID}').parents('td')).effect( 'highlight', {color: '#ccff66'}, 3000 );";
            }


            $qualAward = $qualification->calculate_final_grade();
            if ($qualAward)
            {
                echo "$('.finalAward_S{$studentID}_Q{$qualID}').text('{$qualAward->get_award()}');";
                echo "$($('.finalAward_S{$studentID}_Q{$qualID}').parents('td')).effect( 'highlight', {color: '#ccff66'}, 3000 );";
            }
        
        }
        
        // Done
        echo "$('td#unitAward_U{$unitID}_Q{$qualID}_S{$studentID}').effect( 'highlight', {color: '#ccff66'}, 3000 );";
        
        exit;
        
    break;
    
    case 'update_qual_award':
        
        if (!isset($params['studentID']) || !isset($params['qualID'])) exit;
        
        $studentID = $params['studentID'];
        $qualID = $params['qualID'];
        $value = (isset($params['value'])) ? $params['value'] : -1;
        
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELMIN;
        $loadParams->loadAward = true;
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        if($qualification)
        {
            $qualification->load_student_information($studentID,
                $loadParams);
        }

        // If qualification is still not valid
        if (is_null($qualification) || !$qualification) exit;
        
        if ($value <= 0)
        {
            $qualification->delete_qualification_award('Final');
        }
        else
        {
            
            $award = $DB->get_record("block_bcgt_bspk_q_grade_vals", array("id" => $value));
            if ($award)
            {
                $awardParams = new stdClass();
                $awardParams->award = $award->grade;
                $awardParams->type = 'Final';
                $qualAward = new BespokeQualificationAward($award->id, $awardParams);
                $qualification->update_qualification_award($qualAward);
            }
            
        }
        
        echo "$('.finalAward_S{$studentID}_Q{$qualID}').parent().effect('highlight', {color: '#ccff66'}, 3000);";      
        echo "$('.finalAward_S{$studentID}_Q{$qualID} .qualAwardSelect').val('{$value}');";
        exit;
        
        
    break;
    
    case 'add_criteria_comment':
        
        if (!isset($params['studentID']) || !isset($params['qualID']) || !isset($params['criteriaID']) || !isset($params['comment']) || !isset($params['element']) || !isset($params['imgID'])) exit;
                        
        $criteria = Criteria::get_correct_criteria_class(BespokeQualification::ID, $params['criteriaID']);
        if (!$criteria) exit;
        
        $criteria->load_student_information($params['studentID'], $params['qualID']);
        
        $params['comment'] = trim($params['comment']);
        $params['comment'] = urldecode($params['comment']);
        
        // Datatables throws a major wobbly with even slightly weird characters, so going to ahve to remove anything that
        // isn't simple
        $params['comment'] = preg_replace("/[^a-z 0-9_\-\'\"\!\:\;\n\r\.\,\?\(\)\/]/i", "", $params['comment']);
        
        if (empty($params['comment'])) $params['comment'] = null;
        
        $criteria->add_comments($params['comment']);
        $criteria->save_students_comments($params['qualID']);
        
        if (is_null($params['comment'])){
            echo "$('#{$params['imgID']}').attr('src', '{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/comment_add.png');";
            echo "$($('#{$params['imgID']}').parents('td')[0]).removeClass('hasComments');";
        } else {
            echo "$('#{$params['imgID']}').attr('src', '{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/comment_edit.png');";
            echo "$($('#{$params['imgID']}').parents('td')[0]).addClass('hasComments');";
        }
        
        $params['comment'] = urlencode($params['comment']);
        $params['comment'] = str_replace("'", "", $params['comment']);
        $params['comment'] = preg_replace('/\v+|\\\[rn]/','<br>',$params['comment']);
        $params['comment'] = str_replace("+", " ", $params['comment']);
        
        echo "$('#{$params['imgID']}').removeClass('addComments').addClass('editComments');";
        echo "$($('#{$params['imgID']}').parents('td')[0]).effect( 'highlight', {color: '#ccff66'}, 3000 );";
        echo "$('#{$params['element']}').find('.dialogCommentText').val( decodeURIComponent('{$params['comment']}') );";
        echo "$('#{$params['element']}').dialog('close');";
        echo "apply_grid_stuff();";
        
        exit;
        
    break;
    
    
    case 'add_unit_comment':
        
        if (!isset($params['studentID']) || !isset($params['qualID']) || !isset($params['unitID']) || !isset($params['comment']) || !isset($params['element']) || !isset($params['imgID'])) exit;
        
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
        
        $unit = Unit::get_unit_class_id($params['unitID'], $loadParams);
        if (!$unit) exit;
        
        $unit->load_student_information($params['studentID'], $params['qualID'], $loadParams);
        
        $params['comment'] = trim($params['comment']);
        $params['comment'] = urldecode($params['comment']);
        
        // Datatables throws a major wobbly with even slightly weird characters, so going to ahve to remove anything that
        // isn't simple
        $params['comment'] = preg_replace("/[^a-z 0-9_\-\'\"\!\:\;\n\r\.\,\?\(\)\/]/i", "", $params['comment']);
        
        if (empty($params['comment'])) $params['comment'] = null;
                
        $unit->update_comments($params['qualID'], urldecode($params['comment']));
        
        if (!isset($params['grid']) || !in_array($params['grid'], array('student', 'unit'))){
            $grid = 'student';
        } else {
            $grid = $params['grid'];
        }
        
        if ($grid == 'unit'){
            $siblingTDNum = 1;
        } else {
            $siblingTDNum = 0;
        }
               
        if (is_null($params['comment'])){
            echo "$('#{$params['imgID']}').attr('src', '{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/comment_add.png');";
            echo "$($($('#{$params['imgID']}').parents('td')[0]).siblings('td')[{$siblingTDNum}]).removeClass('hasComments');";
            echo "$($('#{$params['imgID']}').parents('td')[0]).removeClass('hasComments');";
        } else {
            echo "$('#{$params['imgID']}').attr('src', '{$CFG->wwwroot}/blocks/bcgt/plugins/bcgtbespoke/pix/comment_edit.png');";
            echo "$($($('#{$params['imgID']}').parents('td')[0]).siblings('td')[{$siblingTDNum}]).addClass('hasComments');";
            echo "$($('#{$params['imgID']}').parents('td')[0]).addClass('hasComments');";
        }
        
        $params['comment'] = urlencode($params['comment']);
        $params['comment'] = str_replace("'", "", $params['comment']);
        $params['comment'] = preg_replace('/\v+|\\\[rn]/','<br>',$params['comment']);
        $params['comment'] = str_replace("+", " ", $params['comment']);
        
        
        echo "$('#{$params['imgID']}').removeClass('addComments').addClass('editComments');";
        echo "$($('#{$params['imgID']}').parents('td')[0]).effect( 'highlight', {color: '#ccff66'}, 3000 );";
        echo "$('#{$params['element']}').find('.dialogCommentText').val( decodeURIComponent('{$params['comment']}') );";
        echo "$('#{$params['element']}').dialog('close');";
        echo "apply_grid_stuff();";
        
        exit;
        
    break;
    
    
    
    
}