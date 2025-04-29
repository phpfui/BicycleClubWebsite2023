<?php

namespace App\Migration;

class Migration_62 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Multiple RWGPS Routes per Ride';
		}

	public function down() : bool
		{
		$this->addColumn('ride', 'RWGPSId', 'int DEFAULT NULL');
		$this->executeAlters();

		foreach (\PHPFUI\ORM::getDataObjectCursor('select * from rideRWGPS') as $ride)
			{
			\PHPFUI\ORM::execute('update ride set RWGPSId=' . $ride->RWGPSId . ' where rideId=' . $ride->rideId);
			}

		$this->dropTable('rideRWGPS');

		return true;
		}

	public function up() : bool
		{
		$this->dropTable('rideRWGPS');
		$this->runSQL('create table rideRWGPS (
									rideId int not null,
									RWGPSId int not null,
									PRIMARY KEY (`rideId`,`RWGPSId`),
									KEY `RWGPSId` (`RWGPSId`));');

		foreach (\PHPFUI\ORM::getDataObjectCursor('select rideId,RWGPSId from ride where RWGPSId is not null') as $ride)
			{
			\PHPFUI\ORM::execute('INSERT INTO rideRWGPS (`rideId`,`RWGPSId`) VALUES (' . $ride->rideId . ',' . $ride->RWGPSId . ');');
			}

		$this->dropColumn('ride', 'RWGPSId');

		return true;
		}
	}
