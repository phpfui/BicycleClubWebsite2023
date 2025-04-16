<?php

namespace App\View\GA;

class EventEdit
	{
	private readonly \App\Table\GaOption $gaOptionTable;

	private readonly \App\Table\GaPriceDate $gaPriceDateTable;

	private string $testText = 'Test Email';

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->gaPriceDateTable = new \App\Table\GaPriceDate();
		$this->gaOptionTable = new \App\Table\GaOption();
		$this->processRequest();
		}

	public function Edit(\App\Record\GaEvent $event) : \App\UI\ErrorFormSaver
		{
		if ($event->loaded())
			{
			$submit = new \PHPFUI\Submit();
			$form = new \App\UI\ErrorFormSaver($this->page, $event, $submit);
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add', 'action');
			$organizer = \App\Model\Session::signedInMemberRecord();
			$event->registrar = $organizer->fullName();
			$event->registrarEmail = $organizer->email;
			$form = new \App\UI\ErrorFormSaver($this->page, $event);
			}

		if ($form->save())
			{
			$post = $_POST;
			$this->gaPriceDateTable->updateFromTable($post);

			$order = 1;

			foreach ($post['ordering'] ?? [] as $index => $value)
				{
				$post['ordering'][$index] = $order++;
				}
			unset($post['orderName'], $post['required'], $post['maximumAllowed'], $post['price'], $post['csvField']);

			$this->gaOptionTable->updateFromTable($post);

			return $form;
			}

		$tabs = new \PHPFUI\Tabs();
		$tabs->addTab('Required', $this->getRequiredFields($event), true);
		$tabs->addTab('Additional', $this->getAdditional($event));
		$tabs->addTab('Description', $this->getDescription($event));
		$tabs->addTab('Email', $this->getSignupEmail($event, $form));
		$tabs->addTab('Waiver', $this->getWaiver($event, $form));

		if ($event->loaded())
			{
			$tabs->addTab('Pricing', $this->getPricing($event, $form));
			$tabs->addTab('Options', $this->getOptions($event, $form));
			}

		$tabObject = $tabs->getTabs();
		$tabObject->setAttribute('data-deep-link', 'true');
		$tabContent = $tabs->getContent();

		$form->add($tabObject);
		$form->add($tabContent);
		$form->add('<br>');
		$form->add($submit);

		return $form;
		}

	protected function addOptionModal(\App\Record\GaEvent $event, \PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modalForm = new \PHPFUI\Form($this->page);
		$modalForm->setAreYouSure(false);
		$modal->addClass('large');
		$modalForm->add(new \PHPFUI\SubHeader('Add An Option'));
		$modalForm->add(new \PHPFUI\Input\Hidden('gaEventId', (string)$event->gaEventId));
		$option = new \App\Record\GaOption();
		$option->gaEvent = $event;
		$modalForm->add($this->getOptionFields($option));
		$modalForm->add($modal->getButtonAndCancel(new \PHPFUI\Submit('Add Option')));
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

	private function getAdditional(\App\Record\GaEvent $event) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$optionsSet = new \PHPFUI\FieldSet('Options');

		$maxRegistrants = new \PHPFUI\Input\Number('maxRegistrants', 'Max Registrants', $event->maxRegistrants);
		$maxRegistrants->addAttribute('min', (string)0)->addAttribute('max', (string)99)->addAttribute('step', (string)1);
		$maxRegistrants->setToolTip('Set to zero for unlimited registrations');
		$optionsSet->add($maxRegistrants);
		$dayOfRegistration = new \PHPFUI\Input\CheckBoxBoolean('dayOfRegistration', 'Day Of Registration Allowed', (bool)$event->dayOfRegistration);
		$showPreregistration = new \PHPFUI\Input\CheckBoxBoolean('showPreregistration', 'Show Preregistration Numbers', (bool)$event->showPreregistration);
		$allowShopping = new \PHPFUI\Input\CheckBoxBoolean('allowShopping', 'Allow Store Shopping at Checkout', (bool)$event->allowShopping);
		$otherEvent = new \PHPFUI\Input\CheckBoxBoolean('otherEvent', 'Not Signature Event', (bool)$event->otherEvent);
		$optionsSet->add(new \PHPFUI\MultiColumn($allowShopping, $dayOfRegistration, $showPreregistration, $otherEvent));

		$includeMembership = new \PHPFUI\Input\RadioGroupEnum('includeMembership', 'Include Membership with Event Purchase', $event->includeMembership);
		$includeMembership->setToolTip('All options other than "No" add / update a membership.
			 "New Members Only" will not extend memberships for existing members.
			 "Extend" will make sure membership is good through expires date.
			 "Renew" will update lapsed members to new expiration date.');

		$membershipExpiresDate = new \PHPFUI\Input\Date($this->page, 'membershipExpires', 'Membership Expires', $event->membershipExpires);
		$membershipExpiresDate->setToolTip('If a membership is included, this is when it will expire. Leave blank for the end of the year.');
		$optionsSet->add(new \PHPFUI\MultiColumn($includeMembership, $membershipExpiresDate));

		$container->add($optionsSet);
		$volunteerSet = new \PHPFUI\FieldSet('Volunteer Options');
		$volunteerDiscount = new \PHPFUI\Input\Number('volunteerDiscount', 'Volunteer Discount', $event->volunteerDiscount);
		$volunteerDiscount->addAttribute('max', (string)99)->addAttribute('min', (string)0);
		$volunteerDiscount->setToolTip('If someone signs up as a volunteer, then they will receive this discount when registering');
		$volunteerEvent = new \PHPFUI\Input\Select('volunteerEvent', 'Corresponding Volunteer Event');
		$volunteerEvent->setToolTip('This is the volunteer event a member must volunteer for to receive a discount.');
		$volunteerEvent->addOption('', '', 0 == (int)$event->volunteerEvent);
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

	private function getDescription(\App\Record\GaEvent $event) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$description = new \App\UI\TextAreaImage('description', 'Event Description', \str_replace("\n", '<br>', $event->description ?? ''));
		$description->htmlEditing($this->page, new \App\Model\TinyMCETextArea($event->getLength('description')));
		$description->setToolTip('This description will be shown on the web site where people go to sign up. It will also be included on confirmation emails.');
		$container->add($description);

		return $container;
		}

	private function getOptionFields(\App\Record\GaOption $option) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Option Details');
		$fieldSet->add(new \PHPFUI\Input\Hidden('gaOptionId', (string)$option->gaOptionId));
		$fieldSet->add(new \PHPFUI\Input\Hidden('gaEventId', (string)$option->gaEventId));
		$nameField = new \PHPFUI\Input\Text('optionName', 'Description', $option->optionName);
		$nameField->setRequired()->setToolTip('Question text or description of option');
		$fieldSet->add($nameField);
		$required = new \PHPFUI\Input\CheckBoxBoolean('required', 'Rider must select an option', (bool)$option->required);
		$required->setToolTip('If not checked, rider will have the choice of leaving this option blank and not make a selection.');
		$price = new \PHPFUI\Input\Number('price', 'Optional price for this option', \number_format($option->price ?? 0, 2));
		$price->setToolTip('This is the base price for this option, individual selections can have an additional price.');
		$maximumAllowed = new \PHPFUI\Input\Number('maximumAllowed', 'Number of options available', (string)$option->maximumAllowed);
		$maximumAllowed->setToolTip('Leave zero for unlimited options available, or specify a number to limit number available for registrants');
		$csvField = new \PHPFUI\Input\Text('csvField', 'CSV Output Name', $option->csvField);
		$csvField->setToolTip('This option will be listed in CSV output as this field name.  Leave blank to not output the column.');
		$csvField->addAttribute('maxLength', '20');

		$fieldSet->add(new \PHPFUI\MultiColumn($required, $price, $maximumAllowed, $csvField));

		return $fieldSet;
		}

	private function getOptions(\App\Record\GaEvent $event, \PHPFUI\Form $form) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$options = $this->gaOptionTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $event->gaEventId))->addOrderBy('ordering')->getRecordCursor();
		$table = new \PHPFUI\OrderableTable($this->page);
		$table->setRecordId($recordId = 'gaOptionId');
		$delete = new \PHPFUI\AJAX('deleteOption', 'Permanently delete this option and selections?');
		$delete->addFunction('success', "$('#{$recordId}-'+data.response).css('background-color','red').hide('fast').remove()");
		$this->page->addJavaScript($delete->getPageJS());
		$table->setHeaders(['optionName' => 'Description', 'maximumAllowed' => 'Total Units', 'csvField' => 'CSV Field', 'edit' => 'Edit', 'Del' => 'Del']);

		foreach ($options as $option)
			{
			$row = $option->toArray();
			$id = $option->gaOptionId;
			$row['optionName'] .= new \PHPFUI\Input\Hidden("gaOptionId[{$id}]", $option->gaOptionId);
			$row['optionName'] .= new \PHPFUI\Input\Hidden("ordering[{$id}]", $option->ordering);
			$row['optionName'] .= new \PHPFUI\Input\Hidden("optionName[{$id}]", $option->optionName);

			$opener = new \PHPFUI\FAIcon('far', 'edit');
			$form->saveOnClick($opener);
			$reveal = new \PHPFUI\Reveal($this->page, $opener);
			$reveal->add(new \PHPFUI\CloseButton($reveal));
			$reveal->addClass('large');
			$reveal->add($this->getSelectionEditor($option));
			$row['edit'] = $opener;

			if (! \count($option->GaRiderSelectionChildren))
				{
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute([$recordId => $id]));
				$row['Del'] = $icon;
				}
			$table->addRow($row);
			}
		$container->add($table);
		$addOptionButton = new \PHPFUI\Button('Add Option');
		$addOptionButton->addClass('success');
		$form->saveOnClick($addOptionButton);
		$this->addOptionModal($event, $addOptionButton);
		$container->add($addOptionButton);

		return $container;
		}

	private function getPricing(\App\Record\GaEvent $event, \PHPFUI\Form $form) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$pricing = $this->gaPriceDateTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $event->gaEventId))->addOrderBy('date')->getRecordCursor();
		$table = new \PHPFUI\Table();
		$table->setRecordId($recordId = 'gaPriceDateId');
		$delete = new \PHPFUI\AJAX('deletePrice', 'Permanently delete this price?');
		$delete->addFunction('success', "$('#{$recordId}-'+data.response).css('background-color','red').hide('fast').remove()");
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
		$addPriceButton->addClass('success');
		$form->saveOnClick($addPriceButton);
		$this->addPriceModal($event, $addPriceButton);
		$container->add($addPriceButton);

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

	private function getSelectionEditor(\App\Record\GaOption $option = new \App\Record\GaOption()) : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$form->add($this->getOptionFields($option));

		$table = new \PHPFUI\OrderableTable($this->page);
		$table->setRecordId($recordId = 'gaSelectionId');
		$deleteSelection = new \PHPFUI\AJAX('deleteSelection', 'Permanently delete this selection?');
		$deleteSelection->addFunction('success', "$('#{$recordId}-'+data.response).css('background-color','red').hide('fast').remove()");
		$this->page->addJavaScript($deleteSelection->getPageJS());

		$headers = ['selectionName' => 'Selection', 'additionalPrice' => 'Additional Price', 'csvValue' => 'CSV Value', 'del' => 'Del', ];
		$table->setHeaders($headers);

		foreach ($option->GaSelectionChildren as $selection)
			{
			$row = $selection->toArray();
			$row['selectionName'] = new \PHPFUI\Input\Text("selectionName[{$selection->gaSelectionId}]", '', $selection->selectionName);
			$row['selectionName'] .= new \PHPFUI\Input\Hidden("gaSelectionId[{$selection->gaSelectionId}]", (string)$selection->gaSelectionId);
			$row['selectionName'] .= new \PHPFUI\Input\Hidden("ordering[{$selection->gaSelectionId}]", (string)$selection->ordering);
			$row['additionalPrice'] = new \PHPFUI\Input\Number("additionalPrice[{$selection->gaSelectionId}]", '', \number_format($selection->additionalPrice ?? 0.0, 2));
			$row['csvValue'] = new \PHPFUI\Input\Text("csvValue[{$selection->gaSelectionId}]", '', $selection->csvValue);

			if (! \count($selection->GaRiderSelectionChildren))
				{
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $deleteSelection->execute([$recordId => $selection->gaSelectionId]));
				$row['del'] = $icon;
				}
			$table->addRow($row);
			}
		$form->add($table);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton(new \PHPFUI\Submit('Save Option'));
		$addSelectionButton = new \PHPFUI\Button('Add Selection');
		$form->saveOnClick($addSelectionButton);
		$addSelectionButton->addClass('success');

		$modal = new \PHPFUI\Reveal($this->page, $addSelectionButton);
		$modalForm = new \PHPFUI\Form($this->page);
		$modalForm->setAreYouSure(false);
		$modal->addClass('large');
		$modalForm->add(new \PHPFUI\SubHeader('Add A Selection'));
		$modalForm->add(new \PHPFUI\Input\Hidden('gaEventId', (string)$option->gaEventId));
		$modalForm->add(new \PHPFUI\Input\Hidden('gaOptionId', (string)$option->gaOptionId));
		$modalForm->add(new \PHPFUI\Input\Hidden('ordering', '0'));
		$selectionName = new \PHPFUI\Input\Text('selectionName', 'Selection Name');
		$additionalPrice = new \PHPFUI\Input\Number('additionalPrice', 'Additional Price');
		$modalForm->add(new \PHPFUI\MultiColumn($selectionName, $additionalPrice));

		$modalForm->add($modal->getButtonAndCancel(new \PHPFUI\Submit('Add Selection')));
		$modal->add($modalForm);

		$buttonGroup->addButton($addSelectionButton);
		$closeButton = new \PHPFUI\Button('Close');
		$closeButton->addAttribute('aria-label', 'Close')->addAttribute('data-close');
		$closeButton->addClass('hollow')->addClass('secondary');

		$buttonGroup->addButton($closeButton);
		$form->add($buttonGroup);

		return $form;
		}

	private function getSignupEmail(\App\Record\GaEvent $event, \App\UI\ErrorFormSaver $form) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$signupMessage = new \App\UI\TextAreaImage('signupMessage', 'Email on Sign Up', \str_replace("\n", '<br>', $event->signupMessage ?? ''));
		$signupMessage->setToolTip('This will be sent to people who successfully register for the event.');
		$signupMessage->htmlEditing($this->page, new \App\Model\TinyMCETextArea($event->getLength('signupMessage')));
		$container->add($signupMessage);

		if ($event->gaEventId)
			{
			$testButton = new \PHPFUI\Submit($this->testText);
			$testButton->addClass('warning');
			$container->add('<hr>');
			$container->add($testButton);
			$form->saveOnClick($testButton);
			}

		return $container;
		}

	private function getWaiver(\App\Record\GaEvent $event, \App\UI\ErrorFormSaver $form) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$signupMessage = new \App\UI\TextAreaImage('waiver', 'Rider Waiver', \str_replace("\n", '<br>', $event->waiver ?? ''));
		$signupMessage->setToolTip('Riders must agree to this waiver to continue registration.');
		$signupMessage->htmlEditing($this->page, new \App\Model\TinyMCETextArea($event->getLength('waiver')));
		$container->add($signupMessage);

		return $container;
		}

	private function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			$post = $_POST;

			if (isset($post['action']))
				{
				switch ($post['action'])
					{
					case 'deletePrice':
						$gaPriceDate = new \App\Record\GaPriceDate((int)$post['gaPriceDateId']);
						$gaPriceDate->delete();
						$this->page->setResponse($post['gaPriceDateId']);

						break;

					case 'deleteOption':
						$gaOption = new \App\Record\GaOption();
						$gaOption->gaOptionId = (int)$post['gaOptionId'];
						$gaOption->delete();
						$this->page->setResponse($post['gaOptionId']);

						break;

					case 'deleteSelection':
						$gaSelection = new \App\Record\GaSelection();
						$gaSelection->gaSelectionId = (int)$post['gaSelectionId'];
						$gaSelection->delete();
						$this->page->setResponse($post['gaSelectionId']);

						break;

					case 'Add':
						unset($post['gaEventId']);
						$gaEvent = new \App\Record\GaEvent();
						$gaEvent->setFrom($post);
						$gaEventId = $gaEvent->insert();
						$this->page->redirect('/GA/edit/' . $gaEventId);

						break;
					}
				}
			elseif (isset($post['submit']))
				{
				if ('Add Price' == $post['submit'])
					{
					unset($post['gaPriceDateId']);
					$gaPriceDate = new \App\Record\GaPriceDate();
					$gaPriceDate->setFrom($post);
					$gaPriceDate->insert();
					$this->page->redirect();
					}
				elseif ('Add Option' == $post['submit'])
					{
					$option = new \App\Record\GaOption();
					$option->setFrom($post);
					$option->insert();
					$this->page->redirect();
					}
				elseif ('Add Selection' == $post['submit'])
					{
					$selection = new \App\Record\GaSelection();
					$selection->setFrom($post);
					$selection->insert();
					$this->page->redirect();
					}
				elseif ('Save Option' == $post['submit'])
					{
					$option = new \App\Record\GaOption($post['gaOptionId']);
					$option->optionName = $post['optionName'];
					$option->required = (int)$post['required'];
					$option->price = (float)$post['price'];
					$option->maximumAllowed = (int)$post['maximumAllowed'];
					$option->csvField = $post['csvField'];
					$option->update();
					$gaSelectionTable = new \App\Table\GaSelection();
					$ordering = 0;

					foreach ($post['ordering'] as &$value)
						{
						$value = ++$ordering;
						}
					$gaSelectionTable->updateFromTable($post);
					$this->page->redirect();
					}
				elseif ($this->testText == $post['submit'])
					{
					$gaEvent = new \App\Record\GaEvent($post['gaEventId']);
					$model = new \App\Model\GeneralAdmission();
					$gaRiderTable = new \App\Table\GaRider();
					$gaRiderTable->setLimit(1);
					$gaRiderTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', (int)$post['gaEventId']));
					$rider = $gaRiderTable->getRecordCursor()->current();
					$sender = \App\Model\Session::signedInMemberRecord();
					$membership = $sender->membership;
					$rider->address = $membership->address;
					$rider->comments = 'Test email comment';
					$rider->contact = $sender->emergencyContact;
					$rider->contactPhone = $sender->emergencyPhone;
					$rider->email = $sender->email;
					$rider->firstName = $sender->firstName;
					$rider->gaEvent = $gaEvent;
					$rider->lastName = $sender->lastName;
					$rider->memberId = $sender->memberId;
					$rider->pending = 0;
					$rider->phone = $sender->cellPhone;
					$rider->prize = 0;
					$rider->signedUpOn = \date('Y-m-d H:i:s');
					$rider->state = $membership->state;
					$rider->town = $membership->town;
					$rider->zip = $membership->zip;

					$model->addRiderToEmail($gaEvent, $rider);
					\App\Model\Session::setFlash('success', 'Check your inbox for a test email');
					$this->page->redirect();
					}
				}
			}
		}
	}
