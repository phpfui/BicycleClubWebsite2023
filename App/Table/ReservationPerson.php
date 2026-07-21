<?php

namespace App\Table;

class ReservationPerson extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\ReservationPerson::class;

	public function getNamesAlpha(\App\Record\Event $event) : \PHPFUI\ORM\DataObjectCursor
		{
		$reservationTable = new \App\Table\Reservation();

		$reservationTable->setJoin('reservationPerson');
		$reservationTable->setWhere(new \PHPFUI\ORM\Condition('reservation.eventId', $event->eventId));
		$reservationTable->setOrderBy('reservationPerson.lastName');
		$reservationTable->addOrderBy('reservationPerson.firstName');

		return $reservationTable->getDataObjectCursor();
		}
	}
