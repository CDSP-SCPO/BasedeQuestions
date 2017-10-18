<?php

/**
 * @package DB
 * @subpackage Model
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_Question {
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
	protected $_solr_id;

	/**
	 * @var string
	 */
	protected $_litteral;

	/**
	 * @var string
	 */
	protected $_item;

	/**
	 * @var string
	 */
	protected $_pre_question_text;

	/**
	 * @var string
	 */
	protected $_post_question_text;

	/**
	 * @var string
	 */
	protected $_interviewer_instructions;

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
	public function get_solr_id()
	{
		return $this->_solr_id;
	}

	/**
	 * @return void
	 */
	public function set_solr_id($value)
	{
		$this->_solr_id = $value;
	}

	/**
	 * @return string
	 */
	public function get_litteral()
	{
		return $this->_litteral;
	}

	/**
	 * @return void
	 */
	public function set_litteral($value)
	{
		$this->_litteral = $value;
	}

	/**
	 * @return string
	 */
	public function get_item()
	{
		return $this->_item;
	}

	/**
	 * @return void
	 */
	public function set_item($value)
	{
		$this->_item = $value;
	}
	
	/**
	 * @return string
	 */
	public function get_pre_question_text()
	{
		return $this->_pre_question_text;
	}
	
	/**
	 * @return void
	 */
	public function set_pre_question_text($value)
	{
		$this->_pre_question_text = $value;
	}
	

	/**
	 * @return string
	 */
	public function get_post_question_text()
	{
		return $this->_post_question_text;
	}

	/**
	 * @return void
	 */
	public function set_post_question_text($value)
	{
		$this->_post_question_text = $value;
	}

	/**
	 * @return string
	 */
	public function get_interviewer_instructions()
	{
		return $this->_interviewer_instructions;
	}

	/**
	 * @return void
	 */
	public function set_interviewer_instructions($value)
	{
		$this->_interviewer_instructions = $value;
	}
	
}
