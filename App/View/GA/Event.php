<?php

namespace App\View\GA;

class Event
	{
	public function __construct(private \App\View\Page $page)
		{
		}

	public function copyDialog(\App\Record\GaEvent $gaEvent) : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);

		$fieldSet = new \PHPFUI\FieldSet('Original');
		$fieldSet->add(new \App\UI\Display('Name', $gaEvent->title));
		$fieldSet->add(new \App\UI\Display('Date', $gaEvent->eventDate));
		$form->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('New');
		$title = new \PHPFUI\Input\Text('title', 'New Name', $gaEvent->title);
		$title->setRequired();
		$fieldSet->add($title);
		$date = new \PHPFUI\Input\Date($this->page, 'eventDate', 'New Date', \App\Tools\Date::increment($gaEvent->eventDate, 365));
		$date->setToolTip('All dates in the copied event will be offset by the difference between these two dates')->setRequired();
		$fieldSet->add($date);
		$form->add($fieldSet);

		$submit = new \PHPFUI\Submit('Copy');
		$submit->addClass('success');
		$form->add($submit);

		if ($submit->submitted($_POST))
			{
			$gaModel = new \App\Model\GeneralAdmission();
			$newEvent = $gaModel->copy($gaEvent, $_POST['eventDate'], $_POST['title']);
			$this->page->redirect('/GA/edit/' . $newEvent->gaEventId);
			}

		return $form;
		}
	}
