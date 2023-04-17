<?php

namespace App\Cron\Job;

class NewMembers extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Email list of new members to the newsletter editor.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$year = $this->controller->runningAtYear();
		$month = $this->controller->runningAtMonth();

		if (0 == --$month)
			{
			--$year;
			$month = 12;
			}
		$start = \gregoriantojd($month, $this->controller->runningAtDay(), $year);
		$members = \App\Table\Member::getNewMembers(\App\Tools\Date::toString($start), $this->controller->runningAtDate());
		$message = '';

		foreach ($members as $member)
			{
			if (! $member['showNothing'])
				{
				$message .= "{$member['firstName']} {$member['lastName']}";

				if (! $member['showNoTown'])
					{
					$message .= ", {$member['town']}, {$member['state']}";
					}
				$message .= "\n";
				}
			}

		if (empty($message))
			{
			$message = 'There were no new members for this month.';
			}
		$settingTable = new \App\Table\Setting();
		$title = 'New ' . $settingTable->value('clubAbbrev') . ' members for ' . \date('M Y', $this->controller->getStartTime());
		$email = new \App\Tools\EMail();
		$email->setSubject($title);
		$email->setBody($message);
		$memberPicker = new \App\Model\MemberPicker('Membership Chair');
		$email->setFromMember($memberPicker->getMember());
		$email->addToMember($memberPicker->getMember('Newsletter Editor'));
		$email->send();
		}

	public function willRun() : bool
		{
		return $this->controller->runDayOfMonth(20) && $this->controller->runAt(2, 15);
		}
	}
