<?php

namespace App\Cron\Job;

class EmailJournal extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Email out the weekly journal to all subscribing members.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$journalItemTable = new \App\Table\JournalItem();
		$journalItemTable->addOrderBy('timeSent');

		if (\count($journalItemTable))
			{
			$today = \App\Tools\Date::toString($this->controller->runningAtJD() - 3, 'F j, Y', );
			$settings = new \App\Table\Setting();
			$clubAbbrev = $settings->value('clubAbbrev');
			$title = "{$clubAbbrev} Announcements for the week of {$today}";
			$html = "<h1>{$title}</h1><p>";
			$counter = 1;

			foreach ($journalItemTable->getRecordCursor() as $journalItem)
				{
				$html .= "{$counter}. <a href='#{$counter}'>{$journalItem->title} From: {$journalItem->member->fullName()}</a><p>";
				$counter += 1;
				}
			$html .= '<hr>';
			$counter = 1;

			foreach ($journalItemTable->getRecordCursor() as $journalItem)
				{
				$member = $journalItem->member;
				$html .= "<h2><a name='{$counter}'>{$counter}. {$journalItem->title}</a></h2>From: {$member->fullName()} <a href='mailto:{$member->email}'>{$member->email}</a><br>";
				$html .= \date('D M j, Y g:i a', \strtotime((string)$journalItem->timeSent)) . '<p>';
				$html .= \App\Tools\TextHelper::addLinks(\str_replace("\n", '<br>', (string)$journalItem->body));
				$counter += 1;
				$html .= '<hr>';
				}
			// delete all items in the journal queue
			$journalItemTable->setWhere(new \PHPFUI\ORM\Condition('journalItemId', 0, new \PHPFUI\ORM\Operator\NotEqual()));
			$journalItemTable->delete();
			$clubName = $settings->value('clubName');
			$host = $this->controller->getSchemeHost();
			$html .= "This digest of {$clubAbbrev} emails was sent to you as a member of {$clubName}<br>You can edit your email preferences <a href='{$host}/Membership/myNotifications'>here</a>";
			$email = new \App\Tools\EMail();
			$email->setBody($html);
			$email->setHtml();
			$memberPicker = new \App\Model\MemberPicker('Membership Chair');
			$email->setFromMember($memberPicker->getMember());
			$email->setSubject($title);

			foreach (\App\Table\Member::getJournalMembers(\App\Tools\Date::todayString()) as $member)
				{
				$email->addToMember($member);
				}
			$email->bulkSend();
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runWeekday(4) && $this->controller->runAt(1, 0);
		}
	}
