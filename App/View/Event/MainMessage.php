<?php

namespace App\View\Event;

class MainMessage
	{
	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->settingTable = new \App\Table\Setting();
		}

	/**
	 * @param array<string, mixed> $fields
	 */
	public function getEditor(string $type, array $fields) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			foreach ($_POST as &$value)
				{
				$value = \str_replace('&nbsp;', ' ', (string)$value);
				}
			unset($value);
			$this->settingTable->save($type . 'Title', $_POST[$type . 'Title']);
			$this->settingTable->saveHtml($type . 'Body', $_POST[$type . 'Body']);
			$this->page->setResponse('Saved');
			}
		elseif (\App\Model\Session::checkCSRF())
			{
			$reservationModel = new \App\Model\Reservation();
			$reservationTable = new \App\Table\Reservation();
			$reservationTable->setWhere(new \PHPFUI\ORM\Condition('eventId', $_POST['eventId']));
			$reservation = $reservationTable->getRecordCursor()->current();
			$email = $reservationModel->getEmail($_POST['paymentType'], $reservation);
			$email->setToMember(\App\Model\Session::getSignedInMember());
			$email->send();
			$this->page->redirect('', 'emailSent');
			}
		else
			{
			if (isset($_GET['emailSent']))
				{
				$alert = new \App\UI\Alert('You should receive a test email shortly');
				$alert->setFadeOut($this->page);
				$form->add($alert);
				}
			$fieldSet = new \PHPFUI\FieldSet('Substitution Fields');
			$fieldSet->add(new \App\UI\SubstitutionFields($fields));
			$form->add($fieldSet);
			$fieldSet = new \PHPFUI\FieldSet('Email Settings');
			$value = $this->settingTable->value($type . 'Title');
			$subject = new \PHPFUI\Input\Text($type . 'Title', 'Email Subject line', $value);
			$subject->setToolTip('You can the above fields here to insert text specific to the event.');
			$fieldSet->add($subject);
			$value = $this->settingTable->value($type . 'Body');
			$textarea = new \App\UI\TextAreaImage($type . 'Body', 'Email Body', $value);
			$textarea->setToolTip('You can use ~instructions~ to include payment instructions as well as all the above fields.');
			$textarea->htmlEditing($this->page, new \App\Model\TinyMCETextArea(new \App\Record\Setting()->getLength('value')));
			$fieldSet->add($textarea);
			$form->add($fieldSet);
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);
			$test = new \PHPFUI\Button('Test');
			$test->addClass('warning');
			$this->getModal($test);
			$buttonGroup->addButton($test);
			$form->add($buttonGroup);
			}

		return $form;
		}

	private function getModal(\PHPFUI\HTML5Element $link) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $link);
		$modalForm = new \PHPFUI\Form($this->page);
		$modalForm->setAreYouSure(false);
		$modalForm->add(new \PHPFUI\SubHeader('Test Event Registration email'));
		$eventView = new \App\View\Event\Events($this->page);
		$modalForm->add($eventView->getSelect());
		$paymentType = new \PHPFUI\Input\Select('paymentType', 'Payment Type');
		$paymentType->setToolTip('Select a payment type to test');
		$types = \App\Model\Event::getEmailTypes();
		unset($types['Event']);

		foreach ($types as $type => $name)
			{
			$paymentType->addOption($name, $type);
			}
		$modalForm->add($paymentType);
		$test = new \PHPFUI\Submit('Test');
		$modalForm->add($test);
		$modal->add($modalForm);
		}
	}
