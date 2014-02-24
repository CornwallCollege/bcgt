<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserDataImport
 *
 * @author mchaney
 */
class UserDataImport {
    //put your code here
    protected $file;
    protected $summary;
    protected $success;
    
    CONST FILENAME = 'userdataimport.xlsx';
    CONST FILEEXT = 'xlsx';
    
    protected $overwriteData;
    protected $overwriteIfNewer;
    protected $ignoreIDs;
    
    protected $importMethod;
    protected $importQualID;
    protected $usersearch;
    protected $importUserID;
    
    protected $upload_dir;
    
    protected $runDataChanges;
    protected $addUserUnit;
    
    function UserDataImport()
    {
        global $CFG;
        $importMethod = optional_param('sub', '', PARAM_TEXT);
        $this->importMethod = $importMethod;
        
        $overwriteData = optional_param('option1', false, PARAM_BOOL);
        $this->overwriteData = $overwriteData;
        
        $overwriteIfNewer = optional_param('overwritenewer', false, PARAM_BOOL);
        $this->overwriteIfNewer = $overwriteIfNewer; 
        
        $exportFromThisSystem = optional_param('option2', false, PARAM_BOOL);
        //if it is exportedFromThisSystem then we DONT want to ignore the IDs
        $this->ignoreIDs = !$exportFromThisSystem;
        
        $runDataChanges = optional_param('rundatachanges', false, PARAM_BOOL);
        $this->runDataChanges = $runDataChanges;
        
        $addUserUnit = optional_param('adduserunit', false, PARAM_BOOL);
        $this->addUserUnit = $addUserUnit;
        
        
        $this->upload_dir = $CFG->dataroot.'/bcgt/import/';
        
        switch($importMethod)
        {
            case"all":
                //no extras
                break;
            case"qual":
                $qualID = optional_param('qualimport', -1, PARAM_INT);
                $this->importQualID = $qualID;
                break;
            case "user":
                $userSearch = optional_param('usersearch', '', PARAM_TEXT);
                $this->usersearch = $userSearch;
                $userID = optional_param('userimport', -1, PARAM_INT);
                $this->importUserID = $userID;
                break;
        }
    }
    
    public function get_headers()
    {
        switch($this->importMethod)
        {
            case "all":
                return $this->get_header(1);
                break;
            case "qual":
                return $this->get_header(2);
                break;
            case "user":
                return $this->get_header(3);
                break;
            case "unit":
                return $this->get_header(4);
                break;
            default:
                return "";
                break;
        }
    }
    
    public function get_template()
    {
        global $CFG;
        switch($this->importMethod)
        {
            case "all":
                return $CFG->dirroot.'/blocks/bcgt/templates/alluserimportexample.xlsx';
                break;
            case "qual":
                return $CFG->dirroot.'/blocks/bcgt/templates/qualuserimportexample.xlsx';
                break;
            case "user":
                return $CFG->dirroot.'/blocks/bcgt/templates/useruserimportexample.xlsx';
                break;
            default:
                break;
        }
    }
    
    private function get_header($no)
    {
        $retval = '';
        $retval .= '<ul>';
        switch($no)
        {
            case 1:
                //this is 'all'
                $retval .= '<li>'.get_string('qualsheet','block_bcgt').' = ';
                $retval .= '(';
                $retval .= 'Username,QualificationID,Family,Level,Subtype,Name,Additional Name,Comments';
                $retval .= ')</li>';
                $retval .= '<li>'.get_string('unitsheet','block_bcgt').' = ';
                $retval .= '(';
                $retval .= 'Username,QualificationID,UnitID,Family(OF Unit),Level(OF Unit),'.
                        'Name(OF Unit),UniqueID,Award,Comments, Value, User Define Value';
                $retval .= ')</li>';
                $retval .= '<li>'.get_string('criteriasheet','block_bcgt').' = ';
                $retval .= '(';
                $retval .= 'Username,UnitID,CriteriaID,Criteria Name, Value,'.
                        'Set By, Date Set,Comments, User Defined Value, Target Date, '.
                        'Target Grade, Target Breakdown, Awad Date';
                $retval .= ')</li>';
                $retval .= '<li>'.get_string('awardsheet','block_bcgt').' = ';
                $retval .= '(';
                $retval .= 'Username,QualID,Breakdown,'.
                        'Grade, Type, Overall Grade';
                $retval .= ')</li>';
                break;
            case 2:
                //this is 'qual'
                $retval .= '<li>'.get_string('qualsheet','block_bcgt').' = ';
                $retval .= '(';
                $retval .= 'Username,Comments';
                $retval .= ')</li>';
                $retval .= '<li>'.get_string('unitsheet','block_bcgt').' = ';
                $retval .= '(';
                $retval .= 'Username,UnitID,Level(OF Unit),'.
                        'Name(OF Unit),UniqueID,Comments, Value, User Define Value';
                $retval .= ')</li>';
                $retval .= '<li>'.get_string('criteriasheet','block_bcgt').' = ';
                $retval .= '(';
                $retval .= 'Username,UnitID,CriteriaID,Criteria Name, Value,'.
                        'Set By, Date Set,Comments, User Defined Value, Target Date, '.
                        'Target Grade, Target Breakdown, Awad Date';
                $retval .= ')</li>';
                break;
            case 3:
                //this is 'user'
                $retval .= '<li>'.get_string('qualsheet','block_bcgt').' = ';
                $retval .= '(';
                $retval .= 'QualificationID,Family,Level,Subtype,Name,Additional Name,Comments';
                $retval .= ')</li>';
                $retval .= '<li>'.get_string('unitsheet','block_bcgt').' = ';
                $retval .= '(';
                $retval .= 'QualificationID,UnitID,Family(OF Unit),Level(OF Unit),'.
                        'Name(OF Unit),UniqueID,Award,Comments, Value, User Define Value';
                $retval .= ')</li>';
                $retval .= '<li>'.get_string('criteriasheet','block_bcgt').' = ';
                $retval .= '(';
                $retval .= 'UnitID,CriteriaID,Criteria Name, Value,'.
                        'Set By, Date Set,Comments, User Defined Value, Target Date, '.
                        'Target Grade, Target Breakdown, Awad Date';
                $retval .= ')</li>';
                $retval .= '<li>'.get_string('awardsheet','block_bcgt').' = ';
                $retval .= '(';
                $retval .= 'QualID,Breakdown,'.
                        'Grade, Type, Overall Grade';
                $retval .= ')</li>';
                break;
            case 4:
                //this is unit
                break;
        }
        $retval .= '</ul>';
        return $retval;
    }
    
    public function get_examples()
    {
        global $CFG;
        $retval = '';
        if($this->importMethod != '')
        {
            $retval = get_string('seeuseruploadexample','block_bcgt');
        }
        switch($this->importMethod)
        {
            case "all":
                $retval .= '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/download_template.php?action=userdatatemplate&sub=all" target="_blank">'.get_string('examplefile', 'block_bcgt').'</a>';
                break;
            case "qual":
                $retval .= '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/download_template.php?action=userdatatemplate&sub=qual" target="_blank">'.get_string('examplefile', 'block_bcgt').'</a>';
                break;
            case "user":
                $retval .= '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/download_template.php?action=userdatatemplate&sub=user" target="_blank">'.get_string('examplefile', 'block_bcgt').'</a>';
                break;
        }
        return $retval;
    }
    
    public function get_description()
    {
        global $CFG;
        $retval = '<p>'.get_string('overalluserdateimportdesc','block_bcgt').'</p>';
        $retval .= '<div class="tabs"><div class="tabtree">';
        $retval .= '<ul class="tabrow0">';
//        $retval .= '<li>'.
//                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/import.php?a=ud&sub=all">'.
//                '<span>'.get_string('all', 'block_bcgt').'</span></a></li>';
        $retval .= '<li>'.
                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/import.php?a=ud&sub=qual">'.
                '<span>'.get_string('byqual', 'block_bcgt').'</span></a></li>';
//        $retval .= '<li>'.
//                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/import.php?a=ud&sub=user">'.
//                '<span>'.get_string('byuser', 'block_bcgt').'</span></a></li>';
//        $retval .= '<li>'.
//                '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/import.php?a=fam&sub=unit">'.
//                '<span>'.get_string('byunit', 'block_bcgt').'</span></a></li>';
        $retval .= '</ul>';
        $retval .= '</div></div>';
        $retval .= '<p>'.$this->get_sub_description().'</p>';
        //now output the description. 
        
        return $retval .= '';
//        return get_string('uddesc', 'block_bcgt');
    }
    
    protected function get_sub_description()
    {
        switch($this->importMethod)
        {
            case "all":
                return get_string('alluserimportdesc', 'block_bcgt');
                break;
            case "qual":
                return get_string('qualuserimportdesc', 'block_bcgt');
                break;
            case "user":
                return get_string('useruserimportdesc', 'block_bcgt');
                break;
            case "unit":
                return get_string('unituserimportdesc','block_bcgt');
                break;
            default:
                return "";
                break;
        }
    }
    
    public function get_file_names()
    {
        $retval = '';
        $retval .= UserDataImport::FILENAME;
        return $retval; 
    }
        
    public function has_multiple()
    {
        return true;
    }
    
    public function get_file_options()
    {
        $retval = get_string('import','block_bcgt').' : ';
        $retval .= '<input type="file" name="importfile1" value="file1" id="file1"/><br />';
        return $retval;
    }
    
    public function get_submitted_import_options()
    {
        
    }
    
    public function was_success()
    {
        return $this->success;
    }
    
