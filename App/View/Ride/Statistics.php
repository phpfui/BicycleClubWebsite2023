<?php

namespace App\View\Ride;

class Statistics
	{
	private \App\Table\RideSignup $rideSignupTable;

	public function __construct(private readonly \App\View\Page $page, private readonly string $title)
		{
		$this->rideSignupTable = new \App\Table\RideSignup();
		$this->rideSignupTable->addSelect(new \PHPFUI\ORM\Literal('count(distinct ride.rideId)'), 'Total Rides');
		$this->rideSignupTable->addSelect(new \PHPFUI\ORM\Literal('round(sum(ride.mileage))'), 'Total Distance Ridden');
		$this->rideSignupTable->addSelect(new \PHPFUI\ORM\Literal('round(avg(ride.mileage))'), 'Average Distance');
		$this->rideSignupTable->addSelect(new \PHPFUI\ORM\Literal('avg(ride.elevation)'), 'Averaged Elevation Gained');
		$this->rideSignupTable->addSelect(new \PHPFUI\ORM\Literal('round(avg(ride.averagePace),1)'), 'Average Speed');
		$memberJoin = new \PHPFUI\ORM\Condition('member.memberId', new \PHPFUI\ORM\Literal('rideSignup.memberId'));
		$this->rideSignupTable->addJoin('member', $memberJoin);
		$rideSignupJoin = new \PHPFUI\ORM\Condition('rideSignup.rideId', new \PHPFUI\ORM\Literal('ride.rideId'));
		$rideSignupJoin->and('ride.rideId', new \PHPFUI\ORM\Literal('rideSignup.rideId'));
		$this->rideSignupTable->addJoin('ride', $rideSignupJoin);
		}

	public function download() : \PHPFUI\Container
		{
		$button = new \PHPFUI\Button('Download ' . $this->title . ' Statistics');
		$rideSearch = new \App\View\Ride\Search($this->page);
		$modal = $rideSearch->getDateRangeModal($button, $this->title . ' Statistics', 'Download');
		$output = new \PHPFUI\Container();
		$output->add($button);

		if (! empty($_GET))
			{
			$this->rideSignupTable->find($_GET);
			$riderCount = new \App\Table\RideSignup();
			$riderCount->find($_GET);
			$riderCount->setSelect(new \PHPFUI\ORM\Literal('count(*)'));
			$riderCount->getWhereCondition()->and(new \PHPFUI\ORM\Condition('member.memberId', new \PHPFUI\ORM\Literal('ride.memberId')));

			$this->rideSignupTable->addSelect($riderCount, 'Number Of Riders');

			$rides = $this->rideSignupTable->getArrayCursor();

			if (\count($rides))
				{
				$writer = new \App\Tools\CSV\FileWriter(\str_replace(' ', '_', $this->title . ' Statistics.csv'));

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

	public function getRideSignupTable() : \App\Table\RideSignup
		{
		return $this->rideSignupTable;
		}
	}
