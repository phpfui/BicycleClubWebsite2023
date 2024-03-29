<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property int $gaOptionId MySQL type int
 * @property \App\Record\GaOption $gaOption related record
 * @property int $gaRiderId MySQL type int
 * @property \App\Record\GaRider $gaRider related record
 * @property int $gaSelectionId MySQL type int
 * @property \App\Record\GaSelection $gaSelection related record
 */
abstract class GaRiderSelection extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = false;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, ALLOWS_NULL, DEFAULT
		'gaOptionId' => ['int', 'int', 0, false, ],
		'gaRiderId' => ['int', 'int', 0, false, ],
		'gaSelectionId' => ['int', 'int', 0, false, ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['gaRiderId', 'gaOptionId', 'gaSelectionId', ];

	protected static string $table = 'gaRiderSelection';
	}
