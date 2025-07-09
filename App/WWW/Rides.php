<?php

namespace App\WWW;

class Rides extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Model\Ride $model;

	private readonly \App\Model\SMS $smsModel;

	private \App\View\Rides $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Rides($this->page);
		$this->model = new \App\Model\Ride();
		$this->smsModel = new \App\Model\SMS();
		}

	public function addByCueSheet() : void
		{
		if ($this->page->addHeader('Add Ride To Schedule'))
			{
			$view = new \App\View\Ride\Editor($this->page);
			$this->page->addPageContent($view->addByCueSheet());
			}
		}

	public function addByRWGPS() : void
		{
		if ($this->page->addHeader('Add RWGPS Ride'))
			{
			$view = new \App\View\Ride\Editor($this->page);
			$this->page->addPageContent($view->addByRWGPS());
			}
		}

	public function allPending() : void
		{
		if ($this->page->addHeader('Approve All Rides'))
			{
			$rideTable = new \App\Table\Ride();
			$rideTable->setWhere(new \PHPFUI\ORM\Condition('pending', 1));
			$rideTable->addOrderBy('rideDate');
			$rideTable->addOrderBy('mileage');
			$this->page->addPageContent($this->view->approvingRides()->schedule($rideTable->getRecordCursor(), 'There are no pending rides'));
			}
		}

	public function approve(\App\Record\Ride $ride = new \App\Record\Ride()) : void
		{
		if ($this->page->addHeader('Ride Approved', 'Approve Rides'))
			{
			if ($ride->loaded() && $ride->pending)
				{
				$this->page->addPageContent(new \PHPFUI\Header($ride->title, 5));
				$ride->dateAdded = \date('Y-m-d H:i:s');
				$ride->pending = 0;
				$ride->update();
				$model = new \App\Model\Ride();
				$model->emailRideApproved($ride);
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Ride Not Found'));
				}
			$this->page->redirect('/Rides/pending', timeout:2);
			}
		}

	public function attendance(\App\Record\Member $member = new \App\Record\Member(), int $year = 0) : void
		{
		if (! $member->loaded() || ! $this->page->isAuthorized('Ride Attendance'))
			{
			$member = \App\Model\Session::signedInMemberRecord();
			}

		if ($this->page->addHeader('Ride Attendance'))
			{
			if ($member->loaded())
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader($member->fullName()));
				}
			$view = new \App\View\RiderHistory($this->page);
			$this->page->addPageContent($view->history($member, $year));
			}
		}

	public function calendar(\App\Record\Ride $ride) : void
		{
		if (! $this->page->isAuthorized('Signed Up Riders'))
			{
			$this->page->redirect('/Home');
			}

		$rideModel = new \App\Model\Ride();
		$calendar = $rideModel->getCalendarObject($ride);

		if ($calendar)
			{
			$filename = 'ride_' . $ride->rideDate . '_' . $ride->rideId . '.ics';
			$file = $calendar->export();
			\header('Content-Type: text/calendar');
			\header('Content-Length: ' . \strlen($file));
			\header('Content-Disposition: inline; filename="' . $filename . '"');
			\header('Cache-Control: private, max-age=0, must-revalidate');
			\header('Pragma: public');
			echo $file;

			exit;
			}
		}

	public function clone(\App\Record\Ride $ride = new \App\Record\Ride()) : void
		{
		if ($this->page->addHeader('Clone As Leader') && $ride->canClone())
			{
			$view = new \App\View\Ride\Editor($this->page);
			$view->setRWGPSRoutes($ride);
			$ride->rideId = 0;
			$ride->memberId = \App\Model\Session::signedInMemberId();
			$this->page->addPageContent($view->edit($ride));
			}
		else
			{
			$this->page->redirect('/Rides/memberSchedule');
			}
		}

	public function confirm(\App\Record\Ride $ride = new \App\Record\Ride()) : void
		{
		$allow = $ride->loaded() && $this->model->isLeaderOrAssistant($ride);

		if ($ride->loaded() && $this->page->addHeader('Confirm Riders', '', $allow))
			{
			if ($ride->rideDate <= \App\Tools\Date::todayString() && ($allow || $this->page->isAuthorized('Confirm Riders On Any Ride')))
				{
				$this->page->addPageContent($this->view->confirmRiders($ride));
				}
			else
				{
				$this->page->notAuthorized();
				}
			}
		else
			{
			$this->page->redirect('/Rides/memberSchedule');
			}
		}

	public function csv() : void
		{
		if ($this->page->addHeader('Download Rides.csv'))
			{
			$view = new \App\View\Ride\Search($this->page);
			$this->page->addPageContent($view->csvSearch());
			}
		}

	public function cueSheet(\App\Record\CueSheet $cuesheet = new \App\Record\CueSheet()) : void
		{
		if ($this->page->addHeader('Rides for Cue Sheet'))
			{
			if ($cuesheet->loaded())
				{
				$this->page->addPageContent('<h1>Rides for Cue Sheet</h1>');
				$this->page->addPageContent("<h3>{$cuesheet->name}</h3>");
				$rideTable = new \App\Table\Ride();
				$rideTable->setRidesForCueSheetCursor($cuesheet);
				$this->page->addPageContent($this->view->schedule($rideTable->getRecordCursor()));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Cue Sheet not found'));
				}
			}
		}

	public function delete(\App\Record\Ride $ride = new \App\Record\Ride()) : void
		{
		if ($ride->empty())
			{
			$this->page->addPageContent('<h3>The ride has already been deleted</h3>');
			}
		else
			{
			$view = new \App\View\Rides($this->page);

			if ($view->canDelete($ride))
				{
				$this->page->addPageContent('<h3>The following ride has been deleted</h3>');
				$this->page->addPageContent($view->getRideInfo($ride));
				$ride->delete();
				}
			else
				{
				$this->page->addPageContent("<h3>You can't delete this ride.</h3>");
				$this->page->addPageContent($view->getRideInfo($ride));
				}
			}
		}

	public function edit(\App\Record\Ride $ride = new \App\Record\Ride()) : void
		{
		$afterRide = $ride->rideDate && $ride->rideDate < \App\Tools\Date::todayString();
		$canAddRide = \App\Model\Ride::canAddRide($this->page->getPermissions());

		if (! $ride->memberId && $canAddRide && ! $afterRide)
			{
			$ride->memberId = \App\Model\Session::signedInMemberId();
			}
		$myride = $ride->memberId == \App\Model\Session::signedInMemberId();

		if (! $ride->loaded())
			{
			$this->page->addHeader('Add A Ride', override:$canAddRide);
			}
		elseif ($afterRide)
			{
			$this->page->addHeader('Update Ride Status', override:$myride);
			}
		else
			{
			$this->page->addHeader('Edit Ride', override:$myride);
			}
		$view = new \App\View\Ride\Editor($this->page);
		$RWGPSId = (int)($_GET['RWGPSId'] ?? 0);

		if ($RWGPSId)
			{
			$view->addRWGPSRoute($RWGPSId);
			}
		$this->page->addPageContent($view->edit($ride));
		}

	public function emailRide(\App\Record\Ride $ride = new \App\Record\Ride()) : void
		{
		if ($ride->loaded() && $this->page->addHeader('Email All Signed Up Riders'))
			{
			$this->page->addPageContent(new \App\View\Email\Ride($this->page, $ride));
			}
		else
			{
			$this->page->redirect('/Rides/memberSchedule');
			}
		}

	public function forLocation(\App\Record\StartLocation $startLocation = new \App\Record\StartLocation()) : void
		{
		if ($this->page->addHeader('Rides For Location'))
			{
			if (! $startLocation->empty())
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader($startLocation->name));
				$rideTable = new \App\Table\Ride();
				$rideTable->setRidesForLocationCursor($startLocation);
				$this->page->addPageContent($this->view->schedule($rideTable->getRecordCursor(), 'No rides have been lead from this location'));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Not Found'));
				}
			}
		}

	public function memberSchedule() : void
		{
		if ($this->page->addHeader('Ride Schedule'))
			{
			$content = new \App\View\Content($this->page);
			$this->page->addPageContent($content->getDisplayCategoryHTML('Ride Schedule'));
			$categories = [];

			if ($this->page->isAuthorized('Ride Schedule Filter'))
				{
				$categories = \App\Table\MemberCategory::getRideCategoriesForMember(\App\Model\Session::signedInMemberId());
				$this->page->addPageContent($this->view->categorySelector($categories));
				}
			$this->page->addPageContent($this->view->schedule(\App\Table\Ride::upcomingRides()));
			}
		}

	public function optOut(\App\Record\Ride $ride = new \App\Record\Ride()) : void
		{
		if ($ride->loaded())
			{
			$this->view = new \App\View\Rides($this->page);

			if (! $ride->memberId)
				{
				$this->page->addPageContent('<h2>You have opted out of this ride</h2>');
				$this->page->addPageContent($this->view->getRideInfo($ride));
				}
			elseif ($ride->memberId == \App\Model\Session::signedInMemberId())
				{
				$this->Edit($ride);
				}
			else
				{
				$this->page->addPageContent('<h2>You are no longer the leader of this ride</h2>');
				$this->page->addPageContent($this->view->getRideInfo($ride));
				}
			}
		else
			{
			$this->page->redirect('/Rides/memberSchedule');
			}
		}

	public function past(int $year = 0, int $month = 0) : void
		{
		$today = \App\Tools\Date::today();
		$year = $year ?: \App\Tools\Date::year($today);
		$month = $month ?: \App\Tools\Date::month($today);

		if ($this->page->addHeader('Past Rides'))
			{
			$oldestRide = \App\Table\Ride::getOldest();

			if (! $oldestRide->empty())
				{
				$firstYear = (int)$oldestRide['rideDate'];

				$yearMonthNav = new \App\UI\YearMonthSubNav($this->page->getBaseURL(), $year, $month, $firstYear);
				$this->page->addPageContent($yearMonthNav);

				if ($month && $year)
					{
					$start = \App\Tools\Date::make($year, $month, 1);

					if (++$month > 12)
						{
						++$year;
						$month = 1;
						}
					$end = \App\Tools\Date::make($year, $month, 1) - 1;
					$this->page->addPageContent($this->view->schedule(\App\Table\Ride::getDateRange($start, $end)));
					}
				}
			else
				{
				$this->page->addPageContent('No rides found');
				}
			}
		}

	public function pending() : void
		{
		if ($this->page->addHeader('Approve Rides'))
			{
			$rideTable = new \App\Table\Ride();
			$rideTable->addJoin('pace');
			$rideTable->addJoin('category', new \PHPFUI\ORM\Condition('category.categoryId', new \PHPFUI\ORM\Field('pace.categoryId')));
			$where = new \PHPFUI\ORM\Condition('pending', 1);
			$where->and('category.coordinatorId', \App\Model\Session::signedInMemberId());
			$rideTable->setWhere($where);
			$rideTable->addOrderBy('rideDate');
			$rideTable->addOrderBy('mileage');
			$this->page->addPageContent($this->view->approvingRides()->schedule($rideTable->getRecordCursor(), 'There are no pending rides'));
			}
		}

	public function rideComments(\App\Record\Ride $ride = new \App\Record\Ride()) : void
		{
		if ($ride->loaded() && $this->page->addHeader('Ride Comments'))
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader($ride->title));
			$rideCommentView = new \App\View\Ride\Comments($this->page, $ride);
			$this->page->addPageContent($rideCommentView->getRideComments());
			}
		else
			{
			$this->page->redirect('/Rides/memberSchedule');
			}
		}

	public function riders(\App\Record\Ride $ride = new \App\Record\Ride()) : void
		{
		if ($ride->loaded() && $this->canPrintSigninSheet($ride))
			{
			$waiver = new \App\Report\RideWaiver();
			$waiver->generateRiders($ride);
			$waiver->Output("Riders-{$ride->rideDate}-{$ride->rideId}.pdf", 'I');
			$this->page->done();
			}
		}

	public function riderWaiver(\App\Record\Ride $ride = new \App\Record\Ride(), \App\Record\Member $member = new \App\Record\Member()) : void
		{
		if ($ride->loaded() && ($this->page->isAuthorized('Download Rider Waiver') || $member->loaded() && $member->memberId == \App\Model\Session::signedInMemberId()))
			{
			$waiver = new \App\Report\RideWaiver();
			$waiver->generateRideSignupWaiver($member, $ride);
			$waiver->output("Ride-{$ride->rideId}-Waiver-{$member->firstName}_{$member->lastName}.pdf", 'D');
			$this->page->done();
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Ride or rider not found'));
			}
		}

	public function schedule() : void
		{
		$this->page->setPublic();
		$this->page->addPageContent(new \PHPFUI\SubHeader('Upcoming Rides'));
		$content = new \App\View\Content($this->page);
		$this->page->addPageContent($content->getDisplayCategoryHTML('Ride Schedule'));
		$limit = (int)$this->page->value('publicRideListLimit');
		$showNoLeader = (int)$this->page->value('NoLeadersOnPublicSchedule');

		$this->page->addPageContent($this->view->schedule(\App\Table\Ride::upcomingRides($limit), showNoLeader:$showNoLeader));
		}

	public function search() : void
		{
		if ($this->page->addHeader('Search Rides'))
			{
			$view = new \App\View\Ride\Search($this->page);
			$this->page->addPageContent($view->list());
			}
		}

	public function signedUp(\App\Record\Ride $ride = new \App\Record\Ride()) : void
		{
		if ($ride->loaded() && $this->page->addHeader('Signed Up Riders'))
			{
			$this->page->addPageContent($this->view->getRideInfo($ride));

			if (\App\Enum\Ride\Status::CANCELLED_FOR_WEATHER == $ride->rideStatus)
				{
				$callout = new \PHPFUI\Callout('alert');
				$callout->add($ride->rideStatus->name());
				$this->page->addPageContent($callout);
				}
			$editSignups = $this->canEditRideSignups($ride);
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$signup = null;

			if ($ride->rideDate >= \App\Tools\Date::todayString() && $this->page->isAuthorized('Ride Sign Up') && \App\Enum\Ride\Status::NOT_YET == $ride->rideStatus)
				{
				$signup = new \PHPFUI\Button('Sign Up For This Ride');
				$signup->addClass('success');
				$modal = new \PHPFUI\Reveal($this->page, $signup);
				$modal->addClass('large');
				$modal->add('<h2>Ride Sign Up</h2>');
				$view = new \App\View\Ride\Signup($this->page, $ride);
				$form = $view->getForm();
				$submit = new \PHPFUI\Submit();
				$bg = new \PHPFUI\ButtonGroup();
				$bg->addButton($submit);
				$close = new \PHPFUI\Cancel('Cancel');
				$close->addClass('hollow')->addClass('alert');
				$bg->addButton($close);
				$form->add($bg);
				$modal->add($form);
				$buttonGroup->addButton($signup);
				}
			$this->page->addPageContent($this->view->getSignedUpRidersView($ride, $editSignups, $this->canSeeRiderComments($ride), $signup));

			if ($ride->rideDate <= \App\Tools\Date::todayString() && ($this->model->isLeaderOrAssistant($ride) ||
					$this->page->isAuthorized('Confirm Riders On Any Ride')))
				{
				$confirm = new \PHPFUI\Button('Confirm Riders', '/Rides/confirm/' . $ride->rideId);
				$buttonGroup->addButton($confirm);
				}

			if ($this->canPrintSigninSheet($ride))
				{
				$print = new \PHPFUI\DropDownButton('Print Sign In');
				$print->addLink('/Rides/riders/' . $ride->rideId, 'Riders Only');
				$print->addLink('/Rides/waiver/' . $ride->rideId, 'With Waiver');
				$print->addClass('info');
				$buttonGroup->addButton($print);
				}

			if ($this->page->isAuthorized('Email All Signed Up Riders'))
				{
				$emailAll = new \PHPFUI\Button('Email All Riders', '/Rides/emailRide/' . $ride->rideId);
				$emailAll->addClass('warning');
				$buttonGroup->addButton($emailAll);
				}

			if ($this->page->isAuthorized('Text All Signed Up Riders') && $this->smsModel->enabled() && $ride->rideDate == \App\Tools\Date::todayString())
				{
				$textAll = new \PHPFUI\Button('Text All Riders', '/Rides/textRide/' . $ride->rideId);
				$textAll->addClass('warning');
				$buttonGroup->addButton($textAll);
				}

			$rideSignup = new \App\Record\RideSignup(['memberId' => \App\Model\Session::signedInMemberId(), 'rideId' => $ride->rideId]);

			if ($rideSignup->signedUpTime)
				{
				$addToCalendarButton = new \PHPFUI\Button('Add To Calendar');
				$addToCalendarButton->addAttribute('onclick', 'window.location.href="/Rides/calendar/' . $ride->rideId . '"');
				$addToCalendarButton->addClass('secondary');
				$buttonGroup->addButton($addToCalendarButton);
				}

			if ($this->page->isAuthorized('Clone As Leader') && $ride->canClone())
				{
				$cloneButton = new \PHPFUI\Button('Clone As Leader', '/Rides/clone/' . $ride->rideId);
				$dollar = '$';
				$elementId = $cloneButton->getId();
				$cloneButton->setAttribute('onclick', "{$dollar}(\"#{$elementId}\").toggleClass(\"disabled\")");
				$buttonGroup->addButton($cloneButton);
				}

			if ($ride->memberId == \App\Model\Session::signedInMemberId())
				{
				$editButton = new \PHPFUI\Button('Edit Ride', '/Rides/edit/' . $ride->rideId)->addClass('success');
				$buttonGroup->addButton($editButton);
				}

			$this->page->addPageContent($buttonGroup);

			if ($this->page->isAuthorized('Ride Comments'))
				{
				$rideCommentView = new \App\View\Ride\Comments($this->page, $ride);
				$this->page->addPageContent($rideCommentView->getRideComments());
				}
			}
		else
			{
			$this->page->redirect('/Rides/memberSchedule');
			}
		}

	public function signUp(\App\Record\Ride $ride = new \App\Record\Ride(), \App\Record\Member $member = new \App\Record\Member()) : void
		{
		$permission = 'Edit Ride Signups';

		if ($ride->loaded() && $this->page->addHeader('Ride Sign Up', $permission, $this->canEditRideSignups($ride)))
			{
			$view = new \App\View\Ride\Signup($this->page, $ride, $member);
			$form = $view->getForm();
			$form->add(new \PHPFUI\Submit());
			$this->page->addPageContent($form);
			}
		else
			{
			$this->page->redirect('/Rides/memberSchedule');
			}
		}

	public function statistics() : void
		{
		if ($this->page->addHeader($header = 'Ride Statistics'))
			{
			$landing = $this->page->mainMenu->getLandingPage($this->page, '/Rides/statistics', $header);
			$this->page->addPageContent($landing);
			}
		}

	public function textRide(\App\Record\Ride $ride = new \App\Record\Ride()) : void
		{
		if ($this->page->addHeader('Text All Signed Up Riders'))
			{
			if ($ride->loaded() && $ride->rideDate == \App\Tools\Date::todayString())
				{
				$view = new \App\View\Text($this->page);
				$this->page->addPageContent($view->textRide($ride));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Ride is not textable'));
				}
			}
		}

	public function waiver(\App\Record\Ride $ride = new \App\Record\Ride(), \App\Record\SigninSheet $signinSheet = new \App\Record\SigninSheet()) : void
		{
		if ($this->canPrintSigninSheet($ride))
			{
			$type = $_GET['type'] ?? '';

			if ('printed' == $type)
				{
				$waiver = new \App\Report\RideWaiver();
				$waiver->generateSignupSheetWaiver($ride);
				$waiver->Output("Waiver-{$ride->rideId}.pdf", 'I');
				$this->page->done();
				}
			elseif ('submitted' == $type)
				{
				$model = new \App\Model\SignInSheet();
				$model->download($signinSheet);
				$this->page->done();
				}
			else
				{
				$signInSheets = $ride->SigninSheetRideChildren;

				if (\count($signInSheets))
					{
					if ($this->page->addHeader('Print Sign In Sheet'))
						{
						$view = new \App\View\SignInSheet($this->page);
						$this->page->addPageContent($view->listSheets($signInSheets, $ride));
						}
					}
				else
					{
					$waiver = new \App\Report\RideWaiver();
					$waiver->generateSignupSheetWaiver($ride);
					$waiver->Output("Waiver-{$ride->rideId}.pdf", 'I');
					$this->page->done();
					}
				}
			}
		else
			{
			$this->page->addHeader('Print Sign In Sheet');
			$this->page->addPageContent('Sign in sheet unavailable');
			}
		}

	private function canEditRideSignups(\App\Record\Ride $ride) : bool
		{
		$editSignups = $this->page->isAuthorized('Edit Ride Signups');
		$editSignups |= ($ride->memberId ?? 0) == \App\Model\Session::signedInMemberId();
		$assistantLeaderTable = new \App\Table\AssistantLeader();
		$condition = new \PHPFUI\ORM\Condition('rideId', $ride->rideId ?? 0);
		$condition->and('memberId', \App\Model\Session::signedInMemberId());
		$assistantLeaderTable->setWhere($condition);
		$editSignups |= \count($assistantLeaderTable);

		return 0 != $editSignups;
		}

	private function canPrintSigninSheet(\App\Record\Ride $ride) : bool
		{
		return $this->page->isAuthorized('Print Sign In Sheet');
		}

	private function canSeeRiderComments(\App\Record\Ride $ride) : bool
		{
		return $ride->memberId == \App\Model\Session::signedInMemberId() || $this->page->isAuthorized('Can See Rider Comments');
		}
	}
