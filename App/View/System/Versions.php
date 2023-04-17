<?php

namespace App\View\System;

class Versions
	{
	// @phpstan-ignore-next-line
	public function __construct(\App\View\Page $page, private readonly \Gitonomy\Git\Repository $model)
		{
		}

	public function list(string $branch = '') : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (empty($branch))
			{
			$branch = 'master';
			}
		$page = (int)($_GET['p'] ?? 0);
		$limit = (int)($_GET['l'] ?? 25);

		$table = new \PHPFUI\SortableTable();
		$table->setHeaders(['Title', 'Date', 'Release', 'Commit']);

		// fetch origin and prune any local branches no longer on github
		$this->model->run('fetch', ['--prune']);
		$head = $this->model->getHeadCommit();
		$currentHash = $head->getHash();

		$branches = $this->model->getReferences()->getBranches();
		$subnav = new \App\UI\SubNav('Displaying Branch:');

		foreach ($branches as $b)
			{
			$name = $b->getName();

			if (\str_starts_with((string)$name, 'origin/'))
				{
				$subnav->addTab("/System/versions/{$name}", $name, $name == $branch);
				}
			}
		$container->add($subnav);

		$log = $this->model->getLog([$branch]);

		$count = \count($log);
		$lastPage = (int)(($count - 1) / $limit) + 1;

		$log->setOffset($page * $limit);
		$log->setLimit($limit);

		$localTZ = new \DateTimeZone(\date_default_timezone_get());
		$sortedTags = new \App\Tools\SortedTags($this->model);

		foreach ($log as $commit)
			{
			$hash = $commit->getHash();
			$row = [];
			$row['Title'] = $commit->getShortMessage();
			$row['Date'] = $commit->getCommitterDate()->setTimezone($localTZ)->format('Y-m-d g:i a');
			$row['Release'] = ($tag = $sortedTags->getTag($hash)) ? $tag->getName() : '';

			if ($hash == $currentHash)
				{
				$row['Commit'] = '<b>On This Version</b>';
				}
			else
				{
				$field = \PHPFUI\Session::csrfField();
				$csrf = \PHPFUI\Session::csrf();
				$migrateUrl = "/System/versions/{$branch}?{$field}={$csrf}&sha1={$hash}";
				$link = new \PHPFUI\Link($migrateUrl, $commit->getShortHash(), false);
				$link->setConfirm('Deploy this version?');
				$row['Commit'] = $link;
				}
			$table->addRow($row);
			}

		if (! \count($log))
			{
			$container->add(new \PHPFUI\SubHeader('No versions found'));
			}
		else
			{
			$container->add($table);
			}

		$parameters = $table->getParsedParameters();
		$parameters['l'] = $limit;
		$parameters['p'] = 'PAGE';

		$url = $table->getBaseUrl() . '?' . \http_build_query($parameters);

		$paginator = new \PHPFUI\Pagination($page, $lastPage, $url);
		$paginator->center();
		$paginator->setFastForward(30);
		$container->add($paginator);

		return $container;
		}
	}
