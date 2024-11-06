<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \App\Enum\Event\MembersOnly $membersOnly
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Reservation> $ReservationChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\ReservationPerson> $ReservationPersonChildren
 */
class Event extends \App\Record\Definition\Event
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'ReservationChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Reservation::class],
		'ReservationPersonChildren' => [\PHPFUI\ORM\Children::class, \App\Table\ReservationPerson::class],
		'membersOnly' => [\PHPFUI\ORM\Enum::class, \App\Enum\Event\MembersOnly::class],
	];

	public function clean() : static
		{
		$this->information = \App\Tools\TextHelper::cleanUserHtml($this->information);
		$this->location = \App\Tools\TextHelper::cleanUserHtml($this->location);
		$this->additionalInfo = \App\Tools\TextHelper::cleanUserHtml($this->additionalInfo);

		return $this;
		}
	}
