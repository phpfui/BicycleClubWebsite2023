<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $additionalInfo MySQL type blob
 * @property ?int $checks MySQL type int
 * @property string $commentTitle MySQL type varchar(255)
 * @property ?string $directionsUrl MySQL type varchar(100)
 * @property ?int $door MySQL type int
 * @property ?string $endTime MySQL type varchar(20)
 * @property string $eventDate MySQL type date
 * @property int $eventId MySQL type int
 * @property \App\Record\Event $event related record
 * @property ?string $information MySQL type blob
 * @property string $lastRegistrationDate MySQL type date
 * @property ?string $location MySQL type varchar(250)
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
 * @property ?string $title MySQL type varchar(100)
 */
abstract class Event extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, ALLOWS_NULL, DEFAULT
		'additionalInfo' => ['blob', 'string', 0, true, ],
		'checks' => ['int', 'int', 0, true, 0, ],
		'commentTitle' => ['varchar(255)', 'string', 255, false, '', ],
		'directionsUrl' => ['varchar(100)', 'string', 100, true, ],
		'door' => ['int', 'int', 0, true, 0, ],
		'endTime' => ['varchar(20)', 'string', 20, true, ],
		'eventDate' => ['date', 'string', 10, false, ],
		'eventId' => ['int', 'int', 0, false, ],
		'information' => ['blob', 'string', 0, true, ],
		'lastRegistrationDate' => ['date', 'string', 10, false, ],
		'location' => ['varchar(250)', 'string', 250, true, ],
		'maxDiscounts' => ['int', 'int', 0, false, 0, ],
		'maxReservations' => ['int', 'int', 0, true, 4, ],
		'membersOnly' => ['int', 'int', 0, true, 1, ],
		'newMemberDate' => ['date', 'string', 10, true, ],
		'newMemberDiscount' => ['decimal(6,2)', 'float', 6, false, 0.00, ],
		'numberReservations' => ['int', 'int', 0, true, ],
		'organizer' => ['int', 'int', 0, true, ],
		'paypal' => ['int', 'int', 0, true, 1, ],
		'price' => ['decimal(6,2)', 'float', 6, false, 0.00, ],
		'publicDate' => ['date', 'string', 10, true, ],
		'registrationStartDate' => ['date', 'string', 10, true, ],
		'requireComment' => ['int', 'int', 0, true, 0, ],
		'showComments' => ['tinyint(1)', 'int', 1, false, 0, ],
		'showRegistered' => ['tinyint(1)', 'int', 1, false, 1, ],
		'startTime' => ['varchar(20)', 'string', 20, true, ],
		'title' => ['varchar(100)', 'string', 100, true, ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['eventId', ];

	protected static string $table = 'event';
	}
