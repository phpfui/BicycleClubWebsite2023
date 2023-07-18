<?php

namespace App\Table;

class GaRider extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\GaRider::class;

	/**
	 * @param array<int> $events
	 *
	 * @return array<array<string,mixed>>
	 */
	public static function getEmailsForEvents(array $events, $pending = 0) : array
		{
		$sql = 'select email,firstName,lastName,gaRiderId,pending from gaRider where gaEventId in (';
		$ids = [];

		foreach ($events as $gaEventId => $value)
			{
			if ($value)
				{
				$ids[] = (int)$gaEventId;
				}
			}

		if (! $ids)
			{
			$ids[] = 0;
			}
		$sql .= \implode(',', $ids);
		$sql = $sql . ') group by email order by email, pending desc';
		$result = \PHPFUI\ORM::getArrayCursor($sql);
		$retVal = [];

		foreach ($result as $rider)
			{
			if ($pending == $rider['pending'])
				{
				$retVal[] = $rider;
				}
			}

		return $retVal;
		}

	/**
	 * @param array<int> $events
	 *
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\GaRider>
	 */
	public function getForEvents(array $events) : \PHPFUI\ORM\RecordCursor
		{
		$this->addOrderBy('lastName')->addOrderBy('firstName');

		if (empty($events))
			{
			$events = [0];
			}
		$this->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $events, new \PHPFUI\ORM\Operator\In()));

		return $this->getRecordCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\GaRider>
	 */
	public function getPaidRiderCursor(\App\Record\GaEvent $event) : \PHPFUI\ORM\RecordCursor
		{
		$condition = new \PHPFUI\ORM\Condition('gaEventId', $event->gaEventId);
		$condition->and(new \PHPFUI\ORM\Condition('pending', 0));
		$this->setWhere($condition);
		$this->addOrderBy('lastName')->addOrderBy('firstName');

		return $this->getRecordCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\GaRider>
	 */
	public function getRidersBySignup(\App\Record\GaEvent $gaEvent) : \PHPFUI\ORM\RecordCursor
		{
		$this->addOrderBy('signedUpOn');
		$condition = new \PHPFUI\ORM\Condition('gaEventId', $gaEvent->gaEventId);
		$condition->and('signedUpOn', '1000-01-01', new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('pending', 0);
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	public function purgePendingDupes(\App\Record\GaEvent $event) : bool
		{
		$sql = 'DELETE FROM gaRider where gaEventId=? and pending=1 and email IN (select email from (select distinct email from gaRider where gaEventId=? and pending=0) as c)';

		return \PHPFUI\ORM::execute($sql, [$event->gaEventId, $event->gaEventId]);
		}

	public function totalRegistrants(\App\Record\GaEvent $event) : int
		{
		return (int)\PHPFUI\ORM::getValue('select count(*) from gaRider where gaEventId=? and pending=0', [$event->gaEventId]);
		}
	}
