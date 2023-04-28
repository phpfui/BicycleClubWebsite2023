<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\CueSheetVersion> $CueSheetVersionChildren
 */
class CueSheet extends \App\Record\Definition\CueSheet
	{
	protected static array $virtualFields = [
		'CueSheetVersionChildren' => [\PHPFUI\ORM\Children::class, \App\Table\CueSheetVersion::class],
	];

	private static array $terrainNames = ['',
		'Flat',
		'Rolling',
		'Mod Hilly',
		'Hilly',
		'Mountainous',
		'Oh My God!', ];

	public function allTerrains() : array
		{
		return self::$terrainNames;
		}

	public function getFullNameLink() : string
		{
		$settingTable = new \App\Table\Setting();
		$base = $settingTable->value('homePage');

		return "<a href='{$base}/CueSheets/download/{$this->cueSheetId}'>#{$this->cueSheetId} {$this->name}</a>";
		}

	public function terrain() : string
		{
		return self::$terrainNames[$this->terrain] ?? '';
		}
	}
