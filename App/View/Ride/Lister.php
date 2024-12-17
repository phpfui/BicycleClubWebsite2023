<?php

namespace App\View\Ride;

class Lister
	{
	private readonly bool $metric;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->metric = (bool)$page->value('RWGPSUnits');
		}

	public function list(\App\Table\Ride $rideTable) : \App\UI\ContinuousScrollTable
		{
		$normalHeaders = [];
		$searchableHeaders = ['rideDate' => 'Ride Date', 'title' => 'RWGPS Link'];
		$sortableHeaders = $searchableHeaders + ['name' => 'Ride Name', 'Leader' => 'Leader'];

		$metric = $this->metric;

		if ($this->metric)
			{
			$sortableHeaders['meters'] = 'Dist Km';
			}
		else
			{
			$sortableHeaders['meters'] = 'Dist Mi';
			}

		$view = new \App\UI\ContinuousScrollTable($this->page, $rideTable);

		$view->addCustomColumn('name', static function(array $ride)
			{
			$name = new \PHPFUI\Link("/Rides/signedUp/{$ride['rideId']}", \PHPFUI\TextHelper::unhtmlentities($ride['name']), false);
			$name->addAttribute('target', '_blank');

			return $name;
			});

		$view->addCustomColumn('Leader', static function(array $ride)
			{
			return \PHPFUI\TextHelper::unhtmlentities($ride['firstName'] . ' ' . $ride['lastName']);
			});

		$view->addCustomColumn('title', static function(array $ride)
			{
			if (! $ride['RWGPSId'])
				{
				return '';
				}

			$name = new \PHPFUI\Link("/RWGPS/detail/{$ride['RWGPSId']}", \PHPFUI\TextHelper::unhtmlentities($ride['title']), false);
			$name->addAttribute('target', '_blank');

			return $name;
			});

		$view->addCustomColumn('meters', static function(array $ride) use ($metric)
			{
			if ($metric)
				{
				return \number_format($ride['meters'] / 1000, 2);
				}

			return \number_format($ride['meters'] * 0.000621371192, 2);
			});

		$view->setHeaders($sortableHeaders + $searchableHeaders + $normalHeaders)->setSortableColumns(\array_keys($sortableHeaders));
		$view->setSearchColumns($searchableHeaders);

		return $view;
		}
	}
