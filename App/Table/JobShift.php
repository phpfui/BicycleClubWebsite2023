<?php

namespace App\Table;

class JobShift extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\JobShift::class;

	public function getAvailableJobShifts(int $jobId) : \PHPFUI\ORM\DataObjectCursor
		{
		$volunteerJobShiftTable = new \App\Table\VolunteerJobShift();
		$volunteerJobShiftTable->setSelect(new \PHPFUI\ORM\Literal('count(*)'));
		$vjsCondition = new \PHPFUI\ORM\Condition('volunteerJobShift.jobId', $jobId);
		$vjsCondition->and('volunteerJobShift.jobShiftId', new \PHPFUI\ORM\Literal('jobShift.jobShiftId'));
		$volunteerJobShiftTable->setWhere($vjsCondition);
		$volunteerJobShiftTable->setGroupBy('volunteerJobShift.jobShiftId');

		$condition = new \PHPFUI\ORM\Condition('jobId', $jobId);
		$input = [];
		$countSql = \str_replace(' = ? ', ' = ' . $jobId . ' ', $volunteerJobShiftTable->getSelectSQL($input));
		$coalesce = 'COALESCE((' . $countSql . '),0)';
		$condition->and(new \PHPFUI\ORM\Literal($coalesce), new \PHPFUI\ORM\Literal('jobShift.needed'), new \PHPFUI\ORM\Operator\LessThan());
		$this->setWhere($condition);
		$this->setOrderBy('jobShiftId');
		$this->setOrderBy('startTime');

		return $this->getDataObjectCursor();
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
