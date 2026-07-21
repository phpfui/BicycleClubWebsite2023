<?php

namespace App\Table;

class VolunteerJobShift extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\VolunteerJobShift::class;

	public function getJobsForEventDateMember(int $jobEventId, string $date, int $memberId) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setSelect('volunteerJobShift.*');
		$this->setJoin('job');
		$condition = new \PHPFUI\ORM\Condition('job.jobEventId', $jobEventId);
		$condition->and('volunteerJobShift.memberId', $memberId);
		$condition->and('job.date', $date);
		$this->setWhere($condition);

		return $this->getDataObjectCursor();
		}

	public function getJobsForMember(int $memberId, int $jobEventId = 0) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setDistinct();
		$this->setSelect('volunteerJobShift.jobId');
		$this->addJoin('job');
		$condition = new \PHPFUI\ORM\Condition('volunteerJobShift.memberId', $memberId);

		if (! $jobEventId)
			{
			$condition->and('job.date', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}
		else
			{
			$condition->and('job.jobEventId', $jobEventId);
			}
		$this->setWhere($condition);
		$this->setOrderBy('job.date');

		return $this->getDataObjectCursor();
		}

	public function getJobVolunteersSince(string $date) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setDistinct();
		$this->setSelect('jobEventId');
		$this->addSelect('date');
		$this->addSelect('volunteerJobShift.memberId');
		$this->addSelect('worked');
		$this->setJoin('job');
		$this->setWhere(new \PHPFUI\ORM\Condition('date', $date, new \PHPFUI\ORM\Operator\GreaterThanEqual()));
		$this->setOrderBy('jobEventId');
		$this->addOrderBy('date');
		$this->addOrderBy('volunteerJobShift.memberId');

		return $this->getDataObjectCursor();
		}

	public function getShiftsForMember(\App\Record\Job $job, \App\Record\Member $member) : \PHPFUI\ORM\DataObjectCursor
		{
		$condition = new \PHPFUI\ORM\Condition('volunteerJobShift.memberId', $member->memberId);
		$condition->and('volunteerJobShift.jobId', $job->jobId);
		$this->setWhere($condition);
		$this->setJoin('jobShift');
		$this->setOrderBy('startTime');

		return $this->getDataObjectCursor();
		}

	public function getUniqueVolunteers(\App\Record\JobEvent $jobEvent) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->addSelect('member.*');
		$this->addJoin('member');
		$this->addJoin('jobShift');
		$this->addJoin('job', new \PHPFUI\ORM\Condition('job.jobId', new \PHPFUI\ORM\Literal('jobShift.jobId')));
		$this->addGroupBy('member.memberId');
		$this->addOrderBy('member.lastName');
		$this->addOrderBy('member.firstName');
		$condition = new \PHPFUI\ORM\Condition('job.jobEventId', $jobEvent->jobEventId);
		$condition->and('member.memberId', null, new \PHPFUI\ORM\Operator\IsNotNull());
		$this->setWhere($condition);

		return $this->getDataObjectCursor();
		}

	public function getVolunteers(int $jobId) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setSelect('volunteerJobShift.*');
		$this->addSelect('member.*');
		$this->setJoin('member');
		$this->setWhere(new \PHPFUI\ORM\Condition('jobId', $jobId));
		$this->setOrderBy('shiftLeader', 'desc');
		$this->addOrderBy('lastName');
		$this->addOrderBy('firstName');

		return $this->getDataObjectCursor();
		}

	public function getVolunteersByShift(\App\Record\Job $job) : \PHPFUI\ORM\DataObjectCursor
		{
		$jobShiftTable = new \App\Table\JobShift();
		$jobShiftTable->setSelect('jobShift.*');
		$jobShiftTable->addSelect('volunteerJobShift.*');
		$jobShiftTable->addSelect('member.*');
		$vjsJoin = new \PHPFUI\ORM\Condition('volunteerJobShift.jobId', new \PHPFUI\ORM\Literal('jobShift.jobId'));
		$vjsJoin->and('volunteerJobShift.jobShiftId', new \PHPFUI\ORM\Literal('jobShift.jobShiftId'));
		$jobShiftTable->setJoin('volunteerJobShift', $vjsJoin);
		$jobShiftTable->addJoin('member', new \PHPFUI\ORM\Condition('volunteerJobShift.memberId', new \PHPFUI\ORM\Literal('member.memberId')));
		$jobShiftTable->setWhere(new \PHPFUI\ORM\Condition('jobShift.jobId', $job->jobId));
		$jobShiftTable->setOrderBy('volunteerJobShift.shiftLeader', 'desc');
		$jobShiftTable->addOrderBy('jobShift.startTime');
		$jobShiftTable->addOrderBy('jobShift.jobShiftId');
		$jobShiftTable->addOrderBy('member.lastName');
		$jobShiftTable->addOrderBy('member.firstName');

		return $jobShiftTable->getDataObjectCursor();
		}

	public function getVolunteerSchedule(\App\Record\JobEvent $jobEvent) : \PHPFUI\ORM\ArrayCursor
		{
		$this->setSelect('member.memberId');
		$this->addSelect('volunteerJobShift.*');
		$this->addSelect('member.lastName');
		$this->addSelect('member.firstName');
		$this->addSelect('member.email');
		$this->addSelect('member.cellPhone');
		$this->addSelect('jobShift.startTime');
		$this->addSelect('jobShift.endTime');
		$this->addSelect('job.title');
		$this->addSelect('job.date');

		$this->setJoin('member');
		$this->addJoin('jobShift');
		$this->addJoin('job', new \PHPFUI\ORM\Condition('job.jobId', new \PHPFUI\ORM\Literal('jobShift.jobId')));
		$this->setWhere(new \PHPFUI\ORM\Condition('job.jobEventId', $jobEvent->jobEventId));
		$this->setOrderBy('job.date')->addOrderBy('jobShift.startTime')->addOrderBy('member.lastName')->addOrderBy('member.firstName');

		return $this->getArrayCursor();
		}

	public function getVolunteersForDates(string $startDate, string $endDate) : \PHPFUI\ORM\ArrayCursor
		{
		$this->setSelect(new \PHPFUI\ORM\Literal("concat(member.firstName,' ',member.lastName)"), 'name');
		$this->addSelect('job.date');
		$this->addSelect('jobEvent.name', 'event');
		$this->addSelect('job.title', 'job');
		$this->setJoin('member');
		$this->addJoin('job');
		$this->addJoin('jobEvent', new \PHPFUI\ORM\Condition('jobEvent.jobEventId', new \PHPFUI\ORM\Literal('job.jobEventId')));
		$condition = new \PHPFUI\ORM\Condition('worked', 1);
		$condition->and('jobEvent.date', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('jobEvent.date', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$this->setWhere($condition);

		$this->setOrderBy('member.lastName')->addOrderBy('member.firstName')->addOrderBy('job.date')->addOrderBy('job.title');

		return $this->getArrayCursor();
		}

	public function isShiftLeader(\App\Record\Job $job, \App\Record\Member $member) : bool
		{
		$sql = 'select count(*) from volunteerJobShift where shiftLeader>0 and memberId=? and jobId=?';

		return \PHPFUI\ORM::getValue($sql, [$member->memberId, $job->jobId]) > 0;
		}
	}
