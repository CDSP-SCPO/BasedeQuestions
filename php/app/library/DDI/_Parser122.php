<?php

/**
 * @package DDI
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class DDI_Parser122 implements DDI_Parser {

	/**
	 * Parsed XML file.
	 * @var string
	 */
	protected $_file;

	/**
	 * @var DOMDocument
	 */
	protected $_DOMDocument;

	/**
	 * @var DOMXPath
	 */
	protected $_DOMXPath;

	/**
	 * @var string
	 */
	protected $_XPathToStudyTitle = '/codeBook/stdyDscr/citation/titlStmt/titl';

	/**
	 * @var string
	 */
	protected $_XPathToStudyAbstract = '/codeBook/stdyDscr/stdyInfo/abstract';

	/**
	 * @var string
	 */
	protected $_XPathToStudyKeywords = '/codeBook/stdyDscr/stdyInfo/subject/keyword';

	/**
	 * @var string
	 */
	protected $_XPathToStudySampProc = '/codeBook/stdyDscr/method/dataColl/sampProc';

	/**
	 * @var string
	 */
	protected $_XPathToStudyCollMode = '/codeBook/stdyDscr/method/dataColl/collMode';
	
	/**
	 * @var string
	 */
	protected $_XPathToStudyCollectDate = '/codeBook/stdyDscr/stdyInfo/sumDscr/collDate';

	/**
	 * @var string
	 */
	protected $_XPathFromCollectDateToDate = './@date';
	
	/**
	 * @var string
	 */
	protected $_XPathFromCollectDateToEvent = './@event';
	
	/**
	 * @var string
	 */
	protected $_XPathFromCollectDateToCycle = './@cycle';
	
	/**
	 * @var string
	 */
	protected $_XPathToStudyUniverse = '/codeBook/stdyDscr/stdyInfo/sumDscr/universe';
	
	/**
	 * @var string
	 */
	protected $_XPathToStudyAnlyUnit = '/codeBook/stdyDscr/stdyInfo/sumDscr/anlyUnit';

	/**
	 * @var string
	 */
	protected $_XPathToStudyGeogCover = '/codeBook/stdyDscr/stdyInfo/sumDscr/geogCover';

	/**
	 * @var string
	 */
	protected $_XPathToStudyNesstarId = '/codeBook/stdyDscr/citation/titlStmt/IDNo';
	
	/**
	 * @var string
	 */
	protected $_XPathToStudyNation = '/codeBook/stdyDscr/stdyInfo/sumDscr/nation';
	
	/**
	 * @var string
	 */
	protected $_XPathToStudyCaseQuantity = '/codeBook/fileDscr/fileTxt/dimensns/caseQnty';
	
	/**
	 * @var string
	 */
	protected $_XPathToStudyProducers = '/codeBook/stdyDscr/citation/prodStmt/producer';

	/**
	 * @var string
	 */
	protected $_XPathFromProducerToAbbr = './@abbr';

	/**
	 * @var string
	 */
	protected $_XPathToStudyFundingAgencies = '/codeBook/stdyDscr/citation/prodStmt/fundAg';

	/**
	 * @var string
	 */
	protected $_XPathFromFundingAgencyToAbbr = './@abbr';

	/**
	 * @var string
	 */
	protected $_XPathToStudyDistributors = '/codeBook/stdyDscr/citation/distStmt/distrbtr';

	/**
	 * @var string
	 */
	protected $_XPathFromDistributorToAbbr = './@abbr';

	/**
	 * @var string
	 */
	protected $_XPathToVariables = '/codeBook/dataDscr/var';

	/**
	 * @var string
	 */
	protected $_XPathFromVariableToName = './@name';

	/**
	 * @var string
	 */
	protected $_XPathFromVariableToLabel = './labl';
	
	/**
	 * @var string
	 */
	protected $_XPathFromVariableToNotes = './notes';

	/**
	 * @var string
	 */
	protected $_XPathFromVariableToNesstarId = './@ID';

	/**
	 * @var string
	 */
	protected $_XPathFromVariableToIvuInstr = './qstn/ivuInstr';

	/**
	 * @var string
	 */
	protected $_XPathFromVariableToQuestionLit = './qstn/qstnLit';

	/**
	 * @var string
	 */
	protected $_XPathFromVariableToPreQuestionText = './qstn/preQTxt';
	
	/**
	 * @var string
	 */
	protected $_XPathFromVariableToPostQuestionText = './qstn/postQTxt';
	
	/**
	 * @var string
	 */
	protected $_XPathFromVariableToCategories = './catgry';

	/**
	 * @var string
	 */
	protected $_XPathFromVariableToUniverse = './universe';

	/**
	 * @var string
	 */
	protected $_XPathFromModalityToMissing = './@missing';
	
	/**
	 * @var string
	 */
	protected $_XPathFromModalityToLabel = './labl';
	
	/**
	 * @var string
	 */
	protected $_XPathFromModalityToValue = './catValu';

	/**
	 * @var string
	 */
	protected $_XPathFromModalityToStats = './catStat';

	/**
	 * @var string
	 */
	protected $_XPathFromModalityToStatsType = './catStat/@type';
	
	/**
	 * @var string
	 */
	protected $_XPathFromVariableToConcept = './concept';
	
	/**
	 * @var string
	 */
	protected $_XPathFromVariableToValid = './sumStat[@type="vald"]';

	/**
	 * @var string
	 */
	protected $_XPathFromVariableToInvalid = './sumStat[@type="invd"]';

	/**
	 * @var string
	 */
	protected $_XPathToVariableWithQuestion = '/codeBook/dataDscr/var[count(./qstn/qstnLit) > 0 and string-length(./qstn/qstnLit) > 0]';

	/**
	 * @return DDI_Parser122
	 */
	public function __construct($file, $DOMDocument = null)
	{

		if ( ! $DOMDocument)
		{
			$DOMDocument = new DOMDocument ('1.0', 'utf8');
			$file = realpath($file);
			$DOMDocument->load($file);
		}

		$this->_DOMDocument = $DOMDocument;
		$this->_DOMXPath = new DOMXPath($this->_DOMDocument);
		
		if ($nameSpace = $this->_DOMDocument->childNodes->item(0)->namespaceURI)
		{
			$this->_DOMXPath->registerNameSpace($prefix = 'ddi', $nameSpace);
			$this->_XPathToStudyTitle = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:citation/$prefix:titlStmt/$prefix:titl";
			$this->_XPathToStudyAbstract = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:stdyInfo/$prefix:abstract";
			$this->_XPathToStudyKeywords = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:stdyInfo/$prefix:subject/$prefix:keyword";
			$this->_XPathToStudySampProc = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:method/$prefix:dataColl/$prefix:sampProc";
			$this->_XPathToStudyCollMode = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:method/$prefix:dataColl/$prefix:collMode";
			$this->_XPathToStudyCollectDate = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:stdyInfo/$prefix:sumDscr/$prefix:collDate";
			$this->_XPathFromCollectDateToDate = "./@date";
			$this->_XPathFromCollectDateToEvent = "./@event";
			$this->_XPathFromCollectDateToCycle = "./@cycle";
			$this->_XPathToStudyUniverse = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:stdyInfo/$prefix:sumDscr/$prefix:universe";
			$this->_XPathToStudyAnlyUnit = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:stdyInfo/$prefix:sumDscr/$prefix:anlyUnit";
			$this->_XPathToStudyGeogCover = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:stdyInfo/$prefix:sumDscr/$prefix:geogCover";
			$this->_XPathToStudyNesstarId = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:citation/$prefix:titlStmt/$prefix:IDNo";
			$this->_XPathToStudyNation = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:stdyInfo/$prefix:sumDscr/$prefix:nation";
			$this->_XPathToStudyCaseQuantity = "/$prefix:codeBook/$prefix:fileDscr/$prefix:fileTxt/$prefix:dimensns/$prefix:caseQnty";
			$this->_XPathToStudyProducers = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:citation/$prefix:prodStmt/$prefix:producer";
			$this->_XPathFromProducerToAbbr = "./@abbr";
			$this->_XPathToStudyFundingAgencies = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:citation/$prefix:prodStmt/$prefix:fundAg";
			$this->_XPathFromFundingAgencyToAbbr = "./@abbr";
			$this->_XPathToStudyDistributors = "/$prefix:codeBook/$prefix:stdyDscr/$prefix:citation/$prefix:distStmt/$prefix:distrbtr";
			$this->_XPathFromDistributorToAbbr = "./@abbr";
			$this->_XPathToVariables = "/$prefix:codeBook/$prefix:dataDscr/$prefix:var";
			$this->_XPathFromVariableToName = "./@name";
			$this->_XPathFromVariableToLabel = "./$prefix:labl";
			$this->_XPathFromVariableToNotes = "./$prefix:notes";
			$this->_XPathFromVariableToNesstarId = "./@ID";
			$this->_XPathFromVariableToIvuInstr = "./$prefix:qstn/$prefix:ivuInstr";
			$this->_XPathFromVariableToQuestionLit = "./$prefix:qstn/$prefix:qstnLit";
			$this->_XPathFromVariableToPreQuestionText = "./$prefix:qstn/$prefix:preQTxt";
			$this->_XPathFromVariableToPostQuestionText = "./$prefix:qstn/$prefix:postQTxt";
			$this->_XPathFromVariableToCategories = "./$prefix:catgry";
			$this->_XPathFromVariableToUniverse = "./$prefix:universe";
			$this->_XPathFromModalityToMissing = "./@missing";
			$this->_XPathFromModalityToLabel = "./$prefix:labl";
			$this->_XPathFromModalityToValue = "./$prefix:catValu";
			$this->_XPathFromModalityToStats = "./$prefix:catStat";
			$this->_XPathFromModalityToStatsType = "./$prefix:catStat/@type";
			$this->_XPathFromVariableToConcept = "./$prefix:concept";
			$this->_XPathFromVariableToValid = "./$prefix:sumStat[@type=\"vald\"]";
			$this->_XPathFromVariableToInvalid = "./$prefix:sumStat[@type=\"invd\"]";
			$this->_XPathToVariableWithQuestion = "/$prefix:codeBook/$prefix:dataDscr/$prefix:var[count(./$prefix:qstn) > 0 and string-length(./$prefix:qstn/$prefix:qstnLit) > 0]";
		}
		
		$this->_file = basename($file);
	}

	/**
	 * @return string
	 */
	public function getFile()
	{
		return $this->_file;
	}

	/**
	 * @see DDI_Parser#getStudy($lang)
	 */
	public function getStudyDescription()
	{
		return array(
			'title' => $this->_getStudyTitle(),
			'sample_procedure' => $this->_getStudySampProc(),
			'universe' => $this->_getStudyUniverse(),
			'analysis_unit' => $this->_getStudyAnlyUnit(),
			'geographic_coverage' => $this->_getStudyGeogCover(),
			'abstract' => $this->_getStudyAbstract(),
			'keywords' => $this->_getStudyKeywords(),
			'collect_mode' => $this->_getStudyCollMode(),
			'nesstar_id' => $this->_getStudyNesstarId(),
			'question_count' => $this->_getStudyQuestionCount(),
			'nation' => $this->_getStudyNation(),
			'case_quantity' => $this->_getStudyCaseQuantity(),
		);
	}

	/**
	 * @return string
	 */
	protected function _getStudyTitle()
	{
		$studyTitleNodeList = $this->_DOMXPath->query($this->_XPathToStudyTitle);
		return $studyTitleNodeList->length > 0 ? $this->_cleanXMLString($studyTitleNodeList->item(0)->textContent) : '';
	}

	/**
	 * @return string
	 */
	protected function _getStudyAbstract()
	{
		$abstractNodeList = $this->_DOMXPath->query($this->_XPathToStudyAbstract);
		return $abstractNodeList->length > 0 ? $this->_cleanXMLString($abstractNodeList->item(0)->textContent) : '';
	}

	/**
	 * @return array
	 */
	protected function _getStudyKeywords()
	{
		$keywordNodeList = $this->_DOMXPath->query($this->_XPathToStudyKeywords);
		$l = $keywordNodeList->length;
		$result = array();
		
		for ($i = 0; $i < $l; $i++)
		{
			$result[] = $this->_cleanXMLString($keywordNodeList->item($i)->textContent);
		}
	}

	/**
	 * @return string
	 */
	protected function _getStudySampProc()
	{
		$sspNodeList = $this->_DOMXPath->query($this->_XPathToStudySampProc);
		return $sspNodeList->length > 0 ? $this->_cleanXMLString($sspNodeList->item(0)->textContent) : '';
	}
	
	/**
	 * @return string
	 */
	protected function _getStudyCollectStartDate()
	{
		$scsdNodeList = $this->_DOMXPath->query($this->_XPathToStudyCollectStartDate);
		return $scsdNodeList->length > 0 ? $this->_cleanXMLString($scsdNodeList->item(0)->textContent) : '';
	}

	/**
	 * @return string
	 */
	protected function _getStudyCollectEndDate()
	{
		$scedNodeList = $this->_DOMXPath->query($this->_XPathToStudyCollectEndDate);
		return $scedNodeList->length > 0 ? $this->_cleanXMLString($scedNodeList->item(0)->textContent) : '';
	}
	
	/**
	 * @return string
	 */
	protected function _getStudyCollMode()
	{
		$scmNodeList = $this->_DOMXPath->query($this->_XPathToStudyCollMode);
		return $scmNodeList->length > 0 ? $this->_cleanXMLString($scmNodeList->item(0)->textContent) : '';
	}

	/**
	 * @return string
	 */
	protected function _getStudyUniverse()
	{
		$suNodeList = $this->_DOMXPath->query($this->_XPathToStudyUniverse);
		return $suNodeList->length > 0 ? $this->_cleanXMLString($suNodeList->item(0)->textContent) : '';
	}
	
	/**
	 * @return string
	 */
	protected function _getStudyAnlyUnit()
	{
		$sauNodeList = $this->_DOMXPath->query($this->_XPathToStudyAnlyUnit);
		return $sauNodeList->length > 0 ? $this->_cleanXMLString($sauNodeList->item(0)->textContent) : '';
	}
	
	/**
	 * @return string
	 */
	protected function _getStudyGeogCover()
	{
		$sgcNodeList = $this->_DOMXPath->query($this->_XPathToStudyGeogCover);
		return $sgcNodeList->length > 0 ? $this->_cleanXMLString($sgcNodeList->item(0)->textContent) : '';
	}

	/**
	 * @return string
	 */
	protected function _getStudyNesstarId()
	{
		$sniNodeList = $this->_DOMXPath->query($this->_XPathToStudyNesstarId);
		return $sniNodeList->length > 0 ? $this->_cleanXMLString($sniNodeList->item(0)->textContent) : '';
	}
	
	/**
	 * @return int
	 */
	protected function _getStudyQuestionCount()
	{
		$nl = $this->_DOMXPath->query($this->_XPathToVariableWithQuestion);
		return $nl->length;
	}
	
	/**
	 * @return string
	 */
	protected function _getStudyNation()
	{
		$snNodeList = $this->_DOMXPath->query($this->_XPathToStudyNation);
		return $snNodeList->length > 0 ? $this->_cleanXMLString($snNodeList->item(0)->textContent) : '';
	}
	
	/**
	 * @return int
	 */
	protected function _getStudyCaseQuantity()
	{
		$scqNodeList = $this->_DOMXPath->query($this->_XPathToStudyCaseQuantity);
		return $scqNodeList->length > 0 ? $this->_cleanXMLString($scqNodeList->item(0)->textContent) : '';
	}

	/**
	 * @return array
	 */
	public function getCollectDates()
	{
		$collectDateNodeList = $this->_DOMXPath->query($this->_XPathToStudyCollectDate);
		$l = $collectDateNodeList->length;
		$result = array();
		
		for ($i = 0; $i < $l; $i++)
		{
			$cd = $collectDateNodeList->item($i);
			
			$date = $this->_DOMXPath->query($this->_XPathFromCollectDateToDate, $cd);
			$date = $date->length > 0 ? $date->item(0)->textContent : '';
			$date = $this->_cleanXMLString($date);
			
			$event = $this->_DOMXPath->query($this->_XPathFromCollectDateToEvent, $cd);
			$event = $event->length > 0 ? $event->item(0)->textContent : '';
			$event = $this->_cleanXMLString($event);
			
			$cycle = $this->_DOMXPath->query($this->_XPathFromCollectDateToCycle, $cd);
			$cycle = $cycle->length > 0 ? $cycle->item(0)->textContent : '';
			$cycle = $this->_cleanXMLString($cycle);
			
			$result[] = array(
				'date' => $date, 
				'event' => $event,
				'cycle' => $cycle
			);
			
		}
		
		return $result;
	}
	
	/**
	 * @return array
	 */
	public function getDistributors()
	{
		$distributorNodeList = $this->_DOMXPath->query($this->_XPathToStudyDistributors);
		$l = $distributorNodeList->length;
		$result = array();
		
		for ($i = 0; $i < $l; $i++)
		{
			$distributorNode = $distributorNodeList->item($i);
			$distributorTitle = $this->_cleanXMLString($distributorNode->textContent);
			$abbrNodeList = $this->_DOMXPath->query($this->_XPathFromDistributorToAbbr, $distributorNode);
			$distributorAbbr = $abbrNodeList->length > 0 ? $abbrNodeList->item(0)->textContent : '';
			$distributorAbbr = $this->_cleanXMLString($distributorAbbr);
			$result[] = array(
				'abbreviation' => $distributorAbbr, 
				'title' => $distributorTitle
			);
			
		}
		
		return $result;
	}
	
	/**
	 * @return array
	 */
	public function getFundingAgencies()
	{
		$agencyNodeList = $this->_DOMXPath->query($this->_XPathToStudyFundingAgencies);
		$l = $agencyNodeList->length;
		$result = array();
		
		for ($i = 0; $i < $l; $i++)
		{
			$agencyNode = $agencyNodeList->item($i);
			$agencyTitle = $this->_cleanXMLString($agencyNode->textContent);
			$abbrNodeList = $this->_DOMXPath->query($this->_XPathFromFundingAgencyToAbbr, $agencyNode);
			$agencyAbbr = $abbrNodeList->length > 0 ? $abbrNodeList->item(0)->textContent : '';
			$agencyAbbr = $this->_cleanXMLString($agencyAbbr);
			$result[] = array(
				'abbreviation' => $agencyAbbr, 
				'title' => $agencyTitle
			);
			
		}
		
		return $result;
	}
	
	
	/**
	 * @return array
	 */
	public function getProducers()
	{
		$producerNodeList = $this->_DOMXPath->query($this->_XPathToStudyProducers);
		$l = $producerNodeList->length;
		$result = array();
		
		for ($i = 0; $i < $l; $i++)
		{
			$producerNode = $producerNodeList->item($i);
			$producerTitle = $this->_cleanXMLString($producerNode->textContent);
			$abbrNodeList = $this->_DOMXPath->query($this->_XPathFromProducerToAbbr, $producerNode);
			$producerAbbr = $abbrNodeList->length > 0 ? $abbrNodeList->item(0)->textContent : '';
			$producerAbbr = $this->_cleanXMLString($producerAbbr);
			$result[] = array(
				'abbreviation' => $producerAbbr, 
				'title' => $producerTitle
			);
			
		}
		
		return $result;
	}
	
	/**
	 * @see DDI_Parser#getVariables($lang)
	 */
	public function & getVariables()
	{
		$result = array();
		$variableNodeList = $this->_getVariables();
		$variableCount = $variableNodeList->length;
		$questionCount = $this->_getVariableWithQuestionCount();
		$questionsCounted = 0;

		for ($i = 0; $i < $variableNodeList->length; $i ++)
		{
			$variableNode = $variableNodeList->item($i);
			$questionLit = $this->_getVariableQuestionLit($variableNode);
			
			if ($questionLit)
			{
				$questionsCounted++;
			}
			
			$result[] = array(
				'nesstar_id' => $this->_getVariableNesstarId($variableNode),
				'name' => $this->_getVariableName($variableNode),
				'label' => $this->_getVariableLabel($variableNode),
				'valid' => $this->_getVariableValid($variableNode),
				'invalid' => $this->_getVariableInvalid($variableNode),
				'concept' => $this->_getVariableConcept($variableNode),
				'universe' => $this->_getVariableUniverse($variableNode),
				'notes' => $this->_getVariableNotes($variableNode),
				'question' => array
				(
					'preceding_question_count' => $questionsCounted,
					'interviewer_instruction' => $this->_getVariableQuestionIvuInstr($variableNode),
					'pre_question_text' => $this->_getVariableQuestionPreQuestionText($variableNode),
					'question_text' => $questionLit,
					'post_question_text' => $this->_getVariableQuestionPostQuestionText($variableNode),
				),
				'categories' => $this->_getVariableCategories($variableNode),
			);
		}

		return $result;
	}
	
	/**
	 * @return DOMNodeList
	 */
	protected function _getVariables()
	{
		return $this->_DOMXPath->query($this->_XPathToVariables);
	}

	/**
	 * @param DOMNode $variable
	 * @return string
	 */
	protected function _getVariableName(DOMNode $variable)
	{
		$vnNodeList = $this->_DOMXPath->query($this->_XPathFromVariableToName, $variable);
		return $vnNodeList->length > 0 ? $this->_cleanXMLString($vnNodeList->item(0)->textContent) : '';
	}

	/**
	 * @param DOMNode $variable
	 * @return string
	 */
	protected function _getVariableLabel(DOMNode $variable)
	{
		$vlNodeList = $this->_DOMXPath->query($this->_XPathFromVariableToLabel, $variable);
		return $vlNodeList->length > 0 ? $this->_cleanXMLString($vlNodeList->item(0)->textContent) : '';
	}
	
	/**
	 * @return string
	 */
	protected function _getVariableNotes(DOMNode $variable)
	{
		$vnNodeList = $this->_DOMXPath->query($this->_XPathFromVariableToNotes, $variable);
		return $vnNodeList->length > 0 ? $this->_cleanXMLString($vnNodeList->item(0)->textContent) : '';
	}
	
	
	/**
	 * @param DOMNode $variable
	 * @return string
	 */
	protected function _getVariableValid(DOMNode $variable)
	{
		$vvNodeList = $this->_DOMXPath->query($this->_XPathFromVariableToValid, $variable);
		return $vvNodeList->length > 0 ? $this->_cleanXMLString($vvNodeList->item(0)->textContent) : '';
	}

	/**
	 * @param DOMNode $variable
	 * @return string
	 */
	protected function _getVariableInvalid(DOMNode $variable)
	{
		$viNodeList = $this->_DOMXPath->query($this->_XPathFromVariableToInvalid, $variable);
		return $viNodeList->length > 0 ? $this->_cleanXMLString($viNodeList->item(0)->textContent) : '';
	}
	
	/**
	 * @param DOMNode $variable
	 * @return string
	 */
	protected function _getVariableUniverse(DOMNode $variable)
	{
		$vuNodeList = $this->_DOMXPath->query($this->_XPathFromVariableToUniverse, $variable); 
		return $vuNodeList->length > 0 ? $this->_cleanXMLString($vuNodeList->item(0)->textContent) : '';
	}

	/**
	 * @param DOMNode $variable
	 * @return string
	 */
	protected function _getVariableNesstarId(DOMNode $variable)
	{
		$vniNodeList = $this->_DOMXPath->query($this->_XPathFromVariableToNesstarId, $variable);
		return $vniNodeList->length > 0 ? $this->_cleanXMLString($vniNodeList->item(0)->textContent) : '';
	}

	/**
	 * @param DOMNode $variable
	 * @return string
	 */
	protected function _getVariableQuestionLit(DOMNode $variable)
	{
		$vqlNodeList = $this->_DOMXPath->query($this->_XPathFromVariableToQuestionLit, $variable);  
		return $vqlNodeList->length > 0 ? $this->_cleanXMLString($vqlNodeList->item(0)->textContent) : '';
	}
	
	/**
	 * @param DOMNode $variable
	 * @return string
	 */
	protected function _getVariableQuestionIvuInstr(DOMNode $variable)
	{
		$vqiNodeList = $this->_DOMXPath->query($this->_XPathFromVariableToIvuInstr, $variable);  
		return $vqiNodeList->length > 0 ? $this->_cleanXMLString($vqiNodeList->item(0)->textContent) : '';
	}
	
	
	/**
	 * @param DOMNode $variable
	 * @return string
	 */
	protected function _getVariableQuestionPreQuestionText(DOMNode $variable)
	{
		$qpqtNodeList = $this->_DOMXPath->query($this->_XPathFromVariableToPreQuestionText, $variable);
		return $qpqtNodeList->length > 0 ? $this->_cleanXMLString($qpqtNodeList->item(0)->textContent) : '';
	}
	
	/**
	 * @param DOMNode $variable
	 * @return string
	 */
	protected function _getVariableQuestionPostQuestionText(DOMNode $variable)
	{
		$qpqtNodeList = $this->_DOMXPath->query($this->_XPathFromVariableToPostQuestionText, $variable);
		return $qpqtNodeList->length > 0 ? $this->_cleanXMLString($qpqtNodeList->item(0)->textContent) : '';
	}
	
	/**
	 * @param DOMNode $variable
	 * @return string
	 */
	protected function _getVariableConcept(DOMNode $variable)
	{
		$conceptNodeList = $this->_DOMXPath->query($this->_XPathFromVariableToConcept, $variable);
		return $conceptNodeList->length > 0 ? $this->_cleanXMLString($conceptNodeList->item(0)->textContent) : '';
	}

	/**
	 * @param DOMNode $variable
	 * @return array
	 */
	protected function _getVariableCategories(DOMNode $variable)
	{
		$modalities = $this->_DOMXPath->query($this->_XPathFromVariableToCategories, $variable);
		$l = $modalities->length;
		$result = array();
		
		for ($i = 0; $i < $l; $i++)
		{
			$modality = $modalities->item($i);
			$result[] = array(
				'missing' => $this->_getModalityMissing($modality),
				'value' => $this->_getModalityValue($modality),
				'label' => $this->_getModalityLabel($modality),
				'stats' => $this->_getModalityStats($modality),
				'stats_type' => $this->_getModalityStatsType($modality),
			);
		}
		
		return $result;
	}

	protected function _getModalityMissing(DOMNode $modality)
	{
		$m = $this->_DOMXPath->query($this->_XPathFromModalityToMissing, $modality);
		return $m->length > 0 ? $m->item(0)->textContent : NULL;
	}
	
	/**
	 * @param DOMNode $modality
	 * @return string
	 */
	protected function _getModalityLabel(DOMNode $modality)
	{
		$mlNodeList = $this->_DOMXPath->query($this->_XPathFromModalityToLabel, $modality);
		return $mlNodeList->length > 0 ? $this->_cleanXMLString($mlNodeList->item(0)->textContent) : '';
	}

	/**
	 * @param DOMNode $modality
	 * @return string
	 */
	protected function _getModalityValue(DOMNode $modality)
	{
		$mvNodeList = $this->_DOMXPath->query($this->_XPathFromModalityToValue, $modality);
		return $mvNodeList->length > 0 ? $this->_cleanXMLString($mvNodeList->item(0)->textContent) : '';
	}
	
	/**
	 * @param DOMNode $modality
	 * @return string
	 */
	protected function _getModalityStats(DOMNode $modality)
	{
		$msNodeList = $this->_DOMXPath->query($this->_XPathFromModalityToStats, $modality);
		return $msNodeList->length > 0 ? $this->_cleanXMLString($msNodeList->item(0)->textContent) : '';
	}
	
	/**
	 * @param DOMNode $modality
	 * @return string
	 */
	protected function _getModalityStatsType(DOMNode $modality)
	{
		$mstNodeList = $this->_DOMXPath->query($this->_XPathFromModalityToStatsType, $modality);
		return $mstNodeList->length > 0 ? $this->_cleanXMLString($mstNodeList->item(0)->textContent) : '';
	}

	/**
	 * @return array
	 */
	public function getVariablesMultipleItemNearbyAnalysis
	(
		$questionAndItemSeparator = "- ", 
		$acceptedCommonStartLengthDifference = 3,
		$commonStartMaxDist = 97
	)
	{
		$variables = $this->getVariables();
		$l = count($variables);
		$result = array(); 
		$i = 0;

		while ($i < $l - 1)
		{
			$pos1 = strpos(
				$variables[$i]['question']['question_text'],
				$questionAndItemSeparator
			);

			if ($pos1 === false)
			{
				$i++;
				continue;
			}

			$pos2 = strpos(
				$variables[$i + 1]['question']['question_text'],
				$questionAndItemSeparator
			);
			

			if (abs($pos1 - $pos2) <= $acceptedCommonStartLengthDifference)
			{
				$start1 = substr($variables[$i]['question']['question_text'], 0, $pos1);
				$start1 = trim($start1);
				$start2 = substr($variables[$i + 1]['question']['question_text'], 0, $pos2);
				$start2 = trim($start2);
				similar_text($start1, $start2, $percent);

				$_start1 = str_split($start1, 255);
				$_start2 = str_split($start1, 255);

				$_l = count($_start1);
				
				if ($_l != count($_start2))
				{
					continue;
				}

				for ($k = 0; $k < $_l; $k++)
				{
					if ( ! levenshtein($_start1[$k], $_start2[$k]) <= $commonStartMaxDist)
					{
						$i++;
						continue;
					}
				}

			}

			else
			{	
				$i++;
				continue;
			}

			$miVariables = array(); // Multiple item variable
			$miVariables[] = $variables[$i];
			$miVariables[] = $variables[$i + 1];
			$i++;

			while($i < $l - 1)
			{
				$pos3 = strpos(
					$variables[$i]['question']['question_text'],
					$questionAndItemSeparator
				);

				if ($pos3 === false)
				{
					break;
				}

				if (abs($pos3 - (($pos2 + $pos1) / 2)) <= $acceptedCommonStartLengthDifference)
				{
					$start3 = substr($variables[$i]['question']['question_text'], 0, $pos3);
					$start3 = trim($start3);
					$_start3 = str_split($start1, 255);
					$_l = count($_start1);
				
					if ($_l != count($_start3))
					{
						continue;
					}

					for ($k = 0; $k < $_l; $k++)
					{
						if ( ! levenshtein($_start1[$k], $_start3[$k]) <= $commonStartMaxDist)
						{
							$i++;
							break;
						}
					}

				}

				else
				{
					break;
				}

				$miVariables[] = $variables[$i];
				$i++;
			}

			$result[] = $miVariables;

		}

		$result = $this->_formatBatteries($result, $questionAndItemSeparator);
		return $result;
	}

	/**
	 * @param array $batteries
	 * @return array
	 */
	protected function _formatBatteries($batteries, $questionAndItemSeparator)
	{
		$result = array();
		
		foreach ($batteries as $battery)
		{
			$_battery = array();
			
			foreach ($battery as $var)
			{
				$qi = explode($questionAndItemSeparator, $var['question']['question_text']);
				$var['question']['question_no_item'] = trim($qi[0]);
				$var['question']['item'] = trim($qi[1]);
				$_battery[$var['name']] = $var;
			}
			
			$result[] = $_battery;
		}
		
		return $result;
		
	}
	
	/**
	 * @param string $question1
	 * @param string $question2
	 * @return string
	 */
	protected function _commonStart($question1, $question2)
	{
		
		if ($question1 === '' || $question2 == '')
		{
			return '';
		}
		
		$result = '';
		$l = strlen($question1);
		
		for ($i = 0; $i < $l; $i ++)
		{
			if (($c = $question1[$i]) == $question2[$i])
			{
				$result .= $c;
			}
			
			else
			{
				return $result;
			}
		}
		
		return $result;
		
	}

	/**
	 * Tells if 2 variables returned by the DDI_Parser:getVariables method have the same modalities
	 *  
	 * @param array $variable1
	 * @param array $variable2
	 * @return bool
	 */
	protected function _sameCategories($variable1, $variable2)
	{
		
		if ($variable1['question']['question_text'] == '' || $variable2['question']['question_text'] == '')
		{
			return false;
		}

		if (($l = count($variable1['categories'])) != count($variable2['categories']))
		{
			return false;
		}

		for ($i = 0; $i < $l; $i ++)
		{
			similar_text($variable1['categories'][$i]['label'], $variable2['categories'][$i]['label'], $percent);

			if ($percent < 90)
			{
				return false;
			}

		}

		return true;

	}
	
	/**
	 * @return int
	 */
	protected function _getVariableWithQuestionCount()
	{
		 return $this->_DOMXPath->query($this->_XPathToVariableWithQuestion)->length;
	}
	
	/**
	 * @return string
	 */
	protected function _cleanXMLString($value)
	{
		$value = $this->_stripCDATA($value);
		$value = trim($value);
		$value = htmlspecialchars($value);
		return $value;
	}
	
	/**
	 * @return string
	 */
	protected function _stripCDATA($xml)
	{

		if(($i = strpos($xml, '<![CDATA[')) !== false)
		{
			$before = substr($xml, 0, $i);
			$j = strpos($xml, ']]>');
			$after = substr($xml, $j + 3, strlen($xml));
			$between = substr($xml, $i + 9, $j - $i - 9);
			$between = htmlspecialchars($between);
			$xml = $before . $between . $after;
			$xml = $this->_stripCDATA($xml);
		}

		return $xml;
	}

}
