<?php

/**
 * @package Solr_BDQ_Search
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_BDQ_Search_AdvancedSearch extends Solr_BDQ_Search_SimpleSearch {

	/**
	 * @var array
	 */
	protected $_fields;

	/**
	 * @return Solr_BDQ_Search_AdvancedSearch
	 */
	public function __construct(array $fields)
	{
		$this->_clientSettings = BDQ_Settings_Client::getInstance();
		$this->_fields = $fields;
		$this->addFq('solrLangCode:' . $this->getSearchLang());
		$this->spellcheck = false;
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
		parent::getSelect();
		return $this->_select;
	}

	/**
	 * @return string
	 */
	public function getSearchLang()
	{
		return $this->_fields[0]->getSearchLang();
	}

	/**
	 * @return string
	 */
	public function getQuery()
	{
		$query = '';
		$i = 0;
		$l = count($this->_fields);

		while ($i < $l)
		{
			$field = $this->_fields[$i];
			$query .= $field->getQuery();
			
			if ($i < $l - 1)
			{
				$query .= ' ';
			}
			
			$i++;
		}

		return $query;
	}

}