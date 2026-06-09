<?php

include 'commonbase.php';

function fix(string $name) : string
	{
//	$name = \str_replace([\chr(215), \chr(188), \chr(189), \chr(190)], [' x ', '.25', '.5', '.75'], $name);
//	$name = \str_replace(['1/8', '1/4', '3/8', '1/2', '5/8', '3/4', '7/8'], ['.125', '.25', '.375', '.5', '.625', '.75', '.875'], $name);
	$name = \str_replace([\chr(215), \chr(188), \chr(189), \chr(190)], [' x ', ' 1/4', ' 1/2', ' 3/4'], $name);
	$name = \str_replace(['1/', '3/', '5/', '7/'], [' 1/', ' 3/', ' 5/', ' 7/'], $name);

	$name = \str_replace([' .', '  ', '   '], ['.', ' ', ' '], $name);

	return $name;
	}

$csvReader = new \App\Tools\CSVReader('tires.csv', delimiter:"\t");

$all = [];
foreach ($csvReader as $row)
	{
	unset($row['diameter(in)'], $row['Circ(m)']);

	foreach ($row as &$field)
		{
		$field = trim($field);
		}
	$row['Imperial'] = fix($row['Imperial']);
	$row['American'] = fix($row['American']);
	[$width, $rim] = explode('-', $row['ISO']);
	$row['Width'] = (int)$width;
	$row['Rim'] = (int)$rim;
	$all[] = $row;
	}

$keys = ['Rim', 'Width', 'ISO', 'Diameter', 'Metric', 'Imperial', 'American',];

function compare(array $lhs, array $rhs)
	{
	global $keys;
	foreach ($keys as $key)
		{
		$result = $lhs[$key] <=> $rhs[$key];
		if ($result !== 0)
			{
			return $result;
			}
    }
	return $result;
	}

usort($all, 'compare');

$csvWriter = new \App\Tools\CSVWriter('normalizedTires.csv', download:false);
$csvWriter->addHeaderRow();
foreach ($all as $row)
	{
	$csvWriter->outputRow($row);
	}


