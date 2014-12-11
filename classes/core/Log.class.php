<?php
/**
 * Description of Log
 *
 * @author cwarwicker
 */
class Log {
    
    
    public static function search($filter, $page=1, $order=array('log.ID' => 'DESC'))
    {
        
        global $CFG, $DB;
        
        // Build SQL
        $params = array();
        $sql = "";
        $sql .= "SELECT log.id, staff.id as staffid, staff.username as staffname, student.id as studentid, student.username as studentname, log.module, log.element, log.log, log.valueid, log.valueid2, log.valueid3, log.time, log.bcgtqualificationid, log.courseid, course.fullname ";
        $sql .= "FROM {block_bcgt_logs} log ";
        $sql .= "LEFT JOIN {user} staff ON log.userid = staff.id ";
        $sql .= "LEFT JOIN {user} student ON log.studentid = student.id ";
        $sql .= "LEFT JOIN {course} course ON log.courseid = course.id ";
        
        if(!empty($filter)) $sql .= "WHERE ";
        
        // Filter
        $numOfFilter = 0;
        
        foreach($filter as $where => $value)
        {
            
            $sql .= ($numOfFilter > 0) ? "AND " : "";
            
            switch($where)
            {
                
                case "staff":
                    $sql .= "( staff.username LIKE ? OR staff.lastname LIKE ? OR staff.firstname LIKE ? ) ";
                    $params[] = '%'.$value.'%';
                    $params[] = '%'.$value.'%';
                    $params[] = '%'.$value.'%';
                break;
                case "student":
                    $sql .= "( student.username LIKE ? OR student.lastname LIKE ? OR student.firstname LIKE ? ) ";
                    $params[] = '%'.$value.'%';
                    $params[] = '%'.$value.'%';
                    $params[] = '%'.$value.'%';
                break;
                case "allstudents": // This case is used in the unit grid view to get all students on a unit
                    $sql .= "( student.id IN (".implode(", ", $value).") ) ";
                break;
                case "allquals": // This case is used in the unit grid view to get all quals a unit is on
                    $sql .= "( log.bcgtqualificationid IN (".implode(", ", $value).") ) ";
                break;
                case "module":
                    $sql .= "( log.module = ? )";
                    $params[] = $value;
                break;
                case "qualID":
                    $sql .= " ( log.bcgtqualificationid = ? ) ";
                    $params[] = $value;
                break;
                case "unitID":
                    $sql .= " ( log.bcgtunitid = ? ) ";
                    $params[] = $value;
                break;
                case "course":
                    $sql .= " ( course.shortname LIKE ? OR course.fullname LIKE ? ) ";
                    $params[] = "'%".$value."%'";
                    $params[] = "'%".$value."%'";
                break;
                case "days":
                    $now = time();
                    $days = ((60 * 60) * 24) * $value;
                    $ago = $now - $days;
                    $sql .= " (log.time >= {$ago}) ";
                break;
                case "lastID":
                    $sql .= " ( log.id > ? ) ";
                    $params[] = $value;
                break;
            
            }
            
            $numOfFilter++;
            
        }
        
        // Order
        $sql .= "ORDER BY ";
        
        $countOrder = count($order);
        $numOfOrder = 1;
        
        foreach($order as $field => $direction)
        {
            
            $sql .= "{$field} {$direction} ";
            $sql .= ($numOfOrder < $countOrder) ? ", " : "";
            $numOfOrder++;
                        
        }
                
        // Before we limit it, do a count of the rows so we know how many pages we need
        $countRecords = $DB->get_records_sql($sql, $params);
        $countRecords = count($countRecords);
        $pagesRequired = ceil($countRecords / 100);       
                
        // Limit
//        $limit = 50;
//                                                   
//        $limitFrom = (--$page) * 50;
//        
        $return = array();
        $return['records'] = $DB->get_records_sql($sql, $params);
        $return['pages'] = $pagesRequired;
        
        return $return;
        
    }
    
