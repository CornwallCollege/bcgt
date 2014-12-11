<?php


/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
global $COURSE, $CFG, $PAGE, $OUTPUT, $USER, $DB;;
require_once('../../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

//this is the course we are coming from. 
$cID = optional_param('cID', -1, PARAM_INT);
$sCID = optional_param('sCID', -1, PARAM_INT);
if($cID != -1)
{
    $context = context_course::instance($cID);
}
else
{
    $context = context_course::instance($COURSE->id);
}
require_login();

$PAGE->set_context($context);
require_capability('block/bcgt:viewclassgrids', $context);
$grid = optional_param('g', 's', PARAM_TEXT);
$qualID = optional_param('qID', -1, PARAM_INT);
$aQualID = optional_param('aqID', -1, PARAM_INT);
$courseID = optional_param('courseID', -1, PARAM_INT);
$aCourseID = optional_param('acourseID', -1, PARAM_INT);
$studentID = optional_param('students', -1, PARAM_INT);
$assID = optional_param('assessments', -1, PARAM_INT);
$actID = optional_param('activities', -1, PARAM_INT);
$unitID = optional_param('units', -1, PARAM_INT);
$registerGroupID = optional_param('registerGroupID', -1, PARAM_INT);
//these are all actually the grouping id
$groupID = optional_param('grID',-1, PARAM_INT);
$aGroupID = optional_param('agrID',-1, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);
$initialLoad = optional_param('il', false, PARAM_BOOL);
//if initial load is set to tru then we want to auto load up 
//quals and courses
if($cID != 1 && $courseID == -1 && $initialLoad)
{
    $courseID = $cID;
}
if($cID != 1 && $cID != -1)
{
    //then we are coming from a course
    //so we dont want the initaial load, we want to show the stuff from the
    //courses. 
    $initialLoad = false;
}
if($grid == 'u' && $qualID != -1 && $unitID != -1)
{
    //then we are on the unit grid select
    //we knoe the qual
    //we know the unit
    //lets go there
    redirect($CFG->wwwroot.'/blocks/bcgt/grids/unit_grid.php?uID='.$unitID.'&qID='.$qualID.'&cID='.$cID);
}
elseif($grid == 'u' && $qualID == -1 && $unitID != -1)
{
    //then we have selected a unit, not for a specific qual. then lets load that unit 
    //specific unit up and lets show it for all
    //of the groups and courses its on that this user has access to
    redirect($CFG->wwwroot.'/blocks/bcgt/grids/unit_group_grid.php?uID='.$unitID.'&cID='.$cID);
}
elseif($grid == 'c' && $qualID != -1)
{
    //we are looking at the class grid of a qual:
    redirect($CFG->wwwroot.'/blocks/bcgt/grids/class_grid.php?qID='.$qualID.'&cID='.$cID.'&g=c');
}
elseif($grid == 'c' && $aQualID != -1)
{
    //we are looking at the class grid of a qual:
    redirect($CFG->wwwroot.'/blocks/bcgt/grids/class_grid.php?qID='.$aQualID.'&cID='.$cID.'&g=c');
}
$viewAll = false;
$qualExcludes = array();
$tabFocusS = '';
$tabFocusU = '';
$tabFocusC = '';
$tabFocusR = '';
$tabFocusFA = '';
$tabFocusG = '';
switch($grid)
{
   case 's':
       $string = 'gridselectstudent';
       $tabFocusS = 'focus';
       break;
   case 'u':
       $string = 'gridselectunit';
       $tabFocusU = 'focus';
       $qualExcludes = array('ALevel');
       break;
   case 'c':
       $string = 'gridselectclass';
       $tabFocusC = 'focus';
       $qualExcludes = array('Bespoke');
       break;
   case 'r':
       $string = 'gridselectregister';
       $tabFocusR = 'focus';
   break;
   case 'fa':
       $string = 'gridselectfassessment';
       $tabFocusFA = 'focus';
       break;
   case 'a':
       $string = 'gridselectgradebook';
       $tabFocusG = 'focus';
       break;
   default:
       $string = 'gridselectstudent';
       $tabFocusS = 'focus';
       break;
}

$viewAll = false;
if(has_capability('block/bcgt:viewallgrids', context_system::instance()))
{
    $viewAll = true;
    $onCourse = null;
    if($courseID != -1)
    {
        $onCourse = true;
    }
    $allQuals = search_qualification(-1, -1, -1, '', 
        -1, null, -1, $onCourse, true, $qualExcludes); 
    $allCourses = bcgt_get_courses_with_quals(-1, $qualExcludes);
}
//else
//{
$teacher = $DB->get_record_select('role', 'shortname = ?', array('editingteacher'));
$userQualRole = $DB->get_record_select('role', 'shortname = ?', array('teacher'));
$quals = get_users_quals($USER->id, array($userQualRole->id, $teacher->id), '', -1, -1, $qualExcludes);
$courses = bcgt_get_users_courses($USER->id, $teacher->id, true, -1, $qualExcludes);
//}

$url = '/blocks/bcgt/forms/grid_select.php';
$PAGE->set_url($url, array());
$PAGE->set_title(get_string($string, 'block_bcgt'));
$PAGE->set_heading(get_string($string, 'block_bcgt'));
$PAGE->set_pagelayout( bcgt_get_layout() );
$PAGE->add_body_class(get_string('gridselect', 'block_bcgt'));
$PAGE->navbar->add(get_string('pluginname', 'block_bcgt'),$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=track&cID='.$courseID,'title');
if($cID != -1 && $cID != 1)
{
    global $DB;
    $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($cID));
    if($course)
    {
        $PAGE->navbar->add($course->shortname,$CFG->wwwroot.'/course/view.php?id='.$cID,'title');
    }
    
}
$PAGE->navbar->add(get_string($string, 'block_bcgt'),'','title');

$jsModule = array(
    'name'     => 'block_bcgt',
    'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
    'requires' => array('base', 'io', 'node', 'json', 'event', 'button')
);
$PAGE->requires->js_init_call('M.block_bcgt.initgridselect', null, true, $jsModule);
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
$out = $OUTPUT->header();
$out .= load_javascript(true, true);
$out .= html_writer::tag('h2', get_string($string,'block_bcgt').
        '', 
        array('class'=>'formheading'));
        //needs to check available capibilities
$qualFamilies = bcgt_get_users_qual_families(-1, $viewAll, true);
$out .= '<div class="bcgt_div_container">';
$out .= '<div class="tabs"><div class="tabtree">';
$out .= '<ul class="tabrow0">';
$out .= '<li class="'.$tabFocusS.'">'.
        '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=s&cID='.$cID.'&il=true">'.
        '<span>'.get_string('byStudent', 'block_bcgt').'</span></a></li>';
if(count(array_intersect(explode('|',BCGT_UNIT_VIEW_FAMILIES), $qualFamilies)) > 0)
{
    $out .= '<li class="'.$tabFocusU.'">'.
        '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=u&cID='.$cID.'&il=true">'.
        '<span>'.get_string('byunit', 'block_bcgt').'</span></a></li>';
}

if(count(array_intersect(explode('|',BCGT_CLASS_VIEW_FAMILIES), $qualFamilies)) > 0)
{
    $out .= '<li class="'.$tabFocusC.'">'.
        '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=c&cID='.$cID.'&il=true">'.
        '<span>'.get_string('byClassGroup', 'block_bcgt').'</span></a></li>';
}

if(count(array_intersect(explode('|',BCGT_REGISTER_VIEW_FAMILIES), $qualFamilies)) > 0)
{
    $out .= '<li class="'.$tabFocusR.'">'.
        '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=r&cID='.$cID.'&il=true">'.
        '<span>'.get_string('byregistergroup', 'block_bcgt').'</span></a></li>';
}

if(get_config('bcgt','usefa') && 
                        count(array_intersect(explode('|',BCGT_FA_VIEW_FAMILIES), $qualFamilies)) > 0)
{
    $out .= '<li class="'.$tabFocusFA.'">'.
        '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=fa&cID='.$cID.'">'.
        '<span>'.get_string('byformalassessment', 'block_bcgt').'</span></a></li>';
}

if(count(array_intersect(explode('|',BCGT_ACTIVITYT_VIEW_FAMILIES), $qualFamilies)) > 0)
{
    $out .= '<li class="'.$tabFocusG.'">'.
        '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=a&cID='.$cID.'">'.
        '<span>'.get_string('bygradebook', 'block_bcgt').'</span></a></li>';
}
$out .= '</ul>';
$out .= '</div></div>';
$out .= html_writer::start_tag('div', array('class'=>'bcgt_admin_controls bcgtColumnConainer bcgt_float_container', 
    'id'=>'gridSelect'));
//two columns

if ($grid != 'r')
{

    $out .= '<div class="bcgt_col_one">';
    $out .= '<form name="gridselect" class="bcgt_form" action="grid_select.php" method="POST" id="gridselect">';

    //wrap it in a container: Full Access
    $useGroups = false;
    if($grid != 's' && get_config('bcgt','usegroupsingradetracker'))
    {
        $useGroups = true;
    }


    $out .= '<fieldset>';
    $out .= '<legend>'.get_string('mytrackerfilters', 'block_bcgt').'</legend>';
    $out .= '<input type="hidden" id="cID" name="cID" value="'.$cID.'"/>';
    $out .= '<input type="hidden" name="g" value="'.$grid.'"/>';
    $out .= '<input type="hidden" name="il" value="false"/>';
    $out .= '<p class="gridSelectDesc">'.get_string('disabledoptiondescgridselect', 'block_bcgt').'</p>';
    $out .= '<p class="gridSelectDesc">'.get_string('griddisabledlinksdesc','block_bcgt').'</p>';
    $divID = 'bcgtmainfilter';
    if($useGroups)
    {
        $divID = 'bcgtmainfiltergroups';
    }
    $out .= '<div id="'.$divID.'" class="bcgtmainfilter">';
    $out .= '<div class="inputContainer"><div class="inputLeft">'.
                '<label for="type">'.get_string('myquals', 'block_bcgt').'</label></div>';
        $out .= '<div class="inputRight"><select name="qID" id="qual"><option value="-1">Please select one</option>';
    $myQual = null;
    if($quals)
    {    
        foreach($quals AS $qual)
        {
            $disabled = '';
            //is this qual actuall on a course?
            $onCourse = $DB->get_records_sql('SELECT * FROM {block_bcgt_course_qual} WHERE bcgtqualificationid = ?', array($qual->id));
            if(!$onCourse)
            {
                $disabled = 'disabled';
            }

            $class = '';
            $hasStudents = $DB->get_records_sql('SELECT userqual.id FROM {block_bcgt_user_qual} userqual 
                JOIN {role} role ON role.id = userqual.roleid WHERE bcgtqualificationid = ? AND role.shortname = ?', 
                    array($qual->id, 'student'));
            if(!$hasStudents)
            {
                $class = 'noStudents';
            }
            $selected = '';
            if($initialLoad && (count($quals) == 1 || ($qualID != -1 && $qualID == $qual->id)))
            {
                if(count($quals) == 1)
                {
                    $myQual = $qual;
                    $qualID = $qual->id;
                }
                $selected = 'selected';
            }
            elseif($qualID == $qual->id)
            {
                $selected = 'selected';
            }
            $out .= '<option class="'.$class.'" '.$selected.' value="'.$qual->id.' '.$disabled.'">'.
                    bcgt_get_qualification_display_name($qual, true, ' ').'</option>';
        }
    }
    $out .= '</select>';
    $out .= '</div></div>';
    if($useGroups)
    {
        $out .= '<p class="bcgtqualfilterdesc">'.get_string('qualfilterdesc', 'block_bcgt').
                '<span class="editgroups"> - <a href="'.$CFG->wwwroot.
                '/blocks/bcgt/forms/edit_user_groups.php">'.
                get_string('editmygroups', 'block_bcgt').
                '</a></span></p>';
    }

}

$searchString = 'searchstudent';
if($grid == 's')
{
    $searchString = 'searchstudent';
    //then have a student or qual searchable
    //drop down of all of their students
//    if(!$viewAll)
//    {
        $stuRole = $DB->get_record_select('role', 'shortname = ?', array('student'));
        $students = bcgt_get_users_users($USER->id, array($userQualRole->id, $teacher->id), $stuRole->id, $search);
        if($students)
        {
            $out .= '<div class="inputContainer"><div class="inputLeft">'.
            '<label for="students">'.get_string('mystudents', 'block_bcgt').'</label></div>';
            $out .= '<div class="inputRight"><select name="students" id="studentID"><option value="-1">Please select one</option>'; 
            foreach($students AS $student)
            {
                $selected = '';
                if($student->id == $studentID)
                {
                    $selected = 'selected';
                }
                $out .= '<option '.$selected.' value="'.$student->id.'">'.
                        $student->username .' : '.$student->firstname.' '.$student->lastname.'</option>';
            }
            $out .= '</select>';
            $out .= '</div></div>';
        }
//    }
}
elseif($grid == 'u')
{
    $searchString = 'searchunit';
    //then have a unit or qual searchable
    //drop down of all of theur units
    //then have a student or qual searchable
    //drop down of all of their students
//    if(!$viewAll)
//    {
        $teacherRole = $DB->get_record_select('role', 'shortname = ?', array('teacher'));
        $units = bcgt_get_users_units($USER->id, $teacherRole->id, '');
            $out .= '<div class="inputContainer"><div class="inputLeft">'.
            '<label for="units">'.get_string('myunits', 'block_bcgt').'</label></div>';
            $out .= '<div class="inputRight"><select name="units" id="unitID"><option value="-1">Please select one</option>'; 
            if($units)
            {
                foreach($units AS $unit)
                {
                    $selected = '';
                    if($unit->id == $unitID)
                    {
                        $selected = 'selected';
                    }
                    $out .= '<option '.$selected.' value="'.$unit->id.'">'.
                            $unit->uniqueid .' : '.$unit->name.'</option>';
                }
            }
            $out .= '</select>';
            $out .= '</div></div>';
//    }
}
elseif($grid == 'fa')
{
    $searchString = 'searchass';
//    if(!$viewAll)
//    {
        $userQualRole = $DB->get_record_select('role', 'shortname = ?', array('teacher'));
        $assessments = bcgt_get_users_assessments($USER->id, $userQualRole->id, $search, $qualID);
        if($assessments)
        {
            $out .= '<div class="inputContainer"><div class="inputLeft">'.
            '<label for="students">'.get_string('myassessments', 'block_bcgt').'</label></div>';
            $out .= '<div class="inputRight"><select name="assessments" id="assID"><option value="-1">Please select one</option>'; 
            foreach($assessments AS $ass)
            {
                $selected = '';
                if($ass->id == $assID)
                {
                    $selected = 'selected';
                }
                $out .= '<option '.$selected.' value="'.$ass->id.'">'.
                        $ass->targetdate .' : '.$ass->name.'</option>';
            }
            $out .= '</select>';
            $out .= '</div></div>';
        }
//    }
    //then have a assessment or qual that is seachable
    //drop down of all of their assessments
}
elseif($grid == 'c')
{
    $searchString = 'searchclass';
    //then have a qual that is searchable.
}

elseif ($grid == 'r')
{
    
    $out .= "<h2>".get_string('yourregistergroups', 'block_bcgt')."</h2>";
        
    $out .= "<p>";
    
    // If bedford college, we can use an MIS connection to get the groups
    $script = $CFG->dataroot . '/bcgt/scripts/register_groups.php';
    if ( file_exists($script) )
    {
        
        include_once $script;
        
        $refresh = optional_param('refresh', false, PARAM_INT);
        
        if ($refresh)
        {
            
            if ( function_exists('bcgt_ext_get_register_groups') ){

                
                // Is it confirmed?
                if (isset($_POST['confirm']))
                {
                    
                    $groupIDs = @$_POST['ids'];
                    $out .= bcgt_ext_save_register_groups($groupIDs);
                    
                }
                else
                {
                                
                    $groups = bcgt_ext_get_register_groups($USER);

                    $out .= "<form action='' method='post'>";
                    $out .= "<table>";

                        $out .= "<tr><th colspan='5'>Register Groups From MIS</th></tr>";
                        $out .= "<tr><th><input type='checkbox' onclick='$(\".reggroup\").prop(\"checked\", this.checked);' /></th><th>ID</th><th>Event</th><th>Dates</th><th>Times</th><th>No. Learners</th></tr>";

                        if ($groups)
                        {

                            foreach($groups as $group)
                            {

                                $out .= "<tr><td><input type='checkbox' class='reggroup' name='ids[]' value='{$group->id}' /></td><td>{$group->id}</td><td>{$group->name}</td><td>{$group->sdate} - {$group->edate}</td><td>{$group->stime} - {$group->etime}</td><td>{$group->cnt}</td></tr>";

                            }

                        }
                        else
                        {
                            $out .= "<tr><td colspan='5'>".get_string('norecordsfound', 'block_bcgt')."</td></tr>";
                        }

                    $out .= "</table>";

                    $out .= "<p><input type='submit' name='confirm' value='".get_string('update')."' /></p>";

                    $out .= "</form>";
                
                }
                

            }
            
            
        }
        
        $out .= "<a href='{$CFG->wwwroot}/blocks/bcgt/forms/grid_select.php?g=r&cID={$courseID}&il={$initialLoad}&refresh=1'><img src='".$OUTPUT->pix_url('t/reload')."' alt='refresh' /> ".get_string('refreshregistergroups', 'block_bcgt')."</a> &nbsp;&nbsp; ";
        
    }
    
    // otherwise link to import them
    else
    {
        
    }
    
    // Edit existing
    $out .= "<a href='{$CFG->wwwroot}/blocks/bcgt/forms/grid_select.php?g=r&cID={$courseID}&il={$initialLoad}&edit=1'><img src='".$OUTPUT->pix_url('t/edit')."' alt='refresh' /> ".get_string('editregistergroups', 'block_bcgt')."</a>";

    $out .= "</p>";
    
    $registerGroups = bcgt_get_users_register_groups($USER->id);
    
    if ($registerGroups)
    {
        
        // Edit existing ones - delete them basically
        $edit = optional_param('edit', false, PARAM_INT);
        if ($edit == 1)
        {
            
            if (isset($_POST['confirmedit']))
            {
                
                // Wipe all links to the register groups for this user
                $DB->delete_records("block_bcgt_user_reg_groups", array("userid" => $USER->id, "type" => "T"));
                
                // Add submitted ones back in
                if (isset($_POST['ids']))
                {
                    foreach($_POST['ids'] as $id)
                    {
                        bcgt_add_user_to_register_group($id, $USER->id, "T");
                    }
                }
                
                $out .= "<h3>Saved!</h3>";
                $registerGroups = bcgt_get_users_register_groups($USER->id);

                
            }
            
            $out .= "<form action='' method='post'>";
            $out .= "<table>";

                $out .= "<tr><th colspan='5'>".get_string('editregistergroups', 'block_bcgt')."</th></tr>";
                $out .= "<tr><th><input type='checkbox' onclick='$(\".reggroupedit\").prop(\"checked\", this.checked);' /></th><th>ID</th><th>Event</th><th>Dates</th><th>Times</th></tr>";

                    foreach($registerGroups as $group)
                    {
                        $out .= "<tr><td><input type='checkbox' class='reggroupedit' name='ids[]' value='{$group->id}' checked /></td><td>{$group->recordid}</td><td>{$group->name}</td><td>{$group->startdate} - {$group->enddate}</td><td>{$group->starttime} - {$group->endtime}</td></tr>";
                    }


            $out .= "</table>";

            $out .= "<p><input type='submit' name='confirmedit' value='".get_string('update')."' /></p>";

            $out .= "</form>";
            
        }
        
        
        
        $out .= "<div class='bcgt_admin_controls bcgtColumnConainer bcgt_float_container'>";
        
        $out .= "<div class='bcgt_col_one'>";
        
            $out .= "<div class='bcgtmainfilter'>";
            
            $out .= "<form action='{$CFG->wwwroot}/blocks/bcgt/forms/grid_select.php?g=r&cID={$courseID}&il={$initialLoad}' method='post'>";
                        
                $out .= '<div class="inputContainer"><div class="inputLeft">'.
                '<label for="type">'.get_string('registergroup', 'block_bcgt').'</label></div>';
                $out .= '<div class="inputRight">';
        
                    $out .= "<select name='registerGroupID'>";
                    
                        $out .= "<option value=''>".get_string('pleaseselect', 'block_bcgt')."</option>";
                        
                        foreach($registerGroups as $group)
                        {
                            $out .= "<option value='{$group->id}'>({$group->recordid}) {$group->name} ({$group->startdate} - {$group->enddate}) ({$group->starttime} - {$group->endtime})</option>";
                        }
                    
                    $out .= "</select>";
                    
                    $out .= "<br><br>";
                    $out .= '<input type="submit" class="filter_input" name="searchsubmit" value="'.get_string('filter', 'block_bcgt').'"/>';
                    
                $out .= '</div>';
                
            $out .= "</form>";
                
            $out .= "</div>";
            
        $out .= "</div>";
        
        $out .= "</div>";
        
    }
    else
    {
        $out .= "<p>".get_string('norecordsfound', 'block_bcgt')."</p>";
    }
    
    
}

elseif($grid == 'a')
{
    $searchString = 'searchgradebook';
    //drop down of all activities. 
    $userQualRole = $DB->get_record_select('role', 'shortname = ?', array('editingteacher'));
    $activities = bcgt_get_users_activities($USER->id, $userQualRole->id, $qualID, $courseID);
    if($activities)
    {
        $out .= '<div class="inputContainer"><div class="inputLeft">'.
        '<label for="students">'.get_string('myactivities', 'block_bcgt').'</label></div>';
        $out .= '<div class="inputRight"><select name="activities" id="actID"><option value="-1">Please select one</option>'; 
        foreach($activities AS $act)
        {
            $selected = '';
            if($act->id == $actID)
            {
                $selected = 'selected';
            }
            $out .= '<option '.$selected.' value="'.$act->id.'">'.
                    $act->module .' : '.$act->name.'</option>';
        }
        $out .= '</select>';
        $out .= '</div></div>';
    }
}

if ($grid != 'r')
{

    $out .= '<div class="inputContainer"><div class="inputLeft">'.
    '<label for="search">'.get_string($searchString, 'block_bcgt').'</label></div>';
    $out .= '<div class="inputRight"><input type="text" name="search" id="search"  value="'.$search.'"/>';
    $out .= '</div></div>';

    $out .= '</div>';
    $out .= '<p class="bcgtoptionalfilter">'.get_string('or', 'block_bcgt').'</p>';
    $out .= '<div id="'.$divID.'" class="bcgtmainfilter">';
    $out .= '<input id="grid" type="hidden" name="grid" value="'.$grid.'"/>';
    $out .= '<div class="inputContainer"><div class="inputLeft">'.
                '<label for="course">'.get_string('mycourse', 'block_bcgt').'</label></div>';
        $out .= '<div class="inputRight"><select name="courseID" id="course"><option value="-1">Please select one</option>';
    if($courses)
    {    
        foreach($courses AS $course)
        {
    //        if(count($courses) == 1)
    //        {
    //            $courseID = $course->id;
    //        }
            $selected = '';
            if(count($courses) == 1 || ($courseID != -1 && $courseID == $course->id))
            {
                $selected = 'selected';
            }
            $out .= '<option '.$selected.' value="'.$course->id.'">'.
                    $course->shortname.':'.$course->fullname.'</option>';
        }
    }
    $out .= '</select>';
    $out .= '</div></div>';
    //if we are using groups:
    if($useGroups)
    {
        $group = new Group();
        //but we dont want groups that are for qualifications that
        //we cant view a grid for. 
        //e.g. Unit grid: no Alevels
        $groupings = $group->get_my_groups($courseID, $qualExcludes);
        //then we need to show an OR
        $out .= '<div class="inputContainer"><div class="inputLeft">'.
                '<label for="type">'.get_string('mygroupings', 'block_bcgt').'</label></div>';
        $out .= '<div class="inputRight"><select name="grID" id="group"><option value="-1">Please select one</option>';
        if($groupings)
        {    
            foreach($groupings AS $myGrouping)
            {
                $selected = '';
                if($groupID == $myGrouping->id)
                {
                    $selected = 'selected="selected"';
                }
                $out .= '<option '.$selected.' value="'.$myGrouping->id.'">'.
                        $myGrouping->name.' ('.$myGrouping->shortname.')</option>';
            }
        }
        $out .= '</select>';
        $out .= '</div></div>';
        $out .= '<p class="bcgtqualfilterdesc">'.get_string('groupfilterdesc', 'block_bcgt').
                '<span class="editgroups"> - <a href="'.$CFG->wwwroot.
                '/blocks/bcgt/forms/edit_user_groups.php">'.
                get_string('editmygroups', 'block_bcgt').
                '</a></span></p>';
    }
    //allow to filter by groups:

    $out .= '<input type="submit" class="filter_input" name="searchsubmit" value="'.get_string('filter', 'block_bcgt').'"/>';
    $out .= '</div>';
    $out .= '</fieldset>';

    if(has_capability('block/bcgt:viewallgrids', context_system::instance()) && $allQuals)
    {  
        $out .= '<fieldset>';
        $out .= '<legend>'.get_string('bcgtfullaccess', 'block_bcgt').'</legend>';
        $out .= '<p class="gridSelectDesc">'.get_string('bcgtfullaccessdesc','block_bcgt').'</p>';
        $out .= '<div class="bcgtmainfilter">';
        $out .= '<div class="inputContainer"><div class="inputLeft">'.
                '<label for="type">'.get_string('allquals', 'block_bcgt').'</label></div>';
        $out .= '<div class="inputRight"><select name="aqID" id="aqual"><option value="-1">Please select one</option>';
        foreach($allQuals AS $qual)
        {
            if(count($allQuals) == 1)
            {
                $qualID = $qual->id;
            }
            $selected = '';
            if(count($allQuals) == 1 || ($aQualID != -1 && $aQualID == $qual->id))
            {
                $selected = 'selected';
            }
            $out .= '<option '.$selected.' value="'.$qual->id.'">'.
                    bcgt_get_qualification_display_name($qual, true, ' ').'</option>';
        }
        $out .= '</select>';
        $out .= '</div></div>';
        $out .= '</div>';
        $out .= '<p class="bcgtoptionalfilter">'.get_string('or', 'block_bcgt').'</p>';
        $out .= '<div class="bcgtmainfilter">';
        $out .= '<div class="inputContainer"><div class="inputLeft">'.
                '<label for="acourseID">'.get_string('allcourse', 'block_bcgt').'</label></div>';
        $out .= '<div class="inputRight"><select name="acourseID" id="acourse"><option value="-1">Please select one</option>';
        foreach($allCourses AS $allCourse)
        {
    //        if(count($allCourses) == 1)
    //        {
    //            $courseID = $course->id;
    //        }
            $selected = '';
            if(count($allCourses) == 1 || ($aCourseID != -1 && $aCourseID == $allCourse->id))
            {
                $selected = 'selected';
            }
            $out .= '<option '.$selected.' value="'.$allCourse->id.'">'.
                    $allCourse->shortname.':'.$allCourse->fullname.'</option>';
        }
        $out .= '</select>';
        $out .= '</div></div>';

        if($useGroups)
        {
            $group = new Group();
            //dont want to suggest groups for alevels for units, or BTEC for class
            $groupings = $group->get_all_possible_groups($aCourseID, $qualExcludes);
            //then we need to show an OR
            $out .= '<div class="inputContainer"><div class="inputLeft">'.
                    '<label for="type">'.get_string('allgroupings', 'block_bcgt').'</label></div>';
            $out .= '<div class="inputRight"><select name="agrID" id="agroup"><option value="-1">Please select one</option>';
            if($groupings)
            {    
                foreach($groupings AS $allGrouping)
                {
                    $selected = '';
                    if($aGroupID == $allGrouping->id)
                    {
                        $selected = 'selected="selected"';
                    }
                    $out .= '<option '.$selected.' value="'.$allGrouping->id.'">'.
                            $allGrouping->name.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                            '--('.$allGrouping->shortname.')</option>';
                }
            }
            $out .= '</select>';
            $out .= '</div></div>';
        }
        $out .= '</div>';
        $out .= '<input type="submit" class="filter_input" name="searchsubmit" value="'.get_string('filter', 'block_bcgt').'"/>';
        $out .= '</fieldset>';

    }


    $out .= '</div>';//closes column one
    
    
}

    
    $out .= '<div class="bcgt_col_two">';
    if($courseID != -1 || $aCourseID != -1)
    {
        $courseDB = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ? OR id = ?", array($courseID, $aCourseID));
        if($courseDB)
        {
            $out .= '<h2>'.$courseDB->shortname.'</h2>';
        }
    }
    if($groupID != -1 || $aGroupID != -1)
    {
        $groupDB = $DB->get_record_sql("SELECT * FROM {groups} WHERE id = ? OR id = ?", array($groupID, $aGroupID));
        if($groupDB)
        {
            $out .= '<h2>'.$groupDB->name.'</h2>';
        }
    }







$out .= '<div id="gridresults">';
if($grid == 's')
{
    if($qualID != -1)
    {
        //we have the qualification that has been selected in the drop downn
        $out .= bcgt_display_qual_grid_select($qualID, $cID, $search);
    }
    elseif($aQualID != -1)
    {
        //we have the qualification that has been selected in the drop downn
        $out .= bcgt_display_qual_grid_select($aQualID, $cID, $search);
    }       
    elseif($courseID != -1 || $aCourseID != -1)
    {
        //we have a course ID and we only have once course to show
//        if(count($courses) == 1)
//        {
//            $course = end($courses);
//            $courseID = $course->id;
//        }
        if($courseID != -1)
        {
            //then we need to get all of the quals that are on this course. 
            $quals = bcgt_get_course_quals($courseID, -1, $qualID, $qualExcludes);
        }
        elseif($aCourseID != -1)
        {
            //then we need to get all of the quals that are on this course. 
            $quals = bcgt_get_course_quals($aCourseID, -1, $qualID, $qualExcludes);
        }
        if($quals)
        {
            foreach($quals AS $qual)
            {
                $out .= bcgt_display_qual_grid_select($qual->id, $cID, $search);
            }
        }
        
    }
    elseif($studentID != -1)
    {
        //then we need to  display the options for just one student
        $out .= bcgt_display_student_grid_select($search, $USER->id, $studentID);
    }
    elseif($viewAll && $search != '')
    {
        //so we are an admin looking for any students
        //then we can find any student(s)
        $out .= bcgt_display_student_grid_select($search);
    }
    elseif(!$viewAll && $search != '')
    {
        //so we are not an admin, we have just entered a search
        //so lets search for the students out of the ones I can see. 
        $out .= bcgt_display_student_grid_select($search, $USER->id);
    }
}
elseif($grid == 'u')
{
    //so;
    //if the qual id is selected. 
    //then show units for that qual
    
    //if the courseid is selected, then it depends on the groups??
    //if no group:
    //show all units found on that course
    
    //if group:
    //show all units found on that course by that group. 
    if($qualID != -1)
    {
        $out .= bcgt_display_unit_grid_select($qualID, $cID, $search);
    }
    elseif($aQualID != -1)
    {
        //we have the qualification that has been selected in the drop downn
        $out .= bcgt_display_unit_grid_select($aQualID, $cID, $search);
    }
    elseif($groupID != -1 || $courseID != -1)
    {
        //we are searching by the group
        $out .= bcgt_display_course_group_unit_grid_select($courseID, $groupID, $cID, $unitID, $search);
    }
    elseif($aGroupID != -1 || $aCourseID != -1)
    {
        //we are searching by all groups
        $out .= bcgt_display_course_group_unit_grid_select($aCourseID, $aGroupID, $cID, $unitID, $search);
    } 
    elseif($unitID != -1)
    {
        //then display for just the one unit and all of the possible quals
        //then we can find any unit of those that we can see
        $out .= bcgt_display_unit_grid_select_search($search, $qualExcludes, $USER->id, $unitID);
    }
    elseif($viewAll && $search != '')
    {
        //then we can find any units in the entire system (that belong to quals that are 
        //on a course)
        $out .= bcgt_display_unit_grid_select_search($search, $qualExcludes);
    }
    elseif(!$viewAll && $search != '')
    {
        //then we can find any unit of those that we can see
        $out .= bcgt_display_unit_grid_select_search($search, $qualExcludes, $USER->id);
    }
}
elseif($grid == 'c')
{
    //if we are here we have one course and potentially multiple quals.
    //if a qual was selected then it will go straight to the grid
    //if a group was selected then it will go straight to the grid.
    if($groupID != -1 || $courseID != -1)
    {
        $out .= bcgt_display_class_grid_select($courseID, $groupID, $cID, $qualExcludes, $search);
    }
    elseif($aGroupID != -1 || $aCourseID != -1)
    {
        $out .= bcgt_display_class_grid_select($aCourseID, $aGroupID, $cID, $qualExcludes, $search);
    }
    elseif($viewAll && $search != '')
    {
        //then we can find any student(s)
        $out .= bcgt_display_class_grid_select_search($cID, $search, $qualExcludes);
    }
    elseif($search != '')
    {
        //else we cant view all and/or the view is 
        $out .= bcgt_display_class_grid_select_search($cID, $search, $qualExcludes, $USER->id); 
    }
    elseif($myQual != null)
    {
        //one qual has been loaded
        //qualid is set and we havent auto loaded. 
        //so output the bcgt_display_class_grid_select for the qual:
        $out .= '<table class="qualificationClass bcgt_table" align="center">';
        $out .= class_qual_select_grid(array($myQual), $cID, true, -1, -1);
        $out .= '</table>';
    }
}
elseif ($grid == 'r')
{
    
    if ($registerGroupID > 0)
    {
        
        // Check we are assigned to this register group
        if (bcgt_is_user_in_register_group($USER->id, $registerGroupID) )
        {
            
            $group = $DB->get_record("block_bcgt_register_groups", array("id" => $registerGroupID));
            if ($group)
            {
            
                $out .= "<h2>({$group->recordid}) {$group->name}</h2>";
                
                $out .= "<div class='tabtree'>";
                $out .= "<ul class='tabrow0'><li><a href='#' onclick='$(\".reg_stud_grids\").show();$(\".reg_unit_grids\").hide();return false;'>".get_string('registerstudgrids', 'block_bcgt')."</a></li><li><a href='#' onclick='$(\".reg_stud_grids\").hide();$(\".reg_unit_grids\").show();return false;'>".get_string('registerunitgrids', 'block_bcgt')."</a></li></ul>";
                $out .= "</div>";

               

                // Find users
                $users = bcgt_get_register_group_users($registerGroupID);



                // No way to know from a register group which qual it should be linked to
                // So.. Find all the quals this teacher is on, then for each of them, loop through the students
                // and see how many of them are on that qual.
                // Then order by the amount of students on the qual, and display the lists for any with > 0 of
                // the register group's students
                $qualRegisterGroupStudents = array();

                if ($quals)
                {

                    foreach($quals as $qual)
                    {

                        $qualRegisterGroupStudents[$qual->id] = array(
                            'cnt' => 0,
                            'students' => array()
                        );                    

                        // Loop through the register group students
                        if ($users)
                        {

                            foreach($users as $user)
                            {

                                // Are they on this qual?
                                $check = $DB->get_record("block_bcgt_user_qual", array("userid" => $user->id, "bcgtqualificationid" => $qual->id));
                                if ($check)
                                {
                                    $qualRegisterGroupStudents[$qual->id]['cnt']++;
                                    $qualRegisterGroupStudents[$qual->id]['students'][] = $user;
                                }

                            }

                        }

                    }

                }

                // Order the array
                uasort($qualRegisterGroupStudents, function($a, $b){
                    return ($a['cnt'] < $b['cnt']);
                });

                // Now display the lists
                if ($qualRegisterGroupStudents)
                {

                    foreach($qualRegisterGroupStudents as $qID => $info)
                    {

                        if ($info['cnt'] > 0)
                        {

                            $out .= "<div class='reg_stud_grids'>";
                            $out .= bcgt_display_qual_grid_select($qID, $courseID, false, $info['students']);
                            $out .= "</div>";
                            $out .= "<div class='reg_unit_grids' style='display:none;'>";
                            $out .= bcgt_display_unit_grid_select($qID, $courseID, false, array('regGrpID' => $registerGroupID));
                            $out .= "</div>";
                            $out .= "<br><br>";
                        
                        }

                    }

                }
                                
                
                            
            }
            
            
        }
        else
        {
            $out .= get_string('accessdenied', 'block_bcgt');
        }
                
    }
    
}
elseif($grid == 'fa')
{
    if($assID != -1)
    {
        //then display for a specific assignment and all of the quals
        //it could be on. 
        $out .= bcgt_display_assessment_grid_select_search($search, $USER->id, $assID);
    }
    elseif($qualID != -1)
    {
        //then display all of the assessments that are on this qual
        $out  .= bcgt_display_qual_assessments($qualID, $search, $USER->id, $assID, $groupID);
    } 
    elseif($aQualID != -1)
    {
        //then display all of the assessments that are on this qual
        $out  .= bcgt_display_qual_assessments($aQualID, $search, -1, $assID, $aGroupID);
    }
    elseif($courseID != -1 || $aCourseID != -1 || $groupID != -1 || $aGroupID != -1)
    {
        if($courseID != -1 || $groupID != -1)
        {
            $out .= bcgt_display_assessment_grid_select($courseID, $groupID);
        }
        else
        {
            $out .= bcgt_display_assessment_grid_select($aCourseID, $aGroupID);
        }
    }
    elseif($viewAll && $search != '')
    {
        //then we can find any assessments
        $out .= bcgt_display_assessment_grid_select_search($search);
    }
    elseif(!$viewAll && $search != '')
    {
        //then we can find any student(s)
        $out .= bcgt_display_assessment_grid_select_search($search, $USER->id);
    }
}
elseif($grid == 'a')
{
    if($actID != -1)
    {
        //then display for a specific assignment and all of the quals
        //it could be on. 
        $out .= bcgt_display_activity_grid_select_search($search, $USER->id, $actID);
    }
    elseif($qualID != -1)
    {
        //then display all of the assessments that are on this qual
        $out  .= bcgt_display_qual_activity($qualID, $search, $USER->id, $actID, $groupID);
    } 
    elseif($aQualID != -1)
    {
        //then display all of the assessments that are on this qual
        $out  .= bcgt_display_qual_activity($aQualID, $search, $USER->id, $actID, $aGroupID);
    }
    elseif($courseID != -1 || $aCourseID != -1)
    {
        if($courseID != -1)
        {
            $out .= bcgt_display_activity_grid_select($courseID, $groupID);
        }
        else
        {
            $out .= bcgt_display_activity_grid_select($aCourseID, $aGroupID);
        }
    }
    elseif($viewAll && $search != '')
    {
        //then we can find any assessments
        $out .= bcgt_display_activity_grid_select_search($search);
    }
    elseif(!$viewAll && $search != '')
    {
        //then we can find any student(s)
        $out .= bcgt_display_activity_grid_select_search($search, $USER->id);
    }
}
$out .= '</div>';
$out .= '</div>';//end column two
$out .= '</form>';
$out .= '</div>';//closses floating container
$out .= html_writer::end_tag('div');//end main column
$out .= '</div>';
$out .= $OUTPUT->footer();

echo $out;
?>
