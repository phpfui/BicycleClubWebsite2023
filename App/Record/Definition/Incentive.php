<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $description MySQL type varchar(100)
 * @property int $incentiveId MySQL type int
 * @property \App\Record\Incentive $incentive related record
 * @property ?int $notRide MySQL type int
 * @property ?int $points MySQL type int
 */
abstract class Incentive extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, ALLOWS_NULL, DEFAULT
		'description' => ['varchar(100)', 'string', 100, true, ],
		'incentiveId' => ['int', 'int', 0, false, ],
		'notRide' => ['int', 'int', 0, true, ],
		'points' => ['int', 'int', 0, true, ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['incentiveId', ];

	protected static string $table = 'incentive';
	}
