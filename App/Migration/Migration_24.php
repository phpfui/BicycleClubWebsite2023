<?php

namespace App\Migration;

class Migration_24 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Drop old General Admission tables';
		}

	public function down() : bool
		{
		$this->dropTable('gaIncentive');
		$this->dropTable('gaAnswer');
		$this->dropTable('gaRide');
		$this->runSQL('CREATE TABLE `gaIncentive` (
				`gaIncentiveId` int NOT NULL AUTO_INCREMENT,
				`gaEventId` int NOT NULL,
				`description` char(250) COLLATE utf8mb4_general_ci DEFAULT NULL,
				PRIMARY KEY (`gaIncentiveId`),
				KEY `gaEventIdIndex` (`gaEventId`)
			);
			CREATE TABLE `gaAnswer` (
				`gaAnswerId` int NOT NULL AUTO_INCREMENT,
				`gaEventId` int NOT NULL,
				`answer` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
				`ordering` int NOT NULL DEFAULT 0,
				PRIMARY KEY (`gaAnswerId`),
				KEY `gaEventIdIndex` (`gaEventId`)
			);
			CREATE TABLE `gaRide` (
				`gaRideId` int NOT NULL AUTO_INCREMENT,
				`gaEventId` int NOT NULL,
				`distance` int DEFAULT NULL,
				`description` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
				`extraPrice` decimal(6,2) DEFAULT NULL,
				`startTime` time DEFAULT NULL,
				`endTime` time DEFAULT NULL,
				PRIMARY KEY (`gaRideId`),
				KEY `gaEventIdIndex` (`gaEventId`)
			);');

		$this->dropColumn('gaRider', 'customerId');
		$this->addColumn('gaRider', 'gaIncentiveId', 'int');
		$this->addColumn('gaRider', 'gaRideId', 'int');
		$this->addColumn('gaRider', 'referral', 'int');
		$this->dropColumn('gaEvent', 'allowShopping');
		$this->addColumn('gaEvent', 'live', 'int');
		$this->addColumn('gaEvent', 'incentiveName', 'varchar(50)');
		$this->addColumn('gaEvent', 'incentiveCount', 'int');
		$this->addColumn('gaEvent', 'question', 'varchar(100)');
		$this->addColumn('gaEvent', 'parent', 'int');

		return true;
		}

	public function up() : bool
		{
		$this->dropTable('gaAnswer');
		$this->dropTable('gaRide');
		$this->dropTable('gaIncentive');

		$this->addColumn('gaRider', 'customerId', 'int');
		$this->dropColumn('gaRider', 'gaIncentiveId');
		$this->dropColumn('gaRider', 'gaRideId');
		$this->dropColumn('gaRider', 'referral');
		$this->addColumn('gaEvent', 'allowShopping', 'int');
		$this->dropColumn('gaEvent', 'live');
		$this->dropColumn('gaEvent', 'incentiveName');
		$this->dropColumn('gaEvent', 'incentiveCount');
		$this->dropColumn('gaEvent', 'question');
		$this->dropColumn('gaEvent', 'parent');

		return true;
		}
	}
