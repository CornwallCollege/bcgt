<?php
// Do we want to use the enrol & unenrol events to automatically link users to quals if they enrol/unenrol on a course with a qual?
$settings->add(
        new admin_setting_configcheckbox('bcgt/autoenrolusers', get_string('autoenrolusers', 'block_bcgt'), get_string('autoenrolusersdesc', 'block_bcgt'), '1')
);

$settings->add(
        new admin_setting_configcheckbox('bcgt/autounenrolusers', get_string('autounenrolusers', 'block_bcgt'), get_string('autounenrolusersdesc', 'block_bcgt'), '0')
);

$settings->add(
        new admin_setting_configcheckbox('bcgt/autocalculateasptargetgrade', get_string('labelautocalculateasptargetgrade', 'block_bcgt'), get_string('descautocalculateasptargetgrade', 'block_bcgt'), '0')
);

$settings->add(
        new admin_setting_configtext('bcgt/autocalcaspvalue', get_string('labelautocalcaspvalue', 'block_bcgt'), get_string('descautocalcaspvalue', 'block_bcgt'), '1')
);

$settings->add(
        new admin_setting_configcheckbox('bcgt/showtargetgrades', get_string('labelshowtargetgrades', 'block_bcgt'), get_string('descshowtargetgrades', 'block_bcgt'), '1')
);

$settings->add(
        new admin_setting_configcheckbox('bcgt/showaspgrades', get_string('labelshowaspgrades', 'block_bcgt'), get_string('descshowaspgrades', 'block_bcgt'), '0')
);

//Theming
$settings->add(new admin_setting_configcheckbox(
        'bcgt/themejquery',
        get_string('labelthemejquery', 'block_bcgt'),
        get_string('descthemejquery', 'block_bcgt'),
        '0'
        ));

$settings->add(new admin_setting_configtext(
        'bcgt/themejqueryloc',
        get_string('labelthemejqueryloc', 'block_bcgt'),
        get_string('descthemejqueryloc', 'block_bcgt'),
        ''
        ));

//grids and orders
$settings->add(new admin_setting_configtext(
        'bcgt/pagingnumber',
        get_string('labelpagingnumber', 'block_bcgt'),
        get_string('descpagingnumber', 'block_bcgt'),
        '20'
        ));

//ALEVELS
$settings->add(new admin_setting_configcheckbox(
        'bcgt/usefa',
        get_string('labelalevelusefa', 'block_bcgt'),
        get_string('descalevelusefa', 'block_bcgt'),
        '0'
        ));

$settings->add(new admin_setting_configcheckbox(
        'bcgt/alevelLinkAlevelGradeBook',
        get_string('labelalavelLinkAlevelGradeBook', 'block_bcgt'),
        get_string('descalavelLinkAlevelGradeBook', 'block_bcgt'),
        '0'
        ));

$settings->add(new admin_setting_configcheckbox(
    'bcgt/alevelgradebookscaleonly',
    get_string('labelalevelgradebookscaleonly', 'block_bcgt'),
    get_string('descalevelgradebookscaleonly', 'block_bcgt'),
    '1'
    ));

$settings->add(new admin_setting_configcheckbox(
        'bcgt/aleveluseceta',
        get_string('labelaleveluseceta', 'block_bcgt'),
        get_string('descaleveluseceta', 'block_bcgt'),
        '0'
        ));

//$settings->add(new admin_setting_configcheckbox(
//        'bcgt/alevelusecalcpredicted',
//        get_string('labelalevelusecalcpredicted', 'block_bcgt'),
//        get_string('descalevelusecalcpredicted', 'block_bcgt'),
//        '0'
//        ));

//$settings->add(new admin_setting_configcheckbox(
//        'bcgt/alevelproggradefa',
//        get_string('labelalevelpgfa', 'block_bcgt'),
//        get_string('descalevelpgfa', 'block_bcgt'),
//        '0'
//        ));

