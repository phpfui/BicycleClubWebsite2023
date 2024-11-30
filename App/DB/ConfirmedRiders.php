<?php

namespace App\DB;

/**
 * @property \App\Record\Ride $currentRecord
 */
class ConfirmedRiders extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<mixed> $parameters
	 */
	public function getValue(array $parameters) : \PHPFUI\ORM\RecordCursor
		{
		$table = new \App\Table\RideSignup();
		$condition = new \PHPFUI\ORM\Condition('rideId', $this->currentRecord->rideId);
		$condition->and('attended', \App\Enum\RideSignup\Attended::CONFIRMED);
		$table->setWhere($condition);

		return $table->getRecordCursor();
		}
	}
