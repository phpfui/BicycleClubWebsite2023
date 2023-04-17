<?php

namespace App\Cron\Job;

class CalendarPending extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Email calendar coordinator there are pending calendar events to approve.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$settingTable = new \App\Table\Setting();
		$calendarName = $settingTable->value('calendarName');

		if (! $calendarName)
			{
			return;
			}

		$calendarTable = new \App\Table\Calendar();
		$pending = $calendarTable->getPending();

		if (! \count($pending))
			{
			return;
			}

		$host = $this->controller->getSchemeHost();
		$email = new \App\Tools\EMail();
		$email->setBody("There are pending calendar events that need to be reviewed\n\n" . "You can review them here: {$host}/Calendar/pending");
		$email->setSubject('Pending Calender events need review');
		$memberPicker = new \App\Model\MemberPicker('Web Master');
		$email->setFromMember($memberPicker->getMember());
		$email->addToMember($memberPicker->getMember('Calendar Coordinator'));
		$email->send();
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(9, 30);
		}
	}
