<?php
function _get_language_id($languages)
{
	echo "Which language for labels?\n";
	$l = count($languages);

	for ($i = 0; $i < $l; $i++)
	{
		$code = $languages[$i]->get_code();
		$accept[] = (string) $i;
		echo "($i)\t$code\n";
	}

	if (($choice = _get_choice($accept)) === "-1")
	{
		echo "Bye.\n";
		exit(0);
	}

	return $languages[$choice]->get_id();
}

function _get_choice(array $accept)
{
	$accept[] = 'q';
	$accept[] = 'Q';
	echo "(Q)\tQuit\n";
	$choice = '';

	while ( ! in_array($choice, $accept, true))
	{
		
		echo "Type your choice then press enter.\n";
		$c = '';
		$choice = '';
		
		while (($c = fgetc(STDIN)) != "\n")
		{
			$choice .= $c;
		}
		
	}
	
	if (strtolower($choice) == 'q')
	{
		return "-1";
	}
	
	return $choice;
}