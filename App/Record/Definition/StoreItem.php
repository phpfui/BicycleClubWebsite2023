<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property int $active MySQL type int
 * @property ?int $clothing MySQL type int
 * @property ?string $description MySQL type text
 * @property ?int $folderId MySQL type int
 * @property \App\Record\Folder $folder related record
 * @property ?int $noShipping MySQL type int
 * @property ?int $payByPoints MySQL type int
 * @property ?string $pickupZip MySQL type char(5)
 * @property int $pointsOnly MySQL type int
 * @property ?float $price MySQL type decimal(5,2)
 * @property ?float $shipping MySQL type decimal(5,2)
 * @property int $storeItemId MySQL type int
 * @property \App\Record\StoreItem $storeItem related record
 * @property ?int $taxable MySQL type int
 * @property ?string $title MySQL type char(100)
 */
abstract class StoreItem extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, ALLOWS_NULL, DEFAULT
		'active' => ['int', 'int', 0, false, 0, ],
		'clothing' => ['int', 'int', 0, true, ],
		'description' => ['text', 'string', 65535, true, ],
		'folderId' => ['int', 'int', 0, true, ],
		'noShipping' => ['int', 'int', 0, true, ],
		'payByPoints' => ['int', 'int', 0, true, ],
		'pickupZip' => ['char(5)', 'string', 5, true, ],
		'pointsOnly' => ['int', 'int', 0, false, 0, ],
		'price' => ['decimal(5,2)', 'float', 5, true, ],
		'shipping' => ['decimal(5,2)', 'float', 5, true, ],
		'storeItemId' => ['int', 'int', 0, false, ],
		'taxable' => ['int', 'int', 0, true, ],
		'title' => ['char(100)', 'string', 100, true, ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['storeItemId', ];

	protected static string $table = 'storeItem';
	}
