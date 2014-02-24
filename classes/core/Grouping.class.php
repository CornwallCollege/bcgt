<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Grouping
 *
 * @author mchaney
 */
class Grouping {
    //put your code here
    protected $courseid;
    protected $name;
    protected $id;
    
    public function Grouping($id = -1, $params = null)
    {
        $this->id = $id;
        if($id != -1)
        {
            if($params)
            {
                $this->extract_params($params);
            }
            else
            {
                $this->load_grouping($id);
            }
        }
        elseif($params)
        {
            $this->extract_params($params);
        }
    }
    
    public function get_id()
    {
        return $this->id;
    }
    
    public function set_name($name)
    {
        $this->name = $name;
    }
    
    public function get_name()
    {
        return $this->name;
    }
    
    public function set_course_id($courseID)
    {
        $this->courseid = $courseID;
    }
    
    public function get_course_id()
    {
        return $this->courseid;
    }

    
    /**
     * Gets the grouping on a course
     * Used to check if a grouping exists
     * @param type $courseID
     * @param type $groupingName
     */
    public function get_grouping_on_course($courseID, $groupingName)
    {
        global $DB;
        $sql = "SELECT groupings.* FROM {groupings} groupings 
            WHERE groupings.courseid = ? AND groupings.name = ?";
        $params = array();
        $params[] = $courseID;
        $params[] = $groupingName;
        return $DB->get_record_sql($sql, $params);
    }
    
