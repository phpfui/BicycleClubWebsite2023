<?php

namespace App\View;

class Newsletter
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function display(int $year) : \PHPFUI\Container
		{
		$newsletterTable = new \App\Table\Newsletter();
		$container = new \PHPFUI\Container();
		$first = $newsletterTable->getFirst();

		if ($first->empty())
			{
			$container->add(new \PHPFUI\SubHeader('No Newsletters found'));

			return $container;
			}
		$earliest = (int)\App\Tools\Date::formatString('Y', $first->date);
		$latest = $newsletterTable->getLatest();
		$start = (int)\App\Tools\Date::formatString('Y', $latest->date);

		if (! $year)
			{
			$year = $start;
			}

		$yearNav = new \App\UI\YearSubNav('/Newsletter/all', $year, $earliest);
		$container->add($yearNav);

		$currentButtons = [];
		$newsletters = $newsletterTable->getAllByYear($year);

		foreach ($newsletters as $newsletter)
			{
			$month = \App\Tools\Date::formatString('M', $newsletter->date);
			$currentButtons[$month][$newsletter->date] = $newsletter->newsletterId;
			}
		$container->add($this->renderButtons($currentButtons));

		return $container;
		}

	public function Settings() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$settingsSaver = new \App\Model\SettingsSaver();

		$saveButton = new \PHPFUI\Submit('Save');
		$form = new \PHPFUI\Form($this->page, $saveButton);
		$fieldSet = new \PHPFUI\FieldSet('Required Settings');
		$fieldSet->add($settingsSaver->generateField('newsletterName', 'Newsletter Name'));
		$fieldSet->add($settingsSaver->generateField('newsletterEmail', 'Newsletter Sending Email Address', 'email'));

		$editor = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPicker('Newsletter Editor'));
		$editControl = $editor->getEditControl();
		$editControl->setRequired();
		$fieldSet->add($editControl);

		if ($form->isMyCallback())
			{
			$settingsSaver->save($_POST);
			$this->page->setResponse('Saved');

			return $container;
			}

		$form->add($fieldSet);
		$form->add(new \App\UI\CancelButtonGroup($saveButton));
		$container->add($form);

		return $container;
		}

	/**
	 * @param array<string, array<int|string, mixed>> $buttons
	 */
	private function renderButtons(array $buttons) : \PHPFUI\GridX
		{
		$row = new \PHPFUI\GridX();

		foreach ($buttons as $month => $monthButtons)
			{
			$count = \count($monthButtons);

			if (1 === $count)
				{
				$button = new \PHPFUI\Button($month, '/Newsletter/download/' . \current($monthButtons));
				$button->addAttribute('style', 'margin-right:.25em;');
				}
			else
				{
				$button = new \PHPFUI\DropDownButton($month);

				foreach ($monthButtons as $date => $id)
					{
					$button->addLink('/Newsletter/download/' . $id, \App\Tools\Date::formatString('D M j Y', $date));
					}
				}
			$row->add($button);
			}

		return $row;
		}
	}
