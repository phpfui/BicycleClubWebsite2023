<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $menu MySQL type varchar(255)
 * @property ?string $name MySQL type varchar(255)
 * @property int $permissionId MySQL type int
 * @property \App\Record\Permission $permission related record
 * @property int $system MySQL type tinyint
 */
abstract class Permission extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['permissionId', ];

	protected static string $table = 'permission';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'menu' => new \PHPFUI\ORM\FieldDefinition('varchar(255)', 'string', 255, true, ),
				'name' => new \PHPFUI\ORM\FieldDefinition('varchar(255)', 'string', 255, true, ),
				'permissionId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'system' => new \PHPFUI\ORM\FieldDefinition('tinyint', 'int', 0, false, 0, ),
			];
			}

		return $this;
		}
	}
