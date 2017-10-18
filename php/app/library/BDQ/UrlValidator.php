<?php
/**
 * An URL validator for the administration form
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 * @package BDQ
 */
class BDQ_UrlValidator extends Zend_Validate_Abstract
{
	/**
	 * @var string
	 */
	const INVALID_URL = 'invalidUrl';

	/**
	 * @var array
	 */
	protected $_messageTemplates = array(
		self::INVALID_URL => "'%value%' is not a valid URL.",
	);
	
	/**
	 * @param string
	 */
	public function isValid($value)
	{
		$valueString = (string) $value;
		$this->_setValue($valueString);

		if ( ! Zend_Uri::check($value))
		{
			$this->_error(self::INVALID_URL);
			return false;
		}

		return true;
	}

}