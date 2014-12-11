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
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/lib.php');

$courseID = optional_param('cID', -1, PARAM_INT);
$tab = optional_param('tab', 'cef', PARAM_TEXT);
$mode = optional_param('mode','v',PARAM_TEXT);
$editing = false;
if(isset($_POST['edit']))
{
    $editing = true;
    $mode = 'e';
}
elseif(isset($_POST['view']))
{
    $editing = false;
    $mode = 'v';
}

if($editing)
{
    $inputVal = get_string('view','block_bcgt');
    $action = 'view';
}
else
{
    $inputVal = get_string('edit','block_bcgt');
    $action = 'edit';
}
$onCourse = optional_param('courses',NULL,PARAM_BOOL);
if($courseID != -1)
{
    $context = context_course::instance($courseID);
}
else
{
    $context = context_course::instance($COURSE->id);
}

$familiesSetting = get_config('bcgt','alpsweightedfamilies');
$families = array();
if($familiesSetting)
{
    $families = explode(',',$familiesSetting);
}
$levels = explode('|',QualWeighting::BCGT_WEIGHTINGS_LEVELS);
$params = new stdClass();
$params->onCourse = $onCourse;
$params->hasStudents = null;
$params->families = $families;
$params->levels = $levels;
if($tab == 'cef')
{
    $quals = bcgt_search_quals_2($params);
}
elseif($tab == 'tq')
{
    $targetQuals = bcgt_get_all_alps_target_quals();
}
require_login();
$PAGE->set_context($context);
require_capability('block/bcgt:editqualweightings', $context);
$url = '/blocks/bcgt/forms/my_dashboard.php';
$PAGE->set_url($url, array('tab' => 'track'));
$PAGE->set_title(get_string('qualificationweightingsettings', 'block_bcgt'));
$PAGE->set_heading(get_string('qualificationweightingsettings', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('bcgtmydashboard', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add('',$url.'?tab=track','title');
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initqualweightsettings', null, true, $jsModule);
load_javascript();
set_time_limit(0);
$out = '';
$out .= $OUTPUT->header();
$out .= '<div id="bcgt_qual_weightings">';
$out .= '<h2>'.get_string('qualificationweightingsettings','block_bcgt').'</h2>';


//tabs
$out .= '<div class="tabs"><div class="tabtree">';
$out .= '<ul class="tabrow0">';
$out .= '<li class="">'.
    '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/qual_weightings.php?tab=cef">'.
    '<span>'.get_string('coefficients', 'block_bcgt').'</span></a></li>';
$out .= '<li class="">'.
    '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/qual_weightings.php?tab=con">'.
    '<span>'.get_string('constants', 'block_bcgt').'</span></a></li>';
$out .= '<li class="">'.
    '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/qual_weightings.php?tab=tq">'.
    '<span>'.get_string('targetcoefficients', 'block_bcgt').'</span></a></li>';
$out .= '</ul>';
$out .= '</div></div>';

if($tab == 'cef')
{
    $out .= '<form action="" method="POST" name="">';
    $out .= '<div class="bcgt_controls">';
    $out .= '<input type="submit" name="'.$action.'" value="'.$inputVal.'"/>';
    if($editing)
    {
        $out .= '<input type="submit" name="save" value="'.get_string('save','block_bcgt').'"/>';
    }
    $out .= '<input type="hidden" name="mode" value="'.$mode.'"/>';
    $out .= '<br /><label for="courses">'.get_string('qualsoncourses','block_bcgt').'</label>';
    $checked = '';
    if($onCourse)
    {
        $checked = 'checked="checked"';
    }
    $out .= '<input type="checkbox" name="courses" value="course" '.$checked.'/>';
    $out .= '('.get_string('found','block_bcgt').' : '.count($quals).')';
    $out .= '</div>';
    $out .= '<br clear="all">';
    $out .= '<table class="bcgt_table qual_weightings">';
    $out .= '<head>';
    $out .= '<tr><th>'.get_string('qual','block_bcgt').'</th>';
    for($i=0;$i<AlevelQualification::DEFAULTINITIALWEIGHTINGS;$i++)
    {
        $out .= '<th class="alpstemp'.($i+1).'">'.($i+1).'</th>';
    }
    $out .= '</tr>';
    $out .= '</head>';
    $out .= '<body>';
    if($quals)
    {
        $saving = false;
        if(isset($_POST['save']))
        {
            $saving = true;
        }
        $count = 0;
        $qualWeightingObj = new QualWeighting();
        foreach($quals AS $qual)
        {
            if($saving)
            {
                for($i=0;$i<AlevelQualification::DEFAULTINITIALWEIGHTINGS;$i++)
                {
                    $value = isset($_POST['q_'.$qual->id.'_'.($i + 1).'']) ? $_POST['q_'.$qual->id.'_'.($i + 1).''] : null;
                    if($value)
                    {
                        $params = new stdClass();
                        $params->bcgtqualificationid = $qual->id;
                        $params->coefficient = $value;
                        $params->number = $i + 1;
                        $qualWeighting = new QualWeighting(-1, $params);
                        $qualWeighting->save(true);
                    }
                }
            }    

            $count++;
            $rowClass = 'rO';
            if($count % 2)
            {
                $rowClass = 'rE';
            }
            $out .= '<tr class="'.$rowClass.'">';
            $out .= '<td>'.  bcgt_get_qualification_display_name($qual).'</td>';
            $coefficients = $qualWeightingObj->get_all_coefficients_for_qual($qual->id);
            if($coefficients)
            {
                $count = 0;
                foreach($coefficients AS $weighting)
                {
                    //compare the number of this to the header:
                    $count++;
                    if($count != $weighting->number)
                    {
                        while($count < $weighting->number)
                        {
                            $count++;
                            $out .= '<td>';
                            if($editing)
                            {
                                $out .= '<input class="coef" type="text" name="q_'.$qual->id.'_'.$count.'"/>';
                            }
                            $out .= '</td>';
                        }
                    }

                    $out .= '<td class="alpstemp'.$weighting->number.'">';
                    if($editing)
                    {
                        $out .= '<input class="coef" type="text" name="q_'.$qual->id.'_'.$weighting->number.'" value="'.$weighting->coefficient.'"/>';
                    }
                    else
                    {
                        $out .= $weighting->coefficient;
                    }
                    $out .= '</td>';
                }
                if($count != AlevelQualification::DEFAULTINITIALWEIGHTINGS)
                {
                    while($count < AlevelQualification::DEFAULTINITIALWEIGHTINGS)
                    {
                        $count++;
                        $out .= '<td>';
                        if($editing)
                        {
                            $out .= '<input class="coef" type="text" name="q_'.$qual->id.'_'.$count.'"/>';
                        }
                        $out .= '</td>';
                    }
                }
            }
            else
            {
                for($i=0;$i<AlevelQualification::DEFAULTINITIALWEIGHTINGS;$i++)
                {
                    $out .= '<td>';
                    if($editing)
                    {
                        $out .= '<input class="coef" type="text" name="q_'.$qual->id.'_'.($i + 1).'" value=""/>';
                    }
                    $out .= '</td>';
                }
            }
            $out .= '</tr>';
        }
    }
    $out .= '</body>';
    $out .= '</table>';
    //table
    //quals

    $out .= '</form>';
}
elseif($tab == 'tq')
{
    $out .= '<form action="" method="POST" name="">';
    $out .= '<div class="bcgt_controls">';
    $out .= '<input type="submit" name="'.$action.'" value="'.$inputVal.'"/>';
    if($editing)
    {
        $out .= '<input type="submit" name="save" value="'.get_string('save','block_bcgt').'"/>';
    }
    $out .= '<input type="hidden" name="mode" value="'.$mode.'"/>';
    $out .= '</div>';
    $out .= '<br clear="all">';
    $out .= '<table class="bcgt_table qual_weightings">';
    $out .= '<head>';
    $out .= '<tr><th>'.get_string('targetqual','block_bcgt').'</th>';
    for($i=0;$i<AlevelQualification::DEFAULTINITIALWEIGHTINGS;$i++)
    {
        $out .= '<th class="alpstemp'.($i+1).'">'.($i+1).'</th>';
    }
    $out .= '</tr>';
    $out .= '</head>';
    $out .= '<body>';
    
    if($targetQuals)
    {
        $saving = false;
        if(isset($_POST['save']))
        {
            $saving = true;
        }
        $count = 0;
        $qualWeightingObj = new TargetQualWeighting();
        foreach($targetQuals AS $targetQual)
        {
            if($saving)
            {
                for($i=0;$i<AlevelQualification::DEFAULTINITIALWEIGHTINGS;$i++)
                {
                    $value = isset($_POST['q_'.$targetQual->id.'_'.($i + 1).'']) ? $_POST['q_'.$targetQual->id.'_'.($i + 1).''] : null;
                    if($value)
                    {
                        $params = new stdClass();
                        $params->bcgttargetqualid = $targetQual->id;
                        $params->coefficient = $value;
                        $params->number = $i + 1;
                        $qualWeighting = new TargetQualWeighting(-1, $params);
                        $qualWeighting->save(true);
                    }
                }
            }    

            $count++;
            $rowClass = 'rO';
            if($count % 2)
            {
                $rowClass = 'rE';
            }
            $out .= '<tr class="'.$rowClass.'">';
            $out .= '<td>'.$targetQual->family.' '.$targetQual->trackinglevel.' '.$targetQual->subtype.'</td>';
            $coefficients = $qualWeightingObj->get_all_coefficients_for_targetqual($targetQual->id);
            if($coefficients)
            {
                $count = 0;
                foreach($coefficients AS $weighting)
                {
                    //compare the number of this to the header:
                    $count++;
                    if($count != $weighting->number)
                    {
                        while($count < $weighting->number)
                        {
                            $count++;
                            $out .= '<td>';
                            if($editing)
                            {
                                $out .= '<input class="coef" type="text" name="q_'.$targetQual->id.'_'.$count.'"/>';
                            }
                            $out .= '</td>';
                        }
                    }

                    $out .= '<td class="alpstemp'.$weighting->number.'">';
                    if($editing)
                    {
                        $out .= '<input class="coef" type="text" name="q_'.$targetQual->id.'_'.$weighting->number.'" value="'.$weighting->coefficient.'"/>';
                    }
                    else
                    {
                        $out .= $weighting->coefficient;
                    }
                    $out .= '</td>';
                }
                if($count != AlevelQualification::DEFAULTINITIALWEIGHTINGS)
                {
                    while($count < AlevelQualification::DEFAULTINITIALWEIGHTINGS)
                    {
                        $count++;
                        $out .= '<td>';
                        if($editing)
                        {
                            $out .= '<input class="coef" type="text" name="q_'.$targetQual->id.'_'.$count.'"/>';
                        }
                        $out .= '</td>';
                    }
                }
            }
            else
            {
                for($i=0;$i<AlevelQualification::DEFAULTINITIALWEIGHTINGS;$i++)
                {
                    $out .= '<td>';
                    if($editing)
                    {
                        $out .= '<input class="coef" type="text" name="q_'.$targetQual->id.'_'.($i + 1).'" value=""/>';
                    }
                    $out .= '</td>';
                }
            }
            $out .= '</tr>';
        }
    }
    $out .= '</body>';
    $out .= '</table>';
    $out .= '<p>'.get_string('targetqualweightingwarning', 'block_bcgt').'</p>';
    //table
    //quals

    $out .= '</form>';
}
elseif($tab == 'con')
{
    $out .= '<p>'.get_string('descweightedtargetuseconstant','block_bcgt').'</p>';
    $out .= '<form action="" method="POST" name="">';
    $out .= '<div class="bcgt_controls">';
    $out .= '<input type="submit" name="'.$action.'" value="'.$inputVal.'"/>';
    if($editing)
    {
        $out .= '<input type="submit" name="save" value="'.get_string('save','block_bcgt').'"/>';
    }
    $out .= '<input type="hidden" name="mode" value="'.$mode.'"/>';
    $out .= '</div>';
    $out .= '<br clear="all">';
    $out .= '<table class="bcgt_table qual_weightings">';
    $out .= '<head>';
    $out .= '<tr><th>'.get_string('targetqual','block_bcgt').'</th>';
    $out .= '<th>'.get_string('constant', 'block_bcgt').'</th>';
    $out .= '<th>'.get_string('alpsmultiplier', 'block_bcgt').'</th>';
    $out .= '</tr>';
    $out .= '</head>';
    $out .= '<body>';
    
    $targetQuals = bcgt_get_target_quals_array(array('BTEC','ALevel'));
    if($targetQuals)
    {
        $saving = false;
        if(isset($_POST['save']))
        {
            $saving = true;
        }
        $count = 0;
        foreach($targetQuals AS $targetQual)
        {
            if($saving)
            {
                $constant = optional_param('tq_'.$targetQual->id, null, PARAM_TEXT);
                $multiplier = optional_param('tqw_'.$targetQual->id, null, PARAM_TEXT);
                $params = new stdClass();
                $qualWeighting = new QualWeighting(-1);
                $qualWeighting->save_constant($targetQual->id, $constant);
                $qualWeighting->save_multiplier($targetQual->id, $multiplier);
            }
            
            $count++;
            $rowClass = 'rO';
            if($count % 2)
            {
                $rowClass = 'rE';
            }
            $out .= '<tr class="'.$rowClass.'">';
            $out .= '<td>'.$targetQual->family.' '.$targetQual->trackinglevel.' '.$targetQual->subtype.'</td>';
            $out .= '<td>';
            
            //go and get the value
            $qualWeighting = new QualWeighting();
            $constant = $qualWeighting->get_constant($targetQual->id);
            $multiplier = $qualWeighting->get_multiplier($targetQual->id);
            if($editing)
            {
                $out .= '<input class="coef" type="text" name="tq_'.$targetQual->id.'" value="'.$constant.'"/>';
            }
            else
            {
                $out .= $constant;
            }
            $out .= '</td>';
            $out .= '<td>';
            if($editing)
            {
                $out .= '<input class="coef" type="text" name="tqw_'.$targetQual->id.'" value="'.$multiplier.'"/>';
            }
            else
            {
                $out .= $multiplier;
            }
            
            $out .= '</td>';
        }
    }
    $out .= '</body>';
    $out .= '</table>';
    //table
    //quals

    $out .= '</form>';
}
$out .= '</div>';
$out .= $OUTPUT->footer();
echo $out;
?>