    public function display_summary()
    {   
        $errors = $this->summary->errors;
        $retval = '';
        if($this->summary->usersNotFound)
        {
            $retval .= '<h3>'.get_string('usersnotfound', 'block_bcgt').'</h3>';
            $retval .= '<p>';
            foreach($this->summary->usersNotFound AS $userNotFound)
            {
                $retval .= $userNotFound.', ';
            }
            $retval .= '</p>';
        }
        if($this->summary->unitsNotFound)
        {
            $retval .= '<h3>'.get_string('unitsnotfound', 'block_bcgt').'</h3>';
            $retval .= '<p>';
            foreach($this->summary->unitsNotFound AS $unitNotFound)
            {
                $retval .= $unitNotFound.', ';
            }
            $retval .= '</p>';
        }
        if($this->summary->multipleUnitsFoundNonStudent)
        {
            $retval .= '<h3>'.get_string('multipleunitsfoundnostudent', 'block_bcgt').'</h3>';
            $retval .= '<p>';
            foreach($this->summary->multipleUnitsFoundNonStudent AS $unitNotFound)
            {
                $retval .= $unitNotFound.', ';
            }
            $retval .= '</p>';
        }
        if($this->summary->unitsNotOnQual)
        {
            $retval .= '<h3>'.get_string('unitsnotonqual', 'block_bcgt').'</h3>';
            $retval .= '<p>';
            foreach($this->summary->unitsNotOnQual AS $unitNotOnQual)
            {
                $retval .= $unitNotOnQual.', ';
            }
            $retval .= '</p>';
        }
        if($this->summary->criteriaNotOnUnit)
        {
            $retval .= '<h3>'.get_string('criterianotonunit', 'block_bcgt').'</h3>';
            $retval .= '<table><thead><tr>';
            $retval .= '<th>'.get_string('unit', 'block_bcgt').'</th>';
            $retval .= '<th>'.get_string('criteria', 'block_bcgt').'</th>';
            $retval .= '</tr></head>';
            foreach($this->summary->criteriaNotOnUnit AS $unit => $criterias)
            {
                $retval .= '<tr>';
                $retval .= '<td>'.$unit.'</td>';
                $retval .= '<td><ul>';
                foreach($criterias AS $crit)
                {
                    $retval .= '<li>'.$crit.'</li>';
                }
                $retval .= '</ul></td>';
                $retval .= '</tr>';
            }
            $retval .= '<tbody></table>';
        }
        if($this->summary->valuesNotFound)
        {
            $retval .= '<h3>'.get_string('valuesnotfound', 'block_bcgt').'</h3>';
            $retval .= '<p>';
            foreach($this->summary->valuesNotFound AS $valueNotFound)
            {
                $retval .= $valueNotFound.', ';
            }
            $retval .= '</p>';
        }
        if($this->summary->userNotOnUnit)
        {
            $retval .= '<h3>'.get_string('usersnotonunit', 'block_bcgt').'</h3>';
            $retval .= '<table><thead><tr>';
            $retval .= '<th>'.get_string('unit', 'block_bcgt').'</th>';
            $retval .= '<th>'.get_string('users', 'block_bcgt').'</th>';
            $retval .= '</tr></head>';
            foreach($this->summary->userNotOnUnit AS $unit => $users)
            {
                $retval .= '<tr>';
                $retval .= '<td>'.$unit.'</td>';
                $retval .= '<td><ul>';
                foreach($users AS $user)
                {
                    $retval .= '<li>'.$user.'</li>';
                }
                $retval .= '</ul></td>';
                $retval .= '</tr>';
            }
            $retval .= '<tbody></table>';
        }
        
        $retval .= '<h3>'.get_string('othererrors','block_bcgt').'</h3>';
        $retval .= '<table><thead><tr>';
        $retval .= '<th>Sheet No</th><th>Row No</th><th>Error</th><th>Warnings</th><th>Code</th>';
        $retval .= '</tr></thead><tbody>';
        
        //errors is an array of objects  
        foreach($errors AS $error)
        {
            if(isset($error->sheetno) || isset($error->sheetno) 
                    || (isset($error->sheetno) && count($error->sheetno) > 0) 
                    ||(isset($error->warnings) && count($error->warnings) > 0))
            {
                $retval .= '<tr>';
                $retval .= '<td>';
                if(isset($error->sheetno))
                {
                    $retval .= $error->sheetno;
                }
                $retval .= '</td>';
                $retval .= '<td>';
                if(isset($error->rowno))
                {
                    $retval .= $error->rowno;
                }
                $retval .= '</td>';
                $retval .= '<td><ul>';
                foreach($error->errors AS $e)
                {
                    $retval .= '<li>'.$e.'</li>';
                }
                $retval .= '</ul></td>';
                $retval .= '<td><ul>';
                foreach($error->warnings AS $w)
                {
                    $retval .= '<li>'.$w.'</li>';
                }
                $retval .= '</ul></td>';
                $retval .= '<td><ul>';
                foreach($error->code AS $e)
                {
                    $retval .= '<li>'.$e.'</li>';
                }
                $retval .= '</ul></td>';
                $retval .= '</tr>';
            }
//            $summary->sheetno = 1;
//            $summary->rowno = $rowNumber;
//            $summary->user = $upUserName;
//            $summary->errors[] = 'No User';
        }
        $retval .= '</tbody></table>';
        return $retval;
    }
    
    public function validate($server = false)
    {
        global $CFG;
        $retval = new stdClass();
        $retval->errorMessage = '';
        $retval->retval = true;
        if($server)
        {
            $fullFile = $CFG->dataroot.'/bcgt/import/'.UserDataImport::FILENAME;
            if(!file_exists($fullFile))
            {
                $this->errorMessage = get_string('noimportfile','block_bcgt');
                $retval->errorMessage = $this->errorMessage;
                $retval->retval = false;
                return $retval;
            }
            if(substr(strrchr($fullFile,'.'),1) != UserDataImport::FILEEXT)
            {
                $retval->errorMessage = get_string('notcorrectfileformat','block_bcgt').UserDataImport::FILEEXT;
                $retval->retval = false;
                return $retval;
            }
            //IDEALLY need to get all of the sheets and check the headers. 
        }
        else
        {
            if(!isset($_FILES['importfile1']))
            {
                $retval->errorMessage = get_string('noimportfile','block_bcgt');
                $retval->retval = false;
            }
            if(substr(strrchr($_FILES['importfile1']["name"],'.'),1) != UserDataImport::FILEEXT)
            {
                $retval->errorMessage = get_string('notcorrectfileformat','block_bcgt').UserDataImport::FILEEXT;
                $retval->retval = false;
            }
            //IDEALLY need to get all of the sheets and check the headers. 
        }
        switch($this->importMethod)
        {
            case"all":
                //no entra validations
                break;
            case"qual":
                //need a qualid
                if($this->importQualID == -1)
                {
                    $retval->errorMessage = get_string('pleaseselectaqual','block_bcgt');
                    $retval->retval = false;
                    return $retval;
                }
                break;
        }
        return $retval;
    }
    
//    public function check_header($file, $headerArray)
//    {
//        $count = 0;
//        $CSV = fopen($file, 'r');
//        $header = '';
//        while(($assessmentMark = fgetcsv($CSV)) !== false) {
//            if($count === 1)
//            {
//                break;
//            }
//            $header = $assessmentMark;
//            $count++;
//        }
//        return $this->validate_header($header, $headerArray);
//    }
//    
//    public function validate_header($headerCSV, $headerArray)
//    {
//        $retval = new stdCLass();
//        if(count($headerArray) != count($headerCSV))
//        {
//            $retval->errorMessage = get_string('countheadersimport','block_bcgt');
//            $retval->retval = false;
//        }
//        $headerCount = 0;
//        foreach($headerArray AS $h)
//        {
//            if($headerCSV[$headerCount] != $h)
//            {
//                $retval->errorMessage = get_string('csvheadersdontmatch','block_bcgt');
//                $retval->retval = false;
//            }
//            $headerCount++;
//        }
//        $retval->retval = true;
//        return $retval;
//    }

    public function get_files($server = false)
    {
        global $CFG;
        $retval = new stdClass();
        if($server)
        {
            $retval->file = $CFG->dataroot.'/bcgt/import/'.UserDataImport::FILENAME;
        }
        else
        {
            $retval->file = $_FILES['importfile1'];
        }
        $this->file = $retval;
        return $retval;
    }
    
