<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FAStudentCetas
 *
 * @author mchaney
 */
class FAStudentCetas extends CoreReports{
    //put your code here
    private $freezepane = 'D2';
    private $frozencolumnsJS = 3;
    private $frozenColumnsWidth = 280;
    private $splitHeader = false;
    private $applyDataTables = true;
        
    
    private $canrun = false;
    private $canexport = false;
    
    function FAStudentCetas()
    {
        $this->applyDataTables = false;
    }
    
    function get_name()
    {
        return "Formal Assessments: Student's current Grades";
    }
    
    function get_description()
    {
        $retval = '<p>Shows students with current Grade or Ceta</p>';
        $retval .= '<p>Options include : </p>';
        $retval .= '<ul>';
        $retval .= '<li>Target Grades</li>';
        $retval .= '<li>Qualification Summaries (Grade Counts)</li>';
        $retval .= '<li>Value Added Scores</li>';
        $retval .= '<li>Ucas points</li>';
        $retval .= '<li>View all subjects or student subjects</li>';
        $retval .= '</ul>';
        return $retval;
    }
    
    function get_examples()
    {
        global $CFG;
        $retval = '<div id="reportexaples">';
        $retval .= '<h2>Report Options Explained</h2>';
        $retval .= '<p>After selecting the filters, (Categories, Family, Levels and Subtypes),
            choose if its the Grade or Ceta and which students to show. Selecting an optional Target Grade
            will allow for colour coded cells. Ahead, Begind or On. The Display Options are explained below:</p>';
        
        $retval .= '<h3>Display Options</h3>';
        $retval .= '<h4>Anonymous - No Qualification Information</h4>';
        $retval .= '<p>This shows generic columns for SubjectA, SubjectB, SubjectC etc</p>';
        $retval .= '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/fa_current_display1_example.jpg"/>';

        $retval .= '<h4>Options - The Users Qualifications</h4>';
        $retval .= '<p>This shows sections for QualificationA, QualificationB, etc. But an extra column contains the Qualification Name</p>';
        $retval .= '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/fa_current_display2_example.jpg"/>';
        
        $retval .= '<h4>Every Qualification - Every Qualification Has A Section</h4>';
        $retval .= '<p>This shows sections for every possible Qualification. If the user is not on this Qualification then blanks are produced. This is a very slow generating report.</p>';
        $retval .= '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/fa_current_display3_example.jpg"/>';

        
        $retval .= '</div>';
        return $retval;
    }
    
    function get_extra_JS()
    {
        global $CFG;
        $out = '<script>';
        $out .= '
            $(document).ready(function(){
                var select = $("#displayoptions");
                select.unbind("change");
                select.bind("change", function(){
                    //need to change the forms destination
                    $("#corereportrun").attr("action", "'.$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=reporting&action=bespoke");
                    $("#corereportrun").attr("target", "");

                    var selectVal = $("#displayoptions").val();
                    if(selectVal == "bysubject")
                    {
                        //disable the run command.
                        $("#runsub").attr("disabled","disabled");
                    }
                    $("#corereportrun").submit();
                });
            });
        ';
        $out .= '</script>';
        return $out;
    }
    
