<?php

namespace App\View\Ride;

class Statistics
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function download() : \PHPFUI\Container
		{
		$button = new \PHPFUI\Button($name = 'Download Rider Statistics');
		$rideSearch = new \App\View\Ride\Search($this->page);
		$modal = $rideSearch->getDateRangeModal($button, $name);
		$output = new \PHPFUI\Container();
		$output->add($button);

		if (! empty($_GET['start']) && ! empty($_GET['end']))
			{
			$rideTable = new \App\Table\Ride();
			$rideTable->find($_GET);
			$rideTable->addSelect('member.firstName', 'First Name');
			$rideTable->addSelect('member.lastName', 'Last Name');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('count(ride.rideId)'), '# Number Of Club Rides');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('round(sum(ride.mileage))'), '# Club Miles');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('sum(ride.elevation)'), 'Elevation Gained');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('round(avg(ride.averagePace),1)'), 'AVS');
			$rideTable->addJoin('member');
			$rideSignupJoin = new \PHPFUI\ORM\Condition('rideSignup.rideId', new \PHPFUI\ORM\Literal('ride.rideId'));
			$rideSignupJoin->and('rideSignup.memberId', new \PHPFUI\ORM\Literal('member.memberId'));
			$rideTable->addJoin('rideSignup', $rideSignupJoin);
			$rideTable->setOrderBy('member.lastName');
			$rideTable->addOrderBy('member.firstName');
			$rideTable->addGroupBy('member.lastName');
			$rideTable->addGroupBy('member.firstName');

			$rides = $rideTable->getArrayCursor();

			if (\count($rides))
				{
				$writer = new \App\Tools\CSVWriter('RidersStatistics.csv');
				$writer->addHeaderRow();

				foreach ($rides as $rider)
					{
					$writer->outputRow($rider);
					}
				}
			}
		else
			{
			$modal->showOnPageLoad();
			}

		return $output;
		}
	}
