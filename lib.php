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
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Qualification.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Level.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/SubType.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Unit.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Criteria.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Task.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Grade.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Pathway.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/PathwayType.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Project.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Breakdown.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/TargetGrade.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/UserCourseTarget.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/UserPriorLearning.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/EntryQual.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/EntryGrade.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/QualWeighting.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Import.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/UserDataImport.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Subject.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Range.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Reporting.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/UnitTests.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/UserCalculations.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Data.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/SystemDataImport.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/BackupImport.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/UserVA.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Group.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Grouping.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Alps.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Log.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/RegisterGroupImport.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/TargetQualWeighting.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Archive.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/classes/core/Value.class.php');
require_once($CFG->dirroot.'/blocks/bcgt/bclib.php');

define('BCGT_NUMBER_CORE_DASH_TABS', 11);
define('BCGT_COURSE_TAB_NUMBER', 2);
define('BCGT_ADMIN_TAB_NUMBER', 9);

//these are not widely used, but should be!
//etxra | on end of unit to denote Bespoke that have no family
define('BCGT_UNIT_VIEW_FAMILIES', 'BTEC|CG|Bespoke||');
define('BCGT_REGISTER_VIEW_FAMILIES', 'BTEC|CG|ALevel|Bespoke');
define('BCGT_CLASS_VIEW_FAMILIES', 'BTEC|CG|ALevel|Bespoke');
define('BCGT_FA_VIEW_FAMILIES', 'BTEC|ALevel');
define('BCGT_ACTIVITYT_VIEW_FAMILIES', 'BTEC|CG');

/**
 * 
 * 
 * @global type $PAGE
 * @param type $uI
 * @param type $simpleLoad - This is for loading it up within other ajax stuff, like the elbp
 */
function load_javascript($uI = false, $simpleLoad = false)
{
    global $CFG, $PAGE;
    
    $output = "";
        
    if(!get_config('bcgt', 'themejquery'))
    {
        if ($simpleLoad){
            $output .= "<script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/js/jquery-1.9.1.js'></script>";
        } else {
            $PAGE->requires->js( new moodle_url('http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js') );
            #$PAGE->requires->js('/blocks/bcgt/js/jquery-1.9.1.js');
        }
    }
    else
    {
        $location = get_config('bcgt', 'themejqueryloc');
        if ($simpleLoad){
            $output .= "<script type='text/javascript' src='{$CFG->wwwroot}/{$location}'></script>";
        } else {
            $PAGE->requires->js(''.$location.'');
        }
    }
    
    if($uI)
    {
        if ($simpleLoad){
            $output .= "<script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/js/jquery-ui.js'></script>";
        } else {
            $PAGE->requires->js( new moodle_url('http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js') );
            #$PAGE->requires->js('/blocks/bcgt/js/jquery-ui.js');
        }
    }
    
    if ($simpleLoad){
        $output .= "<script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/js/block_bcgt_functions.js'></script>";
        $output .= "<script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/js/jquery.dataTables.js'></script>";
        $output .= "<script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/js/FixedColumns.stable.js'></script>";
        $output .= "<script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/js/FixedHeader.js'></script>";
        $output .= "<script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/js/jquery.ui.touch-punch.min.js'></script>";
        $output .= "<script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/js/jquery.doubleScroll.js'></script>";
        $output .= "<script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/js/jquery.fixedheadertable.min.js'></script>";
        $output .= "<script type='text/javascript' src='{$CFG->wwwroot}/blocks/bcgt/js/tinytbl/jquery.ui.tinytbl.js'></script>";
    } else {
        $PAGE->requires->js('/blocks/bcgt/js/block_bcgt_functions.js');
        $PAGE->requires->js('/blocks/bcgt/js/jquery.dataTables.js');
        $PAGE->requires->js('/blocks/bcgt/js/FixedColumns.stable.js');
        $PAGE->requires->js('/blocks/bcgt/js/FixedHeader.js'); 
        $PAGE->requires->js('/blocks/bcgt/js/jquery.ui.touch-punch.min.js');
        $PAGE->requires->js('/blocks/bcgt/js/jquery.doubleScroll.js');
        $PAGE->requires->js('/blocks/bcgt/js/jquery.fixedheadertable.min.js');
        $PAGE->requires->js('/blocks/bcgt/js/tinytbl/jquery.ui.tinytbl.js');
    }
    
    return $output;
    
}


function load_css($ui = false, $simple = false)
{
    
    global $CFG, $PAGE;
    
    $output = "";
    
    if ($simple)
    {
        $output .= "<link rel='stylesheet' type='text/css' href='{$CFG->wwwroot}/blocks/bcgt/js/jquery.fixedtableheader.defaulttheme.css' />";
        $output .= "<link rel='stylesheet' type='text/css' href='http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.min.css' />";
    }
    else
    {
        $PAGE->requires->css('/blocks/bcgt/js/jquery.fixedtableheader.defaulttheme.css');
        $PAGE->requires->css( new moodle_url('http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.min.css') );
    }
    
    return $output;
    
}

function bcgt_get_qualification_details_fields_for_sql()
{
    return " family.family, family.id as familyid, level.trackinglevel, level.id as levelid , subtype.subtype, subtype.subtypeshort,  
        qual.name, qual.additionalname, type.id as typeid, type.type, subtype.id as subtypeid";
        
}

function bcgt_get_qualification_details_join_for_sql()
{
    return " JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
        JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
        JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
        JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
        JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid";
}

function bcgt_search_quals_report($options = null)
{
    global $DB;
    $sql = "SELECT distinct(qual.id), ".bcgt_get_qualification_details_fields_for_sql().', qual.bcgttargetqualid';
    $sql .= " FROM {block_bcgt_qualification} qual";
    $sql .= bcgt_get_qualification_details_join_for_sql();
    
    $onCourse = ((isset($options['oncourse']) && $options['oncourse']) ? true : false);
    $users = ((isset($options['users']) && $options['users']) ? true : false);
    $myStudents = ((isset($options['mystudents']) && $options['mystudents'] == 'qual') ? true : false);
    
    if($onCourse)
    {
        $sql .= ' JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id';
    }
    if($users)
    {
        $sql .= ' JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qual.id';
    }
    if($myStudents)
    {
        $sql .= ' JOIN {block_bcgt_user_qual} staffqual ON staffqual.bcgtqualificationid = userqual.bcgtqualificationid';
    }
    
    $params = array();
    if($options)
    {
        $categoryID = isset($options['categoryid']) ? $options['categoryid'] : ''; 
        if($categoryID != '')
        {
            $sql .= ' JOIN {block_bcgt_course_qual} coursequalcat ON coursequalcat.bcgtqualificationid = qual.id
                JOIN {course} course ON course.id = coursequalcat.courseid
                JOIN {course_categories} cats ON cats.id = course.category';
        }
        //then we need some where clauses. 
        $families = $options['family'];
        $levels = $options['level'];
        $subtypes = $options['subtype'];
        //categoryID or '' is All
        if(count($families) > 0 || count($levels) > 0 || count($subtypes) > 0 || $categoryID != '' || $myStudents == 'qual')
        {
            $sql .= ' WHERE ';
        }
        $and = false;
        $count = 0;
        if(count($families) > 0)
        {
            $sql .= ' family.id IN (';
            foreach($families AS $family)
            {
                $count++;
                $sql .= '?';
                $params[] = $family;
                if($count != count($families))
                {
                    $sql .= ',';
                }
            }
            $sql .= ')';
            $and = true;
        }
        
        $count = 0;
        if(count($levels) > 0)
        {
            if($and)
            {
                $sql .= ' AND ';
            }
            $sql .= ' level.id IN (';
            foreach($levels AS $level)
            {
                $count++;
                $sql .= '?';
                $params[] = $level;
                if($count != count($levels))
                {
                    $sql .= ',';
                }
            }
            $sql .= ')';
            $and = true;
        }
        
        $count = 0;
        if(count($subtypes) > 0)
        {
            if($and)
            {
                $sql .= ' AND ';
            }
            $sql .= ' subtype.id IN (';
            foreach($subtypes AS $subtype)
            {
                $count++;
                $sql .= '?';
                $params[] = $subtype;
                if($count != count($subtypes))
                {
                    $sql .= ',';
                }
            }
            $sql .= ')';
            $and = true;
        }
        if($categoryID != '')
        {
            if($and)
            {
                $sql .= ' AND ';
            }
            $sql .= ' cats.id = ?';
            $params[] = $categoryID;
            $and = true;
        }
        if($myStudents == 'qual')
        {
            global $USER;
            //then just find where this user is linked to the same quals
            //as the students
            if($and)
            {
                $sql .= ' AND ';
            }
            $sql .= ' staffqual.userid = ?';
            $params[] = $USER->id;

        }
    }
    $sql .= ' ORDER BY family.family ASC, level.id ASC, subtype.subtype ASC, qual.name ASC';
    return $DB->get_records_sql($sql, $params);    
        
}

function bcgt_search_users_report($options, $roles = array())
{
    global $DB;
    $sql = "SELECT distinct(u.id), u.firstname, u.lastname, u.username";
    $sql .= " FROM {user} u JOIN {block_bcgt_user_qual} userqual ON userqual.userid = u.id 
        JOIN {block_bcgt_qualification} qual ON qual.id = userqual.bcgtqualificationid ";
    $sql .= bcgt_get_qualification_details_join_for_sql();
    if($roles)
    {
        $sql .= ' JOIN {role} role ON role.id = userqual.roleid';
    }
    $params = array();
    if($options || count($roles) > 0)
    {
        $myStudents = isset($options['mystudents']) ? $options['mystudents'] : null;
        $categoryID = isset($options['categoryid']) ? $options['categoryid'] : '';
        if($categoryID != '')
        {
            $sql .= ' JOIN {block_bcgt_course_qual} coursequalcat ON coursequalcat.bcgtqualificationid = qual.id
                JOIN {course} course ON course.id = coursequalcat.courseid
                JOIN {course_categories} cats ON cats.id = course.category';
        }
        if($myStudents)
        {
            if($myStudents == 'qual')
            {
                //then just find where this user is linked to the same quals
                //as the students
                $sql .= ' JOIN {block_bcgt_user_qual} staffqual ON staffqual.bcgtqualificationid = userqual.bcgtqualificationid';
            }
            elseif($myStudents == 'user')
            {
                $sql .= " JOIN {context} c ON c.instanceid = u.id 
                    JOIN {role_assignments} r ON r.contextid = c.id "; 
            }
        }
        //then we need some where clauses. 
        $families = $options['family'];
        $levels = $options['level'];
        $subtypes = $options['subtype'];
        $studentFilter = 'all';
        if(isset($options['studentfilter']))
        {
            $studentFilter = $options['studentfilter'];
        }
        
        if(count($families) > 0 || count($levels) > 0 || count($subtypes) > 0 
                || count($roles) > 0 || $categoryID != '' || $studentFilter != 'all' || $myStudents)
        {
            $sql .= ' WHERE ';
        }
        $and = false;
        $count = 0;
        if(count($families) > 0)
        {
            $sql .= ' family.id IN (';
            foreach($families AS $family)
            {
                $count++;
                $sql .= '?';
                $params[] = $family;
                if($count != count($families))
                {
                    $sql .= ',';
                }
            }
            $sql .= ')';
            $and = true;
        }
        
        $count = 0;
        if(count($levels) > 0)
        {
            if($and)
            {
                $sql .= ' AND ';
            }
            $sql .= ' level.id IN (';
            foreach($levels AS $level)
            {
                $count++;
                $sql .= '?';
                $params[] = $level;
                if($count != count($levels))
                {
                    $sql .= ',';
                }
            }
            $sql .= ')';
            $and = true;
        }
        
        $count = 0;
        if(count($subtypes) > 0)
        {
            if($and)
            {
                $sql .= ' AND ';
            }
            $sql .= ' subtype.id IN (';
            foreach($subtypes AS $subtype)
            {
                $count++;
                $sql .= '?';
                $params[] = $subtype;
                if($count != count($subtypes))
                {
                    $sql .= ',';
                }
            }
            $sql .= ')';
            $and = true;
        }
        $count = 0;
        if(count($roles) > 0)
        {
            if($and)
            {
                $sql .= ' AND ';
            }
            $sql .= ' role.shortname IN (';
            foreach($roles AS $role)
            {
                $count++;
                $sql .= '?';
                $params[] = $role;
                if($count != count($roles))
                {
                    $sql .= ',';
                }
            }
            $sql .= ')';
            $and = true;
        }
        if($categoryID != '')
        {
            if($and)
            {
                $sql .= ' AND ';
            }
            $sql .= ' cats.id = ?';
            $params[] = $categoryID;
            $and = true;
        }
        if($studentFilter != 'all')
        {
            if($and)
            {
                $sql .= ' AND ';
            }
            $sql .= ' u.lastname LIKE ?';
            $params[] = $studentFilter.'%';
            $and = true;
        }
        if($myStudents)
        {
            global $USER;
            if($myStudents == 'qual')
            {
                //then just find where this user is linked to the same quals
                //as the students
                if($and)
                {
                    $sql .= ' AND ';
                }
                $sql .= ' staffqual.userid = ?';
                $params[] = $USER->id;
                
            }
            elseif($myStudents == 'user')
            {
                if($and)
                {
                    $sql .= ' AND ';
                }
                $sql .= ' r.userid = ? AND c.contextlevel = ? AND r.roleid = ?';
                $params[] = $USER->id;
                $params[] = CONTEXT_USER;
                $params[] = getRole(get_config('bcgt','tutorrole'));
            }
            $and = true;
        }
    }
    $sql .= ' ORDER BY u.lastname ASC, u.firstname ASC';
    return $DB->get_records_sql($sql, $params);  
}

function getRole($shortname)
{
    global $DB;
    $record = $DB->get_record("role", array("shortname" => $shortname));
    return ($record) ? $record->id : false;
}

function bcgt_is_user_on_qual($qualID, $studentID)
{
    global $DB;
    $sql = "SELECT * FROM {block_bcgt_user_qual} userqual 
        WHERE userid = ? AND bcgtqualificationid = ?";
    if($DB->get_records_sql($sql, array($studentID, $qualID)))
    {
        return true;
    }
    return false;
}

function bcgt_is_user_on_unit($userID, $unitID, $qualID = false)
{
    
    global $DB;
    
    $params = array($userID, $unitID);
    $sql = "SELECT id FROM {block_bcgt_user_unit}
            WHERE userid = ? AND bcgtunitid = ?";
    
    if ($qualID){
        $sql .= " AND bcgtqualificationid = ?";
        $params[] = $qualID;
    }
    
    if ($DB->get_records_sql($sql, $params))
    {
        return true;
    }
    else
    {
        return false;
    }
    
}

function bcgt_search_quals_2($params = null)
{
    global $DB;
    $sql = "SELECT distinct(qual.id), ".bcgt_get_qualification_details_fields_for_sql();
    $sql .= " FROM {block_bcgt_qualification} qual";
    $sql .= bcgt_get_qualification_details_join_for_sql();
    if($params && isset($params->onCourse) && $params->onCourse !== null)
    {
        if($params->onCourse)
        {
            $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id";
        }
        else
        {
            //then we need to do a LEFT OUTER JOIN and then where courseid IS NULL
        }
            
    }
    if($params && isset($params->hasStudents) && $params->hasStudents !== null)
    {
        if($params->hasStudents)
        {
            $sql .= " JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qual.id";
        }
        else
        {
            //then we need to do a LEFT OUTER JOIN and then where userid IS NULL
        }
    }
    $sqlParams = array();
    if($params)
    {
        $and = false;
        $where = true;
        
        if(isset($params->families) && $params->families !== null)
        {
            if($where)
            {
                $sql .= " WHERE ";
            }
            $sql .= " family.family IN (";
            $countFam = 0;
            foreach($params->families AS $fam)
            {
                $countFam++;
                $sql .= "?";
                if($countFam != count($params->families))
                {
                    $sql .= ',';
                }
                $sqlParams[] = $fam;
            }
            $sql .= ")";
            $and = true;
            $where = false;
        }
        if(isset($params->levels) && $params->levels !== null)
        {
            if($where)
            {
                $sql .= " WHERE ";
            }
            if($and)
            {
                $sql .= " AND ";
            }
            $sql .= " level.id IN (";
            $countLevel = 0;
            foreach($params->levels AS $levelID)
            {
                $countLevel++;
                $sql .= "?";
                if($countLevel != count($params->levels))
                {
                    $sql .= ',';
                }
                $sqlParams[] = $levelID;
            }
            $sql .= ")";
            $and = true;
            $where = false;
        }
    }
    $sql .= ' ORDER BY family.family ASC, level.id ASC, type.type ASC, subtype.subtype ASC, qual.name ASC';
    return $DB->get_records_sql($sql, $sqlParams);
}

function bcgt_get_users_quals($userID)
{
    global $DB;
    $sql = "SELECT distinct(qual.id),".bcgt_get_qualification_details_fields_for_sql()." FROM {block_bcgt_qualification} qual 
        JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qual.id";
    $sql .= bcgt_get_qualification_details_join_for_sql();
    $sql .= " WHERE userqual.userid = ? ORDER BY qual.name ASC";
    return $DB->get_records_sql($sql, array($userID));
}

function bcgt_get_users_quals_assessments($userID)
{
    global $DB;
    $sql = "SELECT distinct(qual.id),".bcgt_get_qualification_details_fields_for_sql()." FROM {block_bcgt_qualification} qual 
        JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qual.id";
    $sql .= bcgt_get_qualification_details_join_for_sql();
    $sql .= ' JOIN {block_bcgt_activity_refs} ref ON ref.bcgtqualificationid = qual.id';
    $sql .= " WHERE userqual.userid = ? AND ref.coursemoduleid IS NULL ORDER BY family.family ASC, level.trackinglevel ASC, subtype.subtype ASC, qual.name ASC";
    return $DB->get_records_sql($sql, array($userID));
}

function bcgt_get_qual($qualID)
{
    global $DB;
    $sql = "SELECT qual.id,".bcgt_get_qualification_details_fields_for_sql()." FROM {block_bcgt_qualification} qual";
    $sql .= bcgt_get_qualification_details_join_for_sql();
    $sql .= " WHERE qual.id = ? ORDER BY qual.name ASC";
    return $DB->get_record_sql($sql, array($qualID));
}

function get_qualification_type_families_used($familyID = -1, $excludeBespoke = false)
{
	global $DB;
	$sql = "SELECT distinct(family.id), family.family
    FROM {block_bcgt_type_family} AS family";
//	WHERE type.id IN (SELECT distinct(bcgttypeid) FROM {block_bcgt_target_qual})";
    $params = array();
    if($familyID != -1 || $excludeBespoke)
    {
        $sql .= " WHERE ";
    }
    $and = false;
	if($familyID != -1)
	{
		$sql .= " family.id = ?";
        $and = true;
        $params[] = $familyID;
	}
    if($excludeBespoke)
    {
        if($and)
        {
            $sql .= ' AND ';
        }
        $sql .= "family.family <> ?";
        $params[] = 'Bespoke';
        $and = true;
    }
	return $DB->get_records_sql($sql, $params);
}

/**
 * 
 * @global type $DB
 * @param type $id
 * @param type $sortOrder
 * @param type $ascDesc
 * @param type $typeID
 * @param type $levelID
 * @param type $subtypeID
 * @return type
 */
function get_qualification_targets($id = -1, $sortOrder = '', 
        $typeID = -1, $levelID = -1, $subtypeID = -1, $excludingBespoke = false)
{
    global $DB;
	$sql = "SELECT qual.id, level.id AS levelid, level.trackinglevel, type.id AS typeid, 
	type.type, subtype.id AS subtypeid, subtype.subtype, qual.id as bcgttargetqualid, countquals, family.family 
	FROM {block_bcgt_target_qual} qual 
	JOIN {block_bcgt_level} level ON level.id = qual.bcgtlevelid
	JOIN {block_bcgt_type} type ON type.id = qual.bcgttypeid
	JOIN {block_bcgt_subtype} subtype ON subtype.id = qual.bcgtsubtypeid
    JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
	LEFT OUTER JOIN 
	(
		SELECT bcgttargetqualid, count(bcgttargetqualid) AS countquals FROM {block_bcgt_qualification} 
		GROUP BY bcgttargetqualid
	) qualification ON qualification.bcgttargetqualid = qual.id 
	";
    $params = array();
	if($id != -1 || $typeID != -1 || $levelID != -1 || $subtypeID != -1 || $excludingBespoke)
	{
		$and = false;
		$sql .= " WHERE"; 
		if($id != -1)
		{
			$sql .= " qual.id = ?";
            $params[] = $id;
			$and = true;	
		}
		if($typeID != -1)
		{
			if($and)
			{
				$sql .= " AND";
			}
			$sql .= " type.id = ?";
            $params[] = $typeID;
			$and = true;
		}
		if($subtypeID != -1)
		{
			if($and)
			{
				$sql .= " AND";
			}
			$sql .= " subtype.id = ?";
            $params[] = $subtypeID;
			$and = true; 
		}
		if($levelID != -1)
		{
			if($and)
			{
				$sql .= " AND";
			}
			$sql .= " level.id = ?";
            $params[] = $levelID;
			$and = true;
		}
        if($excludingBespoke)
        {
            if($and)
            {
                $sql .= " AND ";
            }
            $sql .= " family.family <> ?";
            $params[] = 'Bespoke';
            $and = true;
        }
	}
	if($sortOrder != '')
	{
		$sql .= " ORDER BY $sortOrder";
	}
        	
	if($id != -1)
	{
		return $DB->get_record_sql($sql, $params);
	}
	return $DB->get_records_sql($sql, $params);
}

/**
 * 
 * @global type $DB
 * @param type $id
 * @return type
 */
function get_qualification_breakdown_by_targetQual($id)
{
	global $DB;
	$sql = "SELECT * FROM {block_bcgt_target_breakdown}  
	WHERE bcgttargetqualid = $id ORDER BY ranking DESC, unitsscoreupper DESC, ucaspoints DESC";

	return $DB->get_records_sql($sql);
}

/**
 * 
 * @global type $DB
 * @param type $id
 * @return type
 */
function get_qualification_grades_by_targetQual($id)
{
    global $DB;
	$sql = "SELECT * FROM {block_bcgt_target_grades}  
	WHERE bcgttargetqualid = $id ORDER BY ranking DESC, upperscore DESC, ucaspoints DESC";

	return $DB->get_records_sql($sql);
}

/**
 * Gets the database units for a qual
 * @global type $DB
 * @param type $qualID
 * @return type
 */
function get_qualification_units($qualID)
{
    global $DB;
    $sql = "SELECT unit.* FROM {block_bcgt_unit} unit 
        JOIN {block_bcgt_qual_units} qualunits ON qualunits.bcgtunitid = unit.id 
        WHERE qualunits.bcgtqualificationid = ?";
    return $DB->get_records_sql($sql, array($qualID));
}

/**
 * Gets the types
 * @global type $DB
 * @param type $typeID
 * @param type $familyID
 * @return type
 */
function bcgt_get_types($typeID = -1, $familyID = -1, $orderBy = 'type ASC')
{
    global $DB;
	$sql = "SELECT type.* FROM {block_bcgt_type} type ";
    if($typeID != -1 || $familyID != -1)
    {
        $sql .= ' WHERE';
    }
    $and = false;
    $params = array();
    if($typeID != -1)
    {
        $sql .= ' id = ?';
        $params[] = $typeID;
        $and = true;
    }
    if($familyID != -1)
    {
        if($and)
        {
            $sql .= ' AND';
        }
        $sql .= ' bcgttypefamilyid = ?';
        $params[] = $familyID;
    }
    $sql .= 'ORDER BY '.$orderBy;
	return $DB->get_records_sql($sql, $params);
}

/**
 * This gets the levels that have been added in
 * block_bcgt_target_qual for the the type passed
 * @param $typeID
 */
function get_level_from_type($typeID = -1, $familyID = -1, $subTypeID = -1)
{
	global $DB;
	$sql = "SELECT DISTINCT(level.id), level.trackinglevel FROM {block_bcgt_level} AS level 
	JOIN {block_bcgt_target_qual} AS targetqual ON targetqual.bcgtlevelid = level.id
	JOIN {block_bcgt_type} AS type ON type.id = targetqual.bcgttypeid";
	$params = array();
	if($typeID != -1 || $familyID != -1 || $subTypeID != -1)
	{
		$sql .= " WHERE"; 
	}
	$andUsed = false;
	if($typeID != -1)
	{
		$sql .= " targetqual.bcgttypeid = ?";
        $params[] = $typeID;
		$andUsed = true;
	}
	elseif($familyID != -1)
	{
		$sql .= " type.bcgttypefamilyid = ?";
        $params[] = $familyID;
		$andUsed = true;
	}
	if($subTypeID != -1)
	{
		if($andUsed)
		{
			$sql .= " AND";
		}
		$sql .= " targetqual.bcgtsubtypeid = ?";
        $params[] = $subTypeID;
	}
    $sql .= " ORDER BY level.trackinglevel ASC";      
    
	$levels = $DB->get_records_sql($sql, $params);
	$levelsArray = array();
	if($levels)
	{
		if(count($levels) == 1)
		{
			$level = end($levels);
			$levelObj = new Level($level->id, $level->trackinglevel);
			$levelsArray[] = $levelObj;
		}
		else
		{
			foreach($levels AS $level)
			{
				$levelObj = new Level($level->id, $level->trackinglevel);	
				$levelsArray[$level->id] = $levelObj;
			}
		}
	}
	return $levelsArray;
}

/**
 * This gets the subtypes that have been added in
 * block_bcgt_target_qual for the the type passed in and the level passed in
 * @param $typeID
 */
function get_subtype_from_type($typeID = -1, $levelID = -1, $familyID = -1)
{
	global $DB;
	$sql = "SELECT DISTINCT(subtype.id), subtype.subtype FROM {block_bcgt_subtype} AS subtype 
	JOIN {block_bcgt_target_qual} AS targetqual ON targetqual.bcgtsubtypeid = subtype.id
	JOIN {block_bcgt_type} AS type ON type.id = targetqual.bcgttypeid";
    $params = array();
	if($typeID != -1 || $levelID != -1 || $familyID != -1)
	{
		$sql .= " WHERE";
	}
	$andUsed = false;
	if($typeID != -1)
	{
		$sql .= " targetqual.bcgttypeid = ?";
		$andUsed = true;
        $params[] = $typeID;
	}
	elseif($familyID != -1)
	{
		$sql .= " type.bcgttypefamilyid = ?";
		$andUsed = true;
        $params[] = $familyID;
	}
	if($levelID != -1)
	{
		if($andUsed)
		{
			$sql .= " AND";
		}
		$sql .= " targetqual.bcgtlevelid = ?";
        $params[] = $levelID;
	}
    $sql .= " ORDER BY subtype.subtype ASC";
	$subTypes = $DB->get_records_sql($sql, $params);
	
	$subTypesArray = array();
	if($subTypes)
	{
		if(count($subTypes) == 1)
		{
			$subType = end($subTypes);
			$subTypeObj = new SubType($subType->id, $subType->subtype);
			$subTypesArray[] = $subTypeObj;
		}
		else
		{
			foreach($subTypes AS $subType)
			{
				$subTypeObj = new SubType($subType->id, $subType->subtype);	
				$subTypesArray[$subType->id] = $subTypeObj;
			}
		}
	}
	return $subTypesArray;
}

function get_pathway_from_type($familyID)
{
    
    global $DB;
    
    $sql = "SELECT p.*
            FROM {block_bcgt_pathway_dep} p
            WHERE p.bcgttypefamilyid = ?";
    
    $params = array($familyID);
    
    $records = $DB->get_records_sql($sql, $params);
    $results = array();
    if ($records)
    {
        foreach($records as $record)
        {
            $results[$record->id] = $record->pathway;
        }
    }
    
    return $results;
    
}

function get_pathway_types_from_pathway($pathwayID){
    
    global $DB;
    
    $sql = "SELECT t.*, dt.id as dtid
            FROM {block_bcgt_pathway_type} t
            INNER JOIN {block_bcgt_pathway_dep_type} dt ON dt.bcgtpathwaytypeid = t.id
            WHERE dt.bcgtpathwaydepid = ?";
    $records = $DB->get_records_sql($sql, array($pathwayID));
   
    $results = array();
    if ($records)
    {
        foreach($records as $record)
        {
            $results[$record->dtid] = $record->pathwaytype;
        }
    }
    
    return $results;
    
}

function get_pathway_subtypes_from_type($pathwayTypeID){
    
    global $DB;
    
    $sql = "SELECT s.*
            FROM {block_bcgt_subtype} s
            INNER JOIN {block_bcgt_pathway_subtype} ps ON ps.bcgtsubtypeid = s.id
            WHERE ps.bcgtpathwaydeptypeid = ?";
    $records = $DB->get_records_sql($sql, array($pathwayTypeID));
   
    $results = array();
    if ($records)
    {
        foreach($records as $record)
        {
            $results[$record->id] = $record->subtype;
        }
    }
    
    return $results;
    
}

function get_pathway_dep_type_from_both($pathway, $type)
{
    global $DB;

    $record = $DB->get_record("block_bcgt_pathway_dep_type", array("bcgtpathwaydepid" => $pathway, "bcgtpathwaytypeid" => $type));
    return ($record) ? $record->id : null;
}

function get_pathway_and_type_from_dep_type($pathway){
    global $DB;
    return $DB->get_record_sql("SELECT dt.id, p.id as pathway, t.id as type
                                    FROM {block_bcgt_pathway_dep_type} dt
                                    INNER JOIN {block_bcgt_pathway_dep} p ON p.id = dt.bcgtpathwaydepid
                                    INNER JOIN {block_bcgt_pathway_type} t ON t.id = dt.bcgtpathwaytypeid
                                    WHERE dt.id = ?", array($pathway));
}

/**
 * Returns the qualification level as specfied by the levelID. 
 * If level id is -1 (or not passed in) then all qualification level are returned
 * @param $levelID
 */
function get_qualification_level($levelID = -1)
{
	global $DB;
	$sql = "SELECT * FROM {block_bcgt_level} AS level";
	if($levelID != -1)
	{
		$sql .= " WHERE level.id = ?";	
	}
	
	$levels = $DB->get_records_sql($sql, array($levelID));
	$levelsArray = array();
	if($levels)
	{
		if(count($levels) == 1)
		{
			$level = end($levels);
			$levelObj = new Level($level->id, $level->trackinglevel);
			return $levelObj;
		}
		foreach($levels AS $level)
		{
			$levelObj = new Level($level->id, $level->trackinglevel);
			$levelsArray[] = $levelObj;
		}
	}
	return $levelsArray;
}

/**
 * Returns the qualification subtype as specfied by the subtypeID. 
 * If subtype id is -1 (or not passed in) then all qualification subtypes are returned
 * @param $subtypeID
 */
function get_qualification_subtype($subtypeID = -1)
{
	global $DB;
	$sql = "SELECT * FROM {block_bcgt_subtype} AS subtype";
	if($subtypeID != -1)
	{
		$sql .= " WHERE subtype.id = ?";	
	}
	
	$subTypes = $DB->get_records_sql($sql, array($subtypeID));
	
	$subTypesArray = array();
	if($subTypes)
	{
		if(count($subTypes) == 1)
		{
			$subType = end($subTypes);
			$subTypeObj = new SubType($subType->id, $subType->subtype);
			return $subTypeObj;
		}
		foreach($subTypes AS $subType)
		{
			$subTypeObj = new SubType($subType->id, $subType->subtype);	
			$subTypesArray[$subType->id] = $subTypeObj;
		}
	}
	return $subTypesArray;
}

function bcgt_get_familyID_from_typeID($typeID)
{
    global $DB;
    $sql = "SELECT * FROM {block_bcgt_type} type WHERE id = ?";
    $params = array();
    $params[] = $typeID;
    
    $typeObj = $DB->get_record_sql($sql, $params);
    if($typeObj)
    {
        return $typeObj->bcgttypefamilyid;
    }
    return -1;
}

function bcgt_get_family_for_qual($qualID)
{
    global $DB;
    $sql = "SELECT family.* FROM {block_bcgt_type_family} family 
        JOIN {block_bcgt_type} type ON type.bcgttypefamilyid = family.id 
        JOIN {block_bcgt_target_qual} targetqual ON targetqual.bcgttypeid = type.id 
        JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetqual.id 
        WHERE qual.id = ?";
    $params = array();
    $params[] = $qualID;
    
    $familyObj = $DB->get_record_sql($sql, $params);
    if($familyObj)
    {
        return $familyObj->family;
    }
    return '';
}

/**
 * 
 * @global type $DB
 * @param type $typeID
 * @param type $levelID
 * @param type $subTypeID
 * @param type $search
 * @param type $familyID
 * @param type $notIN
 * @param type $courseID
 * @param type $onCourse
 * @param type $hasStudents
 * @param type $excludeFamilies is an array of string family names
 * @return type
 */
function search_qualification($typeID = -1, $levelID = -1, $subTypeID = -1, $search = '', 
        $familyID = -1, $notIN = null, $courseID = -1, $onCourse = false, $hasStudents = false, 
        $excludeFamilies = array(), $editableByUserID = -1)
{	
	$and = false;
	global $DB; 
	$sql = "
	SELECT distinct(qual.id) AS id, type.type, level.id AS levelid, level.trackinglevel, 
    subtype.subtype, subtype.subtypeshort, qual.name, qual.additionalname,  
	qualunits.countunits, coursequal.countcourse, family.family 
	FROM {block_bcgt_qualification} AS qual 
	JOIN {block_bcgt_target_qual} AS targetQual ON targetQual.id = qual.bcgttargetqualid 
	JOIN {block_bcgt_type} AS type ON type.id = targetQual.bcgttypeid 
	JOIN {block_bcgt_subtype} AS subtype ON subtype.id = targetQual.bcgtsubtypeid 
	JOIN {block_bcgt_level} AS level ON level.id = targetQual.bcgtlevelid
    JOIN {block_bcgt_type_family} AS family ON family.id = type.bcgttypefamilyid
	LEFT OUTER JOIN 
	(
	 SELECT bcgtqualificationid, COUNT(bcgtunitid) AS countunits
	 FROM {block_bcgt_qual_units}
	 group by bcgtqualificationid
	) AS qualunits ON qualunits.bcgtqualificationid = qual.id
	LEFT OUTER JOIN 
	(
	 SELECT bcgtqualificationid, COUNT(courseid) AS countcourse 
	 FROM {block_bcgt_course_qual} AS coursequal
	 GROUP BY bcgtqualificationid
	) AS coursequal ON coursequal.bcgtqualificationid = qual.id";
    if($hasStudents)
    {
        $sql .= " JOIN {block_bcgt_user_qual} userqual 
            ON userqual.bcgtqualificationid = qual.id 
            JOIN {role} role ON role.id = userqual.roleid AND role.shortname = ?";
    }
    if($onCourse)
    {
        $sql .= " JOIN {block_bcgt_course_qual} coursequalforce ON coursequalforce.bcgtqualificationid = qual.id ";
    }
    if($editableByUserID != -1)
    {
        //then we want to check coursequal
        //course, context etc for the quals they can edit
        $sql .= " JOIN {block_bcgt_course_qual} teachcoursequal 
            JOIN {context} context ON context.instanceid = teachcoursequal.courseid 
            JOIN {role_assignments} roleass ON roleass.contextid = context.id 
            JOIN {role} role ON role.id = roleass.roleid";
    }
	$params = array();
    if($hasStudents)
    {
        $params[] = 'student';
    }
	if($typeID != -1 || $levelID != -1 || $subTypeID != -1 || $search != '' 
            || $familyID != -1 || $notIN || $courseID != -1 || count($excludeFamilies) != 0 
            || $editableByUserID != -1)
	{
		//then we are searching
		$sql .= " WHERE";
		
		if($typeID != -1)
		{
			$sql .= " type.id = ?";
			$and = true;
            $params[] = $typeID;
		}
		if($levelID != -1)
		{
			if($and)
			{
				$sql .= " AND";
			}
			$sql .= " level.id = ?";	
			$and = true;
            $params[] = $levelID;
		}
		if($subTypeID != -1)
		{
			if($and)
			{
				$sql .= ' AND';
			}
			$sql .= " subtype.id = ?";
			$and = true;
            $params[] = $subTypeID;
		}
		if($search != '')
		{
			if($and)
			{
				$sql .= ' AND';
			}
			$sql .= " qual.name LIKE ?";
			$and = true;
            $params[] = '%'.$search.'%';
		}
		if($familyID != -1)
		{
			if($and)
			{
				$sql .= ' AND';
			}
			$sql .= " type.bcgttypefamilyid = ?";
            $params[] = $familyID;
            $and = true;
		}
        if($notIN)
        {
            if($and)
            {
                $sql  .= ' AND';
            }
            $sql .= " qual.id NOT IN (";
            $count = 0;
            foreach($notIN AS $qualID)
            {
                $count++;
                if($count != 1)
                {
                    $sql .= ',';
                }
                $sql .= "?";
                $params[] = $qualID;
            }
            $sql .= ")";
            $and = true;
        }
        if($courseID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $sql .= ' qual.id';
            if($onCourse)
            {
                $sql .= ' IN';
            }
            else
            {
               $sql .= ' NOT IN'; 
            }
            $sql .= ' (SELECT bcgtqualificationid FROM {block_bcgt_course_qual} WHERE courseid = ?)';
            $params[] = $courseID;
            $and = true;
        }
        if($excludeFamilies && count($excludeFamilies) != 0)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $count = 0;
            foreach($excludeFamilies AS $family)
            {
                $count++;
                if($count != 1)
                {
                    $sql .= ' AND';
                }
                $sql .= ' family.family != ?';
                $params[] = $family;
            }
            $and = true;
        }
        if($editableByUserID != -1)
        {
            //then we want to check coursequal
            //course, context etc for the quals they can edit
            if($and)
            {
                $sql .= ' AND';
            }
            $and = true;
            $sql .= ' role.shortname = ? AND roleass.userid = ?';
            $params[] = 'editingteacher';
            $params[] = $editableByUserID;
        }
	}
	$sql .= " ORDER BY family.family ASC, level.trackinglevel ASC, subtype.subtype ASC, qual.name ASC";
    $records = $DB->get_records_sql($sql, $params);
    // Bespoke cannot be done like this, there are no types, levels, etc... to join
    // Search bespoke quals as well
    
    if(($excludeFamilies && !in_array('Bespoke', $excludeFamilies)) || (!$excludeFamilies))
    {
        if ($familyID == 1 || $familyID == -1)
        {
            $sql = "SELECT q.id, b.displaytype, b.subtype, b.level, q.name, q.additionalname, 1 as isbespoke, qualunits.countunits, coursequal.countcourse
                    FROM {block_bcgt_bespoke_qual} b
                    INNER JOIN {block_bcgt_qualification} q ON q.id = b.bcgtqualid
                    LEFT OUTER JOIN 
                    (
                        SELECT bcgtqualificationid, COUNT(bcgtunitid) AS countunits
                        FROM {block_bcgt_qual_units}
                        group by bcgtqualificationid
                    ) AS qualunits ON qualunits.bcgtqualificationid = q.id
                    LEFT OUTER JOIN 
                    (
                        SELECT bcgtqualificationid, COUNT(courseid) AS countcourse 
                        FROM {block_bcgt_course_qual} AS coursequal
                        GROUP BY bcgtqualificationid
                    ) AS coursequal ON coursequal.bcgtqualificationid = q.id
                    WHERE (q.name LIKE ? OR b.displaytype LIKE ? OR b.subtype LIKE ?)";
            $params = array('%'.$search.'%', '%'.$search.'%', '%'.$search.'%');
            if($courseID > 0)
            {
                $sql .= " AND ( ? ";
                if($onCourse)
                {
                    $sql .= ' IN ';
                }
                else
                {
                    $sql .= ' NOT IN ';
                }
                $sql .= "( SELECT sq.courseid
                        FROM {block_bcgt_course_qual} sq
                        WHERE sq.bcgtqualificationid = q.id ) ) ";
                $params[] = $courseID;
            }
            $sql .= " ORDER BY b.displaytype ASC, b.level ASC, b.subtype ASC, q.name ASC";
            
            $bespoke = $DB->get_records_sql($sql, $params);
            
            if ($bespoke)
            {
                foreach($bespoke as $bspk)
                {
                    if (!isset($records[$bspk->id])){
                        $bspk->trackinglevel = '';
                        $bspk->family = '';
                        $records[$bspk->id] = $bspk;
                    }
                }
            }

        }
    }    
    
    
        
    return $records;
}

