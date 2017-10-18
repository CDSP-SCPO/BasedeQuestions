<?php

/**
 * @package Solr
 * @category Search
 */

/**
 * Compiles a simple solr query into a question bank specific one.<br/>
 * The given query shall not contain the target field operator ":".<br/>
 * This class doesn't perform query validation which should occur on the client side.<br/>
 * The compiled query:
 * <ul>
 * <li>targets both composite (for example question and items) and atomic fields (questions, items, modalities, variable), to get matches in composite fields, and highlighting from atomic fields,</li>
 * <li>boosts matches in non stemmed fields,</li>
 * <li>works with negative clauses.</li>
 * </ul>
 * Relies on naming conventions in the solr schema. 
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/
 */
class Solr_BDQ_Search_SearchField {

	/**
	 * Indicates if the compiled query shall or shall not be matched (boolean NOT).
	 * @var boolean
	 */
	public $not = false;

	/**
	 * Indicates if the generated query is mandatory or optionnal.
	 * @var boolean
	 */
	public $required = true;

	/**
	 * Indicates which field(s) (question, modalities, variable label) are targeted.
	 * <ul>
	 * <li>SEARCH_QUESTION equals to 1 and target question and items</li>
	 * <li>SEARCH_MODALITIES equals to 2 and targets categorie code and label</li>
	 * <li>SEARCH_VARIABLE equals to 4 and targets variable name and label</li>
	 * </ul>
	 * Constants can be combined to target several fields at once.
	 * <code>
	 * $field->target = SEARCH_QUESTION | SEARCH_MODALITIES;
	 * </code>
	 * @see inc/constants.php, SEARCH_QUESTION
	 * @see inc/constants.php, SEARCH_MODALITIES
	 * @see inc/constants.php, SEARCH_VARIABLE
	 * @var int current target code
	 */
	protected $_code;

	/**
	 * Solr schema field's base name, without lang and analysis.
	 * @var string
	 */
	protected $_target;

	/**
	 * Raw query, as given to the constructor
	 * @var string
	 */
	protected $_query = '';

	/**
	 * Solr schema search lang code. It appears as the last characters from a field name.
	 * @var string
	 */
	protected $_searchLang;
	
	/**
	 * Solr schema field's full name, including lang and analysis codes.
	 * @var string
	 */
	protected $_fullName;
	
	/**
	 * Solr schema analysis code, which is a combo of Sw (stop words) and St (stemming).
	 * @var string
	 */
	protected $_analysis;

	/**
	 * @param string $query the search terms or the user query, which shall not contain the target field operator ":"
	 * @param string $searchLang a reference to the schema search lang code
	 * @param string $code an integer with bits 0, 1, 2 to 0 or 1
	 * @return BDQ_SearchField
	 */
	public function __construct($query, $code, $searchLang)
	{
		$query = trim($query);
		$this->_query = $query;
		$this->_code = $code;
		$this->_searchLang = strtoupper($searchLang);
		$this->_target = self::getTarget($code);
		$this->_analysis = BDQ_Settings_Client::getAnalysisCode();
		$this->_fullName = $this->_target . $this->_analysis . $this->_searchLang;
	}

	/**
	 * @return string Solr schema search lang code
	 */
	public function getSearchLang()
	{
		return $this->_searchLang;
	}

	/**
	 * @return string Current value of the uncompiled query
	 */
	public function getRawQuery()
	{
		return $this->_query;
	}

	/**
	 * @return string compiled query
	 */
	public function getQuery()
	{
		$query = '';
		$query = $this->_getBaseFieldQuery();
		$query = $this->_addHighlighting($query);
		$settings = BDQ_Settings_Client::getInstance();

		if ($settings->stemming)
		{
			$query = $this->_addExactMatchBoost($query);
		}

		if ($this->not)
		{
			$query = "-($query AND *:*)";
		}

		else if ( ! $this->required)
		{
			$query = "($query OR *:*)";
		}

		return $query;
	}

	/**
	 * Solr schema analysis code.
	 * @return string
	 */
	public function getAnalysis()
	{
		return $this->_analysis;
	}

	/**
	 * Solr schema field full name.
	 * @return string
	 */
	public function getName()
	{
		return $this->_fullName;
	}

