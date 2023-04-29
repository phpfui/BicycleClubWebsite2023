<?php

namespace App\View\Public;

class Menu extends \PHPFUI\Menu implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Public\Page $publicPage;

	public function __construct(private readonly \PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct();
		$this->addClass('vertical');

		$publicPageTable = new \App\Table\PublicPage();
		$publicPageTable->addOrderBy('sequence');
		$this->publicPage = new \App\View\Public\Page($controller);

		foreach ($publicPageTable->getRecordCursor() as $page)
			{
			$this->addPage($page);
			}

		$redirectTable = new \App\Table\Redirect();

		foreach ($redirectTable->getRecordCursor() as $redirect)
			{
			// @phpstan-ignore-next-line
			$this->controller->addRedirect($redirect->originalUrl, $redirect->redirectUrl);
			}
		}

	private function addPage(\App\Record\PublicPage $page) : void
		{
		if (! $page['name'])
			{
			return;
			}
		$link = $this->publicPage->getUniqueLink($page);

		if ($page['publicMenu'])
			{
			$this->addMenuItem(new \PHPFUI\MenuItem($page['name'], $link));
			}
		$query = \strpos($link, '?');

		if (false !== $query)
			{
			$link = \substr($link, 0, $query);
			}

		// @phpstan-ignore-next-line
		$this->controller->addRoute($link, [$this->publicPage, 'custom']);
		}
	}
