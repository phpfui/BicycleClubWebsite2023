<?php

namespace App\View\GA;

class EventEdit
	{
	private readonly \App\Table\GaAnswer $gaAnswerTable;

	private readonly \App\Table\GaPriceDate $gaPriceDateTable;

	private readonly \App\Table\GaRide $gaRideTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->gaPriceDateTable = new \App\Table\GaPriceDate();
		$this->gaAnswerTable = new \App\Table\GaAnswer();
		$this->gaRideTable = new \App\Table\GaRide();
		$this->processRequest();
		}

	public function Edit(\App\Record\GaEvent $event) : \PHPFUI\Form
		{
		if ($event->loaded())
			{
			$submit = new \PHPFUI\Submit();
			$form = new \PHPFUI\Form($this->page, $submit);
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add', 'action');
			$organizer = \App\Model\Session::signedInMemberRecord();
			$event->registrar = $organizer->fullName();
			$event->registrarEmail = $organizer->email;
			$form = new \PHPFUI\Form($this->page);
			}

		if ($form->isMyCallback())
			{
			$event->setFrom($_POST);
			$event->description = \App\Tools\TextHelper::cleanUserHtml($_POST['description']);
			$event->signupMessage = \App\Tools\TextHelper::cleanUserHtml($_POST['signupMessage']);
			$event->update();
			$this->gaRideTable->updateFromTable($_POST);
			$this->gaPriceDateTable->updateFromTable($_POST);
			$this->gaAnswerTable->updateFromTable($_POST);
			$this->page->setResponse('Saved');

			return $form;
			}

		$tabs = new \PHPFUI\Tabs();
		$tabs->addTab('Required', $this->getRequiredFields($event), true);
		$tabs->addTab('Options', $this->getOptions($event));
		$tabs->addTab('Description', $this->getDescription($event));
		$tabs->addTab('Email', $this->getSignupEmail($event));

		if ($event->loaded())
			{
			$tabs->addTab('Route', $this->getRoute($event, $form));
			$tabs->addTab('Pricing', $this->getPricing($event, $form));
			$tabs->addTab('Q & A', $this->getQuestion($event, $form));
			}

		$tabObject = $tabs->getTabs();
		$tabObject->setAttribute('data-deep-link', 'true');
		$tabContent = $tabs->getContent();

		$form->add($tabObject);
		$form->add($tabContent);
		$form->add('<br>');
		$form->add($submit);
		$form->add(new \PHPFUI\FormError());

		return $form;
		}

	public function editRoute(\App\Record\GaRide $route) : string | \PHPFUI\Form
		{
		$event = $route->gaEvent;
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			unset($_POST['gaRideId']);
			$route->setFrom($_POST);
			$route->update();
			$this->page->setResponse('Saved');

			return '';
			}
		$form->add(new \PHPFUI\SubHeader($event->title));
		$form->add($this->getRouteFields($route));
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->addButton($submit);
		$editButton = new \PHPFUI\Button('Edit Event', '/GA/edit/' . $event->gaEventId);
		$editButton->addClass('secondary');
		$buttonGroup->addButton($editButton);
		$form->add($buttonGroup);

		return $form;
		}

	protected function addAnswerModal(\App\Record\GaEvent $event, \PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modalForm = new \PHPFUI\Form($this->page);
		$modalForm->setAreYouSure(false);
		$modalForm->add(new \PHPFUI\SubHeader('Add A Question Answer'));
		$modalForm->add(new \PHPFUI\Input\Hidden('gaEventId', (string)$event->gaEventId));
		$answer = new \PHPFUI\Input\Text('answer', 'Answer for the rider question');
		$answer->setRequired();
		$modalForm->add($answer);
		$modalForm->add($modal->getButtonAndCancel(new \PHPFUI\Submit('Add Answer')));
		$modal->add($modalForm);
		}

	protected function addPriceModal(\App\Record\GaEvent $event, \PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modalForm = new \PHPFUI\Form($this->page);
		$modalForm->setAreYouSure(false);
		$modalForm->add(new \PHPFUI\SubHeader('Add A Price'));
		$modalForm->add(new \PHPFUI\Input\Hidden('gaEventId', (string)$event->gaEventId));
		$date = new \PHPFUI\Input\Date($this->page, 'date', 'Date the price increases');
		$date->setRequired();
		$modalForm->add($date);
		$price = new \PHPFUI\Input\Number('price', 'Price as of this date');
		$price->addAttribute('max', (string)999)->addAttribute('min', (string)0);
		$price->setRequired();
		$modalForm->add($price);
		$modalForm->add($modal->getButtonAndCancel(new \PHPFUI\Submit('Add Price')));
		$modal->add($modalForm);
		}

	protected function addRouteModal(\App\Record\GaEvent $event, \PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modalForm = new \PHPFUI\Form($this->page);
		$modalForm->setAreYouSure(false);
		$modalForm->add(new \PHPFUI\SubHeader('Add A Route'));
		$ride = new \App\Record\GaRide();
		$ride->gaEventId = $event->gaEventId;
		$modalForm->add($this->getRouteFields($ride, $modal));
		$modalForm->add($modal->getButtonAndCancel(new \PHPFUI\Submit('Add Route')));
		$modal->add($modalForm);
		}

	protected function getRouteFields(\App\Record\GaRide $route, ?\PHPFUI\Reveal $reveal = null) : \PHPFUI\Container
		{
		$form = new \PHPFUI\Container();
		$form->add(new \PHPFUI\Input\Hidden('gaEventId', (string)$route->gaEventId));
		$distance = new \PHPFUI\Input\Number('distance', 'Distance', $route->distance);
		$distance->addAttribute('max', (string)999)->addAttribute('min', (string)0);
		$distance->setRequired();
		$extraPrice = new \PHPFUI\Input\Number('extraPrice', 'Additional Fee', $route->extraPrice);
		$extraPrice->addAttribute('max', (string)99)->addAttribute('min', (string)0);
		$form->add(new \PHPFUI\MultiColumn($distance, $extraPrice));
		$startTime = new \PHPFUI\Input\Time($this->page, 'startTime', 'Route Start Time', $route->startTime ?? '');

		if ($reveal)
			{
			$startTime->setParentReveal($reveal);
			}
		$startTime->setToolTip('The first time when riders can go out on the route.');
		$endTime = new \PHPFUI\Input\Time($this->page, 'endTime', 'Route End Time', $route->endTime ?? '');
		$endTime->setToolTip('The last time riders can head out on a route.');
		$form->add(new \PHPFUI\MultiColumn($startTime, $endTime));
		$description = new \PHPFUI\Input\TextArea('description', 'Route Description', $route->description);
		$description->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$form->add($description);
		$form->add('<br>');

		return $form;
		}

	private function getDescription(\App\Record\GaEvent $event) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$description = new \PHPFUI\Input\TextArea('description', 'Event Description', \str_replace("\n", '<br>', $event->description ?? ''));
		$description->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$description->setToolTip('This description will be shown on the web site where people go to sign up. It will also be included on confirmation emails.');
		$container->add($description);

		return $container;
		}

	private function getSignupEmail(\App\Record\GaEvent $event) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$signupMessage = new \PHPFUI\Input\TextArea('signupMessage', 'Email on Sign Up', \str_replace("\n", '<br>', $event->signupMessage ?? ''));
		$signupMessage->setToolTip('This will be sent to people who successfully register for the event.');
		$signupMessage->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$container->add($signupMessage);

		return $container;
		}

	private function getRoute(\App\Record\GaEvent $event, \PHPFUI\Form $form) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$this->gaRideTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $event->gaEventId))->addOrderBy('distance');

		$table = new \PHPFUI\Table();
		$table->setRecordId($recordId = 'gaRideId');
		$delete = new \PHPFUI\AJAX('deleteRoute', 'Permanently delete this route?');
		$delete->addFunction('success', "$('#{$recordId}-'+data.response).css('background-color','red').hide('fast').remove();");
		$this->page->addJavaScript($delete->getPageJS());
		$table->setHeaders(['distance' => 'Distance', 'startTime' => 'Start Time', 'endTime' => 'End Time',
			'extraPrice' => 'Extra Price', 'edit' => 'Edit', 'del' => 'Del', ]);

		foreach ($this->gaRideTable->getRecordCursor() as $route)
			{
			$row = $route->toArray();
			$id = $route->gaRideId;
			$editIcon = new \PHPFUI\FAIcon('far', 'edit', '/GA/editRoute/' . $id);
			$row['edit'] = $editIcon;
			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $delete->execute([$recordId => $id]));
			$row['del'] = $icon;
			$table->addRow($row);
			}
		$container->add($table);
		$addRouteButton = new \PHPFUI\Button('Add Route');
		$form->saveOnClick($addRouteButton);
		$this->addRouteModal($event, $addRouteButton);
		$container->add($addRouteButton);

		return $container;
		}

	private function getPricing(\App\Record\GaEvent $event, \PHPFUI\Form $form) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$pricing = $this->gaPriceDateTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $event->gaEventId))->getRecordCursor();
		$table = new \PHPFUI\Table();
		$table->setRecordId($recordId = 'gaPriceDateId');
		$delete = new \PHPFUI\AJAX('deletePrice', 'Permanently delete this price?');
		$delete->addFunction('success', "$('#{$recordId}-'+data.response).css('background-color','red').hide('fast')");
		$this->page->addJavaScript($delete->getPageJS());
		$table->setHeaders(['date' => 'Date', 'price' => 'Price', 'del' => 'Del']);

		foreach ($pricing as $price)
			{
			$id = $price->gaPriceDateId;
			$row = $price->toArray();
			$row['gaPriceDateId'] = $id;
			$row['date'] = new \PHPFUI\Input\Date($this->page, "date[{$id}]", '', $price->date);
			$row['price'] = new \PHPFUI\Input\Number("price[{$id}]", '', $price->price);
			$row['price']->addAttribute('max', (string)999)->addAttribute('min', (string)0);
			$row['price'] .= new \PHPFUI\Input\Hidden("gaPriceDateId[{$id}]", $price->gaPriceDateId);
			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $delete->execute([$recordId => $id]));
			$row['del'] = $icon;
			$table->addRow($row);
			}
		$container->add($table);
		$addPriceButton = new \PHPFUI\Button('Add Price');
		$form->saveOnClick($addPriceButton);
		$this->addPriceModal($event, $addPriceButton);
		$container->add($addPriceButton);

		return $container;
		}

	private function getQuestion(\App\Record\GaEvent $event, \PHPFUI\Form $form) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$container->add(new \PHPFUI\Input\Text('question', 'Question to ask riders', $event->question));
		$answers = $this->gaAnswerTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $event->gaEventId))->getRecordCursor();
		$table = new \PHPFUI\Table();
		$table->setRecordId($recordId = 'gaAnswerId');
		$delete = new \PHPFUI\AJAX('deleteAnswer', 'Permanently delete this answer?');
		$delete->addFunction('success', "$('#{$recordId}-'+data.response).css('background-color','red').hide('fast').remove();");
		$this->page->addJavaScript($delete->getPageJS());
		$table->setHeaders(['answer' => 'Answer', 'del' => 'Del']);
		$table->setWidths(['90%', '10%']);

		foreach ($answers as $answer)
			{
			$id = $answer->gaAnswerId;
			$row = $answer->toArray();
			$row['gaAnswerId'] = $id;
			$row['answer'] = new \PHPFUI\Input\Text("answer[{$id}]", '', $answer->answer);
			$row['answer'] .= new \PHPFUI\Input\Hidden("gaAnswerId[{$id}]", $answer->gaAnswerId);
			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $delete->execute([$recordId => $id]));
			$row['del'] = $icon;
			$table->addRow($row);
			}
		$container->add($table);
		$addAnswerButton = new \PHPFUI\Button('Add Answer');
		$form->saveOnClick($addAnswerButton);
		$this->addAnswerModal($event, $addAnswerButton);
		$container->add($addAnswerButton);

		return $container;
		}

	private function getOptions(\App\Record\GaEvent $event) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$optionsSet = new \PHPFUI\FieldSet('Options');
		$maxRegistrants = new \PHPFUI\Input\Number('maxRegistrants', 'Max Registrants', $event->maxRegistrants);
		$maxRegistrants->addAttribute('min', (string)0)->addAttribute('max', (string)99)->addAttribute('step', (string)1);
		$maxRegistrants->setToolTip('Set to zero for unlimited registrations');
		$optionsSet->add($maxRegistrants);
		$dayOfRegistration = new \PHPFUI\Input\CheckBoxBoolean('dayOfRegistration', 'Day Of Registration Allowed', (bool)$event->dayOfRegistration);
		$showPreregistration = new \PHPFUI\Input\CheckBoxBoolean('showPreregistration', 'Show Preregistration Numbers', (bool)$event->showPreregistration);
		$optionsSet->add(new \PHPFUI\MultiColumn($dayOfRegistration, $showPreregistration));
		$includeMembership = new \PHPFUI\Input\CheckBoxBoolean('includeMembership', 'Include Membership with Event Purchase', (bool)$event->includeMembership);
		$membershipExpiresDate = new \PHPFUI\Input\Date($this->page, 'membershipExpires', 'Membership Expires', $event->membershipExpires);
		$membershipExpiresDate->setToolTip('If a membership is included, this is when it will expire. Leave blank for the end of the year.');
		$membershipMultiColumn = new \PHPFUI\MultiColumn($includeMembership, $membershipExpiresDate);
		$otherEvent = new \PHPFUI\Input\CheckBoxBoolean('otherEvent', 'Not Signature Event', (bool)$event->otherEvent);
		$optionsSet->add(new \PHPFUI\MultiColumn($membershipMultiColumn, $otherEvent));
		$container->add($optionsSet);
		$volunteerSet = new \PHPFUI\FieldSet('Volunteer Options');
		$volunteerDiscount = new \PHPFUI\Input\Number('volunteerDiscount', 'Volunteer Discount', $event->volunteerDiscount);
		$volunteerDiscount->addAttribute('max', (string)99)->addAttribute('min', (string)0);
		$volunteerDiscount->setToolTip('If someone signs up as a volunteer, then they will receive this discount when registering');
		$volunteerEvent = new \PHPFUI\Input\Select('volunteerEvent', 'Corresponding Volunteer Event');
		$volunteerEvent->setToolTip('This is the volunteer event a member must volunteer for to receive a discount.');
		$jobEventTable = new \App\Table\JobEvent();
		$jobs = $jobEventTable->getJobEvents();

		foreach ($jobs as $job)
			{
			$volunteerEvent->addOption($job->name, $job->jobEventId, $event->volunteerEvent == $job->jobEventId);
			}
		$volunteerSet->add(new \PHPFUI\MultiColumn($volunteerEvent, $volunteerDiscount));
		$container->add($volunteerSet);

		return $container;
		}

	private function getRequiredFields(\App\Record\GaEvent $event) : \PHPFUI\Container
		{
		$requiredFields = new \PHPFUI\Container();
		$gaEventId = new \PHPFUI\Input\Hidden('gaEventId', (string)$event->gaEventId);
		$requiredFields->add($gaEventId);
		$title = new \PHPFUI\Input\Text('title', 'Title', $event->title);
		$title->setRequired();
		$requiredFields->add($title);
		$eventDate = new \PHPFUI\Input\Date($this->page, 'eventDate', 'Event Date', $event->eventDate);
		$eventDate->setRequired();
		$lastRegDate = new \PHPFUI\Input\Date($this->page, 'lastRegistrationDate', 'Last Registration Date', $event->lastRegistrationDate);
		$lastRegDate->setRequired();

		$lteValidator = new \PHPFUI\Validator\LTE();
		$gteValidator = new \PHPFUI\Validator\GTE();
		$this->page->addAbideValidator($lteValidator)->addAbideValidator($gteValidator);

		$eventDate->setValidator($gteValidator, 'Must be greater or equal to Last Registration Date', $lastRegDate->getId());
		$lastRegDate->setValidator($lteValidator, 'Must be less than or equal to Event Date', $eventDate->getId());

		$this->page->addJavaScript($lteValidator->getJavaScript());
		$this->page->addJavaScript($gteValidator->getJavaScript());

		$requiredFields->add(new \PHPFUI\MultiColumn($eventDate, $lastRegDate));
		$registrar = new \PHPFUI\Input\Text('registrar', 'Registrar Name', $event->registrar);
		$registrar->setRequired();
		$registrarEmail = new \PHPFUI\Input\Email('registrarEmail', 'Registrar Email', $event->registrarEmail);
		$registrarEmail->setRequired();
		$requiredFields->add(new \PHPFUI\MultiColumn($registrar, $registrarEmail));
		$location = new \PHPFUI\Input\Text('location', 'Location', $event->location);
		$location->setRequired();
		$requiredFields->add($location);

		return $requiredFields;
		}

	private function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'deleteRoute':

						$gaRide = new \App\Record\GaRide((int)$_POST['gaRideId']);
						$gaRide->delete();
						$this->page->setResponse($_POST['gaRideId']);

						break;

					case 'deleteAnswer':
						$gaAnswer = new \App\Record\GaAnswer((int)$_POST['gaAnswerId']);
						$gaAnswer->delete();
						$this->page->setResponse($_POST['gaAnswerId']);

						break;

					case 'deletePrice':
						$gaPriceDate = new \App\Record\GaPriceDate((int)$_POST['gaPriceDateId']);
						$gaPriceDate->delete();
						$this->page->setResponse($_POST['gaPriceDateId']);

						break;

					case 'Add':
						unset($_POST['gaEventId']);
						$gaEvent = new \App\Record\GaEvent();
						$gaEvent->setFrom($_POST);
						$gaEventId = $gaEvent->insert();
						$this->page->redirect('/GA/edit/' . $gaEventId);

						break;
					}
				}
			elseif (isset($_POST['submit']))
				{
				if ('Add Route' == $_POST['submit'])
					{
					unset($_POST['gaRideId']);
					$gaRide = new \App\Record\GaRide();
					$gaRide->setFrom($_POST);
					$gaRide->insert();
					$this->page->redirect();
					}
				elseif ('Add Price' == $_POST['submit'])
					{
					unset($_POST['gaPriceDateId']);
					$gaPriceDate = new \App\Record\GaPriceDate();
					$gaPriceDate->setFrom($_POST);
					$gaPriceDate->insert();
					$this->page->redirect();
					}
				elseif ('Add Answer' == $_POST['submit'])
					{
					unset($_POST['gaAnswerId']);
					$gaAnswer = new \App\Record\GaAnswer();
					$gaAnswer->setFrom($_POST);
					$gaAnswer->insert();
					$this->page->redirect();
					}
				}
			}
		}
	}
