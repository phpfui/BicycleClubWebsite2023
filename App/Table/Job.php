<?php

namespace App\Table;

class Job extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Job::class;

	public function deleteAll(int $jobId) : bool
		{
		$sql = 'delete from volunteerJobShift where jobId = ?';
		\PHPFUI\ORM::execute($sql, [$jobId]);

		$sql = 'delete from jobShift where jobId = ?';
		\PHPFUI\ORM::execute($sql, [$jobId]);

		$sql = 'delete from job where jobId = ?';
		\PHPFUI\ORM::execute($sql, [$jobId]);

		return true;
		}

	public function getJobs(int $jobId) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select j.*,sum(js.needed) needed,(SELECT COUNT(*) FROM volunteerJobShift vjs WHERE vjs.jobId=j.jobId) taken
						from job j
						left join jobShift js on j.jobId=js.jobId
						where j.jobEventId=?
						group by j.jobId
						ORDER BY j.title';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$jobId]);
		}
	}
