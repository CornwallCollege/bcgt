<?php
/**
 * Description of AsssesmentTracker
 *
 * @author Sammy Guergachi <sguergachi at gmail.com> - Who the hell is this?
 */
class AssessmentTracker {
    
    const DEFAULT_MOD_LINKS = 'crit';
    const DEFAULT_MOD_TYPES = 'assign,assignment,turnitintool,quiz';
    
    private $student = false;
    private $year;
    private $courses = array();
    private $course;
    private $qual;
    private $modLinks;
    private $modTypes;
    private $viewType = 'calendar';
    private $showValues = false;
    
    public function __construct() {
        
        $this->year = date('Y');
        
    }
    
    
    public function loadStudent($studentID){
        
        global $DB;
        
        $student = $DB->get_record("user", array("id" => $studentID));
        if ($student)
        {
            $this->student = $student;
            return true;
        }
        else
        {
            return false;
        }
        
        
    }
    
    public function getStudentID(){
        return ($this->student) ? $this->student->id : false;
    }
    
    public function setYear($year){
        $this->year = $year;
    }
    
    public function setCourses($courses){
        $this->courses = $courses;
    }
    
    public function setCourse($course){
        $this->course = $course;
    }
    
    public function setQual($qual){
        $this->qual = $qual;
    }
    
    public function setModuleLinks($modLinks){
        $this->modLinks = $modLinks;
    }
    
    public function setModuleTypes($types){
        $this->modTypes = $types;
    }
    
    public function setShowValues($val){
        $this->showValues = $val;
    }
    
    public function setViewType($type){
        $this->viewType = $type;
    }
    
    public function getColour($id){
        
        $array = array();
        
        $array[1] = array('bg' => 'A2F7B3', 'font' => '000000');
        $array[2] = array('bg' => 'F4F7A2', 'font' => '000000');
        $array[3] = array('bg' => '00BFFF', 'font' => 'FFFFFF');
        $array[4] = array('bg' => 'EFC8FF', 'font' => '000000');
        $array[5] = array('bg' => 'FF7F50', 'font' => '000000');
        $array[6] = array('bg' => 'FFE000', 'font' => '000000');
        $array[7] = array('bg' => 'EBEBEB', 'font' => '000000');
        $array[8] = array('bg' => 'FF1493', 'font' => 'FFFFFF');
        $array[9] = array('bg' => 'DC143C', 'font' => 'FFFFFF');
        $array[10] = array('bg' => '0000CD', 'font' => 'FFFFFF');
        $array[11] = array('bg' => '3CB371', 'font' => 'FFFFFF');
        $array[12] = array('bg' => '7FFFD4', 'font' => '000000');
        
        $default = array('bg' => '000000', 'font' => 'FFFFFF');
                
        return (isset($array[$id])) ? $array[$id] : $default;
        
    }
    
    public function getModPluginName($mod){
        return get_string('pluginname', 'mod_'.$mod);
    }
    
    
    
    /**
     * 
     * @global type $CFG
     * @return string|boolean
     */
    public function getStudentTracker(){
        
        global $CFG;
        
        if (!$this->student){
            return false;
        }
        
        
        // If we only want ones linked to criteria, filter out others
        if ($this->modLinks == 'crit'){
            
            $newCourseArray = array();
            
            if ($this->courses)
            {
                foreach($this->courses as $course)
                {
                    
                    if (bcgt_course_has_criteria_module_links($course->id))
                    {
                        $newCourseArray[] = $course;
                    }
                    
                }
            }
                        
            $this->courses = $newCourseArray;
            
            
        }
        
                
        $output = "";
                        
            // Colour key
            $output .= "<fieldset><legend>".get_string('coursecolourkey', 'block_bcgt')."</legend>";

                $output .= "<div class='c'>";

                    if ($this->courses)
                    {
                        $i = 1;
                        foreach($this->courses as $course)
                        {
                            $col = $this->getColour($i);
                            $output .= "<div style='height:15px;padding:4px;margin-bottom:20px;font-size:8pt;background-color:#{$col['bg']};color:#{$col['font']};float:left;margin-left:25px;'>{$course->fullname}</div>";
                            $i++;
                        }
                    }

                $output .= "<br style='clear:both;' />";
                $output .= "</div>";

            $output .= "</fieldset><br>";



            $output .= "<p class='c' id='loading2'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/ajax-loader.gif' /></p>";

            $output .= "<div id='assessment_tracker_div'>";

                $output .= "<table id='assessment_tracker_table'>";
                    $output .= "<thead>";
                    $output .= "<tr>";
                    $output .= "<th class='daynum'></th>";

                    for ($month = 1; $month <= 12; $month++)
                    {
                        $strtime = "01-{$month}-{$this->year}";
                        $output .= "<th class='month monthheader'>".date('M', strtotime($strtime))."</th>";
                    }

                    $output .= "</tr>";
                    $output .= "<thead>";

                    $output .= "<tbody>";
                    for ($day = 1; $day <= 31; $day++)
                    {
                        $output .= "<tr>";
                            $output .= "<td class='daynum'>{$day}</td>";

                            for ($month = 1; $month <= 12; $month++)
                            {
                                if (checkdate($month, $day, $this->year))
                                {
                                    $output .= "<td class='month'>".$this->getStudentTrackerDay($day, $month, $this->year)."</td>";
                                }
                                else
                                {
                                    $output .= "<td class='non-date'></td>";
                                }
                            }

                        $output .= "</tr>";
                    }
                    $output .= "</tbody>";

                    $output .= "<tfoot></tfoot>";

                $output .= "</table>";       

            $output .= "</div>";      
                
        return $output;
        
    }
    
