<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Import
 *
 * @author mchaney
 */
class Import {
    //put your code here
    
    protected $action;
    protected $file;
    
    protected $userPriorLearning;
    protected $userCourseTarget;
    protected $qualWeighting;
    protected $project;
    protected $userData;
    protected $systemData;
    protected $errorMessage;
    protected $backup;
    protected $group;
    protected $registerGroups;
    //I AM FULLY AWARE THAT HAVING THESE SWITCHES IN IT IS NOT FULLY OBJECT ORIENTED!!
//THEY SHOULD BE inherited instancs of classes. in a workspace or something
    
    public function Import($action, $file)
    {
        $this->action = $action;
        $this->file = $file;
        
        $this->load_import_obj();
    }
    
    public function check_capability($cID = -1)
    {
        global $COURSE;
        if($cID != -1)
        {
            $context = context_course::instance($cID);
        }
        else
        {
            $context = context_course::instance($COURSE->id);
        }
        switch($this->action)
        {
            //qualsOnEntry
            case 'pl':
                require_capability('block/bcgt:importpriorlearning', $context);
                break;
            default:
                break;
        }
    }
    
    public function get_message()
    {
        return $this->errorMessage;
    }
    
    public function validate($server = false)
    {
        global $CFG;
        $obj = $this->get_object();
        if($obj->has_multiple())
        {
            $retval = $obj->validate($server);
            $this->errorMessage = $retval->errorMessage;
            return $retval->retval;
        }
        else 
        {
            if($server)
            {
                //get from the server
                $file = $CFG->dataroot.'/bcgt/import/'.$obj->get_file_names();
                $fileFull = $file;
                if(!file_exists($file))
                {
                    $this->errorMessage = get_string('noimportfile','block_bcgt');
                    return false;
                }
            }
            else 
            {
                if(!isset($_FILES['importfile']))
                {
                    $this->errorMessage = get_string('noimportfile','block_bcgt');
                    return false;
                }
                $fileFull = $_FILES['importfile']["name"];
                $file = $_FILES['importfile']["tmp_name"];
            }
            
            if(substr(strrchr($fileFull,'.'),1) != 'csv')
            {
                $this->errorMessage = get_string('notcsvfile','block_bcgt');
                return false;
            }
            $count = 0;
            $CSV = fopen($file, 'r');
            $header = '';
            while(($assessmentMark = fgetcsv($CSV)) !== false) { 
                if($count === 1)
                {
                    break;
                }
                $header = $assessmentMark;
                $count++;
            }
            //check headers. 
            $arrayHeaders = $obj->get_headers();
            if(count($arrayHeaders) != count($header))
            {
                $this->errorMessage = get_string('countheadersimport','block_bcgt');
                return false;
            }
            $headerCount = 0;
            foreach($arrayHeaders AS $h)
            {
                if($header[$headerCount] != $h)
                {
                    $this->errorMessage = get_string('csvheadersdontmatch','block_bcgt');
                    $this->errorMessage.= '<br />'.get_string('expected', 'block_bcgt').implode(',',$arrayHeaders);
                    $this->errorMessage.= '<br />'.get_string('found', 'block_bcgt').implode(',',$header);
                    return false;
                }
                $headerCount++;
            }
        }
        return true;
    }
    
    public function display_file_options()
    {
        $obj = $this->get_object();
        if($obj->has_multiple())
        {
            $out = $obj->get_file_options();
        }
        else 
        {
            $out = '<input type="file" name="importfile" value="file" id="file"/>';
            $out .= '<input type="hidden" name="a" value="'.$this->action.'"/>';
        }
        $out .= '<input type="submit" name="import" value="'.get_string('import', 'block_bcgt').'"/>';
        $out .= '<input type="submit" name="importcalc" value="'.get_string('importcalc', 'block_bcgt').'"/>';
        $out .= '<input type="submit" name="importcalcfromserver" value="'.get_string('importcalcserver', 'block_bcgt').'"/>';
        $out .= '<p>'.get_string('importserverdesc','block_bcgt').'</p>';
        $out .= $obj->get_file_names();
        return $out;
    }
    
