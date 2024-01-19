<?php

namespace App\Migration;

class Migration_33 extends \PHPFUI\ORM\Migration
	{
	/** @var array<string,string> */
	private array $permissions = [
		'Approve Pending Leaders' => 'Approve Pending Ride Leaders',
		'Email All Leaders' => 'Email All Ride Leaders',
		'Leader Configuration' => 'Ride Leader Configuration',
		'Leader Report' => 'Ride Leader Report',
		'Leader Stats' => 'Ride Leader Stats',
		'Leaders By Name' => 'Ride Leaders By Name',
		'Leaders Statistics' => 'Ride Leader Statistics',
		'New Leader Email' => 'New Ride Leader Email',
		'Pending Leaders' => 'Pending Ride Leaders',
		'Show Leaders' => 'Show Ride Leaders',
	];

	public function description() : string
		{
		return 'Change Leaders menu to Ride Leaders';
		}

	public function down() : bool
		{
		$this->runSQL('update permission set name="Leaders" where name="Ride Leaders"');

		return $this->changePermissions(\array_flip($this->permissions));
		}

	public function up() : bool
		{
		$this->alterColumn('permission', 'name', 'varchar(255)');
		$this->alterColumn('permission', 'menu', 'varchar(255)');
		$this->runSQL('update permission set name="Ride Leaders" where name="Leaders"');

		return $this->changePermissions($this->permissions);
		}

	/** @param array<string,string> $permissions */
	private function changePermissions(array $permissions) : bool
		{
		foreach ($permissions as $old => $new)
			{
			$this->runSQL('update permission set name=? where name=?', [$new, $old]);
			}

		return true;
		}
	}
