<?php

namespace App\UI;

class CancelButtonGroup extends \PHPFUI\ButtonGroup
	{
	public function __construct(\PHPFUI\Button ...$buttons)
		{
		parent::__construct();

		foreach ($buttons as $button)
			{
			$this->addButton($button);
			}
		}

	protected function getStart() : string
		{
		$parts = \explode('/', \parse_url((string)$_SERVER['REQUEST_URI'])['path']);
		$last = \array_pop($parts);
		// if last one was a number, then go another one up
		if ((int)$last > 0)
			{
			\array_pop($parts);
			}
		$cancelButton = new \PHPFUI\Button('Cancel', \implode('/', $parts));
		$cancelButton->addClass('hollow')->addClass('alert');
		$this->addButton($cancelButton);

		return parent::getStart();
		}
	}