    public function process_import_csv($process = false)
    {
        // Check the format of the spreadsheet
        /** PHPExcel_IOFactory */
        global $CFG;
        require_once $CFG->dirroot.'/blocks/bcgt/lib/PHPExcel/Classes/PHPExcel.php';
        foreach (glob($CFG->dirroot.'/blocks/bcgt/lib/PHPExcel/Classes/PHPExcel/Reader/*.php.') as $filename)
        {
            require_once $filename;
        }
        
//        require_once $CFG->dirroot.'/blocks/bcgt/lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
        $file = $this->file->file;
        $fileName = $file['tmp_name'];
        if(file_exists($this->upload_dir.UserDataImport::FILENAME))
        {
            unlink($this->upload_dir.UserDataImport::FILENAME);
        }
        move_uploaded_file($fileName, $this->upload_dir.UserDataImport::FILENAME);
        switch($this->importMethod)
        {
            case"all":
                $summary = $this->process_all_import($process);
                break;
            case"qual":
                $summary = $this->process_qual_import($process);
                $users = $summary->users;
                if($this->runDataChanges && $users)
                {
                    $loadParams = new stdClass();
                    $loadParams->loadLevel = Qualification::LOADLEVELALL;
                    $userQualification = Qualification::get_qualification_class_id($this->importQualID, $loadParams);
                    if($userQualification)
                    {
                        foreach($users AS $user)
                        {
                            $userQualification->load_student_information($user->id, $loadParams);
                            $units = $userQualification->get_units();
                            if($units)
                            {
                                foreach($units AS $unit)
                                {
                                    $unit->calculate_unit_award($this->importQualID);
                                }
                            }
                            //calc all unit awards
                            //calc all qual awards
                            $userQualification->calculate_predicted_grade();
                        }
                    }
                }
                //now we loop over all and calc their qual award and unit awards
                
                break;
            case"user":
                $summary = $this->process_user_import($process);
                break;
        }
        
        $summaryErrors = $summary->errors;
        //only want to delete once we have completely finished and once we
        //are happy. so not always here. 
        if(count($summaryErrors) == 0)
        {
            unlink($this->upload_dir.UserDataImport::FILENAME);
        }
        else
        {
            $this->summary = $summary;
        }
        
//        $processRecords = optional_param('count', 0, PARAM_INT);
//        global $DB;
//        //needs to get the first file and process it, then the second, then third and so on. 
//        $userCriteriaCSV = $this->files->criteriafile;
//        $CSV = fopen($userCriteriaCSV, 'r');
//        $count = 1;
//        $qualNotFound = array();
//        $unitNotFound = array();
//        $studentNotFound = array();
//        $valueNotFound = array();
//        $criteriaNotFound = array();
//        $moreRecentUpdate = array();
//        $moreRecentUnitUpdate = array();
//        $awardNotFound = array();
//        $breakdownNotFound = array();
//        $moreRecentAwardUpdate = array();
//        $teachersFound = array();
//        $updatedRecords = 0;
//        $insertedRecords = 0;
//        $successCount = 0;
//        global $CFG;
//        //do 100 lines at a time.
//        $studentsFound = array();
////        echo "Processing ".($processRecords * 250)." to ".($processRecords * 250 + 250).'<br />';
//        while(($userCriteria = fgetcsv($CSV)) !== false) {
//            if($userCriteria[0] != '' && $count != 1)
//            {    
//                
//                //&& ($count >= ($processRecords * 250)) && ($count < ($processRecords * 250 + 250))
//                if(array_key_exists($userCriteria[5], $studentsFound))
//                {
//                    $studentID = $studentsFound[$userCriteria[5]];
//                }
//                else
//                {
//                    $student = $this->find_user($userCriteria[5]);
//                    if(!$student)
//                    {
//                        $studentNotFound[$userCriteria[5]] = $userCriteria[5];
//                        continue;
//                    }
//                    $studentID = $student->id;
//                    $studentsFound[$userCriteria[5]] = $studentID;
//                }
//                
//                if(array_key_exists($userCriteria[13], $teachersFound))
//                {
//                    $teacherID = $teachersFound[$userCriteria[13]];
//                }
//                else
//                {
//                    $teacher = $this->find_user($userCriteria[13]);
//                    $teacherID = -1;
//                    if($teacher)
//                    {
//                        $teacherID = $teacher->id;
//                        $teachersFound[$userCriteria[13]] = $teacherID;
//                    }
//                }
//                
//                
//                //find the qual
//                $quals = $this->find_qual($userCriteria[0], $userCriteria[1], $userCriteria[2], $userCriteria[3], $userCriteria[4]);
//                if(!$quals)
//                {
//                    print_object($userCriteria);
//                    echo ''.$userCriteria[0].' '.$userCriteria[1].' '.$userCriteria[2].
//                            ' '.$userCriteria[3].' '.$userCriteria[4].' Qual Not Found <br />';
//                    $qualNotFound[$userCriteria[0].' '.$userCriteria[1].' '.$userCriteria[2].' '.$userCriteria[3].' '.
//                        $userCriteria[4]] = $userCriteria[0].' '.$userCriteria[1].' '.$userCriteria[2].
//                            ' '.$userCriteria[3].' '.$userCriteria[4];
//                    continue;
//                }
//                if(count($quals) != 1)
//                {
//                    continue;
//                }
//                $qual = end($quals);
//                $qualID = $qual->id;
//                $typeID = $qual->typeid;
//                
//                //find the unit
//                $unit = $this->find_unit($userCriteria[0], $userCriteria[6], $userCriteria[7], $userCriteria[8], $userCriteria[9], $qualID);
//                if(!$unit)
//                {
//                    $unitNotFound[$userCriteria[0].' '.$userCriteria[6].' '.
//                        $userCriteria[7].' '.$userCriteria[8].' '.$userCriteria[9].
//                        ' '.$qualID] = $userCriteria[0].' '.$userCriteria[6].' '.
//                            $userCriteria[7].' '.$userCriteria[8].' '.$userCriteria[9].
//                            ' '.$qualID;
//                    
//                    //can we find it with just the name and the qualificationid?
//
//                    continue;
//                }
//                elseif(count($unit) > 1)
//                {
//                    continue;
//                }
//                $unitObj = end($unit);
//                $unitID = $unitObj->id;
//                
//                //find the criteria
//                $criteria = $this->find_criteria($userCriteria[10],$unitID);
//                if(!$criteria)
//                {
//                    $criteriaNotFound[$userCriteria[10]." ".$unitID] = $userCriteria[10]." ".$unitID;
//                    continue;
//                }
//                $criteriaID = $criteria->id;
//                //find the value
//                $value = $this->find_value($userCriteria[11], $typeID);
//                if(!$value)
//                {
//                    $valueNotFound[$userCriteria[11]." ".$typeID] = $userCriteria[11]." ".$typeID;
//                    continue;
//                }
//                $valueID = $value->id;
//                
//                $record = new stdClass();
//                $record->userid = $studentID;
//                $record->bcgtqualificationid = $qualID;
//                $record->bcgtcriteriaid = $criteriaID;
//                $record->bcgtvalueid = $valueID;
//                $record->updatebyuserid = $teacherID;
//                $record->comments = $userCriteria[12];
//                
//                $usersCriteriaRecord = $this->find_users_criteria_record($studentID, $criteriaID, $qualID);
//                if($usersCriteriaRecord)
//                {
//                    //has it been updated more recently than our last?
//                    if(!$usersCriteriaRecord->dateupdated || $usersCriteriaRecord->dateupdated < $userCriteria[14])
//                    {
//                        //then we want to update it
//                        $record->id = $usersCriteriaRecord->id;
//                        $DB->update_record('block_bcgt_user_criteria', $record);
//                        $updatedRecords++;
//                    }
//                    else
//                    {
//                        $moreRecentUpdate[] = $count; 
//                    }
//                }
//                else 
//                {
//                    //lets insert a brand new one
//                    $DB->insert_record('block_bcgt_user_criteria', $record);
//                    $insertedRecords++;
//                }     
//                $successCount++;
//            }
////            elseif($count >= (($processRecords * 250) + 250))
////            {
////                //then we are after the number and so we want to reload
////                $processRecords = $processRecords + 1;
////                redirect($CFG->wwwroot.'/blocks/bcgt/forms/import.php?a=ud&server=1&count='.$processRecords);
////            }
//            $count++;
//        }  
//        fclose($CSV);
//        $summary = new stdClass();
//        $success = true;
//        if(count($qualNotFound) > 0 || count($unitNotFound) > 0 || count($criteriaNotFound) > 0)
//        {
//            $success = false;
//        }
//        
//        $summary->successCount = $successCount;
//        $summary->successCountCriteria = $successCount;
//        $summary->qualsNotFound = $qualNotFound;
//        $summary->criteriasNotFound = $criteriaNotFound;
//        $summary->unitsNotFound = $unitNotFound;
//        $summary->insertCriteria = $insertedRecords;
//        $summary->updatedCriteria = $updatedRecords;
////        pn($summary);
//        
//        $userUnitCSV = $this->files->unitfile;
//        $CSV = fopen($userUnitCSV, 'r');
//        $count = 1;
//        $insertedRecords = 0;
//        $updatedRecords = 0;
//        $unitSuccess = 0;
//        while(($userUnit = fgetcsv($CSV)) !== false) {
//            if($count != 1)
//            {
//                //need to find the qual
//                //need to find the unit
//
//                if(array_key_exists($userUnit[5], $studentsFound))
//                {
//                    $studentID = $studentsFound[$userUnit[5]];
//                }
//                else
//                {
//                    $student = $this->find_user($userUnit[5]);
//                    if(!$student)
//                    {
//                        $studentNotFound[$userUnit[5]] = $userUnit[5];
//                        continue;
//                    }
//                    $studentID = $student->id;
//                    $studentsFound[$userUnit[5]] = $studentID;
//                }
//                if(array_key_exists($userUnit[12], $teachersFound))
//                {
//                    $teacherID = $teachersFound[$userUnit[12]];
//                }
//                else
//                {
//                    $teacher = $this->find_user($userUnit[12]);
//                    $teacherID = null;
//                    if($teacher)
//                    {
//                        $teacherID = $teacher->id;
//                        $teachersFound[$userUnit[12]] = $teacherID;
//                    }
//                    
//                }
//                   
////                
//                //find the qual
//                //$family, $type, $subtype, $level, $name
//                $quals = $this->find_qual($userUnit[0], $userUnit[1], $userUnit[2], $userUnit[3], $userUnit[4]);
//                if(!$quals)
//                {
//                    $qualNotFound[$userUnit[0].' '.$userUnit[1].' '.$userUnit[2].' '.$userUnit[3].' '.
//                        $userUnit[4]] = $userUnit[0].' '.$userUnit[1].' '.$userUnit[2].
//                            ' '.$userUnit[3].' '.$userUnit[4];
//                    continue;
//                }
//                elseif(count($quals) > 1)
//                {
//                    continue;
//                }
//                $qual = end($quals);
//                $qualID = $qual->id;
//                $typeID = $qual->typeid;
//                
//                //find the unit
//                //$family, $type, $level, $name, $uniqueID, $qualID = -1
//                $unit = $this->find_unit($userUnit[0], $userUnit[6], $userUnit[7], $userUnit[8], $userUnit[9], $qualID);
//                if(!$unit)
//                {
//                    $unitNotFound[$userUnit[0].' '.$userUnit[6].' '.
//                        $userUnit[7].' '.$userUnit[8].' '.$userUnit[9].
//                        ' '.$qualID] = $userUnit[0].' '.$userUnit[6].' '.
//                            $userUnit[7].' '.$userUnit[8].' '.$userUnit[9].
//                            ' '.$qualID;
//                    
//                    //can we find it with just the name and the qualificationid?
//
//                    continue;
//                }
//                elseif(count($unit) > 1)
//                {
//                    continue;
//                }
//                $unitObj = end($unit);
//                $unitID = $unitObj->id;
//                
//                $award = $this->find_award($userUnit[10], $typeID);
//                if(!$award)
//                {
//                    $awardNotFound[$userUnit[10].' '.$typeID] = $userUnit[10].' '.$typeID;
//                    continue;
//                }
//                $awardID = $award->id;
//                //need to find the award
//                //need to find the teacher
//                //need to find the student
//                $record = new stdClass();
//                $record->userid = $studentID;
//                $record->updatedbyuserid = $teacherID;
//                $record->bcgtunitid = $unitID;
//                $record->bcgtqualificationid = $qualID;
//                $record->bcgttypeawardid = $awardID;
//                $record->comments = $userUnit[11];
//                
//                //now find the user unit record;
//                $userUnitRecord = $this->find_users_unit_record($studentID, $unitID, $qualID);
//                if($userUnitRecord)
//                {
//                    if(!$userUnitRecord->dateupdated || $userUnitRecord->dateupdated < $userCriteria[12])
//                    {
//                        //update
//                        $record->id = $userUnitRecord->id;
//                        $updatedRecords++;
//                        $DB->update_record('block_bcgt_user_unit', $record);
//                    }
//                    else
//                    {
//                        $moreRecentUnitUpdate[] = $count; 
//                    }
//                }
//                else
//                {
//                    //insert
//                    $insertedRecords++;
//                    $DB->insert_record('block_bcgt_user_unit', $record);
//                }
//                $unitSuccess++;
//                $successCount++;
//            }
//            $count++;
//            
//        }  
//        fclose($CSV);
////        
//        if(count($qualNotFound) > 0 || count($unitNotFound) > 0)
//        {
//            $success = false;
//        }
//        
//        $summary->successCount = $successCount;
//        $summary->successCountUnit = $unitSuccess;
//        $summary->qualsNotFound = $qualNotFound;
//        $summary->unitsNotFound = $unitNotFound;
//        $summary->insertUnit = $insertedRecords;
//        $summary->updatedUnit = $updatedRecords;
////        pn($summary);
//        
//        $insertedRecords = 0;
//        $updatedRecords = 0;
//        $userAwardCSV = $this->files->awardfile;
//        $CSV = fopen($userAwardCSV, 'r');
//        $count = 1;
//        $awardSuccess = 0;
//        while(($userAward = fgetcsv($CSV)) !== false) {
//            if($count != 1)
//            {
//                //need to find the qual
//                //need to find the unit
//                //need to find the award
//                //need to find the teacher
//                //need to find the student
//                if(array_key_exists($userAward[5], $studentsFound))
//                {
//                    $studentID = $studentsFound[$userAward[5]];
//                }
//                else
//                {
//                    $student = $this->find_user($userAward[5]);
//                    if(!$student)
//                    {
//                        $studentNotFound[$userAward[5]] = $userUnit[5];
//                        continue;
//                    }
//                    $studentID = $student->id;
//                    $studentsFound[$userAward[5]] = $studentID;
//                }
//                
//                //find the qual
//                //$family, $type, $subtype, $level, $name
//                $quals = $this->find_qual($userAward[0], $userAward[1], $userAward[2], $userAward[3], $userAward[4]);
//                if(!$quals)
//                {
//                    $qualNotFound[$userAward[0].' '.$userAward[1].' '.$userAward[2].' '.$userAward[3].' '.
//                        $userAward[4]] = $userAward[0].' '.$userAward[1].' '.$userAward[2].
//                            ' '.$userAward[3].' '.$userAward[4];
//                    continue;
//                }
//                $qual = end($quals);
//                $qualID = $qual->id;
//                $targetQualID = $qual->bcgttargetqualid;
//
//                $breakdown = $this->find_breakdown($userAward[6], $targetQualID);
//                if(!$breakdown)
//                {
//                    $breakdownNotFound[$userAward[6].' '.$targetQualID] = $userAward[6].' '.$targetQualID;
//                    continue;
//                }
//                $breakdownID = $breakdown->id;
//                //need to find the award
//                //need to find the teacher
//                //need to find the student
//                $record = new stdClass();
//                $record->userid = $studentID;
//                $record->bcgtqualificationid = $qualID;
//                $record->bcgtbreakdownid = $breakdownID;
//                $record->type = 'Import';
//                $record->warning = '';
//                
//                //now find the user unit record;
//                $userAwardRecord = $this->find_users_award_record($studentID, $qualID);
//                if($userAwardRecord)
//                {
//                    //update
//                    foreach($userAwardRecord AS $award)
//                    {
//                        $record->id = $award->id;
//                        $updatedRecords++;
//                        $DB->update_record('block_bcgt_user_award', $record);
//                    }
//                }
//                else
//                {
//                    //insert
//                    $DB->insert_record('block_bcgt_user_award', $record);
//                    $insertedRecords++;
//                }
//                $awardSuccess++;
//                $successCount++;
//            }
//            $count++;
//            
//        }  
//        fclose($CSV);
//        
//        if(count($qualNotFound) > 0 || count($breakdownNotFound) > 0)
//        {
//            $success = false;
//        }
//        $summary->successCount = $successCount;
//        $summary->successCountAward = $awardSuccess;
//        $summary->qualsNotFound = $qualNotFound;
//        $summary->breakdownNotFound = $breakdownNotFound;
//        $summary->insertAward = $insertedRecords;
//        $summary->updatedAward = $updatedRecords;
////        pn($summary);
//        
//        $this->summary = $summary;
//        $this->success = $success;
    }
    
