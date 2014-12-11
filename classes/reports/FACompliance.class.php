<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FACompliance
 *
 * @author mchaney
 */
class FACompliance extends CoreReports{
    //put your code here
    private $freezepane = 'D2';
    private $frozencolumnsJS = 3;
    private $frozenColumnsWidth = 280;
    private $splitHeader = false;
    private $applyDataTables = true;

    private $canrun = true;
    private $canexport = true;
    
    function FAStudentCetas()
    {
        $this->applyDataTables = true;
    }
    
    function get_name()
    {
        return "Formal Assessments: Student Compliance";
    }
    
    function get_description()
    {
        $retval = '<p>Students with no Grade/Ceta for Formal Assessment(s)</p>';
        $retval .= '<p>Options include : </p>';
        $retval .= '<ul>';
        $retval .= '<li>Grade or Ceta</li>';
        $retval .= '<li>Teaching Staff</li>';
        $retval .= '<li>Optional Mentor/Mentee Relationship</li>';
        $retval .= '</ul>';
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
        if(isset($_POST['run']))
        {
            $this->applyDataTables = true;
        }
        return $this->applyDataTables;
    }
    
    function get_icon()
    {
        global $CFG;
        return '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/compliance_report_thumb.jpg"/>';
    }
    function get_image()
    {
        global $CFG;
        return '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/fa_compliance_image.jpg"/>';
    }
    
