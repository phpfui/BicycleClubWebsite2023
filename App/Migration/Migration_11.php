<?php

namespace App\Migration;

class Migration_11 extends \PHPFUI\ORM\Migration
	{
	/** @var array<string> */
	private array $tables = ['member', 'pointHistory'];

	public function description() : string
		{
		return 'leaderPoint to volunteerPoints';
		}

	public function down() : bool
		{
		foreach ($this->tables as $table)
			{
			$this->alterColumn($table, 'volunteerPoints', 'int NOT NULL DEFAULT "0"');
			$this->renameColumn($table, 'volunteerPoints', 'leaderPoints');
			}

		return true;
		}

	public function up() : bool
		{
		foreach ($this->tables as $table)
			{
			$this->alterColumn($table, 'leaderPoints', 'int NOT NULL DEFAULT "0"');
			$this->alterColumn($table, 'leaderPoints', 'volunteerPoints');
			}

		return true;
		}
	}
