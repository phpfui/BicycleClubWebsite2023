<?php

namespace App\Migration;

class Migration_71 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add ordering for Volunteer Polls';
		}

	public function down() : bool
		{
		$this->dropColumn('volunteerPoll', 'ordering');
		$this->dropColumn('volunteerPoll', 'required');

		return $this->dropColumn('volunteerPollAnswer', 'ordering');
		}

	public function up() : bool
		{
		$this->runSQL('delete from volunteerPoll where question is null or jobEventId=0 or jobEventId is null;');
		$this->runSQL('delete from volunteerPollAnswer where volunteerPollId not in (select volunteerPollId from volunteerPoll);');
		$this->addColumn('volunteerPoll', 'ordering', 'int not null default 0');
		$this->addColumn('volunteerPoll', 'required', 'int not null default 0');

		return $this->addColumn('volunteerPollAnswer', 'ordering', 'int not null default 0');
		}
	}
