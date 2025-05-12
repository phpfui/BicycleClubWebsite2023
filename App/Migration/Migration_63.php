<?php

namespace App\Migration;

class Migration_63 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Incomplete registration emails for General Admission';
		}

	public function down() : bool
		{
		$this->dropColumn('gaEvent', 'incompleteMessage');
		$this->dropColumn('gaEvent', 'incompleteDaysAfter');
		$this->dropColumn('gaEvent', 'deleteIncomplete');

		return true;
		}

	public function up() : bool
		{
		$this->addColumn('gaEvent', 'incompleteMessage', 'mediumtext');
		$this->addColumn('gaEvent', 'incompleteDaysAfter', "varchar(255) default '' not null");
		$this->addColumn('gaEvent', 'deleteIncomplete', 'int default 0 not null');

		$permissionTable = new \App\Table\Permission();
		$permissionTable->setWhere(new \PHPFUI\ORM\Condition('name', 'landing Page%', new \PHPFUI\ORM\Operator\Like()));
		$ids = [];

		foreach ($permissionTable->getRecordCursor() as $permission)
			{
			$ids[] = $permission->permissionId;
			}

		$permissionTable->delete();

		new \App\Table\UserPermission()->setWhere(new \PHPFUI\ORM\Condition('permissionGroup', $ids, new \PHPFUI\ORM\Operator\In()))->delete();
		new \App\Table\PermissionGroup()->setWhere(new \PHPFUI\ORM\Condition('permissionId', $ids, new \PHPFUI\ORM\Operator\In()))->delete();

		return true;
		}
	}
