<?php

namespace App\Table;

class ReservationPerson extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\ReservationPerson::class;

	public static function getNamesAlpha(\App\Record\Event $event) : iterable
		{
		$sql = 'SELECT * FROM reservationPerson p left join reservation r on r.reservationId=p.reservationId where p.eventId=? and r.signedUpAt>"2000" order by p.lastName,p.firstName';

		return \PHPFUI\ORM::getArrayCursor($sql, [$event->eventId]);
		}
	}
