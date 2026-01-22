<?php

namespace App\View\GA;

class Rider
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		if (\App\Model\Session::checkCSRF())
			{
			$post = $_POST;

			if ('Add Registration' == ($post['submit'] ?? ''))
				{
				$rider = new \App\Record\GaRider();
				$rider->setFrom($post);
				$rider->signedUpOn = \date('Y-m-d H:i:s');
				$rider->memberId = 0;
				$rider->prize = 0;

				$id = $rider->insert();

				if (\is_array($post['gaOptionId'] ?? false))
					{
					$post['gaRiderId'] = $id;
					$gaRiderSelectionTable = new \App\Table\GaRiderSelection();
					$gaRiderSelectionTable->updateFromPost($post);
					}

				$url = $this->page->getBaseURL();
				$pos = \strrpos($url, '/');

				if ($pos > 0)
					{
					$url = \substr($url, 0, $pos + 1);
					}
				$this->page->redirect($url . $id);
				}
			elseif ('deleteRider' == ($post['action'] ?? ''))
				{
				$rider = new \App\Record\GaRider((int)$post['gaRiderId']);
				$rider->delete();
				$this->page->setResponse($post['gaRiderId']);
				}
			}
		}

	public function edit(\App\Record\GaRider $rider, string $onSaveUrl = '') : \App\UI\ErrorFormSaver
		{
		if ($rider->loaded())
			{
			$submit = new \PHPFUI\Submit();
			$form = new \App\UI\ErrorFormSaver($this->page, $rider, $submit);

			if (! $this->page->isAuthorized('Add Registration'))
				{
				unset($_POST['pricePaid'], $_POST['pending']);
				}

			if ($form->save($onSaveUrl))
				{
				if (\is_array($_POST['gaOptionId']))
					{
					$gaRiderSelectionTable = new \App\Table\GaRiderSelection();
					$gaRiderSelectionTable->updateFromPost($_POST);
					}

				return $form;
				}
			$form->add($this->getEditFields($rider));
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton($submit);
			$form->add(new \PHPFUI\FormError());
			$form->add($buttonGroup);

			return $form;
			}

		$rider = new \App\Record\GaRider();
		$submit = new \PHPFUI\Submit('Add Registration');
		$form = new \App\UI\ErrorFormSaver($this->page, $rider);
		$form->add(new \App\View\GA\EventPicker($this->page, \App\Enum\GeneralAdmission\EventPicker::SINGLE_SELECT, 'Select Event'));
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
			$fieldset->add(new \PHPFUI\Input\Hidden('gaEventId', "{$event->gaEventId}"));
			$container->add($fieldset);
			}
		$container->add($this->getRiderSettings($rider, $event));
		$container->add($this->getAddress($rider));

		if (! $this->page->isAuthorized('Add Registration'))
			{
			$container->add($this->getWaiver($event->waiver, $rider));
			}
		$container->add($this->getOptions($rider));

		if ($this->page->isAuthorized('Add Registration'))
			{
			$container->add($this->getAdminFields($rider));
			}
		$container->add(new \PHPFUI\Input\Hidden('gaRiderId', (string)$rider->gaRiderId));
		$container->add(new \PHPFUI\Input\Hidden('memberId', (string)$rider->memberId));

		return $container;
		}

	public function show(\App\Table\GaRider $gaRiderTable) : \App\UI\ContinuousScrollTable
		{
		$gaRiderTable->addJoin('gaEvent');
		$view = new \App\UI\ContinuousScrollTable($this->page, $gaRiderTable);
		$sortableHeaders = ['eventDate' => 'Event Date', 'title' => 'Event', 'firstName' => 'First Name', 'lastName' => 'Last Name', 'email' => 'EMail', 'phone' => 'Cell',
			'contact' => 'Contact', 'pending' => 'Paid', ];

		$otherHeaders = ['edit' => 'Edit', 'del' => 'Del', ];

		new \App\Model\EditIcon($view, $gaRiderTable, '/GA/editRider/');
		$deleter = new \App\Model\DeleteRecord($this->page, $view, $gaRiderTable, 'Are you sure you want to permanently delete this rider?');
		$view->addCustomColumn('del', $deleter->columnCallback(...));

		$view->addCustomColumn('pending', static fn (array $rider) : string => $rider['pending'] ? '' : '&check;');

		$view->addCustomColumn('phone', static fn (array $rider) : \PHPFUI\Link => \PHPFUI\Link::phone($rider['phone']));

		$view->setHeaders(\array_merge($sortableHeaders, $otherHeaders))->setSortableColumns(\array_keys($sortableHeaders));
		unset($sortableHeaders['pending']);
		$view->setSearchColumns(\array_keys($sortableHeaders));

		return $view;
		}

	private function getAddress(\App\Record\GaRider $rider, bool $requireAllAddressFields = true) : \PHPFUI\FieldSet
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
		$fieldSet = new \PHPFUI\FieldSet('Administrative Fields');
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

		return $fieldSet;
		}

	private function getOptions(\App\Record\GaRider $rider) : string
		{
		$options = $rider->gaEvent->GaOptionChildren;

		if (! \count($options))
			{
			return '';
			}
		$fieldSet = new \PHPFUI\FieldSet('Rider Options');

		foreach ($options as $option)
			{
			if ($option->active)
				{
				$riderSelection = new \App\Record\GaRiderSelection(['gaRiderId' => $rider->gaRiderId, 'gaOptionId' => $option->gaOptionId]);
				$fieldSet->add(new \App\View\GA\OptionPicker($option, $riderSelection));
				}
			}

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
		$phone = new \App\UI\TelUSA($this->page, 'phone', 'Phone', $rider->phone);
		$phone->setRequired();
		$riderFieldset->add(new \PHPFUI\MultiColumn($email, $phone));
		$emergencyContact = new \PHPFUI\Input\Text('contact', 'Emergency Contact Name', $rider->contact);
		$emergencyContact->setRequired();
		$emergencyPhone = new \App\UI\TelUSA($this->page, 'contactPhone', 'Emergency Contact Phone', $rider->contactPhone);
		$emergencyPhone->setRequired();
		$riderFieldset->add(new \PHPFUI\MultiColumn($emergencyContact, $emergencyPhone));

		return $riderFieldset;
		}

	private function getWaiver(?string $waiver, \App\Record\GaRider $rider) : string
		{
		if (! $waiver)
			{
			return '';
			}
		$fieldSet = new \PHPFUI\FieldSet('Rider Waiver');
		$clubName = $this->page->value('clubName');
		$waiverLink = new \PHPFUI\Link('#', $clubName . ' Waiver');
		$modal = new \PHPFUI\Reveal($this->page, $waiverLink);
		$modal->addClass('large');
		$modal->add('<h3>I Agree To The Following</h3>');
		$modal->add($waiver);
		$modal->add('<hr>');
		$modal->add(new \PHPFUI\CloseButton($modal));
		$modal->add(new \PHPFUI\Cancel('Close'));
		$waiver = new \PHPFUI\Input\CheckBoxBoolean('agreedToWaiver', 'You must agree to the ' . $waiverLink, (bool)$rider->agreedToWaiver);
		$waiver->setRequired();
		$fieldSet->add($waiver);

		return "{$fieldSet}";
		}
	}
