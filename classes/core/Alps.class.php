<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Alps
 *
 * @author mchaney
 */
class Alps {
    //put your code here
    
    const ENTRIESMULTIPLYER = 50;
    const REPORTGENERATESETTING = "alps_report_generation_date";
    protected $alpsMultiplier = 50;
    
    const EXPORTCOLOR1 = '9E1616';
    const EXPORTCOLORTEXT1 = 'FF8533';
    const EXPORTCOLOR2 = 'B14545';
    const EXPORTCOLORTEXT2 = 'FF8533';
    const EXPORTCOLOR3 = 'C87D7D';
    const EXPORTCOLORTEXT3 = 'A32900';
    const EXPORTCOLOR4 = '999999';
    const EXPORTCOLORTEXT4 = 'B2B2B2';
    const EXPORTCOLOR5 = '000000';
    const EXPORTCOLORTEXT5 = 'B2B2B2';
    const EXPORTCOLOR6 = '999999';
    const EXPORTCOLORTEXT6 = 'B2B2B2';
    const EXPORTCOLOR7 = '8080E6';
    const EXPORTCOLORTEXT7 = '0000B8';
    const EXPORTCOLOR8 = '0000CC';
    const EXPORTCOLORTEXT8 = '6666E0';
    const EXPORTCOLOR9 = '00008F';
    const EXPORTCOLORTEXT9 = '6666E0';
    
    public function Alps()
    {
        
    }
    
    public static function get_description()
    {
        $retval = '<h3>ALPS Overview</h3>';
        $retval .= '<p>Each year an institution can pay for an ALPS report. 
            This report will contain scores and analysis of the institutions results compared with other 
            institutions. Each Subject/Qualification can be given a colour coded score and this score can be used to rank the
            Qualifications, Qualification Families and institution as a whole. ALPS also take into consideration how difficult a Subject/Qualification
            is deemed to be.</p>';
        $retval .= '<h3>Grade Tracker ALPS</h3>';
        $retval .= '<p>ALPS are used in two ways in the Grade Tracker: 
            <ol><li>To calculate average GCSE scores and then target grades (for BTEC and Alevel).</li>
            <li>To calculate Student, Qualification and Qualification Family ALPS temperature ratings based upon last years data.</li></ol></p>';
        
        $retval .= '<h3>Setting up the data</h3>';
        $retval .= '<p>Each Qualification (and each Qualification SubType) has coefficients added against it. These can be found in the ALPS reports. 
            These coefficients correspond to the temperatures of 1 - 9. Please see the admin menu to add these scores in.</p>';
        $retval .= '<h3>Generating Temperature Scores</h3>';
        $retval .= '<p>Within the ALPS reports provided by ALPS, each Qualification family has a calculation that can be used to determine that subject`s coefficient. 
            This usually requires using the following formula:  1 - (UCAS Achieved Points - UCAS Target Points)/(Entries * Qualification Family Constant).</p>';
        $retval .= '<p>This coefficient is then turned into a specific qualification temperature score using the data entered. This calculation can be used to generate the Student, Qualification/Class and the Target Qual/SubType coefficients and scores.</p>';
        $retval .= '<h3>Calculating the other report sections</h3>';
        $retval .= '<p>The following are used to calculate the other sections of the report (other than Subject, Qualification and Target Qual)
            <ul><li>Qualification Family: Averaging the SCORE of the Target Qualifications beneath it</li>
            <li>Course: Averaging all of the SCORES of all of the Students on all Qualifications on that Course</li>
            <li>Course Category: Averaging  all of the SCORES of all of the Students on all Qualifications on all Courses that are beneath that Course Category</li></ul></p>';
        return $retval;
    }
    
    public function set_alps_multiplier($multiplier)
    {
        $this->alpsMultiplier = $multiplier;
    }
    
    public function perform_report_calculations($cronRun = false)
    {
        set_time_limit(0);
        global $DB;
        $sql = "TRUNCATE TABLE {block_bcgt_alps_scores}";
        $DB->execute($sql);
        
        $cronRun ? mtrace("Calculate All Students individual ALPS scores.") : null;
        $this->calculate_individual_stu_alps();
        
        $cronRun ? mtrace("Calculate All Qualification Individual ALPS Scores") : null;
        $this->calculate_qualification_alps(null, true);

        $cronRun ? mtrace("Calculate ALL Project Alps Scores") : null;
        $this->calculate_project_alps();
        
        $cronRun ? mtrace("Calculate ALL Target Qual Alps Scores") : null;
        $this->calculate_target_qual_alps();
        
        $cronRun ? mtrace("Calculate ALL Family Alps Scores") : null;
        $this->calculate_family_alps();
        
        $cronRun ? mtrace("Calculate ALL Course Alps Scores") : null;
        $this->calculate_course_alps();
        
        $cronRun ? mtrace("Calculate ALL Category Alps Scores") : null;
        $this->calculate_category_alps();
        
        //update to say it has been run. 
        $this->update_alps_report_run_date();
        
    }
    
    public function export_alps_report()
    {
        global $CFG;
        set_time_limit(0);
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        global $CFG, $USER;
        $name = preg_replace("/[^a-z 0-9]/i", "", "ALPS REPORT");
        
        $includeProjects = optional_param('includeproj',false,PARAM_BOOL);
        $displayOptions = optional_param('displayoptions','anonymous',PARAM_TEXT);
    
        $this->showCeta = false;
        if(get_config('bcgt', 'aleveluseceta'))
        {
            $this->showCeta = true;
        }
        
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
                     ->setTitle('Alps Reports')
                     ->setSubject('Alps Reports')
                     ->setDescription('Alps Reports');

        // Remove default sheet
        $objPHPExcel->removeSheetByIndex(0);
        
        $sheetIndex = 0;  
        // Set current sheet
        $objPHPExcel->createSheet($sheetIndex);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $objPHPExcel->getActiveSheet()->setTitle("Family");
        
        $rowNum = 1;
        $projects = array();
        if($includeProjects)
        {
            $project = new Project();
            $projects = $project->get_all_projects($centrallyManaged = null);
            if($projects)
            {
                require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ProjectsSorter.class.php');
                $projectSorter = new ProjectsSorter();
                usort($projects, array($projectSorter, "ComparisonDelegateByObjectDueDate"));
            }
        }
        
        $this->get_alps_export_header($objPHPExcel, $includeProjects, $rowNum, get_string('family','block_bcgt'), $projects);
        
        //then we would want to do the data
        $families = $this->get_families_use_alps();
        if($families)
        {
            foreach($families AS $family)
            {
                $rowNum++;
                $colNum = 1;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowNum, $family->family);
                
                $scores = $this->get_family_quals_overall_alps_scores($family->id);                
                $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum);
                if($includeProjects)
                {
                    foreach($projects AS $project)
                    {
                        $scores = $this->get_family_quals_projects_overall_alps_scores($family->id, $project->get_id());
                        $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum);
                    }
                }
            }
        }
        $objPHPExcel->getActiveSheet()->freezePane($frozenPanes);
        //Sheet 2
        //The Target quals
        //Heading: TargetQual, Score, Each Project, Score and Grade
        $sheetIndex++;  
        // Set current sheet
        $objPHPExcel->createSheet($sheetIndex);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $objPHPExcel->getActiveSheet()->setTitle("Target Qual");
        $this->get_alps_export_header($objPHPExcel, $includeProjects, $rowNum, get_string('targetqual','block_bcgt'), $projects);
        
        $targetQuals = $this->get_target_quals_use_alps();
        if($targetQuals)
        {
            foreach($targetQuals AS $targetQual)
            {
                $rowNum++;
                $colNum = 1;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowNum, $targetQual->trackinglevel.' '.$targetQual->subtype);
                
                $scores = $this->get_targetqual_overall_alps_scores($targetQual->id);                
                $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum);
                if($includeProjects)
                {
                    foreach($projects AS $project)
                    {
                        $scores = $this->get_targetqual_project_overall_alps_scores($targetQual->id, $project->get_id());
                        $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum);
                    }
                }
            }
        }
        $objPHPExcel->getActiveSheet()->freezePane($frozenPanes);
        
        
        //Sheet 3,
        //The Quals
        //Heading: Qual, Score, Each Project, Score and Grade
        $sheetIndex++;  
        // Set current sheet
        $objPHPExcel->createSheet($sheetIndex);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $objPHPExcel->getActiveSheet()->setTitle("Qual");
        $this->get_alps_export_header($objPHPExcel, $includeProjects, $rowNum, get_string('qual','block_bcgt'), $projects);
        
        $quals = $this->get_quals_use_alps();
        if($quals)
        {
            foreach($quals AS $qual)
            {
                $rowNum++;
                $colNum = 1;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowNum, bcgt_get_qualification_display_name($qual));
                
                $scores = $this->get_qual_overall_alps_scores($qual->id);                
                $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum);
                if($includeProjects)
                {
                    foreach($projects AS $project)
                    {
                        $scores = $this->get_qual_project_overall_alps_scores($qual->id, $project->get_id());
                        $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum);
                    }
                }
            }
        }
        $objPHPExcel->getActiveSheet()->freezePane($frozenPanes);
        
        
        //Sheet 4,
        //The Students
        //Heading: Student, Each Qual, Score, Each Project, Score and Grade
        
        //OR
        
        //Heading: Student, Qual1, With Qual in It, Score, Each Project
        $sheetIndex++;  
        // Set current sheet
        $objPHPExcel->createSheet($sheetIndex);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $objPHPExcel->getActiveSheet()->setTitle("Student");
        $quals = $this->get_quals_use_alps();
        $students = bcgt_search_users_report(null, array('student'));
        $currentMaxQuals = 5;
        $rowNum = 1;
        if($displayOptions && ($displayOptions == 'anonymous' || $displayOptions == 'byoption'))
        {
            if($students)
            {
                foreach($students AS $student)
                {
                    //need to know max number of quals
                    $quals = $this->get_users_quals_alps_scores($student->id);
                    if($quals)
                    {
                        //has this student got a greater number of maximum quals?
                        $currentMaxQuals = (count($quals) > $currentMaxQuals)? count($quals) : $currentMaxQuals;
                    }
                }
            }
        }
        
        $this->get_alps_export_header($objPHPExcel, $includeProjects, $rowNum, get_string('username'), $projects, $displayOptions, $currentMaxQuals);

        if($students)
        {
            foreach($students AS $student)
            {
                $rowNum++;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowNum, $student->username);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowNum, $student->lastname);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowNum, $student->firstname);

                if($displayOptions && $displayOptions == 'bysubject')
                {
                    //then we need to get all quals
                    if($quals)
                    {
                        $colNum = 3;
                        foreach($quals AS $qual)
                        {
                            //do they have a score?
                            //overall
                            $scores = $this->get_alps_user_overall_scores($student->id, $qual->id);
                            $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum, false);
                            if($includeProjects)
                            {
                                foreach($projects AS $project)
                                {
                                    $scores = $this->get_alps_scores($student->id, $qual->id, $project->get_id());
                                    $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum, false);
                                }
                            }
                        }
                    }
                }
                elseif($displayOptions && ($displayOptions == 'anonymous' || $displayOptions == 'byoption'))
                {
                    //then find the users quals. 
                    $quals = $this->get_users_quals_alps_scores($student->id);
                    if($quals)
                    {
                        $colNum = 3;
                        //has this student got a greater number of maximum quals?
                        foreach($quals AS $qual)
                        {
                            if($displayOptions == 'byoption')
                            {
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colNum, $rowNum, bcgt_get_qualification_display_name($qual));
                                $colNum++;
                            }
                            //now get the overall ceta and grade
                            $scores = $this->get_alps_user_overall_scores($student->id, $qual->id);
                            $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum);
                            if($includeProjects)
                            {
                                foreach($projects AS $project)
                                {
                                    $scores = $this->get_alps_scores($student->id, $qual->id, $project->get_id());
                                    $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum);
                                }
                            }
                        }
                    }
                }
                elseif($includeProjects)
                {
                    foreach($projects AS $project)
                    {
                        $scores = $this->get_qual_project_overall_alps_scores($qual->id, $project->get_id());
                        $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum);
                    }
                }
            }
        }
        
