<?php
require_once('../../../config.php');
require_once('../lib.php');
global $CFG;

$action = optional_param('action', '', PARAM_TEXT);
switch($action)
{
    case "userdatatemplate";
        $userDateImport = new UserDataImport();
        $file_url = $userDateImport->get_template();
        break;
    default:
        break;
}
header('Content-Type: application/vnd.ms-excel');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
readfile($file_url); // do the double-download-dance (dirty but worky)

?>