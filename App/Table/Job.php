<?php

namespace App\Table;

class Job extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Job::class;

	public function getJobs(\App\Record\JobEvent $jobEvent) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setSelect('job.*');

		$volunteerJobShiftTable = new \App\Table\VolunteerJobShift();
		$volunteerJobShiftTable->setWhere(new \PHPFUI\ORM\Condition('volunteerJobShift.jobId', new \PHPFUI\ORM\Literal('job.jobId')));
		$input = [];
		$this->addSelect(new \PHPFUI\ORM\Literal('(' . $volunteerJobShiftTable->getCountSQL($input, '') . ')'), 'taken');
		$this->addSelect(new \PHPFUI\ORM\Literal('sum(jobShift.needed)'), 'needed');

		$this->setWhere(new \PHPFUI\ORM\Condition('job.jobEventId', $jobEvent->jobEventId));
		$this->setJoin('jobShift');
		$this->setGroupBy('job.jobId');
		$this->setOrderBy('job.title');

		return $this->getDataObjectCursor();
		}
	}
