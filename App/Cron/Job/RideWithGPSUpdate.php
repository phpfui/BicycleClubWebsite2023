<?php

namespace App\Cron\Job;

class RideWithGPSUpdate extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Update RideWithGPS info';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$model = new \App\Model\RideWithGPS();
		$rwgpsTable = new \App\Table\RWGPS();
		$rideTable = new \App\Table\Ride();

		$upcoming = $rwgpsTable->getUpcomingRWGPS();

		foreach ($upcoming as $rwgps)
			{
			$original = clone $rwgps;
			$updated = $model->scrape($rwgps);

			if ($updated && $updated->RWGPSId)
				{
				// datebase may store things differently, save and reload to make it consistant
				$updated->insertOrUpdate();
				$updated->reload();

				// if the ride has been updated, notify all signed up riders
				if ($original->csv != $updated->csv)
					{
					$settingTable = new \App\Table\Setting();
					$url = $settingTable->value('homePage');

					$rwgpsLink = new \PHPFUI\Link($updated->routeLink(), 'RWGPS route');

					$rideTable->addJoin('rideRWGPS');
					$condition = new \PHPFUI\ORM\Condition('rideDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
					$condition->and('rideRWGPS.RWGPSId', $updated->RWGPSId);
					$rideTable->setWhere($condition);

					foreach ($rideTable->getRecordCursor() as $ride)
						{
						$email = new \App\Tools\EMail();
						$email->setFromMember($ride->member->toArray());
						$email->setSubject('The RWGPS route has changed for the ride ' . $ride->title);
						$link = new \PHPFUI\Link($url . '/Rides/signedUp/' . $ride->rideId, $ride->title);
						$updatedAt = \date('g:i a', \strtotime($updated->lastUpdated)) . ' on ' . $updatedAt = \date('F j', \strtotime($updated->lastUpdated));
						$html = "The {$rwgpsLink} has changed for the ride {$link} was changed at {$updatedAt}.<p>You should reload the route if you have downloaded it previously.";
						$email->setBody($html);
						$email->setHtml();

						foreach ($ride->rideSignups as $rider)
							{
							$email->addBCCMember($rider->member->toArray());
							}
						$email->bulkSend();
						}
					}
				}
			}

		$rides = $rwgpsTable->getOldest(200);

		foreach ($rides as $rwgps)
			{
			$updated = $model->scrape($rwgps);

			if (! $updated)
				{
				break;
				}

			if ($updated->RWGPSId)
				{
				$updated->update();
				}
			}
		}

	public function willRun() : bool
		{
		return true;
		}
	}
