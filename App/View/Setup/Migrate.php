<?php

namespace App\View\Setup;

class Migrate extends \PHPFUI\Container
	{
	public function __construct(private readonly \PHPFUI\Page $page, \App\View\Setup\WizardBar $wizardBar)
		{
		$this->add(new \PHPFUI\Header('Migrate the Database', 4));

		$settings = new \App\Settings\DB();
		$migrator = new \PHPFUI\ORM\Migrator();
		$migrationNeeded = $migrator->migrationNeeded();

		if (isset($_GET['migrate']))
			{
			$migrator->migrate();

			$errors = $migrator->getErrors();

			if ($errors)
				{
				\PHPFUI\Session::setFlash('alert', \print_r($errors, true));
				}

			$this->page->redirect('/Config/wizard/' . $settings->stage);

			return;
			}

		$migrationNeeded = false;
		$wizardBar->nextAllowed(! $migrationNeeded);

		if ($migrationNeeded)
			{
			$migrationButton = new \PHPFUI\Button('Migrate Database', $this->page->getBaseURL() . '?migrate');
			$migrationButton->addClass('success');
			$wizardBar->addButton($migrationButton);
			$callout = new \PHPFUI\Callout('warning');
			$callout->add('You need to migrate the database to proceed.');
			}
		else
			{
			$callout = new \PHPFUI\Callout('success');
			$callout->add('The database is on the latest migration. Proceed to the next step.');
			}
		$this->add($wizardBar);
		$this->add($callout);
		}
	}
