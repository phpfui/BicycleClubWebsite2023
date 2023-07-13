<?php

namespace App\UI;

class CassettePicker extends \PHPFUI\Input\Select
	{
	public function __construct(string $value = '')
		{
		parent::__construct('cassette', 'Cassette');
		parent::setToolTip('Select your cassette');
		$csvReader = new \App\Tools\CSVReader(PROJECT_ROOT . '/files/gearCalculator/cassettes.csv');

		$found = false;

		foreach ($csvReader as $row)
			{
			$name = "{$row['speeds']} speed, {$row['manufacturer']}, {$row['cogs']} {$row['name']}";
			$selected = $row['cogs'] == $value;
			$this->addOption($name, $row['cogs'], $selected && ! $found);
			$found |= $selected;
			}

		if (! $found)
			{
			$this->addOption('Custom - ' . $value, $value, true);
			}
		}
	}
