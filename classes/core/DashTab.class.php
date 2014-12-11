<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

require_once('../../../config.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');

abstract class DashTab { 
    
    const DASHTAB1 = 'dash';
    const DASHTAB2 = 'track';
    const DASHTAB3 = 'course';
    const DASHTAB4 = 'stu';
    const DASHTAB5 = 'team';
    const DASHTAB6 = 'unit';
    const DASHTAB7 = 'rep';
    const DASHTAB8 = 'ass';
    const DASHTAB9 = 'adm';
    const DASHTAB10 = 'hel';
    const DASHTAB11 = 'feed';
    const DASHTAB12 = 'mess';
    const DASHTAB13 = 'grouping';
    const DASHTAB14 = 'reporting'; // Why are you doing it like this?
    const DASHTAB15 = 'alps'; // Why are you doing it like this?
    
    function DashTab ()
    {
        
    }
    
    public static function bcgt_get_dashboard_tab_title($tab)
    {
        switch($tab)
        {
            case(DashTab::DASHTAB1):
            case(DashTab::DASHTAB2):
            case(DashTab::DASHTAB3):
            case(DashTab::DASHTAB4):
            case(DashTab::DASHTAB5):
            case(DashTab::DASHTAB6):
            case(DashTab::DASHTAB7):
            case(DashTab::DASHTAB8):
            case(DashTab::DASHTAB9):
            case(DashTab::DASHTAB10):
            case(DashTab::DASHTAB11):
            case(DashTab::DASHTAB12):
            case(DashTab::DASHTAB13):
            case(DashTab::DASHTAB14):
            case(DashTab::DASHTAB15):
                return get_string('dashtab'.$tab, 'block_bcgt');
                break;
            default:
                return DashTab::get_plugin_title($tab);
                break;
        }
        
    }
    
    public static function bcgt_get_dashboard_tabs($tab){
        $retval = "";
        $retval .= DashTab::bcgt_core_get_core_dashboard_tabs($tab);
        $retval .= DashTab::bcgt_get_plugin_tabs($tab);
        return $retval;
    }
    //Dashboard stuff:
    public static function bcgt_core_get_core_dashboard_tabs($tab){
        //this will be driven by capibilities and who is logged in
        $retval = "";
        //get context
        global $COURSE;
        $courseContext = context_course::instance($COURSE->id);
        
        //the order is: 
        //'My Dashboard'
        //'Trackers' 'Courses' 'Students' 'Team' 'Units' 'Reports' 'Assignments';
        //'Admin' 'Help' 'Feedback' 'Messages';
//        $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB1, $tab, 'first');
        $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB2, $tab, 'first');
        $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB13, $tab, 'first');
//        if(($linkQualCourse = get_config('bcgt', 'linkqualcourse')) 
//                && has_capability('block/bcgt:viewcoursestab', $courseContext))
//        {
//            $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB3, $tab, 'middle');
//        }
//        if(has_capability('block/bcgt:viewstudentstab', $courseContext))
//        {
//            $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB4, $tab, 'middle');
//        }
//        if(has_capability('block/bcgt:viewteamtab', $courseContext))
//        {
//            $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB5, $tab, 'middle');
//        }
//        if(has_capability('block/bcgt:viewunitstab', $courseContext))
//        {
//            $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB6, $tab, 'middle');
//        }
//        if(has_capability('block/bcgt:viewreportsstab', $courseContext))
//        {
//            $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB7, $tab, 'middle');
//        }
//        if(has_capability('block/bcgt:viewassignmentstab', $courseContext))
//        {
//            $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB8, $tab, 'middle');
//        }
        if(has_capability('block/bcgt:viewadmintab', $courseContext))
        {
            $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB9, $tab, 'middle');
        }
