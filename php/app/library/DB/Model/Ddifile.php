<?php

/**
 * @package DB
 * @subpackage Model
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_DdiFile {
	/**
	 * @var int
	 */
	protected $_id;

	/**
	 * @var int
	 */
	protected $_translation_language_id;

	/**
	 * @var int
	 */
	protected $_nesstar_server_id;

	/**
	 * @var int
	 */
	protected $_concept_list_id;

	/**
	 * @var int
	 */
	protected $_study_serie_id;
	
	/**
	 * @var string
	 */
	protected $_file_name;

	/**
	 * @var string
	 */
	protected $_questionnaire_url;
	
	/**
	 * @var string
	 */
	protected $_questionnaire_file_name;
	
	/**
	 * @var string
	 */
	protected $_alternate_description_url;
	
	/**
	 * @var int
	 */
	protected $_multiple_item_parsed;
	
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
	 * @return int
	 */
	public function get_translation_language_id()
	{
		return $this->_translation_language_id;
	}

	/**
	 * @return void
	 */
	public function set_translation_language_id($value)
	{
		$this->_translation_language_id = $value;
	}

	/**
	 * @return string
	 */
	public function get_questionnaire_url()
	{
		return $this->_questionnaire_url;
	}
	
	/**
	 * @return void
	 */
	public function set_questionnaire_url($value)
	{
		$this->_questionnaire_url = $value;
	}

	/**
	 * @return string
	 */
	public function get_questionnaire_file_name()
	{
		return $this->_questionnaire_file_name;
	}

	/**
	 * @return void
	 */
	public function set_questionnaire_file_name($value)
	{
		$this->_questionnaire_file_name = $value;
	}

	/**
	 * @return string
	 */
	public function get_alternate_description_url()
	{
		return $this->_alternate_description_url;
	}
	
	/**
	 * @return void
	 */
	public function set_alternate_description_url($value)
	{
		$this->_alternate_description_url = $value;
	}
	
	/**
	 * @return int
	 */
	public function get_nesstar_server_id()
	{
		return $this->_nesstar_server_id;
	}

	/**
	 * @return void
	 */
	public function set_nesstar_server_id($value)
	{
		$this->_nesstar_server_id = $value;
	}

	/**
	 * @return int
	 */
	public function get_concept_list_id()
	{
		return $this->_concept_list_id;
	}

	/**
	 * @return void
	 */
	public function set_concept_list_id($value)
	{
		$this->_concept_list_id = $value;
	}
	
	/**
	 * @return int
	 */
	public function get_study_serie_id()
	{
		return $this->_study_serie_id;
	}

	/**
	 * @return void
	 */
	public function set_study_serie_id($value)
	{
		$this->_study_serie_id = $value;
	}
	

	/**
	 * @return string
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
	 * @return int
	 */
	public function get_multiple_item_parsed()
	{
		return $this->_multiple_item_parsed;
	}

	/**
	 * @return void
	 */
	public function set_multiple_item_parsed($value)
	{
		$this->_multiple_item_parsed = $value;
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