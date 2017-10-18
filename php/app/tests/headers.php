<?php
define('APPLICATION_PATH', realpath(dirname(__FILE__) .'/../application'));
define('DDI_FILES_DIR', realpath(dirname(__FILE__) . '/fixtures/ddi'));
set_include_path(
	implode(
		PATH_SEPARATOR, 
		array(
			realpath(APPLICATION_PATH . '/../library'),
			'/build/buildd/php5-5.2.10.dfsg.1/pear-build-download/PHPUnit-3.4.11',
			get_include_path(),
		)
	)
);
require_once APPLICATION_PATH . '/inc/constants.php';
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
#
$application->bootstrap();
require_once APPLICATION_PATH . '/inc/functions.php';
