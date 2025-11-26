<?php

namespace App\WWW;

class Holiday extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Holiday $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Holiday($this->page);
		}

	public function halloween() : void
		{
		if ($this->page->isAuthorized('Halloween') || $this->view->date(10, 31))
			{
			echo $this->view->halloween();

			exit;
			}

		$this->page->redirect('/');
		}

	public function independenceDay() : void
		{
		if ($this->page->isAuthorized('Independence Day') || $this->view->date(7, 4))
			{
			echo $this->view->independenceDay();

			exit;
			}

		$this->page->redirect('/');
		}

	public function newYearsDay() : void
		{
		if ($this->page->isAuthorized('New Years Day') || $this->view->date(1, 1))
			{
			echo $this->view->newYearsDay();

			exit;
			}

		$this->page->redirect('/');
		}

	public function snow() : void
		{
		}

	public function thanksgiving() : void
		{
		if ($this->page->isAuthorized('Thanksgiving') || $this->view->thanksgivingDay())
			{
			echo $this->view->thanksgiving();

			exit;
			}

		$this->page->redirect('/');
		}
	}
