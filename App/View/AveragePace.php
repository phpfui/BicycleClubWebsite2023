<?php

namespace App\View;

class AveragePace extends \PHPFUI\Base
	{
	/** @var array<int,array<string,int>> */
	private array $paces = [];

	public function addRide(\App\Record\Ride $ride) : static
		{
		$averagePace = $ride->averagePace ?? 0.0;

		if ($averagePace > 25.0 || $averagePace < 5.0)
			{
			return $this;
			}
		$pace = $ride->paceId;

		if (! isset($this->paces[$pace]))
			{
			$this->paces[$pace] = ['speed' => 0, 'rides' => 0];
			}
		++$this->paces[$pace]['rides'];
		$this->paces[$pace]['speed'] += $averagePace;

		return $this;
		}

	protected function getBody() : string
		{
		$totalRides = 0;
		$totalSpeed = 0;

		foreach ($this->paces as $key => $values)
			{
			$totalRides += $values['rides'];
			$totalSpeed += $values['speed'];
			}
		\ksort($this->paces);
		$table = new \PHPFUI\Table();
		$table->setHeaders(['Pace', 'Rides', 'Average']);
		$paceTable = new \App\Table\Pace();

		foreach ($this->paces as $key => $values)
			{
			$average = \number_format($values['speed'] / $values['rides'], 1);
			$table->addRow(['Pace' => "<b>{$paceTable->getPace($key)}</b>", 'Rides' => $values['rides'],
				'Average' => $average, ]);
			}

		if ($totalRides < 1)
			{
			$totalRides = 1;
			$totalSpeed = 0;
			}
		$link = new \PHPFUI\HTML5Element('a');
		$link->addAttribute('href', '#');
		$link->add(\number_format($totalSpeed / $totalRides, 1));

		return new \PHPFUI\DropDown($link, $table);
		}

	protected function getEnd() : string
		{
		return '';
		}

	protected function getStart() : string
		{
		return '';
		}
	}
