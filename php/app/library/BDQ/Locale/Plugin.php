<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class BDQ_Locale_Plugin extends Zend_Controller_Plugin_Abstract
{

	/**
	 * @var string
	 */
	protected $_defaultLocale;
	
	/**
	 * @var string
	 */
	protected $_lang;
	
	/**
	 * @param Zend_Controller_Request_Abstract
	 */
	public function routeStartup(Zend_Controller_Request_Abstract $request)
	{
		$this->_initTranslationLanguages();
		$uri = $request->getRequestUri();
		$uri = explode('/', $uri);
		$tls = Zend_Registry::get('translationLanguagesGui');
		$l = count($tls);
		$tlCodes = array();
		
		for ($i = 0; $i < $l; $i++)
		{
			$tlCodes[] = $tls[$i]->get_code();
		}

		$this->_lang = ( ! isset($uri[1]) || empty($uri[1]) || ! in_array($uri[1], $tlCodes)) ? DEFAULT_GUI_LANGUAGE : $uri[1];
		Zend_Registry::set('translationLanguageGui', $this->_lang);
		$this->_defaultLocale = $this->_lang;
		$translate = new Zend_Translate(
			'tmx',
			realpath($locale = APPLICATION_PATH . '/../locale/routes.xml'), 
			$this->_lang
		);
		Zend_Controller_Router_Route::setDefaultTranslator($translate);
		Zend_Controller_Router_Route::setDefaultLocale($this->_lang);
	}

	/**
	 * @param Zend_Controller_Request_Abstract
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		
		$this->_initErrorTranslation();

		if ($request->getBDQParam('module') == 'admin')
		// Admin translation loading
		{
			$this->_initBackEndTranslation();
			$this->_initZendFormTranslation(); // Zend_Form is only used in back end
			$translateAdmin = Zend_Registry::get('translateAdmin');
			$translateAdmin->setLocale($this->_defaultLocale);
		}

		else
		// Front end translation loading
		{
			$this->_initFrontEndTranslation();
			$translateFront = Zend_Registry::get('translateFront');
			$translateFront->setLocale($this->_defaultLocale);
		}

		$translationLanguages = Zend_Registry::get('translationLanguagesGui');
		$l = count($translationLanguages);

		for ($i = 0; $i < $l; $i++)
		{

			if ($translationLanguages[$i]->get_code() == $this->_defaultLocale)
			{
				Zend_Registry::set('translationLanguageGuiCurrent', $translationLanguages[$i]);
				break;
			}

		}

	}
	
	protected function _initTranslationLanguages()
    {
    	$mapper = new DB_Mapper_TranslationLanguage;
    	$tl = $mapper->findAll();
    	$l = count($tl);
    	// Languages used for GUI translations (in XML TMX locale files and database tables)
    	$enabledGui = array();
    	// Languages used for full text search with solr (from DDI files)
    	$enabledSolr = array();
    	
    	for ($i = 0; $i < $l; $i++)
    	{
    		
    		if ($tl[$i]->get_enabled_gui())
    		{
    			$enabledGui[] = $tl[$i];
    		}
    		
    		if ($tl[$i]->get_enabled_solr())
    		{
    			$enabledSolr[] = $tl[$i];
    		}
    		
    	}
    	
    	Zend_Registry::set('translationLanguagesGui', $enabledGui);
    	Zend_Registry::set('translationLanguagesSolr', $enabledSolr);
    }
	
	protected function _initBackEndTranslation()
    {
    	require APPLICATION_PATH . "/../locale/admin.xml.fr.php";
    	$translator = new Zend_Translate(
    		'array',
    		$translate,
    		'fr'
    	);
    	$tl = Zend_Registry::get('translationLanguagesGui');
    	$l = count($tl);
    	
    	Zend_Registry::set('translateAdmin', $translator);
    }
    
	protected function _initZendFormTranslation()
   	{
		$translator = new Demo_Translate(
			'array',
			realpath(APPLICATION_PATH . '/../locale/Zend_ValidateFr.php'), 
			'fr'
		);
     	Zend_Validate_Abstract::setDefaultTranslator($translator);
   	}
    
    protected function _initFrontEndTranslation()
   	{
   		$translate = APPLICATION_PATH . "/../locale/front.xml.$this->_lang.php";
   		$translate = realpath($translate);
 
   		if ( ! $translate)
   		{
   			$translate = APPLICATION_PATH . "/../locale/front.xml.fr.php";
   			$translate = realpath($translate);
   		}
 		require $translate; // translate is now an array
    	$translator = new Zend_Translate(
    		'array', 
    		$translate,
    		$this->_lang
    	);
    	
    	Zend_Registry::set('translateFront', $translator);
   	}
   	
   	protected function _initErrorTranslation()
   	{
   		$translator = new Zend_Translate(
    		'tmx', 
    		realpath($locale = APPLICATION_PATH . '/../locale/error.xml'), 
    		'fr'
    	);		
	 	$tl = Zend_Registry::get('translationLanguagesGui');
    	$l = count($tl);
    	
    	for ($i = 0; $i < $l; $i++)
    	{
	    	$translator->addTranslation(
	    		$locale, 
	    		$tl[$i]->get_code()
	    	);
    	}

    	Zend_Registry::set('translateError', $translator);
   	}
   	
}

class Demo_Translate extends Zend_Translate{

	public function _($stringId)
	{
		$trad = parent::_($stringId);
		$pattern = '/^[0-9]{9} - /';
		$replace = '';
		return preg_replace($pattern, $replace, $trad);
	}

}