    public static function form($filter)
    {
//        global $CFG, $access;
//        
//        $current['staff'] = (isset($filter['staff'])) ? $filter['staff'] : "";
//        $current['student'] = (isset($filter['student'])) ? $filter['student'] : "";
//        $current['module'] = (isset($filter['module'])) ? $filter['module'] : "";
//        $current['qual'] = (isset($filter['qualID'])) ? $filter['qualID'] : "";
//        $current['days'] = (isset($filter['days'])) ? $filter['days'] : "";
//
//        // Available modules
//        $modules = array();
//        $modules[] = LOG_MODULE_BKSB;
//        $modules[] = LOG_MODULE_GRADETRACKER;
//        $modules[] = LOG_MODULE_PLP;
//        
//        $moduleString = "<option value=''></option>";
//        foreach($modules as $module)
//        {
//            $selected = ($current['module'] == $module) ? "selected" : "";
//            $moduleString .= "<option value='{$module}' {$selected}>{$module}</option>";
//        }
//        
//        // Load available quals
//        // If admin, display all qualifications, otherwise , just the ones they teach on
//        if($access['god']){
//            $sql = "SELECT DISTINCT(qual.id) as id, type.type, level.level, subtype.subtype, qual.name 
//                    FROM {$CFG->prefix}ilp_course_qualification AS courseQual 
//                    JOIN {$CFG->prefix}tracking_qualification AS qual ON qual.id = courseQual.trackingqualificationid 
//                    JOIN {$CFG->prefix}ilp_target_qual AS targetQual ON targetQual.id = qual.targetqualid 
//                    JOIN {$CFG->prefix}tracking_level AS level ON level.id = targetQual.trackinglevelid 
//                    JOIN {$CFG->prefix}tracking_subtype AS subtype ON subtype.id = targetQual.trackingsubtypeid 
//                    JOIN {$CFG->prefix}tracking_type AS type ON type.id = targetQual.trackingtypeid
//                    ORDER BY type.type ASC, level.id ASC, subtype.id ASC, name ASC"; 
//            $quals = get_records_sql($sql);	
//        }
//        else
//        {
//            $quals = get_quals_under_teacher(true);	
//        }
//        
//        
//        
//        $qualString = "<option value=''></option>";
//        if($quals)
//        {
//            foreach($quals as $qual)
//            {
//                $selected = ($current['qual'] == $qual->id) ? "selected" : "" ;
//                $qualString .= "<option value='{$qual->id}' {$selected}>$qual->type $qual->level $qual->subtype $qual->name</option>";
//            }
//        }
//        
//        
//        
//        echo "<table style='width:100%;margin:auto;'>";
//            echo "<tr><th colspan='5'>Filter your search:</th></tr>";
//            echo "<tr>";
//                echo "<td>Staff member: <input type='text' name='staff' value='{$current['staff']}' /></td>";
//                echo "<td>Student: <input type='text' name='student' value='{$current['student']}' /></td>";
//                echo "<td>System Module: <select name='module' style='max-width:250px;'>{$moduleString}</select></td>";
//                echo "<td>Qualification: <select name='qual' style='max-width:250px;'>{$qualString}</select></td>";
//                echo "<td>Last <input type='text' style='width:20px;' value='{$current['days']}' name='days' /> days</td>";
//            echo "</tr>";
//            echo "<tr><td colspan='5' class='c'><br><input type='submit' name='submitsearch' value='Search Logs' /></td></tr>";
//        echo "</table>";
        
    }
    
    public static function display_pages($info)
    {
        
//        global $CFG, $page;
//        
//        // Strip page parameter from query string if it exists
//        $_SERVER['QUERY_STRING'] = preg_replace("/&page=\d/", "", $_SERVER['QUERY_STRING']);
//        echo "<p class='c'>";
//            for($i = 1; $i <= $info['pages']; $i++)
//            {
//                $linkCol = ($page == $i) ? "style='color:purple;'" : "" ;
//                echo "<a {$linkCol} href='{$CFG->wwwroot}/mod/qualification/logs.php?{$_SERVER['QUERY_STRING']}&page={$i}'>Page {$i}</a> &nbsp;&nbsp; ";
//            }
//        echo "</p>";
    }
    
