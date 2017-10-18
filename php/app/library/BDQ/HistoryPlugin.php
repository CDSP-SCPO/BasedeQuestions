<?php
/**
 * Stores the last search request params in session.
 * Offers two methods to get the last search results URL, if a search occured in the previous actions.
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 * @package BDQ
 */
class BDQ_HistoryPlugin extends Zend_Controller_Plugin_Abstract
{
	
	/**
	 * @var array
	 */
	protected $_searchActions = array(
		'solrsearch' => array( // the key is a controller
			'advancedsearchresults', // elements are actions
			'searchresults',
			'studyview',
			'conceptview',
			'serieview',
		)
	);
	
	/**
	 * @var Zend_Session_Namespace
	 */
	protected $_lastSearch;

	/**
	 * @param string $lang
	 * @return string last search results URL 
	 */
	public function getLastSearchResultsUrl($lang)
	{
		$params = $this->getLastSearchResultsParams();
		
		if ( ! $params)
		{
			return NULL;
		}
		
		switch ($params['action']):

			case 'advancedsearchresults':
	    		$route = 'solradvancedsearchResults';
	    	break;
	
			case 'searchresults':
	    		$route = 'solrsearchResults';
	    	break;
	
	    	case'studyview':
	    		$route = 'studyView';
	    	break;
	
	    	case 'conceptview':
	    		$route = 'conceptView';
	    	break;
	
	    	case 'serieview':
	    		$route = 'serieView';
	    	break;
    
    	endswitch;

    	$router = Zend_Controller_Front::getInstance();
		$router = $router->getRouter();
		$params['lang'] = $lang;
    	return '/' . $router->getRoute($route)->assemble($params, true, true);
	}

	/**
	 * @see library/Zend/Controller/Plugin/Zend_Controller_Plugin_Abstract#routeShutdown($request)
	 * @return void
	 */
	public function routeShutdown(Zend_Controller_Request_Abstract $request)
	{
		$this->_lastSearch = new Zend_Session_Namespace('lastSearch');
		$this->_addSearchResultsParamsToHistory();
	}

	/**
	 * Add the request params to history if it is a search
	 * @return void
	 */
	protected function _addSearchResultsParamsToHistory()
	{
		$params = $this->_request->getParams();

		if (array_key_exists($params['controller'], $this->_searchActions) && in_array($params['action'], $this->_searchActions[$params['controller']]))
		{
			$this->_lastSearch->params = $params;
		}

	}

	/**
	 * @return array the last search results params
	 */
	public function getLastSearchResultsParams()
	{
			return $this->_lastSearch->params;
	}

}