<?php

namespace App\UI;

class PaidSelect extends \PHPFUI\Input\RadioGroup
	{
	public function __construct(string $name = 'paid', string $label = 'Paid / Unpaid', ?string $value = '1')
		{
		parent::__construct($name, $label, $value);
		$this->addButton('Payed', (string)1);
		$this->addButton('Unpaid', (string)0);
		$this->addButton('Both', (string)2);
		$this->setToolTip('You can select Paid / Unpaid or Both for download');
		}
	}
