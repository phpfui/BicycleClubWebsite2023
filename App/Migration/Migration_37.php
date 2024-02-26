<?php

namespace App\Migration;

class Migration_37 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Normalize ride.startTime to SQL format';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$rideTable = new \App\Table\Ride();
		$rideTable->setDistinct();
		$rideTable->setSelectFields('startTime');

		foreach ($rideTable->getDataObjectCursor() as $ride)
			{
			$startTime = \App\Tools\TimeHelper::toMilitary(\App\Tools\TimeHelper::fromString($ride->startTime));
			$rideTable->setWhere(new \PHPFUI\ORM\Condition('startTime', $ride->startTime));
			$rideTable->update(['startTime' => $startTime]);
			}

		return $this->alterColumn('ride', 'startTime', 'time');
		}
	}
