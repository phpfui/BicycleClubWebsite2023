<?php

namespace App\Table;

class VolunteerPoll extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\VolunteerPoll::class;

	public function getAllPolls() : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setJoin('jobEvent');
		$this->setOrderBy('ordering');

		return $this->getDataObjectCursor();
		}

	public function getPolls(\App\Record\JobEvent $jobEvent) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setJoin('jobEvent');
		$this->setOrderBy('ordering');
		$this->setWhere(new \PHPFUI\ORM\Condition('volunteerPoll.jobEventId', $jobEvent->jobEventId));

		return $this->getDataObjectCursor();
		}
}
