<?php

namespace App\DB;

/**
 * @property \App\Record\Ride $currentRecord
 */
class RideWaitList extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<mixed> $parameters
	 */
	public function getValue(array $parameters) : \PHPFUI\ORM\RecordCursor
		{
		$table = new \App\Table\RideSignup();
		$condition = new \PHPFUI\ORM\Condition('rideId', $this->currentRecord->rideId);
		$condition->and('status', \App\Enum\RideSignup\Status::WAIT_LIST);
		$table->setWhere($condition);

		return $table->getRecordCursor();
		}
	}
