<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property string $dateAdded MySQL type date
 * @property ?string $ext MySQL type char(10)
 * @property int $memberId MySQL type int
 * @property \App\Record\Member $member related record
 * @property ?int $pending MySQL type int
 * @property int $pointsAwarded MySQL type int
 * @property int $signinSheetId MySQL type int
 * @property \App\Record\SigninSheet $signinSheet related record
 */
abstract class SigninSheet extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['signinSheetId', ];

	protected static string $table = 'signinSheet';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'dateAdded' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, false, ),
				'ext' => new \PHPFUI\ORM\FieldDefinition('char(10)', 'string', 10, true, '', ),
				'memberId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'pending' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, 1, ),
				'pointsAwarded' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, 0, ),
				'signinSheetId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
			];
			}

		return $this;
		}
	}
