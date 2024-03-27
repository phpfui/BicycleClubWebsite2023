<?php

namespace App\Cron\Job;

class MatchStartLocationCueSheet extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Assign Cue Sheet start location based on start location from rides';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		try
			{
			$rideTable = new \App\Table\Ride();
			$rideTable->addJoin('cueSheet');
			$where = new \PHPFUI\ORM\Condition('ride.cueSheetId', 0, new \PHPFUI\ORM\Operator\GreaterThan());
			$where->and('ride.startLocationId', 0, new \PHPFUI\ORM\Operator\GreaterThan());
			$noCuesheetCondition = new \PHPFUI\ORM\Condition('cueSheet.startLocationId', operator:new \PHPFUI\ORM\Operator\IsNull());
			$noCuesheetCondition->or('cueSheet.startLocationId', 0);
			$where->and($noCuesheetCondition);

			$startLocationIds = [];

			foreach ($rideTable->getRecordCursor() as $ride)
				{
				$startLocationId = $ride->startLocationId;
				$cueSheetId = $ride->cueSheetId;

				if (! isset($startLocationIds[$cueSheetId]))
					{
					$startLocationIds[$cueSheetId] = [];
					}

				if (! isset($startLocationIds[$cueSheetId][$startLocationId]))
					{
					$startLocationIds[$cueSheetId][$startLocationId] = 0;
					}
				++$startLocationIds[$cueSheetId][$startLocationId];
				}

			foreach ($startLocationIds as $cueSheetId => $startLocations)
				{
				$cueSheet = new \App\Record\CueSheet($cueSheetId);
				\arsort($startLocations);
				\reset($startLocations);
				$cueSheet->startLocationId = \key($startLocations);
				$cueSheet->update();
				}
			}
		catch (\Exception $e)
			{
			$this->controller->log_exception($e->getMessage());
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(4, 50);
		}
	}
