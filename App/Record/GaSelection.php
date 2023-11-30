<?php

namespace App\Record;

/**
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\GaRiderSelection> $GaRiderSelectionChildren
 */
class GaSelection extends \App\Record\Definition\GaSelection
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'GaRiderSelectionChildren' => [\PHPFUI\ORM\Children::class, \App\Table\GaRiderSelection::class],
	];
	}
