<?php

/**
 * @package DB
 * @subpackage Model
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_TranslationLanguage {
	/**
	 * @var int
	 */
	protected $_id;

	/**
	 * @var string
	 */
	protected $_code;

	/**
	 * @var string
	 */
	protected $_code_solr;
	
	/**
	 * @var string
	 */
	protected $_label_translation_id;

	/**
	 * @var int
	 */
	protected $_enabled_gui;
	
	/**
	 * @var int
	 */
	protected $_enabled_solr;
	
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
	 * @return string
	 */
	public function get_code()
	{
		return $this->_code;
	}

	/**
	 * @return void
	 */
	public function set_code($value)
	{
		$this->_code = $value;
	}

	/**
	 * @return string
	 */
	public function get_code_solr()
	{
		return $this->_code_solr;
	}

	/**
	 * @return void
	 */
	public function set_code_solr($value)
	{
		$this->_code_solr = $value;
	}
	
	/**
	 * @return string
	 */
	public function get_label_translation_id()
	{
		return $this->_label;
	}

	/**
	 * @return void
	 */
	public function set_label_translation_id($value)
	{
		$this->_label = $value;
	}

	/**
	 * @return int
	 */
	public function get_enabled_gui()
	{
		return $this->_enabled_gui;
	}
	
	/**
	 * @return void
	 */
	public function set_enabled_gui($value)
	{
		$this->_enabled_gui = $value;
	}
	
	/**
	 * @return int
	 */
	public function get_enabled_solr()
	{
		return $this->_enabled_solr;
	}
	
	/**
	 * @return void
	 */
	public function set_enabled_solr($value)
	{
		$this->_enabled_solr = $value;
	}
	
}
