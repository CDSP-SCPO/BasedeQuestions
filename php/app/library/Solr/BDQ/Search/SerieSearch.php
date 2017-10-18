<?php

/**
 * @package Solr_BDQ_Search
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_BDQ_Search_SerieSearch extends Solr_BDQ_Search_FacetSearch
{
	/**
	 * @var int
	 */
	protected $_serieId;
	
	/*
	 * @param int $conceptId
	 * @param string $searchLang
	 */
	public function __construct($serieId, $searchLang)
	{
		$this->_serieId = $serieId;
		$this->addFq($this->getFirstFq());
		parent::__construct($searchLang);
	}

	/**
	 * @return string
	 */
	public function getFirstFq()
	{
		return "studySerieId:$this->_serieId";
	}
	
}