//$settings->add(new admin_setting_configcheckbox(
//        'bcgt/alevelproggradehw',
//        get_string('labelalevelpggb', 'block_bcgt'),
//        get_string('descalevelpggb', 'block_bcgt'),
//        '0'
//        ));

$settings->add(new admin_setting_configcheckbox(
        'bcgt/allowalpsweighting',
        get_string('labelallowalpsweighting', 'block_bcgt'),
        get_string('descallowalpsweighting', 'block_bcgt'),
        '0'
        ));

$settings->add(new admin_setting_configcheckbox(
        'bcgt/weightedtargetgradesuseconstant',
        get_string('labelweightedtargetuseconstant', 'block_bcgt'),
        get_string('descweightedtargetuseconstant', 'block_bcgt'),
        '0'
        ));

$settings->add(new admin_setting_configselect(
            'bcgt/weightedtargetgradesclosestgrade',
            get_string('labelweightedtargetgradesclosestgrade', 'block_bcgt'),
            get_string('descweightedtargetgradesclosestgrade', 'block_bcgt'),
            'DOWN',
            array(
                'UP'  => get_string('up', 'block_bcgt'),
                'DOWN' => get_string('down', 'block_bcgt'),
            )
        ));


$settings->add(new admin_setting_configtext(
        'bcgt/alpsweightedfamilies',
        get_string('labelalpsweightedfamilies', 'block_bcgt'),
        get_string('descalpsweightedfamilies', 'block_bcgt'),
        'ALevel,BTEC'
        ));

$settings->add(new admin_setting_configtext(
        'bcgt/alpsweightedfamiliestargets',
        get_string('labelalpsweightedfamiliestargets', 'block_bcgt'),
        get_string('descalpsweightedfamiliestargets', 'block_bcgt'),
        'ALevel,BTEC'
        ));




$settings->add(new admin_setting_configtext(
        'bcgt/weightedtargetmethod',
        get_string('labelweightedtargetmethod', 'block_bcgt'),
        get_string('descweightedtargetmethod', 'block_bcgt'),
        '2'
        ));

$settings->add(new admin_setting_configtext(
        'bcgt/defaultalpsperc',
        get_string('labeldefaultalpspercentage', 'block_bcgt'),
        get_string('descdefaultalpspercentage', 'block_bcgt'),
        '75'
        ));

$settings->add(new admin_setting_configcheckbox(
        'bcgt/calcultealpstempreports',
        get_string('labelcalcultealpstempreports', 'block_bcgt'),
        get_string('desccalcultealpstempreports', 'block_bcgt'),
        '0'
        ));

//BTECS
$settings->add(new admin_setting_configtext(
        'bcgt/btecunitspredgrade',
        get_string('labelbtecunitspredgrade', 'block_bcgt'),
        get_string('descbtecunitspredgrade', 'block_bcgt'),
        '3'
        ));

$settings->add(new admin_setting_configtext(
        'bcgt/btecgridcolumns',
        get_string('labelbtecgridcolumns', 'block_bcgt'),
        get_string('descbtecgridcolumns', 'block_bcgt'),
        'picture,username,name'
        ));

$settings->add(new admin_setting_configtext(
        'bcgt/bteclockedcolumnswidth',
        get_string('labelbteclockedcolumnswidth', 'block_bcgt'),
        get_string('descbteclockedcolumnswidth', 'block_bcgt'),
        '430'
        ));   

$settings->add(new admin_setting_configtext(
        'bcgt/bteclockedcolumnswidthclass',
        get_string('labelbteclockedcolumnswidthclass', 'block_bcgt'),
        get_string('descbteclockedcolumnswidthclass', 'block_bcgt'),
        '250'
        ));



$settings->add(new admin_setting_configtext(
        'bcgt/logoimgurl',
        get_string('logoimgurl', 'block_bcgt'),
        get_string('desclogoimgurl', 'block_bcgt'),
        $CFG->wwwroot . '/blocks/bcgt/pix/bc.png'
        ));  

