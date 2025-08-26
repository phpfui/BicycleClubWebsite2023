<?php

namespace App\Table;

class VolunteerPoll extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\VolunteerPoll::class;

	public function getAllPolls() : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from volunteerPoll p left join jobEvent j on p.jobEventId=j.jobEventId order by ordering';

		return \PHPFUI\ORM::getDataObjectCursor($sql);
		}

	public function getPolls(\App\Record\JobEvent $jobEvent) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from volunteerPoll p left join jobEvent j on p.jobEventId=j.jobEventId where p.jobEventId=? order by ordering';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$jobEvent->jobEventId]);
		}
}
