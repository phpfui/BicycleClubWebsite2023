<?php

namespace App\View;

class RiderHistory
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function history(\App\Record\Member $member, int $year = 0) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$today = \App\Tools\Date::today();
		$year = (int)$year ?: \App\Tools\Date::year($today);

		$noRides = 'You have no online signups';
		$newestRide = \App\Table\RideSignup::getNewest($member);
		$oldestRide = \App\Table\RideSignup::getOldest($member);

		if ($newestRide && $oldestRide)
			{
			$firstYear = (int)$oldestRide['rideDate'];
			$newestYear = (int)$newestRide['rideDate'];

			$url = $this->page->getBaseURL();
			$parts = \explode('/', $url);
			$intCount = 0;

			foreach ($parts as $part)
				{
				if (\is_numeric($part))
					{
					++$intCount;
					}
				}

			if ($intCount < 1)
				{
				$parts[] = $member->memberId;
				$noRides = '';
				}

			if ($intCount < 2)
				{
				$noRides = '';
				}
			$url = \implode('/', $parts);

			$yearMonthNav = new \App\UI\YearSubNav($url, $year, $firstYear, $newestYear);
			$container->add($yearMonthNav);

			if ($year)
				{
				$start = \App\Tools\Date::makeString($year, 1, 1);
				$end = \App\Tools\Date::makeString($year, 12, 31);
				$rideSignupTable = new \App\Table\RideSignup();
				$rides = $rideSignupTable->getRidesForMember($member, $start, $end);

				if (\count($rides))
					{
					$container->add($this->historyTable($rides));
					}
				else
					{
					$container->add($noRides);
					}
				}
			}
		else
			{
			$container->add($noRides);
			}

		return $container;
		}

	private function historyTable(\PHPFUI\ORM\DataObjectCursor $rides) : \PHPFUI\Table
		{
		$table = new \PHPFUI\Table();
		$headers = ['Date', 'Status', 'Registered', 'Ride'];
		$table->setHeaders($headers);

		foreach ($rides as $rideRecord)
			{
			$ride = $rideRecord->toArray();
			$ride['Date'] = \App\Tools\Date::formatString('D M j, Y', $ride['rideDate']);

			if ('1000-01-01 00:00:00' < $ride['signedUpTime'])
				{
				$ride['Registered'] = $ride['signedUpTime'];
				}

			if ($ride['attended'])
				{
				$ride['Status'] = \App\Table\RideSignup::getAttendedStatus()[$ride['attended']] ?? 'Unknown';
				}
			else
				{
				$ride['Status'] = \App\Table\RideSignup::getRiderStatus()[$ride['status']] ?? 'Unknown';
				}
			$ride['Ride'] = new \PHPFUI\Link('/Rides/signedUp/' . $ride['rideId'], $ride['title'], false);
			$table->addRow($ride);
			}

		return $table;
		}
	}
