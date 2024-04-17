<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\CueSheetVersion> $CueSheetVersionChildren
 */
class CueSheet extends \App\Record\Definition\CueSheet
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'CueSheetVersionChildren' => [\PHPFUI\ORM\Children::class, \App\Table\CueSheetVersion::class],
	];

	public function getFullNameLink() : string
		{
		$settingTable = new \App\Table\Setting();
		$base = $settingTable->value('homePage');

		return "<a href='{$base}/CueSheets/download/{$this->cueSheetId}'>#{$this->cueSheetId} {$this->name}</a>";
		}

	public function terrain() : string
		{
		$terrain = new \App\Record\Terrain($this->terrainId);

		if (! $terrain->loaded())
			{
			return '';
			}

		return $terrain->name;
		}
	}
