<?php

/**
 * Add two methods common to the administration forms.
 * 
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 * @package BDQ
 */
class BDQ_Form extends Zend_Form
{
	
	protected function _getTitleValidators()
	{
		$validators = array();
		$validator = new Zend_Validate_StringLength;
		$validator->setMin(2);
		$validator->setMax(255);
		$validators[] = $validator;
		$validator = new Zend_Validate_NotEmpty(
			array(
				Zend_Validate_NotEmpty::ALL
	    	)
		);
		$validators[] = $validator;
		return $validators;
	}
	
	/**
	 * @param string $formFieldName
	 * @param string $baseDataBaseFieldName the string between get and _id in the getter method, ex : get_id : '', get_translation_id : translation
	 * @param string $baseAttributeName the Zend_Form attribute containing the DB_Model_* object full name, ex : domain
	 * @return void
	 */
	protected function _addHiddenId($formFieldName, $baseDataBaseFieldName, $baseAttributeName)
	{
		
		$baseAttributeName = "_$baseAttributeName";
		
		if ($baseDataBaseFieldName != '')
		{
			$baseDataBaseFieldName = "${baseDataBaseFieldName}_";
		}
		
		$id = new Zend_Form_Element_Hidden($formFieldName);
		
		if ($this->$baseAttributeName !== NULL)
		{	
			$methodName = "get_${baseDataBaseFieldName}id";
			$id->setValue($this->$baseAttributeName->$methodName());
		}
		
		$id->removeDecorator('HtmlTag');
		$id->removeDecorator('Label');		
		$this->addElement($id);
	}

}