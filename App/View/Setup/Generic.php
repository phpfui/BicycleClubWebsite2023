<?php

namespace App\View\Setup;

class Generic extends \PHPFUI\Container
	{
	public function __construct(string $title, string $view, \App\View\Setup\WizardBar $wizardBar)
		{
		$this->add(new \PHPFUI\Header($title, 4));

		$this->add($wizardBar);

		$this->add($view);
		}
	}
