<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\StoreItemOption> $StoreItemOptionChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\StoreItemDetail> $StoreItemDetailChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\StorePhotoOption> $storePhotoChildren
 */
class StoreItem extends \App\Record\Definition\StoreItem
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'StoreItemDetailChildren' => [\PHPFUI\ORM\Children::class, \App\Table\StoreItemDetail::class],
		'StoreItemOptionChildren' => [\PHPFUI\ORM\Children::class, \App\Table\StoreItemOption::class, 'sequence'],
		'storePhotoChildren' => [\PHPFUI\ORM\Children::class, \App\Table\StorePhoto::class],
	];

	public function clean() : static
		{
		$this->description = \App\Tools\TextHelper::cleanUserHtml($this->description);

		return $this;
		}
	}
