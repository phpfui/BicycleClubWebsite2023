<?php

namespace App\UI;

class TelUSAValidator extends \PHPFUI\Validator
	{
	public function __construct()
		{
		parent::__construct('telUSA');
		$this->setJavaScript($this->getJavaScriptTemplate('((/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/).test(to))'));
		}
	}
