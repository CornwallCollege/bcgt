<?php

require_once $CFG->dirroot . '/blocks/bcgt/lib.php';

/**
 * 
 */
class ReportingSystem {
    
    public static $qualArray;
    
    public static function get_available_elements(){
        
        $array = array(
            'noexpectedcredits' => 'No. Credits (Expected)',
            'nostudcredits' => 'No. Credits (Actual)',
            'creditsoffset' => 'Credits Offset',
            'defcredits' => 'Default Credits',
            'mincredits' => 'Min Credits',
            'maxcredits' => 'Max Credits',
            'nostudwrongcredits' => 'No. Students With Wrong Credits',
            'nostudbelowcredits' => 'No. Students With Too Few Credits',
            'nostudcorrectcredits' => 'No. Students With Correct Credits',
            'nostudabovecredits' => 'No. Students With Too Many Credits',
            'noquals' => 'No. Qualifications',
            'nounits' => 'No. Units',
            'nocrit' => 'No. Criteria',
            'nostud' => 'No. Students',
            'nostaff' => 'No. Staff',
            'predgrade' => 'Predicted Grade',
            'targetgrade' => 'Target Grade',
            'nostudbelowtarget' => 'No. Students Behind Target',
            'nostudontarget' => 'No. Students On Target',
            'nostudaheadtarget' => 'No. Students Ahead Of Target',
            'percentasscritachieved' => '% of Assignment Criteria Achieved',
            'noasscriteria' => 'No. Assignment Criteria',
            'nostudasscriteria' => 'No. Student Assignment Criteria'
        );
        
        
        return $array;
        
    }
    
    public static function display_create_js(){
        
        $output = <<<JS
                <script>
                    function changeStartingPoint(val){
                
                        if (val == 'qual'){
                            $('#start_level').val('FAMILY');
                        } else if (val == 'unit'){
                            $('#start_level').val('FAMILY');
                            val = 'qual'; // Use the same select menu
                        } else if (val == 'cat'){
                            $('#start_level').val('CATEGORY');
                        }
                
                        $('.hidden_start_choice').hide();
                        $('#hidden_start_choice_'+val).show();
                
                    
                    }
                </script>
JS;
        
        return $output;
        
    }
    
    
    public static function display_create_form(){
        
        global $CFG, $DB;
        
        $reportID = optional_param('report_id', false, PARAM_INT);
        $report = false;
        $data = false;
        
        if ($reportID){
            
            $report = $DB->get_record("block_bcgt_repsys_reports", array("id" => $reportID));
            if ($report){
                $data = unserialize($report->data);
            }
            
        }
        
        
        
        
        $output = "";
        
        $output .= self::display_create_js();
        
        if (isset($_POST['save_report']))
        {
            $result = self::submit_save_report();
            if ($result['result'] === true)
            {
                
                redirect( $CFG->wwwroot . '/blocks/bcgt/forms/my_dashboard.php?tab=reporting&action=view&id=' . $result['report']->id );
                exit;
                
            }
            else
            {
                if ($result['errors'])
                {
                    foreach($result['errors'] as $err)
                    {
                        $output .= "<span style='color:red;text-align:center;'>Error: {$err}<br></span>";
                    }
                    $output .= "<br>";
                }
            }
        }
        
        
        if ($report){
            $output .= "<h2 class='c'>Editing Report ({$report->name})</h2><br>";
        }
        else
        {
            $output .= "<h3 class='d'>Create A Report</h2><br>";
        }
        
                
        $output .= "<form action='' method='post'>";
        
        if ($report){
            $output .= "<input type='hidden' name='report_id' value='{$report->id}' />";
        }
        
        $output .= "<div class='bcgt_report_create_area' style='float:left;width:30%;'>";
        
        $output .= "<span><b>1.) Starting Point</b></span><br>";
        $output .= "<select name='start_type' onchange='changeStartingPoint(this.value);'>";
            $output .= "<option value=''></option>";
            $output .= "<option value='qual' ".( (isset($data) && $data['start_type'] == 'qual') ? 'selected' : '' )." >Qualification Family</option>";
            $output .= "<option value='unit' ".( (isset($data) && $data['start_type'] == 'unit') ? 'selected' : '' )." >Unit Family</option>";
            $output .= "<option value='cat' ".( (isset($data) && $data['start_type'] == 'cat') ? 'selected' : '' )." >Course Category</option>";
        $output .= "</select><br><br>";
        
        $startLevel = '';
        if (isset($data) && $data['start_type'] == 'qual') $startLevel = 'FAMILY';
        elseif (isset($data) && $data['start_type'] == 'unit') $startLevel = 'FAMILY';
        elseif (isset($data) && $data['start_type'] == 'cat') $startLevel = 'CATEGORY';
        
        $output .= "<input type='hidden' id='start_level' name='start_level' value='".$startLevel."' />";
        
        $display = 'none';
        if ($report && ($data['start_type'] == 'qual' || $data['start_type'] == 'unit')){
            $display = 'block';
        }
        
        $output .= "<div class='hidden_start_choice' id='hidden_start_choice_qual' style='display:{$display};' >";

        $families = get_qualification_type_families_used();
        
            $output .= "<select name='start_qual_unit'>";
            
                $output .= "<option value=''></option>";
                
                if ($families)
                {
                    $output .= "<option value='all' ".( (isset($data) && $data['start_point'] == 'all') ? 'selected' : '' ).">All</option>";
                    foreach($families as $family)
                    {
                        $output .= "<option value='{$family->id}' ".( (isset($data) && $data['start_point'] == $family->id) ? 'selected' : '' ).">{$family->family}</option>";
                    }
                }
                
            
            $output .= "</select>";
        
        $output .= "</div>";
        
        $display = 'none';
        if ($report && $data['start_type'] == 'cat'){
            $display = 'block';
        }
        
        $output .= "<div class='hidden_start_choice' id='hidden_start_choice_cat' style='display:{$display};'>";
            
            $categories = get_config('bcgt', 'reportingcats');
            if ($categories)
            {
                
                $output .= "<select name='start_cat' style='max-width:90%;'>";
                $output .= "<option value=''></option>";
                //$output .= "<option value='all' ".( (isset($data) && $data['start_point'] == 'all') ? 'selected' : '' ).">All</option>";
                
                $catArray = explode(",", $categories);
                foreach($catArray as $catID)
                {
                    
                    $catName = self::get_course_category_name_with_parent($catID);
                    $output .= "<option value='{$catID}' ".( (isset($data) && $data['start_point'] == $catID) ? 'selected' : '' ).">{$catName}</option>";
                    
                }
                
                $output .= "</select>";
                
            }
            else
            {
                $output .= "<p>No Categories Defined in BCGT Settings</p>";
            }
        
        $output .= "</div>";
        
        $output .= "</div>";
        
        
        
        
        $output .= "<div class='bcgt_report_create_area' style='float:left;width:30%;'>";
        
            $output .= "<span><b>2.) Filters</b></span><br>";
            
            $levels = bcgt_get_all_levels();
            if ($levels)
            {
                foreach($levels as $level)
                {
                    if (preg_match("/[0-9]/", $level->trackinglevel))
                    {
                        $output .= "<input type='checkbox' name='filters[levels][]' value='{$level->id}' ".( (isset($data['filters']['levels']) && in_array($level->id, $data['filters']['levels']) ) ? 'checked' : '' )." /> {$level->trackinglevel}<br>";
                    }
                }
            }
                        
            $output .= "<br>";
            
            $subtypes = bcgt_get_all_subtypes();
            
            if ($subtypes)
            {
                foreach($subtypes as $subtype)
                {
                    $output .= "<input type='checkbox' name='filters[subtypes][]' value='{$subtype->id}' ".( (isset($data['filters']['subtypes']) && in_array($subtype->id, $data['filters']['subtypes']) ) ? 'checked' : '' )." /> {$subtype->subtype}<br>";
                }
            }
            
            
            if (get_config('bcgt', 'reportingftptfilter') == 1){
                $output .= "<br>";
                $output .= "<input type='checkbox' name='filters[mode][]' value='FT' ".( (isset($data['filters']['mode']) && in_array('FT', $data['filters']['mode']) ) ? 'checked' : '' )." /> Full Time<br>";
                $output .= "<input type='checkbox' name='filters[mode][]' value='PT' ".( (isset($data['filters']['mode']) && in_array('PT', $data['filters']['mode']) ) ? 'checked' : '' )." /> Part Time<br>";
            }
                        
            $output .= "<br><br>";
        
        $output .= "</div>";
        
        
        
        
        
        
        $output .= "<div class='bcgt_report_create_area' style='float:left;width:30%;'>";
        
            $output .= "<span><b>3.) Elements</b></span><br>";
            
            $elements = self::get_available_elements();
                        
            if ($elements)
            {
                foreach($elements as $element => $name)
                {
                    $output .= "<input type='checkbox' name='elements[]' value='{$element}' ".( (isset($data['elements']) && in_array($element, $data['elements']) ) ? 'checked' : '' )." /> {$name}<br>";
                }
            }     
            
            
        $output .= "</div>";
        
      
        $output .= "<br style='clear:both;'>";
        
        
        $output .= "<p class='c'><b>4.)</b> <input type='text' placeholder='Report Name' name='report_name' value='".( ( $report ) ? $report->name : '' )."' /> <input type='submit' name='save_report' value='Save' /></p>";
        
        $output .= "</form>";
        
        
        return $output;
        
    }
    
    
    public static function get_course_category_name_with_parent($catID){
        
        global $DB;
        
        $cat = $DB->get_record("course_categories", array("id" => $catID));
        if (!$cat) return false;
        
        $name = $cat->name;
        
        if ($cat->parent > 0){
            $par = $DB->get_record("course_categories", array("id" => $cat->parent));
            if ($par){
                $name = $par->name . ' // ' . $name;
            }
        }
        
        return $name;
        
    }
    
    
    public static function submit_save_report(){
                
        global $DB, $USER;
        
        $result = array(
            'result' => false,
            'errors' => array(),
            'report' => false
        );
                
        $startType = $_POST['start_type'];
        $level = $_POST['start_level'];
        
        if ($startType == 'cat'){
            $start = $_POST['start_cat'];
        } else {
            $start = $_POST['start_qual_unit'];
        }
        
        
        $filters = @$_POST['filters'];
        $elements = @$_POST['elements'];
        $name = trim($_POST['report_name']);
        
        $reportID = (isset($_POST['report_id'])) ? $_POST['report_id'] : false;;
        
        
        if (empty($startType)){
            $result['errors'][] = 'Starting Point Type Must Be Set';
        }
        
        if (empty($startType)){
            $result['errors'][] = 'Starting Level Must Be Set';
        }
        
        if (empty($start)){
            $result['errors'][] = 'Starting Point Must Be Set';
        }
        
        if (empty($elements)){
            $result['errors'][] = 'At Least One Element Must Be Chosen';
        }
        
        if (empty($name)){
            $result['errors'][] = 'Report Name Must Be Set';
        }
        
        
        // Create report
        if (!$result['errors']){
            
            $data = array(
                'start_type' => $startType,
                'start_level' => $level,
                'start_point' => $start,
                'filters' => $filters,
                'elements' => $elements
            );
            
            if ($reportID)
            {
                
                $report = $DB->get_record("block_bcgt_repsys_reports", array("id" => $reportID));
                if ($report)
                {
                    
                    $report->name = $name;
                    $report->updatedbyuserid = $USER->id;
                    $report->timeupdated = time();
                    $report->data = serialize($data);
                    $report->del = 0;
                    if ($DB->update_record("block_bcgt_repsys_reports", $report)){
                        $result['result'] = true;
                        $result['report'] = $report;
                    }
                    
                }
                
            }
            else
            {
                
                $report = new stdClass();
                $report->id = null;
                $report->name = $name;
                $report->createdbyuserid = $USER->id;
                $report->timecreated = time();
                $report->data = serialize($data);
                $report->runs = 0;
                $report->del = 0;

                $id = $DB->insert_record("block_bcgt_repsys_reports", $report);
                if ($id){

                    $report->id = $id;
                    $result['result'] = true;
                    $result['report'] = $report;

                } 
                
            }
            
                       
            
        }
                
        
        return $result;
        
        
    }
    
