<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\BlogItem> $BlogItemChildren
 * @property \App\Record\Member $editor
 */
class Story extends \App\Record\Definition\Story
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'BlogItemChildren' => [\PHPFUI\ORM\Children::class, \App\Table\BlogItem::class],
		'editor' => [\PHPFUI\ORM\RelatedRecord::class, \App\Record\Member::class],
	];

	public function clean() : static
		{
		$this->body = \App\Tools\TextHelper::cleanUserHtml($this->body);
		$this->lastEdited = \App\Tools\Date::todayString();
		$this->editorId = \App\Model\Session::signedInMemberId();

		return $this;
		}
	}
