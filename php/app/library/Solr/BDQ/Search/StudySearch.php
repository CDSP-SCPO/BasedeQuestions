<?php

/**
 * @package Solr_BDQ_Search
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_BDQ_Search_StudySearch extends Solr_BDQ_Search_FacetSearch
{
	
	/**
	 * @var int
	 */
	protected $_studyDescriptionId;
	
	/*
	 * @param int $studyDescriptionId
	 * @param string $searchLang
	 */
	public function __construct($studyDescriptionId, $searchLang)
	{
		$this->_studyDescriptionId = $studyDescriptionId;
		$this->addFq($this->getFirstFq());
		parent::__construct($searchLang);
	}
	
	/**
	 * @return string
	 */
	public function getFirstFq()
	{
		return "studyDescriptionId:$this->_studyDescriptionId";
	}

}