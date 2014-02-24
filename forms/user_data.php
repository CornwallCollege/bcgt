<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
global $COURSE, $CFG, $PAGE, $OUTPUT, $DB;;
require_once('../../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

$cID = optional_param('cID', -1, PARAM_INT);
$uID = optional_param('uID', -1, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);
if($cID != -1)
{
    $context = context_course::instance($cID);
}
else
{
    $context = context_course::instance($COURSE->id);
}

$a = optional_param('a', '', PARAM_TEXT);
$qID = optional_param('qID', -1, PARAM_INT);
$roleID = optional_param('rID', -1, PARAM_INT);

//$report = '';
require_login();
$PAGE->set_context($context);
require_capability('block/bcgt:checkuseraccess', $context);

$url = '/blocks/bcgt/forms/user_access.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('useraccess', 'block_bcgt'));
$PAGE->set_heading(get_string('useraccess', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('useraccess', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
$PAGE->navbar->add(get_string('admin', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string('useraccess', 'block_bcgt'),'','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.inituseraccess', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();
$out .= '<div id="userAccessBCGT">';
$out .= '<h2>'.get_string('userdata','block_bcgt').'</h2>';
$out .= '<div id="userAccessWrapper">';
$out .= html_writer::start_tag('div', array('class'=>'bcgt_user_access_controls', 
    'id'=>'userAccessContainer'));

$out .= '<form name="" id="userAccessform" method="POST" action="#" enctype="multipart/form-data">';

$out .= '<input type="text" name="search" value="'.$search.'"/>';
$out .= '<input type="submit" name="runsearch" value="'.get_string('search', 'block_bcgt').'"/>';

if($search != '')
{
    $sql = "SELECT * FROM {user} WHERE username LIKE ? OR firstname LIKE ? OR lastname LIKE ? OR email LIKE ?";
    $users = $DB->get_records_sql($sql, array('%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%'));
    if($users)
    {
        $out  .= '<select name="uID">';
        $out .= '<option value="-1">'.get_string('pleaseselect', 'block_bcgt').'</option>';
        foreach($users AS $user)
        {
            $selected = '';
            if($user->id == $uID)
            {
                $selected = 'selected';
            }
            $out .= '<option '.$selected.' value="'.$user->id.'">'.$user->username.' : '.$user->firstname.' '.$user->lastname.'</option>';
        }
        $out .= '</select>';
        $out .= '<input type="submit" name="run" value="'.get_string('checkuserdata','block_bcgt').'"/>';
    }
}

if($uID != -1)
{
    
    // Find their quals
    $out .= "<br><br>";
    $out .= '<h2>'.get_string('qualifications', 'block_bcgt').'</h2>';
    $userQuals = get_users_quals($uID);
    $load = new stdClass();
    $load->loadLevel = Qualification::LOADLEVELUNITS;
    
    $load2 = new stdClass();
    $load2->loadLevel = Qualification::LOADLEVELALL;
    $load2->loadAward = true;
    
    $unitArray = array();
    
    if ($userQuals)
    {
        foreach($userQuals as $qual)
        {
            
            $qualification = Qualification::get_qualification_class_id($qual->id, $load);
            if ($qualification)
            {
                
                if (!isset($unitArray[$qualification->get_id()])){
                    $unitArray[$qualification->get_id()] = array();
                }
                
                $qualification->load_student_information($uID, $load);
                $out .= '<h3>'.$qualification->get_display_name().'</h3>';
                $out .= "<br>";
                $out .= "<table style='text-align:center;'>";
                    $out .= "<tr><th>Unit</th><th>Award</th><th>No. Criteria Updated</th><th>Last Criteria Update</th></tr>";
                    
                    $units = $qualification->get_units();
                    if ($units)
                    {
                        foreach($units as $unit)
                        {
                            $unit->load_student_information($uID, $qualification->get_id(), $load2);
                            
                            if ($unit->is_student_doing())
                            {
                            
                                $unitArray[$qualification->get_id()][] = $unit->get_id();
                                
                                $out .= "<tr>";
                                    $out .= "<td>{$unit->get_name()}</td>";
                                    $award = $unit->get_user_award();
                                    if ($award) $award = $award->get_award();
                                    else $award = '-';
                                    $out .= "<td>{$award}</td>";

                                    $cnt = $DB->count_records_sql("SELECT COUNT(uc.id)
                                                                   FROM {block_bcgt_criteria} c
                                                                   INNER JOIN {block_bcgt_user_criteria} uc ON uc.bcgtcriteriaid = c.id
                                                                   WHERE uc.userid = ? AND c.bcgtunitid = ? AND uc.bcgtqualificationid = ? AND uc.bcgtvalueid > 0", array($uID, $unit->get_id(), $qualification->get_id()), "COUNT(id)");
                                    $out .= "<td>{$cnt}</td>";

                                    $lastUpdate = $DB->get_record_sql("SELECT uc.dateupdated
                                                                   FROM {block_bcgt_criteria} c
                                                                   INNER JOIN {block_bcgt_user_criteria} uc ON uc.bcgtcriteriaid = c.id
                                                                   WHERE uc.userid = ? AND c.bcgtunitid = ? AND uc.bcgtqualificationid = ? AND uc.bcgtvalueid > 0
                                                                   ORDER BY uc.dateupdated DESC", array($uID, $unit->get_id(), $qualification->get_id()), IGNORE_MULTIPLE);

                                    $lastSet = $DB->get_record_sql("SELECT uc.dateset
                                                                   FROM {block_bcgt_criteria} c
                                                                   INNER JOIN {block_bcgt_user_criteria} uc ON uc.bcgtcriteriaid = c.id
                                                                   WHERE uc.userid = ? AND c.bcgtunitid = ? AND uc.bcgtqualificationid = ? AND uc.bcgtvalueid > 0
                                                                   ORDER BY uc.dateset DESC", array($uID, $unit->get_id(), $qualification->get_id()), IGNORE_MULTIPLE);

                                    $last = 0;

                                    if ($lastUpdate){
                                        $last = $lastUpdate->dateupdated;
                                    }

                                    if ($lastSet && $lastSet->dateset > $last){
                                        $last = $lastSet->dateset;
                                    }

                                    if ($last > 0){
                                        $last = date('D jS M Y, H:i:s', $last);
                                    } else {
                                        $last = '-';
                                    }

                                    $out .= "<td>{$last}</td>";

                                $out .= "</tr>";
                            
                            }
                        }
                    }
                    
                $out .= "</table>";
                
                $out .= "<br><br>";
            
            }
            
        }
    }
        
    
    $out .= '<h2>'.get_string('unlinkedunits', 'block_bcgt').'</h2>';
    
    $out .= "<br>";
    $out .= "<table style='text-align:center;'>";
    $out .= "<tr><th>Qualification</th><th>Unit</th><th>Award</th><th>No. Criteria Updated</th><th>Last Criteria Update</th></tr>";
                 
    
    // Find any units not yet listed (e.g. ones they were on but are not on the qual anymore)
    $allUnits = $DB->get_records("block_bcgt_user_unit", array("userid" => $uID));
    if ($allUnits)
    {
        foreach($allUnits as $userUnit)
        {
            
            if (!isset($unitArray[$userUnit->bcgtqualificationid]) || !in_array($userUnit->bcgtunitid, $unitArray[$userUnit->bcgtqualificationid]))
            {
                
                $qual = Qualification::get_qualification_class_id($userUnit->bcgtqualificationid, $load);
                $unit = Unit::get_unit_class_id($userUnit->bcgtunitid, $load2);            
                
                if (!$qual || !$unit) continue;

                $out .= "<tr>";
                $out .= "<td>{$qual->get_display_name()}</td>";
                    $out .= "<td>{$unit->get_name()}</td>";
                    
                    $award = Award::get_award_id($userUnit->bcgttypeawardid);
                    if ($award) $award = $award->get_award();
                    else $award = '-';
                    
                    $out .= "<td>{$award}</td>";

                    $cnt = $DB->count_records_sql("SELECT COUNT(uc.id)
                                                   FROM {block_bcgt_criteria} c
                                                   INNER JOIN {block_bcgt_user_criteria} uc ON uc.bcgtcriteriaid = c.id
                                                   WHERE uc.userid = ? AND c.bcgtunitid = ? AND uc.bcgtqualificationid = ? AND uc.bcgtvalueid > 0", array($uID, $unit->get_id(), $userUnit->bcgtqualificationid), "COUNT(id)");
                    $out .= "<td>{$cnt}</td>";

                    $lastUpdate = $DB->get_record_sql("SELECT uc.dateupdated
                                                   FROM {block_bcgt_criteria} c
                                                   INNER JOIN {block_bcgt_user_criteria} uc ON uc.bcgtcriteriaid = c.id
                                                   WHERE uc.userid = ? AND c.bcgtunitid = ? AND uc.bcgtqualificationid = ? AND uc.bcgtvalueid > 0
                                                   ORDER BY uc.dateupdated DESC", array($uID, $unit->get_id(), $userUnit->bcgtqualificationid), IGNORE_MULTIPLE);

                    $lastSet = $DB->get_record_sql("SELECT uc.dateset
                                                   FROM {block_bcgt_criteria} c
                                                   INNER JOIN {block_bcgt_user_criteria} uc ON uc.bcgtcriteriaid = c.id
                                                   WHERE uc.userid = ? AND c.bcgtunitid = ? AND uc.bcgtqualificationid = ? AND uc.bcgtvalueid > 0
                                                   ORDER BY uc.dateset DESC", array($uID, $unit->get_id(), $userUnit->bcgtqualificationid), IGNORE_MULTIPLE);

                    $last = 0;

                    if ($lastUpdate){
                        $last = $lastUpdate->dateupdated;
                    }

                    if ($lastSet && $lastSet->dateset > $last){
                        $last = $lastSet->dateset;
                    }

                    if ($last > 0){
                        $last = date('D jS M Y, H:i:s', $last);
                    } else {
                        $last = '-';
                    }

                    $out .= "<td>{$last}</td>";

                $out .= "</tr>";
                            
                
            }
            
        }
    }
    
    $out .= "</table>";
    $out .= "<br><br>";
    
    
}

$out .= '</form>';

$out .= html_writer::end_tag('div');//end main column
$out .= html_writer::end_tag('div');//

$out .= '</div>';
$out .= $OUTPUT->footer();

echo $out;
?>
