<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $fileName MySQL type varchar(255)
 * @property int $forumAttachmentId MySQL type int
 * @property \App\Record\ForumAttachment $forumAttachment related record
 * @property ?int $forumId MySQL type int
 * @property \App\Record\Forum $forum related record
 * @property ?int $forumMessageId MySQL type int
 * @property \App\Record\ForumMessage $forumMessage related record
 */
abstract class ForumAttachment extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['forumAttachmentId', ];

	protected static string $table = 'forumAttachment';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'fileName' => new \PHPFUI\ORM\FieldDefinition('varchar(255)', 'string', 255, true, ),
				'forumAttachmentId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'forumId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'forumMessageId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
			];
			}

		return $this;
		}
	}
