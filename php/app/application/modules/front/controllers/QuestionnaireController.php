<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class QuestionnaireController extends BDQ_Locale_FrontController
{

	public function viewAction()
    {
    	$this->view->layout()->setLayout('empty');
    	$id = $this->_request->getBDQParam('id');
    	$mapper = new DB_Mapper_Questionnaire;

    	if ( ! ($questionnaire = $mapper->find($id)))
    	{
    		throw new Zend_Controller_Action_Exception($this->_translate->_('fr0025000000'), 404);
    	}

    	$fileName = $questionnaire->get_file_name();
    	$fileName = stripslashes($fileName);
    	$filePath = QUESTIONNAIRE_FILES . '/' . $fileName;

    	if (file_exists($filePath) && is_file($filePath))
    	{
	    	$pattern = '/\-[0-9]{10}/';
	    	$replace = '';
	    	$fileName = preg_replace($pattern, $replace, $fileName);

	    	$response = $this->getResponse();
	    	$response->setHeader('Content-Type', 'application/pdf', true);
	    	$response->setHeader('Content-Disposition', "inline; filename=\"$fileName\"", true);
	    	$response->sendHeaders();

	    	echo file_get_contents($filePath);
	    	die;
    	}

    	else
    	{
    		throw new Zend_Controller_Action_Exception($this->_translate->_('fr0025000050'), 404);
    	}

	}

}