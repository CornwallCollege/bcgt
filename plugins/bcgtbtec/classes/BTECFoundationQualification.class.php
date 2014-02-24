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
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/lib.php');
require_once($CFG->dirroot.'/blocks/bcgt/plugins/bcgtbtec/classes/BTECQualification.class.php');
//include_once('BTECQualification.class.php');
class BTECFoundationQualification extends BTECQualification {
	
	const ID = 4;
	const NAME = 'BTEC Foundation';
	
	function BTECFoundationQualification($qualID, $params, $loadParams)
	{
		parent::BTECQualification($qualID, $params, $loadParams);
	}
	
	function get_type()
	{
		return BTECFoundationQualification::NAME;
	}
	
	function get_class_ID()
	{
		return BTECFoundationQualification::ID;
	}
	
	/**
	 * Using the object values inserts the qualification into the database
	 */
	function insert_qualification()
	{
        global $DB;
		//as each qual is different its easier to do this hear. 
		$dataobj = new stdClass();
		$dataobj->name = $this->name;
        $dataobj->additionalname = $this->additionalName;
		$dataobj->code = $this->code;
		$dataobj->credits = $this->credits;
        $dataobj->noyears = $this->noYears;
		$targetQualID = parent::get_target_qual(BTECFoundationQualification::ID);
		$dataobj->bcgttargetqualid = $targetQualID;
		$id = $DB->insert_record("block_bcgt_qualification", $dataobj);
		$this->id = $id;
        logAction(LOG_MODULE_GRADETRACKER, LOG_ELEMENT_GRADETRACKER_QUALIFICATION, LOG_VALUE_GRADETRACKER_INSERTED_QUAL, null, $this->id, null, null, null);
	}

    public static function get_instance($qualID, $params, $loadParams)
    {   
        if(!$params || !isset($params->level))
        {
            $levelID = optional_param('level', -1, PARAM_INT);
            if(!$params)
            {
                $params = new stdClass();
            }
            if($levelID)
            {
                $level = new Level($levelID);
                $params->level = $level;
            } 
        }
//        if(!$params || !isset($params->subtype))
//        {
//            $subTypeID = optional_param('subtype', -1, PARAM_INT);
//            if(!$params)
//            {
//                $params = new stdClass();
//            }
//            if($subTypeID)
//            {
//                $subType = new SubType($subTypeID);
//                $params->subType = $subType;
//            }
//        }
        //subtype to be hardcoded!!

        return new BTECFoundationQualification($qualID, $params, $loadParams);
    }
	
/**
	 * Calculate the predicted award and if its all units
	 * have been awarded then its final else predicted
	 */
	function calculate_predicted_grade()
	{
		return $this->calculate_qual_award(false);
	}
	
/**
	 * This calculates the Qualification Award
	 * If all units have been awarded it can calculate the final grade
	 * if only some units have been awarded it MAY be able to calculate 
	 * the predicted/progress award.
	 * Each unit has credits
	 * At each Qualification award level the unit award is worth a set number of points
	 * Each units points are then credits * points
	 * We can get the total unit points for the users units and calculate an 
	 * average per credit.
	 * This can be pushed out to predict the final award by taking the average and
	 * multiplying it by the total credits of the qual
	 * This points value is then looked up in the database and an award MAY be
	 * able to get it.
	 * Its possible if they only have one PASS (lets say a 7) for them
	 * not to be able to get a full award (If a student gets passes at all
	 * units they may not get an award at all)
	 * @param unknown_type $failIfPredicted
	 */
	protected function calculate_qual_award($failIfPredicted = false)
	{		
		//get all of the units
		//as long as all of them are at pass
		//then get the one that reads 'final project' and take that qual award

		$unitsNoAward = false;
		$units = $this->units;
		if($units)
		{
			$qualAward = false;
			foreach($units AS $unit)
			{
				$unitTypeID = $unit->get_unit_type_id();
				$unitTypes = null;
				if($unitTypeID)
				{
					$unitTypes = UNIT::get_unit_types($unit->get_typeID(), $unitTypeID);
				}
				$finalProject = false;
				if($unitTypes)
				{
					if($unitTypes->type == 'Final Project')
					{
						$type = 'Final';
						$predicted = false;
						$finalProject = $unit->get_user_award();
						if($finalProject)
						{
							//now we need to convert this into a Qualification Award
							$award = $this->get_final_grade_by_award($finalProject->get_award());
							if($award)
							{
                                $params = new stdClass();
                                $params->targetgrade = $award->targetgrade;
                                $params->type = $type;
                                $params->ucaspoints = $award->ucaspoints;
								//get the qual award by those points
								$qualAward = new QualificationAward($award->id, $params);
								//update the students award in the DB
								$this->update_qualification_award($qualAward);
							}
						}
						else
						{
							$qualAward = false;
						}
					}
				}	
				if($unitAward = $unit->get_user_award())
				{
					if($unitAward->get_id() == -1)
					{
						$unitsNoAward = true;
					}
				}
				else
				{
					$unitsNoAward = true;
				}
			}
		}
		else
		{
			$qualAward = false;
		}
		
		if($qualAward && !$unitsNoAward)
		{
			return $qualAward;
		}
		else
		{
			$this->set_qualification_award_null();
			return false;
		}
	}
	
}