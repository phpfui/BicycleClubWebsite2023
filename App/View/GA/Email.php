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
			$appendMessage = "<p>This email was sent from <a href='{$link}'>{$link}</a> by {$name} {$emailAddress}</p>";
			$settingTable = new \App\Table\Setting();
			$server = $settingTable->value('homePage');
			$appendMessage .= "<p><a href='{$server}/GA/~unsubscribe~'>You may Unsubscribe Here</a></p>";

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
			$clean = \App\Tools\TextHelper::cleanUserHtml($_POST['message']);
			$gaRiderTable = new \App\Table\GaRider();
			$riders = $gaRiderTable->getEmailsForEvents($_POST['gaEventId'], (int)($_POST['pending'] ?? 0));

			if ($_POST['submit'] == $this->testMessage)
				{
				$riderData = new \App\Model\Email\GaRider($_POST['gaEventId']);
				$email->setBody(\App\Tools\TextHelper::processText($clean, $riderData->toArray()) . $appendMessage);
				$email->setToMember($sender);
				$email->send();
				\App\Model\Session::setFlash('success', 'Check your inbox for a test email.  It would have been sent to ' . \count($riders) . ' riders');
				}
			else
				{
				foreach ($riders as $rider)
					{
					$riderData = new \App\Model\Email\GaRider($_POST['gaEventId'], $rider);
					$email->setBody(\App\Tools\TextHelper::processText($clean, $riderData->toArray()) . $appendMessage);
					$email->setToMember($rider->toArray());
					$email->bulkSend();
					}
				\App\Model\Session::setFlash('success', 'You emailed ' . \count($riders) . ' riders.');
				}
			$this->page->redirect();
			}
		}

	public function __toString() : string
		{
		$form = new \PHPFUI\Form($this->page);

		$tabs = new \PHPFUI\Tabs();

		$picker = new \App\View\GA\EventPicker($this->page, \App\Enum\GeneralAdmission\EventPicker::MULTIPLE, 'Email Riders In These Events');
		$picker->setSelected($this->parameters['gaEventId'] ?? []);
		$tabs->addTab('Select Events', $picker, true);

		$fieldSet = new \PHPFUI\Container();
		$subject = new \PHPFUI\Input\Text('subject', 'Subject', $this->parameters['subject'] ?? '');
		$subject->setRequired();
		$subject->addAttribute('placeholder', 'Email Subject');
		$fieldSet->add($subject);
		$pending = new \PHPFUI\Input\CheckBoxBoolean('pending', 'Just Send to Unpaid Riders');
		$pending->setToolTip('If checked, the email will only go to riders who have signed up, but not paid. Otherwise it only goes to paid riders.');
		$fieldSet->add($pending);
		$message = new \PHPFUI\Input\TextArea('message', 'Message', $this->parameters['message'] ?? '');
		$message->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$message->addAttribute('placeholder', 'Message to all riders');
		$message->setRequired();
		$fieldSet->add($message);
		$tabs->addTab('Email Content', $fieldSet);

		$tabs->addTab('Attachment', new \PHPFUI\Input\File($this->page, 'file', 'Optional file to attach'));

		$riderFields = new \App\Model\Email\GaRider($this->parameters['gaEventId'] ?? []);
		$info = new \PHPFUI\Callout('info');
		$info->add('To see the event specific fields, you must first send a test email with the correct event selected on the first tab');
		$tabs->addTab('Substitutions', new \App\UI\SubstitutionFields($riderFields->toArray()) . $info);
		$form->add($tabs);
		$form->add('<br>');

		$form->add(new \PHPFUI\FormError('Please fill out the required fields in the <b>Email Content</b> tab'));

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
