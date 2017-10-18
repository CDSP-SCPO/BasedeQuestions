<?php

/**
 * @package DB
 * @subpackage Model
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_NesstarServer {
	/**
	 * @var int
	 */
	protected $_id;

	/**
	 * @var string
	 */
	protected $_title;

	/**
	 * @var string
	 */
	protected $_ip;
	
	/**
	 * @var string
	 */
	protected $_port;
	
	/**
	 * @var string
	 */
	protected $_domain_name;
	
	/**
	 * @var string
	 */
	protected $_responsible;

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
	public function get_ip()
	{
		return $this->_ip;
	}

	/**
	 * @return void
	 */
	public function set_ip($value)
	{
		$this->_ip = $value;
	}
	
	/**
	 * @return string
	 */
	public function get_port()
	{
		return $this->_port;
	}

	/**
	 * @return void
	 */
	public function set_port($value)
	{
		$this->_port = $value;
	}
	
	/**
	 * @return string
	 */
	public function get_domain_name()
	{
		return $this->_domain_name;
	}

	/**
	 * @return void
	 */
	public function set_domain_name($value)
	{
		$this->_domain_name = $value;
	}
	
	/**
	 * @return string
	 */
	public function get_responsible()
	{
		return $this->_responsible;
	}

	/**
	 * @return void
	 */
	public function set_responsible($value)
	{
		$this->_responsible = $value;
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
