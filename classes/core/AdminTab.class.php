<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 */

/**
 * Description of AdminTab
 *
 * @author mchaney
 */
class AdminTab extends DashTab{
    public function AdminTab()
    {
        
    }
    
    public function get_tab_view($courseID)
    {
        global $COURSE, $CFG;
        $courseContext = context_course::instance($COURSE->id);
        if($courseID != -1)
        {
            $courseContext = context_course::instance($courseID);
        }
        else
        {
            $courseContext = context_course::instance($COURSE->id);
        }
        
        
        $retval = '<div id="bcgtAdminConsole" class="bcgt_three_c_container">';
            $retval .= '<div class="bcgt_col_one bcgt_col">';
                $retval .= '<div class="bcgt_admin_box">';
                $retval .= '<h2 class="bcgt_dash_subtitle bcgt_admin_title">
                    '.get_string('qualifications', 'block_bcgt').'</h2>';
                $retval .= $this->get_qual_options($courseID);
                $retval .= '</div>';
                $retval .= '<div class="bcgt_admin_box">';
                    $retval .= '<h2 class="bcgt_dash_subtitle bcgt_admin_title">
                        '.get_string('activitiesfas', 'block_bcgt').'</h2>';
                    $retval .= $this->get_activity_options($courseID);
                $retval .= '</div>';
                
                $retval .= '<div class="bcgt_admin_box">';
                    $retval .= '<h2 class="bcgt_dash_subtitle bcgt_admin_title">
                        '.get_string('general', 'block_bcgt').'</h2>';
                    $retval .= $this->get_general_options();
                $retval .= '</div>';
                
            $retval .= '</div>';
            $retval .= '<div class="bcgt_col_two bcgt_col">';
                $retval .= '<div class="bcgt_admin_box">';
                $retval .= '<h2 class="bcgt_dash_subtitle bcgt_admin_title">
                    '.get_string('units', 'block_bcgt').'</h2>';
                $retval .= $this->get_unit_options($courseID);
                $retval .= '</div>';
                if(has_capability('block/bcgt:editqualfamilysettings', $courseContext))
                {
                    $retval .= '<div class="bcgt_admin_box">';
                    $retval .= '<h2 class="bcgt_dash_subtitle bcgt_admin_title">
                        '.get_string('qualsettingsandtests', 'block_bcgt').'</h2>';
                    $retval .= $this->get_qual_family_options($courseID);
                    $retval .= '</div>';
                }
                $retval .= '<div class="bcgt_admin_box">';
                    $retval .= '<h2 class="bcgt_dash_subtitle bcgt_admin_title">
                        '.get_string('criteria', 'block_bcgt').'</h2>';
                    $retval .= $this->get_criteria_options($courseID);
                    $retval .= '</div>';
            $retval .= '</div>';
            $retval .= '<div class="bcgt_col_three bcgt_col">';
                $retval .= '<div class="bcgt_admin_box">';
                $retval .= '<h2 class="bcgt_dash_subtitle bcgt_admin_title">
                    '.get_string('users', 'block_bcgt').'</h2>';
                $retval .= $this->get_user_options($courseID);
                $retval .= '</div>';  
                $retval .= '<div class="bcgt_admin_box">';
                $retval .= '<h2 class="bcgt_dash_subtitle bcgt_admin_title">
                    '.get_string('gradesettings', 'block_bcgt').'</h2>';
                $retval .= $this->get_grade_options($courseID);
                $retval .= '</div>'; 
                
                $retval .= '<div class="bcgt_admin_box">';
                $retval .= '<h2 class="bcgt_dash_subtitle bcgt_admin_title">
                    '.get_string('archivedata', 'block_bcgt').'</h2>';
                $retval .= $this->get_archive_options($courseID);
                $retval .= '</div>'; 
                
                $retval .= '<div class="bcgt_admin_box">';
                $retval .= '<h2 class="bcgt_dash_subtitle bcgt_admin_title">
                    '.get_string('importexportdata', 'block_bcgt').'</h2>';
                $retval .= $this->get_import_options($courseID);
                $retval .= '</div>'; 
            $retval .= '</div>';
        $retval .= '</div>';
        return $retval;
    }
    
