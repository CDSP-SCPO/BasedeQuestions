<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DomainController extends BDQ_Locale_FrontController
{
	
    public function init()
    {
    	parent::init();
    }

    public function viewAction()
    {

    	$id = $this->_request->getBDQParam('id');
    	$mapper = new DB_Mapper_Domain;
    	
    	if ( ! $this->view->domain = $mapper->findWithDetails($id, $this->_translationLanguageGuiCurrent->get_id()))
    	{
    		throw new Zend_Controller_Action_Exception($this->_translate->_('fr0015000000'), 404);
    	}
    	
    	$tls = Zend_Registry::get('translationLanguagesSolr');

    	$mapper = new DB_Mapper_StudyDescription;
    	$studies = array();

    	foreach ($tls as $tl)
    	{
    		$titles = $mapper->findStudyTitlesForDomain($id, $tl->get_id(), $this->_translationLanguageGuiCurrent->get_id());
    		
    		if (count($titles) > 0)
    		{
    			$studies[$tl->get_code_solr()] = $titles;
    			$wc = new Solr_BDQ_Search_WordCloud(
    				'solrLangCode:' . $tl->get_code_solr() . ' domainId:' . $id,
    				$tl->get_code_solr()
    			);
    			$wc->tfLowerBound = 0.43;
    			$cloudWords[$tl->get_code_solr()] = $wc->getWords();
    		}
    		
    	}
    	
    	$this->view->cloudWords = $cloudWords;
    	$this->view->studiesGroupedByLanguages = $studies;
    	$this->view->id = $id;
    }
    
}