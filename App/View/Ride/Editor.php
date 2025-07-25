<?php

namespace App\View\Ride;

class Editor
	{
	private string $cueSheetSelectorId;

	private readonly \App\UI\TextAreaImage $description;

	private \PHPFUI\Input\Number $elevation;

	private ?\PHPFUI\ORM\RecordCursor $leaders = null;

	private string $mileageSelectorId;

	private readonly \App\View\Ride\Settings $rideSettingsView;

	/**
	 * @var array<int> $rwgpsRoutes
	 */
	private array $rwgpsRoutes = [];

	private int $startTimeOffset = 15;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->description = new \App\UI\TextAreaImage('description', 'Description');
		$textArea = new \App\Model\TinyMCETextArea(new \App\Record\Ride()->getLength('description'));
		$ems = (int)$this->page->value('RideDescriptionEms');

		if ($ems < 13)
			{
			$ems = 30;
			}
		$textArea->addSetting('height', '"' . $ems . 'em"');
		$this->description->htmlEditing($page, $textArea);
		$this->rideSettingsView = new \App\View\Ride\Settings($page);
		$this->startTimeOffset = ((int)$this->page->value('RideStartTimeOffset')) ?: 15;
		$this->elevation = new \PHPFUI\Input\Number('elevation', 'Elevation Gain (' . \App\Record\RWGPS::getSmallUnits() . ')');
		$this->elevation->setToolTip('Just how hilly was this ride?');
		$this->elevation->addAttribute('min', (string)0)->addAttribute('max', (string)99999)->addAttribute('step', (string)10);

		$this->processRequest();
		}

	public function addByCueSheet() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Next');
		$form = new \PHPFUI\Form($this->page);

		$fieldSet = new \PHPFUI\FieldSet('Required Fields');
		$date = new \PHPFUI\Input\Date($this->page, 'rideDate', 'Date');
		$date->setMinDate(\App\Tools\Date::todayString());
		$date->setToolTip('The date of the ride');
		$date->setRequired();

		$time = new \PHPFUI\Input\Time($this->page, 'startTime', 'Start Time', '09:00', $this->startTimeOffset);
		$time->setToolTip('Time the ride leaves the start location');
		$time->setRequired();
		$category = new \App\View\PacePicker('paceId', 'Category', 'Pace');
		$fieldSet->add(new \PHPFUI\MultiColumn($date, $time, $category));
		$cueSheetPicker = new \App\UI\CueSheetPicker($this->page, 'cueSheetId', "Select {$this->page->value('cueSheetFieldName')} (start typing to search)");
		$picker = $cueSheetPicker->getEditControl();
		$picker->setRequired();
		$fieldSet->add($picker);
		$fieldSet->add(new \PHPFUI\Input\Hidden('memberId', (string)\App\Model\Session::signedInMemberId()));
		$form->add($fieldSet);
		$form->add($submit);

		return $form;
		}

	public function addByRWGPS() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Add RWGPS Ride');
		$form = new \PHPFUI\Form($this->page);

		$fieldSet = new \PHPFUI\FieldSet('Required Fields');
		$date = new \PHPFUI\Input\Date($this->page, 'rideDate', 'Date');
		$date->setMinDate(\App\Tools\Date::todayString());
		$date->setToolTip('The date of the ride');
		$date->setRequired();

		$time = new \PHPFUI\Input\Time($this->page, 'startTime', 'Start Time', '09:00', $this->startTimeOffset);
		$time->setToolTip('Time the ride leaves the start location');
		$time->setRequired();
		$category = new \App\View\PacePicker('paceId', 'Category', 'Pace');
		$fieldSet->add(new \PHPFUI\MultiColumn($date, $time, $category));

		$rwgpsPicker = new \App\UI\RWGPSPicker($this->page, 'RWGPSId', 'RWGPS to create from (start typing to search)');
		$picker = $rwgpsPicker->getEditControl();
		$picker->setRequired();
		$fieldSet->add($picker);
		$fieldSet->add(new \PHPFUI\Input\Hidden('memberId', (string)\App\Model\Session::signedInMemberId()));
		$form->add($fieldSet);
		$form->add($submit);

		return $form;
		}

	public function addRWGPSRoute(int $RWGPSId) : void
		{
		$this->rwgpsRoutes[] = $RWGPSId;
		}

	public function edit(\App\Record\Ride $ride) : \PHPFUI\Container
		{
		$output = new \PHPFUI\Container();
		$errorCallout = new \PHPFUI\Callout('alert');
		$errorCallout->addClass('small')->addClass('hide');

		if ($ride->rideId)
			{
			$submit = new \PHPFUI\Submit();
			$form = new \App\UI\ErrorForm($this->page, $submit);
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add');
			$form = new \App\UI\ErrorForm($this->page, $submit);
			$get = $_GET;

			foreach ($get as $key => $value)
				{
				if (\str_ends_with($key, 'Id'))
					{
					$get[$key] = (int)$value;
					}
				}
			$ride->setFrom($get);
			$ride->signupNotifications = (int)$this->page->value('signupNotifications');
			$ride->rideId = 0;
			}

		if ($form->isMyCallback())
			{
			$rideModel = new \App\Model\Ride();

			if (! $ride->rideId)
				{
				$post = $_POST;

				$id = $rideModel->add($post);

				if (\is_array($id))
					{
					$this->page->setRawResponse(\json_encode(['response' => 'Errors Found!', 'color' => 'red', 'errors' => $id], JSON_THROW_ON_ERROR));

					return $output;
					}

				$url = $this->page->getBaseURL();

				if (\stripos($url, 'clone'))
					{
					$pos = \strrpos($url, '/');

					if ($pos > 0)
						{
						$clonedRide = new \App\Record\Ride((int)\substr($url, $pos + 1));
						$leader = \App\Model\Session::signedInMemberRecord();
						// process the wait list since we may have taken the new leader off the old ride
						$clonedRideSignupModel = new \App\Model\RideSignup($clonedRide, $leader);
						$clonedRideSignupModel->notifyWaitList();
						// copy the waitlist to the new ride
						$rideSignupModel = new \App\Model\RideSignup(new \App\Record\Ride($id), $leader);
						$rideSignupModel->copyWaitList($clonedRide);
						}
					}
				$addedRide = new \App\Record\Ride($id);

				$response = ['response' => 'Saved', 'color' => 'lime', 'record' => $addedRide->toArray(), ];

				if ($addedRide->pending)
					{
					$response['redirect'] = '/Rides/My/pending';
					}
				else
					{
					$response['redirect'] = '/Rides/edit/' . $id;
					}
				$this->page->setRawResponse(\json_encode($response));

				return $output;
				}

			$_POST['rideId'] = $ride->rideId;

			if (! empty($_POST['accident']))
				{
				\App\Model\AccidentReport::report($ride);
				}
			$errors = $rideModel->save($_POST);

			if ($errors)
				{
				$this->page->setRawResponse(\json_encode(['response' => 'Errors Found!', 'color' => 'red', 'errors' => $errors], JSON_THROW_ON_ERROR));
				}
			else
				{
				$this->page->setResponse('Saved');
				}

			return $output;
			}

		if (empty($ride->startTime))
			{
			$ride->startTime = '09:00:00';
			}
		$afterRide = 0 != $ride->rideId && $ride->rideDate . ' ' . $ride->startTime < \date('Y-m-d H:i:s');

		$form->add(new \PHPFUI\Input\Hidden('rideId', (string)$ride->rideId));

		if (! $ride->memberId && \App\Model\Ride::canAddRide($this->page->getPermissions()) && ! $afterRide)
			{
			$ride->memberId = \App\Model\Session::signedInMemberId();
			}

		if (! $ride->rideId)
			{
			$content = new \App\View\Content($this->page);
			$output->add($content->getDisplayCategoryHTML('Add A Ride'));
			$ride->memberId = \App\Model\Session::signedInMemberId();
			}

		if ($afterRide)
			{
			$rideView = new \App\View\Rides($this->page);

			if (! $ride->numberOfRiders)
				{
				$ride->numberOfRiders = \count($ride->confirmedRiders);

				if ($ride->numberOfRiders && ! $ride->rideStatus->value)
					{
					$ride->rideStatus = \App\Enum\Ride\Status::COMPLETED;
					}
				}

			$output->add($rideView->getRideInfo($ride));
			$fieldSet = new \PHPFUI\FieldSet('Ride Statistics');
			$multiColumn = new \PHPFUI\MultiColumn();

			if ($this->page->isAuthorized('Change Ride Pace'))
				{
				$category = new \App\View\PacePicker('paceId', 'Category', 'Pace', $ride->paceId);
				$multiColumn->add($category);
				}
			$mileage = new \PHPFUI\Input\Number('mileage', 'Mileage', $ride->mileage);
			$mileage->addAttribute('max', (string)999)->addAttribute('min', (string)0);
			$this->mileageSelectorId = $mileage->getId();
			$mileage->setToolTip('Actual mileage for the ride');
			$mileage->setRequired($afterRide);
			$multiColumn->add($mileage);
			$status = new \PHPFUI\Input\SelectEnum('rideStatus', 'Ride Status', $ride->rideStatus);
			$status->setToolTip('This helps us judge how many rides are actually led');
			$multiColumn->add($status);
			$fieldSet->add($multiColumn);

			$numRiders = new \PHPFUI\Input\Number('numberOfRiders', 'Number Of Riders', $ride->numberOfRiders);
			$numRiders->setToolTip('This helps us judge the number of active members');
			$numRiders->addAttribute('min', (string)0)->addAttribute('max', (string)99)->addAttribute('step', (string)1);
			$numRiders->setRequired($afterRide);
			$averagePace = new \PHPFUI\Input\Number('averagePace', 'Average Pace (if known)', $ride->averagePace);
			$averagePace->addAttribute('min', (string)5)->addAttribute('max', (string)25)->addAttribute('step', (string)0.1);
			$averagePace->setRequired($afterRide);
			$averagePace->setToolTip('This would be the group consensus of the average pace of the ride');
			$this->elevation->setValue((string)$ride->elevation);
			$fieldSet->add(new \PHPFUI\MultiColumn($numRiders, $averagePace, $this->elevation));

			$multiColumn = new \PHPFUI\MultiColumn();
			$accident = new \PHPFUI\Input\CheckBoxBoolean('accident', 'Any crashes on the Ride?', (bool)$ride->accident);
			$accident->setToolTip('If there were any crashes on this ride, please check this box.  We will email more information to follow up.');
			$accident->setConfirm('Are you sure you want to report an accident on this ride?');

			$multiColumn->add('<br>' . $accident);
			$fieldSet->add($multiColumn);

			$form->add($fieldSet);
			$form->add($this->getLeaderFieldSet($ride, $form));
			$form->add($this->getOptionalInfo($ride, $form));
			}
		else
			{
			$fieldSet = new \PHPFUI\FieldSet('Required Fields');

			$title = new \PHPFUI\Input\Text('title', 'Ride Title', $ride->title);
			$title->setToolTip('A good title will draw attention to the ride.');
			$title->setRequired();
			$fieldSet->add($title);
			$date = new \PHPFUI\Input\Date($this->page, 'rideDate', 'Date', $ride->rideDate);
			$date->setMinDate(\App\Tools\Date::todayString());
			$date->setToolTip('The date of the ride');
			$date->setRequired();
			$time = new \PHPFUI\Input\Time($this->page, 'startTime', 'Start Time', $ride->startTime, $this->startTimeOffset);
			$time->setToolTip('Time the ride leaves the start location');
			$time->setRequired();
			$fieldSet->add(new \PHPFUI\MultiColumn($date, $time));
			$category = new \App\View\PacePicker('paceId', 'Category', 'Pace', $ride->paceId);
			$mileage = new \PHPFUI\Input\Number('mileage', 'Mileage', $ride->mileage);
			$mileage->addAttribute('max', (string)999)->addAttribute('min', (string)0);
			$this->mileageSelectorId = $mileage->getId();
			$mileage->setToolTip('Expected mileage for the ride');
			$mileage->setRequired();
			$multiColumn = new \PHPFUI\MultiColumn($category, $mileage);
			$settingTable = new \App\Table\Setting();

			$fieldSet->add($multiColumn);

			$requiredColumns = $this->rideSettingsView->getRequiredFields($ride);

			if (\count($requiredColumns))
				{
				$fieldSet->add($requiredColumns);
				}

			$startLocation = new \App\View\StartLocation($this->page);
			$slEdit = $startLocation->getEditControl($ride->startLocationId);
			$slEdit->setToolTip('Once you choose a start location, you can select a cue sheet.');
			$slEdit->setRequired();
			$ajax = new \PHPFUI\AJAX('changeStartLocation');

			// side effect of calling getOptionalInfo is to set $this->cueSheetSelectorId
			$optionalInfo = $this->getOptionalInfo($ride, $form);
			$ajax->addFunction('success', '$("#' . $this->cueSheetSelectorId . '").html(data.response);');

			$slEdit->addAttribute('onchange', $ajax->execute(['startLocationId' => 'this.value']));
			$this->page->addJavaScript($ajax->getPageJS());
			$fieldSet->add($slEdit);

			$form->add($fieldSet);
			$form->add($this->getLeaderFieldSet($ride, $form));
			$form->add($optionalInfo);
			}
		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$settingTable = new \App\Table\Setting();
		$settingTable->value('DeleteRidesPastDays');
		$today = \App\Tools\Date::todayString();

		if ($ride->rideDate >= $today && $ride->memberId && $ride->rideId)
			{
			$button = new \PHPFUI\Button('Cancel Ride');
			$button->addClass('alert');
			$form->saveOnClick($button);
			$this->getCancelRideModal($ride, $button);
			$buttonGroup->addButton($button);
			}

		if ($ride->rideId)
			{
			$signedUp = new \PHPFUI\Button('Signed Up Riders', "/Rides/signedup/{$ride->rideId}");
			$signedUp->addClass('secondary');
			$buttonGroup->addButton($signedUp);
			}
		$form->add($buttonGroup);
		$form->add(new \PHPFUI\FormError());
		$form->add($errorCallout);
		$output->add($form);

		return $output;
		}

	public function setRWGPSRoutes(\App\Record\Ride $ride) : void
		{
		$this->rwgpsRoutes = [];

		$rideRWGPSTable = new \App\Table\RideRWGPS()->setWhere(new \PHPFUI\ORM\Condition('rideId', $ride->rideId));

		foreach ($rideRWGPSTable->getRecordCursor() as $rideRWGPS)
			{
			$this->rwgpsRoutes[] = $rideRWGPS->RWGPSId;
			}
		}

	protected function getCancelRideModal(\App\Record\Ride $ride, \PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$submit = new \PHPFUI\Submit('Cancel Ride');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('rideId', (string)$ride->rideId));
		$form->add(new \PHPFUI\Panel('When you cancel leading a ride, the following happen:
                              <ul><li>The ride is marked as "Leader Not Coming"</li>
                              <li>All signed up riders are emailed</li>
                              <li>Someone else can step up and volunteer to lead the ride</li></ul>'));
		$text = new \PHPFUI\Input\TextArea('message', 'Message to all signed up riders');
		$text->setToolTip('It would be a good idea to say why you decided to not lead the ride.');
		$text->setRequired();
		$form->add($text);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	protected function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['submit']))
				{
				switch ($_POST['submit'])
					{
					case 'Next':

						$parameters = $_POST;
						$cueSheet = new \App\Record\CueSheet($parameters['cueSheetId']);
						$parameters['RWGPSId'] = $cueSheet->RWGPSId;
						$parameters['memberId'] = \App\Model\Session::signedInMemberId();
						$parameters['description'] = $cueSheet->description;
						$parameters['elevation'] = \round($cueSheet->RWGPS->elevationFeet ?? 0.0);
						$parameters['mileage'] = $cueSheet->mileage;
						$parameters['startLocationId'] = $cueSheet->startLocationId;
						$parameters['title'] = $cueSheet->name;
						$parameters = $this->cleanParameters($parameters);

						$this->page->redirect('/Rides/edit/0?' . \http_build_query($parameters));

						break;

					case 'Add RWGPS Route':
						if (isset($_POST['RWGPSId']))
							{
							$RWGPSId = (int)$_POST['RWGPSId'];
							}
						else
							{
							$rwgpsModel = new \App\Model\RideWithGPS();
							$rwgps = $rwgpsModel->getRWGPSFromLink($_POST['RWGPSurl'] ?? '');
							$RWGPSId = $rwgps ? $rwgps->RWGPSId : 0;
							}

						if ($RWGPSId)
							{
							$rideRWGPS = new \App\Record\RideRWGPS();
							$rideRWGPS->rideId = (int)$_POST['rideId'];
							$rideRWGPS->RWGPSId = $RWGPSId;
							$rideRWGPS->reload();

							if (! $rideRWGPS->loaded())
								{
								$rideRWGPS->rideId = (int)$_POST['rideId'];
								$rideRWGPS->RWGPSId = $RWGPSId;
								$rideRWGPS->insertOrIgnore();
								$rideModel = new \App\Model\Ride();
								$rideModel->emailRWGPSChange($rideRWGPS);
								}
							}
						$this->page->redirect();

						break;

					case 'Add RWGPS Ride':

						$parameters = $_POST;
						$rwgps = new \App\Record\RWGPS($parameters['RWGPSId']);
						$parameters['memberId'] = \App\Model\Session::signedInMemberId();
						$parameters['RWGPSId'] = $rwgps->RWGPSId;
						$parameters['description'] = $rwgps->description;
						$parameters['elevation'] = \round($rwgps->elevationFeet ?? 0.0);
						$parameters['mileage'] = $rwgps->miles;
						$parameters['title'] = $rwgps->title;
						$parameters = $this->cleanParameters($parameters);

						$this->page->redirect('/Rides/edit/0?' . \http_build_query($parameters));

						break;

					case 'Cancel Ride':

						$rideModel = new \App\Model\Ride();
						$ride = new \App\Record\Ride((int)$_POST['rideId']);
						$rideModel->cancel($ride, $_POST['message']);
						$this->page->redirect("/Rides/optOut/{$ride->rideId}");

						break;


					case 'Add Assistant':
						if (! empty($_POST['memberId']))
							{
							$assistant = new \App\Record\AssistantLeader();
							$assistant->setFrom($_POST);

							if (! $assistant->loaded())
								{
								$assistant->insertOrIgnore();
								}
							}
						$rideModel = new \App\Model\Ride();
						$ride = new \App\Record\Ride((int)$_POST['rideId']);
						$rideModel->addLeaderSignups($ride);
						$this->page->redirect();

						break;

					}
				}
			elseif (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'changeStartLocation':
						$cueSheetView = new \App\View\CueSheet($this->page);
						$cueSheet = $cueSheetView->getEditControl('cueSheetId', $this->page->value('cueSheetFieldName'), (int)$_POST['startLocationId']);
						$this->page->setResponse($cueSheet);

						break;

					case 'deleteAssistant':
						$assistantLeader = new \App\Record\AssistantLeader();
						$assistantLeader->setFrom($_POST);
						$assistantLeader->delete();
						$this->page->setResponse($_POST['memberId']);

						break;

					case 'deleteRWGPS':
						$rideRWGPS = new \App\Record\RideRWGPS($_POST);
						$rideRWGPS->delete();
						$rideModel = new \App\Model\Ride();
						$rideModel->emailRWGPSChange($rideRWGPS);

						$this->page->setRawResponse(\json_encode(['response' => $_POST['count']], JSON_THROW_ON_ERROR));

						break;

					case 'changeRWGPS':
						$rwgpsModel = new \App\Model\RideWithGPS();
						$rwgps = $rwgpsModel->getRWGPSFromLink($_POST['RWGPSurl'] ?? '');

						$data = [];

						if ($rwgps)
							{
							$elevation = \App\Model\RideWithGPS::getElevation($rwgps);

							if ($elevation > 0)
								{
								$rwgps->elevationFeet = $elevation;
								}
							$rwgps->miles = (float)\number_format($rwgps->miles ?? 0.0, 1);
							$data = $rwgps->toArray();
							$data['RWGPSId'] = $rwgps->routeLink();
							}

						$this->page->setRawResponse(\json_encode(['response' => $data], JSON_THROW_ON_ERROR));

						break;

					case 'changeRWGPSId':
						$rwgps = new \App\Record\RWGPS($_POST['RWGPSId'] ?? '');

						if ($rwgps->loaded())
							{
							$elevation = \App\Model\RideWithGPS::getElevation($rwgps);

							if ($elevation > 0)
								{
								$rwgps->elevationFeet = $elevation;
								}
							}
						else
							{
							$rwgps->elevationFeet = 0.0;
							}
						$rwgps->miles = (float)\number_format((float)$rwgps->miles, 1);
						$data = $rwgps->toArray();
						$data['RWGPSId'] = $rwgps->RWGPSId;

						$this->page->setRawResponse(\json_encode(['response' => $data], JSON_THROW_ON_ERROR));

						break;
					}
				}
			}
		}

	/**
	 * @param array<string,string> $parameters
	 *
	 * @return array<string,string>
	 */
	private function cleanParameters(array $parameters) : array
		{
		unset($parameters['submit'], $parameters['csrf']);

		foreach ($parameters as $key => $value)
			{
			if (\str_ends_with($key, 'Text'))
				{
				unset($parameters[$key]);
				}
			}

		return $parameters;
		}

	private function getAssistantModal(\App\Record\Ride $ride, \PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$submit = new \PHPFUI\Submit('Add Assistant');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('rideId', (string)$ride->rideId));
		$memberPickerModel = new \App\Model\MemberPickerNoSave('Assistant Leader');
		$memberPicker = new \App\UI\MemberPicker($this->page, $memberPickerModel, 'memberId');
		$form->add($memberPicker->getEditControl());
		$assistantLeaderTypeSelect = new \App\UI\AssistantLeaderTypeSelect();

		if ($assistantLeaderTypeSelect->count())
			{
			$form->add($assistantLeaderTypeSelect);
			}
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function getLeaderFieldSet(\App\Record\Ride $ride, \PHPFUI\Form $form) : \PHPFUI\FieldSet
		{
		$leaderView = new \App\View\Leader($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Leader Information');
		$memberTable = new \App\Table\Member();
		$this->leaders = $memberTable->getLeaders();

		if ($this->page->isAuthorized('Change Ride Leader'))
			{
			$leaderField = $leaderView->getEditControl('memberId', 'Leader', $this->leaders, $ride->memberId);
			$leaderField->setToolTip('Pick a leader.  Leave alone for a "Leaderless" ride.');
			}
		else
			{
			$leader = $ride->member;
			$fieldSet->add(new \PHPFUI\Input\Hidden('memberId', (string)$ride->memberId));
			$leaderField = new \PHPFUI\Input\Text('leaderName', 'Leader', $leader->fullName());
			$leaderField->setDisabled();
			}
		$fieldSet->add($leaderField);

		if ($ride->rideId)
			{
			$assistants = $ride->assistantLeaders;

			if (\count($assistants))
				{
				$index = 'memberId';
				$delete = new \PHPFUI\AJAX('deleteAssistant');
				$delete->addFunction('success', "$('#{$index}-'+data.response).css('background-color','red').hide('fast').remove()");
				$this->page->addJavaScript($delete->getPageJS());
				$table = new \PHPFUI\Table();
				$table->setHeaders(['leader' => 'Assistant Ride Leaders', 'name' => 'Type', 'del' => 'Del']);
				$table->setRecordId('memberId');

				foreach ($assistants as $assistant)
					{
					$row = $assistant->toArray();
					$row['leader'] = $assistant->member->fullName();
					$row['name'] = $assistant->assistantLeaderType->name;
					$trash = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
					$trash->addAttribute('onclick', $delete->execute([$index => $assistant->memberId, 'rideId' => $ride->rideId]));
					$row['del'] = $trash;
					$table->addRow($row);
					}
				$fieldSet->add($table);
				}
			$addAssistantButton = new \PHPFUI\Button('Add Assistant Leader');
			$form->saveOnClick($addAssistantButton);
			$this->getAssistantModal($ride, $addAssistantButton);
			$fieldSet->add($addAssistantButton);
			}

		return $fieldSet;
		}

	private function getOptionalInfo(\App\Record\Ride $ride, \PHPFUI\Form $form) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Optional Information (recommended)');
		$this->description->setValue($ride->description ?? '');
		$this->description->setToolTip('Here is your chance to sell the ride and remember, being concise is a virtue!');
		$fieldSet->add($this->description);

		$optionalColumns = $this->rideSettingsView->getOptionalFields($ride);

		if (\count($optionalColumns))
			{
			$fieldSet->add($optionalColumns);
			}


		if (! $ride->rideId)	// First time in, use old method of adding a route
			{
			$RWGPS = new \App\Record\RWGPS();

			if (\count($this->rwgpsRoutes))
				{
				$RWGPS->read($this->rwgpsRoutes[0]);
				}

			if ($this->page->isAuthorized('Add / Update RWGPS'))
				{
				$RWGPSInput = new \PHPFUI\Input\Url('RWGPSurl', 'Ride With GPS Link', $RWGPS->routeLink());
				$ajax = new \PHPFUI\AJAX('changeRWGPS');
				$js = 'if(data.response.miles)$("#' . $this->mileageSelectorId . '").val(data.response.miles);';
				$js .= '$("#' . $RWGPSInput->getId() . '").val(data.response.RWGPSId);';
				$RWGPSInput->addAttribute('onchange', $ajax->execute(['RWGPSurl' => 'this.value']));
				}
			else
				{
				$rwgpsPicker = new \App\UI\RWGPSPicker($this->page, 'RWGPSId', 'RWGPS (start typing to search)', $RWGPS);
				$RWGPSInput = $rwgpsPicker->getEditControl();
				$hidden = $RWGPSInput->getHiddenField();
				$ajax = new \PHPFUI\AJAX('changeRWGPSId');
				$js = 'if(data.response.miles)$("#' . $this->mileageSelectorId . '").val(data.response.miles);';
				$js .= '$("#' . $hidden->getId() . '").val(data.response.RWGPSId);';
				$hidden->addAttribute('onchange', $ajax->execute(['RWGPSId' => '$("#' . $hidden->getId() . '").val()']));
				}

			$multiColumn = new \PHPFUI\MultiColumn($RWGPSInput);

			$multiColumn->add($this->elevation);
			$js .= 'if(data.response.elevationFeet)$("#' . $this->elevation->getId() . '").val(Math.round(data.response.elevationFeet));';
			$ajax->addFunction('success', $js);
			$this->page->addJavaScript($ajax->getPageJS());

			$fieldSet->add($multiColumn);
			}
		else	// Editing exitsing ride, use new multi route version
			{
			foreach ($this->rwgpsRoutes as $RWGPSId)
				{
				$fieldSet->add(new \PHPFUI\Input\Hidden('RWGPSId[]', "{$RWGPSId}"));
				}

			$routes = $ride->RWGPSChildren;

			$delete = new \PHPFUI\AJAX('deleteRWGPS');
			$delete->addFunction('success', '$("#count-"+data.response).css("background-color","red").hide("fast").remove()');
			$this->page->addJavaScript($delete->getPageJS());

			$rwgpsTable = new \PHPFUI\Table();
			$rwgpsTable->setRecordId('count');
			$count = 0;
			$rwgpsTable->setHeaders(['RWGPS' => 'Ride With GSP Link', 'Distance', 'Elevation', 'Average Gain', 'Delete']);

			foreach ($routes as $RWGPS)
				{
				$link = new \PHPFUI\Link($RWGPS->routeLink(), $RWGPS->title);
				$link->addAttribute('target', '_blank');
				$row['RWGPS'] = $link;
				$row['Distance'] = $RWGPS->distance();
				$row['Elevation'] = \number_format(\App\Model\RideWithGPS::getElevation($RWGPS)) . ' ' . $RWGPS->getSmallUnits();
				$row['Average Gain'] = $RWGPS->gain();
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute(['rideId' => $ride->rideId, 'RWGPSId' => $RWGPS->RWGPSId, 'count' => $count]));
				$row['Delete'] = $icon;
				$row['count'] = $count++;

				$rwgpsTable->addRow($row);
				}
			$fieldSet->add($rwgpsTable);

			$addRWGPSButton = new \PHPFUI\Button('Add RWGPS');
			$form->saveOnClick($addRWGPSButton);
			$this->getRWGPSModal($ride, $addRWGPSButton);
			$fieldSet->add($addRWGPSButton);
			}

		$div = new \PHPFUI\HTML5Element('div');
		$div->setId('cuesheet');
		$cueSheetView = new \App\View\CueSheet($this->page);
		$cueSheet = $cueSheetView->getEditControl('cueSheetId', $this->page->value('cueSheetFieldName'), $ride->startLocationId ?? 0, $ride->cueSheetId ?? 0);
		$this->cueSheetSelectorId = $cueSheet->getId();
		$div->add($cueSheet);
		$fieldSet->add($div);

		if (! $this->page->isAuthorized('Cue Sheets'))
			{
			$div->addClass('hide');
			}

		$disableComments = new \PHPFUI\Input\RadioGroupEnum('commentsDisabled', 'Ride Comments Setting:', $ride->commentsDisabled);
		$disableComments->setToolTip('You can disable or hide comments. If comments are disabled, no more comments can be posted.');
		$fieldSet->add($disableComments);

		$signupNotifications = new \PHPFUI\Input\CheckBoxBoolean('signupNotifications', 'Email Rider Signup Changes', (bool)$ride->signupNotifications);
		$signupNotifications->setToolTip('You will be sent an email every time a rider changes their ride signup status.');
		$multiColumn = new \PHPFUI\MultiColumn($signupNotifications);

		if ($this->page->isAuthorized('Unaffiliated Rides Leader'))
			{
			$unaffiliated = new \PHPFUI\Input\CheckBoxBoolean('unaffiliated', 'List as an unaffiliated ride', (bool)($ride->unaffiliated ?? 0));
			$unaffiliated->setToolTip('Unaffiliated rides are not official club ride, do not need sign in sheets, and are listed for the benefit of the local cycling community.');
			$multiColumn->add($unaffiliated);
			}
		$fieldSet->add($multiColumn);

		if ($ride->dateAdded)
			{
			$fieldSet->add(new \App\UI\Display('Date / Time Added', \date('n/j/Y g:i a', \strtotime($ride->dateAdded))));
			}

		return $fieldSet;
		}

	private function getRWGPSModal(\App\Record\Ride $ride, \PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$title = 'Add RWGPS Route';
		$modal->add(new \PHPFUI\Header($title, 4));
		$submit = new \PHPFUI\Submit($title);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('rideId', (string)$ride->rideId));

		if ($this->page->isAuthorized('Add / Update RWGPS'))
			{
			$RWGPSInput = new \PHPFUI\Input\Url('RWGPSurl', 'Enter RWGPS URL');
			}
		else
			{
			$rwgpsPicker = new \App\UI\RWGPSPicker($this->page, 'RWGPSId', 'RWGPS (start typing to search)');
			$RWGPSInput = $rwgpsPicker->getEditControl();
			}
		$form->add($RWGPSInput);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
	}
