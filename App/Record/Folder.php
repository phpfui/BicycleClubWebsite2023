<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Photo> $photoChildren
 * @property \App\Record\Folder $parentFolder
 * @property \App\Enum\FolderType $folderType
 */
class Folder extends \App\Record\Definition\Folder
	{
	/** @var array<string, array<string>> */
	 protected static array $virtualFields = [
	 	'photoChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Photo::class],
	 	'parentFolder' => [\PHPFUI\ORM\RelatedRecord::class, \App\Record\Folder::class],
	 	'folderType' => [\PHPFUI\ORM\Enum::class, \App\Enum\FolderType::class],
	 ];

	 public function clean() : static
		 {
		 $this->cleanProperName('name');

		 return $this;
		 }
	}
