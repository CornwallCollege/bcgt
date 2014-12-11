<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 * 
 */

global $CFG;

function run_bcgtcg_initial_import()
{
    //this will process the csv's in data and import the contents
    echo "todo...";
    
}


function require_cg(){
    global $CFG;
    
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php';
    
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGSubType.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGCriteria.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGUnit.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGQualification.class.php';
    
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGHBVRQCriteria.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGHBVRQUnit.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGHBVRQQualification.class.php';
    
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGHBNVQCriteria.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGHBNVQUnit.class.php';
    require_once $CFG->dirroot . '/blocks/bcgt/plugins/bcgtcg/classes/CGHBNVQQualification.class.php';
    
}

/**
     * this will output the view of the activity by unit page
     * this shows what units are on what activities
     * @global type $CFG
     * @param type $courseID
     * @return string
     */
    function cg_activity_by_unit_page($courseID)
    {
        global $CFG;
        $context = context_course::instance($courseID);
        $retval = '';
        //get all of the quals of this type that are on this course
        //get all of the units
        //output all of the units
        
        
        //Table
            //columns: Add Activity, Units, Activities
            //rows -> units, activities
        
        //get the quals
        $quals = bcgt_get_course_quals($courseID, CGQualification::FAMILYID);
        if(count($quals) == 1)
        {
            $retval .= '<h2>'.bcgt_get_qualification_display_name(end($quals)).'</h2>';
        }
        $modLinking = load_bcgt_mod_linking();
        //load the icons
        $modIcons = load_mod_icons($courseID);
        //there must be quals to get this far
        $units = bcgt_get_course_units($courseID, CGQualification::FAMILYID);
        if($units)
        {
            $retval .= '<div class="bcgtmodlinkingselections">';
            $manage = has_capability('block/bcgt:manageactivitylinks', $context);
            $link = $CFG->wwwroot.'/blocks/bcgt/forms/add_activity.php?page=addact&';
            $img = $CFG->wwwroot.'/blocks/bcgt/pix/greenPlus.png';
            $retval .= '<table class="activityLinks activityLinksMain" align="center">';
            $retval .= '<thead><th></th><th>'.get_string('unit', 'block_bcgt').
                    '</th><th>'.get_string('activities', 'block_bcgt').'</th></thead>'; 
            $retval .= '<body>';
            foreach($units AS $unit)
            {
                $retval .= '<tr>';
                $retval .= '<td>';
                if($manage)
                {
                    $retval .= '<a href="'.$link.'uID='.$unit->id.'&cID='.$courseID
                        .'&fID='.CGQualification::FAMILYID.'"><img src="'.$img.'"/></a>';
                }
                $retval .= '</td>';
                $retval .= '<td>'.$unit->name.'</td>';
                $activities = CGQualification::get_unit_activities($courseID, $unit->id);
                $retval .= '<td>';
                    $retval .= '<table class="activityLinksAssignmentGroup modlinkingsummary">';
                        
                    foreach($activities AS $activity)
                    {
                        $dueDate = get_bcgt_mod_due_date($activity->id, $activity->instanceid, 
                        $activity->cmodule, $modLinking);
                        $out = '<tr>';
                        $activityDetails = bcgt_get_activity_mod_details($activity->id);
                        $out .= '<td>';
                            //get the name
                            //get the criteria its on
                            //give it an option to be removed
                        $out .= '<table class="activityLinksActivities">';
                        $criterias = get_activity_criteria($activity->id, null, $unit->id);
                        //need to sort these. 
                        
                        $out .= '<tr>';
                        $out .= '<th>';
                        if(array_key_exists($activity->module,$modIcons))
                        {
                            $icon = $modIcons[$activity->module];
                            //show the icon. 
                            $out .= html_writer::empty_tag('img', array('src' => $icon,
                                        'class' => 'bcgtmodcriticon activityicon', 'alt' => $activity->module));
                        }
                        $out .= '</th>';
                        $out .= '<th colspan="'.(count($criterias) - 2).'">';
                        $out .= $activityDetails->name;
                        $out .= '</th>';
                        $out .= '<th>';
                        if($dueDate)
                        {
                            $out .= date('d M Y : H:m', $dueDate); 
                        }
                        $out .= '</th>';
                        $out .= '</tr>';
                        $out .= '<tr>';
                        foreach($criterias AS $criteria)
                        {
                            $out .= '<td>'.$criteria->name.'</td>';
                        }
                        $out .= '</tr>';
                        $out .= '</table>';
                        $out .= '</td>';
                        $out .= '</tr>';
                        $activity->out = $out;
                        $activity->dueDate = $dueDate;
                    }
                       
                        require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ModSorter.class.php');
                    $modSorter = new ModSorter();
                    usort($activities, array($modSorter, "ComparisonDelegateByDueDateObj"));

                    foreach($activities AS $activity)
                    {
                        $retval .= $activity->out;
                    }
                    $retval .= '</table>';
                $retval .= '</td>';
                $retval .= '</tr>';
            }
            $retval .= '</body>';
            $retval .= '</table>';
            $retval .= '</div>';
        }
        return $retval;
    }
    
    
    
    function get_cg_unit_activity_table($activityID, $unit, $courseID, 
            $new = false, $activity = null, $modLinking = null, $modIcons = null)
    {
        if(!$modLinking)
        {
            $modLinking = load_bcgt_mod_linking();
        }
        if(!$modIcons)
        {
            $modIcons = load_mod_icons($courseID, -1, -1, -1);
        }
        global $CFG;
        $retval = '';
        if(!$activity)
        {
            $activity = bcgt_get_activity_mod_details($activityID);
        }
        if($activity)
        {
            if(!$new)
            {
                //then get the criteria details for this unit/activity
                $qualsOnActivity = get_activity_quals($activityID, $unit->get_id());
                $criteriaOnActivity = get_activity_criteria($activityID, $qualsOnActivity);
            }    
            if(!isset($activity->dueDate))
            {
                $dueDate = get_bcgt_mod_due_date($activity->id, $activity->instanceid, $activity->cmodule, $modLinking);
            }
            else
            {
                $dueDate = $activity->dueDate;
            }
            $retval .= '<h3>';
            $retval .= '<span class="activityIcon">';
            if(array_key_exists($activity->module,$modIcons))
            {
                $icon = $modIcons[$activity->module];
                //show the icon. 
                $retval .= html_writer::empty_tag('img', array('src' => $icon,
                            'class' => 'bcgtmodcriticon activityicon', 'alt' => $activity->module));
            }
            $retval .= '</span>';
            $retval .= '<span class="activityName">'.$activity->name.'</span>';
            $retval .= '<span class="activityDueDate">';
            if($dueDate)
            {
                $retval .= date('d M Y : H:m', $dueDate); 
            }
            $retval .= '</span>';
            $retval .= '</h3>';
            if($unit)
            {
                $qualsUnitOn = $unit->get_quals_on('', -1, -1, $courseID );
                //we also need a selection for each qual. 
                if($qualsUnitOn)
                {
                    foreach($qualsUnitOn AS $qual)
                    {
                        $checked = 'checked="checked"';
                        if($new && $activityID != -1 && !isset($_POST['q_'.
                            $qual->id.'_a_'.$activityID.'']) && count($qualsUnitOn) != 1)
                        {
                            $checked = '';
                        }
                        elseif(!$new && !array_key_exists($qual->id, $qualsOnActivity))
                        {
                            $checked = '';
                        }
                        $retval .= '<label>'.  bcgt_get_qualification_display_name($qual).
                                ' : </label><input '.$checked.' type="checkbox" name="q_'.$qual->id.'_a_'.
                                $activityID.'"/>';
                    }
                }
                $criterias = $unit->get_criteria();
                require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
                $criteriaSorter = new CriteriaSorter();
                usort($criterias, array($criteriaSorter, "ComparisonDelegateByObjectName")); 

                if($criterias)
                {
                    $retval .= '<table>';
                    $retval .= '<tr>';
                    foreach($criterias AS $criteria)
                    {
                        $retval .= '<th>'.$criteria->get_name().'</th>';
                    }
                    $retval .= '</tr>';
                    $retval .= '<tr>';
                    foreach($criterias AS $criteria)
                    {
                        $checked = '';
                        if($new && isset($_POST['a_'.$activityID.'_c_'.$criteria->get_id().'']))
                        {
                            $checked = 'checked="checked"';
                        }
                        elseif(!$new && array_key_exists($criteria->get_id(), $criteriaOnActivity)) {
                            $checked = 'checked="checked"';
                        }
                        $retval .= '<td><input '.$checked.' type="checkbox" name="a_'.$activityID.'_c_'.$criteria->get_id().'"/></td>';
                    }
                    $retval .= '<td class="deleteselection">'.get_string('delete', 'block_bcgt').' : <input type="checkbox" name="rem_'.$activityID.'"/></td>';
                    $retval .= '</tr>';
                    $retval .= '</table>';
                }
            }
        }
        return $retval;
    }
    
    /**
     * 
     * @global type $CFG
     * @param type $activityID
     * @param type $unitID
     * @param type $courseID
     * @param type $new
     * @param type $modDirect
     * @return string
     */
    function get_cg_activity_unit_table($activityID, $unitID, $courseID, $new = false, $modDirect = false)
    {
        global $CFG;
        $retval = '';
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELCRITERIA;
        $unit = Unit::get_unit_class_id($unitID, $loadParams);
        if($unit)
        {
            $retval .= '<div class="bcgtunitmod" id="bcgtunitMod_'.$unit->get_id().'">';
            $retval .= '<h3>'.$unit->get_name().'</h3>';
            if(!$new)
            {
                //then get the criteria details for this unit/activity
                $qualsOnActivity = get_activity_quals($activityID, $unitID);
                $criteriaOnActivity = get_activity_criteria($activityID, $qualsOnActivity);
            }
            $qualsUnitOn = $unit->get_quals_on('', -1, -1, $courseID );
            //we also need a selection for each qual. 
            if($qualsUnitOn)
            {
                foreach($qualsUnitOn AS $qual)
                {
                    $checked = 'checked="checked"';
                    if($new && $activityID != -1 && !isset($_POST['q_'.
                        $qual->id.'_u_'.$unitID.'']) && count($qualsUnitOn) != 1)
                    {
                        $checked = '';
                    }
                    elseif(!$new && !array_key_exists($qual->id, $qualsOnActivity))
                    {
                        $checked = '';
                    }
                    $retval .= '<label>'.  bcgt_get_qualification_display_name($qual).
                            ' : </label><input '.$checked.' type="checkbox" name="q_'.$qual->id.'_u_'.
                            $unitID.'"/>';
                }
            }
            $criterias = $unit->get_criteria();
            require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtcg/classes/CGCriteriaSorter.class.php');
            $criteriaSorter = new CGCriteriaSorter();
            usort($criterias, array($criteriaSorter, "Comparison")); 

            if($criterias)
            {
                $retval .= '<table>';
                $retval .= '<tr>';
                foreach($criterias AS $criteria)
                {
                    $retval .= '<th>'.$criteria->get_name().'</th>';
                }
                $retval .= '</tr>';
                $retval .= '<tr>';
                foreach($criterias AS $criteria)
                {
                    $checked = '';
                    if($new && isset($_POST['u_'.$unitID.'_c_'.$criteria->get_id().'']))
                    {
                        $checked = 'checked="checked"';
                    }
                    elseif(!$new && array_key_exists($criteria->get_id(), $criteriaOnActivity))
                    {
                        $checked = 'checked="checked"';
                    }                    
                    $retval .= '<td><input '.$checked.' type="checkbox" name="u_'.$unitID.'_c_'.$criteria->get_id().'"/></td>';
                }
                if($modDirect)
                {
                    $retval .= '<td><input type="submit" name="remU_'.$unitID.
                            '" class="remUnit" unit="'.$unitID.'" value="'.
                            get_string('delete', 'block_bcgt').'"/></td>';
                }
                else
                {
                    $retval .= '<td>'.get_string('delete', 'block_bcgt').' : <input type="checkbox" name="remU_'.$unitID.'"/></td>';
                }
                $retval .= '</tr>';
                $retval .= '</table>';
            }
            $retval .= '</div>';
        }
        return $retval;
    }
    
    /**
     * This shows what activities have units etc
     * @global type $CFG
     * @param type $courseID
     * @return string
     */
    function cg_activity_by_activity_page($courseID)
    {
        //get all of the activities on this course
        //load all of their units and criteria etc. 
        //table
            //columns: Add, Activity, Units/Criterias
            //rows -> activities, units/criterias
        global $CFG;
        $context = context_course::instance($courseID);
        $retval = '';
        $quals = bcgt_get_course_quals($courseID, CGQualification::FAMILYID);
        if(count($quals) == 1)
        {
            $retval .= '<h2>'.bcgt_get_qualification_display_name(end($quals)).'</h2>';
        }
        $activities = bcgt_get_coursemodules_in_course($courseID);
        $modLinking = load_bcgt_mod_linking();
        //load the icons
        $modIcons = load_mod_icons($courseID);
        if($activities)
        {
            $manage = has_capability('block/bcgt:manageactivitylinks', $context);
            $link = $CFG->wwwroot.'/blocks/bcgt/forms/add_activity.php?page=addunit&';
            $img = $CFG->wwwroot.'/blocks/bcgt/pix/greenPlus.png';
            $retval .= '<div class="bcgtmodlinkingselections">';
            $retval .= '<table class="activityLinks">';
            $retval .= '<thead><th></th><th></th><th>'.get_string('activity', 'block_bcgt').
                    '</th><th>'.get_string('duedate','block_bcgt').'</th><th>'.
                    get_string('units', 'block_bcgt').'</th></thead>'; 
            $retval .= '<body>';
            foreach($activities AS $activity)
            {
                $dueDate = get_bcgt_mod_due_date($activity->id, $activity->instanceid, 
                        $activity->cmodule, $modLinking);
                $out = '<tr>';
                $out .= '<td>';
                if($manage)
                {
                    $out .= '<a href="'.$link.'aID='.$activity->id.'&cID='.$courseID
                        .'&fID='.CGQualification::FAMILYID.'"><img src="'.$img.'"/></a>';
                }
                $out .= '</td>';
                //the icon
                $out .= '<td>';
                if(array_key_exists($activity->module,$modIcons))
                {
                    $icon = $modIcons[$activity->module];
                    //show the icon. 
                    $out .= html_writer::empty_tag('img', array('src' => $icon,
                                'class' => 'bcgtmodcriticon activityicon', 'alt' => $activity->module));
                }
                $out .= '</td>';
                $out .= '<td>'.$activity->name.' ('.$activity->module.')</td>';
                //due date
                $out .= '<td>';
                if($dueDate)
                {
                    $out .= date('d M Y : H:m', $dueDate); 
                }
                $out .= '</td>';
                //now get the units that are on it. 
                $out .= '<td class="bcgtmodlinkingunitsummary">';
                $out .= get_mod_unit_summary_table($activity->id, CGQualification::FAMILYID);
                $out .= '</td>';
                $activity->out = $out;
                $activity->dueDate = $dueDate;
            }
            
            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ModSorter.class.php');
                $modSorter = new ModSorter();
                usort($activities, array($modSorter, "ComparisonDelegateByDueDateObj"));
            
            foreach($activities AS $activity)
            {
                $retval .= $activity->out;
            }
            $retval .= '</body>';
            $retval .= '</table>';
            $retval .= '</div>';
        }
        return $retval;
    }

   
    
    /**
     * This searches for any units that are attached to the mod
     * that arent selected now and deletes their selections. 
     * @global type $DB
     * @param type $courseModuleID
     * @param type $units
     * @return boolean
     */
    function bcgt_cg_remove_mod_unit_selection($courseModuleID, $units = array())
    {
        //this wants to find all unit selections that were on this courseModuleID
        //and remove them. 
        if(!$units || count($units) < 1)
        {
            return false;
        }
        global $DB;
        
        $sql = "SELECT refs.*, t.bcgttypefamilyid
                FROM {block_bcgt_activity_refs} refs 
                INNER JOIN {block_bcgt_qualification} q ON q.id = refs.bcgtqualificationid
                INNER JOIN {block_bcgt_target_qual} tq ON tq.id = q.bcgttargetqualid
                INNER JOIN {block_bcgt_type} t ON t.id = tq.bcgttypeid
                WHERE refs.coursemoduleid = ? AND t.bcgttypefamilyid = ? AND refs.bcgtunitid NOT IN (";

        $params = array($courseModuleID, CGQualification::FAMILYID);
        $count = 0;
        foreach($units AS $unitID)
        {
            $count++;
            $sql .= "?";
            if($count != count($units))
            {
                $sql .= ',';
            }
            $params[] = $unitID;
        }
        $sql .= ')';
        $records = $DB->get_records_sql($sql, $params);
        if($records)
        {
            foreach($records AS $record)
            {
                $DB->delete_records('block_bcgt_activity_refs',array("id"=>$record->id));
            }
        }
    }
    
    /**
     * Tis will add all of the quals and criteria selected for this unit
     * into the system. 
     * @param type $courseModuleID
     * @param type $unitID
     * @param type $courseID
     */
    function bcgt_cg_process_mod_unit_selection($courseModuleID, $unitID, $courseID)
    {
                
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELCRITERIA;
        $unit = Unit::get_unit_class_id($unitID, $loadParams);
        $criterias = $unit->get_criteria();
        $criteriasUsed = array();
        //I really dont want to loop through all criteria for every qual possible
        foreach($criterias AS $criteria)
        {
           if(isset($_POST['u_'.$unitID.'_c_'.$criteria->get_id().'']))
           {
               //then we want to insert it
               $criteriasUsed[] = $criteria->get_id();
           }
        }
        $qualsUnitOn = $unit->get_quals_on('', -1, -1, $courseID );
        //is it on a qual?
        foreach($qualsUnitOn AS $qual)
        {
            if(isset($_POST['q_'.$qual->id.'_u_'.$unitID]))
            {
                //is on this qual so lets insert it. 
                //we need to get the criteriaIDs. We know the unitID
                $stdObj = new stdClass();
                $stdObj->coursemoduleid = $courseModuleID;
                $stdObj->bcgtunitid = $unitID;
                $stdObj->bcgtqualificationid = $qual->id;
                foreach($criteriasUsed AS $criteriaID)
                {
                    $stdObj->bcgtcriteriaid = $criteriaID;
                    insert_activity_onto_unit($stdObj);
                }
            }
        }
    }
    
    /**
     * This function checked the unitid passed in for any mod changes. 
     * Has the qualifications it is associated with changed?
     * Has the criteria selected changed?
     * @param type $courseModuleID
     * @param type $unitID
     * @param type $courseID
     */
    function bcgt_cg_process_mod_selection_changes($courseModuleID, $unitID, $courseID)
    {
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELCRITERIA;
        $unit = Unit::get_unit_class_id($unitID, $loadParams);
        $qualsUnitOn = $unit->get_quals_on('', -1, -1, $courseID);
        //now check quals. 
        foreach($qualsUnitOn AS $qual)
        {
            //is it checked?
            if(!isset($_POST['q_'.$qual->id.'_u_'.$unitID]))
            {
                unset($qualsUnitOn[$qual->id]);
                delete_activity_by_qual_from_unit($courseModuleID, $qual->id, $unitID);
            }
        }
        $criterias = $unit->get_criteria();
        foreach($criterias AS $criteria)
        {
            //was it checked before?
            $criteriaOnActivity = get_activity_criteria($courseModuleID, $qualsUnitOn);
            if(isset($_POST['u_'.$unitID.'_c_'.$criteria->get_id()])
                    && !array_key_exists($criteria->get_id(), $criteriaOnActivity))
            {
                //so its been checked and it wasnt in the array from the database
                //therefore INSERT!
                foreach($qualsUnitOn AS $qual)
                {
                    $stdObj = new stdClass();
                    $stdObj->coursemoduleid = $courseModuleID;
                    $stdObj->bcgtunitid = $unitID;
                    $stdObj->bcgtqualificationid = $qual->id;
                    $stdObj->bcgtcriteriaid = $criteria->get_id();
                    insert_activity_onto_unit($stdObj);
                }
            }
            elseif(!isset($_POST['u_'.$unitID.'_c_'.$criteria->get_id()])
                    && array_key_exists($criteria->get_id(), $criteriaOnActivity))
            {
                //its in the array from before and its no longer checked!
                //therefore delete
                delete_activity_by_criteria_from_unit($courseModuleID, $criteria->get_id(), $unitID);
            }
            //is it checked? 
        }
    }
    
    /**
     * This function will check the units that are being addd to the mod
     * It will check if its a new unit (and thus process all units and criteria)
     * If its a unit we already had it will check for any changes (quals selected, criteria ticked)
     * @param type $courseModuleID
     * @param type $unitID
     * @param type $courseID
     */
    function bcgt_cg_process_mod_units($courseModuleID, $unitID, $courseID)
    {
        $activityUnits = get_activity_units($courseModuleID, CGQualification::FAMILYID);
        //was this unit on it before?
        if(array_key_exists($unitID, $activityUnits))
        {
            //then we need to check it
            bcgt_cg_process_mod_selection_changes($courseModuleID, $unitID, $courseID);
        }
        else 
        {
            //we are just adding it all
            bcgt_cg_process_mod_unit_selection($courseModuleID, $unitID, $courseID);
        }
        
    }