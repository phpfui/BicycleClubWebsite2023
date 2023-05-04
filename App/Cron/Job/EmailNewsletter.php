<?php

namespace App\Cron\Job;

class EmailNewsletter extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Email the newsletter to all subscribing members.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$today = \App\Tools\Date::toString($this->controller->runningAtJD());
		$newsletterTable = new \App\Table\Newsletter();
		$newsletterTable->setWhere(new \PHPFUI\ORM\Condition('dateAdded', \App\Tools\Date::increment($today, -1)));

		foreach ($newsletterTable->getRecordCursor() as $newsletter)
			{
			if ($newsletter->date > \App\Tools\Date::increment($today, -15) && $newsletter->date < \App\Tools\Date::increment($today, 15))
				{
				$email = new \App\Tools\EMail();
				$settings = new \App\Table\Setting();
				$abbrev = $settings->value('clubAbbrev');
				$name = $settings->value('newsletterName');
				$date = \App\Tools\Date::formatString('F Y', $newsletter->date);
				$email->setBody($settings->value('newsletter'));
				$email->setHtml();
				$email->setSubject("The {$date} {$abbrev} {$name} is now available");
				$memberPicker = new \App\Model\MemberPicker('Newsletter Editor');
				$email->setFromMember($memberPicker->getMember());
				$fileModel = new \App\Model\NewsletterFiles($newsletter->toArray());
				$emailAttached = clone $email;
				$emailAttached->addAttachment($fileModel->get($newsletter->newsletterId . '.pdf'), $fileModel->getPrettyFileName());
				$members = \App\Table\Member::getNewsletterMembers($today);

				foreach ($members as $member)
					{
					if (2 == $member['emailNewsletter']) // attach the newsletter
						{
						$emailAttached->addToMember($member);
						}
					elseif (1 == $member['emailNewsletter']) // email notification only
						{
						$email->addToMember($member);
						}
					}
				$email->bulkSend();
				$emailAttached->bulkSend();
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(6, 0);
		}
	}
