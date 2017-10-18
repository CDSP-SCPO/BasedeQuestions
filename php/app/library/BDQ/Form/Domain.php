<?php

/**
 * @package Form
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class BDQ_Form_Domain extends BDQ_Locale_Form
{
	
	/**
	 * @var Zend_Translate
	 */
	protected $_translate;
	
	/**
	 * @var DB_Domain
	 */
	protected $_domain;
	
	/**
	 * @var array
	 */
	protected $_translations;

	/**
	 * @param array $options
	 * @return BDQ_Form_Domain
	 */
	public function __construct($options = null)
	{
		parent::__construct($options);
	}
	
	/**
	 * @see Zend_Form#init()
	 * @param DB_Model_Domain $domain
	 */
	public function init()
	{
		parent::init();
		$this->setMethod('post');
		$this->_addHiddenId('id', '', 'domain'); // Used to edit data
		$this->_addHiddenId('title_translation_id', 'title_translation', 'domain'); // Used to edit data
		$this->_addHiddenId('description_translation_id', 'description_translation', 'domain'); // Used to edit data
		
		// Translated fields
		$tls = Zend_Registry::get('translationLanguagesGui');

		foreach($tls as $tl)
		{
			$elt = $this->_getTranslatedField(
				'title',
				$tl->get_id()
			);
			$elt->setLabel($this->_translate->_('li0335000000') . ' - ' . $tl->get_code());
			$elt->setRequired(true);
			$this->addElement($elt);
			
			$elt = $this->_getTranslatedField(
				'description',
				$tl->get_id(),
				'Textarea'
			);
			$elt->setLabel($this->_translate->_('li0335000050') . ' - ' . $tl->get_code());
			$this->addElement($elt);
		}
		
		$this->_addSubmit();
	}

	protected function _addSubmit()
	{
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label' => $this->_translate->_('li0335000100'),
		));
	}

	/**
	 * @param array $translations
	 * @return BDQ_Form_Domain
	 */
	public function setTranslations(array $translations)
	{
		$this->_translations = $translations;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getTranslations()
	{
		return $this->_translations;
	}
	
	/**
	 * @param DB_Model_Domain $domain
	 * @return BDQ_Form_Domain
	 */
	public function setDomain(DB_Model_Domain $domain)
	{
		$this->_domain = $domain;
		return $this;
	}
	
	/**
	 * @return DB_Model_Domain
	 */
	public function getDomain()
	{
		return $this->_domain;
	}
	
}