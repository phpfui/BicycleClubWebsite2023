<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\File> $FileChildren
 * @property \App\Record\Folder $parentFolder
 */
class FileFolder extends \App\Record\Definition\FileFolder
	{
	/** @var array<string, array<string>> */
	 protected static array $virtualFields = [
	 	'FileChildren' => [\PHPFUI\ORM\Children::class, \App\Table\File::class],
	 	'parentFolder' => [\PHPFUI\ORM\RelatedRecord::class, \App\Record\Folder::class],
	 ];

	 public function clean() : static
		 {
		 $this->cleanProperName('fileFolder');

		 return $this;
		 }
	}
