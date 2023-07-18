<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\BikeShop> $BikeShopChildren
 */
class BikeShopArea extends \App\Record\Definition\BikeShopArea
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'BikeShopChildren' => [\PHPFUI\ORM\Children::class, \App\Table\BikeShop::class],
	];
	}
