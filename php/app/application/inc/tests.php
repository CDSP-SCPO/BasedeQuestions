<?php

if ( ! extension_loaded('curl'))
{
	trigger_error('curl extension missing', E_USER_ERROR);
}

if ( ! extension_loaded('mysql'))
{
	trigger_error('mysql extension missing', E_USER_ERROR);
}

if ( ! extension_loaded('pdo'))
{
	trigger_error('pdo extension missing', E_USER_ERROR);
}

$db = $application->getBootstrap()->getResource('db');

try
{
	$c = $db->getConnection();
}

catch (Exception $e)
{
	echo $e;
	trigger_error('database connection error', E_USER_ERROR);
}

$client = Solr_Client::getInstance();

try
{
	$client->ping();	
}

catch (Exception $e)
{
	echo $e;
	trigger_error('solr connection error', E_USER_ERROR);
}

if ( ! is_writable(Solr_Client::$logFile))
{
	trigger_error(Solr_Client::$logFile . ' is not writable', E_USER_ERROR);	
}

if ( ! is_writable(EXCEPTIONS_LOG))
{
	trigger_error(EXCEPTIONS_LOG . ' is not writable', E_USER_ERROR);
}
