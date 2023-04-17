<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Table\StoreItemDetail> $StoreItemDetail
 */
class CartItem extends \App\Record\Definition\CartItem
	{
	protected static array $virtualFields = [
		'StoreItemDetail' => [\App\DB\StoreItemDetail::class],
	];
	}
