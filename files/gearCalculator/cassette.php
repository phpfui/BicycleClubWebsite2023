<?php

include 'commonbase.php';

$csvReader = new \App\Tools\CSVReader('cassettes.csv');

$allCassettes = [];
foreach ($csvReader as $row)
	{
	$cogs = $row['cogs'];
	$speeds = count(explode('-', $cogs));
	$parts = explode(' ', trim($row['name']));
	$manufacturer = array_shift($parts);
	array_shift($parts);
	$speedName = "{$speeds}-speed";
	$name = implode(' ', $parts);
	$name = trim(str_replace($speedName, '', $name));
	$allCassettes[] = ['speeds' => $speeds, 'manufacturer' => $manufacturer, 'cogs' => $cogs, 'name' => $name];
	}

$keys = ['speeds' => -1, 'manufacturer' => 1, 'cogs' => 1, 'name' => 1];
function cassetteCompare(array $lhs, array $rhs)
	{
	global $keys;
	foreach ($keys as $key => $sort)
		{
		$result = $lhs[$key] <=> $rhs[$key];
		if ($result !== 0)
			{
			return $result * $sort;
			}
    }
	return $result;
	}

usort($allCassettes, 'cassetteCompare');

$csvWriter = new \App\Tools\CSVWriter('normalizedCassettes.csv', download:false);
$csvWriter->addHeaderRow();
foreach ($allCassettes as $row)
	{
	$csvWriter->outputRow($row);
	}

