<?php

/**
 * @package BDQSettings
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class BDQ_Settings_Client {

	/**
	 * @var BDQ_ClientSettings
	 */
	private static $_instance;

	/**
	 * @var string
	 */
	public static $defaultsIniFile;

	/**
	 * @var boolean
	 */
	public $stemming;

	/**
	 * @var boolean
	 */
	public $synonyms;
	
	/**
	 * @var boolean
	 */
	public $stopwords;

	/**
	 * @var boolean
	 */
	public $autoComplete;

	/**
	 * @var string
	 */
	public $queryOperator;
	
	/**
	 * @var boolean
	 */
	public $displayModalities;
	
	/**
	 * @var boolean
	 */
	public $displayInterviewerInstructions;
	
	/**
	 * @var boolean
	 */
	public $displayPreQuestionText;
	
	/**
	 * @var boolean
	 */
	public $displayPostQuestionText;
	
	/**
	 * @var boolean
	 */
	public $displayConcept;
	
	/**
	 * @var boolean
	 */
	public $displayNotes;
	
	/**
	 * @var boolean
	 */
	public $displayUniverse;
	
	/**
	 * @var boolean
	 */
	public $displayQuestionPositionDecile;
	
	/**
	 * @var boolean
	 */
	public $displayNavigationBar;
	
	/**
	 * @var boolean
	 */
	public $displayQuestionnairePdfLink;
	
	/**
	 * @var boolean
	 */
	public $displaySimilarQuestionLink;
	
	/**
	 * @var int
	 */
	public $rows;

	/**
	 * @var string
	 */
	public $sort;
	
	/**
	 * @var boolean
	 */
	public $fluoHighlight;
	
	/**
	 * @var string
	 */
	public $refineMenuSize;
	
	/**
	 * @var string
	 */
	public $bodySize;
	
	/**
	 * @var string
	 */
	public $selectedQuestions;
	
	/**
	 * @var int
	 */
	public $producerFacetPosition;

	/**
	 * @var int
	 */
	public $studySerieFacetPosition;

	/**
	 * @var int
	 */
	public $studyFacetPosition;

	/**
	 * @var int
	 */
	public $decadeFacetPosition;

	/**
	 * @var int
	 */
	public $keywordFacetPosition;

	/**
	 * @var int
	 */
	public $conceptFacetPosition;
	
	/**
	 * @var boolean
	 */
	public $producerFacetDisplay;
	
	/**
	 * @var boolean
	 */
	public $studySerieFacetDisplay;

	/**
	 * @var boolean
	 */
	public $studyFacetDisplay;

	/**
	 * @var boolean
	 */
	public $decadeFacetDisplay;

	/**
	 * @var boolean
	 */
	public $keywordFacetDisplay;

	/**
	 * @var boolean
	 */
	public $conceptFacetDisplay;
	
	/**
	 * @return BDQ_ClientSettings
	 */
	private function __construct()
	{
		$this->loadDefaults();

		if (isset($_COOKIE['settings']))
		{

			while (list($key, $val) = each($_COOKIE['settings']))
			{
				$this->$key = $val;
			}

		}
		
		if (isset($_COOKIE['selectedQuestions']))
		{
			$this->selectedQuestions = $_COOKIE['selectedQuestions'];
		}
	}

	/**
	 * @return BDQ_ClientSettings
	 */
	public function getInstance()
	{

		if ( ! isset(self::$_instance))
		{
			self::$_instance = new BDQ_Settings_Client;
		}

		return self::$_instance;
	}
	
	/**
	 * @return void
	 */
	public function setCookie()
	{
		setcookie('settings[synonyms]', (int) $this->synonyms, 25920000 + time(), '/');
		setcookie('settings[stemming]', (int) $this->stemming, 25920000 + time(), '/');
		setcookie('settings[stopwords]', (int) ($this->stopwords), 25920000 + time(), '/');
		setcookie('settings[autoComplete]', (int) $this->autoComplete, 25920000 + time(), '/');
		setcookie('settings[displayInterviewerInstructions]', (int) $this->displayInterviewerInstructions, 25920000 + time(), '/');
		setcookie('settings[displayPreQuestionText]', (int) $this->displayPreQuestionText, 25920000 + time(), '/');
		setcookie('settings[displayPostQuestionText]', (int) $this->displayPostQuestionText, 25920000 + time(), '/');
		setcookie('settings[displayConcept]', (int) $this->displayConcept, 25920000 + time(), '/');
		setcookie('settings[displayNotes]', (int) $this->displayNotes, 25920000 + time(), '/');
		setcookie('settings[displayUniverse]', (int) $this->displayUniverse, 25920000 + time(), '/');
		setcookie('settings[displayQuestionPositionDecile]', (int) $this->displayQuestionPositionDecile, 25920000 + time(), '/');
		setcookie('settings[displayNavigationBar]', (int) $this->displayNavigationBar, 25920000 + time(), '/');
		setcookie('settings[displayQuestionnairePdfLink]', (int) $this->displayQuestionnairePdfLink, 25920000 + time(), '/');
		setcookie('settings[displaySimilarQuestionLink]', (int) $this->displaySimilarQuestionLink, 25920000 + time(), '/');
		setcookie('settings[rows]', (int) $this->rows, 25920000 + time(), '/');
		setcookie('settings[sort]', $this->sort, 25920000 + time(), '/');
		setcookie('settings[fluoHighlight]', (int) $this->fluoHighlight, 25920000 + time(), '/');
		setcookie('settings[refineMenuSize]', (string) $this->refineMenuSize, 25920000 + time(), '/');
		setcookie('settings[bodySize]', (string) $this->bodySize, 25920000 + time(), '/');
		setcookie('selectedQuestions', (string) $this->selectedQuestions, 25920000 + time(), '/');
		setcookie('settings[producerFacetPosition]', (int) $this->producerFacetPosition, 25920000 + time(), '/');
		setcookie('settings[studySerieFacetPosition]', (int) $this->studySerieFacetPosition, 25920000 + time(), '/');
		setcookie('settings[studyFacetPosition]', (int) $this->studyFacetPosition, 25920000 + time(), '/');
		setcookie('settings[decadeFacetPosition]', (int) $this->decadeFacetPosition, 25920000 + time(), '/');
		setcookie('settings[keywordFacetPosition]', (int) $this->keywordFacetPosition, 25920000 + time(), '/');
		setcookie('settings[conceptFacetPosition]', (int) $this->conceptFacetPosition, 25920000 + time(), '/');
		setcookie('settings[producerFacetDisplay]', (int) $this->producerFacetDisplay, 25920000 + time(), '/');
		setcookie('settings[studySerieFacetDisplay]', (int) $this->studySerieFacetDisplay, 25920000 + time(), '/');
		setcookie('settings[studyFacetDisplay]', (int) $this->studyFacetDisplay, 25920000 + time(), '/');
		setcookie('settings[decadeFacetDisplay]', (int) $this->decadeFacetDisplay, 25920000 + time(), '/');
		setcookie('settings[keywordFacetDisplay]', (int) $this->keywordFacetDisplay, 25920000 + time(), '/');
		setcookie('settings[conceptFacetDisplay]', (int) $this->conceptFacetDisplay, 25920000 + time(), '/');
	}
	
	/**
	 * @return void
	 */
	public function loadDefaults()
	{
		$settings = parse_ini_file(self::$defaultsIniFile);
		reset($settings);

		while (list($key, $val) = each($settings))
		{
			$this->$key = $val;
		}
	}

	/**
	 * @return string
	 */
	static function getAnalysisCode()
	{
		$code = '';
		$code .= ! self::$_instance->stopwords ? 'Sw' : '';
		$code .= self::$_instance->stemming ? 'St' : '';
		return $code;
	}

}