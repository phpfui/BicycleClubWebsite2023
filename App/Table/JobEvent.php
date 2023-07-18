<?php

namespace App\Table;

class JobEvent extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\JobEvent::class;

	public function copy(int $fromId, string $title, string $toDate) : void
		{
		$fromJobEvent = new \App\Record\JobEvent($fromId);
		$dateDiff = \App\Tools\Date::diff($fromJobEvent->date, $toDate);

		$newJobEvent = new \App\Record\JobEvent();
		$newJobEvent->setFrom($fromJobEvent->toArray());
		$newJobEvent->cutoffDate = \App\Tools\Date::increment($fromJobEvent->cutoffDate, $dateDiff);
		$newJobEvent->name = $title;
		$newJobEvent->date = $toDate;
		$newJobEvent->organizer = \App\Model\Session::signedInMemberId();
		$newJobEvent->jobEventId = 0;
		$newJobEvent->insert();

		$jobShiftTable = new \App\Table\JobShift();
		$jobTable = new \App\Table\Job();
		$jobs = $jobTable->getJobs($fromId);

		foreach ($jobs as $jobObject)
			{
			$newJob = new \App\Record\Job($jobObject->toArray());
			$newJob->jobEvent = $newJobEvent;
			$newJob->date = \App\Tools\Date::increment($jobObject->date, $dateDiff);
			$newJob->jobId = 0;
			$jobShifts = $jobShiftTable->getJobShifts($jobObject->jobId);

			foreach ($jobShifts as $jobShiftObject)
				{
				$newJobShift = new \App\Record\JobShift();
				$newJobShift->setFrom($jobShiftObject->toArray());
				$newJobShift->job = $newJob;
				$newJobShift->jobShiftId = 0;
				$newJobShift->insert();
				}
			}
		$volunteerPollTable = new \App\Table\VolunteerPoll();
		$volunteerPollAnswerTable = new \App\Table\VolunteerPollAnswer();
		$polls = $volunteerPollTable->getPolls($fromId);

		foreach ($polls as $pollObject)
			{
			$newVolunteerPull = new \App\Record\VolunteerPoll($pollObject->toArray());
			$newVolunteerPull->jobEventId = $newJobEvent->jobEventId;
			$newVolunteerPull->volunteerPollId = 0;
			$answers = $volunteerPollAnswerTable->getPollAnswers($pollObject['volunteerPollId']);

			foreach ($answers as $answerObject)
				{
				$newVolunteerPollAnswer = new \App\Record\VolunteerPollAnswer();
				$newVolunteerPollAnswer->setFrom($answerObject->toArray());
				$newVolunteerPollAnswer->volunteerPoll = $newVolunteerPull;
				$newVolunteerPollAnswer->insert();
				}
			}
		}

	public function deleteAll(int $id) : bool
		{
		if (! $id)
			{
			throw new \Exception('Required id not passed into ' . __METHOD__);
			}
		$sql = 'delete from volunteerPollResponse where volunteerPollId in (select volunteerPollId from volunteerPoll where jobEventId=?)';
		\PHPFUI\ORM::execute($sql, [$id]);
		$sql = 'delete from volunteerPollAnswer where volunteerPollId in (select volunteerPollId from volunteerPoll where jobEventId=?)';
		\PHPFUI\ORM::execute($sql, [$id]);
		$sql = 'delete from volunteerPoll where jobEventId=?';
		\PHPFUI\ORM::execute($sql, [$id]);
		$sql = 'delete from volunteerJobShift where jobId in (select jobId from job where jobEventId=?)';
		\PHPFUI\ORM::execute($sql, [$id]);
		$sql = 'delete from jobShift where jobId in (select jobId from job where jobEventId=?)';
		\PHPFUI\ORM::execute($sql, [$id]);
		$sql = 'delete from job where jobEventId=?';
		\PHPFUI\ORM::execute($sql, [$id]);
		$sql = 'delete from jobEvent where jobEventId=?';
		\PHPFUI\ORM::execute($sql, [$id]);

		return true;
		}

	public function getJobEvents(string $date = '1000-01-01') : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from jobEvent where cutoffDate>=? order by cutoffDate';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$date]);
		}

	public function getJobEventsBetween(string $startDate, string $endDate) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from jobEvent where cutoffDate>=? and cutoffDate<=? order by cutoffDate';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$startDate, $endDate]);
		}

	/**
	 * @return (null|scalar)[]
	 *
	 * @psalm-return array<string, null|scalar>
	 */
	public function getLatest() : array
		{
		$sql = 'select * from jobEvent order by date desc limit 1';

		return \PHPFUI\ORM::getRow($sql);
		}

	/**
	 * @return (null|scalar)[]
	 *
	 * @psalm-return array<string, null|scalar>
	 */
	public function getOldest() : array
		{
		$sql = 'select * from jobEvent order by date asc limit 1';

		return \PHPFUI\ORM::getRow($sql);
		}
	}
