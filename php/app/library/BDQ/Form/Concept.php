<?php

/**
 * @package Form
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class BDQ_Form_Concept extends BDQ_Locale_Form
{
	
	/**
	 * @var Zend_Translate
	 */
	protected $_translate;
	
	/**
	 * @var DB_Concept
	 */
	protected $_concept;
	
	/**
	 * @var array
	 */
	protected $_translations;
	
	/**
	 * @param array $options
	 * @return BDQ_Form_Concept
	 */
	public function __construct($options = null)
	{
		parent::__construct($options);
	}
	
	/**
	 * @see Zend_Form#init()
	 */
	public function init()
	{
		parent::init();
		$this->setMethod('post');
		$this->_addHiddenId('id', '', 'concept');
		$this->_addHiddenId('title_translation_id', 'title_translation', 'concept'); // Used to edit data
		
		// Translated fields
		$tls = Zend_Registry::get('translationLanguagesGui');
		
		$this->_addConceptListId();

		foreach($tls as $tl)
		{
			$elt = $this->_getTranslatedField(
				'title',
				$tl->get_id()
			);
			$elt->setLabel($this->_translate->_('li0325000000') . ' - ' . $tl->get_code());
			$elt->setRequired(true);
			$this->addElement($elt);
		}
		
		$this->_addSubmit();	
	}
	
	protected function _addConceptListId()
	{
		$mapper = new DB_Mapper_ConceptList;
		$ctlg = Zend_Registry::get('translationLanguageGuiCurrent');
		$cls = $mapper->findAllWithDetails($ctlg->get_id());
		
		$options = array();
		$options[''] = $this->_translate->_('li0325000050');

		foreach ($cls as $cl)
		{
			$options[$cl['id']] = $cl['title'];
		}
		
		$select = new Zend_Form_Element_Select('concept_list_id');
		$select->setLabel($this->_translate->_('li0325000100'));
		$select->addMultiOptions($options);
		
		if (isset($this->_concept))
		{
			$select->setValue($this->_concept->get_concept_list_id());
		}
		
		$this->addElement($select);

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
	 * @return BDQ_Form_Concept
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
	 * @param DB_Model_Concept $cl
	 * @return BDQ_Form_Concept
	 */
	public function setConcept(DB_Model_Concept $c)
	{
		$this->_concept = $c;
		return $this;
	}
	
	/**
	 * @return DB_Model_Concept
	 */
	public function getConcept()
	{
		return $this->_concept;
	}
}