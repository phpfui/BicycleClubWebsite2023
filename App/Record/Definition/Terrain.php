<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $name MySQL type varchar(70)
 * @property int $terrainId MySQL type int
 * @property \App\Record\Terrain $terrain related record
 */
abstract class Terrain extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['terrainId', ];

	protected static string $table = 'terrain';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'name' => new \PHPFUI\ORM\FieldDefinition('varchar(70)', 'string', 70, true, '', ),
				'terrainId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
			];
			}

		return $this;
		}
	}
