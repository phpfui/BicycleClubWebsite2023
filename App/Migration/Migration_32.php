<?php

namespace App\Migration;

class Migration_32 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Change category.coordinator to coordinatorId';
		}

	public function down() : bool
		{
		return $this->alterColumn('category', 'coordinatorId', 'int', 'coordinator');
		}

	public function up() : bool
		{
		return $this->alterColumn('category', 'coordinator', 'int', 'coordinatorId');
		}
	}
