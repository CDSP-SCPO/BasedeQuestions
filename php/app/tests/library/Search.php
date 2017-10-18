<?php
/**
 * Integration tests.
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/
 */
require_once '../headers.php';

class SearchIntegrationTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var string
	 */
	public $query;

	/**
	 * @var string
	 */
	public $lang = 'FR';

	/**
	 * @var array
	 */
	public $langs = array(
		'EN',
		'FR',
	);
	
	/**
	 * @var int
	 */
	public $target;

	/**
	 * @var array
	 */
	public $commonTerms = array(
		'FR' => array(
			'emploi',
			'dire',
			'pouvez',
		),
		'EN' => array(
			'please',
			'each',
			'have',
			'say'
		)
	);

	/**
	 * @var array
	 */
	public $lessCommonTerms = array(
		'FR' => array(
			'chirac NOT sarkozy',
			'chirac diriez vous',
			'jospin chirac bayrou',
			'rchom',
			'qualificatifs suivants',
			'djqsldkjskldjqskldj',
			'"chirac honnete"~10',
			'voici maintenant une liste de phrases',
			'homme femme',
			'date de naissance',
			'"chirac honnete"~10',
			'croissance économique',
			'(chirac OR jospin) NOT sarkozy',
			'développement',
			'énergie nucléaire',
			'immigration',
			'"voici maintenant une liste de phrases"',
			'emploi OR diplome',
			'parti OR mouvement',
		),
		'EN' => array(
			'statement',
			'political',
			'parliament',
			'institution',
			'personnaly',
			'human'
		)
	);

	/**
	 * @return Solr_BDQ_Search_SearchField
	 */
	public function getSearchField()
	{
		return new Solr_BDQ_Search_SearchField(
			$this->query,
			$this->target,
			$this->lang
		);
	}

	/**
	 * @return string
	 */
	public function getSearchFieldQuery()
	{
		$field = $this->getSearchField();
		return $field->getQuery();
	}

	/**
	 * @return Solr_Select
	 */
	public function getSearchFieldSelect()
	{
		$query = $this->getSearchFieldQuery();
		$select = new Solr_Select($query);
		$select->setFl(array('id'));
		$select->setRows(1000);
		return $select;
	}

	/**
	 * @return Solr_Select
	 */
	public function getReferenceSelect()
	{
		$target = Solr_BDQ_Search_SearchField::getTarget($this->target);
		$analysisCode = BDQ_Settings_Client::getAnalysisCode();
		$selectQuery = $this->getField() . ":($this->query)";
		$select = new Solr_Select(utf8_decode($selectQuery));
		$select->setFl(array('id'));
		$select->setRows(1000);
		return $select;
	}

	public function getField()
	{
		$target = Solr_BDQ_Search_SearchField::getTarget($this->target);
		$analysisCode = BDQ_Settings_Client::getAnalysisCode();
		return $target . $analysisCode . $this->lang;
	}

	/**
	 * @return int
	 */
	public function getRandomTarget()
	{
		$target = 0;
		$target |= round(mt_rand(0, 1)) ? SEARCH_QUESTION : 0;
		$target |= round(mt_rand(0, 1)) ? SEARCH_MODALITIES : 0;
		$target |= round(mt_rand(0, 1)) ? SEARCH_VARIABLE : 0;

		if ( ! $target)
		{
			$target = SEARCH_QUESTION;
		}
		
		return $target;
	}

	/*
	 * 
	 */
	public function testSelectAgainstXPath()
	{
		echo "Testing Solr_Select against XPath\n";
		$ddiFileMapper = new DB_Mapper_Ddifile;
		$query = <<<HEREDOC

		SELECT
			study_descriptions.id AS study_description_id,
			file_name,
			translation_languages.code_solr AS search_lang 
		
		FROM
			ddi_files
			
		LEFT JOIN
			translation_languages
			ON
			ddi_files.translation_language_id = translation_languages.id
			
		LEFT JOIN
			study_descriptions
			ON
			ddi_files.id = study_descriptions.ddi_file_id			
HEREDOC;

		$results = $ddiFileMapper->getDbTable()->getAdapter()->query($query)->fetchAll();
		$l = count($results);
		
		for ($i = 0; $i < $l; $i++):
			$studyDescriptionId = $results[$i]['study_description_id'];
			$fileName = $results[$i]['file_name'];
			$searchLang = $results[$i]['search_lang'];
			$domDocument = new DOMDocument('1.0', 'utf8');
			$file = DDI_FILES . DIRECTORY_SEPARATOR . $fileName;
			$domDocument->load($file);
			$domXPath = new DOMXPath($domDocument);
			$terms = $this->commonTerms[$searchLang];

			foreach ($terms as $term)
			{
				$XPathQuery = "//qstnLit[contains(., '$term')]";
				$result = $domXPath->query($XPathQuery);

				if (($reference = $result->length) > 0)
				{
					$query = "qi$searchLang:($term) AND studyDescriptionId:$studyDescriptionId";
					$select = new Solr_Select($query);
					$response = $select->send();
					$nbResults = $l = count($response);

					for ($i = 0; $i < $l; $i++)
					{
						$doc = $response[$i];
						$method = "get_q$searchLang";
						$question = $doc->$meth();

						if ($doc->get_hasMultipleItems())
						{

							if (strpos($question, $term) !== false)
							{
								$nbResults += $l2 - 1;
								continue;
							}

							$method = "get_i$searchLang";
							$items = $doc->$method();
							$l2 = count($items);
							
							for ($j = 0; $j < $l2; $j++)
							{
								$item = $items[$j];
							}
							
						}

					}

				}

			}

		endfor;

	}
	
	/*
	 * Ensures that a query crafted by the Solr_BDQ_Search_SearchField returns the same results as a simple one.
	 */
	public function testSearchField()
	{
		echo <<<HEREDOC

		
**********************
*TESTING SEARCH FIELD*
**********************

HEREDOC;
		$settings = BDQ_Settings_Client::getInstance();
		
		foreach ($this->langs as $lang):
			$this->lang = $lang;
			$l = count($this->lessCommonTerms[$lang]);
		
			for ($i = 0; $i < $l; $i ++)
			{
				$stemming = $settings->stemming = round(mt_rand(0, 1));
				$settings->stopwords = round(mt_rand(0, 1));
	
				{ 
					$query = $this->query = $this->lessCommonTerms[$lang][$i];
					$this->target = $this->getRandomTarget();
				}
				echo "Testing compiled query against standard one to ensure they return the same number of results: \"$query\" in $this->lang.\n\n";
				{
					$fieldQuery = $this->getSearchFieldQuery();
					echo "Crafted query:\n\"$fieldQuery\"\n";
					$select1 = $this->getSearchFieldSelect();
					$response1 = $select1->send();
					$numFound1 = $response1->response['response']['numFound'];
					echo "Sent. Numfound: $numFound1\n\n";
				}
				
				{
					$select2 = $this->getReferenceSelect();
					$selectQuery = $select2->getQ();
					echo "Reference query:\n$selectQuery\n";
					$response2 = $select2->send();
					$numFound2 = $response2->response['response']['numFound'];
					echo "Sent. Numfound: $numFound2\n\n";
				}
				
				$this->assertEquals(
					$numFound1,
					$numFound2
				);
				
				if ( ! $stemming)
				{
					$response1->createDocuments();
					$response2->createDocuments();
					$l2 = count($response1);
					echo "Checking document positions to ensure that relevancy isn't affected:\n";
	
					for ($j = 0; $j < $l2; $j++)
					{
						$doc1 = $response1[0];
						$doc2 = $response2[0];
						echo "$j.";
						$this->assertEquals($doc1->get_id(), $doc2->get_id());
					}
					
					echo "\n\n";
				}
	
			}
		
		endforeach;

	}

	/*
	 * 
	 */
	public function testSimpleSearch()
	{
		echo <<<HEREDOC


*******************************
*TESTING SIMPLE SEARCH QUERIES*
*******************************

HEREDOC;
		$settings = BDQ_Settings_Client::getInstance();
		Solr_BDQ_Search_Search::$fl = array('id');
		$settings->rows = 1000;
		
		foreach ($this->langs as $lang):
		
			$this->lang = $lang;
			$l = count($this->commonTerms[$lang]);
			$l2 = count($this->lessCommonTerms[$lang]);

			for ($i = 0; $i < $l; $i++)
			{
				
				$base = $this->commonTerms[$lang][$i];
				
				for ($j = 0; $j < $l2; $j++)
				{
					$stemming = $settings->stemming = round(mt_rand(0, 1));
					$settings->stopwords = round(mt_rand(0, 1));
					$this->query = $base . ' ' . $this->lessCommonTerms[$lang][$j];
					$this->target = $target1 = $this->getRandomTarget();
					$field1 = $this->getSearchField(); 
					$simpleSearch = new Solr_BDQ_Search_SimpleSearch(
						$field1,
						$this->lang
					);
					$response1 = $simpleSearch->send();
					$numFound1 = $response1->response['response']['numFound'];
					echo "Simple search sent. Numfound: $numFound1\n\n";
					$select = $this->getReferenceSelect();
					$response2 = $select->send();
					$numFound2 = $response2->response['response']['numFound'];
					echo "Select sent. Numfound: $numFound2\n\n";
					$this->assertEquals(
						$numFound1,
						$numFound2
					);
				}
	
			}
			
		endforeach;

	}

}