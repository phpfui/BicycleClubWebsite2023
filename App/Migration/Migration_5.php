<?php

namespace App\Migration;

class Migration_5 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Adding RWGPS comments, alternates and ratings';
		}

	public function down() : bool
		{
		$this->dropTable('RWGPSAlternate');
		$this->dropTable('RWGPSComment');
		$this->dropTable('RWGPSRating');

		if ('WIN' != \strtoupper(\substr(PHP_OS, 0, 3)))
			{
			$this->renameTable('RWGPS', 'rwgps');
			}

		return true;
		}

	public function up() : bool
		{
		$permissions = new \App\Model\Permission();
		$permissions->addPermissionToGroup('Normal Member', 'RideWithGPS Detail', 'RWGPS');

		if ('WIN' != \strtoupper(\substr(PHP_OS, 0, 3)))
			{
			$this->renameTable('rwgps', 'RWGPS');
			}

		$this->dropTable('RWGPSAlternate');
		$this->dropTable('RWGPSComment');
		$this->dropTable('RWGPSRating');

		return $this->runSQL('
			create table RWGPSAlternate (
				RWGPSAlternateId int not null,
				RWGPSId int not null,
				memberId int not null,
				primary key (RWGPSId, RWGPSAlternateId));

			create table RWGPSComment (
				RWGPSId int not null,
				comments varchar(255) not null,
				memberId int not null,
				lastEdited timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				primary key (RWGPSId, memberId));

			create table RWGPSRating (
				RWGPSId int not null,
				memberId int not null,
				rating int not null,
				primary key (RWGPSId,memberId));');
		}
	}
