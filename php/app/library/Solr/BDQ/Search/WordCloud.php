<?php
/**
 * @package Search
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_BDQ_Search_WordCloud
{

	/**
	 * @var string
	 */
	protected $_query;

	/**
	 * @var string
	 */
	protected $_searchLang;
	
	/**
	 * @var float
	 */
	public $tfLowerBound;

	/**
	 * @param string $query
	 * @param string $searchLang
	 * @return Solr_BDQ_Search_WordCloud
	 */
	public function __construct($query, $searchLang)
	{
		$this->_query = $query;
		$this->_searchLang = $searchLang;
	}

	/**
	 * @return Solr_Select
	 */
	protected function _getSelect()
	{
		$select = new Solr_Select($this->_query);
    	$select->setFl(array('id'));
    	$select->setRows(1);
    	$select->setFacet(true);
    	$select->setFacetFields(array("qiSw$this->_searchLang"));
    	$select->setFacetMincount(1);
    	$select->setFacetLimit(-1);
    	return $select;
	}

	/**
	 * @return array
	 */
	public function & getWords()
	{
		$response = $this->_getSelect()->send();
    	$facets = $response->response['facet_counts']['facet_fields']["qiSw$this->_searchLang"];
    	$l = count($facets);
    	$_facets = array();
    	$wc = 0;
    	
    	for ($i = 0; $i < $l; $i += 2)
    	{

    		if (
    			! preg_match('/[0-9]/', $facets[$i])
    			&& strlen($facets[$i]) > 2
    		)
    		{
    			$add = array(
    				'word' =>  $facets[$i],
    				'freq' =>$facets[$i + 1]
    			);
    			$wc += $facets[$i + 1];
    			$_facets[] = $add;
    		}

    	}

    	$facets = $_facets;
    	$l = count($facets);
    	$_facets = array();
    	$lowerBound = $this->tfLowerBound ? $this->tfLowerBound : TF_LOWER_BOUND;
    	
    	for ($i = 0; $i < $l; $i++)
    	{
    		$frequency = $facets[$i]['freq'] / $wc * 100;
    		
    		if ($frequency > $lowerBound && $frequency < TF_UPPER_BOUND)
    		{
    			$facets[$i]['freq'] = $frequency * 15;
    			$_facets[] = $facets[$i];
    		}
    		
    	}
    	
    	return $_facets;
	}

}