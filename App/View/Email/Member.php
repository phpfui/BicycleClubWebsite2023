<?php

namespace App\View\Email;

class Member implements \Stringable
	{
	protected string $contactName;

	protected string $title;

	public function __construct(private \App\View\Page $page, \App\Record\Member $member)
		{
		$this->page = $page;

		if ($member->loaded())
			{
			$this->contactName = \App\Tools\TextHelper::unhtmlentities($member->fullName());
			$this->title = $this->contactName;

			if (\App\Model\Session::checkCSRF() && isset($_POST['submit']))
				{
				$settings = new \App\Table\Setting();
				$link = $settings->value('homePage');
				$email = new \App\Tools\EMail();
				$email->setSubject($_POST['subject'] ?? 'No Subject');
				$email->addToMember($member->toArray());

				if (! empty($_POST['memberId']))
					{
					$member = new \App\Record\Member((int)$_POST['memberId']);
					$name = $member->fullName();
					$emailAddress = $member->email;
					$phone = $member->phone;
					$email->setFromMember($member->toArray());
					}
				else
					{
					$name = $_POST['name'];
					$emailAddress = $_POST['email'];
					$phone = $_POST['phone'];
					$email->setFrom($emailAddress, $name);
					}
				$email->setBody(\App\Tools\TextHelper::cleanUserHtml($_POST['message']) . "\n\nThis email was sent from {$link} by {$name}\n{$emailAddress}\n{$phone}");
				$email->send();
				$this->page->redirect('', 'sent');
				}
			}
		}

	public function __toString() : string
		{
		$container = new \PHPFUI\Container();

		if (isset($_GET['sent']))
			{
			$container->add(new \PHPFUI\SubHeader($this->title));
			$container->add(new \App\UI\Alert("Thanks for contacting {$this->contactName}, they should get back to you shortly."));
			}
		elseif ($this->title)
			{
			$form = new \PHPFUI\Form($this->page);
			$signedInMember = \App\Model\Session::signedInMemberId();
			$form->add("<h2>{$this->title}</h2>");
			$form->add(new \PHPFUI\Input\Hidden('memberId', (string)$signedInMember));

			if (! $signedInMember)
				{
				$fieldSet = new \PHPFUI\FieldSet('Your Information');
				$name = new \PHPFUI\Input\Text('name', 'Name');
				$name->setToolTip('We need to know who you are so we can address you by name');
				$name->addAttribute('placeholder', 'Your Name');
				$name->setRequired();
				$fieldSet->add($name);
				$email = new \App\UI\UniqueEmail($this->page, new \App\Record\Member(), 'email', 'Email Address');
				$email->setToolTip('We work best with email, so give us yours and we will get back to you. Promise!');
				$email->addAttribute('placeholder', 'your@email.com');
				$email->setRequired();
				$fieldSet->add($email);
				$phone = new \App\UI\TelUSA($this->page, 'phone', 'Phone Number');
				$phone->setToolTip('Occasionally email does not work, and a phone call may be better, but it is not required.');
				$phone->addAttribute('placeholder', '914-555-1212');
				$fieldSet->add($phone);
				$form->add($fieldSet);
				}
			$fieldSet = new \PHPFUI\FieldSet('Email');
			$title = empty($_GET['title']) ? '' : \urldecode((string)$_GET['title']);
			$subject = new \PHPFUI\Input\Text('subject', 'Subject', $title);
			$subject->setRequired();
			$subject->setToolTip('Give us the basic jist of what you are asking here, so we can see it in our inbox.');
			$subject->addAttribute('placeholder', 'Email Subject');
			$fieldSet->add($subject);
			$message = new \PHPFUI\Input\TextArea('message', 'Message');
			$message->setToolTip('So what is on your mind?');
			$message->addAttribute('placeholder', 'So what is on your mind?');
			$message->setRequired();
			$fieldSet->add($message);
			$form->add($fieldSet);
			$form->add(new \PHPFUI\Submit('Email ' . $this->title));
			$container->add($form);
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('Member not found'));
			}

		return (string)$container;
		}
	}
