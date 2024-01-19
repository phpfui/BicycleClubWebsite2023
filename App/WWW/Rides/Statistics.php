<?php

namespace App\WWW\Rides;

class Statistics extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private \App\View\Rides $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Rides($this->page);
		}

	public function cuesheets() : void
		{
		if ($this->page->addHeader('Cue Sheet Statistics'))
			{
			$view = new \App\View\Ride\Statistics($this->page, 'Cue Sheet');
			$rideSignupTable = $view->getRideSignupTable();
			$rideSignupTable->addSelect('cueSheet.name', 'Name');
			$rideSignupTable->addSelect('startLocation.name', 'Start Location');
			$rideSignupTable->addSelect('startLocation.link', 'Link');
			$cueSheetJoin = new \PHPFUI\ORM\Condition('cueSheet.cueSheetId', new \PHPFUI\ORM\Literal('ride.cueSheetId'));
			$rideSignupTable->addJoin('cueSheet', $cueSheetJoin);
			$startLocationJoin = new \PHPFUI\ORM\Condition('startLocation.startLocationId', new \PHPFUI\ORM\Literal('ride.startLocationId'));
			$rideSignupTable->addJoin('startLocation', $startLocationJoin);
			$rideSignupTable->setGroupBy('cueSheet.cueSheetId');
			$rideSignupTable->setOrderBy('cueSheet.name');
			$this->page->addPageContent($view->download());
			}
		}

	public function landingPage() : void
		{
		$this->page->landingPage('Ride Statistics');
		}

	public function leaders() : void
		{
		if ($this->page->addHeader('Ride Leader Statistics'))
			{
			$view = new \App\View\Ride\Statistics($this->page, 'Leader');

			$rideSignupTable = $view->getRideSignupTable();
			$rideSignupTable->addSelect('member.firstName', 'First Name');
			$rideSignupTable->addSelect('member.lastName', 'Last Name');
			$rideSignupTable->addSelect('membership.joined', 'Joined');
			$membershipJoin = new \PHPFUI\ORM\Condition('member.membershipId', new \PHPFUI\ORM\Literal('membership.membershipId'));
			$rideSignupTable->addJoin('membership', $membershipJoin);
			$rideSignupTable->setOrderBy('member.lastName');
			$rideSignupTable->addOrderBy('member.firstName');
			$rideSignupTable->addGroupBy('member.memberId');
			$where = $rideSignupTable->getWhereCondition();
			$where->and('ride.memberId', new \PHPFUI\ORM\Literal('member.memberId'));

			$this->page->addPageContent($view->download());
			}
		}

	public function ride(int $year = 0) : void
		{
		if (! $year)
			{
			$year = \App\Tools\Date::format('Y');
			}

		if ($this->page->addHeader('Ride Statistics'))
			{
			$oldest = \App\Table\Ride::getOldest();
			$earliest = (int)\App\Tools\Date::formatString('Y', $oldest['rideDate'] ?? \App\Tools\Date::todayString());
			$subnav = new \App\UI\YearSubNav('/Rides/Statistics/ride', $year, $earliest);
			$this->page->addPageContent($subnav);
			$this->page->addPageContent($this->view->stats($year));
			}
		}

	public function riders() : void
		{
		if ($this->page->addHeader('Rider Statistics'))
			{
			$view = new \App\View\Ride\Statistics($this->page, 'Rider');

			$rideSignupTable = $view->getRideSignupTable();
			$rideSignupTable->addSelect('member.firstName', 'First Name');
			$rideSignupTable->addSelect('member.lastName', 'Last Name');
			$rideSignupTable->addSelect('membership.joined', 'Joined');
			$membershipJoin = new \PHPFUI\ORM\Condition('member.membershipId', new \PHPFUI\ORM\Literal('membership.membershipId'));
			$rideSignupTable->addJoin('membership', $membershipJoin);
			$rideSignupTable->setOrderBy('member.lastName');
			$rideSignupTable->addOrderBy('member.firstName');
			$rideSignupTable->addGroupBy('member.memberId');

			$this->page->addPageContent($view->download());
			}
		}

	public function rwgps() : void
		{
		if ($this->page->addHeader('RWGPS Statistics'))
			{
			$view = new \App\View\Ride\Statistics($this->page, 'RWGPS');
			$rideSignupTable = $view->getRideSignupTable();
			$rideSignupTable->addSelect('RWGPS.title', 'RWGPS Title');
			$rideSignupTable->addSelect('RWGPS.RWGPSId', 'RWGPS ID');
			$rideSignupTable->addSelect('RWGPS.miles', 'Miles');
			$rideSignupTable->addSelect('RWGPS.elevationFeet', 'Elevation Feet');
			$rideSignupTable->addSelect('RWGPS.feetPerMile', 'Feet / Mile');
			$rideSignupTable->addSelect('RWGPS.km', 'Km');
			$rideSignupTable->addSelect('RWGPS.elevationMeters', 'Elevation Meters');
			$rideSignupTable->addSelect('RWGPS.metersPerKm', 'Meters / Km');
			$rideSignupTable->addSelect('RWGPS.percentPaved', 'Percent Paved');
			$rwgpsJoin = new \PHPFUI\ORM\Condition('RWGPS.RWGPSId', new \PHPFUI\ORM\Literal('ride.RWGPSId'));
			$rideSignupTable->addJoin('RWGPS', $rwgpsJoin);
			$rideSignupTable->setGroupBy('RWGPS.RWGPSId');
			$rideSignupTable->setOrderBy('RWGPS.title');

			$this->page->addPageContent($view->download());
			}
		}

	public function startLocations() : void
		{
		if ($this->page->addHeader('Start Location Statistics'))
			{
			$view = new \App\View\Ride\Statistics($this->page, 'Start Location');
			$rideSignupTable = $view->getRideSignupTable();
			$rideSignupTable->addSelect('startLocation.name', 'Name');
			$rideSignupTable->addSelect('startLocation.link', 'Link');
			$startLocationJoin = new \PHPFUI\ORM\Condition('startLocation.startLocationId', new \PHPFUI\ORM\Literal('ride.startLocationId'));
			$rideSignupTable->addJoin('startLocation', $startLocationJoin);
			$rideSignupTable->setGroupBy('startLocation.startLocationId');
			$rideSignupTable->setOrderBy('startLocation.name');
			$this->page->addPageContent($view->download());
			}
		}
	}
