<?php

/**
 * @package Solr_BDQ_Search
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_BDQ_Search_ConceptSearch extends Solr_BDQ_Search_FacetSearch
{
	/**
	 * @var int
	 */
	protected $_conceptId;
	
	/*
	 * @param int $conceptId
	 * @param string $searchLang
	 */
	public function __construct($conceptId, $searchLang)
	{
		$this->_conceptId = $conceptId;
		$this->addFq($this->getFirstFq());
		parent::__construct($searchLang);
	}

	/**
	 * @return string
	 */
	public function getFirstFq()
	{
		return "conceptId:$this->_conceptId";
	}
	
}