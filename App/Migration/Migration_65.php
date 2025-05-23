<?php

namespace App\Migration;

class Migration_65 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Normalize RWGPS Cue Sheets';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		foreach (new \App\Table\RWGPS()->getRecordCursor() as $rwgps)
			{
			$rwgps->update();
			}

		return true;
		}
	}
