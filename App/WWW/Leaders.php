<?php

namespace App\WWW;

class Leaders extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \PHPFUI\Button $backButton;

	private readonly \App\Table\Setting $settingTable;

	private readonly \App\View\Leader $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Leader($this->page);
		$this->settingTable = new \App\Table\Setting();
		$this->backButton = new \PHPFUI\Button('Ride Leader Configuration', '/Leaders/configure');
		$this->backButton->addClass('hollow');
		}

	public function accidents() : void
		{
		if ($this->page->addHeader('Accidents Reported'))
			{
			$view = new \App\View\Ride\Accidents($this->page);
			$rideTable = new \App\Table\Ride();
			$rideTable->setWhere(new \PHPFUI\ORM\Condition('accident', 1));
			$this->page->addPageContent($view->list($rideTable));
			}
		}

	public function addLocation() : void
		{
		if ($this->page->addHeader('Add Start Location'))
			{
			$view = new \App\View\StartLocation($this->page);

			if (! $view->checkForAdd())
				{
				$startLocation = new \App\Record\StartLocation();
				$this->page->addPageContent($view->edit($startLocation));
				}
			}
		}

	public function allUnreported() : void
		{
		if ($this->page->addHeader('All Unreported Leads'))
			{
			$rides = \App\Table\Ride::unreportedRides();
			$view = new \App\View\Rides($this->page);
			$this->page->addPageContent($view->schedule($rides, 'There are no unreported leads (REALLY?)'));
			}
		}

	public function apply() : void
		{
		if ($this->page->addHeader($title = 'Become A Ride Leader'))
			{
			$member = \App\Model\Session::signedInMemberRecord();

			if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
				{
				$member->pendingLeader = 1;
				$member->update();
				$email = new \App\Tools\EMail();
				$email->setFromMember($member->toArray());
				$email->setSubject('Request to become a Ride Leader');
				$body = "A member has requested to become a ride leader.\n\nYou can approve them here: " . $this->settingTable->value('homePage') . '/Leaders/pending';
				$email->setBody($body);
				$memberPicker = new \App\Model\MemberPicker('Rides Chair');
				$email->addToMember($memberPicker->getMember());
				$email->send();
				\App\Model\Session::setFlash('success', 'Your ride leader application as been submitted. You should hear back soon.');
				$this->page->redirect();
				}
			elseif (! $member->pendingLeader)
				{
				$content = new \App\View\Content($this->page);
				$this->page->addPageContent($content->getDisplayCategoryHTML($title));
				$form = new \PHPFUI\Form($this->page);
				$form->add(new \PHPFUI\Submit('Apply To Become A Ride Leader'));
				$this->page->addPageContent($form);
				}
			else
				{
				$callout = new \PHPFUI\Callout();
				$callout->add('Your application is pending.');
				$this->page->addPageContent($callout);
				}
			}
		}

	public function assistantLeads(int $year = 0) : void
		{
		if ($this->page->addHeader('My Assistant Leads'))
			{
			$this->showRidesForAssistant(\App\Model\Session::signedInMemberRecord(), $year);
			}
		}

	public function assistantTypes() : void
		{
		if ($this->page->addHeader('Assistant Leader Types'))
			{
			$this->page->addPageContent(new \App\View\Leader\AssistantTypes($this->page));
			}
		}

	public function assists(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		if ($this->page->addHeader('Assistant Leads'))
			{
			if ($member->loaded())
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader($member->fullName()));
				$rides = \App\Table\Ride::pastRidesForAssistant($member, 0);
				$view = new \App\View\Rides($this->page);
				$this->page->addPageContent($view->schedule($rides, 'No leader assists for ' . $member->fullName()));
				}
			}
		}

	public function categories() : void
		{
		if ($this->page->addHeader('Categories'))
			{
			$view = new \App\View\Categories($this->page, $this->backButton);
			$this->page->addPageContent($view->edit());
			}
		}

	public function configure() : void
		{
		if ($this->page->addHeader($header = 'Ride Leader Configuration'))
			{
			$landing = $this->page->mainMenu->getLandingPage($this->page, '/Leaders/configure', $header);

			$this->page->addPageContent($landing);
			}
		}

	public function coordinators() : void
		{
		if ($this->page->addHeader('Ride Coordinators'))
			{
			$view = new \App\View\Coordinators($this->page);
			$this->page->addPageContent($view->getRideCoordinators($this->backButton));
			}
		}

	public function crashReport() : void
		{
		if ($this->page->addHeader('Crash Report'))
			{
			$this->page->addPageContent(\App\View\AccidentReport::output($this->page));
			}
		}

	public function deleteCategory(\App\Record\Category $category = new \App\Record\Category()) : void
		{
		if ($this->page->addHeader('Delete Category'))
			{
			$this->page->addPageContent(new \App\View\CategoryDelete($this->page, $category));
			}
		}

	public function deletePace(\App\Record\Pace $pace = new \App\Record\Pace()) : void
		{
		if ($this->page->addHeader('Delete Pace'))
			{
			$this->page->addPageContent(new \App\View\PaceDelete($this->page, $pace));
			}
		}

	public function downloadWaiver(string $file) : void
		{
		if ($this->page->addHeader('Manage Non Member Waivers'))
			{
			$model = new \App\Model\NonMemberWaivers();
			$model->download($file, '.pdf');

			exit;
			}
		}

	public function email() : void
		{
		if ($this->page->addHeader('Email All Ride Leaders'))
			{
			$this->page->addPageContent(new \App\View\Email\Leaders($this->page));
			}
		}

	public function emailLeader(\App\Record\Member $leader = new \App\Record\Member()) : void
		{
		if ($this->page->addHeader('Email Leader'))
			{
			$this->page->addPageContent(new \App\View\Email\Member($this->page, $leader, 'Ride Leader'));
			}
		}

	public function leaderUpcoming(\App\Record\Member $leader) : void
		{
		if ($this->page->addHeader('Upcoming Rides for Leader'))
			{
			$this->page->addSubHeader($leader->fullName());
			$rideTable = new \App\Table\Ride();
			$rides = $rideTable->futureRidesForMember($leader);
			$scheduleView = new \App\View\Rides($this->page);
			$this->page->addPageContent($scheduleView->schedule($rides, 'This leader has no upcoming rides'));
			}
		}

	public function leaderYear(\App\Record\Member $leader = new \App\Record\Member(), int $year = 0) : void
		{
		if (! $leader->memberId)
			{
			$this->Show();
			}
		elseif ($this->page->addHeader('Rides for Leader'))
			{
			$this->page->addSubHeader($leader->fullName());
			$this->showRidesForLeader($leader, $year);
			}
		}

	public function minorWaiver() : void
		{
		if ($this->page->addHeader('Minor Waiver'))
			{
			$waiver = $this->settingTable->value('MinorWaiverText');

			if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
				{
				$email = new \App\Tools\EMail();
				$email->setFromMember(\App\Model\Session::getSignedInMember());
				$email->addBccMember(\App\Model\Session::getSignedInMember());
				$email->setTo($_POST['email'], $_POST['firstName']);
				$club = $this->settingTable->value('clubName');
				$email->setSubject($club . ' Minor Waiver');
				$email->setBody("Dear {$_POST['firstName']},<br><br>Thanks for attending a {$this->settingTable->value('clubAbbrev')} ride.  Please see the attached minor release waiver.");
				$email->setHtml();
				$_POST['acceptedWaiver'] = \date('Y-m-d H:i:s');
				$waiverReport = new \App\Report\MemberWaiver();
				$waiverReport->generate($_POST, 'In consideration of your being a minor, you have agreed to the following:<br><br>');
				$waiverReport->generateMinorRelease();
				$waiverAttachment = $waiverReport->output('', \Mpdf\Output\Destination::STRING_RETURN);
				$email->addAttachment($waiverAttachment, \str_replace(' ', '_', "{$club} Minor Release Waiver.pdf"));
				$email->send();

				$fileName = "{$_POST['firstName']}_{$_POST['lastName']}_" . \App\Tools\Date::todayString() . '.pdf';
				$fileName = \preg_replace('/[^a-zA-Z0-9\.\-\_()]/', '', \str_replace(' ', '_', $fileName));
				$fileName = PROJECT_ROOT . '/files/nonMemberWaivers/' . $fileName;
				$waiverReport->output($fileName, \Mpdf\Output\Destination::FILE);

				$callout = new \PHPFUI\Callout('success');
				$callout->add("Thanks for signing the {$club} minor waiver.");
				$this->page->addPageContent($callout);
				}
			else
				{
				$view = new \App\View\Member\NonMemberWaiver($this->page);
				$view->addField(new \PHPFUI\Input\Text('guardian', 'Full name of Legal Guardian or Responsible Adult'));
				$this->page->addPageContent($view->sign($waiver));
				}
			}
		}

	public function movePace() : void
		{
		if ($this->page->addHeader('Move Pace'))
			{
			$pace = new \App\View\Pace($this->page);
			$this->page->addPageContent($pace->outputMovePace($this->backButton));
			}
		}

	public function myRides() : void
		{
		if ($this->page->addHeader('My Upcoming Leads'))
			{
			$rides = \App\Table\Ride::futureRidesForMember(\App\Model\Session::signedInMemberRecord());
			$view = new \App\View\Rides($this->page);
			$this->page->addPageContent($view->schedule($rides, 'You have no upcoming leads'));
			}
		}

	public function newLeader() : void
		{
		if ($this->page->addHeader('New Ride Leader Email'))
			{
			$editor = new \App\View\Email\Settings($this->page, 'newLeader', new \App\Model\Email\Leader());
			$editor->addButton($this->backButton);
			$this->page->addPageContent($editor);
			}
		}

	public function newRiderEmail() : void
		{
		if ($this->page->addHeader('New Rider Email'))
			{
			$editor = new \App\View\Email\Settings($this->page, 'newRiderEmail', new \App\Model\Email\Rider());
			$editor->addButton($this->backButton);
			$this->page->addPageContent($editor);
			}
		}

	public function nonMemberWaiver() : void
		{
		if ($this->page->addHeader('Non Member Waiver'))
			{
			$waiver = $this->settingTable->value('NonMemberWaiverText');

			if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
				{
				$email = new \App\Tools\EMail();
				$email->setFromMember(\App\Model\Session::getSignedInMember());
				$email->addBccMember(\App\Model\Session::getSignedInMember());
				$email->setTo($_POST['email'], $_POST['firstName'] . ' ' . $_POST['lastName']);
				$club = $this->settingTable->value('clubName');
				$email->setSubject($club . ' Non Member Waiver');
				$email->setBody("Dear {$_POST['firstName']} {$_POST['lastName']},<br><br>Thanks for attending a {$this->settingTable->value('clubAbbrev')} ride.  Please see the attached non-member waiver.");
				$email->setHtml();
				$_POST['acceptedWaiver'] = \date('Y-m-d H:i:s');
				$waiverReport = new \App\Report\MemberWaiver();
				$waiverReport->generate($_POST, "In consideration of your not being a member of {$club}, you have agreed to the following:<br><br>", $waiver);
				$waiverAttachment = $waiverReport->output('', \Mpdf\Output\Destination::STRING_RETURN);
				$email->addAttachment($waiverAttachment, \str_replace(' ', '_', "{$club} Non Member Waiver.pdf"));
				$email->send();
				$fileName = "{$_POST['firstName']}_{$_POST['lastName']}_" . \App\Tools\Date::todayString() . '.pdf';
				$fileName = \preg_replace('/[^a-zA-Z0-9\.\-\_()]/', '', \str_replace(' ', '_', $fileName));
				$fileName = PROJECT_ROOT . '/files/nonMemberWaivers/' . $fileName;
				$waiverReport->output($fileName, \Mpdf\Output\Destination::FILE);
				$callout = new \PHPFUI\Callout('success');
				$callout->add("Thanks for signing the {$club} non member waiver.");
				$this->page->addPageContent($callout);
				}
			else
				{
				$view = new \App\View\Member\NonMemberWaiver($this->page);
				$this->page->addPageContent($view->sign($waiver));
				}
			}
		}

	public function nonMemberWaivers() : void
		{
		if ($this->page->addHeader('Manage Non Member Waivers'))
			{
			$fileView = new \App\View\Admin\Files($this->page, new \App\Model\NonMemberWaivers());
			$fileView->disableUpload();
			$this->page->addPageContent($fileView->list());
			}
		}

	public function pace(\App\Record\Category $category = new \App\Record\Category()) : void
		{
		if ($this->page->addHeader('Edit Pace'))
			{
			$pace = new \App\View\Pace($this->page);
			$this->page->addPageContent($pace->edit($category));
			}
		}

	public function pastRides(int $year = 0) : void
		{
		if ($this->page->addHeader('My Past Leads'))
			{
			$leader = \App\Model\Session::signedInMemberRecord();
			$this->showRidesForLeader($leader, $year);
			}
		}

	public function pending() : void
		{
		if ($this->page->addHeader('Pending Ride Leaders'))
			{
			$this->page->addPageContent($this->view->pendingLeaders($this->page->getPermissions()));
			}
		}

	public function regroupingPolicy() : void
		{
		if ($this->page->addHeader('Regrouping Policy'))
			{
			$view = new \App\UI\RegroupPolicy($this->page);
			$this->page->addPageContent($view->edit());
			}
		}

	public function report() : void
		{
		if ($this->page->addHeader($title = 'Ride Leader Report'))
			{
			if ((isset($_POST['pdf']) || isset($_POST['csv'])) && \App\Model\Session::checkCSRF())
				{
				$report = new \App\Report\Leader($title);
				$report->generateClassic($_POST);
				$this->page->done();
				}
			else
				{
				$this->page->addPageContent($this->view->getReportRequest());
				}
			}
		}

	public function rideStatus() : void
		{
		if ($this->page->addHeader('Request Ride Status Email'))
			{
			$editor = new \App\View\Email\Settings($this->page, 'requestSta', new \App\Model\Email\Ride());
			$editor->addButton($this->backButton);
			$this->page->addPageContent($editor);
			}
		}

	public function settings() : void
		{
		if ($this->page->addHeader('Ride Settings'))
			{
			$this->page->addPageContent($this->view->getSettings($this->backButton));
			}
		}

	public function show() : void
		{
		if ($this->page->addHeader('Show Ride Leaders'))
			{
			$this->page->addPageContent($this->view->show($_GET, $this->page->getPermissions()->getPermissionId('Ride Leader')));
			}
		}

	public function stats(\App\Record\Member $leader = new \App\Record\Member()) : void
		{
		if ($this->page->addHeader('Ride Leader Stats'))
			{
			$this->page->addPageContent($this->view->getStats($this->page, $leader));
			}
		}

	public function text() : void
		{
		if ($this->page->addHeader('Text All Ride Leaders'))
			{
			$this->page->addPageContent(new \App\View\Text\Leaders($this->page));
			}
		}

	public function unreported() : void
		{
		if ($this->page->addHeader('My Unreported Leads'))
			{
			$rides = \App\Table\Ride::unreportedRidesForMember(\App\Model\Session::signedInMemberId());
			$view = new \App\View\Rides($this->page);
			$this->page->addPageContent($view->schedule($rides, 'You have no unreported leads!'));
			}
		}

	public function waitListEmail() : void
		{
		if ($this->page->addHeader('Wait List Email'))
			{
			$editor = new \App\View\Email\Settings($this->page, 'waitListEmail', new \App\Model\Email\Rider());
			$editor->addButton($this->backButton);
			$this->page->addPageContent($editor);
			}
		}

	private function showRidesForAssistant(\App\Record\Member $leader, int $year) : void
		{
		if (! $year)
			{
			$year = \App\Tools\Date::format('Y');
			}

		$oldest = \App\Table\Ride::oldestRideForAssistant($leader->memberId);
		$latest = \App\Table\Ride::latestRideForAssistant($leader->memberId);

		if ($oldest->empty() || $latest->empty())
			{
			$this->page->addPageContent('<h3>You have no leader assists</h3>');

			return;
			}
		$yearSubNav = new \App\UI\YearSubNav(
			$this->page->getBaseURL(),
			$year,
			(int)\App\Tools\Date::formatString('Y', $oldest['rideDate']),
			(int)\App\Tools\Date::formatString('Y', $latest['rideDate'])
		);
		$this->page->addPageContent($yearSubNav);

		$assistantLeaderTypeView = new \App\View\Leader\AssistantTypes($this->page);

		$this->page->addPageContent($assistantLeaderTypeView->stats($leader, $year));

		$rides = \App\Table\Ride::pastRidesForAssistant($leader, 0, $year);
		$view = new \App\View\Rides($this->page);
		$this->page->addPageContent($view->schedule($rides, 'No leader assists in ' . $year));
		}

	private function showRidesForLeader(\App\Record\Member $leader = new \App\Record\Member(), int $year = 0) : void
		{
		if (! $year)
			{
			$year = \App\Tools\Date::format('Y');
			}
		$oldest = \App\Table\Ride::oldestRideForMember($leader->memberId);
		$latest = \App\Table\Ride::latestRideForMember($leader->memberId);

		if (! $oldest->loaded() || ! $latest->loaded())
			{
			$this->page->addPageContent('<h3>You not led any rides yet</h3>');

			return;
			}
		$yearSubNav = new \App\UI\YearSubNav(
			$this->page->getBaseURL(),
			$year,
			(int)\App\Tools\Date::formatString('Y', $oldest['rideDate']),
			(int)\App\Tools\Date::formatString('Y', $latest['rideDate'])
		);
		$this->page->addPageContent($yearSubNav);

		$rides = \App\Table\Ride::pastRidesForMember($leader, 0, $year);
		$view = new \App\View\Rides($this->page);
		$this->page->addPageContent($view->schedule($rides, 'No rides led in ' . $year));
		}
	}
