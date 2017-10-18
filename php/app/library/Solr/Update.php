<?php

/**
 * @package Solr
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_Update extends Solr_Request {

	/**
	 * @var array
	 */
	protected $_documents;

	/**
	 * @return Solr_Update
	 */
	public function __construct($documents)
	{
		parent::__construct();

		if (is_array($documents))
		{
			$this->_documents = $documents;
		}

		elseif ($documents instanceof Solr_Document)
		{
			$this->_documents = array($documents);
		} 
	}

	/**
	 * @return Solr_Response
	 */
	public function send()
	{
		$client = Solr_Client::getInstance();
		return $client->send($this);
	}

	/**
	 * @return string
	 */
	public function getXML()
	{
		$xml = '<add>';
		
		foreach ($this->_documents as $doc)
		{
			$xml .= $doc->getXML();
		}

		return $xml .= '</add>';
	}

}