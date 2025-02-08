<?php

namespace App\View\Ride;

class Accidents
	{

	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function list(\App\Table\Ride $rideTable) : \App\UI\ContinuousScrollTable
		{
		$rideTable->addSelect('ride.*');
		$rideTable->addSelect('member.firstName');
		$rideTable->addSelect('member.lastName');
		$rideTable->addSelect('startLocation.name');
		$rideTable->addSelect('startLocation.link');
		$rideTable->addSelect('startLocation.name');
		$rideTable->addSelect('pace.pace');
		$rideTable->addSelect(new \PHPFUI\ORM\Literal('count(rideSignup.status)'), 'numRiders');
		$rideTable->addGroupBy('ride.rideId');
		$condition = $rideTable->getWhereCondition();
		$condition->and('rideSignup.attended', \App\Enum\RideSignup\Attended::CONFIRMED);

		$rideTable->addJoin('member');
		$rideTable->addJoin('startLocation');
		$rideTable->addJoin('rideSignup');
		$rideTable->addJoin('pace');
		$normalHeaders = [];
		$searchableHeaders = ['rideDate' => 'Ride Date', 'pace' => 'Category', 'mileage' => 'Mileage',
			'elevation' => 'Elevation', 'numRiders' => '# Riders', 'lastName' => 'Leader'];
		$sortableHeaders = $searchableHeaders;

		$view = new \App\UI\ContinuousScrollTable($this->page, $rideTable);

		$view->addCustomColumn('lastName', static function(array $ride)
			{
			return \PHPFUI\TextHelper::unhtmlentities($ride['firstName'] . ' ' . $ride['lastName']);
			});

		$view->addCustomColumn('rideDate', static function(array $ride)
			{
			$name = new \PHPFUI\Link("/Rides/signedup/{$ride['rideId']}", $ride['rideDate'], false);
			$name->addAttribute('target', '_blank');

			return $name;
			});

		$view->setHeaders($sortableHeaders + $searchableHeaders + $normalHeaders)->setSortableColumns(\array_keys($sortableHeaders));
		$view->setSearchColumns($searchableHeaders);

		return $view;
		}
	}
