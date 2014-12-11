<?php
class RegisterGroupImport {
    
    
    protected $importmissinguser;
    protected $success;
    
    
    public function get_description()
    {
        
        return get_string('registergroupimportdesc', 'block_bcgt');
        
    }
    
    public function has_multiple()
    {
        return false;
    }
    
    public function get_headers()
    {
        
        $retval = "Username,LearnerTutor,RegGrpID,RegGrpName,RegGrpStartDate,RegGrpEndDate,RegGrpStartTime,RegGrpEndTime";
        return explode(',', $retval);
        
    }
    
    public function get_examples()
    {
       
        return "88888888,L,76123,Unit 23: Project,21/09/2014,30/06/2015,09:00,11:30<br>
                bsmith,T,76456,U21 Materials Engineering,22/09/2014,30/06/2015,15:00,17:00";
        
    }
    
    public function display_import_options()
    {
        $retval = '<table>';        
        $retval .= '<tr><td><label for="option4">'.get_string('plcreatemissinguser', 'block_bcgt').' : </label></td>';
        $retval .= '<td><input type="checkbox" name="option4"/></td>';
        $retval .= '<td><span class="description">('.get_string('plcreatemissinguserdesc', 'block_bcgt').')</span></td></tr>';
        $retval .= '</table>';
        return $retval;
    }
    
    public function get_file_names()
    {
        return 'registergroups.csv';
    }
    
    public function process_import_csv($csvFile, $process = false)
    {
        
        global $DB;
        
        $usersNotFound = 0;
        $successCount = 0;
        
        $rowNum = 1;
        
        $CSV = fopen($csvFile, 'r');
        while(($row = fgetcsv($CSV)) !== false) {
             
            if($rowNum != 1)
            {
                
                $username = $row[0];
                $type = $row[1];
                $regID = $row[2];
                $regName = $row[3];
                $sDate = $row[4];
                $eDate = $row[5];
                $sTime = $row[6];
                $eTime = $row[7];
                
                // All cols must be filled in
                foreach($row as $col)
                {
                    if (empty($col))
                    {
                        continue 2;
                    }
                }

                // Check if user exists
                $user = $DB->get_record("user", array("username" => $username, "deleted" => 0));
                if (!$user)
                {
                    
                    // Are we creating missing users?
                    if ($this->importmissinguser)
                    {
                        
                        $obj = new stdClass();
                        $obj->firstname = $username;
                        $obj->lastname = $username;
                        $obj->username = $username;
                        $obj->idnumber = $username;
                        $obj->email = $username . '@' . $_SERVER['HTTP_HOST'];
                        $obj->country = 0;
                        $obj->city = 'unknown';
                        $id = $DB->insert_record("user", $obj);
                        $user = $DB->get_record("user", array("id" => $id));
                        
                    }
                    else
                    {
                        $usersNotFound++;
                        continue;
                    }
                    
                }
                
                // Type must be L or T
                if ($type != 'L' && $type != 'T'){
                    continue;
                }
                
                // Rest of it is up to them to get right
                
                // Check if we have a register event of this
                $check = $DB->get_record("block_bcgt_register_groups", array("recordid" => $regID));
                if ($check)
                {
                    // Update
                    $regRecordID = $check->id;
                    
                    $check->name = $regName;
                    $check->startdate = $sDate;
                    $check->enddate = $eDate;
                    $check->starttime = $sTime;
                    $check->endtime = $eTime;
                    $DB->update_record("block_bcgt_register_groups", $check);
                }
                else
                {
                    
                    $obj = new stdClass();
                    $obj->recordid = $regID;
                    $obj->name = $regName;
                    $obj->startdate = $sDate;
                    $obj->enddate = $eDate;
                    $obj->starttime = $sTime;
                    $obj->endtime = $eTime;
                    $regRecordID = $DB->insert_record("block_bcgt_register_groups", $obj);
                    
                }
                
                // Is user already linked to this?
                $check = $DB->get_record("block_bcgt_user_reg_groups", array("registergroupid" => $regRecordID, "userid" => $user->id));
                if ($check)
                {
                    
                    // Update
                    $check->type = $type;
                    $DB->update_record("block_bcgt_user_reg_groups", $check);
                    
                }
                else
                {
                    
                    // Insert
                    $obj = new stdClass();
                    $obj->userid = $user->id;
                    $obj->registergroupid = $regRecordID;
                    $obj->type = $type;
                    $DB->insert_record("block_bcgt_user_reg_groups", $obj);
                    
                }
                
                $successCount++;
                
            }
            
            $rowNum++;
            
         }
         
         $success = true;
         
         // If we're not creating missing users and we have missing users, false
         if (!$this->importmissinguser && $usersNotFound > 0){
             $success = false;
         }
         
         $this->success = $success;
         
         $summary = new stdClass();
         $summary->usersnotfound = $usersNotFound;
         $summary->successCount = $successCount;
         $this->summary = $summary;
        
         
    }
    
    public function get_submitted_import_options(){
        
        if(isset($_POST['option4']))
        {
            $this->importmissinguser = true;
        }
        
    }
    
    public function display_summary()
    {
        
        $retval = '<p><ul>';
        if($this->summary)
        {
            $retval .= '<li>'.get_string('recordsinserted','block_bcgt').' : '.$this->summary->successCount.'</li>';
            if(!$this->success)
            {
                $retval .= '<li>'.get_string('plimportsum2','block_bcgt').' : '.count($this->summary->usersnotfound).'</li>';
            }
        } 
        $retval .= '</ul></p>';
        return $retval;
        
    }
    
    public function was_success()
    {
        return $this->success;
    }
    
    
}
