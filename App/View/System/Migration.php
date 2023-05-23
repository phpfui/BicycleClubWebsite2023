<?php

namespace App\View\System;

class Migration
	{
	private readonly \PHPFUI\ORM\Migrator $model;

	public function __construct(private readonly \App\View\Page $page)
		{
		\App\Tools\File::mkdir(\PHPFUI\ORM::getMigrationNamespacePath(), 0x777, true);
		$this->model = new \PHPFUI\ORM\Migrator();
		}

	public function list() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$table = new \PHPFUI\SortableTable();
		$table->setHeaders(['Id', 'Description', 'Ran', 'Action']);

		// get the parameter we know we are interested in
		$parameters = $table->getParsedParameters();
		$page = (int)($parameters['p'] ?? 0);
		$limit = (int)($parameters['l'] ?? 10);

		$migrations = $this->model->getMigrationObjects($page, $limit);
		$undo = new \PHPFUI\AJAX('undoMigration', 'Rollback to this version?');
		$undo->addFunction('success', '');
		$this->page->addJavaScript($undo->getPageJS());

		foreach ($migrations as $migration)
			{
			$row = [];
			$row['Id'] = $migration->id();
			$row['Description'] = $migration->description();

			if ($migration->ran())
				{
				$row['Ran'] = \date('Y-m-d g:i a', \strtotime($migration->ran()));
				}
			else
				{
				$row['Ran'] = '';
				}

			$field = \PHPFUI\Session::csrfField();
			$csrf = \PHPFUI\Session::csrf();
			$migrateUrl = "/System/migrations/{$page}?{$field}={$csrf}&";

			if ($migration->ran())
				{
				$id = $migration->id() - 1;
				$icon = new \PHPFUI\FAIcon('fas', 'undo', $migrateUrl . "migration={$id}");
				$icon->setConfirm('Undo to before this version?');
				$row['Action'] = $icon;
				}
			else
				{
				$icon = new \PHPFUI\FAIcon('fas', 'play', $migrateUrl . "migration={$migration->id()}");
				$icon->setConfirm('Roll forward to this version?');
				$row['Action'] = $icon;
				}

			$table->addRow($row);
			}

		if (! \count($migrations))
			{
			$container->add(new \PHPFUI\SubHeader('No migrations found'));
			}
		else
			{
			$container->add($table);
			}

		$parameters['p'] = 'PAGE';
		$url = $table->getBaseUrl() . '?' . \http_build_query($parameters);
		$lastPage = (int)((\count($this->model) - 1) / $limit) + 1;
		$paginator = new \PHPFUI\Pagination($page, $lastPage, $url);
		$paginator->center();
		$container->add($paginator);

		return $container;
		}
	}
