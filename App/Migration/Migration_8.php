<?php

namespace App\Migration;

class Migration_8 extends \PHPFUI\ORM\Migration
	{
	private array $headers = [
		'Edit Accept Cue Sheet Email',
		'Edit Reject Cue Sheet Email',
		'Edit Ride Settings',
		'Edit Categories',
		'Edit New Leader Email',
		'Edit New Rider Email',
		'Edit Request Ride Status Email',
		'Edit Wait List Email',
		'Edit My Info',
		'Edit Accept Calendar Email',
		'Edit Reject Calendar Email',
		'Edit Thank You Calendar Email',
		'Edit PayPal Terms and Conditions',
		'Edit Reject Sign In Sheet Email',
		'Edit Accept Sign In Sheet Email',
		'Edit Board Members',
		'Edit Public Pages',
	];

	public function description() : string
		{
		return 'Rename Edit permissions';
		}

	public function down() : bool
		{
		$permissionTable = new \App\Table\Permission();

		foreach ($this->headers as $header)
			{
			$permissionTable->rename(\str_replace('Edit ', '', $header), $header);
			}

		return true;
		}

	public function up() : bool
		{
		$permissionTable = new \App\Table\Permission();

		foreach ($this->headers as $header)
			{
			$permissionTable->rename($header, \str_replace('Edit ', '', $header));
			}

		return true;
		}
	}
