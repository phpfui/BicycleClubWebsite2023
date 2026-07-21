<?php

namespace App\Table;

class Reservation extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Reservation::class;

	public function getEmails(int $eventId, int $unpaidOnly) : \PHPFUI\ORM\ArrayCursor
		{
		$this->setSelect(new \PHPFUI\ORM\Literal('coalesce(reservationPerson.email,reservation.reservationemail)'), 'email');
		$this->addSelect(new \PHPFUI\ORM\Literal('coalesce(reservationPerson.firstName,reservation.reservationFirstName)'), 'firstName');
		$this->addSelect(new \PHPFUI\ORM\Literal('coalesce(reservationPerson.lastName,reservation.reservationLastName)'), 'lastName');

		$this->setJoin('reservationPerson', type:'left outer');
		$condition = new \PHPFUI\ORM\Condition('reservation.eventId', $eventId);

		if (1 == $unpaidOnly)
			{
			$condition->and('paymentId', 0, new \PHPFUI\ORM\Operator\GreaterThan());
			}
		elseif (2 == $unpaidOnly)
			{
			$orCondition = new \PHPFUI\ORM\Condition('paymentId', null);
			$orCondition->or('paymentId', 0);
			$condition->and($orCondition);
			}
		$this->setWhere($condition);

		return $this->getArrayCursor();
		}

	public function getLatestReservation() : \App\Record\Reservation
		{
		$this->addOrderBy('reservationId', 'desc');
		$this->setLimit(1);

		return $this->getRecordCursor()->current();
		}

	public function setReservationsCursor(\App\Record\Event $event) : static
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('reservation.eventId', $event->eventId));
		$this->addJoin('reservationPerson');
		$this->addJoin('payment');

		return $this;
		}
	}
