<?php

namespace App\Migration;

class Migration_73 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Customize Upcoming Events text';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$settingTable = new \App\Table\Setting();
		$header = $settingTable->value('HomePageUpcoming_Events_Header');

		if (! $header)
			{
			$header = $settingTable->save('HomePageUpcoming_Events_Header', 'Upcoming Events');
			}

		return true;
		}
	}
