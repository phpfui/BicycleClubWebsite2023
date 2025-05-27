<?php

namespace App\WWW\Rides;

class My extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private \App\View\Rides $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Rides($this->page);
		}

	public function category() : void
		{
		if ($this->page->addHeader('Rides In My Category'))
			{
			$this->page->addPageContent($this->view->schedule(\App\Table\Ride::getMyCategoryRides(\App\Model\Session::signedInMemberRecord())));
			}
		}

	public function past(int $year = 0, int $month = 0) : void
		{
		$today = \App\Tools\Date::today();
		$year = $year ?: \App\Tools\Date::year($today);
		$month = $month ?: \App\Tools\Date::month($today);

		if ($this->page->addHeader('My Past Rides'))
			{
			$status = \App\Enum\RideSignup\Attended::from(\min(2, \max((int)($_GET['s'] ?? 0), 0)));
			$noRides = 'You have no status of <b>' . $status->name() . '</b> for this month';
			$newestRide = \App\Table\Ride::getMyNewest();
			$oldestRide = \App\Table\Ride::getMyOldest();

			if ($newestRide->loaded() && $oldestRide->loaded())
				{
				$firstYear = (int)$oldestRide->rideDate;
				$newestYear = (int)$newestRide->rideDate;
				$year = \min($newestYear, $year);

				$fieldSet = new \PHPFUI\FieldSet('Rider Status');
				$statusInput = new \PHPFUI\Input\RadioGroupEnum('status', '', $status);
				$this->page->addJavaScript('$(\'input[name="status"]\').change(function(){window.location.assign(window.location.pathname+"?s="+$(\'input[name="status"]:checked\').val());});');
				$fieldSet->add($statusInput);
				$this->page->addPageContent($fieldSet);

				$yearMonthNav = new \App\UI\YearMonthSubNav($this->page->getBaseURL(), $year, $month, $firstYear, $newestYear);
				$this->page->addPageContent($yearMonthNav);

				if ($month && $year)
					{
					$start = \App\Tools\Date::makeString($year, $month, 1);

					if (++$month > 12)
						{
						++$year;
						$month = 1;
						}
					$end = \App\Tools\Date::toString(\App\Tools\Date::make($year, $month, 1) - 1);
					$rideTable = new \App\Table\Ride();
					$this->page->addPageContent($this->view->schedule($rideTable->getMyDateRange($start, $end, $status), $noRides));
					}
				}
			else
				{
				$this->page->addPageContent($noRides);
				}
			}
		}

	public function pending() : void
		{
		if ($this->page->addHeader('My Pending Rides'))
			{
			$rideTable = new \App\Table\Ride();
			$this->page->addPageContent($this->view->schedule($rideTable->getMyPendingRides(\App\Model\Session::signedInMemberRecord()), 'You have no pending rides'));
			}
		}

	public function statistics() : void
		{
		if ($this->page->addHeader('My Ride Statistics'))
			{
			$rideSignupTable = new \App\Table\RideSignup();
			$rideSignupTable->addJoin('member');
			$rideSignupTable->addJoin('ride');
			$rideSignupTable->addOrderBy('member.lastName');
			$rideSignupTable->addOrderBy('member.firstName');
			$rideSignupTable->addOrderBy('ride.rideDate');
			$condition = new \PHPFUI\ORM\Condition('member.memberId', \App\Model\Session::signedInMemberId());
			$condition->and('ride.rideId', null, new \PHPFUI\ORM\Operator\IsNotNull());
			$condition->and('ride.rideDate', '2023-01-01', new \PHPFUI\ORM\Operator\GreaterThanEqual());
			$rideSignupTable->addSelect('member.firstName');
			$rideSignupTable->addSelect('member.lastName');
			$rideSignupTable->addSelect('rideSignup.*');
			$rideSignupTable->addSelect('ride.*');
			$rideSignupTable->setWhere($condition);
			$writer = new \App\Tools\CSV\FileWriter('riderStats.csv');

			foreach ($rideSignupTable->getArrayCursor() as $row)
				{
				$writer->outputRow($row);
				}
			}
		}
	}
