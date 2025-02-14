<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $client MySQL type varchar(255)
 * @property string $expires MySQL type datetime
 * @property int $oauthTokenId MySQL type int
 * @property \App\Record\OauthToken $oauthToken related record
 * @property ?int $oauthUserId MySQL type int
 * @property \App\Record\OauthUser $oauthUser related record
 * @property ?string $scopes MySQL type text
 * @property ?string $token MySQL type varchar(255)
 */
abstract class OauthToken extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['oauthTokenId', ];

	protected static string $table = 'oauthToken';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'client' => new \PHPFUI\ORM\FieldDefinition('varchar(255)', 'string', 255, true, ),
				'expires' => new \PHPFUI\ORM\FieldDefinition('datetime', 'string', 20, false, 'CURRENT_TIMESTAMP', ),
				'oauthTokenId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'oauthUserId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'scopes' => new \PHPFUI\ORM\FieldDefinition('text', 'string', 65535, true, ),
				'token' => new \PHPFUI\ORM\FieldDefinition('varchar(255)', 'string', 255, true, ),
			];
			}

		return $this;
		}
	}