    function display_options()
    {
        
        $out = '<div>';
        $out .= '<div>';
        $out .= $this->get_core_options();
        $out .= '</div>';
        $out .= '<div>';
        $out .= $this->get_extra_JS();
        $out .= $this->get_display_selectors();
        $out .= '</div>';
        $out .= '<div id="customOptions">';
        $out .= $this->get_custom_options();
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
    
    function can_run()
    {
        return $this->canrun;
    }
    function can_export()
    {
        return $this->canexport;
    }
    function get_frozen_panes()
    {
        return $this->freezepane;
    }
    function get_frozen_columns()
    {
        return $this->frozencolumnsJS;
    }
    
    function get_frozen_width()
    {
        return $this->frozenColumnsWidth;
    }
    function has_split_header()
    {
        return $this->splitHeader;
    }
    function can_run_data_tables()
    {
        if(isset($_POST['run']))
        {
            $this->applyDataTables = true;
        }
        return $this->applyDataTables;
    }
    
    function get_icon()
    {
        global $CFG;
        return '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/formal_assessment_current_t.jpg"/>';
    }
    function get_image()
    {
        global $CFG;
        return '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/formal_assessment_current_i.jpg"/>';
    }
    
    function get_display_selectors()
    {
        $out = '<div>';
        //the three options
        $out .= get_string('displayoptions', 'block_bcgt');
        if(isset($_POST['displayoptions']))
        {
            $this->canrun = true;
            $this->canexport = true;
            $this->options['displayoption'] = $_POST['displayoptions'];
            if($_POST['displayoptions'] == 'bysubject')     
            {
                $this->canrun = false;
            }
        }
        $out .= '<select id="displayoptions" name="displayoptions">';
        $out .= '<option value="">'.get_string('pleaseselect', 'block_bcgt').'</option>';        
        $out .= '<option value="anonymous" '.((isset($_POST['displayoptions']) && $_POST['displayoptions'] == 'anonymous') ? "selected": "").'>Anonymous: No Qualification Information</option>';
        $out .= '<option value="byoption" '.((isset($_POST['displayoptions']) && $_POST['displayoptions'] == 'byoption') ? "selected": "").'>Options: A section per student Qualificaion</option>';
        $out .= '<option value="bysubject" '.((isset($_POST['displayoptions']) && $_POST['displayoptions'] == 'bysubject') ? "selected": "").'>Every Qualification</option>';
        $out .= '</select>';
        $out .= '</div>';
        return $out;
    }
    
    function get_custom_options()
    {
        $out = '';
        if(isset($this->options['displayoption']) && $this->options['displayoption'] == 'bysubject')
        {
            $checked = '';
            if(isset($_POST['showqgradesum']))
            {
                $this->options['showqgradesum'] = true;
                $checked = 'checked';
            }
            $out .= '<span>'.get_string('showqualgradesummary','block_bcgt').' : <input type="checkbox" '.$checked.' name="showqgradesum"/></span>';
            $checked = '';
            if(isset($_POST['showsgradesum']))
            {
                $this->options['showsgradesum'] = true;
                $checked = 'checked';
            }
            $out .= '<span>'.get_string('showstugradesummary','block_bcgt').' : <input type="checkbox" '.$checked.' name="showsgradesum"/></span>';
            $checked = '';
            if(isset($_POST['showqgradesplitsum']))
            {
                $this->options['showqgradesplitsum'] = true;
                $checked = 'checked';
            }
            $out .= '<span>'.get_string('showqualgradesplitsummary','block_bcgt').' : <input type="checkbox" '.$checked.' name="showqgradesplitsum"/></span>';
        }
        
        $out .= $this->get_grade_filter(false);
        $out .= $this->get_target_grade_filter();
        //now for the 'my section'
        $out .= $this->get_my_students_filter();
        $out .= '<span>Some options above will only allow a report to be `exported` due to its size and complexity</span>';
        return $out;
    }
    
    function run_display_report()
    {
        $this->build_report();
        $out = $this->display_report();
        return $out;
    }
    
    function export_report()
    {
        $this->build_report(true);
        $this->perform_export();
    }
    
    function build_report($export = false)
    {
        global $CFG;
        //which display type are we doing?
        $displayType = $this->options['displayoption'];
        $this->displayType = $displayType;
        $header = array();
        $this->options['oncourse'] = true;
        $this->options['users'] = true;
        $qualifications = null;
        if($displayType == 'bysubject')
        {
            //If display type == bysubject then we are having a header for each qualification
            $qualifications = bcgt_search_quals_report($this->options);
        }
                
        $displayStudentSummary = (isset($this->options['showsgradesum']) ? $this->options['showsgradesum'] : false);
        $this->displayStudentSummary = $displayStudentSummary;
        
        //we always need the students
        $students = bcgt_search_users_report($this->options, array('student'));
        
        //some arrays to hold the summary info, 
        $qualSummary = array();
        $totalGrades = array();
        $studentCount = array();
        $studentSummary = array();
        $studentGradeCountArray = array();
        $currentMaxQuals = 0;
        //to do the grades
        //another array, as it loops over the grades
        //the grade becomes the array key 
        //the value is incremented number
        //ranking based on the object
        $studentData = array();
        if($this->options['gradeoption'] == 'ceta')
        {
            $column = 'grade';
        }
        else
        {
            $column = 'shortvalue'; 
        }
        if($displayType == 'bysubject')
        {
            $this->build_display_subject_report($students, $qualifications, 
            $qualSummary, $totalGrades, $studentSummary, $studentData, $studentCount, $column, $studentGradeCountArray);
        }
        else 
        {
            $currentMaxQuals = 0;
            $studentData = array();
            require_once $CFG->dirroot . '/blocks/bcgt/classes/core/Level.class.php';
            
            $this->build_display_non_subject_report($students, 
            $currentMaxQuals, $column, $export, $studentData);
        }
        $this->currentMaxQuals = $currentMaxQuals;
        //now the header
        $splitHeader = false;
        $row = array(); 
        $row2 = array();
        $this->build_header($qualifications, $currentMaxQuals, 
            $header, $splitHeader, $row, $row2);
        
        $this->qualSummary = $qualSummary;
        $this->studentCount = $studentCount;
        $this->studentSummary = $studentSummary;
        $this->studentGradeCountArray = $studentGradeCountArray;
        $this->build_summary($totalGrades, $qualifications, $studentData, 
                $header, $students, $row, $row2);
                
        if($splitHeader)
        {
            $header[] = $row;
            $header[] = $row2; 
        }
        
        $this->data = $studentData;
        $this->header = $header;
    }
    
    protected function build_display_subject_report($students, $qualifications, 
            &$qualSummary, &$totalGrades, &$studentSummary, &$studentData, &$studentCount, $column, 
            &$studentGradeCountArray)
    {
        $userVA = new userVA();
        foreach($students AS $student)
        {
            
            $studentSummaryObj = new stdClass();
            $data = array();
            $data[] = $this->create_cell(array("content"=>$student->username));
            $data[] = $this->create_cell(array("content"=>$student->firstname));;
            $data[] = $this->create_cell(array("content"=>$student->lastname)); 
            foreach($qualifications AS $qual)
            {
                $aheadBehindClass = '';
                $qualSummaryObj = (array_key_exists($qual->id, $qualSummary) ? $qualSummary[$qual->id] : new stdClass());                    
                //are they on the qual?
                //if yes: Then find their ceta
                //if no then skip
                if(bcgt_is_user_on_qual($qual->id, $student->id))
                {
                    if(array_key_exists($qual->id, $studentCount))
                    {
                        $count = $studentCount[$qual->id];
                        $count++;
                    }
                    else
                    {
                        $count = 1;
                    }
                    $studentCount[$qual->id] = $count;
                    //get the latest ceta for this qual.
                    $grade = false;
                    $gradeRanking = $this->get_ranking_grade($qual->id, $student->id, $grade);
                                                            
                    if(isset($this->options['targetgrade']))
                    {
                        $targetGradeObj = $this->get_target_grade_obj($student->id, $qual->id, 'target');
                    }

                    if(isset($this->options['weightedgrade']))
                    {
                        $wTargetGradeObj = $this->get_target_grade_obj($student->id, $qual->id, 'weighted');
                    }

                    $targetRanking = null;
                    if( (isset($targetGradeObj) && $targetGradeObj) || (isset($wTargetGradeObj) && $wTargetGradeObj) )
                    {
                        if($wTargetGradeObj)
                        {
                            $targetRanking = $wTargetGradeObj->get_ranking();
                        }
                        else
                        {
                            $targetRanking = $targetGradeObj->get_ranking();
                        }    
                    }
                    $aheadBehindClass = $userVA->ahead_behind_on($targetRanking, $gradeRanking);
                    //create the cell with the correct class, content  
                    if(isset($this->options['targetgrade']))
                    {
                        $content = $targetGradeObj ? $targetGradeObj->get_grade() : "";
                        $data[] = $this->create_cell(array("content"=>$content, "class"=>$aheadBehindClass));
                    }
                    if(isset($this->options['weightedgrade']))
                    {
                        $content = $wTargetGradeObj ? $wTargetGradeObj->get_grade() : "";
                        $data[] = $this->create_cell(array("content"=>$content, "class"=>$aheadBehindClass));
                    }
                                        
                    if($grade && $grade->id > 0)
                    {
                        
                        $data[] = $this->create_cell(array("content"=>$grade->$column, "class"=>$aheadBehindClass));
                        
                        //now we need to total up the number of grades. 
                        //make sure we know that this grade has been found.
                        $totalGrades[$grade->ranking] = $grade->$column;
                        $param = $grade->$column;
                        $this->increment_qual_grade_count($qualSummaryObj, $param);
                        //NOTE: The below array is really a duplicate of the above array. Below are all in one array that is added
                        //to the object.above are all added straight to the object. 
                        //WHY DID I DO THIS?
                        $this->increment_qual_grades_array($qualSummaryObj, $param);
                       
                        $this->increment_student_grades_count($studentSummaryObj, $param);
                        
                        //IF we are looking at a GRADE, then we need to convert this object to a Target Grade
                        //so we can get the UCas Points
                        if($this->displayStudentSummary)
                        {
                            $ucasPoints = 0;
                            //then we want to show ucas points AND VA
                            if($this->options['gradeoption'] != 'ceta')
                            {
                                //then we need to convert the value object into a 
                                //target qual object
                                $targetGradeObj = TargetGrade::get_obj_from_grade($grade->shortvalue, $gradeRanking, $grade->bcgttargetqualid);
                                if($targetGradeObj)
                                {
                                    $ucasPoints = $targetGradeObj->get_ucas_points();
                                }
                            }
                            else
                            {
                                $ucasPoints = $grade->ucaspoints;
                            }
                            
                            //now we want to increment the ucaspoints and number of grades
                            $this->increment_student_ucas($studentSummaryObj, $ucasPoints);
                            
                            //want to show the VA
                            $valueAdded = $gradeRanking - $targetRanking;
                            //now we want to increment the value added and number of grades
                            $this->increment_student_va($studentSummaryObj, $valueAdded);
                            
                        }
                        $this->increment_student_total_grades_count($studentGradeCountArray, $param);
                    }
                    else
                    {
                        $data[] = $this->create_cell(array("content"=>"N/A"));
                        $qualGradeCount = (isset($qualSummaryObj->nograde) ? $qualSummaryObj->nograde : 0);
                        $qualGradeCount++;
                        $qualSummaryObj->nograde = $qualGradeCount;

                        $studentGradeCount = (isset($studentSummaryObj->nograde) ? $studentSummaryObj->nograde : 0);
                        $studentGradeCount++;
                        $studentSummaryObj->nograde = $studentGradeCount;
                        
                        $this->increment_student_total_grades_count($studentGradeCountArray, 'NA');
                    }
                }
                else
                {
                    //user isnt on qual. so lets output the blank.
                    if(isset($this->options['targetgrade']))
                    {
                        $data[] = $this->create_cell(array("content"=>""));
                    }
                    if(isset($this->options['weightedgrade']))
                    {
                        $data[] = $this->create_cell(array("content"=>""));
                    }
                    $data[] = $this->create_cell(array("content"=>""));
                }
                $qualSummary[$qual->id] = $qualSummaryObj;
            }
            $studentSummary[$student->id] = $studentSummaryObj;
            $studentData[$student->id] = $data;
        }
                        
    }
    
    protected function build_display_non_subject_report($students, 
            &$currentMaxQuals, $column, $export, &$studentData)
    {
        $displayType = $this->displayType;
        $userVA = new userVA();
        //we just want to search for each student
        foreach($students AS $student)
        {
            $data = array();
            //output their username etc
            $data[] = $this->create_cell(array("content"=>$student->username));
            $data[] = $this->create_cell(array("content"=>$student->firstname));;
            $data[] = $this->create_cell(array("content"=>$student->lastname));
            
            //need to get the users quals and makesure we know the max number of
            //quals a student is on. Then we can make sure that if a student is on 3 quals, but 
            //another student has 5, then the two different are empty/blank and not simply null.
            //what quals are the students on?
            $quals = bcgt_get_users_quals($student->id);
            if($quals)
            {
                if($currentMaxQuals < count($quals))
                {
                    //find he current maximum number of quals
                    $currentMaxQuals = count($quals);
                }
                foreach($quals AS $qual)
                {
                    
                    //so if we are doing 
                    $grade = null;
                    $gradeRanking = $this->get_ranking_grade($qual->id, $student->id, $grade);
                    $targetGradeObj = null;
                    $wTargetGradeObj = null;
                    if(isset($this->options['targetgrade']))
                    {
                        $targetGradeObj = $this->get_target_grade_obj($student->id, $qual->id, 'target');
//                        $this->get_target_grade_cell($student->id, $qual->id, $gradeRanking, $data, 'target', $aheadBehindClass, $gradeRanking);       
                    }

                    if(isset($this->options['weightedgrade']))
                    {
                        $wTargetGradeObj = $this->get_target_grade_obj($student->id, $qual->id, 'weighted');
//                        $this->get_target_grade_cell($student->id, $qual->id, $gradeRanking, $data, 'weighted', $aheadBehindClass, $gradeRanking); 
                    }

                    if($displayType == 'byoption')
                    {
                        $data[] = $this->create_cell(array("content"=>Level::get_short_version($qual->levelid).
                            ' '.$qual->subtypeshort.' '.$qual->name, 
                            "colspan"=>1));
                    }
                    
                    $targetRanking = null;
                    if($targetGradeObj || $wTargetGradeObj)
                    {
                        if($wTargetGradeObj)
                        {
                            $targetRanking = $wTargetGradeObj->get_ranking();
                        }
                        else
                        {
                            $targetRanking = $targetGradeObj->get_ranking();
                        }    
                    }
                    $aheadBehindClass = $userVA->ahead_behind_on($targetRanking, $gradeRanking);
                    //create the cell with the correct class, content  
                    if(isset($this->options['targetgrade']))
                    {
                        $content = $targetGradeObj ? $targetGradeObj->get_grade() : "";
                        $data[] = $this->create_cell(array("content"=>$content, "class"=>$aheadBehindClass));
                    }
                    if(isset($this->options['weightedgrade']))
                    {
                        $content = $wTargetGradeObj ? $wTargetGradeObj->get_grade() : "";
                        $data[] = $this->create_cell(array("content"=>$content, "class"=>$aheadBehindClass));
                    }
                    //now we can add the 
                    
                    $cellContent = ''; 
                    //if we arent exporting then add the extra css and html
                    if(!$export)
                    {
                        $cellContent .= '<div class="cellcent">';
                    }
                    if($grade)
                    {
                        $cellContent .= $grade->$column;
                    }
                    else 
                    {
                        $cellContent .= 'N/A';
                    }
                    if(!$export)
                    {
                        $cellContent .= '</div>';
                    }
                    $data[] = $this->create_cell(array("content"=>$cellContent, "class"=>$aheadBehindClass));
                }
            }
            $studentData[$student->id] = $data;
        }
    }
    
    protected function get_ranking_grade($qualID, $studentID, &$grade)
    {
        $gradeRanking = null;
        if($this->options['gradeoption'] == 'ceta')
        {
            $grade = Qualification::get_current_ceta($qualID, $studentID);
            if($grade)
            {
                $gradeRanking = $grade->ranking;
            }
        }
        else
        {
            $grade = Qualification::get_current_fa_grade($qualID, $studentID); 
            if($grade)
            {
                $gradeRanking = $grade->ranking;
            }
        }
        return $gradeRanking;
    }
    
    protected function get_target_grade_cell($studentID, $qualID, $gradeRanking, &$data, $type, &$aheadBehindClass, &$targetRanking)
    {
        //get the users target grade. 
        if($type == 'target')
        {
            $targetGrade = UserCourseTarget::get_users_target_grade($studentID, $qualID);
        }
        elseif($type == 'weighted')
        {
            $targetGrade = UserCourseTarget::get_users_weighted_target_grade($studentID, $qualID);
        }
        if($targetGrade)
        {
            $targetRanking = $targetGrade->get_ranking();
            $data[] = $this->build_target_grade_cell($targetGrade->get_grade(), $targetRanking, $gradeRanking);
        }
        else
        {
            //no target grade so make a blank cell.
            $data[] = $this->create_cell(array("content"=>""));
        }
    }
    
    protected function build_target_grade_cell($grade, $targetRanking, $gradeRanking)
    {
        $userVA = new userVA();
        //get the class to check if they are ahead, begind or on.
        $aheadBehindClass = $userVA->ahead_behind_on($targetRanking, $gradeRanking);
        //create the cell with the correct class, content 
        return $this->create_cell(array("content"=>$grade, "class"=>$aheadBehindClass));
    }
    
    protected function get_target_grade_obj($studentID, $qualID, $type)
    {
        if($type == 'target')
        {
            $targetGrade = UserCourseTarget::get_users_target_grade($studentID, $qualID);
        }
        elseif($type == 'weighted')
        {
            $targetGrade = UserCourseTarget::get_users_weighted_target_grade($studentID, $qualID);
        }
        return $targetGrade;
    }
    
    protected function increment_qual_grade_count(&$qualSummaryObj, $param)
    {
        //now we need to know if this count for this grade for this qual has been set before. 
        //eg. qualID of 5, has the grade of C used 5 times. 
        if (!is_null($param)){
            $qualGradeCount = (isset($qualSummaryObj->{$param}) ? $qualSummaryObj->{$param} : 0);
            $qualGradeCount++; 
            $qualSummaryObj->{$param} = $qualGradeCount;
        }
    }
    
    protected function increment_qual_grades_array(&$qualSummaryObj, $param)
    {
        //an array of the grade to number occured. 
        if (!is_null($param)){
            $qualGrades = (isset($qualSummaryObj->qualGrades) ? $qualSummaryObj->qualGrades : array());
            $qualGradesCount = (isset($qualGrades[$param]) ? $qualGrades[$param] : 0);
            $qualGradesCount++;
            $qualGrades[$param] = $qualGradesCount;
            $qualSummaryObj->qualGrades = $qualGrades;
        }
    }
    
    protected function increment_student_grades_count(&$studentSummaryObj, $param)
    {
        //now we need to know how many grades this student has. 
        //e.g across all quals, they have three C and 1 U
        if (!is_null($param)){
            $studentGradeCount = (isset($studentSummaryObj->{$param}) ? $studentSummaryObj->{$param} : 0);
            $studentGradeCount++;
            $studentSummaryObj->{$param} = $studentGradeCount;
        }
    }
    
    protected function increment_student_ucas(&$studentSummaryObj, $ucaspoints)
    {
        //now we need to know how many grades this student has. 
        //e.g across all quals, they have three C and 1 U
        $totalUcas = (isset($studentSummaryObj->ucas) ? $studentSummaryObj->ucas : 0);
        $totalUcas = $totalUcas + $ucaspoints;
        $studentSummaryObj->ucas = $totalUcas;
        
        $totalUcasCount = (isset($studentSummaryObj->ucascount) ? $studentSummaryObj->ucascount : 0);
        $totalUcasCount++;
        $studentSummaryObj->ucascount = $totalUcasCount;
    }
    
    protected function increment_student_va(&$studentSummaryObj, $valueAdded)
    {
        //now we need to know how many grades this student has. 
        //e.g across all quals, they have three C and 1 U
        $va = (isset($studentSummaryObj->va) ? $studentSummaryObj->va : 0);
        $va = $va + $valueAdded;
        $studentSummaryObj->va = $va;
        
        $totalVACount = (isset($studentSummaryObj->vacount) ? $studentSummaryObj->vacount : 0);
        $totalVACount++;
        $studentSummaryObj->vacount = $totalVACount;
    }

    protected function increment_student_total_grades_count(&$studentGradeCountArray, $param)
    {
        //now we need to know how many times this 
        //grade has been awarded
        $count = (isset($studentGradeCountArray[$param]) ? $studentGradeCountArray[$param] : 0);
        $count++;
        $studentGradeCountArray[$param] = $count;
    }
    
    protected function build_header($qualifications, $currentMaxQuals, 
            &$header, &$splitHeader, &$row, &$row2)
    {
        $colCount = 1;
        $this->build_stu_header($splitHeader, $colCount, $header, $row, $row2);
        
        $gradeCell = 'G';
        if($this->options['gradeoption'] == 'ceta')
        {
            $gradeCell = 'C';
        }
        $displayType = $this->displayType;
        if($displayType == 'bysubject')
        {
            foreach($qualifications AS $qual)
            {
                if($splitHeader)
                {
                    $rowObj = new stdClass();
                    $rowObj->content = $this->get_qual_report_header($qual);
                    $rowObj->colCount = $colCount;
                    $row[] = $rowObj;
                    $this->build_grade_header($row2, $gradeCell);
                }
                else
                {
                    $header[] = $this->get_qual_report_header($qual);
                }
            }
        }
        else
        {
            if($displayType == 'byoption')
            {
                $colCount++;
            }
            //need to know the total number of subjects?
            $letter = 'A';
            for($i=0;$i<$currentMaxQuals;$i++)
            {
                if($splitHeader)
                {
                    if($displayType == 'byoption')
                    {
                        $tgObj = new stdClass();
                        $tgObj->content = 'Qual';
                        $tgObj->colCount = 1;
                        $row2[] = $tgObj;
                    }
                    $rowObj = new stdClass();
                    $rowObj->content = 'Subject'.$letter++;
                    $rowObj->colCount = $colCount;
                    $row[] = $rowObj;   
                    $this->build_grade_header($row2, $gradeCell);
                }
                else
                {
                    $header[] = 'Subject'.$letter++;
                }
            }
        }
    }
    
    protected function build_stu_header(&$splitHeader, &$colCount, 
            &$header, &$row, &$row2)
    {
        if((isset($this->options['targetgrade']) && $this->options['targetgrade']) 
                || (isset($this->options['weightedgrade']) && $this->options['weightedgrade']))
        {
            $this->splitHeader = true;
            $this->freezepane = 'D3';
            $splitHeader = true;
            $colCount = isset($this->options['targetgrade']) && isset($this->options['weightedgrade']) ? 3 : 2;
            //then we are having a colspan header.
            //therefore header becomes an array of rows.
            $rowObj = new stdClass();
            $rowObj->content = get_string('username');
            $rowObj->colCount = 1;
            $row[] = $rowObj;
            $rowObj = new stdClass();
            $rowObj->content = get_string('firstname');
            $rowObj->colCount = 1;
            $row[] = $rowObj;
            $rowObj = new stdClass();
            $rowObj->content = get_string('lastname');
            $rowObj->colCount = 1;
            $row[] = $rowObj;    
            
            $blankObj = $this->set_up_blank_object();
            $row2[] = $blankObj;
            $row2[] = $blankObj;
            $row2[] = $blankObj;
        }
        else
        {
            $header = array();
            $header[] = get_string('username');
            $header[] = get_string('firstname');
            $header[] = get_string('lastname');
        }
    }
    protected function build_grade_header(&$row2, $gradeCell)
    {
        if(isset($this->options['targetgrade']))
        {
            $tgObj = new stdClass();
            $tgObj->content = 'TG';
            $tgObj->colCount = 1;
            $row2[] = $tgObj;
        }
        if(isset($this->options['weightedgrade']))
        {
            $tgObj = new stdClass();
            $tgObj->content = 'STG';
            $tgObj->colCount = 1;
            $row2[] = $tgObj;
        }
        $tgObj = new stdClass();
        $tgObj->content = $gradeCell;
        $tgObj->colCount = 1;
        $row2[] = $tgObj;
    }
    
    protected function build_summary($totalGrades, $qualifications, 
            &$studentData, &$header, $students, &$row, &$row2)
    {
        //are we displaying the qual summary?
        $displayQualSummary = (isset($this->options['showqgradesum']) ? $this->options['showqgradesum'] : false);
        //are we displaying the student summary?
        $displayStudentSummary = (isset($this->options['showsgradesum']) ? $this->options['showsgradesum'] : false);
        $this->displayStudentSummary = $displayStudentSummary;
        //are we combing the split grades into whole grades?
        $displayNoSplitSummary = (isset($this->options['showqgradesplitsum']) ? $this->options['showqgradesplitsum'] : false);
        
        //lets create a blank cell for use as a spacer. 
        $blankCell = $this->create_cell(array("content"=>""));
        $displayType = $this->displayType;
        //only create the summary if the display type is by subject
        if($displayType == 'bysubject' && ($displayQualSummary || $displayStudentSummary || $displayNoSplitSummary))
        {
            //username, firstname and surname
            $blankRow = array($blankCell,$blankCell,$blankCell); 
            $gradesBlank = array();
            //need to sort by their ranking which is the key
            krsort($totalGrades);
            $this->totalGrades = $totalGrades;
            
            //blank rows may be needed for such things as blank rows and blank columns
            $this->set_up_blank_columns_rows($blankRow, 
                    $gradesBlank, $qualifications, $blankCell);

            if($displayQualSummary)
            {
                //qual summary with the student count and number of each
                //grade that appears for each qual
                $this->set_up_qual_summary($blankCell, $qualifications, 
                $gradesBlank, $blankRow, $studentData);
            }
            elseif($displayStudentSummary) {
                //along the same line as the total student count, we want the
                //sum of how many times each grade has been awarded to a student
                //if qual summary doesnt exist, then this row doesnt exist
                $data = array();
                $data[] = $blankCell;
                $data[] = $blankCell;
                $data[] = $blankCell;
                foreach($qualifications AS $qual)
                {
                    //how many students:
                    $this->add_blank_target_cells($data, $blankCell);
                    $data[] = $blankCell;
                }
                $data[] = $this->create_cell(array("content"=>"Total Grades")); //one extra for the blank column.
                $this->create_student_summary_grade_totals($data, $blankCell);
            }
            
            if($displayNoSplitSummary)
            {
                //number of whole grades that appear for each qual
                $this->set_up_no_split_summary($qualifications, $blankRow, 
                $studentData, $blankCell, $gradesBlank);
            }
            //END OF EXTRA ROWS!!!!
            
            //START EXTRA COLUMNS
            //now add the summary data for the students on the end. 
            if($displayStudentSummary)
            {
                //number of each grade that a student has
                $this->set_up_student_summary($header, $students, $row, $row2, 
                $studentData, $blankCell);
            } 
        }
        
        if($displayType != 'bysubject') //its not display a column per sibject its the other displays. 
        {
            //for example. If a student is on 3 quals, but another student has say 5 quals, 
            //then we need some blank spaces. 
            $this->set_up_student_subjects($students, $studentData, $blankCell);
        }
    }
    
    protected function set_up_blank_columns_rows(&$blankRow, 
            &$gradesBlank, $qualifications, $blankCell)
    {
        //the total grades is an array of objects with the ranking as the key 
        if($this->displayStudentSummary)
        {
            //if we are displaying the student summary, then there is a spare column after the quals
            //before the student summary.
            $gradesBlank = array($blankCell);//one extra for the blank column
        }

        //SO FIRST we want the qualification summary.
        //this is the number of grades that occur in that
        //qualification. e.g. B occurs 3 times, B/C occurs 10 times. 

        //now we can add the summary         
        //create a blank row
        //then count the no of students on each qual.
        //then put put the number at each grade, for each qual. 
        foreach($this->totalGrades AS $ranking=>$grade)
        {
            //gradesBlank is the spacer between the student summary and the qualification data
            //the vertical column. 
            $gradesBlank[] = $blankCell;
            if($this->displayStudentSummary)
            {
                //for each grade we will need an extra space for this
                //greade to appear for the student summary.                 
                $blankRow[] = $blankCell;
            }

        }

        foreach($qualifications AS $qual)
        {
            //need a blank column for each qual in the blank row.
            $blankRow[] = $blankCell;
            if(isset($this->options['targetgrade']))
            {
                $blankRow[] = $blankCell;
            }
            if(isset($this->options['weightedgrade']))
            {
                $blankRow[] = $blankCell;
            }
        }
        if($this->displayStudentSummary)
        {
            $blankRow[] = $blankCell;//one extra for the blank column
            $blankRow[] = $blankCell;//one extra for the nograde column
        }
    }
    
    protected function set_up_qual_summary($blankCell, $qualifications, 
            &$gradesBlank, &$blankRow, &$studentData)
    {
        $data = array();
        $data[] = $blankCell;
        $data[] = $this->create_cell(array("content"=>"Students : "));
        $data[] = $blankCell;
        $studentCount = $this->studentCount;
        foreach($qualifications AS $qual)
        {
            //how many students:
            $this->add_blank_target_cells($data, $blankCell);
            if(array_key_exists($qual->id, $studentCount))
            {
                $data[] = $this->create_cell(array("content"=>$studentCount[$qual->id]));
            }
            else
            {
                $data[] = $this->create_cell(array("content"=>0));
            }
        }
        if($this->displayStudentSummary)
        {           
//            $this->add_student_summary_cells($data, $blankCell, $gradesBlank);
            $data[] = $this->create_cell(array("content"=>"Total Grades"));
            $this->create_student_summary_grade_totals($data, $blankCell);
        }

        //add a blank row for the overall result
        $studentData[] = $blankRow;
        //add the data for the overall result.
        $studentData[] = $data;

        //now for the actual grades
        $qualSummary = $this->qualSummary;
        foreach($this->totalGrades AS $ranking=>$grade)
        {
            $data = array();
            $data[] = $blankCell;
            //e,g. (A) or {C/B}
            $data[] = $this->create_cell(array("content"=>'('.$grade.') : '));
            $data[] = $blankCell;
            foreach($qualifications AS $qual)
            {
                if(array_key_exists($qual->id, $qualSummary))
                {
                    $this->add_blank_target_cells($data, $blankCell);
                    $qualSummaryObj = $qualSummary[$qual->id];
                    if(isset($qualSummaryObj->{$grade}))
                    {
                        $data[] = $this->create_cell(array("content"=>$qualSummaryObj->{$grade}));
                    }
                    else
                    {
                        $data[] = $blankCell;
                    }
                }
                else
                {
                    $this->add_blank_target_cells($data, $blankCell);
                    $data[] = $blankCell;
                }
            }
            if($this->displayStudentSummary)
            {
                $this->add_student_summary_cells($data, $blankCell, $gradesBlank);
            } 
            $studentData[] = $data;
        }

        $data = array();
        $data[] = $blankCell;
        $data[] = $this->create_cell(array("content"=>'(No Grade) : '));
        $data[] = $blankCell;
        foreach($qualifications AS $qual)
        {
            if(array_key_exists($qual->id, $qualSummary))
            {
                $this->add_blank_target_cells($data, $blankCell);
                $qualSummaryObj = $qualSummary[$qual->id];
                if(isset($qualSummaryObj->nograde))
                {
                    $data[] = $this->create_cell(array("content"=>$qualSummaryObj->nograde));
                }
                else
                {
                    $data[] = $blankCell;
                }
            }
            else
            {
                $this->add_blank_target_cells($data, $blankCell);
                $data[] = $blankCell;
            }
        }
        if($this->displayStudentSummary)
        {
            $this->add_student_summary_cells($data, $blankCell, $gradesBlank);
            //also need ones for the ucas and va
        }

        $studentData[] = $data;
        //that ends the grades rows. 
    }
    
    protected function add_blank_target_cells(&$data, $blankCell)
    {
        if(isset($this->options['targetgrade']))
        {
            $data[] = $blankCell;
        }
        if(isset($this->options['weightedgrade']))
        {
            $data[] = $blankCell;
        }
    }
    
    protected function add_student_summary_cells(&$data, $blankCell, $gradesBlank)
    {
        //if we are showing the studentSummary, then after the studentCounts (e.g. qual 1 has 5 stu)
        //then there needs to be a set number of blanks for the student summary. 
        //the student summary contains a column for every possible grade that a student could get
        //plus one for no grade 
        $data[] = $blankCell;//one for extra column
        $data[] = $blankCell; //one extra for the no grade
        $data = array_merge($data, $gradesBlank);
        $data[] = $blankCell;//one for extra column between summary and ucas
        $data[] = $blankCell;//two for ucas
        $data[] = $blankCell;//two for ucas
        $data[] = $blankCell;//one for extra column between ucas and va
        $data[] = $blankCell;//two for va
        $data[] = $blankCell;//two for va
    }
    
    protected function set_up_no_split_summary($qualifications, $blankRow, 
            &$studentData, $blankCell, $gradesBlank)
    {
        $qualSummary = $this->qualSummary;
        $totalGrades = $this->totalGrades;
        //we now want to add summary data for the grades where they are not split.
        foreach($qualifications AS $qual)
        {
            if(array_key_exists($qual->id, $qualSummary))
            {
                $qualSummaryObj = $qualSummary[$qual->id];
                //this is an array
                if(isset($qualSummaryObj->qualGrades))
                {
                    //we want to be able to take a B/C and a C/B and put the relevant ratios
                    //to the actual B and C grades. 
                    $noSplitGrades = process_split_grades($qualSummaryObj->qualGrades, $qual->bcgttargetqualid, $totalGrades);
                    $qualSummaryObj->noSplitGrades = $noSplitGrades;
                    $qualSummary[$qual->id] = $qualSummaryObj;
                }
            }
        }
        $studentData[] = $blankRow;
        //need to resort the keys and totalGrades as some whole grades may have been 
        //re-added to the array
        krsort($totalGrades);
        

        foreach($totalGrades AS $rank=>$grade)
        {
            //is it a whole grade? or does it have a decimal place of 0
            $whole = floor($rank);
            if(is_int($rank) || $rank - $whole == 0)
            {
                $data = array($blankCell);
                $data[] = $this->create_cell(array("content"=>'('.$grade.')'));
                $data[] = $blankCell;
                foreach($qualifications AS $qual)
                {
                    if(array_key_exists($qual->id, $qualSummary))
                    {
                        $qualSummaryObj = $qualSummary[$qual->id];
                        //this is an array
                        if(isset($qualSummaryObj->noSplitGrades))
                        {
                            $this->add_blank_target_cells($data, $blankCell);
                            $noSplitGrades = $qualSummaryObj->noSplitGrades;
                            if(isset($noSplitGrades[$grade]))
                            {
                                $data[] = $this->create_cell(array("content"=>$noSplitGrades[$grade]));
                            }
                            else
                            {
                                $data[] = $blankCell;
                            }
                        }
                        else
                        {
                            $this->add_blank_target_cells($data, $blankCell);
                            $data[] = $blankCell;
                        }
                    }
                    else
                    {
                        $this->add_blank_target_cells($data, $blankCell);
                        $data[] = $blankCell;
                    }

                }
                if($this->displayStudentSummary)
                {
                    $this->add_student_summary_cells($data, $blankCell, $gradesBlank);
                }
                $studentData[] = $data;
            }//end is int
        }
    }
    
    protected function set_up_student_summary(&$header, $students, &$row, &$row2, 
            &$studentData, $blankCell)
    {
        //blank column
        if((isset($this->options['targetgrade']) && $this->options['targetgrade']) 
        || (isset($this->options['weightedgrade']) && $this->options['weightedgrade']))
        {
            $blankObj = $this->set_up_blank_object();
            $row[] = $blankObj;
            $row2[] = $blankObj;

            foreach($this->totalGrades AS $ranking=>$grade)
            {
                $row[] = $this->create_header_cell($grade, 1);
                $row2[] = $blankObj;
            }
            $row[] = $this->create_header_cell('No Grade', 1); 
            $row2[] = $blankObj;
            
            //now we need to add the ucas points stuff
            //and the value added. 
            //blank cell
            //ucas avg
            //ucas total
            //blank cell
            //va avg
            //va total
            $row[] = $blankObj; 
            $row2[] = $blankObj;
            $row[] = $this->create_header_cell('UCAS (AVG)', 1);
            $row2[] = $blankObj;
            $row[] = $this->create_header_cell('UCAS (SUM)', 1);
            $row2[] = $blankObj;
            $row[] = $blankObj;
            $row2[] = $blankObj;
            $row[] = $this->create_header_cell('VA (AVG)', 1);
            $row2[] = $blankObj;
            $row[] = $this->create_header_cell('VA (SUM)', 1);
            $row2[] = $blankObj;
            
        }
        else
        {
            $header[] = '';
            foreach($this->totalGrades AS $ranking=>$grade)
            {
                $header[] = $grade;
            }
            $header[] = 'No Grade';
            $header[] = '';
            $header[] = 'UCAS (AVG)';
            $header[] = 'UCAS (SUM)';
            $header[] = '';
            $header[] = 'VA (AVG)';
            $header[] = 'VA (SUM)';
        }
        
        

        $studentSummary = $this->studentSummary;
        foreach($students AS $student)
        {
            //now we want to summarise their data
            $studentRow = $studentData[$student->id];
            $studentRow[] = $blankCell;//blank column
            $studentSummaryObj = $studentSummary[$student->id];
            foreach($this->totalGrades AS $ranking=>$grade)
            {
                if(isset($studentSummaryObj->$grade))
                {
                    $studentRow[] = $this->create_cell(array("content"=>$studentSummaryObj->$grade));
                }
                else
                {
                    $studentRow[] = $blankCell;
                }
            }
            if(isset($studentSummaryObj->nograde))
            {
                $studentRow[] = $this->create_cell(array("content"=>$studentSummaryObj->nograde));
            }
            else
            {
                $studentRow[] = $blankCell;
            }
            $studentRow[] = $blankCell; //one for the blank column between student summary and ucas
            //then two ucas
            if(isset($studentSummaryObj->ucas) && isset($studentSummaryObj->ucascount) && $studentSummaryObj->ucascount != 0)
            {
                $studentRow[] = $this->create_cell(array("content"=>round(($studentSummaryObj->ucas/$studentSummaryObj->ucascount), 2)));
                $studentRow[] = $this->create_cell(array("content"=>$studentSummaryObj->ucas));
            }
            else
            {
                $studentRow[] = $blankCell;
                $studentRow[] = $blankCell;
            }
            $studentRow[] = $blankCell; //one for the blank column between ucas and va
            //then two ucas
            if(isset($studentSummaryObj->va) && isset($studentSummaryObj->vacount) && $studentSummaryObj->vacount != 0)
            {
                $studentRow[] = $this->create_cell(array("content"=>round(($studentSummaryObj->va/$studentSummaryObj->vacount), 2)));
                $studentRow[] = $this->create_cell(array("content"=>$studentSummaryObj->va));
            }
            else
            {
                $studentRow[] = $blankCell;
                $studentRow[] = $blankCell;
            }
            $studentData[$student->id] = $studentRow;
        }
    }
    
    protected function set_up_student_subjects($students, &$studentData, $blankCell)
    {
        $displayType = $this->displayType;
        $currentMaxQuals = $this->currentMaxQuals;
        //we need to append the extra blank cells on:
        foreach($students AS $student)
        {
            $studentRow = $studentData[$student->id];
            $studentRow1 = array();
            $quals = bcgt_get_users_quals($student->id);
            if(($quals && count($quals) < $currentMaxQuals) || !$quals)
            {
                $blankCells = count($currentMaxQuals);
                if($quals)
                {
                    $blankCells = $currentMaxQuals - count($quals);
                }
                for($i=0;$i<$blankCells;$i++)
                {
                    $studentRow[] = $blankCell;
                    $studentRow1[] = $blankCell;
                    if(isset($this->options['targetgrade']))
                    {
                        $studentRow[] = $blankCell;
                        $studentRow1[] = $blankCell;
                    }
                    if(isset($this->options['weightedgrade']))
                    {
                        $studentRow[] = $blankCell;
                        $studentRow1[] = $blankCell;
                    }
                }
            }
            $studentData[$student->id] = $studentRow;
        }
            
    }
    
    function create_student_summary_grade_totals(&$data, $blankCell)
    {
        //need to output the grades. 
        
        //so loop over the targetgrades
        //for each, find the total number.
        $studentGradeCountArray = $this->studentGradeCountArray;
        foreach($this->totalGrades AS $ranking=>$grade)
        {
            if(isset($studentGradeCountArray[$grade]))
            {
                $data[] = $this->create_cell(array("content"=>$studentGradeCountArray[$grade]));
            }
            else
            {
                $data = $blankCell;
            }
        }
        $data[] = $blankCell; //one for the no grade
        $data[] = $blankCell;//one for extra column between summary and ucas
        $data[] = $blankCell;//two for ucas
        $data[] = $blankCell;//two for ucas
        $data[] = $blankCell;//one for extra column between ucas and va
        $data[] = $blankCell;//two for va
        $data[] = $blankCell;//two for va
        
    }
}

?>
