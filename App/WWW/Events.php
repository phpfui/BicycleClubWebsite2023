<?php

namespace App\WWW;

class Events extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Table\Reservation $reservationTable;

	private readonly \App\View\Event\Events $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Event\Events($this->page);
		$this->reservationTable = new \App\Table\Reservation();
		}

	public function addReservation(\App\Record\Event $event = new \App\Record\Event()) : void
		{
		if ($this->page->addHeader('Add Reservation'))
			{
			$view = new \App\View\Event\Registration($this->page);
			$this->page->addPageContent($view->edit(new \App\Record\Reservation(), $event, false));
			}
		}

	public function attendees(\App\Record\Event $event = new \App\Record\Event()) : void
		{
		if ($this->page->addHeader('Event Participants'))
			{
			if ($event->loaded())
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader($event->title . ' ' . $event->eventDate));
				$this->page->addPageContent($this->view->reservations($event));
				}
			else
				{
				$this->page->addPageContent('Event not found');
				}
			}
		}

	public function cancelUnpaid(\App\Record\Reservation $reservation = new \App\Record\Reservation()) : void
		{
		if ($reservation->memberId == \App\Model\Session::signedInMemberId() && ! $reservation->paymentId && ! $reservation->invoiceId)
			{
			$reservation->delete();
			$this->page->redirect('/Events');
			}
		elseif ($reservation->memberId != \App\Model\Session::signedInMemberId())
			{
			$this->page->addPageContent(new \PHPFUI\Header('This reservation can not be cancelled'));
			$this->page->addPageContent(new \PHPFUI\SubHeader("You can not cancel another person's reservation"));
			}
		elseif ($reservation->paymentId)
			{
			$this->page->addPageContent(new \PHPFUI\Header('This reservation can not be cancelled'));
			$this->page->addPageContent(new \PHPFUI\SubHeader('This reservation has an associated payment'));
			$this->page->addPageContent(new \PHPFUI\Header('Please contact the event organizer to request a refund', 5));
			}
		else // corrent signed in member, invoice but no payment, cancel
			{
			$invoiceModel = new \App\Model\Invoice();
			$invoiceModel->delete($reservation->invoice);
			$this->page->redirect('/Events');
			}
		}

	public function checks(\App\Record\Invoice $invoice = new \App\Record\Invoice()) : void
		{
		if ($this->page->addHeader('Pay By Check'))
			{
			$this->page->addPageContent($this->view->payInstructions('checks', $invoice));
			}
		}

	public function clone(\App\Record\Event $event = new \App\Record\Event()) : void
		{
		if ($event->loaded() && $this->page->addHeader('Clone Event'))
			{
			$event->eventId = 0;
			$this->page->addPageContent($this->view->edit($event));
			}
		else
			{
			$this->page->notAuthorized();
			}
		}

	public function confirm(\App\Record\Reservation $reservation = new \App\Record\Reservation()) : void
		{
		$this->view->setEvent($reservation->event);

		if ($this->page->addHeader('Confirm My Reservation'))
			{
			if ($reservation->loaded() && $reservation->memberId == \App\Model\Session::getCustomerNumber())
				{
				$view = new \App\View\Event\Registration($this->page);
				$this->page->addPageContent($view->edit($reservation, $reservation->event));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\Header('Reservation not found', 4));
				}
			}
		}

	public function confirmed(\App\Record\Reservation $reservation = new \App\Record\Reservation()) : void
		{
		$html = $this->view->confirmed($reservation);

		if ($this->page->addHeader('Reservation Confirmed'))
			{
			$this->page->addPageContent($html);
			}
		}

	public function door(\App\Record\Invoice $invoice = new \App\Record\Invoice()) : void
		{
		if ($this->page->addHeader('Pay At Door'))
			{
			$this->page->addPageContent($this->view->payInstructions('door', $invoice));
			}
		}

	public function edit(\App\Record\Event $event = new \App\Record\Event()) : void
		{
		$title = $event->loaded() ? 'Edit Event' : 'Add Event';

		if ($this->page->addHeader($title))
			{
			$this->page->addPageContent($this->view->edit($event));
			}
		}

	public function editReservation(\App\Record\Reservation $reservation = new \App\Record\Reservation()) : void
		{
		if ($this->page->addHeader('Edit Reservation'))
			{
			if (! $reservation->loaded())
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Event not found'));
				}
			else
				{
				$view = new \App\View\Event\Registration($this->page);
				$this->page->addPageContent($view->edit($reservation, $reservation->event));
				}
			}
		}

	public function email(\App\Record\Event $event = new \App\Record\Event()) : void
		{
		if ($this->page->addHeader('Email Event Participants'))
			{
			$this->page->addPageContent(new \App\View\Email\Event($this->page, $event));
			}
		}

	public function manage(string $type = 'My') : void
		{
		if ($this->page->addHeader("Manage {$type} Events"))
			{
			$eventTable = new \App\Table\Event();
			$eventTable->setEventAttendeeCountCursor();

			if ('All' != $type)
				{
				$eventTable->setWhere(new \PHPFUI\ORM\Condition('organizer', \App\Model\Session::signedInMemberId()));
				}

			$this->page->addPageContent($this->view->listEvents($eventTable));
			}
		}

	public function messages(string $type = '') : void
		{
		$types = \App\Model\Event::getEmailTypes();

		if (isset($types[$type]))
			{
			if ($this->page->addHeader($types[$type] . ' Messages'))
				{
				$member = new \App\Record\Member();
				$memberFields = $member->getFields();
				$membership = new \App\Record\Membership();
				$membershipFields = $membership->getFields();
				$event = new \App\Record\Event();
				$eventsFields = $event->getFields();
				$reservation = new \App\Record\Reservation();
				$reservationFields = $reservation->getFields();
				$removedFields = ['lastLogin', 'password', 'verifiedEmail', 'acceptedWaiver', 'pendingLeader',
					'lastRegistrationDate', 'numberReservations', 'expires', 'pending', 'joined', 'lastRenewed',
					'membersOnly', 'organizer', 'paypal', 'door', 'checks', 'maxReservations', 'membershipId',
					'showNothing', 'showNoStreet', 'showNoTown', 'showNoPhone', 'emailAnnouncements',
					'volunteerPoints', 'rideJournal', 'newRideEmail', 'emergencyContact', 'emergencyPhone',
					'journal', 'license', 'deceased', 'emailNewsletter', 'allowedMembers', ];

				foreach ($removedFields as $field)
					{
					unset($eventsFields[$field], $memberFields[$field], $membershipFields[$field]);
					}
				$organizerFields = [];

				foreach (\array_keys($memberFields) as $field)
					{
					$organizerFields['organizer_' . $field] = 1;
					}

				foreach (\array_keys($membershipFields) as $field)
					{
					$organizerFields['organizer_' . $field] = 1;
					}
				$fields = \array_merge($memberFields, $membershipFields, $eventsFields, $reservationFields, $organizerFields);

				if ('Event' === $type)
					{
					$view = new \App\View\Event\MainMessage($this->page);
					}
				else
					{
					$view = new \App\View\Event\Messages($this->page);
					}
				$this->page->addPageContent($view->getEditor($type, $fields));
				}
			}
		else
			{
			if ($this->page->addHeader('Event Messages'))
				{
				$landingPage = new \App\UI\LandingPage($this->page);

				foreach ($types as $link => $header)
					{
					$landingPage->addLink("/Events/messages/{$link}", "{$header} Messages");
					}
				$this->page->addPageContent($landingPage);
				}
			}
		}

	public function my() : void
		{
		if ($this->page->addHeader('My Events'))
			{
			$eventTable = new \App\Table\Event();
			$signedUpForMember = $eventTable->getSignedUpForMember(\App\Model\Session::signedInMemberRecord());

			if (\count($signedUpForMember))
				{
				$this->page->addPageContent(new \PHPFUI\Header('You have a reservation for the following:', 4));
				$this->page->addPageContent($this->view->show($signedUpForMember));
				}

			$availableForMember = $eventTable->getAvailableForMember(\App\Model\Session::signedInMemberRecord());

			if (\count($availableForMember))
				{
				$this->page->addPageContent(new \PHPFUI\Header('You can sign up for the following:', 4));
				$this->page->addPageContent($this->view->show($availableForMember));
				}
			}
		}

	public function nameTags(\App\Record\Event $event = new \App\Record\Event()) : void
		{
		if ($this->page->addHeader('Print Name Tags'))
			{
			$this->page->addPageContent(new \App\View\Event\NameTags($this->page, $event));
			}
		}

	public function payPal(\App\Record\Invoice $invoice = new \App\Record\Invoice()) : void
		{
		if (! $invoice->loaded())
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Invoice Not Found'));

			return;
			}

		$this->page->setPublic();
		$unpaidBalance = $invoice->unpaidBalance();

		if ($unpaidBalance <= 0)
			{
			$this->page->redirect('/Store/paid/' . $invoice->invoiceId);
			}
		else
			{
			$invoiceItem = new \App\Record\InvoiceItem(['invoiceId' => $invoice->invoiceId, 'type' => \App\Enum\Store\Type::EVENT->value]);
			$reservation = new \App\Record\Reservation($invoiceItem->storeItemDetailId);

			$customerModel = new \App\Model\Customer();
			$customerId = $customerModel->getNumber();

			if ($invoice['memberId'] < 0)
				{
				$customer = $reservation->toArray();
				$customer['customerId'] = $customerId;
				$customer['firstName'] = $reservation->reservationFirstName;
				$customer['lastName'] = $reservation->reservationLastName;
				$customer['email'] = $reservation->reservationemail;
				// update customer info with reservation address info
				$customerModel->save($customer);
				}

			if ($invoice['memberId'] == $customerId)
				{
				$container = new \PHPFUI\HTML5Element('div');
				$container->add(new \PHPFUI\Header('Pay For Your Reservation'));
				$view = new \App\View\PayPal($this->page, new \App\Model\PayPal('Events'));
				$container->add($view->getPayPalLogo());
				$owe = '<p>You owe $' . $unpaidBalance . ' to complete this reservation.';
				$container->add($owe);
				$reservation->update();
				$container->add($view->getCheckoutForm($invoice, $container->getId(), 'Event Reservation'));
				$this->page->addPageContent($container);
				}
			}
		}

	public function refund(\App\Record\Event $event = new \App\Record\Event(), \App\Record\Reservation $reservation = new \App\Record\Reservation()) : void
		{
		if ($this->page->addHeader('Refund Participant'))
			{
			$this->page->addPageContent($this->view->refund($event, $reservation));
			}
		}

	public function registered(\App\Record\Event $event = new \App\Record\Event()) : void
		{
		if ($this->page->addHeader('Currently Registered'))
			{
			if ($event->loaded() && $event->showRegistered)
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader($event->title));
				$this->reservationTable->setReservationsCursor($event);
				$this->reservationTable->addOrderBy('lastName')->addOrderBy('firstName');
				$participantCursor = $this->reservationTable->getArrayCursor();
				$table = new \PHPFUI\Table();
				$table->addHeader('name', 'Name');

				if ($event->showComments)
					{
					$table->addHeader('comments', $event->commentTitle);
					}

				$attending = 0;
				$userSignedUp = false;

				foreach ($participantCursor as $participant)
					{
					if (! $event->price || ($event->price && $participant['paymentId']))
						{
						++$attending;
						$row = ['name' => $participant['firstName'] . ' ' . $participant['lastName']];

						if ($event->showComments)
							{
							$row['comments'] = $participant['comments'];
							}
						$table->addRow($row);
						}

					if ($participant['memberId'] == \App\Model\Session::signedInMemberId())
						{
						$userSignedUp = true;
						}
					}
				$row = ['name' => '<b>Total Attending:</b> ' . $attending];

				if ($event->showComments)
					{
					$row['comments'] = '';
					}
				$table->addRow($row);
				$this->page->addPageContent($table);

				if (! $userSignedUp)
					{
					$this->page->addPageContent(new \PHPFUI\Button('Register Now', '/Events/signUp/' . $event->eventId));
					}
				}
			else
				{
				$this->page->addPageContent('Event not found');
				}
			}
		}

	public function signUp(\App\Record\Event $event = new \App\Record\Event()) : void
		{
		// view will set the page to public if need be.
		$this->view->setEvent($event);

		if (! $event->empty() && \App\Enum\Event\MembersOnly::MEMBERS_ONLY == $event->membersOnly)
			{
			$this->page->setPublic(false);
			}

		if ($this->page->addHeader('Sign Up For An Event'))
			{
			$event = $this->view->getEvent();

			if (! $event->empty() && (! $event->publicDate || $event->publicDate <= \App\Tools\Date::todayString()))
				{
				if ($event->registrationStartDate <= \App\Tools\Date::todayString())
					{
					$this->page->addPageContent($this->view->signUp());
					}
				else
					{
					$message = $event->title . ' opens for registration on ' . $event->registrationStartDate;
					$callout = new \PHPFUI\Callout('warning');
					$callout->add($message);
					$this->page->addPageContent($callout);
					}
				}
			else
				{
				$this->page->addPageContent('Event not found');
				}
			}
		}

	public function signupMember(\App\Record\Event $event = new \App\Record\Event()) : void
		{
		// view will set the page to public if need be.
//		$this->view->setEventId($event->eventId);
		$this->page->setPublic(false);

		if ($this->page->addHeader('Sign Up For An Event'))
			{
			if (! $this->view->getEvent()->empty())
				{
				$this->page->addPageContent($this->view->Signup());
				}
			else
				{
				$this->page->addPageContent('Event not found');
				}
			}
		}

	public function signupSheet(string $type, \App\Record\Event $event = new \App\Record\Event()) : void
		{
		if ($this->page->isAuthorized('Signup Sheets'))
			{
			$fileName = "EventSignup-{$event->eventId}.";

			if ('CSV' == $type)
				{
				$csvWriter = new \App\Tools\CSV\FileWriter($fileName . 'csv');
				$participants = $this->reservationTable->setReservationsCursor($event);

				foreach ($participants->getArrayCursor() as $participant)
					{
					$csvWriter->outputRow($participant);
					}
				}
			else
				{
				$pdf = new \PDF_MC_Table();
				$pdf->SetDisplayMode('fullpage');
				$pdf->SetFont('Arial', '', 10);
				$pdf->setNoLines(true);
				$pdf->headerFontSize = 18;
				$pdf->SetAutoPageBreak(true, 2);
				$pdf->SetWidths([70, // name
					20, // price
					20, // payment received
					20, // payment type
					40, // payment number
				]);

				if ($event->price > 0)
					{
					$pdf->SetHeader(['Name',
						'Price',
						"Payment\nReceived",
						"Payment\nType",
						"Payment\nNumber",
					]);
					}
				else
					{
					$pdf->SetHeader(['Name']);
					}
				$pdf->SetAligns(['L', 'C', 'C', 'C']);
				$pdf->AddPage('P', 'Letter');
				$pdf->SetDocumentTitle($event->title);
				$pdf->PrintHeader();
				$total = 0;
				$this->reservationTable->setReservationsCursor($event);
				$this->reservationTable->addOrderBy('lastName')->addOrderBy('firstName');
				$participantCursor = $this->reservationTable->getArrayCursor();
				$paymentTypes = \App\Table\Payment::getPaymentTypes();

				foreach ($participantCursor as $participant)
					{
					$name = $participant['firstName'] . ' ' . $participant['lastName'];
					$Price = 0.0;
					$PaymentReceived = $PaymentType = $PaymentNumber = '';
					++$total;

					if ($event->price > 0)
						{
						$Price = (float)$participant['pricePaid'];

						if ($participant['paymentId'])
							{
							$PaymentReceived = 'Yes';
							$PaymentType = $paymentTypes[$participant['paymentType']];
							$PaymentNumber = $participant['paymentNumber'];
							}
						else
							{
							$PaymentReceived = 'No';
							}
						}
					$pdf->Row([$name, '$' . \number_format($Price, 2), $PaymentReceived, $PaymentType, $PaymentNumber]);
					}
				$pdf->Row(['', '', '', '', '']);
				$pdf->Row(['Total Attending', $total, '', '', '']);
				$pdf->Row(['', '', '', '', '']);
				$pdf->Row(['Printed ' . \date('n/j/Y \a\\t g:i a'), '', '', '', '']);
				$pdf->Output($fileName . 'pdf', 'I');
				}
			}
		}

	public function upcoming() : void
		{
		$this->page->setPublic();

		$name = $this->page->value('HomePageUpcoming_Events_Header');

		if ($this->page->addHeader($name))
			{
			$content = new \App\View\Content($this->page);
			$this->page->addPageContent($content->getDisplayCategoryHTML('Upcoming Events'));
			$eventTable = new \App\Table\Event();
			$eventTable->setUpcomingCursor(false);
			$cursor = $eventTable->getDataObjectCursor();
			$this->page->addPageContent($this->view->show($cursor));
			}
		}
	}
