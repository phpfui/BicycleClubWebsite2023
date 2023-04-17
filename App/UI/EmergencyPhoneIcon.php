<?php

namespace App\UI;

class EmergencyPhoneIcon extends \PHPFUI\Icon
	{
	public function __construct(?string $number, ?string $name)
		{
		parent::__construct('phone', 'tel:' . $number);
		$this->addClass('red');
		$this->setToolTip($name);
		$this->setConfirm("Call emergency contact {$name}?");
		}
	}
