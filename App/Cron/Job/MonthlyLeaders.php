<?php

namespace App\Cron\Job;

class MonthlyLeaders extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Email the Monthly Leader report the the Rides Chair';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$settings = new \App\Table\Setting();
		$rideTable = new \App\Table\Ride();
		$memberPicker = new \App\Model\MemberPicker('Rides Chair');
		$ridesChair = $memberPicker->getMember();
		$day = $this->controller->runningAtDay();
		$month = $this->controller->runningAtMonth();
		$year = $this->controller->runningAtYear();
		$endDate = \App\Tools\Date::makeString($year, $month, $day);

		if (0 == --$month)
			{
			$month = 12;
			$year -= 1;
			}
		$startDate = \App\Tools\Date::makeString($year, $month, $day);
		$rides = $rideTable->getLeadersRides([0], $startDate, $endDate);
		$leaderRides = [];

		foreach ($rides as $ride)
			{
			if (! isset($leaderRides[$ride['memberId']]))
				{
				$leaderRides[$ride['memberId']] = [];
				}
			$leaderRides[$ride['memberId']][] = $ride['rideDate'];
			}
		$file = new \App\Tools\TempFile('monthlyLeaders');
		$csv = new \App\Tools\CSVWriter($file, ',', false);
		$range = $startDate . ' through ' . $endDate;
		$csv->outputRow(['Full Name', 'First Name', 'Last Name', 'Total Rides', 'Dates Led ' . $range]);

		foreach ($leaderRides as $memberId => $datesLed)
			{
			$member = new \App\Record\Member($memberId);
			$member->firstName = \html_entity_decode($member->firstName);
			$member->lastName = \html_entity_decode($member->lastName);
			$row = [$member->firstName . ' ' . $member->lastName, $member->firstName, $member->lastName,
				\count($datesLed), ];

			foreach ($datesLed as $date)
				{
				$row[] = $date;
				}
			$csv->outputRow($row);
			}
		unset($csv);
		$title = $settings->value('clubAbbrev') . ' Monthly Ride Leaders ' . $range;
		$email = new \App\Tools\EMail();
		$email->setSubject($title);
		$email->addAttachment($file, $fileName = \str_replace(' ', '_', $title) . '.csv');
		$email->setBody('See attached file ' . $fileName);
		$email->setFromMember($ridesChair);
		$email->addToMember($ridesChair);
		$memberTable = new \App\Table\Member();
		$memberTable->setMembersWithPermission('Ride Coordinator');

		foreach ($memberTable->getArrayCursor() as $member)
			{
			$email->addCCMember($member);
			}
		$email->send();
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(4, 15) && $this->controller->runDayOfMonth(15);
		}
	}
