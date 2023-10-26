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
			$rideTable = $view->getRideTable();
			$rideTable->addSelect('cueSheet.name', 'Name');
			$rideTable->addSelect('startLocation.name', 'Start Location');
			$rideTable->addSelect('startLocation.link', 'Link');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('count(ride.rideId)'), '# Number Of Club Rides');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('round(sum(ride.mileage))'), '# Club Miles');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('sum(ride.elevation)'), 'Elevation Gained');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('round(avg(ride.averagePace),1)'), 'AVS');
			$rideTable->addJoin('cueSheet');
			$rideTable->addJoin('startLocation');
			$rideTable->addJoin('member');
			$rideSignupJoin = new \PHPFUI\ORM\Condition('rideSignup.rideId', new \PHPFUI\ORM\Literal('ride.rideId'));
			$rideSignupJoin->and('rideSignup.memberId', new \PHPFUI\ORM\Literal('member.memberId'));
			$rideTable->addJoin('rideSignup', $rideSignupJoin);
			$rideTable->setGroupBy('cueSheet.cueSheetId');
			$rideTable->setOrderBy('cueSheet.name');
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

			$rideTable = $view->getRideTable();
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
			$rideTable->addGroupBy('member.memberId');

			$this->page->addPageContent($view->download());
			}
		}

	public function rwgps() : void
		{
		if ($this->page->addHeader('RWGPS Statistics'))
			{
			$view = new \App\View\Ride\Statistics($this->page, 'RWGPS');
			$rideTable = $view->getRideTable();
			$rideTable->addSelect('rwgps.title', 'RWGPS Title');
			$rideTable->addSelect('rwgps.RWGPSId', 'RWGPS ID');
			$rideTable->addSelect('rwgps.miles', 'Miles');
			$rideTable->addSelect('rwgps.elevationFeet', 'Elevation Feet');
			$rideTable->addSelect('rwgps.feetPerMile', 'Feet / Mile');
			$rideTable->addSelect('rwgps.km', 'Km');
			$rideTable->addSelect('rwgps.elevationMeters', 'Elevation Meters');
			$rideTable->addSelect('rwgps.metersPerKm', 'Meters / Km');
			$rideTable->addSelect('rwgps.percentPaved', 'Percent Paved');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('count(ride.rideId)'), '# Number Of Club Rides');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('round(sum(ride.mileage))'), '# Club Miles');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('sum(ride.elevation)'), 'Elevation Gained');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('round(avg(ride.averagePace),1)'), 'AVS');
			$rideTable->addJoin('rwgps');
			$rideTable->addJoin('member');
			$rideSignupJoin = new \PHPFUI\ORM\Condition('rideSignup.rideId', new \PHPFUI\ORM\Literal('ride.rideId'));
			$rideSignupJoin->and('rideSignup.memberId', new \PHPFUI\ORM\Literal('member.memberId'));
			$rideTable->addJoin('rideSignup', $rideSignupJoin);
			$rideTable->setGroupBy('rwgps.RWGPSId');
			$rideTable->setOrderBy('rwgps.title');

			$this->page->addPageContent($view->download());
			}
		}

	public function startLocations() : void
		{
		if ($this->page->addHeader('Start Location Statistics'))
			{
			$view = new \App\View\Ride\Statistics($this->page, 'Start Location');
			$rideTable = $view->getRideTable();
			$rideTable->addSelect('startLocation.name', 'Name');
			$rideTable->addSelect('startLocation.link', 'Link');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('count(ride.rideId)'), '# Number Of Club Rides');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('round(sum(ride.mileage))'), '# Club Miles');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('sum(ride.elevation)'), 'Elevation Gained');
			$rideTable->addSelect(new \PHPFUI\ORM\Literal('round(avg(ride.averagePace),1)'), 'AVS');
			$rideTable->addJoin('startLocation');
			$rideTable->addJoin('member');
			$rideSignupJoin = new \PHPFUI\ORM\Condition('rideSignup.rideId', new \PHPFUI\ORM\Literal('ride.rideId'));
			$rideSignupJoin->and('rideSignup.memberId', new \PHPFUI\ORM\Literal('member.memberId'));
			$rideTable->addJoin('rideSignup', $rideSignupJoin);
			$rideTable->setGroupBy('startLocation.startLocationId');
			$rideTable->setOrderBy('startLocation.name');
			$this->page->addPageContent($view->download());
			}
		}
	}
