<?php

namespace App\UI;

class TirePicker extends \PHPFUI\Input\Select
	{
	public function __construct(string $name, string $label = '', string $value = '')
		{
		parent::__construct($name, $label);
		parent::setToolTip('Select your tire size');
		$csvReader = new \App\Tools\CSV\FileReader(PROJECT_ROOT . '/files/gearCalculator/tires.csv');
		$indexes = ['Metric', 'American', 'Imperial'];

		foreach ($csvReader as $row)
			{
			$name = $row['ISO'];
			$separator = ' ';

			foreach ($indexes as $index)
				{
				if ($row[$index])
					{
					$name .= $separator . $row[$index];
					$separator = ' \ ';
					}
				}

			$key = $row['Diameter'] . '~' . $row['ISO'];
			$this->addOption($name, $key, $value == $key);
			}
		}
	}
