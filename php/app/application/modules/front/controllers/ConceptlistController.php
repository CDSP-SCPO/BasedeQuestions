<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class ConceptlistController extends BDQ_Locale_FrontController
{
	
    public function init()
    {
    	parent::init();
	}
	
	public function viewAction()
	{
    	$id = $this->_request->getBDQParam('id');
	   	$conceptListMapper = new DB_Mapper_ConceptList;

    	if ( ! ($this->view->conceptList = $conceptListMapper->findWithDetails($id, $this->_translationLanguageGuiCurrent->get_id())))
    	{
			throw new Zend_Controller_Action_Exception($this->_translate->_('fr0030000000'), 404);
    	}

    	$this->_router->setGlobalParam('searchLang', $this->_request->getBDQParam('searchLang'));
    	$search = new Solr_BDQ_Search_ConceptFacet(
    		"conceptListId:$id solrLangCode:" . $this->_request->getBDQParam('searchLang'),
    		$this->_translationLanguageGuiCurrent->get_id()
    	);
    	$this->view->concepts = $search->getConceptFacets();
	}

}