<?php

namespace App\Cron\Job;

class RideWithGPSDupes extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Remove RideWithGPS Duplicates';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$dupFields = ['km', 'elevationMeters', 'latitude', 'longitude'];

		$rwgpsTable = new \App\Table\RWGPS();
		$rideTable = new \App\Table\Ride();

		foreach ($dupFields as $field)
			{
			$rwgpsTable->addOrderBy($field);
			}
		$rwgpsTable->addOrderBy('club');	// keep the club version in case of tie
		$rwgpsTable->addOrderBy('lastUpdated');	// pick the most recent

		$last = new \App\Record\RWGPS();

		$dupFields[] = 'csv';
		$dups = [];

		foreach ($rwgpsTable->getRecordCursor() as $rwgps)
			{
			$all = true;

			if (0 == $rwgps->RWGPSId)
				{
				$rwgps->delete();

				continue;
				}

			foreach ($dupFields as $field)
				{
				if ($last->{$field} != $rwgps->{$field} && ! empty($rwgps->{$field}))
					{
					$all = false;

					break;
					}
				}

			if ($all && $last->RWGPSId)
				{
				$rideTable->changeRWGPSId($last->RWGPSId, $rwgps->RWGPSId);
				$dups[] = ['Delete' => $this->getLink($last->RWGPS), 'Keep' => $this->getLink($rwgps->RWGPS)];
				$last->delete();
				}
			$last = clone $rwgps;
			}

		if (\count($dups))
			{
			$settingTable = new \App\Table\Setting();
			$email = new \App\Tools\EMail();
			$email->setSubject($settingTable->value('clubAbbrev') . ' Duplicated RWGPS routes');
			$model = new \App\Model\MemberPicker('RideWithGPS Coordinator');
			$email->setToMember($model->getMember());
			$email->setHtml();
			$body = new \PHPFUI\Container();
			$body->add(new \PHPFUI\SubHeader('The following Ride With GPS routes are duplicates in the club library.'));
			$body->add(new \PHPFUI\Header('They should be deleted.  No rides use them.', 4));
			$table = new \PHPFUI\Table();
			$table->setHeaders(\array_keys($dups[0]));

			foreach ($dups as $row)
				{
				$table->addRow($row);
				}
			$body->add($table);

			$email->setBody($body);
			$email->send();
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(4, 45);
		}

	private function getLink(\App\Record\RWGPS $rwgps) : \PHPFUI\Link
		{
		return new \PHPFUI\Link($rwgps->routeLink(), $rwgps->title);
		}
	}
