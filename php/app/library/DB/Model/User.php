<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_User {
	
	/**
	 * @var int
	 */
	static $salt = '1984';
	
	/**
	 * @var int
	 */
	protected $_id;

	/**
	 * @var string
	 */
	protected $_user_name;

	/**
	 * @var string
	 */
	protected $_password;

	/**
	 * @var string
	 */
	protected $_real_name;

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
	public function get_user_name()
	{
		return $this->_user_name;
	}

	/**
	 * @return void
	 */
	public function set_user_name($value)
	{
		$this->_user_name = $value;
	}

	/**
	 * @return string
	 */
	public function get_password()
	{
		return $this->_password;
	}

	/**
	 * @return void
	 */
	public function set_password($value)
	{
		$this->_password = $value;
	}

	/**
	 * @return string
	 */
	public function get_real_name()
	{
		return $this->_real_name;
	}

	/**
	 * @return void
	 */
	public function set_real_name($value)
	{
		$this->_real_name = $value;
	}

}
