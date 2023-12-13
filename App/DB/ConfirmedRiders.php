<?php

namespace App\DB;

class ConfirmedRiders extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<mixed> $parameters
	 */
	public function getValue(array $parameters) : \PHPFUI\ORM\RecordCursor
		{
		$table = new \App\Table\RideSignup();
		// @phpstan-ignore-next-line
		$condition = new \PHPFUI\ORM\Condition('rideId', $this->currentRecord->rideId);
		$condition->and('attended', \App\Table\RideSignup::CONFIRMED);
		$table->setWhere($condition);

		return $table->getRecordCursor();
		}
	}
