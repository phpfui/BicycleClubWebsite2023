<?php

namespace App\UI;

class LabelStock extends \PHPFUI\Input\Select
	{
	public function __construct(string $name = 'label', string $label = 'Stock Number')
		{
		parent::__construct($name, $label);
		$labels = new \PDF_Label();

		foreach ($labels->getLabelStock() as $stock => $details)
			{
			$this->addOption($stock, $stock, '5960' == $stock);
			}
		}
	}
