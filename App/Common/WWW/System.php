<?php

namespace App\Common\WWW;

class System extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function auditTrail() : void
		{
		if ($this->page->addHeader('Audit Trail'))
			{
			$view = new \App\View\System\AuditTrail($this->page);
			$this->page->addPageContent($view->getTrail());
			}
		}

	public function cron() : void
		{
		if ($this->page->addHeader('Cron Jobs'))
			{
			$cronView = new \App\View\System\Cron($this->page);
			$this->page->addPageContent($cronView->list());
			}
		}

	public function importSQL() : void
		{
		if ($this->page->addHeader('Import SQL'))
			{
			$view = new \App\View\System\Import($this->page);
			$this->page->addPageContent($view->SQL());
			}
		}

	public function landingPage() : void
		{
		$this->page->landingPage('System');
		}

	public function memory() : void
		{
		$errorModel = new \App\Model\Errors();
		// clear the error log
		$errorModel->deleteAll();
		\file_get_contents($this->page->getSchemeHost() . '/System/memoryHog');  // create a memory error
		\sleep(1);	// wait to make sure the error log is written
		$errors = $errorModel->getErrors(true);

		// find the error and get the memory limit
		foreach($errors as $error)
			{
			$text = 'Allowed memory size of ';
			$pos = \strpos($error, $text);

			if ($pos)
				{
				$maxMemory = (int)\substr($error, $pos + \strlen($text));
				echo 'Your version of PHP supports <b>' . \number_format($maxMemory / 102400, 0, '.', ',') . '</b> MB of memory.';
				echo '<br><br>At least 700 MB is recommended.';
				}
			}
		echo 'Unable to determine memory size.';

		exit;
		}

	public function memoryHog() : void
		{
		$memoryHog = [];

		// @phpstan-ignore while.alwaysTrue
		while (1)
			{
			$memoryHog[] = \array_fill(0, 1000, 1);  // produce an out of memory error
			}
		}

	public function permission(string $reload = '') : void
		{
		if ($this->page->addHeader('Permission Reloader'))
			{
			$baseUri = '/System/permission';
			$reloadNumber = \App\Model\Session::getFlash('reload');

			if ($reload && $reloadNumber == $reload)
				{
				$this->page->getPermissions()->generatePermissionLoader();
				\App\Model\Session::setFlash('success', 'Permissions Regerated');
				$this->page->redirect($baseUri);
				}
			else
				{
				$callout = new \PHPFUI\Callout('alert');
				$callout->add('Sometimes the permissions file needs to be reloaded, like after a database restore or other permissions database work. It may result in currently logged in users to have to log out and back in to see the results.');
				$this->page->addPageContent($callout);
				$random = \random_int(0, \mt_getrandmax());
				\App\Model\Session::setFlash('reload', $random);
				$button = new \PHPFUI\Button('Reload Permission File', $baseUri . '/' . $random);
				$button->addClass('alert');
				$button->setConfirm('Reloading the permission file may cause users to need to log in again.  Are you sure?');
				$this->page->addPageContent($button);
				}
			}
		}

	public function redirects() : void
		{
		if ($this->page->addHeader('Redirects'))
			{
			$this->page->addPageContent(new \App\View\System\Redirects($this->page));
			}
		}
	}
