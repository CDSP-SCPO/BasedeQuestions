<?php
define('APPLICATION_PATH', realpath(dirname(__FILE__) .'/../../application'));
require_once APPLICATION_PATH . '/inc/constants.php';
set_include_path(
	implode(
		PATH_SEPARATOR, 
		array(
			realpath(APPLICATION_PATH . '/../library'),
			get_include_path(),
		)
	)
);
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace(array
	(
	'DB',
	'DDI_',
	'Solr_',
	'SolrBDQSearch_',
	)
);
$application = new Zend_Application(
    'production',
    APPLICATION_PATH . '/configs/application.ini'
);
$bootstrap = $application->getBootstrap();
$bootstrap->bootstrap('db');
$dbAdapter = $bootstrap->getResource('db');
ini_set('display_errors','On'); 