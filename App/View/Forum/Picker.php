<?php

namespace App\View\Forum;

class Picker extends \PHPFUI\Input\Select
	{
	public function __construct(string $name, int $value, string $label = '')
		{
		parent::__construct($name, $label);

		$forumTable = new \App\Table\Forum();
		$forumTable->addOrderBy('name');
		$this->addOption('', (string)0, 0 == $value);

		foreach ($forumTable->getRecordCursor() as $forum)
			{
			$this->addOption($forum->name, $forum->forumId, $forum->forumId == $value);
			}
		}
	}
