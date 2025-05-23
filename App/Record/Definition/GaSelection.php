<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?float $additionalPrice MySQL type decimal(7,2)
 * @property ?string $csvValue MySQL type varchar(20)
 * @property int $gaEventId MySQL type int
 * @property \App\Record\GaEvent $gaEvent related record
 * @property int $gaOptionId MySQL type int
 * @property \App\Record\GaOption $gaOption related record
 * @property int $gaSelectionId MySQL type int
 * @property \App\Record\GaSelection $gaSelection related record
 * @property int $ordering MySQL type int
 * @property ?int $selectionActive MySQL type int
 * @property string $selectionName MySQL type varchar(255)
 */
abstract class GaSelection extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['gaSelectionId', ];

	protected static string $table = 'gaSelection';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'additionalPrice' => new \PHPFUI\ORM\FieldDefinition('decimal(7,2)', 'float', 7, true, ),
				'csvValue' => new \PHPFUI\ORM\FieldDefinition('varchar(20)', 'string', 20, true, ),
				'gaEventId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'gaOptionId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'gaSelectionId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'ordering' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, 0, ),
				'selectionActive' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, 1, ),
				'selectionName' => new \PHPFUI\ORM\FieldDefinition('varchar(255)', 'string', 255, false, ),
			];
			}

		return $this;
		}
	}
