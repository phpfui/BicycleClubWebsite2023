<?php

namespace App\View\Email;

class Event implements \Stringable
	{
	private string $emailText = 'Email All Attendees';

	private string $testMessage = 'Send Test Email';

	public function __construct(private readonly \App\View\Page $page, private readonly \App\Record\Event $event)
		{
		if (\App\Model\Session::checkCSRF())
			{
			$email = new \App\Tools\EMail();
			$member = \App\Model\Session::getSignedInMember();
			$email->setSubject($_POST['subject']);
			$email->setFrom($member['email'], $member['firstName'] . ' ' . $member['lastName']);
			$email->setHtml();
			$message = 'Unknown command';
			$status = 'error';
			$persons = \App\Table\Reservation::getEmails($this->event->eventId, $_POST['status']);
			\App\Model\Session::setFlash('post', $_POST);

			if ($_POST['submit'] == $this->testMessage)
				{
				$body = \str_replace(['~firstName~', '~lastName~', '~email~'], [$member['firstName'], $member['lastName'], $member['email']], $_POST['message']);
				$email->setBody(\App\Tools\TextHelper::cleanUserHtml($body));
				$email->setTo($member['email'], $member['firstName'] . ' ' . $member['lastName']);
				$email->send();
				$status = 'success';
				$message = 'Check your inbox for a test email.<br>Your email would be sent to ' . \count($persons) . ' people';
				}
			elseif ($_POST['submit'] == $this->emailText)
				{
				foreach ($persons as $person)
					{
					$body = \str_replace(['~firstName~', '~lastName~', '~email~'], [$person['firstName'], $person['lastName'], $person['email']], $_POST['message']);
					$email->setBody(\App\Tools\TextHelper::cleanUserHtml($body));
					$email->setTo($person['email'], $person['firstName'] . ' ' . $person['lastName']);
					$email->send();
					}
				$message = 'Your email was sent to ' . \count($persons) . ' people';
				$status = 'success';
				}
			\App\Model\Session::setFlash($status, $message);
			$this->page->redirect();
			}
		}

	public function __toString() : string
		{
		$post = \App\Model\Session::getFlash('post');

		$form = new \PHPFUI\Form($this->page);
		$form->add(new \PHPFUI\Header($this->event->title, 4));

		$fieldSet = new \PHPFUI\FieldSet('Selection Criteria');
		$status = new \PHPFUI\Input\RadioGroup('status', 'Payment Status', $post['status'] ?? '');
		$status->setRequired();
		$status->addButton('All', (string)0);
		$status->addButton('Paid', (string)1);
		$status->addButton('Unpaid', (string)2);
		$fieldSet->add($status);
		$form->add($fieldSet);
		$fieldSet = new \PHPFUI\FieldSet('Email');
		$subject = new \PHPFUI\Input\Text('subject', 'Subject', $post['subject'] ?? $this->event->title);
		$subject->setRequired();
		$subject->addAttribute('placeholder', 'Email Subject');
		$fieldSet->add($subject);
		$message = new \App\UI\TextAreaImage('message', 'Message', $post['message'] ?? '');
		$message->addAttribute('placeholder', 'Message to all attendees?');
		$message->htmlEditing($this->page, new \App\Model\TinyMCETextArea(new \App\Record\MailItem()->getLength('body')));
		$message->setRequired();
		$fieldSet->add($message);
		$form->add($fieldSet);
		$div = new \PHPFUI\Callout('info');
		$div->add('<b>Substitution fields:</b> ~firstName~, ~lastName~, ~email~');
		$form->add($div);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$emailAll = new \PHPFUI\Submit($this->emailText);
		$emailAll->addClass('warning');
		$emailAll->setConfirm('Are you sure you want to email all attendees?');
		$buttonGroup->addButton($emailAll);
		$test = new \PHPFUI\Submit($this->testMessage);
		$test->addClass('success');
		$buttonGroup->addButton($test);
		$form->add($buttonGroup);

		return (string)$form;
		}
	}
