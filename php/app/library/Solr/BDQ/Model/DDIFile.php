<?php

/**
 * @package SolrModel
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_BDQ_Model_DDIFile {

	/**
	 * @var DDI_Parser
	 */
	protected $_parser;

	/**
	 * @var string
	 */
	protected $_lang;
	
	/**
	 * @return BDQ_Model_DDIFile
	 */
	public function __construct($file, $lang)
	{
		$factory = DDI_ParserFactory::getInstance();
		$this->_parser = $factory->create($file);
		$this->_lang = $lang;
	}

	/**
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return call_user_func_array(array($this->_parser, $name), $args);
	}

	/**
	 * @return 
	 */
	public function send()
	{
		$questions = $this->getQuestions($this->_lang);
		$docs = array();
		
		foreach ($questions as $question)
		{
			$doc = new Solr_Document;
			
			foreach (SolrModel_Question::$fields as $field)
			{
				
				if($field == 'id')
				{
					continue;
				}
				
				$set = "set$field";
				$get = "get$field";
				$doc->$set($question->$get());
			}	

			$docs[] = $doc;
		}
		
		$update = new Solr_Update($docs);
		return $update->send();
	}

	/**
	 * @return 
	 */
	public function delete()
	{

	}

}