	/**
	 * Solr schema base field name, without analysis or search lang code, given from an integer target value.
	 * @param int $code
	 * @return string
	 */
	static function getTarget($code)
	{
		$target = '';

		if (SEARCH_QUESTION & $code)
		{
			$target .= 'qi';
		}
		
		if (SEARCH_MODALITIES & $code)
		{
			$target .= ($target != '') ? 'AndM' : 'm';
		}
		
		if (SEARCH_VARIABLE & $code)
		{
			$target .= ($target != '') ? 'AndVl' : 'vl';
		}
		
		return $target; 
	}

	/**
	 * Target integer value from a solr schema field base name.
	 * @param string $target
	 * @return int
	 */
	static function getCode($target = NULL)
	{
		$code = 0;
		$code |= strpos($target, 'qi') !== false ? SEARCH_QUESTION : 0;
		$code |= stripos($target, 'm') !== false ? SEARCH_MODALITIES : 0;
		$code |= stripos($target, 'vl') !== false ? SEARCH_VARIABLE : 0;
		return $code;
	}

	/**
	 * A query targetting the right field and including a protection for negative clauses. 
	 * @return string
	 */
	protected function _getBaseFieldQuery()
	{
		$query = $this->_fullName . ':(';
		$query .= $this->_getQueryContent();
		$query .= ')';
		return $query; //the AND *:* allows negative clause which would fails otherwise
	}

	/**
	 * Eliminates accents, decodes the query, and cleans it to be compound words safe.
	 * @return string
	 */
	protected function _getQueryContent()
	{
		$query = utf8_decode(normalize($this->_query));
		return $query = $this->_handleCompound($query);
	}

	/**
	 * Clean the query to be compound words safe.
	 * @param string $query
	 * @return string
	 */
	protected function _handleCompound($query)
	{
		$pattern = '/(\S)\-(\S)/';
		$replace = '$1 $2';
		return preg_replace($pattern, $replace, $query);
	}

	/**
	 * Add a boost to matches occurring in a non stemmed field.
	 * @param string $query
	 * @return string
	 */
	protected function _addExactMatchBoost($query)
	{
		$baseQuery = $this->_getBaseFieldQuery();
		$field = $this->_target . str_replace('St', '', $this->_analysis) . $this->_searchLang;
		$content = $this->_getQueryContent();
		//According to the boolean law of absorption, the generated query is equivalent to the one given as input.
		//Fields added for highlighting are given a boost value of 0 so they don't mess the score up.
		return "$query AND ($baseQuery^0 OR $field:($content)^1)"; 
	}
	
	/**
	 * Targets atomic fields crafted for highlighting to get matches in those fields.
	 * @param string $query
	 * @return string
	 */
	protected function _addHighlighting($query)
	{
		$content = $this->_getQueryContent();
		$fieldSuffix = $this->_analysis . $this->_searchLang;
		$qHLField = 'q' . $fieldSuffix;
		$iHLField = 'iHL' . $fieldSuffix;
		$mHLField = 'mHL' . $fieldSuffix;
		$vHLField = 'vlHL' . $fieldSuffix;
		//According to the boolean law of absorption, the generated query is equivalent to the one given as input.
		//Fields added for highlighting are given a boost value of 0 so they don't mess the score up.
		switch ($this->_code):
		
			case SEARCH_QUESTION:
				return "$query AND ($query OR ($qHLField:($content)^0.001 AND $iHLField:($content)^0.001))"; 
			break;
			
			case SEARCH_MODALITIES:
				return "$query AND ($query OR ($mHLField:($content)^0.001))";
			break;
		
			case SEARCH_VARIABLE:
				return "$query AND ($query OR ($vHLField:($content)^0.001))";
			break;
			
			case SEARCH_QUESTION | SEARCH_MODALITIES | SEARCH_VARIABLE:
				return "$query AND ($query OR ($qHLField:($content)^0.001 AND $iHLField:($content)^0.001 AND $mHLField:($content)^0.001 AND $vHLField:($content)^0.001))";
			break;
			
			case SEARCH_QUESTION | SEARCH_MODALITIES:
				return "$query AND ($query OR ($qHLField:($content)^0.001 AND $iHLField:($content)^0.001 AND $mHLField:($content)^0.001))";
			break;
		
			case SEARCH_QUESTION | SEARCH_VARIABLE:
				return "$query AND ($query OR ($qHLField:($content)^0.001 AND $iHLField:($content)^0.001 AND $vHLField:($content)^0.001))";
			break;
			
			case SEARCH_MODALITIES | SEARCH_VARIABLE:
				return "$query AND ($query OR ($mHLField:($content)^0.001 AND $vHLField:($content)^0.001))";
			break;
		
		endswitch;

	}
	
}