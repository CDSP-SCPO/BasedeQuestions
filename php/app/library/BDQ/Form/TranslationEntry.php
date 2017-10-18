<?php

/**
 * @package Form
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class BDQ_Form_TranslationEntry extends BDQ_Form
{
	
	/**
	 * @var int
	 */
	protected $_translationId;
	
	/**
	 * @var int
	 */
	protected $_languageId;
	
	/**
	 * @param int $translationId
	 * @param int $languageId
	 * @param array $options
	 * @return BDQ_Form_TranslationEntry
	 */
	public function __construct($translationId, $languageId, $options = null)
	{
		parent::__construct($options);
		$this->init($translationId, $languageId);
	}
	
	/**
	 * @see Zend_Form#init()
	 * @param string $token
	 */
	public function init($translationId, $languageId)
	{
		$this->_translationId = $translationId;
		$this->_languageId = $languageId;
		$this->setMethod('post');
		$this->_addTranslationId();
		$this->_addTranslationLanguageId();
		$this->_addTranslatedText();
	}
	
	protected function _addLanguageId()
	{
		$lid = new Zend_Form_Element_Hidden('language_id');
		$lid->setValue($this->_languageId);
		$lid->removeDecorator('Errors');
		$lid->removeDecorator('Description');
		$lid->removeDecorator('HtmlTag');
		$lid->removeDecorator('Label');
		$this->addElement($lid);
	}
	
	protected function _addTranslatedText()
	{
		$userName = new Zend_Form_Element_Text('translated_text');
		$userName->setRequired(true);
		$this->addElement($userName);
	}
	
}