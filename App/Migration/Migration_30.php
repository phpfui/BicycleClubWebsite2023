<?php

namespace App\Migration;

class Migration_30 extends \PHPFUI\ORM\Migration
	{
	private \App\Table\Setting $settingTable;

	public function description() : string
		{
		return 'Add Cue Sheet Fonts';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->settingTable = new \App\Table\Setting();
		$this->setValue('CueSheetFont', 'helvetica');
		$this->setValue('CueSheetFontSize', '14');

		$permissionTable = new \App\Table\Permission();
		$permissionTable->setWhere(new \PHPFUI\ORM\Condition('name', 'Add CueSheet Ride'));
		$permissionTable->update(['name' => 'Add Ride To Schedule']);

		return true;
		}

	private function setValue(string $name, string $value) : void
		{
		$oldValue = $this->settingTable->value($name);

		if (! $oldValue)
			{
			$this->settingTable->save($name, $value);
			}
		}
	}
