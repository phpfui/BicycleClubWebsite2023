<?php

if (PHP_SAPI == 'cli')
	{
	// Command-line interface
	\ini_set('log_errors', '0');

	if (2 !== $argc)
		{
		exit("Usage: php convertPHPfont.php path_to_PHP_files\n");
		}

	foreach (glob($argv[1]) as $file)
		{
		echo "$file\n";
		makeJsonFile($file);
		}

	}

function makeJsonFile(string $includeFile)
	{
	include $includeFile;
	$newCW = [];
	foreach ($cw as $index => $value)
		{
		if ($index >= chr(126))
			{
			$index = mb_chr(ord($index), 'UTF-8');
			}
		$newCW[$index] = $value;
		}
	$cw = $newCW;
	unset($newCW);
	$array = get_defined_vars();
	unset($array['includeFile']);
	$json = \json_encode($array, JSON_PRETTY_PRINT);
	$jsonFileName = str_replace('.php', '.json', $includeFile);
	file_put_contents($jsonFileName, $json);
	}

