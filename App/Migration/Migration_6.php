<?php

namespace App\Migration;

class Migration_6 extends \PHPFUI\ORM\Migration
	{
	/** @var array<string, array<mixed>> */
	private array $tableChanges = [
		'assistantLeader' => [['rideId', 'memberId'], 'memberId'],
		'blogItem' => [['blogId', 'storyId']],
		'invoiceItem' => [['invoiceId', 'storeItemId', 'storeItemDetailId'], 'invoiceId'],
		'memberCategory' => [['memberId', 'categoryId']],
		'migration' => [['migrationId']],
		'permissionGroup' => [['groupId', 'permissionId']],
		'pollAnswer' => [['pollId', 'pollAnswerId']],
		'pollResponse' => [['pollId', 'membershipId', 'memberId']],
		'rideSignup' => [['rideId', 'memberId'], 'memberId'],
		'signinSheetRide' => [['signinSheetId', 'rideId'], 'signinSheetId'],
		'storeItemDetail' => [['storeItemId', 'storeItemDetailId']],
		'storeItemOption' => [['storeItemId', 'storeOptionId']],
		'userPermission' => [['memberId', 'permissionGroup']],
		'volunteerPollResponse' => [['volunteerPollId', 'memberId']],
	];

	public function description() : string
		{
		return 'Fix table definitions';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('banner', 'startDate', 'DATE NULL DEFAULT NULL');
		$this->alterColumn('banner', 'endDate', 'DATE NULL DEFAULT NULL');
		$this->alterColumn('member', 'passwordResetExpires', 'TIMESTAMP NULL DEFAULT NULL');
		$this->alterColumn('oauthUser', 'lastLogin', 'TIMESTAMP NULL DEFAULT NULL');

		foreach ($this->tableChanges as $table => $changes)
			{
			$keys = \array_shift($changes);
			$this->deleteDuplicateRows($table, $keys);
			$this->dropAllIndexes($table);

			foreach ($keys as $key)
				{
				$this->alterColumn($table, $key, 'int not null');
				}
			$this->addPrimaryKey($table, $keys);
			$this->executeAlters();
			$additionalIndex = \array_shift($changes);

			if ($additionalIndex)
				{
				$this->addIndex($table, $additionalIndex);
				}
			}

		return true;
		}
	}
