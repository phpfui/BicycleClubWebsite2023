<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Pace> $PaceChildren
 */
class Category extends \App\Record\Definition\Category
	{
	protected static array $virtualFields = [
		'PaceChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Pace::class],
	];
	}
