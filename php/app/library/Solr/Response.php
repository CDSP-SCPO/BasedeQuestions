<?php

/**
 * @package Solr
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_Response implements Countable, ArrayAccess, Iterator {

	/**
	 * @var Solr_Request
	 */
	public $request;

	/**
	 * @var array
	 */
	public $response;

	/**
	 * @var array
	 */
	public $documents = array();
	
	/**
	 * @var mixed $_currentItemOffset
	 */
	protected $_currentItemOffset;

	/**
	 * @return Solr_Response
	 */
	public function __construct(& $response, $request = null)
	{
		$this->request = $request;
		$this->response = json_decode($response, true);
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->documents);
	}

	/**
	 * @return BDQ_Model_Question
	 */
	public function current()
	{
		return $this->documents[$this->_currentItemOffset];
	}

	/**
	 * @return int
	 */
	public function key()
	{
		return $this->_currentItemOffset;
	}

	/**
	 * @return BDQ_Model_Question
	 */
	public function next()
	{
		$this->_currentItemOffset += 1;		
		return $this->current();
	}

	/**
	 * @return void
	 */
	public function rewind()
	{
		$this->_currentItemOffset = 0;
	}

	/**
	 * @return boolean
	 */
	public function valid()
	{
		return $this->_currentItemOffset < count($this->documents);
	}

	/**
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $offset < count($this->documents);
	}

	/**
	 * @return int|Solr_Document
	 */
	public function offsetGet($offset)
	{
		return $this->documents[$offset];
	}

	/**
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->documents[$offset] = $value;
	}

	/**
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->documents[$offset]);
	}

	/**
	 * @return array
	 */
	public function createDocuments()
	{

		if ( ! isset($this->response['response']['docs']))
		{
			return;	
		}
		
		$t = microtime(true);
		$l = count($this->response['response']['docs']);
		
		foreach ($this->response['response']['docs'] as $doc)
		{
			$_doc = new Solr_Document;
			$_doc->response = $this;
			
			foreach ($doc as $field => $value)
			{
				$method = "set_$field";
				$_doc->$method($value);		

				if (isset($this->response['highlighting'][$doc['id']]))
				{
					$_doc->setRawHl($this->response['highlighting'][$doc['id']]);
				}
	
				if (isset($this->response['debug']['explain'][$doc['id']]))
				{
					$_doc->setExplain($this->response['debug']['explain'][$doc['id']]);
				}

			}
			
			$_doc->addHl();
			$this->documents[] = $_doc;
		}
		
	}

}