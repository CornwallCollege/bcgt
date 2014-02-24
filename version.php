<?php

//Each time the upgrade process is to be run this should be the date of the day when the 
//code and database changes are made!!!!!!

    $plugin->version = 2014022100;  // YYYYMMDDHH (year, month, day, 24-hr time)
    $plugin->requires = 2010112400; // YYYYMMDDHH (This is the release version for Moodle 2.0)
    $plugin->cron = 60;
    //$plugin->cron = 3600;
