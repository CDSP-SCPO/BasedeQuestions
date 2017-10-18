<?php

/**
 * @package Solr_BDQ_Search
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
abstract class Solr_BDQ_Search_FulltextSearch extends Solr_BDQ_Search_Search {

	/**
	 * @return void
	 */
	public function getSelect()
	{
		parent::getSelect();
		$this->_addSort();
		return $this->_select;
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