function bcgt_search_system($qualID = -1, $courseID = -1, $search = '', $searchParams = '', $qualExcludes = array())
{
    global $DB;
    $sql = '';
    $params = array();
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_courses_with_quals($qualID = -1, $excludeFamilies = array(), $courseSearch = '')
{
    $and = false;
    global $DB;
    $sql = "SELECT distinct(course.id), course.* FROM {course} course 
        JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = course.id";
    if($excludeFamilies && count($excludeFamilies) != 0)
    {
        $sql .= ' JOIN {block_bcgt_qualification} qual ON qual.id = coursequal.bcgtqualificationid 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid';
    }
    $params = array();
    if($qualID != -1 || ($excludeFamilies && count($excludeFamilies) != 0) || $courseSearch != '')
    {
        $sql .= ' WHERE';
    }
    if($qualID != -1)
    {
        $sql .= " coursequal.bcgtqualificationid = ?";
        $params[] = $qualID;
        $and = true;
    }
    if($excludeFamilies && count($excludeFamilies) != 0)
    {
        if($and)
        {
            $sql .= ' AND';
        }
        $count = 0;
        foreach($excludeFamilies AS $family)
        {
            $count++;
            if($count != 1)
            {
                $sql .= ' AND';
            }
            $sql .= ' family.family != ?';
            $params[] = $family;
        }
        $sql .= '';
        $and = true;
        
    }
    if($courseSearch != '')
    {
        if($and)
        {
            $sql .= ' AND';
        }
        $sql .= '(';
        $sql .= ' course.shortname LIKE ? OR course.shortname LIKE ?';
        $params[] = '%'.$courseSearch.'%';
        $params[] = '%'.$courseSearch.'%';
        $coursesSearches = explode(" ", $courseSearch);
        if($coursesSearches)
        {
            foreach($coursesSearches AS $search)
            {
                $sql .= ' OR course.shortname LIKE ? OR course.shortname LIKE ?';
                $params[] = '%'.$search.'%';
                $params[] = '%'.$search.'%';
            }
        }
        $sql .= ')';
        $and = true;
    }
    $sql .= " ORDER BY shortname ASC, fullname ASC";
    return $DB->get_records_sql($sql, $params);
}

/**
 * 
 * @global type $DB
 * @param type $unitTypeID
 * @param type $qualID
 * @param type $search
 * @param type $levelID
 * @param type $subTypeID
 * @param type $in
 * @param type $qualTypeID
 * @param type $uniqueID
 * @param type $name
 * @param type $unitFamilyID
 * @param type $qualFamilyID
 * @param type $unitLevelID
 * @param type $qualIDExclude
 * @param type $qualSearch
 * @return type
 */
function search_unit($unitTypeID = -1, $qualID = -1, $search = '', $levelID = -1, 
        $subTypeID = -1, $in = '', $qualTypeID = -1, 
        $uniqueID = '', $name = '', $unitFamilyID = -1, $qualFamilyID = -1, 
        $unitLevelID = -1, $qualIDExclude = -1, $qualSearch = '')
{
	$and = false;
	
	global $DB;
	$sql = "SELECT DISTINCT(unit.id), unit.*, unitLevel.trackinglevel as unitlevel, 
        unitLevel.id AS unitlevelid, unitFamily.family 
        FROM {block_bcgt_unit} AS unit 
	LEFT OUTER JOIN {block_bcgt_qual_units} AS qualUnits ON qualUnits.bcgtunitid = unit.id 
	LEFT OUTER JOIN {block_bcgt_qualification} AS qual ON qual.id = qualUnits.bcgtqualificationid 
	LEFT OUTER JOIN {block_bcgt_target_qual} AS targetQual ON targetQual.id = qual.bcgttargetqualid 
	LEFT OUTER JOIN {block_bcgt_type} AS type ON type.id = targetQual.bcgttypeid 
	LEFT OUTER JOIN {block_bcgt_subtype} AS subtype ON subtype.id = targetQual.bcgtsubtypeid 
	LEFT OUTER JOIN {block_bcgt_level} AS level ON level.id = targetQual.bcgtlevelid 
	LEFT OUTER JOIN {block_bcgt_type} AS unitType ON unitType.id = unit.bcgttypeid
	LEFT OUTER JOIN {block_bcgt_level} AS unitLevel on unitLevel.id = unit.bcgtlevelid
    LEFT OUTER JOIN {block_bcgt_type_family} AS unitFamily ON unitFamily.id = unitType.bcgttypefamilyid ";
    $params = array();
    if($unitTypeID != -1 || $qualID != -1 || $levelID != -1 || 
	$subTypeID != -1 || $search != '' || 
	$in != '' || $qualTypeID != -1 || $uniqueID != '' || 
	$name != '' || $unitFamilyID != -1 || $qualFamilyID != -1 || $unitLevelID != -1 
            || $qualIDExclude != -1 || $qualSearch != '')
	{
		//then we are searching
		$sql .= " WHERE";
		
		if($unitTypeID != -1)
		{
			$sql .= " unit.bcgttypeid = ?";
			$and = true;
            $params[] = $unitTypeID;
		}
		if($qualID != -1)
		{
			if($and)
			{
				$sql .= " AND";
			}
			$sql .= "qual.id = ?";
			$and = true;
            $params[] = $qualID;
		}
		if($levelID != -1)
		{
			if($and)
			{
				$sql .= " AND";
			}
			$sql .= " level.id = ?";	
			$and = true;
            $params[] = $levelID;
		}
		if($subTypeID != -1)
		{
			if($and)
			{
				$sql .= ' AND';
			}
			$sql .= " subtype.id = ?";
			$and = true;
            $params[] = $subTypeID;
		}
		if($search != '')
		{
			if($and)
			{
				$sql .= ' AND';
			}
			$sql .= " (unit.name LIKE ? OR unit.uniqueid LIKE ? OR unit.details LIKE ?)";
			$and = true;
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
		}	
		if($in != '')
		{
			if($and)
			{
				$sql .= ' AND';
			}
			$sql .= " unit.id NOT IN (";
            $inSplit = explode(',', $in);
            $count = 0;
            foreach($inSplit AS $split)
            {
                $count++;
                if($count != 1)
                {
                    $sql .= ',';
                }
                $sql .= '?';
                
                $split = preg_replace("/[^0-9]/", "", $split);
                
                $params[] = $split;
            }
			$and = true;
            $sql .= ')';
		}
		if($qualTypeID != -1)
		{
			if($and)
			{
				$sql .= ' AND';
			}
			$sql .= " type.id = ?";
			$and = true;
            $params[] = $qualTypeID;
		}
		if($uniqueID != '')
		{
			if($and)
			{
				$sql .= ' AND';
			}
			$sql .= " unit.uniqueid = ?";
			$and = true;
            $params[] = $uniqueID;
		}
		if($name != '')
		{
			if($and)
			{
				$sql .= ' AND';
			}
			$sql .= " units.name = ?";
			$and = true;
            $params[] = $name;
		}
		if($qualFamilyID != -1)
		{
			if($and)
			{
				$sql .= ' AND';
			}
			$sql .= " type.bcgttypefamilyid = ?";
			$and = true;
            $params[] = $qualFamilyID;
		}
		if($unitFamilyID != -1)
		{
			if($and)
			{
				$sql .= ' AND';
			}
			$sql .= " unitType.bcgttypefamilyid = ?";
			$and = true;
            $params[] = $unitFamilyID;
		}
		if($unitLevelID != -1)
		{
			if($and)
			{
				$sql .= ' AND';
			}
			$sql .= " unit.bcgtlevelid = ?";
			$and = true;
            $params[] = $unitLevelID;
		}
        if($qualIDExclude != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $sql .= " unit.id NOT IN (SELECT bcgtunitid 
                FROM {block_bcgt_qual_units} WHERE bcgtqualificationid = ?)";
                $params[] = $qualIDExclude;
        }
        if($qualSearch != '')
        {
            if($and)
			{
				$sql .= ' AND';
			}
			$sql .= " qual.name LIKE ?";
			$and = true;
            $params[] = '%'.$qualSearch.'%';
        }
	}
        
	$results = $DB->get_records_sql($sql, $params, 0 , 50);
    
    // Search bespoke quals as well
    if ($unitFamilyID == 1 || $unitFamilyID <= 0){
                
        $sql = "SELECT u.*, b.displaytype, b.level, 1 as isbespoke
                FROM {block_bcgt_bespoke_unit} b
                INNER JOIN {block_bcgt_unit} u ON u.id = b.bcgtunitid
                WHERE u.name LIKE ? OR b.displaytype LIKE ?
                ORDER BY b.displaytype ASC, b.level ASC, u.name ASC";
        $bespoke = $DB->get_records_sql($sql, array('%'.$search.'%', '%'.$search.'%'));
                        
        if ($bespoke)
        {
            foreach($bespoke as $bspk)
            {
                if (!isset($results[$bspk->id])){
                    $bspk->family = '';
                    $bspk->unitlevel = '';
                    $results[$bspk->id] = $bspk;
                }
            }
        }
        
    }    
    
    
    
    return $results;
}

function get_users_not_on_qual($qualID, $roleID, $search = '')
{
    global $DB;
    $sql = "SELECT u.* FROM {user} u
        WHERE u.id NOT IN 
        (SELECT userid FROM {block_bcgt_user_qual} WHERE roleid = ? 
        AND bcgtqualificationid = ?)";
    $params = array($roleID,$qualID);
    if($search != '')
    {
        $sql .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? 
            OR u.email LIKE ? OR u.username LIKE ?)";
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
    }
    return $DB->get_records_sql($sql, $params, 0, 100);
}

/**
 * Seraches for a user
 * @global type $DB
 * @param type $search
 * @return type
 */
function get_users_bcgt($search = '', $userID = -1)
{
    global $DB;
    $sql = "SELECT u.* FROM {user} u";
    $params = array();
    if($search != '' || $userID != -1)
    {
        $sql .= " WHERE";
    }
    $and = false;
    if($search != '')
    {
        $sql .= " (u.firstname LIKE ? OR u.lastname LIKE ? 
            OR u.email LIKE ? OR u.username LIKE ?)";
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $and = true;
    }
    if($userID != -1)
    {
        if($and)
        {
            $sql .= ' AND';
        }
        $sql .= ' u.id = ?';
        $params[] = $userID;
    }
    return $DB->get_records_sql($sql, $params, 0, 100);
}

/**
 * Find the users in the database that are not assigned to the user passed in. 
 * @global type $DB
 * @param type $roleID
 * @param type $userID
 * @param type $search
 * @return type
 */
function get_users_non_users($roleID, $userID, $search = '')
{
    global $DB;
    $sql = 'SELECT u.* FROM {user} u
        WHERE u.id NOT IN (SELECT user2.id FROM {user} user2
        JOIN {block_bcgt_user_assign} assign ON assign.assigneeuserid = user2.id 
        WHERE assign.roleid = ? AND assign.userid = ?) AND u.id <> ? ';
    $params = array($roleID, $userID, $userID);
    if($search != '')
    {
        $sql .= ' AND (u.lastname LIKE ? 
            OR u.firstname LIKE ? OR u.username LIKE ? 
            OR u.username LIKE ?)';
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
    }
    return $DB->get_records_sql($sql, $params);
}

function add_users_users($userIDs, $roleID, $userID)
{
    global $DB;
    foreach($userIDs AS $idAdd)
    {
        $stdObj = new stdClass();
        $stdObj->userid = $userID;
        $stdObj->roleid = $roleID;
        $stdObj->assigneeuserid = $idAdd;
        $DB->insert_record('block_bcgt_user_assign', $stdObj);
    }
}

function remove_users_users($userIDs, $roleID, $userID)
{
    global $DB;
    foreach($userIDs AS $idRemove)
    {
        $DB->delete_records('block_bcgt_user_assign', array('roleid'=>$roleID, 
            'userid'=>$userID, 'assigneeuserid'=>$idRemove));
    }
}

/**
 * Finds the users in the database that have been assigned under the role
 * to the user passed in
 * @global type $DB
 * @param type $roleID
 * @param type $userID
 * @param type $search
 * @return type
 */
function get_users_users($roleID, $userID, $search = '')
{
    global $DB;
    $sql = 'SELECT u.* FROM {user} u 
        JOIN {block_bcgt_user_assign} assign ON assign.assigneeuserid = u.id 
        WHERE assign.roleid = ? AND assign.userid = ?';
    $params = array($roleID, $userID);
    if($search != '')
    {
        $sql .= ' AND (u.lastname LIKE ? 
            OR u.firstname LIKE ? OR u.username LIKE ? 
            OR u.username LIKE ?)';
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
    }
    return $DB->get_records_sql($sql, $params);

    
}

/**
 * Find the role from the database and then gets the quals for that user.  
 * @global type $DB
 * @param type $userID
 * @param type $role
 * @param string $searc
 */
function get_role_quals($userID, $role, $search = '', $familyID = -1, $courseID = -1)
{
    global $DB;
    $sql = "SELECT id FROM {role} WHERE ";
    $params = array();
    if(is_array($role))
    {
        $count = 0;
        //then we split it.
        foreach($role AS $r)
        {
            $count++;
            if($count != 1)
            {
                $sql .= ' OR';
            }
            $sql .= ' shortname = ?';
            $params[] = $r;
        }   
    }
    else
    {
        $sql .= " shortname = ?";
        $params[] = $role;
    }
    $roleDB = $DB->get_records_sql($sql, $params);
    if($roleDB)
    {
        $roles = array();
        foreach($roleDB AS $role)
        {
            $roles[] = $role->id;
        }
        return get_users_quals($userID, $roles, $search, $familyID, $courseID);
    } 
    return false;
}

function bcgt_get_role($role)
{
    global $DB;
    return $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = ?', array($role));
}

function get_users_credits($userID, $qualID = false)
{
    
    global $DB;
    
    $sql = "SELECT SUM(u.credits) as ttl
            FROM {block_bcgt_qualification} q
            INNER JOIN {block_bcgt_user_qual} uq ON uq.bcgtqualificationid = q.id
            INNER JOIN {block_bcgt_qual_units} qu ON qu.bcgtqualificationid = q.id
            INNER JOIN {block_bcgt_unit} u ON u.id = qu.bcgtunitid
            INNER JOIN {block_bcgt_user_unit} uu ON (uu.bcgtunitid = u.id AND uu.userid = uq.userid)
            WHERE uq.userid = ?";
    
    $array = array($userID);
    
    if ($qualID){
        $sql .= " AND uu.bcgtqualificationid = ?";
        $array[] = $qualID;
    }
    
    $check = $DB->get_record_sql($sql, $array);
    return ($check) ? $check->ttl : 0;
    
}

function get_users_expected_credits($userID)
{
    
    global $DB;
    
    $quals = get_users_quals($userID, 5);
    $credits = 0;
    $load = new stdClass();
    $load->loadLevel = Qualification::LOADLEVELMIN;
    
    if ($quals)
    {
        foreach($quals as $qual)
        {
            $qualification = Qualification::get_qualification_class_id($qual->id, $load);
            if ($qualification)
            {
                $check = $DB->get_record("block_bcgt_target_qual_att", array("bcgttargetqualid" => $qualification->get_target_qual_id(), "name" => SubType::DEFAULTNUMBEROFCREDITSNAME));
                if ($check)
                {
                    $credits += $check->value;
                }
            }
        }
    }
    
    return $credits;
        
}

/**
 * Gets the users quals. 
 * @global type $DB
 * @param type $userID
 * @param type $roleID
 * @param type $search
 * @return type
 */
function get_users_quals($userID, $roleID = -1, $search = '', $familyID = -1, $courseID = -1, $excludeFamilies = array())
{
    global $DB;
    $sql = "SELECT distinct(qual.id), qual.*, family.family, 
        level.trackinglevel, subtype.subtype, level.id as levelid, subtype.subtypeshort, type.type, targetQual.id as bcgttargetqualid
        FROM {block_bcgt_user_qual} userQual 
        JOIN {block_bcgt_qualification} qual ON qual.id = userQual.bcgtqualificationid
        JOIN {block_bcgt_target_qual} targetQual ON targetQual.id = qual.bcgttargetqualid
        JOIN {block_bcgt_type} type ON type.id = targetQual.bcgttypeid
        JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid
        JOIN {block_bcgt_level} level ON level.id = targetQual.bcgtlevelid 
        JOIN {block_bcgt_subtype} subtype ON subtype.id = targetQual.bcgtsubtypeid";
        if($courseID != -1)
        {
            $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id";
        }
        $sql .= " WHERE userQual.userid = ?";
        $params = array($userID);
        if($roleID != -1)
        {
            $sql .= ' AND (';
            if(is_array($roleID))
            {
                $count = 0;
                //then we split it.
                foreach($roleID AS $role)
                {
                    $count++;
                    if($count != 1)
                    {
                        $sql .= ' OR';
                    }
                    $sql .= ' userQual.roleid = ?';
                    $params[] = $role;
                }   
            }
            else
            {
                $sql .= " userQual.roleid = ?";
                $params[] = $roleID;
            }
            $sql .= ')';
        }
        if($search != '')
        {
            $sql .= ' AND qual.name LIKE ?';
            $params[] = '%'.$search.'%';
        }
        if($familyID != -1)
        {
            if (is_array($familyID))
            {
                $qMarks = str_repeat('?,', count($familyID) - 1) . '?';
                $sql .= ' AND family.id IN (' . $qMarks . ')';
                foreach($familyID as $fID)
                {
                    $params[] = $fID;
                }
            }
            else
            {
                $sql .= ' AND family.id = ?';
                $params[] = $familyID;
            }
        }
        if($courseID != -1)
        {
            $sql .= ' AND coursequal.courseid = ?';
            $params[] = $courseID;
        }
        if($excludeFamilies && count($excludeFamilies) != 0)
        {
            $sql .= ' AND';
            $count = 0;
            foreach($excludeFamilies AS $family)
            {
                $count++;
                if($count != 1)
                {
                    $sql .= ' AND';
                }
                $sql .= ' family.family != ?';
                $params[] = $family;
            }
            $sql .= '';
            $and = true;
        }
        $sql .= ' ORDER BY family.family DESC, subtype.subtype ASC, qual.name ASC';
    $records = $DB->get_records_sql($sql, $params);
    // Bespoke cannot be done like this, there are no types, levels, etc... to join
    // Search bespoke quals as well  
    if ($familyID == 1 || $familyID < 0 && !in_array('Bespoke', $excludeFamilies))
    {
    
        $sql = "SELECT q.*, b.displaytype, b.subtype, b.level, 1 as isbespoke
                FROM {block_bcgt_bespoke_qual} b
                INNER JOIN {block_bcgt_qualification} q ON q.id = b.bcgtqualid
                INNER JOIN {block_bcgt_user_qual} userQual ON userQual.bcgtqualificationid = q.id
                LEFT OUTER JOIN 
                (
                    SELECT bcgtqualificationid, COUNT(bcgtunitid) AS countunits
                    FROM {block_bcgt_qual_units}
                    group by bcgtqualificationid
                ) AS qualunits ON qualunits.bcgtqualificationid = q.id
                LEFT OUTER JOIN 
                (
                    SELECT bcgtqualificationid, COUNT(courseid) AS countcourse 
                    FROM {block_bcgt_course_qual} AS coursequal
                    GROUP BY bcgtqualificationid
                ) AS coursequal ON coursequal.bcgtqualificationid = q.id
                WHERE userQual.userid = ?";
        
                $params = array($userID);
        
                if($roleID != -1)
                {
                    $sql .= ' AND (';
                    if(is_array($roleID))
                    {
                        $count = 0;
                        //then we split it.
                        foreach($roleID AS $role)
                        {
                            $count++;
                            if($count != 1)
                            {
                                $sql .= ' OR';
                            }
                            $sql .= ' userQual.roleid = ?';
                            $params[] = $role;
                        }   
                    }
                    else
                    {
                        $sql .= " userQual.roleid = ?";
                        $params[] = $roleID;
                    }
                    $sql .= ')';
                }
                if($search != '')
                {
                    $sql .= ' AND q.name LIKE ?';
                    $params[] = '%'.$search.'%';
                }
//                if($familyID != -1)
//                {
//                    $sql .= ' AND family.id = ?';
//                    $params[] = $familyID;
//                }
        
        $bespoke = $DB->get_records_sql($sql, $params);
                        
        if ($bespoke)
        {
            foreach($bespoke as $bspk)
            {
                if (!isset($records[$bspk->id])){
                    $bspk->family = '';
                    $records[$bspk->id] = $bspk;
                }
            }
        }
        
    }
        
    
    
    return $records;
}

