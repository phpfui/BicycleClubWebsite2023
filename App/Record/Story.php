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

		return $this;
		}

	public function delete() : bool
		{
		$condition = new \PHPFUI\ORM\Condition('storyId', $this->storyId);
		$blogItemTable = new \App\Table\BlogItem()->setWhere($condition)->delete();
		$pollTable = new \App\Table\Poll()->setWhere($condition)->update(['storyId' => null]);

		return parent::delete();
		}
	}
