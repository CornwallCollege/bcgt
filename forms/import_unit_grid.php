<?php
set_time_limit(0);
require_once '../../../config.php';
require_once $CFG->dirroot . '/blocks/bcgt/lib.php';

require_login();

$qualID = required_param('qualID', PARAM_INT);
$unitID = required_param('unitID', PARAM_INT);
$courseID = optional_param('courseID', SITEID, PARAM_INT);

if ($courseID < 1){
    $courseID = SITEID;
}

$context = context_course::instance($courseID);

if (!has_capability('block/bcgt:importexportunitgrids', $context)){
    print_error('invalid access');
}

$loadParams = new stdClass();
$loadParams->loadLevel = \Qualification::LOADLEVELALL;
$loadParams->loadAward = true;
$loadParams->loadTargets = true;
$unit = Unit::get_unit_class_id($unitID, $loadParams);


$PAGE->set_context(context_system::instance());
$PAGE->set_url($CFG->wwwroot . '/blocks/bcgt/forms/import_unit_grid.php?qualID=' . $qualID . '&unitID=' . $unitID . '&courseID=' . $courseID, array());
$PAGE->set_title(get_string('importgrid', 'block_bcgt'));
$PAGE->set_heading(get_string('importgrid', 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=track&cID='.$courseID,'title');

if ($unit){
    $PAGE->navbar->add($unit->get_name(),$CFG->wwwroot.'/blocks/bcgt/grids/unit_grid.php?uID='.$unitID.'&qID='.$qualID.'&cID='.$courseID,'title');
}

$PAGE->navbar->add(get_string('importgrid', 'block_bcgt'),$CFG->wwwroot.'/blocks/bcgt/forms/import_unit_grid.php?qualID='.$qualID.'&unitID='.$unitID.'&courseID='.$courseID,'title');

load_javascript(true);
load_css(true);

echo $OUTPUT->header();

if ($unit && method_exists($unit, 'import_unit_grid')){
    
    $error = array();
    $output = false;

    // Submitted
    if (isset($_POST['submit_sheet']) && isset($_FILES['sheet']) && $_FILES['sheet']['size'] > 0)
    {
                
        $file = $_FILES['sheet'];

        if ($file['error']){
            $error[] = 'There was a problem opening the file';
        }
        
        if (!$error)
        {
        
            // Check mime type of file to make sure it is csv
            $fInfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($fInfo, $file['tmp_name']);
            finfo_close($fInfo);

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        
            // On linux PHP says the mime type of an xlsx is application/zip, which is handy...
            if ( ($mime != 'application/vnd.ms-excel' && $mime != 'application/zip' && $mime != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') || $ext != 'xlsx'){
                $error[] = 'Invalid file format. Expected: application/vnd.ms-excel or application/vnd.openxmlformats-officedocument.spreadsheetml.sheet (.xlsx) Found: ' . $mime . ' ('.$ext.')';
            }
                    
        }
        
        // No errors, so carry on
        if (!$error)
        {
            
            require_once $CFG->dirroot . '/blocks/bcgt/lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
            
            $output = $unit->import_unit_grid($qualID, $file, false);
            $confirm = false;
            
        }
                
    }
    elseif( isset($_POST['submit_confirm']) && isset($_POST['now']) )
    {
        
        require_once $CFG->dirroot . '/blocks/bcgt/lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
        
        // Get the tmp file we saved
        $now = $_POST['now'];
        $file = $CFG->dataroot . DIRECTORY_SEPARATOR . 'bcgt' . DIRECTORY_SEPARATOR . 'import_unit_grids' . DIRECTORY_SEPARATOR . $qualID . '_' . $unitID . '_' . $now . '.xlsx';
        if (!file_exists($file))
        {
            print_error('Cannot find file: ' . str_replace($CFG->dataroot, '', $file));
        }
        
        // Fake the use of _FILES array for use in method
        $fileArray = array();
        $fileArray['tmp_name'] = $file;
        
        $output = $unit->import_unit_grid($qualID, $fileArray, true);
        $confirm = true;
        
    }
    
    
    
    
    
    
    
    
    echo "<h2 class='c'>{$unit->get_display_name()}</h2>";
    
    echo "<p class='c'>".get_string('importunitgrid:desc', 'block_bcgt')."</p>";
    
    if ($error){
        
        echo "<div class='c'>";
            foreach($error as $err){
                echo "<span style='color:red;'>{$err}</span><br>";
            }
        echo "</div>";
        
    }
    
    echo "<br>";
    
    echo "<form action='' method='post' class='c' enctype='multipart/form-data'>";
    
    echo '<input id="uploadFile" placeholder="" disabled="disabled" />
          <div class="fileUpload btn btn-primary">
              <span>Choose File</span>
              <input id="uploadBtn" name="sheet" type="file" class="upload" />
          </div>';    
        
    echo '<br><br>';
    
    echo '<input class="btn" type="submit" name="submit_sheet" value="'.get_string('import', 'block_bcgt').'" />';
    
    echo "</form>";
    
    echo "<br><br>";
    
    if ($output){
        
        // Imported
        if ($confirm)
        {
            echo "<div class='importsuccessful'>Successfully updated {$output['summary']} criteria. <small><a href='#' onclick='$(\"#import_output\").toggle();return false;'>[Show details]</a></small></div><br><br>";
            $class = 'cmdoutput';
            $display = 'none';
        }
        else
        {
            $class = 'importoutput';
            $display = 'block';
        }
        
        echo "<div id='import_output' class='{$class}' style='display:{$display};'>";
            echo $output['output'];
        echo "</div>";
        
    }
    
    echo "<br><br>";
    
    echo "<p class='c'>";
        echo "<br><br>";
        echo "<input type='button' class='btn' value='Back to Grid' onclick='window.location.href=\"{$CFG->wwwroot}/blocks/bcgt/grids/unit_grid.php?uID={$unitID}&qID={$qualID}&cID={$courseID}\"' />";
    echo "</p>";

        
} else {
    echo "Grids of this qualification family cannot yet be imported.";
}

echo $OUTPUT->footer();

echo "<script>  
                   
   $(document).ready( function(){

        $('#uploadBtn').unbind('change'); 
        $('#uploadBtn').bind('change', function(){
            console.log('changed');
             $('#uploadFile').val( $('#uploadBtn').val() );

        });

    } );

</script>";

exit;