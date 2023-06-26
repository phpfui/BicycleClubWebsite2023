<?php

namespace App\View\Event;

class Registration
	{
	private bool $canAddAttendee = false;

	private bool $canAddPayment = false;

	private bool $canDeleteAttendee = false;

	private string $recordId = 'reservationPersonId';

	private readonly \App\Model\Reservation $reservationModel;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->reservationModel = new \App\Model\Reservation();
		}

	public function edit(\App\Record\Reservation $reservation, \App\Record\Event $event = new \App\Record\Event(), bool $required = true) : \PHPFUI\Form
		{
		$selfEditing = false;

		if ($reservation->loaded())
			{
			$eventId = $reservation->eventId;
			$selfEditing = \App\Model\Session::getCustomerNumber() == $reservation->memberId;
			}
		else
			{
			$eventId = $event->eventId;
			}

		if ($reservation->empty())
			{
			// no record yet, setup for a post and redirect
			$submit = new \PHPFUI\Submit('Add Event Registration', 'action');
			$form = new \PHPFUI\Form($this->page);
			}
		elseif ($reservation->pricePaid && empty($reservation->paymentId))
			{
			// no payment yet, do a post and redirect
			$submit = new \PHPFUI\Submit('Save', 'action');
			$form = new \PHPFUI\Form($this->page);
			$this->canAddPayment = ! $selfEditing;
			$this->canAddAttendee = ! $selfEditing;
			$this->canDeleteAttendee = ! $selfEditing;
			}
		else
			{
			// already have payment
			$submit = new \PHPFUI\Submit();
			$form = new \PHPFUI\Form($this->page, $submit);
			$this->canAddPayment = false;
			$this->canAddAttendee = ! $selfEditing;
			$this->canDeleteAttendee = ! $selfEditing;
			}

		if (! $reservation->paymentId)
			{
			$this->canAddAttendee = true;
			$this->canDeleteAttendee = true;
			}

		if ($event->loaded())
			{
			$form->add(new \PHPFUI\Header($event->title, 4));
			}

		if ($form->isMyCallback())
			{
			$this->saveForm($reservation);
			$this->page->setResponse('Saved');
			}
		elseif (\App\Model\Session::checkCSRF() && isset($_REQUEST['submit']))
			{
			$this->saveForm($reservation);
			$paymentOption = $_POST['paymentOption'] ?? 'payPal';
			$invoiceId = $this->reservationModel->checkout($reservation);
			$paymentOption = \strtolower((string)$paymentOption);

			if ($invoiceId)
				{
				$url = "/Events/{$paymentOption}/{$invoiceId}";
				$this->page->redirect($url);
				}
			else
				{
				$this->page->redirect('/Events/confirmed/' . $reservation->reservationId);
				}
			}
		elseif (\App\Model\Session::checkCSRF() && isset($_REQUEST['action']))
			{
			switch ($_REQUEST['action'])
				{
				case 'Save':
					$this->saveForm($reservation);
					$this->page->redirect();

					break;


				case 'Add Payment':
					if ($this->canAddPayment)
						{
						unset($_POST['paymentId']);
						$payment = new \App\Record\Payment();
						$payment->setFrom($_POST);
						$payment->dateReceived = \App\Tools\Date::todayString();
						$payment->enteringMemberNumber = \App\Model\Session::signedInMemberId();
						$reservation->payment = $payment;
						$reservation->update();
						}
					$this->page->redirect();

					break;

				case 'Add Event Registration':

					unset($_POST['reservationId']);
					$_POST['eventId'] = $eventId;
					$reservation = new \App\Record\Reservation();
					$reservation->setFrom($_POST);
					$reservation->memberId = 0;
					$person = new \App\Record\ReservationPerson();
					$person->eventId = $eventId;
					$person->reservation = $reservation;
					$person->email = $_POST['reservationemail'];
					$person->firstName = $_POST['reservationFirstName'];
					$person->lastName = $_POST['reservationLastName'];
					$person->insert();
					$this->reservationModel->updatePrices($reservation);
					$this->page->redirect('/Events/attendees/' . $eventId);

					break;


				case 'deleteAttendee':

					if ($this->canDeleteAttendee)
						{
						$this->reservationModel->deleteAttendee($reservation, new \App\Record\ReservationPerson((int)$_GET['reservationPersonId']));
						}
					$this->page->redirect($this->page->getBaseURL());

					break;


				case 'Add Attendee':

					if ($this->canAddAttendee)
						{
						$reservationPerson = new \App\Record\ReservationPerson();
						$reservationPerson->setFrom($_POST);
						$reservationPerson->insert();
						}
					$reservation = new \App\Record\Reservation((int)$_POST['reservationId']);
					$this->reservationModel->updatePrices($reservation);
					$this->page->redirect();

					break;


				default:

					$this->page->redirect();

				}
			}
		else
			{
			$billing = new \PHPFUI\FieldSet('Billing Information');
			$multiColumn = new \PHPFUI\MultiColumn();
			$firstName = new \PHPFUI\Input\Text('reservationFirstName', 'First Name', $reservation->reservationFirstName);
			$firstName->setRequired();
			$multiColumn->add($firstName);
			$lastName = new \PHPFUI\Input\Text('reservationLastName', 'Last Name', $reservation->reservationLastName);
			$lastName->setRequired();
			$multiColumn->add($lastName);

			$billing->add($multiColumn);
			$multiColumn = new \PHPFUI\MultiColumn();
			$address = new \PHPFUI\Input\Text('address', 'Street Address', $reservation->address);
			$multiColumn->add($address);
			$town = new \PHPFUI\Input\Text('town', 'Town', $reservation->town);
			$multiColumn->add($town);
			$billing->add($multiColumn);

			$multiColumn = new \PHPFUI\MultiColumn();
			$state = new \PHPFUI\Input\Text('state', 'State', $reservation->state);
			$multiColumn->add($state);
			$zip = new \PHPFUI\Input\Zip($this->page, 'zip', 'Zip', $reservation->zip);
			$multiColumn->add($zip);
			$multiColumn->add(new \App\UI\Display('Price', '$' . \number_format($reservation->pricePaid, 2)));
			$billing->add($multiColumn);
			$billing->add(new \PHPFUI\Input\Tel($this->page, 'phone', 'Phone', $reservation->phone));
			$email = new \PHPFUI\Input\Email('reservationemail', 'email', $reservation->reservationemail);
			$billing->add($email);
			$billing->add(new \PHPFUI\Input\Text('comments', 'Comments to the organizer', $reservation->comments));
			$form->add($billing);

			$payment = $reservation->payment;
			$form->add(new \PHPFUI\Input\Hidden('paymentId', (string)$payment->paymentId));

			if ($required && $event->price)
				{
				$address->setRequired();
				$town->setRequired();
				$state->setRequired();
				$zip->setRequired();
				$email->setRequired();
				}

			$paymentInfo = new \PHPFUI\FieldSet('Payment Information');
			$mustPay = true;

			if ($this->canAddPayment)
				{
				if ($reservation->pricePaid > 0)
					{
					if ($reservation->paymentId)
						{
						$paymentInfo->add($this->getPaymentFields($payment, $reservation->paymentId > 0));
						}
					else
						{
						$addPaymentButton = new \PHPFUI\Button('Add Payment');
						$addPaymentButton->addClass('success');
						$paymentInfo->add($addPaymentButton);

						$reveal = new \PHPFUI\Reveal($this->page, $addPaymentButton);
						$submit = new \PHPFUI\Submit('Add Payment', 'action');

						$revealForm = new \PHPFUI\Form($this->page);
						$revealForm->add(new \App\UI\Display('Total Owed', '$' . \number_format($reservation->pricePaid, 2)));
						$revealForm->add($this->getPaymentFields($payment, (bool)$reservation->paymentId));
						$revealForm->add($reveal->getButtonAndCancel($submit));
						$fieldSet = new \PHPFUI\FieldSet('Add Payment Information');
						$fieldSet->add($revealForm);
						$reveal->add($fieldSet);
						}
					}
				else
					{
					$mustPay = false;
					$paymentInfo->add('This event is free, no payment required.');
					}
				}
			else
				{
				$mustPay = false;

				if ($reservation->paymentId)
					{
					$paymentInfo->add($this->getPaymentFields($payment, $reservation->paymentId > 0));
					}
				}

			$reservationPersonTable = new \App\Table\ReservationPerson();
			$condition = new \PHPFUI\ORM\Condition('reservationId', $reservation->reservationId);

			if ($reservation->eventId)
				{
				$condition->and('eventId', $reservation->eventId);
				}
			$reservationPersonTable->setWhere($condition);

			$personCount = \count($reservationPersonTable);

			if ($personCount && \count($paymentInfo) > 1)
				{
				$form->add($paymentInfo);
				}

			$table = new \PHPFUI\Table();
			$table->setRecordId($this->recordId);
			$table->addHeader('firstName', 'First Name');
			$table->addHeader('lastName', 'Last Name');
			$table->addHeader('email', 'email');

			if ($this->canDeleteAttendee && $personCount > 1)
				{
				$table->addHeader('delete', 'Del');
				}

			foreach ($reservationPersonTable->getRecordCursor() as $record)
				{
				$row = $record->toArray();
				$id = $row[$this->recordId];
				$firstName = new \PHPFUI\Input\Text("firstName[{$id}]", '', $record->firstName);
				$firstName->setRequired();
				$hidden = new \PHPFUI\Input\Hidden("{$this->recordId}[{$id}]", $id);
				$row['firstName'] = $firstName . $hidden;
				$row['email'] = new \PHPFUI\Input\Email("email[{$id}]", '', $record->email);
				$lastName = new \PHPFUI\Input\Text("lastName[{$id}]", '', $record->lastName);
				$lastName->setRequired();
				$row['lastName'] = $lastName;

				if ($this->canDeleteAttendee)
					{
					$icon = new \PHPFUI\FAIcon('far', 'trash-alt', "?action=deleteAttendee&reservationPersonId={$id}&csrf=" . \App\Model\Session::csrf());
					$icon->setConfirm('Permanently delete this attendee?');
					$row['delete'] = $icon;
					}

				$table->addRow($row);
				}

			$form->add($table);

			if (! \App\Model\Session::isSignedIn())
				{
				$settingTable = new \App\Table\Setting();

				if (\App\Table\Event::FREE_MEMBERSHIP == $event->membersOnly)
					{	// includes membership

					$fieldSet = new \PHPFUI\FieldSet('Free Membership Included');
					$fieldSet->add('This event includes a free membership in ' . $settingTable->value('clubName') . ' good for one year. ');
					$fieldSet->add('We will use your billing information as your address and send a new password to you once we receive payment.');
					$fieldSet->add('<br><br>If you are already a member, please sign in here: ');
					$fieldSet->add(new \PHPFUI\Button('Sign In', '/Events/signUpMember/' . $eventId));
					$alert = new \PHPFUI\Panel($fieldSet);
					$alert->setCallOut();
					$form->add($alert);
					}
				elseif (\App\Table\Event::PAID_MEMBERSHIP == $event->membersOnly)
					{	// requires membership

					$fieldSet = new \PHPFUI\FieldSet('Membership Is Required');
					$fieldSet->add('This event requires membership in ' . $settingTable->value('clubName') . '. ');
					$fieldSet->add('We have added the appropriate membership fee for the number of reservations you are requesting in the price above. ');
					$fieldSet->add('We will also use your billing information as your address and send a new password to you once we receive payment. ');
					$fieldSet->add('You can change things once you sign in if needed.');
					$fieldSet->add('<br><br>If you are already a member, please sign in here: ');
					$fieldSet->add(new \PHPFUI\Button('Sign In', '/Events/signUpMember/' . $eventId));
					$alert = new \PHPFUI\Panel($fieldSet);
					$alert->setCallOut();
					$form->add($alert);
					}
				}

			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton($submit);

			if ($reservation->reservationId && $this->canAddAttendee && $personCount < $event->numberReservations)
				{
				$add = new \PHPFUI\Button('Add Attendee');
				$add->addClass('success');
				$form->add(new \PHPFUI\Input\Hidden('reservationId', (string)$reservation->reservationId));
				$form->saveOnClick($add);
				$this->addModal($reservation, $add);
				$buttonGroup->addButton($add);
				}

			if ($personCount)
				{
				if ($mustPay)
					{
					$checkout = new \PHPFUI\Submit('Checkout And Pay');
					$checkout->addClass('success');
					$buttonGroup->addButton($checkout);
					}
				elseif (! $reservation->paymentId && $selfEditing)
					{
					$confirm = new \PHPFUI\Submit('Confirm Reservation');
					$confirm->addClass('success');
					$buttonGroup->addButton($confirm);
					}
				}

			if ($reservation->reservationId)
				{
				$cancelButton = new \PHPFUI\Button('Cancel', '/Events/cancelUnpaid/' . $reservation->reservationId);
				}
			else
				{
				$cancelButton = new \PHPFUI\Button('Cancel', '/Events');
				}
			$cancelButton->addClass('hollow')->addClass('alert');
			$buttonGroup->addButton($cancelButton);

			$form->add($buttonGroup);
			}

		return $form;
		}

	private function addModal(\App\Record\Reservation $reservation, \PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Add Attendee');
		$fieldSet->add(new \PHPFUI\Input\Hidden('reservationId', (string)$reservation->reservationId));
		$fieldSet->add(new \PHPFUI\Input\Hidden('eventId', (string)$reservation->eventId));
		$firstName = new \PHPFUI\Input\Text('firstName', 'First Name');
		$firstName->setRequired();
		$fieldSet->add($firstName);
		$lastName = new \PHPFUI\Input\Text('lastName', 'Last Name');
		$lastName->setRequired();
		$fieldSet->add($lastName);
		$fieldSet->add(new \PHPFUI\Input\Email('email', 'email'));
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Add Attendee', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function getPaymentFields(\App\Record\Payment $payment, bool $readOnly) : \PHPFUI\MultiColumn
		{
		$paymentTypes = \App\Table\Payment::getPaymentTypes();
		$paymentType = new \PHPFUI\Input\Select('paymentType', 'Payment Type');

		foreach ($paymentTypes as $index => $type)
			{
			$paymentType->addOption($type, $index, $index == $payment->paymentType);
			}

		$checkNumber = new \PHPFUI\Input\Text('paymentNumber', 'Payment Number', $payment->paymentNumber);
		$checkNumber->setToolTip('Check number, PayPal transaction id, money order number, or blank for cash.');
		$checkDate = new \PHPFUI\Input\Date($this->page, 'paymentDated', 'Payment Dated', $payment->paymentDated);
		$checkAmount = new \PHPFUI\Input\Text('amount', 'Payment Amount', \number_format($payment->amount ?? 0.0, 2));
		$multiColumn = new \PHPFUI\MultiColumn();

		if ($readOnly)
			{
			$paymentType->setAttribute('disabled');
			$checkNumber->setAttribute('readonly');
			$checkDate->setAttribute('readonly');
			$checkAmount->setAttribute('readonly');
			}

		$multiColumn->add($paymentType);
		$multiColumn->add($checkNumber);
		$multiColumn->add($checkDate);
		$multiColumn->add($checkAmount);

		return $multiColumn;
		}

	private function saveForm(\App\Record\Reservation $reservation) : void
		{
		$_POST['eventId'] = $reservation->eventId;
		$_POST['reservationId'] = $reservation->reservationId;
		$reservation->setFrom($_POST);
		$reservation->update();

		if (isset($_POST[$this->recordId]) && \is_array($_POST[$this->recordId]))
			{
			$reservationPersonTable = new \App\Table\ReservationPerson();
			$reservationPersonTable->updateFromTable($_POST);
			}

		$this->reservationModel->updatePrices($reservation);
		}
	}
