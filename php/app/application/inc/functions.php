<?php
/**
 * Several utility functions.
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */


/**
 * Remove all accents from a string
 * 
 * @param string $string
 * @return string
 */

function normalize ($string)
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

/**
 * Check if the string contains a lucene function or operator.
 * Doesn't look the syntax, validated from the client side.
 * 
 * @param string $query
 * @return boolean
 */
function queryUseLuceneFunctionOrOperator($query)
{
	$pattern = '/^\s*([' . PCRE_WORD_CHARACTERS .']+\s*)+$/';
    	
	if ( ! preg_match($pattern, $query))
	{
		return true;
	}
    	
	if
	(
	(strpos($query, 'AND') !== false)
  	||
	(strpos($query, 'OR') !== false)
  	||
	(strpos($query, 'NOT') !== false)
	)
	{
    		return true;
	}
    	
	return false;
    	
}

/**
 * Query the database and return an associative array containing all nesstar servers ip, port and domain.
 * There shouldn't be more that 4-5 servers.
 * 
 * @return array
 */
function getNesstarServersIpAndPortAndDomain()
{
    $urls = array();
    $mapper = new DB_Mapper_NesstarServer;
    $servers = $mapper->findAll();
    $l = count($servers);
    
    for ($i = 0; $i < $l; $i ++)
    {
    	$urls[$servers[$i]->get_id()]['ip'] = $servers[$i]->get_ip();
    	$urls[$servers[$i]->get_id()]['port'] = $servers[$i]->get_port();
    	$urls[$servers[$i]->get_id()]['domain_name'] = $servers[$i]->get_domain_name();
    }
    
    return $urls;
}

/**
 * 
 * @param int $nesstarServerId Nesstar server database row id.
 * @param string $studyNesstarId DDI file nesstar id.
 * @return string Study description nesstar server URL.
 */
function getStudyNesstarUrl($nesstarServerId, $studyNesstarId)
{
	$serversAndPorts = getNesstarServersIpAndPortAndDomain();
	$domainAndPort = $serversAndPorts[$nesstarServerId]['domain_name'] . ':' . $serversAndPorts[$nesstarServerId]['port'];
	$ipAndPort = $serversAndPorts[$nesstarServerId]['ip'] . ':' . $serversAndPorts[$nesstarServerId]['port'];
	$nsUrl = "http://$domainAndPort";
	$_nsUrl = "http://$ipAndPort";
	$_nsUrl = rawurlencode($_nsUrl);
	return htmlentities(sprintf(
		NESSTAR_STUDY_URL_MASK,
		$nsUrl,
		$_nsUrl,
		$studyNesstarId
	));
}

/**
 * 
 * @param int $nesstarServerId Nesstar server database row id.
 * @param string $studyNesstarId DDI file nesstar id.
 * @param string $variableNesstarId Variable nesstar id.
 * @return string Variable nesstar server URL.
 */
function getVariableNesstarUrl($nesstarServerId, $studyNesstarId, $variableNesstarId)
{
	$serversAndPorts = getNesstarServersIpAndPortAndDomain();
	$domainAndPort = $serversAndPorts[$nesstarServerId]['domain_name'] . ':' . $serversAndPorts[$nesstarServerId]['port'];
	$ipAndPort = $serversAndPorts[$nesstarServerId]['ip'] . ':' . $serversAndPorts[$nesstarServerId]['port'];
	$nsUrl = "http://$domainAndPort";
	$_nsUrl = "http://$ipAndPort";
	$_nsUrl = rawurlencode($_nsUrl);
	return htmlentities(sprintf(
		NESSTAR_VARIABLE_URL_MASK,
		$nsUrl,
		$_nsUrl,
		$studyNesstarId,
		$_nsUrl,
		$studyNesstarId,
		$variableNesstarId
	));
}

/**
 * Array power set.
 * 
 * @param array $in 
 * @param int $number
 * @return array
 */
