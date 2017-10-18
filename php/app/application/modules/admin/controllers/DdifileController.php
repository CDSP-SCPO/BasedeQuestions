<?php
/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class Admin_DdifileController extends BDQ_Locale_AdminController
{

	/**
	 * Stores data when adding a file : 
	 * <ul>
	 *  <li>initially from the HTTP POST request</li>
	 *  <li>then data from the ddi file analysis is added</li>
	 *  <li>then identifiers from database insertions (last insert id) are added</li>
	 * </ul>
	 * 
	 * @var array
	 */
	protected $_data;

	/**
	 * @var DDI_Parser122
	 */
	protected $_parser;
	
	/**
	 * @var DB_Mapper_Variable
	 */
	protected $_variableMapper;
	
	/**
	 * @var DB_Mapper_Question
	 */
	protected $_questionMapper;
	
	/**
	 * @var DB_Mapper_Category
	 */
	protected $_categoryMapper;

	/**
	 * @var DB_Mapper_VariableGroup
	 */
	protected $_variableGroupMapper;

	/**
	 * @var array
	 */
	protected $_solrDocuments = array();
	
	/**
	 * @var array
	 */
	protected $_concepts;
	
	/**
	 * @var Zend_Translate
	 */
	protected $_translate;
	
    public function init()
    {
    	parent::init();
    }

	public function indexAction()
    {
    	
		$mapper = new DB_Mapper_Ddifile;
		$this->view->ddifiles = $mapper->findAllWithDetails($this->_translationLanguageGuiCurrent->get_id());
    }
    
    public function viewAction()
    {
    	$request = $this->getRequest();

    	if (($id = $request->getBDQParam('id')) && is_numeric($id))
    	{
    		$mapper = new DB_Mapper_Ddifile;
    		$this->view->ddifile = $mapper->findWithDetails(
    			$id,
    			$this->_translationLanguageGuiCurrent->get_id()
    		);

    		if ($this->view->ddifile === NULL)
    		{
    			$this->_redirectToDdifileIndex();
    		}

    		$mapper = new DB_Mapper_Domain;
    		$domains = $mapper->findTitleTranslationsForDdifile($id, $this->_translationLanguageGuiCurrent->get_id());
    		$this->view->domains = $domains;
    		
    		$mapper = new DB_Mapper_Questionnaire;
    		$this->view->questionnaires = $mapper->findForDdifile($id);
    		
    		$select = new Solr_Select("ddiFileId:$id");
    		$select->setFl(array('id'));
    		$this->view->solr_response = $select->send();
    	}

    	else
    	{
    		$this->_redirectToDdifileIndex();
    	}

    }

	public function addAction()
    {
    	$form = new BDQ_Form_Ddifile;
        $request = $this->getRequest();

		if ($request->isPost())
        {

        	if ($form->isValid($request->getPost()) && $form->ddifile->receive())
			{
				// DDI file
				$fileName = $form->ddifile->getFileName();
				$newName = basename($fileName);
				$newName = explode('.', $newName);
				$ext = array_pop($newName);
				$newName = implode('.', $newName);
				$newName .= '-' . time() . '.' . $ext;
				rename($fileName, $fileNewPath = realpath(dirname($fileName) . '/../') . '/' . $newName);
				$this->_data = $request->getPost();
				$this->_data['file_name'] = basename($newName);
				$this->_data['file_new_path'] = $fileNewPath;
				$this->_data['questionnaire_title'] = NULL;
				$this->_moveUploadedQuestionnairePDF($form);

				if(isset($this->_data['question_item_separator']))
				/*
				 * A new line entered in a text area becomes a line feed and a carriage return.
				 * This make it a line feed.
				 */ 
				{
					$this->_data['question_item_separator'] = str_replace(
						"\r\n",
						"\n",
						$this->_data['question_item_separator']
					);
				}
				
				if ($this->_data['question_item_separator'] == '')
				{
					$this->_data['question_item_separator'] = ' - ';
				}
				
				/**
				 * Post data is saved in a session variable.
				 * The request is then handled by the appropriate action
				 */
				$ddiFileNS = new Zend_Session_Namespace('ddiFile');
				$ddiFileNS->postdata = $this->_data;
				
				if (((int) $this->_data['item_analysis']) === BDQ_Form_Ddifile::MULTIPLE_ITEM_NO_ANALYSIS)
				{
					$this->_redirectToDdifileAnalysis();
				}
				
				else
				{
					$this->_redirectToDdifileMultipleitemlist();
				}
				
			}

		}

		$this->view->form = $form;
    }
    
    protected function _moveUploadedQuestionnairePDF($form)
    {
		if (($fileNames = $form->questionnaire_file_name->getFileName()) && $form->questionnaire_file_name->receive())
		{

			if(is_string($fileNames))
			{
				$fileNames = array($fileNames);
			}
			
			foreach ($fileNames as $fileName)
			{
				$newName = basename($fileName);
				$newName = explode('.', $newName);
				$ext = array_pop($newName);
				$newName = implode('.', $newName);
				$newName .= '-' . time() . '.' . $ext;
				rename($fileName, $fileNewPath = realpath(dirname($fileName) . '/../') . '/' . $newName);
				$this->_data['questionnaire_file_name'][] = basename($newName);
			}

			$this->_data['questionnaire_title'] = $_POST['questionnaire_title'];
			$this->_data['questionnaire_id'] = $_POST['questionnaire_id'];
		}
    }

 	public function analysisAction()
    {
    	ini_set('memory_limit', '256M');
    	
    	$this->_translate = Zend_Registry::get('translateAdmin');
    	
    	$this->view->layout()->setLayout('empty');
    	$this->render();
    	$response = $this->getResponse();
    	$response->outputBody();	/* sends some Javascript to scroll the page down while
									 * data about the ongoing operation is sent to the administrator browser
    								 */
    	
    	$ddiFileNS = new Zend_Session_Namespace('ddiFile');
		$this->_data = $ddiFileNS->postdata;
		
		$bootstrap = $this->getInvokeArg('bootstrap');
		$db = $bootstrap->getResource('db');
		$db->beginTransaction();

		$solrClient = Solr_Client::getInstance();
		
		try
		{
			$this->_parseAndSaveDdifileInformations(); // create database entities, and solr documents
			echo $this->_translate->_('fr0025000000');
    		ob_flush();flush();
    		$solrUpdate = new Solr_Update($this->_solrDocuments);
			$solrUpdate->send();
			
			echo $this->_translate->_('fr0025000010');
			ob_flush();flush();
			$solrClient->commit();
			
			echo $this->_translate->_('fr0025000020');
			ob_flush();flush();
			$solrClient->optimize();
			
			echo $this->_translate->_('fr0025000030');
			ob_flush();flush();
			$db->commit();
			
			echo $this->_translate->_('fr0025000040');
			ob_flush();flush();
		}
		
		catch (Exception $e)
		{
			$db->rollBack();
			$solrClient->rollback();

			if (isset($this->_data['ddi_file_id']))
			{

				{
					$delete = new Solr_Delete(array(
						'query' => 'ddiFileId: ' . $this->_data['ddi_file_id']
					));
					$delete->send();
					$solrClient->commit();
					$solrClient->optimize();
				}

				$mapper = new DB_Mapper_Ddifile;
				$mapper->delete($this->_data['ddi_file_id']);
				{
					
					if (file_exists(DDI_FILES . $this->_data['file_name']))
					{
						unlink(DDI_FILES . $this->_data['file_name']);
					}
				
				}

				{
					$questionnaires = isset($this->_data['questionnaire_file_name']) ? $this->_data['questionnaire_file_name'] : array();
					
					foreach ($questionnaires as $questionnaire)
					{
						
						if (file_exists(QUESTIONNAIRE_FILES . $questionnaire))
						{
							unlink(QUESTIONNAIRE_FILES . $questionnaire);
						}
	
					}
				
				}
				
			}
			
			echo $this->_translate->_('fr0025000050');
			echo "<br/>";
			echo $this->_translate->_('fr0025000060');
			echo "<br/>";
			echo $this->_translate->_('fr0025000070');
			echo "<br/>";

		}
		
		echo 	'<a href="', 
							$this->view->url(
								array(),
								'ddifileIndex'
							), 
							'">', 
				$this->_translate->_('fr0025000080'), 
				'</a><br/>',
				<<<HEREDOC
<script type="text/javascript">
	window.scrollTo(0,document.height);
	clearInterval(timer);
</script>
HEREDOC;
;
		ob_flush();flush();
		die;
    }
    
    /**
     * @return int
     */
    protected function _saveDdifile()
    {
		$ddifile = new DB_Model_Ddifile;

		foreach ($this->_data as $name => $value)
		{

			if (method_exists($ddifile, $method = "set_$name"))
			{
				$ddifile->$method($value);
			}

			$ddifile->set_translation_language_id($this->_data['lang_id']);
			$ddifile->set_multiple_item_parsed((int) ! ((int) $this->_data['item_analysis'] === BDQ_Form_Ddifile::MULTIPLE_ITEM_NO_ANALYSIS));

		}

		$mapper = new DB_Mapper_Ddifile;
		$this->_data['ddi_file_id'] = $mapper->save($ddifile);
		
		$this->_saveDomainsId();
		
		return $this->_data['ddi_file_id'];
    }
    
    /**
     * @return array
     */
    protected function _saveQuestionnaires()
    {
    	$questionnaires =  isset($this->_data['questionnaire_file_name']) ? $this->_data['questionnaire_file_name'] : array();
    	$l = count($questionnaires);
    	$questionnaireMapper = new DB_Mapper_Questionnaire;
    	$ids = NULL;
    	
    	for ($i = 0; $i < $l; $i++)
    	{
    		$questionnaire = $questionnaires[$i];
    		$questionnaire = new DB_Model_Questionnaire;
    		$questionnaire->set_ddi_file_id($this->_data['ddi_file_id']);
    		$questionnaire->set_file_name($this->_data['questionnaire_file_name'][$i]);
    		$questionnaire->set_title($this->_data['questionnaire_title'][$i]);

    		$ids[] = $questionnaireMapper->save($questionnaire);
    	}
    	
    	return $ids;
    }
    
    protected function _saveDomainsId()
    {
		$mapper = new DB_Mapper_DomainDdifile;

   		if ( ! isset($this->_data['domain_ids']))
		{
			$this->_data['domain_ids'] = array();
		}

		foreach ($this->_data['domain_ids'] as $domain_id)
		{
			$ddf = new DB_Model_DomainDdifile;
			$ddf->set_ddi_file_id($this->_data['ddi_file_id']);
			$ddf->set_domain_id($domain_id);
			$mapper->save($ddf);
		}

    }

    /**
     * @param string $ddiFilePath path to the XML DDI file
     * @return unknown_type
     */
    protected function _parseAndSaveDdifileInformations()
    {
    	$this->_data['ddi_file_id'] = $this->_saveDdifile();
    	$this->_data['questionnaires_id'] = $this->_saveQuestionnaires();
    	$this->_parser = new DDI_Parser122($this->_data['file_new_path']);
    	echo $this->_translate->_('fr0025000090'), '<br/>';
    	ob_flush();flush();

    	$this->_data['study_description']['id'] = $this->_saveStudyDescription();
    	$this->_data['producers_id'] = $this->_saveProducers();
    	$this->_data['collect_dates_id'] = $this->_saveCollectDates();
    	$this->_data['distributors_id'] = $this->_saveDistributors();
    	$this->_data['funding_agencies_id'] = $this->_saveFundingAgencies();

    	echo $this->_translate->_('fr0025000100'), '<br/>';
    	ob_flush();flush();

    	{
    		$this->_variableMapper = new DB_Mapper_Variable;
    		$this->_questionMapper = new DB_Mapper_Question;
    		$this->_categoryMapper = new DB_Mapper_Category;
    		
    		if (
    			isset($this->_data['concept_list_id'])
    			&& ! empty($this->_data['concept_list_id'])
    		)
    		{
    			$this->_concepts = $this->_getConcepts();
    		}

	    	if (((int) $this->_data['item_analysis']) === BDQ_Form_Ddifile::MULTIPLE_ITEM_NO_ANALYSIS)
	    	{
	    		$this->_handleVariables();
	    	}

	    	else
	    	{
	    		$this->_handleVariablesAndMultipleItemGroups();
	    	}

    	}
    	

    }
    
    /**
     * @return int study_descriptions table inserted row primary key
     */
    protected function _saveStudyDescription()
    {
    	$studyDescription = $this->_parser->getStudyDescription();
    	$this->_data['study_description'] = $studyDescription;
    	$study = new DB_Model_StudyDescription;
    	$study->set_ddi_file_id($this->_data['ddi_file_id']);
    	$study->set_nesstar_id($studyDescription['nesstar_id']);
    	$study->set_title($studyDescription['title']);
    	$study->set_abstract($studyDescription['abstract']);
    	$study->set_keywords($studyDescription['keywords']);
    	$study->set_analysis_unit($studyDescription['analysis_unit']);
    	$study->set_year($this->_data['study_year'] . '-06-29');
    	$study->set_collect_mode($studyDescription['collect_mode']);
    	$study->set_geographic_coverage($studyDescription['geographic_coverage']);
    	$study->set_sample_procedure($studyDescription['sample_procedure']);
    	$study->set_universe($studyDescription['universe']);
    	$study->set_nation($studyDescription['nation']);
    	$study->set_case_quantity($studyDescription['case_quantity']);
    	$mapper = new DB_Mapper_StudyDescription;
    	return $mapper->save($study);
    }
    
    /**
     * @return array producers table inserted rows primary keys 
     */
	protected function _saveProducers()
    {
    	$producers = $this->_parser->getProducers();
    	$mapper = new DB_Mapper_Producer;
    	$ids = array();
    	
    	foreach ($producers as $prod)
    	{
    		$producer = new DB_Model_Producer;
    		$producer->set_ddi_file_id($this->_data['ddi_file_id']);
    		$producer->set_title($prod['title']);
    		$producer->set_abbreviation($prod['abbreviation']);
    		$ids[] = $mapper->save($producer);
    	}
    	
    	return $ids;
    }
    
	/**
     * @return array collect_dates table inserted rows primary keys 
     */
	protected function _saveCollectDates()
    {
    	$dates = $this->_parser->getCollectDates();
    	$mapper = new DB_Mapper_CollectDate;
    	$ids = array();
    	
    	foreach ($dates as $date)
    	{
    		$_date = new DB_Model_CollectDate;
    		$_date->set_ddi_file_id($this->_data['ddi_file_id']);
    		$_date->set_event($date['event']);
    		$_date->set_date($date['date']);
    		$_date->set_cycle($date['cycle']);
    		$ids[] = $mapper->save($_date);
    	}
    	
    	return $ids;
    }
    
 	/**
     * @return array distributors table inserted rows primary keys 
     */
	protected function _saveDistributors()
    {
    	$distributors = $this->_parser->getDistributors();
    	$mapper = new DB_Mapper_Distributor;
    	$ids = array();
    	
    	foreach ($distributors as $dist)
    	{
    		$_dist = new DB_Model_Distributor;
    		$_dist->set_ddi_file_id($this->_data['ddi_file_id']);
    		$_dist->set_title($dist['title']);
    		$_dist->set_abbreviation($dist['abbreviation']);
    		$ids[] = $mapper->save($_dist);
    	}
    	
    	return $ids;
    }
    
 	/**
     * @return array funding_agencies table inserted rows primary keys 
     */
	protected function _saveFundingAgencies()
    {
    	$agencies = $this->_parser->getFundingAgencies();
    	$mapper = new DB_Mapper_FundingAgency();
    	$ids = array();
    	
    	foreach ($agencies as $ag)
    	{
    		$_ag = new DB_Model_FundingAgency;
    		$_ag->set_ddi_file_id($this->_data['ddi_file_id']);
    		$_ag->set_title($ag['title']);
    		$_ag->set_abbreviation($ag['abbreviation']);
    		$ids[] = $mapper->save($_ag);
    	}
    	
    	return $ids;
    }
    
    /**
     * @return void
     */
    protected function _handleVariables()
    {
    	echo $this->_translate->_('fr0025000110'), '<br/>';ob_flush();flush();
    	$variables = & $this->_parser->getVariables();
    	$l = count($variables);
    	
    	for($i = 0; $i < $l; $i++)
    	{
    		echo $this->_translate->_('fr0025000120') . $variables[$i]['name'] . '<br/>';
    		ob_flush();flush();
    		$this->_handleVariable($variables[$i]);
    	}
    	
    }
    
    /**
     * @return void
     */
    protected function _handleVariable(array $variable, $groupId = null)
    {
    	
    	if 
    	(
    		isset($this->_data['concept_list_id'])
    		&& ! empty($this->_data['concept_list_id'])
    		&& isset($variable['concept'])
    		&& ! empty($variable['concept'])
    	)
    	{
    		$concept = $this->_getReferenceConcept($variable['concept']);
    		$variable['concept'] = $concept['concept_title'];
    		$variable['concept_id'] = $concept['concept_id'];
    	}

    	$variableId = $this->_saveVariable($variable, $groupId);
    	$variable['id'] = $variableId;
    	$variable['question_id'] = $this->_saveQuestion($variable);
    	$categoryIds = array();
    	
    	foreach ($variable['categories'] as $category)
    	{
    		$category['variable_id'] = $variableId;
    		$categoryIds[] = $this->_saveCategory($category);
    	}
    	
    	if ($groupId === null)
    	{
   			$this->_addSolrDocument($variable);
    	}
    	
    	$result = array(
    		'variable_id' => $variableId,
    		'question_id' => $variable['question_id'],
    		'categories_id' => $categoryIds,
    	);
    	
    	if (isset($variable['concept_id']))
    	{
    		$result['concept_id'] = $variable['concept_id'];
    		$result['concept'] = $variable['concept'];
    	}
    	
    	return $result;
    }
    
    /**
     * @param array $variable
     * @param int $variableGroupId
     * @return int variables table inserted row primary key
     */
	protected function _saveVariable(array $variable, $variableGroupId = null)
    {
    	$var = new DB_Model_Variable;
    	$var->set_ddi_file_id($this->_data['ddi_file_id']);
    	$var->set_nesstar_id($variable['nesstar_id']);
    	$var->set_name($variable['name']);
    	$var->set_label($variable['label']);
    	$var->set_notes($variable['notes']);
    	$var->set_valid($variable['valid']);
    	$var->set_invalid($variable['invalid']);
    	$var->set_universe($variable['universe']);

    	if (isset($variable['concept_id']))
    	{
    		$var->set_concept_id($variable['concept_id']);
    	}

    	if (isset($variableGroupId))
    	{
    		$var->set_variable_group_id($variableGroupId);
    	}

    	return $this->_variableMapper->save($var);
    }
    
    /**
     * @param array $variable
     * @return void
     */
    protected function _addSolrDocument(array $variable)
    {
    	static $questionPosition = 1;
    	
    	$tls = Zend_Registry::get('translationLanguagesSolr');
    	
    	foreach ($tls as $tl)
    	{
    		if ($tl->get_id() == $this->_data['lang_id'])
    		{
    			$solrLangCode = $tl->get_code_solr();
    			break;
    		}
    	}

    	$doc = new Solr_Document;
    	$doc->set_id($variable['id']);
		$doc->set_langId($this->_data['lang_id']);
		$doc->set_solrLangCode($solrLangCode);
		$doc->set_domainId(
			(isset($this->_data['domain_ids']) && ! empty($this->_data['domain_ids']))
			?
			$this->_data['domain_ids']
			:
			SOLR_NULL
		);
		$doc->set_studySerieId(
			(isset($this->_data['study_serie_id']) && ! empty($this->_data['study_serie_id']))
			?
			$this->_data['study_serie_id']
			:
			SOLR_NULL
		);
		$doc->set_ddiFileId($this->_data['ddi_file_id']);
		$doc->set_conceptListId($this->_data['concept_list_id']);
		$doc->set_studyDescriptionId($this->_data['study_description']['id']);
		$doc->set_questionId($variable['question_id']);
		$doc->set_questionnaireId($this->_data['questionnaires_id']);
		$doc->set_questionnaireTitle($this->_data['questionnaire_title']);
		$doc->set_variableId($variable['id']);
		$doc->set_variableNesstarId($variable['nesstar_id']);
		$doc->set_nesstarServerId($this->_data['nesstar_server_id'] ? $this->_data['nesstar_server_id'] : SOLR_NULL);
		$doc->set_studyNesstarId($this->_data['study_description']['nesstar_id']);

		$doc->set_conceptId(isset($variable['concept_id']) ? $variable['concept_id'] : SOLR_NULL);
		
		$doc->set_studyQuestionCount(
			isset($this->_data['solr_documents_with_question_count']) 
			? 
			($this->_data['study_description']['question_count'] - $this->_data['solr_documents_with_question_count']) 
			: 
			$this->_data['study_description']['question_count']
		);
		
		if (isset($variable['group_id']))
		{
			$doc->set_id($variable['id'][0]);
			$doc->set_hasMultipleItems("true");
			$doc->set_variableGroupId($variable['group_id']);
			$meth = "set_q$solrLangCode";
			$doc->$meth($variable['question']['question_no_item']);
			$meth ="set_i$solrLangCode";
			$doc->$meth($variable['question']['items']);
			
			{ // ADD A NUMBER BEFORE EACH ITEM TO EASE THEIR HIGHLIGHTING
				$i = 0;
				$items = array();
				
				foreach($variable['question']['items'] as $item)
				{
					$items[] = sprintf('%05d ', $i) . $item;
					$i++;
				}
				
			}
			
			$meth ="set_iHL$solrLangCode";
			$doc->$meth($items);
		}
		
		else
		{
			$doc->set_hasMultipleItems("false");
			$meth = "set_q$solrLangCode";
			$doc->$meth($variable['question']['question_text']);
		}
		
		$doc->set_variableName($variable['name']);
		
   		if (is_string($variable['label']))
		{
			$variable['label'] = array($variable['label']);
		}
		
   		if (is_string($variable['name']))
		{
			$variable['name'] = array($variable['name']);
		}
		
		$vs = array();
		$_vs = array();
		$l = count($variable['label']);

		for ($i = 0; $i < $l; $i++)
		{
			$vl = $variable['label'][$i];
			$vn = $variable['name'][$i];
			$v = "$vn - $vl";
			$vs[] = $v;
			$_vs[] = sprintf('%05d ', $i) . $v;
		}
		
		$meth = "set_vl$solrLangCode";
		$doc->$meth($vs);
		$meth = "set_vlHL$solrLangCode";
		$doc->$meth($_vs);
		$doc->set_studyTitle($this->_data['study_description']['title']);
		$doc->set_studyYear($this->_data['study_year']);
		$decade = $this->_data['study_year'];
    	$decade[3] = '5';
    	$doc->set_studyDecade($decade);
		$doc->set_questionnaireUrl($this->_data['questionnaire_url']);
		
		if ( ! $this->_data['questionnaire_url'] && ! $this->_data['questionnaires_id'])
		{
			$doc->set_hasQuestionnaire('false');
		}
		
		else
		{
			$doc->set_hasQuestionnaire('true');
		}
		
		$doc->set_universe($variable['universe']);
		$doc->set_notes($variable['notes']);
		$doc->set_preQuestionText($variable['question']['pre_question_text']);
		$doc->set_postQuestionText($variable['question']['post_question_text']);
		$doc->set_interviewerInstructions($variable['question']['interviewer_instruction']);
		$doc->set_questionPosition(
			(
				! empty($variable['question']['question_text'])
				&&
				isset($variable['question']['preceding_question_count'])
				&& 
				! empty($variable['question']['preceding_question_count'])
			)
			?
			$questionPosition++
			:
			NULL
		);
		
		$categories = $variable['categories'];
		$modalities = array();
		$_modalities = array();
		$i = 0;
		
		foreach ($categories as $category)
		{
			$modalities[] = $category['value'] . ((isset($category['label']) && ! empty($category['label'])) ? ' - ' : '') . $category['label'];
			$_modalities[] = sprintf('%05d ', $i) . $category['value'] . ((isset($category['label']) && ! empty($category['label'])) ? ' - ' : '') . $category['label'];
			$i++;
		}
		
		$meth = "set_mHL$solrLangCode"; 
		$doc->$meth($_modalities);
		$meth = "set_m$solrLangCode"; 
		$doc->$meth($modalities);

		$this->_solrDocuments[] = $doc;
    }
    
    /**
     * @param array $variable
     * @return unknown_type
     */
    protected function _saveQuestion(array $variable)
    {
    	$qu = new DB_Model_Question;
    	
    	$qu->set_interviewer_instructions($variable['question']['interviewer_instruction']);
    	
    	if ( ! isset($variable['group_id']))
    	{
    		$qu->set_litteral($variable['question']['question_text']);
    	}
    	
    	else
    	{
    		$qu->set_litteral($variable['question']['question_no_item']);
    		$qu->set_item($variable['question']['item']);
    	}
    	
    	$qu->set_post_question_text($variable['question']['post_question_text']);
    	$qu->set_pre_question_text($variable['question']['pre_question_text']);
    	$qu->set_variable_id($variable['id']);
    	return $this->_questionMapper->save($qu);
    }
    
    /**
	 * @param array $category
	 * @return int categories table row primary key
     */
    protected function _saveCategory(array $category)
    {
    	$cat = new DB_Model_Category;
    	$cat->set_variable_id($category['variable_id']);
    	$cat->set_label($category['label']);
    	$cat->set_stats($category['stats']);
    	$cat->set_type($category['stats_type']);
    	$cat->set_value($category['value']);
    	$cat->set_missing($category['missing']);
    	return $this->_categoryMapper->save($cat);
    }
    
    protected function _handleVariablesAndMultipleItemGroups()
    {
    	$this->_variableGroupMapper = new DB_Mapper_VariableGroup;
    	$variables = $this->_parser->getVariables();
    	$variablesItemGroups = $this->_parser->getVariablesMultipleItemNearbyAnalysis(
    		$this->_data['question_item_separator']
    	);
    	$ddiFileNS = new Zend_Session_Namespace('ddiFile');
    	$selectedBatteryKeys = array_flip($ddiFileNS->postdata['selected_batteries']);
    	$variablesItemGroups = array_intersect_key($variablesItemGroups, $selectedBatteryKeys);
    	$variablesItemGroups = array_values($variablesItemGroups);
    	
    	$vn = $this->_getMultipleItemVariableNames($variablesItemGroups);
    	
    	$this->_data['solr_documents_with_question_count'] = $this->_getSolrDocumentWithQuestionCount($variablesItemGroups);
    	
		$handledGroups = array();
    	
    	foreach ($variables as $variable)
    	{
    		
    		if
    		(
    			array_key_exists($variable['name'], $vn) 
    		&& 
    			! in_array($group = $vn[$variable['name']], $handledGroups)
    		)
    		{
    			$this->_handleVariableGroup($variablesItemGroups[$group]);
    			$handledGroups[] = $group;
    		}
    		
    		elseif 
    		(
    			array_key_exists($variable['name'], $vn) 
    		&& 
    			in_array($group = $vn[$variable['name']], $handledGroups)
    		)
    		{
    			continue;
    		}
    		
    		else
    		{
    			echo $this->_translate->_('fr0025000130') . $variable['name'] . '<br/>';
    			ob_flush();flush();
    			$this->_handleVariable($variable);
    		}
    		
    	}
    	
    }
    
    /**
     * Returns an array which keys are variable names and values are variable's group number
     * 
     * @param array $variablesItemGroups
     * @return array
     */
    protected function _getMultipleItemVariableNames(array $variablesItemGroups)
    {
    	$vn = array();
    	$i = 0;
    	
    	foreach ($variablesItemGroups as $group)
    	{
    		
    		foreach ($group as $var)
    		{
    			$vn[$var['name']] = $i;
    		}
    		
    		$i++;
    	}
    	
    	return $vn;
    }
    
    /**
     * @param array $variablesItemGroups
     * @return int
     */
    protected function _getSolrDocumentWithQuestionCount(array $variablesItemGroups)
    {
    	$c = 0;
    	$l = count($variablesItemGroups);
    	
    	for ($i = 0; $i < $l; $i++)
    	{
    		$c += count($variablesItemGroups[$i]) - 1;
    	}
    	
    	return $c;
    }
    
    /**
     * @param array $group
     * @return void
     */
    protected function _handleVariableGroup(array $group)
    {
    	$groupId = $this->_saveVariableGroup();
    	$items = array();
    	$names = array();
    	$labels = array();
    	$nesstarIds = array();
    	$ids = array();
    	$conceptIds = array();
    	$conceptPositionsAndIds = array();
    	
    	foreach ($group as $variable)
    	{
    		$variable['group_id'] = $groupId;
    		echo $this->_translate->_('fr0025000140') . $variable['name'] . '<br/>';
    		ob_flush();flush();
    		$ids[] = $this->_handleVariable($variable, $groupId);
    		$items[] = $variable['question']['item'];
    		$names[] = $variable['name'];
    		$labels[] = $variable['label'];
    		$nesstarIds[] = $variable['nesstar_id'];
    		$questionPositions[] = $variable['question']['preceding_question_count'];
    	}
    	
    	$ids = $this->_formatHandleVariableGroupIds($ids);
    	
    	unset($variable['item']);
    	
    	$variable['question']['items'] = $items;
    	$variable['name'] = $names;
    	$variable['label'] = $labels;
    	$variable['nesstar_id'] = $nesstarIds;
    	$variable['id'] = $ids['variable_id'];
    	$variable['question_id'] = $ids['question_id'];
    	$variable['question']['preceding_question_count'] = $questionPositions;
    	
    	if (isset($ids['concept_id']) && $ids['concept_id'] !== array())    	
    	{
    		$variable['concept'] = $ids['concept'];
    		$variable['concept_id'] = $ids['concept_id'];
    	}
    	
    	$this->_addSolrDocument($variable);
    }
    
    /**
     * @return int
     */
    protected function _saveVariableGroup()
    {
    	$vg = new DB_Model_VariableGroup;
    	$vg->set_ddi_file_id($this->_data['ddi_file_id']);
    	return $this->_variableGroupMapper->save($vg);
    }
    
    /**
     * Format the question and the variable identifiers for the handleVariableGroup method
     * 
     * @param array $ids
     * @return array
     */
    protected function _formatHandleVariableGroupIds($ids)
    {
    	$variable_ids = array();
    	$question_ids = array();
    	$concepts = array();
    	$concept_ids = array();
    	$concept_positions_and_ids = array();
    	
    	foreach ($ids as $id)
    	{
    		$variable_ids[] = $id['variable_id'];
    		$question_ids[] = $id['question_id'];
    		
    		if (isset($id['concept_id']))
    		{
    			$concepts[] = $id['concept'];
    			$concept_ids[] = $id['concept_id'];
    		}
    		
    	}
    	
    	$result = array(
    		'variable_id' => $variable_ids,
    		'question_id' => $question_ids
    	);
    	
    	if ($concept_ids !== array())
    	{
    		$result['concept'] = $concepts;
    		$result['concept_id'] = $concept_ids;
    	}
    	
    	return $result;
    }
    
	public function editAction()
    {
    	ini_set('memory_limit', '256M');
    	$request = $this->getRequest();

		if (($id = $request->getBDQParam('id')) && ! $request->isPost() && is_numeric($id))
		{
			$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
			$dfmapper = new DB_Mapper_Ddifile;
			$this->view->study = $dfmapper->findWithStudyTitle($id);
			
			if ($this->view->study === NULL)
			{
				$this->_redirectToDdifileIndex();
			}
			
			$sdmapper = new DB_Mapper_StudyDescription;
			$this->view->form = new BDQ_Form_Ddifile(
				array(
					'ddifile' => $dfmapper->find($id),
					'studydescription' => $sdmapper->findForDdifile($id)
				)
			);
			
		}

		elseif ($request->isPost() && is_numeric($id))
		{
			$this->getFrontController()->getRouter()->setGlobalParam('id', $id);
			$form = new BDQ_Form_Ddifile(
				array(
					'ddifile' => new DB_Model_Ddifile
				)
			);
			
			if ($form->isValid($data = $request->getPost()) || $form->getErrorMessages() == array())
			{
				$this->_data = $data;
				$this->_moveUploadedQuestionnairePDF($form);
				$this->_edit($data);
				$this->_redirectToDdifileIndex();
			}
			
			$this->view->form = $form;
		}

		else
		{
			$this->_redirectToDdifileIndex();
		}
		
		$questionnaireMapper = new DB_Mapper_Questionnaire;
		$this->view->questionnaires = $questionnaireMapper->findForDdifile($id);

    }
    
	/**
     * @param array $data
     * @return int
     */
    protected function _edit( )
    {
    	$bootstrap = $this->getInvokeArg('bootstrap');
		$db = $bootstrap->getResource('db');
		$db->beginTransaction();
		$client = Solr_Client::getInstance();
		
		try
		{
	    	$mapper = new DB_Mapper_Ddifile;
	    	$ddifile = $mapper->find($this->_data['id']);
	    	$ddifile->set_nesstar_server_id($this->_data['nesstar_server_id']);
	    	$ddifile->set_study_serie_id($this->_data['study_serie_id']);
	    	$ddifile->set_questionnaire_url($this->_data['questionnaire_url']);
	    	$mapper->save($ddifile);

	    	$this->_updateDomains();
	    	$this->_updateStudyYear();

	    	$questionnaireMapper = new DB_Mapper_Questionnaire;
			$questionnaireIds = NULL;
			$currentQuestionnaires = $questionnaireMapper->findForDdifile($this->_data['id']);

	    	if( ! empty($this->_data['questionnaire_url']))
	    	{
	    		$questionnaireMapper->deleteForDddile($this->_data['id']);
	    	}

	    	else
	    	{
				$l = count($this->_data['questionnaire_id']);
				$newQuestionnaires = array();

				for ($i = 0; $i < $l; $i++)
				{
					$questionnaire = NULL;
					$questionnaireId = $this->_data['questionnaire_id'][$i];
					$questionnaireTitle = $this->_data['questionnaire_title'][$i];
					$questionnaireFileName = $this->_data['has_file'][$i] ? array_shift($this->_data['questionnaire_file_name']) : NULL;

					if ($questionnaireId)
					{
						$questionnaire = $questionnaireMapper->find($questionnaireId);
						$questionnaire->set_id(NULL);

						if ($questionnaireFileName) 
						{
							$questionnaire->set_file_name($questionnaireFileName);
						}

						else
						/* No file was uploaded, the questionnaire is still in the list, its file
						 * will not be deleted
						 */
						{
							reset($currentQuestionnaires);

							while (list($i, $_questionnaire) = each($currentQuestionnaires))
							{

								if ($_questionnaire['questionnaire_id'] == $questionnaireId)
								{
									break;
								}

							}

							unset($currentQuestionnaires[$i]);
						}

						if ($questionnaireTitle)
						{
							$questionnaire->set_title($questionnaireTitle);
						}

					}

					else
					{

						if ($questionnaireFileName)
						{
							$questionnaire = new DB_Model_Questionnaire;
							$questionnaire->set_ddi_file_id($this->_data['id']);
							$questionnaire->set_file_name($questionnaireFileName);
							$questionnaire->set_title($questionnaireTitle);
						}

					}
					
					if ($questionnaire)
					{
						$newQuestionnaires[] = $questionnaire;
					}

				}

				$questionnaireMapper->deleteForDddile($this->_data['id']);
				$l = count($newQuestionnaires);

    			for ($j = 0; $j < $l; $j++)
				{
					$questionnaireIds[] = $questionnaireMapper->save($newQuestionnaires[$j]);
				}
				
				

	    	}

	    	$this->_deleteQuestionnaireFiles($currentQuestionnaires);

	    	$this->_data['questionnaire_ids'] = $questionnaireIds;
	    	$this->_getDdifileSolrDocuments();
	    	$this->_updateSolrDocuments();

	    	$solrUpdate = new Solr_Update($this->_solrDocuments);
	    	$solrUpdate->send();

	    	$client->commit();
	    	$client->optimize();

	    	$db->commit();
		}

		catch (Exception $e)
		{
			$db->rollBack();
			$client->rollback();
			echo $this->_translate->_('fr0025000150');
			echo "<br/>";
			echo $this->_translate->_('fr0025000160');
			echo "<br/>";
			echo $this->_translate->_('fr0025000170');
			echo "<br/>";
			die;
		}

    }
    
    protected function _updateDomains()
    {
		$mapper = new DB_Mapper_DomainDdifile;
		$mapper->deleteForDdifile($this->_data['id']);
		
		if ( ! isset($this->_data['domain_ids']))
		{
			$this->_data['domain_ids'] = array();
		}

    	foreach ($this->_data['domain_ids'] as $domain_id)
		{
			$ddf = new DB_Model_DomainDdifile;
			$ddf->set_ddi_file_id($this->_data['id']);
			$ddf->set_domain_id($domain_id);
			$mapper->save($ddf);
		}
		
    }
    
    protected function _updateStudyYear()
    {
    	$mapper = new DB_Mapper_StudyDescription;
    	return $mapper->updateYear($this->_data['id'], $this->_data['study_year'] . '-06-29');
    }
    
    protected function _getDdifileSolrDocuments()
    {
    	$select = new Solr_Select('ddiFileId: ' . $this->_data['id']);
    	$select->setRows(10000);
    	$select->setFl(
    		Solr_BDQ_Model_Question::$fields
    	);
    	$response = $select->send();
    	$response->createDocuments();
    	$this->_solrDocuments = $response->documents;
    } 
    
    protected function _updateSolrDocuments()
    {

    	foreach ($this->_solrDocuments as & $doc)
    	{
    		$doc->set_domainId(
    			(isset($this->_data['domain_ids']) && ! empty($this->_data['domain_ids'])) 
    			?
    			$this->_data['domain_ids']
    			:
    			SOLR_NULL
    		);
    		$doc->set_conceptListId(isset($this->_data['concept_list_id']) ? $this->_data['concept_list_id'] : '');
    		$doc->set_studySerieId(
    			(isset($this->_data['study_serie_id']) && ! empty($this->_data['study_serie_id'])) 
    			?
    			$this->_data['study_serie_id']
    			:
    			SOLR_NULL
    		);
    		$doc->set_nesstarServerId(
    			(isset($this->_data['nesstar_server_id']) && ! empty($this->_data['nesstar_server_id'])) 
    			?
    			$this->_data['nesstar_server_id']
    			:
    			SOLR_NULL
    		);
    		$doc->set_questionnaireId(
    			$this->_data['questionnaire_ids']
    		);
    		
    		$doc->set_questionnaireTitle(
    			$this->_data['questionnaire_title']
    		);
    		
    		$doc->set_questionnaireUrl($this->_data['questionnaire_url'] ? $this->_data['questionnaire_url'] : NULL);
    		
    		if ($doc->get_questionnaireId() || $doc->get_questionnaireUrl)
    		{
    			$doc->set_hasQuestionnaire(true);
    		}

    		$doc->set_studyYear($this->_data['study_year']);
    		$decade = $this->_data['study_year'];
    		$decade[3] = '5';
    		$doc->set_studyDecade($decade);
    	}
    	
    }
    
	public function multipleitemlistAction()
    {
    	ini_set('memory_limit', '256M');
    	$ddiFileNS = new Zend_Session_Namespace('ddiFile');
		$request = $this->getRequest();

		if ($request->isPost() && ($qis = $request->getBDQParam('question_item_separator')))
		{
			$ddiFileNS->postdata['question_item_separator'] = str_replace(
						"\r\n",
						"\n",
						$qis
			);
		}

		elseif ($request->isPost() && ($uidi = $request->getBDQParam('use_identified_item')) != NULL)
		{

			if ( ! $uidi)
			{
				$ddiFileNS->postdata['item_analysis'] = BDQ_Form_Ddifile::MULTIPLE_ITEM_NO_ANALYSIS;
			}

			else
			{
				$ddiFileNS->postdata['selected_batteries'] = $request->getBDQParam('selected_batteries');
			}

			$this->_redirectToDdifileAnalysis();
		}
		
		$this->_data = $ddiFileNS->postdata;
		
    	$this->_parser = new DDI_Parser122($this->_data['file_new_path']);
    	$this->view->mi_groups = $this->_parser->getVariablesMultipleItemNearbyAnalysis(
    		$this->_data['question_item_separator']
    	);
    }
    
	public function confirmdeleteAction()
    {
		$request = $this->getRequest();

		if ($id = $request->getBDQParam('id'))
		{
			$mapper = new DB_Mapper_Ddifile;
			$this->view->ddifile = $mapper->findWithStudyTitle($id);
		}

	}

	public function deleteAction()
    {
		$request = $this->getRequest();
		
    	if ($request->isPost() && ($id = $request->getBDQParam('id')) && is_numeric($id))
		{
			
			$bootstrap = $this->getInvokeArg('bootstrap');
			$db = $bootstrap->getResource('db');
			$db->beginTransaction();
			$client = Solr_Client::getInstance();

			try
			{
				{
					$ddiFileMapper = new DB_Mapper_Ddifile;
					$df = $ddiFileMapper->find($id);
					
					if (file_exists(DDI_FILES .$df->get_file_name()))
					{
						rename(
							DDI_FILES . $df->get_file_name(),
							DELETED_DDI_FILES . $df->get_file_name()
						);
					}
				}

				{
					$questionnaireMapper = new DB_Mapper_Questionnaire;
					$questionnaires = $questionnaireMapper->findForDdifile($id);
					$this->_deleteQuestionnaireFiles($questionnaires);
				}

				$ddiFileMapper->delete($id);
				$solrDelete = new Solr_Delete(
					array(
						'query' => "ddiFileId: $id",
					)
				);
				$solrDelete->send();
				$client->commit();
				$client->optimize();
				$db->commit();
				$this->_redirectToDdifileIndex();
			}

			catch (Exception $e)
			{
				$db->rollBack();
				$client->rollback();
				
				{
					$ddiFileMapper = new DB_Mapper_Ddifile;
					$df = $ddiFileMapper->find($id);
					
					if (file_exists(DELETED_DDI_FILES . $df->get_file_name()))
					{
						rename(
							DELETED_DDI_FILES . $df->get_file_name(),
							DDI_FILES. $df->get_file_name()						
						);
					}
				
				}

				{
					$questionnaireMapper = new DB_Mapper_Questionnaire;
					$questionnaires = $questionnaireMapper->findForDdifile($id);
					
					foreach ($questionnaires as $questionnaire)
					{
						
						if (file_exists(DELETED_QUESTIONNAIRE_FILES . $questionnaire[questionnaire_file_name]))
						{
							rename(
								DELETED_QUESTIONNAIRE_FILES . $questionnaire[questionnaire_file_name],
								QUESTIONNAIRE_FILES . $questionnaire[questionnaire_file_name]
							);
						}
	
					}
				
				}
				
				echo $this->_translate->_('fr0025000180');
				echo "<br/>";
				echo $this->_translate->_('fr0025000190');
				echo "<br/>";
				echo $this->_translate->_('fr0025000200');
				echo "<br/>";
			}

		}
		
		else
		{
			$this->_redirectToDdifileIndex();
		}

	}
	
	protected function _deleteQuestionnaireFiles($questionnaires)
	{
		foreach ($questionnaires as $questionnaire)
		{
			
			if (file_exists(QUESTIONNAIRE_FILES . $questionnaire['questionnaire_file_name']))
			{
				rename(
					QUESTIONNAIRE_FILES . $questionnaire['questionnaire_file_name'],
					DELETED_QUESTIONNAIRE_FILES . $questionnaire['questionnaire_file_name']
				);
			}

		}
	}
	
	protected function _getConcepts()
	{
		$mapper = new DB_Mapper_Concept;
		return $mapper->findAllForConceptList($this->_data['concept_list_id'], $this->_data['lang_id']);
	}
	
	/**
	 * 
	 * @param string $conceptStr1
	 * @param string $conceptStr2
	 * @return boolean
	 */
	protected function _sameConcept($conceptStr1, $conceptStr2)
	{
		$conceptStr2 = htmlspecialchars_decode($conceptStr2);
		$conceptStr1 = strtolower($conceptStr1); $conceptStr2 = strtolower($conceptStr2);
		$conceptStr1 = normalize($conceptStr1); $conceptStr2 = normalize($conceptStr2);

		{
			$search = array(
				'/(\W|\d)/',
				'/\s/'
			);
			$replace = array(
				' ',
				''
			);
			$conceptStr1 = preg_replace($search, $replace, $conceptStr1);
			$conceptStr2 = preg_replace($search, $replace, $conceptStr2);

		}

		if (levenshtein($conceptStr1, $conceptStr2) > 3)
		{
			return false;
		}

		return true;

	}

	protected function _getReferenceConcept($conceptStr)
	{
		
		if ( ! isset($this->_concepts))
		{
			$this->_concepts = $this->_getConcepts();
		}
		
		foreach ($this->_concepts as $concept)
		{
			
			if ($this->_sameConcept($concept['concept_title'], $conceptStr))
			{
				return $concept;
			}
			
		}

	}
	
	protected function _redirectToDdifileIndex()
    {
    	$this->_helper->getHelper('Redirector')->setGotoRoute(	
			array(
				'module' => 'admin',
				'controller' => 'ddifile',
				'action' => 'index'
			),
			'ddifileIndex'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
    }
	
	protected function _redirectToDdifileAnalysis()
    {
    	$this->_helper->getHelper('Redirector')->setGotoRoute(	
			array(
				'module' => 'admin',
				'controller' => 'ddifile',
				'action' => 'analysis'
			),
			'ddiFileAnalysis'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
    }
    
    protected function _redirectToDdifileMultipleitemlist()
    {
    	$this->_helper->getHelper('Redirector')->setGotoRoute(	
			array(
				'module' => 'admin',
				'controller' => 'ddifile',
				'action' => 'multipleitemlist'
			),
			'ddifileMultipleitemlist'
		);
		$this->_helper->getHelper('Redirector')->redirectAndExit();
    }
    
}
