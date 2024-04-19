<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Table\StoreItemDetail> $StoreItemDetail
 * @property \App\Enum\Store\Type $type
 */
class CartItem extends \App\Record\Definition\CartItem
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'StoreItemDetail' => [\App\DB\StoreItemDetail::class],
		'type' => [\PHPFUI\ORM\Enum::class, \App\Enum\Store\Type::class],
	];
	}
