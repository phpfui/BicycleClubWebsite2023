<?php

namespace App\View\GA;

class OptionPicker extends \PHPFUI\Input\Select
	{
	public function __construct(\App\Record\GaOption $option, int $value)
		{
		parent::__construct("gaOptionId[{$option->gaOptionId}]", $option->optionName);

		if (! $option->required)
			{
			$this->addOption('', '', 0 == $value);
			}

		foreach ($option->GaSelectionChildren as $selection)
			{
			$label = $selection->selectionName;
			$price = $option->price + $selection->additionalPrice;

			if ($price)
				{
				$label .= ' - $' . \number_format($price, 2);
				}
			$this->addOption($label, (string)$selection->gaSelectionId, $value == $selection->gaSelectionId);
			}
		}
	}
