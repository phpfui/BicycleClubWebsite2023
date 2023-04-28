<?php

namespace App\View;

class Leader
	{
	private readonly \App\Table\Member $memberTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->memberTable = new \App\Table\Member();
		}

	public function getEditControl(string $name, string $title, ?\PHPFUI\ORM\RecordCursor $leaders = null, $leaderNumber = 0) : \PHPFUI\Input\SelectAutoComplete
		{
		$select = new \PHPFUI\Input\SelectAutoComplete($this->page, $name, $title);
		$select->addOption('Please select a ' . $title, (string)0, 0 == $leaderNumber);
		$select->addOption('No Leader', (string)0, false);

		if (! $leaders || ! \count($leaders))
			{
			$leaders = $this->memberTable->getLeaders();
			}

		foreach ($leaders as $leader)
			{
			$select->addOption($leader->fullName(), $leader->memberId, $leaderNumber == $leader->memberId);
			}

		return $select;
		}

	public function getName(int $memberId) : string
		{
		$member = new \App\Record\Member($memberId);

		return $member->fullName();
		}

	public function getReportRequest() : string
		{
		$form = new \PHPFUI\Form($this->page);
		$form->addAttribute('target', '_blank');
		$row = new \PHPFUI\GridX();
		$column = new \PHPFUI\Cell(12, 6, 4);
		$categoryView = new \App\View\Categories($this->page, new \PHPFUI\Button('back'));
		$picker = $categoryView->getMultiCategoryPicker('categories', 'Category Restriction');
		$picker->setToolTip('Pick specific categories if you to filter the report to specific categories');
		$column->add($picker);
		$radio = new \PHPFUI\Input\RadioGroup('sort', '', 'leader');
		$radio->setSeparateRows();
		$radio->addButton('Leader', 'leader');
		$radio->addButton('Category', 'cat');
		$radio->addButton('5 Year Total', 'last5');
		$radio->addButton('Last Led', 'lastRide');
		$radio->addButton('Next Lead', 'nextRide');
		$group = new \PHPFUI\FieldSet('Sort By');
		$group->add($radio);
		$column->add($group);
		$row->add($column);
		$form->add($row);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->addButton(new \PHPFUI\Submit('Download PDF', 'pdf'));
		$buttonGroup->addButton(new \PHPFUI\Submit('Download CSV', 'csv'));
		$form->add($buttonGroup);

		return $form;
		}

	public function getSettings(\PHPFUI\Button $backButton) : \PHPFUI\Container
		{
		$rideSettings = new \App\View\Ride\Settings($this->page);
		$fields = \array_merge(['DeleteRidesPastDays', 'RideEditedWarningDays', 'unaffiliatedMessage', 'RideMinutesApart', 'RideSignupLimit',
			'RequireRiderWaiver', 'AdvancePostVolunteer', 'PacePicker', 'LeaderForum', 'LeaderlessName'], $rideSettings->getFieldNames());
		$container = new \PHPFUI\Container();
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$settingTable = new \App\Table\Setting();

		if ($form->isMyCallback())
			{
			foreach ($fields as $field)
				{
				$settingTable->save($field, $_POST[$field] ?? '');
				}
			$this->page->setResponse('Saved');
			}
		else
			{
			$ridesChair = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPicker('Rides Chair'));
			$chairEmail = $ridesChair->getEditControl();
			$chairEmail->setToolTip('This address will be used to email ride reminders, etc.');
			$incentivesChair = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPicker('Sign In Sheet Coordinator'));
			$cuesheetCoordinatorEmail = $incentivesChair->getEditControl();
			$cuesheetCoordinatorEmail->setToolTip('This address will be used to email pending sign in sheets.');
			$container->add(new \PHPFUI\MultiColumn($chairEmail, $cuesheetCoordinatorEmail));

			$multiColumn = new \PHPFUI\MultiColumn();
			$field = 'DeleteRidesPastDays';
			$value = (int)$settingTable->value($field);
			$deleteDays = new \PHPFUI\Input\Number($field, 'Delete Ride Days Back Restriction', $value);
			$deleteDays->addAttribute('step', (string)1)->addAttribute('min', (string)0);
			$deleteDays->setToolTip('Restrict leaders from deletings rides this number of days back. The idea is you don\'t want
         rides deleted right before they are due to be lead since people have made plans. Leaders should opt out
         of the ride, which will notify all signed up riders and allow someone else to lead the ride.
         Zero means a leader can not delete a ride listed for today.
         One would mean the leader can not delete a ride listed for tomorrow.
         Negative numbers would allow a leader to delete rides that many days in the past.');
			$multiColumn->add($deleteDays);

			$field = 'RideEditedWarningDays';
			$value = (int)$settingTable->value($field);
			$warningDays = new \PHPFUI\Input\Number($field, 'Ride Edit Warning Days', $value);
			$warningDays->addAttribute('step', (string)1)->addAttribute('min', (string)0);
			$warningDays->setToolTip('The number of days in advance of the ride that leaders will receive updates that the ride has been edited.
															Zero is off, 1 would be the ride is edited the day before.');
			$multiColumn->add($warningDays);
			$form->add($multiColumn);

			$multiColumn = new \PHPFUI\MultiColumn();
			$field = 'RideMinutesApart';
			$value = (int)$settingTable->value($field);
			$minutesApart = new \PHPFUI\Input\Number($field, 'Ride Departure Minutes Apart', $value);
			$minutesApart->addAttribute('step', (string)15)->addAttribute('min', (string)0)->addAttribute('max', (string)120);
			$minutesApart->setToolTip('The number of minutes rides must be separated that leave from the same start location. Zero is off, 30 would mandate a 1/2 hour separation of departure times.');
			$multiColumn->add($minutesApart);

			$field = 'RideSignupLimit';
			$value = (int)$settingTable->value($field);
			$signupLimit = new \PHPFUI\Input\Number($field, 'Rider Signup Limit', $value);
			$signupLimit->addAttribute('step', (string)1)->addAttribute('min', (string)0);
			$signupLimit->setToolTip('This limits the number of riders that can sign up for a ride. Additional riders signing up after the ride is full will be waitlisted. Zero is no limit.');
			$multiColumn->add($signupLimit);
			$form->add($multiColumn);

			$multiColumn = new \PHPFUI\MultiColumn();
			$field = 'RequireRiderWaiver';
			$value = (bool)$settingTable->value($field);
			$requireWaiver = new \PHPFUI\Input\CheckBoxBoolean($field, 'Require Rider Waiver', $value);
			$requireWaiver->setToolTip('Checking this will require rider to agree to the waiver on sign up.');
			$multiColumn->add($requireWaiver);

			$field = 'AdvancePostVolunteer';
			$value = (int)$settingTable->value($field);
			$hoursBefore = new \PHPFUI\Input\Number($field, 'Volunteer Credit Advance Posting Hours', $value);
			$hoursBefore->addAttribute('step', (string)1)->addAttribute('min', (string)0);
			$hoursBefore->setToolTip('Rides posted within this number of hours of the ride start will not qualify for leader points. Zero is all ride qualify.');
			$multiColumn->add($hoursBefore);

			$field = 'LeaderlessName';
			$value = $settingTable->value($field);
			$leaderless = new \PHPFUI\Input\Text($field, 'Leaderless Name', $value);
			$leaderless->setToolTip('Use this name for leaderless rides in the ride schedule.');
			$multiColumn->add($leaderless);

			$form->add($multiColumn);

			$field = 'PacePicker';
			$value = $settingTable->value($field);
			$pacePicker = new \PHPFUI\Input\RadioGroup($field, 'Category Picker Type', $value);
			$pacePicker->addButton('Combined Category/Pace Picker', (string)0);
			$pacePicker->addButton('Separate Category and Pace Picker', (string)1);
			$pacePicker->setToolTip('The combined picker will show one long list, the separate pickers show only the paces for the chosen category.');

			$field = 'LeaderForum';
			$value = (int)$settingTable->value($field);
			$forumPicker = new \App\View\Forum\Picker($field, $value, 'Leader Forum');
			$forumPicker->setToolTip('All leaders will be added to this forum. Leave blank for no leader forum.');
			$form->add(new \PHPFUI\MultiColumn($pacePicker, $forumPicker));

			$fieldSet = new \PHPFUI\FieldSet('Optional / Required Field Settings');
			$fieldSet->add($rideSettings->getOptionalFieldsConfiguration());
			$form->add($fieldSet);

			$field = 'unaffiliatedMessage';
			$value = $settingTable->value($field);
			$textArea = new \PHPFUI\Input\TextArea($field, 'Ride Schedule Unaffiliated Message', $value);
			$textArea->setToolTip('This message will be displayed at the end of the ride schedule if any rides listed are unaffiliated, but will not otherwise appear.');
			$textArea->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
			$form->add($textArea);
			$form->add('<br>');

			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);
			$buttonGroup->addButton($backButton);
			$form->add($buttonGroup);
			$container->add($form);
			}

		return $container;
		}

	public function getStats(\App\View\Page $page, \App\Record\Member $leader) : \PHPFUI\Container
		{
		$output = new \PHPFUI\Container();
		$output->add(new \PHPFUI\SubHeader($leader->fullName()));
		$table = new \PHPFUI\Table();
		$table->setHeaders($headers = ['Year',
			'Compl<wbr>eted',
			'Average Pace',
			'Average Riders',
			'Leader Opted Out', ]);
		$rideTable = new \App\Table\Ride();
		$rides = $rideTable->pastRidesForMember($leader, 0);
		$lastYear = 0;
		$count = 0;
		$row = [];
		$ridesWithRiders = 0;

		foreach ($rides as $ride)
			{
			$year = \App\Tools\Date::formatString('Y', $ride['rideDate']);

			if ($lastYear != $year)
				{
				if ($row && $ridesWithRiders)
					{
					$row['Average Riders'] = \number_format($row['Average Riders'] / $ridesWithRiders, 1);
					$table->addRow($row);
					}
				$row = [];
				$ridesWithRiders = 0;

				foreach ($headers as $key)
					{
					$row[$key] = 0;
					}
				$row['Average Pace'] = new \App\View\AveragePace();
				$row['Year'] = "<a href='/Leaders/leaderYear/{$ride['memberId']}/{$year}'>{$year}</a>";
				}

			if (\App\Table\Ride::STATUS_NO_LEADER == $ride['rideStatus'])
				{
				++$row['Leader Opted Out'];
				}

			if ($ride['rideStatus'] > \App\Table\Ride::STATUS_NO_LEADER)
				{
				++$count;
				++$row['Compl<wbr>eted'];

				if ($ride['averagePace'] > 0)
					{
					$row['Average Pace']->addRide($ride);
					}

				if (! empty($ride['numberOfRiders']))
					{
					$row['Average Riders'] += $ride['numberOfRiders'];
					++$ridesWithRiders;
					}
				}
			$lastYear = $year;
			}

		if ($row && $ridesWithRiders)
			{
			$row['Average Riders'] = \number_format($row['Average Riders'] / $ridesWithRiders, 1);
			$table->addRow($row);
			}
		$output->add($table);
		$output->add($this->formatRow('Total Rides Led', $count));

		return $output;
		}

	public function getStatsLink(\App\Record\Member $member) : string
		{
		if ($member->loaded())
			{
			return "<a href='/Leaders/stats/{$member->memberId}'>{$member->fullName()}</a>";
			}

		return '';
		}

	public function pendingLeaders() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (isset($_POST['action']) && \App\Model\Session::checkCSRF())
			{
			switch ($_POST['action'])
				{
				case 'approveLeader':

					$leaderPermission = $this->page->getPermissions()->getPermissionId('Ride Leader');
					$userPermission = new \App\Record\UserPermission();

					if (! empty($_POST['memberId']))
						{
						$userPermission->setEmpty();
						$userPermission->setFrom(['memberId' => $_POST['memberId'],
							'permissionGroup' => $leaderPermission, ]);
						$userPermission->insert();

						$member = new \App\Record\Member((int)$_POST['memberId']);
						$member->pendingLeader = 0;
						$member->update();
						$settingModel = new \App\Model\Settings();
						$settingModel->sendSettingEmail('newLeader', $member->toArray(), 'You are now a ~clubName~ Ride Leader');
						}
					$this->page->setResponse($_POST['memberId']);

					break;


				case 'deleteLeader':

					$member = new \App\Record\Member((int)$_POST['memberId']);
					$member->pendingLeader = 0;
					$member->update();
					$this->page->setResponse($_POST['memberId']);

					break;

				}
			}
		else
			{
			$this->memberTable->setWhere(new \PHPFUI\ORM\Condition('pendingLeader', 1));

			$table = new \App\UI\ContinuousScrollTable($this->page, $this->memberTable);
			$table->setRecordId('memberId');
			$page = $this->page;

			$delete = new \PHPFUI\AJAX('deleteLeader', "Delete this member's leader application?");
			$delete->addFunction('success', '$("#memberId-"+data.response).css("background-color","red").hide("fast")');
			$this->page->addJavaScript($delete->getPageJS());

			$table->addCustomColumn('Del', static function(array $member) use ($delete)
				{
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute(['memberId' => $member['memberId']]));

				return $icon;
				});

			$approve = new \PHPFUI\AJAX('approveLeader', 'Promote this member to a ride leader?');
			$approve->addFunction('success', '$("#memberId-"+data.response).css("background-color","green").hide("fast")');
			$this->page->addJavaScript($approve->getPageJS());
			$table->addCustomColumn('Accept', static function(array $member) use ($approve)
				{
				$icon = new \PHPFUI\FAIcon('far', 'thumbs-up', '#');
				$icon->addAttribute('onclick', $approve->execute(['memberId' => $member['memberId']]));

				return $icon;
				});

			$table->addCustomColumn('firstName', static fn (array $member) => "<a href='/Membership/edit/{$member['memberId']}'>{$member['firstName']}</a>");

			$table->addCustomColumn('lastName', static fn (array $member) => "<a href='/Membership/edit/{$member['memberId']}'>{$member['lastName']}</a>");

			$table->addCustomColumn('email', static fn (array $member) => new \PHPFUI\FAIcon('far', 'envelope', '/Membership/email/' . $member['memberId']));

			$assistantLeaderTable = new \App\Table\AssistantLeader();
			$table->addCustomColumn('assistantLeads', static function(array $member) use ($assistantLeaderTable)
				{
				$assistantLeaderTable->setWhere(new \PHPFUI\ORM\Condition('memberId', $member['memberId']));

				return "<a href='/Leaders/assists/{$member['memberId']}'>{$assistantLeaderTable->count()}</a>";
				});

			$sortableHeaders = ['firstName', 'lastName', 'cellPhone'];
			$normalHeaders = ['assistantLeads'];

			if ($this->page->isAuthorized('Email Member'))
				{
				$normalHeaders[] = 'email';
				}
			$normalHeaders[] = 'Accept';
			$normalHeaders[] = 'Del';
			$table->setSearchColumns($sortableHeaders)->setHeaders(\array_merge($sortableHeaders, $normalHeaders))->setSortableColumns($sortableHeaders);

			$container->add($table);
			}

		return $container;
		}

	private function formatRow(string $label, int $value) : \PHPFUI\GridX
		{
		$row = new \PHPFUI\GridX();
		$col = new \PHPFUI\Cell(6);
		$col->add("<strong>{$label}</strong>");
		$row->add($col);
		$col = new \PHPFUI\Cell(6);
		$col->add("<strong>{$value}</strong>");
		$row->add($col);

		return $row;
		}
	}
