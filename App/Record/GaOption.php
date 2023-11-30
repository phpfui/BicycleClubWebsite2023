<?php

namespace App\Record;

/**
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\GaSelection> $GaSelectionChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\GaRiderSelection> $GaRiderSelectionChildren
 */
class GaOption extends \App\Record\Definition\GaOption
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'GaSelectionChildren' => [\PHPFUI\ORM\Children::class, \App\Table\GaSelection::class, 'ordering'],
		'GaRiderSelectionChildren' => [\PHPFUI\ORM\Children::class, \App\Table\GaRiderSelection::class],
	];
	}
