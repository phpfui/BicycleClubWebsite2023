<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property string $endTime MySQL type time
 * @property ?int $jobId MySQL type int
 * @property \App\Record\Job $job related record
 * @property int $jobShiftId MySQL type int
 * @property \App\Record\JobShift $jobShift related record
 * @property ?int $needed MySQL type int
 * @property string $startTime MySQL type time
 */
abstract class JobShift extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, ALLOWS_NULL, DEFAULT
		'endTime' => ['time', 'string', 0, false, ],
		'jobId' => ['int', 'int', 0, true, ],
		'jobShiftId' => ['int', 'int', 0, false, ],
		'needed' => ['int', 'int', 0, true, ],
		'startTime' => ['time', 'string', 0, false, ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['jobShiftId', ];

	protected static string $table = 'jobShift';
	}
