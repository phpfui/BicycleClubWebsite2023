<?php

namespace App\View\Volunteer;

class Email
	{
	private string $emailJobMessage = 'Email Job Volunteers';

	private string $emailMessage = 'Email All Volunteers';

	private string $testMessage = 'Send Test Email To You Only';

	public function __construct(private readonly \App\View\Page $page)
		{
		if (\App\Model\Session::checkCSRF() && $_POST['submit'])
			{
			\App\Model\Session::setFlash('post', $_POST);
			$model = new \App\Model\Volunteer();
			$memberTable = new \App\Table\Member();
			$eventId = $_POST['eventid'];

			if (isset($_POST['jobId']))
				{
				$job = new \App\Record\Job($jobId = (int)$_POST['jobId']);
				$members = $memberTable->getVolunteersForJob($job);
				$who = $job->title . ' volunteers';
				}
			else
				{
				$jobId = 0;
				$members = $memberTable->getVolunteersForEvents([$eventId]);
				$who = 'all volunteers';
				}

			if ($_POST['submit'] == $this->testMessage)
				{
				$members = [\App\Model\Session::getSignedInMember()];
				$who = 'only you';
				}
			$message = "Your email was sent to {$who}";
			\App\Model\Session::setFlash('success', $message);
			$model->email($eventId, $jobId, $members, $_POST['title'], $_POST['message'], $_POST['shiftInfo'], $_FILES);
			$this->page->redirect();
			}
		}

	public function allVolunteers(\App\Record\JobEvent $jobEvent) : \PHPFUI\Form
		{
		$post = \App\Model\Session::getFlash('post');

		$form = new \PHPFUI\Form($this->page);

		if ($jobEvent->jobEventId)
			{
			$form->add(new \PHPFUI\SubHeader($jobEvent->name));
			$form->add(new \App\View\Volunteer\Menu($jobEvent, 'Email'));
			$fieldSet = new \PHPFUI\Input\Hidden('eventid', (string)$jobEvent->jobEventId);
			}
		else
			{
			$fieldSet = new \PHPFUI\FieldSet('Select Event');
			$select = new \PHPFUI\Input\Select('eventid');
			$jobEvents = new \App\Table\JobEvent();
			$events = $jobEvents->getJobEvents();

			foreach ($events as $event)
				{
				$select->addOption($event->name, $event->jobEventId, $post['eventId'][$event->jobEventId] ?? false);
				}
			$fieldSet->add($select);
			}
		$form->add($fieldSet);
		$form->add($this->getEmailBody());
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$emailAll = new \PHPFUI\Submit($this->emailMessage);
		$emailAll->setConfirm('Are you sure you want to email all volunteers?');
		$emailAll->addClass('warning');
		$buttonGroup->addButton($emailAll);
		$test = new \PHPFUI\Submit($this->testMessage);
		$test->addClass('success');
		$buttonGroup->addButton($test);
		$form->add($buttonGroup);

		return $form;
		}

	public function job(\App\Record\Job $job) : \PHPFUI\HTML5Element
		{
		if ($job->empty())
			{
			return new \PHPFUI\SubHeader('Job not found');
			}
		$title = $job->title;

		$form = new \PHPFUI\Form($this->page);
		$form->add(new \PHPFUI\SubHeader($job->jobEvent->name));
		$form->add(new \App\View\Volunteer\Menu($job->jobEvent, 'Jobs'));
		$form->add(new \App\View\Volunteer\JobSubMenu($job, 'Email Volunteers'));
		$form->add(new \PHPFUI\Input\Hidden('jobId', (string)$job->jobId));
		$form->add(new \PHPFUI\Input\Hidden('eventid', (string)$job->jobEventId));
		$form->add(new \PHPFUI\Header($title, 4));
		$form->add($this->getEmailBody());
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$emailAll = new \PHPFUI\Submit($this->emailJobMessage);
		$emailAll->addClass('warning');
		$emailAll->setConfirm("Are you sure you want to email {$title} volunteers?");
		$buttonGroup->addButton($emailAll);
		$test = new \PHPFUI\Submit($this->testMessage);
		$test->addClass('success');
		$buttonGroup->addButton($test);
		$form->add($buttonGroup);

		return $form;
		}

	private function getEmailBody() : \PHPFUI\FieldSet
		{
		$post = \App\Model\Session::getFlash('post');

		$fieldSet = new \PHPFUI\FieldSet('Email Information');
		$member = \App\Model\Session::getSignedInMember();
		$input = new \PHPFUI\Input\Text('from', 'From', $member['firstName'] . ' ' . $member['lastName']);
		$input->addAttribute('disabled');
		$fieldSet->add($input);
		$title = new \PHPFUI\Input\Text('title', 'Title', $post['title'] ?? '');
		$title->setRequired();
		$fieldSet->add($title);
		$message = new \PHPFUI\Input\TextArea('message', 'Message', $post['message'] ?? '');
		$message->addAttribute('placeholder', 'Message to volunteers');
		$message->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$message->setRequired();
		$fieldSet->add($message);
		$attachment = new \PHPFUI\Input\File($this->page, 'attachment', 'Attach a File');
		$fieldSet->add($attachment);
		$fieldSet->add(new \PHPFUI\Input\CheckBoxBoolean('shiftInfo', 'Send shift information at the bottom of each email', $post['shiftInfo'] ?? true));

		return $fieldSet;
		}
	}
