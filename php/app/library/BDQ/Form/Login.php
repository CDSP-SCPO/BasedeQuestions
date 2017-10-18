<?php

/**
 * @package Form
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class BDQ_Form_Login extends BDQ_Form
{
	
	/**
	 * @var Zend_Translate
	 */
	protected $_translate;
	
	/**
	 * @var string
	 */
	protected $_token;
	
	/**
	 * @param array $options
	 * @param string $token
	 * @return BDQ_Form_Login
	 */
	public function __construct($token, $options = null)
	{
		parent::__construct($options);
		$this->init($token);
	}
	
	/**
	 * @see Zend_Form#init()
	 * @param string $token
	 */
	public function init($token = null)
	{
		$this->_token = $token;
		$this->_translate = Zend_Registry::get('translateAdmin');
		$this->setMethod('post');
		$this->_addToken();
		$this->_addUserName();
		$this->_addPassword();
		$this->_addSubmit();	
	}
	
	protected function _addToken()
	{
		$token = new Zend_Form_Element_Hidden('token');
		$token->setValue($this->_token);
		$token->removeDecorator('Errors');
		$token->removeDecorator('Description');
		$token->removeDecorator('HtmlTag');
		$token->removeDecorator('Label');
		$this->addElement($token);
	}
	
	protected function _addUserName()
	{
		$userName = new Zend_Form_Element_Text('user_name');
		$userName->setRequired(true);
		$userName->addValidators($this->_getTitleValidators());
		$userName->setLabel($this->_translate->_('li0340000000'));
		$this->addElement($userName);
	}
	
	protected function _addPassword()
	{
		$password = new Zend_Form_Element_Password('password');
		$password->setRequired(true);
		$password->setLabel($this->_translate->_('li0340000050'));
		$this->addElement($password);
	}
	
	protected function _addSubmit()
	{
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label' => $this->_translate->_('li0340000100'),
		));
	}

	protected function _getTitleValidators()
	{
		$validators = array();
		$validator = new Zend_Validate_NotEmpty(
			array(
				Zend_Validate_NotEmpty::ALL
	    	)
		);
		$validators[] = $validator;
		return $validators;
	}
}