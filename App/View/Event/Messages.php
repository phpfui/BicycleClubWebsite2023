<?php

namespace App\View\Event;

class Messages
	{
	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->settingTable = new \App\Table\Setting();
		}

	public function getEditor($type, array $fields) : string
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$this->save($type);
			$this->page->setResponse('Saved');
			}
		else
			{
			if (\App\Model\Session::checkCSRF() && isset($_POST['test']))
				{
				$this->save($type);
				$reservationModel = new \App\Model\Reservation();
				$reservationTable = new \App\Table\Reservation();
				$reservation = $reservationTable->getLatestReservation();
				$email = $reservationModel->getEmail($type, $reservation);
				$member = \App\Model\Session::getSignedInMember();
				$email->setFromMember($member);
				$email->setToMember($member);
				$email->send();
				$alert = new \App\UI\Alert('Test email sent. Check your email.');
				$alert->setFadeout($this->page);
				$form->add($alert);
				}
			$fieldSet = new \PHPFUI\FieldSet('Substitution Fields');
			$fieldSet->add('You can substitute specific fields in the following bodies of text.<p>');
			$multiColumn = new \PHPFUI\MultiColumn();

			foreach ($fields as $field)
				{
				if (\count($multiColumn) >= 3)
					{
					$fieldSet->add($multiColumn);
					$multiColumn = new \PHPFUI\MultiColumn();
					}
				$multiColumn->add("~{$field}~<br>");
				}

			while (\count($multiColumn) < 3)
				{
				$multiColumn->add('&nbsp;');
				}
			$fieldSet->add($multiColumn);
			$form->add($fieldSet);
			$fieldSet = new \PHPFUI\FieldSet('Payment Instructions');
			$value = $this->settingTable->value($type . 'Instructions');
			$textarea = new \PHPFUI\Input\TextArea($type . 'Instructions', '', $value);
			$textarea->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
			$fieldSet->add($textarea);
			$form->add($fieldSet);
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);
			$test = new \PHPFUI\Submit('Test Email', 'test');
			$test->addClass('warning');
			$buttonGroup->addButton($test);
			$form->add($buttonGroup);
			}

		return (string)$form;
		}

	private function save($type) : void
		{
		foreach ($_POST as &$value)
			{
			$value = \str_replace('&nbsp;', ' ', (string)$value);
			}
		unset($value);
		$this->settingTable->save($type . 'Instructions', \App\Tools\TextHelper::cleanUserHtml($_POST[$type . 'Instructions']));
		}
	}
