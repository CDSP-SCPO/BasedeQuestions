<?php
/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 * @package Locale
 */
class BDQ_Locale_FrontController extends Zend_Controller_Action
{

	/**
	 * @var array 
	 */
	protected $_solrDocumentLanguages;
	
	/**
	 * @var array
	 */
	protected $_translationLanguageGuiCurrent;
	
	/**
	 * @var Zend_Controller_Request_Abstract
	 */
	protected $_request;
	
	/**
	 * @var Zend_Controller_Router_Abstract
	 */
	protected $_router;
	
	/**
	 * @return void
	 */
    public function init()
    {
        $this->_request = $this->getRequest();
        $this->_router = $this->getFrontController()->getRouter();
		$this->view->translationLanguageGuiCurrent = $this->_translationLanguageGuiCurrent = Zend_Registry::get('translationLanguageGuiCurrent');
		$this->_router->setGlobalParam('lang', $this->_translationLanguageGuiCurrent->get_code());
		$this->view->clientSettings = $this->_clientSettings = BDQ_Settings_Client::getInstance();
		$mapper = new DB_Mapper_TranslationLanguage;
		$this->view->solrDocumentLanguages = $this->_solrDocumentLanguages = $mapper->findAllLinkedToAtLeastOneDdifile($this->_translationLanguageGuiCurrent->get_id());
		$this->view->translationLanguages = $mapper->findAllSameTranslationLabelLanguage();
		$this->view->searchResultsUrl = Zend_Controller_Front::getInstance()->getPlugin('BDQ_HistoryPlugin')->getLastSearchResultsUrl(
    		$this->_translationLanguageGuiCurrent->get_code()
    	);
		
		if ($this->_request->getBDQParam('module') == 'default')
		{
			$this->view->translate = $this->_translate = Zend_Registry::get('translateFront');
		}

		else
		{
			$this->view->translate = $this->_translate = Zend_Registry::get('translateAdmin');
		}
		
		if (MAINTENANCE_MODE)
		{
			
			if ( $this->_router->getCurrentRouteName() != 'maintenanceView')
			{
				$ip = $this->_request->getClientIp();
				
				if ($ip != MAINTENANCE_ALLOW_IP)
				{
					$this->_helper->getHelper('Redirector')->setGotoRoute(	
						array(),
						'maintenanceView'
					);
					$this->_helper->getHelper('Redirector')->redirectAndExit();
				}

			}

		}
    }
 
}