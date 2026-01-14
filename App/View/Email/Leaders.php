<?php

namespace App\View\Email;

class Leaders implements \Stringable
	{
	private string $emailText = 'Email All Ride Leaders';

	private string $testText = 'Sent Test Email';

	public function __construct(private readonly \App\View\Page $page)
		{
		if (\App\Model\Session::checkCSRF())
			{
			$message = 'Unknown command';
			$status = 'error';
			$post = $_POST;
			$post['categories'] ??= [];
			$post['fromDate'] ??= \App\Tools\Date::todayString();
			$post['toDate'] ??= \App\Tools\Date::todayString(100);
			\App\Model\Session::setFlash('post', $post);

			$type = empty($post['coordinatorsOnly']) ? 'Ride Leader' : 'Ride Coordinator';
			$leaders = \App\Table\Member::getLeaders($post['categories'], $type, $post['fromDate'], $post['toDate'], $post['timesLed']);
			$email = new \App\Tools\EMail();
			$email->setSubject($post['subject']);
			$member = \App\Model\Session::getSignedInMember();
			$name = $member['firstName'] . ' ' . $member['lastName'];
			$emailAddress = $member['email'];
			$phone = $member['phone'];
			$email->setFromMember($member);
			$settings = new \App\Table\Setting();
			$link = $settings->value('homePage');
			$email->setHtml();
			$email->setBody(\App\Tools\TextHelper::cleanUserHtml($post['message']) . "<p>This email was sent to ride leaders from {$link} by {$name}\n{$emailAddress}\n{$phone}");

			if ($post['submit'] == $this->emailText)
				{
				foreach ($leaders as $leader)
					{
					$email->addBCCMember($leader->toArray());
					}
				$email->bulkSend();
				$message = 'Your email was sent to ' . \count($leaders) . ' leaders';
				$status = 'success';
				}
			elseif ($post['submit'] == $this->testText)
				{
				$email->setToMember($member);
				$email->send();
				$message = '<b>Check your inbox for a test email. Your email would be sent to the following ' . \count($leaders) . ' leaders:</b>';
				$multiColumn = new \PHPFUI\MultiColumn();

				foreach ($leaders as $leader)
					{
					$multiColumn->add($leader->fullName());

					if (4 == \count($multiColumn))
						{
						$message .= $multiColumn;
						$multiColumn = new \PHPFUI\MultiColumn();
						}
					}

				if (\count($multiColumn))
					{
					while (\count($multiColumn) < 4)
						{
						$multiColumn->add('&nbsp;');
						}
					$message .= $multiColumn;
					}
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
		$fieldSet = new \PHPFUI\FieldSet('Selection Criteria');

		$accordion = new \PHPFUI\Accordion();
		$accordion->addAttribute('data-multi-expand', 'true');
		$accordion->addAttribute('data-allow-all-closed', 'true');

		$fieldSet->add($accordion);
		$picker = new \App\UI\MultiCategoryPicker('categories', 'Category Restriction', $post['categories'] ?? []);
		$picker->setToolTip('Pick specific categories if you to restrict the email, optional');
		$accordion->addTab('Category Restriction', $picker);

		$coordinatorsOnly = new \PHPFUI\Input\CheckBoxBoolean('coordinatorsOnly', 'Ride Coordinators Only', $post['coordinatorsOnly'] ?? false);
		$timesLed = new \PHPFUI\Input\Number('timesLed', 'Minimum Number of Leads in Date Range', $post['timesLed'] ?? '0');
		$timesLed->addAttribute('min', '0');
		$timesLed->addAttribute('step', '1');
		$coordinators = new \PHPFUI\MultiColumn($coordinatorsOnly, $timesLed);
		$accordion->addTab('Coordinators and Times Led', $coordinators);

		$dates = new \PHPFUI\MultiColumn();
		$from = new \PHPFUI\Input\Date($this->page, 'fromDate', 'Leading Rides From', $post['fromDate'] ?? '');
		$from->setToolTip('Only leaders leading a ride from this date and beyond will get the email');
		$dates->add($from);
		$to = new \PHPFUI\Input\Date($this->page, 'toDate', 'Leading Rides Until', $post['toDate'] ?? '');
		$to->setToolTip('Only leaders leading a ride upto this date will get the email');
		$dates->add($to);
		$accordion->addTab('Led Rides Dates', $dates);


		$form->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Email');
		$subject = new \PHPFUI\Input\Text('subject', 'Subject', $post['subject'] ?? '');
		$subject->setRequired();
		$subject->addAttribute('placeholder', 'Email Subject');
		$fieldSet->add($subject);
		$message = new \App\UI\TextAreaImage('message', 'Message', $post['message'] ?? '');
		$message->addAttribute('placeholder', 'Message to leaders?');
		$message->htmlEditing($this->page, new \App\Model\TinyMCETextArea(new \App\Record\MailItem()->getLength('body')));
		$message->setRequired();
		$fieldSet->add($message);
		$form->add($fieldSet);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$sendButton = new \PHPFUI\Submit($this->emailText);
		$sendButton->setConfirm('Email all leaders?');
		$sendButton->addClass('warning');
		$buttonGroup->addButton($sendButton);
		$testButton = new \PHPFUI\Submit($this->testText);
		$testButton->addClass('success');
		$buttonGroup->addButton($testButton);
		$form->add($buttonGroup);

		return (string)$form;
		}
	}