    private function process_all_import($process)
    {
        //are we ignoring the IDs?
        //are we overriting data?
        
        
    }
    
    private function process_qual_import($process)
    {
        //we have a qualification id
        //we want to loop over all of the users that are in the spreadsheet. 
        
        //are we ignoring the IDs?
        //Are we overwriting the data?
        global $DB, $USER;
        //need to move the file first
        //then delete it
//        print_object($file);
//        print_object($_FILES['importfile1']);
        // Load the spreadsheet into a reader
        /**  Create a new Reader of the type defined in $inputFileType  **/
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        /**  Advise the Reader that we only want to load cell data  **/
        $objReader->setReadDataOnly(true);
        /**  Load $inputFileName to a PHPExcel Object  **/
        $objPHPExcel = $objReader->load($this->upload_dir.UserDataImport::FILENAME);
////
        // Set the first worksheet as #1
        $worksheetNum = 0;
////
//
        //are we ignoring the IDs?
        //are we overwriting the data. 
        $foundUsers = array();
        $summaryErrors = array();
        $foundUnit = array();
        $workSheets = $objPHPExcel->getAllSheets();
        $qualTypeID = $this->get_type_of_qual($this->importQualID);
        
        //to be used so we can quickly skip. 
        $usersNotOnQual = array();
        $usersNotFound = array();
        $unitNotOnQual = array();
        //an array of Unitid -> aray of users. 
        $usersNotOnUnit = array();
        $valueNotFound = array();
        $unitNotFound = array();
        $criteriaNotOnUnit = array();
        $multipleUnitsFoundNonStudent = array();
        foreach ($workSheets as $worksheet)
        {
            $worksheetNum++;
            $worksheetName = $worksheet->getTitle();
            switch($worksheetName)
            {
                case 'Qualification':
                    //the is the qual
                    foreach ($worksheet->getRowIterator() as $row)
                    {
                        $errored = false;
                        $summary = new stdClass();
                        $summary->code = array();
                        $summary->errors = array();
                        $summary->warnings = array();
                        if(!is_null($row))
                        {
                            $rowNumber = $row->getRowIndex();
                            if($rowNumber == 1)
                            {
                                continue;
                            }
                            $cellIterator = $row->getCellIterator();
                            $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
                            // Loop Cells
                            $user = null;
                            foreach ($cellIterator as $cell)
                            {
                                $cellName = $cell->getCoordinate();
                                $cellData = (string)$cell->getCalculatedValue();

                                switch($cellName)
                                {
                                    case 'A'.$rowNumber:
                                        $upUserName = $cellData;
                                        if(array_key_exists($upUserName, $foundUsers))
                                        {
                                            $user = $foundUsers[$upUserName];
                                        }
                                        else
                                        {
                                            $user = $this->find_user($upUserName);
                                            if($user)
                                            {
//                                                echo "User Found : $upUserName <br />";
                                                $foundUsers[$upUserName] = $user;
                                            }
                                            else
                                            {
//                                                echo "No User can be found: $upUserName <br />";
                                                //then we cant find it so lets add it to the summary
                                                $usersNotFound[$upUserName] = $upUserName;
                                                $errored = true;
                                            }
                                        }
                                        break;
                                    case 'B'.$rowNumber:
                                        if($user)
                                        {
                                            $onQual = $this->is_user_on_qual($user->id, $this->importQualID);
                                            if(!$onQual)
                                            {
                                                $errored = true;
                                                $usersNotOnQual[$upUserName] = $upUserName;
//                                                echo "User Not on Qual : $upUserName <br />";
                                            }
                                            else
                                            {
                                                $comments = $cellData;
                                                if($comments != '')
                                                {
                                                    $existingComments = $onQual->comments;
                                                    if($existingComments)
                                                    {
                                                        //then we have existing comments
                                                        //do we need to create a history?
                                                        //do we need to check if we are updating?
                                                        if($this->overwriteData)
                                                        {
                                                            $onQual->comments = $comments;
                                                            //history
                                                            if($this->runDataChanges)
                                                            {
                                                                Qualification::insert_user_qual_history($onQual->id);
                                                                //are we updating them?
                                                                $DB->update_record('block_bcgt_user_qual', $onQual);
                                                            }
                                                            
                                                        }
                                                        else 
                                                        {
//                                                            echo "Qual Comments Already Exists: $upUserName<br />";
                                                            $summary->sheetno = 1;
                                                            $summary->rowno = $rowNumber;
                                                            $summary->user = $upUserName;
                                                            $summary->warnings[] = 'Qual Comment Exists';
                                                            $errored = true;
                                                        }
                                                    }
                                                    else
                                                    {
                                                        if($this->runDataChanges)
                                                        {
//                                                          echo "Updating comments : $upUserName <br />";
                                                            $onQual->comments = $comments;
                                                            Qualification::insert_user_qual_history($onQual->id);
                                                            $DB->update_record('block_bcgt_user_qual', $onQual);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    default:
                                        break;
                                }
                            }
                            //are they on this qual?
                            if($errored)
                            {
                                $summaryErrors[] = $summary;
                            }
                        }
                    }
                    echo "Finished Users : <br />";
                    break;
                case "Unit":
                    //this is the unit
                    
                    //find the user by the username -> ave we already found them from above?
                    //are they on this qual?
                    
                    //are we ignoring IDs? or are we using the ID?
                    
                    //if we are ignoring then whatever id they have there needs to go in an 
                    //array of the id vs the id in the system. We also need to find the unit
                    
                    //is the unit on this qualification?
                    
                    //is the user on the unit?
                    
                    //can we find the award?
                    
                    //can we find the value
                    
                    //can we find the user define value?
                    foreach ($worksheet->getRowIterator() as $row)
                    {
                        $userUnit = new stdClass();
                        $errored = false;
                        $warning = false;
                        $summary = new stdClass();
                        $summary->code = array();
                        $summary->errors = array();
                        $summary->warnings = array();
                        if(!is_null($row))
                        {
                            $rowNumber = $row->getRowIndex();
                            if($rowNumber == 1)
                            {
                                continue;
                            }
                            $cellIterator = $row->getCellIterator();
                            $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
                            // Loop Cells
                            $user = null;
                            foreach ($cellIterator as $cell)
                            {
                                $cellName = $cell->getCoordinate();
                                $cellData = (string)$cell->getCalculatedValue();

                                switch($cellName)
                                {
                                    case 'A'.$rowNumber:
                                        $upUserName = $cellData;
                                        if(array_key_exists($upUserName, $foundUsers))
                                        {
                                            $user = $foundUsers[$upUserName];
                                            $userUnit->userid = $user->id;
                                        }
                                        else
                                        {
                                            $user = $this->find_user($upUserName);
                                            if($user)
                                            {
                                                $userUnit->userid = $user->id;
                                                $foundUsers[$upUserName] = $user;
                                            }
                                            else
                                            {
                                                //then we cant find it so lets add it to the summary
                                                $usersNotFound[$upUserName] = $upUserName;
                                                $errored = true;
                                            }
                                        }
                                        break;
                                    case 'B'.$rowNumber:
                                        //its the unitID
                                        $userUnit->unitid = (int)$cellData;
                                    case 'C'.$rowNumber:
                                        //its the unitFamily
                                        $userUnit->unitFamily = $cellData;
                                    case 'D'.$rowNumber:
                                        //its the unitType
                                        $userUnit->unitType = $cellData;
                                    case 'E'.$rowNumber:
                                        //its the unitLevel
                                        $userUnit->unitLevel = $cellData;
                                    case 'F'.$rowNumber:
                                        //its the unitName
                                        $userUnit->unitName = $cellData;
                                    case 'G'.$rowNumber:
                                        //its the unitUniqueID
                                        $userUnit->unitUniqueID = $cellData;
                                    case 'H'.$rowNumber:
                                        //its the comments
                                        $userUnit->userComments = $cellData;
                                    case 'I'.$rowNumber:
                                        //its the value
                                        $userUnit->uservalue = $cellData;
                                    case 'J'.$rowNumber:
                                        //its the userDefineValue
                                        $userUnit->userDefinedValue = $cellData;
                                    default:
                                        break;
                                }
                            }
                            if(isset($userUnit->userid))
                            {
                                //so now process the userUnit obj
                                //are we ignoring the ID?
                                $unit = null;
                                if($this->ignoreIDs)
                                {
                                    //find the unit by the name etc
                                    $units = $this->find_unit(trim($userUnit->unitFamily), 
                                            trim($userUnit->unitType), null, 
                                            trim($userUnit->unitName), trim($userUnit->unitUniqueID));
                                    if(!$units)
                                    {
//                                        echo "Found no Units : $userUnit->unitFamily 
//                                                $userUnit->unitType $userUnit->unitName
//                                                   $userUnit->unitUniqueID <br />";
                                        $unitNotFound[$userUnit->unitFamily." ".
                                                $userUnit->unitType." ".$userUnit->unitLevel. 
                                            " ".$userUnit->unitName." ".$userUnit->unitUniqueID] = $userUnit->unitFamily." ".
                                                $userUnit->unitType." ".$userUnit->unitLevel. 
                                            " ".$userUnit->unitName." ".$userUnit->unitUniqueID;
                                        $errored = true;
                                        //can we find it by a different UniqueID?
                                        //can we find it by a different name?
                                        //the unique id is the most important one. 
                                        $unitDBs = $this->find_unit($userUnit->unitFamily, 
                                        $userUnit->unitType, $userUnit->unitLevel, 
                                        null, $userUnit->unitUniqueID, -1, $userUnit->userid);
                                        if($unitDBs)
                                        {
                                            echo "But Found unit by just uniqueid :<br />";
                                            if(count($unitDBs) == 1)
                                            {
                                                $unitDB = end($unitDBs);
                                                $summary->warnings[] = 'User Unit found:Same Unique ID, Different Name. Forced to Skip : Import Name = '.$userUnit->unitName.' vs DB Name = '.$unitDB->name;
                                                $warning = true;
                                            }
                                            else
                                            {
                                                $summary->warnings[] = 'Multiple User Units found:Same Unique ID, Different Name. Forced to Skip : Import Name = '.$userUnit->unitName.' vs Import UniqueID = '.$userUnit->unitUniqueID;
                                                $warning = true;
                                            }
                                            
                                        }
                                    }
                                    elseif(count($units) > 1)
                                    {
                                        //do we have the user?
                                        //can we find which one they are on, or which the qual has?
                                        $sql = "SELECT unit.* FROM {block_bcgt_qual_units} qualunits 
                                            JOIN {block_bcgt_unit} unit ON unit.id = qualunits.bcgtunitid
                                            WHERE qualunits.bcgtqualificationid = ? AND bcgtunitid IN ";
                                        $count = 0;
                                        $params = array($this->importQualID);
                                        $sql .= '(';
                                        foreach($units AS $possibleUnit)
                                        {
                                            $count++;
                                            $sql .= '?';
                                            if($count != count($units))
                                            {
                                                $sql .= ',';
                                            }
                                            $params[] = $possibleUnit->id;
                                        }
                                        $sql .= ')';
                                        $newqualUnit = $DB->get_records_sql($sql, $params);
                                        if($newqualUnit && count($newqualUnit) == 1)
                                        {
//                                            echo "Found one user unit <br />";
                                            $unit = end($newqualUnit);
                                        }
                                        else
                                        {
                                            $multipleUnitsFoundNonStudent[$userUnit->unitFamily." ".
                                                $userUnit->unitType." ".$userUnit->unitLevel. 
                                            " ".$userUnit->unitName." ".$userUnit->unitUniqueID] = $userUnit->unitFamily." ".
                                                $userUnit->unitType." ".$userUnit->unitLevel. 
                                            " ".$userUnit->unitName." ".$userUnit->unitUniqueID;
                                            $errored = true;
                                        }
                                    }
                                    else
                                    {
                                        //we just have the one
//                                        echo "Found one Unit <br />";
                                        $unit = end($units);
                                    }
                                }
                                else
                                {
                                    //find the unit by the id
                                    $unitID = $userUnit->unitid;
                                    $unit = $this->find_unit_by_id($unitID);
                                    if(!$unit)
                                    {
                                        $unitNotFound[$userUnit->unitFamily." ".
                                                $userUnit->unitType." ".$userUnit->unitLevel. 
                                            " ".$userUnit->unitName." ".$userUnit->unitUniqueID] = $userUnit->unitFamily." ".
                                                $userUnit->unitType." ".$userUnit->unitLevel. 
                                            " ".$userUnit->unitName." ".$userUnit->unitUniqueID;
                                        $errored = true;
                                    }
                                }
                                if($unit && $unit != '')
                                {
                                    //is the unit on the qual
                                    //
                                    $unitOnQual = $this->is_unit_on_qual($this->importQualID, $unit->id);
                                    if(!$unitOnQual)
                                    {
                                        $unitNotOnQual[$unit->id] = $unit->name;
                                        $errored = true;
                                    }
                                    
                                    //is the user on the unit?
                                    $userOnUnit = $this->is_user_on_unit($userUnit->userid, $unit->id, $this->importQualID);
                                    if(!$userOnUnit)
                                    {
                                        if(array_key_exists($userUnit->unitFamily." ".
                                                $userUnit->unitType." ".$userUnit->unitLevel. 
                                            " ".$userUnit->unitName." ".$userUnit->unitUniqueID, $usersNotOnUnit))
                                        {
                                            $usersArray = $usersNotOnUnit[$userUnit->unitFamily." ".
                                                $userUnit->unitType." ".$userUnit->unitLevel. 
                                            " ".$userUnit->unitName." ".$userUnit->unitUniqueID];
                                        }
                                        else
                                        {
                                            $usersArray = array();
                                        }
                                        $usersArray[$upUserName] = $upUserName;
                                        $usersNotOnUnit[$userUnit->unitFamily." ".
                                                $userUnit->unitType." ".$userUnit->unitLevel. 
                                            " ".$userUnit->unitName." ".$userUnit->unitUniqueID] = $usersArray;
                                        $errored = true;
                                    }                                    
                                    //now we want to get the value
                                    $value = null;
                                    if(isset($userUnit->userValue) && $userUnit->userValue != '')
                                    {
                                        $value = $this->find_value($userUnit->userValue, $this->get_type_of_qual($qualTypeID));
                                        if(!$value)
                                        {
                                            $valueNotFound[$userUnit->userValue] = $userUnit->userValue;
                                            $errored = true;
                                        }
                                    }
                                }
                            }
                            
                            //so we have all of the details. 
                            //now we want to update the database. 
                            if($unit && !$errored)
                            {
                                //then $userOnUnit
                                //are we overwriting? //are we ignoring the unitIDs?
                                //o we have other comments?
                                if(isset($userUnit->userComments) && $userUnit->userComments != '')
                                {
                                    //we have new comments
                                    //do we have old comments?
                                    if($userOnUnit->comments && !$this->overwriteData)
                                    {
                                        $summary->sheetno = 2;
                                        $summary->rowno = $rowNumber;
                                        $summary->user = $upUserName;
                                        $summary->warnings[] = 'Unit Comment Exists';
                                        $errored = true;
                                    } 
                                    $userOnUnit->comments = $userUnit->userComments;
                                }
                                
                                //now we need to do the value
                                if(isset($userUnit->userValue) && $userUnit->userValue != '' && $value)
                                {
                                    //then we have found the value object and we have a value to go in
                                    if($userOnUnit->bcgtvalueid && !$this->overwriteData)
                                    {
                                        $summary->sheetno = 2;
                                        $summary->rowno = $rowNumber;
                                        $summary->user = $upUserName;
                                        $summary->warnings[] = 'User Unit Value already exists';
                                        $errored = true;
                                    }
                                    $userOnUnit->bcgtvalueid = $value->id;
                                }                                
                                //now we do the userdefinedvalue
                                if(isset($userUnit->userDefinedValue) && $userUnit->userDefinedValue != '')
                                {
                                    if($userOnUnit->userdefinedvalue && !$this->overwriteData)
                                    {
                                        $summary->sheetno = 2;
                                        $summary->rowno = $rowNumber;
                                        $summary->user = $upUserName;
                                        $summary->warnings[] = 'User defined value already exists';
                                        $errored = true;
                                    }
                                }
                                
                                //now we run the update. 
                                //as we already now they are on the unit. 
                                if(!$errored)
                                {
                                    if($this->runDataChanges)
                                    {
                                        Unit::insert_user_unit_history_by_id($userOnUnit->id);
                                        $DB->update_record('block_bcgt_user_unit', $userOnUnit);
                                    }
                                }
                            }
                            //if we were ignoring the ids then we want to record what
                            //the temp unitid in the spreadsheet was with the 
                            //actual unitid it belongs to. 
                            if($unit)
                            {
                                $foundUnit[$userUnit->unitid] = $unit;
                            }
                            
                            
                            //are they on this qual?
                            if($errored)
                            {
                                $summaryErrors[] = $summary;
                            }
                        }
                    }
                    break;
                case "Criteria":
                    //this is the criteria:
                    
                    //find the user by the username -> ave we already found them from above?
                    //are they on this qual?
                    
                    //get the id of the unit from before. 
                    
                    //are we ignoring the id of the criteria? or are we adding the criteria?
                    
                    //can we find it by name?
                    
                    //Does this criteria exist for this unit? Do we want to create it?
                    
                    //can we find the value?
                    
                    //can we find who it was set by
                    
                    //can we find the grades or the breakdowns?
                    foreach ($worksheet->getRowIterator() as $row)
                    {
                        $foundValues = array();
                        $userCriteria = new stdClass();
                        $errored = false;
                        $warning = false;
                        $summary = new stdClass();
                        $summary->code = array();
                        $summary->errors = array();
                        $summary->warnings = array();
                        if(!is_null($row))
                        {
                            $rowNumber = $row->getRowIndex();
                            if($rowNumber == 1)
                            {
                                continue;
                            }
                            $cellIterator = $row->getCellIterator();
                            $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
                            // Loop Cells
                            $user = null;
                            foreach ($cellIterator as $cell)
                            {
                                $cellName = $cell->getCoordinate();
                                $cellData = (string)$cell->getCalculatedValue();

                                switch($cellName)
                                {
                                    case 'A'.$rowNumber:
                                        $upUserName = $cellData;
                                        if(array_key_exists($upUserName, $foundUsers))
                                        {
                                            $user = $foundUsers[$upUserName];
                                            $userCriteria->userid = $user->id;
                                        }
                                        else
                                        {
                                            $user = $this->find_user($upUserName);
                                            if($user)
                                            {
                                                $userCriteria->userid = $user->id;
                                                $foundUsers[$upUserName] = $user;
                                            }
                                            else
                                            {
                                                //then we cant find it so lets add it to the summary
                                                $usersNotFound[$upUserName] = $upUserName;
                                                $errored = true;
                                            }
                                        }
                                        break;
                                    case 'B'.$rowNumber:
                                        //its the unitID
                                        $userCriteria->unitid = (int)$cellData;
                                    case 'C'.$rowNumber:
                                        //its the criteriaid
                                        $userCriteria->criteriaid = (int)$cellData;
                                    case 'D'.$rowNumber:
                                        //its the criteriaName
                                        $userCriteria->criteriaName = $cellData;
                                    case 'E'.$rowNumber:
                                        //its the userValue
                                        $userCriteria->userValue = $cellData;
                                    case 'F'.$rowNumber:
                                        //its the setby
                                        $userCriteria->setBy = $cellData;
                                    case 'G'.$rowNumber:
                                        //its the dateset
                                        $userCriteria->dateSet = $cellData;
                                    case 'H'.$rowNumber:
                                        //updateby
                                        $userCriteria->updatedBy = $cellData;
                                    case 'I'.$rowNumber:
                                        //its the dateupdated
                                        if(!$cellData || $cellData == '')
                                        {
                                            $userCriteria->dateUpdated = $userCriteria->dateSet;
                                        }
                                        else
                                        {
                                            $userCriteria->dateUpdated = $cellData;
                                        }
                                    case 'J'.$rowNumber:
                                        //its the comments
                                        $userCriteria->comments = $cellData;
                                    case 'K'.$rowNumber:
                                        //its the userDefineValue
                                        $userCriteria->userDefinedValue = $cellData;
                                    case 'L'.$rowNumber:
                                        //its the targetdate
                                        $userCriteria->targetDate = $cellData;
                                    case 'M'.$rowNumber:
                                        //its the targetgrade
                                        $userCriteria->targetGrade = $cellData;
                                    case 'N'.$rowNumber:
                                        //its the targetbreakdown
                                        $userCriteria->targetBreakdown = $cellData;
                                    case 'O'.$rowNumber:
                                        //its the targetbreakdown
                                        $userCriteria->awardDate = $cellData;
                                    default:
                                        break;
                                }
                            }
                            if(isset($userCriteria->userid))
                            {
                                $dbUnitID = null;
                                $dbUnit = new stdClass();
                                $dbUnit->name = '';
                                $dbUnit->uniqueid = '';
                                //get the unit id from before
                                //if not set: error
                                if(array_key_exists($userCriteria->unitid, $foundUnit))
                                {
                                    $dbUnit = $foundUnit[$userCriteria->unitid];
                                    $dbUnitID = $dbUnit->id;
                                    //is the user doing this unit?
                                    $userOnUnit = $this->is_user_on_unit($userCriteria->userid, $dbUnitID, $this->importQualID);
                                    if(!$userOnUnit)
                                    {
                                        if(array_key_exists($userUnit->unitFamily." ".
                                                $userUnit->unitType." ".$userUnit->unitLevel. 
                                            " ".$userUnit->unitName." ".$userUnit->unitUniqueID, $usersNotOnUnit))
                                        {
                                            $usersArray = $usersNotOnUnit[$userUnit->unitFamily." ".
                                                $userUnit->unitType." ".$userUnit->unitLevel. 
                                            " ".$userUnit->unitName." ".$userUnit->unitUniqueID];
                                        }
                                        else
                                        {
                                            $usersArray = array();
                                        }
                                        $usersArray[$upUserName] = $upUserName;
                                        $usersNotOnUnit[$userUnit->unitFamily." ".
                                                $userUnit->unitType." ".$userUnit->unitLevel. 
                                            " ".$userUnit->unitName." ".$userUnit->unitUniqueID] = $usersArray;
                                        $errored = true;
                                    }
                                    
                                }
                                else
                                {
                                    $summary->sheetno = 3;
                                    $summary->rowno = $rowNumber;
                                    $summary->user = $upUserName;
                                    $summary->criteria = $userCriteria->criteriaName;
                                    $summary->errors[] = 'The Unit ID for this criteria was not in the unit list';
                                    $errored = true;
                                }
                                //find the criteria -> by name or by id?
                                //does this criteria actually exist?
                                if(!$this->ignoreIDs)
                                {
                                    //is it on this unit?
                                    $criteria = $this->find_criteria_by_id($userCriteria->criteriaid, $dbUnitID);
                                    if(!$criteria)
                                    {
                                        if(array_key_exists($dbUnit->name.' '.$dbUnit->uniqueid, $criteriaNotOnUnit))
                                        {
                                            $criteriaArray = $criteriaNotOnUnit[$dbUnit->name.' '.$dbUnit->uniqueid];
                                        }
                                        else
                                        {
                                            $criteriaArray = array();
                                        }
                                        $criteriaArray[$userCriteria->criteriaid] = $userCriteria->criteriaid;
                                        $criteriaNotOnUnit[$dbUnit->name.' '.$dbUnit->uniqueid] = $criteriaArray;
                                        $errored = true;
                                    }
                                }
                                else 
                                {
                                    //then we want to find the criteria by the name
                                    //is it on this unit?
                                    $criteria = $this->find_criteria($userCriteria->criteriaName, $dbUnitID);
                                    if(!$criteria)
                                    {
                                        if(array_key_exists($dbUnit->name.' '.$dbUnit->uniqueid, $criteriaNotOnUnit))
                                        {
                                            $criteriaArray = $criteriaNotOnUnit[$dbUnit->name.' '.$dbUnit->uniqueid];
                                        }
                                        else
                                        {
                                            $criteriaArray = array();
                                        }
                                        $criteriaArray[$userCriteria->criteriaName] = $userCriteria->criteriaName;
                                        $criteriaNotOnUnit[$dbUnit->name.' '.$dbUnit->uniqueid] = $criteriaArray;
                                        $errored = true;
                                    }
                                }
                                //find the value
                                //now we want to get the value
                                $value = null;
                                if(isset($userCriteria->userValue) && $userCriteria->userValue != '')
                                {
                                    if(array_key_exists($userCriteria->userValue, $foundValues))
                                    {
                                        $value = $foundValues[$userCriteria->userValue];
                                    }
                                    else
                                    {
                                        $value = $this->find_value($userCriteria->userValue, $qualTypeID);
                                        if(!$value)
                                        {
                                            $valueNotFound[$userCriteria->userValue] = $userCriteria->userValue;
                                        }
                                        else
                                        {
                                            $foundValues[$userCriteria->userValue] = $value;
                                        }
                                    }  
                                }
                                $setByUser = null;
                                //find the set by or updated by
                                if(isset($userCriteria->setBy) && $userCriteria->setBy != '')
                                {
                                    $setByUser = $this->find_user($userCriteria->setBy);
                                    if(!$setByUser)
                                    {
                                        $summary->sheetno = 3;
                                        $summary->rowno = $rowNumber;
                                        $summary->user = $upUserName;
                                        $summary->criteria = $userCriteria->criteriaName;
                                        $summary->errors[] = 'Set by user not found: '.$userCriteria->setBy;
                                        $warning = true;
                                    }
                                }
                                $updatedByUser = null;
                                if(isset($userCriteria->updatedBy) && $userCriteria->updatedBy != '')
                                {
                                    $updatedByUser = $this->find_user($userCriteria->updatedBy);
                                    if($updatedByUser)
                                    {
                                        $summary->sheetno = 3;
                                        $summary->rowno = $rowNumber;
                                        $summary->user = $upUserName;
                                        $summary->criteria = $userCriteria->criteriaName;
                                        $summary->errors[] = 'Updated by user not found: '.$userCriteria->setBy;
                                        $warning = true;
                                    }
                                }
                                //covert the dateset and dateupdated
                                $dateSet = null;
                                if(isset($userCriteria->dateSet) && $userCriteria->dateSet != '')
                                {
                                    $dateSet = strtotime($userCriteria->dateSet);
                                }
                                $dateUpdated = null;
                                if(isset($userCriteria->dateUpdated) && $userCriteria->dateUpdated != '')
                                {
                                    $dateUpdated = strtotime($userCriteria->dateUpdated);
                                }
                                $targetDate = null;
                                if(isset($userCriteria->targetDate) && $userCriteria->targetDate != '')
                                {
                                    $targetDate = strtotime($userCriteria->targetDate);
                                }
                                $awardDate = null;
                                if(isset($userCriteria->awardDate) && $userCriteria->awardDate != '')
                                {
                                    $awardDate = strtotime($userCriteria->awardDate);
                                }
                                $targetGrade = null;
                                //find the targetgrades or the breakdowsn. 
                                if(isset($userCriteria->targetGrade) && $userCriteria->targetGrade != '')
                                {
                                    //find it. 
                                    $targetGrade = $this->find_target_grade($targetGrade, $this->importQualID);
                                    if(!$targetGrade)
                                    {
                                        $summary->sheetno = 3;
                                        $summary->rowno = $rowNumber;
                                        $summary->user = $upUserName;
                                        $summary->criteria = $userCriteria->criteriaName;
                                        $summary->errors[] = 'Target Grade Not Found: '.$userCriteria->targetGrade;
                                        $warning = true;
                                    } 
                                }
                                $breakdown = null;
                                if(isset($userCriteria->targetBreakdown) && $userCriteria->targetBreakdown != '')
                                {
                                    $breakdown = $this->find_breakdown($targetGrade, $this->importQualID);
                                    if(!$breakdown)
                                    {
                                        $summary->sheetno = 3;
                                        $summary->rowno = $rowNumber;
                                        $summary->user = $upUserName;
                                        $summary->criteria = $userCriteria->criteriaName;
                                        $summary->errors[] = 'Target Breakdown Not Found: '.$userCriteria->targetBreakdown;
                                        $warning = true;
                                    }
                                }
                            }
                            
                            //update the record
                            //so we have all of the details. 
                            //now we want to update the database. 
                            if($criteria && !$errored)
                            {
                                //value
                                //comments
                                //userdefinedvalue
                                //targetdate
                                //targetgrade
                                //targetbreakdown
                                //awarddate
                                
                                //do we have a record for this user/critera before?
                                $userCriteriaDB = $this->find_users_criteria_record($userCriteria->userid, $criteria->id, $this->importQualID);
                                if($userCriteriaDB)
                                {
                                    $olderCantUpdate = false;
                                    if($userCriteriaDB->bcgtvalueid && $this->overwriteData && $this->overwriteIfNewer)
                                    {
                                        //then we need to check the date updated
                                        $dbDateUpdated = $userCriteriaDB->dateupdated;
                                        if(!$dbDateUpdated || $dateUpdated == -1)
                                        {
                                            $dbDateUpdated = $userCriteriaDB->dateset;
                                        }  
                                        if($dateUpdated && $dbDateUpdated && $dateUpdated < $dbDateUpdated)
                                        {
                                            $olderCantUpdate = true;
                                        }
                                    }
                                    
                                    
                                    if(isset($userCriteria->userValue) && $userCriteria->userValue != '' && $value)
                                    {
                                        if(($userCriteriaDB->bcgtvalueid && !$this->overwriteData))
                                        {
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User Criteria Value already exists : Stu='.$upUserName.' Uni='.$dbUnit->name.' : Crit='.$userCriteria->criteriaName;
                                            $errored = true;
                                        }
                                        if($userCriteriaDB->bcgtvalueid && $this->overwriteData && $this->overwriteIfNewer && $olderCantUpdate)
                                        {
                                            //then the data in the system is newer than that being imported
                                            //we are trying to overwrite if newer, so error.
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User Criteria Value already exists : Stu='.$upUserName.' Uni='.$dbUnit->name.' : Crit='.$userCriteria->criteriaName.' 
                                                AND its date updated ('.$dateUpdated.') is older than the system date updated/set ('.$dbDateUpdated.')';
                                            $errored = true;
                                        }
                                        $userCriteriaDB->bcgtvalueid = $value->id;
                                    }
                                    if(isset($userCriteria->comments) && $userCriteria->comments != '')
                                    {
                                        if($userCriteriaDB->comments && !$this->overwriteData )
                                        {
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User Criteria comments already exist : '.$upUserName.' Uni='.$dbUnit->name.' : '.$userCriteria->criteriaName;
                                            $errored = true;
                                        }
                                        if($userCriteriaDB->comments && $this->overwriteData && $this->overwriteIfNewer && $olderCantUpdate)
                                        {
                                            //then the data in the system is newer than that being imported
                                            //we are trying to overwrite if newer, so error.
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User Criteria comments already exist : '.$upUserName.' Uni='.$dbUnit->name.' : '.$userCriteria->criteriaName.' 
                                                AND its date updated ('.$dateUpdated.') is older than the system date updated/set ('.$dbDateUpdated.')';
                                            $errored = true;
                                        }
                                        $userCriteriaDB->comments = $userCriteria->comments;
                                    }
                                    if(isset($userCriteria->userDefinedValue) && $userCriteria->userDefinedValue != '')
                                    {
                                        if($userCriteriaDB->userdefinedvalue && !$this->overwriteData)
                                        {
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User defined value already exists : '.$upUserName.' Uni='.$dbUnit->name.' : '.$userCriteria->criteriaName;
                                            $errored = true;
                                        }
                                        if($userCriteriaDB->userdefinedvalue && $this->overwriteData && $this->overwriteIfNewer && $olderCantUpdate)
                                        {
                                            //then the data in the system is newer than that being imported
                                            //we are trying to overwrite if newer, so error.
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User defined value already exists : Stu='.$upUserName.' Uni='.$dbUnit->name.' : Crit='.$userCriteria->criteriaName.' 
                                                AND its date updated ('.$dateUpdated.') is older than the system date updated/set ('.$dbDateUpdated.')';
                                            $errored = true;
                                        }
                                        $userCriteriaDB->userdefinedvalue = $userCriteria->userDefinedValue;
                                    }
                                    if(isset($userCriteria->targetGrade) && $userCriteria->targetGrade != '' && $targetGrade)
                                    {
                                        if($userCriteriaDB->bcgttargetgradesid && !$this->overwriteData)
                                        {
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User target grade already exists : '.$upUserName.' Uni='.$dbUnit->name.' : '.$userCriteria->criteriaName;
                                            $errored = true;
                                        }
                                        if($userCriteriaDB->bcgttargetgradesid && $this->overwriteData && $this->overwriteIfNewer && $olderCantUpdate)
                                        {
                                            //then the data in the system is newer than that being imported
                                            //we are trying to overwrite if newer, so error.
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User target grade already exists : Stu='.$upUserName.' Uni='.$dbUnit->name.' : Crit='.$userCriteria->criteriaName.' 
                                                AND its date updated ('.$dateUpdated.') is older than the system date updated/set ('.$dbDateUpdated.')';
                                            $errored = true;
                                        }
                                        $userCriteriaDB->bcgttargetgradesid = $targetGrade->id;
                                    }
                                    if(isset($userCriteria->targetBreakdown) && $userCriteria->targetBreakdown != '' && $breakdown)
                                    {
                                        if($userCriteriaDB->bcgttargetbreakdownid && !$this->overwriteData)
                                        {
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User target breakdown already exists : '.$upUserName.' Uni='.$dbUnit->name.' : '.$userCriteria->criteriaName;
                                            $errored = true;
                                        }
                                        if($userCriteriaDB->bcgttargetbreakdownid && $this->overwriteData && $this->overwriteIfNewer && $olderCantUpdate)
                                        {
                                            //then the data in the system is newer than that being imported
                                            //we are trying to overwrite if newer, so error.
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User target breakdown already exists : Stu='.$upUserName.' Uni='.$dbUnit->name.' : Crit='.$userCriteria->criteriaName.' 
                                                AND its date updated ('.$dateUpdated.') is older than the system date updated/set ('.$dbDateUpdated.')';
                                            $errored = true;
                                        }
                                        $userCriteriaDB->bcgttargetbreakdownid = $breakdown->id;
                                    }
                                    if(isset($userCriteria->targetDate) && $userCriteria->targetDate != '' && $targetDate)
                                    {
                                        if($userCriteriaDB->targetdate && !$this->overwriteData)
                                        {
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User target date already exists : '.$upUserName.' Uni='.$dbUnit->name.' : '.$userCriteria->criteriaName;
                                            $errored = true;
                                        }
                                        if($userCriteriaDB->targetdate && $this->overwriteData && $this->overwriteIfNewer && $olderCantUpdate)
                                        {
                                            //then the data in the system is newer than that being imported
                                            //we are trying to overwrite if newer, so error.
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User target date already exists : Stu='.$upUserName.' Uni='.$dbUnit->name.' : Crit='.$userCriteria->criteriaName.' 
                                                AND its date updated ('.$dateUpdated.') is older than the system date updated/set ('.$dbDateUpdated.')';
                                            $errored = true;
                                        }
                                        $userCriteriaDB->targetdate = $targetDate;
                                    }
                                    if(isset($userCriteria->awardDate) && $userCriteria->awardDate != '' && $awardDate)
                                    {
                                        if($userCriteriaDB->awarddate && !$this->overwriteData)
                                        {
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User award date already exists : '.$upUserName.' Uni='.$dbUnit->name.' : '.$userCriteria->criteriaName;
                                            $errored = true;
                                        }
                                        if($userCriteriaDB->awarddate && $this->overwriteData && $this->overwriteIfNewer && $olderCantUpdate)
                                        {
                                            //then the data in the system is newer than that being imported
                                            //we are trying to overwrite if newer, so error.
                                            $summary->sheetno = 3;
                                            $summary->rowno = $rowNumber;
                                            $summary->user = $upUserName;
                                            $summary->warnings[] = 'User award date already exists : Stu='.$upUserName.' Uni='.$dbUnit->name.' : Crit='.$userCriteria->criteriaName.' 
                                                AND its date updated ('.$dateUpdated.') is older than the system date updated/set ('.$dbDateUpdated.')';
                                            $errored = true;
                                        }
                                        $userCriteriaDB->awarddate = $awardDate;
                                    }
                                    if($dateUpdated)
                                    {
                                        $userCriteriaDB->dateupdated = $dateUpdated;
                                    }
                                    else
                                    {
                                        $userCriteriaDB->dateupdated = time();
                                    }
                                    //update
                                    if(!$errored)
                                    {
                                        if($this->runDataChanges)
                                        {
                                            Criteria::insert_user_criteria_history_by_id($userCriteriaDB->id);
                                            $DB->update_record('block_bcgt_user_criteria', $userCriteriaDB);
                                        }
                                    }
                                }
                                else
                                {
                                    //insert
                                    //just do a straight insert
                                    $stdObj = new stdClass();
                                    $stdObj->userid = $userCriteria->userid;
                                    $stdObj->bcgtqualificationid = $this->importQualID;
                                    $stdObj->bcgtcriteriaid = $criteria->id;
                                    if(isset($userCriteria->userValue) && $userCriteria->userValue != '' && $value)
                                    {
                                        $stdObj->bcgtvalueid = $value->id;
                                    }
                                    else
                                    {
                                        $stdObj->bcgtvalueid = -1;
                                    }
                                    if($setByUser)
                                    {
                                        $stdObj->setbyuserid = $setByUser->id;
                                    }
                                    else
                                    {
                                        $stdObj->setbyuserid = $USER->id;
                                    }
                                    if($dateSet)
                                    {
                                        $stdObj->dateset = $dateSet;
                                    }
                                    else
                                    {
                                        $stdObj->dateset = time();
                                    }
                                    if($updatedByUser)
                                    {
                                        $stdObj->updatedbyuserid = $updatedByUser->id;
                                    }
                                    else
                                    {
                                        $stdObj->updatedbyuserid = $USER->id;
                                    }
                                    if($dateUpdated)
                                    {
                                        $stdObj->dateupdated = $dateUpdated;
                                    }
                                    else
                                    {
                                        $stdObj->dateupdated = time();
                                    }
                                    if(isset($userCriteria->comments) && $userCriteria->comments != '')
                                    {
                                        $stdObj->comments = $userCriteria->comments;
                                    }
                                    if(isset($userCriteria->userDefinedValue) && $userCriteria->userDefinedValue != '')
                                    {
                                        $stdObj->userdefinedvalue = $userCriteria->userDefinedValue;
                                    }
                                    if($targetDate)
                                    {
                                        $stdObj->targetdate = $targetDate;
                                    }
                                    if($targetGrade)
                                    {
                                        $stdObj->bcgttargetgradesid = $targetGrade->id;
                                    }
                                    if($breakdown)
                                    {
                                        $stdObj->bcgttargetbreakdownid = $breakdown->id;
                                    }
                                    if($awardDate)
                                    {
                                        $stdObj->awarddate = $awardDate;
                                    }
                                    if($this->runDataChanges)
                                    {
                                        $DB->insert_record('block_bcgt_user_criteria', $stdObj);
                                    }
                                }
                                //now we run the update. 
                                //as we already now they are on the unit. 
                            }

                            if($errored)
                            {
                                $summaryErrors[] = $summary;
                            }
                            
                        }//end if row isnt null
                    }//end for each row.
                    break;
                default:
                     break;
            }//end switch on workbook
        }//end for each on workbook. 
        $retval = new stdClass();
        $retval->users = $foundUsers;
        $retval->errors = $summaryErrors;
        $retval->unitsNotFound = $unitNotFound;
        $retval->unitsNotOnQual = $unitNotOnQual;
        $retval->usersNotFound = $usersNotFound;
        $retval->valuesNotFound = $valueNotFound;
        $retval->userNotOnUnit = $usersNotOnUnit;
        $retval->criteriaNotOnUnit = $criteriaNotOnUnit;
        $retval->multipleUnitsFoundNonStudent = $multipleUnitsFoundNonStudent;
        
        return $retval;
    }
    
    private function process_user_import($process)
    {
        
    }
    
    public function display_import_options()
    {
        global $DB;
        $retval = '<input type="hidden" name="sub" value="'.$this->importMethod.'"/>';
        $retval .= '<table>';
        $retval .= '<tr><td><label for="rundatachanges">'.get_string('runactualdatachanges', 'block_bcgt').' : </label></td>';
        $retval .= '<td><input type="checkbox" name="rundatachanges"/></td>';
        $retval .= '<td><span class="description">('.get_string('runactualdatachangesdesc', 'block_bcgt').')</span></td></tr>';
        $retval .= '<tr><td><label for="option1">'.get_string('udoverwrightdata', 'block_bcgt').' : </label></td>';
        $retval .= '<td><input type="checkbox" name="option1"/></td>';
        $retval .= '<td><span class="description">('.get_string('udoverwrightdatadesc', 'block_bcgt').')</span></td></tr>';
        $retval .= '<tr><td><label for="option1">'.get_string('overwriteifnewer', 'block_bcgt').' : </label></td>';
        $retval .= '<td><input type="checkbox" name="overwritenewer"/></td>';
        $retval .= '<td><span class="description">('.get_string('overwriteifnewerdesc', 'block_bcgt').')</span></td></tr>';
        $retval .= '<tr><td><label for="option2">'.get_string('exportfromthissystem', 'block_bcgt').' : </label></td>';
        $retval .= '<td><input type="checkbox" checked="checked" name="option2"/></td>';
        $retval .= '<td><span class="description">('.get_string('exportfromthissystemdesc', 'block_bcgt').')</span></td></tr>';
//        $retval .= '<tr><td><label for="adduserunit">'.get_string('addusertounit', 'block_bcgt').' : </label></td>';
//        $retval .= '<td><input type="checkbox" name="adduserunit"/></td>';
//        $retval .= '<td><span class="description">('.get_string('addusertounitdesc', 'block_bcgt').')</span></td></tr>';
        $retval .= '</table>';
        switch($this->importMethod)
        {
            case"all":
                //no extra options
                break;
            case"qual":
                //choose qualification
                $retval .= '<label for="qualimport"> '.get_string('qual', 'block_bcgt').' : </label>';
                $retval .= '<select name="qualimport">';
                $retval .= '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>';
                $quals = search_qualification();
                if($quals)
                {
                    foreach($quals AS $qual)
                    {
                        $selected = '';
                        if($this->importQualID == $qual->id)
                        {
                            $selected = 'selected';
                        }
                        $retval .= '<option '.$selected.' value="'.$qual->id.'">'.bcgt_get_qualification_display_name($qual).'</option>';
                    }
                }
                $retval .= '</select><br />';
                if($this->importQualID)
                {
                   $retval .= '<h3>'.get_string('unitsonqual', 'block_bcgt').'</h3>';
                   $loadParams = new stdClass();
                   $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
                   $qualification = Qualification::get_qualification_class_id($this->importQualID, $loadParams);
                   if($qualification)
                   {
                       $retval .= '<table>';
                       $retval .= '<thead><tr>';
                       $retval .= '<th>'.get_string('type', 'block_bcgt').'</th>';
                       $retval .= '<th>'.get_string('level', 'block_bcgt').'</th>';
                       $retval .= '<th>'.get_string('name', 'block_bcgt').'</th>';
                       $retval .= '<th>'.get_string('uniqueid', 'block_bcgt').'</th>';
                       $retval .='</tr></thead><tbody>';
                       foreach($qualification->get_units() AS $unit)
                       {
                           $retval .= '<tr>';
                           $unitLevel = $unit->get_level();
                           $level = '';
                           if($unitLevel)
                           {
                               $level = $unitLevel->get_level();
                           }
                           $retval .= '<td>'.$unit->get_type_name().'</td>';
                           $retval .= '<td>'.$level.'</td>';
                           $retval .= '<td>'.$unit->get_name().'</td>';
                           $retval .= '<td>'.$unit->get_uniqueID().'</td>';
                           $retval .= '</tr>';
                       }
                       $retval .= '</tbody></table>';
                   }
                }
                break;
            case"user":
                //choose user
                //choose qualification
                $retval .= '<label for="usersearch"> '.get_string('user', 'block_bcgt').' : </label>';
                $retval .= '<input type="text" name="usersearch" value="'.$this->usersearch.'"/>';
                $retval .= '<input type="submit" name="searchuser" value="'.get_string('search','block_bcgt').'"/><br />';
                if($this->usersearch != '')
                {
                    $retval .= '<select name="userimport">';
                    $retval .= '<option value="-1">'.get_string('pleaseselect','block_bcgt').'</option>';
                    $users = $DB->get_records_sql('SELECT * FROM {user} WHERE username LIKE ? '.
                            'OR firstname LIKE ? OR lastname LIKE ? OR email LIKE ?', 
                            array('%'.$this->usersearch.'%','%'.$this->usersearch.'%','%'.$this->usersearch.'%','%'.$this->usersearch.'%'));
                    if($users)
                    {
                        foreach($users AS $user)
                        {
                            $selected = '';
                            if($this->importUserID == $user->id)
                            {
                                $selected = 'selected';
                            }
                            $retval .= '<option '.$selected.' value="'.$user->id.'">'.
                                    $user->username.' : '.$user->firstname.' '.
                                    $user->lastname.'</option>';
                        }
                    }
                    $retval .= '</select>';
                }
                $retval .= '<br />';
                
                break;
        }
        $retval .= '<br />';
        
        return $retval;
    }
    
    private function find_qual($family, $type, $subtype, $level, $name)
    {
        global $DB;
        $sql = "SELECT distinct(qual.id) as id, targetqual.id AS bcgttargetqualid, type.id as typeid, 
            type.type, subtype.id AS subtypeid, family.id AS familyid, level.id as levelid, qual.name 
            FROM {block_bcgt_qualification} qual 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
            JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
            WHERE family.family = ? AND type.type = ? AND subtype.subtype = ? AND level.trackinglevel = ? 
            AND qual.name = ?";
        $records = $DB->get_records_sql($sql, array($family, $type, $subtype, $level, $name));
        if(count($records) > 1)
        {
//            print_object($records);
        }
        return $records;
    }
    
    private function find_unit_by_id($unitID)
    {
        global $DB;
        $sql = "Select * FROM {block_bcgt_unit} WHERE id = ?";
        return $DB->get_record_sql($sql, array($unitID));
    }
    
    private function find_unit($family, $type, $level = null, $name = null, $uniqueID = null, $qualID = -1, $userID = -1)
    {
        global $DB;
        $sql = "SELECT distinct(unit.id) as id, unit.name, unit.uniqueid FROM {block_bcgt_unit} unit 
            JOIN {block_bcgt_type} type ON unit.bcgttypeid = type.id 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
            LEFT OUTER JOIN {block_bcgt_level} level ON level.id = unit.bcgtlevelid";
        $params = array();
        if($qualID != -1)
        {
            $sql .= " JOIN {block_bcgt_qual_units} qualunits ON qualunits.bcgtunitid = unit.id";
        }
        if($userID != -1)
        {
            $sql .= " JOIN {block_bcgt_user_unit} userunit ON userunit.bcgtunitid = unit.id AND userunit.userid = ?";
            $params[] = $userID;
            if($qualID != -1)
            {
                " AND userunit.bcgtqualificationid = ?";
                $params[] = $qualID;
            }
        }
        $params[] = $family;
        $params[] = $type;
        $sql .= ' WHERE family.family = ? AND type.type = ?';
        if($level)
        {
            $sql .= ' AND level.trackinglevel = ?';
            $params[] = $level;
        }
        if($name)
        {
            $params[] = $name;
            $sql .= ' AND unit.name = ?';
        }
        if($uniqueID)
        {
            $params[] = $uniqueID;
            $sql .= ' AND unit.uniqueid = ?';
        }
        if($qualID != -1)
        {
            $sql .= ' AND qualunits.bcgtqualificationid = ?';
            $params[] = $qualID;
        }
        return $DB->get_records_sql($sql, $params);
        
    }
    
    private function find_criteria_by_id($criteriaID, $unitID)
    {
        global $DB;
        $sql = "SELECT criteria.* FROM {block_bcgt_criteria} criteria 
            WHERE id= ? AND bcgtunitid = ?";
        return $DB->get_record_sql($sql, array($criteriaID, $unitID));
    }
    
    private function find_criteria($name, $unitID)
    {
        global $DB;
        $sql = "SELECT criteria.* FROM {block_bcgt_criteria} criteria 
            WHERE criteria.name = ? AND criteria.bcgtunitid = ?";
        return $DB->get_record_sql($sql, array($name, $unitID));
    }
    
    private function find_value($shortvalue, $typeID)
    {
        //need to get the family:
        global $DB;
        $sql = "SELECT family.id, family.family FROM {block_bcgt_type_family} family 
            JOIN {block_bcgt_type} type ON type.bcgttypefamilyid = family.id 
            WHERE type.id = ?";
        $params = array($typeID);
        $familyDB = $DB->get_record_sql($sql, $params);
        if($familyDB)
        {
            $family = $familyDB->family;
            
            switch($family)
            {
                case "BTEC":
                    //we need the parent type id which is 2.
                    $params = array($shortvalue, 2);
                    break;
                default:  
                    $params = array($shortvalue, $typeID);
                    break;
            }
        }
        $sql = "SELECT value.* FROM {block_bcgt_value} value 
        WHERE value.shortvalue = ? AND value.bcgttypeid = ?";
        return $DB->get_record_sql($sql, $params);
    }
    
    private function find_user($username)
    {
        global $DB;
        $sql = "SELECT * FROM {user} WHERE username = ?";
        return $DB->get_record_sql($sql, array($username));
    }
    
    private function get_type_of_unit($unitID)
    {
        global $DB;
        $sql = "SELECT type.id FROM {block_bcgt_type} type 
            JOIN {block_bcgt_unit} unit ON unit.bcgttypeid = type.id 
            WHERE unit.id = ?";
        $id = $DB->get_record_sql($sql, array($unitID));
        if($id)
        {
            return $id->id;
        }
        return -1;
    }
    
    private function find_award($award, $typeID)
    {
        global $DB;
        $sql = "SELECT award.* FROM {block_bcgt_type_award} award 
            WHERE award.award = ? AND award.bcgttypeid = ?";
        return $DB->get_record_sql($sql, array($award, $typeID));
    }
    
    private function find_breakdown($targetGrade, $qualID)
    {
        global $DB;
        $sql = "SELECT breakdown.* FROM {block_bcgt_target_breakdown} breakdown 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = breakdown.bcgttargetqualid 
            JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetqual.id
            WHERE breakdown.targetgrade = ? AND bqual.id = ?";
        return $DB->get_record_sql($sql, array($targetGrade, $qualID));
    }
    
    private function find_target_grade($targetGrade, $qualID)
    {
        global $DB;
        $sql = "SELECT grade.* FROM {block_bcgt_target_grades} grade 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = grade.bcgttargetqualid 
            JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetqual.id
            WHERE grade.grade = ? AND qual.id = ?";
        return $DB->get_record_sql($sql, array($targetGrade, $qualID));
    }
    
    private function find_users_criteria_record($studentID, $criteriaID, $qualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_criteria} WHERE 
            userid = ? AND bcgtcriteriaid = ? AND bcgtqualificationid = ?";
        return $DB->get_record_sql($sql, array($studentID, $criteriaID, $qualID));
    }
    
    private function find_users_unit_record($studentID, $unitID, $qualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_unit} WHERE 
            userid = ? AND bcgtunitid = ? AND bcgtqualificationid = ?";
        return $DB->get_record_sql($sql, array($studentID, $unitID, $qualID));
    }
    
    private function find_users_award_record($studentID, $qualID)
    {
        global $DB;
        $sql = "SELECT distinct(useraward.id), useraward.courseid, 
            useraward.bcgtqualificationid, useraward.userid, useraward.bcgtbreakdownid, 
            useraward.bcgttargetgradesid, useraward.type, useraward.warning, useraward.dateupdated, useraward.overallgrade
            FROM {block_bcgt_user_award} useraward WHERE 
            userid = ? AND bcgtqualificationid = ?";
        return $DB->get_records_sql($sql, array($studentID, $qualID));
    }
    
    private function get_type_of_qual($qualID)
    {        
        global $DB;
        $sql = "SELECT type.id FROM {block_bcgt_type} type 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.bcgttypeid = type.id 
            JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetqual.id 
            WHERE qual.id = ?";
        $typeID = $DB->get_record_sql($sql, array($qualID));
        if($typeID)
        {
            return $typeID->id;
        }
        return -1;
    }
    
    private function is_user_on_qual($userID, $qualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_qual} WHERE userid = ? and bcgtqualificationid = ?";
        return $DB->get_record_sql($sql, array($userID, $qualID));
    }
    
    private function is_unit_on_qual($qualID, $unitID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_qual_units} WHERE bcgtqualificationid = ? AND bcgtunitid = ?";
        return $DB->get_record_sql($sql, array($qualID, $unitID));
    }
    
    private function is_user_on_unit($userID, $unitID, $qualID)
    {
        global $DB;
        $sql = "SELECT * FROM {block_bcgt_user_unit} WHERE userid = ? and bcgtunitid = ? AND bcgtqualificationid = ?";
        return $DB->get_record_sql($sql, array($userID, $unitID, $qualID));
    }
}

?>
