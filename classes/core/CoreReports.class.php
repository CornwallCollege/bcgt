<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CoreReports
 *
 * @author mchaney
 */
abstract class CoreReports {
    //put your code here
    
    protected $options = array();
    CONST oncolor = '0066FF';
    CONST aheadcolor = '00CC99';
    CONST behindcolor = 'F28A8C';
    public function CoreReports()
    {
        
    }
    
    public static function display_view_reports()
    {
        set_time_limit(0);
        global $CFG;
        $retval = '';
        
        //needs to load all of the classes in the folder
        //then needs to instatiate them
        //then needs to output their names. 
        
        $directories = scandir($CFG->dirroot.'/blocks/bcgt/classes/reports/');
        if($directories)
        {
            $retval .= "<table id='bcgt_reports'>";
        
            $retval .= "<tr><th>Report Name</th><th colspan='2'>Report Description</th></tr>";
            $count = 0;
            //there is always a '.' and a '..' directory
            foreach($directories AS $directory)
            {
                $count++;
                if($directory != '.' && $directory != '..')
                {
                    //require_once the class
                    require_once($CFG->dirroot.'/blocks/bcgt/classes/reports/'.$directory);
                    //get the class name
                    $className = strtok($directory, '.');
                    $classObject = new $className();
                    
                    $retval .= '<tr>';
                    $retval .= "<td><a href='my_dashboard.php?tab=reporting&action=bespoke&id={$count}' target='_blank'>{$classObject->get_name()}</a></td>"; 
                    $retval .= '<td>';
                    $retval .= '<span class="image_cont">';
                    $retval .= '<span class="report_icon">';
                    $retval .= '<a href="my_dashboard.php?tab=reporting&action=bespoke&id='.$count.'" target="_blank">';
                    $retval .= $classObject->get_icon();
                    $retval .= '</a></span>';
                    $retval .= '<span class="report_image">';
                    $retval .= $classObject->get_image();
                    $retval .= '</span>';
                    $retval .= '</span>';
                    $retval .= '</td>';
                    $retval .= '<td>';
                    $retval .= '<span class="report_desc">';
                    $retval .= $classObject->get_description();
                    $retval .= '</span>';
                    $retval .= '</td>';
                    $retval .= '</tr>';
                }
            }
            $retval .= '</table>';
        }
        
        return $retval;
    }
    abstract function get_frozen_panes();
    abstract function get_frozen_columns();
    abstract function get_frozen_width();
    abstract function has_split_header();
    abstract function can_run_data_tables();
    abstract function can_run();
    abstract function can_export();
    abstract function get_icon();
    abstract function get_image();
    
    function get_examples()
    {
        return null;
    }
    
    public static function display_view_report($number, $export = false)
    {
        set_time_limit(0);
        global $CFG, $PAGE;
        $retval = '';
        $directories = scandir($CFG->dirroot.'/blocks/bcgt/classes/reports/');
        if($directories)
        {
            $count = 0;
            //there is always a '.' and a '..' directory
            foreach($directories AS $directory)
            {
                $count++;
                if($directory != '.' && $directory != '..' && ($count == $number))
                {
                    //require_once the class
                    require_once($CFG->dirroot.'/blocks/bcgt/classes/reports/'.$directory);
                    //get the class name
                    $className = strtok($directory, '.');
                    $classObject = new $className();
                    if(!$export)
                    {
                        $jsModule = array(
                        'name'     => 'block_bcgt',
                        'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
                        'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
                        );
                        $PAGE->requires->js_init_call('M.block_bcgt.initcorereports', 
                                array($CFG->wwwroot.'/blocks/bcgt/forms/export_core_report.php',
                                    $CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=reporting&action=bespoke', 
                                    $classObject->get_frozen_columns(), $classObject->get_frozen_width(), 
                                    $classObject->can_run_data_tables()), true, $jsModule);
                        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
                        load_javascript();
                    }
                    
                    $retval .= '<form name="runreport" id="corereportrun" method="POST" action="my_dashboard.php?tab=reporting&action=bespoke">';
                    $retval .= '<div id="reportoptions">';
                    $retval .= '<h2 id="optionsHeader">'.get_string('reportoptions','block_bcgt').'</h2>';
                    $retval .= '<div id="optionsContent" class="content_collapse">';
                    $retval .= $classObject->display_options();
                    $retval .= '</div>';
                    $retval .= $classObject->get_output_options($classObject->can_run(), $classObject->can_export());
                    $retval .= '</div>';
                    $retval .= '<input type="hidden" name="id" value="'.$number.'"/>';
                    $retval .= '</form>';
                    
                    //if it was run or export run
                    if(isset($_POST['run']) && !$export)
                    {
                        $retval .= '<div id="reportResults">';
                        $retval .= '<h2>'.get_string('results', 'block_bcgt').'</h2>';
                        $retval .= '<div id="results">';
                        $retval .= $classObject->run_display_report();
                        $retval .= '</div></div>';
                    }
                    elseif($export)
                    {
                        $classObject->export_report();
                    }
                    else
                    {
                        $retval .= $classObject->get_examples();
                    }
                    break;
                }
            }
            
        }
        return $retval;
    }
    
