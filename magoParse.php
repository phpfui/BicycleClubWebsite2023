<?php

$lines = file_get_contents('mago.errors');

$only = $argc > 1 ? $argv[1] : '';

$count = findErrors('error[', $lines, $only);
$count += findErrors('warning[', $lines, $only);
$count += findErrors('help[', $lines, $only);

echo "{$count} issues found\n";

function findErrors(string $search, string $lines, string $only) : int
	{
	$count = 0;
	$searchLength = strlen($search);
	while (($found = strstr($lines, $search)) !== false)
		{
		$eol = strpos($found, "\n");
		$idEnd = strpos($found, ']');
		$id = substr($found, $searchLength, $idEnd - $searchLength);
		$error = substr($found, $idEnd + 1, strpos($found, chr(10)) - $idEnd - 1);
		$lines = substr($found, strpos($found, chr(10)));
		$last = (int)strpos($lines, chr(10));
		$temp = substr($lines, strpos($lines, chr(148).chr(128)) + 3, 100);
		$file = '';
		for ($i = 0; $i < 100; ++$i)
			{
			if (ord($temp[$i]) == 10)
				{
				break;
				}
			$file .= $temp[$i];
			}
		if (empty($only) || $only === $id)
			{
			echo __DIR__ . '\\' . $file . ':' . $id .  $error . "\n";
			$count += 1;
			}

		$lines = strstr($lines, "\n");
		}

	return $count;
	}