    public static function display_view_css(){
        
        global $CFG;
        
        $output = "";
        $output .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/js/tinytbl/jquery.ui.tinytbl.css' />";
        
        return $output;
        
    }
    
    public static function display_view_js($report){
        
        global $CFG;
        
        $output = <<<JS
                <script>
                
                var myResp;
                
                    function loadReporting(fromLevel, id, el){
                
                
                        // See if it's already been retrieved so we can just toggle
                        var lookFor = '.'+fromLevel+'_'+id;
                                
                        var r = $( lookFor );

                        if (r.length > 0){
                            $(lookFor).toggle();
                
                            if ( $(lookFor).hasClass('is_hidden') ){
                                $(lookFor).removeClass('is_hidden');
                            } else {
                                $(lookFor).addClass('is_hidden');
                            }
                            return;
                        }
                            
                
                
                
                        $('#loadinggif').show();
                    
                        var data = { action: 'load', reportID: {$report->id}, below: fromLevel, id: id };
                        
                        var applyClass = el.replace('ROW_', '');
                
                        $.post('{$CFG->wwwroot}/blocks/bcgt/ajax/reporting.php', data, function(response){
                            
                            myResp = $(response);
                            
                            $.each(myResp, function(k, row){
                                
                                $(row).addClass( applyClass );
                        
                            });
                                                
                            // Destroy tinytbl
                            try {
                                //$('#reporting_results_table').tinytbl('destroy');
                            } catch (e) {
                                // Do nothing
                            }
                        
                            // Append rows
                            $('#'+el).after(myResp);
                        
                            // Get the new height
                            var tableHeight = $('#reporting_results_table').css('height');
                            tableHeight = tableHeight.replace('px', '');
                        
                            tableHeight += 50;
                        
                            if (tableHeight > 750) tableHeight = 750;
                                                                        
                            // Create tinytbl
//                            $('#reporting_results_table').tinytbl({
//                                direction: 'ltr',
//                                thead:     true,
//                                tfoot:     false,
//                                cols:      1,
//                                width:     'auto',
//                                height:    tableHeight,
//                                renderer:  true
//                            });
                                                
                            $('.is_hidden').hide();
                        
                            $('#loadinggif').hide();
                        });
                    
                    }
                </script>
JS;
        
                        
        return $output;
        
    }
    
    public static function display_view_reports(){
        
        global $CFG, $DB;
        
        $reports = $DB->get_records("block_bcgt_repsys_reports", array("del" => 0), "timeupdated DESC, timecreated DESC");
        
        $output = "";
        
        $output .= "<table id='bcgt_reports'>";
        
            $output .= "<tr><th>Report Name</th><th>Created By</th><th>No. Runs</th><th>Last Run</th></tr>";
            
            if ($reports)
            {
                foreach($reports as $report)
                {
                    
                    $user = $DB->get_record("user", array("id" => $report->createdbyuserid));
                                        
                    $output .= "<tr>";
                        $output .= "<td><a href='my_dashboard.php?tab=reporting&action=view&id={$report->id}' target='_blank'>{$report->name}</a></td>";
                                                
                        $output .= "<td>".fullname($user)." ({$user->username}), ".date('D jS M Y, H:i', $report->timecreated)."</td>";
                        $output .= "<td>{$report->runs}</td>";
                        
                        if ($report->lastrunbyuserid){
                            $lastRunBy = $DB->get_record("user", array("id" => $report->lastrunbyuserid));
                            if ($lastRunBy){
                                $output .= "<td>".fullname($lastRunBy)." ({$lastRunBy->username}), ".date('D jS M Y, H:i', $report->timelastrun)."</td>";
                            } else {
                                $output .= "<td>?</td>";
                            }
                            
                        } else {
                            $output .= "<td>-</td>";
                        }
                    $output .= "</tr>";
                }
            }
            else
            {
                $output .= "<tr><td colspan='6'>No reports found...</td></tr>";
            }
            
        $output .= "</table>";
        
        
        
        return $output;
        
    }
    
    
    public static function create_csv_file($report){
        
        global $CFG;
        
        // Reporting dir
        $dir = $CFG->dataroot . '/bcgt/repsys/'; 
        if (!is_dir($dir)){
            if (!mkdir($dir, 0775)){
                print_error('Cannot create directory: ' . $dir);
                return false;
            }
        }
        
        
        // Dir for this report id
        $dir .= $report->id;
        if (!is_dir($dir)){
            if (!mkdir($dir, 0775)){
                print_error('Cannot create directory: ' . $dir);
                return false;
            }
        }
        
        $file = time() . '.csv';
        $fh = fopen($dir . '/' . $file, 'w+');
        
        if (!$fh){
            print_error('Cannot create file: ' . $dir . '/' . $file);
            return false;
        }
        
        fclose($fh);
        return $dir . '/' . $file;
        
        
    }


    public static function export_csv($report){
        
        global $CFG, $DB;
        
        $file = self::create_csv_file($report);
            
        if ($file){

            $fh = fopen($file, 'w+');

            // Write headers
            $elementArray = self::get_available_elements();
            $arr = array();
            $arr[] = 'name';    

            foreach($report->data['elements'] as $element)
            {
                $arr[] = $elementArray[$element];
            }

            fputcsv($fh, $arr);

            // Looking at quals
            if ($report->data['start_type'] == 'qual'){

                // ALl families
                if ($report->data['start_point'] == 'all'){

                    $families = get_qualification_type_families_used();
                    
                    foreach($families as $family)
                    {
                        
                        // Specific family
                        $results = self::get_results($report->data['start_type'], $report->data['start_level'], $report->data['start_point'], $report->data['filters'], $report->data['elements']);

                        $r = array();
                        $r['name'] = $family->family;

                        foreach($report->data['elements'] as $element){
                            if (!isset($results[$family->id][$element])){
                                $r[] = '-';
                            } else {
                                $r[] = $results[$family->id][$element];
                            }
                        }

                        fputcsv($fh, $r);


                        // Now find all quals in this family
                        $qualResults = self::get_results($report->data['start_type'], 'QUALS', $family->id, $report->data['filters'], $report->data['elements']);

                        foreach($qualResults as $qualID => $result){

                            $r = array();
                            $r['name'] = $result['name'];
                            foreach($report->data['elements'] as $element){
                                if (!isset($result[$element])){
                                    $r[] = '-';
                                } else {
                                    $r[] = $result[$element];
                                }
                            }

                            fputcsv($fh, $r);

                            // Now find all the students on this qual
                            $studResults = self::get_results($report->data['start_type'], 'STUDS', $qualID, $report->data['filters'], $report->data['elements']);

                            foreach($studResults as $result){

                                $r = array();
                                $r['name'] = $result['name'];
                                foreach($report->data['elements'] as $element){
                                    if (!isset($result[$element])){
                                        $r[] = '-';
                                    } else {
                                        $r[] = $result[$element];
                                    }
                                }

                                fputcsv($fh, $r);

                            }

                        }
                        
                    }

                } elseif (ctype_digit($report->data['start_point'])) {

                    // Specific family
                    $results = self::get_results($report->data['start_type'], $report->data['start_level'], $report->data['start_point'], $report->data['filters'], $report->data['elements']);

                    $family = $DB->get_record("block_bcgt_type_family", array("id" => $report->data['start_point']));

                    $r = array();
                    $r['name'] = $family->family;

                    foreach($report->data['elements'] as $element){
                        if (!isset($results[$family->id][$element])){
                            $r[] = '-';
                        } else {
                            $r[] = $results[$family->id][$element];
                        }
                    }

                    fputcsv($fh, $r);


                    // Now find all quals in this family
                    $qualResults = self::get_results($report->data['start_type'], 'QUALS', $family->id, $report->data['filters'], $report->data['elements']);

                    foreach($qualResults as $qualID => $result){

                        $r = array();
                        $r['name'] = $result['name'];
                        foreach($report->data['elements'] as $element){
                            if (!isset($result[$element])){
                                $r[] = '-';
                            } else {
                                $r[] = $result[$element];
                            }
                        }

                        fputcsv($fh, $r);

                        // Now find all the students on this qual
                        $studResults = self::get_results($report->data['start_type'], 'STUDS', $qualID, $report->data['filters'], $report->data['elements']);

                        foreach($studResults as $result){

                            $r = array();
                            $r['name'] = $result['name'];
                            foreach($report->data['elements'] as $element){
                                if (!isset($result[$element])){
                                    $r[] = '-';
                                } else {
                                    $r[] = $result[$element];
                                }
                            }

                            fputcsv($fh, $r);

                        }

                    }

                }

            } elseif ($report->data['start_type'] == 'unit') {
                
                // All families
                 if ($report->data['start_point'] == 'all'){
                     
                     $families = get_qualification_type_families_used();
                    
                     foreach($families as $family)
                     {
                        
                        // Specific family
                        $results = self::get_results($report->data['start_type'], $report->data['start_level'], $report->data['start_point'], $report->data['filters'], $report->data['elements']);

                        $r = array();
                        $r['name'] = $family->family;

                        foreach($report->data['elements'] as $element){
                            if (!isset($results[$family->id][$element])){
                                $r[] = '-';
                            } else {
                                $r[] = $results[$family->id][$element];
                            }
                        }

                        fputcsv($fh, $r);


                        // Now find all units in this family
                        $unitResults = self::get_results($report->data['start_type'], 'UNITS', $family->id, $report->data['filters'], $report->data['elements']);

                        foreach($unitResults as $unitID => $result){

                            $r = array();
                            $r['name'] = $result['name'];
                            foreach($report->data['elements'] as $element){
                                if (!isset($result[$element])){
                                    $r[] = '-';
                                } else {
                                    $r[] = $result[$element];
                                }
                            }

                            fputcsv($fh, $r);

                            // Now find all the students on this qual
                            $studResults = self::get_results($report->data['start_type'], 'STUDS', $unitID, $report->data['filters'], $report->data['elements']);

                            foreach($studResults as $result){

                                $r = array();
                                $r['name'] = $result['name'];
                                foreach($report->data['elements'] as $element){
                                    if (!isset($result[$element])){
                                        $r[] = '-';
                                    } else {
                                        $r[] = $result[$element];
                                    }
                                }

                                fputcsv($fh, $r);

                            }

                        }
                        
                    }
                     
                 } elseif (ctype_digit($report->data['start_point'])) {
                     
                    // Specific family
                    $results = self::get_results($report->data['start_type'], $report->data['start_level'], $report->data['start_point'], $report->data['filters'], $report->data['elements']);

                    $family = $DB->get_record("block_bcgt_type_family", array("id" => $report->data['start_point']));

                    $r = array();
                    $r['name'] = $family->family;

                    foreach($report->data['elements'] as $element){
                        if (!isset($results[$family->id][$element])){
                            $r[] = '-';
                        } else {
                            $r[] = $results[$family->id][$element];
                        }
                    }

                    fputcsv($fh, $r);


                    // Now find all units in this family
                    $unitResults = self::get_results($report->data['start_type'], 'UNITS', $family->id, $report->data['filters'], $report->data['elements']);

                    foreach($unitResults as $unitID => $result){

                        $r = array();
                        $r['name'] = $result['name'];
                        foreach($report->data['elements'] as $element){
                            if (!isset($result[$element])){
                                $r[] = '-';
                            } else {
                                $r[] = $result[$element];
                            }
                        }

                        fputcsv($fh, $r);

                        // Now find all the students on this qual
                        $studResults = self::get_results($report->data['start_type'], 'STUDS', $unitID, $report->data['filters'], $report->data['elements']);

                        foreach($studResults as $result){

                            $r = array();
                            $r['name'] = $result['name'];
                            foreach($report->data['elements'] as $element){
                                if (!isset($result[$element])){
                                    $r[] = '-';
                                } else {
                                    $r[] = $result[$element];
                                }
                            }

                            fputcsv($fh, $r);

                        }
                     
                      }
                
                    }
                    
            }


            fclose($fh);


        }            

        require_once $CFG->dirroot . '/lib/filelib.php';
        send_file($file, $report->name . '.csv');
        exit;
        
    }
    

