<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property string $extension MySQL type varchar(10)
 * @property string $file MySQL type varchar(255)
 * @property int $fileFolderId MySQL type int
 * @property \App\Record\FileFolder $fileFolder related record
 * @property int $fileId MySQL type int
 * @property string $fileName MySQL type varchar(255)
 * @property ?int $memberId MySQL type int
 * @property \App\Record\Member $member related record
 * @property int $public MySQL type int
 * @property ?string $uploaded MySQL type timestamp
 */
abstract class File extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, ALLOWS_NULL, DEFAULT
		'extension' => ['varchar(10)', 'string', 10, false, '', ],
		'file' => ['varchar(255)', 'string', 255, false, '', ],
		'fileFolderId' => ['int', 'int', 0, false, ],
		'fileId' => ['int', 'int', 0, false, ],
		'fileName' => ['varchar(255)', 'string', 255, false, '', ],
		'memberId' => ['int', 'int', 0, true, ],
		'public' => ['int', 'int', 0, false, 0, ],
		'uploaded' => ['timestamp', 'string', 20, true, null, ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['fileId', ];

	protected static string $table = 'file';
	}
