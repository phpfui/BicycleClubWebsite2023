<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \App\Enum\Store\Type $type
 */
class InvoiceItem extends \App\Record\Definition\InvoiceItem
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'type' => [\PHPFUI\ORM\Enum::class, \App\Enum\Store\Type::class],
	];
	}
