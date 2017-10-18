<?php

/**
 * @package DB
 * @subpackage Model
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_Concept {
	/**
	 * @var int
	 */
	protected $_id;

	/**
	 * @var int
	 */
	protected $_concept_list_id;

	/**
	 * @var int
	 */
	protected $_title_translation_id;

	/**
	 * @var int
	 */
	protected $_position;

	/**
	 * @var string
	 */
	protected $_created;
	
	/**
	 * @var string
	 */
	protected $_modified;
	
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
	public function get_concept_list_id()
	{
		return $this->_concept_list_id;
	}

	/**
	 * @return void
	 */
	public function set_concept_list_id($value)
	{
		$this->_concept_list_id = $value;
	}

	/**
	 * @return int
	 */
	public function get_title_translation_id()
	{
		return $this->_title_translation_id;
	}

	/**
	 * @return void
	 */
	public function set_title_translation_id($value)
	{
		$this->_title_translation_id = $value;
	}
	
	/**
	 * @return int
	 */
	public function get_position()
	{
		return $this->_position;
	}

	/**
	 * @return void
	 */
	public function set_position($value)
	{
		$this->_position = $value;
	}

	/**
	 * @return string
	 */
	public function get_created()
	{
		return $this->_created;
	}

	/**
	 * @return void
	 */
	public function set_created($value)
	{
		$this->_created = $value;
	}

	/**
	 * @return string
	 */
	public function get_modified()
	{
		return $this->_modified;
	}

	/**
	 * @return void
	 */
	public function set_modified($value)
	{
		$this->_modified = $value;
	}

}
