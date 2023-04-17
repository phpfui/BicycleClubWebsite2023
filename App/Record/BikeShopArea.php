<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\BikeShop> $BikeShopChildren
 */
class BikeShopArea extends \App\Record\Definition\BikeShopArea
	{
	protected static array $virtualFields = [
		'BikeShopChildren' => [\PHPFUI\ORM\Children::class, \App\Table\BikeShop::class],
	];
	}
