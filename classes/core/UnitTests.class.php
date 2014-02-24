<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UnitTests
 *
 * @author mchaney
 */
class UnitTests {
    //put your code here
    
    protected $test;
    
    public function UnitTests($view)
    {
        switch($view)
        {
            case 'tg':
                $this->test = new UserCourseTarget();
                break;
            default:
                return "";
                break;
        }
    }
    
    private function get_tests()
    {
        return array('targetgrades');
    }
    
    //returns a ul. li list of unit tests that can be run.
    public function get_unit_tests_list()
    {
        global $CFG;
        $courseID = optional_param('cID', 1, PARAM_INT);
        $retval = '';
        $retval .= '<table>';
        foreach($this->get_tests() AS $test)
        {
            $retval .= '<tr><td><a href="'.$CFG->wwwroot.'/blocks/bcgt/forms/tests.php?cID='.$courseID.'&view=tg">'.
                    get_string($test, 'block_bcgt').'</a></td>'.
                    '<td>'.get_string($test.'testdesc', 'block_bcgt').'</td></tr>';
        }
        $retval .= '</table>';
        return $retval;
    }
        
    public function get_unit_test($view)
    {
        switch($view)
        {
            case 'tg':
                return $this->test->get_test_page();
                break;
            default:
                return "";
                break;
        }
    }
    
    public function process_test($view)
    {
        switch($view)
        {
            case 'tg':
                return $this->test->process_test();
                break;
            default:
                return "";
                break;
        }
    }
    
    public function get_unit_test_result($view)
    {
        switch($view)
        {
            case 'tg':
                return $this->test->get_test_page_results();
                break;
            default:
                return "";
                break;
        }
    }
}

?>
