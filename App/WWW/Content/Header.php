<?php

namespace App\WWW\Content;

class Header extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\HeaderContent $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\HeaderContent($this->page);
		}

	public function edit(\App\Record\HeaderContent $headerContent = new \App\Record\HeaderContent()) : void
		{
		$type = $headerContent->loaded() ? 'Edit' : 'Add';

		if ($this->page->addHeader($type . ' Header Content'))
			{
			$this->page->addPageContent($this->view->edit($headerContent));
			}
		}

	public function list() : void
		{
		if ($this->page->addHeader('Header Content'))
			{
			$headerContentTable = new \App\Table\HeaderContent();
			$this->page->addPageContent(new \PHPFUI\Button('Add Header', '/Content/Header/edit/0'));
			$this->page->addPageContent($this->view->list($headerContentTable));
			}
		}

	public function test(\App\Record\HeaderContent $headerContent = new \App\Record\HeaderContent()) : void
		{
		$model = new \App\Model\HeaderContent('');
		$model->setHeaderContent($headerContent);
		$this->page->setHeaderContentModel($model);
		$view = new \App\View\Rides($this->page);
		$table = new \App\Table\Ride();
		$table->setLimit(50);
		$table->addOrderBy('rideDate');

		$this->page->addPageContent($view->schedule($table->getRecordCursor()));
		}
	}
