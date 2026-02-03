<?php

namespace App\View\Public;

class ContactUs implements \Stringable
	{
	private readonly \App\UI\Captcha $captcha;

	/**
	 * @param \PHPFUI\ORM\RecordCursor<\App\Record\BoardMember> $boardMembers
	 */
	public function __construct(private \App\View\Page $page, private readonly \PHPFUI\ORM\RecordCursor $boardMembers)
		{
		$this->captcha = new \App\UI\Captcha($this->page);
		}

	public function __toString() : string
		{
		$output = new \PHPFUI\Container();

		$post = \App\Model\Session::getFlash('post');
		$settings = new \App\Table\Setting();
		$form = new \PHPFUI\Form($this->page);

		if (\App\Model\Session::checkCSRF() && isset($_POST['submit']))
			{
			\App\Model\Session::setFlash('post', $_POST);

			if ($this->captcha->isValid())
				{
				$link = $settings->value('homePage');
				$email = new \App\Tools\EMail();
				$email->setSubject($_POST['subject']);

				if (! empty($_POST['contact']))
					{
					$memberId = 0;

					foreach ($this->boardMembers as $member)
						{
						if ($member->memberId == $_POST['contact'])
							{
							$memberId = $member->memberId;
							}
						}

					if ($memberId)
						{
						$member = new \App\Record\Member($memberId);

						if ($member->loaded())
							{
							$email->setToMember($member);
							$name = \strip_tags((string)$_POST['name']);
							$emailAddress = \filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
							$phone = \strip_tags((string)$_POST['phone']);
							$email->setFrom($emailAddress, $name);
							$email->setReplyTo($emailAddress, $name);
							$email->setBody(\strip_tags((string)$_POST['message']) . "\n\nThis email was sent from {$link} by {$name}\n{$emailAddress}\n{$phone}");
							$email->send();
							}
						}
					}
				\App\Model\Session::setFlash('success', 'Thanks for contacting us, someone should get back to you shortly.');
				}
			else
				{
				\App\Model\Session::setFlash('alert', 'You appear to be a robot! Please confirm you are not.');
				}
			$this->page->redirect();
			}
		else
			{
			if (\count($this->boardMembers))
				{
				$form->add(new \PHPFUI\Input\Hidden('memberId', (string)\App\Model\Session::signedInmemberId()));
				$fieldSet = new \PHPFUI\FieldSet('Your Information');
				$name = new \PHPFUI\Input\Text('name', 'Your Name', $post['name'] ?? '');
				$name->setToolTip('We need to know who you are so we can address you by name');
				$name->addAttribute('placeholder', 'Your Name');
				$name->setRequired();
				$fieldSet->add($name);
				$email = new \PHPFUI\Input\Email('email', 'Your Email Address', $post['email'] ?? '');
				$email->setToolTip('We work best with email, so give us yours and we will get back to you. Promise!');
				$email->addAttribute('placeholder', 'your@email.com');
				$email->setRequired();
				$fieldSet->add($email);
				$phone = new \App\UI\TelUSA($this->page, 'phone', 'Your Phone Number', $post['phone'] ?? '');
				$phone->setToolTip('Occasionally email does not work, and a phone call may be better, but it is not required.');
				$phone->addAttribute('placeholder', '914-555-1212');
				$fieldSet->add($phone);
				$form->add($fieldSet);
				$fieldSet = new \PHPFUI\FieldSet('Email you want to send');
				$select = new \PHPFUI\Input\Select('contact', 'To');
				$select->setToolTip('Pick who you think the most appropriate person is. That will get your question answered the fastest.');
				$selected = $_GET['id'] ?? $post['contact'] ?? '';
				$select->addOption('', '');
				$select->setRequired();

				foreach ($this->boardMembers as $member)
					{
					$select->addOption($member->title . ' - ' . $member->member->fullName(), (string)$member->memberId, $member->memberId == $selected);
					}
				$fieldSet->add($select);
				$subject = new \PHPFUI\Input\Text('subject', 'Subject', $post['subject'] ?? '');
				$subject->setRequired();
				$subject->setToolTip('Give us the basic jist of what you are asking here, so we can see it in our inbox.');
				$subject->addAttribute('placeholder', 'Email Subject');
				$fieldSet->add($subject);
				$message = new \PHPFUI\Input\TextArea('message', 'Message', $post['message'] ?? '');
				$message->setToolTip('So what is on your mind?');
				$message->addAttribute('placeholder', 'So what is on your mind?');
				$message->setRequired();
				$fieldSet->add($message);
				$form->add($fieldSet);

				$form->add($this->captcha);
				$form->add(new \PHPFUI\Submit('Send!'));
				}
			}

		$address = $settings->value('memberAddr');

		$text = '';

		if ($address)
			{
			$text = '<p>Our mailing address:<blockquote><strong>';
			$text .= $settings->value('clubName');
			$text .= '</strong><br>';
			$text .= $address;
			$text .= '<br>';
			$text .= $settings->value('memberTown');
			$text .= '<br></blockquote>';
			}
		$phone = $settings->value('phone');

		if ($phone)
			{
			$link = \PHPFUI\Link::phone($phone);
			$text .= "<p>You can also contact us at {$link}</p>";
			}

		if ($text)
			{
			$panel = new \PHPFUI\Panel($text);
			$panel->setRadius();
			$form->add($panel);
			}
		$output->add($form);

		return "{$output}";
		}
	}
