<?php
/**
 * @author Xavier Schepler
 */
require_once '../library/BDQ/Settings/Client.php';
require_once '../library/Solr/Client.php';
require_once '../library/Solr/Request.php';
require_once '../library/Solr/Select.php';
require_once '../library/Solr/Response.php';
require_once '../library/Solr/BDQ/Search/Autocomplete.php';

Solr_Client::$iniFile = '../application/configs/solr.ini';
Solr_Client::$logFile = '/logs/Solr_Client.log';
BDQ_Settings_Client::$defaultsIniFile = '../application/configs/client-settings.ini';

$ac = new Solr_BDQ_Search_Autocomplete(
	$_GET['terms'],
    $_GET['searchLang'],
    $_GET['searchQuestion'],
    $_GET['searchModalities'],
    $_GET['searchVariableLabel']
);
echo json_encode($ac->getSuggestions());