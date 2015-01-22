<?php

//Each time the upgrade process is to be run this should be the date of the day when the 
//code and database changes are made!!!!!!
    $plugin->version = 2015010800;  // YYYYMMDDHH (year, month, day, 24-hr time)
    $plugin->requires = 2012120300; // Moodle 2.4
    //$plugin->cron = 60; - This would be every 60 seconds..................
    $plugin->cron = 3600;
