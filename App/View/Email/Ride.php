<?php

namespace App\View\Email;

class Ride implements \Stringable
	{
	/**
	 * @var array<int>
	 */
	protected $leadersOnRide = [];

	protected string $title;

	private readonly \App\View\Rides $view;

	public function __construct(private \App\View\Page $page, private readonly \App\Record\Ride $ride)
		{
		$this->page = $page;
		$this->view = new \App\View\Rides($this->page);
		$this->title = 'The ' . $this->view->getPace($this->ride->paceId) . ' ride on ' . \App\Tools\Date::formatString('l, F j, Y', $this->ride->rideDate) . ' at ' . \App\Tools\TimeHelper::toSmallTime($this->ride->startTime);

		$settingTable = new \App\Table\Setting();
		$leaders = \App\Table\RideSignup::getSignedUpByPermmission($ride, $settingTable->getStandardPermissionGroup('Ride Leader'));

		foreach ($leaders as $leader)
			{
			$this->leadersOnRide[] = $leader['memberId'];
			}

		if (\App\Model\Session::checkCSRF() && isset($_POST['submit']))
			{
			$settings = new \App\Table\Setting();
			$link = $settings->value('homePage');
			$email = new \App\Tools\EMail();
			$email->setSubject($_POST['subject']);
			$member = \App\Model\Session::getSignedInMember();
			$name = \App\Tools\TextHelper::unhtmlentities($member['firstName'] . ' ' . $member['lastName']);
			$emailAddress = $member['email'];
			$phone = $member['phone'];
			$email->setHtml();
			$email->setFromMember($member);
			$body = \App\Tools\TextHelper::cleanUserHtml($_POST['message']);
			$email->setBody("{$body}<p>This email was sent from {$link} by {$name}<br>{$emailAddress}<br>{$phone}");

			if (isset($_FILES['file']))
				{
				if (! $_FILES['file']['error'])
					{
					$file = $_FILES['file']['tmp_name'];

					if (\is_uploaded_file($file))
						{
						$email->addAttachment(\file_get_contents($file), $_FILES['file']['name']);
						}
					}
				}
			$rideSignupTable = new \App\Table\RideSignup();
			$riders = $rideSignupTable->getAllSignedUpRiders($ride);
			$leadersOnly = empty($_POST['leadersOnly']) ? '' : '&leadersOnly=1';

			foreach ($riders as $rider)
				{
				if (! $leadersOnly || \in_array($rider->memberId, $this->leadersOnRide))
					{
					$email->addBCCMember($rider->toArray());
					}
				}
			$email->bulkSend();
			$this->page->redirect('', 'sent' . $leadersOnly);
			}
		}

	public function __toString() : string
		{
		if (isset($_GET['sent']))
			{
			$container = new \PHPFUI\Container();
			$container->add(new \App\UI\Alert('Your email was sent to the following riders'));
			$rideSignupTable = new \App\Table\RideSignup();
			$riders = $rideSignupTable->getAllSignedUpRiders($this->ride);

			foreach ($riders as $rider)
				{
				if (empty($_GET['leadersOnly']) || \in_array($rider->memberId, $this->leadersOnRide))
					{
					$row = new \PHPFUI\GridX();
					$row->add($rider->firstName . ' ' . $rider->lastName);
					$container->add($row);
					}
				}
			$output = (string)$container;
			}
		else
			{
			$form = new \PHPFUI\Form($this->page);
			$form->add($this->view->getRideInfo($this->ride));
			$fieldSet = new \PHPFUI\FieldSet('Message');
			$subject = new \PHPFUI\Input\Text('subject', 'Subject', $this->title);
			$subject->setRequired();
			$fieldSet->add($subject);

			if (\in_array(\App\Model\Session::signedInMemberId(), $this->leadersOnRide))
				{
				$leadersOnly = new \PHPFUI\Input\CheckBoxBoolean('leadersOnly', 'Send to Ride Leaders only');
				$leadersOnly->setToolTip('This will limit the email to just other ride leaders who have signed up for this ride.');
				$fieldSet->add($leadersOnly);
				}
			$message = new \PHPFUI\Input\TextArea('message', 'Message');
			$message->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
			$message->setToolTip('So what is on your mind?');
			$message->addAttribute('placeholder', 'So what is on your mind?');
			$message->setRequired();
			$fieldSet->add($message);
			$fieldSet->add(new \PHPFUI\Input\File($this->page, 'file', 'Optional file to attach'));
			$form->add($fieldSet);
			$row = new \PHPFUI\GridX();
			$row->add(new \PHPFUI\Submit('Email Riders'));
			$form->add($row);
			$output = $form;
			}

		return (string)$output;
		}
	}
