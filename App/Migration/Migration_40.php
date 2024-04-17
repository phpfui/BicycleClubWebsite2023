<?php

namespace App\Migration;

class Migration_40 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add terrain table';
		}

	public function down() : bool
		{
		$this->alterColumn('cuesheet', 'terrainId', 'int', 'terrain');

		return $this->dropTable('terrain');
		}

	public function up() : bool
		{
		$this->alterColumn('cuesheet', 'terrain', 'int', 'terrainId');

		$this->runSQL('CREATE TABLE `terrain` (`terrainId` int NOT NULL AUTO_INCREMENT,`name` varchar(70) DEFAULT "",PRIMARY KEY (`terrainId`))');

		foreach (['Flat', 'Rolling', 'Mod Hilly', 'Hilly', 'Mountainous', 'Oh My God!', ] as $name)
			{
			$terrain = new \App\Record\Terrain();
			$terrain->name = $name;
			$terrain->insert();
			}

		return true;
		}
	}
