<?php

namespace App\Table;

class GaRider extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\GaRider::class;

	/**
	 * @param array<int> $events
	 *
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\GaRider>
	 */
	public function getEmailsForEvents(array $events, int $pending = 0) : \PHPFUI\ORM\RecordCursor
		{
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
		$condition = new \PHPFUI\ORM\Condition('gaEventId', $ids, new \PHPFUI\ORM\Operator\In());

		if ($pending)
			{
			$condition->and('pending', $pending);
			}
		$this->setWhere($condition);
		$this->addGroupBy('email');
		$this->addOrderBy('email');
		$this->addOrderBy('pending', 'desc');

		return $this->getRecordCursor();
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

	public function getRiderCursor(\App\Record\GaEvent $event, int $paid = 1) : \PHPFUI\ORM\DataObjectCursor
		{
		$gaOptionTable = new \App\Table\GaOption();
		$whereCondition = new \PHPFUI\ORM\Condition('gaeventId', $event->gaEventId);
		$whereCondition->and(new \PHPFUI\ORM\Condition('csvField', '', new \PHPFUI\ORM\Operator\GreaterThan()));
		$gaOptionTable->setWhere($whereCondition);
		$gaOptionTable->addOrderBy('ordering');
		$options = $gaOptionTable->getRecordCursor();

		$this->addSelect('gaRider.*');
		$joinNumber = 1;

		foreach ($options as $option)
			{
			$this->addSelect(new \PHPFUI\ORM\Literal("COALESCE(s{$joinNumber}.csvValue,s{$joinNumber}.selectionName)"), $option->csvField);

			$riderSelectionJoinAlias = 'rs' . $joinNumber;
			$onCondition = new \PHPFUI\ORM\Condition('gaRider.gaRiderId', new \PHPFUI\ORM\Literal("{$riderSelectionJoinAlias}.gaRiderId"));
			$onCondition->and("{$riderSelectionJoinAlias}.gaOptionId", $option->gaOptionId);
			$this->addJoin('gaRiderSelection', $onCondition, 'left', $riderSelectionJoinAlias);

			$selectionJoinAlias = 's' . $joinNumber;
			$selectionOnCondition = new \PHPFUI\ORM\Condition("{$selectionJoinAlias}.gaSelectionId", new \PHPFUI\ORM\Literal("{$riderSelectionJoinAlias}.gaSelectionId"));
			$this->addJoin('gaSelection', $selectionOnCondition, 'left', $selectionJoinAlias);
			++$joinNumber;
			}

		$condition = new \PHPFUI\ORM\Condition('gaRider.gaEventId', $event->gaEventId);

		if (0 === $paid)
			{
			$condition->and(new \PHPFUI\ORM\Condition('pending', 1));
			}
		elseif (1 === $paid)
			{
			$condition->and(new \PHPFUI\ORM\Condition('pending', 0));
			}
		$this->setWhere($condition);
		$this->addOrderBy('lastName')->addOrderBy('firstName');

		return $this->getDataObjectCursor();
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
