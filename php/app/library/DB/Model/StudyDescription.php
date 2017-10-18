<?php

/**
 * @package DB
 * @subpackage Model
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Model_StudyDescription {
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
	protected $_nesstar_id;
	
	/**
	 * @var string
	 */
	protected $_title;

	/**
	 * @var string
	 */
	protected $_year;
	
	/**
	 * @var string
	 */
	protected $_sample_procedure;

	/**
	 * @var string
	 */
	protected $_universe;

	/**
	 * @var string
	 */
	protected $_analysis_unit;

	/**
	 * @var string
	 */
	protected $_geographic_coverage;

	/**
	 * @var string
	 */
	protected $_abstract;

	/**
	 * @var string
	 */
	protected $_keywords;

	/**
	 * @var string
	 */
	protected $_collect_mode;

	/**
	 * @var string
	 */
	protected $_nation;
	
	/**
	 * @var int
	 */
	protected $_case_quantity;
	
	
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
	public function get_nesstar_id()
	{
		return $this->_nesstar_id;
	}
	
	/**
	 * @return void
	 */
	public function set_nesstar_id($value)
	{
		$this->_nesstar_id = $value;
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
	public function get_year()
	{
		return $this->_year;
	}

	/**
	 * @return void
	 */
	public function set_year($value)
	{
		$this->_year = $value;
	}

	/**
	 * @return string
	 */
	public function get_sample_procedure()
	{
		return $this->_sample_procedure;
	}

	/**
	 * @return void
	 */
	public function set_sample_procedure($value)
	{
		$this->_sample_procedure = $value;
	}

	/**
	 * @return string
	 */
	public function get_universe()
	{
		return $this->_universe;
	}

	/**
	 * @return void
	 */
	public function set_universe($value)
	{
		$this->_universe = $value;
	}

	/**
	 * @return string
	 */
	public function get_analysis_unit()
	{
		return $this->_analysis_unit;
	}

	/**
	 * @return void
	 */
	public function set_analysis_unit($value)
	{
		$this->_analysis_unit = $value;
	}

	/**
	 * @return string
	 */
	public function get_geographic_coverage()
	{
		return $this->_geographic_coverage;
	}

	/**
	 * @return void
	 */
	public function set_geographic_coverage($value)
	{
		$this->_geographic_coverage = $value;
	}

	/**
	 * @return string
	 */
	public function get_abstract()
	{
		return $this->_abstract;
	}

	/**
	 * @return void
	 */
	public function set_abstract($value)
	{
		$this->_abstract = $value;
	}

	/**
	 * @return string
	 */
	public function get_keywords()
	{
		return $this->_keywords;
	}

	/**
	 * @return void
	 */
	public function set_keywords($value)
	{
		$this->_keywords = $value;
	}

	/**
	 * @return string
	 */
	public function get_collect_mode()
	{
		return $this->_collect_mode;
	}

	/**
	 * @return void
	 */
	public function set_collect_mode($value)
	{
		$this->_collect_mode = $value;
	}

	/**
	 * @return string
	 */
	public function get_nation()
	{
		return $this->_nation;
	}
	
	/**
	 * @return void
	 */
	public function set_nation($value)
	{
		$this->_nation = $value;
	}
	
	/**
	 * @return int
	 */
	public function get_case_quantity()
	{
		return $this->_case_quantity;
	}
	
	/**
	 * @return void
	 */
	public function set_case_quantity($value)
	{
		$this->_case_quantity = $value;
	}
	
}