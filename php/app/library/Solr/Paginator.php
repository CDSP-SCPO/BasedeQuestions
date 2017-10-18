<?php

/**
 * @package Solr
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_Paginator implements Zend_Paginator_Adapter_Interface {

	/**
	 * @var Solr_BDQ_Search_Search
	 */
	protected $_search;

	/**
	 * @var int
	 */
	protected $_count;

	/**
	 * @return void
	 */
	public function __construct(Solr_BDQ_Search_Search $search)
	{
		$this->_search = $search;
	}

	/**
	 * @return int
	 */
	public function count()
	{

		if ( ! isset($this->_count))
		{
			$select = $this->_search->getSelect();
			$select->setFl(array('id'));
			$select->setFacet(false);
			$select->setHl(false);
			$select->setSpellcheck(false);
			$select->setRows(0);
			$response = $select->send();
			$this->_count = $response->response['response']['numFound'];
		}

		return $this->_count;
	}

	/**
	 * @return Solr_Response
	 */
	public function getItems($offset, $count)
	{
		$this->_search->start = $offset;
		return $this->_search->send();
	}

}