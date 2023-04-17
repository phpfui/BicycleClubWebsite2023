<?php

namespace App\DB;

class Migration_cascading_deletes extends \PHPFUI\ORM\Migration
	{
	private array $tables = [
		'bikeShopArea' => [
			'bikeShop' => 'CASCADE',
		],
		'blog' => [
			'blogItem' => 'CASCADE',
		],
		'category' => [
			'memberCategory' => 'CASCADE',
			'pace' => 'CASCADE',
		],
		'cueSheet' => [
			'cueSheetVersion' => 'CASCADE',
		],
		'discountCode' => [
			'cartItem' => 'NULL',
			'invoice' => 'NULL',
		],
		'event' => [
			'reservation' => 'CASCADE',
			'reservationPerson' => 'CASCADE',
		],
		'forum' => [
			'forumAttachment' => 'CASCADE',
			'forumMember' => 'CASCADE',
			'forumMessage' => 'CASCADE',
		],
		'forumMessage' => [
			'forumAttachment' => 'CASCADE',
		],
		'gaEvent' => [
			'gaAnswer' => 'CASCADE',
			'gaIncentive' => 'CASCADE',
			'gaPriceDate' => 'CASCADE',
			'gaRide' => 'CASCADE',
			'gaRider' => 'CASCADE',
		],
		'gaIncentive' => [
			'gaRider' => 'NULL',
		],
		'incentive' => [
			'rideIncentive' => 'CASCADE',
		],
		'invoice' => [
			'invoiceItem' => 'CASCADE',
			'payment' => 'CASCADE',
			'paypalRefund' => 'CASCADE',
			'reservation' => 'CASCADE',
		],
		'job' => [
			'jobShift' => 'CASCADE',
			'volunteerJobShift' => 'CASCADE',
		],
		'jobEvent' => [
			'job' => 'CASCADE',
			'volunteerPoint' => 'CASCADE',
			'volunteerPoll' => 'CASCADE',
		],
		'jobShift' => [
			'volunteerJobShift' => 'CASCADE',
		],
		'mailItem' => [
			'mailAttachment' => 'CASCADE',
			'mailPiece' => 'CASCADE',
		],
		'member' => [
			'additionalEmail' => 'CASCADE',
			'assistantLeader' => 'CASCADE',
			'auditTrail' => 'CASCADE',
			'boardMember' => 'CASCADE',
			'cartItem' => 'CASCADE',
			'cueSheet' => 'NULL',
			'cueSheetVersion' => 'NULL',
			'forumMember' => 'CASCADE',
			'forumMessage' => 'CASCADE',
			'gaRider' => 'CASCADE',
			'invoice' => 'CASCADE',
			'memberCategory' => 'CASCADE',
			'memberOfMonth' => 'NULL',
			'photo' => 'NULL',
			'photoComment' => 'CASCADE',
			'photoTag' => 'CASCADE',
			'pointHistory' => 'CASCADE',
			'poll' => 'NULL',
			'pollResponse' => 'CASCADE',
			'reservation' => 'CASCADE',
			'ride' => 'NULL',
			'rideComment' => 'CASCADE',
			'rideSignup' => 'CASCADE',
			'signinSheet' => 'NULL',
			'slideShow' => 'CASCADE',
			'userPermission' => 'CASCADE',
			'volunteerJobShift' => 'CASCADE',
			'volunteerPoint' => 'CASCADE',
			'volunteerPollResponse' => 'CASCADE',
		],
		'membership' => [
			'member' => 'CASCADE',
			'payment' => 'CASCADE',
			'pollResponse' => 'CASCADE',
		],
		'payment' => [
			'reservation' => 'CASCADE',
		],
		'photo' => [
			'photoComment' => 'CASCADE',
			'photoTag' => 'CASCADE',
		],
		'photoFolder' => [
			'photo' => 'CASCADE',
		],
		'poll' => [
			'pollAnswer' => 'CASCADE',
			'pollResponse' => 'CASCADE',
		],
		'reservation' => [
			'reservationPerson' => 'CASCADE',
		],
		'ride' => [
			'assistantLeader' => 'CASCADE',
			'rideComment' => 'CASCADE',
			'rideIncentive' => 'CASCADE',
			'rideSignup' => 'CASCADE',
			'signinSheetRide' => 'CASCADE',
		],
		'rwgps' => [
			'cueSheet' => 'NULL',
			'ride' => 'NULL',
		],
		'signinSheet' => [
			'signinSheetRide' => 'CASCADE',
		],
		'slideShow' => [
			'slide' => 'CASCADE',
		],
		'storeItem' => [
			'storeItemDetail' => 'CASCADE',
		],
		'story' => [
			'blogItem' => 'CASCADE',
		],
		'volunteerPoll' => [
			'volunteerPollAnswer' => 'CASCADE',
			'volunteerPollResponse' => 'CASCADE',
		],
	];

	public function description() : string
		{
		return 'Adding foreign key constraints';
		}

	public function down() : bool
		{
		foreach ($this->tables as $baseTable => $tables)
			{
			foreach ($tables as $table)
				{
//				$this->dropForeignKey($table, "fk_{$baseTable}_{$table}");
				}
			}

		return true;
		}

	public function up() : bool
		{
		foreach ($this->tables as $baseTable => $tables)
			{
			foreach ($tables as $table => $type)
				{
	//			$this->dropForeignKey($table, "fk_{$baseTable}_{$table}");
				$baseId = \lcfirst($baseTable) . 'Id';
				$sql = "delete from {$table} where {$baseId} not in (select {$baseId} from {$baseTable})";
				$this->runSQL($sql);
				$sql = "ALTER TABLE {$table} ADD CONSTRAINT fk_{$baseTable}_{$table} FOREIGN KEY ({$baseId}) REFERENCES {$baseTable} ({$baseId}) ON DELETE {$type} ON UPDATE CASCADE;";
				$this->runSQL($sql);
				}
			}

		return true;
		}
	}
