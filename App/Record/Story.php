<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\BlogItem> $BlogItemChildren
 */
class Story extends \App\Record\Definition\Story
	{
	protected static array $virtualFields = [
		'BlogItemChildren' => [\PHPFUI\ORM\Children::class, \App\Table\BlogItem::class],
	];
	}
