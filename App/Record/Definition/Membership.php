<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $address MySQL type varchar(100)
 * @property ?string $affiliation MySQL type varchar(200)
 * @property int $allowedMembers MySQL type int
 * @property ?string $expires MySQL type date
 * @property ?string $joined MySQL type date
 * @property ?string $lastRenewed MySQL type date
 * @property int $membershipId MySQL type int
 * @property \App\Record\Membership $membership related record
 * @property int $pending MySQL type int
 * @property ?string $renews MySQL type date
 * @property ?string $state MySQL type char(2)
 * @property ?string $subscriptionId MySQL type varchar(20)
 * @property ?string $town MySQL type varchar(50)
 * @property ?string $zip MySQL type varchar(10)
 */
abstract class Membership extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['membershipId', ];

	protected static string $table = 'membership';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'address' => new \PHPFUI\ORM\FieldDefinition('varchar(100)', 'string', 100, true, ),
				'affiliation' => new \PHPFUI\ORM\FieldDefinition('varchar(200)', 'string', 200, true, ),
				'allowedMembers' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, 0, ),
				'expires' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, true, ),
				'joined' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, true, ),
				'lastRenewed' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, true, ),
				'membershipId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'pending' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, 1, ),
				'renews' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, true, ),
				'state' => new \PHPFUI\ORM\FieldDefinition('char(2)', 'string', 2, true, ),
				'subscriptionId' => new \PHPFUI\ORM\FieldDefinition('varchar(20)', 'string', 20, true, ),
				'town' => new \PHPFUI\ORM\FieldDefinition('varchar(50)', 'string', 50, true, ),
				'zip' => new \PHPFUI\ORM\FieldDefinition('varchar(10)', 'string', 10, true, ),
			];
			}

		return $this;
		}
	}
