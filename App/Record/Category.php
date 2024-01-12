<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Pace> $PaceChildren
 * @property \App\Record\Member $coordinator
 */
class Category extends \App\Record\Definition\Category
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'PaceChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Pace::class],
		'coordinator' => [\PHPFUI\ORM\RelatedRecord::class, \App\Record\Member::class],
	];
	}