    abstract function get_name();
    
    abstract function get_description();
    
    abstract function display_options();
    
    abstract function run_display_report();
    
    abstract function export_report();
    
    /**
     * This function builds the header and the content. 
     * ******** HEADER **********
     * The header comes from $this->header
     * If the header is a split header (more than one row), ($this->has_split_header())
     *      Then the header object is an array of rows. 
     *      Each row is an array of objects. 
     *      Each object has colCount and content as properties
     *      $this->header = array($row0 = array(new stdClass(colCount, content), 
     *      new stdClass(colCount, content)),$row1 = array(...))
     * 
     * Else the header is an array of TH CONTENTS
     * 
     * ************** BODY ***************
     * The body comes from $this->data
     * $this->data is an array or rows
     * $this->data = array(row0, row1, row2.....)
     * Each row is an array or cells. 
     * $row0 = array(cell0, cell1, cell 2)
     * If cell is an object
     *      Then it will take the colspan, content and class properties and create a cell with these
     * Else its a string
     *      Its JUST the html content of the TD
     *
     * @return string
     */
    function perform_export()
    {
        global $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        global $CFG, $USER;
        $name = preg_replace("/[^a-z 0-9]/i", "", $this->get_name());
    
        ob_clean();
        header("Pragma: public");
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.$name.'.xlsx"');     
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);

        require_once $CFG->dirroot . '/blocks/bcgt/lib/PHPExcel/Classes/PHPExcel.php';
    
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
                     ->setCreator(fullname($USER))
                     ->setLastModifiedBy(fullname($USER))
                     ->setTitle($this->get_name())
                     ->setSubject($this->get_name())
                     ->setDescription($this->get_description());

        // Remove default sheet
        $objPHPExcel->removeSheetByIndex(0);
        
        $sheetIndex = 0;
        
        // Set current sheet
        $objPHPExcel->createSheet($sheetIndex);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $objPHPExcel->getActiveSheet()->setTitle("Report");
        
        $rowNum = 1;

        // Headers
        if(isset($this->header))
        {
            if(!$this->has_split_header())
            {
                $col = 0;
                foreach($this->header AS $head)
                {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $rowNum, $head);
                    $col++;
                }
                $rowNum++;
            }
            else
            {
                //foreach row
                foreach($this->header AS $row)
                {
                    $col = 0;
                    foreach($row AS $rowObj)
                    {
                        $columnCount = $rowObj->colCount;
                        $columnContent = $rowObj->content;
                        //add all the cells, 
                        //thenmerge
                        $startCol = $col;
                        for($i=0;$i<$columnCount;$i++)
                        {
                            if($i == 0)
                            {
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $rowNum, $columnContent);
                            }
                            else
                            {
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $rowNum, '');
                            }
                            $col++;
                        }
                        $endCol = $col;
                        if($columnCount != 1)
                        {
                            $objPHPExcel->getActiveSheet()->mergeCells(''.PHPExcel_Cell::stringFromColumnIndex($startCol).
                                ''.$rowNum.':'.PHPExcel_Cell::stringFromColumnIndex($endCol - 1).''.$rowNum);
                        }
                        
                        
                    }
                    $rowNum++;
                }
            } 
            
        }
        //data
        if(isset($this->data))
        {
            foreach($this->data AS $data)
            {
                $col = 0;
                foreach($data AS $cell)
                {  
                    if(is_a($cell, 'stdClass'))
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $rowNum, $this->build_excell_cell($cell));
                        $objPHPExcel->getActiveSheet()->getStyle(''.PHPExcel_Cell::stringFromColumnIndex($col).''.$rowNum)->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => $this->get_excell_cell_color($cell)
                                ),
                                'borders' => array(
                                    'outline' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                                        'color' => array('rgb'=>'cfcfcf')
                                    )
                                )
                            )
                        );                        
                        if(isset($cell->colspan) && $cell->colspan > 1)
                        {
                            $objPHPExcel->getActiveSheet()->mergeCells(''.PHPExcel_Cell::stringFromColumnIndex($col).
                                ''.$rowNum.':'.PHPExcel_Cell::stringFromColumnIndex($col + ($cell->colspan - 1)).''.$rowNum);
                            
                            $col = $col + ($cell->colspan - 1);
                        }
                    }
                    else
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $rowNum, $cell);
                    }
                    
                    $col++;
                } 
                $rowNum++;
            }
        }
        
        // Freeze rows and cols (everything to the left of D and above 2)
        $objPHPExcel->getActiveSheet()->freezePane($this->get_frozen_panes());
        
        // End
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        ob_clean();
        $objWriter->save('php://output');
        
        exit;
    }
    
    function get_core_options($data = null)
    {
        $families = get_qualification_type_families_used();
        $levels = bcgt_get_all_levels();
        $subtypes = bcgt_get_all_subtypes();
        
        $out = '<div id="coreOptions">';
        $out .= '<table><thead><tr>';
        $out .= '<th>'.get_string('categories').'</th>';
        $out .= '<th>'.get_string('family', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('levels', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('subtypes', 'block_bcgt').'</th>';
        $out .= '</tr></thead>';
        $out .= '<tbody>';
        $out .= '<tr>';
        $out .= '<td>';
        $out .= "<div>";
            
        $categories = get_config('bcgt', 'reportingcats');
        if ($categories)
        {
            $out .= "<select name='start_cat' style='max-width:90%;'>";
            $out .= "<option value=''>All</option>";
            //$output .= "<option value='all' ".( (isset($data) && $data['start_point'] == 'all') ? 'selected' : '' ).">All</option>";
            $catArray = explode(",", $categories);
            foreach($catArray as $catID)
            {    
                $catName = ReportingSystem::get_course_category_name_with_parent($catID);
                $out .= "<option value='{$catID}' ".( (isset($_POST['start_cat']) && $_POST['start_cat'] == $catID) ? 'selected' : '' ).">{$catName}</option>";
            }
            $out .= "</select>";
        }
        else
        {
            $out .= "<p>No Categories Defined in BCGT Settings</p>";
        }
        if(isset($_POST['start_cat']))
        {
            $this->options['categoryid'] = $_POST['start_cat'];
        }
        
        $out .= "</div>";
        $out .= '</td>';
        $out .= '<td>';
        $this->options['family'] = array();
        foreach($families AS $family)
        {
            $checked = '';
            if(isset($_POST['family_'.$family->id]))
            {
                $this->options['family'][$family->id] = $family->id;
                $checked = 'checked';
            }
            $out .= '<span>'.$family->family.' : <input type="checkbox" '.$checked.' name="family_'.$family->id.'"/></span>';
        }
        $out .= '</td>';
        $out .= '<td>';
        $this->options['level'] = array();
        foreach($levels AS $level)
        {
            $checked = '';
            if(isset($_POST['level_'.$level->id]))
            {
                $this->options['level'][$level->id] = $level->id;
                $checked = 'checked';
            }
            $out .= '<span>'.$level->trackinglevel.' : <input type="checkbox" '.$checked.' name="level_'.$level->id.'"/></span>';
        }
        $out .= '</td>';
        $out .= '<td>';
        $this->options['subtype'] = array();
        foreach($subtypes AS $subtype)
        {
            $checked = '';
            if(isset($_POST['subtype_'.$subtype->id]))
            {
                $this->options['subtype'][$subtype->id] = $subtype->id;
                $checked = 'checked';
            }
            $out .= '<span>'.$subtype->subtype.' : <input type="checkbox" '.$checked.' name="subtype_'.$subtype->id.'"/></span>';
        }
        $out .= '</td>';
        $out .= '</tr>';
        $out .= '</tbody></table>';
        $out .= '</div>';
        return $out;
    }
    
    protected function get_my_students_filter()
    {
        $out = get_string('mystudents','block_bcgt');
        $out .= '<select name="mystudents">';
        $selected = '';
        if(isset($_POST['mystudents']) && $_POST['mystudents'] == '')
        {
            $selected = 'selected';
        }
        $out .= '<option '.$selected.' value="">'.get_string('showall','block_bcgt').'</option>'; 
        $selected = '';
        if(isset($_POST['mystudents']) && $_POST['mystudents'] == 'qual')
        {
            $selected = 'selected';
            $this->options['mystudents'] = 'qual';
        }
        $out .= '<option '.$selected.' value="qual">'.get_string('showbyquals','block_bcgt').'</option>';
        if($tutorRole = get_config('bcgt','tutorrole'))
        {
            $selected = '';
            if(isset($_POST['mystudents']) && $_POST['mystudents'] == 'user')
            {
                $selected = 'selected';
                $this->options['mystudents'] = 'user';
            }
            $out .= '<option '.$selected.' value="user">'.get_string('showbyrole','block_bcgt').' : '.$tutorRole.'</option>';
        }
        $out .= '</select>';
        return $out;
    }
    
    protected function get_grade_filter($showEitherOption = false)
    {
        $out = '<span>';
        $out .= get_string('gradegc', 'block_bcgt').' : ';
        $out .= '<select name="gradeoption">';
        if($showEitherOption)
        {
            $selected = '';
            if(isset($_POST['gradeoption']) && $_POST['gradeoption'] == 'either')
            {
                $selected = 'selected';
                $this->options['gradeoption'] = 'either'; 
            }
            $out .= '<option value="either" '.$selected.'>'.get_string('either','block_bcgt').'</value>';
        }
       
        $selected = '';
        if(isset($_POST['gradeoption']) && $_POST['gradeoption'] == 'ceta')
        {
            $selected = 'selected';
            $this->options['gradeoption'] = 'ceta'; 
        }
        $out .= '<option value="ceta" '.$selected.'>'.get_string('ceta','block_bcgt').'</value>';
        $selected = '';
        if(isset($_POST['gradeoption']) && $_POST['gradeoption'] == 'grade')
        {
            $selected = 'selected';
            $this->options['gradeoption'] = 'grade'; 
        }
        $out .= '<option value="grade" '.$selected.'>'.get_string('grade','block_bcgt').'</value>';
        $out .= '</select>';
        $out .= '</span>';     
        return $out;
    }
    
    protected function get_target_grade_filter()    
    {
        $out = '<span>';
        $checked = '';
        if(isset($_POST['targetgrade']))
        {
            $checked = 'checked';
            $this->options['targetgrade'] = true; 
        }
        $out .= get_string('targetgradetg', 'block_bcgt').' : <input type="checkbox" '.$checked.' name="targetgrade"/>';
        $out .= '</span>';
        //weighted?
        if(get_config('bcgt', 'allowalpsweighting'));
        {
            $out .= '<span>';
            $checked = '';
            if(isset($_POST['weightedgrade']))
            {
                $checked = 'checked';
                $this->options['weightedgrade'] = true; 
            }
            $out .= get_string('specifictargetgradest', 'block_bcgt').' : <input type="checkbox" '.$checked.' name="weightedgrade"/>';
            $out .= '</span>';
        }
        return $out;
    }
    
    function get_grade_options($data = null)
    {
        //wants to return, for all targetqualids, the possible grades
        
    }
    
    function build_student_selector()
    {
        $out = '';
        $out .= '<div>';
        $out .= get_string('studentfilter', 'block_bcgt').' : ';
        $out .= '<select name="studentfilter">';
        $selected = '';
        if(isset($_POST['studentfilter']) && $_POST['studentfilter'] == 'all')
        {
            $this->options['studentfilter'] = $_POST['studentfilter'];
            $selected = 'selected';
        }
        $out .= '<option '.$selected.' value="all">All</value>';
        $letter = 'A';
        for($i=0;$i<26;$i++)
        {
            $selected = '';
            if(isset($_POST['studentfilter']) && $_POST['studentfilter'] == $letter)
            {
                $this->options['studentfilter'] = $_POST['studentfilter'];
                $selected = 'selected';
            }
            $out .= '<option '.$selected.' value="'.$letter.'">'.$letter.'</option>';
            $letter++;
        }
        $out .= '</select>';
        $out .= '</div>';
        return $out;
    }
    
    function get_target_grade_options()
    {
        global $CFG;
        require_once $CFG->dirroot . '/blocks/bcgt/classes/core/TargetGrade.class.php';
        //wants to return, for all targetqualids, the possible target grades
        $targetQuals = bcgt_get_all_target_qual();
        $out = '<table>';
        
        $targetGradeObj = new TargetGrade();
        foreach($targetQuals AS $targetQual)
        {
            $out .= '<tr>';
            $out .= '<td>'.$targetQual->family.'</td>';
            $out .= '<td>'.$targetQual->trackinglevel.'</td>';
            $out .= '<td>'.$targetQual->subtype.'</td>';
            $targetGrades = $targetGradeObj->get_all_target_grades($targetQual->id);
            foreach($targetGrades AS $targetGrade)
            {
                $out .= '<td>';
                $out .= $targetGrade->grade.' : <input type="checkbox" name=""/>';
                $out .= '</td>';
            }
            $out .= '</tr>';
        }
        $out .= '</table>';
        return $out;
    }

    function get_output_options($run = true, $export = true)
    {
        global $CFG;
        $out = '<div id="outputcommands">';
        $disabled = '';
        if(!$run)
        {
            $disabled = 'disabled';
        }
        $out .= '<input type="submit" id="runsub" '.$disabled.' name="run" value="'.get_string('run', 'block_bcgt').'"/>';
        $disabled = '';
        if(!$export)
        {
            $disabled = 'disabled';
        }
        $out .= '<input type="submit" id="exportsub" '.$disabled.' name="run" value="'.get_string('export', 'block_bcgt').'"/>';
        $out .= '</div>';
        
        return $out;
    }
    
    /**
     * This function builds the header and the content. 
     * ******** HEADER **********
     * The header comes from $this->header
     * If the header is a split header (more than one row), ($this->has_split_header())
     *      Then the header object is an array of rows. 
     *      Each row is an array of objects. 
     *      Each object has colCount and content as properties
     *      $this->header = array($row0 = array(new stdClass(colCount, content), 
     *      new stdClass(colCount, content)),$row1 = array(...))
     * 
     * Else the header is an array of TH CONTENTS
     * 
     * ************** BODY ***************
     * The body comes from $this->data
     * $this->data is an array or rows
     * $this->data = array(row0, row1, row2.....)
     * Each row is an array or cells. 
     * $row0 = array(cell0, cell1, cell 2)
     * If cell is an object
     *      Then it will take the colspan, content and class properties and create a td with these
     * Else its a string
     *      Its JUST the html content of the TD
     *
     * @return string
     */
    function display_report()
    {
        $out = '<div>';
        $out .= '<table id="resultsTable">';
        $out .= '<thead>';
        if(isset($this->header))
        {
            if(!$this->has_split_header())
            {
                $out .= '<tr>';
                foreach($this->header AS $head)
                {
                    $out .= '<th>'.$head.'</th>';
                }    
                $out .= '</tr>';
            }
            else
            {
                //foreach row
                foreach($this->header AS $row)
                {
                    $out .= '<tr>';
                    foreach($row AS $rowObj)
                    {
                        $columnCount = isset($rowObj->colCount) ? $rowObj->colCount : 1;
                        $columnContent = $rowObj->content;
                        $out .= '<th colspan="'.$columnCount.'">'.$columnContent.'</th>';
                    }
                    $out .= '</tr>';
                }
            } 
        }
        $out .= '</thead>';
        $out .= '<tbody>';
        if(isset($this->data))
        {
            foreach($this->data AS $row)
            {
                $out .= '<tr>';
                foreach($row AS $cell)
                {
                    if(is_a($cell, 'stdClass'))
                    {
                        $out .= $this->build_cell($cell);
                    }
                    else
                    {
                        $out .= '<td>'.$cell.'</td>';
                    }
                }  
                $out .= '</tr>';
            }
        }
        //count the last row
//        $out .= '<tr>';
//        for($i=0;$i<count($row);$i++)
//        {
//            $out .= '<td class="emptyrow"><div class="emptycell">X</div></td>';
//        }
//        $out .= '</tr>';
        $out .= '</tbody>';
        $out .= '</table>';
        $out .= '</div>';
        
        return $out;
    }
    
    protected function get_qual_report_header($qual)
    {
        return Level::get_short_version($qual->levelid).' '.$qual->subtypeshort.' '.$qual->name;
    }
    
    /**
     * Creates a cell for the build report
     * @param type $params
     * @return \stdClass
     */
    protected function create_cell($params = array())
    {
        $cellObj = new stdClass();
        if($params)
        {
            foreach($params AS $name=>$value)
            {
                $cellObj->$name = $value;
            }
        }
        return $cellObj;
    }
    
    /**
     * Creates a cell object 
     * @param type $content
     * @param type $collCount
     * @return \stdClass
     */
    protected function create_header_cell($content, $collCount)
    {
        return $this->create_cell(array('content'=>$content,'colCount'=>$collCount));
    }
    
    /**
     * Creates a blank cell/object for the tables/excell
     * @return \stdClass
     */
    protected function set_up_blank_object()
    {
        return $this->create_cell(array('content'=>'','colCount'=>1));
    }
    
    protected function get_mentees_sql()
    {
        global $DB, $USER;
        $sql = "SELECT u.* FROM {role_assignments} r 
            JOIN {context} c ON c.id = r.contextid 
            JOIN {user} u ON u.id = c.instanceid 
            WHERE r.userid = ? AND c.contentlevel = ? AND r.roleid = ?";
        
        
        $params[] = $USER->id;
        $params[] = CONTEXT_USER;
        //get the setting from the global config. : tutorrole
        $params[] = $this->getRole( \ELBP\PersonalTutor::getPersonalTutorRole() );
    }
        
    protected function build_cell($cellObj = null)
    {
        $retval = '<td';
        if($cellObj && isset($cellObj->class))
        {
            $retval .= ' class="'.$cellObj->class.'"';
        }
        if($cellObj && isset($cellObj->colspan))
        {
            $retval .= ' colspan="'.$cellObj->colspan.'"';
        } 
        $retval .= '>';
        if($cellObj && isset($cellObj->content))
        {
            $retval .= $cellObj->content;
        }
        $retval .= '</td>';
        return $retval;
    }
    
    protected function build_excell_cell($cellObj = null)
    {
        $retval = '';
        if($cellObj && isset($cellObj->content))
        {
            $retval .= $cellObj->content;
        }
        return $retval;
    }
    
    protected function get_excell_cell_color($cellObj = null)
    {
        $retval = array();
        if($cellObj && isset($cellObj->class))
        {
            switch($cellObj->class)
            {
                case "Ahead":
                    $retval['rgb'] = CoreReports::aheadcolor;
                    break;
                case "Behind":
                    $retval['rgb'] = CoreReports::behindcolor;
                    break;
                case "OnTarget":
                    $retval['rgb'] = CoreReports::oncolor;
                    break;
                default:
                    $retval['rgb'] = 'FFFFFF';
                    break;
            }
        }
        return $retval;        
    }    
}

?>
