<?php

namespace App\Cron\Job;

class PurgeOrphanContent extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Purge Orphan content (body reads "Enter body here...")';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$storyTable = new \App\Table\Story();
		$storyTable->setWhere(new \PHPFUI\ORM\Condition('body', 'Enter body here...'));
		// for when we have cascading deletes
		// $storyTable->delete();

		foreach ($storyTable->getRecordCursor() as $story)
			{
			$story->delete();
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(7, 10);
		}
	}
