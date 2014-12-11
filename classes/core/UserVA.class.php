<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserVA
 *
 * @author mchaney
 */
class UserVA {
    //put your code here
    
    public function UserVA (){
        
    }
    
    public function calculate_VA()
    {
        
    }
    
    public function get_diff($targetRanking, $awardRanking)
    {
        return $this->calculate_difference($awardRanking, $targetRanking);
    }
    
    public function ahead_behind_on($targetRanking, $awardRanking)
    {
        $onTarget = 'Unknown';
        if($targetRanking && $awardRanking)
        {
            $diff = $this->calculate_difference($awardRanking, $targetRanking);
            //so we have the differance
            if($diff > 0)
            {
                $onTarget = 'Ahead';
            }
            elseif($diff < 0)
            {
                $onTarget = 'Behind';
            }
            elseif($diff == 0)
            {
                $onTarget = 'OnTarget';
            }
            else
            {
                $onTarget = 'Unknown';
            }
        }
        return $onTarget;
    }
    
    private function calculate_difference($awardRanking, $targetRanking)
    {
        if($awardRanking && $targetRanking)
        {
            return ($awardRanking - $targetRanking);
        }
        return null;
    }
}

?>
