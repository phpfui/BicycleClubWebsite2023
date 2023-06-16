<?php

namespace App\Cron\Job;

class RideWithGPSAddClubRides extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Add RideWithGPS Club Rides';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$model = new \App\Model\RideWithGPS();

		$routes = $model->getClubRoutes();

		if (\count($routes))
			{
			$rwgpsTable = new \App\Table\RWGPS();
			$rwgpsTable->setWhere(new \PHPFUI\ORM\Condition('RWGPSId', \array_keys($routes), new \PHPFUI\ORM\Operator\In()));
			$rwgpsTable->update(['club' => 1]);

			$rwgpsTable->setWhere(new \PHPFUI\ORM\Condition('club', 1));
			$rwgpsTable->addSelect('RWGPSId');

			foreach ($rwgpsTable->getArrayCursor() as $row)
				{
				unset($routes[(int)$row['RWGPSId']]);
				}

			if (! \count($routes))
				{
				return;
				}

			$rwgpsTable->setWhere();
			$newRows = [];

			foreach ($routes as $ride)
				{
				$record = new \App\Record\RWGPS();
				$model->updateFromData($record, $ride);
				$record->club = 1;
				$record->clean();
				$record->computeElevationGain();
				$newRows[] = $record;
				}

			$rwgpsTable->insertOrIgnore($newRows);
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(20, 45);
		}
	}
