<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $description MySQL type varchar(100)
 * @property ?int $distance MySQL type int
 * @property ?string $endTime MySQL type time
 * @property ?float $extraPrice MySQL type decimal(6,2)
 * @property int $gaEventId MySQL type int
 * @property \App\Record\GaEvent $gaEvent related record
 * @property int $gaRideId MySQL type int
 * @property \App\Record\GaRide $gaRide related record
 * @property ?string $startTime MySQL type time
 */
abstract class GaRide extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, ALLOWS_NULL, DEFAULT
		'description' => ['varchar(100)', 'string', 100, true, ],
		'distance' => ['int', 'int', 0, true, ],
		'endTime' => ['time', 'string', 0, true, ],
		'extraPrice' => ['decimal(6,2)', 'float', 6, true, ],
		'gaEventId' => ['int', 'int', 0, false, ],
		'gaRideId' => ['int', 'int', 0, false, ],
		'startTime' => ['time', 'string', 0, true, ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['gaRideId', ];

	protected static string $table = 'gaRide';
	}
