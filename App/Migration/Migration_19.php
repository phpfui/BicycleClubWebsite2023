<?php

namespace App\Migration;

class Migration_19 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Remove bad fields from jobEvent and forumMessage tables';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->dropColumn('forumMessage', 'description');
		$this->dropColumn('jobEvent', 'email');
		$this->dropColumn('jobEvent', 'description');

		return true;
		}
	}
