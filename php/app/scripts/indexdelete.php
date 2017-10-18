#! /usr/bin/php
<?php
/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
require_once 'inc/headers.php';
require_once 'inc/cli.php';
require_once APPLICATION_PATH . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'functions.php';

function help()
{
	echo <<<HEREDOC
*********************************************
* Question data bank administration utility *
*********************************************
Selective deletion of solr index contents.
Doesn't affect the database.

Everything			indexdelete --all	OR indexdelete -a

Interactive mode
	Producer		indexdelete --producer	OR indexdelete -p
	Serie			indexdelete --serie	OR indexdelete -S

HEREDOC;
	exit(0);
}

function delete_all($client)
{
	$accept = array('y', 'Y', 'n', 'N');
echo <<<HEREDOC
Delete everything from solr index?
(Y)	Yes
(N)	No

HEREDOC;
	
	if (($choice = _get_choice($accept)) === "-1" || strtolower($choice) === 'n')
	{
		echo "Bye.\n";
		exit(0);
	}

	$delete = new Solr_Delete(array('query' => '*:*'));
	$delete->send();
	$client->commit();
	$client->optimize();
}

function delete_producer($client, $languages)
{
	if (($lId = _get_language_id($languages)) === "-1")
	{
		echo "Bye.\n";
		exit(0);
	}
	
	$select = new Solr_Select('*:*');
	$select->setFl('id');
	$select->setFacet(true);
	$select->setFacetFields(array('domainId'));
	$select->setFacetSort('lex');
	$response = $select->send();
	$facets = $response->response['facet_counts']['facet_fields']["domainId"];
	$producerIds = array();
	$l = count($facets);
	
	for ($i = 0; $i < $l; $i+=2)
	{
		$producerIds[] = $facets[$i]; 
	}
	
	$mapper = new DB_Mapper_Domain;
	$list = $mapper->findAllTitleAndId($producerIds, $lId);
	$l = count($producerIds);
	echo "\nWhich producer?\n";
	
	for ($i = 0; $i < $l; $i++)
	{
		$producer = binarySearch($producerIds[$i], $list);
		
		if ($producer !== -1)
		{
			echo "($producer[id])\t$producer[title]\n";
		}
		
		else
		{
			echo "(0)\tNot given\n";
		}

	}
	
	if (($choice = _get_choice($producerIds)) === "-1")
	{
		echo "Bye.\n";
		exit(0);
	}
	
	$chosenProducer = binarySearch($choice, $list);
	echo <<<HEREDOC
	
Delete all $chosenProducer[title] studies from solr index?
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
			$delete = new Solr_Delete(array('query' => 'domainId:' . $chosenProducer['id']));
			$delete->send();
			$client->commit();
			$client->optimize();
			echo "\n$chosenProducer[title] studies have been deleted.\n";
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

function delete_serie($client, $languages)
{
	if (($lId = _get_language_id($languages)) === "-1")
	{
		echo "Bye.\n";
		exit(0);
	}
	
	$select = new Solr_Select('*:*');
	$select->setFl('id');
	$select->setFacet(true);
	$select->setFacetFields(array('studySerieId'));
	$select->setFacetSort('lex');
	$response = $select->send();
	$facets = $response->response['facet_counts']['facet_fields']["studySerieId"];
	$serieIds = array();
	$l = count($facets);
	
	for ($i = 0; $i < $l; $i+=2)
	{
		$serieIds[] = $facets[$i]; 
	}
	
	$mapper = new DB_Mapper_StudySerie;
	$list = $mapper->findAllTitleAndId($serieIds, $lId);
	$l = count($serieIds);
	echo "\nWhich serie?\n";
	echo "(0)\tNot given\n";
	
	for ($i = 0; $i < $l; $i++)
	{
		$serie = binarySearch($serieIds[$i], $list);
		echo "($serie[id])\t$serie[title]\n";
	}
	
	if (($choice = _get_choice($serieIds)) === "-1")
	{
		echo "Bye.\n";
		exit(0);
	}
	
	$chosenSerie = binarySearch($choice, $list);
	echo <<<HEREDOC
	
Delete all $chosenSerie[title] studies from solr index ?
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
			$delete = new Solr_Delete(array('query' => "studySerieId:$chosenSerie[id]"));
			$delete->send();
			$client->commit();
			$client->optimize();
			echo "\n$chosenSerie[title] studies have been deleted.\n";
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

Solr_Client::$iniFile = APPLICATION_PATH . '/configs/solr.ini';

try
{
	$opts = new Zend_Console_Getopt(
		array(
			'help|h' => 'Show an help message and exit.',
			'all|a' => 'Delete all index contents',
			'producer|p' => 'Interactive mode - delete index contents by producer',
			'serie|S' => 'Interactive mode - delete index contents by serie',
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

$client = Solr_Client::getInstance();
$mapper = new DB_Mapper_TranslationLanguage;
$languages = $mapper->findAll();

if ($opts->getOption('all'))
{
	delete_all($client);
}

elseif ($opts->getOption('producer'))
{
	delete_producer($client, $languages);
}

elseif ($opts->getOption('serie'))
{
	delete_serie($client, $languages);
}

Solr_Client::$iniFile = APPLICATION_PATH . '/configs/solr.ini';