$settings->add(new admin_setting_configcheckbox(
        'bcgt/showcoursecategories',
        get_string('labelshowcoursecategories', 'block_bcgt'),
        get_string('descshowcoursecategories', 'block_bcgt'),
        '0'
        ));

$settings->add(new admin_setting_configcheckbox(
        'bcgt/usegroupsingradetracker',
        get_string('labelusegroupsingradetracker', 'block_bcgt'),
        get_string('descusegroupsingradetracker', 'block_bcgt'),
        '0'
        ));

$settings->add(new admin_setting_configtext(
        'bcgt/fullcoursegroupname',
        get_string('labelfullcoursegroupname', 'block_bcgt'),
        get_string('descfullcoursegroupname', 'block_bcgt'),
        'idnumber'
        )); 

$settings->add(new admin_setting_configtext(
        'bcgt/metacoursegroupnames',
        get_string('labelmetacoursegroupnames', 'block_bcgt'),
        get_string('descmetacoursegroupnames', 'block_bcgt'),
        '[idnumber]{_}[groupname]'
        ));

$settings->add(new admin_setting_configcheckbox(
        'bcgt/usecrongroupsync',
        get_string('labelusecrongroupsync', 'block_bcgt'),
        get_string('descusecrongroupsync', 'block_bcgt'),
        '0'
        ));


$settings->add(new admin_setting_configcheckbox(
'bcgt/useassignmentbtecautoupdate',
        get_string('labelassignmentbtecautoupdate', 'block_bcgt'),
        get_string('descassignmentbtecautoupdate', 'block_bcgt'),
        '0'
        ));
//$settings->add(new admin_setting_configcheckbox(
//        'bcgt/assignmentcheckajax',
//        get_string('labelassignmentcheckajax', 'block_bcgt'),
//        get_string('descassignmentcheckajax', 'block_bcgt'),
//        '1'
//        ));

$settings->add(new admin_setting_configselect(
            'bcgt/assignmentfrequencycheck',
            get_string('labelassignmentfrequencycheck', 'block_bcgt'),
            get_string('labelassignmentfrequencycheck', 'block_bcgt'),
            'daily',
            array(
                'daily'  => get_string('assignmentfrequencycheckdaily', 'block_bcgt'),
                'hourly' => get_string('assignmentfrequencycheckhourly', 'block_bcgt'),
                'never' => get_string('assignmentfrequencychecknever', 'block_bcgt'),
            )
        )); 

$settings->add(new admin_setting_configselect(
            'bcgt/assignmenttimecheck',
            get_string('labelassignmenttimecheck', 'block_bcgt'),
            get_string('descassignmenttimecheck', 'block_bcgt'),
            '24',
            array(
                '1'  => "1 am",'2' => "2 am",'3' => " 3 am",'4' => " 4 am",'5' => " 5 am",
                '6' => " 6 am",'7' => " 7 am",'8' => " 8 am",'9' => " 9 am",'10' => " 10 am",
                '11' => " 11 am",'12' => " 12 pm",'13' => " 1 pm",'14' => " 2 pm",'15' => " 3 pm",
                '16' => " 4 pm",'17' => " 5 pm",'18' => " 6 pm",'19' => " 7 pm",'20' => " 8 pm",
                '21' => " 9 pm",'22' => " 10 pm",'23' => " 11 pm",'24' => " 12 am",
            )
        ));

$settings->add(new admin_setting_configtext(
        'bcgt/modscheckedcronupdate',
        get_string('labelmodscheckedcronupdate', 'block_bcgt'),
        get_string('descmodscheckedcronupdate', 'block_bcgt'),
        'assign,quiz'
        )); 

$settings->add(new admin_setting_configcheckbox(
        'bcgt/modstrackercheckcoursevisible',
        get_string('labelmodstrackercheckcoursevisible', 'block_bcgt'),
        get_string('descmodstrackercheckcoursevisible', 'block_bcgt'),
        '1'
        )); 



