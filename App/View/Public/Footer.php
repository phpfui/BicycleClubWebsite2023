<?php

namespace App\View\Public;

class Footer implements \Stringable
	{
	private readonly \App\Table\Setting $settingTable;

	private readonly \App\View\Public\Page $publicPage;

	public function __construct(\App\Model\Controller $controller)
		{
		if (! \PHPFUI\ORM::pdo())
			{
			return;
			}
		$this->settingTable = new \App\Table\Setting();
		$this->publicPage = new \App\View\Public\Page($controller);
		}

	public function __toString() : string
		{
		$copyright = $this->settingTable->value('clubName') . ' ' . \App\Tools\Date::format('Y');
		$topBar = new \PHPFUI\TopBar();
		$menu = new \PHPFUI\Menu();
		$menu->addClass('simple');
		$menu->addMenuItem(new \PHPFUI\MenuItem('By-Laws', '/pdf/By-Laws.pdf'));

		$publicPageTable = new \App\Table\PublicPage();
		$publicPageTable->addOrderBy('sequence');
		$publicPageTable->setWhere(new \PHPFUI\ORM\Condition('footerMenu', 1));

		foreach ($publicPageTable->getRecordCursor() as $page)
			{
			$link = $this->publicPage->getUniqueLink($page);
			$menu->addMenuItem(new \PHPFUI\MenuItem($page->name, $link));
			}
		$topBar->addLeft($menu);

		$topBar->addRight("&copy; {$copyright}");

		return "{$topBar}";
		}
	}
