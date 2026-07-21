<?php

namespace App\Table;

class Event extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Event::class;

	public function getAvailableForMember(\App\Record\Member $member) : \PHPFUI\ORM\DataObjectCursor
		{
		$today = \App\Tools\Date::todayString();
		$condition = new \PHPFUI\ORM\Condition('eventDate', $today, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('publicDate', $today, new \PHPFUI\ORM\Operator\LessThanEqual());
		$reservationTable = new \App\Table\Reservation();
		$reservationTable->setSelect('memberId');
		$reservationTable->setWhere(new \PHPFUI\ORM\Condition('eventId', new \PHPFUI\ORM\Literal('event.eventId')));
		$condition->and(new \PHPFUI\ORM\Literal("{$member->memberId}"), $reservationTable, new \PHPFUI\ORM\Operator\NotIn());
		$this->setWhere($condition);
		$this->setGroupBy('eventDate');
		$this->setOrderBy('eventDate');

		return $this->getDataObjectCursor();
		}

	public static function getFirst(int $memberId = 0) : string
		{
		$parameters = [];
		$sql = 'select eventDate from event';

		if ($memberId)
			{
			$sql .= ' where organizer = ?';
			$parameters[] = $memberId;
			}

		$sql .= ' order by eventDate limit 1';

		return \PHPFUI\ORM::getValue($sql, $parameters);
		}

	public static function getLast(int $memberId = 0) : string
		{
		$parameters = [];
		$sql = 'select eventDate from event';

		if ($memberId)
			{
			$sql .= ' where organizer = ?';
			$parameters[] = $memberId;
			}

		$sql .= ' order by eventDate desc limit 1';

		return \PHPFUI\ORM::getValue($sql, $parameters);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Event>
	 */
	public function getMostRecentRegistered(int $limit = 10) : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit($limit);
		$this->setDistinct();
		$this->addSelect('event.*');
		$this->addJoin('reservation');
		$this->setOrderBy('eventDate', 'desc');

		return $this->getRecordCursor();
		}

	public function getSignedUpForMember(\App\Record\Member $member) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setJoin('reservation');
		$this->addJoin('member', new \PHPFUI\ORM\Condition('reservation.memberId', new \PHPFUI\ORM\Literal('member.memberId')));
		$condition = new \PHPFUI\ORM\Condition('eventDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('reservation.memberId', $member->memberId);
		$this->setWhere($condition);
		$this->setOrderBy('eventDate');

		return $this->getDataObjectCursor();
		}

	public function setEventAttendeeCountCursor() : static
		{
		$this->addJoin('reservation', 'eventId');
		$this->addJoin('reservationPerson', new \PHPFUI\ORM\Condition(new \PHPFUI\ORM\Field('reservationPerson.reservationId'), new \PHPFUI\ORM\Field('reservation.reservationId')));
		$this->addSelect('event.*');
		$this->addSelect(new \PHPFUI\ORM\Literal('count(reservationPerson.reservationPersonId)'), 'attendees');
		$this->addGroupBy('event.eventId')->addOrderBy('event.eventDate', 'desc');

		return $this;
		}

	public function setUpcomingCursor(bool $publicOnly = true) : static
		{
		$this->addOrderBy('eventDate');
		$condition = new \PHPFUI\ORM\Condition('eventDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());

		if ($publicOnly)
			{
			$condition->and('membersOnly', 0);
			}
		$condition->and('eventDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$publicCondition = new \PHPFUI\ORM\Condition('publicDate', null, new \PHPFUI\ORM\Operator\IsNull());
		$publicCondition->or('publicDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and($publicCondition);
		$this->setWhere($condition);

		return $this;
		}
	}
