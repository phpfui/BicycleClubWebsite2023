<?php

namespace App\Table;

class ReservationPerson extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\ReservationPerson::class;

	public static function getNamesAlpha(\App\Record\Event $event) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'SELECT * FROM reservation r left join reservationPerson p on r.reservationId=p.reservationId where r.eventId=? order by p.lastName,p.firstName';

		return \PHPFUI\ORM::getArrayCursor($sql, [$event->eventId]);
		}
	}
