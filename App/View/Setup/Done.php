<?php

namespace App\View\Setup;

class Done extends \PHPFUI\Container
	{
	public function __construct(private readonly \PHPFUI\Page $page, \App\Settings\DB $settings, \App\View\Setup\WizardBar $wizardBar)
		{
		$this->add(new \PHPFUI\Header('Setup Wizard Complete!', 4));

		if (isset($_GET['finish']))
			{
			$settings->setup = false;
			$settings->save();

			$this->page->redirect('/Home');

			return;
			}

		$wizardBar->nextAllowed(false);

		$finishButton = new \PHPFUI\Button('Go Live!', $this->page->getBaseURL() . '?finish');
		$finishButton->addClass('success');
		$wizardBar->addButton($finishButton);
		$this->add($wizardBar);
		}
	}
