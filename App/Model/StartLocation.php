<?php

namespace App\Model;

class StartLocation
	{
	/**
	 * @param array<string,string> $startLocation
	 */
	public function add(array $startLocation) : bool
		{
		$location = new \App\Record\StartLocation();
		$location->setFrom($startLocation);
		$errors = $location->validate();

		if ($errors)
			{
			\App\Model\Session::setFlash('alert', $errors);

			return false;
			}

		$id = $location->insert();
		$author = \App\Model\Session::getSignedInMember();
		$settingTable = new \App\Table\Setting();
		$email = new \App\Tools\EMail();
		$message = "The following start location was added by {$author['firstName']} {$author['lastName']}:<p>";
		$server = $settingTable->value('homePage');
		$message .= "<a href='{$server}/Locations/edit/{$id}'>{$startLocation['name']}</a>";
		$email->setBody($message);
		$email->setHtml();
		$email->setSubject('New start location added');
		$memberPicker = new \App\Model\MemberPicker('Rides Chair');
		$email->addToMember($memberPicker->getMember());
		$email->setFromMember($author);
		$email->send();

		return true;
		}

	public function computeCoordinates(bool $overwriteStartLocations = false, bool $updateRWGPS = false) : void
		{
		foreach ($this->startLocationRWGPSIds() as $startLocationId => $rwgpsRoutes)
			{
			\arsort($rwgpsRoutes);

			$RWGPS = new \App\Record\RWGPS(\array_key_first($rwgpsRoutes));
			$startLocation = new \App\Record\StartLocation($startLocationId);

			if ($overwriteStartLocations || ! $startLocation->latitude || ! $startLocation->longitude)
				{
				$startLocation->latitude = $RWGPS->latitude;
				$startLocation->longitude = $RWGPS->longitude;
				$startLocation->update();
				}

			foreach ($rwgpsRoutes as $RWGPSId => $count)
				{
				$RWGPS = new \App\Record\RWGPS($RWGPSId);

				if ($updateRWGPS || ! $RWGPS->startLocationId)
					{
					$RWGPS->startLocation = $startLocation;
					$RWGPS->update();
					}
				}
			}
		}

	/**
	 * @return array<int,array<int,int>> indexed by startLocationId containing array indexed by RWGPSId containing count of RWGPS routes from that start location
	 */
	public function startLocationRWGPSIds() : array
		{
		$rideTable = new \App\Table\Ride();
		$condition = new \PHPFUI\ORM\Condition('RWGPSId', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('startLocationId', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$rideTable->setWhere($condition);
		$rideTable->addOrderBy('RWGPSId');

		$startLocations = [];

		foreach ($rideTable->getRecordCursor() as $ride)
			{
			if (! \array_key_exists($ride->startLocationId, $startLocations))
				{
				$startLocations[$ride->startLocationId] = [];
				}

			if (! \array_key_exists($ride->RWGPSId, $startLocations[$ride->startLocationId]))
				{
				$startLocations[$ride->startLocationId][$ride->RWGPSId] = 1;
				}
			else
				{
				++$startLocations[$ride->startLocationId][$ride->RWGPSId];
				}
			}

		return $startLocations;
		}
	}
