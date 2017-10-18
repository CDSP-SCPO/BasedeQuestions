<?php
/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 * @see token_get_all http://fr.php.net/manual/fr/function.token-get-all.php
 * Use the Zend Engine token analyzers to extract translations string
 * Generates identifiers
 * 
 */
require_once 'inc/headers.php';
define('LIBRARY_PATH', realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'library');

function checkDir($dir, $extensions, DOMDocument $dom, DOMNode $body)
{
	$d = dir($dir);
	$ptr = $d->handle;
	$path = $d->path;

	while (false !== ($entry = $d->read()))
	{
		
		if ($entry == '.' || $entry == '..')
		{
			continue;
		}
		
		$entry = $path . DIRECTORY_SEPARATOR . $entry;
		
		if (is_dir($entry))
		{
			checkDir($entry, $extensions, $dom, $body);
		}
		
		else
		{
			$x = explode('.', $entry);
			$x = array_pop($x);
			
			if (in_array($x, $extensions))
			{
				parseFile($entry, generateDirId($dir), $dom, $body);
			}

		}
	}

	$d->close();
}

function parseFile($fileName, $dirId, DOMDocument $dom, DOMNode $body)
{
	static $fileId = 0;
	$file = file_get_contents($fileName);
	$tokens = token_get_all($file);
	$l = count($tokens);
	$stringId = 0;
	
	for ($i = 0; $i < $l; $i ++):
		$token = $tokens[$i];
		$tokenName = is_array($token) ? $token[0] : null;
        $tokenData = is_array($token) ? $token[1] : $token;
        
        if ($tokenName == T_OBJECT_OPERATOR):
        
        	for ($j = $i - 1; $j > 0; $j--):
        		$_token = $tokens[$j];
        		$_tokenName = $_token[0];

        		if ($_tokenName != T_WHITESPACE)
        		{
        			break;
        		}

        	endfor;
        
        	$prevToken = $tokens[$j];
        	$prevTokenName = is_array($prevToken) ? $prevToken[0] : null;
        	$prevTokenData = is_array($prevToken) ? $prevToken[1] : $prevToken;
        	
        	if ($prevTokenName == T_VARIABLE || $prevTokenName = T_VAR):
        	
        		if (strstr($prevTokenData, 'translate') !== false):
        		
        			for ($j = $i + 1; $j < $l; $j++): //Skip whitespaces
		        		$_token = $tokens[$j];
		        		$_tokenName = $_token[0];
		
		        		if ($_tokenName != T_WHITESPACE)
		        		{
		        			break;
		        		}
		
		        	endfor;
        		
        			$nextToken = $tokens[$j];
		        	$nextTokenName = is_array($nextToken) ? $nextToken[0] : null;
		        	$nextTokenData = is_array($nextToken) ? $nextToken[1] : $nextToken;
		        	
		        	if($nextTokenData == '_'):

		        		for ($j = $j + 1; $j < $l; $j++): //Skip whitespaces
			        		$_token = $tokens[$j];
			        		$_tokenName = $_token[0];
			
			        		if ($_tokenName != T_WHITESPACE)
			        		{
			        			break;
			        		}
			
		        		endfor;
		        		/* Here is the opening parenthese */
		        		for ($j = $j + 1; $j < $l; $j++):
			        		$_token = $tokens[$j];
			        		$_tokenName = $_token[0];
			
			        		if ($_tokenName != T_WHITESPACE)
			        		{
			        			break;
			        		}
			
		        		endfor;

		        		$nextToken = $tokens[$j];
		        		$translatedString = is_array($nextToken) ? $nextToken[1] : $nextToken;
		        		
		        		if (is_string($translatedString))
		        		{
		        			$comment = $dom->createComment('File : ' . str_replace('/var/www/bdq_dev/', '', $fileName));
							$body->appendChild($comment);
			        		$_translatedString = extractString($translatedString);
			        		$tu = $dom->createElement('tu');
			        		$tuidV = generateTuid($dirId, $fileId, $stringId);
			        		replaceInFile($translatedString, $tuidV, $fileName);
			        		$tuid = $dom->createAttribute('tuid');
			        		$tuid->value = $tuidV;
			        		$tu->appendChild($tuid);
			        		{
				        		$tuvFr = $dom->createElement('tuv');
				        		$attr = $dom->createAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:lang');
				        		$attr->value = 'fr';
				        		$tuvFr->appendChild($attr);
				        		$seg = $dom->createElement('seg', htmlspecialchars($_translatedString));
				        		$tuvFr->appendChild($seg);
				        		$tu->appendChild($tuvFr);
			        		}
			        		{
				        		$tuvEn = $dom->createElement('tuv');
				        		$attr = $dom->createAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:lang');
				        		$attr->value = 'en';
				        		$tuvEn->appendChild($attr);
				        		$seg = $dom->createElement('seg', '');
				        		$tuvEn->appendChild($seg);
				        		$tu->appendChild($tuvEn);
			        		}
			        		$body->appendChild($tu);
			        		$stringId += 10;
		        		}

		        	endif;
        		
        		endif;
        		
        	endif;
        	
        endif;
        
	endfor;
	
	$fileId += 50;

}

function generateDirId($path)
{

	if (strstr($path, APPLICATION_PATH) !== false)
	{
		$id = "fr";
	}
	
	else
	{
		$id = "li";
	}

	return $id;
}

function generateTuid($dirId, $fileId, $stringId)
{
	$_fileId = sprintf('%05d', $fileId);
	$_stringId = sprintf('%05d', $stringId);
	return $dirId . $_fileId . $_stringId;
}

function extractString($translatedString)
{
	$translatedString = substr($translatedString, 1, strlen($translatedString) - 2);
	$translatedString = stripslashes($translatedString);
	$pattern = '/^[0-9]{9} - /';
	$replace = '';
	return preg_replace($pattern, $replace, $translatedString);
}

function replaceInFile($string, $token, $file){
	$content = file_get_contents($file);
	$string = preg_quote($string, '/');
	$string = '/' . $string . '/m';
	$content = preg_replace($string, "'" . $token . "'", $content, 1);
	file_put_contents($file, $content);
}

$checkedDirs = array(
	APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'admin',
);


$extensions = array( //parsed files extensions
	'php',
	'phtml'
);

$dom = new DOMDocument('1.0', 'utf8');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$tmx = $dom->createElement('tmx');
$body = $dom->createElement('body');
$tmx->appendChild($body);
$dom->appendChild($tmx);

foreach ($checkedDirs as $dir):
	checkDir($dir, $extensions, $dom, $body);
endforeach;


$dom->save('locale.xml');