function bcgt_get_users_courses($userID, $roleID, $hasQual = false, $qualID = -1, $excludeFamilies = array())
{
    global $DB;
    $sql = "SELECT distinct course.* FROM {course} course
         JOIN {context} context ON context.instanceid = course.id
            JOIN {role_assignments} roleass ON roleass.contextid = context.id 
            JOIN {user} u ON u.id = roleass.userid 
            JOIN {role} role ON role.id = roleass.roleid";
    if($hasQual || $qualID != -1 || ($excludeFamilies && count($excludeFamilies) != 0))
    {
        $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = course.id";
        if($excludeFamilies && count($excludeFamilies) != 0)
        {
            $sql .= ' LEFT JOIN {block_bcgt_qualification} qual ON qual.id = coursequal.bcgtqualificationid 
                LEFT JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
                LEFT JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
                LEFT JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid';
        }
    }
    $params = array($userID, $roleID);
    $sql .= " WHERE u.id = ? AND role.id = ?";
    if($qualID != -1)
    {
        $sql .= " AND coursequal.bcgtqualificationid = ?";
        $params[] = $qualID;
    }
    if($excludeFamilies && count($excludeFamilies) != 0)
    {
        foreach($excludeFamilies AS $family)
        {
            $sql .= ' AND (family.family != ? OR family.family IS NULL)';
            $params[] = $family;
        }
    }
    $sql .= " ORDER BY shortname ASC, fullname ASC";
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_users_course_roles($userID, $courseID)
{
    global $DB;
    $sql = "SELECT distinct(role.id), role.shortname 
        FROM {role} role 
        JOIN {role_assignments} roleass ON roleass.roleid = role.id 
        JOIN {context} context ON context.id = roleass.contextid 
        WHERE roleass.userid = ? AND context.instanceid = ? ORDER BY role.shortname ASC";
    $params = array($userID, $courseID);
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_users_courses_any_role($userID, $hasQual = false)
{
    global $DB;
    $sql = "SELECT distinct(course.id), course.shortname, course.fullname  
        FROM {course} course
         JOIN {context} context ON context.instanceid = course.id
            JOIN {role_assignments} roleass ON roleass.contextid = context.id 
            JOIN {user} u ON u.id = roleass.userid 
            JOIN {role} role ON role.id = roleass.roleid";
    if($hasQual)
    {
        $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = course.id";
    }
    $sql .= " WHERE u.id = ?";
    $params = array($userID);
    $sql .= " ORDER BY shortname ASC, fullname ASC";
    return $DB->get_records_sql($sql, $params);
}

function bcgt_is_user_on_course($userID, $courseID){
    
    global $DB;
    
    $sql = "SELECT r.id
            FROM {role_assignments} r
            INNER JOIN {context} x ON r.contextid = x.id
            WHERE r.userid = ? AND x.contextlevel = ? AND x.instanceid = ?";
    $params = array($userID, CONTEXT_COURSE, $courseID);
    return $DB->get_record_sql($sql, $params);
    
}

/**
 * Gets the users courses. 
 * @global type $DB
 * @param type $userID
 * @param type $hasQual
 * @return type
 */
function bcgt_get_users_course_access($userID, $hasQual = false, $includeHidden = false)
{
    global $DB;
    $sql = "SELECT distinct(roleass.id), course.id as courseid, course.shortname, course.fullname, 
        role.shortname AS role
        FROM {course} course
         JOIN {context} context ON context.instanceid = course.id
            JOIN {role_assignments} roleass ON roleass.contextid = context.id 
            JOIN {user} u ON u.id = roleass.userid 
            JOIN {role} role ON role.id = roleass.roleid 
            JOIN {course_categories} category ON category.id = course.category ";
    if($hasQual)
    {
        $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = course.id";
    }
    $sql .= ' WHERE u.id = ?';
    $params = array($userID);
    if(!$includeHidden)
    {
       $sql .= ' AND course.visible = ? AND category.visible = ?';
       $params[] = 1;
       $params[] = 1;
    }

    $sql .= " ORDER BY shortname ASC, fullname ASC";
    $records = $DB->get_records_sql($sql, $params);
    return $records;
}

function bcgt_get_users_assessments($userID, $roleID, $search, 
        $qualID = -1, $courseID = -1, $groupID = -1)
{
    global $DB;
    $sql = "SELECT distinct(project.id), project.* FROM {block_bcgt_project} project 
        JOIN {block_bcgt_activity_refs} activityrefs ON activityrefs.bcgtprojectid = project.id 
        JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = activityrefs.bcgtqualificationid"; 

    if($courseID != -1)
    {
        $sql .= ' JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = activityrefs.bcgtqualificationid ';
    }
    if($groupID != -1)
    {
        $sql .= ' JOIN {groups_members} members ON members.userid = userqual.userid';
    }
    $sql .= ' WHERE userqual.roleid = ? AND userqual.userid = ?';
    $params = array($roleID, $userID);
    if($search != '')
    {
        $sql .= ' AND (project.name LIKE ?';
        $params[] = '%'.$search.'%';
        $searchSplit = explode(' ', $search);
        if($searchSplit)
        {
            foreach($searchSplit AS $split)
            {
                $sql .= ' OR project.name LIKE ?';
                $params[] = '%'.$split.'%';
            }
        }
        $sql .= ')';
    }
    if($qualID != -1)
    {
        $sql .= ' AND activityrefs.bcgtqualificationid = ?';
        $params[] = $qualID;
    }
    if($courseID != -1)
    {
        $sql .= ' AND coursequal.courseid = ?';
        $params[] = $courseID;
    }
    if($groupID != -1)
    {
        $sql .= ' members.groupid = ?';
        $params[] = $groupID;
    }
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_users_activities($userID = -1, $roleID = -1, $qualID = -1, $courseID = -1, $search = '', $cmID = -1)
{
    global $DB;
    $sql = "SELECT distinct(cmods.id), items.courseid, items.itemname as name, 
        items.itemtype as type, items.itemmodule as module, items.iteminstance, 
        cmods.module AS cmodule, cmods.section, cmods.groupingid, cmods.instance AS instanceid 
        FROM {grade_items} items 
        JOIN {modules} mods ON mods.name = items.itemmodule
        JOIN {course_modules} cmods ON cmods.module = mods.id";
        if($courseID != -1 && $courseID != SITEID)
        {
            $sql .= " AND cmods.course = ?";
        }
        $sql .= " AND cmods.instance = items.iteminstance
        JOIN {block_bcgt_activity_refs} refs ON refs.coursemoduleid = cmods.id
        JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = refs.bcgtqualificationid";   
    $sql .= " WHERE items.itemtype=? AND mods.visible = ? AND cmods.visible = ?";
    $params = array();
    if($courseID != -1 && $courseID != SITEID)
    {
        $params[] = $courseID;
    }
    $params[] = 'mod';
    $params[] = 1;
    $params[] = 1;
    if($userID != -1)
    {
        $sql .= " AND userqual.userid = ?";
        $params[] = $userID;
    }
    if($roleID != -1)
    {
        $sql .= " AND userqual.roleid = ?";
        $params[] = $roleID;
    }
    if($courseID != -1 && $courseID != SITEID)
    {
        $sql .= " AND items.courseid = ?";
        $params[] = $courseID;
    }
    if($qualID != -1)
    {
        $sql .= " AND refs.bcgtqualificationid = ?";
        $params[] = $qualID;
    }
    if($search != '')
    {
        $sql .= ' AND (items.itemname LIKE ?';
        $params[] = '%'.$search.'%';
        $searchSplit = explode(' ', $search);
        if($searchSplit)
        {
            foreach($searchSplit AS $split)
            {
                $sql .= ' OR items.itemname LIKE ?';
                $params[] = '%'.$split.'%';
            }
        }
        $sql .= ')';
    }
    if($cmID != -1)
    {
        $sql .= ' AND cmods.id = ?';
        $params[] = $cmID;
    }
                
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_users_units($userID, $roleID, $search, $qualID = -1)
{
    global $DB;
    $sql = "SELECT distinct(unit.id), unit.* FROM {block_bcgt_unit} unit 
        JOIN {block_bcgt_qual_units} qualunits ON qualunits.bcgtunitid = unit.id 
        JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qualunits.bcgtqualificationid 
        WHERE userqual.roleid = ? AND userqual.userid = ?";
    $params = array($roleID, $userID);
    if($search != '')
    {
        $sql .= ' AND (unit.name LIKE ? OR unit.uniqueid LIKE ?';
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $searchSplit = explode(' ', $search);
        if($searchSplit)
        {
            foreach($searchSplit AS $split)
            {
                $sql .= ' OR unit.name LIKE ? OR unit.uniqueid LIKE ?';
                $params[] = '%'.$split.'%';
                $params[] = '%'.$split.'%';
            }
        }
        $sql .= ')';
    }
    if($qualID != -1)
    {
        $sql .= ' AND qualunits.bcgtqualificationid = ?';
        $params[] = $qualID;
    }
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_users_users($userID, $theirRoleID, $userRoleID, $search = '')
{
    global $DB;
    $sql = "SELECT distinct(u.id), u.* FROM {user} u
        JOIN {role_assignments} roleass ON roleass.userid = u.id 
        JOIN {context} context ON context.id = roleass.contextid 
        JOIN {course} course ON course.id = context.instanceid 
        JOIN {block_bcgt_user_qual} userqual ON userqual.userid = u.id
        JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = userqual.bcgtqualificationid
        JOIN {block_bcgt_user_qual} userqualteach ON userqualteach.bcgtqualificationid = coursequal.bcgtqualificationid
        WHERE userqual.roleid = ? AND userqualteach.userid = ? AND";
    $params = array($userRoleID, $userID);
    if(is_array($theirRoleID))
    {
        $count = 0;
        $sql .= ' (';
        foreach($theirRoleID AS $roleID)
        {
            $count++;
            $sql .= ' userqualteach.roleid = ?';
            if($count != count($theirRoleID))
            {
                $sql .= ' OR';
            }
            $params[] = $roleID;
        }
        $sql .= ')';
    }
    else
    {
        $params[] = $theirRoleID;
        $sql .= ' userqualteach.roleid = ?';
    }
    
    
    if($search != '')
    {
        $sql .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? 
                OR u.email LIKE ? OR u.username LIKE ? ";
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $searchSplit = explode(' ', $search);
        if($searchSplit)
        {
            foreach($searchSplit AS $split)
            {
                $sql .= ' OR u.firstname LIKE ? OR u.lastname LIKE ? 
            OR u.email LIKE ? OR u.username LIKE ? ';
                $params[] = '%'.$split.'%';
                $params[] = '%'.$split.'%';
                $params[] = '%'.$split.'%';
                $params[] = '%'.$split.'%';
            }
        }
        $sql .= ')';
    }
    $sql .= 'ORDER BY u.lastname ASC, u.firstname ASC';
    return $DB->get_records_sql($sql, $params);
}

function add_qual_user($qualIDs, $roleID, $userID, $role = 'student')
{
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
    global $DB;
    foreach($qualIDs AS $qualID)
    {
        if(!Qualification::check_user_on_qual($userID, $roleID, $qualID))
        {
            $userQual = new stdClass();
            $userQual->bcgtqualificationid = $qualID;
            $userQual->userid = $userID;
            $userQual->roleid = $roleID;
            $DB->insert_record('block_bcgt_user_qual', $userQual);
            
            if($role == 'student')
            {
                $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
                if($qualification)
                {
                    $qualification->add_single_student_units($userID);
                }
            }
        }
    }
}

function remove_qual_user($qualIDs, $roleID, $userID, $role = 'student')
{
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
    global $DB;
    foreach($qualIDs AS $qualID)
    {
        if($role == 'student')
        {
            $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
            if($qualification)
            {
                $qualification->remove_single_students_units($userID);
            }
        }
        $DB->delete_records('block_bcgt_user_qual', array('bcgtqualificationid'=>$qualID, 
        'roleid'=>$roleID, 'userid'=>$userID));
    }
}

/**
 * 
 * @global type $DB
 * @param type $categoryID
 * @param type $search
 * @param type $studentSearch
 * @param type $teacherSearch
 * @return type
 */
function search_courses($categoryID = -1, $search = '', $studentSearch = '', $teacherSearch = '', $sort = '')
{
    global $DB;
    $sql = "SELECT distinct(course.id), course.* , category.name as categoryname FROM {course} course 
        JOIN {course_categories} category ON course.category = category.id ";
    if($studentSearch != '')
    {
        $sql .= " JOIN {context} scontext ON scontext.instanceid = course.id
            JOIN {role_assignments} sroleass ON sroleass.contextid = scontext.id 
            JOIN {user} suser ON suser.id = sroleass.userid 
            JOIN {role} srole ON srole.id = sroleass.roleid";
    }
    if($teacherSearch != '')
    {
        $sql .= " JOIN {context} tcontext ON tcontext.instanceid = course.id
            JOIN {role_assignments} troleass ON troleass.contextid = tcontext.id 
            JOIN {user} tuser ON tuser.id = troleass.userid 
            JOIN {role} trole ON trole.id = troleass.roleid"; 
    }
    $params = array();
    if($categoryID != -1 || $search != '' || $studentSearch != '' || $teacherSearch != '')
    {
        $and = false;
        $sql .= " WHERE";
        if($categoryID != -1)
        {
            $sql .= " course.category = ?";
            $and = true;
            $params[] = $categoryID;
        }
        if($search != '')
        {
            if($and)
            {
                $sql .= " AND";
            }
            $and = true;
            $sql .= " (course.fullname LIKE ? OR course.shortname LIKE ? OR course.idnumber LIKE ?)";
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
        }
        if($studentSearch != '')
        {
            if($and)
            {
                $sql .= " AND";
            }
            $and = true;
            $sql .= " srole.shortname = ? AND (suser.username LIKE ? OR suser.firstname LIKE ? OR suser.lastname LIKE ?)";
            $params[] = 'student';
            $params[] = '%'.$studentSearch.'%';
            $params[] = '%'.$studentSearch.'%';
            $params[] = '%'.$studentSearch.'%';
            //JOIN ON users and enrollments where student...
        }
        if($teacherSearch != '')
        {
            //JOIN ON usres and enrollments where teacher ...
            if($and)
            {
                $sql .= " AND";
            }
            $and = true;
            $sql .= " trole.shortname LIKE ? AND (tuser.username LIKE ? OR tuser.firstname LIKE ? OR tuser.lastname LIKE ?)";
            $params[] = '%teacher%';
            $params[] = '%'.$teacherSearch.'%';
            $params[] = '%'.$teacherSearch.'%';
            $params[] = '%'.$teacherSearch.'%';
        }
    }
    
    $sortAND = false;
    if(get_config('bcgt', 'showcoursecategories'))
    {
        $sql .= ' ORDER BY';
        $sql .= ' categoryname ASC ';
        $sortAND = true;
    }
    if($sort != '')
    {
        if($sortAND)
        {
            $sql .= ' ,';
        }
        else
        {
            $sql .= ' ORDER BY';
        }
        $sql .= ' '.$sort;
        $sortAND = true;
    }
    return $DB->get_records_sql($sql, $params, null, 100);
    
    //Include child courses???
}

/**
 * 
 * @global type $DB
 * @param type $courseID
 * @return type
 */
function bcgt_get_course_students($courseID)
{
    global $DB;
    $sql = "SELECT ra.id as id , usr.id as userid, usr.username, usr.firstname, usr.lastname, usr.picture, 
        usr.imagealt, usr.email, 
course.id as courseid, course.shortname as courseshortname, 'direct' as enrolment 
FROM {user} usr 
JOIN {role_assignments} ra ON ra.userid = usr.id
JOIN {context} c ON c.id = ra.contextid
JOIN {role} r ON r.id = ra.roleid
JOIN {course} course ON course.id = c.instanceid
WHERE course.id = ? AND r.shortname LIKE ?  AND (ra.component = '' OR ra.component = 'enrol_database')
UNION
SELECT ra.id as id , usr.id as usrid, usr.username, usr.firstname, usr.lastname, 
usr.picture, usr.imagealt, usr.email, childcourse.id as courseid, 
childcourse.shortname as courseshortname, 'child' as enrolment 
FROM {user} usr 
JOIN {role_assignments} ra ON ra.userid = usr.id 
JOIN {context} c ON c.id = ra.contextid 
JOIN {role} r ON r.id = ra.roleid 
JOIN {course} childcourse ON childcourse .id = c.instanceid 
LEFT OUTER JOIN {enrol} e ON e.customint1 = childcourse .id 
JOIN {course} course ON course.id = e.courseid 
WHERE course.id = ? AND r.shortname = ? 
ORDER BY enrolment DESC, courseid ASC, lastname ASC";
    return $DB->get_records_sql($sql, array($courseID, 'student', $courseID, 'student'));
}

function display_course_tracker_users($courseID, $users, $currentQuals, $role, $isRole, $checkForOtherRoles = false)
{
    global $CFG, $COURSE, $DB;
    $out = '';
    $count = 0;
    $canViewLinks = false;
    $context = context_course::instance($COURSE->id);
    if(has_capability('block/bcgt:checkuseraccess', $context))
    {
        $canViewLinks = true;
    }
    $lastCourse = $courseID;
    foreach($users AS $user)
    {
        $count++;
        $out .= '<tr>';
        $currentCourse = $user->courseid;
        if($count == 1)
        {
            $out .= '<td>'.get_string('direct', 'block_bcgt').'</td><td></td><td></td><td></td><td></td>';
            foreach($currentQuals AS $qual)
            {
                //Select all Students on this course for this Qual
                $out .= '<td class="qualSelect"><a class="qualSelect" href="edit_course_qual_user?cID='.$courseID.'" 
                        title="'.get_string('courseualusersselectall','block_bcgt').'">'.
                            '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowdown.jpg"'. 
                            'width="25" height="25" class="qualColumnCourse" '.
                        'id="q'.$qual->id.'c'.$currentCourse.'"/></a></td>';
            }
            $out .= '</tr>';
            $out .= '<tr>';
        }
        if($currentCourse != $lastCourse)
        {
            $lastCourse = $currentCourse;
            $out .= '<td>'.$user->courseshortname.'</td><td></td><td></td><td></td><td></td>';
            foreach($currentQuals AS $qual)
            {
                //Select all Students on this course for this Qual
                $out .= '<td class="qualSelect"><a class="qualSelect" href="edit_course_qual_user?cID='.$courseID.'" 
                        title="'.get_string('courseualusersselectall', 'block_bcgt').'">'.
                            '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowdown.jpg"'. 
                            'width="25" height="25" class="qualColumnCourse" '.
                        'id="q'.$qual->id.'c'.$currentCourse.'"/></a></td>';
            }
            $out .= '</tr>';
            $out .= '<tr>';
        }
        $out .= '<td></td>';
        //if commenting this back in dont forget that the student object doesnt have
        //the id as the user id, is the the role assignment id
        //so $userObj = $student
        //userObj->id = $student->userid
    //    $out .= '<td>'.$OUTPUT->user_picture($student, array(1)).'</td>';
        $out .= '<td></td>';
        $out .= '<td>'.$user->username.'</td>';
        $out .= '<td>'.$user->firstname.' '.$user->lastname.'</td>';
        //, 
        $out .= '<td class="qualSelect"><a class="qualSelect" href="edit_course_qual_course.php?qID='.$qual->id.'&sID='.$user->userid.'"'.
                'title="'.get_string('selectallusersquals', 'block_bcgt').'">'.
                '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowright.jpg"'. 
                'width="25" height="25" class="studentRow" id="s'.$user->userid.'"/>'.
                '</a></td>';
        foreach($currentQuals AS $qual)
        {
            $checked = '';
            if(Qualification::check_user_on_qual($user->userid, $role->id, $qual->id, $isRole))
            {
                $checked = 'checked';
            }
            $otherRoles = false;
            if($checkForOtherRoles)
            {
                $otherRoles = Qualification::check_user_on_qual($user->userid, $role->id, $qual->id, !$isRole);
            }
            $extraClass = '';
            if($otherRoles)
            {
                $extraClass = 'otherRoles';
            }
            
            //check if they are on another course that has this qualification:
            $sql = "SELECT * FROM {block_bcgt_course_qual} coursequal 
                JOIN {course} course ON course.id = coursequal.courseid 
                JOIN {context} context ON context.instanceid = course.id 
                JOIN {role_assignments} roleass ON roleass.contextid = context.id 
                WHERE coursequal.courseid != ? AND roleass.userid = ? AND coursequal.bcgtqualificationid = ?";
            $params = array($courseID, $user->userid, $qual->id);
            $otherCourses = $DB->get_records_sql($sql, $params);
            $symbol = '';
            if($otherCourses)
            {
                $title = get_string('useronothercourses', 'block_bcgt').' : ';
                if($canViewLinks)
                {
                    $symbol .= '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/user_access.php?uID='.$user->userid.'&search='.$user->username.'" title="'.$title.'">';
                }
                $symbol .= '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/linksymbol.jpg"/>';
                if($canViewLinks)
                {
                    $symbol .= '</a>';
                }
            }
            $out .= '<td class=" '.$extraClass.' qualSelect"><input type="checkbox" name="chq'.$qual->id.'s'.$user->userid.'"'.
                'id="" class="qualSelect chq'.$qual->id.' chq'.$qual->id.'c'.$currentCourse.' '.
                    'chs'.$user->userid.'" '.$checked.'/> '.$symbol;
//            if($otherRoles)
//            {
//                $out .= '<span class="otherRoles"><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/users_qual_access.php"></a></span>';
//            }
            $out .= '</td>';
        }
        $out .= '</tr>';
    }
    return $out;
}

function display_course_tracker_staff($courseID, $users, $currentQuals, $role, $isRole, $checkForOtherRoles = false)
{
    global $CFG;
    $out = '';
    $count = 0;
    $lastCourse = $courseID;
    foreach($users AS $user)
    {
        $count++;
        $out .= '<tr>';
        $currentCourse = $user->courseid;
        if($count == 1)
        {
            $out .= '<td>'.get_string('direct', 'block_bcgt').'</td><td></td><td></td><td></td><td></td>';
            foreach($currentQuals AS $qual)
            {
                //Select all Students on this course for this Qual
                $out .= '<td class="qualSelect"><a class="qualSelect" href="edit_course_qual_user?cID='.$courseID.'" 
                        title="'.get_string('courseualusersselectall','block_bcgt').'">'.
                            '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowdown.jpg"'. 
                            'width="25" height="25" class="qualColumnCourseStaff" '.
                        'id="q'.$qual->id.'c'.$currentCourse.'st"/></a></td>';
            }
            $out .= '</tr>';
            $out .= '<tr>';
        }
        if($currentCourse != $lastCourse)
        {
            $lastCourse = $currentCourse;
            $out .= '<td>'.$user->courseshortname.'</td><td></td><td></td><td></td><td></td>';
            foreach($currentQuals AS $qual)
            {
                //Select all Students on this course for this Qual
                $out .= '<td class="qualSelect"><a class="qualSelect" href="edit_course_qual_user?cID='.$courseID.'" 
                        title="'.get_string('courseualusersselectall', 'block_bcgt').'">'.
                            '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowdown.jpg"'. 
                            'width="25" height="25" class="qualColumnCourseStaff" '.
                        'id="q'.$qual->id.'c'.$currentCourse.'st"/></a></td>';
            }
            $out .= '</tr>';
            $out .= '<tr>';
        }
        $out .= '<td></td>';
        //if commenting this back in dont forget that the student object doesnt have
        //the id as the user id, is the the role assignment id
        //so $userObj = $student
        //userObj->id = $student->userid
    //    $out .= '<td>'.$OUTPUT->user_picture($student, array(1)).'</td>';
        $out .= '<td></td>';
        $out .= '<td>'.$user->username.'</td>';
        $out .= '<td>'.$user->firstname.' '.$user->lastname.'</td>';
        //, 
        $out .= '<td class="qualSelect"><a class="qualSelect" href="edit_course_qual_course.php?qID='.$qual->id.'&sID='.$user->userid.'"'.
                'title="'.get_string('selectallusersquals', 'block_bcgt').'">'.
                '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowright.jpg"'. 
                'width="25" height="25" class="studentRow" id="s'.$user->userid.'"/>'.
                '</a></td>';
        foreach($currentQuals AS $qual)
        {
            $checked = '';
            if(Qualification::check_user_on_qual($user->userid, $role->id, $qual->id, $isRole))
            {
                $checked = 'checked';
            }
            $otherRoles = false;
            if($checkForOtherRoles)
            {
                $otherRoles = Qualification::check_user_on_qual($user->userid, $role->id, $qual->id, !$isRole);
            }
            $extraClass = '';
            if($otherRoles)
            {
                $extraClass = 'otherRoles';
            }
            $out .= '<td class=" '.$extraClass.' qualSelect"><input type="checkbox" name="chq'.$qual->id.'s'.$user->userid.'st"'.
                'id="" class="qualSelect chq'.$qual->id.'st chq'.$qual->id.'c'.$currentCourse.'st '.
                    'chs'.$user->userid.'" '.$checked.'/>';
            if($otherRoles)
            {
                //they have other roles on this qual! e.g. a student role
                $out .= '<span class="otherRoles"><a disbled="true" href="'.$CFG->wwwroot.'/blocks/bcgt/forms/users_qual_access.php">!!!</a></span>';
            }
            $out .= '</td>';
        }
        $out .= '</tr>';
    }
    return $out;
}

function display_course_tracker_unlinked_users($courseID, $users, $currentQuals)
{
    global $CFG, $COURSE;
    $out = '';
    $count = 0;
    $canViewLinks = false;
    $context = context_course::instance($COURSE->id);
    if(has_capability('block/bcgt:checkuseraccess', $context))
    {
        $canViewLinks = true;
    }
    foreach($users AS $userObj)
    {
        $user = $userObj->student;
        $count++;
        if($count == 1)
        {
            $out .= '<tr><td colspan="1">'.get_string('unlinkedenrolments', 'block_bcgt').'</td>';
            $out .= '<td></td>';
            $out .= '<td></td>';
            $out .= '<td></td>';
            $out .= '<td></td>';
            foreach($currentQuals AS $qual)
            {
                //Select all Students on this course for this Qual
                $out .= '<td class="qualSelect"><a class="qualSelect" href="edit_course_qual_user?cID='.$courseID.'" 
                        title="'.get_string('courseualusersselectall','block_bcgt').'">'.
                            '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowdown.jpg"'. 
                            'width="25" height="25" class="qualUnlinkedColumnCourse" '.
                        'id="q'.$qual->id.'u"/></a></td>';
            }
            $out .= '</tr>';
        }
        $out .= '<tr>';
        $out .= '<td></td>';
        //if commenting this back in dont forget that the student object doesnt have
        //the id as the user id, is the the role assignment id
        //so $userObj = $student
        //userObj->id = $student->userid
    //    $out .= '<td>'.$OUTPUT->user_picture($student, array(1)).'</td>';
        $out .= '<td></td>';
        $out .= '<td>'.$user->username.'</td>';
        $out .= '<td>'.$user->firstname.' '.$user->lastname.'</td>';
        //, 
        $out .= '<td class="qualSelect"><a class="qualSelect" href="edit_course_qual_course.php?qID='.$qual->id.'&sID='.$user->userid.'"'.
                'title="'.get_string('selectallusersquals', 'block_bcgt').'">'.
                '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/arrowright.jpg"'. 
                'width="25" height="25" class="studentRow" id="s'.$user->userid.'"/>'.
                '</a></td>';
        foreach($currentQuals AS $qual)
        {
            //for each qual
            //is the student on this qualification
            //remember that the students will be on at least one of these
            $checked = '';
            $extraClass = '';
            $symbol = '';
            $userCourses = $userObj->courses;
            if($userCourses)
            {
                //usercourses is an array of all of the students
                //courses that they are on that has this qualID attached to it.
                //that isnt this course!
                if(isset($userCourses[$qual->id]))
                {
                    $title = get_string('useronothercourses', 'block_bcgt').' : ';
                    $courses = $userCourses[$qual->id];
                    foreach($courses AS $course)
                    {
                        $title .= $course->shortname.' | ';
                    }
                    if($canViewLinks)
                    {
                        $symbol .= '<a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/user_access.php?uID='.$user->userid.'&search='.$user->username.'" title="'.$title.'">';
                    }
                    $symbol .= '<img src="'.$CFG->wwwroot.'/blocks/bcgt/images/linksymbol.jpg"/>';
                    if($canViewLinks)
                    {
                        $symbol .= '</a>';
                    }
                }
            }
            if(isset($userObj->quals[$qual->id]))
            {
                //so the student is on this qual.
                //these sudents arent on the course
                $checked = 'checked';
                $extraClass = 'checked';
                $out .= '<td class="'.$extraClass.' qualSelect"><input type="checkbox" name="chq'.$qual->id.'s'.$user->userid.'u"'.
                'id="" class="qualSelect chq'.$qual->id.'u chq'.$qual->id.'c'.$courseID.'u '.
                    'chs'.$user->userid.'" '.$checked.'/> '.$symbol;
                $out .= '</td>';
            }
            else
            {
                //if they aent on it then dont let them be added to it!
                $out .= '<td class="'.$extraClass.' qualSelect">'.$symbol.'</td>';
            }
        }
        $out .= '</tr>';
    }
    return $out;
}

/**
 * 
 * @global type $DB
 * @param type $courseID
 * @return type
 */
function bcgt_get_course_staff($courseID)
{
    global $DB;
    $sql = "SELECT ra.id as id , u.id as userid, u.username, u.firstname, u.lastname, u.picture, 
                    u.imagealt, u.email, 
            course.id as courseid, course.shortname as courseshortname, 'direct' as enrolment 
            FROM {user} u 
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} c ON c.id = ra.contextid
            JOIN {role} r ON r.id = ra.roleid
            JOIN {course} course ON course.id = c.instanceid
            WHERE course.id = ? AND r.shortname != ?  AND ra.component = ? 
            UNION
            SELECT ra.id as id , u.id as userid, u.username, u.firstname, u.lastname, 
            u.picture, u.imagealt, u.email, childcourse.id as courseid, 
            childcourse.shortname as courseshortname, 'child' as enrolment 
            FROM {user} u 
            JOIN {role_assignments} ra ON ra.userid = u.id 
            JOIN {context} c ON c.id = ra.contextid 
            JOIN {role} r ON r.id = ra.roleid 
            JOIN {course} childcourse ON childcourse .id = c.instanceid 
            LEFT OUTER JOIN {enrol} e ON e.customint1 = childcourse .id 
            JOIN {course} course ON course.id = e.courseid 
            WHERE course.id = ? AND r.shortname != ? 
            ORDER BY enrolment DESC, courseid ASC, lastname ASC";
    return $DB->get_records_sql($sql, array($courseID, 'student', '', $courseID, 'student'));
}

/**
 * 
 * @global type $DB
 * @param type $courseID
 * @return type
 */
function bcgt_get_old_students_still_on_qual($courseID, $quals)
{
    global $DB;
    //get all of the students that are on this qual
    //but get all of them that arent on this course
    //are they on anoher course?
    $retval = array();
    foreach($quals AS $qual)
    {
        $qualification = Qualification::get_qualification_class_id($qual->id);
        if($qualification)
        {
            $students = array();
            //this gets the students on the qual that are not on the courseID passed in.
            $students = $qualification->get_students('', '', $courseID, false);
            foreach($students AS $student)
            {
                //are they on another course that this qual is on?
                $sql = "SELECT distinct(coursequal.id), course.* FROM {course} course 
                    JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = course.id AND coursequal.bcgtqualificationid = ?
                    JOIN {context} con ON con.instanceid = course.id 
                    JOIN {role_assignments} ra ON ra.contextid = con.id
                    JOIN {role} role ON role.id = ra.roleid
                    WHERE ra.userid = ? AND course.id != ? AND role.shortname = ?";
                $courses = $DB->get_records_sql($sql, array($qual->id, $student->id, $courseID, 'student'));
                if(isset($retval[$student->id]))
                {
                    //then we have found this student before:
                    $foundStudent = $retval[$student->id];
                    //ad these courses that the student is on
                    $coursesFound = $foundStudent->courses;
                    if($courses && count($courses) >= 1)
                    {
                        $coursesFound[$qual->id] = $courses;
                    }
                    $foundStudent->courses = $coursesFound;
                    //add the qualID to the quals that have been found.
                    $foundStudentQuals = $foundStudent->quals;
                    $foundStudentQuals[$qual->id] = true;
                    $foundStudent->quals = $foundStudentQuals;
                    $retval[$student->id] = $foundStudent;
                }
                else
                {
                    $foundStudentQuals = array();
                    //add the qualID to the quals that have been found.
                    $foundStudentQuals[$qual->id] = true;
                    //ad these courses that the student is on
                    $coursesFound = array();
                    if($courses && count($courses) >= 1)
                    {
                        $coursesFound[$qual->id] = $courses;
                    }
                    $foundStudent = new stdClass();
                    //add the students courses, details and quals. 
                    $student->userid = $student->id;
                    $foundStudent->student = $student;
                    $foundStudent->quals = $foundStudentQuals;
                    $foundStudent->courses = $coursesFound;
                    $retval[$student->id] = $foundStudent;
                }
            }
        }
    }
    return $retval;
}

/**
 * Gets all of the qualifications that are on a course
 * @global type $DB
 * @param type $courseID
 * @return type
 */
function bcgt_get_course_quals($courseID, $familyID = -1, $qualID = -1, 
        $excludeFamilies = array(), $search = '', $groupingID = -1, $checkHasStudents = false)
{
    global $DB;
    $sql = "SELECT distinct(qual.id), family.family, level.trackinglevel, level.id as levelid , subtype.subtype, subtype.subtypeshort,  
        qual.name, qual.additionalname, type.type FROM {block_bcgt_course_qual} coursequal 
        JOIN {block_bcgt_qualification} qual ON qual.id = coursequal.bcgtqualificationid 
        JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
        JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
        JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
        JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
        JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid";
    if($groupingID != -1)
    {
        $sql .= ' JOIN {groups} g ON g.courseid = coursequal.courseid 
            JOIN {groups_members} members ON members.groupid = g.id 
            JOIN {block_bcgt_user_qual} userqualg ON userqualg.userid = members.userid 
            AND userqualg.bcgtqualificationid = qual.id 
            JOIN {groupings_groups} gg ON gg.groupid = g.id';
    }
    if($checkHasStudents)
    {
        $sql .= " JOIN {block_bcgt_user_qual} userqual 
            ON userqual.bcgtqualificationid = qual.id 
            JOIN {role} role ON role.id = userqual.roleid AND role.shortname = ?";
    }
    if($courseID != -1 || $familyID != -1 || $qualID != -1 || 
            ($excludeFamilies && count($excludeFamilies) != 0) || 
            $search != '' || $groupingID != -1)
    {
        $sql .= ' WHERE';
    }
    $params = array();
    if($checkHasStudents)
    {
        $params[] = 'student';
    }
    $and = false;
    if($courseID != -1)
    {
        $sql .= " coursequal.courseid = ?
       ";
        $and = true;
        $params[] = $courseID;
    }
    if($familyID != -1)
    {
        if($and)
        {
            $sql .= ' AND';
        }
        $and = true;
        $sql .= ' type.bcgttypefamilyid = ?';
        $params[] = $familyID;
    }
    if($qualID != -1)
    {
        if($and)
        {
            $sql .= ' AND';
        }
        $and = true;
        $sql .= ' qual.id = ?';
        $params[] = $qualID;
    }
    if($excludeFamilies && count($excludeFamilies) != 0)
    {
        if($and)
        {
            $sql .= ' AND';
        }
        $and = true;
        $count = 0;
        foreach($excludeFamilies AS $family)
        {
            $count++;
            if($count != 1)
            {
                $sql .= ' AND';
            }
            $sql .= ' family.family != ?';
            $params[] = $family;
        }
        $sql .= '';
    }
    if($search != '')
    {
        if($and)
        {
            $sql .= ' AND';
        }
        $and = true;
        $sql .= ' (qual.name LIKE ?';
        $params[] = '%'.$search.'%';
        $seachSplit = explode(' ', $search);
        if($seachSplit)
        {
            foreach($seachSplit AS $split)
            {
                $sql .= ' OR qual.name LIKE ?';
                $params[] = '%'.$split.'%';
            }
        }
        $sql .= ')';
    }
    if($groupingID != -1)
    {
        if($and)
        {
            $sql .= ' AND';
        }
        $and = true;
        $sql .= ' gg.groupingid = ?';
        $params[] = $groupingID;
    }
    $records = $DB->get_records_sql($sql, $params);
    
    
    // Bespoke check
    if ($familyID == 1 || $familyID < 0){
        
        $bespoke = $DB->get_records_sql("SELECT q.id, q.name, b.displaytype, b.level, b.subtype, 1 as isbespoke
                                        FROM {block_bcgt_course_qual} cq
                                        INNER JOIN {block_bcgt_qualification} q ON q.id = cq.bcgtqualificationid
                                        INNER JOIN {block_bcgt_bespoke_qual} b ON b.bcgtqualid = q.id
                                        WHERE cq.courseid = ?", array($courseID));
        //Also get any bespoke records that are on this course. 
        if ($bespoke)
        {
            foreach($bespoke as $record)
            {
                if (!isset($records[$record->id])){
                    $records[$record->id] = $record;
                }
            }
        }
    
    }
    
    return $records;
}

function bcgt_get_user_qual_roles($userID, $qualID)
{
    global $DB;
    $sql = "SELECT role.id, role.shortname FROM {role} role 
        JOIN {block_bcgt_user_qual} userqual ON userqual.roleid = role.id 
        WHERE userqual.userid = ? AND userqual.bcgtqualificationid = ?";
    $params = array($userID, $qualID);
    return $DB->get_records_sql($sql, $params);
}

/**
 * 
 * @global type $DB
 * @param type $qualID
 * @return type
 */
function bcgt_get_qual_courses($qualID)
{
    global $DB;
    $sql = "SELECT course.* FROM {course} course 
        JOIN {block_bcgt_course_qual} coursequal ON coursequal.courseid = course.id 
        WHERE coursequal.bcgtqualificationid = ?";
    return $DB->get_records_sql($sql, array($qualID));
}

function bcgt_get_quals_by_target_qual($targetQualID)
{
    global $DB;
    $sql = "SELECT * FROM {block_bcgt_qualification} qual WHERE bcgttargetqualid = ?";
    return $DB->get_records_sql($sql, array($targetQualID));
}


function bcgt_get_unit_quals($unitID, $courseID = -1)
{
    global $DB;
    $sql = "SELECT distinct(qual.id), qual.* FROM {block_bcgt_qualification} qual 
        JOIN {block_bcgt_course_qual} cq ON cq.bcgtqualificationid = qual.id 
        JOIN {block_bcgt_qual_units} qu ON qu.bcgtqualificationid = qual.id 
        WHERE qu.bcgtunitid = ?";
    $params[] = $unitID;
    if($courseID != -1)
    {
        $sql .= " AND cq.courseid = ?";
        $params[] = $courseID;
    }
    return $DB->get_records_sql($sql, $params);
}

/**
 * 
 * @global type $DB
 * @param type $courseID
 * @return type
 */
function bcgt_get_course_units($courseID, $familyID = -1)
{
    global $DB;
    $sql = "SELECT distinct(unit.id), unit.* FROM {block_bcgt_course_qual} coursequal 
        JOIN {block_bcgt_qualification} qual ON qual.id = coursequal.bcgtqualificationid 
        JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
        JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
        JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
        JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
        JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
        JOIN {block_bcgt_qual_units} qualunits ON qualunits.bcgtqualificationid = qual.id
        JOIN {block_bcgt_unit} unit ON unit.id = qualunits.bcgtunitid 
        WHERE coursequal.courseid = ?
        ";
    $params = array($courseID);
    if($familyID != -1)
    {
        $sql .= ' AND type.bcgttypefamilyid = ?';
        $params[] = $familyID;
    }
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_max_activity_units($unitIDs, $userID = -1)
{
    global $DB;
    $sql = "SELECT MAX(count) as maxcount FROM (
        SELECT refs.bcgtunitid, count(distinct(refs.coursemoduleid)) as count 
        FROM {block_bcgt_activity_refs} refs";
    if($userID != -1)
    {
        $sql .= " JOIN {block_bcgt_user_unit} userunit ON userunit.bcgtunitid = refs.bcgtunitid";
    }
    $sql .= " JOIN {course_modules} cmods ON cmods.id = refs.coursemoduleid";
    $sql .= " JOIN {modules} mods ON mods.id = cmods.module";
    $sql .= " JOIN {context} con ON con.instanceid = cmods.course 
            JOIN {role_assignments} ra ON ra.contextid = con.id";
    $sql .= " WHERE refs.bcgtunitid IN (";
    $params = array();
    $count = 0;
    foreach($unitIDs AS $id)
    {
        $count++;
        $sql .= '?';
        if(count($unitIDs) != $count)
        {
            $sql .= ',';
        }
        $params[] = $id;
    }
    $sql .= ")";
    if($userID != -1)
    {
        $sql .= " AND userunit.userid = ? AND ra.userid = ?";
        $params[] = $userID;
        $params[] = $userID;
    }
    $sql .= " AND refs.coursemoduleid IS NOT NULL AND refs.coursemoduleid > ? 
        AND mods.visible = ?";
    $params[] = 0;
    $params[] = 1;
    if(get_config('bcgt','modstrackercheckcoursevisible'))
    {
        //if we are checking for visibility
        $sql .= ' AND cmods.visible = ?';
        $params[] = 1;
    }
    $sql .= " GROUP BY refs.bcgtunitid) as activitycount";
    return $DB->get_record_sql($sql, $params);
}

function bcgt_get_max_units_activity($courseModuleIDs, $userID = -1)
{
    global $DB;
    $sql = "SELECT MAX(count) as maxcount FROM (
        SELECT refs.coursemoduleid, count(distinct(refs.bcgtunitid)) as count 
        FROM {block_bcgt_activity_refs} refs";
    if($userID != -1)
    {
        $sql .= " JOIN {block_bcgt_user_unit} userunit ON userunit.bcgtunitid = refs.bcgtunitid";
    }  
    $sql .= " WHERE refs.coursemoduleid IN (";
    $params = array();
    $count = 0;
    foreach($courseModuleIDs AS $id)
    {
        $count++;
        $sql .= '?';
        if(count($courseModuleIDs) != $count)
        {
            $sql .= ',';
        }
        $params[] = $id;
    }
    $sql .= ")";
    if($userID != -1)
    {
        $sql .= " AND userunit.userid = ?";
        $params[] = $userID;
    }
    $sql .= " GROUP BY refs.coursemoduleid) as unitcount";
    return $DB->get_record_sql($sql, $params);
}

function bcgt_user_activities($qualID, $userID, $unitID = -1)
{
    //need to check the user is actually on the course
    //that this activity is on
    global $DB;
    $sql = "SELECT distinct(cmods.id), items.courseid, items.itemname as name, 
        items.itemtype as type, items.itemmodule as module, items.iteminstance, 
        cmods.module AS cmodule, cmods.section, cmods.groupingid, cmods.instance AS instanceid 
        FROM {grade_items} items 
        JOIN {modules} mods ON mods.name = items.itemmodule
        JOIN {course_modules} cmods ON cmods.module = mods.id 
        AND cmods.instance = items.iteminstance
        JOIN {block_bcgt_activity_refs} refs ON refs.coursemoduleid = cmods.id 
        JOIN {block_bcgt_user_qual} userquals ON userquals.bcgtqualificationid = refs.bcgtqualificationid
        JOIN {context} con ON con.instanceid = cmods.course 
        JOIN {role_assignments} ra ON ra.contextid = con.id 
        WHERE refs.bcgtqualificationid = ? AND userquals.userid = ? AND mods.visible = ? AND ra.userid = ?";
    $params = array();
    $params[] = $qualID;
    $params[] = $userID;
    $params[] = 1;
    $params[] = $userID;
    if($unitID != -1)
    {
        $sql .= ' AND refs.bcgtunitid = ?';
        $params[] = $unitID;
    }
    if(get_config('bcgt','modstrackercheckcoursevisible'))
    {
        //if we are checking for visibility
        $sql .= ' AND cmods.visible = ?';
        $params[] = 1;
    }
    $retval = array();
    $mods = $DB->get_records_sql($sql, $params);
    if($mods)
    {
        //we need to check for grouping
        foreach($mods AS $mod)
        {
            if($mod->groupingid)
            {
                //then we need to check for grouping.
                if(bcgt_is_user_in_grouping($userID, $mod->groupingid))
                {
                    $retval[$mod->id] = $mod;
                }
            }
            else
            {
                $retval[$mod->id] = $mod;
            }
        }
    }
    return $retval;
}

function bcgt_unit_activities($courseID = -1, $unitID = -1, $qualID = -1, $groupingID = -1)
{
    global $DB;
    $sql = "SELECT distinct(cmods.id), items.courseid, items.itemname as name, 
        items.itemtype as type, items.itemmodule as module, items.iteminstance, 
        cmods.module AS cmodule, cmods.section, cmods.groupingid, cmods.instance AS instanceid   
        FROM {grade_items} items 
        JOIN {modules} mods ON mods.name = items.itemmodule
        JOIN {course_modules} cmods ON cmods.module = mods.id 
        AND cmods.instance = items.iteminstance
        JOIN {block_bcgt_activity_refs} refs ON refs.coursemoduleid = cmods.id";
    if($courseID != -1 || $unitID != -1)
    {
        $and = false;
        $sql .= ' WHERE';
        if($courseID != -1)
        {
            $sql .= ' cmods.course = ?';
            $params[] = $courseID;
            $and = true;
        }
        if($unitID != -1)
        {
            if($and)
            {
                $sql .= " AND";
            }
            $sql .= ' refs.bcgtunitid = ?';
            $and = true;
            $params[] = $unitID;
        }
        if($qualID != -1)
        {
            if($and)
            {
                $sql .= " AND";
            }
            $sql .= ' refs.bcgtqualificationid = ?';
            $and = true;
            $params[] = $qualID;
        }
        if($groupingID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $sql .= ' cmods.groupingid = ?';
            $and = true;
            $params[] = $groupingID;
        }
            
    }
    return $DB->get_records_sql($sql, $params);
    
//    
//    
//    
//    
//    global $DB;
//    $sql = "SELECT distinct(cm.id), cm.*, m.id as mid, m.name as modname $extraField 
//        FROM {block_bcgt_activity_refs} activity 
//        JOIN {course_modules} cm ON cm.id = activity.coursemoduleid
//        JOIN {modules} md ON md.id = cm.module 
//        JOIN {".$moduleName."} m ON m.id = cm.instance 
//        WHERE cm.course = ? AND md.name = ? AND activity.bcgtunitid = ?
//        ";
//        $params = array($courseID, $moduleName, $unitID);
//    return $DB->get_records_sql($sql, $params);
}

function get_grid_menu($studentID, $unitID, $qualID = -1, $courseID = -1)
{
    global $CFG, $COURSE, $qualID, $studentID;

    if($courseID != -1)
    {
        $context = context_course::instance($courseID);
    }
    else
    {
        $context = context_course::instance($COURSE->id);
    }
    $qualification = null;
    $load = new stdClass();
    if($qualID != -1)
    {
        $load->loadLevel = Qualification::LOADLEVELMIN;
        $qualification = Qualification::get_qualification_class_id($qualID, $load);
    }
    
    
    //KD-debug
    //echo '<br />qualID=';
    //print_r($qualID);
    //echo '<br />studentID=';
    //print_r($studentID);
    //echo '<br />courseID=';
    //print_r($courseID);
    //echo '<br />COURSE->id=';
    //print_r($COURSE->id);

    // Gets cID from URL after clicking link to view grid
    // if cID not set then it is 1 for front page
    if (isset($_GET['cID'])) {
        $cID = $_GET['cID'];
    }
    else {
        $cID = SITEID;
    }
    
    $gridtype = optional_param('g', '', PARAM_TEXT);
   
    //echo '<br />cID=';
    //print_r($cID);

    //This gets the menu for the grid
    $out = '<ul class="bcgtGridMenuList">';
    if(has_capability('block/bcgt:viewclassgrids', $context) || 
            has_capability('block/bcgt:manageactivitylinks', $context) || 
            has_capability('block/bcgt:viewdashboard', $context))
    {
        // CORE MENU
        $out .= '<li class="bcgtHeadLink"><a href="#">Core &darr;</a>';
        
            $out .= '<ul class="bcgtDroppy">';
            
                if(has_capability('block/bcgt:viewclassgrids', $context))
                {
                    $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=s&cID='.$courseID.'">'.get_string('studentgrids','block_bcgt').'</a></li>';
                    $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=u&cID='.$courseID.'">'.get_string('unitgrids','block_bcgt').'</a></li>';        
                    $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/grid_select.php?g=c&cID='.$courseID.'">'.get_string('classgrids','block_bcgt').'</a></li>';
                }
                if(has_capability('block/bcgt:manageactivitylinks', $context))
                {
                    //$out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/activities.php?tab=act&cID='.$courseID.'">Assessments</a></li>';
                    if($cID!=1 && $gridtype!='c'){
                    $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/activities.php?tab=act&cID='.$cID.'">'.get_string('assessments','block_bcgt').'</a></li>';
                    }
                    else { 
                        // do nothing as we're not viewing this via a particular course, and a qual might be on multiple courses
                        // grid type is to avoid it showing on class at the moment
                    }
                }
                if(has_capability('block/bcgt:viewdashboard', $context))
                {
                    $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/my_dashboard.php">'.get_string('bcgtmydashboard','block_bcgt').'</a></li>';
                }      
                
                
                
            $out .= '</ul>';
        $out .= '</li>';
    }
    // CONTEXT MENU
    if(has_capability('block/bcgt:viewclassgrids', $context))
    {
    $out .= '<li class="bcgtHeadLink"><a href="#">Context &darr;</a>';

        $out .= '<ul class="bcgtDroppy">';            
        if(has_capability('block/bcgt:editqual', $context))
        {
            if($qualID != -1)
            {
                $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_qual.php?qID='.$qualID.'">'.get_string('editqualsimple','block_bcgt').'</a></li>';
            }
            else
            {
                $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/qual_select.php?">'.get_string('editqualsimple','block_bcgt').'</a></li>';
            }
        }
        if(has_capability('block/bcgt:editunit', $context))
        {
            if($qualID != -1)
            {
                $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_qual_units.php?qID='.$qualID.'">'.get_string('editqualunitssimple','block_bcgt').'</a></li>';
            }
            else
            {
                $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_unit_qual.php?&unitID='.$unitID.'">'.get_string('editqualunitssimple','block_bcgt').'</a></li>';
            }
            
        }
        if(has_capability('block/bcgt:editstudentunits', $context))
        {
            if($qualID != -1)
            {
                $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_students_units.php?qID='.$qualID.'">'.get_string('editstudentsunitssimple','block_bcgt').'</a></li>';
            }
            else
            {
                $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_students_units.php?a=u&uID='.$unitID.'">'.get_string('editstudentsunitssimple','block_bcgt').'</a></li>';
            }
        }

        if(has_capability('block/bcgt:editstudentunits', $context))
        {
            if($studentID)
            {
                $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/edit_students_units.php?a=s&sID='.$studentID.'">'.get_string('editindividualunits','block_bcgt').'</a></li>';
            }
        }
        $out .= '</ul>';
    $out .= '</li>';
    }        
        
    $out .= "<li class='bcgtHeadLink'><a href='#'>Grid &darr;</a>";
            $out .= "<ul class='bcgtDroppy'>";

    
    // Grid menu
    // TEMPORARY until print & download done - Hiding this menu if not student
    // Which grid are we on?
    if($studentID && $studentID > 0 && (has_capability('block/bcgt:printstudentgrid', $context))) {

        $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/print_grid.php?sID='.$studentID.'&qID='.$qualID.'" target="_blank">'.get_string('printgrid','block_bcgt').'</a></li>';
         
        if ($qualification && $qualification->has_printable_report()){
            $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/print_report.php?sID='.$studentID.'&qID='.$qualID.'" target="_blank">'.get_string('printreport','block_bcgt').'</a></li>';
        }
        

    } 
    // TEMPORARY fix
    elseif ($unitID && $unitID > 0 && has_capability('block/bcgt:printunitgrid', $context)){
        if($qualID != -1)
        {
            $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/print_grid.php?uID='.$unitID.'&qID='.$qualID.'" target="_blank">'.get_string('printgrid','block_bcgt').'</a></li>';
        }
        else
        {
            $out .= '<li>'.get_string('pleaseselectaqualprint','block_bcgt').'</li>';
        }
    }
    elseif ($qualID && $qualID > 0 && $gridtype == 'c')
    {
        if (has_capability('block/bcgt:printclassgrids', $context)){
            $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/print_grid.php?qID='.$qualID.'" target="_blank">'.get_string('printgrid','block_bcgt').'</a></li>';
        }
        
        if(has_capability('block/bcgt:viewassessmenttracker', $context)) {
            $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/assessment_tracker.php?qualID='.$qualID.'" target="_blank">'.get_string('assessmenttracker','block_bcgt').'</a></li>';
        }
        
    }
    
        
    
    // Export/Import
    if($studentID && $studentID > 0 && (has_capability('block/bcgt:importexportstudentgrids', $context))) {
        if ($qualID > 0)
        {
            $out .= "<li><a href='{$CFG->wwwroot}/blocks/bcgt/forms/export_student_grid.php?qualID={$qualID}&studentID={$studentID}&courseID={$cID}' target='_blank'>".get_string('exportdatasheet', 'block_bcgt')."</a></li>";
            $out .= "<li><a href='{$CFG->wwwroot}/blocks/bcgt/forms/import_student_grid.php?qualID={$qualID}&studentID={$studentID}&courseID={$cID}'>".get_string('importdatasheet', 'block_bcgt')."</a></li>";
        }
        else
        {
            $out .= '<li>'.get_string('pleaseselectaqualexportimport','block_bcgt').'</li>';
        }
    }
    
    elseif($unitID && $unitID > 0 && (has_capability('block/bcgt:importexportunitgrids', $context))) {
        if ($qualID > 0)
        {
            $groupID = optional_param("grID", -1, PARAM_INT);
            $out .= "<li><a href='{$CFG->wwwroot}/blocks/bcgt/forms/export_unit_grid.php?qualID={$qualID}&unitID={$unitID}&courseID={$cID}&grID={$groupID}' target='_blank'>".get_string('exportdatasheet', 'block_bcgt')."</a></li>";
            $out .= "<li><a href='{$CFG->wwwroot}/blocks/bcgt/forms/import_unit_grid.php?qualID={$qualID}&unitID={$unitID}&courseID={$cID}'>".get_string('importdatasheet', 'block_bcgt')."</a></li>";
        }
        else
        {
            $out .= '<li>'.get_string('pleaseselectaqualexportimport','block_bcgt').'</li>';
        }
    }
    
    elseif ($qualID && $qualID > 0 && $gridtype == 'c' && has_capability('block/bcgt:importexportgrids', $context)){
        
        $out .= "<li><a href='{$CFG->wwwroot}/blocks/bcgt/forms/export_grid.php?qualID={$qualID}&courseID={$cID}' target='_blank'>".get_string('exportdatasheet', 'block_bcgt')."</a></li>";
        
    }
    
    
    
            
    $out .= "</ul>";
$out .= "</li>";
                
    // CLass grid - import/export data
        // Postponed for the time being, doing student grid and unit grid first
//    if ($qualID && $qualID > 0 && $gridtype == 'c' && has_capability('block/bcgt:importexportgrids', $context)){
////        $out .= "<li class='bcgtHeadLink'><a href='#'>Data Sheets &darr;</a>";
////            $out .= "<ul class='bcgtDroppy'>";
////                $out .= "<li><a href='{$CFG->wwwroot}/blocks/bcgt/forms/export_grid.php?qualID={$qualID}' target='_blank'>".get_string('exportdatasheet', 'block_bcgt')."</a></li>";
////                $out .= "<li><a href='{$CFG->wwwroot}/blocks/bcgt/forms/import_grid.php?qualID={$qualID}'>".get_string('importdatasheet', 'block_bcgt')."</a></li>";
////            $out .= "</ul>";
////        $out .= "</li>";
//    }
    
    
    // Unit grid - import/export
    // todo
    
        
            
    // Custom links
            // Student grid
            $links = bcgt_get_setting("custom_stud_link");

            if ($studentID && $studentID > 0)
            {
                $out .= "<li class='bcgtHeadLink'><a href='#'>Student &darr;</a>";
                $out .= "<ul class='bcgtDroppy'>";
                
                // Assessment tracker
                if(has_capability('block/bcgt:viewassessmenttracker', $context)) {
                    $out .= '<li><a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/assessment_tracker.php?studentID='.$studentID.'" target="_blank">'.get_string('assessmenttracker','block_bcgt').'</a></li>';
                }
                
                if ($links)
                {
                    foreach((array)$links as $link)
                    {
                        $explode = explode(",", $link);
                        $url = $explode[0];
                        $title = $explode[1];
                        $out .= '<li><a href="'. bcgt_convert_custom_url($url, array("s" => $studentID, "u" => false, "q" => $qualID, "c" => $courseID)).'" target="_blank">'.$title.'</a></li>';
                    }
                }
                
                $out .= "</ul>";
                $out .= "</li>";
            }
            
            
            // Unit grid
            $links = bcgt_get_setting("custom_unit_link");

            if ($unitID && $unitID > 0 && $links)
            {
                
                $out .= "<li class='bcgtHeadLink'><a href='#'>Unit &darr;</a>";
                $out .= "<ul class='bcgtDroppy'>";
                
                foreach((array)$links as $link)
                {
                    $explode = explode(",", $link);
                    $url = $explode[0];
                    $title = $explode[1];
                    $out .= '<li><a href="'. bcgt_convert_custom_url($url, array("s" => false, "u" => $unitID, "q" => $qualID, "c" => $courseID)).'" target="_blank">'.$title.'</a></li>';
                }
                
                $out .= "</ul>";
                $out .= "</li>";
                
            }
            
            
            
        
    $out .= '</ul>';
    return $out;
}

function bcgt_get_module_from_course_mod($cmID, $moduleName = 'assign')
{
    global $DB;
    $sql = "SELECT m.* FROM {".$moduleName."} m
        JOIN {course_modules} cm ON cm.instance = m.id 
        WHERE cm.id = ?";
        $params = array($cmID);
    return $DB->get_record_sql($sql, $params);
}

/**
 * 
 * @global type $DB
 * @param type $courseID
 * @param type $role
 * @return type
 */
function bcgt_get_course_users($courseID, $role)
{
    global $DB;
    $sql = "SELECT * FROM {user} u
        JOIN {role_assignments} roleass ON roleass.userid = u.id 
        JOIN {role} role ON role.id = roleass.roleid 
        JOIN {context} context ON context.id = roleass.contextid 
        JOIN {course} course ON course.id = context.instanceid 
        WHERE course.id = ? AND role LIKE ?";
    $params = array($courseID, '%'.$role.'%');
    return $DB->get_record_sql($sql, $params);
}

/**
 * 
 * @global type $DB
 * @param type $qualID
 * @param type $userID
 * @param type $role
 * @return boolean
 */
function bcgt_get_user_on_qual($qualID, $userID, $role = 'student')
{
    global $DB;
    $role = bcgt_get_role($role);
    if($role)
    {
        $sql = "SELECT * FROM {block_bcgt_user_qual} WHERE 
            bcgtqualificationid = ? AND userid = ? AND roleid = ?";
        $params = array($qualID, $userID, $role->id);
        return $DB->get_record_sql($sql, $params);
    }
    return false;
}

function bcgt_get_non_stu_on_qual($qualID)
{
    global $DB;
    $sql = "SELECT distinct u.* FROM {user} user 
        JOIN {block_bcgt_user_qual} userqual ON userqual.userid = u.id 
        JOIN {role} role ON role.id = userqual.roleid 
        WHERE role.shortname != ? AND userqual.bcgtqualificationid = ?";
    $params = array('student',$qualID);
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_users_tutors($userID)
{
    global $DB;
    $role = get_config('bcgt','tutorrole');
    $params = array($userID, CONTEXT_USER, bcgt_get_role($role)->id);

    $sql = "SELECT ";
    $sql .= "DISTINCT u.*";
    $sql .= " FROM {role_assignments} r ";
    $sql .= " INNER JOIN {context} c ON c.id = r.contextid ";
    $sql .= " INNER JOIN {user} u ON u.id = r.userid ";
    $sql .= " WHERE c.instanceid = ? AND c.contextlevel = ? AND r.roleid = ? ";
    $sql .= " ORDER BY u.lastname, u.firstname ";
    
    return $DB->get_records_sql($sql, $params);
}

/**
 * 
 * @param type $courseID
 */
function bcgt_process_course_qual_users($courseID)
{
    global $DB;
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
    $currentQuals = bcgt_get_course_quals($courseID);
    $users = bcgt_get_course_students($courseID);
    $qualsArray = array();
    $role = bcgt_get_role('student');
    
    if($users)
    {
        foreach($users AS $student)
        {
            if($currentQuals)
            {
                foreach($currentQuals AS $qual)
                {
                    if(array_key_exists($qual->id, $qualsArray))
                    {
                        //then we already know about the qualification
                        $qualification = $qualsArray[$qual->id];
                    }
                    else 
                    {
                        $qualification = Qualification::get_qualification_class_id($qual->id, $loadParams);
                        $qualsArray[$qual->id] = $qualification;
                    }
                    if(isset($_POST['chq'.$qual->id.'s'.$student->userid]))
                    {
                        //the check box is set
                        //ihis will check its in the db and add it to all units
                        $qualification->add_user_to_qual($student->userid, $role->id, true);
                    }
                    else
                    {
                        //it isnt now, was it before?
                        $qualification->remove_user_from_qual($student->userid, $role->id, true);
                    }
                }
            }
        }
    }
    $context = context_course::instance($courseID);
    $stRole = bcgt_get_role('student');
    $tRole = bcgt_get_role('editingteacher');
    //now we need to do the unlinked users.
    if(has_capability('block/bcgt:editredundanttrackeruserlinks', $context))
    {
        $oldStudents = bcgt_get_old_students_still_on_qual($courseID, $currentQuals);
        if($oldStudents)
        {
            foreach($oldStudents AS $oldStudent)
            {
                $user = $oldStudent->student;
                $userQuals = $oldStudent->quals;
                $userID = $user->userid;
                if($currentQuals)
                {
                    foreach($currentQuals AS $qual)
                    {
                        //is it checked
                        //was it before?

                        //did the checkbox exist?
                        if(isset($userQuals[$qual->id]) && isset($_POST['chq'.$qual->id.'s'.$userID.'u']))
                        {
                            $sql = "SELECT * FROM {block_bcgt_user_qual} WHERE userid = ? AND bcgtqualificationid = ? AND roleid = ?";
                            $assign = $DB->get_record_sql($sql, array($userID, $qual->id, $stRole->id));
                            if($assign)
                            {
                                //then we have it before, so lets leave it alone
                            }
                            else
                            {
                                //we need to insertit
                                $stdObj = new stdClass();
                                $stdObj->bcgtqualificationid = $qual->id;
                                $stdObj->userid = $userID;
                                $stdObj->roleid = $stRole->id;
                                $DB->insert_record('block_bcgt_user_qual', $stdObj);
                            }
                        }
                        elseif(isset($userQuals[$qual->id]) && !isset($_POST['chq'.$qual->id.'s'.$userID.'u'])) 
                        {    
                            if($qual->id == 130 && $userID == 2456)
                            {
                                if(isset($userQuals[$qual->id]))
                                {
//                                    print_object($oldStudent);
//                                    echo "TRUE <br />";
//                                    print_object($userQuals[$qual->id]);
                                }
                                if(isset($_POST['chq'.$qual->id.'s'.$userID.'u']))
                                {
//                                    echo "SECOND SET <br />";
                                }
                            }
                            //then remove
                            $sql = "DELETE FROM {block_bcgt_user_qual} WHERE bcgtqualificationid = ? AND roleid = ? AND userid = ?";
                            $params = array($qual->id, $stRole->id, $userID);
                            $DB->execute($sql, $params);
                        }
                    }
                }
            }
        }
    }

    //then we want to do the staff
    if(has_capability('block/bcgt:editstafftrackerlinks', $context))
    {
        $staff = bcgt_get_course_staff($courseID);
        if($staff)
        {
            foreach($staff AS $user)
            {
                if($currentQuals)
                {
                    foreach($currentQuals AS $qual)
                    {
                        //need to go in as editingteacher
                        //is it checked?
                        //was it before?
                        if(isset($_POST['chq'.$qual->id.'s'.$user->userid.'st']))
                        {
                            //then update/insert
                            $sql = "SELECT * FROM {block_bcgt_user_qual} WHERE userid = ? AND bcgtqualificationid = ? AND roleid = ?";
                            $assign = $DB->get_record_sql($sql, array($user->userid, $qual->id, $tRole->id));
                            if($assign)
                            {
                                //then we have it before, so lets leave it alone
                            }
                            else
                            {
                                //we need to insertit
                                $stdObj = new stdClass();
                                $stdObj->bcgtqualificationid = $qual->id;
                                $stdObj->userid = $user->userid;
                                $stdObj->roleid = $tRole->id;
                                $DB->insert_record('block_bcgt_user_qual', $stdObj);
                            }
                        }
                        else 
                        {    
                            //then remove
                            //we need the student role. 
                            $sql = "DELETE FROM {block_bcgt_user_qual} WHERE bcgtqualificationid = ? AND roleid != ? AND userid = ?";
                            $params = array($qual->id, $stRole->id, $user->userid);
                            $DB->execute($sql, $params);
                        }
                    }
                }
            }
        }
    }
}

/**
 * 
 * @param type $qual
 * @param type $long
 * @param type $seperator
 * @param type $exclusions
 * @return string
 */
function bcgt_get_qualification_display_name($qual, $long = true, $seperator = ' ', $exclusions = array(), $returnType = 'String')
{
    $retval = '';
    if(!in_array('type', $exclusions))
    {
        if($returnType == 'Table')
        {
            $retval .= '<td>';
        }
        if (isset($qual->isbespoke)){
            $retval .= $qual->displaytype;
        } else {
            $retval .= (isset($qual->type)) ? $qual->type : $qual->family;
        }
        if($returnType == 'Table')
        {
            $retval .= '</td>';
        }
        else
        {
            $retval .= $seperator;
        }
    }
    elseif(!in_array('family', $exclusions))
    {
        if($returnType == 'Table')
        {
            $retval .= '<td>';
        }
        if (isset($qual->isbespoke)){
            $retval .= '';
        } else {
            $retval .= (isset($qual->family)) ? $qual->family : '';
        }
        if($returnType == 'Table')
        {
            $retval .= '</td>';
        }
        else
        {
            $retval .= $seperator;
        }
    }
    if(!in_array('trackinglevel', $exclusions))
    {
        if($returnType == 'Table')
        {
            $retval .= '<td>';
        }
        if($long)
        {
            
            if (isset($qual->isbespoke)){
                $retval .= "Level " . $qual->level;
            } else {
                $retval .= $qual->trackinglevel;
            }
            
        }
        else
        {
            
            if (isset($qual->isbespoke)){
                $retval .= "L{$qual->level}";
            } else {
                $retval .= Level::get_short_version($qual->levelid);
            }
            
        }
        if($returnType == 'Table')
        {
            $retval .= '</td>';
        }
        else
        {
            $retval .= $seperator;
        }
    }
    if(!in_array('subtype', $exclusions))
    {
        if($returnType == 'Table')
        {
            $retval .= '<td>';
        }
        if($long)
        {
            $retval .= $qual->subtype;
        }
        else
        {
            //$retval .= $qual->subtype;
            $retval .= $qual->subtypeshort;
        }
        if($returnType == 'Table')
        {
            $retval .= '</td>';
        }
        else
        {
            $retval .= $seperator;
        }
    }
    if($returnType == 'Table')
    {
        $retval .= '<td>';
    }
    $retval .= $qual->name;
    if($returnType == 'Table')
    {
        $retval .= '</td>';
    }
    if(!in_array('additionalname', $exclusions))
    {
        if($qual->additionalname && $qual->additionalname != '')
        {
            if($returnType == 'Table')
            {
                $retval .= '<td>';
            }
            $retval .= ' ('.$qual->additionalname.')';
            if($returnType == 'Table')
            {
                $retval .= '</td>';
            }
        }
    }
    return $retval;
}

function get_student_qual_update_time($qualID, $studentID)
{
    global $DB;
    $studentRole = $DB->get_record_sql("SELECT * FROM {role} WHERE shortname = ? ", array('student'));
    
    $sql = "SELECT * FROM {block_bcgt_user_qual} WHERE userid = ? AND bcgtqualificationid = ? AND roleid = ?";
    $params = array($studentID, $qualID, $studentRole->id);
    $record = $DB->get_record_sql($sql, $params);
    if($record)
    {
        return $record->lastupdatedtime;
    }
    return false;
}

/**
 * Finds the unittype, find the location of the unit class and loads it
 * @param type $unitID
 */
function load_unit_class($unitID)
{
    global $DB, $CFG;
    $sql = "SELECT * FROM {block_bcgt_type_family} family 
        JOIN {block_bcgt_type} type ON type.bcgttypefamilyid = family.id 
        JOIN {block_bcgt_unit} unit ON unit.bcgttypeid = type.id 
        WHERE unit.id = ?";
    $record = $DB->get_record_sql($sql, array($unitID));
    if($record)
    {
        $file = $CFG->dirroot."/blocks/bcgt/plugins/bcgt".strtolower($record->family)."/lib.php";
        //also need to load the lib of the family. 
        if(file_exists($file))
        {
            require_once($file);
        }
        require_once($CFG->dirroot.$record->classfolderlocation."/".$record->family."Unit.class.php");
    }
}

/**
 * Finds the unittype, find the location of the unit class and loads it
 * @param type $unitID
 */
function load_qual_class($qualID)
{
    global $DB, $CFG;
    $sql = "SELECT * FROM {block_bcgt_type_family} family 
        JOIN {block_bcgt_type} type ON type.bcgttypefamilyid = family.id 
        JOIN {block_bcgt_target_qual} targetqual ON targetqual.bcgttypeid = type.id 
        JOIN (block_bcgt_qualification) qual ON qual.bcgttargetqualid = targetqual.id
        WHERE qual.id = ?";
    $record = $DB->get_record_sql($sql, array($qualID));
    if($record)
    {
        require_once($CFG->dirroot.$record->classfolderlocation."/".$record->family."Qualification.class.php");
    }
}

//AND where the user is on the qual!
function get_users_on_unit_qual($unitID, $qualID = -1, $courseID = -1, $groupingID = -1, $limit = false, $limitFrom = false)
{
    global $DB;
    $sql = "SELECT distinct u.* 
        FROM {block_bcgt_user_unit} userunit 
        JOIN {user} u ON u.id = userunit.userid 
        JOIN {block_bcgt_user_qual} userqual ON userqual.userid = u.id ";
    $params = array();
    if($qualID != -1)
    {
        $sql .= " AND userqual.bcgtqualificationid = ?";
        $params[] = $qualID;
    }
    if($courseID != -1 && $courseID != SITEID)
    {
        $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = userqual.bcgtqualificationid";
    }
    if($groupingID != -1)
    {
        $sql .= " JOIN {groups_members} members ON members.userid = userunit.userid 
            JOIN {groupings_groups} gg ON gg.groupid = members.groupid";
    }
    $sql .= " WHERE userunit.bcgtunitid = ? AND u.deleted != ?"; 
    $params[] = $unitID;
    $params[] = 1;
    if($qualID != -1)
    {
        $sql .= " AND userunit.bcgtqualificationid = ? ";  
        $params[] = $qualID;
    }
    if($courseID != -1 && $courseID != SITEID)
    {
        $sql .= " AND coursequal.courseid = ?";
        $params[] = $courseID;
    }
    if($groupingID != -1)
    {
        $sql .= " AND gg.groupingid = ?";
        $params[] = $groupingID;
    }   
    $sql .= " ORDER BY u.lastname ASC, u.firstname ASC, u.username ASC";
    
    if ($limit !== false)
    {
        $sql .= " LIMIT ";
        if ($limitFrom !== false){
            $sql .= "{$limitFrom},";
        }
        $sql .= $limit;
    }
    
    return $DB->get_records_sql($sql, $params);
}

/**
 * Checks if the user is a student on a tracking sheet,
 * @param type $userID
 * @return boolean
 */
function does_user_have_tracking_sheets($userID)
{
    global $DB;
    $studentRole = $DB->get_record_sql('SELECT * FROM {role} WHERE shortname = ? ', array('student'));
    $trackingSheets = get_users_quals($userID, $studentRole->id);
    if($trackingSheets)
    {
        return true;
    }
    return false;
}

function install_plugin($pluginName)
{
    global $CFG;
    //check that the plugin hasnt already been installed
        //instantiate it
            //call its install function.
    //require_once the class
    require_once($CFG->dirroot.'/blocks/bcgt/plugins/'.$pluginName.'/'.$pluginName.'.class.php');
    $plugin = $pluginName::get_instance();
    if($plugin)
    {
        $plugin->install();
    }
}

//function find_new_plugins($install = false)
//{
//    //needs to check a database table that holds the plugins. 
//}

function is_plugin_installed($name)
{
    global $DB;
    $sql = "SELECT * FROM {block_bcgt_plugins} WHERE name = ?";
    return $DB->get_record_sql($sql, array($name));
}

function update_session_qual($studentID, $qualID, $qualification, $unit = null)
{
        
    $sessionQuals = isset($_SESSION['session_stu_quals'])? unserialize(urldecode($_SESSION['session_stu_quals'])) : array();
        
    $qualArray = array();
    if(array_key_exists($studentID, $sessionQuals))
    {
        $qualArray = $sessionQuals[$studentID];
    }
    if(array_key_exists($qualID, $qualArray))
    {
        $qualObject = $qualArray[$qualID];
    }
    else 
    {
        $qualObject = new stdClass();
    }

    if (!is_null($unit)){
        
        if ($qualification){
            $qualUnits = $qualification->get_units();
            if (isset($qualUnits[$unit->get_id()])){
                $qualUnits[$unit->get_id()] = $unit;
                $qualification->set_units($qualUnits);
            }
        } /*else {
            $loadParams = new stdClass();
            $loadParams->loadLevel = Qualification::LOADLEVELALL;
            $loadParams->loadAward = true;
            $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
            $qualification->load_student_information($studentID, $loadParams);
        }*/
        
    }
        
    $qualObject->qualification = $qualification;
    $qualArray[$qualID] = $qualObject;
    $sessionQuals[$studentID] = $qualArray;
    $_SESSION['session_stu_quals'] = urlencode(serialize($sessionQuals));
    
}

function update_session_unit($studentID, $unitID, $unit, $qualID){
    
    global $DB;
    
    $sessionUnits = isset($_SESSION['session_unit'])? unserialize(urldecode($_SESSION['session_unit'])) : array();
            
    if(array_key_exists($unitID, $sessionUnits))
    {
        $unitObject = $sessionUnits[$unitID];
        $qualArray = $unitObject->qualArray;
    }
    else
    {
        //it hasnt been loaded into the session before! (can it even get here if this is the case?)
        //then we need to add it
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
        
        $unitObject = new stdClass();
        $unitObject->unit = Unit::get_unit_class_id($unitID, $loadParams);
        $qualArray = array();
    }
    
    
    if(array_key_exists($qualID, $qualArray))
    {
        $studentArray = $qualArray[$qualID];
    }
    else
    {
        $studentArray = array();
    }
    
    if(array_key_exists($studentID, $studentArray))
    {
        $studentObject = $studentArray[$studentID];
    }
    else
    {
        $studentObject = $DB->get_record_sql("SELECT * FROM {user} WHERE id = ?", array($studentID));
    }
    $studentObject->unit = $unit;    
    $studentArray[$studentID] = $studentObject;
    $qualArray[$qualID] = $studentArray;
    $unitObject->qualArray = $qualArray;
    $sessionUnits[$unitID] = $unitObject;
    $_SESSION['session_unit'] = urlencode(serialize($sessionUnits));    

    
}

function get_student_qual_from_session($qualID, $studentID)
{
    $sessionQuals = isset($_SESSION['session_stu_quals'])? 
    unserialize(urldecode($_SESSION['session_stu_quals'])) : array(); 

    $qualObject = new stdClass();
    $qualification = null;
    //this will be an array of studentID => qualarray->qual object->qual
    //does the qual exist already for this student?
    if(array_key_exists($studentID, $sessionQuals))
    {
        //the sessionsQuals[studentID] is an array of qualid =>object
        //where object has qualification and session start
        $studentQualArray = $sessionQuals[$studentID];
        if(array_key_exists($qualID, $studentQualArray))
        {
            $qualObject = $studentQualArray[$qualID];
            if(isset($qualObject->sessionStartTime))
            {
                $sessionStartTime = $qualObject->sessionStartTime;
            }
            $qualification = $qualObject->qualification;
        }
        else
        {
            $qualObject->sessionStartTime = time();
            $studentQualArray[$qualID] = $qualObject;
            $sessionQuals[$studentID] = $sessionQuals[$studentID];
        }
    }
    else
    {
        $qualObject->sessionStartTime = time();
        $qualArray = array();
        $qualArray[$qualID] = $qualObject;
        $sessionQuals[$studentID] = $qualArray;
    }       

    if(!$qualification)
    {
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELALL;
        $loadParams->loadAward = true;
        $loadParams->loadTargets = true;
        $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
        $qualification->load_student_information($studentID, $loadParams);
        
        //we need to put this into the session!
        update_session_qual($studentID, $qualID, $qualification);
    }

    return $qualification;
}

function get_plugin_name($familyID)
{
    global $DB;
    $sql = "SELECT * FROM {block_bcgt_type_family} WHERE id = ?";
    $class = $DB->get_record_sql($sql, array($familyID));
    if($class)
    {
        return $class->pluginname;
    }
    return false;
}

function get_course_qual_families($courseID, $includeFamilies = array())
{
    global $DB;
    $sql = "SELECT distinct(type.id), type.type, family.id as familyid, family.family, 
        family.classfolderlocation, family.pluginname, type.specificationdesc 
        FROM {block_bcgt_type} type
        JOIN {block_bcgt_target_qual} targetqual ON targetqual.bcgttypeid = type.id 
        JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetqual.id 
        JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id 
        JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid
        WHERE coursequal.courseid = ?";
    $params = array();
    $params[] = $courseID;
    if($includeFamilies && count($includeFamilies) != 0)
    {
        $count = 0;
        $sql .= ' AND ( ';
        foreach($includeFamilies AS $family)
        {
            $count++;
            $sql .= ' family.family = ? OR ';
            $params[] = $family;
        }
        $sql = substr($sql, 0, -3);
        $sql .= ' ) ';
    }
    $records = $DB->get_records_sql($sql, $params);
    if($records)
    {
        $retval = array();
        foreach($records AS $record)
        {   
            $record->id = $record->familyid;
            $retval[$record->familyid] = $record;
        }
        return $retval;
    }
    return false;
}

function insert_activity_onto_unit($record)
{
    global $DB, $USER;
    $record->createdby = $USER->id;
    $record->created = time();
    $record->updatedby = $USER->id;
    $record->updated = time();
    $DB->insert_record('block_bcgt_activity_refs', $record);
}

function delete_activity_from_unit($cmID, $unitID)
{
    global $DB;
    $DB->delete_records('block_bcgt_activity_refs', array('coursemoduleid'=>$cmID, 
         'bcgtunitid'=>$unitID));
}

function delete_activity_by_qual_from_unit($cmID, $qualID, $unitID)
{
    global $DB;
    $DB->delete_records('block_bcgt_activity_refs', array('coursemoduleid'=>$cmID, 
        'bcgtqualificationid'=>$qualID, 'bcgtunitid'=>$unitID));
}

function delete_activity_by_criteria_from_unit($cmID, $criteriaID, $unitID)
{
    global $DB;
    $DB->delete_records('block_bcgt_activity_refs', array('coursemoduleid'=>$cmID, 
        'bcgtcriteriaid'=>$criteriaID, 'bcgtunitid'=>$unitID));
}

function get_activity_criteria($cmID, $quals = null, $unitID = -1)
{
    global $DB;
    $sql = "SELECT distinct(criteria.id), criteria.* FROM {block_bcgt_criteria} criteria 
        JOIN {block_bcgt_activity_refs} refs ON refs.bcgtcriteriaid = criteria.id 
        WHERE refs.coursemoduleid = ?";
    $params = array($cmID);
    if($quals)
    {
        $count = 1;
        $sql .= 'AND refs.bcgtqualificationid IN (';
        foreach($quals AS $qual)
        {
            if($count != 1)
            {
                $sql .= ',';
            }
            $sql .= '?';
            $params[] = $qual->id;
            $count++;
        }
        $sql .= ')';
    }
    if($unitID != -1)
    {
        $sql .= ' AND refs.bcgtunitid = ?';
        $params[] = $unitID;
    }
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_mod_unit_criteria($courseID = -1, $qualID = -1, 
            $groupingID = -1, $cmID = -1)
{
    global $DB;
    $sql = "SELECT refs.id, refs.bcgtunitid, refs.bcgtcriteriaid, crit.name as critname, refs.coursemoduleid 
        FROM {block_bcgt_activity_refs} refs 
        JOIN {block_bcgt_criteria} crit ON crit.id = refs.bcgtcriteriaid";
    if($courseID != -1 || $groupingID != -1)
    {
        $sql .= " JOIN {course_modules} mods ON mods.id = refs.coursemoduleid";
    }
    $params = array();
    if($courseID != -1 || $qualID != -1 || $groupingID != -1)
    {
        $and = false; 
        $sql .= " WHERE";
        if($courseID != -1)
        {
            $sql .= " mods.course = ?";
            $params[] = $courseID;
            $and = true;
        }
        if($qualID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $sql .= " refs.bcgtqualificationid = ?";
            $params[] = $qualID;
            $and = true;
        }
        if($groupingID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $sql .= " mods.groupingid = ?";
            $params[] = $groupingID;
            $and = true;
        }
    }
    if($cmID != -1)
    {
        $sql .= " AND refs.coursemoduleid = ?";
        $params[] = $cmID;
    }
    $records = $DB->get_records_sql($sql, $params);
    if($records)
    {
        $retval = array();
        foreach($records AS $record)
        {
            if(array_key_exists($record->bcgtunitid, $retval))
            {
                $criteriaArray = $retval[$record->bcgtunitid];
            }
            else
            {
                $criteriaArray = array();
            }
            $criteriaArray[$record->bcgtcriteriaid] = $record->critname;
            $retval[$record->bcgtunitid] = $criteriaArray;
        }
        return $retval;
    }
    return false;
}

/**
 * This gets the quals that were selected
 * for an activity and for a specific unit if given
 * @global type $DB
 * @param type $cmID
 * @param type $unitID
 * @return type
 */
function get_activity_quals($cmID, $unitID = -1)
{
    global $DB;
    $sql = "SELECT distinct(qual.id), qual.* FROM {block_bcgt_qualification} qual 
        JOIN {block_bcgt_activity_refs} refs ON refs.bcgtqualificationid = qual.id 
        WHERE refs.coursemoduleid = ?";
    $params = array($cmID);
    if($unitID != -1)
    {
        $sql .= ' AND refs.bcgtunitid = ?';
        $params[] = $unitID;
    }
    return $DB->get_records_sql($sql, $params);
}

function get_activity_units($cmID, $familyID = -1)
{
    global $DB;
    
    $params = array($cmID);
    
    $sql = "SELECT distinct(unit.id), unit.*
        FROM {block_bcgt_unit} unit
        JOIN {block_bcgt_activity_refs} refs ON refs.bcgtunitid = unit.id 
        JOIN {block_bcgt_type} t ON t.id = unit.bcgttypeid
        WHERE refs.coursemoduleid = ?";
    
    if ($familyID > 0){
        $sql .= " AND t.bcgttypefamilyid = ?";
        $params[] = $familyID;
    }
    
    
    return $DB->get_records_sql($sql, $params);
}

function get_activity_units_criteria($cmID, $unitID)
{
    global $DB;
    $sql = "SELECT distinct(criteria.id), criteria.*
        FROM {block_bcgt_criteria} criteria
        JOIN {block_bcgt_activity_refs} refs ON refs.bcgtcriteriaid = criteria.id 
        WHERE refs.coursemoduleid = ? AND refs.bcgtunitid = ?";
    return $DB->get_records_sql($sql, array($cmID, $unitID));
}

function bcgt_get_coursemodules_types_in_course($courseID = -1, $qualID = -1, 
        $groupingID = -1, $criteriaID = -1, $unitID = -1)
{
    global $DB;
    $sql = "SELECT distinct(mods.id), mods.* FROM {modules} mods 
        JOIN {course_modules} cmods ON cmods.module = mods.id 
        JOIN {block_bcgt_activity_refs} refs ON refs.coursemoduleid = cmods.id";
    $params = array();
    if($courseID != -1 || $qualID != -1 || $groupingID != -1 
            || $criteriaID != -1 || $unitID != -1)
    {
        $sql .= " WHERE";
        $and = false;
        if($courseID != -1)
        {
            $sql .= " cmods.course = ?";
            $and = true;
            $params[] = $courseID;
        }
        if($qualID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $sql .= " refs.bcgtqualificationid = ?";
            $and = true;
            $params[] = $qualID;
        }
        if($groupingID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $sql .= " cmods.groupingid = ?";
            $params[] = $groupingID;
            $and = true;
        }
        if($criteriaID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $and = true;
            $sql .= " refs.bcgtcriteriaid = ?";
            $params[] = $criteriaID;
        }
        if($unitID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $sql .= " refs.bcgtunitid = ?";
            $params[] = $unitID;
            $and = true;
        }
        
    }
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_activity_mod_details($cmID)
{
    global $DB;
    $sql = "SELECT distinct(cmods.id), items.courseid, items.itemname as name, 
        items.itemtype as type, items.itemmodule as module, items.iteminstance, 
        cmods.module AS cmodule, cmods.section, cmods.groupingid, cmods.instance AS instanceid
        FROM {grade_items} items 
        JOIN {modules} mods ON mods.name = items.itemmodule
        JOIN {course_modules} cmods ON cmods.module = mods.id 
        AND cmods.instance = items.iteminstance
        WHERE cmods.id = ?";
    $params = array();
    $params[] = $cmID;
    return $DB->get_record_sql($sql, $params);
}

function get_criteria_distinct_mods($criteriaID, $courseID = -1, $qualID = -1, 
                                    $groupingID = -1)
{
    global $DB;
    $sql = "SELECT mods.name as name, count(mods.name) as count FROM {modules} mods 
        JOIN {course_modules} cmods ON cmods.module = mods.id 
        JOIN {block_bcgt_activity_refs} refs ON refs.coursemoduleid = cmods.id 
        WHERE refs.bcgtcriteriaid = ? ";
    $params = array();
    $params[] = $criteriaID;
    if($courseID != -1)
    {
        $sql .= " AND cmods.course = ?";
        $params[] = $courseID;
    }
    if($qualID != -1)
    {
        $sql .= " AND refs.bcgtqualificationid = ?";
        $params[] = $qualID;
    }
    if($groupingID != -1)
    {
        $sql .= " AND cmods.groupingid = ?";
        $params[] = $groupingID;
    }
    $sql .= " GROUP BY mods.name";
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_coursemodules_in_course($courseID, $qualID = -1, $groupingID = -1)
{
    global $DB;
    $sql = "SELECT distinct(cmods.id), items.courseid, items.itemname as name, 
        items.itemtype as type, items.itemmodule as module, items.iteminstance, 
        cmods.module AS cmodule, cmods.section, cmods.groupingid, cmods.instance AS instanceid 
        FROM {grade_items} items 
        JOIN {modules} mods ON mods.name = items.itemmodule
        JOIN {course_modules} cmods ON cmods.module = mods.id 
        AND cmods.course = ? AND cmods.instance = items.iteminstance";
    if($qualID != -1)
    {
        $sql .= " JOIN {block_bcgt_activity_refs} refs ON refs.coursemoduleid = cmods.id";
    }    
    $sql .= " WHERE items.courseid = ? AND items.itemtype=? AND mods.visible = ? AND cmods.visible = ?";
    $params = array($courseID, $courseID, 'mod', 1, 1);
    if($groupingID != -1)
    {
        $sql .= " AND cmods.groupingid = ?";
        $params[] = $groupingID;
    }
    if($qualID != -1)
    {
        $sql .= " AND refs.bcgtqualificationid = ?";
        $params[] = $qualID;
    }
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_quals_on_course_modules($courseID = -1, $groupingID = -1, 
        $search = '', $userID = -1, $cmID = -1)
{
    global $DB;
    $sql = "SELECT distinct(qual.id), qual.* FROM {block_bcgt_qualification} qual 
        JOIN {block_bcgt_activity_refs} refs ON refs.bcgtqualificationid = qual.id 
        JOIN {course_modules} cmods ON cmods.id = refs.coursemoduleid 
        JOIN {modules} mods ON mods.id = cmods.module 
        JOIN {course} course ON course.id = cmods.course
        JOIN {grade_items} items ON items.itemmodule = mods.name AND cmods.instance = items.iteminstance 
        JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = refs.bcgtqualificationid 
        ";
    $params = array();
    $sql .= " WHERE items.itemtype = ? AND mods.visible = ? AND cmods.visible = ?";
    $params[] = 'mod';
    $params[] = 1;
    $params[] = 1;
    if($courseID != -1)
    {
        $sql .= " AND items.courseid = ? AND cmods.course = ?";
        $params[] = $courseID;
        $params[] = $courseID;
    }
    if($groupingID != -1)
    {
        $sql .= " AND cmods.groupingid = ?";
        $params[] = $groupingID;
    }
    if($search != '')
    {
        $sql .= ' AND (items.itemname LIKE ?';
        $params[] = '%'.$search.'%';
        $searchSplit = explode(' ', $search);
        if($searchSplit)
        {
            foreach($searchSplit AS $split)
            {
                $sql .= ' OR items.itemname LIKE ?';
                $params[] = '%'.$split.'%';
            }
        }
        $sql .= ')';
    }
    if($userID != -1)
    {
        $sql .= ' AND userqual.userid = ?';
        $params[] = $userID;
    }
    if($cmID != -1)
    {
        $sql .= " AND cmods.id = ?";
        $params[] = $cmID;
    }
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_coursemodules($courseID = -1, $qualID = -1, $groupingID = -1, 
        $search = '', $userID = -1, $cmID = -1)
{
    global $DB;
    $sql = "SELECT distinct(cmods.id), items.courseid, items.itemname as name, 
        items.itemtype as type, items.itemmodule as module, items.iteminstance, 
        cmods.module AS cmodule, cmods.section, cmods.groupingid, cmods.instance AS instanceid, 
        course.shortname as shortname, course.fullname as fullname
        FROM {grade_items} items 
        JOIN {modules} mods ON mods.name = items.itemmodule
        JOIN {course_modules} cmods ON cmods.module = mods.id";
    $params = array();
    if($courseID != -1)
    {
        $sql .= " AND cmods.course = ?";
        $params[] = $courseID;
    }
    $sql .= " AND cmods.instance = items.iteminstance";  
    $sql .= " JOIN {course} course ON course.id = cmods.course 
        JOIN {block_bcgt_activity_refs} refs ON refs.coursemoduleid = cmods.id
        JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = refs.bcgtqualificationid ";
    $sql .= " WHERE items.itemtype = ? AND mods.visible = ? AND cmods.visible = ?";
    $params[] = 'mod';
    $params[] = 1;
    $params[] = 1;
    if($courseID != -1)
    {
        $sql .= " AND items.courseid = ?";
        $params[] = $courseID;
    }
    if($groupingID != -1)
    {
        $sql .= " AND cmods.groupingid = ?";
        $params[] = $groupingID;
    }
    if($qualID != -1)
    {
        $sql .= " AND refs.bcgtqualificationid = ?";
        $params[] = $qualID;
    }
    if($search != '')
    {
        $sql .= ' AND (items.itemname LIKE ?';
        $params[] = '%'.$search.'%';
        $searchSplit = explode(' ', $search);
        if($searchSplit)
        {
            foreach($searchSplit AS $split)
            {
                $sql .= ' OR items.itemname LIKE ?';
                $params[] = '%'.$split.'%';
            }
        }
        $sql .= ')';
    }
    if($userID != -1)
    {
        $sql .= ' AND userqual.userid = ?';
        $params[] = $userID;
    }
    if($cmID != -1)
    {
        $sql .= " AND cmods.id = ?";
        $params[] = $cmID;
    }
                
    return $DB->get_records_sql($sql, $params);
}

function count_courses_qual_activities($courseID = -1, $qualID = -1, $groupingID = -1, 
        $search = '', $userID = -1, $cmID = -1)
{
    global $DB;
    $sql = "SELECT COUNT(distinct(cmods.course)) as count 
        FROM {grade_items} items 
        JOIN {modules} mods ON mods.name = items.itemmodule
        JOIN {course_modules} cmods ON cmods.module = mods.id 
        JOIN {block_bcgt_activity_refs} refs ON refs.coursemoduleid = cmods.id
        JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = refs.bcgtqualificationid ";
    $params = array();
    if($courseID != -1 || $qualID != -1 || $groupingID != -1 ||
            $search != '' || $userID != -1 || $cmID != -1)
    {
        $sql .= " WHERE";
        $and = false;
        if($courseID != -1)
        {
            $sql .= " cmods.course = ?";
            $params[] = $courseID;
        }
        if($qualID != -1)
        {
            if($and)
            {
                $sql .= " AND";
            } 
            $and = true;
            $sql .= " refs.bcgtqualificationid = ?";
            $params[] = $qualID;
        }
        if($groupingID != -1)
        {
            if($and)
            {
                $sql .= " AND";
            }   
            $and = true;
            $sql .= " cmods.groupingid = ?";
            $params[] = $groupingID;
        } 
        if($search != '')
        {
            $sql .= ' AND (items.itemname LIKE ?';
            $params[] = '%'.$search.'%';
            $searchSplit = explode(' ', $search);
            if($searchSplit)
            {
                foreach($searchSplit AS $split)
                {
                    $sql .= ' OR items.itemname LIKE ?';
                    $params[] = '%'.$split.'%';
                }
            }
            $sql .= ')';
        }
        if($userID != -1)
        {
            $sql .= ' AND userqual.userid = ?';
            $params[] = $userID;
        }
        if($cmID != -1)
        {
            $sql .= " AND cmods.id = ?";
            $params[] = $cmID;
        }
    }
    return $DB->get_record_sql($sql, $params);
}

function count_qual_activities($courseID = -1, $qualID = -1, $groupingID = -1, 
        $search = '', $userID = -1, $cmID = -1)
{
    global $DB;
    $sql = "SELECT COUNT(distinct(refs.bcgtqualificationid)) as count 
        FROM {grade_items} items 
        JOIN {modules} mods ON mods.name = items.itemmodule
        JOIN {course_modules} cmods ON cmods.module = mods.id 
        JOIN {block_bcgt_activity_refs} refs ON refs.coursemoduleid = cmods.id
        JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = refs.bcgtqualificationid ";
    $params = array();
    if($courseID != -1 || $qualID != -1 || $groupingID != -1 ||
            $search != '' || $userID != -1 || $cmID != -1)
    {
        $sql .= " WHERE";
        $and = false;
        if($courseID != -1)
        {
            $sql .= " cmods.course = ?";
            $params[] = $courseID;
        }
        if($qualID != -1)
        {
            if($and)
            {
                $sql .= " AND";
            } 
            $and = true;
            $sql .= " refs.bcgtqualificationid = ?";
            $params[] = $qualID;
        }
        if($groupingID != -1)
        {
            if($and)
            {
                $sql .= " AND";
            }   
            $and = true;
            $sql .= " cmods.groupingid = ?";
            $params[] = $groupingID;
        } 
        if($search != '')
        {
            $sql .= ' AND (items.itemname LIKE ?';
            $params[] = '%'.$search.'%';
            $searchSplit = explode(' ', $search);
            if($searchSplit)
            {
                foreach($searchSplit AS $split)
                {
                    $sql .= ' OR items.itemname LIKE ?';
                    $params[] = '%'.$split.'%';
                }
            }
            $sql .= ')';
        }
        if($userID != -1)
        {
            $sql .= ' AND userqual.userid = ?';
            $params[] = $userID;
        }
        if($cmID != -1)
        {
            $sql .= " AND cmods.id = ?";
            $params[] = $cmID;
        }
    }
    return $DB->get_record_sql($sql, $params);
}

function bcgt_html($str, $nl2br = false){
    if ($nl2br) return nl2br( htmlspecialchars($str, ENT_QUOTES) );
    else return htmlspecialchars($str, ENT_QUOTES);
}



/**
 * Handle enrolments onto a course
 * Add the student to relevant quals & units if they're not already on them
 * @param type $eventData
 * @return true - This has to return true or it will mark it as different in the db and never run it again...
 */
function event_handle_user_enrolled($eventData){
    echo "Event Handle User Enrolled in <br />";
    global $DB;
    
    $userID = $eventData->userid;
    $courseID = $eventData->courseid;
    
    // Enrol id is wrong, it brings down the default role not the actual role_assignment
    $context = $DB->get_record("context", array("contextlevel" => CONTEXT_COURSE, "instanceid" => $courseID));
    if (!$context) return true; # Has to eb true or the event won't ever work again...
    
    $ra = $DB->get_record("role_assignments", array("contextid" => $context->id, "userid" => $userID));
    if (!$ra) return true;
    
    $roleID = $ra->roleid;
    $role = ($roleID == 5) ? 'student' : 'teacher';
    
    //now we need to do the group:
    //config: Use cron events to keep groups up to date with new enrolments
    $group = new Group();
    $grouping = new Grouping();
    if(get_config('bcgt', 'usegroupsingradetracker') && get_config('bcgt', 'usecrongroupsync'))
    {
        mtrace("Attempting Groups");
        $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($courseID));
        //is it a student?
        if($role == 'student')
        {
//            mtrace("Attempting Student");
            //Student:
            //Add student to whole group for course
            $groupID = $group->check_create_add_user_group($courseID, $course->shortname, $userID);
            $grouping->create_grouping_and_add_group($courseID, $course->shortname, $groupID);
            //find children
            $children = bcgt_get_child_courses($courseID);
            if($children)
            {
//                mtrace("Course has child courses :".count($children));
                foreach($children AS $child)
                {
                    $appendName = $group->get_meta_course_group_name_string($child->id);
//                    mtrace($child->id." IS Child ID");
                    $group->duplicate_groups_and_groupings_between_courses($child->id, $courseID, $appendName);
                }
//                mtrace("Done child courses");
            }
             
            //find meta
                    //does the meta course have all groups from this course?
                    //if not
                    //create and add correct students
            $metas = bcgt_get_meta_courses($courseID);  
            if($metas)
            {
                $appendName = $group->get_meta_course_group_name_string($courseID);
//                mtrace("Course has meta courses");
                foreach($metas AS $meta)
                {
                    $group->duplicate_groups_and_groupings_between_courses($courseID, $meta->id, $appendName);
                }
//                mtrace("Done meta courses");
            }
            else
            {
//                mtrace("No Meta Courses so skipping");
            }
//            mtrace("Finished student group bcgt enrolment");
        }
        elseif($role == 'teacher')
        {
//            mtrace("Attempting Teacher");
            //is it a teacher or non ediing teacher?
            //staff:
            //does course have qual
                //yes
                //add staff to course group pref 
            //find meta courses
                //does course have qual
                //yes
                //is staff on this course?
                //yes
                //does staff have any groups already from this course?
                //no
                //add staff to all groups
            if(bcgt_get_course_quals($courseID))
            {
//                mtrace("This course has a qualification, so adding user");
                //we know that staff is on this course:
                //does staff have any groups already from this course?
                $staffPrefGroups = $group->get_user_bcgt_group_prefs(-1, -1, $courseID, $userID);
                if($staffPrefGroups)
                {
                    //they already have preferances for this course
                    //lets just skip
                }
                else
                {
                    //they dont
                    //so lets add them to the pref of entire course
//                    mtrace("Attempting to find Group on course with: $courseID, $course->shortname This is the whole course group");
                    $groupOnCourse = $group->get_group_on_course($courseID, $course->shortname);
                    if($groupOnCourse)
                    {
//                        mtrace("Adding user to Group Pref: $groupOnCourse->id, $courseID, $userID");
                        $group->add_user_bcgt_group_pref($groupOnCourse->id, $courseID, $userID);
                    }
                        
                }
            }
//            mtrace("Add staff to meta course groups");
            $group->add_staf_to_meta_course_group_prefs($courseID, $userID, $course->shortname);
        }
        else
        {
//            mtrace("Role was: $role");
        }
        mtrace("Done Groups");
    }
    
    // Check if we have this setting enabled
    $setting = get_config('bcgt', 'autoenrolusers');
    if ($setting != '1') return true;
  
    $quals = $DB->get_records("block_bcgt_course_qual", array("courseid" => $courseID));
    if (!$quals) return true;

    $qualIDs = array();
    
    foreach($quals as $qual){
        $qualIDs[] = $qual->bcgtqualificationid;
    }
    echo count($quals)." Quals to be enrolled on Course".$courseID."<br />";   
 
    add_qual_user($qualIDs, $roleID, $userID, $role);
    mtrace("added userid {$userID} to qualids " . implode(",", $qualIDs));

    mtrace("END bcgt user enroled hook");
    return true;
}


function event_handle_user_unenrolled($eventData){
    
    global $DB; 
    
    //now we need to do the group:
    //is it a student?
    //is it a teacher or non ediing teacher?
    //

    $userID = $eventData->userid;
    $courseID = $eventData->courseid;

    $group = new Group();
    if(get_config('bcgt', 'usegroupsingradetracker') && get_config('bcgt', 'usecrongroupsync'))
    {
//        mtrace("Attempting Groups");
        //is it a student? 
        //staff:
            //does course have qual
                //yes
                //remove staff from course group pref 
            //find meta courses
                //does course have qual
                //yes
                //is staff on this course?
                //yes
                //remove staff from course group pref
            if(bcgt_get_course_quals($courseID))
            {
//                mtrace("Course has quals. Remove staff pref");
                //we know that staff was on this course:
                //remove the staff from any of the groups on this course. 
                $group->remove_user_bcgt_pref_from_all_groups_on_course($courseID, $userID);
            }
//            mtrace("Process Meta Course group prefs");
            $group->remove_staff_from_meta_course_group_prefs($courseID, $userID);
            
            
            //all
        //get course
            //any empty groups?
            //any empty groupings?
        //find metas
            //any empty groups?
            //any empty groupings?
        //find child
            //any empty groups?
            //any empty groupings?
//        mtrace("Clear empty groups and groupings on course");
        $group->clear_empty_groups_and_groupings_course($courseID);
        $children = bcgt_get_child_courses($courseID);
        if($children)
        {
//            mtrace("Clear empty groups and groupings on child courses");
            foreach($children AS $child)
            {
                $group->clear_empty_groups_and_groupings_course($child->id);
            }
        }   
        $metas = bcgt_get_meta_courses($courseID);  
        if($metas)
        {
//            mtrace("Clear empty groups and groupings on meta courses");
            foreach($metas AS $meta)
            {
                $group->clear_empty_groups_and_groupings_course($meta->id);
            }
        }
    }
    
//    mtrace("End Groups");
    
    // Check if we have this setting enabled
//    mtrace("checking bcgt auto unenrol setting");
    $setting = get_config('bcgt', 'autounenrolusers');
    if ($setting != '1') return true;
        
    $quals = $DB->get_records("block_bcgt_course_qual", array("courseid" => $courseID));
    if (!$quals) return true;
    
    $qualIDs = array();
    
    foreach($quals as $qual){
        $qualIDs[] = $qual->bcgtqualificationid;
    }
        
    
    // The event data doesn't give us the correct roleid because it's shit so we'll just have to remove all records for the
    // user on the qual
    $roles = $DB->get_records("role");
    foreach($roles as $role){
        remove_qual_user($qualIDs, $role->id, $userID);
    }
    
//    mtrace("END bcgt user un-enroled hook");
    return true;
    
}

/**
 * Get a dsitcint list of all quals user is on and has ever been on
 * @param type $userID
 */
function bcgt_get_all_users_quals($userID){
    
    global $DB;
    
    $sql = "SELECT DISTINCT uq.bcgtqualificationid,
                CASE
                    WHEN q.id IS NULL THEN 'F'
                    WHEN q.id IS NOT NULL THEN 'T'
                END as qualExists
            FROM {block_bcgt_user_qual} uq
            LEFT JOIN {block_bcgt_qualification} q ON q.id = uq.bcgtqualificationid
            WHERE uq.userid = ?

            UNION

            SELECT DISTINCT uqh.bcgtqualificationid,
                CASE
                    WHEN q.id IS NULL THEN 'F'
                    WHEN q.id IS NOT NULL THEN 'T'
                END as qualExists
            FROM {block_bcgt_user_qual_his} uqh
            LEFT JOIN {block_bcgt_qualification} q ON q.id = uqh.bcgtqualificationid
            WHERE uqh.userid = ?";
    
    $params = array($userID, $userID);
    
    // This gives us the qual ids and whether ot not they are still in qual or in qual_history
    $records = $DB->get_records_sql($sql, $params);
    
    // Now let's get the info about the qual - name, level, type, etc...
    $quals = array();
        
    if ($records)
    {
        foreach($records as $record)
        {
            
            // If it exists get from qual, else get from qual_history
            if ($record->qualexists == 'T')
            {
                $sql = "SELECT DISTINCT q.id, t.type, q.name, l.trackinglevel, s.subtype
                    FROM {block_bcgt_qualification} q
                    INNER JOIN {block_bcgt_target_qual} tq ON tq.id = q.bcgttargetqualid
                    INNER JOIN {block_bcgt_type} t ON t.id = tq.bcgttypeid
                    INNER JOIN {block_bcgt_level} l ON l.id = tq.bcgtlevelid
                    INNER JOIN {block_bcgt_subtype} s ON s.id = tq.bcgtsubtypeid
                    WHERE q.id = ?";            }
            else
            {
                $sql = "SELECT DISTINCT qh.bcgtqualificationid as id, t.type, qh.name, l.trackinglevel, s.subtype
                    FROM {block_bcgt_qualification_his} qh
                    INNER JOIN {block_bcgt_target_qual} tq ON tq.id = qh.bcgttargetqualid
                    INNER JOIN {block_bcgt_type} t ON t.id = tq.bcgttypeid
                    INNER JOIN {block_bcgt_level} l ON l.id = tq.bcgtlevelid
                    INNER JOIN {block_bcgt_subtype} s ON s.id = tq.bcgtsubtypeid
                    WHERE qh.bcgtqualificationid = ?";  
            }
            
            $info = $DB->get_record_sql($sql, array($record->bcgtqualificationid));
            if ($info)
            {
                $quals[$info->id] = $info;
            }
            
            
        }
    }
    
        
    return $quals;
    
}

function bcgt_get_qualification_family_ID($family)
{
    global $DB;
    $sql = "SELECT * FROM {block_bcgt_type_family} WHERE family = ?";
    $record = $DB->get_record_sql($sql, array($family));
    if($record)
    {
        return $record->id;
    }
    return -1;
}    

function get_unit_name_by_id($unitID)
{
	global $DB;
    $record = $DB->get_record("block_bcgt_unit", array("id" => $unitID));
	return ($record) ? $record->name : false;
}

function get_criteria_by_id($id)
{
    global $DB;
    $record = $DB->get_record("block_bcgt_criteria", array("id" => $id));
    return $record;
}

function bcgt_get_users_column_headings($rowSpan = 1, $returnArray = false)
{
    $retval = '';
    if($returnArray)
    {
        $retval = array();
    }
    //picture,username,name,firstname,lastname,email
    $columns = array('picture', 'username','name');
    //need to get the global config record

    $configColumns = get_config('bcgt','btecgridcolumns');
    if($configColumns)
    {
        $columns = explode(",", $configColumns);
    }
    foreach($columns AS $column)
    { 
        trim($column);
        $out = '<th rowspan="'.$rowSpan.'">';
        $out .= get_string(trim($column), 'block_bcgt');
        $out .= '</th>';
        if($returnArray)
        {
            $returnArray[] = $out;
        }
        else
        {
            $retval .=  $out;
        }
        
    }
    return $retval;
}

function bcgt_get_users_columns($user, $qualID = -1)
{
    global $OUTPUT, $CFG;
    
    $view = optional_param('view', '', PARAM_TEXT);
    
    $out = '';
    //picture,username,name,firstname,lastname,email
    $columns = array('picture', 'username','name');
    //need to get the global config record
    $configColumns = get_config('bcgt','btecgridcolumns');
    if($configColumns)
    {
        $columns = explode(",", $configColumns);
    }
    foreach($columns AS $column)
    {
        $out .= '<td>';
        if($qualID != -1)
        {
            //then we are showing a link
            $out .= '<a href="'.$CFG->wwwroot.'/blocks/bcgt/grids/student_grid.php?sID='.$user->id.'&qID='.$qualID.'&view='.$view.'">';
        }
        switch(trim($column))
        {
            case("picture"):
                $out .= $OUTPUT->user_picture($user, array(1));
                break;
            case("username"):
                $out .= $user->username;
                break;
            case("name"):
                $out .= $user->firstname."<br />".$user->lastname;
                break;
            case("firstname"):
                $out .= $user->firstname;
                break;
            case("lastname"):
                $out .= $user->lastname;
                break;
            case("email"):
                $out .= $user->email;
                break;
        }
        if($qualID != -1)
        {
            //then we are showing a link
            $out .= '</a>';
        }
        $out .= '</td>';
    }
    return $out;
}

function bcgt_display_qual_grid_select($qualID, $courseID, $search, $theseUsers = false)
{
    global $CFG, $DB, $COURSE;
    if($courseID == -1)
    {
        $courseID = $COURSE->id;
    }
    
    $rgID = optional_param('registerGroupID', -1, PARAM_INT);
    
    $context = context_course::instance($courseID);
    $out = '';
    // echo $cID;
    $role = $DB->get_record_select('role', 'shortname = ?', array('student'));
    //get all of the students on this qual
    $qualification = Qualification::get_qualification_class_id($qualID);
    if($qualification)
    {
        $canEdit = false;
        if(has_capability('block/bcgt:editstudentgrid', $context))
        {	
            $canEdit = true;
        }
        $advancedMode = false;
        if($qualification->has_advanced_mode())
        {
            $advancedMode = true;
        }
        $out .= '<div>';
        $out .= '<h3>'.$qualification->get_display_name().'</h3>';
        
        if ($theseUsers){
            $users = $theseUsers;
        } else {
            $users = $qualification->get_users($role->id, $search);
        }
        
        $out .= '<table class="qualificationUsers bcgt_table" align="center">';
        $out .= '<thead>';
        $out .= bcgt_get_users_column_headings();
        $out .= '<th>'.get_string('viewsimple', 'block_bcgt').'</th>';
        if($canEdit)
        {
            $out .= '<th>'.get_string('editsimple', 'block_bcgt').'</th>';
        }
        if($advancedMode)
        {
            $out .= '<th>'.get_string('viewadvanced', 'block_bcgt').'</th>';
            if($canEdit)
            {
                $out .= '<th>'.get_string('editadvanced', 'block_bcgt').'</th>';
            }
        }
        if(get_config('bcgt', 'alevelusefa') && $qualification->has_formal_assessments() && $qualification->get_formal_assessments())
        {
            //then we are using formal assessments
            $out .= '<th>'.get_string('formalassessments', 'block_bcgt').'</th>';
        }
        $out .= '</tr></thead><tbody>';
        if($users)
        {
            $link = $CFG->wwwroot.'/blocks/bcgt/grids/student_grid.php?';
            foreach($users AS $user)
            {
                $out .= '<tr>';
                $out .= bcgt_get_users_columns($user);
                $out .= '<td><a href="'.$link.'sID='.$user->id.'&qID='.$qualID.'&g=s&cID='.$courseID.'&rgID='.$rgID.'">'.get_string('viewsimple', 'block_bcgt').'</a></td>';
                if($canEdit)
                {
                    $out .= '<td><a href="'.$link.'sID='.$user->id.'&qID='.$qualID.'&g=se&cID='.$courseID.'&rgID='.$rgID.'">'.get_string('editsimple', 'block_bcgt').'</a></td>';
                }
                if($advancedMode)
                {
                    $out .= '<td><a href="'.$link.'sID='.$user->id.'&qID='.$qualID.'&g=a&cID='.$courseID.'&rgID='.$rgID.'">'.get_string('viewadvanced', 'block_bcgt').'</a></td>';
                    if($canEdit)
                    {
                        $out .= '<td><a href="'.$link.'sID='.$user->id.'&qID='.$qualID.'&g=ae&cID='.$courseID.'&rgID='.$rgID.'">'.get_string('editadvanced', 'block_bcgt').'</a></td>';
                    }
                }
                if(get_config('bcgt', 'alevelusefa') && $qualification->has_formal_assessments() && $qualification->get_formal_assessments())
                {
                    $out .= '<td><a href="'.$CFG->wwwroot.'/blocks/bcgt/'.
                            'grids/ass_grid.php?cID='.$courseID.'&sID='.$user->id.'&qID='.$qualID.'">'.
                            get_string('formalassessments', 'block_bcgt').
                            '</a></td>';
                }
                $out .= '</tr>';
            }
        }
        $out .= '</tbody></table>';
        $out .= '</div>';
    }
    return $out;
}

function bcgt_display_student_grid_select($search, $userID = -1, $studentID = -1)
{
    $out = '';
    $courseID = optional_param('cID', -1, PARAM_INT);
    //basically get all of the users that have quals in the system and find their quals.
    global $DB, $CFG;
    $studenRole = $DB->get_record_select('role', 'shortname = ?', array('student'));
    $sql = "SELECT distinct(userqual.id), u.id AS userid, u.firstname, u.lastname, u.username, u.picture, 
        u.email, u.url, u.imagealt, userqual.bcgtqualificationid FROM {user} u
        JOIN {block_bcgt_user_qual} userqual ON userqual.userid = u.id";
//    if($userID != -1)
//    {
//        //then we want to search for only students that this user can see.
//        $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = userqual.bcgtqualificationid 
//            JOIN {course} course ON course.id = coursequal.courseid 
//            JOIN {context} context ON context.instanceid = course.id 
//            JOIN {role_assignments} roleass ON roleass.contextid = context.id 
//            JOIN {role} role ON role.id = roleass.roleid 
//            JOIN {context} teachcontext ON teachcontext.instanceid = course.id 
//            JOIN {role_assignments} teachroleass ON teachroleass.contextid = teachcontext.id
//            JOIN {role} teachrole ON teachrole.id = teachroleass.roleid";   
//    }
    $sql .= " WHERE u.deleted != ? AND userqual.roleid = ?";
    $params = array(1, $studenRole->id);
    if($search != '')
    {
        $sql .= ' AND (';
        $sql .= bcgt_student_search_db($search, $params);
        
//        $sql .= " AND (user.firstname LIKE ? OR user.lastname LIKE ? 
//                OR user.email LIKE ? OR user.username LIKE ? ";
//        $params[] = '%'.$search.'%';
//        $params[] = '%'.$search.'%';
//        $params[] = '%'.$search.'%';
//        $params[] = '%'.$search.'%';
//        $searchSplit = explode(' ', $search);
//        if($searchSplit)
//        {
//            foreach($searchSplit AS $split)
//            {
//                $sql .= ' OR user.firstname LIKE ? OR user.lastname LIKE ? 
//            OR user.email LIKE ? OR user.username LIKE ? ';
//                $params[] = '%'.$split.'%';
//                $params[] = '%'.$split.'%';
//                $params[] = '%'.$split.'%';
//                $params[] = '%'.$split.'%';
//            }
//        }
        $sql .= ')';
    }
//    if($userID != -1)
//    {
//        $sql .= ' AND role.shortname = ? AND teachrole.shortname = ? AND teachroleass.userid = ?';
//        $params[] = 'student';
//        $params[] = 'editingteacher';
//        $params[] = $userID;
//    }
    if($studentID != -1)
    {
        $sql .= ' AND u.id = ?';
        $params[] = $studentID;
    }
    $sql .= ' ORDER BY lastname ASC';
    $users = $DB->get_records_sql($sql, $params);
    if($users)
    {
        $out = '<table class="bcgtGridSelectTable bcgt_table" align="center">';
        $out .= '<thead><tr>';
        $out .= bcgt_get_users_column_headings();
        $out .= '<th>'.get_string('quals', 'block_bcgt').'</th>';
        $out .= '</tr></thead>';
        $out .= '<tbody><tr>';
        $lastUserID = -1;
        $link = $CFG->wwwroot.'/blocks/bcgt/grids/student_grid.php?g=s';
        foreach($users AS $user)
        {
            $userID = $user->userid;
            //we need the is of user to the userid for the
            //images. 
            $user->id = $user->userid;
            if($lastUserID != $userID)
            {
                if($lastUserID != -1)
                {
                    $out .= '</ul></td>';
                    $out .= '</tr>';
                }
                $lastUserID = $userID;
                $out .= '<tr>';
                $out .= bcgt_get_users_columns($user);
                $out .= '<td><ul>';
                //then we are on a new user
            }
            $qualification = Qualification::get_qualification_class_id($user->bcgtqualificationid);
            if($qualification)
            {
                global $USER;
                //does this user have access to this qualification though?
                $userQualRole = Qualification::does_user_have_access($USER->id, $user->bcgtqualificationid);
                $out .= '<li class="gridSelectQualSelect">';
                if($userQualRole)
                {
                    $out .= '<a href="'.$link.'&sID='.$userID.'&qID='.$user->bcgtqualificationid.'&cID='.$courseID.'">';
                }
                $out .= $qualification->get_display_name();
                if($userQualRole)
                {
                    $out .= '</a>';
                }
                $out .= '</li>';
            }
        }
        $out .= '</ul></td></tr></tbody>';
        $out .= '</table>';
    }
    else
    {
        $out .= '<p>'.get_string('noqualsuser', 'block_bcgt').'</p>';
    }
    return $out;
}

function bcgt_student_search_db($search, &$params)
{
    //if it doesnt have a space
    //then do an equals or a like on that one thing
    //if it has one space
    //if it has more than one space
    $sql = '';
    $sql .= " u.username LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ?";
    $params[] = '%'.$search.'%';
    $params[] = '%'.$search.'%';
    $params[] = '%'.$search.'%';
    $params[] = '%'.$search.'%';
    
    if(substr_count($search,' ') == 1)
    {
        $searchSplit = explode(' ', $search);
        $sql .= " OR u.firstname LIKE ? AND u.lastname LIKE ?";
        $params[] = '%'.$searchSplit[0].'%';
        $params[] = '%'.$searchSplit[1].'%';
    }
    else
    {
        $searchSplit = explode(' ', $search);
        if($searchSplit)
        {
            foreach($searchSplit AS $split)
            {
                $sql .= ' OR u.firstname LIKE ? OR u.lastname LIKE ? 
            OR u.email LIKE ? OR u.username LIKE ? ';
                $params[] = '%'.$split.'%';
                $params[] = '%'.$split.'%';
                $params[] = '%'.$split.'%';
                $params[] = '%'.$split.'%';
            }
        }
    }
    return $sql;
}

function bcgt_display_course_group_unit_grid_select($courseID = -1, $groupingID = -1, 
        $originalCourseID = -1, $unitID = -1, $search = '')
{
    global $CFG, $DB, $COURSE;
    
    $sCID = optional_param('sCID', -1, PARAM_INT);
    $out = '';
    
    //get the students on the group
    //get the quals these guys have access to
    //get the distinct units
    $sql = "SELECT distinct(unit.id), unit.*, family.family FROM {block_bcgt_unit} unit 
        JOIN {block_bcgt_type} type ON type.id = unit.bcgttypeid 
        JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
        JOIN {block_bcgt_qual_units} qualunits ON qualunits.bcgtunitid = unit.id 
        JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qualunits.bcgtqualificationid"; 
      
    if($courseID != -1)
    {
        $sql .= ' JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qualunits.bcgtqualificationid ';
    }
    if($groupingID != -1)
    {
        $sql .= " JOIN {block_bcgt_user_unit} userunit ON userunit.bcgtunitid = qualunits.bcgtunitid AND 
            unit.id = userunit.bcgtunitid";
        $sql .= " JOIN {groups_members} members ON members.userid = userqual.userid 
            AND members.userid = userunit.userid 
            JOIN {groupings_groups} gg ON gg.groupid = members.groupid ";
    }
    $sql .= " WHERE";
    $params = array();
    $and = false;
    if($courseID != -1)
    {
        $sql .= ' coursequal.courseid = ?';
        $params[] = $courseID;
        $and = true;
    }
    if($groupingID != -1)
    {
        if($and)
        {
            $sql .= ' AND';
        }
        $sql .= ' gg.groupingid = ?';
        $params[] = $groupingID;
    }
    if($search != '')
    {
        $sql .= " AND (unit.name LIKE ? OR unit.uniqueid LIKE ?";
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $searchSplit = explode(' ', $search);
        if($searchSplit)
        {
            foreach($searchSplit AS $split)
            {
                $sql .= ' OR unit.name LIKE ? OR unit.uniqueid LIKE ?';
                $params[] = '%'.$split.'%';
                $params[] = '%'.$split.'%';
            }
        }
        $sql .= ')';
    }
    if($unitID != -1)
    {
        $sql .= ' AND unit.id = ?';
        $params[] = $unitID;
    }
    $units = $DB->get_records_sql($sql, $params);
    if($units)
    {
        $out = '<p>'.get_string('groupsunitsdesc', 'block_bcgt').'</p>';
        $out .= '<table class="bcgt_table" align="center">';
        $out .= '<tr>';
        $out .= '<th>'.get_string('uniqueid', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('name', 'block_bcgt').'</th>';
        $out .= '<th colspan="2">'.get_string('grids', 'block_bcgt').'</th>';
        $link = $CFG->wwwroot.'/blocks/bcgt/grids/unit_group_grid.php?g=s';
        foreach($units AS $unit)
        {
            if(count(array_intersect(explode('|',BCGT_UNIT_VIEW_FAMILIES), array($unit->family))) > 0)
            {
                $out .= '<tr>';
                $out .= '<td>'.$unit->uniqueid.'</td>';
                $out .= '<td>'.$unit->name.'</td>';
                $out .= '<td><a href="'.$link.'&uID='.$unit->id.'&grID='.$groupingID.'&g=s&cID='.$originalCourseID.'&scID='.$sCID.'">'.get_string('viewsimple', 'block_bcgt').'</a></td>';
                $out .= '<td><a href="'.$link.'&uID='.$unit->id.'&grID='.$groupingID.'&g=se&cID='.$originalCourseID.'&scID='.$sCID.'">'.get_string('editsimple', 'block_bcgt').'</a></td>';
                $out .= '</tr>';
            }
        }
        $out .= '</table>';
    }
    return $out;
}

function bcgt_display_unit_grid_select_search($search, $familesExcluded = array(), $userID = -1, $unitID = -1)
{
    //get units
    //get the quals they are on
    $out = '';
    $courseID = optional_param('cID', -1, PARAM_INT);
    global $DB, $CFG;
    $params = array();
    $sql = "SELECT distinct(qualunits.id), qualunits.bcgtunitid, qualunits.bcgtqualificationid, unit.name, unit.uniqueid 
        FROM {block_bcgt_unit} unit JOIN {block_bcgt_qual_units} qualunits 
        ON unit.id = qualunits.bcgtunitid 
        JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qualunits.bcgtqualificationid";
    if($familesExcluded && count($familesExcluded) != 0)
    {
        $sql .= " JOIN {block_bcgt_qualification} qual ON qual.id = qualunits.bcgtqualificationid 
            JOIN {block_bcgt_target_qual} targetqual ON targetqual.id = qual.bcgttargetqualid 
            JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
            JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid";
    }
    if($userID != -1)
    {
        //then we need to find the courses that this user can see
        //then find the quals that are on this
        $sql .= ' JOIN {course} course ON course.id = coursequal.courseid 
            JOIN {context} context ON context.instanceid = course.id 
            JOIN {role_assignments} roleass ON roleass.contextid = context.id 
            JOIN {role} role ON role.id = roleass.roleid';
    }
    $sql .= " WHERE (unit.name LIKE ? OR unit.uniqueid LIKE ?";
        $params[] = '%'.$search.'%';
        $params[] = '%'.$search.'%';
        $searchSplit = explode(' ', $search);
        if($searchSplit)
        {
            foreach($searchSplit AS $split)
            {
                $sql .= ' OR unit.name LIKE ? OR unit.uniqueid LIKE ?';
                $params[] = '%'.$split.'%';
                $params[] = '%'.$split.'%';
            }
        }
        $sql .= ')';
    if($familesExcluded && count($familesExcluded) != 0)
    {
        $sql .= ' AND (';
        $count = 0;
        foreach($familesExcluded AS $family)
        {
            $count++;
            if($count != 1)
            {
                $sql .= ' AND';
            }
            $sql .= 'family.family != ?';
        }
        $params[] = $family;
        $sql .= ')';
    }
    if($userID != -1)
    {
        $sql .= ' AND role.shortname = ? AND roleass.userid = ?';
        $params[] = 'editingteacher';
        $params[] = $userID;
    }
    if($unitID != -1)
    {
        $sql .= ' AND unit.id = ?';
        $params[] = $unitID;
    }
    $units = $DB->get_records_sql($sql, $params);
    $viewAll = false;
    if(has_capability('block/bcgt:viewallgrids', context_system::instance()))
    {
        $viewAll = true;
    }
    if($units)
    {
        $out = '<table class="bcgt_table" align="center">';
        $out .= '<tr>';
        $out .= '<th>'.get_string('uniqueid', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('name', 'block_bcgt').'</th>';
        $out .= '<th>'.get_string('quals', 'block_bcgt').'</th>';
        $out .= '</tr>';
        $out .= '<tr>';
        $lastUnitID = -1;
        $link = $CFG->wwwroot.'/blocks/bcgt/grids/unit_grid.php?g=s';
        foreach($units AS $unit)
        {
            $unitID = $unit->bcgtunitid;
            if($lastUnitID != $unitID)
            {
                if($lastUnitID != -1)
                {
                    $out .= '</ul></td></tr>';
                }
                $lastUnitID = $unitID;
                $out .= '<tr>';
                $out .= '<td>'.$unit->uniqueid.'</td>';
                $out .= '<td>'.$unit->name.'</td>';
                $out .= '<td><ul>';
                //then we are on a new user
            }
            $qualification = Qualification::get_qualification_class_id($unit->bcgtqualificationid);
            if($qualification)
            {
                global $USER;
                $userQualRole = Qualification::does_user_have_access($USER->id, $unit->bcgtqualificationid);
                $out .= '<li class="gridSelectQualSelect">';
                if($viewAll || $userQualRole)
                {
                    $out .= '<a href="'.$link.'&uID='.$unitID.'&qID='.$unit->bcgtqualificationid.'&cID='.$courseID.'">';
                }
                $out .= $qualification->get_display_name(false);
                if($viewAll || $userQualRole)
                {
                    $out .= '</a>';
                }
                $out .= '</li>';
            }
        }
        $out .= '</ul></td></tr>';
        $out .= '</table>';
    }
    return $out;
}

function bcgt_display_unit_grid_select($qualID, $courseID, $search, $info = false)
{
    global $DB, $CFG;
    $out = '';
    //get all of the students on this qual
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELUNITS;
    $qualification = Qualification::get_qualification_class_id($qualID, $loadParams);
    if($qualification)
    {
        $out .= '<h3>'.$qualification->get_display_name().' '.get_string('units', 'block_bcgt').'</h3>';
        $units = $qualification->get_units();
        $out .= '<table class="qualificationUsers bcgt_table" align="center">';
        $out .= '<thead><tr><th>'.get_string('uniqueid', 'block_bcgt').'</th><th>'.get_string('name', 'block_bcgt').'</th>'.
                '<th>'.get_string('viewsimple', 'block_bcgt').'</th><th>'.get_string('editsimple', 'block_bcgt').'</th></tr></thead><tbody>';
        if($units)
        {
            $link = $CFG->wwwroot.'/blocks/bcgt/grids/unit_grid.php?';
            if (isset($info['regGrpID']) && $info['regGrpID'] > 0)
            {
                $link .= 'regGrpID=' . $info['regGrpID'];
            }
            foreach($units AS $unit)
            {
                $out .= '<tr>';
                $out .= '<td>'.$unit->get_uniqueID().'</td>';
                $out .= '<td>'.$unit->get_name().'</td>';
                $out .= '<td><a href="'.$link.'&uID='.$unit->get_id().'&qID='.$qualID.'&g=s&cID='.$courseID.'">'.get_string('viewsimple', 'block_bcgt').'</a></td>';
                $out .= '<td><a href="'.$link.'&uID='.$unit->get_id().'&qID='.$qualID.'&g=se&cID='.$courseID.'">'.get_string('editsimple', 'block_bcgt').'</a></td>';
                $out .= '</tr>';
            }
        }
        $out .= '</tbody></table>';
    }
    return $out;
}

function bcgt_display_class_grid_select_search($cID, $search, $qualExcludes, $userID = -1)
{
    global $COURSE;
    if($cID == -1)
    {
        $cID = $COURSE->id;
    }
    $context = context_course::instance($cID);
    $out = '';
    
    //get all of the students on this qual
    $quals = search_qualification(-1, -1, -1, $search, 
        -1, null, -1, false, false, 
        $qualExcludes, $userID);
    if($quals)
    {
        $canEdit = false;
        if(has_capability('block/bcgt:editstudentgrid', $context))
        {	
            $canEdit = true;
        }
        $out .= '<table class="qualificationClass bcgt_table" align="center">';
        $out .= class_qual_select_grid($quals, $cID, $canEdit);
        $out .= '</table>';
    }
    return $out;
}

function bcgt_display_assessment_grid_select_search($search, $userID = -1, $assID = -1)
{
    $retval = '';
    //so, lets either get all of the assessments in the system
    //and then a drop down of all of the quals this is on
    //or get the formal assessments that just we can see: through coursequal, context etc
    //and the quals drop down will be our quals. 
    global $DB, $CFG;
    $cID = optional_param('cID', -1, PARAM_INT);
    $sql = "SELECT distinct(project.id), project.* FROM {block_bcgt_project} project 
        JOIN {block_bcgt_activity_refs} refs ON refs.bcgtprojectid = project.id 
        JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = refs.bcgtqualificationid";
    if($userID != -1)
    {
        $sql .= ' JOIN {context} context ON context.instanceid = coursequal.courseid 
            JOIN {role_assignments} roleass ON roleass.contextid = context.id 
            JOIN {role} role ON role.id = roleass.roleid';
    }
    $params = array();
    if($search != '' || $userID != -1 || $assID != -1)
    {
        $sql .= ' WHERE';
        $and = false;
        if($search != '')
        {
            $and = true;
            $sql .= ' project.name LIKE ?';
            $params[] = '%'.$search.'%';
            $searchSplit = explode(' ', $search);
            if($searchSplit)
            {
                foreach($searchSplit AS $split)
                {
                    $sql .= ' OR project.name LIKE ?';
                    $params[] = '%'.$split.'%';
                }
            }
        }
        if($userID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $and = true;
            $sql .= ' role.shortname = ? AND roleass.userid = ?';
            $params[] = 'editingteacher';
            $params[] = $userID;;
        }
        if($assID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $and = true;
            $sql .= ' project.id = ?';
            $params[] = $assID;
        }
    }
    
    $projects = $DB->get_records_sql($sql, $params);
    if($projects) 
    {
        $retval .= '<table class="bcgt_table" align="center">';
        foreach($projects AS $project)
        {
            $qualLink = $CFG->wwwroot.'/blocks/bcgt/grids/ass_grid_class.php?cID='.$cID.'&g=a';
            $projectLink = $CFG->wwwroot.'/blocks/bcgt/grids/ass.php?cID='.$cID;
            $retval .= '<tr>';
            $retval .= '<th>'.$project->targetdate.' : '.$project->name.'</th>';
            $retval .= '<th>'.get_string('view','block_bcgt').'</th>';
            $retval .= '<th>'.get_string('edit','block_bcgt').'</th>';
            $retval .= '</tr>';
            $projectObj = new Project($project->id, $project);
            $quals = $projectObj->get_editable_quals($userID);
            if($quals)
            {
                foreach($quals AS $qual)
                {
                    $retval .= '<tr>';
                    $retval .= '<td>'.bcgt_get_qualification_display_name($qual).'</td>';
                    $retval .= '<td><a href="'.$projectLink.'&pID='.$project->id.'&qID='.$qual->id.'">'.get_string('view','block_bcgt').'</td>';
                    $retval .= '<td><a href="'.$projectLink.'&pID='.$project->id.'&qID='.$qual->id.'&edit=true">'.get_string('edit','block_bcgt').'</td>';
                    $retval .= '</tr>';
                }
            }
        }   
        $retval .= '</table>';
    }
    else
    {
        $retval .= '<p>'.get_string('nofaselection', 'block_bcgt').'</p>';
    }
    
    return $retval;
}

function bcgt_display_activity_grid_select_search($search, $userID = -1, $actID = -1)
{
    $retval = '';
    //so, lets either get all of the activities in the system
    //or
    global $DB, $CFG;
    $cID = optional_param('cID', -1, PARAM_INT);
    
    //so go and get all of the activities that match this
    $activities = bcgt_get_users_activities($userID, -1, -1, -1, $search, $actID);
    if($activities) 
    {
        $modLinking = load_bcgt_mod_linking();
        $modIcons = load_mod_icons(-1, -1, -1, -1);
        $retval .= '<table class="bcgt_table" align="center">';
        foreach($activities AS $activity)
        {
            //order by due date. 
            $dueDate = get_bcgt_mod_due_date($activity->id, $activity->instanceid, $activity->cmodule, $modLinking);
            $activity->dueDate = $dueDate;
            //the activity
            //then the quals that are on this activity
            $qualLink = $CFG->wwwroot.'/blocks/bcgt/grids/act_grid.php?cID='.$cID.'&g=se';
            $projectLink = $CFG->wwwroot.'/blocks/bcgt/grids/act_grid.php?cID='.$cID;
            $out = '<tr>';
            $out .= '<th>';
            if(array_key_exists($activity->module,$modIcons))
            {
                $icon = $modIcons[$activity->module];
                //show the icon. 
                $out .= html_writer::empty_tag('img', array('src' => $icon,
                            'class' => 'bcgtmodcriticon activityicon', 'alt' => $activity->module));
            }
            $out .= '</th>';//one for the icon
            $out .= '<th>'.$activity->name.'</th>';
            $out .= '<th>';
            if($dueDate)
            {
                $out .= date('d M Y : H:m', $dueDate); 
            }
            $out .= '</th>';
//            $out .= '<th><a href="'.$qualLink.'&cmID='.$activity->id.'">'.get_string('viewall','block_bcgt').'</th>';
//            $out .= '<th><a href="'.$qualLink.'&cmID='.$activity->id.'&g=se">'.get_string('editall','block_bcgt').'</th>';
            $out .= '<th>'.get_string('viewall','block_bcgt').'</th>';
            $out .= '<th>'.get_string('editall','block_bcgt').'</th>';
            $out .= '</tr>';
            $quals = get_bcgt_mod_quals($activity->id);
            if($quals)
            {
                foreach($quals AS $qual)
                {
                    $qualification = Qualification::get_qualification_class_id($qual->id);
                    $out .= '<tr>';
                    $out .= '<td></td>';
                    //one for name of activity, one for due date. 
                    $out .= '<td colspan="2">'.$qualification->get_display_name().'</td>';
                    $out .= '<td><a href="'.$projectLink.'&cmID='.$activity->id.'&qID='.$qual->id.'">'.get_string('view','block_bcgt').'</td>';
                    $out .= '<td><a href="'.$projectLink.'&cmID='.$activity->id.'&qID='.$qual->id.'&g=se">'.get_string('edit','block_bcgt').'</td>';
                    $out .= '</tr>';
                }
            }
            $activity->out = $out;
        }   
        
        require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ModSorter.class.php');
        $modSorter = new ModSorter();
		usort($activities, array($modSorter, "ComparisonDelegateByDueDateObj"));
        foreach($activities AS $activity)
        {
            $retval .= $activity->out;
        }
        
        $retval .= '</table>';
    }
    else
    {
        $retval .= '<p>'.get_string('noactselection', 'block_bcgt').'</p>';
    }
    
    return $retval;
}

function bcgt_display_qual_activity($qualID, $search = '', $userID = -1, $actID = -1, $groupingID = -1)
{
    //selected a qual
    //show the activities that are on this qual. 
    global $CFG;
    $cID = optional_param('cID', -1, PARAM_INT);
    //get the qual
    $qualification = Qualification::get_qualification_class_id($qualID);
    if($qualification)
    {
        $modLinking = load_bcgt_mod_linking();
        $modIcons = load_mod_icons(-1, -1, -1, -1);
        $retval = '<table class="bcgt_table" align="center">';
        $retval .= '<tr>';
        //one for act icon
        //one for act name
        //one for act due date
        //one for the coursename?
        $qualLink = $CFG->wwwroot.'/blocks/bcgt/grids/act_grid.php?cID='.$cID.'&g=a&grID='.$groupingID;
        $projectLink = $CFG->wwwroot.'/blocks/bcgt/grids/act_grid.php?cID='.$cID.'&grID='.$groupingID;
        $retval .= '<th colspan="4">';
        $retval .= $qualification->get_display_name();
        $retval .= '</th>';
        //now get activities 
        $activities = bcgt_get_coursemodules(-1, $qualID, $groupingID, $search, $userID, $actID);
        if($activities)
        {
            $retval .= '<th><a href="'.$qualLink.'&qID='.$qualID.'">'.get_string('viewall','block_bcgt').'</th>';
            $retval .= '<th><a href="'.$qualLink.'&qID='.$qualID.'&g=se">'.get_string('editall','block_bcgt').'</th>';
            $retval .= '</tr>';
            //how many courses does this cover?
            //do we need to show the courses?
            $courseCount = count_courses_qual_activities(-1, $qualID, $groupingID, $search, $userID, $actID);
            foreach($activities AS $activity)
            {
                //order by due date. 
                $dueDate = get_bcgt_mod_due_date($activity->id, $activity->instanceid, $activity->cmodule, $modLinking);
                $activity->dueDate = $dueDate;
                //the activity
                //then the quals that are on this activity 
                $out = '<tr>';
                $out .= '<td>';
                if(array_key_exists($activity->module,$modIcons))
                {
                    $icon = $modIcons[$activity->module];
                    //show the icon. 
                    $out .= html_writer::empty_tag('img', array('src' => $icon,
                                'class' => 'bcgtmodcriticon activityicon', 'alt' => $activity->module));
                }
                $out .= '</td>';//one for the icon
                $out .= '<td>'.$activity->name.'</td>';
                $out .= '<td>';
                if($dueDate)
                {
                    $out .= date('d M Y : H:m', $dueDate); 
                }
                $out .= '</td>';
                $out .= '<td>';
                if($courseCount->count > 1)
                {
                    $out .= $activity->shortname;
                }
                $out .= '</td>';
                $out .= '<td><a href="'.$projectLink.'&cmID='.$activity->id.'">'.get_string('view','block_bcgt').'</td>';
                $out .= '<td><a href="'.$projectLink.'&cmID='.$activity->id.'&g=se">'.get_string('edit','block_bcgt').'</td>';
                $out .= '</tr>';
                $activity->out = $out;
            }   

            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ModSorter.class.php');
            $modSorter = new ModSorter();
            usort($activities, array($modSorter, "ComparisonDelegateByDueDateObj"));
            foreach($activities AS $activity)
            {
                $retval .= $activity->out;
            } 
        }
        else
        {
            $retval .= '</tr>';
        }
        $retval .= '</table>';
    }
    
    
    //give it a header ability to view all by this qual
    return $retval;
    //load the activities on this qual. ability to view by activity. 
}

function bcgt_display_activity_grid_select($courseID, $groupingID = -1)
{
    //load the course
    //option to view by all on course
    
    //load the activities
    //option to view all quals by course
    //then a link to filter by this activity.
    
    //selected a qual
    //show the activities that are on this qual. 
    global $CFG, $DB;
    $cID = optional_param('cID', -1, PARAM_INT);
    //get the qual
    $courseDB = $DB->get_record_sql('SELECT * FROM {course} WHERE id = ?', array($courseID));
    if($courseDB)
    {
        $courseQuals = bcgt_get_course_quals($cID);
        $modLinking = load_bcgt_mod_linking();
        $modIcons = load_mod_icons(-1, -1, -1, -1);
        $retval = '<table class="bcgt_table" align="center">';
        $retval .= '<tr>';
        //one for act icon
        //one for act name
        //one for act due date
        //one for the coursename?
        $qualLink = $CFG->wwwroot.'/blocks/bcgt/grids/act_grid.php?cID='.$cID.'&g=a&grID='.$groupingID;
        $projectLink = $CFG->wwwroot.'/blocks/bcgt/grids/act_grid.php?cID='.$cID.'&grID='.$groupingID;
        $retval .= '<th colspan="3">';
        $retval .= $courseDB->shortname;
        $retval .= '</th>';
        $canEdit = false;
        if($courseQuals && count($courseQuals) == 1)
        {
            $canEdit = true;
            $retval .= '<th><a href="'.$qualLink.'&cID='.$courseID.'">'.get_string('viewall','block_bcgt').'</th>';
            $retval .= '<th><a href="'.$qualLink.'&cID='.$courseID.'&g=se">'.get_string('editall','block_bcgt').'</th>';
        }
        else
        {
            $retval .= '<th>Filter</th>';
        }
        $retval .= '</tr>';

        //now get activities 
        $activities = bcgt_get_coursemodules($courseID, -1, $groupingID, '', -1, -1);
        if($activities)
        {
            //how many courses does this cover?
            //do we need to show the courses?
            $qualCount = count_qual_activities($courseID, -1, $groupingID, '', -1, -1);
            foreach($activities AS $activity)
            {
                //order by due date. 
                $dueDate = get_bcgt_mod_due_date($activity->id, $activity->instanceid, $activity->cmodule, $modLinking);
                $activity->dueDate = $dueDate;
                //the activity
                //then the quals that are on this activity 
                $out = '<tr>';
                $out .= '<td>';
                if(array_key_exists($activity->module,$modIcons))
                {
                    $icon = $modIcons[$activity->module];
                    //show the icon. 
                    $out .= html_writer::empty_tag('img', array('src' => $icon,
                                'class' => 'bcgtmodcriticon activityicon', 'alt' => $activity->module));
                }
                $out .= '</td>';//one for the icon
                $out .= '<td>'.$activity->name.'</td>';
                $out .= '<td>';
                if($dueDate)
                {
                    $out .= date('d M Y : H:m', $dueDate); 
                }
                $out .= '</td>';
                //need to work on it so that it can be view / edit all for multiple quals
//                $out .= '<td>';
                $filterLink = '<a href="?g=a&cID='.$cID.'&course='.$courseID.'&activities='.$activity->id.'">'.get_string('filterbyqual', 'block_bcgt').'</a>';//link to further filter.
//                $out .= '</td>';
                if($canEdit)
                {
                    $out .= '<td><a href="'.$projectLink.'&cmID='.$activity->id.'">'.get_string('view','block_bcgt').'</td>';
                    $out .= '<td><a href="'.$projectLink.'&cmID='.$activity->id.'&g=se">'.get_string('edit','block_bcgt').'</td>';
                }
                else
                {
                    $out .= '<td>'.$filterLink.'</td>';
                }
                $out .= '</tr>';
                $activity->out = $out;
            }   

            require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/ModSorter.class.php');
            $modSorter = new ModSorter();
            usort($activities, array($modSorter, "ComparisonDelegateByDueDateObj"));
            foreach($activities AS $activity)
            {
                $retval .= $activity->out;
            } 
        }
        $retval .= '</table>';
    }
    
    
    //give it a header ability to view all by this qual
    return $retval;
}


function bcgt_display_qual_assessments($qualID, $search = '', $userID = -1, $assID = -1, $groupingID = -1)
{
    global $DB, $CFG;
    $cID = optional_param('cID', -1, PARAM_INT);
    $retval = '';
    $sql = "SELECT distinct(project.id), project.* FROM {block_bcgt_project} project 
        JOIN {block_bcgt_activity_refs} refs ON refs.bcgtprojectid = project.id 
        JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = refs.bcgtqualificationid";
    if($userID != -1)
    {
        $sql .= ' JOIN {context} context ON context.instanceid = coursequal.courseid 
            JOIN {role_assignments} roleass ON roleass.contextid = context.id 
            JOIN {role} role ON role.id = roleass.roleid';
    }
    if($groupingID != -1)
    {
        $sql .= ' JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = refs.bcgtqualificationid 
            JOIN {groups_members} members ON members.userid = userqual.userid 
            JOIN {groupings_groups} gg ON gg.groupid = members.groupid';
    }
    $params = array();
    if($search != '' || $userID != -1 || $assID != -1 || $groupingID != -1 || $qualID != -1)
    {
        $sql .= ' WHERE';
        $and = false;
        if($search != '')
        {
            $and = true;
            $sql .= ' project.name LIKE ?';
            $params[] = '%'.$search.'%';
            $searchSplit = explode(' ', $search);
            if($searchSplit)
            {
                foreach($searchSplit AS $split)
                {
                    $sql .= ' OR project.name LIKE ?';
                    $params[] = '%'.$split.'%';
                }
            }
        }
        if($userID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $and = true;
            $sql .= ' (role.shortname = ? OR role.shortname = ?) AND roleass.userid = ?';
            $params[] = 'editingteacher';
            $params[] = 'teacher';
            $params[] = $userID;;
        }
        if($assID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $and = true;
            $sql .= ' project.id = ?';
            $params[] = $assID;
        }
        if($qualID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $and = true;
            $sql .= ' refs.bcgtqualificationid = ?';
            $params[] = $qualID;
        }
        if($groupingID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $and = true;
            $sql .= ' gg.groupingid = ?';
            $params[] = $groupingID;
        }
    }
    $projects = $DB->get_records_sql($sql, $params);
    if($projects)
    {
        $qualLink = $CFG->wwwroot.'/blocks/bcgt/grids/ass_grid_class.php?cID='.$cID.'&g=a';
        $projectLink = $CFG->wwwroot.'/blocks/bcgt/grids/ass.php?cID='.$cID;
        $qual = Qualification::get_qualification_class_id($qualID);
        $retval .= '<table class="bcgt_table" align="center">';
        $retval .= '<tr>';
        $retval .= '<th>'.$qual->get_display_name().'</th>';
        $retval .= '<th><a href="'.$qualLink.'&qID='.$qualID.'">'.get_string('viewall', 'block_bcgt').'</th>';
        $retval .= '<th><a href="'.$qualLink.'&qID='.$qualID.'&edit=true">'.get_string('editall', 'block_bcgt').'</th>';
        $retval .= '</tr>';
        foreach($projects AS $project)
        {
            $retval .= '<tr>';
            $retval .= '<td>'.$project->targetdate.' : '.$project->name.'</td>';
            $retval .= '<td><a href="'.$projectLink.'&pID='.$project->id.'&qID='.$qualID.'">'.get_string('view','block_bcgt').'</td>';
            $retval .= '<td><a href="'.$projectLink.'&pID='.$project->id.'&qID='.$qualID.'&edit=true">'.get_string('edit','block_bcgt').'</td>';
            $retval .= '</tr>';
        }   
        $retval .= '</table>';
    }
    else
    {
        $retval .= '<p>'.get_string('nofaselection', 'block_bcgt').'</p>';
    }
    return $retval;
}

function bcgt_display_assessment_grid_select($courseID = -1, $groupingID = -1)
{
    //this wants to display the quals that are on 
    //this course and then all of the assessments that can be chosen from
    //the qual will click through to show all, the assessments will just show the
    //ass
    $retval = '';
    global $DB, $CFG;
    //so get all of the quals that are on this course
    //get all of the projects that are on these quals
    $sql = "SELECT distinct(activityrefs.id), qual.id as bcgtqualificationid, project.id as projectid, project.name, project.targetdate FROM {block_bcgt_qualification} qual 
        JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id
        JOIN {block_bcgt_activity_refs} activityrefs ON activityrefs.bcgtqualificationid = qual.id
        JOIN {block_bcgt_project} project ON project.id = activityrefs.bcgtprojectid";
    if($groupingID != -1)
    {
        $sql .= " JOIN {groupings} g ON g.courseid = coursequal.courseid ";
    }
    $sql .= " WHERE project.centrallymanaged = ? ";
    $params = array(1);
    if($courseID != -1 )
    {
        $sql .= ' AND coursequal.courseid = ?';
        $params[] = $courseID;
    }
    if($groupingID != -1)
    {
        $sql .= ' AND g.id = ?';
        $params[] = $groupingID;
    }
    $sql .= ' ORDER BY bcgtqualificationid ASC, project.name ASC';
    $projects = $DB->get_records_sql($sql, $params);
    if($projects)
    {
        $retval .= '<table class="bcgt_table" align="center">';
        $lastQualID = -1;
        $retval .= '<tr>';
        $qualLink = $CFG->wwwroot.'/blocks/bcgt/grids/ass_grid_class.php?cID='.$courseID.'&g=a&grID='.$groupingID;
        $projectLink = $CFG->wwwroot.'/blocks/bcgt/grids/ass.php?cID='.$courseID.'&grID='.$groupingID;
        foreach($projects AS $project)
        {
            if($lastQualID != $project->bcgtqualificationid)
            {
                $lastQualID = $project->bcgtqualificationid;
                //then we are on a new qual
                $retval .= '</tr>';
                $retval .= '<tr>';
                $qualification = Qualification::get_qualification_class_id($project->bcgtqualificationid);
                $retval .= '<th>'.$qualification->get_display_name().'</a></th>';
                $retval .= '<th><a href="'.$qualLink.'&qID='.$lastQualID.'">'.get_string('viewall', 'block_bcgt').'</th>';
                $retval .= '<th><a href="'.$qualLink.'&qID='.$lastQualID.'&edit=true">'.get_string('editall', 'block_bcgt').'</th>';
                $retval .= '</tr>';
            }
            $retval .= '<tr>';
            $retval .= '<td>'.$project->name.'</a></td>';
            $retval .= '<td><a href="'.$projectLink.'&pID='.$project->projectid.'&qID='.$lastQualID.'">'.get_String('view','block_bcgt').'</td>';
            $retval .= '<td><a href="'.$projectLink.'&pID='.$project->projectid.'&qID='.$lastQualID.'&edit=true">'.get_String('edit','block_bcgt').'</td>';
            $retval .= '</tr>';
            
        }
        $retval .= '</tr>';
        $retval .= '</table>';
    }
    else
    {
        $retval .= '<p>'.get_string('nofaselection', 'block_bcgt').'</p>';
    }
    return $retval;
    
}

function bcgt_display_class_grid_select($courseID = -1, $groupingID = -1, $cID = -1, 
        $qualExcludes = array(), $search = '')
{
    global $COURSE;
    if($cID == -1)
    {
        $cID = $COURSE->id;
    }
    $context = context_course::instance($cID);
    $out = '';
    //get all of the students on this qual
    $quals = bcgt_get_course_quals($courseID, -1, -1, $qualExcludes, $search, $groupingID, true);
    if($quals)
    {
        $canEdit = false;
        if(has_capability('block/bcgt:editstudentgrid', $context))
        {	
            $canEdit = true;
        }
        $out .= '<p>'.get_string('classgridnoaccess', 'block_bcgt').'</p>';
        $out .= '<table class="qualificationClass bcgt_table" align="center">';
        $out .= class_qual_select_grid($quals, $cID, $canEdit, $courseID, $groupingID);
        $out .= '</table>';
    }
    else
    {
        $out .= '<p>'.get_string('nocoursequalusers', 'block_bcgt').'</p>';
    }
    return $out;
}

function class_qual_select_grid($quals, $cID, $canEdit, $courseID = -1, $groupingID = -1)
{
    $out = '';
    global $CFG, $USER;
    $link = $CFG->wwwroot.'/blocks/bcgt/grids/class_grid.php?cID='.$cID.'&grID='.$groupingID.'&scID='.$courseID;
    $out .= '<tr>';
    $out .= '<th>'.get_string('family', 'block_bcgt').'</th>';
    $out .= '<th>'.get_string('level', 'block_bcgt').'</th>';
    $out .= '<th>'.get_string('subtype', 'block_bcgt').'</th>';
    $out .= '<th>'.get_string('qual', 'block_bcgt').'</th>';
    $out .= '<th>'.get_string('view', 'block_bcgt').'</th>';
    if($canEdit)
    {
        $out .= '<th>'.get_string('edit', 'block_bcgt').'</th>'; 
    }
    $out .= '</tr>';
    foreach($quals AS $qual)
    {
        //is the user actually on this qual?
        $qualID = $qual->id;
        $loadParams = new stdClass();
        $loadParams->loadLevel = Qualification::LOADLEVELMIN;
        $qualification = Qualification::get_qualification_class_id($qual->id, $loadParams);
        if($qualification)
        { 
            $hasAccess = true;
            if(!has_capability('block/bcgt:viewallgrids', context_system::instance()))
            {
                $hasAccess = Qualification::does_user_have_access($USER->id, $qual->id); 
            }
            $out .= '<tr>'.$qualification->get_display_name(false, ' ', array('additionalname'), 'Table').'';
            $out .= '<td>';
            $class = '';
            if(!$hasAccess)
            {
                $class = 'bcgtnoaccess';
            }
            $out .= '<a class="'.$class.'" href="'.$link.'&qID='.$qualID.'">';
            $out .= get_string('view', 'block_bcgt');
               $out .= '</a>'; 
            $out .= '</td>';
            if($canEdit)
            {
                $out .= '<td>';
                $out .= '<a class="'.$class.'" href="'.$link.'&qID='.$qualID.'&g=se">';
                $out .= get_string('edit', 'block_bcgt');
                $out .= '</a>';
                $out .= '</td>';
            }
            $out .= '</tr>';
        }
    }
    return $out;
}

function check_target_qual_exists($familyID = -1, $typeID = -1, $subTypeID = -1, $levelID = -1)
{
    global $DB;
    $sql = "SELECT * FROM {block_bcgt_target_qual} targetqual 
        JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
        JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
        JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid 
        JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid";
    $params = array();
    if($familyID != -1 || $typeID != -1 || $subTypeID != -1 || $levelID != -1)
    {
        $sql .= " WHERE";
        $and = false;
        if($familyID != -1)
        {
            $sql .= ' family.id = ?';
            $params[] = $familyID;
            $and = true;
        }
        if($typeID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $and = true;
            $sql .= ' type.id = ?';
            $params[] = $typeID;
        }
        if($subTypeID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $and = true;
            $sql .= ' subtype.id = ?';
            $params[] = $subTypeID;
        }
        if($levelID != -1)
        {
            if($and)
            {
                $sql .= ' AND';
            }
            $sql .= ' level.id = ?';
            $params[] = $levelID;
        }
    }
    return $DB->get_records_sql($sql, $params);
}

/**
 * 
 * @global type $DB
 * @param type $quals
 * @param type $courses
 * @param type $users
 * @return type
 */
function bcgt_get_users($quals, $courses, $users)
{
    global $DB;
    $sql = "SELECT distinct(u.id) FROM {user} u 
        JOIN {block_bcgt_user_qual} userqual ON userqual.userid = u.id 
        JOIN {role_assignments} ra ON ra.userid = u.id 
        JOIN {context} con ON con.id = ra.contextid 
        JOIN {course} course ON course.id = con.instanceid ";
    
    if ($quals || $courses || $users)
    {
        $sql .= " WHERE ";
    }
    
    $params = array();
    $count = 0;
    foreach($quals AS $qual)
    {
        $count++;
        $sql .= " userqual.bcgtqualificationid = ?";
        if(count($quals) != $count)
        {
            $sql .= ' OR ';
        }
        $params[] = $qual;
    }
    $count = 0;
    foreach($courses AS $course)
    {
        $count++;
        $sql .= " course.id = ?";
        if(count($courses) != $count)
        {
            $sql .= ' OR ';
        }
        $params[] = $course;
    }
    $count = 0;
    foreach($users AS $user)
    {
        $count++;
        $sql .= " u.id = ?";
        if(count($users) != $count)
        {
            $sql .= ' OR ';
        }
        $params[] = $user;
    }
    return $DB->get_records_sql($sql, $params);
}

function bcgt_count_quals_course($courseID)
{
    global $DB;
    $sql = "SELECT count(id) FROM {block_bcgt_course_qual} WHERE courseid = ?";
    return $DB->count_records_sql($sql, array($courseID));
}


function bcgt_start_timing(){
    
   global $starttime;
   $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime; 
    
}

function bcgt_end_timing(){
    
   global $starttime, $endtime;
   $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   return ($endtime - $starttime); 
    
}

function bcgt_get_stud_grade($type, $studentID, $qualID = false, $courseID = false){
    
    global $DB;
    
    if ($qualID){
        $records = $DB->get_record("block_bcgt_stud_course_grade", array("userid" => $studentID, "qualid" => $qualID, "type" => $type));
    } elseif($courseID) {
        $records = $DB->get_record("block_bcgt_stud_course_grade", array("userid" => $studentID, "courseid" => $courseID, "type" => $type));
    } else {
        $records = $DB->get_records("block_bcgt_stud_course_grade", array("userid" => $studentID, "type" => $type));
    }
        
    if (!$records) return false;
    
    if (!is_array($records)){
        $records = array($records);
    }
    
    $gradeArray = array();
    
    foreach($records as $record)
    {
    
        $setby = $DB->get_record("user", array("id" => $record->setbyuserid));

        $qual = false;
        $course = false;
        $name = '-';

        if ($qualID){
            
            $qual = $DB->get_record("block_bcgt_qualification", array("id" => $qualID));
            if (!$qual) continue;
            
            if ($qual){
                $name = $qual->name;
            }
            
        } elseif ($courseID){
            
            $course = $DB->get_record("course", array("id" => $courseID));
            if (!$course) continue;
            
            if ($course){
                $name = $course->fullname;
            }
            
        } elseif (!is_null($record->qualid)){
            
            $qual = $DB->get_record("block_bcgt_qualification", array("id" => $record->qualid));
            if (!$qual) continue;
            
            if ($qual){
                $name = $qual->name;
            }
            
        } elseif (!is_null($record->courseid)){
            
            $course = $DB->get_record("course", array("id" => $record->courseid));
            if (!$course) continue;
            
            if ($course){
                $name = $course->fullname;
            }
            
        }
        
        // Makes sure they are still on that qual/course
        if ($qual){
            $check = bcgt_get_user_on_qual($qual->id, $studentID);
            if (!$check){
                continue;
            }
        }
        
        if ($course){
            $check = bcgt_is_user_on_course($studentID, $course->id);
            if (!$check){
                continue;
            }
        }
        
        

        $grade = new stdClass();
        $grade->id = $record->recordid;
        $grade->setby = $setby;
        $grade->settime = $record->settime;
        $grade->name = $name;
        $grade->grade = false;
        $grade->ucaspoints = '';

        switch($record->location)
        {

            case 'block_bcgt_target_breakdown':
                $obj = $DB->get_record("block_bcgt_target_breakdown", array("id" => $record->recordid));
                if ($obj)
                {
                    $grade->grade = $obj->targetgrade;
                    $grade->ucaspoints = $obj->ucaspoints;
                }
            break;

            case 'block_bcgt_target_grades':
                $obj = $DB->get_record("block_bcgt_target_grades", array("id" => $record->recordid));
                if ($obj)
                {
                    $grade->grade = $obj->grade;
                    $grade->ucaspoints = $obj->ucaspoints;
                }
            break;

            case 'block_bcgt_custom_grades':
                $obj = $DB->get_record("block_bcgt_custom_grades", array("id" => $record->recordid));
                if ($obj)
                {
                    $grade->grade = $obj->grade;
                    $grade->ucaspoints = $obj->ucaspoints;
                }
            break;

            case 'block_bcgt_bspk_q_grade_vals':
                $obj = $DB->get_record("block_bcgt_bspk_q_grade_vals", array("id" => $record->recordid));
                if ($obj)
                {
                    $grade->grade = $obj->grade;
                    $grade->ucaspoints = $obj->ucaspoints;
                }
            break;


        }
        
        $gradeArray[] = $grade;
    
    }
    
    return $gradeArray;    
    
}


function bcgt_get_qual_possible_grades($qual){
    
    global $DB;
        
    if (isset($qual->bespoke) && $qual->bespoke)
    {

        $awards = $qual->get_possible_awards();
        if ($awards)
        {
            $awardArray = array();
            foreach($awards as $award)
            {
                $awardArray[] = array("id" => $award->id, "grade" => $award->grade, "location" => "block_bcgt_bspk_q_grade_vals");
            }
            $possibleGrades = $awardArray;
        }

    }
    else
    {

        // Check breakdown first
        $breakdown = $DB->get_records("block_bcgt_target_breakdown", array("bcgttargetqualid" => $qual->get_target_qual_id()), "ranking DESC, unitsscoreupper DESC");
        if ($breakdown)
        {

            $courseGrades = array();
            foreach($breakdown as $b)
            {
                $courseGrades[] = array("id" => $b->id, "grade" => $b->targetgrade, "location" => "block_bcgt_target_breakdown");
            }

            $possibleGrades = $courseGrades;

        }


        else
        {

            // If not, try target_grades
            $targetgrades = $DB->get_records("block_bcgt_target_grades", array("bcgttargetqualid" => $qual->get_target_qual_id()), "ranking DESC, upperscore DESC");
            if ($targetgrades)
            {

                $courseGrades = array();
                foreach($targetgrades as $b)
                {
                    $courseGrades[] = array("id" => $b->id, "grade" => $b->grade, "location" => "block_bcgt_target_grades");
                }

                $possibleGrades = $courseGrades;

            }

        }

    }

    return $possibleGrades;
    
}


function bcgt_get_aspirational_target_grade($studentID, $qualID = false, $courseID = false){
    
    return bcgt_get_stud_grade("aspirational", $studentID, $qualID, $courseID);
    
}

function bcgt_get_target_grade($studentID, $qualID = false, $courseID = false){
    
    return bcgt_get_stud_grade("target", $studentID, $qualID, $courseID);
    
}

function bcgt_get_target_qual($family, $level, $subtype)
{
    global $DB;
    $sql = "SELECT tq.* FROM {block_bcgt_target_qual} tq 
        JOIN {block_bcgt_subtype} subtype ON subtype.id = tq.bcgtsubtypeid 
        JOIN {block_bcgt_level} level ON level.id = tq.bcgtlevelid 
        JOIN {block_bcgt_type} type ON type.id = tq.bcgttypeid 
        JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
        WHERE subtype.subtype = ? AND family.family = ? AND level.trackinglevel = ?";
    return $DB->get_record_sql($sql, array($subtype, $family, $level));
}

function bcgt_get_target_qual_id($targetQualID)
{
    global $DB;
    $sql = "SELECT tq.*, family.family, family.id as familyid, subtype.subtype, level.trackinglevel 
        FROM {block_bcgt_target_qual} tq 
        JOIN {block_bcgt_subtype} subtype ON subtype.id = tq.bcgtsubtypeid 
        JOIN {block_bcgt_level} level ON level.id = tq.bcgtlevelid 
        JOIN {block_bcgt_type} type ON type.id = tq.bcgttypeid 
        JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
        WHERE tq.id = ?";
    return $DB->get_record_sql($sql, array($targetQualID));
}

function bcgt_get_target_quals_array($families)
{
    global $DB;
    $sql = "SELECT tq.id, family.family, family.id as familyid, level.trackinglevel, subtype.subtype, type.id as typeid 
        FROM {block_bcgt_target_qual} tq 
        JOIN {block_bcgt_subtype} subtype ON subtype.id = tq.bcgtsubtypeid 
        JOIN {block_bcgt_level} level ON level.id = tq.bcgtlevelid 
        JOIN {block_bcgt_type} type ON type.id = tq.bcgttypeid 
        JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid 
        WHERE ";
    $sql .= " family.family IN (";
    $params = array();
    $count = 0;
    foreach($families AS $family)
    {
        $count++;
        $sql .= "?";
        $params[] = $family;
        if($count != count($families))
        {
            $sql .= ',';
        }
    }
    $sql .= ")";
    $sql .= ' ORDER BY family.family ASC, level.trackinglevel DESC, subtype.subtype ASC';
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_all_target_qual($checkIfUsed = true)
{
    global $DB;
    $sql = "SELECT targetqual.id, family.family, level.trackinglevel, subtype.subtype, 
        targetqual.* FROM {block_bcgt_target_qual} targetqual 
        JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
        JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
        JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid
        JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid";
        if($checkIfUsed)
        {
            $sql .= ' JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetqual.id
                JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id';
        }
    return $DB->get_records_sql($sql, array());
}

function bcgt_get_all_alps_target_quals()
{
    global $DB;
    $sql = "SELECT distinct(targetqual.id), family.family, level.trackinglevel, subtype.subtype, 
        targetqual.* FROM {block_bcgt_target_qual} targetqual 
        JOIN {block_bcgt_type} type ON type.id = targetqual.bcgttypeid 
        JOIN {block_bcgt_subtype} subtype ON subtype.id = targetqual.bcgtsubtypeid 
        JOIN {block_bcgt_level} level ON level.id = targetqual.bcgtlevelid
        JOIN {block_bcgt_type_family} family ON family.id = type.bcgttypefamilyid";
        $sql .= ' JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetqual.id
                JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id';
        $sql .= ' JOIN {block_bcgt_qual_weighting} weighting ON weighting.bcgtqualificationid = qual.id';
    return $DB->get_records_sql($sql, array());
}

function bcgt_flatten_by_keys($array, &$returnArray = false){
    
    if (is_array($returnArray))
    {
                
        foreach($array as $critName => $value)
        {

            $returnArray[] = $critName;
            
            if (is_array($value))
            {
                bcgt_flatten_by_keys($value, $returnArray);
            }

        }
        
        return true;
        
    }
    
    
    
    $return = array();
    
    foreach($array as $critName => $value)
    {
        
        $return[] = $critName;
        
        if (is_array($value))
        {
            bcgt_flatten_by_keys($value, $return);
        }
        
    }
    
    return $return;
    
}


function bcgt_flatten_sub_criteria_array($array, $finalArray, $parent = false)
{
            
    if ($array)
    {
        foreach($array as $key => $value)
        {
            if (is_array($value))
            {
                $finalArray = bcgt_flatten_sub_criteria_array($value, $finalArray, $key);
            }
            else
            {
                if (!in_array($parent, $finalArray)){
                    $finalArray[] = $parent;
                }
                $finalArray[] = $value;
            }
        }
    }
    elseif ($parent)
    {
        if (!in_array($parent, $finalArray)){
            $finalArray[] = $parent;
        }
    }
    
    return $finalArray;
    
}




/**
 * 
 * @global type $DB
 * @param type $courseID
 * @param type $qualID
 * @param type $unitID
 * @param type $moduleName
 * @param type $orderBy
 * @param type $extraField
 * @param type $activityID
 * @return type
 */
function bcgt_get_activities_on_course($courseID, $qualID = -1, $unitID = -1, $moduleName = 'assign', 
        $orderBy = '', $extraField = '', $activityID = -1)
{
    global $DB;
    $sql = "SELECT distinct(cm.id), cm.*, m.id as mid, m.name as modname $extraField 
        FROM {block_bcgt_activity_refs} activity 
        JOIN {course_modules} cm ON cm.id = activity.coursemoduleid
        JOIN {modules} md ON md.id = cm.module 
        JOIN {".$moduleName."} m ON m.id = cm.instance 
        WHERE cm.course = ? AND md.name = ?
        ";
        $params = array($courseID, $moduleName);
        if($qualID != -1)
        {
            $sql .= ' AND activity.bcgtqualificationid = ?';
            $params[] = $qualID;
        }
        if($unitID != -1)
        {
            $sql .= ' AND activity.bcgtunitid = ?';
            $params[] = $unitID;
        }
        if($activityID != -1)
        {
            $sql .= " AND cm.id = ?";
            $params[] = $activityID;
        }
            
    return $DB->get_records_sql($sql, $params);
}

function bcgt_get_criteria_activities_on_qual($qualID){
    
    global $DB;
    
    $sql = "select DISTINCT c.*
            from {block_bcgt_activity_refs} refs
            inner join {course_modules} cm ON cm.id = refs.coursemoduleid
            inner join {modules} m ON m.id = cm.module
            inner join {block_bcgt_criteria} c ON c.id = refs.bcgtcriteriaid
            where refs.bcgtqualificationid = ?";
    
    return $DB->get_records_sql($sql, array($qualID));
    
}

function bcgt_get_setting($setting){
    
    global $DB;
    
    $records = $DB->get_records("block_bcgt_settings", array("setting" => $setting));
    if (!$records) return false;
    
    if (count($records) == 1){
        $record = reset($records);
        return $record->value;
    }
    
    $a = array();
    foreach($records as $record)
    {
        $a[] = $record->value;
    }
    
    return $a;
    
}

function bcgt_update_setting($setting, $value){
    
    global $DB;
    
    $check = $DB->get_record("block_bcgt_settings", array("setting" => $setting));
    if ($check)
    {
        $check->value = $value;
        return $DB->update_record("block_bcgt_settings", $check);
    }
    else
    {
        $obj = new stdClass();
        $obj->setting = $setting;
        $obj->value = $value;
        return bcgt_insert_setting($obj);
    }
    
}

function bcgt_insert_setting($obj){
    
    global $DB;
    
    return $DB->insert_record("block_bcgt_settings", $obj);
    
}

function bcgt_convert_custom_url($url, $params){
    
    if (!isset($params['s']) || !isset($params['u']) || !isset($params['q']) || !isset($params['c'])){
        echo "Invalid Parameters sent to bcgt_convert_custom_url";
        exit;
    }
    
    $url = preg_replace("/%sid%/", $params['s'], $url);
    $url = preg_replace("/%uid%/", $params['u'], $url);
    $url = preg_replace("/%qid%/", $params['q'], $url);
    $url = preg_replace("/%cid%/", $params['c'], $url);
    
    return $url;
    
}

/**
 * This gets all of the meta courses for a course
 * @param type $courseID
 */
function bcgt_get_meta_courses($courseID)
{
    global $DB;
    $sql = "SELECT course.* FROM {course} course JOIN {enrol} enrol ON enrol.courseid = course.id 
        WHERE enrol = ? AND customint1 = ? ";
    $params = array();
    $params[] = 'meta';
    $params[] = $courseID;
    return $DB->get_records_sql($sql, $params);
}

/**
 * This gets all of the child courses for a course
 * @global type $DB
 * @param type $courseID
 * @return type
 */
function bcgt_get_child_courses($courseID)
{
    global $DB;
    $sql = "SELECT course.* FROM {course} course JOIN {enrol} enrol ON enrol.customint1 = course.id 
        WHERE enrol = ? AND courseid = ? ";
    $params = array();
    $params[] = 'meta';
    $params[] = $courseID;
    return $DB->get_records_sql($sql, $params);
}



/**
 * Deletes all groups and groupings
 * Firts finds all of the groups and groupings by the groupName
 * gets rid of the dependancies (users and groups)
 * then deletes them.
 * @param type $courseID
 * @param type $groupName
 */
function bcgt_remove_groups_and_groupings($courseID, $groupName)
{
    //get the group(s)
    //remove all users from group
    //delete group
    $group = new Group();
    $groups = $group->get_all_groups_by_name($courseID, $groupName);
    if($groups)
    {
        foreach($groups AS $group)
        {
            
            $actualGroup = new Group($group->id, $group);
            $actualGroup->delete_group();
        }
    }
    
    //get the grouping(s)
    //remove all groups from grouping
    //delete grouping
    
    $grouping = new Grouping();
    $groupingss = $grouping->get_all_groupings_by_name($courseID, $groupName);
    if($groupingss)
    {
        foreach($groupingss AS $grouping)
        {
            
            $actualGrouping = new Grouping($grouping->id, $grouping);
            $actualGrouping->delete_grouping();
        }
    }
}

/**
 * Checks to see if a role assignment exists for the course in Moodle.
 * @global type $DB
 * @param type $userID
 * @param type $courseID
 * @return boolean
 */
function bcgt_is_user_on_course_user($userID, $courseID)
{
    global $DB;
    $sql = "SELECT distinct(u.id), u.* FROM {user} u
        JOIN {role_assignments} roleass ON roleass.userid = u.id 
        JOIN {context} context ON context.id = roleass.contextid 
        JOIN {course} course ON course.id = context.instanceid 
        WHERE course.id = ? AND u.id = ?";
    $params = array();
    $params[] = $courseID;
    $params[] = $userID;
    $records = $DB->get_records_sql($sql, $params);
    if($records)
    {
        return true;
    }
    return false;
}

function get_mod_linking_by_name($mod)
{
    
    global $DB;
    $sql = "SELECT link.*
            FROM {block_bcgt_mod_linking} link 
            INNER JOIN {modules} modules ON modules.id = link.moduleid
            WHERE modules.name = ?";
    $params = array($mod);
    return $DB->get_record_sql($sql, $params);
    
}

function get_mod_linking($id = -1)
{
    global $DB;
    $sql = "SELECT link.*, modules.name as modname FROM {block_bcgt_mod_linking} link 
        JOIN {modules} modules ON modules.id = link.moduleid";
    $params = array();
    if($id != -1)
    {
        $sql .= " WHERE link.id = ?";
        $params[] = $id;
        return $DB->get_record_sql($sql, $params);
    }
    return $DB->get_records_sql($sql, $params);
}

function get_non_used_mods()
{
    global $DB;
    $sql = "SELECT * FROM {modules} WHERE id NOT IN (SELECT moduleid FROM {block_bcgt_mod_linking}) AND visible = 1 ORDER BY name ASC";
    return $DB->get_records_sql($sql, array());
}

function get_used_mod_names()
{
    global $DB;
    $mods = array();
    
    $records = $DB->get_records_sql("SELECT m.name
                                     FROM {modules} m
                                     INNER JOIN {block_bcgt_mod_linking} l ON l.moduleid = m.id");
    
    if ($records)
    {
        foreach($records as $record)
        {
            $mods[] = $record->name;
        }
    }
    
    
    return $mods;
}

function bcgt_get_mod_details($criteriaID = -1, $modType = '', $qualID = -1, $courseID = -1, $groupingID = -1)
{
    //i want the coursemoduleid, the module, the instance name, the courseid, groupingid
    global $DB;
    $sql = "SELECT distinct(cmods.id), items.courseid as courseid, items.itemname as name, 
        items.itemtype as type, items.itemmodule as module, items.iteminstance, 
        cmods.module AS cmodule, cmods.section, cmods.groupingid, cmods.instance AS instanceid 
        FROM {grade_items} items 
        JOIN {modules} mods ON mods.name = items.itemmodule
        JOIN {course_modules} cmods ON cmods.module = mods.id AND cmods.instance = items.iteminstance";
    $params = array();
    if($courseID != -1)
    {
        $sql .= " AND cmods.course = ? ";
        $params[] = $courseID;
    }  
    if($qualID != -1)
    {
        $sql .= " JOIN {block_bcgt_activity_refs} refs ON refs.coursemoduleid = cmods.id";
    }    
    $sql .= " WHERE items.itemtype=? AND mods.visible = ? AND cmods.visible = ?";
    $params[] = 'mod';
    $params[] = 1;
    $params[] = 1;
    if($criteriaID != -1)
    {
        $sql .= " AND refs.bcgtcriteriaid = ?";
        $params[] = $criteriaID;
    }
    if($modType != '')
    {
        $sql .= ' AND itemmodule = ?';
        $params[] = $modType;
    }
    if($courseID != -1)
    {
        $sql .=  ' AND items.courseid = ? AND cmods.course = ?';
        $params[] = $courseID;
        $params[] = $courseID;
    }
    if($groupingID != -1)
    {
        $sql .= " AND cmods.groupingid = ?";
        $params[] = $groupingID;
    }
    if($qualID != -1)
    {
        $sql .= " AND refs.bcgtqualificationid = ?";
        $params[] = $qualID;
    }
    return $DB->get_records_sql($sql, $params);
}

function get_bcgt_mod_due_date($cmID, $instanceID = -1, $modID = -1, $bcgtModLinking = null)
{
    global $DB;
    if($modID == -1 || $instanceID == -1)
    {
        $sql = "SELECT * FROM {course_modules} modules WHERE id = ?";
        $cmRecord = $DB->get_record($sql, array($cmID));
        if(!$cmRecord)
        {
            return false;
        }
        $modID = $cmRecord->module;
        $instanceID = $cmRecord->instance;
    }
    if(!$bcgtModLinking)
    {
        $bcgtModLinking = load_bcgt_mod_linking();
    }
    if(!isset($bcgtModLinking[$modID]))
    {
        return false;
    }
    $modLink = $bcgtModLinking[$modID];
    
    $sql = "SELECT ".$modLink->modtableduedatefname." FROM {".$modLink->modtablename."} WHERE id = ?";
    $modRecord = $DB->get_record_sql($sql, array($instanceID));
    $dueDate = $modRecord->{$modLink->modtableduedatefname};
    
    return $dueDate;
}

function bcgt_get_user_activity_units($userID, $qualID, $cmID)
{
    global $DB;
    $sql = "SELECT * FROM {block_bcgt_unit} unit 
        JOIN {block_bcgt_user_unit} userunit ON userunit.bcgtunitid = unit.id 
        JOIN {block_bcgt_activity_refs} refs ON refs.bcgtunitid = unit.id 
        WHERE refs.bcgtqualificationid = ? AND userunit.userid = ? AND refs.coursemoduleid = ?";
    return $DB->get_record_sql($sql, array($qualID, $userID, $cmID));
}

function get_bcgt_mod_quals($cmID)
{
    global $DB;
    $sql = "SELECT distinct(qual.id), qual.* FROM {block_bcgt_qualification} qual 
        JOIN {block_bcgt_activity_refs} refs ON refs.bcgtqualificationid = qual.id 
        WHERE refs.coursemoduleid = ?";
    $params = array();
    $params[] = $cmID;
    return $DB->get_records_sql($sql, $params);
}

function load_bcgt_mod_linking()
{
    global $DB;
    $sql = "SELECT moduleid, modtablename, modtablecoursefname, modtableduedatefname, 
        modsubmissiontable, submissionuserfname, submissiondatefname, submissionmodidfname, 
        checkforautotracking 
        FROM {block_bcgt_mod_linking}";
    return $DB->get_records_sql($sql, array());
}

function get_mod_unit_summary_table($cmID, $familyID = -1)
    {
    global $CFG;
        $retval = '';
        $activityUnits = get_activity_units($cmID, $familyID);
        if($activityUnits)
        {
            $retval .= '<table class="activityLinksAssignmentGroup modlinkingsummary">';
            foreach($activityUnits AS $activityUnit)
            {
                $retval .= '<tr>';
                $retval .= '<td>';
                $activityCriterias = get_activity_units_criteria($cmID, $activityUnit->id);
                require_once($CFG->dirroot.'/blocks/bcgt/classes/sorters/CriteriaSorter.class.php');
                $critSorter = new CriteriaSorter();
                usort($activityCriterias, array($critSorter, "ComparisonDelegateByDBtName"));
                $retval .= '<table class="activityLinksActivities">';
                $retval .= '<tr><th colspan="'.count($activityCriterias).'">'.$activityUnit->name.'</th></tr>';
                $retval .= '<tr>';
                foreach($activityCriterias AS $activityCriteria)
                {
                    $retval .= '<td>'.$activityCriteria->name.'</td>';
                }
                $retval .= '</tr>';
                $retval .= '</table>';
                $retval .= '</td>';
                $retval .= '</tr>';
            }
            $retval .= '</table>';
        }
        return $retval;
    }
    
    function bcgt_get_criteria_submission_attempted($criteriaID, $courseID = -1, $qualID = -1, 
                                    $groupingID = -1)
{
    $courseJoinSql = " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = 
            qualunits.bcgtqualificationid 
            JOIN {context} con ON con.instanceid = coursequal.courseid 
            JOIN {role_assignments} roleass ON roleass.contextid = con.id 
            JOIN {user} u ON u.id = roleass.userid AND u.id = userunit.userid";
    
    $groupingJoinSql = " JOIN {groups_members} members ON members.userid = u.id 
            JOIN {groupings_groups} gg ON gg.groupid = members.groupid";
    
    $courseWhere = " coursequal.courseid = ?";
    $qualWhere = " qualunits.bcgtqualificationid = ?";
    $groupingWhere = " gg.groupingid = ?";
    
    global $DB;
    $params = array();
    $attemptedSql = "SELECT count(distinct(usercrit.userid)) as count 
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
            AND value.shortvalue != ? AND usercrit.bcgtvalueid IS NOT NULL";
    $params[] = 'WNS';
    $params[] = 'ABS';
    $params[] = 'N/A';
    return $DB->get_record_sql($attemptedSql, $params);
}

function bcgt_get_criteria_submission_achieved($criteriaID, $courseID = -1, $qualID = -1, 
                                    $groupingID = -1)
{
    $courseJoinSql = " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = 
            qualunits.bcgtqualificationid 
            JOIN {context} con ON con.instanceid = coursequal.courseid 
            JOIN {role_assignments} roleass ON roleass.contextid = con.id 
            JOIN {user} u ON u.id = roleass.userid AND u.id = userunit.userid";
    
    $groupingJoinSql = " JOIN {groups_members} members ON members.userid = u.id 
            JOIN {groupings_groups} gg ON gg.groupid = members.groupid";
    
    $courseWhere = " coursequal.courseid = ?";
    $qualWhere = " qualunits.bcgtqualificationid = ?";
    $groupingWhere = " gg.groupingid = ?";
    
    global $DB;
    $params = array(); 
    
    $achievedSql = "SELECT count(distinct(usercrit.userid)) as count 
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
    $achievedSql .= " AND value.shortvalue = ?";
    $params[] = 'A';
    return $DB->get_record_sql($achievedSql, $params);
}

function bcgt_get_users_on_coursemodules($qualID = -1,$courseID = -1, $groupingID = -1, $actID = -1)
{
    global $DB;
    $sql = "SELECT distinct(u.id), u.* FROM {user} u 
        JOIN {block_bcgt_user_qual} userqual ON userqual.userid = u.id 
        JOIN {role_assignments} ra ON ra.userid = u.id 
        JOIN {role} role ON role.id = ra.roleid
        JOIN {context} con ON con.id = ra.contextid 
        JOIN {course} course ON course.id = con.instanceid 
        JOIN {course_modules} cmods ON cmods.course = course.id 
        JOIN {block_bcgt_activity_refs} refs ON refs.coursemoduleid = cmods.id
        JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = refs.bcgtqualificationid 
        AND coursequal.courseid = cmods.course";
    if($groupingID != -1)
    {
        $sql .= " JOIN {groups_members} members ON members.userid = u.id 
            JOIN {groupings_groups} gg ON gg.groupid = members.groupid";
    }
    $sql .= " WHERE cmods.groupingid = ? AND role.shortname = ?";
    $params = array(0, 'student');
    if($qualID != -1)
    {
        $sql .= " AND userqual.bcgtqualificationid = ? AND refs.bcgtqualificationid = ?";
        $params[] = $qualID;
        $params[] = $qualID;
    }
    if($courseID != -1 && $courseID != SITEID)
    {
        $sql .= " AND cmods.course = ?";
        $params[] = $courseID;
    }
    if($groupingID != -1)
    {
        $sql .= " AND gg.groupingid = ?";
        $params[] = $groupingID;
    }
    if($actID != -1)
    {
        $sql .= " AND cmods.id = ?";
        $params[] = $actID;
    }

    $sqlUnion = " UNION ";
    $sql2 = " SELECT distinct(u.id), u.* FROM {user} u 
        JOIN {block_bcgt_user_qual} userqual ON userqual.userid = u.id 
        JOIN {role_assignments} ra ON ra.userid = u.id 
        JOIN {context} con ON con.id = ra.contextid 
        JOIN {course} course ON course.id = con.instanceid 
        JOIN {course_modules} cmods ON cmods.course = course.id
        JOIN {block_bcgt_activity_refs} refs ON refs.coursemoduleid = cmods.id
        JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = refs.bcgtqualificationid 
        AND coursequal.courseid = cmods.course
        JOIN {groupings_groups} gg ON gg.groupingid = cmods.groupingid 
        JOIN {groups_members} members ON members.groupid = gg.groupid AND members.userid = u.id 
        WHERE cmods.groupingid != ? ";
    $params[] = 0;
    if($qualID != -1)
    {
        $sql2 .= " AND userqual.bcgtqualificationid = ? AND refs.bcgtqualificationid = ?";
        $params[] = $qualID;
        $params[] = $qualID;
    }
    if($courseID != -1 && $courseID != SITEID)
    {
        $sql2 .= " AND cmods.course = ?";
        $params[] = $courseID;
    }
    if($groupingID != -1)
    {
        $sql2 .= " AND gg.groupingid = ?";
        $params[] = $groupingID;
    }
    if($actID != -1)
    {
        $sql2 .= " AND cmods.id = ?";
        $params[] = $actID;
    }
    $finalSQL = $sql.$sqlUnion.$sql2;
    $finalSQL .= ' ORDER BY lastname ASC, firstname ASC, username ASC';
    return $DB->get_records_sql($finalSQL, $params);
}

function bcgt_get_criteria_submission_students($criteriaID, $courseID = -1, $qualID = -1, 
                                    $groupingID = -1)
{
    $courseJoinSql = " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = 
            qualunits.bcgtqualificationid 
            JOIN {context} con ON con.instanceid = coursequal.courseid 
            JOIN {role_assignments} roleass ON roleass.contextid = con.id 
            JOIN {user} u ON u.id = roleass.userid AND u.id = userunit.userid";
    
    $groupingJoinSql = " JOIN {groups_members} members ON members.userid = u.id 
            JOIN {groupings_groups} gg ON gg.groupid = members.groupid";
    
    $courseWhere = " coursequal.courseid = ?";
    $qualWhere = " qualunits.bcgtqualificationid = ?";
    $groupingWhere = " gg.groupingid = ?";
    
    global $DB;
    $params = array(); 
    $totalSql = "SELECT count(distinct(userunit.userid)) as count FROM {block_bcgt_user_unit} userunit 
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
    return $DB->get_record_sql($totalSql, $params);
}

function user_view_activity_grids($courseID)
{
    //are they on the front page?
    
    //are they on their course
    
    //does the user have the capibility
    
    //does the user have any qualifications that are BTECs?
    
    //are we allowing activities to be linked to the grade tracker?
    
    //
}

function load_mod_icons($courseID = -1, $qualID = -1, $groupingID = -1, $criteriaID = -1, $unitID = -1)
{
    global $OUTPUT;
    $modIconArray = array();
    //get all of the icons that are on the selection
    $activities = bcgt_get_coursemodules_types_in_course($courseID, $qualID, $groupingID, $criteriaID, $unitID);
    if($activities)
    {
        foreach($activities AS $activity)
        {
            $modIconArray[$activity->name] = $OUTPUT->pix_url('icon', $activity->name);
        }
    }
    return $modIconArray;
}

/**
 * Gets the distinct family of the qualifications a user can see
 * @global type $DB
 * @global type $USER
 * @param type $courseID
 * @param type $seeAll
 * @return type
 */
function bcgt_get_users_qual_families($courseID = -1, $seeAll = false, $returnArray = true)
{
    global $DB, $USER;
    $sql = "SELECT distinct(family.id), family.* FROM {block_bcgt_type_family} family 
        JOIN {block_bcgt_type} type ON type.bcgttypefamilyid = family.id 
        JOIN {block_bcgt_target_qual} targetqual ON targetqual.bcgttypeid = type.id
        JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetqual.id ";
    $params = array();
    if($courseID != -1 && $courseID != SITEID)
    {
        //then we can search by courseid
        $sql .= " JOIN {block_bcgt_course_qual} coursequal ON coursequal.bcgtqualificationid = qual.id";
    }
    if(!$seeAll)
    {
        $sql .= " JOIN {block_bcgt_user_qual} userqual ON userqual.bcgtqualificationid = qual.id ";
    }
    if(($courseID != -1 && $courseID != SITEID) || (!$seeAll))
    {
        $sql .= " WHERE";
        $and = false;
        if(($courseID != -1 && $courseID != 1))
        {
            $sql .= " coursequal.courseid = ?";
            $params[] = $courseID;
            $and = true;
        }
        if(!$seeAll)
        {
            if($and)
            {
                $sql .= " AND";
            }
            $sql .= " userqual.userid = ?";
            $params[] = $USER->id;
            $and = true;
        }
    }
    $retval = array();
    $records = $DB->get_records_sql($sql, $params);
    if($records)
    {
        if($returnArray)
        {
            
            foreach($records AS $family)
            {
                $retval[] = $family->family;
            }
        }
    }
    
    //does it have any bespoke courses?
    if($courseID != -1)
    {
        $bespoke = $DB->get_records_sql("SELECT q.id, q.name, b.displaytype, b.level, b.subtype, 1 as isbespoke
                                        FROM {block_bcgt_course_qual} cq
                                        INNER JOIN {block_bcgt_qualification} q ON q.id = cq.bcgtqualificationid
                                        INNER JOIN {block_bcgt_bespoke_qual} b ON b.bcgtqualid = q.id
                                        WHERE cq.courseid = ?", array($courseID));
        if($bespoke)
        {
            $bespokeFamily = $DB->get_record_sql("SELECT * FROM {block_bcgt_type_family} WHERE family = ?", array('Bespoke'));
            $retval[] = "Bespoke";
            $records[] = $bespokeFamily;
        }
    }
    if($returnArray)
    {
        return $retval;
    }
    elseif($records)
    {
        return $records;
    }
    return null; 
    
}

function bcgt_get_assessment_planner($userID = -1, $qualID = -1, $courseID = -1, $groupingID = -1)
{
    //list of activities
    //for each % achieved,
    //or grade from gradebook
    //link to course
    //criteria on (as in no)
    //criteria achieved (as in no)
    //feedback from grid
    //feedback from gradebook
    
    //colour coded to denote behind
    //date submitted
}

function bcgt_get_assessment_calendar($userID = -1, $qualID = -1, $courseID = -1, $groupingID = -1, $view = '')
{
    //by year
    //by month
    //by unit
    
    //display hidden activities?
    
    //display other calendars from rest of the system. 
}

function bcgt_output_simple_grid_table($data)
{
    $out = '<table>';
    foreach($data AS $rows)
    {
        $out .= '<tr>';
        foreach($rows AS $cell)
        {
            $out .= '<td>'.$cell.'</td>';
        }
        $out .= '</tr>';
    }
    
    $out .= '<table>';
    return  $out;
}

function bcgt_add_mentee($tutorID, $studentID){
            
    global $DB;

    // First thing we need is a context for the type CONTEXT_USER with the given studentID, so that we can assign a role with it
    $student = $DB->get_record("user", array("id" => $studentID));
    if (!$student){
        return false;
    }

    $context = \context_user::instance($student->id);

    if (!$context){
        return false;
    }
    
    $tutorRoleShortname = get_config('bcgt', 'tutorrole');
    $role = $DB->get_record("role", array("shortname" => $tutorRoleShortname));

    if (!$tutorRoleShortname || !$role) return false;

    if ( role_assign($role->id, $tutorID, $context) ){
        return true;
    }
    else
    {
        return false;
    }
            
}

function bcgt_remove_mentee($tutorID, $studentID){
    
     global $DB;

    // First thing we need is a context for the type CONTEXT_USER with the given studentID, so that we can assign a role with it
    $student = $DB->get_record("user", array("id" => $studentID));
    if (!$student){
        return false;
    }

    $context = \context_user::instance($student->id);

    if (!$context){
        return false;
    }
    
    $tutorRoleShortname = get_config('bcgt', 'tutorrole');
    $role = $DB->get_record("role", array("shortname" => $tutorRoleShortname));

    if (!$tutorRoleShortname || !$role) return false;

    if ( role_unassign($role->id, $tutorID, $context->id) ){
        return true;
    }
    else
    {
        return false;
    }
}

function bcgt_get_students_on_course($courseID){
    
    global $DB;
    
    $records = $DB->get_records_sql("SELECT DISTINCT u.*
                                     FROM {user} u
                                     INNER JOIN {role_assignments} r ON r.userid = u.id
                                     INNER JOIN {context} x ON x.id = r.contextid
                                     WHERE x.instanceid = ? AND r.roleid = 5
                                     ORDER BY u.lastname, u.firstname", array($courseID));
    
    return $records;
    
}


function bcgt_get_students_on_tutor($tutorID){
    
    global $DB;
    
    $tutorRoleShortname = get_config('bcgt', 'tutorrole');
    $role = $DB->get_record("role", array("shortname" => $tutorRoleShortname));

    if (!$tutorRoleShortname || !$role) return false;
    
    $records = $DB->get_records_sql("SELECT DISTINCT u.*
                                     FROM {role_assignments} r 
                                     INNER JOIN {context} x ON x.id = r.contextid
                                     INNER JOIN {user} u ON u.id = x.instanceid
                                     WHERE r.userid = ? AND r.roleid = ? AND x.contextlevel = ?
                                     ORDER BY u.lastname, u.firstname", array($tutorID, $role->id, CONTEXT_USER));
    
    return $records;
    
}


function bcgt_get_all_subtypes(){
    
    global $DB;
    
    return $DB->get_records("block_bcgt_subtype", null, "subtype ASC");
    
}

function bcgt_get_all_levels(){
    
    global $DB;
    
    return $DB->get_records("block_bcgt_level", null, "trackinglevel ASC");
    
}

function bcgt_get_qual_types($family, $hasQuals = false, $levels = null)
{
    global $DB;
    $sql = "SELECT distinct(type.id), type.* FROM {block_bcgt_type} type JOIN {block_bcgt_type_family} 
        fam ON fam.id = type.bcgttypefamilyid"; 
    if($hasQuals)
    {
        $sql .= " JOIN {block_bcgt_target_qual} targetqual ON targetqual.bcgttypeid = type.id 
            JOIN {block_bcgt_qualification} qual ON qual.bcgttargetqualid = targetqual.id";
    }
    if($levels)
    {
        $sql .= " JOIN {block_bcgt_target_qual} targetquallevel ON targetquallevel.bcgttypeid = type.id";
    }
    $sql .= " WHERE fam.family = ?";
    $params = array($family);
    if($levels)
    {
        $sql .= " AND targetquallevel.bcgtlevelid IN (";
        $count = 0;
        foreach($levels AS $levelID)
        {
            $count++;
            $sql .= '?';
            if($count != count($levels))
            {
                $sql .= ',';
            }
            $params[] = $levelID;
        }
        $sql .= ")";
        
    }
    return $DB->get_records_sql($sql, $params);
}
    

function bcgt_display_alps_temp($temp, $showCoefficent, $desc = '')
{
    $retval = '';
    $temperature = '';
    $score = '';
    if($temp)
    {
        if($showCoefficent)
        {
            if(isset($temp->number))
            {
                $temperature = $temp->number;
                $score = "{".round($temp->score, 3)."}";
            }
            else
            {
                $temperature = $temp;
            }
        }
        elseif(isset($temp->number))
        {
            $temperature = $temp->number;
        }
        else
        {
            $temperature = $temp;
        }
        $retval .= "<span class='alpstemp alpstemp".$temperature."'>$desc".$temperature." <sub class='alpsscore'>".$score."</sub></span>";
    }
    return $retval;
}
    

function bcgt_get_grid_assignment_overview_buttons($tab, $cID)
    {
        $retval = '<div class="tabs"><div class="tabtree">';
        $retval.= '<ul class="tabrow0">';
        $focus = ($tab == 'os')? 'focus' : '';
        $retval.= '<li class="first '.$focus.'">'.
                '<a href="?view=os&cID='.$cID.'&tab=acheck">'.
                '<span>'.get_string('overviewsimple', 'block_bcgt').'</span></a></li>';
        $focus = ($tab == 'oa')? 'focus' : '';
        $retval.= '<li class="last '.$focus.'">'.
                '<a href="?view=oa&cID='.$cID.'&tab=acheck">'.
                '<span>'.get_string('overviewadvanced', 'block_bcgt').'</span></a></li>';
        $focus = ($tab == 'subatt')? 'focus' : '';
        $retval.= '<li class="last '.$focus.'">'.
                '<a href="?view=subatt&cID='.$cID.'&tab=acheck">'.
                '<span>'.get_string('submissionsattempted', 'block_bcgt').'</span></a></li>';
        $focus = ($tab == 'subach')? 'focus' : '';
        $retval.= '<li class="last '.$focus.'">'.
                '<a href="?view=subach&cID='.$cID.'&tab=acheck">'.
                '<span>'.get_string('submissionsachieved', 'block_bcgt').'</span></a></li>';
        $retval.= '</ul>';
        $retval.= '</div></div>';
        return $retval;
    }


    
function bcgt_save_file($tmpFile, $newName, $dir = false){
    
    global $CFG;
    
    $location = $CFG->dataroot . DIRECTORY_SEPARATOR . 'bcgt' . DIRECTORY_SEPARATOR;
    if ($dir){
        $location .= $dir . DIRECTORY_SEPARATOR;
    }
    
    // If the directory doesn't exist
    if (!is_dir($location))
    {
        // Try to create it
        if (!mkdir($location, $CFG->directorypermissions))
        {
            return false;
        }
    }
    
    $location .= $newName;
    
    // Got this far so directory must exist, try to move the file
    return move_uploaded_file($tmpFile, $location);
    
}

/**
 * 
 * @param type $courseID
 * @param type $moduleID
 * @param type $instanceID
 */
function bcgt_get_course_module($courseID, $moduleID, $instanceID){
    
    global $DB;
    
    $sql = "select cm.*
            from {course_modules} cm
            inner join {modules} m ON m.id = cm.module
            where m.id = ?
            and cm.course = ?
            and cm.instance = ?";
        
    $params = array($moduleID, $courseID, $instanceID);
        
    return $DB->get_record_sql($sql, $params);
    
}

/**
 * Given a coursemodule id, find all the units & criteria linked to it
 * @param type $cmID
 */
function bcgt_get_course_module_criteria($cmID){
    
    global $DB;
    
    $return = array();
    
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELMIN;
    
    $records = $DB->get_records("block_bcgt_activity_refs", array("coursemoduleid" => $cmID));
    if ($records)
    {
        
        foreach($records as $record)
        {
            
                
            // Set array for this qual, if not already set
            if (!isset($return[$record->bcgtqualificationid])){
                $return[$record->bcgtqualificationid] = array();
            }

            // Set array for unit, if not alreayd set
            if (!isset($return[$record->bcgtqualificationid][$record->bcgtunitid])){
                $return[$record->bcgtqualificationid][$record->bcgtunitid] = array();
            }

            $return[$record->bcgtqualificationid][$record->bcgtunitid][] = $record->bcgtcriteriaid;
                
            
        }
        
    }
    
    return $return;
    
}


function bcgt_course_has_criteria_module_links($courseID){
    
    global $DB;
    
    $sql = "SELECT refs.id
            FROM {block_bcgt_activity_refs} refs
            INNER JOIN {course_modules} cm ON cm.id = refs.coursemoduleid
            WHERE cm.course = ?";
    
    $records = $DB->get_records_sql($sql, array($courseID));
    
    return ($records) ? true : false;
    
}


function bcgt_get_course_module_unit_links($cmID, $qualID = false)
{
    
    global $DB;
    
    $sql = "SELECT DISTINCT bcgtunitid
            FROM {block_bcgt_activity_refs}
            WHERE coursemoduleid = ?";
    
    $params = array($cmID);
    
    if ($qualID > 0){
        $sql .= " AND bcgtqualificationid = ?";
        $params[] = $qualID;
    }
    
    $records = $DB->get_records_sql($sql, $params);
    $return = array();
    
    if ($records)
    {
        foreach($records as $record)
        {
            $return[] = $record->bcgtunitid;
        }
    }
    
    return $return;
    
}

function bcgt_is_user_on_any_of_these_units($userID, $units)
{
    
    $return = false;
    
    if ($units)
    {
        foreach($units as $unitID)
        {
            if (bcgt_is_user_on_unit($userID, $unitID))
            {
                $return = true;
            }
        }
    }
    
    return $return;
    
}


function bcgt_course_module_has_criteria_links($cmID, $qualID = false){
    
    global $DB;
    
    $sql = "SELECT id
            FROM {block_bcgt_activity_refs}
            WHERE coursemoduleid = ?";
    
    $params = array($cmID);
    
    if ($qualID > 0){
        $sql .= " AND bcgtqualificationid = ?";
        $params[] = $qualID;
    }
    
    $records = $DB->get_records_sql($sql, $params);
    
    return ($records) ? true : false;
    
}

function bcgt_is_user_in_grouping($userID, $groupingID){
    
    global $DB;
    
    $sql = "SELECT gm.id
            FROM {groupings_groups} gg
            INNER JOIN {groups_members} gm ON gm.groupid = gg.groupid
            where gg.groupingid = ? AND gm.userid = ?";
    
    $record = $DB->get_record_sql($sql, array($groupingID, $userID));
    
    return ($record) ? true : false;
    
}

/**
 * For a given qualid, find all the criteria that are not linked to any activities
 * @param type $qualID
 */
function bcgt_get_unlinked_units_criteria($qual){
    
    global $DB;
    
    $return = array();
    
    $units = $qual->get_units();
    
    if ($units)
    {
        foreach($units as $unit)
        {
            
            $return[$unit->get_id()] = array();
            
            $criteria = $unit->get_criteria();
            if ($criteria)
            {
                foreach($criteria as $criterion)
                {
                    
                    // Check if this is linked to any activity
                    $check = $DB->get_records("block_bcgt_activity_refs", array("bcgtqualificationid" => $qual->get_id(), "bcgtcriteriaid" => $criterion->get_id()));
                    
                    // If it's not, add it to the array
                    if (!$check)
                    {
                        $return[$unit->get_id()][] = $criterion;
                    }
                    
                }
            }
        }
    }
    
    return $return;
    
}

function process_split_grades($totalGrades, $targetQualID, &$totalOverallGrades)
    {
        //array of Key = Grade and Value = countInstances
        $newGrades = $totalGrades;
        foreach($totalGrades AS $grade => $countInstances)
        {
            //get the grade:
            $targetGrade = TargetGrade::retrieve_target_grade(-1, $targetQualID, $grade);
            if(!$targetGrade)
            {
                continue;
            }
            $rank = $targetGrade->get_ranking();
            $whole = floor($rank);
            //if the rank is a decimal place, then we know its a split grade
            if(!is_int($rank) && $rank - $whole != 0)
            {
                //then we want the number before the decimal point to get the whole grade equivalant. 
                $wholeGradeRank = floor($rank);
                //now we want the number of grades between this number and the next one up
                $wholeGradeRankAbove = $wholeGradeRank+1;
                $numberOfSplitGrades = count_split_grades($wholeGradeRank, $wholeGradeRankAbove, $targetQualID);
                $numberOfSplitGaps = ($numberOfSplitGrades->count)+1;
                $splitGradeFraction = 1 / $numberOfSplitGaps;
                
                //for this grade we need to know how many come before it so we 
                //know where its place is. e/g. is it is the 2nd of 4, or is it the 1st of 3
                $numberOfGradesBelow = count_split_grades($wholeGradeRank, $rank, $targetQualID);
                $currentGradePlace = ($numberOfGradesBelow->count) + 1;  
                //now we can add how many grades should be added to the grade above
                //and how many grades should be added to the one below. 
                
                //fraction * $currentGradePlace = number of grades to give to the one above
                //fraction * ($numberOfSplitGrades - $currentGradePlace) = number of grades to give to the one below.
                
                //e.g if we have C = 7, C/B = 7.3, B/C = 7.6 and B = 8. and we are currently looking at B/C
                //If B/C has a count of 9
                //
                //B/C rank = 7.6.. This is a whole rank of 7. Which gives us a C. 
                //
                //There are 2 split grades and 3 gaps. 
                //
                //The fraction is 1/number of gaps = 1/3.
                //
                //Now find its place: There are one split grades before it
                //So its place is 2. 
                //
                //So we know that we want 1/3 * current place
                //1/3 * 2 = 2/3. So we want 2/3 of the number of grades to go to the grade above
                //
                //We want 1/3 to go to the one below. 
                //
                $gradesToGoAbove = round(($splitGradeFraction * $currentGradePlace) * $countInstances, 2);
                $gradesToGoBelow = round(($splitGradeFraction * (($numberOfSplitGaps) - $currentGradePlace)) * $countInstances, 2);

                //now we find the grades above and grades below.
                //get the grade of the rank above
                $targetGrade = TargetGrade::retrieve_target_grade(-1, $targetQualID, '', true, $wholeGradeRankAbove);
                if($targetGrade)
                {
                    $gradeAbove = $targetGrade->get_grade();
                    $aboveRank = $targetGrade->get_ranking();
                    $gradeAboveCount = (isset($newGrades[$gradeAbove]) ? $newGrades[$gradeAbove] : 0);
                    $gradeAboveCount = $gradeAboveCount + $gradesToGoAbove;
                    $newGrades[$gradeAbove] = $gradeAboveCount;#
                    //also need to make sure that the newGrade exists in the totalGrades.
                    //e.g. Imagine that we have A/B and B/C. We dont have the actual B. 
                    //This means, when we loop over the totalGrades, we will skip B, even though we will have calculated them
                    //so lets check and add it
                    if(!isset($totalOverallGrades[$aboveRank]))
                    {
                        $totalOverallGrades[$aboveRank] = $gradeAbove;
                    }
                }
                
                $targetGrade = TargetGrade::retrieve_target_grade(-1, $targetQualID, '', true, $wholeGradeRank);
                if($targetGrade)
                {
                    $gradeBelow = $targetGrade->get_grade();
                    $belowRank = $targetGrade->get_ranking();
                    $gradeBelowCount = (isset($newGrades[$gradeBelow]) ? $newGrades[$gradeBelow] : 0);
                    $gradeBelowCount = $gradeBelowCount + $gradesToGoBelow;
                    $newGrades[$gradeBelow] = $gradeBelowCount;
                    
                    if(!isset($totalOverallGrades[$belowRank]))
                    {
                        $totalOverallGrades[$belowRank] = $gradeBelow;
                    }
                }
            }
        }
        return $newGrades;
    }
    
    function count_split_grades($lowRank, $highRank, $targetQualID)
    {
        global $DB;
        $sql = "SELECT count(grades.id) as count FROM {block_bcgt_target_grades} grades 
            WHERE ranking < ? AND ranking > ? AND bcgttargetqualid = ?";
        return $DB->get_record_sql($sql, array($highRank, $lowRank, $targetQualID));
    }
    
function bcgt_get_course_mod($courseID, $modID, $instanceID)
{
    global $DB;
    return $DB->get_record("course_modules", array("course" => $courseID, "module" => $modID, "instance" => $instanceID));
}

function bcgt_get_activity_refs($cmID){
    
    global $DB;
    $records = $DB->get_records_sql("SELECT r.*, c.name
                                    FROM {block_bcgt_activity_refs} r
                                    INNER JOIN {block_bcgt_criteria} c ON c.id = r.bcgtcriteriaid
                                    WHERE r.coursemoduleid = ?", array($cmID));
    
    $return = array();
    
    if ($records)
    {
        foreach($records as $record)
        {
            if (!isset($return[$record->bcgtunitid]))
            {
                $return[$record->bcgtunitid] = array();
            }
            
            $return[$record->bcgtunitid][] = array(
                'id' => $record->bcgtcriteriaid,
                'name' => $record->name,
                'qualID' => $record->bcgtqualificationid
            );
            
        }
    }
    
    return $return;
}

function bcgt_apply_assignment_grading_box(&$mform, $instance, $mod, $userID)
{
    
    global $DB;
        
    if (!$mform || !$instance || !$mod || !$userID) return false;
        
    // Check if this assignment is linked to any units
    $cm = bcgt_get_course_mod($instance->course, $mod->id, $instance->id);
    if (!$cm) return false;
        
    $activityRefs = bcgt_get_activity_refs($cm->id);
    if (!$activityRefs) return false;
        
    $newArray = array();
    if ($activityRefs)
    {
        foreach($activityRefs as $unitID => $ref)
        {
            if ($ref)
            {
                foreach($ref as $r)
                {
                    
                    if (!array_key_exists($r['qualID'], $newArray))
                    {
                        $newArray[$r['qualID']] = array();
                    }
                    
                    if (!array_key_exists($unitID, $newArray[$r['qualID']]))
                    {
                        $newArray[$r['qualID']][$unitID] = array();
                    }
                    
                    $newArray[$r['qualID']][$unitID][] = $r;
                    
                }
            }
        }
    }
                    
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELALL;
    
    
    $mform->addElement('header', 'gradetrackerheader', get_string('gradetracker2', 'block_bcgt'));
        
    if ($newArray)
    {
    
        foreach($newArray as $qualID => $units)
        {
        
            if ($units)
            {
            
                $qual = Qualification::get_qualification_class_id($qualID);
                if ($qual)
                {
                    
                    $mform->addElement('html', '<br><b>' . $qual->get_display_name() . '</b><br>');

                    foreach($units as $unitID => $criteria)
                    {
                        
                        if ($criteria)
                        {
                            $unit = Unit::get_unit_class_id($unitID, $loadParams);
                            $unit->load_student_information($userID, $qualID, $loadParams);
                            if ($unit)
                            {
                                $mform->addElement('html', $unit->get_display_name() . '<br>');
                                if ($unit->is_student_doing())
                                {
                                    $criteria = $unit->order_criteria_ids($criteria);
                                    foreach($criteria as $crit)
                                    {
                                        $criterion = $unit->get_single_criteria($crit['id']);
                                        if ($criterion)
                                        {
                                            $possibleValues = $criterion->get_possible_values_for_assignment_grading($crit['qualID']);
                                            $el = $criterion->get_grading_form_select($crit, $mform);
                                            if ($possibleValues)
                                            {
                                                foreach($possibleValues as $val => $info)
                                                {
                                                    $criterion->add_grading_form_select_option($val, $info, $el);
                                                }
                                            }

                                            $studentValue = $criterion->get_student_value();
                                            if ($studentValue)
                                            {
                                                $el->setSelected( $studentValue->get_id() );
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $mform->addElement('html', 'Student Not On Unit<br><br>');
                                }
                            }
                        }
                    }
                
                }
            
            }
        
        }
        
    }
            
    $mform->addElement('html', '<br><br><br>');
    
    // If BTEC grading
    $gradingID = $instance->grade;
    if ($gradingID < 0)
    {
        $scale = $DB->get_record("scale", array("id" => -$gradingID));
        if ($scale && $scale->name == "BCGT BTEC Scale (PMD)")
        {
            $mform->addElement('html', "<script> $('#id_grade').bind('change', function(){
                
                var value = $(this).find('option:selected').text();

                $('.GT_P, .GT_M, .GT_D').each(function(){
                        $(this).val(-1);
                    });

                if (value == 'Pass'){
                    
                    $('.GT_P').each(function(){
                        var met = $(this).find('.GT_MET').val();
                        $(this).val(met);
                    });

                } else if(value == 'Merit'){
                
                    $('.GT_P, .GT_M').each(function(){
                        var met = $(this).find('.GT_MET').val();
                        $(this).val(met);
                    });
                    
                } else if(value == 'Distinction'){
                    
                    $('.GT_P, .GT_M, .GT_D').each(function(){
                        var met = $(this).find('.GT_MET').val();
                        $(this).val(met);
                    });

                } 
                
            }); </script>");
        }
    }
    
}

function bcgt_apply_assigment_grades($data, $userID){
        
    if (!$userID || !isset($data->criteria)) return false;
    
    $loadParams = new stdClass();
    $loadParams->loadLevel = Qualification::LOADLEVELALL;
    
    if ($data->criteria)
    {
        foreach($data->criteria as $qualID => $units)
        {
            if ($units)
            {
                foreach($units as $unitID => $criteria)
                {
                    
                    $unit = Unit::get_unit_class_id($unitID, $loadParams);
                    $unit->load_student_information($userID, $qualID, $loadParams);
                    
                    if ($criteria)
                    {
                        foreach($criteria as $critID => $valueID)
                        {
                            
                            $criterion = $unit->get_single_criteria($critID);
                            if ($criterion->get_student_ID() == $userID)
                            {
                            
                                // Update value
                                $criterion->update_students_value($valueID);
                                $criterion->save_student($qualID, true);  
                            
                            }
                                    
                        }
                    }
                    
                    $unit->calculate_unit_award($qualID);
                    
                }
            }
        }
    }
        
}

function bcgt_get_layout(){
    
    $setting = get_config('bcgt', 'pagelayout');
    return ($setting) ? $setting : 'login'; // Return the defined one or the default
    
}

function bcgt_get_categories()
{
    global $DB;
    $sql = "SELECT * FROM {course_categories}";
    return $DB->get_records_sql($sql, array());
}

function bcgt_get_course_from_cat($categoryID)
{
    //get the courses and add them to an array
    //then find sub categories and add them to the array as well by call recursive. 
    
    global $DB;
    $courses = $DB->get_records("course", array("category" => $categoryID), "fullname ASC");
    
    //now get the sub categories. 
    // Cats
    $subCats = $DB->get_records("course_categories", array("parent" => $categoryID));
    if ($subCats)
    {
        foreach($subCats as $subCat)
        {
            $subCourses = bcgt_get_course_from_cat($subCat->id);
            $courses = array_merge($subCourses, $courses);
        }
    }
    
    return $courses;
    
}

function bcgt_get_sub_categories($categoryID)
{
    global $DB;
    $subCats = $DB->get_records("course_categories", array("parent" => $categoryID));
    return $subCats;
}

function bcgt_get_category_courses($categoryID)
{
    global $DB;
    $courses = $DB->get_records("course", array("category" => $categoryID), "fullname ASC");
    return $courses;
}


// Just tutor
function bcgt_wipe_user_register_groups($userID){
    
    global $DB;
    $DB->delete_records("block_bcgt_user_reg_groups", array("userid" => $userID, "type" => "T"));
    
}

// Just the students
function bcgt_wipe_register_group_users($groupID){
    
    global $DB;
    $DB->delete_records("block_bcgt_user_reg_groups", array("registergroupid" => $groupID, "type" => "L"));
    
}

function bcgt_create_register_group($obj){
    
    global $DB;
    
    // Chekc if exists
    $check = $DB->get_record("block_bcgt_register_groups", array("recordid" => $obj->recordid));
    if ($check)
    {
        
        $obj->id = $check->id;
        $DB->update_record("block_bcgt_register_groups", $obj);
        return $obj->id;
        
    }
    else
    {
        
        return $DB->insert_record("block_bcgt_register_groups", $obj);
        
    }
    
}

function bcgt_add_user_to_register_group($groupID, $userID, $type){
    
    global $DB;
    
    $check = $DB->get_record("block_bcgt_user_reg_groups", array("registergroupid" => $groupID, "userid" => $userID));
    if ($check)
    {
        $check->type = $type;
        return $DB->update_record("block_bcgt_user_reg_groups", $check);
    }
    else
    {
        $ins = new stdClass();
        $ins->userid = $userID;
        $ins->registergroupid = $groupID;
        $ins->type = $type;
        return $DB->insert_record("block_bcgt_user_reg_groups", $ins);
    }
    
    
    
}

function bcgt_get_users_register_groups($userID){
    
    global $DB;
    
    return $DB->get_records_sql("SELECT rg.*
                                 FROM {block_bcgt_register_groups} rg
                                 INNER JOIN {block_bcgt_user_reg_groups} urg ON urg.registergroupid = rg.id
                                 WHERE urg.userid = ? AND type = 'T'
                                 ORDER BY rg.recordid", array($userID));
    
}

function bcgt_get_register_group_users($groupID){
    
    global $DB;
    
    return $DB->get_records_sql("SELECT u.*
                                 FROM {user} u
                                 INNER JOIN {block_bcgt_user_reg_groups} urg ON urg.userid = u.id
                                 WHERE urg.registergroupid = ? AND type = 'L'
                                 ORDER BY u.lastname, u.firstname", array($groupID));
    
}

function bcgt_is_user_in_register_group($userID, $groupID){
    global $DB;
    return $DB->get_records("block_bcgt_user_reg_groups", array("userid" => $userID, "registergroupid" => $groupID));
}


function bcgt_fullname($user){
    
    global $DB;
    
    if (is_object($user)){
        return fullname($user);
    } elseif (is_numeric($user)){
        $u = $DB->get_record("user", array("id" => $user));
        return fullname($u) . " ({$u->username})";
    }
    
    return false;
    
}

/**
 * For an array of courses and quals, get all the course mods on them
 * @param type $courseIDArray
 * @param type $qualIDArray
 */
function bcgt_get_course_modules_in_course_qual($courseIDArray, $qualIDArray){
    
    global $DB;
    
    $return = array();
    
    $courseIn = str_repeat('?,', count($courseIDArray) - 1) . '?';
    $qualIn = str_repeat('?,', count($qualIDArray) - 1) . '?';

    $courseModules = $DB->get_records_sql("SELECT DISTINCT cm.*
                                            FROM {block_bcgt_activity_refs} a
                                            INNER JOIN {course_modules} cm ON cm.id = a.coursemoduleid
                                            INNER JOIN {block_bcgt_criteria} c ON c.id = a.bcgtcriteriaid
                                            INNER JOIN {block_bcgt_mod_linking} l ON l.moduleid = cm.module
                                            WHERE cm.course IN ({$courseIn}) AND a.bcgtqualificationid IN ({$qualIn})", array_merge($courseIDArray, $qualIDArray));
                                            
    // For each of the course modules, find its table info
    if ($courseModules)
    {
        foreach($courseModules as $courseModule)
        {
            $info = $DB->get_record("block_bcgt_mod_linking", array("moduleid" => $courseModule->module));
            if ($info)
            {
                $module = $DB->get_record($info->modtablename, array("id" => $courseModule->instance));
                if ($module)
                {
                    $module->courseModuleID = $courseModule->id;
                    $module->moduleInfo = $info;
                    $return[] = $module;
                }
            }
        }
    }
    
    return $return;

    
}

/**
 * 
 * @global type $DB
 * @param type $cmID
 * @param type $qualID
 * @param type $unitID
 * @return type
 */
function bcgt_get_criteria_on_course_module($cmID, $qualID, $unitID)
{
    
    global $DB;
    
    $records = $DB->get_records_sql("SELECT DISTINCT c.*
                                     FROM {block_bcgt_activity_refs} r
                                     INNER JOIN {block_bcgt_criteria} c ON c.id = r.bcgtcriteriaid
                                     WHERE r.coursemoduleid = ? AND r.bcgtqualificationid = ? AND c.bcgtunitid = ?", array($cmID, $qualID, $unitID));
    
    return $records;
    
}


function bcgt_get_turnitin_parts($id)
{
    
    global $DB;
    return $DB->get_records("turnitintool_parts", array("turnitintoolid" => $id, "deleted" => 0));
    
}


/**
 * For a given file path create a code we can use to download that file
 * @global type $DB
 * @param type $path
 * @return type
 */
function bcgt_create_data_path_code($path){
    
    global $DB;
    
    // See if one already exists for this path
    $record = $DB->get_record("block_bcgt_files", array("path" => $path));
    if ($record){
        return $record->code;
    }

    // Create one
    $code = bcgt_rand_str(10);

    // Unlikely, but check if code has already been used
    $cnt = $DB->count_records("block_bcgt_files", array("code" => $code));
    while ($cnt > 0)
    {
        $code = bcgt_rand_str(10);
        $cnt = $DB->count_records("block_bcgt_files", array("code" => $code));
    }
    

    $ins = new \stdClass();
    $ins->path = $path;
    $ins->code = $code;

    $DB->insert_record("block_bcgt_files", $ins);
    return $code;
    
}


/**
 * Create a random string
 * @param type $length
 * @return string
 */
function bcgt_rand_str($length)
{

    $str = "987654321AaBbCcDdEeFfGgHhJjKkMmNnPpQqRrSsTtUuVvWwXxYyZz123456789";

    $count = strlen($str) - 1;

    $output = "";

    for($i = 0; $i < $length; $i++)
    {
        $output .= $str[mt_rand(0, $count)];
    }

    return $output;

}
