<?php

namespace App\Table;

class VolunteerPoll extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\VolunteerPoll::class;

	public function deleteAll(int $volunteerPollId) : bool
		{
		if (! $volunteerPollId)
			{
			throw new \Exception('Required id not passed into ' . __METHOD__);
			}
		$sql = 'delete from volunteerPollResponse where volunteerPollId=?';
		\PHPFUI\ORM::execute($sql, [$volunteerPollId]);
		$sql = 'delete from volunteerPollAnswer where volunteerPollId=?';
		\PHPFUI\ORM::execute($sql, [$volunteerPollId]);
		$sql = 'delete from volunteerPoll where volunteerPollId=?';
		\PHPFUI\ORM::execute($sql, [$volunteerPollId]);

		return true;
		}

	public function getAllPolls() : iterable
		{
		$sql = 'select * from volunteerPoll p left join jobEvent j on p.jobEventId=j.jobEventId order by question';

		return \PHPFUI\ORM::getDataObjectCursor($sql);
		}

	public function getPolls(int $jobEventId) : iterable
		{
		$sql = 'select * from volunteerPoll p left join jobEvent j on p.jobEventId=j.jobEventId where p.jobEventId=? order by p.question';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$jobEventId]);
		}
}
