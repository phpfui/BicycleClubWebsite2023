<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\File> $FileChildren
 * @property \App\Record\FileFolder $parentFolder
 */
class FileFolder extends \App\Record\Definition\FileFolder
	{
	 protected static array $virtualFields = [
	 	'FileChildren' => [\PHPFUI\ORM\Children::class, \App\Table\File::class],
	 	'parentFolder' => [\App\DB\ParentRecord::class, \App\Record\FileFolder::class],
	 ];

	 public function clean() : static
		 {
		 $this->cleanProperName('fileFolder');

		 return $this;
		 }
	}