    /**
     * 
     * @global type $CFG
     * @return string
     */
    public function getStudentTrackerOptions(){
        
        global $CFG;
        
        $courseID = optional_param('courseID', SITEID, PARAM_INT);
        if ($courseID == -1){
            $courseID = SITEID;
        }
        
 
        
        $output = "";
        
        $url = $CFG->wwwroot . '/blocks/bcgt/grids/assessment_tracker.php';
        
        $output .= "<form>";
        
        // Year
        $currentYear = date('Y');
        
        $output .= "<small>".ucfirst(get_string('year'))."</small><br>";
        $output .= "<select name='year' id='yearfield'>";
        
            for($y = ($currentYear - 1); $y <= ($currentYear + 1); $y++)
            {
                $chk = ($this->year == $y) ? 'selected' : '';
                $output .= "<option value='{$y}' {$chk} >{$y}</option>";
            }
        
        $output .= "</select>";
        
        $output .= "<br><br>";
        
        // List courses this student is on
        $output .= "<small>".get_string('course')."</small><br>";
        $output .= "<select name='courseID' id='coursefield'>";
            $output .= "<option value='-1'>All</option>";
            if ($this->courses)
            {
                foreach($this->courses as $course)
                {
                    $chk = ($courseID == $course->id) ? 'selected' : '';
                    $output .= "<option value='{$course->id}' {$chk} >{$course->fullname}</option>";
                }
            }
        $output .= "</select>";
        
        $output .= "<br><br>";
        
        $output .= "<small>".get_string('modulelinks', 'block_bcgt')."</small><br>";
        $output .= "&nbsp;&nbsp;";
        $chk = ($this->modLinks == 'any') ? 'checked' : '';
        $output .= "<small>".get_string('any', 'block_bcgt')."</small>  <input type='radio' name='critact' value='any' {$chk} />";
        $output .= "&nbsp;&nbsp;&nbsp;&nbsp;";
        $chk = ($this->modLinks == 'crit') ? 'checked' : '';
        $output .= "<small>".get_string('criteria', 'block_bcgt')."</small>  <input type='radio' name='critact' value='crit' {$chk} />";
        
        $output .= "<br><br>";
        
        // List module types
        $output .= "<small>".get_string('moduletypes', 'block_bcgt')."</small><br>";
        $output .= "<select name='modules[]' multiple='multiple' id='modulesfield'>";
        
        $someMods = false;
        
        $defaultModules = explode(",", self::DEFAULT_MOD_TYPES);
        
        if ($defaultModules)
        {
            foreach($defaultModules as $mod)
            {
                $modInfo = get_mod_linking_by_name($mod);
                if ($modInfo)
                {
                    $someMods = true;
                    $chk = (in_array($mod, $this->modTypes)) ? 'selected' : '';
                    $output .= "<option value='{$mod}' {$chk} >".$this->getModPluginName($mod)."</option>";
                }
            }
        }
        
        if (!$someMods)
        {
            $output .= "<option value='' disabled='disabled'>No Modules Have Been Linked to Gradetracker</option>";
        }
        
        $output .= "</select>";
        
        $output .= "<br><br>";
        
        // Show the values the student has for the criteria, if linked?
        $output .= "<small>".get_string('showcriteriavalues', 'block_bcgt')."</small><br>";
        $output .= "&nbsp;&nbsp;";
        $chk = ($this->showValues) ? 'checked' : '';
        $output .= "<small>".get_string('yes')."</small>  <input type='radio' name='showvalues' value='1' {$chk} />";
        $output .= "&nbsp;&nbsp;&nbsp;&nbsp;";
        $chk = (!$this->showValues) ? 'checked' : '';
        $output .= "<small>".get_string('no')."</small>  <input type='radio' name='showvalues' value='0' {$chk} />";
        
        $output .= "<br><br>";
        
        
        
        $output .= "<p class='c'><input type='button' id='filter_calendar' value='".get_string('filter')."' class='btn' /></p>";
        
        $output .= "</form>";
        
        return $output;
        
    }
    
    
    
