<?php

namespace App\Table;

class Job extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Job::class;

	public function getJobs(\App\Record\JobEvent $jobEvent) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select j.*,sum(js.needed) needed,(SELECT COUNT(*) FROM volunteerJobShift vjs WHERE vjs.jobId=j.jobId) taken
						from job j
						left join jobShift js on j.jobId=js.jobId
						where j.jobEventId=?
						group by j.jobId
						ORDER BY j.title';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$jobEvent->jobEventId]);
		}
	}
