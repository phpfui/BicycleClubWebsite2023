<?php

namespace App\View\Volunteer;

class JobEdit
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function getJobForm(\App\Record\JobEvent $jobEvent, \App\Record\Job $job, ?\PHPFUI\Submit $submit = null) : \PHPFUI\Form
		{
		if ($jobEvent->empty())
			{
			$this->page->redirect('/Volunteer/events');
			}
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			unset($_POST['jobId']);
			$job->setFrom($_POST);
			$job->update();
			$this->page->setResponse('Saved');
			}
		else
			{
			$fieldSet = new \PHPFUI\FieldSet('Job Details for ' . $jobEvent->name);
			$fieldSet->add(new \PHPFUI\Input\Hidden('jobId', (string)$job->jobId));
			$fieldSet->add(new \PHPFUI\Input\Hidden('jobEventId', (string)$jobEvent->jobEventId));
			$date = new \PHPFUI\Input\Date($this->page, 'date', 'Actual Date Of Job', $job->date ?: $jobEvent->date);
			$date->setRequired()->setToolTip('The date the volunteer needs to do the job.');
			$fieldSet->add($date);
			$title = new \PHPFUI\Input\Text('title', 'Job Title', $job->title);
			$title->setRequired()->setToolTip('This is the job title, so make it clear and descriptive');
			$fieldSet->add($title);
			$location = new \PHPFUI\Input\Text('location', 'Location', $job->location);
			$location->setRequired()->setToolTip('Where the volunteer should show up');
			$fieldSet->add($location);
			$description = new \PHPFUI\Input\TextArea('description', 'Description', $job->description);
			$description->setRequired()->setToolTip('What the volunteer is expected to do, so make it clear and descriptive');
			$fieldSet->add($description);
			$form->add($fieldSet);
			}

		return $form;
		}

	public function output(\App\Record\Job $job) : \PHPFUI\Form
		{
		if ($job->empty())
			{
			$this->page->redirect('/Volunteer/events');
			}
		$submit = new \PHPFUI\Submit();
		$form = $this->getJobForm($job->jobEvent, $job, $submit);

		$jobShiftTable = new \App\Table\JobShift();
		$shifts = $jobShiftTable->getJobShifts($job->jobId);

		if (! \count($shifts))
			{
			$callout = new \PHPFUI\Callout('alert');
			$link = new \PHPFUI\Link("/Volunteer/editShift/{$job->jobId}", 'add shifts to this job', false);
			$callout->add("You need to {$link}.");
			$form->add($callout);
			}

		$jobEvent = $job->jobEvent;
		$form->addAsFirst(new \PHPFUI\Header($job->title, 4));
		$form->addAsFirst(new \App\View\Volunteer\JobSubMenu($job, 'Job Details'));
		$form->addAsFirst(new \App\View\Volunteer\Menu($jobEvent, 'Jobs'));
		$form->addAsFirst(new \PHPFUI\SubHeader($jobEvent->name));

		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->addButton($submit);

		$form->add($buttonGroup);

		return $form;
		}
	}
