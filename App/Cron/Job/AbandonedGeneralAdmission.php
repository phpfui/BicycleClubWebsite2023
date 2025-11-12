<?php

namespace App\Cron\Job;

class AbandonedGeneralAdmission extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Send abandon General Admission emails.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{

		$today = \App\Tools\Date::toString($this->controller->runningAtJD());

		$gaRiderTable = new \App\Table\GaRider()->setWhere(new \PHPFUI\ORM\Condition('pending', 1))->setOrderBy('gaEventId');
		$lastEventId = 0;

		$event = new \App\Record\GaEvent();

		foreach ($gaRiderTable->getRecordCursor() as $gaRider)
			{
			if ($lastEventId != $gaRider->gaEventId)
				{
				$lastEventId = $gaRider->gaEventId;
				$event = new \App\Record\GaEvent($lastEventId);
				}

			if (! $event->incompleteMessage || ! $event->incompleteDaysAfter)
				{
				continue;
				}
			$daysAfter = \explode(',', $event->incompleteDaysAfter);

			foreach ($daysAfter as $index => $day)
				{
				$day = (int)$day;

				if ($day)
					{
					$daysAfter[$index] = (int)$day;
					}
				else
					{
					unset($daysAfter[$index]);
					}
				}

			if (! \count($daysAfter))	// @phpstan-ignore-line
				{
				continue;
				}
			\sort($daysAfter);
			\end($daysAfter);
			$oldestDay = \current($daysAfter);
			$daysAgo = \App\Tools\Date::diff(\substr($gaRider->signedUpOn, 0, 10), $today);

			if ($oldestDay < $daysAgo)
				{
				$gaRider->delete();
				}
			elseif (\in_array($daysAgo, $daysAfter))
				{
				$gaModel = new \App\Model\GeneralAdmission();
				$gaModel->addRiderToIncompleteEmail($event, $gaRider);
				unset($gaModel);
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(10, 30);
		}
	}
