<?php

namespace App\UI;

class MainMenu extends \PHPFUI\AccordionMenu
	{
	private string $activeMenu = '';

	private string $currentMenu = '';

	private array $theMenu = [];

	public function __construct(private readonly \App\Model\PermissionBase $permissions, private string $activeLink = '')
		{
		parent::__construct();
		$parts = \explode('/', $this->activeLink);
		$this->activeMenu = $parts[1] ?? '';
		}

	public function addMenu(\PHPFUI\Menu $parentMenu, string $page, string $name) : ?\PHPFUI\Menu
		{
		$this->currentMenu = $name;

		$menu = null;

		if ($this->permissions->isAuthorized($name, $this->currentMenu))
			{
			$menu = new \PHPFUI\Menu();
			$parentMenu->addSubMenu(new \PHPFUI\MenuItem($name), $menu);

			if ($this->activeLink == $page)
				{
				$parentMenu->addClass('is-active');
				$menu->addClass('is-active');
				}
			$this->theMenu[$name] = $menu;
			}

		return $menu;
		}

	public function addSub(\PHPFUI\Menu $parentMenu, string $page, string $name) : static
		{
		if ($this->permissions->isAuthorized($name, $this->currentMenu))
			{
			$urlParts = \parse_url($page);

			$target = '';

			if (isset($urlParts['scheme']))
				{
				// do nothing, outside link
				$target = '_blank';
				}

			$menuItem = new \PHPFUI\MenuItem($name, $page);

			if ($target)
				{
				$menuItem->getLinkObject()->addAttribute('target', $target);
				}

			if ($this->activeLink == $page)
				{
				$menuItem->setActive();
				}

			$parentMenu->addMenuItem($menuItem);
			}

		return $this;
		}

	public function addTopMenu(string $menuName, string $name) : ?\PHPFUI\Menu
		{
		$this->currentMenu = $menuName;

		$menu = null;

		if ($this->permissions->isAuthorized($name, $menuName))
			{
			$menu = new \PHPFUI\Menu();
			$this->addSubMenu(new \PHPFUI\MenuItem($name), $menu);
			$this->theMenu[$menuName] = $menu;
			}

		return $menu;
		}

	public function getActiveMenu() : string
		{
		return $this->activeMenu;
		}

	public function getLandingPage(\App\View\Page $page, string $section) : \App\UI\LandingPage
		{
		$landingPage = new \App\UI\LandingPage($page);

		if (isset($this->theMenu[$section]))
			{
			foreach ($this->theMenu[$section]->getMenuItems() as $menuItem)
				{
				if ($menuItem instanceof \PHPFUI\MenuItem)
					{
					$landingPage->addMenuItem($menuItem);
					}
				}
			}

		return $landingPage;
		}

	public function getMenuSections() : array
		{
		return $this->theMenu;
		}

	/**
	 * @return string[]
	 *
	 * @psalm-return list<string>
	 */
	public function getSectionURLs() : array
		{
		$returnValue = [];

		foreach ($this->theMenu as $key => $menu)
			{
			foreach ($menu->getMenuItems() as $menuItem)
			$returnValue[] = $menuItem->getLink();
			}

		return $returnValue;
		}

	protected function getStart() : string
		{
		$this->walk('sort');

		return parent::getStart();
		}
	}
