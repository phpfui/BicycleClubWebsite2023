<?php

namespace App\View\GA;

class OptionPicker extends \PHPFUI\Input\Select
	{
	public function __construct(\App\Record\GaOption $option, \App\Record\GaRiderSelection $riderSelection)
		{
		parent::__construct("gaOptionId[{$option->gaOptionId}]", $option->optionName);

		if (! $riderSelection->loaded() && $option->maximumAllowed && \count($option->GaRiderSelectionChildren) >= $option->maximumAllowed)
			{
			$this->addOption('Sold Out', '');

			return;
			}

		$this->setRequired((bool)$option->required);
		$this->addOption($option->required ? 'Please Select' : '', '');

		foreach ($option->GaSelectionChildren as $selection)
			{
			if ($selection->selectionActive)
				{
				$label = $selection->selectionName;
				$price = $option->price + $selection->additionalPrice;

				if ($price)
					{
					$label .= ' - $' . \number_format($price, 2);
					}
				$this->addOption($label, (string)$selection->gaSelectionId, $riderSelection->gaSelectionId == $selection->gaSelectionId);
				}
			}
		}
	}
