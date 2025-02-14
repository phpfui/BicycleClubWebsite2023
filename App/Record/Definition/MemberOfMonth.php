<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $bio MySQL type mediumtext
 * @property ?string $fileNameExt MySQL type varchar(10)
 * @property int $memberId MySQL type int
 * @property \App\Record\Member $member related record
 * @property int $memberOfMonthId MySQL type int
 * @property \App\Record\MemberOfMonth $memberOfMonth related record
 * @property string $month MySQL type date
 */
abstract class MemberOfMonth extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['memberOfMonthId', ];

	protected static string $table = 'memberOfMonth';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'bio' => new \PHPFUI\ORM\FieldDefinition('mediumtext', 'string', 16777215, true, ),
				'fileNameExt' => new \PHPFUI\ORM\FieldDefinition('varchar(10)', 'string', 10, true, ),
				'memberId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'memberOfMonthId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'month' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, false, ),
			];
			}

		return $this;
		}
	}
