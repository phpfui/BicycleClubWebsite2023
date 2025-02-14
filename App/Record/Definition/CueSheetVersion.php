<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?int $cueSheetId MySQL type int
 * @property \App\Record\CueSheet $cueSheet related record
 * @property int $cueSheetVersionId MySQL type int
 * @property \App\Record\CueSheetVersion $cueSheetVersion related record
 * @property string $dateAdded MySQL type date
 * @property ?string $extension MySQL type char(10)
 * @property ?string $link MySQL type varchar(200)
 * @property ?int $memberId MySQL type int
 * @property \App\Record\Member $member related record
 */
abstract class CueSheetVersion extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['cueSheetVersionId', ];

	protected static string $table = 'cueSheetVersion';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'cueSheetId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'cueSheetVersionId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'dateAdded' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, false, ),
				'extension' => new \PHPFUI\ORM\FieldDefinition('char(10)', 'string', 10, true, ),
				'link' => new \PHPFUI\ORM\FieldDefinition('varchar(200)', 'string', 200, true, ),
				'memberId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
			];
			}

		return $this;
		}
	}