    public static function display_results($filter, $page)
    {
        
        global $CFG;
        
        $info = Log::search($filter, $page);
                
        $results = $info['records'];
        
        // Display pages
        Log::display_pages($info);
                                
        
        echo "<table id='logResults'>";
        
            echo "<tr><th></th><th>Staff</th><th>Student</th><th>Log</th><th>Qual</th><th>Course</th><th>Value</th><th>Time</th></tr>";
        
            if($results)
            {
                
                $row = 0;
                
                foreach($results as $result)
                {
                    
                    $row++;
                    
                    // If the qualid > 0, get the lvl and subtype and all that shite
                    if($result->qualid > 0){
                        
                        $loadParams = new stdClass();
                        $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                        $qualification = Qualification::get_qualification_class_id($result->qualid, $loadParams);
                        if ($qualification){
                            $result->qualname = $qualification->get_display_name();
                        }
                        
                    }
                    
                    // Work out which table row class to use (white or grey)
                    $class = ( ($row % 2) == 0 ) ? "rowEven" : "rowOdd" ;
                    
                    // Work out what row number to display, taking pages into consideration
                    $rowDisp = ($page > 1) ? ( $row ) + (100 * ($page-1)) : ( $row );
                    
                    // Apply links to other elements if required
                    if ($result->staffname){
                        $result->staffname = "<a href='{$CFG->wwwroot}/user/view.php?id={$result->staffid}'>{$result->staffname}</a>";
                    } else {
                        $result->staffname = 'System';
                    }
                    $result->studentname = "<a href='{$CFG->wwwroot}/user/view.php?id={$result->studentid}'>{$result->studentname}</a>";
                    $result->course = (!is_null($result->fullname)) ? "<a href='{$CFG->wwwroot}/course/view.php?id={$result->courseid}'>{$result->fullname}</a>" : "";
                    $result->qualname = (isset($result->qualname)) ? "{$result->qualname}" : "" ;
                    
                    echo "<tr class='{$class}'>";
                        echo "<td>#{$rowDisp}</td>";
                        echo "<td>{$result->staffname}</td>";
                        echo "<td>{$result->studentname}</td>";
                        echo "<td>{$result->log}</td>";
                        echo "<td>{$result->qualname}</td>";
                        echo "<td>{$result->course}</td>";
                        echo "<td>".Log::value($result->module, $result->element, $result->log, $result->valueid, $result->valueid2, $result->valueid3)."</td>";
                        echo "<td>".date('d M Y H:i:s', $result->time)."</td>";
                    echo "</tr>";
                                                            
                }
                
            }
            else
            {
                echo "<tr><td colspan='8'>No results...</td></tr>";
            }
            
        echo "</table>";
        
        // Display pages
        //Log::display_pages($info);
        
        
    }
    
    /**
     * Work out what the value id is, e.g. a criteria, an award, etc... and display the relevant info
     * @global type $CFG
     * @param type $module
     * @param type $element
     * @param type $log
     * @param type $value 
     */
    public static function value($module, $element, $log, $value, $value2=null, $value3=null)
    {
        
        global $CFG, $DB;
        
        $qualroot = $CFG->wwwroot . '/block/bcgt/';
        $value2 = html($value2);
        $value3 = html($value3);
        
        // Switch the module
        switch($module)
        {
            
            case LOG_MODULE_GRADETRACKER:
                
                // Switch which gradetracker element it is
                switch($element)
                {
                
                    case LOG_ELEMENT_GRADETRACKER_CRITERIA:
                        
                        
                        // $value is always criteriaID
                        $criteria = $DB->get_record("block_bcgt_criteria", array("id" => $value));
                        if(!$criteria) return "?";
                                
                        
                        // Switch which log type it is
                        switch($log)
                        {
                        
                            case LOG_VALUE_GRADETRACKER_INSERTED_CRIT:
                            case LOG_VALUE_GRADETRACKER_UPDATED_CRIT:
                                
                                // ValueID will be criteria ID
                                // So we want the criteria name and the name of the unit it's on
                                $loadParams = new stdClass();
                                $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                                $unit = Unit::get_unit_class_id($criteria->bcgtunitid, $loadParams);
                                if(!$unit) return "Criterion: " . $criteria->name;
                                
                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->get_id()}'>" . $unit->get_display_name() . "</a><br>Criterion: " . $criteria->name;
         
                            break;
                            
                            case LOG_VALUE_GRADETRACKER_UPDATED_CRIT_AWARD:
                                
                                // valueID will be criteria ID & valueID2 will be award ID
                                
                                $award = $DB->get_record("block_bcgt_value", array("id" => $value2));
                                if(!$award && $value2 > -1) return "Criterion: " . $criteria->name;
                                if($value2 < 0) $award->value = "N/S";
                                
                                $loadParams = new stdClass();
                                $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                                $unit = Unit::get_unit_class_id($criteria->bcgtunitid, $loadParams);
                                
                                if(!$unit) return "Criterion: " . $criteria->name . "<br>Award: " . $award->value;
                                
                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->get_id()}'>" . $unit->get_display_name() . "</a><br>Criterion: " . $criteria->name . "<br>Award: " . $award->value;
                                
                            break;
                        
//                            case LOG_VALUE_GRADETRACKER_UPDATED_USER_DEFINED_VALUE:
//                                
//                                // valueID will be criteria ID & valueID2 will be the user defined value
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$criteria->trackingunitid}'");
//                                if(!isset($unit->id)) return $criteria->name;
//                                                                
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Criterion: " . $criteria->name . "<br>Value: " . $value2;
//                                
//                            break;
                            
                            case LOG_VALUE_GRADETRACKER_UPDATED_CRIT_COMMENT:
                                
                                // ValueID will be criteria ID
                                // So we want the criteria name and the name of the unit it's on
                                
                                $loadParams = new stdClass();
                                $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                                $unit = Unit::get_unit_class_id($criteria->bcgtunitid, $loadParams);
                                if(!$unit) return "Criterion: " . $criteria->name;
                                
                                // ValueID2 will be the text comment
                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->get_id()}'>" . $unit->get_display_name() . "</a><br>Criterion: " . $criteria->name . "<br>Comment: " . clean_text($value2, FORMAT_PLAIN);
                                
