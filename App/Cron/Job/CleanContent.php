<?php

namespace App\Cron\Job;

class CleanContent extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Cleanup HTML in Content';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$storyTable = new \App\Table\Story();
		$condition = new \PHPFUI\ORM\Condition('body', '%<br %', new \PHPFUI\ORM\Operator\Like());
		$condition->andNot('body', '%<br data-mce-bogus="1">%', new \PHPFUI\ORM\Operator\Like());
		$storyTable->setWhere($condition);

		foreach ($storyTable->getRecordCursor() as $story)
			{
			$story->update();	// update should clean the html to current standards
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(5, 45);
		}
	}
