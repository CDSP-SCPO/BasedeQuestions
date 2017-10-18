<?php

/**
 * @package Solr
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class Solr_Document {
	
	/**
	 * @var Solr_Response
	 */
	public $response;

	/**
	 * An associative array containing the document's fields.
	 * 
	 * @var array
	 */
	protected $_fields;

	/**
	 * The document boost used in the pertinence score formula.
	 * 
	 * @var float
	 */
	protected $_boost;

	/**
	 * Raw highlighting of terms matched by the query.
	 * 
	 * @var string
	 */
	protected $_rawHl;

	/**
	 * Informations about the score of this document against the query that matched it.
	 * Requires debugQuery=on in the select query
	 * 
	 * @var string
	 */
	protected $_explain;

	/**
	 * Getters and setters to the document's fields.
	 * 
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		$mode = substr($method, 0, 3);
		
		if ( ! ($mode == 'get' || $mode == 'set'))
		{
			trigger_error("Solr_Document::$method - no such method", E_USER_ERROR);			
		}
		
		$field = substr($method, 4);
		
		if ($mode == 'get')
		{
			return isset($this->_fields[$field]) ? $this->_fields[$field] : NULL;
		}
		
		if ($mode == 'set')
		{
			$this->_fields[$field] = $args[0];
		}
	}	
	
	/**
	 * Set the document's boost in the pertinence score formula.
	 * 
	 * @return void
	 */
	public function setBoost($value)
	{
		$this->_boost = $value; 
	}

	/**
	 * Returns the document XML string as expected in a document list sent to the Solr server.
	 * 
	 * @return string
	 */
	public function getXml()
	{
		
		$xml = '<doc' . (isset($this->_boost) ? ' boost="' . (float) $this->_boost .'"' : '') . '>';
		
		foreach($this->_fields as $name => $value)
		{
			
			if ($value === NULL)
			{
				continue;
			}
			
			if (is_array($value))
			{
				
				foreach($value as $val)
				{
					$xml .= "<field name=\"$name\"><![CDATA[$val]]></field>";
				}
				
				continue;
				
			}
			
			$xml .= "<field name=\"$name\"><![CDATA[$value]]></field>";			
		}
		
		return $xml .= '</doc>';		
	}

	/**
	 * Send the document to the Solr server. Can be followed by a commit or a rollback command.
	 * 
	 * @return void
	 */
	public function send()
	{
		$update = new Solr_Update($this);
		return $update->send();
	}

	/**
	 * Delete the document from the Solr server. Can be followed by a commit or a rollback command.
	 * 
	 * @return void
	 */
	public function delete()
	{
		$delete = new Solr_Delete(array('id' => $this->_fields['id']));
		return $delete->send();
	}

	/**
	 * Returns the raw highlighting of terms matched by the query.
	 * 
	 * @return string
	 */
	public function getRawHl($field)
	{
		return isset($this->_rawHl[$field][0]) ? $this->_hl[$field][0] : null;
	}

	/**
	 * Set the raw highlighting of terms matched by the query.
	 * 
	 * @return void
	 */
	public function setRawHl($value)
	{
		$this->_rawHl = $value;
	}

	/**
	 * If the select's parameter debugQuery is equals to on, returns informations about the score of this document against the query that matched it.
	 * 
	 * @return string
	 */
	public function getExplain()
	{
		return $this->_explain;
	}

	/**
	 * Set the _explain attribute.
	 * 
	 * @return void
	 */
	public function setExplain($value)
	{
		$this->_explain = $value;
	}
	
	/**
	 * Returns the _fields attributes
	 * 
	 * @return array
	 */
	public function toArray()
	{
		return $this->_fields;
	}
	
	/**
	 * Add the highlight simple pre and highlight simple post parameters to the returned document's fields in the _fields attribute
	 * 
	 * @return void
	 */
	public function addHl()
	{
		
		if ( ! isset($this->_rawHl))
		{
			return;
		}
		
		$lang = $this->response->request->search->getSearchLang();
		
		while (list($fieldName, $val) = each($this->_rawHl))
		{
			$baseFieldName = '';
			$baseFieldName = $fieldName[0] == 'v' ? 'vl' : $fieldName[0];
			$fieldName = $baseFieldName . $lang;
			
			if ($baseFieldName == 'q')
			{
				$this->_fields[$fieldName] = $val[0];
			}
			
			else
			{
				$l = count($val);
				$matchesPos = array();
				
				for ($i = 0; $i < $l; $i++)
				{
					$this->_fields[$fieldName][$pos = (int)substr($val[$i], 0, 5)] = substr($val[$i], 6);
					$matchesPos[] = $pos;
				}
				
				sort($matchesPos);
				$this->_fields["{$baseFieldName}Matches"] = $matchesPos;
			}
			
		}

	}

}