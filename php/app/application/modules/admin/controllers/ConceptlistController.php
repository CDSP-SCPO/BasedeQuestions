<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class Admin_ConceptlistController extends BDQ_Locale_AdminController
{

    public function init()
    {
    	parent::init();
    }

    public function indexAction()
    {
		$mapper = new DB_Mapper_ConceptList;
		$this->view->conceptlists = $mapper->findAllWithDetails($this->_translationLanguageGuiCurrent->get_id());
    }
    
    public function viewAction()
    {
    	$request = $this->getRequest();

    	if (($id = $request->getBDQParam('id')) && is_numeric($id))
    	{
    		$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
    		$mapper = new DB_Mapper_ConceptList;
    		$this->view->conceptlist = $mapper->findAllTranslations($id, $this->_translationLanguageGuiCurrent->get_id());
    		
    		if ($this->view->conceptlist === NULL)
    		{
    			$this->_redirectToConceptListIndex();
    		}
    		
    		$mapper = new DB_Mapper_Concept;
    		$this->view->concepts = $mapper->findAllForConceptList($id, $this->_translationLanguageGuiCurrent->get_id());
    		
    	}
    	
    	else
    	{
    		$this->_redirectToConceptListIndex();
    	}

    }

	public function addAction()
    {
		$form = new BDQ_Form_ConceptList;
		$request = $this->getRequest();

		if ($request->isPost())
        {

        	if ($form->isValid($data = $request->getPost()))
			{
				$this->_add($data);
				$this->_redirectToConceptListIndex(); 
			}

		}

		$this->view->form = $form;
    }

	protected function _add($data)
    {
		$cl = new DB_Model_ConceptList;
		$titleTranslation = new DB_Model_Translation;
		$descTranslation = new DB_Model_Translation;
    	$mapper = new DB_Mapper_Translation;
		$cl->set_title_translation_id($mapper->save($titleTranslation));
		$cl->set_description_translation_id($mapper->save($descTranslation));
		$tls = Zend_Registry::get('translationLanguagesGui');
		$mapper = new DB_Mapper_TranslationEntry;

		foreach($tls as $tl)
		{
			$languageId = $tl->get_id();

			$titleTranslation = new DB_Model_TranslationEntry;
			$titleTranslation->set_translation_id($cl->get_title_translation_id());
			$titleTranslation->set_translation_language_id($languageId);
			$titleTranslation->set_translated_text($data["ttitle_$languageId"]);
			$mapper->save($titleTranslation);

			$descTranslation = new DB_Model_TranslationEntry;
			$descTranslation->set_translation_id($cl->get_description_translation_id());
			$descTranslation->set_translation_language_id($languageId);
			$descTranslation->set_translated_text($data["tdescription_$languageId"]);
			$mapper->save($descTranslation);
		}

		$mapper = new DB_Mapper_ConceptList;
		return $mapper->save($cl);
    }

    public function editAction()
    {
    	$request = $this->getRequest();

		if (($id = $request->getBDQParam('id')) && ! $request->isPost() && is_numeric($id))
		{
			$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
			$mapper = new DB_Mapper_ConceptList;
			$ts = $mapper->findAllTranslations($id, $this->_translationLanguageGuiCurrent->get_id());
			
			if ($ts === NULL)
			{
				$this->_redirectToConceptListIndex();
			}
			
			/*
			 * A form is created to validate data
			 * translation_entries rows id are given in form inputs' names
			 */
			$this->view->form = new BDQ_Form_ConceptList(
				array(
					'conceptlist' => $mapper->find($id),
					'translations' => $ts
				)
			);
		}
		
		elseif (($id = $request->getBDQParam('id')) && $request->isPost() && is_numeric($id))
		{
			$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
			$mapper = new DB_Mapper_ConceptList;
			/*
			 * A form is created to validate data
			 * translation_entries rows id are given in form inputs' names
			 */
			
			$form = new BDQ_Form_ConceptList(
				array(
					'conceptlist' => $mapper->find($id), 
					'translations' => $mapper->findAllTranslations($id, $this->_translationLanguageGuiCurrent->get_id())
				)
			);
			
			if ($form->isValid($data = $request->getPost()))
			{
				$this->_edit($data);
				$this->_redirectToConceptListIndex(); 
			}

			$this->view->form = $form;
			
		}
		
		else
		{
			$this->_redirectToConceptListIndex(); 
		}
		
    }

    /**
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

	public function confirmdeleteAction()
    {
		$request = $this->getRequest();

		if ($id = $request->getBDQParam('id'))
		{
			$mapper = new DB_Mapper_ConceptList;
			$this->view->conceptlist = $mapper->findTitleTranslation(
				$id, 
				$this->_translationLanguageGuiCurrent->get_id()
			);
		}

	}

	public function deleteAction()
    {
		$request = $this->getRequest();
		
    	if ($request->isPost() && ($id = $request->getBDQParam('id')))
		{
			$mapper = new DB_Mapper_ConceptList;

			if ($mapper->delete($id))
			{
				$this->_redirectToConceptListIndex(); 
			}
		}
		
		else
		{
			$this->_redirectToConceptListIndex(); 
		}

	}

	protected function _redirectToConceptListIndex()
	{
		$this->_helper->getHelper('Redirector')->setGotoRoute(	
			array(
				'module' => 'admin',
				'controller' => 'conceptlist',
				'action' => 'index'
			),
			'adminconceptlistIndex'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
	}
	
}