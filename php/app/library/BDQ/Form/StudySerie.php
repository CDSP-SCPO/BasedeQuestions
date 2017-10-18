<?php

/**
 * @package Form
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class BDQ_Form_StudySerie extends BDQ_Locale_Form
{
	
	/**
	 * @var Zend_Translate
	 */
	protected $_translate;
	
	/**
	 * @var DB_StudySerie
	 */
	protected $_studyserie;
	
	/**
	 * @var array
	 */
	protected $_translations;

	/**
	 * @param array $options
	 * @return BDQ_Form_StudySerie
	 */
	public function __construct($options = null)
	{
		parent::__construct($options);
	}
	
	/**
	 * @see Zend_Form#init()
	 * @param DB_Model_StudySerie $studyserie
	 */
	public function init()
	{
		parent::init();
		$this->setMethod('post');
		$this->_addHiddenId('id', '', 'studyserie'); // Used to edit data
		$this->_addHiddenId('title_translation_id', 'title_translation', 'studyserie'); // Used to edit data
		$this->_addHiddenId('description_translation_id', 'description_translation', 'studyserie'); // Used to edit data
		
		// Translated fields
		$tls = Zend_Registry::get('translationLanguagesGui');

		foreach($tls as $tl)
		{
			$elt = $this->_getTranslatedField(
				'title',
				$tl->get_id()
			);
			
			$elt->setLabel($this->_translate->_('li0350000000') . ' - ' . $tl->get_code());
			$elt->setRequired(true);
			$this->addElement($elt);
			
			$elt = $this->_getTranslatedField(
				'description',
				$tl->get_id(),
				'Textarea'
			);
			$elt->setLabel($this->_translate->_('li0350000050') . ' - ' . $tl->get_code());
			$this->addElement($elt);
		}
		
		$this->_addSubmit();
	}

	protected function _addSubmit()
	{
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label' => $this->_translate->_('li0350000100'),
		));
	}

	/**
	 * 
	 * @param array $translations
	 * @return BDQ_Form_StudySerie
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
	 * @param DB_Model_StudySerie $studyserie
	 * @return BDQ_Form_StudySerie
	 */
	public function setStudySerie(DB_Model_StudySerie $studyserie)
	{
		$this->_studyserie = $studyserie;
		return $this;
	}
	
	/**
	 * @return DB_Model_StudySerie
	 */
	public function getStudySerie()
	{
		return $this->_studyserie;
	}
}