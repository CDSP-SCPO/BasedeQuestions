#! /usr/bin/php
<?php
error_reporting(E_ALL);
require_once 'inc/headers.php';
require_once 'inc/cli.php';
require_once 'inc/TMXPHPBridge/TMXResourceBundle.php';

$tmxDir = realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR;
$files = glob($tmxDir . '*.xml');

foreach($files as $k => $v)
{
	echo "($k) $v\n";
}

$choices = array_keys($files);
array_walk($choices, function(&$val, $key){
	$val = (string) $val;
});

if(($choice = _get_choice($choices)) == -1)
{
	echo "Bye";
	exit(0);
}

echo "Enter TMX locale language code:\n";
$lang = '';

while (($c = fgetc(STDIN)) != "\n")
{
	$lang .= $c;
}

$tmx = new TMXResourceBundle($files[$choice], $lang);
$file = $files[$choice];
$file = "$file.$lang.php";
$array = var_export($tmx->getResource(), true);
$content = <<<HEREDOC
<?php
\$translate = $array;
HEREDOC;
file_put_contents($file, $content);
echo "Conversion done. See: $file\n";