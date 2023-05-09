<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\StoreItemOption> $StoreItemOptionChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\StoreItemDetail> $StoreItemDetailChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\StorePhotoOption> $StorePhotoChildren
 */
class StoreItem extends \App\Record\Definition\StoreItem
	{
	protected static array $virtualFields = [
		'StoreItemDetailChildren' => [\PHPFUI\ORM\Children::class, \App\Table\StoreItemDetail::class],
		'StoreItemOptionChildren' => [\PHPFUI\ORM\Children::class, \App\Table\StoreItemOption::class, 'sequence'],
		'StorePhotoChildren' => [\PHPFUI\ORM\Children::class, \App\Table\StorePhoto::class],
	];

	public function clean() : static
		{
		$this->description = \App\Tools\TextHelper::cleanUserHtml($this->description);

		return $this;
		}
	}
