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

$PAGE->set_url('/blocks/bcgt/forms/general_settings.php', array());
$PAGE->set_title(get_string('generalsettings', 'block_bcgt'));
$PAGE->set_heading(get_string('generalsettings', 'block_bcgt'));
$PAGE->set_pagelayout('login');
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),'my_dashboard.php','title');
$PAGE->navbar->add(get_string('myDashboard', 'block_bcgt'),'my_dashboard.php?tab=dash','title');
$PAGE->navbar->add(get_string('dashtabadm', 'block_bcgt'),'my_dashboard.php?tab=adm','title');
$PAGE->navbar->add(get_string('generalsettings', 'block_bcgt'));

if (isset($_POST['submit_stud_settings']))
{
    
    unset($_POST['submit_stud_settings']);
    $settings = $_POST;
    
    // Custom Links first
    $DB->delete_records("block_bcgt_settings", array("setting" => "custom_stud_link"));
    
    if (isset($settings['custom_link_url'])){
        
        $urls = $settings['custom_link_url'];
        $titles = $settings['custom_link_title'];
        $cnt = count($urls);
        
        for ($i = 0; $i < $cnt; $i++)
        {
            
            $url = trim($urls[$i]);
            $title = trim($titles[$i]);
            
            if (empty($url) || empty($title)) continue;
            
            // Add domain if not set
            if (!preg_match("/^http:\/\//", $url)){
                $url = $CFG->wwwroot . '/' . $url;
            }
            
            $obj = new stdClass();
            $obj->setting = "custom_stud_link";
            $obj->value = $url . ',' . $title;
            bcgt_insert_setting($obj);
            
        }
        
    }
    
}

elseif (isset($_POST['submit_unit_settings']))
{
    
    unset($_POST['submit_unit_settings']);
    $settings = $_POST;
    
    // Custom Links first
    $DB->delete_records("block_bcgt_settings", array("setting" => "custom_unit_link"));
    
    if (isset($settings['custom_link_url'])){
        
        $urls = $settings['custom_link_url'];
        $titles = $settings['custom_link_title'];
        $cnt = count($urls);
        
        for ($i = 0; $i < $cnt; $i++)
        {
            
            $url = trim($urls[$i]);
            $title = trim($titles[$i]);
            
            if (empty($url) || empty($title)) continue;
            
            // Add domain if not set
            if (!preg_match("/^http:\/\//", $url)){
                $url = $CFG->wwwroot . '/' . $url;
            }
            
            $obj = new stdClass();
            $obj->setting = "custom_unit_link";
            $obj->value = $url . ',' . $title;
            bcgt_insert_setting($obj);
            
        }
        
    }
    
}

echo $OUTPUT->header();

echo "<script>";

echo <<<JS

    function cloneTableRow(id){

        var tr = $('#'+id);
        var c = tr.clone();
        $(c).attr('id', '');
        c.find(":text").val('');
        tr.after(c);

    }

JS;

echo "</script>";

echo $OUTPUT->heading(get_string('generalsettings', 'block_bcgt'));

echo "<div>";

echo "<form action='' method='post'>";

echo "<b>".get_string('studgrid', 'block_bcgt')."</b>";

echo "<table>";


    // Custom Links
    $links = bcgt_get_setting("custom_stud_link");
    if ($links)
    {
        foreach((array)$links as $link)
        {
            $explode = explode(",", $link);
            $url = $explode[0];
            $title = $explode[1];
            echo "<tr>";
            echo "<td>".get_string('customlink', 'block_bcgt')." <a href='#' onclick='cloneTableRow(\"custom_link_stud_row\");return false;'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/greenPlus.png' alt='add another' /></a><br><small>".get_string('helpcustomlink', 'block_bcgt')."</small></td>";
            echo "<td><small>URL <span title='".get_string('helpcustomlinkurl', 'block_bcgt')."'>[?]</span></small><br><input type='text' name='custom_link_url[]' value='{$url}' class='long' /></td>";
            echo "<td><small>".get_string('title', 'block_bcgt')."</small><br><input type='text' name='custom_link_title[]' value='{$title}' /></td>";
            echo "</tr>";
        }
    }
    
echo "<tr id='custom_link_stud_row'>";

    
    echo "<td>".get_string('customlink', 'block_bcgt')." <a href='#' onclick='cloneTableRow(\"custom_link_stud_row\");return false;'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/greenPlus.png' alt='add another' /></a><br><small>".get_string('helpcustomlink', 'block_bcgt')."</small></td>";
    echo "<td><small>URL <span title='".get_string('helpcustomlinkurl', 'block_bcgt')."'>[?]</span></small><br><input type='text' name='custom_link_url[]' value='' class='long' /></td>";
    echo "<td><small>".get_string('title', 'block_bcgt')."</small><br><input type='text' name='custom_link_title[]' value='' /></td>";
    
echo "</tr>";

echo "</table>";


echo "<input type='submit' name='submit_stud_settings' value='".get_string('save', 'block_bcgt')."' />";

echo "</form>";

echo "<br><br>";


echo "<form action='' method='post'>";

echo "<b>".get_string('unitgrid', 'block_bcgt')."</b>";

echo "<table>";


    // Custom Links
    $links = bcgt_get_setting("custom_unit_link");
    if ($links)
    {
        foreach((array)$links as $link)
        {
            $explode = explode(",", $link);
            $url = $explode[0];
            $title = $explode[1];
            echo "<tr>";
            echo "<td>".get_string('customlink', 'block_bcgt')." <a href='#' onclick='cloneTableRow(\"custom_link_unit_row\");return false;'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/greenPlus.png' alt='add another' /></a><br><small>".get_string('helpcustomlink', 'block_bcgt')."</small></td>";
            echo "<td><small>URL <span title='".get_string('helpcustomlinkurl', 'block_bcgt')."'>[?]</span></small><br><input type='text' name='custom_link_url[]' value='{$url}' class='long' /></td>";
            echo "<td><small>".get_string('title', 'block_bcgt')."</small><br><input type='text' name='custom_link_title[]' value='{$title}' /></td>";
            echo "</tr>";
        }
    }
    
echo "<tr id='custom_link_unit_row'>";

    
    echo "<td>".get_string('customlink', 'block_bcgt')." <a href='#' onclick='cloneTableRow(\"custom_link_unit_row\");return false;'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/greenPlus.png' alt='add another' /></a><br><small>".get_string('helpcustomlinkunit', 'block_bcgt')."</small></td>";
    echo "<td><small>URL <span title='".get_string('helpcustomlinkurl', 'block_bcgt')."'>[?]</span></small><br><input type='text' name='custom_link_url[]' value='' class='long' /></td>";
    echo "<td><small>".get_string('title', 'block_bcgt')."</small><br><input type='text' name='custom_link_title[]' value='' /></td>";
    
echo "</tr>";

echo "</table>";


echo "<input type='submit' name='submit_unit_settings' value='".get_string('save', 'block_bcgt')."' />";

echo "</form>";

echo "</div>";

echo $OUTPUT->footer();