<?php

namespace App\View\Email;

class FellowRiders implements \Stringable
	{
	private string $emailText = 'Email All Fellow Riders';

	private string $testText = 'Sent Test Email';

	public function __construct(private readonly \App\View\Page $page)
		{
		if (\App\Model\Session::checkCSRF() && isset($_POST['submit']))
			{
			$message = 'Unknown command';
			$status = 'error';
			\App\Model\Session::setFlash('post', $_POST);

			$riderSelect = new \App\Table\RideSignup();
			$riderSelect->addSelect('rideId');
			$riderWhere = new \PHPFUI\ORM\Condition('memberId', $_POST['memberId']);
			$riderWhere->and('attended', 1, new \PHPFUI\ORM\Operator\NotEqual());
			$riderSelect->setWhere($riderWhere);

			$rideSignupTable = new \App\Table\RideSignup();
			$rideSignupTable->addJoin('member');
			$rideSignupTable->addJoin('ride');
			$rideSignupTable->addSelect(new \PHPFUI\ORM\Literal('count(*)'), 'numberRides');
			$rideSignupTable->addSelect('member.firstName');
			$rideSignupTable->addSelect('member.lastName');
			$rideSignupTable->addSelect('member.email');
			$rideSignupTable->addGroupBy('member.email');
			$rideSignupTable->addOrderBy('numberRides', 'desc');
			$numberRides = (int)($_POST['numberRides'] ?? 0);

			if ($numberRides)
				{
				$rideSignupTable->setHaving(new \PHPFUI\ORM\Condition('numberRides', $numberRides, new \PHPFUI\ORM\Operator\GreaterThanEqual()));
				}
			$where = new \PHPFUI\ORM\Condition('member.email', '', new \PHPFUI\ORM\Operator\GreaterThan());
			$where->and('rideSignup.rideId', $riderSelect, new \PHPFUI\ORM\Operator\In());

			if (! (int)($_POST['includeFellow'] ?? 0))
				{
				$where->and('member.memberId', $_POST['memberId'], new \PHPFUI\ORM\Operator\NotEqual());
				}

			if ($_POST['fromDate'])
				{
				$where->and('ride.rideDate', $_POST['fromDate'], new \PHPFUI\ORM\Operator\GreaterThanEqual());
				}

			if ($_POST['toDate'])
				{
				$where->and('ride.rideDate', $_POST['toDate'], new \PHPFUI\ORM\Operator\LessThanEqual());
				}
			$rideSignupTable->setWhere($where);
			$riders = $rideSignupTable->getArrayCursor();

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
			$body = \App\Tools\TextHelper::cleanUserHtml($_POST['message']) . "<p>This email was sent from {$link} by {$name}\n{$emailAddress}\n{$phone}";

			if ($_POST['submit'] == $this->emailText)
				{
				$email->setBody($body);

				foreach ($riders as $rider)
					{
					$email->addBCCMember($rider);
					}
				$email->bulkSend();
				$message = 'Your email was sent to ' . \count($riders) . ' fellow riders';
				$status = 'success';
				}
			elseif ($_POST['submit'] == $this->testText)
				{
				$table = new \PHPFUI\Table();
				$table->setHeaders(['numberRides' => 'Rides With', 'firstName' => 'First Name', 'lastName' => 'Last Name', 'email' => 'Email']);

				foreach ($riders as $rider)
					{
					$table->addRow($rider);
					}
				$header = new \PHPFUI\SubHeader('Email would be sent to the following:');
				$email->setBody($body . $header . $table);
				$email->setToMember($member);
				$email->send();
				$message = 'Check your inbox for a test email.<br>Your email would be sent to ' . \count($riders) . ' follow riders';
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

	//	$form->add(new \PHPFUI\Debug($post));
		$fieldSet = new \PHPFUI\FieldSet('Select the fellow rider (can be a former member)');
		$member = new \App\Record\Member($post['memberId'] ?? 0);
		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\NonMemberPickerNoSave('Fellow Rider'), 'memberId', $member->toArray());
		$fieldSet->add($memberPicker->getEditControl()->setRequired());
		$form->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Optional Selection Criteria');
		$from = new \PHPFUI\Input\Date($this->page, 'fromDate', 'Rides From', $post['fromDate'] ?? '');
		$from->setToolTip('Only fellow riders on a ride from this date and beyond will get the email');
		$to = new \PHPFUI\Input\Date($this->page, 'toDate', 'Rides Until', $post['toDate'] ?? '');
		$to->setToolTip('Only fellow riders on a ride on or before this date will get the email');
		$fieldSet->add(new \PHPFUI\MultiColumn($from, $to));
		$numberRides = new \PHPFUI\Input\Number('numberRides', 'Number of rides', $post['numberRides'] ?? '');
		$numberRides->setToolTip('Number of rides fellow riders where on. Zero for all riders, or number of rides together or higher.');
		$includeMember = new \PHPFUI\Input\CheckBoxBoolean('includeFellow', 'Include Fellow Rider', (bool)($post['includeFellow'] ?? false));
		$includeMember->setToolTip('Check if you want to email the fellow rider as well.');
		$fieldSet->add(new \PHPFUI\MultiColumn($numberRides, $includeMember));
		$form->add($fieldSet);
		$fieldSet = new \PHPFUI\FieldSet('Email');
		$subject = new \PHPFUI\Input\Text('subject', 'Subject', $post['subject'] ?? '');
		$subject->setRequired();
		$subject->addAttribute('placeholder', 'Email Subject');
		$fieldSet->add($subject);
		$message = new \PHPFUI\Input\TextArea('message', 'Message', $post['message'] ?? '');
		$message->addAttribute('placeholder', 'Message to fellow riders?');
		$message->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
		$message->setRequired();
		$fieldSet->add($message);
		$form->add($fieldSet);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$sendButton = new \PHPFUI\Submit($this->emailText);
		$sendButton->setConfirm('Email all fellow riders?');
		$sendButton->addClass('warning');
		$buttonGroup->addButton($sendButton);
		$testButton = new \PHPFUI\Submit($this->testText);
		$testButton->addClass('success');
		$buttonGroup->addButton($testButton);
		$form->add($buttonGroup);

		return (string)$form;
		}
	}