                            break;
                            
//                            case LOG_VALUE_GRADETRACKER_DELETED_CRIT_COMMENT:
//                                
//                                // ValueID will be criteria ID
//                                // So we want the criteria name and the name of the unit it's on
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$criteria->trackingunitid}'");
//                                if(!isset($unit->id)) return "Criterion: " . $criteria->name;
//                                
//                                // ValueID2 will be the text comment
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Criterion: " . $criteria->name;
//                                
//                            break;
                            
                            case LOG_VALUE_GRADETRACKER_UPDATED_CRIT_USER_TARGET_DATE:
                                
                                // valueID is a criteria
                                
                                $loadParams = new stdClass();
                                $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                                $unit = Unit::get_unit_class_id($criteria->bcgtunitid, $loadParams);
                                if(!$unit) return "Criterion: " . $criteria->name;
                                
                                // ValueID2 will be the date
                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->get_id()}'>" . $unit->get_display_name() . "</a><br>Criterion: " . $criteria->name . "<br>Target Date: " . $value2;
                                
                                
                            break;
                            
                            case LOG_VALUE_GRADETRACKER_UPDATED_CRIT_USER_AWARD_DATE:
                                
                                // value is criteriaID
                                // value2 is date (string)
                                
                                $loadParams = new stdClass();
                                $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                                $unit = Unit::get_unit_class_id($criteria->bcgtunitid, $loadParams);
                                if(!$unit) return "Criterion: " . $criteria->name;
                                
                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->get_id()}'>" . $unit->get_display_name() . "</a><br>Criterion: " . $criteria->name . "<br>Award Date: " . $value2;
         
                                
                            break;
                            
//                            case LOG_VALUE_GRADETRACKER_UPDATED_CRIT_AWARD_AUTO:
//                                
//                                // value is critid, value2 is award id
//                                
//                                $award = get_record_select("tracking_value", "`id` = '{$value2}'");
//                                if(!isset($award->id) && $value2 > -1) return "Criterion: " . $criteria->name;
//                                if($value2 < 0) $award->value = "N/S";
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$criteria->trackingunitid}'");
//                                if(!isset($unit->id)) return "Criterion: " . $criteria->name . "<br>Award: " . $award->value;
//                                
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Criterion: " . $criteria->name . "<br>Award: " . $award->value;
//                                
//                            break;
                            