//        $objPHPExcel->getActiveSheet()->freezePane($frozenPanes);
        
        
        //Sheet 5 Categories
        //Heading: Category, Score, Each Project, Score and Grade
        $sheetIndex++;  
        $rowNum = 1;
        // Set current sheet
        $objPHPExcel->createSheet($sheetIndex);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $objPHPExcel->getActiveSheet()->setTitle("Categories");
        $this->get_alps_export_header($objPHPExcel, $includeProjects, $rowNum, 'Categories', $projects);
        //get the top level categories
        require_once($CFG->dirroot.'/lib/coursecatlib.php');
        $topCategories = coursecat::get(0)->get_children();
        foreach($topCategories AS $category)
        {
            $rowNum++;
            $categoryID = $category->__get('id');
            $categoryName = $category->__get('name');
            $this->get_alps_report_category_export_rows($objPHPExcel, $rowNum, 
                    $includeProjects, $projects, $categoryID, $categoryName);
        }

        $objPHPExcel->getActiveSheet()->freezePane($frozenPanes);
        
        //Sheet 6,
        //Heading: Course, Score, Each Project, Score and Grade

        // Freeze rows and cols (everything to the left of D and above 2)
        $sheetIndex++;  
        // Set current sheet
        $objPHPExcel->createSheet($sheetIndex);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $objPHPExcel->getActiveSheet()->setTitle("Course");
        $this->get_alps_export_header($objPHPExcel, $includeProjects, $rowNum, 'Course', $projects);
        
        $courses = $this->get_courses_use_alps();
        if($courses)
        {
            foreach($courses AS $course)
            {
                $rowNum++;
                $colNum = 1;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowNum, $course->fullname);
                
                $scores = $this->get_course_overall_alps_scores($course->id);                
                $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum);
                if($includeProjects)
                {
                    foreach($projects AS $project)
                    {
                        $scores = $this->get_course_project_overall_alps_scores($course->id, $project->get_id());
                        $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum);
                    }
                }
            }
        }
        $objPHPExcel->getActiveSheet()->freezePane($frozenPanes);
        
        // End
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        ob_clean();
        $objWriter->save('php://output');
        
        exit;
    }
    
    protected function get_alps_report_category_export_rows(&$objPHPExcel, &$rowNum, 
            $includeProjects, $projects, $categoryID, $categoryName)
    {
        //get the category name
        //get the 
        $colNum = 0;
        $scores = $this->get_category_overall_alps_scores($categoryID);
        if($scores)
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colNum, $rowNum, $categoryName);
            $colNum++;
            $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum);
            
            //now for projects
            if($includeProjects)
            {
                foreach($projects AS $project)
                {
                    $scores = $this->get_category_project_overall_alps_scores($categoryID, $project->get_id());
                    $this->get_alps_report_export_rows($objPHPExcel, $scores, $colNum, $rowNum);
                }
            }
            
            //now get sub categories. 
            $subCategories = bcgt_get_sub_categories($categoryID);
            if($subCategories)
            {
                foreach($subCategories AS $subCategory)
                {
                    $rowNum++;
                    $this->get_alps_report_category_export_rows($objPHPExcel, $rowNum, 
                            $includeProjects, $projects, $subCategory->id, $subCategory->name);
                }
            }
        }
        
    }
    
    protected function get_alps_export_header(&$objPHPExcel, $includeProjects, &$rowNum, $colName, 
            $projects, $displayOptions = null, $currentMaxQuals = 0)
    {
        //Are we showing the projects?
        $frozenPanes = 'A1';
        //sheet 1, 
        //The family
        //Heading: Family, Score, Each Project, Score and Grade
        $rowNum = 1;
        if($includeProjects)
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowNum, '');
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowNum, '');
            if($displayOptions)
            {
                //one for the firstname
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowNum, '');
            }
            $rowNum++;
        }
        if($displayOptions)
        {
            //one for the username, lastname and firstname
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowNum, '');
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowNum, '');
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowNum, '');
            $rowNum++;
        }
        //NOW THE FIRST COLUMN NAME
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowNum, $colName);
        if(!$displayOptions)
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowNum, get_string('grade','block_bcgt'));
            if($this->showCeta)
            {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowNum, get_string('ceta','block_bcgt'));
            }
        }
        else
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowNum, get_string('lastname'));
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowNum, get_string('firstname'));
        }
        
        
        if($displayOptions && $displayOptions == 'bysubject')
        {
            //then we are outputting each project for each qual
            //get quals
            $quals = $this->get_quals_use_alps();
            if($quals)
            {
                $multiplier = 1;
                $collNum = 3;
                if($this->showCeta)
                {
                    $multiplier = 2;
                }
                $projectCount = (count($projects) * $multiplier);
                $projectCount++;//one for the overall qual grade
                if($this->showCeta)
                {
                    $projectCount++;//one for the overall qual ceta
                }
                $colSubNum = $collNum;
                foreach($quals AS $qual)
                {
                    $startCollNum = $collNum;
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($collNum, 1, bcgt_get_qualification_display_name($qual));
                    
                    $collNum++;
                    if($includeProjects)
                    {
                        for($i=0;$i<($projectCount - 1);$i++)
                        {
                            //its -1 because we already have one space allocated for the name
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($collNum, 1, '');
                            $collNum++;
                        }
                        $objPHPExcel->getActiveSheet()->mergeCells(''.PHPExcel_Cell::stringFromColumnIndex($startCollNum).
                        '1:'.PHPExcel_Cell::stringFromColumnIndex(($collNum - 2)).'1');//-2 as we have come one project too far, so we need to go back a project to merge
                    }
                    else
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($collNum, 1, ''); //overall grade
                        $collNum++;
                        if($this->showCeta)
                        {
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($collNum, 1, ''); //overall ceta
                            $collNum++;
                            $objPHPExcel->getActiveSheet()->mergeCells(''.PHPExcel_Cell::stringFromColumnIndex($startCollNum).
                            '1:'.PHPExcel_Cell::stringFromColumnIndex(($collNum - 1)).'1');//
                        }
                        
                    }
                    
                    //now add the two cells, one for overall ceta, one for overall grade
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colSubNum, 2, get_string('qual','block_bcgt').' '.get_string('grade','block_bcgt'));
                    $colSubNum++;
                    if($includeProjects)
                    {
                        //blank
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colSubNum, 3, '');
                    }
                    if($this->showCeta)
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colSubNum, 2, get_string('qual','block_bcgt').' '.get_string('ceta','block_bcgt'));
                        $colSubNum++;
                        if($includeProjects)
                        {
                            //blank
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colSubNum, 3, '');
                        }
                    }
                    foreach($projects AS $project)
                    {
                        $this->get_alps_report_export_header($objPHPExcel,$colSubNum, $project->get_name(), 2);
                    }
                }
            }
        }
        elseif($displayOptions && ($displayOptions == 'byoption' || $displayOptions == 'anonymous'))
        {
            //then we want SUBJECT A, SUBJECT B etc
            //if its byoption, an extra one for the qual name
            //then for each its grade and ceta
            //then the projects
            $letter = 'A';
            $collNum = 3;
            $multiplier = 1;
            if($this->showCeta)
            {
                $multiplier = 2;
            }
            $colSubNum = $collNum;
            $projectCount = (count($projects) * $multiplier);
            $projectCount++;//one for the overall qual grade
            if($this->showCeta)
            {
                $projectCount++;//one for the overall qual ceta
            }
            for($i=0;$i<$currentMaxQuals;$i++)
            {
                $startCollNum = $collNum;
                if($displayOptions == 'byoption')
                {
                    $projectCount++;//one for the qual name
                }
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($collNum, 1, 'Subject'.$letter++);

                $collNum++;
                if($includeProjects)
                {
                    for($k=0;$k<($projectCount - 1);$k++)
                    {
                        //its -1 because we already have one space allocated for the name
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($collNum, 1, '');
                        $collNum++;
                    }
                    $objPHPExcel->getActiveSheet()->mergeCells(''.PHPExcel_Cell::stringFromColumnIndex($startCollNum).
                    '1:'.PHPExcel_Cell::stringFromColumnIndex(($collNum - 1)).'1');//-1 as we have come one project too far, so we need to go back a project to merge
                }
                else
                {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($collNum, 1, ''); //overall grade
                    $collNum++;
                    if($this->showCeta)
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($collNum, 1, ''); //overall ceta
                        $collNum++;
                        $objPHPExcel->getActiveSheet()->mergeCells(''.PHPExcel_Cell::stringFromColumnIndex($startCollNum).
                    '1:'.PHPExcel_Cell::stringFromColumnIndex(($collNum - 1)).'1');//
                    }
                    
                }
                
                //now the sub menu
                if($displayOptions == 'byoption')
                {
                    //one for the qual name
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colSubNum, 2, get_string('qual','block_bcgt'));
                    $colSubNum++;
                    if($includeProjects)
                    {
                        //blank
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colSubNum, 3, '');
                    }
                }
                //now add the two cells, one for overall ceta, one for overall grade
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colSubNum, 2, get_string('qual','block_bcgt').' '.get_string('grade','block_bcgt'));
                $colSubNum++;
                if($includeProjects)
                {
                    //blank
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colSubNum, 3, '');
                }
                if($this->showCeta)
                {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colSubNum, 2, get_string('qual','block_bcgt').' '.get_string('ceta','block_bcgt'));
                    $colSubNum++;
                    if($includeProjects)
                    {
                        //blank
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colSubNum, 3, '');
                    }
                }
                foreach($projects AS $project)
                {
                    $this->get_alps_report_export_header($objPHPExcel,$colSubNum, $project->get_name(), 2);
                }
            }
        }
        elseif($includeProjects)
        {
            $collNum = 2;
            if($this->showCeta)
            {
                $collNum = 3;
            }
            $frozenPanes = 'B1';
            //then get the projects
            foreach($projects AS $project)
            {
                $this->get_alps_report_export_header($objPHPExcel,$collNum, $project->get_name());
            }
        }
    }
    
    protected function get_alps_report_export_header(&$objPHPExcel, &$collNum, $projectName, $rowNum = 1)
    {
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($collNum, $rowNum, $projectName);
        if($this->showCeta)
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($collNum + 1), $rowNum, '');
            $objPHPExcel->getActiveSheet()->mergeCells(''.PHPExcel_Cell::stringFromColumnIndex($collNum).
                        $rowNum.':'.PHPExcel_Cell::stringFromColumnIndex($collNum + 1).$rowNum);
        }
        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($collNum, ($rowNum + 1), get_string('grade','block_bcgt'));
        if($this->showCeta)
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($collNum + 1), ($rowNum + 1), get_string('ceta','block_bcgt'));
            $collNum++;
        }
        $collNum++;
    }
    
    protected function get_alps_report_export_rows(&$objPHPExcel, $scores, &$colNum, &$rowNum, $addStyling = true)
    {
        
        $latestCeta = '';
        $latestGrade = '';
        if($scores)
        {
            $score = end($scores);
            $latestCeta = number_format((double)$score->cetascore);
            $latestGrade = number_format((double)$score->gradescore);
        }
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colNum, $rowNum, $latestGrade);
        //need to set the color
        if($addStyling)
        {
            $objPHPExcel->getActiveSheet()->getStyle(''.PHPExcel_Cell::stringFromColumnIndex($colNum).''.$rowNum)->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => $this->get_export_cell_color($latestGrade)
                    ),
    //                'font'  => array(
    //                    'bold'  => true,
    //                    'color' => $this->get_export_font_color($latestGrade)
    //                ),
                    'borders' => array(
                        'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('rgb'=>'cfcfcf')
                        )
                    )
                )
            );
        }
        
        $colNum++;
        if($this->showCeta)
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colNum, $rowNum, $latestCeta);
            if($addStyling)
            {
                $objPHPExcel->getActiveSheet()->getStyle(''.PHPExcel_Cell::stringFromColumnIndex($colNum).''.$rowNum)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => $this->get_export_cell_color($latestCeta)
                        ),
                        'font'  => array(
                            'bold'  => true,
                            'color' => $this->get_export_font_color($latestCeta)
                        ),
                        'borders' => array(
                            'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb'=>'cfcfcf')
                            )
                        )
                    )
                );
            }
            
            $colNum++;
        }
    }
    
    protected function get_export_cell_color($score)
    {
        $constantColor = 'EXPORTCOLOR'.$score;
        $retval = array();
        $retval['rgb'] = 'FFFFFF';
        if($score && $score != '' && $score > 0)
        {
            if(constant('ALPS::'.$constantColor))
            {
                $retval['rgb'] = constant('ALPS::'. $constantColor);
            }
            
        }
        return $retval;
    }
    
    protected function get_export_font_color($score)
    {
        $constant = 'EXPORTCOLORTEXT'.$score;
        $retval = array();
        $retval['rgb'] = '000000';
        if($score && $score != '' && $score > 0)
        {
            if(constant('ALPS::'. $constant))
            {
                $retval['rgb'] = constant('ALPS::'. $constant);
            }
        }
        return $retval;
    }
    
    
    public function get_alps_report_rows($type, $value, $courseID)
    {
        global $CFG;
        $projects = Project::get_all_projects();
        if($projects)
        {
            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ProjectsSorter.class.php');
            $projectSorter = new ProjectsSorter();
            usort($projects, array($projectSorter, "ComparisonDelegateByObjectDueDate"));
        }
        $showCeta = false;
        $this->showCeta = false;
        if(get_config('bcgt', 'aleveluseceta'))
        {
            $showCeta = true;
            $this->showCeta = true;
        }
        $count = 0;
        $retval = '';
        $newAttr = '';
        $content = '';
        switch($type)
        {
            case "fam":
                $newAttr = 'fam="'.$value.'"';
                //get all of the targetquals
                $targetQuals = $this->get_target_quals_use_alps($value);
                foreach($targetQuals AS $targetQual)
                {
                    $rem = 'targetqual_'.
                            $targetQual->id.'';
                    $count++;
                    $content .= '<tr class="targetqualrem" '.$newAttr.' rem="'.$rem.'" id="e_targetqual_'.
                            $targetQual->id.'">';
                    $content .= '<td><span class="alpsreport expand" val="'.$targetQual->id.'" type="targetqual">&nbsp;&nbsp;'.
                            $targetQual->trackinglevel.' '.$targetQual->subtype.'</span></td>';
                    //get the latest ceta
                    $scores = $this->get_targetqual_overall_alps_scores($targetQual->id);
                    $content .= Alps::get_scores_reports_display($scores, $showCeta, true);
                    
                    //then the projects. 
                    foreach($projects AS $project)
                    {
                        //need to get the corresponding grade and ceta
                        $scores = $this->get_targetqual_project_overall_alps_scores($targetQual->id, $project->get_id());
                        $content .= Alps::get_scores_reports_display($scores, $showCeta, true);
                    }
                    $retval .= '</tr>';
                }
                break;
            case "targetqual":
                $newAttr = 'targetqual="'.$value.'"';
                //get all of the quals under the target qual
                $quals = $this->get_quals_use_alps($value);
                foreach($quals AS $qual)
                {
                    $rem = 'qual_'.
                            $qual->id;
                    $scores = $this->get_qual_overall_alps_scores($qual->id);
                    if($scores)
                    {
                        $count++;
                        $content .= '<tr class="qualrem" '.$newAttr.' targetqual="'.$value.'" rem="'.$rem.'" id="e_qual_'.
                                $qual->id.'">';
                        $content .= '<td><span class="alpsreport expand" val="'.$qual->id.'" type="qual">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                                $qual->name.'</span></td>';
                        //get the latest ceta

                        $content .= Alps::get_scores_reports_display($scores, $showCeta, true);

                        //then the projects. 
                        foreach($projects AS $project)
                        {
                            //need to get the corresponding grade and ceta
                            $scores = $this->get_qual_project_overall_alps_scores($qual->id, $project->get_id());
                            $content .= Alps::get_scores_reports_display($scores, $showCeta, true);
                        }
                        $content .= '</tr>';
                    }
                    
                }
                break;
            case "qual":
                //get all of the students under the quals
                $newAttr = 'qual="'.$value.'"';
                //get all of the quals under the target qual
                $loadParams = new stdClass();
                $loadParams->loadLevel = Qualification::LOADLEVELALL;
                $qualification = Qualification::get_qualification_class_id($value, $loadParams);
                if($qualification)
                {
                    $students = $qualification->get_students();
                    if($students)
                    {
                        $count = 0;
                        foreach($students AS $student)
                        {
                            $rem = 'stu_'.
                            $student->id;
                            $scores = $this->get_alps_user_overall_scores($student->id, $value);
                            if($scores)
                            {
                                $count++;
                                $content .= '<tr class="sturem" '.$newAttr.' rem="'.$rem.'" id="e_stu_'.
                                        $student->id.'">';
                                $content .= '<td><span class="alpsreport" val="'.$student->id.'" type="stu">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                                        '<a target="_blank" href="'.$CFG->wwwroot.'/blocks/bcgt/grids/student_grid.php?qID='.
                                        $value.'&sID='.$student->id.'">'.$student->firstname.' '.
                                        $student->lastname.'</a></span></td>';
                                //get the latest ceta

                                $content .= Alps::get_scores_reports_display($scores, $showCeta, true);

                                //then the projects. 
                                foreach($projects AS $project)
                                {
                                    //need to get the corresponding grade and ceta
                                    $scores = $this->get_alps_scores($student->id, $value, $project->get_id());
                                    $content .= Alps::get_scores_reports_display($scores, $showCeta, true);
                                }
                                $content .= '</tr>';
                            }
                        }
                    }
                }
                break;
            case "cat":
                //get all of the sub categories OR courses under this category
                //so, does it have sub categories?
                $subCategories = bcgt_get_sub_categories($value);
                if($subCategories)
                {
                    $newAttr = 'cat="'.$value.'"';
                    $count = 0;
                    foreach($subCategories AS $category)
                    {
                        $count++;
                        $content .= $this->get_category_row($category->id, $count, $category->name, $projects, $newAttr, $showCeta);
                    }
                }
                else
                {
                    $newAttr = 'cat="'.$value.'"';
                    //get the courses from this category
                    $courses = bcgt_get_category_courses($value);
                    if($courses)
                    {
                        foreach($courses AS $course)
                        {
                            $rem = 'course_'.
                            $course->id;
                            $scores = $this->get_course_overall_alps_scores($course->id);
                            if($scores)
                            {
                                $count++;
                                $content .= '<tr class="courserem" '.$newAttr.' rem="'.$rem.'" id="e_course_'.
                                        $course->id.'">';
                                $content .= '<td><span class="alpsreport expand" val="'.$course->id.'" type="course">&nbsp;&nbsp;&nbsp;&nbsp;'.
                                        $course->fullname.'</span></td>';
                                //get the latest ceta

                                $content .= Alps::get_scores_reports_display($scores, $showCeta, true);

                                //then the projects. 
                                foreach($projects AS $project)
                                {
                                    //need to get the corresponding grade and ceta
                                    $scores = $this->get_course_project_overall_alps_scores($course->id, $project->get_id());
                                    $content .= Alps::get_scores_reports_display($scores, $showCeta, true);
                                }
                                $content .= '</tr>';
                            }
                        }
                    }
                }
                break;
            case "course":
                $newAttr = 'course="'.$value.'"';
                //get all of the quals under the course
                $quals = $this->get_quals_use_alps(null, $value);
                foreach($quals AS $qual)
                {
                    $rem = 'qual_'.
                            $qual->id;
                    $scores = $this->get_qual_overall_alps_scores($qual->id);
                    if($scores)
                    {
                        $count++;
                        $content .= '<tr class="qualrem" '.$newAttr.' rem="'.$rem.'" id="e_qual_'.
                                $qual->id.'">';
                        $content .= '<td><span class="alpsreport expand" val="'.$qual->id.'" type="qual">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                                $qual->name.'</span></td>';
                        //get the latest ceta

                        $content .= Alps::get_scores_reports_display($scores, $showCeta, true);

                        //then the projects. 
                        foreach($projects AS $project)
                        {
                            //need to get the corresponding grade and ceta
                            $scores = $this->get_qual_project_overall_alps_scores($qual->id, $project->get_id());
                            $content .= Alps::get_scores_reports_display($scores, $showCeta, true);
                        }
                        $content .= '</tr>';
                    }
                    
                }
                break;
        }
        $retval .= $this->add_blank_row(count($projects),'spacer',$newAttr);
        $retval .= $content;
        $retval .= $this->add_blank_row(count($projects),'end',$newAttr);
        
        return $retval;
    }
    
    public function get_category_row($categoryID, $count, $categoryName, $projects, $rem, $showCeta)
    {
        $retval = '';
        //now to get the scores
        $scores = $this->get_category_overall_alps_scores($categoryID);
        if($scores)
        {
            $retval = '<tr class="subcatrem" '.$rem.' rem="cat_'.$categoryID.'" id="e_cat_'.$categoryID.'">';
            $retval .= '<td>';
            $retval .= '<span class="alpsreport expand"  val="'.$categoryID.'" type="cat">&nbsp;&nbsp;';
            $retval .= $categoryName;
            $retval .= '</span></td>';
            $retval .= Alps::get_scores_reports_display($scores, $showCeta, true);

            foreach($projects AS $project)
            {
                $scores = $this->get_category_project_overall_alps_scores($categoryID, $project->get_id());
                $retval .= Alps::get_scores_reports_display($scores, $showCeta, true);
            }
            $retval .= '</tr>';
        }
        return $retval;
    }
    
    public static function get_scores_reports_display($scores, $showCeta, $showFa)       
    {
        $retval = '';
        $latestCetaClass = '';
        $latestGradeClass = '';  
        $latestCeta = '<span class="noscore">?</span>';
        $latestGrade = '<span class="noscore">?</span>';
        if($scores)
        {
            $score = end($scores);
            $latestCetaClass = number_format((double)$score->cetascore);
            $latestGradeClass = number_format((double)$score->gradescore);
            $latestCeta = $score->cetascore ? number_format((double)$score->cetascore) : '<span class="noscore">?</span>';
            $latestGrade = $score->gradescore ? number_format((double)$score->gradescore) : '<span class="noscore">?</span>';
        }
        if($showFa)
        {
            $retval .= '<td class="alpstemp grade alpstemp'.$latestGradeClass.'">'.$latestGrade.'</td>';
        }
        if($showCeta)
        {
            $retval .= '<td class="alpstemp ceta alpstemp'.$latestCetaClass.'">'.$latestCeta.'</td>';
        }
        return $retval;
    }
    
    protected function add_blank_row($projCount, $extraClass, $type)
    {
        $retval = '<tr class="blankrow '.$extraClass.'" '.$type.'>';
        $retval .= '<td></td><td></td>';
        if($this->showCeta)
        {
            $retval .= '<td></td>';
        }
        for($i=0;$i<$projCount;$i++)
        {
            $retval .= '<td></td>';
            if($this->showCeta)
            {
                $retval .= '<td></td>';
            }
        }
        $retval .= '</tr>';
        
        return $retval;
    }
    
    public function calculate_class_alps_report($ucasTarget, $ucasAchieved, $qualID, $noEntries, $showCoefficient = false)
    {
        $coefficientScore = $this->calculate_alps_score($ucasTarget, $ucasAchieved, $noEntries);
        //get the weightings:
        $qualWeighting = new QualWeighting();
        $qualWeightingRecord = $qualWeighting->get_alps_temperature($qualID, $coefficientScore);
        if($qualWeightingRecord)
        {
            if($showCoefficient)
            {
                $stdObj = new stdClass();
                $stdObj->number = $qualWeightingRecord->number;
                $stdObj->score = $coefficientScore;
                
                return $stdObj;
            }
            else
            {
                return $qualWeightingRecord->number;
            }
        }
        return -1;
    }
    
    public function calculate_targetqual_alps_report($ucasTarget, $ucasAchieved, $targetQualID, $noEntries, $showCoefficient = false)
    {
        $coefficientScore = $this->calculate_alps_score($ucasTarget, $ucasAchieved, $noEntries);
        //get the weightings:
        $qualWeighting = new TargetQualWeighting();
        $qualWeightingRecord = $qualWeighting->get_alps_temperature($targetQualID, $coefficientScore);
        if($qualWeightingRecord)
        {
            if($showCoefficient)
            {
                $stdObj = new stdClass();
                $stdObj->number = $qualWeightingRecord->number;
                $stdObj->score = $coefficientScore;
                
                return $stdObj;
            }
            else
            {
                return $qualWeightingRecord->number;
            }
        }
        return -1;
    }
    
    public function calculate_students_alps_report($ucasTarget, $ucasAchieved, $qualID, $showCoefficient = false)
    {
        $coefficientScore = $this->calculate_alps_score($ucasTarget, $ucasAchieved, 1);
        //get the weightings:
        $qualWeighting = new QualWeighting();
        $qualWeightingRecord = $qualWeighting->get_alps_temperature($qualID, $coefficientScore);
        if($qualWeightingRecord)
        {
            if($showCoefficient)
            {
                $stdObj = new stdClass();
                $stdObj->number = $qualWeightingRecord->number;
                $stdObj->score = $coefficientScore;
                
                return $stdObj;
            }
            else
            {
                return $qualWeightingRecord->number;
            }
        }
        elseif($coefficientScore)
        {
            //so we have a coefficient score, but no score. 
            //this means it must be either a 1 or a 9?
            if($coefficientScore > 1)
            {
                //then its a 1
                $score = 1;
            }
            else
            {
                //then its a 9
                $score = 9;
            } 
            
            if($showCoefficient)
            {
                $stdObj = new stdClass();
                $stdObj->number = $score;
                $stdObj->score = $coefficientScore;
                
                return $stdObj;
            }
            else
            {
                return $score;
            }
            
        }
        return -1;
    }
    
    public function calculate_alps_score($ucasTarget, $ucasAchieved, $noEntries)
    {
        //The alps entrymultipyer is dependant on the Qualification
        return 1 + (($ucasAchieved - $ucasTarget)/($this->alpsMultiplier * $noEntries));
    }
    
    public function get_quals_use_alps($targetQualID = null, $courseID = null)
    {
        global $DB;
        $params = array();
        $sql = "SELECT distinct(qual.id), ";
        $sql .= bcgt_get_qualification_details_fields_for_sql();
        $sql .= " FROM {block_bcgt_qualification} qual 
            JOIN {block_bcgt_qual_weighting} weighting ON weighting.bcgtqualificationid = qual.id ";
        $sql .= bcgt_get_qualification_details_join_for_sql();
        if($targetQualID)
        {
            $sql .= " WHERE qual.bcgttargetqualid = ?";
            $params[] = $targetQualID;
        }
        elseif($courseID)
        {
            $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id 
                WHERE coursequal.courseid = ?";
            $params[] = $courseID;
        }
        $sql .= ' ORDER BY family.family ASC, trackinglevel ASC, subtype ASC, qual.name ASC';
        return $DB->get_records_sql($sql, $params);
    }
    
    public function get_users_quals_alps_scores($userID)
    {
        global $DB;
        $sql = "SELECT distinct(qual.id), ";
        $sql .= bcgt_get_qualification_details_fields_for_sql();
        $sql .= " FROM {block_bcgt_qualification} qual 
            JOIN {block_bcgt_qual_weighting} weighting ON weighting.bcgtqualificationid = qual.id ";
        $sql .= bcgt_get_qualification_details_join_for_sql();
        $sql .= " JOIN {block_bcgt_alps_scores} scores ON scores.bcgtqualificationid = qual.id 
            WHERE scores.userid = ?";
        return $DB->get_records_sql($sql, array($userID));
        
    }
    
    public function get_projects_use_alps()
    {
        global $DB;
        $sql = "SELECT distinct(project.id) FROM {block_bcgt_project} project 
            JOIN {block_bcgt_activity_refs} refs ON refs.bcgtprojectid = project.id 
            JOIN {block_bcgt_qualification} qual ON qual.id = refs.bcgtqualificationid
            JOIN {block_bcgt_qual_weighting} weighting ON weighting.bcgtqualificationid = qual.id AND refs.coursemoduleid IS NULL";
        return $DB->get_records_sql($sql, array());
    }
    
    public function get_target_quals_use_alps($familyID = null)
    {
        global $DB;
        $params = array();
        $sql = "SELECT distinct(targetqual.id), subtype.subtype, level.trackinglevel, 
            type.id as typeid, type.bcgttypefamilyid AS familyid 
            FROM {block_bcgt_target_qual} targetqual 
            JOIN {block_bcgt_qualification} qual ON targetqual.id = qual.bcgttargetqualid
            JOIN {block_bcgt_qual_weighting} weighting ON weighting.bcgtqualificationid = qual.id 
            JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
            JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid
            ";
        if($familyID)
        {
            $sql .= "  
                WHERE type.bcgttypefamilyid = ?";
            $params[] = $familyID;
        }
        $sql .=  'ORDER BY familyid DESC, trackinglevel ASC, subtype ASC';
        return $DB->get_records_sql($sql, $params);
    }
    
    public function get_families_use_alps()
    {
        global $DB;
        $sql = "SELECT distinct(family.id), family.family FROM {block_bcgt_target_qual} targetqual 
            JOIN {block_bcgt_qualification} qual ON targetqual.id = qual.bcgttargetqualid
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid
            JOIN {block_bcgt_qual_weighting} weighting ON weighting.bcgtqualificationid = qual.id";
        return $DB->get_records_sql($sql, array());
    }
    
    public function get_courses_use_alps()
    {
        global $DB;
        $sql = "SELECT distinct(course.id), course.fullname FROM {course} course
            JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = course.id
            JOIN {block_bcgt_qualification} qual ON coursequal.bcgtqualificationid = qual.id
            JOIN {block_bcgt_qual_weighting} weighting ON weighting.bcgtqualificationid = qual.id";
        return $DB->get_records_sql($sql, array());
    }
    
    public function calculate_qualification_alps($quals = null, $pullFromDB = true)
    {
        if(!$quals)
        {
            $quals = $this->get_quals_use_alps();
        }
        if($pullFromDB)
        {
            //get it from the DB
            foreach($quals AS $qual)
            {
                //all students, qualid
                $scores = $this->get_alps_qual_user_scores($qual->id, null);
                if($scores)
                {
                    $stdObject = $this->average_alps_scores($qual->id, $scores);
                    $records = $this->get_qual_overall_alps_scores($qual->id);
                    if($records)
                    {
                        $record = end($records);
                        $stdObject->id = $record->id;
                        $this->update_alps_scores($stdObject);
                    }
                    else
                    {
                        $this->insert_alps_scores($stdObject);
                    }
                }
                else
                {
                    //do we want to delete them?
                    //probably yes
                    $records = $this->get_qual_overall_alps_scores($qual->id);
                    if($records)
                    {
                        $this->delete_qual_overall_alps($qual->id);
                    }
                }
                
                
                //now get all of the formal assessments
                $projects = Project::get_qual_assessments($qual->id);
                if($projects)
                {
                    foreach($projects AS $project)
                    {
                        //get the points scores per student per qual
                        $scores = $this->get_alps_qual_user_scores($qual->id, $project->get_id());
                        if($scores)
                        {
                            $stdObject = $this->average_alps_scores($qual->id, $scores);
                            $stdObject->bcgtprojectid = $project->get_id();
                            $records = $this->get_qual_project_overall_alps_scores($qual->id, $project->get_id());
                            if($records)
                            {
                                $record = end($records);
                                $stdObject->id = $record->id;
                                $this->update_alps_scores($stdObject);
                            }
                            else
                            {
                                $this->insert_alps_scores($stdObject);
                            }
                        }
                        else
                        {
                            //do we want to delete them?
                            //probably yes
                            $records = $this->get_qual_project_overall_alps_scores($qual->id, $project->get_id());
                            if($records)
                            {
                                $this->delete_qual_project_overall_alps($qual->id);
                            }
                        }
                    }
                }
            }
        }
        else
        {
            //we are calculating them again
            //get_class_alps_temp
        }
    }
    
    public function calculate_project_alps()
    {
        //need to get all of the projects that have quals on them, that can have weightings
        $projects = $this->get_projects_use_alps();
        if($projects)
        {
            foreach($projects AS $project)
            {
                //need to get the overall qualification coeficients for each project
                $scores = $this->get_project_quals_overall_alps_scores($project->id);
                if($scores)
                {
                    $stdObject = $this->average_alps_scores(null, $scores);                    
                    $stdObject->bcgtprojectid = $project->id;
                    $records = $this->get_project_overall_alps_scores($project->id);
                    if($records)
                    {
                        $record = end($records);
                        $stdObject->id = $record->id;
                        $this->update_alps_scores($stdObject);
                    }
                    else
                    {
                        $this->insert_alps_scores($stdObject);
                    }                    
                }
                else
                {
                    //do we want to delete them?
                    //probably yes
                    $records = $this->get_project_overall_alps_scores($project->get_id());
                    if($records)
                    {
                        $this->delete_project_overall_alps($project->get_id());
                    }
                }
            }
        }
    }
    
    public function calculate_target_qual_ceta_alps_score($targetQualID, $showCoefficient)       
    {
        //need to get all of the quals under this target qual
        //then for each of those get the students
        //then get the target grade ucas and the ceta or grade ucas. 
        //this is totaled up and then compared to the TARGET QUAL weightings in
        //the table tqual_weight
        $userCount = 0;
        $totalCetaUcas = 0;
        $totalTargetUcas = 0;
        
        $quals = bcgt_get_quals_by_target_qual($targetQualID);
        if($quals)
        {
            foreach($quals AS $qual)
            {
                $qualification = Qualification::get_qualification_class_id($qual->id);
                $usersUcas = $qualification->get_users_and_ucas_points(-1);
                if($usersUcas)
                {
                    foreach($usersUcas AS $user)
                    {
                        //ceta:
                        $cetaUcas = 'X';
                        $ceta = $qualification->get_current_ceta($qual->id, $user->userid);
                        if($ceta && $ceta->grade)
                        {
                            $cetaUcas = $ceta->ucaspoints;
                        }
                        else
                        {
                            $cetas = $qualification->get_most_recent_ceta($qual->id, $user->userid);
                            if($cetas)
                            {
                                $ceta = end($cetas);
                                $cetaUcas = $ceta->ucaspoints;
                            }
                        }
                        if($cetaUcas != 'X' && ($user->ucaspoints && $user->ucaspoints != 0))
                        {
                            $totalCetaUcas = $totalCetaUcas + $cetaUcas;
                            $totalTargetUcas = $totalTargetUcas + $user->ucaspoints;
                            $userCount++; 
                        }
                    }
                }
                
                
            }
               
        }
        $temp = '';
        if($totalCetaUcas != 0 && $totalTargetUcas != 0 && $userCount != 0)
        {
            $qualWeighting = new TargetQualWeighting();
            $multiplier = $qualWeighting->get_multiplier($targetQualID);
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_targetqual_alps_report($totalTargetUcas, $totalCetaUcas, $targetQualID, $userCount, $showCoefficient);
        }
        return $temp;
    }
    
    public function calculate_target_qual_fa_alps_score($targetQualID, $showCoefficient)       
    {
        //need to get all of the quals under this target qual
        //then for each of those get the students
        //then get the target grade ucas and the ceta or grade ucas. 
        //this is totaled up and then compared to the TARGET QUAL weightings in
        //the table tqual_weight
        $userCount = 0;
        $totalFAUcas = 0;
        $totalTargetUcas = 0;
        
        $quals = bcgt_get_quals_by_target_qual($targetQualID);
        if($quals)
        {
            foreach($quals AS $qual)
            {
                $qualification = Qualification::get_qualification_class_id($qual->id);
                $usersUcas = $qualification->get_users_and_ucas_points(-1);
                if($usersUcas)
                {
                    foreach($usersUcas AS $user)
                    {
                        //ceta:
                        $faUcas = 'X';
                        $shortValue = null;
                        $fa = $qualification->get_current_fa_grade($qual->id, $user->userid);
                        if($fa && $fa->shortvalue)
                        {
                            $shortValue = $fa->shortvalue;
                        }
                        else
                        {
                            $fas = $qualification->get_most_recent_fa_grade($qual->id, $user->userid);
                            if($fas)
                            {
                                $fa = end($fas);
                                $shortValue = $fa->shortvalue;
                            }
                        }
                        if($shortValue)
                        {
                            $targetGrade = new TargetGrade();
                            $targetGradeObj = $targetGrade->retrieve_target_grade(-1, $targetQualID, $shortValue);
                            if($targetGradeObj && $targetGradeObj->get_grade() && $targetGradeObj->get_grade() != '')
                            {
                                $faUcas = $targetGradeObj->get_ucas_points();
                            }
                        }
                        
                        if($faUcas != 'X' && ($user->ucaspoints && $user->ucaspoints != 0))
                        {
                            $totalFAUcas = $totalFAUcas + $faUcas;
                            $totalTargetUcas = $totalTargetUcas + $user->ucaspoints;
                            $userCount++; 
                        }
                    }
                }
            }  
        }
        $temp = '';
        if($totalFAUcas != 0 && $totalTargetUcas != 0 && $userCount != 0)
        {
            $qualWeighting = new TargetQualWeighting();
            $multiplier = $qualWeighting->get_multiplier($targetQualID);
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_targetqual_alps_report($totalTargetUcas, $totalFAUcas, $targetQualID, $userCount, $showCoefficient);
        }
        return $temp;
    }
    
    public function calculate_target_qual_ceta_alps_score_project($targetQualID, $showCoefficient, $projectID)       
    {
        //need to get all of the quals under this target qual
        //then for each of those get the students
        //then get the target grade ucas and the ceta or grade ucas. 
        //this is totaled up and then compared to the TARGET QUAL weightings in
        //the table tqual_weight
        $userCount = 0;
        $totalCetaUcas = 0;
        $totalTargetUcas = 0;
        
        $quals = bcgt_get_quals_by_target_qual($targetQualID);
        if($quals)
        {
            foreach($quals AS $qual)
            {
                $qualification = Qualification::get_qualification_class_id($qual->id);
                $usersUcas = $qualification->get_users_and_ucas_points(-1);
                if($usersUcas)
                {
                    foreach($usersUcas AS $user)
                    {
                        
                        //we have their target ucas points
                        //can we get formal assessment grade ucas points?
                        $project = new Project($projectID);
                        $project->load_student_information($user->userid,$qual->id);
                        $targetGrade = $project->get_user_grade();
                        if($targetGrade && $targetGrade->get_grade() && $targetGrade->get_grade() != '')
                        {
                            $cetaUcas = $targetGrade->get_ucas_points();
                            $totalCetaUcas = $totalCetaUcas + $cetaUcas;
                            $totalTargetUcas = $totalTargetUcas + $user->ucaspoints;
                            $userCount++;
                        } 
                    }
                }
            } 
        }
        $temp = '';
        if($totalCetaUcas != 0 && $totalTargetUcas != 0 && $userCount != 0)
        {
            $qualWeighting = new TargetQualWeighting();
            $multiplier = $qualWeighting->get_multiplier($targetQualID);
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_targetqual_alps_report($totalTargetUcas, $totalCetaUcas, $targetQualID, $userCount, $showCoefficient);
        }
        return $temp;
    }
    
    public function calculate_target_qual_fa_alps_score_project($targetQualID, $showCoefficient, $projectID)       
    {
        //need to get all of the quals under this target qual
        //then for each of those get the students
        //then get the target grade ucas and the ceta or grade ucas. 
        //this is totaled up and then compared to the TARGET QUAL weightings in
        //the table tqual_weight
        $userCount = 0;
        $totalFaUcas = 0;
        $totalTargetUcas = 0;
        
        $quals = bcgt_get_quals_by_target_qual($targetQualID);
        if($quals)
        {
            foreach($quals AS $qual)
            {
                $qualification = Qualification::get_qualification_class_id($qual->id);
                $usersUcas = $qualification->get_users_and_ucas_points(-1);
                if($usersUcas)
                {
                    foreach($usersUcas AS $user)
                    {
                        //we have their target ucas points
                        //can we get formal assessment grade ucas points?
                        $project = new Project($projectID);
                        $project->load_student_information($user->userid, $qual->id);
                        $userValue = $project->get_user_value();
                        if($userValue && $userValue->get_value())
                        {
                            $targetGrade = new TargetGrade();
                            $targetGradeObj = $targetGrade->retrieve_target_grade(-1, $targetQualID, $userValue->get_value());
                            if($targetGradeObj && $targetGradeObj->get_grade() && $targetGradeObj->get_grade() != '')
                            {
                                $faUcas = $targetGradeObj->get_ucas_points();
                                $totalFaUcas = $totalFaUcas + $faUcas;
                                $totalTargetUcas = $totalTargetUcas + $user->ucaspoints;
                                $userCount++;
                            }
                        }
                    }
                }
            }  
        }
        $temp = '';
        if($totalFaUcas != 0 && $totalTargetUcas != 0 && $userCount != 0)
        {
            $qualWeighting = new TargetQualWeighting();
            $multiplier = $qualWeighting->get_multiplier($targetQualID);
            $alps = new Alps();
            $alps->set_alps_multiplier($multiplier);
            $temp = $alps->calculate_targetqual_alps_report($totalTargetUcas, $totalFaUcas, $targetQualID, $userCount, $showCoefficient);
        }
        return $temp;
    }
    
    
    public function calculate_target_qual_alps()
    {
        //need to get all targetquals that have quals that can have weightings
        $targetQuals = $this->get_target_quals_use_alps();
        if($targetQuals)
        {
            foreach($targetQuals AS $targetQual)
            {                
                //need to get all of the quals under this target qual
                //then for each of those get the students
                //then get the target grade ucas and the ceta or grade ucas. 
                //this is totaled up and then compared to the TARGET QUAL weightings in
                //the table tqual_weight
                
                //get the ceta for the targetqual:
                $alpsScoreCoefCeta = $this->calculate_target_qual_ceta_alps_score($targetQual->id, true);
                $alpsScoreCoefFA = $this->calculate_target_qual_fa_alps_score($targetQual->id, true);
                if(($alpsScoreCoefCeta && is_object($alpsScoreCoefCeta)) || ($alpsScoreCoefFA && is_object($alpsScoreCoefFA) != -1))
                {
                    $alpsScoreCoef = new stdClass();
                    $alpsScoreCoef->bcgttargetqualid = $targetQual->id;
                    if($alpsScoreCoefFA && is_object($alpsScoreCoefFA))
                    {
                        $alpsScoreCoef->gradescore = $alpsScoreCoefFA->number;
                        $alpsScoreCoef->gradecoef = $alpsScoreCoefFA->score;
                    }
                    if($alpsScoreCoefCeta && is_object($alpsScoreCoefCeta))
                    {
                        $alpsScoreCoef->cetascore = $alpsScoreCoefCeta->number;
                        $alpsScoreCoef->cetacoef = $alpsScoreCoefCeta->score;
                    }
                    $records = $this->get_targetqual_overall_alps_scores($targetQual->id);
                    if($records)
                    {
                        //we know its one
                        $record = end($records);
                        $alpsScoreCoef->id = $record->id;
                        $this->update_alps_scores($alpsScoreCoef);
                    }
                    else
                    {
                        $this->insert_alps_scores($alpsScoreCoef);
                    }
                }
                else
                {
                    //do we want to delete them?
                    //probably yes
                    $records = $this->get_targetqual_overall_alps_scores($targetQual->id);
                    if($records)
                    {
                        $this->delete_target_qual_overall_alps($targetQual->id);
                    }
                }
                                
                //now we need to do the projects.
                //get all of the possible projects
                $projects = Project::get_all_projects(null);
                if($projects)
                {
                    foreach($projects AS $project)
                    {
                        $alpsScoreCoefCeta = $this->calculate_target_qual_ceta_alps_score_project($targetQual->id, true, $project->get_id());
                        $alpsScoreCoefFA = $this->calculate_target_qual_fa_alps_score_project($targetQual->id, true, $project->get_id());
                        
                        if(($alpsScoreCoefCeta && is_object($alpsScoreCoefCeta)) || ($alpsScoreCoefFA && is_object($alpsScoreCoefFA)))
                        {
                            $alpsScoreCoef = new stdClass();
                            $alpsScoreCoef->bcgttargetqualid = $targetQual->id;
                            $alpsScoreCoef->bcgtprojectid = $project->get_id();
                            if($alpsScoreCoefFA && is_object($alpsScoreCoefFA))
                            {
                                $alpsScoreCoef->gradescore = $alpsScoreCoefFA->number;
                                $alpsScoreCoef->gradecoef = $alpsScoreCoefFA->score;
                            }
                            if($alpsScoreCoefCeta && is_object($alpsScoreCoefCeta))
                            {
                                $alpsScoreCoef->cetascore = $alpsScoreCoefCeta->number;
                                $alpsScoreCoef->cetacoef = $alpsScoreCoefCeta->score;
                            }
                            $records = $this->get_targetqual_project_overall_alps_scores($targetQual->id, $project->get_id());
                            if($records)
                            {
                                //we know its one
                                $record = end($records);
                                $alpsScoreCoef->id = $record->id;
                                $this->update_alps_scores($alpsScoreCoef);
                            }
                            else
                            {
                                $this->insert_alps_scores($alpsScoreCoef);
                            }
                        }
                        else
                        {
                            //do we want to delete them?
                            //probably yes
                            $records = $this->get_targetqual_project_overall_alps_scores($targetQual->id, $project->get_id());
                            if($records)
                            {
                                $this->delete_target_qual_project_overall_alps($targetQual->id);
                            }
                        }
                    }
                } 
            }
        }    
    }
    
    public function calculate_family_alps()
    {
        //need to get all families that have quals that can have weightings
        $families = $this->get_families_use_alps();
        if($families)
        {
            foreach($families AS $family)
            {
                //get all scores of this targetqual
                $scores = $this->get_family_targetquals_overall_alps_scores($family->id);
                if($scores)
                {
                    $stdObject = $this->average_alps_scores(null, $scores);
                    $stdObject->bcgtfamilyid = $family->id;
                    $records = $this->get_family_overall_alps_scores($family->id);
                    if($records)
                    {
                        $record = end($records);
                        $stdObject->id = $record->id;
                        $this->update_alps_scores($stdObject);
                    }
                    else
                    {
                        $this->insert_alps_scores($stdObject);
                    }
                }
                else
                {
                    //do we want to delete them?
                    //probably yes
                    $records = $this->get_family_overall_alps_scores($family->id);
                    if($records)
                    {
                        $this->delete_family_overall_alps($family->id);
                    }
                }
                
                //now we need to do the projects.
                //get all of the possible projects
                $projects = Project::get_all_projects(null);
                if($projects)
                {
                    foreach($projects AS $project)
                    {
                        //get all scores of this targetqual
                        $scores = $this->get_family_target_quals_projects_overall_alps_scores($family->id, $project->get_id());
                        if($scores)
                        {
                            $stdObject = $this->average_alps_scores(null, $scores);
                            $stdObject->bcgtfamilyid = $family->id;
                            $stdObject->bcgtprojectid = $project->get_id();
                            $records = $this->get_family_project_overall_alps_scores($family->id, $project->get_id());
                            if($records)
                            {
                                $record = end($records);
                                $stdObject->id = $record->id;
                                $this->update_alps_scores($stdObject);
                            }
                            else
                            {
                                $this->insert_alps_scores($stdObject);
                            }
                        }
                        else
                        {
                            //do we want to delete them?
                            //probably yes
                            $records = $this->get_family_project_overall_alps_scores($family->id, $project->get_id());
                            if($records)
                            {
                                $this->delete_family_project_overall_alps($family->id, $project->get_id());
                            }
                        }
                    }
                }
                
            }
        }
    }
    
    public function calculate_course_alps()
    {
        $courses = $this->get_courses_use_alps();
        if($courses) 
        {
            foreach($courses AS $course)
            {
                //now get all of their scores
                $scores = $this->get_course_quals_overall_alps_scores($course->id);
                //then do the usual with them. 
                if($scores)
                {
                    $stdObject = $this->average_alps_scores(null, $scores);
                    $stdObject->courseid = $course->id;
                    $records = $this->get_course_overall_alps_scores($course->id);
                    if($records)
                    {
                        $record = end($records);
                        $stdObject->id = $record->id;
                        $this->update_alps_scores($stdObject);
                    }
                    else
                    {
                        $this->insert_alps_scores($stdObject);
                    }
                }
                else
                {
                    //do we want to delete them?
                    //probably yes
                    $records = $this->get_course_overall_alps_scores($course->id);
                    if($records)
                    {
                        $this->delete_course_overall_alps($course->id);
                    }
                }
                
                
                //now we need to do the projects.
                //get all of the possible projects
                $projects = Project::get_all_projects(null);
                if($projects)
                {
                    foreach($projects AS $project)
                    {
                        //get all scores of this targetqual
                        $scores = $this->get_course_quals_projects_overall_alps_scores($course->id, $project->get_id());
                        if($scores)
                        {
                            $stdObject = $this->average_alps_scores(null, $scores);
                            $stdObject->courseid = $course->id;
                            $stdObject->bcgtprojectid = $project->get_id();
                            $records = $this->get_course_project_overall_alps_scores($course->id, $project->get_id());
                            if($records)
                            {
                                $record = end($records);
                                $stdObject->id = $record->id;
                                $this->update_alps_scores($stdObject);
                            }
                            else
                            {
                                $this->insert_alps_scores($stdObject);
                            }
                        }
                        else
                        {
                            //do we want to delete them?
                            //probably yes
                            $records = $this->get_course_project_overall_alps_scores($course->id, $project->get_id());
                            if($records)
                            {
                                $this->delete_course_project_overall_alps($course->id, $project->get_id());
                            }
                        }
                    }
                }
            }
        }
    }
    
    public function calculate_category_alps()
    {
        //need to get all categories, child and parent
        //that have quals that can have weightings.
        //get all categories
        $categories = bcgt_get_categories();
        if($categories)
        {
            foreach($categories AS $category)
            {
                $courses = bcgt_get_course_from_cat($category->id);
                //for each course get the alps overall:
                if($courses)
                {
                    $stdObject = $this->average_course_alps_scores($courses);
                    $stdObject->categoryid = $category->id;
                    if($stdObject->cetascore != 0 || $stdObject->gradescore != 0)
                    {
                        //now need to do the project stuff. 
                        $records = $this->get_category_overall_alps_scores($category->id);
                        if($records)
                        {
                            $record = end($records);
                            $stdObject->id = $record->id;
                            $this->update_alps_scores($stdObject);
                        }
                        else
                        {
                            $this->insert_alps_scores($stdObject);
                        }
                    }
                    else
                    {
                        //do we want to delete them?
                        //probably yes
                        $records = $this->get_category_overall_alps_scores($category->id);
                        if($records)
                        {
                            $this->delete_category_overall_alps($category->id);
                        }
                    }
                    
                    
                    //now we need to do the projects.
                    //get all of the possible projects
                    $projects = Project::get_all_projects(null);
                    if($projects)
                    {
                        foreach($projects AS $project)
                        {
                            $stdObject = $this->average_course_alps_scores($courses, $project->get_id());
                            $stdObject->categoryid = $category->id;
                            $stdObject->bcgtprojectid = $project->get_id();
                            if($stdObject->cetascore != 0 || $stdObject->gradescore != 0)
                            {
                                $records = $this->get_category_project_overall_alps_scores($category->id, $project->get_id());
                                if($records)
                                {
                                    $record = end($records);
                                    $stdObject->id = $record->id;
                                    $this->update_alps_scores($stdObject);
                                }
                                else
                                {
                                    $this->insert_alps_scores($stdObject);
                                }  
                            }
                            else
                            {
                                //do we want to delete them?
                                //probably yes
                                $records = $this->get_category_project_overall_alps_scores($category->id, $project->get_id());
                                if($records)
                                {
                                    $this->delete_category_project_overall_alps($category->id, $project->get_id());
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    protected function average_alps_scores($qualID = null, $scores)
    {
        $totalCetaScore = 0;
        $totalFaScore = 0;
        $totalCetaCoef = 0;
        $totalFaCoef = 0;
        $countCeta = 0;
        $countFa = 0;
        foreach($scores AS $score)
        {
            //this is the score per user where the project is null
            if($score->cetascore && $score->cetascore != 0)
            {
                $countCeta++; 
                $totalCetaScore += $score->cetascore;
                $totalCetaCoef += $score->cetacoef;
            }

            if($score->gradescore && $score->gradescore != 0)
            {
                $countFa++;
                $totalFaScore += $score->gradescore;
                $totalFaCoef += $score->gradecoef;
            }
        }
        $stdObject = new stdClass();
        $stdObject->bcgtqualificationid = $qualID;
        $stdObject->cetacoef = $this->average_ceta_coef($countCeta, $totalCetaCoef);
        $stdObject->cetascore = $this->average_ceta_score($countCeta, $stdObject->cetacoef, $totalCetaScore, $qualID);
        $stdObject->gradecoef = $this->average_fa_coef($totalFaCoef, $countFa);
        $stdObject->gradescore = $this->average_fa_score($countFa, $totalFaScore, $stdObject->gradecoef, $qualID);
        
         
        return $stdObject;
    }
    
    protected function average_course_alps_scores($courses, $projectID = null)
    {
        $totalCetaScore = 0;
        $totalFaScore = 0;
        $totalCetaCoef = 0;
        $totalFaCoef = 0;
        $countCeta = 0;
        $countFa = 0;
        foreach($courses AS $course)
        {
            if($projectID)
            {
                $records = $this->get_course_quals_projects_overall_alps_scores($course->id, $projectID);
            }
            else
            {
                $records = $this->get_course_quals_overall_alps_scores($course->id);
            }
            if($records)
            {
                //now get the correct stuff.
                foreach($records AS $score)
                {
                    //this is the score per user where the project is null
                    if($score->cetascore)
                    {
                        $countCeta++; 
                        $totalCetaScore += $score->cetascore;
                        $totalCetaCoef += $score->cetacoef;
                    }

                    if($score->gradescore)
                    {
                        $countFa++;
                        $totalFaScore += $score->gradescore;
                        $totalFaCoef += $score->gradecoef;
                    }
                }
            }
        }
        $stdObject = new stdClass();
        $stdObject->cetacoef = $this->average_ceta_coef($countCeta, $totalCetaCoef);
        $stdObject->cetascore = $this->average_ceta_score($countCeta, $stdObject->cetacoef, $totalCetaScore, null);
        $stdObject->gradecoef = $this->average_fa_coef($totalFaCoef, $countFa);
        $stdObject->gradescore = $this->average_fa_score($countFa, $totalFaScore, $stdObject->gradecoef, null);
        
        return $stdObject;
    }
    
    protected function average_ceta_score($countCeta, $newCetaCoef, $totalCetaScore, $qualID)
    {
        if($countCeta != 0)
        {
            if($qualID)
            {
                //we cant just average the score. It needs to be re-looked up:
                //$stdObject->cetascore = $totalCetaScore/$countCeta;
                $qualWeighting = new QualWeighting();
                $qualWeightingRecord = $qualWeighting->get_alps_temperature($qualID, $newCetaCoef);
                return $qualWeightingRecord->number;
            }
            else
            {
                return floor($totalCetaScore/$countCeta);
            }
        }
        return null;
    }
    
    protected function average_ceta_coef($countCeta, $totalCetaCoef)
    {
        if($countCeta != 0 && $totalCetaCoef != 0)
        {
            return $totalCetaCoef/$countCeta;
        }
        return null;
    }
    
    protected function average_fa_score($countFa, $totalFaScore, $newGradeCeof, $qualID)
    {
        if($countFa != 0)
        {
            if($qualID)
            {
                //we cant just average the score. It needs to be re-looked up: 
                //$stdObject->gradescore = $totalFaScore/$countFa;
                $qualWeighting = new QualWeighting();
                $qualWeightingRecord = $qualWeighting->get_alps_temperature($qualID, $newGradeCeof);
                return $qualWeightingRecord->number;
            }
            else
            {
                return floor($totalFaScore/$countFa);
            }  
        }
        return null;
    }
    
    protected function average_fa_coef($totalFaCoef, $countFa)
    {
        if($countFa != 0 && $totalFaCoef != 0)
        {
            return $totalFaCoef/$countFa;
        }
        return null;
    }
    
    public function calculate_qual_alps_scores($qualID)
    {
        //get the qualification
        
        //the overall ceta
        //the overall grade
        //each individual project
    }

    /**
     * 
     * @param type $quals
     */
    public function calculate_individual_stu_alps($quals = null)
    {
        if(!$quals)
        {
            $quals = $this->get_quals_use_alps();
        }
        
        $showCoefficient = true;
        foreach($quals AS $qual)
        {
            $this->save_all_stu_qual_alps_scores($qual->id, $showCoefficient);
        }
    }
    
    protected function save_all_stu_qual_alps_scores($qualID, $showCoefficient)
    {
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        if($qualification)
        {
            $students = $qualification->get_students();
            if($students)
            {
                foreach($students AS $student)
                {
                    $this->save_stu_qual_alps_scores($showCoefficient, $qualID, $student->id, $qualification);
                }
            }
        }
    }
    
    protected function save_stu_qual_alps_scores($showCoefficient, $qualID, $studentID, $qualification = null)
    {
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        if(!$qualification)
        {
            $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        }
        //load the qual information.
        //calculate their coefficient and alps score 
        $qualification->load_student_information($studentID, $loadParams);

        //Overall Ceta Coefficient
        $alpsScoreCoefCeta = $qualification->get_user_ceta_alps_temp($studentID, $showCoefficient);
        //OVERALL Grade Coeeficient
        $alpsScoreCoefFA = $qualification->get_user_fa_alps_temp($studentID, $showCoefficient);
        if(($alpsScoreCoefCeta && is_object($alpsScoreCoefCeta)) || ($alpsScoreCoefFA && is_object($alpsScoreCoefFA) != -1))
        {
            $alpsScoreCoef = new stdClass();
            $alpsScoreCoef->userid = $studentID;
            $alpsScoreCoef->bcgtqualificationid = $qualID;
            if($alpsScoreCoefFA && is_object($alpsScoreCoefFA))
            {
                $alpsScoreCoef->gradescore = ($alpsScoreCoefFA->number != 0)? $alpsScoreCoefFA->number : null;
                $alpsScoreCoef->gradecoef = $alpsScoreCoefFA->score;
            }
            else
            {
                $alpsScoreCoef->gradescore = null;
                $alpsScoreCoef->gradecoef = null;
            }
            if($alpsScoreCoefCeta && is_object($alpsScoreCoefCeta))
            {
                $alpsScoreCoef->cetascore = ($alpsScoreCoefCeta->number != 0)? $alpsScoreCoefCeta->number : null;
                $alpsScoreCoef->cetacoef = $alpsScoreCoefCeta->score;
            }
            else
            {
                $alpsScoreCoef->cetascore = null;
                $alpsScoreCoef->cetacoef = null;
            }
            $records = $this->get_alps_user_overall_scores($studentID, $qualID);
            if($records)
            {
                //we know its one
                $record = end($records);
                $alpsScoreCoef->id = $record->id;
                $this->update_alps_scores($alpsScoreCoef);
            }
            else
            {
                $this->insert_alps_scores($alpsScoreCoef);
            }
        }
        else
        {
            //do we want to delete them?
            //probably yes
            $records = $this->get_alps_user_overall_scores($studentID, $qualID);
            if($records)
            {
                $this->delete_user_overall_alps($studentID, $qualID);
            }
        }
        
        //each individual Formal Assessment Coefficients
        //get the assessments that are on this. 
        $projects = Project::get_qual_assessments($qualID);
        if($projects)
        {
            foreach($projects AS $project)
            {
                $alpsScoreCoefCeta = $qualification->get_user_ceta_ind_alps_temp($studentID, $project->get_id(), $showCoefficient);
                $alpsScoreCoefFa = $qualification->get_user_fa_ind_alps_temp($studentID, $project->get_id(), $showCoefficient);
                if($alpsScoreCoefCeta || $alpsScoreCoefFa)
                {
                    $alpsScoreCoef = new stdClass();
                    $alpsScoreCoef->userid = $studentID;
                    $alpsScoreCoef->bcgtqualificationid = $qualID;
                    $alpsScoreCoef->bcgtprojectid = $project->get_id();
                    if($alpsScoreCoefCeta && is_object($alpsScoreCoefCeta))
                    {
                        $alpsScoreCoef->cetascore = $alpsScoreCoefCeta->number;
                        $alpsScoreCoef->cetacoef = $alpsScoreCoefCeta->score;
                    }
                    if($alpsScoreCoefFa && is_object($alpsScoreCoefFa))
                    {
                        $alpsScoreCoef->gradescore = $alpsScoreCoefFa->number;
                        $alpsScoreCoef->gradecoef = $alpsScoreCoefFa->score;
                    }
                    $records = $this->get_alps_scores($studentID, $qualID, $project->get_id());
                    if($records)
                    {
                        $record = end($records);
                        $alpsScoreCoef->id = $record->id;
                        $this->update_alps_scores($alpsScoreCoef);
                    }
                    else
                    {
                        $this->insert_alps_scores($alpsScoreCoef);
                    }
                }
                else
                {
                    //do we want to delete them?
                    $records = $this->get_alps_scores($studentID, $qualID, $project->get_id());
                    if($records)
                    {
                        $this->delete_user_project_alps($studentID, $qualID);
                    }
                }
            }
        }
    }
        
//    protected function save_alps_score($params)
//    {
//        global $DB;
//        if($params)
//        {
//            if($preExist = $this->get_alps_score($params))
//            {
//                $params->id = $preExist->id;
//                $this->update_alps_score($params);
//            }
//            else
//            {
//                $this->insert_alps_score($params);
//            }
//        } 
//    }
    
    protected function get_alps_scores($userID = null, $qualID = null, $projectID = null)
    {
        global $DB;
        $params = array();
        $sql = "SELECT * FROM {block_bcgt_alps_scores}";
        if($userID || $qualID || $projectID)
        {
            $sql .= " WHERE";
        }
        $and = false;
        if($userID)
        {
            $sql .= " userid = ?";
            $params[] = $userID;
            $and = true;
        }
        if($qualID)
        {
            if($and)
            {
                $sql .= " AND";
            }
            $sql .= " bcgtqualificationid = ?";
            $params[] = $qualID;
            $and = true;
        }
        if($projectID)
        {
            if($and)
            {
                $sql .= " AND";
            }
            $sql .= " bcgtprojectid = ?";
            $params[] = $projectID;
            $and = true;
        }
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_alps_user_overall_scores($userID, $qualID)
    {
        global $DB;
        $params = array();
        $sql = "SELECT * FROM {block_bcgt_alps_scores}";
        $sql .= " WHERE";
        $sql .= " userid = ?";
        $params[] = $userID;
        $sql .= " AND";
        $sql .= " bcgtqualificationid = ?";
        $params[] = $qualID;
        
        $sql .= " AND";
        
        $sql .= " bcgtprojectid IS NULL";
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_alps_qual_user_scores($qualID, $projectID = null)
    {
        global $DB;
        $params = array();
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores";
        if($qualID || $projectID)
        {
            $sql .= " WHERE";
        }
        $and = false;
        if($qualID)
        {
            $sql .= " bcgtqualificationid = ?";
            $params[] = $qualID;
            $and = true;
        }
        else
        {
            $sql .= " bcgtqualificationid IS NULL";
            $and = true;
        }
        if($projectID)
        {
            if($and)
            {
                $sql .= " AND";
            }
            $sql .= " bcgtprojectid = ?";
            $params[] = $projectID;
            $and = true;
        }
        else
        {
            if($and)
            {
                $sql .= " AND";
            }
            $sql .= " bcgtprojectid IS NULL";
            $and = true;
        }
        if($and)
        {
            $sql .= " AND";
        }
        $sql .= " userid IS NOT NULL";
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_qual_overall_alps_scores($qualID)
    {
        global $DB;
        $params = array();
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores";
        $sql .= " WHERE";
        $sql .= " bcgtqualificationid = ?";
        $params[] = $qualID;
        $sql .= " AND";
        $sql .= " bcgtprojectid IS NULL";
        $sql .= " AND";
        $sql .= " userid IS NULL";
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_qual_project_overall_alps_scores($qualID, $projectID)
    {
        global $DB;
        $params = array();
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores";
        $sql .= " WHERE";
        $sql .= " bcgtqualificationid = ?";
        $params[] = $qualID;
        $sql .= " AND";
        $sql .= " bcgtprojectid = ?";
        $params[] = $projectID;
        $sql .= " AND";
        $sql .= " userid IS NULL";
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_project_quals_overall_alps_scores($projectID)
    {
        global $DB;
        $params = array($projectID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            WHERE bcgtprojectid = ? AND bcgtqualificationid IS NOT NULL AND userid IS NULL";
        return $DB->get_records_sql($sql, $params);
        
    }
    
    protected function get_targetqual_quals_overall_alps_scores($targetQualID)
    {
        global $DB;
        $params = array($targetQualID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            JOIN {block_bcgt_qualification} qual ON qual.id = scores.bcgtqualificationid
            WHERE qual.bcgttargetqualid = ? AND scores.bcgtprojectid IS NULL AND scores.userid IS NOT NULL";
        return $DB->get_records_sql($sql, $params);
    }
    
    public function get_family_quals_overall_alps_scores($familyID)
    {
        global $DB;
        $params = array($familyID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            JOIN {block_bcgt_qualification} qual ON qual.id = scores.bcgtqualificationid
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid
            WHERE family.id = ? AND userid IS NULL AND bcgtprojectid IS NULL AND scores.bcgttargetqualid IS NULL";
        return $DB->get_records_sql($sql, $params);
    }
    
    public function get_family_targetquals_overall_alps_scores($familyID)
    {
        global $DB;
        $params = array($familyID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = scores.bcgttargetqualid
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid
            WHERE family.id = ? AND userid IS NULL AND bcgtprojectid IS NULL AND scores.bcgtqualificationid IS NULL AND scores.bcgttargetqualid IS NOT NULL";
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_course_quals_overall_alps_scores($courseID)
    {
        global $DB;
        $params = array($courseID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            JOIN {block_bcgt_qualification} qual ON qual.id = scores.bcgtqualificationid
            JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id
            WHERE coursequal.courseid = ? AND userid IS NOT NULL AND bcgtprojectid IS NULL";
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_targetqual_quals_projects_overall_alps_scores($targetQualID, $projectID)
    {
        global $DB;
        $params = array($targetQualID, $projectID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            JOIN {block_bcgt_qualification} qual ON qual.id = scores.bcgtqualificationid
            WHERE qual.bcgttargetqualid = ? AND userid IS NOT NULL AND bcgtprojectid = ?";
        return $DB->get_records_sql($sql, $params);
    }
    
    public function get_family_quals_projects_overall_alps_scores($familyID, $projectID)
    {
        global $DB;
        $params = array($familyID, $projectID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            JOIN {block_bcgt_qualification} qual ON qual.id = scores.bcgtqualificationid
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid
            WHERE family.id = ? AND userid IS NULL AND bcgtprojectid = ? AND scores.bcgttargetqualid IS NULL";
        return $DB->get_records_sql($sql, $params);
    }
    
    public function get_family_target_quals_projects_overall_alps_scores($familyID, $projectID)
    {
        global $DB;
        $params = array($familyID, $projectID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = scores.bcgttargetqualid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid
            WHERE family.id = ? AND userid IS NULL AND bcgtprojectid = ? AND scores.bcgttargetqualid IS NOT NULL AND scores.bcgtqualificationid IS NULL";
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_course_quals_projects_overall_alps_scores($courseID, $projectID)
    {
        global $DB;
        $params = array($courseID, $projectID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            JOIN {block_bcgt_qualification} qual ON qual.id = scores.bcgtqualificationid
            JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id
            WHERE coursequal.courseid = ? AND userid IS NOT NULL AND bcgtprojectid = ?";
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_targetqual_overall_alps_scores($targetQualID)
    {
        global $DB;
        $params = array($targetQualID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            WHERE scores.bcgttargetqualid = ? AND scores.bcgtprojectid IS NULL";
        return $DB->get_records_sql($sql, $params);
    }
    
    public function get_family_overall_alps_scores($familyID)
    {
        global $DB;
        $params = array($familyID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            WHERE scores.bcgtfamilyid = ? AND bcgtprojectid IS NULL";
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_course_overall_alps_scores($courseID)
    {
        global $DB;
        $params = array($courseID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            WHERE scores.courseid = ? AND bcgtprojectid IS NULL";
        return $DB->get_records_sql($sql, $params);
    }
        
    public function get_category_overall_alps_scores($categoryID)
    {
        global $DB;
        $params = array($categoryID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            WHERE scores.categoryid = ? AND bcgtprojectid IS NULL";
        return $DB->get_records_sql($sql, $params);
    }

    protected function get_targetqual_project_overall_alps_scores($targetQualID, $projectID)
    {
        global $DB;
        $params = array($targetQualID,$projectID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            WHERE scores.bcgttargetqualid = ? AND bcgtprojectid = ?";
        return $DB->get_records_sql($sql, $params);
    }
    
    public function get_family_project_overall_alps_scores($familyID, $projectID)
    {
        global $DB;
        $params = array($familyID,$projectID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            WHERE scores.bcgtfamilyid = ? AND bcgtprojectid = ?";
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_course_project_overall_alps_scores($courseID, $projectID)
    {
        global $DB;
        $params = array($courseID,$projectID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            WHERE scores.courseid = ? AND scores.bcgtprojectid = ?";
        return $DB->get_records_sql($sql, $params);
    }
    
    public function get_category_project_overall_alps_scores($categoryID, $projectID)
    {
        global $DB;
        $params = array($categoryID,$projectID);
        $sql = "SELECT * FROM {block_bcgt_alps_scores} scores
            WHERE scores.categoryid = ? AND scores.bcgtprojectid = ?";
        return $DB->get_records_sql($sql, $params);
    }
    
    protected function get_project_overall_alps_scores($projectID)
    {
        global $DB;
        $params = array($projectID);
        $sql = "SELECT scores.* FROM {block_bcgt_alps_scores} scores
            WHERE bcgtprojectid = ? AND bcgtqualificationid IS NULL AND userid IS NULL";
        return $DB->get_records_sql($sql, $params);
    }
    
//    /**
//     * Check on userid, qualid, projectid, categoryid, typeid, familyid
//     * @global type $DB
//     * @param type $params
//     * @return type
//     */
//    public function get_alps_score($params)
//    {
//        global $DB;
//        $sqlParams = array();
//        $sql = "SELECT * FROM {block_bcgt_alps_scores}";
//        if($params)
//        {
//            $sql .= " WHERE";
//            $count = 0;
//            foreach($params AS $field=>$param)
//            {
//                $count++;
//                $sql .= ' '.$field.' = ?';
//                $sqlParams[] = $param;
//                if($count != count($params))
//                {
//                    $sql .= ' AND';
//                }
//            }
//        }
//        return $DB->get_records_sql($sql, $sqlParams);
//    }
    
    protected function insert_alps_scores($params)
    {
        global $DB;
        return $DB->insert_record('block_bcgt_alps_scores', $params);
    }
    
    protected function update_alps_scores($params)
    {
        global $DB;
        return $DB->update_record('block_bcgt_alps_scores', $params);
    }
    
    protected function delete_user_overall_alps($studentID, $qualID)       
    {
        global $DB;
        return $DB->execute('DELETE FROM {block_bcgt_alps_scores} 
            WHERE bcgtprojectid IS NULL AND userid = ? AND bcgtqualificationid = ?', array($studentID, $qualID));
    }
    
    protected function delete_user_project_alps($studentID, $qualID, $projectID)       
    {
        global $DB;
        return $DB->execute('DELETE FROM {block_bcgt_alps_scores} 
            WHERE bcgtprojectid = ? AND userid = ? AND bcgtqualificationid = ?', array($projectID, $studentID, $qualID));
    }
    
    protected function delete_qual_overall_alps($qualID)
    {
        global $DB;
        return $DB->execute('DELETE FROM {block_bcgt_alps_scores} 
            WHERE bcgtprojectid IS NULL AND userid IS NULL AND bcgtqualificationid = ?', array($qualID));
    }
    
    protected function delete_qual_project_overall_alps($qualID, $projectID)
    {
        global $DB;
        return $DB->execute('DELETE FROM {block_bcgt_alps_scores} 
            WHERE bcgtprojectid = ? AND userid IS NULL AND bcgtqualificationid = ?', array($projectID, $qualID));
    }
    
    protected function delete_project_overall_alps($projectID)
    {
        global $DB;
        return $DB->execute('DELETE FROM {block_bcgt_alps_scores} 
            WHERE bcgtprojectid = ? AND userid IS NULL AND bcgtqualificationid IS NULL', array($projectID));
    }
    
    protected function delete_target_qual_overall_alps($targetQualID)
    {
        global $DB;
        return $DB->execute('DELETE FROM {block_bcgt_alps_scores} 
            WHERE bcgttargetqualid = ? AND bcgtprojectid IS NULL AND userid IS NULL 
            AND bcgtqualificationid IS NULL', array($targetQualID));
    }
    
    protected function delete_target_qual_project_overall_alps($targetQualID, $projectID)
    {
        global $DB;
        return $DB->execute('DELETE FROM {block_bcgt_alps_scores} 
            WHERE bcgttargetqualid = ? AND bcgtprojectid = ? AND userid IS NULL 
            AND bcgtqualificationid IS NULL', array($targetQualID, $projectID));
    }
    
    protected function delete_family_overall_alps($familyID)
    {
        global $DB;
        return $DB->execute('DELETE FROM {block_bcgt_alps_scores} 
            WHERE bcgtfamilyid = ? AND bcgtprojectid IS NULL AND userid IS NULL 
            AND bcgtqualificationid IS NULL', array($familyID));
    }
    
    protected function delete_family_project_overall_alps($familyID, $projectID)
    {
        global $DB;
        return $DB->execute('DELETE FROM {block_bcgt_alps_scores} 
            WHERE bcgtfamilyid = ? AND bcgtprojectid = ? AND userid IS NULL 
            AND bcgtqualificationid IS NULL', array($familyID, $projectID));
    }
    
    protected function delete_course_overall_alps($courseID)
    {
        global $DB;
        return $DB->execute('DELETE FROM {block_bcgt_alps_scores} 
            WHERE courseid = ? AND bcgtprojectid IS NULL AND userid IS NULL 
            AND bcgtqualificationid IS NULL', array($courseID));
    }
    
    protected function delete_course_project_overall_alps($courseID, $projectID)
    {
        global $DB;
        return $DB->execute('DELETE FROM {block_bcgt_alps_scores} 
            WHERE courseid = ? AND bcgtprojectid = ? AND userid IS NULL 
            AND bcgtqualificationid IS NULL', array($courseID, $projectID));
    }
    
    protected function delete_category_overall_alps($categoryID)
    {
        global $DB;
        return $DB->execute('DELETE FROM {block_bcgt_alps_scores} 
            WHERE categoryid = ? AND bcgtprojectid IS NULL AND userid IS NULL 
            AND bcgtqualificationid IS NULL', array($categoryID));
    }
    
    protected function delete_category_project_overall_alps($categoryID, $projectID)
    {
        global $DB;
        return $DB->execute('DELETE FROM {block_bcgt_alps_scores} 
            WHERE categoryid = ? AND bcgtprojectid = ? AND userid IS NULL 
            AND bcgtqualificationid IS NULL', array($categoryID, $projectID));
    }
    
    public function update_alps_report_run_date()
    {
        global $DB;
        if($record = $this->get_alps_report_run_date())
        {
            $record->value = time();
            $DB->update_record('block_bcgt_settings', $record);
        }
        else
        {
            $record = new stdClass();
            $record->setting = ALPS::REPORTGENERATESETTING;
            $record->value = time();
            $DB->insert_record('block_bcgt_settings', $record);
        }
    }
    
    public function get_alps_report_run_date()
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_settings} settings 
            WHERE settings.setting = ?";
        return $DB->get_record_sql($sql, array(ALPS::REPORTGENERATESETTING));
    }
    
    
    
    
    
}

?>
