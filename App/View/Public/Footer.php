<?php

namespace App\View\Public;

class Footer implements \Stringable
	{
	public function __toString() : string
		{
		$settingTable = new \App\Table\Setting();
		$copyright = $settingTable->value('clubName') . ' ' . \App\Tools\Date::format('Y');
		$topBar = new \PHPFUI\TitleBar();
		$menu = new \PHPFUI\Menu();
		$menu->addClass('simple');
		$byLawsFile = $settingTable->value('ByLawsFile');

		if ($byLawsFile)
			{
			$menu->addMenuItem(new \PHPFUI\MenuItem('By-Laws', $byLawsFile));
			}

		$publicPageTable = new \App\Table\PublicPage();
		$publicPageTable->setDates();
		$publicPageTable->addOrderBy('sequence');
		$condition = $publicPageTable->getWhereCondition();
		$condition->and(new \PHPFUI\ORM\Condition('footerMenu', 1));
		$publicPageTable->setWhere($condition);

		foreach ($publicPageTable->getRecordCursor() as $page)
			{
			$link = \App\View\Public\Page::getUniqueLink($page);
			$menu->addMenuItem(new \PHPFUI\MenuItem($page->name, $link));
			}
		$topBar->addLeft($menu);

		$toolTip = new \PHPFUI\ToolTip("&copy; {$copyright}", 'Check this site out on GitHub');
		$link = new \PHPFUI\Link('https://github.com/phpfui/BicycleClubWebsite2023', $toolTip);
		$topBar->addRight($link);

		return "{$topBar}";
		}
	}
