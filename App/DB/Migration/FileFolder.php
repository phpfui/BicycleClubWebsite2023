<?php

namespace App\DB\Migration;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\File> $FileChildren
 * @property \App\Record\FileFolder $parentFolder
 */
class FileFolder extends \App\DB\Migration\Definition\FileFolder
	{
	/** @var array<string, array<string>> */
	 protected static array $virtualFields = [
	 	'FileChildren' => [\PHPFUI\ORM\Children::class, \App\Table\File::class],
	 	'parentFolder' => [\PHPFUI\ORM\RelatedRecord::class, \App\Record\FileFolder::class],
	 ];

	 public function clean() : static
		 {
		 $this->cleanProperName('fileFolder');

		 return $this;
		 }
	}
