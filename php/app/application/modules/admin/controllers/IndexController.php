<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class Admin_IndexController extends BDQ_Locale_AdminController
{

    public function init()
    {
    	parent::init();
    }

    public function indexAction()
    {
    }

	public function loginAction()
	{
		$translate = Zend_Registry::get('translateAdmin');
		$tokenNameSpace = new Zend_Session_Namespace('token');
		$form = new BDQ_Form_Login($token = md5(uniqid()));
		$data = $this->_request->getPost();

		if ($this->_request->isPost() && $form->isValidPartial(
			array(
				'user_name' => $data['user_name'],
				'password' => $data['password']
			)
		))
		{
			
			if ( ! isset($data['token']))
			{
				$this->_redirectToIndex();
			}
			
			if ($tokenNameSpace->token && $tokenNameSpace->token === $data['token'])
			{
				
				if ($this->_checkCredentials($data['user_name'], $data['password'])->isValid())
				{
					$this->_redirectToIndex();
				}
				
				else
				{
					$this->view->message = $translate->_('fr0000000000');
				}
				
			}

		}
		
		$tokenNameSpace->token = $token;
		$this->view->form = $form;
		$this->view->layout()->setLayout('login');
	}

	public function disconnectAction()
	{
		$authNamespace = new Zend_Session_Namespace('auth');
		unset($authNamespace->result);
		$this->_redirectToLogin();
	}
	
	public function _checkCredentials($user_name, $password)
	{
		$mapper = new DB_Mapper_User;
		$authAdapter = new Zend_Auth_Adapter_DbTable(
			$mapper->getDBTable()->getAdapter()
		);
		$authAdapter->setTableName('users')
				->setIdentityColumn('user_name')
				->setCredentialColumn('password');
		$authAdapter->setIdentity($user_name)
				->setCredential(md5($password . DB_Model_User::$salt));
		$authNamespace = new Zend_Session_Namespace('auth');
		return $authNamespace->result = $authAdapter->authenticate();
	}
	
	protected function _redirectToIndex()
    {
    	$this->_helper->getHelper('Redirector')->setGotoRoute(	
					array(
						'module' => 'admin',
						'controller' => 'index',
						'action' => 'index'
					),
					'adminIndex'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
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