<?php

namespace App\Model;

class Reservation
	{
	public function checkout(\App\Record\Reservation $reservation) : int
		{
		$price = $this->updatePrices($reservation);
		$reservation->update();

		if (! $price)
			{
			$this->getEmail('free', $reservation)->send();
			$this->incrementNewMemberDiscountCount($reservation);

			return 0;
			}

		if ($reservation->invoiceId)
			{
			$invoice = $reservation->invoice;

			if ($invoice->loaded() && $invoice->totalPrice == $price)
				{
				return $reservation->invoiceId;
				}
			// have a new price, delete old invoice
			$invoice->delete();
			}

		$invoice = new \App\Record\Invoice();
		$attendees = $reservation->ReservationPersonChildren;
		$event = $reservation->event;
		// create an invoice, there are bills to pay
		$invoice->orderDate = $invoice->fullfillmentDate = \App\Tools\Date::todayString();
		$invoice->memberId = $reservation->memberId;
		$invoice->totalPrice = $price;
		$invoice->totalTax = 0.0;
		$invoice->totalShipping = 0.0;
		$invoice->discount = 0.0;
		$invoice->paypalPaid = 0.0;
		$reservation->invoice = $invoice;
		$reservation->update();
		$invoiceItem = new \App\Record\InvoiceItem();
		$invoiceItem->invoice = $invoice;
		$invoiceItem->title = $event->title;
		$invoiceItem->description = $event->information;
		$names = [];

		foreach ($attendees as $attendee)
			{
			$names[] = "{$attendee->firstName} {$attendee->lastName}";
			}
		$invoiceItem->detailLine = \implode(',', $names);
		$invoiceItem->quantity = \count($attendees);
		$invoiceItem->type = \App\Enum\Store\Type::EVENT;
		$invoiceItem->shipping = 0.0;
		$invoiceItem->tax = 0.0;
		$invoiceItem->price = $event->price;
		$invoiceItem->storeItemId = (int)$reservation->eventId;
		$invoiceItem->storeItemDetailId = $reservation->reservationId;
		$invoiceItem->insert();

		if (! \App\Model\Session::isSignedIn() && (\App\Enum\Event\MembersOnly::FREE_MEMBERSHIP == $event->membersOnly || \App\Enum\Event\MembersOnly::PAID_MEMBERSHIP == $event->membersOnly))
			{
			$invoiceItem = new \App\Record\InvoiceItem();
			$invoiceItem->invoice = $invoice;
			$price = 0;
			$invoiceItem->title = \App\Model\Member::MEMBERSHIP_TITLE;

			$duesModel = new \App\Model\MembershipDues();

			if (\App\Enum\Event\MembersOnly::PAID_MEMBERSHIP == $event->membersOnly)
				{
				$price = $duesModel->getMembershipPrice($attendees->count());
				}
			$invoiceItem->description = '';
			$invoiceItem->detailLine = \array_shift($names);
			$invoiceItem->quantity = 1;
			$invoiceItem->type = \App\Enum\Store\Type::MEMBERSHIP;
			$invoiceItem->shipping = 0.0;
			$invoiceItem->tax = 0.0;
			$invoiceItem->price = $price;
			$invoiceItem->storeItemId = \App\Model\Member::MEMBERSHIP_RENEWAL;
			$invoiceItem->storeItemDetailId = \App\Model\Member::EVENT_MEMBERSHIP;
			$invoiceItem->insert();
			$maxMembers = (int)$duesModel->MaxMembersOnMembership;
			$invoiceItem->title = \App\Model\Member::MEMBERSHIP_ADDITIONAL_TITLE;
			$invoiceItem->price = 0.0;
			$invoiceItem->storeItemDetailId = \App\Model\Member::EVENT_ADDITIONAL_MEMBERSHIP;

			foreach ($names as $name)
				{
				if (--$maxMembers > 0)
					{
					$invoiceItem->detailLine = $name;
					$invoiceItem->insert();
					}
				}
			}

		return $invoice->invoiceId;
		}

	public function delete(\App\Record\Reservation $reservation) : void
		{
		$reservation->delete();
		}

	public function deleteAttendee(\App\Record\Reservation $reservation, \App\Record\ReservationPerson $reservationPerson) : void
		{
		$reservationPerson->delete();
		$this->updatePrices($reservation);
		}

	/**
	 * @return array<string,mixed>
	 */
	public function executeInvoice(\App\Record\Invoice $invoice, \App\Record\InvoiceItem $invoiceItem, \App\Record\Payment $payment) : array
		{
		$invoice->fullfillmentDate = \App\Tools\Date::todayString();
		$invoice->update();
		$reservation = new \App\Record\Reservation($invoiceItem->storeItemDetailId);
		$reservation->invoice = $invoice;
		$reservation->payment = $payment;
		$reservation->update();
		$this->getEmail('paypal', $reservation)->send();
		$this->incrementNewMemberDiscountCount($reservation);

		return $reservation->member->toArray();
		}

	/**
	 * @return array<string,mixed>
	 */
	public function getChair(int $eventId) : array
		{
		$event = new \App\Record\Event($eventId);
		$member = new \App\Record\Member($event->organizer);

		return $member->toArray();
		}

	public function getEmail(string $type, \App\Record\Reservation $reservation) : \App\Tools\EMail
		{
		$data = $this->getMergeData($reservation);
		$settingTable = new \App\Table\Setting();
		$subject = \App\Tools\TextHelper::processText($settingTable->value('EventTitle'), $data);
		$instructions = \App\Tools\TextHelper::processText($settingTable->value($type . 'Instructions'), $data);
		$paypalTerms = '';

		if ('paypal' == $type)
			{
			$paypalTerms = \str_replace("\n", '<br>', $settingTable->value('PayPalTerm'));
			}
		$data['instructions'] = $instructions . $paypalTerms;
		$text = \App\Tools\TextHelper::processText($settingTable->value('EventBody'), $data);
		$email = new \App\Tools\EMail();
		$email->setSubject($subject);
		$email->setBody($text);
		$email->setHtml();
		$email->setFrom($data['organizer_email'], "{$data['organizer_firstName']} {$data['organizer_lastName']}");
		$email->setReplyTo($data['organizer_email'], "{$data['organizer_firstName']} {$data['organizer_lastName']}");
		$email->setToMember($data);
		$email->addBCC($data['organizer_email'], "{$data['organizer_firstName']} {$data['organizer_lastName']}");

		return $email;
		}

	/**
	 * @return array<string,mixed>
	 */
	public function getMergeData(\App\Record\Reservation $reservation) : array
		{
		$event = $reservation->event;
		$organizer = new \App\Record\Member($event->organizer);
		$member = $reservation->member;

		if (! $member->loaded())
			{
			$member = new \App\Record\Member();
			$reservationPerson = new \App\Record\ReservationPerson(['reservationId' => $reservation->reservationId]);
			$member->setFrom($reservationPerson->toArray());
			}
		$data = \array_merge($event->toArray(), $reservation->toArray(), $member->toArray());

		foreach ($organizer->toArray() as $key => $value)
			{
			$data['organizer_' . $key] = $value;
			}
		$data['signedUpAt'] ??= \date('Y-m-d H:i:s');
		$data['eventDate'] = \App\Tools\Date::formatString('l F j, Y', $data['eventDate'] ?? \App\Tools\Date::todayString());

		foreach ($data as $field => $value)
			{
			if (\str_ends_with($field, 'Time'))
				{
				$data[$field] = \App\Tools\TimeHelper::toSmallTime($data[$field]);
				}
			}

		return $data;
		}

	public function incrementNewMemberDiscountCount(\App\Record\Reservation $reservation) : void
		{
		$event = $reservation->event;

		if (\App\Model\Event::newMemberDiscountQualified($event))
			{
			$member = $reservation->member;
			$member->discountCount += 1;
			$member->update();
			}
		}

	/**
	 * Issue a refund for the reservation and return the email message body
	 */
	public function refund(\App\Record\Event $event, \App\Record\Reservation $reservation) : string
		{
		$paymentTable = new \App\Table\Payment();
		$paymentTypes = $paymentTable->getPaymentTypes();
		$settingTable = new \App\Table\Setting();
		$memberPicker = new \App\Model\MemberPicker('Treasurer');
		$treasurer = $memberPicker->getMember();
		$coordinator = new \App\Record\Member($event->organizer);

		if (! $coordinator->loaded())
			{
			$coordinator = \App\Model\Session::signedInMemberRecord();
			}
		$clubName = $settingTable->value('clubName');

		$payment = $reservation->payment;

		$reservation->paymentId = null;
		$reservation->update();
		$email = new \App\Tools\EMail();
		$email->setSubject($subject = "{$clubName} Refund Request");
		$attendees = $reservation->ReservationPersonChildren;
		$attendee = $attendees->current();
		$message = "Dear {$attendee['firstName']} {$attendee['lastName']},<p>";
		$message .= "We are processing your request for a refund for {$event->title}<p>";
		$message .= "We will be refunding a payment of $ {$payment->amount} paid by {$paymentTypes[$payment->paymentType]}";
		$message .= " payment number {$payment->paymentNumber} on " . $payment->dateReceived;

		if (3 == $payment->paymentType && (\App\Tools\Date::today() - \App\Tools\Date::fromString($payment->dateReceived)) > 25)
			{
			$message .= ', less a transaction fees of $.30 charged by PayPal for issuing a refund.';
			}
		$message .= ' The refund should be done shortly, but please allow some time for processing.<p>';
		$message .= "Refunds have to be processed by our treasurer.  If you don't get your refund within a week of this email, " .
				'please contact our treasurer ' . \PHPFUI\Link::email($treasurer['email'], "{$treasurer['firstName']} {$treasurer['lastName']}", $subject);
		$message .= '<p>If refund is to made by check, the address we have on file is:<p>';
		$message .= "{$reservation->address}\n{$reservation->town}, {$reservation->state} {$reservation->zip}<p>";
		$message .= "Thanks for your cooperation,<p>{$coordinator['firstName']} {$coordinator['lastName']}";
		$email->setBody($message);
		$email->setHtml();
		$email->setFromMember($coordinator->toArray());
		$email->addCCMember($treasurer);
		$email->addBCCMember($coordinator->toArray());

		foreach ($attendees as $attendee)
			{
			$email->addToMember($attendee->toArray());
			}
		$email->send();
		$payment->delete();

		return $message;
		}

	public function updatePrices(\App\Record\Reservation $reservation) : float
		{
		$persons = $reservation->ReservationPersonChildren;
		$price = 0.0;
		$event = new \App\Record\Event();
		$event->membersOnly = \App\Enum\Event\MembersOnly::PUBLIC;

		$personCount = \count($persons);

		if (\count($persons))
			{
			$person = $persons->current();
			$event = $person->event;
			$price = \App\Model\Event::getActualPrice($event);
			}
		$price *= $personCount;

		if (! \App\Model\Session::isSignedIn() && \App\Enum\Event\MembersOnly::PAID_MEMBERSHIP == $event->membersOnly)
			{
			$duesModel = new \App\Model\MembershipDues();
			$price += (float)$duesModel->getMembershipPrice($personCount);
			}
		$reservation->pricePaid = $price;
		$reservation->update();

		return $price;
		}
	}
