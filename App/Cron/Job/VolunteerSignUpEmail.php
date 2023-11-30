<?php

namespace App\Cron\Job;

class VolunteerSignUpEmail extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Send information to new Volunteers.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$volunteerJobShiftTable = new \App\Table\VolunteerJobShift();
		$condition = new \PHPFUI\ORM\Condition('signedUpDate', \App\Tools\Date::todayString(-1), new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and(new \PHPFUI\ORM\Condition('signedUpDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\LessThan()));
		$volunteerJobShiftTable->setWhere($condition);
		$shifts = $volunteerJobShiftTable->getRecordCursor();
		$view = new \App\View\Volunteer\JobShifts(new \App\View\Page(new \App\Model\Controller(new \App\Model\Permission())));
		$server = $this->controller->getSchemeHost();

		foreach ($shifts as $shift)
			{
			$job = $shift->job;
			$jobEvent = $job->jobEvent;
			$fromMember = new \App\Record\Member($jobEvent->organizer);
			$member = $shift->member;
			$email = new \App\Tools\EMail();
			$email->setSubject("Thanks for volunteering for {$jobEvent->name} on {$job->date}");
			$email->setFromMember($fromMember->toArray());
			$email->addBccMember($fromMember->toArray());
			$email->setToMember($member->toArray());
			$body = '<p>You have signed up for the following shift:<p>';
			$body .= $view->showJobShiftsFor($job, $member, false);
			$body .= "<p><a href='{$server}/Volunteer/myJobs'>See all your shift details here</a>";
			$email->setBody($body);
			$email->setHtml();
			$email->send();
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(4, 15);
		}
	}
