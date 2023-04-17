<?php

namespace App\View\Event;

class Events
	{
	private \App\Record\Event $event;

	private readonly \App\Table\Event $eventTable;

	private readonly \App\Table\Reservation $reservationTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->eventTable = new \App\Table\Event();
		$this->reservationTable = new \App\Table\Reservation();
		$this->processRequest();
		}

	public function confirmed(\App\Record\Reservation $reservation) : string | \PHPFUI\Container
		{
		$container = 'Reservation not found';

		if ($reservation->loaded() && $reservation->memberId == \App\Model\Session::getCustomerNumber())
			{
			$this->setEvent($reservation->event);
			$container = new \PHPFUI\Container();

			if ($reservation->paymentId || 0 == $reservation->pricePaid)
				{
				$title = 'You are signed up for:';
				$buttonText = 'Registration Details';
				}
			else
				{
				$title = 'You still own a payment for the following:';
				$buttonText = 'Confirm and Pay';
				}
			$container->add(new \PHPFUI\Header($title, 4));
			$container->add($this->getInformationFieldSet($this->getEvent()));
			$container->add(new \PHPFUI\Button($buttonText, "/Events/confirm/{$reservation->reservationId}"));
			}

		return $container;
		}

	public function edit(\App\Record\Event $event) : \PHPFUI\Form
		{
		if ($event->eventId ?? 0)
			{
			$submit = new \PHPFUI\Submit();
			$form = new \PHPFUI\Form($this->page, $submit);
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add', 'action');
			$event->organizer = \App\Model\Session::signedInMemberId();
			$form = new \PHPFUI\Form($this->page);
			}

		if ($form->isMyCallback())
			{
			$_POST['eventId'] = $event->eventId;

			foreach (['information', 'location', 'additionalInfo'] as $field)
				{
				$_POST[$field] = \App\Tools\TextHelper::cleanUserHtml($_POST[$field]);
				}
			$event->setFrom($_POST);
			$event->update();
			$this->page->setResponse('Saved');

			return $form;
			}

		$lteValidator = new \PHPFUI\Validator\LTE();
		$gteValidator = new \PHPFUI\Validator\GTE();

		$this->page->addAbideValidator($lteValidator)->addAbideValidator($gteValidator);

		$dateFields = new \PHPFUI\FieldSet('Dates');

		$dates = new \PHPFUI\MultiColumn();
		$eventDate = new \PHPFUI\Input\Date($this->page, 'eventDate', 'Event Date', $event->eventDate);
		$eventDate->setMinDate(\App\Tools\Date::todayString());
		$eventDate->setRequired()->setToolTip('The date the meeting is happening');
		$dates->add($eventDate);

		$publicDate = new \PHPFUI\Input\Date($this->page, 'publicDate', 'Public Date', $event->publicDate);
		$publicDate->setMinDate(\App\Tools\Date::todayString(-1));
		$publicDate->setToolTip('The date this event will be made public. Registration can open after that.');
		$dates->add($publicDate);

		$registrationStartDate = new \PHPFUI\Input\Date($this->page, 'registrationStartDate', 'Registration Start Date', $event->registrationStartDate);
		$registrationStartDate->setMinDate(\App\Tools\Date::todayString(-1));
		$registrationStartDate->setToolTip('The first date people can register for this event.');
		$dates->add($registrationStartDate);

		$lastRegistrationDate = new \PHPFUI\Input\Date($this->page, 'lastRegistrationDate', 'Last Prereg Date', $event->lastRegistrationDate);
		$lastRegistrationDate->setMinDate(\App\Tools\Date::todayString(-1));
		$lastRegistrationDate->setRequired()->setToolTip('The last date (til midnight) people can preregister for the event.');
		$dates->add($lastRegistrationDate);

		$lastRegistrationDate->setValidator($lteValidator, 'Must be less than or equal to Event Date', $eventDate->getId());
		$registrationStartDate->setValidator($lteValidator, 'Must be less than or equal to Last Registration Date', $lastRegistrationDate->getId());
		$publicDate->setValidator($lteValidator, 'Must be less than or equal to Event Date', $eventDate->getId());
		$eventDate->setValidator($gteValidator, 'Must be greater or equal to Last Registration Date', $lastRegistrationDate->getId());

		$dateFields->add($dates);

		$form->add($dateFields);
		$infoFields = new \PHPFUI\FieldSet('Event Information');
		$title = new \PHPFUI\Input\Text('title', 'Event Title', $event->title);
		$title->setRequired()->setToolTip('The title of the event, keep it short and concise. No need to include e date, as it will always be shown with a date and day of week.');
		$infoFields->add($title);
		$organizer = new \PHPFUI\Input\SelectAutoComplete($this->page, 'organizer', 'Event Organizer');
		$organizer->setRequired();
		$memberTable = new \App\Table\Member();
		$memberTable->setMembersWithPermission('Event Coordinator');

		foreach ($memberTable->getDataObjectCursor() as $member)
			{
			$organizer->addOption("{$member->firstName} {$member->lastName}", $member->memberId, $member->memberId == $event->organizer);
			}
		$infoFields->add($organizer);
		$membersOnly = new \PHPFUI\Input\RadioGroup('membersOnly', 'Membership Requirements', (string)$event->membersOnly);
		$membersOnly->setSeparateRows(false);
		$membersOnly->addButton('Public', (string)\App\Table\Event::PUBLIC);
		$membersOnly->addButton('Members Only', (string)\App\Table\Event::MEMBERS_ONLY);
		$membersOnly->addButton('Include Membership', (string)\App\Table\Event::FREE_MEMBERSHIP);
		$membersOnly->addButton('Charge Membership', (string)\App\Table\Event::PAID_MEMBERSHIP);
		$membersOnly->setToolTip('Select membership requirement: Public (no membership needed), Members Only (must be an existing member), Include (free) membership, Charge (for a new) Membership.');
		$infoFields->add($membersOnly);
		$form->add($infoFields);
		$pricingFields = new \PHPFUI\FieldSet('Pricing');
		$price = new \PHPFUI\Input\Number('price', 'Price', $event->price);
		$numbers = new \PHPFUI\MultiColumn();
		$numbers->add($price);
		$maxReservations = new \PHPFUI\Input\Number('maxReservations', 'Max Reservations Allowed', $event->maxReservations);
		$numbers->add($maxReservations);
		$numberReservations = new \PHPFUI\Input\Number('numberReservations', 'Max People per Reservation', $event->numberReservations);
		$numbers->add($numberReservations);
		$pricingFields->add($numbers);

		$newMembers = new \PHPFUI\MultiColumn();
		$newMemberDiscount = new \PHPFUI\Input\Number('newMemberDiscount', 'New Member Discount', $event->newMemberDiscount);
		$newMemberDiscount->setToolTip('Dollar amount of the discount for members who joined after the following date.');
		$newMembers->add($newMemberDiscount);
		$newMemberDate = new \PHPFUI\Input\Date($this->page, 'newMemberDate', 'Members Joined Since', $event->newMemberDate);
		$newMemberDate->setToolTip('New member discount applies to members who join at or after this date.');
		$newMembers->add($newMemberDate);
		$maxDiscounts = new \PHPFUI\Input\Number('maxDiscounts', 'Max Discounts', $event->maxDiscounts);
		$maxDiscounts->setToolTip('Number of times a new member can get a discount (normally 1)');
		$newMembers->add($maxDiscounts);
		$pricingFields->add($newMembers);

		$pricingFields->add(new \PHPFUI\Header('Payment Types Allowed', 6));
		$checkboxes = new \PHPFUI\MultiColumn();
		$checkboxes->add(new \PHPFUI\Input\CheckBoxBoolean('paypal', 'PayPal', (bool)$event->paypal));
		$checkboxes->add(new \PHPFUI\Input\CheckBoxBoolean('checks', 'Check', (bool)$event->checks));
		$checkboxes->add(new \PHPFUI\Input\CheckBoxBoolean('door', 'Door', (bool)$event->door));
		$pricingFields->add($checkboxes);
		$form->add($pricingFields);
		$timeFields = new \PHPFUI\FieldSet('Event Times');
		$times = new \PHPFUI\MultiColumn();
		$startTime = new \PHPFUI\Input\Text('startTime', 'Start Time', $event->startTime);
		$times->add($startTime);
		$endTime = new \PHPFUI\Input\Text('endTime', 'End Time', $event->endTime);
		$times->add($endTime);
		$timeFields->add($times);
		$form->add($timeFields);
		$generalFields = new \PHPFUI\FieldSet('Event General Information');
		$information = new \PHPFUI\Input\TextArea('information', 'General Information', \str_replace("\n", '<div></div>', $event->information ?? ''));
		$information->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$generalFields->add($information);
		$location = new \PHPFUI\Input\TextArea('location', 'Location of Venue', \str_replace("\n", '<div></div>', $event->location ?? ''));
		$location->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$generalFields->add($location);
		$additionalInfo = new \PHPFUI\Input\TextArea('additionalInfo', 'Directions to Venue', \str_replace("\n", '<div></div>', $event->additionalInfo ?? ''));
		$additionalInfo->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$generalFields->add($additionalInfo);
		$directionsUrl = new \PHPFUI\Input\Url('directionsUrl', 'Directions Link', $event->directionsUrl);
		$generalFields->add($directionsUrl);
		$form->add($generalFields);
		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);

		if ($event->eventId ?? 0)
			{
			$clone = new \PHPFUI\Button('Clone', '/Events/clone/' . $event->eventId);
			$clone->addClass('secondary');
			$buttonGroup->addButton($clone);
			}
		$cancel = new \PHPFUI\Button('Cancel', '/Events/manage/My');
		$cancel->addClass('hollow')->addClass('secondary');
		$buttonGroup->addButton($cancel);
		$form->add($buttonGroup);
		$form->add(new \PHPFUI\FormError());

		return $form;
		}

	public function getContactLink(\App\Record\Event $event) : string | \PHPFUI\Link
		{
		$organizer = new \App\Record\Member($event->organizer);

		if (! $organizer->loaded())
			{
			return '';
			}

		return \PHPFUI\Link::email($organizer['email'], $organizer->fullName(), $event->title);
		}

	public function getEvent() : \App\Record\Event
		{
		return $this->event;
		}

	public function getInformationFieldSet(\App\Record\Event $event, int $reservationCount = 0) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet($event->title);
		$fieldSet->add(new \App\UI\Display('Event Date', \App\Tools\Date::formatString('l, F d, Y', $event->eventDate)));
		$organizer = new \App\Record\Member($event->organizer);

		if ($organizer->loaded())
			{
			$fieldSet->add(new \App\UI\Display('Organizer', $organizer->fullName()));
			}

		$price = \App\Model\Event::getActualPrice($event);
		$price = $price ? '$' . \number_format($price, 2) : 'FREE! Reservations encouraged!';

		$fieldSet->add(new \App\UI\Display('Price', $price));

		if ($reservationCount)
			{
			$attending = $reservationCount . ' are currently signed up. ';

			if ($event->maxReservations > 0)
				{
				$attending .= " (Guest capacity {$event->maxReservations})";
				}
			$fieldSet->add(new \App\UI\Display('Currently Attending', $attending));
			}
		$fieldSet->add(new \App\UI\Display('Start Time', $event->startTime));
		$fieldSet->add(new \App\UI\Display('End Time', $event->endTime));

		if ($event->information)
			{
			$fieldSet->add(new \App\UI\Display('General Information', $event->information));
			}

		if ($event->location)
			{
			$fieldSet->add(new \App\UI\Display('Location', $event->location));
			}

		if ($event->additionalInfo)
			{
			$fieldSet->add(new \App\UI\Display('Directions', $event->additionalInfo));
			}

		if ($event->directionsUrl)
			{
			$fieldSet->add(new \App\UI\Display('Link', new \PHPFUI\Link($event->directionsUrl)));
			}

		return $fieldSet;
		}

	public function getSelect(int $limit = 10) : \PHPFUI\Input\Select
		{
		$select = new \PHPFUI\Input\Select('eventId', 'Test Against Event');
		$select->setToolTip('Select an event to test in the email');
		$events = $this->eventTable->getMostRecentRegistered($limit);

		foreach ($events as $event)
			{
			$select->addOption($event->title, $event->eventId);
			}

		return $select;
		}

	public function listEvents(\App\Table\Event $eventTable) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (! \count($eventTable))
			{
			$container->add(new \PHPFUI\Header("You don't have any events", 4));
			$container->add(new \PHPFUI\Button('Add Event', '/Events/edit/0'));

			return $container;
			}

		$searchableHeaders = ['eventDate' => 'Date', 'title' => 'Title', ];
		$normalHeaders = ['attendees' => 'Attendees', 'links' => 'Links'];

		$view = new \App\UI\ContinuousScrollTable($this->page, $eventTable);
		$view->setRecordId('eventId');

		$delete = new \PHPFUI\AJAX('deleteEvent', 'Permanently delete this event?');
		$delete->addFunction('success', "$('#eventId-'+data.response).css('background-color','red').hide('fast')");
		$this->page->addJavaScript($delete->getPageJS());
		$view->addCustomColumn('title', static fn (array $event) => new \PHPFUI\Link('/Events/edit/' . $event['eventId'], $event['title'] ?? 'Missing', false));
		$view->addCustomColumn('attendees', static function(array $event) use ($delete)
			{
			$eventId = $event['eventId'];

			if ($event['attendees'])
				{
				$icon = new \PHPFUI\FAIcon('fas', 'users');

				return new \PHPFUI\Link('/Events/attendees/' . $eventId, "{$icon} ({$event['attendees']})", false);
				}

				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute(['eventId' => $eventId]));
				$addIcon = new \PHPFUI\FAIcon('fas', 'plus', '/Events/addReservation/' . $eventId);

				return $icon . $addIcon;
			});
		$page = $this->page;
		$view->addCustomColumn('links', static function(array $event) use ($page)
			{
			$icon = new \PHPFUI\FAIcon('fas', 'link');
			$reveal = new \PHPFUI\Reveal($page, $icon);
			$table = new \PHPFUI\Table();
			$table->setHeaders(['l' => 'Link for Users', 'c' => 'Copy HTML To Clipboard', 'p' => 'Plain Copy']);

			$url = $page->value('homePage') . '/Events/signUp/' . $event['eventId'];
			$link = new \PHPFUI\Link($url, 'Sign up for ' . $event['title']);

			$copyIcon = new \PHPFUI\FAIcon('far', 'clipboard');
			$callout = new \PHPFUI\HTML5Element('span');
			$callout->add('Copied!');
			$callout->addClass('callout success small');
			$page->addCopyToClipboard("{$link}", $copyIcon, $callout);

			$plainCopyIcon = new \PHPFUI\FAIcon('far', 'clipboard');
			$plainCallout = new \PHPFUI\HTML5Element('span');
			$plainCallout->add('Copied!');
			$plainCallout->addClass('callout success small');
			$page->addCopyToClipboard($url, $plainCopyIcon, $plainCallout);

			$table->addRow(['l' => $link, 'c' => $copyIcon . $callout, 'p' => $plainCopyIcon . $plainCallout]);
			$reveal->add($table);

			return $icon;
			});

		$view->setSearchColumns($searchableHeaders)->setSortableColumns(\array_keys($searchableHeaders));
		$view->setHeaders(\array_merge($searchableHeaders, $normalHeaders));
		$container->add($view);

		return $container;
		}

	public function payInstructions(string $type, \App\Record\Invoice $invoice) : string | \PHPFUI\Container
		{
		$container = 'Invoice not found';

		if (! $invoice->empty() && $invoice->memberId == \App\Model\Session::getCustomerNumber())
			{
			$container = new \PHPFUI\Container();
			$invoiceItem = new \App\Record\InvoiceItem();
			$invoiceItem->read(['invoiceId' => $invoice->invoiceId, 'type' => \App\Model\Cart::TYPE_EVENT]);
			$this->setEvent(new \App\Record\Event($invoiceItem->storeItemId));
			$reservation = new \App\Record\Reservation($invoiceItem->storeItemDetailId);

			if ($reservation->loaded() && ($reservation->paymentId || 0 == $reservation->pricePaid))
				{
				$title = 'You are signed up for:';
				$buttonText = 'Registration Details';
				}
			else
				{
				$title = 'You still own a payment for the following:';
				$buttonText = 'Change Payment Type';
				}
			$container->add(new \PHPFUI\Header($title, 4));
			$container->add($this->getInformationFieldSet($this->getEvent()));
			$fieldSet = new \PHPFUI\FieldSet('Payment Instructions');
			$settingTable = new \App\Table\Setting();
			$reservationModel = new \App\Model\Reservation();
			$data = $reservationModel->getMergeData($reservation);
			$value = \App\Tools\TextHelper::processText($settingTable->value($type . 'Instructions'), $data);
			$fieldSet->add($value);
			$container->add($fieldSet);
			$container->add(new \PHPFUI\Button($buttonText, "/Events/confirm/{$reservation->reservationId}"));
			}

		return $container;
		}

	public function refund(\App\Record\Event $event, \App\Record\Reservation $reservation) : \PHPFUI\Form
		{
		$participantsUrl = "/Events/attendees/{$event->eventId}";
		$participantsButton = new \PHPFUI\Button('Event Participants', $participantsUrl);
		$submit = new \PHPFUI\Submit('Delete Reservation Now', 'delete');
		$submit->addClass('alert');
		$form = new \PHPFUI\Form($this->page);
		$form->add(new \PHPFUI\Input\Hidden('reservationId', (string)$reservation->reservationId));

		if ($reservation->loaded())
			{
			$attendees = $reservation->ReservationPersonChildren;

			if (\count($attendees))
				{
				$paymentTable = new \App\Table\Payment();
				$paymentTypes = $paymentTable->getPaymentTypes();
				$payment = $reservation->payment;
				$reservationModel = new \App\Model\Reservation();

				if ($payment->loaded())
					{
					if (\App\Model\Session::checkCSRF() && isset($_POST['refund']))
						{
						$message = $reservationModel->refund($event, $reservation);
						\App\Model\Session::setFlash('RefundMessage', $message);
						\App\Model\Session::setFlash('success', 'A refund has been requested');
						$this->page->done();
						$this->page->redirect();
						}
					else
						{
						$form->add('Are you sure you want to refund<p>');

						foreach ($attendees as $attendee)
							{
							$form->add("<br>{$attendee->firstName} {$attendee->lastName}");
							}
						$form->add("<p>a payment of \${$payment->amount} paid by {$paymentTypes[$payment->paymentType]} " .
								"payment number {$payment->paymentNumber} on " . $payment->dateReceived . '?<p>');
						$buttonGroup = new \App\UI\CancelButtonGroup();
						$refundButton = new \PHPFUI\Submit('Yes, Refund', 'refund');
						$refundButton->addClass('warning');
						$buttonGroup->addButton($refundButton);
						$buttonGroup->addButton($participantsButton);
						$form->add($buttonGroup);
						}
					}
				else
					{
					$refundMessage = \App\Model\Session::getFlash('RefundMessage');

					if ($refundMessage)
						{
						$fieldSet = new \PHPFUI\FieldSet('The following email has been sent:');
						$fieldSet->add($refundMessage);
						$form->add($fieldSet);
						$form->add(new \PHPFUI\Header('Registration is still active', 4));
						$buttonGroup = new \PHPFUI\ButtonGroup();
						$buttonGroup->addButton($submit);
						$buttonGroup->addButton($participantsButton);
						$form->add($buttonGroup);
						}
					elseif (\App\Model\Session::checkCSRF() && isset($_POST['delete']))
						{
						$reservationModel->delete($reservation);
						\App\Model\Session::setFlash('success', 'Reservation deleted');
						$this->page->done();
						$this->page->redirect($participantsUrl);
						}
					}
				}
			else
				{
				$form->add('No attendees for this reservation.');
				}
			}
		else
			{
			$form->add('No reservation');
			$this->page->done();
			$this->page->redirect($participantsUrl);
			}

		return $form;
		}

	public function reservations(\App\Record\Event $event) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$addButton = new \PHPFUI\Button('Add Reservation', '/Events/addReservation/' . $event->eventId);
		$buttonGroup->addButton($addButton);
		$emailButton = new \PHPFUI\Button('Email All', '/Events/email/' . $event->eventId);
		$emailButton->addClass('warning');
		$buttonGroup->addButton($emailButton);
		$signupButton = new \PHPFUI\Button('Signup Sheet');
		$signupButton->addClass('success');
		$this->signupSheetModal($event, $signupButton);
		$buttonGroup->addButton($signupButton);
		$nameTagButton = new \PHPFUI\Button('Name Tags', '/Events/nameTags/' . $event->eventId);
		$nameTagButton->addClass('secondary');
		$buttonGroup->addButton($nameTagButton);
		$container->add($buttonGroup);

		$this->reservationTable->setReservationsCursor($event);
		$view = new \App\UI\ContinuousScrollTable($this->page, $this->reservationTable);
		$view->setRecordId('reservationId');

		$sortableHeaders = ['reservationemail' => 'Registered email', 'firstName' => 'First Name', 'lastName' => 'Last Name', 'signedUpAt' => 'Signed Up At', 'pricePaid' => 'Paid', ];
		$normalHeaders = ['edit' => 'Edit', 'del' => 'Refund/<wbr>Delete', ];
		$view->setHeaders(\array_merge($sortableHeaders, $normalHeaders));
		$view->setSearchColumns($sortableHeaders);

		$view->setSortableColumns(\array_keys($sortableHeaders));

		$editColumn = new \App\Model\EditIcon($view, $this->reservationTable, '/Events/editReservation/');
		$view->addCustomColumn('reservationemail', static function(array $participant)
			{
			if ($participant['memberId'] > 0)
				{
				return "<a href='/Membership/email/{$participant['memberId']}'>{$participant['email']}</a>";
				}
			elseif (\filter_var($participant['reservationemail'], FILTER_VALIDATE_EMAIL))
				{
				return \PHPFUI\Link::email($participant['reservationemail']);
				}

			return '';
			});

		$delete = new \PHPFUI\AJAX('deleteReservation', 'Permanently delete this reservation?');
		$delete->addFunction('success', "$('#reservationId-'+data.response).css('background-color','red').hide('fast');location.reload();");
		$this->page->addJavaScript($delete->getPageJS());

		$paymentTypes = \App\Table\Payment::getPaymentTypes();
		$view->addCustomColumn('pricePaid', static function(array $participant) use ($paymentTypes)
			{
			$participant['pricePaid'] = (float)$participant['pricePaid'];
			$amount = '$' . \number_format($participant['pricePaid'], 2);

			if ($participant['pricePaid'] > 0.0 && ! $participant['paymentId'])
				{
				$amount .= ' Due (' . $paymentTypes[$participant['paymentType'] ?? 0] . ')';
				}

			return $amount;
			});

		$view->addCustomColumn('del', static function(array $participant) use ($delete)
			{
			if (! $participant['paymentId'])
				{
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute(['reservationId' => $participant['reservationId'] ?? 0]));

				return $icon;
				}

				$button = new \PHPFUI\Button('Refund', "/Events/refund/{$participant['eventId']}/{$participant['reservationId']}");
				$button->addClass('small warning');

				return $button;

			});

		$container->add($view);

		return $container;
		}

	public function setEvent(\App\Record\Event $event) : void
		{
		$this->event = $event;

		if (! isset($this->event->membersOnly) || 1 != $this->event->membersOnly)
			{
			$this->page->setPublic();
			}
		}

	public function show(\PHPFUI\ORM\DataObjectCursor $eventCursor, $noEventsMessage = 'There are no upcoming events') : \App\UI\Accordion | \PHPFUI\Header
		{
		if (! \count($eventCursor))
			{
			return new \PHPFUI\Header($noEventsMessage, 5);
			}
		$today = \App\Tools\Date::todayString();
		$accordion = new \App\UI\Accordion();

		foreach ($eventCursor as $event)
			{
			$row = new \PHPFUI\GridX();
			$title = new \PHPFUI\Cell(9);
			$title->add($event->title);
			$row->add($title);
			$date = new \PHPFUI\Cell(3);
			$date->add(\App\Tools\Date::formatString('l, F j', $event->eventDate));
			$row->add($date);
			$container = new \PHPFUI\Container();
			$container->add(\str_replace("\n", '<br>', (string)$event->information));
			$container->add('<p>');
			$memberId = \App\Model\Session::signedInMemberId();
			$reservation = new \App\Record\Reservation();

			if ($memberId)
				{
				$reservation->read(['eventId' => $event->eventId, 'memberId' => $memberId]);
				}
			$buttonGroup = new \PHPFUI\ButtonGroup();

			if ($reservation->loaded())
				{
				$buttonGroup->addButton(new \PHPFUI\Button('Confirm', '/Events/confirm/' . $reservation->reservationId));
				}
			elseif ($today <= $event->lastRegistrationDate)
				{
				if ($today >= $event->registrationStartDate)
					{
					$button = new \PHPFUI\Button('Register Now', '/Events/signUp/' . $event->eventId);
					}
				else
					{
					$button = new \PHPFUI\Button('Registration opens ' . \App\Tools\Date::formatString('D M j', $event->registrationStartDate));
					$button->addClass('hollow')->addClass('secondary');
					}
				$buttonGroup->addButton($button);
				}
			else
				{
				$button = new \PHPFUI\Button('Registration closed on ' . \App\Tools\Date::formatString('D M j', $event->lastRegistrationDate));
				$button->addClass('hollow');
				$buttonGroup->addButton($button);
				}

			if ($this->page->isAuthorized('Currently Registered') && $today >= $event->registrationStartDate)
				{
				$coming = new \PHPFUI\Button("Who's Coming?", '/Events/registered/' . $event->eventId);
				$buttonGroup->addButton($coming->addClass('secondary'));
				}
			$container->add($buttonGroup);
			$accordion->addTab($row, $container, true);
			}

		return $accordion;
		}

	public function signup() : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$customerModel = new \App\Model\Customer();
		$memberId = $customerModel->getNumber();
		$customer = $customerModel->read($memberId);

		if (\App\Model\Session::checkCSRF() && isset($_POST['submit']))
			{
			$reservation = new \App\Record\Reservation();
			$reservation->setFrom($customer->toArray());
			$reservation->reservationFirstName = $customer->firstName;
			$reservation->reservationLastName = $customer->lastName;
			$reservation->reservationemail = $customer->email;
			$reservation->eventId = $this->event->eventId;
			$reservation->memberId = $memberId;
			$reservation->insert();

			$count = (int)($_POST['currentReservations'] ?? 1);

			for ($i = 1; $i <= $count; ++$i)
				{
				if (isset($_POST['firstName'][$i]))
					{
					$reservationPerson = new \App\Record\ReservationPerson();
					$reservationPerson->reservation = $reservation;
					$reservationPerson->eventId = $reservation->eventId;
					$reservationPerson->firstName = $_POST['firstName'][$i];
					$reservationPerson->lastName = $_POST['lastName'][$i];
					$reservationPerson->email = $_POST['email'][$i];
					$reservationPerson->insert();
					}
				}
			$reservationModel = new \App\Model\Reservation();
			$reservationModel->updatePrices($reservation);
			$this->page->redirect("/Events/confirm/{$reservation->reservationId}");
			}
		else
			{
			$form->add($this->getInformationFieldSet($this->event));
			$reservations = $this->reservationTable->setReservationsCursor($this->event);

			if (\App\Tools\Date::todayString() > $this->event->lastRegistrationDate)
				{
				$fieldSet = new \PHPFUI\FieldSet('Preregistration is no longer available');
				$fieldSet->add('Please contact ' . $this->getContactLink($this->event) . ' for information');
				$form->add($fieldSet);
				}
			elseif ($this->event->maxReservations > 0 && \count($reservations) >= $this->event->maxReservations)
				{
				$fieldSet = new \PHPFUI\FieldSet('This event is sold out!');
				$fieldSet->add('Please contact ' . $this->getContactLink($this->event) . ' to see if they can fit you in.');
				$form->add($fieldSet);
				}
			else
				{
				$currentReservations = new \PHPFUI\Input\Hidden('currentReservations', (string)1);
				$currentReservationsId = $currentReservations->getId();
				$form->add($currentReservations);
				$numberReservations = new \PHPFUI\Input\Hidden('numberReservations', (string)$this->event->numberReservations);
				$numberReservationsId = $numberReservations->getId();
				$form->add($numberReservations);
				$form->add($this->getSignup('1', $customer->toArray()));
				$buttonGroup = new \App\UI\CancelButtonGroup();
				// insert before button bar
				$buttonGroupId = $buttonGroup->getId();
				$buttonGroup->addButton(new \PHPFUI\Submit('Sign Up'));

				if ($this->event->numberReservations > \count($reservations))
					{
					$addAttendee = new \PHPFUI\Button('Add Attendee');
					$addAttendee->addClass('success');
					$addAttendeeId = $addAttendee->getId();
					$addAttendee->setAttribute('onclick', 'return addAttendee();');
					$buttonGroup->addButton($addAttendee);
					$signupHtml = \str_replace(["'", "\n"], ['"', ''], $this->getSignup('~index~'));
					$js = <<<JAVASCRIPT
function addAttendee(){var numberReservations=$('#{$numberReservationsId}').val();
var currentReservations=$('#{$currentReservationsId}').val();var signupHtml='{$signupHtml}';
if(numberReservations>currentReservations){++currentReservations;
$('#{$currentReservationsId}').val(currentReservations);$('#{$buttonGroupId}').before(signupHtml.replace(/~index~/g, currentReservations));}
if(numberReservations==currentReservations){ $('#{$addAttendeeId}').hide();}return false;};
JAVASCRIPT;
					$this->page->addJavaScript($js);
					}
				$form->add($buttonGroup);
				}
			}

		return $form;
		}

	protected function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'Add':
						unset($_POST['eventId']);
						$event = new \App\Record\Event();
						$event->setFrom($_POST);
						$event->insert();
						$this->page->redirect('/Events/manage/My');

						break;

					case 'deleteEvent':
						$event = new \App\Record\Event((int)$_POST['eventId']);
						$event->delete();
						$this->page->setResponse($_POST['eventId']);

						break;

					case 'deleteReservation':
						$reservation = new \App\Record\Reservation((int)$_POST['reservationId']);
						$reservation->delete();
						$this->page->setResponse($_POST['reservationId']);

						break;
					}
				}
			}
		}

	protected function signupSheetModal(\App\Record\Event $event, \PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->add(new \PHPFUI\Header('Select the Signup Sheet format', 4));
		$buttonGroup = new \PHPFUI\ButtonGroup();
		$pdf = new \PHPFUI\Button('PDF', '/Events/signupSheet/Sheet/' . $event->eventId);
		$pdf->addClass('closeOnClick');
		$buttonGroup->addButton($pdf);
		$csv = new \PHPFUI\Button('CSV', '/Events/signupSheet/CSV/' . $event->eventId);
		$csv->addClass('closeOnClick');
		$this->page->addJavaScript('$("a.closeOnClick").click(function(){$("#' . $modal->getId() . '").foundation("reveal","close");});');
		$buttonGroup->addButton($csv);
		$modal->add($buttonGroup);
		}

	private function getSignup(string $index, array $member = []) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet("Attendee {$index}");
		$fieldSet->setId("FS{$index}");
		$multiColumn = new \PHPFUI\MultiColumn();
		$value = $member['firstName'] ?? '';
		$firstName = new \PHPFUI\Input\Text("firstName[{$index}]", 'First Name', $value);
		$firstName->setId("FN{$index}");
		$firstName->setRequired();
		$multiColumn->add($firstName);
		$value = $member['lastName'] ?? '';
		$lastName = new \PHPFUI\Input\Text("lastName[{$index}]", 'Last Name', $value);
		$lastName->setId("FN{$index}");
		$lastName->setRequired();
		$multiColumn->add($lastName);
		$fieldSet->add($multiColumn);
		$value = $member['email'] ?? '';
		$email = new \PHPFUI\Input\Email("email[{$index}]", 'email', $value);
		$email->setId("EM{$index}");
		$fieldSet->add($email);

		return $fieldSet;
		}
	}
