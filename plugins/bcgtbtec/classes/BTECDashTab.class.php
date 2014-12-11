<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
require_once('../../../../../config.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/DashTab.class.php');
class BTECDashTab extends DashTab{
    //put your code here
    const BTECDashTab = 'BTEC';
    
    public static function bcgt_get_plugin_tabs($tabFocus)
    {
        $courseID = optional_param('cID', SITEID, PARAM_INT);
        global $CFG;
        $class='last';
        return'<li class="'.$class.'">'.
        '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php?tab=BTEC&cID='.$courseID.'>'.
        '<span>BTEC</span></a></li>';
    }
    
    public static function bcgt_display_dashboard_tab_view($tabName)
    {
        $retval = '';
        $retval .= '<h2 class="dashContentHeading">BTEC</h2>';
        return $retval;
    }
    
    public static function bcgt_get_title($tab)
    {
        if($tab == BTECDashTab::BTECDashTab)
        {
            return BTECDashTab::BTECDashTab;
        }
        return false;
    }
}

?>
