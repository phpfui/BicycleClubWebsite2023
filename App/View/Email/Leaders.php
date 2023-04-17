<?php

namespace App\View\Email;

class Leaders implements \Stringable
	{
	private string $emailText = 'Email All Leaders';

	private string $testText = 'Sent Test Email';

	public function __construct(private readonly \App\View\Page $page)
		{
		if (\App\Model\Session::checkCSRF())
			{
			$message = 'Unknown command';
			$status = 'error';
			\App\Model\Session::setFlash('post', $_POST);
			$type = empty($_POST['coordinatorsOnly']) ? 'Ride Leader' : 'Ride Coordinator';
			$leaders = \App\Table\Member::getLeaders($_POST['categories'] ?? [], $type, $_POST['fromDate'] ?? \App\Tools\Date::todayString(), $_POST['toDate'] ?? \App\Tools\Date::todayString(100));
			$email = new \App\Tools\EMail();
			$email->setSubject($_POST['subject']);
			$member = \App\Model\Session::getSignedInMember();
			$name = $member['firstName'] . ' ' . $member['lastName'];
			$emailAddress = $member['email'];
			$phone = $member['phone'];
			$email->setFromMember($member);
			$settings = new \App\Table\Setting();
			$link = $settings->value('homePage');
			$email->setHtml();
			$email->setBody(\App\Tools\TextHelper::cleanUserHtml($_POST['message']) . "<p>This email was sent to ride leaders from {$link} by {$name}\n{$emailAddress}\n{$phone}");

			if ($_POST['submit'] == $this->emailText)
				{
				foreach ($leaders as $leader)
					{
					$email->addBCCMember($leader->toArray());
					}
				$email->bulkSend();
				$message = 'Your email was sent to ' . \count($leaders) . ' leaders';
				$status = 'success';
				}
			elseif ($_POST['submit'] == $this->testText)
				{
				$email->setToMember($member);
				$email->send();
				$message = 'Check your inbox for a test email.<br>Your email would be sent to ' . \count($leaders) . ' leaders';
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
		$categoryView = new \App\View\Categories($this->page, new \PHPFUI\Button('back'));
		$picker = $categoryView->getMultiCategoryPicker('categories', 'Category Restriction', $post['categories'] ?? []);
		$picker->setToolTip('Pick specific categories if you to restrict the email, optional');
		$columna = new \PHPFUI\Cell(12, 6);
		$columna->add($picker);
		$columnb = new \PHPFUI\Cell(12, 6);
		$coordinatorsOnly = new \PHPFUI\Input\CheckBoxBoolean('coordinatorsOnly', 'Ride Coordinators Only', $post['coordinatorsOnly'] ?? false);
		$columnb->add($coordinatorsOnly);
		$from = new \PHPFUI\Input\Date($this->page, 'fromDate', 'Leading Rides From', $post['fromDate'] ?? '');
		$from->setToolTip('Only leaders leading a ride from this date and beyond will get the email');
		$columnb->add($from);
		$to = new \PHPFUI\Input\Date($this->page, 'toDate', 'Leading Rides Until', $post['toDate'] ?? '');
		$to->setToolTip('Only leaders leading a ride upto this date will get the email');
		$columnb->add($to);
		$fieldSet->add($columna);
		$fieldSet->add($columnb);
		$form->add($fieldSet);
		$fieldSet = new \PHPFUI\FieldSet('Email');
		$subject = new \PHPFUI\Input\Text('subject', 'Subject', $post['subject'] ?? '');
		$subject->setRequired();
		$subject->addAttribute('placeholder', 'Email Subject');
		$fieldSet->add($subject);
		$message = new \PHPFUI\Input\TextArea('message', 'Message', $post['message'] ?? '');
		$message->addAttribute('placeholder', 'Message to leaders?');
		$message->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
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