    public static function display_view_report($id){
        
        global $CFG, $DB, $USER;
        
        $report = $DB->get_record("block_bcgt_repsys_reports", array("id" => $id));
        if (!$report){
            print_error('Invalid report ID');
            return false;
        }
                
        $report->data = unserialize($report->data);
        
        if ($report->data['start_type'] == 'qual' && ctype_digit($report->data['start_point'])){
            
            $family = $DB->get_record("block_bcgt_type_family", array("id" => $report->data['start_point']));
            if (!$family){
                print_error('Invalid Qual Family');
                return false;
            }
            
            $startType = 'Qualification Family';
            $startPoint = $family->family;
            
        }
        
        // All Qual families
        elseif ($report->data['start_type'] == 'qual'){
            
            $startType = 'Qualification Family';
            $startPoint = 'All';
            
        }
        
        // Specific unit family
        elseif ($report->data['start_type'] == 'unit' && ctype_digit($report->data['start_point'])){
            $family = $DB->get_record("block_bcgt_type_family", array("id" => $report->data['start_point']));
            if (!$family){
                print_error('Invalid Qual Family');
                return false;
            }
            
            $startType = 'Unit Family';
            $startPoint = $family->family;
        }
        
        // All unit families
        elseif ($report->data['start_type'] == 'unit'){
            $startType = 'Unit Family';
            $startPoint = 'All';
        }
        
        elseif ($report->data['start_type'] == 'cat' && ctype_digit($report->data['start_point'])){
            
            $family = $DB->get_record("course_categories", array("id" => $report->data['start_point']), "id");
            if (!$family){
                print_error('Invalid Course CAtegory');
                return false;
            }
            
            $startType = 'Course Category';
            $startPoint = self::get_course_category_name_with_parent($family->id);
            
        }
        
        elseif ($report->data['start_type'] == 'cat'){
            $startType = 'Course Category';
            $startPoint = 'All';
        }
        
        
        
        $filters = array();
        
        if (!empty($report->data['filters']['levels'])){
            
            foreach($report->data['filters']['levels'] as $level){
                $l = $DB->get_record("block_bcgt_level", array("id" => $level));
                $filters[] = $l->trackinglevel;
            }
                        
        }
        
        if (!empty($report->data['filters']['subtypes'])){
            
            foreach($report->data['filters']['subtypes'] as $subtype){
                $s = $DB->get_record("block_bcgt_subtype", array("id" => $subtype));
                $filters[] = $s->subtype;
            }
            
        }
        
        if (!empty($report->data['filters']['mode'])){
            
            foreach($report->data['filters']['mode'] as $mode){
                $filters[] = $mode;
            }
            
        }
        
        
        
        if (isset($_POST['run'])){
            
            $results = self::get_results($report->data['start_type'], $report->data['start_level'], $report->data['start_point'], $report->data['filters'], $report->data['elements']);
            $update = new stdClass();
            $update->id = $report->id;
            $update->lastrunbyuserid = $USER->id;
            $update->timelastrun = time();
            $update->runs = $report->runs + 1;
            $DB->update_record("block_bcgt_repsys_reports", $update);
            
            $report->lastrunbyuserid = $update->lastrunbyuserid;
            $report->timelastrun = $update->timelastrun;
            $report->runs = $update->runs;
            
        } 
        
            
        
        
        $user = $DB->get_record("user", array("id" => $report->createdbyuserid));
        
        $output = "";
        
        $output .= self::display_view_js($report);
        $output .= self::display_view_css();
        
        $output .= "<h1 class='c'>{$report->name}</h1>";
        
        $output .= "<form action='' method='post' class='c'>";
            $output .= "<input type='submit' name='run' value='Run Report' />";
        $output .= "</form>";
        
        $output .= "<p class='c'>";
            $output .= "<a href='{$CFG->wwwroot}/blocks/bcgt/forms/export.php?id={$report->id}&export=csv'>Save as CSV</a>";
            $output .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";    
            $output .= "Saved Results: ";
            $output .= "<select onchange='if(this.value != \"\"){ window.location = \"{$CFG->wwwroot}/blocks/bcgt/forms/export.php?id={$report->id}&csv=\"+this.value }'>";
                $output .= "<option value=''></option>";
                
                $files = array();
                if (is_dir($CFG->dataroot . '/bcgt/repsys/'.$report->id.'/'))
                {
                    $files = scandir( $CFG->dataroot . '/bcgt/repsys/'.$report->id.'/' , 1 );
                    $files = array_filter($files, function($var){
                        return ($var != '.' && $var != '..');
                    });
                }    
                                
                foreach($files as $file){
                    $unix = str_replace(".csv", "", $file);
                    $output .= "<option value='{$file}'>".date('d/m/Y H:i', $unix)."</option>";
                }
                
                
            $output .= "</select>";
        $output .= "</p>";
        
        
        $output .= "<br><br>";
        
        
        $output .= "<div>";
            
            $output .= "<table>";
            
                $output .= "<tr>";
                    $output .= "<td>Report Name:</td>";
                    $output .= "<td>{$report->name}</td>";
                $output .= "</tr>";
                
                $output .= "<tr>";
                    $output .= "<td>Starting Point:</td>";
                    $output .= "<td>{$startType} // {$startPoint}</td>";
                $output .= "</tr>";
                
                $output .= "<tr>";
                    $output .= "<td>Filters:</td>";
                    $output .= "<td>". ( (!empty($filters)) ? implode(", ", $filters) : '-' ) ."</td>";
                $output .= "</tr>";
                
                $output .= "<tr>";
                    $output .= "<td>Elements:</td>";
                    $output .= "<td>".implode(', ', $report->data['elements'])."</td>";
                $output .= "</tr>";
                
                $output .= "<tr>";
                    $output .= "<td>Created:</td>";
                    $output .= "<td>".fullname($user)." ({$user->username})<br>".date('D jS M Y, H:i', $report->timecreated)."</td>";
                $output .= "</tr>";
                
                $output .= "<tr>";
                    $output .= "<td>Updated:</td>";
                    if ($report->timeupdated > 0){
                        $updatedUser = $DB->get_record("user", array("id" => $report->updatedbyuserid));
                        $output .= "<td>".fullname($updatedUser)." ({$updatedUser->username})<br>".date('D jS M Y, H:i', $report->timeupdated)."</td>";
                    } else {
                        $output .= "<td>-</td>";
                    }
                $output .= "</tr>";
                
                $output .= "<tr>";
                    $output .= "<td>Last Run:</td>";
                    if ($report->timelastrun > 0){
                        $lastUser = $DB->get_record("user", array("id" => $report->lastrunbyuserid));
                        $output .= "<td>".fullname($lastUser)." ({$lastUser->username})<br>".date('D jS M Y, H:i', $report->timelastrun)." ({$report->runs})</td>";
                    } else {
                        $output .= "<td>-</td>";
                    }
                $output .= "</tr>";
                
                $output .= "<tr>";
                    $output .= "<td><form action='my_dashboard.php?tab=reporting&action=create' method='post'><input type='hidden' name='report_id' value='{$report->id}' /><input type='submit' name='edit' value='Edit' /></form></td>";
                    $output .= "<td></td>";
                $output .= "</tr>";
                
            $output .= "</table>";
        
        $output .= "</div>";
        
        
        
        $output .= "<p class='c' id='loadinggif'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/ajax-loader.gif' alt='loading...' /></p>";
        
        
        $output .= "<div id='table-wrapper'>";

            $output .= "<table id='reporting_results_table' cellpadding='5' cellspacing='0'>";
            
                $output .= "<thead>";
                
                $output .= "<tr>";
                    $output .= "<th>Name</th>";
                    
                    $elementArray = self::get_available_elements();
                    
                    foreach($report->data['elements'] as $element)
                    {
                        $output .= "<th>".$elementArray[$element]."</th>";
                    }
                    
                $output .= "</tr>";
                
                $output .= "</thead>";
                
                $output .= "<tbody>";
                
                if (isset($results) && $results)
                {
                    
                    if ($report->data['start_type'] == 'qual')
                    {
                        
                        if ($report->data['start_point'] == 'all')
                        {
                            
                            // Get all families
                            $families = get_qualification_type_families_used();
                            foreach($families as $family)
                            {
                                $output .= self::get_results_row('FAMILY', $family->id, $startPoint, $report->data['elements'], $results[$family->id]);
                            }
                            
                        }
                        else
                        {
                            $output .= self::get_results_row('FAMILY', $report->data['start_point'], $startPoint, $report->data['elements'], $results[$report->data['start_point']]);
                        }
                        
                    } elseif ($report->data['start_type'] == 'unit') {
                        
                        if ($report->data['start_point'] == 'all')
                        {
                            
                            // Get all families
                            $families = get_qualification_type_families_used();
                            foreach($families as $family)
                            {
                                $output .= self::get_results_row('FAMILY', $family->id, $startPoint, $report->data['elements'], $results[$family->id]);
                            }
                            
                        }
                        else
                        {
                            $output .= self::get_results_row('FAMILY', $report->data['start_point'], $startPoint, $report->data['elements'], $results[$report->data['start_point']]);
                        }
                        
                    } elseif ($report->data['start_type'] == 'cat') {
                    
                        if ($report->data['start_point'] == 'all')
                        {
                            
                            // Get all families
                            $categories = get_config('bcgt', 'reportingcats');
                            $catArray = explode(",", $categories);
                            foreach($catArray as $catID)
                            {
                                $output .= self::get_results_row('CATEGORY', $catID, $startPoint, $report->data['elements'], $results[$catID]);
                            }
                            
                        }
                        else
                        {
                            $output .= self::get_results_row('CATEGORY', $report->data['start_point'], $startPoint, $report->data['elements'], $results[$report->data['start_point']]);
                        }
                        
                    } 

                }
                
                $output .= "</tbody>";
                            
            $output .= "</table>";
        
        $output .= "</div>";
        
        
              
        
        return $output;
        
    }
    