//                            case LOG_VALUE_GRADETRACKER_UPDATED_OUTCOME_OBSERVATION:
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$criteria->trackingunitid}'");
//                                if(!isset($unit->id)) return "Outcome: " . $criteria->name . "<br>Observation: " . $value2 . "<br>Award Date: " . $value3;
//                                
//                                // ValueID2 will be the date
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Outcome: " . $criteria->name . "<br>Observation: " . $value2 . "<br>Award Date: " . date('d M Y', (int)$value3);
//                                
//                            break;
//                                                        
//                            case LOG_VALUE_GRADETRACKER_UPDATED_CRIT_ATTRIBUTE:
//                                
//                                // value is record in db
//                                $attribute = get_record_select("tracking_criteria_attributes", "`id` = '{$value}'");
//                                if(!$attribute) return "?";
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$attribute->unitid}'");
//                                if(!isset($unit->id)) return "Criterion: {$criteria->name}<br>Attribute: {$attribute->attribute}<br>Value: {$attribute->value}";
//                                
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Criterion: {$criteria->name}<br>Attribute: {$attribute->attribute}<br>Value: {$attribute->value}";
//                                
//                            break;
                                                   
                        }
                        
                        
                    break;
                
                    case LOG_ELEMENT_GRADETRACKER_QUALIFICATION:
                        
                        switch($log)
                        {
                        
                            case LOG_VALUE_GRADETRACKER_ADDED_UNIT_TO_QUAL:
                            case LOG_VALUE_GRADETRACKER_REMOVED_UNIT_FROM_QUAL:
                                
                                // ValueID is a unit ID
                                $loadParams = new stdClass();
                                $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                                $unit = Unit::get_unit_class_id($value, $loadParams);
                                if(!$unit) return "?";
                                                                
                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->get_id()}'>" . $unit->get_display_name() . "</a>";
                                
                            break;
                            
                            case LOG_VALUE_GRADETRACKER_UPDATED_QUAL_AWARD:
                                
                                // value ID is an award ID
                                $award = $DB->get_record("block_bcgt_target_breakdown", array("id" => $value));
                                if(!$award->id) return "Award: N/A";
                                return "Award: " . $award->targetgrade;
                                
                            break;
                            
                            case LOG_VALUE_GRADETRACKER_UPDATED_QUAL_COMMENTS:
                                
                                // ValueID will be null as expects an actual ID
                                // ValueID2 will be the comment text
                                
                                return "Comment: " . clean_text($value2, FORMAT_PLAIN);
                                
                            break;
                        
//                            case LOG_VALUE_GRADETRACKER_UPDATED_QUAL_ATTRIBUTE:
//                                
//                                // value is record in db
//                                $attribute = get_record_select("tracking_qualification_attributes", "`id` = '{$value}'");
//                                if(!$attribute) return "?";
//                                return "Attribute: {$attribute->attribute}<br>Value: " . $attribute->value;
//                                
//                            break;
                        
                        
                        }
                        
                    break;
                
                    case LOG_ELEMENT_GRADETRACKER_SETTINGS:
                        
                        
                        
                    break;
                
