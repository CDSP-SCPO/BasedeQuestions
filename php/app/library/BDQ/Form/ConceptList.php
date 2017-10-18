<?php

/**
 * @package Form
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class BDQ_Form_ConceptList extends BDQ_Locale_Form
{
	
	/**
	 * @var Zend_Translate
	 */
	protected $_translate;
	
	/**
	 * @var DB_ConceptList
	 */
	protected $_conceptlist;
	
	/**
	 * @var array
	 */
	protected $_translations;
	
	/**
	 * @param array $options
	 * @return BDQ_Form_ConceptList
	 */
	public function __construct($options = null)
	{
		parent::__construct($options);
	}
	
	/**
	 * @see Zend_Form#init()
	 * @param DB_ConceptList $conceptlist
	 */
	public function init()
	{
		parent::init();
		$this->setMethod('post');
		$this->_addHiddenId('id', '', 'conceptlist');
		$this->_addHiddenId('title_translation_id', 'title_translation', 'conceptlist'); // Used to edit data
		$this->_addHiddenId('description_translation_id', 'description_translation', 'conceptlist'); // Used to edit data
		
		// Translated fields
		$tls = Zend_Registry::get('translationLanguagesGui');

		foreach($tls as $tl)
		{
			$elt = $this->_getTranslatedField(
				'title',
				$tl->get_id()
			);
			$elt->setLabel($this->_translate->_('li0315000000') . ' - ' . $tl->get_code());
			$elt->setRequired(true);
			$this->addElement($elt);
			
			$elt = $this->_getTranslatedField(
				'description',
				$tl->get_id(),
				'Textarea'
			);
			$elt->setLabel($this->_translate->_('li0315000050') . ' - ' . $tl->get_code());
			$this->addElement($elt);
		}
		$this->_addSubmit();	
	}
	
	protected function _addId()
	{
		$id = new Zend_Form_Element_Hidden('id');
		
		if ($this->_conceptlist !== NULL)
		{	
			$id->setValue($this->_conceptlist->get_id());
		}
		
		$id->removeDecorator('Errors');
		$id->removeDecorator('Description');
		$id->removeDecorator('HtmlTag');
		$id->removeDecorator('Label');
		$this->addElement($id);
	}
	
	protected function _addSubmit()
	{
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label' => 'Envoyer',
		));
	}

	/**
	 * @param array $translations
	 * @return BDQ_Form_ConceptList
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
	 * @param DB_Model_ConceptList $cl
	 * @return BDQ_Form_ConceptList
	 */
	public function setConceptlist(DB_Model_ConceptList $cl)
	{
		$this->_conceptlist = $cl;
		return $this;
	}
	
	/**
	 * @return DB_Model_ConceptList
	 */
	public function getConceptlist()
	{
		return $this->_conceptlist;
	}

}