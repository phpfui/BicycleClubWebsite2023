<?php

namespace App\View\StartLocation;

class Coordinates
  {
	public function __construct(private \App\View\Page $page)
		{
		}

	public function assigned() : \App\UI\ContinuousScrollTable
	 {
	 $startLocationTable = new \App\Table\StartLocation();
	 $condition = new \PHPFUI\ORM\Condition('latitude', null, new \PHPFUI\ORM\Operator\IsNotNull());
	 $condition->and(new \PHPFUI\ORM\Condition('longitude', null, new \PHPFUI\ORM\Operator\IsNotNull()));
	 $startLocationTable->setWhere($condition);
	 $view = new \App\View\StartLocation($this->page);

	 return $view->showLocations($startLocationTable);
	 }

	public function missing() : \App\UI\ContinuousScrollTable
	 {
	 $startLocationTable = new \App\Table\StartLocation();
	 $condition = new \PHPFUI\ORM\Condition('latitude', null, new \PHPFUI\ORM\Operator\IsNull());
	 $condition->or(new \PHPFUI\ORM\Condition('longitude', null, new \PHPFUI\ORM\Operator\IsNull()));
	 $startLocationTable->setWhere($condition);
	 $view = new \App\View\StartLocation($this->page);

	 return $view->showLocations($startLocationTable);
	 }

	public function update() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$startLocationTable = new \App\Table\StartLocation();
		$startLocationsTotal = \count($startLocationTable);

		$fieldset = new \PHPFUI\FieldSet('Start Locations');
		$fieldset->add(new \App\UI\Display('Total Start Locations', $startLocationsTotal));
		$condition = new \PHPFUI\ORM\Condition('latitude', null, new \PHPFUI\ORM\Operator\IsNull());
		$condition->or(new \PHPFUI\ORM\Condition('longitude', null, new \PHPFUI\ORM\Operator\IsNull()));
		$startLocationTable->setWhere($condition);
		$startLocationMissing = \count($startLocationTable);
		$fieldset->add(new \App\UI\Display('Start Locations with GPS Coordinates', $startLocationsTotal - $startLocationMissing));
		$fieldset->add(new \App\UI\Display('Start Locations missing GPS Coordinates', $startLocationMissing));
		$container->add($fieldset);

		$fieldset = new \PHPFUI\FieldSet('RWGPS Routes');
		$RWGPSTable = new \App\Table\RWGPS();
		$RWGPSTotal = \count($RWGPSTable);
		$fieldset->add(new \App\UI\Display('Total RWGPS Routes', $RWGPSTotal));
		$condition = new \PHPFUI\ORM\Condition('startLocationId', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$RWGPSTable->setWhere($condition);
		$RWGPSWith = \count($startLocationTable);
		$fieldset->add(new \App\UI\Display('RWGPS Routes with Start Locations', $RWGPSWith));
		$fieldset->add(new \App\UI\Display('RWGPS Routes missing Start Locations', $RWGPSTotal - $RWGPSWith));
		$container->add($fieldset);

		$callout = new \PHPFUI\Callout('info');
		$callout->add('<p>This function will match rides with start locations, then select the most often led RWGPS route and assign those GPS Coordinates to the start location.</p>');
		$callout->add('<p>This function will also assign the start location to the RWGPS route.</p>');
		$container->add($callout);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$overWriteButton = new \PHPFUI\Button('Overwrite Existing GPS Coordinates', '/Locations/Coordinates/updateExisting');
		$overWriteButton->addClass('warning');
		$overWriteButton->setConfirm('This will replace any existing hand edited start location GPS coordinates.  Are you sure?');
		$updateButton = new \PHPFUI\Button('Only Add Missing GPS Coordinates', '/Locations/Coordinates/updateAdd');
		$updateButton->addClass('success');
		$buttonGroup->addButton($updateButton);
		$buttonGroup->addButton($overWriteButton);
		$container->add($buttonGroup);

		return $container;
		}
  }
