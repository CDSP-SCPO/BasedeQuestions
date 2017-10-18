<?php

/**
 * @package Solr
 */

require_once 'Exceptions.php';

/**
 * Handles the communication with the Solr server
 * 
 * <p>Requests are represented in those classes :</p>
 * <ul>
 * <li><b>{@link Solr_Select}</b>, to get documents</li>
 * <li><b>{@link Solr_Delete}</b>, to delete documents</li>
 * <li><b>{@link Solr_Update}</b>, to add or update documents</li>
 * </ul>
 *
 * <br/>
 * 
 * <p>They are sent using {@link Solr_Client::send}.</p>
 * <p>The connection parameters are expected in an ini file specified in {@link iniFile}.</p>
 * <p>When an error occurs, it is logged to a file, specified in {@link logFile}.</p>
 * <p>Implemented as a singleton.</p>
 * 
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 * 
 */
class Solr_Client {

	/**
	 * Path to the ini configuration file
	 * 
	 * <p>Expected informations in the file are :</p>
	 * <ul>
	 * <li>host, (ex: host=localhost)</li>
	 * <li>port, (ex: port=8080)</li>
	 * <li>timeout, (ex: timeout=5)</li>
	 * </ul>
	 * 
	 * @var string
	 */
	public static $iniFile = 'solr.ini';

	/**
	 * Path to the error log
	 * 
	 * @var string
	 */
	public static $logFile = 'error.log';

	/**
	 * @var Solr_Client
	 */
	private static $_instance;

	/**
	 * Solr server host
	 * 
	 * @var string
	 */
	protected $_host;
	
	/**
	 * Solr server url directory, usually solr
	 * 
	 * @var string
	 */
	protected $_urlDirectory;

	/**
	 * Solr server port
	 * 
	 * @var int
	 */
	protected $_port;

	/**
	 * Delay to timeout in seconds
	 * 
	 * @var float
	 */
	protected $_timeout;

	/**
	 * The request to be sent. Given by {@link Solr_Client::send()}.
	 * 
	 * @var Solr_Request
	 */
	protected $_request;

	/**
	 * The response returned by {@link Solr_Client::send()}
	 *  
	 * @var string
	 */
	protected $_response;

	/**
	 * An Solr_Client instance should be built with {@link Solr_Client::getInstance()}
	 * 
	 * @return Solr_Client
	 */
	private function __construct()
	{
		$params = parse_ini_file(self::$iniFile);

		while (list($attributeName, $attributeValue) = each($params))
		{
			$attribute = "_$attributeName";
			$this->$attribute = empty($attributeValue) ? null : $attributeValue;
		}
	}

	/**
	 * Acces to the Solr_Client
	 * 
	 * @return Solr_Client
	 */
	public function getInstance()
	{
		if ( ! self::$_instance)
		{
			self::$_instance = new Solr_Client();
		}

		return self::$_instance;
	}

	/**
	 * Sends an instance of a {@link Solr_Request} subclass.
	 * 
	 * @param Solr_Select|Solr_Update|Solr_Delete $request
	 * @return Solr_Response
	 */
	public function send(Solr_Request $request)
	{

		$this->_request = $request;

		if ($request instanceof Solr_Select)
		{
			$this->_response = $this->_sendSelect();
		}

		elseif ($request instanceof Solr_Update)
		{
			$this->_response = $this->_sendUpdate();
		}

		elseif ($request instanceof Solr_Delete)
		{
			$this->_response = $this->_sendDelete();
		}
		
		else
		{
			trigger_error('Unexpected request type.');
		}

		return new Solr_Response($this->_response, $this->_request);		
	}

	/**
	 * Sends a ping request to the Solr server 
	 * 
	 * @return Solr_Response
	 */
	public function ping()
	{
		return new Solr_Response($this->_sendGet($this->_getPingUrl()));
	}

	/**
	 * Sends a optimize request to the Solr server
	 * 
	 * @return Solr_Response
	 */
	public function optimize()
	{
		return new Solr_Response($this->_sendPost($this->_getUpdateUrl(), '<optimize />'));
	}

	/**
	 * Sends a commit request to the Solr server
	 * 
	 * @return Solr_Response
	 */
	public function commit()
	{
		return new Solr_Response($this->_sendPost($this->_getUpdateUrl(), '<commit />'));
	}

