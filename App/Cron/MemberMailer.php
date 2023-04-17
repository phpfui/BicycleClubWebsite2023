<?php

namespace App\Cron;

abstract class MemberMailer extends \App\Cron\BaseJob
	{
	/** @param array<string, mixed> $fromMember */
	public function bulkMailMembers(\PHPFUI\ORM\DataObjectCursor $memberships, string $subject, string $message, array $fromMember) : void
		{
		$email = new \App\Tools\EMail();
		$email->setSubject(\App\Tools\TextHelper::unhtmlentities($subject));
		$email->setFromMember($fromMember);
		$email->addBCCMember($fromMember);
		$email->setHtml();

		foreach ($memberships as $member)
			{
			if (\filter_var($member['email'], FILTER_VALIDATE_EMAIL))
				{
				$email->setBody(\App\Tools\TextHelper::processText($message, $member->toArray()));
				$email->setToMember($member->toArray());
				$email->bulkSend();
				}
			}
		}
	}
