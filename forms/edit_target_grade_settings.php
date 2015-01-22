<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * THIS WHOLE FILE IS A WORK IN PROGRESS AND IS CURRENTLY THROWN TOGETHER AS AN INTERIM SOLUTION
 * 
 * Author mchaney@bedford.ac.uk
 */

global $COURSE, $CFG, $PAGE, $OUTPUT;
require_once('../../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
$courseID = optional_param('cID', -1, PARAM_INT);
$id = optional_param('id', -1, PARAM_INT);
$action = optional_param('a', '', PARAM_TEXT);
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
require_capability('block/bcgt:edittargetgradesettings', $context);
$tab = optional_param('page', 1, PARAM_INTEGER);
$url = '/blocks/bcgt/forms/my_dashboard.php';
$PAGE->set_url($url, array('page' => $tab));
$PAGE->set_title(get_string('edittargetgradesettings', 'block_bcgt'));
$PAGE->set_heading(get_string('edittargetgradesettings', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
//$PAGE->navbar->add(get_string('bcgtmydashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add('',$url.'?page='.$tab,'title');
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.inittargetqualsettings', null, true, $jsModule);
load_javascript();

$out = '';
//get all qual families
$families = get_qualification_type_families_used(-1, true);
//if($families)
//{
//    $out .= '<table>';
//    $out .= '<thead><tr><th>'.get_string('type', 'block_bcgt').'</th>';
//    $out .= '<th>'.get_string('level', 'block_bcgt').'</th>';
//    $out .= '<th>'.get_string('subtype', 'block_bcgt').'</th>';
//    $out .= '</tr></thead>';
//    foreach($families AS $family)
//    {
//        //for each family it want to expand and load all of the different possible target quals for
//        //the family. 
//        $out .= '<tr><td>'.$family->family.'</td></tr>';
//    }
//    $out .= '</table>';
//}
$typeID = -1;
$levelID = -1;
$subtypeID = -1;

if($action == 'edit' && $id != -1)
{
    $breakdowns = get_qualification_breakdown_by_targetQual($id);
    $newB = 8;
    if($breakdowns)
    {
        $newB = 3;
    }
    $grades = get_qualification_grades_by_targetQual($id);
    $newG = 8;
    if($grades)
    {
        $newG = 3;
    }
    if(isset($_POST['save']) || isset($_POST['saveRows']))
    {
        //then we need to get them all and update
        //get new ones and save
        //delete blank ones. 
        if($breakdowns)
        {
            //if the grade is blank, delete it
            //else lets save it.
            foreach($breakdowns AS $breakdown)
            {
                if(isset($_POST['bgradeid_'.$breakdown->id]) && $_POST['bgradeid_'.$breakdown->id] != '')
                {
                    $params = new stdClass();
                    $params->bcgttargetqualid= $id;
                    $params->targetgrade = $_POST['bgradeid_'.$breakdown->id];
                    $params->ucaspoints = isset($_POST['bucasid_'.$breakdown->id])? $_POST['bucasid_'.$breakdown->id] : 0;
                    $params->entryscoreupper = isset($_POST['bupperid_'.$breakdown->id])? $_POST['bupperid_'.$breakdown->id] : 0;
                    $params->entryscorelower = isset($_POST['blowerid_'.$breakdown->id])? $_POST['blowerid_'.$breakdown->id] : 0;
                    $params->unitsscoreupper = isset($_POST['bupperunitid_'.$breakdown->id])? $_POST['bupperunitid_'.$breakdown->id] : 0;
                    $params->unitsscorelower = isset($_POST['blowerunitid_'.$breakdown->id])? $_POST['blowerunitid_'.$breakdown->id] : 0;
                    $params->ranking = isset($_POST['brankid_'.$breakdown->id])? $_POST['brankid_'.$breakdown->id] : 0;
                    $breakdown = new Breakdown($breakdown->id, $params);
                    $breakdown->save();
                }
                else
                {
                    Breakdown::delete_breakdown($breakdown->id);
                }
            }
            
        }
        for($i=0;$i<=$newB;$i++)
        {
            if(isset($_POST['bgradeid_n_'.$i]) && $_POST['bgradeid_n_'.$i] != '')
            {
                $params = new stdClass();
                $params->bcgttargetqualid= $id;
                $params->targetgrade = $_POST['bgradeid_n_'.$i];
                $params->ucaspoints = isset($_POST['bucasid_n_'.$i])? $_POST['bucasid_n_'.$i] : 0;
                $params->entryscoreupper = isset($_POST['bupperid_n_'.$i])? $_POST['bupperid_n_'.$i] : 0;
                $params->entryscorelower = isset($_POST['blowerid_n_'.$i])? $_POST['blowerid_n_'.$i] : 0;
                $params->unitsscoreupper = isset($_POST['bupperunitid_n_'.$i])? $_POST['bupperunitid_n_'.$i] : 0;
                $params->unitsscorelower = isset($_POST['blowerunitid_n_'.$i])? $_POST['blowerunitid_n_'.$i] : 0;
                $params->ranking = isset($_POST['brankid_n_'.$i])? $_POST['brankid_n_'.$i] : 0;
                $breakdown = new Breakdown(-1, $params);
                $breakdown->save();
            }    
        }
        if($grades)
        {
            foreach($grades AS $grade)
            {
                if(isset($_POST['ggradeid_'.$grade->id]) && $_POST['ggradeid_'.$grade->id] != '')
                {
                    $params = new stdClass();
                    $params->bcgttargetqualid= $id;
                    $params->grade = $_POST['ggradeid_'.$grade->id];
                    $params->ucaspoints = isset($_POST['gucasid_'.$grade->id])? $_POST['gucasid_'.$grade->id] : 0;
                    $params->upperscore = isset($_POST['gupperid_'.$grade->id])? $_POST['gupperid_'.$grade->id] : 0;
                    $params->lowerscore = isset($_POST['glowerid_'.$grade->id])? $_POST['glowerid_'.$grade->id] : 0;
                    $params->ranking = isset($_POST['grankid_'.$grade->id])? $_POST['grankid_'.$grade->id] : 0;
                    $grade = new TargetGrade($grade->id, $params);
                    $grade->save();
                }
                else
                {
                    TargetGrade::delete_target_grade($grade->id);
                }
            }
        }
        for($i=0;$i<=$newG;$i++)
        {
            //if the grade isnt blank then lets insert it
            if(isset($_POST['ggradeid_n_'.$i]) && $_POST['ggradeid_n_'.$i] != '')
            {
                $params = new stdClass();
                $params->bcgttargetqualid= $id;
                $params->grade = $_POST['ggradeid_n_'.$i];
                $params->ucaspoints = isset($_POST['gucasid_n_'.$i])? $_POST['gucasid_n_'.$i] : 0;
                $params->upperscore = isset($_POST['gupperid_n_'.$i])? $_POST['gupperid_n_'.$i] : 0;
                $params->lowerscore = isset($_POST['glowerid_n_'.$i])? $_POST['glowerid_n_'.$i] : 0;
                $params->ranking = isset($_POST['grankid_n_'.$i])? $_POST['grankid_n_'.$i] : 0;
                $grade = new TargetGrade(-1, $params);
                $grade->save();
            } 
        }
        if(isset($_POST['save']))
        {
            redirect('edit_target_grade_settings.php?cID='.$courseID.'');
        }
        elseif(isset($_POST['saveRows']))
        {
            redirect('edit_target_grade_settings.php?cID='.$courseID.'&a=edit&id='.$id);
        }
    }
    echo $OUTPUT->header();
    $targetQual = get_qualification_targets($id);
    if($targetQual)
    {
        echo "<h3>$targetQual->family $targetQual->type $targetQual->trackinglevel $targetQual->subtype</h3>";
    }
    echo "<p>To delete a Grade simply delete the entry in the first column 'Target Grade' or 'Grade' and then save. 
        The rankings work when the higher the grade the higher the ranking. Not all 
        qualification families and grades need Upper and Lower scores 
        (These are used for Average GCSE Scores and Target Grades). The predicted grades upper and predicted grades lower 
        are used for any qualifications that have unit points that go towards qualification awards. 
        </p>";
    //then we need a form
    echo '<form action="" name="" method="POST"/>';
    echo '<input type="hidden" name="a" value="edit"/>';
    echo '<input type="hidden" name="id" value="'.$id.'"/>';
    echo '<div id="breakdownupdateform">';
    echo '<input type="submit" name="save" value="Save & Return" class="bcgtFormButton" />';
    echo '<input type="submit" name="saveRows" value="Save & Add New Rows" class="bcgtFormButton"/>';
    echo '<h3>Overall Target Grades - <span class="desc">Used in Quals on Entry</span></h3>';
    echo '<table>';
    echo '<tr><th>Target Grade</th><th>Ucas Points</th><th>Score Upper</th><th>Score Lower</th><th>Predicted Points Upper</th><th>Predicted Points Lower</th><th>Ranking</th></tr>';
    if($breakdowns)
    {
        foreach($breakdowns AS $breakdown)
        {
            echo '<tr>';
            echo '<td><input type="text" name="bgradeid_'.$breakdown->id.'" value="'.$breakdown->targetgrade.'"/></td>';
            echo '<td><input type="text" name="bucasid_'.$breakdown->id.'" value="'.$breakdown->ucaspoints.'"/></td>';
            echo '<td><input type="text" name="bupperid_'.$breakdown->id.'" value="'.$breakdown->entryscoreupper.'"/></td>';
            echo '<td><input type="text" name="blowerid_'.$breakdown->id.'" value="'.$breakdown->entryscorelower.'"/></td>';
            echo '<td><input type="text" name="bupperunitid_'.$breakdown->id.'" value="'.$breakdown->unitsscoreupper.'"/></td>';
            echo '<td><input type="text" name="blowerunitid_'.$breakdown->id.'" value="'.$breakdown->unitsscorelower.'"/></td>';
            echo '<td><input type="text" name="brankid_'.$breakdown->id.'" value="'.$breakdown->ranking.'"/></td>';
            echo '</tr>';
        }
    }
    for($i=0;$i<=$newB;$i++)
    {
        echo '<tr>';
        echo '<td><input type="text" name="bgradeid_n_'.$i.'" value=""/></td>';
        echo '<td><input type="text" name="bucasid_n_'.$i.'" value=""/></td>';
        echo '<td><input type="text" name="bupperid_n_'.$i.'" value=""/></td>';
        echo '<td><input type="text" name="blowerid_n_'.$i.'" value=""/></td>';
        echo '<td><input type="text" name="bupperunitid_n_'.$i.'" value=""/></td>';
        echo '<td><input type="text" name="blowerunitid_n_'.$i.'" value=""/></td>';
        echo '<td><input type="text" name="brankid_n_'.$i.'" value=""/></td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
    echo '<div>';
    echo '<h3>Individual Target Grades- <span class="desc">Used in giving CETA\'s</span></h3>';
    echo '<table>';
    echo '<tr><th>Grade</th><th>Ucas Points</th><th>Score Upper</th><th>Score Lower</th><th>Ranking</th></tr>';
    if($grades)
    {
        foreach($grades AS $grade)
        {
            echo '<tr>';
            echo '<td><input type="text" name="ggradeid_'.$grade->id.'" value="'.$grade->grade.'"/></td>';
            echo '<td><input type="text" name="gucasid_'.$grade->id.'" value="'.$grade->ucaspoints.'"/></td>';
            echo '<td><input type="text" name="gupperid_'.$grade->id.'" value="'.$grade->upperscore.'"/></td>';
            echo '<td><input type="text" name="glowerid_'.$grade->id.'" value="'.$grade->lowerscore.'"/></td>';
            echo '<td><input type="text" name="grankid_'.$grade->id.'" value="'.$grade->ranking.'"/></td>';
            echo '</tr>';
        }
    }
    for($i=0;$i<=$newG;$i++)
    {
        echo '<tr>';
        echo '<td><input type="text" name="ggradeid_n_'.$i.'" value=""/></td>';
        echo '<td><input type="text" name="gucasid_n_'.$i.'" value=""/></td>';
        echo '<td><input type="text" name="gupperid_n_'.$i.'" value=""/></td>';
        echo '<td><input type="text" name="glowerid_n_'.$i.'" value=""/></td>';
        echo '<td><input type="text" name="grankid_n_'.$i.'" value=""/></td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
    echo '<input type="submit" name="save" value="Save & Return" class="bcgtFormButton" />';
    echo '<input type="submit" name="saveRows" value="Save & Add New Rows" class="bcgtFormButton" />';
    echo '</form>';
}
else
{
    echo $OUTPUT->header();
    $qualifications = get_qualification_targets(-1, 'family ASC, trackinglevel DESC, subtype ASC', $typeID, $levelID, $subtypeID, true);
    echo "<table align='center' id='bcgtTargetGradeSettingOverview'>";
    echo "<tr>";
        echo "<th></th><th>Family</th><th>Type</th><th>Level</th><th>Sub Type</th><th>Edit</th><th>Breakdowns</th><th>No Quals</th>";//<th>Delete</th>
        foreach($qualifications AS $qual)
        {
            echo "<tr class=\"headingCollapse\"><td><img src='{$CFG->wwwroot}/blocks/bcgt/pix/expandIcon.jpg'></td>";
                echo "<td>$qual->family</td><td>$qual->type</td><td>$qual->trackinglevel</td><td>$qual->subtype</td>
                    <td><a href=\"edit_target_grade_settings.php?id=$qual->id&cID=$courseID&a=edit\">Edit</a></td>";
            $breakdowns = get_qualification_breakdown_by_targetQual($qual->bcgttargetqualid);
            if($breakdowns)
            {
                echo "<td><img src=\"{$CFG->wwwroot}/blocks/bcgt/pix/tick.jpg\"></td>";
            }
            else
            {
                echo "<td><img src=\"{$CFG->wwwroot}/blocks/bcgt/pix/cross.gif\"></td>";
            }	
//            echo "<td><p class='delete' href='edit_target_grade_settings?qID=$qual->id'>Delete</p></td>";
            echo "<td>".(int)$qual->countquals."</td>";
            echo "</tr>";
            echo "<tr class='contentCollapse'><td colspan='9' class='bcgtTargetGradeSettingsExpanded'>
                <table id='bcgtTargetGradeSettingsExpandedCols'><tr><td colspan='4'><h3>Overall Target Grades</h3> <span class='desc'>
                Used in Quals on Entry</span></td><td colspan='4'><h3>Individual 
                Target Grades</h3> <span class='desc'>Used in giving CETAs</span>
                </td></tr>
                <tr><td colspan='4' valign='top'>
                <table align='center' class='bcgtTargetGradeSettingsOverall'>
                <tr><th>TargetGrade</th><th>Ucas Points</th><th>Score Upper</th><th>Score Lower</th><th>Predicted Points Upper</th><th>Predicted Points Lower</th><th>Ranking</th></tr>";
                if($breakdowns)
                {
                    foreach($breakdowns AS $grade)
                    {
                        echo "<tr><td>$grade->targetgrade</td><td>$grade->ucaspoints</td><td>$grade->entryscoreupper</td><td>$grade->entryscorelower</td><td>$grade->unitsscoreupper</td><td>$grade->unitsscorelower</td><td>$grade->ranking</td></tr>";
                    }
                }
            echo "</table></td>";
            echo "<td></td>";
            echo "<td colspan='4' valign='top'>";
            echo "<table class='bcgtTargetGradeSettingsIndividual'>";
            echo "<tr><th>Grade</th><th>Ucas Points</th><th>Score Upper</th><th>Score Lower</th><th>Ranking</th></tr>";
            $grades = get_qualification_grades_by_targetQual($qual->bcgttargetqualid);
            if($grades)
            {
                foreach($grades AS $grade)
                {
                    echo "<tr><td>$grade->grade</td><td>$grade->ucaspoints</td><td>$grade->upperscore</td><td>$grade->lowerscore</td><td>$grade->ranking</td></tr>";
                }
            }

            echo "</table>";
            echo "</td>";
            echo "</tr></table></td></tr>";
        }
    echo "</tr>";
    echo "</table>";
}
echo $out;
echo $OUTPUT->footer();
?>
