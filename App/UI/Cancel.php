<?php

namespace App\UI;

class Cancel extends \PHPFUI\Cancel
	{
	public function __construct(string $name = 'Cancel')
		{
		parent::__construct($name);
		$this->addClass('hollow alert');
		}
	}
