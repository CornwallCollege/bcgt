<?php
global $CFG;
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Qualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/classes/ALevelQualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/classes/ASLevelQualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/classes/A2LevelQualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/classes/ALevelUnit.class.php');
//require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/classes/ALevelCriteria.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/lib.php');
function run_alevel_initial_import()
    {
        upgrade_set_timeout(10000);
        echo "Running initial Import<br />";
        
        //first create the quals
        //then create the units
        //then add the units to the quals.
        //then create the weightings
        global $CFG;
        $count = 1;
        $qualsCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/data/alevelquals.csv', 'r');
        $qualsAfterInsert = array();
        echo "Inserting Initial Quals<br />";
        while(($qual = fgetcsv($qualsCSV)) !== false) {
            if($count != 1)
            {
                $qualRecord = alevel_insert_initial_qual($qual);
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
        
        
        
        
//        $count = 1;
//        $unitsCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/data/alevelunits.csv', 'r');
//        $unitsAfterInsert = array();
//        echo "Inserting Initial Units<br />";
//        while(($unit = fgetcsv($unitsCSV)) !== false) {
//            if($count != 1)
//            {
//                $unitRecord = insert_initial_unit($unit);
//                if($unitRecord)
//                {
//                    //Old unit id as the key, new unit id is in the object. 
//                    $unitsAfterInsert[$unit[0]] = $unitRecord; 
//                }
//            }
////            else
////            {
////                print_object($unit);
////            }
//            $count++;
//        }
//        echo "Done<br />";
        
            //now we do the weightings
        $count = 1;
        $qualsCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/data/alevelqualweightings.csv', 'r');
        $qualsAfterInsert = array();
        echo "Inserting Initial Qual weightings<br />";
        while(($qual = fgetcsv($qualsCSV)) !== false) {
            if($count != 1)
            {
                alevel_insert_initial_qual_weighings($qual);
            }
            $count++;
        }  
        echo "Done: $count quals inserted<br />";
        fclose($qualsCSV);
        
        
        
        
//        $criteriaAfterInsert = array();
//        echo "Done: $count units inserted<br />";
//        fclose($unitsCSV);
//        $count = 1;
//        $criteriaCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/data/alevelcriteria.csv', 'r');
//        echo "Inserting Initial Criteria<br />";
//        while(($criteria = fgetcsv($criteriaCSV)) !== false) {
//            if($count != 1)
//            {
//                $citeriaRecord = insert_initial_criteria($criteria, $unitsAfterInsert, $qualsAfterInsert); 
//                if($citeriaRecord)
//                {
//                    $criteriaAfterInsert[$criteria[0]] = $citeriaRecord; 
//                }
//            } 
//            $count++;
//        }
//        echo "Done: $count criteria inserted<br />";
//        fclose($criteriaCSV);
        
//        $qualUnitsCSV = fopen($CFG->dirroot.'/blocks/bcgt/plugins/bcgtalevel/data/alevelqualsunits.csv', 'r');
//        $qualUnits = fgetcsv($qualUnitsCSV); #mr potatoe head
//        $count = 1;
//        echo "Inserting Initial Quals Units<br />";
//        while(($qualUnit = fgetcsv($qualUnitsCSV)) !== false) {
//            if($count != 1)
//            {
//                insert_initial_qual_unit($qualUnit, $unitsAfterInsert, $qualsAfterInsert);
//            }
//            $count++;
//        }
//        echo "Done: $count units put onto quals inserted<br />";
//        fclose($qualUnitsCSV);
//
//       echo "Finished<br />";
    }
    
    
    function alevel_insert_initial_qual($qual)
    {
        global $DB;
        $targetqualID = get_target_qual(-1, $qual[1], -1, $qual[2], 3, '');
        if($targetqualID)
        {
            $qualRecord = new stdClass();
            $qualRecord->name = $qual[3];
            $qualRecord->bcgttargetqualid = $targetqualID;
            $qualRecord->credits = (isset($qual[4]) ? $qual[4] : '');
            $qualRecord->code = (isset($qual[5]) ? $qual[5] : '');
            $qualRecord->additionalname = (isset($qual[6]) ? $qual[6] : '');
            
            $newID = $DB->insert_record('block_bcgt_qualification', $qualRecord);
            $qualRecord->id = $newID;
            return $qualRecord;
        }
        return false;  
    }
    
    function alevel_insert_initial_qual_weighings($weighting, $qualsAfterInsert)
    {
        if(array_key_exists($weighting[0], $qualsAfterInsert))
        {
            //then we have the new qualID
            $newQualID = $qualsAfterInsert[$weighting[0]];
            $record = new stdClass();
            $record->bcgtqualificationid = $newQualID->id;
            $record->coefficient = $weighting[2];
            $record->percentage = $weighting[1];
            $record->number = $weighting[3]; 
            return $DB->insert_record('block_bcgt_qual_weighting', $record);
        }
    }
    
    function alevel_insert_initial_unit($unit)
    {
        global $DB;
//        $typeID = get_type_id($unit[5]);
//        $levelID = get_level_id($unit[7]);
//        if($typeID)
//        {
//            if(!$levelID)
//            {
//                $levelID = -1;
//            }
//            $unitRecord = new stdClass();
//            $unitRecord->uniqueid = $unit[2];
//            $unitRecord->name = $unit[1];
//            $unitRecord->credits = $unit[3];
//            $unitRecord->bcgttypeid = $typeID;
//            $unitRecord->bcgtlevelid = $levelID;
//            $unitRecord->bcgtunittypeid = $unit[8];
//            $unitRecord->details = $unit[9];
//            
//            $newID = $DB->insert_record('block_bcgt_unit', $unitRecord);
//            $unitRecord->id = $newID;
//            return $unitRecord;
//        }
        return false;
    }
        
    function alevel_insert_initial_qual_unit($qualUnit, $unitsAfterInsert, $qualsAfterInsert)
    {
//        global $DB;
//        $newUnitID = false;
//        $newQualID = false;
//        if(array_key_exists($qualUnit[1], $unitsAfterInsert))
//        {
//            $unit = $unitsAfterInsert[$qualUnit[1]];
//            if($unit)
//            {
//                $newUnitID = $unit->id;
//            } 
//        }
////        else
////        {
////            echo "$qualUnit[1] Not Found<br />";
////        }
//        if(array_key_exists($qualUnit[0], $qualsAfterInsert))
//        {
//            $qualification = $qualsAfterInsert[$qualUnit[0]];
//            if($qualification)
//            {
//                $newQualID = $qualification->id; 
//            } 
//        }
////        else
////        {
////            echo "$qualUnit[0] Not Found<br />";
////        }
//        if($newUnitID && $newQualID)
//        {
//            $record = new stdClass();
//            $record->bcgtqualificationid = $newQualID;
//            $record->bcgtunitid = $newUnitID;
//            $DB->insert_record('block_bcgt_qual_units', $record);
//        }
            
    }
?>