    /**
     * gets all of the groups on a grouping
     */
    public function get_groups_in_grouping($groupingID)
    {
        global $DB;
        $sql = "SELECT g.* FROM {groups} g 
            JOIN {groupings_groups} gg ON gg.groupid = g.id 
            WHERE gg.groupingid = ?";
        $params = array();
        $params[] = $groupingID;
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Removs all members from a grouping
     * @global type $DB
     * @param type $groupingID
     */
    public function remove_all_groups_from_grouping($groupingID = -1)
    {
        global $DB;
        if($groupingID == -1)
        {
            $groupingID = $this->id;
        }
        $DB->delete_records('groupings_groups', array("groupingid"=>$groupingID));
    }
    
    /**
     * Counts the members of a group
     * @global type $DB
     * @param type $groupID
     * @return type
     */
    public function count_groups_in_grouping($groupingID)
    {
        global $DB;
        return $DB->count_records_sql("SELECT COUNT(*) FROM {groupings_groups} WHERE groupingid = ?", array($groupingID));
    } 
    
    public function count_users_in_grouping($groupingID)
    {
        $returnCount = 0;
        $groups = $this->get_groups_in_grouping($groupingID);
        if($groups)
        {
            foreach($groups AS $group)
            {
                $groupObj = new Group();
                $returnCount += $groupObj->count_group_members($group->id);
            }
        }
        return $returnCount;
    }
    
    /**
     * Deletes the Grouping
     * First it removes all groups from the grouping
     * Then it deletes the grouping
     * @global type $DB
     * @param type $groupingID
     */
    public function delete_grouping($groupingID = -1)
    {
        global $DB;
        if($groupingID == -1)
        {
            $groupingID = $this->id;
        }
        $this->remove_all_groups_from_grouping($groupingID);
        $DB->delete_records('groupings', array("id"=>$groupingID));
    }
    
    public function remove_group_from_groupings($groupID)
    {
        global $DB;
        $DB->delete_records('groupings_groups', array("groupid"=>$groupID));
    }
    
    
    /**
     * This checks if the groupig exists. 
     * if it doesnt creates it
     * Checks if group is on grouping
     * if not it adds it. 
     * @param type $courseID
     * @param type $groupName
     * @param type $groupID
     */
    public function create_grouping_and_add_group($courseID, $groupName, $groupID)
    {
        $grouping = $this->get_grouping_on_course($courseID, $groupName);
        if(!$grouping)
        {
            $groupingID = $this->create_grouping_on_course($courseID, $groupName);
            $this->add_group_to_grouping($groupID, $groupingID);
        }
        else
        {
            //does group exists on the grouping?
            if(!$this->get_group_in_grouping($groupID, $grouping->id))
            {
                //then the group is not on the grouping
                $this->add_group_to_grouping($groupID, $grouping->id);
            }
        }
    }
    
    /**
     * Gets the group in the grouping record
     * Used to check if the group is already in the grouping
     * @global type $DB
     * @param type $userID
     * @param type $groupID
     * @return type
     */
    public function get_group_in_grouping($groupID, $groupingID)
    {
        global $DB;
        $sql = "SELECT * FROM {groupings_groups} groups WHERE groupid = ? AND groupingid = ?";
        $params = array();
        $params[] = $groupID;
        $params[] = $groupingID;
        return $DB->get_record_sql($sql, $params);
    }
    
    /**
     * Gets the group that matches the group name thats in the grouping
     * Used to check if the group is already in the grouping
     * @global type $DB
     * @param type $userID
     * @param type $groupID
     * @return type
     */
    public function get_group_in_grouping_by_group_name($groupName, $groupingID)
    {
        global $DB;
        $sql = "SELECT g.* FROM {groups} g JOIN {groupings_groups} gg ON
            gg.groupid = g.id WHERE g.name = ? AND gg.groupingid = ?";
        $params = array();
        $params[] = $groupName;
        $params[] = $groupingID;
        return $DB->get_record_sql($sql, $params);
    }
    
    /**
     * Gets the user in the group record
     * Used to check if the user is already in the group
     * @global type $DB
     * @param type $userID
     * @param type $groupID
     * @return type
     */
    public function get_group_in_grouping_by_name($groupID, $groupName)
    {
        global $DB;
        $sql = "SELECT groups.* FROM {groupings_groups} groups 
            JOIN {groupings} groupingss ON groupings.id = groups.groupingid WHERE groupid = ? AND groups.name = ?";
        $params = array();
        $params[] = $groupID;
        $params[] = $groupName;
        return $DB->get_record_sql($sql, $params);
    }
    
    /**
     * Gets all of the groupings on a course. 
     * @global type $DB
     * @param type $courseID
     * @return type
     */
    public function get_all_groupings($courseID)
    {
        global $DB;
        $sql = "SELECT * FROM {groupings} WHERE courseid = ?";
        $params = array($courseID);
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Adds a group to a grouping. 
     * First it checks if the group already exists in the grouping.
     * @global type $DB
     * @param type $groupID
     * @param type $groupingID
     * @return boolean
     */
    public function add_group_to_grouping($groupID, $groupingID)
    {
        global $DB;
        $sql = "SELECT * FROM {groupings_groups} WHERE groupid = ? AND groupingid = ?";
        $params = array();
        $params[] = $groupID;
        $params[] = $groupingID;
        $exists = $DB->get_record_sql($sql, $params);
        if($exists)
        {
            return true;
        }
        
        $record = new stdClass();
        $record->groupid = $groupID;
        $record->groupingid = $groupingID;
        $DB->insert_record('groupings_groups', $record);
    }
    
    /**
     * ◦finds all groupingss by name. 
        ◦Used for a like %childcoursename%
        ◦Used for meta courses and child courses
     * @global type $DB
     * @param type $courseID
     * @param type $groupName
     */
    public function get_all_groupings_by_name($courseID, $groupName)
    {
        global $DB;
        $sql = "SELECT * FROM {groupingss} groupingss WHERE courseid = ? AND name LIKE ?";
        $params = array();
        $params[] = $courseID;
        $params[] = '%'.$groupName.'%';
        return $DB->get_records($sql, $params);
    }
    
    
    /**
     * Gets all of the groupings that have no groups. 
     * @global type $DB
     * @param type $courseID
     */
    public function get_all_empty_groupings($courseID)
    {
        global $DB;
        $sql = "SELECT grouping.* FROM {groupings} grouping 
            WHERE grouping.courseid = ? AND id NOT IN 
            (SELECT grouping.id FROM {groupings} grouping 
            JOIN {groupings_groups} groups ON groups.groupingid = grouping.id)";
        $params = array();
        $params[] = $courseID;
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Saves the Grouping to the database. 
     * It checks if the courseid and name is set
     * Checks if the database is is set, if it is, updates
     * if it isnt then it updats. 
     * @global type $DB
     * @return type
     */
    public function save_grouping()
    {
        global $DB;
        if($this->id == -1)
        {
            if(!isset($this->courseid) || !isset($this->name))
            {
                return -1;
            }
            $params = $this->get_params();
            //we are inserting a new one
            $DB->insert_record('groupings', $params);
        }
        else
        {
            //we are updating one
            if(!isset($this->courseid) || !isset($this->name))
            {
                $this->load_group($this->id);
            }
            $params = $this->get_params();
            $DB->update_record('groupings', $params);
        }
    }
    
    /**
     * This creates a new Grouping in the database
     * @global type $DB
     * @param type $courseID
     * @param type $groupingName
     */
    public function create_grouping_on_course($courseID, $groupingName)
    {
        global $DB;
        $grouping = new stdClass();
        $grouping->courseid = $courseID;
        $grouping->name = $groupingName;
        return $DB->insert_record('groupings', $grouping);
    }
    
    /**
     * Gets the params from the object and passes it bak as a new object. 
     * @return \stdClass
     */
    private function get_params()
    {
        $params = new stdClass();
        $params->courseid = $this->courseid;
        $params->name = $this->name;
        return $params;
    }
    
    /**
     * Gets the params from the object passed in and puts them onto 
     * the Grouping objectl. 
     * @param type $params
     */
    private function extract_params($params)
    {        
        $this->courseid = $params->courseid;
        $this->name = $params->name;
    }
    
    /**
     * gets the grouping from the database and loads onto the obj
     * @global type $DB
     * @param type $id
     */
    private function load_grouping($id)
    {
        global $DB;
        $sql = "SELECT * FROM {groupingss} WHERE id = ?";
        $record = $DB->get_record_sql($sql, array($id));
        if($record)
        {
            $this->extract_params($record);
        }
    }
}

?>
