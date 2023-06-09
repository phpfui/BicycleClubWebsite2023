<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?int $parentFolderId MySQL type int
 * @property ?int $permissionId MySQL type int
 * @property \App\Record\Permission $permission related record
 * @property string $photoFolder MySQL type varchar(255)
 * @property int $photoFolderId MySQL type int
 */
abstract class PhotoFolder extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, ALLOWS_NULL, DEFAULT
		'parentFolderId' => ['int', 'int', 0, true, 0, ],
		'permissionId' => ['int', 'int', 0, true, ],
		'photoFolder' => ['varchar(255)', 'string', 255, false, '', ],
		'photoFolderId' => ['int', 'int', 0, false, ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['photoFolderId', ];

	protected static string $table = 'photoFolder';
	}
