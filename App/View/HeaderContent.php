<?php

namespace App\View;

class HeaderContent
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function edit(\App\Record\HeaderContent $headerContent) : \App\UI\ErrorFormSaver
		{
		$listUrl = '/Content/Header/list';

		if ($headerContent->headerContentId)
			{
			$submit = new \PHPFUI\Submit();
			$redirectOnAdd = '';
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add');
			$headerContent->headerContentId = 0;
			$redirectOnAdd = $listUrl;
			}

		$form = new \App\UI\ErrorFormSaver($this->page, $headerContent, $submit);

		if ($form->save($redirectOnAdd))
			{
			return $form;
			}

		$tabs = new \PHPFUI\Tabs();
		$tabs->addTab('Details', $this->getDetailsTab($headerContent), true);
		$htmlEditor = new \App\UI\TextAreaImage('content', 'Header Content', $headerContent->content);
		$htmlEditor->htmlEditing($this->page, new \App\Model\TinyMCETextArea($headerContent->getLength('content')));
		$tabs->addTab('HTML', $htmlEditor);
		$tabs->addTab('CSS', new \PHPFUI\Input\TextArea('css', 'Header CSS', $headerContent->css));
		$tabs->addTab('JavaScript', new \PHPFUI\Input\TextArea('javaScript', 'Header JavaScript', $headerContent->javaScript));
		$form->add($tabs);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);

		if ($headerContent->headerContentId)
			{
			$testButton = new \PHPFUI\Button('Test', '/Content/Header/test/' . $headerContent->headerContentId);
			$testButton->addClass('warning');
			$buttonGroup->addButton($testButton);
			}
		$listButton = new \PHPFUI\Button('All Headers', $listUrl);
		$listButton->addClass('secondary')->addClass('hollow');
		$buttonGroup->addButton($listButton);
		$form->add($buttonGroup);

		return $form;
		}

	public function list(\App\Table\HeaderContent $headerContentTable) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (\count($headerContentTable))
			{
			$view = new \App\UI\ContinuousScrollTable($this->page, $headerContentTable);
			$deleter = new \App\Model\DeleteRecord($this->page, $view, $headerContentTable, 'Permanently delete this header?');
			$view->addCustomColumn('del', $deleter->columnCallback(...));
			$view->addCustomColumn('urlPath', static fn (array $row) : \PHPFUI\Link => new \PHPFUI\Link('/Content/Header/edit/' . $row['headerContentId'], $row['urlPath'], false));
			$view->addCustomColumn('active', static fn (array $row) : string => $row['active'] ? '&check;' : '');
			$headers = ['urlPath', 'name', 'startDate', 'endDate', 'active'];
			$view->setSearchColumns($headers)->setHeaders(\array_merge($headers, ['del']))->setSortableColumns($headers);
			$container->add($view);
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('No Content Headers Found'));
			}

		return $container;
		}

	private function getDetailsTab(\App\Record\HeaderContent $headerContent) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$container->add(new \PHPFUI\Input\Hidden('headerContentId', (string)$headerContent->headerContentId));
		$urlPath = new \PHPFUI\Input\Text('urlPath', 'URL Path to match against', $headerContent->urlPath);
		$urlPath->setRequired()->setToolTip('This can be a partical path to match a range of ULRs or a specific URL to match just one page');
		$name = new \PHPFUI\Input\Text('name', 'Name for identification', $headerContent->name);
		$name->setToolTip('Set the name for easy identification in the list view. Never shown to users');
		$active = new \PHPFUI\Input\CheckBoxBoolean('active', 'Active', (bool)$headerContent->active);
		$active->setToolTip('Inactive headers can still be tested but will not be displayed for users');
		$multiColumn = new \PHPFUI\MultiColumn($urlPath, $name, $active);
		$container->add($multiColumn);

		$startDate = new \PHPFUI\Input\Date($this->page, 'startDate', 'Start Date', $headerContent->startDate);
		$startDate->setToolTip('Header will not be shown until this date');
		$endDate = new \PHPFUI\Input\Date($this->page, 'endDate', 'End Date', $headerContent->endDate);
		$endDate->setToolTip('Header will not be shown after this date');
		$multiColumn = new \PHPFUI\MultiColumn($startDate, $endDate);
		$container->add($multiColumn);

		$month = new \App\UI\Month('showMonth', 'Show on this month', (string)$headerContent->showMonth);
		$month->setToolTip('Header will be shown on only on this month if set');
		$day = new \PHPFUI\Input\Number('showDay', 'Show on this day of the month', $headerContent->showDay);
		$day->addAttribute('max', '31')->addAttribute('min', '1')->setToolTip('If combined with a month, then the header will only be shown on the specified month and day');
		$multiColumn = new \PHPFUI\MultiColumn($month, $day);
		$container->add($multiColumn);

		return $container;
		}
	}
