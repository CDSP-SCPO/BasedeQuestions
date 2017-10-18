#! /usr/bin/php
<?php
/**
 * Rebuild the Solr index from the database
 * 
 * Dumps of the database should be made every week by a cron job.
 * In the case of an huge study addition a dump should be made manually.
 * 
 * When to use this ?
 * - when a stopword was added
 * - when a stemming protected word was added
 * - or when needed ;)
 *  
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */

define('MULTIPLE_VALUE_SEPARATOR','!<(^.^)>!');
error_reporting(E_ALL);

require_once 'inc/headers.php';
require_once 'inc/cli.php';
require_once APPLICATION_PATH . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'functions.php';
set_time_limit(3600000);

function help()
{
	echo <<<HEREDOC
*********************************************
* Question data bank administration utility *
*********************************************
Selective rebuild of solr index contents from the database contents.
Doesn't affect the database.

Everything			indexbuild --all	OR indexbuild -a

Interactive mode
	Producer		indexbuild --producer	OR indexbuild -p
	Serie			indexbuild --serie	OR indexbuild -S

HEREDOC;
	exit(0);
}

function build_producer($client, $languages)
{
	if (($lId = _get_language_id($languages)) === "-1")
	{
		echo "Bye.\n";
		exit(0);
	}
	
	$mapper = new DB_Mapper_Domain;
	$list = $mapper->findAllWithDetails($lId);
	$l = count($list);
	echo "\nWhich producer?\n";
	echo "(0)\tNot given\n";
	
	for ($i = 0; $i < $l; $i++)
	{
		$producer = $list[$i];
		$producerIds[] = $producer['id'];
		echo "($producer[id])\t$producer[title]\n";
	}
	
	if (($choice = _get_choice($producerIds)) === "-1")
	{
		echo "Bye.\n";
		exit(0);
	}
	
	$chosenProducer = binarySearch($choice, $list);
	echo <<<HEREDOC
	
Rebuild all $chosenProducer[title] studies ?
(Y)	Yes
(N)	No

HEREDOC;
	$accept = array('y', 'Y', 'n', 'N');

	if (($choice = _get_choice($accept)) === "-1")
	{
		echo "Bye.\n";
		exit(0);
	}
	
	if (strtolower($choice) == 'y')
	{
		try
		{
			build($client, $chosenProducer['id']);
		}
		
		catch (Exception $e)
		{
			echo "An error occured\n";
			exit(1);
		}
	}
	
	echo "Bye.\n";
	exit(0);
}

function build_serie($client, $languages)
{
	if (($lId = _get_language_id($languages)) === "-1")
	{
		echo "Bye.\n";
		exit(0);
	}
	
	$mapper = new DB_Mapper_StudySerie;
	$list = $mapper->findAllWithDetails($lId);
	$l = count($list);

	echo "\nWhich serie?\n";
	echo "(0)\tNot given\n";

	for ($i = 0; $i < $l; $i++)
	{
		$serie = $list[$i];
		echo "($serie[id])\t$serie[title]\n";
		$serieIds[] = $serie['id'];
	}
	
	if (($choice = _get_choice($serieIds)) === "-1")
	{
		echo "Bye.\n";
		exit(0);
	}
	
	$chosenSerie = binarySearch($choice, $list);
	echo <<<HEREDOC
	
Rebuild all $chosenSerie[title] studies ?
(Y)	Yes
(N)	No

HEREDOC;
	$accept = array('y', 'Y', 'n', 'N');

	if (($choice = _get_choice($accept)) === "-1")
	{
		echo "Bye.\n";
		exit(0);
	}
	
	if (strtolower($choice) == 'y')
	{

		try
		{
			build($client, null, $chosenSerie['id']);		
		}
		
		catch (Exception $e)
		{
			echo "An error occured\n";
			exit(1);
		}
	}
	
	echo "Bye.\n";
	exit(0);
}