    public static function get_results_row( $level, $startPointID, $startPointName, $elements, $results ){
        
        global $CFG;
        
        $output = "";
        
        $name = (!empty($results['name'])) ? $results['name'] : $startPointName;
        
        $class = "{$level}";
        
        $output .= "<tr class='{$class}' id='ROW_{$class}_{$startPointID}'>";
        
            if ($level == 'STUD'){
                $output .= "<td><a href='{$CFG->wwwroot}/blocks/bcgt/grids/student_grid.php?sID={$startPointID}&qID={$results['qualID']}&g=s' target='_blank'>{$name}</a></td>";
            } else {
                $output .= "<td><a href='#' onclick='loadReporting(\"{$level}\", \"{$startPointID}\", \"ROW_{$class}_{$startPointID}\");return false;'>{$name}</a></td>";
            }
            
            foreach($elements as $element)
            {
                
                $output .= "<td>". ( (isset($results[$element])) ? $results[$element] : '-' ) ."</td>";
                
            }
        
        $output .= "</tr>";
        
        return $output;
        
    }
    
    public static function get_results($startType, $startLevel, $startPoint, $filters, $elements){
        
        global $CFG, $DB;
        
                
                                
        $results = array();
                        
        switch($startType)
        {
            
            
            // Top level - just the qual families being returned as rows, so we want to calculate everything below
            case 'qual':
                
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELCRITERIA;
                
                switch($startLevel)
                {
                
                    // Showing the family row
                    case 'FAMILY':
                        
                        $families = false;

                        if ($startPoint == 'all'){

                            $families = get_qualification_type_families_used();

                        } else {

                            $fam = $DB->get_record("block_bcgt_type_family", array("id" => $startPoint));
                            if ($fam){
                                $families = array( $fam );
                            }

                        }
                        
                                                

                        if (!$families) return false;

                        // For each family, find all qualifications in the system of that family type
                        foreach($families as $family)
                        {
                            
                            $qualifications = array();
                            $students = array();

                            // FIrst find all block_bcgt_type records with this family
                            $types = $DB->get_records("block_bcgt_type", array("bcgttypefamilyid" => $family->id));
                            if ($types)
                            {

                                // For each type, find the block_bcgt_target_qual records with that type
                                foreach($types as $type)
                                {

                                    $where = "bcgttypeid = ?";
                                    $params = array($type->id);

                                    // Filter by level
                                    if (!empty($filters['levels'])){

                                        $where .= " AND bcgtlevelid IN ( ";

                                        $cnt = count($filters['levels']);
                                        $i = 0;

                                        foreach($filters['levels'] as $filterLevel)
                                        {
                                            $i++;
                                            $where .= "?";
                                            if ($i < $cnt){
                                                $where .= ",";
                                            }
                                            $params[] = $filterLevel;

                                        }

                                        $where .= " ) ";

                                    }

                                    // Filter by sub type
                                    if (!empty($filters['subtypes'])){

                                        $where .= " AND bcgtsubtypeid IN ( ";

                                        $cnt = count($filters['subtypes']);
                                        $i = 0;

                                        foreach($filters['subtypes'] as $filterSubType)
                                        {
                                            $i++;
                                            $where .= "?";
                                            if ($i < $cnt){
                                                $where .= ",";
                                            }
                                            $params[] = $filterSubType;

                                        }

                                        $where .= " ) ";

                                    }

                                    $targetQuals = $DB->get_records_select("block_bcgt_target_qual", $where, $params);
                                    if ($targetQuals)
                                    {

                                        // For each of these, find any qualifications of this target qual type
                                        foreach($targetQuals as $targetQual)
                                        {

                                            $quals = $DB->get_records("block_bcgt_qualification", array("bcgttargetqualid" => $targetQual->id));
                                            if ($quals)
                                            {

                                                foreach($quals as $qual)
                                                {

                                                    $qualObj = Qualification::get_qualification_class_id($qual->id, $loadParams);
                                                    $qualifications[$qual->id] = $qualObj;

                                                }

                                            }

                                        }

                                    }

                                }
                                
                                                                
                                
                                require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeQualification.class.php';
                                
                                                                
                                // Now bespoke ones as well, if the start point was "all" or "bespoke"
                                if ( $family->id == BespokeQualification::FAMILYID){
                                    
                                    $sql = "SELECT q.*
                                            FROM {block_bcgt_bespoke_qual} b
                                            INNER JOIN {block_bcgt_qualification} q ON q.id = b.bcgtqualid";
                                    
                                    $params = array();
                                    
                                    
                                    // Filter by level
                                    if (!empty($filters['levels'])){

                                        $sql .= " AND b.level IN ( ";

                                        foreach($filters['levels'] as $filterLevel)
                                        {
                                            
                                            // the filterLevel is the id of a block_bcgt_level record
                                            // so we need to get the actual level
                                            $levelRecord = $DB->get_record("block_bcgt_level", array("id" => $filterLevel));
                                            
                                            if ($levelRecord)
                                            {
                                                
                                                preg_match_all("/[0-9]+.?/", $levelRecord->trackinglevel, $matches);
                                                $matches = $matches[0];
                                                                                                
                                                if ($matches)
                                                {
                                                    
                                                    foreach($matches as $lvl)
                                                    {
                                                         $sql .= "?,";
                                                         $params[] = $lvl;
                                                    }
                                                    
                                                    // Remove the last comma
                                                    $sql = substr($sql, 0, -1);
                                                    
                                                }
                                               
                                            }
                                            
                                        }

                                        $sql .= " ) ";

                                    }
                                    
                                    
                                    // Filter by sub type
                                    if (!empty($filters['subtypes'])){

                                        $sql .= " AND subtype IN ( ";

                                        foreach($filters['subtypes'] as $filterSubType)
                                        {
                                            
                                            $subTypeRecord = $DB->get_record("block_bcgt_subtype", array("id" => $filterSubType));
                                            
                                            if ($subTypeRecord)
                                            {
                                                
                                                 $sql .= "?,";
                                                 $params[] = $subTypeRecord->subtype;                                                    
                                               
                                            }
                                            
                                        }
                                        
                                        // Remove the last comma
                                        $sql = substr($sql, 0, -1);

                                        $sql .= " ) ";

                                    }
                                    
                                                                        
                                    $records = $DB->get_records_sql($sql, $params);
                                    
                                    if ($records){
                                        
                                        foreach($records as $qual){
                                            $qualObj = Qualification::get_qualification_class_id($qual->id, $loadParams);
                                            $qualifications[$qual->id] = $qualObj;
                                        }
                                        
                                    }
                                    
                                                                       
                                    
                                }


                            }
                            
                            


                            // If any quals were returned, order them. First by level, then by subtype
                            if ($qualifications){

                                $qualifications = self::sort_quals($qualifications);
                                
                                self::$qualArray = $qualifications;

                                foreach($qualifications as $qualification){

                                    $qualStudents = $DB->get_records_sql("SELECT u.id
                                                                          FROM {user} u
                                                                          INNER JOIN {block_bcgt_user_qual} uq ON uq.userid = u.id
                                                                          WHERE uq.bcgtqualificationid = ? AND uq.roleid = ? AND u.deleted = 0", array($qualification->get_id(), 5));
                                    
                                    if ($qualStudents){
                                        foreach($qualStudents as $student){
                                            $students[$student->id] = $student->id;
                                        }
                                    }
                                    
                                    $qualification->listOfStudents = $qualStudents;

                                }

                            }
                            

                            // Get the results of elements which are only applicable for the qual itself, not the students level
                            $results[$family->id] = self::get_element_results($startType, 'FAMILY', $family->id, $elements, $qualifications, $students);
                            

                        }
                        
                    break;
                    
                    
                    // List the qualifications on a family
                    case 'QUALS':
                        
                        $familyID = $startPoint;
                        
                        $students = array();
                        $qualifications = array();

                        // FIrst find all block_bcgt_type records with this family
                        $types = $DB->get_records("block_bcgt_type", array("bcgttypefamilyid" => $familyID));
                                                                        
                        if ($types)
                        {

                            // For each type, find the block_bcgt_target_qual records with that type
                            foreach($types as $type)
                            {

                                $where = "bcgttypeid = ?";
                                $params = array($type->id);

                                // Filter by level
                                if (!empty($filters['levels'])){

                                    $where .= " AND bcgtlevelid IN ( ";

                                    $cnt = count($filters['levels']);
                                    $i = 0;

                                    foreach($filters['levels'] as $filterLevel)
                                    {
                                        $i++;
                                        $where .= "?";
                                        if ($i < $cnt){
                                            $where .= ",";
                                        }
                                        $params[] = $filterLevel;

                                    }

                                    $where .= " ) ";

                                }

                                // Filter by sub type
                                if (!empty($filters['subtypes'])){

                                    $where .= " AND bcgtsubtypeid IN ( ";

                                    $cnt = count($filters['subtypes']);
                                    $i = 0;

                                    foreach($filters['subtypes'] as $filterSubType)
                                    {
                                        $i++;
                                        $where .= "?";
                                        if ($i < $cnt){
                                            $where .= ",";
                                        }
                                        $params[] = $filterSubType;

                                    }

                                    $where .= " ) ";

                                }

                                $targetQuals = $DB->get_records_select("block_bcgt_target_qual", $where, $params);
                                                                
                                if ($targetQuals)
                                {

                                    // For each of these, find any qualifications of this target qual type
                                    foreach($targetQuals as $targetQual)
                                    {

                                        $quals = $DB->get_records("block_bcgt_qualification", array("bcgttargetqualid" => $targetQual->id));
                                        if ($quals)
                                        {

                                            foreach($quals as $qual)
                                            {

                                                $qualObj = Qualification::get_qualification_class_id($qual->id, $loadParams);
                                                $qualifications[$qual->id] = $qualObj;

                                            }

                                        }

                                    }

                                }

                            }

                            
                            
                            require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeQualification.class.php';
                                
                            // Now bespoke ones as well, if the start point was "all" or "bespoke"
                            if ($familyID == BespokeQualification::FAMILYID){

                                $sql = "SELECT q.*
                                        FROM {block_bcgt_bespoke_qual} b
                                        INNER JOIN {block_bcgt_qualification} q ON q.id = b.bcgtqualid";

                                $params = array();


                                // Filter by level
                                if (!empty($filters['levels'])){

                                    $sql .= " AND b.level IN ( ";

                                    foreach($filters['levels'] as $filterLevel)
                                    {

                                        // the filterLevel is the id of a block_bcgt_level record
                                        // so we need to get the actual level
                                        $levelRecord = $DB->get_record("block_bcgt_level", array("id" => $filterLevel));

                                        if ($levelRecord)
                                        {

                                            preg_match_all("/[0-9]+.?/", $levelRecord->trackinglevel, $matches);
                                            $matches = $matches[0];

                                            if ($matches)
                                            {

                                                foreach($matches as $lvl)
                                                {
                                                     $sql .= "?,";
                                                     $params[] = $lvl;
                                                }

                                                // Remove the last comma
                                                $sql = substr($sql, 0, -1);

                                            }

                                        }

                                    }

                                    $sql .= " ) ";

                                }


                                // Filter by sub type
                                if (!empty($filters['subtypes'])){

                                    $sql .= " AND subtype IN ( ";

                                    foreach($filters['subtypes'] as $filterSubType)
                                    {

                                        $subTypeRecord = $DB->get_record("block_bcgt_subtype", array("id" => $filterSubType));

                                        if ($subTypeRecord)
                                        {

                                             $sql .= "?,";
                                             $params[] = $subTypeRecord->subtype;                                                    

                                        }

                                    }

                                    // Remove the last comma
                                    $sql = substr($sql, 0, -1);

                                    $sql .= " ) ";

                                }


                                $records = $DB->get_records_sql($sql, $params);

                                if ($records){

                                    foreach($records as $qual){
                                        $qualObj = Qualification::get_qualification_class_id($qual->id, $loadParams);
                                        $qualifications[$qual->id] = $qualObj;
                                    }

                                }



                            }


                        }


                        // If any quals were returned, order them. First by level, then by subtype
                        if ($qualifications){

                            $qualifications = self::sort_quals($qualifications);
                            
                            self::$qualArray = $qualifications;

                            foreach($qualifications as $qualification){

                                $qualStudents = $DB->get_records_sql("SELECT u.id
                                                                          FROM {user} u
                                                                          INNER JOIN {block_bcgt_user_qual} uq ON uq.userid = u.id
                                                                          WHERE uq.bcgtqualificationid = ? AND uq.roleid = ? AND u.deleted = 0", array($qualification->get_id(), 5));
                                
                                if ($qualStudents){
                                    foreach($qualStudents as $student){
                                        $students[$student->id] = $student->id;
                                    }
                                }
                                
                                $qualification->listOfStudents = $qualStudents;
                                
                                $results[$qualification->get_id()] = self::get_element_results($startType, 'QUALS', $qualification->get_id(), $elements, array($qualification), $qualStudents);

                            }

                        }


                        
                        
                    break;
                    
                    
                    
                    
                    case 'STUDS':
                        
                        $qualID = $startPoint;
                        
                        
                        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);

                        // If any quals were returned, order them. First by level, then by subtype
                        if ($qualification){

                                $qualStudents = $DB->get_records_sql("SELECT u.id
                                                                      FROM {user} u
                                                                      INNER JOIN {block_bcgt_user_qual} uq ON uq.userid = u.id
                                                                      WHERE uq.bcgtqualificationid = ? AND uq.roleid = ? AND u.deleted = 0
                                                                      ORDER BY u.lastname ASC, u.firstname ASC, u.username", array($qualification->get_id(), 5));
                                
                                if ($qualStudents){
                                    foreach($qualStudents as $student){
                                        
                                        $results[$student->id] = self::get_element_results($startType, 'STUDS', $student->id, $elements, $qualification, array($student));
                                        
                                    }
                                }
                                

                        }

                        
                        
                    break;
                                        
                    
                
                }
                                                
            break;
            
            
            
            // Viewing by units, not quals
            case 'unit':
                
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELCRITERIA;
                                
                switch($startLevel)
                {
                
                    // Showing the family row
                    case 'FAMILY':
                        
                        $families = false;

                        if ($startPoint == 'all'){

                            $families = get_qualification_type_families_used();

                        } else {

                            $fam = $DB->get_record("block_bcgt_type_family", array("id" => $startPoint));
                            if ($fam){
                                $families = array( $fam );
                            }

                        }
                        
                                                                        

                        if (!$families) return false;

                        // For each family, find all qualifications in the system of that family type
                        foreach($families as $family)
                        {
                            
                            $units = array();
                            $students = array();

                            // FIrst find all block_bcgt_type records with this family
                            $types = $DB->get_records("block_bcgt_type", array("bcgttypefamilyid" => $family->id));
                            if ($types)
                            {
                                
                                foreach($types as $type)
                                {
                                 
                                    $params = array();
                                    $where = "";
                                    $where .= "bcgttypeid = ?";
                                    $params[] = $type->id;
                                    
                                    // Level filtenig
                                    if (!empty($filters['levels'])){

                                        $where .= " AND bcgtlevelid IN ( ";

                                        $cnt = count($filters['levels']);
                                        $i = 0;

                                        foreach($filters['levels'] as $filterLevel)
                                        {
                                            $i++;
                                            $where .= "?";
                                            if ($i < $cnt){
                                                $where .= ",";
                                            }
                                            $params[] = $filterLevel;

                                        }

                                        $where .= " ) ";

                                    }
                                    
                                    
                                    // FINd all units of this type
                                    $findUnits = $DB->get_records_select("block_bcgt_unit", $where, $params);
                                    if ($findUnits)
                                    {
                                        foreach($findUnits as $findUnit)
                                        {
                                            $obj = Unit::get_unit_class_id($findUnit->id, $loadParams);
                                            $units[$findUnit->id] = $obj;
                                        }
                                    }
                                    
                                    
                                }
                                
                                
                                
                                
                                // Now bespoke as well
                                require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeQualification.class.php';
                                
                                                                
                                // Now bespoke ones as well, if the start point was "all" or "bespoke"
                                if ( $family->id == BespokeQualification::FAMILYID){
                                    
                                    $sql = "SELECT q.*
                                            FROM {block_bcgt_bespoke_unit} b
                                            INNER JOIN {block_bcgt_unit} q ON q.id = b.bcgtunitid";
                                    
                                    $params = array();
                                    
                                    
                                    // Filter by level
                                    if (!empty($filters['levels'])){

                                        $sql .= " AND b.level IN ( ";

                                        foreach($filters['levels'] as $filterLevel)
                                        {
                                            
                                            // the filterLevel is the id of a block_bcgt_level record
                                            // so we need to get the actual level
                                            $levelRecord = $DB->get_record("block_bcgt_level", array("id" => $filterLevel));
                                            
                                            if ($levelRecord)
                                            {
                                                
                                                preg_match_all("/[0-9]+.?/", $levelRecord->trackinglevel, $matches);
                                                $matches = $matches[0];
                                                                                                
                                                if ($matches)
                                                {
                                                    
                                                    foreach($matches as $lvl)
                                                    {
                                                         $sql .= "?,";
                                                         $params[] = $lvl;
                                                    }
                                                    
                                                    // Remove the last comma
                                                    $sql = substr($sql, 0, -1);
                                                    
                                                }
                                               
                                            }
                                            
                                        }

                                        $sql .= " ) ";

                                    }
                                    
                                                                                                                                                
                                    $records = $DB->get_records_sql($sql, $params);
                                    
                                    if ($records){
                                        
                                        foreach($records as $unit){
                                            $obj = Unit::get_unit_class_id($unit->id, $loadParams);
                                            $units[$unit->id] = $obj;
                                        }
                                        
                                    }
                                    
                                                                       
                                    
                                }
                                
                                
                                
                            }
                            
                            
                                                      
                            
                            
                            
                            // Now looop through those units and find the students on them
                            if ($units){

                                $units = self::sort_units($units);
                                
                                foreach($units as $unit){

                                    $unitStudents = $DB->get_records_sql("SELECT DISTINCT u.id
                                                                          FROM {user} u
                                                                          INNER JOIN {block_bcgt_user_unit} uq ON uq.userid = u.id
                                                                          WHERE uq.bcgtunitid = ? AND u.deleted = 0", array($unit->get_id()));

                                    if ($unitStudents){
                                        foreach($unitStudents as $student){
                                            $students[$student->id] = $student->id;
                                        }
                                    }

                                    $unit->listOfStudents = $unitStudents;

                                }

                            }
                                                        
                            $results[$family->id] = self::get_element_results($startType, 'FAMILY', $family->id, $elements, $units, $students);

                        }
                                                
                                                
                    break;
                    
                    
                    case 'UNITS':
                        
                        $familyID = $startPoint;
                        
                        // FIrst find all block_bcgt_type records with this family
                        $types = $DB->get_records("block_bcgt_type", array("bcgttypefamilyid" => $familyID));
                        if ($types)
                        {

                            foreach($types as $type)
                            {

                                $params = array();
                                $where = "";
                                $where .= "bcgttypeid = ?";
                                $params[] = $type->id;

                                // Level filtenig
                                if (!empty($filters['levels'])){

                                    $where .= " AND bcgtlevelid IN ( ";

                                    $cnt = count($filters['levels']);
                                    $i = 0;

                                    foreach($filters['levels'] as $filterLevel)
                                    {
                                        $i++;
                                        $where .= "?";
                                        if ($i < $cnt){
                                            $where .= ",";
                                        }
                                        $params[] = $filterLevel;

                                    }

                                    $where .= " ) ";

                                }


                                // FINd all units of this type
                                $findUnits = $DB->get_records_select("block_bcgt_unit", $where, $params);
                                if ($findUnits)
                                {
                                    foreach($findUnits as $findUnit)
                                    {
                                        $obj = Unit::get_unit_class_id($findUnit->id, $loadParams);
                                        $units[$findUnit->id] = $obj;
                                    }
                                }


                            }




                            // Now bespoke as well
                            require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtbespoke/classes/BespokeQualification.class.php';


                            // Now bespoke ones as well, if the start point was "all" or "bespoke"
                            if ( $familyID == BespokeQualification::FAMILYID){

                                $sql = "SELECT q.*
                                        FROM {block_bcgt_bespoke_unit} b
                                        INNER JOIN {block_bcgt_unit} q ON q.id = b.bcgtunitid";

                                $params = array();


                                // Filter by level
                                if (!empty($filters['levels'])){

                                    $sql .= " AND b.level IN ( ";

                                    foreach($filters['levels'] as $filterLevel)
                                    {

                                        // the filterLevel is the id of a block_bcgt_level record
                                        // so we need to get the actual level
                                        $levelRecord = $DB->get_record("block_bcgt_level", array("id" => $filterLevel));

                                        if ($levelRecord)
                                        {

                                            preg_match_all("/[0-9]+.?/", $levelRecord->trackinglevel, $matches);
                                            $matches = $matches[0];

                                            if ($matches)
                                            {

                                                foreach($matches as $lvl)
                                                {
                                                     $sql .= "?,";
                                                     $params[] = $lvl;
                                                }

                                                // Remove the last comma
                                                $sql = substr($sql, 0, -1);

                                            }

                                        }

                                    }

                                    $sql .= " ) ";

                                }


                                $records = $DB->get_records_sql($sql, $params);

                                if ($records){

                                    foreach($records as $unit){
                                        $obj = Unit::get_unit_class_id($unit->id, $loadParams);
                                        $units[$unit->id] = $obj;
                                    }

                                }

                            }

                        }
                        
                        // Now looop through those units and find the students on them
                        if ($units){

                            $units = self::sort_units($units);
                            
                            foreach($units as $unit){

                                $unitStudents = $DB->get_records_sql("SELECT DISTINCT u.id
                                                                      FROM {user} u
                                                                      INNER JOIN {block_bcgt_user_unit} uq ON uq.userid = u.id
                                                                      WHERE uq.bcgtunitid = ? AND u.deleted = 0", array($unit->get_id()));

                                if ($unitStudents){
                                    foreach($unitStudents as $student){
                                        $students[$student->id] = $student->id;
                                    }
                                }

                                $unit->listOfStudents = $unitStudents;
                                $results[$unit->get_id()] = self::get_element_results($startType, 'UNITS', $unit->get_id(), $elements, array($unit), $unitStudents);

                            }

                        }

                        
                    break;
                    
                    
                    
                    case 'STUDS':
                        
                        $unitID = $startPoint;
                        
                        $unit = Unit::get_unit_class_id($unitID, $loadParams);

                        // If any quals were returned, order them. First by level, then by subtype
                        if ($unit){

                               $unitStudents = $DB->get_records_sql("SELECT DISTINCT u.id
                                                                      FROM {user} u
                                                                      INNER JOIN {block_bcgt_user_unit} uq ON uq.userid = u.id
                                                                      WHERE uq.bcgtunitid = ? AND u.deleted = 0
                                                                      ORDER BY u.lastname, u.firstname, u.username", array($unit->get_id()));

                                 if ($unitStudents){
                                    foreach($unitStudents as $student){
                                        
                                        $results[$student->id] = self::get_element_results($startType, 'STUDS', $student->id, $elements, $unit, array($student));
                                        
                                    }
                                }

                        }

                        
                        
                    break;
                    
                    
                    
                    
                }
                                    
                
            break;
            
            case 'cat':

                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELCRITERIA;
                
                switch($startLevel)
                {
                    
                    case 'CATEGORY':
                        
                        $cats = false;

                        if ($startPoint == 'all'){

                            $catIDs = get_config('bcgt', 'reportingcats');
                            if ($catIDs)
                            {
                                $catExplode = explode(",", $catIDs);
                                $cats = array();
                                foreach($catExplode as $catID)
                                {
                                    $cat = $DB->get_record("course_categories", array("id" => $catID));
                                    $cats[] = $cat;
                                }
                            }

                        } else {

                            $cat = $DB->get_record("course_categories", array("id" => $startPoint));
                            if ($cat){
                                $cats = array( $cat );
                            }

                        }
                        
                        if (!$cats) return false;
                        
                        // Find all courses in these cats
                        $courseArray = array();
                        
                        foreach($cats as $cat)
                        {
                            
                            $courses = $DB->get_records("course", array("category" => $cat->id));
                            if ($courses)
                            {
                                foreach($courses as $course)
                                {
                                    $courseArray[$course->id] = $course->id;
                                }
                            }
                            
                            
                            // Find all quals linked to these courses
                            $qArray = array();

                            if ($courseArray)
                            {
                                foreach($courseArray as $courseID)
                                {

                                    $quals = $DB->get_records("block_bcgt_course_qual", array("courseid" => $courseID));
                                    if ($quals)
                                    {
                                        foreach($quals as $qual)
                                        {
                                            $qualObj = Qualification::get_qualification_class_id($qual->bcgtqualificationid, $loadParams);
                                            $qArray[$qual->bcgtqualificationid] = $qualObj;
                                        }
                                    }

                                }
                            }

                            // Now filter them, if we have any filters
                            $qualArray = array();

                            if ($qArray)
                            {
                                foreach($qArray as $qual)
                                {

                                    $exclude = false;

                                    // Bespoke
                                    if (isset($qual->bespoke) && $qual->bespoke == true)
                                    {

                                        // [todo]

                                    }
                                    else
                                    {

                                        // Normal qual
                                        if (!empty($filters['levels'])){

                                            // Check level
                                            $levelOkay = false;

                                            foreach($filters['levels'] as $filterLevel){

                                                if ($qual->get_level()->get_id() == $filterLevel){
                                                    $levelOkay = true;
                                                }

                                            }

                                            // If level wasn't in our filter list, exclude this qual from results
                                            if (!$levelOkay){
                                                $exclude = true;
                                            }

                                        }


                                        // Check subtype
                                        if (!empty($filters['subtypes'])){

                                            $subTypeOkay = false;

                                            foreach($filters['subtypes'] as $filterSubType)
                                            {

                                                if ($qual->get_subtype()->get_id() == $filterSubType){
                                                    $subTypeOkay = true;
                                                }

                                            }

                                            // If level wasn't in our filter list, exclude this qual from results
                                            if (!$subTypeOkay){
                                                $exclude = true;
                                            }


                                        }

                                    }


                                    // If not exlcuded, add to array
                                    if (!$exclude){
                                        $qualArray[$qual->get_id()] = $qual;
                                    }


                                }

                            }       



                            // If any quals were returned, order them. First by level, then by subtype
                            if ($qualArray){

                                $qualArray = self::sort_quals($qualArray);

                                self::$qualArray = $qualArray;

                                $students = array();
                                
                                foreach($qualArray as $qualification){

                                    $sql = "SELECT u.id
                                            FROM {user} u
                                            INNER JOIN {block_bcgt_user_qual} uq ON uq.userid = u.id
                                            WHERE uq.bcgtqualificationid = ? AND uq.roleid = ? AND u.deleted = 0";
                                    
                                    $params = array($qualification->get_id(), 5);
                                    
                                    if (!empty($filters['mode']))
                                    {
                                        
                                        $sql .= " AND ( ";
                                        
                                        foreach($filters['mode'] as $mode)
                                        {
                                            $sql .= " u.mode = ? OR ";
                                            $params[] = $mode;
                                        }
                                        
                                        // Remove trailing "OR"
                                        $sql = substr($sql, 0, -3);
                                        $sql .= " ) ";
                                        
                                    }
                                                                        
                                    $qualStudents = $DB->get_records_sql($sql, $params);
                                    
                                    if ($qualStudents){
                                        foreach($qualStudents as $student){
                                            $students[$student->id] = $student->id;
                                        }
                                    }

                                    $qualification->listOfStudents = $qualStudents;

                                }

                            }


                            // Get the results of elements which are only applicable for the qual itself, not the students level
                            $results[$cat->id] = self::get_element_results($startType, 'CATEGORY', $cat->id, $elements, $qualArray, $students);                            
                            
                        }
                                                
                        
                    break;
                    
                    
                    case 'QUALS':
                        
                        $catID = $startPoint;
                        $cat = $DB->get_record("course_categories", array("id" => $catID));

                            
                        // Find all courses in these cats
                        $courseArray = array();
                                                    
                        $courses = $DB->get_records("course", array("category" => $cat->id));
                        if ($courses)
                        {
                            foreach($courses as $course)
                            {
                                $courseArray[$course->id] = $course->id;
                            }
                        }


                        // Find all quals linked to these courses
                        $qArray = array();

                        if ($courseArray)
                        {
                            foreach($courseArray as $courseID)
                            {

                                $quals = $DB->get_records("block_bcgt_course_qual", array("courseid" => $courseID));
                                if ($quals)
                                {
                                    foreach($quals as $qual)
                                    {
                                        $qualObj = Qualification::get_qualification_class_id($qual->bcgtqualificationid, $loadParams);
                                        $qArray[$qual->bcgtqualificationid] = $qualObj;
                                    }
                                }

                            }
                        }

                        // Now filter them, if we have any filters
                        $qualArray = array();

                        if ($qArray)
                        {
                            foreach($qArray as $qual)
                            {

                                $exclude = false;

                                // Bespoke
                                if (isset($qual->bespoke) && $qual->bespoke == true)
                                {

                                    // [todo]

                                }
                                else
                                {

                                    // Normal qual
                                    if (!empty($filters['levels'])){

                                        // Check level
                                        $levelOkay = false;

                                        foreach($filters['levels'] as $filterLevel){

                                            if ($qual->get_level()->get_id() == $filterLevel){
                                                $levelOkay = true;
                                            }

                                        }

                                        // If level wasn't in our filter list, exclude this qual from results
                                        if (!$levelOkay){
                                            $exclude = true;
                                        }

                                    }


                                    // Check subtype
                                    if (!empty($filters['subtypes'])){

                                        $subTypeOkay = false;

                                        foreach($filters['subtypes'] as $filterSubType)
                                        {

                                            if ($qual->get_subtype()->get_id() == $filterSubType){
                                                $subTypeOkay = true;
                                            }

                                        }

                                        // If level wasn't in our filter list, exclude this qual from results
                                        if (!$subTypeOkay){
                                            $exclude = true;
                                        }


                                    }

                                }


                                // If not exlcuded, add to array
                                if (!$exclude){
                                    $qualArray[$qual->get_id()] = $qual;
                                }


                            }

                        }       



                        // If any quals were returned, order them. First by level, then by subtype
                        if ($qualArray){

                            $qualArray = self::sort_quals($qualArray);
                            $students = array();
                                
                            self::$qualArray = $qualArray;

                            foreach($qualArray as $qualification){

                                $sql = "SELECT u.id
                                          FROM {user} u
                                          INNER JOIN {block_bcgt_user_qual} uq ON uq.userid = u.id
                                          WHERE uq.bcgtqualificationid = ? AND uq.roleid = ? AND u.deleted = 0";
                                $params = array($qualification->get_id(), 5);
                                
                                
                                if (!empty($filters['mode']))
                                {

                                    $sql .= " AND ( ";

                                    foreach($filters['mode'] as $mode)
                                    {
                                        $sql .= " u.mode = ? OR ";
                                        $params[] = $mode;
                                    }

                                    // Remove trailing "OR"
                                    $sql = substr($sql, 0, -3);
                                    $sql .= " ) ";

                                }
                                
                                
                                
                                $qualStudents = $DB->get_records_sql($sql, $params);

                                if ($qualStudents){
                                    foreach($qualStudents as $student){
                                        $students[$student->id] = $student->id;
                                    }
                                }

                                $qualification->listOfStudents = $qualStudents;
                                
                                // Get the results of elements which are only applicable for the qual itself, not the students level
                                $results[$qualification->get_id()] = self::get_element_results($startType, 'QUALS', $qualification->get_id(), $elements, array($qualification), $qualStudents);                            

                            }

                        }

                        
                    break;
                    
                    case 'STUDS':
                        
                        $qualID = $startPoint;
                        
                        
                        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);

                        // If any quals were returned, order them. First by level, then by subtype
                        if ($qualification){

                                $sql = "SELECT u.id
                                        FROM {user} u
                                        INNER JOIN {block_bcgt_user_qual} uq ON uq.userid = u.id
                                        WHERE uq.bcgtqualificationid = ? AND uq.roleid = ? AND u.deleted = 0";
                                
                                $params = array($qualification->get_id(), 5);
                                
                                
                                if (!empty($filters['mode']))
                                {

                                    $sql .= " AND ( ";

                                    foreach($filters['mode'] as $mode)
                                    {
                                        $sql .= " u.mode = ? OR ";
                                        $params[] = $mode;
                                    }

                                    // Remove trailing "OR"
                                    $sql = substr($sql, 0, -3);
                                    $sql .= " ) ";

                                }
                                
                                $sql .= " ORDER BY u.lastname ASC, u.firstname ASC, u.username";
                                
                                
                                $qualStudents = $DB->get_records_sql($sql, $params);
                                
                                if ($qualStudents){
                                    foreach($qualStudents as $student){
                                        
                                        $results[$student->id] = self::get_element_results($startType, 'STUDS', $student->id, $elements, $qualification, array($student));
                                        
                                    }
                                }
                                

                        }
                        
                    break;
                    
                    
                }
                
                
            break;
            
            
            
        }
        
        return $results;
        
        
        
    }
    
    
    public static function get_element_results($startType, $level, $id, $elements, $qualifications, $students){
        
        global $DB;
        
        $results = array();
        
        if ($level == 'FAMILY'){
            
            $family = $DB->get_record("block_bcgt_type_family", array("id" => $id));
            $results['name'] = $family->family;
            
        } elseif ($level == 'CATEGORY'){
            
            $results['name'] = self::get_course_category_name_with_parent($id);
            
        } elseif ($level == 'QUALS'){
            
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELMIN;
            $loadParams->loadAddUnits = false;
            $qual = Qualification::get_qualification_class_id($id, $loadParams);
            $results['name'] = $qual->get_display_name();
            
        } elseif ($level == 'UNITS'){
            
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELMIN;
            $unit = Unit::get_unit_class_id($id, $loadParams);
            $results['name'] = $unit->get_display_name();
            
        } elseif ($level == 'STUDS'){
            
            $student = $DB->get_record("user", array("id" => $id));
            if ($student){
                $results['name'] = fullname($student) . " ({$student->username})";
            } else {
                $results['name'] = 'Error: No such user ('.$id.')';
            }
            
            // Singular
            $qual = $qualifications;
            $results['qualID'] = $qual->get_id();
            
            
        }
        
        
        
        
        // Loop through the elements we are including in our report
        foreach($elements as $element)
        {
            
            switch($element)
            {
                
                // No. Student Credits 
                case 'nostudcredits':
                // Max Credits - Highest number of credits a student has
                case 'maxcredits':
                // Min Credits - Lowest number of credits a student has
                case 'mincredits':
                // DEfault credits
                case 'defcredits':
                // No. Expected Credits
                case 'noexpectedcredits':
                // Credits offset
                case 'creditsoffset':
                // Students with wrong credits
                case 'nostudwrongcredits':
                // Students with > credits
                case 'nostudabovecredits':    
                // Students with < credits    
                case 'nostudbelowcredits':
                // Studets == credits
                case 'nostudcorrectcredits':
                    
                    // It'll try to do all this 6 times otherwise
                    if (!isset($results[$element]))
                    {
                    
                        // Loop through quals, then through students, work out avg
                        $cnt = 0; // Number of students
                        $min = 0;
                        $max = 0;
                        $expectedCredits = 0; // Total default credits
                        $actualCredits = 0; // Actual total credits
                        $numWithWrongCredits = 0;
                        $numWithCorrectCredits = 0;
                        $numWithAboveCredits = 0;
                        $numWithBelowCredits = 0;
                        
                        if ($level == 'FAMILY' || $level == 'CATEGORY' || $level == 'QUALS' || $level == 'UNITS'){
                        
                            
                            // Looking at Quals
                            if ($startType == 'qual' || $startType == 'cat'){
                                                    
                                if ($qualifications){

                                    $loadParams = new stdClass();
                                    $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
                                    $loadParams->loadAddUnits = false;

                                    foreach($qualifications as $qual){

                                        $defaultCredits = 0;

                                        if (method_exists($qual, 'get_default_credits')){
                                            $defaultCredits = $qual->get_default_credits();
                                        }

                                        $qualStudents = $qual->listOfStudents;

                                        $expectedCredits += ( $defaultCredits * count($qualStudents) );

                                        if ($qualStudents){

                                            $cnt += count($qualStudents);

                                            foreach($qualStudents as $student){

                                                // How many credits does this student have on this qual?
                                                $qual->load_student_information($student->id, $loadParams);

                                                if (method_exists($qual, 'get_students_total_credits')){

                                                    $credits = $qual->get_students_total_credits();

                                                    $actualCredits += $credits;

                                                    if ($credits < $min || $min == 0){
                                                        $min = $credits;
                                                    }

                                                    if ($credits > $max){
                                                        $max = $credits;
                                                    }

                                                    if ($defaultCredits > 0 && $credits <> $defaultCredits){
                                                        $numWithWrongCredits++;
                                                    }
                                                    
                                                    if ($defaultCredits > 0 && $credits > $defaultCredits){
                                                        $numWithAboveCredits++;
                                                    }
                                                    
                                                    if ($defaultCredits > 0 && $credits < $defaultCredits){
                                                        $numWithBelowCredits++;
                                                    }
                                                    
                                                    if ($defaultCredits > 0 && $credits == $defaultCredits){
                                                        $numWithCorrectCredits++;
                                                    }

                                                }

                                            }

                                        }

                                    }

                                }
                            
                            } elseif ($startType == 'unit') {
                                
                                // Looking at units
                                $units = $qualifications;
                                
                                $loadParams = new stdClass();
                                $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
                                $loadParams->loadAddUnits = false;
                                
                                foreach($units as $unit){

                                    $defaultCredits = 0;

                                    if (method_exists($unit, 'get_default_credits')){
                                        $defaultCredits = $unit->get_default_credits();
                                        $results['defcredits'] = $defaultCredits;
                                    }

                                }                                
                                
                            }
                            
                            
                            
                            
                            if (isset($credits))
                            {
                                
                                $offset = -( $expectedCredits - $actualCredits );
                                if ($offset > 0) $offset = '+' . $offset;

                                $results['noexpectedcredits'] = $expectedCredits;
                                $results['nostudcredits'] = $actualCredits;
                                $results['mincredits'] = $min;
                                $results['maxcredits'] = $max;
                                $results['creditsoffset'] = $offset;

                                if ($level == 'QUALS'){

                                    // Should only be one qual sent in the array, so defaultCredits won't change
                                    $results['defcredits'] = $defaultCredits;

                                }
                                
                                $results['nostudwrongcredits'] = $numWithWrongCredits;
                                $results['nostudabovecredits'] = $numWithAboveCredits;
                                $results['nostudbelowcredits'] = $numWithBelowCredits;
                                $results['nostudcorrectcredits'] = $numWithCorrectCredits;
                            
                            }
                            
                            
                            
                            
                        
                        } elseif ($level == 'STUDS') {
                            
                            if ($student)
                            {
                            
                                
                                if ($startType == 'qual' || $startType == 'cat'){
                                
                                    $loadParams = new stdClass();
                                    $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
                                    $loadParams->loadAddUnits = false;
                                    
                                    // Singular to avoid confusion
                                    $qual = $qualifications;
                                    $qual->load_student_information($student->id, $loadParams);
                                    
                                    if (method_exists($qual, 'get_default_credits')){
                                        $defaultCredits = $qual->get_default_credits();
                                    }
                                    
                                    $results['nostudwrongcredits'] = 0;

                                    if (method_exists($qual, 'get_students_total_credits')){

                                        
                                        $results['nostudwrongcredits'] = 0;
                                        $results['nostudabovecredits'] = 0;
                                        $results['nostudbelowcredits'] = 0;
                                        $results['nostudcorrectcredits'] = 0;
                                        
                                        
                                        $credits = $qual->get_students_total_credits();
                                        $results['nostudcredits'] = $credits;

                                        $offset = -( $defaultCredits - $credits );
                                        if ($offset > 0) $offset = '+' . $offset;

                                        if ($offset <> 0){
                                            $results['nostudwrongcredits'] = 1;
                                        }
                                        
                                        if ($credits > $defaultCredits){
                                            $results['nostudabovecredits'] = 1;
                                        } elseif ($credits < $defaultCredits){
                                            $results['nostudbelowcredits'] = 1;
                                        } elseif ($credits == $defaultCredits){
                                            $results['nostudcorrectcredits'] = 1;
                                        }

                                        $results['creditsoffset'] = $offset;
                                        $results['noexpectedcredits'] = $defaultCredits;

                                    }
                                
                                } elseif ($startType == 'unit'){
                                    
                                    // Can't do it, as we have to specify a qualID to load the student into unit
                                    
                                }
                            
                            }
                            
                        }
                                                
                    
                    }
                    
                break;
            
                
               
            
                case 'noquals':
                    
                    // If looking @ a student, couht how many quals that student is on
                    if ($level == 'STUDS' && $student){
                        
                        $quals = get_users_quals($student->id, 5);
                        $results['noquals'] = count($quals);
                        
                    } else {
                        
                        if ($startType == 'qual' || $startType == 'cat'){
                            $results['noquals'] = count($qualifications);
                        } elseif ($startType == 'unit'){
                            
                            // Count how many quals this unit appears on
                            $units = $qualifications;
                            $arr = array();
                            
                            foreach($units as $unit){
                                $qualUnits = $DB->get_records("block_bcgt_qual_units", array("bcgtunitid" => $unit->get_id()));
                                if ($qualUnits){
                                    foreach($qualUnits as $qualUnit){
                                        $arr[$qualUnit->bcgtqualificationid] = $qualUnit->bcgtqualificationid;
                                    }
                                }
                            }
                            
                            $results['noquals'] = count($arr);
                            
                        }
                        
                    }
                    
                    
                break;
            
                // No. Staff - linked to the qual
                case 'nostaff':
                    
                    if ($level == 'FAMILY' || $level == 'QUALS' || $level == 'CATEGORY'){
                        
                        $staff = array();
                        
                        if ($startType == 'qual' || $startType == 'cat'){
                        
                            foreach($qualifications as $qual){


                                $qualStaff = $DB->get_records_select("block_bcgt_user_qual", "bcgtqualificationid = ? AND roleid < ?", array($qual->get_id(), 5));

                                if ($qualStaff){
                                    foreach($qualStaff as $s){
                                        $staff[$s->userid] = $s->userid;
                                    }
                                }

                            }
                            
                            $results['nostaff'] = count($staff);
                        
                        } elseif ($startType == 'unit'){
                            
                            $results['nostaff'] = '-';
                            
                        }
                        
                        
                        
                    }
                    
                break;
            
                // No. Students - linked to the qual
                case 'nostud':
                    
                    // Pointless putting "1" for student rows
                    if ($level == 'FAMILY' || $level == 'QUALS' || $level == 'UNITS' || $level == 'CATEGORY'){
                        $results['nostud'] = count($students);
                    }
                    
                break;
            
                // No. Units - on the qual
                case 'nounits':
                    
                    if ($level == 'FAMILY' || $level == 'QUALS' || $level == 'UNITS' || $level == 'CATEGORY'){
                        
                        $units = array();
                        
                        if ($startType == 'qual' || $startType == 'cat'){
                        
                            foreach($qualifications as $qual){

                               $qualUnits = $qual->get_units();
                               if ($qualUnits){
                                   foreach($qualUnits as $qualUnit){
                                       $units[$qualUnit->get_id()] = $qualUnit->get_id();
                                   }
                               }

                            }     
                            
                            $results['nounits'] = count($units);
                        
                        } elseif ($startType == 'unit'){
                                                        
                            $units = $qualifications;                            
                            $results['nounits'] = count($units);
                            
                        }
                        
                    }
                    
                break;
                
                // Number of criteria on the qual
                case 'nocrit':
                    
                    if ($level == 'FAMILY' || $level == 'QUALS' || $level == 'UNITS' || $level == 'CATEGORY'){
                        
                        $crit = array();
                        
                        
                        if ($startType == 'qual' || $startType == 'cat'){
                        
                            foreach($qualifications as $qual){

                               $qualUnits = $qual->get_units();
                               if ($qualUnits){
                                   foreach($qualUnits as $qualUnit){
                                       $unitCriteria = $qualUnit->get_criteria();
                                       if ($unitCriteria){
                                           foreach($unitCriteria as $unitCriterion){
                                               $crit[$unitCriterion->get_id()] = $unitCriterion->get_id();
                                           }
                                       }
                                   }
                               }

                            }     
                        
                        } elseif ($startType == 'unit'){
                            
                            $units = $qualifications; // Variable is called qualifications in the method, but in this context its units
                            
                            foreach($units as $unit){
                               $unitCriteria = $unit->get_criteria();
                               if ($unitCriteria){
                                   foreach($unitCriteria as $unitCriterion){
                                       $crit[$unitCriterion->get_id()] = $unitCriterion->get_id();
                                   }
                               }
                           }
                            
                        }
                        
                        $results['nocrit'] = count($crit);
                        
                    }
                    
                break;
            
                // Predicted grade - That student has on this qual
                case 'predgrade':
                // Target grade - That student has on this qual
                case 'targetgrade':
                // Value added
                case 'nostudbelowtarget':
                case 'nostudontarget':
                case 'nostudaheadtarget':
                    
                    if (!isset($results[$element]))
                    {
                    
                        $numAheadOfTarget = 0;
                        $numOnTarget = 0;
                        $numBehindTarget = 0;

                        if ($level == 'FAMILY' || $level == 'QUALS' || $level == 'CATEGORY'){

                            if ($startType == 'qual' || $startType == 'cat'){

                                    if ($qualifications){

                                        $loadParams = new stdClass();
                                        $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
                                        $loadParams->loadAddUnits = false;
                                        $loadParams->loadAward = true;

                                        foreach($qualifications as $qual){

                                            $defaultCredits = 0;
                                            if (method_exists($qual, 'get_default_credits')){
                                                $defaultCredits = $qual->get_default_credits(); #180
                                            }
                                            
                                            if ($qual->listOfStudents){

                                                foreach($qual->listOfStudents as $student){

                                                    $qual->load_student_information($student->id, $loadParams);
                                                    $credits = $qual->get_students_total_credits(); #220
                                                    
                                                    // Only do it if their credits are correct
                                                    if ($defaultCredits > 0) #&& $credits == $defaultCredits
                                                    {

                                                        $predRank = false;
                                                        $targetRank = false;

                                                        // Predicted Grade
                                                        $predAward = $qual->get_predicted_award();
                                                        if ($predAward)
                                                        {
                                                            if(method_exists($predAward, 'get_rank'))
                                                            {
                                                                $predRank = $predAward->get_rank();
                                                            }
                                                            elseif(method_exists($predAward, 'get_ranking'))
                                                            {
                                                                $predRank = $predAward->get_ranking();
                                                            }
                                                        }


                                                        // Target Grade
                                                        $userCourseTarget = new UserCourseTarget();
                                                        $targetGrade = $userCourseTarget->retrieve_users_target_grades($student->id, $qual->get_id());
                                                        if($targetGrade)
                                                        {
                                                            $targetGradeObj = $targetGrade[$qual->get_id()];
                                                            if($targetGradeObj && isset($targetGradeObj->breakdown))
                                                            {
                                                                $breakdown = $targetGradeObj->breakdown;
                                                                if ($breakdown && method_exists($breakdown, 'get_ranking'))
                                                                {
                                                                    $targetRank = $breakdown->get_ranking();
                                                                }
                                                            }
                                                        }


                                                        // If we have both
                                                        if ($targetRank && $predRank){

                                                            if ($predRank > $targetRank){
                                                                $numAheadOfTarget++;
                                                            } elseif ($predRank == $targetRank){
                                                                $numOnTarget++;
                                                            } elseif ($predRank < $targetRank){
                                                                $numBehindTarget++;
                                                            }

                                                        } 
                                                    
                                                    }

                                                }

                                            }

                                        }

                                    }

                            }

                            $results['targetgrade'] = '-';
                            $results['predgrade'] = '-';



                        } elseif ($level == 'STUDS' && $student){

                            $predRank = false;
                            $targetRank = false;

                            if ($startType == 'qual' || $startType == 'cat'){


                                $loadParams = new stdClass();
                                $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
                                $loadParams->loadAward = true;
                                $loadParams->loadAddUnits = false;

                                // Singular to avoid confusion
                                $qual = $qualifications;
                                $qual->load_student_information($student->id, $loadParams);
                                
                                $defaultCredits = 0;
                                if (method_exists($qual, 'get_default_credits')){
                                    $defaultCredits = $qual->get_default_credits();
                                }
                                
                                $credits = $qual->get_students_total_credits();
                                
                                $results['predgrade'] = '-';
                                $results['targetgrade'] = '-';
                                
                                // Only do it if their credits are correct
                                if ($defaultCredits > 0) #&& $credits == $defaultCredits
                                {
                                
                                    // Predicted Grade
                                    $predAward = $qual->get_predicted_award();
                                    if ($predAward)
                                    {
                                        $award = $predAward->get_award();
                                        if ($award)
                                        {
                                            $results['predgrade'] = $award;
                                        }
                                        if(method_exists($predAward, 'get_rank'))
                                        {
                                            $predRank = $predAward->get_rank();
                                        }
                                        elseif(method_exists($predAward, 'get_ranking'))
                                        {
                                            $predRank = $predAward->get_ranking();
                                        }
                                        $results['predgrade'] .= ' ('.$predRank.')';
                                    }


                                    // Target Grade
                                    $userCourseTarget = new UserCourseTarget();
                                    $targetGrade = $userCourseTarget->retrieve_users_target_grades($student->id, $qual->get_id());
                                    if($targetGrade)
                                    {
                                        $targetGradeObj = $targetGrade[$qual->get_id()];
                                        if($targetGradeObj && isset($targetGradeObj->breakdown))
                                        {
                                            $breakdown = $targetGradeObj->breakdown;
                                            if($breakdown)
                                            {
                                                $results['targetgrade'] = $breakdown->get_target_grade();
                                            }
                                            if (method_exists($breakdown, 'get_ranking'))
                                            {
                                                $targetRank = $breakdown->get_ranking();
                                            }
                                            $results['targetgrade'] .= ' ('.$targetRank.')';
                                        }
                                    }
                                }
                            }

                            // If we have both
                            if ($targetRank && $predRank){

                                if ($predRank > $targetRank){
                                    $numAheadOfTarget = 1;
                                } elseif ($predRank == $targetRank){
                                    $numOnTarget = 1;
                                } elseif ($predRank < $targetRank){
                                    $numBehindTarget = 1;
                                }

                            } 


                        }

                        $results['nostudaheadtarget'] = $numAheadOfTarget;
                        $results['nostudontarget'] = $numOnTarget;
                        $results['nostudbelowtarget'] = $numBehindTarget;
                    
                    }
                                                    
                    
                break;
                
                // Find all the criteria linked to assignments on these quals
                // Then find out how many of those are achieved
                case 'percentasscritachieved':
                case 'nostudasscriteria':
                case 'noasscriteria':
                    
                    $results['percentasscritachieved'] = '-';
                    $results['noasscriteria'] = '-';
                    $results['nostudasscriteria'] = '-';

                    $totalAssignmentCriteria = 0;
                    $totalAssignmentCriteriaAchieved = 0;
                    $distinctAssignmentCriteria = array();
  
                    // Qual family, qual, or course cat level
                    if ($level == 'FAMILY' || $level == 'QUALS' || $level == 'CATEGORY'){

                        if ($startType == 'qual' || $startType == 'cat'){

                            if ($qualifications){

                                $loadParams = new stdClass();
                                $loadParams->loadLevel = Qualification::LOADLEVELALL;
                                $loadParams->loadAddUnits = false;
                                $loadParams->loadAward = true;

                                foreach($qualifications as $qual){

                                    if ($qual->listOfStudents){

                                        foreach($qual->listOfStudents as $student){

                                            // Get all the criteria on this qualification which are linked
                                            // to activities
                                            $criteriaWithActivities = bcgt_get_criteria_activities_on_qual($qual->get_id());
                                            
                                            $totalAssignmentCriteria += count($criteriaWithActivities);

                                            // Go through the criteria and see if this student has achieved them
                                            if ($criteriaWithActivities){

                                                foreach($criteriaWithActivities as $critWithAct){
                                                    $distinctAssignmentCriteria[$critWithAct->id] = $critWithAct->id;
                                                    $unit = $qual->get_unit($critWithAct->bcgtunitid);
                                                    if ($unit){
                                                        $unit->load_student_information($student->id, $qual->get_id(), $loadParams);
                                                        if ($unit->is_student_doing()){
                                                            $studentCriterion = $unit->get_single_criteria($critWithAct->id);
                                                            if ($studentCriterion){
                                                                if ($studentCriterion->is_met()){
                                                                    $totalAssignmentCriteriaAchieved++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    
                    
                    
                    
                    
                    // Student level
                    if ($level == 'STUDS' && $student){
                        
                        // If by course category, then we can 
                        if ( $startType == 'cat' || $startType == 'qual'){
                            
                            $loadParams = new stdClass();
                            $loadParams->loadLevel = Qualification::LOADLEVELALL;
                            $loadParams->loadAward = true;
                            $loadParams->loadAddUnits = false;
                            
                            $qual = $qualifications;
                            $qual->load_student_information($student->id, $loadParams);
                            
                            // Get all the criteria on this qualification which are linked
                            // to activities
                            $criteriaWithActivities = bcgt_get_criteria_activities_on_qual($qual->get_id());
                            
                            $totalAssignmentCriteria += count($criteriaWithActivities);
                            
                            // Go through the criteria and see if this student has achieved them
                            if ($criteriaWithActivities){
                                foreach($criteriaWithActivities as $critWithAct){
                                    $distinctAssignmentCriteria[$critWithAct->id] = $critWithAct->id;
                                    $unit = $qual->get_unit($critWithAct->bcgtunitid);
                                    if ($unit){
                                        if ($unit->is_student_doing()){
                                            $studentCriterion = $unit->get_single_criteria($critWithAct->id);
                                            if ($studentCriterion){
                                                if ($studentCriterion->is_met()){
                                                    $totalAssignmentCriteriaAchieved++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                                        
                    $results['noasscriteria'] = count($distinctAssignmentCriteria);
                    $results['nostudasscriteria'] = $totalAssignmentCriteria;
                    
                    // Work out percentage
                    if ($totalAssignmentCriteria > 0){
                        $results['percentasscritachieved'] = round( ( $totalAssignmentCriteriaAchieved / $totalAssignmentCriteria ) * 100) . '%';
                    }
                    
                break;
                
                
            }
            
        }
        
        return $results;
        
    }
    
    
    
    public static function sort_quals($qualifications){
        
        usort($qualifications, function($a, $b){
                        
                        
                        return (    ($a->get_level()->get_id() === $b->get_level()->get_id()) 
                                        ? ( 
                                            ($a->get_subtype()->get_id() === $b->get_subtype()->get_id()) 
                                                ?
                                                    strcasecmp($a->get_name(), $b->get_name())
                                                :
                                            ( $b->get_subtype()->get_id() < $a->get_subtype()->get_id() )        
                                
                                     
                                      ) : 
                                    ( $b->get_level()->get_id() < $a->get_level()->get_id() )
                               );
                        
        });
                    
        return $qualifications;
        
    }
    
    
    
    public static function sort_units($units){
        
        usort($units, function($a, $b){
                        
                $aName = $a->get_name();
                $bName = $b->get_name();
                
                // Strip "Unit " frmo the front if it has it, and just use the number
                if (stripos($aName, 'Unit ') === 0){
                    $aName = substr($aName, 5);
                }
                if (stripos($bName, 'Unit ') === 0){
                    $bName = substr($bName, 5);
                }
            
                        
                return (    ($a->get_level()->get_id() === $b->get_level()->get_id()) 
                                ? ( 
                                        strnatcmp($aName, $bName)
                                  ) : 
                            ( $b->get_level()->get_id() < $a->get_level()->get_id() )
                       );
                        
        });
                    
        return $units;
        
    }
    
    
    
    
}