//$settings->add(new admin_setting_configcheckbox(
//        'bcgt/enrolstudentqual',
//        get_string('labelenrolstudentqual', 'block_bcgt'),
//        get_string('descenrolstudentqual', 'block_bcgt'),
//        '0'
//        ));
//
//$settings->add(new admin_setting_configcheckbox(
//        'bcgt/unenrolstudentqual',
//        get_string('labelunenrolstudentqual', 'block_bcgt'),
//        get_string('descunenrolstudentqual', 'block_bcgt'),
//        '0'
//        ));
//
//$settings->add(new admin_setting_configcheckbox(
//        'bcgt/enroldeaultallunits',
//        get_string('labelenroldeaultallunits', 'block_bcgt'),
//        get_string('descenroldeaultallunits', 'block_bcgt'),
//        '0'
//        ));

    
// Tutor role short name
$settings->add(new admin_setting_configtext(
        'bcgt/tutorrole',
        get_string('tutorroleshortname', 'block_bcgt'),
        get_string('tutorroleshortname:desc', 'block_bcgt'),
        ''
));


$cats = get_categories();
$catArray = array();
foreach($cats as $cat)
{
    $catArray[$cat->id] = $cat->name;
}

// Choose which course categories to use in Reporting
$settings->add( new admin_setting_configmultiselect(
        'bcgt/reportingcats', 
        get_string('reportingcats', 'block_bcgt'), 
        get_string('reportingcats:desc', 'block_bcgt'), 
        null, 
        $catArray) );


$settings->add(new admin_setting_configcheckbox(
        'bcgt/reportingftptfilter',
        get_string('reportingftptfilter', 'block_bcgt'),
        get_string('reportingftptfilterdesc', 'block_bcgt'),
        '0'
        )); 

$settings->add(new admin_setting_configcheckbox(
        'bcgt/showucaspoints',
        get_string('showucaspoints', 'block_bcgt'),
        get_string('showucaspointsdesc', 'block_bcgt'),
        '0'
        )); 


$settings->add(new admin_setting_configcheckbox(
        'bcgt/usemassupdate',
        get_string('usemassupdate', 'block_bcgt'),
        get_string('usemassupdatedesc', 'block_bcgt'),
        '0'
        )); 


// Page layout
$settings->add(new admin_setting_configtext(
        'bcgt/pagelayout',
        get_string('pagelayout', 'block_bcgt'),
        get_string('pagelayout:desc', 'block_bcgt'),
        'login'
));

$settings->add(new admin_setting_configselect(
            'bcgt/alpscrontime',
            get_string('labelalpscrontime', 'block_bcgt'),
            get_string('descalpscrontime', 'block_bcgt'),
            '24',
            array(
                '1'  => "1 am",'2' => "2 am",'3' => " 3 am",'4' => " 4 am",'5' => " 5 am",
                '6' => " 6 am",'7' => " 7 am",'8' => " 8 am",'9' => " 9 am",'10' => " 10 am",
                '11' => " 11 am",'12' => " 12 pm",'13' => " 1 pm",'14' => " 2 pm",'15' => " 3 pm",
                '16' => " 4 pm",'17' => " 5 pm",'18' => " 6 pm",'19' => " 7 pm",'20' => " 8 pm",
                '21' => " 9 pm",'22' => " 10 pm",'23' => " 11 pm",'24' => " 12 am",
            )
        ));

$settings->add(new admin_setting_configcheckbox(
        'bcgt/showarchivegriddata',
        get_string('labelshowarchivegriddata', 'block_bcgt'),
        get_string('descshowarchivegriddata', 'block_bcgt'),
        '0'
        )); 
$settings->add(new admin_setting_configcheckbox(
        'bcgt/useregistergroups',
        get_string('useregistergroups', 'block_bcgt'),
        get_string('useregistergroupsdesc', 'block_bcgt'),
        '1'
        )); 

$settings->add(new admin_setting_configtext(
        'bcgt/alvlvalenience',
        get_string('alvlvalenience', 'block_bcgt'),
        get_string('alvlvalenience:desc', 'block_bcgt'),
        '0'
));

