<?php 

/**
 * @package Form
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class BDQ_Form_Ddifile extends BDQ_Form
{

	/**
	 * @var int
	 */
	const MULTIPLE_ITEM_NO_ANALYSIS = 0;

	/**
	 * @var int
	 */
	const MULTIPLE_ITEM_NEARBY_ANALYSIS = 1;

	/**
	 * @var int
	 */
	const RANGE_YEAR_START = 1900;
	
	/**
	 * @var Zend_Translate
	 */
	protected $_translate;
	
	/**
	 * @var DB_Model_DdiFile
	 */
	protected $_ddifile;
	
	/**
	 * @var array
	 */
	protected $_studydescription;
	
	/**
	 * @param array $options
	 * @return Form_DdiFile
	 */
	public function __construct($options = null)
	{
		parent::__construct($options);
	}

	/**
	 * @see Zend_Form#init()
	 */
	public function init()
	{
		
		$this->_translate = Zend_Registry::get('translateAdmin');
		$this->setMethod('post');
		$this->setAttrib('enctype', 'multipart/form-data');
		$this->_addHiddenId('id', '', 'ddifile');
		
		if ( ! isset($this->_ddifile))
		{
			$this->_addLangs();
			$this->_addDdiFile();
		}
		
		$this->_addYear();
		$this->_addStudySerieId();
		$this->_addDomainId();
		
		if ( ! isset($this->_ddifile))
		{
			$this->_addConceptListId();
		}
		
		$this->_addNesstarServerId();
		$this->_addQuestionnaireUrl();
		$this->_addQuestionnaireFile();
		
		if ( ! isset($this->_ddifile))
		{
			$this->_addMultipleItemAnalysis();
			$this->_addQuestionItemSeparator();
		}
		
		
		$this->_addSubmit();
	}
	
	protected function _addLangs()
	{
		$solrTl = Zend_Registry::get('translationLanguagesSolr');
		$langs = array();
		$l = count($solrTl);
		
		for ($i = 0; $i < $l; $i++)
		{
			$langs[$solrTl[$i]->get_id()] = $solrTl[$i]->get_code();
		}
		
		$element = new Zend_Form_Element_Radio('lang_id');
		$element->setLabel($this->_translate->_('li0345000000'))
				->setMultiOptions($langs)
				->setRequired(True);
		$this->addElement($element);
	}
	
	protected function _addDdiFile()
	{
		$element = new Zend_Form_Element_File('ddifile');
		$element->setLabel($this->_translate->_('li0345000050'))
				->setDestination(DDI_FILES . '/temp')
				->addValidator('Count', false, 1)
				->addValidator('Extension', false, 'xml')
				->setRequired(true)
				->setMaxFileSize('20000000')
				->addValidator('Extension', false, 'xml');
		$this->addElement($element);
	}
	
	protected function _addYear()
	{
		$elt = new Zend_Form_Element_Select('study_year');
		$elt->setLabel($this->_translate->_('li0345000100'));		
		
		for ($i = date('Y'); $i >= self::RANGE_YEAR_START; $i--)
		{
			$elt->addMultiOption($i, $i);
		}
		
		$elt->setRequired(True);
		
		if (isset($this->_studydescription))
		{
			$year = $this->_studydescription['year'];
			$year = explode('-', $year);
			$elt->setValue($year[0]);
		}
		
		$this->addElement($elt);
	}

	protected function _addStudySerieId()
	{
		$mapper = new DB_Mapper_StudySerie;
		$options = array();
		$options[''] = $this->_translate->_('li0345000150');
		$ctlg = Zend_Registry::get('translationLanguageGuiCurrent');
		$series = $mapper->findAllWithDetails($ctlg->get_id());

		foreach ($series as $serie)
		{
			$options[$serie['id']] = $serie['title'];
		}

		$select = new Zend_Form_Element_Select('study_serie_id');
		$select->setLabel($this->_translate->_('li0345000200'));
		$select->addMultiOptions($options);

		if (isset($this->_ddifile))
		{
			$select->setValue($this->_ddifile->get_study_serie_id());
		}

		$this->addElement($select);
	}
	
	protected function _addDomainId()
	{
		$mapper = new DB_Mapper_Domain;
		$options = array();
		$ctlg = Zend_Registry::get('translationLanguageGuiCurrent');
		$domains = $mapper->findAllWithDetails($ctlg->get_id());

		foreach ($domains as $domain)
		{
			$options[$domain['id']] = stripslashes($domain['title']);
		}

		$select = new Zend_Form_Element_Multiselect('domain_ids');
		$select->setLabel($this->_translate->_('li0345000250'));
		$select->addMultiOptions($options);

		if (isset($this->_ddifile))
		{
			$mapper = new DB_Mapper_Domain;
			$_ids = $mapper->findDomainIdsForDdifile($this->_ddifile->get_id());
			$ids = array();

			foreach ($_ids as $id)
			{
				$ids[] = $id['domain_id'];
			}

			$select->setValue($ids);
		}

		$this->addElement($select);
	}
	
	protected function _addConceptListId()
	{
		$mapper = new DB_Mapper_ConceptList;
		$options = array();
		$options[''] = $this->_translate->_('li0345000300');
		$ctlg = Zend_Registry::get('translationLanguageGuiCurrent');
		$cls = $mapper->findAllWithDetails($ctlg->get_id());

		foreach ($cls as $cl)
		{
			$options[$cl['id']] = $cl['title'];
		}

		$select = new Zend_Form_Element_Select('concept_list_id');
		$select->setLabel($this->_translate->_('li0345000350'));
		$select->addMultiOptions($options);

		if (isset($this->_ddifile))
		{
			$select->setValue($this->_ddifile->get_concept_list_id());
		}

		$this->addElement($select);
	}
	
	protected function _addNesstarServerId()
	{
		$mapper = new DB_Mapper_NesstarServer;
		$nss = $mapper->findAll();
		$nss = $nss ? $nss : array();
		$options = array();
		$options[''] = $this->_translate->_('li0345000400');
		
		foreach ($nss as $ns)
		{
			$options[$ns->get_id()] = $ns->get_title();
		}
		
		$select = new Zend_Form_Element_Select('nesstar_server_id');
		$select->setLabel($this->_translate->_('li0345000450'));
		$select->addMultiOptions($options);
		
		if (isset($this->_ddifile))
		{
			$select->setValue($this->_ddifile->get_nesstar_server_id());
		}
		
		$this->addElement($select);
	}
	
	protected function _addQuestionnaireUrl()
	{
		$questionnaireUrl = new Zend_Form_Element_Text('questionnaire_url');
		$questionnaireUrl->setLabel($this->_translate->_('li0345000500'))
						->addValidator(new BDQ_UrlValidator);
						
		if (isset($this->_ddifile))
		{
			$questionnaireUrl->setValue($this->_ddifile->get_questionnaire_url());
		}
						
		$this->addElement($questionnaireUrl);
	}

	protected function _addQuestionnaireFile()
	{
		$element = new Zend_Form_Element_File('questionnaire_file_name');
		$element->setLabel($this->_translate->_('li0345000550'))
				->setDestination(QUESTIONNAIRE_FILES . '/temp')
				->setMaxFileSize('20000000')
				->addValidator('Extension', false, 'pdf')
				->setMultiFile(10);
		$this->addElement($element);
	}
	
	protected function _addMultipleItemAnalysis()
	{
		$element = new Zend_Form_Element_Radio('item_analysis');
		$analysis = array(
			self::MULTIPLE_ITEM_NO_ANALYSIS => $this->_translate->_('li0345000600'),
			self::MULTIPLE_ITEM_NEARBY_ANALYSIS => $this->_translate->_('li0345000650'),
		);
		$element->setLabel($this->_translate->_('li0345000700'))
				->setMultiOptions($analysis)
				->setValue(0);
		$this->addElement($element);
	}
	
	protected function _addQuestionItemSeparator()
	{
		$elt = new Zend_Form_Element_Textarea('question_item_separator');
		$elt->setLabel($this->_translate->_('li0345000750'))
			->setAttrib('rows', '3')
			->setAttrib('cols', '5');
		$elt->getDecorator('HtmlTag')->setOption('class', 'hidden qi_sep');
		$elt->getDecorator('Label')->setOption('class', 'hidden qi_sep');
		$this->addElement($elt);
	}
	
	protected function _addSubmit()
	{
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label' => $this->_translate->_('li0345000800'),
		));
	}

	protected function setDdifile(DB_Model_DdiFile $value)
	{
		$this->_ddifile = $value;
		return $this;
	}
	
	protected function getDdifile()
	{
		return $this->_ddifile;
	}
	
	protected function setStudydescription(array $description)
	{
		$this->_studydescription = $description;
		return $this;
	}
	
	protected function getStudydescription()
	{
		return $this->_studydescription;	
	}
	
}