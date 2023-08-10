<?php

namespace App\UI;

class HubPicker extends \PHPFUI\Input\Select
	{
	public function __construct(string $name, string $label = '', string $value = '')
		{
		parent::__construct($name, $label);
		parent::setToolTip('Select your hub');
		$csvReader = new \App\Tools\CSVReader(PROJECT_ROOT . '/files/gearCalculator/hubs.csv');

		$found = false;

		foreach ($csvReader as $row)
			{
			if ($row['type'] == $name)
				{
				$selected = $row['ratios'] == $value;
				$this->addOption("{$row['name']}, {$row['ratios']}", $row['ratios'], $selected && ! $found);
				$found |= $selected;
				}
			}

		if (! $found)
			{
			$this->addOption('Custom - ' . $value, $value, true);
			}
		}
	}
