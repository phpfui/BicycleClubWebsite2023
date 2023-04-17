<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\File> $FileChildren
 */
class FileFolder extends \App\Record\Definition\FileFolder
	{
	 protected static array $virtualFields = [
	 	'FileChildren' => [\PHPFUI\ORM\Children::class, \App\Table\File::class],
	 ];

	 public function clean() : static
		 {
		 $this->cleanProperName('fileFolder');

		 return $this;
		 }
	}
