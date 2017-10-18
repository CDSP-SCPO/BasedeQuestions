<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
abstract class BDQ_Locale_Form extends BDQ_Form
{
	/**
	 * @param array $translations
	 * @return BDQ_Form_Domain
	 */
	abstract public function setTranslations(array $translations);

	/**
	 * @return array
	 */
	abstract public function getTranslations();

	/**
	 * @var array
	 */
	protected $_translations;

	/**
	 * @var Zend_Translate
	 */
	protected $_translate;
	
	public function init()
	{
		parent::init();
		$this->_translate = Zend_Registry::get('translateAdmin');
	}

	/*
	 * 
	 * domains.id AS id,
	 * domains.concept_list_id AS concept_list_id,
	 * domains.title_translation_id AS title_translation_id,
	 * domains.description_translation_id AS description_translation_id,
	 * title_translation.translated_text AS title,
	 * title_translation.id AS title_translation_entry_id,
	 * description_translation.translated_text AS description,
	 * description_translation.id AS description_translation_entry_id,
	 * translation_language.id AS lang_id,
	 * translation_language.code AS lang_code,
	 * translation_language.label AS lang_label,
	 */
	/**
	 * @param string $fieldName
	 * @param int $languageId
	 * @param string $label
	 * @param string $type
	 * @param int $textAreaRows
	 * @param int $textAreaCols
	 * @return void
	 */
	protected function _getTranslatedField
	(
		$fieldName, 
		$languageId, 
		$type = 'Text'
		)
	{

		if ( !  in_array($type, array('Text', 'Textarea')))
		{
			throw new Exception('$type must be "Text" or "Textarea" ');
		}

		$value = '';
		$formFieldName = "t${fieldName}_$languageId";

		if ($this->_translations)
		{

			$formFieldName .= '_';
			$l = count($this->_translations);
			
			for($i = 0; $i < $l; $i++)
			{
				
				if ($this->_translations[$i]['lang_id'] == $languageId)
				{
					$value = (string) $this->_translations[$i][$fieldName];
					$formFieldName .= $this->_translations[$i]["${fieldName}_translation_entry_id"];
					break;
				}

			}

			if ($i == $l)
			{
				$formFieldName .= 'n';	
			}
			
		}
		
		$class = "Zend_Form_Element_$type";
		
		$tt = new $class($formFieldName);
		
		$tt->setValue(stripslashes($value));
		return $tt;
	}

}