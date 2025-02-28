<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property string $email MySQL type varchar(255)
 * @property int $mailItemId MySQL type int
 * @property \App\Record\MailItem $mailItem related record
 * @property int $mailPieceId MySQL type int
 * @property \App\Record\MailPiece $mailPiece related record
 * @property ?int $memberId MySQL type int
 * @property \App\Record\Member $member related record
 * @property ?string $name MySQL type varchar(100)
 */
abstract class MailPiece extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['mailPieceId', ];

	protected static string $table = 'mailPiece';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'email' => new \PHPFUI\ORM\FieldDefinition('varchar(255)', 'string', 255, false, ),
				'mailItemId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'mailPieceId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'memberId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'name' => new \PHPFUI\ORM\FieldDefinition('varchar(100)', 'string', 100, true, '', ),
			];
			}

		return $this;
		}
	}
