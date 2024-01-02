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