    /**
     * 
     * @global type $CFG
     * @return string|boolean
     */
    public function getCourseTracker(){
       
        global $CFG;
        
        if (!$this->course){
            return false;
        }
        
        $output = "";
        
        $output .= "<h2 class='c'>".$this->course->fullname."</h2>";

        // Colour key
        if ($this->viewType == 'calendar')
        {
            $output .= "<fieldset><legend>".get_string('modulecolourkey', 'block_bcgt')."</legend>";

                $output .= "<div class='c'>";

                    if ($this->modTypes)
                    {
                        $this->tmpColArray = array();
                        $i = 1;
                        foreach($this->modTypes as $type)
                        {
                            $col = $this->getColour($i);
                            $this->tmpColArray[$type] = $i;
                            $output .= "<div style='height:15px;padding:4px;margin-bottom:20px;font-size:8pt;background-color:#{$col['bg']};color:#{$col['font']};float:left;margin-left:25px;'>".$this->getModPluginName($type)."</div>";
                            $i++;
                        }
                    }

                $output .= "<br style='clear:both;' />";
                $output .= "</div>";

            $output .= "</fieldset><br>";
        }

        $output .= "<p class='c' id='loading2'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/ajax-loader.gif' /></p>";

        $output .= "<div id='assessment_tracker_div'>";

        $output .= "<table id='assessment_tracker_table'>";

        
        // Normal calendar view
        if ($this->viewType == 'calendar')
        {
        

            $output .= "<thead>";
                $output .= "<tr>";
                $output .= "<th class='daynum'></th>";

                for ($month = 1; $month <= 12; $month++)
                {
                    $strtime = "01-{$month}-{$this->year}";
                    $output .= "<th class='month monthheader'>".date('M', strtotime($strtime))."</th>";
                }

                $output .= "</tr>";
            $output .= "</thead>";

                $output .= "<tbody>";
                for ($day = 1; $day <= 31; $day++)
                {
                    $output .= "<tr>";
                        $output .= "<td class='daynum'>{$day}</td>";

                        for ($month = 1; $month <= 12; $month++)
                        {
                            if (checkdate($month, $day, $this->year))
                            {
                                $output .= "<td class='month'>".$this->getCourseTrackerDay($day, $month, $this->year)."</td>";
                            }
                            else
                            {
                                $output .= "<td class='non-date'></td>";
                            }
                        }

                    $output .= "</tr>";
                }

            $output .= "</tbody>";


        }
        else
        {
            
            
            // Get all modules this year for this course
            $year = $this->year;
            $strtimestart = "01-01-{$year} 00:00";
            $strtimeend = "31-12-{$year} 23:59";
            
            $start = strtotime($strtimestart);
            $end = strtotime($strtimeend);
            
            $activeMods = $this->getActiveModules($start, $end, $this->course);
            
            $activeModsArray = array();
            if ($activeMods){
                foreach($activeMods as $activeMod){
                    $courseModule = bcgt_get_course_module($this->course->id, $activeMod->modid , $activeMod->id);
                    if (!$courseModule){
                        continue;
                    }

                    // If we only want criteria linked ones, and this has none, skip
                    if ($this->modLinks == 'crit' && !bcgt_course_module_has_criteria_links($courseModule->id)){
                        continue;
                    }
                    
                    $activeModsArray[] = $activeMod;
                    
                }
            }
            
            $activeMods = $activeModsArray;
            
                        
            // Modules along the top
            $output .= "<thead>";
                $output .= "<tr>";
                $output .= "<th class='weeknum'></th>";

                if ($activeMods)
                {
                    foreach($activeMods as $activeMod)
                    {
                        
                        // See if there is an icon
                        $icon = $CFG->dirroot . '/mod/' . $activeMod->modtype . '/pix/icon.png';
                        $iconOutput = '';
                        if (file_exists($icon)){

                            $icon = str_replace($CFG->dirroot, $CFG->wwwroot, $icon);
                            $iconOutput = "<img class='icn' src='{$icon}' /> ";

                        }
                        
                        $output .= "<th class='modheader mod'><a href='#' class='mod_head' moduleType='{$activeMod->modtype}' moduleID='{$activeMod->id}'>{$iconOutput} {$activeMod->name}</a></th>";
                    }
                }
                else
                {
                    $output .= "<th class='modheader mod'>".get_string('nomodules', 'block_bcgt')."</th>";
                }

                $output .= "</tr>";
            $output .= "</thead>";
            
            $output .= "<tbody>";
                                    
            for ($i = 0; $i < 52; $i++)
            {
                $week = array();
                $week['number'] = $i + 1;
                $week['start'] = strtotime("+{$i} weeks", $start);
                $week['end'] = strtotime("+6 days 23:59", $week['start']);
                
                $output .= "<tr>";
                
                    $output .= "<td class='weeknum'>".date('d/m/Y', $week['start'])."</td>";
                    
                    if ($activeMods)
                    {
                        foreach($activeMods as $activeMod)
                        {
                            $mod = get_mod_linking_by_name($activeMod->modtype);
                            
                            $sField = $mod->modtablestartdatefname;
                            $eField = $mod->modtableduedatefname;
                            
                            // Did the module start during this week?
                            if ($activeMod->$sField >= $week['start'] && $activeMod->$sField <= $week['end'])
                            {
                                $output .= "<td class='mod modstart'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/start.png' /> ".date('D jS M Y, H:i', $activeMod->$sField)."</td>";
                            }
                            // Does the module end during this week?
                            elseif($activeMod->$eField >= $week['start'] && $activeMod->$eField <= $week['end'])
                            {
                                $output .= "<td class='mod modend'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/stop.png' /> ".date('D jS M Y, H:i', $activeMod->$eField)."</td>";
                            }
                            else
                            {
                                $output .= "<td class='mod'></td>";
                            }
                            
                        }
                    }
                    else
                    {
                        $output .= "<td class='mod'></td>";
                    }
                    
                $output .= "</tr>";
                                
            }
            
            $output .= "</tbody>";
            
            
        }
                    
        $output .= "<tfoot></tfoot>";

        $output .= "</table>";       

        $output .= "</div>";    
        
        
        return $output;
        
    }
    
    
    