    private function get_qual_options($courseID)
    {
        global $COURSE, $CFG;
        if($courseID != -1)
        {
            $courseContext = context_course::instance($courseID);
        }
        else
        {
            $courseContext = context_course::instance($COURSE->id);
        }
        $retval = '<ul class="bcgt_list bcgt_admin_list">';
        //the order is: 
        //'My Dashboard'
        //'Trackers' 'Courses' 'Students' 'Team' 'Units' 'Reports' 'Assignments';
        //'Admin' 'Help' 'Feedback' 'Messages';
        if(has_capability('block/bcgt:addnewqual', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_qual.php?cID='.$courseID.'"'. 
                    'title="'.get_string('addnewqualhelp', 'block_bcgt').'">'.
                    get_string('addnewqual', 'block_bcgt').'</a></li>';
        }
        if(has_capability('block/bcgt:editqual', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/qual_select.php?cID='.$courseID.'"'. 
                    'title="'.get_string('editqualhelp', 'block_bcgt').'">'.
                    get_string('editqual', 'block_bcgt').'</a></li>';
        }
        if(has_capability('block/bcgt:editqualunit', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/qual_select.php?cID='.$courseID.'"'. 
                    'title="'.get_string('editqualunithelp', 'block_bcgt').'">'.
                    get_string('editqualunit', 'block_bcgt').'</a></li>';
        }
        if(has_capability('block/bcgt:editqualscourse', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/course_select.php?oCID='.$courseID.'"'. 
                    'title="'.get_string('editqualscoursehelp', 'block_bcgt').'">'.
            get_string('editqualscourse', 'block_bcgt').'</a></li>';
        }
//        if(($linkQualCourse = get_config('bcgt', 'linkqualteacher')) && 
//                has_capability('block/bcgt:editteacherqual', $courseContext))
//        {
//            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_teacher_qual.php?cID='.$courseID.'"'. 
//                    'title="'.get_string('editteacherqualhelp', 'block_bcgt').'">'.
//                    get_string('editteacherqual', 'block_bcgt').'</a></li>';
//        }
//        if(($linkQualCourse = get_config('bcgt', 'linkqualstudent')) && 
//                has_capability('block/bcgt:editstudentqual', $courseContext))
//        {
//            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/qual_select.php?cID='.$courseID.'"'. 
//                    'title="'.get_string('editstudentqualhelp', 'block_bcgt').'">'.
//                    get_string('editstudentqual', 'block_bcgt').'</a></li>';
//        }
        if(has_capability('block/bcgt:editstudentunits', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/qual_select.php?cID='.$courseID.'"'. 
                    'title="'.get_string('editstudentunitsqualhelp', 'block_bcgt').'">'.
                    get_string('editstudentunitsqual', 'block_bcgt').'</a></li>';
        }
        
        if(has_capability('block/bcgt:deletequalification', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/delete_quals.php"'. 
                    'title="'.get_string('deletequalshelp', 'block_bcgt').'">'.
                    get_string('deletequals', 'block_bcgt').'</a></li>';
        }
        if(has_capability('block/bcgt:editqual', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/qual_select.php?cID='.$courseID.'"'. 
                    'title="'.get_string('copyqualhelp', 'block_bcgt').'">'.
                    get_string('copyqual', 'block_bcgt').'</a></li>';
        }
        
        // Bespoke
        if(has_capability('block/bcgt:addqualgradingstructure', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grading_structure.php?cID='.$courseID.'&type=qual"'. 
                    'title="'.get_string('addnewgradingstructurehelp', 'block_bcgt').'">'.
                    get_string('addnewgradingstructure', 'block_bcgt').'</a></li>';
        }
        
        
        $retval .= '</ul>';
        return $retval;
    }
    
    private function get_qual_family_options($courseID)
    {
        global $CFG;
        //this needs to load up all of the qualification families
        //show alink to each
//        $retval = '<ul class="bcgt_list bcgt_admin_list">';
//            $retval .= '<li>Install New Plugin(s)</li>';
//            $retval .= '<li>Upgrade Existing Plugin(s)</li>';
//        $retval .= '</ul>';
        
        $families = get_qualification_type_families_used();
        $retval = '';
        if($families)
        {
            $retval = '<ul class="bcgt_list bcgt_admin_list">';
            foreach($families AS $family)
            {                
                $pluginName = get_plugin_name($family->id);
                if($pluginName)
                {
                   $path = $CFG->dirroot.'/blocks/bcgt/plugins/'.$pluginName.'/forms/qual_settings.php'; 
                }
                //check if file exists. 
                if(file_exists($path))
                {
                    $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/qual_settings.php?cID='.$courseID.'&fID='.
                        $family->id.'">'.$family->family.' '.
                        get_string('settings').'</a></li>';
                }
            }
            $retval .= '</ul>';
        }
        else
        {
            $retval .= '<p></p>';
        }
        $retval .= '<ul>';
        $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/tests.php?cID='.$courseID.'">'.
                get_string('unittests', 'block_bcgt').'</a></li>';
        $retval .= '</ul>';
        return $retval;
    }
    
    private function get_general_options()
    {
        global $CFG, $COURSE, $USER;
        
        $retval = '<ul class="bcgt_list bcgt_admin_list">';
//            $retval .= '<li>'.get_string('manageactivitylinks', 'block_bcgt').'</li>';
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/general_settings.php"'. 
                    'title="'.get_string('generalsettings', 'block_bcgt').'">'.
                    get_string('generalsettings', 'block_bcgt').'</a></li>';
            $retval .= '<li><a href="'.$CFG->wwwroot.'/admin/settings.php?section=blocksettingbcgt"'. 
                    'title="'.get_string('adminpluginsettings', 'block_bcgt').'">'.
                    get_string('adminpluginsettings', 'block_bcgt').'</a></li>';
        $retval .= '</ul>';
        
        
        $currentContext = context_course::instance($COURSE->id);
        //can the user view grids by the different methods?
        //>>BEDCOLL TODO this should check if the users are associated with any quals
        if (has_capability('block/bcgt:viewclassgrids', $currentContext)){
                $retval .= '<ul class="list">';
                $retval .= '<li>'.get_string('viewEditBy', 'block_bcgt').'<ul>';

                $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=s&cID='.$COURSE->id.'">';   			
                $retval .= get_string('byStudent', 'block_bcgt').'</a></li>';   			

                $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=u&cID='.$COURSE->id.'">';   			
                $retval .= get_string('byunit', 'block_bcgt').'</a></li>'; 

                $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=c&cID='.$COURSE->id.'">';   			
                $retval .= get_string('byClass', 'block_bcgt').'</a></li>';
                
                $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=r&cID='.$COURSE->id.'">';   			
                $retval .= get_string('byregistergroup', 'block_bcgt').'</a></li>';
                
                if(get_config('bcgt','alevelusefa'))
                {
                    $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=a&cID='.$COURSE->id.'">';   			
                    $retval .= get_string('byassessment', 'block_bcgt').'</a></li>';
                }
                
                $retval .= '</ul>';
        }
        //>>BEDCOLL TODO this should be the user context
        if (has_capability('block/bcgt:viewowngrid', $currentContext)){
            if(does_user_have_tracking_sheets($USER->id))
            {
                $retval .= '<ul class="list">';
                //TODO will check if the student actually has a grid!
                $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/my_grids.php?g=s&cID='.$COURSE->id.'">';   			
                $retval .= get_string('mytrackers', 'block_bcgt').'</a></li>'; 
                $retval .= '</ul>';
            }
        }
        if ($COURSE->id != 1 && has_capability('block/bcgt:addqualtocurentcourse', $currentContext)){
            $retval .= '<ul class="list">';
            //TODO will check if the course has a qual already
            $count = bcgt_count_quals_course($COURSE->id);
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_course_qual.php?oCID='.$COURSE->id.'&cID='.$COURSE->id.'">';   			
            $retval .= get_string('editcoursequals', 'block_bcgt').'</a> ['.$count.']</li>'; 
            $retval .= '</ul>';
        }
        if($COURSE->id != 1 && has_capability('block/bcgt:manageactivitylinks', $currentContext))
        {
            $retval .= '<ul class="list">';
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/activities.php?cID='.$COURSE->id.'">';   			
            $retval .= get_string('viewactivitylinks', 'block_bcgt').'</a></li>'; 
            $retval .= '</ul>';
        }
        
        
        
        return $retval;
        
    }
    
    private function get_activity_options($courseID)
    {
        global $CFG;
        $currentContext = context_system::instance();
        //this needs to load up all of the qualification families
        //show alink to each
        $retval = '<ul class="bcgt_list bcgt_admin_list">';
//            $retval .= '<li>'.get_string('manageactivitylinks', 'block_bcgt').'</li>';
        $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/assessments.php?cID='.$courseID.'"'. 
                'title="'.get_string('managefahelp', 'block_bcgt').'">'.
                get_string('managefas', 'block_bcgt').'</a></li>';
        $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/assessment_grades.php?cID='.$courseID.'"'. 
                'title="'.get_string('managefagradeshelp', 'block_bcgt').'">'.
                get_string('managefagrades', 'block_bcgt').'</a></li>';
        if(has_capability('block/bcgt:managemodlinking', $currentContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/mod_linking.php?cID='.$courseID.'"'. 
                'title="'.get_string('managemodlinkinghelp', 'block_bcgt').'">'.
                get_string('managemodlinking', 'block_bcgt').'</a></li>';
        }
        $retval .= '</ul>';
        return $retval;
    }
    
    private function get_unit_options($courseID)
    {
        global $COURSE, $CFG;
        if($courseID != -1)
        {
            $courseContext = context_course::instance($courseID);
        }
        else
        {
            $courseContext = context_course::instance($COURSE->id);
        }
        $retval = '<ul class="bcgt_list bcgt_admin_list">';
        if(has_capability('block/bcgt:addnewunit', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_unit.php?cID='.$courseID.'"'. 
                    'title="'.get_string('addnewunithelp', 'block_bcgt').'">'.
                    get_string('addnewunit', 'block_bcgt').'</a></li>';
        }
        if(has_capability('block/bcgt:editunit', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/unit_select.php?cID='.$courseID.'"'. 
                    'title="'.get_string('editunithelp', 'block_bcgt').'">'.
                    get_string('editunit', 'block_bcgt').'</a></li>';
        }
        if(has_capability('block/bcgt:editqualunit', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_unit_qual.php?cID='.$courseID.'"'. 
                    'title="'.get_string('editqualunitshelp', 'block_bcgt').'">'.
                    get_string('editqualunits', 'block_bcgt').'</a></li>';
        }
        if(has_capability('block/bcgt:editstudentunits', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_students_units.php?cID='.$courseID.'&a=u"'. 
                    'title="'.get_string('editstudentunitshelp', 'block_bcgt').'">'.
                    get_string('editstudentunits', 'block_bcgt').'</a></li>';
        }
        
        if(has_capability('block/bcgt:transferstudentsunits', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/transfer_units_new.php"'. 
                    'title="'.get_string('transferstudentsunitshelp', 'block_bcgt').'">'.
                    get_string('transferstudentsunits', 'block_bcgt').'</a></li>';
        }
        
        if(has_capability('block/bcgt:deleteunit', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/delete_units.php?a=u"'. 
                    'title="'.get_string('deleteunitshelp', 'block_bcgt').'">'.
                    get_string('deleteunits', 'block_bcgt').'</a></li>';
        }
        
        // Bespoke
        if(has_capability('block/bcgt:addunitgradingstructure', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grading_structure.php?cID='.$courseID.'&type=unit"'. 
                    'title="'.get_string('addnewgradingstructurehelp', 'block_bcgt').'">'.
                    get_string('addnewgradingstructure', 'block_bcgt').'</a></li>';
        }
        
        $retval .= '</ul>';
        return $retval;
    }
    
    private function get_criteria_options($courseID)
    {
        
        global $COURSE, $CFG;
        if($courseID != -1)
        {
            $courseContext = context_course::instance($courseID);
        }
        else
        {
            $courseContext = context_course::instance($COURSE->id);
        }
        
        $retval = '<ul class="bcgt_list bcgt_admin_list">';
        
        if(has_capability('block/bcgt:addcriteriagradingstructure', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grading_structure.php?cID='.$courseID.'&type=crit"'. 
                    'title="'.get_string('addnewgradingstructurehelp', 'block_bcgt').'">'.
                    get_string('addnewgradingstructure', 'block_bcgt').'</a></li>';
            
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grading_structure.php?cID='.$courseID.'&type=critnonmet"'. 
                    'title="'.get_string('addnewnonmetcritvalueshelp', 'block_bcgt').'">'.
                    get_string('addnewnonmetcritvalues', 'block_bcgt').'</a></li>';
            
        }
        
        $retval .= '</ul>';
        return $retval;
        
        
    }
    
    private function get_user_options($courseID)
    {
        global $COURSE, $CFG;
        if($courseID != -1)
        {
            $courseContext = context_course::instance($courseID);
        }
        else
        {
            $courseContext = context_course::instance($COURSE->id);
        }
        $retval = '<ul class="bcgt_list bcgt_admin_list">';
        if(has_capability('block/bcgt:editmanagersteam', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_users_users.php?cID='.$courseID.'&role=gtmanager"'. 
                    'title="'.get_string('editmanagerteamhelp', 'block_bcgt').'">'.
                    get_string('editmanagerteam', 'block_bcgt').'</a></li>';
        }
        if(has_capability('block/bcgt:editmentorsmentees', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_users_users.php?cID='.$courseID.'&role=gttutor"'. 
                    'title="'.get_string('editmentorsstudentshelp', 'block_bcgt').'">'.
                    get_string('editmentorsstudents', 'block_bcgt').'</a></li>';
        }
//        if(($linkQualCourse = get_config('bcgt', 'linkqualteacher')) && 
//                has_capability('block/bcgt:editteacherqual', $courseContext))
//        {
//            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_qual_user.php?cID='.$courseID.'&role=teacher"'. 
//                    'title="'.get_string('editqualsteacherhelp', 'block_bcgt').'">'.
//                    get_string('editqualsteacher', 'block_bcgt').'</a></li>';
//        }
//        if(($linkQualCourse = get_config('bcgt', 'linkqualstudent')) && 
//                has_capability('block/bcgt:editstudentqual', $courseContext))
//        {
//            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_qual_user.php?cID='.$courseID.'&role=student"'. 
//                    'title="'.get_string('editqualsstudenthelp', 'block_bcgt').'">'.
//                    get_string('editqualsstudent', 'block_bcgt').'</a></li>';
//        }
        if(has_capability('block/bcgt:editstudentunits', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_students_units.php?cID='.$courseID.'&a=s"'. 
                    'title="'.get_string('editunitsstudenthelp', 'block_bcgt').'">'.
                    get_string('editunitsstudent', 'block_bcgt').'</a></li>';
        }
        $retval .= '</ul>';
        return $retval;
    }
    
    function get_import_options($courseID)
    {
        global $COURSE, $CFG;
        if($courseID != -1)
        {
            $courseContext = context_course::instance($courseID);
        }
        else
        {
            $courseContext = context_course::instance($COURSE->id);
        }
        
        $retval = '<ul class="bcgt_list bcgt_admin_list">';
        
        if  ( has_capability('block/bcgt:exportqualspec', $courseContext)){
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/qual_select.php?cID='.$courseID.'">'.get_string('exportspec', 'block_bcgt').'</a></li>';
        }
        
        if  ( has_capability('block/bcgt:importqualspec', $courseContext)){
//            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/import_spec.php">'.get_string('importspec', 'block_bcgt').'</a></li>';
        }
        
        $retval .= "</ul>";
        $retval .= '<ul class="bcgt_list bcgt_admin_list">';
        
        if(has_capability('block/bcgt:importdata', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/import.php?cID='.$courseID.'&a=pl"'. 
                    'title="'.get_string('importhelp', 'block_bcgt').'">'.
                    get_string('import', 'block_bcgt').'</a></li>';
        }
//        if(has_capability('block/bcgt:exportdata', $courseContext))
//        {
//            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/export.php?cID='.$courseID.'"'. 
//                    'title="'.get_string('exporthelp', 'block_bcgt').'">'.
//                    get_string('export', 'block_bcgt').'</a></li>';
//        }
        if(has_capability('block/bcgt:rundatacleanse', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/data_cleanse.php?cID='.$courseID.'"'. 
                    'title="'.get_string('datacleansehelp', 'block_bcgt').'">'.
                    get_string('datacleanse', 'block_bcgt').'</a></li>';
        }
        if(has_capability('block/bcgt:checkuseraccess', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/user_access.php?cID='.$courseID.'"'. 
                    'title="'.get_string('useraccesshelp', 'block_bcgt').'">'.
                    get_string('useraccess', 'block_bcgt').'</a></li>';
            
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/user_data.php?cID='.$courseID.'"'. 
                    'title="'.get_string('userdatahelp', 'block_bcgt').'">'.
                    get_string('userdatacheck', 'block_bcgt').'</a></li>';
            
        }
        $retval .= '</ul>';
        return $retval;
    }
    
    function get_grade_options($courseID)
    {
        global $COURSE, $CFG;
        if($courseID != -1)
        {
            $courseContext = context_course::instance($courseID);
        }
        else
        {
            $courseContext = context_course::instance($COURSE->id);
        }
        $retval = '<ul class="bcgt_list bcgt_admin_list">';
        if(has_capability('block/bcgt:edittargetgradesettings', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_target_grade_settings.php?cID='.$courseID.'"'. 
                    'title="'.get_string('edittargetgradesettingshelp', 'block_bcgt').'">'.
                    get_string('edittargetgradesettings', 'block_bcgt').'</a></li>';
        }
        if(has_capability('block/bcgt:editqualweightings', $courseContext) && get_config('bcgt','allowalpsweighting'))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/qual_weightings.php?cID='.$courseID.'"'. 
                    'title="'.get_string('qualificationweightingsettingshelp', 'block_bcgt').'">'.
                    get_string('qualificationweightingsettings', 'block_bcgt').'</a></li>';
        }
//        if(has_capability('block/bcgt:editpriorqualsettings', $courseContext))
//        {
//            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_prior_learning_settings.php?cID='.$courseID.'"'. 
//                    'title="'.get_string('editpriorlearningsettingshelp', 'block_bcgt').'">'.
//                    get_string('editpriorlearningsettings', 'block_bcgt').'</a></li>';
//        }
//        if(has_capability('block/bcgt:calculateaveragegcsescore', $courseContext))
//        {
//            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/settings?cID='.$courseID.'"'. 
//                    'title="'.get_string('calculateaveragegcsescoreshelp', 'block_bcgt').'">'.
//                    get_string('calculateaveragegcsescores', 'block_bcgt').'</a></li>';
//        }
        if(has_capability('block/bcgt:calculatetargetgrades', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/calculate_user_values.php?cID='.$courseID.'&a=tg"'. 
                    'title="'.get_string('calculatetargetgradeshelp', 'block_bcgt').'">'.
                    get_string('calculatetargetgrade', 'block_bcgt').'</a></li>';
        }
        if(has_capability('block/bcgt:calculatepredictedgrades', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/calculate_user_values.php?cID='.$courseID.'&a=pg"'. 
                    'title="'.get_string('calculatepredictedgradeshelp', 'block_bcgt').'">'.
                    get_string('calculatepredictedgrade', 'block_bcgt').'</a></li>';
        }
        $retval .= '</ul>';
        return $retval;
    }
    
    function get_archive_options($courseID)
    {   
        global $COURSE, $CFG;
        if($courseID != -1)
        {
            $courseContext = context_course::instance($courseID);
        }
        else
        {
            $courseContext = context_course::instance($COURSE->id);
        }
        $retval = '<ul class="bcgt_list bcgt_admin_list">';
        if(has_capability('block/bcgt:archivestudentdata', $courseContext))
        {
            $retval .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/archive_data.php?type='.Archive::STUDENTARCHIVETYPE.'&cID='.$courseID.'"'. 
                    'title="'.get_string('archivestudentdatahelp', 'block_bcgt').'">'.
                    get_string('archivestudentdata', 'block_bcgt').'</a></li>';
        }
        $retval .= '</ul>';
        return $retval;
    }
    
}
//TODO : settings page for calculating averagegcsestudents, calculating targetgrades
    //his needs to be able to pick which quals/students as optional. 
//Weightings insert: Need to check they dont already exist e.g. by percentage or by number
//Entry Quals insert: Need to checj they dont already exist e.g. by Name
//Entry Grades insert: Need to check they dont already exist e.g. by grade
//test. 

//Formal Assessments/. 


?>
