<?php

namespace App\Model;

class GeneralAdmission
	{
	private array $chair = [];

	private ?\App\Tools\EMail $email = null;

	private readonly \App\Table\GaPriceDate $gaPriceDateTable;

	private readonly \App\Table\GaRider $gaRiderTable;

	private string $message = '';

	public function __construct()
		{
		$this->gaPriceDateTable = new \App\Table\GaPriceDate();
		$this->gaRiderTable = new \App\Table\GaRider();
		}

	public function __destruct()
		{
		if ($this->email)
			{
			$this->email->setBody($this->message);
			$this->email->send();
			}
		}

	public function executeInvoice(\App\Record\Invoice $invoice, \App\Record\InvoiceItem $invoiceItem) : array
		{
		$event = new \App\Record\GaEvent($invoiceItem->storeItemId);
		$rider = new \App\Record\GaRider($invoiceItem->storeItemDetailId);
		$rider->pending = 0;
		$rider->pricePaid = $invoiceItem->price;

		if (! $this->email)
			{
			$this->email = new \App\Tools\EMail();
			$this->email->setSubject('Registration Confirmation for ' . $event->title);
			$this->email->setFromMember($this->chair);
			$this->email->setHtml();
			$this->message = $event->signupMessage;
			$this->message .= "<p>The following riders have been signed up for <strong>{$event->title}</strong>";
			$this->message .= ' on ' . $event->eventDate . ' starting from ' . $event->location . '.</p>';
			}
		$this->email->addToMember($rider->toArray());
		$this->message .= "<p>Rider: <strong>{$rider->firstName} {$rider->lastName}</strong><br>";
		$this->message .= "Address: {$rider->address} {$rider->town}, {$rider->state} {$rider->zip}<br>";
		$this->message .= "Phone:: {$rider->phone}<br>";
		$this->message .= "Emergency Contact: {$rider->contact} Number: {$rider->contactPhone}<br></p>";

		if ($event->includeMembership && empty($rider->memberId))
			{
			$memberModel = new \App\Model\Member();
			$membership = new \App\Record\Membership();
			$membership->setFrom($rider->toArray());
			$membership->pending = 0;
			$membership->joined = \App\Tools\Date::todayString();
			$membership->affiliation = $event->title;

			if (empty($event->membershipExpires))
				{
				$membership->expires = \App\Tools\Date::makeString(\date('Y'), 12, 31);
				}
			else
				{
				$membership->expires = $event->membershipExpires;
				}
			$password = $memberModel->getRandomPassword();
			$member = new \App\Record\Member();
			$member->setFrom($rider->toArray());
			$member->emailAnnouncements = 1;
			$member->membership = $membership;
			$member->emailNewsletter = 2;
			$member->acceptedWaiver = null;
			$member->rideComments = 1;
			$member->emergencyContact = $rider->contact;
			$member->emergencyPhone = $rider->contactPhone;
			$member->cellPhone = $rider->phone;
			$member->password = $memberModel->hashPassword($password);
			$member->insert();
			$memberModel->setNormalMemberPermission($member);
			$rider->member = $member;
			}
		$rider->update();

		return $rider->toArray();
		}

	public function getCurrentRegistrationRecord(\App\Record\GaEvent $event) : \App\Record\GaPriceDate
		{
		return $this->gaPriceDateTable->getCurrentRegistrationRecord($event);
		}

	public function getChair(int $gaEventId) : array
		{
		$event = new \App\Record\GaEvent($gaEventId);
		$this->chair['lastName'] = '';
		$this->chair['firstName'] = $event->registrar;
		$this->chair['email'] = $event->registrarEmail;

		return $this->chair;
		}

	public function getLastRegistrationDateRecord(\App\Record\GaEvent $gaEvent) : \App\Record\GaPriceDate
		{
		return new \App\Record\GaPriceDate($this->gaPriceDateTable
			->addOrderBy('date', 'desc')
			->setLimit(1)
			->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $gaEvent->gaEventId))
			->getRecordCursor()->current()->toArray());
		}

	public function getPrice(\App\Record\GaEvent $event, \App\Record\GaRider $rider) : float
		{
		$gaPriceDate = $this->getCurrentRegistrationRecord($event);
		$price = (float)$gaPriceDate->price;

		if ($rider->memberId > 0 && $event->volunteerDiscount > 0 && $event->volunteerEvent > 0)
			{
			$volunteerJobShiftTable = new \App\Table\VolunteerJobShift();
			$shifts = $volunteerJobShiftTable->getJobsForMember($rider->memberId, $event->volunteerEvent);

			if (\count($shifts))
				{
				$price -= (float)$event->volunteerDiscount;
				}
			}

		return $price;
		}

	public function getWarningMessage(int $gaRiderId) : string
		{
		$rider = new \App\Record\GaRider($gaRiderId);
		$message = '';
		$condition = new \PHPFUI\ORM\Condition('email', $rider->email ?? '');
		$condition->and('pending', 0)->and('gaEventId', $rider->gaEventId);
		$this->gaRiderTable->setWhere($condition);
		$similar = $this->gaRiderTable->getRecordCursor();

		if (\count($similar))
			{
			$dupRider = $similar->current();
			$message = 'A rider with this email address registered on ' . $dupRider->signedUpOn;
			}

		return $message;
		}

	public function setRiderPending(int $gaRiderId, int $pending) : void
		{
		$rider = new \App\Record\GaRider($gaRiderId);
		$rider->pending = $pending;
		$rider->update();
		}

	public function totalRegistrants(\App\Record\GaEvent $event) : int
		{
		return $this->gaRiderTable->totalRegistrants($event);
		}
	}
