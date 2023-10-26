<?php

namespace App\View\Ride;

class Statistics
	{
	private \App\Table\Ride $rideTable;

	public function __construct(private readonly \App\View\Page $page, private readonly string $title)
		{
		$this->rideTable = new \App\Table\Ride();
		}

	public function download() : \PHPFUI\Container
		{
		$button = new \PHPFUI\Button('Download ' . $this->title . ' Statistics');
		$rideSearch = new \App\View\Ride\Search($this->page);
		$modal = $rideSearch->getDateRangeModal($button, $this->title . ' Statistics', 'Download');
		$output = new \PHPFUI\Container();
		$output->add($button);

		if (! empty($_GET['start']) && ! empty($_GET['end']))
			{
			$this->rideTable->find($_GET);

			$rides = $this->rideTable->getArrayCursor();
			$input = [];
			$sql = $this->rideTable->getSQL($input);
			\App\Tools\Logger::get()->debug($input, $sql);

			if (\count($rides))
				{
				$writer = new \App\Tools\CSV\FileWriter(\str_replace(' ', '_', $this->title . ' Statistics.csv'));
				$writer->addHeaderRow();

				foreach ($rides as $rider)
					{
					$writer->outputRow($rider);
					}
				}
			}
		else
			{
			$modal->showOnPageLoad();
			}

		return $output;
		}

	public function getRideTable() : \App\Table\Ride
		{
		return $this->rideTable;
		}
	}
