<?php

namespace App\WWW\System;

class Releases extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function landingPage() : void
		{
		$this->page->landingPage('System Releases');
		}

	public function migrations() : void
		{
		if (\PHPFUI\Session::checkCSRF() && isset($_GET['migration']))
			{
			$model = new \PHPFUI\ORM\Migrator();
			$model->migrateTo((int)$_GET['migration']);
			$errors = $model->getErrors();

			if ($errors)
				{
				$messages = [];

				foreach ($errors as $fields)
					{
					if (\is_array($fields))
						{
						$messages[] = "Error: {$fields['error']}, {$fields['sql']}";
						}
					else
						{
						$messages[] = $fields;
						}
					}
				\App\Model\Session::setFlash('alert', $messages);
				}
			else
				{
				\App\Model\Session::setFlash('success', $model->getStatus());
				}

			$this->page->redirect();
			}
		elseif ($this->page->addHeader('Migrations'))
			{
			$view = new \App\View\System\Migration($this->page);
			$this->page->addPageContent($view->list());
			}
		}

	public function releaseNotes() : void
		{
		if ($this->page->addHeader('Release Notes'))
			{
			$releaseNotes = new \App\View\System\ReleaseNotes();

			if ($releaseNotes->count())
				{
				$this->page->addPageContent($releaseNotes->show());
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('No Release Notes Found'));
				}
			}
		}

	public function releases() : void
		{
		$repo = new \Gitonomy\Git\Repository(PROJECT_ROOT);
		$deployer = new \App\Model\Deploy($repo);

		if (\PHPFUI\Session::checkCSRF() && isset($_GET['sha1']))
			{
			$deployer->deployTarget($_GET['sha1']);
			$this->page->redirect();

			return;
			}

		if ($this->page->addHeader('Releases'))
			{
			$view = new \App\View\System\Releases($repo);
			$this->page->addPageContent($view->list($deployer->getReleaseTags()));
			}
		}

	public function versions(string $origin = '', string $branch = '') : void
		{
		$repo = new \Gitonomy\Git\Repository(PROJECT_ROOT);

		if (\PHPFUI\Session::checkCSRF() && isset($_GET['sha1']))
			{
			$deployer = new \App\Model\Deploy($repo);
			$deployer->deployTarget($_GET['sha1']);
			$this->page->redirect();
			}
		elseif ($this->page->addHeader('Versions'))
			{
			if (empty($branch))
				{
				$branch = $origin;
				}
			elseif ($origin)
				{
				$branch = $origin . '/' . $branch;
				}
			$repo->run('fetch');
			$view = new \App\View\System\Versions($this->page, $repo);
			$this->page->addPageContent($view->list($branch));
			}
		}
	}
