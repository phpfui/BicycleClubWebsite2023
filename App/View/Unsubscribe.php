<?php

namespace App\View;

class Unsubscribe implements \Stringable
	{
	public function __construct(private readonly \App\View\Page $page, private readonly string $subheader, private readonly \PHPFUI\ORM\Record $record, private string $email)
		{
		$this->email = \strtolower($email);
		}

	public function __toString() : string
		{
		if (! $this->record->loaded())
			{
			return (string)new \PHPFUI\SubHeader('Person not found');
			}

		if (! empty($this->record->email) && \strtolower((string)$this->record->email) != \strtolower($this->email))
			{
			return (string)new \PHPFUI\SubHeader('Email not found');
			}
		$output = new \PHPFUI\Container();
		$unsubscribe = $this->record->emailAnnouncements ?? 0;
		$type = $unsubscribe ? 'Unsubscribe' : 'Subscribe';
		$output->add(new \PHPFUI\Header($type));
		$settingTable = new \App\Table\Setting();
		$output->add(new \PHPFUI\SubHeader('To ' . $settingTable->value('clubAbbrev') . ' ' . $this->subheader));
		$submit = new \PHPFUI\Submit('Confirm');
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			// @phpstan-ignore-next-line
			$this->record->emailAnnouncements = 0 == $unsubscribe ? 1 : 0;
			$this->record->update();
			$this->page->setResponse('Thank you. You have been ' . $type . 'd.');
			}
		else
			{
			$form->add(new \App\UI\Display('Your email address', $this->email));
			$form->add($submit);
			}
		$output->add($form);

		return (string)$output;
		}
	}
