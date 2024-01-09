<?php

namespace App\WWW\Locations;

class Coordinates extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\StartLocation\Coordinates $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\StartLocation\Coordinates($this->page);
		}

	public function assigned() : void
		{
		if ($this->page->addHeader('Assigned Coordinates'))
			{
			$this->page->addPageContent($this->view->assigned());
			}
		}

	public function missing() : void
		{
		if ($this->page->addHeader('Missing Coordinates'))
			{
			$this->page->addPageContent($this->view->missing());
			}
		}

	public function update() : void
		{
		if ($this->page->addHeader('Update Coordinates'))
			{
			$this->page->addPageContent($this->view->update());
			}
		}

	public function updateAdd() : void
		{
		if ($this->page->isAuthorized('Update Coordinates'))
			{
			$model = new \App\Model\StartLocation();
			$model->computeCoordinates();
			}
		$this->page->redirect('/Locations/Coordinates/update');
		}

	public function updateExisting() : void
		{
		if ($this->page->isAuthorized('Update Coordinates'))
			{
			$model = new \App\Model\StartLocation();
			$model->computeCoordinates(overwriteStartLocations:true, updateRWGPS:true);
			}
		$this->page->redirect('/Locations/Coordinates/update');
		}
	}
