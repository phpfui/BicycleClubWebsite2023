<?php

namespace App\Cron\Job;

class SparkPostSuppressions extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Process SparkPost Suppression Lists';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		try
			{
			$model = new \App\Model\SparkPost();
			$suppressions = $model->getSuppressionList();
			$deletes = [];

			foreach ($suppressions as $suppression)
				{
				$member = new \App\Record\Member(['email' => \App\Model\Member::cleanEmail($suppression['recipient'])]); // @phpstan-ignore-line

				if ($member->loaded())
					{
					$member->emailAnnouncements = 0;
					$member->emailNewsletter = 0;
					$member->journal = 0;
					$member->newRideEmail = 0;
					$member->rideComments = 0;
					$member->rideJournal = 0;
					$member->update();

					if ('Bounce Rule' != $suppression['source']) // @phpstan-ignore-line
						{
						$message = $member->fullName() . ', ' . $member->email . ' has been removed from the SparkPost suppression list';
						$deletes[] = $suppression['recipient']; // @phpstan-ignore-line
						}
					else
						{
						$message = $member->fullName() . ', ' . $member->email . ' is bouncing: ' . $suppression['description']; // @phpstan-ignore-line
						}
					\App\Tools\Logger::get()->debug($message);

					$deletes[] = $suppression['recipient']; // @phpstan-ignore-line
					}
				}
			$model->deleteSuppressions($deletes);
			}
		catch (\Exception $e)
			{
			$this->controller->log_exception($e->getMessage());
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(3, 15);
		}
	}