//        if(has_capability('block/bcgt:viewhelptab', $courseContext))
//        {
//            $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB10, $tab, 'middle');
//        }
//        if(has_capability('block/bcgt:viewfeedbacktab', $courseContext))
//        {
//            $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB11, $tab, 'middle');
//        }
//        if(has_capability('block/bcgt:viewmessagestab', $courseContext))
//        {
//            $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB12, $tab, 'middle');
//        }
        if(has_capability('block/bcgt:viewrepsystab', $courseContext))
        {
            $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB14, $tab, 'middle');
        }
        if(get_config('bcgt', 'calcultealpstempreports') && get_config('bcgt', 'usefa') && 
                has_capability('block/bcgt:seealpsreportsquals', $courseContext))
        {
            $retval .= DashTab::bcgt_core_get_core_dash_tab(DashTab::DASHTAB15, $tab, 'first');
        }
        return $retval;
    }
    
    public static function bcgt_core_get_core_dash_tab($tabName, $tabFocus, $class){
        global $CFG;
        $courseID = optional_param('cID', -1, PARAM_INT);
        $focus = ($tabFocus == $tabName ? 'focus' : '');
        $retval = '<li class="'.$class.' '.$focus.'">'.
        '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab='.$tabName.'&cID='.$courseID.'">'.
        '<span>'.get_string('dashtab'.$tabName, 'block_bcgt').'</span></a></li>';
        return $retval;
    }
    
    public static function bcgt_get_plugin_tabs($tabName)
    {
        //query the tabs table
        //get the records. For each get the file
        //get the class
        //call the bcgt_get_plugin_tabs method
        $classes = DashTab::get_plugin_classes();
        $retval = '';
        if($classes)
        {
            foreach($classes AS $class)
            {
                $retval .= $class::bcgt_get_plugin_tabs($tabName);
            }
        }
        return $retval;
    }
    
    public static function get_plugin_classes()
    {
        global $DB, $CFG;
        $classArray = array();
        $sql = "SELECT * FROM {block_bcgt_tabs} WHERE component != ?";
        $pluginTabs = $DB->get_records_sql($sql, array('core'));
        if($pluginTabs)
        {
            foreach($pluginTabs AS $tab)
            {
                $file = $CFG->dirroot.$tab->tabclassfile;
                $className = $tab->component;
                if (file_exists($file)) {
                    include_once($file);
                    $class = $className.'DashTab';
                    if(class_exists($class))
                    {
                        $classArray[] = $class;
                    }
                }
            }
        }
        return $classArray;
    }
    
    public static function bcgt_display_dashboard_tab_view($tabName, $courseID){
        switch($tabName)
        {
            case(DashTab::DASHTAB1):
                //then get the dashboard
                return DashTab::bcgt_tab_get_dashboard_tab();
                break;
            case(DashTab::DASHTAB2):
                //then get the trackers
                return DashTab::bcgt_tab_get_trackers_tab();
                break;
            case(DashTab::DASHTAB3):
                //then get the courses
                return DashTab::bcgt_tab_get_courses_tab();
                break;
            case(DashTab::DASHTAB4):
                //then get the students
                return DashTab::bcgt_tab_get_students_tab();
                break;
            case(DashTab::DASHTAB5):
                //then get the team
                return DashTab::bcgt_tab_get_team_tab();
                break;
            case(DashTab::DASHTAB6):
                //then get the units
                return DashTab::bcgt_tab_get_units_tab();
                break;
            case(DashTab::DASHTAB7):
                //then get the reports
                return DashTab::bcgt_tab_get_report_tab();
                break;
            case(DashTab::DASHTAB8):
                //then get the assignments
                return DashTab::bcgt_tab_get_assignments_tab();
                break;
            case(DashTab::DASHTAB9):
                //then get the admin
                require_once('AdminTab.class.php');
                $tab = new AdminTab();
                return $tab->get_tab_view($courseID);
                break;
            case(DashTab::DASHTAB10):
                //then get the help
                return DashTab::bcgt_tab_get_help_tab();
                break;
            case(DashTab::DASHTAB11):
                //then get the feedback
                return DashTab::bcgt_tab_get_feedback_tab();
                break;
            case(DashTab::DASHTAB12):
                //then get the messages
                return DashTab::bcgt_tab_get_messages_tab();
                break;
            case(DashTab::DASHTAB13):
                return DashTab::bcgt_tab_get_group_tab();
                break;
            case(DashTab::DASHTAB14):
                return DashTab::bcgt_tab_get_reporting_tab();
                break;
            case(DashTab::DASHTAB15):
                return DashTab::bcgt_tab_get_alps_tab();
                break;
            default:
                return DashTab::bcgt_tab_get_plugin_tab($tabName);
                break;
        }
    }
    
    public static function bcgt_tab_get_plugin_tab($tabName)
    {
        $classes = DashTab::get_plugin_classes();
        $retval = '';
        if($classes)
        {
            foreach($classes AS $class)
            {
                //if this $tabName is not from this tab, then lets
                //move onto the next. If its not this will return false. 
                //so if it returns anything we have our tab.
                $tabFound = $class::bcgt_display_dashboard_tab_view($tabName);
                if($tabFound)
                {
                    $retval = $tabFound;
                    //then lets break out of the loop. 
                    break;
                }
            }
        }
        return $retval;
    }
    
    public static function get_plugin_title($tab)
    {
        $classes = DashTab::get_plugin_classes();
        $retval = '';
        if($classes)
        {
            foreach($classes AS $class)
            {
                $tabTitle = $class::bcgt_get_title($tab);
                if($tabTitle)
                {
                    $retval = $tabTitle;
                    break;
                }
            }
        }
        return $retval;
    }
    
    public static function bcgt_tab_get_dashboard_tab()
    {
        $retval = '';
        $retval .= '<h2 class="dashContentHeading">'.get_string('bcgtmydashboard', 'block_bcgt').'</h2>';
        return $retval;
    }
    
    public static function bcgt_tab_get_courses_tab()
    {
        $retval = '';
        $retval .= '<h2 class="dashContentHeading">'.get_string('mycourses', 'block_bcgt').'</h2>';
        return $retval;
    }
    
    public static function bcgt_tab_get_trackers_tab()
    {
        $courseID = optional_param('cID', -1, PARAM_INT);
        //if its 1 its come from the front main page. 
        global $USER, $CFG, $PAGE, $DB;
        $jsModule = array(
            'name'     => 'block_bcgt',
            'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
            'requires' => array('base', 'io', 'node', 'json', 'event')
        );
        $PAGE->requires->js_init_call('M.block_bcgt.inittrackerstab', null, true, $jsModule);
        $retval = '<div id="trackersDashContainer">';
        $retval .= '<h2 class="dashContentHeading">'.get_string('mytrackers', 'block_bcgt').'</h2>';
        $retval .= '<p>'.get_string('mytrackersdesc', 'block_bcgt').'</p>';
        //If they have the capibility to add themselves to trackers
        //then show that link
        if($courseID != 1)
        {
            //then lets show the ones on this qual from here:
            $retval .= '<div id="coursequaltrackers">';
            if(has_capability('block/bcgt:viewallgrids', context_system::instance()))
            {
                $qualifications = search_qualification(-1, -1, -1, '', 
                    -1, null, $courseID, true, true, null);
            }
            else
            {
                $qualifications = get_role_quals($USER->id, array('teacher', 'editingteacher'), '', -1, $courseID);
            }
            if($qualifications)
            {
                $course = $DB->get_record_sql('SELECT * FROM {course} WHERE id = ?', array($courseID));
                if($course)
                {
                    $retval .= '<h3>'.$course->shortname.' '.get_string('qualifications','block_bcgt').'</h3>';
                }
                $retval .= DashTab::display_qual_trackers($qualifications, 'my');
            }
            $retval .= '</div>';
            $retval .= '<h3>'.get_string('allqualifications','block_bcgt').'</h3>';
        }    
        
        if(has_capability('block/bcgt:viewallgrids', context_system::instance()))
        {
            $qualifications = search_qualification(-1, -1, -1, '', 
                -1, null, -1, null, true, null); 
        }
        else
        {
            $qualifications = get_role_quals($USER->id, array('teacher', 'editingteacher'));
        }
        if($qualifications)
        {
            $retval .= DashTab::display_qual_trackers($qualifications, 'all');
        }
        $retval .= '</div>';
        return $retval;
    }
    
    public static function display_qual_trackers($qualifications, $type)
    {
        global $DB, $CFG;
        $courseID = optional_param('cID', -1, PARAM_INT);
        $retval = '<table class="trackersOverallTable">';
        $retval .= '<tr><th>'.get_string('report', 'block_bcgt').'</th>'.
                '<th>'.get_string('qualification', 'block_bcgt').'</th>';
        $retval .= '<th colspan="4">'.get_string('grids','block_bcgt').'</th>';
        $retval .= '</tr>';
        foreach($qualifications AS $qual)
        {
            //is the qualification on a course?
            //does the qualification haave any students?
            $expand = true;
            $expandClass = '';
            $onCourse = $DB->get_records_sql('SELECT * FROM {block_bcgt_course_qual} WHERE bcgtqualificationid = ?', array($qual->id));
            if(!$onCourse)
            {
               $expand = false;
               $expandClass='no';
            }

            $class = '';
            $hasStudents = $DB->get_records_sql('SELECT userqual.id FROM {block_bcgt_user_qual} userqual 
                JOIN {role} role ON role.id = userqual.roleid WHERE bcgtqualificationid = ? AND role.shortname = ?', 
                    array($qual->id, 'student'));
            if(!$hasStudents)
            {
                $class = 'noStudents';
            }

            //if have the ability, then allow to add students to this qual. 
            $retval .= "<tr><td class='simplequalreportheading$expandClass ".
                    "$class' id='sqrh_".$qual->id."_$type'><span class='report'>".
                    get_string('report','block_bcgt')."<span></td>";
            //<img src='$CFG->wwwroot/blocks/bcgt/pix/expandIcon.jpg'>
            if($expand)
            {
                $retval .= "<td>".bcgt_get_qualification_display_name($qual, true, ' ')."</td>";
                $retval .= '<td><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.'.
                        'php?g=s&qID='.$qual->id.'&aqID='.$qual->id.'&cID='.
                        $courseID.'">'.get_string('student', 'block_bcgt').'</a></td>';
                if(count(array_intersect(explode('|',BCGT_UNIT_VIEW_FAMILIES), array($qual->family))) > 0)
                {
                    $retval .= '<td><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.'.
                        'php?g=u&qID='.$qual->id.'&aqID='.$qual->id.'&cID='.
                        $courseID.'">'.get_string('unit', 'block_bcgt').'</a></td>';
                }
                if(count(array_intersect(explode('|',BCGT_CLASS_VIEW_FAMILIES), array($qual->family))) > 0)
                {
                    $retval .= '<td><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.'.
                        'php?g=c&qID='.$qual->id.'&aqID='.$qual->id.'&cID='.
                        $courseID.'">'.get_string('class', 'block_bcgt').'</a></td>';
                }
//                    $retval .= '<td><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.'.
//                            'php?g=a&qID='.$qual->id.'&aqID='.$qual->id.'&cID='.
//                            $courseID.'">'.get_string('assignment', 'block_bcgt').'</a></td>';
            }
            $retval .= '</tr>';
            $retval .= '<tr><td colspan="6">';
            $retval .= '<div class="simplequalreport" id="sqrc_'.$qual->id.'_'.$type.'"></div>';
            $retval .= '</td></tr>';
            //ajax display call
            //expand
            //will get tabs and reporting. 


        }
        $retval .= '</table>';
        return $retval;
    }
    
    public static function bcgt_tab_get_group_tab()
    {
        $courseID = optional_param('cID', -1, PARAM_INT);
        global $USER, $CFG, $PAGE, $DB;        
        $jsModule = array(
            'name'     => 'block_bcgt',
            'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
            'requires' => array('base', 'io', 'node', 'json', 'event')
        );
        $PAGE->requires->js_init_call('M.block_bcgt.initgroupstab', null, true, $jsModule);
        $retval = '<div id="groupsDashContainer">';
        $retval .= '<h2 class="dashContentHeading">'.get_string('mygroupings', 'block_bcgt');
        $retval .= '<span class="editgroups"> - <a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_user_groups.php">'.get_string('editmygroups', 'block_bcgt').'</a></span>';
        $retval .= '</h2>';
        $retval .= '<p>'.get_string('mytrackersdesc', 'block_bcgt').'</p>';
        //If they have the capibility to add themselves to trackers
        //then show that link
        $group = new Group();
        $checkAllGroups = false;
        
        if($courseID != 1 && $courseID != -1)
        {
            //then lets show the ones on this qual from here:
            $retval .= '<div id="coursequaltrackers">';
            if(has_capability('block/bcgt:viewallgrids', context_system::instance()))
            {
                $groupings = $group->get_all_possible_groups($courseID);
            }
            else
            {
                $groupings = $group->get_my_groups($courseID);
            }
            if($groupings)
            {
                $course = $DB->get_record_sql('SELECT * FROM {course} WHERE id = ?', array($courseID));
                if($course)
                {
                    $retval .= '<h3>'.$course->shortname.' '.get_string('groups','block_bcgt').'</h3>';
                }
                $retval .= DashTab::display_group_trackers($groupings, 'my');
            }
            $retval .= '</div>';
            $retval .= '<h3>'.get_string('allqualifications','block_bcgt').'</h3>';
        } 
        
        if(has_capability('block/bcgt:viewallgrids', context_system::instance()))
        {
            $checkAllGroups = true;
            $groupings = $group->get_all_possible_groups();
        }
        else
        {
            $groupings = $group->get_my_groups();
        }
        if($groupings)
        {
            $retval .= DashTab::display_group_trackers($groupings, 'all');
        }
        else
        {
            $retval .= '<p>'.get_string('nogroupaccess', 'block_bcgt').'</p>';
        }
        $retval .= '</div>';
        return $retval;
    }
    
    public static function display_group_trackers($groupings, $type)
    {
        global $DB, $CFG;
        $courseID = optional_param('cID', -1, PARAM_INT);
        $retval = '<table class="trackersOverallTable">';
        $retval .= '<tr><th>'.get_string('report', 'block_bcgt').'</th>'.
                '<th>'.get_string('grouping', 'block_bcgt').'</th>';
        $retval .= '<th colspan="4">'.get_string('grids','block_bcgt').'</th>';
        $retval .= '</tr>';
        foreach($groupings AS $grouping)
        {
            //is the qualification on a course?
            //does the qualification haave any students?
            $expand = true;
            $expandClass = '';
            $class = '';

            //if have the ability, then allow to add students to this qual. 
            $retval .= "<tr><td class='simplegroupreportheading$expandClass ".
                    "$class' id='sqrh_".$grouping->id."_$type'><span class='report'>".
                    get_string('report','block_bcgt')."<span></td>";
            //<img src='$CFG->wwwroot/blocks/bcgt/pix/expandIcon.jpg'>
            if($expand)
            {
                $retval .= "<td>".$grouping->name."</td>";
                $retval .= '<td><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.'.
                        'php?g=s&grID='.$grouping->id.'&cID='.
                        $courseID.'">'.get_string('student', 'block_bcgt').'</a></td>';
                $retval .= '<td><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.'.
                        'php?g=u&grID='.$grouping->id.'&cID='.
                        $courseID.'">'.get_string('unit', 'block_bcgt').'</a></td>';
                $retval .= '<td><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.'.
                        'php?g=c&grID='.$grouping->id.'&cID='.
                        $courseID.'">'.get_string('class', 'block_bcgt').'</a></td>';
//                    $retval .= '<td><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.'.
//                            'php?g=a&grID='.$group->id.'&agrID='.$group->id.'&cID='.
//                            $courseID.'">'.get_string('assignment', 'block_bcgt').'</a></td>';
            }
            $retval .= '</tr>';
            $retval .= '<tr><td colspan="6">';
            $retval .= '<div class="simplegroupreport" id="sqrc_'.$grouping->id.'_'.$type.'"></div>';
            $retval .= '</td></tr>';
        }
        $retval .= '</table>';
        return $retval;
    }
    
    public static function bcgt_tab_get_students_tab()
    {
        $retval = '';
        $retval .= '<h2 class="dashContentHeading">'.get_string('mystudents', 'block_bcgt').'</h2>';
        return $retval;
    }
    
    public static function bcgt_tab_get_units_tab()
    {
        $retval = '';
        $retval .= '<h2 class="dashContentHeading">'.get_string('myunits', 'block_bcgt').'</h2>';
        return $retval;
    }
    
    public static function bcgt_tab_get_team_tab()
    {
        $retval = '';
        $retval .= '<h2 class="dashContentHeading">'.get_string('myteam', 'block_bcgt').'</h2>';
        return $retval;
    }
    
    public static function bcgt_tab_get_help_tab()
    {
        $retval = '';
        $retval .= '<h2 class="dashContentHeading">'.get_string('help', 'block_bcgt').'</h2>';
        return $retval;
    }
    
    public static function bcgt_tab_get_report_tab()
    {
//        global $DB, $USER;
//        $courseID = optional_param('courseID', -1, PARAM_INT);
//        $cID = optional_param('cID', -1, PARAM_INT);
//        $studentID = optional_param('sID', -1, PARAM_INT);
//        $search = optional_param('search', '', PARAM_TEXT);
//        $qualID = optional_param('qID', -1, PARAM_INT);
//        $stuSearch = optional_param('stusearch', '', PARAM_TEXT);
//        $grade = optional_param('grade', '', PARAM_TEXT);
//        $retval = '';
//        $retval .= '<h2 class="dashContentHeading">'.get_string('myreports', 'block_bcgt').'</h2>';
//        
//        //select a qual
//        //get all quals or get 
//        //select a course
//        //search for a user and select them
//        //list of reports
//        //run. 
//        if(has_capability('block/bcgt:viewallgrids', context_system::instance()))
//        {
//            $viewAll = true;
//            $onCourse = null;
//            if($courseID != -1)
//            {
//                $onCourse = true;
//            }
//            $quals = search_qualification(-1, -1, -1, '', 
//                -1, null, -1, $onCourse, true); 
//            $courses = bcgt_get_courses_with_quals(-1);
//        }
//        else
//        {
//            $teacher = $DB->get_record_select('role', 'shortname = ?', array('editingteacher'));
//            $userQualRole = $DB->get_record_select('role', 'shortname = ?', array('teacher'));
//            $quals = get_users_quals($USER->id, $userQualRole->id, '', -1, -1, null);
//            if(!$quals)
//            {
//                $teacher = $DB->get_record_select('role', 'shortname = ?', array('editingteacher'));
//                $quals = get_users_quals($USER->id, $teacher->id);
//            }
//            $courses = bcgt_get_users_courses($USER->id, $teacher->id, true, -1, null);
//        }
//        $retval .= '<form name="gridselect" action="" method="POST" id="gridselect">';
//        $retval .= '<input type="hidden" id="cID" name="cID" value="'.$cID.'"/>';
//        $retval .= '<div class="inputContainer"><div class="inputLeft">'.
//                    '<label for="type">'.get_string('quals', 'block_bcgt').'</label></div>';
//        $retval .= '<div class="inputRight"><select name="qID" id="qual">'.
//                '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>';
//        if($quals)
//        {    
//            foreach($quals AS $qual)
//            {
//                if(count($quals) == 1)
//                {
//                    $qualID = $qual->id;
//                }
//                $selected = '';
//                if(count($quals) == 1 || ($qualID != -1 && $qualID == $qual->id))
//                {
//                    $selected = 'selected';
//                }
//                $retval .= '<option '.$selected.' value="'.$qual->id.'">'.
//                        bcgt_get_qualification_display_name($qual, true).'</option>';
//            }
//        }
//        $retval .= '</select>';
//        $retval .= '</div></div>';
//        $retval .= '<div class="inputContainer"><div class="inputLeft">'.
//                    '<label for="course">'.get_string('course').'</label></div>';
//        $retval .= '<div class="inputRight"><select name="courseID" id="course">'.
//                '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>';
//        if($courses)
//        {    
//            foreach($courses AS $course)
//            {
//                if(count($courses) == 1)
//                {
//                    $courseID = $course->id;
//                }
//                $selected = '';
//                if(count($courses) == 1 || ($courseID != -1 && $courseID == $course->id))
//                {
//                    $selected = 'selected';
//                }
//                $retval .= '<option '.$selected.' value="'.$course->id.'">'.
//                        $course->shortname.':'.$course->fullname.'</option>';
//            }
//        }
//        $retval .= '</select>';
//        $retval .= '</div></div>';
//        //then have a student or qual searchable
//        //drop down of all of their students
//        if(!$viewAll)
//        {
//            $stuRole = $DB->get_record_select('role', 'shortname = ?', array('student'));
//            $students = bcgt_get_users_users($USER->id, $userQualRole->id, $stuRole->id, $search);
//            if($students)
//            {
//                $retval .= '<div class="inputContainer"><div class="inputLeft">'.
//                '<label for="students">'.get_string('students', 'block_bcgt').'</label></div>';
//                $retval .= '<div class="inputRight"><select name="sID" id="studentID">'.
//                    '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>'; 
//                foreach($students AS $student)
//                {
//                    $selected = '';
//                    if($student->id == $studentID)
//                    {
//                        $selected = 'selected';
//                    }
//                    $retval .= '<option '.$selected.' value="'.$student->id.'">'.
//                            $student->username .' : '.$student->firstname.' '.$student->lastname.'</option>';
//                }
//                $retval .= '</select>';
//                $retval .= '</div></div>';
//            }
//        }
//        else {
//            $retval .= '<div class="inputContainer"><div class="inputLeft">'.
//                '<label for="stusearch">'.get_string('students', 'block_bcgt').'</label></div>';
//                $retval .= '<div class="inputRight"><input type="text"'.
//                        ' name="stusearch" id="stusearch" value="'.$stuSearch.'">';
//                $retval .= '<input type="submit" name="search" value="'.get_string('search', 'block_bcgt').'"/>';
//            $retval .= '</div></div>';
//            if($stuSearch != '')
//            {
//                //then lets find the students
//                $students = $DB->get_records_sql("SELECT * FROM {user} WHERE username ".
//                        "LIKE ? OR firstname LIKE ? OR lastname LIKE ?", array('%'.$stuSearch.'%', '%'.$stuSearch.'%', '%'.$stuSearch.'%'));
//                if($students)
//                {
//                    $retval .= '<div class="inputContainer"><div class="inputLeft">'.
//                    '<label for="students">'.get_string('students', 'block_bcgt').'</label></div>';
//                    $retval .= '<div class="inputRight"><select name="sID" id="studentID">'.
//                        '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>'; 
//                    foreach($students AS $student)
//                    {
//                        $selected = '';
//                        if($student->id == $studentID)
//                        {
//                            $selected = 'selected';
//                        }
//                        $retval .= '<option '.$selected.' value="'.$student->id.'">'.
//                                $student->username .' : '.$student->firstname.' '.$student->lastname.'</option>';
//                    }
//                    $retval .= '</select>';
//                    $retval .= '</div></div>';
//                }
//                
//            }
//        }
//        
//        
//        //get a list of the reports. 
//        $reporting = new Reporting();
//        $retval .= '<div class="inputContainer"><div class="inputLeft">'.
//                    '<label for="report">'.get_string('report', 'block_bcgt').'</label></div>';
//        $retval .= '<div class="inputRight">';
//        $retval .= $reporting->get_reports_drop_down();
//        $retval .= '</div></div>';
//        
//        $retval .= '<div class="inputContainer"><div class="inputLeft">'.
//                    '<label for="grade">'.get_string('gradetype', 'block_bcgt').'</label></div>';
//        $retval .= '<div class="inputRight">';
//        $retval .= '<select name="grade">';
//        $selected = '';
//        if($grade == 'full')
//        {
//            $selected = 'selected';
//        }
//        $retval .= '<option '.$selected.' value="full">'.get_string('gradetypealps','block_bcgt').'</option>';
//        $selected = '';
//        if($grade == 'weight')
//        {
//            $selected = 'selected';
//        }
//        $retval .= '<option '.$selected.' value="weight">'.get_string('gradetypeweighted','block_bcgt').
//                '</option>';
//        $selected = '';
//        if($grade == 'teach')
//        {
//            $selected = 'selected';
//        }
//        $retval .= '<option '.$selected.' value="teach">'.get_string('gradetypealps','block_bcgt').'</option></select>';
//        $retval .= '</div></div>';
//        $retval .= '<input type="submit" name="run" value="Fetch Report"/>';
//        $retval .= '</form>';
//        
//        if(isset($_POST['run']))
//        {
//            $retval .= $reporting->get_report();
//        }
//        return $retval;
    }
    
    public static function bcgt_tab_get_feedback_tab()
    {
        $retval = '';
        $retval .= '<h2 class="dashContentHeading">'.get_string('feedback', 'block_bcgt').'</h2>';
        return $retval;
    }
    
    public static function bcgt_tab_get_messages_tab()
    {
        $retval = '';
        $retval .= '<h2 class="dashContentHeading">'.get_string('messages', 'block_bcgt').'</h2>';
        return $retval;
    }
    
    public static function bcgt_tab_get_assignments_tab()
    {
        $retval = '';
        $retval .= '<h2 class="dashContentHeading">'.get_string('myassignments', 'block_bcgt').'</h2>';
        return $retval;
    }
    
    public static function bcgt_tab_get_alps_tab()
    {
        global $USER, $CFG, $PAGE, $DB;        
        $jsModule = array(
            'name'     => 'block_bcgt',
            'fullpath' => '/blocks/bcgt/js/block_bcgt.js',
            'requires' => array('base', 'io', 'node', 'json', 'event')
        );
        $alps = new Alps();
        $generate = optional_param('gen', false, PARAM_BOOL);
        if($generate)
        {
            $alps->perform_report_calculations(false);
        }
            
        $showCeta = false;
        $multiplier = 1;
        if(get_config('bcgt', 'aleveluseceta'))
        {
            $showCeta = true;
            $multiplier = 2;
        }
        
        $PAGE->requires->js_init_call('M.block_bcgt.initalpstab', null, true, $jsModule);
        //get all of the formal assessments possible:
        $project = new Project();
        $projects = $project->get_all_projects($centrallyManaged = null);
        $subTab = optional_param('subtab', 'qual', PARAM_TEXT);
        $courseID = optional_param('cID',-1,PARAM_INT);
        if($projects)
        {
            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ProjectsSorter.class.php');
            $projectSorter = new ProjectsSorter();
            usort($projects, array($projectSorter, "ComparisonDelegateByObjectDueDate"));
        }
        
        $retval = '<div class="bcgt_tab_content_container" id="bcgtAlpsTabContentContainer">';
        //subtabs
        $retval .= '<div id="bcgtDashTabs" class="tabs"><div class="tabtree">';
        $retval .= '<ul class="tabrow0">';
        $focus = '';
        if($subTab == 'qual')
        {
            $focus = 'focus';
        }
        $retval .= '<li class="'.$focus.'">'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=alps&cID='.$courseID.'&subtab=qual">'.
                '<span>'.get_string('byqual', 'block_bcgt').'</span></a></li>';
        $focus = '';
        if($subTab == 'course')
        {
            $focus = 'focus';
        }
        $retval .= '<li class="'.$focus.'">'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=alps&cID='.$courseID.'&subtab=course">'.
                '<span>'.get_string('bycourse', 'block_bcgt').'</span></a></li>';
        $focus = '';
        if($subTab == 'export')
        {
            $focus = 'focus';
        }
        $retval .= '<li class="'.$focus.'">'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=alps&cID='.$courseID.'&subtab=export">'.
                '<span>'.get_string('export', 'block_bcgt').'</span></a></li>';
        $retval .= '</ul>';
        $retval .= '</div></div>';
        //The loading symbol
        
        if($subTab == 'export')
        {
            $retval .= '<div id="exportalps">';
            $retval .= '<h3>'.get_string('exportoptions','block_bcgt').'</h3>';
            $retval .= '<form meth="POST" name="alpsexport" action="'.$CFG->wwwroot.'/blocks/bcgt/forms/export_alps.php" target="_blank">';
            $retval .= get_string('includeprojects','block_bcgt');
            $retval .= '<input type="checkbox" name="includeproj" checked="checked"><br />';
            $retval .= get_string('displayoptions', 'block_bcgt');
            $retval .= '<select id="displayoptions" name="displayoptions">';
            $retval .= '<option value="">'.get_string('pleaseselect', 'block_bcgt').'</option>';
            $retval .= '<option value="anonymous" '.((isset($_POST['displayoptions']) && $_POST['displayoptions'] == 'anonymous') ? "selected": "").'>Anonymous: No Qualification Information</option>';
            $retval .= '<option value="bysubject" '.((isset($_POST['displayoptions']) && $_POST['displayoptions'] == 'bysubject') ? "selected": "").'>Every Qualification</option>';
            $retval .= '<option value="byoption" '.((isset($_POST['displayoptions']) && $_POST['displayoptions'] == 'byoption') ? "selected": "").'>Options: A section per student Qualificaion</option>';
            $retval .= '</select><br />';
            $retval .= '<input type="submit" name="exportalps" value='.get_string('export','block_bcgt').'>';
            $retval .= '</form>';
            $retval .= '</div>';
            
            $retval .= '<div id="exportexamples">';
            $retval .= '<h3>Options Explained</h3>';
            $retval .= '<p>Including Assessments will add a section/scores for each Assessment. These will be shown for each summary section (Family, Target Qual, Category, Course, Qual and Student).<p>';
            $retval .= '<p>There are three display options. These are used in the Student sheet of the export. The options are: No Qualification Information, Every Qualification or Options. Examples of these are below:</p>';
            
            $retval .= '<h3>Anonymous - No Qualification Information</h3>';
            $retval .= '<p>This shows generic columns for SubjectA, SubjectB, SubjectC etc</p>';
            $retval .= '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/alps_anonymous_example.jpg"/>';
            
            $retval .= '<h3>Every Qualification - Every Qualification Has A Section</h3>';
            $retval .= '<p>This shows sections for every possible Qualification. If the user is not on this Qualification then blanks are produced. This is a very slow generating report.</p>';
            $retval .= '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/alps_subject_example.jpg"/>';
            
            $retval .= '<h3>Options - The Users Qualifications</h3>';
            $retval .= '<p>This shows sections for QualificationA, QualificationB, etc. But an extra column contains the Qualification Name</p>';
            $retval .= '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/alps_option_example.jpg"/>';
            $retval .= '</div>';
        }
        else
        {
            $retval .= '<div id="expand"></div>';
            $retval .= '<div id="alpsreportstable">';
            $retval .= '<table id="alpsreportstable" class="reportingtable" align="center">';
            $retval .= '<thead>';
            $retval .= '<tr>';
            if($subTab == 'qual')
            {
                //its by qual
                $retval .= '<th rowspan="3">Family/Subtype/Qual <br />(Click to Expand)</th>';
            }
            else
            {
                //its by course
                $retval .= '<th rowspan="3">Category/Course <br />(Click to Expand)</th>';
            }
            $retval .= '<th rowspan="3">'.
                    get_string('latestgrade','block_bcgt').'</th>';
            if($showCeta)
            {
                $retval .= '<th rowspan="3">'.
                    get_string('latestceta','block_bcgt').'</th>';
            }
                
            $retval .= '<th colspan="'.(count($projects) * $multiplier).'">'.get_string('formalassessments','block_bcgt').'</th>';
            $retval .= '</tr>';
            $subHeader = '';
            $subSubHeader = '';
            foreach($projects AS $project)
            {
                $subHeader .= '<th colspan="'.$multiplier.'">'.$project->get_name().'</th>';
                $subSubHeader .= '<th>'.get_string('grade','block_bcgt').'</th>';
                if($showCeta)
                {
                    $subSubHeader .= '<th>'.get_string('ceta','block_bcgt').'</th>';
                }
            }
            $retval .= '<tr>';
            $retval .= $subHeader;
            $retval .= '</tr>';
            $retval .= '<tr>';
            $retval .= $subSubHeader;
            $retval .= '</tr>';
            $retval .= '</thead>';
            $retval .= '<tbody>';

            //this is now where we need to do the families or the categories
            //if its by qual
            //then start by getting the qual stuff
            //if its by course then start with the top level categories

            if($subTab == 'qual')
            {
                $families = $alps->get_families_use_alps();
                if($families)
                {
                    $count = 0;
                    foreach($families AS $family)
                    {
                        $count++;
                        $retval .= '<tr rem="fam_'.$family->id.'|" id="e_fam_'.$family->id.'">';
                        $scores = $alps->get_family_overall_alps_scores($family->id);
                        $retval .= '<td>';
                        $retval .= '<span class="alpsreport expand" rem="fam_'.$family->id.'|" val="'.$family->id.'" type="fam">';
                        $retval .= $family->family;
                        $retval .= '</td>';
                        $retval .= Alps::get_scores_reports_display($scores, $showCeta, true);

                        foreach($projects AS $project)
                        {
                            $scores = $alps->get_family_project_overall_alps_scores($family->id, $project->get_id());
                            $retval .= Alps::get_scores_reports_display($scores, $showCeta, true);
                        }
                        $retval .= '</tr>';
                    }
                }
            }
            else
            {
                $count = 0;
                require_once($CFG->dirroot.'/lib/coursecatlib.php');
                $topCategories = coursecat::get(0)->get_children();
                foreach($topCategories AS $category)
                {
                    $categoryID = $category->__get('id');
                    $count++;
                    $scores = $alps->get_category_overall_alps_scores($categoryID);
                    if($scores)
                    {
                        $retval .= '<tr rem="cat_'.$categoryID.'|" id="e_cat_'.$categoryID.'">';
                        $retval .= '<td>';
                        $retval .= '<span class="alpsreport expand" rem="cat_'.$categoryID.'|" val="'.$categoryID.'" type="cat">';
                        $retval .= $category->__get('name');
                        $retval .= '</span></td>';
                        $retval .= Alps::get_scores_reports_display($scores, $showCeta, true);

                        foreach($projects AS $project)
                        {
                            $scores = $alps->get_category_project_overall_alps_scores($categoryID, $project->get_id());
                            $retval .= Alps::get_scores_reports_display($scores, $showCeta, true);
                        }
                        $retval .= '</tr>';
                    }
                }
            }
            $retval .= '</tbody>';
            $retval .= '</table>';
            $retval .= '</div>';
            $retval .= '<input type="hidden" name="alpsrows" id="alpsrows" value=""/>';
            $retval .= '<input type="hidden" name="cid" id="cid" value="'.$courseID.'"/>';
            $retval .= '<div id="lastRun">';
            $lastRun = $alps->get_alps_report_run_date();
            $lastRunTimeDate = 'N/A';
            if($lastRun)
            {
                $lastRunTimeDate = date('Y-m-d : H:i', $lastRun->value);
            }
            $retval .= 'Report Last Generated : '.$lastRunTimeDate;
            $retval .= '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=alps&cID='.$courseID.'&gen=true">Generate Scores Now</a>';
            $retval .= '</div>';
            $retval .= '<div class="information description">';
            $retval .= '<p class="alpsreportdesc">'.ALPS::get_description().'</p>';
            $retval .= '</div>';
        }
        
        
        $retval .= '</div>';
        return $retval;
    }
    
    public static function bcgt_tab_get_reporting_tab()
    {
        
        global $CFG, $USER, $DB;
        
        require_once $CFG->dirroot . '/blocks/bcgt/classes/core/ReportingSystem.class.php';
        require_once $CFG->dirroot . '/blocks/bcgt/classes/core/CoreReports.class.php';
        $action = optional_param('action', false, PARAM_TEXT);
        $id = optional_param('id', false, PARAM_INT);
        
        $retval = '<div id="bcgt_reporting">';
        $retval .= '<h2 class="c">'.get_string('dashtabreporting', 'block_bcgt').'</h2>';
        
        $retval .= '<p class="c"><a href="my_dashboard.php?tab=reporting&action=create">Create Report</a>';
        $retval .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="my_dashboard.php?tab=reporting&';
        $retval .= 'action=view">View Saved Reports</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $retval .= '<a href="my_dashboard.php?tab=reporting&action=bespoke">View Custom Reports</a></p>';
        $retval .= '<br><br>';
        $retval .= '<p style="color:red;text-align:center;">Reporting is still in development, so there may be issues/glitches.</p>';
        $retval .= '<br><br>';
                
        if ($action == 'create')
        {
            $retval .= ReportingSystem::display_create_form();
        }
        elseif ($action == 'view')
        {
            if ($id)
            {
                $retval .= ReportingSystem::display_view_report($id);
            }
            else
            {
                $retval .= ReportingSystem::display_view_reports();
            }
        }
        elseif($action == 'bespoke')
        {
            if($id)
            {
                $retval .= CoreReports::display_view_report($id);
            }
            else
            {
                $retval .= CoreReports::display_view_reports();
                
            }
        }       
        else
        {
            global $CFG;
            //this needs a table
            $retval .= '<table id="reporting_main">';
            $retval .= '<tr><th><a href="my_dashboard.php?tab=reporting&action=create">'.
                    'Create Report</a></th><th>'.
                    '<a href="my_dashboard.php?tab=reporting&action=view">Saved Reports</a>'.
                    '</th><th><a href="my_dashboard.php?tab=reporting&action=bespoke">'.
                    'Custom Reports</a></th></tr>';
            $retval .= '<tr>';
            $retval .= '<td class="create_report">';
            $retval .= '<span class="report_desc">';
            $retval .= '<p>Create a report based upon elements and filters selected. Options include:</p>';
            $retval .= '<ul>';
            $retval .= '<li>Starting point: (Qual, Unit or Course)</li>';
            $retval .= '<li>Filters: Levels, Subtypes</li>';
            $retval .= '<li>Elements: Credits, Target Comparisons, Counts (students, quals, units)</li>';
            $retval .= '<li>Reports can be drilled down from overview to granular students</li>';
            $retval .= '<li>Reports can be saved or exported</li>';
            $retval .= '</ul>';
            $retval .= '</span>';
            $retval .= '<span class="report_icon">';
            $retval .= '<a href="my_dashboard.php?tab=reporting&action=view"><img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/Create_Report_Icon.jpg"/>'; 
            $retval .= '</a></span>';
            $retval .= '</td>';
            $retval .= '<td class="view_report">';
            $retval .= '<span class="report_icon">';
            $retval .= '<a href="my_dashboard.php?tab=reporting&';
            $retval .= 'action=view"><img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/View_Report_Icon.jpg"/>'; 
            $retval .= '</a></span>';
            $retval .= '<span class="report_desc">';
            $retval .= '<p>View a report template saved before.</p>';
            $retval .= '<ul>';
            $retval .= '<li>View a list of saved reports</li>';
            $retval .= '<li>View simple statistics of those reports</li>';
            $retval .= '<li>Edit, Run, Export or Save these reports.</li>';
            $retval .= '</ul>';
            $retval .= '</span>';
            $retval .= '</td>';
            $retval .= '<td class="custom_report">'; 
            $retval .= '<span class="report_icon">';
 
            $retval .= '<a href="my_dashboard.php?tab=reporting&action=bespoke"><img src="'.$CFG->wwwroot.'/blocks/bcgt/images/reports/Custom_Report_Icon.jpg"/>'; 
            $retval .= '</a></span>';
            $retval .= '<span class="report_desc">';
            $retval .= '<p>View and run set Grade Tracker reports</p>';
            $retval .= '<ul>';
            $retval .= '<li>View a list of set reports</li>';
            $retval .= '<li>Specific filters and options</li>';
            $retval .= '<li>Specific format of headings</li>';
            $retval .= '<li>Each can be run or exported</li>';
            $retval .= '<li>Examples: Current grades, Value Added scores, Compliency checks, Comparisons</li>';
            $retval .= '</ul>';
            $retval .= '</span>';
            $retval .= '</td>';
            $retval .= '</tr></table>';
            $retval .= 'Please choose an action';
        }
        $retval .= '</div>';
        return $retval;
        
    }
    
    
    
}
?>
