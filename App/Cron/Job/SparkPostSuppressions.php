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
				$member = new \App\Record\Member(['email' => \App\Model\Member::cleanEmail($suppression['recipient'])]);

				if ($member->loaded() && 0 == $member->membership->pending)
					{
					$member->emailAnnouncements = 0;
					$member->emailNewsletter = 0;
					$member->journal = 0;
					$member->newRideEmail = 0;
					$member->rideComments = 0;
					$member->rideJournal = 0;
					$member->update();

					if ('Bounce Rule' != $suppression['source'])
						{
						$message = $member->fullName() . ', ' . $member->email . ' has been removed from the SparkPost suppression list (' . $suppression['source'] . ')';
						$deletes[] = $suppression['recipient'];
						}
					else
						{
						$message = $member->fullName() . ', ' . $member->email . ' is bouncing: ' . $suppression['description'];
						}
					\App\Tools\Logger::get()->debug($message);

					$deletes[] = $suppression['recipient'];
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