    /**
     * 
     * @global type $CFG
     * @return string
     */
    public function getCourseTrackerOptions(){
        
        global $CFG;
        
        if (!$this->course){
            return false;
        }
                        
        $output = "";
        
        $url = $CFG->wwwroot . '/blocks/bcgt/grids/assessment_tracker.php';
        
        $output .= "<form>";
        
        // View
        $output .= "<small>".get_string('view')."</small><br>";
        $output .= "&nbsp;&nbsp;";
        $chk = ($this->viewType == 'calendar') ? 'checked' : '';
        $output .= "<small>".get_string('calendar', 'block_bcgt')."</small>  <input type='radio' name='viewtype' value='calendar' {$chk} />";
        $output .= "&nbsp;&nbsp;";
        $chk = ($this->viewType == 'weeks') ? 'checked' : '';
        $output .= "<small>".get_string('assweeks', 'block_bcgt')."</small>  <input type='radio' name='viewtype' value='weeks' {$chk} />";
        
        $output .= "<br><br>";
        
        // Year
        $currentYear = date('Y');
        
        $output .= "<small>".ucfirst(get_string('year'))."</small><br>";
        $output .= "<select name='year' id='yearfield'>";
        
            for($y = ($currentYear - 1); $y <= ($currentYear + 1); $y++)
            {
                $chk = ($this->year == $y) ? 'selected' : '';
                $output .= "<option value='{$y}' {$chk} >{$y}</option>";
            }
        
        $output .= "</select>";
        
        $output .= "<br><br>";
                
        $output .= "<small>".get_string('modulelinks', 'block_bcgt')."</small><br>";
        $output .= "&nbsp;&nbsp;";
        $chk = ($this->modLinks == 'any') ? 'checked' : '';
        $output .= "<small>".get_string('any', 'block_bcgt')."</small>  <input type='radio' name='critact' value='any' {$chk} />";
        $output .= "&nbsp;&nbsp;&nbsp;&nbsp;";
        $chk = ($this->modLinks == 'crit') ? 'checked' : '';
        $output .= "<small>".get_string('criteria', 'block_bcgt')."</small>  <input type='radio' name='critact' value='crit' {$chk} />";
        
        $output .= "<br><br>";
        
        // List module types
        $output .= "<small>".get_string('moduletypes', 'block_bcgt')."</small><br>";
        $output .= "<select name='modules[]' multiple='multiple' id='modulesfield'>";
        
        $someMods = false;
        
        $defaultModules = explode(",", self::DEFAULT_MOD_TYPES);
        
        if ($defaultModules)
        {
            foreach($defaultModules as $mod)
            {
                $modInfo = get_mod_linking_by_name($mod);
                if ($modInfo)
                {
                    $someMods = true;
                    $chk = (in_array($mod, $this->modTypes)) ? 'selected' : '';
                    $output .= "<option value='{$mod}' {$chk} >".$this->getModPluginName($mod)."</option>";
                }
            }
        }
        
        if (!$someMods)
        {
            $output .= "<option value='' disabled='disabled'>No Modules Have Been Linked to Gradetracker</option>";
        }
        
        $output .= "</select>";
        
        $output .= "<br><br>";
        
        $output .= "<p class='c'><input type='button' id='filter_calendar' value='".get_string('filter')."' class='btn' /></p>";
        
        $output .= "</form>";
        
        return $output;
        
    }
    
    
    
