<?php
/**
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 * @package BDQ
 */
class BDQ_Request extends Zend_Controller_Request_Http
{

	/**
	 * @param Zend_Uri|string $uri
	 * @return BDQ_Request
	 */
	public function __construct($uri = NULL)
	{
		parent::__construct($uri);
	}

	/**
	 * Decodes a param before returning it :
	 * <ul>
	 * <li>utf8_decode</li>
	 * <li>rawurldecode</li>
	 * </ul
	 * @param string $key
	 * @return string|array
	 */
	public function getBDQParam($key)
	{
		$value = parent::getParam($key);
		
		if (is_string($value))
		{
			$value = rawurldecode($value);
		}
		
		if (is_array($value))
		{
			array_walk_recursive($value, function(&$value, $key){
				$value = rawurldecode(utf8_decode($value));
			});
		}
		
		return $value;
	}
	
}