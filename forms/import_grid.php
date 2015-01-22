<?php
//
//set_time_limit(0);
//require_once '../../../config.php';
//require_once $CFG->dirroot . '/blocks/bcgt/lib.php';
//
//require_login();
//
//if (!isset($_GET['qualID'])) exit;
//
//$qualID = $_GET['qualID'];
//
//$loadParams = new stdClass();
//$loadParams->loadLevel = \Qualification::LOADLEVELALL;
//$loadParams->loadAward = true;
//$loadParams->loadTargets = true;
//$qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
//
//$PAGE->set_context(context_system::instance());
//$PAGE->set_url($CFG->wwwroot . '/blocks/bcgt/forms/import_grid.php?qualID=' . $qualID, array());
//$PAGE->set_title(get_string('importgrid', 'block_bcgt'));
//$PAGE->set_heading(get_string('importgrid', 'block_bcgt'));
//$PAGE->set_pagelayout( bcgt_get_layout() );
//$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=track','title');
//
//if ($qualification){
//    $PAGE->navbar->add($qualification->get_name(),$CFG->wwwroot.'/blocks/bcgt/grids/class_grid.php?qID='.$qualID,'title');
//}
//
//$PAGE->navbar->add(get_string('importgrid', 'block_bcgt'),$CFG->wwwroot.'/blocks/bcgt/forms/import_grid.php?qualID='.$qualID,'title');
//
//load_javascript(true);
//load_css(true);
//
//echo $OUTPUT->header();
//
//if ($qualification && method_exists($qualification, 'import_grid')){
//    
//    $error = array();
//    $output = "";
//
//    // Submitted
//    if (isset($_POST['submit_sheet']) && isset($_FILES['sheet']))
//    {
//                
//        $file = $_FILES['sheet'];
//        if ($file['error']){
//            $error[] = 'There was a problem opening the file';
//        }
//        
//        // Check mime type of file to make sure it is csv
//        $fInfo = finfo_open(FILEINFO_MIME_TYPE);
//            $mime = finfo_file($fInfo, $file['tmp_name']);
//        finfo_close($fInfo);
//        
//        if ($mime != 'application/vnd.ms-excel'){
//            $error[] = 'Invalid file format. Expected: application/vnd.ms-excel (.xlsx)';
//        }
//        
//        
//        // No errors, so carry on
//        if (!$error)
//        {
//            
//            $confirm = (isset($_POST['confirm'])) ? true : false;
//            
//            require_once $CFG->dirroot . '/blocks/bcgt/lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
//            
//            $output = $qualification->import_grid($file['tmp_name'], $confirm);
//            
//        }
//        
//        
//        
//    }
//    
//    
//    
//    
//    
//    
//    
//    
//    echo "<h2 class='c'>{$qualification->get_display_name()}</h2>";
//    
//    echo "<p class='c'>".get_string('importgrid:desc', 'block_bcgt')."</p>";
//    
//    if ($error){
//        
//        echo "<div class='c'>";
//            foreach($error as $err){
//                echo "<span style='color:red;'>{$err}</span><br>";
//            }
//        echo "</div>";
//        
//    }
//    
//    echo "<br>";
//    
//    echo "<form action='' method='post' class='c' enctype='multipart/form-data'>";
//    
//    echo '<input id="uploadFile" placeholder="" disabled="disabled" />
//          <div class="fileUpload btn btn-primary">
//              <span>Choose File</span>
//              <input id="uploadBtn" name="sheet" type="file" class="upload" />
//          </div>';    
//        
//    echo '<br><br>';
//    
//    echo '<input class="btn" type="submit" name="submit_sheet" value="'.get_string('import', 'block_bcgt').'" />';
//    
//    echo "</form>";
//    
//    echo "<br><br>";
//    
//    if ($output != ""){
//        
//        $class = ($confirm) ? 'cmdoutput' : 'importoutput';
//        echo "<div class='{$class}'>";
//            echo $output;
//        echo "</div>";
//        
//    }
//    
//
//    
//    echo "<script>  
//                   
//                   $('#uploadBtn').bind('change', function(){
//                   
//                        $('#uploadFile').val( $('#uploadBtn').val() );
//
//                   });
//                   
//                   
//
//          </script>";
//    
//    
//        
//} else {
//    echo "Grids of this qualification family cannot yet be imported.";
//}
//
//echo $OUTPUT->footer();
//exit;