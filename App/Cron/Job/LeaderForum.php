<?php

namespace App\Cron\Job;

class LeaderForum extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Update leader forum with current leaders';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$addMembers = [];

		$settingTable = new \App\Table\Setting();
		$leaderForum = (int)$settingTable->value('LeaderForum');

		if (! $leaderForum)
			{
			return;
			}

		$currentMembers = \PHPFUI\ORM::getValueArray('select memberId from forumMember where forumId=?', [$leaderForum]);
		$currentMembers = \array_flip($currentMembers);

		$permissionTable = new \App\Table\Permission();
		$currentLeaders = $permissionTable->getMembersWithPermissionGroup('Ride Leader');

		foreach ($currentLeaders ?? [] as $permissionUser)
			{
			if (isset($currentMembers[$permissionUser->memberId]))
				{
				unset($currentMembers[$permissionUser->memberId]);
				}
			else
				{
				$addMembers[] = $permissionUser->memberId;
				}
			}

		$forumMember = new \App\Record\ForumMember();
		$forumMember->emailType = \App\Table\ForumMember::INDIVIDUAL;
		$forumMember->forumId = $leaderForum;
		// add new members
		foreach ($addMembers as $memberId)
			{
			$forumMember->memberId = $memberId;
			$forumMember->insert();
			}

		if (empty($currentMembers))
			{
			return;
			}

		$forumMemberTable = new \App\Table\ForumMember();
		$condition = new \PHPFUI\ORM\Condition('forumId', $leaderForum);
		$condition->and('memberId', \array_flip($currentMembers), new \PHPFUI\ORM\Operator\In());
		$forumMemberTable->setWhere($condition);
		$forumMemberTable->delete();
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(4, 35);
		}
	}
