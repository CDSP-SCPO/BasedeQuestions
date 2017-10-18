<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class Admin_ConceptController extends BDQ_Locale_AdminController
{
	
    public function init()
    {
    	parent::init();
    }

    public function indexAction()
    {
    	
		$mapper = new DB_Mapper_Concept;
		$this->view->concepts = $mapper->findAllWithDetails($this->_translationLanguageGuiCurrent->get_id());
    }
    
    public function viewAction()
    {
    	$request = $this->getRequest();

    	if (($id = $request->getBDQParam('id')) && is_numeric($id))
    	{
    		$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
    		$mapper = new DB_Mapper_Concept;
    		$this->view->concept = $mapper->findAllTranslations($id, $this->_translationLanguageGuiCurrent->get_id());
    		
    		if ($this->view->concept === NULL)
    		{
    			$this->_redirectToConceptIndex();
    		}
    		
    		$mapper = new DB_Mapper_ConceptList; 
    		$this->view->conceptlist = $mapper->findTitleTranslationForConcept($id, $this->_translationLanguageGuiCurrent->get_id());
    		
    	}
    	
    	else
    	{
    		$this->_redirectToConceptIndex();
    	}

    }

	public function addAction()
    {
		$form = new BDQ_Form_Concept;
		$request = $this->getRequest();

		if ($request->isPost())
        {

        	if ($form->isValid($data = $request->getPost()))
			{
				$this->_add($data);
				$this->_redirectToConceptIndex(); 
			}

		}

		$this->view->form = $form;
    }
    
	protected function _add($data)
    {
		$c = new DB_Model_Concept;
		$titleTranslation = new DB_Model_Translation;
		$descTranslation = new DB_Model_Translation;
    	$mapper = new DB_Mapper_Translation;
		$c->set_title_translation_id($mapper->save($titleTranslation));
		$c->set_concept_list_id($data['concept_list_id']);

		$tls = Zend_Registry::get('translationLanguagesGui');
		$mapper = new DB_Mapper_TranslationEntry;

		foreach($tls as $tl)
		{
			$languageId = $tl->get_id();
			$titleTranslation = new DB_Model_TranslationEntry;
			$titleTranslation->set_translation_id($c->get_title_translation_id());
			$titleTranslation->set_translation_language_id($languageId);
			$titleTranslation->set_translated_text($data["ttitle_$languageId"]);
			$mapper->save($titleTranslation);
		}
		
		$mapper = new DB_Mapper_Concept;
		return $mapper->save($c);
    }
    
    public function editAction()
    {
    	$request = $this->getRequest();

		if (($id = $request->getBDQParam('id')) && ! $request->isPost() && is_numeric($id))
		{
			$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
			$mapper = new DB_Mapper_Concept;
			$ts = $mapper->findAllTranslations($id, $this->_translationLanguageGuiCurrent->get_id());
			
			if ($ts === NULL)
			{
				$this->_redirectToConceptIndex();
			}
			
			/*
			 * A form is created to validate data
			 * translation_entries rows id are given in form inputs' names
			 */
			$this->view->form = new BDQ_Form_Concept(
				array(
					'concept' => $mapper->find($id),
					'translations' => $ts
				)
			);
		}
		
		elseif (($id = $request->getBDQParam('id')) && $request->isPost() && is_numeric($id))
		{
			$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
			$mapper = new DB_Mapper_Concept;
			/*
			 * A form is created to validate data
			 * translation_entries rows id are given in form inputs' names
			 */
			$form = new BDQ_Form_Concept(
				array(
					'concept' => $mapper->find($id), 
					'translations' => $mapper->findAllTranslations($id, $this->_translationLanguageGuiCurrent->get_id())
				)
			);
			
			if ($form->isValid($data = $request->getPost()))
			{
				$this->_edit($data);
				$this->_redirectToConceptIndex(); 
			}

			$this->view->form = $form;
			
		}
		
		else
		{
			$this->_redirectToConceptIndex(); 
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
    	
    	$mapper = new DB_Mapper_Concept;
    	$c = $mapper->find($data['id']);
    	$c->set_concept_list_id($data['concept_list_id']);
    	$mapper->save($c);
    }

	public function confirmdeleteAction()
    {
		$request = $this->getRequest();

		if ($id = $request->getBDQParam('id'))
		{
			$mapper = new DB_Mapper_Concept;
			$this->view->concept = $mapper->findTitleTranslation(
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
			$mapper = new DB_Mapper_Concept;

			if ($mapper->delete($id))
			{
				$this->_redirectToConceptIndex(); 
			}
		}
		
		else
		{
			$this->_redirectToConceptIndex(); 
		}

	}

	protected function _redirectToConceptIndex()
	{
		$this->_helper->getHelper('Redirector')->setGotoRoute(	
			array(
				'module' => 'admin',
				'controller' => 'concept',
				'action' => 'index'
			),
			'adminconceptIndex'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
	}
	
}