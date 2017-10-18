#! /usr/bin/php
<?php
/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 * @abstract This script checks the synchronization between the question data bank and the nesstar servers
 * It should be set as a cron job.
 */

require_once 'inc/headers.php';
require_once APPLICATION_PATH . '/inc/functions.php';

define('SYNC_DIR', realpath(APPLICATION_PATH . '/data/sync'));

if ( ! is_writeable(SYNC_DIR))
{
	file_put_contents('php://stderr', "Question Data Bank nesstar synchronization script error.\n\"" . SYNC_DIR . '" is not writeable.');
	die;
}

define('DDI_DIR', realpath(APPLICATION_PATH . '/data/ddi'));

$mapper = new DB_Mapper_Ddifile;
$list = $mapper->findAllWithDetailsForSynchronization();
$unsynchronizedFiles = array();

foreach ($list as $study):

	if (empty($study['ns_ip_and_port']) || empty($study['ns_responsible']))
	{
		continue;
	}

	$url = "http://$study[ns_ip_and_port]/webview/velocity/xml.zip?gzip=false&format=xml&mode=transform&study=" . rawurlencode("http://$study[ns_ip_and_port]/obj/fStudy/$study[study_nesstar_id]") . "&gs=";
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); 
	curl_setopt($curl, CURLOPT_BINARYTRANSFER,true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, True);
	$zip = curl_exec($curl);
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	if (substr($httpCode, 0, 1) != 2) // Server down
	{
		continue;
	}

	file_put_contents($file = SYNC_DIR . '/zip.zip', $zip);
	$zip = zip_open($file);
	$zip_entry = zip_read($zip);
	zip_entry_open($zip, $zip_entry, "r");
	file_put_contents($nsDdiFile = SYNC_DIR . '/xml.xml', zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
	zip_close($zip);
	$bdqDdiFile = DDI_DIR . '/' . $study['ddi_file_name'];
	$nsParser = new DDI_Parser122($nsDdiFile);
	$bdqParser = new DDI_Parser122($bdqDdiFile);
	$bdqVariables = $bdqParser->getVariables();
	$nsVariables = $nsParser->getVariables();
	$l = count($bdqVariables);
	$unsynchronizedVariables = array();
	$missingVariables = array();

	for ($i = 0; $i < $l; $i++)
	{
		$bdqVariable = $bdqVariables[$i];
		$l2 = count($nsVariables);
		$found = false;
		reset($nsVariables);

		while(list($j, $nsVariable) = each($nsVariables))
		{

			if ($nsVariable["nesstar_id"] === $bdqVariable["nesstar_id"] && $nsVariable["name"] !== $bdqVariable["name"])
			{
				$unsynchronizedVariables[] = 'ID : ' . utf8_encode($nsVariable['nesstar_id']) . ' - Nom de variable Nesstar : ' . utf8_encode($nsVariable['name']) . ' - Nom de variable BDQ : ' . utf8_encode($bdqVariable['name']);
				
				array(
					'id' => $nsVariable["nesstar_id"],
					'bdq_name' => $bdqVariable["name"],
					'ns_name' => $nsVariable["name"],
				);
				$found = true;
				unset($nsVariables[$j]);
				break;
			}

			elseif ($nsVariable["nesstar_id"] === $bdqVariable["nesstar_id"] && $nsVariable["name"] === $bdqVariable["name"])
			{
				$found = true;
				unset($nsVariables[$j]);
				break;
			}

			elseif($nsVariable["nesstar_id"] == NULL)
			{
				die;
			}

		}

		if ( ! $found)
		{
			$missingVariables[] = 'ID : ' . utf8_encode($bdqVariable['nesstar_id']) . ' - Nom de variable BDQ : ' . utf8_encode($bdqVariable['name']);
		}

	}

	if ( ! empty($unsynchronizedVariables) || ! empty($missingVariables))
	{
		$infos = array(
			'study_title' => utf8_encode($study['study_title']),
			'nesstar_server' => array(
					'title' => utf8_encode($study['ns_title']),
					'responsible' => utf8_encode($study['ns_responsible'])
			)
		);

		if ( ! empty($unsynchronizedVariables))
		{
			$infos['unsynchronized_variables'] = $unsynchronizedVariables;
		}

		if ( ! empty($missingVariables))
		{
			$infos['missing_variables'] = $missingVariables;
		}

		$unsynchronizedFiles[] = $infos;

	}

endforeach;

$filesByResponsible = array();

foreach($unsynchronizedFiles as $file):
	
	$filesByResponsible[$file['nesstar_server']['responsible']][] = $file;

endforeach;


$mailSubject = "Perte de synchronisation entre la base de questions et le serveur Nesstar";

foreach ($filesByResponsible as $recipient => &$files):
	$mailBody = '';
	
	foreach ($files as &$file):
		$missingVariables = isset($file['missing_variables']) ? implode("\n", $file['missing_variables']) : '';
		$unsynchronizedVariables = isset($file['unsynchronized_variables']) ? implode("\n", $file['unsynchronized_variables']) : '';
		$mailBody .= $file['study_title'];
		$mailBody .= "\n\n";

		if ($missingVariables)
		{
			$mailBody .= utf8_decode("Variable(s) manquante(s) :\n");
			$mailBody .= $missingVariables;
			$mailBody .= utf8_decode("\n\n");
		}

		if ($unsynchronizedVariables)
		{
			$mailBody .= utf8_decode("Variable(s) désynchronisée(s) :\n");
			$mailBody .= $unsynchronizedVariables;
		}

		$mailBody .= utf8_decode("\n\n\n\n");

	endforeach;

	$mail = new Zend_Mail();
	$mail->setBodyText(utf8_decode($mailBody));
	$mail->setFrom(utf8_decode('info@cdsp.sciences-po.fr'), utf8_decode('Bot BDQ'));
	$mail->addTo(utf8_decode($recipient), 'Responsable synchronisation BDQ - Nesstar');
	$mail->setSubject(utf8_decode($mailSubject));
	$mail->send();

endforeach;