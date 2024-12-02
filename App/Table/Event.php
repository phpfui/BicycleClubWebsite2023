<?php

namespace App\Table;

class Event extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Event::class;

	public static function getAvailableForMember(\App\Record\Member $member) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from event e where eventDate>=? and publicDate<=? and ? not in (select memberId from reservation where eventId=e.eventId) group by eventDate order by eventDate';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [\App\Tools\Date::todayString(), \App\Tools\Date::todayString(), $member->memberId, ]);
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
		$sql = 'select distinct e.* from reservation r left join event e on e.eventId=r.eventId order by e.eventDate desc';

		if ($limit)
			{
			$sql .= ' limit ' . (int)$limit;
			}

		return \PHPFUI\ORM::getRecordCursor($this->instance, $sql);
		}

	public static function getSignedUpForMember(\App\Record\Member $member) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select *,e.eventId from event e left join reservation r on r.eventId=e.eventId left join member m on m.memberId=r.memberId
			where e.eventDate>=? and r.memberId=? order by eventDate';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [\App\Tools\Date::todayString(), $member->memberId]);
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
