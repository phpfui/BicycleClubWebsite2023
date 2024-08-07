<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $address MySQL type varchar(100)
 * @property ?int $agreedToWaiver MySQL type tinyint
 * @property ?string $comments MySQL type varchar(50)
 * @property ?string $contact MySQL type varchar(50)
 * @property ?string $contactPhone MySQL type char(15)
 * @property ?int $customerId MySQL type int
 * @property \App\Record\Customer $customer related record
 * @property ?string $email MySQL type varchar(50)
 * @property ?int $emailAnnouncements MySQL type int
 * @property ?string $firstName MySQL type varchar(50)
 * @property int $gaEventId MySQL type int
 * @property \App\Record\GaEvent $gaEvent related record
 * @property int $gaRiderId MySQL type int
 * @property \App\Record\GaRider $gaRider related record
 * @property ?string $lastName MySQL type varchar(50)
 * @property int $memberId MySQL type int
 * @property \App\Record\Member $member related record
 * @property int $pending MySQL type int
 * @property ?string $phone MySQL type varchar(20)
 * @property ?float $pricePaid MySQL type decimal(5,2)
 * @property ?int $prize MySQL type int
 * @property string $signedUpOn MySQL type datetime
 * @property ?string $state MySQL type char(2)
 * @property ?string $town MySQL type varchar(50)
 * @property ?string $zip MySQL type varchar(10)
 */
abstract class GaRider extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, ALLOWS_NULL, DEFAULT
		'address' => ['varchar(100)', 'string', 100, true, ],
		'agreedToWaiver' => ['tinyint', 'int', 0, true, ],
		'comments' => ['varchar(50)', 'string', 50, true, ],
		'contact' => ['varchar(50)', 'string', 50, true, ],
		'contactPhone' => ['char(15)', 'string', 15, true, ],
		'customerId' => ['int', 'int', 0, true, ],
		'email' => ['varchar(50)', 'string', 50, true, ],
		'emailAnnouncements' => ['int', 'int', 0, true, 1, ],
		'firstName' => ['varchar(50)', 'string', 50, true, ],
		'gaEventId' => ['int', 'int', 0, false, ],
		'gaRiderId' => ['int', 'int', 0, false, ],
		'lastName' => ['varchar(50)', 'string', 50, true, ],
		'memberId' => ['int', 'int', 0, false, ],
		'pending' => ['int', 'int', 0, false, 1, ],
		'phone' => ['varchar(20)', 'string', 20, true, '', ],
		'pricePaid' => ['decimal(5,2)', 'float', 5, true, ],
		'prize' => ['int', 'int', 0, true, ],
		'signedUpOn' => ['datetime', 'string', 20, false, null, ],
		'state' => ['char(2)', 'string', 2, true, ],
		'town' => ['varchar(50)', 'string', 50, true, ],
		'zip' => ['varchar(10)', 'string', 10, true, ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['gaRiderId', ];

	protected static string $table = 'gaRider';
	}
