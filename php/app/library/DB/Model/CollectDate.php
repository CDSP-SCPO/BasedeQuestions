<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_CollectDate {
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
	protected $_date;

	/**
	 * @var string
	 */
	protected $_event;

	/**
	 * @var string
	 */
	protected $_cycle;

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
	public function get_date()
	{
		return $this->_date;
	}

	/**
	 * @return void
	 */
	public function set_date($value)
	{
		$this->_date = $value;
	}

	/**
	 * @return string
	 */
	public function get_event()
	{
		return $this->_event;
	}

	/**
	 * @return void
	 */
	public function set_event($value)
	{
		$this->_event = $value;
	}

	/**
	 * @return string
	 */
	public function get_cycle()
	{
		return $this->_cycle;
	}

	/**
	 * @return void
	 */
	public function set_cycle($value)
	{
		$this->_cycle = $value;
	}

}
?>
