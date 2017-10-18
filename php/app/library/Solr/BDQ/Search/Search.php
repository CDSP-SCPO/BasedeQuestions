<?php

/**
 * @package Solr_BDQ_Search
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
abstract class Solr_BDQ_Search_Search {

	/**
	 * @var boolean
	 */
	public $facetOnly = false;
	
	/**
	 * @var boolean
	 */
	public $facetOnDecade = true;
	
	/**
	 * @var boolean
	 */
	public $facetOnSerie = true;
	
	/**
	 * @var boolean
	 */
	public $facetOnStudy = true;

	/**
	 * @var boolean
	 */
	public $facetOnProducer = true;
	
	/**
	 * @var boolean
	 */
	public $facetOnConcept = false;
	
	/**
	 * @var int
	 */
	public $start = 0;
	
	/**
	 * @var boolean
	 */
	public $hl = true;
	
	/**
	 * @var array
	 */
	static $fl = array(
		'id',
		'domainId',
		'solrLangCode',
		'studySerieId',
		'ddiFileId',
		'studyDescriptionId',
		'nesstarServerId',
		'studyNesstarId',
		'variableId',
		'questionId',
		'variableNesstarId',
		'conceptId',
		'studyTitle',
		'studyQuestionCount',
		'hasMultipleItems',
		'variableName',
		'hasQuestionnaire',
		'questionnaireId',
		'questionnaireTitle',
		'questionnaireUrl',
		'studyDescriptionUrl',
		'universe',
		'notes',
		'preQuestionText',
		'postQuestionText',
		'interviewerInstructions',
		'questionPosition',
		'score',
		'vlFR',
		'qFR',
		'iFR',
		'mFR',
		'vlEN',
		'qEN',
		'iEN',
		'mEN',
	);

	/**
	 * @var array
	 */
	public $sort;

	/**
	 * @var Solr_Select
	 */
	protected $_select;
	
	/**
	 * @var array
	 */
	protected $_fqs = array();

	/**
	 * @return Solr_Response
	 */
	abstract public function send();

	/**
	 * @return string
	 */
	abstract public function getSearchLang();

	/**
	 * @return Solr_Select
	 */
	public function getSelect()
	{
		$settings = BDQ_Settings_Client::getInstance();
		$this->_select->search = $this;
		$this->_select->setOmitHeader(true);
		$this->_select->setStart($this->start);
		$this->_select->setRows($settings->rows);
		
		if ( ! $this->facetOnly)
		{
			$this->_addFl();
		}
		
		else
		{
			$this->_select->setFl(
				array(
					'id',
				)
			);
		}
		
		$this->_addFqs();
		$this->_addFacets();
		
		if ($this->hl)
		{
			$this->_addHl();
		}
		
		return $this->_select;
	}

	/**
	 * @return void
	 */
	public function addFq($fq)
	{
		$this->_fqs[] = $fq;
	}
	
	/**
	 * @return void
	 */
	protected function _addFqs()
	{
		$this->_select->setFqs($this->_fqs);
	}

	/**
	 * @return void
	 */
	protected function _addFl()
	{
		$this->_select->setFl(self::$fl);
	}

	/**
	 * @return void
	 */
	protected function _addFacets()
	{
		$this->_select->setFacet(true);
		$fields = array();
		
		if ($this->facetOnProducer)
		{
			$fields[] = '{!ex=studySerieIds,decades,studyIds,queryFilters,conceptIds,domainIds}domainId';
		}
		
		if ($this->facetOnDecade)
		{
			$fields[] = '{!ex=studySerieIds,decades,studyIds,queryFilters,conceptIds,domainIds}studyDecade';
		}
		
		if ($this->facetOnSerie)
		{
			$fields[] = '{!ex=studySerieIds,decades,studyIds,queryFilters,conceptIds,domainIds}studySerieId';
		}
		
		if ($this->facetOnStudy)
		{
			$fields[] = '{!ex=studySerieIds,decades,studyIds,queryFilters,conceptIds,domainIds}studyDescriptionId';
		}
		
		if ($this->facetOnConcept)
		{
			$fields[] = '{!ex=studySerieIds,decades,studyIds,queryFilters,conceptIds,domainIds}conceptId';
		}
		
		$this->_select->addFacetFieldSort('studyDecade', 'lex');
		$this->_select->setFacetFields($fields);
		$this->_select->setFacetMincount(1);
	}
	
	protected function _addHl()
	{
		$this->_select->setHl($this->hl);
		$this->_select->setHLFragSize(0);
		$this->_select->setHLSnippets(10000);
		$this->_select->setHlHighlightMultiTerm(true);
		$this->_select->setHlUsePhraseHighlighter(true);
		$this->_select->setHlRequireFieldMatch(true);
		$searchLang = $this->getSearchLang();
		$analysis = BDQ_Settings_Client::getAnalysisCode();
		$hlFl = array(
			"q$analysis$searchLang",
			"iHL$analysis$searchLang",
			"mHL$analysis$searchLang",
			"vlHL$analysis$searchLang"
		);
			$this->_select->setHlFl($hlFl);
	}
	
}