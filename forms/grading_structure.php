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

require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeQualification.class.php';
require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeUnit.class.php';

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

$type = required_param('type', PARAM_TEXT);

$PAGE->set_url('/blocks/bcgt/forms/grading_structure.php?type='.$type, array());
$PAGE->set_title(get_string('gradingstructure', 'block_bcgt'));
$PAGE->set_heading(get_string('gradingstructure', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php?tab=track','title');
//$PAGE->navbar->add(get_string('bcgtmydashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string('gradingstructure', 'block_bcgt'));

require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
load_javascript(true);

echo $OUTPUT->header();
echo'<h2 class="bcgt_form_heading">'.get_string('gradingstructure', 'block_bcgt').'</h2>';
	echo "<div id='bcgtEditQualForm' class='bcgt_admin_controls'>";
		echo "<form method='POST' action='' enctype='multipart/form-data'>";
			
        switch($type)
        {
            case 'qual':
                
                require_capability('block/bcgt:addqualgradingstructure', $context);
                                
                
                echo "<script>var num = 2; function addNewGSRow(){ num++; $('#gsTable').append('<tr><td><input type=\"text\" name=\"grades['+num+']\" /></td><td><input type=\"text\" style=\"width:40px;\" name=\"shortgrades['+num+']\" /></td><td><input type=\"text\" style=\"width:40px;\" name=\"lowers['+num+']\" /></td><td><input  type=\"text\" style=\"width:40px;\" name=\"uppers['+num+']\" /></td></tr>'); }</script>";
                
                echo '<h3>'.get_string('gradingstructure', 'block_bcgt').': '.get_string('qualification', 'block_bcgt').'</h3>';
                
                echo '<p>'.get_string('qualgradingstructurehelp', 'block_bcgt').'</p>';
                
                
                if (isset($_POST['save'])){
                    
                    $name = trim($_POST['name']);
                    $grades = $_POST['grades'];
                    $shortgrades = $_POST['shortgrades'];
                    $lowers = $_POST['lowers'];
                    $uppers = $_POST['uppers'];
                    
                    if (empty($name)){
                        echo '<span style="color:red;">'.get_string('error:name', 'block_bcgt').'</span><br>';
                    } else {
                    
                        $obj = new stdClass();
                        $obj->name = $name;
                        $id = $DB->insert_record('block_bcgt_bspk_qual_grading', $obj);

                        $cnt = count($grades);

                        for($i = 0; $i < $cnt; $i++){

                            if (empty($grades[$i])) continue;

                            $obj = new stdClass();
                            $obj->qualgradingid = $id;
                            $obj->grade = $grades[$i];
                            $obj->shortgrade = $shortgrades[$i];
                            $obj->rangelower = $lowers[$i];
                            $obj->rangeupper = $uppers[$i];
                            $DB->insert_record('block_bcgt_bspk_q_grade_vals', $obj);

                        }

                        echo '<span style="color:blue;">Saved</span><br>';
                    
                    }
                    
                    
                }
                
                
                echo '<label>'.get_string('name').'</label> <input type="text" name="name" value="" /><br><br>';
                echo '<label><b>'.get_string('gradevalues', 'block_bcgt').'</b></label><br><small><a href="#" onclick="addNewGSRow();return false;">['.get_string('add').']</a></small><br>';
                echo '<table style="margin:auto;" id="gsTable">';
                    echo '<tr><th>'.get_string('grade').'</th><th>'.get_string('shortgrade', 'block_bcgt').'</th><th>'.get_string('lowerrangescore', 'block_bcgt').'</th><th>'.get_string('upperrangescore', 'block_bcgt').'</th></tr>';
                    
                    echo '<tr><td><input placeholder="e.g. Pass" type="text" name="grades[0]" /></td><td><input placeholder="P" type="text" style="width:40px;" name="shortgrades[0]" /></td><td><input placeholder="1.0" type="text" style="width:40px;" name="lowers[0]" /></td><td><input placeholder="1.5" type="text" style="width:40px;" name="uppers[0]" /></td></tr>';
                    echo '<tr><td><input placeholder="e.g. Merit" type="text" name="grades[1]" /></td><td><input placeholder="M" type="text" style="width:40px;" name="shortgrades[1]" /></td><td><input placeholder="1.6" type="text" style="width:40px;" name="lowers[1]" /></td><td><input placeholder="2.5" type="text" style="width:40px;" name="uppers[1]" /></td></tr>';
                    echo '<tr><td><input placeholder="e.g. Distinction" type="text" name="grades[2]" /></td><td><input placeholder="D" type="text" style="width:40px;" name="shortgrades[2]" /></td><td><input placeholder="2.6" type="text" style="width:40px;" name="lowers[2]" /></td><td><input placeholder="3" type="text" style="width:40px;" name="uppers[2]" /></td></tr>';
                
                echo '</table>';
                echo '<br><br>';
                                
            break;
            
            
            case 'unit':
                
                require_capability('block/bcgt:addunitgradingstructure', $context);
                                
                echo "<script>var num = 2; function addNewGSRow(){ num++; $('#gsTable').append('<tr><td><input type=\"text\" name=\"grades['+num+']\" /></td><td><input type=\"text\" style=\"width:40px;\" name=\"shortgrades['+num+']\" maxlength=\"2\" /></td><td><input type=\"text\" style=\"width:40px;\" name=\"points['+num+']\" /></td><td><input type=\"text\" style=\"width:40px;\" name=\"lowers['+num+']\" /></td><td><input type=\"text\" style=\"width:40px;\" name=\"uppers['+num+']\" /></td></tr>'); }</script>";
                
                echo '<h3>'.get_string('gradingstructure', 'block_bcgt').': '.get_string('unit', 'block_bcgt').'</h3>';
                
                echo '<p>'.get_string('unitgradingstructurehelp', 'block_bcgt').'</p>';
                echo '<p>'.get_string('unitgradingpointshelp', 'block_bcgt').'</p>';
                echo '<p>'.get_string('unitgradingrangehelp', 'block_bcgt').'</p>';
                
                
                if (isset($_POST['save'])){
                    
                    $name = trim($_POST['name']);
                    $grades = $_POST['grades'];
                    $shortgrades = $_POST['shortgrades'];
                    $points = $_POST['points'];
                    $lowers = $_POST['lowers'];
                    $uppers = $_POST['uppers'];
                    
                    if (empty($name)){
                        echo '<span style="color:red;">'.get_string('error:name', 'block_bcgt').'</span><br>';
                    } else {
                    
                        $obj = new stdClass();
                        $obj->name = $name;
                        $id = $DB->insert_record('block_bcgt_bspk_unit_grading', $obj);

                        $cnt = count($grades);

                        for($i = 0; $i < $cnt; $i++){

                            if (empty($grades[$i])) continue;

                            $obj = new stdClass();
                            $obj->unitgradingid = $id;
                            $obj->grade = $grades[$i];
                            $obj->shortgrade = $shortgrades[$i];
                            $obj->points = $points[$i];
                            $obj->rangelower = $lowers[$i];
                            $obj->rangeupper = $uppers[$i];
                            $DB->insert_record('block_bcgt_bspk_u_grade_vals', $obj);

                        }

                        echo '<span style="color:blue;">Saved</span><br>';
                    
                    }
                    
                    
                }
                
                
                echo '<label>'.get_string('name').'</label> <input type="text" name="name" value="" /><br>';
                echo '<label>'.get_string('gradevalues', 'block_bcgt').'</label><br><small><a href="#" onclick="addNewGSRow();return false;">['.get_string('add').']</a></small><br>';
                echo '<table style="margin:auto;" id="gsTable">';
                    echo '<tr><th>'.get_string('grade').'</th><th>'.get_string('shortgrade', 'block_bcgt').'</th><th>'.get_string('points', 'block_bcgt').'</th><th>'.get_string('lowerrangescore', 'block_bcgt').'</th><th>'.get_string('upperrangescore', 'block_bcgt').'</th></tr>';
                    
                    echo '<tr><td><input placeholder="e.g. Pass" type="text" name="grades[0]" /></td><td><input placeholder="P" type="text" style="width:40px;" name="shortgrades[0]" maxlength="2" /></td><td><input placeholder="1" type="text" style="width:40px;" name="points[0]" /></td><td><input placeholder="1.0" type="text" style="width:40px;" name="lowers[0]" /></td><td><input placeholder="1.5" type="text" style="width:40px;" name="uppers[0]" /></td></tr>';
                    echo '<tr><td><input placeholder="e.g. Merit" type="text" name="grades[1]" /></td><td><input placeholder="M" type="text" style="width:40px;" name="shortgrades[1]" maxlength="2" /></td><td><input placeholder="2" type="text" style="width:40px;" name="points[1]" /></td><td><input placeholder="1.6" type="text" style="width:40px;" name="lowers[1]" /></td><td><input placeholder="2.5" type="text" style="width:40px;" name="uppers[1]" /></td></tr>';
                    echo '<tr><td><input placeholder="e.g. Distinction" type="text" name="grades[2]" /></td><td><input placeholder="D" type="text" style="width:40px;" name="shortgrades[2]" maxlength="2" /></td><td><input placeholder="3" type="text" style="width:40px;" name="points[2]" /></td><td><input placeholder="2.6" type="text" style="width:40px;" name="lowers[2]" /></td><td><input placeholder="3" type="text" style="width:40px;" name="uppers[2]" /></td></tr>';
                
                echo '</table>';
                echo '<br><br>';
                                
            break;
            
            case 'critnonmet':
                
                require_capability('block/bcgt:addcriteriagradingstructure', $context);
                
                echo '<h3>'.get_string('gradingstructure', 'block_bcgt').': '.get_string('nonmetcriteriavalues', 'block_bcgt').'</h3>';
                
                echo '<p>'.get_string('nonmetcriteriavalueshelp', 'block_bcgt').'</p>';
                
                if (isset($_POST['save'])){
                    
                    $gradeids = $_POST['gradeids'];
                    $grades = $_POST['grades'];
                    $shortgrades = $_POST['shortgrades'];
                    $imgs = $_FILES['img'];
                    
                    // Grades
                    $cnt = count($grades);

                    for($i = 0; $i < $cnt; $i++){

                        if (isset($gradeids[$i]) && !empty($gradeids[$i]))
                        {

                            if (empty($grades[$i]))
                            {
                                $DB->delete_records("block_bcgt_bspk_c_grade_vals", array("id" => $gradeids[$i]));
                            }
                            else
                            {
                                $obj = $DB->get_record("block_bcgt_bspk_c_grade_vals", array("id" => $gradeids[$i]));
                                if ($obj)
                                {

                                    if (isset($imgs['name'][$i])){
                                        $img = new stdClass();
                                        $img->name = $imgs['name'][$i];
                                        $img->type = $imgs['type'][$i];
                                        $img->tmp_name = $imgs['tmp_name'][$i];
                                        $img->error = $imgs['error'][$i];
                                        $img->size = $imgs['size'][$i];
                                    }

                                    $obj->grade = $grades[$i];
                                    $obj->shortgrade = $shortgrades[$i];
                                    $obj->met = 0;


                                    // Upload image
                                    if (isset($img) && preg_match('/^image\//', $img->type))
                                    {

                                        // create directory if it doesn't exist
                                        if (!is_dir($CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/pix/grid_symbols/bespoke/')){
                                            mkdir($CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/pix/grid_symbols/bespoke/', 0775);
                                        }
                                        
                                        $filename = $obj->id . '_' . $obj->shortgrade . '.jpg';
                                        move_uploaded_file( $img->tmp_name, $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/pix/grid_symbols/bespoke/'.$filename);
                                        $obj->img = $filename;

                                    }

                                    $DB->update_record('block_bcgt_bspk_c_grade_vals', $obj);

                                }
                            }

                        }
                        else
                        {

                            if (empty($grades[$i])) continue;
                            $obj = new stdClass();
                            $obj->critgradingid = null;
                            $obj->grade = $grades[$i];
                            $obj->shortgrade = $shortgrades[$i];
                            $obj->points = 0;
                            $obj->rangelower = 0;
                            $obj->rangeupper = 0;
                            $obj->met = 0;
                            
                            $obj->id = $DB->insert_record('block_bcgt_bspk_c_grade_vals', $obj);
                            
                            if (isset($imgs['name'][$i])){
                                $img = new stdClass();
                                $img->name = $imgs['name'][$i];
                                $img->type = $imgs['type'][$i];
                                $img->tmp_name = $imgs['tmp_name'][$i];
                                $img->error = $imgs['error'][$i];
                                $img->size = $imgs['size'][$i];
                            }
                            
                            // Upload image
                            if (isset($img) && preg_match('/^image\//', $img->type))
                            {

                                // create directory if it doesn't exist
                                if (!is_dir($CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/pix/grid_symbols/bespoke/')){
                                    mkdir($CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/pix/grid_symbols/bespoke/', 0775);
                                }
                                        
                                $filename = $obj->id . '_' . $obj->shortgrade . '.jpg';
                                move_uploaded_file( $img->tmp_name, $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/pix/grid_symbols/bespoke/'.$filename);
                                $obj->img = $filename;

                            }
                            
                            $DB->update_record('block_bcgt_bspk_c_grade_vals', $obj);
                            

                        }




                    }

                    echo '<br><span style="color:blue;">Saved</span><br><br>';
                                        
                }
                
                
                
                $values = $DB->get_records_select("block_bcgt_bspk_c_grade_vals", "met = 0 AND critgradingid IS NULL", array(), "grade ASC");
                
                echo "<script>var num = ".(count($values) - 1)."; function addNewGSRow(){ num++; $('#gsTable').append('<tr><td><input type=\"text\" name=\"grades['+num+']\" /></td><td><input type=\"text\" style=\"width:40px;\" name=\"shortgrades['+num+']\" /></td><td><input type=\"file\" name=\"img['+num+']\" accept=\"image/*\" /></td></tr>'); }</script>";
                
                echo '<a href="#" onclick="addNewGSRow();return false;">['.get_string('add').']</a><br>';
                
                echo '<table style="margin:auto;text-align:center;" id="gsTable">';
                    echo '<tr><th>'.get_string('grade').'</th><th>'.get_string('shortgrade', 'block_bcgt').'</th><th>'.get_string('gridimg', 'block_bcgt').'</th></tr>';
                
                    if ($values)
                    {
                        $i = 0;
                        foreach($values as $value)
                        {
                            $image = BespokeQualification::get_grid_image($value->shortgrade, $value->grade, $value);
                            $img = "<img src='{$image->image}' alt='{$image->title}' />";
                            echo '<tr><td><input type="hidden" name="gradeids['.$i.']" value="'.$value->id.'" /><input placeholder="e.g. Absent" type="text" name="grades['.$i.']" value="'.$value->grade.'" /></td><td><input placeholder="Abs" maxlength="3" type="text" style="width:40px;" name="shortgrades['.$i.']" value="'.$value->shortgrade.'" /></td><td>'.$img.' <input type="file" name="img['.$i.']" value="" /></td></tr>';
                            $i++;
                        }
                    }
                    else
                    {
                        
                        $i = 0;
                        echo '<tr><td><input type="hidden" name="gradeids['.$i.']" value="" /><input placeholder="e.g. Absent" type="text" name="grades['.$i.']" value="" /></td><td><input placeholder="Abs" maxlength="3" type="text" style="width:40px;" name="shortgrades['.$i.']" value="" /></td><td>'.$img.' <input type="file" name="img['.$i.']" value="" /></td></tr>';
                        
                    }
                    
                echo '</table>';    
                
            break;
            
            
            
            case 'crit':
                
                require_capability('block/bcgt:addcriteriagradingstructure', $context);
                                
                
                $id = optional_param('id', false, PARAM_INT);
                $editingGrades = array();
                $editing = false;
                $cntGrades = 2;
                
                if ($id)
                {
                    $editing = $DB->get_record("block_bcgt_bspk_crit_grading", array("id" => $id));
                    if ($editing)
                    {
                        $editingGrades = $DB->get_records("block_bcgt_bspk_c_grade_vals", array("critgradingid" => $id), "points ASC");
                        $cntGrades = count($editingGrades) - 1;
                    }
                }
                
                
                echo "<script>var num = ".$cntGrades."; function addNewGSRow(){ num++; $('#gsTable').append('<tr><td><input type=\"text\" name=\"grades['+num+']\" /></td><td><input type=\"text\" style=\"width:40px;\" name=\"shortgrades['+num+']\" /></td><td><input type=\"text\" style=\"width:40px;\" name=\"points['+num+']\" /></td><td><input type=\"text\" style=\"width:40px;\" name=\"lowers['+num+']\" /></td><td><input type=\"text\" style=\"width:40px;\" name=\"uppers['+num+']\" /></td><td><input type=\"file\" name=\"img['+num+']\" accept=\"image/*\" /></td></tr>'); }</script>";
                
                echo '<h3>'.get_string('gradingstructure', 'block_bcgt').': '.get_string('criteria', 'block_bcgt').'</h3>';
                
                echo '<p>'.get_string('critgradingstructurehelp', 'block_bcgt').'</p>';
                echo '<p>'.get_string('critgradingpointshelp', 'block_bcgt').'</p>';
                echo '<p>'.get_string('critgradingrangehelp', 'block_bcgt').'</p>';
                
                
                if (isset($_POST['save'])){
                    
                    $name = trim($_POST['name']);
                    $gradeids = @$_POST['gradeids'];
                    $grades = $_POST['grades'];
                    $shortgrades = $_POST['shortgrades'];
                    $points = $_POST['points'];
                    $lowers = $_POST['lowers'];
                    $uppers = $_POST['uppers'];
                    $imgs = $_FILES['img'];
                    
                    if (empty($name)){
                        echo '<span style="color:red;">'.get_string('error:name', 'block_bcgt').'</span><br>';
                    } else {
                    
                        
                        if ($editing)
                        {
                            
                            $editing->name = $name;
                            $DB->update_record("block_bcgt_bspk_crit_grading", $editing);
                            
                            // Grades
                            $cnt = count($grades);

                            for($i = 0; $i < $cnt; $i++){

                                if (isset($gradeids[$i]))
                                {
                                                                        
                                    if (empty($grades[$i]))
                                    {
                                        $DB->delete_records("block_bcgt_bspk_c_grade_vals", array("id" => $gradeids[$i]));
                                    }
                                    else
                                    {
                                        $obj = $DB->get_record("block_bcgt_bspk_c_grade_vals", array("id" => $gradeids[$i]));
                                        if ($obj)
                                        {
                                            
                                            if (isset($imgs['name'][$i])){
                                                $img = new stdClass();
                                                $img->name = $imgs['name'][$i];
                                                $img->type = $imgs['type'][$i];
                                                $img->tmp_name = $imgs['tmp_name'][$i];
                                                $img->error = $imgs['error'][$i];
                                                $img->size = $imgs['size'][$i];
                                            }
                                                                                        
                                            $obj->grade = $grades[$i];
                                            $obj->shortgrade = $shortgrades[$i];
                                            $obj->points = $points[$i];
                                            $obj->rangelower = $lowers[$i];
                                            $obj->rangeupper = $uppers[$i];
                                            $obj->met = 1;
                                            
                                            
                                            // Upload image
                                            if (isset($img) && preg_match('/^image\//', $img->type))
                                            {
                                                
                                                $filename = $obj->id . '_' . $obj->shortgrade . '.jpg';
                                                move_uploaded_file( $img->tmp_name, $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/pix/grid_symbols/bespoke/'.$filename);
                                                $obj->img = $filename;
                                                
                                            }
                                            
                                            $DB->update_record('block_bcgt_bspk_c_grade_vals', $obj);
                                            
                                            
                                        }
                                    }
                                    
                                }
                                else
                                {
                                    
                                    if (empty($grades[$i])) continue;
                                    $obj = new stdClass();
                                    $obj->critgradingid = $id;
                                    $obj->grade = $grades[$i];
                                    $obj->shortgrade = $shortgrades[$i];
                                    $obj->points = $points[$i];
                                    $obj->rangelower = $lowers[$i];
                                    $obj->rangeupper = $uppers[$i];
                                    $obj->met = 1;
                                    $DB->insert_record('block_bcgt_bspk_c_grade_vals', $obj);
                                    
                                }
                                
                                
                               

                            }
                            
                            $editingGrades = $DB->get_records("block_bcgt_bspk_c_grade_vals", array("critgradingid" => $id), "points ASC");
                            
                            
                        }
                        else
                        {
                            
                            $obj = new stdClass();
                            $obj->name = $name;
                            $id = $DB->insert_record('block_bcgt_bspk_crit_grading', $obj);

                            $cnt = count($grades);

                            for($i = 0; $i < $cnt; $i++){

                                if (empty($grades[$i])) continue;

                                $obj = new stdClass();
                                $obj->critgradingid = $id;
                                $obj->grade = $grades[$i];
                                $obj->shortgrade = $shortgrades[$i];
                                $obj->points = $points[$i];
                                $obj->rangelower = $lowers[$i];
                                $obj->rangeupper = $uppers[$i];
                                $obj->met = 1;
                                
                                $obj->id = $DB->insert_record('block_bcgt_bspk_c_grade_vals', $obj);
                                
                                if (isset($imgs['name'][$i])){
                                    $img = new stdClass();
                                    $img->name = $imgs['name'][$i];
                                    $img->type = $imgs['type'][$i];
                                    $img->tmp_name = $imgs['tmp_name'][$i];
                                    $img->error = $imgs['error'][$i];
                                    $img->size = $imgs['size'][$i];
                                }

                                // Upload image
                                if (isset($img) && preg_match('/^image\//', $img->type))
                                {

                                    $filename = $obj->id . '_' . $obj->shortgrade . '.jpg';
                                    move_uploaded_file( $img->tmp_name, $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/pix/grid_symbols/bespoke/'.$filename);
                                    $obj->img = $filename;

                                }

                                $DB->update_record('block_bcgt_bspk_c_grade_vals', $obj);                                

                            }
                            
                        }
                        
                        echo '<span style="color:blue;">Saved</span><br>';
                    
                    }
                    
                    
                }
                
                $name = (isset($editing) && $editing) ? $editing->name : '';
                
                if (isset($editing) && $editing)
                {
                    echo '<input type="hidden" name="grading_id" value="'.$editing->id.'" />';
                }
                
                echo '<label>'.get_string('name').'</label> <input type="text" name="name" value="'.$name.'" /><br>';
                echo '<label>'.get_string('gradevalues', 'block_bcgt').'</label><br><small><a href="#" onclick="addNewGSRow();return false;">['.get_string('add').']</a></small><br>';
                echo '<table style="margin:auto;text-align:center;" id="gsTable">';
                    echo '<tr><th>'.get_string('grade').'</th><th>'.get_string('shortgrade', 'block_bcgt').'</th><th>'.get_string('points', 'block_bcgt').'</th><th>'.get_string('lowerrangescore', 'block_bcgt').'<br><small>(Optional)</small></th><th>'.get_string('upperrangescore', 'block_bcgt').'<br><small>(Optional)</small></th><th>'.get_string('gridimg', 'block_bcgt').'</th></tr>';
                    
                    if ($editingGrades)
                    {
                        $i = 0;
                        foreach($editingGrades as $grade)
                        {
                            $image = BespokeQualification::get_grid_image($grade->shortgrade, $grade->grade, $grade);
                            $img = "<img src='{$image->image}' alt='{$image->title}' />";
                            echo '<tr><td><input type="hidden" name="gradeids['.$i.']" value="'.$grade->id.'" /><input placeholder="e.g. Pass" type="text" name="grades['.$i.']" value="'.$grade->grade.'" /></td><td><input placeholder="P" maxlength="3" type="text" style="width:40px;" name="shortgrades['.$i.']" value="'.$grade->shortgrade.'" /></td><td><input type="text" style="width:40px;" name="points['.$i.']" value="'.$grade->points.'" /></td><td><input placeholder="" type="text" style="width:40px;" name="lowers['.$i.']" value="'.$grade->rangelower.'" /></td><td><input placeholder="" type="text" style="width:40px;" name="uppers['.$i.']" value="'.$grade->rangeupper.'" /></td><td>'.$img.' <input type="file" name="img['.$i.']" value="" /></td></tr>';
                            $i++;
                        }
                    }
                    else
                    {
                        echo '<tr><td><input placeholder="e.g. Pass" type="text" name="grades[0]" /></td><td><input placeholder="P" type="text" style="width:40px;" name="shortgrades[0]" maxlength="3" /></td><td><input placeholder="1" type="text" style="width:40px;" name="points[0]" /></td><td><input placeholder="1.0" type="text" style="width:40px;" name="lowers[0]" /></td><td><input placeholder="1.5" type="text" style="width:40px;" name="uppers[0]" /></td><td><input type="file" name="img[0]" value="" /></td></tr>';
                        echo '<tr><td><input placeholder="e.g. Merit" type="text" name="grades[1]" /></td><td><input placeholder="M" type="text" style="width:40px;" name="shortgrades[1]" maxlength="3" /></td><td><input placeholder="2" type="text" style="width:40px;" name="points[1]" /></td><td><input placeholder="1.6" type="text" style="width:40px;" name="lowers[1]" /></td><td><input placeholder="2.5" type="text" style="width:40px;" name="uppers[1]" /></td><td><input type="file" name="img[1]" value="" /></td></tr>';
                        echo '<tr><td><input placeholder="e.g. Distinction" type="text" name="grades[2]" /></td><td><input placeholder="D" type="text" style="width:40px;" name="shortgrades[2]" maxlength="3" /></td><td><input placeholder="3" type="text" style="width:40px;" name="points[2]" /></td><td><input placeholder="2.6" type="text" style="width:40px;" name="lowers[2]" /></td><td><input placeholder="3" type="text" style="width:40px;" name="uppers[2]" /></td><td><input type="file" name="img[2]" value="" /></td></tr>';
                    }
                    
                    
                echo '</table>';
                echo '<br><br>';
                                
            break;
            
            
        }
        
        echo '<input type="submit" name="save" value="'.get_string('save', 'block_bcgt').'" /> ';
        
		echo '</form>';
        
        echo '<br><br>';
        
        
        
        
		echo "<form method='POST' action=''>";
        
        // Delete existing structures
        switch($type)
        {
            case 'qual':
                
                echo '<b>'.get_string('deleteexisting', 'block_bcgt').'</b><br>';
                
                if (isset($_POST['deletestructures']) && ctype_digit($_POST['delete'])){
                    
                    $id = $_POST['delete'];
                    
                    // Check if any quals using this structure
                    $check = $DB->get_records("block_bcgt_bespoke_qual", array("gradingstructureid" => $id));
                    if ($check)
                    {
                        $names = array();
                        foreach($check as $qual)
                        {
                            $qualrecord = $DB->get_record("block_bcgt_qualification", array("id" => $qual->bcgtqualid), "id, name");
                            $names[] = '['.$qualrecord->id.'] '.$qualrecord->name;
                        }
                        echo '<span style="color:red;">'.get_string('error:cannotdelqualstructure', 'block_bcgt').'<br>'.implode(",", $names).'</span><br>';
                    }
                    else
                    {
                        $DB->delete_records('block_bcgt_bspk_qual_grading', array('id' => $id));
                        $DB->delete_records('block_bcgt_bspk_q_grade_vals', array('qualgradingid' => $id));
                        echo '<span style="color:blue;">Deleted</span><br>';
                    }
                    
                }
                
                
                
                echo '<select name="delete" id="bcgtDelGradStruct">';
                    echo '<option value="">'.get_string('pleaseselect', 'block_bcgt').'</option>';
                    
                    $structures = BespokeQualification::get_qual_grading_structures();
                    if ($structures)
                    {
                        foreach($structures as $structure)
                        {
                            $output = "<option value='{$structure->id}'>";
                            $output .= $structure->name . ": &nbsp;&nbsp;&nbsp; ";
                            foreach($structure->values as $val)
                            {
                                $output .= $val->grade . " ({$val->rangelower} - {$val->rangeupper} ) &nbsp;";
                            }

                            $output .= "</option>";
                            echo $output;
                        }
                    }
                    
                echo '</select>';
                echo '<br>';
                echo '<input type="submit" name="deletestructures" value="Delete" />';
                
            break;
            
            
             case 'unit':
                
                echo '<b>'.get_string('deleteexisting', 'block_bcgt').'</b><br>';
                
                if (isset($_POST['deletestructures']) && ctype_digit($_POST['delete'])){
                    
                    $id = $_POST['delete'];
                    
                    // CHeck if any quals using this structure
                    $check = $DB->get_records("block_bcgt_bespoke_unit", array("gradingstructureid" => $id));
                    if ($check)
                    {
                        $names = array();
                        foreach($check as $qual)
                        {
                            $qualrecord = $DB->get_record("block_bcgt_unit", array("id" => $qual->bcgtqualid), "id, name");
                            $names[] = '['.$qualrecord->id.'] '.$qualrecord->name;
                        }
                        echo '<span style="color:red;">'.get_string('error:cannotdeunitstructure', 'block_bcgt').'<br>'.implode(",", $names).'</span><br>';
                    }
                    else
                    {
                        $DB->delete_records('block_bcgt_bspk_unit_grading', array('id' => $id));
                        $DB->delete_records('block_bcgt_bspk_u_grade_vals', array('unitgradingid' => $id));
                        echo '<span style="color:blue;">Deleted</span><br>';
                    }
                    
                }
                
                
                
                echo '<select name="delete">';
                    echo '<option value="">'.get_string('pleaseselect', 'block_bcgt').'</option>';
                    
                    $structures = BespokeUnit::get_unit_grading_structures();
                    if ($structures)
                    {
                        foreach($structures as $structure)
                        {
                            $output = "<option value='{$structure->id}'>";
                            $output .= $structure->name . ": &nbsp;&nbsp;&nbsp; ";
                            foreach($structure->values as $val)
                            {
                                $output .= $val->grade . " [{$val->points}] ({$val->rangelower} - {$val->rangeupper} ), &nbsp; ";
                            }

                            $output .= "</option>";
                            echo $output;
                        }
                    }
                    
                echo '</select>';
                echo '<br>';
                echo '<input type="submit" name="deletestructures" value="Delete" />';
                
            break;
            
            
            
            case 'crit':
                                
                echo '<b>'.get_string('editexisting', 'block_bcgt').'</b><br>';
                                
                echo "<select name='edit' onchange='window.location.href=\"{$CFG->wwwroot}/blocks/bcgt/forms/grading_structure.php?type=crit&id=\"+this.value;return false;'>";
                    echo '<option value="">'.get_string('pleaseselect', 'block_bcgt').'</option>';
                    
                    $structures = BespokeCriteria::get_crit_grading_structures();
                    if ($structures)
                    {
                        foreach($structures as $structure)
                        {
                            $output = "<option value='{$structure->id}'>";
                            $output .= $structure->name . ": &nbsp;&nbsp;&nbsp; ";
                            foreach($structure->values as $val)
                            {
                                $output .= $val->grade . " [{$val->points}] ({$val->rangelower} - {$val->rangeupper} ), &nbsp; ";
                            }

                            $output .= "</option>";
                            echo $output;
                        }
                    }
                    
                echo '</select>';                
                
                
                echo '<br><br><br>';
                
                
                
                echo '<b>'.get_string('deleteexisting', 'block_bcgt').'</b><br>';
                
                if (isset($_POST['deletestructures']) && ctype_digit($_POST['delete'])){
                    
                    $id = $_POST['delete'];
                    
                    // CHeck if any quals using this structure
                    $check = $DB->get_records("block_bcgt_bespoke_criteria", array("gradingstructureid" => $id));
                    if ($check)
                    {
                        $names = array();
                        foreach($check as $qual)
                        {
                            $qualrecord = $DB->get_record("block_bcgt_criteria", array("id" => $qual->bcgtqualid), "id, name");
                            $names[] = '['.$qualrecord->id.'] '.$qualrecord->name;
                        }
                        echo '<span style="color:red;">'.get_string('error:cannotdelcritstructure', 'block_bcgt').'<br>'.implode(",", $names).'</span><br>';
                    }
                    else
                    {
                        $DB->delete_records('block_bcgt_bspk_crit_grading', array('id' => $id));
                        $DB->delete_records('block_bcgt_bspk_c_grade_vals', array('critgradingid' => $id));
                        echo '<span style="color:blue;">Deleted</span><br>';
                    }
                    
                }
                
                
                
                echo '<select name="delete">';
                    echo '<option value="">'.get_string('pleaseselect', 'block_bcgt').'</option>';
                    
                    $structures = BespokeCriteria::get_crit_grading_structures();
                    if ($structures)
                    {
                        foreach($structures as $structure)
                        {
                            $output = "<option value='{$structure->id}'>";
                            $output .= $structure->name . ": &nbsp;&nbsp;&nbsp; ";
                            foreach($structure->values as $val)
                            {
                                $output .= $val->grade . " [{$val->points}] ({$val->rangelower} - {$val->rangeupper} ), &nbsp; ";
                            }

                            $output .= "</option>";
                            echo $output;
                        }
                    }
                    
                echo '</select>';
                echo '<br>';
                echo '<input type="submit" name="deletestructures" value="Delete" />';
                
            break;
            
            
            
        }
        echo '</form>';
        
        echo '<p>'.get_string('qualgradingstructurehelpex', 'block_bcgt').'</p>';
        
        
	echo '</div>';


echo $OUTPUT->footer();
?>
