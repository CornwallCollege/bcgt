<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

global $COURSE, $CFG, $PAGE, $OUTPUT;
require_once('../../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
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


$PAGE->set_context($context);
require_capability('block/bcgt:transferstudentsunits', $context);
$tab = optional_param('page', 1, PARAM_INTEGER);
$url = '/blocks/bcgt/forms/my_dashboard.php';
$PAGE->set_url($url, array('page' => $tab));
$PAGE->set_title(get_string('transferstudentsunits', 'block_bcgt'));
$PAGE->set_heading(get_string('transferstudentsunits', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
//$PAGE->navbar->add(get_string('bcgtmydashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string('transferstudentsunits', 'block_bcgt'), $CFG->wwwroot . '/blocks/bcgt/forms/transfer_units_new.php','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.inittransferunits', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();

$fromQualID = optional_param('fromQualID', 0, PARAM_INT);
$toQualID = optional_param('toQualID', 0, PARAM_INT);
$units = optional_param_array('units', false, PARAM_INT);
$students = optional_param_array('students', false, PARAM_INT);
$fromQual = false;
$toQual = false;

if ($fromQualID){
    $fromQual = Qualification::get_qualification_class_id($fromQualID);
}

if ($toQualID){
    $toQual = Qualification::get_qualification_class_id($toQualID);
}

$loadUnitParams = new stdClass();
$loadUnitParams->loadLevel = Qualification::LOADLEVELCRITERIA;
$loadUnitParams->loadAward = true;


echo $OUTPUT->header();


// Final stage - Transfer of data
if ($students && $units && $fromQual && $toQual && isset($_POST['submit_transfer']))
{
    
    echo "<div class='cmdoutput'>";
            
        echo "Transfering data from {$fromQual->get_display_name()} to {$toQual->get_display_name()}...<br><br>";
        
        foreach($students as $sID)
        {
        
            foreach($units as $uID)
            {

                $unit = Unit::get_unit_class_id($uID, $loadUnitParams);
                echo "Loaded student - " . bcgt_fullname($sID) . "...<br>";
                
                // Record for the new qual?
                $toUnitData = $DB->get_record("block_bcgt_user_unit", array("userid" => $sID, "bcgtunitid" => $uID, "bcgtqualificationid" => $toQualID));
                
                // Do they have a unit record on the old qual?
                $fromUnitData = $DB->get_record("block_bcgt_user_unit", array("userid" => $sID, "bcgtunitid" => $uID, "bcgtqualificationid" => $fromQualID));
                if ($fromUnitData)
                {
                    
                    echo "Loaded unit data ({$unit->get_display_name()}) for qualification - {$fromQual->get_display_name()}...<br>";
                    
                    // Set the user unit record for the new qual to that of the old qual, to overwrite it
                    if ($toUnitData)
                    {
                        
                        // Archive one we are overwriting
                        $DB->execute( "INSERT INTO {block_bcgt_user_unit_his} 
                                       (bcgtuserunitid, userid, bcgtqualificationid, bcgtunitid, bcgttypeawardid, comments, dateupdated, userdefinedvalue, bcgtvalueid, setbyuserid, updatedbyuserid, dateset, studentcomments) 
                                       SELECT id, userid, bcgtqualificationid, bcgtunitid, bcgttypeawardid, comments, dateupdated, userdefinedvalue, bcgtvalueid, setbyuserid, updatedbyuserid, dateset, studentcomments FROM {block_bcgt_user_unit} WHERE id = ?", array($toUnitData->id) );

                        echo "Archived existing user unit data...<br>";

                        $toRecordID = $toUnitData->id;
                        $toUnitData = $fromUnitData;
                        $toUnitData->id = $toRecordID;
                        $toUnitData->bcgtqualificationid = $toQualID;
                        
                        // Update
                        $DB->update_record("block_bcgt_user_unit", $toUnitData);
                        
                        echo "Updated user unit record for qualification {$toQual->get_name()} with data from {$fromQual->get_name()}...<br>";
                        
                        // Criteria
                        $criteria = $unit->get_criteria();
                        if ($criteria)
                        {
                            
                            foreach($criteria as $crit)
                            {
                                
                                echo "Loaded criterion [{$crit->get_id()}] - {$crit->get_name()}...<br>";

                                $cID = $crit->get_id();
                                $fromCriteriaData = $DB->get_record("block_bcgt_user_criteria", array("userid" => $sID, "bcgtcriteriaid" => $cID, "bcgtqualificationid" => $fromQualID));
                                $toCriteriaData = $DB->get_record("block_bcgt_user_criteria", array("userid" => $sID, "bcgtcriteriaid" => $cID, "bcgtqualificationid" => $toQualID));
                                                                               
                                // Do they have data on the old one?
                                if ($fromCriteriaData)
                                {
                                    
                                    if ($toCriteriaData)
                                    {
                                    
                                        // Archive it
                                        $DB->execute( "INSERT INTO {block_bcgt_user_criteria_his} 
                                                        (bcgtusercriteriaid,
                                                        userid,
                                                        bcgtqualificationid,
                                                        bcgtcriteriaid,
                                                        bcgtrangeid,
                                                        bcgtvalueid,
                                                        setbyuserid,
                                                        dateset,
                                                        dateupdated,
                                                        updatedbyuserid,
                                                        comments,
                                                        bcgtprojectid,
                                                        userdefinedvalue,
                                                        targetdate,
                                                        bcgttargetgradesid,
                                                        bcgttargetbreakdownid,
                                                        flag,
                                                        awarddate) 
                                                    SELECT 
                                                        id,
                                                        userid,
                                                        bcgtqualificationid,
                                                        bcgtcriteriaid,
                                                        bcgtrangeid,
                                                        bcgtvalueid,
                                                        setbyuserid,
                                                        dateset,
                                                        dateupdated,
                                                        updatedbyuserid,
                                                        comments,
                                                        bcgtprojectid,
                                                        userdefinedvalue,
                                                        targetdate,
                                                        bcgttargetgradesid,
                                                        bcgttargetbreakdownid,
                                                        flag,
                                                        awarddate
                                                    FROM {block_bcgt_user_criteria} WHERE id = ?", array($toCriteriaData->id) );

                                            echo "Archived existing user criteria data...<br>";
                                            
                                            $oldValue = new Value($toCriteriaData->bcgtvalueid);
                                            $oldValueName = ($oldValue->get_id() > 0) ? $oldValue->get_short_value() : 'N/A';
                                                                                        
                                            $toRecordID = $toCriteriaData->id;
                                            $toCriteriaData = $fromCriteriaData;
                                            $toCriteriaData->id = $toRecordID;
                                            $toCriteriaData->bcgtqualificationid = $toQualID;

                                            // Update
                                            $DB->update_record("block_bcgt_user_criteria", $toCriteriaData);
                                            
                                            $value = new Value($toCriteriaData->bcgtvalueid);
                                            $valueName = ($value->get_id() > 0) ? $value->get_short_value() : 'N/A';
                                            
                                            echo "Updated user criteria record to: {$valueName}, overriding previous value ({$oldValueName})...<br>";
                                            
                                    
                                    }
                                    else
                                    {
                                        
                                        // Insert new
                                        $toCriteriaData = $fromCriteriaData;
                                        unset($toCriteriaData->id);
                                        $toCriteriaData->bcgtqualificationid = $toQualID;
                                        $DB->insert_record("block_bcgt_user_criteria", $toCriteriaData);
                                        
                                        $value = new Value($toCriteriaData->bcgtvalueid);
                                        $valueName = ($value->get_id() > 0) ? $value->get_short_value() : 'N/A';
                                        
                                        echo "Inserted user criteria record ({$valueName})...<br>";
                                        
                                    }
                                        
                                    
                                    
                                }
                                
                            }
                            
                        }
                        
                        
                        
                    }
                    else
                    {
                        
                        // Insert new
                        $toUnitData = $fromUnitData;
                        unset($toUnitData->id);
                        $toUnitData->bcgtqualificationid = $toQualID;
                        $DB->insert_record("block_bcgt_user_unit", $toUnitData);
                        
                        echo "Inserted user unit record for qualification {$toQual->get_name()} with data from {$fromQual->get_name()}...<br>";
                        
                    }
                                        
                }

            }
        
        }
        
        echo "<br>Completed data transfer...<br>";
    
    echo "</div>";
    
    echo "<form action='{$CFG->wwwroot}/blocks/bcgt/forms/my_dashboard.php?tab=adm' method='post'>";
        echo "<br>";
        echo "<p class='c'><input type='submit' value='Continue' /></p>";
    echo "</form>";
    
}

// Stage 5 - Confirmation of transfer
elseif ($students && $units && $fromQual && $toQual)
{
    
    
    
    
    echo "<small>Transferring from: {$fromQual->get_display_name()}</small><br>";
    echo "<small>Transferring to: {$toQual->get_display_name()}</small><br>";
    echo "<small>Units: ";
        foreach($units as $unitID){
            $unit = Unit::get_unit_class_id($unitID, false);
            echo $unit->get_display_name() . ", ";
        }
    echo "</small><br>";
    echo "<small>Students: ";
        foreach($students as $sID){
            echo bcgt_fullname($sID) . ", ";
        }
    echo "</small><br><br>";
    
    echo "<h2>Transfer Confirmation</h2>";
    echo "<p>Please review the data transfer and confirm that you are happy to go ahead.</p>";
    
    echo "<form action='' method='post'>";
    
    echo "<input type='hidden' name='fromQualID' value='{$fromQualID}' />";
    echo "<input type='hidden' name='toQualID' value='{$toQualID}' />";
    foreach($units as $unitID){
        echo "<input type='hidden' name='units[]' value='{$unitID}' />";
    }
    foreach($students as $sID)
    {
        echo "<input type='hidden' name='students[]' value='{$sID}' />";
    }
    
    echo "<table style='width:90%;margin:auto;'>";
    
     echo "<tr>";
        
            echo "<th style='width:45%;'>From<br><h3>{$fromQual->get_display_name()}</h3></th>";
            echo "<th></th>";
            echo "<th style='width:45%;'>To<br><h3>{$toQual->get_display_name()}</h3></th>";
            
    echo "</tr>";
    
    
    foreach($students as $sID)
    {
        
        echo "<tr>";
            echo "<th colspan='3' class='c blackbg'>".bcgt_fullname($sID)."</td>";
        echo "</tr>";
        
        echo "<tr>";
        
            echo "<td>";
            
                echo "<table class='black-border'>";

                    echo "<tr><th>Unit</th><th>Award</th><th>Criteria</th></tr>";

                    foreach($units as $uID)
                    {

                        $unit = Unit::get_unit_class_id($uID, $loadUnitParams);
                        $unit->load_student_information($sID, $fromQualID, $loadUnitParams);

                        $check = $DB->get_record_sql("SELECT uu.id, a.award
                                                    FROM {block_bcgt_user_unit} uu
                                                    LEFT JOIN {block_bcgt_type_award} a ON a.id = bcgttypeawardid
                                                    WHERE uu.userid = ?
                                                    AND uu.bcgtqualificationid = ?
                                                    AND uu.bcgtunitid = ?", array($sID, $fromQualID, $uID));

                        if ($check)
                        {
                            $unit->award = $check->award;
                            $unit->userUnitID = $check->id;
                        }
                        else
                        {
                            $unit->award = '';
                        }
                        

                        echo "<tr class='TRANSFERHOVER FROMUNIT{$uID}' unitid='{$uID}' studentid='{$sID}'>";
                            echo "<td>{$unit->get_display_name()}</td>";
                            echo "<td>{$unit->award}</td>";
                            echo "<td>";
                            
                                $criteria = $unit->get_criteria();
                                if ($criteria)
                                {
                                    foreach($criteria as $crit)
                                    {
                                                                                
                                        $val = $crit->get_student_value();
                                        if ($val){
                                            $val = " ({$val->get_short_value()})";
                                            echo "<span class='CRITHOVER FROM_S{$sID}Q{$fromQualID}U{$unit->get_id()}C{$crit->get_id()}' cc='S{$sID}Q{$fromQualID}U{$unit->get_id()}C{$crit->get_id()}'>".$crit->get_name() . $val . "</span>, ";
                                        }
                                        
                                    }
                                }
                            
                            echo "</td>";
                        echo "</tr>";

                    }


                echo "</table>";
            
            echo "</td>";
            
            echo "<td></td>";
            
            echo "<td>";
            
                echo "<table class='black-border'>";

                    echo "<tr><th>Unit</th><th>Award</th><th>Criteria</th></tr>";

                    foreach($units as $uID)
                    {

                        $unit = Unit::get_unit_class_id($uID, $loadUnitParams);
                        $unit->load_student_information($sID, $toQualID, $loadUnitParams);

                        $check = $DB->get_record_sql("SELECT uu.id, a.award
                                                    FROM {block_bcgt_user_unit} uu
                                                    LEFT JOIN {block_bcgt_type_award} a ON a.id = bcgttypeawardid
                                                    WHERE uu.userid = ?
                                                    AND uu.bcgtqualificationid = ?
                                                    AND uu.bcgtunitid = ?", array($sID, $toQualID, $uID));

                        if ($check)
                        {
                            $unit->award = $check->award;
                            $unit->userUnitID = $check->id;
                        }
                        else
                        {
                            $unit->award = '';
                        }

                        echo "<tr class='UNIT{$uID}STUD{$sID}'>";
                            echo "<td>{$unit->get_display_name()}</td>";
                            echo "<td>{$unit->award}</td>";
                            echo "<td>";
                            
                                $criteria = $unit->get_criteria();
                                if ($criteria)
                                {
                                    foreach($criteria as $crit)
                                    {
                                                                                
                                        $val = $crit->get_student_value();
                                        if ($val){
                                            $val = " ({$val->get_short_value()})";
                                            echo "<span class='CRITHOVERTO TO_S{$sID}Q{$fromQualID}U{$unit->get_id()}C{$crit->get_id()}' cc='S{$sID}Q{$fromQualID}U{$unit->get_id()}C{$crit->get_id()}'>".$crit->get_name() . $val . "</span>, ";
                                        }
                                        
                                    }
                                }
                            
                            echo "</td>";
                        echo "</tr>";

                    }


                echo "</table>";
            
            echo "</td>";
        
        
        
        echo "</tr>";
        
        
    }
    
           
    
    echo "</table>";
    
    echo "<p class='c'><input type='submit' name='submit_transfer' value='Transfer' /></p>";
    
    echo "</form>";
    
    
}

// Stage 4 - Choose the students
elseif ($units && $fromQual && $toQual)
{
    
    // Find the students who are on both quals
    $chooseUsers = $DB->get_records_sql("SELECT DISTINCT u.*
                                        FROM {block_bcgt_user_qual} uq
                                        INNER JOIN {user} u ON u.id = uq.userid
                                        WHERE uq.bcgtqualificationid = ? 
                                        AND uq.roleid = 5
                                        AND u.id IN (
                                            SELECT userid
                                            FROM {block_bcgt_user_qual}
                                            WHERE bcgtqualificationid = ?
                                            AND roleid = 5 
                                        )
                                        ORDER BY u.lastname ASC, u.firstname ASC", array($fromQualID, $toQualID));
    
    echo "<small>Transferring from: {$fromQual->get_display_name()}</small><br>";
    echo "<small>Transferring to: {$toQual->get_display_name()}</small><br>";
    echo "<small>Units: ";
        foreach($units as $unitID){
            $unit = Unit::get_unit_class_id($unitID, false);
            echo $unit->get_display_name() . ", ";
        }
    echo "</small><br><br>";
    
    echo "<h2>Choose Students</h2>";
    
    echo "<form action='' method='get'>";
    
        echo "<input type='hidden' name='fromQualID' value='{$fromQualID}' />";
        echo "<input type='hidden' name='toQualID' value='{$toQualID}' />";
        foreach($units as $unitID){
            echo "<input type='hidden' name='units[]' value='{$unitID}' />";
        }
        
        if ($chooseUsers)
        {
            echo "<select style='width:100%;' size='20' name='students[]' multiple>";

            foreach ($chooseUsers as $chooseUser)
            {
                echo "<option value='{$chooseUser->id}'>".fullname($chooseUser)."</option>";
            }
            
            echo "</select><br>";
            echo "<input type='button' value='Back' onclick='window.history.back();' />";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;";
            echo "<input type='submit' value='Next' />";
        }
        else
        {
            echo "<p>There are no students who are on both {$fromQual->get_display_name()} and {$toQual->get_display_name()}</p>";
            echo "<input type='button' value='Back' onclick='window.history.back();' />";
        }
        
        
    echo "</form>";
    
}


// Stage 3 - Choose the units
elseif ($toQual)
{
    
    // Get the units which are on both
    $chooseUnits = $DB->get_records_sql("SELECT DISTINCT qu.bcgtunitid, u.name
                                         FROM {block_bcgt_qual_units} qu
                                         INNER JOIN {block_bcgt_unit} u ON u.id = qu.bcgtunitid
                                         WHERE qu.bcgtqualificationid = ? 
                                         AND qu.bcgtunitid IN (
                                            SELECT bcgtunitid
                                            FROM {block_bcgt_qual_units}
                                            WHERE bcgtqualificationid = ?
                                         )", array(
                                             $fromQualID, $toQualID
                                         ));
    
    echo "<small>Transferring from: {$fromQual->get_display_name()}</small><br>";
    echo "<small>Transferring to: {$toQual->get_display_name()}</small><br><br>";
    
    echo "<h2>Choose Units</h2>";
    
    echo "<form action='' method='get'>";
    
        echo "<input type='hidden' name='fromQualID' value='{$fromQualID}' />";
        echo "<input type='hidden' name='toQualID' value='{$toQualID}' />";
        
        if ($chooseUnits)
        {
            echo "<select style='width:100%;' size='20' name='units[]' multiple>";

            foreach ($chooseUnits as $chooseUnit)
            {
                echo "<option value='{$chooseUnit->bcgtunitid}'>{$chooseUnit->name}</option>";
            }
            
            echo "</select><br>";
            echo "<input type='button' value='Back' onclick='window.history.back();' />";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;";
            echo "<input type='submit' value='Next' />";
        }
        else
        {
            echo "<p>There are no units which are on both {$fromQual->get_display_name()} and {$toQual->get_display_name()}</p>";
            echo "<input type='button' value='Back' onclick='window.history.back();' />";
        }
        
        
    echo "</form>";
    
    
}



// Stage 2 - Choose a qual to transfer to
elseif ($fromQual)
{
    
    // All quals
    $allQuals = search_qualification(-1, -1, -1, '', -1, null, -1, false, true); 

    echo "<small>Transferring from: {$fromQual->get_display_name()}</small><br><br>";
    echo "<h2>Transfer TO Qualification:</h2>";

    echo "<form action='' method='get'>";
    
        echo "<input type='hidden' name='fromQualID' value='{$fromQualID}' />";
        echo "<select style='width:100%;' size='20' name='toQualID'>";
            if ($allQuals)
            {
                foreach($allQuals as $qual)
                {
                    if ($qual->id <> $fromQualID)
                    {
                        echo "<option value='{$qual->id}'>".bcgt_get_qualification_display_name($qual, true, ' ')."</option>";
                    }
                }
            }
        echo "</select><br>";
        echo "<input type='button' value='Back' onclick='window.history.back();' />";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<input type='submit' value='Next' />";
    echo "</form>";
    
}
else
{


    // Stage 1 - Choose a qual to transfer from

    // All quals
    $allQuals = search_qualification(-1, -1, -1, '', -1, null, -1, false, true); 

    echo "<h2>Transfer FROM Qualification:</h2>";

    echo "<form action='' method='get'>";
        echo "<select style='width:100%;' size='20' name='fromQualID'>";
            if ($allQuals)
            {
                foreach($allQuals as $qual)
                {
                    echo "<option value='{$qual->id}'>".bcgt_get_qualification_display_name($qual, true, ' ')."</option>";
                }
            }
        echo "</select><br>";
        echo "<input type='submit' value='Next' />";
    echo "</form>";

}


echo $OUTPUT->footer();