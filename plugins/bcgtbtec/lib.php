<?php
/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */
    global $CFG;
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECQualification.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECUnit.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECCriteria.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECSubType.class.php');
    require_once($CFG->dirroot.'/lib/upgradelib.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECHigherQualification.class.php');    
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECLowerQualification.class.php');  
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFoundationQualification.class.php');  
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECHigherCriteria.class.php');    
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECLowerCriteria.class.php'); 
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFoundationUnit.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECHigherUnit.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECLowerUnit.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFoundationCriteria.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFirst2013Qualification.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFirst2013Unit.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFirst2013Criteria.class.php');
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/sorters/BTECCriteriaSorter.class.php');

    
    function run_btec_initial_import()
    {
        upgrade_set_timeout(10000);
        echo "Running initial Import<br />";
        
//        echo "TODO: REMOVE THIS RETURN TRUE WHEN NOT IN DEV";
//        return true;
        
        //first create the quals
        //then create the units
        //then create the criteria
        //then add the units to the quals. 
        global $CFG;
        $count = 1;
        $qualsCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/data/quals.csv', 'r');
        $qualsAfterInsert = array();
        echo "Inserting Initial Quals<br />";
        while(($qual = fgetcsv($qualsCSV)) !== false) {
            if($count != 1)
            {
                $qualRecord = insert_initial_qual($qual);
                if($qualRecord)
                {
                    //Old qual id as the key, new qual id is in the object. 
                    $qualsAfterInsert[$qual[0]] = $qualRecord; 
                }
            }
            $count++;
        }  
        echo "Done: $count quals inserted<br />";
        fclose($qualsCSV);
        $count = 1;
        $unitsCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/data/units.csv', 'r');
        $unitsAfterInsert = array();
        echo "Inserting Initial Units<br />";
        while(($unit = fgetcsv($unitsCSV)) !== false) {
            if($count != 1)
            {
                $unitRecord = insert_initial_unit($unit);
                if($unitRecord)
                {
                    //Old unit id as the key, new unit id is in the object. 
                    $unitsAfterInsert[$unit[0]] = $unitRecord; 
                }
            }
//            else
//            {
//                print_object($unit);
//            }
            $count++;
        }
        echo "Done<br />";
        $criteriaAfterInsert = array();
        echo "Done: $count units inserted<br />";
        fclose($unitsCSV);
        $count = 1;
        $criteriaCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/data/criteria.csv', 'r');
        echo "Inserting Initial Criteria - Warning: This can take quite a while as it will do roughly 11,000<br />";
        while(($criteria = fgetcsv($criteriaCSV)) !== false) {
            if($count != 1)
            {
                $citeriaRecord = insert_initial_criteria($criteria, $unitsAfterInsert, $qualsAfterInsert); 
                if($citeriaRecord)
                {
                    $criteriaAfterInsert[$criteria[0]] = $citeriaRecord; 
                }
            } 
            $count++;
        }
        echo "Done: $count criteria inserted<br />";
        fclose($criteriaCSV);
        
        $qualUnitsCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/data/qualsunits.csv', 'r');
        $qualUnits = fgetcsv($qualUnitsCSV); #mr potatoe head
        $count = 1;
        echo "Inserting Initial Quals Units<br />";
        while(($qualUnit = fgetcsv($qualUnitsCSV)) !== false) {
            if($count != 1)
            {
                insert_initial_qual_unit($qualUnit, $unitsAfterInsert, $qualsAfterInsert);
            }
            $count++;
        }
        echo "Done: $count units put onto quals inserted<br />";
        fclose($qualUnitsCSV);

//        //then we do the teachers
//        $teachersAfterInsert = array();
//        $teachersCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/data/teachers.csv', 'r');
//        $teachers = fgetcsv($teachersCSV);
//        $count = 1;
//        echo "Inserting Teachers<br />";
//        while(($teacher = fgetcsv($teachersCSV)) !== false) {
//            if($count != 1)
//            {
//                $newID = insert_new_user($teacher);
//                $teachersAfterInsert[$teacher[0]] = $newID;
//            }
//            $count++;
//        }
//        echo "Done<br />";
//        fclose($teachersCSV);
//        
//        
//        //then we do the students
//        $studentsAfterInsert = array();
//        $studentsCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/data/students.csv', 'r');
//        $students = fgetcsv($studentsCSV);
//        $count = 1;
//        echo "Inserting Students<br />";
//        while(($student = fgetcsv($studentsCSV)) !== false) {
//            if($count != 1)
//            {
//                $newID = insert_new_user($student);
//                $studentsAfterInsert[$student[0]] = $newID;
//            }
//            $count++;
//        }
//        echo "Done<br />";
//        fclose($studentsCSV);
//        
//        //then we do the user_quals
//        $userQualsCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/data/user_qual.csv', 'r');
//        $count = 1;
//        echo "Inserting User Quals<br />";
//        while(($userQual = fgetcsv($userQualsCSV)) !== false) {
//            if($count != 1)
//            {
//                insert_new_user_qual($userQual, $qualsAfterInsert, $studentsAfterInsert);
//            }
//            $count++;
//        }
//        echo "Done<br />";
//        fclose($userQualsCSV);
//        
//        //then we do the user_units which will also do user_quals table
//        $userUnitsCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/data/user_unit.csv', 'r');
//        $count = 1;
//        $values = array();
//        $awards = array();
//        echo "Inserting User Units<br />";
//        while(($userUnit = fgetcsv($userUnitsCSV)) !== false) {
//            if($count != 1)
//            {
//                insert_new_user_unit($userUnit, $qualsAfterInsert, $unitsAfterInsert, $studentsAfterInsert, $teachersAfterInsert);
//            }
//            $count++;
//        }
//        echo "Done<br />";
//        fclose($userUnitsCSV);
//        
//        
//        
//        //then we do the user_criteria
//        $userCriteriaCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/data/user_criteria.csv', 'r');
//        $count = 1;
//        $values = array();
//        $awards = array();
//        echo "Inserting User Criteria<br />";
//        while(($userCriteria = fgetcsv($userCriteriaCSV)) !== false) {
//            if($count != 1)
//            {
//                insert_new_user_criteria($userCriteria, $qualsAfterInsert, $criteriaAfterInsert, $studentsAfterInsert, $teachersAfterInsert);
//            }
//            $count++;
//        }
//        echo "Done<br />";
//        fclose($userCriteriaCSV);
//        
//        //then we do the user_awards
//            //will need to find the user, award, qual
//        $userAwardsCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/data/user_award.csv', 'r');
//        $count = 1;
//        echo "Inserting User Awards<br />";
//        while(($userAward = fgetcsv($userAwardsCSV)) !== false) {
//            if($count != 1)
//            {
//                insert_new_user_award($userAward, $qualsAfterInsert, $studentsAfterInsert);
//            }
//            $count++;
//        }
//        echo "Done<br />";
//        fclose($userAwardsCSV);
            
        global $DB;
//        //then courses
//            //loop through and just find the new id of the course
//        $coursesCSV = fopen($CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec/data/courses.csv', 'r');
//        $count = 1;
//        $coursesAfterImport = array();
//        echo "Collating Courses<br />";
//        while(($course = fgetcsv($coursesCSV)) !== false) {
//            if($count != 1)
//            {
//                $courseRecord = $DB->get_record_sql('SELECT * FROM {course} WHERE shortname = ?', 
//                        array($course[2]));
//                if($courseRecord)
//                {
//                    $coursesAfterImport[$course[0]] = $courseRecord->id;
//                }
//                else
//                {
//                    echo "Could not find course $course[2]<br />";
//                }
//            }
//            $count++;
//        }
//        echo "Done<br />";
//        fclose($coursesCSV);
//        
//        //then course enrolments
//            //find the new id of the staff member.
//            //find the new if of the course. 
//            //add them to it. 
//        $courseTeachersCSV = fopen($CFG->wwwroot.'/blocks/bcgt/plugins/bcgtbtec/data/user_course.csv', 'r');
//        $count = 1;
//        echo "Enrolling Users<br />";   
//         
//        while(($courseUser = fgetcsv($courseTeachersCSV)) !== false) {
//            if($count != 1)
//            {
//                enrol_new_staff($courseUser, $coursesAfterImport, $teachersAfterInsert);
//            }
//            $count++;
//        }
//        echo "Done<br />";
//        fclose($courseTeachersCSV);
       echo "Finished<br />";
    }
    
    function enrol_new_staff($courseUser, $coursesAfterImport, $teachersAfterInsert)
    {
        
        global $DB, $enrol_manual;
        $enrol_manual = enrol_get_plugin('manual');
        if(array_key_exists($courseUser[0], $coursesAfterImport))
        {
            $newCourseID = $coursesAfterImport[$courseUser[0]];
            if(array_key_exists($courseUser[1], $teachersAfterInsert))
            {
                $newStaffID = $teachersAfterInsert[$courseUser[1]];
                // Add enrolment method to course if it doesn't exist
                $instance = $DB->get_record("enrol", array("courseid" => $newCourseID, "enrol" => "manual"), "*", IGNORE_MULTIPLE);
                if (!$instance){
                    
                    $course = $DB->get_record("course", array("id" => $newCourseID));
                    
                    $fields = array(
                        'roleid'          => 3);
                    $enrol_manual->add_instance($course, $fields);
                    $instance = $DB->get_record("enrol", array("courseid" => $newCourseID, "enrol" => "manual"), "*", IGNORE_MULTIPLE);
                }
                
                if ($enrol_manual){
                    $enrol_manual->enrol_user($instance, $newStaffID, 3);
                }
            }
        }
    }
    
    function insert_initial_qual($qual)
    {
        global $DB;
        $targetqualID = get_target_qual(-1, $qual[2], -1, $qual[4], -1, $qual[6]);
        if($targetqualID)
        {
            $qualRecord = new stdClass();
            $qualRecord->name = $qual[7];
            $qualRecord->bcgttargetqualid = $targetqualID;
            $qualRecord->credits = $qual[8];
            $qualRecord->code = $qual[9];
            $qualRecord->additionalname = '';
            
            $newID = $DB->insert_record('block_bcgt_qualification', $qualRecord);
            $qualRecord->id = $newID;
            return $qualRecord;
        }
        return false;  
    }
    
    function insert_new_user_qual($userQual, $qualsAfterInsert, $studentsAfterInsert)
    {
        global $DB;
        if(array_key_exists($userQual[2], $qualsAfterInsert))
        {
            $newQualID = $qualsAfterInsert[$userQual[2]];
            if(array_key_exists($userQual[1], $studentsAfterInsert))
            {
                $newUserID = $studentsAfterInsert[$userQual[1]];
                $record = new stdClass();
                $record->bcgtqualificationid = $newQualID->id;
                $record->userid = $newUserID;
                $record->roleid = 5;
                $record->comments = $userQual[3]; 
                return $DB->insert_record('block_bcgt_user_qual', $record);
            }
            else
            {
                echo "Couldnt find user: $userQual[1]";
            }
        }
        else
        {
            echo "Couldnt find qual : $userQual[2]";
        }
        echo "Could not insert : ".print_object($userQual);
    }
    
    function insert_new_user_unit($userUnit, $qualsAfterInsert, $unitsAfterInsert, $studentsAfterInsert, $teachersAfterInsert)
    {
        global $DB, $values, $awards;
        if(array_key_exists($userUnit[2], $qualsAfterInsert))
        {
            $newQualID = $qualsAfterInsert[$userUnit[2]];
            if(array_key_exists($userUnit[1], $studentsAfterInsert))
            {
                $newUserID = $studentsAfterInsert[$userUnit[1]];
                if(array_key_exists($userUnit[3], $unitsAfterInsert))
                {
                    $newUnitID = $unitsAfterInsert[$userUnit[3]];
                    //need to find the value
                    $valueID = -1;
                    if($userUnit[8])
                    {
                        if(!array_key_exists($userUnit[8], $values))
                        {
                            //then go and get the value
                            $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? 
                                AND shortvalue = ?";
                            $record = $DB->get_record_sql($sql, array(BTECQualification::ID, $userUnit[8]));
                            if($record)
                            {
                                $valueID = $record->id;
                                $values[$userUnit[8]] = $valueID;
                            }
                        }
                        else
                        {
                            $valueID = $values[$userUnit[8]];
                        }
                    }
                    
                    $awardID = -1;
                    if($userUnit[4])
                    {
                        //need to find the award
                        if(!array_key_exists($userUnit[4], $awards))
                        {
                            //then go and get the award
                            $sql = "SELECT * FROM {block_bcgt_type_award} WHERE 
                                bcgttypeid = ? 
                                AND award = ?";
                            $record = $DB->get_record_sql($sql, array(BTECQualification::ID, $userUnit[4]));
                            if($record)
                            {
                                $awardID = $record->id;
                                $awards[$userUnit[4]] = $awardID;
                            }

                        }
                        else {
                            $awardID = $awards[$userUnit[4]];
                        }
                    }
                    
                    //now insert it
                    $stdObject = new stdClass();
                    $stdObject->userid = $newUserID;
                    $stdObject->bcgtqualificationid = $newQualID->id;
                    $stdObject->bcgtunitid = $newUnitID->id;
                    $stdObject->bcgttypeawardid = $awardID;
                    $stdObject->comments = $userUnit[5];
                    $stdObject->dateupdated = $userUnit[6];
                    $stdObject->userdefinedvalue = $userUnit[7];
                    $stdObject->bcgtvalueid = $valueID;
                    
                    if(array_key_exists($userUnit[9], $teachersAfterInsert))
                    {
                        $stdObject->setbyuserid = $teachersAfterInsert[$userUnit[9]];
                    }
                    else
                    {
                        $stdObject->setbyuserid = -1;
                    }
                    if(array_key_exists($userUnit[10], $teachersAfterInsert))
                    {
                        $stdObject->updatedbyuserid = $teachersAfterInsert[$userUnit[10]];
                    }
                    else
                    {
                        $stdObject->updatedbyuserid = -1;
                    }
                    $stdObject->dateset = $userUnit[11];
                    $DB->insert_record('block_bcgt_user_unit', $stdObject);
                    
                    //also check if the user is already on the qualification.
                    $sql = "SELECT * FROM {block_bcgt_user_qual} WHERE userid = ? AND bcgtqualificationid = ?";
                    $record = $DB->get_record_sql($sql, array($newUserID, $newQualID->id));
                    if(!$record)
                    {
                        //then the user needs to go on the qual
                        $record = new stdClass();
                        $record->bcgtqualificationid = $newQualID->id;
                        $record->userid = $newUserID;
                        $record->roleid = 5;
                        return $DB->insert_record('block_bcgt_user_qual', $record);
                    }
                    return true;
                }
                else
                {
                    echo "Couldnt find unit<br />";
                }
            }
            else
            {
                echo "Couldnt find User<br />";
            }
        }
        else
        {
            echo "Couldnt find Qual<br />";
        }
        
    }
    
    function insert_new_user_criteria($userCriteria, $qualsAfterInsert, $criteriaAfterInsert, $studentsAfterInsert, $teachersAfterInsert)
    {
        global $DB, $values;
        if(array_key_exists($userCriteria[2], $qualsAfterInsert))
        {
            $newQualID = $qualsAfterInsert[$userCriteria[2]];
            if(array_key_exists($userCriteria[1], $studentsAfterInsert))
            {
                $newUserID = $studentsAfterInsert[$userCriteria[1]];
                
                if(array_key_exists($userCriteria[3], $criteriaAfterInsert))
                {
                    $newCriteriaID = $criteriaAfterInsert[$userCriteria[3]];
                    //need to find the value
                    $valueID = -1;
                    if($userCriteria[5])
                    {
                        if(!array_key_exists($userCriteria[5], $values))
                        {
                            //then go and get the value
                            $sql = "SELECT * FROM {block_bcgt_value} WHERE bcgttypeid = ? 
                                AND shortvalue = ?";
                            $record = $DB->get_record_sql($sql, array(BTECQualification::ID, $userCriteria[5]));
                            if($record)
                            {
                                $valueID = $record->id;
                                $values[$userCriteria[5]] = $valueID;
                            }
                        }
                        else
                        {
                            $valueID = $values[$userCriteria[5]];
                        }
                    }

                    //now insert it
                    $stdObject = new stdClass();
                    $stdObject->userid = $newUserID;
                    $stdObject->bcgtqualificationid = $newQualID->id;
                    $stdObject->bcgtcriteriaid = $newCriteriaID;
                    $stdObject->bcgtvalueid = $valueID;
                    $stdObject->dateset = $userCriteria[6];
                    if(array_key_exists($userCriteria[7], $teachersAfterInsert))
                    {
                        $stdObject->setbyuserid = $teachersAfterInsert[$userCriteria[7]];
                    }
                    else
                    {
                        $stdObject->setbyuserid = -1;
                    }
                    $stdObject->dateupdated = $userCriteria[8];
                    if(array_key_exists($userCriteria[9], $teachersAfterInsert))
                    {
                        $stdObject->updatedbyuserid = $teachersAfterInsert[$userCriteria[9]];
                    }
                    else
                    {
                        $stdObject->updatedbyuserid = -1;
                    }
                    $stdObject->comments = $userCriteria[10];
                    $stdObject->userdefinedvalue = $userCriteria[12];
                    return $DB->insert_record('block_bcgt_user_criteria', $stdObject);
                }
            }
            else
            {
                echo "Couldnt insert, couldnt find  student <br />";
            }
        }
        else 
        {
            echo "Couldnt insert, couldnt find qual <br />";
        }
        
    }
    
    function insert_new_user_award($userAward, $qualsAfterInsert, $studentsAfterInsert)
    {
        global $DB;
        if(array_key_exists($userAward[2], $qualsAfterInsert))
        {
            $newQualID = $qualsAfterInsert[$userAward[2]];
            if(array_key_exists($userAward[3], $studentsAfterInsert))
            {
                $newUserID = $studentsAfterInsert[$userAward[3]];
                //need to find the breakdown
                $breakdownRecord = $DB->get_record_sql('SELECT breakdown.* FROM {block_bcgt_target_breakdown} breakdown 
                    JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = breakdown.bcgttargetqualid 
                    JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetqual.id 
                    WHERE breakdown.targetgrade = ? AND qual.id = ?', array($userAward[4], $newQualID->id));
                if($breakdownRecord)
                {
                    $breakdownID = $breakdownRecord->id;
                    //now insert it
                    $stdObject = new stdClass();
                    $stdObject->userid = $newUserID;
                    $stdObject->bcgtqualificationid = $newQualID->id;

                    $stdObject->bcgtbreakdownid = $breakdownID;
                    $stdObject->type = $userAward[5];
                    $stdObject->dateupdated = $userAward[7];
                    $stdObject->warning = '';
                    return $DB->insert_record('block_bcgt_user_award', $stdObject);
                }
                else
                {
                    echo "Couldnt find breakdown<br />";
                }
                
            }
            else
            {
                echo "Couldnt find student <br />";
            }
        }
        else
        {
            echo "Couldnt find Qual <br />";
        }
    }
    
    function insert_initial_unit($unit)
    {
        global $DB;
        $typeID = get_type_id($unit[5]);
        $levelID = get_level_id($unit[7]);
        if($typeID)
        {
            if(!$levelID)
            {
                $levelID = -1;
            }
            $unitRecord = new stdClass();
            $unitRecord->uniqueid = $unit[2];
            $unitRecord->name = $unit[1];
            $unitRecord->credits = $unit[3];
            $unitRecord->bcgttypeid = $typeID;
            $unitRecord->bcgtlevelid = $levelID;
            $unitRecord->bcgtunittypeid = $unit[8];
            $unitRecord->details = $unit[9];
            
            $newID = $DB->insert_record('block_bcgt_unit', $unitRecord);
            $unitRecord->id = $newID;
            return $unitRecord;
        }
        return false;
    }
    
    function insert_initial_criteria($criteria, $unitsAfterInsert, $qualsAfterInsert)
    {
        global $DB;
        $newUnitID = false;
        $newQualID = -1;
        if(array_key_exists($criteria[4], $unitsAfterInsert))
        {
            $unit = $unitsAfterInsert[$criteria[4]];
            if($unit)
            {
                $newUnitID = $unit->id;
            } 
        }
//        else
//        {
//            echo "$criteria[4] Not Found<br />";
//        }
//        
        //Commented out due to not being used with criteria not on Quals
//        if($criteria[])
//        {
//            $qualification = $qualsAfterInsert[$criteria[]];
//            if($qualification)
//            {
//                $newQualID = $qualification->id;
//            } 
//        }
        # woody
        if($newUnitID)
        {
            //if(!$criteria[] || ($criteria[] && $newQualID))
            //{
                $criteriaRecord = new stdClass();
                $criteriaRecord->name = $criteria[1];
                $criteriaRecord->details = $criteria[2];
                $criteriaRecord->type = $criteria[3];
                //TODO REMOVE
                $criteriaRecord->bcgttypeawardid = 0;
                $criteriaRecord->bcgtunitid = $newUnitID;
                $criteriaRecord->parentcriteriaid = $criteria[5];
                //$criteriaRecord->targetdate = $criteria[];
                //Currently always set to -1
                $criteriaRecord->bcgtqualificationid = $newQualID;
                return $DB->insert_record('block_bcgt_criteria', $criteriaRecord);
            //}
        }
        
    }
    
    function insert_initial_qual_unit($qualUnit, $unitsAfterInsert, $qualsAfterInsert)
    {
        global $DB;
        $newUnitID = false;
        $newQualID = false;
        if(array_key_exists($qualUnit[1], $unitsAfterInsert))
        {
            $unit = $unitsAfterInsert[$qualUnit[1]];
            if($unit)
            {
                $newUnitID = $unit->id;
            } 
        }
//        else
//        {
//            echo "$qualUnit[1] Not Found<br />";
//        }
        if(array_key_exists($qualUnit[0], $qualsAfterInsert))
        {
            $qualification = $qualsAfterInsert[$qualUnit[0]];
            if($qualification)
            {
                $newQualID = $qualification->id; 
            } 
        }
//        else
//        {
//            echo "$qualUnit[0] Not Found<br />";
//        }
        if($newUnitID && $newQualID)
        {
            $record = new stdClass();
            $record->bcgtqualificationid = $newQualID;
            $record->bcgtunitid = $newUnitID;
            $DB->insert_record('block_bcgt_qual_units', $record);
        }
            
    }
    
    /**
     * 
     * @global type $DB
     * @param type $typeID
     * @param type $type
     * @param type $subtypeID
     * @param type $subtype
     * @param type $levelID
     * @param type $level
     * @return boolean
     */
    function get_target_qual($typeID = -1, $type = '', 
            $subtypeID = -1, $subtype = '', $levelID = -1, $level = '')
    {
        global $DB;
        $sql = "SELECT qual.id FROM {block_bcgt_target_qual} AS qual
        JOIN {block_bcgt_type} AS type ON type.id = qual.bcgttypeid 
        JOIN {block_bcgt_subtype} AS subtype ON subtype.id = qual.bcgtsubtypeid 
        JOIN {block_bcgt_level} AS level ON level.id = qual.bcgtlevelid 
        WHERE type.type = ? AND subtype.subtype = ? AND level.trackinglevel = ?";
//        echo $sql;
//        print_object(array($type, $subtype, $level));
        $record = $DB->get_record_sql($sql, array($type, $subtype, $level));
        if($record)
        {
            //will only return one
            return $record->id;
        }
        return false;
    }
    
    function get_level_id($level)
    {
        global $DB;
        $sql = "SELECT level.id FROM {block_bcgt_level} AS level
            WHERE level.trackinglevel = ?";
        $record = $DB->get_record_sql($sql, array($level));
        if($record)
        {
            return $record->id;
        }
        return false;
    }
    
    function get_type_id($type)
    {
        global $DB;
        $sql = "SELECT type.id FROM {block_bcgt_type} AS type
            WHERE type.type = ?";
        $record = $DB->get_record_sql($sql, array($type));
        if($record)
        {
            return $record->id;
        }
        return false;
    }
    
    function get_btec_requires()
    {
        global $CFG;
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECQualification.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECHigherQualification.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECLowerQualification.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFoundationQualification.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECUnit.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECHigherUnit.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECLowerUnit.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFoundationUnit.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECCriteria.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECHigherCriteria.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECLowerCriteria.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFoundationCriteria.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFirst2013Qualification.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFirst2013Unit.class.php');
        require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECFirst2013Criteria.class.php');
    }
    
    function insert_new_user($user)
    {
        global $DB;
        $sql = 'SELECT  * FROM {user} WHERE username = ?';
        $record = $DB->get_record_sql($sql, array($user[1]));
        if($record)
        {
            return $record->id;
        }
        
        $record = new stdClass();
        $record->username = $user[1];
        $record->firstname = $user[2];
        $record->lastname = $user[3];
        $record->email = $user[4];
        $record->dob = $user[5];
        if(!$user[5] || $user[5] == '' || $user[5] == 0)
        {
            $record->dob = null;
        }
        $record->idnumber = $user[6];
        $record->phone2 = $user[7];
        $record->institution = $user[8];
        $record->city = $user[9];
        $record->country = $user[10];
        $record->auth = 'ldap';
        return $DB->insert_record('user', $record);
    }
    
    function get_btec_unit_activity_table($activityID, $unit, $courseID, 
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
    function get_btec_activity_unit_table($activityID, $unitID, $courseID, $new = false, $modDirect = false)
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
                            $unitID.'" class="qualunitcheck qualunitcheck'.$unitID.'" unit="'.$unitID.'"/>';
                }
                $retval .= '<p class="warningNoQuals" id="'.$unitID.'_noqualswarn">'.get_string('warningnoqualsselected','block_bcgt').'</p>';
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
    function btec_activity_by_activity_page($courseID)
    {
        //get all of the activities on this course
        //load all of their units and criteria etc. 
        //table
            //columns: Add, Activity, Units/Criterias
            //rows -> activities, units/criterias
        global $CFG;
        $context = context_course::instance($courseID);
        $retval = '';
        $quals = bcgt_get_course_quals($courseID, BTECQualification::FAMILYID);
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
                        .'&fID='.BTECQualification::FAMILYID.'"><img src="'.$img.'"/></a>';
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
                $out .= get_mod_unit_summary_table($activity->id, BTECQualification::FAMILYID);
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
     * this will output the view of the activity by unit page
     * this shows what units are on what activities
     * @global type $CFG
     * @param type $courseID
     * @return string
     */
    function btec_activity_by_unit_page($courseID)
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
        $quals = bcgt_get_course_quals($courseID, BTECQualification::FAMILYID);
        if(count($quals) == 1)
        {
            $retval .= '<h2>'.bcgt_get_qualification_display_name(end($quals)).'</h2>';
        }
        $modLinking = load_bcgt_mod_linking();
        //load the icons
        $modIcons = load_mod_icons($courseID);
        //there must be quals to get this far
        $units = bcgt_get_course_units($courseID, BTECQualification::FAMILYID);
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
                        .'&fID='.BTECQualification::FAMILYID.'"><img src="'.$img.'"/></a>';
                }
                $retval .= '</td>';
                $retval .= '<td>'.$unit->name.'</td>';
                $activities = BTECQualification::get_unit_activities($courseID, $unit->id);
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
        
    
    
    /**
     * This searches for any units that are attached to the mod
     * that arent selected now and deletes their selections. 
     * @global type $DB
     * @param type $courseModuleID
     * @param type $units
     * @return boolean
     */
    function bcgt_btec_remove_mod_unit_selection($courseModuleID, $units = array())
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
        $params = array($courseModuleID, BTECQualification::FAMILYID);
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
    function bcgt_btec_process_mod_unit_selection($courseModuleID, $unitID, $courseID, $attemptNo = 1)
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
                $stdObj->attemptno = $attemptNo;
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
    function bcgt_btec_process_mod_selection_changes($courseModuleID, $unitID, $courseID, $attemptNo = 1)
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
                    $stdObj->attemptno = $attemptNo;
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
    function bcgt_btec_process_mod_units($courseModuleID, $unitID, $courseID, $attemptNo = 1)
    {
        $activityUnits = get_activity_units($courseModuleID, BTECQualification::FAMILYID);
        //was this unit on it before?
        if(array_key_exists($unitID, $activityUnits))
        {
            //then we need to check it
            bcgt_btec_process_mod_selection_changes($courseModuleID, $unitID, $courseID, $attemptNo);
        }
        else 
        {
            //we are just adding it all
            bcgt_btec_process_mod_unit_selection($courseModuleID, $unitID, $courseID, $attemptNo);
        }
        
    }
  

    
function bcgt_btec_get_criteria_submission_summary($criteriaID, $courseID = -1, $qualID = -1, 
                                    $groupingID = -1)
{
    
    $courseJoinSql = " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = 
            qualunits.bcgtqualificationid 
            JOIN {context} con ON con.instanceid = coursequal.courseid 
            JOIN {role_assignments} roleass ON roleass.contextid = con.id 
            JOIN {user} user ON user.id = roleass.userid AND user.id = userunit.userid";
    
    $groupingJoinSql = " JOIN {groups_members} members ON members.userid = user.id 
            JOIN {groupings_groups} gg ON gg.groupid = members.groupid";
    
    $courseWhere = " coursequal.courseid = ?";
    $qualWhere = " qualunits.bcgtqualificationid = ?";
    $groupingWhere = " gg.groupingid = ?";
    
    global $DB;
    $params = array();
    $sql = "SELECT totalcount.count AS totalcount, attempted.count AS attemptedcount, 
        achieved.count as achievedcount FROM";
    
    $totalSql = "(SELECT count(distinct(userunit.userid)) as count FROM {block_bcgt_user_unit} userunit 
        JOIN {block_bcgt_criteria} crit ON crit.bcgtunitid = userunit.bcgtunitid 
        JOIN {block_bcgt_qual_units} qualunits ON qualunits.bcgtunitid = userunit.bcgtunitid 
        LEFT OUTER JOIN {block_bcgt_user_criteria} usercrit ON usercrit.bcgtcriteriaid = crit.id 
        LEFT OUTER JOIN {block_bcgt_value} value ON value.id = usercrit.bcgtvalueid ";
    if($courseID != -1)
    {
        $totalSql .= $courseJoinSql;
    }
    if($groupingID != -1)
    {
        $totalSql .= $groupingJoinSql;
    }
    $totalSql .= " WHERE crit.id = ?";
    $params[] = $criteriaID;
    if($courseID != -1)
    {
        $totalSql .= " AND ".$courseWhere;
        $params[] = $courseID;
    }
    if($qualID != -1)
    {
        $totalSql .= " AND ".$qualWhere;
        $params[] = $qualID;
    }
    if($groupingID != -1)
    {
        $totalSql .= " AND ".$groupingWhere;
        $params[] = $groupingID;
    }

    $sql .= $totalSql." ) AS totalcount";
    
    $attemptedSql = ", (SELECT count(distinct(usercrit.userid)) as count 
        FROM {block_bcgt_user_criteria} 
        usercrit JOIN {block_bcgt_criteria} crit ON crit.id = usercrit.bcgtcriteriaid
        LEFT OUTER JOIN {block_bcgt_value} value ON value.id = usercrit.bcgtvalueid 
        JOIN {block_bcgt_qual_units} qualunits ON qualunits.bcgtunitid = crit.bcgtunitid 
        JOIN {block_bcgt_user_unit} userunit ON userunit.userid = usercrit.userid AND qualunits.bcgtunitid = userunit.bcgtunitid";
    if($courseID != -1)
    {
        $attemptedSql .= $courseJoinSql;
    }
    if($groupingID != -1)
    {
        $attemptedSql .= $groupingJoinSql;
    }
    $attemptedSql .= " WHERE crit.id = ?";
    $params[] = $criteriaID;
    if($courseID != -1)
    {
        $attemptedSql .= " AND ".$courseWhere;
        $params[] = $courseID;
    }
    if($qualID != -1)
    {
        $attemptedSql .= " AND ".$qualWhere;
        $params[] = $qualID;
    }
    if($groupingID != -1)
    {
        $attemptedSql .= " AND ".$groupingWhere;
        $params[] = $groupingID;
    } 
    $attemptedSql .= " AND value.shortvalue != ? AND value.shortvalue != ? 
            AND value.shortvalue != ? AND usercrit.bcgtvalueid IS NOT NULL) AS attempted";
    $params[] = 'WNS';
    $params[] = 'ABS';
    $params[] = 'N/A';
    $sql .= $attemptedSql;
    //now the achieved sql. 
    $achievedSql = ", (SELECT count(distinct(usercrit.userid)) as count 
        FROM {block_bcgt_user_criteria} 
        usercrit JOIN {block_bcgt_criteria} crit ON crit.id = usercrit.bcgtcriteriaid
        LEFT OUTER JOIN {block_bcgt_value} value ON value.id = usercrit.bcgtvalueid 
        JOIN {block_bcgt_qual_units} qualunits ON qualunits.bcgtunitid = crit.bcgtunitid
        JOIN {block_bcgt_user_unit} userunit ON userunit.userid = usercrit.userid AND qualunits.bcgtunitid = userunit.bcgtunitid";
    if($courseID != -1)
    {
        $achievedSql .= $courseJoinSql;
    }
    if($groupingID != -1)
    {
        $achievedSql .= $groupingJoinSql;
    }
    $achievedSql .= " WHERE crit.id = ?";
    $params[] = $criteriaID;
    if($courseID != -1)
    {
        $achievedSql .= " AND ".$courseWhere;
        $params[] = $courseID;
    }
    if($qualID != -1)
    {
        $achievedSql .= " AND ".$qualWhere;
        $params[] = $qualID;
    }
    if($groupingID != -1)
    {
        $achievedSql .= " AND ".$groupingWhere;
        $params[] = $groupingID;
    } 
    $achievedSql .= " AND value.shortvalue = ? ) AS achieved";
    $params[] = 'A';
    $sql .= $achievedSql;
    return $DB->get_record_sql($sql, $params);
}


function btec_apply_assignment_grading_box(&$mform)
{
    
    $mform->addElement('header', 'gradetrackerheader', get_string('gradetracker2', 'block_bcgt'));
        
    
    
    $mform->addElement('html', '<br><br><br>');
    
}
    
?>
