<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property string $category MySQL type varchar(20)
 * @property int $categoryId MySQL type int
 * @property ?int $coordinatorId MySQL type int
 * @property ?string $description MySQL type varchar(100)
 * @property ?string $maxSpeed MySQL type varchar(5)
 * @property int $memberDefault MySQL type tinyint(1)
 * @property ?string $minSpeed MySQL type varchar(5)
 * @property int $ordering MySQL type int
 */
abstract class Category extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, ALLOWS_NULL, DEFAULT
		'category' => ['varchar(20)', 'string', 20, false, ],
		'categoryId' => ['int', 'int', 0, false, ],
		'coordinatorId' => ['int', 'int', 0, true, ],
		'description' => ['varchar(100)', 'string', 100, true, ],
		'maxSpeed' => ['varchar(5)', 'string', 5, true, ],
		'memberDefault' => ['tinyint(1)', 'int', 1, false, 0, ],
		'minSpeed' => ['varchar(5)', 'string', 5, true, ],
		'ordering' => ['int', 'int', 0, false, 999999, ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['categoryId', ];

	protected static string $table = 'category';
	}
