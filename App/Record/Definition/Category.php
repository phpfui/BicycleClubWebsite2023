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

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['categoryId', ];

	protected static string $table = 'category';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'category' => new \PHPFUI\ORM\FieldDefinition('varchar(20)', 'string', 20, false, ),
				'categoryId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'coordinatorId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'description' => new \PHPFUI\ORM\FieldDefinition('varchar(100)', 'string', 100, true, ),
				'maxSpeed' => new \PHPFUI\ORM\FieldDefinition('varchar(5)', 'string', 5, true, ),
				'memberDefault' => new \PHPFUI\ORM\FieldDefinition('tinyint(1)', 'int', 1, false, 0, ),
				'minSpeed' => new \PHPFUI\ORM\FieldDefinition('varchar(5)', 'string', 5, true, ),
				'ordering' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, 999999, ),
			];
			}

		return $this;
		}
	}
