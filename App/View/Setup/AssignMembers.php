<?php

namespace App\View\Setup;

class AssignMembers extends \PHPFUI\Container
	{
	public function __construct(\PHPFUI\Page $page, \App\View\Setup\WizardBar $wizardBar)
		{
		$this->add(new \PHPFUI\Header('Assign Member Roles', 4));
		$this->add($wizardBar);

		$assignView = new \App\View\Member\Assign($page);
		$this->add($assignView->getForm());
		}
	}
