<?php

if (PHP_SAPI == 'cli')
	{
	// Command-line interface
	\ini_set('log_errors', '0');

	if (2 !== $argc)
		{
		exit("Usage: php convertPHPfont.php path_to_PHP_files\n");
		}

	foreach (\glob($argv[1]) as $file)
		{
		echo "{$file}\n";
		\makeJsonFile($file);
		}

	}

function makeJsonFile(string $includeFile) : void
	{
	include $includeFile;
	$newCW = [];

	foreach ($cw as $value)
		{
		$newCW[] = $value;
		}
	$cw = $newCW;
	unset($value, $newCW);
	$array = \get_defined_vars();
	unset($array['includeFile']);
	$json = \json_encode($array, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR);
	$jsonFileName = \str_replace('.php', '.json', $includeFile);
	\file_put_contents($jsonFileName, $json);
	}
