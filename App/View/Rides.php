<?php

namespace App\View;

class Rides
	{
	private bool $approvingRides = false;

	private readonly \App\Table\CueSheetVersion $cueSheetVersionTable;

	private readonly \App\View\CueSheet $cueSheetView;

	private int $deletePastDays = 0;

	private readonly \App\View\Leader $leader;

	private readonly \App\View\Member $memberView;

	private readonly \App\Table\Pace $paceTable;

	private readonly \App\Model\SMS $smsModel;

	private readonly \App\View\StartLocation $startLocationView;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->leader = new \App\View\Leader($page);
		$this->paceTable = new \App\Table\Pace();
		$this->cueSheetVersionTable = new \App\Table\CueSheetVersion();
		$this->startLocationView = new \App\View\StartLocation($this->page);
		$this->cueSheetView = new \App\View\CueSheet($this->page);
		$this->memberView = new \App\View\Member($this->page);
		$this->smsModel = new \App\Model\SMS();

		$this->deletePastDays = (int)$this->page->value('DeleteRidesPastDays');
		}

	public function approvingRides() : self
		{
		$this->approvingRides = true;

		return $this;
		}

	/**
	 * See if we can delete a ride.
	 *
	 * Normal leaders can't delete past rides.
	 * Anyone with "Delete Past Rides" can delete past rides.
	 * If you are the leader, you can delete your own ride if date
	 * is before opt out window
	 *
	 *
	 * @return bool
	 */
	public function canDelete(\PHPFUI\ORM\DataObject $ride)
		{
		if (! $this->page->isAuthorized('Delete Past Rides') && \App\Tools\Date::fromString($ride->rideDate) - $this->deletePastDays <= \App\Tools\Date::today())
			{
			return false; // can't delete rides today or earlier
			}
		$member = \App\Model\Session::signedInMemberRecord();

		return $ride->memberId == $member->memberId || $this->page->isAuthorized('Delete Ride');
		}

	/**
	 * @param array<int,int> $categories
	 */
	public function categorySelector(array $categories) : \PHPFUI\HTML5Element
		{
		$categoryTable = new \App\Table\Category();

		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->setAttribute('method', 'GET');

		$checkBoxMenu = new \PHPFUI\Input\CheckBoxMenu('c');
		$checkBoxMenu->setJavaScriptCallback('categoryMenu');
		$js = 'function categoryMenu(name,value,active){name="cat-"+name.substring(name.indexOf("[")+1).replace("]","");if(active){$("."+name).show();}else{$("."+name).hide();}};';
		$checkBoxMenu->addAll();

		foreach ($categoryTable->getAllCategories() as $category)
			{
			$menuItem = $checkBoxMenu->addCheckBox($category->category, $categories[$category->categoryId] ?? false, (string)$category->categoryId, $category->categoryId);

			if (empty($categories[$category->categoryId]))
				{
				$js .= '$(".cat-' . $category->categoryId . '").hide();';
				}
			}
		$this->page->addJavaScript($js);

		$form->add(new \PHPFUI\Header('Ride Schedule Category Filter', 5));
		$form->add($checkBoxMenu);
		$form->add('<hr>');

		return $form;
		}

	public function confirmRiders(\App\Record\Ride $ride) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback() && isset($_POST['submit']))
			{
			if (isset($_POST['attended']))
				{
				\PHPFUI\ORM::beginTransaction();

				$rideSignup = new \App\Record\RideSignup();

				foreach ($_POST['attended'] as $key => $value)
					{
					$rideSignup->read(['rideId' => $ride->rideId, 'memberId' => $key]);
					$rideSignup->attended = \App\Enum\RideSignup\Attended::from((int)$value);
					$rideSignup->update();
					}
				\PHPFUI\ORM::commit();
				}
			$this->page->setResponse('Saved');
			}
		else
			{
			if (\App\Model\Session::checkCSRF() && 'Add Rider' == ($_POST['submit'] ?? '') && ! empty($_POST['memberId']))
				{
				$data = ['rideId' => $ride->rideId, 'memberId' => (int)$_POST['memberId']];
				$rideData = new \App\Record\RideSignup($data);

				if ($rideData->loaded())
					{
					$rideData->attended = \App\Enum\RideSignup\Attended::CONFIRMED;
					$rideData->update();
					}
				else
					{
					$rideData->status = \App\Enum\RideSignup\Status::DEFINITELY_RIDING;
					$rideData->comments = '';
					$rideData->attended = \App\Enum\RideSignup\Attended::CONFIRMED;
					$rideData->ride = $ride;
					$rideData->memberId = (int)$_POST['memberId'];
					$rideData->insert();
					}
				$this->page->redirect();

				return $container;
				}
			$rideSignupTable = new \App\Table\RideSignup();
			$riders = $rideSignupTable->getAllSignedUpRiders($ride, false);
			$add = new \PHPFUI\Button('Add Rider');
			$add->addClass('warning');
			$form->saveOnClick($add);
			$modal = new \PHPFUI\Reveal($this->page, $add);
			$modalForm = new \PHPFUI\Form($this->page);
			$modalForm->setAreYouSure(false);
			$fieldSet = new \PHPFUI\FieldSet('Confirmed Rider Name (type first or last name)');
			$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Enter Rider Name to Confirm'), 'memberId');
			$fieldSet->add($memberPicker->getEditControl());
			$modalForm->add($fieldSet);
			$modalForm->add(new \PHPFUI\Submit('Add Rider'));
			$modal->add($modalForm);

			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton($submit);
			$buttonGroup->addButton($add);

			$nonMemberWaiver = new \PHPFUI\Button('Non-Member Waiver', '/Leaders/nonMemberWaiver');
			$nonMemberWaiver->addClass('info');
			$buttonGroup->addButton($nonMemberWaiver);

			$editRide = new \PHPFUI\Button('Edit Ride', '/Rides/edit/' . $ride->rideId);
			$editRide->addClass('success');
			$buttonGroup->addButton($editRide);

			$form->add($buttonGroup);

			foreach ($riders as $rider)
				{
				$row = new \PHPFUI\GridX();
				$row->add("<b>{$rider->firstName} {$rider->lastName}</b>");
				$image = $this->memberView->getImageIcon($rider->toArray());

				if ($image)
					{
					$row->add('&nbsp;');
					$row->add($image);
					}
				$form->add($row);
				$id = $rider->memberId;

				$rideSignup = new \App\Record\RideSignup();
				$rideSignup->setFrom($rider->toArray());
				$attended = $rideSignup->attended;

				if (empty($rider->attended))
					{
					if ($rider->memberId == \App\Model\Session::signedInMemberId())
						{
						$attended = \App\Enum\RideSignup\Attended::CONFIRMED;
						}
					elseif (\App\Enum\RideSignup\Status::DEFINITELY_NOT_RIDING == $rider->status)
						{
						$attended = \App\Enum\RideSignup\Attended::NO_SHOW;
						}
					}
				$radio = new \PHPFUI\Input\RadioGroupEnum("attended[{$id}]", '', $attended);
				$row = new \PHPFUI\GridX();
				$row->add($radio);
				$form->add($row);
				}
			}

		$container->add($form);

		return $container;
		}

	public function getPace(int $paceId) : string
		{
		return $this->paceTable->getPace($paceId);
		}

	public function getRideInfo(\App\Record\Ride $ride) : \PHPFUI\FieldSet
		{
		$view = new \App\View\Ride\Info($this->page);

		return $view->getRideInfo($ride, $this->getRWGPSMenu($ride));
		}

	public function getRWGPSMenu(\PHPFUI\ORM\DataObject $rideObject) : string
		{
		$ride = new \App\Record\Ride($rideObject);
		$routes = $ride->RWGPSChildren;

		if (! \count($routes))
			{
			return '';
			}

		$route = clone $routes->current();
		$menu = new \PHPFUI\DropDownMenu();

		if (1 == \count($routes))
			{
			$rwgpsMenu = new \PHPFUI\MenuItem('RWGPS' . ($route->club ? '*' : ''), $route->routeLink());
			$rwgpsMenu->getLinkObject()->addAttribute('target', '_blank');
			$menu->addMenuItem($rwgpsMenu);
			}
		else
			{
			$rwgpsMenu = new \PHPFUI\DropDownMenu();

			foreach ($routes as $RWGPS)
				{
				$title = $RWGPS->distance();

				if ($RWGPS->club)
					{
					$title .= ' *';
					}
				$rwgpsMenuItem = new \PHPFUI\MenuItem($title, $RWGPS->routeLink());
				$rwgpsMenuItem->getLinkObject()->addAttribute('target', '_blank');
				$rwgpsMenu->addMenuItem($rwgpsMenuItem);
				}
			$rwgpsMenu->computeWidth();
			$menu->addSubMenu(new \PHPFUI\MenuItem('RWGPS', '#'), $rwgpsMenu);
			}

		$link = $route->directionsUrl();

		if ($link)
			{
			$directions = new \PHPFUI\MenuItem('Dir To Start', $link);
			$directions->getLinkObject()->addAttribute('target', '_blank');
			$menu->addMenuItem($directions);
			}

		if (1 == \count($routes))
			{
			$menu->addMenuItem(new \PHPFUI\MenuItem('Cue Sheet', '/RWGPS/cueSheetRide/' . $ride->rideId));
			}
		else
			{
			$cueSheetMenu = new \PHPFUI\DropDownMenu();

			$count = 0;

			foreach ($routes as $RWGPS)
				{
				$title = $RWGPS->distance();
				$cueSheetMenuItem = new \PHPFUI\MenuItem($title, '/RWGPS/cueSheetRide/' . $ride->rideId . '/' . $count);
				++$count;
				$cueSheetMenu->addMenuItem($cueSheetMenuItem);
				}
			$cueSheetMenu->computeWidth();
			$menu->addSubMenu(new \PHPFUI\MenuItem('Cue Sheet', '#'), $cueSheetMenu);
			}

		$menuItem = new \PHPFUI\MenuItem('Stats', '#');
		$this->getStatsReveal($menuItem, $route);
		$menu->addMenuItem($menuItem);

		return "{$menu}";
		}

	public function getSignedUpRidersView(\App\Record\Ride $ride, bool $editSignups, bool $showComments = false, ?\PHPFUI\Button $signup = null) : \PHPFUI\FieldSet
		{
		$settingTable = new \App\Table\Setting();
		$rideLeaderPermission = $settingTable->getStandardPermissionGroup('Ride Leader');
		$model = new \App\Model\RideSignup($ride, \App\Model\Session::signedInMemberRecord());
		$model->notifyWaitList();
		$signupLimit = $model->getRiderSignupLimit();
		$rideSignupTable = new \App\Table\RideSignup();
		$riders = $rideSignupTable->getAllSignedUpRiders($ride);
		$fieldSet = new \PHPFUI\FieldSet('Confirmed Riders');
		$statusArray = $rideSignupTable->getRiderStatus();

		$rideModel = new \App\Model\Ride();
		$isLeader = $rideModel->isLeaderOrAssistant($ride);
		$counts = [];

		foreach ($riders as $rider)
			{
			if (! isset($counts[$rider->status]))
				{
				$counts[$rider->status] = 1;
				}
			else
				{
				++$counts[$rider->status];
				}
			}

		if ($signupLimit && $signupLimit <= ($counts[\App\Enum\RideSignup\Status::DEFINITELY_RIDING->value] ?? 0))
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->addClass('small');
			$callout->add('The ride is full, waitlist only.');
			$fieldSet->add($callout);
			}

		$row = new \PHPFUI\GridX();

		foreach ($counts as $index => $count)
			{
			if (isset($statusArray[$index]))
				{
				$row->add("<strong>{$statusArray[$index]}:</strong> &nbsp; {$count} &nbsp; ");
				}
			}
		$fieldSet->add($row);
		$fieldSet->add('<strong><hr></strong>');
		$emailMember = $this->page->isAuthorized('Email Member');
		$rideAttendance = $this->page->isAuthorized('Ride Attendance');
		$waiver = $this->page->isAuthorized('Download Rider Waiver');
		$textMember = $this->page->isAuthorized('Text Member') && $this->smsModel->enabled();

		$row = new \PHPFUI\GridX();
		$nameColumn = new \PHPFUI\Cell(8 - (int)$isLeader);
		$nameColumn->add('<strong>Rider Name</strong>');
		$row->add($nameColumn);

		if ($isLeader)
			{
			$leaderColumn = new \PHPFUI\Cell(1);
			$leaderColumn->add('<strong>Ldr</strong>');
			$row->add($leaderColumn);
			}

		$editColumn = new \PHPFUI\Cell(4);
		$editColumn->add('<strong>Select An Action</strong>');
		$row->add($editColumn);
		$fieldSet->add($row);

		$isLeader = ($ride->memberId ?? 0) == \App\Model\Session::signedInMemberId();
		$this->page->addJavaScript('function selectRiderContactMethod(v){if(v>"")if(v[0]=="/"||v.startsWith("tel:")){window.location=v}else{window.open(v)}}');

		foreach ($riders as $rider)
			{
			$row = new \PHPFUI\GridX();
			$nameColumn = new \PHPFUI\Cell(8 - (int)$isLeader);

			if (\App\Enum\RideSignup\Attended::SIGNED_UP->value == $rider->attended)
				{
				$status = $statusArray[$rider->status];
				}
			elseif (\App\Enum\RideSignup\Attended::NO_SHOW->value == $rider->attended)
				{
				$status = 'No Show';
				}
			else
				{
				$status = 'Confirmed';
				}

			$private = false;

			if ($rider->showNoRideSignup && $rider->memberId != \App\Model\Session::signedInMemberId() && ! $isLeader)
				{
				$nameColumn->add("<b>Private</b><br>{$status}");
				$row->add($nameColumn);
				$private = true;
				}
			else
				{
				$image = $this->memberView->getImageIcon($rider->toArray());
				$nameColumn->add("<b>{$rider->firstName} {$rider->lastName}</b> {$image}<br>{$status}");
				$row->add($nameColumn);
				}

			if ($isLeader)
				{
				$leaderColumn = new \PHPFUI\Cell(1);
				$userPermission = new \App\Record\UserPermission(['memberId' => $rider->memberId, 'permissionGroup' => $rideLeaderPermission->permissionId]);

				if ($userPermission->loaded() && ! $userPermission->revoked)
					{
					$leaderColumn->add('&check;');
					}
				$row->add($leaderColumn);
				}

			$selectColumn = new \PHPFUI\Cell(4);

			if ($rider->memberId != \App\Model\Session::signedInMemberId())
				{
				$select = new \PHPFUI\Input\Select('action');
				$select->addAttribute('onchange', 'selectRiderContactMethod(this.value)');
				$select->addOption('Choose ...');

				if ($textMember && $rider->allowTexting && ! $private)
					{
					$select->addOption('Text Member', "/Membership/text/{$rider->memberId}");
					}

				if ($editSignups || ! $rider->showNoPhone)
					{
					$select->addOption('Call Cell', 'tel:1-' . $rider->cellPhone);
					}

				if ($emailMember && ! $private)
					{
					$select->addOption('Email Member', "/Membership/email/{$rider->memberId}");
					}

				if ($editSignups || ! $rider->showNoPhone)
					{
					$select->addOption('Emergency: ' . $rider->emergencyContact, 'tel:' . $rider->emergencyPhone);
					}

				if ($waiver || $rider->memberId == \App\Model\Session::signedInMemberId())
					{
					$select->addOption('Download Waiver', "/Rides/riderWaiver/{$ride->rideId}/{$rider->memberId}");
					}

				if ($rideAttendance)
					{
					$select->addOption('Ride Attendance', '/Rides/attendance/' . $rider->memberId);
					}

				if ($editSignups)
					{
					$select->addOption('Edit Signup', "/Rides/signUp/{$ride->rideId}/{$rider->memberId}");
					}
				$selectColumn->add($select);
				}
			elseif ($signup)
				{
				$signup->setText('Revise My Signup');
				$selectColumn->add($signup);
				}

			$row->add($selectColumn);
			$fieldSet->add($row);

			if ($showComments && ! empty($rider->comments))
				{
				$row = new \PHPFUI\GridX();
				$row->add(\App\Tools\TextHelper::addLinks($rider->comments));
				$fieldSet->add($row);
				}
			}

		return $fieldSet;
		}

	public function schedule(\PHPFUI\ORM\DataObjectCursor $rides, string $noRidesMessage = 'No rides are currently scheduled', int $showNoLeader = 0) : \App\UI\Accordion | \PHPFUI\Header
		{
		if (! \count($rides))
			{
			return new \PHPFUI\Header($noRidesMessage, 5);
			}

		$unaffiliated = '';
		$lastDate = $rides->current()->rideDate;
		$dateAccordion = new \App\UI\Accordion();
		$dayAccordion = 0;
		$targetPaceColumn = $this->page->value('targetPaceOption');

		$leaderless = $this->page->value('LeaderlessName') ?: 'Cancelled';

		foreach ($rides as $ride)
			{
			if (! $ride instanceof \App\Record\Ride)
				{
				$ride = new \App\Record\Ride($ride);
				}

			if ($ride->rideDate != $lastDate)
				{
				$dateAccordion->addTab(\App\Tools\Date::formatString('l, F j, Y', $lastDate), $dayAccordion, true)->addClass('ride-date-header');
				$dayAccordion = 0;
				$lastDate = $ride->rideDate;
				}

			if (! $dayAccordion)
				{
				$dayAccordion = new \App\UI\Accordion();
				}
			$row = new \PHPFUI\GridX();
			$row->addClass('text-center');
			$time = new \PHPFUI\Cell(1);
			$time->add(\App\Tools\TimeHelper::toSmallTime($ride->startTime ?? ''));
			$row->add($time);
			$cat = new \PHPFUI\Cell(2);
			$categoryLetter = $this->paceTable->getPace($ride->paceId ?? 0);

			if ($targetPaceColumn && $ride->targetPace > 0.0)
				{
				$categoryLetter .= '/' . \str_replace('.0', '', \number_format($ride->targetPace, 1));
				}
			$cat->add($categoryLetter);
			$row->add($cat);

			$mileageCell = new \PHPFUI\Cell(1);
			$mileageCell->add($this->responsiveMileage($ride->mileage));
			$row->add($mileageCell);
			$title = new \PHPFUI\Cell(5 + (int)$showNoLeader * 3);

			if ($ride->unaffiliated)
				{
				$ride->title .= '<span class="unaffiliated">*</span>';
				$ride->description .= ' <span class="unaffiliated">*Unaffiliated Ride</span>';
				$unaffiliated = '<div class="unaffiliated">' . $this->page->value('unaffiliatedMessage') . '</div>';
				}
			$title->add($ride->title);
			$row->add($title);

			$status = '';

			if (! $showNoLeader)
				{
				$leader = new \PHPFUI\Cell(3);

				if (\App\Enum\Ride\Status::LEADER_OPTED_OUT == $ride->rideStatus)
					{
					$status = $leaderName = "<span class='ride-cancelled'>{$leaderless}</span>";
					}
				elseif (\App\Enum\Ride\Status::CANCELLED_FOR_WEATHER == $ride->rideStatus)
					{
					$status = $ride->rideStatus->name();
					$status = $leaderName = "<span class='ride-cancelled'>{$status}</span>";
					}
				else
					{
					$leaderName = $this->leader->getName($ride->memberId);
					}
				$leader->add($leaderName);
				$row->add($leader);
				}
			$today = \App\Tools\Date::todayString();
			$content = new \PHPFUI\Container();
			$content->add(\App\Tools\TextHelper::addRideLinks(\App\Tools\TextHelper::unhtmlentities($ride->description ?? ''), $this->page->isSignedIn()));

			if ($status)
				{
				$content->prepend($status);
				}

			$bg = new \PHPFUI\ButtonGroup();
			$bg->addClass('round');

			if ($this->page->isSignedIn())
				{
				if ($ride->rideDate < $today && $ride->rideStatus->value > 0 && \App\Enum\Ride\Status::LEADER_OPTED_OUT != $ride->rideStatus)
					{
					$content->add('<br><b>Ride Stats: </b>' . $ride->rideStatus->name() . ' ');

					if ($ride->averagePace)
						{
						$content->add(' <b>Average:</b> ' . $ride->averagePace);
						}

					if ($ride->numberOfRiders)
						{
						$content->add(' <b>Riders:</b> ' . $ride->numberOfRiders);
						}

					if ($ride->elevation)
						{
						$content->add(' <b>Elevation:</b> ' . $ride->elevation);
						}
					}

				if ($ride->startLocationId)
					{
					$link = $this->startLocationView->getLocationPicker($ride->startLocation);
					$content->add("<div><b>Start:</b> {$link}</div>");
					}

				$content->add($this->getRWGPSMenu($ride));

				if ($ride->pending && $this->page->isAuthorized('Approve Rides'))
					{
					$button = new \PHPFUI\Button('Approve', '/Rides/approve/' . $ride->rideId);
					$button->addClass('success');
					$bg->addButton($button);
					}

				if ($this->page->isAuthorized('Ride Sign Up') && ! $ride->unaffiliated && ! $this->approvingRides && \App\Enum\Ride\Status::CANCELLED_FOR_WEATHER != $ride->rideStatus)
					{
					if ($ride->rideDate >= $today)
						{
						$button = new \PHPFUI\Button('Sign Up', '/Rides/signedUp/' . $ride->rideId);
						$button->addClass('success');
						}
					else
						{
						$button = new \PHPFUI\Button('Signed Up', '/Rides/signedup/' . $ride->rideId);
						}
					$bg->addButton($button);
					}

				if (! empty($ride->cueSheetId))
					{
					$button = new \PHPFUI\DropDownButton('Cue ' . $ride->cueSheetId);
					$cueSheet = new \App\Record\CueSheet($ride->cueSheetId);
					$this->cueSheetVersionTable->setDateDescCursor($cueSheet);

					if ($this->cueSheetVersionTable->count() > 1)
						{
						$button->addLink($this->cueSheetView->getUrl($cueSheet), 'Description');
						}

					foreach ($this->cueSheetVersionTable->getRecordCursor() as $version)
						{
						if ($version->link)
							{
							$link = new \PHPFUI\Link($version->link, 'Link');
							$menuItem = new \PHPFUI\MenuItem('Link', $version->link);
							$menuItem->setLinkObject($link);
							$button->addMenuItem($menuItem);
							}

						if ($version->extension)
							{
							$button->addLink($this->cueSheetView->getRevisionUrl($version->cueSheetVersionId), $version->extension);
							}
						}
					$bg->addButton($button);
					}

				$edit = '';

				if ($ride->memberId)
					{
					$button = new \PHPFUI\Button('Ride Leader Stats', '/Leaders/stats/' . $ride->memberId);
					$button->addClass('info');
					$bg->addButton($button);
					}

				$canAddRide = \App\Model\Ride::canAddRide($this->page->getPermissions());

				if (! $ride->memberId && $canAddRide && $ride->rideDate >= $today)
					{
					$edit = 'Lead It!';
					}

				if ($this->canEdit($ride))
					{
					$edit = 'Edit';
					}

				if ($edit)
					{
					$button = new \PHPFUI\Button($edit, '/Rides/edit/' . $ride->rideId);
					$button->addClass('warning');
					$bg->addButton($button);
					}

				if ($canAddRide && ! $this->approvingRides)
					{
					$button = new \PHPFUI\Button('Repeat Ride');
					$button->addClass('secondary');
					$this->getRepeatRideModal($ride, $button);
					$bg->addButton($button);
					}

				if ($this->canDelete($ride))
					{
					$button = new \PHPFUI\Button('Del', '/Rides/delete/' . $ride->rideId);
					$button->addClass('alert');
					$button->setConfirm('Have you notified all signed up riders you are deleting this ride?  It can not be undone.');
					$bg->addButton($button);
					}
				}
			elseif ($ride->startLocationId && $ride->unaffiliated)
				{
				$link = $this->startLocationView->getLocationPicker($ride->startLocation);
				$content->add("<br><b>Start:</b> {$link}");
				$content->add($this->getRWGPSMenu($ride));
				}

			if ($ride->memberId)
				{
				$title = "Your {$ride->title} ride on " . \App\Tools\Date::formatString('M j', $ride->rideDate);
				$button = new \PHPFUI\Button('Contact Leader', '/Membership/email/' . $ride->memberId . '?title=' . \urlencode($title));
				$bg->addButton($button);
				}

			if (\count($bg))
				{
				$bg->addButtonClass('small');
				$content->add($bg);
				}

			$dayAccordion->addTab($row, $content)->addClass('ride-header cat-All cat-' . $this->paceTable->getCategoryIdFromPaceId($ride->paceId));
			}
		$dateAccordion->addTab(\App\Tools\Date::formatString('l, F j, Y', $lastDate), $dayAccordion . $unaffiliated, true)->addClass('ride-date-header');

		return $dateAccordion;
		}

	public function stats(int $year) : string
		{
		$rides = \App\Table\Ride::getDateRange(\gregoriantojd(1, 1, $year), \gregoriantojd(12, 31, $year));
		$rideTotals = [];
		$ridesWithRiders = [];
		$riderCounts = [];
		$mainRidesWithRiders = [];
		$averageCounts = [];
		$minSpeed = [];
		$maxSpeed = [];
		$mainMinSpeed = [];
		$mainMaxSpeed = [];
		$averageSpeeds = [];
		$mileage = [];
		$mainMileage = [];
		$mainAverageCounts = [];
		$mainAverageSpeeds = [];
		$mainRiderCounts = [];
		$mainCategoryCounts = [];
		$startingLocation = [];
		$riderMiles = 0;
		$totalRiders = 0;
		$riderRides = 0;

		foreach ($rides as $ride)
			{
			if ($ride->unaffiliated && ! $ride->rideStatus->value)
				{
				continue;	// don't count unreported unafilliated rides
				}
			$paceId = $ride->paceId;

			if ($ride->startLocationId)
				{
				if (isset($startingLocation[$ride->startLocationId]))
					{
					$rideStat = $startingLocation[$ride->startLocationId];
					++$rideStat['count'];
					$rideStat['numberOfRiders'] += $ride->numberOfRiders;
					$startingLocation[$ride->startLocationId] = $rideStat;
					}
				else
					{
					$startingLocation[$ride->startLocationId] = ['count' => 1, 'numberOfRiders' => $ride->numberOfRiders];
					}
				}

			if (! isset($rideTotals[$paceId]))
				{
				$rideTotals[$paceId] = 0;
				}
			$rideTotals[$paceId]++;

			if (! isset($mileage[$paceId]))
				{
				$mileage[$paceId] = 0;
				}
			$mileage[$paceId] += (int)$ride->mileage;
			$ride->averagePace ??= 0.0;

			if ($ride->averagePace > 0)
				{
				if (! isset($minSpeed[$paceId]) || $minSpeed[$paceId] > $ride->averagePace)
					{
					$minSpeed[$paceId] = \number_format((float)$ride->averagePace, 1);
					}

				if (! isset($maxSpeed[$paceId]) || $maxSpeed[$paceId] < $ride->averagePace)
					{
					$maxSpeed[$paceId] = \number_format((float)$ride->averagePace, 1);
					}

				if (! isset($averageCounts[$paceId]))
					{
					$averageCounts[$paceId] = 0;
					}
				$averageCounts[$paceId]++;

				if (! isset($averageSpeeds[$paceId]))
					{
					$averageSpeeds[$paceId] = 0.0;
					}
				$averageSpeeds[$paceId] += $ride->averagePace;
				}
			$categoryId = $this->paceTable->getCategoryIdFromPaceId($ride->paceId);

			if (! isset($mainCategoryCounts[$categoryId]))
				{
				$mainCategoryCounts[$categoryId] = 0;
				}
			$mainCategoryCounts[$categoryId]++;

			if (! isset($mainMileage[$categoryId]))
				{
				$mainMileage[$categoryId] = 0;
				}
			$mainMileage[$categoryId] += (int)$ride->mileage;

			if ($ride->numberOfRiders > 0)
				{
				$riderMiles += (int)$ride->numberOfRiders * (int)$ride->mileage;

				if (! isset($mainRidesWithRiders[$categoryId]))
					{
					$mainRidesWithRiders[$categoryId] = 0;
					}
				$mainRidesWithRiders[$categoryId]++;

				if (! isset($mainRiderCounts[$categoryId]))
					{
					$mainRiderCounts[$categoryId] = 0;
					}
				$mainRiderCounts[$categoryId] += (int)$ride->numberOfRiders;

				if ($ride->averagePace > 0)
					{
					if (! isset($mainMinSpeed[$categoryId]) || $mainMinSpeed[$categoryId] > $ride->averagePace)
						{
						$mainMinSpeed[$categoryId] = \number_format((float)$ride->averagePace, 1);
						}

					if (! isset($mainMaxSpeed[$categoryId]) || $mainMaxSpeed[$categoryId] < $ride->averagePace)
						{
						$mainMaxSpeed[$categoryId] = \number_format((float)$ride->averagePace, 1);
						}

					if (! isset($mainAverageCounts[$categoryId]))
						{
						$mainAverageCounts[$categoryId] = 0;
						}
					$mainAverageCounts[$categoryId]++;

					if (! isset($mainAverageSpeeds[$categoryId]))
						{
						$mainAverageSpeeds[$categoryId] = 0.0;
						}
					$mainAverageSpeeds[$categoryId] += $ride->averagePace;
					}
				}

			if ($ride->numberOfRiders > 0)
				{
				++$riderRides;
				$totalRiders += $ride->numberOfRiders;

				if (! isset($ridesWithRiders[$paceId]))
					{
					$ridesWithRiders[$paceId] = 0;
					}
				$ridesWithRiders[$paceId]++;

				if (! isset($riderCounts[$paceId]))
					{
					$riderCounts[$paceId] = 0;
					}
				$riderCounts[$paceId] += (int)$ride->numberOfRiders;
				}
			}
		\uksort($mainCategoryCounts, static fn ($a, $b) => $a <=> $b);
		\uksort($rideTotals, static fn ($a, $b) => $a <=> $b);
		\uksort($ridesWithRiders, static fn ($a, $b) => $a <=> $b);
		$output = '<h3>There were ' . \count($rides) . ' rides led in ' . $year . '.</h3>';
		$table = new \PHPFUI\Table();
		$table->addHeader((string)0, 'Main Category');
		$table->addHeader((string)1, 'Total Rides');
		$table->addHeader((string)2, 'Status Reported');
		$table->addHeader((string)3, '% Reported');
		$table->addHeader((string)4, 'Total Riders');
		$table->addHeader((string)5, 'Average Riders / Ride');
		$table->addHeader((string)6, 'Average Mileage');
		$table->addHeader((string)7, 'Average Speed');
		$table->addHeader((string)8, 'Minimum Average Speed');
		$table->addHeader((string)9, 'Maximum Average Speed');
		$categoryTable = new \App\Table\Category();

		foreach ($mainCategoryCounts as $key => $value)
			{
			if (! isset($mainRidesWithRiders[$key]))
				{
				$mainRidesWithRiders[$key] = 0;
				}

			if (! isset($mainRiderCounts[$key]))
				{
				$mainRiderCounts[$key] = 0;
				}

			if (! isset($mainMileage[$key]))
				{
				$mainMileage[$key] = 0;
				}

			if (! isset($mainAverageSpeeds[$key]))
				{
				$mainAverageSpeeds[$key] = 0;
				}

			if (! isset($mainAverageCounts[$key]))
				{
				$mainAverageCounts[$key] = 0;
				}

			if (! isset($mainMinSpeed[$key]))
				{
				$mainMinSpeed[$key] = 0;
				}

			if (! isset($mainMaxSpeed[$key]))
				{
				$mainMaxSpeed[$key] = 0;
				}
			$row = [];
			$row[] = $this->graphDropDown($categoryTable->getCategoryForId($key), $this->getCategoryPaceData($key, $rides));
			$row[] = $value;
			$row[] = $mainRidesWithRiders[$key];
			$result = ' ';

			if ($value > 0)
				{
				$result = \number_format($mainRidesWithRiders[$key] / $value * 100, 1);
				}
			$row[] = $result . '%';

			if ($mainRidesWithRiders[$key] > 0)
				{
				$result = \number_format($mainRiderCounts[$key] / $mainRidesWithRiders[$key], 1);
				}
			$row[] = $mainRiderCounts[$key];
			$row[] = $result;
			$row[] = \number_format($mainMileage[$key] / $value, 1);
			$result = ' ';

			if ($mainAverageCounts[$key] > 0)
				{
				$result = \number_format($mainAverageSpeeds[$key] / $mainAverageCounts[$key], 1);
				}
			$row[] = $result;
			$row[] = $mainMinSpeed[$key];
			$row[] = $mainMaxSpeed[$key];
			$table->addRow($row);
			}
		$output .= $table;
		$table = new \PHPFUI\Table();
		$table->addHeader((string)0, 'Specific Category');
		$table->addHeader((string)1, 'Total Rides');
		$table->addHeader((string)2, 'Status Reported');
		$table->addHeader((string)3, '% Reported');
		$table->addHeader((string)4, 'Total Riders');
		$table->addHeader((string)5, 'Average Riders / Ride');
		$table->addHeader((string)6, 'Average Mileage');
		$table->addHeader((string)7, 'Average Speed');
		$table->addHeader((string)8, 'Minimum Average Speed');
		$table->addHeader((string)9, 'Maximum Average Speed');

		foreach ($rideTotals as $key => $value)
			{
			if (! isset($averageCounts[$key]))
				{
				$averageCounts[$key] = 0;
				}

			if (! isset($averageSpeeds[$key]))
				{
				$averageSpeeds[$key] = 0;
				}

			if (! isset($minSpeed[$key]))
				{
				$minSpeed[$key] = 0;
				}

			if (! isset($maxSpeed[$key]))
				{
				$maxSpeed[$key] = 0;
				}

			if (! isset($ridesWithRiders[$key]))
				{
				$ridesWithRiders[$key] = 0;
				}

			if (! isset($riderCounts[$key]))
				{
				$riderCounts[$key] = 0;
				}

			$row = [];
			$row[] = $this->graphDropDown($this->paceTable->getPace((int)$key), $this->getPaceData((int)$key, $rides));
			$row[] = $value;
			$row[] = $ridesWithRiders[$key];
			$result = ' ';

			if ($value > 0)
				{
				$result = \number_format($ridesWithRiders[$key] / $value * 100, 1);
				}
			$row[] = $result . '%';
			$result = ' ';

			if ($ridesWithRiders[$key] > 0)
				{
				$result = \number_format($riderCounts[$key] / $ridesWithRiders[$key], 1);
				}
			$row[] = $riderCounts[$key];
			$row[] = $result;
			$row[] = \number_format($mileage[$key] / $value, 1);
			$result = ' ';

			if ($averageCounts[$key] > 0)
				{
				$result = \number_format($averageSpeeds[$key] / $averageCounts[$key], 1);
				}
			$row[] = $result;
			$row[] = $minSpeed[$key];
			$row[] = $maxSpeed[$key];
			$table->addRow($row);
			}
		$output .= $table;

		if ($riderRides)
			{
			$output .= '<h3>The average ride had ' . \number_format($totalRiders / $riderRides, 1) . ' riders.</h3>';
			}
		$output .= '<h3>' . \number_format($riderMiles) . ' miles ridden by club members on club rides.</h3>';
		\arsort($startingLocation);
		$table = new \PHPFUI\Table();
		$table->addHeader((string)0, 'Starting Location');
		$table->addHeader((string)1, 'Rides Led');
		$table->addHeader((string)2, 'Percent');
		$table->addHeader((string)3, 'Total Riders');
		$locations = new \App\View\StartLocation($this->page);

		foreach ($startingLocation as $key => $rideStat)
			{
			$value = $rideStat['count'];
			$table->addRow([$locations->getText(new \App\Record\StartLocation($key)),
				$value,
				\number_format($value * 100 / \count($rides), 1),
				$rideStat['numberOfRiders'], ]);
			}
		$output .= $table;

		return $output;
		}

	private function canEdit(\PHPFUI\ORM\DataObject $ride) : bool
		{
		$member = \App\Model\Session::signedInMemberRecord();

		return $ride->memberId == $member->memberId || $this->page->isAuthorized('Edit Ride');
		}

	/**
	 * @return int[]
	 *
	 * @psalm-return array<string, positive-int>
	 */
	private function getCategoryPaceData(int $paceId, \PHPFUI\ORM\RecordCursor $rides) : array
		{
		$paceCount = [];

		foreach ($rides as $ride)
			{
			if ($this->paceTable->getCategoryIdFromPaceId($ride->paceId) == $paceId)
				{
				$ride->averagePace = $ride->averagePace;

				if ($ride->averagePace > 0)
					{
					$pace = \number_format($ride->averagePace, 1);

					if (isset($paceCount[$pace]))
						{
						$paceCount[$pace]++;
						}
					else
						{
						$paceCount[$pace] = 1;
						}
					}
				}
			}

		return $paceCount;
		}

	/**
	 *
	 * @return int[]
	 * @psalm-return array<string, positive-int>
	 */
 private function getPaceData(int|string $paceId, \PHPFUI\ORM\RecordCursor $rides) : array
		{
		$paceCount = [];

		foreach ($rides as $ride)
			{
			if ($paceId == $ride->paceId)
				{
				if ($ride->averagePace > 0)
					{
					$pace = \number_format($ride->averagePace, 1);

					if (isset($paceCount[$pace]))
						{
						$paceCount[$pace]++;
						}
					else
						{
						$paceCount[$pace] = 1;
						}
					}
				}
			}

		return $paceCount;
		}

	private function getRepeatRideModal(\PHPFUI\ORM\DataObject $ride, \PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$submit = new \PHPFUI\Submit('Repeat Ride');
		$status = new \PHPFUI\Callout('success');
		$status->addClass('hide');
		$id = $status->getId();
		$statusId = '$("#' . $id . '")';
		$this->page->addJavaScript("function updateStatus{$id}(d){{$statusId}.html(d.status).removeClass('hide');}");
		$form = new \PHPFUI\Form($this->page, $submit, "updateStatus{$id}");
		$form->setAreYouSure(false);

		if ($form->isMyCallback() && ($_POST['rideId'] ?? 0) == $ride->rideId)
			{
			\PHPFUI\ORM::beginTransaction();
			$cloning = new \App\Record\Ride((int)$_POST['rideId']);
			$cloning->rideId = $cloning->numberOfRiders = $cloning->accident = $cloning->pointsAwarded = 0;
			$cloning->averagePace = null;
			$cloning->rideStatus = \App\Enum\Ride\Status::NOT_YET;
			$cloning->releasePrinted = '';
			$cloning->memberId = \App\Model\Session::signedInMemberId();

			$startDate = $_POST['cloneToDate'];
			$returnValue = '<h6>Ride was repeated to the following dates:</h6>';
			$rideModel = new \App\Model\Ride();

			$RWGPSIds = [];
			$rideRWGPSTable = new \App\Table\RideRWGPS()->setWhere(new \PHPFUI\ORM\Condition('rideId', $ride->rideId));

			foreach ($rideRWGPSTable->getRecordCursor() as $rideRWGPS)
				{
				$RWGPSIds[] = $rideRWGPS->RWGPSId;
				}

			for ($i = 0; $i < (int)($_POST['cloneCount']); ++$i)
				{
				$cloning->rideDate = $startDate;
				$cloning->memberId = 0;
				$id = $rideModel->add(\array_merge($cloning->toArray(), ['RWGPSId' => $RWGPSIds]), true);
				$date = \App\Tools\Date::formatString('D M j, Y', $startDate);
				$returnValue .= "<p><a href='/Rides/edit/{$id}' target=_blank>{$date}</a>";
				$startDate = \App\Tools\Date::increment($startDate, (int)($_POST['cloneDayInterval']));
				}
			\PHPFUI\ORM::commit();
			$this->page->setRawResponse(\json_encode(['response' => 'Ride Repeated', 'color' => 'lime',
				'status' => $returnValue, ], JSON_THROW_ON_ERROR));

			return;
			}
		$form->add(new \PHPFUI\Input\Hidden('rideId', (string)$ride->rideId));
		$form->add(new \PHPFUI\Panel('Repeating a ride allows you to copy the ride to another date, or series of dates'));
		$date = new \PHPFUI\Input\Date($this->page, 'cloneToDate', 'Repeat on this date');
		$date->setMinDate(\App\Tools\Date::todayString());
		$date->setRequired();
		$date->setToolTip('This is the date where you want to repeat it to, or the start of the series of repeated rides');
		$form->add($date);
		$number = new \PHPFUI\Input\Number('cloneCount', 'Number of times you want to repeat the ride.', 1);
		$number->setToolTip('If you enter a number higher than one, your ride will be repeated based on the following day offset.');
		$number->addAttribute('min', (string)0)->addAttribute('max', (string)99)->addAttribute('step', (string)1);
		$div = new \PHPFUI\HTML5Element('div');
		$divId = $div->getId();
		$number->addAttribute('onchange', '$("#' . $divId . '").toggle(this.value>1);');
		$form->add($number);
		$interval = new \PHPFUI\Input\Number('cloneDayInterval', 'Day interval for repeating multiple rides.', 7);
		$interval->setToolTip('1 would repeat the ride every day, 7 would repeat the ride each week, 14 would repeat it every other week.');
		$interval->addAttribute('min', (string)0)->addAttribute('max', (string)99)->addAttribute('step', (string)1);
		$div->add($interval);
		$div->addAttribute('style', 'display:none;');
		$form->add($div);
		$form->add($status);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function getStatsReveal(\PHPFUI\HTML5Element $opener, \App\Record\RWGPS $RWGPS) : \PHPFUI\Reveal
		{
		$reveal = new \PHPFUI\Reveal($this->page, $opener);
		$reveal->addClass('large');
		$div = new \PHPFUI\HTML5Element('div');
		$reveal->add($div);
		$reveal->add($reveal->getCloseButton());
		$reveal->loadUrlOnOpen('/RWGPS/stats/' . $RWGPS->RWGPSId, $div->getId());

		return $reveal;
		}

	/**
	 * @param array<int> $paceCount
	 */
	private function graphDropDown(string $name, array $paceCount) : \PHPFUI\DropDown
		{
		$span = new \PHPFUI\HTML5Element('div');

		\ksort($paceCount);
		$max = 0;

		foreach ($paceCount as $value)
			{
			$max = \max($max, $value);
			}

		foreach ($paceCount as $key => $value)
			{
			$progress = new \PHPFUI\ProgressBar($key . '&nbsp;(' . $value . ')');
			$percent = 100 * $value / $max;
			$progress->setPercent((int)\round($percent));
			$span->add($progress);
			}

		$dropDown = new \PHPFUI\DropDown(new \PHPFUI\Link('#x', $name), $span);
		$dropDown->setHover();

		return $dropDown;
		}

	private function responsiveMileage(?float $mileage, int $maxDecimals = 2) : string
		{
		$small = new \PHPFUI\HTML5Element('span');
		$small->addClass('show-for-small-only');
		$small->add(\number_format($mileage, 0));

		$medium = new \PHPFUI\HTML5Element('span');
		$medium->addClass('show-for-medium-only');
		$medium->add(\number_format($mileage, \min($maxDecimals, 1)));

		$large = new \PHPFUI\HTML5Element('span');
		$large->addClass('show-for-large');
		$large->add(\number_format($mileage, $maxDecimals));

		return $small . $medium . $large;
		}
	}
