<?php

/**
 * @package Solr
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
abstract class Solr_Request {

	/**
	 * @var string
	 */
	protected $_date;

	/**
	 * @return Solr_Request
	 */
	public function __construct()
	{
		$this->_setDate(date('Y/m/d H:i:s'));
	}
		
	/**
	 * @return mixed
	 */
	abstract public function send();

	/**
	 * @return string
	 */
	public function getDate()
	{
		return $this->_date;
	}

	/**
	 * @return void
	 */
	protected function _setDate($value)
	{
		$this->_date = $value;
	}

}