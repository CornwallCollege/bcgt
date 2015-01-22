<?php
set_time_limit(0);
require_once '../../../config.php';
require_once $CFG->dirroot . '/blocks/bcgt/lib.php';

require_login();

$qualID = optional_param('qualID', false, PARAM_INT);
$by = optional_param('by', false, PARAM_TEXT);
$students = optional_param_array('students', false, PARAM_INT);
$units = optional_param_array('units', false, PARAM_INT);
$courseID = optional_param('cID', SITEID, PARAM_INT);
$context = context_course::instance($courseID);
$page = optional_param('page', false, PARAM_TEXT);

$loadParams = new stdClass();
$loadParams->loadLevel = Qualification::LOADLEVELALL;
$loadParams->loadAward = true;

if (!has_capability('block/bcgt:exportimportgriddata', $context)){
    print_error('invalid access');
}

if ($qualID)
{
    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
    if (!$qualification) print_error('Invalid qualification');
}



$stageTxt = get_string('impexpdata:stage1', 'block_bcgt');

if ($qualID && $by == 'Students')
{
    $stageTxt = get_string('impexpdata:stage3s', 'block_bcgt');
}
elseif ($qualID && $by == 'Units')
{
    $stageTxt = get_string('impexpdata:stage3u', 'block_bcgt');
}
elseif ($qualID)
{
    $stageTxt = get_string('impexpdata:stage2', 'block_bcgt');
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url($CFG->wwwroot . '/blocks/bcgt/forms/grid_data.php');
$PAGE->set_title(get_string('impexpdata', 'block_bcgt'));
$PAGE->set_heading(get_string('impexpdata', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=track&cID='.$courseID,'title');
$PAGE->navbar->add(get_string('impexpdata', 'block_bcgt'),$CFG->wwwroot.'/blocks/bcgt/forms/grid_data.php', 'title');

load_javascript(true);
load_css(true);

echo $OUTPUT->header();

echo '<div class="tabs"><div class="tabtree">';
echo '<ul class="tabrow0">';
echo '<li><a href="grid_data.php?cID='.$courseID.'&page=export">Export</a></li>';
echo '<li><a href="grid_data.php?cID='.$courseID.'&page=import">Import</a></li>';
echo '</ul>';
echo '</div></div>';

echo "<form action='' method='post' enctype='multipart/form-data'>";

if ($page == 'export')
{

    echo $OUTPUT->heading( get_string('expdata', 'block_bcgt') . ' &gt;&gt; ' . $stageTxt );


    if ($qualID && $by == 'Students' && isset($_POST['export']))
    {

        echo $qualification->get_display_name();

        $fName = $CFG->dataroot . '/bcgt/grid_data_export_'.$USER->id.'.csv';
        $file = fopen($fName, 'w');
        $data = array();

        // Header
        $data[] = array('username', 'unittype', 'unitlevel', 'unitname', 'unitaward', 'criterion', 'criterionaward');

        // Loop through students
        if ($students)
        {
            foreach($students as $sID)
            {

                $qualification->load_student_information($sID, $loadParams);

                // Loop through units
                $units = $qualification->get_units();
                if ($units)
                {
                    foreach($units as $unit)
                    {
                        if ($unit->is_student_doing())
                        {

                            $student = $qualification->get_student();
                            $username = $student->username;

                            $level = $unit->get_level();
                            $level = $level->get_level();

                            $userAward = $unit->get_user_award();
                            $award = '';
                            if ($userAward)
                            {
                                $award = $userAward->get_award();
                            }

                            $data[] = array( $username, $unit->get_unit_type_name(), $level, $unit->get_name(), $award );

                            // Now the criteria
                            $criteria = $unit->get_criteria();
                            if ($criteria)
                            {

                                foreach($criteria as $criterion)
                                {

                                    $award = '';
                                    $userAward = $criterion->get_student_value();
                                    if ($userAward)
                                    {
                                        $award = $userAward->get_short_value();
                                    }
                                    $data[] = array( $username, $unit->get_unit_type_name(), $level, $unit->get_name(), '', $criterion->get_name(), $award );

                                    if ($criterion->get_sub_criteria())
                                    {

                                        foreach($criterion->get_sub_criteria() as $subCriterion)
                                        {

                                            $award = '';
                                            $userAward = $subCriterion->get_student_value();
                                            if ($userAward)
                                            {
                                                $award = $userAward->get_short_value();
                                            }
                                            $data[] = array( $username, $unit->get_unit_type_name(), $level, $unit->get_name(), '', $subCriterion->get_name(), $award );

                                        }

                                    }

                                }

                            }

                        }

                    }

                }

            }

        }

        if ($data)
        {
            foreach($data as $d)
            {
                fputcsv($file, $d);    
            }
        }

        fclose($file);

        // Create download link
        $code = bcgt_create_data_path_code($fName);

        echo "<br><br>";
        echo "<a href='{$CFG->wwwroot}/blocks/bcgt/download.php?f={$code}'><img src='".$OUTPUT->pix_url('f/spreadsheet-24')."' /> Download File</a>";
        echo "<br><br>";
        echo "<input type='submit' name='reset' value='Start Again' />";


    }


    elseif ($qualID && $by == 'Students')
    {

        echo $qualification->get_display_name();

        $students = $qualification->get_students();

        echo "<input type='hidden' name='qualID' value='{$qualID}' />";
        echo "<input type='hidden' name='by' value='{$by}' />";

        echo "<br><br>";

        if ($students)
        {
           
            echo "<select name='students[]' multiple='multiple' size='10'>";

            foreach($students as $student)
            {
                echo "<option value='{$student->id}'>".fullname($student)." ({$student->username})</option>";
            }

            echo "</select>";

            echo "<br><br>";

            echo "<input type='submit' name='export' value='Export' />";
        
        }
        else
        {
            echo "No students found...";
        }

    }

    elseif ($qualID && $by == 'Units' && isset($_POST['export']))
    {

        echo $qualification->get_display_name();

        $fName = $CFG->dataroot . '/bcgt/grid_data_export_'.$USER->id.'.csv';
        $file = fopen($fName, 'w');
        $data = array();

        // Header
        $data[] = array('username', 'unittype', 'unitlevel', 'unitname', 'unitaward', 'criterion', 'criterionaward');

        // Loop through units
        if ($units)
        {
            foreach($units as $uID)
            {

                $unit = $qualification->get_unit($uID);

                // Loop through students
                $students = get_users_on_unit_qual($uID, $qualID);

                if ($students)
                {
                    foreach($students as $student)
                    {

                        $unit->load_student_information($student->id, $qualification->get_id(), $loadParams);

                        if ($unit->is_student_doing())
                        {

                            $username = $student->username;

                            $level = $unit->get_level();
                            $level = $level->get_level();

                            $userAward = $unit->get_user_award();
                            $award = '';
                            if ($userAward)
                            {
                                $award = $userAward->get_award();
                            }

                            $data[] = array( $username, $unit->get_unit_type_name(), $level, $unit->get_name(), $award );

                            // Now the criteria
                            $criteria = $unit->get_criteria();
                            if ($criteria)
                            {

                                foreach($criteria as $criterion)
                                {

                                    $award = '';
                                    $userAward = $criterion->get_student_value();
                                    if ($userAward)
                                    {
                                        $award = $userAward->get_short_value();
                                    }
                                    $data[] = array( $username, $unit->get_unit_type_name(), $level, $unit->get_name(), '', $criterion->get_name(), $award );

                                    if ($criterion->get_sub_criteria())
                                    {

                                        foreach($criterion->get_sub_criteria() as $subCriterion)
                                        {

                                            $award = '';
                                            $userAward = $subCriterion->get_student_value();
                                            if ($userAward)
                                            {
                                                $award = $userAward->get_short_value();
                                            }
                                            
                                            $data[] = array( $username, $unit->get_unit_type_name(), $level, $unit->get_name(), '', $subCriterion->get_name(), $award );

                                        }

                                    }

                                }

                            }

                        }

                    }

                }

            }

        }

        if ($data)
        {
            foreach($data as $d)
            {
                fputcsv($file, $d);    
            }
        }

        fclose($file);

        // Create download link
        $code = bcgt_create_data_path_code($fName);

        echo "<br><br>";
        echo "<a href='{$CFG->wwwroot}/blocks/bcgt/download.php?f={$code}'><img src='".$OUTPUT->pix_url('f/spreadsheet-24')."' /> Download File</a>";
        echo "<br><br>";
        echo "<input type='submit' name='reset' value='Start Again' />";


    }

    elseif ($qualID && $by == 'Units')
    {

        echo $qualification->get_display_name();

        $units = $qualification->get_units();
        $unitSorter = new UnitSorter();
        usort($units, array($unitSorter, "Comparison"));       

        echo "<input type='hidden' name='qualID' value='{$qualID}' />";
        echo "<input type='hidden' name='by' value='{$by}' />";

        echo "<br><br>";

        if ($units)
        {

            echo "<select name='units[]' multiple='multiple' size='10'>";

            foreach($units as $unit)
            {
                echo "<option value='{$unit->get_id()}'>{$unit->get_display_name()}</option>";
            }

            echo "</select>";

            echo "<br><br>";

            echo "<input type='submit' name='export' value='Export' />";
        
        }
        else
        {
            echo "No units found...";
        }


    }

    // Qual has been selected
    elseif ($qualID)
    {

        echo $qualification->get_display_name();

        echo "<input type='hidden' name='qualID' value='{$qualID}' />";

        echo "<br><br>";
        echo "<input type='submit' name='by' value='Students' />";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<input type='submit' name='by' value='Units' />";

    }

    else
    {

        // Choose qualification
        $quals = search_qualification();

        echo "<select name='qualID' multiple='multiple' size='20'>";

        if ($quals)
        {
            foreach($quals as $result)
            {

                if (isset($result->isbespoke)){

                    $display = "{$result->displaytype} ";
                    if ($result->level > 0){
                        $display .= get_string('level', 'block_bcgt') . " {$result->level} ";
                    }
                    $display .= $result->subtype . " ";
                    $display .= $result->name;

                    echo "<option value='$result->id'>{$display}</option>";
                } else {
                    echo "<option value='$result->id'>$result->type $result->trackinglevel ".
                    "$result->subtype $result->name </option>";
                }

            }
        }

        echo "</select>";

        echo "<br><br>";

        echo "<input type='submit' name='next' value='Next' />";

    }

}
elseif ($page == 'import' && $qualID)
{
    
    $output = '';
    
    echo $OUTPUT->heading( get_string('impdata', 'block_bcgt') . ' :: ' . get_string('impdata:stage2', 'block_bcgt') );
    echo $qualification->get_display_name() . '<br><br>';
            
    if (isset($_FILES['upload']))
    {
        
        $f = $_FILES['upload'];
        $file = fopen($f['tmp_name'], 'r');
        $row = fgetcsv($file);
        $header = array('username', 'unittype', 'unitlevel', 'unitname', 'unitaward', 'criterion', 'criterionaward');
        
        if ( array_diff($header, $row) )
        { 
            print_error('Invalid header. Expected: ' . implode(', ', $header));
        }
        
        $unitArray = array();
        
        while ($row = fgetcsv($file))
        {
            
            $username = $row[0];
            $unitType = $row[1];
            $unitLevel = $row[2];
            $unitName = $row[3];
            $unitAward = @$row[4];
            $criterionName = @$row[5];
            $criterionValue = @$row[6];
            
            $student = $DB->get_record("user", array("username" => $username));
            if (!$student)
            {
                $output .= "Cannot find user ($username). Skipping to next row.<br>";
                continue;
            }
            
            if (isset($unitArray[$unitType . '_' . $unitLevel . '_' . $unitName]))
            {
                $unit = $unitArray[$unitType . '_' . $unitLevel . '_' . $unitName];
            }
            else
            {
                
                $sql = "SELECT COUNT(u.id)
                        FROM {block_bcgt_unit} u
                        INNER JOIN {block_bcgt_type} t ON t.id = u.bcgttypeid
                        LEFT JOIN {block_bcgt_level} l ON l.id = u.bcgtlevelid
                        where t.type = ? AND u.name = ?";
                
                $params = array($unitType, $unitName);
                
                if (strlen($unitLevel) > 0)
                {
                    $sql .= " AND l.trackinglevel = ? ";
                    $params[] = $unitLevel;
                }
                
                
                // Get the unit based on those things
                $cnt = $DB->count_records_sql($sql, $params);            
                if ($cnt <> 1) print_error("Found ({$cnt}) records when searching for unit ({$unitName}). Cannot continue.");

                // Okay, carry on
                $sql = "SELECT u.*
                        FROM {block_bcgt_unit} u
                        INNER JOIN {block_bcgt_type} t ON t.id = u.bcgttypeid
                        LEFT JOIN {block_bcgt_level} l ON l.id = u.bcgtlevelid
                        where t.type = ? AND u.name = ?";
                
                $params = array($unitType, $unitName);
                
                if (strlen($unitLevel) > 0)
                {
                    $sql .= " AND l.trackinglevel = ? ";
                    $params[] = $unitLevel;
                }

                $unitRecord = $DB->get_record_sql($sql, $params);
                $unit = $qualification->get_unit($unitRecord->id);
                if (!$unit) print_error('Invalid unit');

                // Put in array so don't have to do all this every time
                $unitArray[$unitType . '_' . $unitLevel . '_' . $unitName] = $unit;
                
            }           
            
            $unit->load_student_information($student->id, $qualification->get_id(), $loadParams);
                        
            // Is the student doing this unit?
            if ($unit->is_student_doing())
            {
                                
                // Unit award
                if (strlen($unitAward) > 0)
                {
                    
                    // Get id of award
                    $awardRecord = $DB->get_record("block_bcgt_type_award", array("bcgttypeid" => Unit::get_unit_tracking_type($unit->get_id()), "award" => $unitAward));
                    $awardID = ($awardRecord) ? $awardRecord->id : -1;
                    
                    // Update the user's award
                    $award = Award::get_award_id($awardID);
                    $unit->set_user_award($award);
                    $unit->save_student($qualID);
                    $output .= "Updated unit award for ".fullname($student)." ($student->username) to {$award->get_award()}<br>";
                    
                }
                
                // Criteria
                if (strlen($criterionName) > 0)
                {
                    
                    $criterion = $unit->get_single_criteria(-1, $criterionName);
                    
                    // Get id of award
                    $awardID = $qualification->get_value_id($criterionValue, $unit->get_typeID());                    
                    if (!$awardID) $criterionValue = 'N/A';
                    
                    // Set info
                    $criterion->set_user($USER->id);
                    $criterion->set_date();
                    $criterion->update_students_value($awardID);
                    $criterion->save_student($qualID, false);       
                    $output .= "Updated {$criterion->get_name()} award for ".fullname($student)." ($student->username) to {$criterionValue}<br>";
                    
                    
                }
                
            }
            
            
            
        }
                
        fclose($file);
        unlink($f['tmp_name']);
        
        echo "<div class='cmdoutput'>";
            echo $output;
        echo "</div>";
        echo "<br><br>";
        
    }
    
    echo "<input type='hidden' name='qualID' value='{$qualID}' />";
    echo "<input type='file' name='upload' />";
    echo "<br><br>";
    echo "<input type='submit' name='import' value='Import' />";
    
    
}
elseif ($page == 'import')
{
    
    echo $OUTPUT->heading( get_string('impdata', 'block_bcgt') . ' :: ' . get_string('impdata:stage1', 'block_bcgt') );
    echo get_string('impdata:stage1:desc', 'block_bcgt') . '<br>';
    
    // Choose qualification
    $quals = search_qualification();

    echo "<select name='qualID' multiple='multiple' size='20'>";

    if ($quals)
    {
        foreach($quals as $result)
        {

            if (isset($result->isbespoke)){

                $display = "{$result->displaytype} ";
                if ($result->level > 0){
                    $display .= get_string('level', 'block_bcgt') . " {$result->level} ";
                }
                $display .= $result->subtype . " ";
                $display .= $result->name;

                echo "<option value='$result->id'>{$display}</option>";
            } else {
                echo "<option value='$result->id'>$result->type $result->trackinglevel ".
                "$result->subtype $result->name </option>";
            }

        }
    }

    echo "</select>";

    echo "<br><br>";

    echo "<input type='submit' name='next' value='Next' />";
    
}

echo "</form>";


echo $OUTPUT->footer();
exit;