function build($solrClient, $producerId = null, $serieId = null)
{
	$ddifileMapper = new DB_Mapper_Ddifile;
	$variableMapper = new DB_Mapper_Variable;
	$categoryMapper = new DB_Mapper_Category;
	$files = $ddifileMapper->findAllWithDetailsForSolrIndexBuild($producerId, $serieId);
	$l = count($files);
	
	for ($i = 0; $i < $l; $i ++):
		$file = $files[$i]; // A file = A study
		echo "Traitement de : $file[study_title] \n";
		$studyQuestionCount = $ddifileMapper->getVariableWithQuestionCount($file['study_ddi_file_id']);
		$documents = array(); // This will hold the created solr documents
		$variables = $variableMapper->findForDdifile($file['study_ddi_file_id']);
		$solrLangCode = $file['study_solr_language_code'];
		$l2 = count($variables);
		$questionPosition = 1;
	
		for ($j = 0; $j < $l2; $j++):
			$variable = $variables[$j];
	
			if (isset($variable['variable_group_id'])): // Multiple item question (in French : batterie de questions)
				$groupId = $variable['variable_group_id'];
				$items = array($variable['question_item']);
				$variableLabels = array($variable['variable_label']);
				$variableNesstarIds = array($variable['variable_nesstar_id']);
				$variableNames = array($variable['variable_name']);
				$variableIds = array($variable['variable_id']);
				$variableConceptsIds = array(isset($variable['concept_id']) ? $variable['concept_id'] : SOLR_NULL);
				
				if (isset($variable['concept_id']))
				{
					$conceptPositionAndId = $variable['concept_position'] . SOLR_VALUE_SEPARATOR . $variable['concept_id'];
				}
	
				else
				{
					$conceptPositionAndId = SOLR_NULL . '_' . SOLR_NULL;
				}
				
				$variableConceptPositionAndIds = array($conceptPositionAndId);
				$questionIds = array($variable['question_id']);
				
				while ($groupId == $variables[++$j]['variable_group_id'])
				{
					$_variable = $variables[$j];
					$items[] = $_variable['question_item'];
					$variableLabels[] = $_variable['variable_label'];
					$variableNesstarIds[] = $_variable['variable_nesstar_id'];
					$variableNames[] = $_variable['variable_name'];
					$variableIds[] = $_variable['variable_id'];
	
					if (isset($_variable['concept_id']))
					{
						$conceptPositionAndId = $_variable['concept_position'] . SOLR_VALUE_SEPARATOR . $_variable['concept_id'];
						$variableConceptPositionAndIds[] = $conceptPositionAndId;
						$variableConceptsIds[] = $_variable['concept_id'];
					}
	
					else
					{
						$conceptPositionAndId = SOLR_NULL . '_' . SOLR_NULL;
					}
					
					$questionIds[] = $_variable['question_id'];
				}
	
				$j--;
	
				$variable['question_items'] = $items;
				$variable['variable_label'] = $variableLabels;
				$variable['variable_nesstar_id'] = $variableNesstarIds;
				$variable['variable_name'] = $variableNames;
				$variable['variable_id'] = $variableIds;
				$variable['concept_id'] = $variableConceptsIds;
				$variable['concept_position_and_id'] = $variableConceptPositionAndIds;
				$variable['question_id'] = $questionIds;
			endif;
	
			$solrDocument = new Solr_Document;
			$solrDocument->set_id($variable['variable_id']);
			$solrDocument->set_langId($file['study_language_id']);
			$solrDocument->set_solrLangCode($file['study_solr_language_code']);
	
			{
				$domainIds = $file['study_domain_ids'];
				$domainIds = explode(MULTIPLE_VALUE_SEPARATOR, $domainIds);
				$solrDocument->set_domainId(
					$domainIds != array('')
					?
					$domainIds
					:
					SOLR_NULL
				);
			}
	
			$solrDocument->set_studySerieId(
				(isset($file['study_serie_id']) && ! empty($file['study_serie_id']))
				?
				$file['study_serie_id']
				:
				SOLR_NULL
			);
			$solrDocument->set_ddiFileId($file['study_ddi_file_id']);
			$solrDocument->set_conceptListId($file['study_concept_list_id']);
			$solrDocument->set_studyDescriptionId($file['study_description_id']);
			
			$solrDocument->set_questionId($variable['question_id']);
			$solrDocument->set_variableId($variable['variable_id']);
			$solrDocument->set_variableNesstarId($variable['variable_nesstar_id']);
			$solrDocument->set_nesstarServerId($file['study_nesstar_server_id'] ? $file['study_nesstar_server_id'] : SOLR_NULL);
			$solrDocument->set_studyNesstarId($file['study_nesstar_id']);
			$solrDocument->set_conceptId(isset($variable['concept_id']) ? $variable['concept_id'] : SOLR_NULL);
			
			$solrDocument->set_studyQuestionCount($studyQuestionCount);
			$meth = "set_q$solrLangCode";
			$solrDocument->$meth($variable['question_litteral']);
	
			if (isset($variable['variable_group_id']))
			{
				$solrDocument->set_id($variable['variable_id'][0]);
				$solrDocument->set_hasMultipleItems('true');
				$solrDocument->set_variableGroupId($variable['variable_group_id']);
				$meth ="set_i$solrLangCode";
				$solrDocument->$meth($variable['question_items']);
	
				{ /** This is a trick that reduces the part of the highlighting time done with PHP.
					* It increases the index size.
					*/
					$m = 0;
					$items = array();
					
					foreach($variable['question_items'] as $item)
					{
						$items[] = sprintf('%05d ', $m) . $item;
						$m++;
					}
	
				}
	
				$meth ="set_iHL$solrLangCode";
				$solrDocument->$meth($items);
			}
	
			else
			{
				$solrDocument->set_hasMultipleItems('false');
			}
	
			$solrDocument->set_variableName($variable['variable_name']);
	
			if (is_string($variable['variable_label']))
			{
				$variable['variable_label'] = array($variable['variable_label']);
			}
	
	   		if (is_string($variable['variable_name']))
			{
				$variable['variable_name'] = array($variable['variable_name']);
			}
	
			$vs = array();
			$_vs = array();
			$l3 = count($variable['variable_label']);
	
			for ($m = 0; $m < $l3; $m++)
			{
				$vl = $variable['variable_label'][$m];
				$vn = $variable['variable_name'][$m];
				$v = "$vn - $vl";
				$vs[] = $v;
				$_vs[] = sprintf('%05d ', $m) . $v;
			}
			
			$meth = "set_vl$solrLangCode";
			$solrDocument->$meth($vs);
			$meth = "set_vlHL$solrLangCode";
			$solrDocument->$meth($_vs);
	
			$solrDocument->set_studyTitle($file['study_title']);
			$solrDocument->set_studyYear($file['study_description_year']);
			$decade = $file['study_description_year'];
			$decade[3] = '5';
			$solrDocument->set_studyDecade($decade);
			$solrDocument->set_questionnaireUrl($file['study_questionnaire_url']);
	
			if ( ! $file['study_questionnaire_url'] && ! $file['study_questionnaire_ids'])
			{
				$solrDocument->set_hasQuestionnaire('false');
			}
	
			else
			{
				$solrDocument->set_hasQuestionnaire('true');
			}
			
			{
				$questionnaireId = $file['study_questionnaire_ids'];
				$questionnaireId = explode(MULTIPLE_VALUE_SEPARATOR, $questionnaireId);
				$solrDocument->set_questionnaireId(
					$questionnaireId != array('')
					?
					$questionnaireId
					:
					NULL
				);
			}
			
			
			{
				$questionnaireTitle = $file['study_questionnaire_titles'];
				$questionnaireTitle = explode(MULTIPLE_VALUE_SEPARATOR, $questionnaireTitle);
				$solrDocument->set_questionnaireTitle(
					$questionnaireTitle != array('')
					?
					$questionnaireTitle
					:
					NULL
				);
			}
			
			$solrDocument->set_universe($variable['variable_universe']);
			$solrDocument->set_notes($variable['variable_notes']);
			$solrDocument->set_preQuestionText($variable['question_pre_question_text']);
			$solrDocument->set_postQuestionText($variable['question_post_question_text']);
			$solrDocument->set_interviewerInstructions($variable['question_interviewer_instructions']);
			$solrDocument->set_questionPosition(
				$variable['question_litteral']
				?
				$questionPosition++
				:
				NULL
			);
	
			$categories = $categoryMapper->findForVariable(is_array($variable['variable_id']) ? $variable['variable_id'][0] : $variable['variable_id']);
	
			$modalities = array();
			$_modalities = array();
			$m = 0;
	
			foreach ($categories as $category)
			{
				$modalities[] = $category['value'] . ((isset($category['label']) && ! empty($category['label'])) ? ' - ' : '') . $category['label'];
				$_modalities[] = sprintf('%05d ', $m) . $category['value'] . ((isset($category['label']) && ! empty($category['label'])) ? ' - ' : '') . $category['label'];
				$m++;
			}
	
			$meth = "set_mHL$solrLangCode"; 
			$solrDocument->$meth($_modalities);
			$meth = "set_m$solrLangCode"; 
			$solrDocument->$meth($modalities);
			
			$solrDocuments[] = $solrDocument;
		endfor;
	
		$update = new Solr_Update($solrDocuments);
		$update->send();
		$solrClient->commit();
		
	endfor;
	
	$solrClient->optimize();
}

try
{
	$opts = new Zend_Console_Getopt(
		array(
			'help|h' => 'Show an help message and exit.',
			'all|a' => 'Rebuild whole index',
			'producer|p' => 'Rebuild studies from a producer',
			'serie|S' => 'Rebuild studies from a serie',
		)
	);
}

catch (Exception $e)
{
	echo $e->getUsageMessage();
	exit(1);
}

try
{

	if ( ! $opts->toArray())
	{
		echo $opts->getUsageMessage();
		exit(0);
	}

}

catch (Exception $e)
{
	echo $opts->getUsageMessage();
	exit(1);
}

if ($opts->getOption('help'))
{
	help();
}

Solr_Client::$iniFile = APPLICATION_PATH . '/configs/solr.ini';
$solrClient = Solr_Client::getInstance();

$mapper = new DB_Mapper_TranslationLanguage;
$languages = $mapper->findAll();

if ($opts->getOption('all'))
{
	build($solrClient);
}

elseif ($opts->getOption('producer'))
{
	build_producer($solrClient, $languages);
}

elseif ($opts->getOption('serie'))
{
	build_serie($solrClient, $languages);
}