<?php

/**
 * @package Solr
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class Solr_ClientException extends Exception {

	public function __construct($msg = '')
	{
		parent::__construct($msg);
	}
	
}

class Solr_ClientHTTPCodeException extends Solr_ClientException {
	
	public function __construct($msg = '')
	{
		parent::__construct($msg);
	}
	
}

class Solr_ClientNoServerResponseException extends Solr_ClientException {
	
	public function __construct($msg = '')
	{
		parent::__construct($msg);
	}
	
}