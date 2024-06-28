<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\File> $fileChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Photo> $photoChildren
 * @property \App\Record\Folder $parentFolder
 * @property \App\Enum\FolderType $folderType
 */
class Folder extends \App\Record\Definition\Folder
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'fileChildren' => [\PHPFUI\ORM\Children::class, \App\Table\File::class],
		'photoChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Photo::class],
		'parentFolder' => [\PHPFUI\ORM\RelatedRecord::class, \App\Record\Folder::class],
		'folderType' => [\PHPFUI\ORM\Enum::class, \App\Enum\FolderType::class],
	];

	public function childCount() : int
		{
		$count = (int)\PHPFUI\ORM::getValue('select count(*) from folder where parentFolderId=?', [$this->folderId]);

		foreach (['photo', 'file', ] as $table)
		{
			$count += (int)\PHPFUI\ORM::getValue('select count(*) from ' . $table . ' where folderId=?', [$this->folderId]);
		}

		return $count;
		}

	public function clean() : static
		{
		$this->cleanProperName('name');

		return $this;
		}
	}
