<?php

namespace App\Table;

class GaPriceDate extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\GaPriceDate::class;

	public function getLastRegistrationRecord(\App\Record\GaEvent $event) : \App\Record\GaPriceDate
		{
		$sql = 'select * from gaPriceDate where gaEventId=? order by date desc limit 1';
		$input = [$event->gaEventId];

		$price = new \App\Record\GaPriceDate();
		$price->loadFromSQL($sql, $input);

		return $price;
		}

	public function getCurrentRegistrationRecord(\App\Record\GaEvent $event) : \App\Record\GaPriceDate
		{
		$sql = 'select * from gaPriceDate where gaEventId=? and date<=? order by date desc limit 1';
		$input = [$event->gaEventId, \App\Tools\Date::todayString(), ];

		$price = new \App\Record\GaPriceDate();
		$price->loadFromSQL($sql, $input);

		return $price;
		}
	}
