<?php

namespace App\View\Ride;

class Search
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function csvSearch() : string
		{
		$button = new \PHPFUI\Button($name = 'Download .csv');
		$modal = $this->getDateRangeModal($button, $name);
		$output = new \PHPFUI\Container();
		$output->add($button);

		if (! empty($_GET['start']) && ! empty($_GET['end']))
			{
			$rideTable = new \App\Table\Ride();
			$rides = $rideTable->find($_GET);
			$model = new \App\Model\Ride();
			$model->downloadCSV($rides);
			}
		else
			{
			$modal->showOnPageLoad();
			}

		return "{$output}";
		}

	public function getDateRangeModal(\PHPFUI\HTML5Element $modalLink, string $name = 'Search Rides') : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->setAttribute('method', 'get');
		$fieldSet = new \PHPFUI\FieldSet($name);
		$end = \App\Tools\Date::today();
		$start = $end - 30;
		$min = $max = '';

		if (! empty($_GET['minDistance']))
			{
			$min = $_GET['minDistance'];
			}

		if (! empty($_GET['maxDistance']))
			{
			$max = $_GET['maxDistance'];
			}

		if (! empty($_GET['start']))
			{
			$start = \App\Tools\Date::fromString($_GET['start']);
			}

		if (! empty($_GET['end']))
			{
			$end = \App\Tools\Date::fromString($_GET['end']);
			}
		$multiColumn = new \PHPFUI\MultiColumn();
		$multiColumn->add(new \PHPFUI\Input\Date($this->page, 'start', 'Start Date', \App\Tools\Date::toString($start)));
		$multiColumn->add(new \PHPFUI\Input\Date($this->page, 'end', 'End Date', \App\Tools\Date::toString($end)));
		$fieldSet->add($multiColumn);
		$multiColumn = new \PHPFUI\MultiColumn();

		$minDistance = new \PHPFUI\Input\Number('minDistance', 'Minimum Distance', $min);
		$minDistance->addAttribute('max', (string)999)->addAttribute('min', (string)0);
		$multiColumn->add($minDistance);

		$maxDistance = new \PHPFUI\Input\Number('maxDistance', 'Maximum Distance', $max);
		$maxDistance->addAttribute('max', (string)999)->addAttribute('min', (string)0);
		$multiColumn->add($maxDistance);

		$fieldSet->add($multiColumn);
		$multiColumn = new \PHPFUI\MultiColumn();
		$multiColumn->add(new \PHPFUI\Input\Text('title', 'Phrase in Title', $_GET['title'] ?? ''));
		$multiColumn->add(new \PHPFUI\Input\Text('description', 'Phrase in Description', $_GET['description'] ?? ''));
		$fieldSet->add($multiColumn);

		$startLocationView = new \App\View\StartLocation($this->page);
		$location = $_GET['startLocationId'] ?? 0;
		$fieldSet->add($startLocationView->getEditControl($location, 'startLocationId', 'Limit to Start Location'));

		$categoryView = new \App\View\Categories($this->page);
		$categories = $_GET['categories'] ?? [];
		$multiPicker = $categoryView->getMultiCategoryPicker('categories', 'Limit to Categories', $categories);
		$multiPicker->setColumns(2);
		$fieldSet->add($multiPicker);

		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit($name);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	public function list() : \PHPFUI\Container
		{
		$button = new \PHPFUI\Button($name = 'Search Rides');
		$modal = $this->getDateRangeModal($button, $name);
		$output = new \PHPFUI\Container();
		$output->add($button);

		if (! empty($_GET['start']) && ! empty($_GET['end']))
			{
			$view = new \App\View\Rides($this->page);
			$rideTable = new \App\Table\Ride();
			$rides = $rideTable->find($_GET);
			$output->add($view->schedule($rides, 'No Rides found'));

			if (\count($rides))
				{
				$output->add($button);
				}
			}
		else
			{
			$modal->showOnPageLoad();
			}

		return $output;
		}
	}
