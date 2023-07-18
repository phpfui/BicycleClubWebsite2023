<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Photo> $PhotoChildren
 * @property \App\Record\PhotoFolder $parentFolder
 */
class PhotoFolder extends \App\Record\Definition\PhotoFolder
	{
	/** @var array<string, array<string>> */
	 protected static array $virtualFields = [
	 	'PhotoChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Photo::class],
	 	'parentFolder' => [\PHPFUI\ORM\RelatedRecord::class, \App\Record\PhotoFolder::class],
	 ];

	 public function clean() : static
		 {
		 $this->cleanProperName('photoFolder');

		 return $this;
		 }
	}
