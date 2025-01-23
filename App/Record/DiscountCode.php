<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property int $timesUsed
 * @property \App\Enum\Store\Type $type
 */
class DiscountCode extends \App\Record\Definition\DiscountCode
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'type' => [\PHPFUI\ORM\Enum::class, \App\Enum\Store\DiscountType::class],
		'timesUsed' => [\App\DB\DiscountCodeUses::class],
	];
	}
