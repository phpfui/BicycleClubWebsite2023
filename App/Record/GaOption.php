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

	public function clean() : static
		{
		if (! $this->csvField || ! \preg_match("/^[\p{L}_][\p{L}\p{Nd}_$]*$/u", $this->csvField))
			{
			$this->csvField = null;
			}

		return $this;
		}

	public function clone(\App\Record\GaEvent $gaEvent) : static
		{
		$selections = [];
		foreach ($this->GaSelectionChildren as $selection)
			{
			$selections[] = clone $selection;
			}
		$this->gaOptionId = 0;
		$this->gaEvent = $gaEvent;
		$this->insert();

		foreach ($selections as $selection)
			{
			$selection->gaEvent = $gaEvent;
			$selection->gaOption = $this;
			$selection->gaSelectionId = 0;
			$selection->insert();
			}

		return $this;
		}
	}
