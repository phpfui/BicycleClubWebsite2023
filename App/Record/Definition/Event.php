<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $additionalInfo MySQL type text
 * @property ?int $checks MySQL type int
 * @property string $commentTitle MySQL type varchar(255)
 * @property ?string $directionsUrl MySQL type varchar(255)
 * @property ?int $door MySQL type int
 * @property ?string $endTime MySQL type varchar(20)
 * @property string $eventDate MySQL type date
 * @property int $eventId MySQL type int
 * @property \App\Record\Event $event related record
 * @property ?string $information MySQL type text
 * @property string $lastRegistrationDate MySQL type date
 * @property ?string $location MySQL type varchar(255)
 * @property int $maxDiscounts MySQL type int
 * @property ?int $maxReservations MySQL type int
 * @property ?int $membersOnly MySQL type int
 * @property ?string $newMemberDate MySQL type date
 * @property float $newMemberDiscount MySQL type decimal(6,2)
 * @property ?int $numberReservations MySQL type int
 * @property ?int $organizer MySQL type int
 * @property ?int $paypal MySQL type int
 * @property float $price MySQL type decimal(6,2)
 * @property ?string $publicDate MySQL type date
 * @property ?string $registrationStartDate MySQL type date
 * @property ?int $requireComment MySQL type int
 * @property int $showComments MySQL type tinyint(1)
 * @property int $showRegistered MySQL type tinyint(1)
 * @property ?string $startTime MySQL type varchar(20)
 * @property ?string $title MySQL type varchar(255)
 */
abstract class Event extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['eventId', ];

	protected static string $table = 'event';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'additionalInfo' => new \PHPFUI\ORM\FieldDefinition('text', 'string', 65535, true, ),
				'checks' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, 0, ),
				'commentTitle' => new \PHPFUI\ORM\FieldDefinition('varchar(255)', 'string', 255, false, '', ),
				'directionsUrl' => new \PHPFUI\ORM\FieldDefinition('varchar(255)', 'string', 255, true, ),
				'door' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, 0, ),
				'endTime' => new \PHPFUI\ORM\FieldDefinition('varchar(20)', 'string', 20, true, ),
				'eventDate' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, false, ),
				'eventId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'information' => new \PHPFUI\ORM\FieldDefinition('text', 'string', 65535, true, ),
				'lastRegistrationDate' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, false, ),
				'location' => new \PHPFUI\ORM\FieldDefinition('varchar(255)', 'string', 255, true, ),
				'maxDiscounts' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, 0, ),
				'maxReservations' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, 4, ),
				'membersOnly' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, 1, ),
				'newMemberDate' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, true, ),
				'newMemberDiscount' => new \PHPFUI\ORM\FieldDefinition('decimal(6,2)', 'float', 6, false, 0.00, ),
				'numberReservations' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'organizer' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'paypal' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, 1, ),
				'price' => new \PHPFUI\ORM\FieldDefinition('decimal(6,2)', 'float', 6, false, 0.00, ),
				'publicDate' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, true, ),
				'registrationStartDate' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, true, ),
				'requireComment' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, 0, ),
				'showComments' => new \PHPFUI\ORM\FieldDefinition('tinyint(1)', 'int', 1, false, 0, ),
				'showRegistered' => new \PHPFUI\ORM\FieldDefinition('tinyint(1)', 'int', 1, false, 1, ),
				'startTime' => new \PHPFUI\ORM\FieldDefinition('varchar(20)', 'string', 20, true, ),
				'title' => new \PHPFUI\ORM\FieldDefinition('varchar(255)', 'string', 255, true, ),
			];
			}

		return $this;
		}
	}
