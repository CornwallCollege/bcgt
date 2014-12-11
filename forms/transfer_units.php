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
$PAGE->navbar->add('',$url.'?page='.$tab,'title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.inittransferunits', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();


echo $OUTPUT->header();

echo "<h2>Select Student</h2>";
echo "<div class='bcgt_admin_controls' style='text-align:center;'>";


// If we selected a student, choose their quals
$studentID = optional_param("sid", -1, PARAM_INT);


// Transferingf them
if ($studentID > 0 && isset($_POST['transfer']) && ctype_digit($_POST['transferfrom']) && ctype_digit($_POST['transferto']) && isset($_POST['transferIDs']))
{
    
    $transferFrom = $_POST['transferfrom'];
    $transferTo = $_POST['transferto'];
    
    $transferIDs = $_POST['transferIDs'];
        
    // Loop through ids to transfer and see if they are in current or history user_unit table
    if ($transferIDs)
    {
        foreach ($transferIDs as $id)
        {
            
            // This is the one we are transfering across
            $userUnit = $DB->get_record("block_bcgt_user_unit", array("id" => $id, "userid" => $studentID));
            
            // If doesn't exist, try history table
            if (!$userUnit){
                $userUnit = $DB->get_record("block_bcgt_user_unit_his", array("bcgtuserunitid" => $id, "userid" => $studentID));
            }
            
            if ($userUnit)
            {
                
                // Transfer this across to the other qual - if this unit is on the other qual
                $qualUnit = $DB->get_record("block_bcgt_qual_units", array("bcgtqualificationid" => $transferTo, "bcgtunitid" => $userUnit->bcgtunitid));
                if ($qualUnit)
                {
                    
                    // If the user has a record already, update it, otherwise create a new one
                    // This is the current one we are replacing
                    $userRecord = $DB->get_record("block_bcgt_user_unit", array("userid" => $studentID, "bcgtunitid" => $userUnit->bcgtunitid, "bcgtqualificationid" => $transferTo));
                    
                    // Update
                    if ($userRecord)
                    {
                        
                        // Archive old one
                        $DB->execute( "INSERT INTO {block_bcgt_user_unit_his} 
                                       (bcgtuserunitid, userid, bcgtqualificationid, bcgtunitid, bcgttypeawardid, comments, dateupdated, userdefinedvalue, bcgtvalueid, setbyuserid, updatedbyuserid, dateset, studentcomments) 
                                       SELECT * FROM {block_bcgt_user_unit} WHERE id = ?", array($userRecord->id) );
                        
                        // Update record
                        $userRecord->bcgttypeawardid = $userUnit->bcgttypeawardid;
                        $userRecord->comments = $userUnit->comments;
                        $userRecord->dateupdated = $userUnit->dateupdated;
                        $userRecord->userdefinedvalue = $userUnit->userdefinedvalue;
                        $userRecord->bcgtvalueid = $userUnit->bcgtvalueid;
                        $userRecord->setbyuserid = $userUnit->setbyuserid;
                        $userRecord->updatedbyuserid = $userUnit->updatedbyuserid;
                        $userRecord->dateset = $userUnit->dateset;
                        $DB->update_record("block_bcgt_user_unit", $userRecord);
                        
                        
                        // Update user_criteria
                            // Find all criteria on this unit
                            $criteria = $DB->get_records("block_bcgt_criteria", array("bcgtunitid" => $userUnit->bcgtunitid));
                        
                            // FInd their records in user_criteria (or user_criteria_his) for the transferFrom
                            if ($criteria)
                            {
                                foreach($criteria as $criterion)
                                {
                                    
                                    // Is there a user_criteria record in the main table?
                                    $userCriterionFrom = $DB->get_record("block_bcgt_user_criteria", array("userid" => $studentID, "bcgtcriteriaid" => $criterion->id, "bcgtqualificationid" => $transferFrom));
                                    $history = false;
                                    if (!$userCriterionFrom)
                                    {
                                        $userCriterionFrom = $DB->get_record("block_bcgt_user_criteria_his", array("userid" => $studentID, "bcgtcriteriaid" => $criterion->id, "bcgtqualificationid" => $transferFrom));
                                        $history = true;
                                    }
                                        
                                    // One exists, so let's put it onto the transferTo qual
                                    if ($userCriterionFrom)
                                    {
                                        
                                        // If we have some records already, archive it
                                        $userCriterionTo = $DB->get_record("block_bcgt_user_criteria", array("userid" => $studentID, "bcgtcriteriaid" => $criterion->id, "bcgtqualificationid" => $transferTo));
                                        if ($userCriterionTo)
                                        {
                                            $userCriterionTo->bcgtusercriteriaid = $userCriterionTo->id;
                                            unset($userCriterionTo->id);
                                            $DB->insert_record("block_bcgt_user_criteria_his", $userCriterionTo);
                                            
                                        }
                                        
                                        // Then update the old one to the new qual id
                                        // If was in the main table just update it
                                        if (!$history)
                                        {
                                            $userCriterionFrom->bcgtqualificationid = $transferTo;
                                            $DB->update_record("block_bcgt_user_criteria", $userCriterionFrom);
                                        }
                                        else
                                        {
                                            // Else we will have to insert a new one
                                            unset($userCriterionFrom->id);
                                            unset($userCriterionFrom->bcgtusercriteriaid);
                                            $userCriterionFrom->bcgtqualificationid = $transferTo;
                                            $DB->insert_record("block_bcgt_user_criteria", $userCriterionFrom);
                                        }
                                        
                                    }
                                    
                                    
                                }
                            }
                                                
                        
                    }
                    // Insert
                    else
                    {
                        
                        $userRecord = $userUnit;
                        unset($userRecord->id);
                        if (isset($userRecord->bcgtuserunitid)) unset($userRecord->bcgtuserunitid);
                        $userRecord->bcgtqualificationid = $transferTo;
                        $DB->insert_record("block_bcgt_user_unit", $userRecord);
                        
                        // Insert user_criteria
                        
                        
                    }
                    
                    
                    // Criteria
                    
                    
                    
                    
                    // Get unit name
                    $unit = $DB->get_record("block_bcgt_unit", array("id" => $userUnit->bcgtunitid));
                    echo "{$unit->name} [#{$id}] TRANSFERED SUCCESSFULLY!<br>";
                    echo "<br>";
                    
                    
                }
                
            }
        }
    }
    
    echo "<br><br><a href='{$CFG->wwwroot}/blocks/bcgt/forms/transfer_units.php'>[Transfer More Units]</a>";
    echo "<br><br><a href='{$CFG->wwwroot}/blocks/bcgt/forms/my_dashboard.php?tab=adm&cID=".$courseID."'>[Back to Dashboard]</a>";
    
    
}


elseif ($studentID > 0 && isset($_POST['transferfrom']) && ctype_digit($_POST['transferfrom']) && isset($_POST['transferto']) && ctype_digit($_POST['transferto']))
{
    
    $transferFrom = $_POST['transferfrom'];
    $transferTo = $_POST['transferto'];
    
    // CHeck if qualfrom is in qual table, if not get from qual_his
    $check = $DB->get_record("block_bcgt_qualification", array("id" => $transferFrom));
    
    if ($check)
    {
        $sql = "SELECT DISTINCT q.id, t.type, q.name, l.trackinglevel, s.subtype, 'T' as qualexists
                FROM {block_bcgt_qualification} q
                INNER JOIN {block_bcgt_target_qual} tq ON tq.id = q.bcgttargetqualid
                INNER JOIN {block_bcgt_type} t ON t.id = tq.bcgttypeid
                INNER JOIN {block_bcgt_level} l ON l.id = tq.bcgtlevelid
                INNER JOIN {block_bcgt_subtype} s ON s.id = tq.bcgtsubtypeid
                WHERE q.id = ?";  
    }
    else
    {
        $check = $DB->get_record("block_bcgt_qualification_his", array("bcgtqualificationid" => $transferFrom));
        if ($check)
        {
             $sql = "SELECT DISTINCT q.bcgtqualificationid as id, t.type, q.name, l.trackinglevel, s.subtype, 'F' as qualexists
                FROM {block_bcgt_qualification_his} q
                INNER JOIN {block_bcgt_target_qual} tq ON tq.id = q.bcgttargetqualid
                INNER JOIN {block_bcgt_type} t ON t.id = tq.bcgttypeid
                INNER JOIN {block_bcgt_level} l ON l.id = tq.bcgtlevelid
                INNER JOIN {block_bcgt_subtype} s ON s.id = tq.bcgtsubtypeid
                WHERE q.bcgtqualificationid = ?";  
        }
    }
    
    
    $qualFrom = $DB->get_record_sql($sql, array($transferFrom));
    $qualTo = $DB->get_record_sql("SELECT DISTINCT q.id, t.type, q.name, l.trackinglevel, s.subtype
                FROM {block_bcgt_qualification} q
                INNER JOIN {block_bcgt_target_qual} tq ON tq.id = q.bcgttargetqualid
                INNER JOIN {block_bcgt_type} t ON t.id = tq.bcgttypeid
                INNER JOIN {block_bcgt_level} l ON l.id = tq.bcgtlevelid
                INNER JOIN {block_bcgt_subtype} s ON s.id = tq.bcgtsubtypeid
                WHERE q.id = ?", array($transferTo));
    
    if (!$check || !$qualFrom || !$qualTo)
    {
        echo "<h2 style='color:red;'>No such Qualification</h2>";
        echo "<a href='".$CFG->wwwroot."/blocks/bcgt/forms/transfer_units.php?sid=".$studentID."'>[Back]</a>";
    }
    
    
    elseif ($transferFrom == $transferTo)
    {
        echo "<h2 style='color:red;'>You cannot transfer to the same qualification</h2>";
        echo "<a href='".$CFG->wwwroot."/blocks/bcgt/forms/transfer_units.php?sid=".$studentID."'>[Back]</a>";
    }
    else
    {
    
        
        // Get a list of units that appear on both quals
            // From
            $unitIDsFrom = $DB->get_records_sql("SELECT distinct bcgtunitsid as id
                                                FROM {block_bcgt_qual_units_his}
                                                WHERE bcgtqualificationid = ?

                                                UNION

                                                SELECT distinct bcgtunitid as id
                                                FROM {block_bcgt_qual_units}
                                                WHERE bcgtqualificationid = ?", array($qualFrom->id, $qualFrom->id));
            
            $unitsFrom = array();
            
            // Get the unit info for each
            if ($unitIDsFrom)
            {
                foreach($unitIDsFrom as $unitID)
                {
                    // Check if unit still exists or if it's in the history table
                    $check = $DB->get_record("block_bcgt_unit", array("id" => $unitID->id));
                    if ($check)
                    {
                        $unitsFrom[] = $check;
                    }
                    else
                    {
                        $check = $DB->get_record("block_bcgt_unit_history", array("bcgtunitsid" => $unitID->id));
                        if ($check)
                        {
                            $unitsFrom[] = $check;
                        }
                    }
                }
            }
            
            // Get the awards they've got for these - if one exists get that, else get most recent history record
            if ($unitsFrom)
            {
                foreach($unitsFrom as $unitFrom)
                {
                    
                    $check = $DB->get_record_sql("SELECT uu.id, a.award
                                                FROM {block_bcgt_user_unit} uu
                                                LEFT JOIN {block_bcgt_type_award} a ON a.id = bcgttypeawardid
                                                WHERE uu.userid = ?
                                                AND uu.bcgtqualificationid = ?
                                                AND uu.bcgtunitid = ?", array($studentID, $qualFrom->id, $unitFrom->id));
                    
                    if ($check)
                    {
                        $unitFrom->award = $check->award;
                        $unitFrom->userUnitID = $check->id;
                    }
                    else
                    {
                        
                        // Check history table
                        $check = $DB->get_record_sql("SELECT uu.id, uu.bcgtuserunitid, a.award
                                                    FROM {block_bcgt_user_unit_his} uu
                                                    LEFT JOIN {block_bcgt_type_award} a ON a.id = bcgttypeawardid
                                                    WHERE uu.userid = ?
                                                    AND uu.bcgtqualificationid = ?
                                                    AND uu.bcgtunitid = ?
                                                    ORDER BY id DESC", array($studentID, $qualFrom->id, $unitFrom->id), IGNORE_MULTIPLE);
                        
                        if ($check)
                        {
                            $unitFrom->award = $check->award;
                            $unitFrom->userUnitID = $check->bcgtuserunitid;
                        }
                        
                    }
                    
                    
                }
            }
            
            
            
            
            // Get units on the qual we're transfering to
            $sql = "SELECT uu.id, u.id as unitid, u.name, a.award
                    FROM {block_bcgt_qual_units} qu
                    INNER JOIN {block_bcgt_user_unit} uu ON (uu.bcgtunitid = qu.bcgtunitid AND uu.bcgtqualificationid = qu.bcgtqualificationid)
                    INNER JOIN {block_bcgt_unit} u ON u.id = uu.bcgtunitid
                    LEFT JOIN {block_bcgt_type_award} a ON a.id = uu.bcgttypeawardid
                    WHERE uu.bcgtqualificationid = ? AND uu.userid = ?";
            
            $results = $DB->get_records_sql($sql, array($qualTo->id, $studentID));
            $unitsTo = array();
            if ($results)
            {
                foreach($results as $result)
                {
                    $unitsTo[$result->unitid] = $result;
                }
            }
            
            
        
        
        echo "<form action='' method='post'>";
        echo "<table style='width:98%;margin:auto;'>";

            echo "<tr>";

                echo "<th style='width:50%;'>Transfer from:<br>{$qualFrom->type} {$qualFrom->trackinglevel} {$qualFrom->subtype} {$qualFrom->name}</th>";
                echo "<th style='width:50%;'>Transfer to:<br>{$qualTo->type} {$qualTo->trackinglevel} {$qualTo->subtype} {$qualTo->name}</th>";


            echo "</tr>";

            echo "<tr>";

                echo "<td>";

                    // Get all the user_unit records on this qual
                echo "<table class='black-border'>";
                
                    echo "<tr><th>Unit</th><th>Award</th><th></th></tr>";
                    
                    if ($unitsFrom)
                    {
                        foreach($unitsFrom as $unitFrom)
                        {
                            $award = (isset($unitFrom->award)) ? $unitFrom->award : '-';
                            echo "<tr class='TRANSFERHOVER FROMUNIT{$unitFrom->id}' unitid='{$unitFrom->id}'><td>{$unitFrom->name}</td><td>{$award}</td><td><input type='checkbox' class='transferUnit' name='transferIDs[]' unitid='{$unitFrom->id}' value='{$unitFrom->userUnitID}' /></td></tr>";
                        }
                    }
                    
                echo "</table>";

                echo "</td>";

                echo "<td>";

                
                    echo "<table class='black-border'>";
                    echo "<tr><th>Unit</th><th>Award</th></tr>";
                    
                    if ($unitsTo)
                    {
                        foreach($unitsTo as $unitTo)
                        {
                            $award = (isset($unitTo->award)) ? $unitTo->award : '-';
                            echo "<tr class='UNIT{$unitTo->unitid}'><td>{$unitTo->name}</td><td>{$award}</td></tr>";
                        }
                    }
                    
                    echo "</table>";

                echo "</td>";


            echo "</tr>";

        echo "</table>";
        
        echo "<input type='hidden' name='transferfrom' value='{$qualFrom->id}' /><input type='hidden' name='transferto' value='{$qualTo->id}' />";
        
        echo "<p><input type='submit' name='transfer' style='font-size:14pt;padding:10px;' value='".get_string('transferunits', 'block_bcgt')."' /></p>";
        
        echo "</form>";
    
    }
    
}




elseif ($studentID > 0)
{
    
    $user = $DB->get_record("user", array("id" => $studentID));
    
    echo "<h1>({$user->username}) ".fullname($user)."</h1>";
    echo "<br>";
    
    // Find a distinct list of all the qualifications this student is or was ever on
    $allQuals = bcgt_get_all_users_quals($user->id);
    
    echo "<form action='' method='post'>";
    echo "<table style='width:90%;margin:auto;'>";
    
        echo "<tr>";
        
            echo "<th style='width:40%;'>Transfer Units From This Qualification</th>";
            echo "<th style='width:20%;'></th>";
            echo "<th style='width:40%;'>To This Qualification</th>";
            
        
        echo "</tr>";
    
        echo "<tr>";
        
            echo "<td>";
            
                echo "<select style='width:100%;' size='20' name='transferfrom'>";
                    if ($allQuals)
                    {
                        foreach($allQuals as $qual)
                        {
                            echo "<option value='{$qual->id}'>{$qual->type} {$qual->trackinglevel} {$qual->subtype} {$qual->name}</option>";
                        }
                    }
                echo "</select>";
            
            echo "</td>";
            
            echo "<td style='vertical-align:middle;'><input type='submit' name='selectquals' value='Select' style='font-size:14pt;padding:10px;' /></td>";
            
            echo "<td>";
            
                echo "<select style='width:100%;' size='20' name='transferto'>";
                $currentQuals = get_users_quals($user->id);
                if ($currentQuals)
                {
                    foreach($currentQuals as $qual)
                    {
                        echo "<option value='{$qual->id}'>{$qual->type} {$qual->trackinglevel} {$qual->subtype} {$qual->name}</option>";
                    }
                }
                echo "</select>";
            
            echo "</td>";
            
        
        echo "</tr>";
    
    echo "</table>";
    echo "</form>";
    
    
}
else
{

    // Search for student
    echo "<h3>".get_string('findstudent', 'block_bcgt')."</h3>";
    echo "<br>";

    echo "<form action='' method='post'>";

        echo "<small>".get_string('username')."</small><br>";
        echo "<input type='text' name='usernamesearch' /><br><br>";
        echo "<input type='submit' name='findstudent' value='Search' />";
        echo "<br><br>";

        if (isset($_POST['findstudent']) && !empty($_POST['usernamesearch']))
        {

            $user = $DB->get_record("user", array("username" => $_POST['usernamesearch']));

            echo "<h3>Results</h3>";
            if ($user)
            {
                echo "<a href='{$CFG->wwwroot}/blocks/bcgt/forms/transfer_units.php?sid={$user->id}'>({$user->username}) ".fullname($user)."</a><br>";
            }
            else
            {
                echo "No student found with that username...<br>";
            }

        }

echo "</form>";

}

echo "</div>";
echo $OUTPUT->footer();