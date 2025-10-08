<?php

namespace App\WWW;

class RWGPS extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\RideWithGPS $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\RideWithGPS($this->page);
		}

	public function addUpdate(\App\Record\RWGPS $rwgps = new \App\Record\RWGPS()) : void
		{
		if ($this->page->addHeader('Add / Update RWGPS'))
			{
			$this->page->addPageContent($this->view->addUpdate($rwgps));
			}
		}

	public function cueSheetRide(\App\Record\Ride $ride = new \App\Record\Ride(), int $routeNumber = 0) : void
		{
		$cueSheet = new \App\Report\CueSheet();
		$cueSheet->generateFromRide($ride, $routeNumber);
		$cueSheet->output('D', "CueSheet_ride_{$ride->rideId}.pdf");
		}

	public function cueSheetRWGPS(\App\Record\RWGPS $rwgps = new \App\Record\RWGPS()) : void
		{
		$cueSheet = new \App\Report\CueSheet();
		$cueSheet->generateFromRWGPS($rwgps);
		$cueSheet->output('D', "CueSheet_rwgps_{$rwgps->RWGPSId}.pdf");
		}

	public function detail(\App\Record\RWGPS $rwgps = new \App\Record\RWGPS()) : void
		{
		if ($this->page->addHeader('RideWithGPS Detail'))
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader($rwgps->title));
			$this->page->addPageContent($this->view->info($rwgps));
			$this->page->addPageContent($this->view->additional($rwgps));
			}
		}

	public function distance() : void
		{
		if ($this->page->addHeader('Distance To Start'))
			{
			$select = new \PHPFUI\Button('Select Coordinates');
			$show = new \PHPFUI\Submit('Search');
			$reveal = new \App\UI\CoordinatePicker($this->page, $select);
			$reveal->addClass('medium');
			$reveal->addMyLocation();

			$reveal->getForm()->add($reveal->getButtonAndCancel($show));
			$this->page->addPageContent($select);

			if (\is_numeric($_GET['latitude'] ?? '') && \is_numeric($_GET['longitude'] ?? ''))
				{
				$latitude = (float)$_GET['latitude'];
				$longitude = (float)$_GET['longitude'];
				$rwgpsTable = new \App\Table\RWGPS();
				$rwgpsTable->distanceFrom($latitude, $longitude);

				$this->page->addPageContent(new \PHPFUI\Header(new \PHPFUI\Link("https://www.google.com/maps/?q={$latitude},{$longitude}", 'Distance from here (Google Maps)'), 5));

				$distanceView = new \App\View\RideWithGPS($this->page);
				$this->page->addPageContent($distanceView->list($rwgpsTable, ['meters' => 'Dist']));
				}
			}
		}

	public function find() : void
		{
		if ($this->page->addHeader('Search RWGPS'))
			{
			$this->page->addPageContent(new \App\View\RWGPS\Search($this->page));
			}
		}

	public function forLocation(\App\Record\StartLocation $startLocation = new \App\Record\StartLocation()) : void
		{
		if ($this->page->addHeader('RWGPS Routes for Location'))
			{
			if (! $startLocation->loaded())
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Start Location not found'));

				return;
				}
			$this->page->addPageContent($this->view->byStartLocation($startLocation));
			}
		}

	public function settings() : void
		{
		if ($this->page->addHeader('RideWithGPS Settings'))
			{
			$this->page->addPageContent($this->view->edit());
			}
		}

	public function stats(\App\Record\RWGPS $rwgps = new \App\Record\RWGPS()) : void
		{
		if ($this->page->isAuthorized('RWGPS Stats') && $rwgps->RWGPSId)
			{
			$div = new \PHPFUI\FieldSet('Ride Details');
			$table = new \PHPFUI\Table();
			$headers = ['rideDate' => 'Date', 'mileage' => 'Mileage', 'elevation' => 'Elev', 'averagePace' => 'Average', 'pace' => 'Pace'];
			$table->setHeaders($headers);
			$paceTable = new \App\Table\Pace();
			$rideTable = new \App\Table\Ride();
			$rides = $rideTable->getRWGPSStats($rwgps);
			$count = \count($rides);

			if (! $count)
				{
				$table = new \PHPFUI\Callout('warning');
				$table->add('No club rides found for this route');
				}
			$mileage = 0.0;
			$elevation = 0.0;

			foreach ($rides as $ride)
				{
				$mileage += (float)$ride->mileage;
				$elevation += (float)$ride->elevation;
				$row = $ride->toArray();
				$row['pace'] = $paceTable->getPace($ride->paceId);
				$table->addRow($row);
				}
			$div->add($table);

			if ($count)
				{
				$div->add(new \App\UI\Display('Average Mileage', \number_format($mileage / $count, 1)));
				$div->add(new \App\UI\Display('Average Elevation', (int)($elevation / $count)));
				}
			$this->page->setRawResponse($div, false);
			}
		elseif ($this->page->addHeader('RWGPS Stats'))
			{
			$this->page->addPageContent($this->view->stats());
			}
		}

	public function upcoming() : void
		{
		if ($this->page->addHeader('Upcoming RWGPS'))
			{
			$rwgpsTable = new \App\Table\RWGPS();
			$rwgpsTable->setNonClubBetween();
			$this->page->addPageContent($this->view->list($rwgpsTable, ['date' => 'Date']));
			}
		}
	}
