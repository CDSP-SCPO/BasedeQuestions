<?php

/**
 * @package Solr
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_Select extends Solr_Request {
	
	/**
	 * @var Solr_BDQ_Search_Search
	 */
	public $search;

	/**
	 * @var string
	 */
	protected $_requestHandler = 'select';

	/**
	 * @var string
	 */
	protected $_q;
	
	/**
	 * @var string
	 */
	protected $_df;
	
	/**
	 * @var string
	 */
	protected $_qOp = 'AND';

	/**
	 * @var int
	 */
	protected $_start = 0;

	/**
	 * @var int
	 */
	protected $_rows = 10;

	/**
	 * @var array
	 */
	protected $_fl = array();

	/**
	 * @var array
	 */
	protected $_sort;

	/**
	 * @var array
	 */
	protected $_fqs;

	/**
	 * @var boolean
	 */
	protected $_hl;

	/**
	 * @var array
	 */
	protected $_hlFl;

	/**
	 * @var boolean
	 */
	protected $_hlRequireFieldMatch;
	
	/**
	 * @var boolean
	 */
	protected $_hlMergeContiguous;
	
	/**
	 * @var boolean
	 */
	protected $_hlUsePhraseHighlighter;

	/**
	 * @var boolean
	 */
	protected $_hlHighlightMultiTerm;

	/**
	 * @var int
	 */
	protected $_hlFragSize;

	/**
	 * @var string
	 */
	protected static $_hlSimplePre = '<b>';

	/**
	 * @var string
	 */
	protected static $_hlSimplePost = '</b>';

	/**
	 * @var int
	 */
	protected $_hlSnippets;
	
	/**
	 * @var boolean
	 */
	protected $_facet;

	/**
	 * @var array
	 */
	protected $_facetFields;

	/**
	 * @var string
	 */
	protected $_facetLimit;

	/**
	 * @var string
	 */
	protected $_facetMethod;

	/**
	 * @var string
	 */
	protected $_facetMincount;

	/**
	 * @var string
	 */
	protected $_facetMissing;

	/**
	 * @var string
	 */
	protected $_facetOffset;

	/**
	 * @var string
	 */
	protected $_facetPrefix;
	
	/**
	 * @var string
	 */
	protected $_facetSort = 'count';
	
	/**
	 * @var array
	 */
	protected $_facetFieldsSorts = array();

	/**
	 * @var array
	 */
	protected $_facetQueries = array();
	
	/**
	 * @var boolean
	 */
	protected $_mlt;

	/**
	 * @var string
	 */
	protected $_mltBoost;

	/**
	 * @var int
	 */
	protected $_mltCount;

	/**
	 * @var array
	 */
	protected $_mltFl;

	/**
	 * @var string
	 */
	protected $_mltMatchInclude;

	/**
	 * @var string
	 */
	protected $_mltMatchOffset;

	/**
	 * @var string
	 */
	protected $_mltMaxntp;

	/**
	 * @var string
	 */
	protected $_mltMaxqt;

	/**
	 * @var int
	 */
	protected $_mltMaxwl;

	/**
	 * @var int
	 */
	protected $_mltMindf;

	/**
	 * @var int
	 */
	protected $_mltMintf;

	/**
	 * @var int
	 */
	protected $_mltMinwl;

	/**
	 * @var string
	 */
	protected $_mltQf;

	/**
	 * @var string
	 */
	protected $_mltInterestingTerms;
	
	/**
	 * @var boolean
	 */
	protected $_omitHeader;
	
	/**
	 * @var boolean
	 */
	protected $_debugQuery;

	/**
	 * @var boolean
	 */
	protected $_spellcheck;

	/**
	 * @var string
	 */
	protected $_spellcheckBuild;

	/**
	 * @var int
	 */
	protected $_spellcheckCount;

	/**
	 * @var string
	 */
	protected $_spellcheckCollate;

	/**
	 * @var string
	 */
	protected $_spellcheckDictionary;

	/**
	 * @var boolean
	 */
	protected $_spellcheckExtendedResults;

	/**
	 * @var boolean
	 */
	protected $_spellcheckOnlyMorePopular;

	/**
	 * @var string
	 */
	protected $_spellcheckQ;

	/**
	 * @return Solr_Select
	 */
	public function __construct($query = '')
	{
		parent::__construct();
		$this->_q = $query;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		$query = $this->_getWtParameter();
		$query .= $this->_getOmitHeaderParameter();
		$query .= $this->_getQParameter();
		$query .= $this->_getDfParameter();
		$query .= $this->_getQOpParameter();
		$query .= $this->_getStartParameter();
		$query .= $this->_getRowsParameter();
		$query .= $this->_getFlParameter();
		$query .= $this->_getSortParameter();
		$query .= $this->_getFqsParameter();

		if ($this->_facet)
		{
			$query .= $this->_getFacetParameter();
			$query .= $this->_getFacetFieldsParameter();
			$query .= $this->_getFacetLimitParameter();
			$query .= $this->_getFacetMincountParameter();
			$query .= $this->_getFacetPrefixParameter();
			$query .= $this->_getFacetSortParameter();	
			$query .= $this->_getFacetFieldsSortsParameter();
			$query .= $this->_getFacetQueriesParameter();
		}

		if ($this->_mlt)
		{
			$query .= $this->_getMltParameter();
			$query .= $this->_getMltflParameter();
			$query .= $this->_getMltCountParameter();
			$query .= $this->_getMltMintfParameter();
			$query .= $this->_getMltMindfParameter();
			$query .= $this->_getMltMinwlParameter();
			$query .= $this->_getMltMaxwlParameter();
			$query .= $this->_getMltInterestingTermsParameter();
		}

		if ($this->_spellcheck)
		{
			$query .= $this->_getSpellcheckParameter();
			$query .= $this->_getSpellcheckBuildParameter();
			$query .= $this->_getSpellcheckCollateParameter();
			$query .= $this->_getSpellcheckCountParameter();
			$query .= $this->_getSpellcheckDictionaryParameter();
			$query .= $this->_getSpellcheckExtendedResultsParameter();
			$query .= $this->_getSpellcheckOnlyMorePopularParameter();
			$query .= $this->_getSpellcheckQParameter();
		}

		$query .= $this->_getDebugQueryParameter();

		if ($this->_hl)
		{
			$query .= $this->_getHlParameter();
			$query .= $this->_getHlFlParameter();
			$query .= $this->_getHlFragSizeParameter();
			$query .= $this->_getHlSnippetsParameter();
			$query .= $this->_getHlUsePhraseHighlighterParameter();
			$query .= $this->_getHlHighlightMultiTermParameter();
			$query .= $this->_getHlRequireFieldMatchParameter();
			$query .= $this->_getHlSimplePreParameter();
			$query .= $this->_getHlSimplePostParameter();
			$query .= $this->_getHlMergeContiguousParameter();
		}
		
		return $query;
	}

	/**
	 * @return string
	 */
	public function getRequestHandler()
	{
		return $this->_requestHandler;
	}

	/**
	 * @return void
	 */
	public function setRequestHandler($value)
	{
		$this->_requestHandler = $value;
	}
	
	/**
	 * @return mixed
	 */
	public function send()
	{
		$client = Solr_Client::getInstance();
		return $client->send($this);
	}

	/**
	 * @return string
	 */
	public function getQ()
	{
		return $this->_q;
	}

	/**
	 * @return void
	 */
	public function setQ($value)
	{
		$this->_q = $value;
	}
	
	/**
	 * @return string
	 */
	public function getDf()
	{
		return $this->_df;
	}

	/**
	 * @return void
	 */
	public function setDf($value)
	{
		$this->_df = $value;
	}

	/**
	 * @return string
	 */
	public function getQOp()
	{
		return $this->_qOp;
	}
	
	/**
	 * @return void
	 */
	public function setQOp($value)
	{
		$this->_qOp = $value;
	}
	
	/**
	 * @return int
	 */
	public function getStart()
	{
		return $this->_start;
	}

	/**
	 * @return void
	 */
	public function setStart($value)
	{
		$this->_start = $value;
	}

	/**
	 * @return int
	 */
	public function getRows()
	{
		return $this->_rows;
	}
	
	/**
	 * @return void
	 */
	public function setRows($value)
	{
		$this->_rows = $value;
	}

	/**
	 * @return array
	 */
	public function getFl()
	{
		return $this->_fl;
	}

	/**
	 * @return void
	 */
	public function setFl($value)
	{
		$this->_fl = $value;
	}
	
	/**
	 * @return array
	 */
	public function getSort()
	{
		return $this->_sort;
	}
	
	/**
	 * @return void
	 */
	public function setSort($value)
	{
		$this->_sort = $value;
	}
	
	/**
	 * @return array
	 */
	public function getFqs()
	{
		return $this->_fqs;
	}

	/**
	 * @return void
	 */
	public function setFqs($value)
	{
		$this->_fqs = $value;
	}
	
	/**
	 * @return boolean
	 */
	public function getHl()
	{
		return $this->_hl;
	}

	/**
	 * @return void
	 */
	public function setHl($value)
	{
		$this->_hl = $value;
	}

	/**
	 * @return boolean
	 */
	public function getHlRequireFieldMatch()
	{
		return $this->_hlRequireFieldMatch;
	}

	/**
	 * @return void
	 */
	public function setHlRequireFieldMatch($value)
	{
		$this->_hlRequireFieldMatch = $value;
	}
	
	/**
	 * @return boolean
	 */
	public function getHlMergeContiguous()
	{
		return $this->_hlMergeContiguous;
	}

	/**
	 * @return void
	 */
	public function setHlMergeContiguous($value)
	{
		$this->_hlMergeContiguous = $value;
	}

	/**
	 * @return boolean
	 */
	public function getHlUsePhraseHighlighter()
	{
		return $this->_hlUsePhraseHighlighter;
	}

	/**
	 * @return void
	 */
	public function setHlUsePhraseHighlighter($value)
	{
		$this->_hlUsePhraseHighlighter = $value;
	}

	/**
	 * @return boolean
	 */
	public function getHlHighlightMultiTerm()
	{
		return $this->_hlHighlightMultiTerm;
	}

	/**
	 * @return void
	 */
	public function setHlHighlightMultiTerm($value)
	{
		$this->_hlHighlightMultiTerm = $value;
	}

	/**
	 * @return int
	 */
	public function getHlSnippets()
	{
		return $this->_hlSnippets;
	}
	
	/**
	 * @return void
	 */
	public function setHlSnippets($value)
	{
		$this->_hlSnippets = $value;
	}

	/**
	 * @return array
	 */
	public function getHlFl()
	{
		return $this->_hlFl;
	}

	/**
	 * @return void
	 */
	public function setHlFl($value)
	{
		$this->_hlFl = $value;
	}

	/**
	 * @return int
	 */
	public function getHlFragSize()
	{
		return $this->_hlFragSize;
	}

	/**
	 * @return void
	 */
	public function setHlFragSize($value)
	{
		$this->_hlFragSize = $value;
	}

	/**
	 * @return string
	 */
	public static function getHlSimplePre()
	{
		return self::$_hlSimplePre;
	}

	/**
	 * @return void
	 */
	public static function setHlSimplePre($value)
	{
		self::$_hlSimplePre = $value;
	}

	/**
	 * @return string
	 */
	public function getHlSimplePost()
	{
		return self::$_hlSimplePost;
	}

	/**
	 * @return void
	 */
	public function setHlSimplePost($value)
	{
		self::$_hlSimplePost = $value;
	}

	/**
	 * @return boolean
	 */
	public function getFacet()
	{
		return $this->_facet;
	}

	/**
	 * @return void
	 */
	public function setFacet($value)
	{
		$this->_facet = $value;
	}
	
	/**
	 * @return string
	 */
	public function getFacetSort()
	{
		return $this->_facetSort;
	}
	
	/**
	 * @return void
	 */
	public function setFacetSort($value)
	{
		$this->_facetSort = $value;
	}
	
	/**
	 * @param string $fieldName
	 * @param string $order
	 * @return void
	 */
	public function addFacetFieldSort($fieldName, $order)
	{
		$this->_facetFieldsSorts[$fieldName] = $order;
	}
	
	/**
	 * @return array
	 */
	public function getFacetFieldsSorts()
	{
		return $this->_facetFieldsSorts;
	}

	/**
	 * @return array
	 */
	public function getFacetFields()
	{
		return $this->_facetFields;
	}

	/**
	 * @return void
	 */
	public function setFacetFields($value)
	{
		$this->_facetFields = $value;
	}

	/**
	 * @return string
	 */
	public function getFacetMincount()
	{
		return $this->_facetMincount;
	}

	/**
	 * @return void
	 */
	public function setFacetMincount($value)
	{
		$this->_facetMincount = $value;
	}

	/**
	 * @return string
	 */
	public function getFacetLimit()
	{
		return $this->_facetLimit;
	}

	/**
	 * @return void
	 */
	public function setFacetLimit($value)
	{
		$this->_facetLimit = $value;
	}

	/**
	 * @return string
	 */
	public function getFacetPrefix()
	{
		return $this->_facetPrefix;
	}

	/**
	 * @return void
	 */
	public function setFacetPrefix($value)
	{
		$this->_facetPrefix = $value;
	}
	
	/**
	 * 
	 * @param unknown_type $value
	 * @return unknown_type
	 */
	public function setFacetQueries($value)
	{
		$this->_facetQueries = $value;
	}
	
	/**
	 * @return boolean
	 */
	public function getMlt()
	{
		return $this->_mlt;
	}

	/**
	 * @return void
	 */
	public function setMlt($value)
	{
		$this->_mlt = $value;
	}
	
	/**
	 * @return int
	 */
	public function getMltCount()
	{
		return $this->_mltCount;
	}
	
	/**
	 * @return void
	 */
	public function setMltCount($value)
	{
		$this->_mltCount = $value;
	}

	/**
	 * @return array
	 */
	public function getMltFl()
	{
		return $this->_mltFl;
	}

	/**
	 * @return array
	 */
	public function setMltFl($value)
	{
		$this->_mltFl = $value;
	}

	/**
	 * @return int
	 */
	public function getMltMintf()
	{
		return $this->_mltMintf;
	}

	/**
	 * @return void
	 */
	public function setMltMintf($value)
	{
		$this->_mltMintf = $value;
	}

	/**
	 * @return int
	 */
	public function getMltMindf()
	{
		return $this->_mltMindf;
	}

	/**
	 * @return void
	 */
	public function setMltMindf($value)
	{
		$this->_mltMindf = $value;
	}

	/**
	 * @return int
	 */
	public function getMltMinwl()
	{
		return $this->_mltMinwl;
	}

	/**
	 * @return void
	 */
	public function setMltMinwl($value)
	{
		$this->_mltMinwl = $value;
	}

	/**
	 * @return string
	 */
	public function getMltMaxwl()
	{
		return $this->_mltMaxwl;
	}

	/**
	 * @return void
	 */
	public function setMltMaxwl($value)
	{
		$this->_mltMaxwl = $value;
	}

	/**
	 * @return string
	 */
	public function getMltInterestingTerms()
	{
		return $this->_mltInterestingTerms;
	}

	/**
	 * @return void
	 */
	public function setMltInterestingTerms($value)
	{
		$this->_mltInterestingTerms = $value;
	}

	/**
	 * @return boolean
	 */
	public function getOmitHeader()
	{
		return $this->_omitHeader;
	}
	
	/**
	 * @param boolean $value
	 * @return void
	 */
	public function setOmitHeader($value)
	{
		$this->_omitHeader = $value;
	}
	
	/**
	 * @return boolean
	 */
	public function getSpellcheck()
	{
		return $this->_spellcheck;
	}

	/**
	 * @return void
	 */
	public function setSpellcheck($value)
	{
		$this->_spellcheck = $value;
	}

	/**
	 * @return string
	 */
	public function getSpellcheckBuild()
	{
		return $this->_spellcheckBuild;
	}

	/**
	 * @return void
	 */
	public function setSpellcheckBuild($value)
	{
		$this->_spellcheckBuild = $value;
	}

	/**
	 * @return int
	 */
	public function getSpellcheckCount()
	{
		return $this->_spellcheckCount;
	}

	/**
	 * @return void
	 */
	public function setSpellcheckCount($value)
	{
		$this->_spellcheckCount = $value;
	}

	/**
	 * @return string
	 */
	public function getSpellcheckCollate()
	{
		return $this->_spellcheckCollate;
	}

	/**
	 * @return void
	 */
	public function setSpellcheckCollate($value)
	{
		$this->_spellcheckCollate = $value;
	}

	/**
	 * @return string
	 */
	public function getSpellcheckDictionary()
	{
		return $this->_spellcheckDictionary;
	}

	/**
	 * @return void
	 */
	public function setSpellcheckDictionary($value)
	{
		$this->_spellcheckDictionary = $value;
	}

	/**
	 * @return boolean
	 */
	public function getSpellcheckExtendedResults()
	{
		return $this->_spellcheckExtendedResults;
	}

	/**
	 * @return void
	 */
	public function setSpellcheckExtendedResults($value)
	{
		$this->_spellcheckExtendedResults = $value;
	}

	/**
	 * @return boolean
	 */
	public function getSpellcheckOnlyMorePopular()
	{
		return $this->_spellcheckOnlyMorePopular;
	}

	/**
	 * @return void
	 */
	public function setSpellcheckOnlyMorePopular($value)
	{
		$this->_spellcheckOnlyMorePopular = $value;
	}

	/**
	 * @return string
	 */
	public function getSpellcheckQ()
	{
		return $this->_spellcheckQ;
	}

	/**
	 * @return void
	 */
	public function setSpellcheckQ($value)
	{
		$this->_spellcheckQ = $value;
	}
	
	/**
	 * @return boolean
	 */
	public function getDebugQuery()
	{
		return $this->_debugQuery;
	}

	/**
	 * @return void
	 */
	public function setDebugQuery($value)
	{
		$this->_debugQuery = $value;
	}
	
	/**
	 * @return string
	 */
	protected function _getQParameter()
	{

		if ($this->_q === null)
		{
			return '';
		}

		return '&q=' . rawurlencode($this->_q);
	}
	
	/**
	 * @return string
	 */
	protected function _getDfParameter()
	{
		
		if ($this->_df === null)
		{
			return '';
		}

		return '&df=' . rawurlencode($this->_df);
	}
	
	/**
	 * @return string
	 */
	protected function _getQOpParameter()
	{
		return '&q.op=' .  $this->_qOp;
	}

	/**
	 * @return string
	 */
	protected function _getStartParameter()
	{

		if ($this->_start === null)
		{
			return '';
		}
		
		return '&start=' . $this->_start;
	}

	/**
	 * @return string
	 */
	protected function _getRowsParameter()
	{

		if ($this->_rows === null)
		{
			return '';
		}

		return '&rows=' . $this->_rows;
	}

	/**
	 * @return string
	 */
	protected function _getFlParameter()
	{
		
		if ($this->_fl === null)
		{
			return '';
		}
		
		$parameter = '&fl=';
		$l = count($this->_fl);
		
		for ($i = 0; $i < $l; $i++)
		{
			$parameter .= rawurlencode($this->_fl[$i]) . ',';
		}
		
		return $parameter;
	}
	
	/**
	 * @return string
	 */
	protected function _getSortParameter()
	{

		if ($this->_sort === null)
		{
			return '';
		}

		$parameter = '&sort=';
		$l = count($this->_sort);

		for ($i = 0; $i < $l; $i++)
		{
			$parameter .= rawurlencode($this->_sort[$i]);
			
			if ($i < $l - 1)
			{
				$parameter .= ',';
			}

		}

		return $parameter;
	}

	/**
	 * @return string
	 */
	protected function _getFqsParameter()
	{
		
		if ($this->_fqs === null)
		{
			return '';
		}
		
		$parameter = '';
		$l = count($this->_fqs);
		
		for ($i = 0; $i < $l; $i++)
		{
			$parameter .= '&fq=' . rawurlencode($this->_fqs[$i]);
		}
		
		return $parameter;
	}

	/**
	 * @return string
	 */
	protected function _getHlParameter()
	{
		
		if ($this->_hl === null)
		{
			return '';
		}
		
		return $this->_hl ? '&hl=on' : '';
	}

	/**
	 * @return string
	 */
	protected function _getHlRequireFieldMatchParameter()
	{
		if ( ! isset($this->_hlRequireFieldMatch))
		{
			return '';
		}
		
		return '&hl.requireFieldMatch=' . ($this->_hlRequireFieldMatch ? 'true' : 'false');
	}
	
	/**
	 * @return string
	 */
	protected function _getHlUsePhraseHighlighterParameter()
	{
		return '&hl.usePhraseHighlighter=' . ($this->_hlUsePhraseHighlighter ? 'true' : 'false');
	}
	
	/**
	 * @return string
	 */
	protected function _getHlMergeContiguousParameter()
	{
		return '&hl.mergeContiguous=' . ($this->_hlMergeContiguous ? 'true' : 'false');
	}
	
	/**
	 * @return string
	 */
	protected function _getHlHighlightMultiTermParameter()
	{
		return '&hl.highlightMultiTerm=' . ($this->_hlHighlightMultiTerm ? 'true' : 'false');
	}
	
	
	/**
	 * @return string
	 */
	protected function _getHlFlParameter()
	{

		if ($this->_hlFl === null)
		{
			return '';
		}

		$parameter = '&hl.fl=';
		$l = count($this->_hlFl);
		
		for ($i = 0; $i < $l; $i++)
		{
			$parameter .= rawurlencode($this->_hlFl[$i]);
			
			if ($i < $l - 1)
			{
				$parameter .= ',';
			}
			
		}
		
		return $parameter;
	}

	/**
	 * @return string
	 */
	protected function _getHlSnippetsParameter()
	{
		if ($this->_hlSnippets === null)
		{
			return '';
		}
		
		return '&hl.snippets=' . $this->_hlSnippets;
	}
	
	
	/**
	 * @return string
	 */
	protected function _getHlFragSizeParameter()
	{
		
		if ($this->_hlFragSize === null)
		{
			return '';
		}

		return '&hl.fragsize=' . $this->_hlFragSize;
	}

	/**
	 * @return string
	 */
	protected function _getHlSimplePreParameter()
	{

		if (self::$_hlSimplePre === null)
		{
			return '';
		}

		return '&hl.simple.pre=' .  rawurlencode(self::$_hlSimplePre);
	}

	/**
	 * @return string
	 */
	protected function _getHlSimplePostParameter()
	{
		
		if (self::$_hlSimplePost === null)
		{
			return '';
		}

		return '&hl.simple.post=' .  rawurlencode(self::$_hlSimplePost);
	}

	/**
	 * @return string
	 */
	protected function _getFacetParameter()
	{

		if ($this->_facet === null)
		{
			return '';
		}

		return '&facet=' . ($this->_facet ? 'true' : 'false');
	}

	/**
	 * @return string
	 */
	protected function _getFacetSortParameter()
	{
		
		if ($this->_facetSort === null)
		{
			return '';
		}
		
		return '&facet.sort=' . $this->_facetSort;
	}
	
	/**
	 * @return string
	 */
	protected function _getFacetFieldsSortsParameter()
	{
		
		if (! $this->_facetFieldsSorts)
		{
			return '';
		}
		
		$ret = '';
		reset($this->_facetFieldsSorts);
		
		while (list($fieldName, $sortOrder) = each($this->_facetFieldsSorts))
		{
			$ret .= "&f.$fieldName.facet.sort=$sortOrder";
		}
		
		return $ret;
		
		
	}

	/**
	 * @return string
	 */
	protected function _getFacetFieldsParameter()
	{

		if ($this->_facetFields === null)
		{
			return '';
		}

		$parameter = '';
		$l = count($this->_facetFields);
		
		for ($i = 0; $i < $l; $i++)
		{
			$parameter.= '&facet.field=' . rawurlencode($this->_facetFields[$i]);
		}

		return $parameter;
	}

	/**
	 * @return string
	 */
	protected function _getFacetMincountParameter()
	{
		
		if ($this->_facetMincount === null)
		{
			return '';
		}

		return '&facet.mincount=' . $this->_facetMincount;
	}

	/**
	 * @return string
	 */
	protected function _getFacetLimitParameter()
	{
		
		if ($this->_facetLimit === null)
		{
			return '';
		}
		
		return '&facet.limit=' . $this->_facetLimit;
	}

	/**
	 * @return string
	 */
	protected function _getFacetPrefixParameter()
	{

		if ($this->_facetPrefix === null)
		{
			return '';
		}

		return '&facet.prefix=' . $this->_facetPrefix;
	}
	
	/**
	 * @return string
	 */
	protected function _getFacetQueriesParameter()
	{
		
		if ($this->_facetQueries === array())
		{
			return '';
		}
		
		$l = count($this->_facetQueries);
		$query = '';
		
		for ($i = 0; $i < $l; $i++)
		{
			$query .= '&facet.query=' . rawurlencode($this->_facetQueries[$i]);
		}
		
		return $query;
	}

	/**
	 * @return string
	 */
	protected function _getMltParameter()
	{

		if ($this->_mlt === null)
		{
			return '';
		}
		
		return '&mlt=' . ($this->_mlt ? 'true' : 'false');
	}
	
	/**
	 * @return string
	 */
	protected function _getMltCountParameter()
	{
		
		if ($this->_mltCount === null)
		{
			return '';
		}

		return '&mlt.count=' . $this->_mltCount;
	}

	/**
	 * @return string
	 */
	protected function _getMltFlParameter()
	{

		if ($this->_mltFl === null)
		{
			return '';
		}

		 $parameter = '&mlt.fl=';
		 $l = count($this->_mltFl);

		 for($i = 0; $i < $l; $i++)
		 {
		 	$parameter .= rawurlencode($this->_mltFl[$i]) . ',';
		 }
		 
		 return $parameter;
	}

	/**
	 * @return string
	 */
	protected function _getMltMintfParameter()
	{
		
		if ($this->_mltMintf === null)
		{
			return '';
		}
		
		return '&mlt.mintf=' . $this->_mltMintf;
	}

	/**
	 * @return string
	 */
	protected function _getMltMindfParameter()
	{
		
		if ($this->_mltMindf === null)
		{
			return '';
		}
		
		return '&mlt.mindf=' . $this->_mltMindf;
	}

	/**
	 * @return string
	 */
	protected function _getMltMinWlParameter()
	{
		
		if ($this->_mltMinwl === null)
		{
			return '';
		}
		
		return '&mlt.minwl=' . $this->_mltMinwl;
	}

	/**
	 * @return string
	 */
	protected function _getMltMaxwlParameter()
	{

		if ($this->_mltMaxwl === null)
		{
			return '';
		}

		return '&mlt.maxwl=' . $this->_mltMaxwl;
	}

	/**
	 * @return string
	 */
	protected function _getMltInterestingTermsParameter()
	{

		if ($this->_mltInterestingTerms === null)
		{
			return '';
		}

		return '&mlt.interestingTerms=' . $this->_mltInterestingTerms;
	}

	/**
	 * @return string
	 */
	protected function _getOmitHeaderParameter()
	{
		if ($this->_omitHeader === null)
		{
			return '';
		}

		return '&omitHeader=' . ($this->_omitHeader ? 'true' : 'false');
	}
	
	/**
	 * @return string
	 */
	protected function _getSpellcheckParameter()
	{

		if ($this->_spellcheck === null)
		{
			return '';
		}

		return '&spellcheck=' . ($this->_spellcheck ? 'true' : 'false');
	}

	/**
	 * @return string
	 */
	protected function _getSpellcheckCountParameter()
	{
		
		if ($this->_spellcheckCount === null)
		{
			return '';
		}
	
		return '&spellcheck.count=' . $this->_spellcheckCount;
	}

	/**
	 * @return string
	 */
	protected function _getSpellcheckCollateParameter()
	{

		if ($this->_spellcheckCollate === null)
		{
			return '';
		}

		return '&spellcheck.collate=' . ($this->_spellcheckCollate ? 'on' : 'off');
	}

	/**
	 * @return string
	 */
	protected function _getSpellcheckDictionaryParameter()
	{
		
		if ($this->_spellcheckDictionary === null)
		{
			return '';
		}

		return '&spellcheck.dictionary=' . $this->_spellcheckDictionary;
	}

	/**
	 * @return string
	 */
	protected function _getSpellcheckExtendedResultsParameter()
	{
		
		if ($this->_spellcheckExtendedResults === null)
		{
			return '';
		}

		$parameter = '&spellcheck.extendedResults=';
		return $parameter .= ($this->_spellcheckExtendedResults ? 'true' : 'false');
	}

	/**
	 * @return string
	 */
	protected function _getSpellcheckOnlyMorePopularParameter()
	{
		
		if ($this->_spellcheckOnlyMorePopular === null)
		{
			return '';
		}
		
		$parameter = '&spellcheck.onlyMorePopular=';
		return $parameter .= ($this->_spellcheckOnlyMorePopular ? 'true' : 'false');
	}

	/**
	 * @return string
	 */
	protected function _getSpellcheckQParameter()
	{
		
		if ($this->_spellcheckQ === null)
		{
			return '';
		}

		return '&spellcheck.q=' . rawurlencode($this->_spellcheckQ);
	}
	
	/**
	 * @return string
	 */
	protected function _getSpellcheckBuildParameter()
	{
		
		if ($this->_spellcheckBuild === null)
		{
			return '';
		}
		
		$parameter = '&spellcheck.build=';
		return $parameter .= ($this->_spellcheckBuild ? 'true' : 'false');
	}
	
	/**
	 * @return string
	 */
	protected function _getDebugQueryParameter()
	{

		if ($this->_debugQuery === null)
		{
			return '';
		}

		$parameter = '&debugQuery=';
		return $parameter .= ($this->_debugQuery ? 'on' : 'off');
	}
	
	/**
	 * @return string
	 */
	protected function _getWtParameter()
	{
		return '&wt=json';
	}

}