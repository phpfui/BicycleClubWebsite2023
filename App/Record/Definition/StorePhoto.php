<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $extension MySQL type varchar(10)
 * @property ?string $filename MySQL type varchar(255)
 * @property int $sequence MySQL type int
 * @property int $storeItemId MySQL type int
 * @property \App\Record\StoreItem $storeItem related record
 * @property int $storePhotoId MySQL type int
 * @property \App\Record\StorePhoto $storePhoto related record
 */
abstract class StorePhoto extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, ALLOWS_NULL, DEFAULT
		'extension' => ['varchar(10)', 'string', 10, true, ],
		'filename' => ['varchar(255)', 'string', 255, true, ],
		'sequence' => ['int', 'int', 0, false, ],
		'storeItemId' => ['int', 'int', 0, false, ],
		'storePhotoId' => ['int', 'int', 0, false, ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['storePhotoId', ];

	protected static string $table = 'storePhoto';
	}
