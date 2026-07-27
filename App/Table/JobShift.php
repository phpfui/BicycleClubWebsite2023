<?php

namespace App\Table;

class JobShift extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\JobShift::class;

	public function getAvailableJobShifts(int $jobId) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'SELECT js.* FROM jobShift js WHERE js.jobId=? and COALESCE((SELECT count(*) FROM volunteerJobShift v where v.jobId=? and v.jobShiftId=js.jobShiftId group by v.jobShiftId),0) < js.needed group by js.jobShiftId order by js.startTime';
		return \PHPFUI\ORM::getDataObjectCursor($sql, [$jobId, $jobId, ]);

//		$volunteerJobShiftTable = new \App\Table\VolunteerJobShift();
//		$vjsCondition = new \PHPFUI\ORM\Condition('volunteerJobShift.jobId', $jobId);
//		$vjsCondition->and('volunteerJobShift.jobShiftId', new \PHPFUI\ORM\Literal('jobShift.jobShiftId'));
//		$volunteerJobShiftTable->setWhere($vjsCondition);
//		$volunteerJobShiftTable->setGroupBy('volunteerJobShift.jobShiftId');
//
//		$condition = new \PHPFUI\ORM\Condition('jobId', $jobId);
//		$input = [];
//		$countSql = str_replace(' = ? ', ' = ' . $jobId . ' ',  $volunteerJobShiftTable->getCountSQL($input, ''));
//		$coalesce = 'COALESCE((' . $countSql . ' as shifts),0)';
//		$coalesce = \str_replace(['/'], $input, $coalesce);
//		$condition->and(new \PHPFUI\ORM\Literal($coalesce), new \PHPFUI\ORM\Literal('needed'), new \PHPFUI\ORM\Operator\LessThan());
//		$this->setWhere($condition);
//		$this->setOrderBy('jobShiftId');
//		$this->setOrderBy('startTime');
//
//		return $this->getDataObjectCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\JobShift>
	 */
	public function getJobShifts(int $jobId) : \PHPFUI\ORM\RecordCursor
		{
		$this->setOrderBy('startTime');
		$this->addOrderBy('needed');
		$this->setWhere(new \PHPFUI\ORM\Condition('jobId', $jobId));

		return $this->getRecordCursor();
		}
	}