    private function getCourseTrackerDay($day, $month, $year){
        
        global $CFG;
        
        $output = "";
        
        if (!checkdate($month, $day, $year)){
            return false;
        }
        
        $now = time();
        
        if (!$this->modLinks || !in_array($this->modLinks, array('any', 'crit'))){
            $this->modLinks = self::DEFAULT_MOD_LINKS;
        }
        
        if (!$this->modTypes){
            $this->modTypes = explode(",", self::DEFAULT_MOD_TYPES);
        }

        $visibleSetting = get_config('bcgt', 'modstrackercheckcoursevisible');
        
        
        if ($day < 10) $day = '0'.$day;
        if ($month < 10) $month = '0'.$month;
        
        $strtimestart = "{$day}-{$month}-{$year} 00:00";
        $strtimeend = "{$day}-{$month}-{$year} 23:59";
        $start = strtotime($strtimestart);
        $end = strtotime($strtimeend);
                        
        $checkModLinks = false;
        
        if ($this->modLinks == 'crit'){
            $checkModLinks = true;
        }
     
        
        // Just for this one course
        $activeMods = $this->getActiveModules($start, $end, $this->course);
        if ($activeMods){

            foreach($activeMods as $activeMod){

                $courseModule = bcgt_get_course_module($this->course->id, $activeMod->modid , $activeMod->id);
                $info = get_mod_linking_by_name($activeMod->modtype);
                $dueField = $info->modtableduedatefname;
                                
                if ($courseModule)
                {
                
                    // If we only want criteria linked ones, and this has none, skip
                    if ($checkModLinks && !bcgt_course_module_has_criteria_links($courseModule->id)){
                        continue;
                    }

                    // If not visible yet, and we don't want to see invisible ones, skip
                    if ($courseModule->visible == 0 && $visibleSetting == 1){
                        continue;
                    }

                    $i = $this->tmpColArray[$activeMod->modtype];
                    $colour = $this->getColour($i);
                    $opacity = ($activeMod->$dueField < $now) ? 'opacity:0.4;' : '';
                    

                    $output .= "<div class='mod_item' style='background-color:#{$colour['bg']};color:#{$colour['font']};{$opacity}' moduleType='{$activeMod->modtype}' moduleID='{$activeMod->id}'>";

                        $name = substr($activeMod->name, 0, 8);
                        if ( strlen($activeMod->name) > 8 ){
                            $name .= '..';
                        }

                        // See if there is an icon
                        $icon = $CFG->dirroot . '/mod/' . $activeMod->modtype . '/pix/icon.png';
                        if (file_exists($icon)){

                            $icon = str_replace($CFG->dirroot, $CFG->wwwroot, $icon);
                            $output .= "<img class='icn' src='{$icon}' /> ";

                        }

                        // Name of the mod
                        $output .= $name;

                    $output .= "</div>";
                
                }

            }
        }
                        
        return $output;
        
    }
    
    private function getStudentTrackerDay($day, $month, $year)
    {
        
        global $CFG;
        
        $output = "";
        
        if (!checkdate($month, $day, $year)){
            return false;
        }
        
        if (!$this->modLinks || !in_array($this->modLinks, array('any', 'crit'))){
            $this->modLinks = self::DEFAULT_MOD_LINKS;
        }
        
        if (!$this->modTypes){
            $this->modTypes = explode(",", self::DEFAULT_MOD_TYPES);
        }

        $visibleSetting = get_config('bcgt', 'modstrackercheckcoursevisible');
        
        
        if ($day < 10) $day = '0'.$day;
        if ($month < 10) $month = '0'.$month;
        
        $strtimestart = "{$day}-{$month}-{$year} 00:00";
        $strtimeend = "{$day}-{$month}-{$year} 23:59";
        $start = strtotime($strtimestart);
        $end = strtotime($strtimeend);
                
        $i = 1;
        
        $checkModLinks = false;
        
        if ($this->modLinks == 'crit'){
            $checkModLinks = true;
        }
        
        foreach($this->courses as $course){
            
            $activeMods = $this->getActiveModules($start, $end, $course);
                        
            if ($activeMods){
                
                foreach($activeMods as $activeMod){
                    
                    $courseModule = bcgt_get_course_module($course->id, $activeMod->modid , $activeMod->id);
                    
                    if ($courseModule)
                    {
                    
                        // If we only want criteria linked ones, and this has none, skip
                        if ($checkModLinks && !bcgt_course_module_has_criteria_links($courseModule->id)){
                            continue;
                        }

                        // If not visible yet, and we don't want to see invisible ones, skip
                        if ($courseModule->visible == 0 && $visibleSetting == 1){
                            continue;
                        }

                        // If this activity is assigned to a particular group, and user is not in that group, skip
                        if ($courseModule->groupingid > 0 && !bcgt_is_user_in_grouping($this->student->id, $courseModule->groupingid)){
                            continue;
                        }


                        $colour = $this->getColour($i);

                        $output .= "<div class='mod_item' style='background-color:#{$colour['bg']};color:#{$colour['font']};' moduleType='{$activeMod->modtype}' moduleID='{$activeMod->id}'>";

                            $name = substr($activeMod->name, 0, 8);
                            if ( strlen($activeMod->name) > 8 ){
                                $name .= '..';
                            }

                            // See if there is an icon
                            $icon = $CFG->dirroot . '/mod/' . $activeMod->modtype . '/pix/icon.png';
                            if (file_exists($icon)){

                                $icon = str_replace($CFG->dirroot, $CFG->wwwroot, $icon);
                                $output .= "<img class='icn' src='{$icon}' /> ";

                            }

                            // Name of the mod
                            $output .= $name;

                        $output .= "</div>";
                    
                    }
                                        
                }
            }
            
            $i++;
            
        }
        
        return $output;
        
    }
    
    
    
