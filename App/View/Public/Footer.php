<?php

namespace App\View\Public;

class Footer implements \Stringable
	{
	private readonly \App\View\Public\Page $publicPage; // @phpstan-ignore-line

	private readonly \App\Table\Setting $settingTable; // @phpstan-ignore-line

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
		$byLawsFile = $this->publicPage->value('ByLawsFile');

		if ($byLawsFile)
			{
			$menu->addMenuItem(new \PHPFUI\MenuItem('By-Laws', $byLawsFile));
			}

		$publicPageTable = new \App\Table\PublicPage();
		$publicPageTable->addOrderBy('sequence');
		$publicPageTable->setWhere(new \PHPFUI\ORM\Condition('footerMenu', 1));

		foreach ($publicPageTable->getRecordCursor() as $page)
			{
			$link = $this->publicPage->getUniqueLink($page);
			$menu->addMenuItem(new \PHPFUI\MenuItem($page->name, $link));
			}
		$topBar->addLeft($menu);

		$toolTip = new \PHPFUI\ToolTip("&copy; {$copyright}", 'Check this site out on GitHub');
		$link = new \PHPFUI\Link('https://github.com/phpfui/BicycleClubWebsite2023', $toolTip);
		$topBar->addRight($link);

		return "{$topBar}";
		}
	}
