<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class Admin_NesstarserverController extends BDQ_Locale_AdminController
{

    public function init()
    {
    	parent::init();
    }

    public function indexAction()
    {
		$mapper = new DB_Mapper_NesstarServer;
		$this->view->nesstarservers = $mapper->findAllWithDetails();

    }
    
    public function viewAction()
    {
    	$request = $this->getRequest();

    	if (($id = $request->getBDQParam('id')) && is_numeric($id))
    	{
    		$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
    		$mapper = new DB_Mapper_NesstarServer;
    		$this->view->nesstarserver = $mapper->find($id);
    		
    		if ($this->view->nesstarserver === NULL)
    		{
    			$this->_redirectToNesstarServerIndex();
    		}
    		
    		$tls = Zend_Registry::get('translationLanguagesSolr');
    		
    		$mapper = new DB_Mapper_StudyDescription;
    		$studies = array();
    		
    		foreach ($tls as $tl)
    		{
    			
    			$titles = $mapper->findStudyTitlesForNesstarserver($id, $tl->get_id());
    			
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
    		$this->_redirectToNesstarServerIndex();
    	}

    }

	public function addAction()
    {
		$form = new BDQ_Form_NesstarServer;
		$request = $this->getRequest();

		if ($request->isPost())
        {

        	if ($form->isValid($data = $request->getPost()))
			{
				$this->_save($data);
				$this->_redirectToNesstarServerIndex();
			}

		}

		$this->view->form = $form;
    }
    
    public function editAction()
    {
    	$request = $this->getRequest();

		if (($id = $request->getBDQParam('id')) && ! $request->isPost() && is_numeric($id))
		{
			$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
			$mapper = new DB_Mapper_NesstarServer;
			$ns = $mapper->find($id);
			
			if ($ns === NULL)
    		{
    			$this->_redirectToNesstarServerIndex();
    		}
			
			$this->view->form = new BDQ_Form_NesstarServer(
				array('nesstarserver' => $ns)
			);
		}
		
		elseif ($request->isPost() && is_numeric($id))
		{
			$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
			$form = new BDQ_Form_NesstarServer;

			if ($form->isValid($data = $request->getPost()))
			{
				$this->_save($data);
				$this->_redirectToNesstarServerIndex();
			}
			
			$this->view->form = $form;

		}
		
		else
		{
			$this->_redirectToNesstarServerIndex();
		}
		
    }
    
    protected function _save($data)
    {
		$nesstarserver = new DB_Model_NesstarServer;
		
		foreach ($data as $name => $value)
		{
			
			if (method_exists($nesstarserver, $method = "set_$name"))
			{
				$nesstarserver->$method($value);
			}
			
		}
		
		if ( ! is_numeric($nesstarserver->get_id()))
		{
			$nesstarserver->set_id(NULL);
		}
		
		$ip = $nesstarserver->get_ip();
		$nesstarserver->set_domain_name(gethostbyaddr($ip));
		$mapper = new DB_Mapper_NesstarServer;
		return $mapper->save($nesstarserver);
    }
	
	public function confirmdeleteAction()
    {
		$request = $this->getRequest();

		if ($id = $request->getBDQParam('id'))
		{
			$mapper = new DB_Mapper_NesstarServer;
			$this->view->nesstarserver = $mapper->find($id);
		}

	}

	public function deleteAction()
    {
		$request = $this->getRequest();
		
    	if ($request->isPost() && ($id = $request->getBDQParam('id')) && is_numeric($id))
		{
			$mapper = new DB_Mapper_NesstarServer;

			if ($mapper->delete($id))
			{
				$this->_redirectToNesstarServerIndex();
			}
		}
		
		else
		{
			$this->_redirectToNesstarServerIndex();
		}

	}

	protected function _redirectToNesstarServerIndex()
    {
    	$this->_helper->getHelper('Redirector')->setGotoRoute(	
			array(
				'module' => 'admin',
				'controller' => 'nesstarserver',
				'action' => 'index'
			),
			'nesstarserverIndex'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
    }
    
}