     /**
     * 
     * @global type $CFG
     * @return string|boolean
     */
    public function getQualTracker(){
        
        global $CFG;
        
        if (!$this->qual){
            return false;
        }
        
        
        // If we only want ones linked to criteria, filter out others
        if ($this->modLinks == 'crit'){
            
            $newCourseArray = array();
            
            if ($this->courses)
            {
                foreach($this->courses as $course)
                {
                    
                    if (bcgt_course_has_criteria_module_links($course->id))
                    {
                        $newCourseArray[] = $course;
                    }
                    
                }
            }
                        
            $this->courses = $newCourseArray;
            
        }
        
                
        $output = "";
        
        $output .= "<h2 class='c'>".$this->qual->get_display_name()."</h2>";
                        
            // Colour key
        if ($this->viewType != 'weeks'){
            
            $output .= "<fieldset><legend>".get_string('modulecolourkey', 'block_bcgt')."</legend>";

                $output .= "<div class='c'>";

                    if ($this->modTypes)
                    {
                        $this->tmpColArray = array();
                        $i = 1;
                        foreach($this->modTypes as $type)
                        {
                            $col = $this->getColour($i);
                            $this->tmpColArray[$type] = $i;
                            $output .= "<div style='height:15px;padding:4px;margin-bottom:20px;font-size:8pt;background-color:#{$col['bg']};color:#{$col['font']};float:left;margin-left:25px;'>".$this->getModPluginName($type)."</div>";
                            $i++;
                        }
                    }

                $output .= "<br style='clear:both;' />";
                $output .= "</div>";

            $output .= "</fieldset><br>";
            
        }

            $output .= "<p class='c' id='loading2'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/ajax-loader.gif' /></p>";

            $output .= "<div id='assessment_tracker_div'>";

                $output .= "<table id='assessment_tracker_table'>";
                    $output .= "<thead>";
                    $output .= "<tr>";
                    $output .= "<th class='daynum'></th>";

                    if ($this->viewType == 'weeks'){
                        
                        // Get all modules this year for this course
                        $year = $this->year;
                        $strtimestart = "01-01-{$year} 00:00";
                        $strtimeend = "31-12-{$year} 23:59";

                        $start = strtotime($strtimestart);
                        $end = strtotime($strtimeend);
                        
                        $activeModsArray = array();
                        
                        if ($this->courses)
                        {

                            foreach($this->courses as $course)
                            {
                                
                                $activeMods = $this->getActiveModules($start, $end, $course);
                                
                                if ($activeMods){
                                    
                                    foreach($activeMods as $activeMod){
                                                                                
                                        $courseModule = bcgt_get_course_module($course->id, $activeMod->modid , $activeMod->id);
                                        if (!$courseModule){
                                            continue;
                                        }

                                        // If we only want criteria linked ones, and this has none, skip
                                        if ($this->modLinks == 'crit' && !bcgt_course_module_has_criteria_links($courseModule->id)){
                                            continue;
                                        }

                                        $activeModsArray[$activeMod->id] = $activeMod;

                                    }
                                }
                                
                            }
                        
                        }

                        $activeMods = $activeModsArray;
                        
                        if ($activeMods)
                        {
                            foreach($activeMods as $activeMod)
                            {
                                // See if there is an icon
                                $icon = $CFG->dirroot . '/mod/' . $activeMod->modtype . '/pix/icon.png';
                                $iconOutput = '';
                                if (file_exists($icon)){

                                    $icon = str_replace($CFG->dirroot, $CFG->wwwroot, $icon);
                                    $iconOutput = "<img class='icn' src='{$icon}' /> ";

                                }

                                $output .= "<th class='modheader mod'><a href='#' class='mod_head' moduleType='{$activeMod->modtype}' moduleID='{$activeMod->id}'>{$iconOutput} {$activeMod->name}</a></th>";

                            }
                        }
                        
                        
                        
                    } else {
                    
                        for ($month = 1; $month <= 12; $month++)
                        {
                            $strtime = "01-{$month}-{$this->year}";
                            $output .= "<th class='month monthheader'>".date('M', strtotime($strtime))."</th>";
                        }
                    
                    }

                    $output .= "</tr>";
                    $output .= "<thead>";

                    $output .= "<tbody>";
                    
                    if ($this->viewType == 'weeks')
                    {
                        
                        for ($i = 0; $i < 52; $i++)
                        {
                            $week = array();
                            $week['number'] = $i + 1;
                            $week['start'] = strtotime("+{$i} weeks", $start);
                            $week['end'] = strtotime("+6 days 23:59", $week['start']);

                            $output .= "<tr>";

                                $output .= "<td class='weeknum'>".date('d/m/Y', $week['start'])."</td>";

                                if ($activeMods)
                                {
                                    foreach($activeMods as $activeMod)
                                    {
                                        $mod = get_mod_linking_by_name($activeMod->modtype);

                                        $sField = $mod->modtablestartdatefname;
                                        $eField = $mod->modtableduedatefname;

                                        // Did the module start during this week?
                                        if ($activeMod->$sField >= $week['start'] && $activeMod->$sField <= $week['end'])
                                        {
                                            $output .= "<td class='mod modstart'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/start.png' /> ".date('D jS M Y, H:i', $activeMod->$sField)."</td>";
                                        }
                                        // Does the module end during this week?
                                        elseif($activeMod->$eField >= $week['start'] && $activeMod->$eField <= $week['end'])
                                        {
                                            $output .= "<td class='mod modend'><img src='{$CFG->wwwroot}/blocks/bcgt/pix/stop.png' /> ".date('D jS M Y, H:i', $activeMod->$eField)."</td>";
                                        }
                                        else
                                        {
                                            $output .= "<td class='mod'></td>";
                                        }

                                    }
                                }
                                else
                                {
                                    $output .= "<td class='mod'></td>";
                                }

                            $output .= "</tr>";

                        }
                        
                    }
                    else
                    {
                    
                        for ($day = 1; $day <= 31; $day++)
                        {
                            $output .= "<tr>";
                                $output .= "<td class='daynum'>{$day}</td>";

                                for ($month = 1; $month <= 12; $month++)
                                {
                                    if (checkdate($month, $day, $this->year))
                                    {
                                        $output .= "<td class='month'>".$this->getQualTrackerDay($day, $month, $this->year)."</td>";
                                    }
                                    else
                                    {
                                        $output .= "<td class='non-date'></td>";
                                    }
                                }

                            $output .= "</tr>";
                        }
                    
                    }
                    
                    $output .= "</tbody>";

                    $output .= "<tfoot></tfoot>";

                $output .= "</table>";       

            $output .= "</div>";      
                
        return $output;
        
    }
    
    
    
