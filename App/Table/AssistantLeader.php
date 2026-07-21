<?php

namespace App\Table;

class AssistantLeader extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\AssistantLeader::class;

	public function getForDateRange(string $startDate, string $endDate) : \PHPFUI\ORM\ArrayCursor
		{
		$this->addSelect('assistantLeader.*');
		$this->addJoin('ride');
		$condition = new \PHPFUI\ORM\Condition('rideDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('rideDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$this->setWhere($condition);

		return $this->getArrayCursor();
		}

	public function getForMemberDate(int $memberId, string $startDate, string $endDate) : \PHPFUI\ORM\DataObjectCursor
		{
		$rideTable = new \App\Table\Ride();
		$rideTable->addSelect('ride.*');
		$rideTable->addJoin('assistantLeader');
		$condition = new \PHPFUI\ORM\Condition('assistantLeader.memberId', $memberId);
		$condition->and('rideDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('rideDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$rideTable->setWhere($condition);
		$rideTable->setOrderBy('rideDate');

		return $rideTable->getDataObjectCursor();
		}

	public function getForRide(\App\Record\Ride $ride) : \PHPFUI\ORM\RecordCursor
		{
		$memberTable = new \App\Table\Member();
		$memberTable->setJoin('assistantLeader');
		$memberTable->setWhere(new \PHPFUI\ORM\Condition('rideId', $ride->rideId));

		return $memberTable->getRecordCursor();
		}
	}
