<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;;
require_once('../../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

$cID = optional_param('cID', -1, PARAM_INT);
$action = optional_param('a', 'view', PARAM_TEXT);
$id = optional_param('id', -1, PARAM_INT);
if(isset($_POST['save']))
{
    $stdObject = new stdClass();
    $stdObject->id = $id;
    if($id == -1 && isset($_POST['modname']))
    {
        $stdObject->moduleid = $_POST['modname'];
    }
    $stdObject->modtablename = isset($_POST['modtablename'])? $_POST['modtablename'] : '';
    $stdObject->modtablecoursefname = isset($_POST['modtablecoursefname'])? $_POST['modtablecoursefname'] : '';
    $stdObject->modtableduedatefname = isset($_POST['modtableduedatefname'])? $_POST['modtableduedatefname'] : '';
    $stdObject->modsubmssiontable = isset($_POST['modsubmssiontable'])? $_POST['modsubmssiontable'] : '';
    $stdObject->submissionuserfname = isset($_POST['submissionuserfname'])? $_POST['submissionuserfname'] : '';
    $stdObject->submissiondatefname = isset($_POST['submissiondatefname'])? $_POST['submissiondatefname'] : '';
    $stdObject->submissionmodidfname = isset($_POST['submissionmodidfname'])? $_POST['submissionmodidfname'] : '';
    $stdObject->checkforautotracking = isset($_POST['checkforautotracking'])? 1 : 0;
    if($id != -1)
    {
        $DB->update_record('block_bcgt_mod_linking', $stdObject);
    }
    else
    {
        $DB->insert_record('block_bcgt_mod_linking', $stdObject);
    }
    $action = 'view';
}
elseif(isset($_POST['cancel']))
{
    $id = -1;
    $action = 'view';
}
if($action == 'del' && $id != -1)
{
    //then we want to delete an instance
    $DB->delete_records('block_bcgt_mod_linking', array("id"=>$id));
    $action = 'view';
}
$context = context_system::instance();
require_login();
$PAGE->set_context($context);
require_capability('block/bcgt:managemodlinking', $context);
$url = '/blocks/bcgt/forms/mod_linking.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string('managemodlinking', 'block_bcgt'));
$PAGE->set_heading(get_string('managemodlinking', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->add_body_class(get_string('managemodlinking', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
$PAGE->navbar->add(get_string('managemodlinking', 'block_bcgt'),'','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initmanagemodule', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript();
$out = $OUTPUT->header();

$out .= html_writer::tag('h2', get_string('managemodlinking','block_bcgt').
        '', 
        array('class'=>'formheading'));
$out .= html_writer::start_tag('div', array('class'=>'bcgt_activity_controls bcgt_admin_controls', 
    'id'=>'manageModuleLinking'));
$out .= '<form name="managemodulelinking" method="POST" action=""/>';
$out .= '<input type="hidden" name="cID" value="'.$cID.'"/>';
if($action == 'new' || $action == 'edit')
{
    //output the form to edit/new
    $module = new stdClass();
    if($id != -1)
    {
        $module = get_mod_linking($id); 
    }
    else
    {
        $out .= '<div class="inputContainer"><div class="inputLeft">';
        $out .= '<label for="modname"><span class="required">*</span>'
            .get_string('mod', 'block_bcgt').': </label></div>';
        $out .= '<div class="inputRight"><select id="modname" name="modname">';
        $out .= '<option value="">'.get_string('pleaseselect', 'block_bcgt').'</option>';
        $possibleMods = get_non_used_mods();
        if($possibleMods)
        {
            foreach($possibleMods as $mod) {
                $out .= "<option value='".$mod->id."'>".$mod->name."</option>";
            }	
        }
        $out .= "</select></div></div>";
    }
    $out .= '<input type="hidden" name="id" value="'.$id.'"/>';
    $out .= '<div class="inputContainer"><div class="inputLeft">';
    $out .= '<label for="modtablename"><span class="required">*</span>'
        .get_string('mlmodtable', 'block_bcgt').': </label></div>';
    $out .= '<div class="inputRight"><input type="text" name="modtablename"'.
            'value="'.(isset($module->modtablename)? $module->modtablename : '').'"/>';
    $out .= '</div></div>';
        
    $out .= '<div class="inputContainer"><div class="inputLeft">';
    $out .= '<label for="modtablecoursefname"><span class="required">*</span>'
        .get_string('mlcoursefieldname', 'block_bcgt').': </label></div>';
    $out .= '<div class="inputRight"><input type="text" name="modtablecoursefname"'.
            'value="'.(isset($module->modtablecoursefname)? $module->modtablecoursefname : '').'"/>';
    $out .= '</div></div>';
    
    $out .= '<div class="inputContainer"><div class="inputLeft">';
    $out .= '<label for="modtableduedatefname"><span class="required">*</span>'
        .get_string('mlduedatefieldname', 'block_bcgt').': </label></div>';
    $out .= '<div class="inputRight"><input type="text" name="modtableduedatefname"'.
            'value="'.(isset($module->modtableduedatefname)? $module->modtableduedatefname : '').'"/>';
    $out .= '</div></div>';
    
    $out .= '<div class="inputContainer"><div class="inputLeft">';
    $out .= '<label for="modsubmssiontable">'
        .get_string('mlmodsubmissiontable', 'block_bcgt').': </label></div>';
    $out .= '<div class="inputRight"><input type="text" name="modsubmssiontable"'.
            'value="'.(isset($module->modsubmssiontable)? $module->modsubmssiontable : '').'"/>';
    $out .= '</div></div>';
    
    $out .= '<div class="inputContainer"><div class="inputLeft">';
    $out .= '<label for="submissionuserfname">'
        .get_string('mlsubmissionuserfield', 'block_bcgt').': </label></div>';
    $out .= '<div class="inputRight"><input type="text" name="submissionuserfname"'.
            'value="'.(isset($module->submissionuserfname)? $module->submissionuserfname : '').'"/>';
    $out .= '</div></div>';
    
    $out .= '<div class="inputContainer"><div class="inputLeft">';
    $out .= '<label for="submissiondatefname">'
        .get_string('mlsubmissiondatefield', 'block_bcgt').': </label></div>';
    $out .= '<div class="inputRight"><input type="text" name="submissiondatefname"'.
            'value="'.(isset($module->submissiondatefname)? $module->submissiondatefname : '').'"/>';
    $out .= '</div></div>';
    
    $out .= '<div class="inputContainer"><div class="inputLeft">';
    $out .= '<label for="submissionmodidfname">'
        .get_string('mlsubmissionmoduleinstancefield', 'block_bcgt').': </label></div>';
    $out .= '<div class="inputRight"><input type="text" name="submissionmodidfname"'.
            'value="'.(isset($module->submissionmodidfname)? $module->submissionmodidfname : '').'"/>';
    $out .= '</div></div>';
    
    $out .= '<div class="inputContainer"><div class="inputLeft">';
    $out .= '<label for="checkforautotracking">'
        .get_string('mlcheckautolinking', 'block_bcgt').': </label></div>';
    $out .= '<div class="inputRight"><input type="checkbox" name="checkforautotracking"'.
            ''.((isset($module->checkforautotracking) && $module->checkforautotracking != 0)? ' checked="checked' : '').'"/>';
    $out .= '</div></div>';
    
    $out .= '<input type="submit" name="save" value="'.get_string('save', 'block_bcgt').'"/>';
    $out .= '<input type="submit" name="cancel" value="'.get_string('cancel', 'block_bcgt').'"/>';
}
elseif($action == 'view')
{
    //then put what we have
    $modules = get_mod_linking();
    if($modules)
    {
        $out .= '<table class="bcgt_table" align="center">';
        $out .= '<tr>';
        $out .= '<th>'.get_string('mod', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('mlmodtable', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('mlcoursefieldname', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('mlduedatefieldname', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('mlmodsubmissiontable', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('mlsubmissionuserfield', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('mlsubmissiondatefield', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('mlsubmissionmoduleinstancefield', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('mlcheckautolinking', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('edit', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('delete', 'block_bcgt').'</th>';
        $out .= '</tr>';
        $count = 0;
        foreach($modules AS $mod)
        {
            $count++;
            $class = 'rO';
            if($count%2)
            {
                $class = 'rE';
            }
            $out .= '<tr class="'.$class.'">';
            $out .= '<td>'.$mod->modname.'</td>';
            $out .= '<td>'.$mod->modtablename.'</td>';
            $out .= '<td>'.$mod->modtablecoursefname.'</td>';
            $out .= '<td>'.$mod->modtableduedatefname.'</td>';
            $out .= '<td>'.$mod->modsubmssiontable.'</td>';
            $out .= '<td>'.$mod->submissionuserfname.'</td>';
            $out .= '<td>'.$mod->submissiondatefname.'</td>';
            $out .= '<td>'.$mod->submissionmodidfname.'</td>';
            $out .= '<td>'.$mod->checkforautotracking.'</td>';
            $out .= '<td><a href="?cID='.$cID.'&a=edit&id='.$mod->id.'">'.get_string('edit', 'block_bcgt').'</a></td>';
            $out .= '<td><a href="?cID='.$cID.'&a=del&id='.$mod->id.'">'.get_string('delete', 'block_bcgt').'</a></td>';
            $out .= '</tr>';
        }
        $out .= '</table>';
    }
    else
    {
        $out .= '<p>'.get_string('nomoduleslinked','block_bcgt').'</p>';
    }
    $out .= '<div>';
    $out .= '<a href="?cID='.$cID.'&a=new"/>'.get_string('linknewmodule', 'block_bcgt').'</a>';
    $out .= '</div>';
}
$out .= '</form>';
$out .= html_writer::end_tag('div');//end main column
$out .= $OUTPUT->footer();

echo $out;
?>
