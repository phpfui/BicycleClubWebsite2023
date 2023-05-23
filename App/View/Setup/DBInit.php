<?php

namespace App\View\Setup;

class DBInit extends \PHPFUI\Container
	{
	public function __construct(private readonly \PHPFUI\Page $page, \App\View\Setup\WizardBar $wizardBar)
		{
		\App\Tools\File::mkdir(\PHPFUI\ORM::getMigrationNamespacePath(), 0x777, true);
		$migrator = new \PHPFUI\ORM\Migrator();

		$this->add(new \PHPFUI\Header('Initialize the Database', 4));

		$officialTables = [];

		foreach (\PHPFUI\ORM\Table::getAllTables() as $table)
			{
			$officialTables[\strtolower($table->getTableName())] = false;
			}

		$tableCursor = \PHPFUI\ORM::getArrayCursor('show tables');
		$currentTables = [];

		foreach ($tableCursor as $tableArray)
			{
			$table = \strtolower((string)\array_pop($tableArray));
			$currentTables[$table] = false;
			}

		$extraTables = [];

		foreach ($currentTables as $table => $inuse)
			{
			if (isset($officialTables[$table]))
				{
				$officialTables[$table] = true;
				}
			else
				{
				$extraTables[] = $table;
				}
			}

		$missingTables = [];

		foreach ($officialTables as $table => $inuse)
			{
			if (! $inuse)
				{
				$missingTables[$table] = $table;
				}
			}

		$settings = new \App\Settings\DB();

		if (isset($_GET['extra']))
			{
			foreach ($tableCursor as $tableArray)
				{
				$table = \array_pop($tableArray);

				if (\in_array(\strtolower((string)$table), $extraTables))
					{
					\PHPFUI\ORM::execute('drop table ' . $table);
					}
				}
			$this->page->redirect('/Config/wizard/' . $settings->stage);

			return;
			}

		if (isset($_GET['init']))
			{
			$restore = new \App\Model\Restore(PROJECT_ROOT . '/Initial.schema');

			if (! $restore->run())
				{
				\App\Model\Session::setFlashList('alert', $restore->getErrors());
				}
			else
				{
				$migrator->migrate();

				$errors = $migrator->getErrors();

				if ($errors)
					{
					\PHPFUI\Session::setFlash('alert', \print_r($errors, true));
					}

				$permissionModel = new \App\Model\Permission();
				$permissionModel->loadStandardPermissions();
				}

			$this->page->redirect('/Config/wizard/' . $settings->stage);

			return;
			}

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

		$initDB = false;
		$dropExtra = \count($extraTables) > 0;
		$callout = '';

		if ($migrator->migrationNeeded() && ! \array_key_exists('migration', $missingTables))
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->add('You need to run press the Migrate Database button.');
			$callout->add('<br><br>OR<br><br>');
			$callout->add('You must click on the Initialize Database button to continue.');
			$migrateButton = new \PHPFUI\Button('Migrate Database', $this->page->getBaseURL() . '?migrate');
			$migrateButton->addClass('info');
			$wizardBar->addButton($migrateButton);
			$initDB = true;
			}
		elseif (! \count($missingTables) && ! \count($extraTables))
			{
			$callout = new \PHPFUI\Callout('success');
			$callout->add('All required tables are present and no extra tables have been found.');
			$callout->add('<br><br>It is optional to the initialize database.');
			}
		elseif (\count($missingTables) == \count($officialTables))
			{
			$callout = new \PHPFUI\Callout('success');
			$callout->add('This appears to be an empty database, which is good!<br><br>Click on the Initialize Database button to continue.');
			$initDB = true;
			}
		elseif (! \count($missingTables) && \count($extraTables))
			{
			$callout = new \PHPFUI\Callout('warning');
			$callout->add('All required tables are present, but these tables are extra:<p>');
			$callout->add($this->list($extraTables));
			$callout->add('<p>It is optional to the initialize database or remove the extra tables.');
			}
		elseif (\count($missingTables))
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->add('These tables are missing:<p>');
			$callout->add($this->list($missingTables));
			$callout->add('<p>You must click on the Initialize Database button to continue.');
			$initDB = true;
			}

		$wizardBar->nextAllowed(! $initDB);

		if ($dropExtra)
			{
			$removeExtraButton = new \PHPFUI\Button('Remove Extra Tables', $this->page->getBaseURL() . '?extra');
			$removeExtraButton->addClass('warning');
			$removeExtraButton->setConfirm('Are you sure you want to remove the extra tables?');
			$wizardBar->addButton($removeExtraButton);
			}
		$initButton = new \PHPFUI\Button('Initialize Database', $this->page->getBaseURL() . '?init');

		if (! $initDB)
			{
			$initButton->addClass('warning');
			$initButton->setConfirm('Are you sure you want to initialize the database and remove all existing data?');
			}
		else
			{
			$initButton->addClass('success');
			}
		$wizardBar->addButton($initButton);
		$this->add($callout);
		$this->add($wizardBar);
		}

	private function list(array $tables) : \PHPFUI\UnorderedList
		{
		$ul = new \PHPFUI\UnorderedList();

		foreach ($tables as $table)
			{
			$ul->addItem(new \PHPFUI\ListItem($table));
			}

		return $ul;
		}
	}
