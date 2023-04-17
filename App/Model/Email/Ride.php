<?php

namespace App\Model\Email;

class Ride extends \App\Model\EmailData
	{
	public function __construct(\App\Record\Ride $ride = new \App\Record\Ride())
		{
		if ($ride->empty())
			{
			$rideTable = new \App\Table\Ride();
			$rideTable->addOrderBy('RideId', 'desc');
			$rideTable->setLimit(1);
			$ride = $rideTable->getRecordCursor()->current();
			}
		$this->fields = $ride->toArray();
		}
	}
