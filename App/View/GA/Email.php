<?php

namespace App\View\GA;

class Email implements \Stringable
	{
	/**
	 * @var array<string,string>
	 */
	private array $parameters = [];

	private string $testMessage = 'Send Test Email To You Only';

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->parameters = \App\Model\Session::getFlash('post') ?? [];

		if (\App\Model\Session::checkCSRF() && isset($_POST['submit']))
			{
			\App\Model\Session::setFlash('post', $_POST);
			$email = new \App\Tools\EMail();
			$sender = \App\Model\Session::getSignedInMember();
			$email->setSubject($_POST['subject']);
			$name = $sender['firstName'] . ' ' . $sender['lastName'];
			$emailAddress = $sender['email'];
			$email->setFromMember($sender);
			$email->setHtml();
			$settings = new \App\Table\Setting();
			$link = $settings->value('homePage');
			$message = \App\Tools\TextHelper::cleanUserHtml($_POST['message']) . "<p>This email was sent from <a href='{$link}'>{$link}</a> by {$name} {$emailAddress}</p>";
			$settingTable = new \App\Table\Setting();
			$server = $settingTable->value('homePage');
			$message .= "<p><a href='{$server}/GA/~unsubscribe~'>You may Unsubscribe Here</a></p>";
			$email->setBody($message);

			if (isset($_FILES['file']))
				{
				if (! $_FILES['file']['error'])
					{
					$file = $_FILES['file']['tmp_name'];

					if (\is_uploaded_file($file))
						{
						$email->addAttachment(\file_get_contents($file), $_FILES['file']['name']);
						\App\Model\Session::setFlash('warning', 'If you want to resend this email, you must select the file again.  Sorry about that!');
						}
					}
				}
			$email->setHtml();
			$riders = \App\Table\GaRider::getEmailsForEvents($_POST['gaEventId'], $_POST['pending']);

			if ($_POST['submit'] == $this->testMessage)
				{
				$email->addToMember($sender);
				$email->bulkSend();
				\App\Model\Session::setFlash('success', 'Check your inbox for a test email after 5 minutes.  It would have been sent to ' . \count($riders) . ' riders');
				}
			else
				{
				foreach ($riders as $rider)
					{
					$email->addBCC($rider['email'], $rider['firstName'] . ' ' . $rider['lastName'], $rider['gaRiderId'] ?? 0);
					}
				$email->bulkSend();
				\App\Model\Session::setFlash('success', 'You emailed ' . \count($riders) . ' riders.');
				}
			$this->page->redirect();
			}
		}

	public function __toString() : string
		{
		$form = new \PHPFUI\Form($this->page);

		$picker = new \App\View\GA\EventPicker($this->page, \App\View\GA\EventPicker::MULTIPLE, 'Email Riders In These Events');
		$picker->setSelected($this->parameters['gaEventId'] ?? []);

		$form->add($picker);

		$fieldSet = new \PHPFUI\FieldSet('Email Content');
		$subject = new \PHPFUI\Input\Text('subject', 'Subject', $this->parameters['subject'] ?? '');
		$subject->setRequired();
		$subject->addAttribute('placeholder', 'Email Subject');
		$fieldSet->add($subject);
		$message = new \PHPFUI\Input\TextArea('message', 'Message', $this->parameters['message'] ?? '');
		$message->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$message->addAttribute('placeholder', 'Message to all riders');
		$message->setRequired();
		$fieldSet->add($message);
		$fieldSet->add(new \PHPFUI\Input\File($this->page, 'file', 'Optional file to attach'));
		$pending = new \PHPFUI\Input\CheckBoxBoolean('pending', 'Just Send to Unpaid Riders');
		$pending->setToolTip('If checked, the email will only go to riders who have signed up, but not paid. Otherwise it only goes to paid riders.');
		$fieldSet->add($pending);
		$form->add($fieldSet);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$emailAll = new \PHPFUI\Submit('Email All Riders');
		$emailAll->setConfirm('Are you sure you want to email all riders?');
		$buttonGroup->addButton($emailAll);
		$test = new \PHPFUI\Submit($this->testMessage);
		$test->addClass('warning');
		$buttonGroup->addButton($test);
		$form->add($buttonGroup);

		return (string)$form;
		}
	}
