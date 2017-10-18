<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class SolrsearchController extends BDQ_Locale_FrontController
{

	/**
	 * @var array
	 */
	protected $_sortOrders = array(
		1 => 'score desc',
		2 => 'domainId asc, studySerieId asc, ddiFileId asc, questionPosition asc',
		3 => 'studyYear asc, questionPosition asc',
		4 => 'studyYear desc, questionPosition asc',
		5 => 'questionPosition asc',
		6 => 'questionPosition desc',
	);

	/**
	 * @var array
	 */
	protected $_sortOrdersLabels;

    public function init()
    {
    	parent::init();
		$this->_sortOrdersLabels = array(
			1 => $this->_translate->_('fr0020000000'),
			2 => $this->_translate->_('fr0020000050'),
			3 => $this->_translate->_('fr0020000100'),
			4 => $this->_translate->_('fr0020000150'),
			5 => $this->_translate->_('fr0020000200'),
			6 => $this->_translate->_('fr0020000250'),
		);
    }

    public function searchresultsAction()
    {
    	$search = $this->_simpleSearchFactory();
    	
    	if ($this->_request->getBDQParam('updateFacets'))
		{
			$this->_updateFacets($search);
		}
		
		if ($this->_request->getBDQParam('filterResults'))
		{
			$this->_filterResults('_redirectToSimpleSearchResults');
		}
    	
		else
		{
	    	$paginator = new Zend_Paginator(new Solr_Paginator($search));

	    	try
	    	{
	    		$paginator->setItemCountPerPage($this->_clientSettings->rows);
	    	}
	    	
	    	catch (Solr_ClientHTTPCodeException $e)
	    	{
	    		$this->view->searchError = true;
	    		$this->view->search = $search;
	    		return;
	    	}
	    	
			$paginator->setCurrentPageNumber($this->_request->getBDQParam('page'));
			$this->_addSearchResultsViewParams($search, $paginator);
			$router = Zend_Controller_Front::getInstance()->getRouter();
		}
    }

	/**
     * @return Solr_BDQ_Search_SimpleSearch
     */
    protected function _simpleSearchFactory()
    {
    	$searchLang = $this->_request->getBDQParam('searchLang');
    	$target = $this->_request->getBDQParam('target');
    	$query = $this->_request->getBDQParam('query');

    	if (empty($query))
    	{
    		$this->_redirectToHome();
    	}

    	$searchField = new Solr_BDQ_Search_SearchField($query, $target, $searchLang);
    	$search = new Solr_BDQ_Search_SimpleSearch($searchField);
    	$this->_addKeywordFilterQueries($search);
    	$this->_addFilterQueries($search, 'domainIds', 'domainId', 'OR');
    	$this->_addFilterQueries($search, 'studySerieIds', 'studySerieId', 'OR');
		$this->_addFilterQueries($search, 'studyIds', 'studyDescriptionId', 'OR');
		$this->_addFilterQueries($search, 'decades', 'studyDecade', 'OR');
    	$this->_addFilterQueries($search, 'conceptIds', 'conceptId', 'OR');
    	$search->sort = array($this->_sortOrders[$this->_request->getBDQParam('sort')]);

    	if ($this->_clientSettings->displayConcept)
    	{
    		$search->facetOnConcept = true;
    	}
    	
    	return $search;
    }

	/**
     * @param Solr_BDQ_Search_Search $search
     * @param string $paramName the request param name
     * @param string $solrFieldName the solr field name to filter on
     * @param string $operator the boolean operator to use
     * @return void
     */
    protected function _addFilterQueries(Solr_BDQ_Search_Search $search, $paramName, $solrFieldName, $operator)
    {
    	$filters = $this->_request->getBDQParam($paramName);

  		if ($filters === URL_PARAM_NULL)
    	{
    		return;
    	}
    	
   	  	$filters = explode(URL_PARAM_SEPARATOR, $filters);
    	$fq = "{!tag=${paramName}}";
    	$fq .= "$solrFieldName: (";
    	$l = count($filters);
    	
    	for ($i = 0; $i < $l; $i++)
    	{
    		$fq .= $filters[$i];
    		
    		if ($i < $l - 1)
    		{
    			$fq .= " $operator ";
    		}
    		
    	}
    	
    	$fq .= ')';
    	$search->addFq($fq);
    }

    /**
     * @param array
     * @return string
     */
    protected function _getQueryFiltersQuery(array $queries)
    {
    	$searchLang = $this->_request->getBDQParam('searchLang');
    	$l = count($queries);
    	$query = '';
    	$qQ = '';
    	$mQ = '';
    	$vQ = '';
    	$qmQ = '';
    	$qvQ = '';
    	$mvQ = '';
    	$qmvQ = '';

    	for ($i = 0; $i < $l; $i++)
    	{
    		$filter = $queries[$i];
    		$_target = substr($filter, -1);
    		$_query = substr($filter, 0, -1);
    		
    		if ($_target == SEARCH_QUESTION)
    		{
    			$qQ .= $_query . ' ';
    		}
    		
    		elseif ($_target == SEARCH_MODALITIES)
    		{
    			$mQ .= $_query . ' ';
    		}
    		
    		elseif ($_target == SEARCH_VARIABLE)
    		{
    			$vQ .= $_query . ' ';
    		}

    		elseif ($_target == (SEARCH_QUESTION | SEARCH_MODALITIES))
    		{
    			$qmQ .= $_query . ' ';
    		}
    		
   		 	elseif ($_target == (SEARCH_QUESTION | SEARCH_VARIABLE))
    		{
    			$qvQ .= $_query . ' ';
    		}
    		
    		elseif ($_target == (SEARCH_MODALITIES | SEARCH_VARIABLE))
    		{
    			$mvQ .= $_query . ' ';
    		}
    		
    		elseif ($_target == (SEARCH_QUESTION | SEARCH_MODALITIES | SEARCH_VARIABLE))
    		{
    			$qmvQ .= $_query . ' ';
    		}
    		
    	};

    	if ($qQ)
    	{
	    	$field = new Solr_BDQ_Search_SearchField(
    			$qQ,
    			SEARCH_QUESTION,
    			$searchLang
    		);
    		$query .= $field->getQuery();
    	}

    	if ($mQ)
    	{
    		$field = new Solr_BDQ_Search_SearchField(
    			$mQ,
    			SEARCH_MODALITIES,
    			$searchLang
    		);
    		$query .= $field->getQuery();
    	}

    	if ($vQ)
    	{
    		$field = new Solr_BDQ_Search_SearchField(
    			$vQ,
    			SEARCH_VARIABLE,
    			$searchLang
    		);
    		$query .= $field->getQuery();
    	}
    	
    	if ($qmQ)
    	{
	    	$field = new Solr_BDQ_Search_SearchField(
    			$qmQ,
    			SEARCH_QUESTION | SEARCH_MODALITIES,
    			$searchLang
    		);
    		$query .= $field->getQuery();
    	}

    	if ($mvQ)
    	{
    		$field = new Solr_BDQ_Search_SearchField(
    			$mvQ,
    			SEARCH_MODALITIES | SEARCH_VARIABLE,
    			$searchLang
    		);
    		$query .= $field->getQuery();
    	}

    	if ($qvQ)
    	{
    		$field = new Solr_BDQ_Search_SearchField(
    			$qvQ,
    			SEARCH_QUESTION | SEARCH_VARIABLE,
    			$searchLang
    		);
    		$query .= $field->getQuery();
    	}
    	
    	if ($qmvQ)
    	{
    		$field = new Solr_BDQ_Search_SearchField(
    			$qmvQ,
    			SEARCH_QUESTION | SEARCH_MODALITIES | SEARCH_VARIABLE,
    			$searchLang
    		);
    		$query .= $field->getQuery();
    	}

    	return $query;
    }
    
    /**
     * @param Solr_BDQ_Search_Search $search
     * @return void
     */
    protected function _addKeywordFilterQueries(Solr_BDQ_Search_Search $search)
    {
    	$filters = $this->_request->getBDQParam('queryFilters');

  		if ($filters === URL_PARAM_NULL)
    	{
    		return;
    	}
    	
   	  	$filters = explode(URL_PARAM_SEPARATOR, $filters);
    	$fq = "{!tag=queryFilters}(";
    	$fq .= $this->_getQueryFiltersQuery($filters);
    	$fq .= ')';
    	$search->addFq($fq);
    }
    
    /**
     * @param Solr_BDQ_Search_FulltextSearch $search
     * @param Solr_Paginator $paginator
     * @return void
     */
    protected function _addSearchResultsViewParams($search, $paginator)
    {
    	$this->view->search = $search;
    	$this->view->searchLang = $searchLang = $this->_request->getBDQParam('searchLang');

    	if (($queryFilters = $this->_request->getBDQParam('queryFilters')) !== URL_PARAM_NULL)
    	{
    		$search->hl = false;
    		$search->facetOnly = true;
    	}

    	try
    	{
    		$response = $paginator->getCurrentItems();
    	}

    	catch (Solr_ClientHTTPCodeException $e)
    	{
    		$this->view->searchError = true;
    		return;
    	}

  		if ($queryFilters !== URL_PARAM_NULL):
    		$search->hl = true;
    		$select = $search->getSelect();
    		$select = clone $select;
    		$select->setFl(
    			Solr_BDQ_Search_Search::$fl
    		);
	    	$select->setQ(
	    		$select->getQ()
	    		. ' ' .
	    		$this->_getQueryFiltersQuery(
	    			explode(
	    				URL_PARAM_SEPARATOR,
	    				$queryFilters
	    			)
	    		)
	    	);
	    	$fqs = $select->getFqs();
	    	$select->setHl(true);
	    	$select->setFqs($fqs);
	    	$select->setFacet(false);
	    	$select->setRows(
	    		$this->_clientSettings->rows
	    	);
	    	$_response = $select->send();
	    	$response->response['response'] = $_response->response['response'];
	    	$response->response['highlighting'] = $_response->response['highlighting'];
    	endif;

    	$response->createDocuments();
    	$this->view->response = $response;

    	if
    	(
    		(isset($response->response['spellcheck']) && $response->response['spellcheck']['suggestions'] !== array())
    		&&
    		! ($this->_clientSettings->stemming && $response->response['response']['numFound'] != 0)
    	)
    	{

    		if ($validQueries = $this->_getSpellcheckedQueries($response->response['spellcheck']['suggestions']))
    		{
    			$this->view->validQueries = $validQueries;
    		}
    		
    		elseif ( ! queryUseLuceneFunctionOrOperator($this->_request->getBDQParam('query')) && $querySuggestions = $this->_getQuerySuggestions())
    		{
    			$this->view->querySuggestions = $querySuggestions;
    		}
    		
    	}

    	elseif ($response->response['response']['numFound'] == 0)
    	{

    		if ( ! queryUseLuceneFunctionOrOperator($this->_request->getBDQParam('query')))
    		{
    			$this->view->querySuggestions = $this->_getQuerySuggestions();
    		}
    		
    	}

		$this->view->paginator = $paginator;
		$this->view->sort = $this->_request->getBDQParam('sort');
		$this->view->sortOrdersLabels = $this->_sortOrdersLabels;
		$this->view->domainByLangs = DB_Mapper_Domain::getDomainByLangs(
			$this->_translationLanguageGuiCurrent->get_id()
		);
		
		$domainList = $this->_getItemList(
			$this->_getFacetsDbIds(
				$response->response['facet_counts']['facet_fields']['domainId'],
				NULL,
				0,
				false
			),
			'DB_Mapper_Domain',
			true
		);
		$this->view->domainList = $domainList;
		
		$this->view->domainFacets = $this->_formatFacets(
			$response->response['facet_counts']['facet_fields']['domainId'],
			$domainList,
			$this->_translate->_('fr0020000300'),
			false
		);
		
		$studySerieList = $this->_getItemList(
			$this->_getFacetsDbIds(
				$response->response['facet_counts']['facet_fields']['studySerieId'],
				NULL,
				0,
				false
			),
			'DB_Mapper_StudySerie',
			true
		);
		
		$this->view->studySerieFacets = $this->_formatFacets(
			$response->response['facet_counts']['facet_fields']['studySerieId'],
			$studySerieList,
			$this->_translate->_('fr0020000350'),
			false
		);

		$studyList = $this->_getItemList(
			$this->_getFacetsDbIds(
				$response->response['facet_counts']['facet_fields']['studyDescriptionId'],
				NULL,
				0,
				false
			),
			'DB_Mapper_StudyDescription'
		);
		
		$this->view->studyFacets = $this->_formatFacets(
			$response->response['facet_counts']['facet_fields']['studyDescriptionId'],
			$studyList,
			'',
			false
		);
		
		$decadeFacets = $response->response['facet_counts']['facet_fields']['studyDecade'];
		$l = count($decadeFacets);
		$decadeList = array();
		
		for ($i = 0; $i < $l; $i+=2)
		{
			$decade = $decadeFacets[$i];
			$decadeList[] = array(
				'id' => $decade,
				'title' =>	($decade - 5) . ' - ' . ($decade + 4)
			);
		}
		

		$this->view->decadeFacets = $this->_formatFacets(
			$decadeFacets,
			$decadeList,
			'',
			false
		);
		
		if (isset($response->response['facet_counts']['facet_fields']['conceptId']))
		{
			$conceptList = $this->_getItemList(
	    		$this->_getFacetsDbIds(
					$response->response['facet_counts']['facet_fields']['conceptId'],
					NULL,
					0,
					false
				),
				'DB_Mapper_Concept',
				true
	    	);

	    	$this->view->conceptFacets = $this->_formatFacets(
	    		$response->response['facet_counts']['facet_fields']['conceptId'],
	    		$conceptList,
	    		$this->_translate->_('fr0020000400'),
	    		false
	    	);

	    	$this->view->conceptFilters = $this->_filterParamToArray('conceptIds');
	    	$this->view->conceptTitles = $conceptList;
	    }

		$this->view->studySerieFilters = $this->_filterParamToArray('studySerieIds');
		$this->view->decadeFilters = $this->_filterParamToArray('decades');
		$this->view->queryFilters = $this->_filterParamToArray('queryFilters');
		$this->view->studyFilters = $this->_filterParamToArray('studyIds');
		$this->view->domainFilters = $this->_filterParamToArray('domainIds');
//		echo $search->getSelect();
    }

    /**
     * @param string $paramName
     * @return array
     */
    protected function _filterParamToArray($paramName)
    {
    	
    	if ($this->_request->getBDQParam($paramName) !== URL_PARAM_NULL)
		{
			return explode(URL_PARAM_SEPARATOR, $this->_request->getBDQParam($paramName));
		}
		
		else
		{
			return array();
		}
		
    }

    /**
     * @param array $solrFacets
     * @param string $separator
     * @param int $idPos
     * @return array
     */
    protected function _getFacetsDbIds($solrFacets, $separator = NULL, $idPos = 0, $keepNoResult = true)
    {
    	$l = count($solrFacets);
		$_solrFacets = array();

		for ($i = 0; $i < $l; $i += 2)
		{
			$facet = $solrFacets[$i];
			
			if ($facet == SOLR_NULL)
			{
				continue;
			}

			if ($separator !== NULL)
			{
				$facet = explode($separator, $facet);
				$facet =  $facet[$idPos];
			}

			if( ! $keepNoResult && $solrFacets[$i + 1] == 0)
			{
				continue;
			}

			$_solrFacets[] = (int)$facet;

		}
		
		return $_solrFacets;
    }
    
    /**
     * @param array $solrFacets
     * @param array $rowList
     * @param string $nulllabel
     * @param boolean $keepNoResultGroup
     * @return array
     */
    protected function _formatFacets($solrFacets, $rowList, $nullLabel, $keepNoResultGroup = false)
    {
    	$l = count($solrFacets);
		$_solrFacets = array();

		for ($i = 0; $i < $l; $i += 2)
		{

			if ($solrFacets[$i + 1] == 0)
			{

				if ( ! $keepNoResultGroup)
				{
					continue;
				}

			}
			
			$id = $solrFacets[$i];
			$object = binarySearch($id, $rowList);
			
			if ($object == -1)
			{
				$id = 0;
			}
			
			$_solrFacets[] = $id;
			$_solrFacets[] = $object != -1 ? $object['title'] : $nullLabel;
			$_solrFacets[] = $solrFacets[$i + 1];
		}
		
		return $_solrFacets;
    }
    
    /**
     * @param array $rawSuggestions
     * @return array
     */
	protected function _getSpellcheckedQueries($rawSuggestions)
    {
    	$validQueries = array();
    	$query = $this->_request->getBDQParam('query');
    	$query = normalize($query);
    	$sugg = $this->_getMaxSuggestions($rawSuggestions);
    	$sugg = cartesianProduct($sugg);
    	$select = $this->_getFireSelect();
    	$fieldName = Solr_BDQ_Search_SearchField::getTarget($this->_request->getBDQParam('target'))
    	. BDQ_Settings_Client::getAnalysisCode()
    	. $this->_request->getBDQParam('searchLang');
    	$l = count($sugg);

    	if ($l > 0)
    	{
    		$l2 = count($sugg[0]);
    	}

    	for ($i = 0; $i < $l; $i ++)
    	{

    		$_query = $query;

			for ($j = 0; $j < $l2; $j++)
			{
				$rAs = explode(' ', $sugg[$i][$j]);
				$_query = str_replace($rAs[1], $rAs[0], $_query);
			}

			$select->setQ("$fieldName:($_query)");
			$response = $select->send();
			
			if ($response->response['response']['numFound'] > 0)
			{
				$validQueries[] = utf8_encode($_query) . URL_PARAM_SEPARATOR . $response->response['response']['numFound'];
			}
			
    	}

    	return $validQueries;
    	
    }
    
    /**
     * @param array $rawSuggestions
     * @return 	array
     */
    protected function _getMaxSuggestions($rawSuggestions)
    {
    	$l = count($rawSuggestions);
    	$terms = array();
    	$_terms = array();
    	$validQueries = array();
    	$card = 1;
    	
    	/*
    	 * The goals of this loop are :
    	 * 1) 	To find the possible number of spellchecked queries
    	 * 2) 	To create an array that will be easily sorted by spellcheck suggestions count.
    	 * 		The (quick and dirty) algorithm need that to create groups of suggestions.
    	 * 3)	This algorithm will be used only if there are more possibilities of queries combos
    	 * 		than allowed.
    	 */
    	   	 
    	for ($i = 1; $i < $l; $i +=2)
    	{
    		$c = count($rawSuggestions[$i]['suggestion']);
    		$card *= $c;
    		
    		for ($j = 0; $j < $c; $j++)
    		{
    			$rawSuggestions[$i]['suggestion'][$j] .= ' ' . $rawSuggestions[$i -1]; 
    			/*
    			 * The suggestion and the text that it will replace (thanks to explode + str_replace)
    			 * are separated by a space.
    			 */ 
    		}
    		
    		if (array_key_exists($c, $terms)) 
    		/*
    		 * Several words have the same suggestions count
    		 * As word need to be sorted by suggestions count thanks to ksort we store them in an array
    		 * indexed by their suggestion count
    		 */ 
    		{

    			if (is_array($terms[$c][0]))
    			{
    				$terms[$c][] = $rawSuggestions[$i]['suggestion'];
    			}
    			
    			else
    			{
    				$terms[$c] = array($terms[$c], $rawSuggestions[$i]['suggestion']);
    			}
    		}
    		
    		else
    		{
    			$terms[$c] = $rawSuggestions[$i]['suggestion'];
    		}
    		
    		$_terms[] = $rawSuggestions[$i]['suggestion'];

    	}
    	
    	if ($card > MAX_SPELLCHECK_QUERIES) 
    	{
    		$l /= 2;
    		ksort($terms);
    		$n = floor(pow(MAX_SPELLCHECK_QUERIES, 1 / $l));
    		$unused = 0;
    		reset($terms);
    		
    		$_terms = array();
    		$j = 0;
    		
    		while(list($c, $list) = each($terms))
    		{
    			
    			if (is_array($list[0]))
    			{

    				$l2 = count($list);
    				
    				for ($i = 0; $i < $l2; $i++)
    				{
    					
	    				if ($c > ($n + $unused))
		    			{
		    				$_terms[] = array_slice($list[$i], 0, $n + ceil($unused / ($l - $j + 1)));
		    			}
		    			
		    			else
		    			{
		    				$_terms[] = $list[$i];
		    				$unused += $n - $c;
		    			}
		    			
		    			$j++;
    					
    				}
    				
    			}
    			
    			else
    			{
    			
	    			if ($c > ($n + $unused))
	    			{
	    				$_terms[] = array_slice($terms[$c], 0, $n + ceil($unused / ($l - $j + 1)));
	    				$unused = 0;
	    			}
	
	    			else
	    			{
	    				$_terms[] = $terms[$c];
	    				$unused += $n - $c;
	    			}
	    			
	    			$j++;
    			
    			}

    		}
    		
    	}
    	
    	$terms = $_terms;
    	return $terms;
    }
    
    /**
     * @return array
     */
    protected function _getQuerySuggestions()
    {
    	$query = $this->_request->getBDQParam('query');
    	$query = trim($query);
    	$query = preg_split('/\s+/', $query);
    	$query = array_unique($query);
    	
    	if (($l = count($query)) < 2)
    	{
    		return array();
    	}
    	
    	$queries = queryPowerSet($query, $l - 1);
    	$select = $this->_getFireSelect();
    	$fieldName = Solr_BDQ_Search_SearchField::getTarget($this->_request->getBDQParam('target'))
    	. BDQ_Settings_Client::getAnalysisCode()
    	. $this->_request->getBDQParam('searchLang');
    	$suggestions = array();
    	$v = current($queries);
    	$l2 = count($v);
    	
    	$i = 0;
    	
    	while ($i < $l2)
    	{
    		$select->setQ("$fieldName: (" . $v[$i] . ')');
    		$response = $select->send();

			if ($response->response['response']['numFound'] > 0)
			{
				$suggestions[] = utf8_encode($v[$i]) . URL_PARAM_SEPARATOR . $response->response['response']['numFound'];
			}
			
			$i++;

    	}
    	
    	return $suggestions;
    }
     
    /**
     * @return Solr_Select
     */
    protected function _getFireSelect()
    {
   		$select = new Solr_Select;
   		$select->setOmitHeader(true);
    	$select->setFl(array('id'));
    	$select->setStart(0);
    	$select->setRows(0);
    	return $select;
    }
    
    public function searchhandlerAction()
    {
		$query = $this->_request->getQuery('query');
    	$searchLang = $this->_request->getQuery('searchLang');

    	if
    	(
    		$query !== NULL && $query !== '.'
    		&&
    		$searchLang !== NULL && ! empty($searchLang)
    	)
    	{
	    	$target = $this->_buildTarget(
	    		$this->_request->getQuery('searchQuestion'),
	    		$this->_request->getQuery('searchModalities'),
	    		$this->_request->getQuery('searchVariableLabel')
	    	);
    		$this->_redirectToSimpleSearchResults(
    			array(
					'searchLang' => $searchLang,
					'target' => $target,
					'query' => rawurlencode($query),
					'queryFilters' => URL_PARAM_NULL,
					'page' => 1,
					'sort' => array_search($this->_clientSettings->sort, $this->_sortOrders),
					'domainIds' => URL_PARAM_NULL,
					'studySerieIds' => URL_PARAM_NULL,
					'studyIds' => URL_PARAM_NULL,
					'decades' => URL_PARAM_NULL,
					'conceptIds' => URL_PARAM_NULL
				)
    		);
    	}
    	
    	else
    	{
    		$this->_redirectToHome();
    	}
    }
    
	public function clientsettingsAction()
    {
    	$this->view->sortOrders = $this->_sortOrders;
    	$this->view->sortOrdersLabels = $this->_sortOrdersLabels;
    	$this->view->domainByLangs = DB_Mapper_Domain::getDomainByLangs(
			$this->_translationLanguageGuiCurrent->get_id()
		);
		$this->view->lastSearchResultsParams = Zend_Controller_Front::getInstance()->getPlugin('BDQ_HistoryPlugin')->getLastSearchResultsParams();
    }
    
    public function editclientsettingsAction()
    {

    	{
	    	$this->_clientSettings->synonyms = $this->_request->getBDQParam('synonyms') == 'on';
	    	$this->_clientSettings->stemming = $this->_request->getBDQParam('stemming') == 'on';
	    	$this->_clientSettings->stopwords = $this->_request->getBDQParam('stopwords') == 'on';
	    	$this->_clientSettings->autoComplete = $this->_request->getBDQParam('autoComplete') == 'on';
    	}
    	
    	{
    		$this->_clientSettings->displayInterviewerInstructions = $this->_request->getBDQParam('displayInterviewerInstructions') == 'on';
    		$this->_clientSettings->displayPreQuestionText = $this->_request->getBDQParam('displayPreQuestionText') == 'on';
    		$this->_clientSettings->displayPostQuestionText = $this->_request->getBDQParam('displayPostQuestionText') == 'on';
    		$this->_clientSettings->displayConcept = $this->_request->getBDQParam('displayConcept') == 'on';
    		$this->_clientSettings->displayNotes = $this->_request->getBDQParam('displayNotes') == 'on';
    		$this->_clientSettings->displayUniverse = $this->_request->getBDQParam('displayUniverse') == 'on';
    		$this->_clientSettings->displayQuestionPositionDecile = $this->_request->getBDQParam('displayQuestionPositionDecile') == 'on';
    		$this->_clientSettings->displayNavigationBar = $this->_request->getBDQParam('displayNavigationBar') == 'on';
    		$this->_clientSettings->displayQuestionnairePdfLink = $this->_request->getBDQParam('displayQuestionnairePdfLink') == 'on';
    	}

    	$this->_clientSettings->fluoHighlight = $this->_request->getBDQParam('fluoHighlight') == 'on';
    	$this->_clientSettings->sort = $this->_sortOrders[$this->_request->getBDQParam('sort')];
    	$this->_clientSettings->rows = $this->_request->getBDQParam('rows');
    	$this->_clientSettings->setCookie();
    	
    	if ($params = Zend_Controller_Front::getInstance()->getPlugin('BDQ_HistoryPlugin')->getLastSearchResultsParams())
    	{
    		$this->_redirectToLastSearchResults($params);
    	}

    	else
    	{
    		$this->_redirectToSettings();
    	}
    }

	public function resetclientsettingsAction()
    {
    	$this->_clientSettings = BDQ_Settings_Client::getInstance();
    	$this->_clientSettings->loadDefaults();
    	$this->_clientSettings->setCookie();
		
    	if ($params = Zend_Controller_Front::getInstance()->getPlugin('BDQ_HistoryPlugin')->getLastSearchResultsParams())
    	{
    		$this->_redirectToLastSearchResults($params);
    	}

    	else
    	{
    		$this->_redirectToSettings();
    	}
    	
    	die;

    }
    
    /**
     * 
     * @param string $searchQuestion
     * @param string $searchModalities
     * @param string $searchVariableLabel
     * @return int
     */
    protected function _buildTarget
    (
    	$searchQuestion,
    	$searchModalities,
    	$searchVariableLabel
    )
    {
    	$target = 0;
    	
    	if ($searchQuestion == 'on')
    	{
    		$target += SEARCH_QUESTION;
    	}
    	
    	if ($searchModalities == 'on')
    	{
    		$target += SEARCH_MODALITIES;
    	}
    	
    	if ($searchVariableLabel == 'on')
    	{
    		$target += SEARCH_VARIABLE;
    	}
    	
    	if ($target == 0)
    	{
    		$target = 7;
    	}
    	
    	return $target;
    }

    /**
     * @return Solr_Document
     */
    protected function _getReferenceQuestion()
    {
    	$select = new Solr_Select('id:' . $this->_request->getBDQParam('solrId'));
    	$select->setFl(Solr_BDQ_Search_Search::$fl);
    	$response = $select->send();
    	$response->createDocuments();
    	return $response[0];
    }
    
    /**
     * @param array $ids
     * @param string $mapperClassName
     * @param boolean $translate
     * @return array
     */
    protected function _getItemList(array $ids, $mapperClassName, $translate = false)
    {
    	
    	if (empty($ids))
    	{
    		return;
    	}
    	
    	$mapper = new $mapperClassName;
    	
    	return $translate ? $mapper->findAllTitleAndId(
    		$ids,
    		$this->_translationLanguageGuiCurrent->get_id()
    	) : $mapper->findAllTitleAndId(
    		$ids
    	);
    }

    /**
     * @param Solr_BDQ_Search_FulltextSearch $search
     */
    protected function _updateFacets(Solr_BDQ_Search_Search $search)
    {
    	$this->view->layout()->setLayout('empty');
    	$select = $search->getSelect();
    	$select->setFl(array('id'));
		$select->setRows(0);
		$select->setHl(false);
		$select->setSpellcheck(false);
		$facetFields = array(
			"{!ex=domainIds,studySerieIds,studyIds}domainId",
			"{!ex=studySerieIds,studyIds}studySerieId",
			"{!ex=studyIds}studyDescriptionId",
			"{!ex=decades,domainIds,studySerieIds,studyIds}studyDecade",
		);

		if ($this->_clientSettings->displayConcept)
		{
			$facetFields[] = "{!ex=conceptIds,studyIds}conceptId";
		}

		$select->setFacetFields($facetFields);
		$fqs = array();

		if ($search instanceOf Solr_BDQ_Search_FacetSearch)
		{
			$fqs[] = $search->getFirstFq();
		}

		else
		{
			$fqs[] = 'solrLangCode:' . $this->_request->getBDQParam('searchLang');
		}

    	if ($this->_request->getBDQParam('domainFilters'))
		{
			$domains = $this->_request->getBDQParam('domainFilters');
	    	$fq = '{!tag=domainIds}domainId:(';
	    	$l = count($domains);
	    	
	    	for ($i = 0; $i < $l; $i++)
	    	{
	    		$fq .= $domains[$i];
	    		
	    		if ($i < $l - 1)
	    		{
	    			$fq .= ' OR ';
	    		}
	    		
	    	}
	    	
	    	$fq .= ')';
	    	$fqs[] = $fq;
		}

    	if ($this->_request->getBDQParam('conceptFilters'))
		{
			$concepts = $this->_request->getBDQParam('conceptFilters');
	    	$fq = '{!tag=conceptIds}conceptId:(';
	    	$l = count($concepts);
	    	
	    	for ($i = 0; $i < $l; $i++)
	    	{
	    		$fq .= $concepts[$i];
	    		
	    		if ($i < $l - 1)
	    		{
	    			$fq .= ' OR ';
	    		}
	    		
	    	}
	    	
	    	$fq .= ')';
	    	$fqs[] = $fq;
		}

		if ($this->_request->getBDQParam('studySerieFilters'))
		{
			$studySeries = $this->_request->getBDQParam('studySerieFilters');
	    	$fq = '{!tag=studySerieIds}studySerieId:(';
	    	$l = count($studySeries);
	    	
	    	for ($i = 0; $i < $l; $i++)
	    	{
	    		$fq .= $studySeries[$i];
	    		
	    		if ($i < $l - 1)
	    		{
	    			$fq .= ' OR ';
	    		}
	    		
	    	}
	    	
	    	$fq .= ')';
	    	$fqs[] = $fq;
		}

   		if ($this->_request->getBDQParam('studyFilters'))
		{
			$studies = $this->_request->getBDQParam('studyFilters');
	    	$fq = '{!tag=studyIds}studyDescriptionId:(';
	    	$l = count($studies);
	    	
	    	for ($i = 0; $i < $l; $i++)
	    	{
	    		$fq .= $studies[$i];
	    		
	    		if ($i < $l - 1)
	    		{
	    			$fq .= ' OR ';
	    		}
	    		
	    	}
	    	
	    	$fq .= ')';
	    	$fqs[] = $fq;
		}

		if ($this->_request->getBDQParam('decadeFilters'))
		{
			$selectedDecades = $this->_request->getBDQParam('decadeFilters');
			$fq = '{!tag=decades}studyDecade:(';
	    	$l = count($selectedDecades);
	    	
	    	for ($i = 0; $i < $l; $i++)
	    	{
	    		$fq .= $selectedDecades[$i];
	    		
	    		if ($i < $l - 1)
	    		{
	    			$fq .= ' OR ';
	    		}
	    		
	    	}
	    	
	    	$fq .= ')';
	    	
	    	$fqs[] = $fq;
		}

   		if ($this->_request->getBDQParam('queryFilters2'))
		{
			$analysisCode = BDQ_Settings_Client::getAnalysisCode();
			$searchLang = $search->getSearchLang();
			$keywordFilters = $this->_request->getBDQParam('queryFilters2');
	    	$l = count($keywordFilters);
	    	$checkedQueries = array();
	    	$facetQueries = array();
	    	
	    	for ($i = 0; $i < $l; $i++)
	    	{
	    		$keywordFilter = $keywordFilters[$i];
				$isChecked = substr($keywordFilter, -1);
				$kfTargetInt = substr($keywordFilter, -2, 1);
				$keywordFilter = substr($keywordFilter, 0, -2);
				$kfTarget = Solr_BDQ_Search_SearchField::getTarget($kfTargetInt);
				$fieldName = $kfTarget . $analysisCode . $searchLang;
				$fQuery = "$fieldName:($keywordFilter)";
				
				if ($isChecked)
				{
		    		$checkedQueries[] = $keywordFilter . $kfTargetInt;
				}

	    		$facetQueries[] = $fQuery;
	    	}

	    	if (count($checkedQueries) > 0)
	    	{
	    		$fq = $this->_getQueryFiltersQuery($checkedQueries);
	    		$fqs[] = $fq;
	    	}

	    	$select->setFacetQueries($facetQueries);
		}

		$select->setFqs($fqs);
		
		$response = $select->send();
		$facets = array();
		$facets['numFound'] = & $response->response['response']['numFound'];
		$facets['studies'] = & $response->response['facet_counts']['facet_fields']['studyDescriptionId'];
		$facets['domains'] = & $response->response['facet_counts']['facet_fields']['domainId'];
		$facets['studySeries'] = & $response->response['facet_counts']['facet_fields']['studySerieId'];
		$facets['decades'] = & $response->response['facet_counts']['facet_fields']['studyDecade'];
		$facets['keywords'] = isset($response->response['facet_counts']['facet_queries']) ? $response->response['facet_counts']['facet_queries'] : NULL;
		$keywords = array();

		while (list($k, $v) = each($facets['keywords']))
		{
			$i = strpos($k, ':');
			$target = substr($k, 0, $i);
			$target = str_replace($analysisCode, '', $target);
			$target = str_replace($searchLang, '', $target);
			$target = Solr_BDQ_Search_SearchField::getCode($target);
			$query = substr($k, $i + 2, -1);
			$keywords[$query . $target] = $v;
		}
		
		$facets['keywords'] = $keywords;

		if (isset($response->response['facet_counts']['facet_fields']['conceptId']))
		{
	    	$facets['concepts'] = $response->response['facet_counts']['facet_fields']['conceptId'];
	    }
	    
		echo json_encode($facets);die;
    }
    
    
    
    /**
     * @param array $redirectionMethod
     * @return void
     */
    protected function _filterResults($redirectionMethod)
    {
		$params = array();
		$params['conceptIds'] = $this->_getResultFilterParam('conceptFilters');
		$params['domainIds'] = $this->_getResultFilterParam('domainFilters');
    	$params['studySerieIds'] = $this->_getResultFilterParam('studySerieFilters');
    	$params['decades'] = $this->_getResultFilterParam('decadeFilters');
    	$params['queryFilters'] = $this->_getResultFilterParam('queryFilters2');
    	$params['studyIds'] = $this->_getResultFilterParam('studyFilters');
    	$params['page'] = 1;
    	$this->$redirectionMethod($params);
    }

    /**
     * @param string $getParamName the query string param that is going to be added
     * @param boolean $mergeWithCurrent keep the current value in the request param
     * @param string $currentParamName the current value request param name
     * @return string
     */
    protected function _getResultFilterParam($getParamName, $mergeWithCurrent = false, $currentParamName = '')
	{
		$filters = $this->_request->getBDQParam($getParamName);
		
		if ($filters == NULL)
		{
			$filters = array();
		}
		
		if ($mergeWithCurrent && $filters != array())
		{
			$current = $this->_request->getBDQParam($currentParamName);
			
			if ($current != URL_PARAM_NULL)
			{
				$current = explode(URL_PARAM_SEPARATOR, $current);
				$filters = array_merge($filters, $current);
				$filters = array_unique($filters);
			}
			
		}
		
		$filters = implode(URL_PARAM_SEPARATOR, $filters);
		return $filters !== '' ? rawurlencode($filters) : URL_PARAM_NULL;
	}
	
	public function advancedsearchAction()
	{
		$this->view->domainByLangs = DB_Mapper_Domain::getDomainByLangs(
			$this->_translationLanguageGuiCurrent->get_id()
		);
		
		$this->view->advancedSearchConditions = $this->_getAdvancedSearchConditions();;
		$this->view->searchLang = $this->_request->getBDQParam('searchLang');
	}
	
	public function advancedsearchhandlerAction()
	{
		$params = array();
		$params['searchLang'] = $this->_request->getBDQParam('searchLang');
		$operators = $this->_request->getBDQParam('operators');
		$targets = $this->_request->getBDQParam('targets');
		$analysis = $this->_request->getBDQParam('analysis');
		$distanceValues = $this->_request->getBDQParam('distanceValues');
		$levenshteins = $this->_request->getBDQParam('levenshteins');
		$distanceValues = $this->_request->getBDQParam('distanceValues');
		$keywords = $this->_request->getBDQParam('keywords');
		
		$l = count($keywords);
		
		for ($i = 0; $i < $l; $i ++)
		{
			
			if (empty($keywords[$i]))
			{
				unset($keywords[$i]);
				unset($levenshteins[$i]);
				unset($distanceValues[$i]);
				unset($analysis[$i]);
				unset($targets[$i]);
				unset($operators[$i]);
			}
			
		}
		
		if (empty($keywords))
		{
			$this->_helper->getHelper('Redirector')->setGotoRoute(	
				array(),
				'solradvancedSearch'
			);
			$this->_helper->getHelper('Redirector')->redirectAndExit();
		}
		
		$params['operators'] = $this->_getAdvancedSearchArrayGetParam($operators);
		$params['targets'] = $this->_getAdvancedSearchArrayGetParam($targets);
		$params['analysis'] = $this->_getAdvancedSearchArrayGetParam($analysis);
		$params['levenshteins'] = $this->_getAdvancedSearchArrayGetParam($levenshteins);
		$params['distanceValues'] = $this->_getAdvancedSearchArrayGetParam($distanceValues);
		$params['keywords'] = $this->_getAdvancedSearchArrayGetParam($keywords);
		
		$params['queryFilters'] = URL_PARAM_NULL;
		$params['domainIds'] = URL_PARAM_NULL;
		$params['studySerieIds'] = URL_PARAM_NULL;
		$params['studyIds'] = URL_PARAM_NULL;
		$params['conceptIds'] = URL_PARAM_NULL;
		$params['decades'] = URL_PARAM_NULL;
		$params['page'] = 1;
		$params['sort'] = array_search($this->_clientSettings->sort, $this->_sortOrders);
		$this->_redirectToAdvancedSearchResults($params);
	}

	/**
	 * @param array $param
	 * @return array
	 */
	protected function _getAdvancedSearchArrayGetParam(array $param)
	{
		$param = implode(QUERY_FILTER_SEPARATOR, $param);
		$param = utf8_encode($param);
		$param = rawurlencode($param);
		return $param;
	}

	protected function _advancedSearchFactory()
	{
		$searchLang = $this->_request->getBDQParam('searchLang');
		$targets = $this->_getAdvancedSearchArrayRouterParam('targets');
		$analysis = $this->_getAdvancedSearchArrayRouterParam('analysis');
		$distanceValues = $this->_getAdvancedSearchArrayRouterParam('distanceValues');
		$keywords = $this->_getAdvancedSearchArrayRouterParam('keywords');
		$levenshteins = $this->_getAdvancedSearchArrayRouterParam('levenshteins');
		$operators = $this->_getAdvancedSearchArrayRouterParam('operators');
		$operators = array_values($operators);
		$fields = array();
		$l = count($targets);
		
		for ($i = 0; $i < $l; $i++):
			$keyword = $keywords[$i];
			
			if (empty($keyword))
			{
				continue;
			}
			
			$distanceValue = $distanceValues[$i];
			$levenshtein = $levenshteins[$i];
			$_keyword = trim($keyword);
			
			switch($analysis[$i])
			{

				case ADVANCED_SEARCH_ANALYSIS_STARTS_WITH:
					$_keyword = "$_keyword*";
					break;

				case ADVANCED_SEARCH_ANALYSIS_LEVENSHTEIN:
					$_keyword = "$_keyword~0.$levenshtein";
					break;

				case ADVANCED_SEARCH_ANALYSIS_DISTANCE:
					$_keyword = "\"$_keyword\"~$distanceValue";
					break;
				
				case ADVANCED_SEARCH_ANALYSIS_PHRASE_TRUE:
					$_keyword = "\"$_keyword\"";
					break;
					
				case ADVANCED_SEARCH_ANALYSIS_ALL_TERMS_REQUIRED_FALSE:
					$_keyword = preg_replace('/\s+/', ' ', $_keyword);
					$_keyword = explode(' ', $_keyword);
					$_keyword = implode(' OR ', $_keyword);
					$_keyword = "($_keyword)";
					break;

			}
			
			$field = new Solr_BDQ_Search_SearchField($_keyword, $targets[$i], $searchLang);

			switch ($operators[$i])
			{

				case ADVANCED_SEARCH_OR_OPERATOR:
					$field->required = false;
					break;

				case ADVANCED_SEARCH_NOT_OPERATOR:
					$field->not = true;
					break;
			}
			
			$fields[] = $field;

		endfor;
		
		if (empty($fields))
		{
			$this->_helper->getHelper('Redirector')->setGotoRoute(	
				array(),
				'solradvancedSearch'
			);
			$this->_helper->getHelper('Redirector')->redirectAndExit();
		}

		$search = new Solr_BDQ_Search_AdvancedSearch($fields);
    	$this->_addFilterQueries($search, 'domainIds', 'domainId', 'OR');
    	$this->_addFilterQueries($search, 'studySerieIds', 'studySerieId', 'OR');
		$this->_addFilterQueries($search, 'studyIds', 'studyDescriptionId', 'OR');
		$this->_addFilterQueries($search, 'decades', 'studyDecade', 'OR');
		$this->_addFilterQueries($search, 'conceptIds', 'conceptId', 'OR');
		$this->_addKeywordFilterQueries($search);

    	$search->sort = array($this->_sortOrders[$this->_request->getBDQParam('sort')]);
    	
    	if ($this->_clientSettings->displayConcept)
    	{
    		$search->facetOnConcept = true;
    	}
    	
    	return $search;
	}
	
	/**
	 * @param array $paramName
	 * @return void
	 */
	protected function _getAdvancedSearchArrayRouterParam($paramName)
	{
		$param = $this->_request->getBDQParam($paramName);
		
		if ($param !== URL_PARAM_NULL)
		{
			return explode(QUERY_FILTER_SEPARATOR, $param);
		}
		
	}
	
	public function advancedsearchresultsAction()
	{
		$search = $this->_advancedSearchFactory();
		
		if ($this->_request->getBDQParam('updateFacets'))
		{
			$this->_updateFacets($search);
		}
		
		if ($this->_request->getBDQParam('filterResults'))
		{
			$this->_filterResults('_redirectToAdvancedSearchResults');
		}
		
		else
		{
			$paginator = new Zend_Paginator(new Solr_Paginator($search));
	    	
			try
	    	{
	    		$paginator->setItemCountPerPage($this->_clientSettings->rows);
	    	}
	    	
	    	catch (Solr_ClientHTTPCodeException $e)
	    	{
	    		$this->view->searchError = true;
	    		$this->view->search = $search;
	    		$this->renderScript('solrsearch/searchresults.phtml');
	    		return;
	    	}
			
			$paginator->setCurrentPageNumber($this->_request->getBDQParam('page'));
			$this->_addAdvancedSearchViewParams();
			$this->_addSearchResultsViewParams($search, $paginator);
			$this->_router->setGlobalParam('query', $search->getQuery());
	    	$this->renderScript('solrsearch/searchresults.phtml');
		}

	}
	
	protected function _addAdvancedSearchViewParams()
	{
		$this->view->advancedSearchConditions = $this->_getAdvancedSearchConditions();
    	$this->view->searchLang = $this->_request->getBDQParam('searchLang');
		$this->view->targetsParam = $this->_request->getBDQParam('targets');
		$this->view->analysisParam = $this->_request->getBDQParam('analysis');
		$this->view->distanceValuesParam = $this->_request->getBDQParam('distanceValues');
		$this->view->keywordsParam = $this->_request->getBDQParam('keywords');
		$this->view->levenshteinsParam = $this->_request->getBDQParam('levenshteins');
		$this->view->operatorsParam = $this->_request->getBDQParam('operators');
	}
	
	protected function _getAdvancedSearchConditions()
	{
		$targets = $this->_getAdvancedSearchArrayRouterParam('targets');
		$analysis = $this->_getAdvancedSearchArrayRouterParam('analysis');
		$distanceValues = $this->_getAdvancedSearchArrayRouterParam('distanceValues');
		$keywords = $this->_getAdvancedSearchArrayRouterParam('keywords');
		$levenshteins = $this->_getAdvancedSearchArrayRouterParam('levenshteins');
		$operators = $this->_getAdvancedSearchArrayRouterParam('operators');
		
		$l = count($targets);
		$conditions = array();
		
		for ($i = 0; $i < $l; $i++)
		{
			$condition = array();
			$condition['target'] = $targets[$i];
			$condition['analysis'] = $analysis[$i];
			$condition['distanceValue'] = $distanceValues[$i];
			$condition['keyword'] = $keywords[$i];
			$condition['levenshtein'] = $levenshteins[$i];
			$condition['operator'] = $operators[$i];
			$conditions[] = $condition;
		}
		
		return $conditions;
	}
	
	public function studyviewAction()
	{

		if ($this->_request->getBDQParam('filterResults'))
		{
			$this->_filterResults('_redirectToStudyView');
		}

		$id = $this->_request->getBDQParam('id');
		$page = $this->_request->getBDQParam('page');
		$search = new Solr_BDQ_Search_StudySearch($id, $this->_request->getBDQParam('searchLang'));
		$search->sort = array($this->_sortOrders[$this->_request->getBDQParam('sort')]);

		if ($this->_clientSettings->displayConcept)
    	{
    		$search->facetOnConcept = true;
    		$this->_addFilterQueries($search, 'conceptIds', 'conceptId', 'OR');
    	}
		
		$this->_addKeywordFilterQueries($search);
		
		if ($this->_request->getBDQParam('updateFacets'))
		{
			$this->_updateFacets($search);
		}

		$paginator = new Zend_Paginator(new Solr_Paginator($search));

		try
    	{
    		$paginator->setItemCountPerPage($this->_clientSettings->rows);
    	}

    	catch (Solr_ClientHTTPCodeException $e)
    	{
    		$this->view->searchError = true;
    		$this->view->search = $search;
    		$this->renderScript('solrsearch/searchresults.phtml');
    		return;
    	}

		$paginator->setCurrentPageNumber($this->_request->getBDQParam('page'));
		$this->_addSearchResultsViewParams($search, $paginator);
		$this->renderScript('solrsearch/searchresults.phtml');
	}
	
	public function conceptviewAction()
	{
		
		if ($this->_request->getBDQParam('filterResults'))
		{
			$this->_filterResults('_redirectToConceptView');
		}
		
		$id = $this->_request->getBDQParam('id');
		
		$mapper = new DB_Mapper_Concept;
		$this->view->concept = $mapper->findTitleTranslation($id, $this->_translationLanguageGuiCurrent->get_id());
		
		$page = $this->_request->getBDQParam('page');
		$search = new Solr_BDQ_Search_ConceptSearch($id, $this->_request->getBDQParam('searchLang'));
		$search->sort = array($this->_sortOrders[$this->_request->getBDQParam('sort')]);
		$search->facetOnConcept = true;
		
		$this->_addKeywordFilterQueries($search);
		$this->_addFilterQueries($search, 'domainIds', 'domainId', 'OR');
    	$this->_addFilterQueries($search, 'studySerieIds', 'studySerieId', 'OR');
		$this->_addFilterQueries($search, 'studyIds', 'studyDescriptionId', 'OR');
		$this->_addFilterQueries($search, 'decades', 'studyDecade', 'OR');
		
		if ($this->_request->getBDQParam('updateFacets'))
		{
			$this->_updateFacets($search);
		}
		
		$paginator = new Zend_Paginator(new Solr_Paginator($search));

		try
    	{
    		$paginator->setItemCountPerPage($this->_clientSettings->rows);
    	}

    	catch (Solr_ClientHTTPCodeException $e)
    	{
    		$this->view->searchError = true;
    		$this->view->search = $search;
    		$this->renderScript('solrsearch/searchresults.phtml');
    		return;
    	}

		$paginator->setCurrentPageNumber($this->_request->getBDQParam('page'));
		$this->_addSearchResultsViewParams($search, $paginator);
		$this->renderScript('solrsearch/searchresults.phtml');
	}
	
	public function serieviewAction()
	{
		
		if ($this->_request->getBDQParam('filterResults'))
		{
			$this->_filterResults('_redirectToSerieView');
		}
		
		$id = $this->_request->getBDQParam('id');
		
		$mapper = new DB_Mapper_StudySerie;
		$this->view->serie = $mapper->findWithDetails($id, $this->_translationLanguageGuiCurrent->get_id());

		$page = $this->_request->getBDQParam('page');
		$search = new Solr_BDQ_Search_SerieSearch($id, $this->_request->getBDQParam('searchLang'));
		$search->sort = array($this->_sortOrders[$this->_request->getBDQParam('sort')]);
		
		if ($this->_clientSettings->displayConcept)
    	{
    		$search->facetOnConcept = true;
    		$this->_addFilterQueries($search, 'conceptIds', 'conceptId', 'OR');
    	}

		$this->_addKeywordFilterQueries($search);
		$this->_addFilterQueries($search, 'domainIds', 'domainId', 'OR');
    	$this->_addFilterQueries($search, 'studySerieIds', 'studySerieId', 'OR');
		$this->_addFilterQueries($search, 'studyIds', 'studyDescriptionId', 'OR');
		$this->_addFilterQueries($search, 'decades', 'studyDecade', 'OR');
		
		if ($this->_request->getBDQParam('updateFacets'))
		{
			$this->_updateFacets($search);
		}
		
		$paginator = new Zend_Paginator(new Solr_Paginator($search));

		try
    	{
    		$paginator->setItemCountPerPage($this->_clientSettings->rows);
    	}

    	catch (Solr_ClientHTTPCodeException $e)
    	{
    		$this->view->searchError = true;
    		$this->view->search = $search;
    		$this->renderScript('solrsearch/searchresults.phtml');
    		return;
    	}

		$paginator->setCurrentPageNumber($this->_request->getBDQParam('page'));
		$this->_addSearchResultsViewParams($search, $paginator);
		$this->renderScript('solrsearch/searchresults.phtml');
	}
	
	public function domainviewAction()
	{
		
		if ($this->_request->getBDQParam('filterResults'))
		{
			$this->_filterResults('_redirectToDomainView');
		}
		
		$id = $this->_request->getBDQParam('id');

		$mapper = new DB_Mapper_Domain;
		$this->view->domain = $mapper->findTitleTranslation($id, $this->_translationLanguageGuiCurrent->get_id());

		$page = $this->_request->getBDQParam('page');
		$search = new Solr_BDQ_Search_DomainSearch($id, $this->_request->getBDQParam('searchLang'));
		$search->sort = array($this->_sortOrders[$this->_request->getBDQParam('sort')]);

		if ($this->_clientSettings->displayConcept)
    	{
    		$search->facetOnConcept = true;
    		$this->_addFilterQueries($search, 'conceptIds', 'conceptId', 'OR');
    	}

		$this->_addKeywordFilterQueries($search);
		$this->_addFilterQueries($search, 'domainIds', 'domainId', 'OR');
    	$this->_addFilterQueries($search, 'studySerieIds', 'studySerieId', 'OR');
		$this->_addFilterQueries($search, 'studyIds', 'studyDescriptionId', 'OR');
		$this->_addFilterQueries($search, 'decades', 'studyDecade', 'OR');
		
		if ($this->_request->getBDQParam('updateFacets'))
		{
			$this->_updateFacets($search);
		}
		
		$paginator = new Zend_Paginator(new Solr_Paginator($search));

		try
    	{
    		$paginator->setItemCountPerPage($this->_clientSettings->rows);
    	}

    	catch (Solr_ClientHTTPCodeException $e)
    	{
    		$this->view->searchError = true;
    		$this->view->search = $search;
    		$this->renderScript('solrsearch/searchresults.phtml');
    		return;
    	}

		$paginator->setCurrentPageNumber($this->_request->getBDQParam('page'));
		$this->_addSearchResultsViewParams($search, $paginator);
		$this->renderScript('solrsearch/searchresults.phtml');
	}
	
	/**
	 * @param array $params
	 * @return void
	 */
	protected function _redirectToLastSearchResults(array $params)
    {
  		$params['sort'] = array_search($this->_clientSettings->sort, $this->_sortOrders);
    		
    	if ($params['action'] == 'advancedsearchresults')
    	{
    		$this->_redirectToAdvancedSearchResults($params);
    	}

    	if ($params['action'] == 'searchresults')
    	{
    		$this->_redirectToSimpleSearchResults($params);
    	}

    	if ($params['action'] == 'studyview')
    	{
    		$this->_redirectToStudyView($params);
    	}
    	
    	if ($params['action'] == 'conceptview')
    	{
    		$this->_redirectToConceptView($params);
    	}
    	
   		if ($params['action'] == 'serieview')
    	{
    		$this->_redirectToSerieView($params);
    	}

    }
    
    /**
     * @return void
     */
	protected function _redirectToSettings()
    {
    	$this->_helper->getHelper('Redirector')->setGotoRoute(	
			array(),
			'solrsearchclientSettings'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
    }
	   
    /**
     * @return void
     */
	protected function _redirectToHome()
    {
    	$this->_helper->getHelper('Redirector')->setGotoRoute(	
			array(),
			'solrsearchHome'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
    }
    
    /**
     * @param array $params
     * @return void
     */
    protected function _redirectToSimpleSearchResults(array $params)
    {
    	$params['lang'] = $this->_translationLanguageGuiCurrent->get_code();
    	$this->_helper->getHelper('Redirector')->setGotoRoute(	
			$params,
			'solrsearchResults'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
    }
    
	/**
	 * @param array $paramName
	 * @return void
	 */
	protected function _redirectToAdvancedSearchResults($params)
	{
		$this->_helper->getHelper('Redirector')->setGotoRoute(	
			$params,
			'solradvancedsearchResults'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
	}
	
	/**
	 * @param array $params
	 * @return void
	 */
	protected function _redirectToStudyView(array $params)
	{
		$this->_helper->getHelper('Redirector')->setGotoRoute(	
			$params,
			'studyView'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
	}

	/**
	 * @param array $params
	 * @return void
	 */
	protected function _redirectToConceptView(array $params)
	{
		$this->_helper->getHelper('Redirector')->setGotoRoute(	
			$params,
			'conceptView'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
	}
	
	/**
	 * @param array $params
	 * @return void
	 */
	protected function _redirectToSerieView(array $params)
	{
		$this->_helper->getHelper('Redirector')->setGotoRoute(	
			$params,
			'serieView'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
	}
	
	/**
	 * @param array $params
	 * @return void
	 */
	protected function _redirectToDomainView(array $params)
	{
		$this->_helper->getHelper('Redirector')->setGotoRoute(	
			$params,
			'domainView'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
	}

}