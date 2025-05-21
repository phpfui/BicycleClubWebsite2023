<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \App\Enum\Forum\SubscriptionType $emailType
 */
class ForumMember extends \App\Record\Definition\ForumMember
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'emailType' => [\PHPFUI\ORM\Enum::class, \App\Enum\Forum\SubscriptionType::class],
	];

	public function delete() : bool
		{
		// delete the permission from the member as well
		$forumJoin = new \PHPFUI\ORM\Condition('forum.name', new \PHPFUI\ORM\Literal('permission.name'));
		$rows = new \App\Table\Permission()->addSelect('permissionId')->addJoin('forum', $forumJoin)->setWhere(new \PHPFUI\ORM\Condition('forumId', $this->forumId))->getRows();
		$userPermission = new \App\Record\UserPermission();
		$userPermission->memberId = $this->memberId;
		$userPermission->permissionGroup = (int)$rows[0]['permissionId'];
		$userPermission->delete();

		return parent::delete();
		}

	// add user permission when added a member to a forum
	public function insert() : int | bool
		{
		return $this->insertOrIgnore();
		}

	public function insertOrIgnore() : int | bool
		{
		$forumJoin = new \PHPFUI\ORM\Condition('forum.name', new \PHPFUI\ORM\Literal('permission.name'));
		$rows = new \App\Table\Permission()->addSelect('permissionId')->addJoin('forum', $forumJoin)->setWhere(new \PHPFUI\ORM\Condition('forumId', $this->forumId))->getRows();
		$userPermission = new \App\Record\UserPermission();
		$userPermission->memberId = $this->memberId;
		$userPermission->permissionGroup = (int)$rows[0]['permissionId'];
		$userPermission->revoked = 0;
		$userPermission->insertOrIgnore();

		return parent::insertOrIgnore();
		}

	public function insertOrUpdate() : int | bool
		{
		return $this->insertOrIgnore();
		}

	public function save() : int | bool
		{
		return $this->insertOrIgnore();
		}
	}
