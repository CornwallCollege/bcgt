<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FAOverview
 *
 * @author mchaney
 */
class FAOverview extends CoreReports{
    //put your code here
    //put your code here
    private $freezepane = 'D3';
    private $frozencolumnsJS = 3;
    private $frozenColumnsWidth = 280;
    private $splitHeader = true;
    private $applyDataTables = true;
    
    private $canrun = true;
    private $canexport = true;
    
    function FAOverview()
    {
        
    }
    
    function get_name()
    {
        return "Formal Assessment: Overview";
    }
    
    function get_description()
    {        
        $retval = '<p>All Students and their formal assessment grades</p>';
        $retval .= '<p>Options include : </p>';
        $retval .= '<ul>';
        $retval .= '<li>Select one, all or a selection</li>';
        $retval .= '<li>View all subjects or student subjects</li>';
        $retval .= '<li>Sort options: </li>';
        $retval .= '<ul>';
        $retval .= '<li>Formal Assessments and then Qualifications</li>';
        $retval .= '<li>Qualifications and then Formal Assessments</li>';
        $retval .= '</ul>';
        $retval .= '</ul>';
        return $retval;
    }
    
    function display_options()
    {
        
        $out = '<div>';
        $out .= '<div>';
        $out .= $this->get_core_options();
        $out .= '</div>';
        $out .= '<div id="customOptions">';
        $out .= $this->get_extra_JS();
        $out .= $this->get_custom_options();
        $out .= '</div>';
        $out .= '<div>';
        $out .= $this->get_display_selectors();
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
    
    function get_examples()
    {
        global $CFG;
        $retval = '<div id="reportexaples">';
        $retval .= '<h2>Report Options Explained</h2>';
        $retval .= '<p>After selecting the filters, (Categories, Family, Levels and Subtypes), then select which Formal Assessments the overview will contain.
            Choose if its the Grade or Ceta and which students to show. The Report Sort and Display Options are explained below:</p>';
        $retval .= '<h3>Report Sort</h3>';
        $retval .= '<h4>Formal Assessment then Qualification</h4>';
        $retval .= '<p>This has the all Qualification Grades together for each Formal Assessment selected</p>';
        $retval .= '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/fa_overview_sort1_example.jpg"/>';
        
        $retval .= '<h4>Qualification then Formal Assessment</h4>';
        $retval .= '<p>This has the all Formal Assessment Grades together for each Qualification</p>';
        $retval .= '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/fa_overview_sort2_example.jpg"/>';
        
        $retval .= '<h3>Display Options</h3>';
        $retval .= '<h4>Anonymous - No Qualification Information</h4>';
        $retval .= '<p>This shows generic columns for SubjectA, SubjectB, SubjectC etc</p>';
        $retval .= '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/fa_overview_display1_exampl.jpg"/>';

        $retval .= '<h4>Options - The Users Qualifications</h4>';
        $retval .= '<p>This shows sections for QualificationA, QualificationB, etc. But an extra column contains the Qualification Name</p>';
        $retval .= '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/fa_overview_display3_exampl.jpg"/>';
        
        $retval .= '<h4>Every Qualification - Every Qualification Has A Section</h4>';
        $retval .= '<p>This shows sections for every possible Qualification. If the user is not on this Qualification then blanks are produced. This is a very slow generating report.</p>';
        $retval .= '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/fa_overview_display2_exampl.jpg"/>';

        
        $retval .= '</div>';
        return $retval;
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
        return $this->applyDataTables;
    }
    
    function get_icon()
    {
        global $CFG;
        return '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/formal_assessment_over_thum.jpg"/>';
    }
    function get_image()
    {
        global $CFG;
        return '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/formal_assessment_overview_.jpg"/>';
    }
    
    function get_custom_options()
    {
        $out = '';
        $formalAssessments = Project::get_all_projects(null, ' ORDER BY targetdate ASC');
        if($formalAssessments)
        {
            $this->assessments = $formalAssessments;
            $out .= '<div class="assessments">';
            $out .= '<h2>'.get_string('formalassessments','block_bcgt').'</h2>';
            foreach($formalAssessments AS $fa)
            {
                $checked = '';
                $out .= '<span>';
                if(isset($_POST['fa_'.$fa->get_id()]))
                {
                    $checked = 'checked';
                    $assessmentArray = isset($this->options['assessments']) ? $this->options['assessments'] : array();
                    $assessmentArray[$fa->get_id()] = $fa->get_id();
                    $this->options['assessments'] = $assessmentArray;
                }
                $out .= $fa->get_name().' <input type="checkbox" '.$checked.' name="fa_'.$fa->get_id().'"/>';
                $out .= '</span>';
            }
            $out .= '</div>';
        }
        
        $out .= $this->get_grade_filter(false);
        $out .= $this->get_my_students_filter();
        return $out;
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
                    if(selectVal == "bysubject" || selectVal == "byoption")
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
    
    function get_display_selectors()
    {
        $out = '<div>';
        //the three options
        $out .= get_string('reportsort', 'block_bcgt');
        if(isset($_POST['reportsort']))
        {
            $this->options['reportsort'] = $_POST['reportsort'];
        }
        $out .= '<select name="reportsort">';
        $out .= '<option value="faqual" '.((isset($_POST['reportsort']) && $_POST['reportsort'] == 'faqual') ? "selected": "").'>Formal Assessments then Qualifications</option>';
        $out .= '<option value="qualfa" '.((isset($_POST['reportsort']) && $_POST['reportsort'] == 'qualfa') ? "selected": "").'>Qualifications then Formal Assessments</option>';
        $out .= '</select>';
        $out .= '</div>';
        
        $out .= '<div>';
        //the three options
        $out .= get_string('displayoptions', 'block_bcgt');
        if(isset($_POST['displayoptions']))
        {
            $this->options['displayoption'] = $_POST['displayoptions'];
            if($_POST['displayoptions'] == 'bysubject' || $_POST['displayoptions'] == 'byoption')
            {
                $this->canrun = false;
            }
        }
        $out .= '<select id="displayoptions" name="displayoptions">';        
        $out .= '<option value="anonymous" '.((isset($_POST['displayoptions']) && $_POST['displayoptions'] == 'anonymous') ? "selected": "").'>Anonymous: No Qualification Information</option>';
        $out .= '<option value="byoption" '.((isset($_POST['displayoptions']) && $_POST['displayoptions'] == 'byoption') ? "selected": "").'>Options: A section per student Qualificaion</option>';
        $out .= '<option value="bysubject" '.((isset($_POST['displayoptions']) && $_POST['displayoptions'] == 'bysubject') ? "selected": "").'>Every Qualification</option>';
        $out .= '</select>';
        $out .= '</div>';
        
        $out .= $this->build_student_selector();
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
        $reportSort = $this->options['reportsort'];
        $displayType = $this->options['displayoption'];
        $this->displayType = $displayType;
        $this->options['oncourse'] = true;
        $this->options['users'] = true;
        $students = bcgt_search_users_report($this->options, array('student'));
        $assessments = isset($this->options['assessments']) ? $this->options['assessments'] : array();
        
        $studentData = array();//3d array
        //so how are we displaying them:
        $header = array();//2d array
        $row1 = array();
        $subHeader = array();
        
        $this->build_header($row1, $subHeader);
        
        /*CREATE AND SORT THE ASSESSMENTS*/
        $assessmentObjects = array();
        //get the assessments and sort them
        foreach($assessments AS $assessmentID)
        {
            $assessmentObj = new Project($assessmentID, null);
            $assessmentObjects[$assessmentID] = $assessmentObj;
        }
        require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ProjectsSorter.class.php');
        $projectSorter = new ProjectsSorter();
        uasort($assessmentObjects, array($projectSorter, "ComparisonDelegateByObjectDueDate"));
        $assessments = array_keys($assessmentObjects);
        
        /**CREATE THE REPORT**/
        if($reportSort == 'faqual')
        {
            $this->create_fa_qual_report($students, $assessments, $row1, $subHeader, $studentData);
        }
        else
        {
            $this->create_qual_fa_report($students, $assessments, $row1, $subHeader, $studentData);
        }//end if display type. 
        
        $header[] = $row1;
        $header[] = $subHeader;
        
        $this->data = $studentData;
        $this->header = $header;
    }
    
    protected function build_header(&$row1, &$subHeader)
    {
        $row1[] = $this->create_header_cell(get_string('username'), 1);
        $row1[] = $this->create_header_cell(get_string('firstname'), 1);
        $row1[] = $this->create_header_cell(get_string('lastname'), 1);

        $subHeader[] = $this->set_up_blank_object();
        $subHeader[] = $this->set_up_blank_object();
        $subHeader[] = $this->set_up_blank_object();
    }
    
    /**
     * The formal assessments and then the qualifications. 
     */
    protected function create_fa_qual_report($students, $assessments, &$row1, &$subHeader, &$studentData)
    {
        if($this->displayType == 'anonymous' || $this->displayType == 'byoption')
        {
            $this->anonymous_fa_qual_report($students, $assessments, $row1, $subHeader, $studentData);
        }///end if display type is annonymous
        else
        {
            //we want the qualification names. 
            $this->subject_fa_qual_report($students, $assessments, $row1, $subHeader, $studentData);
        }
    }
    
    protected function anonymous_fa_qual_report($students, $assessments, &$row1, &$subHeader, &$studentData)
    {
        //we need to know the maximum number of quals, per formal assessment that a student can have:  
        $maxAssessmentQuals = array();
        foreach($students AS $student)
        {
            //we want fas and then quals
            foreach($assessments AS $assessmentID)
            {
                //get the students quals that this assessment is on:
                $quals = Project::get_user_assessment_quals($student->id, $assessmentID);
                //need to know max qual count per formal assessment
                if($quals)
                {
                    $currentMaxQuals = isset($maxAssessmentQuals[$assessmentID]) ? $maxAssessmentQuals[$assessmentID] : 0;
                    $currentMaxQuals = (count($quals) > $currentMaxQuals) ? count($quals) : $currentMaxQuals;
                    $maxAssessmentQuals[$assessmentID] = $currentMaxQuals;
                }
            }
        }
        $this->maxAssessmentQuals = $maxAssessmentQuals;
        $colSpan = 1;
        if($this->displayType == 'byoption')
        {
            $colSpan++;
        }
        //need to build the header. 
        foreach($assessments AS $assessmentID)
        {
            $row1[] = $this->create_header_cell($this->assessments[$assessmentID]->get_name(), ($maxAssessmentQuals[$assessmentID] * $colSpan));
            for($i=0;$i<$maxAssessmentQuals[$assessmentID];$i++)
            {
                $subHeader[] = $this->create_header_cell('Subject '.($i+1), $colSpan);
            }
        }

        foreach($students AS $student)
        {
            $data = array();
            $data[] = $student->username;
            $data[] = $student->firstname;
            $data[] = $student->lastname;
            
            //we want fas and then quals
            foreach($assessments AS $assessmentID)
            {
                //get the students quals that this assessment is on:
                $quals = Project::get_user_assessment_quals($student->id, $assessmentID);
                //need to know max qual count per formal assessment
                if($quals)
                {                        
                    foreach($quals AS $qual)
                    {
                        if($this->displayType == 'byoption')
                        {
                            //qual name
                            $content = $this->get_qual_report_header($qual);
                            $data[] = $this->create_cell(array("content"=>$content,"colspan"=>1));
                        }
                            
                        $content = $this->build_user_project_grade_value($student->id, $assessmentID, $qual->id);
                        $data[] = $this->create_cell(array("content"=>$content,"colspan"=>1));
                        
                    }

                    if(count($quals) < $maxAssessmentQuals[$assessmentID])
                    {
                        $diff = $maxAssessmentQuals[$assessmentID] - count($quals);
                        for($i=0;$i<$diff;$i++)
                        {
                            if($this->displayType == 'byoption')
                            {
                                $data[] = $this->create_cell(array("content"=>'',"colspan"=>1));
                            }
                            $data[] = $this->create_cell(array("content"=>'',"colspan"=>1));
                        }
                    }
                }//no quals on fa for this student
                else
                {
                    //user has no possible grades for this
                    //need to know the maximum number of quals that we should have had
                    for($i=0;$i<$maxAssessmentQuals[$assessmentID];$i++)
                    {
                        if($this->displayType == 'byoption')
                        {
                            $data[] = $this->create_cell(array("content"=>'',"colspan"=>1));
                        }
                        $data[] = $this->create_cell(array("content"=>'',"colspan"=>1));
                    }
                }
            }//end for each assessment
            $studentData[$student->id] = $data;
        }//end for each sudent
    }
    
    protected function subject_fa_qual_report($students, $assessments, &$row1, &$subHeader, &$studentData)
    {
        //header:
        //top is one for every fa
        //bottom is one for qual
        //now we can build the header

        $qualifications = bcgt_search_quals_report($this->options);
        foreach($assessments AS $assessmentID)
        {
            $row1[] = $this->create_header_cell($this->assessments[$assessmentID]->get_name(), count($qualifications));
            foreach($qualifications AS $qual)
            {                    
                $subHeader[] = $this->create_header_cell($this->get_qual_report_header($qual), 1);
            }
        }

        //now the data:
        foreach($students AS $student)
        {
            $data = array();
            $data1 = array();
            $data[] = $student->username;
            $data[] = $student->firstname;
            $data[] = $student->lastname;
            foreach($assessments AS $assessmentID)
            {
                foreach($qualifications AS $qual)
                {
                    $data[] = $this->build_user_project_grade_value($student->id, $assessmentID, $qual->id);
                }
            }
            $studentData[$student->id] = $data;
        }//end for each student    
    }
    
    protected function build_user_project_grade_value($studentID, $assessmentID, $qualID)    
    {
        if($this->options['gradeoption'] == 'ceta')
        {
            $grade = Project::get_user_qual_grade($studentID, $assessmentID, $qualID);
            if($grade)
            {
                return $grade->get_grade();
            }
            else
            {
                return '';
            }
        }
        else
        {
            $value = Project::get_user_qual_value($studentID, $assessmentID, $qualID);
            if($value)
            {
                return $value->get_short_value();
            }
            else
            {
                return '';
            }
        }
    }
    
    protected function create_qual_fa_report($students, $assessments, &$row1, &$subHeader, &$studentData)
    {
        if($this->displayType == 'anonymous' || $this->displayType == 'byoption')
        {
            //we dont need the qualification names. 
            //just qual1, to qual X

            //so for each student, 
            //find the most number of quals that they are on, for each assignment.
            $currentMaxQuals = 0;
            foreach($students AS $student)
            {
                //we want fas and then quals
                foreach($assessments AS $assessmentID)
                {
                    //get the students quals that this assessment is on:
                    $quals = bcgt_get_users_quals_assessments($student->id);
                    //need to know max qual count per formal assessment
                    if($quals)
                    {
                        $currentMaxQuals = (count($quals) > $currentMaxQuals) ? count($quals) : $currentMaxQuals;
                    }
                }
            }
            $extra = 0;
            if($this->displayType == 'byoption')
            {
                $extra++;
            }
            //now we can build the header
            for($i=0;$i<$currentMaxQuals;$i++)
            {
                $row1[] = $this->create_header_cell('Subject '.($i+1), (count($assessments) + $extra));
                if($this->displayType == 'byoption')
                {
                    $subHeader[] = $this->create_header_cell('Qual',1);

                }
                foreach($assessments AS $assessmentID)
                {                    
                    $subHeader[] = $this->create_header_cell($this->assessments[$assessmentID]->get_name(), 1);
                }
            }

            ///now for the data
            //for each student
            //get their quals
            //for each qual
            //for each assessment
            //get the grade or value
            //if they dont have an assessment, then blank,
            //if they have less quals than others, blank.

            foreach($students AS $student)
            {
                $data = array();
                $data[] = $student->username;
                $data[] = $student->firstname;
                $data[] = $student->lastname;
                $quals = bcgt_get_users_quals_assessments($student->id);
                foreach($quals AS $qual)
                {
                    if($this->displayType == 'byoption')
                    {
                        //qual name
                        $content = $this->get_qual_report_header($qual);
                        $data[] = $this->create_cell(array("content"=>$content,"colspan"=>1));
                    }
                    
                    foreach($assessments AS $assessmentID)
                    {                     
                        $content = $this->build_user_project_grade_value($student->id, $assessmentID, $qual->id);
                        $data[] = $this->create_cell(array("content"=>$content,"colspan"=>1));
                    }
                }
                if(count($quals) < $currentMaxQuals)
                {
                    $diff = $currentMaxQuals - count($quals);
                    for($i=0;$i<$diff;$i++)
                    {
                        if($this->displayType == 'byoption')
                        {
                            $data[] = $this->create_cell(array("content"=>"","colspan"=>1));
                        }
                        foreach($assessments AS $assessmentID)
                        {
                            $data[] = $this->create_cell(array("content"=>'',"colspan"=>1));
                        }
                    }
                }
                $studentData[$student->id] = $data;
            }//end for each student

        }
        else
        {
            $assessmentsCount = count($assessments);
            $qualifications = bcgt_search_quals_report($this->options);
            //build the header:
            foreach($qualifications AS $qual)
            {
                $row1[] = $this->create_header_cell($this->get_qual_report_header($qual), $assessmentsCount);
                foreach($assessments AS $assessmentID)
                {
                    $subHeader[] = $this->create_header_cell($this->assessments[$assessmentID]->get_name(),1);
                }
            }
            //we want quals and then fas
            foreach($students AS $student)
            {
                $data = array();
                $data[] = $student->username;
                $data[] = $student->firstname;
                $data[] = $student->lastname;
                foreach($qualifications AS $qual)
                {
                    if(bcgt_is_user_on_qual($qual->id, $student->id))
                    {
                        //then get the formal assessment record
                        foreach($assessments AS $assessmentID)
                        {
                            $data[] = $this->build_user_project_grade_value($student->id, $assessmentID, $qual->id);
                        }//end for each assessment
                    }//end if user is on qual
                    else
                    {
                        //user isnt on this qual
                        //for the count of fa's dont put anything
                        foreach($assessments AS $assessmentID)
                        {
                            $data[] = '';
                        }
                    }
                }//end for each qual
                $studentData[$student->id] = $data;
            }//end for each student
        }
    }
}

?>
