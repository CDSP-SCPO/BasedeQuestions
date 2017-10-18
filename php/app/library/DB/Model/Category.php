<?php

/**
 * @package DB
 * @subpackage Model
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_Category {
	/**
	 * @var int
	 */
	protected $_id;

	/**
	 * @var int
	 */
	protected $_variable_id;

	/**
	 * @var string
	 */
	protected $_number;
	
	/**
	 * @var string
	 */
	protected $_missing;

	/**
	 * @var string
	 */
	protected $_label;

	/**
	 * @var string
	 */
	protected $_stats;

	/**
	 * @var string
	 */
	protected $_type;

	/**
	 * @var int
	 */
	protected $_value;
	
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
	public function get_missing()
	{
		return $this->_missing;
	}

	/**
	 * @return void
	 */
	public function set_missing($value)
	{
		$this->_missing = $value;
	}

	/**
	 * @return int
	 */
	public function get_variable_id()
	{
		return $this->_variable_id;
	}

	/**
	 * @return void
	 */
	public function set_variable_id($value)
	{
		$this->_variable_id = $value;
	}

	/**
	 * @return string
	 */
	public function get_number()
	{
		return $this->_number;
	}

	/**
	 * @return void
	 */
	public function set_number($value)
	{
		$this->_number = $value;
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
	public function get_stats()
	{
		return $this->_stats;
	}

	/**
	 * @return void
	 */
	public function set_stats($value)
	{
		$this->_stats = $value;
	}

	/**
	 * @return string
	 */
	public function get_type()
	{
		return $this->_type;
	}

	/**
	 * @return void
	 */
	public function set_type($value)
	{
		$this->_type = $value;
	}
	
	/**
	 * @return string
	 */
	public function get_value()
	{
		return $this->_value;
	}

	/**
	 * @return void
	 */
	public function set_value($value)
	{
		$this->_value = $value;
	}

}