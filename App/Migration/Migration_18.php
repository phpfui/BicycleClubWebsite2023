<?php

namespace App\Migration;

class Migration_18 extends \PHPFUI\ORM\Migration
	{
	/**
	 * @var array<string,string>
	 */
	private array $tables = [
		'story' => 'body',
		'forumMessage' => 'htmlMessage',
		'jobEvent' => 'description',
		'ride' => 'description',
		'gaEvent' => 'signupMessage',
	];

	public function description() : string
		{
		return 'Convert HTML fields to MediumText for better pasted image support';
		}

	public function down() : bool
		{
		foreach ($this->tables as $table => $field)
			{
			$this->alterColumn($table, $field, 'text');
			}

		return true;
		}

	public function up() : bool
		{
		foreach ($this->tables as $table => $field)
			{
			$this->alterColumn($table, $field, 'mediumText');
			}

		return true;
		}
	}
