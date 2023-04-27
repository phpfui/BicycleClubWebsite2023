<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \App\Record\Member $editor
 */
class PointHistory extends \App\Record\Definition\PointHistory
	{
	protected static array $virtualFields = [
		'editor' => [\PHPFUI\ORM\RelatedRecord::class, \App\Record\Member::class],
	];
	}
