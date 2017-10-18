<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_FundingAgency {
	/**
	 * @var int
	 */
	protected $_id;

	/**
	 * @var int
	 */
	protected $_ddi_file_id;

	/**
	 * @var string
	 */
	protected $_title;

	/**
	 * @var string
	 */
	protected $_abbreviation;

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
	 * @return string
	 */
	public function get_title()
	{
		return $this->_title;
	}

	/**
	 * @return void
	 */
	public function set_title($value)
	{
		$this->_title = $value;
	}

	/**
	 * @return string
	 */
	public function get_abbreviation()
	{
		return $this->_abbreviation;
	}

	/**
	 * @return void
	 */
	public function set_abbreviation($value)
	{
		$this->_abbreviation = $value;
	}

}
