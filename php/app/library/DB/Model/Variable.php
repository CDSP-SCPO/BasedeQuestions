<?php

/**
 * @package DB
 * @subpackage Model
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_Variable {
	/**
	 * @var int
	 */
	protected $_id;

	/**
	 * @var int
	 */
	protected $_ddi_file_id;

	/**
	 * @var int
	 */
	protected $_concept_id;

	/**
	 * @var int
	 */
	protected $_variable_group_id;

	/**
	 * @var string
	 */
	protected $_nesstar_id;

	/**
	 * @var string
	 */
	protected $_name;

	/**
	 * @var string
	 */
	protected $_label;

	/**
	 * @var string
	 */
	protected $_notes;
	
	/**
	 * @var int
	 */
	protected $_valid;

	/**
	 * @var int
	 */
	protected $_invalid;

	/**
	 * @var string
	 */
	protected $_universe;
	
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
	public function get_ddi_file_id()
	{
		return $this->_ddi_file_id;
	}

	/**
	 * @return void
	 */
	public function set_ddi_file_id($value)
	{
		$this->_ddi_file_id = $value;
	}

	/**
	 * @return int
	 */
	public function get_concept_id()
	{
		return $this->_concept_id;
	}

	/**
	 * @return void
	 */
	public function set_concept_id($value)
	{
		$this->_concept_id = $value;
	}

	/**
	 * @return int
	 */
	public function get_variable_group_id()
	{
		return $this->_variable_group_id;
	}

	/**
	 * @return void
	 */
	public function set_variable_group_id($value)
	{
		$this->_variable_group_id = $value;
	}

	/**
	 * @return string
	 */
	public function get_nesstar_id()
	{
		return $this->_nesstar_id;
	}

	/**
	 * @return void
	 */
	public function set_nesstar_id($value)
	{
		$this->_nesstar_id = $value;
	}

	/**
	 * @return string
	 */
	public function get_name()
	{
		return $this->_name;
	}

	/**
	 * @return void
	 */
	public function set_name($value)
	{
		$this->_name = $value;
	}

	/**
	 * @return string
	 */
	public function get_label()
	{
		return $this->_label;
	}

	/**
	 * @return void
	 */
	public function set_label($value)
	{
		$this->_label = $value;
	}
	
	/**
	 * @return string
	 */
	public function get_notes()
	{
		return $this->_notes;
	}

	/**
	 * @return void
	 */
	public function set_notes($value)
	{
		$this->_notes = $value;
	}

	/**
	 * @return int
	 */
	public function get_valid()
	{
		return $this->_valid;
	}

	/**
	 * @return void
	 */
	public function set_valid($value)
	{
		$this->_valid = $value;
	}

	/**
	 * @return int
	 */
	public function get_invalid()
	{
		return $this->_invalid;
	}

	/**
	 * @return void
	 */
	public function set_invalid($value)
	{
		$this->_invalid = $value;
	}
	
	/**
	 * @return string
	 */
	public function get_universe()
	{
		return $this->_universe;
	}
		
	/**
	 * @return void
	 */
	public function set_universe($value)
	{
		$this->_universe = $value;
	}
}
