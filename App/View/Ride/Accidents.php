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

		$rideTable->addJoin('member');
		$rideTable->addSelect('member.firstName');
		$rideTable->addSelect('member.lastName');

		$rideTable->addJoin('startLocation');
		$rideTable->addSelect('startLocation.name');
		$rideTable->addSelect('startLocation.link');

		$rideTable->addJoin('pace');
		$rideTable->addSelect('pace.pace');

		$rideTable->addJoin('rideSignup');
		$rideTable->addSelect(new \PHPFUI\ORM\Literal('count(rideSignup.status)'), 'numRiders');
		$rideTable->addGroupBy('ride.rideId');
		$rideTable->getWhereCondition()->and('rideSignup.attended', \App\Enum\RideSignup\Attended::CONFIRMED);

		$headers = ['rideDate' => 'Ride Date', 'pace' => 'Category', 'mileage' => 'Mileage',
			'elevation' => 'Elevation', 'numRiders' => '# Riders', 'lastName' => 'Leader'];

		$view = new \App\UI\ContinuousScrollTable($this->page, $rideTable);

		$view->addCustomColumn('lastName', static fn (array $ride) : string => \PHPFUI\TextHelper::unhtmlentities($ride['firstName'] . ' ' . $ride['lastName']));

		$view->addCustomColumn('rideDate', static fn (array $ride) : \PHPFUI\Link => new \PHPFUI\Link("/Rides/signedup/{$ride['rideId']}", $ride['rideDate'], false)->addAttribute('target', '_blank'));

		$view->setHeaders($headers)->setSortableColumns(\array_keys($headers))->setSearchColumns($headers);

		return $view;
		}
	}