//                    case LOG_ELEMENT_GRADETRACKER_TASK:
//                        
//                        // Switch the log type
//                        switch($log)
//                        {
//                        
//                            case LOG_VALUE_GRADETRACKER_UPDATED_TASK_AWARD:
//                            case LOG_VALUE_GRADETRACKER_INSERTED_TASK_AWARD:
//                                
//                                // valueID will be criteria ID , valueid2 will be taskID & valueID3 will be award ID
//                                
//                                $criteria = get_record_select("tracking_criteria", "`id` = '{$value}'");
//                                if(!isset($criteria->id)) return "?";
//                                
//                                $task = get_record_select("tracking_task", "`id` = '{$value2}'");
//                                if(!isset($task->id)) return "Criterion: " . $criteria->name;
//                                
//                                $award = get_record_select("tracking_value", "`id` = '{$value3}'");
//                                if(!isset($award->id)) return "Criterion: " . $criteria->name . "<br>Task: " . $task->name;
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$criteria->trackingunitid}'");
//                                if(!isset($unit->id)) return "Criterion: " . $criteria->name . "<br>Task: " . $task->name . "<br>Award: " . $award->value;
//                                
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Criterion: " . $criteria->name . "<br>Task: " . $task->name . "<br>Award: " . $award->value;
//                                
//                            break;
//                            
//                            case LOG_VALUE_GRADETRACKER_INSERTED_TASK:
//                                
//                                // valueid is task ID
//                                $task = get_record_select("tracking_task", "`id` = '{$value}'");
//                                if(!isset($task->id)) return "?";
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$task->trackingunitid}'");
//                                if(!isset($unit->id)) return "Task: " . $task->name;
//                                
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Task: " . $task->name;                                
//                            break;
//                            
//                            case LOG_VALUE_GRADETRACKER_UPDATED_TASK_COMMENT:
//                                
//                                // valueid is the id of the user criteria task record
//                                $record = get_record_select("tracking_user_criteria_task", "`id` = '{$value}'");
//                                if(!isset($record->id)) return "?";
//                                
//                                $criteria = get_record_select("tracking_criteria", "`id` = '{$record->criteriaid}'");
//                                if(!isset($criteria->id)) return "?";
//                                
//                                $task = get_record_select("tracking_task", "`id` = '{$record->taskid}'");
//                                if(!isset($task->id)) return "?";
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$criteria->trackingunitid}'");
//                                if(!isset($unit->id)) return "Criterion: " . $criteria->name . "<br>Task: " . $task->name . "<br>Comment: " . $value2;
//                                
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Criterion: " . $criteria->name . "<br>Task: " . $task->name . "<br>Comment: " . $value2;
//                                
//                                
//                            break;
//                            
//                            case LOG_VALUE_GRADETRACKER_DELETED_TASK_COMMENT:
//                                
//                                // valueid is the id of the user criteria task record
//                                $record = get_record_select("tracking_user_criteria_task", "`id` = '{$value}'");
//                                if(!isset($record->id)) return "?";
//                                
//                                $criteria = get_record_select("tracking_criteria", "`id` = '{$record->criteriaid}'");
//                                if(!isset($criteria->id)) return "?";
//                                
//                                $task = get_record_select("tracking_task", "`id` = '{$record->taskid}'");
//                                if(!isset($task->id)) return "?";
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$criteria->trackingunitid}'");
//                                if(!isset($unit->id)) return "Criterion: " . $criteria->name . "<br>Task: " . $task->name . "<br>Comment: " . $value2;
//                                
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Criterion: " . $criteria->name . "<br>Task: " . $task->name;
//                                
//                                
//                            break;
//                        
//                        }
//                        
//                    break;
                
                    case LOG_ELEMENT_GRADETRACKER_UNIT:
                        
                        // Switch log type
                        switch($log)
                        {
                        
                            case LOG_VALUE_GRADETRACKER_INSERTED_UNIT:
                            case LOG_VALUE_GRADETRACKER_UPDATED_UNIT:
                                
                                // valueID will be unit ID
                                $loadParams = new stdClass();
                                $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                                $unit = Unit::get_unit_class_id($value, $loadParams);
                                if(!$unit) return "?";
                                
                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$value}'>" . $unit->get_display_name() . "</a>";
                                
                            break;
                            
                            case LOG_VALUE_GRADETRACKER_UPDATED_UNIT_AWARD:
                                
                                // valueID is going to be unitid and valueid2 is going to be award id
                                $loadParams = new stdClass();
                                $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                                $unit = Unit::get_unit_class_id($value, $loadParams);
                                if(!$unit) return "?";
                                
                                $award = $DB->get_record("block_bcgt_type_award", array("id" => $value2));
                                if(!$award) return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->get_id()}'>" . $unit->get_display_name() . "</a>";
                                
                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$value}'>" . $unit->get_display_name() . "</a><br>Award: " . $award->award;
                                
                            break;
                            
                            case LOG_VALUE_GRADETRACKER_UPDATED_UNIT_COMMENT:
                                
                                // valueID is going to be unitid and valueid2 is going to be award id
                                $loadParams = new stdClass();
                                $loadParams->loadLevel = Qualification::LOADLEVELMIN;
                                $unit = Unit::get_unit_class_id($value, $loadParams);
                                if(!$unit) return "?";
                                
                                // valueID2 is the comment
                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$value}'>" . $unit->get_display_name() . "</a><br>Comment: " . clean_text($value2, FORMAT_PLAIN);
                                
                            break;
                            
//                            case LOG_VALUE_GRADETRACKER_UPDATED_UNIT_ATTRIBUTE:
//                                
//                                 // value is record in db
//                                 $attribute = get_record_select("tracking_unit_attributes", "`id` = '{$value}'");
//                                 if(!$attribute) return "?";
//                                 
//                                 $unit = get_record_select("tracking_units", "`id` = '{$attribute->unitid}'");
//                                 if(!isset($unit->id)) return "Attribute: {$attribute->attribute}<br>Value: {$attribute->value}";
//                                
//                                 return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Attribute: {$attribute->attribute}<br>Value: {$attribute->value}";
//                                
//                                
//                            break;
                            
