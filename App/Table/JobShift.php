<?php

namespace App\Table;

class JobShift extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\JobShift::class;

	public function getAvailableJobShifts(int $jobId) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'SELECT js.* FROM jobShift js WHERE js.jobId=? and COALESCE((SELECT count(*) FROM volunteerJobShift v where v.jobId=? and v.jobShiftId=js.jobShiftId group by v.jobShiftId),0) < js.needed group by js.jobShiftId order by js.startTime';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$jobId, $jobId, ]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\JobShift>
	 */
	public function getJobShifts(int $jobId) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from jobShift where jobId=? order by startTime,needed';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\JobShift(), $sql, [$jobId]);
		}
	}
