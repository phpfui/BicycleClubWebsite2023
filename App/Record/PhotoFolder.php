<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Photo> $PhotoChildren
 */
class PhotoFolder extends \App\Record\Definition\PhotoFolder
	{
	 protected static array $virtualFields = [
	 	'PhotoChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Photo::class],
	 ];

	 public function clean() : static
		 {
		 $this->cleanProperName('photoFolder');

		 return $this;
		 }
	}
