<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class VariableController extends BDQ_Locale_FrontController
{

    public function init()
    {
    	parent::init();
    }

    public function viewAction()
    {

    	if 
    	( 
	    	! 
	    	(
	    		(($ddiFileId = $this->_request->getBDQParam('ddiFileId')) != URL_PARAM_NULL) 
	    		&& 
	    		(($to = $this->_request->getBDQParam('to')) != URL_PARAM_NULL)
	    		&&
	    		(($from = $this->_request->getBDQParam('from')) != URL_PARAM_NULL)
	    	)
	    )
    	{
    		$id = $this->_request->getBDQParam('id');
    	}

    	else
    	{

	    	if ( ! $this->_request->isXmlHttpRequest())
	    	{
	    		throw new Zend_Controller_Action_Exception('', 404);
	    	}

    		$select = new Solr_Select("ddiFileId:$ddiFileId questionPosition:$to");
    		$select->setFl(array('id', 'variableId'));
    		$response = $select->send();
    		$response->createDocuments();
    		$id = $response->documents[0]->get_variableId();
    		$id = $id[0];
    		$this->view->from = $from;
    	}

    	$variableMapper = new DB_Mapper_Variable;

    	if ( ! ($this->view->variable = $variableMapper->find($id)))
    	{
    		throw new Zend_Controller_Action_Exception($this->_translate->_('fr0010000000'), 404);
    	}

    	$ddiFileMapper = new DB_Mapper_Ddifile;
    	$this->view->ddifile = $ddiFileMapper->find($this->view->variable->get_ddi_file_id());

    	$questionnaireMapper = new DB_Mapper_Questionnaire;
    	$this->view->questionnaires = $questionnaireMapper->findForDdifile($this->view->ddifile->get_id());

    	$studyDescriptionMapper = new DB_Mapper_StudyDescription;
    	$this->view->studyDescription = $studyDescriptionMapper->findForDdifile($this->view->variable->get_ddi_file_id());

    	$questionMapper = new DB_Mapper_Question;
    	$this->view->question = $questionMapper->findForVariable($id);

    	$select = new Solr_Select('variableId:' . $this->view->variable->get_id());
    	$select->setFl(Solr_BDQ_Model_Question::$fields);
    	$response = $select->send();
    	$response->createDocuments();
    	$this->view->solrDocument = $response->documents[0];

    	if ($this->_clientSettings->displayConcept)
    	{
    		$conceptMapper = new DB_Mapper_Concept;
    		$this->view->conceptTitles = $conceptMapper->findAllTitleAndId(
		    	$response->documents[0]->get_conceptId(),
	    		$this->_translationLanguageGuiCurrent->get_id()
	    	);
    	}

    	$categoryMapper = new DB_Mapper_Category;
    	$this->view->categories = $categoryMapper->findForVariable($id);

    	if ($this->view->isAjax = $this->_request->isXmlHttpRequest())
    	{
    		$this->view->layout()->setLayout('empty');
    	}

    }
    
}
