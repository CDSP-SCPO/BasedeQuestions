<?php
/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 * @package Search
 *
 */
class Solr_BDQ_Search_ConceptFacet
{

	/**
	 * @var string
	 */
	protected $_query;
	
	/**
	 * @var Solr_Select
	 */
	protected $_select;
	
	/**
	 * @var int
	 */
	protected $_translationLanguageId;
	
	/**
	 * @var DB_Mapper_Concept
	 */
	protected $_conceptMapper;
	
	/**
	 * @var Solr_Response
	 */
	protected $_response;

	/**
	 * @var array
	 */
	protected $_conceptIds;
	
	/**
	 * @var array
	 */
	protected $_conceptCounts;

	/**
	 * @param string $query
	 * @param int $translationLanguageId
	 */
	public function __construct($query, $translationLanguageId)
	{
		$this->_query = $query;
		$this->_translationLanguageId = $translationLanguageId;
		$this->_select = new Solr_Select($this->_query);
    	$this->_select->setFl('id');
    	$this->_select->setFacet(true);
    	$this->_select->setFacetFields(array('conceptId'));
    	$this->_select->setRows(0);
    	$this->_conceptMapper = new DB_Mapper_Concept;
	}

	/**
	 * @return Solr_Select
	 */
	public function getSelect()
	{
		return $this->_select();
	}

	/**
	 * @return Solr_Response
	 */
	public function send()
	{
		return $this->_response = $this->_select->send();
	}

	/**
	 * @return array
	 */
	public function getConceptIds()
	{

		if ( ! $this->_response)
		{
			$this->_response = $this->send();
		}
		
		if ( ! $this->_conceptIds)
		{
			$l = count($this->_response->response['facet_counts']['facet_fields']['conceptId']);
			
			for ($i = 0; $i < $l; $i+=2)
			{
				
				if ($this->_response->response['facet_counts']['facet_fields']['conceptId'][$i + 1] > 0)
				{
					$this->_conceptIds[] = $this->_response->response['facet_counts']['facet_fields']['conceptId'][$i];
					$this->_conceptCounts[$this->_response->response['facet_counts']['facet_fields']['conceptId'][$i]] = $this->_response->response['facet_counts']['facet_fields']['conceptId'][$i + 1];
				}

			}

		}

		return $this->_conceptIds;
	}

	/**
	 * @return array
	 */
	public function getConceptFacets()
	{
		$concepts = $this->_conceptMapper->findAllTitleAndId(
			$this->getConceptIds(),
			$this->_translationLanguageId
		);
		$l = count($concepts);
		
		for ($i = 0; $i < $l; $i++)
		{
			$concepts[$i]['count'] = $this->_conceptCounts[$concepts[$i]['id']];
		}
		
		return $concepts;
	}

}