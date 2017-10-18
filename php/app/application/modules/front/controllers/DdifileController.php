<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DdifileController extends BDQ_Locale_FrontController
{

    public function init()
    {
    	parent::init();
    }

    public function indexAction()
    {
    	$mapper = new DB_Mapper_TranslationLanguage;
		$languagesLinkedToADdifile = $mapper->findAllLinkedToAtLeastOneDdifile($this->_translationLanguageGuiCurrent->get_id());
		$l = count($languagesLinkedToADdifile);
		$studiesByLang = array();
		$mapper = new DB_Mapper_Ddifile;
		
		for ($i = 0 ; $i < $l ; $i++)
		{
			$studiesByLang[$languagesLinkedToADdifile[$i]['label']] = $mapper->findAllWithDetails($this->_translationLanguageGuiCurrent->get_id(), 0, 0, $languagesLinkedToADdifile[$i]['translation_language_id']); 
		}
		
		$this->view->studiesByLang = $studiesByLang;
		$ddiFileMapper = new DB_Mapper_Ddifile;
    	$this->view->ddiFileCounts = $ddiFileMapper->getCountsByLanguage(
    		$this->_translationLanguageGuiCurrent->get_id()
    	);
    	$variableMapper = new DB_Mapper_Variable;
    	$this->view->variableCount = $variableMapper->getCount();
    }
    
    public function viewAction()
    {
    	$id = $this->_request->getBDQParam('id');
    	$ddiFileMapper = new DB_Mapper_Ddifile;

    	if ( ! $this->view->ddifile = $ddiFileMapper->find($id))
    	{
			throw new Zend_Controller_Action_Exception($this->_translate->_('fr0045000000'), 404);
    	}

    	$serieMapper = new DB_Mapper_StudySerie;
    	$this->view->serie = $serieMapper->findWithDetails($this->view->ddifile->get_study_serie_id(), $this->_translationLanguageGuiCurrent->get_id()); 

    	$questionnaireMapper = new DB_Mapper_Questionnaire;
    	$this->view->questionnaires = $questionnaireMapper->findForDdifile($id);

    	$translationLanguageMapper = new DB_Mapper_TranslationLanguage;
    	$this->view->translationLanguage = $translationLanguageMapper->find($this->view->ddifile->get_translation_language_id());

    	$studyDescriptionMapper = new DB_Mapper_StudyDescription;
    	$this->view->studyDescription = $studyDescriptionMapper->findForDdifile($id);

    	$producerMapper = new DB_Mapper_Producer;
    	$this->view->producers = $producerMapper->findForDdifile($id);

    	$collectDateMapper = new DB_Mapper_CollectDate;
    	$this->view->collectDates = $collectDateMapper->findForDdifile($id);

    	$distributorMapper = new DB_Mapper_Distributor;
    	$this->view->distributors = $distributorMapper->findForDdifile($id);

    	$agencyMapper = new DB_Mapper_FundingAgency;
    	$this->view->fundingAgencies = $agencyMapper->findForDdifile($id);
    	
    	$domainMapper = new DB_Mapper_Domain;
    	$this->view->domains = $domainMapper->findTitleTranslationsForDdifile(
    		$this->view->ddifile->get_id(),
    		$this->_translationLanguageGuiCurrent->get_id()
    	);
    	
    	if ($clId = $this->view->ddifile->get_concept_list_id())
    	{
    		$search = new Solr_BDQ_Search_ConceptFacet(
    			'studyDescriptionId:' . $this->view->studyDescription['id'],
    			$this->_translationLanguageGuiCurrent->get_id()
    		);
    		$this->view->concepts = $search->getConceptFacets();
    		$conceptListMapper = new DB_Mapper_ConceptList;
    		$this->view->conceptList = $conceptListMapper->findTitleTranslation(
    			$clId,
    			$this->_translationLanguageGuiCurrent->get_id()
    		);
    	}

    	if ($this->view->isAjax = $this->_request->isXmlHttpRequest())
	    {
	    	$this->view->layout()->setLayout('empty');
	    }
	    
	    $wc = new Solr_BDQ_Search_WordCloud(
	    	'studyDescriptionId:' . $this->view->studyDescription['id'],
	    	$this->view->translationLanguage->get_code_solr()
	    );
	    $wc->tfLowerBound = 0.43;
	    $this->view->cloudWords = $wc->getWords();

    }

}
