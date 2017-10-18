<?php

/**
 * @package Form
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class BDQ_Form_NesstarServer extends BDQ_Form
{
	
	/**
	 * @var Zend_Translate
	 */
	protected $_translate;
	
	/**
	 * @var DB_NesstarServer
	 */
	protected $_nesstarserver;
	
	/**
	 * @param array $options
	 * @param DB_NesstarServer $nesstarserver
	 * @return BDQ_Form_Domain
	 */
	public function __construct($options = null)
	{
		parent::__construct($options);
	}
	
	/**
	 * @see Zend_Form#init()
	 * @param DB_NesstarServer $nesstarserver
	 */
	public function init( )
	{
		$this->_translate = Zend_Registry::get('translateAdmin');
		$this->setMethod('post');
		$this->_addId();
		$this->_addTitle();
		$this->_addIp();
		$this->_addPort();
		$this->_addResponsible();
		$this->_addSubmit();	
	}
	
	protected function _addId()
	{
		$id = new Zend_Form_Element_Hidden('id');
		
		if ($this->_nesstarserver !== NULL)
		{	
			$id->setValue($this->_nesstarserver->get_id());
		}
		
		$id->removeDecorator('Errors');
		$id->removeDecorator('Description');
		$id->removeDecorator('HtmlTag');
		$id->removeDecorator('Label');
		$this->addElement($id);
	}
	
	protected function _addTitle()
	{
		$title = new Zend_Form_Element_Text('title');
		
		if ($this->_nesstarserver !== NULL)
		{		
			$title->setValue(stripslashes($this->_nesstarserver->get_title()));
		}
		
		$title->setRequired(true);
		$title->addValidators($this->_getTitleValidators());
		$title->setLabel($this->_translate->_('li0330000000'));
		$this->addElement($title);
	}
	
	protected function _addIp()
	{
		$ip = new Zend_Form_Element_Text('ip');
		
		if ($this->_nesstarserver !== NULL)
		{		
			$ip->setValue(stripslashes($this->_nesstarserver->get_ip()));
		}
		
		$ip->addValidator(new Zend_Validate_Ip);
		$ip->setLabel($this->_translate->_('li0330000050'));
		$this->addElement($ip);
	}
	
	protected function _addPort()
	{
		$port = new Zend_Form_Element_Text('port');
		
		if ($this->_nesstarserver !== NULL)
		{		
			$port->setValue(stripslashes($this->_nesstarserver->get_port()));
		}
		
		$port->setLabel($this->_translate->_('li0330000100'));
		$this->addElement($port);
	}
	
	protected function _addResponsible()
	{
		$responsible = new Zend_Form_Element_Text('responsible');
		
		if ($this->_nesstarserver !== NULL)
		{		
			$responsible->setValue(stripslashes($this->_nesstarserver->get_responsible()));
		}
		
		$responsible->setRequired(false);
		$responsible->addValidators($this->_getTitleValidators());
		$responsible->addValidator(new Zend_Validate_EmailAddress);
		$responsible->setLabel($this->_translate->_('li0330000150'));
		$this->addElement($responsible);
	}
	
	protected function _addSubmit()
	{
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label' => 'Envoyer',
		));
	}
	
	public function setNesstarserver(DB_Model_NesstarServer $server)
	{
		$this->_nesstarserver = $server;
		return $this;
	}
	
	public function getNesstarserver()
	{
		return $this->_nesstarserver;
	}
	
}