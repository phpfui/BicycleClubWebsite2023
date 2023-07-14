<?php

namespace App\UI;

class WheelSize extends \PHPFUI\Input\Select
	{
	public function __construct(string $value)
		{
		parent::__construct('wd', 'Wheel Size');
		parent::setToolTip('Select your tire size');
		$csvReader = new \App\Tools\CSVReader(PROJECT_ROOT . '/files/gearCalculator/tires.csv');
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
