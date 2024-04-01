<?php

namespace App\View\Volunteer;

class Reports
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function history() : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Enter Report Criteria');

		$today = \App\Tools\Date::today();
		$start = $today - 365;
		$startDate = new \PHPFUI\Input\Date($this->page, 'start', 'Start Date', \App\Tools\Date::toString($start));
		$startDate->setRequired();
		$endDate = new \PHPFUI\Input\Date($this->page, 'end', 'End Date', \App\Tools\Date::toString($today));
		$endDate->setRequired();
		$fieldSet->add(new \PHPFUI\MultiColumn($startDate, $endDate));
		$form->add($fieldSet);

		$form->add(new \PHPFUI\Submit('Print'));

		return $form;
		}

	public function show(\App\Record\JobEvent $jobEvent) : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);

		if ($jobEvent->empty())
			{
			$this->page->redirect('/Volunteer/events');

			return $form;
			}

		$form->add(new \PHPFUI\SubHeader($jobEvent->name));
		$form->add(new \App\View\Volunteer\Menu($jobEvent, 'Reports'));
		$form->addAttribute('target', '_blank');
		$fieldSet = new \PHPFUI\FieldSet('Select Job Report Details');
		$fieldSet->add($this->makeCB('details', 'Job Description'));
		$fieldSet->add($this->makeCB('current', 'Current Volunteers'));
		$fieldSet->add($this->makeCB('needed', 'Needed Volunteers'));
		$fieldSet->add($this->makeCB('multiple', 'Multiple Jobs per page'));
		$form->add($fieldSet);

		$pollTable = new \App\Table\VolunteerPoll();
		$polls = $pollTable->getPolls($jobEvent);

		if (\count($polls))
			{
			$form->add('<h3>Poll Reports</h3>');
			$fieldSet = new \PHPFUI\FieldSet('Select Polls for Report');

			foreach ($polls as $poll)
				{
				$fieldSet->add($this->makeCB('pollId-' . $poll['volunteerPollId'], "<b>{$poll['question']}</b> ({$poll['name']})"));
				$fieldSet->add($this->makeCB('detail-' . $poll['volunteerPollId'], 'Print Individual Member Detail', true));
				}
			$form->add($fieldSet);
			}
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->addButton(new \PHPFUI\Submit('Print Reports'));
		$form->add($buttonGroup);

		return $form;
		}

	private function makeCB(string $name, string $label, bool $indent = false) : \PHPFUI\GridX
		{
		$row = new \PHPFUI\GridX();

		if ($indent)
			{
			$row->add(' &nbsp; &bull; &nbsp; ');
			}
		$row->add(new \PHPFUI\Input\CheckBoxBoolean($name, $label));

		return $row;
		}
	}
