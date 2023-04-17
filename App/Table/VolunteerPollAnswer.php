<?php

namespace App\Table;

class VolunteerPollAnswer extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\VolunteerPollAnswer::class;

	public function getPollAnswers(int $volunteerPollId) : iterable
		{
		$sql = 'select * from volunteerPollAnswer where volunteerPollId=? order by answer';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$volunteerPollId]);
		}
}
