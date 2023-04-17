<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\BlogItem> $BlogItemChildren
 */
class Blog extends \App\Record\Definition\Blog
	{
	protected static array $virtualFields = [
		'BlogItemChildren' => [\PHPFUI\ORM\Children::class, \App\Table\BlogItem::class],
	];
	}
