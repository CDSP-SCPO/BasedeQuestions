<?php

/**
 * @package Solr_BDQ_Search
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
abstract class Solr_BDQ_Search_FacetSearch extends Solr_BDQ_Search_Search
{
	/**
	 * @var string
	 */
	protected $_searchLang;

	/**
	 * @param string $searchLang
	 * @return Solr_BDQ_Search_FacetSearch
	 */
	public function __construct($searchLang)
	{
		$this->hl = false;
		$this->_select = new Solr_Select('*:*');
		$this->_searchLang = $searchLang;
	}

	/**
	 * @return string
	 */
	abstract function getFirstFq();

	/**
	 * @return Solr_Response
	 */
	public function send()
	{
		return $this->getSelect()->send();
	}

	/**
	 * @return Solr_Select
	 */
	public function getSelect()
	{
		$this->_addSort();
		return parent::getSelect();
	}

	/**
	 * @return string
	 */
	public function getSearchLang()
	{
		return $this->_searchLang;
	}

	/**
	 * @return void
	 */
	protected function _addSort()
	{
		if ($this->sort === NULL)
		{
			$settings = BDQ_Settings_Client::getInstance();
			$this->_select->setSort(array($settings->sort));
		}
		
		else
		{
			$this->_select->setSort($this->sort);
		}

	}

}