<?php

namespace App\Model;

class GeneralAdmission
	{
	/**
	 * @var array<string,string>
	 */
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

	public function addRiderToEmail(\App\Record\GaEvent $event, \App\Record\GaRider $rider) : void
		{
		if (! $this->email)
			{
			$this->email = new \App\Tools\EMail();
			$this->email->setSubject('Registration Confirmation for ' . $event->title);
			$this->email->setFromMember($this->getChair($event->gaEventId));
			$this->message = \App\Tools\TextHelper::processText($event->signupMessage, $rider->toArray());
			$this->message .= "<p>The following riders have been signed up for <strong>{$event->title}</strong>";
			$this->message .= ' on ' . $event->eventDate . ' starting from ' . $event->location . '.</p>';
			}
		$this->itemizeRider($rider);
		}

	public function addRiderToIncompleteEmail(\App\Record\GaEvent $event, \App\Record\GaRider $rider) : void
		{
		if (! $this->email)
			{
			$this->email = new \App\Tools\EMail();
			$this->email->setSubject('Your Registration is NOT complete for ' . $event->title);
			$this->email->setFromMember($this->getChair($event->gaEventId));
			$this->message = \App\Tools\TextHelper::processText($event->incompleteMessage, $rider->toArray());
			$this->message .= "<p>The following riders have NOT been signed up for <strong>{$event->title}</strong>";
			$this->message .= ' on ' . $event->eventDate . ' starting from ' . $event->location . '.</p>';
			}
		$this->email->addBCCMember($this->chair);
		$this->itemizeRider($rider);
		}

	public function copy(\App\Record\GaEvent $originalEvent, ?string $newDate, ?string $newTitle) : \App\Record\GaEvent
		{
		$dateDiff = \App\Tools\Date::diff($originalEvent->eventDate, $newDate);

		$newEvent = new \App\Record\GaEvent($originalEvent);
		$newEvent->volunteerEvent = null;
		$newEvent->gaEventId = 0;

		if ($newTitle)
			{
			$newEvent->title = $newTitle;
			}

		if ($newDate)
			{
			$newEvent->eventDate = $newDate;
			}
		$newEvent->lastRegistrationDate = \App\Tools\Date::increment($newEvent->lastRegistrationDate, $dateDiff);
		$newEvent->membershipExpires = \App\Tools\Date::increment($newEvent->membershipExpires, $dateDiff);
		$newEvent->insert();

		$where = new \PHPFUI\ORM\Condition('gaEventId', $originalEvent->gaEventId);
		$optionTable = new \App\Table\GaOption();
		$optionTable->setWhere($where);

		foreach ($optionTable->getRecordCursor() as $record)
			{
			$record->clone($newEvent);
			}

		$priceDateTable = new \App\Table\GaPriceDate();
		$priceDateTable->setWhere($where);

		foreach ($priceDateTable->getRecordCursor() as $record)
			{
			$record->gaEvent = $newEvent;
			$record->gaPriceDateId = 0;
			$record->date = \App\Tools\Date::increment($record->date, $dateDiff);
			$record->insert();
			}

		return $newEvent;
		}

	/**
	 * @return array<string,mixed>
	 */
	public function executeInvoice(\App\Record\Invoice $invoice, \App\Record\InvoiceItem $invoiceItem) : array
		{
		$event = new \App\Record\GaEvent($invoiceItem->storeItemId);
		$rider = new \App\Record\GaRider($invoiceItem->storeItemDetailId);
		$rider->pending = 0;
		$rider->pricePaid = (float)$invoiceItem->price;

		$this->addRiderToEmail($event, $rider);
		$gaWaiver = new \App\Report\GAWaiver();

		if ($gaWaiver->generate($rider))
			{
			$fileName = "Rider-{$rider->fullName()}-{$event->gaEventId}-{$rider->gaRiderId}-Waiver.pdf";
			$fileName = PROJECT_ROOT . '/files/GAWaivers/' . \str_replace(' ', '_', $fileName);
			$gaWaiver->output($fileName, 'F');
			$this->email->addAttachment($fileName, \str_replace(' ', '_', $event->title . ' Waiver for ' . $rider->fullName() . '.pdf'));
			}

		$includeMembership = $event->includeMembership;

		if (\App\Enum\GeneralAdmission\IncludeMembership::NO != $includeMembership)
			{
			$today = \App\Tools\Date::todayString();
			$goodThrough = empty($event->membershipExpires) ? \App\Tools\Date::makeString(\date('Y'), 12, 31) : $event->membershipExpires;
			$existingMember = new \App\Record\Member(['email' => $rider->email]);

			if ($existingMember->loaded())
				{
				$rider->member = $existingMember;
				$membership = $existingMember->membership;

				switch ($includeMembership)
					{
					case \App\Enum\GeneralAdmission\IncludeMembership::EXTEND_MEMBERSHIP:
						if ($membership->expires < $goodThrough)
							{
							$membership->expires = $goodThrough;
							$membership->update();
							}

						break;

					case \App\Enum\GeneralAdmission\IncludeMembership::RENEW_MEMBERSHIP:
						if ($membership->expires < $today)
							{
							$membership->expires = $goodThrough;
							$membership->update();
							}

						break;
					}
				}
			else // if (\App\Enum\GeneralAdmission\IncludeMembership::NEW_MEMBERS_ONLY == $includeMembership)
				{
				$memberModel = new \App\Model\Member();
				$membership = new \App\Record\Membership();
				$membership->setFrom($rider->toArray());
				$membership->pending = 0;
				$membership->joined = \App\Tools\Date::todayString();
				$membership->affiliation = $event->title;
				$membership->expires = $goodThrough;
				$member = new \App\Record\Member();

				$member->setFrom($rider->toArray());
				$memberModel = new \App\Model\Member();
				$memberModel->setDefaultFields($member);
				$member->membership = $membership;
				$member->verifiedEmail = 9;
				$member->acceptedWaiver = null;
				$member->emergencyContact = $rider->contact;
				$member->emergencyPhone = $rider->contactPhone;
				$member->cellPhone = $rider->phone;
				$member->password = $memberModel->hashPassword(\uniqid());  // will not match password for sure
				$member->insert();
				$memberModel->setNormalMemberPermission($member);
				$rider->member = $member;
				}
			}
		$rider->update();

		return $rider->toArray();
		}

	/**
	 * @return array<string,string>
	 */
	public function getChair(int $gaEventId) : array
		{
		$event = new \App\Record\GaEvent($gaEventId);
		$this->chair['lastName'] = '';
		$this->chair['firstName'] = $event->registrar;
		$this->chair['email'] = $event->registrarEmail;

		return $this->chair;
		}

	public function getCurrentRegistrationRecord(\App\Record\GaEvent $event) : \App\Record\GaPriceDate
		{
		return $this->gaPriceDateTable->getCurrentRegistrationRecord($event);
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

	/**
	 * @return array<string>
	 */
	public function getWarningMessages(int $gaRiderId) : array
		{
		$rider = new \App\Record\GaRider($gaRiderId);
		$messages = [];

		$fields = [
			'phone' => 'phone number',
			'email' => 'email address',
		];

		foreach ($fields as $field => $fieldName)
			{
			$condition = new \PHPFUI\ORM\Condition($field, $rider->{$field} ?? '');
			$condition->and('pending', 0)->and('gaEventId', $rider->gaEventId);
			$this->gaRiderTable->setWhere($condition);
			$similar = $this->gaRiderTable->getRecordCursor();

			if (\count($similar))
				{
				$dupRider = $similar->current();
				$messages[] = "A rider with this {$fieldName} registered on {$dupRider->signedUpOn}";
				}
			}

		return $messages;
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

	private function itemizeRider(\App\Record\GaRider $rider) : void
		{
		$this->email->addToMember($rider->toArray());
		$this->message .= "<p>Rider: <strong>{$rider->firstName} {$rider->lastName}</strong><br>";
		$this->message .= "Address: {$rider->address} {$rider->town}, {$rider->state} {$rider->zip}<br>";
		$this->message .= "Phone:: {$rider->phone}<br>";
		$this->message .= "Emergency Contact: {$rider->contact} Number: {$rider->contactPhone}<br></p>";
		$options = $rider->optionsSelected;

		if (\count($options))
			{
			$this->message .= '<p><strong>Rider Options Selected:</strong></p>';
			$ol = new \PHPFUI\OrderedList();

			foreach ($options as $option)
				{
				$ol->addItem(new \PHPFUI\ListItem("<strong>{$option->optionName}</strong>"));
				$ul = new \PHPFUI\UnorderedList();
				$ul->addItem(new \PHPFUI\ListItem($option->selectionName));
				$ol->addItem($ul);
				}
			$this->message .= $ol;
			}
		}
	}
