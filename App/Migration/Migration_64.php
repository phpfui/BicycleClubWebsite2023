<?php

namespace App\Migration;

class Migration_64 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add user permissions for Forums';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$forumMemberTable = new \App\Table\ForumMember();
		$forumMemberTable->setWhere(new \PHPFUI\ORM\Condition('memberId', 0))->delete()->setWhere();
		$forumMemberTable->addJoin('forum')->setOrderBy('forum.name');

		$userPermissionTable = new \App\Table\UserPermission();
		$records = [];

		$permission = new \App\Record\Permission();
		$userPermission = new \App\Record\UserPermission();
		$userPermission->revoked = 0;

		foreach ($forumMemberTable->getDataObjectCursor() as $forum)
			{
			if ($forum->name != $permission->name)
				{
				if ($records)
					{
					$userPermissionTable->insert($records, 'ignore');
					}
				$records = [];
				$permission = new \App\Record\Permission(['name' => $forum->name]);
				}
			$userPermission->memberId = $forum->memberId;
			$userPermission->permissionGroup = $permission->permissionId;
			$records[] = clone $userPermission;
			}

		$userPermissionTable->insert($records, 'ignore');

		return true;
		}
	}