    function display_options()
    {
        
        $out = '<div>';
        $out .= '<div>';
        $out .= $this->get_core_options();
        $out .= '</div>';
        $out .= '<div id="customOptions">';
        $out .= $this->get_custom_options();
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
    
    function get_custom_options()
    {
        $out = '';
        $formalAssessments = Project::get_all_projects(null, ' ORDER BY targetdate ASC');
        if($formalAssessments)
        {
            $this->assessments = $formalAssessments;
            $out .= get_string('formalassessments','block_bcgt');
            $out .= '<select name="formalassessment">';
            $out .= '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>';
            foreach($formalAssessments AS $fa)
            {
                $selected = '';
                if(isset($_POST['formalassessment']) && $fa->get_id() == $_POST['formalassessment'])
                {
                    $selected = 'selected';
                    $this->options['assessments'] = $fa->get_id();
                }
                $out .= '<option '.$selected.' value="'.$fa->get_id().'">'.$fa->get_name().'</option>';
            }
            $out .= '</select>';
        }
        
        
        $out .= $this->get_grade_filter(true);
           
        //now for the 'my section'
        $out .= $this->get_my_students_filter();
        
        $out .= '<span>';
        $checked = '';
        if(isset($_POST['teachers']))
        {
            $checked = 'checked';
            $this->options['teachers'] = true; 
        }
        $out .= get_string('showteachingstaff', 'block_bcgt').' : <input type="checkbox" '.$checked.' name="teachers"/>';
        $out .= '</span>';
        
        if($tutorRole = get_config('bcgt','tutorrole'))
        {
            $out .= '<span>';
            $checked = '';
            if(isset($_POST['tutor']))
            {
                $checked = 'checked';
                $this->options['tutor'] = true; 
            }
            $out .= get_string('showtutor', 'block_bcgt').' ('.$tutorRole.') : <input type="checkbox" '.$checked.' name="tutor"/>';
            $out .= '</span>';
        }
        
        
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

        //need to find the students
        $studentData = array();
        require_once $CFG->dirroot . '/blocks/bcgt/classes/core/Level.class.php';

        //we always need the students
        $colspan = 1;
        if($this->options['gradeoption'] == 'either')
        {
            $colspan++;
        }
        if(isset($this->options['teachers']))
        {
            $colspan++;
        }
        $students = bcgt_search_users_report($this->options, array('student'));
        $currentMaxQuals = 0;
        foreach($students AS $student)
        {
            
            //need to know max number of quals
            $quals = $this->bcgt_get_users_quals_fa($student->id, $this->options['assessments'], $this->options);
            //need to know max qual count per formal assessment
            if($quals)
            {
                $currentMaxQuals = (count($quals) > $currentMaxQuals) ? count($quals) : $currentMaxQuals;
            }
        }
        foreach($students AS $student)
        {  
            $data = array();
            $data1 = array();
            //an extra row, per student
            //is created that holds the qual name, beneath that it holds the data
            $data1[] = $this->create_cell(array("content"=>$student->username));
            $data1[] = $this->create_cell(array("content"=>$student->firstname));;
            $data1[] = $this->create_cell(array("content"=>$student->lastname));

            //we need to create a cell with the content blank and the col span 3, 
            //to take into consideration the extra row for the qual name, and the
            ///student details
            //then lets try having another row per student
            $data[] = $this->create_cell(array("content"=>"", "colspan"=>1)); 
            $data[] = $this->create_cell(array("content"=>"", "colspan"=>1));
            $data[] = $this->create_cell(array("content"=>"", "colspan"=>1));
            $showStudent = false;
            //need to get the users quals and makesure we know the max number of
            //quals a student is on. Then we can make sure that if a student is on 3 quals, but 
            //another student has 5, then the two different are empty/blank and not simply null.
            //what quals are the students on?
            
            //by sending down the formal assessment id, we know that this qual is on the formal assessment
            $quals = $this->bcgt_get_users_quals_fa($student->id, $this->options['assessments'], $this->options);
            if($quals)
            {
                foreach($quals AS $qual)
                {  
                    $data1[] = $this->create_cell(array("content"=>Level::get_short_version($qual->levelid).
                        ' '.$qual->subtypeshort.' '.$qual->name, 
                        "colspan"=>$colspan));
                    //so for each qual we know that they MUST have a grade
                    
                    if($this->options['gradeoption'] == 'grade' || $this->options['gradeoption'] == 'either')
                    {
                        $value = Project::get_user_qual_value($student->id, $this->options['assessments'], $qual->id);
                        if(!$value || !isset($value->shortvalue))
                        {
                            $showStudent = true;
                            $data[] = $this->create_cell(array("content"=>"X"));
                        }   
                        else
                        {
                            $shortValue = $value->shortvalue;
                            $data[] = $this->create_cell(array("content"=>$shortValue));
                        }
                    }
                    if($this->options['gradeoption'] == 'ceta' || $this->options['gradeoption'] == 'either')
                    {
                        $grade = Project::get_user_qual_grade($student->id, $this->options['assessments'], $qual->id);
                        if(!$grade || !$grade->get_grade() || $grade->get_grade() == '')
                        {
                            $showStudent = true;
                            $data[] = $this->create_cell(array("content"=>"X"));
                        }
                        else
                        {
                            $gradeString = $grade->get_grade();
                            $data[] = $this->create_cell(array("content"=>$gradeString));
                        }
                    }
                    if(isset($this->options['teachers']))
                    {
                        $content = '';
                        //need to get the staff on this qual
                        $staffs = bcgt_get_non_stu_on_qual($qual->id);
                        if($staffs)
                        {
                            foreach($staffs AS $staff)
                            {
                                $content .= $staff->firstname.' '.$staff->lastname.',';
                            }
                        }
                        $data[] = $this->create_cell(array("content"=>$content));
                    } 
                }
                if(count($quals) < $currentMaxQuals)
                {
                    $diff = $currentMaxQuals - count($quals);
                    for($i=0;$i<$diff;$i++)
                    {
                        $data1[] = $this->create_cell(array("content"=>"", "colspan"=>$colspan));
                        $data[] = $this->create_cell(array("content"=>""));
                        if($this->options['gradeoption'] == 'either')
                        {
                            $data[] = $this->create_cell(array("content"=>""));
                        }
                        if(isset($this->options['teachers']))
                        {
                            $data[] = $this->create_cell(array("content"=>""));
                        }
                    }
                }
                if(isset($this->options['tutor']))
                {
                    $content = '';
                    $tutors = bcgt_get_users_tutors($student->id);
                    if($tutors)
                    {
                        foreach($tutors AS $tutor)
                        {
                            $content .= $tutor->firstname.' '.$tutor->lastname.',';
                        }
                    }
                    $data1[] = $this->create_cell(array("content"=>""));
                    $data[] = $this->create_cell(array("content"=>$content));
                }
            }
            if($showStudent)
            {
                $studentData[$student->id.'_0'] = $data1;
                $studentData[$student->id] = $data;
            }        
        }
                
        $header = array();
        $row = array();
        $row2 = array();
        $this->splitHeader = true;
        $this->freezepane = 'D3';
        $blankObj = new stdClass();
        $blankObj->content = '';
        $blankObj->colCount = 1;
        //then we are having a colspan header.
        //therefore header becomes an array of rows.
        $rowObj = new stdClass();
        $rowObj->content = get_string('username');
        $rowObj->colCount = 1;
        $row[] = $rowObj;
        $row2[] = $blankObj;
        $rowObj = new stdClass();
        $rowObj->content = get_string('firstname');
        $rowObj->colCount = 1;
        $row[] = $rowObj;
        $row2[] = $blankObj;
        $rowObj = new stdClass();
        $rowObj->content = get_string('lastname');
        $rowObj->colCount = 1;
        $row[] = $rowObj; 
        $row2[] = $blankObj;

        $letter = 'A';
        for($i=0;$i<$currentMaxQuals;$i++)
        {
            $rowObj = new stdClass();
            $rowObj->content = 'Subject'.$letter++;
            $rowObj->colCount = $colspan;
            $row[] = $rowObj;
            if($this->options['gradeoption'] == 'either')
            {
                $rowObj = new stdClass();
                $rowObj->content = 'G';
                $rowObj->colCount = 1;
                $row2[] = $rowObj;
                
                $rowObj = new stdClass();
                $rowObj->content = 'C';
                $rowObj->colCount = 1;
                $row2[] = $rowObj;
            }
            if(isset($this->options['teachers']))
            {
                $rowObj = new stdClass();
                $rowObj->content = 'Staff';
                $rowObj->colCount = 1;
                $row2[] = $rowObj;
            }   
        }
        if(isset($this->options['tutor']))
        {
            $rowObj = new stdClass();
            $rowObj->content = get_config('bcgt','tutorrole');
            $rowObj->colCount = 1;
            $row[] = $rowObj;
            if($this->options['gradeoption'] == 'either')
            {
                $rowObj = new stdClass();
                $rowObj->content = '';
                $rowObj->colCount = 1;
                $row2[] = $rowObj;
            }
        }
        $header[] = $row;
        if($this->options['gradeoption'] == 'either')
        {
            $header[] = $row2;
        }
        $this->data = $studentData;
        $this->header = $header;
    }
        
    protected function bcgt_get_users_quals_fa($userID, $formalAssessmentID, $options = null)
    {
        global $DB;
        $sql = "SELECT distinct(qual.id),".bcgt_get_qualification_details_fields_for_sql()." FROM {block_bcgt_qualification} qual 
            JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qual.id";
        $sql .= bcgt_get_qualification_details_join_for_sql();
        $sql .= ' JOIN {block_bcgt_activity_refs} ref ON ref.bcgtqualificationid = userqual.bcgtqualificationid';
        $sql .= " WHERE userqual.userid = ? AND ref.bcgtprojectid = ?";
        $params = array($userID, $formalAssessmentID);
        if($options)
        {
            $families = $options['family'];
            $levels = $options['level'];
            $subtypes = $options['subtype'];
            
            $count = 0;
            if(count($families) > 0)
            {
                $sql .= ' AND family.id IN (';
                foreach($families AS $family)
                {
                    $count++;
                    $sql .= '?';
                    $params[] = $family;
                    if($count != count($families))
                    {
                        $sql .= ',';
                    }
                }
                $sql .= ')';
            }
            
            $count = 0;
            if(count($levels) > 0)
            {
                $sql .= ' AND ';
                $sql .= ' level.id IN (';
                foreach($levels AS $level)
                {
                    $count++;
                    $sql .= '?';
                    $params[] = $level;
                    if($count != count($levels))
                    {
                        $sql .= ',';
                    }
                }
                $sql .= ')';
                $and = true;
            }

            $count = 0;
            if(count($subtypes) > 0)
            {
                $sql .= ' AND ';
                $sql .= ' subtype.id IN (';
                foreach($subtypes AS $subtype)
                {
                    $count++;
                    $sql .= '?';
                    $params[] = $subtype;
                    if($count != count($subtypes))
                    {
                        $sql .= ',';
                    }
                }
                $sql .= ')';
                $and = true;
            }
        }
        $sql .= "ORDER BY qual.name ASC";
        return $DB->get_records_sql($sql, $params);
    }
    
}

?>