    /**
     * 
     * @global type $CFG
     * @return string
     */
    public function getQualTrackerOptions(){
        
        global $CFG;
        
        if (!$this->qual){
            return false;
        }
                        
        $courseID = optional_param('courseID', SITEID, PARAM_INT);
        if ($courseID == -1){
            $courseID = SITEID;
        }
        
        
        $output = "";
        
        $url = $CFG->wwwroot . '/blocks/bcgt/grids/assessment_tracker.php';
        
        $output .= "<form>";
        
        
        // View
        $output .= "<small>".get_string('view')."</small><br>";
        $output .= "&nbsp;&nbsp;";
        $chk = ($this->viewType == 'calendar') ? 'checked' : '';
        $output .= "<small>".get_string('calendar', 'block_bcgt')."</small>  <input type='radio' name='viewtype' value='calendar' {$chk} />";
        $output .= "&nbsp;&nbsp;";
        $chk = ($this->viewType == 'weeks') ? 'checked' : '';
        $output .= "<small>".get_string('assweeks', 'block_bcgt')."</small>  <input type='radio' name='viewtype' value='weeks' {$chk} />";
        
        $output .= "<br><br>";
        
        
        // Year
        $currentYear = date('Y');
        
        $output .= "<small>".ucfirst(get_string('year'))."</small><br>";
        $output .= "<select name='year' id='yearfield'>";
        
            for($y = ($currentYear - 1); $y <= ($currentYear + 1); $y++)
            {
                $chk = ($this->year == $y) ? 'selected' : '';
                $output .= "<option value='{$y}' {$chk} >{$y}</option>";
            }
        
        $output .= "</select>";
        
        $output .= "<br><br>";
        
        
        // List courses this qual is on
        $output .= "<small>".get_string('course')."</small><br>";
        $output .= "<select name='courseID' id='coursefield'>";
            $output .= "<option value='-1'>All</option>";
            if ($this->courses)
            {
                foreach($this->courses as $course)
                {
                    $chk = ($courseID == $course->id) ? 'selected' : '';
                    $output .= "<option value='{$course->id}' {$chk} >{$course->fullname}</option>";
                }
            }
        $output .= "</select>";
        
        $output .= "<br><br>";
        
        
        // Module links
        // When looking at a qual, presumably we only want ones linked to this qual...
        
        
        // List module types
        $output .= "<small>".get_string('moduletypes', 'block_bcgt')."</small><br>";
        $output .= "<select name='modules[]' multiple='multiple' id='modulesfield'>";
        
        $someMods = false;
        
        $defaultModules = explode(",", self::DEFAULT_MOD_TYPES);
        
        if ($defaultModules)
        {
            foreach($defaultModules as $mod)
            {
                $modInfo = get_mod_linking_by_name($mod);
                if ($modInfo)
                {
                    $someMods = true;
                    $chk = (in_array($mod, $this->modTypes)) ? 'selected' : '';
                    $output .= "<option value='{$mod}' {$chk} >".$this->getModPluginName($mod)."</option>";
                }
            }
        }
        
        if (!$someMods)
        {
            $output .= "<option value='' disabled='disabled'>No Modules Have Been Linked to Gradetracker</option>";
        }
        
        $output .= "</select>";
        
        $output .= "<br><br>";
        
        $output .= "<p class='c'><input type='button' id='filter_calendar' value='".get_string('filter')."' class='btn' /></p>";
        
        $output .= "</form>";
        
        return $output;
        
    }
    
    
    
