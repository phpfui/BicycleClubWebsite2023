<?php

namespace App\View;

class PacePicker extends \PHPFUI\MultiColumn
	{
	public function __construct(string $name = 'paceId', string $categoryTitle = 'Category', string $paceTitle = 'Pace', int $selected = 0)
		{
		parent::__construct();

		$categoryTable = new \App\Table\Category();
		$paceTable = new \App\Table\Pace();
		$paces = $paceTable->getPaces();
		$settingTable = new \App\Table\Setting();

		if (0 == (int)$settingTable->value('PacePicker'))
			{
			$select = new \PHPFUI\Input\Select($name, $categoryTitle);
			$select->setToolTip('Expected Category / Pace of the ride');

			foreach ($paces as $key => $pace)
				{
				$select->addOption(\App\View\Categories::getPaceLabel(new \App\Record\Pace($pace)), $key, $selected == $key);
				}

			$this->add($select);

			return;
			}

		$categorySelected = $paces[$selected]['categoryId'] ?? 0;

		$categoryInput = new \PHPFUI\Input\Select('category' . $name);
		$categoryLabel = new \PHPFUI\HTML5Element('label');
		$categoryLabel->addAttribute('for', $categoryInput->getId());
		$categoryLabel->add(new \PHPFUI\ToolTip($categoryTitle, 'Choose the overall ride category'));
		$categoryLabel->add($categoryInput);
		$this->add($categoryLabel);

		$categories = \array_merge([['categoryId' => 0, 'category' => 'All']], $categoryTable->getAllCategories());
		$paceInput = new \PHPFUI\Input\Select($name);
		$paceInputId = $paceInput->getId();

		foreach ($categories as $category)
			{
			$categoryId = $category['categoryId'];
			$categoryInput->addOption($category['category'], $categoryId, $categoryId == $categorySelected);
			}

		$categoryId = 0;
		$js = 'var paces = {';
		$comma = '';
		$activePace = null;

		foreach ($paces as $key => $pace)
			{
			if ($pace['categoryId'] != $categoryId)
				{
				$js .= $comma . '"' . $categoryId . '":"' . \htmlspecialchars(\str_replace("\n", '', $paceInput)) . '"';
				$comma = ',';
				$paceInput = new \PHPFUI\Input\Select($name);
				$paceInput->setId($paceInputId);
				$categoryId = $pace['categoryId'];
				}
			$active = $selected == $key;
			$paceInput->addOption($pace['pace'], $key, $active);

			if ($active && ! $activePace)
				{
				$activePace = $paceInput;
				}
			}

		if (! $activePace)
			{
			$paceInput->removeAll();
			$paceInput->addOption('All', (string)1);
			$activePace = $paceInput;
			}
		$js .= $comma . '"' . $categoryId . '":"' . \htmlspecialchars(\str_replace("\n", '', $paceInput)) . '"};';
		$js .= '$("#' . $paceInputId . '").replaceWith($("<textarea />").html(paces[this.value]).text());';

		$categoryInput->setAttribute('onchange', $js);

		$paceLabel = new \PHPFUI\HTML5Element('label');
		$paceLabel->addAttribute('for', $paceInputId);
		$paceLabel->add(new \PHPFUI\ToolTip($paceTitle, 'Choose the pace within the ride category'));
		$paceLabel->add($activePace);
		$this->add($paceLabel);
		}
	}
