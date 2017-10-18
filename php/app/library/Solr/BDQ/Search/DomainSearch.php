<?php

/**
 * @package Solr_BDQ_Search
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_BDQ_Search_DomainSearch extends Solr_BDQ_Search_FacetSearch
{
	/**
	 * @var int
	 */
	protected $_domainId;
	
	/*
	 * @param int $_domainId
	 * @param string $searchLang
	 */
	public function __construct($domainId, $searchLang)
	{
		$this->_domainId = $domainId;
		$this->addFq($this->getFirstFq());
		parent::__construct($searchLang);
	}

	/**
	 * @return string
	 */
	public function getFirstFq()
	{
		return "domainId:$this->_domainId";
	}

}