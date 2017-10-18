<?php

/**
 * @package Solr_BDQ_Search
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_BDQ_Search_SimpleSearch extends Solr_BDQ_Search_FulltextSearch {
	
	/**
	 * @var boolean
	 */
	public $spellcheck = true;
	
	/**
	 * @var Solr_BDQ_Search_SearchField
	 */
	protected $_field;
	
	/**
	 * @var BDQ_Settings_Client
	 */
	protected $_clientSettings;

	/**
	 * @return BDQ_SimpleSearch
	 */
	public function __construct(Solr_BDQ_Search_SearchField $field)
	{
		$this->_clientSettings = BDQ_Settings_Client::getInstance();
		$this->setSearchField($field);
		$this->addFq('solrLangCode:' . $this->getSearchLang());
	}
	
	/**
	 * @return Solr_Response
	 */
	public function send()
	{
		return $this->getSelect()->send();
	}

	/**
	 * @return Solr_Response
	 */
	public function getSelect()
	{
		$query = $this->getQuery();
		$this->_select = new Solr_Select($query);
		parent::getSelect();
		
		if ($this->spellcheck)
		{
			$this->_addSpellcheck();
		}
		
		return $this->_select;
	}

	/**
	 * @return string
	 */
	public function getSearchLang()
	{
		return $this->_field->getSearchLang();
	}
	
	/**
	 * @return string
	 */
	public function getRawQuery()
	{
		return $this->_field->getRawQuery();
	}
	
	/**
	 * @return string
	 */
	public function getQuery()
	{
		return $this->_field->getQuery();
	}
	
	/**
	 * @param Solr_BDQ_Search_SearchField $field
	 * @return void
	 */
	public function setSearchField(Solr_BDQ_Search_SearchField $field)
	{
		$this->_field = $field;
	}
	
	/**
	 * @return Solr_BDQ_Search_SearchField
	 */
	public function getSearchField()
	{
		return $this->_field;
	}
	
	/**
	 * @return void
	 */
	protected function _addSpellcheck()
	{
		$q = $this->_getSpellcheckQ();
		$q = trim($q);
		
		if (empty($q))
		{
			return ;
		}
		
		$this->_select->setSpellcheck(true);
		$this->_select->setSpellcheckDictionary(
			str_replace(
				array(
					'Sw',
					'St',
				),
				array(
					'',
					'',
				),
				$this->_field->getName()
			)
		);
		$this->_select->setSpellcheckCount(10);
		$this->_select->setSpellcheckQ($q);
	}

	/**
	 * @return string
	 */
	protected function _getSpellcheckQ()
	{
		$query = $this->getRawQuery();
		$patterns = array();
		$patterns[] = '/\w+\*/';
		$patterns[] = '/\w+\?\w*/';
		$patterns[] = '/~(0\.[5-9])?/';
		$patterns[] = '/"/';
		$patterns[] = '/\(|\)/';
		$patterns[] = '/(\+|-|!)\s+/';
		$patterns[] = '/\^[0-9]+/';
		$patterns[] = '/\d+/';
		$replaces = array();
		$replaces[] = ' ';
		$replaces[] = ' ';
		$replaces[] = ' ';
		$replaces[] = '';
		$replaces[] = '';
		$replaces[] = '';
		$replaces[] = '';
		$replaces[] = ' ';
		$replaces[] = ' ';
		$query = preg_replace($patterns, $replaces, $query);
		$query = str_replace(
			array(
				'AND',
				'OR',
				'NOT'
			),
			array(
				' ',
				' ',
				' '
			),
			$query
		);
		$query = utf8_decode($query);
		return $query;
	}

}