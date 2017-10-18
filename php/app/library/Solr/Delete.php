<?php

/**
 * @package Solr
 */

/**
 * Deletes documents from the Solr server 
 * 
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class Solr_Delete extends Solr_Request {

	/**
	 * The Solr document's id to delete if it was given as parameter to the constructor.
	 * @var string
	 */
	protected $_id;

	/**
	 * The query that must be matched by documents to delete if it was given as parameter to the constructor.
	 * @var string
	 */
	protected $_query;

	/**
	 * 
	 * @param array $toDelete An associative array containing a single element. 
	 * The key must be either id or query.
	 * If an id is given then a single document will be deleted.
	 * If a lucene query is given all matched documents will be deleted.
	 * A call to {@link Solr_Client::commit()} is needed for changes to take effect 
	 * or to {@link Solr_Client::rollback()} to cancel changes.
	 * 
	 * @return Solr_Delete
	 */
	public function __construct($toDelete)
	{
		parent::__construct();

		if ( ! isset($toDelete['id']) && ! isset($toDelete['query']))
		{
			trigger_error('Solr_Delete\'s constructor expect an array with either a "id" or "query" key as parameter.');			
		}

		elseif (isset($toDelete['id']))
		{
			$this->_id = $toDelete['id'];
		}

		else
		{
			$this->_query = $toDelete['query'];
		}

	}

	/**
	 * Sends the delete command to Solr. Can be followed by a commit or a rollback.
	 * 
	 * @return Solr_Response
	 */
	public function send()
	{
		$client = Solr_Client::getInstance();
		return $client->send($this);
	}

	/**
	 * Returns the XML sent by the HTTP POST method to Solr
	 * 
	 * @return string
	 */
	public function getXML()
	{
		$xml = '<delete>';

		if (isset($this->_id))
		{
			$xml .= '<id>' . $this->_id . '</id>';
		}

		else
		{
			$xml .= '<query>' . $this->_query . '</query>';	
		}

		$xml .= '</delete>';
		return $xml;
	}

}