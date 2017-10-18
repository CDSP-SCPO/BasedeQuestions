<?php
/**
 * Checks if the user is logged before any action.
 * 
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 * @package Locale
 */
class BDQ_Locale_AdminController extends BDQ_Locale_FrontController
{

	public function init()
    {
    	parent::init();
    	$controller = $this->_request->getBDQParam('controller');
    	$action = $this->_request->getBDQParam('action');
    	$authNamespace = new Zend_Session_Namespace('auth');
    	
    	if ( ! ($controller == 'index' && $action == 'login'))
    	{
    		
	    	if ($authNamespace->result)
	    	{

	    		if ($authNamespace->result->isValid() !== true)
	    		{
	    			$this->_redirectToLogin();
	    		}
	    	}
	    	
	    	else
	    	{
	    		$this->_redirectToLogin();
	    	}
    	}
    	
    	$this->view->layout()->setLayout('admin');
    }	
   
    protected function _redirectToLogin()
    {
    	$this->_helper->getHelper('Redirector')->setGotoRoute(	
					array(
						'module' => 'admin',
						'controller' => 'index',
						'action' => 'login'
					),
					'adminLogin'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
    }
    
}