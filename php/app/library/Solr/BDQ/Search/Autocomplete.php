<?php

/**
 * @package Solr_BDQ_Search
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_BDQ_Search_Autocomplete {

	/**
	 * @var string
	 */
	const termSeparator = '|||';
	
	/**
	 * @var Solr_Select
	 */
	protected $_select;

	/**
	 * @var string
	 */
	protected $_kw;

	/**
	 * @var string
	 */
	protected $_q;

	/**
	 * @var string
	 */
	protected $_prefix;
		
	/**
	 * @var string
	 */
	protected $_target;

	/**
	 * @var string
	 */
	protected $_lang;
	
	/**
	 * @var boolean
	 */
	protected $_searchQuestion;
	
	/**
	 * @var boolean
	 */
	protected $_searchModalities;
	
	/**
	 * @var boolean
	 */
	protected $_searchVariableLabel;
	
	/**
	 * @var array
	 */
	protected $_terms;
	
	/**
	 * @var BDQ_Settings_Client
	 */
	protected $_clientSettings;
	
	/**
	 * @param string $kw
	 * @param string $lang
	 * @param boolean $searchQuestion
	 * @param boolean $searchModalities
	 * @param boolean $searchVariableLabel
	 * @return Solr_BDQ_Search_Autocomplete
	 */
	public function __construct($kw, $lang, $searchQuestion, $searchModalities, $searchVariableLabel)
	{
		$this->_kw = rawurldecode($kw);
		$this->_kw = utf8_encode($this->_kw);
		$this->_clientSettings = BDQ_Settings_Client::getInstance();
		$this->_lang = strtoupper($lang);
		$this->_kw = str_replace(self::termSeparator, '', $this->_kw);
		$this->_kw = strtolower($this->_kw);
		$this->_kw = $this->_normalize($this->_kw);
		$this->_searchQuestion = $searchQuestion;
		$this->_searchModalities = $searchModalities;
		$this->_searchVariableLabel = $searchVariableLabel;
		$this->_buildTarget();
		$this->_parseTerms();
		$this->_select = new Solr_Select;
	}

	/**
	 * @return Solr_Response
	 */
	public function send()
	{
		return $this->getSelect()->send();
	}

	/**
	 * @return Solr_Select
	 */
	public function getSelect()
	{
		$this->_select->setFacetSort('count');
		$this->_select->setRows(0);
		$this->_select->setQOp('AND');
		$this->_select->setQ($this->_target . $this->_lang . ': (' . $this->_q . ')');
		$this->_select->setOmitHeader(true);
		$this->_addFacet();
		return $this->_select;
	}

	/**
	 * @return array
	 */
	public function getSuggestions()
	{

		if 
		(
			! $this->_searchQuestion
			&&
			! $this->_searchModalities
			&&
			! $this->_searchVariableLabel
		)
		{
			echo '[]';die;
		}
		
		$response = $this->send();
		$facets = $response->response['facet_counts']['facet_fields'][$this->_target . 'AC' . $this->_lang];
		$sugg = array();
		$l = count($facets);
		$sl = strlen($this->_kw);
		$j = 0;
		
		for ($i = 0; $i < $l; $i+=2)
		{
			
			if ( ! in_array($facets[$i], $this->_terms))
			{
				
				if (strpos($facets[$i], ' ') !== false)
				{
					$facets[$i] = '"' . $facets[$i] .'"';
				}
				
				$sugg[] = ($this->_q == '*:*' ? '' : utf8_encode(stripslashes($this->_q)) . ' ') . $facets[$i];
				$j++;
				
				$sugg[] = $facets[$i + 1];


			}
			
			if ($j == 10)
			{
				break;
			}
			
		}

		return $sugg;
	}
	
	/**
	 * @return void
	 */
	protected function _buildTarget()
	{
		
		if ($this->_searchQuestion && $this->_searchModalities && $this->_searchVariableLabel)
		// question and modalities and variable label
		{
			$target = 'qiAndMAndVl';
		}
		
		elseif ($this->_searchQuestion && $this->_searchModalities && ! $this->_searchVariableLabel)
		// question and modalities
		{
			$target = 'qiAndM';	
		}
		
		elseif ($this->_searchQuestion && ! $this->_searchModalities && $this->_searchVariableLabel)
		// question and variable label
		{
			$target = 'qiAndVl';
		}
		
		elseif ( ! $this->_searchQuestion && $this->_searchModalities && $this->_searchVariableLabel)
		// modalities and variable label
		{
			$target = 'mAndVl';
		}
		
		elseif ($this->_searchQuestion && ! $this->_searchModalities && ! $this->_searchVariableLabel)
		// question
		{
			$target = 'qi';
		}
		
		elseif ( ! $this->_searchQuestion && $this->_searchModalities && ! $this->_searchVariableLabel)
		// modalities
		{
			$target = 'm';
		}
		
		elseif ( ! $this->_searchQuestion && ! $this->_searchModalities && $this->_searchVariableLabel)
		// variable label
		{
			$target = 'vl';
		}
				
		else
		{
			$target = 'qiAndMAndVl';
		}
		
		if ( ! $this->_clientSettings->stopwords)
		{
			$target .= 'Sw';
		}
		
		$this->_target = $target;
	}
	
	/**
	 * @return void
	 */
	protected function _parseTerms()
	{
		$terms = preg_replace('/\s+/', ' ', $this->_kw);
		$terms = trim($terms);
		$terms = explode(' ', $terms);
		$prefix = array_pop($terms);
		$this->_terms = $terms;
		
		if ( ! empty($prefix))
		{
			$this->_prefix = $prefix;
			$this->_q = implode(' ', $terms);
			
			$this->_q = ($this->_q == '') ? '*:*' : $this->_q;
		}
		
		else
		{
			echo 'ici';
			echo '[]';die;
		}
		
	}

	/**
	 * @return void
	 */
	protected function _addFacet()
	{
		$this->_select->setFacet(true);
		$this->_select->setFacetLimit(15);
		$this->_select->setFacetMincount(1);
		$this->_select->setFacetFields(array($this->_target . 'AC' . $this->_lang));
		$this->_select->setFacetPrefix($this->_prefix);
	}

	/**
	 * @param string $string
	 * @return string
	 */
	protected function _normalize ($string)
	{
		$table = array(
			'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
			'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
			'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
			'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
			'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
			'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
			'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
			'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
		);
		return strtr($string, $table);
	}

}