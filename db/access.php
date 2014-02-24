<?php

/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

$capabilities = array(
 
    'block/bcgt:myaddinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        ),
 
        'clonepermissionsfrom' => 'moodle/my:manageblocks'
    ),
 
    'block/bcgt:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
 
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
 
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
    
    'block/bcgt:addasteacherongrids' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:addasstudentongrids' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:viewdashboard' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
            
        ),
    ),
        
    'block/bcgt:viewclassgrids' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW
        ),
    ),
    
    'block/bcgt:editclassgrids' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW
        ),
    ),

    'block/bcgt:viewcoursestab' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT    
        ),
    ),
    
    'block/bcgt:viewstudentstab' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT 
        ),
    ),
        
    'block/bcgt:viewteamtab' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT 
        ),
    ),
    
    'block/bcgt:viewunitstab' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT 
        ),
    ),
    
    'block/bcgt:viewreportsstab' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT 
        ),
    ),
    
    'block/bcgt:viewassignmentstab' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT 
        ),
    ),
    
    'block/bcgt:viewadmintab' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT 
        ),
    ),
    
    'block/bcgt:viewhelptab' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT 
        ),
    ),
    
    'block/bcgt:viewfeedbacktab' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT 
        ),
    ),
    
    'block/bcgt:viewmessagestab' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT 
        ),
    ),
   
    'block/bcgt:addnewqual' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT 
        ),
    ),
    
    'block/bcgt:editqual' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:addnewunit' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:editunit' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:searchunits' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:searchquals' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW, 
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:editqualunit' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW, 
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:editqualscourse' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:editteacherqual' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:editstudentqual' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW, 
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:editstudentunits' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW, 
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:editmentorsmentees' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:editmanagersteam' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:editmyownquals' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:editmyownmentees' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:editmyownteam' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:importdata' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:exportdata' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:editstudentgrid' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW, 
        ),
    ),
    
    'block/bcgt:editunitgrid' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW, 
        ),
    ),
    
    'block/bcgt:viewallgrids' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ),
    ),
    
    'block/bcgt:viewowngrid' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        ),
    ),
    
    'block/bcgt:editqualfamilysettings' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT
        ),
    ),
    
    'block/bcgt:viewbteclatetracking' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW
        ),
    ),
  
    'block/bcgt:addqualtocurentcourse' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW
        ), 
    ),
    
    'block/bcgt:viewbtecmaxgrade' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_ALLOW
        ), 
    ),
    
    'block/bcgt:viewbtecavggrade' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_ALLOW
        ), 
    ),
    
    'block/bcgt:viewbtecmingrade' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_ALLOW
        ), 
    ),
    
    'block/bcgt:viewbtectargetgrade' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_ALLOW
        ), 
    ),
    
    'block/bcgt:viewtargetgrade' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:viewaspgrade' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:viewweightedtargetgrade' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_ALLOW
        ), 
    ),
    
    'block/bcgt:viewbothweightandnormaltargetgrade' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:editstafftrackerlinks' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:editredundanttrackeruserlinks' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:manageactivitylinks' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW
        ), 
    ),
    
    'block/bcgt:viewactivitylinks' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_ALLOW
        ), 
    ),
    
    'block/bcgt:addqualgradingstructure' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ), 
    ),
    
    'block/bcgt:addunitgradingstructure' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ), 
    ),
    
    'block/bcgt:addcriteriagradingstructure' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ), 
    ),
    
    'block/bcgt:deleteunit' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ), 
    ),
    
    'block/bcgt:deletequalification' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ), 
    ),
    
    'block/bcgt:mergeunit' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ), 
    ),
    
    'block/bcgt:mergequalification' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ), 
    ),
    
    'block/bcgt:transferstudentsunits' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_PREVENT
        ), 
    ),
    
    'block/bcgt:printstudentgrid' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'student' => CAP_ALLOW
        ), 
    ),
    
    'block/bcgt:downloadstudentgrid' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'student' => CAP_ALLOW
        ), 
    ),
    
    'block/bcgt:printunitgrid' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'student' => CAP_PROHIBIT
        ), 
    ),
    
    'block/bcgt:downloadunitgrid' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'student' => CAP_PROHIBIT
        ), 
    ),
    
    'block/bcgt:edittargetgradesettings' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
   'block/bcgt:editpriorqualsettings' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:importpriorlearning' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:importtargetgrades' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:importqualweightings' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:importassess' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:importquals' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:calculateaveragegcsescore' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:calculatetargetgrades' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:calculatepredictedgrades' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:viewvalueaddedgrids' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:edittargetgrade' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        ), 
    ),
    
    'block/bcgt:editasptargetgrade' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array( 
        ), 
    ),
    
    'block/bcgt:viewajaxrequestdata' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array( 
        ), 
    ),
    
    'block/bcgt:rundatacleanse' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array( 
        ), 
    ),
    
    'block/bcgt:checkuseraccess' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array( 
        ), 
    ),
    
    'block/bcgt:manageusersgroups' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array( 
            
        ), 
    ),
    
    'block/bcgt:managemodlinking' => array(
        'captype' => 'edit',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array( 
            
        ), 
    ),
    
    
);