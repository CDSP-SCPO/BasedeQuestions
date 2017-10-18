<?php

/**
 * @package DDI
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class DDI_ParserFactory {

	/**
	 * @var DDI_ParserFactory
	 */
	private static $_instance;

	/**
	 * @var array
	 */
	public static $_supportedDDIVersions = array('122');

	/**
	 * @var array
	 */
	private $_tagsWithDDIVersionAttribute = array (
		array (
			'tag' => 'codeBook',
			'att' => 'version',
		),	
	);

	/**
	 * @var array
	 */
	private $_DDIDTDFiles = array('122' => '',);

	/**
	 * @var string
	 */
	private $_DDIXMLFilePath;

	/**
	 * @var DOMDocument
	 */
	private $_DOMDocument;

	/**
	 * @return DDI_ParserFactory
	 */
	public static function getInstance()
	{
		if ( ! self::$_instance)
		{
			self::$_instance = new DDI_ParserFactory();
		}
		
		return self::$_instance;
	}

	/**
	 * @return DDI_Parser
	 */
	public function create($file)
	{
		$this->_DOMDocument = new DOMDocument('1.0', 'utf8');
		$file = realpath($file);

		if ( ! $this->_DOMDocument->load($file))
		{
			throw new DOMDocumentLoadingException("Couldn't load $file.");
		}

		$DDIVersion = $this->_detectDDIVersion();
		
		if ( ! $DDIVersion)
		{		
			throw new UndetectedDDIVersionException("Couldn't detect \"$file\"'s DDI version.");
		}
		
		if ( ! in_array($DDIVersion, self::$_supportedDDIVersions))
		{
			throw new UnsupportedDDIVersionException("DDI $DDIVersion is not supported.");
		}
		
		if ($DDIVersion == 122)
		{
			return new DDI_Parser122($file, $this->_DOMDocument);
		}
	}

	/**
	 * @return int
	 */
	private function _detectDDIVersion()
	{
		$DDIVersion = '';		
		
		if ( ! $this->_DOMDocument->doctype)
		{
			$DDIVersion = $this->_getDDIVersionFromTag();	
		}
		
		else
		{
			$DDIVersion = $this->_getDDIVersionFromDoctype();
		}
		
		if ($DDIVersion)
		{
			$DDIVersion = str_replace (
				array ('.', '-', ',', '_',), 
				array ('', '', '', '',), 
				$DDIVersion
			);
			$DDIVersion = trim($DDIVersion);
			$DDIVersion = (int) $DDIVersion;
		}
			
		return $DDIVersion;
	}

	/**
	 * @return string
	 */
	private function _getDDIVersionFromTag()
	{
		$DDIVersion = '';
		
		foreach ($this->_tagsWithDDIVersionAttribute as $elt):

			extract($elt);
			$items = $this->_DOMDocument->getElementsByTagName($tag);
			
			for ($i = 0 ; $i < $items->length ; $i++)
			{
				$item = $items->item($i);
				
				if ($item->attributes)
				{

					if ($itemAtt = $item->attributes->getNamedItem($att))
					{
						$DDIVersion = trim($itemAtt->textContent);
						break 2;
					}
				}
			}

		endforeach;
		
		return $DDIVersion;
	}

	/**
	 * @return string
	 */
	private function _getDDIVersionFromDoctype()
	{
	}

}