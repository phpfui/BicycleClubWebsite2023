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
			if (! $rwgps->empty())
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Route Added / Updated'));
				}
			$this->page->addPageContent($this->view->addUpdate($rwgps));
			}
		}

	public function cueSheetRide(\App\Record\Ride $ride = new \App\Record\Ride()) : void
		{
		$cueSheet = new \App\Report\CueSheet();
		$cueSheet->generateFromRide($ride);
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

	public function find() : void
		{
		if ($this->page->addHeader('Search RWGPS'))
			{
			$this->page->addPageContent(new \App\View\RideWithGPSSearch($this->page));
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
