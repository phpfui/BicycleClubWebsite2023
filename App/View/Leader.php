<?php

namespace App\View;

class Leader
	{
	private readonly \App\Table\Member $memberTable;

	public function __construct(private readonly \PHPFUI\Page $page)
		{
		$this->memberTable = new \App\Table\Member();
		}

	public function getEditControl(string $name, string $title, ?\PHPFUI\ORM\RecordCursor $leaders = null, int $leaderNumber = 0) : \PHPFUI\Input\SelectAutoComplete
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

		$assistantLeaderTypeTable = new \App\Table\AssistantLeaderType();
		$assistantLeaderTypeTable->setOrderBy('name');

		if (\count($assistantLeaderTypeTable))
			{
			$fieldSet = new \PHPFUI\FieldSet('Leader Type');
			$leaderSelect = new \PHPFUI\Input\MultiSelect('leaders');
			$leaderSelect->addOption('Ride Leader', '0');

			foreach ($assistantLeaderTypeTable->getRecordCursor() as $type)
				{
				$leaderSelect->addOption($type->name, $type->assistantLeaderTypeId);
				}
			$column->add($fieldSet->add($leaderSelect));
			}

		$picker = new \App\UI\MultiCategoryPicker('categories', 'Category Restriction');
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
		$fields = $rideSettings->getFieldNames();
		$container = new \PHPFUI\Container();
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$settingTable = new \App\Table\Setting();

		$multiColumn = new \PHPFUI\MultiColumn();
		$fields[] = $field = 'DeleteRidesPastDays';
		$value = (int)$settingTable->value($field);
		$deleteDays = new \PHPFUI\Input\Number($field, 'Delete Ride Days Back Restriction', $value);
		$deleteDays->addAttribute('step', (string)1)->addAttribute('min', (string)0);
		$deleteDays->setToolTip('Restrict ride leaders from deletings rides this number of days back. The idea is you don\'t want
			 rides deleted right before they are due to be lead since people have made plans. Leaders should opt out
			 of the ride, which will notify all signed up riders and allow someone else to lead the ride.
			 Zero means a leader can not delete a ride listed for today.
			 One would mean the leader can not delete a ride listed for tomorrow.
			 Negative numbers would allow a leader to delete rides that many days in the past.');
		$multiColumn->add($deleteDays);

		$fields[] = $field = 'RideEditedWarningDays';
		$value = (int)$settingTable->value($field);
		$warningDays = new \PHPFUI\Input\Number($field, 'Ride Edit Warning Days', $value);
		$warningDays->addAttribute('step', (string)1)->addAttribute('min', (string)0);
		$warningDays->setToolTip('The number of days in advance of the ride that riders will receive updates that the ride has been edited. Zero is off, 1 would be the ride is edited the day before.');
		$multiColumn->add($warningDays);
		$form->add($multiColumn);

		$multiColumn = new \PHPFUI\MultiColumn();
		$offsetField = 'RideStartTimeOffset';
		$offsetValue = ((int)$settingTable->value($offsetField)) ?: 15;

		$fields[] = $field = 'RideMinutesApart';
		$value = (int)$settingTable->value($field);
		$minutesApart = new \PHPFUI\Input\Number($field, 'Ride Departure Minutes Apart', $value);
		$minutesApart->addAttribute('step', (string)$offsetValue)->addAttribute('min', (string)0)->addAttribute('max', (string)120);
		$minutesApart->setToolTip('The number of minutes rides must be separated that leave from the same start location. Zero is off, 30 would mandate a 1/2 hour separation of departure times.');
		$multiColumn->add($minutesApart);

		$startTimeOffset = new \PHPFUI\Input\Number($offsetField, 'Ride Start Time Offset', $offsetValue);
		$startTimeOffset->addAttribute('step', (string)1)->addAttribute('min', (string)0)->addAttribute('max', (string)15);
		$startTimeOffset->setToolTip('The number of minute increments for the ride start times.');
		$multiColumn->add($startTimeOffset);

		$form->add($multiColumn);

		$multiColumn = new \PHPFUI\MultiColumn();
		$fields[] = $field = 'RideSignupLimit';
		$value = (int)$settingTable->value($field);
		$signupLimit = new \PHPFUI\Input\Number($field, 'Rider Signup Limit', $value);
		$signupLimit->addAttribute('step', (string)1)->addAttribute('min', (string)0);
		$signupLimit->setToolTip('This limits the number of riders that can sign up for a ride. Additional riders signing up after the ride is full will be waitlisted. Zero is no limit.');
		$multiColumn->add($signupLimit);

		$fields[] = $field = 'RideSignupLimitDefault';
		$value = (int)$settingTable->value($field);
		$signupLimit = new \PHPFUI\Input\Number($field, 'Rider Signup Limit Default', $value);
		$signupLimit->addAttribute('step', (string)1)->addAttribute('min', (string)0);
		$signupLimit->setToolTip('This is the default rider limit if there is no rider limit. Ride Leaders are free to change this on each ride.');
		$multiColumn->add($signupLimit);
		$form->add($multiColumn);

		$multiColumn = new \PHPFUI\MultiColumn();
		$fields[] = $field = 'RequireRiderWaiver';
		$value = (bool)$settingTable->value($field);
		$requireWaiver = new \PHPFUI\Input\CheckBoxBoolean($field, 'Require Rider Waiver', $value);
		$requireWaiver->setToolTip('Checking this will require rider to agree to the waiver on sign up.');
		$multiColumn->add($requireWaiver);

		$fields[] = $field = 'NoLeadersOnPublicSchedule';
		$value = (bool)$settingTable->value($field);
		$noLeader = new \PHPFUI\Input\CheckBoxBoolean($field, "Don't show leaders on public schedule", $value);
		$noLeader->setToolTip('Checking this will remove ride leader names from the public schedule.');
		$multiColumn->add($noLeader);
		$form->add($multiColumn);

		$multiColumn = new \PHPFUI\MultiColumn();
		$fields[] = $field = 'RidePendingDefault';
		$value = (bool)$settingTable->value($field);
		$pending = new \PHPFUI\Input\CheckBoxBoolean($field, 'Default New Rides to Pending', $value);
		$pending->setToolTip('Checking this will make new rides pending until approved.');
		$multiColumn->add($pending);

		$fields[] = $field = 'RideDescriptionEms';
		$value = (int)$settingTable->value($field);
		$editorHeight = new \PHPFUI\Input\Number($field, 'Ride Description Height', $value);
		$editorHeight->addAttribute('min', '13');
		$editorHeight->setToolTip('Description editor height in EMs. 13 is the minimum.');
		$multiColumn->add($editorHeight);

		$form->add($multiColumn);

		$multiColumn = new \PHPFUI\MultiColumn();
		$fields[] = $field = 'signupNotifications';
		$value = (bool)$settingTable->value($field);
		$signupNotifications = new \PHPFUI\Input\CheckBoxBoolean($field, 'Default Ride Signup Notifications', $value);
		$signupNotifications->setToolTip('Check to default ride signup notification on for newly added rides.');
		$multiColumn->add($signupNotifications);

		$fields[] = $field = 'cueSheetFieldName';
		$value = $settingTable->value($field);
		$cueSheetFieldName = new \PHPFUI\Input\Text($field, 'Cue Sheet Field Name', $value);
		$cueSheetFieldName->setToolTip('You can change the name of the Cue Sheet field in the ride editor.');
		$multiColumn->add($cueSheetFieldName);
		$form->add($multiColumn);

		$multiColumn = new \PHPFUI\MultiColumn();
		$fields[] = $field = 'AdvancePostVolunteer';
		$value = (int)$settingTable->value($field);
		$hoursBefore = new \PHPFUI\Input\Number($field, 'Volunteer Credit Advance Posting Hours', $value);
		$hoursBefore->addAttribute('step', (string)1)->addAttribute('min', (string)0);
		$hoursBefore->setToolTip('Rides posted within this number of hours of the ride start will not qualify for leader points. Zero is all ride qualify.');
		$multiColumn->add($hoursBefore);

		$fields[] = $field = 'LeaderlessName';
		$value = $settingTable->value($field);
		$leaderless = new \PHPFUI\Input\Text($field, 'Leaderless Name', $value);
		$leaderless->setToolTip('Use this name for leaderless rides in the ride schedule.');
		$multiColumn->add($leaderless);

		$form->add($multiColumn);

		$multiColumn = new \PHPFUI\MultiColumn();
		$fields[] = $field = 'RideStatusHourOffset';
		$value = (int)$settingTable->value($field);
		$rideStatusRequestHourOffset = new \PHPFUI\Input\Number($field, 'Ride Status Request Email Hour Offset', $value);
		$rideStatusRequestHourOffset->addAttribute('step', (string)1)->addAttribute('min', (string)0);
		$rideStatusRequestHourOffset->setToolTip('The email request for Ride Status will be sent this number of hours after the start of the ride.');
		$multiColumn->add($rideStatusRequestHourOffset);

		$multiColumn->add('&nbsp;');

		$form->add($multiColumn);

		$fields[] = $field = 'PacePicker';
		$value = (int)$settingTable->value($field);
		$pacePicker = new \PHPFUI\Input\RadioGroup($field, 'Category Picker Type', "{$value}");
		$pacePicker->addButton('Combined Category/Pace Picker', (string)0);
		$pacePicker->addButton('Separate Category and Pace Picker', (string)1);
		$pacePicker->setToolTip('The combined picker will show one long list, the separate pickers show only the paces for the chosen category.');

		$fields[] = $field = 'LeaderForum';
		$value = (int)$settingTable->value($field);
		$forumPicker = new \App\View\Forum\Picker($field, $value, 'Leader Forum');
		$forumPicker->setToolTip('All leaders will be added to this forum. Leave blank for no leader forum.');
		$form->add(new \PHPFUI\MultiColumn($pacePicker, $forumPicker));

		$fieldSet = new \PHPFUI\FieldSet('Optional / Required Field Settings');
		$fieldSet->add($rideSettings->getOptionalFieldsConfiguration());
		$form->add($fieldSet);

		$fields[] = $field = 'unaffiliatedMessage';
		$value = $settingTable->value($field);
		$textArea = new \App\UI\TextAreaImage($field, 'Ride Schedule Unaffiliated Message', $value);
		$textArea->setToolTip('This message will be displayed at the end of the ride schedule if any rides listed are unaffiliated, but will not otherwise appear.');
		$textArea->htmlEditing($this->page, new \App\Model\TinyMCETextArea(new \App\Record\Setting()->getLength('value'), ['height' => '"20em"']));
		$form->add($textArea);
		$form->add('<br>');

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

			if (\App\Enum\Ride\Status::LEADER_OPTED_OUT->value == $ride['rideStatus'])
				{
				++$row['Leader Opted Out'];
				}

			if ($ride['rideStatus'] > \App\Enum\Ride\Status::LEADER_OPTED_OUT->value)
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

	public function pendingLeaders(\App\Model\PermissionBase $permissions) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (isset($_POST['action']) && \App\Model\Session::checkCSRF())
			{
			switch ($_POST['action'])
				{
				case 'approveLeader':

					$leaderPermission = $permissions->getPermissionId('Ride Leader');
					$userPermission = new \App\Record\UserPermission();

					if (! empty($_POST['memberId']))
						{
						$userPermission->setEmpty();
						$userPermission->setFrom(['memberId' => $_POST['memberId'],
							'permissionGroup' => $leaderPermission, ]);
						$userPermission->insertOrIgnore();

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
			$delete->addFunction('success', '$("#memberId-"+data.response).css("background-color","red").hide("fast").remove()');
			$this->page->addJavaScript($delete->getPageJS());

			$table->addCustomColumn('Del', static fn (array $member) : \PHPFUI\FAIcon => new \PHPFUI\FAIcon('far', 'trash-alt', '#')->addAttribute('onclick', $delete->execute(['memberId' => $member['memberId']])));

			$approve = new \PHPFUI\AJAX('approveLeader', 'Promote this member to a ride leader?');
			$approve->addFunction('success', '$("#memberId-"+data.response).css("background-color","green").hide("fast").remove()');
			$this->page->addJavaScript($approve->getPageJS());
			$table->addCustomColumn('Accept', static fn (array $member) : \PHPFUI\FAIcon => new \PHPFUI\FAIcon('far', 'thumbs-up', '#')->addAttribute('onclick', $approve->execute(['memberId' => $member['memberId']])));

			$table->addCustomColumn('firstName', static fn (array $member) : string => "<a href='/Membership/edit/{$member['memberId']}'>{$member['firstName']}</a>");

			$table->addCustomColumn('lastName', static fn (array $member) : string => "<a href='/Membership/edit/{$member['memberId']}'>{$member['lastName']}</a>");

			$table->addCustomColumn('email', static fn (array $member) : \PHPFUI\FAIcon => new \PHPFUI\FAIcon('far', 'envelope', '/Membership/email/' . $member['memberId']));

			$assistantLeaderTable = new \App\Table\AssistantLeader();
			$table->addCustomColumn('assistantLeads', static function(array $member) use ($assistantLeaderTable) : string
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

	/**
	 * @param array<string,string|array<int>> $parameters
	 */
	public function show(array $parameters, int $permissionGroupId) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$filterButton = new \PHPFUI\Button('Filter');
		$this->addFilterReveal($parameters, $filterButton);
		$gridx = new \PHPFUI\GridX();
		$gridx->setMargin();
		$cell = new \PHPFUI\Cell(2);
		$cell->add($filterButton);
		$gridx->add($cell);

		if (\count($parameters['categories'] ?? []))
			{
			$cell = new \PHPFUI\Cell();
			$cell->setAuto();
			$categoryTable = new \App\Table\Category();
			$categoryTable->addSelect(new \PHPFUI\ORM\Literal('group_concat(category) as categories'));
			$categoryTable->addOrderBy('ordering');
			$categoryTable->setWhere(new \PHPFUI\ORM\Condition('categoryId', $parameters['categories'] ?? [], new \PHPFUI\ORM\Operator\In()));
			$dataObjectCursor = $categoryTable->getDataObjectCursor();
			$cell->add('<b>Filter:</b> ' . $dataObjectCursor->current()['categories']);
			$gridx->add($cell);
			}
		$container->add($gridx);
		$memberTable = new \App\Table\Member();
		$memberTable->addJoin('membership');
		$memberTable->addJoin('userPermission', new \PHPFUI\ORM\Condition('userPermission.memberId', new \PHPFUI\ORM\Field('member.memberId')));
		$condition = new \PHPFUI\ORM\Condition('membership.expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('userPermission.permissionGroup', $permissionGroupId);

		if (\count($parameters['categories'] ?? []))
			{
			$memberTable->addJoin('memberCategory');
			$condition->and('categoryId', $parameters['categories'], new \PHPFUI\ORM\Operator\In());
			}
		$memberTable->setWhere($condition);
		$memberTable->addGroupBy('member.memberId');
		$table = new \App\UI\ContinuousScrollTable($this->page, $memberTable);
		$table->setSortColumn('lastName');

		$headers = [
			'firstName',
			'lastName',
			'categories',
			'upcoming',
			'past',
			'phone',
		];

		if ($this->page->isAuthorized('Email Member'))
			{
			$headers[] = 'email';
			}

		$fields = ['firstName', 'lastName'];
		$table->setSearchColumns($fields)->setSortableColumns($fields)->setHeaders($headers);

		$table->addCustomColumn('upcoming', static fn (array $leader) : \PHPFUI\Link => new \PHPFUI\Link('/Leaders/leaderUpcoming/' . $leader['memberId'], 'Upcoming', false));
		$table->addCustomColumn('past', static fn (array $leader) : \PHPFUI\Link => new \PHPFUI\Link('/Leaders/leaderYear/' . $leader['memberId'], 'Past', false));
		$table->addCustomColumn('first', static fn (array $leader) : \PHPFUI\Link => new \PHPFUI\Link('/Leaders/stats/' . $leader['memberId'], $leader['firstName'], false));
		$table->addCustomColumn('last', static fn (array $leader) : \PHPFUI\Link => new \PHPFUI\Link('/Leaders/stats/' . $leader['memberId'], $leader['lastName'], false));
		$table->addCustomColumn('email', static fn (array $leader) : \PHPFUI\FAIcon => new \PHPFUI\FAIcon('far', 'envelope', '/Leaders/emailLeader/' . $leader['memberId']));
		$table->addCustomColumn('phone', static function(array $leader) : string
			{
			$phone = $leader['phone'];

			if (\strlen((string)$leader['cellPhone']) >= 7)
				{
				$phone = $leader['cellPhone'];
				}

			if (! $phone)
				{
				return '';
				}

			return \PHPFUI\Link::phone($phone);
			});
		$table->addCustomColumn('categories', static fn (array $leader) : string => \App\Table\MemberCategory::getRideCategoryStringForMember($leader['memberId']));

		$container->add($table);

		return $container;
		}

	/**
	 * @param array<string,string> $parameters
	 */
	private function addFilterReveal(array $parameters, \PHPFUI\HTML5Element $button) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $button);
		$submit = new \PHPFUI\Submit('Set Filter');
		$form = new \PHPFUI\Form($this->page);
		$form->setAttribute('method', 'get');
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Select Categories to filter on');
		$picker = new \App\UI\MultiCategoryPicker('categories', 'Category Restriction', $parameters['categories'] ?? []);
		$fieldSet->add($picker);
		$form->add($fieldSet);
		$form->add($submit);
		$modal->add($form);
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
