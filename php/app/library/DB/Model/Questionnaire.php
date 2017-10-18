<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_Questionnaire {
	/**
	 * @var 
	 */
	protected $_id;

	/**
	 * @var 
	 */
	protected $_ddi_file_id;

	/**
	 * @var 
	 */
	protected $_title;

	/**
	 * @var 
	 */
	protected $_file_name;

	/**
	 * @var 
	 */
	protected $_created;

	/**
	 * @var 
	 */
	protected $_modified;

	/**
	 * @return 
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
	 * @return 
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
	 * @return 
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
	 * @return 
	 */
	public function get_file_name()
	{
		return $this->_file_name;
	}

	/**
	 * @return void
	 */
	public function set_file_name($value)
	{
		$this->_file_name = $value;
	}

	/**
	 * @return 
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
	 * @return 
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
?>
