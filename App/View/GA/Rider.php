<?php

namespace App\View\GA;

class Rider
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		$this->processRequest();
		}

	public function edit(\App\Record\GaRider $rider) : \App\UI\ErrorFormSaver
		{
		if ($rider->loaded())
			{
			$submit = new \PHPFUI\Submit();
			$form = new \App\UI\ErrorFormSaver($this->page, $rider, $submit);

			if ($form->save())
				{
				return $form;
				}
			$form->add($this->getEditFields($rider));
			$form->add(new \PHPFUI\Input\Hidden('gaEventId', (string)$rider->gaEventId));
			$form->add($submit);

			return $form;
			}

		$rider = new \App\Record\GaRider();
		$submit = new \PHPFUI\Submit('Add Rider');
		$form = new \App\UI\ErrorFormSaver($this->page, $rider);
		$form->add(new \App\View\GA\EventPicker($this->page, \App\View\GA\EventPicker::SINGLE_SELECT, 'Select Event'));
		$form->add($this->getEditFields($rider));
		$form->add($submit);
		$form->add(new \PHPFUI\FormError());

		return $form;
		}

	public function getEditFields(\App\Record\GaRider $rider) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$event = $rider->gaEvent;

		if ($event->loaded())
			{
			$fieldset = new \PHPFUI\FieldSet('Event Information');
			$fieldset->add(new \App\UI\Display('Event', $event->title . ' / ' . $event->eventDate));
			$container->add($fieldset);
			}
		$container->add($this->getRiderSettings($rider, $event));
		$container->add($this->getAddress($rider));

		if ($this->page->isAuthorized('Add Registration'))
			{
			$container->add($this->getAdminFields($rider));
			}
		$container->add(new \PHPFUI\Input\Hidden('gaRiderId', (string)$rider->gaRiderId));
		$container->add(new \PHPFUI\Input\Hidden('memberId', (string)$rider->memberId));
		$container->add(new \PHPFUI\Input\Hidden('gaRiderId', (string)$rider->gaRiderId));

		return $container;
		}

	public function show(\App\Table\GaRider $gaRiderTable) : \App\UI\ContinuousScrollTable
		{
		$gaRiderTable->addJoin('gaEvent');
		$view = new \App\UI\ContinuousScrollTable($this->page, $gaRiderTable);
		$sortableHeaders = ['eventDate' => 'Event Date', 'title' => 'Event', 'firstName' => 'First Name', 'lastName' => 'Last Name', 'town' => 'Town', 'phone' => 'Cell',
			'contact' => 'Contact', 'pending' => 'Paid', ];

		$otherHeaders = ['edit' => 'Edit', 'del' => 'Del', ];

		new \App\Model\EditIcon($view, $gaRiderTable, '/GA/editRider/');
		$deleter = new \App\Model\DeleteRecord($this->page, $view, $gaRiderTable, 'Are you sure you want to permanently delete this rider?');
		$view->addCustomColumn('del', $deleter->columnCallback(...));

		$view->addCustomColumn('pending', static fn (array $rider) => $rider['pending'] ? '' : '&check;');

		$view->addCustomColumn('phone', static fn (array $rider) => \PHPFUI\Link::phone($rider['phone']));

		$view->setHeaders(\array_merge($sortableHeaders, $otherHeaders))
			->setSortableColumns(\array_keys($sortableHeaders));
		unset($sortableHeaders['pending']);
		$view->setSearchColumns(\array_keys($sortableHeaders));

		return $view;

//			if (! empty($rider->contactPhone))
//				{
//				if (empty($rider->contact))
//					{
//					$rider->contact = \PHPFUI\Link::phone($rider->contactPhone);
//					}
//				else
//					{
//					$rider->contact = new \PHPFUI\ToolTip(\PHPFUI\Link::phone($rider->contactPhone, $rider->contact), $rider->contactPhone);
//					}
//				}
		}

	private function getAddress(\App\Record\GaRider $rider, bool $requireAllAddressFields = false) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Rider Address');
		$address = new \PHPFUI\Input\Text('address', 'Street Address', $rider->address);
		$address->setRequired($requireAllAddressFields);
		$town = new \PHPFUI\Input\Text('town', 'Town', $rider->town);
		$town->setRequired($requireAllAddressFields);
		$fieldSet->add(new \PHPFUI\MultiColumn($address, $town));
		$state = new \App\UI\State($this->page, 'state', 'State', $rider->state ?? '');
		$state->setRequired($requireAllAddressFields);
		$zip = new \PHPFUI\Input\Zip($this->page, 'zip', 'Zip Code', $rider->zip);
		$zip->setRequired($requireAllAddressFields);
		$fieldSet->add(new \PHPFUI\MultiColumn($state, $zip));

		return $fieldSet;
		}

	private function getAdminFields(\App\Record\GaRider $rider) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Adminstrative Fields');
		$address = new \PHPFUI\Input\Number('pricePaid', 'Price Paid', $rider->pricePaid);
		$pending = new \PHPFUI\Input\CheckBoxBoolean('pending', 'Pending', (bool)$rider->pending);
		$pending->setToolTip('Payment has not cleared PayPal yet or check not received if checked');
		$emailAnnouncements = new \PHPFUI\Input\CheckBoxBoolean('emailAnnouncements', 'Send Email', (bool)$rider->emailAnnouncements);
		$emailAnnouncements->setToolTip('Uncheck to unsubscribe rider from emails.  Not recommended for current year riders.');
		$fieldSet->add(new \PHPFUI\MultiColumn($address, $pending, $emailAnnouncements));

		if ($rider->loaded() && '1000-01-01' < $rider->signedUpOn)
			{
			$fieldSet->add(new \App\UI\Display('Signed Up On', \date('F j, Y, g:i a', \strtotime($rider->signedUpOn))));
			}
		$editIcon = new \PHPFUI\FAIcon('far', 'edit', "/Membership/edit/{$rider->memberId}");
		$thumbsUp = new \PHPFUI\FAIcon('far', 'thumbs-up');
		$member = $rider->memberId ? $thumbsUp . ' &nbsp; &nbsp; &nbsp; &nbsp; ' . $editIcon : new \PHPFUI\FAIcon('far', 'thumbs-down');
		$fieldSet->add(new \App\UI\Display('Club Member', $member));
		// add gaIncentiveId, prize
		return $fieldSet;
		}

	private function getRiderSettings(\App\Record\GaRider $rider, \App\Record\GaEvent $event) : \PHPFUI\FieldSet
		{
		$riderFieldset = new \PHPFUI\FieldSet('Rider Information');
		$firstName = new \PHPFUI\Input\Text('firstName', 'First Name', $rider->firstName);
		$firstName->setRequired();
		$lastName = new \PHPFUI\Input\Text('lastName', 'Last Name', $rider->lastName);
		$lastName->setRequired();
		$riderFieldset->add(new \PHPFUI\MultiColumn($firstName, $lastName));
		$email = new \PHPFUI\Input\Email('email', 'Email address', $rider->email);
		$email->setRequired();
		$phone = new \PHPFUI\Input\Tel($this->page, 'phone', 'Phone', $rider->phone);
		$phone->setRequired();
		$riderFieldset->add(new \PHPFUI\MultiColumn($email, $phone));
		$emergencyContact = new \PHPFUI\Input\Text('contact', 'Emergency Contact Name', $rider->contact);
		$emergencyContact->setRequired();
		$emergencyPhone = new \PHPFUI\Input\Tel($this->page, 'contactPhone', 'Emergency Contact Phone', $rider->contactPhone);
		$emergencyPhone->setRequired();
		$riderFieldset->add(new \PHPFUI\MultiColumn($emergencyContact, $emergencyPhone));

		if ($event->loaded())
			{
			$select = new \PHPFUI\Input\Select('referral', $event->question);
			$gaAnswerTable = new \App\Table\GaAnswer();
			$gaAnswerTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $rider->gaEventId));
			$gaAnswerTable->addOrderBy('answer');

			$select->addOption('Please Select', '', 0 == $rider->referral);

			foreach ($gaAnswerTable->getRecordCursor() as $answer)
				{
				$select->addOption($answer->answer, $answer->gaAnswerId, $rider->referral ? $rider->referral == $answer->gaAnswerId : false);
				}
			$select->setRequired();
			$riderFieldset->add($select);
			}

		return $riderFieldset;
		}

	private function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if ('Add Rider' == ($_POST['submit'] ?? ''))
				{
				$rider = new \App\Record\GaRider();
				$rider->setFrom($_POST);
				$rider->signedUpOn = \date('Y-m-d H:i:s');
				$rider->gaRideId = 0;
				$rider->memberId = 0;
				$rider->referral = 0;
				$rider->gaIncentiveId = 0;
				$rider->prize = 0;

				$id = $rider->insert();
				$url = $this->page->getBaseURL();
				$pos = \strrpos($url, '/');

				if ($pos > 0)
					{
					$url = \substr($url, 0, $pos + 1);
					}
				$this->page->redirect($url . $id);
				}
			elseif ('deleteRider' == ($_POST['action'] ?? ''))
				{
				$rider = new \App\Record\GaRider((int)$_POST['gaRiderId']);
				$rider->delete();
				$this->page->setResponse($_POST['gaRiderId']);
				}
			}
		}
	}