//                            case LOG_VALUE_GRADETRACKER_UPDATED_SIGNOFF_RANGE_OBSERVATION:
//                                                                
//                                // value will be sheet, value2 range, value3 observationnum
//                                $sheet = get_record_select("tracking_signoff_sheets", "`id` = '{$value}'");
//                                if(!$sheet) return "???";
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$sheet->unitid}'");
//                                if(!isset($unit->id)) return "Sheet: " . $sheet->name;
//                                
//                                $range = get_record_select("tracking_signoff_sheet_ranges", "`id` = '{$value2}'");
//                                if(!$range) return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Sheet: {$sheet->name}";
//                                
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Sheet: " . $sheet->name . "<br>Range: " . $range->name . "<br>Observation: " . $value3;
//                                
//                            break;
                            
                            
                        
                        }
                        
                    break;
                    
//                    case LOG_ELEMENT_GRADETRACKER_RANGE:
//                        
//                        switch($log)
//                        {
//                        
//                            case LOG_VALUE_GRADETRACKER_UPDATED_CRITERIA_RANGE_VALUE:
//                                
//                                // value is the value given, value2 is the rangeid, value3 is the critid
//                                $range = get_record_select("tracking_range", "`id` = '{$value2}'");
//                                if(!isset($range->id)) return "?";
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$range->unitid}'");
//                                if(!isset($unit->id)) return "?";
//                                
//                                $criteria = get_record_select("tracking_criteria", "`id` = '{$value3}'");
//                                if(!isset($criteria->id)) return "?";
//                                
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Range: " . $range->name . "<br>Criteria: " . $criteria->name . "<br>Points: " . $value;
//                                
//                                
//                            break;
//                            
//                            case LOG_VALUE_GRADETRACKER_UPDATED_RANGE_AWARD_DATE:
//                                
//                                $range = get_record_select("tracking_range", "`id` = '{$value}'");
//                                if(!isset($range->id)) return "?";
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$range->unitid}'");
//                                if(!isset($unit->id)) return "?";
//                                
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Range: " . $range->name . "<br>Date: " . $value2;
//                                
//                            break;
//                            
//                            case LOG_VALUE_GRADETRACKER_UPDATED_RANGE_TARGET_DATE:
//                                
//                                $range = get_record_select("tracking_range", "`id` = '{$value}'");
//                                if(!isset($range->id)) return "?";
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$range->unitid}'");
//                                if(!isset($unit->id)) return "?";
//                                
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Range: " . $range->name . "<br>Target Date: " . $value2;
//                                
//                            break;
//                            
//                            case LOG_VALUE_GRADETRACKER_UPDATED_RANGE_AWARD:
//                            case LOG_VALUE_GRADETRACKER_UPDATED_RANGE_AWARD_AUTO:
//                                
//                                $range = get_record_select("tracking_range", "`id` = '{$value}'");
//                                if(!isset($range->id)) return "?";
//                                
//                                $unit = get_record_select("tracking_units", "`id` = '{$range->unitid}'");
//                                if(!isset($unit->id)) return "?";
//                                
//                                if($value2 <= 0) return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Range: " . $range->name . "<br>Award: N/A";
//                                
//                                $award = get_record_select("tracking_value", "`id` = '{$value2}'");
//                                if(!isset($award->id)) return "?";
//                                
//                                return "Unit: <a href='{$qualroot}edit_unit_form.php?unitID={$unit->id}'>" . $unit->name . "</a><br>Range: " . $range->name . "<br>Award: " . $award->value;
//                                
//                            break;
//                          
//                                
//                            
//                        
//                        }
//                        
//                    break; // End RANGE
                
                }
                
                
            break;
            
        }
        
        
    }
    
    /**
     * Get the logs to go at the bottom of a grid
     * @global type $CFG
     * @param array $params Array of parameters, such as ['qualID'], ['studentID'], ['element'], etc... 
     */
    public static function get_grid_xml($params)
    {
                      
        global $DB;
        
        $records = Log::search($params);
        $records = $records['records'];
                                
        $output = "";
        if(!$records) return $output;
        
        $output .= "<logs>";
        
        $row = 1;
        
        foreach($records as $record)
        {
            
            $staff = $DB->get_record("user", array("id" => $record->staffid));
            $student = $DB->get_record("user", array("id" => $record->studentid));
            
            // Work out which table row class to use (white or grey)
            $class = ( ( (++$row) % 2) == 0 ) ? "rowEven" : "rowOdd" ;
            $output .= "<log>";
            $output .= "<id>{$record->id}</id>";
            $output .= "<class>{$class}</class>";
            $output .= "<time>".date('d M Y H:i:s', $record->time) . "</time>";
            $output .= "<staff>".fullname($staff)."</staff>";
            $output .= "<student>".fullname($student)."</student>";
            $output .= "<text>{$record->log}</text>";
            
            $value = Log::value($record->module, $record->element, $record->log, $record->valueid, $record->valueid2, $record->valueid3);
            $value = preg_replace("/\<br\>/", " | ", $value);
            $value = strip_tags($value);
            
            $output .= "<value><![CDATA[{$value}]]></value>";
            $output .= "</log>";
            
            // Set the latest ID in a session
            if (!isset($_SESSION['lastLogID'])) $_SESSION['lastLogID'] = array();
            if (!isset($_SESSION['lastLogID'][$params['qualID']])) $_SESSION['lastLogID'][$params['qualID']] = $record->id;
            if ($record->id > $_SESSION['lastLogID'][$params['qualID']]) $_SESSION['lastLogID'][$params['qualID']] = $record->id;
            
        }
        
        $output .= "</logs>";
                                
        return $output;
        
    }
    
    public static function parse_logs_xml($xml)
    {
        
        
        $output = "<table style='width:100%;text-align:center;'>";
        $output .= "<thead>";
        $output .= "<tr><th>Time</th><th>Student</th><th>Staff</th><th>Action</th><th>Value</th></tr>";
        $output .= "</thead>";
        $output .= "<tbody id='gridLogsBody'>";
        
        if(!$xml) return $output . "</tbody></table>";
        
        try
        {
            $xml = new SimpleXMLElement($xml);
        }
        catch(Exception $e)
        {
            echo "Error: $e<br>";
            echo "XML: " . $xml;
            exit;
        }
        
        // Set JS variable "lastLog" to last ID
        $output .= "<script>lastLog = {$xml->log->id};</script>";
        
                
        foreach($xml as $x)
        {
                        
            $x->value = preg_replace("/ \| /", "<br>", $x->value);
            $output .= "<tr class='{$x->class}' id='LOG_{$x->id}'><td>{$x->time}</td><td>{$x->student}</td><td>{$x->staff}</td><td>{$x->text}</td><td>{$x->value}</td></tr>";
                        
        }
        
        $output .= "</tbody>";
        $output .= "</table>";
        
        
        return $output;
        
    }
    
    public static function apply_links()
    {
        echo '<script>$("#logResults a").each(function(){$(this).attr(\'target\', \'_blank\');});</script>';
    }
    
    
    public static function display_eval($qualID, $studentID = null, $unitID = null)
    {
        
        // Get the logs for this qual & student
        $user = get_record_select("user", "`id` = '{$studentID}'");
        $params['qualID'] = $qualID;
        if(!is_null($studentID)) $params['student'] = $user->username;
        if(!is_null($unitID)) $params['unitID'] = $unitID;
        
        $params['lastID'] = (isset($_SESSION['lastLogID'][$qualID])) ? $_SESSION['lastLogID'][$qualID] : 0;
        
        $logXml = Log::get_grid_xml($params);
        
        // Loop through the logs and see if we already have that log displayed on the page
        $listOfLogs = new SimpleXMLIterator($logXml);
        if(!$listOfLogs) exit;

        echo 'var str = "";';

        foreach($listOfLogs as $log)
        {

            $log->value = htmlspecialchars($log->value, ENT_QUOTES);
            // If the log isn't already displayed on the screen, add it
            echo <<<JS
            if( $('#LOG_{$log->id}').length < 1 )
            {
                str += "<log><id>{$log->id}</id><time>{$log->time}</time><student>{$log->student}</student><staff>{$log->staff}</staff><text>{$log->text}</text><value>{$log->value}</value></log>";
            }
JS;
                
        }

        echo 'addLog($("<logs>"+str+"</logs>"));';
        
        // If any error display in logs
        if(!empty($GLOBALS['AJAX_ERROR'])){
            $GLOBALS['AJAX_ERROR'] = str_replace("\n", "", $GLOBALS['AJAX_ERROR']);
            $GLOBALS['AJAX_ERROR'] = addslashes($GLOBALS['AJAX_ERROR']);
            echo "addError('".$GLOBALS['AJAX_ERROR']."');";
        }
                
    }
    
    
}