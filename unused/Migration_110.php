<?php

namespace App\Migration;

class Migration_110 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add strava table';
		}

	public function down() : bool
		{
		$this->dropColumn('ride', 'stravaId');

		return $this->dropTable('strava');
		}

	public function up() : bool
		{
		$this->dropTable('strava');

		$this->addColumn('ride', 'stravaId', 'varchar(25) default null');

		$this->runSQL('CREATE TABLE `strava` (
			`stravaId` varchar(25) NOT NULL,
			`description` varchar(255) DEFAULT NULL,
			`mileage` decimal(4,1) DEFAULT NULL,
			`elevation` int DEFAULT NULL,
			`title` varchar(255) DEFAULT NULL,
			`created` datetime DEFAULT NULL,
			`lastUpdated` datetime DEFAULT NULL,
			`feetPerMile` decimal(5,1) default null,
			`latitude` decimal(10,6) default null,
			`longitude` decimal(10,6) default null,
			`startLocationId` int default null,
			`state` char(2) default null,
			`town` varchar(50) default null,
			`firstName` varchar(50) default null,
			`lastName` varchar(50) default null,
			PRIMARY KEY (`stravaId`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;');
		$this->executeAlters();

		$rideTable = new \App\Table\Ride();
		$rideTable->setWhere(new \PHPFUI\ORM\Condition('description', '%strava.com/routes/%', new \PHPFUI\ORM\Operator\Like()));

		foreach ($rideTable->getRecordCursor() as $ride)
			{
			$links = \App\Tools\TextHelper::getLinks($ride->description);

			foreach ($links as $link)
				{
				if (\str_contains($link, 'strava'))
					{
					$parts = \explode('/', $link);
					$id = \array_pop($parts);
					$ride->stravaId = $id;
					$ride->update();
					}
				}
			}

		return true;
		}
	}