function queryPowerSet($in, $number)
{
	$count = count($in);
	$members = pow(2,$count);
	$return = array();

	for ($i = 0; $i < $members; $i++)
	{
		$b = sprintf("%0".$count."b",$i);
		$out = array();

		for ($j = 0; $j < $count; $j++)
		{

			if ($b{$j} == '1')
			{
				$out[] = $in[$j];
			}

		}

		if (($c = count($out)) == $number)
		{

			if (array_key_exists($c, $return))
			{
				$return[$c][] = implode(' ', $out);
			}

			else
			{
				$return[$c] = array(implode(' ', $out));
			}

		}

   }

   return $return;
}

/**
 * id binary search in a database result list.
 *  
 * @param id $value The id to look for.
 * @param array $A Multidimensional array. Its elements must have an id key. The array must be sorted by this id key.
 * @return array
 */
function binarySearch($value, $A)
{
	if (empty($A))
	{
		return -1;
	}
	
	$starting = 0;
	$ending = count($A);
	$mid = 0;
	$length = 0;

	while (true)
	{

		if ($starting > $ending)
		{
			return -1;
		}
		
		$length = $ending - $starting;

		if ($length === 0)
		{
			
			if ($value === $A[$starting]['id'])
			{
				return $A[$starting];
			}

			return -1;

		}

		$mid = $starting + intval($length / 2);
         
		if ($value < $A[$mid]['id'])
		{
			$ending = $mid - 1;
		}
		
		else
		{
			
			if ($value > $A[$mid]['id'])
			{
				$starting = $mid + 1;
			}
			
			else
			{
				return $A[$mid];
			}
		}
   
	}
	
	return -1;
}

/**
 * Returns the cartesian products of arrays.
 * 
 * @param array $arrays Array of arrays.
 * @return array
 */
function cartesianProduct($arrays)
{
    $result = array();
    $sizeIn = count($arrays);
    $size = $sizeIn > 0 ? 1 : 0;

    for($i = 0; $i < $sizeIn; $i++)
    {
        $size = $size * count($arrays[$i]);
    }
    
    for ($i = 0; $i < $size; $i ++)
    {
        $result[$i] = array();
        
        for ($j = 0; $j < $sizeIn; $j ++)
        {
            array_push($result[$i], current($arrays[$j]));
        }
        
        for ($j = ($sizeIn -1); $j >= 0; $j --)
        {
        	
            if (next($arrays[$j]))
            {
                break;
            }

            elseif (isset ($arrays[$j]))
            {
                reset($arrays[$j]);
            }

        }
    }
    return $result;
}

/**
 * Returns an associative array containing each stopword file URL. 
 * 
 * @return string
 */
function getStopWordsUrl()
{
	$files = glob(STOPWORDS_FILES . DIRECTORY_SEPARATOR .'*.txt');
	$l = count($files);
	$return = array();
	
	for ($i = 0; $i < $l; $i++)
	{
		$file = basename($files[$i]);
		$name = explode('.',  $file);
		$return[$name[0]] = STOPWORDS_BASEURL . DIRECTORY_SEPARATOR . $file;
	}
	
	return $return;
}

/**
 * Espace the question for the solr query syntax.
 * @param string $query
 * @return array
 */
function getQuestionQueryFilters($query)
{
	$query = htmlspecialchars_decode($query);
	$query = normalize($query);
	$query = strtolower($query); // avoid AND OR NOT
	$query = preg_replace('/\W/', ' ', $query); // removes the unwanted ? * & ! - ... caracters
	$query = preg_replace('/\w+\d+\w*/', '', $query); // removes the question identifiants
	$query = explode(' ', $query);
	$query = array_unique($query);
	$query = array_values($query);

	$l = count($query);
	
	for ($i = 0; $i < $l; $i++)
	{
		
		if (strlen($query[$i]) > 2)
		{
			$_query[] = $query[$i] . SEARCH_QUESTION;
		}

	}
	
	return $_query; 
}