	/**
	 * Sends a rollback request to the Solr Server
	 * 
	 * @return Solr_Response
	 */
	public function rollback()
	{
		return new Solr_Response($this->_sendPost($this->_getUpdateUrl(), '<rollback />'));
	}

	/**
	 * Sends a HTTP GET request to the Solr Server
	 * 
	 * @return string
	 */
	protected function _sendGet($url)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->_timeout);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->_timeout);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, True);
		$response = curl_exec($curl);
		$httpCode = curl_getinfo ($curl, CURLINFO_HTTP_CODE);
		$this->_checkResponse($response, $httpCode);
		return $response;
	}

	/**
	 * Sends a HTTP POST request to the Solr Server
	 * 
	 * @return void
	 */
	protected function _sendPost($url, $data)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type:text/xml; charset=utf-8"));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, True);
		curl_setopt($curl, CURLOPT_POST, True);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
		$response = curl_exec($curl);
		$httpCode = curl_getinfo ($curl, CURLINFO_HTTP_CODE);
		$this->_checkResponse($response, $httpCode);
		return $response;
	}

	/**
	 * Sends a Solr_Select
	 * 
	 * @return string
	 */
	protected function _sendSelect()
	{
		return $this->_sendGet($this->_getSelectUrl($this->_request->getRequestHandler()) . '?' . (string) $this->_request);
	}

	/**
	 * Sends a Solr_Update
	 * 
	 * @return string
	 */
	protected function _sendUpdate()
	{
		return $this->_sendPost($this->_getUpdateUrl(), $this->_request->getXML());
	}

	/**
	 * Sends a Solr_Delete
	 * 
	 * @return string
	 */
	protected function _sendDelete()
	{
		return $this->_sendPost($this->_getDeleteUrl(), $this->_request->getXML());
	}

	/**
	 * Solr base url
	 * 
	 * @return string
	 */
	protected function _getBaseUrl()
	{
		return 'http://' . $this->_host . ':' . $this->_port . '/' . $this->_urlDirectory;
	}

	/**
	 * Solr ping url
	 * 
	 * @return string
	 */
	protected function _getPingUrl()
	{
		return $this->_getBaseUrl() . '/admin/ping?wt=json';
	}

	/**
	 * Solr select url
	 * 
	 * @return string
	 */
	protected function _getSelectUrl($handler)
	{
		return $this->_getBaseUrl() . "/$handler";
	}

	/**
	 * Solr update url
	 * 
	 * @return string
	 */
	protected function _getUpdateUrl()
	{
		return $this->_getBaseUrl() . '/update?wt=json';
	}

	/**
	 * Solr delete url
	 * 
	 * @return string
	 */
	protected function _getDeleteUrl()
	{
		return $this->_getBaseUrl() . '/update?wt=json';
	}

	/**
	 * Checks HTTP status code
	 * 
	 * @return void
	 */
	protected function _checkResponse(& $response, $httpCode)
	{

		if ( ! $httpCode)
		{
			
			if ($this->_request)
			{			
				$this->_logError();
			}
			
			throw new Solr_ClientNoServerResponseException('No response from server.');
		}

		if (substr($httpCode, 0, 1) != 2)
		{
			
			if ($this->_request)
			{
				$this->_logError($response, $httpCode);
			}
			
			throw new Solr_ClientHTTPCodeException($response);
		}
	}

	/**
	 * Logs error
	 * 
	 * @return void
	 */
	protected function _logError($response = '', $httpCode = '')
	{
		
		if (empty($response) && empty($httpCode))
		{
			$type = 'no server response';
		}
		
		if ( ! empty($httpCode))
		{
			$httpCode = ", HTTP status code: $httpCode";
		}
		
		$type = get_class($this->_request);
		$date = $this->_request->getDate();
		
		if ($this->_request instanceOf Solr_Select)
		{
			$msg = "Request (GET URL):\n\n";
			$msg .= (string) $this->_request;
		}
		
		elseif ($this->_request instanceOf Solr_Update || $this->_request instanceOf Solr_Delete)
		{
			$msg = "Request (POST data):\n\n";
			$msg .= $this->_request->getXml();
		}
		
		$logEntry = <<<HEREDOC
Date: $date 
		
Type: $type 

Code : $httpCode

Response : $response

Message : $msg

#


HEREDOC;
		
		file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
	}

}