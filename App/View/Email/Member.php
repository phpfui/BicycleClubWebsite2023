<?php

namespace App\View\Email;

class Member implements \Stringable
	{
	private ?\App\UI\Captcha $captcha = null;

	private string $name;

	private readonly int $signedInMemberId;

	public function __construct(private \App\View\Page $page, \App\Record\Member $member, string $title = '')
		{
		$this->captcha = new \App\UI\Captcha($this->page, ! $this->page->isSignedIn());
		$this->signedInMemberId = \App\Model\Session::signedInMemberId();

		if ($member->loaded())
			{
			if ($this->signedInMemberId)
				{
				$this->name = \App\Tools\TextHelper::unhtmlentities($member->fullName());
				}
			else
				{
				$this->name = $title ?: 'Club Member';
				}

			if (\App\Model\Session::checkCSRF() && isset($_POST['submit']))
				{
				if ($this->captcha->valid())
					{
					$settings = new \App\Table\Setting();
					$link = $settings->value('homePage');
					$email = new \App\Tools\EMail();
					$subject = \Soundasleep\Html2Text::convert($_POST['subject'] ?? 'No Subject', ['drop_links' => 'href', 'ignore_errors' => true]);
					$email->setSubject($subject);
					$email->addToMember($member->toArray());

					if ($this->signedInMemberId)
						{
						$member = new \App\Record\Member($this->signedInMemberId);
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
					$email->setReplyTo($emailAddress, $name);
					$email->setBody(\App\Tools\TextHelper::cleanUserHtml($_POST['message']) . "\n\nThis email was sent from {$link} by {$name}\n{$emailAddress}\n{$phone}");
					$email->send();
					$this->page->redirect('', 'sent');
					}
				else
					{
					\App\Model\Session::setFlash('alert', 'You appear to be a robot');
					$this->page->redirect();
					}
				}
			}
		}

	public function __toString() : string
		{
		$container = new \PHPFUI\Container();

		if (isset($_GET['sent']))
			{
			$container->add(new \PHPFUI\SubHeader($this->name));
			$container->add(new \App\UI\Alert("Thanks for contacting {$this->name}, they should get back to you shortly."));
			}
		elseif ($this->name)
			{
			$form = new \PHPFUI\Form($this->page);
			$form->add(new \PHPFUI\Header($this->name, 4));

			if (! $this->signedInMemberId)
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
			$title = \urldecode($_GET['title'] ?? 'No Subject');
			$title = \Soundasleep\Html2Text::convert($title, ['drop_links' => 'href', 'ignore_errors' => true]);
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
			$form->add($this->captcha);
			$form->add(new \PHPFUI\Submit('Email ' . $this->name));
			$container->add($form);
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('Member not found'));
			}

		return (string)$container;
		}
	}
