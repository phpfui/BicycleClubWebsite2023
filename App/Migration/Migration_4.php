<?php

namespace App\Migration;

class Migration_4 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Rename Permissions';
		}

	public function down() : bool
		{
		$permissionTable = new \App\Table\Permission();
		$permissionTable->rename('Permissions', 'Show All Permissions');
		$permissionTable->rename('Permission Groups', 'Show Permission Groups');

		return true;
		}

	public function up() : bool
		{
		$permissionTable = new \App\Table\Permission();
		$permissionTable->rename('Show All Permissions', 'Permissions');
		$permissionTable->rename('Show Permission Groups', 'Permission Groups');

		return true;
		}
	}