    public function load_import_obj()
    {
        switch($this->action)
        {
            //qualsOnEntry
            case 'pl':
                $userPLearn = new UserPriorLearning();
                $this->userPriorLearning = $userPLearn;
            break;
            //target grades
            case 'tg':
                $obj = new UserCourseTarget();
                $this->userCourseTarget = $obj;
            break;
            //qual weightings
            case 'w':
                $obj = new QualWeighting();
                $this->qualWeighting = $obj;
            break;
            //project
            case 'fam':
                $obj = new Project();
                $this->project = $obj;
            break;
            //userdata
            case 'ud':
                $obj = new UserDataImport();
                $this->userData = $obj;
                break;
            case 'sd':
                $obj = new SystemDataImport();
                $this->systemData = $obj;
                break;
            case 'b':
                $obj = new BackupImport();
                $this->backup = $obj;
                break;
            case 'gr':
                $obj = new Group();
                $this->group = $obj;
                break;
            case 'reggrp':
                $obj = new RegisterGroupImport();
                $this->registerGroups = $obj;
            break;
        }
    }
    
    public function get_object()
    {
        switch($this->action)
        {
            //qualsOnEntry
            case 'pl':
                return $this->userPriorLearning;
                break;
            //target grades
            case 'tg':
                return $this->userCourseTarget;
                break;
            //qual weightings
            case 'w':
                return $this->qualWeighting;
                break;
            //formal assessment marks
            case 'fam':
                return $this->project;
                break;
            //userdata
            case 'ud':
                return $this->userData;
                break;
            //systemdata
            case 'sd':
                return $this->systemData;
                break;
            //backup
            case 'b':
                return $this->backup;
                break;
            case 'gr':
                return $this->group;
                break;
            case 'reggrp':
                return $this->registerGroups;
            break;
        }
    }
    
    public function set_file($server = false)
    {
        global $CFG;
        $obj = $this->get_object();
        if($obj->has_multiple())
        {
            $this->file = $obj->get_files($server);
        }
        else 
        {
            if($server)
            {
                $this->file = $CFG->dataroot.'/bcgt/import/'.$obj->get_file_names();
            }
            else 
            {
                $this->file = $_FILES['importfile'];
            }
        }
    }
    
    public function get_header()
    {
        switch($this->action)
        {
            case 'pl':
                return html_writer::tag('h2', get_string('priorlearning','block_bcgt').
        '', array('class'=>'formheading'));
            break;
            case 'tg':
                return html_writer::tag('h2', get_string('targetgrades','block_bcgt').
        '', array('class'=>'formheading'));
            break;
            //qual weightings
            case 'w':
                return html_writer::tag('h2', get_string('qualweightings','block_bcgt').
        '', array('class'=>'formheading'));
            break;
            case 'fam':
                return html_writer::tag('h2', get_string('assessmentmarks', 'block_bcgt'). 
                        '', array('class'=>'formheading'));
                break;
            case 'ud':
                return html_writer::tag('h2', get_string('userdata', 'block_bcgt'). 
                        '', array('class'=>'formheading'));
                break;
            case 'sd':
                return html_writer::tag('h2', get_string('systemdata', 'block_bcgt'). 
                        '', array('class'=>'formheading'));
                break;
            case 'b':
                return html_writer::tag('h2', get_string('backup', 'block_bcgt'). 
                        '', array('class'=>'formheading'));
                break;
            case 'gr':
                return html_writer::tag('h2', get_string('groups'). 
                        '', array('class'=>'formheading'));
                break;
            case 'reggrp':
                return html_writer::tag('h2', get_string('registergroups', 'block_bcgt'), array('class' => 'formheading'));
            break;
        }
        
    }
    
    public function display_import_options()
    {
        $obj = $this->get_object();
        return $obj->display_import_options();
    }
    
    public function get_submitted_import_options()
    {
        $obj = $this->get_object();
        return $obj->get_submitted_import_options();
    }
    
    
    public function get_tabs($courseID = -1)
    {
        global $CFG;
        //needs to check available capibilities
        $out = '<div class="tabs"><div class="tabtree">';
        $out .= '<ul class="tabrow0">';
        $focus = '';
        if($this->action == 'pl')
        {
            $focus='focus';
        }
        $out .= $this->get_ind_tab($this->action, 'priorlearning', 'pl');
        $out .= $this->get_ind_tab($this->action, 'targetgrades', 'tg');
        $out .= $this->get_ind_tab($this->action, 'qualweightings', 'w');
        $out .= $this->get_ind_tab($this->action, 'assessmentmarks', 'fam');
        //$out .= $this->get_ind_tab($this->action, 'userdata', 'ud');
        if(get_config('bcgt', 'usegroupsingradetracker'))
        {
            $out .= $this->get_ind_tab($this->action, 'groupsgroupings', 'gr');
        }
        if(get_config('bcgt', 'useregistergroups'))
        {
            $out .= $this->get_ind_tab($this->action, 'registergroups', 'reggrp');
        }
//        $out .= '<li>'.
//                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/import.php?a=sd">'.
//                '<span>'.get_string('systemdata', 'block_bcgt').'</span></a></li>';
//        $out .= '<li>'.
//                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/import.php?a=b">'.
//                '<span>'.get_string('backups', 'block_bcgt').'</span></a></li>';
        $out .= '</ul>';
        $out .= '</div></div>';
        
        return $out;
    }
    
