<?php

namespace App\Table;

class VolunteerJobShift extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\VolunteerJobShift::class;

	public function getJobsForEventDateMember(int $jobEventId, string $date, int $memberId) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select vjs.* from volunteerJobShift vjs left join job j on vjs.jobId=j.jobId where j.jobEventId=? and vjs.memberId=? and j.date=?';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$jobEventId, $memberId, $date]);
		}

	public function getJobsForMember(int $memberId, int $event = 0) : \PHPFUI\ORM\DataObjectCursor
		{
		if (! $event)
			{
			$sql = 'select distinct vjs.jobId from volunteerJobShift vjs left join job j on vjs.jobId=j.jobId where vjs.memberId=? and j.date>=? order by j.date';

			return \PHPFUI\ORM::getDataObjectCursor($sql, [$memberId,
				\App\Tools\Date::todayString(), ]);
			}
		$sql = 'select distinct vjs.jobId from volunteerJobShift vjs left join job j on vjs.jobId=j.jobId where vjs.memberId=? and j.jobEventId=? order by j.date';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$memberId, $event, ]);
		}

	public function getJobVolunteersSince(string $date) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select distinct j.jobEventId,j.date,vjs.memberId,vjs.worked from volunteerJobShift vjs left join job j on vjs.jobId=j.jobId where j.date>=? order by j.jobEventId,j.date,vjs.memberId';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$date]);
		}

	public function getShiftsForMember(\App\Record\Job $job, \App\Record\Member $member) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from volunteerJobShift vjs left join jobShift js on js.jobShiftId=vjs.jobShiftId where vjs.memberId=? and vjs.jobId=? order by js.startTime';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$member->memberId, $job->jobId, ]);
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
		$sql = 'select * from volunteerJobShift vjs left join member m on vjs.memberId=m.memberId where vjs.jobId=? order by vjs.shiftLeader desc,m.lastName,m.firstName';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$jobId]);
		}

	public function getVolunteersByShift(\App\Record\Job $job) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from jobShift js left join volunteerJobShift vjs on vjs.jobId=js.jobId and vjs.jobShiftId=js.jobShiftId left join member m on vjs.memberId=m.memberId where js.jobId=? order by vjs.shiftLeader desc,js.startTime,js.jobShiftId,m.lastName,m.firstName';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$job->jobId]);
		}

	public function getVolunteerSchedule(\App\Record\JobEvent $jobEvent) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select m.memberId,vjs.*,m.lastName,m.firstName,m.email,m.cellPhone,js.startTime,js.endTime,j.title,j.date from volunteerJobShift vjs left join member m on vjs.memberId=m.memberId left join jobShift js on vjs.jobShiftId=js.jobShiftId left join job j on j.jobId=js.jobId where j.jobEventId=? order by j.date,js.startTime,m.lastName,m.firstName';

		return \PHPFUI\ORM::getArrayCursor($sql, [$jobEvent->jobEventId]);
		}

	public function getVolunteersForDates(string $startDate, string $endDate) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = "select concat(m.firstName,' ',m.lastName) as name,j.date,je.name as event,j.title as job from volunteerJobShift vjs " .
			'left join member m on m.memberId=vjs.memberId left join job j on j.jobId=vjs.jobId ' .
			'left join jobEvent je on je.jobEventId=j.jobEventId ' .
			'where vjs.worked=1 and je.date>=? and je.date<=? order by m.lastName,m.firstName,j.date,j.title';

		return \PHPFUI\ORM::getArrayCursor($sql, [$startDate, $endDate]);
		}

	public function isShiftLeader(\App\Record\Job $job, \App\Record\Member $member) : bool
		{
		$sql = 'select count(*) from volunteerJobShift where shiftLeader>0 and memberId=? and jobId=?';

		return (int)\PHPFUI\ORM::getValue($sql, [$member->memberId, $job->jobId]) > 0;
		}
	}
