<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $fileName MySQL type mediumblob
 * @property int $mailAttachmentId MySQL type int
 * @property \App\Record\MailAttachment $mailAttachment related record
 * @property int $mailItemId MySQL type int
 * @property \App\Record\MailItem $mailItem related record
 * @property ?string $prettyName MySQL type varchar(255)
 */
abstract class MailAttachment extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, ALLOWS_NULL, DEFAULT
		'fileName' => ['mediumblob', 'string', 0, true, ],
		'mailAttachmentId' => ['int', 'int', 0, false, ],
		'mailItemId' => ['int', 'int', 0, false, ],
		'prettyName' => ['varchar(255)', 'string', 255, true, '', ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['mailAttachmentId', ];

	protected static string $table = 'mailAttachment';
	}
