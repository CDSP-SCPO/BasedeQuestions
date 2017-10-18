<?php

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected function _initConstants()
	{
		require_once APPLICATION_PATH . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'constants.php';
	}

  	protected function _initAutoload()
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->registerNamespace(array
			(
			'BDQ',
			'DB',
			'DDI',
			'Form',
			'PHPExcel',
			'Solr',
			)
		);
    }

	protected function _initConfigs()
    {
    	Solr_Client::$iniFile = APPLICATION_PATH . DIRECTORY_SEPARATOR .'configs' . DIRECTORY_SEPARATOR . 'solr.ini';
    	Solr_Client::$logFile = realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs') . DIRECTORY_SEPARATOR . 'Solr_Client.log';
    	BDQ_Settings_Client::$defaultsIniFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'client-settings.ini';
    }
    
    protected function _initRequest()
    {
    	$frontController = Zend_Controller_Front::getInstance();
    	$frontController->setRequest('BDQ_Request');
    }

    protected function _initRoutes()
    {
    	$frontController = Zend_Controller_Front::getInstance();
		$router = $frontController->getRouter();
		$config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'routes.ini', 'production');
		$router->addConfig($config, 'routes');
    }

	protected function _initDoctype()
    {
		$this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_TRANSITIONAL');
    }

    protected function _initCss()
    {

    	if (APPLICATION_ENV == 'development')
    	{
    		$files = glob(realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . '*.less');
    		$l = count($files);
    		$exec = LESSC_PATH;

    		for ($i = 0; $i < $l; $i++)
    		{
    			$file = $files[$i];
    			$_file = str_replace('.less', '.css', $file);

    			if ( ! file_exists($_file) || filemtime($_file) < filemtime($file)) // Recompile the CSS file if needed
    			{	
    				`$exec $file`;
    			}

    		}

    	}

    }
    
    protected function _initJs()
    {
    	
	    if (APPLICATION_ENV == 'development')
    	{
    		$files = glob(realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . '*.js');
    		$l = count($files);
    		$exec = YUI_COMPRESSOR_PATH;
    		
    		for ($i = 0; $i < $l; $i++)
    		{
    			$file = $files[$i];
    			
    			if (strpos($file, '.min.js') !== false)
    			{
    				continue;
    			}
    			
    			$_file = str_replace('.js', '.min.js', $file);
    			
    			if ( ! file_exists($_file) || filemtime($_file) < filemtime($file)) // Recompile the JS file if needed
    			{	
    				`java -jar $exec $file -o $_file --charset utf-8`;
    			}

    		}

    	}

	}

}