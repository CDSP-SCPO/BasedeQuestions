<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_DomainDdifile {
	/**
	 * @var int
	 */
	protected $_id;

	/**
	 * @var int
	 */
	protected $_domain_id;

	/**
	 * @var int
	 */
	protected $_ddi_file_id;

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
	public function get_domain_id()
	{
		return $this->_domain_id;
	}

	/**
	 * @return void
	 */
	public function set_domain_id($value)
	{
		$this->_domain_id = $value;
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

}