    protected function get_ind_tab($currentAction, $name, $action)
    {
        global $CFG;
        $focus = '';
        if($currentAction == $action)
        {
            $focus = 'focus';
        }
        return '<li class="'.$focus.'">'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/import.php?a='.$action.'">'.
                '<span>'.get_string($name, 'block_bcgt').'</span></a></li>';;
    }
    
    public function get_description()
    {
        $retval = '';        
        $obj = $this->get_object();
                
        $desc = $obj->get_description();
        $multipleFiles = $obj->has_multiple();
        $headers = $obj->get_headers();
        $examples = $obj->get_examples();
                
        $retval .= '<h3>'.get_string('desc', 'block_bcgt').'</h3>';
        $retval .= '<p id="import">';
        $retval .= '<span class="description">'.$desc.'</span>';
        $retval .= '<span class="importheader">'.get_string('header', 'block_bcgt').'</span>';
        $out = '';
        if(!$multipleFiles)
        {
            foreach($headers AS $header)
            {
                $out .= $header.',';
            }
        }
        else
        {
            $out .= $headers;
        }
        
        $retval .= '<span class="header desc">'.$out.'</span>';
        $retval .= '<span class="importexamples">'.get_string('examples', 'block_bcgt').'</span>';
        $retval .= '<span class="examples desc">'.$examples.'</span>';
        $retval .= '</p>';
        return $retval;
    }
    
    public function get_import_values()
    {
        //find the families, 
        //find the levels
        //find the subtypes
        
        $families = get_qualification_type_families_used();
        $levels = get_qualification_level();
        $subtypes = get_qualification_subtype();
        $retval = '<h3>'.get_string('importoptions','block_bcgt').'</h3>';
        $retval .= '<p>'.get_string('importnotice','block_bcgt').'</p>';
        $retval .= '<ul>';
        $retval .= '<li>'.get_string('family', 'block_bcgt').' : ';
        foreach($families AS $family)
        {
            $retval .= $family->family.', ';
        }
        $retval .= '<li>'.get_string('levels', 'block_bcgt').' : ';
        foreach($levels AS $level)
        {
            $retval .= $level->get_level().', ';
        }
        $retval .= '<li>'.get_string('subtypes', 'block_bcgt').' : ';
        foreach($subtypes AS $subtype)
        {
            $retval .= $subtype->get_subtype().', ';
        }
        //TODO ->values
        $retval .= '</ul>';
        
        
        return $retval;
    }
    
    public function display_summary()
    {
        $obj = $this->get_object();
        return $obj->display_summary();
    }
    
    public function was_success()
    {
        $obj = $this->get_object();
        return $obj->was_success();
    }
    
    public function process_import($processActions, $server = false)
    {
//        global $CFG;
//        $fullFilePath = $CFG->dirroot.'/blocks/bcgt/';
//        move_uploaded_file($this->file["tmp_name"],
//        $fullFilePath . $this->file["name"]);
//        
//        $obj = $this->get_object();
//        $obj->process_import_csv($fullFilePath.$this->file['name'], $processActions);
        set_time_limit(0);
        global $CFG;
//        move_uploaded_file($this->file["tmp_name"],
//        $fullFilePath . $this->file["name"]);
        
        $obj = $this->get_object();
        if($obj->has_multiple())
        {
            $obj->process_import_csv($processActions, $server);
        }
        else
        {
            if($server)
            {
                $obj->process_import_csv($this->file, $processActions);
            }
            else
            {
                $obj->process_import_csv($this->file["tmp_name"], $processActions);
            }
        }
    }
}

?>
