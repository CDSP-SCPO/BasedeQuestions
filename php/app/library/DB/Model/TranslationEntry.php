<?php

/**
 * @package DB
 * @subpackage Model
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_TranslationEntry {
	/**
	 * @var int
	 */
	protected $_id;

	/**
	 * @var int
	 */
	protected $_translation_id;

	/**
	 * @var int
	 */
	protected $_translation_language_id;

	/**
	 * @var string
	 */
	protected $_translated_text;

	/**
	 * @return int
	 */
	public function get_id()
	{
		return $this->_id;
	}

	/**
	 * @return void
	 */
	public function set_id($value)
	{
		$this->_id = $value;
	}

	/**
	 * @return int
	 */
	public function get_translation_id()
	{
		return $this->_translation_id;
	}

	/**
	 * @return void
	 */
	public function set_translation_id($value)
	{
		$this->_translation_id = $value;
	}

	/**
	 * @return int
	 */
	public function get_translation_language_id()
	{
		return $this->_translation_language_id;
	}

	/**
	 * @return void
	 */
	public function set_translation_language_id($value)
	{
		$this->_translation_language_id = $value;
	}

	/**
	 * @return string
	 */
	public function get_translated_text()
	{
		return $this->_translated_text;
	}

	/**
	 * @return void
	 */
	public function set_translated_text($value)
	{
		$this->_translated_text = $value;
	}

}
