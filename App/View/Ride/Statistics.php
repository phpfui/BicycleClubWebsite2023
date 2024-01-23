<?php

namespace App\View\Ride;

class Statistics
	{
	private \App\Table\RideSignup $rideSignupTable;

	public function __construct(private readonly \App\View\Page $page, private readonly string $title)
		{
		$this->rideSignupTable = new \App\Table\RideSignup();
		$this->rideSignupTable->addSelect(new \PHPFUI\ORM\Literal('count(ride.rideId)'), '# Number Of Club Rides');
		$this->rideSignupTable->addSelect(new \PHPFUI\ORM\Literal('round(sum(ride.mileage))'), '# Club Miles');
		$this->rideSignupTable->addSelect(new \PHPFUI\ORM\Literal('sum(ride.elevation)'), 'Elevation Gained');
		$this->rideSignupTable->addSelect(new \PHPFUI\ORM\Literal('round(avg(ride.averagePace),1)'), 'AVS');
		$memberJoin = new \PHPFUI\ORM\Condition('member.memberId', new \PHPFUI\ORM\Literal('rideSignup.memberId'));
		$this->rideSignupTable->addJoin('member', $memberJoin);
		$rideSignupJoin = new \PHPFUI\ORM\Condition('rideSignup.rideId', new \PHPFUI\ORM\Literal('ride.rideId'));
		$rideSignupJoin->and('ride.rideId', new \PHPFUI\ORM\Literal('rideSignup.rideId'));
		$this->rideSignupTable->addJoin('ride', $rideSignupJoin);
		$where = $this->rideSignupTable->getWhereCondition();
		$where->and('ride.averagePace', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		}

	public function download() : \PHPFUI\Container
		{
		$button = new \PHPFUI\Button('Download ' . $this->title . ' Statistics');
		$rideSearch = new \App\View\Ride\Search($this->page);
		$modal = $rideSearch->getDateRangeModal($button, $this->title . ' Statistics', 'Download');
		$output = new \PHPFUI\Container();
		$output->add($button);

		if (! empty($_GET['start']) && ! empty($_GET['end']))
			{
			$this->rideSignupTable->find($_GET);

			$rides = $this->rideSignupTable->getArrayCursor();
			$input = [];

			if (\count($rides))
				{
				$writer = new \App\Tools\CSV\FileWriter(\str_replace(' ', '_', $this->title . ' Statistics.csv'));
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

	public function getRideSignupTable() : \App\Table\RideSignup
		{
		return $this->rideSignupTable;
		}
	}
