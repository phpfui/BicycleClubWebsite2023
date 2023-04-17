<?php

namespace App\WWW;

class Locations extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\StartLocation $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\StartLocation($this->page);
		}

	public function edit(\App\Record\StartLocation $location = new \App\Record\StartLocation()) : void
		{
		if ($this->page->addHeader('Edit Start Location'))
			{
			$this->page->addPageContent($this->view->edit($location));
			}
		}

	public function locations() : void
		{
		if ($this->page->addHeader('Start Locations'))
			{
			$this->page->addPageContent($this->view->showLocations());
			}
		}

	public function merge() : void
		{
		if ($this->page->addHeader('Merge Start Locations'))
			{
			$this->page->addPageContent($this->view->Merge());
			}
		}

	public function new() : void
		{
		if ($this->page->addHeader('Add Start Location'))
			{
			if (! $this->view->checkForAdd())
				{
				$this->page->addPageContent($this->view->edit(new \App\Record\StartLocation()));
				}
			}
		}
	}
