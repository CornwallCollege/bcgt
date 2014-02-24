<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

class block_bcgt extends block_base {
    public function init() {
        $this->title = get_string('gradetracker', 'block_bcgt');
    }
    
    public function has_config() {
        return true;
    }
    
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        global $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        
        global $CFG, $COURSE, $USER;
        $this->content         =  new stdClass;
        $this->content->text = "";
        $currentContext = context_course::instance($COURSE->id);
        //can the user see the 'My Tracking Area' Link -> The link to the teachers page where they view the tabs.
//        if ((($this->page->course->id == SITEID) && (has_capability('block/bcgt:viewdashboard', context_system::instance())))
//            || has_capability('block/bcgt:viewdashboard', $currentContext) || has_capability('block/bcgt:viewdashboard',$this->page->context)){
        if(has_capability('block/bcgt:viewadmintab', $currentContext))
        {
            $this->content->text .= '<ul class="list">';
            $this->content->text .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?&tab=adm&cID='.$COURSE->id.'">';
            $this->content->text .= get_string('admin', 'block_bcgt').'</a></li>';
            $this->content->text .= '</ul>';
        }
        if(has_capability('block/bcgt:viewdashboard', $currentContext) && has_capability('block/bcgt:viewclassgrids', $currentContext)){        
            $this->content->text .= '<ul class="list">';
            $this->content->text .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?cID='.$COURSE->id.'&tab=track">';
            $this->content->text .= get_string('bcgtmytrackers', 'block_bcgt').'</a></li>';
            if(get_config('bcgt', 'usegroupsingradetracker'))
            {
                $this->content->text .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?cID='.$COURSE->id.'&tab=grouping">';
                $this->content->text .= get_string('bcgtmygroups', 'block_bcgt').'</a></li>';
            }
            $this->content->text .= '</ul>';
        }
        //can the user view grids by the different methods?
        //>>BEDCOLL TODO this should check if the users are associated with any quals
        if (has_capability('block/bcgt:viewclassgrids', $currentContext)){
            $viewALL = (has_capability('block/bcgt:viewallgrids', context_system::instance()));
            $qualFamilies = bcgt_get_users_qual_families($COURSE->id, $viewALL, true);
            
            
			$this->content->text .= '<ul class="list">';
			$this->content->text .= '<li>'.get_string('viewEditBy', 'block_bcgt').'<ul>';

			$this->content->text .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=s&cID='.$COURSE->id.'&il=true">';   			
			$this->content->text .= get_string('byStudent', 'block_bcgt').'</a></li>';   			

			if($COURSE->id == 1 || ($COURSE->id != 1 && count($qualFamilies)) > 0)
			{
                //do they have the ability to view by unit?
                //are the families that they can see in the view by families constant?
                if(count(array_intersect(explode('|',BCGT_UNIT_VIEW_FAMILIES), $qualFamilies)) > 0)
                {
                    $this->content->text .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=u&cID='.$COURSE->id.'&il=true">';   			
                    $this->content->text .= get_string('byUnit', 'block_bcgt').'</a></li>'; 
                }
                
                if(count(array_intersect(explode('|',BCGT_CLASS_VIEW_FAMILIES), $qualFamilies)) > 0)
                {
                    $this->content->text .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=c&cID='.$COURSE->id.'&il=true">';   			
                    $this->content->text .= get_string('byClassGroup', 'block_bcgt').'</a></li>';
                }

                if(get_config('bcgt','usefa') && 
                        count(array_intersect(explode('|',BCGT_FA_VIEW_FAMILIES), $qualFamilies)) > 0)
                {
                    $this->content->text .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=fa&cID='.$COURSE->id.'">';   			
                    $this->content->text .= get_string('byformalassessment', 'block_bcgt').'</a></li>';
                }
                
                if(count(array_intersect(explode('|',BCGT_ACTIVITYT_VIEW_FAMILIES), $qualFamilies)) > 0)
                {
                    $this->content->text .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=a&cID='.$COURSE->id.'">';   			
                    $this->content->text .= get_string('bygradebook', 'block_bcgt').'</a></li>';
                }
			}
                
			$this->content->text .= '</ul>';
        }
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        //>>BEDCOLL TODO this should be the user context
        if (has_capability('block/bcgt:viewowngrid', $currentContext)){
            if(does_user_have_tracking_sheets($USER->id))
            {
                $this->content->text .= '<ul class="list">';
                //TODO will check if the student actually has a grid!
                $this->content->text .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/my_grids.php?g=s&cID='.$COURSE->id.'">';   			
                $this->content->text .= get_string('mytrackers', 'block_bcgt').'</a></li>'; 
                $this->content->text .= '</ul>';
            }
        }
        if ($COURSE->id != 1 && has_capability('block/bcgt:addqualtocurentcourse', $currentContext)){
            $this->content->text .= '<ul class="list">';
            //TODO will check if the course has a qual already
            $count = bcgt_count_quals_course($COURSE->id);
            $this->content->text .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_course_qual.php?oCID='.$COURSE->id.'&cID='.$COURSE->id.'">';   			
            $this->content->text .= get_string('editcoursequals', 'block_bcgt').'</a> ['.$count.']</li>'; 
            $this->content->text .= '</ul>';
        }
        if($COURSE->id != 1 && has_capability('block/bcgt:manageactivitylinks', $currentContext))
        {
            $this->content->text .= '<ul class="list">';
            $this->content->text .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/activities.php?cID='.$COURSE->id.'">';   			
            $this->content->text .= get_string('viewactivitylinks', 'block_bcgt').'</a></li>'; 
            $this->content->text .= '</ul>';
        }
        $this->content->footer = '';
 
        return $this->content;
    }
    
    public function cron() 
    {
        global $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
        require_once($CFG->dirroot.'/blocks/bcgt/cron.php');
        mtrace( "Running cron for BCGT" );

        //are we running the cron for assignments?
        if(get_config('bcgt', 'useassignmentbtecautoupdate') && get_config('bcgt', 'assignmentfrequencycheck') != 'never')
        {
            mtrace(" Processing Assignment Linking checks");
            mtrace(" This will now check all assignments that are linked to qualifications for updates: Work Submmited, Late and Work Not Submmited");
            process_assignments_cron();
            
        }
        // do something

        return true;
    }
} 
?>