    private function getQualTrackerDay($day, $month, $year){
        
        global $CFG;
        
        $output = "";
                
        if (!checkdate($month, $day, $year)){
            return false;
        }
        
        // Only ones linked to this qual
        $this->modLinks = 'crit';
        
        if (!$this->modTypes){
            $this->modTypes = explode(",", self::DEFAULT_MOD_TYPES);
        }

        $visibleSetting = get_config('bcgt', 'modstrackercheckcoursevisible');
        
        
        if ($day < 10) $day = '0'.$day;
        if ($month < 10) $month = '0'.$month;
        
        $strtimestart = "{$day}-{$month}-{$year} 00:00";
        $strtimeend = "{$day}-{$month}-{$year} 23:59";
        $start = strtotime($strtimestart);
        $end = strtotime($strtimeend);
                        
        $checkModLinks = false;
        
        if ($this->modLinks == 'crit'){
            $checkModLinks = true;
        }
                
        foreach($this->courses as $course){
            
            $activeMods = $this->getActiveModules($start, $end, $course);
                        
            if ($activeMods){
                
                foreach($activeMods as $activeMod){
                    
                    $courseModule = bcgt_get_course_module($course->id, $activeMod->modid , $activeMod->id);
                    
                    if ($courseModule)
                    {
                    
                        // Must have links to criteria and must be on this qual
                        if (!bcgt_course_module_has_criteria_links($courseModule->id, $this->qual->get_id())){
                            continue;
                        }

                        // If not visible yet, and we don't want to see invisible ones, skip
                        if ($courseModule->visible == 0 && $visibleSetting == 1){
                            continue;
                        }

                        $i = $this->tmpColArray[$activeMod->modtype];
                        $colour = $this->getColour($i);

                        $output .= "<div class='mod_item' style='background-color:#{$colour['bg']};color:#{$colour['font']};' moduleType='{$activeMod->modtype}' moduleID='{$activeMod->id}'>";

                            $name = substr($activeMod->name, 0, 8);
                            if ( strlen($activeMod->name) > 8 ){
                                $name .= '..';
                            }

                            // See if there is an icon
                            $icon = $CFG->dirroot . '/mod/' . $activeMod->modtype . '/pix/icon.png';
                            if (file_exists($icon)){

                                $icon = str_replace($CFG->dirroot, $CFG->wwwroot, $icon);
                                $output .= "<img class='icn' src='{$icon}' /> ";

                            }

                            // Name of the mod
                            $output .= $name;

                        $output .= "</div>";
                    
                    }
                                        
                }
            }
                        
        }
        
        
                        
        return $output;
        
    }
    
    
    
    
    
    
    private function getActiveModules($start, $end, $course){
        
        global $DB;
        
        $return = array();
        
        if ($this->modTypes){
            
            foreach($this->modTypes as $type){
                
                $mod = get_mod_linking_by_name($type);
                                
                if ($mod)
                {
                    
                    $modID = $mod->moduleid;

                    $sql = "SELECT *, '{$type}' as modtype, '{$modID}' as modid, {$mod->modtitlefname} as name
                            FROM {{$mod->modtablename}}
                            WHERE {$mod->modtablecoursefname} = ?
                            AND 
                            (
                                (
                                    {$mod->modtablestartdatefname} >= ?
                                    AND
                                    {$mod->modtablestartdatefname} <= ?
                                )
                                OR
                                (
                                    {$mod->modtablestartdatefname} <= ?
                                )

                            )
                            AND {$mod->modtableduedatefname} >= ?";
                            
                    $params = array($course->id, $start, $end, $start, $start);

                    $records = $DB->get_records_sql($sql, $params);
                    if ($records){
                        foreach($records as $record){
                            $return[] = $record;
                        }
                    }
                
                }
                
            }
            
        }
        
        return $return;
        
    }
    
    
    public function getActiveAssessments()
    {
                
        $return = array();     
        
        $this->modTypes = explode(",", self::DEFAULT_MOD_TYPES);

        $visibleSetting = get_config('bcgt', 'modstrackercheckcoursevisible');
        
        $strtimestart = date('d-m-Y') . " 00:00";
        $strtimeend = date('d-m-Y') . " 23:59";
        $start = strtotime($strtimestart);
        $end = strtotime($strtimeend);
                                
        foreach($this->courses as $course){
            
            $activeMods = $this->getActiveModules($start, $end, $course);
                        
            if ($activeMods){
                
                foreach($activeMods as $activeMod){
                    
                    $courseModule = bcgt_get_course_module($course->id, $activeMod->modid , $activeMod->id);
                    $modLinkInfo = get_mod_linking_by_name($activeMod->modtype);
                                        
                    $dueDateField = $modLinkInfo->modtableduedatefname;
                    $titleField = $modLinkInfo->modtitlefname;
                    
                    $activeMod->modduetime = $activeMod->$dueDateField;
                    $activeMod->modinstancetitle = $activeMod->$titleField;
                                        
                    // If not visible yet, and we don't want to see invisible ones, skip
                    if ($courseModule->visible == 0 && $visibleSetting == 1){
                        continue;
                    }
                    
                    // If this activity is assigned to a particular group, and user is not in that group, skip
                    if ($courseModule->groupingid > 0 && !bcgt_is_user_in_grouping($this->student->id, $courseModule->groupingid)){
                        continue;
                    }
                    
                    $return[$activeMod->id] = $activeMod;
                                                            
                }
            }
                        
        }
        
        
        usort($return, function($a, $b){
            return ($b->modduetime < $a->modduetime);
        });
                
        return $return;
        
    }
    
    
    
}
