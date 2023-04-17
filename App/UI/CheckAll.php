<?php

namespace App\UI;

class CheckAll extends \PHPFUI\Input\CheckBox
	{
	public function __construct(string $selector, string $label = 'All')
		{
		parent::__construct('', $label);
		$this->addAttribute('onchange', '$("' . $selector . '").prop("checked",this.checked);');
		}
	}
