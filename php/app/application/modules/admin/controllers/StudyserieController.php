<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class Admin_StudySerieController extends BDQ_Locale_AdminController
{

	
    public function init()
    {
    	parent::init();
    }

    public function indexAction()
    {
		$mapper = new DB_Mapper_StudySerie;
		$this->view->studyseries = $mapper->findAllWithDetails($this->_translationLanguageGuiCurrent->get_id());
    }

    public function viewAction()
    {
    	$request = $this->getRequest();

    	if (($id = $request->getBDQParam('id')) && is_numeric($id))
    	{
    		$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
    		$mapper = new DB_Mapper_StudySerie;
    		$this->view->studyserie = $mapper->findAllTranslations($id, $this->_translationLanguageGuiCurrent->get_id());
    		
    		if ($this->view->studyserie === NULL)
    		{
    			$this->_redirectToStudySerieIndex();
    		}
    		
    		$tls = Zend_Registry::get('translationLanguagesSolr');
    		
    		$mapper = new DB_Mapper_StudyDescription;
    		$studies = array();
    		
    		foreach ($tls as $tl)
    		{
    			
    			$titles = $mapper->findStudyTitlesForStudySerie($id, $tl->get_id());
    			
    			if (count($titles) > 0)
    			{
	    			$studies[] = array(
	    				'titles' => $titles,
	    				'language_code' => $tl->get_code(),
	    			);
    			}
    			
    		}
    		
    		$this->view->studiesGroupedByLanguages = $studies;
    	}
    	
    	else
    	{
    		$this->_redirectToStudySerieIndex();
    	}

    }

	public function addAction()
    {
		$form = new BDQ_Form_StudySerie;
		$request = $this->getRequest();

		if ($request->isPost())
        {

        	if ($form->isValid($data = $request->getPost()))
			{
				$this->_add($data);
				$this->_redirectToStudySerieIndex();
			}

		}

		$this->view->form = $form;
    }

	/**
     * @param array $data
     * @return int
     */
    protected function _add(array $data)
    {
		$studyserie = new DB_Model_StudySerie;
		$titleTranslation = new DB_Model_Translation;
		$descTranslation = new DB_Model_Translation;
    	$mapper = new DB_Mapper_Translation;
		$studyserie->set_title_translation_id($mapper->save($titleTranslation));
		$studyserie->set_description_translation_id($mapper->save($descTranslation));
		$tls = Zend_Registry::get('translationLanguagesGui');
		$mapper = new DB_Mapper_TranslationEntry;

		foreach($tls as $tl)
		{
			$languageId = $tl->get_id();

			$titleTranslation = new DB_Model_TranslationEntry;
			$titleTranslation->set_translation_id($studyserie->get_title_translation_id());
			$titleTranslation->set_translation_language_id($languageId);
			$titleTranslation->set_translated_text($data["ttitle_$languageId"]);
			$mapper->save($titleTranslation);
			
			$descTranslation = new DB_Model_TranslationEntry;
			$descTranslation->set_translation_id($studyserie->get_description_translation_id());
			$descTranslation->set_translation_language_id($languageId);
			$descTranslation->set_translated_text($data["tdescription_$languageId"]);
			$mapper->save($descTranslation);
		}
		
		$mapper = new DB_Mapper_StudySerie;
		return $mapper->save($studyserie);
    }

    /**
     * StudySerie edit
     */
    public function editAction()
    {
    	$request = $this->getRequest();

		if (($id = $request->getBDQParam('id')) && ! $request->isPost() && is_numeric($id))
		{
			$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
			$mapper = new DB_Mapper_StudySerie;
			$ts = $mapper->findAllTranslations($id, $this->_translationLanguageGuiCurrent->get_id());
			
			if ($ts === NULL)
			{
				$this->_redirectToStudySerieIndex();
			}
			
			/*
			 * A form is created to validate data
			 * translation_entries rows id are given in form inputs' names
			 */
			$this->view->form = new BDQ_Form_StudySerie(
				array(
					'studyserie' => $mapper->find($id), 
					'translations' => $ts
				)
			);
		}
		
		elseif (($id = $request->getBDQParam('id')) && $request->isPost() && is_numeric($id))
		{
			$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
			$mapper = new DB_Mapper_StudySerie;
			/*
			 * A form is created to validate data
			 * translation_entries rows id are given in form inputs' names
			 */
			$form = new BDQ_Form_StudySerie(
				array(
					'studyserie' => $mapper->find($id), 
					'translations' => $mapper->findAllTranslations($id, $this->_translationLanguageGuiCurrent->get_id())
				)
			);

			if ($form->isValid($data = $request->getPost()))
			{
				$this->_edit($data);
				$this->_redirectToStudySerieIndex();
			}
			
			$this->view->form = $form;

		}
		
		else
		{
			$this->_redirectToStudySerieIndex();
		}
		
    }
    
    /**
     * 
     * @param array $data
     * @return int
     */
    protected function _edit(array $data)
    {
    	$mapper = new DB_Mapper_TranslationEntry;
    	
    	foreach($data as $k => $v)
    	{
    		
    		if (strpos($k, 'ttitle') !== false || strpos($k, 'tdescription') !== false) 
    		// We are in a translated field
    		{
    			// What is this field translation entry id ?
    			$tfield = strpos($k, 'title') !== false ? 'title_translation_id' : 'description_translation_id';
    			$k = explode('_', $k);
    			$teId = $k[2]; // translation_entries table id
    			$tlId = $k[1]; // translation_language table id

    			if(is_numeric($k[2]))
    			// an translation entry was saved before
    			{    			
    				$te = $mapper->find($k[2]);
    			}
    			
    			elseif($teId == 'n')
    			// no translation entry was saved before so one is created then saved
    			{
    				$te = new DB_Model_TranslationEntry;
    				$te->set_translation_id($data[$tfield]);
    				$te->set_translation_language_id($tlId);
    			}
    	
    			$te->set_translated_text($v);
    			$mapper->save($te);
    		}
    		
    	}
    	
    }    
    
    /**
     * StudySerie confirm delete
     */
	public function confirmdeleteAction()
    {
		$request = $this->getRequest();

		if ($id = $request->getBDQParam('id'))
		{
			$mapper = new DB_Mapper_StudySerie;
			$this->view->studyserie = $mapper->findTitleTranslation(
				$id, 
				$this->_translationLanguageGuiCurrent->get_id()
			);
		}

	}

	/**
	 * StudySerie delete
	 */
	public function deleteAction()
    {
		$request = $this->getRequest();
		
    	if ($request->isPost() && ($id = $request->getBDQParam('id')) && is_numeric($id))
		{
			$mapper = new DB_Mapper_StudySerie;

			if ($mapper->delete($id))
			{
				$this->_redirectToStudySerieIndex();
			}
		}
		
		else
		{
			$this->_redirectToStudySerieIndex();
		}

	}

	protected function _redirectToStudySerieIndex()
    {
    	$this->_helper->getHelper('Redirector')->setGotoRoute(	
			array(
				'module' => 'admin',
				'controller' => 'studyserie',
				'action' => 'index'
			),
			'studyserieIndex'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
    }
}